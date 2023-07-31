<?php

namespace App\Http\Controllers\parametria;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\importPrestaciones;
use App\Exports\exportPrestaciones;
use App\Models\RegistroPatronal;
use App\Models\Prestacion;
use App\Models\Categoria;

class PrestacionesController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    protected const PRESTACION_ACTIVO = 1;
    protected const PRESTACION_INACTIVO = 0;

    public function inicio()
    {
        //tienePermisoA('categorias');
        cambiarBase(Session::get('base'));
        $prestaciones = Categoria::where('estatus', self::PRESTACION_ACTIVO)->orderBy('nombre', 'asc')->get();
        $idEmpresa = Session::get('empresa')['id'];
        $clases = DB::select( DB::raw("SELECT distinct  concat(rp.num_registro_patronal,'/',rp.tipo_clase) as tipo_clase, rp.id from registro_patronal as rp join asigna_empresas_emisoras as ae on rp.id_empresa_emisora = ae.id_empresa_e where rp.estatus <> 0 and ae.estatus <> 0 and ae.id_empresa = " . $idEmpresa));

        return view('parametria.prestaciones.inicio', compact('prestaciones', 'clases'));
        
    }

    public function agregar()
    {
        cambiarBase(Session::get('base'));

        $clases = $this->clases();
        
        return view('parametria.prestaciones.agregar',compact('clases'));
    }

    public function insertar(Request $request)
    {
        cambiarBase(Session::get('base'));

        $validated = $request->validate([
            'nombre' => 'required',
            'tipo_clase' => 'required',
        ]);

        try{

            $data =  [
                'nombre' => $request->nombre,
                'tipo_clase' => $request->tipo_clase,
                'estatus' => 1,
                'fecha_edicion' => date('Y-m-d H:i:s')
            ];

            Categoria::create($data);            
            session()->flash('success', 'Los datos se guardaron correctamente');

        }catch(\Exception $e){

            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        
        }
        return redirect()->route('parametria.prestaciones.inicio');
    }

    public function listado(Request $request)
    {
        $idPrestacion = \decrypt($request['id']);
 
        cambiarBase(Session::get('base'));
        
        $prestacion = Categoria::where('id', $idPrestacion)->first();
        $prestaciones = Prestacion::where('id_categoria', $idPrestacion)->where('estatus', 1)->orderBy('antiguedad', 'asc')->get();

        return view('parametria.prestaciones.listado', compact('prestacion', 'prestaciones'));
    }

    public function modificar(Request $request)
    {
        cambiarBase(Session::get('base'));
        $clases = $this->clases();
        $data = $request->all();
    
        return view('parametria.prestaciones.modificar',compact('clases','data'));
    }

    public function modificarRegistros(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        
        $id = $request['id'];
        $validated = $request->validate([
            'nombre' => 'required',
            'tipo_clase' => 'required',
        ]);

        try{

            $data =  [
                'nombre' => $request->nombre,
                'tipo_clase' => $request->tipo_clase,
                'estatus' => 1,
                'fecha_edicion' => date('Y-m-d H:i:s')
            ];

            Categoria::where('id',decrypt($id))->update($data);                
            session()->flash('success', 'Los datos se guardaron correctamente');

        }catch(\Exception $e){
            
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        
        }
     
        $data = array('id'=>$id ,'nombre'=>$request->nombre,'tipo_clase' =>encrypt($request->tipo_clase));
      
        return redirect()->route('parametria.prestaciones.modificar',$data);
    }
    
    public function clases()
    {
        $clases = RegistroPatronal::join('asigna_empresas_emisoras AS ae','ae.id_empresa_e','=','registro_patronal.id_empresa_emisora')
            ->select(DB::raw("DISTINCT CONCAT(registro_patronal.num_registro_patronal,'/',registro_patronal.tipo_clase) as tipo_clase, registro_patronal.id"))
            ->where("registro_patronal.estatus" ,"<>",0 )
            ->where("ae.estatus" ,"<>",0 )
            ->where("ae.id_empresa", Session::get('empresa')['id'] )
            ->get();

        return $clases;
    }

    public function exportar($id)
    {  
        return Excel::download( new exportPrestaciones(decrypt($id)), 'Prestaciones_'.date('d-m-Y_H:i').'.xlsx');     
    }

    public function insertarPrestacion(Request $request)
    {
        cambiarBase(Session::get('base'));
        
        $validated = $request->validate([

            'antiguedad' => 'required',
            'vacaciones' => 'required',
            'prima_vacacional' => 'required',
            'aguinaldo' => 'required',
            'bono_aguinaldo' => 'required',
            'bono_vacaciones' => 'required',
            'bono_prima_vacacional' => 'required',
            'vacaciones' => 'required',
        ]);

        try{

            // calculo de factor de integracion
            $porcentaje = $request->prima_vacacional / 100;
            $factor1 = $request->vacaciones * $porcentaje;
            $b = $request->aguinaldo + 365;
            $facInt = ($factor1 + $b) / 365;
            
            $data = array(
                'id_categoria' => decrypt($request->id_categoria),
                'antiguedad' => $request->antiguedad,
                'vacaciones' => $request->vacaciones,
                'prima_vacacional' => $request->prima_vacacional,
                'aguinaldo' => $request->aguinaldo,
                'bono_aguinaldo' => $request->bono_aguinaldo,
                'bono_vacaciones' => $request->bono_vacaciones,
                'bono_prima_vacacional' => $request->bono_prima_vacacional,
                'factor_integracion' => round($facInt,4),
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            );
  
            
            if(empty($request->id)){

                Prestacion::create($data);
                $data = ['id'=>$request->id_categoria];
                $url = 'parametria.prestaciones.listado';

            }else{
                
                Prestacion::where('id',decrypt($request->id))->update($data);
                $data = array(
                    'id' =>$request->id,
                    'id_categoria' => $request->id_categoria,
                    'antiguedad' => $request->antiguedad,
                    'vacaciones' => $request->vacaciones,
                    'prima_vacacional' => $request->prima_vacacional,
                    'aguinaldo' => $request->aguinaldo,
                    'bono_aguinaldo' => $request->bono_aguinaldo,
                    'bono_vacaciones' => $request->bono_vacaciones,
                    'bono_prima_vacacional' => $request->bono_prima_vacacional
                );
                $url = 'parametria.prestaciones.modificarPrestacion'; 
            }
                    
            session()->flash('success', 'Los datos se guardaron correctamente');

        }catch(\Exception $e){

            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        
        }
        return redirect()->route($url,$data);
    }

    public function importar(Request $request)
    {
        try{

            cambiarBase(Session::get('base'));
            $id_categoria = \decrypt($request->id_categoria);
            Excel::import( new importPrestaciones($id_categoria),$request->file('prestaciones_file'));
            session()->flash('success', 'Los datos se generaron correctamente');
            
        }catch(\Exception $e){
            session()->flash('danger', 'Los datos no se procesaron verifica que los datos correspondan al layout de ejemplo ..!!'); 
        }

        return redirect()->route('parametria.prestaciones.listado',['id'=>$request->id_categoria]);
 
    }

    public function borrarPrestacion(Request $request)
    {
        try{
            cambiarBase(Session::get('base'));
            Prestacion::where('id',\decrypt($request->id))->update(['estatus' => 0]);
            $respuesta= true;
            
        }catch(\Exception $e){
            $respuesta= false;
        }
        return response()->json(['respuesta' =>$respuesta ]);
    }

    public function borrar(Request $request)
    {
        try{

            cambiarBase(Session::get('base'));
            $id_categoria = \decrypt($request->id);
            Prestacion::where('id_categoria',$id_categoria)->delete();
            Categoria::where('id', $id_categoria)->delete();
           
            $respuesta= true;
            
        }catch(\Exception $e){
            $respuesta= false;
        }
        return response()->json(['respuesta' =>$respuesta ]);
    }
}