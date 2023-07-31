<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View as ViewView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class ReporteNominasPeriodoExport implements FromView, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $datos;
    public $periodo;

    public function __construct($datos, $periodo)
    {
        $this->datos = collect($datos);
        $this->periodo = collect($periodo);
    }

    public function view(): View
    {
        $datos = $this->datos;
        $periodo = $this->periodo;

        return view('consultas.reporte-nominasPeriodo.reporteA-nominasPeriodo', compact('datos','periodo'));
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) { 
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(45);
                $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(20);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(80);
     
            },
        ];
    }
}
