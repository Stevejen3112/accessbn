<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\CopyTrading;
use App\Models\CopyTradingHistory;
use App\Services\LozandServices;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CopyTradingController extends Controller
{
    /**
     * Display a listing of copy trading strategies.
     */
    public function index()
    {
        $page_title = __('Copy Trading Strategies');
        $strategies = CopyTrading::latest()->paginate(getSetting('pagination', 15));

        return view('templates.' . config('site.template') . '.blades.admin.trading.copy.index', compact('page_title', 'strategies'));
    }

    /**
     * Show the form for creating a new copy trading.
     */
    public function create()
    {
        $page_title = __('New Copy Trade');

        $lozandServices = new LozandServices();
        $tickersResponse = $lozandServices->futureTickers();
        $tickers = [];
        $tickerError = null;

        if ($tickersResponse && $tickersResponse['status'] === 'success') {
            $tickers = $tickersResponse['data'];
        } else {
            $tickerError = $tickersResponse['message'] ?? __('Failed to fetch market data.');
        }

        return view('templates.' . config('site.template') . '.blades.admin.trading.copy.create', compact('page_title', 'tickers', 'tickerError'));
    }

    /**
     * Store a newly created copy trading in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'roi' => 'required|numeric|min:0',
            'pair' => 'required|string',
            'amount_type' => 'required|in:manual,percentage',
            'percentage' => 'required_if:amount_type,percentage|nullable|numeric|min:0|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Generate unique code: XDC-XXXX (4 random uppercase letters/numbers)
        $code = strtoupper(Str::random(6));
        while (CopyTrading::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(6));
        }

        $expires_at = $request->expires_at ? strtotime($request->expires_at) : null;

        $strategy = CopyTrading::create([
            'code' => $code,
            'pair' => $request->pair,
            'roi' => $request->roi,
            'amount_type' => $request->amount_type,
            'percentage' => $request->amount_type === 'percentage' ? $request->percentage : null,
            'expires_at' => $expires_at,
        ]);

        $messages = $this->generateSignalMessages($strategy);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Copy trade created successfully.'),
                'data' => [
                    'strategy' => $strategy,
                    'messages' => $messages
                ]
            ]);
        }

        return redirect()->route('admin.copy-trading.index')->with('success', __('Copy trade created successfully.'));
    }

    /**
     * Generate signal messages in multiple languages.
     */
    private function generateSignalMessages($strategy)
    {
        $timezone = config('app.timezone', 'UTC');
        $siteName = config('app.name', 'XDCBIT');
        $expiry = $strategy->expires_at
            ? \Carbon\Carbon::createFromTimestamp($strategy->expires_at, $timezone)->format('M d, Y H:i')
            : __('Never');

        // Get enabled languages
        $locales = config('languages');
        $messages = [];

        foreach ($locales as $code => $lang) {
            // We use the language code (en, fr, etc.) as the locale for translation
            $messages[$code] = __("🚀 COPY TRADE SIGNAL 🚀\n\n📱 Platform: :site_name\n💎 Pair: :pair\n🔑 Trading Code: :code\n⏰ Expires at: :time (:timezone)\n\n✅ Copy the code above and open :site_name to execute this trade!", [
                'site_name' => $siteName,
                'pair' => $strategy->pair,
                'code' => $strategy->code,
                'time' => $expiry,
                'timezone' => $timezone
            ], $code);
        }

        return $messages;
    }

    /**
     * Show the form for editing the specified copy trading.
     */
    public function edit($id)
    {
        $strategy = CopyTrading::findOrFail($id);
        $page_title = __('Edit Strategy') . ': ' . $strategy->code;

        $lozandServices = new LozandServices();
        $tickersResponse = $lozandServices->futureTickers();
        $tickers = [];
        $tickerError = null;

        if ($tickersResponse['status'] === 'success') {
            $tickers = $tickersResponse['data'];
        } else {
            $tickerError = $tickersResponse['message'];
        }

        return view('templates.' . config('site.template') . '.blades.admin.trading.copy.edit', compact('page_title', 'strategy', 'tickers', 'tickerError'));
    }

    /**
     * Update the specified copy trading in storage.
     */
    public function update(Request $request, $id)
    {
        $strategy = CopyTrading::findOrFail($id);

        $request->validate([
            'roi' => 'required|numeric|min:0',
            'pair' => 'required|string',
            'amount_type' => 'required|in:manual,percentage',
            'percentage' => 'required_if:amount_type,percentage|nullable|numeric|min:0|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $expires_at = $request->expires_at ? strtotime($request->expires_at) : null;

        $strategy->update([
            'pair' => $request->pair,
            'roi' => $request->roi,
            'amount_type' => $request->amount_type,
            'percentage' => $request->amount_type === 'percentage' ? $request->percentage : null,
            'expires_at' => $expires_at,
        ]);

        $messages = $this->generateSignalMessages($strategy);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Copy trade updated successfully.'),
                'data' => [
                    'strategy' => $strategy,
                    'messages' => $messages
                ]
            ]);
        }

        return redirect()->route('admin.copy-trading.index')->with('success', __('Copy trade updated successfully.'));
    }

    /**
     * Get signal messages for a strategy.
     */
    public function getSignalMessages($id)
    {
        $strategy = CopyTrading::findOrFail($id);
        $messages = $this->generateSignalMessages($strategy);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages
            ]
        ]);
    }

    /**
     * Remove the specified copy trading from storage.
     */
    public function destroy($id)
    {
        $strategy = CopyTrading::findOrFail($id);
        $strategy->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Copy trading strategy deleted successfully.')
            ]);
        }

        return redirect()->route('admin.copy-trading.index')->with('success', __('Copy trade deleted successfully.'));
    }

    /**
     * Display a listing of copy trading activations (History).
     */
    /**
     * Display a listing of copy trading activations (History).
     */
    public function history(Request $request)
    {
        $page_title = __('Copy Trading History');

        // Base Query
        $query = CopyTradingHistory::with('user');

        // Search & Filter
        if ($request->has('search') && $request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('copy_code', 'like', "%$term%")
                    ->orWhere('pair', 'like', "%$term%")
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('username', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%");
                    });
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
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
                $requestedCols = (array) ($requestedCols ?? ['username', 'copy_code', 'pair', 'amount', 'profit', 'roi', 'status', 'activated_at']);
            }

            // Header whitelist and mapping
            $columnMap = [
                'username' => 'User',
                'copy_code' => 'Copy Code',
                'pair' => 'Trading Pair',
                'amount' => 'Capital',
                'profit' => 'Profit',
                'roi' => 'ROI',
                'status' => 'Status',
                'activated_at' => 'Activated At',
                'completed_at' => 'Completed At',
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
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView("templates.$template.blades.admin.pdf.copy_trading_history", [
                    'activations' => $exportActivations,
                    'page_title' => $page_title,
                    'columns' => $selectedCols,
                    'orientation' => $orientation
                ]);
                return $pdf->download('copy-trading-history-' . now()->format('Y-m-d-H-i-s') . '.pdf');
            }

            if ($exportType == 'sql') {
                $headers = [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="copy-trading-dump-' . now()->format('Y-m-d-H-i-s') . '.sql"',
                ];

                $callback = function () use ($exportActivations) {
                    $file = fopen('php://output', 'w');
                    fwrite($file, "-- Copy Trading History Table Dump\n\n");
                    foreach ($exportActivations as $activation) {
                        $attributes = is_object($activation) && method_exists($activation, 'toArray') ? $activation->toArray() : (array) $activation;
                        $attributes = array_filter($attributes, fn($v) => !is_array($v) && !is_object($v));
                        $columns = array_keys($attributes);
                        $values = array_map(function ($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes((string) $value) . "'";
                        }, array_values($attributes));

                        $sql = "INSERT INTO copy_trading_histories (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($file, $sql);
                    }
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }

            if ($exportType == 'csv') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=copy-trading-history-" . now()->format('Y-m-d-H-i-s') . ".csv",
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
                                case 'status':
                                    $row[] = $activation->status === 'active' ? 'Running' : ucfirst($activation->status);
                                    break;
                                case 'profit':
                                    $row[] = $activation->status === 'active' ? '--' : $activation->profit;
                                    break;
                                case 'roi':
                                    $row[] = $activation->status === 'active' ? '--' : $activation->roi . '%';
                                    break;
                                case 'activated_at':
                                    $row[] = $activation->activated_at->format('Y-m-d H:i:s');
                                    break;
                                case 'completed_at':
                                    $row[] = $activation->completed_at ? $activation->completed_at->format('Y-m-d H:i:s') : 'N/A';
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

        // Analytics Data
        $analytics = $this->getAnalyticsData($request);
        $stats = $analytics['stats'];
        $chart_distribution = $analytics['chart_distribution'];
        $chart_trend = $analytics['chart_trend'];

        return view('templates.' . config('site.template') . '.blades.admin.trading.copy.history', compact('page_title', 'activations', 'stats', 'chart_distribution', 'chart_trend'));
    }

    /**
     * Get shared analytics data for copy trading
     */
    private function getAnalyticsData(Request $request)
    {
        $stats = [
            'total_active' => CopyTradingHistory::where('status', 'active')->count(),
            'total_capital' => (float) CopyTradingHistory::sum('amount'),
            'total_profit' => (float) CopyTradingHistory::sum('profit'),
            'total_trades' => CopyTradingHistory::count(),
        ];

        // Distribution: Capital by Pair
        $distribution = CopyTradingHistory::select('pair', \Illuminate\Support\Facades\DB::raw('sum(amount) as total_capital'))
            ->groupBy('pair')
            ->get();

        $chart_distribution = [
            'labels' => $distribution->pluck('pair'),
            'data' => $distribution->map(fn($d) => (float) $d->total_capital),
        ];

        // Profit Trend
        $days = (int) $request->get('interval', 7);
        $startDate = now()->subDays($days)->startOfDay();

        $trend_raw = CopyTradingHistory::select(
            \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
            \Illuminate\Support\Facades\DB::raw('sum(profit) as total_profit')
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
        // Accept 7d, 30d, 90d format and convert to integer
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
     * Remove a specific copy trading history record from storage.
     */
    public function destroyHistory($id)
    {
        try {
            $history = CopyTradingHistory::findOrFail($id);
            $history->delete();

            return response()->json([
                'success' => true,
                'message' => __('Copy trading history deleted successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete copy trading history.')
            ], 500);
        }
    }
}
