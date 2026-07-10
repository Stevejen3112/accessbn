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
        Schema::table('trading_bot_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('exit_time')->nullable()->after('profit_percentage');
            $table->unsignedInteger('leverage')->nullable()->after('exit_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trading_bot_logs', function (Blueprint $table) {
            $table->dropColumn(['exit_time', 'leverage']);
        });
    }
};
