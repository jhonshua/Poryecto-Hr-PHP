<?php

namespace App\Http\Controllers\consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodosNomina;

class RecibosNominaEmpleado extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function inicio(){
        $base = Session::get('base');
        cambiarBase($base);     
   
        $periodos = PeriodosNomina::orderBy("id",'desc')->where('estatus',1)->where('activo',2)->get();
        return view('consultas.recibos-nomina-empleados.inicio',compact('periodos'));
    }

    public function timbrarNominaPeriodo($periodo){
        try{
            $base = Session::get('base');
            cambiarBase($base);     

            $repo = Session::get('empresa')['id'];
            
            /* periodo */
            $periodo = PeriodosNomina::find($periodo);
            $id_periodo  = $periodo->id;

            $tipo_nomina = $periodo->nombre_periodo;
            $nombre_periodo = $periodo->nombre_periodo;
            $ejercicio = $periodo->ejercicio;
            $empleados = array();
            $query = "SELECT *
                    FROM empleados
                    WHERE estatus = 1
                    AND tipo_de_nomina = '$tipo_nomina'
                    AND id IN (
                        SELECT id_empleado 
                        FROM rutinas$ejercicio 
                        WHERE id_periodo = '$id_periodo' 
                        AND fnq_valor = 0 
                        AND neto_fiscal > 0);
                    ";
            $emple = DB::connection('empresa')->select($query);   

            /* Verificamos si ya existen timbrados para el periodo */
            $r = DB::connection('empresa')
                        ->table('timbrado')
                        ->where('id_periodo',$id_periodo)
                        ->where('sello_sat', '<>', 'error')
                        ->get()
                        ->count();
                $existen_timbrados = $r; 
            
                
            /* PROCESAMOS LOS EMPLEADOS */                  
            foreach($emple as $e){
                $query = "SELECT neto_fiscal
                        FROM rutinas$ejercicio
                        WHERE id_empleado = $e->id
                        AND id_periodo = $id_periodo
                        AND fnq_valor = 0;";
                /* Sacamos sus importe */
                $r = DB::connection('empresa')->select($query);   
                $e->importe_fiscal = round($r[0]->neto_fiscal,2);
                //$importeFiscal=round($rowresultFiscal['NetoFiscal'],2);
                
                /* 
                    01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                    $numRegistros -> original
                    Sacamos tambien el estatus del timbre:
                    0 = ?
                    1 = Timbrado
                    2 = Error
                */
                $query2="SELECT * 
                        FROM timbrado 
                        WHERE id_empleado = '$e->id' 
                        AND id_periodo = '$id_periodo' 
                        AND estatus_timbre = 1";
                $r = DB::connection('empresa')->select($query2);   
                $e->timbres = $r;

                /*
                02.- Si hay timbres no error //Normalmente es 1
                $numRegistrosreTimbre -> original
                */
                $r = DB::connection('empresa')
                        ->table('timbrado')
                        ->where('id_empleado',$e->id)
                        ->where('id_periodo',$id_periodo)
                        ->where('sello_sat', '<>', 'error')
                        ->get()
                        ->count();
                $e->numero_timbres_noerror = $r;            
                /* 
                03.- traemos el ultimo registro de  timbre 
                $numRegistrosreTimbreError -> original
                */
                $r = DB::connection('empresa')
                ->table('timbrado')
                ->where('id_empleado',$e->id)
                ->where('id_periodo',$id_periodo)
                ->orderBy('id','desc')
                ->first();
                $e->ultimo_timbre = $r; 

                /* 
                04.-timbres cancelados 
                */
                $r = DB::connection('empresa')
                        ->table('timbrado_cancelaciones')
                        ->where('id_empleado', $e->id)
                        ->where('id_periodo', $id_periodo)
                        ->get();
                $e->timbres_cancelados = $r;
                

                $empleados[] = $e;
            }

            $cadena_departamentos = 0;
            $tipo = 1;     
            $regresar = 'R';
            return view('procesos.timbrado-nomina.nomina-lista', compact('periodo','empleados','cadena_departamentos','existen_timbrados','repo','tipo', 'regresar'));           
        }catch(\Exception $e) {
            //dd($e);
        }
    }

    public function timbrarNominaPeriodocerrado($periodo){
        $base = Session::get('base');
        cambiarBase($base);     

        $repo = Session::get('empresa')['id'];
        
        /* periodo */
        $periodo = periodosNomina::find($periodo);
        $id_periodo  = $periodo->id;

        $tipo_nomina = $periodo->nombre_periodo;
        $nombre_periodo = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;
        $empleados = array();
        $query = "SELECT *
                  FROM empleados
                    WHERE estatus in (1,2)
                    AND tipo_de_nomina = '$tipo_nomina'
                  AND id IN (
                      SELECT id_empleado 
                      FROM rutinas$ejercicio 
                      WHERE id_periodo = '$id_periodo' 
                      AND fnq_valor = 0 
                      AND neto_fiscal > 0 and id_empleado in (select id_empleado from timbrado where id_periodo='$id_periodo'));
                  ";
                  //dd($query);
        $emple = DB::connection('empresa')->select($query);   

        /* Verificamos si ya existen timbrados para el periodo */
        $r = DB::connection('empresa')
                     ->table('timbrado')
                     ->where('id_periodo',$id_periodo)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();
            $existen_timbrados = $r; 
        
            
        /* PROCESAMOS LOS EMPLEADOS */                  
        foreach($emple as $e){
            $query = "SELECT neto_fiscal
                      FROM rutinas$ejercicio
                      WHERE id_empleado = $e->id
                      AND id_periodo = $id_periodo
                      AND fnq_valor = 0;";
            /* Sacamos sus importe */
            $r = DB::connection('empresa')->select($query);   
            $e->importe_fiscal = round($r[0]->neto_fiscal,2);
            //$importeFiscal=round($rowresultFiscal['NetoFiscal'],2);
            
            /* 
                01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                $numRegistros -> original
                Sacamos tambien el estatus del timbre:
                0 = ?
                1 = Timbrado
                2 = Error
            */
            $query2="SELECT * 
                     FROM timbrado 
                     WHERE id_empleado = '$e->id' 
                     AND id_periodo = '$id_periodo' 
                     AND estatus_timbre = 1";
            $r = DB::connection('empresa')->select($query2);   
            $e->timbres = $r;

            /*
              02.- Si hay timbres no error //Normalmente es 1
              $numRegistrosreTimbre -> original
            */
            $r = DB::connection('empresa')
                     ->table('timbrado')
                     ->where('id_empleado',$e->id)
                     ->where('id_periodo',$id_periodo)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();
            $e->numero_timbres_noerror = $r;            
            /* 
               03.- traemos el ultimo registro de  timbre 
               $numRegistrosreTimbreError -> original
            */
            $r = DB::connection('empresa')
            ->table('timbrado')
            ->where('id_empleado',$e->id)
            ->where('id_periodo',$id_periodo)
            ->orderBy('id','desc')
            ->first();
            $e->ultimo_timbre = $r; 

            /* 
              04.-timbres cancelados 
            */
            $r = DB::connection('empresa')
                    ->table('timbrado_cancelaciones')
                    ->where('id_empleado', $e->id)
                    ->where('id_periodo', $id_periodo)
                    ->get();
             $e->timbres_cancelados = $r;
            

            $empleados[] = $e;
        }

        $cadena_departamentos = 0;
        $tipo = 1;
        $regresar = 'R';
        return view('consultas.recibos-nomina-empleados.lista-nomina-cerrada',compact('periodo','empleados','cadena_departamentos','existen_timbrados','repo','tipo', 'regresar'));

    }
}
