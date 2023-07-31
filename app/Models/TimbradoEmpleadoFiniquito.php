<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoEmpleadoFiniquito extends Model
{
    protected $connection = 'empresa';
    protected $table = 'timbrado_finiquito';
    public $timestamps = false;
}
