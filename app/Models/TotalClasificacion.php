<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalClasificacion extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'totales_clasificacion';
    
    public $timestamps = false;
    protected $fillable = ['idcuestionario_trabajador','total'];
    public $clasificacion_por_pregunta = [
        1 => [1,8], 2 => [1,8], 3 => [1,8], 4 => [1,8], 5 => [1,8],
        6 => [4,9], 7 => [4,9], 8 => [4,9], 9 => [4,9], 10 => [4,9],
        11 => [4,9], 12 => [4,9], 13 => [4,9], 14 => [4,9], 15 => [4,9],
        16 => [4,9], 17 => [5,11], 18 => [5,11], 19 => [5,12], 20 => [5,12],
        21 => [5,12], 22 => [5,12], 23 => [4,10], 24 => [4,10], 25 => [4,10],
        26 => [4,10], 27 => [4,10], 28 => [4,10], 29 => [4,10], 30 => [4,10],
        31 => [6,13], 32 => [6,13], 33 => [6,13], 34 => [6,13], 35 => [4,10],
        36 => [4,10], 37 => [6,13], 38 => [6,13], 39 => [6,13], 40 => [6,13],
        41 => [6,13], 42 => [6,14], 43 => [6,14], 44 => [6,14], 45 => [6,14],
        46 => [6,14], 47 => [7,16], 48 => [7,16], 49 => [7,16], 50 => [7,16],
        51 => [7,16], 52 => [7,16], 53 => [7,17], 54 => [7,17], 55 => [7,17],
        56 => [7,17], 57 => [6,15], 58 => [6,15], 59 => [6,15], 60 => [6,15],
        61 => [6,15], 62 => [6,15], 63 => [6,15], 64 => [6,15], 65 => [4,9],
        66 => [4,9], 67 => [4,9], 68 => [4,9], 69 => [6,14], 70 => [6,14],
        71 => [6,14], 72 => [6,14]
    ];

  
    //función que actualiza la clasificación de los cuestionarios
    public function actualizarTotalNvo($cuestionario_trabajador,$datos){
        $dinamico = array();
        foreach($datos as $respuesta){
            if(!empty($this->clasificacion_por_pregunta[$respuesta['idpregunta']])){
                foreach($this->clasificacion_por_pregunta[$respuesta['idpregunta']] as $clasificacion){
                    $dinamico[$clasificacion] = (!empty($dinamico[$clasificacion]))? $dinamico[$clasificacion] + $respuesta['valor'] : $respuesta['valor'];
                }
            }
        }
        if(count($dinamico) > 0){
            foreach($dinamico as $clase => $valor){
                $totalClase =  TotalClasificacion::where("idcuestionario_trabajador","=",$cuestionario_trabajador)->where("idclasificacion","=",$clase)->get();
                if(!empty($totalClase[0])){
                    foreach($totalClase as $total){
                        $total->total = $total->total + $valor;
                        $total->save();
                    }
                }else{
                    TotalClasificacion::insert([
                        'idclasificacion' => $clase,
                        'idcuestionario_trabajador' => $cuestionario_trabajador,
                        'total' => $valor
                    ]);
                }
            }
        }
    }
}
