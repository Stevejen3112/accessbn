<?php

namespace App\Http\Controllers\User\Trading;

use App\Http\Controllers\Controller;
use App\Models\CopyTrading;
use App\Models\CopyTradingHistory;
use App\Services\LozandServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class CopyTradingController extends Controller
{
    public function __construct()
    {
        if (!moduleEnabled('copy_trading_module')) {
            abort(404);
        }
    }

    /**
     * Display the copy trading terminal.
     */
    public function index()
    {
        $current_ticker = request('ticker') ?? 'BTCUSDT';
        $page_title = __("Copy Trading");
        $template = config('site.template');

        $all_crypto_tickers = [];
        $last_error_message = null;
        $current_ticker_info = [];

        $lozandServices = new LozandServices();
        $get_all_crypto_tickers = $lozandServices->futureTickers();

        if (!$get_all_crypto_tickers || $get_all_crypto_tickers['status'] !== 'success') {
            $last_error_message = $get_all_crypto_tickers['message'] ?? __('Failed to fetch market data.');
        } else {
            $all_crypto_tickers = $get_all_crypto_tickers['data'];
            foreach ($all_crypto_tickers as $ticker) {
                if ($ticker['ticker'] == $current_ticker) {
                    $current_ticker_info = $ticker;
                    break;
                }
            }
        }

        // recent trades
        $recent_trades = [];
        $get_recent_trades = $lozandServices->futuresRecentTrades($current_ticker);
        if ($get_recent_trades['status'] === 'success') {
            $recent_trades = $get_recent_trades['data'];
        }

        // order book
        $order_book = [];
        $get_order_book = $lozandServices->futuresOrderBook($current_ticker);
        if ($get_order_book['status'] === 'success') {
            $order_book = $get_order_book['data'];
        }

        $add_available = Auth::user()->balance ?? 0.0;

        $activations = CopyTradingHistory::with('copyTrading')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        $active_sandbox_codes = [];
        if (config('app.env') === 'sandbox') {
            $active_sandbox_codes = CopyTrading::active()->latest()->get();
        }

        if (request()->ajax()) {
            return view("templates.{$template}.blades.user.trading.copy.index_inner", compact(
                'page_title',
                'all_crypto_tickers',
                'last_error_message',
                'current_ticker_info',
                'current_ticker',
                'recent_trades',
                'order_book',
                'add_available',
                'activations',
                'active_sandbox_codes'
            ));
        }

        return view("templates.{$template}.blades.user.trading.copy.index", compact(
            'page_title',
            'all_crypto_tickers',
            'last_error_message',
            'current_ticker_info',
            'current_ticker',
            'recent_trades',
            'order_book',
            'add_available',
            'activations',
            'active_sandbox_codes'
        ));
    }

    /**
     * Check a trading code and return strategy details.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $strategy = CopyTrading::active()->notExpired()->where('code', $request->code)->first();

        if (!$strategy) {
            return response()->json(['success' => false, 'message' => __('Invalid or expired trading code.')], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $strategy->id,
                'pair' => $strategy->pair,
                'roi' => $strategy->roi,
                'ticker' => $strategy->pair,
                'amount_type' => $strategy->amount_type,
                'percentage' => $strategy->percentage,
            ]
        ]);
    }

    /**
     * Display a listing of the user's copy trading history.
     */
    public function history(Request $request)
    {
        $page_title = __('Trading History');
        $activations = CopyTradingHistory::with('copyTrading')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(getSetting('pagination', 15));

        // Analytics Data
        $analytics = $this->getAnalyticsData($request);
        $stats = $analytics['stats'];
        $chart_distribution = $analytics['chart_distribution'];
        $chart_trend = $analytics['chart_trend'];

        return view('templates.' . config('site.template') . '.blades.user.trading.copy.history', compact('page_title', 'activations', 'stats', 'chart_distribution', 'chart_trend'));
    }

    /**
     * Get shared analytics data for copy trading (user scoped)
     */
    private function getAnalyticsData(Request $request)
    {
        $userId = Auth::id();
        $stats = [
            'total_profit' => (float) CopyTradingHistory::where('user_id', $userId)->sum('profit'),
            'today_profit' => (float) CopyTradingHistory::where('user_id', $userId)->whereDate('created_at', now()->toDateString())->sum('profit'),
            'active_trades' => CopyTradingHistory::where('user_id', $userId)->where('status', 'active')->count(),
            'total_trades' => CopyTradingHistory::where('user_id', $userId)->count(),
        ];

        // Distribution: Profit by Pair
        $distribution = CopyTradingHistory::where('user_id', $userId)
            ->select('pair', DB::raw('sum(profit) as total_profit'))
            ->groupBy('pair')
            ->get();

        $chart_distribution = [
            'labels' => $distribution->pluck('pair'),
            'data' => $distribution->map(fn($d) => (float) $d->total_profit),
        ];

        // Profit Trend
        $days = (int) $request->get('interval', 7);
        $startDate = now()->subDays($days)->startOfDay();

        $trend_raw = CopyTradingHistory::where('user_id', $userId)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('sum(profit) as total_profit')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->pluck('total_profit', 'date');

        $chart_trend = [
            'labels' => [],
            'data' => [],
        ];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chart_trend['labels'][] = date('M d', strtotime($date));
            $chart_trend['data'][] = (float) ($trend_raw[$date] ?? 0);
        }

        return compact('stats', 'chart_distribution', 'chart_trend');
    }

    /**
     * Get chart data via AJAX
     */
    public function chartData(Request $request)
    {
        $interval = $request->get('interval', '7');
        if (str_ends_with($interval, 'd')) {
            $interval = (int) substr($interval, 0, -1);
        } else {
            $interval = (int) $interval;
        }
        $request->merge(['interval' => $interval]);

        $analytics = $this->getAnalyticsData($request);
        return response()->json($analytics['chart_trend']);
    }

    /**
     * Process the initiation of a copy trading.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'copy_trading_id' => 'required|exists:copy_tradings,id',
            'amount_type' => 'required|in:manual,percentage',
            'amount' => 'required_if:amount_type,manual|nullable|numeric|min:0',
        ]);

        $strategy = CopyTrading::findOrFail($request->copy_trading_id);
        $user = Auth::user();

        // Calculate amount if percentage mode
        $amount = $request->amount;
        if ($strategy->amount_type === 'percentage') {
            $amount = $user->balance * ($strategy->percentage / 100);

            if ($amount <= 0) {
                return response()->json(['success' => false, 'message' => __('Calculated trade amount is zero. Please fund your account.')], 400);
            }
        }

        if ($strategy->expires_at && $strategy->expires_at < now()->timestamp) {
            return response()->json(['success' => false, 'message' => __('This copy trade has expired.')], 400);
        }

        if ($user->balance < $amount) {
            return response()->json(['success' => false, 'message' => __('Insufficient balance.')], 400);
        }

        DB::beginTransaction();
        try {
            // Deduct balance
            $user->balance -= $amount;
            $user->save();

            $currency = getSetting('currency');
            $ref = \Str::orderedUuid();
            $description = __('Copy trading initiation');

            recordTransaction($user, $amount, $currency, $amount, $currency, 1, 'debit', 'completed', $ref, $description, $user->balance);

            $title = __('Copy Trade Started');
            $body = __('You have successfully started copy trade :code with :amount capital', [
                'code' => $strategy->code,
                'amount' => showAmount($amount)
            ]);
            recordNotificationMessage($user, $title, $body);

            // Create activation record
            CopyTradingHistory::create([
                'user_id' => $user->id,
                'copy_trading_id' => $strategy->id,
                'amount' => $amount,
                'pair' => $strategy->pair,
                'copy_code' => $strategy->code,
                'roi' => $strategy->roi,
                'status' => 'active',
                'activated_at' => now(),
                'completes_at' => $strategy->expires_at,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Copy trade started successfully!'),
                'redirect' => route('user.copy-trading.history')
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['success' => false, 'message' => __('Failed to start copy trade. Please try again.')], 500);
        }
    }
}
