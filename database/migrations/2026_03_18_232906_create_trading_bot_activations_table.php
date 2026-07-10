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
        Schema::create('trading_bot_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trading_bot_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 8); //total amount given to the bot
            $table->decimal('today_roi', 20, 2)->default(0); // target roi % on the main amount based on the plan min/max
            $table->decimal('returned_profit', 20, 8)->default(0); // total profit returned to the user
            $table->decimal('today_amount', 20, 8)->default(0); // total amount allocated to trade for today
            $table->decimal('today_amount_roi', 20, 2)->default(0); // target roi % on todays amount
            $table->unsignedBigInteger('today_cycle_reset_at')->nullable(); //will store unix timestamp here
            $table->unsignedBigInteger('next_profit_date')->nullable(); //will store unix timestamp here
            $table->enum('status', ['active', 'suspended', 'completed'])->default('active');
            $table->unsignedBigInteger('start_date'); //will store unix timestamp here
            $table->unsignedBigInteger('end_date'); //will store unix timestamp here
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_bot_activations');
    }
};
