@extends('templates.bento.blades.admin.layouts.admin')

@section('content')
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('admin.trading-bots.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-text-secondary hover:text-white transition-colors mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>
                    {{ __('Back to Bots') }}
                </a>
                <h1 class="text-2xl font-bold text-white">{{ __('Create Trading Bot') }}</h1>
                <p class="text-sm text-text-secondary mt-1">
                    {{ __('Configure a new automated trading bot for users.') }}
                </p>
            </div>
            <button type="button"
                class="btn-save-bot hidden md:flex items-center gap-2 bg-accent-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ __('Save Bot') }}
            </button>
        </div>

        <form id="botForm" action="{{ route('admin.trading-bots.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('templates.bento.blades.admin.trading.bots.partials.bot-form')

            {{-- Mobile Submit Button --}}
            <div class="mt-8 flex md:hidden">
                <button type="button"
                    class="btn-save-bot w-full flex justify-center items-center gap-2 bg-accent-primary text-white px-6 py-3.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    {{ __('Save Bot') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').attr('src', e.target.result);
                    $('#logo-preview-container').removeClass('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function() {
            // Pair search handler
            $('#pair-search').on('input', function() {
                const search = $(this).val().toLowerCase();
                $('.pair-item').each(function() {
                    const name = $(this).data('pair-name');
                    if (name.includes(search)) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });
            });

            // Type change handler
            $('#type').on('change', function() {
                const type = $(this).val();
                if (type === 'forex') {
                    $('#exchanges-container').addClass('hidden');
                    $('#crypto-pairs').addClass('hidden');
                    $('#forex-pairs').removeClass('hidden');
                } else {
                    $('#exchanges-container').removeClass('hidden');
                    $('#crypto-pairs').removeClass('hidden');
                    $('#forex-pairs').addClass('hidden');
                }
            });

            // Save bot handler
            $('.btn-save-bot').on('click', function() {
                const $form = $('#botForm');
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<svg class="w-5 h-5 animate-spin mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');

                let formData = new FormData($form[0]);

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastNotification(response.message, 'success');
                            setTimeout(() => {
                                window.location.href = "{{ route('admin.trading-bots.index') }}";
                            }, 1500);
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
