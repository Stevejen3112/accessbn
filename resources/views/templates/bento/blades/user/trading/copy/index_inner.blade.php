@if ($last_error_message)
    <div
        class="glass-panel rounded-xl md:rounded-2xl px-4 py-3 mb-4 md:mb-6 border-red-500/30 bg-red-500/10 text-red-400 text-sm flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>{{ $last_error_message }}</span>
    </div>
@endif

@if (!$last_error_message || !empty($current_ticker_info))
    <div id="topPanelStats" class="glass-panel rounded-xl md:rounded-2xl px-3 md:px-4 py-3 mb-4 md:mb-6 relative z-50">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="flex items-center gap-2 relative">
                    <button id="pairDropdownBtn"
                        class="flex items-center gap-1.5 hover:bg-white/5 pr-2 pl-1 py-1 rounded-lg transition-colors group">
                        <div class="font-semibold tracking-tight text-white text-sm md:text-base">
                            {{ $current_ticker }}</div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="text-white/50 group-hover:text-white transition-colors">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div id="pairDropdownMenu"
                        class="absolute top-full left-0 mt-2 w-56 rounded-xl border border-white/10 shadow-2xl shadow-black/50 overflow-hidden hidden z-50 bg-[#131722]">
                        <div class="px-3 py-2 border-b border-white/10">
                            <input type="text" placeholder="{{ __('Search') }}" id="pairSearch"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-2 py-1.5 text-base text-white outline-none focus:border-accent-primary/50 transition-colors">
                        </div>
                        <div class="max-h-[300px] overflow-y-auto custom-scrollbar no-scrollbar">
                            @foreach ($all_crypto_tickers as $asset)
                                <a href="{{ route('user.copy-trading.index', ['ticker' => $asset['ticker']]) }}"
                                    class="pair-item w-full text-left px-4 py-2.5 text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors flex items-center justify-between group">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">{{ $asset['ticker'] }}</span>
                                        <span class="text-xs text-white/40 group-hover:text-white/60">/USD</span>
                                    </div>
                                    @if ($asset['ticker'] == $current_ticker)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="text-accent-primary">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <span
                        class="text-xs px-2 py-1 rounded-full bg-white/5 border border-white/10 text-white/70">{{ __('Copy') }}</span>
                </div>
                <div class="flex items-center gap-2 ml-2 md:ml-4">
                    <div class="text-white/70 text-xs md:text-sm">{{ __('Price') }}</div>
                    <div class="font-semibold text-white text-sm md:text-base" id="lastPrice">
                        @php $price = $current_ticker_info['current_price'] ?? 0; @endphp
                        {{ number_format($price, $price < 1 ? 4 : 2) }}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto">
                <div class="bg-white/5 border border-white/10 rounded-xl px-3 py-1.5 flex items-center gap-2">
                    <span class="font-semibold text-white text-xs">{{ __('Change 1D') }}</span>
                    <span
                        class="{{ ($current_ticker_info['change_1d_percentage'] ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400' }} text-xs font-bold">{{ ($current_ticker_info['change_1d_percentage'] ?? 0) > 0 ? '+' : '' }}{{ $current_ticker_info['change_1d_percentage'] ?? '0.00' }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-5 mb-4 md:mb-6">
        {{-- Chart Panel --}}
        <section class="lg:col-span-8 glow-border">
            <div class="glass-panel rounded-2xl md:rounded-3xl p-3 md:p-4 lg:p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-widest">{{ __('Market Chart') }}</h3>
                    <div id="chartTime" class="text-[10px] text-white/50">--:--:--</div>
                </div>
                <div
                    class="relative h-[250px] sm:h-[350px] md:h-[400px] lg:h-[470px] rounded-xl border border-white/10 bg-[#131722] overflow-hidden">
                    <div id="chartLoader"
                        class="absolute inset-0 z-20 flex items-center justify-center bg-[#131722]/50 backdrop-blur-sm">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-accent-primary"></div>
                    </div>
                    <div id="chartContainer" class="absolute inset-0 z-10 w-full h-full"></div>
                </div>
            </div>
        </section>

        {{-- Right Column: Copy Trading Activation --}}
        <div class="lg:col-span-4 space-y-4 md:space-y-5">
            <section class="glow-border">
                <div class="glass-panel rounded-2xl md:rounded-3xl p-6">
                    <h3 class="font-bold text-white text-base mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        {{ __('Start Copy') }}
                    </h3>

                    <div class="space-y-6">
                        {{-- Sandbox signals --}}
                        @if (config('app.env') === 'sandbox' && count($active_sandbox_codes) > 0)
                            <div class="p-3 rounded-xl bg-accent-primary/5 border border-accent-primary/10">
                                <p class="text-[8px] text-orange-500 mb-3 italic leading-tight">
                                    {{ __('{For demo only, copy any of the trading codes below to see how the copy trading works}') }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($active_sandbox_codes->take(5) as $code)
                                        <button onclick="copyToClipboard('{{ $code->code }}')"
                                            class="px-2 py-1 rounded-md bg-white/5 border border-white/10 hover:border-accent-primary/30 hover:bg-accent-primary/10 transition-all text-[10px] text-white/70 hover:text-white flex items-center gap-1.5 group">
                                            <span class="font-bold tracking-wider">{{ $code->code }}</span>
                                            <span
                                                class="text-[8px] text-white/20 group-hover:text-white/40 font-medium">({{ $code->pair }})</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        {{-- Trading Code Input --}}
                        <div>
                            <label
                                class="text-[10px] text-white/55 block mb-2 font-black uppercase tracking-widest">{{ __('Enter Trading Code') }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-accent-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <input type="text" id="copyTradingCode"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl py-4 pl-11 pr-4 text-white font-bold outline-none focus:border-accent-primary transition-all text-base tracking-widest placeholder:text-white/20"
                                    placeholder="e.g. BTC-FREE">
                            </div>
                        </div>

                        {{-- Strategy Details (Visible after validation) --}}
                        <div id="tradeDetails" class="hidden space-y-4 animate-fadeIn">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-accent-primary/5 border border-accent-primary/10 rounded-xl p-3">
                                    <span
                                        class="block text-[9px] text-white/50 uppercase font-bold mb-1">{{ __('Trading Pair') }}</span>
                                    <span id="tradePair" class="text-sm font-bold text-white tracking-tight">--</span>
                                </div>
                                <div class="bg-emerald-400/5 border border-emerald-400/10 rounded-xl p-3">
                                    <span
                                        class="block text-[9px] text-white/50 uppercase font-bold mb-1">{{ __('Expected ROI') }}</span>
                                    <span id="tradeRoi"
                                        class="text-sm font-bold text-emerald-400 tracking-tight">--</span>
                                </div>
                            </div>

                            <div id="amountInputContainer">
                                <div class="flex justify-between items-center mb-2">
                                    <label
                                        class="text-[10px] text-white/55 font-black uppercase tracking-widest">{{ __('Capital Amount') }}</label>
                                    <span class="text-[10px] text-white/55 font-bold">{{ __('Balance:') }} <span
                                            id="availableBalanceValue"
                                            class="text-white">{{ number_format($add_available, 2) }}</span>
                                        {{ getSetting('currency') }}</span>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span
                                            class="text-accent-primary font-bold text-sm">{{ getSetting('currency_symbol') }}</span>
                                    </div>
                                    <input type="number" id="inputAmount" step="any"
                                        class="w-full bg-white/5 border border-white/10 rounded-xl py-4 pl-10 pr-4 text-white font-bold outline-none focus:border-accent-primary transition-all text-lg"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <div id="percentageInfoContainer" class="hidden">
                                <div
                                    class="bg-accent-primary/10 border border-accent-primary/20 rounded-2xl p-4 flex items-center gap-4">
                                    <div class="bg-accent-primary/20 p-3 rounded-xl">
                                        <svg class="w-6 h-6 text-accent-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-bold text-sm">{{ __('Auto-Calculate Mode') }}</h4>
                                        <p class="text-white/60 text-xs mt-1">
                                            {{ __('This trade will automatically use') }} <span id="displayPercentage"
                                                class="text-white font-bold">--</span>% {{ __('of your balance.') }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-white/40 mt-3 italic text-center">
                                    {{ __('The capital will be calculated at the moment of activation.') }}
                                </p>
                            </div>
                        </div>

                        <button id="btnActivate" disabled
                            class="w-full py-4 rounded-2xl font-black text-sm bg-gradient-to-r from-accent-primary to-purple-600 hover:opacity-90 transition disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed shadow-xl shadow-accent-primary/20 text-white uppercase tracking-widest">
                            {{ __('Start Copy Trade') }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Bottom Section: Active/Recent Activations --}}
    <div class="glow-border">
        <div class="glass-panel rounded-3xl p-6">
            <h3 class="font-bold text-white text-lg mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('Recent Copy Trades') }}
            </h3>

            <div class="overflow-x-auto" id="activationsTable">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/55 border-b border-white/10">
                            <th class="text-left py-4 px-2">{{ __('Trade') }}</th>
                            <th class="text-left py-4 px-2">{{ __('Pair') }}</th>
                            <th class="text-right py-4 px-2">{{ __('Capital') }}</th>
                            <th class="text-right py-4 px-2">{{ __('Profit') }}</th>
                            <th class="text-right py-4 px-2">{{ __('ROI') }}</th>
                            <th class="text-right py-4 px-2">{{ __('Activated At') }}</th>
                            <th class="text-center py-4 px-2">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-white/80">
                        @forelse($activations as $activation)
                            <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-2 font-bold text-accent-primary">{{ $activation->copy_code }}</td>
                                <td class="py-4 px-2 text-white/70">{{ $activation->pair }}</td>
                                <td class="py-4 px-2 text-right font-mono">{{ showAmount($activation->amount) }}</td>
                                <td class="py-4 px-2 text-right font-mono">
                                    {{ $activation->status === 'active' ? '--' : showAmount($activation->profit) }}
                                </td>
                                <td
                                    class="py-4 px-2 text-right {{ $activation->roi < 0 ? 'text-red-400' : 'text-emerald-400' }} font-bold">
                                    {{ $activation->status === 'active' ? '--' : ($activation->roi > 0 ? '+' : '') . $activation->roi . '%' }}
                                </td>
                                <td class="py-4 px-2 text-right text-white/55 text-xs">
                                    {{ $activation->activated_at->format('M d, H:i') }}</td>
                                <td class="py-4 px-2 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $activation->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/10 text-white/50' }}">
                                        {{ $activation->status === 'active' ? __('running') : $activation->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-white/20 italic">
                                    {{ __('No recent activations') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('user.copy-trading.history') }}"
                    class="text-xs font-bold text-accent-primary hover:text-white transition-colors uppercase tracking-widest">
                    {{ __('View Full History') }} →
                </a>
            </div>
        </div>
    </div>
@endif
