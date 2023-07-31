<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activos extends Model
{
    protected $connection = 'empresa';
    protected $table = 'activos';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    protected $guarded = [];
}
