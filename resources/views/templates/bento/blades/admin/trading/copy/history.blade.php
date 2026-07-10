@extends('templates.bento.blades.admin.layouts.admin')

@section('content')
    <div class="space-y-8">
        {{-- Header Section --}}
        <div class="relative p-8 rounded-3xl bg-secondary-dark border border-white/5 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-accent-primary/5 blur-3xl rounded-full -mr-32 -mt-32"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-white font-heading tracking-tight mb-2">{{ $page_title }}</h1>
                    <p class="text-text-secondary max-w-2xl">
                        {{ __('View and manage all user activations of copy trading codes.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div
                class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-accent-primary/20 rounded-xl text-accent-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">
                            {{ __('Total Capital') }}</p>
                        <h4 class="text-xl font-bold text-white">{{ showAmount($stats['total_capital']) }}</h4>
                    </div>
                </div>
            </div>

            <div
                class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-emerald-500/20 rounded-xl text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">
                            {{ __('Total Profit') }}</p>
                        <h4 class="text-xl font-bold text-emerald-400">
                            {{ $stats['total_profit'] >= 0 ? '+' : '' }}{{ showAmount($stats['total_profit']) }}</h4>
                    </div>
                </div>
            </div>

            <div
                class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-indigo-500/20 rounded-xl text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">
                            {{ __('Active Trades') }}</p>
                        <h4 class="text-xl font-bold text-white">{{ number_format($stats['total_active']) }}</h4>
                    </div>
                </div>
            </div>

            <div
                class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-amber-500/20 rounded-xl text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">
                            {{ __('Total Trades') }}</p>
                        <h4 class="text-xl font-bold text-white">{{ number_format($stats['total_trades']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Profit Trend --}}
            <div
                class="lg:col-span-2 bg-secondary border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden shadow-2xl group transition-all duration-500">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-white uppercase tracking-widest">{{ __('Profit Trend') }}
                            </h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 italic">
                                {{ __('Platform-wide daily returns') }} (<span id="chart-period-display">7D</span>)</p>
                        </div>
                        <div class="flex items-center bg-white/5 rounded-xl p-1 gap-1 border border-white/10">
                            @foreach ([7 => '7D', 30 => '30D', 90 => '90D', 365 => '1Y'] as $d => $label)
                                <button type="button" onclick="updateProfitTrend({{ $d }}, this)"
                                    class="px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest transition-all interval-btn {{ $d == 7 ? 'bg-accent-primary text-white shadow-lg shadow-accent-primary/20' : 'text-slate-500 hover:text-white' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div id="profitTrendChart" class="min-h-[300px]"></div>
                </div>
            </div>

            {{-- Asset Distribution --}}
            <div
                class="bg-secondary border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden shadow-2xl group transition-all duration-500">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                </div>
                <div class="relative z-10">
                    <h3 class="text-lg font-black text-white uppercase tracking-widest mb-1">
                        {{ __('Ticker Distribution') }}</h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-8 italic">
                        {{ __('Capital by Ticker') }}</p>
                    <div id="distributionChart" class="min-h-[300px]"></div>
                </div>
            </div>
        </div>

        {{-- Main Content Section --}}
        <div class="bg-secondary-dark border border-white/5 rounded-3xl overflow-hidden backdrop-blur-xl">
            <div class="p-8 border-b border-white/5 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <h3 class="text-xl font-bold text-white">{{ __('Activation History') }}</h3>

                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <form action="{{ route('admin.copy-trading.history') }}" method="GET"
                        class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('Search user, code or pair...') }}"
                                class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-base text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-accent-primary/50 w-full sm:w-64">
                        </div>
                        <select name="status" onchange="this.form.submit()"
                            class="bg-secondary border border-white/10 rounded-xl px-4 py-2 text-base text-white focus:outline-none focus:ring-1 focus:ring-accent-primary/50 cursor-pointer [&>option]:bg-secondary">
                            <option value="all">{{ __('All Status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('Running') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                {{ __('Completed') }}</option>
                        </select>
                    </form>

                    <button type="button" onclick="openExportModal()"
                        class="flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold px-4 py-2 rounded-xl border border-white/10 transition-all text-sm w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4 text-accent-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('Export Reports') }}
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 border-b border-white/5 text-text-secondary text-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-8 py-5 font-bold">{{ __('User') }}</th>
                            <th class="px-8 py-5 font-bold">{{ __('Trading Code') }}</th>
                            <th class="px-8 py-5 font-bold">{{ __('Pair') }}</th>
                            <th class="px-8 py-5 font-bold text-right">{{ __('Capital') }}</th>
                            <th class="px-8 py-5 font-bold text-right">{{ __('Profit') }}</th>
                            <th class="px-8 py-5 font-bold text-right">{{ __('ROI') }}</th>
                             <th class="px-8 py-5 font-bold text-right">{{ __('Date') }}</th>
                             <th class="px-8 py-5 font-bold text-right">{{ __('Ends In') }}</th>
                             <th class="px-8 py-5 font-bold">{{ __('Status') }}</th>
                            <th class="px-8 py-5 font-bold text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-white/80">
                        @forelse($activations as $activation)
                            <tr class="hover:bg-white/[0.02] transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="relative shrink-0">
                                            @if ($activation->user->photo)
                                                <img src="{{ asset('storage/profile/' . $activation->user->photo) }}"
                                                    class="w-10 h-10 rounded-full border border-white/10 object-cover">
                                            @else
                                                <div
                                                    class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-black text-xs uppercase border border-indigo-500/20">
                                                    {{ substr($activation->user->first_name, 0, 1) }}{{ substr($activation->user->last_name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.users.detail', $activation->user->id) }}"
                                                class="text-sm font-bold text-white hover:text-accent-primary transition-colors block leading-tight">
                                                {{ $activation->user->fullname }}
                                            </a>
                                            <p class="text-[10px] text-text-secondary font-mono opacity-50">
                                                {{ $activation->user->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-bold text-white tracking-wider">
                                            {{ $activation->copy_code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-medium text-text-secondary">{{ $activation->pair }}</span>
                                </td>
                                <td class="px-8 py-5 text-right font-mono text-sm">
                                    {{ showAmount($activation->amount) }}
                                </td>
                                <td class="px-8 py-5 text-right font-mono text-sm">
                                    {{ $activation->status === 'active' ? '--' : showAmount($activation->profit) }}
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span
                                        class="text-sm font-bold {{ $activation->roi < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ $activation->status === 'active' ? '--' : ($activation->roi > 0 ? '+' : '') . $activation->roi . '%' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-sm text-white font-medium">{{ $activation->activated_at->format('M d, Y') }}</span>
                                        <span
                                            class="text-[10px] text-text-secondary">{{ $activation->activated_at->format('H:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right font-bold text-accent-primary text-xs">
                                    @if ($activation->status === 'active' && $activation->completes_at)
                                        {{ \Carbon\Carbon::parse($activation->completes_at)->diffForHumans() }}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td class="px-8 py-5">
                                    @php
                                        $statusClasses = [
                                            'active' => 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20',
                                            'completed' => 'bg-blue-400/10 text-blue-400 border-blue-400/20',
                                            'cancelled' => 'bg-red-400/10 text-red-400 border-red-400/20',
                                        ];
                                        $class =
                                            $statusClasses[$activation->status] ??
                                            'bg-gray-400/10 text-gray-400 border-gray-400/20';
                                    @endphp
                                     <span
                                        class="px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-widest {{ $class }}">
                                        {{ $activation->status === 'active' ? __('Running') : __($activation->status) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button type="button"
                                        onclick="openDeleteModal('{{ $activation->id }}', '{{ route('admin.copy-trading.history.delete', $activation->id) }}')"
                                        class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer"
                                        title="{{ __('Delete History') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                 <td colspan="10" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div
                                            class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-text-secondary/20">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-white font-bold">{{ __('No history records found') }}</p>
                                            <p class="text-sm text-text-secondary mt-1">
                                                {{ __('Copy trade activations will appear here.') }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activations->hasPages())
                <div class="p-8 border-t border-white/5">
                    {{ $activations->links() }}
                </div>
            @endif
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/80 backdrop-blur-sm" aria-hidden="true"
                    onclick="closeDeleteModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-secondary rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/5 relative z-10">
                    <div class="px-6 py-6 bg-secondary">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-500/10 rounded-xl sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 14c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-white" id="modal-title">
                                    {{ __('Delete History Record') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-text-secondary">
                                        {{ __('Are you sure you want to delete this history record? This action cannot be undone.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-white/5 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button type="button" onclick="closeDeleteModal()"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-bold text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" id="confirmDeleteButton"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-all shadow-lg shadow-red-500/20">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
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
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">{{ __('Export Settings') }}
                        </h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 italic">
                            {{ __('Select columns & format') }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 italic">
                            {{ __('Include Columns') }}</p>
                        <div class="grid grid-cols-2 gap-3" id="export-columns-container">
                            @foreach ([
            'username' => 'User',
            'copy_code' => 'Copy Code',
            'pair' => 'Trading Pair',
            'amount' => 'Capital',
            'profit' => 'Profit',
            'roi' => 'ROI',
            'status' => 'Status',
            'activated_at' => 'Activated At',
            'completed_at' => 'Completed At',
        ] as $key => $label)
                                <label
                                    class="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-white/5 hover:border-accent-primary/30 transition-all cursor-pointer group/item">
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
                        height: 300,
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
                                return "{{ getSetting('currency_symbol', '$') }}" + parseFloat(val).toFixed({{ getSetting('decimal_places', 2) }});
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
                        height: 300,
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
                        theme: 'dark'
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
                    'text-slate-500 hover:text-white');
                $(btn).removeClass('text-slate-500 hover:text-white').addClass(
                    'bg-accent-primary text-white shadow-lg shadow-accent-primary/20');

                $('#chart-period-display').text(days == 365 ? '1Y' : days + 'D');

                $.ajax({
                    url: "{{ route('admin.copy-trading.chart-data') }}",
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

            // Export Actions
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
                    alert("{{ __('Please select at least one column') }}");
                    return;
                }

                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('export', type);
                currentUrl.searchParams.set('columns', selectedCols.join(','));

                window.location.href = currentUrl.toString();
                closeExportModal();
            }

            // Delete History Functions
            let deleteUrl = '';

            function openDeleteModal(id, url) {
                deleteUrl = url;
                $('#deleteModal').removeClass('hidden');
            }

            function closeDeleteModal() {
                $('#deleteModal').addClass('hidden');
                deleteUrl = '';
            }

            $('#confirmDeleteButton').on('click', function() {
                const btn = $(this);
                const originalText = btn.html();

                btn.prop('disabled', true).html(
                    '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __('Deleting...') }}'
                );

                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            closeDeleteModal();
                            window.location.reload();
                        } else {
                            alert(response.message || "{{ __('Something went wrong') }}");
                            btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        alert("{{ __('Error deleting history record') }}");
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        </script>
    @endpush
@endsection
