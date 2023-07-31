<?php 

namespace App\Http\Controllers\Juridico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Juridico\DemandasController;
use App\Models\Demanda;
use App\Models\Audiencia;
use App\Models\EvidenciaJuridico;

use DataTables;
use DateTime;


class AudienciaController extends Controller
{
    protected const AUDIENCIA_JUDICIAL = 0;
    protected const AUDIENCIA_PRE_JUDICIAL = 1;
    protected const AUDIENCIA_CONSTITUCIONAL = 2;

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function iniciodemanda(Request $request)
    {
        cambiarBase(Session::get('base'));
        if ($request->ajax()) {
            $audiencias = Audiencia::where('id_demanda',$request->demanda)->with('evidencias');

            return Datatables::of($audiencias->get())
            ->addColumn('operaciones',function($row){
                if($row->pre == self::AUDIENCIA_JUDICIAL){   
                    return '<a data-audiencia="'.$row->id.'" data-tipo="'.$row->pre.'" class=" mr-2 tooltip_" title="Editar Audiencia"  data-toggle="modal" data-target="#audienciaModal"> <img src="/img/icono-editar.png" class="button-style-icon"></a>'; 
                }else if($row->pre == self::AUDIENCIA_PRE_JUDICIAL){
                    return '<a data-audiencia="'.$row->id.'" data-tipo="'.$row->pre.'" class=" mr-2 tooltip_" title="Editar Audiencia"  data-toggle="modal" data-target="#audienciaModal"> <img src="/img/icono-editar.png" class="button-style-icon"></a>'; 
                }else if($row->pre == self::AUDIENCIA_CONSTITUCIONAL){
                    return '<a data-audiencia="'.$row->id.'" data-tipo="'.$row->pre.'" class=" mr-2 tooltip_" title="Editar Audiencia"  data-toggle="modal" data-target="#audienciaModal"> <img src="/img/icono-editar.png" class="button-style-icon"></a>';
                }else{
                    return "SIN";
                }
                
            })
            ->editColumn('tipo_audiencia',function($row){
                if($row->tipo_audiencia == 1){
                    return "Conciliación";
                }else if($row->tipo_audiencia == 2){
                    return "Contestación de Demanda y Exepciones";
                }else if($row->tipo_audiencia == 3){
                    return "Ofrecimiento de Pruebas";
                }else if($row->tipo_audiencia == 4){
                    return "Desahogo de Pruebas";
                }else if($row->tipo_audiencia == 5){
                    return "Reinstalación";
                }else if($row->tipo_audiencia == 6){
                    return "Alegatos";
                }else if($row->tipo_audiencia == 7){
                    return "Laudo";
                }else{
                    return $row->tipo_audiencia;
                }
            })
            ->editColumn('tipo_prueba',function($row){
                if($row->tipo_prueba == 1){
                    return "Documental Privado";
                }else if($row->tipo_prueba == 2){
                    return "Documental Publico";
                }else if($row->tipo_prueba == 3){
                    return "Confesional hechos propios";
                }else if($row->tipo_prueba == 4){
                    return "Confesional Representado";
                }else if($row->tipo_prueba == 5){
                    return "Testimonial";
                }else if($row->tipo_prueba == 6){
                    return "Inspeccion Ocular";
                }else if($row->tipo_prueba == 7){
                    return "Prueba pericial";
                }else if($row->tipo_prueba == 8){
                    return "Instrumental Actuaciones";
                }else if($row->tipo_prueba == 9){
                    return "Presuncional Humana";
                }else{
                    return "";
                }
            })
            ->editColumn('fecha_aviso', function($row){
                if(!empty($row->fecha_aviso)){
                    return Carbon::parse($row->fecha_aviso)->format('d/M/y');
                }else{
                    return "-";
                }
            })
            ->editColumn('fecha_audiencia', function($row){
                if(!empty($row->fecha_audiencia)){
                    return Carbon::parse($row->fecha_audiencia)->format('d/M/y');
                }else{
                    return "-";
                }
            })
            ->editColumn('fecha_proxima', function($row){
                if(!empty($row->fecha_proxima)){
                    return Carbon::parse($row->fecha_proxima)->format('d/M/y');
                }else{
                    return "-";
                }
            })
            ->editColumn('fecha_sentencia', function($row){
                if(!empty($row->fecha_sentencia)){
                    return Carbon::parse($row->fecha_sentencia)->format('d/M/y');
                }else{
                    return "-";
                }
            })
            ->editColumn('pre',function($row){
                if($row->pre == self::AUDIENCIA_JUDICIAL){
                    return "AUDIENCIA JUDICIAL";
                }else if($row->pre == self::AUDIENCIA_PRE_JUDICIAL){
                    return "AUDIENCIA PRE JUDICIAL";
                }else if($row->pre == self::AUDIENCIA_CONSTITUCIONAL){
                    return "AUDIENCIA CONSTITUCIONAL";
                }else{
                    return "SIN CATEGORIA";
                }
            })
            ->addColumn('evidencias',function($row){
                $img = $pdf = 0;
                $url_repositorio = asset('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$row->id_demanda.'/'.$row->id.'/');
                $url_carpeta = public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$row->id_demanda.'/'.$row->id.'/');

                $imgs = '<div class="row">';
                $pdfs = '<div style="width:100%;padding-top:30px;">
                            <table class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead><tbody>';
                foreach($row->evidencias as $m){

                    if($m->tipo != 'pdf' && $m->tipo != "PDF"){
                        $imgs .='<div class="col-sm-2" id="evi'.$m->id.'"><a href="'.$url_repositorio.'/'.$m->nombre.'"  data-toggle="lightbox" data-title="Evidencia" data-gallery="gallery" >
                                    <img src="'.$url_repositorio.'/'.$m->nombre.'" class="img-fluid"/>
                                </a>

                                <a class="eliminar-evidencia" 
                                data-evidencia="'.$m->id.'"
                                data-url="'.$url_carpeta."/".$m->nombre.'"
                                data-masiva="'.$m->masiva.'"
                                 style="width:100%;border-radius:0rem 0rem 0.25rem 0.25rem"> 
                                 <img src="/img/eliminar.png" class="button-style-icon">
                                </a></div>';
                        $img++;
                    }else{
                        $pdfs .= '<tr id="evi'.$m->id.'">
                                    <td>'.$m->nombre.'</td>
                                    <td>
                                        <a class="eliminar-evidencia" 
                                            data-evidencia="'.$m->id.'"
                                            data-url="'.$url_carpeta."/".$m->nombre.'"
                                            data-masiva="'.$m->masiva.'"
                                            style="width:100%;border-radius:0rem 0rem 0.25rem 0.25rem"> 
                                            <img src="/img/eliminar.png" class="button-style-icon">
                                        </a>

                                        <a href="'.$url_repositorio.'/'.$m->nombre.'" class="" target="blank_">
                                            <img src="/img/ver-documentos-empleado.png" class="button-style-icon">  
                                        </a>
                                    </td>
                                </tr>';
                        $pdf++;
                    }
                    
                    //print_r($m);
                }
                $pdfs .= '</tbody></table></div>';
                $imgs .= '</div>';
                $res = "";
                if($pdf == 0 && $img == 0){
                    $res .=  '<p class="text-center" style="width:100%;"><i>No hay evidencia</i></p>';
                }else {
                    if($img > 0){
                        $res .= $imgs;
                    }
                    if($pdf > 0){
                        $res .= $pdfs;
                    }
                }
                    
                return $res;
            })
            ->rawColumns(['evidencias','operaciones','tipo_audiencia','tipo_prueba'])
            ->make(true);

        }

        $demanda = Demanda::where('id',$request->demanda_audiencia)->with('empleado')->get();
        $demanda = $demanda->first();
        
        $empleado = $demanda->empleado;
        $IndmConst=  $empleado->salario_diario * $demanda->indemnizacion_constitucional;
        //dd($empleado);
        $demandaController = new DemandasController;
        $totales = $demandaController->calcularTotal($demanda,$empleado->salario_diario);


        return view('juridico.audiencia-inicio',compact('demanda','empleado','IndmConst','totales'));
    }


