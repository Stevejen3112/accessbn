<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeService
{
    protected $stripe;
    protected $webhook_secret;

    public function __construct()
    {
        $secret_key = $this->safeDecrypt(config('site.stripe.secret'));
        $this->webhook_secret = $this->safeDecrypt(config('site.stripe.webhook_secret'));
        
        if ($secret_key) {
            $this->stripe = new StripeClient($secret_key);
        }
    }

    /**
     * Create a Stripe Checkout Session
     */
    public function createCheckoutSession($amount, $currency, $reference, $success_url, $cancel_url, $customer_email = null)
    {
        try {
            if (!$this->stripe) {
                throw new \Exception('Stripe client not initialized. Check your credentials.');
            }

            $params = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => 'Wallet Deposit',
                            'description' => 'Deposit to Wallet - Ref: ' . $reference,
                        ],
                        'unit_amount' => (int)($amount * 100), // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'client_reference_id' => $reference,
            ];

            if ($customer_email) {
                $params['customer_email'] = $customer_email;
            }

            $session = $this->stripe->checkout->sessions->create($params);

            return [
                'status' => true,
                'data' => $session->toArray(),
                'message' => 'Checkout session created successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Session Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage() ?? 'An unexpected error occurred during checkout initialization.',
            ];
        }
    }

    /**
     * Verify Stripe Webhook Signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        if (empty($this->webhook_secret) || empty($signature)) {
            Log::error('Stripe Webhook Error: Missing secret or signature header.');
            return false;
        }

        try {
            // This verifies the signature and the timestamp automatically
            $event = Webhook::constructEvent($payload, $signature, $this->webhook_secret);
            return $event;
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe Webhook Signature Verification Failed: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Stripe Webhook General Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve Session (for sync verification if needed)
     */
    public function getSession($sessionId)
    {
        try {
            if (!$this->stripe) {
                throw new \Exception('Stripe client not initialized.');
            }

            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            
            return [
                'status' => true,
                'data' => $session->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Session Retrieval Failed: ' . $e->getMessage());
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
            return $value; // Fallback if not encrypted
        }
    }
}
