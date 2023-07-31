<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Parametros;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CalculoNominaExport implements FromView, ShouldAutoSize
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }



    public function view(): View
    {
        $misc                          = $this->datos['misc'];
        $this->datos['columnaPVAC']    = $misc['columnaPVAC'];
        $this->datos['rowsidpvacAnti'] = $misc['rowsidpvacAnti'];
        $this->datos['rowsidprdom']    = $misc['rowsidprdom'];

        $this->datos['parametros_empresa'] = Parametros::first();

        return view('calculo-nomina.reporte-nomina-excel', $this->datos);
    }
}
