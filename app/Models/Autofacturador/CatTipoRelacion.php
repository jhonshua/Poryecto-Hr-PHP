<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatTipoRelacion extends Model
{
    protected $table = 'tipo_relacion';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'clave';
    public $incrementing = false;
}
