<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Resumen - {{ $room->name ?? 'Sala ' . $room->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #fff;
            color: #1a202c;
            font-size: 11px;
            line-height: 1.5;
        }
        .page { padding: 30px 35px; }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #6366f1;
        }
        .header-logo {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }
        .room-name {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .room-meta {
            font-size: 11px;
            color: #6b7280;
        }
        .room-code {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* Stats Row */
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .stat-box:first-child { border-radius: 8px 0 0 8px; }
        .stat-box:last-child { border-radius: 0 8px 8px 0; border-left: none; }
        .stat-box:nth-child(2) { border-left: none; }
        .stat-box.primary {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 2px;
        }
        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }

        /* Section */
        .section { margin-bottom: 20px; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-left: 10px;
            border-left: 3px solid #6366f1;
        }

        /* Categories */
        .categories-row {
            display: table;
            width: 100%;
        }
        .category-box {
            display: table-cell;
            text-align: center;
            padding: 12px 8px;
            background: #fafafa;
            border: 1px solid #e5e7eb;
            border-right: none;
        }
        .category-box:first-child { border-radius: 6px 0 0 6px; }
        .category-box:last-child { border-radius: 0 6px 6px 0; border-right: 1px solid #e5e7eb; }
        .category-name {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .category-amount {
            font-size: 16px;
            font-weight: 800;
            color: #6366f1;
        }
        .category-percent {
            font-size: 9px;
            color: #9ca3af;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background: #6366f1;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        th:last-child { text-align: right; }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        td:last-child { text-align: right; }
        .expense-name { font-weight: 600; color: #1f2937; }
        .expense-payer { color: #6b7280; }
        .expense-amount {
            font-weight: 800;
            font-family: 'Courier New', monospace;
            color: #6366f1;
            font-size: 11px;
        }

        /* Participants */
        .participants-grid {
            display: table;
            width: 100%;
            background: #fafafa;
            border-radius: 8px;
            padding: 10px;
        }
        .participant-row { display: table-row; }
        .participant-cell {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 8px 4px;
            vertical-align: top;
        }
        .participant-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #6366f1;
            margin: 0 auto 5px;
            line-height: 36px;
            font-size: 14px;
            font-weight: 800;
            color: white;
        }
        .participant-name {
            font-size: 9px;
            color: #374151;
            font-weight: 500;
        }
        .participant-admin {
            font-size: 7px;
            color: #6366f1;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .footer-text {
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ public_path('images/logo.png') }}" alt="Cuanto Dolio?" style="height: 70px;">
            </div>
            <div class="header-info">
                <div class="room-name">{{ $room->name ?? 'Sala ' . $room->code }}</div>
                <div class="room-meta">
                    Codigo: <span class="room-code">{{ $room->code }}</span>
                    &nbsp;&bull;&nbsp;
                    {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box primary">
                <div class="stat-value">${{ number_format($totalExpenses, 0, ',', '.') }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ count($room->participants) }}</div>
                <div class="stat-label">Personas</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ count($room->expenses) }}</div>
                <div class="stat-label">Gastos</div>
            </div>
        </div>

        <!-- Categories -->
        @if(count($categories) > 0)
        <div class="section">
            <h2 class="section-title">Por Categoria</h2>
            <div class="categories-row">
                @foreach($categories as $cat)
                <div class="category-box">
                    <div class="category-name">{{ $cat['name'] }}</div>
                    <div class="category-amount">${{ number_format($cat['amount'], 0, ',', '.') }}</div>
                    <div class="category-percent">{{ $cat['percent'] }}%</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Expenses -->
        @if(count($room->expenses) > 0)
        <div class="section">
            <h2 class="section-title">Gastos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Descripcion</th>
                        <th>Pago</th>
                        <th>Div.</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($room->expenses->sortByDesc('amount') as $expense)
                    <tr>
                        <td class="expense-name">{{ $expense->description }}</td>
                        <td class="expense-payer">{{ $expense->payer->name ?? 'N/A' }}</td>
                        <td>{{ count($expense->splits) }}</td>
                        <td class="expense-amount">${{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Participants -->
        <div class="section">
            <h2 class="section-title">Participantes</h2>
            <div class="participants-grid">
                @foreach($room->participants->chunk(5) as $chunk)
                <div class="participant-row">
                    @foreach($chunk as $participant)
                    <div class="participant-cell">
                        <div class="participant-avatar">{{ strtoupper(mb_substr($participant->name, 0, 1)) }}</div>
                        <div class="participant-name">{{ $participant->name }}</div>
                        @if($participant->role === 'admin')
                        <div class="participant-admin">Admin</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <img src="{{ public_path('images/logo.png') }}" alt="" style="height: 25px; opacity: 0.6; margin-bottom: 5px;">
            <p class="footer-text">Dividi gastos sin dolor de cabeza</p>
        </div>
    </div>
</body>
</html>
