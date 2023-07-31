<?php

namespace App\Http\Controllers\Empleado;
use DataTables;
use App\Models\PeriodoNorma;
use App\Models\BloqueCuestionario;
use App\Models\Pregunta;
use App\Models\Cuestionario;
use App\Models\CuestionarioTrabajador;
use App\Models\Trabajador;
use App\Models\RespuestaCuestionario;
use App\Models\TotalClasificacion;
use App\Models\PeriodoImplementacion;
use App\Models\Actividad;
use App\Models\Sede;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use DateTime;
use Illuminate\Support\Facades\Schema;

class FormularioNormaController extends Controller
{
    protected const ACTIVIDAD_CREADA = 1;
    protected const ACTIVIDAD_ELIMINADA = 0;

    public function index(Request $request){ 
        elegirBase();
        $hoy = new DateTime();
        $sede_implementacion = null;
        if(isset(session('empleado')->sede) && session('empleado')->sede != 0 && session('empleado')->sede != "" && session('empleado')->sede != null){
            $sede_implementacion = session('empleado')->sede;
        }
        $respuesta = array();
        $actividadPeriodoNorma = $periodoNorma = array();
        $cuestionarioO = new Cuestionario; // instancia de cuestionario
        $oImplementacion = new PeriodoImplementacion; // instancia del periodo de implementacion
        $tarjetaInformacionPersonal = $datosTarjeta = $stepInformativo = $tarjetas = array();
        $conInformativo = 2;
        $tituloNorma = $expansion = "";
        $datosImplementacion = $oImplementacion->validarDiaDentroDePeriodoImplementacion(new DateTime(),$sede_implementacion);

        $hoy = new DateTime();
        $estadoFinal = 0; //en que estado del proceso de llenado se encuentra
        if(!empty($datosImplementacion) && $datosImplementacion->count() > 0){ // verificar si hay un periodo de implementacion activo
            $finicio = new DateTime($datosImplementacion->first()->fecha_inicio);
            $ffin = new DateTime($datosImplementacion->first()->fecha_fin);
            $hoy = new DateTime();
            $i = "<p>Periodo de implementación ".$finicio->format('d-m-Y')." al ".$ffin->format('d-m-Y')."</p>";
            
            $norma="No hay un periodo para captura de cuestionarios";
            $id_periodo_norma = 0;

            //buscar actividad de llenado de formulario
            // $actividadPeriodoNorma = Actividad::whereNotNull('apertura_formulario')->where('estatus',self::ACTIVIDAD_CREADA)->where('idperiodo_implementacion',$datosImplementacion[0]->id)->get();
           $actividadPeriodoNorma = $datosImplementacion->first()->actividad_formulario()->get();

           //validar si existe actividad para el llenado de formulario
            if(!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0){
                $periodoNorma = $actividadPeriodoNorma[0]->formulario; //id de formulario
                if(!empty($periodoNorma) && $periodoNorma->estatus == 1){
                    $fNormaInicio = new DateTime($periodoNorma->fecha_inicio);
                    $fNormaFin = new DateTime($periodoNorma->fecha_fin);
                    $fNormaExp = new DateTime($periodoNorma->fecha_fin_expansion);
                    $id_periodo_norma = $periodoNorma->id;
                    $tituloNorma = "Periodo para el llenado de cuestionarios <br/><b>".$fNormaInicio->format('d-m-Y')."</b> al <b>".$fNormaFin->format('d-m-Y')."</b>";
                    $expansion = "";
                    //valida si esta dentro del periodo de llenado
                    if( $hoy>= $fNormaInicio  &&  $hoy <= $fNormaExp){
                        if($hoy > $fNormaFin  &&  $hoy <= $fNormaExp){
                            $expansion = "<p style='color:red'><small>(periodo especial <b>".$fNormaFin->format('d-m-Y')."</b> al <b>".$fNormaExp->format('d-m-Y')."</b>)</small></p>";
                        }
                        
                        $desabilitado = "disabled";
                        $informacion_personal_validada = 0;

                        $cuestionariosTrabajador = $periodoNorma->trabajadorCuestionarioPeriodo->where('correo', 'like', session('empleado')['correo']);

                        if(count($cuestionariosTrabajador) > 0){
                            $estadoBtnAnterior = 1;
                            foreach($cuestionariosTrabajador as $c){
                                if($c->informacion_validada == 1 && $informacion_personal_validada == 0){  
                                    $informacion_personal_validada = 1;
                                    $desabilitado = "";
                                }
                                //obtener datos del cuestionario
                                $cu = Cuestionario::find($c->pivot->idcuestionario);
                                $stepInformativo[$c->pivot->idcuestionario]['nombre'] = $cu->nombre;

                                $tarjetas['encabezado'] = $cu->nombre;
                                $tarjetas['descripcion'] = $cu->descripcion;
                                $tarjetas['enlace'] = route('empleado.norma.cuestionario');
                                $tarjetas['cuestionario_trabajador'] = $c->pivot->id;
                                $tarjetas['cuestionario'] = $c->pivot->idcuestionario;
                                $tarjetas['desabilitado'] = $desabilitado;

                                $tarjetaInformacionPersonal['informacion_trabajador'] = $tarjetas['informacion_trabajador'] = $c->pivot->idinformacion_trabajador;

                                    if($c->pivot->estatus == 0){
                                        $tarjetas['clases'] = "btn-primary";
                                        $tarjetas['textoBoton'] = "Comenzar cuestionario";
                                    }else if($c->pivot->estatus == 1){
                                        $tarjetas['clases'] = "btn-success";
                                        $tarjetas['textoBoton'] = "Cuestionario completado";
                                        $tarjetas['desabilitado'] = 'disabled="disabled"';
                                        $estadoFinal = $c->pivot->idcuestionario;
                                        $estadoFinal++;
                                        if($c->pivot->idcuestionario == 3){
                                            $estadoFinal++;
                                        }
                                    }else if($c->pivot->estatus == 2){
                                        $tarjetas['clases'] = "btn-danger";
                                        $tarjetas['textoBoton'] = "Reanudar cuestionario";
                                    }
                                    array_push( $datosTarjeta, $tarjetas );
                            }
                            $tarjetaInformacionPersonal['encabezado'] = "Información Personal";
                            $tarjetaInformacionPersonal['descripcion'] = "DATOS RELATIVOS A LAS CARACTERÍSTICAS PERSONALES, SOCIALES Y LABORALES RELEVANTES PARA LA IMPLEMENTACIÓN DE LA NORMA 035";
                            $tarjetaInformacionPersonal['enlace'] = route('empleado.norma.informacionPersonal');
                            $tarjetaInformacionPersonal['cuestionario_trabajador'] = $tarjetaInformacionPersonal['cuestionario'] = 0;
                            $tarjetaInformacionPersonal['desabilitado'] = "";
                            if($informacion_personal_validada == 0){
                                $tarjetaInformacionPersonal['clases'] = "btn-primary";
                                $tarjetaInformacionPersonal['textoBoton'] = "Comenzar cuestionario";
                            }else{
                                $tarjetaInformacionPersonal['clases'] = "btn-success";
                                $tarjetaInformacionPersonal['textoBoton'] = "Cuestionario completado";
                                $tarjetaInformacionPersonal['desabilitado'] = 'disabled="disabled"';
                                if($estadoFinal == 0){
                                    $estadoFinal = 1;
                                }
                            }
                            array_unshift($datosTarjeta, $tarjetaInformacionPersonal);
                            $respuesta['estado'] = 1;
                        }else{
                           $respuesta['estado'] = 6;
                           $respuesta['msj'] ="";
                           $respuesta['tituloMsj'] = "Sin cuestionarios";
                        }

                    }else{
                        if($hoy < $fNormaInicio){
                            $respuesta['estado'] = 5;
                            $respuesta['tituloMsj'] ="Periodo no iniciado";
                            $respuesta['msj'] = "El periodo para llenar los cuestionarios correspondientes a la norma 035 aún no están activos, el llenado comienza el ".$fNormaInicio->format('d-m-y').".";
                        }else{
                            $respuesta['estado'] = 5;
                            $respuesta['tituloMsj'] ="Periodo terminado";
                            $respuesta['msj'] = "El periodo para llenar los cuestionarios correspondientes a la norma 035 ha terminado, agradecemos su valiosa participación.";
                        }
                    }
                }
            }else{
                $respuesta['estado'] = 4;//No hay actividad para el periodo de llenado
                $respuesta['msj'] ="No hay periodo de llenado de cuestionarios";
                $respuesta['tituloMsj'] = "No hay actividad para el periodo de llenado";
            }
        }else{
            $respuesta['estado'] = 2; //No hay implementación
            $respuesta['msj'] ="No hay ninguna implementación vigente para cuestionario de la norma 035.";
            $respuesta['tituloMsj'] = "No hay implementación";
        }
        return view('norma.inicio',compact("respuesta","datosTarjeta","stepInformativo","conInformativo","estadoFinal","tituloNorma","expansion"));
    }

