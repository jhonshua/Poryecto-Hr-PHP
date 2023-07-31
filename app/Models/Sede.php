<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
     /*  para usar en la norma con la bd antigua 
        cuando se trabaje con la bd v2 se cambia el nombre de 'sede' a 'sedes' */
    //protected $table = 'sede';+
    protected $connection = 'empresa';
    protected $table = 'sedes';
    protected $guarded = [];
    public $timestamps = false;
}
