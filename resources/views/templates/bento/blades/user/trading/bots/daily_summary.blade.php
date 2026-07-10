@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <style>
        .glass-panel { background: rgba(255, 255, 255, 0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .text-glow-emerald { text-shadow: 0 0 15px rgba(52, 211, 153, 0.3); }
        .trading-grid { 
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .flatpickr-calendar { background: #0b0e11 !important; border: 1px solid rgba(255,255,255,0.1) !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
        .flatpickr-day.selected { background: var(--accent-primary) !important; border-color: var(--accent-primary) !important; }
    </style>

    <div class="space-y-12 pb-20 relative">
        {{-- Subtle background pattern --}}
        <div class="absolute inset-0 trading-grid pointer-events-none opacity-50"></div>

        {{-- Hero Header Section --}}
        <div class="relative overflow-hidden bg-[#0b0e11] border border-white/5 rounded-[60px] p-12 sm:p-20 shadow-2xl transition-all duration-700 hover:border-accent-primary/20">
            <div class="relative z-10 max-w-4xl">
                <div class="inline-flex items-center gap-3 bg-white/5 border border-white/10 px-5 py-2.5 rounded-2xl mb-8 group transition-all">
                    <span class="w-2 h-2 rounded-full bg-accent-primary shadow-[0_0_10px_rgba(59,130,246,0.6)]"></span>
                    <span class="text-[11px] font-black text-white/60 uppercase tracking-[0.3em]">{{ __('AI Execution Intelligence') }}</span>
                </div>
                <h1 class="text-5xl sm:text-7xl font-black text-white mb-8 leading-[0.95] tracking-tighter">
                    {{ __('Trading') }} <br>
                    <span class="text-accent-primary">{{ __('Performance') }}</span> <span class="text-white/20 italic">{{ __('Flow') }}</span>
                </h1>
                <p class="text-text-secondary text-xl leading-relaxed font-medium max-w-2xl opacity-70">
                    {{ __('Precision analytics meeting clinical execution. Your daily trading performance distilled into a singular high-fidelity overview.') }}
                </p>
            </div>
            
            {{-- Simplified Decorative Elements --}}
            <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-accent-primary/5 rounded-full blur-[120px]"></div>
            <div class="absolute inset-0 opacity-[0.02] pointer-events-none" style="background-image: url('{{ asset('assets/images/noise.svg') }}')"></div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Total Net Profit --}}
            <div class="glass-panel rounded-[40px] p-10 relative overflow-hidden group shadow-2xl transition-all hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                        <div class="text-[11px] text-text-secondary uppercase tracking-[0.3em] font-black opacity-60">{{ __('Net Profit') }}</div>
                    </div>
                    <div class="text-5xl font-black text-emerald-400 italic tracking-tighter leading-none text-glow-emerald">
                        {{ showAmount($stats['total_profit']) }}</div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/15 transition-all duration-700"></div>
            </div>

            {{-- Daily Average --}}
            <div class="glass-panel rounded-[40px] p-10 relative overflow-hidden group shadow-2xl transition-all hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-accent-primary/20 flex items-center justify-center text-accent-primary">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2V9h2v8zm-4 0H8v-4h2v4zm8 0h-2v-7h2v7z"/></svg>
                        </div>
                        <div class="text-[11px] text-text-secondary uppercase tracking-[0.3em] font-black opacity-60">{{ __('Daily Velocity') }}</div>
                    </div>
                    <div class="text-5xl font-black text-white italic tracking-tighter leading-none">
                        {{ showAmount($stats['avg_daily_profit']) }}</div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-accent-primary/5 rounded-full blur-3xl group-hover:bg-accent-primary/15 transition-all duration-700"></div>
            </div>

            {{-- Total Executions --}}
            <div class="glass-panel rounded-[40px] p-10 relative overflow-hidden group shadow-2xl transition-all hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h7v7l10-12h-7V2z"/></svg>
                        </div>
                        <div class="text-[11px] text-text-secondary uppercase tracking-[0.3em] font-black opacity-60">{{ __('Trade Intelligence') }}</div>
                    </div>
                    <div class="text-5xl font-black text-white italic tracking-tighter leading-none">
                        {{ number_format($stats['total_trades']) }}</div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-purple-500/5 rounded-full blur-3xl group-hover:bg-purple-500/15 transition-all duration-700"></div>
            </div>

            {{-- Active Strategies --}}
            <div class="glass-panel rounded-[40px] p-10 relative overflow-hidden group shadow-2xl transition-all hover:-translate-y-2">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2Z"/></svg>
                        </div>
                        <div class="text-[11px] text-text-secondary uppercase tracking-[0.3em] font-black opacity-60">{{ __('Active Alphas') }}</div>
                    </div>
                    <div class="text-5xl font-black text-white italic tracking-tighter leading-none">{{ $stats['total_activations'] }}</div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/15 transition-all duration-700"></div>
            </div>
        </div>

        {{-- Distribution Section (Donuts) --}}
        <div class="space-y-8">
            <div class="flex items-center justify-between px-2">
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">{{ __('Profit Distributions') }}</h3>
                    <p class="text-xs text-text-secondary font-medium mt-1">{{ __('Multi-dimensional analysis of your bot earnings.') }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- By Pair --}}
                <div class="glass-panel rounded-[50px] p-10 shadow-3xl relative overflow-hidden group hover:bg-white/[0.05] transition-all duration-700">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h4 class="text-xl font-black text-white tracking-tight">{{ __('Trading Pairs') }}</h4>
                            <p class="text-[10px] text-text-secondary uppercase tracking-[0.25em] font-black mt-1 opacity-50">{{ __('Profit by Asset') }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-accent-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                    </div>
                    <div id="chart-pair-distribution" class="min-h-[300px]"></div>
                </div>

                {{-- By Exchange --}}
                <div class="glass-panel rounded-[50px] p-10 shadow-3xl relative overflow-hidden group hover:bg-white/[0.05] transition-all duration-700">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h4 class="text-xl font-black text-white tracking-tight">{{ __('High-Performance') }}</h4>
                            <p class="text-[10px] text-text-secondary uppercase tracking-[0.25em] font-black mt-1 opacity-50">{{ __('Profit by Exchange') }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                    </div>
                    <div id="chart-exchange-distribution" class="min-h-[300px]"></div>
                </div>

                {{-- By Type --}}
                <div class="glass-panel rounded-[50px] p-10 shadow-3xl relative overflow-hidden group hover:bg-white/[0.05] transition-all duration-700">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h4 class="text-xl font-black text-white tracking-tight">{{ __('Strategy Logic') }}</h4>
                            <p class="text-[10px] text-text-secondary uppercase tracking-[0.25em] font-black mt-1 opacity-50">{{ __('Profit by Type') }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                    </div>
                    <div id="chart-type-distribution" class="min-h-[300px]"></div>
                </div>
            </div>
        </div>

        {{-- Daily Summary Cards --}}
        <div class="space-y-8 relative">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2">
                <div>
                    <h3 class="text-3xl font-black text-white tracking-tight">{{ __('Daily Trading Journey') }}</h3>
                    <p class="text-xs text-text-secondary font-medium mt-1">{{ __('Step-by-step breakdown of your bot activity and cumulative growth.') }}</p>
                </div>
                
                {{-- Date Range Selector --}}
                <div class="flex items-center gap-4 bg-white/5 border border-white/10 p-2 pl-6 rounded-3xl shadow-xl group hover:border-accent-primary/30 transition-all">
                    <div class="flex items-center gap-4">
                        <svg class="w-4 h-4 text-text-secondary opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <input type="text" id="date_range" class="bg-transparent border-none text-xs font-black text-white p-0 focus:ring-0 cursor-pointer min-w-[200px]" placeholder="{{ __('SELECT RANGE') }}" readonly>
                    </div>
                </div>

                {{-- Interval Info Badge --}}
                <div class="hidden xl:flex items-center gap-3 bg-white/5 border border-white/10 px-5 py-3 rounded-2xl">
                    <span class="text-[10px] text-text-secondary uppercase tracking-widest font-black">{{ __('Display Cycle') }}</span>
                    <span class="text-xs font-black text-white">{{ __('30-Day Intervals') }}</span>
                </div>
            </div>

            <div id="summaries-container" class="relative">
                <div id="loading-overlay" class="absolute inset-x-0 -top-4 -bottom-20 z-20 bg-primary/60 backdrop-blur-md flex items-center justify-center rounded-[48px] opacity-0 pointer-events-none transition-all duration-500">
                    <div class="flex flex-col items-center gap-6">
                        <div class="relative w-16 h-16">
                            <div class="absolute inset-0 border-4 border-accent-primary/20 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-t-accent-primary rounded-full animate-spin"></div>
                        </div>
                        <span class="text-[10px] font-black text-white uppercase tracking-[0.3em] bg-accent-primary px-6 py-2 rounded-full shadow-lg shadow-accent-primary/20">{{ __('Synchronizing Data...') }}</span>
                    </div>
                </div>

                <div id="cards-wrapper">
                    @include('templates.' . config('site.template') . '.blades.user.trading.bots.partials.daily_cards', ['summaries' => $summaries])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            $(document).ready(function() {
                const decimalPlaces = {{ getSetting('decimal_places', 2) }};
                const currencySymbol = '{{ $currency['symbol'] }}';
                let current_start_date = '{{ request('start_date') }}';
                let current_end_date = '{{ request('end_date') }}';

                const formatAmount = (val) => {
                    return currencySymbol + parseFloat(val).toLocaleString('en-US', {
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
                };

                // Initialize Flatpickr
                const fp = flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    defaultDate: [current_start_date, current_end_date],
                    theme: "dark",
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                            current_start_date = instance.formatDate(selectedDates[0], "Y-m-d");
                            current_end_date = instance.formatDate(selectedDates[1], "Y-m-d");
                            loadSummaries('{{ route('user.trading-bots.daily-summary') }}');
                        }
                    }
                });

                const donutBaseOptions = {
                    chart: {
                        type: 'donut',
                        height: 300,
                        foreColor: '#94a3b8',
                        fontFamily: 'Inter, sans-serif'
                    },
                    stroke: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '11px',
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
                                        fontSize: '11px',
                                        fontWeight: '800',
                                        color: '#94a3b8'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '20px',
                                        fontWeight: '900',
                                        color: '#fff',
                                        formatter: formatAmount
                                    },
                                    total: {
                                        show: true,
                                        label: '{{ __('TOTAL PROFIT') }}',
                                        fontSize: '9px',
                                        fontWeight: '800',
                                        color: '#64748b',
                                        formatter: function(w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return formatAmount(total);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: formatAmount
                        }
                    }
                };

                // Pair Distribution
                new ApexCharts(document.querySelector("#chart-pair-distribution"), {
                    ...donutBaseOptions,
                    series: @json($pair_distribution->pluck('value')),
                    labels: @json($pair_distribution->pluck('label')),
                }).render();

                // Exchange Distribution
                new ApexCharts(document.querySelector("#chart-exchange-distribution"), {
                    ...donutBaseOptions,
                    series: @json($exchange_distribution->pluck('value')),
                    labels: @json($exchange_distribution->pluck('label')),
                }).render();

                // Type Distribution
                new ApexCharts(document.querySelector("#chart-type-distribution"), {
                    ...donutBaseOptions,
                    series: @json($type_distribution->pluck('value')),
                    labels: @json($type_distribution->pluck('label')),
                }).render();

                // AJAX Pagination & Filtering
                function loadSummaries(url) {
                    // Maintain current URL params but update dates
                    const finalUrl = new URL(url);
                    if (current_start_date) finalUrl.searchParams.set('start_date', current_start_date);
                    if (current_end_date) finalUrl.searchParams.set('end_date', current_end_date);
                    
                    $('#loading-overlay').addClass('opacity-100 pointer-events-auto');
                    
                    $.ajax({
                        url: finalUrl.toString(),
                        success: function(html) {
                            $('#cards-wrapper').html(html);
                            $('#loading-overlay').removeClass('opacity-100 pointer-events-auto');
                            
                            // Scroll to cards top
                            $('html, body').animate({
                                scrollTop: $("#summaries-container").offset().top - 100
                            }, 500);
                        },
                        error: function() {
                            $('#loading-overlay').removeClass('opacity-100 pointer-events-auto');
                        }
                    });
                }

                $(document).on('click', '.daily-pagination a', function(e) {
                    e.preventDefault();
                    loadSummaries($(this).attr('href'));
                });
            });
        </script>
    @endpush
@endsection
