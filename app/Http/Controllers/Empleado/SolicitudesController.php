<?php

namespace App\Http\Controllers\Empleado;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\EmpleadoProduccion;
use App\Events\PrestamoCreado;
use App\Models\PrestamosTipos;
use App\Models\PrestamosRequisitos;
use App\Models\Empresa;
use App\Models\Empleado;
use App\Models\Prestamo;

class SolicitudesController extends Controller
{
    public function inicio(){
        return view('empleados.empleado.solicitudes');
    }
    public function obtenerSolicitudes(){
        
        $prestamos = Prestamo::join('prestamos_tipos', 'prestamos.prestamos_tipo_id', '=', 'prestamos_tipos.id')
                            ->select('prestamos.*', 'prestamos.id AS pid', 'prestamos_tipos.id as prestamo_tipo_id', 'prestamos_tipos.nombre')
                            ->where('prestamos.estatus', '!=', Prestamo::PRESTAMO_BORRADO)
                            ->where('prestamos.empleado_id', Session::get('empleado')['id'])
                            ->orderBy('prestamos.id', 'desc')
                            ->get();
        $data=[];
 
        foreach($prestamos as $prestamo ){

            $estatus_nombre="";
            if($prestamo->estatus == 1){
                $estatus_nombre='<span class="label label-rouded label-success pull-right"><strong>Abierto</strong></span>';
            }elseif($prestamo->estatus == 3){
                $estatus_nombre='<span class="label label-rouded label-error pull-right"><strong>Rechazado</strong></span>';
            }elseif($prestamo->estatus == 4){
                $estatus_nombre='<span class="label label-rouded label-warning pull-right"><strong>En proceso de revisión</strong></span>';
            }elseif($prestamo->estatus == 0){
                $estatus_nombre='<span class="label label-rouded label-secondary pull-right"><strong>Cerrado</strong></span>';
            }
            
            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"); 
            
            $fecha_creacion = Carbon::parse($prestamo->fecha_creacion);
           
            $mes_creacion = $meses[($fecha_creacion->format('n')) - 1];
            $mes_cierre="";
            if(!empty($prestamo->fecha_cierre)){
                $fecha_cierre = Carbon::parse($prestamo->fecha_cierre); 
                $mes_cierre = $meses[($fecha_cierre->format('n')) - 1];
                $mes_cierre =$fecha_cierre->format('d') . ' de ' . $mes_cierre . ' de ' . $fecha_cierre->format('Y');
            }else{
                $mes_cierre='<span class="label label-rouded label-error pull-right"><strong>Aún sin cerrar</strong></span>';
            }
            
            $data[]=array('0'=>$prestamo->nombre, 
                          '1'=>$prestamo->medio_contacto,
                          '2'=>$fecha_creacion->format('d') . ' de ' . $mes_creacion . ' de ' . $fecha_creacion->format('Y'),
                          '3'=>$estatus_nombre,
                          '4'=>'<a href='.route("empleado.obtenerSolicitudes.detalles.prestamo",encrypt($prestamo->pid)).'><div type="button" class="btn btn-warning btn-sm"><span><i class="fas fa-book" data="toggle" title="Ver mi solicitud " ></i></span></div></a>'
              
                        );
        }
        $results=array(
            "sEcho"=>1, //informacion para date tables
            "iTotalRecords"=>count($data),//Enviamos el total de registros en datatable
            "iTotalDisplayRecords"=>count($data), //Enviamos el total de registros a vizualisar
            "aaData"=>$data,
        );
        return response()->json($results);
    }
    public function obtenerDetallesDelPrestamo($id){

        try{
            $prestamo = Prestamo::find(decrypt($id));
            $prestamo->load('empresa', 'tipoPrestamo', 'usuario');
            $prestamo->tipoPrestamo->load('requisitos');
        
            $tiposPrestamos = PrestamosTipos::with('requisitos')->get();
            $empresa = Empresa::find($prestamo->empresa_id);
            $empleado = new EmpleadoProduccion();
            $empleado = $empleado->obtenerEmpleadoPorID($empresa->base, $prestamo->empleado_id);

            return view('empleados.empleado.detallesSolicitudes',compact('prestamo', 'tiposPrestamos', 'empleado','empresa'));
        }catch(\Exception $e) {
            
            return redirect()->route('empleado.login');
        }
    }
}
