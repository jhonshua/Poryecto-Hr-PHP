<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorariosDias extends Model
{
    use HasFactory;
    protected $connection = "empresa";
    protected $table = "horarios_dias";
    public const CREATED_AT = false;
    public const UPDATED_AT = false;
    public $timestamps = false; 
}
