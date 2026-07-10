@extends('templates.bento.blades.admin.layouts.admin')
 
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 Flat Styling */
        .select2-container--default .select2-selection--single {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 1rem !important;
            height: 56px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.3s ease !important;
        }
 
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: rgba(var(--accent-primary-rgb), 0.5) !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }
 
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: white !important;
            padding-left: 1.5rem !important;
            padding-right: 2.5rem !important;
            width: 100% !important;
            font-weight: 700 !important;
            font-size: 16px !important;
        }
 
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 1rem !important;
            display: flex !important;
            align-items: center !important;
        }
 
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: rgba(255, 255, 255, 0.4) transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
 
        .select2-container--open .select2-dropdown {
            background: #151525 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 1rem !important;
            margin-top: 8px !important;
            overflow: hidden !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5) !important;
            z-index: 9999 !important;
        }
 
        .select2-search--dropdown {
            padding: 1rem !important;
            background: transparent !important;
        }
 
        .select2-search--dropdown .select2-search__field {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            color: white !important;
            padding: 0.75rem 1rem !important;
            outline: none !important;
            font-size: 16px !important;
        }
 
        .select2-search--dropdown .select2-search__field:focus {
            border-color: rgba(var(--accent-primary-rgb), 0.5) !important;
        }
 
        .select2-results__option {
            padding: 0.75rem 1.5rem !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02) !important;
        }
 
        .select2-results__option--highlighted[aria-selected] {
            background: rgba(var(--accent-primary-rgb), 0.1) !important;
        }
 
        .select2-results__option[aria-selected=true] {
            background: rgba(var(--accent-primary-rgb), 0.2) !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('admin.copy-trading.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-text-secondary hover:text-white transition-colors mb-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    {{ __('Back to Strategies') }}
                </a>
                <h1 class="text-2xl font-black text-white">
                    {{ __('New Copy Trade') }}
                </h1>
                <p class="text-text-secondary italic">
                    {{ __('Create a new master copy trade for signals.') }}
                </p>
            </div>
            <button type="button"
                class="btn-save-strategy hidden md:flex items-center gap-2 bg-accent-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ __('Save Strategy') }}
            </button>
        </div>

        <form id="strategyForm" action="{{ route('admin.copy-trading.store') }}" method="POST">
            @csrf
            @include('templates.bento.blades.admin.trading.copy.partials.form')

            {{-- Mobile Submit Button --}}
            <div class="mt-8 flex md:hidden">
                <button type="button"
                    class="btn-save-strategy w-full flex justify-center items-center gap-2 bg-accent-primary text-white px-6 py-3.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    {{ __('Save Strategy') }}
                </button>
            </div>
        </form>
    </div>

    @include('templates.bento.blades.admin.trading.copy.partials.signal-modal')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-pairs, .select2-basic').select2({
                width: '100%',
                dropdownParent: $('#strategyForm')
            });

            $('#amount_type').on('change', function() {
                if ($(this).val() === 'percentage') {
                    $('#percentage_container').removeClass('hidden');
                } else {
                    $('#percentage_container').addClass('hidden');
                }
            });

            $('.btn-save-strategy').on('click', function() {
                const $form = $('#strategyForm');
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<svg class="w-5 h-5 animate-spin mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastNotification(response.message, 'success');
                            showSignalModal(response.data.messages, true);
                        } else {
                            toastNotification(response.message, 'error');
                            $btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(key => {
                                toastNotification(errors[key][0], 'error');
                            });
                        } else {
                            toastNotification('{{ __('An error occurred.') }}', 'error');
                        }
                    }
                });
            });
        });
    </script>
@endpush
