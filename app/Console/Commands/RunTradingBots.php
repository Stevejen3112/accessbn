<?php

namespace App\Console\Commands;

use App\Models\TradingBotActivation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ReferralBonusTrait;

class RunTradingBots extends Command
{
    use ReferralBonusTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lozand:run-trading-bots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run trading bots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //check if forex module is loaded
        if (!moduleEnabled('trading_bot_module')) {
            $this->info("Trading Bot module is not enabled. Please enable it first.");
            return 0;
        }

        $this->info("Starting Trading Bot Manager...");


        // update all activations to completed if its active beyond end_date
        $this->info("Updating all activations to completed if its active beyond end_date...");
        $this->updateCompletedActivations();


        // calculate and update today_cycle_count for all active activations with today_cycle_reset_at not today
        $this->info("Calculating and updating today_cycle_count for all active activations with today_cycle_reset_at not today...");
        $this->updateTodayCycleCount();


        // Return today's profit
        $this->info("Returning today's profit for all active activations...");
        $this->returnTodayProfit();

        updateLastCronJob($this->signature);
    }


    // generate 

    protected function updateCompletedActivations()
    {
        $this->info("Checking for bots that reached their end date...");

        TradingBotActivation::with('bot', 'user')
            ->where('status', 'active')
            ->where('end_date', '<', now()->timestamp)
            ->chunkById(100, function ($activations) {
                /** @var \App\Models\TradingBotActivation $activation */
                foreach ($activations as $activation) {
                    try {
                        DB::transaction(function () use ($activation) {
                            $user = $activation->user;
                            $bot = $activation->bot;

                            // Return the Principal Capital to User Balance if enabled
                            if ($bot->is_capital_returned) {
                                $user->balance += $activation->amount;
                                $user->save();

                                // Record Transaction for Capital Return
                                $ref = 'BOT-CAP-' . strtoupper(\Str::random(10));
                                $desc = __('Capital return from completed bot :bot', ['bot' => $bot->name], $user->lang);
                                recordTransaction($user, (string) $activation->amount, getSetting('currency'), (string) $activation->amount, getSetting('currency'), 1, 'credit', 'completed', $ref, $desc, (string) $user->balance);
                            }

                            // Mark as completed
                            $activation->status = 'completed';
                            $activation->save();

                            // Record Notification Message
                            $title = "Trading Bot Completed";
                            if ($bot->is_capital_returned) {
                                $message = __("Your trading bot :bot_name has completed its scheduled run. Your capital of :amount has been returned to your balance. Total profit earned: :profit", [
                                    'bot_name' => $bot->name,
                                    'amount' => showAmount($activation->amount),
                                    'profit' => showAmount($activation->returned_profit),
                                ], $user->lang);
                            } else {
                                $message = __("Your trading bot :bot_name has completed its scheduled run. Total profit earned: :profit", [
                                    'bot_name' => $bot->name,
                                    'profit' => showAmount($activation->returned_profit),
                                ], $user->lang);
                            }
                            recordNotificationMessage($user, $title, $message);
                        });
                    } catch (\Exception $e) {
                        $this->error("Error completing activation {$activation->id}: " . $e->getMessage());
                        Log::error("Bot Completion Error: " . $e->getMessage(), [
                            'activation_id' => $activation->id,
                        ]);
                    }
                }
            });

        $this->info("Automated bot completion check finished.");
    }

    protected function updateTodayCycleCount()
    {

        TradingBotActivation::with('bot', 'user')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('today_cycle_reset_at')
                    ->orWhere('today_cycle_reset_at', '<', now()->startOfDay()->timestamp);
            })
            ->chunkById(100, function ($activations) {
                /** @var \App\Models\TradingBotActivation $activation */
                foreach ($activations as $activation) {

                    try {
                        // lets check if today is in trading day
                        $trading_days = $activation->bot->trading_days;
                        $today = date('l');
                        if (!in_array($today, $trading_days)) {
                            $this->info($today . ' is not a trading day for ' . $activation->bot->name);
                            continue;
                        }

                        // lets calculate next profit date via a private function
                        $next_profit_date = $this->calculateNextProfitDate($activation);

                        // Generate random return with 2 decimal places
                        $min = (float) ($activation->bot->daily_return_min ?? 0);
                        $max = (float) ($activation->bot->daily_return_max ?? 0);

                        // To get 2 decimal places: multiply by 100, rand, divide by 100
                        $daily_return = rand($min * 100, $max * 100) / 100;

                        $today_amount = 0;
                        while ($today_amount < 30) {
                            $calculated_amount = $activation->amount * rand(1, 7) / 100;
                            $today_amount += $calculated_amount;
                        }

                        // Generate random leverage based on bot type (Crypto: 5-50x, Forex: 50-500x)
                        $leverage = $activation->bot?->type === 'crypto' ? rand(3, 10) : rand(50, 500);

                        // Calculate today_amount_roi
                        $today_amount_roi = ($activation->amount * $daily_return) / ($today_amount * $leverage);

                        $activation->setAttribute('today_roi', (string) $daily_return);
                        $activation->setAttribute('today_amount', (string) $today_amount);
                        $activation->setAttribute('today_amount_roi', (string) $today_amount_roi);
                        $activation->setAttribute('leverage', $leverage);
                        $activation->today_cycle_reset_at = now()->timestamp;
                        $activation->next_profit_date = $next_profit_date;
                        $activation->save();
                    } catch (\Exception $e) {
                        $this->error("Error updating cycle count for activation {$activation->id}: " . $e->getMessage());
                        Log::error("Cycle Count Error: " . $e->getMessage(), [
                            'activation_id' => $activation->id,
                        ]);
                    }
                }
            });

        $this->info("Updated today's cycle counts for active bots.");
    }

    /**
     * Calculate the next profit distribution time based on bot type.
     */
    protected function calculateNextProfitDate($activation)
    {
        $botType = $activation->bot?->type ?? 'crypto';

        if ($botType === 'crypto') {
            $now = now();
            $endHour = 22; // Crypto window ends at 10 PM
            if ($now->hour >= $endHour) {
                return $now->copy()->addDay()->hour(rand(8, $endHour))->minute(rand(0, 59))->timestamp;
            }
            return $now->copy()->hour(rand($now->hour + 1, $endHour))->minute(rand(0, 59))->timestamp;
        } else {
            // Forex: Based on NYSE hours (9:30 AM - 4:00 PM ET)
            $nyTime = now()->setTimezone('America/New_York');
            $marketOpenHour = 9;
            $marketCloseHour = 16; // 4 PM

            // Check if it's weekend or past market close
            if ($nyTime->isWeekend() || $nyTime->hour >= $marketCloseHour) {
                $nextTradingDay = $nyTime->copy()->addDay();
                while ($nextTradingDay->isWeekend()) {
                    $nextTradingDay->addDay();
                }
                // Schedule for next trading day between 10 AM and 3 PM ET for safety
                return $nextTradingDay->hour(rand($marketOpenHour + 1, $marketCloseHour - 1))
                    ->minute(rand(0, 59))
                    ->setTimezone(config('app.timezone', 'UTC'))
                    ->timestamp;
            }

            // If it's before market open today (before 9:30 AM ET)
            if ($nyTime->hour < $marketOpenHour || ($nyTime->hour == $marketOpenHour && $nyTime->minute < 30)) {
                return $nyTime->copy()->hour(rand($marketOpenHour + 1, $marketCloseHour - 1))
                    ->minute(rand(0, 59))
                    ->setTimezone(config('app.timezone', 'UTC'))
                    ->timestamp;
            }

            // Currently within NY trading window
            $currentHour = $nyTime->hour;
            if ($currentHour >= $marketCloseHour - 1) {
                // Too close to close, move to tomorrow
                $nextDay = $nyTime->copy()->addDay();
                while ($nextDay->isWeekend()) {
                    $nextDay->addDay();
                }
                return $nextDay->hour(rand($marketOpenHour + 1, $marketCloseHour - 1))->minute(rand(0, 59))->setTimezone(config('app.timezone', 'UTC'))->timestamp;
            }

            return $nyTime->copy()
                ->hour(rand($currentHour + 1, $marketCloseHour - 1))
                ->minute(rand(0, 59))
                ->setTimezone(config('app.timezone', 'UTC'))
                ->timestamp;
        }
    }


    protected function returnTodayProfit()
    {
        $this->info("Checking for bots ready for profit distribution...");

        $startOfToday = now()->startOfDay()->timestamp;

        TradingBotActivation::with('bot', 'user')
            ->where('status', 'active')
            ->whereNotNull('next_profit_date')
            ->where('next_profit_date', '<', now()->timestamp)
            ->where(function ($query) use ($startOfToday) {
                $query->whereNull('last_profit_date')
                    ->orWhere('last_profit_date', '<', $startOfToday);
            })
            ->chunkById(100, function ($activations) {
                /** @var \App\Models\TradingBotActivation $activation */
                foreach ($activations as $activation) {
                    try {
                        DB::transaction(function () use ($activation) {
                            $user = $activation->user;
                            $bot = $activation->bot;
                            // Skip if mandatory trading data is missing
                            if (empty($bot->traded_pairs)) {
                                return;
                            }
                            if ($bot->type === 'crypto' && empty($bot->exchanges)) {
                                return;
                            }

                            //check if today is in trading day
                            $trading_days = $bot->trading_days;
                            $today = date('l');
                            if (!in_array($today, $trading_days)) {
                                $this->info($today . ' is not a trading day for ' . $bot->name);
                                return;
                            }


                            //if its forex, check if market is open
                            if ($bot->type === 'forex') {
                                $nyTime = now()->setTimezone('America/New_York');
                                $marketOpenHour = 9;
                                $marketCloseHour = 16;
                                if ($nyTime->isWeekend() || $nyTime->hour >= $marketCloseHour || $nyTime->hour < $marketOpenHour) {
                                    return;
                                }
                            }


                            $trading_pair = $bot->traded_pairs[array_rand($bot->traded_pairs)];
                            $exchange = $bot->type === 'crypto' ? $bot->exchanges[array_rand($bot->exchanges)] : null;

                            //get current price and price 30 minutes ago
                            $price_data = $this->getPriceData($trading_pair, $bot->type);
                            if (!is_array($price_data)) {
                                return;
                            }
                            if (empty($price_data)) {
                                return;
                            }

                            $current_price = $price_data['current_price'];
                            $price_30_minutes_ago = $price_data['price_30_minutes_ago'];

                            $current_price_conversion = rateConverter($current_price, 'USDT', getSetting('currency'), 'bot_trading');
                            $current_price_converted = $current_price_conversion['converted_amount'];

                            // determine direction
                            $direction = $bot->type == 'crypto' ? 'long' : 'buy';
                            if ($current_price < $price_30_minutes_ago) {
                                $direction = $bot->type == 'crypto' ? 'short' : 'sell';
                            }

                            $profit_percentage = $activation->today_amount_roi;
                            $amount = $activation->today_amount;
                            $leverage = $activation->leverage;

                            // Calculate profit based on direction and leverage
                            $profit = (string) (($amount * $profit_percentage * $leverage) / 100);

                            // Update User Balance
                            $user->balance += $profit;
                            $user->save();

                            // Give referral bonus on daily profit
                            try {
                                $this->giveReferralBonus($user, $profit);
                            } catch (\Exception $e) {
                                Log::error('Trading Bot Referral Bonus Error: ' . $e->getMessage());
                            }

                            // Record Transaction
                            $ref = 'BOT-PR-' . strtoupper(\Str::random(10));
                            $desc = __('Trading profit from :bot', ['bot' => $bot->name], $user->lang);
                            recordTransaction($user, (string) $profit, getSetting('currency'), (string) $profit, getSetting('currency'), 1, 'credit', 'completed', $ref, $desc, (string) $user->balance);

                            // Record Trading Bot Log
                            \App\Models\TradingBotLog::create([
                                'user_id' => $user->id,
                                'trading_bot_activation_id' => $activation->id,
                                'trading_pair' => $trading_pair,
                                'exchange' => $exchange,
                                'type' => $bot->type,
                                'amount' => $amount,
                                'profit' => $profit,
                                'profit_percentage' => $profit_percentage,
                                'exit_time' => $activation->next_profit_date,
                                'exit_price' => $current_price_converted,
                                'direction' => $direction,
                                'leverage' => $leverage,
                            ]);

                            // Update Activation
                            $activation->returned_profit += $profit;
                            $activation->last_profit_date = now()->timestamp;
                            $activation->save();

                            // Notify User
                            $title = 'Trading Profit Distributed';
                            $msg = __('Your bot :bot has generated a profit of :profit today.', ['bot' => $bot->name, 'profit' => showAmount($profit)], $user->lang);
                            recordNotificationMessage($user, $title, $msg);
                        });
                    } catch (\Exception $e) {
                        $this->error("Error distributing profit for activation {$activation->id}: " . $e->getMessage());
                        Log::error("Profit Distribution Error: " . $e->getMessage(), [
                            'activation_id' => $activation->id,
                        ]);
                    }
                }
            });

        $this->info("Daily profit distribution cycle complete.");
    }


    protected function getPriceData($trading_pair, $type)
    {
        try {
            $url = "https://lozand.com/api/v1/bots/market-data/{$type}/{$trading_pair}";
            $license_key = safeDecrypt(config('site.product_key'));

            $headers = [
                'x-license-key' => $license_key,
                'x-domain' => request()->getHost(),
                'x-version' => config('site.version')
            ];
            $response = Http::withHeaders($headers)->get($url);

            if ($response->failed()) {
                return false;
            }

            $data = $response->json('data');
            return $data;


        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }


}
