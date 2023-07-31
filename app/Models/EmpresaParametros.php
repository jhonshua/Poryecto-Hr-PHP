<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaParametros extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'parametros';
    protected $guarded = [];
}
