<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CopyTradingHistory;
use App\Models\CopyTrading;
use App\Services\LozandServices;
use App\Traits\ReferralBonusTrait;

class RunCopyTrades extends Command
{
    use ReferralBonusTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lozand:run-copy-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process completed copy trades and distribute profits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!moduleEnabled('copy_trading_module')) {
            $this->info("Copy Trading module is not enabled.");
            return 0;
        }


        // if in sandbox mode, generate trading codes
        if (config('app.env') === 'sandbox') {
            $this->info("Sandbox mode is enabled. Generating trading codes...");
            $this->generateCopyTradingCodes();

        }

        $this->info("Checking for completed copy trades...");

        CopyTradingHistory::with('user')
            ->where('status', 'active')
            ->whereNotNull('completes_at')
            ->where('completes_at', '<=', now()->timestamp)
            ->chunkById(100, function ($activations) {
                /** @var \App\Models\CopyTradingHistory $activation */
                foreach ($activations as $activation) {
                    try {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($activation) {
                            $user = $activation->user;

                            // Calculate profit
                            $profit = (float) ($activation->amount * ($activation->roi / 100));
                            $totalReturn = (float) ($activation->amount + $profit);

                            $user->balance += $totalReturn;
                            $user->save();

                            // Give referral bonus on profit
                            try {
                                $this->giveReferralBonus($user, $profit);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Copy Trade Referral Bonus Error: ' . $e->getMessage());
                            }

                            // Update history record
                            $activation->status = 'completed';
                            $activation->profit = $profit;
                            $activation->completed_at = \Illuminate\Support\Carbon::now();
                            $activation->save();

                            // Record Transaction
                            $ref = 'COPY-COMPL-' . strtoupper(\Illuminate\Support\Str::random(10));
                            $desc = __('Return of capital and profit from completed copy trade :code', ['code' => $activation->copy_code], $user->lang);

                            recordTransaction(
                                $user,
                                (string) $totalReturn,
                                getSetting('currency'),
                                (string) $totalReturn,
                                getSetting('currency'),
                                1,
                                'credit',
                                'completed',
                                $ref,
                                $desc,
                                (string) $user->balance
                            );

                            // Record Notification
                            $title = __('Copy Trade Completed');
                            $message = __("Your copy trade :code has completed. Your capital of :amount and profit of :profit have been returned to your balance.", [
                                'code' => $activation->copy_code,
                                'amount' => showAmount($activation->amount),
                                'profit' => showAmount($profit),
                            ], $user->lang);

                            recordNotificationMessage($user, $title, $message);
                        });

                        $this->info("Completed copy trade {$activation->copy_code} for user {$activation->user->email}");

                    } catch (\Exception $e) {
                        $this->error("Error processing copy trade {$activation->id}: " . $e->getMessage());
                        \Illuminate\Support\Facades\Log::error("Copy Trade Completion Error: " . $e->getMessage(), [
                            'history_id' => $activation->id,
                        ]);
                    }
                }
            });

        $this->info("Copy trade completion check finished.");
        updateLastCronJob($this->signature);

        return 0;
    }


    // generate copy trading code
    private function generateCopyTradingCodes()
    {
        // Only when non-expired trading codes are less than 3
        $activeCount = CopyTrading::active()->count();

        if ($activeCount >= 3) {
            return;
        }

        $this->info("Active codes count ($activeCount) is low. Generating 5 new signals...");

        $lozandServices = new LozandServices();
        $tickersResponse = $lozandServices->futureTickers();

        if (!$tickersResponse || $tickersResponse['status'] !== 'success' || empty($tickersResponse['data'])) {
            $this->warn("Failed to fetch market data: " . ($tickersResponse['message'] ?? 'No data') . ". Aborting signal generation.");
            return;
        }

        $pairs = array_map(fn($item) => $item['ticker'], $tickersResponse['data']);

        for ($i = 0; $i < 5; $i++) {
            $code = strtoupper(\Illuminate\Support\Str::random(6));
            while (CopyTrading::where('code', $code)->exists()) {
                $code = strtoupper(\Illuminate\Support\Str::random(6));
            }

            CopyTrading::create([
                'code' => $code,
                'pair' => $pairs[array_rand($pairs)],
                'roi' => rand(1500, 9700) / 100, // 15% to 97%
                'amount_type' => 'percentage',
                'percentage' => rand(200, 500) / 100, // 2% to 5%
                'expires_at' => now()->addMinutes(rand(5, 15))->timestamp,
            ]);
        }

        $this->info("Generated 5 new copy trading signals.");
    }
}
