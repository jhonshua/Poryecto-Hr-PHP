<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};
use Illuminate\Support\Facades\DB;

class SubsidioImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        $data_subsidio = []; 
        foreach ($rows as $row){
            $data_subsidio[] = [
                'ingreso_desde' => floatval ($row['ingreso_desde']),
                'ingreso_hasta' => floatval ($row['ingreso_hasta']),
                'subsidio' => floatval ($row['subsidio']),
                'tipo_tabla' => strtoupper($row['tipo_tabla']),
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            ];
        }
        if(count($data_subsidio)>0){
            DB::connection('empresa')->table('subsidios')->insert($data_subsidio); 
        }
    }
}
