<?php

namespace App\Exports;

use App\Models\Prestamo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PrestamosExport implements  FromCollection, WithHeadings
{
    use Exportable;

    protected $estatus;

    public function __construct($estatus = null)
    {
        $this->estatus = $estatus;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->estatus ?: Prestamo::all();
    }

    public function headings(): array
    {
        return [
            '#',
            'EMPRESA',
            'EJECUTIVO',
            'TIPO PRESTAMO',
            'ESTATUS',
            'FECHA CREACIÓN',
            'FECHA EDICIÓN',
            'FECHA CIERRE'
        ];
    }
}
