<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamosInformacion extends Model
{
    protected $fillable = ['prestamo_id', 'prestamo_requisito_id', 'valor', 'fecha_creacion', 'fecha_edicion'];
    protected $table = 'prestamos_informacion';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    public function prestamo() {
        return $this->belongsTo('App\Models\Prestamo', 'id');
    }

    public function requisito() {
        return $this->hasOne('App\Models\PrestamosRequisitos', 'id', 'prestamo_requisito_id');
    }
}
