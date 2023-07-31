<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    protected $connection = 'empresa';
    protected $table = 'rutinas';

    public $timestamps = false;
   // protected $fillable = ["id_empleado","id_periodo","fecha_guardado","confirmado","archivo_generado","name_archivo","ruta","importe","tipo_dispersion","ejercicio"];

   public function valores_conceptos(){
        return $this->hasMany('App\Models\RutinaValor','id_rutina','id')->orderBy('tipo_concepto');
   }
   public function periodoNomina(){
       return $this->hasOne('App\Models\PeriodosNomina','id','id_periodo');
   }

}
