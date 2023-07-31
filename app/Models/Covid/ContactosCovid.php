<?php

namespace App\Models\Covid;

use Illuminate\Database\Eloquent\Model;

class ContactosCovid extends Model
{
    protected $connection = 'empresa';
    protected $table = 'contactos_covid';
    public $timestamps = false;
    public $fillable = [];

    public function empleado()
    {
        return $this->belongsTo('App\Models\Empleado', 'id_empleado', 'id');
    }

    //noo
    public function es_contacto_de(){
        return $this->hasMany('App\Models\Covid\RegistroCovid', 'id', 'registro_covid_id')->with('empleado');
    }
}
