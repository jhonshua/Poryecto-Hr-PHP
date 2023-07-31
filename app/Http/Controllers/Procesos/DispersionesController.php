<?php

namespace App\Http\Controllers\Procesos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Dispersion;
use App\Models\PeriodosNomina;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DispersionTotalExport;
use App\Exports\DispersionXlsBanorte;
use App\Exports\DispersionExcelExport;
use App\Exports\DispersionXlsSinCabecera;

class DispersionesController extends Controller
{
    const DISPERSION_NOMINA = 1;
    const DISPERSION_FINIQUITO = 2;
    const DISPERSION_AGUINALDO = 3;
    const BANCOMER = 12;
    const BANORTE = 72;
    const AZTECA = 127;

    public function inicio(Request $request)
    {
      
        tienePermisoA('dispersion_bancaria');

        if (!empty($request->tipo_dispersion)) {

            cambiarBase(Session::get('base'));
            $nombre_periodo = "";
            $ejercicio = $idperiodo = 0;
            
            if ($request->tipo_dispersion == "finiquitos") {

                $tipo_dispersion = SELF::DISPERSION_FINIQUITO;
                $bancos = $this->listaBancosDeEmpleados();
                $modal_administrar_dispersion = $this->modalAdministrarDispersion($request, $tipo_dispersion);
                $ejercicio_parametro = DB::connection('empresa')->table('parametros')->select('ejercicio')->first();
                //dd($ejercicio_parametro->ejercicio);
                $ejercicio_parametro->ejercicio = 2021;
                $empleados =  Empleado::select('ru.id as rutinaid','empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'empleados.fecha_baja', 'empleados.estatus', 'per.id as periodo', 'ru.neto_fiscal', 'per.nombre_periodo', 'per.ejercicio')
                //->leftJoin('rutinas' . $ejercicio . ' as ru', 'empleados.id', 'ru.id_empleado')
                
                //->leftJoin('periodos_nomina as per', 'ru.id_periodo', 'per.id')
                ->leftJoin("periodos_nomina as per", function ($join) {
                    $join->on("empleados.tipo_de_nomina", "per.nombre_periodo")
                    ->where('per.fecha_inicial_periodo', '<=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                    ->where('per.fecha_final_periodo', '>=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                    ->where('per.estatus',1);
                })
             
                ->join('rutinas' . $ejercicio_parametro->ejercicio . ' as ru', function ($join) {
                    $join->on('empleados.id', 'ru.id_empleado')
                        //->where('ru.id_periodo', 'per.id')
                        ->where('ru.neto_fiscal','!=', '')
                        ->where('ru.fnq_valor', 1);
                })
                ->where('empleados.finiquitado', 1)
                ->whereIn('empleados.estatus', array(2, 20))
                ->whereYear('empleados.fecha_baja',$ejercicio_parametro->ejercicio)
                ->orderBy('empleados.fecha_baja')
                ->get();

              /*  $empleados = Empleado::select('empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'empleados.fecha_baja', 'per.id as periodo', 'ru.neto_fiscal', 'per.nombre_periodo', 'per.ejercicio')
                    
                
                ->leftJoin("periodos_nomina as per", function ($join) {
                    $join->on("empleados.tipo_de_nomina", "per.nombre_periodo")
                    ->where('per.fecha_inicial_periodo', '<=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                    ->where('per.fecha_final_periodo', '>=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                    ->where('per.estatus',1);
                })
                
                ->join('rutinas' . $ejercicio_parametro->ejercicio . ' as ru', function ($join) {
                    $join->on('empleados.id', 'ru.id_empleado')
                        ->where('ru.id_periodo', 'per.id')
                        ->where('ru.fnq_valor', 1);
                })
                //->join('rutinas' . $ejercicio_parametro->ejercicio . ' as ru', 'empleados.id', 'ru.id_empleado')
                   // ->join('periodos_nomina as pernom', 'pernom.id', 'ru.id_periodo')
                    // ->where('ru.fnq_valor',1)->where('ru.neto_fiscal','!=','')
                    //->where('empleados.finiquitado', 1)
                    ->whereIn('empleados.estatus', array(2, 20))
                    //  ->where('pernom.fecha_inicial_periodo','<=','empleados.fecha_baja')
                    //  ->where('pernom.fecha_final_periodo','>=','empleados.fecha_baja')
                    //  ->where('pernom.nombre_periodo','empleados.tipo_de_nomina')
                    //  ->where('pernom.estatus','!=',2)
                  //  ->groupby('ru.id_empleado')
                    ->get();*/

                return view("procesos.dispersion.finiquito", compact('ejercicio', 'idperiodo', 'modal_administrar_dispersion', 'empleados', 'bancos', 'tipo_dispersion'));
                //dd($empleados);

            }else if($request->tipo_dispersion == 'nominas') {
                
                $tipo_dispersion = SELF::DISPERSION_NOMINA;
                $periodos_nomina = PeriodosNomina::with('dispersiones')->orderBy('id', 'DESC')->get();

                return view("procesos.dispersion.nomina", compact('periodos_nomina', 'tipo_dispersion'));
            
            }else{

                $titulo = "Aguinaldo";
                $tipo_dispersion = SELF::DISPERSION_AGUINALDO;
                $bancos = $this->listaBancosDeEmpleados();
                $modal_administrar_dispersion = $this->modalAdministrarDispersion($request, $tipo_dispersion);
                $ejercicios = DB::connection('empresa')->table('aguinaldo')->select('ejercicio')->groupBy('ejercicio')->get();
                
                return view("procesos.dispersion.administrar_dispersion", compact('titulo', 'nombre_periodo', 'ejercicio', 'bancos', 'modal_administrar_dispersion', 'ejercicios', 'tipo_dispersion', 'idperiodo'));
            }
        }
        return view("procesos.dispersion.inicio");
    }

    // trae a los bancos usados por los empleados
    public function listaBancosDeEmpleados()
    {
        return Empleado::select('bancos.banco1 as id', 'bancos.nombre')
            ->join("singh.bancos", 'empleados.id_banco', 'bancos.banco1')
            ->where('empleados.estatus', 1)
            ->groupBy('bancos.banco1')->get()->toArray();
    }

    function modalAdministrarDispersion(Request $request, $tipo_de_dispersion)
    {
        //dd($tipo_de_dispersion);
        if($tipo_de_dispersion == SELF::DISPERSION_FINIQUITO){
            $modal = array('ruta' => route('procesos.dispersion.panelAdministracion.gestion'));
        }else if($tipo_de_dispersion == SELF::DISPERSION_NOMINA) {
            $modal = array('ruta' => route('procesos.dispersion.panelAdministracion.periodo'));
        }else if($tipo_de_dispersion == SELF::DISPERSION_AGUINALDO) {
            $modal = array('ruta' => route('procesos.dispersion.panelAdministracion.periodo'));
        }
        return $modal;
    }

    function panelAdminDispersionPeriodo(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        $depa = array();
        $deptos_asignados = Session::get('usuarioDepartamentos');
        $ejercicio = (!empty($request->ejercicio)) ? $request->ejercicio : null;
        $nombre_periodo = (!empty($request->nombre_periodo)) ? $request->nombre_periodo : null;
        $idperiodo = (!empty($request->idperiodo)) ? $request->idperiodo : null;;
        
        if ($request->tipo_dispersion == SELF::DISPERSION_NOMINA) {
            $periodo = periodosNomina::find($request->idperiodo);

            $departamentos = Departamento::select('departamentos.nombre', 'departamentos.id')
                ->selectRaw('count(empleados.id) as empleados')
                ->join('empleados', 'departamentos.id', 'empleados.id_departamento')
                ->where('empleados.estatus', 1)
                // ->where('empleados.id_banco', $request->banco)
                ->where('empleados.tipo_de_nomina', $nombre_periodo)
                ->whereIn('departamentos.id', $deptos_asignados)
                ->groupBy('departamentos.nombre')
                ->orderBy('departamentos.nombre')
                ->get();
        } else if ($request->tipo_dispersion == SELF::DISPERSION_AGUINALDO){

            $titulo = " Aguinaldo";
            $ejercicio = (!empty($request->ejercicio_aguinaldo)) ? $request->ejercicio_aguinaldo : 0;
            $departamentos = Departamento::select('departamentos.nombre', 'departamentos.id')
                ->selectRaw('count(empleados.id) as empleados')
                ->join('empleados', 'departamentos.id', 'empleados.id_departamento')
                ->where('empleados.estatus', 1)
                ->whereIn('departamentos.id', $deptos_asignados)
                ->groupBy('departamentos.nombre')
                ->orderBy('departamentos.nombre')
                ->get();
        }

        if($departamentos->count() > 0){
            return response()->json(['ok' => 1, 'ruta' => route('procesos.dispersion.panelAdministracion.gestion'), 'nombre_periodo' => $nombre_periodo, 'departamentos' => $departamentos, 'idperiodo' => $idperiodo, 'ejercicio' => $ejercicio, 'idbanco' => $request->banco, 'tipo_dispersion' => $request->tipo_dispersion]);
        }else{
            return response()->json(['ok' => 0, 'mensaje' => "No hay departamentos con el Banco seleccionado"]);
        }

        //return view("procesos.dispersion.seleccionar_departamentos",compact('departamentos','idperiodo'));
    }

    public function confirmarDispersion(Request $request)
    {
        $importe = ($request->tipo_importe == "Fiscal") ? "NetoFiscal" : "NetoSindical";

        cambiarBase(Session::get('base'));

        if($request->tipo_dispersion == self::DISPERSION_FINIQUITO){
            $dispersados = Dispersion::where(function ($query) use ($request, $importe) {
                $query->where('id_periodo', $request->id_periodo)
                ->where('importe', $importe)
                ->where('tipo_dispersion',$request->tipo_dispersion);
            });
            
            //dd($dispersados->get());
            foreach($dispersados->get() as $dispersado){
                Empleado::find($dispersado->id_empleado)->update(['estatus' => 2]);
            }
            $dispersados->update(['confirmado' => 1]);

        }else{
            Dispersion::where(function ($query) use ($request, $importe) {
                $query->where('id_periodo', $request->id_periodo)->where('importe', $importe)
                ->where('tipo_dispersion',$request->tipo_dispersion);
            })->update(['confirmado' => 1]);
        }

        return response()->json(['ok' => 1]);
    }


    public function panelAdminDispersion(Request $request)
    {
        cambiarBase(Session::get('base'));

        $titulo = "";
        $ejercicio = (!empty($request->ejercicio)) ? $request->ejercicio : 0;
        $nombre_periodo = (!empty($request->nombre_periodo)) ? $request->nombre_periodo : 0;
        $idperiodo = 0;
        $idempleado = (!empty($request->idempleado)) ? $request->idempleado : 0;
        $tipo_dispersion = $request->tipo_dispersion;
        if ($tipo_dispersion == SELF::DISPERSION_NOMINA || $tipo_dispersion == SELF::DISPERSION_AGUINALDO) {
            $titulo = ($tipo_dispersion == SELF::DISPERSION_NOMINA) ? "Nomina" : "Aguinaldo";
            $idperiodo = $request->idperiodo;
            $bancos = $this->listaBancosDeEmpleados();
            $modal_administrar_dispersion = $this->modalAdministrarDispersion($request, $tipo_dispersion);
            return view("procesos.dispersion.administrar_dispersion", compact('titulo', 'nombre_periodo', 'idempleado', 'bancos', 'modal_administrar_dispersion', 'ejercicio', 'tipo_dispersion', 'idperiodo'));
        } else if ($tipo_dispersion == SELF::DISPERSION_FINIQUITO) {
            $titulo = "Finiquito";
            $idperiodo = $request->idperiodo;
            $bancos = $this->listaBancosDeEmpleados();
            $modal_administrar_dispersion = $this->modalAdministrarDispersion($request, $tipo_dispersion);
            return view("procesos.dispersion.administrar_dispersion", compact('titulo', 'nombre_periodo', 'idempleado', 'bancos', 'modal_administrar_dispersion', 'ejercicio', 'tipo_dispersion', 'idperiodo'));
        }
    }

    function GestionaDispersion(Request $request)
    {
        $numero_empleados_banco = 0;
        $nombre_periodo = (!empty($request->nombre_periodo)) ? $request->nombre_periodo : null;
        $periodo = $request->idperiodo;
        $idempleado = (!empty($request->idempleado)) ? $request->idempleado : 0;

        $cadena_departamentos = "";
        if (!empty($request->checkDepartamento)) {
            $departamentos = $request->checkDepartamento; //implode(",",$request->checkDepartamento);
            $cadena_departamentos = implode(",", $departamentos);
        }

        cambiarBase(Session::get('base'));
        if ($request->tipo_dispersion == SELF::DISPERSION_AGUINALDO) { // dispersion aguinaldo
            $aguinaldos = $this->obtenerAguinaldosPorBanco($departamentos, $request->ejercicio, $request->idbanco);
            $numero_empleados_banco = $aguinaldos->count();
        } else if ($request->tipo_dispersion == SELF::DISPERSION_NOMINA) {
            $empleados_rutina = DB::connection('empresa')->table('rutinas' . $request->ejercicio)->where('id_periodo', $periodo)
                ->where('fnq_valor', 0)
                ->whereIn('id_empleado', function ($query) use ($periodo, $request, $departamentos) {
                    $query->select('id')
                        ->from(with(new Empleado)->getTable())
                        ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                        ->where('id_banco', $request->idbanco)
                        ->whereIn('id_departamento', $departamentos)
                        ->where('tipo_de_nomina', $request->nombre_periodo);
                })->get();
            $numero_empleados_banco = $empleados_rutina->count();
            //dd( $empleados_rutina);

        } else if ($request->tipo_dispersion == SELF::DISPERSION_FINIQUITO) {
            $request->idbanco =  $request->banco;
            /* $queryemple="SELECT *,em.idempleado as idem 
            from $base.rutinas$ejercicio ru 
            join $base.empleado em on ru.idempleado=em.idempleado 
           - where banco=$idbanco 
           - and FnqValor=1 
           - and idperiodo='$idperiodo' 
           - and tipodeNomina='$TipoNomina' 
            and em.idempleado='$idempleado'";*/
            $empleados_rutina = DB::connection('empresa')
                ->table('rutinas' . $request->ejercicio)->where('id_periodo', $periodo)
                ->where('fnq_valor', 1)
                ->whereIn('id_empleado', function ($query) use ($periodo, $request, $idempleado) {
                    $query->select('id')
                        ->from(with(new Empleado)->getTable())
                        ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                        ->where('id_banco', $request->idbanco)
                        //->whereIn('id_departamento', $departamentos)
                        ->where('id', $idempleado)
                        ->where('tipo_de_nomina', $request->nombre_periodo);
                })->get();
            $numero_empleados_banco = $empleados_rutina->count();
        }


        //dd($empresa_emisora);
        return response()->json(['ok' => 1, 'cadena_departamentos' => $cadena_departamentos, 'ruta_ver_dispersion' => route('procesos.dispersion.ver'), 'nombre_periodo' => $nombre_periodo, 'idempleado' => $idempleado, 'numero_empleados_banco' => $numero_empleados_banco, 'idperiodo' => $request->idperiodo, 'ejercicio' => $request->ejercicio, 'idbanco' => $request->idbanco, 'tipo_dispersion' => $request->tipo_dispersion]);
    }
    
    public function DatosDispersion(Request $request)
    {
        //dd($_POST);
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir = (!empty($request->banco_omitir) && count($request->banco_omitir) > 0) ? $request->banco_omitir : array(0);

        cambiarBase(Session::get('base'));
        
        if ($request->cuenta == 4) {
            $titulo = 'Clave Interbancaria';
        } else {
            $titulo = 'Cuenta Bancaria';
        }

        if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {

            $empleados = $this->finiquitoDatos($request);
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            //dd($request->tipo_dispersion);
            $empleados = $this->aguinaldoDatos($request, $omitir, $departamentos);
        } else if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            //echo "aqui";
            $empleados = $this->nominaDatos($request, $omitir, $departamentos);
        }

        if ($request->tipoarchivo == 'PAG' || $request->tipoarchivo == 'XLS') {
            $empleados = $empleados->where('id_banco', self::BANORTE);
        } else if ($request->tipoarchivo == 'XLSbaz') {
            $empleados = $empleados->where('id_banco', self::AZTECA);
        }

        //obtener el importe
        //obtener la ruta para generar el archivo
        $url_archivo_generar = $this->archivoAgenerar($request);
        //dd($empleados->groupBy('razon_social')->toArray());
        return ([
            'ok' => 1,
            'empleados' => $empleados->groupBy('razon_social')->toArray(),
            'encabezado' => $titulo,
            'tipo_archivo' => $request->tipoarchivo,
            'url_archivo_generar' => $url_archivo_generar,
            'fechadispersion' => $request->fechadispersion,
            'idperiodo' => $request->idperiodo,
            'idbanco' => $request->idbanco,
            'ejercicio' => $request->ejercicio,
            'tipo_dispersion' => $request->tipo_dispersion,
            'idempleado' => $request->idempleado,
            'nombre_periodo' => $request->nombre_periodo,
            'cadena_departamentos' => $request->cadena_departamentos,
            'tipoimporte' => $request->tipoimporte,
            'cuenta' => $request->cuenta,
            'TipoOperacion' => $request->TipoOperacion,
            'referencia' => $request->referencia,
            'banco_omitir' => implode(",", $omitir),
            'concepto' => (isset($request->concepto) && !empty($request->concepto)) ? $request->concepto : ""
        ]);
    }

    public function archivoAgenerar(Request $request)
    {
        if ($request->idbanco == SELF::BANCOMER) { //12
            $ruta = route('procesos.dispersion.generaDiscoBancomer');
        } else if ($request->idbanco == SELF::BANORTE) { // 72
            if ($request->tipoarchivo == 'PAG') {
                $ruta = route('procesos.dispersion.generaPAG');
            } else if ($request->tipoarchivo == 'XLS') {
                $ruta = route('procesos.dispersion.generaXls');
            } else if ($request->tipoarchivo == 'SPEIXLS') { // aquí sera el txt
                $ruta = route('procesos.dispersion.generaXlsBanorteSPEI');
            }
        } else if ($request->idbanco == SELF::AZTECA) { //127
            if ($request->tipoarchivo == 'XLSbaz') {
                $ruta = route('procesos.dispersion.generaXlsBaz');
            } else if ($request->tipoarchivo == 'XLSbazMas') {
                $ruta = route('procesos.dispersion.generaXlsBazMas');
            }
        }

        return $ruta;
    }
    function generaXls(Request $request)
    { //Banorte
        $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $request->idperiodo . "/");
        $FecHr = date('Y/m/d H:i:s');
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir = (!empty($request->banco_omitir) && count($request->banco_omitir) > 0) ? $request->banco_omitir : array(0);
        $consecutivo = $this->getConsecutivo($request->idperiodo);
        $nombre_banco = $this->nombreBanco($request->idbanco);
        $empleados_dispersion = array();
        cambiarBase(Session::get('base'));
        //"SELECT emi.claveEmisora as clave,$bancoemisor as namecuenta from empresasemisoras emi join asigna_empresasemisoras asiemi on emi.ID_EmpresaE=asiemi.ID_EmpresaE inner join empresas empre on asiemi.idEmpresa=empre.idEmpresa where empre.RazonSocial='$empresa' and emi.Status=0 and asiemi.Status=0 and empre.Status=0";

        if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            $empleados_por_emisora = $this->nominaDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE NOMINA";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            $empleados_por_emisora = $this->aguinaldoDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE AGUINALDO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'TotalSindical' : 'TotalFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {
            $empleados_por_emisora = $this->finiquitoDatos($request);
            $descripcion = "PAGO DE FINIQUITO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        }

        $empleados_por_emisora = $empleados_por_emisora->where('id_banco', self::BANORTE);
        $empleadosxls = array();
        if (isset($request->emisora_id)) {
            foreach ($empleados_por_emisora->where('id_emisora', $request->emisora_id)->toArray() as $clave_emisora => $empleado) {
                //dump($empleados);
                $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
                $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);
                //foreach($empleados as $empleado){
                $decimales  = explode(".", round($empleado[$request->tipoimporte],2));
                if (!isset($decimales[1])) {
                    $decimales[1] = "00";
                } else {
                    $decimales[1] = str_replace("0.", "", round("." . $decimales[1], 2));
                }

                $name = utf8_decode($empleado['nombre'] . " " . $empleado['apaterno'] . " " . $empleado['amaterno']);
                $nombre = $this->remplazoNombre($name);

                $empleadosxls[$empleado['id']]['oper'] = $request->TipoOperacion;
                $empleadosxls[$empleado['id']]['idBanco'] = "" . $empleado['id_banco'] . "";
                $empleadosxls[$empleado['id']]['cuentaorigen'] = $emisora['cuenta'];
                $empleadosxls[$empleado['id']]['cuentaclabe'] = $empleado[$request->cuenta];
                $empleadosxls[$empleado['id']]['ImporteTotal'] = str_pad($decimales[0], 13, "0", STR_PAD_LEFT) . $decimales[1];
                $empleadosxls[$empleado['id']]['referencia'] = ($request->referencia != null && $request->referencia != "") ? $request->referencia : "";
                $empleadosxls[$empleado['id']]['descripcion'] = $descripcion;
                $empleadosxls[$empleado['id']]['rfc'] = $empleado['rfc'];
                $empleadosxls[$empleado['id']]['iva'] = "0";
                $empleadosxls[$empleado['id']]['fecha_aplicacion'] = "";
                $empleadosxls[$empleado['id']]['instruccion_de_pago'] = $nombre;
                //$empleadosxls[$empleado['id']]  = collect($empleadosxls[$empleado['id']]);

                // preparando el insert para dispersar
                $empleados_dispersion[] = array(
                    'id_empleado' => $empleado['id'],
                    'id_periodo' => $request->idperiodo,
                    'importe' => $nombre_importe,
                    'fecha_guardado' => $FecHr,
                    'confirmado' => 0,
                    'archivo_generado' => 'BANORTE.XLS',
                    'name_archivo' => $request->nombre_archivo . '.xlsx',
                    'ruta' => $folder,
                    'tipo_dispersion' => $request->tipo_dispersion,
                    'ejercicio' => $request->ejercicio
                );

            }
            //insertar los empleados a dispersar
            $this->crearDispersion($empleados_dispersion);
        }

