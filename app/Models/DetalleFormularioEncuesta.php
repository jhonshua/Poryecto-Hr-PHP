<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFormularioEncuesta extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'detalle_formulario_encuesta';
    public $timestamps = false;
    protected $guarded = [];
}
