<?php

namespace App\Http\Controllers\formularios;


use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\FormularioEncuesta;
use App\Models\FormularioRespuesta;
use App\Models\ConfiguracionFormulario;
use App\Models\DetalleFormularioEncuesta;
use App\Models\IconoConfiguracionFormulario;
use App\Models\DetalleIconoFormulario;
use App\Models\FormularioOpcPregunta;
use App\Models\FormularioPreguntas;
use App\Models\FormularioTipo;
use App\Models\Formulario_respuesta;
use App\Models\Empleado;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncuestaCovidEmail;
use App\Exports\EncuestaCovidResultados;
use DateTime;

class FormularioController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function inicio()
    {
        cambiarBase(Session::get('base'));
        $consultas = FormularioEncuesta::orderBy('created_at','DESC')->get();
        
        $admin =Auth::user()->admin;
        //$tipo_usuario = Session::get('usuarioPermisos')['formularios'];
        $tipo_usuario = 0; /*Valor momentaneo este valor se tiene que cambiar por la line arriiba*/
        return view('formularios.encuesta.inicio',compact('consultas','admin','tipo_usuario'));
    }

    function obtenerDatosPorEncuesta(Request $request)
    {
        cambiarBase(Session::get('base'));
        
        $id = decrypt($request['id']);
        
        $permisos_mostrar_datos=FormularioEncuesta::select('estatus')->where('id',$id)->first();

        if($permisos_mostrar_datos->estatus!==3){
            
            $parametros=['fe.id'=>$id ,'fp.estatus'=>1];
            $datos = $this->obtieneDatosEncuesta($request,$id,$parametros);

            return view('formularios.encuesta.modificar-encuesta',compact('datos'));
        
        }else{
            
            return redirect()->route('formularios.encuestas');
        }
    }

    public function asignarEncuesta(Request $request)
    {
        $id = $request['id'];
        $id_encuesta = decrypt($id); 

        cambiarBase(Session::get('base'));

        $detalle_empleados=DetalleFormularioEncuesta::select('id_empleado')->where('id_encuesta',$id_encuesta)->get();

        $array_empleado=[];
        if(\sizeof($detalle_empleados ) > 0)

            foreach($detalle_empleados as $empleado) $array_empleado[]=$empleado->id_empleado;

        $empleados = DB::connection('empresa')->table('empleados as e')
            ->join('departamentos as d','d.id','=','e.id_departamento')
            ->select('e.id', 'e.nombre', 'e.apaterno', 'e.amaterno', 'd.nombre as departemento')
            //->where('e.estatus','<>',1)
            ->whereNotIn('e.id',$array_empleado)
            ->orderBy('e.nombre', 'asc')
            ->get();

        $empleados_asignados = DB::connection('empresa')->table('detalle_formulario_encuesta as dt')
            ->join('empleados as e','e.id','=','dt.id_empleado')
            ->join('departamentos as d','d.id','=','e.id_departamento')
            ->select('e.id', 'e.nombre', 'e.apaterno', 'e.amaterno', 'd.nombre as departemento','dt.estatus')
            ->where('dt.id_encuesta',$id_encuesta)
            ->orderBy('e.nombre', 'asc')
            ->get();

        return view('formularios.encuesta.asignar-encuesta',compact('empleados','id','empleados_asignados'));
    }

    public function agregarEditarEncuesta(Request $request)
    {
        $redireccion_ruta="";
        try{

            cambiarBase(Session::get('base'));
            $id_encuesta = $request->id_encuesta;

            ($request->valchecked==0) ? $fecha_vencimiento=null : $fecha_vencimiento=new \DateTime($request->fecha_vencimiento);
         
            $data=array('titulo' => $request->titulo, 'descripcion' => $request->descripcion, 'fecha_vencimiento'=>$fecha_vencimiento, 'estatus'=>$request->estatus);

            if(empty($id_encuesta)){
   
                $formulario_encuesta = FormularioEncuesta::create($data);
                $idencuesta = $formulario_encuesta->id;
                    
                foreach($request->tipo_pregunta as $key =>$tipo ){
                    
                    $pregunta=$request->pregunta[$key];
                    $valorPregunta=$request->valor_preg[$key];
                    
                    ($request->icono[$key]==='1')? $icono = $request->icono[$key]:$icono='';

                    $data_preguntas=array("id_encuesta"=>$idencuesta,
                        "pregunta"=>$pregunta,            
                        "id_tipo"=>$tipo,
                        "valor"=>$valorPregunta,
                        'lleva_icono'=>$icono,
                        "estatus"=>1); // Momentaniamente es  1 como prueba

                    $formulario_preguntas = FormularioPreguntas::create($data_preguntas);
                    $id_pregunta = $formulario_preguntas->id;

                    if($tipo!=="1"){
                        
                        $concat=$request->naleatorio1[$key].'_'.$tipo.'_'.$request->naleatorio2[$key];
                        
                        foreach($request->{"titulos_items_$concat"} as $key1 =>$titulo ){
                            
                            $valor=$request->{"valores_items_$concat"};
                            $data_opc=array('id_pregunta'=>$id_pregunta,'titulo'=>$titulo,'valor'=>$valor[$key1]);
                            
                            $formulario_opc_pregunta=FormularioOpcPregunta::create($data_opc);
                            $id_opc_pregunta=$formulario_opc_pregunta->id;
                            
                            if($icono==="1"){
                            
                                $idicono=$request->{"icons_$concat"};
                                $data_iconos=array('id_opc_pregunta'=>$id_opc_pregunta,'idicono'=>$idicono[$key1]);
                                
                                DetalleIconoFormulario::create($data_iconos);
                            }
                        }
                    }
                }
                
                $redireccion_ruta='formularios.encuestas';

            }else{
                
              
                $id_encuesta=\decrypt($request->id_encuesta);
                FormularioEncuesta::where('id',$id_encuesta)->update($data);

                foreach($request->tipo_pregunta as $key =>$tipo ){

                    $id_pregunta=\decrypt($request->id_preg[$key]);

                    FormularioPreguntas::where(['id'=>$id_pregunta,'id_encuesta'=>$id_encuesta])->update(['pregunta'=>$request->pregunta[$key],'valor'=>$request->valor_preg[$key]]);
                    
                    if($tipo!=="1"){
    
                        $concat=$request->naleatorio1[$key].'_'.$tipo.'_'.$request->naleatorio2[$key];
                        foreach($request->{"titulos_items_$concat"} as $key1 =>$titulo ){
                            
                            $id_opc_preg=$request->{"id_items_$concat"};
                            $id_opc_preg=$id_opc_preg[$key1];
                            $valor=$request->{"valores_items_$concat"};
                            $data_opc=array('titulo'=>$titulo,'valor'=>$valor[$key1]);

                            $formulario_opc_pregunta=FormularioOpcPregunta::where(['id'=>\decrypt($id_opc_preg),'id_pregunta'=>$id_pregunta])->update($data_opc);
                            
                        }
                    }                   
                }
                $redireccion_ruta="formularios.inicio";
            }

            session()->flash('success', 'Los datos se guardaron correctamente');

        }catch(\Exception $e) {
            //dd($e);
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }
        //return redirect()->route($redireccion_ruta,$request->id_encuesta);
        return redirect()->route('formularios.inicio');
    }

    public function agregaEmpleado(Request $request)
    {
        try{
            cambiarBase(Session::get('base'));

            $id = $request->id_encuesta;
            $id_encuesta=decrypt($request->id_encuesta);

            if(!empty($request->empleados))

                foreach($request->empleados as $empleado)  DetalleFormularioEncuesta::create(['id_empleado'=>decrypt ($empleado), 'id_encuesta'=>$id_encuesta]);

            session()->flash('success', 'Los datos se guardaron correctamente..!!');

        } catch (\Exception $e){
            session()->flash('danger', 'Los datos no se pudieron procesar favor de contactar a su administrador..!!');
        }
        return redirect()->route('formularios.asignarEncuesta',['id'=>$id]);
    }

    public function desasignarFormulario(Request $request)
    {
        $id=$request->id_encuesta;
        $id_encuesta=decrypt($request->id_encuesta);
        $id_empleado=decrypt($request->id_empleado);
        cambiarBase(Session::get('base'));
        $consulta=DetalleFormularioEncuesta::where(['id_empleado' => $id_empleado,'id_encuesta' => $id_encuesta,'estatus' => 4])->first();

        if (!empty($consulta)) {
            session()->flash('danger', 'El empleado no puede ser desasignado porque ya está contestando dicha encuesta..!!');
        } else {
            $consulta = DetalleFormularioEncuesta::where(['id_empleado' => $id_empleado,'id_encuesta' => $id_encuesta])->delete();
            session()->flash('success', 'El empleado fue desasignado correctamente..!!');
        }
        return redirect()->route('formularios.asignarEncuesta',['id'=>$id]);
    }

    public function obtenerEmpleadosAsignados(Request $request)
    {
        $id =$request['id'];
        $id_encuesta=decrypt($id);
        cambiarBase(Session::get('base'));
        $empleados_asignados=DB::connection('empresa')->table('detalle_formulario_encuesta as dt')
           ->join('empleados as e','e.id','=','dt.id_empleado')
           ->join('departamentos as d','d.id','=','e.id_departamento')
           ->join('formulario_encuesta as fe','fe.id','=','dt.id_encuesta' )
           ->select('e.id', 'e.nombre', 'e.apaterno', 'e.amaterno', 'd.nombre as departemento','dt.id_empleado','dt.id_encuesta','dt.estatus','dt.fecha_finalizacion', 'e.fecha_nacimiento','e.correo')
           ->where('dt.id_encuesta',$id_encuesta)
           ->orderBy('e.nombre', 'asc')
           ->get();

        return view('formularios.encuesta.resultados-encuesta',compact('empleados_asignados','id'));
    }

    public function obtenerResultadosEmpleado(Request $request)
    {
        $datos=$request->all();
        $id_empleado=\decrypt($request['idemplado']);
        $idencuesta=\decrypt($request['idencuesta']);
        $consultas = $this->resultadosRespuestas($id_empleado,$idencuesta);

        return view('formularios.encuesta.visualizar-resultados',compact('consultas','datos'));
    }

    public function resultadosRespuestas($id_empleado,$idencuesta)
    {
        cambiarBase(Session::get('base'));

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
            ->orderBy('fp.id','asc')
            ->where(['fe.id'=>$idencuesta,'fp.estatus'=>1])->get();

        $datos_generales=array('id'=>$consultas[0]->id,
           'titulo'=>$consultas[0]->titulo,
           'descripcion'=> $consultas[0]->descripcion,
           'fecha_vencimiento'=> ($consultas[0]->fecha_vencimiento < 1 )? 'No aplica':   Carbon::parse($consultas[0]->fecha_vencimiento)->format('d/m/Y'),
           'estatus'=>$consultas[0]->estatus);

        $array_respuestas=[];
        $array_preguntas=[];
        $aux='*#--#';
        $array_respuestas=[];
        foreach($consultas as $consulta){

            $idpregunta=$consulta->idp;
            $pregunta=$consulta->pregunta;
            $array_preguntas[]=$idpregunta.$aux.$pregunta;
            $obtiene_respuestas=FormularioRespuesta::where('id_pregunta',$idpregunta)->where('id_empleado',$id_empleado)->get();
            $respuestas="";
            
            if(sizeof($obtiene_respuestas) > 0){

                //foreach($obtiene_preguntas  as $obtiene_pregunta) $respuestas=$obtiene_pregunta->respuestas;
                foreach($obtiene_respuestas  as $obtiene_respuesta){
                    //if($obtiene_pregunta->id_respuesta == $idpregunta){
                        $array_respuestas[$idpregunta][]=$obtiene_respuesta->respuestas;
                    //}
                } 
                
            }else{
                $array_respuestas[$idpregunta][]='No contestada';
            }
           
        }
       
        return $data=array('array_preguntas'=>$array_preguntas,'array_respuestas'=>$array_respuestas,'datos_generales'=>$datos_generales);
    }

    public function visualizarEncuesta(Request $request)
    {
        cambiarBase(Session::get('base'));
        $id = decrypt($request['id']);
        
        $permisos_mostrar_datos=FormularioEncuesta::select('estatus')->where('id',$id)->first();

        $parametros="";

        ($permisos_mostrar_datos->estatus==3) ? $parametros=['fe.id'=>$id]: $parametros=['fe.id'=>$id ,'fp.estatus'=>1];

        $data=$this->obtieneDatosEncuesta($request,$id,$parametros);

        return view('formularios.encuesta.visualizar-encuesta',compact('data'));
    }

    public function obtieneDatosEncuesta($request,$id,$parametros){

        cambiarBase(Session::get('base'));

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
                'ft.tipo',
                'fp.estatus')
            ->where($parametros)
            ->orderBy('fp.id','asc')
            ->get();

        $datos_generales=array('id'=>$consultas[0]->id,
            'titulo'=>$consultas[0]->titulo,
            'descripcion'=> $consultas[0]->descripcion,
            'fecha_vencimiento'=> ($consultas[0]->fecha_vencimiento < 1 )? 'No aplica':   Carbon::parse($consultas[0]->fecha_vencimiento)->format('d/m/Y'),
            'estatus'=>$consultas[0]->estatus);
        
        $preguntas=[];
        $datos_opc_preguntas=[];
        $det_iconos=[];

        foreach ($consultas as $consulta){
            
            $aux='*#--#';
            $idp=$consulta->idp;
            $id_tipo=$consulta->id_tipo;
            $cadena=$idp.$aux. //idpregunta
            $consulta->pregunta. //nombre pregunta
            $aux.$consulta->val_preg. // valor pregunta
            $aux.$id_tipo. // tipo pregunta
            $aux.$consulta->lleva_icono;

            if($consulta->id_tipo!=1){
                $preguntas[]=$cadena;
                $datos_opc=[];
                $opc_preguntas=FormularioOpcPregunta::where('id_pregunta',$idp)->get();

                if ($id_tipo===2 || $id_tipo===4 ) {
                    foreach ($opc_preguntas as $opc_pregunta) {
                        $datos_opc[]=$opc_pregunta->id.$aux. //id opc pregunta
                        $opc_pregunta->titulo. // titulo opc pregunta
                        $aux.$opc_pregunta->valor. // valor opc pregunta
                        $aux.$idp; // id a la pregunta que corresponde
                    }

                    $datos_opc_preguntas[$idp]=$datos_opc;

                } else if ($id_tipo===3) {
                    foreach ($opc_preguntas as $opc_pregunta){
                        $datos_opc[]=$opc_pregunta->id.$aux.//id opc pregunta
                        $opc_pregunta->titulo.// titulo opc pregunta
                        $aux.$opc_pregunta->valor.// valor opc pregunta
                        $aux.$consulta->lleva_icono.//lleva iconos los parametros de la pregunta
                        $aux.$idp;// id a la pregunta que corresponde

                        $detalle_iconos=DB::connection('empresa')->table('detalle_iconos_formularios as dif')
                            ->join('iconos_configformulario as icf','icf.id','=','dif.idicono')                               
                            ->where('dif.id_opc_pregunta',$opc_pregunta->id)
                            ->get();

                        if(sizeof($detalle_iconos) > 0){
                            $iconos=[];
                            foreach ($detalle_iconos as  $detalle_icono) $iconos[]= $detalle_icono->idicono.$aux.$detalle_icono->icono;
                            $det_iconos[$opc_pregunta->id]=$iconos;
                        }
                    }
                    $datos_opc_preguntas[$idp]=$datos_opc;
                }
            } else {
                $preguntas[]= $cadena;
            }
        }

        return $data= array('datos_generales'=>$datos_generales,
            'preguntas'=>$preguntas, 
            'datos_opc_preguntas'=>$datos_opc_preguntas,
            'det_iconos'=>$det_iconos);
    }

    public function pdfEncuesta(Request $request)
    {
        $id_empleado=\decrypt($request->idempleado);
        $idencuesta=\decrypt($request->id);

        $nomempleado=$request->nomempleado;
        $depart=$request->depart;
        $fecha_finalizacion=$request->fecha_finalizacion;
        $correo = $request->correo;
        $edad = $this->obtenerDiffYears($request->fecha_nacimiento);
        $data = $this->resultadosRespuestas($id_empleado,$idencuesta);

        $pdf = \PDF::loadView('formularios.pdfs.pdf-encuesta',compact('data','nomempleado','depart','fecha_finalizacion','correo','edad'));
        $nombre_pdf=$nomempleado.'_encusta_'.Carbon::now()->format('Y_m_d').'.pdf';

        return $pdf->download($nombre_pdf);
    }

    public function cerrarEncuesta(Request $request)
    {
        try{
            $id_empleado=\decrypt($request->id_empleado);
            $id_encuesta=\decrypt($request->id_encuesta);

            cambiarBase(Session::get('base'));
            $consulta = DetalleFormularioEncuesta::where(['id_empleado' => $id_empleado,'id_encuesta' => $id_encuesta])->update(['estatus' => 3,'fecha_finalizacion'=>Carbon::now()->format('Y-m-d')]);

            session()->flash('success', 'Los datos se guardaron correctamente');

        } catch (\Exception $e) {

            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }

        return redirect()->route('formularios.obtenerEmpleadosAsignados',['id' => $request->id_encuesta]);
    }

    public function agregar(Request $request)
    {
        cambiarBase(Session::get('base'));
        $data=FormularioTipo::all();
        return view('formularios.encuesta.agregar-encuesta',compact('data'));
    }

    public function obtieneIconos(Request $request)
    {
        cambiarBase(Session::get('base'));
        
        $query=ConfiguracionFormulario::where('estatus',1)->orderBy('id','desc')->get();
        $almacena_opc=[];

        foreach ($query as $consulta) {
            $options="<option value=".$consulta->id.">".$consulta->titulo."</option>";
            $almacena_opc[] = preg_replace("/[\r\n|\n|\r]+/", " ", $options);
        }

        $almacena_opc=implode("|",$almacena_opc);

        return response()->json($almacena_opc);
    }

    public function obtieneDetallesIconos(Request $request)
    {
        cambiarBase(Session::get('base'));
        $consultas=IconoConfiguracionFormulario::where('idconfigform','=',$request->param)->get();

        return response()->json($consultas);
    }

    public function eliminarPregunta(Request $request)
    {
        try{

            $idencuesta= decrypt($request->idencuesta);
            $idpregunta= decrypt($request->id);
            
            cambiarBase(Session::get('base'));

            FormularioPreguntas::where(['id'=>$idpregunta,'id_encuesta'=>$idencuesta])->update(['estatus'=>0]);
            $contador = FormularioPreguntas::where(['id_encuesta'=>$idencuesta,'estatus'=>1])->count();
            if($contador==0){

                FormularioEncuesta::where('id', $idencuesta)->update(["estatus"=>3]);
                $estado=true;
            
            }else{
             
                $estado=false;
            }
            $respuesta=true;

        }catch(\Exception $e) {
        
            $respuesta=false;
        }
        return response()->json(['respuesta'=>$respuesta,'estado'=>$estado]);
    }

    public function cambiaStatusEncuesta(Request $request){
        
        try{
            
            cambiarBase(Session::get('base')); 
            FormularioEncuesta::where('id',\decrypt($request->idencuesta))->update(['estatus'=>$request->estatus]);
            $respuesta=true;
            
        }catch(\Exception $e) {
            
            //dd($e);
            $respuesta=false;
        }
        return response()->json(['respuesta' => $respuesta]);
    }

    public function agregarEncuestaCovid()
    {
        try{
         
            cambiarBase('empresa000046');
            $date = Carbon::now()->locale('es');

            $titulo ='ENCUESTA DE VIGILANCIA COVID-19, '.$date->isoFormat('LLLL');
            $descripcion = "Buenos días¡ Muchas gracias por su cumplimiento y responsabilidad ante las medidas de bioseguridad. Es OBLIGATORIO contestar esta encuesta 30 minutos antes de llegar a las oficinas e ingresar. Muchas Gracias y que tengas excelente día.";
            $fecha_vencimiento =  Carbon::parse(date('Y-m-d'))->format('d/m/Y');

            $data=array('titulo' =>$titulo,'descripcion'=>$descripcion,'fecha_vencimiento'=>$fecha_vencimiento,'estatus'=> 1);
            $formulario_encuesta = FormularioEncuesta::create($data);
            $idencuesta = $formulario_encuesta->id;
            $preguntas = [
                ['id_encuesta'=>$idencuesta, 'id_tipo'=> 2,'pregunta'=>"Modalidad de trabajo del día de hoy o recurrente",'valor'=>0,'estatus'=>1 ],
                ['id_encuesta'=>$idencuesta, 'id_tipo'=> 4,'pregunta'=>"¿Has tenido en los últimos 5 días cualquiera de estos síntomas? Señala uno o varios según corresponda.",'valor'=>0,'estatus'=>1 ],
                ['id_encuesta'=>$idencuesta, 'id_tipo'=> 4,'pregunta'=>"¿Cuántas dosis te han puesto?",'valor'=>0,'estatus'=>1 ],
                ['id_encuesta'=>$idencuesta, 'id_tipo'=> 2,'pregunta'=>"¿Consideras que tu o tu familia requiere apoyo psicológico debido a la pandemia?",'valor'=>0,'estatus'=>1 ]
            
            ];
            FormularioPreguntas::insert($preguntas);
            $preg = FormularioPreguntas::where('id_encuesta',$idencuesta)->orderBy('id','asc')->get();
            
            $contador = 0;
            $contador1 = 0;
            foreach($preguntas as $key=> $pregunta){
                
                $id_pregunta = $preg[$key]->id;

                switch ($pregunta['id_tipo']) {
                    case 4:

                        $preguntas_opc =array(['id_pregunta'=>$id_pregunta, 'titulo'=>'Tos seca los últimos 5 días','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Fiebre de mas de 37.5 los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dolor de Cuerpo los últimos 5 días','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Falta de olfato los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Perdida del gusto los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dolor de cabeza los últimos 5 días','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dificultad para respirar los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Contacto directo con personas a Covid-19 que sean cohabitantes los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Prueba positiva para COVID-19 los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Evacuaciones liquidas (mas de 3 al día) los últimos 5 días.','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Ningún síntoma.','valor'=>'0']);

                        if($contador == 1){

                            $preguntas_opc =array(['id_pregunta'=>$id_pregunta, 'titulo'=>'Una','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dos','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dosis única','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'No me he aplicado vacuna','valor'=>'0'],
                            ['id_pregunta'=>$id_pregunta ,'titulo'=>'Refuerzo','valor'=>'0']);
                        }

                        FormularioOpcPregunta::insert($preguntas_opc);
                        $contador++;

                    break;

                    /*case 3:

                        $preguntas_opc =array(['id_pregunta'=>$id_pregunta, 'titulo'=>'Una'],
                        ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dos'],
                        ['id_pregunta'=>$id_pregunta ,'titulo'=>'Dosis única'],
                        ['id_pregunta'=>$id_pregunta ,'titulo'=>'No me he aplicado vacuna']);

                        FormularioOpcPregunta::insert($preguntas_opc);

                    break;*/
                
                    default:

                        $preguntas_opc =array(['id_pregunta'=>$id_pregunta, 'titulo'=>'Si','valor'=>'0'],['id_pregunta'=>$id_pregunta ,'titulo'=>'No','valor'=>'0']);
                        if($contador1 == 0){

                            $preguntas_opc =array(['id_pregunta'=>$id_pregunta, 'titulo'=>'Home Office','valor'=>'0'],
                                                  ['id_pregunta'=>$id_pregunta ,'titulo'=>'Visita Cliente','valor'=>'0'],
                                                  ['id_pregunta'=>$id_pregunta ,'titulo'=>'Oficina','valor'=>'0']);

                        }                        
                        FormularioOpcPregunta::insert($preguntas_opc);
                        $contador1++;

                    break;
                }      
            }
        
            $mensaje = $idencuesta;
        
        }catch(\Exception $e){
            
            $e->getMessage();
            dd($e);
        }
        return $mensaje;
    }

    public function exportarExcelResultados(Request $request)
    {
      
        cambiarBase(Session::get('base'));
        
        if(!empty($request->idempleado)){

            $id_empleado=\decrypt($request->idempleado);
            $id_encuesta=\decrypt($request->id);
            $estatus ="";
        
        }else{

            $id = $request->id;
            $estatus =$request['estatus'];
            $id_encuesta = \decrypt($id);
            $id_empleado="";
        }        
    
        try{
            return Excel::download( new EncuestaCovidResultados($id_encuesta,$estatus,$id_empleado), 'Resultados_encuesta_covid_contestados'.date('d-m-Y_H:i').'.xlsx' );
            
        }catch(\Exception $e){
            //dd($e);
            //session()->flash('danger', 'Error al realizar la petición comunicate con tu administrador...!!');
            //return redirect()->route('norma.normaTabla');
        }  
    }
    
    public function exportarEncuesta(Request $request)
    {
        if($request->aux=="2")
            return $this->exportarExcelResultados($request);
        else
            return $this->pdfEncuesta($request);
    }

    public function obtenerDiffYears($fecha)
    {
        $actual = Carbon::now();
        $actual = $actual->format('Y-m-d');

        $fecha_inicio =Carbon::parse($fecha);
        $fecha_inicio = $fecha_inicio->format('Y-m-d');
        
        $fecha_actual = DateTime::createFromFormat('Y-m-d', $actual);
        $fecha_inicio = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
        $diferencia_fecha = $fecha_inicio->diff($fecha_actual);

        return $diferencia_fecha->format('%Y').' Años';
    }

}
