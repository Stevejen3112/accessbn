@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
@endpush
@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <div class="space-y-8 pb-20">
        {{-- Header Section --}}
        <div class="relative overflow-hidden bg-secondary border border-white/5 rounded-3xl p-8 sm:p-12">
            <div class="relative z-10 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-4">
                    {{ __('Trading') }} <span class="text-accent-primary">{{ __('Performance') }}</span>
                </h1>
                <p class="text-text-secondary text-base leading-relaxed">
                    {{ __('Comprehensive overview of your AI trading bot analytics, profitability trends, and execution history.') }}
                </p>
            </div>
            {{-- Decorative Background --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-accent-primary/10 rounded-full blur-[100px]"></div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Profit --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Total Profit') }}</div>
                    <div class="text-3xl font-black text-emerald-400 italic tracking-tighter">
                        +{{ showAmount($stats['total_profit']) }}</div>
                </div>
                <div
                    class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all">
                </div>
            </div>

            {{-- Today's Profit --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Today\'s Profit') }}</div>
                    <div class="text-3xl font-black text-white italic tracking-tighter">
                        +{{ showAmount($stats['today_profit']) }}</div>
                </div>
                <div
                    class="absolute -right-4 -bottom-4 w-20 h-20 bg-accent-primary/5 rounded-full blur-2xl group-hover:bg-accent-primary/10 transition-all">
                </div>
            </div>

            {{-- Active Bots --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Active Bots') }}</div>
                    <div class="text-3xl font-black text-white italic tracking-tighter">{{ $stats['active_bots'] }}</div>
                </div>
                <div
                    class="absolute -right-4 -bottom-4 w-20 h-20 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-all">
                </div>
            </div>

            {{-- Total Trades --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Total Trades') }}</div>
                    <div class="text-3xl font-black text-white italic tracking-tighter">
                        {{ number_format($stats['total_trades']) }}</div>
                </div>
                <div
                    class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-500/5 rounded-full blur-2xl group-hover:bg-purple-500/10 transition-all">
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Earnings Trend (Main Chart) --}}
            <div class="lg:col-span-2 bg-secondary border border-white/5 rounded-[32px] p-8 shadow-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h3 class="text-lg font-black text-white tracking-tight">{{ __('Earnings Trend') }}</h3>
                        <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mt-1">
                            {{ __('Daily Profit performance') }}</p>
                    </div>
                    <div class="flex gap-2 bg-white/5 p-1.5 rounded-2xl border border-white/10">
                        <button
                            class="cursor-pointer interval-btn px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-accent-primary text-white shadow-lg shadow-accent-primary/20"
                            data-interval="7d">7D</button>
                        <button
                            class="cursor-pointer interval-btn px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all hover:bg-white/5 text-text-secondary"
                            data-interval="30d">1M</button>
                        <button
                            class="cursor-pointer interval-btn px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all hover:bg-white/5 text-text-secondary"
                            data-interval="90d">3M</button>
                    </div>
                </div>
                <div id="chart-earnings-trend-container" class="relative min-h-[350px]">
                    <div id="chart-trend-loading"
                        class="absolute inset-0 z-20 bg-secondary/60 backdrop-blur-sm flex items-center justify-center rounded-[32px] opacity-0 pointer-events-none transition-opacity">
                        <div class="flex flex-col items-center gap-4">
                            <div
                                class="w-10 h-10 border-4 border-accent-primary/20 border-t-accent-primary rounded-full animate-spin">
                            </div>
                            <span
                                class="text-[10px] font-black text-white uppercase tracking-widest">{{ __('Updating Trend...') }}</span>
                        </div>
                    </div>
                    <div id="chart-earnings-trend"></div>
                </div>
            </div>

            {{-- Asset Distribution --}}
            <div class="bg-secondary border border-white/5 rounded-[32px] p-8 shadow-2xl">
                <h3 class="text-lg font-black text-white tracking-tight mb-1">{{ __('Profit Distribution') }}</h3>
                <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-8">
                    {{ __('Earnings by Trading Pair') }}</p>
                <div id="chart-asset-distribution" class="min-h-[350px]"></div>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="bg-secondary border border-white/5 rounded-[32px] overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">{{ __('Trade Execution History') }}</h3>
                    <p class="text-xs text-text-secondary font-medium mt-1">
                        {{ __('Real-time log of all bot executions and profit distributions.') }}</p>
                </div>

                {{-- Search Input --}}
                <div class="relative w-full md:w-80">
                    <input type="text" id="log-search"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-3.5 text-base text-white focus:border-accent-primary/50 focus:ring-0 transition-all placeholder:text-text-secondary/50"
                        placeholder="{{ __('Search by pair, exchange...') }}">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-5 h-5 text-text-secondary/30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div id="logs-container" class="relative p-8">
                <div id="table-loading"
                    class="absolute inset-0 z-20 bg-secondary/60 backdrop-blur-sm flex items-center justify-center opacity-0 pointer-events-none transition-opacity">
                    <div class="flex flex-col items-center gap-4">
                        <div
                            class="w-10 h-10 border-4 border-accent-primary/20 border-t-accent-primary rounded-full animate-spin">
                        </div>
                        <span
                            class="text-[10px] font-black text-white uppercase tracking-widest">{{ __('Updating Logs...') }}</span>
                    </div>
                </div>
                <div id="logs-table-container">
                    @include(
                        'templates.' . config('site.template') . '.blades.user.trading.bots.partials.logs_table',
                        ['logs' => $logs]
                    )
                </div>
            </div>
        </div>
    </div>

    {{-- PNL Modal Redesign --}}
    <div id="pnl-modal"
        class="fixed inset-0 z-[100] hidden overflow-y-auto overflow-x-hidden backdrop-blur-2xl bg-black/80 transition-all duration-500 cursor-pointer">
        <div class="flex min-h-full items-center justify-center p-6">
            <div id="pnl-card"
                class="relative w-full max-w-[440px] bg-[#0b0e11] rounded-[48px] overflow-hidden shadow-[0_0_100px_-20px_rgba(0,0,0,1)] border border-white/5 opacity-0 scale-90 transition-all duration-700 cursor-default group/card">

                {{-- Decorative Background Elements --}}
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div id="modal-glow-top"
                        class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] transition-colors duration-700">
                    </div>
                    <div id="modal-glow-bottom"
                        class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-500/5 rounded-full blur-[100px] transition-colors duration-700">
                    </div>
                    <div class="absolute inset-0 opacity-[0.03] mix-blend-overlay"
                        style="background-image: url('{{ asset('assets/images/noise.svg') }}')">
                    </div>
                </div>

                {{-- Close Button --}}
                <button id="close-pnl"
                    class="absolute top-8 right-8 z-30 w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-text-secondary hover:text-white transition-all hover:bg-white/10 hover:rotate-90 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Header: Bot Branding & Platform --}}
                <div class="relative p-12 pb-0">
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-4">
                            <div id="modal-bot-icon"
                                class="w-14 h-14 rounded-2xl bg-accent-primary/20 flex items-center justify-center border border-accent-primary/30 overflow-hidden shadow-2xl">
                                <img id="modal-bot-img" src="" class="w-full h-full object-cover hidden"
                                    alt="Bot">
                                <span class="text-xl font-black text-accent-primary uppercase tracking-tighter">BT</span>
                            </div>
                            <div>
                                <h4 id="modal-bot-name"
                                    class="text-[10px] font-black text-accent-primary uppercase tracking-[0.25em] mb-1">AI
                                    TRADING BOT</h4>
                                <div class="flex items-center gap-2">
                                    <h4 id="modal-pair" class="text-2xl font-black text-white tracking-tighter">BTCUSDT
                                    </h4>
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                                    <span id="modal-type"
                                        class="text-[10px] font-black text-white/40 uppercase tracking-widest italic">CRYPTO</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end opacity-40">
                            <img src="{{ asset('assets/images/logo-square.png') }}" class="h-10 mb-1" alt="Lozand">
                            <span
                                class="text-[8px] font-black tracking-[0.3em] uppercase">{{ \Illuminate\Support\Str::limit(config('app.name'), 14) }}</span>
                        </div>
                    </div>

                    {{-- Hero Section: Large ROI --}}
                    <div class="flex flex-col items-center text-center py-4">
                        <div class="text-[10px] text-text-secondary uppercase tracking-[0.4em] font-black mb-4 opacity-50">
                            {{ __('Profit / Loss') }}</div>
                        <div id="modal-roi"
                            class="text-7xl font-black text-emerald-400 italic tracking-tighter leading-none mb-4 drop-shadow-[0_0_20px_rgba(52,211,153,0.3)] transition-all duration-700">
                            +0.00%</div>
                        <div id="modal-pnl"
                            class="text-2xl font-black text-emerald-400/60 italic tracking-tight font-mono mb-6">
                            +{{ showAmount(0) }}</div>

                        {{-- Risk Management Badge --}}
                        <div
                            class="inline-flex flex-col items-center bg-white/5 border border-white/10 px-8 py-3 rounded-full transition-all duration-500 group-hover/card:bg-white/10 group-hover/card:scale-105">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span
                                    class="text-[9px] font-black text-text-secondary uppercase tracking-[0.2em] opacity-40">{{ __('Trade size') }}</span>
                                <span id="modal-amount"
                                    class="text-xs font-black text-white font-mono tracking-tight">--</span>
                            </div>
                            <div class="text-[10px] font-black text-text-secondary/60 italic tracking-tight text-[8px]">
                                (<span id="modal-risk-percent" class="text-emerald-400">0.0%</span> of <span
                                    id="modal-activation-amount" class="text-white/80">--</span> risked)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="p-12 space-y-10">
                    <div class="grid grid-cols-2 gap-y-10 py-10 border-y border-white/5 relative">
                        {{-- Decorative side lines --}}
                        <div class="absolute left-1/2 top-10 bottom-10 w-px bg-white/5"></div>

                        <div class="pl-0">
                            <div
                                class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2 opacity-40">
                                {{ __('Direction & Lev') }}</div>
                            <div class="flex items-center gap-2">
                                <span id="modal-direction"
                                    class="text-xs font-black uppercase tracking-widest text-emerald-400">LONG</span>
                                <span id="modal-leverage"
                                    class="text-xs font-black text-white italic tracking-tighter bg-white/5 px-2 py-0.5 rounded-lg border border-white/10">20x</span>
                            </div>
                        </div>
                        <div class="pl-8">
                            <div
                                class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2 opacity-40">
                                {{ __('Exit Price') }}</div>
                            <div id="modal-exit"
                                class="text-base font-black text-white font-mono tracking-tight underline decoration-accent-primary/30 underline-offset-4 decoration-2">
                                --</div>
                        </div>
                        <div class="pl-0">
                            <div
                                class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2 opacity-40">
                                {{ __('Execution Engine') }}</div>
                            <div class="flex flex-col gap-2">
                                <div id="modal-exchange-name"
                                    class="text-xs font-black text-white uppercase tracking-widest italic font-mono">
                                    BINANCE
                                    EXCHANGE</div>
                                <div class="flex items-center">
                                    <div class="h-6 flex items-center justify-start overflow-hidden">
                                        <img id="modal-exchange-logo" src=""
                                            class="h-full w-auto object-contain grayscale opacity-60 hidden"
                                            alt="Exchange">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pl-8">
                            <div
                                class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2 opacity-40">
                                {{ __('Completion Date') }}</div>
                            <div id="modal-date"
                                class="text-xs font-black text-white/60 uppercase tracking-widest italic font-mono">
                                2026-03-19</div>
                        </div>
                    </div>

                    {{-- Footer/Action --}}
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-4 group/btn">
                            <button id="btn-save-performance"
                                class="flex-1 py-5 bg-[#f0b90b] hover:bg-[#e0ab08] text-black text-xs font-black rounded-3xl uppercase tracking-[0.25em] transition-all transform hover:-translate-y-1 active:scale-95 shadow-[0_15px_30px_-10px_rgba(240,185,11,0.4)] flex items-center justify-center gap-3 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span class="btn-text">{{ __('Save Performance') }}</span>
                                <div class="btn-loader hidden">
                                    <svg class="animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                        <div
                            class="flex items-center justify-center gap-8 opacity-20 text-[8px] font-black uppercase tracking-[0.5em] italic">
                            <span>Dynamic AI</span>
                            <span class="w-1 h-1 rounded-full bg-white"></span>
                            <span>Verified Stats</span>
                            <span class="w-1 h-1 rounded-full bg-white"></span>
                            <span>Real-Time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- ApexCharts CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            $(document).ready(function() {
                // 1. Earnings Trend Chart
                const trendOptions = {
                    series: [{
                        name: '{{ __('Profit') }}',
                        data: @json($chart_trend['data'])
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        foreColor: '#94a3b8',
                        fontFamily: 'inherit'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                        colors: ['#3b82f6']
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100],
                            colorStops: [{
                                    offset: 0,
                                    color: '#3b82f6',
                                    opacity: 0.4
                                },
                                {
                                    offset: 100,
                                    color: '#3b82f6',
                                    opacity: 0.05
                                }
                            ]
                        }
                    },
                    markers: {
                        size: 5,
                        colors: ['#3b82f6'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 7
                        }
                    },
                    grid: {
                        borderColor: 'rgba(255, 255, 255, 0.05)',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        }
                    },
                    xaxis: {
                        categories: @json($chart_trend['labels']),
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return '{{ $currency['symbol'] }}' + val.toFixed(2);
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return '{{ $currency['symbol'] }}' + val.toFixed(2) +
                                    ' {{ $currency['code'] }}';
                            }
                        }
                    }
                };
                const trendChart = new ApexCharts(document.querySelector("#chart-earnings-trend"), trendOptions);
                trendChart.render();

                // 2. Asset Distribution Chart
                const distOptions = {
                    series: @json($chart_distribution['data']),
                    labels: @json($chart_distribution['labels']),
                    chart: {
                        type: 'donut',
                        height: 350,
                        foreColor: '#94a3b8'
                    },
                    stroke: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        fontWeight: 'bold',
                        markers: {
                            radius: 12
                        }
                    },
                    colors: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '75%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '12px',
                                        fontWeight: '900',
                                        color: '#94a3b8'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '24px',
                                        fontWeight: '900',
                                        color: '#fff',
                                        formatter: function(val) {
                                            return '{{ $currency['symbol'] }}' + val;
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: '{{ __('TOTAL') }}',
                                        fontSize: '10px',
                                        color: '#94a3b8',
                                        formatter: function(w) {
                                            return '{{ $currency['symbol'] }}' + w.globals.seriesTotals.reduce(
                                                (a, b) => a + b, 0).toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark'
                    }
                };
                const distChart = new ApexCharts(document.querySelector("#chart-asset-distribution"), distOptions);
                distChart.render();

                // 1.1 Interval Switcher Logic
                $('.interval-btn').on('click', function() {
                    const btn = $(this);
                    const interval = btn.data('interval');

                    // UI styles
                    $('.interval-btn').removeClass(
                        'bg-accent-primary text-white shadow-lg shadow-accent-primary/20').addClass(
                        'text-text-secondary hover:bg-white/5');
                    btn.removeClass('text-text-secondary hover:bg-white/5').addClass(
                        'bg-accent-primary text-white shadow-lg shadow-accent-primary/20');

                    // Fetch new data
                    $('#chart-trend-loading').addClass('opacity-100 pointer-events-auto');

                    $.ajax({
                        url: "{{ route('user.trading-bots.logs') }}",
                        data: {
                            type: 'chart',
                            interval: interval
                        },
                        success: function(response) {
                            trendChart.updateOptions({
                                xaxis: {
                                    categories: response.labels
                                },
                                series: [{
                                    data: response.data
                                }]
                            });
                            $('#chart-trend-loading').removeClass(
                                'opacity-100 pointer-events-auto');
                        },
                        error: function() {
                            $('#chart-trend-loading').removeClass(
                                'opacity-100 pointer-events-auto');
                        }
                    });
                });

                // 3. AJAX Table Logic
                let searchTimer;
                const fetchLogs = (page = 1) => {
                    const search = $('#log-search').val();
                    $('#table-loading').addClass('opacity-100 pointer-events-auto');

                    $.ajax({
                        url: "{{ route('user.trading-bots.logs') }}?page=" + page + "&search=" + search,
                        success: function(data) {
                            $('#logs-table-container').html(data);
                            $('#table-loading').removeClass('opacity-100 pointer-events-auto');
                        },
                        error: function() {
                            $('#table-loading').removeClass('opacity-100 pointer-events-auto');
                        }
                    });
                };

                $('#log-search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => fetchLogs(1), 500);
                });

                $(document).on('click', '.logs-pagination a', function(e) {
                    e.preventDefault();
                    const page = $(this).attr('href').split('page=')[1];
                    fetchLogs(page);
                });

                // 4. PNL Modal Logic
                $(document).on('click', '.view-pnl-btn', function(e) {
                    e.preventDefault();
                    const btn = $(this);
                    const data = btn.data();

                    // Populate Modal
                    $('#modal-pair').text(data.pair);
                    $('#modal-bot-name').text(data.botName.toUpperCase());
                    $('#modal-direction').text(data.direction.toUpperCase());
                    $('#modal-leverage').text(data.leverage + 'x');
                    $('#modal-type').text(data.type.toUpperCase());

                    const isPositive = parseFloat(data.pnlRaw) > 0;
                    const pnlPrefix = isPositive ? '+' : '';
                    const roiPrefix = isPositive ? '+' : '';

                    $('#modal-pnl').text(pnlPrefix + data.pnl);
                    $('#modal-roi').text(roiPrefix + data.roi);
                    $('#modal-exit').text(data.exit);

                    // Risk Management Data
                    const tradeSize = parseFloat(data.amountRaw) || 0;
                    const activationCapital = parseFloat(data.activationAmountRaw) || 0;
                    const riskPercent = activationCapital > 0 ? (tradeSize / activationCapital * 100) : 0;

                    $('#modal-amount').text(data.amount);
                    $('#modal-activation-amount').text(data.activationAmount);
                    $('#modal-risk-percent').text(riskPercent.toFixed(1) + '%');

                    // Exchange Branding
                    const exchange = data.exchange || "{{ config('site.site_name') }}";
                    $('#modal-exchange-name').text(exchange.substring(0, 18).toUpperCase() + (exchange.length >
                        18 ? '...' : '') + ' ENGINE');

                    if (data.isCustomExchange === true || data.isCustomExchange === 'true') {
                        $('#modal-exchange-logo').addClass('hidden');
                    } else {
                        $('#modal-exchange-logo').attr('src', data.exchangeLogo).removeClass('hidden');
                    }

                    // Date (Full ISO-like format for this design)
                    $('#modal-date').text(data.date);

                    // Bot Icon/Image
                    if (data.botImage && !data.botImage.includes('default.png')) {
                        $('#modal-bot-img').attr('src', data.botImage).removeClass('hidden');
                        $('#modal-bot-icon span').addClass('hidden');
                    } else {
                        $('#modal-bot-img').addClass('hidden');
                        $('#modal-bot-icon span').removeClass('hidden').text(data.pair.substring(0, 2)
                            .toUpperCase());
                    }

                    // Theme Colors & Glows
                    const themeColor = isPositive ? '#34d399' : '#fb7185';
                    const themeGlow = isPositive ? 'rgba(52, 211, 153, 0.4)' : 'rgba(251, 113, 133, 0.4)';
                    const bgGlow = isPositive ? 'rgba(52, 211, 153, 0.15)' : 'rgba(251, 113, 133, 0.15)';

                    $('#modal-roi').css({
                        'color': themeColor,
                        'filter': `drop-shadow(0 0 20px ${themeGlow})`
                    });
                    $('#modal-pnl').css('color', themeColor + '99'); // 60% opacity

                    $('#modal-glow-top').css('background-color', bgGlow);
                    $('#modal-glow-bottom').css('background-color', bgGlow.replace('0.15', '0.05'));

                    $('#modal-bot-icon').css({
                        'border-color': themeColor + '4d', // 30% opacity
                        'background-color': themeColor + '1a' // 10% opacity
                    }).find('span').css('color', themeColor);

                    $('#modal-bot-name').css('color', themeColor);

                    // Direction still colors based on type
                    const dirColor = ['long', 'buy'].includes(data.direction.toLowerCase()) ? '#34d399' :
                        '#fb7185';
                    $('#modal-direction').css('color', dirColor);

                    // Show Modal
                    $('#pnl-modal').removeClass('hidden cursor-default').addClass('cursor-pointer');
                    setTimeout(() => {
                        $('#pnl-card').removeClass('opacity-0 scale-90').addClass(
                            'opacity-100 scale-100 cursor-default');
                    }, 50);
                });

                $(document).on('click', '#btn-save-performance', function() {
                    const btn = $(this);
                    const btnText = btn.find('.btn-text');
                    const btnLoader = btn.find('.btn-loader');
                    const card = document.getElementById('pnl-card');
                    const closeBtn = document.getElementById('close-pnl');

                    // Show Loading
                    btn.attr('disabled', true).addClass('opacity-80 cursor-wait');
                    btnText.addClass('hidden');
                    btnLoader.removeClass('hidden');

                    // Temporary hide elements & fix styles for capture
                    const originalCloseDisplay = closeBtn.style.display;
                    const originalBtnDisplay = btn.parent().get(0).style.display;
                    const originalRadius = card.style.borderRadius;
                    const originalOverflow = card.style.overflow;

                    closeBtn.style.display = 'none';
                    btn.parent().get(0).style.display = 'none';
                    card.style.borderRadius = '0px';
                    card.style.overflow = 'visible';

                    htmlToImage.toPng(card, {
                            quality: 1.0,
                            pixelRatio: 2,
                            backgroundColor: '#0b0e11',
                            cacheBust: true,
                            // Filter out external textures that cause CORS errors
                            filter: (node) => {
                                // Only elements have tagNames and computed styles
                                if (node.nodeType !== 1) return true;

                                if (node.tagName === 'STYLE' || node.tagName === 'LINK') return true;
                                const style = window.getComputedStyle(node);
                                const bg = style.getPropertyValue('background-image');
                                return !bg.includes('transparenttextures.com');
                            }
                        })
                        .then(function(dataUrl) {
                            // Restore elements & styles
                            closeBtn.style.display = originalCloseDisplay;
                            btn.parent().get(0).style.display = originalBtnDisplay;
                            card.style.borderRadius = originalRadius;
                            card.style.overflow = originalOverflow;

                            // Download
                            const link = document.createElement('a');
                            link.download = 'trading-performance-' + new Date().getTime() + '.png';
                            link.href = dataUrl;
                            link.click();

                            // Reset Button
                            btn.attr('disabled', false).removeClass('opacity-80 cursor-wait');
                            btnText.removeClass('hidden');
                            btnLoader.addClass('hidden');
                        })
                        .catch(function(error) {
                            console.error('oops, something went wrong!', error);
                            closeBtn.style.display = originalCloseDisplay;
                            btn.parent().get(0).style.display = originalBtnDisplay;
                            card.style.borderRadius = originalRadius;
                            card.style.overflow = originalOverflow;
                            btn.attr('disabled', false).removeClass('opacity-80 cursor-wait');
                            btnText.removeClass('hidden');
                            btnLoader.addClass('hidden');
                            toastNotification('Failed to generate image. Please try again.', 'error');
                        });
                });

                $('#close-pnl, #pnl-modal').on('click', function(e) {
                    if (e.target !== this && this.id === 'pnl-modal') return;
                    $('#pnl-card').removeClass('opacity-100 scale-100').addClass('opacity-0 scale-90');
                    setTimeout(() => {
                        $('#pnl-modal').addClass('hidden');
                    }, 500);
                });
            });
        </script>
    @endpush
@endsection
