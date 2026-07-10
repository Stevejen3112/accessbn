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
        Schema::create('copy_trading_histories', function (Blueprint $blueprint) {
            $decimal_places = getSetting('decimal_places');
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('copy_trading_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('amount', 24, $decimal_places);
            $blueprint->string('pair');
            $blueprint->string('copy_code');
            $blueprint->decimal('roi', 12, 2);
            $blueprint->decimal('profit', 24, $decimal_places)->nullable();
            $blueprint->enum('status', ['active', 'completed', 'cancelled'])->default('active')->index();
            $blueprint->timestamp('activated_at')->useCurrent();
            $blueprint->timestamp('completed_at')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copy_trading_histories');
    }
};
