<?php 

namespace App\Http\Controllers\Procesos;

use App\Models\Parametros;
use App\Models\Prestacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\PeriodosNomina;
use App\Models\Rutina;
use App\Models\RutinaValor;
use App\Models\DetalleFormularioEncuesta;

use App\Http\Controllers\Empleados\KitBajaController;
use App\Http\Controllers\Procesos\NominaController;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use App\Exports\FiniquitoExport;
use App\Models\Demanda;
use DataTables;
use DateTime;


class CalculoFiniquitoController extends Controller
{

    protected $empleado;
    protected $SDOCompensacion = 0;
    protected $aguinaldoreal = 0;
    protected $vacacionesCompe = 0;
    protected $pvacreal = 0;
    protected $SumaObre = 0;
    protected $conlumnapercepciones;
    protected $conlumnadeducciones;
    protected $conceptoISR;
    protected $valores_calculadora = array();
    protected $claves_conceptos = array();
    protected $concepros_validados = array();
    protected $dias_vacaciones = array(
        0 => 6, // anio
        1 => 8, // segundo anio
        2 => 10, // tercer anio
        3 => 12, // cuarto anio ...
        4 => 14,
        5 => 14,
        6 => 14,
        7 => 14,
        8 => 14,
        9 => 16,
        10 => 16,
        11 => 16,
        12 => 16,
        13 => 16,
        14 => 18,
        15 => 18,
        16 => 18,
        17 => 18,
        18 => 18,
        19 => 18,
        20 => 20,
        21 => 20,
        22 => 20,
        23 => 20
    );
    /**
     * Periodo de nomina a calcular
     */
    protected $periodo;
    protected $ejercicio;
    protected $rutina;

