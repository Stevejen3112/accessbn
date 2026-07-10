@extends('templates.bento.blades.admin.layouts.admin')

@section('content')
    <div id="activations-content" class="space-y-8">
        {{-- Message if module is disabled --}}
        @if (!moduleEnabled('trading_bot_module'))
            <div class="relative z-10 p-8 flex flex-col items-center justify-center text-center h-[300px] bg-secondary/40 border border-white/5 rounded-[2.5rem] shadow-2xl overflow-hidden backdrop-blur-xl">
                <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <h4 class="text-sm font-black text-white uppercase tracking-widest mb-2">
                    {{ __('Trading Bot module disabled') }}</h4>
                <p class="text-[10px] text-slate-500 max-w-[200px] mb-6 font-medium leading-relaxed italic">
                    {{ __('Trading Bot module is disabled. Please enable it in settings to manage activations.') }}
                </p>
                <a href="{{ route('admin.settings.modules.index') }}"
                    class="px-5 py-2 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black text-white uppercase tracking-widest hover:bg-white/10 hover:border-white/20 transition-all active:scale-95">
                    {{ __('Settings') }}
                </a>
            </div>
        @endif

        @if (moduleEnabled('trading_bot_module'))
            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ __('User Bot Activations') }}</h1>
                    <p class="text-sm text-text-secondary mt-1">
                        {{ __('Monitor and manage all active and past trading bot performances across the platform.') }}
                    </p>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="p-3 bg-accent-primary/20 rounded-xl text-accent-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Total Capital') }}</p>
                            <h4 class="text-xl font-bold text-white">{{ showAmount($stats['total_capital']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="p-3 bg-emerald-500/20 rounded-xl text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Net Profit') }}</p>
                            <h4 class="text-xl font-bold text-emerald-400">+{{ showAmount($stats['total_profit']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="p-3 bg-indigo-500/20 rounded-xl text-indigo-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Active Bots') }}</p>
                            <h4 class="text-xl font-bold text-white">{{ number_format($stats['total_active']) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="bg-secondary border border-white/5 rounded-2xl p-5 relative overflow-hidden group hover:border-white/10 transition-all">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="p-3 bg-amber-500/20 rounded-xl text-amber-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-text-secondary text-[10px] uppercase font-black tracking-widest mb-1">{{ __('Total Activations') }}</p>
                            <h4 class="text-xl font-bold text-white">{{ number_format($stats['total_activations']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
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
                        <h3 class="text-lg font-black text-white uppercase tracking-widest mb-1">{{ __('Asset Distribution') }}</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-8 italic">{{ __('Capital by Strategy') }}</p>
                        <div id="distributionChart" class="min-h-[300px]"></div>
                    </div>
                </div>
            </div>

            {{-- Search & Export Row --}}
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
                <form action="{{ route('admin.trading-bots.activations.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search user or strategy...') }}" 
                            class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-accent-primary/50 w-full lg:w-64">
                    </div>
                    <select name="status" class="bg-secondary border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-accent-primary/50 cursor-pointer">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('All Status') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                    <button type="submit" class="bg-accent-primary/10 text-accent-primary p-2.5 rounded-xl hover:bg-accent-primary/20 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openExportModal()" class="bg-white/5 border border-white/10 text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-white/10 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('Export Reports') }}
                    </button>
                </div>
            </div>

            {{-- Activations Table --}}
            <div class="bg-secondary border border-white/5 rounded-[2.5rem] overflow-hidden shadow-2xl relative">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/[0.02] border-b border-white/5">
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary">{{ __('User') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary">{{ __('Bot Strategy') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">{{ __('Capital') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">{{ __('Profit') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-center">{{ __('Status') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">{{ __('Started') }}</th>
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-text-secondary text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($activations as $activation)
                                <tr class="hover:bg-white/[0.01] transition-colors group">
                                    <td class="p-5">
                                        <div class="flex items-center gap-4">
                                            <div class="relative shrink-0">
                                                @if ($activation->user->photo)
                                                    <img src="{{ asset('storage/profile/' . $activation->user->photo) }}"
                                                        class="w-10 h-10 rounded-full border border-white/10 object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-black text-xs uppercase border border-indigo-500/20">
                                                        {{ substr($activation->user->first_name, 0, 1) }}{{ substr($activation->user->last_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.users.detail', $activation->user->id) }}"
                                                    class="text-sm font-bold text-white hover:text-accent-primary transition-colors block leading-tight">
                                                    {{ $activation->user->fullname }}
                                                </a>
                                                <p class="text-[10px] text-text-secondary font-mono opacity-50">{{ $activation->user->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 p-1 flex items-center justify-center">
                                                <img src="{{ asset('assets/images/bots/' . $activation->bot->logo) }}" alt="" class="w-full h-full object-contain">
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-white leading-tight">{{ $activation->bot->name }}</p>
                                                <span class="text-[9px] font-black uppercase tracking-tighter text-text-secondary bg-white/5 px-1.5 py-0.5 rounded border border-white/5">
                                                    {{ $activation->bot->type }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 text-right">
                                        <p class="text-sm font-bold text-white">{{ showAmount($activation->amount) }}</p>
                                    </td>
                                    <td class="p-5 text-right">
                                        <div class="flex flex-col items-end">
                                            <p class="text-sm font-bold text-emerald-400">+{{ showAmount($activation->returned_profit) }}</p>
                                            @if($activation->amount > 0)
                                                <p class="text-[10px] font-black text-emerald-400/50 italic">
                                                    {{ number_format(($activation->returned_profit / $activation->amount) * 100, 2) }}%
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-5 text-center" id="status-cell-{{ $activation->id }}">
                                        @php
                                            $statusClasses = [
                                                'active' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
                                                'suspended' => 'bg-amber-500/10 border-amber-500/20 text-amber-500',
                                                'completed' => 'bg-blue-500/10 border-blue-500/20 text-blue-400',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $statusClasses[$activation->status] ?? 'bg-white/5 border-white/10 text-white' }}">
                                            {{ ucfirst($activation->status) }}
                                        </span>
                                    </td>
                                    <td class="p-5 text-right">
                                        <p class="text-[11px] font-medium text-text-secondary">{{ date('M d, Y', $activation->start_date) }}</p>
                                        <p class="text-[9px] text-text-secondary/50 font-mono">{{ date('H:i', $activation->start_date) }}</p>
                                    </td>
                                    <td class="p-5 text-right">
                                        <div class="flex items-center justify-end gap-2" id="actions-{{ $activation->id }}">
                                            @if($activation->status === 'active')
                                                <button type="button" 
                                                    onclick="updateStatus('{{ $activation->id }}', 'suspended')"
                                                    class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            @elseif($activation->status === 'suspended')
                                                <button type="button" 
                                                    onclick="updateStatus('{{ $activation->id }}', 'active')"
                                                    class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            @endif
                                            <button type="button" 
                                                onclick="openDeleteModal('{{ $activation->id }}')"
                                                class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-text-secondary italic">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <p>{{ __('No bot activations found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 border-t border-white/5">
                    {{ $activations->links('templates.bento.blades.partials.pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Export Modal --}}
    <div id="exportModal" class="hidden fixed inset-0 bg-slate-950/90 backdrop-blur-xl z-[100] flex items-center justify-center p-4">
        <div class="bg-secondary border border-white/10 w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">{{ __('Export Settings') }}</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 italic">{{ __('Select columns & format') }}</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 italic">{{ __('Include Columns') }}</p>
                        <div class="grid grid-cols-2 gap-3" id="export-columns-container">
                            @foreach ([
                                'username' => 'User',
                                'bot_name' => 'Bot Strategy',
                                'amount' => 'Capital',
                                'returned_profit' => 'Profit',
                                'status' => 'Status',
                                'start_date' => 'Start Date',
                                'end_date' => 'End Date',
                            ] as $key => $label)
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-white/5 hover:border-accent-primary/30 transition-all cursor-pointer group/item">
                                    <input type="checkbox" name="export_cols[]" value="{{ $key }}" checked 
                                        class="w-4 h-4 rounded border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary focus:ring-offset-0 cursor-pointer">
                                    <span class="text-xs text-slate-400 group-hover/item:text-white transition-colors">{{ __($label) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 italic">{{ __('Export Format') }}</p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['pdf' => 'PDF', 'csv' => 'CSV', 'sql' => 'SQL'] as $type => $label)
                                <button type="button" onclick="handleExport('{{ $type }}')" 
                                    class="px-4 py-3 rounded-xl border border-white/10 text-white text-xs font-black uppercase tracking-widest hover:bg-accent-primary hover:text-white hover:border-accent-primary transition-all cursor-pointer">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" onclick="closeExportModal()" class="w-full mt-4 text-[10px] text-slate-500 font-black uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                        {{ __('Close Window') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-secondary/90 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-secondary-dark border border-white/10 w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                {{ __('Delete Activation') }}
            </h3>
            <p class="text-text-secondary mb-8">{{ __('Are you sure you want to delete this activation? This will stop all trading and remove it from the user\'s portfolio.') }}</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 rounded-xl border border-white/10 text-white font-medium hover:bg-white/5 transition-all cursor-pointer">{{ __('Cancel') }}</button>
                <button type="button" id="confirm-delete-btn" class="flex-1 px-4 py-2 rounded-xl bg-red-500 text-white font-bold hover:bg-red-600 transition-all cursor-pointer">{{ __('Delete') }}</button>
            </div>
        </div>
        <input type="hidden" id="delete-id">
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
                data: { chart_days: days, type: 'activations' },
                success: function(response) {
                    profitChart.updateSeries([{ name: 'Profit', data: response.series }]);
                    profitChart.updateOptions({ xaxis: { categories: response.labels } });
                }
            });
        }

        // Export Actions
        function openExportModal() { $('#exportModal').removeClass('hidden'); }
        function closeExportModal() { $('#exportModal').addClass('hidden'); }

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

        // Status & Delete Logic
        function updateStatus(id, status) {
            $.ajax({
                url: "{{ route('admin.trading-bots.activations.status', ':id') }}".replace(':id', id),
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", status: status },
                success: function(response) {
                    if (response.success) {
                        toastNotification(response.message, 'success');
                        window.location.reload();
                    } else {
                        toastNotification(response.message, 'error');
                    }
                }
            });
        }

        function openDeleteModal(id) { $('#delete-id').val(id); $('#deleteModal').removeClass('hidden'); }
        function closeDeleteModal() { $('#deleteModal').addClass('hidden'); }

        $('#confirm-delete-btn').on('click', function() {
            const id = $('#delete-id').val();
            $.ajax({
                url: "{{ route('admin.trading-bots.activations.delete', ':id') }}".replace(':id', id),
                type: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        toastNotification(response.message, 'success');
                        window.location.reload();
                    } else {
                        toastNotification(response.message, 'error');
                    }
                }
            });
        });
    </script>
@endpush
