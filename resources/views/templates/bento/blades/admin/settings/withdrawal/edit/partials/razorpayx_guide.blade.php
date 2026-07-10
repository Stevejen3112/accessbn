{{-- RazorpayX Setup Guide --}}
<div class="settings-section">
    <div class="mb-8 border-b border-white/5 pb-4 flex items-center justify-between">
        <h3 class="text-xl font-medium text-white tracking-wide">{{ __('RazorpayX Setup Guide') }}
        </h3>
        <button type="button" id="toggle-guide"
            class="text-[10px] font-black uppercase tracking-widest text-accent-primary hover:text-white transition-colors flex items-center gap-2 cursor-pointer">
            <span id="guide-toggle-text">{{ __('Show Guide') }}</span>
            <svg id="guide-chevron" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                        {{ __('RazorpayX uses separate API keys from standard Razorpay. You must generate API keys from the RazorpayX dashboard at') }}
                        <a href="https://x.razorpay.com" target="_blank"
                            class="text-accent-primary hover:underline font-bold">x.razorpay.com</a>.
                        {{ __('Standard Razorpay keys from dashboard.razorpay.com will NOT work for payouts.') }}
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
                        {{ __('Access the RazorpayX Dashboard') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Sign up or log in at') }} <a href="https://x.razorpay.com" target="_blank"
                                class="text-accent-primary hover:underline font-bold">https://x.razorpay.com</a>
                        </p>
                        <p>{{ __('To switch to Test Mode:') }}</p>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-slate-500">
                            <li>{{ __('Click the profile icon at the top right corner') }}</li>
                            <li>{{ __('Use the toggle to switch between Test and Live mode') }}
                            </li>
                        </ul>
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
                            {{ __('Home → Profile Icon → My Accounts & Settings → Developer Controls → API Keys') }}
                        </div>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-slate-500 mt-2">
                            <li>{{ __('In test mode, no OTP is required – simply click Submit') }}
                            </li>
                            <li>{{ __('The Key ID and Secret will be shown only once') }}</li>
                            <li>{{ __('Download and store them securely – the Secret cannot be retrieved again') }}
                            </li>
                        </ul>
                        <p class="text-amber-500/80 font-medium mt-1">
                            ⚠️ {{ __('Paste these values into the Key ID and Key Secret fields above.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Customer Identifier --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    3</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Locate Your Customer Identifier (Account Number)') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Navigate to:') }}</p>
                        <div
                            class="bg-black/30 rounded-lg px-3 py-2 font-mono text-[10px] text-accent-primary border border-white/5 inline-block">
                            {{ __('Dashboard → Profile Icon → My Accounts & Settings → Banking → Customer Identifier') }}
                        </div>
                        <p class="mt-2">
                            {{ __('This value acts as your account number and is required for payout initiation.') }}
                        </p>
                        <p class="text-amber-500/80 font-medium">
                            ⚠️ {{ __('Paste this value into the "Customer Identifier" field below.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: IP Whitelisting --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    4</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Whitelist Server IPs') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('To allow your server to make payouts, whitelist the IPs below in your RazorpayX Dashboard:') }}
                        </p>
                        <div
                            class="bg-black/30 rounded-lg px-3 py-2 font-mono text-[10px] text-accent-primary border border-white/5 inline-block">
                            {{ __('Dashboard → Profile Icon → My Accounts & Settings → Developer Controls → Allowlist IPs') }}
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if (isset($server_ips))
                                @foreach ($server_ips as $ip)
                                    <span class="ip-badge" data-ip="{{ sandBoxCredentials($ip) }}">
                                        <span>{{ sandBoxCredentials($ip) }}</span>
                                        <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 5: Webhook Events --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    5</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Configure Webhook Events') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('In your RazorpayX dashboard, add the webhook URL shown below and subscribe to the following events:') }}
                        </p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span
                                class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] rounded-lg border border-emerald-500/20 font-mono font-bold">payout.processed</span>
                            <span
                                class="px-2.5 py-1 bg-red-500/10 text-red-400 text-[10px] rounded-lg border border-red-500/20 font-mono font-bold">payout.failed</span>
                            <span
                                class="px-2.5 py-1 bg-amber-500/10 text-amber-400 text-[10px] rounded-lg border border-amber-500/20 font-mono font-bold">payout.reversed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 6: API Flow --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    6</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('How Payouts Work (Automated)') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('When a user requests a withdrawal, the system automatically processes the following steps via the RazorpayX API:') }}
                        </p>
                        <div class="flex flex-col gap-2 mt-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded bg-white/5 text-[9px] font-black text-slate-500 flex items-center justify-center shrink-0">1</span>
                                <span>{{ __('Create a Contact for the user') }}</span>
                            </div>
                            <div class="flex items-center gap-2 pl-2">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded bg-white/5 text-[9px] font-black text-slate-500 flex items-center justify-center shrink-0">2</span>
                                <span>{{ __('Add a Fund Account (bank details) to the Contact') }}</span>
                            </div>
                            <div class="flex items-center gap-2 pl-2">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded bg-white/5 text-[9px] font-black text-slate-500 flex items-center justify-center shrink-0">3</span>
                                <span>{{ __('Initiate a Payout via IMPS/NEFT to the Fund Account') }}</span>
                            </div>
                            <div class="flex items-center gap-2 pl-2">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-5 h-5 rounded bg-white/5 text-[9px] font-black text-slate-500 flex items-center justify-center shrink-0">4</span>
                                <span>{{ __('Webhook confirms payout status (processed / failed / reversed)') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
