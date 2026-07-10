<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $paystack = [
            'PAYSTACK_PUBLIC_KEY' => ['value' => '', 'encrypt' => true],
            'PAYSTACK_SECRET_KEY' => ['value' => '', 'encrypt' => true],
            'PAYSTACK_PAYMENT_URL' => ['value' => 'https://api.paystack.co', 'encrypt' => false],
            'PAYSTACK_DEFAULT_CURRENCY' => ['value' => 'NGN', 'encrypt' => false],
            'PAYSTACK_MERCHANT_EMAIL' => ['value' => '', 'encrypt' => false],
        ];

        foreach ($paystack as $key => $data) {
            updateEnv($key, $data['value'], $data['encrypt']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
