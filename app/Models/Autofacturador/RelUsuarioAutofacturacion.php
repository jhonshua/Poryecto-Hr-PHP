<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class RelUsuarioAutofacturacion extends Model
{
    protected $table = 'rel_usuario_autofacturacion';
    protected $connection = 'singh';
    protected $guarded = [];
    public $timestamps = false;

    public function clientes(){
        return $this->hasOne('App\Models\Autofacturador\Clientes', 'id_autofacturacion', 'id');
    }
}
