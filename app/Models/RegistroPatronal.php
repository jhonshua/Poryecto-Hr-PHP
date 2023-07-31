<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroPatronal extends Model
{
    use HasFactory;
    protected $connection = 'singh';
    protected $table = 'registro_patronal';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
}
