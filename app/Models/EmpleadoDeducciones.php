<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoDeducciones extends Model
{
    protected $connection = 'empresa';
    protected $table = 'empleados_deducciones';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    public const INACTIVO = 0; // en pausa
    public const ACTIVO = 1; // activo normal
    public const TERMINADO = 2; // pagada completamente
    public const ELIMINADO = 3; // eliminado
}
