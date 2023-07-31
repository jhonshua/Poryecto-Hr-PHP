<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfirmacionIncidencia extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'confirmacion_incidencias';
    public $timestamps = false;
    protected $guarded = [];
}