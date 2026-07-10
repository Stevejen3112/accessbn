<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Left Column: Core Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Info Card --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                {{ __('Basic Information') }}
            </h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Bot Name') }}
                            <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $bot->name ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all placeholder:text-text-secondary/50"
                            placeholder="{{ __('e.g., Perpetual Contract Execution Bot') }}">
                    </div>
                    <div>
                        <label for="logo" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Bot Logo') }}
                            @if(!isset($bot)) <span class="text-red-500">*</span> @endif</label>
                        <div class="flex items-center gap-4">
                            @if(isset($bot))
                                <div class="shrink-0">
                                    <img id="logo-preview" 
                                        src="{{ str_starts_with($bot->logo, 'bot-') ? asset('assets/images/bots/' . $bot->logo) : asset('assets/images/bots/' . $bot->logo) }}" 
                                        alt="{{ $bot->name }}" 
                                        class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 object-contain p-1">
                                </div>
                            @else
                                <div class="shrink-0 hidden" id="logo-preview-container">
                                    <img id="logo-preview" src="" alt="Preview" 
                                        class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 object-contain p-1">
                                </div>
                            @endif
                            <input type="file" id="logo" name="logo" @if(!isset($bot)) required @endif onchange="previewImage(this)"
                                class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white text-base focus:outline-none focus:border-accent-primary transition-all file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-accent-primary file:text-white hover:file:bg-accent-primary/90">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Bot Type') }}
                            <span class="text-red-500">*</span></label>
                        <select id="type" name="type" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all appearance-none cursor-pointer">
                            <option value="crypto" class="bg-secondary-dark text-white" {{ old('type', $bot->type ?? '') == 'crypto' ? 'selected' : '' }}>{{ __('Crypto') }}</option>
                            <option value="forex" class="bg-secondary-dark text-white" {{ old('type', $bot->type ?? '') == 'forex' ? 'selected' : '' }}>{{ __('Forex') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Status') }}
                            <span class="text-red-500">*</span></label>
                        <select id="is_active" name="is_active" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all appearance-none cursor-pointer">
                            <option value="1" class="bg-secondary-dark text-white" {{ old('is_active', $bot->is_active ?? '') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="0" class="bg-secondary-dark text-white" {{ old('is_active', $bot->is_active ?? '') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="is_capital_returned" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Capital Return') }}
                            <span class="text-red-500">*</span></label>
                        <select id="is_capital_returned" name="is_capital_returned" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all appearance-none cursor-pointer">
                            <option value="1" class="bg-secondary-dark text-white" {{ old('is_capital_returned', (isset($bot) ? $bot->is_capital_returned : 1)) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                            <option value="0" class="bg-secondary-dark text-white" {{ old('is_capital_returned', (isset($bot) ? $bot->is_capital_returned : 1)) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-3">{{ __('Trading Days') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $selectedDays = old('trading_days', $bot->trading_days ?? $days);
                        @endphp
                        @foreach($days as $day)
                            <label class="cursor-pointer group">
                                <input type="checkbox" name="trading_days[]" value="{{ $day }}" 
                                    {{ in_array($day, $selectedDays) ? 'checked' : '' }}
                                    class="hidden peer">
                                <div class="px-2 py-2 rounded-lg bg-white/5 border border-white/10 text-text-secondary text-[10px] text-center transition-all peer-checked:bg-accent-primary/20 peer-checked:border-accent-primary peer-checked:text-white group-hover:border-white/20 whitespace-nowrap">
                                    {{ __($day) }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Trading Configuration --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                {{ __('Trading Configuration') }}
            </h3>

            <div class="space-y-4">
                <div id="exchanges-container" class="{{ old('type', $bot->type ?? 'crypto') == 'forex' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-text-secondary mb-3">{{ __('Supported Exchanges') }}</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar p-1">
                        @foreach($data['exchanges'] as $exchange)
                            @php
                                $exchangeLogo = str_replace('.', '', strtolower($exchange)) . '.svg';
                            @endphp
                            <label class="flex items-center gap-3 cursor-pointer group p-3 bg-white/5 border border-white/10 rounded-xl hover:border-accent-primary/50 transition-all">
                                <input type="checkbox" name="exchanges[]" value="{{ $exchange }}" 
                                    {{ in_array($exchange, old('exchanges', $bot->exchanges ?? [])) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-12 h-6 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img src="{{ asset('assets/images/exchanges/' . $exchangeLogo) }}" 
                                            alt="{{ $exchange }}" 
                                            class="max-w-full max-h-full object-contain filter brightness-110 group-hover:brightness-125 transition-all"
                                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($exchange) }}&background=0D8ABC&color=fff&size=24'">
                                    </div>
                                    <span class="text-xs font-medium text-text-secondary group-hover:text-white transition-colors truncate">{{ $exchange }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-text-secondary">{{ __('Traded Pairs') }}</label>
                        <div class="relative w-1/2">
                            <input type="text" id="pair-search" placeholder="{{ __('Search pairs...') }}" 
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-base text-white focus:outline-none focus:border-accent-primary transition-all">
                        </div>
                    </div>
                    
                    <div id="crypto-pairs" class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar p-3 bg-white/5 border border-white/10 rounded-xl {{ old('type', $bot->type ?? 'crypto') == 'forex' ? 'hidden' : '' }}">
                        @php
                            $selectedPairs = old('traded_pairs', $bot->traded_pairs ?? []);
                            $cryptoPairs = $data['pairs']['crypto'] ?? [];
                            // Sort pairs: selected first
                            usort($cryptoPairs, function($a, $b) use ($selectedPairs) {
                                $aSelected = in_array($a, $selectedPairs);
                                $bSelected = in_array($b, $selectedPairs);
                                if ($aSelected && !$bSelected) return -1;
                                if (!$aSelected && $bSelected) return 1;
                                return strcmp($a, $b);
                            });
                        @endphp
                        @foreach($cryptoPairs as $pair)
                            <label class="pair-item flex items-center gap-2 cursor-pointer group" data-pair-name="{{ strtolower($pair) }}">
                                <input type="checkbox" name="traded_pairs_crypto[]" value="{{ $pair }}" 
                                    {{ in_array($pair, $selectedPairs) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary">
                                <span class="text-[10px] text-text-secondary group-hover:text-white transition-colors">{{ $pair }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div id="forex-pairs" class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar p-3 bg-white/5 border border-white/10 rounded-xl {{ old('type', $bot->type ?? 'crypto') == 'crypto' ? 'hidden' : '' }}">
                        @php
                            $forexPairs = $data['pairs']['forex'] ?? [];
                            // Sort pairs: selected first
                            usort($forexPairs, function($a, $b) use ($selectedPairs) {
                                $aSelected = in_array($a, $selectedPairs);
                                $bSelected = in_array($b, $selectedPairs);
                                if ($aSelected && !$bSelected) return -1;
                                if (!$aSelected && $bSelected) return 1;
                                return strcmp($a, $b);
                            });
                        @endphp
                        @foreach($forexPairs as $pair)
                            <label class="pair-item flex items-center gap-2 cursor-pointer group" data-pair-name="{{ strtolower($pair) }}">
                                <input type="checkbox" name="traded_pairs_forex[]" value="{{ $pair }}" 
                                    {{ in_array($pair, $selectedPairs) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary">
                                <span class="text-[10px] text-text-secondary group-hover:text-white transition-colors">{{ $pair }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Limits & Returns --}}
    <div class="space-y-6">
        {{-- Activation Limits --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                {{ __('Limits & Returns') }}
            </h3>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_amount" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Min Amount') }}</label>
                        <input type="number" id="min_amount" name="min_amount" step="any" required value="{{ old('min_amount', $bot->min_amount ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all"
                            placeholder="100">
                    </div>
                    <div>
                        <label for="max_amount" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Max Amount') }}</label>
                        <input type="number" id="max_amount" name="max_amount" step="any" required value="{{ old('max_amount', $bot->max_amount ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all"
                            placeholder="10000">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="daily_return_min" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Daily Return Min (%)') }}</label>
                        <input type="number" id="daily_return_min" name="daily_return_min" step="any" required value="{{ old('daily_return_min', $bot->daily_return_min ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-emerald-500 transition-all text-emerald-400 font-bold"
                            placeholder="1.0">
                    </div>
                    <div>
                        <label for="daily_return_max" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Daily Return Max (%)') }}</label>
                        <input type="number" id="daily_return_max" name="daily_return_max" step="any" required value="{{ old('daily_return_max', $bot->daily_return_max ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-emerald-500 transition-all text-emerald-400 font-bold"
                            placeholder="5.0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Duration Card --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Duration & Schedule') }}
            </h3>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="duration" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Duration Value') }}</label>
                        <input type="number" id="duration" name="duration" min="1" required value="{{ old('duration', $bot->duration ?? '') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all"
                            placeholder="30">
                    </div>
                    <div>
                        <label for="duration_type" class="block text-sm font-medium text-text-secondary mb-2">{{ __('Duration Type') }}</label>
                        <select id="duration_type" name="duration_type" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-base focus:outline-none focus:border-accent-primary transition-all appearance-none cursor-pointer">
                            @foreach(['hour', 'day', 'week', 'month', 'year'] as $type)
                                <option value="{{ $type }}" class="bg-secondary-dark text-white" {{ old('duration_type', $bot->duration_type ?? '') == $type ? 'selected' : '' }}>{{ __(ucfirst($type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-[10px] text-text-secondary italic leading-relaxed">
                    {{ __('The duration specifies the total time a user activation in this bot will last before it ends.') }}
                </p>
            </div>
        </div>
    </div>
</div>
