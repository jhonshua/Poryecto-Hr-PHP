<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'actividades';

    public function formulario(){
        return $this->hasOne('\App\Models\PeriodoNorma','id','apertura_formulario')->where('estatus',1);
    }

    public function formularioConCuestionarios(){
        return $this->hasOne('\App\Models\PeriodoNorma','id','apertura_formulario')->with(['tipoCuestionario'])->where('estatus',1);
    }

    public function formularioConTrabajadores(){
        return $this->hasOne('\App\Models\PeriodoNorma','id','apertura_formulario')->with(['trabajadorCuestionarioPeriodo','excentos'])->where('estatus',1);
    }
}
