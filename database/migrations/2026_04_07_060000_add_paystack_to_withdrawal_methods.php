<?php
 
use App\Models\WithdrawalMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        WithdrawalMethod::updateOrCreate(
            ['name' => 'Paystack'],
            [
                'logo' => 'paystack.png',
                'type' => 'bank_transfer',
                'class' => 'automatic',
                'pay' => 'paystack',
                'payment_information' => json_encode([
                    'currency' => 'NGN', // Default to NGN, can be updated in admin
                    'fields' => [
                        'account_number' => 'required|string|numeric',
                        'bank_code' => 'required|string',
                    ]
                ]),
                'status' => 'enabled',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        WithdrawalMethod::where('pay', 'paystack')->delete();
    }
};
