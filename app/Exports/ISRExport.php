<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class ISRExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    { 
        cambiarBase(Session::get('base'));
        $prestaciones =  DB::connection('empresa')->table('impuestos')->select('tipo_tabla', 'limite_inferior', 'limite_superior', 'cuota_fija', 'porcentaje')->orderBy('tipo_tabla', 'desc')->get();

        return $prestaciones;
    }

    public function headings(): array
    {
        return [
            'tipo_tabla',
            'limite_inferior',
            'limite_superior',
            'cuota_fija',
            'porcentaje',
        ];
    }
}
