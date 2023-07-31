<?php

namespace App\Models\Covid;

use Illuminate\Database\Eloquent\Model;

class EvidenciaCovid extends Model
{
    protected $table = 'evidencia_covid';
    protected $connection = 'empresa';
}