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
        Schema::table('trading_bot_logs', function (Blueprint $table) {
            $table->decimal('exit_price', 18, 8)->nullable()->after('exit_time');
            $table->enum('direction', ['buy', 'sell', 'long', 'short'])->nullable()->after('exit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trading_bot_logs', function (Blueprint $table) {
            $table->dropColumn(['exit_price', 'direction']);
        });
    }
};
