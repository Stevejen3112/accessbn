<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .animate-card {
        animation: fadeInUp 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
    }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-10">
    @forelse ($summaries as $index => $day)
        @php
            $isPositive = $day->daily_profit >= 0;
            $accentColor = $isPositive ? '#10b981' : '#f43f5e';
            $dateObj = \Carbon\Carbon::parse($day->date);
        @endphp
        <div class="animate-card group relative bg-[#0b0e11] border border-white/5 rounded-[56px] p-10 transition-all duration-700 hover:border-white/20 hover:-translate-y-4 overflow-hidden shadow-[0_40px_100px_-20px_rgba(0,0,0,0.5)]"
            style="animation-delay: {{ $index * 0.1 }}s">

            {{-- Elite Background Ornament --}}
            <div
                class="absolute -bottom-10 -right-10 w-40 h-40 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-700 pointer-events-none">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full text-white">
                    <path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2Z" />
                </svg>
            </div>

            {{-- Card Header: Premium Date Label --}}
            <div class="flex items-start justify-between mb-12 relative z-10">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span
                            class="w-2 h-2 rounded-full {{ $isPositive ? 'bg-emerald-500' : 'bg-rose-500' }} animate-pulse"></span>
                        <span
                            class="text-[10px] text-text-secondary uppercase tracking-[0.4em] font-black opacity-40">{{ $dateObj->format('Y') }}
                            PHASE</span>
                    </div>
                    <h4 class="text-3xl font-black text-white tracking-tighter leading-none">
                        {{ $dateObj->format('M d') }}</h4>
                    <span
                        class="text-[11px] font-black text-accent-primary uppercase tracking-widest mt-2 italic">{{ $dateObj->format('l') }}</span>
                </div>
                <div
                    class="bg-white/5 border border-white/10 px-4 py-2 rounded-2xl text-[10px] font-black text-white/40 uppercase tracking-widest">
                    ID: {{ substr(md5($day->date), 0, 6) }}
                </div>
            </div>

            {{-- Main PNL: The WOW Factor --}}
            <div class="mb-12 relative z-10">
                <div class="text-[10px] text-text-secondary uppercase tracking-[0.5em] font-black mb-5 opacity-30">
                    {{ __('Session PNL') }}</div>
                <div class="flex flex-col gap-2">
                    <div
                        class="text-5xl font-black italic tracking-tighter leading-none {{ $isPositive ? 'text-emerald-400 text-glow-emerald' : 'text-rose-400' }}">
                        {{ $isPositive ? '+' : '' }}{{ showAmount($day->daily_profit) }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1 bg-{{ $isPositive ? 'emerald' : 'rose' }}-500/10 border border-{{ $isPositive ? 'emerald' : 'rose' }}-500/20 rounded-full text-[10px] font-black text-{{ $isPositive ? 'emerald' : 'rose' }}-400 uppercase tracking-widest">
                            {{ $isPositive ? 'Growth' : 'Drawdown' }}
                        </span>
                        <span class="text-lg font-black text-white/20 italic tracking-tighter">
                            {{ $isPositive ? '+' : '' }}{{ number_format($day->daily_profit_percentage, 2) }}%
                        </span>
                    </div>
                </div>
            </div>


            {{-- Detailed Stats Grid --}}
            <div class="grid grid-cols-2 gap-10 py-10 border-y border-white/5 mb-10 relative z-10">
                <div>
                    <div class="text-[9px] text-text-secondary uppercase tracking-[0.3em] font-black mb-2 opacity-30">
                        {{ __('Executions') }}</div>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-2xl font-black text-white italic tracking-tighter">{{ $day->trade_count }}</span>
                        <span class="text-[9px] text-text-secondary font-black uppercase tracking-widest">Trades</span>
                    </div>
                </div>
                <div>
                    <div class="text-[9px] text-text-secondary uppercase tracking-[0.3em] font-black mb-2 opacity-30">
                        {{ __('Alpha Asset') }}</div>
                    <div
                        class="text-sm font-black text-accent-primary uppercase tracking-widest leading-tight truncate">
                        {{ $day->best_pair }}</div>
                </div>
            </div>

            {{-- Cumulative Growth: The Anchor --}}
            <div class="relative z-10">
                <div
                    class="flex items-center justify-between bg-white/[0.02] border border-white/5 p-6 rounded-[32px] group-hover:bg-white/5 transition-all duration-700">
                    <div class="flex flex-col">
                        <span
                            class="text-[8px] text-text-secondary uppercase tracking-[0.4em] font-black opacity-40 mb-1">{{ __('Cumulative Equity') }}</span>
                        <span
                            class="text-xl font-black text-white italic tracking-tighter">{{ showAmount($day->cumulative_profit) }}</span>
                    </div>
                    <div class="text-right">
                        <div
                            class="text-[20px] font-black {{ $day->cumulative_profit_percentage >= 0 ? 'text-emerald-400' : 'text-rose-400' }} italic leading-none mb-1">
                            {{ $day->cumulative_profit_percentage >= 0 ? '+' : '' }}{{ number_format($day->cumulative_profit_percentage, 1) }}%
                        </div>
                        <span
                            class="text-[8px] text-text-secondary uppercase tracking-widest font-black opacity-30">Total
                            ROI</span>
                    </div>
                </div>
            </div>

            {{-- Hover Shine Effect --}}
            <div
                class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none">
            </div>
        </div>
    @empty
        <div class="col-span-full py-32 text-center glass-panel rounded-[60px]">
            <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 animate-pulse">
                <svg class="w-12 h-12 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-3xl font-black text-white mb-4 tracking-tighter">{{ __('No Trading Logs Found') }}</h3>
            <p class="text-text-secondary text-base max-w-sm mx-auto opacity-60 font-medium">
                {{ __('There is no trading activity recorded for the selected period.') }}
            </p>
        </div>
    @endforelse
</div>

<div class="mt-16 flex justify-center daily-pagination">
    {{ $summaries->links() }}
</div>
