<?php

namespace App\Http\Controllers\consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\ConfiguracionOrganigrama;
use App\Models\Puesto;
use App\Models\PuestoAlias;
use App\Models\PuestoDetalle;

class OrganigramaController extends Controller
{
    public function inicio()
    {
        try{

            $tipoEmpresa=Session::get('base');
            $id_empresa = Session::get('empresa')['id'];
            $verificar_config = ConfiguracionOrganigrama::where('id_empresa',$id_empresa)->first();
            $pasos = 0;
            $lleva_rama ="";
            $lleva_puestos_reales ="";
            $collection ="";
            $ruta ="";
            $puestos ="";
            $puestos_reales="";
            $idconfiguracion="";
            if(!empty($verificar_config)){

                cambiarBase(Session::get('base'));

                $lleva_puestos_reales = $verificar_config->lleva_puestos_reales;
                $lleva_rama = $verificar_config->lleva_ramas;
                $idconfiguracion = $verificar_config->id;

                $ruta = (string) asset('storage/repositorio/'.$id_empresa);

                if($tipoEmpresa==="empresa000046"){  // Si la empresa es la 46 entonces se realiza el siguiente proceso

                    //$arrayEmpleados = new \stdClass;
                    /*Se declara un arreglo llamado directivos y se imprimen los valores directos ya que los CEO´S no estan registrados en db */
                    $arrayEmpleados = array(['ids'=>2,'key'=>10000,'name'=>'Ramsés Palomo Reyes','title'=>'DIRECCION','post' =>'DIR.COMERCIAL','img'=>$ruta.'/fotos_directivos/ramses.jpg'],
                                            ['ids'=>3,'key'=>20000, 'name'=>'Armando Culebro','title'=>'DIRECCION','post' =>'DIR.ADJUNTO','img'=>$ruta.'/fotos_directivos/armando.jpg']);
                    $collection = collect($arrayEmpleados);                        

                    foreach($collection as $arrDir ){

                        if($arrDir['ids']==2){ // Si el directivo es ramses = 1 entonces es front

                            $consultas = $this->consultaEmpleados(1,$ruta,$lleva_puestos_reales,$lleva_rama); // el parametro a pasar es 1 = frontend =  rama
                            $collection=$this->buscaEmpleados($consultas,$collection,$arrDir ); // el parametro a pasar es 1 = frontend =  rama

                        }else{

                            $consultas = $this->consultaEmpleados(2,$ruta,$lleva_puestos_reales,$lleva_rama); // el parametro a pasar es 2 = backend  =  rama
                            $collection=$this->buscaEmpleados($consultas,$collection,$arrDir ); // el parametro a pasar es 2 = backend =  rama
                        }
                    } 

                }else{

                    $consultas = $this->consultaEmpleados($parametro=null, $ruta,$lleva_puestos_reales,$lleva_rama);
                    $collection = collect();      
                    $collection=$this->buscaEmpleados($consultas,$collection,$arrDir="" ); // el parametro a pasar es 2 = backend =  rama
                }


            }else{
               
                $consultas = $this->consultaEmpleados($parametro=null, $ruta,$lleva_puestos_reales,$lleva_rama);
                $collection = collect();      
                $collection=$this->buscaEmpleados($consultas,$collection,$arrDir="" ); // el parametro a pasar es 2 = backend =  rama

            }
            $mensaje = true;

        }catch(\Exception $e){
           // dd($e);
            $mensaje = false;
        }
     
        return view('consultas.organigrama.inicio',compact('collection','ruta','mensaje','puestos','puestos_reales','lleva_rama','lleva_puestos_reales','idconfiguracion'));
    }

