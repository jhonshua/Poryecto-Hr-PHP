<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoAsimilados extends Model
{
    protected $connection = 'generica';
    protected $table = 'timbrado_asimilados';
    public $timestamps = false;
  /*  protected $fillable = ['usuario','creado','emisora','metodo','forma','estatus','regimen','tipo_comprobante',
    'folio_relacionado','tipo_relacion','fecha_pago','monto','folio','importe_pagado','num_parcialidad','importe_saldo_anterior',
    'importe_saldo_insoluto','folio_relacionado_2','folio_2','importe_pagado_2','num_parcialidad_2','importe_saldo_anterior_2',
    'importe_saldo_insoluto_2','folio_relacionado_3','folio_3','importe_pagado_3','num_parcialidad_3','importe_saldo_anterior_3',
    'importe_saldo_insolu_3','metodo_3','metodo_2'];
    */
}
