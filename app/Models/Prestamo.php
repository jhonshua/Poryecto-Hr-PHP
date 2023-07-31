<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $fillable = ['prestamos_tipo_id', 'usuario_id', 'empleado_id', 'empleado', 'empresa_id', 'medio_contacto', 'estatus', 'fecha_creacion', 'fecha_edicion', 'fecha_cierre', 'amortizacion', 'costo_real'];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    public const PRESTAMO_CERRADO = 0;
    public const PRESTAMO_ABIERTO = 1;
    public const PRESTAMO_BORRADO = 2;
    public const PRESTAMO_RECHAZADO = 3;
    public const PRESTAMO_PARA_REVISION = 4;

    public function empresa() {
        return $this->hasOne('App\Models\Empresa', 'id', 'empresa_id');
    }

    public function tipoPrestamo() {
        return $this->hasOne('App\Models\PrestamosTipos', 'id', 'prestamos_tipo_id');
    }

    public function notas() {
        return $this->hasMany('App\Models\PrestamosNotas', 'prestamo_id')->orderBy('created_at', 'desc');
    }

    public function usuario() {
        return $this->hasOne('App\Models\Usuario', 'id', 'usuario_id');
    }

    public function requisitosLlenos() {
        return $this->hasMany('App\Models\PrestamosInformacion', 'prestamo_id');
    }
}
