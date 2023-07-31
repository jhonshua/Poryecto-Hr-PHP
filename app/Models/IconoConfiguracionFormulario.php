<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IconoConfiguracionFormulario extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'iconos_configformulario';
    public $timestamps = false;
    protected $guarded = [];
}
