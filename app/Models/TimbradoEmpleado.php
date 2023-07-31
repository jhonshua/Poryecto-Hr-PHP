<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoEmpleado extends Model
{
    protected $connection = 'empresa';
    protected $table = 'timbrado';
    public $timestamps = false;

    public const TIMBRE_EXITOSO = 1;
    public const TIMBRE_ERROR = 2;

    function periodosNomina(){
        return $this->hasOne('App\Models\periodosNomina', 'id', 'id_periodo');
    }

    function empresaEmisor(){
        return $this->hasOne('App\Models\EmpresaEmisora','rfc','emisor');
    }

    function empresa(){
        return $this->hasOne('App\Models\Empresa','rfc','emisor');
    }

    function empleadoReceptor(){
        return $this->hasOne('App\Models\Empleado','rfc','receptor');
    }

}
