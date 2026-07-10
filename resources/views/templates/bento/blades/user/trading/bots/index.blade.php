@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <div class="space-y-8 pb-20">
        {{-- Header Section --}}
        <div class="relative overflow-hidden bg-secondary border border-white/5 rounded-3xl p-8 sm:p-12">
            <div class="relative z-10 max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">
                    {{ __('Next-Gen') }} <span class="text-accent-primary">{{ __('AI Trading Bots') }}</span>
                </h1>
                <p class="text-text-secondary text-base sm:text-lg leading-relaxed mb-8">
                    {{ __('Automate your trading with our professional AI bots. Select a strategy that fits your risk profile and start earning passive income today.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-5 py-3">
                        <div class="w-10 h-10 rounded-xl bg-accent-primary/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-primary" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] text-text-secondary uppercase tracking-widest font-bold">
                                {{ __('Active Bots') }}</div>
                            <div class="text-lg font-black text-white">{{ $bots->total() }}</div>
                        </div>
                    </div>

                    <a href="{{ route('user.trading-bots.activations') }}"
                        class="group relative flex items-center gap-4 bg-white/5 border border-white/10 hover:border-accent-primary/50 rounded-2xl p-1 pr-4 transition-all hover:shadow-[0_0_20px_-5px_rgba(var(--accent-primary-rgb),0.3)]">
                        <div class="flex items-center gap-3 pr-2">
                            <div
                                class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                                <svg class="w-6 h-6 text-purple-400 group-hover:scale-110 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div
                                    class="text-[10px] text-text-secondary uppercase tracking-widest font-black opacity-70 group-hover:opacity-100 transition-opacity">
                                    {{ __('My Portfolio') }}</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-lg font-black text-white whitespace-nowrap">{{ __('Activations') }}
                                    </div>
                                    <span
                                        class="px-2 py-0.5 bg-accent-primary text-[10px] font-black text-white rounded-md shadow-lg shadow-accent-primary/20">
                                        {{ $total_activations }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ml-auto flex items-center gap-2 group/btn">
                            <span
                                class="text-[10px] font-black text-text-secondary uppercase tracking-widest opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300">{{ __('View All') }}</span>
                            <div
                                class="w-7 h-7 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-accent-primary transition-all">
                                <svg class="w-4 h-4 text-white group-hover:translate-x-0.5 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Decorative Background Elements --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-accent-primary/10 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/10 rounded-full blur-[100px]"></div>
        </div>

        {{-- Filter/Search Section (Optional for later) --}}

        {{-- Bots Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($bots as $bot)
                <div
                    class="group relative bg-secondary border border-white/5 rounded-3xl p-1 transition-all hover:border-accent-primary/30 hover:shadow-[0_0_40px_-10px_rgba(var(--accent-primary-rgb),0.2)]">
                    <div class="bg-secondary-dark rounded-[22px] p-6 h-full flex flex-col">
                        {{-- Bot Header --}}
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div
                                        class="absolute -inset-1 bg-gradient-to-tr from-accent-primary to-purple-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition-opacity">
                                    </div>
                                    <img src="{{ asset('assets/images/bots/' . $bot->logo) }}" alt="{{ $bot->name }}"
                                        class="relative w-14 h-14 rounded-2xl object-cover border border-white/10">
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-bold text-white group-hover:text-accent-primary transition-colors leading-tight">
                                        {{ $bot->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-[9px] font-bold text-text-secondary uppercase tracking-wider">
                                            {{ $bot->type }}
                                        </span>
                                        <span class="text-[10px] text-emerald-400 font-bold">
                                            {{ $bot->daily_return_min }}% - {{ $bot->daily_return_max }}%
                                            {{ __('Daily') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-3">
                                <div class="text-[9px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                    {{ __('Duration') }}</div>
                                <div class="text-sm font-black text-white italic">{{ $bot->duration }}
                                    {{ __($bot->duration_type) }}{{ $bot->duration > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-3">
                                <div class="text-[9px] text-text-secondary uppercase tracking-widest font-bold mb-1">
                                    {{ __('Min Capital') }}</div>
                                <div class="text-sm font-black text-white italic">{{ showAmount($bot->min_amount) }}</div>
                            </div>
                        </div>

                        {{-- Trading Days --}}
                        <div class="mb-6">
                            <div class="text-[9px] text-text-secondary uppercase tracking-widest font-bold mb-2">
                                {{ __('Trading Schedule') }}</div>
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $allDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                    $botDays = array_map(fn($day) => substr($day, 0, 3), $bot->trading_days ?? []);
                                @endphp
                                @foreach ($allDays as $day)
                                    <span
                                        class="px-2 py-1 rounded-md text-[9px] font-bold {{ in_array($day, $botDays) ? 'bg-accent-primary/20 text-accent-primary border border-accent-primary/30' : 'bg-white/5 text-text-secondary/30 border border-white/5' }}">
                                        {{ $day }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Exchanges & Pairs Preview --}}
                        <div class="mb-8 flex-grow">
                            @if ($bot->type === 'crypto' && $bot->exchanges)
                                <div class="flex items-center -space-x-2">
                                    @foreach (array_slice($bot->exchanges, 0, 4) as $exchange)
                                        <div class="w-8 h-8 rounded-full border-2 border-secondary-dark bg-white flex items-center justify-center p-1.5 overflow-hidden"
                                            title="{{ $exchange }}">
                                            @php
                                                $logoName = strtolower(str_replace('.', '', $exchange)) . '.svg';
                                                $logoPath = public_path('assets/images/exchanges/' . $logoName);
                                            @endphp
                                            @if (file_exists($logoPath))
                                                <img src="{{ asset('assets/images/exchanges/' . $logoName) }}"
                                                    class="w-full h-full object-contain">
                                            @else
                                                <span
                                                    class="text-[8px] font-black text-black">{{ strtoupper(substr($exchange, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if (count($bot->exchanges) > 4)
                                        <div
                                            class="w-8 h-8 rounded-full border-2 border-secondary-dark bg-white/10 flex items-center justify-center text-[10px] font-black text-white italic">
                                            +{{ count($bot->exchanges) - 4 }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-[10px] text-text-secondary italic">
                                    {{ __('Global Forex Liquidity Providers') }}
                                </div>
                            @endif
                        </div>

                        {{-- Action Button --}}
                        <button type="button"
                            onclick="openActivationModal({{ $bot->id }}, '{{ $bot->name }}', {{ $bot->min_amount }}, {{ $bot->max_amount }}, '{{ $bot->daily_return_min }}% - {{ $bot->daily_return_max }}%', '{{ $bot->duration }} {{ __($bot->duration_type) }}', '{{ implode(', ', array_slice($bot->traded_pairs, 0, 5)) }}', '{{ $bot->exchanges ? implode(', ', array_slice($bot->exchanges, 0, 3)) : '' }}')"
                            class="w-full bg-accent-primary hover:bg-accent-primary/90 text-white font-black py-4 rounded-2xl transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-accent-primary/20 cursor-pointer">
                            {{ __('Activate Bot') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $bots->links() }}
        </div>
    </div>

    {{-- Activation Modal --}}
    <div id="activationModal"
        class="fixed inset-0 z-[1000] hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-white/20 w-full max-w-md rounded-[24px] shadow-2xl relative overflow-hidden">
            <div class="p-8">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-1" id="modalBotName">{{ __('Activate Bot') }}</h3>
                        <p class="text-[10px] text-accent-primary font-bold uppercase tracking-widest">
                            {{ __('Activation Confirmation') }}</p>
                    </div>
                    <button onclick="closeActivationModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-gray-400 hover:text-white transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Bot Details Summary --}}
                <div class="grid grid-cols-2 gap-3 mb-8">
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/5">
                        <span
                            class="block text-[9px] text-gray-500 uppercase font-bold mb-1">{{ __('Daily Return') }}</span>
                        <span id="modalReturns" class="text-sm font-bold text-emerald-400">--</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/5">
                        <span class="block text-[9px] text-gray-500 uppercase font-bold mb-1">{{ __('Duration') }}</span>
                        <span id="modalDuration" class="text-sm font-bold text-white">--</span>
                    </div>
                    <div class="col-span-2 bg-white/5 rounded-2xl p-4 border border-white/5">
                        <span class="block text-[9px] text-gray-500 uppercase font-bold mb-1">{{ __('Markets') }}</span>
                        <span id="modalPairs" class="text-[11px] font-medium text-blue-400 block truncate">--</span>
                    </div>
                </div>

                {{-- Activation Form --}}
                <form id="activationForm" class="space-y-6">
                    @csrf
                    <input type="hidden" name="bot_id" id="modalBotId">

                    <div>
                        <div class="flex items-center justify-between mb-3 px-1">
                            <label
                                class="block text-[10px] text-gray-400 uppercase font-black tracking-widest">{{ __('Activation Capital') }}</label>
                            <div
                                class="flex items-center gap-1.5 bg-accent-primary/10 px-2 py-0.5 rounded-md border border-accent-primary/20">
                                <span
                                    class="text-[9px] text-accent-primary font-bold uppercase tracking-tighter">{{ __('Bal:') }}</span>
                                <span
                                    class="text-[10px] text-white font-black italic">{{ showAmount(auth()->user()->balance) }}</span>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span
                                    class="text-accent-primary font-bold">{{ getSetting('currency_symbol', '$') }}</span>
                            </div>
                            <input type="number" name="amount" id="modalAmount" step="any" required
                                class="w-full bg-black/40 border border-white/10 rounded-xl py-4 pl-10 pr-4 text-white text-xl font-bold focus:border-accent-primary outline-none transition-all"
                                placeholder="0.00">
                        </div>
                        <div class="flex justify-between mt-2 px-1">
                            <span class="text-[9px] text-gray-500 font-bold uppercase">{{ __('Min:') }} <span
                                    id="modalMinAmount" class="text-white"></span></span>
                            <span class="text-[9px] text-gray-500 font-bold uppercase">{{ __('Max:') }} <span
                                    id="modalMaxAmount" class="text-white"></span></span>
                        </div>
                    </div>

                    <div class="p-4 bg-accent-primary/5 rounded-xl border border-accent-primary/10">
                        <p class="text-[10px] text-gray-400 leading-relaxed italic">
                            {{ __('Your funds will be allocated to this bot for the duration specified. Performance is tracked in real-time.') }}
                        </p>
                    </div>

                    <button type="submit" id="activationSubmit"
                        class="w-full bg-accent-primary text-white font-bold py-4 rounded-xl hover:bg-accent-primary/90 transition-all shadow-lg shadow-accent-primary/20 flex items-center justify-center gap-2 cursor-pointer">
                        <span id="submitSpan">{{ __('Activate Now') }}</span>
                        <div id="submitSpinner"
                            class="hidden w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openActivationModal(id, name, min, max, returns, duration, pairs, exchanges) {
                console.log('Opening modal for', name, id);
                document.getElementById('modalBotId').value = id;
                document.getElementById('modalBotName').innerText = name;
                document.getElementById('modalMinAmount').innerText = min.toLocaleString();
                document.getElementById('modalMaxAmount').innerText = max.toLocaleString();
                document.getElementById('modalAmount').min = min;
                document.getElementById('modalAmount').max = max;
                document.getElementById('modalAmount').value = min;

                document.getElementById('modalReturns').innerText = returns;
                document.getElementById('modalDuration').innerText = duration;
                document.getElementById('modalPairs').innerText = pairs;

                const modal = document.getElementById('activationModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeActivationModal() {
                const modal = document.getElementById('activationModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }

            document.getElementById('activationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = document.getElementById('activationSubmit');
                const span = document.getElementById('submitSpan');
                const spinner = document.getElementById('submitSpinner');

                btn.disabled = true;
                span.innerText = "{{ __('Processing...') }}";
                spinner.classList.remove('hidden');

                const formData = new FormData(this);

                fetch("{{ route('user.trading-bots.activate') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastNotification(data.message, 'success');
                            if (data.redirect) {
                                setTimeout(() => window.location.href = data.redirect, 1500);
                            } else {
                                setTimeout(() => window.location.reload(), 1500);
                            }
                        } else {
                            toastNotification(data.message, 'error');
                            btn.disabled = false;
                            span.innerText = "{{ __('Confirm Activation') }}";
                            spinner.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        toastNotification('{{ __('Something went wrong. Please try again.') }}', 'error');
                        btn.disabled = false;
                        span.innerText = "{{ __('Confirm Activation') }}";
                        spinner.classList.add('hidden');
                    });
            });
        </script>
    @endpush
@endsection
