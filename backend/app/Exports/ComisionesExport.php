<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComisionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $comisiones) {}

    public function collection()
    {
        return $this->comisiones;
    }

    public function headings(): array
    {
        return [
            'Motorizado',
            'Pedido',
            'Negocio',
            'Monto (S/)',
            'Estado',
            'Generado',
            'Cobrado',
        ];
    }

    public function map($comision): array
    {
        return [
            $comision->motorizado?->nombre ?? '—',
            $comision->despacho?->external_order_id ?? '—',
            $comision->despacho?->negocio?->name ?? '—',
            number_format($comision->monto, 2),
            ucfirst($comision->estado),
            $comision->created_at?->format('d/m/Y H:i') ?? '—',
            $comision->cobrado_at?->format('d/m/Y H:i') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
