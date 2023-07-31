<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\UsuariosEmpresas;
use App\Models\EmpresaParametros;
use App\Models\FormularioEncuesta;
use App\Models\DetalleFormularioEncuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use DateTime;

class EmpresaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function cambiarEmpresa(Request $request)
    {
        $id=$request['enterprise']; // id empresa = enterprise
        $empresa = Empresa::find($id);
        $base =$empresa->base;

        if (!empty($base)) {

            cambiarBase($base);
            Session::put('base',$base);
            Session::put('empresa', $empresa->toArray());
            $this->obtenerPermisosUsuario(Auth::id());
            $this->obtenerPramatrosEmpresa();
            $this->deshabilitaEncuestas();
            
            logEmpresa($empresa->base, Auth::user()->email, 'Inicio de Sesion');
            logGeneral(Auth::user()->email, 'El Usuario ha Iniciado Sesion en ' . $empresa->razon_social, $empresa->base, 'V3');
            return redirect()->route('bandeja');

        }else{
            return redirect()->route('home');
        }
    }

    /*
    * Obtiene y setea los permisos del usuario segun la empresa escogida
    * * y los agrega a la sesion
    */
    protected function obtenerPermisosUsuario($id_usuario)
    {
        cambiarBase(Session::get('base'));
        $permisos = Permiso::where('id_usuario', $id_usuario)->first();
        

        /************************************************* */
        ($permisos) ? Session::put('usuarioPermisos', $permisos->toArray()) : Session::put('usuarioPermisos', []);
        /************************************************* */

         /* Cambiopara obtener sedes asignadas a empleados 24-02-21*/
         $deptos = UsuariosEmpresas::select('departamentos','sedes')
            ->where('id_usuario', $id_usuario)
            ->where('id_empresa', Session::get('empresa')['id'])
            ->where('estatus', 1)
            ->first();
        
        $departamentos = ($deptos->departamentos) ? explode(',', $deptos->departamentos) : [];

        $sedes = ($deptos->sedes) ? explode(',', $deptos->sedes) : [];

        /************************************************* */
        Session::put('usuarioDepartamentos', $departamentos);
        Session::put('usuarioSedes', $sedes);
        /************************************************* */
    }
     /*
     * Obtiene y setea los parametros particulares de la empresa escogida
     * y los agrega a la sesion
     */
    protected function obtenerPramatrosEmpresa()
    {
        cambiarBase(Session::get('base'));
        $parametros = EmpresaParametros::first();
        Session::push('empresa.parametros', $parametros->toArray());
    }

    protected function deshabilitaEncuestas()
    {
        cambiarBase(Session::get('base'));
        try{
            $actual = Carbon::now();
            $actual = $actual->format('Y-m-d');
            $encuestas = FormularioEncuesta::where('fecha_vencimiento', 'LIKE', "%$actual%")->where('estatus',1)->get();
          
            if(sizeof($encuestas) > 0){
                
                $fechaActual =Carbon::now();
                $fechaActual = $fechaActual->format('H:i:s');
                $t =[];
                foreach($encuestas as $encuesta){
                
                    $fecha_vencimiento = Carbon::parse($encuesta->fecha_vencimiento );
                    $fecha_vencimiento=$fecha_vencimiento->format('H:i:s');
                    $diff_horas = Carbon::parse($fechaActual)->floatDiffInHours($fecha_vencimiento,false);
                    if($diff_horas < 0 ){

                        DetalleFormularioEncuesta::where('id_encuesta',$encuesta->id)->update(['estatus'=>3]); 
                        $t[]=$encuesta->id;        
                    }         
                }
            }
        }catch(\Exception $e){
            $mensaje = $e->getMessage();

        }
    }
}
