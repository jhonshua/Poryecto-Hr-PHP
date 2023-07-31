<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamosRequisitos extends Model
{
    public $timestamps = false;
    protected $fillable = ['nombre', 'tipo', 'valor', 'prestamos_tipos_id'];

    /**
     * Obtiene el tipo de prestamo al cual pertenece el requisito
     */
    public function tipoPrestamo()
    {
        return $this->belongsTo('App\Models\PrestamosTipos');
    }
}
