<?php

namespace App\Http\Controllers\consultas;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\AsistenciaHorario;
use App\Models\Parametros;
use App\Exports\AsistenciasExportTipoA;
use App\Exports\AsistenciasExportTipoB;
use Maatwebsite\Excel\Facades\Excel;
use App\Console\Commands\EmpleadoAsistencia;
use App\Models\Asistencia;

class ConsultasController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    /* Obtiene los departamentos disponibles para su selección */
    public function departamentosAsistencias()
    {
        cambiarBase(Session::get('base'));
        $dptos_asignados = Session::get('usuarioDepartamentos');
        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $dptos_asignados)->orderBy('nombre', 'asc')->get();

        return view('consultas.reporte-asistencias', compact('departamentos'));
    }
    /* Muestra la tabla reporte */
    public function reporteAsistencias(Request $request)
    {
        cambiarBase(Session::get('base'));

        $parametros_empresa = Parametros::all();

        $biometrico = $parametros_empresa[0]['biometrico'];

        $fecha = ($request->fecha) ?: date('Y-m-d');
        $deptos = $request->deptos;

        $deptos_asignados = Session::get('usuarioDepartamentos');
        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get()->keyBy('id');

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->where('id_horario', '<>', 0)
            ->whereIn('id_departamento', $deptos)
            ->orderBy('apaterno', 'asc')
            ->get();

        $home = Asistencia::where('fecha', 'like', '%' . $fecha . '%')->get()->keyBy('id_empleado');
        $asistencias = AsistenciaHorario::where('dia', $fecha)->get()->keyBy('id_empleado');

   
         foreach ($empleados as $empleado) {
            $empleado->asistencia = (isset($asistencias[$empleado->id])) ? $asistencias[$empleado->id] : null;
           
        } 
        $dia = date('Y-m-d');


        return view('consultas.reporte-asistencias-tabla', compact('departamentos', 'empleados', 'fecha', 'deptos', 'dia', 'home'));
    }
    /* Sincronización con biometricos */
    public function sincronizarAsistencias(Request $request)
    {
        $cron = new EmpleadoAsistencia($request->fecha, $request->idEmpresa);
        $cron->handle();
        if ($request->ajax()) {

            return response()->json(['ok' => 1]);
        } else {

            echo 'Sincronizacion terminada';
        }
    }
    /*  */
    public function fechasAsistencias(Request $request)
    {
        $tipo = ($request->tippo_asistencias) ? $request->tipo_asistencias : 1;
        if ($tipo == 2) {
            return Excel::download(new AsistenciasExportTipoB($request->fecha_inicio, $request->fecha_fin), "Asistencia-{$request->fecha_inicio}-{$request->fecha_fin}.xlsx");
        } else {
            return Excel::download(new AsistenciasExportTipoA($request->fecha_inicio, $request->fecha_fin), "Asistencia-{$request->fecha_inicio}-{$request->fecha_fin}.xls");
        }
    }
}
