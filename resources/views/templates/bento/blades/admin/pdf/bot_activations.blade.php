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
            font-size: 10px;
        }

        .header {
            margin-bottom: 40px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }

        .logo-img {
            height: 30px;
            max-width: 180px;
        }

        .report-info {
            float: right;
            text-align: right;
        }

        .report-label {
            display: block;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .report-meta {
            color: #64748b;
            font-size: 12px;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            text-align: left;
            padding: 8px 6px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 8px 6px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            color: #059669;
            background: #ecfdf5;
        }

        .status-suspended {
            color: #d97706;
            background: #fffbeb;
        }

        .status-completed {
            color: #2563eb;
            background: #eff6ff;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            color: #94a3b8;
            font-size: 10px;
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
                {{ __('Total Activations') }}: {{ $activations->count() }}
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $key => $label)
                    <th class="{{ in_array($key, ['amount', 'returned_profit']) ? 'text-right' : (in_array($key, ['status']) ? 'text-center' : '') }}">
                        {{ __($label) }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($activations as $activation)
                <tr>
                    @foreach ($columns as $key => $label)
                        <td class="{{ in_array($key, ['amount', 'returned_profit']) ? 'text-right' : (in_array($key, ['status']) ? 'text-center' : '') }}">
                            @if ($key == 'username')
                                <span style="font-weight: 600;">{{ $activation->user->username }}</span>
                            @elseif($key == 'bot_name')
                                {{ $activation->bot->name }}
                            @elseif($key == 'status')
                                <span class="status-badge status-{{ $activation->status }}">
                                    {{ ucfirst($activation->status) }}
                                </span>
                            @elseif(in_array($key, ['amount', 'returned_profit']))
                                <span style="font-family: monospace;">{{ showAmount($activation->$key) }}</span>
                            @elseif($key == 'start_date')
                                {{ date('M d, Y', $activation->start_date) }}
                            @elseif($key == 'end_date')
                                {{ $activation->end_date ? date('M d, Y', $activation->end_date) : 'N/A' }}
                            @else
                                {{ $activation->$key }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center" style="padding: 30px; color: #94a3b8;">
                        {{ __('No bot activations found matching your criteria.') }}
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
