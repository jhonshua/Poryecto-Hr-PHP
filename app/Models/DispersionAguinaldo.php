<?php

namespace App\Models\Nomina;

use Illuminate\Database\Eloquent\Model;

class DispersionAguinaldo extends Model
{
    protected $connection = 'empresa';
    protected $table = 'dispersiones_aguinaldo';
    public $timestamps = false;
    protected $fillable = ["id_empleado","ejercicio","fecha_guardado","confirmado","archivo_generado","name_archivo","ruta","importe"];
}
