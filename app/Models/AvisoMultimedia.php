<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvisoMultimedia extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'avisos';
    protected $guarded = [];
    public $timestamps = false;

    public function multimedia(){
        return $this->hasMany('\App\Models\Multimedia','id_avisos','id');
    }
}
