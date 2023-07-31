<?php

namespace App\Http\Controllers\norma;

use App\Http\Controllers\Controller;
use DataTables;
use Illuminate\Http\Request;
use App\Models\CuestionarioTrabajador;
use App\Models\PeriodoImplementacion;
use App\Http\Controllers\PonderadorController;
use App\Models\Actividad;
use App\Models\Cuestionario;
use App\Exports\DiagnosticoTrabajadores;
use App\Exports\DiagnosticoTrabajadoresMenosDiesiseis;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;

class ListaEmpleadosController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const ACTIVIDAD_CREADA = 1;
    protected const ACTIVIDAD_ELIMINADA = 0;
    protected const GUIA_REFERENCIA_UNO = 1;
    protected const GUIA_REFERENCIA_DOS = 2;
    protected const GUIA_REFERENCIA_TRES = 3;
    protected const CUESTIONARIO_AGREGADO = 0;

    // D I A G N O S T I C O 
    public function diagnosticoEmpleados(Request $request){
        validarMetodoPost($request,'norma.normaTabla');      
        //dd($norma = $request->norma);
        if($request->implementacion){
            $implementacion = $request->implementacion;

            elegirBase();
            // cambiarBase(Session::get('base'));
            // if ($request->ajax()) {
                
                /*$ponderadores = new PonderadorController();
                $ponderadores->seleccionarPonderadores($request->tcuestionario);
                //dd($trabajador_diagnostico);
                //$trabajadores = Trabajador::join('cuestionarios_trabajadores',"informacion_trabajadores.id","=","cuestionarios_trabajadores.idinformacion_trabajador")->where("cuestionarios_trabajadores.idperiodo",$request->norma)->select("informacion_trabajadores.*"); //->groupBy("informacion_trabajadores.id");
                $tabla = Datatables::of($trabajador_diagnostico)
            ->editColumn('guiaI', function($row){
                    if($row['guiaI'] == "CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-danger'>CANALIZAR</span>";}
                    else if($row['guiaI'] == "NO CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-success'>APROBADO</span>";}
                    else{ return "<span class='txt-secundary'>".$row['guiaI']."</span>";}
                })
                ->editColumn('guiaII', function($row){
                    if($row['guiaII'] == "CANALIZAR") return "<span class='text-secundary font-weight-bold alert-danger'>CANALIZAR</span>";
                    else if($row['guiaII'] == "NO CANALIZAR") return "<span class='text-secundary font-weight-bold alert-success'>APROBADO</span>";
                    else return "<span class='txt-secundary'>".$row['guiaII']."</span>";
                })
                ;

                $pondera = $ponderadores->categoriasYdominios;
                if(!empty($pondera)){
                    foreach($pondera as $cve => $cyd){
                        //echo $cve;
                        $tabla->editColumn($cve, function($row) use ($cve){
                            if($row["$cve"] == "CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-danger'>CANALIZAR</span>";}
                            else if($row["$cve"] == "NO CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-success'>APROBADO</span>";}
                            else{ return "<span class='txt-secundary'>".$row["$cve"]."</span>";}
                        }); 
                    }
                    $tabla->rawColumns(['guiaI','guiaII',"1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17"]);
                }else{
                    $tabla->rawColumns(['guiaI']);
                }
                
                return $tabla->make(true);*/
            // }else{
                $datosImplementacion = PeriodoImplementacion::find($implementacion);
                //dd($datosImplementacion);
                $actividadPeriodoNorma = Actividad::whereNotNull('apertura_formulario')->where('estatus',self::ACTIVIDAD_CREADA)->where('idperiodo_implementacion',$implementacion)->with('formulario')->get();
                //dd($actividadPeriodoNorma);
                $participantes = $periodoNorma = array();
                if(!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0){
                    $periodoNorma = $actividadPeriodoNorma[0]->formulario; 
                    //dd($periodoNorma);
                    $tipo_cuestionario = CuestionarioTrabajador::where('idperiodo',$periodoNorma->id)->select("idcuestionario")->where("idcuestionario","=",2)->orWhere("idcuestionario","=",3)->groupBy("idcuestionario")->get(); 
                    $ponderadores = new PonderadorController();
                    $tcuestionario = $tipo_cuestionario[0]->idcuestionario;
                    $categorias = $ponderadores->seleccionarPonderadores($tcuestionario);
                    $encabezado = $ponderadores->categoriasYdominios;

                    $encabezadoJson = json_encode($encabezado);
                    $trabajador_diagnostico = $this->informacionDiagnostico($periodoNorma->id, $ponderadores);         
                    $ponderadores->seleccionarPonderadores($tcuestionario);
                    // return $periodoNorma->id;
                }
            // }
          
            return view("norma.implementacion.diagnostico",compact("encabezadoJson","datosImplementacion","actividadPeriodoNorma","periodoNorma","encabezado","tcuestionario", "trabajador_diagnostico", "ponderadores"));
        }
        
        //return redirect()->route('norma.implementacion');
    }

    public function informacionDiagnostico($idNorma, $ponderadores){   
        $trabajadores = array();
        elegirBase();
        // cambiarBase(Session::get('base'));
        // $ponderadores = new PonderadorController();
        $resultados = array();      
        
        $cuestionarios = CuestionarioTrabajador::where('idperiodo',$idNorma)->with('totales','datosPersonales')->orderBy("idinformacion_trabajador")->get();  
        $cont = 0;
        foreach($cuestionarios as $c){             
                $ipersonal = $c->datosPersonales->toArray();
                if(empty($trabajadores[$c->idinformacion_trabajador]['nombre'])){
                    $trabajadores[$c->idinformacion_trabajador]['id'] = $ipersonal['id'];
                    $trabajadores[$c->idinformacion_trabajador]['nombre'] = $ipersonal['nombre'] . " " . $ipersonal['paterno'] . " " . $ipersonal['materno'];
                }
                if($c->idcuestionario == 1){ // guia de referencia I
                    if(is_null($c->total_cuestionario)){
                        $trabajadores[$c->idinformacion_trabajador]['guiaI'] = $c->total_cuestionario."Sin resultados";
                    }else{
                        if($c->total_cuestionario > 5){
                            $trabajadores[$c->idinformacion_trabajador]['guiaI'] =  "CANALIZAR";
                        }else{
                            $trabajadores[$c->idinformacion_trabajador]['guiaI'] =  "NO CANALIZAR";
                        }
                    }
                }else{ //guia de referencia II Y III
                    if(count($ponderadores->nivelRiesgoCatDom) == 0){
                        $ponderadores->seleccionarPonderadores($c->idcuestionario);
                    }
                    $trabajadores[$c->idinformacion_trabajador]['guiaII'] = "Sin resultado";
                    //total de las guias 2 o 3
                    foreach($ponderadores->nivelRiesgoTotal as $nivel){
                        
                        if( $c->total_cuestionario > $nivel[0] && $c->total_cuestionario <= $nivel[1]){
                            $trabajadores[$c->idinformacion_trabajador]['guiaII'] = $nivel[5];
                        }
                    }
    
                    $calTotales = $c->totales->toArray();
                   // print_r($calTotales);echo "<br/><br/><br/>";
                    if(isset($calTotales) && count($calTotales) > 0){
    
                        foreach($calTotales as $clave => $cal){ // barrer los totales del empleado
                            $contInicio = 0;// validar la primer validacion
                            foreach($ponderadores->nivelRiesgoCatDom[$cal['id']] as $nivel){
                               // print_r($nivel);echo "<br/><br/><br/>";
                               
                                if($contInicio == 0){
                                    if( $cal['pivot']['total'] >= $nivel[0] && $cal['pivot']['total'] < $nivel[1]){
                                        $trabajadores[$c->idinformacion_trabajador][$cal['pivot']['idclasificacion']] = isset($nivel[5])? $nivel[5]: "--";
                                    }
                                }else{
                                    if( $cal['pivot']['total'] >= $nivel[0] && $cal['pivot']['total'] < $nivel[1]){
                                        $trabajadores[$c->idinformacion_trabajador][$cal['pivot']['idclasificacion']] = isset($nivel[5])? $nivel[5]: "--";
                                       
                                    }
                                }
                                $contInicio++;
                            }
                        }
                    }else{
                        foreach($ponderadores->categoriasYdominios as $i => $cyd){
                            $trabajadores[$c->idinformacion_trabajador][$i] = "Sin resultado";
                        }
                    }
    
                }
        }
        return $trabajadores;    
    }

    /* ---------------------------------------------------------------------------------------------
        R E P O R T E  E N   E X C E L   D I A G N O S T I C O 
    ----------------------------------------------------------------------------------------------- */

    public function exportar(Request $request, $id_norma)
    {      
        elegirBase();
        // cambiarBase(Session::get('base'));
        $request->norma = $id_norma;
        $tipo_cuestionario = CuestionarioTrabajador::select('idcuestionario')->where('idperiodo',$id_norma)->where('idcuestionario',2)->orWhere('idcuestionario',3)->groupBy("idcuestionario")->first()->toArray();
        $ponderadores = new PonderadorController();
        $ponderadores->seleccionarPonderadores($tipo_cuestionario['idcuestionario']);
        return Excel::download(
            new DiagnosticoTrabajadores($this->informacionDiagnostico($id_norma, $ponderadores),$ponderadores->categoriasYdominios),
            'Diagnostico_'.date('d-m-Y_H:i').'.xlsx'
        );
    }

    public function diagnosticoEmpleadosMenosDiesiseis(Request $request){
        validarMetodoPost($request,'norma.normaTabla');
        if($request->implementacion){
            $implementacion = $request->implementacion;
            $norma = $request->norma;
    
            elegirBase();
            // cambiarBase(Session::get('base'));
            /*if ($request->ajax()) {         
                $trabajador_diagnostico = $this->informacionDiagnosticoMenosDiesiseis($request);
                $ponderadores = new PonderadorController();
                $ponderadores->seleccionarPonderadores($request->tcuestionario);
                //$trabajadores = Trabajador::join('cuestionarios_trabajadores',"informacion_trabajadores.id","=","cuestionarios_trabajadores.idinformacion_trabajador")->where("cuestionarios_trabajadores.idperiodo",$request->norma)->select("informacion_trabajadores.*"); //->groupBy("informacion_trabajadores.id");
                $tabla = Datatables::of($trabajador_diagnostico)
               ->editColumn('guiaI', function($row){
                    if($row['guiaI'] == "CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-danger'>CANALIZAR</span>";}
                    else if($row['guiaI'] == "NO CANALIZAR"){ return "<span class='text-secundary font-weight-bold alert-success'>APROBADO</span>";}
                    else{ return "<span class='txt-secundary'>".$row['guiaI']."</span>";}
                })
                
                ;
    
                $tabla->rawColumns(['guiaI']);
                return $tabla->make(true);
            }else{
                $datosImplementacion = PeriodoImplementacion::with('actividad_formulario')->find($implementacion);
                $actividadPeriodoNorma = $datosImplementacion->actividad_formulario;
                $participantes = $periodoNorma = array();
                if(!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0){
                    $periodoNorma = $actividadPeriodoNorma[0]->formularioConCuestionarios; 
                    $bloques = $actividadPeriodoNorma[0]->formularioConCuestionarios->tipoCuestionario[0]->cuestionario->bloques;
                    
                }
                $encabezadoJson = json_encode($bloques);
            }*/
            
            $datosImplementacion = PeriodoImplementacion::with('actividad_formulario')->find($implementacion);
            $actividadPeriodoNorma = $datosImplementacion->actividad_formulario;
            $participantes = $periodoNorma = array();
            if(!empty($actividadPeriodoNorma) && $actividadPeriodoNorma->count() > 0){
                $ponderadores = new PonderadorController();
                $periodoNorma = $actividadPeriodoNorma[0]->formularioConCuestionarios; 
                $bloques = $actividadPeriodoNorma[0]->formularioConCuestionarios->tipoCuestionario[0]->cuestionario->bloques;
                $trabajador_diagnostico = $this->informacionDiagnosticoMenosDiesiseis($periodoNorma->id, $ponderadores);             
            }
            $encabezadoJson = json_encode($bloques);
            
            return view("norma.implementacion.diagnostico-menos16",compact("encabezadoJson","datosImplementacion","actividadPeriodoNorma","periodoNorma","bloques", "trabajador_diagnostico", "ponderadores"));
        }
        
    }

    public function informacionDiagnosticoMenosDiesiseis($idNorma, $ponderadores){
        //echo $request->norma;
        $trabajadores = array();
        elegirBase();
        // cambiarBase(Session::get('base'));
        // $ponderadores = new PonderadorController();
        $resultados = array();
        
        $cuestionarios = CuestionarioTrabajador::where('idperiodo', $idNorma)->with(['respuestas','datosPersonales'])->orderBy("idinformacion_trabajador")->get();
        
        $cont = 0;
        if($cuestionarios->count() > 0){
            $cuestionario_bloques = Cuestionario::with('bloques')->find($cuestionarios[0]->idcuestionario);
        }
        foreach($cuestionarios as $c){
                
                $ipersonal = $c->datosPersonales->toArray();
                if(empty($trabajadores[$c->idinformacion_trabajador]['nombre'])){
                    $trabajadores[$c->idinformacion_trabajador]['id'] = $ipersonal['id'];
                    $trabajadores[$c->idinformacion_trabajador]['nombre'] = $ipersonal['nombre'] . " " . $ipersonal['paterno'] . " " . $ipersonal['materno'];
                }
                if($c->idcuestionario == 1){
                    if(is_null($c->total_cuestionario)){
                        $trabajadores[$c->idinformacion_trabajador]['guiaI'] = $c->total_cuestionario."Sin resultados";
                        //$preguntas = $this->preguntasGuiaReferenciaI();
                        
                            foreach($cuestionario_bloques->bloques as $bloque){
                                foreach($bloque->preguntas as $cve => $pregunta){
                                    $trabajadores[$c->idinformacion_trabajador][$pregunta->id] = '-';
                                }
                            }
                    }else{
                        if($c->total_cuestionario > 5){
                            $trabajadores[$c->idinformacion_trabajador]['guiaI'] =  "CANALIZAR";
                        }else{
                            $trabajadores[$c->idinformacion_trabajador]['guiaI'] =  "NO CANALIZAR";
                        }
                        //$preguntas = $this->preguntasGuiaReferenciaI();
    
                            foreach($cuestionario_bloques->bloques as $bloque){
                                foreach($bloque->preguntas as $cve => $pregunta){
                                    $trabajadores[$c->idinformacion_trabajador][$pregunta->id] = '-';
                                }
                            }
                                     
                        //$respuestas = RespuestaCuestionario::where('idcuestionario_trabajador','=',$c->id)->get();
                        $respuestas = $c->respuestas;
                        
                        if($respuestas->count() > 0){
                            foreach($respuestas as $respuesta){
                                //dd($respuesta->pivot);
                                if($respuesta->pivot->valor == 1){
                                    $trabajadores[$c->idinformacion_trabajador][$respuesta->pivot->idpregunta] = 'SI';
                                }else if($respuesta->pivot->valor == 0){
                                    $trabajadores[$c->idinformacion_trabajador][$respuesta->pivot->idpregunta] = 'NO';
                                }else{
                                    $trabajadores[$c->idinformacion_trabajador][$respuesta->pivot->idpregunta] = '-';
                                }
                                
                            }
                        }

                    }
                }
        }       
       return $trabajadores;
    }

    public function exportarMenosDiesiseis(Request $request, $id_norma)
    {   
        // cambiarBase(Session::get('base'));
        elegirBase();
        $ponderadores = new PonderadorController();
        $request->norma = $id_norma;
        return Excel::download(
            new DiagnosticoTrabajadoresMenosDiesiseis($this->informacionDiagnosticoMenosDiesiseis($id_norma, $ponderadores)),
            'Diagnostico_'.date('d-m-Y_H:i').'.xlsx'
        );
    }
}
