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
        $razorpay = [
            'RAZORPAY_KEY_ID' => ['value' => '', 'encrypt' => true],
            'RAZORPAY_KEY_SECRET' => ['value' => '', 'encrypt' => true],
            'RAZORPAY_DEFAULT_CURRENCY' => ['value' => 'INR', 'encrypt' => false],
            'RAZORPAYX_ACCOUNT_NUMBER' => ['value' => '', 'encrypt' => true],
        ];

        foreach ($razorpay as $key => $data) {
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
