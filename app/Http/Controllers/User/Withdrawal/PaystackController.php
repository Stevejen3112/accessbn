<?php

namespace App\Http\Controllers\User\Withdrawal;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    protected $paystack_service;

    public function __construct(PaystackService $paystack_service)
    {
        $this->paystack_service = $paystack_service;
    }

    /**
     * Show Paystack Withdrawal Form
     */
    public function index(Request $request)
    {
        try {
            $withdrawal_request = decrypt($request->withdrawal_request);
        } catch (\Exception $e) {
            return redirect()->route('user.withdrawals.new')->with('error', __('Invalid withdrawal request'));
        }

        $withdrawal_method = WithdrawalMethod::findOrFail($withdrawal_request['withdrawal_method_id']);
        $amount = $withdrawal_request['amount'];

        $banks_response = $this->paystack_service->getBanks();
        if (!$banks_response['status']) {
            return redirect()->route('user.withdrawals.new')->with('error', $banks_response['message']);
        }

        $banks = $banks_response['data'];
        $page_title = __('Withdraw via Paystack');
        $template = config('site.template');

        return view("templates.$template.blades.user.withdrawals.payments.paystack", compact(
            'page_title',
            'withdrawal_method',
            'amount',
            'banks',
            'withdrawal_request'
        ));
    }

    /**
     * Process Automatic Paystack Withdrawal
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|numeric|digits:10',
            'account_name' => 'required|string|max:255',
            'withdrawal_request' => 'required|string',
        ]);

        try {
            $withdrawal_request_data = decrypt($request->withdrawal_request);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Invalid request data')], 422);
        }

        $withdrawal_method = WithdrawalMethod::active()->findOrFail($withdrawal_request_data['withdrawal_method_id']);
        $amount = $withdrawal_request_data['amount'];
        $user = auth()->user();

        // Security check: Final balance verification
        if ($user->balance < $amount) {
            return response()->json(['status' => 'error', 'message' => __('Insufficient balance')], 422);
        }

        $fee_percent = getSetting('withdrawal_fee', 0);
        $fee_amount = ($amount * $fee_percent) / 100;
        $amount_payable = $amount - $fee_amount;

        $website_currency = getSetting('currency');
        $withdrawal_currency = config('site.paystack.default_currency', 'NGN');

        $conversion = rateConverter($amount_payable, $website_currency, $withdrawal_currency, 'withdrawal');
        if (empty($conversion) || $conversion['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => __('Currency conversion failed')], 422);
        }

        $converted_amount = $conversion['converted_amount'];
        $paystack_amount = (int) round($converted_amount * 100); // Amount in kobo

        $transaction_reference = (string) Str::orderedUuid();

        // 1. Create Transfer Recipient
        $recipient_response = $this->paystack_service->createTransferRecipient(
            $request->account_name,
            $request->account_number,
            $request->bank_code,
            $withdrawal_currency
        );

        if (!$recipient_response['status']) {
            return response()->json(['status' => 'error', 'message' => $recipient_response['message']], 422);
        }

        $recipient_code = $recipient_response['data']['recipient_code'];

        // 2. Initiate Transfer Immediately (Automatic)
        $transfer_response = $this->paystack_service->initiateTransfer(
            $paystack_amount,
            $recipient_code,
            $transaction_reference,
            __('Withdrawal from :site', ['site' => config('app.name')])
        );

        if (!$transfer_response['status']) {
            return response()->json(['status' => 'error', 'message' => $transfer_response['message']], 422);
        }

        $transfer_data = $transfer_response['data'];

        // 3. Debit User & Create Record
        $user->decrement('balance', $amount);

        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $user->id;
        $withdrawal->withdrawal_method_id = $withdrawal_method->id;
        $withdrawal->amount = $amount;
        $withdrawal->converted_amount = $converted_amount;
        $withdrawal->fee_percent = $fee_percent;
        $withdrawal->fee_amount = $fee_amount;
        $withdrawal->amount_payable = $amount_payable;
        $withdrawal->exchange_rate = $conversion['exchange_rate'];
        $withdrawal->transaction_reference = $transaction_reference;
        $withdrawal->currency = $withdrawal_currency;
        $withdrawal->status = 'pending'; // Awaiting webhook confirmation
        $withdrawal->structured_data = json_encode([
            'bank_code' => $request->bank_code,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'recipient_code' => $recipient_code,
            'transfer_code' => $transfer_data['transfer_code'] ?? null,
        ]);
        $withdrawal->auto_res_dump = json_encode($transfer_data);
        $withdrawal->save();

        // Record Transaction
        recordTransaction(
            $user, 
            $amount, 
            $website_currency, 
            $converted_amount, 
            $withdrawal_currency, 
            $conversion['exchange_rate'], 
            'debit', 
            'completed', 
            $transaction_reference, 
            __('Withdrawal via Paystack'), 
            $user->balance
        );

        // Notify User
        recordNotificationMessage(
            $user, 
            __('Withdrawal Initiated'), 
            __('Your withdrawal of :amount :currency is being processed.', ['amount' => $amount, 'currency' => $website_currency])
        );

        return response()->json([
            'status' => 'success',
            'message' => __('Withdrawal initiated successfully'),
            'redirect' => route('user.withdrawals.index')
        ]);
    }

    /**
     * Handle Withdrawal Specific Webhook logic
     */
    public function handleWebhook($event)
    {
        if ($event['event'] === 'transfer.success') {
            $reference = $event['data']['reference'];
            $withdrawal = Withdrawal::where('transaction_reference', $reference)->first();

            if ($withdrawal && $withdrawal->status === 'pending') {
                $withdrawal->status = 'completed';
                $withdrawal->auto_res_dump = json_encode($event['data']);
                $withdrawal->save();

                // Notify User
                $title = __('Withdrawal Successful');
                $message = __('Your withdrawal of :amount has been processed.', [
                    'amount' => showAmount($withdrawal->amount),
                ], $withdrawal->user->lang);
                recordNotificationMessage($withdrawal->user, $title, $message);

                // Send Email
                $custom_subject = "Withdrawal Completed";
                $custom_message = "Your withdrawal request has been successfully processed and funds have been sent to your bank account.";
                sendWithdrawalEmail($custom_subject, $custom_message, $withdrawal);
            }
        } elseif (in_array($event['event'], ['transfer.failed', 'transfer.reversed'])) {
            $reference = $event['data']['reference'];
            $withdrawal = Withdrawal::where('transaction_reference', $reference)->first();

            if ($withdrawal && $withdrawal->status === 'pending') {
                $withdrawal->status = 'failed';
                $withdrawal->auto_res_dump = json_encode($event['data']);
                $withdrawal->save();

                // Refund the user
                $user = $withdrawal->user;
                $user->refresh();
                $user->increment('balance', $withdrawal->amount);

                // Record transaction
                recordTransaction(
                    $user, 
                    $withdrawal->amount, 
                    getSetting('currency'), 
                    $withdrawal->converted_amount, 
                    $withdrawal->currency, 
                    $withdrawal->exchange_rate, 
                    'credit', 
                    'completed', 
                    $withdrawal->transaction_reference, 
                    __('Failed Withdrawal refund'), 
                    $user->balance
                );

                // Notify User
                $title = __('Withdrawal Failed');
                $message = __('Your withdrawal of :amount failed and has been refunded.', [
                    'amount' => showAmount($withdrawal->amount),
                ], $withdrawal->user->lang);
                recordNotificationMessage($withdrawal->user, $title, $message);
            }
        }
    }

    /**
     * Resolve Account via AJAX
     */
    public function resolveAccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required|numeric|digits:10',
            'bank_code' => 'required|string',
        ]);

        $response = $this->paystack_service->resolveAccountNumber(
            $request->account_number,
            $request->bank_code
        );

        if ($response['status']) {
            return response()->json([
                'status' => 'success',
                'account_name' => $response['data']['account_name'],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $response['message'],
        ], 422);
    }
}
