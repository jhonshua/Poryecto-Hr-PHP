<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametros extends Model
{
    use HasFactory;
    protected $connection = "empresa";
    protected $table = "parametros";
    public const CREATED_AT = false;
    public const UPDATED_AT = false;
    public $timestamps = false;
    
}
