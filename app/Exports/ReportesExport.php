<?php

namespace App\Exports;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportesExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $encabezados;

    protected Builder $query;

    public function __construct(array $encabezados, Builder $query)
    {
        $this->encabezados = $encabezados;
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return $this->encabezados;
    }

    public function map($pago): array
    {
        /** @var Pago $pago */
        return [
            $pago->matricula->alumno->nombre_completo ?? 'N/A',
            $pago->matricula->codigo ?? 'N/A',
            $pago->metodo_pago,
            number_format((float) $pago->monto_total, 2),
            $pago->estado,
        ];
    }
}
