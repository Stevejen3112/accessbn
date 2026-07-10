<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

class PaystackService
{
    protected $secret_key;
    protected $public_key;
    protected $base_url;

    public function __construct()
    {
        $this->secret_key = $this->safeDecrypt(config('site.paystack.secret_key'));
        $this->public_key = $this->safeDecrypt(config('site.paystack.public_key'));
        $this->base_url = config('site.paystack.payment_url', 'https://api.paystack.co');
    }

    /**
     * Initialize a transaction
     */
    public function initializeTransaction($amount, $email, $reference, $callback_url, array $channels = [])
    {
        try {
            $payload = [
                'amount' => $amount, // Amount in kobo/cents
                'email' => $email,
                'reference' => $reference,
                'callback_url' => $callback_url,
            ];

            if (!empty($channels)) {
                $payload['channels'] = $channels;
            }

            $response = Http::withToken($this->secret_key)
                ->post($this->base_url . '/transaction/initialize', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                    'message' => 'Transaction initialized successfully',
                ];
            }

            Log::error('Paystack Initialize Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['message'] ?? 'Failed to initialize transaction',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Initialize Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An unexpected error occurred during initialization.',
            ];
        }
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction($reference)
    {
        try {
            $response = Http::withToken($this->secret_key)
                ->get($this->base_url . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                    'message' => 'Transaction verified successfully',
                ];
            }

            Log::error('Paystack Verify Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['message'] ?? 'Failed to verify transaction',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An unexpected error occurred during verification.',
            ];
        }
    }

    /**
     * Create a transfer recipient (for withdrawals)
     */
    public function createTransferRecipient($name, $account_number, $bank_code, $currency = 'NGN')
    {
        try {
            $payload = [
                'type' => 'nuban',
                'name' => $name,
                'account_number' => $account_number,
                'bank_code' => $bank_code,
                'currency' => $currency,
            ];

            $response = Http::withToken($this->secret_key)
                ->post($this->base_url . '/transferrecipient', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                ];
            }

            Log::error('Paystack Recipient Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['message'] ?? 'Failed to create transfer recipient',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Recipient Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Initiate a transfer (payout)
     */
    public function initiateTransfer($amount, $recipient_code, $reference, $reason = '')
    {
        try {
            $payload = [
                'source' => 'balance',
                'amount' => $amount,
                'recipient' => $recipient_code,
                'reference' => $reference,
                'reason' => $reason,
            ];

            $response = Http::withToken($this->secret_key)
                ->post($this->base_url . '/transfer', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                    'message' => 'Transfer initiated successfully',
                ];
            }

            Log::error('Paystack Transfer Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['message'] ?? 'Failed to initiate transfer',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Transfer Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify Webhook Signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        return hash_equals(hash_hmac('sha512', $payload, $this->secret_key), $signature);
    }

    /**
     * Resolve account number (nuban validation)
     */
    public function resolveAccountNumber($account_number, $bank_code)
    {
        try {
            $response = Http::withToken($this->secret_key)
                ->get($this->base_url . '/bank/resolve', [
                    'account_number' => $account_number,
                    'bank_code' => $bank_code,
                ]);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                    'message' => 'Account resolved successfully',
                ];
            }

            Log::error('Paystack Resolve Error: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json()['message'] ?? 'Unable to resolve account',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Resolve Exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fetch Banks (for nuban validation)
     */
    public function getBanks($currency = 'NGN')
    {
        try {
            $response = Http::withToken($this->secret_key)
                ->get($this->base_url . '/bank', ['currency' => $currency]);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()['data'],
                ];
            }
            return ['status' => false, 'message' => 'Failed to fetch banks'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function safeDecrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            return $value; // Fallback if not encrypted (e.g. during dev)
        }
    }
}
