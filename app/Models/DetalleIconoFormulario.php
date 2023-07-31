<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleIconoFormulario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'detalle_iconos_formularios';
    public $timestamps = false;
    protected $guarded = [];
}
