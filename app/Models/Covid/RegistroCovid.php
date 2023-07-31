<?php

namespace App\Models\Covid;

use Illuminate\Database\Eloquent\Model;

class RegistroCovid extends Model
{
    protected $connection = 'empresa';
    protected $table = 'registro_covid';
    public $timestamps = false;
    public $fillable = [];
    
    public function contactos(){
        return $this->hasMany('App\Models\Covid\ContactosCovid', 'registro_covid_id', 'id')->with('empleado');
    }

    public function lo_contagio(){
        return $this->belongsToMany('App\Models\Covid\RegistroCovid','contactos_covid', 'id_registro', 'registro_covid_id')
        ->with('empleado');
    }

    public function evidencias(){
        return $this->hasMany('App\Models\Covid\EvidenciaCovid',  'id_registro_covid', 'id');
    }

    public function empleado()
    {
        return $this->belongsTo('App\Models\Empleado', 'id_empleado', 'id');
    }

}