    public function informacionPersonal(Request $request){
        elegirBase();
        $trabajador = Trabajador::find($request->informacion_trabajador);
        $trabajador->estado_civil = $trabajador->edad = 0;
        return view("norma.cuestionarios.informacion_personal_user", compact('trabajador'));
    }

    public function getEstadoCivil($estado_civil){
        if($estado_civil == "CASADA" || $estado_civil == "CASADO"){
            return 29;
        }else if($estado_civil == "SOLTERA" || $estado_civil == "SOLTERO"){
            return 30;
        }else if($estado_civil == "UNIÃ’N LIBRE" || $estado_civil == "UNION LIBRE" || $estado_civil == "UNIÓN LIBRE"){
            return 31;
        }else if($estado_civil == "DIVORCIADA" || $estado_civil == "DIVORCIADO"){
            return 32;
        }else if($estado_civil == "VIUDA" || $estado_civil == "VIUDO"){
            return 33;
        }   
    }

    public function getEdad($edad){
        if($edad >= 15 && $edad <=19){
            return 21;
        }else if($edad >= 20 && $edad <= 24){
            return 22;
        }else if($edad >= 25 && $edad <= 29){
            return 23;
        }else if($edad >= 30 && $edad <= 34){
            return 24;
        }else if($edad >= 35 && $edad <= 39){
            return 25;
        }else if($edad >= 40 && $edad <= 44){
            return 26;
        }else if($edad >= 45 && $edad <= 49){
            return 27;
        }else if($edad >= 50 && $edad <= 54){
            return 28;
        }
    }

