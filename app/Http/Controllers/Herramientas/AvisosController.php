<?php

namespace App\Http\Controllers\Herramientas;

use App\Http\Controllers\Controller;
use App\Models\AvisoMultimedia;
use App\Models\Multimedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use DateTime;
use DataTables;

class AvisosController extends Controller
{
    protected const TIPO_IMG = 1;
    protected const TIPO_VIDEO = 2;

    public function ver(Request $request)
    {
        cambiarBase(Session::get('base'));

        if ($request->ajax()) {
            
            $avisos = AvisoMultimedia::select("id","titulo","inicio","fin","estatus","tipo")->with('multimedia');
    
            return Datatables::of($avisos->get())->editColumn('inicio', function($row){
                
                return Carbon::parse($row->inicio)->format('d/M/y');
            
            })->editColumn('fin', function($row){
                
                return Carbon::parse($row->fin)->format('d/M/y');
            
            })->editColumn('estatus', function($row){
                
                if($row->estatus == 1){
                    
                    return '<span class="text-success font-weight-bold">Activo</span>';
                
                }else{
                    
                    return '<span class="text-danger font-weight-bold">Inactivo</span>';
                
                }
            })->editColumn('tipo_aviso', function($row){
                
                if($row->tipo == self::TIPO_IMG){
                    return $row->tipo_aviso.'<img src="' . asset('/img/icono-perfil-e.png') . '" alt="img-avisos" class="w-30" data-toggle="tooltip" title="Imagen"  >';
                }else{
                    return $row->tipo_aviso.'<span class="badge badge-warning"><i class="fa fa-youtube-play  fa-2x"></i></span>';
                }
            
            })->addColumn('multimedia',function($row){
                //$multimedia = Multimedia::where("idAvisos","=",$row->idAvisos)->get();
                
                $imgs = '<div class="row">';
                if($row->tipo == 1){
                    foreach($row->multimedia as $m){
                        $imgs .= '<div class="col-sm-2" id="multi'.$m->id.'">
                                    <a href="'.asset('storage/repositorio/'.Session::get('empresa')['id'].'/avisos/'.$m->nombre).'"  data-toggle="lightbox" data-title="Multimedia" data-gallery="gallery" >
                                        <img src="'.asset('storage/repositorio/'.Session::get('empresa')['id'].'/avisos/'.$m->nombre).'" class="img-fluid"/>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm eliminar-multimedia tooltip_" title="Eliminar multimedia" data-multimedia="'.$m->id.'" style="width:100%;border-radius:0rem 0rem 0.25rem 0.25rem">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>';
                    }
                }else{
                    //dd($row);
                    $imgs .= '<p><a href="'.$row->multimedia[0]->nombre.'" data-toggle="lightbox">'.$row->multimedia[0]->nombre.'</a></p>';
                }
                $imgs .= '</div>';
                return $imgs;
            
            })->addColumn('acciones', function($row){

                $video = ""; 
                $tiempovideo = 0; 
                if($row->tipo == 2){
                    //dd($row->multimedia[0]);
                    $video = $row->multimedia[0]->nombre; 
                    $tiempovideo = $row->multimedia[0]->tiempo; 
                }
                return '<span data-toggle="tooltip" data-html="true"  title="Modificar Aviso" class="tooltip_">
                    <a class="enviaf btn mr-2"  data-toggle="modal" data-target="#avisoModal"  
                        data-aviso="'.$row->id.'"
                        data-titulo="'.$row->titulo.'"
                        data-inicio="'.$row->inicio.'"
                        data-video="'.$video.'"
                        data-tiempo="'.$tiempovideo.'"
                        data-fin="'.$row->fin.'"
                        data-tipo="'.$row->tipo.'"
                        data-estatus="'.$row->estatus.'">
                        <img src="/img/icono-editar.png" class="button-style-icon">
                    </a>
                </span>
                <a href="#" data-toggle="tooltip" title="Eliminar Aviso" data-aviso="'.$row->id.'" class="borrar btn  mr-2 tooltip_" alt="Eliminar Aviso" title="Eliminar Aviso">  <img src="/img/icono-eliminar.png" class="button-style-icon"></a>'; 
            
            })->rawColumns(['tipo_aviso','multimedia','estatus','acciones'])->make(true);
        }
        return view("herramientas.avisosMultimedia.inicio");
    }
    
    public function agregar(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empresa = Session::get('empresa');
        
        $finicio = new DateTime($request->inicio);
        $ffin = new DateTime($request->fin);
        $fcreado = new DateTime();
        $status = ($request->estatus == "on")? 1 : 0;
        $id_aviso = AvisoMultimedia::insertGetId([
            'titulo' => $request->titulo,
            'inicio' => $finicio->format('Y-m-d').' 00:00:00',
            'fin' => $ffin->format('Y-m-d').' 00:00:00',
            'estatus' => $status,
            'creado' => $fcreado->format('Y-m-d').' 00:00:00',
            'tipo' => $request->tipo,
        ]);

        if(!empty($id_aviso)){
            if($request->tipo == 1){
                for($i = 1; $i <= 5; $i++){
                    if(!empty($request->file("imagen_".$i))){
                        $cont = 1;
                        $multimedia = array();
                        foreach($request->file("imagen_".$i) as $img){
                            $extension = pathinfo($img->getClientOriginalName(), PATHINFO_EXTENSION);
                            $nombre = "aviso".$id_aviso."_".$i."_".$fcreado->format('YmdHis')."_".$cont.".".$extension;
                            $url_repositorio ="public/repositorio/".Session::get('empresa')['id']."/avisos/";
                            $img->storeAs ($url_repositorio,$nombre);
                            $multimedia[] = array( 
                                'id_avisos' => $id_aviso,
                                'nombre' => $nombre,
                                'tipo' => $request->tipo,
                                'tiempo' => $_REQUEST['t_'.$i]
                            );
                            $cont++;
                        }

                        Multimedia::insert($multimedia);
                    }
    
                }
            }else{
                Multimedia::insert([
                    'id_avisos' => $id_aviso,
                    'nombre' => $request->url,
                    'tipo' => 2,
                    'tiempo' => $request->tiempo
                ]);
            }
            
        }else{
            return response()->json(['ok' => 0,'msg'=>'El aviso no pudo ser creado, intente nuevamente']);
        }
        return response()->json(['ok' => 1,'msg'=>'El aviso se creó con éxito']);
    
    }

