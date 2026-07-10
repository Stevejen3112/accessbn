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
                    {{ __('Confirm your deposit details and proceed to the secure Paystack checkout.') }}
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
                        <span
                            class="text-xl font-bold text-white tracking-tight">{{ $payment_method->type == 'card' ? __('Card') : ($payment_method->type == 'bank_transfer' ? __('Bank') : __('Wallet')) }}</span>
                    </div>
                </div>
                <div
                    class="hidden lg:flex bg-secondary-dark/40 backdrop-blur-md border border-white/10 rounded-xl p-4 flex-col justify-between relative overflow-hidden group">
                    <span
                        class="text-[10px] text-text-secondary uppercase tracking-widest font-bold z-10">{{ __('Gateway') }}</span>
                    <div class="flex items-center gap-2 mt-1 z-10">
                        <span class="text-sm font-bold text-white">{{ __('Paystack') }}</span>
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
                            <img src="{{ asset('assets/images/deposit-methods/' . $payment_method->logo) }}" alt="Paystack"
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

                        <!-- Paystack Specific Info -->
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
                                {{ __('You will be redirected to Paystack to complete your payment securely. Your funds will be credited to your account automatically upon successful confirmation.') }}
                            </p>
                        </div>

                        <form action="{{ route('user.deposits.new.' . $payment_method->pay . '-validate') }}"
                            method="POST" class="ajax-form" data-action="redirect">
                            @csrf
                            <button type="submit"
                                class="group relative w-full py-5 bg-accent-primary hover:bg-accent-primary-hover text-white font-bold rounded-2xl transition-all shadow-[0_0_30px_rgba(var(--color-accent-primary),0.3)] hover:shadow-[0_0_40px_rgba(var(--color-accent-primary),0.5)] active:scale-[0.98] cursor-pointer">
                                <span class="flex items-center justify-center gap-3 text-lg">
                                    {{ __('Proceed to Paystack') }}
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
                            @if ($payment_method->type == 'card')
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/visa.svg"
                                    alt="Visa"
                                    class="h-3 md:h-4 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/mastercard.svg"
                                    alt="Mastercard"
                                    class="h-5 md:h-6 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                                <img src="https://cdn.brandfetch.io/idEnZSE2P5/w/400/h/400/theme/dark/icon.jpeg?c=1bxid64Mup7aczewSAYMX&t=1772380346551"
                                    alt="Verve"
                                    class="h-6 md:h-8 w-auto object-contain transition-all hover:scale-110 rounded-md">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/americanexpress.svg"
                                    alt="Amex"
                                    class="h-5 md:h-6 w-auto object-contain transition-all hover:scale-110 brightness-0 invert">
                            @elseif($payment_method->type == 'bank_transfer')
                                <div class="flex items-center gap-3 text-white group/icon">
                                    <div
                                        class="p-2 bg-white/5 rounded-lg border border-white/10 group-hover/icon:border-accent-primary/30 transition-colors">
                                        <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-[0.1em]">{{ __('All Commercial Banks') }}</span>
                                </div>
                            @else
                                <div class="flex flex-wrap justify-center items-center gap-6">
                                    <div class="flex items-center gap-3 text-white group/icon">
                                        <div
                                            class="p-2 bg-white/5 rounded-lg border border-white/10 group-hover/icon:border-accent-primary/30 transition-colors">
                                            <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-[0.1em]">{{ __('Mobile Money') }}</span>
                                    </div>
                                    <div class="w-px h-4 bg-white/10"></div>
                                    <div class="flex items-center gap-3 text-white group/icon">
                                        <div
                                            class="p-2 bg-white/5 rounded-lg border border-white/10 group-hover/icon:border-accent-primary/30 transition-colors">
                                            <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-[0.1em]">{{ __('USSD') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                            <span
                                class="text-[10px] text-white/30 font-bold tracking-[0.3em] uppercase">{{ __('Secured by Paystack') }}</span>
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
