<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 10.5px;
            color: #1f2937;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #111827;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .totales {
            margin-top: 16px;
            display: flex;
        }

        .totales div {
            margin-right: 24px;
        }

        .label {
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
        }

        .pendiente {
            color: #9333ea;
            font-weight: bold;
            font-size: 13px;
        }

        .cobrado {
            color: #16a34a;
            font-weight: bold;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <h1>Reporte de Comisiones — Delivery Central</h1>
    <p class="subtitle">{{ $rango['texto'] }} · Generado el {{ $generado }} · {{ $comisiones->count() }} registros
    </p>

    <table>
        <thead>
            <tr>
                <th>Motorizado</th>
                <th>Pedido</th>
                <th>Restaurante</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Generado</th>
                <th>Cobrado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comisiones as $c)
                <tr>
                    <td>{{ $c->motorizado?->nombre ?? '—' }}</td>
                    <td>#{{ $c->despacho?->external_order_id ?? '—' }}</td>
                    <td>{{ $c->despacho?->restaurant?->name ?? '—' }}</td>
                    <td>S/ {{ number_format($c->monto, 2) }}</td>
                    <td>{{ ucfirst($c->estado) }}</td>
                    <td>{{ $c->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $c->cobrado_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <div>
            <p class="label">Pendiente</p>
            <p class="pendiente">S/ {{ number_format($totalPendiente, 2) }}</p>
        </div>
        <div>
            <p class="label">Cobrado</p>
            <p class="cobrado">S/ {{ number_format($totalCobrado, 2) }}</p>
        </div>
    </div>
</body>

</html>
