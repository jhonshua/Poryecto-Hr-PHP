<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use DataTables;
class Biometrico extends Model
{
    protected $connection = 'empresa';
    protected $table = 'biometricos';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    protected $fillable = [
        'nombre','ip','puerto', 'mac', 'modelo','num_serie','firmware','plataforma','proveedor','estatus',
    ];

    public function traeTodos($base = null){
        if(empty($base)) return null;

        if(cambiarBase($base)) {
            $biometricos = Biometrico::all();
        return $biometricos;
        }
    }
    
}
