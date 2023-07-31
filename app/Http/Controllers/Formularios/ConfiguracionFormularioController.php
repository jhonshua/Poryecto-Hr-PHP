<?php

namespace App\Http\Controllers\formularios;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\ConfiguracionFormulario;
use App\Models\IconoConfiguracionFormulario;


class ConfiguracionFormularioController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function inicio()
    {
        cambiarBase(Session::get('base'));

        $consultas = ConfiguracionFormulario::orderBy('id','DESC')->get();
        return view('formularios.configuracion-formularios.inicio',compact('consultas'));
    }

    public function agregarEditar(Request $request)
    {
        cambiarBase(Session::get('base'));
        $titulo = $request->titulo;

        if (empty($request->id)) {
            $ConfiguracionFormulario = ConfiguracionFormulario::firstOrCreate(['titulo'=>$titulo]);

            foreach ($request->file as $key=>  $f) {
                $this->subirIconos( $key , $f, $request->valor[$key] , $ConfiguracionFormulario->id );
            }

            session()->flash('success', 'Los datos se guardaron correctamente..!!');

            return redirect()->route('configuracion.formularios.inicio');
        } else {
            $idconfigform =decrypt($request->id);
            $ConfiguracionFormulario = ConfiguracionFormulario::where('id',$idconfigform)->update(['titulo'=>$titulo]);
            foreach ($request->idicon as $key=> $idicono ) {
                if (!empty($idicono)) {
                    $consulta = IconoConfiguracionFormulario::where('id',decrypt($idicono))->first();
                    $valor =$request->valor[$key];

                    if(!empty($request->file[$key])){
                       
                            $path="public/configuracion-formularios/svg";
                            $file=$request->file[$key];  
                            $extension = $file->getClientOriginalName();
                            $nombre = $key.'_'.time().$extension;
                            $file->storeAs ($path,$nombre);
                            $icono=$nombre;
                          
                            $logodelete = $consulta->icono;
                            if($logodelete != "default.png"){
                            
                                unlink(storage_path('app/public/configuracion-formularios/svg/'.$logodelete));
                            }
                            $consulta->update(['icono'=>$icono,'valor'=>$valor]); 
                    }else{

                        $consulta->update(['valor'=> $valor]); 
                    }
                } else {
                    $this->subirIconos($key, $request->file[$key] , $request->valor[$key] , $idconfigform );
                }
            }
            session()->flash('success', 'Los datos se guardaron correctamente..!!');

            return redirect()->route('configuracion.formularios.obtenerFormularios',['id'=>$request->id,'titulo'=>$request->titulo]);
        }
    }

    public function subirIconos($key, $f, $val, $idconfigform)
    {
        if ($f) {
            $path="public/configuracion-formularios/svg/";
            $file=$f;
            $extension = $file->getClientOriginalName();
            $nombre = $key.'_'.time().$extension;
            $file->storeAs ($path,$nombre);
            $icono=$nombre;
            $valor = $val;
        } else {
            $icono = "default.png";
        }

        $configuracion_formulario= IconoConfiguracionFormulario::create(array('idconfigform'=>$idconfigform,'icono'=>$icono,'valor'=> $valor ));
    }

    public function obtenerFormularios(Request $request)
    {
        cambiarBase(Session::get('base'));
        $consultas =  IconoConfiguracionFormulario::where('idconfigform',decrypt($request['id']))->get();
        $titulo = $request['titulo'];

        return view('formularios.configuracion-formularios.modificar-iconos-formulario',compact('consultas','titulo'));
    }

    public function eliminarItem(Request $request)
    {
        cambiarBase(Session::get('base'));
        $icono = IconoConfiguracionFormulario::where('id',decrypt($request->id))->first();
        unlink(storage_path('app/public/configuracion-formularios/svg/'.$icono->icono));
        $icono->delete();

        return response()->json(1);
    }

    public function deshabilitarIconos(Request $request)
    {
        cambiarBase(Session::get('base'));
        ConfiguracionFormulario::where('id',decrypt($request->id))->update(['estatus'=>$request->estatus]);
        return response()->json($request->estatus);
    }
}
