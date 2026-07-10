{{-- Stripe Setup Guide --}}
<div class="settings-section">
    <div class="mb-8 border-b border-white/5 pb-4 flex items-center justify-between">
        <h3 class="text-xl font-medium text-white tracking-wide">{{ __('Stripe Setup Guide') }}
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
        {{-- Step 1: Access Dashboard --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    1</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Access the Stripe Dashboard') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Log in to your account at') }} <a href="https://dashboard.stripe.com" target="_blank"
                                class="text-accent-primary hover:underline font-bold">dashboard.stripe.com</a>
                        </p>
                        <p>{{ __('Ensure you are in the correct mode (Test or Live) as per your requirements.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Retrieve API Keys --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    2</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Retrieve API Keys') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Navigate to Developers → API keys:') }}</p>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-slate-500 mt-2">
                            <li>{{ __('Copy the "Publishable key" into the Stripe Key field below.') }}</li>
                            <li>{{ __('Copy the "Secret key" into the Stripe Secret field below.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Configure Webhooks --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-7 h-7 rounded-lg bg-accent-primary/10 text-accent-primary flex items-center justify-center text-xs font-black shrink-0">
                    3</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-white mb-2">
                        {{ __('Configure Webhooks') }}</h4>
                    <div class="text-[11px] text-slate-400 leading-relaxed space-y-2">
                        <p>{{ __('Navigate to Developers → Webhooks and click "Add endpoint".') }}</p>
                        <ol class="list-decimal list-inside space-y-1 ml-2 text-slate-500 mt-2">
                            <li>{{ __('Paste the Webhook URL (shown at the bottom of this page) into the "Endpoint URL" field.') }}</li>
                            <li>{{ __('Select the event:') }} <span class="font-mono text-accent-primary">checkout.session.completed</span></li>
                            <li>{{ __('After saving, reveal the "Signing secret" and paste it into the Webhook Secret field below.') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
