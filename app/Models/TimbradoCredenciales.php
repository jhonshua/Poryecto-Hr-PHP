<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimbradoCredenciales extends Model
{
    protected $connection = 'singh';
    protected $table = 'timbrado_credenciales';
    public $timestamps = false;
}
