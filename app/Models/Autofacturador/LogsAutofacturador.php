<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class LogsAutofacturador extends Model
{
    //
    protected $connection = 'empresa';
    protected $table = 'logs';
    protected $guarded = [];
    public $timestamps = false;

    public function usuarios(){

        cambiarBase('singh');
        return $this->hasOne('\App\Models\Usuario','id','usuario');
    }

}
