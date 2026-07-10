<?php

use App\Models\CronJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $command = 'lozand:run-copy-trades';
        $exists = CronJob::where('command', $command)->exists();
        if (!$exists) {
            $job = new CronJob();
            $job->command = 'lozand:run-copy-trades';
            $job->recommended = 60;
            $job->last_run = now()->subDays(6)->timestamp;
            $job->module = 'copy_trading_module';
            $job->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
