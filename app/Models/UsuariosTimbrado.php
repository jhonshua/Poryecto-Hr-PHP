<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuariosTimbrado extends Model
{
    protected $table = 'timbrado_credenciales';
    protected $guarded = [];
    public $timestamps = false;
}
