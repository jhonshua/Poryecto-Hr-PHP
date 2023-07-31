<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class Vendedores extends Model
{
    //
    protected $table = 'vendedores';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
}
