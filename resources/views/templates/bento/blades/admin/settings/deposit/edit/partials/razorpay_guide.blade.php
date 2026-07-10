{{-- Standard Razorpay Setup Guide --}}
<div class="settings-section">
    <div class="mb-8 border-b border-white/5 pb-4 flex items-center justify-between">
        <h3 class="text-xl font-medium text-white tracking-wide">{{ __('Razorpay Setup Guide') }}
        </h3>
        <button type="button" id="toggle-guide"
            class="text-[10px] font-black uppercase tracking-widest text-accent-primary hover:text-white transition-colors flex items-center gap-2 cursor-pointer">
            <span id="guide-toggle-text">{{ __('Show Guide') }}</span>
            <svg id="guide-chevron" class="w-4 h-4 transition-transform duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <div id="setup-guide" class="hidden space-y-6 animate-fade-up">
        {{-- Important Notice --}}
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <div class="p-1.5 bg-amber-500/10 rounded-lg shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.072 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-500 mb-1">{{ __('Important') }}</p>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        {{ __('These standard Razorpay API keys are for handling user deposits via checkout. Do NOT use RazorpayX keys here. Generate API keys from the standard Razorpay dashboard at') }}
                        <a href="https://dashboard.razorpay.com" target="_blank"
                            class="text-accent-primary hover:underline font-bold">dashboard.razorpay.com</a>.
                    </p>
                </div>
            </div>
        </div>

        {{-- Step 1: Access Dashboard --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    1</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Access the Razorpay Dashboard') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Sign up or log in at') }} <a href="https://dashboard.razorpay.com" target="_blank"
                                class="text-accent-primary hover:underline font-bold">dashboard.razorpay.com</a>
                        </p>
                        <p>{{ __('Toggle Test Mode or Live Mode depending on your environment needs.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Generate API Keys --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    2</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Generate API Keys') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Navigate to:') }}</p>
                        <div
                            class="bg-black/30 rounded-lg px-3 py-2 font-mono text-[10px] text-accent-primary border border-white/5 inline-block">
                            {{ __('Account & Settings → API Keys → Generate Key') }}
                        </div>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-slate-500 mt-2">
                            <li>{{ __('The Key ID and Secret will be shown.') }}</li>
                            <li>{{ __('Download and store them securely – the Secret cannot be retrieved again.') }}
                            </li>
                        </ul>
                        <p class="text-amber-500/80 font-medium mt-1">
                            ⚠️ {{ __('Paste these values into the Key ID and Key Secret fields below.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Webhook Events --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    3</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Configure Webhook Events') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('To receive payment updates, add the webhook URL (shown at the bottom of the page) and subscribe to:') }}
                        </p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span
                                class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] rounded-lg border border-emerald-500/20 font-mono font-bold">order.paid</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
