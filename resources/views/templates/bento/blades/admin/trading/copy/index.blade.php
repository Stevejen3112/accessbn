@extends('templates.bento.blades.admin.layouts.admin')

@section('content')
    <div id="copy-trading-content" class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ __('Copy Trades') }}</h1>
                <p class="text-text-secondary">
                    {{ __('Manage master copy trading accounts and signals.') }}
                </p>
            </div>
            <a href="{{ route('admin.copy-trading.create') }}"
                class="bg-accent-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Add Copy Trade') }}
            </a>
        </div>

        {{-- Strategies Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($strategies as $strategy)
                <div class="bg-secondary relative border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition-colors flex flex-col group p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-xl bg-accent-primary/10 flex items-center justify-center text-accent-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] text-text-secondary uppercase tracking-widest font-black mb-1">{{ __('ROI') }}</span>
                            <span class="text-xl font-bold text-emerald-400 leading-none">{{ number_format($strategy->roi, 2) }}%</span>
                        </div>
                    </div>

                    @php
                        $isExpired = $strategy->expires_at && $strategy->expires_at < time();
                    @endphp
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-white mb-2">
                            {{ $strategy->code }}
                            <span class="text-xs font-normal text-text-secondary ml-2">({{ $strategy->pair }})</span>
                        </h3>
                        <p class="text-[10px] {{ $isExpired ? 'text-red-400 bg-red-400/10 border-red-400/20' : 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20' }} uppercase tracking-widest font-black inline-flex items-center gap-2 py-1 px-2 rounded-md border">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @if ($isExpired)
                                {{ __('Expired') }}
                            @else
                                {{ $strategy->expires_at ? __('Expires: ') . date('M d, Y H:i', $strategy->expires_at) : __('Never Expires') }}
                            @endif
                        </p>
                    </div>
                    <div class="mt-auto flex gap-3">
                        <a href="{{ route('admin.copy-trading.edit', $strategy->id) }}"
                            class="flex-1 py-2 text-sm text-accent-primary font-medium bg-accent-primary/10 rounded-lg hover:bg-accent-primary hover:text-white transition-all flex justify-center items-center gap-2 cursor-pointer border border-accent-primary/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('Edit') }}
                        </a>
                        <button type="button"
                            onclick="openSignalModal('{{ $strategy->id }}')"
                            class="flex-1 py-2 text-sm text-emerald-400 font-medium bg-emerald-400/10 rounded-lg hover:bg-emerald-400 hover:text-white transition-all flex justify-center items-center gap-4 cursor-pointer border border-emerald-400/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            {{ __('Signal') }}
                        </button>
                        <button type="button"
                            onclick="openDeleteModal('{{ $strategy->id }}', '{{ route('admin.copy-trading.delete', $strategy->id) }}')"
                            class="w-10 py-2 text-sm text-red-500 font-medium bg-red-500/10 rounded-lg hover:bg-red-500 hover:text-white transition-all flex justify-center items-center cursor-pointer border border-red-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center bg-secondary border border-white/5 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-accent-primary/10 text-accent-primary rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ __('No Copy Trades Found') }}</h3>
                    <p class="text-text-secondary max-w-md mx-auto mb-6">
                        {{ __('Create your first master copy trade to allow users to start trading.') }}
                    </p>
                    <a href="{{ route('admin.copy-trading.create') }}"
                        class="bg-accent-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all">
                        {{ __('Add Copy Trade') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $strategies->links('templates.bento.blades.partials.pagination') }}
        </div>
    </div>

    @include('templates.bento.blades.admin.trading.copy.partials.signal-modal')

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-secondary/90 backdrop-blur-sm z-[100] flex items-center justify-center p-4 transition-all duration-300">
        <div id="deleteModal-content" class="bg-secondary-dark border border-white/10 w-full max-w-md rounded-2xl shadow-2xl scale-95 opacity-0 transition-all duration-300 relative overflow-hidden">
            <div class="p-6 relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        {{ __('Confirm Deletion') }}
                    </h3>
                    <button type="button" onclick="closeModal('deleteModal')"
                        class="text-text-secondary hover:text-white transition-colors cursor-pointer modal-close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="mb-8">
                    <p class="text-text-secondary">
                        {{ __('Are you sure you want to delete this copy trade? This action cannot be undone.') }}</p>
                </div>

                <input type="hidden" id="delete-strategy-id">
                <input type="hidden" id="delete-strategy-url">

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('deleteModal')"
                        class="flex-1 px-4 py-3 rounded-xl border border-white/10 text-white font-medium hover:bg-white/5 transition-all cursor-pointer modal-close">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" id="confirm-delete-btn"
                        class="px-8 py-3 rounded-xl bg-red-500 text-white font-bold hover:bg-red-600 transition-all cursor-pointer">
                        {{ __('Delete Copy Trade') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.openSignalModal = function(id) {
            // Fetch signals
            $.ajax({
                url: "{{ route('admin.copy-trading.get-signal-messages', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        showSignalModal(response.data.messages);
                    }
                }
            });
        };

        window.openDeleteModal = function(id, url) {
            $('#delete-strategy-id').val(id);
            $('#delete-strategy-url').val(url);
            const $modal = $('#deleteModal');
            const $content = $('#deleteModal-content');
            $modal.removeClass('hidden');
            setTimeout(() => {
                $content.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);
        };

        window.closeModal = function(modalId) {
            const $modal = $('#' + modalId);
            const $content = $('#' + modalId + '-content');
            $content.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(() => {
                $modal.addClass('hidden');
            }, 300);
        };

        $('#confirm-delete-btn').on('click', function() {
            const url = $('#delete-strategy-url').val();
            const $btn = $(this);
            $btn.prop('disabled', true).html('<svg class="w-5 h-5 animate-spin mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastNotification(response.message, 'success');
                        location.reload();
                    } else {
                        toastNotification(response.message, 'error');
                        $btn.prop('disabled', false).text('{{ __('Yes, Delete It') }}');
                    }
                },
                error: function() {
                    toastNotification('{{ __('An error occurred.') }}', 'error');
                    $btn.prop('disabled', false).text('{{ __('Yes, Delete It') }}');
                }
            });
        });
    </script>
@endpush
