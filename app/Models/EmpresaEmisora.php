<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaEmisora extends Model
{
    protected $connection = 'singh';
    protected $table = 'empresas_emisoras';
    protected $primaryKey = 'id';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    function registroPatronal(){
        return $this->hasOne('App\Models\RegistroPatronal','id_empresa_emisora','id');
    }

}
