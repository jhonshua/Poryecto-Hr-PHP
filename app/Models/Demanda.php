<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    protected $connection = 'empresa';
    protected $table = 'demandas_juridico';
    protected $fillable = ['folio','prestaciones_devengadas','indeminizacion_constitucional','motivo','created_at','EstImporte','EstPrestaciones','EstIndmCon','EstIndmAno','EstSalarioCaido','ImporteExtra','motivo_arreglo_conciliacion','estatus','fecha_proxima_audiencia'];

    //protected $primaryKey = 'id_demanda';

    public function audiencias(){
        return $this->hasMany('App\Models\Audiencia', 'id_demanda', 'id')->orderBy('fecha_proxima','desc');
    }

    public function audiencias_costo(){
        return $this->hasMany('App\Models\Audiencia', 'id_demanda', 'id')->where('estatus_final', "=", 'FINALIZADO')->orWhere('estatus_final', "=",'CONCILIADO');
    }

    //personas involucradas en la demanda (actor, demandado, quien contrató)
    public function involucrados(){
        return $this->hasMany('App\Models\InvolucradoDemanda', 'id_demanda_juridico', 'id');
    }

    //datos del empleado que presenta la demanda
    public function empleado(){
        return $this->belongsTo('App\Models\Empleado', 'id_empleado', 'id');
    }
}
