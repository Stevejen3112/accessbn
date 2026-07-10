<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Left Column: Core Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                {{ __('Strategy Configuration') }}
            </h3>

            <div class="space-y-4">
                @if(isset($tickerError) && $tickerError)
                    <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-4 text-xs flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold">{{ __('Market Data Error') }}</p>
                            <p class="opacity-80">{{ $tickerError }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="code" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Copy Trade Code') }}</label>
                    <input type="text" id="code" name="code" readonly value="{{ $strategy->code ?? __('AUTO-GENERATED') }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white/50 text-base focus:outline-none cursor-not-allowed"
                        placeholder="{{ __('e.g., MASTER-ALPHA-01') }}">
                    <p class="text-[10px] text-text-secondary mt-2 italic">
                        {{ __('The code will be automatically generated upon saving.') }}
                    </p>
                </div>

                <div>
                    <label for="pair" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Trading Pair') }}</label>
                    <select id="pair" name="pair"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all select2-pairs [&>option]:bg-secondary"
                        required>
                        <option value="">{{ __('Select Pair') }}</option>
                        @foreach($tickers as $ticker)
                            <option value="{{ $ticker['ticker'] }}" {{ (old('pair', $strategy->pair ?? '') == $ticker['ticker']) ? 'selected' : '' }}>
                                {{ $ticker['ticker'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="roi" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Expected ROI (%)') }}
                        <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" id="roi" name="roi" step="0.01" required value="{{ old('roi', $strategy->roi ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-emerald-500 transition-all text-emerald-400 font-bold"
                            placeholder="15.50">
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                            <span class="text-emerald-400/50 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="amount_type" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Amount Mode') }}
                        <span class="text-red-500">*</span></label>
                    <select id="amount_type" name="amount_type"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all select2-basic [&>option]:bg-secondary"
                        required>
                        <option value="manual" {{ (old('amount_type', $strategy->amount_type ?? 'manual') == 'manual') ? 'selected' : '' }}>
                            {{ __('Manual (User enters amount)') }}
                        </option>
                        <option value="percentage" {{ (old('amount_type', $strategy->amount_type ?? 'manual') == 'percentage') ? 'selected' : '' }}>
                            {{ __('Percentage (Auto-calculate from balance)') }}
                        </option>
                    </select>
                </div>

                <div id="percentage_container" class="{{ (old('amount_type', $strategy->amount_type ?? 'manual') == 'percentage') ? '' : 'hidden' }}">
                    <label for="percentage" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Balance Percentage (%)') }}
                        <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" id="percentage" name="percentage" step="0.01" value="{{ old('percentage', $strategy->percentage ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all font-bold"
                            placeholder="10.00">
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                            <span class="text-white/50 font-bold">%</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-text-secondary mt-2 italic">
                        {{ __('The trade amount will be calculated as this percentage of the user\'s balance.') }}
                    </p>
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Expiry Date & Time') }}</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" 
                        value="{{ isset($strategy->expires_at) ? date('Y-m-d\TH:i', $strategy->expires_at) : old('expires_at') }}"
                        min="{{ date('Y-m-d\TH:i') }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all [color-scheme:dark]">
                    <p class="text-[10px] text-text-secondary mt-2 italic">
                        {{ __('Leave empty if the strategy does not expire.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Helper Info --}}
    <div class="space-y-6">
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-widest">{{ __('Instructions') }}</h3>
            <ul class="space-y-3 text-xs text-text-secondary leading-relaxed">
                <li class="flex gap-2">
                    <span class="text-accent-primary">•</span>
                    {{ __('Define a unique code for this trading strategy.') }}
                </li>
                <li class="flex gap-2">
                    <span class="text-accent-primary">•</span>
                    {{ __('Set the expected ROI that will be displayed to users.') }}
                </li>
                <li class="flex gap-2">
                    <span class="text-accent-primary">•</span>
                    {{ __('Expiration date controls when the strategy becomes unavailable for new trades.') }}
                </li>
                <li class="flex gap-2 text-emerald-400">
                    <span class="text-emerald-400">•</span>
                    {{ __('Percentage mode automatically calculates the trade amount from the user\'s total balance.') }}
                </li>
            </ul>
        </div>
    </div>
</div>
