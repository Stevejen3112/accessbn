<div
    class="relative group bg-white/[0.02] backdrop-blur-md border border-white/5 rounded-3xl p-6 transition-all duration-500 hover:border-emerald-500/30 hover:bg-white/[0.04] flex flex-col h-full overflow-hidden">
    {{-- Decorative Glow --}}
    <div
        class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-700">
    </div>

    <div class="relative z-10 flex flex-col h-full">
        {{-- Header: Pair & Badge --}}
        <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden">
                    <span class="text-xs font-black text-white">{{ substr($trade->pair, 0, 2) }}</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white group-hover:text-emerald-400 transition-colors uppercase">
                        {{ $trade->pair }}</h4>
                    <span
                        class="text-[9px] text-text-secondary font-mono opacity-50 uppercase tracking-widest">{{ __('Trading Code:') }}{{ substr($trade->copy_code, 0, 8) }}</span>
                </div>
            </div>
            <div class="text-right">
                <div
                    class="text-[10px] font-black text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/10 uppercase tracking-tighter shadow-sm">
                    +{{ number_format($trade->roi, 2) }}%
                </div>
            </div>
        </div>

        {{-- Profit Display --}}
        <div
            class="mb-6 p-4 rounded-2xl bg-black/20 border border-white/5 group-hover:border-emerald-500/10 transition-all">
            <div class="text-[9px] font-bold text-text-secondary uppercase tracking-[0.2em] mb-2 opacity-40">
                {{ __('Realized Profit') }}</div>
            <div class="text-2xl font-black text-white tracking-tighter flex items-center gap-2">
                <span class="text-emerald-400">+</span>{{ showAmount($trade->profit) }}
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>

        {{-- Footer Metrics --}}
        <div class="mt-auto grid grid-cols-2 gap-4">
            <div>
                <span
                    class="text-[8px] font-black text-text-secondary uppercase tracking-widest opacity-40 block mb-1">{{ __('Capital Used') }}</span>
                <span class="text-xs font-bold text-white">{{ showAmount($trade->amount) }}</span>
            </div>
            <div class="text-right">
                <span
                    class="text-[8px] font-black text-text-secondary uppercase tracking-widest opacity-40 block mb-1">{{ __('Duration') }}</span>
                <span
                    class="text-xs font-bold text-white">{{ $trade->completed_at->diffForHumans($trade->activated_at, true) }}</span>
            </div>
        </div>

        {{-- Verification Stamp --}}
        <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                <span
                    class="text-[9px] font-bold text-emerald-500/80 uppercase tracking-widest">{{ __('Verified Result') }}</span>
            </div>
            <span
                class="text-[9px] font-mono text-white/20 uppercase">{{ $trade->completed_at->format('M d, H:i') }}</span>
        </div>
    </div>
</div>
