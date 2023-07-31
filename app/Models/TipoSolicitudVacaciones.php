<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSolicitudVacaciones extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'tipo_solicitud_vacaciones';
    public $timestamps = false;
    protected $guarded = [];
}
