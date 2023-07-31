<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleActivosEmpleados extends Model
{
    protected $connection = 'empresa';
    protected $table = 'detalle_activos_empleados';
    public $timestamps = false;
    protected $guarded = [];
}
