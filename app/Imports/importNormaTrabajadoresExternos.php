<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};


class importNormaTrabajadoresExternos implements ToCollection, WithHeadingRow
{

    use Importable, SkipsFailures;

    private $errores = 0;
    private $mensajeDeError = '';
    private $importados = 0;
    private $empleados = array('M'=>array(),'F'=>array());

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row){
            // Si el renglon no esta vacio
            if($row->filter()->isNotEmpty()){

                if(!empty($row['genero']) && !empty($row['nombre']) && !empty($row['apaterno']) && !empty($row['amaterno']) && !empty($row['correo'])){
                    
                    if($row['genero'] == 'M'){
                        
                        array_push($this->empleados['M'],array('id'=>0,'nombre' => $row['nombre'], 'apaterno' => $row['apaterno'],'amaterno' => $row['amaterno'],'genero'=>$row['genero'],'correo'=>$row['correo']));
                        $this->importados++;
                    
                    }else if($row['genero'] == 'F'){
                        
                        array_push($this->empleados['F'],array('id'=>0,'nombre' => $row['nombre'], 'apaterno' => $row['apaterno'],'amaterno' => $row['amaterno'],'genero'=>$row['genero'],'correo'=>$row['correo']));
                        $this->importados++;
                    }

                }else{
                    
                    $this->errores++;
                    $this->mensajeDeError .= "celda vacia \n";
                }
            }
        }
    }
    /**
        * Aplana un Collection para facilitar la busqueda de datos
    */
    private function flat2ItemCollection($collection, $key, $value)
    {
        $flaten = [];
        foreach ($collection as $item) $flaten[$item->$key] = $item->$value;
        return $flaten;
    }

    /**
     * Regresa los resultados de la importacion
     */
    public function getImportedResults()
    {
        try{

            $totalEmpleados = (count($this->empleados['M']) + count($this->empleados['F']));
            $datos =['errores' => $this->errores,'mensajeDeError' => $this->mensajeDeError,'importados'=>$this->importados,'hombres'=>$this->empleados['M'],'mujeres'=> $this->empleados['F'],'total'=>$totalEmpleados];
            $respuesta =1;

        }catch(\Exception $e){
            
            //dd($e);
            $datos=[];
            $respuesta =2;  
        }    
        
        return response()->json(['ok' => $respuesta,'datos'=>$datos]);
    
    }
}
