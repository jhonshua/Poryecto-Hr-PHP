<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoNorma extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'periodos_norma'; 

    public function trabajadorCuestionarioPeriodo(){
        return $this->belongsToMany('App\Models\Trabajador', 'cuestionarios_trabajadores','idperiodo','idinformacion_trabajador')
        ->withPivot('id','idinformacion_trabajador','idcuestionario','estatus','total_cuestionario')->orderBy('idcuestionario');
    }

    //Empleados de cuestionario 2 y/o 3
    public function trabajadorCuestionarioCalificablePeriodo(){
        return $this->belongsToMany('App\Models\Trabajador', 'cuestionarios_trabajadores','idperiodo','idinformacion_trabajador')
        ->withPivot('id','idinformacion_trabajador','idcuestionario','estatus','total_cuestionario')
        ->where(function($query){
            $query->where('idcuestionario',2)->orWhere('idcuestionario',3);
        })
        
        //->wherePivot('idperiodo','=','periodos_norma.id')
        ->orderBy('idinformacion_trabajador');
    }

    public function validarDiaDentroDePeriodo($dia){
        return PeriodoNorma::where('estatus','=',1)->whereDate('fecha_inicio', "<=", $dia->format('Y-m-d'))->whereDate('fecha_fin_expansion', ">=", $dia->format('Y-m-d'))->get();
    }

    public function tipoCuestionario(){
        return $this->hasMany('App\Models\CuestionarioTrabajador','idperiodo','id')->with('cuestionario')->groupBy('idcuestionario');
    }

    public function excentos(){
        return $this->hasMany('App\Models\Excento','periodo_norma','id')->orderBy('nombre');

    }
}
