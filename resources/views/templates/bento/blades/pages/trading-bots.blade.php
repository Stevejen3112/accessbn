@extends('templates.bento.blades.layouts.front')

@section('title', $page_title . ' - ' . getSetting('name'))
@section('page_title', $page_title)

@section('content')
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        .animate-float { animation: float 10s ease-in-out infinite; }

        @keyframes pulse-slow {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        .animate-pulse-slow { animation: pulse-slow 12s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

        @keyframes loading-bar {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }
        .animate-loading-bar { animation: loading-bar 2.5s infinite linear; }

        .group\/cinema:hover .animate-float { animation-duration: 5s; }

        /* Path Animations for Stream */
        @keyframes headerStep1 { 0% { stroke-dashoffset: 700; opacity: 0; } 5% { opacity: 1; } 30% { stroke-dashoffset: 0; opacity: 1; } 35% { opacity: 0; } 100% { stroke-dashoffset: 0; opacity: 0; } }
        @keyframes headerStep2 { 0%, 33% { stroke-dashoffset: 700; opacity: 0; } 38% { opacity: 1; } 63% { stroke-dashoffset: 0; opacity: 1; } 68% { opacity: 0; } 100% { stroke-dashoffset: 0; opacity: 0; } }
        @keyframes headerStep3 { 0%, 66% { stroke-dashoffset: 700; opacity: 0; } 71% { opacity: 1; } 96% { stroke-dashoffset: 0; opacity: 1; } 100% { stroke-dashoffset: 0; opacity: 0; } }
    </style>

    <div class="relative py-12 bg-[#05070A]">
        <div class="container mx-auto px-4 relative z-10">
            
            {{-- Mobile Filter Drawer --}}
            <div id="mobileFilterDrawer" class="fixed inset-y-0 left-0 z-[111] w-72 bg-[#0B0F1A]/95 backdrop-blur-xl border-r border-white/10 shadow-2xl transform -translate-x-full transition-transform duration-300 lg:hidden overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-white text-lg font-heading">{{ __('Bot Filters') }}</h3>
                        <button class="text-text-secondary hover:text-white cursor-pointer" onclick="$('#mobileFilterDrawer').addClass('-translate-x-full'); $('#mobileDrawerBackdrop').addClass('hidden');">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    {{-- Sort --}}
                    <div class="mb-8">
                        <label class="text-xs text-text-secondary uppercase tracking-wider font-bold mb-3 block">{{ __('Sort Alignment') }}</label>
                        <select class="sort-bots w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white outline-none">
                            <option value="featured">{{ __('Top Performance') }}</option>
                            <option value="roi_desc">{{ __('Highest Yield') }}</option>
                            <option value="type">{{ __('Algorithm Type') }}</option>
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div class="mb-8">
                        <label class="text-xs text-text-secondary uppercase tracking-wider font-bold mb-3 block">{{ __('Fleet Type') }}</label>
                        <div class="space-y-3">
                            @foreach ($botTypes as $type)
                                <label class="flex items-center gap-3 cursor-pointer group p-2 rounded-lg hover:bg-white/5 transition-colors">
                                    <input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 text-accent-primary focus:ring-accent-primary/50 filter-type" value="{{ $type }}">
                                    <span class="text-sm text-text-secondary group-hover:text-white capitalize">{{ __($type) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Overlay --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[50] hidden lg:hidden" id="mobileDrawerBackdrop" onclick="$('#mobileFilterDrawer').addClass('-translate-x-full'); $(this).addClass('hidden');"></div>

            {{-- Avant-Garde Prism Showcase --}}
            @if ($recommendedBots->isNotEmpty())
                <div class="relative w-full overflow-hidden rounded-[3rem] bg-[#030303] border border-white/5 shadow-2xl mb-24 min-h-[600px] flex items-center group/cinema">
                    {{-- Ambient Aurora --}}
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute -top-[50%] -left-[20%] w-[100%] h-[150%] bg-accent-primary/10 blur-[120px] rounded-full mix-blend-screen animate-pulse-slow"></div>
                        <div class="absolute top-[20%] -right-[20%] w-[80%] h-[120%] bg-accent-secondary/5 blur-[120px] rounded-full mix-blend-screen animate-float"></div>
                    </div>

                    <div class="relative z-10 w-full p-8 md:p-16">
                        <div class="grid lg:grid-cols-12 gap-16 items-center">
                            {{-- Content Column --}}
                            <div class="lg:col-span-12 text-center mb-12">
                                <span class="inline-block px-4 py-1.5 rounded-full border border-accent-primary/50 bg-accent-primary/10 text-accent-primary text-[10px] font-mono tracking-[0.4em] uppercase mb-6 backdrop-blur-md">
                                    // {{ __('FLEET_ADAPTIVE_V.04') }}
                                </span>
                                <h1 class="text-6xl md:text-8xl font-black text-white leading-none tracking-tighter mb-8">
                                    {{ __('Autonomous') }} <br>
                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent-primary via-white/80 to-accent-secondary">{{ __('Bot Fleet.') }}</span>
                                </h1>
                                <p class="text-text-secondary text-xl md:text-2xl leading-relaxed max-w-3xl mx-auto opacity-70">
                                    {{ $page_description }}
                                </p>
                            </div>

                            {{-- Featured Bots Grid --}}
                            <div class="lg:col-span-12">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    @foreach ($recommendedBots as $bot)
                                        @include('templates.bento.blades.pages.partials.bot_card', ['bot' => $bot])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main Content: Sidebar Filters + Fleet Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
                {{-- Filters Sidebar (Desktop) --}}
                <div class="hidden lg:block space-y-8">
                    <div class="bg-white/[0.02] backdrop-blur-md border border-white/5 rounded-[2.5rem] p-8 sticky top-24 shadow-2xl">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent-primary"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                            </div>
                            <h3 class="font-bold text-white text-xl">{{ __('Fleet Controls') }}</h3>
                        </div>

                        {{-- Sort --}}
                        <div class="mb-8">
                            <label class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-4 block pl-1 opacity-50">{{ __('Sort Order') }}</label>
                            <select class="sort-bots w-full appearance-none bg-white/5 border border-white/10 rounded-[1.2rem] px-5 py-4 text-sm text-white focus:border-accent-primary outline-none hover:bg-white/10 transition-all cursor-pointer">
                                <option value="featured">{{ __('Most Active') }}</option>
                                <option value="roi_desc">{{ __('Highest Daily ROI') }}</option>
                                <option value="type">{{ __('Algorithm Class') }}</option>
                            </select>
                        </div>

                        {{-- Type Filter --}}
                        <div class="mb-8">
                            <label class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-4 block pl-1 opacity-50">{{ __('Algorithm Class') }}</label>
                            <div class="space-y-3">
                                @foreach ($botTypes as $type)
                                    <label class="flex items-center gap-4 cursor-pointer group p-2 rounded-xl hover:bg-white/5 transition-all">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" class=" peer h-5 w-5 rounded-lg border-white/10 bg-white/5 text-accent-primary focus:ring-accent-primary/20 transition-all filter-type" value="{{ $type }}">
                                        </div>
                                        <span class="text-sm font-bold text-text-secondary group-hover:text-white capitalize transition-colors">{{ __($type) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Markets Filter --}}
                        <div class="mb-10">
                            <label class="text-[10px] text-text-secondary uppercase tracking-widest font-black mb-4 block pl-1 opacity-50">{{ __('Operational Markets') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach (array_slice($botMarkets->toArray(), 0, 15) as $market)
                                    <button class="px-3 py-2 rounded-xl text-[10px] font-black border border-white/5 bg-white/5 text-text-secondary hover:bg-white/10 hover:text-white hover:border-accent-primary/20 transition-all filter-market cursor-pointer" data-value="{{ $market }}">
                                        {{ $market }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <button id="resetBotFilters" class="w-full py-4 rounded-2xl text-[10px] font-black text-text-secondary hover:text-white hover:bg-white/5 transition-all cursor-pointer border border-white/10 hover:border-accent-primary/20 uppercase tracking-[0.2em]">
                            {{ __('System Reset') }}
                        </button>
                    </div>

                    {{-- Data Pulse Widget --}}
                    <div class="bg-[#0B0F17] rounded-[2rem] p-8 border border-white/5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 blur-3xl rounded-full"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">{{ __('Live Infrastructure') }}</span>
                            </div>
                            <div class="text-3xl font-black text-white mb-2">{{ count($botTrading['exchanges']) }}</div>
                            <div class="text-xs text-text-secondary">{{ __('Exchanges Integrated') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Bots Grid --}}
                <div class="lg:col-span-3 space-y-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-3xl font-black text-white uppercase tracking-tighter">{{ __('Active Deployment') }}</h2>
                        <button class="lg:hidden px-6 py-3 rounded-2xl bg-white/5 border border-white/10 text-white font-bold text-xs uppercase tracking-widest flex items-center gap-2" onclick="$('#mobileFilterDrawer').removeClass('-translate-x-full'); $('#mobileDrawerBackdrop').removeClass('hidden');">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                            {{ __('Filters') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8" id="botsContainer">
                        @foreach ($allBots as $bot)
                            @include('templates.bento.blades.pages.partials.bot_card', ['bot' => $bot])
                        @endforeach
                    </div>

                    {{-- Empty State --}}
                    <div id="noBotsFound" class="hidden text-center py-32 bg-white/[0.02] border border-white/5 rounded-[3rem]">
                        <div class="inline-flex p-8 rounded-full bg-white/5 mb-8 border border-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-white/20"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-4">{{ __('No Bots Matching Parameters') }}</h3>
                        <p class="text-text-secondary mb-8 max-w-md mx-auto">{{ __('Try adjusting your deployment filters to see other available algorithms.') }}</p>
                        <button onclick="$('#resetBotFilters').click()" class="px-8 py-4 rounded-2xl bg-accent-primary text-white font-black uppercase text-xs tracking-widest hover:scale-105 transition-transform">{{ __('Clear System Filters') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let activeTypeFilters = [];
            let activeMarketFilters = [];

            // Sort Handler
            $('.sort-bots').on('change', function() {
                const sortType = $(this).val();
                $('.sort-bots').val(sortType);
                
                const container = $('#botsContainer');
                const cards = container.children('.bot-card').get();

                cards.sort(function(a, b) {
                    const $a = $(a);
                    const $b = $(b);

                    if (sortType === 'roi_desc') return $b.data('roi') - $a.data('roi');
                    if (sortType === 'type') return $a.data('type').localeCompare($b.data('type'));
                    return $b.data('id') - $a.data('id');
                });

                $.each(cards, function(idx, card) {
                    container.append(card);
                });
            });

            // Type Filter
            $('.filter-type').on('change', function() {
                const val = $(this).val();
                const isChecked = $(this).is(':checked');
                $(`.filter-type[value="${val}"]`).prop('checked', isChecked);

                const allChecked = $('.filter-type:checked').map(function() { return $(this).val(); }).get();
                activeTypeFilters = [...new Set(allChecked)];
                applyBotFilters();
            });

            // Market Filter
            $('.filter-market').on('click', function() {
                const val = $(this).data('value');
                const isAdding = !activeMarketFilters.includes(val);
                const $buttons = $(`.filter-market[data-value="${val}"]`);

                if (!isAdding) {
                    activeMarketFilters = activeMarketFilters.filter(i => i !== val);
                    $buttons.removeClass('bg-accent-primary text-white border-accent-primary');
                } else {
                    activeMarketFilters.push(val);
                    $buttons.addClass('bg-accent-primary text-white border-accent-primary');
                }
                applyBotFilters();
            });

            // Reset
            $('#resetBotFilters').on('click', function() {
                $('.filter-type').prop('checked', false);
                $('.filter-market').removeClass('bg-accent-primary text-white border-accent-primary');
                activeTypeFilters = [];
                activeMarketFilters = [];
                $('.sort-bots').val('featured').trigger('change');
                applyBotFilters();
            });

            function applyBotFilters() {
                let visibleCount = 0;
                $('#botsContainer .bot-card').each(function() {
                    const $card = $(this);
                    const type = $card.data('type');
                    const markets = $card.data('markets'); // array

                    const passType = activeTypeFilters.length === 0 || activeTypeFilters.includes(type);
                    const passMarket = activeMarketFilters.length === 0 || activeMarketFilters.some(m => markets.includes(m));

                    if (passType && passMarket) {
                        $card.show();
                        visibleCount++;
                    } else {
                        $card.hide();
                    }
                });

                if (visibleCount === 0) $('#noBotsFound').removeClass('hidden');
                else $('#noBotsFound').addClass('hidden');
            }
        });
    </script>
@endpush
