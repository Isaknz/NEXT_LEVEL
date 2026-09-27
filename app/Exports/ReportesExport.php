<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportesExport implements FromCollection, WithHeadings
{
    protected array $encabezados;

    protected array $data;

    public function __construct(array $encabezados, array $data)
    {
        $this->encabezados = $encabezados;
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->encabezados;
    }
}
