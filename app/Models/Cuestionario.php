<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuestionario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'cuestionarios';

    public function bloques(){
        return $this->hasMany('App\Models\BloqueCuestionario', 'idcuestionario', 'id')->with('preguntas');
    }
}
