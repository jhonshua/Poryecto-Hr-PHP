<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodosNomina;
use App\Models\ConceptosNomina;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class importPrenomina implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    use Importable, SkipsFailures;

    protected $id_periodo;

    public function __construct($id_periodo)
    {
        $this->id_periodo = $id_periodo;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        //tienePermisoA('periodos_nomina');
        cambiarBase(Session::get('base'));

        $periodo =  PeriodosNomina::find($this->id_periodo);
        $columnas = ConceptosNomina::where('tipo_proceso', 0)->where('estatus', 1)->where('file_rool', '!=', 0)->where('activo_en_nomina',1)->where('nomina', 1)->get();

        foreach ($columnas as $col) {
            $concepto = $this->normalize_string($col->nombre_concepto);
            $columnas_ids[$concepto] = $col->id;
        }

        foreach ($rows as $row){

            $row = $row->toArray();
            $registro = [];

            // Se arma el arreglo para despues actualizarlo en la BD
            foreach($columnas_ids as $nombre_concepto => $id){               

                if($nombre_concepto == 'dias_imss'){
                    
                    $registro['dias_imss'] = $row['dias_imss'];
                
                }else if (array_key_exists($nombre_concepto, $row)){           
                    
                    $registro['valor'.$id] = $row[$nombre_concepto];
                } 
            }
  
            DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                ->where('id_periodo', $periodo->id)->where('id_empleado', $row['id'])->where('fnq_valor', 0)
                ->update($registro);
        }
    }

    protected function normalize_string($string)
    {
        $string = str_replace("Á", "a", $string);
        $string = str_replace("É", "e", $string);
        $string = str_replace("Í", "i", $string);
        $string = str_replace("Ó", "o", $string);
        $string = str_replace("Ú", "u", $string);
        $string = str_replace(".", "", $string);
        // $string = str_replace("Ñ", "ñ", $string);
        $string = strtolower($string);
        $string = trim($string);
        $string = str_replace(' ', '_', $string);
        $string = str_replace('/', '', $string);

        return $string;
    }
}
