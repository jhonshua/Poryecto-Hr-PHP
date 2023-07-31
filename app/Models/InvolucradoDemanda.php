<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvolucradoDemanda extends Model
{
    protected $connection = 'empresa';
    protected $table = 'involucrados_demandas';
    protected $fillable = ['id_involucrado'];
    public $timestamps = false;
}
