<?php

namespace App\Models\Biometrico;

use Illuminate\Database\Eloquent\Model;

class huellaUsuario extends Model
{
      
    protected $connection = 'empresa';
    protected $table = 'huellas_empleado';
    protected $fillable = ['huella','indice','id_empleado'];
    protected $guarded = [];
    public $timestamps = false;
}
