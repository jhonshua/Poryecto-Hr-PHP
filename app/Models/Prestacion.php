<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'prestaciones';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
}
