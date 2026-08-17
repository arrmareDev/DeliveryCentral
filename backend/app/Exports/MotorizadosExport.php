<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MotorizadosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $motorizados) {}

    public function collection()
    {
        return $this->motorizados;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'DNI',
            'Fecha de nacimiento',
            'Teléfono',
            'Correo',
            'Placa',
            'Marca',
            'Modelo',
            'Año',
            'N° SOAT',
            'Verificado',
            'Estado',
            'Total entregas',
            'Registrado',
        ];
    }

    public function map($m): array
    {
        return [
            $m->nombre,
            $m->dni ?? '—',
            $m->fecha_nacimiento?->format('d/m/Y') ?? '—',
            $m->telefono,
            $m->email,
            $m->placa ?? '—',
            $m->marca_vehiculo ?? '—',
            $m->modelo_vehiculo ?? '—',
            $m->anio_vehiculo ?? '—',
            $m->soat_numero ?? '—',
            $m->verificado ? 'Sí' : 'No',
            ucfirst($m->estado),
            $m->despachos()->where('estado', 'entregado')->count(),
            $m->created_at?->format('d/m/Y H:i') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
