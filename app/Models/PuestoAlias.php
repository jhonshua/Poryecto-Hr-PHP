<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoAlias extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'empresa';
    protected $table = 'puestos_alias';
    protected $guarded = [];
}
