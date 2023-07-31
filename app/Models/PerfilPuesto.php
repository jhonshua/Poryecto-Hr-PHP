<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilPuesto extends Model
{
   
    use HasFactory;
    protected $connection = "empresa";
    protected $table = "perfil_puesto";
    public const CREATED_AT = false;
    public const UPDATED_AT = false;
    public $timestamps = false;
   
    public function puestos(){
        return $this->hasMany('App\Models\Puesto', 'id', 'id_puesto');
    }
    public function departamentos(){
        return $this->hasMany('App\Models\Departamento', 'id', 'id_departamento');
    }
    public function horarios(){
        return $this->hasMany('App\Models\Horario', 'id', 'id_horario');
    }
 
    public function dependencias(){
        return $this->hasMany('App\Models\Departamento', 'id', 'id_dependencia');
    }
}
