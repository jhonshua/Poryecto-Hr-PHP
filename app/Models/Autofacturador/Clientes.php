<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    //
    protected $table = 'clientes';
    protected $connection = 'singh';
    public $timestamps = false;

    protected $guarded = [];
}
