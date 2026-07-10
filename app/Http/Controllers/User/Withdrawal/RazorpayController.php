<?php

namespace App\Http\Controllers\User\Withdrawal;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    protected $razorpay_service;

    public function __construct(RazorpayService $razorpay_service)
    {
        $this->razorpay_service = $razorpay_service;
    }

    /**
     * Show RazorpayX Withdrawal Form
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

        $page_title = __('Withdraw via Razorpay');
        $template = config('site.template');

        return view("templates.$template.blades.user.withdrawals.payments.razorpay", compact(
            'page_title',
            'withdrawal_method',
            'amount',
            'withdrawal_request'
        ));
    }

    /**
     * Process Automatic RazorpayX Withdrawal
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'ifsc' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'withdrawal_request' => 'required|string'
        ]);

        try {
            $withdrawal_request = decrypt($request->withdrawal_request);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Invalid withdrawal request')
            ], 422);
        }

        $user = auth()->user();
        $amount = $withdrawal_request['amount'];
        $withdrawal_method = WithdrawalMethod::findOrFail($withdrawal_request['withdrawal_method_id']);

        if ($user->balance < $amount) {
            return response()->json(['status' => 'error', 'message' => __('Insufficient balance')], 422);
        }

        $fee_percent = getSetting('withdrawal_fee', 0);
        $fee_amount = ($amount * $fee_percent) / 100;
        $amount_after_fee = $amount - $fee_amount;

        $target_currency = config('site.razorpay.default_currency', 'INR');
        $conversion = rateConverter($amount_after_fee, getSetting('currency'), $target_currency, 'rpw');

        if (empty($conversion) || $conversion['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => __('Currency conversion failed')], 422);
        }

        $converted_amount = round($conversion['converted_amount'], 2);
        $payout_amount = (int) round($converted_amount * 100); // Razorpay expects amount in paise

        $transaction_reference = (string) Str::orderedUuid();

        // 1. Create Contact
        $contact = $this->razorpay_service->createContact(
            $request->account_name,
            $user->email,
            'cust_' . $user->id . '_' . time()
        );

        if (!$contact['status']) {
            return response()->json(['status' => 'error', 'message' => $contact['message']], 422);
        }


        $contact_id = $contact['data']['id'];

        // 2. Create Fund Account
        $fund_account = $this->razorpay_service->createFundAccount(
            $contact_id,
            $request->account_name,
            strtoupper($request->ifsc),
            $request->account_number
        );


        if (!$fund_account['status']) {
            return response()->json(['status' => 'error', 'message' => $fund_account['message']], 422);
        }


        $fund_account_id = $fund_account['data']['id'];

        // 3. Initiate Payout
        $payout = $this->razorpay_service->initiatePayout(
            $payout_amount,
            $fund_account_id,
            $transaction_reference,
            $target_currency,
            'IMPS', // default mode
            'payout'
        );

        if (!$payout['status']) {
            return response()->json(['status' => 'error', 'message' => $payout['message']], 422);
        }


        // Deduct from balance immediately to prevent double spend
        $user->balance -= $amount;
        $user->save();

        $expires_at = now()->addDays(7); // Keep record active

        $amount_payable = $payout_amount / 100;

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
        $withdrawal->currency = $target_currency;
        $withdrawal->status = 'pending';
        // Log payout details
        $payout_details = [
            'payout_id' => $payout['data']['id'],
            'fund_account_id' => $fund_account_id,
            'contact_id' => $contact_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'ifsc' => $request->ifsc
        ];
        $withdrawal->structured_data = json_encode([]);
        $withdrawal->auto_res_dump = json_encode($payout_details);
        $withdrawal->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Withdrawal initiated successfully via RazorpayX. It is currently processing.'),
            'redirect' => route('user.withdrawals.index')
        ]);
    }

    /**
     * Handle RazorpayX Payout Webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload_body = $request->getContent();
        $webhook_signature = $request->header('X-Razorpay-Signature');

        if (!$webhook_signature) {
            return response('Missing signature', 400);
        }

        $is_valid = $this->razorpay_service->verifyWebhookSignature($payload_body, $webhook_signature);

        if (!$is_valid) {
            Log::error('RazorpayX Webhook verification failed.', ['signature' => $webhook_signature]);
            return response('Invalid signature', 400);
        }

        $event = $request->input('event');
        $payload = $request->input('payload');

        if (in_array($event, ['payout.processed', 'payout.failed', 'payout.reversed'])) {
            $payout_id = $payload['payout']['entity']['id'];
            $reference = $payload['payout']['entity']['reference_id'];
            $reason = $payload['payout']['entity']['failure_reason'] ?? '';

            $withdrawal = Withdrawal::where('transaction_reference', $reference)
                ->where('status', 'pending')
                ->first();

            if ($withdrawal) {
                if ($event === 'payout.processed') {
                    $withdrawal->status = 'completed';
                    Log::info("RazorpayX Payout Completed: {$reference}");
                } else {
                    $withdrawal->status = 'failed';
                    // Reversal: Credit user back
                    $user = $withdrawal->user;
                    $user->balance += $withdrawal->amount;
                    $user->save();

                    Log::error("RazorpayX Payout Failed/Reversed: {$reference} - {$reason}");
                }

                $auto_res_dump = json_decode($withdrawal->auto_res_dump, true);
                if (is_array($auto_res_dump)) {
                    $auto_res_dump['webhook_event'] = $event;
                    $auto_res_dump['failure_reason'] = $reason;
                    $withdrawal->auto_res_dump = json_encode($auto_res_dump);
                }
                $withdrawal->save();
            }
        }

        return response('Webhook Received', 200);
    }
}
