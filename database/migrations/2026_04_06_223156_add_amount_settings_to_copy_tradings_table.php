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
        Schema::table('copy_tradings', function (Blueprint $table) {
            $table->enum('amount_type', ['manual', 'percentage'])->default('manual')->after('roi');
            $table->decimal('percentage', 5, 2)->nullable()->after('amount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('copy_tradings', function (Blueprint $table) {
            $table->dropColumn(['amount_type', 'percentage']);
        });
    }
};
