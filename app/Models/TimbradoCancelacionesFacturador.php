<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoCancelacionesFacturador extends Model
{
    protected $connection = 'empresa';
    protected $table = 'timbrado_cancelaciones_facturador';
    public $timestamps = false;
    protected $guarded = [];
    
}