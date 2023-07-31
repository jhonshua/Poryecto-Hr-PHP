<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Console\Commands\EmpleadoAsistencia;
use Illuminate\Support\Facades\DB;
use App\Imports\importAsistencias;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\AsistenciaHorario;
use App\Models\Asistencia;
use App\Models\HorarioDia;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciasController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function inicio(Request $request)
    {      
        //tienePermisoA('asistencia');
        $dia = $request->dia;
        
        $data = $this->datosAsistencia($dia);
        //$home_office = $data['home_office'];
        $empleados = $data['empleados'];
        $departamentos = $data['departamentos'];
        $asistencias = $data['asistencias'];
        $dia = $data['dia'];
        $tipo_asistencia = Session::get('empresa')['tipo_asistencias'];
        return view('empleados_admin.asistencias.inicio', compact('empleados', 'departamentos', 'asistencias', 'dia','tipo_asistencia'));
    
    }

    public function detalle(Request $request)
    {

        cambiarBase(Session::get('base'));

        $id = decrypt($request->id);

        if ($request->fecha_inicio) {
            $fecha_inicio = $request->fecha_inicio;
        }else if (session('fecha_inicio')) { 
            $fecha_inicio = session('fecha_inicio');
        }else {
            $fecha_inicio =  date('Y-m-01');
        }

        if($request->fecha_fin) {
            $fecha_fin = $request->fecha_fin;
        }else if (session('fecha_fin')) { 
            $fecha_fin = session('fecha_fin');
        }else {
            $fecha_fin =  date('Y-m-d');
        }

        if($fecha_inicio > date('Y-m-d') || $fecha_fin > date('Y-m-d')){

            session()->flash('danger', 'Las fechas no pueden ser mayor a la fecha actual. Verifique nuevamente !!');

            return redirect()->route('empleado.asistencias.detalle', $request->id);
            
        } else if($fecha_inicio > $fecha_fin){
            
            return redirect()->route('empleado.asistencias.detalle', $request->id);
        
            session()->flash('danger', 'La fecha inicial no pueden ser mayor a la fecha final. Verifique  nuevamente !!');
            
        }
        $tipo_asistencia = Session::get('empresa')['tipo_asistencias'];
        if($tipo_asistencia == 2 ){

            $empleado = Empleado::where('id', $id)->first();
            $fechas_a_mostrar = CarbonPeriod::create($fecha_inicio, $fecha_fin);
            $asistencias = AsistenciaHorario::where('id_empleado', $id)->whereBetween('dia', [$fecha_inicio, $fecha_fin])->get()->keyBy('dia');
            // ->groupBy('dia')
            $fechas_a_mostrar = CarbonPeriod::create($fecha_inicio, $fecha_fin);

            $asistencias_log = Asistencia::where('id_empleado', $id)->whereBetween('fecha', [$fecha_inicio, $fecha_fin])->get()->keyBy('fecha');

            foreach ($fechas_a_mostrar as $fecha) {

                $fecha = $fecha->format('Y-m-d');
                // numero de la semana
                $num_dia = date('N', strtotime($fecha));
    
                if(!isset($asistencias[$fecha])) {
                    // Sn registro de biometrico
                    $registros[$fecha] = 'SIN REGISTRO';
                } else if(isset($asistencias[$fecha])) {
                    // Asistencia - dia laboral
                    if($asistencias[$fecha]->entrada != null && $asistencias[$fecha]->salida != null){
                        $registros[$fecha] = $asistencias[$fecha];
                    }else{
                        $registros[$fecha] = 'SIN REGISTRO';
                    }
                }   
            }
       
        }else{

            $empleado = Empleado::where('id', $id)->first();
            $fechas_a_mostrar = CarbonPeriod::create($fecha_inicio, $fecha_fin);
            $asistencias =AsistenciaHorario::where('id_empleado', $id)->whereBetween('dia', [$fecha_inicio, $fecha_fin])->get()->keyBy('dia');
            $dias_festivos = HorarioDia::select('fecha_festiva')->where('id_horario', $empleado->id_horario)->whereBetween('fecha_festiva', [$fecha_inicio, $fecha_fin])
                ->get()->keyBy('fecha_festiva')->toArray();
           
            $registros = [];
            $dias_laborables = [
                1 => $empleado->horario->lunes,
                2 => $empleado->horario->martes,
                3 => $empleado->horario->miercoles,
                4 => $empleado->horario->jueves,
                5 => $empleado->horario->viernes,
                6 => $empleado->horario->sabado,
                7 => $empleado->horario->domingo,
            ];
                
             // ciclo en el rango de fechas a mostrar
            foreach ($fechas_a_mostrar as $fecha){

                $fecha = $fecha->format('Y-m-d');
                // numero de la semana
                $num_dia = date('N', strtotime($fecha));

                if(array_key_exists($fecha, $dias_festivos)){
                    // Si es dia festivo
                    $registros[$fecha] = 'DÍA FERIADO O INHABIL';

                } else if($dias_laborables[$num_dia] == 0){
                    // Dia no laborale estipulado en el horario
                    $registros[$fecha] = 'DÍA NO LABORABLE';

                } /* else if(isset($asistencias[$fecha])  && $asistencias[$fecha]->asistencia == 0) {
                    // No asistió
                    $registros[$fecha] = 'NO ASISTIÓ';
                }*/ else if(!isset($asistencias[$fecha])) {
                    // Sn registro de biometrico
                    $registros[$fecha] = 'SIN REGISTRO';
                } else if(isset($asistencias[$fecha])) {
                    // Asistencia - dia laboral
                    $registros[$fecha] = $asistencias[$fecha];
                }
            }
      
        }
    
        return view('empleados_admin.asistencias.detalle', compact('fecha_inicio', 'fecha_fin', 'empleado', 'registros','tipo_asistencia' ));
    }

    public function agregarPermiso(Request $request)
    {
        $dia = $request->dia;
        
        $data = $this->datosAsistencia($dia);

        $empleados = $data['empleados'];
        $departamentos = $data['departamentos'];
        $asistencias = $data['asistencias'];
        $dia = $data['dia'];
        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];

        return view('empleados_admin.asistencias.agregar-permiso',compact('empleados', 'departamentos', 'asistencias', 'dia', 'fecha_inicio', 'fecha_fin'));
    }

    public function  datosAsistencia($dia)
    {
        cambiarBase(Session::get('base'));
        
        if($dia){
            $dia = $dia;
        }else if (session('dia')) { 
            $dia = session('dia');
        }else {
            $dia =  date('Y-m-d');
        }

        $fecha_inicio = $dia;
        $fecha_fin = $dia;

        $usuarioDepts = Session::get('usuarioDepartamentos');
        $activo = Empleado::EMPLEADO_ACTIVO ;
        
        if(Session::get('empresa')['tipo_asistencias'] == 2 ){
            
            $empleados = Empleado::where('estatus',$activo)
                ->whereIn('id_departamento',$usuarioDepts )
                ->orderBy('apaterno', 'asc')
                ->get();
        }else{
            
            $empleados = Empleado::where('estatus',$activo)
                ->where('id_horario', '<>', 0)
                ->whereIn('id_departamento',$usuarioDepts)
                ->orderBy('apaterno', 'asc')
                ->get();
        }
     
        $asistencias =AsistenciaHorario::select('id_empleado','entrada', 'salida', 'asistencia', 'retardo','lugar')->where('dia', $dia)->get()->keyBy('id_empleado');
        //$home_office = Asistencia::where(DB::raw("CAST(fecha AS DATE)") , $dia)->get()->keyBy('id_empleado');
        $departamentos = Departamento::where('estatus', 1)->orderBy('nombre', 'asc')->get()->keyBy('id');

        return $data = array('empleados'=>$empleados,
            'departamentos'=>$departamentos,
            'asistencias'=>$asistencias,
            //'home_office'=>$home_office,
            'dia'=>$dia,
            'fecha_inicio'=>$fecha_inicio,
            'fecha_fin'=>$fecha_fin);
    }
    public function otorgarPermisoPersonal(Request $request)
    {

        $dia = $request->dia;
        $data = $this->datosAsistencia($dia);

        cambiarBase(Session::get('base'));
        $asitencia=AsistenciaHorario::where('id_empleado',$request->empleado)->where('dia',$request->dia)->first();

        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];
        $tipo_permiso = (explode("$", $asitencia->motivo))[0];
        $motivo = (explode("$", $asitencia->motivo))[1];
        return response()->json(['fecha_inicio'=>$fecha_inicio,'fecha_fin'=>$fecha_fin,'motivo' =>$motivo,'tipo_permiso'=>$tipo_permiso]);

    }

    public function permisoGeneral(Request $request)
    {
        try{

            cambiarBase(Session::get('base'));
            $dias_a_justificar = CarbonPeriod::create($request->fecha_inicio, $request->fecha_fin);
            $data = [
                'retardo'=>'0',
                'permiso'=>'1',
                'motivo'=> $request->tipo_permiso.'$'.$request->motivo,
                'autorizo'=> Auth::user()->email,
                'inicio_comida_horario'=>date('Y-m-d H:i:s'),
                'fin_comida_horario'=>date('Y-m-d H:i:s')
            ];
        
            foreach ($dias_a_justificar as $fecha ) {

                ($request->tipo_permiso == 'inasistencia')? $data['asistencia'] = 1 : $data['asistencia'] = 0;
                 
                if($request->tipo_permiso =='p_entrada'){
                    $entrada_horario = $fecha->format('Y-m-d').' '.$request->h_entrada;
                    $data['entrada_horario'] = $entrada_horario;
                }

                if($request->tipo_permiso == 'p_salida'){
                    $salida_horario = $fecha->format('Y-m-d').' '.$request->h_salida;
                    $data['salida_horario'] = $salida_horario;
                }

                foreach ($request->empleados as $id_empleado ) {
                    AsistenciaHorario::updateOrInsert(['id_empleado' => $id_empleado, 'dia' => $fecha->format('Y-m-d') ], $data);
                }
            }

            session()->flash('success', 'Datos se guardaron correctamente !!');

        }catch(\Exception $e){
            session()->flash('danger', 'Error al procesar los datos comunique a su administrador !!');
        }

        return redirect()->route('empleado.asistencias.inicio');

    }

    public function registroAsistenciasCron(Request $request)
    {
        try{
            
            $idempresa= decrypt($request->idempresa);
            $dia = $request->dia;

            $cron = new EmpleadoAsistencia($dia, $idempresa);
            $respuesta = $cron->handle();
            if(empty($respuesta)){ //Sincronizacion completada
                $respuesta = 1;
            }else{
                $respuesta = 2;
            }

        }catch(\Exception $e){
            $respuesta = 3;
        }

        return response()->json(['respuesta' => $respuesta]);
    }

    public function importar(Request $request)
    {
        try{

            $asistencias_import = new importAsistencias;
            Excel::import( $asistencias_import, $request->file('archivo'));
            $resultados = $asistencias_import->getImportedResults();
            
            $errores = $resultados['errores'];
            $importados = $resultados['importados'];
            $mensajeDeError = $resultados['mensajeDeError'];

            if($errores <= 0) $tipo_alerta = 'success';
            elseif($errores > 0 && $importados > 0) $tipo_alerta = 'warning';
            elseif($errores > 0 && $importados <= 0) $tipo_alerta = 'danger';

            $resultados = 'Se procesó correctamente el archivo.';
            if($errores > 0) {
                $resultados .= '<br> Se encontraron los siguientes errores('.$errores.'): <br><br>';
                $resultados .= $mensajeDeError;
            }
            $resultados .= '<br> Se importaron '.$importados.' registros.';
            
            session()->flash($tipo_alerta , $resultados);

            return redirect()->route('empleado.asistencias.inicio');

        }catch(\Exception $e){
     
            session()->flash('danger', 'La fecha inicial no pueden ser mayor a la fecha final. Verifique  nuevamente !!');
        }
    }
}
