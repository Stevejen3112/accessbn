@extends('templates.' . config('site.template') . '.blades.layouts.user')

@section('content')
    <style>
        /* Custom Scrollbar for this page */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        button {
            cursor: pointer;
        }

        .glass-panel {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .glow-border {
            position: relative;
        }

        .glow-border:before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 1.5rem;
            background: linear-gradient(90deg, rgba(124, 58, 237, 0.35), rgba(168, 85, 247, 0.35), rgba(59, 130, 246, 0.35));
            filter: blur(18px);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }

        .glow-border>* {
            position: relative;
            z-index: 1;
        }
    </style>

    <div class="min-h-screen px-2 md:px-0" id="general-page">
        @include('templates.' . config('site.template') . '.blades.user.trading.copy.index_inner')
    </div>
@endsection

@section('scripts')
    <script src="https://s3.tradingview.com/tv.js"></script>
    <script>
        $(document).ready(function() {
            let currentSymbol = "{{ $current_ticker }}";
            let currentPrice = {{ $current_ticker_info['current_price'] ?? 0 }};
            let availableBalance = {{ $add_available ?? 0 }};
            let currentInterval = "15";
            let showToolbar = false;
            let currentStrategyId = null;
            let currentAmountType = 'manual';
            let currentPercentage = 0;

            // Initialize TradingView Chart
            function initChart(symbol, interval = '15', showToolbar = false) {
                $('#chartLoader').removeClass('hidden');
                let tvSymbol = "BINANCE:" + symbol + ".P";
                $('#chartContainer').html('');

                const script = document.createElement('script');
                script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
                script.async = true;
                script.innerHTML = JSON.stringify({
                    "autosize": true,
                    "symbol": tvSymbol,
                    "interval": interval,
                    "timezone": "{{ config('app.timezone') }}",
                    "theme": "dark",
                    "style": "1",
                    "locale": "en",
                    "enable_publishing": false,
                    "backgroundColor": "rgba(19, 23, 34, 1)",
                    "gridColor": "rgba(42, 46, 57, 0.5)",
                    "hide_top_toolbar": !showToolbar,
                    "hide_legend": true,
                    "save_image": false,
                    "calendar": false,
                    "allow_symbol_change": true,
                    "hide_volume": true,
                    "support_host": "https://www.tradingview.com"
                });
                document.getElementById('chartContainer').appendChild(script);

                setTimeout(() => {
                    $('#chartLoader').addClass('hidden');
                }, 1500);
            }

            // Ticker Dropdown
            $(document).on('click', '#pairDropdownBtn', function(e) {
                e.stopPropagation();
                $('#pairDropdownMenu').toggleClass('hidden');
            });

            $(document).click(function(e) {
                const $menu = $('#pairDropdownMenu');
                const $btn = $('#pairDropdownBtn');
                if (!$menu.is(e.target) && $menu.has(e.target).length === 0 && !$btn.is(e.target) && $btn
                    .has(e.target).length === 0) {
                    $menu.addClass('hidden');
                }
            });

            $(document).on('input', '#pairSearch', function() {
                let val = $(this).val().toLowerCase();
                $('.pair-item').each(function() {
                    let ticker = $(this).find('.font-semibold').text().toLowerCase();
                    if (ticker.indexOf(val) > -1) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });
            });

            // AJAX Data Loading
            function loadTickerData(url, ticker, isFullReplace = false) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (isFullReplace) {
                            $('#general-page').html(response);
                            history.pushState(null, '', url);
                            currentSymbol = ticker;

                            // Update current price and balance
                            const rawPrice = $('#lastPrice').text().replace(/,/g, '');
                            currentPrice = parseFloat(rawPrice) || 0;
                            const $bal = $('#availableBalanceValue');
                            if ($bal.length) availableBalance = parseFloat($bal.text()) || 0;

                            initChart(currentSymbol, currentInterval, showToolbar);
                        } else {
                            const $newData = $(response);
                            $('#topPanelStats').html($newData.find('#topPanelStats').html());
                            $('#orderBookContainer').html($newData.find('#orderBookContainer').html());
                            $('#recentTradesContainer').html($newData.find('#recentTradesContainer')
                                .html());
                            $('#activationsTable').html($newData.find('#activationsTable').html());

                            const rawPrice = $('#lastPrice').text().replace(/,/g, '');
                            currentPrice = parseFloat(rawPrice) || 0;
                            const $bal = $('#availableBalanceValue');
                            if ($bal.length) availableBalance = parseFloat($bal.text()) || 0;
                        }
                    }
                });
            }

            // Code Checking
            $(document).on('input paste keyup change', '#copyTradingCode', function() {
                let code = $(this).val();

                if (code.length >= 6) {
                    checkTradingCode(code);
                } else {
                    resetTradeDetails();
                }
            });

            function checkTradingCode(code) {
                $.ajax({
                    url: "{{ route('user.copy-trading.check-code') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        code: code
                    },
                    success: function(response) {
                        if (response.success) {
                            currentTradeId = response.data.id;
                            $('#tradeDetails').removeClass('hidden');
                            $('#tradePair').text(response.data.pair);
                            $('#tradeRoi').text(response.data.roi + '%');
                            $('#btnActivate').prop('disabled', false);

                            currentAmountType = response.data.amount_type;
                            currentPercentage = response.data.percentage;

                            if (currentAmountType === 'percentage') {
                                $('#amountInputContainer').addClass('hidden');
                                $('#percentageInfoContainer').removeClass('hidden');
                                $('#displayPercentage').text(currentPercentage);
                            } else {
                                $('#amountInputContainer').removeClass('hidden');
                                $('#percentageInfoContainer').addClass('hidden');
                            }

                            // If ticker changed, update chart
                            if (response.data.ticker !== currentSymbol) {
                                const url = "{{ route('user.copy-trading.index') }}?ticker=" + response
                                    .data.ticker;
                                loadTickerData(url, response.data.ticker, true);
                            }
                        } else {
                            resetTradeDetails();
                            if (response.message) {
                                toastNotification(response.message, 'error');
                            }
                        }
                    },
                    error: function(xhr) {
                        resetTradeDetails();
                        const message = xhr.responseJSON ? xhr.responseJSON.message :
                            "{{ __('Invalid or expired trading code.') }}";
                        if (xhr.status !== 422) { // Skip validation errors which might be frequent
                            toastNotification(message, 'error');
                        }
                    }
                });
            }

            function resetTradeDetails() {
                currentTradeId = null;
                $('#tradeDetails').addClass('hidden');
                $('#btnActivate').prop('disabled', true);
            }

            // Start Copy
            $(document).on('click', '#btnActivate', function() {
                const amount = $('#inputAmount').val();
                if (currentAmountType === 'manual' && (!amount || amount <= 0)) {
                    toastNotification("{{ __('Please enter a valid amount') }}", 'error');
                    return;
                }

                const $btn = $(this);
                const originalText = $btn.text();
                $btn.prop('disabled', true).html(
                    '<svg class="animate-spin h-5 w-5 mx-auto text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                );

                $.ajax({
                    url: "{{ route('user.copy-trading.activate') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        copy_trading_id: currentTradeId,
                        amount: amount,
                        amount_type: currentAmountType
                    },
                    success: function(response) {
                        if (response.success) {
                            toastNotification(response.message, 'success');
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            toastNotification(response.message, 'error');
                            $btn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON ? xhr.responseJSON.message :
                            "{{ __('An error occurred') }}";
                        toastNotification(message, 'error');
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Initial state
            initChart(currentSymbol);

            // Polling
            setInterval(() => {
                loadTickerData(window.location.href, currentSymbol, false);
            }, 10000);

            // Clock
            setInterval(() => {
                const now = new Date();
                const options = {
                    timeZone: '{{ config('app.timezone') }}',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                $('#chartTime').text(now.toLocaleTimeString('en-GB', options) +
                    ' {{ config('app.timezone') }}');
            }, 1000);
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                toastNotification("{{ __('Code copied to clipboard') }}", 'success');
                $('#copyTradingCode').val(text).trigger('input');
            });
        }
    </script>
@endsection
