<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamosNotas extends Model
{
    protected $fillable = ['prestamo_id', 'texto'];
    protected $table = 'prestamos_notas';
}
