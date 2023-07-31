<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazonSocial extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'razon_social';
    public $timestamps = false;
}
