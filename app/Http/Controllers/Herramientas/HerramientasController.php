<?php

namespace App\Http\Controllers\herramientas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parametros;
use App\Models\ConceptosNomina;
use Illuminate\Support\Facades\Session;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ConfiguracionOrganigrama;

class HerramientasController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    /* Inicia vista parámetros de la empresa */
    public function parametros()
    {
        cambiarBase(Session::get('base'));
        $parametros = Parametros::all();
        $id_empresa = Session::get('empresa')['id'];
        $verificar_config = ConfiguracionOrganigrama::where('id_empresa',$id_empresa)->first();
        $lleva_puestos_reales ="";
        $lleva_rama = "";

        if(!empty($verificar_config)){

            $lleva_puestos_reales = $verificar_config->lleva_puestos_reales;
            $lleva_rama = $verificar_config->lleva_ramas;
        }

        return view('herramientas.parametros', compact('parametros','lleva_puestos_reales','lleva_rama'));
    }
    /* Editar los parametros de la empresa */
    public function editarParametros(Request $request)
    {
       $validated = $request->validate([
            'ejercicio' => 'required',
            'uma' => 'required',
            'salario_minimo' => 'required',
            'salario_maximo' => 'required',
            'dias_aviso_contrato' => 'required',
        ]); 

        cambiarBase(Session::get('base'));

        $alias = $request->lleva_alias;
        $lleva_rama = $request->lleva_rama;

        if($alias=="1"){
            
            $conexion ="empresa";
            $base = Session::get('base');
            $id_empresa = Session::get('empresa')['id'];
      
            $stm = "CREATE TABLE IF NOT EXISTS ".$base.".puestos_alias(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `alias` varchar(250) DEFAULT NULL,
                `jerarquia` int(11) DEFAULT NULL,
                `dependencia` int(11) DEFAULT NULL,
                `estatus` int(11) DEFAULT '1',
                PRIMARY KEY (`id`)
                )ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;";
            DB::connection($conexion)->statement($stm);

            $stm = "CREATE TABLE IF NOT EXISTS ".$base.".puestos_detalle(
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `id_puesto` int(11) DEFAULT NULL,
                    `id_alias` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;";
            DB::connection($conexion)->statement($stm);

            $id_alias = Schema::connection($conexion)->hasColumn('empleados','id_alias');
            if(!$id_alias){

                $stm = "ALTER TABLE ".$base.".empleados ADD id_alias INT NULL  ;";
                DB::connection($conexion)->statement($stm);
            }

            if($lleva_rama!=="0"){

                $stm = "ALTER TABLE ".$base.".puestos_alias ADD rama INT NULL  ;";
                DB::connection($conexion)->statement($stm);
            }
   
            $valida_exist =ConfiguracionOrganigrama::where('id_empresa',$id_empresa)->count();
            $array_datos = ['id_empresa'=>$id_empresa,'lleva_puestos_reales'=>$alias,'lleva_ramas'=>$lleva_rama];
            ($valida_exist==0) ? ConfiguracionOrganigrama::create($array_datos) : ConfiguracionOrganigrama::where('id_empresa',$id_empresa)->update($array_datos);
        
               
        }

        $except = ['_token', 'esconder', 'logo_empresa_cliente_', 'logo_empresa_emisora_','lleva_alias','lleva_rama'];
        $path = 'public/repositorio/' . Session::get('empresa')['id'] . '/';

        if (!empty($request->logo_empresa_cliente_)) {
            $archivo = $request->file('logo_empresa_cliente_');
            $nombreArchivo = 'LC-' . time() . '.' . $archivo->getClientOriginalExtension();
            $archivo = $archivo->storeAs($path, $nombreArchivo);
            $request->request->add(['logo_empresa_cliente' => $nombreArchivo]);
        }

        if (!empty($request->logo_empresa_emisora_)) {
            $archivo = $request->file('logo_empresa_emisora_');
            $nombreArchivo = 'LE-' . time() . '.' . $archivo->getClientOriginalExtension();
            $archivo = $archivo->storeAs($path, $nombreArchivo);
            $request->request->add(['logo_empresa_emisora' =>  $nombreArchivo]);
        }

        Parametros::where('id', $request->id)->update($request->except($except));

        /*$empresas = Empresa::select('base')
            ->where('estatus', 1)
            ->where('sss', 0)
            ->get();
        foreach ($empresas as $empresa) {
            cambiarBase($empresa->base);
            if ($request->esconder) {
                $conceptosNomina = ConceptosNomina::select('id')->where('estatus', 1)->where('file_rool', '>=', 250)->get();
                foreach ($conceptosNomina as  $conceptoNomina) {
                    $ids[] = $conceptoNomina->id;
                }
                ConceptosNomina::whereIn('id', $ids)->update(['file_rool' => 0]);
            } else {
                $conceptosNomina = ConceptosNomina::select('id')->where('estatus', 1)->where('file_rool', 0)->get();
                $ids = array();
                foreach ($conceptosNomina as  $conceptoNomina) {
                    $ids[] = $conceptoNomina->id;
                }
                $file_rool = rand(250, 450);

                ConceptosNomina::whereIn('id', $ids)->update(['file_rool' => $file_rool]);
            }
        }*/
  
        
        
        session()->flash('success', 'Los parámetros se editaron correctamente');
        return redirect()->route('herramientas.parametros');
    }
}
