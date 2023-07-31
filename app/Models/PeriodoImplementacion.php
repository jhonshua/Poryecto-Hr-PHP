<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoImplementacion extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'periodos_implementacion';

    public function validarDiaDentroDePeriodoImplementacion($dia, $sede = null){
        if($sede != null){
            return PeriodoImplementacion::where('sede',$sede)->whereDate('fecha_inicio', "<=", $dia->format('Y-m-d'))->whereDate('fecha_fin', ">=", $dia->format('Y-m-d'))->with('actividades','actividad_formulario','sede_asignada')->get();
        }
        return PeriodoImplementacion::whereDate('fecha_inicio', "<=", $dia->format('Y-m-d'))->whereDate('fecha_fin', ">=", $dia->format('Y-m-d'))->with('actividades','actividad_formulario')->get();
    }

    public function interpretaciones(){
        return $this->hasMany('App\Models\Interpretacion','idperiodo_implementacion');
    }

    public function actividades(){
        return $this->hasMany('App\Models\Actividad','idperiodo_implementacion')->orderBy("fecha_inicio");
    }

    public function actividad_formulario(){
        return $this->hasMany('App\Models\Actividad','idperiodo_implementacion')->whereNotNull('apertura_formulario')->where('estatus',1)->with(['formularioConCuestionarios']);
    }

    public function actividad_formulario_trabajadores(){
        return $this->hasMany('App\Models\Actividad','idperiodo_implementacion')->whereNotNull('apertura_formulario')->where('estatus',1)->with(['formularioConTrabajadores']);
    }

    public function encargados(){
        return $this->hasMany('App\Models\Encargado','idperiodo_implementacion');
    }

    public function sede_asignada(){
       // if(Schema::connection('generica')->hasTable('sedes')){
            return $this->hasOne('\App\Models\Sede','id','sede');
     /*   }
        return collect(array());*/
    }

    public function razon_social_asignada(){
        return $this->hasOne('\App\Models\RazonSocial','id','razon_social');
    }
}
