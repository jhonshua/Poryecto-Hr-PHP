<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioEncuesta extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'formulario_encuesta';
    protected $guarded = [];
}
