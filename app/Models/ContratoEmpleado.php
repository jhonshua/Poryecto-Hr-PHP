<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoEmpleado extends Model
{
    protected $connection = 'empresa';
    protected $table = 'contratos';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    protected $fillable = [""];
}
