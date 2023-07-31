<?php

namespace App\Http\Controllers\Empleado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use App\Models\Puesto;
use App\Models\Departamento;
use App\Models\Horario;

class MiPerfilEmpleadoController extends Controller
{
    protected $archivos = ['file_ine' => 'IDENTIFICACIÓN OFICIAL VIGENTE', 'file_fotografia' => 'FOTOGRAFIA', 'file_nacimiento' => 'ACTA DE NACIMIENTO', 'file_curp' => "CURP", 'file_nss' => 'NSS', 'file_rfc' => 'RFC', 'file_comprobante' => 'COMPROBANTE DE DOMICILIO', 'file_aviso' => 'AVISO DE RETENCIONES INFONAVIT', 'file_estado_cuenta' => 'ESTADO DE CUENTA', 'file_analisis' => 'ANÁLISIS', 'file_fonacot' => 'FONACOT', 'file_curriculum' => 'CURRICULUM','file_fiel_imss' => 'AFIL IMSS'];
    
    public function inicio(){
       
        $base = Session::get('base');
		cambiarBase($base);

        $empleado = Session::get('empleado');
        $categorias = DB::connection('empresa')->table('categorias')->where('estatus', 1)->get();
        $puestos = Puesto::where('estatus', 1)->get();
        $departamentos = Departamento::where('estatus', 1)->get();
        $horarios = Horario::where('estatus', 1)->get();
        $tipos_nomina = DB::connection('empresa')->table('periodos_nomina')->select('nombre_periodo')->distinct('nombre_periodo')->where('estatus', 1)->get();
        $tipos_nomina =array("Diaria","Semanal","Catorcenal","Quincenal","Mensual");
        $bancos = DB::table('bancos')->orderBy('nombre', 'asc')->get();

        $id = Session::get('empleado')['id'];
        $id_empresa=Session::get('empresa')['id'];
        $file_fotografia= Session::get('empleado')['file_fotografia'];

        $query = "SELECT em.id, em.id_categoria, ememi.razon_social from ".$base.".empleados em join ".$base.".categorias cat on em.id_categoria = cat.id inner join singh.registro_patronal regpat on cat.tipo_clase = regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora = ememi.id where em.id=".$id." and cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.estatus in (1,5, 30)";
        $empresa_emisora = DB::connection('empresa')->select(DB::raw($query));

        $empleado->avatar = (file_exists(storage_path().'/app/public/repositorio/'.$id_empresa .'/'. $id .'/'. $file_fotografia)) ? '../storage/repositorio/'.$id_empresa .'/'. $id .'/'. $file_fotografia : '/img/avatar.png';
        // Sacamos campos extras particulares para cada empresa
        try {
            // posiblemente la tabla no exista en todas las empresas
            $archivos_extras = DB::connection('empresa')->table('empleados_campos_extras') ->where('tipo', 'file')->get();
        
        } catch(\PDOException  $e) { 
            $archivos_extras = []; 
        }

        $archivos = $this->archivos;
        return view('empleados.empleado.mi_perfil.inicio',compact('empleado','categorias','puestos','departamentos','horarios','tipos_nomina','bancos','empresa_emisora','archivos','archivos_extras'));
    }
}
