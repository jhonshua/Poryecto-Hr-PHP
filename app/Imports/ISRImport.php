<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{Importable, WithHeadingRow, WithValidation, SkipsFailures};

class ISRImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        $data_impuesto = []; 
        foreach ($rows as $row){
            $data_impuesto[] = [
                'limite_inferior' => floatval ($row['limite_inferior']),
                'limite_superior' => floatval ($row['limite_superior']),
                'cuota_fija' => floatval ($row['cuota_fija']),
                'porcentaje' => floatval ($row['porcentaje']),
                'tipo_tabla' => strtoupper($row['tipo_tabla']),
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            ];
        }
        if(count($data_impuesto)>0){
            DB::connection('empresa')->table('impuestos')->insert($data_impuesto); 
        }
        
    }
}
