<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use App\Models\ManagementTeam;
use App\Models\ClientReview;
use App\Models\TradingBot;
use App\Models\CopyTrading;
use App\Models\CopyTradingHistory;
use App\Services\LozandServices;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $page_title = __("Home");

        $template = config('site.template');
        $investmentPlans = InvestmentPlan::active()
            ->orderBy('min_investment')
            // ->limit(3)
            ->get();

        $path = public_path('assets/templates/' . $template . '/images/mockups/');
        $images = glob($path . '*.{png,jpg,jpeg}', GLOB_BRACE);
        $mockups = array_map(function ($image) use ($template) {
            return asset('assets/templates/' . $template . '/images/mockups/' . basename($image));
        }, $images);

        $lozand = new LozandServices();
        $stocks = $lozand->marketStocks();
        $marketStats = [];
        if ($stocks['status'] == 'success') {
            $marketStats = $stocks['data'];
        }

        $regulatoryCompliance = json_decode(getSetting('regulatory_compliance'), true);

        $aaple = [];
        $aapl_data_request = $lozand->ticker("AAPL");
        if ($aapl_data_request['status'] == 'success') {
            $aapl = $aapl_data_request['data'];
        }

        $btc = [];
        $btc_data_request = $lozand->futureTicker("BTCUSDT");
        if ($btc_data_request['status'] == 'success') {
            $btc = $btc_data_request['data'];
        }

        $sectors = config('sectors');

        // Bot Trading Data (Cache successful response for 7 days)
        $botData = \Illuminate\Support\Facades\Cache::get('trading_bot_data');
        if (!$botData) {
            try {
                $headers = [
                    'x-license-key' => safeDecrypt(config('site.product_key')),
                    'x-domain' => request()->getHost(),
                    'x-version' => config('site.version')
                ];
                $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->get('https://lozand.com/api/v1/bots/trading-pairs');
                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['data']) && !empty($json['data']['pairs'])) {
                        $botData = $json['data'];
                        \Illuminate\Support\Facades\Cache::put('trading_bot_data', $botData, 60 * 60 * 24 * 7);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Bot API Error: " . $e->getMessage());
            }
        }

        $botTrading = $botData ? [
            'exchanges' => $botData['exchanges'] ?? [],
            'crypto_markets' => $botData['pairs']['crypto'] ?? [],
            'forex_markets' => $botData['pairs']['forex'] ?? [],
        ] : [
            'exchanges' => [],
            'crypto_markets' => [],
            'forex_markets' => [],
        ];

        $management_team = ManagementTeam::get();
        $reviews = ClientReview::all();

        $featuredBots = TradingBot::active()->limit(3)->get();

        // Copy Trading Stats for Home Page
        $copyTradingStats = [
            'total_profit' => CopyTradingHistory::where('status', 'completed')->sum('profit'),
            'active_traders' => \App\Models\User::whereHas('copyTradingHistories', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'successful_trades' => CopyTradingHistory::where('status', 'completed')->where('profit', '>', 0)->count(),
        ];

        // Recent copy trading winners for home page
        $copyTradingWinners = CopyTradingHistory::with('copyTrading')
            ->where('status', 'completed')
            ->where('profit', '>', 0)
            ->latest('completed_at')
            ->limit(4)
            ->get();

        return view('templates.' . $template . '.blades.pages.index', compact(
            'investmentPlans',
            'mockups',
            'marketStats',
            'regulatoryCompliance',
            'sectors',
            'page_title',
            'aapl',
            'btc',
            'management_team',
            'reviews',
            'botTrading',
            'featuredBots',
            'copyTradingStats',
            'copyTradingWinners'
        ));
    }


    // Investment Plans Page
    public function investmentPlans()
    {
        // check if stocks module is enabled
        if (!moduleEnabled('investment_module')) {
            abort(403, __("Investment Plans are currently disabled check back later"));
        }

        $template = config('site.template');
        $investment_plans = InvestmentPlan::active()
            ->orderBy('min_investment')
            ->get();

        $page_title = __("Investment Plans");
        $page_description = __("Choose the best investment plan for you. We offer investments plans across stocks, crypto, forex, real estate and more.");

        $recommended_plans = $investment_plans->where('is_featured', 1);

        return view('templates.' . $template . '.blades.pages.investments-plans', compact(
            'investment_plans',
            'recommended_plans',
            'page_title',
            'page_description'
        ));
    }


    // Trading Bots (Dedicated Page)
    public function tradingBots()
    {
        // check if trading bot module is enabled
        if (!moduleEnabled('trading_bot_module')) {
            abort(404);
        }

        $template = config('site.template');
        $page_title = __("Trading Bots");
        $page_description = __("Automate your trading with our world-class, performance-driven bots. Choose your algorithm and start trading 24/7.");

        // Fetch all active bots
        $allBots = TradingBot::active()->get();
        $recommendedBots = TradingBot::active()->limit(3)->get();

        // Extract filters
        $botTypes = $allBots->pluck('type')->unique();
        $botMarkets = $allBots->pluck('traded_pairs')->flatten()->unique();

        // Bot Trading Data (Cache successful response for 7 days)
        $botData = \Illuminate\Support\Facades\Cache::get('trading_bot_data');
        if (!$botData) {
            try {
                $response = \Illuminate\Support\Facades\Http::get('https://lozand.com/api/v1/bots/trading-pairs');
                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['data']) && !empty($json['data']['pairs'])) {
                        $botData = $json['data'];
                        \Illuminate\Support\Facades\Cache::put('trading_bot_data', $botData, 60 * 60 * 24 * 7);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Bot API Error: " . $e->getMessage());
            }
        }

        $botTrading = $botData ? [
            'exchanges' => $botData['exchanges'] ?? [],
            'crypto_markets' => $botData['pairs']['crypto'] ?? [],
            'forex_markets' => $botData['pairs']['forex'] ?? [],
        ] : [
            'exchanges' => [],
            'crypto_markets' => [],
            'forex_markets' => [],
        ];

        return view('templates.' . $template . '.blades.pages.trading-bots', compact(
            'page_title',
            'page_description',
            'allBots',
            'recommendedBots',
            'botTypes',
            'botMarkets',
            'botTrading'
        ));
    }


    // Copy Trading (Dedicated Page)
    public function copyTrading()
    {
        // check if copy trading module is enabled
        if (!moduleEnabled('copy_trading_module')) {
            abort(404);
        }

        $template = config('site.template');
        $page_title = __("Copy Trading");
        $page_description = __("Mirror the success of elite algorithmic strategies. Transparent results, verified performance, and direct market execution.");

        // Statistics
        $totalProfit = CopyTradingHistory::where('status', 'completed')->sum('profit');
        $totalVolume = CopyTradingHistory::sum('amount');
        $successfulTrades = CopyTradingHistory::where('status', 'completed')->where('profit', '>', 0)->count();
        $totalTrades = CopyTradingHistory::where('status', 'completed')->count();
        $successRate = $totalTrades > 0 ? ($successfulTrades / $totalTrades) * 100 : 0;

        $activeTraders = \App\Models\User::whereHas('copyTradingHistories', function ($q) {
            $q->where('status', 'active');
        })->count();

        // Recent winning trades (social proof)
        $recentTrades = CopyTradingHistory::with('copyTrading')
            ->where('status', 'completed')
            ->where('profit', '>', 0)
            ->latest('completed_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_profit' => $totalProfit,
            'total_volume' => $totalVolume,
            'success_rate' => number_format($successRate, 1),
            'active_traders' => $activeTraders,
            'total_trades_count' => $totalTrades,
        ];

        return view('templates.' . $template . '.blades.pages.copy-trading', compact(
            'page_title',
            'page_description',
            'stats',
            'recentTrades'
        ));
    }
    public function aboutUs()
    {
        $template = config('site.template');
        $page_title = __("About Us");
        $page_description = __(":name is a leading financial services company that provides investment opportunities across stocks, crypto, forex, real estate and more.", ['name' => getSetting('name')]);
        return view('templates.' . $template . '.blades.pages.about', compact(
            'page_title',
            'page_description'
        ));
    }

    // License
    public function license()
    {
        $template = config('site.template');
        $page_title = __("Regulatory Compliance");
        $regulatoryCompliance = json_decode(getSetting('regulatory_compliance'), true);
        return view('templates.' . $template . '.blades.pages.license', compact(
            'page_title',
            'regulatoryCompliance'
        ));
    }

    // Contact
    public function contact()
    {
        $template = config('site.template');
        $page_title = __("Contact Us");
        $page_description = __(":name Support is available 24/7 to assist you with any questions or concerns.", ['name' => getSetting('name')]);
        return view('templates.' . $template . '.blades.pages.contact', compact(
            'page_title',
            'page_description'
        ));
    }

    public function contactSend(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100|regex:/^[a-zA-Z\s\.]+$/',
            'email' => 'required|email:rfc,dns|max:255',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:3000',
        ];

        if (getSetting('google_recaptcha') == 'enabled') {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $request->validate($rules, [
            'name.regex' => __('Name contains invalid characters.'),
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'g-recaptcha-response.captcha' => __('Captcha error! try again later or contact site admin.'),
        ]);

        // Sanitization to prevent XSS and injection when rendered in blade/emails
        $name = strip_tags($request->name);
        $subject = strip_tags($request->subject);
        $message = strip_tags($request->message);

        //send email
        try {
            $adminEmail = getSetting('email');
            if ($adminEmail) {
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\ContactEmail(
                    $name,
                    $request->email,
                    $message,
                    $subject
                ));
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'action' => 'reset'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Your message has been dispatched to our support team. We will contact you shortly.'),
            'action' => 'reset'
        ]);
    }


    // privacy policy
    public function privacyPolicy()
    {
        $template = config('site.template');
        $page_title = __("Privacy Policy");
        $page_description = __(":name is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.", ['name' => getSetting('name')]);
        return view('templates.' . $template . '.blades.pages.privacy-policy', compact(
            'page_title',
            'page_description'
        ));
    }

    // terms and conditions
    public function termsAndConditions()
    {
        $template = config('site.template');
        $page_title = __("Terms and Conditions");
        $page_description = __("Our Terms and Conditions outline the rules and guidelines for using our website and services. By accessing or using our website, you agree to be bound by these terms.", ['name' => getSetting('name')]);
        return view('templates.' . $template . '.blades.pages.terms-and-conditions', compact(
            'page_title',
            'page_description'
        ));
    }

    // risk disclosure
    public function riskDisclosure()
    {
        $template = config('site.template');
        $page_title = __("Risk Disclosure");
        $page_description = __("Our Risk Disclosure outlines the risks associated with investing in our platform. By investing, you acknowledge and accept these risks.", ['name' => getSetting('name')]);
        return view('templates.' . $template . '.blades.pages.risk-disclosure', compact(
            'page_title',
            'page_description'
        ));
    }

}
