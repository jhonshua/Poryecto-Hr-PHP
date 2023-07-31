<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionFormulario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'configuracion_formulario';
    protected $guarded = [];
}