    public function borrarMultimedia(Request $request)
    {
        cambiarBase(Session::get('base'));
        $multimedia = Multimedia::find($request->idMultimedia);
        if($this->borrarArchivoMultimedia($multimedia)){
            return response()->json(['ok' => 1,'msg'=>'La imagen se eliminó con éxito']);
        }
        return response()->json(['ok' => 0,'msg'=>'La imagen no puede ser borrado  ']);
    }

    public function borrarArchivoMultimedia(Multimedia $multimedia)
    {
        $m = public_path()."/storage/repositorio/".Session::get('empresa')['id']."/avisos/".$multimedia->nombre;
      //  $mul = str_replace('/', '\%', $m);
      //  $multim = str_replace('%', '', $mul);
        if(File::delete($m)){
            $multimedia->delete();
            return true;
        }
        return false;
    }

    public function editar(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        
        $aviso = AvisoMultimedia::find($request->idAviso);
        $empresa = Session::get('empresa');
        $finicio = new DateTime($request->inicio);
        $ffin = new DateTime($request->fin);
        $fcreado = new DateTime();
        $status = ($request->estatus == "on")? 1 : 0;

        if($request->tipo == 1){ // tipo imagen

            if($aviso->tipo != $request->tipo){ // si cambio el tipo de multimedia a imagen eliminamos el video
                Multimedia::where('id_avisos', $request->idAviso)->delete();
            }
            //si hay nuevas imagenes se agregan
            
            for($i = 1; $i <= 5; $i++){//

                if(!empty($request->file("imagen_".$i))){
                    $cont = 1;
                    $multimedia = array();
                    foreach($request->file("imagen_".$i) as $img){
                            $extension = pathinfo($img->getClientOriginalName(), PATHINFO_EXTENSION);
                            //el nombre contiene $fcreado->format('YmdHis') que genera una cadena unica para evitar datos duplicados
                            $nombre = "aviso".$aviso->id."_".$i."_".$fcreado->format('YmdHis')."_".$cont.".".$extension;
                            $img->move(public_path()."/storage/repositorio/".Session::get('empresa')['id']."/avisos/", $nombre);
                            $multimedia[] = array('id_avisos' => $aviso->id,'nombre' => $nombre,'tiempo' => $_REQUEST['t_'.$i]);
                            $cont++;
                    }
                    
                    Multimedia::insert($multimedia);
                }
            }

        }else if($request->tipo == 2){ // tipo video

            if($aviso->tipo != $request->tipo){ //si anteriormente era imagen
                $imagenes = Multimedia::where('id_avisos', $request->idAviso)->get();
                foreach($imagenes as $img){
                    $this->borrarArchivoMultimedia($img);
                    // se borra imagen y registro
                }
                //se inserta el registro para video
                Multimedia::insert(['id_avisos' => $request->idAviso,'tiempo' => $request->tiempo,'nombre' => $request->url]);
            }else{ // solo se actualiza la información
                //echo $request->idAviso;
                $video = Multimedia::where('id_avisos', $request->idAviso)->first();
                //dd($request->url);
                $video->update(['nombre' => $request->url]);

            }
        }
        //actualizar la información del aviso
        $id_aviso = AvisoMultimedia::find($request->idAviso)->update([
            'titulo' => $request->titulo,
            'inicio' => $finicio->format('Y-m-d').' 00:00:00',
            'fin' => $ffin->format('Y-m-d').' 00:00:00',
            'estatus' => $status,
            'creado' => $fcreado->format('Y-m-d').' 00:00:00',
            'tipo' => $request->tipo,
        ]);
        
        return response()->json(['ok' => 1,'msg'=>'el aviso se actualizó con éxito ']);
        
    }

    public function borrarAviso(Request $request)
    {
        $aviso = $this->avisoMultimedia($request);
        foreach($aviso->multimedia as $multimedia){
            if($multimedia->tipo == 1){
                $this->borrarArchivoMultimedia($multimedia);
            }
        }
        if($aviso->delete()){
            return response()->json(['ok' => 1,'msg'=>'el aviso se eliminó con éxito ']);
        }
        return response()->json(['ok' => 0,'msg'=>'el aviso no puede eliminarse, intentelo de nuevo']);
    }

    //devuelve aviso con multimedia
    public function avisoMultimedia(Request $request)
    {
        cambiarBase(Session::get('base'));
        return AvisoMultimedia::where('id',$request->idAviso)->with('multimedia')->first();
    }
    
}
