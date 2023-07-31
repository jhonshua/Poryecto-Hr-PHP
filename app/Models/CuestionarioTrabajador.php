<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CuestionarioTrabajador extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'cuestionarios_trabajadores';
    const CREATED_AT = 'fecha_inicio';
    const UPDATED_AT = 'fecha_fin';

    public function respuestas(){ // traer las respuestas del trabajador
        return $this->belongsToMany('App\Models\Pregunta', 'respuestas_cuestionarios','idcuestionario_trabajador','idpregunta')
        ->withPivot('id','valor')->orderBy('idpregunta');

    }

    public function totales(){ // traer los totales del trabajador
        return $this->belongsToMany('App\Models\Catalogo', 'totales_clasificacion','idcuestionario_trabajador','idclasificacion')
        ->withPivot('id','total')->orderByRaw("clase ASC, orden ASC");
    }

    public function cuestionario(){
        return $this->hasOne('App\Models\Cuestionario','id','idcuestionario')->with('bloques');
    }

    public function totalesCategoria(){
        return $this->hasMany('App\Models\TotalClasificacion','idcuestionario_trabajador','id');
    }

    public function datosPersonales(){
        return $this->hasOne('App\Models\Trabajador','id','idinformacion_trabajador', DB::raw('CONCAT(nombre," ", paterno, " ", materno) AS nombre_completo'));
    }
}
