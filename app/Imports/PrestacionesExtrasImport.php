<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};
use App\Models\Empleado;

class PrestacionesExtrasImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    private $errores = 0;
    private $mensajeDeError = '';
    private $importados = 0;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));        
        $certificadosExistentes = DB::connection('empresa')->table('prestaciones_extras')->get()->keyBy('id');		
        $certificadosExistentes = $this->flat2ItemCollection($certificadosExistentes, 'id_empleado', 'num_certificado');    
        foreach ($rows as $row){
            $errores = 0;
            if(strlen($row['numero_certificado']) > 0){
                // Quitamos el empleado actual del foreach
                $certificadosExistentes_temp = $certificadosExistentes;
                if(isset($certificadosExistentes_temp[$row['id']])){
                    unset($certificadosExistentes_temp[$row['id']]);
                }                   

                // Existe el numero de certificado en otro empleado
                if(array_search($row['numero_certificado'], $certificadosExistentes_temp) !== false){
                    $this->errores++;
                    $errores++;
                    $this->mensajeDeError .= 'El No. de certificado del empleado con ID: '.$row['id'].' ya esta asignado a otro empleado. <br>';
                }
                
                if(strlen($row['numero_certificado']) != 15 ){
                    $this->errores++;
                    $errores++;
                    $this->mensajeDeError .= 'El No. de certificado del empleado con ID: '.$row['id'].' debe de tener 15 digitos. <br>';
                }
            }
            if($errores == 0){
                $estatus = (strtoupper($row['activo_prestaciones']) == 'SI') ? 1 : 0;

                DB::connection('empresa')->table('prestaciones_extras')->updateOrInsert(
                    [
                        'id_empleado' => $row['id']
                    ],
                    [
                        'estatus' => $estatus,
                        'num_certificado' => ($row['numero_certificado']) ?? '',
                        'valor_seguro_GM' => ($row['valor_seguro_gastos_m']) ?? '',
                        'valor_plan_espejo' => ($row['valor_plan_espejo']) ?? '',
                        'fecha_edicion' => date('Y-m-d H:i:s')
                    ]
                );
                $this->importados++;
            }
        }
    }

    /**
     * Aplana un Collection para facilitar la busqueda de datos
     */
    private function flat2ItemCollection($collection, $key, $value)
    {
        $flaten = [];
        foreach ($collection as $item) {
            $flaten[$item->$key] = $item->$value;
        }
        return $flaten;
    }

    /**
     * Regresa los resultados de la importacion
     */
    public function getImportedResults()
    {
        return [
            'importados' => $this->importados,
            'errores' => $this->errores,
            'mensajeDeError' => $this->mensajeDeError
        ];
    }
}