    public function consultaEmpleados($parametro,$ruta,$lleva_puestos_reales,$lleva_rama)
    {


        cambiarBase(Session::get('base'));
        if(!empty($parametro)){ // Si el parametro es lleno ya sea 1 o 2  es por que pertenece a la empresa000046  si ,no la empresa es diferente a la 46 = DEA
         
            $params =['e.estatus'=>1,'pa.rama'=>$parametro]; // se filtra por la columna rama

        }else{
            
            //$aux='p.fecha_creacion'; // Si la empresa es distinta a la 46 entonces solo se manda la fecha de creacion no se ocupa para nada el valor pero solo así no marca error la consulta de que no existe la columna rama

            $params =['e.estatus'=>1]; // el estatus siempre es 1 y no se manda ningun filtro

        } 

        /*Consulta para cuando lleva alias y ramas */

        if($lleva_puestos_reales===1 && $lleva_rama===1){ // Esta consulta aplica si seleccionaron en la configuracion de empresas que el organigrama llevara alias y sera dividido por ramas

            $consultas=DB::connection('empresa')->table('empleados as e')
                ->join('puestos_alias AS pa','pa.id','=','e.id_alias')
                ->join('puestos as p','p.id','=','e.id_puesto')
                ->join('departamentos as d','d.id','=','e.id_departamento')
                ->select('e.id as ids',
                        'e.id_alias as id',
                        'pa.dependencia as pid',
                        DB::raw('CONCAT(e.nombre, " ", e.apaterno) AS Nombre'),
                        'd.nombre as Departamento',
                        'pa.alias as Puesto',
                        DB::raw("CONCAT('$ruta','/', e.id,'/', e.file_fotografia ) AS img"),
                        'pa.jerarquia')
                ->where($params)
                ->orderBy('pa.dependencia','desc')
                ->get(); 


        }else{


            $consultas=DB::connection('empresa')->table('empleados as e')
                ->join('puestos as p','p.id','=','e.id_puesto')
                ->join('departamentos as d','d.id','=','e.id_departamento')
                ->select('e.id as ids',
                        'e.id_puesto as id',
                        'p.dependencia as pid',
                        DB::raw('CONCAT(e.nombre, " ", e.apaterno) AS Nombre'),
                        'd.nombre as Departamento',
                        'p.puesto as Puesto',
                        DB::raw("CONCAT('$ruta','/', e.id,'/', e.file_fotografia ) AS img"),
                        'p.jerarquia')
                ->where($params)
                ->orderBy('p.dependencia','ASC')
                ->get();   

        }

        return $consultas;
    }

    public function buscaEmpleados($consultas,$collection,$arrDir)
    {
        $datosEmpleado=[];
        $id="";
        foreach($consultas as $empleado){


            if($empleado->jerarquia==0){

                $collection[] = array(
                                      'key'=>$empleado->id, 
                                      'parent'=>(!empty($arrDir['key']))?$arrDir['key']:$arrDir=0, 
                                      'name'=>$empleado->Nombre,
                                      'title'=>$empleado->Departamento,
                                      'post'=>$empleado->Puesto,
                                      'img'=>$empleado->img);
            }else{


                if($empleado->id!=$id){
                    $id=$empleado->id;
                    $collection[] = array('key'=>$empleado->id, 
                                    'parent'=>$empleado->pid, 
                                    'name'=>$empleado->Nombre,
                                    'title'=>$empleado->Departamento,
                                    'post'=>$empleado->Puesto,
                                    'img'=>$empleado->img);

                }else{

                    $collection[] = array('key'=>rand(999,1099), 
                                    'parent'=>$empleado->pid, 
                                    'name'=>$empleado->Nombre,
                                    'title'=>$empleado->Departamento,
                                    'post'=>$empleado->Puesto,
                                    'img'=>$empleado->img);
                }     
            }
        } 
    
        return $collection;
    }

    public function asignarConfiguracion(Request $request){

        $conexion ="empresa";
        $base = Session::get('base');
        $id_empresa = Session::get('empresa')['id'];
        cambiarBase($base);
        $pasos = 1 ;
        $lleva_alias = $request->lleva_alias;
        $lleva_rama = $request->lleva_rama;

        if(!empty($request->idconfiguracion)){

            foreach($request->puestos  as $key=> $puesto){

                if(count($request->alias) > 0) foreach($request->alias[$puesto] as $id_alias) PuestoDetalle::create(['id_puesto'=>$puesto,'id_alias'=>$id_alias]);
                if(count($request->rama) > 0 )Puesto::where('id',$puesto)->update(['rama'=>$request->rama[$key]]);
            }

            ConfiguracionOrganigrama::where("id",$request->idconfiguracion)->update(['pasos'=>2]);
        }


            if($lleva_alias==="1" && $lleva_rama==="1" ){

                try{

                    $stm = "CREATE TABLE IF NOT EXISTS ".$base.".puestos_alias(
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `alias` varchar(250) DEFAULT NULL,
                        `jerarquia` int(11) DEFAULT NULL,
                        `dependencia` int(11) DEFAULT NULL,
                        `rama` int(11) DEFAULT NULL,
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

                    $stm = "ALTER TABLE ".$base.".empleados ADD id_alias INT NULL  ;";
                    DB::connection($conexion)->statement($stm);

                    $pasos = 1;
                    session()->flash('success', 'Los datos se guardaron correctamente');

                }catch(\Exception $e){
                    //dd($e);
                    session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
                }



            }else if($lleva_alias==="0" && $lleva_rama==="0"){
                $pasos = 2;
            }

        ConfiguracionOrganigrama::create(['id_empresa'=>$id_empresa,'lleva_puestos_reales'=>$lleva_alias,'lleva_ramas'=>$lleva_rama,'pasos'=>$pasos]);
        return redirect()->route('organigrama.inicio');
    }
}