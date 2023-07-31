<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioRespuesta extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'formulario_respuestas';
    public $timestamps = false;
    protected $guarded = [];
}
