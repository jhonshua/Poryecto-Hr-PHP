<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ResumenCFDIExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    protected $datos;

    public function __construct($datos = null)
    {
        $this->datos = $datos;
    }

    public function collection()
    {
        $cfdi=$this->datos;
        return collect($cfdi);
    }

    public function headings(): array
    {
        return [
            'Uuid',
            'RfcEmisor',
            'NombreEmisor',
            'RfcReceptor',
            'NombreReceptor',
            'RfcPac',
            'FechaEmision',
            'FechaCertificacionSat',
            'Monto',
            'EfectoComprobante',
            'Estatus',
            'FechaCancelacion',
        ];
    }

}