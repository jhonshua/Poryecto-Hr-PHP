<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoCancelacionesFiniquito extends Model
{
    protected $connection = 'empresa';
    protected $table = 'timbrado_cancelaciones_finiquito';
    public $timestamps = false;
    protected $guarded = [];
    
}