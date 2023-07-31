<?php

namespace App\Http\Controllers\procesos;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Parametros;
use App\Exports\AguinaldoExport;
use App\Imports\AguinaldoImport;

class CalculoAguinaldoController extends Controller
{
    public function inicio()
    {
        tienePermiso('aguinaldo');
        cambiarBase(Session::get('base'));        
        $deptos_asignados = Session::get('usuarioDepartamentos');        
        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get();   
        return view('procesos.calculo-aguinaldo.inicio', compact('departamentos'));
    }

    public function precalculoAguinaldo(Request $request)
    {
        tienePermiso('aguinaldo');
        cambiarBase(Session::get('base'));

        // $parametros_empresa = Parametros::first();        
        // return $request->impuestoanual;

        $deptos = $request->deptos;
        $ejercicio = $request->ejercicio;
        $impuestoanual = $request->impuestoanual;

        $aguinaldos = $this->obtenerAguinaldos($deptos, $ejercicio);

        if($aguinaldos->count() <= 0){
            $impuestos = DB::connection('empresa')->table('impuestos')->select('limite_inferior')->where('tipo_tabla', 'Anual')->get();
            if($impuestos->count() <= 0){
                $request->session()->forget('success');
                session()->flash('danger', 'Debe cargar las Tablas de Impuesto y Subsidios Anuales para continuar.'); 
                return redirect()->route('procesos.calculo.aguinaldo');
            }               
            
            $this->preCalcularAguinaldos($request);
            $aguinaldos = $this->obtenerAguinaldos($deptos, $ejercicio);
        }

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                                ->whereIn('id', $aguinaldos->pluck('id_empleado')->toArray())
                                ->whereIn('id_departamento', $deptos)
                                ->orderBy('id')
                                ->get();
                                $array1 = [];
        foreach($empleados as &$empleado){
            $empleado->aguinaldo = $aguinaldos[$empleado->id];
        }

        $sindical = DB::connection('empresa')->table('conceptos_nomina')->where('file_rool', 0)->where('estatus',1)->count();

        return view('procesos.calculo-aguinaldo.preaguinaldo', compact('empleados', 'sindical', 'deptos', 'ejercicio', 'impuestoanual'));
    }

    protected function obtenerAguinaldos($deptos, $ejercicio)
    {
        $aguinaldos = DB::connection('empresa')->table('aguinaldo')
                                    ->where('ejercicio', $ejercicio)
                                    ->whereIn('id_empleado', function($query) use($deptos){
                                        $query->select('id')
                                        ->from(with(new Empleado)->getTable())
                                        ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                                        ->whereIn('id_departamento', $deptos);
                                    })->orderBy('id_empleado')->get()->keyBy('id_empleado');
        return $aguinaldos;
    }

