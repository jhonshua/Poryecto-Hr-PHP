<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;

class RegimenFiscal extends Model
{
    protected $table = 'cat_regimen_fiscal';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'codigo';
}
