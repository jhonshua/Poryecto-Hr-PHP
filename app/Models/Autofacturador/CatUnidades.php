<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class CatUnidades extends Model
{
    protected $table = 'cat_unidades';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'clave';
    public $incrementing = false;
}
