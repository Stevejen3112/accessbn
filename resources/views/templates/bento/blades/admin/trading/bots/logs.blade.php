@extends('templates.' . config('site.template') . '.blades.admin.layouts.admin')

@section('content')
    <div class="p-4 md:p-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ __('Trading Bot Logs') }}</h1>
                <p class="text-sm text-text-secondary mt-1">
                    {{ __('Complete historical record of all automated trading activities executed by platform bots.') }}
                </p>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all text-left">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-accent-primary/20 rounded-xl text-accent-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Trading Volume') }}</p>
                        <h4 class="text-xl font-bold text-white">{{ showAmount($stats['total_volume']) }}</h4>
                    </div>
                </div>
            </div>

            <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all text-left">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-emerald-500/20 rounded-xl text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Gross Profit') }}</p>
                        <h4 class="text-xl font-bold text-emerald-400">+{{ showAmount($stats['total_profit']) }}</h4>
                    </div>
                </div>
            </div>

            <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all text-left">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-indigo-500/20 rounded-xl text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Total Trades') }}</p>
                        <h4 class="text-xl font-bold text-white">{{ number_format($stats['total_trades']) }}</h4>
                    </div>
                </div>
            </div>

            <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all text-left">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-amber-500/20 rounded-xl text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Win Rate') }}</p>
                        <h4 class="text-xl font-bold text-blue-400">{{ $stats['win_rate'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 text-left">
            {{-- Profit Trend --}}
            <div class="lg:col-span-2 bg-secondary border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden shadow-2xl group transition-all duration-500">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-white uppercase tracking-widest">{{ __('Profit Trend') }}</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 italic">{{ __('Platform-wide daily returns') }} (<span id="chart-period-display">{{ request('chart_days', 7) == 365 ? '1Y' : request('chart_days', 7) . 'D' }}</span>)</p>
                        </div>
                        <div class="flex items-center bg-white/5 rounded-xl p-1 gap-1">
                            @foreach([7 => '7D', 30 => '30D', 90 => '90D', 365 => '1Y'] as $d => $label)
                                <button type="button" 
                                    onclick="updateProfitTrend({{ $d }}, this)"
                                    class="px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest transition-all interval-btn {{ request('chart_days', 7) == $d ? 'bg-accent-primary text-white shadow-lg' : 'text-slate-500 hover:text-white' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div id="profitTrendChart" class="min-h-[300px]"></div>
                </div>
            </div>

            {{-- Asset Distribution --}}
            <div class="bg-secondary border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden shadow-2xl group transition-all duration-500">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                <div class="relative z-10">
                    <h3 class="text-lg font-black text-white uppercase tracking-widest mb-1">{{ __('Profit Distribution') }}</h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-8 italic">{{ __('Returns by Strategy') }}</p>
                    <div id="distributionChart" class="min-h-[300px]"></div>
                </div>
            </div>
        </div>

        {{-- Search & Export Row --}}
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('admin.trading-bots.logs.index') }}" method="GET"
                class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('Search user, pair or exchange...') }}"
                        class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-accent-primary/50 w-full lg:w-64">
                </div>
                <button type="submit"
                    class="bg-accent-primary/10 text-accent-primary p-2.5 rounded-xl hover:bg-accent-primary/20 transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openExportModal()"
                    class="bg-white/5 border border-white/10 text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-white/10 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ __('Export Logs') }}
                </button>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="bg-secondary border border-white/5 rounded-[2.5rem] overflow-hidden shadow-2xl relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary">
                                {{ __('User') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary">
                                {{ __('Strategy') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary">
                                {{ __('Pair / Exchange') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">
                                {{ __('Amount') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">
                                {{ __('Profit') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">
                                {{ __('ROI %') }}</th>
                            <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">
                                {{ __('Executed At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($logs as $log)
                            <tr class="hover:bg-white/[0.01] transition-colors group">
                                <td class="p-5">
                                    <div class="flex items-center gap-3">
                                        @if ($log->user->photo)
                                            <div class="w-8 h-8 rounded-full border border-white/10 shadow-lg overflow-hidden shrink-0">
                                                <img src="{{ asset('storage/profile/' . $log->user->photo) }}" alt="{{ $log->user->username }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-black text-[10px] uppercase border border-indigo-500/20">
                                                {{ substr($log->user->username, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.users.detail', $log->user->id) }}"
                                                class="text-xs font-bold text-white hover:text-accent-primary transition-colors block italic">{{ $log->user->username }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <p class="text-xs font-bold text-white italic">
                                        {{ $log->activation->bot->name ?? 'N/A' }}</p>
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-col">
                                        <p class="text-xs font-black text-white italic">{{ $log->trading_pair }}</p>
                                        <p
                                            class="text-[9px] text-text-secondary font-black uppercase tracking-tighter opacity-50 italic">
                                            {{ $log->exchange }}</p>
                                    </div>
                                </td>
                                <td class="p-5 text-right font-mono text-xs text-white">
                                    {{ showAmount($log->amount) }}
                                </td>
                                <td class="p-5 text-right">
                                    <p
                                        class="text-xs font-black italic {{ $log->profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $log->profit >= 0 ? '+' : '' }}{{ showAmount($log->profit) }}
                                    </p>
                                </td>
                                <td class="p-5 text-right">
                                    <span
                                        class="px-2 py-0.5 rounded text-[9px] font-black italic {{ $log->profit_percentage >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                        {{ $log->profit_percentage >= 0 ? '+' : '' }}{{ number_format($log->profit_percentage * $log->leverage, 2) }}%
                                    </span>
                                </td>
                                <td class="p-5 text-right font-mono text-[10px] text-text-secondary opacity-60 italic">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-text-secondary italic opacity-30">
                                    {{ __('No trading logs found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-white/5">
                {{ $logs->links('templates.bento.blades.partials.pagination') }}
            </div>
        </div>
    </div>

    {{-- Export Modal --}}
    <div id="exportModal"
        class="hidden fixed inset-0 bg-slate-950/90 backdrop-blur-xl z-[100] flex items-center justify-center p-4">
        <div
            class="bg-secondary border border-white/10 w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">{{ __('Export Logs') }}</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 italic">
                            {{ __('Select columns & format') }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 italic">
                            {{ __('Include Columns') }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ([
            'username' => 'User',
            'bot_name' => 'Strategy',
            'trading_pair' => 'Trading Pair',
            'exchange' => 'Exchange',
            'amount' => 'Amount',
            'profit' => 'Profit',
            'profit_percentage' => 'ROI %',
            'created_at' => 'Date',
        ] as $key => $label)
                                <label
                                    class="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-white/5 hover:border-accent-primary/50 transition-all cursor-pointer group/item">
                                    <input type="checkbox" name="export_cols[]" value="{{ $key }}" checked
                                        class="w-4 h-4 rounded border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary focus:ring-offset-0 cursor-pointer">
                                    <span
                                        class="text-xs text-slate-400 group-hover/item:text-white transition-colors">{{ __($label) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 italic">
                            {{ __('Export Format') }}</p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['pdf' => 'PDF', 'csv' => 'CSV', 'sql' => 'SQL'] as $type => $label)
                                <button type="button" onclick="handleExport('{{ $type }}')"
                                    class="px-4 py-3 rounded-xl border border-white/10 text-white text-xs font-black uppercase tracking-widest hover:bg-accent-primary hover:text-white hover:border-accent-primary transition-all cursor-pointer">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" onclick="closeExportModal()"
                        class="w-full mt-4 text-[10px] text-slate-500 font-black uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                        {{ __('Close Window') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let profitChart;

        // Charts Integration
        document.addEventListener('DOMContentLoaded', function() {
            const chartDistributionData = @json($chart_distribution);
            const chartTrendData = @json($chart_trend);

            // Distribution Chart
            new ApexCharts(document.querySelector("#distributionChart"), {
                series: chartDistributionData.series,
                chart: { type: 'donut', height: 300, background: 'transparent' },
                labels: chartDistributionData.labels,
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#ec4899'],
                stroke: { show: false },
                legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '75%', background: 'transparent' } } }
            }).render();

            // Trend Chart
            profitChart = new ApexCharts(document.querySelector("#profitTrendChart"), {
                series: [{ name: 'Profit', data: chartTrendData.series }],
                chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent' },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' },
                colors: ['#10b981'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100, 100, 100] }
                },
                xaxis: {
                    categories: chartTrendData.labels,
                    labels: { style: { colors: '#64748b' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { colors: '#64748b' } } },
                grid: { borderColor: 'rgba(255, 255, 255, 0.05)', strokeDashArray: 3 }
            });
            profitChart.render();
        });

        function updateProfitTrend(days, btn) {
            // UI Update: toggle active class
            $('.interval-btn').removeClass('bg-accent-primary text-white shadow-lg').addClass('text-slate-500 hover:text-white');
            $(btn).removeClass('text-slate-500 hover:text-white').addClass('bg-accent-primary text-white shadow-lg');
            
            // Update description text
            $('#chart-period-display').text(days == 365 ? '1Y' : days + 'D');

            // AJAX Fetch
            $.ajax({
                url: "{{ route('admin.trading-bots.chart-data') }}",
                type: 'GET',
                data: { chart_days: days, type: 'logs' },
                success: function(response) {
                    profitChart.updateSeries([{ name: 'Profit', data: response.series }]);
                    profitChart.updateOptions({ xaxis: { categories: response.labels } });
                }
            });
        }

        function openExportModal() {
            $('#exportModal').removeClass('hidden');
        }

        function closeExportModal() {
            $('#exportModal').addClass('hidden');
        }

        function handleExport(type) {
            const selectedCols = [];
            $('input[name="export_cols[]"]:checked').each(function() {
                selectedCols.push($(this).val());
            });

            if (selectedCols.length === 0) {
                toastNotification("{{ __('Please select at least one column') }}", 'error');
                return;
            }

            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('export', type);
            currentUrl.searchParams.set('columns', selectedCols.join(','));

            window.location.href = currentUrl.toString();
            closeExportModal();
        }
    </script>
@endpush
