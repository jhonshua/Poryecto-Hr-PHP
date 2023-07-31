<?php

namespace App\Http\Controllers\Empleados;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use DateTime, DateInterval, DatePeriod;
use App\Models\Empleado;
use App\Models\TipoSolicitudVacaciones;
use Storage;

class VacacionesController extends Controller
{
    public function calendario(Request $request)
    {
        cambiarBase(Session::get('base'));
        $datos_empleado = Empleado::select('id', 'nombre', 'apaterno', 'amaterno')->where('estatus',1)->orderBy('nombre', 'asc')->get();    
        $datos_tipo_vac =TipoSolicitudVacaciones::all();
        $estatus_solicitud = 0;
        
        return view('empleados_admin.vacaciones.calendario', compact('datos_empleado', 'datos_tipo_vac', 'estatus_solicitud'));
    }

    public function inicio(Request $request)
    {
        // $fechass = '2021-07-27,2021-07-29';
        // return explode(',', $fechass);

        $empresa = Session::get('base');
        $id_empresa = Session::get('empresa')['id'];

        $query_datos_solicitud = "select ve.id, ve.total_dias, ve.fecha_solicitud, group_concat(vd.fechas_solicitud) fechas_solicitud, e.id id_empleado, e.nombre, e.apaterno, e.amaterno, 
        e.file_fotografia, e.dias_vacaciones, p.puesto, ve.estatus_solicitud, ev.descripcion nombre_estatus_solicitud, ve.tipo, lower(ts.descripcion) tipo_solicitud, 
        ts.descripcion tipo_solicitud_mayuscula, ve.usuario_autoriza, ifnull(ve.nota, '') nota, ve.file_solicitud,
        ( (TIMESTAMPDIFF(YEAR, e.fecha_antiguedad, CURDATE()) * 6) - ifnull((select sum(ve2.total_dias) from ".$empresa.".vacaciones_empleado ve2 where ve2.confirmado = 1 and ve2.id_empleado = e.id), 0) )  dias_vacaciones2,
        ifnull((select sum(ve3.total_dias) from ".$empresa.".vacaciones_empleado ve3 where ve3.confirmado = 1 and ve3.id_empleado = e.id), 0) total_dias_pedidos
        from ".$empresa.".vacaciones_empleado ve 
            join ".$empresa.".vacaciones_detalle vd on vd.id_solicitud = ve.id      
            join ".$empresa.".empleados e on e.id = ve.id_empleado
            join ".$empresa.".estatus_vacaciones ev on ev.id = ve.estatus_solicitud
            join ".$empresa.".tipo_solicitud_vacaciones ts on ts.id = ve.tipo
            join ".$empresa.".puestos p on p.id = e.id_puesto
            where ve.estatus_solicitud in (1,2) and vd.estatus = 1 group by ve.id order by ve.fecha_solicitud asc;";
        
        $datos_solicitud =   DB::connection('singh')->select($query_datos_solicitud);
      
        // return $datos_solicitud;
        return view('empleados_admin.vacaciones.inicio', compact('datos_solicitud', 'id_empresa'));
    }

