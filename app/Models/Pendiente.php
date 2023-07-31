<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use DataTables;
class Pendiente extends Model
{
    protected $connection = 'empresa';
    protected $table = 'pendientes';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    protected $fillable = [
        'titulo','descripcion','archivo', 'estatus', 
    ];

    public function traeTodos($base = null){
        if(empty($base)) return null;

        if(cambiarBase($base)) {
            $pendientes = Pendiente::all();
        return $pendientes;
        }
    }
    
}
