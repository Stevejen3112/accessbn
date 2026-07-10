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
                    {{ __('Confirm your deposit details and proceed to the secure Razorpay checkout.') }}
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
                        <span class="text-xl font-bold text-white tracking-tight">{{ __('Card / Netbanking / UPI') }}</span>
                    </div>
                </div>
                <div
                    class="hidden lg:flex bg-secondary-dark/40 backdrop-blur-md border border-white/10 rounded-xl p-4 flex-col justify-between relative overflow-hidden group">
                    <span
                        class="text-[10px] text-text-secondary uppercase tracking-widest font-bold z-10">{{ __('Gateway') }}</span>
                    <div class="flex items-center gap-2 mt-1 z-10">
                        <span class="text-sm font-bold text-white">{{ __('Razorpay') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Card -->
        <div class="max-w-2xl mx-auto w-full">
            <div
                class="bg-secondary-dark/60 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl relative group">
                <div
                    class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-accent-primary to-transparent opacity-50">
                </div>

                <div class="p-8 md:p-12">
                    <div class="flex flex-col items-center mb-10">
                        <div
                            class="w-24 h-24 bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 flex items-center justify-center shadow-inner group-hover:border-accent-primary/30 transition-colors duration-500">
                            <img src="{{ asset('assets/images/deposit-methods/' . $payment_method->logo) }}" alt="Razorpay"
                                class="w-full h-full object-contain filter drop-shadow">
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">{{ __('Confirm Transaction') }}</h2>
                        <p class="text-text-secondary text-sm mt-1 uppercase tracking-widest font-semibold opacity-60">
                            {{ __('Payment Review') }}
                        </p>
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
                                {{ __('You will be prompted by Razorpay to complete your payment securely. Your funds will be credited to your account automatically upon successful confirmation.') }}
                            </p>
                        </div>

                        <!-- Razorpay Payment Button -->
                        <button id="rzp-button"
                            class="group relative w-full py-5 bg-accent-primary hover:bg-accent-primary-hover text-white font-bold rounded-2xl transition-all shadow-[0_0_30px_rgba(var(--color-accent-primary),0.3)] hover:shadow-[0_0_40px_rgba(var(--color-accent-primary),0.5)] active:scale-[0.98] cursor-pointer">
                            <span class="flex flex-row items-center justify-center gap-3 text-lg btn-text">
                                {{ __('Proceed to Razorpay') }}
                                <svg class="w-6 h-6 transition-transform group-hover:translate-x-2" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </span>
                            <div class="hidden loading-spinner">
                                <svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </button>

                        @if (config('app.env') === 'sandbox')
                            <div
                                class="mt-4 p-4 rounded-xl border border-dashed border-amber-500/30 bg-amber-500/5 relative">
                                <div class="absolute -top-2.5 left-4 bg-secondary-dark px-2">
                                    <span
                                        class="text-[9px] font-black uppercase tracking-widest text-amber-500">{{ __('Sandbox Cards') }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-xs font-mono text-amber-500/80">
                                    <div>
                                        <span class="block opacity-50">{{ __('Test Success Card') }}:</span>
                                        <span class="text-amber-400 cursor-pointer hover:text-amber-300"
                                            onclick="navigator.clipboard.writeText('4111111111111111'); toastNotification('Copied card number', 'success')"
                                            title="Click to copy">4111 1111 1111 1111</span><br>
                                        <span class="opacity-50">CVV:</span> <span
                                            class="text-amber-400 cursor-pointer hover:text-amber-300"
                                            onclick="navigator.clipboard.writeText('123'); toastNotification('Copied CVV', 'success')"
                                            title="Click to copy">123</span> | <span class="opacity-50">EXP:</span> <span
                                            class="text-amber-400">12/50</span>
                                    </div>
                                    <div>
                                        <span class="block opacity-50">{{ __('Test Bank (Netbanking)') }}:</span>
                                        <span class="text-amber-400">Any Bank</span><br>
                                        <span class="opacity-50">Status:</span> <span class="text-amber-400">Success</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="opacity-50">{{ __('Test OTP') }}:</span> <span
                                            class="text-amber-400 cursor-pointer hover:text-amber-300"
                                            onclick="navigator.clipboard.writeText('1226'); toastNotification('Copied OTP', 'success')"
                                            title="Click to copy">1226</span>
                                        <span class="opacity-50 ml-2">(Or click Success on popup)</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Trust Badges -->
                    <div
                        class="mt-8 flex flex-col items-center gap-5 opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
                        <div class="flex items-center gap-4">
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                            <span
                                class="text-[10px] text-white/30 font-bold tracking-[0.3em] uppercase">{{ __('Secured by Razorpay') }}</span>
                            <div class="h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        let pollingInterval = null;

        function startPolling(transactionReference) {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(function() {
                $.ajax({
                    url: "{{ route('user.deposits.new.razorpay-check-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        transaction_reference: transactionReference
                    },
                    success: function(response) {
                        if (response.status === 'success' && response.payment_status !== 'pending') {
                            clearInterval(pollingInterval);
                            window.location.href = response.redirect;
                        }
                    }
                });
            }, 2000);
        }

        $('#rzp-button').on('click', function(e) {
            e.preventDefault();

            let $btn = $(this);
            $btn.find('.btn-text').addClass('hidden');
            $btn.find('.loading-spinner').removeClass('hidden');
            $btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

            $.ajax({
                url: "{{ route('user.deposits.new.razorpay-initialize') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status === 'success') {
                        startPolling(response.transaction_reference);
                        var options = {
                            "key": "{{ $razorpay_key }}",
                            "amount": response.amount,
                            "currency": response.currency,
                            "name": response.name,
                            "description": response.description,
                            "image": response.image,
                            "order_id": response.order_id,
                            "handler": function(rzp_response) {
                                // verify the payment
                                verifyPayment(rzp_response);
                            },
                            "prefill": {
                                "name": response.prefill.name,
                                "email": response.prefill.email,
                                "contact": response.prefill.contact
                            },
                            "theme": {
                                "color": "#8b5cf6"
                            },
                            "modal": {
                                "ondismiss": function() {
                                    $btn.find('.btn-text').removeClass('hidden');
                                    $btn.find('.loading-spinner').addClass('hidden');
                                    $btn.prop('disabled', false).removeClass(
                                        'opacity-50 cursor-not-allowed');
                                }
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else {
                        toastNotification(response.message, 'error');
                        $btn.find('.btn-text').removeClass('hidden');
                        $btn.find('.loading-spinner').addClass('hidden');
                        $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message :
                        "{{ __('Something went wrong') }}";
                    toastNotification(msg, 'error');
                    $btn.find('.btn-text').removeClass('hidden');
                    $btn.find('.loading-spinner').addClass('hidden');
                    $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            });
        });

        function verifyPayment(response) {
            let $btn = $('#rzp-button');
            $.ajax({
                url: "{{ route('user.deposits.new.razorpay-verify') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                },
                success: function(verifyResp) {
                    if (verifyResp.status === 'success') {
                        if (pollingInterval) clearInterval(pollingInterval);
                        toastNotification(verifyResp.message, 'success');
                        setTimeout(() => {
                            window.location.href = verifyResp.redirect;
                        }, 1500);
                    } else {
                        toastNotification(verifyResp.message, 'error');
                        $btn.find('.btn-text').removeClass('hidden');
                        $btn.find('.loading-spinner').addClass('hidden');
                        $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message :
                        "{{ __('Payment verification failed.') }}";
                    toastNotification(msg, 'error');
                    $btn.find('.btn-text').removeClass('hidden');
                    $btn.find('.loading-spinner').addClass('hidden');
                    $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            });
        }
    </script>
@endsection
