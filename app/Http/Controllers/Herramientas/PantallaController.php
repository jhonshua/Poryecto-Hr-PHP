<?php

namespace App\Http\Controllers\Herramientas;

//use App\Models\Evento;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use File;
use Input;
use App\Models\Empresa;

class PantallaController extends Controller
{
  
    public function ver(Request $request)
    { 

        try{

            $id=decrypt($request->id);
            $empresa = Empresa::select('id','razon_social','base')->find($id);
            $empresa->repositorio =  url('/public')."/repositorio/".$id."/";
            cambiarBase($empresa->base);
            
            $query = "select `id`, `file_fotografia`, `nombre`, `apaterno`, `amaterno`, `fecha_nacimiento` from `empleados` where `estatus` = 1 and MONTH(fecha_nacimiento) = ". date('m') ." order by DAY(fecha_nacimiento) asc;";
            $cumpleanios = DB::connection('empresa')->select($query);

            if($empresa->base == 'empresa000222')
                return view('avisosMultimedia.pantalla.sindicato',compact('empresa','cumpleanios'));
            else
                return view('avisosMultimedia.pantalla.sindicato',compact('empresa','cumpleanios'));
        
        }catch(\Exception $e) {
            
            return redirect()->route('ajax.listadoInicio');
        
        }
    }

    public function sindicato(){
        
        return view('avisosMultimedia.pantalla.sindicato');
    }
}