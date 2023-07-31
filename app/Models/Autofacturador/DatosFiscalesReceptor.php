<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class DatosFiscalesReceptor extends Model
{
    public $timestamps = false;
    protected $table = 'datos_fiscales_receptor';
    protected $connection = 'empresa';
    protected $guarded = [];
}
