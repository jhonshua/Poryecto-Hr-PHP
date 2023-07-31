<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    //
    protected $table = 'comisiones';
    protected $connection = 'empresa';
    public $timestamps = false;

    protected $guarded = [];
}
