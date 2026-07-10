@extends('templates.bento.blades.admin.layouts.admin')

@section('content')
    <div id="bots-content" class="space-y-8">
        {{-- Message if module is disabled --}}
        @if (!moduleEnabled('trading_bot_module'))
            <div class="relative z-10 p-8 flex flex-col items-center justify-center text-center h-[300px] bg-secondary/40 border border-white/5 rounded-[2.5rem] shadow-2xl overflow-hidden backdrop-blur-xl">
                <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <h4 class="text-sm font-black text-white uppercase tracking-widest mb-2">
                    {{ __('Trading Bot module disabled') }}</h4>
                <p class="text-[10px] text-slate-500 max-w-[200px] mb-6 font-medium leading-relaxed italic">
                    {{ __('Trading Bot module is disabled. Please enable it in settings to manage bots.') }}
                </p>
                <a href="{{ route('admin.settings.modules.index') }}"
                    class="px-5 py-2 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black text-white uppercase tracking-widest hover:bg-white/10 hover:border-white/20 transition-all active:scale-95">
                    {{ __('Settings') }}
                </a>
            </div>
        @endif

        @if (moduleEnabled('trading_bot_module'))
            {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ __('Global Trading Bots') }}</h1>
                <p class="text-sm text-text-secondary mt-1">
                    {{ __('Manage and configure automated trading bots for crypto and forex markets.') }}
                </p>
            </div>
            <a href="{{ route('admin.trading-bots.create') }}"
                class="flex items-center gap-2 bg-accent-primary text-white px-5 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Create New Bot') }}
            </a>
        </div>

        {{-- Bots Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($bots as $bot)
                <div
                    class="bg-secondary relative border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition-colors flex flex-col group">
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-4 right-4 z-20">
                        <span
                            class="px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest border {{ $bot->is_active ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-500' }}">
                            {{ $bot->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>

                    {{-- Top Section --}}
                    <div class="relative z-10 p-6 border-b border-white/5 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden">
                            @if(str_starts_with($bot->logo, 'bot-'))
                                <img src="{{ asset('assets/images/bots/' . $bot->logo) }}" alt="{{ $bot->name }}" class="w-10 h-10 object-contain">
                            @else
                                <img src="{{ asset('assets/images/bots/' . $bot->logo) }}" alt="{{ $bot->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">
                                {{ $bot->name }}
                            </h3>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-widest text-text-secondary">
                                {{ $bot->type }}
                            </span>
                        </div>
                    </div>

                    {{-- Metrics --}}
                    <div class="relative z-10 p-6 grid grid-cols-2 gap-4 bg-white/[0.01]">
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                {{ __('Daily Return') }}</p>
                            <p class="text-lg font-bold text-emerald-400">
                                {{ number_format($bot->daily_return_min, 2) }}% - {{ number_format($bot->daily_return_max, 2) }}%
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                {{ __('Duration') }}</p>
                            <p class="text-lg font-bold text-white">
                                {{ $bot->duration }} {{ __($bot->duration_type . '(s)') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                {{ __('Min Amount') }}</p>
                            <p class="font-medium text-white">{{ showAmount($bot->min_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                {{ __('Max Amount') }}</p>
                            <p class="font-medium text-white">{{ showAmount($bot->max_amount) }}</p>
                        </div>
                    </div>

                    {{-- Traded Pairs & Exchanges --}}
                    <div class="relative z-10 p-6 border-t border-white/5 flex-1 flex flex-col justify-start space-y-4">
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-2">{{ __('Traded Pairs') }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(array_slice($bot->traded_pairs, 0, 5) as $pair)
                                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[9px] uppercase tracking-wider font-bold">
                                        {{ $pair }}
                                    </span>
                                @endforeach
                                @if(count($bot->traded_pairs) > 5)
                                    <span class="px-2 py-0.5 rounded bg-white/5 text-text-secondary border border-white/10 text-[9px] uppercase tracking-wider font-bold">
                                        +{{ count($bot->traded_pairs) - 5 }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($bot->type === 'crypto' && $bot->exchanges)
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-2">{{ __('Exchanges') }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(array_slice($bot->exchanges, 0, 3) as $exchange)
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 border border-amber-500/20 text-[9px] uppercase tracking-wider font-bold">
                                        {{ $exchange }}
                                    </span>
                                @endforeach
                                @if(count($bot->exchanges) > 3)
                                    <span class="px-2 py-0.5 rounded bg-white/5 text-text-secondary border border-white/10 text-[9px] uppercase tracking-wider font-bold">
                                        +{{ count($bot->exchanges) - 3 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Trading Days --}}
                        <div>
                            <p class="text-[10px] text-text-secondary uppercase tracking-widest font-bold mb-2">{{ __('Trading Schedule') }}</p>
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    $botDays = is_array($bot->trading_days) ? $bot->trading_days : [];
                                @endphp
                                @foreach($allDays as $day)
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold {{ in_array($day, $botDays) ? 'bg-accent-primary/20 text-accent-primary border border-accent-primary/30' : 'bg-white/5 text-text-secondary/30 border border-white/5' }}">
                                        {{ substr($day, 0, 3) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions Footer --}}
                    <div
                        class="relative z-10 p-4 border-t border-white/5 bg-secondary flex justify-between gap-3 mt-auto transition-colors group-hover:bg-white/[0.02]">
                        <a href="{{ route('admin.trading-bots.edit', $bot->id) }}"
                            class="flex-1 py-2 text-sm text-white font-medium bg-white/5 rounded-lg hover:bg-white/10 hover:text-accent-primary transition-all flex justify-center items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            {{ __('Edit') }}
                        </a>
                        <button type="button"
                            onclick="openDeleteBotModal('{{ $bot->id }}', '{{ route('admin.trading-bots.delete', $bot->id) }}')"
                            class="flex-1 py-2 text-sm text-red-500 font-medium bg-red-500/10 rounded-lg hover:bg-red-500 hover:text-white transition-all flex justify-center items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full py-16 flex flex-col items-center justify-center bg-secondary border border-white/5 rounded-2xl text-center">
                    <div
                        class="w-16 h-16 bg-accent-primary/10 text-accent-primary rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ __('No Trading Bots Found') }}</h3>
                    <p class="text-text-secondary max-w-md mx-auto mb-6">
                        {{ __('You have not created any trading bots yet. Create your first bot to allow users to start automated trading.') }}
                    </p>
                    <a href="{{ route('admin.trading-bots.create') }}"
                        class="bg-accent-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-accent-primary/90 transition-all">
                        {{ __('Create Your First Bot') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $bots->links('templates.bento.blades.partials.pagination') }}
        </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal"
        class="hidden fixed inset-0 bg-secondary/90 backdrop-blur-sm z-[100] flex items-center justify-center p-4 transition-all duration-300">
        <div id="deleteModal-content"
            class="bg-secondary-dark border border-white/10 w-full max-w-md rounded-2xl shadow-2xl scale-95 opacity-0 transition-all duration-300 relative overflow-hidden">
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
                        {{ __('Are you sure you want to delete this trading bot? This action cannot be undone.') }}</p>
                </div>

                <input type="hidden" id="delete-bot-id">
                <input type="hidden" id="delete-bot-url">

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('deleteModal')"
                        class="flex-1 px-4 py-3 rounded-xl border border-white/10 text-white font-medium hover:bg-white/5 transition-all cursor-pointer modal-close">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" id="confirm-delete-btn"
                        class="flex-1 px-4 py-3 rounded-xl bg-red-500 text-white font-bold hover:bg-red-600 shadow-lg shadow-red-500/20 transition-all cursor-pointer">
                        {{ __('Yes, Delete It') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.openDeleteBotModal = function(id, url) {
            $('#delete-bot-id').val(id);
            $('#delete-bot-url').val(url);
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
            const url = $('#delete-bot-url').val();
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
