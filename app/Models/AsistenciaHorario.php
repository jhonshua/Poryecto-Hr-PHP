<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaHorario extends Model
{
    use HasFactory;
  
    protected $connection = 'empresa';
    protected $table = 'asistencia_horario';
    protected $guarded = [];
    public $timestamps = false;

}
