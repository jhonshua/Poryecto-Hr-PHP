<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoSingh extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $connection = 'singh';
    protected $table = 'permisos';
    protected $guarded = [];
}
