@extends('templates.bento.blades.layouts.front')

@section('title', $page_title . ' - ' . getSetting('name'))
@section('page_title', $page_title)

@section('content')
    <div class="relative py-24 bg-[#05070A] overflow-hidden">
        {{-- Background Auroras --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute -top-[20%] -left-[10%] w-[50%] h-[70%] bg-accent-primary/10 blur-[150px] rounded-full mix-blend-screen opacity-50 animate-pulse-slow">
            </div>
            <div
                class="absolute bottom-[10%] -right-[10%] w-[40%] h-[60%] bg-emerald-500/5 blur-[150px] rounded-full mix-blend-screen opacity-30 animate-float">
            </div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            {{-- Hero Section: Mirror the Best --}}
            <div class="text-center max-w-4xl mx-auto mb-28">
                <div
                    class="inline-flex items-center gap-3 px-6 py-2 rounded-full border border-accent-primary/30 bg-accent-primary/5 text-accent-primary text-[10px] font-black tracking-[0.4em] uppercase mb-10 backdrop-blur-md animate-reveal">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-accent-primary"></span>
                    </span>
                    // {{ __('ALGORITHMIC_COPY_v.01') }}
                </div>
                <h1 class="text-6xl md:text-8xl font-black text-white leading-[0.9] tracking-tighter mb-10 reveal-text">
                    {{ __('Mirror Success.') }} <br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-accent-primary via-white/90 to-emerald-400">
                        {{ __('Automate Your Wealth.') }}
                    </span>
                </h1>
                <p class="text-text-secondary text-xl md:text-2xl leading-relaxed max-w-2xl mx-auto opacity-70 mb-12">
                    {{ $page_description }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <a href="{{ route('user.register') }}"
                        class="group relative px-10 py-5 bg-accent-primary text-white font-black uppercase text-xs tracking-widest rounded-2xl overflow-hidden shadow-2xl hover:scale-105 transition-all w-full sm:w-auto text-center">
                        <span class="relative z-10">{{ __('Start Copying Now') }}</span>
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]">
                        </div>
                    </a>
                    <a href="#results"
                        class="px-10 py-5 bg-white/5 border border-white/10 text-white font-black uppercase text-xs tracking-widest rounded-2xl hover:bg-white/10 transition-all w-full sm:w-auto text-center">
                        {{ __('View Verified Results') }}
                    </a>
                </div>
            </div>

            {{-- Result-Driven Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-32">
                @php
                    $stats_items = [
                        [
                            'label' => __('Total Profit Shared'),
                            'value' => showAmount($stats['total_profit']),
                            'icon' => 'profit',
                            'color' => 'text-emerald-400',
                        ],
                        [
                            'label' => __('Total Volume Traded'),
                            'value' => showAmount($stats['total_volume']),
                            'icon' => 'volume',
                            'color' => 'text-accent-primary',
                        ],
                        [
                            'label' => __('System Success Rate'),
                            'value' => $stats['success_rate'] . '%',
                            'icon' => 'success',
                            'color' => 'text-blue-400',
                        ],
                        [
                            'label' => __('Active Copy Traders'),
                            'value' => number_format($stats['active_traders']),
                            'icon' => 'traders',
                            'color' => 'text-purple-400',
                        ],
                    ];
                @endphp

                @foreach ($stats_items as $item)
                    <div
                        class="relative group bg-[#0B0F17]/50 backdrop-blur-xl border border-white/5 rounded-[2.5rem] p-8 hover:border-white/10 transition-all">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.2em] mb-4 opacity-50">
                            {{ $item['label'] }}</div>
                        <div class="text-4xl font-black {{ $item['color'] }} tracking-tighter mb-2">{{ $item['value'] }}
                        </div>
                        <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden mt-4">
                            <div class="h-full bg-current opacity-20 w-3/4 animate-loading-bar"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- The "Result" Section: Real-Time Verifiable Feed --}}
            <section id="results" class="mb-32 relative">
                <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16">
                    <div class="max-w-xl">
                        <h2 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter mb-4">
                            {{ __('Verified Activity') }}</h2>
                        <p class="text-text-secondary text-lg">
                            {{ __('Real-time feed of recently completed copy trades. Transparency is at the core of our algorithmic performance.') }}
                        </p>
                    </div>
                    <div class="hidden md:flex gap-3">
                        <span
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-[10px] font-black uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            {{ __('LIVE_FEED_ACTIVE') }}
                        </span>
                    </div>
                </div>

                @if ($recentTrades->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        @foreach ($recentTrades as $trade)
                            @include('templates.bento.blades.pages.partials.copy_trade_result_card', [
                                'trade' => $trade,
                            ])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-20 bg-white/[0.02] border border-white/5 rounded-[3rem]">
                        <p class="text-text-secondary italic">
                            {{ __('Performance data is currently being aggregated for the new cycle.') }}</p>
                    </div>
                @endif
            </section>

            {{-- How It Works --}}
            <div
                class="grid lg:grid-cols-2 gap-20 items-center mb-32 bg-[#0B0F17]/30 rounded-[4rem] p-12 md:p-20 border border-white/5">
                <div>
                    <h2 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter mb-8 leading-none">
                        {{ __('Simplicity Meets') }} <br>
                        <span class="text-accent-primary">{{ __('Sophistication.') }}</span>
                    </h2>
                    <div class="space-y-12">
                        @php
                            $steps = [
                                [
                                    'title' => __('Obtain Strategy Code'),
                                    'desc' => __(
                                        'Get exclusive access codes from our master traders or administrative team.',
                                    ),
                                ],
                                [
                                    'title' => __('Initialize Deployment'),
                                    'desc' => __(
                                        'Enter your capital amount and activate the strategy link to your account.',
                                    ),
                                ],
                                [
                                    'title' => __('Observe Execution'),
                                    'desc' => __(
                                        'Our algorithmic mirrors handle the entries, exits, and risk management automatically.',
                                    ),
                                ],
                            ];
                        @endphp
                        @foreach ($steps as $idx => $step)
                            <div class="flex gap-6 group">
                                <div
                                    class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-xl font-black text-white group-hover:bg-accent-primary group-hover:border-accent-primary transition-all duration-500">
                                    {{ $idx + 1 }}
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-white mb-2">{{ $step['title'] }}</h4>
                                    <p class="text-text-secondary leading-relaxed opacity-70">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-10 bg-accent-primary/10 blur-[100px] rounded-full"></div>
                    <div
                        class="relative bg-[#05070A] border border-white/10 rounded-[3rem] p-2 shadow-2xl rotate-2 hover:rotate-0 transition-transform duration-700">
                        <img src="{{ asset('assets/templates/bento/images/copy-trading-2.png') }}" alt="Trading Dashboard"
                            class="w-full rounded-[2.8rem] opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#05070A] via-transparent to-transparent"></div>
                    </div>
                </div>
            </div>

            {{-- Final CTA --}}
            <div
                class="relative rounded-[4rem] bg-gradient-to-br from-accent-primary to-accent-secondary p-12 md:p-24 text-center overflow-hidden shadow-2xl shadow-accent-primary/20">
                {{-- Decorative Elements --}}
                <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-[100px]"></div>
                <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-black/10 rounded-full blur-[100px]"></div>

                <h2 class="text-5xl md:text-7xl font-black text-white tracking-tighter mb-10 leading-none relative z-10">
                    {{ __('Ready to Mirror') }} <br> {{ __('Performance?') }}</h2>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 relative z-10">
                    <a href="{{ route('user.register') }}"
                        class="px-12 py-5 bg-white text-[#05070A] font-black uppercase text-xs tracking-widest rounded-2xl hover:scale-105 transition-all shadow-xl">
                        {{ __('Create FREE Account') }}
                    </a>
                    <a href="{{ route('user.login') }}"
                        class="px-12 py-5 bg-transparent border-2 border-white/30 text-white font-black uppercase text-xs tracking-widest rounded-2xl hover:bg-white/10 transition-all">
                        {{ __('Account Initialization') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes reveal {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-reveal {
            animation: reveal 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.05);
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 10s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(2deg);
            }
        }

        .animate-float {
            animation: float 15s ease-in-out infinite;
        }

        @keyframes loading-bar {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(200%);
            }
        }

        .animate-loading-bar {
            animation: loading-bar 3s infinite linear;
        }
    </style>
@endsection
