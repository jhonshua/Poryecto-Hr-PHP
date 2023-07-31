<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Impuesto extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'impuestos';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    public const IMPUESTO_ACTIVO = 1; // activo normal
    public const IMPUESTO_INACTIVO = 0; // inactivo
    
}
