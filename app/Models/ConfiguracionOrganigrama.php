<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionOrganigrama extends Model
{
    use HasFactory;
    protected $table = 'configuraciones_organigrama';
    public $timestamps = false;
    protected $guarded = [];
}
