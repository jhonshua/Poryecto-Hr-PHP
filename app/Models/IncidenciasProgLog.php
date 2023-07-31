<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidenciasProgLog extends Model
{
    protected $connection = 'empresa';
    protected $table = 'incidencias_prg_log';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
}