    protected function preCalcularAguinaldos($request)
    {
        // $periodo   = periodosNomina::where('activo', periodosNomina::ACTIVO)->where('estatus', periodosNomina::ESTATUS_DISPONIBLE)->first();
        // $ejercicio = ($periodo->ejercicio) ?: date('Y');
        // $idperiodo = $periodo->id;

        $ejercicio = $request->ejercicio;

        // $parametros_empresa = Session::get('empresa.parametros')[0];
        $parametros_empresa = Parametros::first();
        $uma                = $parametros_empresa['uma'];
        $validaajuste       = $request->impuestoanual;

        $isr = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('rutinas', 'ISR')->where('estatus', 1)->first();
        $idconcepISR = $isr->id;
        

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                                ->whereIn('id', function($query) use($ejercicio){
                                    $query->select('id_empleado')->from('rutinas'.$ejercicio)->distinct();
                                })
                                ->whereIn('id_departamento', $request->deptos)
                                ->orderBy('id')->get()->keyBy('id');

        $aguinaldos  = DB::connection('empresa')->table('aguinaldo')->where('ejercicio', $ejercicio)->where('fecha_fiscal', '<>', '0000-00-00')->get()->keyBy('id_empleado');
        $totales_isr = DB::connection('empresa')->table('rutinas'.$ejercicio)
                                                ->selectRaw('SUM(total'.$idconcepISR.') as total_isr, id_empleado')
                                                ->where('fnq_valor', 0)
                                                ->whereIn('id_empleado', $empleados->pluck('id')->toArray())
                                                ->groupBy('id_empleado')
                                                ->get()
                                                ->keyBy('id_empleado');

        $totales_gravado = DB::connection('empresa')->table('rutinas'.$ejercicio)
                                                ->selectRaw('SUM(total_gravado) as total_gravado, id_empleado')
                                                ->where('fnq_valor', 0)
                                                ->whereIn('id_empleado', $empleados->pluck('id')->toArray())
                                                ->groupBy('id_empleado')
                                                ->get()
                                                ->keyBy('id_empleado');

        if($aguinaldos->count() > 0){
            foreach ($aguinaldos as $aguinaldo) {
                $empleados[$aguinaldo->id_empleado]->aguinaldo = $aguinaldo;
            }
        }

        foreach ($empleados as $empleado) {
            $PensionAlimenticia  = 0;
            $DescuestosOtros     = 0;
            $SPensionAlimenticia = 0;
            $SDescuestosOtros    = 0;
            $diasFiscalesReales=0;
            $parteGravadaFiscal = 0;
            $parteGravada=0;
            
            $valorisr = $totales_isr[$empleado->id]->total_isr;
            $añofechalta = date('Y', strtotime($empleado->fecha_antiguedad));

            $finicio = ($añofechalta < $ejercicio) ? $ejercicio.'-01-01' : $empleado->fecha_antiguedad;
            $inicio = Carbon::parse($finicio);
            $fin     = Carbon::parse($ejercicio.'-12-31');
            $dias   = $fin->diffInDays($inicio) + 1;

            //dump($empleado->id, $dias);

            if($empleado->aguinaldo){
                $PensionAlimenticia  = $empleado->aguinaldo->pension_alimenticia;
                $DescuestosOtros     = $empleado->aguinaldo->descuentos_otros;
                $SPensionAlimenticia = $empleado->aguinaldo->s_pension_alimenticia;
                $SDescuestosOtros    = $empleado->aguinaldo->s_descuentos_otros;

                $añoFechaFiscal     = date('Y', strtotime($empleado->aguinaldo->fecha_fiscal));

                $finicio1            = ($añoFechaFiscal < $ejercicio) ? $ejercicio.'-01-01' : $empleado->aguinaldo->fecha_fiscal;
                $inicio1            = Carbon::parse($finicio1);
                $fin1                = Carbon::parse($ejercicio.'-12-31');
                $diasFiscales       = $fin1->diffInDays($inicio1) + 1;
                $diasFiscalesReales = round((($diasFiscales * $empleado->dias_aguinaldo) / 365), 2);
                $aguiFiscal         = ($empleado->tipo_fiscal == 0) ? 0 : (floatval($empleado->salario_diario) * $diasFiscalesReales);
            }
            
            $diasagui           = ($dias * $empleado->dias_aguinaldo) / 365;
            $diasagui           = round($diasagui,2);
            $aguin2             = floatval($empleado->salario_diario) * $diasagui;
            $aguinaldo2         = ($empleado->tipo_fiscal == 0) ? 0 : round($aguin2, 2);
            // if($empleado->id == 76) dump($dias, $diasagui, $aguin2);
            

            $ano_antiguedad     = date('Y', strtotime($empleado->fecha_antiguedad));
            $ano                = $ejercicio - $ano_antiguedad;
            $ano                = ($ano == 0) ? 1 : $ano;
            //dump($diasagui, $aguin2, $aguinaldo2, $ano);

            $queryvaca = "SELECT * from prestaciones p join categorias c on p.id_categoria = c.id where c.id = '$empleado->id_categoria' and p.antiguedad='$ano' and p.estatus=1 and c.estatus=1"; 
            $vaca      = DB::connection('empresa')->select($queryvaca);
            if(isset($vaca[0])){
                $diasagui2 = $vaca[0]->bono_aguinaldo;
            }else{
                $diasagui2 = 0;
            }          

            if($empleado->salario_digital <= 0){
                $netoAguianual = 0;
                $importe       = 0;
            }else{
                $netoAguianual = $empleado->salario_digital*$diasagui;
                $importe       = round($netoAguianual - $aguinaldo2, 2);
            }
            if($empleado->tipo_sindical == 0){
                $importe=0;  
                }

            
            // if($empleado->id == 76) dump($netoAguianual, $aguinaldo2);

            $diasAguiReal = $diasagui;
            $totalGravado = $totales_gravado[$empleado->id]->total_gravado;
            //dump($netoAguianual, $importe, $diasAguiReal, $totalGravado, $uma);

            if($aguinaldo2 > ($uma * 30)){
                $parteGravada = $aguinaldo2 - ($uma * 30);
                $parteExenta  = $uma * 30;
            }else{
                $parteGravada = 0;
                $parteExenta  = $aguinaldo2;
            }

            if($empleado->aguinaldo){            
                if($aguiFiscal > ($uma*30) ){
                    $parteGravadaFiscal = $aguiFiscal - ($uma*30);
                    $parteExentaFiscal  = $uma * 30;
                }else{
                    $parteGravadaFiscal = 0;
                    $parteExentaFiscal  = $aguiFiscal;
                }
                if($parteGravadaFiscal<>0){

                    $querylimitInferiorFiscal = "SELECT limite_inferior, porcentaje, cuota_fija from impuestos where $parteGravadaFiscal between limite_inferior and limite_superior";

                $impuestos_valores        = DB::connection('empresa')->select($querylimitInferiorFiscal);
                    $impuestos_valores        = $impuestos_valores[0];
                    $limitInferiorFiscal      = floatval($impuestos_valores->limite_inferior);
                    $PorcentajeAplicarFiscal  = floatval($impuestos_valores->porcentaje);
                    $CuotaFijaFiscal          = floatval($impuestos_valores->cuota_fija);
                } else {
                    $limitInferiorFiscal      = 0;
                    $PorcentajeAplicarFiscal  = 0;
                    $CuotaFijaFiscal          = 0;
                }

                // $querycantidadSubsidioFiscal = "SELECT subsidio from subsidios where $parteGravadaFiscal between ingreso_desde and ingreso_hasta and tipo_tabla = '$empleado->tipo_de_nomina'";
                // $subsidio_valor              = DB::connection('generica')->select($querycantidadSubsidioFiscal);
                // $subsidio_valor              = $subsidio_valor[0];
                // $CantidadSubsidioFiscal      = $subsidio_valor->subsidio;

                $ingresoExceFiscal      = $parteGravadaFiscal - $limitInferiorFiscal;
                $impuestoMarginalFiscal = $ingresoExceFiscal * ($PorcentajeAplicarFiscal/100);
                $isrretenerFiscal       = $impuestoMarginalFiscal + $CuotaFijaFiscal;
                $impuestoCaFiscal       = $isrretenerFiscal;
                $impuestoCargoFiscal    = ($empleado->tipo_fiscal==0) ? 0 : round($impuestoCaFiscal, 2);
            }

            if($parteGravada<>0){
                if(Session::get('usuarioPermisos')['id_usuario']==64){
                //dump($parteGravada);
                }
                $querylimitInferior = "SELECT limite_inferior, porcentaje, cuota_fija from impuestos where '$parteGravada' between limite_inferior and limite_superior and tipo_tabla='Anual'";
                
                $impuestos_valores  = DB::connection('empresa')->select($querylimitInferior);
                $impuestos_valores  = $impuestos_valores[0];
                $limitInferior      = floatval($impuestos_valores->limite_inferior);
                $PorcentajeAplicar  = floatval($impuestos_valores->porcentaje);
                $CuotaFija          = floatval($impuestos_valores->cuota_fija);

                $ingresoExce      = $parteGravada - $limitInferior;
                $impuestoMarginal = $ingresoExce * ($PorcentajeAplicar/100);
                $isrretener       = $impuestoMarginal + $CuotaFija;
                $impuestoCa       = $isrretener;
                $impuestoCargo    = ($empleado->tipo_fiscal==0) ? 0 : round($impuestoCa,2);

                $totalGravado     = $totalGravado+$parteGravada; 
            }else{
                $ingresoExce      = 0;
                $impuestoMarginal = 0;
                $isrretener       = 0;
                $impuestoCa       = 0;
                $impuestoCargo    = 0;

                $totalGravado     = $totalGravado; 
            }   
            if($totalGravado<>0){
                $queryImAnual             = "SELECT limite_inferior, porcentaje, cuota_fija from impuestos where $totalGravado between limite_inferior and limite_superior and tipo_tabla='Anual'";
                $impuestos_valores        = DB::connection('empresa')->select($queryImAnual);
                $impuestos_valores        = $impuestos_valores[0];
                $limitInferiorImAnual     = $impuestos_valores->limite_inferior;
                $PorcentajeAplicarImAnual = $impuestos_valores->porcentaje;
                $CuotaFijaImAnual         = $impuestos_valores->cuota_fija;
            } else {
                $limitInferiorImAnual     = 0;
                $PorcentajeAplicarImAnual = 0;
                $CuotaFijaImAnual         = 0;
            }        
            $CantidadSubsidioImAnual = 0;

            //echo 'Límite Inferior:'.$limitInferior.'<br>';
            //echo 'Total Gravado Anual:'.$totalGravado.'<br>';
            $ingresoExceImAnual      = $totalGravado - $limitInferiorImAnual;
            //echo 'Excedente de Límite Inferior:'.$ingresoExce.'<br>';
            //echo 'Porcentaje Aplicar:'.$PorcentajeAplicar.'<br>';
            $impuestoMarginalImAnual = $ingresoExceImAnual * ($PorcentajeAplicarImAnual/100);
            //echo 'Impuesto Marginal:'.$impuestoMarginal.'<br>';
            //echo 'Cuota Fija:'.$CuotaFija.'<br>';
            $isrretenerImAnual       = $impuestoMarginalImAnual + $CuotaFijaImAnual;
            //echo 'IRS Retener:'.$isrretener.'<br>';
            //echo 'Subsidio al Empleo:'.$CantidadSubsidio.'<br>';
            $impuestoCaImAnual       = $isrretenerImAnual - $CantidadSubsidioImAnual;
            $impuestoCargoImAnual    = round($impuestoCaImAnual,2);

            $diferenciaImpuesto = ($request->impuestoanual == 1) ? ($impuestoCargoImAnual - $valorisr) : 0;
            $diferenciaImpuesto = ($diferenciaImpuesto < 0 || $empleado->tipo_fiscal==0) ? 0 : $diferenciaImpuesto;        

            if($empleado->aguinaldo){
                $parteGravada  = $parteGravadaFiscal;
                $aguinaldo2    = $aguiFiscal;
                $impuestoCargo = $impuestoCargoFiscal;
                $diasagui      = $diasFiscalesReales;
                $importe       = ($empleado->salario_digital > 0) ? round($netoAguianual-$aguinaldo2, 2) : $importe;
                if($empleado->tipo_sindical == 0){
                $importe=0;  
                }
            }

            $importeTotal  = ($importe+$aguinaldo2) - ($diferenciaImpuesto+$impuestoCargo+$SPensionAlimenticia+$SDescuestosOtros+$PensionAlimenticia+$DescuestosOtros);
            $Neto          = $aguinaldo2 - $diferenciaImpuesto - $impuestoCargo;
            $TotalFiscal   = $Neto - $PensionAlimenticia - $DescuestosOtros;
            $TotalSindical = $importe - $SPensionAlimenticia - $SDescuestosOtros;

            // if(Session::get('usuarioPermisos')['id_usuario']==64 && $empleado->id==5){
            //         //dd($diasFiscalesReales,$diasAguiReal,$empleado->aguinaldo->fecha_fiscal,$inicio1,$ejercicio,$añoFechaFiscal,$finicio1);
            // }

            if($diasFiscalesReales<>0){
                $diasagui=$diasFiscalesReales;
            }else{
                $diasagui=$diasAguiReal;
            }        

            DB::connection('empresa')->table('aguinaldo')->updateOrInsert(
                [
                    'id_empleado' => $empleado->id,
                    'ejercicio'   => $ejercicio
                ],
                [
                    'gravado'         => $parteGravada,
                    'pago_aguinaldo'  => round($aguinaldo2, 2),
                    'impuestos'       => $impuestoCargo,
                    'neto'            => $Neto,
                    'dias_aguinaldo'  => round($diasAguiReal, 2),
                    'dias_aguinaldo2' => round($diasagui2, 2),
                    'importe2'        => $importe,
                    'neto2'           => $importeTotal,
                    'impuesto_anual'  => $diferenciaImpuesto,
                    'dias_fiscales'    => $diasagui,
                    'total_fiscal'     => $TotalFiscal,
                    'total_sindical'  => $TotalSindical
                ]
            );
        }
    }

