<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    protected $connection = 'empresa';
    protected $table = 'facturas_detalle';
    protected $primary_key = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_factura','id_detalle','cantidad','clave','concepto','monto','unidad','estatus',
    'impuesto_retenido'];
}
