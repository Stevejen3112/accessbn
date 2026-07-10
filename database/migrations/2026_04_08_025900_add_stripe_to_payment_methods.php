<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $depositGateway = new PaymentMethod();
        $depositGateway->name = 'Stripe';
        $depositGateway->type = 'card';
        $depositGateway->class = 'automatic';
        $depositGateway->pay = 'stripe';
        $depositGateway->status = 'enabled';
        $depositGateway->logo = 'stripe.png';
        $depositGateway->payment_information = json_encode([
            [
                'code' => 'USD',
                'status' => 'enabled'
            ]
        ]);
        $depositGateway->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PaymentMethod::where('pay', 'stripe')->delete();
    }
};
