<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multimedia extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'avisos_multimedia';
    protected $guarded = [];
    public $timestamps = false;
}
