<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\TradingBot;
use App\Models\TradingBotActivation;
use App\Models\TradingBotLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TradingBotController extends Controller
{
    /**
     * Display a listing of the trading bots.
     */
    public function index()
    {
        $page_title = __('Global Trading Bots');
        $bots = TradingBot::latest()->paginate(getSetting('pagination', 15));

        return view('templates.' . config('site.template') . '.blades.admin.trading.bots.index', compact('page_title', 'bots'));
    }

    /**
     * Show the form for creating a new trading bot.
     */
    public function create()
    {
        $page_title = __('Create Trading Bot');
        $data = $this->getTradingData();

        return view('templates.' . config('site.template') . '.blades.admin.trading.bots.create', compact('page_title', 'data'));
    }

    /**
     * Store a newly created trading bot in storage.
     */
    public function store(Request $request)
    {
        // Merge pairs before validation
        $pairs = $request->type === 'crypto' ? $request->traded_pairs_crypto : $request->traded_pairs_forex;
        $request->merge(['traded_pairs' => $pairs]);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'type' => 'required|in:crypto,forex',
            'exchanges' => 'required_if:type,crypto|array',
            'traded_pairs' => 'required|array|min:1',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'daily_return_min' => 'required|numeric|min:0',
            'daily_return_max' => 'required|numeric|gte:daily_return_min',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:hour,day,week,month,year',
            'trading_days' => 'required|array|min:1',
            'is_active' => 'required|boolean',
            'is_capital_returned' => 'required|boolean',
        ]);

        $bot = new TradingBot($request->except(['logo', 'traded_pairs_crypto', 'traded_pairs_forex']));
        $bot->traded_pairs = $request->traded_pairs;

        if ($request->hasFile('logo')) {
            $path = 'assets/images/bots/';
            $filename = time() . '.' . $request->logo->extension();
            $request->logo->move(public_path($path), $filename);
            $bot->logo = $filename;
        }

        if ($request->type === 'forex') {
            $bot->exchanges = null;
        }

        $bot->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Trading bot created successfully.')
            ]);
        }

        return redirect()->route('admin.trading-bots.index')->with('success', __('Trading bot created successfully.'));
    }

    /**
     * Show the form for editing the specified trading bot.
     */
    public function edit($id)
    {
        $bot = TradingBot::findOrFail($id);
        $page_title = __('Edit') . ' ' . $bot->name;
        $data = $this->getTradingData();

        return view('templates.' . config('site.template') . '.blades.admin.trading.bots.edit', compact('page_title', 'bot', 'data'));
    }

    /**
     * Update the specified trading bot in storage.
     */
    public function update(Request $request, $id)
    {
        $bot = TradingBot::findOrFail($id);

        // Merge pairs before validation
        $pairs = $request->type === 'crypto' ? $request->traded_pairs_crypto : $request->traded_pairs_forex;
        $request->merge(['traded_pairs' => $pairs]);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'type' => 'required|in:crypto,forex',
            'exchanges' => 'required_if:type,crypto|array',
            'traded_pairs' => 'required|array|min:1',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'daily_return_min' => 'required|numeric|min:0',
            'daily_return_max' => 'required|numeric|gte:daily_return_min',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:hour,day,week,month,year',
            'trading_days' => 'required|array|min:1',
            'is_active' => 'required|boolean',
            'is_capital_returned' => 'required|boolean',
        ]);

        $bot->fill($request->except(['logo', 'traded_pairs_crypto', 'traded_pairs_forex']));
        $bot->traded_pairs = $request->traded_pairs;

        if ($request->hasFile('logo')) {
            $path = 'assets/images/bots/';
            $filename = time() . '.' . $request->logo->extension();
            $request->logo->move(public_path($path), $filename);
            $bot->logo = $filename;
        }

        if ($request->type === 'forex') {
            $bot->exchanges = null;
        }

        $bot->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Trading bot updated successfully.')
            ]);
        }

        return redirect()->route('admin.trading-bots.index')->with('success', __('Trading bot updated successfully.'));
    }

    /**
     * Remove the specified trading bot from storage.
     */
    public function destroy($id)
    {
        $bot = TradingBot::findOrFail($id);
        $bot->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Trading bot deleted successfully.')
            ]);
        }

        return redirect()->route('admin.trading-bots.index')->with('success', __('Trading bot deleted successfully.'));
    }

    /**
     * Display a listing of all trading bot activations.
     */
    public function activations(Request $request)
    {
        $page_title = __('User Bot Activations');

        // Base Query
        $query = TradingBotActivation::with(['bot', 'user']);

        // Analytics Data
        $analytics = $this->getBotAnalyticsData($request);
        $stats = $analytics['stats'];
        $chart_distribution = $analytics['chart_distribution'];
        $chart_trend = $analytics['chart_trend'];

        // Filters
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('username', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%");
                })->orWhereHas('bot', function ($bq) use ($term) {
                    $bq->where('name', 'like', "%$term%");
                });
            });
        }

        // Export Handling
        if ($request->has('export')) {
            $exportType = $request->export;
            $exportActivations = (clone $query)->latest()->get();
            $template = config('site.template');

            // Dynamic Column Selection
            $requestedCols = $request->get('columns');
            if (is_string($requestedCols)) {
                $requestedCols = array_map('trim', explode(',', $requestedCols));
            } else {
                $requestedCols = (array) ($requestedCols ?? ['username', 'bot_name', 'amount', 'returned_profit', 'status', 'start_date']);
            }

            // Header whitelist and mapping
            $columnMap = [
                'username' => 'User',
                'bot_name' => 'Strategy',
                'amount' => 'Capital',
                'returned_profit' => 'Profit',
                'status' => 'Status',
                'start_date' => 'Started At',
                'end_date' => 'Ends At',
            ];

            // Filter columns based on whitelist
            $selectedCols = [];
            foreach ($requestedCols as $col) {
                $col = trim($col);
                if (array_key_exists($col, $columnMap)) {
                    $selectedCols[$col] = $columnMap[$col];
                }
            }

            if ($exportType == 'pdf') {
                $orientation = count($selectedCols) <= 8 ? 'portrait' : 'landscape';
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView("templates.$template.blades.admin.pdf.bot_activations", [
                    'activations' => $exportActivations,
                    'page_title' => $page_title,
                    'columns' => $selectedCols,
                    'orientation' => $orientation
                ]);
                return $pdf->download('bot-activations-report-' . now()->format('Y-m-d-H-i-s') . '.pdf');
            }

            if ($exportType == 'sql') {
                $headers = [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="bot-activations-dump-' . now()->format('Y-m-d-H-i-s') . '.sql"',
                ];

                $callback = function () use ($exportActivations) {
                    $file = fopen('php://output', 'w');
                    fwrite($file, "-- Trading Bot Activations Table Dump\n\n");
                    foreach ($exportActivations as $activation) {
                        $attributes = is_object($activation) && method_exists($activation, 'toArray') ? $activation->toArray() : (array) $activation;
                        // Filter out relations for SQL dump
                        $attributes = array_filter($attributes, fn($v) => !is_array($v) && !is_object($v));
                        $columns = array_keys($attributes);
                        $values = array_map(function ($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes((string) $value) . "'";
                        }, array_values($attributes));

                        $sql = "INSERT INTO trading_bot_activations (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($file, $sql);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }

            if ($exportType == 'csv') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=bot-activations-" . now()->format('Y-m-d-H-i-s') . ".csv",
                ];

                $callback = function () use ($exportActivations, $selectedCols) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, array_values($selectedCols));

                    foreach ($exportActivations as $activation) {
                        $row = [];
                        foreach (array_keys($selectedCols) as $key) {
                            switch ($key) {
                                case 'username':
                                    $row[] = $activation->user->username;
                                    break;
                                case 'bot_name':
                                    $row[] = $activation->bot->name;
                                    break;
                                case 'status':
                                    $row[] = ucfirst($activation->status);
                                    break;
                                case 'start_date':
                                    $row[] = date('Y-m-d H:i:s', $activation->start_date);
                                    break;
                                case 'end_date':
                                    $row[] = $activation->end_date ? date('Y-m-d H:i:s', $activation->end_date) : 'N/A';
                                    break;
                                default:
                                    $row[] = $activation->$key ?? '';
                                    break;
                            }
                        }
                        fputcsv($file, $row);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }
        }

        $activations = $query->latest()->paginate(getSetting('pagination', 15));

        return view('templates.' . config('site.template') . '.blades.admin.trading.bots.activations', compact('page_title', 'activations', 'stats', 'chart_distribution', 'chart_trend'));
    }

    /**
     * Update the status of a trading bot activation.
     */
    public function updateActivationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,completed',
        ]);

        $activation = TradingBotActivation::findOrFail($id);
        $activation->status = $request->status;
        $activation->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Activation status updated successfully.')
            ]);
        }

        return back()->with('success', __('Activation status updated successfully.'));
    }

    /**
     * Remove a trading bot activation.
     */
    public function deleteActivation($id)
    {
        $activation = TradingBotActivation::findOrFail($id);
        $activation->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Activation deleted successfully.')
            ]);
        }

        return back()->with('success', __('Activation deleted successfully.'));
    }

    /**
     * Display a listing of all trading bot logs.
     */
    public function logs(Request $request)
    {
        $page_title = __('Trading Bot Logs');

        $query = TradingBotLog::with(['user', 'activation.bot']);

        // Analytics Data
        $analytics = $this->getBotAnalyticsData($request, 'logs');
        $stats = $analytics['stats'];
        $chart_distribution = $analytics['chart_distribution'];
        $chart_trend = $analytics['chart_trend'];

        // Filters
        if ($request->has('search') && $request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('username', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%");
                })->orWhere('trading_pair', 'like', "%$term%")
                    ->orWhere('exchange', 'like', "%$term%");
            });
        }

        // Export Handling
        if ($request->has('export')) {
            $exportType = $request->export;
            $exportLogs = (clone $query)->latest()->get();
            $template = config('site.template');

            $requestedCols = $request->get('columns');
            if (is_string($requestedCols)) {
                $requestedCols = array_map('trim', explode(',', $requestedCols));
            } else {
                $requestedCols = (array) ($requestedCols ?? ['username', 'bot_name', 'trading_pair', 'amount', 'profit', 'created_at']);
            }

            $columnMap = [
                'username' => 'User',
                'bot_name' => 'Strategy',
                'trading_pair' => 'Pair',
                'exchange' => 'Exchange',
                'amount' => 'Amount',
                'profit' => 'Profit',
                'profit_percentage' => 'ROI %',
                'exit_time' => 'Exit Time',
                'created_at' => 'Execution Date',
            ];

            $selectedCols = [];
            foreach ($requestedCols as $col) {
                if (array_key_exists($col, $columnMap)) {
                    $selectedCols[$col] = $columnMap[$col];
                }
            }

            if ($exportType == 'pdf') {
                $orientation = count($selectedCols) <= 8 ? 'portrait' : 'landscape';
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView("templates.$template.blades.admin.pdf.bot_logs", [
                    'logs' => $exportLogs,
                    'page_title' => $page_title,
                    'columns' => $selectedCols,
                    'orientation' => $orientation
                ]);
                return $pdf->download('bot-logs-report-' . now()->format('Y-m-d-H-i-s') . '.pdf');
            }

            if ($exportType == 'sql') {
                $headers = [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="bot-logs-dump-' . now()->format('Y-m-d-H-i-s') . '.sql"',
                ];

                $callback = function () use ($exportLogs) {
                    $file = fopen('php://output', 'w');
                    fwrite($file, "-- Trading Bot Logs Table Dump\n\n");
                    foreach ($exportLogs as $log) {
                        $attributes = is_object($log) && method_exists($log, 'toArray') ? $log->toArray() : (array) $log;
                        // Filter out relations for SQL dump
                        $attributes = array_filter($attributes, fn($v) => !is_array($v) && !is_object($v));
                        $columns = array_keys($attributes);
                        $values = array_map(function ($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes((string) $value) . "'";
                        }, array_values($attributes));

                        $sql = "INSERT INTO trading_bot_logs (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($file, $sql);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }

            if ($exportType == 'csv') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=bot-logs-" . now()->format('Y-m-d-H-i-s') . ".csv",
                ];

                $callback = function () use ($exportLogs, $selectedCols) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, array_values($selectedCols));

                    foreach ($exportLogs as $log) {
                        $row = [];
                        foreach (array_keys($selectedCols) as $key) {
                            switch ($key) {
                                case 'username':
                                    $row[] = $log->user->username;
                                    break;
                                case 'bot_name':
                                    $row[] = $log->activation->bot->name ?? 'N/A';
                                    break;
                                case 'created_at':
                                    $row[] = $log->created_at->format('Y-m-d H:i:s');
                                    break;
                                case 'exit_time':
                                    $row[] = date('Y-m-d H:i:s', $log->exit_time);
                                    break;
                                default:
                                    $row[] = $log->$key ?? '';
                                    break;
                            }
                        }
                        fputcsv($file, $row);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }
        }

        $logs = $query->latest()->paginate(getSetting('pagination', 15));

        return view('templates.' . config('site.template') . '.blades.admin.trading.bots.logs', compact('page_title', 'logs', 'stats', 'chart_distribution', 'chart_trend'));
    }

    /**
     * Get shared analytics data for bot activtions and logs
     */
    private function getBotAnalyticsData(Request $request, $type = 'activations')
    {
        if ($type === 'logs') {
            // Logs Specific Stats
            $total_logs = TradingBotLog::count();
            $success_logs = TradingBotLog::where('profit', '>', 0)->count();
            $win_rate = $total_logs > 0 ? ($success_logs / $total_logs) * 100 : 0;

            $stats = [
                'total_trades' => $total_logs,
                'total_volume' => (float) TradingBotLog::sum('amount'),
                'total_profit' => (float) TradingBotLog::sum('profit'),
                'win_rate' => number_format($win_rate, 1) . '%',
            ];

            // Distribution: Profit by Strategy
            $distribution = TradingBotLog::select('trading_bots.name as bot_name', DB::raw('sum(trading_bot_logs.profit) as total_profit'))
                ->join('trading_bot_activations', 'trading_bot_logs.trading_bot_activation_id', '=', 'trading_bot_activations.id')
                ->join('trading_bots', 'trading_bot_activations.trading_bot_id', '=', 'trading_bots.id')
                ->groupBy('trading_bots.id', 'trading_bots.name')
                ->get();

            $chart_distribution = [
                'labels' => $distribution->pluck('bot_name'),
                'series' => $distribution->map(fn($d) => (float) $d->total_profit),
            ];
        } else {
            // Activations Specific Stats
            $stats = [
                'total_active' => TradingBotActivation::where('status', 'active')->count(),
                'total_capital' => (float) TradingBotActivation::sum('amount'),
                'total_profit' => (float) TradingBotLog::sum('profit'),
                'total_activations' => TradingBotActivation::count(),
            ];

            // Distribution: Capital by Strategy
            $distribution = TradingBotActivation::select('trading_bot_id', DB::raw('sum(amount) as total_capital'))
                ->groupBy('trading_bot_id')
                ->with('bot')
                ->get();

            $chart_distribution = [
                'labels' => $distribution->map(fn($d) => $d->bot->name ?? 'N/A'),
                'series' => $distribution->map(fn($d) => (float) $d->total_capital),
            ];
        }

        // Shared Trend (Profit over time)
        $days = (int) $request->get('chart_days', 7);
        $startDate = now()->subDays($days)->startOfDay();

        $trend_raw = TradingBotLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('sum(profit) as total_profit')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->pluck('total_profit', 'date');

        $chart_trend = [
            'labels' => [],
            'series' => [],
        ];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chart_trend['labels'][] = date('M d', strtotime($date));
            $chart_trend['series'][] = (float) ($trend_raw[$date] ?? 0);
        }

        return compact('stats', 'chart_distribution', 'chart_trend');
    }

    /**
     * Get chart data via AJAX
     */
    public function chartData(Request $request)
    {
        $analytics = $this->getBotAnalyticsData($request, $request->get('type', 'activations'));
        return response()->json($analytics['chart_trend']);
    }

    /**
     * Internal method to fetch trading data (pairs and exchanges)
     */
    private function getTradingData()
    {
        try {
            $url = 'https://lozand.com/api/v1/bots/trading-pairs';
            $headers = [
                'x-license-key' => safeDecrypt(config('site.product_key')),
                'x-domain' => request()->getHost(),
                'x-version' => config('site.version')
            ];
            $response = Http::withHeaders($headers)->get($url);
            if ($response->failed()) {
                Log::error('API Request failed: ' . $response->status());
                return [
                    'pairs' => ['crypto' => [], 'forex' => []],
                    'exchanges' => []
                ];
            }
            $apiData = $response->json();
            return [
                'pairs' => $apiData['data']['pairs'] ?? ['crypto' => [], 'forex' => []],
                'exchanges' => $apiData['data']['exchanges'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch trading data: ' . $e->getMessage());
            return [
                'pairs' => ['crypto' => [], 'forex' => []],
                'exchanges' => []
            ];
        }
    }
}
