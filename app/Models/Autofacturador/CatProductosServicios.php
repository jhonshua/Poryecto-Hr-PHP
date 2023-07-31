<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class CatProductosServicios extends Model
{
    protected $table = 'cat_productos_servicios';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'clave';
}
