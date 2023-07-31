<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excento extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'excentos_norma';
    public $timestamps = false;
    protected $guarded = [];
}