    public function exportarAguinaldo(Request $request)
    {
        return Excel::download(new AguinaldoExport($request->ejercicio, $request->deptos),"Aguinaldo_".date('d-m-Y').".xlsx");
    }

    public function importarAguinaldo(Request $request)
    {     
        tienePermiso('aguinaldo');  
        cambiarBase(Session::get('base'));        
        try{                        
            Excel::import( new AguinaldoImport($request->ejercicio), $request->file('archivo-empleados'));   
            $request->session()->forget('danger');
            session()->flash('success', 'Se importó correctamente el archivo y se re-calcularon los aguinaldos.');        
        }catch(\Exception $e){      
            $request->session()->forget('success');
            session()->flash('danger', 'Error al cargar el archivo, intente nuevamente !!.');                   
        }
        $deptos = $request->deptos;
        $ejercicio = $request->ejercicio;
        $impuestoanual = $request->impuestoanual;

        $this->preCalcularAguinaldos($request);
        $aguinaldos = $this->obtenerAguinaldos($deptos, $ejercicio);

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                                ->whereIn('id', $aguinaldos->pluck('id_empleado')->toArray())
                                ->whereIn('id_departamento', $deptos)
                                ->orderBy('id')
                                ->get();
        foreach($empleados as &$empleado){
            $empleado->aguinaldo = $aguinaldos[$empleado->id];
        }

        $sindical = DB::connection('empresa')->table('conceptos_nomina')->where('file_rool', 0)->where('estatus',1)->count();        
                   
        return view('procesos.calculo-aguinaldo.preaguinaldo')
        // return redirect()->route('procesos.pre.aguinaldo', compact('empleados', 'sindical', 'deptos', 'ejercicio', 'impuestoanual'));
                ->with('empleados', $empleados)
                ->with('sindical', $sindical)
                ->with('deptos', $deptos)
                ->with('ejercicio', $ejercicio)
                ->with('impuestoanual', $impuestoanual);  
                Session::flush();          
                // ->with('tipo_alerta', 'success')
                // ->with('mensaje', 'Se importó correctamente el archivo y se re-calcularon los aguinaldos.');
    }

