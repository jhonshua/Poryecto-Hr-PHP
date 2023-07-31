<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Carbon;

class AguinaldoImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;
    public $ejercicio;
    public function  __construct($ejercicio)
    {
        $this->ejercicio = $ejercicio;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        foreach ($rows as $row){
            // dd($row['fecha_alta']);
            // conversion de excel a date
            // $fecha_fiscal = gmdate("Y-m-d", ($row['fecha_alta'] - 25569) * 86400);
            if($row['fecha_fiscal']=='0000-00-00'){
                $fecha_fiscal='0000-00-00';
            }else{
               $fecha_fiscal=Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_fiscal'])); 
               $fecha_fiscal=$fecha_fiscal->format('Y-m-d');
            }
            
            // if(Session::get('usuarioPermisos')['id_usuario']==64){
            //        //dd($fecha_fiscal->format('Y-m-d'));
            //     }

            DB::connection('empresa')
                ->table('aguinaldo')
                ->where('id_empleado', $row['id'])
                ->where('ejercicio', $this->ejercicio)
                ->update(
                    [
                        'fecha_fiscal'           => $fecha_fiscal,
                        'pension_alimenticia'   => floatval($row['pension_alimenticia']),
                        'descuentos_otros'      => floatval($row['descuentos_otros']),
                        's_pension_alimenticia' => floatval($row['s_pension_alimenticia']),
                        's_descuentos_otros'    => floatval($row['s_descuentos_otros']),
                    ]
                );
        }
    }
}