    public function obtenerEmpleadosconFechaBajaEnPeriodo()
    {
        //obtener el periodo de baja del empleado

        return Empleado::leftJoin("periodos_nomina as pe", function ($join) {
            $join->on("empleados.tipo_de_nomina", "pe.nombre_periodo")
                ->where('empleados.fecha_baja', '!=', "0000-00-00")
                ->where('pe.fecha_inicial_periodo', '<=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                ->where('pe.fecha_final_periodo', '>=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                ->where('pe.estatus', 1);
        })
            ->select(
                "pe.*",
                "empleados.id as idempleado",
                "empleados.nombre",
                "empleados.apaterno",
                "empleados.amaterno",
                "empleados.fecha_baja as fecha_baja_empleado",
                "empleados.causa_baja",
                "empleados.baja_oficial"
            )
            ->where("empleados.estatus", 1)
            ->orderBy("empleados.fecha_baja", "ASC")->get();
    }

    public function inicio(request $request)
    {
        tienePermiso('finiquitos');
        cambiarBase(Session::get('base'));
        if ($request->ajax()) {
            $empleados = $this->obtenerEmpleadosconFechaBajaEnPeriodo();
            //dd($empleados);
            return Datatables::of($empleados)
                /* ->addColumn('fecha_baja', function ($row) {
                    return Carbon::parse($row->fecha_baja_empleado)->format('d/M/y');
                })*/

                ->addColumn('nombre_completo', function ($row) {
                    return $row->nombre . " " . $row->apaterno . " " . $row->amaterno;
                })


                ->addColumn('acciones', function ($row) {
                    $btn = "";

                    $btn = '<div class="btn-group">
                            ';

                    if (!empty($row->id)) {
                        $btn .= '<a href="#" class="btn btn-warning btn-sm mr-2 calcula" data-toggle="modal" data-target="#cerrarFiniquitoModal"
                                    data-id="' . $row->idempleado . '" 
                                    data-nombre="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                                    data-fechabaja="' . $row->fecha_baja_empleado . '"
                                    data-causa="' . $row->causa_baja . '"
                                    data-causaoficial="' . $row->baja_oficial . '"
                                    data-idperiodo="' . $row->id . '"
                                    data-nombreperiodo="' . $row->nombre_periodo . '"
                                    data-ejercicio="' . $row->ejercicio . '"
                                    ><i class="fa fa-calculator tooltip_" data-toggle="tooltip"  title="CALCULAR FINIQUITO"></i>
                                </a>';
                    } else {
                        $btn .= '<span data-toggle="tooltip" data-html="true"  title="CAPTURAR" class="tooltip_">
                        <a href="#" class="btn btn-warning btn-sm mr-2" data-toggle="modal" data-target="#capturaFiniquitoModal"
                            data-id="' . $row->idempleado . '" 
                            data-nombre="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                            data-fechabaja="' . $row->fecha_baja_empleado . '"
                            data-causa="' . $row->causa_baja . '"
                            data-causaoficial="' . $row->baja_oficial . '"
                            data-idperiodo="' . $row->id . '"
                            data-nombreperiodo="' . $row->nombre_periodo . '"
                            ><i class="fa fa-pencil-alt"></i>
                        </a>
                    </span>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })

                ->rawColumns(['acciones'])
                ->make(true);
        }
        return view('procesos.calculo-finiquito.inicio');
    }

    public function inicioHistorico(Request $request)
    {
        // dd($request);
        tienePermiso('finiquitos');
        cambiarBase(Session::get('base'));
        $kitBajaCampos = DB::connection('empresa')->table('kit_baja_campos')->get();
        $ejercicios = DB::connection('empresa')->table('ejercicios')->select('ejercicio')->orderBy("ejercicio");
        
        if ($request->ajax()) {

            $ejercicio = (!empty($request->buscar_ejercicio)) ? intval($request->buscar_ejercicio) : date('Y');
            $empleados_finiquito =  Empleado::select('per.numero_periodo', 'per.ejercicio', 'ru.id as rutina', 'per.id as idperiodo', 'empleados.id as idempleado', 'empleados.estatus', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'empleados.correo', 'empleados.fecha_baja', 'empleados.causa_baja', 'empleados.estatus_firma_finiquito', 'depa.nombre as departamento', 'ru.neto_fiscal', 'test.id as encuesta')

                ->leftJoin("periodos_nomina as per", function ($join) {
                    $join->on("empleados.tipo_de_nomina", "per.nombre_periodo")
                        ->where('per.fecha_inicial_periodo', '<=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                        ->where('per.fecha_final_periodo', '>=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                        ->where('per.estatus', 1);
                })
                ->leftJoin('departamentos as depa', 'depa.id', 'empleados.id_departamento')
                ->leftJoin('encuesta as test', 'empleados.id', 'test.id_empleado')
                ->leftJoin('rutinas' . $ejercicio . ' as ru', function ($join) {
                    $join->on('empleados.id', 'ru.id_empleado')
                        ->where('ru.id_periodo', 'per.id')
                        ->where('ru.fnq_valor', 1);
                })
                ->where('empleados.finiquitado', 1)
                ->whereIn('empleados.estatus', array(2, 20))
                ->whereYear('empleados.fecha_baja', $ejercicio)
                ->orderBy('empleados.fecha_baja')
                ->get()->keyBy('idempleado');

            $empleadosIds = $empleados_finiquito->pluck('idempleado')->toArray();
            if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($empleadosIds);
                }
            $archivosBajaEmpleados = DB::connection('empresa')
                ->table('kit_baja_info')
                ->whereIn('id_empleado', $empleadosIds)
                ->get();

            foreach ($archivosBajaEmpleados as $archivo) {
                $kitBaja[$archivo->id_empleado][$archivo->nombre_campo] = $archivo;
            }
            // dd($kitBaja);

            foreach ($empleados_finiquito as &$empleado) {
                if (isset($kitBaja[$empleado->idempleado])) {
                    $empleado->kitBaja = $kitBaja[$empleado->idempleado];
                } else {
                    $empleado->kitBaja = null;
                }
                $kit = new KitBajaController();
                $empleado->kitBajaCompleto = $kit->estaCompletoKitBaja($kitBajaCampos, $empleado->kitBaja);
            }

            //dd($empleados_finiquito);
            return Datatables::of($empleados_finiquito)
                ->addColumn('fecha_baja', function ($row) {
                    return Carbon::parse($row->fecha_baja)->format('d/M/y');
                })

                ->addColumn('nombre_completo', function ($row) {
                    return $row->nombre . " " . $row->apaterno . " " . $row->amaterno . " ";
                })

                ->addColumn('estatus_firma_finiquito', function ($row) {
                    if ($row->estatus_firma_finiquito == 0) {
                        return '<span class="badge badge-danger">En espera de confirmación</span>';
                    } else if ($row->estatus_firma_finiquito == 1) {
                        return '<span class="badge badge-success">Firmado</span>';
                    } else if ($row->estatus_firma_finiquito == 2) {
                        return '<span class="badge badge-danger">No firmado</span>';
                    }
                })
                ->addColumn('kit', function ($row) {
                    if ($row->kitBajaCompleto) {
                        return '<span class="badge badge-success">Completo</span>';
                    } else {
                        return '<span class="badge badge-danger">Incompleto</span>';
                    }
                })

                ->addColumn('acciones', function ($row) {
                    $btn = "";

                    $btn = '<div width="110px" class="text-center position-relative">
                                <button data-id="'.$row->idempleado.'" class="menubtn btn btn-warning btn-sm mr-2" alt="Opciones finiquito" title="Opciones finiquito">
                                    <i class="fas fa-list"></i>
                                </button>
                                <ul class="menu text-left">';
                    if ($row->estatus == 20) {
                        //pendiente, deshabilitado en la version 2
                        $btn .= '<a href="#" class="cerrar" data-toggle="modal" data-target="#cerrarFiniquitoModal"
                                    data-id="' . $row->idempleado . '" 
                                    data-nombreempleado="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                                    data-fechabaja="' . $row->fecha_baja . '"
                                    data-correo="' . $row->correo . '"
                                    data-encuesta="' . $row->encuesta . '"
                                    ><li>Cerrar finiquito</li></a>';
                    } else if ($row->estatus == 2 && $row->estatus_firma_finiquito == 0) {
                        $btn .= '<a href="#" class="reactivar"  data-toggle="modal" data-target="#firmaFiniquitoModal" data-id="' . $row->idempleado . '"
                                    data-nombreempleado="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                                    data-idperiodo="' . $row->idperiodo . '"
                                    data-ejercicio="' . $row->ejercicio . '"
                                    ><li>Firma finiquito</li></a>';
                    }
                    $btn .= '<a href="#" class="imprimir"
                                data-id="' . $row->idempleado . '" 
                                data-nombre="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                                data-fechabaja="' . $row->fecha_baja_empleado . '"
                                data-numeroperiodo="' . $row->numero_periodo . '"
                                data-idperiodo="' . $row->idperiodo . '"
                                data-ejercicio="' . $row->ejercicio . '"
                                data-rutina="' . $row->rutina . '"
                            ><li>Imprimir</li></a>

                            <a href="#" class="ver"
                                data-id="' . $row->idempleado . '" 
                                data-nombre="' . $row->apaterno . ' ' . $row->amaterno . ' ' . $row->nombre . '"
                                data-fechabaja="' . $row->fecha_baja_empleado . '"
                                data-causa="' . $row->causa_baja . '"
                                data-idperiodo="' . $row->idperiodo . '"
                                data-ejercicio="' . $row->ejercicio . '"
                            ><li>Ver finiquito</li></a>
                            

                            <a class="" data-toggle="modal" data-target="#archivosModal" data-return_id="finiquito"
                            data-id_empleado="' . $row->idempleado . '" ';
                    if ($row->kitBaja != null) {
                        foreach ($row->kitBaja as $campo) {
                            $btn .= ' data-file_' . $campo->nombre_campo . '="' . $campo->archivo . '"';
                        }
                    }

                    $btn .= '><li>';

                    if ($row->kitBajaCompleto) {
                        $btn .= 'Ver kit';
                    } else {
                        $btn .= 'Kit baja';
                    }

                    $btn .= '</li></a>';

                    $btn .= '</ul>
                            </div>';

                    return $btn;
                })
                ->addColumn('encuesta', function ($row) {
                    if ($row->encuesta != null && $row->encuesta != "") {
                        return  '<span class="badge badge-success">SI</span>';
                    } else {
                        return  '<span class="badge badge-danger">NO</span>';
                    }
                })
                ->rawColumns(['acciones', 'estatus_firma_finiquito', 'encuesta', 'kit'])
                ->make(true);
        }
        $buscar_ejercicio = (!empty($request->buscar_ejercicio)) ? intval($request->buscar_ejercicio) : date('Y');

        return view('procesos.calculo-finiquito.inicio-historico', compact('ejercicios', 'buscar_ejercicio', 'kitBajaCampos'));
    }

    public function guardaCapturaFiniquito(Request $request)
    {
        cambiarBase(Session::get('base'));

        if (!empty($request->idrutina)) { // tiene una fecha de baja y un registro en ejercicioXXXX
            //actualizan datos a valores de conceptos del trabajador
            $datos = $request->except(["nombreperiodo", "idperiodo", "ids_conceptos", "no_laborados", "idrutina", '_token', 'rutina_ejercicio', 'id', 'nombre', "fecha_baja_empleado", "causa_baja_empleado", "fecha_baja", "causa_baja", "baja_oficial"]);

            DB::connection('empresa')->table('rutinas' . $request->rutina_ejercicio)
                ->where('id', $request->idrutina)
                ->update($datos);

            return response()->json(['ok' => 1, 'msj' => 'captura éxitosa', 'tipo' => 1]);
        }
        return response()->json(['ok' => 1, 'msj' => 'captura éxitosa', 'tipo' => 0, 'idempleado' => $request->id]);

        return response()->json(['ok' => 0, 'msj' => 'captura no éxitosa, intente nuevamente']);
    }

    public function obtenerPeriodoDentroFechaBaja($empleado)
    {
        //obtener el periodo de baja del empleado
        return Empleado::where("empleados.id", $empleado)
            ->join("periodos_nomina as pe", function ($join) {
                $join->on("empleados.tipo_de_nomina", "pe.nombre_periodo")
                    ->where('pe.fecha_inicial_periodo', '<=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
                    ->where('pe.fecha_final_periodo', '>=', DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'));
            })->select("pe.*")->orderBy("pe.id", "DESC")->get();
    }

    public function verConceptosNominaEmpleado(Request $request)
    {
        $valores_input = array();
        $id_conceptos = array();
        cambiarBase(Session::get('base'));
        //dd($request->id);
        //traer los conceptos de finiquito
        $columnas = DB::connection('empresa')->table('conceptos_nomina')
            ->where('tipo_proceso', 0)
            ->where('estatus', 1)
            ->where('file_rool', '!=', 0)
            ->where('finiquito', 1)
            ->where('nombre_concepto', "!=", 'Sueldo')
            ->get()->keyBy('id');

        //obtener el periodo de baja del empleado
        $periodo = $this->obtenerPeriodoDentroFechaBaja($request->id);
        /* $periodo = Empleado::where("empleados.id",$request->id)
        ->join("periodos_nomina as pe",function($join){
            $join->on("empleados.tipo_de_nomina", "pe.nombre_periodo")
            ->where('pe.fecha_inicial_periodo','<=',DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'))
            ->where('pe.fecha_final_periodo','>=',DB::raw('DATE_FORMAT(empleados.fecha_baja,"%Y-%m-%d")'));            
        })->select("pe.*")->orderBy("pe.id","DESC")->get();
*/
        //debe existir un periodo de nomina de lo contrario no continua
        if ($periodo->count() > 0) {
            $ejercicio = $periodo->first()->ejercicio; //ejercicio para saber en que tabla de rutina buscar
            //dump($ejercicio, $periodo->first()->id);
            //obtener los valores a los conceptos de finiquito del trabajador
            $valores = DB::connection('empresa')
                ->table('rutinas' . $ejercicio)
                ->where('id_empleado', $request->id)
                ->where('id_periodo', $periodo->first()->id)
                ->where('fnq_valor', 1)->get()->toArray();
            // dump($valores);
            $valores_conceptos = (array)$valores[0];
            $idrutina = $valores_conceptos['id'];
            //obtener dias que no labora
            $dias_no_laborados = (!empty($valores_conceptos['dias_no_laborados'])) ? $valores_conceptos['dias_no_laborados'] : "";

            //iterar los conceptos y obtener los valores de estos ya que se muestran en los input
            foreach ($columnas as $concepto => $columna) {


                //si el concepto tiene valor se asigna este
                if (!empty($valores_conceptos['valor' . $concepto])) {
                    array_push($valores_input, array("idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => $valores_conceptos['valor' . $concepto]));
                } else { //si el concepto no tiene valor pasa entra aca 
                    if (isset($valores_conceptos['valor' . $concepto])) { //si la columna existe en la tabla
                        array_push($valores_input, array("idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => 0));
                    }
                    //si el concepto no existe en la tabla de ejercicio no se guarda en el arreglo de id_conceptos
                }
            }

            return response()->json(['ok' => 1, 'rutina_ejercicio' => $ejercicio, 'inputs' => $valores_input, 'no_laborados' => $dias_no_laborados, 'idrutina' => $idrutina]);
        } else {
            return response()->json(['ok' => 0, 'msj' => 'No hay un periodo de nomina para la fecha de baja']);
        }
    }

    public function verConceptosNominaEmpleadoEdit(Request $request)
    {
        $percepciones_input = $deducciones_input = array();
        $id_conceptos = array();
        cambiarBase(Session::get('base'));
        //dd($request->id);
        //traer los conceptos de finiquito
        $percepciones = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 0)->where('nombre_concepto', "!=", 'Sueldo')->get()->keyBy('id');
        $deducciones = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 1)->get()->keyBy('id');

        //obtener el periodo de baja del empleado
        $periodo = $this->obtenerPeriodoDentroFechaBaja($request->id);

        //debe existir un periodo de nomina de lo contrario no continua
        $ejercicio = $request->ejercicio; //ejercicio para saber en que tabla de rutina buscar
        //dump($ejercicio, $periodo->first()->id);
        //obtener los valores a los conceptos de finiquito del trabajador
        $valores = DB::connection('empresa')
            ->table('rutinas' . $ejercicio)
            ->where('id', $request->id_rutina)
            ->where('fnq_valor', 1)->get()->toArray();
        // dump($valores);
        $valores_conceptos = (array)$valores[0];
        $idrutina = $valores_conceptos['id'];
        //obtener dias que no labora
        $dias_no_laborados = (!empty($valores_conceptos['dias_no_laborados'])) ? $valores_conceptos['dias_no_laborados'] : "";

        //iterar los conceptos y obtener los valores de estos ya que se muestran en los input
        foreach ($percepciones as $concepto => $columna) {
            //si el concepto tiene valor se asigna este
            if (!empty($valores_conceptos['total' . $concepto])) {
                array_push($percepciones_input, array("clase" => "total_percepcion_fiscal", "idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => $valores_conceptos['total' . $concepto]));
            } else { //si el concepto no tiene valor pasa entra aca 
                if (isset($valores_conceptos['valor' . $concepto])) { //si la columna existe en la tabla
                    array_push($percepciones_input, array("clase" => "total_percepcion_fiscal", "idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => 0));
                }
                //si el concepto no existe en la tabla de ejercicio no se guarda en el arreglo de id_conceptos
            }
        }

        foreach ($deducciones as $concepto => $columna) {
            //si el concepto tiene valor se asigna este
            if (!empty($valores_conceptos['total' . $concepto])) {
                array_push($deducciones_input, array("clase" => "total_deduccion_fiscal", "idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => $valores_conceptos['total' . $concepto]));
            } else { //si el concepto no tiene valor pasa entra aca 
                if (isset($valores_conceptos['valor' . $concepto])) { //si la columna existe en la tabla
                    array_push($deducciones_input, array("clase" => "total_deduccion_fiscal", "idconcepto" => $concepto, "concepto" => strtoupper($columna->nombre_concepto), "valor" => 0));
                }
                //si el concepto no existe en la tabla de ejercicio no se guarda en el arreglo de id_conceptos
            }
        }

        return response()->json(['ok' => 1, 'rutina_ejercicio' => $ejercicio, 'percepciones' => $percepciones_input, 'deducciones' => $deducciones_input, 'no_laborados' => $dias_no_laborados, 'idrutina' => $idrutina]);
    }

    public function subirArchivosFiniquito(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado_id = $request->id_empleado;
        $archivos = ['err' => []];
        // dd($request->allFiles());
        // subimos los archivos
        if ($request->allFiles() && $empleado_id > 0) {
            foreach ($request->allFiles() as $id_archivo => $archivo) {
                // dd("Ver");
                $nombreArchivo = $id_archivo . '.' . $archivo->getClientOriginalExtension();
                $folder = 'storage/repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . "/kitBaja/";

                if ($archivo->move(public_path($folder), $nombreArchivo)) {

                    DB::connection('empresa')->table('kit_baja_info')->updateOrInsert(
                        [
                            'nombre_campo' => $id_archivo,
                            'id_empleado' => $empleado_id
                        ],
                        [
                            'archivo' => $nombreArchivo,
                            'fecha_creacion' => date('Y-m-d H:i:s')
                        ]
                    );
                } else {
                    $archivos['err'][] = $id_archivo;
                }
            }

            if (count($archivos['err']) > 0) {
                $msg = "En el proceso hubo errores y no se guardaron los siguientes archivos: " . implode(', ', $archivos['err']);
                $tipo = 'danger';
            } else {
                $tipo = 'success';
                $msg = 'Los archivos se subieron correctamente.';
            }

            session()->flash($tipo, $msg);

            return redirect()->route('procesos.historico');

        }

            session()->flash('danger', 'Los archivos no se subieron. Intente nuevamente.');

            return redirect()->route('procesos.historico');

    }

    public function validarEncuestaSalida(Request $request){

        
        cambiarBase(Session::get('base'));
        try{

            $response=DetalleFormularioEncuesta::where(['id_empleado'=>$request->id ,'estatus'=>3])->count();
        
        }catch(\Exception $e){
            
            dd($e);
        
        }
        return response()->json($response);
    }

    public function calculoFiniquito(Request $request)
    {
        cambiarBase(Session::get('base'));
        if (!empty($request->id_periodo_calculo) && !empty($request->id_empleado_calculo) && !empty($request->ejercicio_calculo)) {
            $ejercicio = $request->ejercicio_calculo;
            $periodo = $request->id_periodo_calculo;
            $idempleado = $request->id_empleado_calculo;

            $this->valores_calculadora['ejercicio'] = $this->ejercicio = $ejercicio;
            $rutina = DB::connection('empresa')
                ->table('rutinas' . $ejercicio)
                ->where('id_empleado', $idempleado)
                ->where('id_periodo', $periodo)
                ->where('fnq_valor', 1)->get()->keyBy('id_empleado');

            if ($rutina->count() == 0) {
                DB::connection('empresa')
                    ->table('rutinas' . $ejercicio)
                    ->insert([
                        "id_empleado" => $idempleado,
                        'id_periodo' => $periodo,
                        'fnq_valor' => 1
                    ]);

                $rutina = DB::connection('empresa')
                    ->table('rutinas' . $ejercicio)
                    ->where('id_empleado', $idempleado)
                    ->where('id_periodo', $periodo)
                    ->where('fnq_valor', 1)->get()->keyBy('id_empleado');
            }

            $this->empleado = Empleado::find($idempleado);
            $this->empleado->rutina = $rutina[$idempleado];
            $this->empleado->rutina->total_gravado = 0;
            $this->periodo = periodosNomina::where('id', $periodo)->first();
            $this->empleado->compensacion_sindical = 0;
            if (!empty($request->vacaciones_periodo_actual) && $request->vacaciones_periodo_actual != "") {
                $this->empleado->vacaciones_periodo_actual = $request->vacaciones_periodo_actual;
            }

            if (!empty($request->vacaciones_pendientes)) {
                $this->empleado->vacaciones_pendientes = $request->vacaciones_pendientes;
            }
            if (!empty($request->adelanto_aguinaldo)) {
                $this->empleado->adelanto_aguinaldo = $request->adelanto_aguinaldo;
            }

            $this->empleado->valida_dias = DB::connection('empresa')->table('rutinas' . $this->ejercicio)
                ->where('id_empleado', $this->empleado->id)
                ->where('id_periodo', $this->periodo->id)
                ->where('fnq_valor', 0)
                ->where('estatus', 2);

            //reiniciar total gravado
            DB::connection('empresa')->table('rutinas' . $this->ejercicio)
                ->where('id_empleado', $idempleado)
                ->where('id_periodo', $periodo)
                ->where('fnq_valor', 1)->update(['total_gravado' => 0]);

            //obtener los conceptos para finiquito
            $conceptos = DB::connection('empresa')->table('conceptos_nomina')
                ->where('estatus', 1)
                ->where('finiquito', 1)
                ->get()->keyBy('id');

            $this->obtenerIdsConceptos($conceptos);
            $this->AsignarFaltas();
            $this->AsignarDiasIncapacidad();

            foreach ($conceptos as $concepto) {
                //dump($concepto);
                switch (strtoupper(trim($concepto->rutinas))) {
                    case 'SDO':
                        $this->calcularSDOFiniquito();
                        //dd();
                        break;

                    case 'SDOXHORA':
                        $this->calcularSDOFiniquito();
                        break;

                    case 'IMSS':
                        $this->calcularIMSSFiniquito();
                        break;

                    case 'PRDOM':
                        //$this->calcularPRDOMFiniquito();
                        break;

                    case 'PVAC':
                        $this->calcularPVACFiniquito();
                        break;

                    case 'FAHOPAT':
                        $this->calcularFAHOPATFiniquito();
                        break;

                    case 'HEXT3':
                        $this->calcularHEXT3Finiquito();
                        break;

                    case 'HEXT2':
                        $this->calcularHEXT2Finiquito();
                        break;

                    case 'PPAGUI':
                        $this->calcularPPAGUIFiniquito();
                        break;

                    case 'INFONA':
                        $this->calcularINFONAFiniquito();
                        break;

                    case 'VACA':
                        $this->calcularVACAFiniquito();
                        break;
                    default:

                        $this->calcularDefaultConcepto($concepto);
                        break;
                }
            }

            $this->calcularTotalNetofiniquito();
            $this->calcularCompensacionfiniquito();
            $this->calcularISPTfiniquito();
            $this->calcularNeto_Fiscalfiniquito();
            $this->calcularNeto_Fiscal_Sindicalfiniquito();
        }
    }

    public function vistaCalculadora(Request $request)
    {
            cambiarBase(Session::get('base'));
	        $parametros = Parametros::first();

            $this->claves_conceptos['parametros_empresa'] = $parametros_empresa = $parametros;
            $this->empleado = Empleado::find($request->id_empleado_calculo);
            $this->periodo = periodosNomina::where('id', $request->id_periodo_calculo)->first();

            $this->ejercicio = $this->valores_calculadora['ejercicio'] = $request->ejercicio_calculo;

            $this->empleado->rutina = Rutina::where('ejercicio',$this->ejercicio)
                                            ->where('id_empleado',$request->id_empleado_calculo)
                                            ->where('id_periodo', $request->id_periodo_calculo)
                                            ->where('fnq_valor', 1)
                                            ->with('valores_conceptos')
                                            ->first();

            $validaciones = $this->validarConceptosDefault();
       		
            if($validaciones['errores'] > 0){
                $validacion = $validaciones['validacion'];
                $empleado_validaciones = $validaciones['empleado_validaciones'];
                $validacion_impuestos = $validaciones['validacion_impuestos'];
                $errores = $validaciones['errores'];
                return view('procesos.calculo-finiquito.validar-conceptos', compact('validacion_impuestos','validacion','empleado_validaciones', 'errores'));
            }

            $this->concepros_validados = $validaciones['validacion'];

            $empleado = $this->empleado;
            $periodo = $this->periodo;
            $fecha_inicial_periodo  = Carbon::parse($periodo->fecha_inicial_periodo);
            $fecha_baja_final       = Carbon::parse($empleado->fecha_baja);
            $dias_pagados           = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;
            $fecha_antiguedad_final = Carbon::parse($empleado->fecha_antiguedad);
            $anios_antiguedad       = $fecha_antiguedad_final->diffInYears($fecha_baja_final);

            if ($empleado->fecha_alta > $periodo->fecha_inicial_periodo) {
                $fecha_alta = Carbon::parse($empleado->fecha_alta);
                $dias_lab = $fecha_alta->diffInDays($fecha_baja_final) + 1;
            } else {
                $dias_lab = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;
            }

            if ($anios_antiguedad > 0) {
                $fechacontabilizar = Carbon::parse($this->ejercicio . '-' . $fecha_antiguedad_final->format('m') . '-' . $fecha_antiguedad_final->format('d'));
                if ($fechacontabilizar > $fecha_baja_final) {
                    $fechacontabilizar = Carbon::parse(($this->ejercicio - 1) . '-' . $fecha_antiguedad_final->format('m') . '-' . $fecha_antiguedad_final->format('d'));
                }
            } else {
                $fechacontabilizar = Carbon::parse($empleado->fecha_antiguedad_final);
            }

            $fcontabilizar = $fechacontabilizar->format('Y-m-d');
            $diaspagar = $fecha_baja_final->diffInDays($fechacontabilizar) + 1;
            $parametros_empresa      = $parametros[0];
            $claves_conceptos = $this->claves_conceptos;
            $valores_calculadora = $this->valores_calculadora;

            $rutina = DB::connection('empresa')
                    ->table('rutinas' . $this->ejercicio)
                    ->where('id_empleado', $request->id_empleado_calculo)
                    ->where('id_periodo', $request->id_periodo_calculo)
                    ->where('fnq_valor', 1)->get()->keyBy('id_empleado');

            if ($rutina->count() == 0) {
                    DB::connection('empresa')
                        ->table('rutinas' . $this->ejercicio)
                        ->insert([
                            "id_empleado" => $request->id_empleado_calculo,
                            'id_periodo' => $request->id_periodo_calculo,
                            'fnq_valor' => 1
                        ]);

                    $rutina = DB::connection('empresa')
                        ->table('rutinas' . $this->ejercicio)
                        ->where('id_empleado', $request->id_empleado_calculo)
                        ->where('id_periodo', $request->id_periodo_calculo)
                        ->where('fnq_valor', 1)->get()->keyBy('id_empleado');
            }

            $this->empleado->rutina  = $rutina[$request->id_empleado_calculo];

            return view(
                'procesos.calculo-finiquito.calculadora-finiquito',
                compact('valores_calculadora',
                'claves_conceptos', 'parametros_empresa', 
                'dias_lab', 'diaspagar', 'fechacontabilizar', 
                'fcontabilizar', 'anios_antiguedad', 
                'empleado', 'periodo',
                'dias_pagados',
            ))->with('tipo_alerta', 'success')
                ->with('mensaje', 'El finiquito se calculó automaticamente con éxito');
    }

    protected function calcularSDOFiniquito()
    {

        $periodo = $this->periodo;
        //dump($periodo);
        $sueldo = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'SUELDO')->where('estatus', 1)->first();
        //dump($sueldo); 1
        $dia_baja = date('d', strtotime($this->empleado->fecha_baja));
        $mes_baja = date('m', strtotime($this->empleado->fecha_baja));
        $ano_baja = date('Y', strtotime($this->empleado->fecha_baja));

        $dia_baja = ($dia_baja == 31) ? 30 : $dia_baja;
        $fecha_baja = $ano_baja . '-' . $mes_baja . "-" . $dia_baja;

        // dd($this->empleado);
        /* pendiente febrero

        $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));
        $ano_final_periodo = date('Y', strtotime($periodo->fecha_final_periodo));
        */

        //dd($this->empleado->rutina);

        $colTotalSdo   = 'total' . $sueldo->id;
        $colExcentoSdo = 'excento' . $sueldo->id;
        $colGravadoSdo = 'gravado' . $sueldo->id;


        if ($periodo->especial || ($this->empleado->tipo_sindical == 1 && $this->empleado->tipo_fiscal == 0)) {

            $this->empleado->rutina->total_gravado     = 0;
            $this->empleado->rutina->$colTotalSdo      = 0;
            $this->empleado->rutina->$colExcentoSdo    = 0;
            $this->empleado->rutina->$colGravadoSdo    = 0;
            $this->empleado->rutina->sdo_faltas        = 0;
            $this->empleado->rutina->sdo_incapacidades = 0;
            $this->empleado->rutina->incapacidades     = 0;

            $this->SDOCompensacion = 0;
            //------------------------------------------------------------------------------------------
            $this->claves_conceptos['sueldo']['id'] = $sueldo->id;
            $this->claves_conceptos['sueldo']['valor'] = 0;
            $this->claves_conceptos['sueldo']['compensacion'] = 0;
            $this->valores_calculadora['dias_laborados'] = 0;
            //$this->empleado->rutina->total_gravado     = $resultaGravado;
            $this->empleado->rutina->$colTotalSdo      = 0;
            $this->empleado->rutina->$colExcentoSdo    = 0;
            $this->empleado->rutina->$colGravadoSdo    = 0;
            $this->empleado->rutina->sdo_faltas        = 0;
            $this->empleado->rutina->sdo_incapacidades = 0;
            $this->empleado->rutina->incapacidades     = 0;
        } else {
            // dd($this->empleado->rutina);
            $faltas = $this->empleado->faltas;
            //   dump($faltas);
            $dias_incapacidad = $this->empleado->dias_incapacidad;

            if ($this->empleado->valida_dias->count() > 0) {
                $dias_lab = 0;
            } else {
                if ($this->empleado->fecha_alta > $periodo->fecha_inicial_periodo) {

                    //**pendiente verificar febrero
                    /*  $fin_del_periodo = $periodo->fecha_final_periodo;
                    if (intval($mes_final_periodo) == 2 && intval($dia_final_periodo) > 27) {
                        $fin_del_periodo = date('Y-m-t', strtotime($periodo->fecha_inicial_periodo));
                    }*/

                    $fecha_alta = Carbon::parse($this->empleado->fecha_alta);
                    $fecha_baja_final = Carbon::parse($fecha_baja);
                    $dias_lab = $fecha_alta->diffInDays($fecha_baja_final) + 1;
                } else {

                    $fecha_inicial_periodo = Carbon::parse($periodo->fecha_inicial_periodo);
                    $fecha_baja_final = Carbon::parse($fecha_baja);
                    $dias_lab = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;
                    //dump($dias_lab);
                }
            }
            $dias_laborables = ($dias_lab - ($dias_incapacidad + $faltas));



            //dump($dias_laborables, $periodo->dias_periodo , $dias_incapacidad , $faltas);

            $SDO              = round($this->empleado->salario_diario * $dias_laborables, 2);
            $SDOFaltas        = $this->empleado->salario_diario * $faltas;
            $SDOIncapacidades = $this->empleado->salario_diario * $dias_incapacidad;
            $partegravada     = $SDO;
            $resultaGravado   = round(floatval($this->empleado->rutina->total_gravado) + $partegravada, 2);
            $parteExcenta     = 0;

            //dump($this->empleado->salario_diario, $dias_laborables, $SDO, $SDOFaltas , $SDOIncapacidades ,$this->empleado->rutina->total_gravado, $resultaGravado);

            // No se ocupa ** pendiente de verificar----------------------------------------------------------------------------
            // $parametros_empresa = Session::get('empresa.parametros')[0];

            $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();

            //dd($parametros_empresa['tipo_nomina']);
            if ($parametros_empresa->tipo_nomina == 'Sindical' || $parametros_empresa->tipo_nomina == 'sindical') {
                if ($this->empleado->tipo_sindical == 0 && $this->empleado->tipo_fiscal == 1) {

                    $SDOCompensacion = 0;
                } else if ($this->empleado->tipo_sindical == 1 && $this->empleado->tipo_fiscal == 1) {

                    $salreal = $this->empleado->salario_digital;

                    // dd($salreal,$dias_laborables, $SDO);
                    $SDOCompensacion = ($salreal * $dias_laborables) - $SDO;
                } else if ($this->empleado->tipo_sindical == 1 && $this->empleado->tipo_fiscal == 0) {

                    $salreal = $this->empleado->salario_digital;
                    $SDOCompensacion = ($salreal * $dias_laborables) - $SDO;
                }
            } else if ($parametros_empresa->tipo_nomina == 'Fiscal' || $parametros_empresa->tipo_nomina == 'fiscal') {
                $SDOCompensacion = 0;
            }
            //dd($SDOCompensacion, $this->empleado->rutina->total_gravado, $resultaGravado);
            $this->SDOCompensacion = $SDOCompensacion;
            //------------------------------------------------------------------------------------------
            $this->claves_conceptos['sueldo']['id'] = $sueldo->id;
            $this->claves_conceptos['sueldo']['valor'] = $SDO;
            $this->claves_conceptos['sueldo']['compensacion'] = $SDOCompensacion;
            $this->valores_calculadora['dias_laborados'] = $dias_laborables;
            //$this->empleado->rutina->total_gravado     = $resultaGravado;
            $this->empleado->rutina->$colTotalSdo      = $SDO;
            $this->empleado->rutina->$colExcentoSdo    = $parteExcenta;
            $this->empleado->rutina->$colGravadoSdo    = $partegravada;
            $this->empleado->rutina->sdo_faltas        = $SDOFaltas;
            $this->empleado->rutina->sdo_incapacidades = $SDOIncapacidades;
            $this->empleado->rutina->incapacidades     = $dias_incapacidad;
            // dd($this->empleado->rutina->total_gravado,$this->empleado->rutina->$colTotalSdo,$this->empleado->rutina->$colExcentoSdo);
        }


        //dd($this->empleado->rutina);
    }

    protected function calcularIMSSFiniquito()
    {
        //protected function IMSS() {
        $this->claves_conceptos['imss']['calculoIMSS'] = $calculoIMSS = Session::get('empresa')['calculo_imss'];
        $this->claves_conceptos['imss']['diasIMSS'] = $diasIMSS = Session::get('empresa')['dias_imss'];
        $periodo = $this->periodo;
        //dump($calculoIMSS,$diasIMSS);
        // Concepto Imss
        $concepto_imss = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'IMSS')->where('estatus', 1)->first();
        $this->claves_conceptos['imss']['id'] = $concepto_imss->id;

        if ($periodo->especial) {

            $this->empleado->rutina->dias_imss = $diasIMSS;
            $this->empleado->rutina->{'total' . $concepto_imss->id} = 0;
            $this->empleado->rutina->cuota_fija = 0;
            $this->empleado->rutina->exce_pa = 0;
            $this->empleado->rutina->exce_ob = 0;
            $this->empleado->rutina->pre_dine_obre = 0;
            $this->empleado->rutina->pre_dine_patro = 0;
            $this->empleado->rutina->gas_medi_patro = 0;
            $this->empleado->rutina->gas_medi_obre = 0;
            $this->empleado->rutina->riesgo_trabajo = 0;
            $this->empleado->rutina->inva_vida_patro = 0;
            $this->empleado->rutina->inva_vida_obre = 0;
            $this->empleado->rutina->guarde_presta = 0;
            $this->empleado->rutina->sar_patron = 0;
            $this->empleado->rutina->infonavit_patro = 0;
            $this->empleado->rutina->censa_vejez_patron = 0;
            $this->empleado->rutina->censa_vejez_obre = 0;
            $this->empleado->rutina->censa_vejez_obre_patronal = 0;
            return;
        }

        $this->claves_conceptos['parametros_empresa'] = $parametros_empresa = Session::get('empresa.parametros')[0];
        $uma                     = $parametros_empresa['uma'];
        $salario_base            = $parametros_empresa['salario_minimo'];
        $cuota_fija              = $parametros_empresa['cuota_fija'];
        $excedente_patro         = $parametros_empresa['excedente_patro'];
        $excedente_obrera        = $parametros_empresa['excedente_obrera'];
        $provision_obrero        = $parametros_empresa['provision_obrero'];
        $prestaciones_patronal   = $parametros_empresa['prestaciones_patronal'];
        $prestaciones_obrera     = $parametros_empresa['prestaciones_obrera'];
        $gastos_medi_patronal    = $parametros_empresa['gastos_medi_patronal'];
        $gastos_medi_obrera      = $parametros_empresa['gastos_medi_obrera'];
        $invalidez_patronal      = $parametros_empresa['invalidez_patronal'];
        $invalidez_obrera        = $parametros_empresa['invalidez_obrera'];
        $guarderia_presta_social = $parametros_empresa['guarderia_presta_social'];


        //dump($parametros_empresa);
        // Incapacidades **
        $query_incapacidades = "SELECT sum(dias) as dias, tipo_aplicacion  
            from incapacidades where periodo = $periodo->id and estatus = 1 
            and year(fecha_inicio_incapacidad)='$periodo->ejercicio'
            and id_empleado = {$this->empleado->id} ";
        $incapacidades = DB::connection('empresa')->select($query_incapacidades);
        //dd($incapacidades);

        if (count($incapacidades) > 0) {
            foreach ($incapacidades as $incapacidad) {
                $this->empleado->tipo_aplicacion = strtolower($incapacidad->tipo_aplicacion);
            }
        }

        // $empleadosStr = implode(',', $this->empleados->pluck('id')->toArray());

        // Prima de riesgo ----------------------------------------------------------
        $queryPrimaRiesgo = "SELECT regpa.porcentaje_prima as p_prima 
            from empleados em join categorias cat on em.id_categoria = cat.id 
            inner join singh.registro_patronal regpa on cat.tipo_clase = regpa.id 
            where em.id = {$this->empleado->id} and cat.estatus=1 and regpa.estatus=1";

        $primas_riesgo = DB::connection('empresa')->select($queryPrimaRiesgo);

        $this->claves_conceptos['imss']['primas_riesgo'] = 0;
        if ($primas_riesgo != null) {
            foreach ($primas_riesgo as $prima) {
                $this->claves_conceptos['imss']['primas_riesgo'] = $this->empleado->prima_riesgo = ($prima->p_prima / 100);
            }
        }
        //dd($this->empleado->prima_riesgo);
        //----------------------------------------------------------------------------

        $faltas           = $this->empleado->faltas;
        $dias_incapacidad = $this->empleado->dias_incapacidad;
        $fin_del_periodo = $periodo->fecha_final_periodo;

        $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));

        if ($this->empleado->valida_dias->count() > 0) {
        } else {
            if ($this->empleado->fecha_alta > $periodo->fecha_inicial_periodo) {

                if (intval($mes_final_periodo) == 2 && intval($dia_final_periodo) > 15) {
                    $fin_del_periodo = date('Y-m-t', strtotime($periodo->fecha_inicial_periodo));
                }

                $fecha_final_periodo = Carbon::parse($fin_del_periodo);
                $fecha_alta = Carbon::parse($this->empleado->fecha_alta);

                $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
                // $dias_lab = $fecha_final_periodo->diffInDays($fecha_alta) + 1; // dias laborados
                $dias_lab = $fecha_alta->diffInDays($fecha_baja) + 1;

                $dias_falta = $dias_lab - $faltas - $dias_incapacidad;
                $dias = ($dias_incapacidad == 15 && $dias_lab == 16) ? 0 : $dias_falta;
                $dias_patron = ($dias_incapacidad == 15 && $dias_lab == 16) ? 0 : $dias_lab;

                $fecha_alta = Carbon::parse($this->empleado->fecha_alta);
                $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
                $dias_naturales_periodo = $fecha_baja->diffInDays($fecha_alta) + 1;


                $dias_naturales = (intval($this->empleado->rutina->dias_imss) <=  0) ? ($dias_naturales_periodo - $dias_incapacidad) : intval($this->empleado->rutina->dias_imss);
                if ($dias_incapacidad == 15 && $dias_lab == 16) $dias_naturales = 0;

                $dias_infonavit = ($this->empleado->tipo_aplicacion == 'bimestral') ? $dias_naturales_periodo : ($dias_naturales_periodo - $dias_incapacidad);
            } else {

                /*  $fecha_final_periodo = Carbon::parse($periodo->fecha_final_periodo);
                        $fecha_inicial_periodo = Carbon::parse($periodo->fecha_inicial_periodo);
                        $di = $fecha_final_periodo->diffInDays($fecha_inicial_periodo) + 1;*/

                $fecha_inicial_periodo = Carbon::parse($periodo->fecha_inicial_periodo);
                $fecha_baja_final = Carbon::parse($this->empleado->fecha_baja);
                $dias_lab = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;


                $dias = ($dias_incapacidad == 15 && $dias_lab == 16) ? 0 : ($dias_lab - $faltas - $dias_incapacidad);
                $dias_patron = ($dias_incapacidad == 15 && $dias_lab == 16) ? 0 : $dias_lab;

                $dias_naturales = $dias_lab;


                //   $dias_naturales = (intval($this->empleado->rutina->dias_imss) <=  0) ? ($dias_naturales_periodo - $dias_incapacidad) : intval($this->empleado->rutina->dias_imss);

                /* if($this->empleado->demanda_activa == 1){
                            $dias_infonavit = $dias_naturales_periodo;
                        } else { 
                            $dias_infonavit = ($this->empleado->tipo_aplicacion == 'bimestral') ? $dias_naturales_periodo : ($dias_naturales_periodo - $dias_incapacidad);
                        }
        
                        if($dias_incapacidad == 15 && $dias_lab == 16) $dias_naturales = 0;*/
            }
        }

        $salario_dia_inte = round($this->empleado->salario_diario_integrado, 2);

        $resu_cuota_fija = $uma * $dias_patron * $cuota_fija;
        $resu_cuota_fija_naturales = $uma * $dias_naturales * $cuota_fija;

        //  dump($calculoIMSS,$uma, $dias_patron, $cuota_fija,$dias_naturales);exit();
        if (strtoupper($calculoIMSS) == 'UMA') {

            if ($salario_dia_inte < ($uma * 3)) {
                $exce_patro = 0;
                $exce_patro_naturales = 0;
                $exce_obrera = 0;
            } else {
                $exce_patro = ($salario_dia_inte - (3 * $uma)) * $dias_patron * $excedente_patro;
                $exce_patro_naturales = ($salario_dia_inte - (3 * $uma)) * $dias_naturales * $excedente_patro;
                $exce_obrera = ($salario_dia_inte - (3 * $uma)) * $dias * $excedente_obrera;
            }
        } elseif (strtoupper($calculoIMSS) == 'SALARIODIARIO') {

            $sal_base_cotizacion = $dias_naturales * round($salario_dia_inte, 2);
            $smg_periodo = $salario_base * $periodo->dias_periodo * 3;

            if ($sal_base_cotizacion > $smg_periodo) {

                $exce_patro = (($sal_base_cotizacion - $smg_periodo) * $excedente_patro) * $dias_patron;
                $exce_patro_naturales = (($sal_base_cotizacion - $smg_periodo) * $excedente_patro) * $dias_naturales;
                $exce_obrera = ($salario_dia_inte - (3 * $uma)) * $dias * $excedente_obrera;
            } else {

                $exce_patro = 0;
                $exce_patro_naturales = 0;
                $exce_obrera = 0;
            }
        }

        $pre_patronal               = $salario_dia_inte * $dias_patron * $prestaciones_patronal;
        $pre_patronal_naturales     = $salario_dia_inte * $dias_naturales * $prestaciones_patronal;
        $pre_obrera                 = $salario_dia_inte * $dias * $prestaciones_obrera;
        $gastos_patronal            = $salario_dia_inte * $dias_patron * $gastos_medi_patronal;
        $gastos_patronal_naturales  = $salario_dia_inte * $dias_naturales * $gastos_medi_patronal;
        $gastos_obrera              = $salario_dia_inte * $dias * $gastos_medi_obrera;
        $riesgo_trabajo             = $salario_dia_inte * $dias_patron * $this->empleado->prima_riesgo;
        $riesgo_trabajo_naturales   = $salario_dia_inte * $dias_naturales * $this->empleado->prima_riesgo;
        $inva_patronal              = $salario_dia_inte * $dias_patron * $invalidez_patronal;
        $inva_patronal_natural      = $salario_dia_inte * $dias_naturales * $invalidez_patronal;
        $inva_obrera                = $salario_dia_inte * $dias * $invalidez_obrera;
        $guarde_social              = $salario_dia_inte * $dias_patron * $guarderia_presta_social;
        $guarde_social_naturales    = $salario_dia_inte * $dias_naturales * $guarderia_presta_social;
        $sar_patron                 = $salario_dia_inte * 0.02 * $dias_patron;
        //$sar_patron_naturales       = $salario_dia_inte * 0.02 * $dias_infonavit;
        $sar_patron_naturales       = $salario_dia_inte * 0.02 * $dias_naturales;
        //$infonavit_patron           = $salario_dia_inte * 0.05 * $dias_infonavit;
        $infonavit_patron           = $salario_dia_inte * 0.05 * $dias_patron;
        //$infonavit_patron_naturales = $salario_dia_inte * 0.05 * $dias_infonavit;
        $infonavit_patron_naturales = $salario_dia_inte * 0.05 * $dias_naturales;
        /*
                $CESANTIAYVEJEZ             = $salario_dia_inte * 0.03150 * $dias_infonavit;
                $CESANTIAYVEJEZnaturales    = $salario_dia_inte * 0.03150 * $dias_infonavit;
                $CESANTIAYVEJEZObreraN      = $salario_dia_inte * 0.01125 * $dias_infonavit;*/
        $CESANTIAYVEJEZ             = $salario_dia_inte * 0.03150 * $dias_patron;
        $CESANTIAYVEJEZnaturales    = $salario_dia_inte * 0.03150 * $dias_naturales;
        $CESANTIAYVEJEZObrera       = $salario_dia_inte * 0.01125 * $dias;
        $CESANTIAYVEJEZObreraN      = $salario_dia_inte * 0.01125 * $dias_naturales;
        $CESANTIAYVEJEZ             = $CESANTIAYVEJEZ + $CESANTIAYVEJEZObrera;
        $CESANTIAYVEJEZnaturales               = $CESANTIAYVEJEZnaturales + $CESANTIAYVEJEZObreraN;

        $CESANTIAYVEJEZObreraPatronal          = $salario_dia_inte * 0.01125 * $dias_patron;
        $CESANTIAYVEJEZObreraPatronalNaturales = $salario_dia_inte * 0.01125 * $dias_naturales;

        $SumaPatronal = $resu_cuota_fija + $exce_patro + $pre_patronal + $gastos_patronal + $inva_patronal + $guarde_social + $riesgo_trabajo + $sar_patron + $infonavit_patron + $CESANTIAYVEJEZ;

        $SumaObrera = $exce_obrera + $pre_obrera + $gastos_obrera + $inva_obrera + $CESANTIAYVEJEZObrera;
        $SumaObre   = round($SumaObrera, 2);
        $Subtotal   = $SumaPatronal + $SumaObrera;

        $this->empleado->rutina->dias_imss                 = $dias_naturales;
        $this->empleado->rutina->{'total' . $concepto_imss->id}  = $SumaObre;
        $this->claves_conceptos['imss']['cuota_fija'] = $this->empleado->rutina->cuota_fija = round($resu_cuota_fija_naturales, 6);
        $this->empleado->rutina->exce_pa                   = $exce_patro_naturales;
        $this->empleado->rutina->exce_ob                   = $exce_obrera;
        $this->empleado->rutina->pre_dine_obre             = $pre_obrera;
        $this->empleado->rutina->pre_dine_patro            = $pre_patronal_naturales;
        $this->empleado->rutina->gas_medi_patro            = $gastos_patronal_naturales;
        $this->empleado->rutina->gas_medi_obre             = round($gastos_obrera, 6);
        $this->empleado->rutina->riesgo_trabajo            = round($riesgo_trabajo_naturales, 6);
        $this->empleado->rutina->inva_vida_patro           = round($inva_patronal_natural, 6);
        $this->empleado->rutina->inva_vida_obre            = round($inva_obrera, 6);
        $this->empleado->rutina->guarde_presta             = round($guarde_social_naturales, 6);
        $this->empleado->rutina->sar_patron                = round($sar_patron_naturales, 6);
        $this->empleado->rutina->infonavit_patro           = round($infonavit_patron_naturales, 6);
        $this->empleado->rutina->censa_vejez_patron         = round($CESANTIAYVEJEZnaturales, 6);
        $this->empleado->rutina->censa_vejez_obre          = round($CESANTIAYVEJEZObrera, 6);
        $this->empleado->rutina->censa_vejez_obre_patronal = round($CESANTIAYVEJEZObreraPatronalNaturales, 6);
        $this->SumaObre                                    = $SumaObre;


        //dd($this->empleado->rutina);

        // }

    }

    public function calcularPVACFiniquito()
    {
        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();

        //id prima vacacioinal
        $concepto_prima_vacacional = DB::connection('empresa')->table('conceptos_nomina')->select('id')
            ->where(function ($query) {
                $query->where('nombre_concepto', 'PRIMA VACACIONAL')
                    ->orWhere('nombre_concepto', 'PRIMA VACACIONAL F');
            })
            ->where('estatus', 1)->where('rutinas', 'PVAC')
            ->where(function ($query) {
                $query->where('file_rool', '<=', 249)
                    ->orWhere('file_rool', '!=', 0);
            })->first();

        $prestaciones = DB::connection('empresa')->table('prestaciones')->where('id', $this->empleado->id_prestacion)->first();

        $anio_fecha_alta = date('Y', strtotime($this->empleado->fecha_alta));
        $mes_fecha_alta = date('m', strtotime($this->empleado->fecha_alta));
        $mes_fecha_baja = date('m', strtotime($this->empleado->fecha_baja));

        $salDiario               = $this->empleado->salario_diario;
        $Vacaciones              = $this->empleado->dias_vacaciones;
        $SalDigital              = floatval($this->empleado->salario_digital);
        $primavaca               = $this->empleado->porcentaje_prima;
        $prvaca                  = floatval("0." . $primavaca);
        $this->valores_calculadora['prima'] = $prvaca;
        $uma                     = $parametros_empresa->uma;
        $TotalGrava              = $this->empleado->rutina->total_gravado;
        $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
        $fecha_alta = Carbon::parse($this->empleado->fecha_antiguedad);
        $anios_antiguedad = $fecha_baja->diffInYears($fecha_alta);
        $dias_vacaciones = $this->dias_vacaciones[$anios_antiguedad];

        if ($anios_antiguedad > 0) {
            $fechacontabilizar = Carbon::parse($this->ejercicio . '-' . $fecha_alta->format('m') . '-' . $fecha_alta->format('d'));
            if ($fechacontabilizar > $fecha_baja) {
                $fechacontabilizar = Carbon::parse(($this->ejercicio - 1) . '-' . $fecha_alta->format('m') . '-' . $fecha_alta->format('d'));
            }
        } else {
            $fechacontabilizar = Carbon::parse($this->empleado->fecha_alta);
        }
        $diaspagar = $fecha_baja->diffInDays($fechacontabilizar) + 1;

        $diasvaca = round(($diaspagar * $dias_vacaciones) / 365, 2);
        $prima = $diasvaca * $prvaca;
        $pvac = round($salDiario * $prima, 2);
        $pvacreal = round(($diasvaca * $SalDigital) * $prvaca, 2);
        $pvacreal = $pvacreal - $pvac;

        if ($pvac > ($uma * 15)) {
            $parteExenta = $uma * 15;
            $parteGravada = $pvac - ($uma * 15);
        } else {
            $parteExenta = round($pvac, 2);
            $parteGravada = 0;
        }

        $resultGravable = $TotalGrava + $parteGravada;
        $this->empleado->rutina->total_gravado = $resultGravable;
        $this->empleado->rutina->{'total' . $concepto_prima_vacacional->id} = $pvac;
        $this->empleado->rutina->{'excento' . $concepto_prima_vacacional->id} = $parteExenta;
        $this->empleado->rutina->{'gravado' . $concepto_prima_vacacional->id} = $parteGravada;
        $this->pvacreal = $pvacreal;
        $this->empleado->compensacion_sindical += $pvacreal;
        /**------------------------------------------------------------------------- */

        $this->claves_conceptos['prima']['valor'] = $pvac;
        $this->claves_conceptos['prima']['id'] = $concepto_prima_vacacional->id;
        $this->claves_conceptos['prima']['compensacion'] = $pvacreal;
    }

    public function calcularFAHOPATFiniquito()
    {

        $periodo = $this->periodo;
        // $parametros_empresa = Session::get('empresa.parametros')[0];

        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();

        $concepto_fahorro = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'FONDO DE AHORRO PATRON')->where('estatus', 1)->first();

        $IdFondoAhorro = ($concepto_fahorro != null) ? intval($concepto_fahorro->id) : 0;

        $Porcentaje          = '.' . Session::get('empresa')['porcentaje_fondo'];
        $dia_inicial_periodo = date('d', strtotime($periodo->fecha_inicial_periodo));
        $dia_final_periodo   = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo   = date('m', strtotime($periodo->fecha_final_periodo));
        $ano_final_periodo   = date('Y', strtotime($periodo->fecha_final_periodo));


        $incapacidades = $this->empleado->dias_incapacidad;
        $FechaFinal    = $periodo->fecha_final_periodo;
        $faltas        = $this->empleado->faltas;

        if ($this->empleado->fecha_alta > $periodo->fecha_inicial_periodo) {
            if ($dia_final_periodo == 28 || $dia_final_periodo == 29 || $dia_final_periodo == 31)
                $FechaFinal = $ano_final_periodo . '-' . $mes_final_periodo . '-30';

            if ($this->empleado->valida_dias->count() > 0) {
                $diaspagar = 0;
            } else {
                $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
                $fecha_alta = Carbon::parse($this->empleado->fecha_alta);
                $diaspa     = $fecha_baja->diffInDays($fecha_alta);
            }

            $diaspagar = $diaspa - $faltas - $incapacidades;
            $FAHOPAT   = $this->empleado->salario_diario * $diaspagar * $Porcentaje;

            $this->empleado->rutina->{'total' . $IdFondoAhorro} = $FAHOPAT;
        } else {
            if ($this->empleado->valida_dias->count() > 0) {
                $diaspagar = 0;
            } else {
                $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
                $fecha_inicial = Carbon::parse($periodo->fecha_inicial_periodo);
                $diaspa     = $fecha_baja->diffInDays($fecha_inicial);
            }

            $diaspagar = $diaspa - $faltas - $incapacidades;
            $FAHOPAT   = $this->empleado->salario_diario * $diaspagar * $Porcentaje;

            //$data = ($periodo->especial == 1) ? 0 : $FAHOPAT;

            $this->empleado->rutina->{'total' . $IdFondoAhorro} = $FAHOPAT;
        }

        //echo $IdFondoAhorro;
        //dd($this->empleado->rutina);
    }

    protected function calcularHEXT3Finiquito()
    {

        $periodo = $this->periodo;
        $concepto_sueldo = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'SUELDO')->where('estatus', 1)->first();

        $idsuel = ($concepto_sueldo != null) ? intval($concepto_sueldo->id) : 0;

        $concepto_hrs_ex_3ples = DB::connection('empresa')->table('conceptos_nomina')->select('id')
            ->where('nombre_concepto', 'HORAS EXTRAS TRIPLES')->where('estatus', 1)->first();

        $idhoraExtTriple = ($concepto_hrs_ex_3ples != null) ? intval($concepto_hrs_ex_3ples->id) : 0;

        // $parametros_empresa = Session::get('empresa.parametros')[0];


        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();
        if ($idhoraExtTriple > 0) {


            $salDiario  = (!empty($this->empleado->salario_diario)) ?: 0;
            $colHrEx3   = 'valor' . $idhoraExtTriple;
            $horaTriple = (!empty($this->empleado->rutina->$colHrEx3)) ?: 0;

            $hext3        = ($salDiario / 8 * 3) * $horaTriple;
            $partegravada = $hext3;
            $parteexenta  = 0;

            $TotalGrava  = $this->empleado->rutina->total_gravado;
            $TotalGravar = $partegravada + $TotalGrava;

            $this->empleado->rutina->total_gravado = $TotalGravar;
            $this->empleado->rutina->{'total' . $idhoraExtTriple} = $hext3;
            $this->empleado->rutina->{'excento' . $idhoraExtTriple} = $parteexenta;
            $this->empleado->rutina->{'gravado' . $idhoraExtTriple} = $partegravada;
        }
        //dd($this->empleado->rutina);
    }

    protected function calcularHEXT2Finiquito()
    {

        $periodo = $this->periodo;
        $concepto_hrs_ex_2bles = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'HORAS EXTRAS DOBLES')->where('estatus', 1)->first();

        $idhoraExtDoble = ($concepto_hrs_ex_2bles != null) ? intval($concepto_hrs_ex_2bles->id) : 0;

        // $parametros_empresa = Session::get('empresa.parametros')[0];

        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();

        $Uma = $parametros_empresa->uma;

        if ($idhoraExtDoble > 0) {

            $salDiario = (!empty($this->empleado->salario_diario)) ?: 0;
            $colHrEx2  = 'valor' . $idhoraExtDoble;
            $horaDob   = (!empty($this->empleado->rutina->$colHrEx2)) ?: 0;
            $hext2     = ($salDiario / 8 * 2) * $horaDob;

            if ($hext2 > ($Uma * 5)) {

                $partegravada = $hext2 - ($Uma * 5);
                $parteExenta  = $Uma * 5;
            } else {

                $partegravada = 0;
                $parteExenta  = $hext2;
            }

            $TotalGrava  = $this->empleado->rutina->total_gravado;
            $TotalGravar = $partegravada + $TotalGrava;


            $this->empleado->rutina->total_gravado = $TotalGravar;
            $this->empleado->rutina->{'total' . $idhoraExtDoble} = $hext2;
            $this->empleado->rutina->{'excento' . $idhoraExtDoble} = $parteExenta;
            $this->empleado->rutina->{'gravado' . $idhoraExtDoble} = $partegravada;
        }
        // dd($this->empleado->rutina);
    }

    protected function calcularPPAGUIFiniquito()
    {

        $periodo = $this->periodo;
        $auxagui = 1;
        $concepto_aguinaldo = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'AGUINALDO')->where('estatus', 1)->first();

        $idAguinaldo = ($concepto_aguinaldo != null) ? intval($concepto_aguinaldo->id) : 0;
        // $parametros_empresa = Session::get('empresa.parametros')[0];
        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();
        $uma                = $parametros_empresa->uma;
        $TipoNomina         = $parametros_empresa->tipo_nomina;
        $provisionAguinaldo = $parametros_empresa->provision_aguinaldo;
        $prestaciones = DB::connection('empresa')->table('prestaciones')->where('id', $this->empleado->id_prestacion)->first();
        // periodosNomina::where('id', $periodo->id)->update(['aux_agui' => 1]);
        //dd($periodo,$prestaciones,$this->empleado->id_prestacion);
        $TotalGrava         = $this->empleado->rutina->total_gravado;
        $salDiario          = (!empty($this->empleado->salario_diario)) ? $this->empleado->salario_diario : 0;
        $TipoFiscal         = $this->empleado->TipoFiscal;
        $TipoSindical       = $this->empleado->TipoSindical;
        $fechaAntiguedad    = $this->empleado->fecha_alta;
        $SalDigital         = (!empty($this->empleado->salario_digital)) ? $this->empleado->salario_digital : 0;
        $salreal            = (!empty($this->empleado->salario_digital)) ? $this->empleado->salario_digital : 0;
        $dia_final_periodo   = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo   = date('m', strtotime($periodo->fecha_final_periodo));
        $AñofechaAntiguedad = date('Y', strtotime($this->empleado->fecha_alta));
        $añocompara         = date('Y');
        $incapacidades      = $this->empleado->dias_incapacidad;
        $diasaguinaldo      = 15; //$this->empleado->dias_aguinaldo;
        $faltas             = $this->empleado->faltas;
        //$diasrealesAgui     = $prestaciones->bono_aguinaldo;
        $diasrealesAgui     = (!empty($prestaciones->bono_aguinaldo)) ? $prestaciones->bono_aguinaldo : 15;

        //dump($AñofechaAntiguedad,$añocompara);
        if ($AñofechaAntiguedad < $añocompara) {

            $fechacontabilizar = $periodo->ejercicio . '-01-01';

            $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
            $fechacontabilizar = Carbon::parse($fechacontabilizar);
            $diaspagar = $fecha_baja->diffInDays($fechacontabilizar) + 1;

            //hasta aqui igual que en excel
            //---------------------------------------------------------------

            /*  $dias_pagar_excel = round(($diaspagar/365)*15,2);
            $agui_fiscal =  round($dias_pagar_excel * $salDiario);

            dd($dias_pagar_excel,$agui_fiscal,$diasrealesAgui);
*/
            //---------------------------------------------------------------
            $diasagui = round(($diaspagar * $diasaguinaldo) / 365, 2);
            $aguin2 = $salDiario * $diasagui;
            $aguinaldo2 = round($aguin2, 2);
            //dump($salDiario,"xx",$salreal);

            $diasaguireal = round(($diaspagar * $diasrealesAgui) / 365, 2);
            // dump($diasaguireal);
            //echo 'aguireAL'.$diasaguireal.'<br>';

            $aguinaldoreal = round((floatval($salreal) * $diasaguireal) - $aguinaldo2, 2);
            // dd(floatval($salreal),(floatval($salreal) * $diasagui),$aguinaldoreal);
            // dd($fechacontabilizar, $fecha_baja,$diaspagar,$diasaguinaldo,$diasagui,$salDiario,$aguinaldo2,$salreal,$aguinaldoreal);

            if ($aguin2 > ($uma * 30)) {
                //$parteGravada = $aguinaldoreal;
                $parteGravada = $aguinaldo2 - ($uma * 30);
                $parteExenta = $uma * 30;
            } else {
                $parteGravada = 0;
                $parteExenta = $aguin2;
            }
            $this->empleado->rutina->{'total' . $idAguinaldo} = round($aguinaldo2, 2);
            $this->empleado->compensacion_sindical += $aguinaldoreal;
        } else {
            $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
            $fecha_alta = Carbon::parse($this->empleado->fecha_alta);
            $diaspagar = $fecha_baja->diffInDays($fecha_alta);

            $diasagui = ($diaspagar * $diasaguinaldo) / 365;
            $aguin = $salDiario * $diasagui;
            $aguinaldo = round($aguin, 2);

            $diasaguireal = ($diaspagar * $diasrealesAgui) / 365;
            $aguinaldoreal = round((floatval($salreal) * $diasaguireal) - $aguinaldo, 2);

            if ($aguinaldo > ($uma * 30)) {
                $parteGravada = $aguinaldo - ($uma * 30);
                $parteExenta = $uma * 30;
            } else {
                $parteGravada = 0;
                $parteExenta = $aguinaldo;
            }
            // $this->empleado->rutina->{'total' . $idAguinaldo} = $aguinaldo;
            $this->empleado->rutina->{'total' . $idAguinaldo} = $aguinaldo;

            $this->empleado->compensacion_sindical += $aguinaldoreal;
        }
        //dd($aguinaldoreal,$aguinaldo_depositado,($aguinaldoreal - $aguinaldo_depositado));
        $this->aguinaldoreal = $aguinaldoreal;
        $resultaGravado = $TotalGrava + $parteGravada;


        $resultaGravado = $TotalGrava + $parteGravada;
        $this->empleado->rutina->total_gravado = $resultaGravado;
        $this->empleado->rutina->{'excento' . $idAguinaldo} = $parteExenta;
        $this->empleado->rutina->{'gravado' . $idAguinaldo} = $parteGravada;

        $this->claves_conceptos['aguinaldo']['id'] = $idAguinaldo;
        $this->claves_conceptos['aguinaldo']['valor'] = $this->empleado->rutina->{'total' . $idAguinaldo};
        $this->claves_conceptos['aguinaldo']['compensacion'] = $aguinaldoreal;

        if (isset($this->empleado->adelanto_aguinaldo) && $this->empleado->adelanto_aguinaldo > 0) {
            $this->empleado->compensacion_sindical = $this->empleado->compensacion_sindical - $this->empleado->adelanto_aguinaldo;
        }
        /*     echo $idAguinaldo;
        dd($this->empleado->rutina);*/
    }

    protected function calcularINFONAFiniquito()
    {

        $periodo = $this->periodo;
        $MesPeriodo   = date('m', strtotime($periodo->fecha_inicial_periodo));

        if ($MesPeriodo == 1 || $MesPeriodo == 2) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-02-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-01-01'));
        } else if ($MesPeriodo == 3 || $MesPeriodo == 4) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-04-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-03-01'));
        } else if ($MesPeriodo == 5 || $MesPeriodo == 6) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-06-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-05-01'));
        } else if ($MesPeriodo == 7 || $MesPeriodo == 8) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-08-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-07-01'));
        } else if ($MesPeriodo == 9 || $MesPeriodo == 10) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-10-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-09-01'));
        } else if ($MesPeriodo == 11 || $MesPeriodo == 12) {

            $inicio = date('Y-m-t', strtotime($periodo->ejercicio . '-12-20'));
            $fin = date('Y-m-t', strtotime($periodo->ejercicio . '-11-01'));
        }


