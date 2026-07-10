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
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ __('Withdraw via Paystack') }}</h1>
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
                        <img src="{{ asset('assets/images/deposit-methods/paystack.png') }}" alt="Paystack"
                            class="w-full h-full object-contain">
                    </div>
                    <div>
                        <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                            {{ __('Withdrawal Amount') }}</p>
                        <h2 class="text-xl font-bold text-white">{{ showAmount($amount) }}</h2>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-emerald-400 uppercase tracking-widest font-bold mb-1">
                        {{ __('Status') }}</p>
                    <span class="px-2 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] rounded-lg border border-emerald-500/20 font-bold uppercase tracking-wider">
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
                <form id="paystack-withdrawal-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="withdrawal_request" value="{{ request('withdrawal_request') }}">

                    <div class="space-y-4">
                        <div class="flex flex-col gap-3">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Select Bank') }}
                            </label>
                            <select name="bank_code" class="select2-bank" id="bank_code">
                                <option value="">{{ __('Choose your bank...') }}</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-3">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Account Number') }}
                            </label>
                            <input type="text" name="account_number" id="account_number" maxlength="10"
                                placeholder="1234567890" class="flat-input">
                        </div>

                        <div class="flex flex-col gap-3 relative">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">
                                {{ __('Account Name') }}
                            </label>
                            <input type="text" name="account_name" id="account_name"
                                placeholder="{{ __('Resolving...') }}" class="flat-input uppercase font-bold text-emerald-400" readonly>
                            <div id="account-lookup-spinner" class="absolute right-4 bottom-4 hidden">
                                <svg class="animate-spin h-4 w-4 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <span class="text-[10px] text-slate-500 italic mt-1 ml-1">{{ __('Fetched automatically from bank records') }}</span>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn" disabled
                        class="w-full py-4 mt-8 bg-accent-primary hover:bg-accent-primary/90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-sm uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-accent-primary/20 flex items-center justify-center gap-3">
                        <span id="btn-text">{{ __('Confirm Withdrawal') }}</span>
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </button>
                    
                    <p class="text-[10px] text-slate-500 text-center opacity-70">
                        {{ __('Withdrawals via Paystack are processed instantly to your bank account.') }}
                    </p>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

    #submit-btn {
        cursor: pointer;
    }

    #submit-btn:disabled {
        cursor: not-allowed;
    }
    
    /* Select2 Dark Theme Overrides */
    .select2-container--default .select2-selection--single {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 1rem !important;
        height: 52px !important;
        display: flex !important;
        align-items: center !important;
        cursor: pointer !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: white !important;
        padding-left: 1.5rem !important;
        font-weight: 700 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px !important;
        right: 1rem !important;
    }
    .select2-dropdown {
        background: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 1rem !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        color: rgba(255, 255, 255, 0.7) !important;
        padding: 12px 20px !important;
        cursor: pointer !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: rgba(139, 92, 246, 0.5) !important;
        color: white !important;
    }
    .select2-search--dropdown .select2-search__field {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 0.5rem !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-bank').select2({
            width: '100%',
            placeholder: '{{ __("Choose your bank...") }}'
        });

        const $form = $('#paystack-withdrawal-form');
        const $accountNumber = $('#account_number');
        const $bankCode = $('#bank_code');
        const $accountName = $('#account_name');
        const $spinner = $('#account-lookup-spinner');
        const $submitBtn = $('#submit-btn');

        function resolveAccount() {
            const accountNumber = $accountNumber.val();
            const bankCode = $bankCode.val();

            if (accountNumber.length === 10 && bankCode) {
                $spinner.removeClass('hidden');
                $accountName.val('{{ __("Resolving...") }}');
                $submitBtn.prop('disabled', true);

                $.ajax({
                    url: '{{ route("user.withdrawals.paystack-resolve") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        account_number: accountNumber,
                        bank_code: bankCode
                    },
                    success: function(response) {
                        $spinner.addClass('hidden');
                        if (response.status === 'success') {
                            $accountName.val(response.account_name);
                            $submitBtn.prop('disabled', false);
                        } else {
                            $accountName.val('');
                            toastNotification(response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $spinner.addClass('hidden');
                        $accountName.val('');
                        const message = xhr.responseJSON?.message || '{{ __("Unable to resolve account") }}';
                        toastNotification(message, 'error');
                    }
                });
            } else {
                $accountName.val('');
                $submitBtn.prop('disabled', true);
            }
        }

        $accountNumber.on('input', resolveAccount);
        $bankCode.on('change', resolveAccount);

        $form.on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $submitBtn;
            const $btnText = $('#btn-text');
            const originalText = $btnText.text();
            
            $btn.prop('disabled', true);
            $btnText.text('{{ __("Processing...") }}');
            
            $.ajax({
                url: '{{ route("user.withdrawals.paystack-process") }}',
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
                        $btn.prop('disabled', false);
                        $btnText.text(originalText);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '{{ __("An unexpected error occurred") }}';
                    toastNotification(message, 'error');
                    $btn.prop('disabled', false);
                    $btnText.text(originalText);
                }
            });
        });
    });
</script>
@endpush
