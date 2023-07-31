<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class ComprobantesPagos extends Model
{
    //
    protected $table = 'comprobantes_pagos';
    protected $connection = 'empresa';
    public $timestamps = false;

    protected $guarded = [];

    public const PROCESO_CANCELADO = 0; // activo normal
    public const PAGO_SOLCITADO = 1; // pago solicitado
    public const PAGO_FACTURADO = 3; // activo normal
    public const PAGO_CANCELADO = 99; // activo normal

    public function getCFDI(){
        return $this->hasOne('App\Models\Autofacturador\Cfdi', 'id', 'id_cfdi');
    }

    public function formaPago(){
        return $this->hasOne('App\Models\Autofacturador\CatFormasPagos', 'clave', 'tipo_pago');
    }

}
