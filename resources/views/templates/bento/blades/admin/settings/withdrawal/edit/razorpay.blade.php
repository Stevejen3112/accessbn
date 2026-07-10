@extends('templates.bento.blades.admin.layouts.admin')

@push('css')
    <style>
        .settings-section {
            padding-bottom: 2rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

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

        .flat-input option {
            background: #1e293b;
            color: white;
        }

        .preview-logo {
            width: 80px;
            height: 80px;
            min-width: 80px;
            min-height: 80px;
            flex-shrink: 0;
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 1rem;
        }

        .preview-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: white;
        }

        .ip-badge {
            background: rgba(var(--accent-primary-rgb), 0.1);
            border: 1px solid rgba(var(--accent-primary-rgb), 0.2);
            padding: 0.35rem 0.75rem;
            border-radius: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--accent-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ip-badge:hover {
            background: rgba(var(--accent-primary-rgb), 0.2);
            border-color: rgba(var(--accent-primary-rgb), 0.4);
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col h-full animate-fade-up">
        <div class="flex flex-col lg:flex-row min-h-[calc(100vh-160px)]">
            <div class="w-full lg:w-80 shrink-0 border-b lg:border-b-0 lg:border-r border-white/5 flex flex-col pt-8 pr-8">
                <div id="sideBarSelector">
                    @include("templates.$template.blades.admin.settings.partials.sidebar")
                </div>
            </div>

            <div class="flex-1 flex flex-col pt-8 lg:pl-16 overflow-y-auto custom-scrollbar" id="contentScrollContainer">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-4 mb-12">
                        <a href="{{ route('admin.settings.withdrawal.index') }}"
                            class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div class="flex flex-col gap-1">
                            <h2 class="text-3xl font-light text-white tracking-tight leading-none">
                                {{ __('Edit Razorpay (RazorpayX)') }}
                            </h2>
                            <p class="text-slate-500 text-xs font-medium tracking-wide uppercase">
                                {{ __('Automatic Payout Gateway') }}
                            </p>
                        </div>
                    </div>

                    <form id="razorpay-form" action="{{ route('admin.settings.withdrawal.razorpay.update') }}"
                        method="POST" class="space-y-12 pb-24" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $gateway->id }}">

                        @include('templates.' . $template . '.blades.admin.settings.withdrawal.edit.partials.razorpayx_guide')

                        <div class="settings-section">
                            <div class="mb-8 border-b border-white/5 pb-4">
                                <h3 class="text-xl font-medium text-white tracking-wide">{{ __('API Configuration') }}</h3>
                            </div>

                            <div class="grid grid-cols-1 gap-x-12 gap-y-10">
                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Key ID') }}
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="key_id"
                                            value="{{ sandBoxCredentials(safeDecrypt(config('site.razorpayx.key_id'))) }}"
                                            placeholder="Enter Key ID" class="flat-input pr-12">
                                        <button type="button" class="password-toggle">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Key Secret') }}
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="key_secret"
                                            value="{{ sandBoxCredentials(safeDecrypt(config('site.razorpayx.key_secret'))) }}"
                                            placeholder="Enter Key Secret" class="flat-input pr-12">
                                        <button type="button" class="password-toggle">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Customer Identifier (Account Number)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="account_number"
                                            value="{{ sandBoxCredentials(safeDecrypt(config('site.razorpayx.account_number'))) }}"
                                            placeholder="e.g. 2323230018596645" class="flat-input">
                                    </div>
                                    <span class="text-[10px] text-slate-600 font-medium italic">
                                        {{ __('The Customer Identifier from your RazorpayX dashboard (Settings → Banking → Customer Identifier).') }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Default Currency') }}
                                    </label>
                                    <select name="default_currency" class="flat-input">
                                        @foreach (config('site.razorpay.currencies') as $currency)
                                            <option value="{{ $currency }}"
                                                {{ config('site.razorpay.default_currency') == $currency ? 'selected' : '' }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="mb-8 border-b border-white/5 pb-4">
                                <h3 class="text-xl font-medium text-white tracking-wide">{{ __('Gateway Information') }}
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Display Name') }}
                                    </label>
                                    <input type="text" name="name" value="{{ $gateway->name }}" required
                                        class="flat-input">
                                </div>

                                <div class="flex flex-col gap-3">
                                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ __('Logo') }}
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <div class="preview-logo">
                                            <img src="{{ asset('assets/images/withdrawal-methods/' . $gateway->logo) }}"
                                                alt="{{ $gateway->name }}">
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <input type="file" name="logo"
                                                class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-white/10 file:text-white hover:file:bg-white/20 transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="mb-8 border-b border-white/5 pb-4">
                                <h3 class="text-xl font-medium text-white tracking-wide">{{ __('Webhook Configuration') }}
                                </h3>
                            </div>

                            <div class="bg-accent-primary/5 border border-accent-primary/20 rounded-2xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="p-2 bg-accent-primary/10 rounded-lg shrink-0">
                                        <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <p class="text-xs text-slate-400 leading-relaxed font-medium">
                                            {{ __('To automate withdrawal status changes, add this webhook URL to your RazorpayX dashboard for the events: payout.processed, payout.reversed, and payout.failed.') }}
                                        </p>
                                        <div class="relative group">
                                            <input type="text" readonly
                                                value="{{ route('api.v1.webhooks.razorpay.withdrawal') }}"
                                                id="webhook-url"
                                                class="flat-input bg-black/40 border-white/5 text-accent-primary font-mono text-[10px] pr-20 py-3">
                                            <button type="button" onclick="copyToClipboard('webhook-url')"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-white/5 hover:bg-white/10 rounded-lg text-[9px] font-black uppercase text-white transition-all">
                                                {{ __('Copy') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="pt-10">


                        <div class="pt-10">
                            <button type="submit" id="submit-btn"
                                class="px-10 py-4 bg-accent-primary text-white text-xs font-bold uppercase tracking-[0.15em] rounded-xl hover:bg-accent-secondary active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-3 w-max">
                                <svg class="w-4 h-4 submit-icon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <svg class="w-4 h-4 hidden loading-icon animate-spin" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="btn-text">{{ __('Save RazorpayX Settings') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function copyToClipboard(elementId) {
            const copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            toastNotification("{{ __('URL copied to clipboard!') }}", "success");
        }

        $(document).ready(function() {
            // Setup Guide Toggle
            $('#toggle-guide').on('click', function() {
                const $guide = $('#setup-guide');
                const $text = $('#guide-toggle-text');
                const $chevron = $('#guide-chevron');

                if ($guide.hasClass('hidden')) {
                    $guide.removeClass('hidden');
                    $text.text('{{ __('Hide Guide') }}');
                    $chevron.addClass('rotate-180');
                } else {
                    $guide.addClass('hidden');
                    $text.text('{{ __('Show Guide') }}');
                    $chevron.removeClass('rotate-180');
                }
            });

            // IP Copy Logic
            $('.ip-badge').on('click', function() {
                const ip = $(this).data('ip');
                const $badge = $(this);
                const $text = $badge.find('span');
                const $icon = $badge.find('svg');
                const originalText = $text.text();

                navigator.clipboard.writeText(ip).then(() => {
                    $badge.addClass('bg-accent-primary/20 border-accent-primary/40');
                    $text.text('{{ __('Copied!') }}');
                    $icon.hide();

                    setTimeout(() => {
                        $badge.removeClass('bg-accent-primary/20 border-accent-primary/40');
                        $text.text(originalText);
                        $icon.show();
                    }, 2000);
                });
            });

            $('.password-toggle').on('click', function() {
                const $input = $(this).siblings('input');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);

                if (type === 'text') {
                    $(this).html(`
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    `);
                } else {
                    $(this).html(`
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    `);
                }
            });

            $('#razorpay-form').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $btn = $('#submit-btn');
                const $btnText = $btn.find('.btn-text');
                const $submitIcon = $btn.find('.submit-icon');
                const $loadingIcon = $btn.find('.loading-icon');
                const originalText = $btnText.text();

                const formData = new FormData(this);

                $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                $btnText.text('{{ __('Saving...') }}');
                $submitIcon.addClass('hidden');
                $loadingIcon.removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        toastNotification(response.message, 'success');

                        $btn.prop('disabled', false).removeClass(
                            'opacity-70 cursor-not-allowed');
                        $btnText.text(originalText);
                        $submitIcon.removeClass('hidden');
                        $loadingIcon.addClass('hidden');

                        if (response.logo_url) {
                            $('.preview-logo img').attr('src', response.logo_url);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON.message ||
                            '{{ __('Something went wrong. Please check your inputs.') }}';
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join(' | ');
                        }
                        toastNotification(errorMessage, 'error');
                        $btn.prop('disabled', false).removeClass(
                            'opacity-70 cursor-not-allowed');
                        $btnText.text(originalText);
                        $submitIcon.removeClass('hidden');
                        $loadingIcon.addClass('hidden');
                    }
                });
            });
        });
    </script>
@endsection
