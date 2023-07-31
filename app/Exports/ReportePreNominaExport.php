<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportePreNominaExport implements FromView, ShouldAutoSize
{
    protected $datos;

    public function __construct($datos)
    {

        $this->datos = $datos;

    }


    public function view(): View
    {
        $periodo = $this->datos['periodo'];
        $columnas1 = $this->datos['columnas1'];
        $columnas2 = $this->datos['columnas2'];
        $columnasSindical = $this->datos['columnasSindical'];
        $columnaPVAC = $this->datos['columnaPVAC'];
        $columnasDEDUCC = $this->datos['columnasDEDUCC'];
        $columnas3 = $this->datos['columnas3'];
        $empleados = $this->datos['empleados'];
        $totales = $this->datos['totales'];
        $parametros_empresa = $this->datos['parametros_empresa'];
        $emisoras = $this->datos['emisoras'];

        
        return view('nomina.reporte-nomina-excel', $this->datos);
    }
}
