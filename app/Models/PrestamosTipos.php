<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamosTipos extends Model
{
    public $timestamps = false;
    protected $fillable = ['nombre', 'descripcion', 'notas', 'estatus', 'antiguedad_meses','tipo_solicitud'];

    /**
     * Obtiene los requisitos del tipo de prestamo
     */
    public function requisitos()
    {
        return $this->hasMany('App\Models\PrestamosRequisitos');
    }
}