    public function actualiza_estatus(Request $request)
    {    
        $empresa = Session::get('base');
        $fecha_actual = now()->format('Y-m-d');
        
        //ACTUALIZA vacaciones_detalle
        $affected = DB::table($empresa.'.vacaciones_detalle') 
            ->where('estatus', 1)  
            ->where('fechas_solicitud', '<=', $fecha_actual)             
            ->update(['estatus' => 0 ]);
        
        // ACTUALIZAR vacaciones_empleado
        DB::statement('update '.$empresa.'.vacaciones_empleado ve set ve.estatus_solicitud = 4 where estatus_solicitud in (1,2) and not exists
        (select * from '.$empresa.'.vacaciones_detalle vd where vd.id_solicitud = ve.id and vd.estatus = 1 );');     
            
        return 'ESTATUS_ACTUALIZADO_OK';
    }

    public function datos_fechas(Request $request)
    {
        $id_empresa = Session::get('empresa')['id'];
        $empresa = Session::get('base');
        $array_datos_fechas = array();
        $fecha_actual = now()->format('Y-m-d');        

        // $query_datos_fechas = "select d.id, d.id_empleado, em.nombre, em.apaterno, em.amaterno, d.fechas, d.tipo, d.estatus_solicitud, d.nota, 
        // d.usuario_autoriza, d.fecha_inicio, d.fecha_fin, d.total_dias
        // from ".$empresa.".detalle_vacaciones d 
        // join ".$empresa.".empleados em on d.id_empleado = em.id
        // join ".$empresa.".estatus_vacaciones e on d.estatus_solicitud = e.id
        // join ".$empresa.".tipo_solicitud_vacaciones t on d.tipo = t.id where d.estatus_solicitud in (1,2);";		

        $query_datos_fechas = "select ve.id, e.id id_empleado, ve.total_dias, ve.fecha_solicitud, group_concat(vd.fechas_solicitud) fechas_solicitud, e.nombre, e.apaterno, e.amaterno, 
            ve.estatus_solicitud, ev.descripcion nombre_estatus_solicitud, ve.tipo, vd.periodo, ve.usuario_autoriza, ts.descripcion tipo_solicitud_mayuscula, ve.nota, ve.file_solicitud
            from ".$empresa.".vacaciones_empleado ve 
            join ".$empresa.".vacaciones_detalle vd on vd.id_solicitud = ve.id      
            join ".$empresa.".empleados e on e.id = ve.id_empleado
            join ".$empresa.".estatus_vacaciones ev on ev.id = ve.estatus_solicitud
            join ".$empresa.".tipo_solicitud_vacaciones ts on ts.id = ve.tipo 
            where ve.estatus_solicitud in (1,2) and vd.estatus = 1 group by ve.id order by ve.fecha_solicitud asc";
       
		$datos_fechas = DB::connection('singh')->select($query_datos_fechas);

        // return $datos_fechas; exit;

        foreach ($datos_fechas as $key => $value) {   
            $url_archivo = public_path().'/repositorio/'.$id_empresa.'/'.$value->id_empleado.'/vacaciones/'.$value->file_solicitud;
            $url_archivo2 = '';
            $nombre_archivo = $value->file_solicitud;
            //VALIDAR SI EXISTE EL ARCHIVO DE SOLICITUD
            if( file_exists($url_archivo) && is_file( $url_archivo) ){
                $url_archivo2 = '../public/repositorio/'.$id_empresa.'/'.$value->id_empleado.'/vacaciones/'.$value->file_solicitud;
            }else{
                $url_archivo = '';
                $url_archivo2 = '';
                $nombre_archivo = '';
            }        

            // $array_fechas = json_decode($value->fechas_solicitud, true);
            $array_fechas = explode(',', $value->fechas_solicitud);      
            $estatus_solicitud = $value->estatus_solicitud;
            $nombre_empleado = $value->nombre.' '.$value->apaterno.' '.$value->amaterno;
            // $fecha_solicitud = $value->fecha_inicio;
            // $fecha_inicio = $value->fecha_inicio;
            $fecha_solicitud = null;
            $total_dias = $value->total_dias;
            $color_fecha = "";
            $estatus = "";

            // $fecha = strtotime($fecha_inicio."+ 0 days");
            //     echo $fecha_inicio, '--', date("Y-m-d",$fecha), '<br>';
            //     exit;

            // LAS SOLICITUDES AUTORIZADAS NO SE PERMITEN EDITAR
            if($estatus_solicitud == 1){
                $color_fecha = "#00a65a";
                $estatus = "(Autorizado)";

                /*for($f=0; $f<$total_dias; $f++) {
                    $fecha_solicitud = date("Y-m-d", strtotime($fecha_inicio."+ $f days"));                               

                    if($fecha_solicitud == $fecha_actual){
                        $color_fecha = "#00a65a"; // FECHA ACTUAL
                    }else if($fecha_solicitud > $fecha_actual){
                        $color_fecha = "#3c8dbc"; // FECHA PRÓXIMA
                    }else if($fecha_solicitud < $fecha_actual){
                        $color_fecha = "#dd4b39"; // FECHA VENCIDA
                    }         
                    $array_datos_fechas[] = [                    
                        'title' => $nombre_empleado,
                        'start' => $fecha_solicitud,
                        'allDay' => 1,
                        // 'url' => 'javascript:void(0)',
                        'backgroundColor' => $color_fecha,
                        'borderColor' => $color_fecha,
                        'description' => $nombre_empleado.' '.$estatus,
                    ];
                }*/

                foreach ($array_fechas as $key2 => $f) {
                    // echo $f, '--', $value->nombre, '<br>';    
                    if($f == $fecha_actual){
                        $color_fecha = "#00a65a";
                    }else if($f > $fecha_actual){
                        $color_fecha = "#3c8dbc";
                    }else if($f < $fecha_actual){
                        $color_fecha = "#dd4b39";
                    }         
                    $array_datos_fechas[] = [                    
                        'title' => $nombre_empleado,
                        'start' => $f,
                        'allDay' => 1,
                        // 'url' => 'javascript:void(0)',
                        'backgroundColor' => $color_fecha,
                        'borderColor' => $color_fecha,
                        'description' => $nombre_empleado.' '.$estatus,
                    ];
                }
            }else{ // VALIDAR ESTATUS DE LA SOLICITUD, PARA EDITAR SOLO LOS QUE SE ENCUENTREN PENDIENTES
                $color_fecha = "#f39c12";
                $estatus = "(Pendiente Autorizar)";

                /*for($f=0; $f<$total_dias; $f++) {
                    $fecha_solicitud = date("Y-m-d", strtotime($fecha_inicio."+ $f days"));

                    $array_datos_fechas[] = [
                        'id' => $value->id,
                        'id_empleado' => $value->id_empleado,
                        'tipo' => $value->tipo,
                        'autoriza' => $value->usuario_autoriza,
                        // 'fechas' => $value->fechas,
                        'nota' => $value->nota,
                        'title' => $nombre_empleado,
                        'start' => $fecha_solicitud,
                        'allDay' => 1,
                        'url' => 'javascript:void(0)',
                        'backgroundColor' => $color_fecha,
                        'borderColor' => $color_fecha,
                        'description' => $nombre_empleado.' '.$estatus,
                    ];
                }*/

                foreach ($array_fechas as $key2 => $f) {
                    // echo $f, '--', $value->nombre, '<br>';                
                    $array_datos_fechas[] = [
                        'id' => $value->id,
                        'id_empleado' => $value->id_empleado,
                        'tipo' => $value->tipo,                        
                        'autoriza' => $value->usuario_autoriza,
                        'url_archivo' => $url_archivo,
                        'url_archivo2' => $url_archivo2,
                        'file_solicitud' => $nombre_archivo,
                        'tipo_solicitud_mayuscula' => $value->tipo_solicitud_mayuscula,
                        // 'fechas' => $value->fechas,
                        'nota' => $value->nota,
                        'title' => $nombre_empleado,
                        'start' => $f,
                        'allDay' => 1,
                        'url' => 'javascript:void(0)',
                        'backgroundColor' => $color_fecha,
                        'borderColor' => $color_fecha,
                        'description' => $nombre_empleado.' '.$estatus,
                        'periodo' => $value->periodo,
                    ];
                }
            }

            
        }

        return $array_datos_fechas;
    }
    public function guardar(Request $request){
        //obtenemos el campo file definido en el formulario
        $archivo = $request->file('file');
        $tipo_solicitud_descripcion = ''; 
        $actualizar_archivo_solicitud = $request->actualiza_archivo;
        $tipo_solicitud = $request->tipo_solicitud_nombre;   

        $usuario_aut1 = $request->txtAutoriza1;
        $usuario_aut2 = $request->txtAutoriza2;
        $usuario_aut3 = $request->txtAutoriza3;
        $array_autoriza = array();

        if(trim($usuario_aut1) !== ''){
            $array_autoriza[] = $usuario_aut1;
        }
        if(trim($usuario_aut2) !== ''){
            $array_autoriza[] = $usuario_aut2;
        }
        if(trim($usuario_aut3) !== ''){
            $array_autoriza[] = $usuario_aut3;
        }
        $empresa = Session::get('base');     

        $fecha_inicio = $request->fechainicio;
        $fecha_fin = $request->fechafin;
        $id_empleado = $request->empleado == '' ? $request->id_empleado_edit : $request->empleado;
        $tipo = $request->tipo;
        $periodo = $request->periodo;
        $fecha_vac = base64_decode($request->fechas_datepicker);
        $array_fechas = json_decode($fecha_vac);
        // $usuario_autoriza = $request->autoriza_solicitud;
        $usuarios_autoriza = $request->empleados_autoriza;
        $id_editar = $request->id_editar;
        $nota = $request->textAreaNota;

        if(trim($id_empleado)=='' || trim($tipo)=='' || trim($fecha_vac)=='' || count($array_autoriza)==0){
            // exit;
            session()->flash('danger', 'Ingrese todos los datos necesarios.');
            return redirect()->route('empleados.calendario');
        }         
    
        $fecha1 = new \DateTime($array_fechas[0]);
        $fecha2 = new \DateTime(end($array_fechas));
        $diff = $fecha1->diff($fecha2);
        $dif_dias = ($diff->days)+1;
        $total_dias = count($array_fechas);     
        // $estatus_solicitud = 0;    

        //EDITAR SOLICITUD
        if($id_editar > 0){
            if( file_exists($actualizar_archivo_solicitud) && is_file($actualizar_archivo_solicitud) ){
                // return $request->actualiza_archivo;
                File::delete($actualizar_archivo_solicitud);                
            }
            if(trim($archivo) != ''){
                $tipo_solicitud_descripcion = $this->guardar_archivo($archivo, $tipo_solicitud, $empresa, $id_empleado, $id_editar);
            }
            // exit;
            $affected = DB::table($empresa.'.vacaciones_empleado')                  
                  ->where('id', $id_editar)
                  ->where('id_empleado', $id_empleado)
                  ->where('estatus_solicitud', 2)
                      ->update([
                        'total_dias' => $total_dias,
                        'tipo' => $tipo,
                        'fecha_solicitud' => now(),
                        'usuario_autoriza' => json_encode($array_autoriza),  
                        'file_solicitud' => $tipo_solicitud_descripcion,                   
                        'nota' => $nota                                                                     
                ]);
            if($affected > 0){
                DB::table($empresa.'.vacaciones_detalle')
                ->where('id_solicitud', $id_editar)                
                ->delete();

                $data_vacaciones_detalle = []; 
                foreach ($array_fechas as $key => $f) {               
                    $data_vacaciones_detalle[] = [
                        'id_solicitud' => $id_editar,
                        'fechas_solicitud' => $f,
                        'estatus' => 1,
                        'periodo' => $periodo,
                    ];
                }
                DB::table($empresa.'.vacaciones_detalle')->insert($data_vacaciones_detalle);
                return redirect()->route('empleados.calendario');
            }            
        }else{
            //VALIDAR SI EXISTE SOLICITUD VIGENTE DEL USUARIO
            /*$query_validar_solicitud = "select ve.id, ve.id_empleado, ve.tipo, ve.estatus_solicitud, ve.confirmado from ".$empresa.".vacaciones_empleado ve where estatus_solicitud in (1,2) and exists
            (select * from ".$empresa.".vacaciones_detalle vd where vd.id_solicitud = ve.id and vd.estatus = 1 ) and ve.id_empleado = ".$id_empleado." and ve.confirmado = 0;";        
            $validar_solicitud = DB::connection('singh')->select($query_validar_solicitud);            
            foreach ($validar_solicitud as $key => $item) {
                $estatus_solicitud = $item->estatus_solicitud;
            }
            if($estatus_solicitud > 0){
                return redirect('empleados/calendario');
            }*/

            $id_vacaciones_empleado = (DB::connection('singh')->table($empresa.".vacaciones_empleado")->max('id'))+1; 
                
            if(trim($archivo) != ''){
                $tipo_solicitud_descripcion = $this->guardar_archivo($archivo, $tipo_solicitud, $empresa, $id_empleado, $id_vacaciones_empleado);
            }
        
            $data_vacaciones_empleado = [ 
                [
                    'id' => $id_vacaciones_empleado,
                    'id_empleado' => $id_empleado, 
                    'total_dias' => $total_dias, 
                    'tipo' => $tipo, 
                    'fecha_solicitud' => now(), 
                    'estatus_solicitud' => 2, 
                    'usuario_autoriza' => json_encode($array_autoriza),
                    'file_solicitud' => $tipo_solicitud_descripcion,
                    'nota' => $nota,
                ],         
            ];

            // INSERTA NUEVA SOLICITUD
            if(DB::table($empresa.'.vacaciones_empleado')->insert($data_vacaciones_empleado)){                 
                $data_vacaciones_detalle = []; 
                foreach ($array_fechas as $key => $f) {               
                    $data_vacaciones_detalle[] = [
                        'id_solicitud' => $id_vacaciones_empleado,
                        'fechas_solicitud' => $f,
                        'estatus' => 1,
                        'periodo' => $periodo,
                    ];
                }
                DB::table($empresa.'.vacaciones_detalle')->insert($data_vacaciones_detalle);            
            }         
            return redirect()->route('empleados.calendario');
        }

        /*if(count($array_fechas) == $dif_dias){
            $this->inserta_registros_solicitud($empresa, $id_empleado, json_encode($array_autoriza), $fecha1, $fecha2, $total_dias, $tipo, $nota);
        }else{
            foreach ($array_fechas as $key => $f) {
                $this->inserta_registros_solicitud($empresa, $id_empleado, json_encode($array_autoriza), $f, $f, 1, $tipo, $nota);
            }
        } */
        
        /*if($id_editar > 0){
            // ACTUALIZA La TABLA detalle_vacaciones
            $affected = DB::table($empresa.'.detalle_vacaciones')                  
                ->where('id', $id_editar)
                ->where('id_empleado', $id_empleado)
                    ->update([
                        'id_empleado' => $id_empleado,
                        'usuario_autoriza' => $usuario_autoriza,
                        'fechas' => $fecha_vac,
                        'tipo' => $tipo,
                        'nota' => $request->textAreaNota,                                                      
                    ]);

        }else{        
            DB::table($empresa.'.detalle_vacaciones')->insert([                
                'id_empleado' => $id_empleado,
                'usuario_autoriza' => $usuario_autoriza,
                'fecha_solicitud' => now(), 
                'fechas' => $fecha_vac,    
                'estatus_solicitud' => 2, 
                'tipo' => $tipo,
                'nota' => $request->textAreaNota,            
            ]);
            
        }*/        
    }

    public function guardar_archivo($archivo, $tipo_solicitud, $empresa, $id_empleado, $id_vacaciones_empleado)
    {
        $id_empresa = Session::get('empresa')['id'];        
       
        $tipo_solicitud = str_replace(
                array('Á', 'á', 'É', 'é', 'Í', 'í', 'Ó', 'ó', 'Ú', 'ú'),
                array('A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u'),
                $tipo_solicitud);
        // echo $tipo_solicitud; exit;

        // $archivo = $request->file('file');
        $nombre_archivo = $archivo->getClientOriginalName();

        $info = new \SplFileInfo($nombre_archivo);

        $url_repositorio ='public/repositorio/'.$id_empresa.'/'.$id_empleado.'/vacaciones';
        $url_valido ='repositorio/'.$id_empresa.'/'.$id_empleado.'/vacaciones';
        // echo $ruta_repositorio; exit;

        /*if(!File::exists($url_valido)) {
            File::makeDirectory($url_valido, $mode = 0755, true, true);
        } */
        
    	$renombrado = implode('.', [$tipo_solicitud.'_'.$id_empleado.'_'.$id_vacaciones_empleado, strtolower($info->getExtension()) ]);        
        //$archivo->move($url_repositorio, $renombrado);
        $archivo->storeAs ($url_repositorio,$renombrado);
        Storage::disk('public')->makeDirectory($url_valido,$mode = 0755, true, true);
        return $renombrado;
    }

    public function validar_solicitud_empleado(Request $request)
    {      
        $empresa = Session::get('base');
        $id_empleado = request('id_empleado');
          
        
        /*$query_validar_solicitud = "select ve.id, ve.id_empleado, ve.tipo, ve.estatus_solicitud, ve.confirmado from ".$empresa.".vacaciones_empleado ve where estatus_solicitud in (1,2) and exists
        (select * from ".$empresa.".vacaciones_detalle vd where vd.id_solicitud = ve.id and vd.estatus = 1 ) and ve.id_empleado = ".$id_empleado.";";*/
        
        //VALIDAR SI EXISTE SOLICITUD VIGENTE DEL USUARIO
        $validar_solicitud =[];
        
        if(!empty($id_empleado)){

            $query_validar_solicitud ="SELECT ve.id, ve.id_empleado, ve.tipo, ve.estatus_solicitud, ve.confirmado
                    FROM $empresa.vacaciones_empleado AS ve WHERE estatus_solicitud IN(1,2) AND EXISTS 
                    (SELECT * FROM $empresa.vacaciones_detalle AS vd WHERE vd.id_solicitud = ve.id AND vd.estatus = 1 )
                    AND ve.id_empleado = $id_empleado";

            $validar_solicitud = DB::connection('singh')->select($query_validar_solicitud);
        }
  
        return $validar_solicitud;
    }

    public function autoriza_solicitud(Request $request)
    {
        $empresa = Session::get('base');
        $id_solicitud = explode(",", request('id_solicitud'));
        $id_empleado = request('id_empleado');

        // echo $id_solicitud; exit;

        // ACTUALIZA La TABLA detalle_vacaciones
        $affected = DB::table($empresa.'.vacaciones_empleado')                  
                  ->whereIn('id', $id_solicitud)
                  ->where('id_empleado', $id_empleado)
                  ->where('estatus_solicitud', 2)
                      ->update([
                        'confirmado' => 1,
                        'estatus_solicitud' => 1,
                        // 'usuario_confirma' => 'xx',
                        'fecha_autoriza' => now()
                        // 'respuesta_pac' => $respuestaPaccon,
                                                
                ]);
        return 'autorizado_ok';
        // return redirect('empleados/vacaciones');
    }
    
    public function cancela_solicitud(Request $request){
        // return 'cancela';
        $empresa = Session::get('base');      
        $id_solicitud = explode(",", request('id_solicitud'));
        $id_empleado = request('id_empleado');
        $motivo_cancelacion = request('motivo_cancelacion');
        // echo $id_solicitud, '--', $id_empleado; exit;

        // ACTUALIZA La TABLA detalle_vacaciones
        $affected = DB::table($empresa.'.vacaciones_empleado')                  
                  ->where('id', $id_solicitud)
                  ->where('id_empleado', $id_empleado)
                  ->where('estatus_solicitud', 2)
                      ->update([                      
                        'estatus_solicitud' => 3,                     
                        'fecha_autoriza' => now(),
                        'nota' => $motivo_cancelacion                                                
                ]);
        if($affected > 0){
            $affected = DB::table($empresa.'.vacaciones_detalle')
                  ->where('id_solicitud', $id_solicitud)
                  ->where('estatus', 1)
                      ->update([                      
                        'estatus' => 0,                               
                ]);
        }
        // return 'autorizado_ok';
        // return $affected; exit;
        // return redirect('empleados/vacaciones');
    }

    public function controlVacaciones(Request $request){   
        $empresa = Session::get('base');
        cambiarBase(Session::get('base'));
        $datos_empleados = Empleado::all()->where('estatus',1);

        $datos_empleados = DB::table($empresa.'.empleados')
            ->select('empleados.id',
                    'empleados.nombre',
                    'empleados.apaterno',
                    'empleados.amaterno',
                    DB::raw('ifnull((select count(vd.fechas_solicitud) dias_solicitud from '.$empresa.'.vacaciones_empleado ve join '.$empresa.'.vacaciones_detalle vd on vd.id_solicitud = ve.id where ve.id_empleado=empleados.id and ve.estatus_solicitud = 1 and vd.estatus = 1 and ve.confirmado = 1 and year(vd.fechas_solicitud)=year(curdate()) and ve.tipo=1 group by ve.id_empleado), 0) total_dias_vac_pedidos,
                    ifnull((select sum(vp.dias_vacaciones_pagadas) from '.$empresa.'.vacaciones_pagadas vp where vp.id_empleado=empleados.id and vp.periodo=year(curdate())), 0) total_dias_vac_pagados, 
                    ifnull((select p.vacaciones from '.$empresa.'.prestaciones p where p.estatus=1 and p.id_categoria=empleados.id_categoria and p.antiguedad = TIMESTAMPDIFF(year, empleados.fecha_antiguedad, CURDATE()) ), 0) dias_vacaciones_prestaciones,
                    ifnull((select count(vd.fechas_solicitud) dias_solicitud from '.$empresa.'.vacaciones_empleado ve join '.$empresa.'.vacaciones_detalle vd on vd.id_solicitud = ve.id where ve.id_empleado=empleados.id and ve.estatus_solicitud = 1 and vd.estatus = 1 and ve.confirmado = 1 and year(vd.fechas_solicitud)=year(curdate()) and ve.tipo=2 group by ve.id_empleado), 0) total_perm_dias_pedidos,
                    ifnull((select sum(vp.dias_permisos_pagados) from '.$empresa.'.vacaciones_pagadas vp where vp.id_empleado=empleados.id and vp.periodo=year(curdate())), 0) total_dias_perm_pagados'),               
            )->get();  

        // $datos_periodo = DB::table($empresa.'.empleados')
        //     ->select(
        //         'empleados.id',
        //         'empleados.nombre',
        //         'empleados.apaterno',
        //         'empleados.amaterno',
        //         DB::raw('year(vacaciones_detalle.fechas_solicitud) as periodo')
        //     )
        //     ->join($empresa.'.vacaciones_empleado', 'vacaciones_empleado.id_empleado','=','empleados.id')
        //     ->join($empresa.'.vacaciones_detalle','vacaciones_detalle.id_solicitud','=','vacaciones_empleado.id')          
        //     ->distinct()->orderBy('periodo', 'desc')->get();       
		
        // return $datos_e;
        return view('empleados_admin.vacaciones.control-vacaciones', compact('datos_empleados'));
    }
}
