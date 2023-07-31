<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $connection = 'empresa';
    protected $table = 'horarios';
    protected $guarded = [];
    public $timestamps = false;
}
