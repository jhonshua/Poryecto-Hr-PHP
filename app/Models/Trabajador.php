<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'informacion_trabajadores';
    public $timestamps = false;

    public function cuestionarios(){
        return $this->hasMany('App\Models\CuestionarioTrabajador','idinformacion_trabajador','id');
    }
}