        //dd($empleadosxls);
        return Excel::download(
            new DispersionXlsBanorte(collect($empleadosxls)),
            $request->nombre_archivo . '.xlsx'
        );
    }

    public function remplazoNombre($nombre)
    {
        $n = $nombre;
        $originales  = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'AAAAAAACEEEEIIIIDÑOOOOOOUUUUYBSAAAAAAACEEEEIIIIDÑOOOOOOUUUYYBYRR';
        $n = strtr($n, $originales, $modificadas);
        //$n = substr($n, 0, 30);
        return $n;
    }
    
    public function verDatosDispersion(Request $request)
    {

        return response()->json($this->DatosDispersion($request));

        // return response()->json(['ok' => 1, 'empleados' => $empleados_tabla, 'encabezado' => $titulo, 'tipo_archivo' => $request->tipoarchivo]);
    }

    public function generaPAG(Request $request)
    { //Banorte
        //dd($_POST);
        if($request->idperiodo=='NULL' || $request->idperiodo=='null' || $request->idperiodo==NULL || $request->idperiodo==''){
            cambiarBase(Session::get('base'));
            $periodo = periodosNomina::where('activo', 1)->first();
            $id_periodo  = $periodo->id;
            $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $id_periodo. "/");
        }else{
            $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $request->idperiodo . "/");
        }
        
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir = (!empty($request->banco_omitir) && count($request->banco_omitir) > 0) ? $request->banco_omitir : array(0);


        cambiarBase(Session::get('base'));
        $empleados_dispersion = array();
        $tiporegistro = 'D';
        $tiporegistro01 = 'H';
        $claveservicio = 'NE';
        $referenciaservicio = '                                        ';
        $leyendaOrdenante = '                                        ';
        $importealtasenviados = '000000000000000';
        $cuentasverificar = '000000';
        $accion = '0';
        $filler = '                                                                             ';
        $identi = date('YmdHis');
        $date = date_create($request->fechadispersion);
        $FechaProceso = date_format($date, 'Ymd');
        $FecHr = date('Y/m/d H:i:s');
        if($request->idperiodo=='NULL' || $request->idperiodo=='null' || $request->idperiodo==NULL || $request->idperiodo==''){

            $consecutivo = $this->getConsecutivo($id_periodo);
        }else{
            $consecutivo = $this->getConsecutivo($request->idperiodo);
        }
        $nombre_banco = $this->nombreBanco($request->idbanco);
        $tipo = $request->tipo_dispersion;

        if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            $empleados_por_emisora = $this->nominaDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE NOMINA";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            $empleados_por_emisora = $this->aguinaldoDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE AGUINALDO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'TotalSindical' : 'TotalFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {
            $empleados_por_emisora = $this->finiquitoDatos($request);
            $descripcion = "PAGO DE FINIQUITO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        }
        $empleados_por_emisora = $empleados_por_emisora->where('id_banco', self::BANORTE);


        if (isset($request->emisora_id)) {
            $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
            $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);

            $clave_emisora = $emisora['clave_emisora'];
            $claveemisora = str_pad($clave_emisora, 5, "0", STR_PAD_LEFT);
            $nameArchivo = 'NI' . $clave_emisora . $consecutivo;
            $nombre =  $nameArchivo . ".pag";
            $Rutanamearchi = $folder . $nombre;
            
            if (!File::exists($Rutanamearchi)) {
                File::makeDirectory($folder, $mode = 0777, true, true);
            }
            $contenido_empleados = "";
            $tot = 0;
            $totalImporte = 0;
            foreach ($empleados_por_emisora->where('id_emisora', $request->emisora_id)->toArray() as $clave_emisora => $empleado) {
                if(round($empleado[$request->tipoimporte],2) != 0.00 && round($empleado[$request->tipoimporte],2) != "" && round($empleado[$request->tipoimporte],2) != null){

                    $numerocaracteresTipocuenta = strlen($empleado[$request->cuenta]);
                        if ($numerocaracteresTipocuenta == 18) {
                            $TipoCuenta = '40';
                        } else if ($numerocaracteresTipocuenta == 16) {
                            $TipoCuenta = '03';
                        } else {
                            $TipoCuenta = '01';
                        }

                    $tot++;
                    $cuentaBanca = str_pad($empleado[$request->cuenta], 18, "0", STR_PAD_LEFT);
                    $tipoCuenta = str_pad($TipoCuenta, 2, "0", STR_PAD_LEFT);
                    $idempleado = str_pad($empleado['id'], 10, "0", STR_PAD_LEFT);
                    $bancario = str_pad($empleado['id_bancario'], 10, "0", STR_PAD_LEFT);
                    $decimales  = explode(".", round($empleado[$request->tipoimporte],2));
                    if (!isset($decimales[1])) {
                        $decimales[1] = "00";
                    } else {
                        $decimales[1] = str_pad(str_replace("0.", "", round("." . $decimales[1], 2)),2,"0",STR_PAD_RIGHT);
                    }
                    $ImporteTotal = str_pad($decimales[0], 13, "0", STR_PAD_LEFT) . $decimales[1];
                    $banco = str_pad($empleado['id_banco'], 3, "0", STR_PAD_LEFT);

                    $contenido_empleados .= $tiporegistro . $FechaProceso . $bancario . $referenciaservicio . $leyendaOrdenante . 
                    $ImporteTotal . $banco . $tipoCuenta . $cuentaBanca . '0' . ' ' . '00000000' . '                  ' . PHP_EOL;
                    if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dump($decimales,$ImporteTotal);
                }

                    $totalImporte += round($empleado[$request->tipoimporte],2);


                    if($request->idperiodo=='NULL' || $request->idperiodo=='null' || $request->idperiodo==NULL || $request->idperiodo==''){
                        $empleados_dispersion[] = array(
                        'id_empleado' => $empleado['id'],
                        'id_periodo' => $id_periodo,
                        'importe' => $nombre_importe,
                        'fecha_guardado' => $FecHr,
                        'confirmado' => 0,
                        'archivo_generado' => $request->nombre_archivo . '.PAG',
                        'name_archivo' => $request->nombre_archivo . '.PAG',
                        'ruta' => $folder,
                        'tipo_dispersion' => $request->tipo_dispersion,
                        'ejercicio' => $request->ejercicio
                    );
                    }else{
                        $empleados_dispersion[] = array(
                        'id_empleado' => $empleado['id'],
                        'id_periodo' => $request->idperiodo,
                        'importe' => $nombre_importe,
                        'fecha_guardado' => $FecHr,
                        'confirmado' => 0,
                        'archivo_generado' => $request->nombre_archivo . '.PAG',
                        'name_archivo' => $request->nombre_archivo . '.PAG',
                        'ruta' => $folder,
                        'tipo_dispersion' => $request->tipo_dispersion,
                        'ejercicio' => $request->ejercicio
                    );

                    }
                    

                }
            }
            //insertar los empleados a dispersar
            $this->crearDispersion($empleados_dispersion);
        }

        if (!empty($totalImporte) && $totalImporte != 0 && $totalImporte != "") {
            $totalDecimales  = explode(".", $totalImporte);
            if (!isset($totalDecimales[1])) {
                $totalDecimales[1] = "00";
            } else {
                $totalDecimales[1] = str_replace("0.", "", round("." . $totalDecimales[1], 2));
            }
        }
        $registroenviados01 = str_pad($totalDecimales[0], 13, "0", STR_PAD_LEFT) . $totalDecimales[1];
        $numeroregistros = str_pad($tot, 6, "0", STR_PAD_LEFT);

        if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd();
                }
        
        $file = fopen($Rutanamearchi, "w+");
        fwrite($file, $tiporegistro01 . $claveservicio .  $claveemisora . $FechaProceso . $consecutivo . $numeroregistros . $registroenviados01 . $cuentasverificar . $importealtasenviados . $cuentasverificar . $importealtasenviados . $cuentasverificar . $accion . $filler . PHP_EOL);
        fwrite($file, $contenido_empleados);
        fclose($file);
        $this->bajarArchivo($folder, $nombre);
        
    }

    public function bajarArchivo($directorio, $file)
    {
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($directorio . $file));
        header("Content-Type: text/plain");
        readfile($directorio . $file);
    }

    public function crearDispersion($empleados)
    {
        cambiarBase(Session::get('base'));
        $periodo = periodosNomina::where('activo', 1)->first();
    
        if(!empty($periodo->id)){

            $id_periodo  = $periodo->id;
            if(Session::get('usuarioPermisos')['id_usuario']==64){
                        //dd($id_periodo);
            }


            foreach($empleados as $empleado){
                if ($empleado['tipo_dispersion'] == self::DISPERSION_AGUINALDO) {
                    
                    Dispersion::updateOrCreate(
                            [
                                'id_empleado' => $empleado['id_empleado'],
                                'id_periodo' =>$id_periodo,
                                'ejercicio' => $empleado['ejercicio'],
                                'importe' => $empleado['importe']
                            ],
                            [
                                'id_empleado' => $empleado['id_empleado'],
                                'ejercicio' => $empleado['ejercicio'],
                                'fecha_guardado' => $empleado['fecha_guardado'],
                                'confirmado' => $empleado['confirmado'],
                                'archivo_generado' => $empleado['archivo_generado'],
                                'name_archivo' => $empleado['name_archivo'],
                                'ruta' => $empleado['ruta'],
                                'importe' => $empleado['importe'],
                                'tipo_dispersion' => $empleado['tipo_dispersion'], 
                            
                            ]
                        );
                }else{
                // dd($empleados);
                    Dispersion::updateOrCreate(
                        [
                            'id_empleado' => $empleado['id_empleado'],
                            'id_periodo' => $empleado['id_periodo'],
                            'importe' => $empleado['importe']
                        ],
                        [
                            'id_empleado' => $empleado['id_empleado'],
                            'id_periodo' => $empleado['id_periodo'],
                            'fecha_guardado' => $empleado['fecha_guardado'],
                            'confirmado' => $empleado['confirmado'],
                            'archivo_generado' => $empleado['archivo_generado'],
                            'name_archivo' => $empleado['name_archivo'],
                            'ruta' => $empleado['ruta'],
                            'importe' => $empleado['importe'],
                            'tipo_dispersion' => $empleado['tipo_dispersion'], 
                        ]
                    );
                } 
            }
        }
    }

    public function nombreBanco($idbanco)
    {
        if ($idbanco == self::BANORTE) {
            return "BANORTE";
        } else if ($idbanco == self::AZTECA) {
            return "AZTECA";
        } else {
            return "BANCOMER";
        }
    }

    public function getConsecutivo($idperiodo)
    {
        $consecutivo = str_pad($idperiodo, 2, "0", STR_PAD_LEFT);
        if ($consecutivo > 99) {
            if ($consecutivo == '100') {
                $consecutivo = rand(1, 99);
            } else {
                $consecutivo = substr($consecutivo, -2, 2);
            }
        }
        return $consecutivo;
    }

    public function aguinaldoDatos(Request $request, $omitir, $departamentos)
    {
        return Empleado::select('emi.id as id_emisora', 'emi.clave_emisora', 'emi.razon_social', 'agui.total_sindical as Sindical', 'agui.total_fiscal as Fiscal', 'empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'empleados.cuenta_bancaria', 'empleados.cuenta_bancaria2', 'empleados.cuenta_bancaria3', 'empleados.clabe_interbancaria', 'empleados.id_banco', 'empleados.id_bancario', 'empleados.tipo_cuenta', 'empleados.rfc')
            ->join('aguinaldo as agui', 'empleados.id', 'agui.id_empleado')
            ->join('categorias as cat', 'empleados.id_categoria', 'cat.id')
            ->join('singh.registro_patronal as rp', 'cat.tipo_clase', 'rp.id')
            ->join('singh.empresas_emisoras as emi', 'rp.id_empresa_emisora', 'emi.id')
            ->where('empleados.estatus', 1)
            ->whereNotIn('empleados.id_banco', $omitir)
            ->whereIn('empleados.id_departamento', $departamentos)
            ->where("agui.ejercicio", $request->ejercicio)->get();
    }

    public function finiquitoDatos(Request $request)
    {
        return Empleado::select('emi.id as id_emisora', 'emi.clave_emisora', 'emi.razon_social', 'ru.neto_sindical as Sindical', 'ru.neto_fiscal as Fiscal', 'empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'ru.neto_fiscal as neto', 'empleados.cuenta_bancaria', 'empleados.cuenta_bancaria2', 'empleados.cuenta_bancaria3', 'empleados.clabe_interbancaria', 'empleados.id_banco', 'empleados.id_bancario', 'empleados.tipo_cuenta', 'empleados.rfc')
            ->join('rutinas' . $request->ejercicio . ' as ru', 'empleados.id', 'ru.id_empleado')
            ->join('categorias as cat', 'empleados.id_categoria', 'cat.id')
            ->join('singh.registro_patronal as rp', 'cat.tipo_clase', 'rp.id')
            ->join('singh.empresas_emisoras as emi', 'rp.id_empresa_emisora', 'emi.id')
            ->where('ru.fnq_valor', 1)
            ->where('ru.id_periodo', $request->idperiodo)
            ->where('empleados.tipo_de_nomina', $request->nombre_periodo)
            ->where('empleados.id', $request->idempleado)
            ->get();
    }

    public function nominaDatos(Request $request, $omitir, $departamentos)
    {
        return Empleado::select('emi.id as id_emisora', 'emi.clave_emisora', 'emi.razon_social', 'ru.neto_sindical as Sindical', 'ru.neto_fiscal as Fiscal', 'empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'ru.neto_fiscal as neto', 'empleados.cuenta_bancaria', 'empleados.cuenta_bancaria2', 'empleados.cuenta_bancaria3', 'empleados.clabe_interbancaria', 'empleados.id_banco', 'empleados.id_bancario', 'empleados.tipo_cuenta', 'empleados.rfc')
            ->join('rutinas' . $request->ejercicio . ' as ru', 'empleados.id', 'ru.id_empleado')
            ->join('categorias as cat', 'empleados.id_categoria', 'cat.id')
            ->join('singh.registro_patronal as rp', 'cat.tipo_clase', 'rp.id')
            ->join('singh.empresas_emisoras as emi', 'rp.id_empresa_emisora', 'emi.id')
            ->where('ru.fnq_valor', 0)
            ->where('empleados.estatus', 1)
            ->where('ru.id_periodo', $request->idperiodo)
            ->whereNotIn('empleados.id_banco', $omitir)
            ->whereIn('empleados.id_departamento', $departamentos)
            ->where('empleados.tipo_de_nomina', $request->nombre_periodo)
            ->orderBy("emi.razon_social")->get();
    }
    // ---------------------------------- Reportes ----------------------------------------------------

    public function reportesDatos(Request $request)
    {
        return Empleado::select('emi.id as id_emisora', 'emi.clave_emisora', 'emi.razon_social','ru.neto_sindical as Sindical', 'ru.neto_fiscal as Fiscal', 'empleados.id', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'ru.neto_fiscal as neto', 'empleados.cuenta_bancaria', 'empleados.cuenta_bancaria2', 'empleados.cuenta_bancaria3', 'empleados.clabe_interbancaria', 'empleados.id_banco', 'empleados.id_bancario', 'empleados.tipo_cuenta', 'empleados.rfc','ban.nombre as nombre_banco','empleados.salario_diario','empleados.salario_diario_integrado','empleados.sueldo_neto')
            ->join('rutinas' . $request->ejercicio . ' as ru', 'empleados.id', 'ru.id_empleado')
            ->join('singh.bancos as ban', 'ban.banco1', 'empleados.id_banco')
            ->join('categorias as cat', 'empleados.id_categoria', 'cat.id')
            ->join('singh.registro_patronal as rp', 'cat.tipo_clase', 'rp.id')
            ->join('singh.empresas_emisoras as emi', 'rp.id_empresa_emisora', 'emi.id')
            ->where('ru.fnq_valor', 0)
            ->where('empleados.estatus', 1)
            ->where('ru.id_periodo', $request->idperiodo)
            //->whereNotIn('empleados.id_banco', $omitir)
            //->whereIn('empleados.id_departamento', $departamentos)
            ->where('empleados.tipo_de_nomina', $request->nombre_periodo)
            ->orderBy("ban.id")->get();
    }

    public function getEmisora($id_emisora, $tipo = null)
    {
        $query_emisora = "select emisora.*, b1.nombre 'b1',b2.nombre 'b2',b3.nombre 'b3', bs1.nombre 'bs1', bs2.nombre 'bs2', bs3.nombre 'bs3' from singh.asigna_empresas_emisoras asigna
                    inner join singh.empresas_emisoras emisora on emisora.id = asigna.id_empresa_e
                    left join singh.bancos b1 on b1.id = emisora.banco
                    left join singh.bancos b2 on b2.id = emisora.banco2
                    left join singh.bancos b3 on b3.id = emisora.banco3
                    left join singh.bancos bs1 on bs1.id = emisora.banco_sind
                    left join singh.bancos bs2 on bs2.id = emisora.banco_sind2
                    left join singh.bancos bs3 on bs3.id = emisora.banco_sind3
                    where emisora.id = " . $id_emisora . " and asigna.estatus = 0 and asigna.id_empresa = " . Session::get('empresa')['id'] . ";";

        $empresa_emisora = DB::connection('empresa')->select(DB::raw($query_emisora));
		 
        //dump($id_emisora,$empresa_emisora);
        if ($tipo != null) {
            //dd($empresa_emisora[0]->id);
            $emisora = array("id" => $empresa_emisora[0]->id, "razon_social" => $empresa_emisora[0]->razon_social, "rfc" => $empresa_emisora[0]->rfc, "clave_emisora" => $empresa_emisora[0]->clave_emisora);
            if ($tipo == 'Fiscal') {
                if ($empresa_emisora[0]->cuenta_bancaria != "" && $empresa_emisora[0]->cuenta_bancaria != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria;
                    $emisora['banco'] = $empresa_emisora[0]->b1;
                } else if ($empresa_emisora[0]->cuenta_bancaria2 != "" && $empresa_emisora[0]->cuenta_bancaria2 != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria2;
                    $emisora['banco'] = $empresa_emisora[0]->b2;
                } else if ($empresa_emisora[0]->cuenta_bancaria3 != "" && $empresa_emisora[0]->cuenta_bancaria3 != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria3;
                    $emisora['banco'] = $empresa_emisora[0]->b3;
                }
            } else if ($tipo == 'Sindical') {
                if ($empresa_emisora[0]->cuenta_bancaria_sind != "" && $empresa_emisora[0]->cuenta_bancaria_sind != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria_sind;
                    $emisora['banco'] = $empresa_emisora[0]->bs1;
                } else if ($empresa_emisora[0]->cuenta_bancaria_sind2 != "" && $empresa_emisora[0]->cuenta_bancaria_sind2 != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria_sind2;
                    $emisora['banco'] = $empresa_emisora[0]->bs2;
                } else if ($empresa_emisora[0]->cuenta_bancaria_sind3 != "" && $empresa_emisora[0]->cuenta_bancaria_sind3 != null) {
                    $emisora['cuenta'] = $empresa_emisora[0]->cuenta_bancaria_sind3;
                    $emisora['banco'] = $empresa_emisora[0]->bs3;
                }
            }
            return $emisora;
        }
        return $empresa_emisora[0];
    }

    public function generaXlsBanorteSPEI(Request $request)
    { // banorte spei
        cambiarBase(Session::get('base'));
        $empleados_dispersion = array();
        $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $request->idperiodo . "/");
        $FecHr = date('Y/m/d H:i:s');
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir_banco = (!empty($request->banco_omitir)) ? explode(",", $request->banco_omitir) : array();
        $omitir = (!empty($omitir_banco) && count($omitir_banco) > 0) ? $omitir_banco : array(0);
        $consecutivo = $this->getConsecutivo($request->idperiodo);
        //$nombre_banco = $this->nombreBanco($request->idbanco);

        if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            $empleados_por_emisora = $this->nominaDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO NOMINA";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            $empleados_por_emisora = $this->aguinaldoDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO AGUINALDO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'TotalSindical' : 'TotalFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {
            $empleados_por_emisora = $this->finiquitoDatos($request);
            $descripcion = "PAGO FINIQUITO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        }

        $empleadostxt = array();
        if (isset($request->emisora_id)) {
            $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
            $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);

            $clave_emisora = $emisora['clave_emisora'];
            $claveemisora = str_pad($clave_emisora, 5, "0", STR_PAD_LEFT);
            $nombre =  $request->nombre_archivo . ".txt";
            $Rutanamearchi = $folder . $nombre;
            //dump($omitir);
            //dd($empleados_por_emisora->whereNotIn('id_banco', $omitir));
            foreach ($empleados_por_emisora->where('id_emisora', $request->emisora_id)->whereNotIn('id_banco', $omitir)->toArray() as $clave_emisora => $empleado) {
              //  dump($empleado);
                if(round($empleado[$request->tipoimporte],2) != 0.00 && round($empleado[$request->tipoimporte],2) != "" && round($empleado[$request->tipoimporte],2) != null){


                    $importe = round($empleado[$request->tipoimporte],2);
                    $decimales  = explode(".", $importe);
                    if (!isset($decimales[1])) {
                        //$decimales[1] = "00";
                    } else {
                        if(strlen($decimales[1]) == 1){
                            $importe = $decimales[0] . "." .$decimales[1]. "0";
                        }
                    }

                    $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
                    $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);
                    //foreach($empleados as $empleado){
                    
                    $name = $empleado['nombre'] . " " . $empleado['apaterno'] . " " . $empleado['amaterno'];
                    //dd($empleado);exit();
                    $empleadostxt[$empleado['id']]['oper'] = $request->TipoOperacion;
                    $empleadostxt[$empleado['id']]['idBanco'] =  $empleado['id_bancario'] ;
                    $empleadostxt[$empleado['id']]['cuentaorigen'] = $emisora['cuenta'];
                    $empleadostxt[$empleado['id']]['cuentaclabe'] = $empleado[$request->cuenta];
                    $empleadostxt[$empleado['id']]['ImporteTotal'] = $importe; 
                    $empleadostxt[$empleado['id']]['referencia'] = ($request->referencia != null && $request->referencia != "") ? $request->referencia : "";
                    $empleadostxt[$empleado['id']]['descripcion'] = $descripcion;
                    $empleadostxt[$empleado['id']]['rfc'] = $emisora['rfc'];
                    $empleadostxt[$empleado['id']]['iva'] = "0";
                    $empleadostxt[$empleado['id']]['fecha_aplicacion'] = "";
                    $empleadostxt[$empleado['id']]['instruccion_de_pago'] = $this->quitar_tildes($name);
                    //$empleadostxt[$empleado['id']]  = collect($empleadostxt[$empleado['id']]);
                    
                    $empleados_dispersion[] = array(
                        'id_empleado' => $empleado['id'],
                        'id_periodo' => $request->idperiodo,
                        'importe' => $nombre_importe,
                        'fecha_guardado' => $FecHr,
                        'confirmado' => 0,
                        'archivo_generado' => 'BANORTE.XLSMAS',
                        'name_archivo' => $request->nombre_archivo . '.xlsx',
                        'ruta' => $folder,
                        'tipo_dispersion' => $request->tipo_dispersion,
                        'ejercicio' => $request->ejercicio
                    );
                }
            }
             //insertar los empleados a dispersar
             $this->crearDispersion($empleados_dispersion);
        }

        $this->generaTXT($request,$empleadostxt,$folder,$nombre,$Rutanamearchi);
       
    }

    public function generaTXT(Request $request,$empleados,$folder,$nombre,$Rutanamearchi)
    { //Banorte
        //dd($_POST);
            
        //$empleadosxls = array();
        if (isset($request->emisora_id)) {
            
            
            if (!File::exists($Rutanamearchi)) {
                File::makeDirectory($folder, $mode = 0777, true, true);
            }
            $contenido_empleados = "";
            $tot = 0;
            $totalImporte = 0;
            //dd($empleados);
            foreach ($empleados as $empleado) {
                
                foreach($empleado as $valor){
                    $contenido_empleados .= $valor ."\t";
                }
                $contenido_empleados .= PHP_EOL;
               /* $contenido_empleados.= 
                    $empleados['oper'] . "\t" . 
                    $empleados['idBanco'] . "\t" . 
                    $empleados['cuentaorigen'] . "\t" . 
                    $empleados['cuentaclabe'] . "\t" . 
                    $empleados['ImporteTotal'] . "\t" .  
                    $empleados['referencia'] . "\t" . 
                    $empleados['descripcion'] . "\t" . 
                    $empleados['rfc'] . "\t" . 
                    $empleados['iva'] . "\t" . 
                    $empleados['fecha_aplicacion'] . "\t" . 
                    $empleados['instruccion_de_pago'] . PHP_EOL;*/
            }
        }

      
     
        $file = fopen($Rutanamearchi, "w+");
        fwrite($file, $contenido_empleados);
        fclose($file);
        $this->bajarArchivo($folder, $nombre);

    }
    
    public function generaXlsBaz(Request $request)
    {
     
        $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $request->idperiodo . "/");
        $FecHr = date('Y/m/d H:i:s');
        $empleados_dispersion = array();
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir_banco = (!empty($request->banco_omitir)) ? explode(",", $request->banco_omitir) : array();
        $omitir = (!empty($omitir_banco) && count($omitir_banco) > 0) ? $omitir_banco : array(0);
        cambiarBase(Session::get('base'));


        if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            $empleados_por_emisora = $this->nominaDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE NOMINA";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            $empleados_por_emisora = $this->aguinaldoDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE AGUINALDO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'TotalSindical' : 'TotalFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {
            $empleados_por_emisora = $this->finiquitoDatos($request);
            $descripcion = "PAGO DE FINIQUITO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        }

        $empleados_por_emisora = $empleados_por_emisora->where('id_banco', self::AZTECA);
        $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        $empleadosxls = array();
        if (isset($request->emisora_id)) {
            foreach ($empleados_por_emisora->where('id_emisora', $request->emisora_id)->toArray() as $clave_emisora => $empleado) {
                if(round($empleado[$request->tipoimporte],2) != 0.00 && round($empleado[$request->tipoimporte],2) != "" && round($empleado[$request->tipoimporte],2) != null){

                    $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
                    $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);
                    $decimales  = explode(".", round($empleado[$request->tipoimporte],2));
                    if (!isset($decimales[1])) {
                        $decimales[1] = "00";
                    } else {
                        $decimales[1] = str_replace("0.", "", round("." . $decimales[1], 2));
                    }        

                    $empleadosxls[$empleado['id']]['cuentaclabe'] = " ".$empleado[$request->cuenta];
                    $empleadosxls[$empleado['id']]['ImporteTotal'] = round($empleado[$request->tipoimporte],2);
                    $empleadosxls[$empleado['id']]['concepto'] = $request->concepto;
                    $empleadosxls[$empleado['id']]['idBancario'] = $empleado['id_bancario'];
                    $empleadosxls[$empleado['id']]['nombre'] = $this->quitar_tildes($empleado['nombre'] . " " . $empleado['apaterno'] . " " . $empleado['amaterno']);

                    $empleados_dispersion[] = array(
                        'id_empleado' => $empleado['id'],
                        'id_periodo' => $request->idperiodo,
                        'importe' => $nombre_importe,
                        'fecha_guardado' => $FecHr,
                        'confirmado' => 0,
                        'archivo_generado' => 'AZTECA.XLS',
                        'name_archivo' => $request->nombre_archivo . '.xlsx',
                        'ruta' => $folder,
                        'tipo_dispersion' => $request->tipo_dispersion,
                        'ejercicio' => $request->ejercicio
                    );

                    
                }
            }
            //insertar los empleados a dispersar
            $this->crearDispersion($empleados_dispersion);
        }

        return Excel::download(

            new DispersionXlsSinCabecera(collect($empleadosxls)),
            $request->nombre_archivo . '.xlsx'
        );
    }

    public function quitar_tildes($cadena)
    {
        $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ",'Ñ',"À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
        $permitidas=    array ("a","e","i","o","u","A","E","I","O","U","n","N","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
        $texto = str_replace($no_permitidas, $permitidas ,$cadena);
        return $texto;
    }

    public function generaXlsBazMas(Request $request)
    {

        $folder = public_path("/repositorio/" . Session::get('empresa')['id'] . "/dispersiones/" . $request->idperiodo . "/");
        $FecHr = date('Y/m/d H:i:s');
        $empleados_dispersion = array();
        $departamentos = (!empty($request->cadena_departamentos)) ? explode(",", $request->cadena_departamentos) : array();
        $omitir_banco = (!empty($request->banco_omitir)) ? explode(",", $request->banco_omitir) : array();
        $omitir = (!empty($omitir_banco) && count($omitir_banco) > 0) ? $omitir_banco : array(0);
        $nombre_banco = $this->nombreBanco($request->idbanco);
        $consecutivo = $this->getConsecutivo($request->idperiodo);
        $namealias = isset($request->alias_cliente) ? $request->alias_cliente : "";

        cambiarBase(Session::get('base'));

        if ($request->tipo_dispersion == self::DISPERSION_NOMINA) {
            $empleados_por_emisora = $this->nominaDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE NOMINA";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_AGUINALDO) {
            $empleados_por_emisora = $this->aguinaldoDatos($request, $omitir, $departamentos);
            $descripcion = "PAGO DE AGUINALDO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'TotalSindical' : 'TotalFiscal';
        } else if ($request->tipo_dispersion == self::DISPERSION_FINIQUITO) {
            $empleados_por_emisora = $this->finiquitoDatos($request);
            $descripcion = "PAGO DE FINIQUITO";
            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
        }

            $nombre_importe = ($request->tipoimporte == "Sindical") ? 'NetoSindical' : 'NetoFiscal';
            $empleadosxls = array();
            if (isset($request->emisora_id)) {
                foreach ($empleados_por_emisora->where('id_emisora', $request->emisora_id)->toArray() as $clave_emisora => $empleado) {
                    //dump($empleados);
                    if(round($empleado[$request->tipoimporte],2) != 0.00 && round($empleado[$request->tipoimporte],2) != "" && round($empleado[$request->tipoimporte],2) != null){

                        $id_emisora = (isset($request->emisora_id)) ? $request->emisora_id : 0;
                        $emisora = $this->getEmisora($id_emisora, $request->tipoimporte);
                        $nombre_emisora = $this->remplazoNombreEmisora($emisora['razon_social']);
                        $rfc_emisora = $emisora['rfc'];

                        if ($request->tipoimporte == "Sindical") {
                            $nombre_emisora = str_replace(',', '', substr('SINDICATO NACIONAL DE TRABAJADORES DE CA', 0, 40));
                            $rfc_emisora = 'SNT041023818';
                        }

                        $bancoreceptor = str_pad($empleado['id_banco'], 3, "0", STR_PAD_LEFT);

                        $numerocaracteresTipocuenta = strlen($empleado[$request->cuenta]);
                        if ($numerocaracteresTipocuenta == 18) {
                            $TipoCuenta = '40';
                        } else if ($numerocaracteresTipocuenta == 16) {
                            $TipoCuenta = '03';
                        } else {
                            $TipoCuenta = '01';
                        }

                        $numerocaracteres = strlen($empleado[$request->cuenta]);
                        if ($numerocaracteres == 14 && $empleado['id_banco'] == 127) {
                            $operacion = '03';
                        } else {
                            $operacion = '01';
                        }

                        $decimales  = explode(".", round($empleado[$request->tipoimporte],2));
                        if (!isset($decimales[1])) {
                            $decimales[1] = "00";
                        } else {
                            $decimales[1] = str_replace("0.", "", round("." . $decimales[1], 2));
                        }

                        $empleadosxls[$empleado['id']]['cuentaorigen'] = $emisora['cuenta'];
                        $empleadosxls[$empleado['id']]['Nameemisora'] = $nombre_emisora;
                        $empleadosxls[$empleado['id']]['rfcEmisora'] = $rfc_emisora;
                        $empleadosxls[$empleado['id']]['NameEmpresa'] = $namealias;
                        $empleadosxls[$empleado['id']]['ImporteTotal'] =  round($empleado[$request->tipoimporte],2);
                        $empleadosxls[$empleado['id']]['MXP'] = 'MXP';
                        $empleadosxls[$empleado['id']]['bancoreceptor'] = $bancoreceptor;
                        $empleadosxls[$empleado['id']]['TipoCuenta'] = $TipoCuenta;
                        $empleadosxls[$empleado['id']]['cuentaclabe'] = $empleado[$request->cuenta];
                        $empleadosxls[$empleado['id']]['nombre'] = $this->quitar_tildes($empleado['nombre'] . " " . $empleado['apaterno'] . " " . $empleado['amaterno']);
                        $empleadosxls[$empleado['id']]['concepto'] = $request->concepto;
                        $empleadosxls[$empleado['id']]['espacio1'] = "";
                        $empleadosxls[$empleado['id']]['espacio2'] = "";
                        $empleadosxls[$empleado['id']]['espacio3'] = "";
                        $empleadosxls[$empleado['id']]['operacion'] = $operacion;

                        
                        $empleados_dispersion[] = array(
                            'id_empleado' => $empleado['id'],
                            'id_periodo' => $request->idperiodo,
                            'importe' => $nombre_importe,
                            'fecha_guardado' => $FecHr,
                            'confirmado' => 0,
                            'archivo_generado' => 'AZTECA.XLSMAS',
                            'name_archivo' => $request->nombre_archivo . '.xlsx',
                            'ruta' => $folder,
                            'tipo_dispersion' => $request->tipo_dispersion,
                            'ejercicio' => $request->ejercicio
                        );
                        
                    }
                }
                //insertar los empleados a dispersar
                $this->crearDispersion($empleados_dispersion);

            } 
        return Excel::download(
            new DispersionXlsSinCabecera(collect($empleadosxls)),
            $request->nombre_archivo . '.xlsx'
        );
    }

    public function remplazoNombreEmisora($nombre)
    {
        $n = $nombre;
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
    ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'AAAAAAACEEEEIIIIDÑOOOOOOUUUUY
    BSAAAAAAACEEEEIIIIDÑOOOOOOUUUYYBYRR';
        $n = utf8_decode($n);
        $n = strtoupper(strtr($n, utf8_decode($originales), utf8_decode($modificadas)));
        $n = substr($n, 0, 40);
        return $n;
    }

    public function remplazoNombreAlias($nombre)
    {
        $n = $nombre;
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
    ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'AAAAAAACEEEEIIIIDÑOOOOOOUUUUY
    BSAAAAAAACEEEEIIIIDÑOOOOOOUUUYYBYRR';

        $n = strtoupper(strtr($n, utf8_decode($originales), utf8_decode($modificadas)));
        $n = substr($n, 0, 30);
        return $n;
    }

    public function exportarExcel(Request $request){

        cambiarBase(Session::get('base'));
        $empleados = $this->reportesDatos($request);
        return Excel::download(new DispersionExcelExport($empleados),"Dispersion_periodo_{$request->idperiodo}_".date('d-m-Y').".xlsx");
    }
    
    public function exportarTotales(Request $request)
    {

        cambiarBase(Session::get('base'));
        $empleados = $this->reportesDatos($request);
        $periodo = $periodo = periodosNomina::find($request->idperiodo);
        return Excel::download(new DispersionTotalExport($empleados,$periodo),"Totales_{$request->idperiodo}_".date('d-m-Y').".xlsx");
    }

    public function obtenerAguinaldosPorBanco($deptos, $ejercicio, $banco)
    {
        $aguinaldos = DB::connection('empresa')->table('aguinaldo')
            ->where('ejercicio', $ejercicio)
            ->whereIn('id_empleado', function ($query) use ($deptos, $banco) {
                $query->select('id')
                    ->from(with(new Empleado)->getTable())
                    ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                    ->where('id_banco', $banco)
                    ->whereIn('id_departamento', $deptos);
            })->orderBy('id_empleado')->get()->keyBy('id_empleado');
        return $aguinaldos;
    }
    
}
