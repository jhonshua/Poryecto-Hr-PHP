<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresasEmisoras extends Model
{
    use SoftDeletes;
    protected $table = 'empresas_emisoras';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
}
