<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutinaValor extends Model
{
    protected $connection = 'empresa';
    protected $table = 'rutina_valores';
    public $timestamps = false;
   // protected $fillable = ["id_empleado","id_periodo","fecha_guardado","confirmado","archivo_generado","name_archivo","ruta","importe","tipo_dispersion","ejercicio"];
}
