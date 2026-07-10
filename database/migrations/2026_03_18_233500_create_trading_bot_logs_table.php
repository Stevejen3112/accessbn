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
        Schema::create('trading_bot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trading_bot_activation_id')->constrained()->onDelete('cascade');
            $table->string('trading_pair');
            $table->string('exchange')->nullable();
            $table->enum('type', ['forex', 'crypto']);
            $table->decimal('amount', 20, 8)->default(0);
            $table->decimal('profit', 20, 8)->default(0);
            $table->decimal('profit_percentage', 20, 2)->default(0);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_bot_logs');
    }
};
