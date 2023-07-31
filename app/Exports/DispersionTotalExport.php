<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\ToModel;
use DateTime;

class DispersionTotalExport extends DefaultValueBinder implements FromView, ShouldAutoSize, WithCustomValueBinder
{
    protected $empleados;

    public function __construct($empleados, $periodo)
    {
        $this->empleados = $empleados;
        $this->periodo = $periodo;
    }
  
    public function bindValue(Cell $cell, $value)
    {
        if(is_numeric($value)) {

            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        
        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function view(): View
    {
        $empleados   = $this->empleados;
        $periodo     = $this->periodo;
        $finicio =      new DateTime($periodo->fecha_inicial_periodo);
        $ffin =         new DateTime($periodo->fecha_final_periodo);
        $this->periodo->finicio = $finicio->format('d-m-Y');
        $this->periodo->ffin = $ffin->format('d-m-Y');
        return view('procesos.dispersion.exportar_totales',  compact('empleados','periodo'));
    }
}
