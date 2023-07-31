<?php

namespace App\Http\Controllers\Empleados;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Imports\EmpleadosImport;
use App\Imports\EmpleadosSimpleImport;
use App\Exports\EmpleadosExport;
use App\Http\Controllers\Controller;
use App\EmpleadoLogin;
use App\Models\Empleado;
use App\Models\EmpleadoDeducciones;
use App\Models\EmpleadoPercepciones;
use App\Models\Contrato;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Puesto;
use App\Models\Horario;
use App\Models\Biometrico;
use App\Models\Biometrico\asignacionBiometrico;
use App\Models\Biometrico\huellaUsuario;
use App\Models\AsignaEmpresasEmisoras;
use App\Models\Categoria;
use App\Models\Parametros;
use App\Models\Prestacion;
use App\Models\ComprobanteVacunacion;
use App\Models\ConfiguracionOrganigrama;
use App\Models\PuestoDetalle;
use App\Exports\EmpleadosEdicionMasivaExport;
use App\Imports\EmpleadosEdicionMasivaImport;

class EmpleadosController extends Controller
{
    protected $columnas = ["estatus","fecha_antiguedad","rfc","departamento"];
    protected $archivos = ['file_ine' => 'IDENTIFICACIÓN OFICIAL VIGENTE', 'file_fotografia' => 'FOTOGRAFIA', 'file_nacimiento' => 'ACTA DE NACIMIENTO', 'file_curp' => "CURP", 'file_nss' => 'NSS', 'file_rfc' => 'RFC', 'file_comprobante' => 'COMPROBANTE DE DOMICILIO', 'file_aviso' => 'AVISO DE RETENCIONES INFONAVIT', 'file_estado_cuenta' => 'ESTADO DE CUENTA', 'file_contrato' => 'CONTRATO', 'file_analisis' => 'ANÁLISIS', 'file_fonacot' => 'FONACOT', 'file_curriculum' => 'CURRICULUM', 'file_fiel_imss' => 'AFIL IMSS'];
    protected $url_vcard ="https://hrsystem.com.mx/vcard/";
    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }


    public function empleados()
    {
        $user = auth()->user();
        tienePermiso('empleados');
        
        cambiarBase(Session::get('base'));

        $departamentos = DB::connection('empresa')
            ->table('departamentos')
            ->where('estatus', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        $empleado = new Empleado;
        $empleados = $empleado->obtenerEmpleadosSegunDepartamentosAsignados(Session::get('base'));

        foreach ($empleados as $emp) {
            $porcentaje = $this->obtenerPorcentajeCompletadoArchivoEmpleado($emp);
            $emp->porcentaje = $porcentaje;
        }

        $ids = DB::connection('empresa')
            ->table('asignacion_contratos')
            ->select('id_contrato')
            ->where('estatus', 1)
            ->get();

        $idsContrato = array();
        foreach ($ids as $id) {
            $idsContrato[] = $id->id_contrato;
        }

        $contratos_asignados = Contrato::whereIn('id', $idsContrato)->get();

        $esSindical = DB::connection('empresa')->table('conceptos_nomina')
            ->where('file_rool', 0)
            ->where('estatus', 1)
            ->count();

        $columnas = $this->obtenerColumnasListado() ?? collect($this->columnas);

        $permisos = DB::connection('empresa')
            ->table('permisos')
            ->where('id_usuario', $user->id)
            ->first();
      
        if (Session::get('base') == 'empresa000111') { // JEDISAM
            $layout = asset('/storage/templates/import_empleados_nosindical.xlsx'); // TODO - PENDIENTE
        } elseif ($esSindical > 0) {
            $layout = asset('/storage/templates/import_empleados_sindical.xlsx');
        } else {
            $layout = asset('/storage/templates/import_empleados_nosindical.xlsx');
        }

        cambiarBase(Session::get('base'));
        $id_empresa = Session::get('empresa')['id'];
        $empresa_emisora = count(AsignaEmpresasEmisoras::where('id_empresa', $id_empresa)->get());
        $categorias_asignadas = count(Categoria::all());
        $parametros = count(Parametros::all());
        $prestaciones_asignadas = count(Prestacion::all());

        if ($empresa_emisora == 0 || $categorias_asignadas == 0 || $parametros == 0 || $prestaciones_asignadas == 0) {
            $errores = array(
                'Empresa emisora (no asignada)' => $empresa_emisora,
                'Categorias (no asignadas)' => $categorias_asignadas,
                'Parámetros de la empresa (no asignados)' => $parametros,
                'Prestaciones (no asignadas)' => $prestaciones_asignadas
                );
            
        } else {
            $errores = null;
        }
     
        return view('empleados.empleados', compact('empleados', 'departamentos', 'contratos_asignados', 'layout', 'columnas', 'permisos', 'errores'));
    }


    public function empleadosCatalogos()
    {
        cambiarBase(Session::get('base'));

        $categorias = DB::connection('empresa')->table('categorias')->where('estatus', 1)->get();
        $deptos = Departamento::where('estatus', 1)->get();
        $horarios = Horario::select('id', 'alias')->where('estatus', 1)->get();
        $puestos = Puesto::select('id', 'puesto')->where('estatus', 1)->get();
        $bancos = DB::table('bancos')->get();

        return view('empleados.empleados-catalogos', compact('categorias', 'deptos', 'horarios', 'puestos', 'bancos'));
    }


    public function importarEmpleados(Request $request)
    {

        if (!empty($request->simple) && $request->simple == 1) {
            $empleados_import = new EmpleadosSimpleImport;
        } else {
            $empleados_import = new EmpleadosImport;
        }
        
        Excel::import($empleados_import, $request->file('archivo_empleados'));
        $resultados = $empleados_import->getImportedResults();

        $errores = $resultados['errores'];
        $importados = $resultados['importados'];
        $mensajeDeError = $resultados['mensajeDeError'];

        if ($errores <= 0) $tipo_alerta = 'success';
        elseif ($errores > 0 && $importados > 0) $tipo_alerta = 'warning';
        elseif ($errores > 0 && $importados <= 0) $tipo_alerta = 'danger';

        $resultados = 'Se procesó correctamente el archivo.';
        if ($errores > 0) {
            $resultados .= '<br> Se encontraron los siguientes errores: <br><br>';
            $resultados .= $mensajeDeError;
        } else {
            envioAvisosXMail(Session::get('base'), 8);
        }
        $resultados .= '<br>Se importaron ' . $importados . ' registros.';


        session()->flash('success', $resultados);

        return redirect()->route('empleados.empleados');
    }

    public function exportarEmpleados()
    {
        $base = Session::get('base');
        $nombreArchivo = 'Empleado_' . date('d-m-Y_H:i') . '.xlsx';

        if ($base == 'empresa000111') { // JEDISAM
            return Excel::download(new EmpleadosJedisamExport(), $nombreArchivo); // TODO: - Pendiente
        } else {
            return Excel::download(new EmpleadosExport(), $nombreArchivo);
        }
    }


    public function cambiarColumnas(Request $request)
    {
        $empresa = Session::get('base');

        $columnas = implode(',', $request->campos);
        DB::table('empleados_configuracion_columnas')->updateOrInsert(
            ['empresa' => $empresa],
            [
                'columnas' => $columnas,
                'fecha_edicion' => date('Y-m-d H:i:s')
            ]
        );

        session()->flash('success', 'Se cambiaron correctamente las columnas');

        return redirect()->route('empleados.empleados');
    }

    public function crearEmpleado()
    {
        cambiarBase(Session::get('base'));
        $empresa = Session::get('base');

        $categorias = DB::connection('empresa')
            ->table('categorias')
            ->where('estatus', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        $puestos =  DB::connection('empresa')
            ->table('puestos')
            ->where('estatus', 1)
            ->orderBy('puesto', 'asc')
            ->get();

        $departamentos =  DB::connection('empresa')
            ->table('departamentos')
            ->where('estatus', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        $sedes = DB::connection('empresa')
            ->table('sedes')
            ->where('estatus', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        $horarios =  DB::connection('empresa')
            ->table('horarios')
            ->where('estatus', 1)
            ->get();

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        $tipos_nomina = DB::connection('empresa')
            ->table('periodos_nomina')
            ->select('nombre_periodo')
            ->distinct('nombre_periodo')
            ->where('estatus', 1)
            ->get();

        $tipos_nomina = array("Diaria", "Semanal", "Catorcenal", "Quincenal", "Mensual");

        $bancos = DB::table('bancos')
            ->orderBy('nombre', 'asc')
            ->get();

        $empresa_sede = Empresa::where('base', $empresa)->get();

        $concepto_asimilados = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('rutinas', 'ASIMILADOS')->where('estatus', 1)->first();

        return view('empleados.crear-empleado', compact('categorias', 'puestos', 'departamentos', 'horarios', 'sedes', 'empresa', 'parametros', 'tipos_nomina', 'bancos', 'empresa_sede','concepto_asimilados'));
    }


    public function agregarempleadopasoUno(Request $request)
    {
        $request->validate([
            'numero_empleado' => 'required',
            'nombre' => 'required|regex:/^[a-zA-Z]{3,23}$/',
            'apaterno' => 'required|regex:/^[A-Z]{3,23}$/',
            //'amaterno' => 'required|regex:/^[a-zA-Z]{3,23}$/',
            'rfc' => 'required |regex:/^[A-Z0-9]{12,13}$/',
            'curp' => 'required |regex:/^[A-Z0-9]{18}$/',
            'fecha_nacimiento' => 'required ',
            'fecha_alta' => 'required',
            'lugar_nacimiento' => 'required|regex:/^[A-Z]{3,23}$/',
            'id_categoria' => 'required',
            'genero' => 'required|regex:/^[A-Z]{3,23}$/',
            'nss' => 'required |regex:/^[0-9]{10}$/',
            'id_puesto' => 'required',
            'tipo_jornada' => 'required',
            'tipo_contrato' => 'required',
            'id_departamento'=>'required',
        ]);

        cambiarBase(Session::get('base'));

        $empleado = Empleado::create($request->except('_token'));

        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empleados ' . $empleado->id . ' INSERT 1');

        session()->flash('success', 'Los datos se guardaron correctamente. Continue con los siguientes datos.');

        return redirect()->route('empleados.creardos', $empleado->id);
    }

    public function crearempleadopasoDos($id_empleado)
    {
        cambiarBase(Session::get('base'));

        $empleado = Empleado::find($id_empleado);

        $tipos_nomina = DB::connection('empresa')
            ->table('periodos_nomina')
            ->select('nombre_periodo')
            ->distinct('nombre_periodo')
            ->where('estatus', 1)
            ->get();

        $tipos_nomina = array("Diaria", "Semanal", "Catorcenal", "Quincenal", "Mensual");

        $bancos = DB::table('bancos')
            ->orderBy('nombre', 'asc')
            ->get();

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        return view('empleados.crear-empleado-paso-dos', compact('tipos_nomina', 'id_empleado', 'bancos', 'parametros'));
    }

    public function agregarempleadopasoDos(Request $request)
    {

        $validated = $request->validate([
            'salario_diario' => 'required |regex:/^[0-9]{10}$/',
            'sueldo_neto' => 'required |regex:/^[0-9]{10}$/',
            'salario_digital' => 'required',
            'fecha_antiguedad' => 'required',
            'id_banco' => 'required'
        ]);

        cambiarBase(Session::get('base'));

        Empleado::where('id', $request->id)->update($request->except('_token'));

        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empleados ' . $request->id . ' INSERT 2');

        session()->flash('success', 'Los datos se guardaron correctamente. Continue con los siguientes datos.');

        return redirect()->route('empleados.creartres', $request->id);
    }

    public function crearempleadopasoTres($id_empleado)
    {

        cambiarBase(Session::get('base'));
        $empresa = Session::get('base');

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        return view('empleados.crear-empleado-paso-tres', compact('id_empleado', 'parametros'));
    }

    public function agregarempleadopasoTres(Request $request)
    {

        $base = Session::get('base');
        
        $empresas = Empresa::where('base', $base)->first();

        if($empresas->permiso_extranjero == 0){
            if($request->nacionalidad == "MEXICANA" || $request->nacionalidad == "MEXICANO" || $request->nacionalidad == "MÉXICO"  || $request->nacionalidad == "MEXICO"){

            }else{
                session()->flash('danger', 'La empresa no cuenta con permisos para empleador de extranjeros');
                return redirect()->route('empleados.creartres', $request->id);               
            }
        }

        $validated = $request->validate([
            'nacionalidad' => 'required|regex:/^[a-zA-Z]{3,23}$/',
            'calle_numero' => 'required |regex:/^[a-zA-Z0-9]{6,13}$/',
            'colonia' => 'required |regex:/^[a-zA-Z0-9]{6,13}$/',
            'delegacion' => 'required |regex:/^[a-zA-Z0-9]{6,13}$/',
            'estado' => 'required |regex:/^[a-zA-Z]{6,13}$/',
            'cp' => 'required |regex:/^[0-9]{4,8}$/',
            'correo' => 'required|^[a-zA-Z0-9.!#$%&*+/=?^_{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$',
            'telefono_casa' => 'required |regex:/^[0-9]{7,14}$/',
            'telefono_movil' => 'required |regex:/^[0-9]{7,14}$/',
            'estado_civil' => 'required',
            'escolaridad' => 'required',
            'profesion' => 'required',
            'avisar_a' => 'required',
            'avisar_a_telefono' => 'required |regex:/^[0-9]{7,14}$/',
            'beneficiario' => 'required',
            'avisar_a_parentesco' => 'required',
        ]);

        list($datosRepetidos, $mensaje) = $this->hayDatosRepetidos($request);

        if ($datosRepetidos) {
            session()->flash('danger', $mensaje);
            return redirect()->route('empleados.creartres', $request->id);
        }

        cambiarBase(Session::get('base'));
        
        Empleado::where('id', $request->id)->update($validated);
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empleados ' . $request->id . ' INSERT 3');

        $empleadoLogin = new EmpleadoLogin;
        $empleadoLogin->crearCuentaEmpleado($request->correo, Session::get('base'), 0);

        // TODO - InsertaDatosEmpleadosPerso.php:237 - Falta Jedisam

        session()->flash('success', 'Los datos se guardaron correctamente. Continue con los siguientes datos.');

        return redirect()->route('empleados.crearcuatro', $request->id);
    }

    public function crearempleadopasoCuatro($id_empleado)
    {
        cambiarBase(Session::get('base'));
        $empresa = Session::get('base');

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        return view('empleados.crear-empleado-paso-cuatro', compact('id_empleado', 'parametros'));
    }

    public function agregarempleadopasoCuatro(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update($request->except('_token'));
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empleados ' . $request->id . ' INSERT 4');

        $empleado = Empleado::find($request->id);
        $fecha_antiguedad = Carbon::parse($empleado->fecha_antiguedad);
        $hoy = Carbon::now();
        $antiguedad = $fecha_antiguedad->diffInYears($hoy);

        $prestaciones = DB::connection('empresa')->table('prestaciones')
            ->select('*')->join('categorias', 'prestaciones.id_categoria', '=', 'categorias.id')
            ->where('categorias.id', $empleado->id_categoria)
            ->where('prestaciones.antiguedad', $antiguedad)
            ->where('categorias.estatus', 1)
            ->where('prestaciones.estatus', 1)
            ->first();
        //TODO Quitar esto para sacar el id de la prestacion
        $id_prestaciones = DB::connection('empresa')->table('prestaciones')
            ->select('prestaciones.id')->join('categorias', 'prestaciones.id_categoria', '=', 'categorias.id')
            ->where('categorias.id', $empleado->id_categoria)
            ->where('prestaciones.antiguedad', $antiguedad)
            ->where('categorias.estatus', 1)
            ->where('prestaciones.estatus', 1)
            ->first();

        $empleado->estatus = Empleado::EMPLEADO_ACTIVO;
        $empleado->id_prestacion = $id_prestaciones->id;
        $empleado->dias_vacaciones = $prestaciones->vacaciones;
        $empleado->dias_aguinaldo = $prestaciones->aguinaldo;
        $empleado->porcentaje_prima = $prestaciones->prima_vacacional;
        $empleado->salario_diario_integrado = $prestaciones->factor_integracion * $empleado->salario_diario;
        $empleado->repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado->id;
        $empleado->save();

        DB::connection('empresa')->table('log_incidencias')->insert([
            'id_empleado' => $empleado->id,
            'fecha' => date('Y-m-d'),
            'tipo' => 'ALTA',
            'ejecutivo' => Auth::user()->email,
            'descripcion' => 'ALTA',
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]);


        session()->flash('success', 'Los datos se guardaron correctamente. Continue con los siguientes datos.');

        return redirect()->route('empleados.crearcinco', $request->id);
    }

    public function crearempleadopasoCinco($id_empleado)
    {
        $archivos = $this->archivos;

        cambiarBase(Session::get('base'));
        $empresa = Session::get('base');

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->get();

        return view('empleados.crear-empleado-paso-cinco', compact('id_empleado', 'archivos', 'parametros'));
    }

    public function agregarempleadopasoCinco(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado_id = $request->id;
        $mensj="";
        if ($request->allFiles() && $empleado_id > 0) {
            foreach ($request->allFiles() as $id_archivo => $archivo) {
                $nombreArchivo = $id_archivo . '.' . $archivo->getClientOriginalExtension();

                $path = Storage::disk('public')->put('repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id, $archivo);

                $emp = strlen(Session::get('empresa')['id']);
                $num_id = strlen($empleado_id);
                $total = 14 + $emp + $num_id;

                $archivo_key = $rest = substr($path, $total);

                $subido_key = Storage::disk('public')->move('repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . '/' . $archivo_key, 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . '/' . $nombreArchivo);


                $folder = 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . "/";

                if ($archivo->move(public_path($folder), $nombreArchivo)) {
                    Empleado::where('id', $empleado_id)
                        ->update([$id_archivo => $nombreArchivo]);
                }
            }

            $mensj = 'Los archivos se guardaron correctamente y se completó la creación del empleado';
        }

        $empleado = Empleado::find($empleado_id);
        $nombre_completo = $empleado->apaterno . ' ' . $empleado->amaterno . ' ' . $empleado->nombre;

        logEmpresa(Session::get('base'), Auth::user()->email, 'Se genero el expediente del empleado ' . $empleado_id);


       // dd("Agregar bitacora");

        agregarABitacora(Session::get('base'), 1, 'VE', Auth::user()->id, 'Validacion de expediente del empleado ' . $nombre_completo . '.');
        agregarABitacora(Session::get('base'), 20, 'VB', Auth::user()->id, 'Se ha dado de alta el empleado ' . $nombre_completo . ', por favor ingresa los datos bancarios, ' . Session::get('base'));
        agregarABitacora(Session::get('base'), 21, 'VI', Auth::user()->id, 'Se ha dado de alta el empleado ' . $nombre_completo . ', por favor genera los TXT para dar de alta el IMSS, ' . Session::get('base'));

        envioAvisosXMail(Session::get('base'), 1, $empleado_id);
        envioAvisosXMail(Session::get('base'), 20, $empleado_id);
        envioAvisosXMail(Session::get('base'), 21, $empleado_id);

        session()->flash('success', $mensj);

        return redirect()->route('empleados.editar', $request->id);
    }


    protected function obtenerColumnasListado()
    {
        $row = DB::table('empleados_configuracion_columnas')->where('empresa', Session::get('base'))->first();
        if ($row) {
            return collect(explode(',', $row->columnas));
        } else {
            return null;
        }
    }

    protected function obtenerPorcentajeCompletadoArchivoEmpleado($empleado)
    {
        $conteo = 0;

        $parametros = DB::connection('empresa')->table('parametros')->first();

        if ($parametros->tipo_nomina == 'soloSindical') {
            $neto = 3;
        } else {
            $neto = 8;
            if ($empleado->file_nss) $conteo++;
            if ($empleado->file_comprobante) $conteo++;
            if ($empleado->file_contrato) $conteo++;
            if ($empleado->file_curp) $conteo++;
            if ($empleado->file_rfc) $conteo++;
        }
        if ($empleado->file_fotografia) $conteo++;
        if ($empleado->file_ine) $conteo++;
        if ($empleado->file_nacimiento) $conteo++;

        $porcentaje = ($conteo * 100) / $neto;
        return floor($porcentaje);
    }


    public function infoEmpleado($id_empleado)
    {

        $base = Session::get('base');
        cambiarBase($base);
        $empleado = Empleado::where('id', $id_empleado)->with('camposExtras')->first();

        $usuario = EmpleadoLogin::where('email', $empleado->correo)->first();
        $sedes = Sede::where('estatus', 1)->get();
        $nombre = $id_empleado;

        $empleado->qr = $this->url_vcard.$usuario->codigo;

        if($usuario->codigo =="" || $usuario->codigo == null){
            $empleado_codigo = new Empleado();
            $usuario->codigo=$empleado_codigo->generarPassword(10);
            $usuario->save();
            $empleado->qr = $this->url_vcard.$usuario->codigo;
        }

        $empleado->avatar = ($empleado->file_fotografia) ? '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $empleado->file_fotografia : 'public/img/avatar.png';

        $categorias = DB::connection('empresa')
            ->table('categorias')
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

        try {
            $archivos_extras = DB::connection('empresa')
                ->table('empleados_campos_extras') // posiblemente la tabla no exista en todas las empresas
                ->where('tipo', 'file')
                ->get();
        } catch (\PDOException  $e) {
            $archivos_extras = [];
        }


        $asignados = asignacionBiometrico::where('id_empleado', $empleado->id)->get();


        $tipos_nomina = array("Diaria", "Semanal", "Catorcenal", "Quincenal", "Mensual");
        $repo = '/sorage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre;


        $modificacion_salario_user = DB::connection('empresa')
                ->table('modificaciones_sueldo') // posiblemente la tabla no exista en todas las empresas
                ->where('id_empleado',  $empleado->id)
                ->get();  

        if(empty($modificacion_salario_user)){
            $msu = 0;
        }else{
            $msu = 1;
            $last_modificacion = $modificacion_salario_user->last();
        }


        return view('empleados.info-empleado', compact('empleado',  'categorias', 'puestos', 'departamentos', 'horarios', 'tipos_nomina', 'empresa_emisora', 'bancos', 'archivos', 'archivos_extras', 'asignados', 'repo', 'sedes', 'parametros','msu', 'last_modificacion'));

    }

    public function editarEmpleado($id_empleado)
    {
        $base = Session::get('base');
        cambiarBase($base);
        $empleado = Empleado::where('id', $id_empleado)->with('camposExtras')->first();

        $jefeInmediato = Empleado::select(DB::raw('CONCAT(nombre, " ",apaterno," ",amaterno ) AS nombre'),'id')->where(['id'=>$empleado->jefe_inmediato,'estatus'=>1 ])->get();

        if(sizeof($jefeInmediato) == 0 )
            $jefeInmediato=collect(array(array('nombre'=>"Sin dependencia",'id'=>null)));
    
        $usuario = EmpleadoLogin::where('email', $empleado->correo)->first();
        
        $sedes = Sede::where('estatus', 1)->get();
        $nombre = $id_empleado;

        $empleado->qr = null;
        if (($usuario) && ($usuario->codigo != "" && $usuario->codigo != null)) {
            $x = '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg';

            $url_vcard = "repositorio/".Session::get('empresa')['id']."/".$empleado->id."/".$usuario->codigo.".svg ";
            
            
            if (file_exists( public_path().'/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg' )) {
                $empleado->qr = '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre . '/' . $usuario->codigo . '.svg';
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
          

        $id_alias = Schema::connection('empresa')->hasColumn('empleados','id_alias');
        $select_alias ="";
   
        if($id_alias){
            $obtenerAlias=Empleado::join('puestos_alias','puestos_alias.id','=','empleados.id_alias')->select('puestos_alias.alias','puestos_alias.id')->where('empleados.id',$empleado->id)->first();
            if(!empty($obtenerAlias)){
                $select_alias = array('id'=>$obtenerAlias->id,'alias'=>$obtenerAlias->alias);
            }
        }

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
        $repo = '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $nombre;

        $empresa_sede = Empresa::where('base', $base)->get();

        $modificacion_salario_user = DB::connection('empresa')
                ->table('modificaciones_sueldo') // posiblemente la tabla no exista en todas las empresas
                ->where('id_empleado',  $empleado->id)
                ->get();  

        if(empty($modificacion_salario_user)){
            $msu = 0;
        }else{
            $msu = 1;
            $last_modificacion = $modificacion_salario_user->last();
        }

        $id_empresa = Session::get('empresa')['id'];
        $verificar_config = ConfiguracionOrganigrama::where('id_empresa',$id_empresa)->first();
        $lleva_puestos_reales ="";

        if(!empty($verificar_config)){

            $lleva_puestos_reales = $verificar_config->lleva_puestos_reales;
        }

        $concepto_asimilados = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('rutinas', 'ASIMILADOS')->where('estatus', 1)->first();

        return view('empleados.editar-empleado', compact('empleado', 'categorias', 'puestos', 'departamentos', 'horarios', 'tipos_nomina', 'empresa_emisora', 'bancos', 'archivos', 'archivos_extras', 'btns', 'biometricos', 'biometricos2', 'asignados', 'huellas', 'repo', 'sedes', 'parametros', 'empresa_sede', 'nombre', 'msu', 'last_modificacion','jefeInmediato','lleva_puestos_reales','select_alias','concepto_asimilados'));

    }

    public function actualziarEmpleado(Request $request)
    {
        list($datosRepetidos, $mensaje) = $this->hayDatosRepetidos($request);
        if ($datosRepetidos) {

            session()->flash('danger', $mensaje);
            return redirect()->route('empleados.editar', $request->id);
        }

        cambiarBase(Session::get('base'));

        if($request->infonavit_val==0){
            $request['num_credito_infonavit']=0;
            $request['tipo_descuento']=0;
            $request['valor_descuento']=0;
        }

        if($request->fonacot==0){
            $request['num_credito_fonacot']=0;
            $request['valor_fonacot']=0;
        }


        $emp_old = Empleado::find($request->id);

        if ($request->fecha_antiguedad < $emp_old->fecha_antiguedad) {

            $fecha = explode("-", $emp_old->fecha_antiguedad);
            $fecha1 = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
            $fecha = explode("-", $request->fecha_antiguedad);
            $fecha2 = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
        }

        if ($request->salario_diario != $emp_old->salario_diario) {
            DB::connection('empresa')->table('modificaciones_salario')->insert([
                'id_empleado' => $request->id,
                'salario_anterior' => $emp_old->salario_diario,
                'salario_nuevo' => $request->salario_diario,
                'salario_integrado_anterior' => $emp_old->salario_diario_integrado,
                'salario_integrado_nuevo' => $request->salario_diario_integrado,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
        }

        if ($request->salario_digital != $emp_old->salario_digital) {
            DB::connection('empresa')->table('modificaciones_sueldo')->insert([
                'id_empleado' => $request->id,
                'sueldo_anterior' => $emp_old->sueldo_neto,
                'sueldo_nuevo' => $request->sueldo_neto,
                'sueldo_real_anterior' => $emp_old->salario_digital,
                'sueldo_real_nuevo' => $request->salario_digital,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
        }
        
        Empleado::where('id', $request->id)->update($request->except(['_token','infonavit_val','fonacot']));

        if ($this->noTieneLoginEmpleado($request->id, $request->correo)) {
            // crear login para usuario
            $empleadoObj = new Empleado;
            $password = $empleadoObj->generarPassword();
            EmpleadoLogin::create([
                'email' => $request->correo,
                'password' => bcrypt($password),
                'empresa' => Session::get('base'),
                'estatus' => 1,
                'tmp' => $password
            ]);
        }

        session()->flash('success', 'El empleado se edito correctamente');
        return redirect()->route('empleados.editar', $request->id);
    }

    public function actualziarempleadoFile(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado_id = $request->id_empleado;

        if ($request->allFiles() && $empleado_id > 0) {
            foreach ($request->allFiles() as $id_archivo => $archivo) {
                $nombreArchivo = $id_archivo . '.' . $archivo->getClientOriginalExtension();

                $path = Storage::disk('public')->put('repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id, $archivo);
                $emp = strlen(Session::get('empresa')['id']);
                $num_id = strlen($empleado_id);
                $total = 14 + $emp + $num_id;

                $archivo_key = $rest = substr($path, $total);
                Storage::disk('public')->delete('repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . '/' . $nombreArchivo);
                Storage::disk('public')->move('repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . '/' . $archivo_key, 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . '/' . $nombreArchivo);

                $folder = 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . "/";

                if (array_key_exists($id_archivo, $this->archivos)) {
                    // Archivos genericos en la tabla de empleados
                    Empleado::where('id', $empleado_id)
                        ->update([$id_archivo => $nombreArchivo]);
                } else {
                    // Archivos extras
                    DB::connection('empresa')->table('empleados_informacion_extra')->updateOrInsert(
                        [
                            'nombre_campo' => $id_archivo,
                            'id_empleado' => $empleado_id
                        ],
                        [
                            'info' => $nombreArchivo
                        ]
                    );
                }
                if(!empty($request->id_alias)){
                    Empleado::where('id', $empleado_id)->update(['id_alias'=>$request->id_alias]);
                }



                logGeneral(Auth::user()->email, 'El Usuario ha modificado el expediente del empleado conm id:' . $empleado_id." en empresa".Session::get('base'), 'V3');

                session()->flash('success', 'El empleado se edito correctamente');
                return redirect()->route('empleados.editar',$empleado_id);
            }

            session()->flash('success', 'El empleado se edito correctamente');
            return redirect()->route('empleados.editar',$empleado_id);
        }
        session()->flash('success', 'Sin datos enviados');
        return redirect()->route('empleados.editar',$empleado_id);
    }

    public function eliminarEmpleado(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($request->id);
        $empleado->estatus = Empleado::EMPLEADO_ELIMINADO;
        $empleado->save();


        session()->flash('success', 'El empleado se eliminó, podrá recuperarlo en el apartado de "Eliminados".');
        return redirect()->route('empleados.empleados');
    }


    protected function hayDatosRepetidos($request)
    {
        cambiarBase(Session::get('base'));

        // Verificar num empleado : 387
        if ($request->numero_empleado) {
            $emp = Empleado::where('numero_empleado', $request->numero_empleado)->where('estatus', 1)->where('id', '!=', $request->id)->first();
            if ($emp) {
                return [true, 'El numero de empleado ya se encuentra registrado en otro usuario'];
            }
        }

       


        // Verificar correo: 411
        if ($request->correo) {
            $emp = Empleado::where('correo', $request->correo)->where('estatus', 1)->where('id', '!=', $request->id)->first();
            if ($emp) {
                return [true, 'El email ya se encuentra registrado en otro usuario'];
            }
        }

       

        
        return [false, ''];
    }

    protected function noTieneLoginEmpleado($empleado_id, $empleado_correo)
    {
        cambiarBase(Session::get('base'));
        $logueado = EmpleadoLogin::where('email', $empleado_correo)->count();
        $timbrado = DB::connection('empresa')->table('timbrado')->where('id_empleado', $empleado_id)->where('sello_sat', '!=', 'error')->count();

        return ($logueado <= 0 && $timbrado > 0) ? true : false;
    }


    public function baja(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($request->id);
        $periodos = DB::connection('empresa')->table('periodos_nomina')
                        ->select()
                        ->where('fecha_inicial_periodo', '<=', $request->fecha_baja)
                        ->where('fecha_final_periodo', '>=', $request->fecha_baja)
                        ->where('estatus', 1)
                        ->where('nombre_periodo', 'LIKE', '%'.$empleado->tipo_de_nomina.'%');

        if($periodos->count() > 0){
            // Con finiquito
            if($request->finiquito == 1){

                Empleado::where('id', $request->id)->update(
                    [
                    'fecha_baja' => $request->fecha_baja,
                    'causa_baja' => ($request->causa_baja == 'OTRA') ? $request->causa_baja2 : $request->causa_baja,
                    'baja_oficial' => strtoupper($request->causa_baja_oficial),
                ]);

                $request->id_empleado_calculo = $request->id;
             /*   return redirect()->route('empleados_bck.inicio')
                        ->with('tipo_alerta', 'warning')
                        ->with('mensaje', '¡AVISO! <br> El empleado sera dado de Baja hasta que se CIERRE EL FINIQUITO');*/
                        // return redirect()->route('procesos.finiquito');
                        dd("Redirect Finiquito");
            }else{ // Sin finiquito

                Empleado::where('id', $request->id)->update([
                    'fecha_baja' => $request->fecha_baja,
                    'causa_baja' => ($request->causa_baja == 'OTRA') ? $request->causa_baja2 : $request->causa_baja,
                    'baja_oficial' => strtoupper($request->causa_baja_oficial),
                    'finiquitado' => 0,
                    'estatus' => Empleado::EMPLEADO_BAJA
                ]);

                DB::connection('empresa')->table('log_incidencias')->insert([
                    'id_empleado' => $empleado->id,
                    'fecha' => $request->fecha_baja,
                    'tipo' => 'BAJA',
                    'ejecutivo' => Auth::user()->email,
                    'descripcion' => 'BAJASINFINIQUITO',
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]);

                if($request->encuesta == 'correo'){

                    $nombreComp = $empleado->apaterno . ' ' . $empleado->amaterno . ' ' . $empleado->nombre;
                    $desc='Se ha dado de baja el empleado '.$nombreComp;
                    $tipo='BE';
                    agregarABitacora(Session::get('base'), 44, 'BE', $empleado->id, $desc);
                    envioAvisosXMail(Session::get('base'), 44, $nombreComp);
                   
                    envioAvisosXMail(Session::get('base'), 24, $empleado->id, $empleado->email, '*******');

                    session()->flash('success', 'El empleado ha sido dado de baja SIN FINIQUITO, Se ha enviado el Correo al empleado');
                    return redirect()->route('empleados.empleados');


                }
                elseif($request->encuesta == 'generar'){

                    session()->flash('success', '¡AVISO!. El empleado ha sido dado de baja SIN FINIQUITO');
                    return redirect()->route('empleados.encuesta',$request->id);
                    // empleados_bck.encuesta

                } else {

                    session()->flash('success', '¡AVISO!. El empleado ha sido dado de baja SIN FINIQUITO');
                    return redirect()->route('empleados.empleados');

                }
                
            }

        }else{
            session()->flash('success', 'No hay periodos disponibles para la Fecha de Baja intenta de nuevo con otra fecha');
            return redirect()->route('empleados.empleados');

        }
    }

    public function encuesta($empleado)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($empleado);
        
        return view('emplados.encuesta-salida', compact('empleado'));
    }

    public function guardarEncuesta(Request $request)
    {
        cambiarBase(Session::get('base'));

        DB::connection('empresa')->table('encuesta')
                ->insert($request->except('_token'));

        return view('empleados.encuesta-salida-dos');
    }

    public function cargarAcuse(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado_id = $request->id;


        if($request->allFiles() && $empleado_id > 0){
            foreach($request->allFiles() as $id_archivo => $archivo) {
                $nombreArchivo = $id_archivo.'.'.$archivo->getClientOriginalExtension();
                $folder = '/repositorio/'. Session::get('empresa')['id'] . '/' . $empleado_id;
                
                $path = Storage::disk('public')->put($folder, $archivo);
                $archivo_cer = strlen($folder);

                $archivo_n = substr($path, $archivo_cer);
                
                $subido_cer = Storage::disk('public')->move($folder.'/'.$archivo_n, $folder.'/'.$nombreArchivo);


                Empleado::where('id', $empleado_id)
                    ->update([$id_archivo => $folder."/".$nombreArchivo]);

            }


            session()->flash('success', 'Se cargó el acuse correctamente.');
            return redirect()->route('empleados.empleados');

        }

        return redirect()->route('empleados.empleados');
    }


    public function verAcuse($empleado)
    {

        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($empleado);
        $tituloPag = 'Acuse de: ' . $empleado->nombre . ' ' . $empleado->apaterno . ' ' . $empleado->amaterno;
        $archivo = "/storage".$empleado->file_acuse;
       
        $regresarUrl = 'empleados.empleados';

        return view('empleados.ver-acuse', compact('tituloPag', 'archivo', 'regresarUrl'));
    }

    public function edicionmasivaLayout(Request $request)
    {
        cambiarBase(Session::get('base'));
        if($request->tipo == 'sNeto'){
            return Excel::download(new EmpleadosEdicionMasivaExport($request->tipo),'EmpleadoSueldoNeto_'.date('d-m-Y_H:i').'.xlsx');
        } else if($request->tipo == 'sDiario'){
            return Excel::download(new EmpleadosEdicionMasivaExport($request->tipo),'EmpleadoSalarioDiario_'.date('d-m-Y_H:i').'.xlsx');
        }
    }

    public function edicionMasiva(Request $request)
    {
        Excel::import( new EmpleadosEdicionMasivaImport($request->tipo), $request->file('archivo'));

        session()->flash('success', 'Se importó correctamente el archivo.');
        return redirect()->route('empleados.empleados');

    }

    public function percepcionesDeducciones($empleado)
    {
        cambiarBase(Session::get('base'));
        $id_empleado = $empleado;
        
        /* RENE TODO cmabiar de tabla *      
        $deducciones = DB::connection('generica')->table('incidencias_prg')
                                                 //->where('estatus', '<>', '2')
                                                 ->where('estatus', '1')
                                                 ->where('percep_deduc',1)
                                                 ->where('id_empleado', $id_empleado)

                                                 ->get();
        $percepciones = DB::connection('generica')->table('incidencias_prg')
                                                  ->where('estatus', '<>', '2')
                                                  ->where('percep_deduc',2)
                                                  ->where('id_empleado', $id_empleado)
                                                  ->get();                                                
        */
        /* TODO Version final*/
        $deducciones = EmpleadoDeducciones::where('id_empleado', $id_empleado)->where('estatus', '!=', EmpleadoDeducciones::ELIMINADO)->get();
        $percepciones = EmpleadoPercepciones::where('id_empleado', $id_empleado)->where('estatus', '!=', EmpleadoPercepciones::ELIMINADO)->get();
        
        $conceptosDeducciones = DB::connection('empresa')->table('conceptos_nomina')
                                    ->select('id', 'nombre_concepto')
                                    ->where('estatus',1)
                                    ->where('tipo', 1)
                                    ->where('tipo_proceso', 2)
                                    ->get();
        $conceptosDeducciones = $conceptosDeducciones->keyBy('id');

        $conceptosPercepciones = DB::connection('empresa')->table('conceptos_nomina')
                                    ->select('id', 'nombre_concepto')
                                    ->where('estatus',1)
                                    ->where('tipo', 0)
                                    ->where('tipo_proceso', 2)
                                    ->get();


        $conceptosPercepciones = $conceptosPercepciones->keyBy('id');

       // dd($conceptosDeducciones,$deducciones,$conceptosPercepciones);
        return view('empleados.perc_deduc', compact('id_empleado', 'deducciones', 'conceptosDeducciones', 'percepciones', 'conceptosPercepciones'));
    }

    public function guardarDeduccion(Request $request)
    {
        cambiarBase(Session::get('base'));
     
        EmpleadoDeducciones::create($request->except('_token'));

        session()->flash('success', 'Se creó correctamente la deducción del empleado.');
        return redirect()->route('empleados.percepcionesDeducciones', $request->id_empleado);

    }

    public function estatusdeduccionPercepcion(Request $request)
    {
        cambiarBase(Session::get('base'));
        if($request->tipo == 'd'){
            EmpleadoDeducciones::where('id', $request->id)->update(['estatus'=>$request->estatus]);
        } else {
            EmpleadoPercepciones::where('id', $request->id)->update(['estatus'=>$request->estatus]);
        }

        if ($request->ajax()) {
            return response()->json(['ok' => 1]);

        } else{

            session()->flash('success', 'Se editó el estatus correctamente');
            return redirect()->route('empleados.percepcionesDeducciones', $request->id_empleado);

        }
    }

    public function guardarPercepcion(Request $request)
    {
        cambiarBase(Session::get('base'));
        EmpleadoPercepciones::create($request->except('_token'));


        session()->flash('success', 'Se creó correctamente la percepción del empleado.');
        return redirect()->route('empleados.percepcionesDeducciones', $request->id_empleado);

    }

     /*Obtener jefe inmediato */
    public function obtenerJefeInmediato(Request $request)
    {

        cambiarBase(Session::get('base'));

        $id = $request->id;
        $puesto=Puesto::select('id','jerarquia','dependencia')->where('dependencia',$id)->first();

        if(!empty($puesto)){

            $empleados=Empleado::select(DB::raw('CONCAT(nombre, " ",apaterno," ",amaterno ) AS nombre'),'id')->where(['id_puesto'=>$puesto->dependencia,'estatus'=>1 ])->get();
            (sizeof($empleados) > 0) ? $res = array('data'=>$empleados,'respuesta'=>1) : $res = array('data'=>"El puesto no esta asignado a algún departamento",'respuesta'=>2);

        }else{

            $res = array('data'=>"Sin depedencia asignada",'respuesta'=>0);

        }

        return response()->json($res);
    }

    public function verContrato($empleado)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($empleado);
        $tituloPag = 'Contrato de: ' . $empleado->nombre . ' ' . $empleado->apaterno . ' ' . $empleado->amaterno;

        $archivo = '/storage/repositorio/' . Session::get('empresa')['id'] . '/' . $empleado->id.'/'.$empleado->file_contrato;
        // dd($archivo);
        $regresarUrl = 'empleados.empleados';
        
        return view('empleados.ver-contrato', compact('tituloPag', 'archivo', 'regresarUrl'));


    }

    public function comprobanteVacunacion(Request $request)
    {   
        try{
            
            cambiarBase(Session::get('base'));
            $id_empleado = $request->id_empleado;
            $file="";
            
            if(!empty($request->file)){
                        
                $path="public/repositorio/".Session::get('empresa')['id'].'/'.$id_empleado;
                $file=$request->file;  
                $extension = $file->getClientOriginalName();
                $nombre = 'comprobante_vacunacion_'.time().'_'.$extension;
                $file->storeAs ($path,$nombre);
                $file=$nombre;
            }

            $data = array('tipo_vacuna'=>$request->tipo_vacunacion,
                        'reacciones'=> $request->reacciones,
                        'comprobante' =>$file,
                        'id_empleado' =>$id_empleado);
                        
            ComprobanteVacunacion::create($data);
         
            session()->flash('success', 'Los datos se guardaron correctamente.');
        
        }catch(\Exception $e){
        
            session()->flash('danger', 'Error al guardar los datos comunicate con tu administrador.');
        }
        return redirect()->route('covid.inicio',$id_empleado);
    }

    public function bajaempleado(Request $request)
    {

        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($request->id);
        $periodos = DB::connection('empresa')->table('periodos_nomina')
                        ->select()
                        ->where('fecha_inicial_periodo', '<=', $request->fecha_baja)
                        ->where('fecha_final_periodo', '>=', $request->fecha_baja)
                        ->where('estatus', 1)
                        ->where('nombre_periodo', 'LIKE', '%'.$empleado->tipo_de_nomina.'%');

        if($periodos->count() > 0){
            // Con finiquito
            if($request->finiquito == 1){

                Empleado::where('id', $request->id)->update(
                    [
                    'fecha_baja' => $request->fecha_baja,
                    'causa_baja' => ($request->causa_baja == 'OTRA') ? $request->causa_baja2 : $request->causa_baja,
                    'baja_oficial' => strtoupper($request->causa_baja_oficial),
                ]);

                $request->id_empleado_calculo = $request->id;
             /*   return redirect()->route('empleados_bck.inicio')
                        ->with('tipo_alerta', 'warning')
                        ->with('mensaje', '¡AVISO! <br> El empleado sera dado de Baja hasta que se CIERRE EL FINIQUITO');*/
                        return redirect()->route('procesos.finiquito');

            }else{ // Sin finiquito

                Empleado::where('id', $request->id)->update([
                    'fecha_baja' => $request->fecha_baja,
                    'causa_baja' => ($request->causa_baja == 'OTRA') ? $request->causa_baja2 : $request->causa_baja,
                    'baja_oficial' => strtoupper($request->causa_baja_oficial),
                    'finiquitado' => 0,
                    'estatus' => Empleado::EMPLEADO_BAJA
                ]);

                DB::connection('empresa')->table('log_incidencias')->insert([
                    'id_empleado' => $empleado->id,
                    'fecha' => $request->fecha_baja,
                    'tipo' => 'BAJA',
                    'ejecutivo' => Auth::user()->email,
                    'descripcion' => 'BAJASINFINIQUITO',
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]);

                if($request->encuesta == 'correo'){

                    $nombreComp = $empleado->apaterno . ' ' . $empleado->amaterno . ' ' . $empleado->nombre;
                    $desc='Se ha dado de baja el empleado '.$nombreComp;
                    $tipo='BE';
                    agregarABitacora(Session::get('base'), 44, 'BE', $empleado->id, $desc);
                    envioAvisosXMail(Session::get('base'), 44, $nombreComp);
                   
                    envioAvisosXMail(Session::get('base'), 24, $empleado->id, $empleado->email, '*******');

                    session()->flash('success', 'El empleado ha sido dado de baja SIN FINIQUITO <br> Se ha enviado el Correo al empleado');
                    return redirect()->route('empleados.empleados');
                }
                elseif($request->encuesta == 'generar'){

                    session()->flash('success', '¡AVISO! El empleado ha sido dado de baja SIN FINIQUITO');
                    return redirect()->route('empleados.encuesta');


                } else {

                    session()->flash('success', '¡AVISO! <br> El empleado ha sido dado de baja SIN FINIQUITO');
                    return redirect()->route('empleados.empleados');

                }
                
            }

        }else{

            session()->flash('danger', 'No hay periodos disponibles para la Fecha de Baja intenta de nuevo con otra fecha');
            return redirect()->route('empleados.empleados');

        }
    }
    public function obtenerEmpleadosAlias(Request $request){

        cambiarBase(Session::get('base'));
        $puestosConAlias = PuestoDetalle::join('puestos AS p','puestos_detalle.id_puesto','=','p.id')
        ->join('puestos_alias AS pa','puestos_detalle.id_alias','=','pa.id')
        ->select('pa.id AS id_alias','pa.alias')
        ->where(['puestos_detalle.id_puesto'=>$request->id,'p.estatus'=>1])
        ->get();

        return response()->json($puestosConAlias);
    }

}
