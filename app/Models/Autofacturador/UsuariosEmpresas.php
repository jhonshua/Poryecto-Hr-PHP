<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class UsuariosEmpresas extends Model
{
    protected $table = 'usuarios_empresas_emisoras';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
}