     public function verPre(Request $request)
     {
        cambiarBase(Session::get('base'));
        $audiencia = Audiencia::with("involucrados")->find($request->id);
        $demanda = Demanda::find($audiencia->id_demanda); //obtener informacion de la demanda
        $rutimg = asset('storage/');
        return response()->json(['ok' => 1,'rimg' => $rutimg, 'audiencia' => $audiencia,'demanda' => $demanda]);
    }

    public function editarPre(Request $request)
    {
        cambiarBase(Session::get('base'));
        $demanda = Demanda::where('id',$request->idDemanda)->with(['audiencias' => function ($query) use ($request){
            $query->where('id',"=", $request->idAudiencia);
        }])->first();

        $fecha_aviso = (!empty($request->FechaAviso))? new DateTime($request->FechaAviso) : null;
        $fecha_audiencia = (!empty($request->FechaAudiencia))? new DateTime($request->FechaAudiencia) : null;
        $fecha_proxima_audiencia = (!empty($request->FechaProxima))? new DateTime($request->FechaProxima) : null;
        $estatusFinal='FINALIZADO';
        $Arreglo = (!empty($request->arreglo_conciliatorio))? 1 : 0;

        if($Arreglo == 0 && $demanda->audiencias[0]->Concluido == 1){
            
                $demanda->est_importe = 0;
                $demanda->est_prestaciones = 0;
                $demanda->est_indm_cons = 0;
                $demanda->est_indm_anio = 0;
                $demanda->est_salario_caido = 0;
                $demanda->importe_extra = 0;
                $demanda->estatus = 1;
                $demanda->motivo_arreglo_conciliacion = "";
                $demanda->fecha_proxima_audiencia = (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null;
            
        }
        if($Arreglo){
            $estatusFinal='CONCILIADO';
            
                $demanda->est_importe = isset($request->EstImporte) ? 1 : 0;
                $demanda->est_prestaciones = isset($request->EstPrestaciones) ? 1 : 0;
                $demanda->est_indm_cons = isset($request->EstIndmCon) ? 1 : 0;
                $demanda->est_indm_anio = isset($request->EstIndmAno) ? 1 : 0;
                $demanda->est_salario_caido = isset($request->EstSalarioCaido) ? 1 : 0;
                $demanda->importe_extra = isset($request->ImporteExtra) ? $request->ImporteExtra : 0;
                $demanda->estatus = 3;
                $demanda->motivo_arreglo_conciliacion = isset($request->MotivoArregloConci) ? $request->MotivoArregloConci : NULL;
                $demanda->fecha_proxima_audiencia = (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null;
            
        }
   
        $demanda->audiencias[0]->expediente = $request->expediente;
        $demanda->audiencias[0]->ciudad = $request->ciudad;
        $demanda->audiencias[0]->fecha_audiencia = $fecha_audiencia->format('Y-m-d').' 00:00:00';
        $demanda->audiencias[0]->hora_audiencia = (!empty($request->HoraAudiencia))? $request->HoraAudiencia : null;
        $demanda->audiencias[0]->fecha_aviso = $fecha_aviso->format('Y-m-d').' 00:00:00';
        $demanda->audiencias[0]->arreglo_conciliatorio = (!empty($request->ArregloConci))? $request->ArregloConci : null; //textarea descipcion
        $demanda->audiencias[0]->fecha_proxima = (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null;
        $demanda->audiencias[0]->costo_estimado_honorarios = (!empty($request->honorarios))? $request->honorarios : 0;
        $demanda->audiencias[0]->conciliacion = (!empty($request->ArregloConci))? $request->ArregloConci : null;
        $demanda->audiencias[0]->estatus_final = $estatusFinal;
        $demanda->audiencias[0]->concluido = $Arreglo;
        $demanda->audiencias[0]->concluido_motivo = (!empty($request->MotivoArregloConci))? $request->MotivoArregloConci:"";
        
        if($demanda->audiencias[0]->save()){
            if($demanda->save()){
                $this->cargarEvidencia($request);
                return response()->json(['ok' => 1,'msg' => 'La audiencia se actualizó con éxito ']);
            }
        }
        return response()->json(['ok' => 0,'msg' => 'La audiencia no pudo acualizarse con éxito ']);
    }


    public function agregarPre(Request $request)
    {
        cambiarBase(Session::get('base'));
        $fecha_aviso = (!empty($request->FechaAviso))? new DateTime($request->FechaAviso) : null;
        $fecha_audiencia = (!empty($request->FechaAudiencia))? new DateTime($request->FechaAudiencia) : null;
        $fecha_proxima_audiencia = (!empty($request->FechaProxima))? new DateTime($request->FechaProxima) : null;
        $estatusFinal='FINALIZADO';
        $Arreglo = (!empty($request->arreglo_conciliatorio))? 1 : 0;
        if($Arreglo){
            $estatusFinal='CONCILIADO';
            $d = Demanda::find($request->idDemanda)->update([
                'est_importe' => isset($request->EstImporte) ? 1 : 0,
                'est_prestaciones_d' => isset($request->EstPrestaciones) ? 1 : 0,
                'est_indm_cons' => isset($request->EstIndmCon) ? 1 : 0,
                'est_indm_anio' => isset($request->EstIndmAno) ? 1 : 0,
                'est_salario_caido' => isset($request->EstSalarioCaido) ? 1 : 0,
                'importe_extra' => isset($request->ImporteExtra) ? $request->ImporteExtra : 0,
                'estatus' => 3,
                'motivo_arreglo_conciliacion' => isset($request->MotivoArregloConci) ? $request->MotivoArregloConci : NULL,
                'fecha_proxima_audiencia' => (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null,
            ]);
        }

        $audiencia = Audiencia::insertGetId([
            'id_demanda' => $request->idDemanda,
            'pre' => '1',
            'expediente' => $request->expediente,
            'ciudad' => $request->ciudad,
            'fecha_audiencia' => $fecha_audiencia->format('Y-m-d').' 00:00:00',
            'hora_audiencia' => (!empty($request->HoraAudiencia))? $request->HoraAudiencia : null,
            'fecha_aviso' => $fecha_aviso->format('Y-m-d').' 00:00:00',
            'arreglo_conciliatorio' => (!empty($request->ArregloConci))? $request->ArregloConci : null, //textarea descipcion
            'fecha_proxima' => (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null,
            'costo_estimado_honorarios' => (!empty($request->honorarios))? $request->honorarios : 0,
            'estatus' => '0',
            'conciliacion' => (!empty($request->ArregloConci))? $request->ArregloConci : null,
            'estatus_final' => $estatusFinal,
            'concluido' => $Arreglo,
            'concluido_motivo' => (!empty($request->MotivoArregloConci))? $request->MotivoArregloConci:"",
        ]);
        $this->cargarEvidencia($request,$audiencia);
        return response()->json(['ok' => 1,'msg' => 'La audiencia se agregó con éxito']);
    }

    function cargarEvidencia(Request $request,$idaudiencia = null)
    {
        $audiencia = ($idaudiencia != null)? $idaudiencia : $request->idAudiencia;
        $fcreado = new DateTime();

        if(!File::exists(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas'))) {
            File::makeDirectory(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas'), $mode = 0777, true, true);
        }
        if(!File::exists(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda))) {
                File::makeDirectory(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda), $mode = 0777, true, true);
        } 
        if(!File::exists(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda.'/'.$audiencia))) {
            File::makeDirectory(public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda.'/'.$audiencia), $mode = 0777, true, true);
        }

        $folder_repositorio = public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda.'/'.$audiencia.'/');
        
        
        
        if(!empty($request->imagenS) && count($request->imagenS) > 0){
            $i = 0;
            foreach($request->imagenS as $evidencia){
                $extension = pathinfo($evidencia->getClientOriginalName(), PATHINFO_EXTENSION);
                //$nombre = "prejudicial_".$request->idDemanda."_".$audiencia."_".$fcreado->format('YmdHis')."_".$i.".".$extension;
                $nombre = $request->idDemanda."_".$audiencia."_".$i."_".$evidencia->getClientOriginalName();

                //$evidencia->move(public_path('uploads/'.$empresa["id"].'/demandas/'.$request->idDemanda.'/'.$audiencia.'/'), $nombre);
                $evidencia->move($folder_repositorio, $nombre);

                EvidenciaJuridico::insert([
                        'id_audiencia' => $audiencia,
                        'nombre' => $nombre,
                        'tipo' => $extension
                    ]);
                $i++;
            }
        }
    }


    function cargarEvidenciaLaudo(Request $request,$idaudiencia = null)
    {
        $empresa = Session::get('empresa');
        $fcreado = new DateTime();
        //echo $idaudiencia."---";
        $audiencia = ($idaudiencia != null)? $idaudiencia : $request->idAudiencia;
        $folder_repositorio = public_path('storage/repositorio/' . Session::get('empresa')['id'] . '/demandas/'.$request->idDemanda.'/'.$audiencia.'/');

        if(!empty($request->FileLaudo)){
            $i = 0;
                $extension = pathinfo($request->FileLaudo->getClientOriginalName(), PATHINFO_EXTENSION);
                //$nombre = "judicial_laudo_".$request->idDemanda."_".$audiencia."_".$fcreado->format('YmdHis').".".$extension;
                $nombre = "judicial_laudo_".$request->idDemanda."_".$audiencia."_".$i."_".$request->FileLaudo->getClientOriginalName();

                $request->FileLaudo->move($folder_repositorio, $nombre);
                $id = EvidenciaJuridico::insertGetId([
                        'id_audiencia' => $audiencia,
                        'nombre' => $nombre,
                        'tipo' => $extension
                    ]);
                return $id;
        }
        return null;
    }


    public function guardaJudicial(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        $fecha_aviso = (!empty($request->FechaAviso))? new DateTime($request->FechaAviso) : null;
        $fecha_audiencia = (!empty($request->FechaAudiencia))? new DateTime($request->FechaAudiencia) : null;
        $fecha_proxima_audiencia = (!empty($request->FechaProxima))? new DateTime($request->FechaProxima) : null;
        $estatusFinal='FINALIZADO';
        $Arreglo = (!empty($request->arreglo_conciliatorio))? 1 : 0;
        $Amparo = (!empty($request->Amparo))? 1 : 0;
        $estatusFinal='FINALIZADO';
        //dd($Arreglo);
        if($Arreglo){
            $estatusFinal='CONCILIADO';
        }
        if($Amparo){
            $estatusFinal='AMPARO';
        }

        $datos_audiencia = array(
            'id_demanda' => $request->idDemanda,
            'pre' => $request->pre,
            'junta' => empty(!$request->junta) ? $request->junta : NULL,
            'expediente' => $request->expediente,
            'ciudad' => $request->ciudad,
            'fecha_audiencia' => $fecha_audiencia->format('Y-m-d').' 00:00:00',
            'hora_audiencia' => (!empty($request->HoraAudiencia))? $request->HoraAudiencia : null,
            'fecha_aviso' => $fecha_aviso->format('Y-m-d').' 00:00:00',
            'tipo_audiencia' => empty(!$request->TipoAudiencia) ? $request->TipoAudiencia : NULL,
            'incidencia' => (!empty($request->incidencias))? $request->incidencias : null, //textarea descipcion
            'fecha_proxima' => (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null,
            'costo_estimado_honorarios' => (!empty($request->honorarios))? $request->honorarios : 0,
            'arreglo_conciliatorio' => (!empty($request->ArregloConci))? $request->ArregloConci : null, //textarea descipcion
            'estatus' => 1,
            'conciliacion' => (!empty($request->ArregloConci))? $request->ArregloConci : null, //textarea descipcion
            'tipo_contestacion' => (!empty($request->contestacion))? $request->contestacion : null,
            'tipo_prueba' => empty(!$request->tipoPrueba) ? $request->tipoPrueba : NULL,
            'motivo' => empty(!$request->motivo) ? $request->motivo : NULL,
            'prueba_pericial' => (!empty($request->RadioTipoPrueba)) ? $request->RadioTipoPrueba : null,
            'alegatos' => empty(!$request->ObservacionesAlegato) ? $request->ObservacionesAlegato : NULL,
            'laudo' => empty(!$request->TipoSitua) ? $request->TipoSitua : NULL,
            'desahogo' => empty(!$request->desahogo) ? $request->desahogo : NULL,
            'motivo_desahogo' => empty(!$request->motivoDesahogo) ? $request->motivoDesahogo : NULL,
            'monto' => empty(!$request->monto) ? $request->monto : 0,
            'forma_pago' => empty(!$request->FormaPago) ? $request->FormaPago : NULL,
            'estatus_final' => $estatusFinal,
            'concluido' => $Arreglo,
            'concluido_motivo' => (!empty($request->MotivoArregloConci))? $request->MotivoArregloConci:"",
            'amparo' => $Amparo,
            'historial' => empty(!$request->TipoAudiencia) ? $request->TipoAudiencia : NULL,
        );


        if(!empty($request->idAudiencia) && $request->idAudiencia != ""){//editar
            $id_audiencia = $request->idAudiencia;
            $audiencia = Audiencia::where("id",$id_audiencia);
            $audiencia->update($datos_audiencia);
            $this->cargarEvidencia($request);
            $doclaudo = $this->cargarEvidenciaLaudo($request);
            $audiencia->update(['documento_laudo'=>$doclaudo]);
        
        }else{ // agregar
            $id_audiencia = Audiencia::insertGetId($datos_audiencia);
            $this->cargarEvidencia($request,$id_audiencia);
            $doclaudo = $this->cargarEvidenciaLaudo($request,$id_audiencia);
            if($doclaudo!= null){
                Audiencia::find($id_audiencia)->update(['documento_laudo'=>$doclaudo]);
            }
        }

        // involucrados
        $involucrados = array();
        for($i = 1; $i <= 5; $i++){
            $persona = "persona".$i;
            $domicilio = "domicilio".$i;
            $estado = "estado".$i; 
            if((isset($request->$persona) && !empty($request->$persona)) && (isset($request->$domicilio) && !empty($request->$domicilio)) && isset($request->$estado) && !empty($request->$estado)){
                array_push($involucrados,array("nombre" => $request->$persona, "domicilio" => $request->$domicilio, "estado" => $request->$estado, "id_audiencia" => $id_audiencia));
            }
        }

        if(count($involucrados) > 0){
             DB::connection('empresa')->table('involucrados_audiencias')->where('id_audiencia',$id_audiencia)->delete();
             DB::connection('empresa')->table('involucrados_audiencias')->insert($involucrados);
        }


        if($Arreglo){
            $estatusFinal='CONCILIADO';
            $d = Demanda::where("id",$request->idDemanda)->update([
                'est_importe' => isset($request->EstImporte) ? 1 : 0,
                'est_prestaciones_d' => isset($request->EstPrestaciones) ? 1 : 0,
                'est_indm_cons' => isset($request->EstIndmCon) ? 1 : 0,
                'est_indm_anio' => isset($request->EstIndmAno) ? 1 : 0,
                'est_salario_caido' => isset($request->EstSalarioCaido) ? 1 : 0,
                'importe_extra' => isset($request->ImporteExtra) ? $request->ImporteExtra : 0,
                'estatus' => 3,
                'motivo_arreglo_conciliacion' => isset($request->MotivoArregloConci) ? $request->MotivoArregloConci : NULL,
                'fecha_proxima_audiencia' => (!empty($fecha_proxima_audiencia))? $fecha_proxima_audiencia->format('Y-m-d').' 00:00:00' : null,
            ]);
        }
        if($Amparo){
            Audiencia::insert([
                'id_demanda' => $request->idDemanda,
                'pre' => 2,
            ]);
            $d = Demanda::find($request->idDemanda)->update([
                'estatus' => 2, //AMPARO
            ]);
            if($d){
                return response()->json(['ok' => 1,'msg' => 'Se ha guardado el Registro exitosamente, se inicio el proceso de Amparo']);
            }else{
                return response()->json(['ok' => 0,'msg' => 'No se pudo iniciar el proceso de Amparo, intente nuevamente']);

            }
        }

        return response()->json(['ok' => 1,'msg' => 'Audiencia creada con éxito']);
        
    }


    public function editaConstitucional(Request $request)
    {
        cambiarBase(Session::get('base'));
        $fecha_aviso = (!empty($request->FechaAviso))? new DateTime($request->FechaAviso) : null;
        $fecha_audiencia = (!empty($request->FechaAudiencia))? new DateTime($request->FechaAudiencia) : null;
        $fecha_proxima_audiencia = (!empty($request->FechaProxima))? new DateTime($request->FechaProxima) : null;
        $fecha_sentencia = (!empty($request->FechaSentencia))? new DateTime($request->FechaSentencia) : null; 
        
        $audiencia = Audiencia::where('id',$request->idAudiencia)->update([
            'fecha_audiencia' => $fecha_audiencia->format('Y-m-d').' 00:00:00',
            'hora_audiencia' => (!empty($request->HoraAudiencia))? $request->HoraAudiencia : null,
            'fecha_aviso' => $fecha_aviso->format('Y-m-d').' 00:00:00',
            'tipo_prueba' => (!empty($request->TipoPruebaCons)) ? $request->TipoPruebaCons : NULL,
            'motivo' => (!empty($request->observacionesCons)) ? $request->observacionesCons : NULL,
            'costo_estimado_honorarios' => (!empty($request->honorarios)) ? $request->honorarios : NULL,
            'monto' => (!empty($request->montoCons)) ? $request->montoCons : 0,
            'forma_pago' => (!empty($request->FormaPagoCons)) ? $request->FormaPagoCons : 0,
            'sentido' => (!empty($request->Sentido)) ? $request->Sentido : NULL,
            'fecha_sentencia' => $fecha_sentencia->format('Y-m-d').' 00:00:00',
            'estatus_final' => 'NUEVO',
            'estatus' => 0
            
        ]);
        //echo $audiencia;
        return response()->json(['ok' => 1, 'msg' => "Audiencia actualizada con éxito"]);
    }

    public function guardaMasiva(Request $request){
       // print_r($_POST);
        if(!empty($request->demandas) && count($request->demandas) > 0){
            foreach($request->demandas as $demanda){
                $request->idDemanda = $demanda;
                $request->pre = 0;
              //  $request->masiva = 1;
                $this->guardaJudicial($request);
            }
            return response()->json(['ok' => 1, 'msg' => "Audiencias agregadas con éxito"]);
        }
        return response()->json(['ok' => 0, 'msg' => "No fue posible agregar las Audiencias, intentelo nuevamente"]);

    }


    public function borrarEvidencia(Request $request){
        cambiarBase(Session::get('base'));

        $evidencia = EvidenciaJuridico::find($request->idEvidencia);
        $evi = str_replace('/', '\%', $request->url);
        $evid = str_replace('%', '', $evi);
        if(empty($request->masiva)){ // si no es audiencia masiva se elimina archivo y registro
            if(file_exists($evid)){
                if(File::delete($evid)){
                    $evidencia->delete();
                    return response()->json(['ok' => 1,'msg'=>'La evidencia se eliminó con éxito']);
                }
            }else{
                $evidencia->delete();
                    return response()->json(['ok' => 1,'msg'=>'La evidencia se eliminó con éxito']);
            }
        }else{ //si es audiencia masiva
            $evidencia->delete();
            return response()->json(['ok' => 1,'msg'=>'La evidencia se eliminó con éxito']);
        }
        return response()->json(['ok' => 0,'msg'=>'El archivo no puede ser borrado  ']);
    }




}

?>