        $f1 = Carbon::parse($inicio);
        $f2 = Carbon::parse($fin);
        $diasBimestre = $f1->diffInDays($f2) + 1;

        if ($this->empleado->valida_dias->count() > 0) {
            $diasBimestre = 0;
        }

        $concepto_faltas = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')
            ->where(function ($query) {
                $query->where('nombre_concepto', 'FALTAS')
                    ->orWhere('nombre_concepto', 'faltas')
                    ->orWhere('nombre_concepto', 'FLTASS');
            })->where('estatus', 1)->first();
        $idconcepFalta   = ($concepto_faltas != null) ? intval($concepto_faltas->id) : 0;

        $concepto_infonavit = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'CREDITO INFONAVIT')->where('estatus', 1)->first();
        $IdInfonavit        = ($concepto_infonavit != null) ? intval($concepto_infonavit->id) : 0;

        // $parametros_empresa = Session::get('empresa.parametros')[0];
        $parametros_empresa = DB::connection('empresa')
                    ->table('parametros')
                    ->first();

        $Uma                = $parametros_empresa->uma;
        $dia_final_periodo   = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo   = date('m', strtotime($periodo->fecha_final_periodo));
        $ano_final_periodo   = date('Y', strtotime($periodo->fecha_final_periodo));


        $FechaAlta     = $this->empleado->fecha_alta;
        $incapacidades = $this->empleado->dias_incapacidad;
        $faltas        = $this->empleado->faltas;

        if ($FechaAlta > $periodo->fecha_inicial_periodo) {

            $FechaFinal = $periodo->fecha_final_periodo;

            if ($dia_final_periodo == '28' || $dia_final_periodo == '29' || $dia_final_periodo == '31') {

                $FechaFinal = $ano_final_periodo . '-' . $mes_final_periodo . '-30';
            }

            if ($mes_final_periodo == 2 && $dia_final_periodo > 15) {
                $FechaFinal = date('Y-m-t', strtotime($periodo->fecha_inicial_periodo));
            }

            $fecha_final_periodo  = Carbon::parse($FechaFinal);
            $fecha_alta          = Carbon::parse($this->empleado->fecha_alta);
            $Di                  = $fecha_final_periodo->diffInDays($fecha_alta) + 1;
            $Dias                = $Di - $faltas - $incapacidades;
        } else {

            $Dias = $periodo->dias_periodo + 1;
        }

        $TipoDescuento  = strtoupper($this->empleado->tipo_descuento);
        $SalarioDiaInte = $this->empleado->salario_diario_integrado;
        $Valor          = $this->empleado->valor_descuento;
        $CreditoInfona  = intval($this->empleado->num_credito_infonavit);

        if ($CreditoInfona > 0) {

            $this->empleado->rutina->{'total' . $IdInfonavit} = 0;

            $ValorPorcentaje = $Valor / 100;

            if ($TipoDescuento == 'CUOTA FIJA') {

                $cuotaFija = (($Valor * 2) / $diasBimestre) * $Dias;
                $data = round($cuotaFija, 2);
            } else if ($TipoDescuento == 'POR PORCENTAJE') {

                $Infonavit = $SalarioDiaInte * $ValorPorcentaje * $Dias;
                $data = round($Infonavit, 2);
            } else if ($TipoDescuento == 'VECES EN SALARIO') {

                $Infonavit = ((($Uma * $Valor) * 2) / $diasBimestre) * $Dias;
                $data = round($Infonavit, 2);
            }

            $this->empleado->rutina->{'total' . $IdInfonavit} = $data;
        }
        // dump($IdInfonavit, $CreditoInfona, $this->empleado->rutina->{'total' . $IdInfonavit});
    }

    protected function calcularVACAFiniquito()
    {
        $periodo = $this->periodo;

        $concepto_vacaciones = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id', 'tipo', 'file_rool')->where('nombre_concepto', 'VACACIONES')
            ->where('estatus', 1)->first();

        $idconcepVacaciones  = ($concepto_vacaciones != null) ? intval($concepto_vacaciones->id) : 0;

        $FechaAlta     = $this->empleado->fecha_alta;
        $anio_fecha_alta = date('Y', strtotime($this->empleado->fecha_alta));
        $incapacidades = $this->empleado->dias_incapacidad;
        $faltas        = $this->empleado->faltas;
        $col_vacaciones = 'valor' . $idconcepVacaciones;
        //dd($col_vacaciones);
        $diasla = intval($this->empleado->rutina->$col_vacaciones);
        //dd($this->empleado->rutina->$col_vacaciones);
        $diasvacaiones = $this->empleado->dias_vacaciones;
        $sueldo       = (!empty($this->empleado->salario_diario)) ? $this->empleado->salario_diario : 0;
        $salreal = $this->empleado->salario_digital;
        //dd($sueldo,$diaslabo);
        $TotalGrava   = $this->empleado->rutina->total_gravado;
        $fecha_baja = Carbon::parse($this->empleado->fecha_baja);
        $fecha_alta = Carbon::parse($this->empleado->fecha_antiguedad);
        //dd(intval($anio_fecha_alta), intval($this->ejercicio), $diasvacaiones, $diasla);
        $anios_antiguedad = $fecha_baja->diffInYears($fecha_alta);

        if ($anios_antiguedad > 0) {
            $fechacontabilizar = Carbon::parse($this->ejercicio . '-' . $fecha_alta->format('m') . '-' . $fecha_alta->format('d'));
            if ($fechacontabilizar > $fecha_baja) {
                $fechacontabilizar = Carbon::parse(($this->ejercicio - 1) . '-' . $fecha_alta->format('m') . '-' . $fecha_alta->format('d'));
            }
        } else {
            $fechacontabilizar = Carbon::parse($this->empleado->fecha_alta);
        }
        /*   if(intval($anio_fecha_alta) < intval($this->ejercicio)){
            $fechacontabilizar = Carbon::parse($this->empleado->fecha_alta);
            
        }else{
            //dado que no esta implementada la forma de saber que vacaciones ha tomado
            $fechacontabilizar = Carbon::parse($this->ejercicio.'-01-01');
        }*/

        $diaspagar = $fecha_baja->diffInDays($fechacontabilizar) + 1;
        //dump($diaspagar);
        //dump($this->ejercicio,$fecha_alta,$fecha_baja,$anios_antiguedad,$fechacontabilizar,$diaspagar,$diasvacaiones);

        //$diasagui = round(($diaspagar * $diasvacaiones) / 365,2);
        $diasvaca = round(($diaspagar * $this->dias_vacaciones[$anios_antiguedad]) / 365, 2);
        $this->valores_calculadora['dias_vacaciones'] = $this->dias_vacaciones[$anios_antiguedad];
        // dump($diasvaca,$diasla,$sueldo);
        $diaslabo = $diasla + $diasvaca;
        //dump($diaspagar,$diasvaca, $diaslabo);

        //dump($this->empleado->rutina->$col_vacaciones,$diaslabo,$idconcepVacaciones);
        $vacaciones_fiscal    = round($sueldo * $diaslabo, 2);
        $vacaciones_real      = round($salreal * $diaslabo, 2);
        $vacaciones_sindical = $vacaciones_real - $vacaciones_fiscal;
        $this->empleado->compensacion_sindical += $vacaciones_sindical;
        // dd($vacaciones_fiscal, $vacaciones_real, $vacaciones_sindical,$this->empleado->compensacion_sindical);

        $parteGravada = $vacaciones_fiscal;
        $parteExenta  = 0;
        $Filerool = $concepto_vacaciones->file_rool;

        if ($Filerool > 250 && $Filerool = 0) {
            $parteGravada = 0;
        }

        $resultaGravado = $TotalGrava + $parteGravada;
        // dump($vacaciones_fiscal,$vacaciones_real,$vacaciones_sindical,$resultaGravado);
        $total_vacaciones_pendientes = $total_vacaciones_pendientes_fiscal = $total_vacaciones_pendientes_sindical = 0;
        if (isset($this->empleado->vacaciones_pendientes) && $this->empleado->vacaciones_pendientes > 0) {
            $total_vacaciones_pendientes = $this->empleado->vacaciones_pendientes * $salreal;
            $total_vacaciones_pendientes_fiscal = $this->empleado->vacaciones_pendientes * $sueldo;
            $total_vacaciones_pendientes_sindical = $total_vacaciones_pendientes - $total_vacaciones_pendientes_fiscal;
            //  dd($total_vacaciones_pendientes,$total_vacaciones_pendientes_fiscal,$total_vacaciones_pendientes_sindical);
            //$this->empleado->compensacion_sindical = $this->empleado->compensacion_sindical - $this->empleado->vacaciones_pendientes;               
        }
        $this->empleado->rutina->total_gravado = $resultaGravado;
        $this->empleado->compensacion_sindical = $this->empleado->compensacion_sindical + $total_vacaciones_pendientes_sindical;

        $this->claves_conceptos['vacaciones']['valor'] = $vacaciones_fiscal;
        $this->claves_conceptos['vacaciones']['id'] = $idconcepVacaciones;
        $this->claves_conceptos['vacaciones']['compensacion'] = $vacaciones_sindical;
        $this->empleado->rutina->{'total' . $idconcepVacaciones} = round($vacaciones_fiscal + $total_vacaciones_pendientes_fiscal, 2);
        $this->empleado->rutina->{'gravado' . $idconcepVacaciones} = $parteGravada;
        $this->empleado->rutina->{'excento' . $idconcepVacaciones} = $parteExenta;



        //dd($this->empleado->rutina->total_gravado, $this->empleado->rutina->{'total' . $idconcepVacaciones},$this->empleado->rutina->{'gravado' . $idconcepVacaciones},$this->empleado->rutina->{'excento' . $idconcepVacaciones});

    }

    protected function calcularDefaultConcepto($concepto)
    {

        $periodo = $this->periodo;

        //dump('idconcepto: '.$concepto->id . ' - Rutina: '. $concepto->rutina );
        $TipoConce = $concepto->tipo;
        $Filerool = $concepto->file_rool;
        $idconcepto = $concepto->id;
        $valorConcepto = 'valor' . $idconcepto;

        if (isset($this->empleado->rutina->$valorConcepto)) {
            if ($TipoConce == '0' && $Filerool < 250 && $Filerool != 0) {

                $valorcon   = (!empty($this->empleado->rutina->$valorConcepto)) ? round($this->empleado->rutina->$valorConcepto, 4) : 0;
                $totalGrava = round($this->empleado->rutina->total_gravado, 4);
                $totalneto  = round($valorcon + $totalGrava, 4);

                $this->empleado->rutina->{'total' . $idconcepto} = $valorcon;
            }
        }
        /*  $valorcon   = (!empty($this->empleado->rutina->$valorConcepto)) ? round($this->empleado->rutina->$valorConcepto, 4) : 0;
        $idrutina   = $this->empleado->rutina->id;

        $this->empleado->rutina->{'total' . $idconcepto} = $valorcon;*/
    }

    protected function calcularTotalNetofiniquito()
    {
        $totalGrava = $this->empleado->rutina->total_gravado;
        $totalneto = $totalGrava;

        $validaconceptos = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->where('estatus', "!=", 2)
            ->where('finiquito', 1)
            ->where('tipo', 0)
            ->where('rutinas', '')
            ->where(function ($query) {
                $query->where('file_rool', '>', 0)
                    ->where('file_rool', '<', 250);
            })->get()->keyBy('id');

        foreach ($validaconceptos as $concepto) {
            $columna = 'valor' . $concepto->id;
            //  dump($columna, isset($this->empleado->rutina->$columna));
            if (isset($this->empleado->rutina->$columna)) {
                //dump($columna."-".$this->empleado->rutina->$columna);
                $valorcon = $this->empleado->rutina->$columna;
                //dump($valorcon);
                $totalneto += intval($valorcon);
            }
        }
        $this->empleado->rutina->total_gravado = $totalneto;
    }

    protected function calcularCompensacionfiniquito()
    {
        $concepto_compensacion = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->where(function ($query) {
                $query->where('nombre_concepto', 'COMPENSACION')
                    ->orWhere('nombre_concepto', 'COMPENSACIONES');
            })
            ->where('estatus', 1)->get();
        //dd($concepto_compensacion);
        if ($concepto_compensacion->count() > 0) {
            foreach ($concepto_compensacion as $compensacion) {
                $columna_compe = 'valor' . $compensacion->id;
                if (isset($this->empleado->rutina->$columna_compe)) {
                    $Compensacion = $this->empleado->rutina->$columna_compe;
                    $Compensacion = intval($Compensacion) + intval($this->SDOCompensacion) + intval($this->aguinaldoreal) + intval($this->vacacionesCompe) + intval($this->pvacreal);
                    $this->empleado->rutina->total_gravado += $Compensacion;
                }
            }
        }

        $columna_compe = 'total' . $compensacion->id;
        $this->empleado->rutina->$columna_compe = $this->empleado->compensacion_sindical;
        $this->empleado->rutina->total_gravado  += $this->empleado->compensacion_sindical;
        $this->claves_conceptos['compensacion']['id'] = $compensacion->id;
        $this->claves_conceptos['compensacion']['valor'] = $this->empleado->compensacion_sindical;
    }

    protected function calcularISPTfiniquito()
    {
        $periodo = $this->periodo;
        $sueldo = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')
            ->where('nombre_concepto', 'SUELDO')
            ->where('estatus', 1)->first();
        $idsuel = $sueldo->id;

        $isr = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'ISR')
            ->where('estatus', 1)->first();
        // dump($isr);
        $ispt = $isr->id;
        $this->conceptoISR = $isr;
        $this->claves_conceptos['isr']['id'] = $ispt;

        $imss = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')->where('nombre_concepto', 'IMSS')
            ->where('estatus', 1)->first();
        $IdImss = $imss->id;
        $this->claves_conceptos['imss']['id'] = $imss->id;


        //($imss);
        $queryidDescF = DB::connection('empresa')
            ->table('conceptos_nomina')->select('id')
            ->where('nombre_concepto', 'DESCFISCALES')
            ->where('estatus', 1)->first();

        $id_descfiscales = ($queryidDescF != null) ? intval($queryidDescF->id) : 0;
        $this->claves_conceptos['descfiscales']['id'] = $queryidDescF->id;


        $salDiario = $this->empleado->salario_diario;
        $TipoSindical = $this->empleado->tipo_sindical;
        $TipoFiscal = $this->empleado->tipo_fiscal;
        $TotalGravado = $this->empleado->rutina->total_gravado;
        $TrueIsr = $this->empleado->rutina->true_isr;

        $qryImpuestos = "SELECT * from impuestos where 
        $TotalGravado between limite_inferior 
        and limite_superior  
        and tipo_tabla='MENSUAL' 
        and estatus = 1";
        $impuestos = DB::connection('empresa')->select($qryImpuestos);


        $qryimpuestosMensual = "SELECT * from impuestos where 
         tipo_tabla='MENSUAL' 
        and estatus = 1";
        $this->valores_calculadora['impuestos'] = $impuestos_mensual = DB::connection('empresa')->select($qryimpuestosMensual);
        // dump($impuestos,$impuestos_mensual);


        if (count($impuestos) > 0) {
            $limitInferior = floatval($impuestos[0]->limite_inferior);
            $PorcentajeAplicar = floatval($impuestos[0]->porcentaje);
            $CuotaFija = floatval($impuestos[0]->cuota_fija);
        } else {
            $limitInferior = 0;
            $PorcentajeAplicar = 0;
            $CuotaFija = 0;
        }

        //dd($impuestos,$limitInferior,$PorcentajeAplicar,$CuotaFija);
        $qrySubsidios = "SELECT subsidio from subsidios
        where $TotalGravado 
        between ingreso_desde and ingreso_hasta 
        and tipo_tabla='MENSUAL'
        and estatus = 1";

        $subsidios = DB::connection('empresa')->select($qrySubsidios);

        $qrySubsidiosMensual = "SELECT * from subsidios
        where tipo_tabla='MENSUAL'
        and estatus = 1";

        $this->valores_calculadora['subsidios'] = $subsidios_mensual = DB::connection('empresa')->select($qrySubsidiosMensual);
        //dd($subsidios_mensual);


        //dump($subsidios);
        $CantidadSubsidio = (count($subsidios) > 0) ? floatval($subsidios[0]->subsidio) : 0;
        //dd($CantidadSubsidio);
        // bien
        $ingresoExce      = $TotalGravado - $limitInferior;
        $impuestoMarginal = $ingresoExce * ($PorcentajeAplicar / 100);
        $isrretener       = $impuestoMarginal + $CuotaFija;

        $impuestoCa = (($isrretener - $CantidadSubsidio) > 0) ? ($isrretener - $CantidadSubsidio) : 0;
        $impuestoCargo = round($impuestoCa, 2);

        /* dump("---------------ingresoExce-----------------------------");
        dump($ingresoExce, $TotalGravado , $limitInferior);

        dump("-----------------PorcentajeAplicar---------------------------");
        dump($PorcentajeAplicar,($PorcentajeAplicar / 100),$ingresoExce,$impuestoMarginal);
        
        dump("---------------isrretener-----------------------------");
        dump($isrretener,$impuestoMarginal,$CuotaFija);
        
        dump("-----------------impuestoCa---------------------------");
        dump($impuestoCa, $isrretener, $CantidadSubsidio);*/
        // dd("--------------------------------------------");



        $subsidio = ($impuestoCargo < 0) ? $impuestoCargo : 0;
        $this->empleado->rutina->{'total' . $ispt} = 0;

        $columnaISPT = 'total' . $ispt;
        DB::connection('empresa')
            ->table('rutinas' . $this->ejercicio)
            ->where('id_empleado', $this->empleado->id)
            ->where('id_periodo', $periodo->id)
            ->where('fnq_valor', 1)
            ->update([$columnaISPT => 0]);

        $this->empleado->rutina->$columnaISPT = 0;

        $this->empleado->rutina->$columnaISPT = $impuestoCargo;
        $this->empleado->rutina->subsidio_al_empleo = $CantidadSubsidio;
        $this->empleado->rutina->subsidio = $subsidio;
        // linea 115 
        // ---------------------------------------------------------------------
        $valorpiramidal = $this->SumaObre + $impuestoCargo;

        //suma percepciones ---------------------------------------------------------

        $suma_percepciones = 0;

        $percepciones = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->where('file_rool', '!=', 0)
            ->where('file_rool', '<=',  249)
            ->where('finiquito', 1)
            ->where('tipo', 0)
            ->where('estatus', 1)
            ->get()->keyBy('id');

        $this->conlumnapercepciones = $percepciones;

        foreach ($percepciones as $percepcion) {
            $columna = 'total' . $percepcion->id;
            if (isset($this->empleado->rutina->$columna)) {
                if ($percepcion->rutinas != 'SDO') {
                    //dump($this->empleado->rutina->$columna,$columna,$percepcion->nombre_concepto);echo "<br/><br/>";
                    $suma_percepciones += round($this->empleado->rutina->$columna, 2);
                }
            }
        }
        $this->empleado->rutina->total_percepcion_fiscal = round($suma_percepciones, 2);

        // ---suma deducciones-------------------------------------------------------------------------------------


        $suma_deducciones = 0;

        $deducciones = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->where('estatus', 1)
            ->where('file_rool', '!=', 0)
            ->where('file_rool', '<=',  249)
            ->where('finiquito', 1)
            ->where('tipo', 1)
            ->get();

        $this->conlumnadeducciones = $deducciones;
        // dump($deducciones);

        foreach ($deducciones as $deduccion) {
            $colDeduccion = 'total' . $deduccion->id;
            if (isset($this->empleado->rutina->$colDeduccion)) {
                $suma_deducciones += round($this->empleado->rutina->$colDeduccion, 2);
            }
        }

        $this->empleado->rutina->total_deduccion_fiscal = round($suma_deducciones, 2);

        //-----suma percepcion sindical--------------------------------------------------------------------------------------------------

        $suma_percepcion_sindical = 0;
        $percepcion_sindical = DB::connection('empresa')->table('conceptos_nomina')->select('id')
            ->where('estatus', 1)->where('finiquito', 1)
            ->where('tipo', 0)->where('activo_en_nomina', 1)
            ->where(function ($query) {
                $query->where('file_rool', 0)
                    ->orWhere('file_rool', '>=', 250);
            })->get();
        //dump($percepcion_sindical);
        foreach ($percepcion_sindical as $ps) {

            $colPercepcion = 'total' . $ps->id;
            //$totalesSindi  = round($this->empleado->rutina->$colPercepcion, 2);
            if (isset($this->empleado->rutina->$colPercepcion)) {
                $suma_percepcion_sindical += round($this->empleado->rutina->$colPercepcion, 2);
            }
        }

        $this->empleado->rutina->total_percepcion_sindical = round($suma_percepcion_sindical, 4);

        //----suma deducciones sindicales---------------------------------------------------------------------------------------------------
        $suma_deduccion_sindical = 0;
        $deduccion_sindical = DB::connection('empresa')
            ->table('conceptos_nomina')->select('id')
            ->where('estatus', 1)
            ->where('finiquito', 1)
            ->where('tipo', 1)
            ->where(function ($query) {
                $query->where('file_rool', 0)
                    ->orWhere('file_rool', '>=', 250);
            })->get();
        //dump($deduccion_sindical);
        if ($deduccion_sindical->count() > 0) {

            foreach ($deduccion_sindical as $ds) {
                $colDeduccion = 'total' . $ds->id;
                if (isset($this->empleado->rutina->$colDeduccion)) {
                    $suma_deduccion_sindical += round($this->empleado->rutina->$colDeduccion, 2);
                }
            }

            $this->empleado->rutina->total_deduccion_sindical = round($suma_deduccion_sindical, 4);
        } else {
            $this->empleado->rutina->total_deduccion_sindical = 0;
        }
    }

    protected function calcularNeto_Fiscalfiniquito()
    {
        $TotalPercep    = round($this->empleado->rutina->total_percepcion_fiscal, 4);
        $TotalDeduccion = round($this->empleado->rutina->total_deduccion_fiscal, 4);
        $neto           = round($TotalPercep - $TotalDeduccion, 4);
        $this->empleado->rutina->neto_fiscal = $neto;
    }

    protected function calcularNeto_Fiscal_Sindicalfiniquito()
    {
        $TotalPercep    = round($this->empleado->rutina->total_percepcion_sindical, 2);
        $TotalDeduccion = round($this->empleado->rutina->total_deduccion_sindical, 2);
        $neto           = $TotalPercep - $TotalDeduccion;
        $this->empleado->rutina->neto_sindical = $neto;
    }

    public function validarConceptosDefault()
    {
        $num_errores = $num_errores_conceptos = 0;
        $empleado_validaciones = array();
        $validacion = array();
        $errores = 0;

        // salario diario
        if(is_numeric($this->empleado->salario_diario)){
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIARIO', 'asignado' => true, 'dato' =>  $this->empleado->salario_diario));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIARIO', 'asignado' => false, 'dato' => $this->empleado->salario_diario));
            $errores++;
        }
        // salario digital
        if(is_numeric($this->empleado->salario_digital)){
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIGITAL', 'asignado' => true, 'dato' =>  $this->empleado->salario_digital ));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIGITAL', 'asignado' => false, 'dato' => $this->empleado->salario_digital));
            $errores++;
        }
        // salario digital
        if(is_numeric($this->empleado->salario_diario_integrado)){
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIARIO INTEGRADO', 'asignado' => true, 'dato' =>  $this->empleado->salario_diario_integrado ));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'SALARIO DIARIO INTEGRADO', 'asignado' => false, 'dato' => $this->empleado->salario_diario_integrado));
            $errores++;
        }
       
        // fecha baja
        if($this->empleado->fecha_antiguedad != "0000-00-00" && $this->empleado->fecha_antiguedad != ""){
            array_push($empleado_validaciones,array('concepto' => 'FECHA ANTIGUEDAD', 'asignado' => true, 'dato' =>  $this->empleado->fecha_antiguedad ));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'FECHA ANTIGUEDAD', 'asignado' => false, 'dato' => $this->empleado->fecha_antiguedad));
            $errores++;
        }
        // fecha alta
        if($this->empleado->fecha_alta != "0000-00-00" && $this->empleado->fecha_alta != ""){
            array_push($empleado_validaciones,array('concepto' => 'FECHA ALTA', 'asignado' => true, 'dato' =>  $this->empleado->fecha_alta ));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'FECHA ALTA', 'asignado' => false, 'dato' => 0));
            $errores++;
        }
         // fecha baja
         if($this->empleado->fecha_baja != "0000-00-00" && $this->empleado->fecha_baja != ""){
            array_push($empleado_validaciones,array('concepto' => 'FECHA_BAJA', 'asignado' => true, 'dato' =>  $this->empleado->fecha_baja ));
        }else{
            array_push($empleado_validaciones,array('concepto' => 'FECHA_BAJA', 'asignado' => false, 'dato' => 0));
            $errores++;
        }

        $queryConceptos = "SELECT * FROM conceptos_nomina WHERE finiquito = 1 and estatus = 1";
        $concep = DB::connection('empresa')->select($queryConceptos);
        $conceptos = array();
        foreach ($concep as $c) {
            $concepto = array();
//            $errores = array('sat' => false);
            $concepto['id']         = $c->id;
            $concepto['nombre']     = strtoupper($c->nombre_concepto);
            $concepto['codigo_sat'] = $c->codigo_sat;
            $concepto['rutinas']    = strtoupper($c->rutinas);
            
            
            if ($c->codigo_sat == NULL) {
               // $errores['sat'] = true;
                $num_errores_conceptos++;
            }
            $conceptos[strtoupper($c->nombre_concepto)] = $concepto;
        }
        
        // FALTAS
        if(isset($conceptos['FALTAS']) ){
            array_push($validacion,array('concepto' => 'Faltas', 'asignado' => true, 'id' => $conceptos['FALTAS']['id']));
        }else if(isset($conceptos['faltas'])){
            array_push($validacion,array('concepto' => 'Faltas', 'asignado' => true, 'id' => $conceptos['faltas']['id']));
        }else if(isset($conceptos['FLTASS'])){
            array_push($validacion,array('concepto' => 'Faltas', 'asignado' => true, 'id' => $conceptos['FLTASS']['id']));
        }else{
            array_push($validacion,array('concepto' => 'Faltas', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        //PRIMA VACACIONAL
        if(isset($conceptos['PRIMA VACACIONAL']) && $conceptos['PRIMA VACACIONAL']['rutinas'] == 'PVAC'){
            array_push($validacion,array('concepto' => 'PRIMA VACACIONAL', 'asignado' => true, 'id' =>  $conceptos['PRIMA VACACIONAL']['id']));
            $this->claves_conceptos['prima']['valor'] = 0;
            $this->claves_conceptos['prima']['id'] = $conceptos['PRIMA VACACIONAL']['id'];
            $this->claves_conceptos['prima']['compensacion'] = 0;
        }else if(isset($conceptos['PRIM.VACACIONAL']) && $conceptos['PRIM.VACACIONAL']['rutinas'] == 'PVAC'){
            array_push($validacion,array('concepto' => 'PRIMA VACACIONAL', 'asignado' => true, 'id' =>  $conceptos['PRIM.VACACIONAL']['id']));
            $this->claves_conceptos['prima']['valor'] = 0;
            $this->claves_conceptos['prima']['id'] = $conceptos['PRIMA VACACIONAL']['id'];
            $this->claves_conceptos['prima']['compensacion'] = 0;
        }else{
            array_push($validacion,array('concepto' => 'PRIMA VACACIONAL', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // AGUINALDO
        if(isset($conceptos['AGUINALDO']) && $conceptos['AGUINALDO']['rutinas'] == 'PPAGUI'){
            array_push($validacion,array('concepto' => 'AGUINALDO', 'asignado' => true, 'id' =>  $conceptos['AGUINALDO']['id']));
            $this->claves_conceptos['aguinaldo']['id'] = $conceptos['AGUINALDO']['id'];
            $this->claves_conceptos['aguinaldo']['valor'] = 0;
            $this->claves_conceptos['aguinaldo']['compensacion'] = 0;
        }else{
            array_push($validacion,array('concepto' => 'AGUINALDO', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // VACACIONES
        if(isset($conceptos['VACACIONES']) && $conceptos['VACACIONES']['rutinas'] == 'VACA'){
            array_push($validacion,array('concepto' => 'VACACIONES', 'asignado' => true, 'id' =>  $conceptos['VACACIONES']['id']));
            $this->claves_conceptos['vacaciones']['valor'] = 0;
            $this->claves_conceptos['vacaciones']['id'] = $conceptos['VACACIONES']['id'];
            $this->claves_conceptos['vacaciones']['compensacion'] = 0;

            $prestacion_vacaciones = Prestacion::find($this->empleado->id_prestacion);

            $this->valores_calculadora['dias_vacaciones'] = $prestacion_vacaciones->vacaciones;
            $this->valores_calculadora['factor_integracion'] = $prestacion_vacaciones->factor_integracion;
        }else{
            array_push($validacion,array('concepto' => 'VACACIONES', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // COMPENSACION
        if(isset($conceptos['COMPENSACION'])){
            array_push($validacion,array('concepto' => 'COMPENSACION', 'asignado' => true, 'id' =>  $conceptos['COMPENSACION']['id']));
            $this->claves_conceptos['compensacion']['id'] = $conceptos['COMPENSACION']['id'];
            $this->claves_conceptos['compensacion']['valor'] = 0;
        }else{
            array_push($validacion,array('concepto' => 'COMPENSACION', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // SUELDO
        if(isset($conceptos['SUELDO']) && $conceptos['SUELDO']['rutinas'] == 'SDO'){
            array_push($validacion,array('concepto' => 'SUELDO', 'asignado' => true, 'id' =>  $conceptos['SUELDO']['id']));
            $this->claves_conceptos['sueldo']['id'] = $conceptos['SUELDO']['id'];
            $this->claves_conceptos['sueldo']['valor'] = 0;
            $this->claves_conceptos['sueldo']['compensacion'] = 0;
            $this->valores_calculadora['dias_laborados'] = 0;
        }else{
            array_push($validacion,array('concepto' => 'SUELDO', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // otros descuentos
        if(isset($conceptos['OTROS DESCUENTOS']) ){
            array_push($validacion,array('concepto' => 'OTROS DESCUENTOS', 'asignado' => true, 'id' =>  $conceptos['OTROS DESCUENTOS']['id']));
            $conceptos['OTROS DESCUENTOS']['id'];

            $this->claves_conceptos['otros']['id'] = $conceptos['OTROS DESCUENTOS']['id'];
        }else{
            array_push($validacion,array('concepto' => 'OTROS DESCUENTOS', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // infonavit
        if(isset($conceptos['CREDITO INFONAVIT']) ){
            array_push($validacion,array('concepto' => 'CREDITO INFONAVIT', 'asignado' => true, 'id' =>  $conceptos['CREDITO INFONAVIT']['id']));
            $conceptos['CREDITO INFONAVIT']['id'];

            $this->claves_conceptos['infonavit']['id'] = $conceptos['CREDITO INFONAVIT']['id'];
        }else{
            array_push($validacion,array('concepto' => 'CREDITO INFONAVIT', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // sueldos pagados en demasia
        if(isset($conceptos['SUELDOS PAGADOS EN DEMASIA']) ){
            array_push($validacion,array('concepto' => 'SUELDOS PAGADOS EN DEMASIA', 'asignado' => true, 'id' =>  $conceptos['SUELDOS PAGADOS EN DEMASIA']['id']));
            $conceptos['SUELDOS PAGADOS EN DEMASIA']['id'];

            $this->claves_conceptos['demasia']['id'] = $conceptos['SUELDOS PAGADOS EN DEMASIA']['id'];
        }else{
            array_push($validacion,array('concepto' => 'SUELDOS PAGADOS EN DEMASIA', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        //INDEMNIZACIONES
        if(isset($conceptos['INDEMNIZACIONES']) ){
            array_push($validacion,array('concepto' => 'INDEMNIZACIONES', 'asignado' => true, 'id' =>  $conceptos['INDEMNIZACIONES']['id']));
            $this->claves_conceptos['indemnizacion']['id'] = $conceptos['INDEMNIZACIONES']['id'];
            $this->claves_conceptos['indemnizacion']['valor'] = 0;
        }else{
            array_push($validacion,array('concepto' => 'INDEMNIZACIONES', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // PRIMA POR ANTIGUEDAD
        if(isset($conceptos['PRIMA POR ANTIGUEDAD'])){
            array_push($validacion,array('concepto' => 'PRIMA POR ANTIGUEDAD', 'asignado' => true, 'id' =>  $conceptos['PRIMA POR ANTIGUEDAD']['id']));
        }else{
            array_push($validacion,array('concepto' => 'PRIMA POR ANTIGUEDAD', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // ISR
        if(isset($conceptos['ISR']) && $conceptos['ISR']['rutinas'] == 'ISR'){
            array_push($validacion,array('concepto' => 'ISR', 'asignado' => true, 'id' =>  $conceptos['ISR']['id']));
            $this->claves_conceptos['isr']['id'] = $conceptos['ISR']['id'];
        }else{
            array_push($validacion,array('concepto' => 'ISR', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        // IMSS
        if(isset($conceptos['IMSS']) && $conceptos['IMSS']['rutinas'] == 'IMSS'){
            array_push($validacion,array('concepto' => 'IMSS', 'asignado' => true, 'id' =>  $conceptos['IMSS']['id']));
            $this->claves_conceptos['imss']['calculoIMSS']  = Session::get('empresa')['calculo_imss'];
            $this->claves_conceptos['imss']['diasIMSS'] = Session::get('empresa')['dias_imss'];            
            $this->claves_conceptos['imss']['id'] = $conceptos['IMSS']['id'];
            $this->claves_conceptos['imss']['cuota_fija'] = 0;
            $this->prima_riesgo();
        }else{
            array_push($validacion,array('concepto' => 'IMSS', 'asignado' => false, 'id' => 0));
            $errores++;
        }

        $validacion_impuestos = array();
        $qryimpuestosMensual = "SELECT * from impuestos where tipo_tabla='MENSUAL' and estatus = 1";
        $this->valores_calculadora['impuestos'] =  DB::connection('empresa')->select($qryimpuestosMensual);

        if(count($this->valores_calculadora['impuestos']) > 0){
            array_push($validacion_impuestos,array('concepto' => 'Impuesto Mensual', 'asignado' => true));
        }else{
            array_push($validacion_impuestos,array('concepto' => 'Impuesto Mensual', 'asignado' => false));
            $errores++;
        }
        
        $qrySubsidiosMensual = "SELECT * from subsidios where tipo_tabla='MENSUAL' and estatus = 1";
        $this->valores_calculadora['subsidios'] = DB::connection('empresa')->select($qrySubsidiosMensual);


        if(count($this->valores_calculadora['subsidios']) > 0){
            array_push($validacion_impuestos,array('concepto' => 'Subsidio Mensual', 'asignado' => true));
        }else{
            array_push($validacion_impuestos,array('concepto' => 'Subsidio Mensual', 'asignado' => false));
            $errores++;
        }
        
        $primavaca               = $this->empleado->porcentaje_prima;
        $prvaca                  = floatval("0." . $primavaca);
        $this->valores_calculadora['prima'] = $prvaca;

        return array('validacion_impuestos' => $validacion_impuestos,'validacion'=>$validacion, 'empleado_validaciones' => $empleado_validaciones,'errores' => $errores);
    }

    public function prima_riesgo(){
        $queryPrimaRiesgo = "SELECT regpa.porcentaje_prima as p_prima 
            from empleados em join categorias cat on em.id_categoria = cat.id 
            inner join singh.registro_patronal regpa on cat.tipo_clase = regpa.id 
            where em.id = {$this->empleado->id} and cat.estatus=1 and regpa.estatus=1";

        $primas_riesgo = DB::connection('empresa')->select($queryPrimaRiesgo);

        $this->claves_conceptos['imss']['primas_riesgo'] = 0;
        if ($primas_riesgo != null) {
            foreach ($primas_riesgo as $prima) {
                $this->claves_conceptos['imss']['primas_riesgo'] = $this->empleado->prima_riesgo = ($prima->p_prima / 100);
            }
        }
    }

    public function guardarCalculadora(Request $request)
    {
        cambiarBase(Session::get('base'));
        //conceptos de nomina
        $conceptos = DB::connection('empresa')->table('conceptos_nomina')
            ->orderBy('tipo')->get();

        //validar nueva tabla rutina
        $periodoNomina = new NominaController();
        $periodoNomina->verificarTablaRutinasAdic('rutinas' . date("Y"));

        //$rutinas = Rutina::insertGetId
        $existe_dif = Rutina::where('id_empleado', $request->id_empleado)
            ->where('ejercicio', $request->ejercicio)
            ->where('estatus', 1)
            ->where('fnq_valor', 1)->get();
        if ($existe_dif->count() > 0) {

            foreach ($existe_dif as $existedif) {
                    $rutina_emple = Rutina::find($existedif->id);
                    $rutina_emple->estatus=2;
                    $rutina_emple->update();
                }
        }


        $existe = Rutina::where('id_periodo', $request->id_periodo)
            ->where('id_empleado', $request->id_empleado)
            ->where('ejercicio', $request->ejercicio)
            ->where('estatus', 1)
            ->where('fnq_valor', 1);

        if ($existe->count() == 0) {

            $valores = array(
                'id_periodo' => $request->id_periodo,
                'id_empleado' => $request->id_empleado,
                'ejercicio' => $request->ejercicio,
                'tipo_baja' => $request->calcular,
                'total_percepcion_fiscal' => round(($request->subtotal_finiquito + $request->subtotal_liquidacion), 2),
                'total_deduccion_fiscal' => $request->subtotal_deducciones,
                'neto_fiscal' => $request->neto_fiscal,
                'total_gravado' => $request->total_gravado,
                'fnq_valor' => 1,
                'cuota_fija' => $request->cuota_fija,
                'exce_pa' => $request->exce_pa,
                'exce_ob' => $request->exce_ob,
                'pre_dine_obre' => $request->pre_dine_obre,
                'pre_dine_patro' => $request->pre_dine_patro,
                'gas_medi_patro' => $request->gas_medi_patro,
                'gas_medi_obre' => $request->gas_medi_obre,
                'riesgo_trabajo' => $request->riesgo_trabajo,
                'inva_vida_patro' => $request->inva_vida_patro,
                'inva_vida_obre' => $request->inva_vida_obre,
                'guarde_presta' => $request->guarde_presta,
                'sar_patron' => $request->sar_patron,
                'infonavit_patro' => $request->infonavit_patro,
                'censa_vejez_patron' => $request->censa_vejez_patron,
                'censa_vejez_obre' => $request->censa_vejez_obre,
                'beneficio_sindical' => $request->beneficio_sindical,
                'importe_total' => $request->importe_total,
                'subsidio' => $request->isr_o_subsidio,
                'subsidio_al_empleo' => $request->subsidio_al_empleado,
                'censa_vejez_obre_patronal' => $request->censa_vejez_obre_patronal,
                'dias_laborados' => $request->dias_trabajados_periodo,
                //'infonavit_patron' => $request->infonavit_patro, x
                //   'bono_prima' => $request->, x
                //  'dias_no_laborados' => $request->,
                // 'dias_imss' => $request->,
                // 'concepto_fac' => $request->,
                // 'total_percepcion_fiscal2' => $request->,
                // 'total_percepcion_sindical' => $request->,
                // 'total_deduccion_fiscal2' => $request->,
                // 'total_deduccion_sindical' => $request->,
                //'neto_sindical' => $request->,
                //'incapacidades' => $request->,
                //'sdo_faltas' => $request->,
                //'sdo_incapacidades' => $request->,
                //  'true_isr' => $request->, x
                // 'estatus_confirma' => $request->, x
            );

            //dd($valores);
            if(Session::get('usuarioPermisos')['id_usuario']==64){
                    
                    //dd($request);
                }

            $rutinas = Rutina::insertGetId($valores);



            // //rutina version 1 --------------------------------------------------------------------------------------


            /*
            $valores = array();
            $valores['id_periodo'] = $request->id_periodo;
            $valores['id_empleado'] = $request->id_empleado;
            $valores['total_percepcion_fiscal'] = round(($request->subtotal_finiquito + $request->subtotal_liquidacion), 2);
            $valores['total_deduccion_fiscal'] = $request->subtotal_deducciones;
            $valores['neto_fiscal'] = $request->neto_fiscal;
            $valores['total_gravado'] = $request->total_gravado;
            $valores['fnq_valor'] = 1;
            $valores['cuota_fija'] = $request->cuota_fija;
            $valores['exce_pa'] = $request->exce_pa;
            $valores['exce_ob'] = $request->exce_ob;
            $valores['pre_dine_obre'] = $request->pre_dine_obre;
            $valores['pre_dine_patro'] = $request->pre_dine_patro;
            $valores['gas_medi_patro'] = $request->gas_medi_patro;
            $valores['gas_medi_obre'] = $request->gas_medi_obre;
            $valores['riesgo_trabajo'] = $request->riesgo_trabajo;
            $valores['inva_vida_patro'] = $request->inva_vida_patro;
            $valores['inva_vida_obre'] = $request->inva_vida_obre;
            $valores['guarde_presta'] = $request->guarde_presta;
            $valores['sar_patron'] = $request->sar_patron;
            $valores['infonavit_patro'] = $request->infonavit_patro;
            $valores['censa_vejez_patron'] = $request->censa_vejez_patron;
            $valores['censa_vejez_obre'] = $request->censa_vejez_obre;
            $valores['beneficio_sindical'] = $request->beneficio_sindical;
            $valores['importe_total'] = $request->importe_total;
            $valores['subsidio'] = $request->subsidio;
            $valores['subsidio_al_empleo'] = $request->subsidio_al_empleado;
            $valores['censa_vejez_obre_patronal'] = $request->censa_vejez_obre_patronal;
            $valores['dias_laborados'] = $request->dias_trabajados_periodo;*/
            // 'dias_imss' => $request->,
            // 'concepto_fac' => $request->,
            // 'total_percepcion_fiscal2' => $request->,
            // 'total_percepcion_sindical' => $request->,
            // 'total_deduccion_fiscal2' => $request->,
            // 'total_deduccion_sindical' => $request->,
            //'neto_sindical' => $request->,
            //'incapacidades' => $request->,
            //'sdo_faltas' => $request->,
            //'sdo_incapacidades' => $request->,
            //'infonavit_patron' => $request->infonavit_patro, x
            //   'bono_prima' => $request->, x


            $valores_rutina = array();
            $cont = 0;

            $val = DB::connection('empresa')
                ->table('rutinas' . $request->ejercicio)
                ->where('id_empleado', $request->id_empleado)
                ->where('id_periodo', $request->id_periodo)
                ->where('id', $request->id_rutina)
                ->where('fnq_valor', 1)->first();

            foreach ($conceptos as $concepto) {
                $ct = 'total' . $concepto->id;
                $cv = 'valor' . $concepto->id;
                $cg = 'gravado' . $concepto->id;
                $ce = 'exento' . $concepto->id;
                $cev1 = 'excento' . $concepto->id;

                if (isset($request->$ct)) {
                    if ($concepto->rutinas == 'VACA' && isset($request->totalvacaciones_pendientes) && $request->totalvacaciones_pendientes != "") {
                        if (isset($request->$ct)) {
                            $request->$ct = $request->$ct + $request->totalvacaciones_pendientes;
                            $request->$cg = $request->$cg + $request->gravadovacaciones_pendientes;
                        }
                    } else if ($concepto->rutinas == 'ISR' && isset($request->isr_liquidacion) && $request->isr_liquidacion != "") {
                        $request->$ct = $request->$ct + $request->isr_liquidacion;
                    } else if($concepto->nombre_concepto=='INDEMNIZACIONES'){

                        $parametros_empresa = Session::get('empresa.parametros')[0];
                        //dd($parametros_empresa['uma']);
                        $limite_indemnizacion=$parametros_empresa['uma']*90;
                        
                        $valor_indem='total'.$concepto->id;

                        if($request->$valor_indem>$limite_indemnizacion){
                        $valor_exento_inde=$limite_indemnizacion;
                        $valor_gravado_inde=$request->$valor_indem-$limite_indemnizacion;
                    }else{
                        $valor_exento_inde=$request->$valor_indem;
                        $valor_gravado_inde=0;
                    }

                        $request->$ct=$request->$valor_indem;
                        $request->$ce=$valor_exento_inde;
                        $request->$cg=$valor_gravado_inde;
                    //dd($concepto,$valor_indem);
                    }

                    $valores_rutina[] = array(
                        'id_rutina' => $rutinas,
                        'id_concepto' => $concepto->id,
                        'tipo_concepto' => $concepto->tipo,
                        'nombre_concepto' => strtoupper($concepto->nombre_concepto),
                        'total' => (isset($request->$ct)) ? $request->$ct :  0,
                        'valor' => (isset($request->$cv)) ? $request->$cv :  0,
                        'exento' => (isset($request->$ce)) ? $request->$ce :  0,
                        'gravado' => (isset($request->$cg)) ? $request->$cg :  0
                    );


                    if (isset($val->$ct)) {
                        // dump(isset($val->$ct),$val->$ct);
                        $valores[$ct] = (isset($request->$ct)) ? $request->$ct :  0;
                    }
                    if (isset($val->$cv)) {
                        $valores[$cv] = (isset($request->$cv)) ? $request->$cv :  0;
                    }
                    if (isset($val->$cev1)) {
                        $valores[$cev1] = (isset($request->$ce)) ? $request->$ce :  0;
                    }
                    if (isset($val->$cg)) {
                        $valores[$cg] = (isset($request->$cg)) ? $request->$cg :  0;
                    }
                }
            }

            if ($request->calcular == 2) {
                if (isset($request->indemnizacion) && !empty($request->indemnizacion)) {

                    $parametros_empresa = Session::get('empresa.parametros')[0];
                    //dd($parametros_empresa['uma']);
                    $limite_indemnizacion=$parametros_empresa['uma']*90;
                    if($request->indemnizacion>$limite_indemnizacion){
                        $valor_exento_inde=$limite_indemnizacion;
                        $valor_gravado_inde=$request->indemnizacion-$limite_indemnizacion;
                    }else{
                        $valor_exento_inde=$request->indemnizacion;
                        $valor_gravado_inde=0;

                    }
                    //dd($valor_exento_inde,$valor_gravado_inde);

                    $valores_rutina[] = array(
                        'id_rutina' => $rutinas,
                        'id_concepto' => $concepto->id,
                        'tipo_concepto' => 0,
                        'nombre_concepto' => 'INDEMNIZACION',
                        'total' => (isset($request->indemnizacion)) ? $request->indemnizacion :  0,
                        'valor' => (isset($request->$cv)) ? $request->$cv :  0,
                        'exento' => (isset($request->$ce)) ? $request->$ce :  0,
                        'gravado' => (isset($request->$cg)) ? $request->$cg :  0
                    );
                }

                if (isset($request->priantig) && !empty($request->priantig)) {
                    $valores_rutina[] = array(
                        'id_rutina' => $rutinas,
                        'id_concepto' => 0,
                        'tipo_concepto' => 0,
                        'nombre_concepto' => 'PRIMA ANTIGUEDAD',
                        'total' => (isset($request->priantig)) ? $request->priantig :  0,
                        'valor' => 0,
                        'exento' =>  0,
                        'gravado' =>  0
                    );
                }


                if (isset($request->veinte_dias_por_anio) && !empty($request->veinte_dias_por_anio)) {
                    $valores_rutina[] = array(
                        'id_rutina' => $rutinas,
                        'id_concepto' => 0,
                        'tipo_concepto' => 0,
                        'nombre_concepto' => '20 DIAS POR AÑO',
                        'total' => (isset($request->veinte_dias_por_anio)) ? $request->veinte_dias_por_anio :  0,
                        'valor' => 0,
                        'exento' =>  0,
                        'gravado' =>  0
                    );
                }
                /*    if(isset($request->isr_liquidacion_deducciones) && !empty($request->isr_liquidacion_deducciones)){
                    $valores_rutina[]= array(
                        'id_rutina' => $rutinas,
                        'id_concepto' => 0,
                        'tipo_concepto' => 1,
                        'nombre_concepto' => 'ISR LIQUIDACION',
                        'total' => (isset($request->isr_liquidacion_deducciones))? $request->isr_liquidacion_deducciones :  0,
                        'valor' => 0 ,
                        'exento' =>  0 ,
                        'gravado' =>  0
                    );
                }*/
            }
            //  dump($valores);
            RutinaValor::insert($valores_rutina);
            unset($valores['ejercicio'], $valores['tipo_baja']);

            $val = DB::connection('empresa')
                ->table('rutinas' . $request->ejercicio)
                ->where('id', $request->id_rutina)->update($valores);
        } else {
            $rutinas = $existe->first()->id;
        }
        Empleado::find($request->id_empleado)->update(['estatus' => 2, 'finiquitado' => 1]);

        $empleado = Empleado::where('id', $request->id_empleado)->with('departamento', 'categoria')->first();
        $periodo = periodosNomina::where('id', $request->id_periodo)->first();
        $rutina = Rutina::where('id', $rutinas)->with('valores_conceptos')->first();
        $datos = $this->informacionReporteFiniquitoLiquidacion($periodo->id, $empleado->id);
        $valores = $datos['valores'];
        $totales = $datos['totales'];

        return view('procesos.calculo-finiquito.ver-finiquito', compact('empleado', 'periodo', 'rutina', 'valores', 'totales'))
            ->with('tipo_alerta', 'success')
            ->with('mensaje', 'El finiquito se calculó automaticamente con éxito');
    }

    public function archivosBaja(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($request->idempleado);
        $periodo = PeriodosNomina::where('id', $request->idperiodo)->first();

        $rutina = Rutina::where('ejercicio', $periodo->ejercicio)
            ->where('id_empleado', $empleado->id)
            ->where('id_periodo', $periodo->id)
            ->where('fnq_valor', 1)
            ->with('valores_conceptos')
            ->first();

        $query = "SELECT empemi.* FROM singh.registro_patronal rp 
        INNER JOIN singh.empresas_emisoras empemi ON empemi.id = rp.id_empresa_emisora
        WHERE rp.id = " . $empleado->categoria->tipo_clase . ";";

        $alta = new DateTime($empleado->fecha_alta);
        $baja = new DateTime($empleado->fecha_baja);
        $hoy = new DateTime();

        $sueldo = explode(".", $empleado->salario_diario);
        if (!isset($sueldo[1])) {
            $sueldo[1] = "00";
        } else {
            $sueldo[1] = str_replace("0.", "", round("." . $sueldo[1], 2));
        }
        $sueldo_letra = convertir_letra($sueldo[0]);
        $sueldo_texto = $sueldo_letra . " PESOS " . $sueldo[1] . "/100 M.N.";

        $fiscal = explode(".", $rutina->neto_fiscal);
        if (!isset($fiscal[1])) {
            $fiscal[1] = "00";
        } else {
            $fiscal[1] = str_replace("0.", "", round("." . $fiscal[1], 2));
        }
        $fiscal_letra = convertir_letra($fiscal[0]);
        $fiscal_texto = $fiscal_letra . " PESOS " . $fiscal[1] . "/100 M.N.";

        // $percepciones = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 0)->get();
        // $deducciones = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 1)->get();
        $emisora = DB::connection('empresa')->select($query);
        //dd($emisora);
        return Pdf::loadView("procesos.calculo-finiquito.kit-baja-pdf", compact('hoy', 'fiscal_texto', 'sueldo_texto', 'alta', 'baja', 'rutina', 'emisora', 'empleado'))->stream($empleado->nombre . $empleado->apaterno . $empleado->amaterno . '_kit.pdf');
    }

    public function iniciaEmpleadoCalculo(Request $request)
    {
        cambiarBase(Session::get('base'));
        $this->empleado = Empleado::find($request->id_empleado_calculo);
        $this->periodo = PeriodosNomina::where('id', $request->id_periodo_calculo)->first();
        $this->ejercicio = $request->ejercicio_calculo;
        //   dd($request->ejercicio_calculo);
        $rutina = DB::connection('empresa')
            ->table('rutinas' . $request->ejercicio_calculo)
            ->where('id_empleado', $request->id_empleado_calculo)
            ->where('id_periodo', $request->id_periodo_calculo)
            ->where('fnq_valor', 1)->get()->keyBy('id_empleado');
        $this->empleado->rutina = $rutina[$request->id_empleado_calculo];
    }

    protected function informacionReporteFiniquito($idperiodo = null, $idempleado = null, $idrutina = null)
    {
        cambiarBase(Session::get('base'));
        $valores = $totales = array();
        if ($idperiodo != null) {
            $this->empleado = Empleado::find($idempleado);
            $this->periodo = PeriodosNomina::where('id', $idperiodo)->first();

            $this->empleado->rutina = DB::connection('empresa')
                ->table('rutinas' . $this->periodo->ejercicio)
                ->where('id_empleado', $this->empleado->id)
                ->where('id_periodo', $this->periodo->id)
                ->where('fnq_valor', 1)->get()->keyBy('id_empleado');
        }
        $empresa = Session::get('empresa');
        $periodo = $this->periodo;
        // $parametros_empresa = Session::get('empresa.parametros')[0];
        $parametros_empresa = DB::connection('empresa')
                ->table('parametros')
                ->first();

        $percepciones = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 0)->get();
        $deducciones = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 1)->get();
        $PercepFiscal = (isset($this->empleado->rutina->total_percepcion_fiscal)) ? floatval($this->empleado->rutina->total_percepcion_fiscal) : 0;
        //dump($PercepFiscal);
        $valores['vacaciones'] = $vacaciones = 0.0;
        $valores['anticipo'] = $anticipo = floatval($parametros_empresa->anticipo);
        $valores['comisionMismo'] = $comisionMismo = floatval($parametros_empresa->comision_mismo_dia);
        $provisionPorcentaje = floatval($parametros_empresa->provision_porcentaje);
        $totales['CuotaFija'] = $CuotaFija = round($this->empleado->rutina->cuota_fija, 2) * $provisionPorcentaje;
        $totales['ExcePa'] = $ExcePa = round($this->empleado->rutina->exce_pa, 2) * $provisionPorcentaje;
        $totales['PreDineroPa'] = $PreDineroPa = round($this->empleado->rutina->pre_dine_patro, 2) * $provisionPorcentaje;
        $CensaVejezObrePatronal = (!empty($this->empleado->rutina->censa_vejez_patron)) ? round($this->empleado->rutina->censa_vejez_patron, 2) : 0.0;
        $totales['GasMediPatron'] = $GasMediPatron = round($this->empleado->rutina->gas_medi_patro, 2) * $provisionPorcentaje;
        $totales['RiesgoTrabajo'] = $RiesgoTrabajo = round($this->empleado->rutina->riesgo_trabajo, 2) * $provisionPorcentaje;
        $totales['InvaVidaPatro'] = $InvaVidaPatro = round($this->empleado->rutina->inva_vida_patro, 2) * $provisionPorcentaje;
        $totales['GuardePresta'] = $GuardePresta = round($this->empleado->rutina->guarde_presta, 2) * $provisionPorcentaje;
        // $totales['CensaVejezPatro'] = $CensaVejezPatro = round($this->empleado->rutina->censa_vejez_patron, 2) * $provisionPorcentaje;
        $totales['CensaVejezPatro'] = $CensaVejezPatro = $CensaVejezObrePatronal * $provisionPorcentaje;

        $totales['InfonavitPatro'] = $InfonavitPatro = round($this->empleado->rutina->infonavit_patro, 2) * $provisionPorcentaje;
        $totales['SarPatron'] = $SarPatron = round($this->empleado->rutina->sar_patron, 2) * $provisionPorcentaje;
        $totales['comisionVariable'] = $comisionVariable = $parametros_empresa->comision_variable;
        $provisionPorcentaje = $parametros_empresa->provision_porcentaje;
        $valores['PocentajeNomina'] = $PocentajeNomina = $parametros_empresa->porcentaje_nomina;
        $valorPrestacionExtra = $parametros_empresa->valor_prestacion_extra;
        $Iva = $parametros_empresa->iva;
        $porcentajenom = $PocentajeNomina / 100;
        //dd(floatval($PercepFiscal),$porcentajenom);
        $totales['errogacion'] = $errogacion =  $PercepFiscal * $porcentajenom;
        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valorseguroGM, sum(pre.valor_plan_espejo) as ValorPlanEspejo 
        from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id 
        left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=1 and id_periodo='$periodo->id'  
        and em.tipo_de_nomina='$periodo->nombre_periodo' and em.id = {$this->empleado->id}";

        $seguros = DB::connection('empresa')->select($queryvalorseguroGM);
        $valorseguroGM = $seguros[0]->valorseguroGM;
        $ValorPlanEspejo = $seguros[0]->ValorPlanEspejo;

        $valores['porcentajeHono'] = $porcentajeHono = $parametros_empresa->porcentaje_honorarios;
        $ConcepFacturacion = $parametros_empresa->concepto_facturacion;
        /*        $provision_obrero = $parametros_empresa['provision_obrero'];
*/
        $FecHr = date('Y-m-d H:i:s');

        //dump($empresa,$this->empleado,$periodo,$parametros_empresa,$percepciones,$deducciones);exit();

        //concepto sueldo
        $concepto_sueldo = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('rutinas', 'SDO')->where('estatus', 1)
            ->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->first();

        $concepto_faltas = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')
            ->where(function ($query) {
                $query->where('nombre_concepto', 'FALTAS')
                    ->orWhere('nombre_concepto', 'faltas')
                    ->orWhere('nombre_concepto', 'FLTASS');
            })
            //->where('estatus', 1)
            //->where('activo_en_nomina', 1)
            ->first();
        //dd($concepto_faltas);
        $valores['conceptoFaltas'] = $concepto_faltas->id;

        //concepto PVAC
        $concepto_pvac = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('rutinas', 'PVAC')->where('estatus', 1)
            ->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->first();
        //dd($concepto_sueldo,$concepto_pvac,$concepto_pvac->id);

        if (isset($concepto_pvac)) {
            $columnapvac = 'total' . $concepto_pvac->id;
            $pagoprimavaca = (isset($this->empleado->rutina->$columnapvac)) ? floatval($this->empleado->rutina->$columnapvac) : 0;
            $valores['conceptoISR'] = $concepto_pvac->id;
        } else {
            $valores['conceptoISR'] = $pagoprimavaca = 0;
        }
        $totales['pagoprimavaca'] = $pagoprimavaca;
        //dump($pagoprimavaca,$columnapvac);exit();

        //subsidio
        $valorSubsidio = $this->empleado->rutina->subsidio * -1;
        //dd($PercepFiscal,$this->empleado->rutina->subsidio, $valorSubsidio, $pagoprimavaca);
        $totales['netoFiscalreal'] = $netoFiscalreal = ($PercepFiscal + $valorSubsidio) - $pagoprimavaca;
        //dump($netoFiscalreal,$PercepFiscal, $valorSubsidio, $pagoprimavaca);

        //dd( $netoFiscalreal , $anticipo , $vacaciones , $pagoprimavaca , $comisionMismo);
        $totales['TotalpagarNomina'] = $TotalpagarNomina = $netoFiscalreal + $anticipo + $vacaciones + $pagoprimavaca + $comisionMismo;
        $valorhonorarios = $porcentajeHono / 100;
        //dump("honorarios", $valorhonorarios);
        $totales['pagoHonorarios'] = $pagoHonorarios = $TotalpagarNomina * $valorhonorarios;
        // dd($valorPrestacionExtra,$valorseguroGM,$ValorPlanEspejo);
        $totales['prestacionesExtras'] = $prestacionesExtras = (floatval($valorPrestacionExtra) * 1) + $valorseguroGM + $ValorPlanEspejo;
        $totales['total'] = $total = $prestacionesExtras + $CuotaFija + $ExcePa + $PreDineroPa + $GasMediPatron + $RiesgoTrabajo + $InvaVidaPatro + $GuardePresta + $SarPatron + $CensaVejezPatro + $InfonavitPatro + $errogacion;


        $totales['subtotal'] = $subtotal = $TotalpagarNomina + $pagoHonorarios + $total;
        $totales['iva'] = $iva = $subtotal * $Iva;
        $totales['totalmayor'] = $totalmayor = $subtotal + $iva;

        $totales['cargasocial'] = $cargasocial = $CuotaFija + $ExcePa + $PreDineroPa + $GasMediPatron + $RiesgoTrabajo + $InvaVidaPatro + $GuardePresta;
        //dd($TotalpagarNomina,$TotalpagarNomina);
        $totales['valorcomision'] = $valorcomision = $TotalpagarNomina * ($TotalpagarNomina / 100);
        $SarPatron + $CensaVejezPatro + $InfonavitPatro;
        $totales['subtotal02'] = $totales['TotalpagarNomina'] + $totales['cargasocial'] + $totales['errogacion'] + $totales['valorcomision'];
        //dd($totales['TotalpagarNomina'] , $totales['cargasocial'] , $totales['errogacion'] , $totales['valorcomision'], $totales['subtotal02']);
        $totales['iva02'] = $totales['subtotal02'] * $Iva;
        //dump($totales['iva02'],$totales['subtotal02'], $totales['iva']);
        $totales['totalmayor02'] = $totales['subtotal02'] + $totales['iva02'];


        //dump($totales['TotalpagarNomina'] , $totales['cargasocial'] , $totales['errogacion'] , $totales['valorcomision'],$totales['subtotal02'], $totales['iva']);
        return array(
            'percepciones' => $percepciones,
            'deducciones'  => $deducciones,
            'valores'      => $valores,
            'totales'      => $totales
        );
        //dump($valorSubsidio,$netoFiscalreal);exit();
    }

    protected function informacionReporteFiniquitoLiquidacion($idperiodo = null, $idempleado = null)
    {
        cambiarBase(Session::get('base'));
        $valores = $totales = array();
        if ($idperiodo != null) {
            $this->empleado = Empleado::find($idempleado);
            $this->periodo = PeriodosNomina::where('id', $idperiodo)->first();

            $this->empleado->rutina = DB::connection('empresa')
                ->table('rutinas' . $this->periodo->ejercicio)
                ->where('id_empleado', $this->empleado->id)
                ->where('id_periodo', $this->periodo->id)
                ->where('fnq_valor', 1)->get()->keyBy('id_empleado');

            $this->rutina = Rutina::where('id_empleado', $this->empleado->id)
                ->where('id_periodo', $this->periodo->id)
                ->where('ejercicio', intval($this->periodo->ejercicio))
                ->where('fnq_valor', 1)
                ->with('valores_conceptos')
                ->first();

            // dump($this->periodo->ejercicio,$this->empleado->id,$this->periodo->id);
            //dd($this->empleado,$this->empleado->rutina,$this->rutina);
        }
        $empresa = Session::get('empresa');
        $periodo = $this->periodo;
        $parametros_empresa =  DB::connection('empresa') ->table('parametros')->first();
        
        //$percepciones = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 0)->get();
        //$deducciones = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('finiquito', 1)->where('tipo', 1)->get();
        $PercepFiscal = floatval($this->rutina->total_percepcion_fiscal);
        //dump($PercepFiscal);
        $valores['vacaciones'] = $vacaciones = 0.0;
        $valores['anticipo'] = $anticipo = floatval($parametros_empresa->anticipo);
        $valores['comisionMismo'] = $comisionMismo = floatval($parametros_empresa->comision_mismo_dia);
        $provisionPorcentaje = floatval($parametros_empresa->provision_porcentaje);
        $totales['CuotaFija'] = $CuotaFija = round($this->rutina->cuota_fija, 2) * $provisionPorcentaje;
        $totales['ExcePa'] = $ExcePa = round($this->rutina->exce_pa, 2) * $provisionPorcentaje;
        $totales['PreDineroPa'] = $PreDineroPa = round($this->rutina->pre_dine_patro, 2) * $provisionPorcentaje;
        $CensaVejezObrePatronal = (!empty($this->rutina->censa_vejez_patron)) ? round($this->rutina->censa_vejez_patron, 2) : 0.0;
        $totales['GasMediPatron'] = $GasMediPatron = round($this->rutina->gas_medi_patro, 2) * $provisionPorcentaje;
        $totales['RiesgoTrabajo'] = $RiesgoTrabajo = round($this->rutina->riesgo_trabajo, 2) * $provisionPorcentaje;
        $totales['InvaVidaPatro'] = $InvaVidaPatro = round($this->rutina->inva_vida_patro, 2) * $provisionPorcentaje;
        $totales['GuardePresta'] = $GuardePresta = round($this->rutina->guarde_presta, 2) * $provisionPorcentaje;
        // $totales['CensaVejezPatro'] = $CensaVejezPatro = round($this->empleado->rutina->censa_vejez_patron, 2) * $provisionPorcentaje;
        $totales['CensaVejezPatro'] = $CensaVejezPatro = $CensaVejezObrePatronal * $provisionPorcentaje;

        $totales['InfonavitPatro'] = $InfonavitPatro = round($this->rutina->infonavit_patro, 2) * $provisionPorcentaje;
        $totales['SarPatron'] = $SarPatron = round($this->rutina->sar_patron, 2) * $provisionPorcentaje;
        $totales['comisionVariable'] = $comisionVariable = $parametros_empresa->comision_variable;
        $provisionPorcentaje = $parametros_empresa->provision_porcentaje;
        $valores['PocentajeNomina'] = $PocentajeNomina = $parametros_empresa->porcentaje_nomina;
        $valorPrestacionExtra = $parametros_empresa->valor_prestacion_extra;
        $Iva = $parametros_empresa->iva;
        $porcentajenom = $PocentajeNomina / 100;
        //dd(floatval($PercepFiscal),$porcentajenom);
        $totales['errogacion'] = $errogacion =  $PercepFiscal * $porcentajenom;

        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valorseguroGM, sum(pre.valor_plan_espejo) as ValorPlanEspejo 
        from rutinas ru 
        join empleados em on ru.id_empleado = em.id 
        left join prestaciones_extras pre on em.id = pre.id_empleado 
        where ru.fnq_valor=1 
        and ejercicio = " . $periodo->ejercicio . "
        and id_periodo='$periodo->id'  
        and em.tipo_de_nomina='$periodo->nombre_periodo' 
        and em.id = {$this->empleado->id}";

        $seguros = DB::connection('empresa')->select($queryvalorseguroGM);
        $valorseguroGM = $seguros[0]->valorseguroGM;
        $ValorPlanEspejo = $seguros[0]->ValorPlanEspejo;

        $valores['porcentajeHono'] = $porcentajeHono = $parametros_empresa->porcentaje_honorarios;
        $ConcepFacturacion = $parametros_empresa->concepto_facturacion;
        /*        $provision_obrero = $parametros_empresa['provision_obrero'];
*/
        $FecHr = date('Y-m-d H:i:s');

        //dump($empresa,$this->empleado,$periodo,$parametros_empresa,$percepciones,$deducciones);exit();

        //concepto sueldo
        $concepto_sueldo = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('rutinas', 'SDO')->where('estatus', 1)
            ->where('file_rool', '!=', 0)
            ->where('file_rool', '<=',  249)->first();

        $concepto_faltas = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')
            ->where(function ($query) {
                $query->where('nombre_concepto', 'FALTAS')
                    ->orWhere('nombre_concepto', 'faltas')
                    ->orWhere('nombre_concepto', 'FLTASS');
            })
            //->where('estatus', 1)
            //->where('activo_en_nomina', 1)
            ->first();
        $valores['conceptoFaltas'] = $concepto_faltas->id;

        //concepto PVAC
        $concepto_pvac = DB::connection('empresa')->table('conceptos_nomina')
            ->select('id')->where('rutinas', 'PVAC')->where('estatus', 1)
            ->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->first();
        //dd($concepto_sueldo,$concepto_pvac,$concepto_pvac->id);

        if (isset($concepto_pvac)) {
            $columnapvac = 'total' . $concepto_pvac->id;
            $pagoprimavaca = (isset($this->empleado->rutina->$columnapvac)) ? floatval($this->empleado->rutina->$columnapvac) : 0;
            $valores['conceptoISR'] = $concepto_pvac->id;
        } else {
            $valores['conceptoISR'] = $pagoprimavaca = 0;
        }
        $totales['pagoprimavaca'] = $pagoprimavaca;
        //dump($pagoprimavaca,$columnapvac);exit();

        //subsidio
        $valorSubsidio = $this->rutina->subsidio * -1;
        //dd($PercepFiscal,$this->empleado->rutina->subsidio, $valorSubsidio, $pagoprimavaca);
        $totales['netoFiscalreal'] = $netoFiscalreal = ($PercepFiscal + $valorSubsidio) - $pagoprimavaca;
        //dump($netoFiscalreal,$PercepFiscal, $valorSubsidio, $pagoprimavaca);

        //dd( $netoFiscalreal , $anticipo , $vacaciones , $pagoprimavaca , $comisionMismo);
        $totales['TotalpagarNomina'] = $TotalpagarNomina = $netoFiscalreal + $anticipo + $vacaciones + $pagoprimavaca + $comisionMismo;
        $valorhonorarios = $porcentajeHono / 100;
        //dump("honorarios", $valorhonorarios);
        $totales['pagoHonorarios'] = $pagoHonorarios = $TotalpagarNomina * $valorhonorarios;
        $totales['prestacionesExtras'] = $prestacionesExtras = (floatval($valorPrestacionExtra) * 1) + $valorseguroGM + $ValorPlanEspejo;
        $totales['total'] = $total = $prestacionesExtras + $CuotaFija + $ExcePa + $PreDineroPa + $GasMediPatron + $RiesgoTrabajo + $InvaVidaPatro + $GuardePresta + $SarPatron + $CensaVejezPatro + $InfonavitPatro + $errogacion;


        $totales['subtotal'] = $subtotal = $TotalpagarNomina + $pagoHonorarios + $total;
        $totales['iva'] = $iva = $subtotal * $Iva;
        $totales['totalmayor'] = $totalmayor = $subtotal + $iva;

        $totales['cargasocial'] = $cargasocial = $CuotaFija + $ExcePa + $PreDineroPa + $GasMediPatron + $RiesgoTrabajo + $InvaVidaPatro + $GuardePresta +
            //dd($TotalpagarNomina, $comisionVariable);
            $totales['valorcomision'] = $valorcomision = $TotalpagarNomina * (floatval($comisionVariable) / 100);
        $SarPatron + $CensaVejezPatro + $InfonavitPatro;
        $totales['subtotal02'] = $totales['TotalpagarNomina'] + $totales['cargasocial'] + $totales['errogacion'] + $totales['valorcomision'];
        //dd($totales['TotalpagarNomina'] , $totales['cargasocial'] , $totales['errogacion'] , $totales['valorcomision'], $totales['subtotal02']);
        $totales['iva02'] = $totales['subtotal02'] * $Iva;
        //dump($totales['iva02'],$totales['subtotal02'], $totales['iva']);
        $totales['totalmayor02'] = $totales['subtotal02'] + $totales['iva02'];


        //dump($totales['TotalpagarNomina'] , $totales['cargasocial'] , $totales['errogacion'] , $totales['valorcomision'],$totales['subtotal02'], $totales['iva']);
        return array(
            'valores'   => $valores,
            'totales'   => $totales
        );
        //dump($valorSubsidio,$netoFiscalreal);exit();
    }

    function exportarFiniquito(Request $request)
    {
        $this->iniciaEmpleadoCalculo($request);
        $empleado = $this->empleado;
        $periodo = $this->periodo;
        $datos = $this->informacionReporteFiniquito();
        $datos['periodo'] = $this->periodo;
        $datos['empleado'] = $this->empleado;
        return Excel::download(new FiniquitoExport($datos), "Finiquito_{$this->periodo->id}_" . date('d-m-Y') . ".xlsx");
    }

    public function vistaCalculoFiniquitoVer(Request $request)
    {

        cambiarBase(Session::get('base'));

        $empleado = Empleado::where('id', $request->id_empleado_calculo)->with('departamento', 'categoria')->first();
        $periodo = PeriodosNomina::where('id', $request->id_periodo_calculo)->first();
        $datos = $this->informacionReporteFiniquitoLiquidacion($periodo->id, $empleado->id);
        $rutina = $this->rutina;
        $valores = $datos['valores'];
        $totales = $datos['totales'];
        // dd($rutina);
        return view('procesos.calculo-finiquito.ver-finiquito', compact('empleado', 'periodo', 'rutina', 'valores', 'totales'));
        // $this->calculoFiniquito($request);
        // $this->GuardarEmpleadoRutina();
        $this->iniciaEmpleadoCalculo($request);
        //dd($this->empleado);

        $empleado = $this->empleado;
        $periodo = $this->periodo;
        // $parametros_empresa = Session::get('empresa.parametros')[0];
        $fecha_inicial_periodo = Carbon::parse($this->periodo->fecha_inicial_periodo);
        $fecha_baja_final = Carbon::parse($this->empleado->fecha_baja);
        $dias_pagados = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;

        $datos = $this->informacionReporteFiniquito();
        $percepciones = $datos['percepciones'];
        $deducciones  = $datos['deducciones'];
        $valores      = $datos['valores'];
        $totales      = $datos['totales'];
        $colfaltas    = 'valor' . $valores['conceptoFaltas'];
        $faltas       = (isset($this->empleado->rutina->$colfaltas)) ? $this->empleado->rutina->$colfaltas : 0;
        $DiasNom      = intval($dias_pagados) - intval($faltas);
        $ver = 1;

        /*  Rutina::where('id_empleado',$request->id_empleado_calculo)
        ->where('id_periodo',$request->id_periodo_calculo)
        ->where('ejercicio', $request->ejercicio_calculo);*/

        dd("Hola");
        // return view(
        //     'procesos.calculo-finiquito.calculo_finiquito',
        //     compact('ver', 'empleado', 'periodo', 'percepciones', 'deducciones', 'dias_pagados', 'valores', 'totales', 'DiasNom')
        // )->with('tipo_alerta', 'success')
        //     ->with('mensaje', 'El finiquito se calculó automaticamente con éxito');
    }

    public function firmaFiniquito(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado = Empleado::find($request->id);


        if ($empleado->update(['estatus_firma_finiquito' => $request->firma])) {
            if ($request->firma == 2) {
                $empleado = Empleado::find($request->id);
                $rutina = Rutina::where('id_empleado', $request->id)
                    ->where('id_periodo', $request->idperiodo)
                    ->where('ejercicio', intval($request->ejercicio))
                    ->where('fnq_valor', 1)
                    ->with('valores_conceptos')
                    ->first();

                $fbaja = (!empty($empleado->fecha_baja) && $empleado->fecha_baja != '0000-00-00') ? new DateTime($empleado->fecha_baja) : new DateTime();
                $fantiguedad = (!empty($empleado->fecha_antiguedad)) ? new DateTime($empleado->fecha_antiguedad) : null;
                $falta = (!empty($empleado->fecha_alta)) ? new DateTime($empleado->fecha_alta) : null; // fecha alta
                $diaActual = new DateTime();
                $diaActual->setTime(00, 00, 00);

                if ($diaActual > $fbaja) {
                    $diff = $diaActual->diff($fbaja);
                    $dias_diferencia = $diff->days;
                    $year = $diff->y;
                    $IndmAno = round(($year * ($empleado->salario_diario * 20)), 2);
                    $salarioCaido = round(($empleado->salario_diario * $dias_diferencia), 2);
                } else {
                    $IndmAno = 0;
                    $salarioCaido = 0;
                }

                $fbajaa = (!empty($empleado->fecha_baja)  && $empleado->fecha_baja != '0000-00-00') ? new DateTime($empleado->fecha_baja) : new DateTime();
                $fantiguedad = (!empty($empleado->fecha_antiguedad)) ? new DateTime($empleado->fecha_antiguedad) : null;
                $falta = (!empty($empleado->fecha_alta)) ? new DateTime($empleado->fecha_alta) : null;

                if(Session::get('usuarioPermisos')['id_usuario']==64){
                   //dd($rutina,$request->ejercicio,$request->idperiodo);
                }

                $demanda = Demanda::insertGetId([
                    'id_empleado' => $request->id,
                    'periodo' => $rutina->id_periodo,
                    'ejercicio' => $rutina->ejercicio,
                    'folio' => '',
                    'fecha_baja' => $fbaja->format('Y-m-d') . ' 00:00:00',
                    'importe' => (!empty($rutina->neto_fiscal)) ? $rutina->neto_fiscal : 0,
                    'salario' => $empleado->salario_diario,
                    'fecha_antiguedad' => $fantiguedad->format('Y-m-d') . ' 00:00:00',
                    'fecha_alta' => $falta->format('Y-m-d') . ' 00:00:00',
                    'indemnizacion_anio' => $IndmAno,
                    'salario_caido' => $salarioCaido,
                    'estatus' => 1,
                    'created_at' => $diaActual->format('Y-m-d') . ' 00:00:00'
                ]);


                session()->flash('success', 'Se creo una demanda para el empleado ' . $empleado->nombre . " " . $empleado->apaterno . " " . $empleado->amaterno);

                return redirect()->route('procesos.historico');

            }
        }

        session()->flash('success', 'El finiquito del empleado ' . $empleado->nombre . " " . $empleado->apaterno . " " . $empleado->amaterno . " ha sido firmado");

        return redirect()->route('procesos.historico');

    }
}