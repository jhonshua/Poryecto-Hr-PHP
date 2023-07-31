<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $connection = 'empresa';
    protected $table = 'departamentos';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    public function actividades(){
        return $this->hasMany('App\Models\ActividadesDepartamento', 'id_departamento', 'id');
    }
}