    public function recalculoAguinaldo(Request $request)
    {
        tienePermiso('aguinaldo');
        cambiarBase(Session::get('base'));

        $deptos = $request->deptos;
        $ejercicio = $request->ejercicio;
        $impuestoanual = $request->impuestoanual;

        $impuestos = DB::connection('empresa')->table('impuestos')->select('limite_inferior')->where('tipo_tabla', 'Anual')->get();
        if($impuestos->count() <= 0){
            $request->session()->forget('success');
            session()->flash('danger', 'Debe cargar las Tablas de Impuesto y Subsidios Anuales para continuar.'); 
            return redirect()->route('procesos.calculo_aguinaldo');
        }            
            
        $this->preCalcularAguinaldos($request);
        $aguinaldos = $this->obtenerAguinaldos($deptos, $ejercicio);

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                                ->whereIn('id', $aguinaldos->pluck('id_empleado')->toArray())
                                ->whereIn('id_departamento', $deptos)
                                ->orderBy('id')
                                ->get();
        foreach($empleados as &$empleado){
            $empleado->aguinaldo = $aguinaldos[$empleado->id];
        }

        $sindical = DB::connection('empresa')->table('conceptos_nomina')->where('file_rool', 0)->where('estatus',1)->count();

        return view('procesos.calculo-aguinaldo.preaguinaldo', compact('empleados', 'sindical', 'deptos', 'ejercicio', 'impuestoanual'));
    }

    protected function guardarDatosAdicionalesAguinaldo(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('aguinaldo')->where('id', $request->id)->update(
                [
                    'fecha_fiscal'          => $request->fecha_fiscal,
                    'pension_alimenticia'  => $request->pension_alimenticia,
                    'descuentos_otros'     => $request->descuentos_otros,
                    's_pension_alimenticia'=> $request->s_pension_alimenticia,
                    's_descuentos_otros'   => $request->s_descuentos_otros,
                ]
            );
        return response()->json(['ok' => 1]);
    }
}
