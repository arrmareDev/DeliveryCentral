<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DespachosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $despachos) {}

    public function collection()
    {
        return $this->despachos;
    }

    public function headings(): array
    {
        return [
            'ID Pedido',
            'Negocio',
            'Cliente',
            'Motorizado',
            'Estado',
            'Motivo cancelación',
            'Método de pago',
            'Total (S/)',
            'Solicitado',
            'Entregado',
        ];
    }

    public function map($despacho): array
    {
        $order = $despacho->order_data ?? [];

        return [
            $despacho->external_order_id,
            $despacho->negocio?->name ?? '—',
            $order['client_name'] ?? '—',
            $despacho->motorizado?->nombre ?? '—',
            ucfirst($despacho->estado),
            $despacho->motivo_cancelacion ?? '',
            $order['metodo_pago'] ?? '—',
            number_format($order['total'] ?? 0, 2),
            $despacho->solicitado_at?->format('d/m/Y H:i') ?? '—',
            $despacho->entregado_at?->format('d/m/Y H:i') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
