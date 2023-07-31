<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class UsoCFDI extends Model
{
    protected $table = 'cat_uso_cfdi';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'clave';
    public $incrementing = false;
}
