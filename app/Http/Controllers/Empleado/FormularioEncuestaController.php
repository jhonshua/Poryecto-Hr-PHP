<?php

namespace App\Http\Controllers\Empleado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\FormularioEncuesta;
use App\Models\FormularioPreguntas;
use App\Models\FormularioRespuesta;
use App\Models\FormularioTipo;
use App\Models\ConfiguracionFormulario;
use App\Models\DetalleFormularioEncuesta;
use App\EmpleadoLogin;


class FormularioEncuestaController extends Controller
{
    /**
     * 
     */
    public function inicio()
    {
        try{

            cambiarBase(Session::get('base'));
            $email = Session::get('empleado')['correo'];
            $empleado = EmpleadoLogin::where('email', $email)->first();
            $idempleado=DB::connection('empresa')->table('empleados')->select('id')->where('correo',$empleado->email)->first();
            $idempleado=$idempleado->id;
            $consulta=DB::connection('empresa')->table('formulario_respuestas')->where('id_empleado',$idempleado)->get();
            return view('empleados.empleado.formulario_cuestionario',compact('empleado'));

        } catch (\Exception $e) {
            return view("errors.formularios");
        }
    }
    public function obtieneDatosGenerales(){
        cambiarBase(Session::get('base'));
        $email = Session::get('empleado')['correo'];
        $empleado = EmpleadoLogin::where('email', $email)->first();
        $idempleado=DB::connection('empresa')->table('empleados')->select('id')->where('correo',$empleado->email)->first();
        $idempleado=$idempleado->id;
   
        $consultas=DB::connection('empresa')->table('detalle_formulario_encuesta as dfe')
                                            ->join('formulario_encuesta as fe','dfe.id_encuesta','=','fe.id')
                                            ->select('fe.id',
                                                    'fe.titulo',
                                                    'fe.descripcion',
                                                    'dfe.estatus')
                                            ->where('id_empleado',$idempleado)
                                            ->where('fe.estatus',1)->get();
        $data=[];
        foreach ($consultas as $key => $consulta) {
            
            $id_encuesta=$consulta->id;
            $consulta_preguntas=DB::connection('empresa')->table('formulario_preguntas')->where(['id_encuesta'=>$id_encuesta,'estatus'=>1])->count();
           
            $consulta_preguntas_contestadas= DB::connection('empresa')->table('formulario_respuestas')->where(['id_encuesta'=>$id_encuesta,'id_empleado'=>$idempleado])->groupBy('id_pregunta')->get();
            $consulta_preguntas_contestadas = sizeof($consulta_preguntas_contestadas);
            
            $clase="";                                             
            if($consulta_preguntas_contestadas > 0){
                
                $n_preguntas_contestadas=$consulta_preguntas_contestadas;
                $total_preguntas=$consulta_preguntas;

                $resultado = round(($n_preguntas_contestadas * 100) / $total_preguntas ,2 ); 
                if($resultado >= 90 ){ $clase ="text-success "; }else if($resultado< 90 && $resultado >= 60){ $clase ="text-warning "; }else{ $clase ="text-danger ";}
                $porcentaje=$resultado.' %';;
            
            }else{
                
                $clase ="text-danger ";
                $porcentaje=$consulta_preguntas_contestadas.' %';
            }
    
            if($consulta_preguntas === $consulta_preguntas_contestadas){

                DB::connection('empresa')->table('detalle_formulario_encuesta')
                                          ->where('id_encuesta',$id_encuesta)
                                          ->where('id_empleado',$idempleado)->update(['estatus'=>3]);
            }

            $mensaje='';
            $clase_span='';
            if($consulta->estatus===1 || $consulta->estatus===4 ){
                
                $mensaje='Activo';
                $clase_span='success';

            }else if($consulta->estatus===2){
                
                $mensaje='Inactivo';
                $clase_span='danger';

            }else{

                $mensaje='Cerrado';
                $clase_span='warning';

            }
            $data[]=array("0"=>$consulta->titulo,
                        "1"=>$consulta->descripcion,
                        "2"=>'<span class="badge badge-'.$clase_span.' pull-right">'.$mensaje.'</span>',
                        "3"=>'<span class="'.$clase.'font-weight-bold centrar-text">'.$porcentaje.'</span>',
                        "4"=>($consulta->estatus!==3  && $consulta->estatus!==2  )?'<div type="button" class="btn btn-warning btn-sm" onclick=visualizaEncuesta(\''.encrypt($consulta->id).'\',\''.encrypt($idempleado).'\'); ><span><i class="far fa-eye" ></i></span></div>':'');
        }              
        $results=array(
            "sEcho"=>1, //informacion para date tables
            "iTotalRecords"=>count($data),//Enviamos el total de registros en datatable
            "iTotalDisplayRecords"=>count($data), //Enviamos el total de registros a vizualisar
            "aaData"=>$data
        );
        return response()->json($results);
    }
    public function obtieneDatosCuestionario(Request $request){
        //tienePermisoA('empleados');
        cambiarBase(Session::get('base'));

        $idempleado=decrypt($request->idempleado);
        $id=decrypt($request->param); //id_encuesta
        $preguntas=DB::connection('empresa')->table('formulario_respuestas')
                                                                  ->where('id_encuesta',$id)
                                                                  ->where('id_empleado',$idempleado)->get();
        $array_preguntas=[];                                                           
        foreach($preguntas as $pregunta) $array_preguntas[]=$pregunta->id_pregunta;
        
        $consultas=DB::connection('empresa')->table('formulario_encuesta as fe')
                                            ->join('formulario_preguntas as fp','fp.id_encuesta','=','fe.id')
                                            ->join('formulario_tipo as ft','ft.id','=','fp.id_tipo')
                                            ->select('fe.id',
                                                    'fe.titulo',
                                                    'fe.descripcion',
                                                    'fe.fecha_vencimiento',
                                                    'fe.estatus',
                                                    'fp.id_encuesta',
                                                    'fp.id_tipo',
                                                    'fp.id AS idp',
                                                    'fp.pregunta',
                                                    'fp.valor AS val_preg',
                                                    'fp.lleva_icono',
                                                    'ft.tipo')
                                            ->where(['fe.id'=>$id,'fp.estatus'=>1])
                                            ->whereNotIn('fp.id',$array_preguntas)
                                            ->orderBy('fp.id','asc')
                                            ->get();
        if(\sizeof($consultas) >0){
            $datos_generales=array('titulo_encuesta'=>$consultas[0]->titulo,
                                    'descripcion_encuesta'=> $consultas[0]->descripcion);
        
            $preguntas=[];
            $datos_opc_preguntas=[];
            $det_iconos=[];        
            
            foreach ($consultas as $consulta){

                $aux='*#--#';
                $idp=$consulta->idp;
                $cadena=$consulta->pregunta.$aux.$idp.$aux.$consulta->id_tipo ;

                if($consulta->id_tipo!=1){

                    $preguntas[]=$cadena;
                    $datos_opc=[];
                    $opc_preguntas=DB::connection('empresa')->table('formulario_opc_preguntas as fop')->where('fop.id_pregunta',$idp)->get();

                    if($consulta->id_tipo===2 || $consulta->id_tipo===4  ){
                        
                        foreach ($opc_preguntas as $opc_pregunta){

                            $datos_opc[]= $opc_pregunta->titulo.$aux.$idp;
                        }
                        $datos_opc_preguntas[$idp]=$datos_opc;
                                
                    }else if($consulta->id_tipo===3){
            
                        foreach ($opc_preguntas as $opc_pregunta) {
                            $datos_opc[]= $opc_pregunta->titulo.$aux.$consulta->lleva_icono.$aux.$opc_pregunta->id;
                            $detalle_iconos=DB::connection('empresa')->table('detalle_iconos_formularios as dif')
                                                                ->join('iconos_configformulario as icf','icf.id','=','dif.idicono')                               
                                                                ->where('dif.id_opc_pregunta',$opc_pregunta->id)
                                                                ->get();

                            if(sizeof($detalle_iconos) > 0){

                                $iconos=[];
                                foreach ($detalle_iconos as  $detalle_icono) {
                                    $iconos[]= $detalle_icono->icono;
                                }
                                $det_iconos[$opc_pregunta->id]=$iconos;
                            } 
                        }
                        $datos_opc_preguntas[$idp]=$datos_opc;
                    }
                }else{
                    $preguntas[]= $cadena;   
                }
            }
            $data= array('datos_generales'=>$datos_generales,
                        'preguntas'=>$preguntas, 
                        'datos_opc_preguntas'=>$datos_opc_preguntas,
                        'det_iconos'=>$det_iconos);
                
            return response()->json($data);
        }
    }
    public function registarRespuestas(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        $email = Session::get('empleado')['correo'];
   
        $empleado = EmpleadoLogin::where('email', $email)->first();
        $idempleado=DB::connection('empresa')->table('empleados')->select('id')->where('correo',$empleado->email)->first();
        $idempleado=$idempleado->id;
        $id_encuesta=decrypt($request->id_en);

        foreach($request->param_pregunta as $key=> $param_pregunta ){
        
            if(array_key_exists($param_pregunta, $request->respuestas)){
                
                $respuestas = $request->respuestas[$param_pregunta];
                if(!empty($respuestas)){
                    foreach($respuestas as $respuesta){
                        $Formulario_respuestas=FormularioRespuesta::create(['id_pregunta'=>  $param_pregunta,'id_empleado'=>$idempleado,'respuestas'=>$respuesta, 'id_encuesta'=>$id_encuesta]);
                    }
                }
            }
        }

        $consulta_preguntas=DB::connection('empresa')->table('formulario_preguntas')->where('id_encuesta',$id_encuesta)->count();
        $consulta_preguntas_contestadas= FormularioRespuesta::where(['id_encuesta'=>$id_encuesta,'id_empleado'=>$idempleado])->groupBy('id_pregunta')->get();
        $consulta_preguntas_contestadas = sizeof($consulta_preguntas_contestadas);
        
        $cierra_encuesta="";
        ($consulta_preguntas===$consulta_preguntas_contestadas) ? $cierra_encuesta =1 : $cierra_encuesta =0;
        DetalleFormularioEncuesta::where(['id_empleado'=>$idempleado,'id_encuesta'=>$id_encuesta])->update(['estatus'=>4]);

        return response()->json(['id_encuesta' =>\encrypt($id_encuesta),'idempleado'=>\encrypt($idempleado),'cierra_encuesta'=>$cierra_encuesta ]);
    }
    
    public function obtenerInfoAviso()
    {
        $id = Session::get('empleado')['id'];
        $correo = Session::get('empleado')['correo'];
        $avisos=EmpleadoLogin::select('avisos')->where('email',$correo)->first();
        return response()->json($avisos);
    }
}