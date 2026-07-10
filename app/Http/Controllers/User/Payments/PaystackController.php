<?php

namespace App\Http\Controllers\User\Payments;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    /**
     * Display Paystack Payment Page
     */
    public function index()
    {
        $user_payment_details = session()->get('user_payment_details');

        if (!$user_payment_details) {
            return redirect()->route('user.deposits.index')->with('error', __('Payment details not found'));
        }

        $payment_method = PaymentMethod::find($user_payment_details['payment_method_id']);

        if (!$payment_method || !str_starts_with($payment_method->pay, 'paystack')) {
            return redirect()->route('user.deposits.index')->with('error', __('Invalid payment method'));
        }

        $page_title = __("Pay With :method", ['method' => $payment_method->name]);
        $template = config('site.template');

        return view("templates.$template.blades.user.deposits.payments.paystack", compact(
            'page_title',
            'user_payment_details',
            'payment_method'
        ));
    }

    /**
     * Validate and Initiate Paystack Transaction
     */
    public function paystackValidate(Request $request)
    {
        $user_deposit_details = session()->get('user_payment_details');

        if (!$user_deposit_details) {
            return response()->json(['status' => 'error', 'message' => __('Session expired, please try again')], 422);
        }

        $payment_method = PaymentMethod::find($user_deposit_details['payment_method_id']);

        if (!$payment_method || !str_starts_with($payment_method->pay, 'paystack')) {
            return response()->json(['status' => 'error', 'message' => __('Invalid payment method')], 422);
        }

        $amount = $user_deposit_details['amount'];
        $fee_percent = getSetting('deposit_fee', 0);
        $fee_amount = ($amount * $fee_percent) / 100;
        $total_amount = $amount + $fee_amount;

        $target_currency = config('site.paystack.default_currency', 'NGN');
        $conversion = rateConverter($total_amount, getSetting('currency'), $target_currency, 'paystack');

        if (empty($conversion) || $conversion['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => __('Currency conversion failed')], 422);
        }

        $converted_amount = round($conversion['converted_amount'], 2);
        $paystack_amount = (int) round($converted_amount * 100); // Paystack expects amount in sub-units (kobo/cents)

        $transaction_reference = (string) Str::orderedUuid();
        $payment_info = json_decode($payment_method->payment_information, true);
        $channels = $payment_info['channels'] ?? [];

        $paystack_service = new PaystackService();
        $initialization = $paystack_service->initializeTransaction(
            $paystack_amount,
            auth()->user()->email,
            $transaction_reference,
            route('user.deposits.new.paystack-callback', ['transaction_reference' => $transaction_reference]),
            $channels
        );

        if (!$initialization['status']) {
            return response()->json(['status' => 'error', 'message' => $initialization['message']], 422);
        }

        $paystack_data = $initialization['data'];

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
        $deposit->auto_res_dump = json_encode($paystack_data);
        $deposit->save();

        session()->forget('user_payment_details');

        return response()->json([
            'status' => 'success',
            'message' => __('Transaction initialized successfully'),
            'redirect' => $paystack_data['authorization_url']
        ]);
    }

    /**
     * Handle Paystack Callback (Redirect)
     */
    public function paystackCallback(Request $request)
    {
        $reference = $request->route('transaction_reference');

        if (!$reference) {
            return redirect()->route('user.deposits.index')->with('error', __('No reference found'));
        }

        $deposit = Deposit::where('transaction_reference', $reference)->first();

        if (!$deposit) {
            return redirect()->route('user.deposits.index')->with('error', __('Deposit record not found'));
        }

        if ($deposit->status === 'completed') {
            return redirect()->route('user.deposits.view', $reference)->with('success', __('Deposit already processed'));
        }

        $paystack_service = new PaystackService();
        $verification = $paystack_service->verifyTransaction($reference);

        if ($verification['status'] && $verification['data']['status'] === 'success') {
            //check if the payment has been completed before
            if ($deposit->status === 'completed') {
                return redirect()->route('user.deposits.view', $reference)->with('success', __('Deposit successful'));
            }
            $this->completeDeposit($deposit, $verification['data']);
            return redirect()->route('user.deposits.view', $reference)->with('success', __('Deposit successful'));
        }


        // Handle failure
        if ($deposit->status === 'pending') {
            $deposit->status = 'failed';
            $deposit->auto_res_dump = json_encode($verification['data'] ?? []);
            $deposit->save();

            $title = 'Deposit Failed';
            $message = __('Your deposit via :method (Ref: :reference) has failed or was cancelled.', [
                'method' => $deposit->paymentMethod->name,
                'reference' => $reference,
            ], $deposit->user->lang);

            recordNotificationMessage($deposit->user, $title, $message);
        }

        return redirect()->route('user.deposits.index')->with('error', $verification['message'] ?? __('Verification failed'));
    }

    /**
     * Handle Paystack Webhook
     */
    public function paystackWebhook(Request $request)
    {
        $paystack_service = new PaystackService();
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$paystack_service->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $deposit = Deposit::where('transaction_reference', $reference)->first();

            if ($deposit && $deposit->status === 'pending') {
                $this->completeDeposit($deposit, $event['data']);
            }
        } elseif (str_starts_with($event['event'], 'transfer.')) {
            $withdrawalController = app(\App\Http\Controllers\User\Withdrawal\PaystackController::class);
            $withdrawalController->handleWebhook($event);
        } else {
            $reference = $event['data']['reference'] ?? null;
            if ($reference) {
                $deposit = Deposit::where('transaction_reference', $reference)->first();
                if ($deposit && $deposit->status === 'pending') {
                    $deposit->status = 'failed';
                    $deposit->auto_res_dump = json_encode($event['data']);
                    $deposit->save();

                    $title = 'Deposit Failed';
                    $message = __('Your deposit via :method (Ref: :reference) has failed or was cancelled.', [
                        'method' => $deposit->paymentMethod->name,
                        'reference' => $reference,
                    ], $deposit->user->lang);
                    recordNotificationMessage($deposit->user, $title, $message);
                }
            }
        }



        return response()->json(['status' => 'success']);
    }

    /**
     * Complete the Deposit
     */
    protected function completeDeposit($deposit, $gateway_data)
    {
        // requery the deposit
        $deposit = $deposit->refresh();
        if ($deposit->status === 'completed') {
            return;
        }
        $deposit->status = 'completed';
        $deposit->auto_res_dump = json_encode($gateway_data);
        $deposit->save();

        // credit the user
        $user = $deposit->user;
        $user->balance += $deposit->amount;
        $user->save();

        // record transaction
        $website_currency = getSetting('currency');
        $ref = \Str::orderedUuid();
        $description = __('Deposit via :method', ['method' => $deposit->paymentMethod->name], $deposit->user->lang);
        $new_balance = $user->balance;
        recordTransaction($deposit->user, $deposit->amount, $website_currency, $deposit->converted_amount, $deposit->currency, $deposit->exchange_rate, 'credit', 'completed', $ref, $description, $new_balance);

        // send email
        $subject = __('Deposit Successful', [], $deposit->user->lang);
        $message = __('Your deposit of :amount has been confirmed.', ['amount' => showAmount($deposit->amount)], $deposit->user->lang);
        sendDepositEmail($subject, $message, $deposit);



        // Notify User
        $user = $deposit->user;
        $title = "Deposit Successful";
        $body = "Your deposit of " . showAmount($deposit->amount) . " has been confirmed.";
        recordNotificationMessage($user, $title, $body);
    }
}
