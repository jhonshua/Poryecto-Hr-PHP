<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioPreguntas extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'formulario_preguntas';
    public $timestamps = false;
    protected $guarded = [];
}
