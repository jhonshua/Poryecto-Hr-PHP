<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class Retornos extends Model
{
    //
    protected $table = 'retornos';
    protected $connection = 'empresa';
    public $timestamps = false;

    protected $guarded = [];

    public function usuarios(){
        return $this->hasOne('App\Models\Autofacturador\RetornosUsuarios', 'id', 'id_retorno_usuario');
    }
}
