<?php

namespace App\Http\Controllers\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    /**
     * Display Razorpay Payment Page
     */
    public function index()
    {
        $user_payment_details = session()->get('user_payment_details');

        if (!$user_payment_details) {
            return redirect()->route('user.deposits.index')->with('error', __('Payment details not found'));
        }

        $payment_method = PaymentMethod::find($user_payment_details['payment_method_id']);

        if (!$payment_method || !str_starts_with($payment_method->pay, 'razorpay')) {
            return redirect()->route('user.deposits.index')->with('error', __('Invalid payment method'));
        }

        $page_title = __("Pay With :method", ['method' => $payment_method->name]);
        $template = config('site.template');

        // We need the key id for the frontend JS
        $razorpay_key = safeDecrypt(config('site.razorpay.key_id'));

        return view("templates.$template.blades.user.deposits.payments.razorpay", compact(
            'page_title',
            'user_payment_details',
            'payment_method',
            'razorpay_key'
        ));
    }

    /**
     * Initialize Razorpay Order
     */
    public function razorpayInitialize(Request $request)
    {
        $user_deposit_details = session()->get('user_payment_details');

        if (!$user_deposit_details) {
            return response()->json(['status' => 'error', 'message' => __('Session expired, please try again')], 422);
        }

        $payment_method = PaymentMethod::find($user_deposit_details['payment_method_id']);

        if (!$payment_method || !str_starts_with($payment_method->pay, 'razorpay')) {
            return response()->json(['status' => 'error', 'message' => __('Invalid payment method')], 422);
        }

        $amount = $user_deposit_details['amount'];
        $fee_percent = getSetting('deposit_fee', 0);
        $fee_amount = ($amount * $fee_percent) / 100;
        $total_amount = $amount + $fee_amount;

        $target_currency = config('site.razorpay.default_currency', 'INR');
        $conversion = rateConverter($total_amount, getSetting('currency'), $target_currency, 'razorpay');

        if (empty($conversion) || $conversion['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => __('Currency conversion failed')], 422);
        }

        $converted_amount = round($conversion['converted_amount'], 2);
        $razorpay_amount = (int) round($converted_amount * 100); // Amount in paise

        $transaction_reference = (string) Str::orderedUuid();

        $razorpay_service = new RazorpayService();
        $order = $razorpay_service->createOrder(
            $razorpay_amount,
            $target_currency,
            $transaction_reference
        );

        if (!$order['status']) {
            return response()->json(['status' => 'error', 'message' => $order['message']], 422);
        }

        $order_data = $order['data'];
        $razorpay_order_id = $order_data['id'];

        $expires_at = now()->addMinutes((int) getSetting('deposit_expires_at', 60));

        // Create Deposit Record
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
        $deposit->expires_at = $expires_at;
        $deposit->structured_data = json_encode([]);
        $deposit->auto_res_dump = json_encode([
            'razorpay_order_id' => $razorpay_order_id,
            'initial_response' => $order_data
        ]);
        $deposit->save();

        return response()->json([
            'status' => 'success',
            'order_id' => $razorpay_order_id,
            'amount' => $razorpay_amount,
            'currency' => $target_currency,
            'name' => getSetting('site_name', 'Lozand'),
            'description' => __('Account Funding'),
            'image' => asset('assets/images/' . getSetting('logo_square')),
            'prefill' => [
                'name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                'email' => auth()->user()->email,
                'contact' => auth()->user()->phone ?? '',
            ],
            'transaction_reference' => $transaction_reference
        ]);
    }

    /**
     * Verify successful payment from frontend
     */
    public function razorpayVerify(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required'
        ]);

        $razorpay_service = new RazorpayService();
        $is_valid = $razorpay_service->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$is_valid) {
            return response()->json(['status' => 'error', 'message' => __('Payment verification failed')], 400);
        }

        // Find deposit by order id in auto_res_dump
        $deposit = Deposit::where('auto_res_dump->razorpay_order_id', $request->razorpay_order_id)
            ->where('status', 'pending')
            ->orWhere('status', 'failed')
            ->first();

        if (!$deposit) {
            return response()->json(['status' => 'error', 'message' => __('Deposit not found or already completed')], 404);
        }

        // Credit user and complete deposit
        $deposit->status = 'completed';

        $auto_res_dump = json_decode($deposit->auto_res_dump, true);
        $auto_res_dump['razorpay_payment_id'] = $request->razorpay_payment_id;
        $auto_res_dump['razorpay_signature'] = $request->razorpay_signature;
        $deposit->auto_res_dump = json_encode($auto_res_dump);

        $deposit->save();

        $user = $deposit->user;
        $user->balance += $deposit->amount;
        $user->save();

        // Clear session
        session()->forget('user_payment_details');

        return response()->json([
            'status' => 'success',
            'message' => __('Payment successful! amount added to your wallet.'),
            'redirect' => route('user.deposits.index')
        ]);
    }

    /**
     * Check the status of a deposit for polling
     */
    public function razorpayCheckStatus(Request $request)
    {
        $request->validate([
            'transaction_reference' => 'required'
        ]);

        $deposit = Deposit::where('transaction_reference', $request->transaction_reference)
            ->where('user_id', auth()->id())
            ->first();

        if (!$deposit) {
            return response()->json(['status' => 'error', 'message' => __('Deposit not found')], 404);
        }

        if ($deposit->status !== 'pending') {
            return response()->json([
                'status' => 'success',
                'payment_status' => $deposit->status,
                'redirect' => route('user.deposits.view', ['transaction_reference' => $deposit->transaction_reference])
            ]);
        }

        return response()->json([
            'status' => 'success',
            'payment_status' => 'pending'
        ]);
    }

    /**
     * Handle Razorpay Webhooks (order.paid)
     */
    public function razorpayWebhook(Request $request)
    {
        $webhook_signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        if (!$webhook_signature) {
            return response('Missing signature', 400);
        }

        $razorpay_service = new RazorpayService();
        $is_valid = $razorpay_service->verifyWebhookSignature($payload, $webhook_signature);

        if (!$is_valid) {
            Log::error('Razorpay Webhook verification failed.');
            return response('Invalid signature', 400);
        }

        $event = $request->input('event');
        $payload_data = $request->input('payload');

        if ($event === 'order.paid') {
            $order_entity = $payload_data['order']['entity'];
            $order_id = $order_entity['id'];

            $deposit = Deposit::where('auto_res_dump->razorpay_order_id', $order_id)
                ->where('status', 'pending')
                ->first();

            if ($deposit) {
                $deposit->status = 'completed';

                $auto_res_dump = json_decode($deposit->auto_res_dump, true);
                $auto_res_dump['webhook_confirmed'] = true;
                $deposit->auto_res_dump = json_encode($auto_res_dump);

                $deposit->save();

                $user = $deposit->user;
                $user->balance += $deposit->amount;
                $user->save();

                Log::info("Razorpay deposit completed via webhook for order: {$order_id}");
            }
        }

        // Delegate withdrawal webhook events to the WithdrawalController if needed
        if (str_starts_with($event, 'payout.')) {
            $withdrawal_controller = app(\App\Http\Controllers\User\Withdrawal\RazorpayController::class);
            return $withdrawal_controller->handleWebhook($request);
        }

        return response('Webhook Recieved', 200);
    }
}
