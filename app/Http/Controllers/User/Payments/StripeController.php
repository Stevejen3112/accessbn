<?php

namespace App\Http\Controllers\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    /**
     * Display Stripe Payment Summary
     */
    public function index()
    {
        $user_payment_details = session()->get('user_payment_details');

        if (!$user_payment_details) {
            return redirect()->route('user.deposits.index')->with('error', __('Payment details not found'));
        }

        $payment_method = PaymentMethod::find($user_payment_details['payment_method_id']);

        if (!$payment_method || $payment_method->pay !== 'stripe') {
            return redirect()->route('user.deposits.index')->with('error', __('Invalid payment method'));
        }

        $page_title = __("Pay With :method", ['method' => $payment_method->name]);
        $template = config('site.template');

        // Check if we are in test mode
        $stripe_key = safeDecrypt(config('site.stripe.key'));
        $is_test_mode = str_starts_with($stripe_key, 'pk_test');

        return view("templates.$template.blades.user.deposits.payments.stripe", compact(
            'page_title',
            'user_payment_details',
            'payment_method',
            'is_test_mode'
        ));
    }

    /**
     * Initialize Stripe Checkout Session
     */
    public function stripeInitialize(Request $request)
    {
        $user_payment_details = session()->get('user_payment_details');

        if (!$user_payment_details) {
            return response()->json(['status' => 'error', 'message' => __('Payment details not found')], 422);
        }

        $payment_method = PaymentMethod::find($user_payment_details['payment_method_id']);

        if (!$payment_method || $payment_method->pay !== 'stripe') {
            return response()->json(['status' => 'error', 'message' => __('Invalid payment method')], 422);
        }

        // Calculate amount and conversion
        $amount = $user_payment_details['amount'];
        $fee_percent = getSetting('deposit_fee', 0);
        $fee_amount = ($amount * $fee_percent) / 100;
        $total_amount = $amount + $fee_amount;

        $target_currency = config('site.stripe.default_currency', 'USD');
        $conversion = rateConverter($total_amount, getSetting('currency'), $target_currency, 'stripe');

        if (empty($conversion) || $conversion['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => __('Currency conversion failed')], 422);
        }

        $converted_amount = round($conversion['converted_amount'], 2);
        $transaction_reference = (string) Str::orderedUuid();

        // Create Checkout Session
        $stripe_service = new StripeService();
        $session = $stripe_service->createCheckoutSession(
            $converted_amount,
            $target_currency,
            $transaction_reference,
            route('user.deposits.new.stripe.success', ['transaction_reference' => $transaction_reference]),
            route('user.deposits.new.stripe.cancel', ['transaction_reference' => $transaction_reference]),
            auth()->user()->email
        );

        if (!$session['status']) {
            return response()->json(['status' => 'error', 'message' => $session['message']], 422);
        }

        $stripe_session_id = $session['data']['id'];
        $stripe_url = $session['data']['url'];

        // Create Pending Deposit Record
        $deposit = new Deposit();
        $deposit->user_id = auth()->id();
        $deposit->payment_method_id = $payment_method->id;
        $deposit->amount = $amount;
        $deposit->converted_amount = $converted_amount;
        $deposit->fee_percent = $fee_percent;
        $deposit->fee_amount = $fee_amount;
        $deposit->total_amount = $total_amount;
        $deposit->exchange_rate = $conversion['exchange_rate'];
        $deposit->transaction_reference = $transaction_reference;
        $deposit->currency = $target_currency;
        $deposit->status = 'pending';
        $deposit->expires_at = now()->addMinutes((int) getSetting('deposit_expires_at', 60))->timestamp;
        $deposit->structured_data = json_encode([]);
        $deposit->auto_res_dump = json_encode([
            'stripe_session_id' => $stripe_session_id,
            'initial_response' => $session['data']
        ]);
        $deposit->save();

        // Clear session details as we've successfully created the record
        session()->forget('user_payment_details');

        return response()->json([
            'status' => 'success',
            'redirect' => $stripe_url
        ]);
    }

    /**
     * Handle Successful Return
     */
    public function success(Request $request)
    {
        $transaction_reference = $request->transaction_reference;
        $deposit = Deposit::where('transaction_reference', $transaction_reference)->firstOrFail();

        // We check the session status via API for immediate fulfillment if webhook is slow
        $stripe_service = new StripeService();
        $auto_res_dump = json_decode($deposit->auto_res_dump, true);
        $session_id = $auto_res_dump['stripe_session_id'] ?? null;

        if ($session_id && $deposit->status === 'pending') {
            $session_check = $stripe_service->getSession($session_id);
            if ($session_check['status'] && $session_check['data']['payment_status'] === 'paid') {
                $this->fulfillDeposit($deposit, $session_check['data']);
                return redirect()->route('user.deposits.index')->with('success', __('Payment successful! Your wallet has been credited.'));
            }
        }

        if ($deposit->status === 'completed') {
            return redirect()->route('user.deposits.index')->with('success', __('Payment successful! Your wallet has been credited.'));
        }

        return redirect()->route('user.deposits.index')->with('success', __('Payment processed. Your balance will be updated shortly once confirmed.'));
    }

    /**
     * Handle Cancellation
     */
    public function cancel(Request $request)
    {
        $transaction_reference = $request->transaction_reference;
        $deposit = Deposit::where('transaction_reference', $transaction_reference)->first();

        if ($deposit && $deposit->status === 'pending') {
            $deposit->status = 'failed';
            $deposit->save();
        }

        return redirect()->route('user.deposits.new')->with('error', __('Payment was cancelled.'));
    }

    /**
     * Handle Stripe Webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $stripe_service = new StripeService();
        $event = $stripe_service->verifyWebhookSignature($payload, $signature);

        if (!$event) {
            Log::error('Stripe Webhook signature verification failed');
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $reference = $session->client_reference_id;

            $deposit = Deposit::where('transaction_reference', $reference)
                ->where('status', 'pending')
                ->first();

            if ($deposit) {
                $this->fulfillDeposit($deposit, $session->toArray());
                Log::info("Stripe deposit completed via webhook for session: {$session->id}");
            }
        }

        return response('Webhook Received', 200);
    }

    /**
     * Fulfillment logic to credit user
     */
    protected function fulfillDeposit($deposit, $session_data)
    {
        $deposit->status = 'completed';

        $auto_res_dump = json_decode($deposit->auto_res_dump, true);
        $auto_res_dump['webhook_data'] = $session_data;
        $deposit->auto_res_dump = json_encode($auto_res_dump);

        $deposit->save();

        $user = $deposit->user;
        $user->balance += $deposit->amount;
        $user->save();

        // Record Transaction
        $description = "Deposit via Stripe - Ref: " . $deposit->transaction_reference;
        recordTransaction(
            $user,
            $deposit->amount,
            getSetting('currency'),
            $deposit->converted_amount,
            $deposit->currency,
            $deposit->exchange_rate,
            'credit',
            'completed',
            $deposit->transaction_reference,
            $description,
            $user->balance
        );

        // Record Notification
        $title = "Deposit Completed";
        $body = __('Your deposit of :amount via Stripe was successful.', ['amount' => showAmount($deposit->amount)], $deposit->user->lang);
        recordNotificationMessage($user, $title, $body);

        // Send Email
        $subject = __('Deposit Successful', [], $deposit->user->lang);
        $message = __('Your deposit of :amount has been confirmed.', ['amount' => showAmount($deposit->amount)], $deposit->user->lang);
        sendDepositEmail($subject, $message, $deposit);

        // Clear session if it matches (sync for current user)
        session()->forget('user_payment_details');
    }
}
