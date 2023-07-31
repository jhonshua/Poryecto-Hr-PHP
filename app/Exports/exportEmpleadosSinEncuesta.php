<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use App\Models\Trabajador;
use Illuminate\Support\Facades\DB;

class exportEmpleadosSinEncuesta implements FromCollection, WithHeadings
{

    /**
    * @return \Illuminate\Support\Collection
    */
    public $id;
    public function __construct($id)
    {
        $this->id =$id;
        
    }

    public function collection()
    {
     
        cambiarBase(Session::get('base'));   
        $sedes= DB::connection('empresa')->table('periodos_implementacion')->where('sede','<>', null)->count();
        $id = $this->id;
        if(empty($id) && $sedes===0 ){
            
            $trabajadores = Trabajador::join('cuestionarios_trabajadores', "informacion_trabajadores.id", "=", "cuestionarios_trabajadores.idinformacion_trabajador")
                ->where("cuestionarios_trabajadores.estatus","<>","1" )
                ->select("informacion_trabajadores.nombre",
                        "informacion_trabajadores.paterno",
                        "informacion_trabajadores.materno",
                        "informacion_trabajadores.correo",
                        "informacion_trabajadores.sexo")
                ->with('cuestionarios')
                ->orderBy('informacion_trabajadores.nombre','ASC')
                ->groupBy("informacion_trabajadores.id")
                ->get();

        }else if(empty($id) && $sedes > 0 ){

            $trabajadores = Trabajador::join('cuestionarios_trabajadores', "informacion_trabajadores.id", "=", "cuestionarios_trabajadores.idinformacion_trabajador")
                ->join('periodos_implementacion','periodos_implementacion.id','=','cuestionarios_trabajadores.idperiodo')
                ->join('sedes','sedes.id','=','periodos_implementacion.sede')
                ->where("cuestionarios_trabajadores.estatus","<>","1" )
                ->where("periodos_implementacion.sede","<>",null )
                ->select("informacion_trabajadores.nombre",
                        "informacion_trabajadores.paterno",
                        "informacion_trabajadores.materno",
                        "informacion_trabajadores.correo",
                        "informacion_trabajadores.sexo",
                        "sedes.nombre AS sede")
                ->with('cuestionarios')
                ->orderBy('sedes.nombre','DESC')
                ->groupBy("informacion_trabajadores.id")
                ->get();

        }else{

            $trabajadores = Trabajador::join('cuestionarios_trabajadores', "informacion_trabajadores.id", "=", "cuestionarios_trabajadores.idinformacion_trabajador")
                ->join('periodos_implementacion','periodos_implementacion.id','=','cuestionarios_trabajadores.idperiodo')
                ->join('sedes','sedes.id','=','periodos_implementacion.sede')
                ->where("cuestionarios_trabajadores.estatus","<>","1" )
                ->where("periodos_implementacion.sede","=",$id )
                ->select("informacion_trabajadores.nombre",
                        "informacion_trabajadores.paterno",
                        "informacion_trabajadores.materno",
                        "informacion_trabajadores.correo",
                        "informacion_trabajadores.sexo",
                        "sedes.nombre AS sede")
                ->with('cuestionarios')
                ->orderBy('sedes.nombre','DESC')
                ->groupBy("informacion_trabajadores.id")
                ->get();
        }
        
        return $trabajadores;
    }

    public function headings(): array
    {
        return ['nombre','apaterno','amaterno','correo','genero','sede'];
    }
}
