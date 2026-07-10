<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;
use App\Models\WithdrawalMethod;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $depositGateway = new PaymentMethod();
        $depositGateway->name = 'Razorpay';
        $depositGateway->type = 'card';
        $depositGateway->class = 'automatic';
        $depositGateway->pay = 'razorpay';
        $depositGateway->status = 'enabled';
        $depositGateway->logo = 'razorpay.png';
        $depositGateway->payment_information = json_encode([
            [
                'code' => 'INR',
                'status' => 'enabled'
            ],
            [
                'code' => 'USD',
                'status' => 'enabled'
            ]
        ]);
        $depositGateway->save();

        $withdrawalGateway = new WithdrawalMethod();
        $withdrawalGateway->name = 'Razorpay';
        $withdrawalGateway->type = 'bank_transfer';
        $withdrawalGateway->class = 'automatic';
        $withdrawalGateway->pay = 'razorpay';
        $withdrawalGateway->status = 'enabled';
        $withdrawalGateway->logo = 'razorpay.png';
        $withdrawalGateway->payment_information = json_encode([
            [
                'code' => 'INR',
                'status' => 'enabled'
            ]
        ]);
        $withdrawalGateway->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PaymentMethod::where('pay', 'razorpay')->delete();
        WithdrawalMethod::where('pay', 'razorpay')->delete();
    }
};
