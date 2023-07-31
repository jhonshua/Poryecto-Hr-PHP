<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contractd\View;
use Illuminate\Contracts\View\View as ViewView;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PerfilDescriptivoExport implements FromView, ShouldAutoSize
{
    public $datos;

    public function __construct($datos)
    {
        $this->datos = collect($datos);
    }

    
    public function view(): ViewView
    {
        $datos = $this->datos;
        return view('parametria.puestos.puestos-perfilDescriptivo.perfilDescriptivo-Excel',$datos);
       
      
    }
}
