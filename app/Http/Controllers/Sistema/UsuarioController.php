<?php


namespace App\Http\Controllers\sistema;

use App\Http\Controllers\cfdiUtils\CFDIUtilsHrsystem;
use App\Models\Autofacturador\EmpresasEmisoras;
use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\PermisoSingh;
use App\Models\TimbradoCredenciales;
use App\Models\Usuario;
use App\Models\Autofacturador\RelUsuarioAutofacturacion;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\UsuariosTimbrado;
use App\Models\UsuariosEmpresas;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class UsuarioController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    protected const USUARIO_ACTIVO = 1;
    protected const USUARIO_INACTIVO = 0;
    protected const USUARIO_ELIMINADO = 5;
    protected const USUARIO_TEMPORAL = 30;
    protected const USUARIO_DESHABILITADO = 10;
    protected const USUARIO_BAJA = 2;
    protected const USUARIO_BAJA_DEFINITIVO = 20;

    private $app;

    protected $permisos_nombres = [

        'parametria' => 'Parametria Inicial',
        'tabla_isr' => 'Tabla ISR',
        'tabla_subsidios' => 'Tabla subsidios',
        'puestos_empresa' => 'Puestos de la Empresa',
        'departamentos_empresa' => 'Departamentos de la empresa',
        'tipo_prestaciones' => 'Tipo de Prestaciones',
        'conceptos_nomina' => 'Conceptos de Nómina',
        'periodos_nomina' => 'Periodos de Nomina',
        'conf_kit_baja' => 'Configuracion de kit de baja',

        'procesos_calculo' => 'Procesos de Calculo',
        'captura_incidencias' => 'Captura de Incidencias',
        'timbrado_nomina' => 'Timbrado Nomina',
        'timbrado_asimilados' => 'Timbrado Asimilados',
        'abrir_nomina' => 'Calculo Nomina',
        'aguinaldo' => ' Calculo Aguinaldo',
        'timbrado_aguinaldo' => 'Timbrado Aguinaldo',
        'finiquitos' => 'Calculo Finiquitos',
        'timbrado_finiquito' => 'Timbrado Finiquito',
        'dispersion_bancaria' => 'Dispersiones',

        'empleados' => 'Empleados',
        'control_empleados' => 'Control de Empleados',
        'cuentas_bancarias' => 'Cuentas Bancarias',
        'reingresos' => 'Reingresos',
        'kit_baja' => 'Kit de Baja',
        'asistencia' => 'Asistencias',
        'prestaciones_extras' => 'Prestaciones Extras',

        'imss' => 'IMSS',
        'registro_incapacidades' => 'Control de Incapacidades',
        'movi_afiliatorios' => 'Movimientos Afiliatorios',

        'contabilidad' => 'contabilidad',
        'control_polizas' => 'Control de Polizas',
        'ctrl_fac' => 'Control de Facturas',
        'facturador' => 'Facturador',

        'consultas' => 'Consultas',
        'control_credenciales' => 'Control de Credenciales',
        'reporte_asistencias' => 'Reporte de Asistencias',
        'reporte_acumulados_nomina' => 'Reporte Acumulados de Nomina',
        'recibos_nomina' => 'Recibos de Nomina',
        'docu_empleados' => 'Documetentos de Empleados',
        'recibos_asimilados' => 'Recibos de Asimilados',
        'reporte_movi_personal' => 'Reporte de Movimientos de Personal',
        'indice_rotacion_personal' => 'Indice Rotacion Personal',
        'reporte_nominas_periodo' => 'Reporte de Nominas por Periodo',
        'organigrama' => 'Organigrama',

        'formularios' => 'formularios',
        'encuesta_salida' => 'Encuestas',
        'conf_formularios' => 'Configuracion de Formularios',

        'herramientas' => 'Herramientas',
        'configuracion_empresa' => 'Parametros de la Empresa',
        'horarios_empleados' => 'Horarios para Empleados',
        'vigencia_contratos' => 'Vigencias de contratos',
        'solicitud_beneficiarios' => 'Solicitudes de Beneficiarios',
        'categoria_activos' => 'Categoria de Activos',
        'asignar_activos' => 'Asignar Activos',
        'avisos_rh' => 'Avisos RH',
        'vcard' => 'Configuración de Vcard',
        'biometricos_confg' => 'Configuración de Biometricos',

        'juridico' => 'Jurídico',
        'demandas' => 'Demandas',
        'calendario_demandas' => 'Calendario de Demandas',

        'norma035' => 'norma035',

        'sistema' => 'Sistema',
        'usuarios_sistema' => 'Usuarios del Sistema',
        'usuarios_timbrado' => 'Usuarios del timbrado',
        'contratos_hrsystem' => 'Contratos de HR-System',
        'conceptos_nomina_admin' => 'Adm. Conceptos de Nomina',
        'empresas_emisoras' => 'Empresas Emisoras',
        'empresas_receptora' => 'Empresas Receptoras',

    ];

    protected $permisos_categorias = [

        'parametria' => ['tabla_isr', 'tabla_subsidios', 'puestos_empresa', 'departamentos_empresa', 'tipo_prestaciones', 'conceptos_nomina', 'periodos_nomina', 'conf_kit_baja',],

        'procesos_calculo' => ['captura_incidencias', 'timbrado_nomina', 'timbrado_asimilados', 'abrir_nomina', 'aguinaldo', 'timbrado_aguinaldo', 'finiquitos', 'timbrado_finiquito', 'dispersion_bancaria',],

        'empleados' => ['control_empleados', 'cuentas_bancarias', 'reingresos', 'kit_baja', 'prestaciones_extras','asistencia'],

        'imss' => ['registro_incapacidades', 'movi_afiliatorios'],

        'contabilidad' => ['control_polizas', 'ctrl_fac', 'facturador'],

        'consultas' => ['control_credenciales', 'reporte_asistencias', 'reporte_acumulados_nomina', 'recibos_nomina', 'docu_empleados', 'recibos_asimilados', 'reporte_movi_personal', 'indice_rotacion_personal', 'reporte_nominas_periodo', 'organigrama',],

        'formularios' => ['encuesta_salida', 'conf_formularios',],

        'herramientas' => ['configuracion_empresa', 'horarios_empleados', 'vigencia_contratos', 'solicitud_beneficiarios', 'categoria_activos', 'asignar_activos', 'avisos_rh','vcard','biometricos_confg'],

        'juridico' => ['demandas', 'calendario_demandas'],

        'norma035' => ['norma035'],

        'sistema' => ['usuarios_sistema', 'usuarios_timbrado', 'contratos_hrsystem', 'conceptos_nomina_admin', 'empresas_receptora', 'empresas_emisoras',]
    ];

    public function sistemaUsuarios()
    {
        if(isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador ||
            isset(Auth::user()->admin) && !Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador){
            $usuarios = Usuario::whereIn('estatus', [0,1])->whereIn('autofacturador',[1])->whereIn('base_autofacturador',[Auth::user()->clientes->id])->get();
        }else{
            $usuarios = Usuario::whereIn('estatus', [0,1])->get();
        }

        return view('usuarios.usuarios', compact('usuarios'));
    }

    public function crearUsuario()
    {
        return view('usuarios.crear-usuario');
    }

    public function editarUsuario($usuario)
    {
        $usuarios = Usuario::where('id', $usuario)->first();
        return view('usuarios.editar-usuario', compact('usuarios'));
    }

    public function obtenerUsuario(Usuario $usuario, RelUsuarioAutofacturacion $usuario_autofacturacion)
    {   
        $resultados         = [];
        $usuarioss = RelUsuarioAutofacturacion::where('id_usuario', $usuario['id'])->get();
        foreach ($usuarioss as $key => $value) {
            $resultados[]=array(
                'id_autofacturacion' => $value->id_autofacturacion,
            );  
       }
        return response()->json(['ok' => 1, 'usuario' => $usuario,'base'=>$resultados]);
    }

    public function objeto_a_array($data){
        if (is_array($data) || is_object($data)){
            $result = array();
            foreach ($data as $key => $value){
                $result[$key] = $this->objeto_a_array($value);
            }
            return $result;
        }
        return $data;
    }

    public function addUrelpdateUsuario(Request $request)
    {
        $result = array();
        foreach ($request['datos'] as $key => $value){
            $result[$key] = $this->objeto_a_array($value);

        }

        $validatedData =[];
        $validatedData['base_autofacturador']=$result['data']['id'];

        if (isset($validatedData)) {
            Usuario::updateOrCreate(
                ['id' => Auth::id()], $validatedData
            );
            
            return response()->json(['ok' => true]);
        }
    }

    public function addUpdateUsuario(Request $request, Usuario $usuario)
    {
        $validatedData = $request->validate([
            'nombre_completo' => 'required',
            'email' =>'required|email',
            'email_jefe' =>'required|email',
            'email_ejecutivo' =>'required|email',
            'password' => '',
            'estatus' => 'numeric',
            'admin' => 'numeric',
            'autofacturador' => 'numeric',
            'comision' => '',
            'pagar_del' => '',
            'timbrar' => 'numeric',
            'id_vendedor' => '',
        ]);

        if(isset($request->base_autofacturador)) {
            $validatedData['base_autofacturador'] = $request->base_autofacturador[0];
        }

        if($validatedData['password'] != null){
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }


        if (isset($validatedData)) {
            $user=Usuario::updateOrCreate(
                ['id' => $request['id'], 'email' => $request['email']], $validatedData
            );

            if(!isset($request['id']) && isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador ||
                isset(Auth::user()->admin) && !Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador){

                RelUsuarioAutofacturacion::Create(['id_usuario' => $user->id,'id_autofacturacion'=>Auth::user()->clientes->id]);
            }else if(isset(Auth::user()->admin) && Auth::user()->admin  && isset(Auth::user()->autofacturador) && !Auth::user()->autofacturador && isset($request->base_autofacturador)){

                RelUsuarioAutofacturacion::where('id_usuario', $user->id)->delete();
                foreach ($request->base_autofacturador as $id) {
                    RelUsuarioAutofacturacion::Create(['id_usuario' => $user->id,'id_autofacturacion'=>$id]);
                }
            }
            
            return response()->json(['ok' => true]);
        }
        return response()->json(['ok' => false]);

    }

    public function eliminarUsuario(Request $request)
    {
        Usuario::where('id', $request->id)
            ->update(['estatus' => self::USUARIO_ELIMINADO]);

        session()->flash('success', 'Usuario eliminado correctamente');

        return redirect()->route('sistema.usuarios.usuariosistema');
    }

    public function permisosUsuario($usuario)
    {
        $usuario = Usuario::find($usuario);

        $usuario->permisos = PermisoSingh::where('id_usuario', $usuario->id)->first();

        if (is_null($usuario->permisos)) {
            $usuario->permisos = PermisoSingh::create(['id_usuario' => $usuario->id]);
        }


    }

    public function permisosUsuarioCambiarEmpresa($usuario, $empresa)
    {

        $usuario = Usuario::where('id', $usuario)->first();
        $ids_empresas_asignadas = UsuariosEmpresas::where('id_usuario', $usuario->id)->get();
        $id_empresas = [];

        foreach ($ids_empresas_asignadas as $id_empresas_asignadas) {
            $id_empresas[] = $id_empresas_asignadas->id_empresa;
        }

        $empresas = Empresa::whereIn('id', $id_empresas)
            ->select('id', 'razon_social', 'base')
            ->where('estatus', 1)
            ->get();

        if ($empresa == 0) {
            $empresa = new Empresa();
            $empresa->id = 0;
            $empresa->razon_social = "General";
            $this->permisosUsuario($usuario->id);
            $usuario->permisos = PermisoSingh::where('id_usuario', $usuario->id)->first();
        } else {
            $empresa = Empresa::find($empresa);
            cambiarBase($empresa->base);
            $usuario->permisos = Permiso::where('id_usuario', $usuario->id)->first();
        }

        if (is_null($usuario->permisos)) {
            $usuario->permisos = Permiso::create(['id_usuario' => $usuario->id]);
        }


        $permisos_categorias = $this->permisos_categorias;
        $permisos_nombres = $this->permisos_nombres;

        return view('usuarios.permisos-empresa-usuario', compact('empresa', 'empresas', 'permisos_categorias', 'permisos_nombres', 'usuario'));
    }

    public function permisosUsuarioCambiarPermiso(Request $request)
    {

        if ($request->id_empresa == 0) {
            cambiarBase('singh');
            $permiso = PermisoSingh::where('id_usuario', $request->id_usuario)
                ->update([
                    strval($request->permiso) => $request->valor
                ]);

            $ids_empresas_asignadas = UsuariosEmpresas::where('id_usuario', $request->id_usuario)->where('estatus',1)->get();
            $id_empresas = [];

            foreach ($ids_empresas_asignadas as $id_empresas_asignadas) {
                $empresa = Empresa::where('estatus',1)->find($id_empresas_asignadas->id_empresa);
                if($empresa!=null){
                    cambiarBase($empresa->base);
                    $permiso = DB::connection('empresa')->table('permisos')->where('id_usuario', $request->id_usuario)
                        ->update([$request->permiso => $request->valor]);
                }
                
            }

        }

        if ($request->id_empresa != 0) {

            $empresa = Empresa::find($request->id_empresa);

            cambiarBase($empresa->base);
            $permiso = DB::connection('empresa')->table('permisos')->where('id_usuario', $request->id_usuario)
                ->update([$request->permiso => $request->valor]);

        }


        //session()->flash('success', 'El permiso se actualizó correctamente');
        return $permiso;
    }

    public function empresaUsuario($usuario, Request $request)
    {
        $user = Usuario::find($usuario);

        if(!$user->autofacturador){
            $empresas = Empresa::select('id', 'razon_social', 'base', 'sss', 'sede')
                ->where('estatus', 1)
                ->orderBy('razon_social')
                ->get();

            $usr_emp = DB::table('usuarios_empresas')
                ->select('id_empresa')
                ->where('id_usuario', $usuario)
                ->get();
        }else{
            cambiarBase($user->clientes->base);

            $empresas = EmpresasEmisoras::select('empresas_emisoras.id', 'empresas_emisoras.razon_social', 'cat_etiquetas_emisoras.etiqueta', 'usuarios_empresas_emisoras.id_empresa')
                ->leftJoin('usuarios_empresas_emisoras', function ($join) use ($user) {
                    $join->on('usuarios_empresas_emisoras.id_empresa', '=', 'empresas_emisoras.id')
                        ->on('usuarios_empresas_emisoras.id_usuario', '=', DB::raw($user->id));
                })
                ->leftJoin('cat_etiquetas_emisoras', 'cat_etiquetas_emisoras.id', '=', 'empresas_emisoras.id_cat_etiqueta_emisora')
                ->orderBy('razon_social')
                ->get();

            $usr_emp = \App\Models\Autofacturador\UsuariosEmpresas ::select('id_empresa')
                ->where('id_usuario', $usuario)
                ->get();
        }


        $usuario_empresas = [];

        foreach ($usr_emp as $empId) {
            $usuario_empresas[] = $empId->id_empresa;
        }


        return view('usuarios.empresas-usuario', compact('empresas', 'usuario_empresas', 'usuario'));
    }

    protected function crearRegistroPermisos($usuario, $enterprise)
    {
        Config::set('database.connections.empresa.database', $enterprise);

        DB::connection('empresa')->table('permisos')
            ->insert(['id_usuario' => $usuario]);
    }

    protected function borrarRegistroPermisos($usuario, $enterprise)
    {
        Config::set('database.connections.empresa.database', $enterprise);
        DB::connection('empresa')->table('permisos')
            ->where('id_usuario', $usuario)
            ->delete();
    }


    public function asociarEmpresa(Request $request)
    {
        $user = Usuario::find($request->usuario);

        if($user->autofacturador){
            cambiarBase($user->clientes->base);
            if($request->sss != 1){
                $usr_emp = new \App\Models\Autofacturador\UsuariosEmpresas();
                $usr_emp->id_usuario = $request->usuario;
                $usr_emp->id_empresa = $request->empresa;
                $usr_emp->save();
            } else
                \App\Models\Autofacturador\UsuariosEmpresas::where('id_usuario', $request->usuario)->where('id_empresa', $request->empresa)->delete();
        }else{
            $id_empresa = Empresa::where('id', $request->empresa)->first();
            $enterprise = $id_empresa->base;

            if ($request->sss == 0) {
                $op = 0;

                DB::table('usuarios_empresas')->insert([
                    'id_usuario' => $request->usuario,
                    'id_empresa' => $request->empresa,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'estatus' => 1
                ]);

                $this->crearRegistroPermisos($request->usuario, $enterprise);

                session()->flash('success', 'La empresa se asocio correctamente');

            } else {
                $op = 0;

                DB::table('usuarios_empresas')
                    ->where('id_usuario', $request->usuario)
                    ->where('id_empresa', $request->empresa)
                    ->delete();

                $this->borrarRegistroPermisos($request->usuario, $enterprise);

                session()->flash('success', 'La empresa se desasocio correctamente');
            }

            $empresas = Empresa::select('id', 'sss', 'sede')
                ->where('id', $request->empresa)
                ->update(['sss' => $op]);
            // 'sede'=>$op
        }

        return redirect()->back();
    }


    public function asignarDepartamentoEmpresa($empresa, $usuario)
    {
        $empresa_base=substr('empresa000000', 0, '-'.strlen($empresa)).$empresa;
        $enterprise = $empresa_base;
        Config::set('database.connections.empresa.database', $enterprise);

        $departamentos = Departamento::all();

        $usuario_departamentos = DB::table('usuarios_empresas')
            ->select('departamentos')
            ->where('id_usuario', $usuario)
            ->where('id_empresa', $empresa)
            ->where('estatus', 1)
            ->first();

        if($usuario_departamentos && $usuario_departamentos->departamentos != null){
            $usuario_departamentos = explode(',', $usuario_departamentos->departamentos);
            foreach($departamentos as $key => $depto){
                if(in_array($depto->id, $usuario_departamentos)){
                    $departamentos[$key]['activo'] = 1;
                }
            }
        }

        return view('usuarios.asignar-departamentos-empresa', compact('empresa', 'usuario', 'departamentos', 'enterprise'));
    }


    public function actualizarDepartamento(Request $request)
    {
        $enterprise = $request->enterprise;

        Config::set('database.connections.empresa.database', $enterprise);

        DB::table('usuarios_empresas')
            ->where('id_usuario', $request->id_usuario)
            ->where('id_empresa', $request->id_empresa)
            ->update(['departamentos' => implode(",", $request->dept_id)]);

        session()->flash('success', 'Los departamentos se actualizaron correctamente');

        return redirect()->route('usuarios.empresa', $request->id_usuario);
    }

    public function asignarSedeEmpresa($empresa, $usuario)
    {
        $id_empresa = Empresa::where('id', $empresa)->first();
        $enterprise = $id_empresa->base;

        Config::set('database.connections.empresa.database', $enterprise);

        $sedes = DB::connection('empresa')->table('sedes')->get();

        $usuario_sedes = DB::table('usuarios_empresas')
            ->select('sedes')
            ->where('id_usuario', $usuario)
            ->where('id_empresa', $empresa)
            ->where('estatus', 1)
            ->first();

        return view('usuarios.asignar-sede-empresa', compact('empresa', 'usuario', 'sedes', 'enterprise'));
    }

    public function actualizarSede(Request $request)
    {
        $enterprise = $request->enterprise;

        Config::set('database.connections.empresa.database', $enterprise);

        $deptos = DB::connection('empresa')->table('sedes')->get();

        foreach ($deptos as $key) {
            if (in_array($key->id, $request->dept_id)) {
                $op = 1;
            } else {
                $op = 0;
            }

            $update = DB::connection('empresa')->table('sedes')
                ->where('id', $key->id)
                ->update(['estatus' => $op]);
        }

        DB::table('usuarios_empresas')
            ->where('id_usuario', $request->id_usuario)
            ->where('id_empresa', $request->id_empresa)
            ->update(['sedes' => implode(",", $request->dept_id)]);


        session()->flash('success', 'Las sedes se actualizaron correctamente');

        return redirect()->route('usuarios.empresa', $request->id_empresa);
    }

    public function timbradoUsuarios()
    {
        $usuarios_timbrado = UsuariosTimbrado::orderBy('id', 'desc')->get();

        return view('usuarios.usuarios-timbrado', compact('usuarios_timbrado'));
    }

    public function crearTimbradoUsuario()
    {
        return view('usuarios.crear-timbrado-al-usuario');
    }

    public function agregarTimbrado(Request $request)
    {

        $id = TimbradoCredenciales::select('id')->latest('id')->first();
        $id=$id->id;
        $id++;

        $user_timbrado= [
            'id'=> $id,
            'razon_social' => $request->razon_social,
            'razon_social_ss' => $request->razon_social_ss,
            'rfc' => $request->rfc,
            'regimen_fiscal' => $request->regimen_fiscal,
            'nombre_archivo' => 'user'.$id.$request->rfc,
            'certificado' => $request->certificado,
            'user' => base64_encode($request->user),
            'password' => base64_encode($request->password),
            'pwd_enc' => base64_encode($request->pwd_enc),
            'servicio' => $request->servicio,
            'servicio_cancelacion' => $request->servicio_cancelacion,
        ];


        if ($request->file('file_cer') && $request->file('file_key')) {

            $request->file('file_key')->storeAs('trash/', 'user'.$id.$user_timbrado['rfc'] . '.key', 'public');
            $request->file('file_cer')->storeAs('trash/', 'user'.$id.$user_timbrado['rfc']. '.cer', 'public');

            try {
                CFDIUtilsHrsystem::convertKeyToPem('user'.$id.$user_timbrado['rfc'], $request->pwd_enc,$request->password);
                CFDIUtilsHrsystem::convertCetToPem('user'.$id.$user_timbrado['rfc']);

                UsuariosTimbrado::create($user_timbrado);
                session()->flash('success', 'Se genero correctamente el timbrado');
                return redirect()->route('usuarios.timbrado');
            } catch (\Throwable $th) {
                session()->flash('danger', 'La contraseña es incorrecta');
                return redirect()->route('usuarios.timbrado');
            }

        }


    }

    public function eliminarTimbradoUsuario(Request $request)
    {
        UsuariosTimbrado::findOrFail($request->id)->delete();

        session()->flash('success', 'El timbrado se elimino correctamente');

        return redirect()->route('usuarios.timbrado');
    }
}
