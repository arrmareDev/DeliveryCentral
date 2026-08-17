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

        .verificado-si {
            color: #16a34a;
            font-weight: bold;
        }

        .verificado-no {
            color: #d97706;
        }
    </style>
</head>

<body>
    <h1>Reporte de Motorizados — Delivery Central</h1>
    <p class="subtitle">{{ $filtro }} · Generado el {{ $generado }} · {{ $motorizados->count() }} motorizados
    </p>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Placa</th>
                <th>Vehículo</th>
                <th>Verificado</th>
                <th>Estado</th>
                <th>Entregas</th>
                <th>Registrado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($motorizados as $m)
                <tr>
                    <td>{{ $m->nombre }}</td>
                    <td>{{ $m->dni ?? '—' }}</td>
                    <td>{{ $m->telefono }}</td>
                    <td>{{ $m->email }}</td>
                    <td>{{ $m->placa ?? '—' }}</td>
                    <td>{{ $m->marca_vehiculo }} {{ $m->modelo_vehiculo }} {{ $m->anio_vehiculo }}</td>
                    <td class="verificado-{{ $m->verificado ? 'si' : 'no' }}">
                        {{ $m->verificado ? 'Sí' : 'No' }}
                    </td>
                    <td>{{ ucfirst($m->estado) }}</td>
                    <td>{{ $m->despachos()->where('estado', 'entregado')->count() }}</td>
                    <td>{{ $m->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Total: {{ $motorizados->count() }} motorizados</p>
</body>

</html>
