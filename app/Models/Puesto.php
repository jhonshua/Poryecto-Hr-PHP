<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $connection = 'empresa';
    protected $table = 'puestos';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
}
