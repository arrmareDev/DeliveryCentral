<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
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

        .footer {
            margin-top: 16px;
            text-align: right;
            font-weight: bold;
        }

        .estado-entregado {
            color: #16a34a;
            font-weight: bold;
        }

        .estado-cancelado {
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <h1>Reporte de Despachos — Delivery Central</h1>
    <p class="subtitle">{{ $rango['texto'] }} · Generado el {{ $generado }} · {{ $despachos->count() }} despachos</p>

    <table>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Negocio</th>
                <th>Cliente</th>
                <th>Motorizado</th>
                <th>Estado</th>
                <th>Método pago</th>
                <th>Total</th>
                <th>Solicitado</th>
                <th>Entregado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($despachos as $d)
                @php $order = $d->order_data ?? []; @endphp
                <tr>
                    <td>#{{ $d->external_order_id }}</td>
                    <td>{{ $d->negocio?->name ?? '—' }}</td>
                    <td>{{ $order['client_name'] ?? '—' }}</td>
                    <td>{{ $d->motorizado?->nombre ?? '—' }}</td>
                    <td class="estado-{{ $d->estado }}">{{ ucfirst($d->estado) }}</td>
                    <td>{{ $order['metodo_pago'] ?? '—' }}</td>
                    <td>S/ {{ number_format($order['total'] ?? 0, 2) }}</td>
                    <td>{{ $d->solicitado_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $d->entregado_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Total: S/ {{ number_format($total, 2) }}</p>
</body>

</html>
