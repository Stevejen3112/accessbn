<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

class RazorpayService
{
    // Standard Razorpay (Deposits)
    protected $key_id;
    protected $key_secret;
    protected $base_url;

    // RazorpayX (Payouts)
    protected $x_key_id;
    protected $x_key_secret;
    protected $x_account_number;

    public function __construct()
    {
        // Standard Razorpay credentials (for deposits: orders, payments)
        $this->key_id = $this->safeDecrypt(config('site.razorpay.key_id'));
        $this->key_secret = $this->safeDecrypt(config('site.razorpay.key_secret'));
        $this->base_url = 'https://api.razorpay.com/v1';

        // RazorpayX credentials (for payouts: contacts, fund accounts, payouts)
        $this->x_key_id = $this->safeDecrypt(config('site.razorpayx.key_id'));
        $this->x_key_secret = $this->safeDecrypt(config('site.razorpayx.key_secret'));
        $this->x_account_number = $this->safeDecrypt(config('site.razorpayx.account_number'));
    }

    // ─────────────────────────────────────────────────────────────
    // DEPOSIT METHODS (Standard Razorpay - dashboard.razorpay.com)
    // ─────────────────────────────────────────────────────────────

    /**
     * Create an Order (for Deposits)
     */
    public function createOrder($amount, $currency, $receipt)
    {
        try {
            $payload = [
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'receipt' => $receipt,
            ];

            $response = Http::withBasicAuth($this->key_id, $this->key_secret)
                ->post($this->base_url . '/orders', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json(),
                    'message' => 'Order created successfully',
                ];
            }

            Log::error('Razorpay Order Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['error']['description'] ?? 'Failed to create Razorpay order',
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Order Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An unexpected error occurred during order creation.',
            ];
        }
    }

    /**
     * Verify Payment Signature (for Deposits)
     */
    public function verifyPaymentSignature($order_id, $payment_id, $signature)
    {
        $generated_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $this->key_secret);
        return hash_equals($generated_signature, $signature);
    }

    // ─────────────────────────────────────────────────────────────
    // PAYOUT METHODS (RazorpayX - x.razorpay.com)
    // ─────────────────────────────────────────────────────────────

    /**
     * Create a Contact (for RazorpayX Withdrawals)
     */
    public function createContact($name, $email, $reference_id)
    {
        try {
            $payload = [
                'name' => $name,
                'email' => $email,
                'type' => 'customer',
                'reference_id' => $reference_id,
            ];

            $response = Http::withBasicAuth($this->x_key_id, $this->x_key_secret)
                ->post($this->base_url . '/contacts', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('RazorpayX Contact Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['error']['description'] ?? 'Failed to create contact',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayX Contact Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a Fund Account (for RazorpayX Withdrawals)
     */
    public function createFundAccount($contact_id, $name, $ifsc, $account_number)
    {
        try {
            $payload = [
                'contact_id' => $contact_id,
                'account_type' => 'bank_account',
                'bank_account' => [
                    'name' => $name,
                    'ifsc' => $ifsc,
                    'account_number' => $account_number,
                ]
            ];

            $response = Http::withBasicAuth($this->x_key_id, $this->x_key_secret)
                ->post($this->base_url . '/fund_accounts', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('RazorpayX Fund Account Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['error']['description'] ?? 'Failed to create fund account',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayX Fund Account Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Initiate a Payout (RazorpayX)
     */
    public function initiatePayout($amount, $fund_account_id, $reference_id, $currency = 'INR', $mode = 'IMPS', $purpose = 'payout')
    {
        try {
            if (empty($this->x_account_number)) {
                Log::error('RazorpayX Payout Error: RAZORPAYX_ACCOUNT_NUMBER is not configured.');
                return [
                    'status' => false,
                    'message' => 'RazorpayX account number is not configured. Please set RAZORPAYX_ACCOUNT_NUMBER in your environment.',
                ];
            }

            if (empty($this->x_key_id) || empty($this->x_key_secret)) {
                Log::error('RazorpayX Payout Error: RAZORPAYX_KEY_ID or RAZORPAYX_KEY_SECRET is not configured.');
                return [
                    'status' => false,
                    'message' => 'RazorpayX API keys are not configured. Please set RAZORPAYX_KEY_ID and RAZORPAYX_KEY_SECRET from x.razorpay.com.',
                ];
            }

            $payload = [
                'account_number' => $this->x_account_number,
                'fund_account_id' => $fund_account_id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'mode' => strtoupper($mode),
                'purpose' => $purpose,
                'reference_id' => $reference_id,
            ];

            Log::info('RazorpayX Payout Request', ['payload' => array_merge($payload, ['account_number' => '***' . substr($this->x_account_number, -4)])]);

            $response = Http::withBasicAuth($this->x_key_id, $this->x_key_secret)
                ->post($this->base_url . '/payouts', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json(),
                    'message' => 'Payout initiated successfully',
                ];
            }

            Log::error('RazorpayX Payout Error', [
                'status_code' => $response->status(),
                'body' => $response->body(),
            ]);
            return [
                'status' => false,
                'message' => $response->json()['error']['description'] ?? 'Failed to initiate payout (HTTP ' . $response->status() . ')',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayX Payout Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify Webhook Signature (RazorpayX)
     */
    public function verifyWebhookSignature($payload, $signature, $webhook_secret = null)
    {
        $secret = $webhook_secret ?? $this->x_key_secret;
        $generated_signature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($generated_signature, $signature);
    }

    protected function safeDecrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return $value; // Fallback if not encrypted
        }
    }
}
