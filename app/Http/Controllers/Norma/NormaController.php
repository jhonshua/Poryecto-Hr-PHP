<?php

namespace App\Http\Controllers\norma;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use DataTables;
use DateTime;
use Illuminate\Http\Request;
use App\Models\PeriodoImplementacion;
use App\Models\RazonSocial;
use App\Models\Sede;
use App\Models\Empleado;
use App\Models\Excento;
use App\Models\Actividad;
use App\Models\Encargado;
use App\Models\Cuestionario;
use App\Models\Trabajador;
use App\Models\PeriodoNorma;
use App\Models\CuestionarioTrabajador;
use App\Models\BloqueCuestionario;
use App\Models\EmpleadoLogin;
use App\Models\Empresa;
use App\Models\Interpretacion;
use App\Http\Controllers\PonderadorController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use App\Imports\importNormaTrabajadoresExternos;
use Illuminate\Support\Facades\Mail;
use App\Mail\enviarMail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\exportEmpleadosSinEncuesta;
use Storage;

class NormaController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const ACTIVIDAD_CREADA = 1;
    public $trabajador_resultado_general = array();
    protected const ACTIVIDAD_ELIMINADA = 0;
    protected const GUIA_REFERENCIA_UNO = 1;
    protected const GUIA_REFERENCIA_DOS = 2;
    protected const GUIA_REFERENCIA_TRES = 3;
    protected const CUESTIONARIO_AGREGADO = 0;

    public $respuestas = array(
        0 => array(
            1 => "SI",
            0 => "NO"
        ),
        1 => array(
            0 => "Siempre",
            1 => "Casi siempre",
            2 => "Algunas veces",
            3 => "Casi nunca",
            4 => "Nunca"
        ),
        2 => array(
            4 => "Siempre",
            3 => "Casi siempre",
            2 => "Algunas veces",
            1 => "Casi nunca",
            0 => "Nunca"
        )
    );

    public $catalogos = array(

        "sexo" => array(
            18 => "Masculino",
            19 => "Femenino",
            20 => "Otro"
        ),
        "edad" => array(
            21 => "15-19",
            22 => "20-24",
            23 => "25-29",
            24 => "30-34",
            25 => "35-39",
            26 => "40-44",
            27 => "45-49",
            28 => "50-54"
        ),
        "estado_civil" => array(
            29 => "Casado",
            30 => "Soltero",
            31 => "Unión libre",
            32 => "Divorciado",
            33 => "Viudo"
        ),
        "nivel_estudios" => array(
            34 => "Secundaria terminada",
            35 => "Preparatoria o Bachillerato terminada",
            41 => "Secundaria Incompleta",
            37 => "Técnico Superior terminada",
            38 => "Licenciatura terminada",
            39 => "Maestría terminada",
            40 => "Doctorado terminada",
            42 => "Preparatoria o Bachillerato Incompleta",
            43 => "Técnico Superior Incompleta",
            44 => "Licenciatura Incompleta",
            45 => "Maestría Incompleta",
            46 => "Doctorado Incompleta"
        ),
        "tipo_puesto" => array(
            47 => "Operativo",
            48 => "Supervisor",
            49 => "Profesional o técnico",
            50 => "Gerente"
        ),
        "tipo_contratacion" => array(
            51 => "Por proyecto",
            52 => "Tiempo indeterminado",
            53 => "Por tiempo determinado (temporal)",
            54 => "Honorarios"
        ),
        "tipo_personal" => array(
            55 => "Sindicalizado",
            56 => "Confianza",
            57 => "Ninguno"
        ),
        "tipo_jornada" => array(
            58 => "Fijo diurno (entre las 6:00 y 20:00 hrs)",
            59 => "de 8:00 a 14:00 hrs",
            60 => "de 9:00 a 19:00 hrs",
            61 => "de 10:00 a 20:00 hrs"
        ),
        "rotacion_turnos" => array(
            62 => "Si",
            63 => "No"
        ),
        "experiencia_puesto_actual" => array(
            64 => "Menos de 3 meses",
            65 => "Entre 5 a 9 años",
            66 => "Entre 6 meses y 1 año",
            67 => "Más de 9 años",
            68 => "Entre 1 a 4 años"
        ),
        "experiencia_laboral" => array(
            69 => "Menos de 6 meses",
            70 => "Entre 6 meses y 1 año",
            71 => "Entre 1 a 4 años",
            72 => "Entre 5 a 9 años",
            73 => "Entre 10 a 14 años",
            74 => "Entre 15 a 19 años",
            75 => "Entre 20 a 24 años",
            76 => "25 años o más"
        )
    );

    public function normaTabla(Request $request)
    {
        elegirBase();
        if ($request->ajax()) {
            if (Schema::connection('empresa')->hasTable('sedes')) {
                $implementacion = PeriodoImplementacion::select('id', 'fecha_inicio', 'fecha_fin', 'sede', 'razon_social')->with(['razon_social_asignada', 'sede_asignada', 'encargados', 'actividad_formulario_trabajadores'])->orderBY('id', 'desc');
            } else {
                $implementacion = PeriodoImplementacion::select('id', 'fecha_inicio', 'fecha_fin', 'sede', 'razon_social')->with(['razon_social_asignada', 'encargados', 'actividad_formulario_trabajadores'])->orderBY('id', 'desc');
            }

            return DataTables::of($implementacion->get())
                ->addColumn('fecha_inicio', function ($row) {
                    return Carbon::parse($row->fecha_inicio)->format('d/M/y');
                })
                ->addColumn('fecha_fin', function ($row) {
                    return Carbon::parse($row->fecha_fin)->format('d/M/y');
                })
                ->addColumn('estatus', function ($row) {
                    $inicio = new DateTime($row->fecha_inicio);
                    $fin = new DateTime($row->fecha_fin);
                    $hoy = new DateTime();

                    if ($fin < $hoy) $estatus = "<span class='text-secondary font-weight-bold'>CONCLUIDO</span>";
                    else if ($hoy >= $inicio && $hoy <= $fin) $estatus = "<span class='text-success font-weight-bold'>EN PROCESO</span>";
                    else if ($hoy < $inicio) $estatus = "<span class='text-PRIMARY font-weight-bold'>EN PROCESO</span>";

                    return $estatus;
                })->addColumn('encargados', function ($row) {

                    if (!empty($row->encargados)) {
                        $encargados = $row->encargados->toArray();
                        return $encargados;
                    } else {
                        return "";
                    }
                    /*  $e = "";
                    $tot = count($row->encargados);
                    $cont = 0;
                    foreach ($row->encargados as $encargado) {
                        $e .= $encargado->nombre;
                        if ($cont < ($tot - 1)) {
                            $e .= ", ";
                        }
                        $cont++;
                    }
                    return $e; */
                })->addColumn('sede', function ($row) {
                    if (!empty($row->sede_asignada->nombre) && $row->sede_asignada->nombre != "" && $row->sede_asignada->nombre != null) {
                        return $row->sede_asignada->nombre;
                    }
                    return "-";
                })->addColumn('participantes', function ($row) {
                    $actividad_form = $row->actividad_formulario_trabajadores->first();
                    if (!empty($actividad_form)) {
                        $participantes = $actividad_form->formularioConTrabajadores->trabajadorCuestionarioPeriodo->groupBy('id')->toArray();
                        return count($participantes);
                    } else {
                        return "-";
                    }
                    return "-";
                })->addColumn('total_excentos', function ($row) {
                    $actividad_form = $row->actividad_formulario_trabajadores->first();
                    if (!empty($actividad_form)) {
                        $excento = $actividad_form->formularioConTrabajadores->excentos->toArray();
                        return $excento;
                    } else {
                        return "";
                    }
                })->addColumn('excentos', function ($row) {
                    $actividad_form = $row->actividad_formulario_trabajadores->first();
                    if (!empty($actividad_form)) {
                        $excento = $actividad_form->formularioConTrabajadores->excentos->toArray();

                        return $excento;
                    } else {
                        return "";
                    }
                })->addColumn('razon_social', function ($row) {
                    $base = Session::get('base');
                    $razon = Empresa::select('razon_social')->where('base', $base)->get();
                    if (!empty($razon)) {
                        foreach($razon as $ra){
                            return $ra->razon_social;
                        }
                    }

                    /*  if (!empty($row->razon_social_asignada->razon_social) && $row->razon_social_asignada->razon_social != "" && $row->razon_social_asignada->razon_social != null) {
                        return $row->razon_social_asignada->razon_social;
                    }  */

                    return "-";
                })->addColumn('acciones', function ($row) {
                    $inicio = new DateTime($row->fecha_inicio);
                    $fin = new DateTime($row->fecha_fin);
                    $hoy = new DateTime();
                    $btn = '';
                    $date = Carbon::now();

                    $date = $date->format('d-m-Y');

                    if ($hoy < $inicio) {
                    }
                    // $btn .= '<a data-enlace="' . route('norma.actividades') . '" data-implementacion="' . $row->id . '" class="enviaf" data-toggle="tooltip" data-placement="right" title=" Ver actividades"><img src="/img/icono-actividades.png" class="button-style-icon"></a>

                    $btn .= '<a data-enlace="' . route('norma.actividades') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" data-toggle="tooltip" data-placement="right" title=" Ver actividades"><img src="' . asset('/img/icono-actividades.png') . '" class="button-style-icon text-center"></a>
                    <a data-enlace="' . route('norma.actividades.diagrama') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" data-toggle="tooltip" data-placement="right" title=" Ver calendario"><img src="' . asset('/img/icono-diagrama.png') . '" class="button-style-icon text-center"></a>';


                    if (count($row->actividad_formulario_trabajadores)) {
                        $btn .= '<div class="btn-group"><a data-enlace="' . route('norma.implementacion.lista.empleados') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" data-toggle="tooltip" data-placement="right" title="Lista de empleados"><img src="/img/icono-lista.png" class="button-style-icon text-center"></a>';
                        $empleadoss = $row->actividad_formulario_trabajadores->first()->formularioConTrabajadores->trabajadorCuestionarioPeriodo->toArray();

                        if ($hoy > $fin) {
                            if (count($empleadoss) <= 15) {
                                $btn .= '<a data-enlace="' . route('norma.implementacion.diagnostico.menosdiesiseis') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" title="Diagnostico"><img src="/img/icono-diagnostico.png" class="button-style-icon text-center"></a>';
                            } else if ($empleadoss > 15) {
                                $btn .= '<a data-enlace="' . route('norma.implementacion.reporte') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" data-toggle="tooltip" data-placement="right" title="Reporte"><img src="' . asset('/img/icono-reporte.png') . '" class="button-style-icon"></a>';
                                $btn .= '<a data-enlace="' . route('norma.implementacion.diagnostico') . '" data-implementacion="' . $row->id . '" class="enviaf text-center" data-toggle="tooltip" data-placement="right" title="Diagnostico"><img src="' . asset('/img/icono-diagnostico.png') . '" class="button-style-icon"></a>';
                                // $btn .= '<a data-enlace="'.route('norma.implementacion.diagnostico.menosdiesiseis').'" data-implementacion="'.$row->id.'" class="enviaf btn btn-warning btn-sm mr-2" data-toggle="tooltip" data-placement="right" title="Diagnostico"><i class="fas fa-notes-medical"></i></a>';
                            } else {
                                $btn .= count($empleadoss);
                            }
                        }
                    }
                    return $btn . '</div>';
                })->rawColumns(['acciones', 'estatus', 'encargados'])->make(true);
        }

        if (!Schema::connection('empresa')->hasTable('periodos_implementacion')) {
            return view("norma.norma-nueva");
        }
        $razones_sociales = RazonSocial::where('estatus', 1)->get();
        $sedes = collect(array());

        $sedes = \DB::connection('empresa')->table('sedes')->where('estatus', 1)->get();

       
        
        return view('norma.norma-tabla', compact('razones_sociales', 'sedes'));
    }

    public function crear(Request $request)
    {
        // cambiarBase(Session::get('base'));
        elegirBase();
        $inicio = new DateTime($request->fecha_inicio);
        $fin = new DateTime($request->fecha_fin);
        $actual = new DateTime();
        $correos = $encargados = array();

        $id = PeriodoImplementacion::insertGetId(
            ['fecha_inicio' => $inicio->format('Y-m-d') . ' 00:00:00', 'fecha_fin' => $fin->format('Y-m-d') . ' 23:59:59', 'create_at' => $actual->format('Y-m-d h:i'), 'sede' => $request->sede, 'razon_social' => $request->razon_social]
        );

        if (!empty($request->nombre1) && !empty($request->correo1)) {
            $encargados[] = array("nombre" => $request->nombre1, "correo" => $request->correo1, "idperiodo_implementacion" => $id);
            $correos[] = $request->correo1;
        }
        if (!empty($request->nombre2) && !empty($request->correo2)) {
            $encargados[] = array("nombre" => $request->nombre2, "correo" => $request->correo2, "idperiodo_implementacion" => $id);
            $correos[] = $request->correo2;
        }
        if (!empty($request->nombre3) && !empty($request->correo3)) {
            $encargados[] = array("nombre" => $request->nombre3, "correo" => $request->correo3, "idperiodo_implementacion" => $id);
            $correos[] = $request->correo3;
        }
        if (!empty($request->nombre4) && !empty($request->correo4)) {
            $encargados[] = array("nombre" => $request->nombre4, "correo" => $request->correo4, "idperiodo_implementacion" => $id);
            $correos[] = $request->correo4;
        }
        if (!empty($request->nombre5) && !empty($request->correo5)) {
            $encargados[] = array("nombre" => $request->nombre5, "correo" => $request->correo5, "idperiodo_implementacion" => $id);
            $correos[] = $request->correo5;
        }

        if (Encargado::insert($encargados)) {

            try {
                $titulo = 'Implementación Norma 035';
                $cuerpo = "Usted ha sido asignado como encargado en la implementación de la Norma 035 para la empresa " . Session::get('empresa')['razon_social'] . " <br>Dicho periodo comienza el " . $inicio->format('d-m-Y') . " y termina el " . $fin->format('d-m-Y');
                $btnUrl = "";
                $btnTxt = "";
                foreach ($correos as $correo) Mail::to($correo)->later(now()->addSeconds(5), new enviarMail($titulo, $cuerpo, $btnUrl, $btnTxt));
            }catch (\Exception $e){

            }

        }
        return response()->json(['ok' => 1, 'implementacion' => $id]);

        if (Schema::connection('empresa')->hasTable('sedes')) {
            $sedes = Sede::where('estatus', 1)->get();
        }
        return view('norma.norma-tabla', compact('razones_sociales', 'sedes'));
    }

    public function normaNueva()
    {
        return view('norma.norma-nueva');
    }

    public function normaActividades(Request $request)
    { // DataTable de actividades
        validarMetodoPost($request, 'norma.normaTabla');

        if ($request->implementacion) {
            $implementacion = $request->implementacion;
            elegirBase();
            if ($request->ajax()) {
                //echo $request->implementacion;
                $actividades = Actividad::select('id', 'descripcion', 'fecha_inicio', 'fecha_fin', 'notificacion', 'apertura_formulario', 'estatus')->where("idperiodo_implementacion", "=", $implementacion)->where('estatus', SELF::ACTIVIDAD_CREADA)->orderBy('fecha_inicio', 'desc');
                //print_r($actividades->get());
                return Datatables::of($actividades->get())
                    ->addColumn('fecha_inicio', function ($row) {
                        return Carbon::parse($row->fecha_inicio)->format('d/M/y');
                    })
                    ->addColumn('fecha_fin', function ($row) {
                        return Carbon::parse($row->fecha_fin)->format('d/M/y');
                    })
                    ->addColumn('notificacion', function ($row) {
                        if ($row->notificacion == 1) $notificacion = "<span class='text-success font-weight-bold'>SI</span>";
                        else $notificacion = "<span class='text-secundary font-weight-bold'>NO</span>";
                        return $notificacion;
                    })
                    ->addColumn('apertura_formulario', function ($row) {

                        if (!empty($row->apertura_formulario)) $apertura = "<span class='text-success font-weight-bold'>APERTURA</span>";
                        else  $apertura = "<span class='text-secondary font-weight-bold'>NO</span>";
                        return $apertura;
                    })
                    ->addColumn('acciones', function ($row) {
                        $inicio = new DateTime($row->fecha_inicio);
                        $fin = new DateTime($row->fecha_fin);
                        $hoy = new DateTime();
                        $btn = "";
                        $btn .= '<a data-actividad="' . $row->id . '" class="enviaf text-center"  data-toggle="modal" data-target="#actividadModal"  title="Editar"><img src="/img/icono-editar.png" class="button-style-icon"></a>';

                        if ($hoy < $inicio) {
                            $btn .= '<a href="#" data-id="' . $row->id . '" class="borrar btn" alt="Eliminar Actividad" title="Eliminar Actividad"><img src="/img/eliminar.png" class="button-style-icon"></a>';
                        } else {
                            $btn .= '<a href="#" class="disabled borrar btn " alt="Eliminar Actividad" title="Eliminar Actividad"><img src="/img/eliminar.png" class="button-style-icon"></a>';
                        }


                        return $btn;
                    })
                    ->rawColumns(['acciones', 'notificacion', 'apertura_formulario'])
                    ->make(true);
            } else {
                elegirBase();
                if (Schema::connection('empresa')->hasTable('sedes')) {
                    $datosImplementacion = PeriodoImplementacion::with(['sede_asignada', 'razon_social_asignada'])->find($implementacion);
                } else {
                    $datosImplementacion = PeriodoImplementacion::with(['razon_social_asignada'])->find($implementacion);
                    $datosImplementacion->sede_asignada = "";
                }

                return view("norma.implementacion.norma-actividades", compact("datosImplementacion"));
            }
        }
        return view('norma.norma-tabla');
    }

    public function cuestionarioPdf($id)
    {
        $tipo_cuestionario = $id;
        $cuestionario = array();
        $bloques = array();
        if ($id != 4) {
            elegirBase();
            $cuestionario = Cuestionario::where('id', $id)->get();
            $bloques = BloqueCuestionario::where('idcuestionario', '=', $id)->with('preguntas')->get();
        }

        return  PDF::loadView("norma.implementacion.impresion-cuestionario", compact('cuestionario', 'bloques', 'tipo_cuestionario'))->stream('cuestionario' . $id . '.pdf');
    }

    public function reporteInicio(Request $request)
    {
        $datos =  $this->datosReporteInicio($request);
        $area = $datos['area'];
        $generalResultados = $datos['generalResultados'];
        $general = $datos['general'];
        $datos_categoria_dominio = $datos['datos_categoria_dominio'];
        $total_clasificacion_dominio = $datos['total_clasificacion_dominio'];
        $hombresNivel = $datos['hombresNivel'];
        $mujeresNivel = $datos['mujeresNivel'];
        $profesion = $datos['profesion'];
        $edad = $datos['edad'];
        $datosImplementacion = $datos['datosImplementacion'];
        $periodoNorma = $datos['periodoNorma'];
        $hombres = $datos['hombres'];
        $mujeres = $datos['mujeres'];
        $hombresPie = $datos['hombresPie'];
        $mujeresPie = $datos['mujeresPie'];
        $interpretaciones = $datos['interpretaciones'];
        //dd($generalResultados);

        return view("norma.implementacion.reporte", compact('area', 'generalResultados', 'general', 'datos_categoria_dominio', 'total_clasificacion_dominio', 'hombresNivel', 'mujeresNivel', 'profesion', 'edad', 'datosImplementacion', 'periodoNorma', 'hombres', 'mujeres', 'hombresPie', 'mujeresPie', 'interpretaciones'));
    }

    public function datosReporteInicio(Request $request)
    {
        $d = $this->datosReporte($request);
        $interpretaciones = $d['interpretaciones'];
        $datosImplementacion = $d['datosImplementacion'];
        $periodoNorma = $d["periodoNorma"];
        $hombres = $d['hombres'];
        $mujeres = $d['mujeres'];
        $tipo_cuestionario = $d['tipo_cuestionario'];
        $todos = array_merge($hombres, $mujeres);
        $hombresPie = $this->factorRiesgo($d['hombres'], $tipo_cuestionario);
        $mujeresPie = $this->factorRiesgo($d['mujeres'], $tipo_cuestionario);
        //print_r($this->trabajador_resultado_general);

        $hombresNivel = $this->calcularNivelRiesgo($hombresPie, 2);
        $mujeresNivel = $this->calcularNivelRiesgo($mujeresPie, 2);

        $factorRiesgoPrincipal = $this->factorRiesgoPersonal($todos, $tipo_cuestionario);
        $edad['edad'] = $factorRiesgoPrincipal['edad'];
        $edad['pieEdad'] = (array)$factorRiesgoPrincipal['pieEdad'];
        $edad['edadNivel'] = $this->calcularNivelRiesgo($edad['pieEdad'], 2);

        $profesion['profesion'] = $factorRiesgoPrincipal['profesion'];
        $profesion['pieProfesion'] = (array)$factorRiesgoPrincipal['pieProfesion'];
        $profesion['profesionNivel'] = $this->calcularNivelRiesgo($profesion['pieProfesion'], 2);

        $area['area'] = $factorRiesgoPrincipal['area'];
        $area['pieArea'] = (array)$factorRiesgoPrincipal['pieArea'];
        $area['areaNivel'] = $this->calcularNivelRiesgo($area['pieArea'], 2);

        $general['pie'] = array();
        $generalResultados = $this->trabajador_resultado_general;
        foreach ($mujeresPie as $id => $m) {
            $general['pie'][$id] = $m;
            $general['pie'][$id][2] = $m[2] + $hombresPie[$id][2];
        }
        $general['nivelRiesgo'] = $this->calcularNivelRiesgo($general['pie'], 2);
        $total_clasificacion_dominio = $d['totales_categoria_dominio']; //clasificaciones de nivel por categoria
        $ponderadores = new PonderadorController();
        $datos_categoria_dominio = $ponderadores->categoria_dominio;
        if ($tipo_cuestionario == 2) {
            unset($datos_categoria_dominio[7], $datos_categoria_dominio[16], $datos_categoria_dominio[17]);
        }

        foreach ($datos_categoria_dominio as $id => $cat) {
            //dd($datos_categoria_dominio[$id]);
            if (!empty($datos_categoria_dominio[$id])) {

                $datos_categoria_dominio[$id][2] = $this->calcularNivelRiesgo($total_clasificacion_dominio[$id], 4);
            }
        }

        return array('area' => $area, 'generalResultados' => $generalResultados, 'general' => $general, 'datos_categoria_dominio' => $datos_categoria_dominio, 'total_clasificacion_dominio' => $total_clasificacion_dominio, 'hombresNivel' => $hombresNivel, 'mujeresNivel' => $mujeresNivel, 'profesion' => $profesion, 'edad' => $edad, 'datosImplementacion' => $datosImplementacion, 'periodoNorma' => $periodoNorma, 'hombres' => $hombres, 'mujeres' => $mujeres, 'hombresPie' => $hombresPie, 'mujeresPie' => $mujeresPie, 'interpretaciones' => $interpretaciones);
    }

    public function datosReporte(Request $request)
    {
        //dd($request);
        $implementacion = $request->implementacion;
        $id_cuestionario = $tipo_cuestionario = 0;
        $totalesCategoriaDominio = array();
        // cambiarBase(Session::get('base'));
        elegirBase();
        $datosImplementacion = PeriodoImplementacion::where('id', $implementacion)->with(['interpretaciones', 'actividad_formulario'])->first();
        $interpretaciones = $datosImplementacion->interpretaciones;
        //$actividadPeriodoNorma = Actividad::whereNotNull('apertura_formulario')->where('estatus',self::ACTIVIDAD_CREADA)->where('idperiodo_implementacion',$implementacion)->get();
        $actividadPeriodoNorma = $datosImplementacion->actividad_formulario;
        //dump($actividadPeriodoNorma);
        $hombres = $mujeres = $participantes = $periodoNorma = array();
        if (!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0) {
            $periodoNorma = $actividadPeriodoNorma[0]->formulario;
            //dump($periodoNorma);
            if (!empty($periodoNorma) && $periodoNorma->estatus == 1) {
                // $xxx = $periodoNorma->trabajadorCuestionarioCalificablePeriodo; //->where('pivot_idperiodo',$periodoNorma->id)
                //dd($xxx[0]->idperiodo); 

                $participantes = $periodoNorma->trabajadorCuestionarioCalificablePeriodo->groupBy('sexo')->toArray();
                // dd($participantes);
                if (!empty($participantes[18])) {
                    $totalesCategoriaDominio = $this->totalesTrabajadores($periodoNorma->id, $participantes[18][0]['pivot']['idcuestionario']);
                    $hombres = $participantes[18];
                    $mujeres = $participantes[19];
                    $tipo_cuestionario = $participantes[18][0]['pivot']['idcuestionario'];
                    $id_cuestionario = $participantes[18][0]['pivot']['id'];
                }
            }
        }
        return array('totales_categoria_dominio' => $totalesCategoriaDominio, 'id_cuestionario' => $id_cuestionario, 'tipo_cuestionario' => $tipo_cuestionario, 'datosImplementacion' => $datosImplementacion, 'periodoNorma' => $periodoNorma, 'hombres' => $hombres, 'mujeres' => $mujeres, 'interpretaciones' => $interpretaciones);
    }

    public function factorRiesgo($datos, $tipo_cuestionario)
    {
        $ponderadores = new PonderadorController();
        $ponderadores->seleccionarPonderadores($tipo_cuestionario);
        $clasificacion = $ponderadores->factorRiesgoTotales; //nulo, bajo,medio,alto,muy alto
        $empleados = array();
        foreach ($datos as $d) {
            if (!empty($d['pivot']['total_cuestionario'])) {
                $clasificacionEstatus = $ponderadores->nivelRiesgoTotal;
                $cont = 0;
                foreach ($clasificacionEstatus as $c) {
                    if ($d['pivot']['total_cuestionario'] > $c[0] && $d['pivot']['total_cuestionario'] <= $c[1]) {
                        $clasificacion[$cont][2]++;
                        array_push($this->trabajador_resultado_general, array($clasificacion[$cont][0], $clasificacion[$cont][1], $d['pivot']['total_cuestionario'], $d['nombre'] . " " . $d['paterno'] . " " . $d['materno']));
                    }
                    $cont++;
                }
            }
        }
        return $clasificacion;
    }

    public function factorRiesgoPersonal($participantes, $tipo_cuestionario)
    {
        $ponderadores = new PonderadorController();
        $clasificacionEdad = array(
            21 => array($ponderadores->factorRiesgoTotales, "15-19", 0, "rgb(188,93,236)", array(), 0),
            22 => array($ponderadores->factorRiesgoTotales, "20-24", 0, "rgb(159,116,207)", array(), 0),
            23 => array($ponderadores->factorRiesgoTotales, "25-29", 0, "rgb(61,124,64)", array(), 0),
            24 => array($ponderadores->factorRiesgoTotales, "30-34", 0, "rgb(215,223,216)", array(), 0),
            25 => array($ponderadores->factorRiesgoTotales, "35-39", 0, "rgb(248,232,74)", array(), 0),
            26 => array($ponderadores->factorRiesgoTotales, "40-44 ", 0, "rgb(36,129,11)", array(), 0),
            27 => array($ponderadores->factorRiesgoTotales, "45-49", 0, "rgb(142,145,141)", array(), 0),
            28 => array($ponderadores->factorRiesgoTotales, "50-54", 0, "rgb(70,87,128)", array(), 0)
        );

        $clasificacionProfesion = array(
            34 => array($ponderadores->factorRiesgoTotales, "Secundaria terminada", 0, "rgb(188,93,236)", array(), 0),
            35 => array($ponderadores->factorRiesgoTotales, "Preparatoria o Bachillerato terminada", 0, "rgb(159,116,207)", array(), 0),
            37 => array($ponderadores->factorRiesgoTotales, "Técnico Superior terminada", 0, "rgb(61,124,64)", array(), 0),
            38 => array($ponderadores->factorRiesgoTotales, "Licenciatura terminada", 0, "rgb(215,223,216)", array(), 0),
            39 => array($ponderadores->factorRiesgoTotales, "Maestría terminada", 0, "rgb(248,232,74)", array(), 0),
            40 => array($ponderadores->factorRiesgoTotales, "Doctorado terminada", 0, "rgb(36,129,11)", array(), 0),
            41 => array($ponderadores->factorRiesgoTotales, "Secundaria Incompleta", 0, "rgb(142,145,141)", array(), 0),
            42 => array($ponderadores->factorRiesgoTotales, "Preparatoria o Bachillerato Incompleta", 0, "rgb(70,87,128)", array(), 0),
            43 => array($ponderadores->factorRiesgoTotales, "Técnico Superior Incompleta", 0, "rgb(248,232,74)", array(), 0),
            44 => array($ponderadores->factorRiesgoTotales, "Licenciatura Incompleta", 0, "rgb(36,129,11)", array(), 0),
            45 => array($ponderadores->factorRiesgoTotales, "Maestría Incompleta", 0, "rgb(142,145,141)", array(), 0),
            46 => array($ponderadores->factorRiesgoTotales, "Doctorado Incompleta", 0, "rgb(70,87,128)", array(), 0)
        );

        $clasificacionArea = array(
            47 => array($ponderadores->factorRiesgoTotales, "Operativo", 0, "rgb(188,93,236)", array(), 0),
            48 => array($ponderadores->factorRiesgoTotales, "Supervisor", 0, "rgb(159,116,207)", array(), 0),
            49 => array($ponderadores->factorRiesgoTotales, "Profesional o técnico", 0, "rgb(61,124,64)", array(), 0),
            50 => array($ponderadores->factorRiesgoTotales, "Gerente", 0, "rgb(215,223,216)", array(), 0)
        );
        $pieProfesion = array();
        $pieArea = array();
        $pieEdad = array();
        $participantesTot = 0;
        foreach ($participantes as $t) {
            if (!empty($t['pivot']['total_cuestionario'])) {
                $ponderadores = new PonderadorController();
                $ponderadores->seleccionarPonderadores($tipo_cuestionario);
                $clasificacionEstatus = $ponderadores->nivelRiesgoTotal;
                $cont = 0;
                $participantesTot = 0;
                foreach ($clasificacionEstatus as $c) {
                    if ($t['pivot']['total_cuestionario'] > $c[0] && $t['pivot']['total_cuestionario'] <= $c[1]) {
                        $clasificacionEdad[$t['edad']][0][$cont][2]++;
                        $clasificacionEdad[$t['edad']][2]++;
                        $clasificacionProfesion[$t['nivel_estudios']][0][$cont][2]++;
                        $clasificacionProfesion[$t['nivel_estudios']][2]++;
                        $clasificacionArea[$t['tipo_puesto']][0][$cont][2]++;
                        $clasificacionArea[$t['tipo_puesto']][2]++;

                        $participantesTot++;
                    }
                    $cont++;
                }
            }
        }
        //dd(count($participantes));
        foreach ($clasificacionEdad as $i => $ce) {
            $pieEdad[] = array($ce[3], $ce[1], $ce[2]);
            $clasificacionEdad[$i][4] = $this->calcularNivelRiesgo($ce[0], 2);
            if ($participantesTot != 0) {
                $clasificacionEdad[$i][5] = round(($ce[2] * 100) / count($participantes), 1);
            } else {
                $clasificacionEdad[$i][5] = 0;
            }
        }

        foreach ($clasificacionProfesion as $i => $ce) {
            $pieProfesion[] = array($ce[3], $ce[1], $ce[2]);
            $clasificacionProfesion[$i][4] = $this->calcularNivelRiesgo($ce[0], 2);
            if ($participantesTot != 0) {
                $clasificacionProfesion[$i][5] = round(($ce[2] * 100) / count($participantes), 1);
            } else {
                $clasificacionProfesion[$i][5] = 0;
            }
        }

        foreach ($clasificacionArea as $i => $ce) {
            $pieArea[] = array($ce[3], $ce[1], $ce[2]);
            $clasificacionArea[$i][4] = $this->calcularNivelRiesgo($ce[0], 2);
            if ($participantesTot != 0) {
                $clasificacionArea[$i][5] = round(($ce[2] * 100) / count($participantes), 1);
            } else {
                $clasificacionArea[$i][5] = 0;
            }
        }

        return array('profesion' => $clasificacionProfesion, 'pieProfesion' => $pieProfesion, 'edad' => $clasificacionEdad, 'pieEdad' => $pieEdad, 'area' => $clasificacionArea, 'pieArea' => $pieArea);
    }

    public function calcularNivelRiesgo($datos, $i)
    {
        $nivel = array();
        $mayor = 0;

        foreach ($datos as $d) {
            if ($mayor < $d[$i]) {
                $nivel = array($d);
                $mayor = $d[$i];
            } else if ($mayor == $d[$i] && $mayor != 0) {
                array_push($nivel, $d);
            }
        }
        return $nivel;
    }

    public function totalesTrabajadores($periodoNorma, $tipo_cuestionario)
    {
        //echo $periodoNorma;
        // cambiarBase(Session::get('base'));
        elegirBase();
        $ponderadores = new PonderadorController();
        $ponderadores->seleccionarPonderadores($tipo_cuestionario);
        $clasificacionEstatus = $ponderadores->nivelRiesgoCatDom;
        //$clasificacionEstatus = ($tipo_cuestionario == 2)? $ponderadores->nivelRiesgoEstatusCuestionarioCatDomII : $ponderadores->nivelRiesgoEstatusCuestionarioCatDomIII;
        //dump($periodoNorma);
        $totales = CuestionarioTrabajador::where('idcuestionario', 2)->orWhere('idcuestionario', 3)->with('totalesCategoria')->get()->where('idperiodo', $periodoNorma);
        //dump($totales);
        foreach ($totales as $total) { //Barrido de totales de empleados
            $clasificacion = $ponderadores->factorRiesgoTotales;
            $total = $total->totalesCategoria; // Totales de categoria y dominio
            if (!empty($total)) { // validar que tenga totales
                foreach ($total as $t) { // barrido de totales
                    //print_r($clasificacionEstatus[$t->idclasificacion]);
                    //echo $t->id."-- Tot ".$t->total."-- Class ".$this->categoria_dominio[$t->idclasificacion]." ".$t->idclasificacion."-- Cues ".$t->idcuestionario_trabajador."<br/><br/>";
                    $cont = 0;
                    foreach ($clasificacionEstatus[$t->idclasificacion] as $clasificacion) {
                        if ($clasificacion[0] == 0) {
                            if ($t->total >= $clasificacion[0] && $t->total <= $clasificacion[1]) {
                                $clasificacionEstatus[$t->idclasificacion][$cont][4]++;
                            }
                        } else {
                            if ($t->total > $clasificacion[0] && $t->total <= $clasificacion[1]) {
                                $clasificacionEstatus[$t->idclasificacion][$cont][4]++;
                            }
                        }

                        $cont++;
                    }
                }
            }
        }
        return $clasificacionEstatus;
    }

    public function download(Request $request)
    {
        $datos =  $this->datosReporteInicio($request);
        $area = $datos['area'];
        $generalResultados = $datos['generalResultados'];
        $general = $datos['general'];
        $datos_categoria_dominio = $datos['datos_categoria_dominio'];
        $total_clasificacion_dominio = $datos['total_clasificacion_dominio'];
        $hombresNivel = $datos['hombresNivel'];
        $mujeresNivel = $datos['mujeresNivel'];
        $profesion = $datos['profesion'];
        $edad = $datos['edad'];
        $datosImplementacion = $datos['datosImplementacion'];
        $periodoNorma = $datos['periodoNorma'];
        $hombres = $datos['hombres'];
        $mujeres = $datos['mujeres'];
        $hombresPie = $datos['hombresPie'];
        $mujeresPie = $datos['mujeresPie'];
        $logos_portada = $interpretaciones = array();
        foreach ($datos['interpretaciones'] as $id => $inter) {
            $interpretaciones[$inter->idcatalogo_norma . "_" . $inter->tipo_grafica] = array(
                "interpretacion" => $inter->interpretacion,
                "imagen" => storage_path() . "/app/public/repositorio/" . Session::get('empresa')['id'] . "/charts/" . $request->implementacion . "/" . $inter->imagen
            );
        }
        // obtener logos para la portada del reporte
        $ruta_logos = "public/repositorio/" . Session::get('empresa')['id'] . "/logos_portada_reporte_norma/";
        if (is_dir($ruta_logos)) {
            //    dd($ruta_logos);
            $gestor = opendir($ruta_logos);
            while (($archivo = readdir($gestor)) !== false) {
                // Se muestran todos los archivos y carpetas excepto "." y ".."
                if ($archivo != "." && $archivo != "..") {
                    // Si es un directorio se recorre recursivamente
                    if (!is_dir($ruta_logos . '/' . $archivo)) {
                        $logos_portada[] = asset("/public/repositorio/" . Session::get('empresa')['id'] . "/logos_portada_reporte_norma/" . $archivo);
                    }
                }
            }
        }

        $total_logos = count($logos_portada);
        $pdf = app('dompdf.wrapper');
        //linea para mostrar el pdf en el navegador 
        // return  $pdf = \PDF::loadView("norma.implementacion.reporte_generar",compact('total_logos','logos_portada','area','generalResultados','general','datos_categoria_dominio','total_clasificacion_dominio','hombresNivel','mujeresNivel','profesion','edad','datosImplementacion','periodoNorma','hombres','mujeres','hombresPie','mujeresPie','interpretaciones'))->stream('implementacion.pdf');
        // return $interpretaciones;

        //descarga automatica del pdf
        $pdf = PDF::loadView("norma.implementacion.reporte-download", compact('total_logos', 'logos_portada', 'area', 'generalResultados', 'general', 'datos_categoria_dominio', 'total_clasificacion_dominio', 'hombresNivel', 'mujeresNivel', 'profesion', 'edad', 'datosImplementacion', 'periodoNorma', 'hombres', 'mujeres', 'hombresPie', 'mujeresPie', 'interpretaciones'));
        return $pdf->download(rtrim(Session::get('empresa')['razon_social']) . 'Implementacion' . date("Y") . '.pdf');
    }
    public function listaEmpleados(Request $request)
    {

        if ($request->implementacion) {

            $implementacion = $request->implementacion;
            $catalogos = $this->catalogos;
            elegirBase();


            if (Schema::connection('empresa')->hasTable('sedes')) {

                $datosImplementacion = PeriodoImplementacion::with(['sede_asignada', 'razon_social_asignada'])->find($implementacion);
            } else {

                $datosImplementacion = PeriodoImplementacion::with(['razon_social_asignada'])->find($implementacion);
                $datosImplementacion->sede_asignada = "";
            }

            $actividadPeriodoNorma = Actividad::whereNotNull('apertura_formulario')->where('estatus', self::ACTIVIDAD_CREADA)->where('idperiodo_implementacion', $implementacion)->get();
            $participantes = $periodoNorma = array();
            $terminado = 0;

            /*if(!empty($row->sede_asignada->nmb) && $row->sede_asignada->nmb != "" && $row->sede_asignada->nmb != null){
                return $row->sede_asignada->nmb;
            }*/

            $hoy = new DateTime();
            $sede = $fNormaFin = $fNormaExp = "";
            $tituloNorma = "No hay un periodo para captura de cuestionarios";

            if (!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0) {

                $periodoNorma = $actividadPeriodoNorma[0]->formulario;

                if (!empty($periodoNorma) && $periodoNorma->estatus == 1) {

                    $participantes = $periodoNorma->trabajadorCuestionarioPeriodo;
                    $fNormaInicio = new DateTime($periodoNorma->fecha_inicio);
                    $fNormaFin = new DateTime($periodoNorma->fecha_fin);
                    $fNormaExp = new DateTime($periodoNorma->fecha_fin_expansion);
                    $id_periodo_norma = $periodoNorma->id;
                    $tituloNorma = "Periodo para el llenado de cuestionarios <b>" . $fNormaInicio->format('d-m-Y') . "</b> al <b>" . $fNormaFin->format('d-m-Y') . "</b>";
                    $expansion = "";
                    if ($hoy >= $fNormaInicio  &&  $hoy <= $fNormaExp) {
                        if ($hoy > $fNormaFin  &&  $hoy <= $fNormaExp) {
                            $expansion = "<p style='color:red'><small>(periodo especial <b>" . $fNormaFin->format('d-m-Y') . "</b> al <b>" . $fNormaExp->format('d-m-Y') . "</b>)</small></p>";
                        }
                    } else if ($hoy > $fNormaExp) {
                        $terminado = 1;
                    }
                }
            }

            $trabajadores = Trabajador::join('cuestionarios_trabajadores', "informacion_trabajadores.id", "=", "cuestionarios_trabajadores.idinformacion_trabajador")
                ->where("cuestionarios_trabajadores.idperiodo", $id_periodo_norma)
                ->select("informacion_trabajadores.*")
                ->with('cuestionarios')
                ->groupBy("informacion_trabajadores.id")
                ->get();

            return view("norma.implementacion.lista-empleado", compact("fNormaFin", "fNormaExp", "terminado", "expansion", "tituloNorma", "datosImplementacion", "actividadPeriodoNorma", "periodoNorma", "participantes", "catalogos", "trabajadores"));
        }
    }

    public function generarListaEmpleados(Request $request)
    {
        try {

            elegirBase();
            $sede = $request->sede;

            ($sede != "" && $sede != null && Schema::connection('empresa')->hasTable('sedes')) ? $empleados = Empleado::all()->where('estatus', 1)->where('sede', $sede)->groupBy('genero')->toArray() : $empleados = Empleado::all()->where('estatus', 1)->groupBy('genero')->toArray();

            $mujeres = $hombres = array();
            $totalF = $totalM = 0;

            if (isset($empleados['M'])) {
                $totalM = count($empleados['M']);
                $hombres = $empleados['M'];
            }

            if (isset($empleados['F'])) {
                $totalF = count($empleados['F']);
                $mujeres = $empleados['F'];
            }

            $totalEmpleados = ($totalM + $totalF);
            ($totalEmpleados==0)?$respuesta = 3 : $respuesta = 1;
            $datos = ['msg' => 'construyendo .', 'hombres' => $hombres, 'mujeres' => $mujeres, 'total' => $totalEmpleados];
        } catch (\Exception $e) {

            $respuesta = 2;
            $datos = [];
        }

        return response()->json(['ok' => $respuesta, 'datos' => $datos]);
    }

    //inserta informacion personal de los empleados seleccionados 
    //No se utiliza el Api para esta funcion
    public function crearListaApi(Request $request, Client $client)
    {
        elegirBase();
        $seleccionados = $request->trabajadores;
        $excentos = array();
        $cuestionarios_trabajadores = $informacion_trabajadores = $correos = array();
        $cuestionarios = $this->obtenerCuestionarios($request->totalEmpleados);

        if (!empty($request->excentos) && count($request->excentos) > 0) {

            foreach ($request->excentos as $excento) {

                $sel = explode("$", $excento);
                array_push($excentos, array(
                    'nombre' => $sel[1],
                    'paterno' => $sel[2],
                    'materno' => $sel[3],
                    'empleados_id' => $sel[0],
                    'periodo_norma' => $request->norma,
                    'sexo' => ($sel[4] == "F") ? 19 : 18,
                    'correo' => (!empty($sel[5])) ? $sel[5] : "sincorreo"
                ));
            }

            Excento::insert($excentos);
        }

        foreach ($seleccionados as $seleccionado) {

            $sel = explode("$", $seleccionado);
            $id_informacion_trabajador = Trabajador::insertGetId(

                [
                    'estatus' => 1,
                    'nombre' => $sel[1],
                    'paterno' => $sel[2],
                    'materno' => $sel[3],
                    'empleados_id' => $sel[0],
                    'sexo' => ($sel[4] == "F") ? 19 : 18,
                    'informacion_validada' => 0,
                    'correo' => (!empty($sel[5])) ? $sel[5] : "sincorreo"
                ]
            );

            array_push($correos, $sel[5]);

            foreach ($cuestionarios as $cuestionario) {

                array_push($cuestionarios_trabajadores, array(

                    'idperiodo' => $request->norma,
                    'idcuestionario' => $cuestionario,
                    'idinformacion_trabajador' => $id_informacion_trabajador,
                    'estatus' => self::CUESTIONARIO_AGREGADO,
                    'fecha_inicio' => date('Y-m-d H:i:s')

                ));
            }
        }

        if ($this->agregarCuestionariosTrabajador($cuestionarios_trabajadores)) {

            $resp = $this->mailInicioNorma($correos, $request->norma, $request->idimplementacion, $client);
            ($resp) ? $respuesta = 1 : $respuesta  = 2;

            return response()->json(['ok' => $respuesta]);
        } else {

            return response()->json(['ok' => 3, 'msg' => 'La lista de empleados no pudo crearse con éxito']);
        }
    }

    //insert de cuestionarios por empleado
    public function agregarCuestionariosTrabajador($cuestionarios)
    {
        return CuestionarioTrabajador::insert($cuestionarios);
    }

    // De acuerdo al total de empleados se asignan los cuestionarios a realizar
    public function obtenerCuestionarios($total)
    {
        if ($total <= 15) { // Guía de referencia I
            return array(self::GUIA_REFERENCIA_UNO);
        } else if ($total >= 16 && $total <= 50) { // Guía de referencia I y II, empresas con menos de 50 trabajadores
            return array(self::GUIA_REFERENCIA_UNO, self::GUIA_REFERENCIA_DOS);
        } else if ($total > 50) { // Guía de referencia I y III, empresas con 51 o más trabajadores
            return array(self::GUIA_REFERENCIA_UNO, self::GUIA_REFERENCIA_TRES);
        }
        return array();
    }

    // C O R R E O 
    public function mailInicioNorma($para, $norma, $implementacion, $client)
    {
        try {

            elegirBase();
            $periodoNorma = PeriodoNorma::where('id', $norma)->first()->toArray();
            $fNormaInicio = new DateTime($periodoNorma['fecha_inicio']);
            $fNormaFin = new DateTime($periodoNorma['fecha_fin']);
            $fNormaExp = new DateTime($periodoNorma['fecha_fin_expansion']);
            $para[] = 'desarrollo5@singh.com.mx';
            mailNotificarEncargado($implementacion, 'Lista de trabajadores para el llenado de cuestionarios creada para la implementación de ' . Session::get('empresa')['razon_social'] . '<br/>
            Fecha inicio: ' . $fNormaInicio->format('d-m-Y') . '<br/>Fecha fin: ' . $fNormaFin->format('d-m-Y') . '<br/>Fecha fin Expansión: ' . $fNormaExp->format('d-m-Y'), "Lista de empleados");

            $titulo = 'Norma 035';
            $cuerpo = 'Estimado Usuario<br/>Le informamos que estará participando en una evaluación a favor de su entorno organizacional,<br/>Su opinión es muy importante<br/>Favor de realizarla en el plazo estipulado (<b>' . $fNormaInicio->format('d-m-Y') . ' - ' . $fNormaFin->format('d-m-Y') . '</b>)';
            $btnUrl = "https://www.hrsystem.com.mx/empleado/login";
            $btnTxt = 'Comenzar';

            foreach ($para as $correo) Mail::to($correo)->later(now()->addSeconds(5), new enviarMail($titulo, $cuerpo, $btnUrl, $btnTxt));
            return  true;
        } catch (\Exception $e) {
            //dd($e);
            return false;
        }
    }

    public function importarListaEmpleados(Request $request)
    {

        $empleados_import = new importNormaTrabajadoresExternos;
        Excel::import($empleados_import, $request->importar_trabajador);
        return $empleados_import->getImportedResults();
    }

    public function remplazarEmpleadoApi(Request $request)
    {
        //dd($request->generoEdit);
        elegirBase();
        $excentos = Excento::where('periodo_norma', $request->normaEditar)->where('sexo', $request->generoEdit)->get();
        // echo $excentos->count()."--";
        //dd($excentos);
        if ($excentos->count() > 0) {

            $aleatorio = rand(0, $excentos->count() - 1);
            //   echo $aleatorio;
            //   dd($excentos[$aleatorio]);
            $remplazo_aleatorio = $excentos[$aleatorio];
            //  echo $aleatorio;
            // dd($excentos[$aleatorio]);
            $remplazado = Trabajador::find($request->idEmpleadoEdit);

            $temporal['nombre'] = $remplazado->nombre;
            $temporal['paterno'] = $remplazado->paterno;
            $temporal['materno'] = $remplazado->materno;
            $temporal['correo'] = $remplazado->correo;
            $temporal['empleados_id'] = $remplazado->empleados_id;

            $remplazado->nombre = $remplazo_aleatorio->nombre;
            $remplazado->paterno = $remplazo_aleatorio->paterno;
            $remplazado->materno = $remplazo_aleatorio->materno;
            $remplazado->correo = $remplazo_aleatorio->correo;
            $remplazado->empleados_id = $remplazo_aleatorio->empleados_id;

            $remplazo = Excento::find($remplazo_aleatorio->id);
            $remplazo->nombre = $temporal['nombre'];
            $remplazo->paterno = $temporal['paterno'];
            $remplazo->materno = $temporal['materno'];
            $remplazo->correo = $temporal['correo'];
            $remplazo->empleados_id = $temporal['empleados_id'];

            if ($remplazado->update() && $remplazo->update()) {

                return response()->json(['ok' => 1, 'msg' => 'El empleado se modificó con éxito', "remplazo" => $remplazado]);
            }

            return response()->json(['ok' => 0, 'msg' => 'No fue posible remplazar al empleado, intentelo mas tarde', "remplazo" => $remplazado]);
        }
        return response()->json(['ok' => 2, 'msg' => 'No fue posible remplazar al empleado, no hay empleados de remplazo']);
    }

    public function llenarCuestionariosEmpleado(Request $request)
    {
        elegirBase();
        //echo "--".$request->informacion_trabajador."-x-";
        $trabajador = Trabajador::find($request->informacion_trabajador);

        if ($trabajador->informacion_validada == 1) { // mostrar cuestionarios

            $cuestionarios = array();
            $cont = 0;
            $cuestionarios_trabajador = CuestionarioTrabajador::where('idinformacion_trabajador', $request->informacion_trabajador)->with('respuestas')->get();
            foreach ($cuestionarios_trabajador as $cuestionario) {

                $cuestionario_trabajador = $cuestionario;
                if ($cuestionario_trabajador->estatus == 0) {
                    $tipo_cuestionario = $cuestionario->idcuestionario;
                    //  $respuestas = RespuestaCuestionario::where('idcuestionario_trabajador','=',$cuestionario->id)->get();
                    $respuestas = $cuestionario_trabajador->respuestas;
                    $bloques = BloqueCuestionario::where('idcuestionario', '=', $tipo_cuestionario)->with('preguntas')->get();
                    return view("norma.cuestionarios.cuestionario-admin", compact("bloques", "tipo_cuestionario", "respuestas", "cuestionario_trabajador"));
                }
            }

            return view("norma.cuestionarios.cuestionario-admin-completo", compact("trabajador"));
        } else { // validar informacion personal
            return view("norma.cuestionarios.informacion-personal-admin", compact('trabajador'));
        }
    }

    public function verCorreos(Request $request)
    {
        elegirBase();
        $empleados = Empleado::where('correo', $request->correo)->get();
        $usuario = EmpleadoLogin::where("email", $request->correo)->get();
        return array('ok' => 1, 'idinformacion_trabajador' => $request->idinformacion_trabajador, 'correo' => $request->correo, 'empleado' => $empleados, 'usuario' => $usuario);
    }

    public function operacionCorreo(Request $request)
    {
        elegirBase();
        //dd($_POST);
        if ($request->tipo == 1) { // empleado

            if (Empleado::where('id', $request->id)->update(['correo' => $request->correo])) {
                return array('ok' => 1, 'msj' => "El correo del empleado se actualizó con éxito");
            } else {
                return array('ok' => 1, 'msj' => "No hubo cambio en el correo");
            }
            return array('ok' => 2, 'msj' => "OCURRIO UN ERROR, INTENTELO MAS TARDE");
        } else if ($request->tipo == 2) { // información trabajador

            if (Trabajador::where('id', $request->id)->update(['correo' => $request->correo])) {
                return array('ok' => 1, 'id' => $request->id, 'email' => $request->correo, 'msj' => "El correo para la norma 035 se actualizó con éxito");
            } else {
                return array('ok' => 1, 'msj' => "No hubo cambio en el correo");
            }
            return array('ok' => 2, 'msj' => "OCURRIO UN ERROR, INTENTELO MAS TARDE");
        } else if ($request->tipo == 3) { // cuenta usuario

            if (EmpleadoLogin::where('id', $request->id)->update(['email' => $request->correo])) {
                return array('ok' => 1, 'msj' => "El correo para la cuenta de usuario se actualizó con éxito");
            } else {
                return array('ok' => 1, 'msj' => "No hubo cambio en el correo");
            }
            return array('ok' => 2, 'msj' => "OCURRIO UN ERROR, INTENTELO MAS TARDE");
        } else if ($request->tipo == 4) { // reset contraseña

            if (EmpleadoLogin::where('id', $request->id)->update(['password' => bcrypt('123456')])) {
                return array('ok' => 1, 'msj' => "La contraseña se reinicio con éxito");
            } else {
                return array('ok' => 1, 'msj' => "No hubo cambio en el correo");
            }
            return array('ok' => 2, 'msj' => "OCURRIO UN ERROR, INTENTELO MAS TARDE");
        } else if ($request->tipo == 5) { // crear cuenta usuario

            $usuario = EmpleadoLogin::insertGetId([
                'email' => $request->correo,
                'password' => bcrypt(123456),
                'empresa' => strtolower(session::get('empresa')['base']),
                'estatus' => 1,
                'tmp' => 123456,
                'codigo' => 123456,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s'),
            ]);
            if ($usuario) {

                return array('ok' => 1, 'id' => $usuario, 'email' => $request->correo, 'msj' => "La cuenta de usuario se creó con éxito");
            } else {
                return array('ok' => 1, 'msj' => "No se insertó el usuario");
            }
            return array('ok' => 2, 'msj' => "OCURRIO UN ERROR, INTENTELO MAS TARDE");
        }
        return array('ok' => 1, 'msj' => $request->id . " " . $request->correo);
    }
    public function cuestionariosPdfTrabajador($informacion_trabajador)
    {
        elegirBase();
        //dd($informacion_trabajador);
        $cuestionario = array();
        $bloques = array();
        if (!empty($informacion_trabajador)) {

            $trabajador = CuestionarioTrabajador::where("idinformacion_trabajador", $informacion_trabajador)->with('respuestas', 'cuestionario', 'datosPersonales')->get();
        }

        $razon = session::get('empresa')['razon_social'];
        $logo = public_path() . '/img/logo-sistema.png';

        //$pdf = app('dompdf.wrapper');

        return  PDF::loadView("norma.implementacion.resultados-pdf", compact('trabajador', 'razon', 'logo'))->stream('resultados.pdf');

        /*
        $pdf = \PDF::loadView("norma.implementacion.impresion_cuestionario",compact('bloques'));
        return $pdf->download('cuestionario.pdf');*/
    }

    public function resultadosEmpleado(Request $request)
    {
        elegirBase();
        $resultados = array();
        $tipo_cuestionario = 0;
        $cuestionarios = CuestionarioTrabajador::where('idinformacion_trabajador', $request->idEmpleadoResultados)->with(['totales', 'cuestionario', 'respuestas'])->get();
        $cont = 0;
        foreach ($cuestionarios as $c) {
            if ($tipo_cuestionario == 0 && $c->idcuestionario != 1) {
                $tipo_cuestionario = $c->idcuestionario;
            }
            $resultados[$cont]['cuestionario_trabajador'] = $c;
            $resultados[$cont]['cuestionario'] = $c->cuestionario;
            $resultados[$cont]['totales'] = $c->totales;
            $resultados[$cont]['respuestas'] = $c->respuestas;
            $cont++;
        }
        $respuestas = $this->respuestas;
        $ponderadores = new PonderadorController();
        $ponderadores->seleccionarPonderadores($tipo_cuestionario);
        $ponderadorTotal = $ponderadores->nivelRiesgoTotal;
        $ponderadorCyD = $ponderadores->nivelRiesgoCatDom;
        $ponderador = $ponderadores->clasificacionEstatus;
        return view("norma.implementacion.lista-resultados", compact('resultados', 'respuestas', 'ponderadorTotal', 'ponderadorCyD'));
        // return response()->json(['ok' => 1,'res' => $resultados,'respuestas'=> $this->respuestas,'ponderador' => $ponderadores->clasificacionEstatus]);
    }
    public function recordatorioLlenadoNorma(Request $request)
    {
        try {

            $correos = json_decode($request->correos_enviar);
            $this->mailRecordatorioLlenadoNorma($correos, $request->normaRecordatorio);
            mailNotificarEncargado($request->implementacion_recordatorio, "Recordatorio de llenado de <b> " . Session::get('empresa')['razon_social'] . "</b> enviado a:<br/>" . implode(", <br>", $correos));
            //$respuesta = 1;

        } catch (\Exception $e) {
            $e;
        }

        return response()->json(['ok' => 1]);
    }


    public function mailRecordatorioLlenadoNorma($para, $norma)
    {
        elegirBase();
        $para[] = 'desarrollo2@singh.com.mx';
        $periodoNorma = PeriodoNorma::where('id', $norma)->first()->toArray();
        $fNormaInicio = new DateTime($periodoNorma['fecha_inicio']);
        $fNormaFin = new DateTime($periodoNorma['fecha_fin']);
        $fNormaExp = new DateTime($periodoNorma['fecha_fin_expansion']);
        $hoy = new DateTime();
        if ($hoy >= $fNormaInicio  &&  $hoy <= $fNormaExp) {
            $titulo = 'Norma 035';
            $btnUrl = "https://www.hrsystem.com.mx/empleado/login";
            $btnTxt = 'Comenzar';
            if ($hoy <= $fNormaFin) { // recordatorio normal

                $cuerpo = 'Le recordamos que tiene pendiente el llenado de los cuestionarios correspondientes a la norma 035, fecha limite de llenado es: <b>' . $fNormaFin->format('d-m-Y') . '</b>';
                foreach ($para as $correo) Mail::to($correo)->later(now()->addSeconds(5), new enviarMail($titulo, $cuerpo, $btnUrl, $btnTxt));
            } else if ($hoy > $fNormaFin  &&  $hoy <= $fNormaExp) { // periodo extra de llenado, se uso otro if por posible cambio de contenido en el mail

                $cuerpo = 'Le recordamos que tiene pendiente el llenado de los cuestionarios correspondientes a la norma 035, fecha limite de llenado es: <b> ' . $fNormaExp->format('d-m-Y') . '</b>';
                foreach ($para as $correo) Mail::to($correo)->later(now()->addSeconds(5), new enviarMail($titulo, $cuerpo, $btnUrl, $btnTxt));
            }
        }
    }

    public function guardarGrafica(Request $request){
        $repositorioChart   = storage_path('app/public/repositorio/' . Session::get('empresa')['id'] . '/charts/');
        $folder_repositorio = storage_path('app/public/repositorio/' . Session::get('empresa')['id'] . '/charts/'.$request->implementacion."/");

        if(!File::exists($repositorioChart)) {
            File::makeDirectory($repositorioChart, $mode = 0777, true, true);
        }
        if(!File::exists($folder_repositorio)) {
            File::makeDirectory($folder_repositorio, $mode = 0777, true, true);
        }

        $imagenEnBase64 = $request->imagen;
        $rutaImagenSalida = storage_path("app/public/repositorio/".Session::get('empresa')['id']."/charts/".$request->implementacion."/".$request->implementacion."_".$request->idCat."_".$request->tipoGra.".png");
        $imagenBinaria = base64_decode($imagenEnBase64);


        if(file_put_contents($rutaImagenSalida, $imagenBinaria)){
            return $this->crearInterpretacion($request);
        }else{
            return response()->json(['ok' => 0]);
        }
    }

    public function crearInterpretacion(Request $request){
        elegirBase();
        if (strlen($request->interpretacion) > 700) $request->interpretacion = substr($request->interpretacion, 0, 700);
        $id = Interpretacion::insertGetId(
            [
                'idcatalogo_norma' => $request->idCat, 
                'interpretacion' => $request->interpretacion, 
                'idperiodo_implementacion' => $request->implementacion,
                'tipo_grafica' => $request->tipoGra,
                'imagen' => $request->implementacion."_".$request->idCat."_".$request->tipoGra.".png",
            ]
        );
        if($id){
            return response()->json(['ok' => 1]);
        }
        return response()->json(['ok' => 0]);

    }
    public function crearTablasNOM035()
    {
        $conexion = 'empresa';
        $base = Session::get('base');
        cambiarBase($base);
        
        foreach(Empresa::TABLAS_EMPRESA as $tabla){

            switch($tabla){

                /*-- Volcando estructura para tabla empresa000xxx.periodos_implementacion*/
                case 'periodos_implementacion':

                    try{
                                
                        $nombreTablaNO035='periodos_implementacion';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_inicio` datetime NOT NULL,
                            `fecha_fin` datetime NOT NULL,
                            `create_at` datetime DEFAULT NULL,
                            `update_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                            DB::connection($conexion)->statement($tabla);
                         
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.periodos_norma*/
                case 'periodos_norma':

                    try{
                                
                        $nombreTablaNO035='periodos_norma';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_inicio` datetime DEFAULT NULL,
                            `fecha_fin` datetime DEFAULT NULL,
                            `fecha_fin_expansion` datetime DEFAULT NULL,
                            `estatus` varchar(45) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            )ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                            DB::connection($conexion)->statement($tabla);		
                         
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.actividades*/
                case 'actividades':

                    try{
                                
                        $nombreTablaNO035='actividades';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `descripcion` varchar(255) NOT NULL,
                            `fecha_inicio` datetime NOT NULL,
                            `fecha_fin` datetime NOT NULL,
                            `notificacion` int(11) DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `apertura_formulario` int(11) DEFAULT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_actividades_periodos_norma1_idx` (`apertura_formulario`),
                            KEY `fk_actividades_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            CONSTRAINT `fk_actividades_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_actividades_periodos_norma1` FOREIGN KEY (`apertura_formulario`) REFERENCES `periodos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                            DB::connection($conexion)->statement($tabla);		
                         
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.cuestionarios*/
                case 'cuestionarios':

                    try{
                                
                        $nombreTablaNO035='cuestionarios';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            `descripcion` varchar(255) DEFAULT NULL,
                            `tipo` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                        $resp = DB::connection($conexion)->table($nombreTablaNO035)->count();
                        if($resp===0){
                            
                            $values = "INSERT INTO ".$base.".".$nombreTablaNO035."(`id`, `nombre`, `created_at`, `updated_at`, `descripcion`, `tipo`) VALUES
                            (1, 'Guía de referencia I ', '2020-01-23 14:25:10', NULL, 'IDENTIFICACIÓN Y ANÁLISIS DE LOS FACTORES DE RIESGO PSICOSOCIAL Y EVALUACIÓN DEL ENTORNO ORGANIZACIONAL EN LOS CENTROS DE TRABAJO', 1),
                            (2, 'Guía de referencia II', '2020-01-23 14:25:10', NULL, 'IDENTIFICACIÓN Y ANÁLISIS DE LOS FACTORES DE RIESGO PSICOSOCIAL Y EVALUACIÓN DEL ENTORNO ORGANIZACIONAL EN LOS CENTROS DE TRABAJO', 2),
                            (3, 'Guía de referencia III', '2020-01-23 14:25:10', NULL, 'IDENTIFICACIÓN Y ANÃLISIS DE LOS FACTORES DE RIESGO PSICOSOCIAL Y EVALUACIÓN DEL ENTORNO ORGANIZACIONAL EN LOS CENTROS DE TRABAJO', 3);";
                            
                            DB::connection($conexion)->statement($values);
                        }
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.bloques_cuestionario*/
                case 'bloques_cuestionario':

                    try{
                                
                        $nombreTablaNO035='bloques_cuestionario';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(255) DEFAULT NULL,
                            `descripcion` varchar(255) DEFAULT NULL,
                            `instrucciones` varchar(255) DEFAULT NULL,
                            `idcuestionario` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_bloque_cuestionarios1_idx` (`idcuestionario`),
                            CONSTRAINT `fk_bloque_cuestionarios1` FOREIGN KEY (`idcuestionario`) REFERENCES `cuestionarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                        $resp = DB::connection($conexion)->table($nombreTablaNO035)->count();
                        if($resp===0){
                            
                            $values = "INSERT INTO ".$base.".".$nombreTablaNO035."(`id`, `nombre`, `descripcion`, `instrucciones`, `idcuestionario`) VALUES(1,'Parte I', NULL, 'Para responder las preguntas siguientes considere las condiciones ambientales de su centro de trabajo.', 3),(2, 'Parte II', NULL, 'Para responder a las preguntas siguientes piense en la cantidad y ritmo de trabajo que tiene.', 3),(3, 'Parte III', NULL, 'Las preguntas siguientes están relacionadas con el esfuerzo mental que le exige su trabajo.', 3),(4, 'Parte IV', NULL, 'Las preguntas siguientes están relacionadas con las actividades que realiza en su trabajo y las responsabilidades que tiene.', 3),(5, 'Parte V', NULL, 'Las preguntas siguientes están relacionadas con su jornada de trabajo.', 3),(6, 'Parte VI', NULL, 'Las preguntas siguientes están relacionadas con las decisiones que puede tomar en su trabajo.', 3),(7, 'Parte VII', NULL, 'Las preguntas siguientes están relacionadas con cualquier tipo de cambio que ocurra en su trabajo (considere los últimos cambios realizados).', 3),(8, 'Parte VIII', NULL, 'Las preguntas siguientes están relacionadas con la capacitación e información que se le proporciona sobre su trabajo.', 3),(9, 'Parte IX', NULL, 'Las preguntas siguientes están relacionadas con el o los jefes con quien tiene contacto.', 3),(10, 'Parte X', NULL, 'Las preguntas siguientes se refieren a las relaciones con sus compañeros.', 3),(11, 'Parte XI', NULL, 'Las preguntas siguientes están relacionadas con la información que recibe sobre su rendimiento en el trabajo, el reconocimiento, el sentido de pertenencia y la estabilidad que le ofrece su trabajo.', 3),(12, 'Parte XII', NULL, 'Las preguntas siguientes están relacionadas con actos de violencia laboral (malos tratos, acoso, hostigamiento, acoso psicológico).', 3),(13, 'Parte XIII', 'Las preguntas siguientes están relacionadas con la atención a clientes y usuarios.', 'Si su respuesta fue SÍ, responda las preguntas siguientes. Si su respuesta fue NO pase a las preguntas de la sección siguiente.', 3),(14, 'Parte XIV', NULL, 'Si su respuesta fue SÍ, responda las preguntas siguientes. Si su respuesta fue NO, ha concluido el cuestionario.<br/>Las preguntas siguientes están relacionadas con las actitudes de las personas que supervisa.', 3),(15, 'Parte I', NULL, 'Para responder las preguntas siguientes considere las condiciones de su centro de trabajo, así como la cantidad y ritmo de trabajo.', 2),(16, 'Parte II', NULL, 'Las preguntas siguientes están relacionadas con las actividades que realiza en su trabajo y las responsabilidades que tiene.', 2),(17, 'Parte III', NULL, 'Las preguntas siguientes están relacionadas con el tiempo destinado a su trabajo y sus responsabilidades familiares.', 2),(18, 'Parte IV', NULL, 'Las preguntas siguientes están relacionadas con las decisiones que puede tomar en su trabajo.', 2),(19, 'Parte V', NULL, 'Las preguntas siguientes están relacionadas con la capacitación e información que recibe sobre su trabajo.', 2),(20, 'Parte VI', NULL, 'Las preguntas siguientes se refieren a las relaciones con sus compañeros de trabajo y su jefe.', 2),(21, 'Parte VII', 'Las preguntas siguientes están relacionadas con la atención a clientes y usuarios.', 'Si su respuesta fue SÍ, responda las preguntas siguientes. Si su respuesta fue NO pase a las preguntas de la sección siguiente.', 2),(22, 'Parte VIII', NULL, 'Si su respuesta fue SÍ, responda las preguntas siguientes. Si su respuesta fue NO, ha concluido el cuestionario.<br/>Las siguientes preguntas están relacionadas con las actitudes de los trabajadores que supervisa.', 2),(23, 'Acontecimiento traumático severo', 'I.- Acontecimiento traumático severo', '¿Ha presenciado o sufrido alguna vez, durante o con motivo del trabajo un acontecimiento como los siguientes:', 1),(24, 'Recuerdos persistentes', 'II.- Recuerdos persistentes sobre el acontecimiento (durante el último mes):', NULL, 1),(25, 'Esfuerzo por evitar circunstancias', 'III.- Esfuerzo por evitar circunstancias parecidas o asociadas al acontecimiento (durante el último mes):', NULL, 1),(26, 'Afectación ', 'IV.- Afectación (durante el último mes):', NULL, 1);";
                            
                            DB::connection($conexion)->statement($values);
                        }
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                 /*-- Volcando estructura para tabla empresa000xxx.catalogos_norma*/
                case 'catalogos_norma':

                    try{
                                
                        $nombreTablaNO035='catalogos_norma';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `dato` varchar(255) NOT NULL,
                            `orden` int(11) DEFAULT NULL,
                            `clase` varchar(45) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                        $resp = DB::connection($conexion)->table($nombreTablaNO035)->count();
                        if($resp===0){
                            
                            $values = "INSERT INTO ".$base.".".$nombreTablaNO035."(`id`, `dato`, `orden`, `clase`) VALUES(1, 'Ambiente de trabajo', 1, 'categoria'),(2, 'Condiciones deficientes e insalubres', 2, 'categoria'),(3, 'Trabajos peligrosos', 3, 'categoria'),(4, 'Factores propios de la actividad', 4, 'categoria'),(5, 'Organización del tiempo de trabajo', 5, 'categoria'),(6, 'Liderazgo y relaciones en el trabajo', 6, 'categoria'),(7, 'Entorno organizacional', 7, 'categoria'),(8, 'Condiciones en el ambiente de trabajo', 1, 'dominio'),(9, 'Carga de trabajo', 2, 'dominio'),(10, 'Falta de control sobre el trabajo', 3, 'dominio'),(11, 'Jornada de trabajo', 4, 'dominio'),(12, 'Interferencia en la relación trabajo-famila', 5, 'dominio'),(13, 'Liderazgo', 6, 'dominio'),(14, 'Relaciones en el trabajo', 7, 'dominio'),(15, 'Violencia', 8, 'dominio'),(16, 'Reconocimiento del desempeño', 9, 'dominio'),(17, 'Insuficiente sentido de pertenencia e, inestabilidad', 10, 'dominio'),(18, 'Masculino', 1, 'sexo'),(19, 'Femenino', 2, 'sexo'),(20, 'Otro', 3, 'sexo'),(21, '15 - 19', 1, 'edad'),(22, '20 - 24', 2, 'edad'),(23, '25 - 29', 3, 'edad'),(24, '30 - 34', 4, 'edad'),(25, '35 - 39', 5, 'edad'),(26, '40 - 44 ', 6, 'edad'),(27, '45 - 49', 7, 'edad'),(28, '50 - 54', 8, 'edad'),(29, 'Casado', 1, 'estado_civil'),(30, 'Soltero', 2, 'estado_civil'),(31, 'Unión libre', 3, 'estado_civil'),(32, 'Divorciado', 4, 'estado_civil'),(33, 'Viudo', 5, 'estado_civil'),(34, 'Secundaria terminada', 1, 'nivel_estudios'),(35, 'Preparatoria o Bachillerato terminada', 3, 'nivel_estudios'),(37, 'Técnico Superior terminada', 5, 'nivel_estudios'),(38, 'Licenciatura terminada', 7, 'nivel_estudios'),(39, 'Maestría terminada', 9, 'nivel_estudios'),(40, 'Doctorado terminada', 11, 'nivel_estudios'),(41, 'Secundaria Incompleta', 2, 'nivel_estudios'),(42, 'Preparatoria o Bachillerato Incompleta', 4, 'nivel_estudios'),(43, 'Técnico Superior Incompleta', 6, 'nivel_estudios'),(44, 'Licenciatura Incompleta', 8, 'nivel_estudios'),(45, 'Maestría Incompleta', 10, 'nivel_estudios'),(46, 'Doctorado Incompleta', 12, 'nivel_estudios'),(47, 'Operativo', 1, 'tipo_puesto'),(48, 'Supervisor', 2, 'tipo_puesto'),(49, 'Profesional o técnico', 3, 'tipo_puesto'),(50, 'Gerente', 4, 'tipo_puesto'),(51, 'Por proyecto', 1, 'tipo_contratacion'),(52, 'Tiempo indeterminado', 2, 'tipo_contratacion'),(53, 'Por tiempo determinado (temporal)', 3, 'tipo_contratacion'),(54, 'Honorarios', 4, 'tipo_contratacion'),(55, 'Sindicalizado', 1, 'tipo_personal'),(56, 'Confianza', 1, 'tipo_personal'),(57, 'Ninguno', 1, 'tipo_personal'),(58, 'Fijo diurno (entre las 6:00 y 20:00 hrs)', 1, 'tipo_jornada'),(59, 'de 8:00 a 14:00 hrs', 3, 'tipo_jornada'),(60, 'de 9:00 a 19:00 hrs', 2, 'tipo_jornada'),(61, 'de 10:00 a 20:00 hrs', 4, 'tipo_jornada'),(62, 'Si', 1, 'rotacion_turnos'),(63, 'No', 2, 'rotacion_turnos'),(64, 'Menos de 3 meses', 1, 'experiencia_puesto_actual'),(65, 'Entre 5 a 9 años', 4, 'experiencia_puesto_actual'),(66, 'Entre 6 meses y 1 año', 2, 'experiencia_puesto_actual'),(67, 'Más de 9 años', 5, 'experiencia_puesto_actual'),(68, 'Entre 1 a 4 años', 3, 'experiencia_puesto_actual'),(69, 'Menos de 6 meses', 1, 'experiencia_laboral'),(70, 'Entre 6 meses y 1 año', 3, 'experiencia_laboral'),(71, 'Entre 1 a 4 años', 5, 'experiencia_laboral'),(72, 'Entre 5 a 9 años', 7, 'experiencia_laboral'),(73, 'Entre 10 a 14 años', 2, 'experiencia_laboral'),(74, 'Entre 15 a 19 años', 4, 'experiencia_laboral'),(75, 'Entre 20 a 24 años', 6, 'experiencia_laboral'),(76, '25 años o más', 8, 'experiencia_laboral'),(77, 'Nivel academico', 3, 'grafica'),(78, 'Género', 1, 'grafica'),(79, 'Edad', 2, 'grafica'),(80, 'Área de trabajo', 4, 'grafica'),(81, 'General', 5, 'grafica');";
                            
                            DB::connection($conexion)->statement($values);
                        }
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.preguntas*/
                case 'preguntas':

                    try{
                                
                        $nombreTablaNO035='preguntas';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `pregunta` varchar(255) NOT NULL,
                            `pregunta_simple` varchar(255) DEFAULT NULL,
                            `tipo_respuesta` int(11) DEFAULT NULL,
                            `idcategoria` int(11) DEFAULT NULL,
                            `iddominio` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_preguntas_catalogos_preguntas_norma1_idx` (`idcategoria`),
                            KEY `fk_preguntas_catalogos_preguntas_norma2_idx` (`iddominio`),
                            CONSTRAINT `fk_preguntas_catalogos_preguntas_norma1` FOREIGN KEY (`idcategoria`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_preguntas_catalogos_preguntas_norma2` FOREIGN KEY (`iddominio`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                        $resp = DB::connection($conexion)->table($nombreTablaNO035)->count();
                        if($resp===0){
                            
                            $values = "INSERT INTO ".$base.".".$nombreTablaNO035." (`id`, `pregunta`, `pregunta_simple`, `tipo_respuesta`, `idcategoria`, `iddominio`) VALUES
                            (1, 'El espacio donde trabajo me permite realizar mis actividades de manera segura e higiénica', NULL, 1, 1, 8),
                            (2, 'Mi trabajo me exige hacer mucho esfuerzo físico', NULL, 2, 1, 8),
                            (3, 'Me preocupa sufrir un accidente en mi trabajo', NULL, 2, 1, 8),
                            (4, 'Considero que en mi trabajo se aplican las normas de seguridad y salud en el trabajo', NULL, 1, 1, 8),
                            (5, 'Considero que las actividades que realizo son peligrosas', NULL, 2, 1, 8),
                            (6, 'Por la cantidad de trabajo que tengo debo quedarme tiempo adicional a mi turno', NULL, 2, 4, 9),
                            (7, 'Por la cantidad de trabajo que tengo debo trabajar sin parar', NULL, 2, 4, 9),
                            (8, 'Considero que es necesario mantener un ritmo de trabajo acelerado', NULL, 2, 4, 9),
                            (9, 'Mi trabajo exige que esté muy concentrado', NULL, 2, 4, 9),
                            (10, 'Mi trabajo requiere que memorice mucha información', NULL, 2, 4, 9),
                            (11, 'En mi trabajo tengo que tomar decisiones difíciles muy rápido', NULL, 2, 4, 9),
                            (12, 'Mi trabajo exige que atienda varios asuntos al mismo tiempo', NULL, 2, 4, 9),
                            (13, 'En mi trabajo soy responsable de cosas de mucho valor', NULL, 2, 4, 9),
                            (14, 'Respondo ante mi jefe por los resultados de toda mi área de trabajo', NULL, 2, 4, 9),
                            (15, 'En el trabajo me dan órdenes contradictorias', NULL, 2, 4, 9),
                            (16, 'Considero que en mi trabajo me piden hacer cosas innecesarias', NULL, 2, 4, 9),
                            (17, 'Trabajo horas extras más de tres veces a la semana', NULL, 2, 5, 11),
                            (18, 'Mi trabajo me exige laborar en días de descanso, festivos o fines de semana', NULL, 2, 5, 11),
                            (19, 'Considero que el tiempo en el trabajo es mucho y perjudica mis actividades familiares o personales', NULL, 2, 5, 12),
                            (20, 'Debo atender asuntos de trabajo cuando estoy en casa', NULL, 2, 5, 12),
                            (21, 'Pienso en las actividades familiares o personales cuando estoy en mi trabajo', NULL, 2, 5, 12),
                            (22, 'Pienso que mis responsabilidades familiares afectan mi trabajo ', NULL, 2, 5, 12),
                            (23, 'Mi trabajo permite que desarrolle nuevas habilidades', NULL, 1, 4, 10),
                            (24, 'En mi trabajo puedo aspirar a un mejor puesto', NULL, 1, 4, 10),
                            (25, 'Durante mi jornada de trabajo puedo tomar pausas cuando las necesito', NULL, 1, 4, 10),
                            (26, 'Puedo decidir cuánto trabajo realizo durante la jornada laboral', NULL, 1, 4, 10),
                            (27, 'Puedo decidir la velocidad a la que realizo mis actividades en mi trabajo', NULL, 1, 4, 10),
                            (28, 'Puedo cambiar el orden de las actividades que realizo en mi trabajo', NULL, 1, 4, 10),
                            (29, 'Los cambios que se presentan en mi trabajo dificultan mi labor', NULL, 2, 4, 10),
                            (30, 'Cuando se presentan cambios en mi trabajo se tienen en cuenta mis ideas o aportaciones', NULL, 1, 4, 10),
                            (31, 'Me informan con claridad cuáles son mis funciones', NULL, 1, 6, 13),
                            (32, 'Me explican claramente los resultados que debo obtener en mi trabajo', NULL, 1, 6, 13),
                            (33, 'Me explican claramente los objetivos de mi trabajo', NULL, 1, 6, 13),
                            (34, 'Me informan con quién puedo resolver problemas o asuntos de trabajo', NULL, 1, 6, 13),
                            (35, 'Me permiten asistir a capacitaciones relacionadas con mi trabajo', NULL, 1, 4, 10),
                            (36, 'Recibo capacitación útil para hacer mi trabajo', NULL, 1, 4, 10),
                            (37, 'Mi jefe ayuda a organizar mejor el trabajo', NULL, 1, 6, 13),
                            (38, 'Mi jefe tiene en cuenta mis puntos de vista y opiniones', NULL, 1, 6, 13),
                            (39, 'Mi jefe me comunica a tiempo la información relacionada con el trabajo', NULL, 1, 6, 13),
                            (40, 'La orientación que me da mi jefe me ayuda a realizar mejor mi trabajo', NULL, 1, 6, 13),
                            (41, 'Mi jefe ayuda a solucionar los problemas que se presentan en el trabajo', NULL, 1, 6, 13),
                            (42, 'Puedo confiar en mis compañeros de trabajo', NULL, 1, 6, 14),
                            (43, 'Entre compañeros solucionamos los problemas de trabajo de forma respetuosa', NULL, 1, 6, 14),
                            (44, 'En mi trabajo me hacen sentir parte del grupo', NULL, 1, 6, 14),
                            (45, 'Cuando tenemos que realizar trabajo de equipo los compañeros colaboran', NULL, 1, 6, 14),
                            (46, 'Mis compañeros de trabajo me ayudan cuando tengo dificultades', NULL, 1, 6, 14),
                            (47, 'Me informan sobre lo que hago bien en mi trabajo', NULL, 1, 7, 16),
                            (48, 'La forma como evalúan mi trabajo en mi centro de trabajo me ayuda a mejorar mi desempeño', NULL, 1, 7, 16),
                            (49, 'En mi centro de trabajo me pagan a tiempo mi salario', NULL, 1, 7, 16),
                            (50, 'El pago que recibo es el que merezco por el trabajo que realizo', NULL, 1, 7, 16),
                            (51, 'Si obtengo los resultados esperados en mi trabajo me recompensan o reconocen', NULL, 1, 7, 16),
                            (52, 'Las personas que hacen bien el trabajo pueden crecer laboralmente', NULL, 1, 7, 16),
                            (53, 'Considero que mi trabajo es estable', NULL, 1, 7, 17),
                            (54, 'En mi trabajo existe continua rotación de personal', NULL, 2, 7, 17),
                            (55, 'Siento orgullo de laborar en este centro de trabajo', NULL, 1, 7, 17),
                            (56, 'Me siento comprometido con mi trabajo', NULL, 1, 7, 17),
                            (57, 'En mi trabajo puedo expresarme libremente sin interrupciones', NULL, 1, 6, 15),
                            (58, 'Recibo críticas constantes a mi persona y/o trabajo', NULL, 2, 6, 15),
                            (59, 'Recibo burlas, calumnias, difamaciones, humillaciones o ridiculizaciones', NULL, 2, 6, 15),
                            (60, 'Se ignora mi presencia o se me excluye de las reuniones de trabajo y en la toma de decisiones', NULL, 2, 6, 15),
                            (61, 'Se manipulan las situaciones de trabajo para hacerme parecer un mal trabajador', NULL, 2, 6, 15),
                            (62, 'Se ignoran mis éxitos laborales y se atribuyen a otros trabajadores', NULL, 2, 6, 15),
                            (63, 'Me bloquean o impiden las oportunidades que tengo para obtener ascenso o mejora en mi trabajo', NULL, 2, 6, 15),
                            (64, 'He presenciado actos de violencia en mi centro de trabajo', NULL, 2, 6, 15),
                            (65, 'Atiendo clientes o usuarios muy enojados', NULL, 2, 4, 9),
                            (66, 'Mi trabajo me exige atender personas muy necesitadas de ayuda o enfermas', NULL, 2, 4, 9),
                            (67, 'Para hacer mi trabajo debo demostrar sentimientos distintos a los míos', NULL, 2, 4, 9),
                            (68, 'Mi trabajo me exige atender situaciones de violencia', NULL, 2, 4, 9),
                            (69, 'Comunican tarde los asuntos de trabajo', NULL, 2, 6, 14),
                            (70, 'Dificultan el logro de los resultados del trabajo', NULL, 2, 6, 14),
                            (71, 'Cooperan poco cuando se necesita', NULL, 2, 6, 14),
                            (72, 'Ignoran las sugerencias para mejorar su trabajo', NULL, 2, 6, 14),
                            (73, 'En mi trabajo debo brindar servicio a clientes o usuarios:', NULL, 1, NULL, NULL),
                            (74, 'Soy jefe de otros trabajadores:', NULL, 1, NULL, NULL),
                            (75, '¿Accidente que tenga como consecuencia la muerte, la pérdida de un miembro o una lesión grave?', NULL, 1, NULL, NULL),
                            (76, '¿Asaltos?', NULL, 1, NULL, NULL),
                            (77, '¿Actos violentos que derivaron en lesiones graves?', NULL, 1, NULL, NULL),
                            (78, '¿Secuestro?', NULL, 1, NULL, NULL),
                            (79, '¿Amenazas?, o', NULL, 1, NULL, NULL),
                            (80, '¿Cualquier otro que ponga en riesgo su vida o salud, y/o la de otras personas?', NULL, 1, NULL, NULL),
                            (81, '¿Ha tenido recuerdos recurrentes sobre el acontecimiento que le provocan malestares?', NULL, 1, NULL, NULL),
                            (82, '¿Ha tenido sueños de carácter recurrente sobre el acontecimiento, que le producen malestar?', NULL, 1, NULL, NULL),
                            (83, '¿Se ha esforzado por evitar todo tipo de sentimientos, conversaciones o situaciones que le puedan recordar el acontecimiento?', NULL, 1, NULL, NULL),
                            (84, '¿Se ha esforzado por evitar todo tipo de actividades, lugares o personas que motivan recuerdos del acontecimiento?', NULL, 1, NULL, NULL),
                            (85, '¿Ha tenido dificultad para recordar alguna parte importante del evento?', NULL, 1, NULL, NULL),
                            (86, '¿Ha disminuido su interés en sus actividades cotidianas?', NULL, 1, NULL, NULL),
                            (87, '¿Se ha sentido usted alejado o distante de los demás?', NULL, 1, NULL, NULL),
                            (88, '¿Ha notado que tiene dificultad para expresar sus sentimientos?', NULL, 1, NULL, NULL),
                            (89, '¿Ha tenido la impresión de que su vida se va a acortar, que va a morir antes que otras personas o que tiene un futuro limitado?', NULL, 1, NULL, NULL),
                            (90, '¿Ha tenido usted dificultades para dormir?', NULL, 1, NULL, NULL),
                            (91, '¿Ha estado particularmente irritable o le han dado arranques de coraje?', NULL, 1, NULL, NULL),
                            (92, '¿Ha tenido dificultad para concentrarse?', NULL, 1, NULL, NULL),
                            (93, '¿Ha estado nervioso o constantemente en alerta?', NULL, 1, NULL, NULL),
                            (94, '¿Se ha sobresaltado fácilmente por cualquier cosa?', NULL, 1, NULL, NULL);";
                            
                            DB::connection($conexion)->statement($values);
                        }
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.bloque_preguntas*/
                case 'bloque_preguntas':

                    try{
                                
                        $nombreTablaNO035='bloque_preguntas';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idpregunta` int(11) NOT NULL,
                            `orden` int(11) NOT NULL,
                            `idbloque` int(11) NOT NULL,
                            `condicional` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_cuestionario_preguntas_preguntas1_idx` (`idpregunta`),
                            KEY `fk_cuestionario_preguntas_bloque1_idx` (`idbloque`),
                            CONSTRAINT `fk_cuestionario_preguntas_Bloque1` FOREIGN KEY (`idbloque`) REFERENCES `bloques_cuestionario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_cuestionario_preguntas_preguntas1` FOREIGN KEY (`idpregunta`) REFERENCES `preguntas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                        $resp = DB::connection($conexion)->table($nombreTablaNO035)->count();
                        if($resp===0){
                            
                            $values = "INSERT INTO ".$base.".".$nombreTablaNO035."(`id`, `idpregunta`, `orden`, `idbloque`, `condicional`) VALUES
                            (1, 1, 1, 1, 0),
                            (2, 2, 2, 1, 0),
                            (3, 3, 3, 1, 0),
                            (4, 4, 4, 1, 0),
                            (5, 5, 5, 1, 0),
                            (6, 6, 1, 2, 0),
                            (7, 7, 2, 2, 0),
                            (8, 8, 3, 2, 0),
                            (9, 9, 1, 3, 0),
                            (10, 10, 2, 3, 0),
                            (11, 11, 3, 3, 0),
                            (12, 12, 4, 3, 0),
                            (13, 13, 1, 4, 0),
                            (14, 14, 2, 4, 0),
                            (15, 15, 3, 4, 0),
                            (16, 16, 4, 4, 0),
                            (17, 17, 1, 5, 0),
                            (18, 18, 2, 5, 0),
                            (19, 19, 3, 5, 0),
                            (20, 20, 4, 5, 0),
                            (21, 21, 5, 5, 0),
                            (22, 22, 6, 5, 0),
                            (23, 23, 1, 6, 0),
                            (24, 24, 2, 6, 0),
                            (25, 25, 3, 6, 0),
                            (26, 26, 4, 6, 0),
                            (27, 27, 5, 6, 0),
                            (28, 28, 6, 6, 0),
                            (29, 29, 1, 7, 0),
                            (30, 30, 2, 7, 0),
                            (31, 31, 1, 8, 0),
                            (32, 32, 2, 8, 0),
                            (33, 33, 3, 8, 0),
                            (34, 34, 4, 8, 0),
                            (35, 35, 5, 8, 0),
                            (36, 36, 6, 8, 0),
                            (37, 37, 1, 9, 0),
                            (38, 38, 2, 9, 0),
                            (39, 39, 3, 9, 0),
                            (40, 40, 4, 9, 0),
                            (41, 41, 5, 9, 0),
                            (42, 42, 1, 10, 0),
                            (43, 43, 2, 10, 0),
                            (44, 44, 3, 10, 0),
                            (45, 45, 4, 10, 0),
                            (46, 46, 5, 10, 0),
                            (47, 47, 1, 11, 0),
                            (48, 48, 2, 11, 0),
                            (49, 49, 3, 11, 0),
                            (50, 50, 4, 11, 0),
                            (51, 51, 5, 11, 0),
                            (52, 52, 6, 11, 0),
                            (53, 53, 7, 11, 0),
                            (54, 54, 8, 11, 0),
                            (55, 55, 9, 11, 0),
                            (56, 56, 10, 11, 0),
                            (57, 57, 1, 12, 0),
                            (58, 58, 2, 12, 0),
                            (59, 59, 3, 12, 0),
                            (60, 60, 4, 12, 0),
                            (61, 61, 5, 12, 0),
                            (62, 62, 6, 12, 0),
                            (63, 63, 7, 12, 0),
                            (64, 64, 8, 12, 0),
                            (65, 65, 1, 13, 0),
                            (66, 66, 2, 13, 0),
                            (67, 67, 3, 13, 0),
                            (68, 68, 4, 13, 0),
                            (69, 69, 1, 14, 0),
                            (70, 70, 2, 14, 0),
                            (71, 71, 3, 14, 0),
                            (72, 72, 4, 14, 0),
                            (73, 73, 0, 13, 1),
                            (74, 74, 0, 14, 1),
                            (75, 2, 1, 15, 0),
                            (76, 3, 2, 15, 0),
                            (77, 5, 3, 15, 0),
                            (78, 6, 4, 15, 0),
                            (79, 7, 5, 15, 0),
                            (80, 8, 6, 15, 0),
                            (81, 9, 7, 15, 0),
                            (82, 10, 8, 15, 0),
                            (83, 12, 9, 15, 0),
                            (84, 13, 1, 16, 0),
                            (85, 14, 2, 16, 0),
                            (86, 15, 3, 16, 0),
                            (87, 16, 4, 16, 0),
                            (88, 17, 1, 17, 0),
                            (89, 18, 2, 17, 0),
                            (90, 19, 3, 17, 0),
                            (91, 21, 4, 17, 0),
                            (92, 23, 1, 18, 0),
                            (93, 24, 2, 18, 0),
                            (94, 25, 3, 18, 0),
                            (95, 27, 4, 18, 0),
                            (96, 28, 5, 18, 0),
                            (97, 31, 1, 19, 0),
                            (98, 32, 2, 19, 0),
                            (99, 34, 3, 19, 0),
                            (100, 35, 4, 19, 0),
                            (101, 36, 5, 19, 0),
                            (102, 38, 1, 20, 0),
                            (103, 41, 2, 20, 0),
                            (104, 42, 3, 20, 0),
                            (105, 45, 4, 20, 0),
                            (106, 46, 5, 20, 0),
                            (107, 57, 6, 20, 0),
                            (108, 58, 7, 20, 0),
                            (109, 59, 8, 20, 0),
                            (110, 60, 9, 20, 0),
                            (111, 61, 10, 20, 0),
                            (112, 62, 11, 20, 0),
                            (113, 63, 12, 20, 0),
                            (114, 64, 13, 20, 0),
                            (115, 73, 0, 21, 1),
                            (116, 65, 1, 21, 0),
                            (117, 66, 2, 21, 0),
                            (118, 67, 3, 21, 0),
                            (119, 74, 0, 22, 1),
                            (120, 69, 1, 22, 0),
                            (121, 70, 2, 22, 0),
                            (122, 72, 3, 22, 0),
                            (123, 75, 1, 23, 0),
                            (124, 76, 2, 23, 0),
                            (125, 77, 3, 23, 0),
                            (126, 78, 4, 23, 0),
                            (127, 79, 5, 23, 0),
                            (128, 80, 6, 23, 0),
                            (129, 81, 1, 24, 0),
                            (130, 82, 2, 24, 0),
                            (131, 83, 1, 25, 0),
                            (132, 84, 2, 25, 0),
                            (133, 85, 3, 25, 0),
                            (134, 86, 4, 25, 0),
                            (135, 87, 5, 25, 0),
                            (136, 88, 6, 25, 0),
                            (137, 89, 7, 25, 0),
                            (138, 90, 1, 26, 0),
                            (139, 91, 2, 26, 0),
                            (140, 92, 3, 26, 0),
                            (141, 93, 4, 26, 0),
                            (142, 94, 5, 26, 0);";
                            
                            DB::connection($conexion)->statement($values);
                        }
                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.informacion_trabajadores*/
                case 'informacion_trabajadores':

                    try{
                                
                        $nombreTablaNO035='informacion_trabajadores';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `estatus` int(11) NOT NULL,
                            `nombre` varchar(255) DEFAULT NULL,
                            `paterno` varchar(45) DEFAULT NULL,
                            `materno` varchar(45) DEFAULT NULL,
                            `sexo` int(11) DEFAULT NULL,
                            `edad` int(11) DEFAULT NULL,
                            `estado_civil` int(11) DEFAULT NULL,
                            `nivel_estudios` int(11) DEFAULT NULL,
                            `tipo_puesto` int(11) DEFAULT NULL,
                            `tipo_contratacion` int(11) DEFAULT NULL,
                            `tipo_personal` int(11) DEFAULT NULL,
                            `tipo_jornada` int(11) DEFAULT NULL,
                            `rotacion_turnos` int(11) DEFAULT NULL,
                            `experiencia_puesto_actual` int(11) DEFAULT NULL,
                            `experiencia_laboral` int(11) DEFAULT NULL,
                            `empleados_id` int(11) NULL DEFAULT NULL,
                            `informacion_validada` int(11) NOT NULL,
                            `correo` varchar(65) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma1_idx` (`sexo`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma2_idx` (`edad`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma3_idx` (`estado_civil`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma4_idx` (`nivel_estudios`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma5_idx` (`tipo_puesto`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma6_idx` (`tipo_contratacion`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma7_idx` (`tipo_personal`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma8_idx` (`tipo_jornada`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma9_idx` (`rotacion_turnos`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma10_idx` (`experiencia_puesto_actual`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma11_idx` (`experiencia_laboral`),
                            -- KEY `fk_trabajadores_cuestionario_empleados1_idx` (`empleados_id`),
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma1` FOREIGN KEY (`sexo`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma10` FOREIGN KEY (`experiencia_puesto_actual`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma11` FOREIGN KEY (`experiencia_laboral`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma2` FOREIGN KEY (`edad`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma3` FOREIGN KEY (`estado_civil`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma4` FOREIGN KEY (`nivel_estudios`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma5` FOREIGN KEY (`tipo_puesto`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma6` FOREIGN KEY (`tipo_contratacion`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma7` FOREIGN KEY (`tipo_personal`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma8` FOREIGN KEY (`tipo_jornada`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma9` FOREIGN KEY (`rotacion_turnos`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            -- CONSTRAINT `fk_trabajadores_cuestionario_empleados1` FOREIGN KEY (`empleados_id`) REFERENCES `empleados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.cuestionarios_trabajadores*/
                case 'cuestionarios_trabajadores':

                    try{
                                
                        $nombreTablaNO035='cuestionarios_trabajadores';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idperiodo` int(11) NOT NULL,
                            `idcuestionario` int(11) NOT NULL,
                            `idinformacion_trabajador` int(11) NOT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `fecha_inicio` datetime DEFAULT NULL,
                            `fecha_fin` datetime DEFAULT NULL,
                            `total_cuestionario` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_periodo_seleccionado_cuestionario_periodo_norma1_idx` (`idperiodo`),
                            KEY `fk_periodo_seleccionado_cuestionario_cuestionarios1_idx` (`idcuestionario`),
                            KEY `fk_periodo_seleccionado_cuestionario_seleccionados_cuestion_idx` (`idinformacion_trabajador`),
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_cuestionarios1` FOREIGN KEY (`idcuestionario`) REFERENCES `cuestionarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_periodo_norma1` FOREIGN KEY (`idperiodo`) REFERENCES `periodos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_seleccionados_cuestionar1` FOREIGN KEY (`idinformacion_trabajador`) REFERENCES `informacion_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.encargados*/
                case 'encargados':

                    try{
                                
                        $nombreTablaNO035='encargados';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(255) NOT NULL,
                            `correo` varchar(255) NOT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_encargados_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            CONSTRAINT `fk_encargados_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.interpretaciones*/
                case 'interpretaciones':

                    try{
                                
                        $nombreTablaNO035='interpretaciones';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `interpretacion` varchar(700) DEFAULT NULL,
                            `tipo_grafica` int(11) DEFAULT NULL,
                            `imagen` varchar(100) DEFAULT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            `idcatalogo_norma` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_interpretaciones_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            KEY `fk_interpretaciones_catalogos_norma1_idx` (`idcatalogo_norma`),
                            CONSTRAINT `fk_interpretaciones_catalogos_norma1` FOREIGN KEY (`idcatalogo_norma`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_interpretaciones_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.respuestas_cuestionarios*/
                case 'respuestas_cuestionarios':

                    try{
                                
                        $nombreTablaNO035='respuestas_cuestionarios';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idcuestionario_trabajador` int(11) NOT NULL,
                            `idpregunta` int(11) NOT NULL,
                            `valor` int(11) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_respuestas_cuestionarios_cuestionarios_trabajadores1_idx` (`idcuestionario_trabajador`),
                            KEY `fk_respuestas_cuestionarios_preguntas1_idx` (`idpregunta`),
                            CONSTRAINT `fk_respuestas_cuestionarios_cuestionarios_trabajadores1` FOREIGN KEY (`idcuestionario_trabajador`) REFERENCES `cuestionarios_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_respuestas_cuestionarios_preguntas1` FOREIGN KEY (`idpregunta`) REFERENCES `preguntas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.totales_clasificacion*/
                case 'totales_clasificacion':

                    try{
                                
                        $nombreTablaNO035='totales_clasificacion';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idclasificacion` int(11) NOT NULL,
                            `idcuestionario_trabajador` int(11) NOT NULL,
                            `total` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_totales_clasificacion_cuestionarios_trabajadores1_idx` (`idcuestionario_trabajador`),
                            KEY `fk_totales_clasificacion_catalogos_preguntas_norma1_idx` (`idclasificacion`),
                            CONSTRAINT `fk_totales_clasificacion_catalogos_preguntas_norma1` FOREIGN KEY (`idclasificacion`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_totales_clasificacion_cuestionarios_trabajadores1` FOREIGN KEY (`idcuestionario_trabajador`) REFERENCES `cuestionarios_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.razon_social*/
                case 'razon_social':

                    try{
                                
                        $nombreTablaNO035='razon_social';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `razon_social` VARCHAR(45) NOT NULL,
                            `estatus` INT(11) NOT NULL,
                            `emisora_id` INT(11) NULL DEFAULT NULL,
                            PRIMARY KEY (`id`))
                            ENGINE = InnoDB
                            DEFAULT CHARACTER SET = utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.sedes*/
                case 'sedes':

                    try{
                                
                        $nombreTablaNO035='sedes';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `sede` VARCHAR(45) NOT NULL,
                            `estatus` INT(11) NOT NULL,
                            PRIMARY KEY (`id`))
                            ENGINE = InnoDB
                            DEFAULT CHARACTER SET = utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;

                /*-- Volcando estructura para tabla empresa000xxx.excentos_norma*/
                case 'excentos_norma':

                    try{
                                
                        $nombreTablaNO035='excentos_norma';
                        $tabla = "CREATE TABLE IF NOT EXISTS ".$base.".".$nombreTablaNO035."(
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `nombre` VARCHAR(45) NULL DEFAULT NULL,
                            `paterno` VARCHAR(45) NULL DEFAULT NULL,
                            `materno` VARCHAR(45) NULL DEFAULT NULL,
                            `periodo_norma` INT(11) NOT NULL,
                            `empleados_id` INT(11) NULL DEFAULT NULL,
                            `sexo` INT(11) NULL DEFAULT NULL,
                            `correo` VARCHAR(45) NULL DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            INDEX `fk_excentos_norma_periodos_norma1_idx` (`periodo_norma` ASC) ,
                            CONSTRAINT `fk_excentos_norma_periodos_norma1`
                                FOREIGN KEY (`periodo_norma`)
                                REFERENCES ".$base.".`periodos_norma` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            DEFAULT CHARACTER SET = utf8;";

                        DB::connection($conexion)->statement($tabla);

                    }catch(\Exception $e){
                       
                        $this->descripcionErrorNOM035($e,$nombreTablaNO035);
                    }

                break;
                
            }
        }

        $nombre_mod = "periodos_implementacion";
        $isColExist = Schema::connection($conexion)->hasColumn($nombre_mod,'sedes');

        if(!$isColExist){
            
            $alter_table ="ALTER TABLE ".$base.".".$nombre_mod." 
                ADD COLUMN `sede` INT(11) NULL DEFAULT NULL AFTER `update_at`,
                ADD COLUMN `razon_social` INT(11) NULL DEFAULT NULL AFTER `sede`,
                ADD INDEX `fk_periodos_implementacion_sede1_idx` (`sede` ASC),
                ADD INDEX `fk_periodos_implementacion_razon_social1_idx` (`razon_social` ASC);";

            DB::connection($conexion)->statement($alter_table);
            
            $alter_table ="ALTER TABLE ".$base.".".$nombre_mod." 
                ADD CONSTRAINT `fk_periodos_implementacion_sede1`
                FOREIGN KEY (`sede`)
                REFERENCES ".$base.".`sedes` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
                ADD CONSTRAINT `fk_periodos_implementacion_razon_social1`
                FOREIGN KEY (`razon_social`)
                REFERENCES ".$base.".`razon_social` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION;";

            DB::connection($conexion)->statement($alter_table);
        }
        
        return redirect()->route('norma.normaTabla');
    }

    protected function descripcionErrorNOM035($e,$tabla)
    {
        dd($this->errores[] = 'Error en la tabla: '. $tabla.'. Descripción: ' . $e);
    }

    public function exportEmpNoContestado(Request $request)
    {
      
        try{
            return Excel::download( new exportEmpleadosSinEncuesta($request->id), 'empleados_sin_encuesta_realizada'.date('d-m-Y_H:i').'.xlsx' );
            
        }catch(\Exception $e){
            
            session()->flash('danger', 'Error al realizar la petición comunicate con tu administrador...!!');
            return redirect()->route('norma.normaTabla');
        }  
    }
}

