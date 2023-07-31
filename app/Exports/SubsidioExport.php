<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class SubsidioExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        cambiarBase(Session::get('base'));
        $subsidios =  DB::connection('empresa')->table('subsidios')->select('tipo_tabla', 'ingreso_desde', 'ingreso_hasta', 'subsidio')->orderBy('tipo_tabla', 'desc')->get();

        return $subsidios;
    }

    public function headings(): array
    {
        return [
            'tipo_tabla',
            'ingreso_desde',
            'ingreso_hasta',
            'subsidio',
        ];
    }
}
