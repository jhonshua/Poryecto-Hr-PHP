<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class CatMetodosPagos extends Model
{
    //
    protected $table = 'cat_metodos_pagos';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
}
