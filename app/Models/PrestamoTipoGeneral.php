<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoTipoGeneral extends Model
{
    protected $table = 'prestamos_tipos_generales';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    protected $guarded = [];
}
