<?php

namespace App\Http\Controllers\parametria;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Empresa;
use App\Models\Departamento;

class DepartamentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const DEPTO_ACTIVO = 1;
    protected const DEPTO_INACTIVO = 0;

    public function inicio()
    {
        //tienePermisoA('departamentos');
        cambiarBase(Session::get('base'));
        $deptos = Departamento::where('estatus', self::DEPTO_ACTIVO)->orderBy('nombre', 'asc')->get();
        return view('parametria.departamentos.inicio', compact('deptos'));
    }

    public function crearEditar(Request $request)
    {
        cambiarBase(Session::get('base'));
    
        $existe = Departamento::where('nombre', strtoupper($request->nombre))->get();
        if(count($existe) <= 0){

            (empty($request->id)) ? Departamento::create(['nombre' => strtoupper($request->nombre), 'estatus' => 1] ) : Departamento::where('id',decrypt($request->id) )->update(['nombre' => strtoupper($request->nombre)] );
            
            session()->flash('success', 'Los datos se guardaron correctamente');
        
        } else {
            
            session()->flash('danger', 'Los datos no se pudieron procesar está área ya existe..!!');
        }
       
        return redirect()->route('parametria.departamentos.inicio');
    }

    public function borrar(Request $request)
    {
       try{

            cambiarBase(Session::get('base'));
            Departamento::destroy(decrypt( $request->id));
            $respuesta =1 ;

        }catch(\Exception $e){
            $respuesta =2 ;
        }
        return response()->json(['respuesta' => $respuesta ]);
    }
}
