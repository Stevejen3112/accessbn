@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <div class="space-y-8 pb-20">
        {{-- Header Section --}}
        <div class="relative overflow-hidden bg-secondary border border-white/5 rounded-3xl p-8 sm:p-12">
            <div class="relative z-10 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">
                    {{ __('My') }} <span class="text-accent-primary">{{ __('Copy Trades') }}</span>
                </h1>
                <p class="text-text-secondary text-base sm:text-lg leading-relaxed mb-8">
                    {{ __('Track your active copy tradings. Monitor your growth and manage your copy trading portfolio.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('user.copy-trading.index') }}"
                        class="flex items-center gap-2 bg-accent-primary hover:bg-accent-primary/90 text-white font-bold px-6 py-3 rounded-2xl transition-all shadow-lg shadow-accent-primary/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ __('Start Copy') }}
                    </a>
                </div>
            </div>

            {{-- Decorative Background Elements --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-accent-primary/10 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/10 rounded-full blur-[100px]"></div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Profit --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Total Profit') }}</div>
                    <div
                        class="text-3xl font-black {{ $stats['total_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }} italic tracking-tighter">
                        {{ $stats['total_profit'] >= 0 ? '+' : '' }}{{ showAmount($stats['total_profit']) }}</div>
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
                        {{ $stats['today_profit'] >= 0 ? '+' : '' }}{{ showAmount($stats['today_profit']) }}</div>
                </div>
                <div
                    class="absolute -right-4 -bottom-4 w-20 h-20 bg-accent-primary/5 rounded-full blur-2xl group-hover:bg-accent-primary/10 transition-all">
                </div>
            </div>

            {{-- Active Trades --}}
            <div
                class="bg-secondary border border-white/5 rounded-3xl p-6 relative overflow-hidden group shadow-xl transition-all hover:border-accent-primary/30">
                <div class="relative z-10">
                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                        {{ __('Active Trades') }}</div>
                    <div class="text-3xl font-black text-white italic tracking-tighter">
                        {{ number_format($stats['active_trades']) }}</div>
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
            <div
                class="lg:col-span-2 bg-secondary border border-white/5 rounded-[32px] p-8 shadow-2xl relative overflow-hidden group">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 relative z-10">
                    <div>
                        <h3 class="text-lg font-black text-white tracking-tight">{{ __('Performance Trend') }}</h3>
                        <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mt-1">
                            {{ __('Daily Profit performance') }}</p>
                    </div>
                    <div class="flex gap-2 bg-white/5 p-1.5 rounded-2xl border border-white/10 uppercase">
                        @foreach ([7 => '7D', 30 => '1M', 90 => '3M'] as $d => $label)
                            <button type="button" onclick="updateProfitTrend({{ $d }}, this)"
                                class="cursor-pointer interval-btn px-4 py-2 rounded-xl text-[10px] font-black tracking-widest transition-all {{ $d == 7 ? 'bg-accent-primary text-white shadow-lg shadow-accent-primary/20' : 'text-text-secondary hover:bg-white/5' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div id="profitTrendChart" class="min-h-[350px] relative z-10"></div>
            </div>

            {{-- Profit Distribution --}}
            <div class="bg-secondary border border-white/5 rounded-[32px] p-8 shadow-2xl relative overflow-hidden group">
                <h3 class="text-lg font-black text-white tracking-tight mb-1 relative z-10">{{ __('Profit Distribution') }}
                </h3>
                <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-8 relative z-10">
                    {{ __('Earnings by Ticker') }}</p>
                <div id="distributionChart" class="min-h-[350px] relative z-10"></div>
            </div>
        </div>

        {{-- Activations List --}}
        <div class="grid grid-cols-1 gap-8">
            @forelse ($activations as $activation)
                <div
                    class="group bg-secondary border border-white/5 rounded-[32px] overflow-hidden transition-all hover:border-accent-primary/30 shadow-2xl">
                    <div class="bg-secondary-dark/50 p-6 sm:p-8">
                        {{-- Top Header: Strategy Info & Status --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 pb-8 border-b border-white/5">
                            <div class="flex items-center gap-5">
                                <div class="relative">
                                    <div
                                        class="absolute -inset-1 bg-gradient-to-tr from-accent-primary to-purple-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity">
                                    </div>
                                    <div
                                        class="relative w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-accent-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3
                                        class="text-2xl font-black text-white group-hover:text-accent-primary transition-colors flex items-center gap-3">
                                        {{ $activation->copy_code }}
                                        <span
                                            class="px-2.5 py-0.5 rounded-full bg-accent-primary/10 border border-accent-primary/20 text-[10px] font-bold text-accent-primary uppercase tracking-widest">
                                            {{ $activation->pair }}
                                        </span>
                                    </h3>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span
                                            class="flex items-center gap-2 text-xs font-bold {{ $activation->status === 'active' ? 'text-emerald-400' : ($activation->status === 'completed' ? 'text-blue-400' : 'text-red-400') }}">
                                            <span
                                                class="w-2.5 h-2.5 rounded-full {{ $activation->status === 'active' ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.5)] animate-pulse' : ($activation->status === 'completed' ? 'bg-blue-400' : 'bg-red-400') }}"></span>
                                            {{ $activation->status === 'active' ? __('Running') : ucfirst($activation->status) }}
                                        </span>
                                        <span class="text-xs text-text-secondary font-medium">
                                            {{ __('Activated:') }} <span
                                                class="text-white font-bold">{{ $activation->activated_at->format('M d, Y H:i') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Middle Section: Financial Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div
                                class="bg-white/5 border border-white/5 rounded-3xl p-6 relative overflow-hidden group/card shadow-inner">
                                <div class="relative z-10">
                                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                                        {{ __('Initial Capital') }}</div>
                                    <div class="text-3xl font-black text-white italic tracking-tight">
                                        {{ showAmount($activation->amount) }}</div>
                                </div>
                                <div
                                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-accent-primary/5 rounded-full blur-2xl group-hover/card:bg-accent-primary/10 transition-all">
                                </div>
                            </div>

                            <div
                                class="bg-white/5 border border-white/5 rounded-3xl p-6 relative overflow-hidden group/card shadow-inner">
                                <div class="relative z-10">
                                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                                        {{ __('Profit / Loss') }}</div>
                                    <div class="flex items-baseline gap-2">
                                        <div
                                            class="text-3xl font-black {{ ($activation->profit ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }} italic tracking-tight">
                                            {{ $activation->status === 'active' ? '--' : ($activation->profit >= 0 ? '+' : '') . showAmount($activation->profit) }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover/card:bg-emerald-500/10 transition-all">
                                </div>
                            </div>

                            <div
                                class="bg-white/5 border border-white/5 rounded-3xl p-6 relative overflow-hidden group/card shadow-inner">
                                <div class="relative z-10">
                                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                                        {{ __('Expected ROI') }}</div>
                                    <div class="text-2xl font-black text-white italic tracking-tight">
                                        {{ $activation->status === 'active' ? '--' : ($activation->roi > 0 ? '+' : '') . $activation->roi . '%' }}
                                    </div>
                                </div>
                                <div
                                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover/card:bg-blue-500/10 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-secondary border border-white/5 rounded-3xl p-12 text-center">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-text-secondary/20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ __('No Active Copy Trades') }}</h3>
                    <p class="text-text-secondary mb-8 max-w-sm mx-auto">
                        {{ __('You haven\'t started any copy trades yet. Explore our top strategies to begin.') }}
                    </p>
                    <a href="{{ route('user.copy-trading.index') }}"
                        class="inline-flex items-center gap-2 bg-accent-primary hover:bg-accent-primary/90 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-lg shadow-accent-primary/20">
                        {{ __('Find Strategies') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $activations->links() }}
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            let profitChart;

            document.addEventListener('DOMContentLoaded', function() {
                const chartDistributionData = @json($chart_distribution);
                const chartTrendData = @json($chart_trend);

                // Distribution Chart
                new ApexCharts(document.querySelector("#distributionChart"), {
                    series: chartDistributionData.data,
                    chart: {
                        type: 'donut',
                        height: 350,
                        background: 'transparent'
                    },
                    labels: chartDistributionData.labels,
                    theme: {
                        mode: 'dark'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return "{{ getSetting('currency_symbol', '$') }}" + parseFloat(val)
                                    .toFixed({{ getSetting('decimal_places', 2) }});
                            }
                        }
                    },
                    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#ec4899'],
                    stroke: {
                        show: false
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            colors: '#94a3b8'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '75%',
                                background: 'transparent'
                            }
                        }
                    }
                }).render();

                // Trend Chart
                profitChart = new ApexCharts(document.querySelector("#profitTrendChart"), {
                    series: [{
                        name: 'Profit',
                        data: chartTrendData.data
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
                        background: 'transparent'
                    },
                    theme: {
                        mode: 'dark'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return "{{ getSetting('currency_symbol', '$') }}" + parseFloat(val)
                                    .toFixed({{ getSetting('decimal_places', 2) }});
                            }
                        }
                    },
                    colors: ['#3b82f6'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100]
                        }
                    },
                    xaxis: {
                        categories: chartTrendData.labels,
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#64748b'
                            }
                        }
                    },
                    grid: {
                        borderColor: 'rgba(255, 255, 255, 0.05)',
                        strokeDashArray: 3
                    }
                });
                profitChart.render();
            });

            function updateProfitTrend(days, btn) {
                $('.interval-btn').removeClass('bg-accent-primary text-white shadow-lg shadow-accent-primary/20').addClass(
                    'text-text-secondary hover:bg-white/5');
                $(btn).removeClass('text-text-secondary hover:bg-white/5').addClass(
                    'bg-accent-primary text-white shadow-lg shadow-accent-primary/20');

                $.ajax({
                    url: "{{ route('user.copy-trading.chart-data') }}",
                    type: 'GET',
                    data: {
                        interval: days
                    },
                    success: function(response) {
                        profitChart.updateSeries([{
                            name: 'Profit',
                            data: response.data
                        }]);
                        profitChart.updateOptions({
                            xaxis: {
                                categories: response.labels
                            }
                        });
                    }
                });
            }
        </script>
    @endpush
@endsection
