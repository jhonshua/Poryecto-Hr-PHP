<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\Sede;
use App\Models\Puesto;
use App\Models\Horario;
use App\Models\Biometrico;
use App\EmpleadoLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Biometrico\asignacionBiometrico;
use App\Models\Biometrico\huellaUsuario;

class ReingresosController extends Controller
{
    protected $archivos = ['file_ine' => 'IDENTIFICACIÓN OFICIAL VIGENTE', 'file_fotografia' => 'FOTOGRAFIA', 'file_nacimiento' => 'ACTA DE NACIMIENTO', 'file_curp' => "CURP", 'file_nss' => 'NSS', 'file_rfc' => 'RFC', 'file_comprobante' => 'COMPROBANTE DE DOMICILIO', 'file_aviso' => 'AVISO DE RETENCIONES INFONAVIT', 'file_estado_cuenta' => 'ESTADO DE CUENTA', 'file_contrato' => 'CONTRATO', 'file_analisis' => 'ANÁLISIS', 'file_fonacot' => 'FONACOT', 'file_curriculum' => 'CURRICULUM', 'file_fiel_imss' => 'AFIL IMSS'];

    public function tablaReingresos()
    {
        cambiarBase(Session::get('base'));

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_BAJA])
            ->where('fecha_baja', '>', '1950-01-01')
            ->orderBy('apaterno', 'asc')->get();

        $departamentos = Departamento::where('estatus', 1)
            ->orderBy('nombre', 'asc')->get();

        return view('empleados_admin.reingresos.tabla-reingresos', compact('empleados', 'departamentos'));
    }

    public function individualReingresos(Request $request)
    {

        try {
            cambiarBase(Session::get('base'));
            $empleado = Empleado::find($request->id);

            $empleado->fecha_ult_alta = $empleado->fecha_alta;
            $empleado->fecha_alta = $request->fecha_alta;
            $empleado->fecha_antiguedad = $request->fecha_alta;
            $empleado->estatus = Empleado::EMPLEADO_ACTIVO;
            $empleado->finiquitado = 0;
            $empleado->save();
        } catch (\Throwable $th) {
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }
        session()->flash('success', 'Los datos se guardaron correctamente');
        return redirect()->route('reingresos.tabla');
    }

    public function masivosReingresos(Request $request)
    {
        if(!isset($request->reingreso)){
            session()->flash('success', 'No ah selecionado ningun dato');
            return redirect()->route('reingresos.tabla');
        }

        cambiarBase(Session::get('base'));

        $error = $ok = 0;

        $empleados = Empleado::whereIn('id', $request->reingreso)->get();
        if ($request->fecha_nueva_alta == null) {
            $fecha = date('Y-m-d');
        } else {
            $fecha = $request->fecha_nueva_alta;
        }
        foreach ($empleados as $empleado) {
            if ($empleado->estatus == Empleado::EMPLEADO_BAJA) {
                $empleado->fecha_ult_alta = $empleado->fecha_alta;
                $empleado->fecha_alta = $fecha;
                $empleado->estatus = Empleado::EMPLEADO_ACTIVO;
                $empleado->save();
                $ok++;
            } else {
                $error++;
            }
        }
        if ($error > 0 && $ok > 0) {
            session()->flash('success', 'Los datos se guardaron correctamente, pero hubo' . $error . 'registros con error');
        } else if ($ok > 0 && $error <= 0) {
            session()->flash('success', 'Los datos se guardaron correctamente');
        } else if ($ok <= 0 && $error > 0) {
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }
        return redirect()->route('reingresos.tabla');
    }
    public function infoEmpleado($id_empleado, Request $request)
    {

        $base = Session::get('base');
        cambiarBase($base);
        $empleado = Empleado::where('id', $id_empleado)->with('camposExtras')->first();
        $jefeInmediato = Empleado::select(DB::raw('CONCAT(nombre, " ",apaterno," ",amaterno ) AS nombre'), 'id')->where(['id' => $empleado->jefe_inmediato, 'estatus' => 1])->get();


        $usuario = EmpleadoLogin::where('email', $empleado->correo)->first();
        $sedes = Sede::where('estatus', 1)->get();
        $nombre = $id_empleado;

        $empleado->qr = null;
        if (($usuario) && ($usuario->codigo != "" && $usuario->codigo != null)) {
            $x = '/storage/public/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg';

            if ((Storage::exists('/storage/public/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg'))) {
                $empleado->qr = '/storage/public/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg';
            } else {
                $empleado->qr = null;
            }
        }

        $empleado->avatar = ($empleado->file_fotografia) ? '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $empleado->file_fotografia : '/img/avatar.png';


        $categorias = DB::connection('empresa')
            ->table('categorias')
            ->where('estatus', 1)
            ->get();

        $tipos_nomina = DB::connection('empresa')
            ->table('periodos_nomina')
            ->select('nombre_periodo')
            ->distinct('nombre_periodo')
            ->where('estatus', 1)
            ->get();

        $query = "SELECT em.id, em.id_categoria, ememi.razon_social from " . $base . ".empleados em join " . $base . ".categorias cat on em.id_categoria = cat.id inner join singh.registro_patronal regpat on cat.tipo_clase = regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora = ememi.id where em.id=" . $id_empleado . " and cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.estatus in (1,5, 30)";

        $empresa_emisora = DB::connection('empresa')->select(DB::raw($query));

        $puestos = Puesto::where('estatus', 1)->get();

        $departamentos =  DB::connection('empresa')
            ->table('departamentos')
            ->where('estatus', 1)
            ->get();

        $horarios = Horario::where('estatus', 1)->get();

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        $bancos = DB::table('bancos')
            ->orderBy('nombre', 'asc')
            ->get();

        $archivos = $this->archivos;
        $btns = ($request->btns) ?? 1;

        try {
            $archivos_extras = DB::connection('empresa')
                ->table('empleados_campos_extras') // posiblemente la tabla no exista en todas las empresas
                ->where('tipo', 'file')
                ->get();
        } catch (\PDOException  $e) {
            $archivos_extras = [];
        }


        $biometricos = Biometrico::where('estatus', 1)->get();
        $biometricos2 = clone $biometricos;
        $asignados = asignacionBiometrico::where('id_empleado', $empleado->id)->get();

        $huellas = huellaUsuario::where('id_empleado', $empleado->id)->get();

        $tipos_nomina = array("Diaria", "Semanal", "Catorcenal", "Quincenal", "Mensual");
        $repo = '/sorage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre;


        $modificacion_salario_user = DB::connection('empresa')
            ->table('modificaciones_sueldo') // posiblemente la tabla no exista en todas las empresas
            ->where('id_empleado',  $empleado->id)
            ->get();

        if (empty($modificacion_salario_user)) {
            $msu = 0;
        } else {
            $msu = 1;
            $last_modificacion = $modificacion_salario_user->last();
        }


        return view('empleados_admin.reingresos.empleado-reingresos', compact('empleado', 'jefeInmediato', 'categorias', 'puestos', 'departamentos', 'horarios', 'tipos_nomina', 'empresa_emisora', 'bancos', 'archivos', 'archivos_extras', 'btns', 'biometricos', 'biometricos2', 'asignados', 'huellas', 'repo', 'sedes', 'parametros', 'msu', 'last_modificacion'));
    }
}
