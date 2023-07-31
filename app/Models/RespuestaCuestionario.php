<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestaCuestionario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'respuestas_cuestionarios';
    protected $guarded = [];

    //agregar respuestas de cuestionarios
    public function agregarRespuesta($respuestas){
        
        $respuesta = RespuestaCuestionario::insert($respuestas);
        ($respuesta) ? $resp = true : $resp = false;
        return $resp;
    }
}
