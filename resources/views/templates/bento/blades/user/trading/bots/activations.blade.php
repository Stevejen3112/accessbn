@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <style>
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .animate-shimmer {
            animation: shimmer 3s infinite linear;
        }
    </style>
    <div class="space-y-8 pb-20">
        {{-- Header Section --}}
        <div class="relative overflow-hidden bg-secondary border border-white/5 rounded-3xl p-8 sm:p-12">
            <div class="relative z-10 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">
                    {{ __('My') }} <span class="text-accent-primary">{{ __('Bot Activations') }}</span>
                </h1>
                <p class="text-text-secondary text-base sm:text-lg leading-relaxed mb-8">
                    {{ __('Track your active and past trading bot performances. Monitor your returns and manage your portfolio in real-time.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('user.trading-bots.index') }}"
                        class="flex items-center gap-2 bg-accent-primary hover:bg-accent-primary/90 text-white font-bold px-6 py-3 rounded-2xl transition-all shadow-lg shadow-accent-primary/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ __('Explore More Bots') }}
                    </a>
                </div>
            </div>

            {{-- Decorative Background Elements --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-accent-primary/10 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/10 rounded-full blur-[100px]"></div>
        </div>

        {{-- Activations List --}}
        <div class="grid grid-cols-1 gap-8">
            @forelse ($activations as $activation)
                <div
                    class="group bg-secondary border border-white/5 rounded-[32px] overflow-hidden transition-all hover:border-accent-primary/30 shadow-2xl">
                    <div class="bg-secondary-dark/50 p-6 sm:p-8">
                        {{-- Top Header: Bot Info & Status --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 pb-8 border-b border-white/5">
                            <div class="flex items-center gap-5">
                                <div class="relative">
                                    <div
                                        class="absolute -inset-1 bg-gradient-to-tr from-accent-primary to-purple-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity">
                                    </div>
                                    <img src="{{ asset('assets/images/bots/' . $activation->bot->logo) }}"
                                        alt="{{ $activation->bot->name }}"
                                        class="relative w-16 h-16 rounded-2xl object-cover border border-white/10">
                                </div>
                                <div>
                                    <h3
                                        class="text-2xl font-black text-white group-hover:text-accent-primary transition-colors flex items-center gap-3">
                                        {{ $activation->bot->name }}
                                        <span
                                            class="px-2.5 py-0.5 rounded-full bg-accent-primary/10 border border-accent-primary/20 text-[10px] font-bold text-accent-primary uppercase tracking-widest">
                                            {{ $activation->bot->type }}
                                        </span>
                                    </h3>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span
                                            class="flex items-center gap-2 text-xs font-bold {{ $activation->status === 'active' ? 'text-emerald-400' : 'text-gray-400' }}">
                                            <span
                                                class="w-2.5 h-2.5 rounded-full {{ $activation->status === 'active' ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.5)] animate-pulse' : 'bg-gray-400' }}"></span>
                                            {{ ucfirst($activation->status) }}
                                        </span>
                                        <span class="text-xs text-text-secondary font-medium">
                                            {{ __('Started:') }} <span
                                                class="text-white font-bold">{{ date('M d, Y H:i', $activation->start_date) }}</span>
                                        </span>
                                        <div class="h-1 w-1 rounded-full bg-white/20"></div>
                                        <div
                                            class="flex items-center gap-1.5 px-3 py-1 bg-white/5 border border-white/10 rounded-full group/trades transition-all hover:bg-white/10">
                                            <svg class="w-3 h-3 text-accent-primary group-hover/trades:rotate-12 transition-transform"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span class="text-[10px] font-black text-white uppercase tracking-wider">
                                                {{ number_format($activation->logs_count) }} {{ __('Trades') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('user.trading-bots.logs') }}"
                                    class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-xs font-black transition-all text-center uppercase tracking-widest cursor-pointer">
                                    {{ __('View Trading Logs') }}
                                </a>
                            </div>
                        </div>

                        {{-- Middle Section: Financial Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div
                                class="bg-white/5 border border-white/5 rounded-3xl p-6 relative overflow-hidden group/card shadow-inner">
                                <div class="relative z-10">
                                    <div class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black mb-2">
                                        {{ __('Active Capital') }}</div>
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
                                        {{ __('Current Profit') }}</div>
                                    <div class="flex items-baseline gap-2">
                                        <div class="text-3xl font-black text-emerald-400 italic tracking-tight">
                                            +{{ showAmount($activation->returned_profit) }}
                                        </div>
                                        @if ($activation->amount > 0)
                                            <div
                                                class="text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded-md">
                                                {{ number_format(($activation->returned_profit / $activation->amount) * 100, 2) }}%
                                            </div>
                                        @endif
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
                                        {{ __('Trading End Date') }}</div>
                                    <div class="text-2xl font-black text-white italic tracking-tight">
                                        @if ($activation->end_date > 0)
                                            {{ date('M d, Y', $activation->end_date) }}
                                        @else
                                            {{ __('Infinite') }}
                                        @endif
                                    </div>
                                </div>
                                <div
                                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover/card:bg-blue-500/10 transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Section: Technical Details --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {{-- Exchanges --}}
                            <div class="space-y-4">
                                <h4
                                    class="text-xs font-black text-text-secondary uppercase tracking-[0.2em] flex items-center gap-2">
                                    <svg class="w-4 h-4 text-accent-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ __('Active Exchanges') }}
                                </h4>
                                <div class="flex flex-wrap gap-3">
                                    @if ($activation->bot->exchanges)
                                        @foreach ($activation->bot->exchanges as $exchange)
                                            <div
                                                class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-4 py-2 group/ex hover:bg-white/10 transition-all">
                                                @php
                                                    $logoName = strtolower(str_replace('.', '', $exchange)) . '.svg';
                                                    $logoPath = public_path('assets/images/exchanges/' . $logoName);
                                                @endphp
                                                <div
                                                    class="w-8 h-8 rounded-full bg-white flex items-center justify-center p-1.5 shadow-lg group-hover/ex:scale-110 transition-transform">
                                                    @if (file_exists($logoPath))
                                                        <img src="{{ asset('assets/images/exchanges/' . $logoName) }}"
                                                            class="w-full h-full object-contain">
                                                    @else
                                                        <span
                                                            class="text-[8px] font-black text-black">{{ strtoupper(substr($exchange, 0, 2)) }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-sm font-bold text-white">{{ $exchange }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div
                                            class="bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-text-secondary italic text-xs font-bold leading-relaxed">
                                            {{ __('Utilizing Global Proprietary Liquidity Pools & Multi-Exchange Aggregators') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Traded Pairs --}}
                            <div class="space-y-4">
                                <h4
                                    class="text-xs font-black text-text-secondary uppercase tracking-[0.2em] flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>
                                    {{ __('Active Trading Markets') }}
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($activation->bot->traded_pairs ?? [] as $pair)
                                        <span
                                            class="px-3 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-xl text-[10px] font-black text-blue-400 italic hover:bg-blue-500/20 transition-all cursor-default">
                                            {{ $pair }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Final Section: Progress Bar --}}
                        @if ($activation->status === 'active' && $activation->end_date > 0)
                            @php
                                $total = $activation->end_date - $activation->start_date;
                                $elapsed = time() - $activation->start_date;
                                $percent = min(100, max(0, ($elapsed / $total) * 100));
                            @endphp
                            <div class="mt-10 pt-8 border-t border-white/5">
                                <div class="flex justify-between items-center mb-3">
                                    <span
                                        class="text-xs text-text-secondary font-black uppercase tracking-[0.2em]">{{ __('Strategy Runtime Progress') }}</span>
                                    <span
                                        class="px-3 py-1 bg-accent-primary/20 rounded-lg text-xs font-black text-accent-primary shadow-[0_0_15px_rgba(var(--accent-primary-rgb),0.3)]">{{ round($percent) }}%</span>
                                </div>
                                <div
                                    class="h-3 w-full bg-white/5 rounded-full overflow-hidden border border-white/5 shadow-inner">
                                    <div class="h-full bg-gradient-to-r from-accent-primary via-purple-500 to-accent-primary bg-[length:200%_100%] animate-shimmer rounded-full transition-all duration-1000"
                                        style="width: {{ $percent }}%"></div>
                                </div>
                                <div
                                    class="flex justify-between mt-3 text-[10px] font-bold text-text-secondary/50 uppercase tracing-widest">
                                    <span>{{ date('M d, Y', $activation->start_date) }}</span>
                                    <span>{{ date('M d, Y', $activation->end_date) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-secondary border border-white/5 rounded-3xl p-12 text-center">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-text-secondary/20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ __('No Activations Yet') }}</h3>
                    <p class="text-text-secondary mb-8 max-w-sm mx-auto">
                        {{ __('You haven\'t activated any trading bots yet. Start by exploring our professional AI trading strategies.') }}
                    </p>
                    <a href="{{ route('user.trading-bots.index') }}"
                        class="inline-flex items-center gap-2 bg-accent-primary hover:bg-accent-primary/90 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-lg shadow-accent-primary/20">
                        {{ __('Get Started') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $activations->links() }}
        </div>
    </div>
@endsection
