<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trading_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->enum('type', ['crypto', 'forex'])->default('crypto');
            $table->json('exchanges')->nullable();
            $table->json('traded_pairs');
            $table->decimal('min_amount', 20, 8)->default(0);
            $table->decimal('max_amount', 20, 8)->default(0);
            $table->boolean('is_active')->default(false);
            $table->decimal('daily_return_min', 20, 8)->default(0);
            $table->decimal('daily_return_max', 20, 8)->default(0);
            $table->unsignedInteger('duration');
            $table->enum('duration_type', ['hour', 'day', 'week', 'month', 'year'])->default('hour');
            $table->json('trading_days')->nullable();
            $table->timestamps();
        });

        try {
            // fetch trading pairs
            $headers = [
                'x-license-key' => safeDecrypt(config('site.product_key')),
                'x-domain' => request()->getHost(),
                'x-version' => config('site.version')
            ];
            $url = 'https://lozand.com/api/v1/bots/trading-pairs';
            $response = Http::withHeaders($headers)->get($url);

            $crypto_pairs = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT'];
            $forex_pairs = ['EURUSD', 'GBPUSD', 'USDJPY', 'AUDUSD', 'USDCAD'];
            $exchanges = ['Binance', 'Coinbase', 'Kraken', 'Gate.io', 'KuCoin'];

            if ($response && $response->successful()) {
                $data = $response->json();
                $trading_pairs = $data['data']['pairs'] ?? [];
                $crypto_pairs = $trading_pairs['crypto'] ?? $crypto_pairs;
                $forex_pairs = $trading_pairs['forex'] ?? $forex_pairs;
                $exchanges = $data['data']['exchanges'] ?? $exchanges;
            }

            $this->createDefaultTradingBots($crypto_pairs, $forex_pairs, $exchanges);

        } catch (\Exception $e) {
            Log::error("Trading Bot Migration Error: " . $e->getMessage());
        }

        // update the settings to enable trading_bot module
        try {
            $modules = getSetting('modules');
            if ($modules) {
                $modulesArr = json_decode($modules, true);
                if (isset($modulesArr['trading_bot_module'])) {
                    $modulesArr['trading_bot_module']['status'] = 'enabled';
                }
                unset($modulesArr['trading_bot']);
                updateSetting('modules', $modulesArr);
            }
        } catch (\Exception $e) {
            Log::error("Module Enablement Error: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_bots');
    }

    private function createDefaultTradingBots($crypto_pairs, $forex_pairs, $exchanges)
    {
        $defaults = [
            [
                'name' => 'Perpetual Contract Execution Bot',
                'logo' => 'bot-1.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, rand(1, count($exchanges))),
                'traded_pairs' => array_slice($crypto_pairs, 0, 3),
                'min_amount' => 100,
                'max_amount' => 10000,
                'is_active' => true,
                'daily_return_min' => 1,
                'daily_return_max' => 5,
                'duration' => 20,
                'duration_type' => 'day',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'Spot Grid Trading Bot',
                'logo' => 'bot-2.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, rand(1, count($exchanges))),
                'traded_pairs' => $this->selectRandomPairs($crypto_pairs, 5),
                'min_amount' => 1000,
                'max_amount' => 50000,
                'is_active' => true,
                'daily_return_min' => 0.5,
                'daily_return_max' => 2.5,
                'duration' => 30,
                'duration_type' => 'day',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            [
                'name' => 'Futures Scalping Algorithm',
                'logo' => 'bot-3.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, rand(1, count($exchanges))),
                'traded_pairs' => $this->selectRandomPairs($crypto_pairs, 10),
                'min_amount' => 500,
                'max_amount' => 25000,
                'is_active' => true,
                'daily_return_min' => 2,
                'daily_return_max' => 8,
                'duration' => 7,
                'duration_type' => 'day',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'High-Frequency Arbitrage Bot',
                'logo' => 'bot-4.png',
                'type' => 'crypto',
                'exchanges' => $exchanges,
                'traded_pairs' => array_slice($crypto_pairs, 0, 15),
                'min_amount' => 10000,
                'max_amount' => 1000000,
                'is_active' => true,
                'daily_return_min' => 0.1,
                'daily_return_max' => 1.5,
                'duration' => 90,
                'duration_type' => 'day',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            [
                'name' => 'Institutional Forex Scalper',
                'logo' => 'bot-5.png',
                'type' => 'forex',
                'exchanges' => null,
                'traded_pairs' => array_slice($forex_pairs, 0, 5),
                'min_amount' => 200,
                'max_amount' => 50000,
                'is_active' => true,
                'daily_return_min' => 0.4,
                'daily_return_max' => 1.2,
                'duration' => 4,
                'duration_type' => 'week',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'EURNZD Momentum Bot',
                'logo' => 'bot-6.png',
                'type' => 'forex',
                'exchanges' => null,
                'traded_pairs' => ['EURNZD', 'EURUSD', 'NZDUSD'],
                'min_amount' => 50,
                'max_amount' => 5000,
                'is_active' => true,
                'daily_return_min' => 0.8,
                'daily_return_max' => 3.5,
                'duration' => 48,
                'duration_type' => 'hour',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'Cross-Asset Hedging Bot',
                'logo' => 'bot-7.png',
                'type' => 'forex',
                'exchanges' => null,
                'traded_pairs' => $this->selectRandomPairs($forex_pairs, 8),
                'min_amount' => 5000,
                'max_amount' => 100000,
                'is_active' => true,
                'daily_return_min' => 0.2,
                'daily_return_max' => 0.9,
                'duration' => 1,
                'duration_type' => 'year',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'Gold Synergy Algorithm',
                'logo' => 'bot-8.png',
                'type' => 'forex',
                'exchanges' => null,
                'traded_pairs' => ['XAUUSD', 'XAGUSD'],
                'min_amount' => 1000,
                'max_amount' => 250000,
                'is_active' => true,
                'daily_return_min' => 1.5,
                'daily_return_max' => 4.0,
                'duration' => 3,
                'duration_type' => 'month',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'DCA Strategy Bot',
                'logo' => 'bot-9.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, 2),
                'traded_pairs' => ['BTCUSDT', 'ETHUSDT'],
                'min_amount' => 10,
                'max_amount' => 10000,
                'is_active' => true,
                'daily_return_min' => 0.3,
                'daily_return_max' => 1.0,
                'duration' => 12,
                'duration_type' => 'month',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            [
                'name' => 'Volatility Breakout Bot',
                'logo' => 'bot-10.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, 1),
                'traded_pairs' => ['SOLUSDT', 'AVAXUSDT', 'LUNAUSDT'],
                'min_amount' => 250,
                'max_amount' => 15000,
                'is_active' => true,
                'daily_return_min' => 2.5,
                'daily_return_max' => 12.0,
                'duration' => 24,
                'duration_type' => 'hour',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            [
                'name' => 'Global Macro Bot',
                'logo' => 'bot-11.png',
                'type' => 'forex',
                'exchanges' => null,
                'traded_pairs' => $forex_pairs,
                'min_amount' => 5000,
                'max_amount' => 500000,
                'is_active' => true,
                'daily_return_min' => 0.1,
                'daily_return_max' => 0.5,
                'duration' => 1,
                'duration_type' => 'year',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'DeFi Liquidity Bot',
                'logo' => 'bot-12.png',
                'type' => 'crypto',
                'exchanges' => array_slice($exchanges, 0, rand(1, count($exchanges))),
                'traded_pairs' => ['ETHUSDT', 'BTCUSDT', 'SOLUSDT'],
                'min_amount' => 1000,
                'max_amount' => 250000,
                'is_active' => true,
                'daily_return_min' => 0.5,
                'daily_return_max' => 2.5,
                'duration' => 6,
                'duration_type' => 'month',
                'trading_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
        ];

        foreach ($defaults as $bot) {
            $bot['exchanges'] = json_encode($bot['exchanges']);
            $bot['traded_pairs'] = json_encode($bot['traded_pairs']);
            $bot['trading_days'] = json_encode($bot['trading_days']);
            $bot['created_at'] = now();
            $bot['updated_at'] = now();
            DB::table('trading_bots')->insert($bot);
        }
    }

    private function selectRandomPairs($pairs, $count)
    {
        $randomPairs = [];
        for ($i = 0; $i < $count; $i++) {
            $randomPairs[] = $pairs[array_rand($pairs)];
        }
        return array_unique($randomPairs);
    }
};
