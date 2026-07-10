<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title }}</title>
    <style>
        @page {
            margin: 30px;
            background-color: #ffffff;
            size: {{ $orientation ?? 'landscape' }};
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-size: 9px;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
        }

        .logo-img {
            height: 25px;
            max-width: 150px;
        }

        .report-info {
            float: right;
            text-align: right;
        }

        .report-label {
            display: block;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .report-meta {
            color: #64748b;
            font-size: 10px;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            text-align: left;
            padding: 6px 4px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 7px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 6px 4px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .profit-pos {
            color: #059669;
            font-weight: 700;
        }

        .profit-neg {
            color: #dc2626;
            font-weight: 700;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            color: #94a3b8;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="float: left;">
            <img src="{{ public_path('assets/images/' . getSetting('logo_rectangle')) }}"
                alt="{{ getSetting('site_name') }}" class="logo-img">
        </div>
        <div class="report-info">
            <span class="report-label">{{ $page_title }}</span>
            <div class="report-meta">
                {{ __('Generated on') }}: {{ now()->format('M d, Y H:i A') }}<br>
                {{ __('Total Executions') }}: {{ $logs->count() }}
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $key => $label)
                    <th class="{{ in_array($key, ['amount', 'profit', 'profit_percentage']) ? 'text-right' : '' }}">
                        {{ __($label) }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    @foreach ($columns as $key => $label)
                        <td class="{{ in_array($key, ['amount', 'profit', 'profit_percentage']) ? 'text-right' : '' }}">
                            @if ($key == 'username')
                                <span style="font-weight: 600;">{{ $log->user->username }}</span>
                            @elseif($key == 'bot_name')
                                {{ $log->activation->bot->name ?? 'N/A' }}
                            @elseif($key == 'profit')
                                <span class="{{ $log->profit >= 0 ? 'profit-pos' : 'profit-neg' }}">
                                    {{ $log->profit >= 0 ? '+' : '' }}{{ showAmount($log->profit) }}
                                </span>
                            @elseif($key == 'profit_percentage')
                                <span class="{{ $log->profit_percentage >= 0 ? 'profit-pos' : 'profit-neg' }}">
                                    {{ $log->profit_percentage >= 0 ? '+' : '' }}{{ number_format($log->profit_percentage, 2) }}%
                                </span>
                            @elseif($key == 'amount')
                                <span style="font-family: monospace;">{{ showAmount($log->amount) }}</span>
                            @elseif($key == 'created_at')
                                {{ $log->created_at->format('M d, Y H:i') }}
                            @elseif($key == 'exit_time')
                                {{ date('M d, Y H:i', $log->exit_time) }}
                            @else
                                {{ $log->$key }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center" style="padding: 30px; color: #94a3b8;">
                        {{ __('No trading logs found matching your criteria.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} {{ getSetting('site_name') }}. {{ __('Admin Panel') }}<br>
        {{ url('/') }}
    </div>
</body>

</html>
