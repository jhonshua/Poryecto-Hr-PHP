<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoDetalle extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'empresa';
    protected $table = 'puestos_detalle';
    protected $guarded = [];
}
