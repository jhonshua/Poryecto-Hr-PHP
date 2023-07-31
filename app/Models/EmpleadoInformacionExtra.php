<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoInformacionExtra extends Model
{
    protected $connection = 'empresa';
    protected $table = 'empleados_informacion_extra';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;
}
