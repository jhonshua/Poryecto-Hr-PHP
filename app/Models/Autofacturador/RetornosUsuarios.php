<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class RetornosUsuarios extends Model
{
    //
    protected $table = 'retornos_usuarios';
    protected $connection = 'empresa';
    public $timestamps = false;

    protected $guarded = [];
}
