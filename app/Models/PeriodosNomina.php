<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PeriodosNomina extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'periodos_nomina';
    protected $guarded = [];

    const CREATED_AT = 'fecha_apertura_periodo';
    const UPDATED_AT = 'fecha_edicion';

    // CAMPO ESTATUS
    public const ESTATUS_DISPONIBLE = 1; 
    public const ESTATUS_ELIMINADO = 0; 

    // CAMPO ACTIVO
    public const CERRADO = 2; 
    public const ACTIVO = 1; 
    public const DISP_ABRIR = 3; // Qué es disp ?????
    public const DISP_ABRIR_PERIODO_ACT = 0; // Qué es disp ?????

    function dispersiones(){
        return $this->hasMany('App\Models\Dispersion', 'id_periodo', 'id')->where('confirmado',1)->groupBy('Importe');
    }

    function periodo($ejercicio,$id_periodo,$id_empleado){
        $query = "SELECT * 
                               FROM rutinas$ejercicio
                               WHERE id_periodo = '$id_periodo' 
                               AND  id_empleado = '$id_empleado' 
                               AND fnq_valor = 0;";

        return DB::connection('empresa')->select($query);
    }
    function timbres(){
        return $this->hasMany('App\Models\Timbrado', 'id_periodo', 'id')->where('estatus_timbre', 1);
    }

}
