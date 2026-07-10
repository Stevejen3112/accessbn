<?php

use App\Models\PaymentMethod;
use App\Models\WithdrawalMethod;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Payment Methods for Deposit
        $payment_methods = [
            [
                'name' => 'Paystack',
                'logo' => 'paystack.png',
                'type' => 'card',
                'class' => 'automatic',
                'pay' => 'paystack-card',
                'payment_information' => json_encode(['channels' => ['card']]),
                'status' => 'enabled',
            ],
            [
                'name' => 'Paystack',
                'logo' => 'paystack.png',
                'type' => 'bank_transfer',
                'class' => 'automatic',
                'pay' => 'paystack-bank',
                'payment_information' => json_encode(['channels' => ['bank', 'bank_transfer']]),
                'status' => 'enabled',
            ],
            [
                'name' => 'Paystack',
                'logo' => 'paystack.png',
                'type' => 'digital_wallet',
                'class' => 'automatic',
                'pay' => 'paystack-wallet',
                'payment_information' => json_encode(['channels' => ['ussd', 'mobile_money', 'qr']]),
                'status' => 'enabled',
            ],
        ];

        foreach ($payment_methods as $method) {
            PaymentMethod::updateOrCreate(['pay' => $method['pay']], $method);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pays = [
            'paystack-card',
            'paystack-bank',
            'paystack-wallet',
        ];

        PaymentMethod::whereIn('pay', $pays)->delete();
    }
};
