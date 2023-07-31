<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\formularios\FormularioController;

class EncuestaCovidResultados implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $id_encuesta;
    public $id_empleado;
    public $estatus;
    public function __construct($id_encuesta,$estatus,$id_empleado)
    {
        $this->id_encuesta = $id_encuesta;
        $this->estatus = $estatus;
        $this->id_empleado = $id_empleado;
    }

    public function view(): View
    {
        cambiarBase(Session::get('base'));
        
        $id_encuesta = $this->id_encuesta ;
        $estatus = $this->estatus ;
        $id_empleado = $this->id_empleado ;

        if(!empty($id_empleado)){
            $params = ['e.id'=>$id_empleado,'fe.id'=> $id_encuesta];
        }elseif($estatus == 5){
            $params = ['fe.id'=> $id_encuesta];
        }else{
            $params = ['dt.estatus'=>$estatus,'fe.id'=> $id_encuesta];
        } 
        
        $empleados_asignados=DB::connection('empresa')->table('detalle_formulario_encuesta as dt')
            ->join('empleados as e','e.id','=','dt.id_empleado')
            ->join('departamentos as d','d.id','=','e.id_departamento')
            ->join('formulario_encuesta as fe','fe.id','=','dt.id_encuesta' )
            ->select('e.id',
                    'e.nombre', 
                    'e.apaterno', 
                    'e.amaterno', 
                    'd.nombre as departemento',
                    'dt.id_empleado',
                    'dt.id_encuesta',
                    'dt.estatus',
                    'e.correo',
                    'e.fecha_nacimiento')
            ->where($params)
            ->orderBy('e.nombre', 'asc')
            ->get();
        $formularioController = new FormularioController();

        $datos_personales = [];
        $respuestas =[];
        foreach($empleados_asignados  as $empleados){

            $edad = $formularioController->obtenerDiffYears($empleados->fecha_nacimiento);
            
            $datos_personales[] = array('nombre_completo' => $empleados->nombre.' '.$empleados->apaterno.' '.$empleados->amaterno,
                                        'departamento' => $empleados->departemento,
                                        'correo' => $empleados->correo,
                                        'edad'=>$edad);
       
            $respuestas[] = $formularioController->resultadosRespuestas($empleados->id_empleado,$id_encuesta);   
        }
        
        return view('formularios.pdfs.excel-encuesta',  compact('datos_personales','respuestas'));  
    }
}
