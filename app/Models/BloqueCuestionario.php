<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueCuestionario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'bloques_cuestionario';
    //bloque_preguntas.bloque_cuestionario_idbloque_cuestionario

    public function preguntas(){
        return $this->belongsToMany('App\Models\Pregunta', 'bloque_preguntas','idbloque','idpregunta')
        ->withPivot('idpregunta','condicional')->orderBy('orden');
    }
}
