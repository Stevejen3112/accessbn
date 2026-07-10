@extends('templates.bento.blades.layouts.user')

@section('content')
    <div class="flex flex-col h-full space-y-8 animate-fade-up">
        <!-- Header & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-white">
            <div class="lg:col-span-1">
                <h2 class="text-3xl font-bold font-heading tracking-tight flex items-center gap-3">
                    <span
                        class="w-2 h-8 bg-accent-primary rounded-sm shadow-[0_0_15px_rgba(var(--color-accent-primary),0.5)]"></span>
                    {{ $page_title }}
                </h2>
                <p class="text-text-secondary mt-2 pl-5 border-l border-white/5 font-light">
                    {{ __('Confirm your deposit details and proceed to the secure Stripe checkout.') }}
                </p>
            </div>

            <div class="lg:col-span-2 grid grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                    class="bg-secondary-dark/40 backdrop-blur-md border border-white/10 rounded-xl p-4 flex flex-col justify-between relative overflow-hidden group">
                    <span
                        class="text-[10px] text-text-secondary uppercase tracking-widest font-bold z-10">{{ __('Deposit Amount') }}</span>
                    <div class="flex items-baseline gap-1 mt-1 z-10">
                        <span
                            class="text-xl font-mono font-bold text-white tracking-tight">{{ number_format($user_payment_details['amount'], 2) }}</span>
                        <span class="text-[10px] text-accent-primary font-bold ml-1">{{ getSetting('currency') }}</span>
                    </div>
                </div>
                <div
                    class="bg-secondary-dark/40 backdrop-blur-md border border-white/10 rounded-xl p-4 flex flex-col justify-between relative overflow-hidden group">
                    <span
                        class="text-[10px] text-text-secondary uppercase tracking-widest font-bold z-10">{{ __('Method') }}</span>
                    <div class="flex items-baseline gap-1 mt-1 z-10">
                        <span class="text-xl font-bold text-white tracking-tight">{{ __('Credit Card') }}</span>
                    </div>
                </div>
                <div
                    class="hidden lg:flex bg-secondary-dark/40 backdrop-blur-md border border-white/10 rounded-xl p-4 flex-col justify-between relative overflow-hidden group">
                    <span
                        class="text-[10px] text-text-secondary uppercase tracking-widest font-bold z-10">{{ __('Gateway') }}</span>
                    <div class="flex items-center gap-2 mt-1 z-10">
                        <span class="text-sm font-bold text-white">{{ __('Stripe') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Card -->
        <div class="max-w-2xl mx-auto w-full">
            <div
                class="bg-secondary-dark/60 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl relative group">
                <!-- Top Accent Gradient -->
                <div
                    class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-accent-primary to-transparent opacity-50">
                </div>

                <div class="p-8 md:p-12">
                    <div class="flex flex-col items-center mb-10">
                        <div
                            class="w-24 h-24 bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 flex items-center justify-center shadow-inner group-hover:border-accent-primary/30 transition-colors duration-500">
                            <img src="{{ asset('assets/images/deposit-methods/' . $payment_method->logo) }}" alt="Stripe"
                                class="w-full h-full object-contain filter drop-shadow">
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">{{ __('Confirm Transaction') }}</h2>
                        <p class="text-text-secondary text-sm mt-1 uppercase tracking-widest font-semibold opacity-60">
                            {{ __('Payment Review') }}</p>
                    </div>

                    <div class="space-y-6">
                        @php
                            $amount = $user_payment_details['amount'];
                            $feePercent = getSetting('deposit_fee', 0);
                            $feeAmount = ($amount * $feePercent) / 100;
                            $totalAmount = $amount + $feeAmount;
                        @endphp

                        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-text-secondary font-medium">{{ __('Deposit Amount') }}</span>
                                <span class="text-white font-mono font-bold text-lg">{{ showAmount($amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-text-secondary font-medium">{{ __('Processing Fee') }}
                                    ({{ $feePercent }}%)</span>
                                <span class="text-accent-primary font-mono font-bold">+{{ showAmount($feeAmount) }}</span>
                            </div>
                            <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent my-2"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-white font-bold text-xl">{{ __('Total to Pay') }}</span>
                                <div class="text-right">
                                    <span
                                        class="text-accent-primary font-mono font-bold text-2xl underline decoration-accent-primary/20 underline-offset-8">
                                        {{ showAmount($totalAmount) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sandbox / Test Mode Notice -->
                        @if ($is_test_mode)
                            <div class="animate-pulse">
                                <div
                                    class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6 space-y-4 shadow-[0_0_20px_rgba(245,158,11,0.1)]">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-amber-500/20 rounded-lg">
                                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-amber-500 font-bold text-sm uppercase tracking-wider">
                                                {{ __('Sandbox Mode Active') }}</h4>
                                            <p class="text-[10px] text-amber-500/60 font-medium">
                                                {{ __('Use the test details below for simulation.') }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-black/20 rounded-xl p-3 border border-white/5 group/card cursor-pointer copy-btn hover:bg-black/40 transition-colors"
                                            data-clipboard-text="4242424242424242">
                                            <div class="flex items-center justify-between mb-1">
                                                <span
                                                    class="text-[9px] uppercase font-bold text-white/30 tracking-widest block">{{ __('Test Card') }}</span>
                                                <svg class="w-3 h-3 text-white/20 group-hover/card:text-accent-primary transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                                                    </path>
                                                </svg>
                                            </div>
                                            <span
                                                class="text-white font-mono font-bold text-xs tracking-widest group-hover/card:text-accent-primary transition-colors">4242
                                                4242 4242 4242</span>
                                        </div>
                                        <div class="bg-black/20 rounded-xl p-3 border border-white/5">
                                            <span
                                                class="text-[9px] uppercase font-bold text-white/30 tracking-widest block mb-1">{{ __('Exp / CVC') }}</span>
                                            <span class="text-white font-mono font-bold text-xs tracking-widest">12/35 /
                                                123</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="flex items-start gap-4 p-4 bg-accent-primary/5 border border-accent-primary/10 rounded-xl">
                                <div class="p-2 bg-accent-primary/10 rounded-lg shrink-0">
                                    <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-xs text-text-secondary leading-relaxed italic mt-0.5">
                                    {{ __('You will be redirected to Stripe to complete your payment securely. Your funds will be credited to your account automatically upon successful confirmation.') }}
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('user.deposits.new.stripe-initialize') }}" method="POST" class="ajax-form"
                            data-action="redirect">
                            @csrf
                            <button type="submit"
                                class="group relative w-full py-5 bg-accent-primary hover:bg-accent-primary-hover text-white font-bold rounded-2xl transition-all shadow-[0_0_30px_rgba(var(--color-accent-primary),0.3)] hover:shadow-[0_0_40px_rgba(var(--color-accent-primary),0.5)] active:scale-[0.98] cursor-pointer">
                                <span class="flex items-center justify-center gap-3 text-lg">
                                    {{ __('Confirm & Pay with Stripe') }}
                                    <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    </div>

                    <!-- Trust Badges -->
                    <div
                        class="mt-8 flex flex-col items-center gap-5 opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
                        <div class="flex flex-wrap justify-center items-center gap-8">
                            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/visa.svg" alt="Visa"
                                class="h-3 md:h-4 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/mastercard.svg"
                                alt="Mastercard"
                                class="h-5 md:h-6 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/americanexpress.svg"
                                alt="Amex"
                                class="h-5 md:h-6 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                            <span
                                class="text-[10px] text-white/30 font-bold tracking-[0.3em] uppercase">{{ __('Secured by Stripe') }}</span>
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
