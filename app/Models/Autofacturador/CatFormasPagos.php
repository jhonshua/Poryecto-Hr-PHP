<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class CatFormasPagos extends Model
{
    //
    protected $table = 'cat_formas_pagos';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'clave';
    public $incrementing = false;
}