    public function confirmarInformacionPersonal(Request $request){
        if ($request->ajax()) {
            elegirBase();
            $trabajador = Trabajador::find($request->informacion_trabajador);
            $trabajador->estatus = 1;
            $trabajador->nombre = $request->nombre ;
            $trabajador->paterno = $request->paterno ;
            $trabajador->materno = $request->materno ;
            $trabajador->sexo = $request->sexo ;
            $trabajador->edad = $request->edad ;
            $trabajador->estado_civil = $request->estado_civil ;
            $trabajador->nivel_estudios = $request->nivel_estudios ;
            $trabajador->tipo_puesto = $request->tipo_puesto ;
            $trabajador->tipo_contratacion = $request->tipo_contratacion ;
            $trabajador->tipo_personal = $request->tipo_personal ;
            $trabajador->tipo_jornada = $request->tipo_jornada ;
            $trabajador->rotacion_turnos = $request->rotacion_turnos ;
            $trabajador->experiencia_puesto_actual = $request->experiencia_puesto_actual;
            $trabajador->experiencia_laboral = $request->experiencia_laboral;
            $trabajador->informacion_validada = 1 ;
            $trabajador->save();
            return response()->json(['ok' => 1,'informacion_trabajador' => $request->informacion_trabajador]);
        }
    }

    public function obtenerCuestionario(Request $request){
        elegirBase();
        $tipo_cuestionario = $request->cuestionario;
        $cuestionario_trabajador = CuestionarioTrabajador::where('id',$request->cuestionario_trabajador)->with('respuestas')->get();
        $cuestionario_trabajador = $cuestionario_trabajador->first();
        if($cuestionario_trabajador->estatus == 0){
            $cuestionario_trabajador->estatus = 2;
            $cuestionario_trabajador->save();
        }
        $respuestas = $cuestionario_trabajador->respuestas->toArray();
        $bloques = BloqueCuestionario::where('idcuestionario','=',$tipo_cuestionario)->with('preguntas')->get();
        return view("norma.cuestionarios.cuestionario_user",compact("bloques","tipo_cuestionario","respuestas","cuestionario_trabajador"));
    }

    public function guardarRespuestas(Request $request){
        elegirBase();
        $total = 0;
        $guardarRespuesta = new RespuestaCuestionario;
        $guardarTotal = new TotalClasificacion;
        $informacion_trabajador = 0;
        $guardarRespuesta->agregarRespuesta($request->respuesta);
        $guardarTotal->actualizarTotalNvo($request->cuestionario_trabajador,$request->respuesta);

        if($request->final == 1){
            $cuestionario_trabajador = CuestionarioTrabajador::find($request->cuestionario_trabajador);
            $cuestionario_trabajador->estatus = 1;
            $cuestionario_trabajador->total_cuestionario = RespuestaCuestionario::where('idcuestionario_trabajador','=',$request->cuestionario_trabajador)->get()->sum('valor');
            $informacion_trabajador = $cuestionario_trabajador->idinformacion_trabajador;
            $cuestionario_trabajador->save();
        }
        return response()->json(['ok' => 1]);
    }
   
}
