@extends('templates.bento.blades.layouts.user')

@section('content')
    <div class="max-w-xl mx-auto space-y-8">
        {{-- Header Section --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('user.withdrawals.new') }}"
                class="p-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-text-secondary hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ __('Withdraw via RazorpayX') }}</h1>
                <p class="text-text-secondary text-sm mt-1 uppercase tracking-wider">{{ __('Automatic Transfer') }}</p>
            </div>
        </div>

        {{-- Withdrawal Summary Card --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('{{ asset('/assets/images/noise.svg') }}')] opacity-10 pointer-events-none">
            </div>
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center border border-white/10 p-2">
                        <img src="{{ asset('assets/images/withdrawal-methods/razorpay.png') }}" alt="Razorpay"
                            class="w-full h-full object-contain filter drop-shadow">
                    </div>
                    <div>
                        <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                            {{ __('Withdrawal Amount') }}</p>
                        <h2 class="text-xl font-bold text-white">{{ showAmount($amount) }}</h2>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-accent-primary uppercase tracking-widest font-bold mb-1">
                        {{ __('Status') }}</p>
                    <span class="px-2 py-1 bg-accent-primary/10 text-accent-primary text-[10px] rounded-lg border border-accent-primary/20 font-bold uppercase tracking-wider">
                        {{ __('Available') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Bank Details Form --}}
        <div class="bg-secondary border border-white/5 rounded-2xl p-8 relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-[url('{{ asset('/assets/images/noise.svg') }}')] opacity-10 pointer-events-none">
            </div>
            <div class="relative z-10">
                <form id="razorpay-withdrawal-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="withdrawal_request" value="{{ request('withdrawal_request') }}">

                    <div class="space-y-4">
                        <div class="flex flex-col gap-3">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Bank IFSC Code') }}
                            </label>
                            <input type="text" name="ifsc" id="ifsc" required
                                placeholder="e.g. HDFC0001234" class="flat-input uppercase">
                            <span class="text-[10px] text-slate-500 italic mt-1 ml-1">{{ __('11-character alphanumeric code') }}</span>
                        </div>

                        <div class="flex flex-col gap-3">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Account Number') }}
                            </label>
                            <input type="text" name="account_number" id="account_number" required
                                placeholder="Enter account number" class="flat-input">
                        </div>

                        <div class="flex flex-col gap-3 relative">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Account Name') }}
                            </label>
                            <input type="text" name="account_name" id="account_name" required
                                placeholder="Enter exact account name" class="flat-input" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}">
                            <span class="text-[10px] text-slate-500 italic mt-1 ml-1">{{ __('The name must match the bank records exactly to avoid rejection.') }}</span>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full py-4 mt-8 bg-accent-primary hover:bg-accent-primary-hover text-white font-black text-sm uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-accent-primary/20 flex items-center justify-center gap-3 active:scale-[0.98]">
                        <span id="btn-text">{{ __('Confirm Withdrawal') }}</span>
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                        <svg class="animate-spin h-5 w-5 hidden ml-2" id="spinner-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    
                    @if (config('app.env') === 'sandbox' || config('app.env') === 'local')
                        <div class="mt-4 p-4 rounded-xl border border-dashed border-amber-500/30 bg-amber-500/5 relative">
                            <div class="absolute -top-2.5 left-4 bg-secondary-dark px-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-amber-500">{{ __('Sandbox Payout Details') }}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2 text-xs font-mono text-amber-500/80">
                                <div>
                                    <span class="block opacity-50">{{ __('IFSC Code') }}:</span>
                                    <span class="text-amber-400 cursor-pointer hover:text-amber-300" onclick="navigator.clipboard.writeText('HDFC0001234'); toastNotification('Copied IFSC', 'success')" title="Click to copy">HDFC0001234</span>
                                </div>
                                <div>
                                    <span class="block opacity-50">{{ __('Account Number') }}:</span>
                                    <span class="text-amber-400 cursor-pointer hover:text-amber-300" onclick="navigator.clipboard.writeText('11214311215411'); toastNotification('Copied Account Number', 'success')" title="Click to copy">11214311215411</span>
                                    <span class="opacity-50 text-[10px] ml-1">(Or any 9-18 digit number)</span>
                                </div>
                                <div class="mt-2 text-[10px] opacity-70 italic leading-snug">
                                    {{ __('In test mode, RazorpayX processes payouts to these details instantly. You will receive a payout.processed webhook shorty after submitting.') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-[10px] text-slate-500 text-center opacity-70">
                            {{ __('Withdrawals via RazorpayX are processed to your bank account via IMPS/NEFT automatically.') }}
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .flat-input {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        color: white;
        font-weight: 700;
        transition: all 0.3s ease;
        outline: none;
        width: 100%;
    }

    .flat-input:focus {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(var(--accent-primary-rgb), 0.5);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#razorpay-withdrawal-form');
        const $submitBtn = $('#submit-btn');
        const $btnText = $('#btn-text');
        const $spinner = $('#spinner-icon');

        $form.on('submit', function(e) {
            e.preventDefault();
            
            const originalText = $btnText.text();
            
            $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            $btnText.text('{{ __("Processing...") }}');
            $spinner.removeClass('hidden');
            
            $.ajax({
                url: '{{ route("user.withdrawals.razorpay-process") }}',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        toastNotification(response.message, 'success');
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 2000);
                    } else {
                        toastNotification(response.message, 'error');
                        $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        $btnText.text(originalText);
                        $spinner.addClass('hidden');
                    }
                },
                error: function(xhr) {
                    let message = '{{ __("An unexpected error occurred") }}';
                    if (xhr.responseJSON) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' | ');
                        } else {
                            message = xhr.responseJSON.message || message;
                        }
                    }
                    toastNotification(message, 'error');
                    $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $btnText.text(originalText);
                    $spinner.addClass('hidden');
                }
            });
        });
    });
</script>
@endpush
