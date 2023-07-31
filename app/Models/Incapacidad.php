<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incapacidad extends Model
{
    protected $connection = 'empresa';
    protected $table = 'incapacidades';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
}
