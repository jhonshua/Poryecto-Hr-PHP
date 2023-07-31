<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View as ViewView;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReporteMovimientoPersonalExport implements FromView, ShouldAutoSize
{
    public $movimientos;

    public function __construct($mo)
    {
        $this->movimientos = collect($mo);
    }

    public function view(): ViewView
    {
        $movimientos = $this->movimientos;

        return view('consultas.movimientos-personal.movimiento-personal-excel', compact('movimientos'));
    }
}
