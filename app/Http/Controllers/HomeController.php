<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Pendiente;
use App\Models\Bitacora;
use App\Models\Empleado;
use App\Models\Permiso;
use App\Models\UsuariosEmpresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    protected function obtenerAvisos(){
        // dd( Auth::user()->email);
        $avisos = Bitacora::where('usuario', Auth::user()->email)
                            ->where('estatus', 0)
                            ->orderBy('id')
                            ->get();

        return $avisos;
    }

    protected function acualizarEstatusUsuarios()
    {
        cambiarBase(Session::get('base'));
        $empleados = Empleado::whereIn('estatus',  [1,99]);

        $em_if = Session::get('base');
        if($em_if == null){
            return redirect()->route('home');

        }

        if(Session::get('empresa')['sss'] == 0 && 0){
            $empleados->whereIn('id_departamento', Session::get('usuarioDepartamentos'));
        }
        $empleados = $empleados->get();

        $empleadosNoValidos = [];
        foreach($empleados as $empleado){
            if(empty($empleado->rfc) || empty($empleado->curp) || empty($empleado->correo) ){
                $empleadosNoValidos[] = $empleado->id;
            } else if(Session::get('empresa')['sss'] == 1 && empty($empleado->tipo_de_nomina)){
                $empleadosNoValidos[] = $empleado->id;
            }
        }

        if(count($empleadosNoValidos) > 0){
            Empleado::whereIn('id', $empleadosNoValidos)
                    ->update(['estatus' => 10, 'correo' => 'No existe, '.date('Y-m-d H:i:s')]);
        }
    }



    public function index()
    {
        Session::forget('usuarioPermisos');
        Session::forget('empresa');
        Session::forget('usuarioDepartamentos');
        Session::forget('usuarioSedes');
        Session::forget('usuarioPermisos');
        Session::forget('base');

        $permisos =DB::connection('singh')->table('permisos')->where('id_usuario', Auth::user()->id)->first();
        ($permisos) ? Session::put('usuarioPermisos', (array) $permisos) : Session::put('usuarioPermisos', []);

        $id_empresas_usuario = UsuariosEmpresas::where('id_usuario', Auth::user()->id)->pluck('id_empresa');

        $enterprises = Empresa::whereIn('id', $id_empresas_usuario)->where('estatus', 1)->orderBy('razon_social')->get();

        return view('home.home', compact('enterprises'));
    }


    public function calendario()
    {
        return view('calendario');
    }

    public function bandeja()
    {
        if(!Session::get('base'))
            return redirect()->route('home');

        cambiarBase(Session::get('base'));
        $this->acualizarEstatusUsuarios();

        $avisos = $this->obtenerAvisos();
        // dd($avisos);
        $pendiente = new Pendiente();
        // $pendientes = $pendiente->traeTodos(Session::get('base'));
        
        $pendientes = DB::connection('empresa')->select("select round((if(file_ine='',0,1)+if(file_nacimiento='',0,1)+if(file_curp='',0,1)+if(file_nss='',0,1)+if(file_rfc='',0,1)+if(file_comprobante='',0,1)+if(file_fotografia='',0,1)+if(file_contrato='',0,1))*100/8) as porcentaje, id, nombre, apaterno, amaterno, numero_empleado FROM empleados  WHERE estatus=1 having porcentaje!=100");

        $empleado = new Empleado;
        $empleados = $empleado->obtenerEmpleadosSegunDepartamentosAsignados(Session::get('base'));
        // dd($empleados);
        $mes=date('m');

        $cumple = DB::connection('empresa')
            ->table('empleados')
            ->whereMonth('fecha_nacimiento', $mes)
            ->orderByRaw('DAY(fecha_nacimiento)')
            ->where('estatus', '1')
            ->get();

        $dia=date('y-m-d');

        $total = DB::connection('empresa')->select("SELECT count(*) AS total FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.asistencia=1 AND ah.dia='$dia'");

        $faltas = DB::connection('empresa')->select("SELECT count(*) AS faltas FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.asistencia=0 AND ah.dia='$dia'");


        $retardos = DB::connection('empresa')->select("SELECT count(*) AS retardos FROM asistencia_horario AS ah INNER JOIN empleados AS  e ON e.id = ah.id_empleado WHERE e.estatus=1 AND ah.retardo=1 AND ah.dia='$dia'");

        $data = array('total'=>$total[0]->total, 'faltas'=>$faltas[0]->faltas, 'retardos'=>$retardos[0]->retardos);

        $periodo_nomina = DB::connection('empresa')
            ->table('periodos_nomina')
            ->where('activo', '1')
            ->get();
        
        $activo_periodo = count($periodo_nomina);

        $puestos = DB::connection('empresa')
            ->table('puestos')
            ->get();

        $departamentos = DB::connection('empresa')
            ->table('departamentos')
            ->get();
        
        return view('bandeja', compact('avisos','pendientes', 'cumple', 'total', 'faltas', 'data', 'periodo_nomina','empleados','puestos','departamentos','activo_periodo'));
    }


    public function cerrarEvento(Request $request)
    {
        if(!Session::get('base'))
            return response()->json(['ok' => 0]);


        cambiarBase(Session::get('base'));
        Bitacora::where('id', $request->id)
                ->update(['estatus'=> 2, 'finalizo' => Auth::user()->email]);

        session()->flash('success', 'El aviso se actualizó correctamente.');

        return redirect()->route('bandeja');

    }

    public function bitacoracerrarEvento($id)
    {
        if(!Session::get('base'))

            session()->flash('danger', 'Ocurrió un error. Intente nuevamente.');

            return redirect()->route('bandeja');

        cambiarBase(Session::get('base'));
        Bitacora::where('id', $id)
                ->update(['estatus'=> 2, 'finalizo' => Auth::user()->email]);


        session()->flash('success', 'El aviso se actualizó correctamente.');

        return redirect()->route('bandeja');

    }


    public function cancelarNomina(Request $request)
    {

        $id = $request->get('id');
        $referencia = $request->get('ref');
        $razonCancelacion = $request->get('razon');
        $db = Session::get('base');

        if(!$db){

            session()->flash('danger', 'Ocurrió un error. Intente nuevamente.');

            return redirect()->route('bandeja');
        }

        cambiarBase($db);
        $ejercicio = DB::connection('empresa')
            ->table('periodos_nomina')
            ->select('ejercicio')
            ->where('id', $id)
            ->get();

        if(count($ejercicio)) {
            DB::connection('empresa')
                ->table('rutinas' , $ejercicio[0]->ejercicio)
                ->where('id', $referencia)
                ->update(['estatus_confirma'=> 0]);
        }



        $idEvento = 12;
        $descripcion = 'Se ha solicitado revision de la nomina del periodo ' . $referencia . '. ' . $razonCancelacion;
        $tipo = 'RP';

        agregarABitacora($db, $idEvento, $tipo, $referencia, $descripcion);
        envioAvisosXMail($db, $idEvento, $razonCancelacion);

        Bitacora::where('id', $id)
                ->update(['estatus'=> 2, 'finalizo' => Auth::user()->email]);

        if($request->op == 0){
            session()->flash('success', 'La nomina se aceptó correctamente.');
        }else{
            session()->flash('success', 'La nomina se envió a revisión.');
        }

        return redirect()->route('bandeja');

    }


}
