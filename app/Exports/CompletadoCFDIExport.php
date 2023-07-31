<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CompletadoCFDIExport implements  ShouldAutoSize,FromView
{
    use Exportable;
    protected $datos;

    public function __construct($datos = null)
    {
        $this->datos = $datos;
    }

    public function view(): View
    {
        $this->datos['cfdis']=$this->datos;
        return view('autofacturador.documentos.reporte_cfdi_excel', $this->datos);
    }
}