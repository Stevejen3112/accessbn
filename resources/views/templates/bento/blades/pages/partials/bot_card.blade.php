<div class="bot-card group relative bg-white/[0.02] backdrop-blur-md border border-white/5 rounded-[2.5rem] p-1 transition-all duration-300 hover:border-accent-primary/30 hover:bg-white/[0.04] hover:-translate-y-2 hover:shadow-2xl hover:shadow-accent-primary/10 flex flex-col h-full"
    data-type="{{ $bot->type }}" data-roi="{{ ($bot->daily_return_min + $bot->daily_return_max) / 2 }}"
    data-markets='{{ json_encode($bot->traded_pairs ?? []) }}' data-id="{{ $bot->id }}">

    {{-- Inner Content --}}
    <div class="relative bg-[#0B0F1A]/40 rounded-[2.2rem] p-8 h-full flex flex-col overflow-hidden">
        {{-- Glow Effect --}}
        <div
            class="absolute -top-20 -right-20 w-40 h-40 bg-accent-primary/10 rounded-full blur-3xl group-hover:bg-accent-primary/20 transition-all duration-500">
        </div>

        {{-- Header HUD --}}
        <div class="flex justify-between items-start mb-8 relative z-10">
            <div class="flex items-center gap-4">
                <div class="relative w-14 h-14">
                    <div
                        class="absolute inset-0 bg-accent-primary/20 rounded-2xl blur-lg scale-0 group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div
                        class="relative w-full h-full rounded-2xl border border-white/10 bg-[#0B0F17] p-1 overflow-hidden shadow-2xl">
                        <img src="{{ asset('assets/images/bots/' . $bot->logo) }}" alt="{{ $bot->name }}"
                            class="w-full h-full object-cover rounded-xl transition-transform duration-700 group-hover:scale-110">
                    </div>
                </div>
                <div>
                    <h4
                        class="text-xl font-bold text-white group-hover:text-accent-primary transition-colors leading-tight mb-1">
                        {{ $bot->name }}</h4>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-white/5 border border-white/10 text-[9px] font-bold text-text-secondary uppercase tracking-widest">{{ $bot->type }}</span>
                        <div class="flex gap-0.5">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="w-1 h-1 rounded-full bg-emerald-500/40"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right flex flex-col items-end gap-1.5">
                <div
                    class="text-[9px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/10 uppercase tracking-tighter">
                    {{ __('STATUS: OPTIMAL') }}
                </div>
                <div class="text-[8px] font-mono text-white/20 uppercase tracking-[0.2em]">
                    {{ __('ID:') }}{{ str_pad($bot->id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>

        {{-- Description Small --}}
        @if (isset($bot->description))
            <p
                class="text-text-secondary text-xs leading-relaxed mb-8 line-clamp-2 opacity-80 group-hover:opacity-100 transition-opacity">
                {{ __($bot->description) }}
            </p>
        @endif

        {{-- EXCHANGE LOGOS (SQUEEZED) --}}
        @if ($bot->type === 'crypto' && !empty($bot->exchanges))
            <div class="mb-8 relative z-10">
                <div class="text-[9px] font-bold text-text-secondary uppercase tracking-[0.2em] mb-4 opacity-40">
                    {{ __('Liquidity Venues') }}</div>
                <div class="flex -space-x-3">
                    @foreach (array_slice($bot->exchanges, 0, 5) as $exchange)
                        @php
                            $exchangeName = strtolower(str_replace('.', '', $exchange));
                            $logoPath = 'assets/images/exchanges/' . $exchangeName . '.svg';
                        @endphp
                        <div class="w-10 h-10 rounded-full border-2 border-[#0B0F17] bg-white transition-all group-hover:-translate-y-1 group-hover:rotate-6 hover:z-20 relative shadow-xl flex items-center justify-center overflow-hidden"
                            title="{{ $exchange }}">
                            @if (file_exists(public_path($logoPath)))
                                <img src="{{ asset($logoPath) }}" alt="{{ $exchange }}"
                                    class="w-6 h-6 object-contain">
                            @else
                                <span class="text-[10px] font-black text-black">{{ substr($exchange, 0, 1) }}</span>
                            @endif
                        </div>
                    @endforeach
                    @if (count($bot->exchanges) > 5)
                        <div
                            class="w-10 h-10 rounded-full border-2 border-[#0B0F17] bg-white flex items-center justify-center overflow-hidden z-10 shadow-xl">
                            <span class="text-[9px] font-black text-black">+{{ count($bot->exchanges) - 5 }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TRADED PAIRS --}}
        <div class="mb-10 relative z-10">
            <div class="text-[9px] font-bold text-text-secondary uppercase tracking-[0.2em] mb-4 opacity-40">
                {{ __('Target Pairs') }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach (array_slice($bot->traded_pairs ?? [], 0, 3) as $pair)
                    <span
                        class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/5 text-[9px] font-bold text-white uppercase tracking-tight group-hover:border-accent-primary/20 transition-all">
                        {{ $pair }}
                    </span>
                @endforeach
                @if (count($bot->traded_pairs ?? []) > 3)
                    <span
                        class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/5 text-[9px] font-bold text-white/40 uppercase">
                        +{{ count($bot->traded_pairs) - 3 }}
                    </span>
                @endif
            </div>
        </div>

        {{-- METRICS FOOTER --}}
        <div class="mt-auto relative z-10">
            <div
                class="mb-6 p-4 rounded-[1.5rem] bg-white/[0.03] border border-white/5 flex items-center justify-between group-hover:bg-accent-primary/5 group-hover:border-accent-primary/10 transition-all">
                <div class="flex flex-col">
                    <span
                        class="text-[8px] font-bold text-text-secondary uppercase tracking-[0.2em] mb-1">{{ __('Daily Yield') }}</span>
                    <div class="text-xl font-mono font-bold text-accent-primary tracking-tighter">
                        {{ $bot->daily_return_min }}% <span class="opacity-10 mx-0.5">-</span>
                        {{ $bot->daily_return_max }}%
                    </div>
                </div>
                <div class="text-right">
                    <span
                        class="text-[8px] font-bold text-text-secondary uppercase tracking-[0.2em] mb-1 block">{{ __('Risk Level') }}</span>
                    <span class="text-[10px] font-bold text-white uppercase italic">{{ __('Performance+') }}</span>
                </div>
            </div>

            <a href="{{ route('user.trading-bots.index') }}"
                class="group/btn relative block w-full text-center py-4 bg-accent-primary text-white font-bold uppercase tracking-widest text-[10px] rounded-2xl overflow-hidden shadow-2xl transition-all transform hover:-translate-y-1 hover:shadow-accent-primary/40 active:scale-95">
                <span class="relative z-10">{{ __('Initialize Bot') }}</span>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite]">
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }
</style>
