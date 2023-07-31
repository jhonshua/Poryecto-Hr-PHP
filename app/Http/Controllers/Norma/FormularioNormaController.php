<?php

namespace App\Http\Controllers\norma;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use DateTime;
use Illuminate\Support\Facades\Schema;

class FormularioNormaController extends Controller
{


    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const ACTIVIDAD_CREADA = 1;
    protected const ACTIVIDAD_ELIMINADA = 0;

    public function inicio(Request $request)
    { 
        elegirBase();
        //dd(session('empleado'));
        $sede_implementacion = null;
        //dd(session('empleado')->sede,Schema::hasTable('sedes'),session('empleado')->sede != "",session('empleado')->sede != null);
       // if(Schema::hasTable('sedes') && session('empleado')->sede != "" && session('empleado')->sede != null){
        if(isset(session('empleado')->sede) && session('empleado')->sede != 0 && session('empleado')->sede != "" && session('empleado')->sede != null){

            //if(!empty(session('empleado')['sede'])){
            //$sede = Sede::where('id',session('empleado')->sede)->first();
            //dd($sede,'dd');
            $sede_implementacion = session('empleado')->sede;
        }//else{dd("no entra");}
        //dd("aquí no truena");
        $respuesta = array();
        $actividadPeriodoNorma = $periodoNorma = array();
        $cuestionarioO = new Cuestionario; // instancia de cuestionario
        $oImplementacion = new PeriodoImplementacion; // instancia del periodo de implementacion
        $tarjetaInformacionPersonal = $datosTarjeta = $stepInformativo = $tarjetas = array();
        $conInformativo = 2;
        $tituloNorma = $expansion = "";
        //dump($oImplementacion);
        $datosImplementacion = $oImplementacion->validarDiaDentroDePeriodoImplementacion(new DateTime(),$sede_implementacion);
        $estadoFinal = 0; //en que estado del proceso de llenado se encuentra
        //dump($datosImplementacion);
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
           //dd($actividadPeriodoNorma);
            if(!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0){
                $periodoNorma = $actividadPeriodoNorma[0]->formulario; //id de formulario
               // dd($periodoNorma);
                if(!empty($periodoNorma) && $periodoNorma->estatus == 1){
                   // dd($periodoNorma);
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
                        
                        //dd($periodoNorma);
                        $cuestionariosTrabajador = $periodoNorma->trabajadorCuestionarioPeriodo->where('correo',strtolower(session('empleado')['correo']));
                    //    dUMP($periodoNorma->trabajadorCuestionarioPeriodo,session('empleado')['correo']);
                    //    dUMP($cuestionariosTrabajador);
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
            //dd($respuesta);
        }
        return view('norma.inicio',compact("respuesta","datosTarjeta","stepInformativo","conInformativo","estadoFinal","tituloNorma","expansion"));
    }

    public function confirmarInformacionPersonal(Request $request)
    { //ok
        if ($request->ajax()) {
            try{
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
                $respuesta =1;

            }catch(\Exception $e){
                $respuesta =2;
            }
            return response()->json(['ok' => $respuesta,'informacion_trabajador' => $request->informacion_trabajador]);
        }
    }
    public function guardarRespuestas(Request $request)
    {
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
