<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioTipo extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'formulario_tipo';
    public $timestamps = false;
    protected $guarded = [];
}
