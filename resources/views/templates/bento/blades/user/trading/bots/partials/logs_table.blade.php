<div class="overflow-x-auto">
    <table class="w-full text-left border-separate border-spacing-y-4">
        <thead>
            <tr class="text-[10px] text-text-secondary uppercase tracking-[0.2em] font-black">
                <th class="px-6 py-4 text-left">{{ __('Trade Details') }}</th>
                <th class="px-6 py-4 text-left">{{ __('Execution') }}</th>
                <th class="px-6 py-4 text-left">{{ __('Financials') }}</th>
                <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="group bg-secondary/50 hover:bg-secondary border border-white/5 transition-all">
                    {{-- Trade Details --}}
                    <td class="px-6 py-5 first:rounded-l-2xl border-y border-l border-white/5">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-accent-primary/10 flex items-center justify-center border border-accent-primary/20 transition-all group-hover:bg-accent-primary/20 group-hover:border-accent-primary/40">
                                <span class="text-xs font-black text-accent-primary leading-none">
                                    {{ strtoupper(substr($log->trading_pair, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <div
                                    class="text-sm font-black text-white group-hover:text-accent-primary transition-colors flex items-center gap-1.5">
                                    {{ $log->trading_pair }}
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter {{ $log->type == 'forex' ? 'bg-blue-400/10 text-blue-400 border border-blue-400/20' : 'bg-purple-400/10 text-purple-400 border border-purple-400/20' }}">
                                        {{ $log->type }}
                                    </span>
                                </div>
                                <div
                                    class="text-[10px] text-text-secondary font-bold uppercase tracking-wider flex items-center gap-2 mt-0.5">
                                    @if ($log->exchange)
                                        <div
                                            class="flex items-center gap-1.5 bg-white/5 px-1.5 py-0.5 rounded-md border border-white/10 transition-colors group-hover:bg-white/10">
                                            <img src="{{ asset('assets/images/exchanges/' . strtolower($log->exchange) . '.svg') }}"
                                                class="w-3 h-3 grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100 transition-all"
                                                onerror="this.style.display='none'" alt="{{ $log->exchange }}">
                                            <span>{{ $log->exchange }}</span>
                                        </div>
                                    @endif
                                    @if ($log->exchange)
                                        <span class="w-1 h-1 rounded-full bg-white/10"></span>
                                    @endif
                                    {{ date('M d, H:i', $log->exit_time) }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Execution Details --}}
                    <td class="px-6 py-5 border-y border-white/5">
                        <div class="flex items-center gap-6">
                            <div>
                                <div
                                    class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-1.5 opacity-50">
                                    {{ __('Direction') }}</div>
                                <span
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ in_array($log->direction, ['long', 'buy']) ? 'bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 shadow-[0_0_10px_-2px_rgba(52,211,153,0.1)]' : 'bg-rose-400/10 text-rose-400 border border-rose-400/20 shadow-[0_0_10px_-2px_rgba(251,113,133,0.1)]' }}">
                                    {{ $log->direction }}
                                </span>
                            </div>
                            <div>
                                <div
                                    class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-1.5 opacity-50">
                                    {{ __('Leverage') }}</div>
                                <span
                                    class="text-xs font-black text-white italic tracking-tighter">{{ $log->leverage }}x</span>
                            </div>
                        </div>
                    </td>

                    {{-- Financials --}}
                    <td class="px-6 py-5 border-y border-white/5">
                        <div class="flex items-center gap-8">
                            <div>
                                <div
                                    class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-1.5 opacity-50">
                                    {{ __('Entry') }}</div>
                                <div class="text-xs font-black text-white tracking-tight">
                                    {{ showAmount($log->amount) }}</div>
                            </div>
                            <div>
                                <div
                                    class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-1.5 opacity-50">
                                    {{ __('Profit') }}</div>
                                <div class="text-sm font-black text-emerald-400 tracking-tight italic">
                                    +{{ showAmount($log->profit) }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Action --}}
                    <td class="px-6 py-5 last:rounded-r-2xl border-y border-r border-white/5 text-right">
                        <a href="#"
                            class="view-pnl-btn inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-black text-white uppercase tracking-widest transition-all hover:bg-accent-primary hover:text-white hover:border-accent-primary hover:shadow-[0_0_20px_-5px_rgba(59,130,246,0.5)] cursor-pointer"
                            data-pair="{{ $log->trading_pair }}"
                            data-bot-name="{{ $log->activation->bot->name ?? 'AI Bot' }}"
                            data-bot-image="{{ asset('assets/images/bots/' . ($log->activation->bot->logo ?? 'default.png')) }}"
                            data-type="{{ $log->type }}" data-direction="{{ $log->direction }}"
                            data-leverage="{{ $log->leverage }}" data-pnl="{{ showAmount($log->profit) }}"
                            data-pnl-raw="{{ (float) $log->profit }}"
                            data-amount="{{ showAmount($log->amount) }}"
                            data-amount-raw="{{ (float) $log->amount }}"
                            data-activation-amount="{{ showAmount($log->activation->amount) }}"
                            data-activation-amount-raw="{{ (float) $log->activation->amount }}"
                            data-roi="{{ number_format($log->profit_percentage * $log->leverage, 2) }}%"
                            data-exit="{{ showAmount($log->exit_price) }}"
                            data-exchange="{{ $log->exchange ?? config('app.name') }}"
                            data-exchange-logo="{{ asset('assets/images/exchanges/' . strtolower($log->exchange ?? 'platform') . '.svg') }}"
                            data-is-custom-exchange="{{ $log->exchange ? 'false' : 'true' }}"
                            data-date="{{ date('Y-m-d H:i', $log->exit_time) }}">
                            {{ __('View') }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-text-secondary italic font-bold">
                        {{ __('No trading logs found matching your criteria.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-8 flex justify-center logs-pagination">
    {{ $logs->links() }}
</div>
