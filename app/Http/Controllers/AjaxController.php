<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoProduccion;
use App\Console\Commands\EmpleadosAsistencias;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\AvisoMultimedia;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class AjaxController extends Controller
{
    public function listadoInicio(){

        $usuario = Usuario::with('empresas')->find(210);
        /********************************* */
        Session::put('usuarioEmpresas', $usuario->empresas->toArray());
        /********************************* */
        $terminos = $usuario->terminos;
        return view('avisosMultimedia.pantalla.inicio',compact('usuario','terminos'));
    }
    /**
     * Obtiene empleados activos de una BD
     */
    public function obtenerEmpleados(Request $request)
    {
        $return = ['ok' => 0, 'empleado' => null];
        $base = $request->get('b');
        // dd($base);
        if(empty($base)) {
            return response()->json($return);
        }

        if(cambiarBaseAProduccion($base)) {
            // TODO: Cambiar el estatus a 1 al cambiar de servidor o nomenclaturas
            $empleados = EmpleadoProduccion::where('estatus', 1) 
                                ->orderBy('apaterno', 'asc')
                                ->get();
            return response()->json(['ok' => 1, 'empleados' => $empleados]);
        }
    }

    /**
     * Metodo para ejecutar el CRONJob Diario para registrar las asistencias de los empleados
     */
    public function registroAsistenciasCron(Request $request)
    {
        
        return response()->json(['funciona' => 1]);
        $cron = new EmpleadosAsistencias($request->fecha, $request->idEmpresa);
        $cron->handle();
        if ($request->ajax()) {

            return response()->json(['ok' => 1]);
        } else {

            echo 'Sincronizacion terminada';
        }
    }
    public function updateasistenciaDia(Request $request)
    {
        $cron = new EmpleadosAsistencias($request->fecha, $request->idEmpresa);
        $cron->handle();
        if ($request->ajax()) {

            return response()->json(['ok' => $fecha]);
        } else {

            echo 'Sincronizacion terminada';
        }
    }

    public function updateasistencia(Request $request)
    {
        $fecha= date('Y-m-d');
        $cron = new EmpleadosAsistencias($fecha, $request->idEmpresa);
        $cron->handle();
        if ($request->ajax()) {

            return response()->json(['ok' => $fecha]);
        } else {

            echo 'Sincronizacion terminada';
        }
    }
    /**
     * Metodo que ejecuta y obtiene los empleados del mes
    */
    public function cumpleaños($id){
        $empresa = Empresa::find($id);
        cambiarBase($empresa->base);
        /* Cumpleaños*/
        $query = "select `id`, `file_fotografia`, `nombre`, `apaterno`, `amaterno`, `fecha_nacimiento` from `empleados` where `estatus` = 1 and MONTH(fecha_nacimiento) = ". date('m') ." order by DAY(fecha_nacimiento) asc;";
        $cumpleaños = DB::connection('empresa')->select($query);
    
    }
    /**
     * Metodo que obtiene las asistencias
    */
    public function obtieneAsistenciaEmpleados($id){

        $empresa = Empresa::find($id);
        cambiarBase($empresa->base);
        //$dia='21-05-24';
        $dia=date('y-m-d');
        $total = DB::connection('empresa')->select("SELECT count(*) AS total FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.asistencia=1 AND ah.dia='$dia'");

        $faltas = DB::connection('empresa')->select("SELECT count(*) AS faltas FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.asistencia=0 AND ah.dia='$dia'");
    
        $retardos = DB::connection('empresa')->select("SELECT count(*) AS retardos FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.retardo=1 AND ah.dia='$dia'");

        $data = array('total'=>$total[0]->total, 'faltas'=>$faltas[0]->faltas, 'retardos'=>$retardos[0]->retardos);

        return response()->json($data);
        
    }

    public function obteneSalidas($id){
        
        $dias = (date('w') == 1) ? 3 : 1; // si es lunes, se restan 3 dias
        $ayer =  new \DateTime();
        $ayer->modify("-".$dias." day");
        $empresa = Empresa::find($id);

        cambiarBase($empresa->base);
        $resultados=DB::connection('empresa')->table('asistencia_horario as ah')
                                              ->join('empleados as e','e.id','=','ah.id_empleado')
                                              ->select('ah.salida',
                                                       'ah.salida_horario',
                                                       'e.nombre',
                                                       'e.apaterno',
                                                       'e.amaterno',
                                                       'e.file_fotografia')
                                              ->where(['e.estatus'=>1,'ah.dia'=>$ayer->format('Y-m-d')])
                                              ->where('ah.entrada','>',0)
                                              ->orderBy('ah.salida','DESC')
                                              ->get();
        return response()->json($resultados);
        
    }
    public function listado($id){
        
        $hoy = Carbon::now();
        $inicio = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $empresa = Empresa::find($id);
        cambiarBase($empresa->base);
        
        $avisos = AvisoMultimedia::where(["estatus"=>1,'tipo'=>1 ])
                                   ->with('multimedia')->get();
        
        return response()->json(['ok' => 1,'avisos'=>$avisos]);
    }
    public function video($id){

        $empresa = Empresa::find($id);
        cambiarBase($empresa->base);
        $video = AvisoMultimedia::where(["estatus"=>1,'tipo'=>2])->with('multimedia')->get();
        return response()->json(['video'=>$video]);
    }
}
