<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trading_bot_activations', function (Blueprint $table) {
            $table->unsignedBigInteger('last_profit_date')->nullable()->after('next_profit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trading_bot_activations', function (Blueprint $table) {
            $table->dropColumn('last_profit_date');
        });
    }
};
