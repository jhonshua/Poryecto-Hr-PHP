<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Empleado;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\CalculoNominaExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Exports\CalculoNominaDetalleExport;

class CalculoNominaController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

	protected $empleados;
  	protected $periodo;

  	public function calculonomina()
  	{
        tienePermisoa('periodos_nomina');
        tienePermisoa('abrir_nomina');
        cambiarBase(Session::get('base'));

        $periodo = PeriodosNomina::where('activo', PeriodosNomina::ACTIVO)->where('estatus', PeriodosNomina::ESTATUS_DISPONIBLE)->first();
		
        if(!$periodo){

            // return redirect()->route('parametria.periodosnomina')->with('tipo_alerta', 'danger')->with('mensaje', 'No exsten nominas activas. Verifique.');

            session()->flash('danger', 'No exsten nominas activas');

            return redirect()->route('nomina.periodos');

        }

        $queryNombrePeriodo = "SELECT nombre_periodo from periodos_nomina pn join impuestos im on pn.nombre_periodo=im.tipo_tabla inner join subsidios su on pn.nombre_periodo = su.tipo_tabla where pn.activo=1";
        $valida_periodo = count(DB::connection('empresa')->select($queryNombrePeriodo));

        if($valida_periodo > 0){

            $periodo = PeriodosNomina::find($periodo->id);
            $tblRutinas = 'rutinas'.$periodo->ejercicio;

            $total = DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id_periodo)
                                        ->where('estatus_confirma', 1)->count();

            $deptos_ids = Empleado::distinct('id_departamento')
                                ->select('id_departamento')
                                ->join($tblRutinas, $tblRutinas.'.id_empleado', '=', 'empleados.id')
                                ->where('empleados.estatus', Empleado::EMPLEADO_ACTIVO)
                                ->where('empleados.tipo_de_nomina', 'like',  $periodo->nombre_periodo)
                                ->where($tblRutinas.'.id_periodo',  $periodo->id)
                                ->get();

                                
            foreach ($deptos_ids as $depto) {
                $d_ids[] = $depto->id_departamento;
            }

            $departamentos = Departamento::whereIn('id', $d_ids)->orderBy('nombre', 'asc')->get();
            $validacion = true;
            return view('calculo-nomina.calculo-nomina', compact('periodo', 'departamentos', 'total', 'validacion'));

        } else {
    
            $validacion = false;
            return view('calculo-nomina.calculo-nomina', compact('validacion'));
        }

  	}

    public function revocarNomina(Request $request)
    {
        tienePermiso('periodos_nomina');
        // tienePermisoA('abrir_nomina');
        cambiarBase(Session::get('base'));

        DB::connection('empresa')->table('rutinas'.$request->ejercicio)
                                    ->where('fnq_valor', 0)
                                    ->where('id_periodo', $request->id_periodo)
                                    ->update(['estatus_confirma'=> 0]);

        PeriodosNomina::where('id', $request->id_periodo)->update(['revocado' => 1, 'motivo_revoca' => $request->motivo]);

        $desc ='Se ha revocado la confirmacion de la nomina '.$request->id_periodo.'. ';
        $tipo ='RN';
        
        envioAvisosXMail(Session::get('base'), 45, $request->id_periodo);
        agregarABitacora(Session::get('base'), 45, $tipo, $request->id_periodo, $desc);

        session()->flash('success', $desc);

        return redirect()->route('calculo.nomina');

    }

    public function calcularNomina(Request $request)
    {
        
        tienePermiso('periodos_nomina');
        tienePermiso('abrir_nomina');
        cambiarBase(Session::get('base'));

        $periodo = PeriodosNomina::where('activo', periodosNomina::ACTIVO)->first();

        if(!$periodo){
            // return redirect()->route('parametria.periodosnomina')->with('tipo_alerta', 'danger')->with('mensaje', 'No exsten nominas activas. Verifique.');

            session()->flash('danger', 'No exsten nominas activas');

            return redirect()->route('nomina.periodos');

        }
        $this->periodo = $periodo;
        

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->first();

        $deptos                  = $request->deptos;
        $parametros_empresa      = $parametros;
        $provision_aguinaldo     = $parametros_empresa->provision_aguinaldo;
        $povisionPrimavacacional = $parametros_empresa->provision_prima_vacacional;
        $cadena_empleados        = ''; // TODO - Revisar

        $this->descuentosRecurrentes($periodo, $deptos);
        $this->aportacionesRecurrentes($periodo, $deptos);

        // $queryconcep="SELECT * FROM $base.rutinas$ejercicio where idperiodo='$idperiodo' and FnqValor=0  AND idempleado in (SELECT idempleado from $base.empleado where status=0 $cadena)";
        $empleados_rutinas = DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('fnq_valor', 0)
                                        ->whereIn('id_empleado', function($query) use($deptos, $periodo){
                                            $query->select('id')
                                            ->from(with(new Empleado)->getTable())
                                            ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                                            ->where('fecha_alta', '<=', $periodo->fecha_final_periodo)
                                            ->where('tipo_de_nomina', $periodo->nombre_periodo)
                                            ->whereIn('id_departamento', $deptos);
                                        })->get()->keyBy('id_empleado');
        
        $rutinas_ids = $ids_empleados = [];
        foreach ($empleados_rutinas as $rutina) {
            $rutinas_ids[] = $rutina->id;
            $ids_empleados[] = $rutina->id_empleado;
        }

        $empleados = Empleado::whereIn('id', $ids_empleados)->get()->keyBy('id');
        foreach($empleados as $empleado){
            $empleados[$empleado->id]->rutinas = $empleados_rutinas[$empleado->id];
        }
        $this->empleados = $empleados;
        $this->reinicializarCampos();
        
        // $querylimpia="UPDATE $base.rutinas$ejercicio set TotalGravado=0 where idrutina='$idrutina' and FnqValor=0";
        DB::connection('empresa')->table('rutinas'.$periodo->ejercicio) ->whereIn('id', $rutinas_ids)->where('fnq_valor', 0)->update(['total_gravado' => 0]);

        // TODO: Revisar $cadena_empleados
        // "UPDATE $base.periodosdenomina SET cadenaEmpleados=".'"'.$idsempleados.'"'." WHERE idperiodo='$idperiodo'";
        // periodosNomina::where('id', $periodo->id)->update(['cadena_empleados' => $cadena_empleados]);

        $this->AsignarDiasIncapacidad();
        $this->AsignarFaltas();
        
        $auxagui = 0;
        $auxprimvaca = 0;
        // "SELECT * from $base.conceptosnomina where status<>2 and  activoenNomina=1 and Nomina=1";
        $conceptos = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('activo_en_nomina', 1)->where('nomina', 1)->get();
        // dump('conceptos a revisar: '.$conceptos->pluck('rutinas')->toArray());
		
        foreach ($conceptos as $concepto) {

            switch (strtoupper(trim($concepto->rutinas))) {
                case 'SDO':
                    $this->SDO();
                    break;
                case 'SDOXHORA':
                    $this->SDOXHORA();
                    break;
                case 'IMSS':
                    $this->IMSS();
                    break;
                case 'PRDOM':
                    $this->PRDOM();
                    break;
                case 'PVAC':
                    $this->PVAC();
                    break;
                case 'PVACANTI':
                    $this->PVAC_ANT();
                    break;
				case 'DIASFESTIVOS':
                    $this->DIASFESTIVOS();
	                 break;
                case 'FAHOPAT':
                    $this->FAHOPAT();
                    break;
                case 'HEXT3':
                    $this->HEXT3();
                    break;
                case 'HEXT2':
                    $this->HEXT2();
                    break;
                case 'PPAGUI':
                    $this->PPAGUI();
                    break;
                case 'INFONA':
                    $this->INFONA();
                    break;
                case 'VACA':
                    $this->VACA();
                    break;
                case 'PTU':
                    $this->PTU();
                    break;
                default:
                    $this->Default($concepto);
                    break;
            }
            
            if($provision_aguinaldo == 1)
                $this->PPAGUI();

            if($povisionPrimavacacional == 1)
                $this->PVAC();
        }
        $this->SumaObrero();		 
        $this->ISPT();
        $this->INFONA_PAtron();
        $this->Neto_Fiscal();
        $this->Neto_Fiscal_Sindical();
		
        $this->GuardarEmpleadosRutinas();
		
        $esCalculoNomina = true;

        $empleados              = $this->empleados;
        $data                   = $this->generarReporteNomina($deptos, $esCalculoNomina);
        $misc                   = $data['misc'];
        $columnas1              = $data['columnas1'];
        $columnas2              = $data['columnas2'];
        $columnasSindical       = $data['columnasSindical'];
        $columnaPVAC            = $misc['columnaPVAC'];
        $rowsidpvacAnti         = $misc['rowsidpvacAnti'];
        $rowsidprdom            = $misc['rowsidprdom'];
        $rowConceptoDescuento75 = $misc['rowConceptoDescuento75'];
        $columnasDEDUCC         = $data['columnasDEDUCC'];
        $columnas3              = $data['columnas3'];
        $totales                = $data['totales'];
        $emisoras               = $data['emisoras'];
        $valoresHoras           = $data['valoresHoras'];
        $valorDiasPHoras        = $data['valorDiasPHoras'];
        $departamentos          = $deptos;

        $this->GuardarEmpleadosRutinas();

        //dd($empleados);
    
        return view('calculo-nomina.reporte-nomina', compact('periodo', 'empleados', 'misc', 'columnas1', 'columnas2', 'columnasSindical', 'columnaPVAC', 'rowsidpvacAnti', 'rowsidprdom', 'rowConceptoDescuento75', 'columnasDEDUCC', 'columnas3', 'parametros_empresa', 'totales', 'emisoras', 'departamentos','valoresHoras','valorDiasPHoras'));
    }

    protected function descuentosRecurrentes(PeriodosNomina $periodo, array $deptos)
    {
        cambiarBase(Session::get('base'));
        $deptosStr = implode(',', $deptos);

        $query = "SELECT ru.id_empleado, importe_a_descontar, especial, inci.id_concepto, inci.activa_descuento, inci.estatus as estatuss, con.tipo_proceso FROM rutinas$periodo->ejercicio  ru JOIN  incidencias_prg inci ON ru.id_empleado = inci.id_empleado INNER JOIN conceptos_nomina con ON inci.id_concepto = con.id WHERE fnq_valor = 0 AND con.activo_en_nomina = 1 AND percep_deduc = 1 AND ru.id_periodo='$periodo->id' AND ru.id_empleado in (select id from empleados where estatus=1 and id_departamento in ($deptosStr)) group by ru.id_empleado, inci.id_concepto";

        $registros = DB::connection('empresa')->select($query);

        if(count($registros) > 0){
            foreach($registros as $registro){

                if($registro->activa_descuento == 1 && $registro->estatuss == 0){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);

                }else if($registro->activa_descuento == 1){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto='$importeaDescontar' where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => $registro->importe_a_descontar]);

                }else if($registro->tipo_proceso==0){

                }else{

                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);
                }
            }
        }
        $this->deducciones2($periodo,$deptos);
    }
        
    protected function deducciones2(periodosNomina $periodo, array $deptos)
    {
        cambiarBase(Session::get('base'));
        $deptosStr = implode(',', $deptos);

        $query = "SELECT ru.id_empleado, inci.cantidad_a_descontar,  inci.id_concepto, inci.estatus as estatuss, con.tipo_proceso 
        FROM rutinas$periodo->ejercicio  ru 
        JOIN  empleados_deducciones inci
        ON ru.id_empleado = inci.id_empleado 
        INNER JOIN conceptos_nomina con 
        ON inci.id_concepto = con.id 
        WHERE fnq_valor = 0 
        AND con.activo_en_nomina = 1 
        AND con.estatus = 1 
        AND inci.estatus=1
        AND ru.id_periodo='$periodo->id' 
        AND ru.id_empleado in (select id from empleados where estatus = 1 and id_departamento in ($deptosStr)) group by ru.id_empleado, inci.id_concepto";

        if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($query);
                }

        $registros = DB::connection('empresa')->select($query);

        if(count($registros) > 0){
            foreach($registros as $registro){
                // 3 Pausado
                if( $registro->estatuss == 0){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);
                }else if( $registro->estatuss == 1){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto='$importeaDescontar' where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => $registro->cantidad_a_descontar]);
                                        


                }else if($registro->tipo_proceso==0){

                }else{
                    
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);
                }

            }
        } 
    }


    protected function aportacionesRecurrentes(periodosNomina $periodo, array $deptos)
    {
        
        cambiarBase(Session::get('base'));
        $deptosStr = implode(',', $deptos);

        $query = "SELECT ru.id_empleado, importe, especial, inci.id_concepto, inci.activa_aportacion, inci.numero_pagos FROM rutinas$periodo->ejercicio ru JOIN incidencias_prg inci ON ru.id_empleado = inci.id_empleado INNER JOIN conceptos_nomina con ON inci.id_concepto = con.id WHERE fnq_valor = 0 and con.activo_en_nomina = 1 and percep_deduc = 2 and inci.estatus = 1 AND ru.id_empleado in (select id from empleados where estatus = 1 and inci.activa_aportacion=1 and id_departamento in ($deptosStr)) group by ru.id_empleado, inci.id_concepto";
        if(Session::get('usuarioPermisos')['id_usuario']==64){
            //dd($query);
        }
        $registros = DB::connection('empresa')->select($query);

        if(count($registros) > 0){
            foreach($registros as $registro){

                $importe_aportar = ($registro->activa_aportacion == 1) ? ($registro->importe / $registro->numero_pagos) : 0;
                DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => $importe_aportar]);
            }
        }
        $this->aportaciones2($periodo,$deptos);
    }
	
	/** TODO unificar con lo de arriba para una sola accion y una sola tabla 
         * status
         * 0 en pausa
         * 1 activo normal
         * 2 pagada completamente
         * 3 eliminado
        */
    protected function aportaciones2(periodosNomina $periodo, array $deptos){
        cambiarBase(Session::get('base'));
        $deptosStr = implode(',', $deptos);

        $query = "SELECT ru.id_empleado, inci.cantidad_a_aportar,  inci.id_concepto, inci.estatus as estatuss, con.tipo_proceso 
        FROM rutinas$periodo->ejercicio  ru 
        JOIN  empleados_percepciones inci
        ON ru.id_empleado = inci.id_empleado 
        INNER JOIN conceptos_nomina con 
        ON inci.id_concepto = con.id 
        WHERE fnq_valor = 0 
        AND con.activo_en_nomina = 1         
        AND con.estatus = 1  
        AND inci.estatus=1
        AND ru.id_periodo='$periodo->id' 
        AND ru.id_empleado in (select id from empleados where estatus = 1 and id_departamento in ($deptosStr)) group by ru.id_empleado, inci.id_concepto";

        $registros = DB::connection('empresa')->select($query);

        if(count($registros) > 0){
            foreach($registros as $registro){
                // 3 Pausado
                if( $registro->estatuss == 0){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);
                }else if( $registro->estatuss == 1){
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto='$importeaDescontar' where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => $registro->cantidad_a_aportar]);
                                        


                }else if($registro->tipo_proceso==0){

                }else{
                    
                    // $queryUpdate="UPDATE $base.rutinas$ejercicio set Valor$idconcepto=0 where idempleado='$idempleado' and FnqValor=0 and idperiodo='$idperiodo'";
                    DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
                                        ->where('id_periodo', $periodo->id)
                                        ->where('id_empleado', $registro->id_empleado)
                                        ->where('fnq_valor', 0)
                                        ->update(['valor'.$registro->id_concepto => 0]);
                }

            }
        } 
    }

    protected function reinicializarCampos()
    {
        foreach($this->empleados as &$empleado){
            $empleado->rutinas->total_percepcion_fiscal = 0;
            $empleado->rutinas->total_percepcion_fiscal2 = 0;
            $empleado->rutinas->total_percepcion_sindical = 0;
            $empleado->rutinas->total_deduccion_fiscal = 0;
            $empleado->rutinas->total_deduccion_fiscal2 = 0;
            $empleado->rutinas->neto_fiscal = 0;
            $empleado->rutinas->neto_sindical = 0;
            $empleado->rutinas->total_gravado = 0;
            $empleado->rutinas->sdo_faltas = 0;
            $empleado->rutinas->sdo_incapacidades = 0;
            $empleado->rutinas->cuota_fija = 0;
            $empleado->rutinas->exce_pa = 0;
            $empleado->rutinas->exce_ob = 0;
            $empleado->rutinas->pre_dine_obre = 0;
            $empleado->rutinas->pre_dine_patro = 0;
            $empleado->rutinas->gas_medi_patro = 0;
            $empleado->rutinas->gas_medi_obre = 0;
            $empleado->rutinas->riesgo_trabajo = 0;
            $empleado->rutinas->inva_vida_patro = 0;
            $empleado->rutinas->inva_vida_obre = 0;
            $empleado->rutinas->guarde_presta = 0;
            $empleado->rutinas->sar_patron = 0;
            $empleado->rutinas->infonavit_patro = 0;
            $empleado->rutinas->censa_vejez_patron = 0;
            $empleado->rutinas->censa_vejez_obre = 0;
            $empleado->rutinas->beneficio_sindical = 0;
            $empleado->rutinas->importe_total = 0;
            $empleado->rutinas->subsidio = 0;
            $empleado->rutinas->infonavit_patron = 0;
            $empleado->rutinas->subsidio_al_empleo = 0;
            $empleado->rutinas->censa_vejez_obre_patronal = 0;
            $empleado->rutinas->bono_prima = 0;
            $empleado->rutinas->bono_prima_dom = 0;
            $empleado->rutinas->incapacidades = 0;
        
        }
    }

    protected function AsignarDiasIncapacidad(){

        // Incapacidades
        $query_incapacidades = "SELECT sum(dias) as dias, id_empleado  from incapacidades inc join empleados em on inc.id_empleado=em.id where periodo = {$this->periodo->numero_periodo} and fecha_inicio_incapacidad like '{$this->periodo->ejercicio}%' and inc.estatus = 1 and em.estatus=1 group by id_empleado";
        if(Session::get('usuarioPermisos')['id_usuario']==64 ){
            //dd($query_incapacidades);
        }
        
        $incapacidades = DB::connection('empresa')->select($query_incapacidades);
        
        

        if(count($incapacidades) > 0){

            
            foreach ($incapacidades as $incapacidad) {
                    $this->empleados[$incapacidad->id_empleado]->dias_incapacidad = $incapacidad->dias;
            }
        }

        foreach ($this->empleados as $empleado) {
            if(!isset($empleado->dias_incapacidad)){
                $this->empleados[$empleado->id]->dias_incapacidad = 0;
            }
        }
    }

    protected function AsignarFaltas(){

        // Faltas
        $concepto_faltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->where('activo_en_nomina', 1)->first();
        $id_concepto_faltas = ($concepto_faltas != null) ? intval($concepto_faltas->id) : 0;

        foreach ($this->empleados as $empleado) {
            if($id_concepto_faltas > 0){
                $col_faltas = 'valor'.$id_concepto_faltas;
                $faltas = $empleado->rutinas->$col_faltas;
            } else {
                $faltas = 0;
            }


            $this->empleados[$empleado->id]->faltas = $faltas;
        }
    }

    protected function SDO()
    {
        $periodo = $this->periodo;
		
        $sueldo = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'SUELDO')->where('estatus', 1)->first();

        $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));
        $ano_final_periodo = date('Y', strtotime($periodo->fecha_final_periodo));

        foreach ($this->empleados as &$empleado) {

            $colTotalSdo   = 'total'.$sueldo->id;
            $colExcentoSdo = 'excento'.$sueldo->id;
            $colGravadoSdo = 'gravado'.$sueldo->id;

            if($periodo->especial || ($empleado->tipo_sindical == 1 && $empleado->tipo_fiscal == 0)){

                $empleado->rutinas->total_gravado     = 0;
                $empleado->rutinas->$colTotalSdo      = 0;
                $empleado->rutinas->$colExcentoSdo    = 0;
                $empleado->rutinas->$colGravadoSdo    = 0;
                $empleado->rutinas->sdo_faltas        = 0;
                $empleado->rutinas->sdo_incapacidades = 0;
                $empleado->rutinas->incapacidades     = 0;
				
				$empleado->validaSDO = 0;
				
            } else {

                $faltas = $empleado->faltas;
                $dias_incapacidad = $empleado->dias_incapacidad;


                if($empleado->fecha_alta > $periodo->fecha_inicial_periodo){

                    $fin_del_periodo = $periodo->fecha_final_periodo;
                    if(intval($mes_final_periodo) == 2 && intval($dia_final_periodo) > 27){
                        $fin_del_periodo = date('Y-m-t',strtotime($periodo->fecha_inicial_periodo));
                    }

                    $fecha_final_periodo = Carbon::parse($fin_del_periodo);
                    $fecha_alta = Carbon::parse($empleado->fecha_alta);
                    $dias_lab = $fecha_final_periodo->diffInDays($fecha_alta) + 1;
                    $dias_laborables = ($dias_lab - $dias_incapacidad - $faltas);

                } else {
                    $dias_laborables = $periodo->dias_periodo - $dias_incapacidad - $faltas;
                }
                
                
                $SDO              = round($empleado->salario_diario * $dias_laborables, 2);
                $SDOFaltas        = $empleado->salario_diario * $faltas;
                $SDOIncapacidades = $empleado->salario_diario * $dias_incapacidad;
                $partegravada     = $SDO;
                $resultaGravado   = round($empleado->rutinas->total_gravado + $partegravada, 2);
                $parteExcenta     = 0;
                
                // if($empleado->id == 86)
                //     dump($empleado->salario_diario, $dias_laborables, $SDO, $SDOFaltas , $SDOIncapacidades , $resultaGravado);
                
                $empleado->rutinas->total_gravado     = $resultaGravado;
                $empleado->rutinas->$colTotalSdo      = $SDO;
                $empleado->rutinas->$colExcentoSdo    = $parteExcenta;
                $empleado->rutinas->$colGravadoSdo    = $partegravada;
                $empleado->rutinas->sdo_faltas        = $SDOFaltas;
                $empleado->rutinas->sdo_incapacidades = $SDOIncapacidades;
                $empleado->rutinas->incapacidades     = $dias_incapacidad;
				$empleado->validaSDO = $SDO;
            }
        }
        
        // dd($this->empleados[86]->rutinas);
		/*
		if(Session::get('usuarioPermisos')['id_usuario']==64){
                    dd($this->empleados);
                }
		*/
    }

    protected function IMSS()
    {
        $calculoIMSS = Session::get('empresa')['calculo_imss'];
        $diasIMSS = Session::get('empresa')['dias_imss'];
        $periodo = $this->periodo;

        // Concepto Imss
        $concepto_imss = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'IMSS')->where('estatus', 1)->first();

        if($periodo->especial){

            foreach($this->empleados as &$empleado) {
                
                    $empleado->rutinas->dias_imss = $periodo->dias_periodo;
                    $empleado->rutinas->{'total'.$concepto_imss->id} = 0;
                    $empleado->rutinas->cuota_fija = 0;
                    $empleado->rutinas->exce_pa = 0;
                    $empleado->rutinas->exce_ob= 0;
                    $empleado->rutinas->pre_dine_obre = 0;
                    $empleado->rutinas->pre_dine_patro = 0;
                    $empleado->rutinas->gas_medi_patro = 0;
                    $empleado->rutinas->gas_medi_obre = 0;
                    $empleado->rutinas->riesgo_trabajo = 0;
                    $empleado->rutinas->inva_vida_patro = 0;
                    $empleado->rutinas->inva_vida_obre = 0;
                    $empleado->rutinas->guarde_presta = 0;
                    $empleado->rutinas->sar_patron = 0;
                    $empleado->rutinas->infonavit_patro = 0;
                    $empleado->rutinas->censa_vejez_patron = 0;
                    $empleado->rutinas->censa_vejez_obre = 0;
                    $empleado->rutinas->censa_vejez_obre_patronal = 0;
            }
            
            return;
        } 

        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->first();


        $parametros_empresa      = $parametros;
        $uma                     = $parametros_empresa->uma;
        $salario_base            = $parametros_empresa->salario_minimo;
        $cuota_fija              = $parametros_empresa->cuota_fija;
        $excedente_patro         = $parametros_empresa->excedente_patro;
        $excedente_obrera        = $parametros_empresa->excedente_obrera;
        $provision_obrero        = $parametros_empresa->provision_obrero;
        $prestaciones_patronal   = $parametros_empresa->prestaciones_patronal;
        $prestaciones_obrera     = $parametros_empresa->prestaciones_obrera;
        $gastos_medi_patronal    = $parametros_empresa->gastos_medi_patronal;
        $gastos_medi_obrera      = $parametros_empresa->gastos_medi_obrera;
        $invalidez_patronal      = $parametros_empresa->invalidez_patronal;
        $invalidez_obrera        = $parametros_empresa->invalidez_obrera;
        $guarderia_presta_social = $parametros_empresa->guarderia_presta_social;

        // Incapacidades
        $query_incapacidades = "SELECT sum(dias) as dias, id_empleado, tipo_aplicacion  from incapacidades where periodo = $periodo->id and estatus = 1 and year(fecha_inicio_incapacidad)='$periodo->ejercicio' group by id_empleado";
        
        $incapacidades = DB::connection('empresa')->select($query_incapacidades);

        if(count($incapacidades) > 0) {
            foreach ($incapacidades as $incapacidad) {
                $this->empleados[$incapacidad->id_empleado]->tipo_aplicacion = strtolower($incapacidad->tipo_aplicacion);
            }
        }

        $empleadosStr = implode(',', $this->empleados->pluck('id')->toArray());

        // Prima de riesgo
        $queryPrimaRiesgo = "SELECT regpa.porcentaje_prima as p_prima, em.id from empleados em join categorias cat on em.id_categoria = cat.id inner join singh.registro_patronal regpa on cat.tipo_clase = regpa.id where em.id IN ($empleadosStr) and cat.estatus=1 and regpa.estatus=1";
        
        $primas_riesgo = DB::connection('empresa')->select($queryPrimaRiesgo);
        
        if($primas_riesgo != null){
            foreach ($primas_riesgo as $prima) {
              
                $this->empleados[$prima->id]->prima_riesgo = ($prima->p_prima/100);
            }
        }

        foreach($this->empleados as &$empleado) {

            $faltas           = $empleado->faltas;
            $dias_incapacidad = $empleado->dias_incapacidad;

            $fin_del_periodo = $periodo->fecha_final_periodo;

            $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
            $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));

            if($empleado->fecha_alta > $periodo->fecha_inicial_periodo){

                if(intval($mes_final_periodo) == 2 && intval($dia_final_periodo) > 15){
                    $fin_del_periodo = date('Y-m-t',strtotime($periodo->fecha_inicial_periodo));
                }

                $fecha_final_periodo = Carbon::parse($fin_del_periodo);
                $fecha_alta = Carbon::parse($empleado->fecha_alta);
                $dias_lab = $fecha_final_periodo->diffInDays($fecha_alta) + 1;

                $dias_falta = $dias_lab - $dias_incapacidad;

                $dias = ($dias_incapacidad == 15 && $dias_lab==16) ? 0 : $dias_falta;
                $dias_patron = ($dias_incapacidad == 15 && $Di == 16) ? 0 : $dias_falta;


                $fecha_final_periodo = Carbon::parse($periodo->fecha_final_periodo);
                $fecha_alta = Carbon::parse($empleado->fecha_alta);
                $dias_naturales_periodo = $fecha_final_periodo->diffInDays($fecha_alta) + 1;


                $dias_naturales = (intval($empleado->rutinas->dias_imss) <=  0) ? ($dias_naturales_periodo - $dias_incapacidad) : intval($empleado->rutinas->dias_imss);
                if(Session::get('usuarioPermisos')['id_usuario']==64 && $empleado->id==595){
                    //dump($dias_naturales,$empleado->rutinas->dias_imss,$dias_naturales_periodo);
                    
                }
                if($dias_incapacidad == 15 && $dias_lab==16) $dias_naturales = 0;

                $dias_infonavit = ($empleado->tipo_aplicacion == 'bimestral') ? $dias_naturales_periodo : ($dias_naturales_periodo - $dias_incapacidad);

            } else {

                $fecha_final_periodo = Carbon::parse($periodo->fecha_final_periodo);
                $fecha_inicial_periodo = Carbon::parse($periodo->fecha_inicial_periodo);
                $di = $fecha_final_periodo->diffInDays($fecha_inicial_periodo) + 1;

                $dias = ($dias_incapacidad == 15 && $di == 16) ? 0: ($di - $dias_incapacidad);
                $dias_patron = ($dias_incapacidad == 15 && $di == 16) ? 0 : ($di - $dias_incapacidad);

                $dias_naturales_periodo = $di;
                $dias_naturales = (intval($empleado->rutinas->dias_imss) <=  0) ? ($dias_naturales_periodo - $dias_incapacidad) : intval($empleado->rutinas->dias_imss);

                if($empleado->demanda_activa == 1){
                    $dias_infonavit = $dias_naturales_periodo;
                } else { 
                    $dias_infonavit = ($empleado->tipo_aplicacion == 'bimestral') ? $dias_naturales_periodo : ($dias_naturales_periodo - $dias_incapacidad);
                }

                if($dias_incapacidad == 15 && $di == 16) $dias_naturales = 0;
            }

            $salario_dia_inte = round($empleado->salario_diario_integrado,2);
			
            $resu_cuota_fija = $uma * $dias_naturales * $cuota_fija;
            $resu_cuota_fija_naturales = $uma * $dias_naturales * $cuota_fija;
			
            if(strtoupper($calculoIMSS) == 'UMA'){

                if($salario_dia_inte < ($uma * 3) ){
                    $exce_patro = 0;
                    $exce_patro_naturales = 0;
                    $exce_obrera = 0;

                }else{
                    $exce_patro = ($salario_dia_inte - (3 * $uma)) * $dias_naturales * $excedente_patro;
                    $exce_patro_naturales = ($salario_dia_inte - (3 * $uma)) * $dias_naturales * $excedente_patro;
                    $exce_obrera = ($salario_dia_inte - (3 * $uma)) * $dias_naturales * $excedente_obrera;
                }

            } elseif(strtoupper($calculoIMSS)=='SALARIODIARIO'){

                $sal_base_cotizacion = $dias_naturales * round($salario_dia_inte,2);
                $smg_periodo = $salario_base * $periodo->dias_periodo * 3;

                if($sal_base_cotizacion > $smg_periodo){

                    $exce_patro = (($sal_base_cotizacion - $smg_periodo) * $excedente_patro) * $dias_naturales;
                    $exce_patro_naturales = (($sal_base_cotizacion - $smg_periodo) * $excedente_patro) * $dias_naturales;
                    $exce_obrera = ($salario_dia_inte - (3 * $uma)) * $dias_naturales * $excedente_obrera;

                }else{

                    $exce_patro = 0;
                    $exce_patro_naturales = 0;
                    $exce_obrera = 0;
                }
            }

            $pre_patronal               = $salario_dia_inte * $dias_naturales * $prestaciones_patronal;
            $pre_patronal_naturales     = $salario_dia_inte * $dias_naturales * $prestaciones_patronal;
            $pre_obrera                 = $salario_dia_inte * $dias_naturales * $prestaciones_obrera;
            $gastos_patronal            = $salario_dia_inte * $dias_naturales * $gastos_medi_patronal;
            $gastos_patronal_naturales  = $salario_dia_inte * $dias_naturales * $gastos_medi_patronal;
            $gastos_obrera              = $salario_dia_inte * $dias_naturales * $gastos_medi_obrera;
            $riesgo_trabajo             = $salario_dia_inte * $dias_naturales * $empleado->prima_riesgo; 
            $riesgo_trabajo_naturales   = $salario_dia_inte * $dias_naturales * $empleado->prima_riesgo;
            $inva_patronal              = $salario_dia_inte * $dias_naturales * $invalidez_patronal;
            $inva_patronal_natural      = $salario_dia_inte * $dias_naturales * $invalidez_patronal;
            $inva_obrera                = $salario_dia_inte * $dias_naturales * $invalidez_obrera;
            $guarde_social              = $salario_dia_inte * $dias_naturales * $guarderia_presta_social;
            $guarde_social_naturales    = $salario_dia_inte * $dias_naturales * $guarderia_presta_social;
            $sar_patron                 = $salario_dia_inte * 0.02 * $dias_naturales;
            $sar_patron_naturales       = $salario_dia_inte * 0.02 * $dias_naturales;
            $infonavit_patron           = $salario_dia_inte * 0.05 * $dias_naturales;
            $infonavit_patron_naturales = $salario_dia_inte * 0.05 * $dias_naturales;
            
            $CESANTIAYVEJEZ             = $salario_dia_inte * 0.03150 * $dias_naturales;
            $CESANTIAYVEJEZnaturales    = $salario_dia_inte * 0.03150 * $dias_naturales;
            $CESANTIAYVEJEZObrera       = $salario_dia_inte * 0.01125 * $dias_naturales;
            $CESANTIAYVEJEZObreraN      = $salario_dia_inte * 0.01125 * $dias_naturales;
            $CESANTIAYVEJEZ             = $CESANTIAYVEJEZ + $CESANTIAYVEJEZObrera;

            $CESANTIAYVEJEZObreraPatronal          = $salario_dia_inte * 0.01125 * $dias_naturales;
            $CESANTIAYVEJEZObreraPatronalNaturales = $salario_dia_inte * 0.01125 * $dias_naturales;


            $SumaPatronal = $resu_cuota_fija + $exce_patro + $pre_patronal + $gastos_patronal + $inva_patronal + $guarde_social + $riesgo_trabajo + $sar_patron + $infonavit_patron + $CESANTIAYVEJEZ;
	
			$SumaObrera = $exce_obrera + $pre_obrera + $gastos_obrera + $inva_obrera + $CESANTIAYVEJEZObrera;
			
            $SumaObre   = round($SumaObrera,2);
            $Subtotal   = $SumaPatronal + $SumaObrera;

            if(Session::get('usuarioPermisos')['id_usuario']==64 && $empleado->id==595){
                    //dd('termina',$dias_naturales);
                    
                }

            if($empleado->tipo_sindical == 1 && $empleado->tipo_fiscal == 0){

                $empleado->rutinas->dias_imss = $dias_naturales;
                $empleado->rutinas->{'total'.$concepto_imss->id} = 0;
                $empleado->rutinas->cuota_fija = 0;
                $empleado->rutinas->exce_pa = 0;
                $empleado->rutinas->exce_ob= 0;
                $empleado->rutinas->pre_dine_obre = 0;
                $empleado->rutinas->pre_dine_patro = 0;
                $empleado->rutinas->gas_medi_patro = 0;
                $empleado->rutinas->gas_medi_obre = 0;
                $empleado->rutinas->riesgo_trabajo = 0;
                $empleado->rutinas->inva_vida_patro = 0;
                $empleado->rutinas->inva_vida_obre = 0;
                $empleado->rutinas->guarde_presta = 0;
                $empleado->rutinas->sar_patron = 0;
                $empleado->rutinas->infonavit_patro = 0;
                $empleado->rutinas->censa_vejez_patron = 0;
                $empleado->rutinas->censa_vejez_obre = 0;
                $empleado->rutinas->censa_vejez_obre_patronal = 0;
            } else {

                if(
                    ($faltas==16 && strtoupper($periodo->nombre_periodo) =='QUINCENAL')  || 
                    ($faltas==15 && strtoupper($periodo->nombre_periodo) == 'QUINCENAL')  || 
                    ($faltas==7 && strtoupper($periodo->nombre_periodo) == 'SEMANAL') || 
                    ($faltas==14 && strtoupper($periodo->nombre_periodo) == 'CATORCENAL')
                ){
                    $SumaObre=0;
                }

                $empleado->rutinas->dias_imss                 = $dias_naturales;
                $empleado->rutinas->{'total'.$concepto_imss->id}  = $SumaObre;
				$empleado->conceptoI = $concepto_imss->id;
                $empleado->rutinas->cuota_fija                 = round($resu_cuota_fija_naturales, 6);
                $empleado->rutinas->exce_pa                   = $exce_patro_naturales;
                $empleado->rutinas->exce_ob                   = $exce_obrera;
                $empleado->rutinas->pre_dine_obre             = $pre_obrera;
                $empleado->rutinas->pre_dine_patro            = $pre_patronal_naturales;
                $empleado->rutinas->gas_medi_patro            = $gastos_patronal_naturales;
                $empleado->rutinas->gas_medi_obre             = round($gastos_obrera, 6);
                $empleado->rutinas->riesgo_trabajo            = round($riesgo_trabajo_naturales, 6);
                $empleado->rutinas->inva_vida_patro           = round($inva_patronal_natural, 6);
                $empleado->rutinas->inva_vida_obre            = round($inva_obrera, 6);
                $empleado->rutinas->guarde_presta             = round($guarde_social_naturales, 6);
                $empleado->rutinas->sar_patron                = round($sar_patron_naturales, 6);
                $empleado->rutinas->infonavit_patro           = round($infonavit_patron_naturales, 6);
                $empleado->rutinas->censa_vejez_patron        = round($CESANTIAYVEJEZnaturales, 6);
                $empleado->rutinas->censa_vejez_obre          = round($CESANTIAYVEJEZObrera, 6);
                $empleado->rutinas->censa_vejez_obre_patronal = round($CESANTIAYVEJEZObreraPatronalNaturales, 6);
                
            }
            
        }

    }

    protected function Default($concepto)
    {
        $periodo = $this->periodo;

        //dump('idconcepto: '.$concepto->id . ' - Rutina: '. $concepto->rutinas );
        $TipoConce = $concepto->tipo;
        $Filerool = $concepto->file_rool;
        $idconcepto = $concepto->id;
        $valorConcepto = 'valor'.$idconcepto;

        if($TipoConce == '0' && $Filerool < 250 && $Filerool != 0){
            
            foreach($this->empleados as &$empleado){

                $valorcon   = (!empty($empleado->rutinas->$valorConcepto)) ? round($empleado->rutinas->$valorConcepto, 4) : 0;
                $totalGrava = round($empleado->rutinas->total_gravado, 4);
                $totalneto  = round($valorcon + $totalGrava, 4);

                $empleado->rutinas->total_gravado = $totalneto;
                $empleado->rutinas->{'excento'.$idconcepto} = 0;
                $empleado->rutinas->{'total'.$idconcepto} = $valorcon;
                $empleado->rutinas->{'gravado'.$idconcepto} = $valorcon;
                
            }
        }

        foreach($this->empleados as &$empleado){

            $valorcon   = (!empty($empleado->rutinas->$valorConcepto)) ? round($empleado->rutinas->$valorConcepto, 4) : 0;
            $idrutina   = $empleado->rutinas->id;

            $empleado->rutinas->{'total'.$idconcepto} = $valorcon;
        }
    }

    protected function PTU()
    {
        $periodo = $this->periodo;
        if($periodo->especial == 1){

            $parametros_empresa = DB::connection('empresa')
            	->table('parametros')
            	->first();

            $uma = $parametros_empresa->uma;

            $concepto_ptu = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('rutinas', 'PTU')->where('estatus', 1)->first();
            $idptu        = ($concepto_ptu != null) ? intval($concepto_ptu->id) : 0;

            foreach($this->empleados as &$empleado){

                $colPTU = 'valor'.$idptu;
                $valorptu = (!empty($empleado->rutinas->$colPTU)) ? $empleado->rutinas->$colPTU : 0;

                if($valorptu > ($uma * 15)){
                    $parteGravadaPtu = $valorptu - ($uma * 15);
                    $parteExenta = $uma * 15;
                
                }else{
                    
                    $parteGravadaPtu = 0;
                    $parteExenta = $valorptu;
                }

                $TotalGrava = $empleado->rutinas->total_gravado + $parteGravadaPtu;
                if(Session::get('usuarioPermisos')['id_usuario']==64){
                //dump($parteGravadaPtu);
            }

                $empleado->rutinas->total_gravado = round($parteGravadaPtu, 4);
                $empleado->rutinas->{'total'.$idptu} = $valorptu;
                $empleado->rutinas->{'excento'.$idptu} = $parteExenta;
                $empleado->rutinas->{'gravado'.$idptu} = $parteGravadaPtu;                
            }
        }
    }

	protected function SumaObrero()
	{
	/*	
		if(Session::get('usuarioPermisos')['id_usuario']==64){
			
			foreach($this->empleados as &$empleado){
				if($empleado->validaSDO == 0){
					//$empleado->rutinas->['total'.$empleado->conceptoI]  = 600;
					$empleado->rutinas->{'total'.$empleado->conceptoI} = 0;
				}
			}
			dd($this->empleados);
        }
		*/
		foreach($this->empleados as &$empleado){
				if($empleado->validaSDO == 0){
					 //$empleado->rutinas->total_deduccion_fiscal = $empleado->rutinas->total_deduccion_fiscal - $empleado->rutinas->{'total'.$empleado->conceptoI};
					if($empleado->conceptoI != null){
						$empleado->rutinas->{'total'.$empleado->conceptoI} = 0;
					}
				}
			}
	}

    protected function ISPT()
    {
        $periodo = $this->periodo;
        $sueldo = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'SUELDO')->where('estatus', 1)->first();
        $idsuel = $sueldo->id;

		
		
        $isr = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'ISR')->where('estatus', 1)->first();
        $ispt = $isr->id;

        $imss = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'IMSS')->where('estatus', 1)->first();
        $IdImss = $imss->id;
/*
        $queryidDescF = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'DESCFISCALES')->where('estatus', 1)->first();
        $id_descfiscales = ($queryidDescF != null) ? intval($queryidDescF->id) : 0;
*/
		$queryidDescF = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('rutinas', 'DESCFISCALES')->where('estatus', 1)->first();
        $id_descfiscales = ($queryidDescF != null) ? intval($queryidDescF->id) : 0;
		
        foreach($this->empleados as &$empleado){

            $salDiario = $empleado->salario_diario;
            $TipoSindical = $empleado->tipo_sindical;
            $TipoFiscal = $empleado->tipo_fiscal;
            $TotalGravado = $empleado->rutinas->total_gravado;
        
            $qryImpuestos = "SELECT * from impuestos where $TotalGravado between limite_inferior and limite_superior  and tipo_tabla='$periodo->nombre_periodo' and estatus=1";
            
            if(Session::get('usuarioPermisos')['id_usuario']==64 && $empleado->id==3){
                //dd($qryImpuestos);
            }
            $impuestos = DB::connection('empresa')->select($qryImpuestos);

            if(count($impuestos) > 0){
                $limitInferior = $impuestos[0]->limite_inferior;
                $PorcentajeAplicar = $impuestos[0]->porcentaje;
                $CuotaFija = $impuestos[0]->cuota_fija;
            } else {
                $limitInferior = 0;
                $PorcentajeAplicar = 0;
                $CuotaFija = 0;
            }

            $qrySubsidios = "SELECT subsidio from subsidios where $TotalGravado between ingreso_desde and ingreso_hasta and tipo_tabla='$periodo->nombre_periodo' and estatus=1";
            
            
            $subsidios = DB::connection('empresa')->select($qrySubsidios);
            $CantidadSubsidio = (count($subsidios) > 0 ) ? $subsidios[0]->subsidio : 0;

            $ingresoExce      = $TotalGravado - $limitInferior;
            $impuestoMarginal = $ingresoExce * ($PorcentajeAplicar / 100);
            $isrretener       = $impuestoMarginal + $CuotaFija;

            if($periodo->especial == 1){
                $impuestoCa = $isrretener;
                $impuestoCargo = round($impuestoCa,2);
        
            }else{
                $impuestoCa = $isrretener - $CantidadSubsidio;
                $impuestoCargo = round($impuestoCa, 2);
            }

            $subsidio = ($impuestoCargo < 0) ? $impuestoCargo : 0;

//TODO AQUI
            $empleado->rutinas->{'total'.$ispt} = $impuestoCargo;
            $empleado->rutinas->subsidio_al_empleo = $CantidadSubsidio;
            $empleado->rutinas->subsidio = $subsidio;

            $totalIMSS = 'total'.$IdImss;
            $valorimss = $empleado->rutinas->$totalIMSS;

            if($id_descfiscales > 0){

                $valoradescontar = ($impuestoCargo > 0) ? ($impuestoCargo + $valorimss) : $valorimss;
                if($TipoSindical != 1 || $TipoFiscal != 1)
                    $valoradescontar = 0;

                $empleado->rutinas->{'total'.$id_descfiscales} = $valoradescontar;
            }
        }


        //--------------------------------------------------------------------------------------------------------
        $suma_percepciones = [];

        $percepciones = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 0)->where('activo_en_nomina', 1)->get();

        foreach($percepciones as $percepcion){

            $colPercepcion = 'total'.$percepcion->id;
            foreach($this->empleados as $empleado){
                
                if(isset($suma_percepciones[$empleado->id]))
                    $suma_percepciones[$empleado->id] += round($empleado->rutinas->$colPercepcion, 2);
                else
                    $suma_percepciones[$empleado->id] = round($empleado->rutinas->$colPercepcion, 2);
            }
        }
        

        foreach($this->empleados as &$empleado){

            $suma         = (!empty($suma_percepciones[$empleado->id])) ? $suma_percepciones[$empleado->id] : 0;
            $colSueldo    = 'total'.$idsuel;
            $sueldo       = (!empty($empleado->rutinas->$colSueldo)) ? $empleado->rutinas->$colSueldo : 0;

            $totalpercep2 = round($suma - $sueldo, 2);

            $empleado->rutinas->total_percepcion_fiscal = round($suma, 4);
            $empleado->rutinas->total_percepcion_fiscal2 = round($totalpercep2, 4);
        }
        //--------------------------------------------------------------------------------------------------------


        // sumaDeducciones.php ( produccion )
        $suma_deducciones = [];

        $deducciones = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 1)->where('activo_en_nomina', 1)->get();
        // dump($deducciones);

        foreach($deducciones as $deduccion){
            
            $colDeduccion = 'total'.$deduccion->id;
            foreach($this->empleados as $empleado){
                
                
                // if($empleado->id == 86)
                //     dump($colDeduccion.': '.round($empleado->rutinas->$colDeduccion, 4));

                if(isset($suma_deducciones[$empleado->id]))
                    $suma_deducciones[$empleado->id] += round($empleado->rutinas->$colDeduccion, 4);
                else
                    $suma_deducciones[$empleado->id] = round($empleado->rutinas->$colDeduccion, 4);
            }
        }

        // dump('Arr Deducciones:');
        // dump($suma_deducciones);

        foreach($this->empleados as &$empleado){

            $colIMSS      = 'total'.$IdImss;
            $IMSS         = (!empty($empleado->rutinas->$colIMSS)) ? $empleado->rutinas->$colIMSS : 0;

            $colISPT      = 'total'.$ispt; 
            $isr          = (!empty($empleado->rutinas->$colISPT)) ? $empleado->rutinas->$colISPT : 0;
            
            // if($empleado->id == 86)
            //     dump('columna ISR:' . $colISPT . ' - Valor: ' .$isr);

            $sumaDeduc    = (!empty($suma_deducciones[$empleado->id])) ? $suma_deducciones[$empleado->id]: 0;
            $sumaDeduc2   = $sumaDeduc - ($IMSS) - ($isr);

            // dump('Suma Deduc:'.$sumaDeduc2. ' - IMSS: '.$IMSS.' - ISPT: ' . $isr);
            
            $empleado->rutinas->total_deduccion_fiscal = round($sumaDeduc, 4);
            $empleado->rutinas->total_deduccion_fiscal2 = round($sumaDeduc2, 4);

        }

        //--------------------------------------------------------------------------------------------------------

        $suma_percepcion_sindical = [];
        // $empleados = $this->ActualizaValoresRutinas($periodo, $empleados);
        $percepcion_sindical = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where('nomina', 1)->where('tipo', 0)->where('activo_en_nomina', 1)                         
                                    ->where(function ($query) {
                                        $query->where('file_rool', 0)
                                            ->orWhere('file_rool', '>=', 250);
                                    })->get();


        foreach($percepcion_sindical as $ps){
            //dump($ps);
            $colPercepcion = 'total'.$ps->id;
            foreach($this->empleados as $empleado){

                // if($empleado->id == 86)
                //     dump($colPercepcion.': '.round($empleado->rutinas->$colPercepcion, 4));

                $totalesSindi  = round($empleado->rutinas->$colPercepcion, 2);

                if(isset($suma_percepcion_sindical[$empleado->id]))
                    $suma_percepcion_sindical[$empleado->id] += $totalesSindi;
                else
                    $suma_percepcion_sindical[$empleado->id] = $totalesSindi;
            }
        }
        //dd($suma_percepcion_sindical,$this->empleados);
        foreach($this->empleados as &$empleado){
            if(!empty($suma_percepcion_sindical[$empleado->id])){
                $empleado->rutinas->total_percepcion_sindical = round($suma_percepcion_sindical[$empleado->id], 4);
            }else{
                $empleado->rutinas->total_percepcion_sindical = 0;
            }
        }


        //--------------------------------------------------------------------------------------------------------

        $suma_deduccion_sindical = [];
        $deduccion_sindical = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where('nomina', 1)->where('tipo', 1)->where('activo_en_nomina', 1)                         ->where(function ($query) {
                                        $query->where('file_rool', 0)
                                            ->orWhere('file_rool', '>=', 250);
                                    })->get();

        if($deduccion_sindical->count() > 0){

            foreach($deduccion_sindical as $ds){

                foreach($this->empleados as $empleado){

                    $colDeduccion = 'total'.$ds->id;
                    $totalesSindi  = round($empleado->rutinas->$colDeduccion, 2);

                    if(isset($suma_deduccion_sindical[$empleado->id]))
                        $suma_deduccion_sindical[$empleado->id] += $totalesSindi;
                    else
                        $suma_deduccion_sindical[$empleado->id] = $totalesSindi;
                }
            }

            foreach($this->empleados as &$empleado){
                $empleado->rutinas->total_deduccion_sindical = round($suma_deduccion_sindical[$empleado->id], 4);
            }
        
        } else {

            foreach($this->empleados as &$empleado){
                $empleado->rutinas->total_deduccion_sindical = 0;
            }
        }
    }

    protected function INFONA_PAtron()
    {
        $periodo = $this->periodo;
        $dia_final_periodo   = date('d', strtotime($periodo->fecha_final_periodo));
        $mes_final_periodo   = date('m', strtotime($periodo->fecha_final_periodo));
        $ano_final_periodo   = date('Y', strtotime($periodo->fecha_final_periodo));

        if($dia_final_periodo == 28 || $dia_final_periodo == 29 || $dia_final_periodo == 31){
            $FechaFinal = $ano_final_periodo.'-'.$mes_final_periodo.'-30';
        } else {
            $FechaFinal = $periodo->fecha_final_periodo;
        }

        foreach($this->empleados as &$empleado){

            $fechaAlta     = $empleado->fecha_alta;
            $salarioDiario = (!empty($empleado->salario_diario)) ? $empleado->salario_diario : 0;

            if($fechaAlta > $periodo->fecha_inicial_periodo){

                $f1   = Carbon::parse($FechaFinal);
                $f2   = Carbon::parse($fechaAlta);
                $dias = $f1->diffInDays($f2) + 1;

            } else {
                
                $f1   = Carbon::parse($FechaFinal);
                $f2   = Carbon::parse($periodo->fecha_inicial_periodo);
                $dias = $f1->diffInDays($f2) + 1;
            }

            $sueld           = $dias * $salarioDiario;
            $sueldobase      = round($sueld, 4);
            $infonavitPat    = $sueldobase * 0.05;
            $infonavitpatron = round($infonavitPat * 1.05, 4);

            // dump('Dias: '.$dias.' - salarioDiario: '. $salarioDiario.' - sueldobase: ' . $sueldobase.' - infonavitpatron: '.$infonavitpatron);
            $empleado->rutinas->infonavit_patron = $infonavitpatron;
        }
    }

    /**
     * 
     */
    protected function Neto_Fiscal()
    {
        $periodo = $this->periodo;
        foreach ($this->empleados as &$empleado) {
            
            $TotalPercep    = round($empleado->rutinas->total_percepcion_fiscal, 4);
            $TotalDeduccion = round($empleado->rutinas->total_deduccion_fiscal, 4);
            $neto           = round($TotalPercep - $TotalDeduccion, 4);

            $empleado->rutinas->neto_fiscal = $neto;
        }
    }

    /**
     * 
     */
    protected function Neto_Fiscal_Sindical()
    {
        $periodo = $this->periodo;
        foreach ($this->empleados as &$empleado) {
            
            $TotalPercep    = round($empleado->rutinas->total_percepcion_sindical, 2);
            $TotalDeduccion = round($empleado->rutinas->total_deduccion_sindical, 2);
            $neto           = $TotalPercep - $TotalDeduccion;

            $empleado->rutinas->neto_sindical = $neto;
        }
    }

    protected function GuardarEmpleadosRutinas(){

        // dd($this->empleados[86]->rutinas);

        foreach ($this->empleados as $empleado) {

            $rutinas = collect($empleado->rutinas)->toArray();

            // if($empleado->id == 86)
                DB::connection('empresa')->table('rutinas'.$this->periodo->ejercicio)
                    ->where('id', $empleado->rutinas->id)
                    ->update($rutinas);

        }
    }
    protected function GuardarEmpleadosRutinasEmisoras($agrupar_emisoras){

        // dd($this->empleados[86]->rutinas);

        // dd($this->empleados[86]->rutinas);
        foreach ($agrupar_emisoras as $categoria){
            //dd($categoria);//aqui ya esta recibiendo a los
            foreach ($categoria['empleados']  as $empleado) {

            $rutinas = collect($empleado->rutinas)->toArray();

            // if($empleado->id == 86)
                DB::connection('empresa')->table('rutinas'.$this->periodo->ejercicio)
                    ->where('id', $empleado->rutinas->id)
                    ->update($rutinas);

            }
        }
    }

    protected function generarReporteNomina($deptos, $esCalculoNomina)
    {
        $periodo = $this->periodo;
        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();
            // dd($parametros_empresa);
        $rfc = Session::get('empresa')['rfc'];
        $tipodeNominaEmpresa = strtoupper($parametros_empresa->tipo_nomina);
        $ConcepFacturacion = $parametros_empresa->concepto_facturacion;
        $provisionAguinaldo = $parametros_empresa->provision_aguinaldo;
        $povisionPrimavacacional = $parametros_empresa->provision_prima_vacacional;
        $valorprovisionAgui = 0;
        $valorprovisionPrimvaca = 0;
        if($provisionAguinaldo==1 && $auxagui==0){

            $querysumaProvisionAgui = "SELECT sum(total_aguinaldo) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT idempleado from empleados where estatus = 1 and tipo_de_nomina='$periodo->nombre_periodo' and fechaAlta <= '$periodo->fecha_final_perido' and id_departamento in (".implode(',', $deptos)."))";
            $sumaProvisionAgui = DB::connection('empresa')->select($querysumaProvisionAgui);
            $valorprovisionAgui = $sumaProvisionAgui->result;

        }

        if($povisionPrimavacacional==1 && $auxprimvaca==0){

            $querysumaProvisionPrimvaca = "SELECT sum(total_prima_vacacional) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT idempleado from empleados where estatus = 1 and tipo_de_nomina='$periodo->nombre_periodo' and fechaAlta <= '$periodo->fecha_final_perido' and id_departamento in (".implode(',', $deptos)."))";
            $sumaProvisionPrimvaca = DB::connection('empresa')->select($querysumaProvisionPrimvaca);
            $valorprovisionPrimvaca = $sumaProvisionPrimvaca->result;
        }

        // Prima vacacioinal
        $concepto_prima_vacacional = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('rutinas', 'PVAC')
                                ->where(function ($query) {
                                    $query->where('file_rool', '<=', 249)
                                        ->orWhere('file_rool', '!=', 0);
                                })->first();
        // $rowsidpvac = $concepto_prima_vacacional->id;
        $misc['columnaPVAC'] = $concepto_prima_vacacional;
        // Prima vacacioinal ANTI
        $concepto_prvacacional_anti = DB::connection('empresa')->table('conceptos_nomina')->select('id')
        ->where('estatus', 1)
        ->where('activo_en_nomina', 1)
        ->where('rutinas', 'PVACANTI')
        ->where(function ($query) {
            $query->where('file_rool', '<=', 249)
            ->orWhere('file_rool', '!=', 0);
        })->first();
        // $rowsidpvacAnti = $concepto_prvacacional_anti->id;
        $misc['rowsidpvacAnti'] = $concepto_prvacacional_anti;
        
        
        // Prima vacacioinal
        $concepto_pr_dominical = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('rutinas', 'PRDOM')
                                ->where(function ($query) {
                                    $query->where('file_rool', '<=', 249)
                                        ->orWhere('file_rool', '!=', 0);
                                })->first();
        // $rowsidprdom = $concepto_pr_dominical->id;
        $misc['rowsidprdom'] = $concepto_pr_dominical;

        $sueldo_x_hr = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                            ->where('rutinas', 'SDOXHORA')
                            ->where('estatus', 1)
                            ->where('activo_en_nomina', 1)
                            ->first();
        
        $hrs_adicionales = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                            ->where('nombre_concepto', 'HORAS ADICIONALES')
                            ->where('estatus', 1)
                            ->where('activo_en_nomina', 1)
                            ->first();

        // Se asignan sedes
        $haySedes = false;
        if (Schema::connection('empresa')->hasTable('sedes')) {
            $sedes = DB::connection('empresa')->table('sedes')->get()->keyBy('id');
            if($sedes->count() > 0){
                $haySedes = true;
                foreach ($this->empleados as &$empleado) {
                    if(!empty($empleado->sede) && isset($sedes[$empleado->sede]))
                        $empleado->sede_nombre = $sedes[$empleado->sede]->nombre;
                    else
                        $empleado->sede_nombre = '';
                }
            }
        }
        $misc['haySedes'] = $haySedes;

        $columnas1 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 0)
                                ->where('file_rool', '<=', 249)
                                ->Where('file_rool', '!=', 0)
                                ->get()->keyBy('id');

        $columnas2 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto', 'rutinas')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 1)
                                ->where('file_rool', '<=', 249)
                                ->Where('file_rool', '!=', 0)
                                ->get()->keyBy('id');
                                
        $misc['faltas_s'] = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTAS_S')
                                ->where('tipo', 3)
                                ->first();


        $columnasSindical = collect([]);
        $rowConceptoDescuento75 = collect([]);
        $columnasDEDUCC = collect([]);
        $columnas3 = collect([]);

        if($tipodeNominaEmpresa=='SINDICAL' || $tipodeNominaEmpresa=='SOLOSINDICAL'){

            $columnasSindical = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 0)
                                ->where(function ($query) {
                                    $query->where('file_rool', '>=', 250)
                                        ->orWhere('file_rool', 0);
                                })->get();
                                
            $rowConceptoDescuento75 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'BENEFICIO SINDICAL + BONO FIJO 50%')
                                ->first();
            if($rowConceptoDescuento75){

                $queryConceptoBonoFijo = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'BONO FIJO')
                                ->first();
            }

            $columnasDEDUCC = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 1)
                                ->where(function ($query) {
                                    $query->where('file_rool', '>=', 250)
                                        ->orWhere('file_rool', 0);
                                })->get();

            // TODO: Revisar estos strings para que SIEMRPE sean los mismos en TODAS las empresas. Cotejar con PeriodosNominaController
            $columnas3 = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where(function ($query) {
                                        $query->where('rutinas', 'INFONAzz')
                                            ->orWhere('rutinas', 'FONACOTssss')
                                            ->orWhere('rutinas', 'PENSIONsss')
                                            ->orWhere('rutinas', 'Credito infonavitsssss');
                                    })->get();
        }


        // TODO: revisar este funcionamiento
        // $acum = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', 0)->count();
/*
        $validaconcepFaltaSin = DB::connection('generica')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTASS')
                                ->get();
                                */
        $validaconcepFaltaSin = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTAS_S')
                                ->get();
        
        $rowvalidapremioA = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('nombre_concepto', 'PREMIO DE ASISTENCIA')
                                ->where('estatus', 1)->where('activo_en_nomina', 1)
                                ->first();

        $rowvalidapremioP = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('nombre_concepto', 'PREMIO DE PUNTUALIDAD')
                                ->where('estatus', 1)->where('activo_en_nomina', 1)
                                ->first();


        foreach($this->empleados as &$empleado){
            
            $empleado->numero_empleado = (!empty($empleado->numero_empleado)) ? $empleado->numero_empleado : $empleado->id; // *************************************
            $incapacidades = $empleado->dias_incapacidad;
            $faltas = $empleado->faltas;
            $dias = $periodo->dias_periodo;

            $FechaFinal   = $periodo->fecha_final_periodo;
            $fechaIniPeri = $periodo->fecha_inicial_periodo;
            $diafechafinal = date('d', strtotime($periodo->fecha_final_periodo));
            $mesfechafinal = date('m', strtotime($periodo->fecha_final_periodo));
            $anofechafinal = date('Y', strtotime($periodo->fecha_final_periodo));

            $fin_del_periodo = $periodo->fecha_final_periodo;
            if(intval($mesfechafinal) == 2 && intval($diafechafinal) > 27){
                $fin_del_periodo = date('Y-m-t',strtotime($fechaIniPeri));
            }

            $fecha_final_periodo = Carbon::parse($fin_del_periodo);
            $fecha_alta = Carbon::parse($empleado->fecha_alta);
            $DiasNom = $fecha_final_periodo->diffInDays($fecha_alta) + 1;
            
            if($validaconcepFaltaSin->count() > 0){
                $idconceptoFaltasSin = $validaconcepFaltaSin[0]->id;

                //$valorfaltasSin = $empleado->rutinas->{'valor'.$idconceptoFaltasSin};
                $valorfaltasSin = ($empleado->rutinas->{'valor'.$idconceptoFaltasSin}!="" && $empleado->rutinas->{'valor'.$idconceptoFaltasSin} != null)?$empleado->rutinas->{'valor'.$idconceptoFaltasSin}:0;
            }

            $valorHoras = ($sueldo_x_hr) ? $empleado->rutinas->{'valor'.$sueldo_x_hr->id} : 0;
            $DiasPagadosHoras=0;

            if($empleado->fecha_alta > $periodo->fecha_inicial_periodo){

                $DiasPagados = $DiasNom - $incapacidades - $faltas ;

                if($validaconcepFaltaSin->count()>0){

                    $DiasPagados01 = $DiasNom - $incapacidades - $valorfaltasSin;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                }else{

                    $DiasPagados01 = $DiasNom - $incapacidades - $faltas;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($faltas*8);
                }
                $FaltasHoras=$DiasPagados01-($DiasPagados01xhora/8);
                $DiasPagadosHoras=$DiasPagados01xhora/8;
                
            } else {

                $DiasPagados = ($dias - $incapacidades - $faltas);

                if($validaconcepFaltaSin->count() > 0){

                    $DiasPagados01 = $dias - $incapacidades - $valorfaltasSin;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                }else{
                    $DiasPagados01 = $dias - $incapacidades - $faltas;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($faltas*8);
                }
                $FaltasHoras=$DiasPagados01-($DiasPagados01xhora/8);
                $DiasPagadosHoras=$DiasPagados01xhora/8;
                
            }
            if($FaltasHoras<0){
                $FaltasHoras=0;
            }
            if($DiasPagadosHoras>$DiasPagados01){
                $DiasPagadosHoras=$DiasPagados01;
            }
            $valoresHoras[$empleado->id]=$FaltasHoras;
            $valorDiasPHoras[$empleado->id]=$DiasPagadosHoras;

            
            $empleado->dias_pagados = $DiasPagados;

            $sueldoreal = $empleado->sueldo_neto / $dias;

            if($periodo->especial == 1){
                $importeBeneficio=0;

            } else {
                if(Session::get('base')=='empresa000046'){
                    $DIASBENEFICIOSINDI=$DiasPagados01+$incapacidades;
                }else{
                    $DIASBENEFICIOSINDI=$DiasPagados01;
                }

                $importeBeneficio = (($sueldoreal * $DIASBENEFICIOSINDI) - $empleado->rutinas->neto_fiscal) - $empleado->rutinas->total_deduccion_fiscal2 + $empleado->rutinas->total_percepcion_fiscal2;
                // if($empleado->id == 86)
                //     dd($importeBeneficio);

                 if(Session::get('usuarioPermisos')['id_usuario']==64 && $empleado->id==42){
                    //dd($empleado->rutinas->total_percepcion_sindical,$empleado->rutinas->total_deduccion_sindical,$DiasPagados01,$importeBeneficio);
                }
                
                
                if($sueldo_x_hr){
                    
                    $sueldorealxhora = $sueldoreal / 8;
                    $importeBeneficio = (($sueldorealxhora * $DiasPagados01xhora) - $empleado->rutinas->neto_fiscal) - $empleado->rutinas->total_deduccion_fiscal2 + $empleado->rutinas->total_percepcion_fiscal2;
                    if($hrs_adicionales){
                        $valorHrsAdicionales = $empleado->rutinas->{'valor'.$hrs_adicionales->id};
                        $importeBeneficio = $importeBeneficio-$valorHrsAdicionales;
                    }
                }

                $valorpremioA=0;
                $valorpremioP=0;

                if($rowvalidapremioA){
                    $valorpremioA = $empleado->rutinas->{'valor'.$rowvalidapremioA->id};
                }

                if($rowvalidapremioP){
                    $valorpremioP = $empleado->rutinas->{'valor'.$rowvalidapremioP->id};
                }

                $ValoreNulos = $valorpremioA + $valorpremioP;
                $importeBeneficio = $importeBeneficio - $ValoreNulos;
                if($importeBeneficio < 0){
                    $importeBeneficio=0;
                }
                if($empleado->tipo_fiscal==1 && $empleado->tipo_sindical==0){
                    $importeBeneficio=0;
                }
                if($tipodeNominaEmpresa == 'SOLOSINDICAL'){
                    $importeBeneficio = $sueldoreal * $DiasPagados01;
                    if($importeBeneficio < 0){
                        $importeBeneficio = 0;
                    }
                }
                
                // if($empleado->id == 86)
                //     dd($importeBeneficio);

                $empleado->rutinas->beneficio_sindical = $importeBeneficio; // ***********************************************
            }

            if($rowConceptoDescuento75 && $rowConceptoDescuento75->count() > 0)
            {
                $valorBono = 0;
                $valorBono75 = 0;
                //dd($queryConceptoBonoFijo);
                if($queryConceptoBonoFijo){
                    $valorBono = $empleado->rutinas->{'total'.$queryConceptoBonoFijo->id};
                    $valorBono75 = $valorBono * 0.50;
                }

                $importeBeneficio75 = $importeBeneficio * 0.50;
                $TotalpercepcionSindical = $empleado->rutinas->total_percep_sindical - $valorBono;
                $beneficioSindical75 = $importeBeneficio75 + $valorBono75;

                $empleado->rutinas->{'total'.$rowConceptoDescuento75->id} = $beneficioSindical75; // ******************************************

                $TotalpercepcionSindical = $TotalpercepcionSindical + $empleado->rutinas->bono_prima + $beneficioSindical75 + $empleado->rutinas->bono_prima_dom ;
            } else {
                
                $TotalpercepcionSindical = floatval($empleado->rutinas->total_percepcion_sindical) + floatval($importeBeneficio) + floatval($empleado->rutinas->bono_prima) + floatval($empleado->rutinas->bono_prima_dom);
            }

            if($esCalculoNomina)
                $empleado->rutinas->total_percepcion_sindical = $TotalpercepcionSindical; // ***********************************************

            $sumadeduccion = 0;
            if($columnas3->count() > 0){

                $saldosNomina = DB::connection('empresa')->table('saldo_nomina')->select('valor_concepto')
                                ->where('id_periodo', $periodo->id)
                                ->where('saldo', '<', 0)
                                ->where('id_empleado', $empleado->id)
                                ->whereIn('id_concepto', $columnas3->pluck('id')->toArray())
                                ->get()->keyBy('id_concepto');

                foreach($columnas3 as $col){
                    if(isset($saldosNomina[$col->id])){
                        $empleado->saldosNomina[$col->id] = $saldosNomina[$col->id]->valor_concepto;
                        $sumadeduccion += $saldosNomina[$col->id]->valor_concepto;
                    }
                    else{
                        $empleado->saldosNomina[$col->id] = $empleado->rutinas->{'total'.$col->id};
                        $sumadeduccion += $empleado->rutinas->{'total'.$col->id};
                    }
                }
            }

            $empleado->rutinas->total_deduccion_sindical += $sumadeduccion; // ************************************ 
            $empleado->rutinas->neto_sindical = $empleado->rutinas->total_percepcion_sindical - $empleado->rutinas->total_deduccion_sindical; // ************************************ 

           

            $TotalBeneficio = $importeBeneficio - $empleado->rutinas->total_deduccion_sindical;
            if($tipodeNominaEmpresa=='SOLOSINDICAL')
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_sindical;
            else if($tipodeNominaEmpresa=='FISCAL')
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal;
            else
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal + $empleado->rutinas->neto_sindical;  // ************************************ 
            
            if($tipodeNominaEmpresa != 'SINDICAL' && $tipodeNominaEmpresa != 'SOLOSINDICAL'){
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal; // **********************************
                $TotalpercepcionSindical=0;
                $totalbeneficioSindical=0;
            }
            
        }
       // dd($this->empleados);

        $misc['rowConceptoDescuento75'] = $rowConceptoDescuento75;
        $misc['sueldo_x_hr'] = $sueldo_x_hr;

        /******************************** FIN PRIMERA TABLA ****************************** */


        $this->GuardarEmpleadosRutinas();


        $porcentajeHono       = $parametros_empresa->porcentaje_honorarios;
        $ConcepFacturacion    = $parametros_empresa->concepto_facturacion;
        $anti                 = $parametros_empresa->anticipo;
        $comisionMismo        = $parametros_empresa->comision_mismo_dia;
        $provisionObrero      = $parametros_empresa->provision_obrero;
        $provisionPorcentaje  = $parametros_empresa->provision_porcentaje;
		$rcv  = $parametros_empresa->rvc_patronal_obrero;
        //Rene
        $comisionVariable     = ($parametros_empresa->comision_variable != "")?$parametros_empresa->comision_variable:0;
        $PocentajeNomina      = $parametros_empresa->porcentaje_nomina;
        $Iva                  = $parametros_empresa->iva;
        $valorPrestacionExtra = ($parametros_empresa->valor_prestacion_extra!="")?$parametros_empresa->valor_prestacion_extra:0;

        $concepto_imss       = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'IMSS')->where('estatus', 1)->first();
        $IdImss              = $concepto_imss->id;

        $valorhonorarios=$porcentajeHono/100;

        $querySumas = "SELECT sum(neto_fiscal) as suma_neto_fiscal, sum(total$IdImss) as suma_imss, sum(total_percepcion_fiscal) as suma_tpf, sum(total_percepcion_sindical) as suma_tps, sum(subsidio) as suma_subsidio, sum(beneficio_sindical) as suma_bs, sum(cuota_fija) as suma_cuotaf, sum(exce_pa) as suma_exce_pa, sum(exce_ob) as suma_exce_ob, sum(pre_dine_obre) as suma_pre_dine_obre, sum(pre_dine_patro) as suma_pre_dine_patro, sum(censa_vejez_obre_patronal) as suma_censa_vejez_obre_patronal, sum(gas_medi_patro) as suma_gas_medi_patro, sum(gas_medi_obre) as suma_gas_medi_obre, sum(riesgo_trabajo) as suma_riesgo_trabajo, sum(inva_vida_patro) as suma_inva_vida_patro, sum(inva_vida_obre) as suma_inva_vida_obre, sum(guarde_presta) as suma_guarde_presta, sum(censa_vejez_obre) as suma_censa_vejez_obre, sum(censa_vejez_patron) as suma_censa_vejez_patron, sum(infonavit_patro) as suma_infonavit_patro, sum(sar_patron) as suma_sar_patron from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id where em.estatus=1 and ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_periodo' and em.id_departamento in (".implode(',', $deptos).")";

        if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($querySumas);
                }
        
        $rowresultnetofiscal     = DB::connection('empresa')->select($querySumas)[0]; 
        $netofiscal              = $rowresultnetofiscal->suma_neto_fiscal;
        $sumaObrera             = $rowresultnetofiscal->suma_imss;
        $PercepFiscal           = $rowresultnetofiscal->suma_tpf;
        $valorSubsidio          = $rowresultnetofiscal->suma_subsidio * -1;
        $BeneficioSindical       = $rowresultnetofiscal->suma_bs;
        $CuotaFija              = round($rowresultnetofiscal->suma_cuotaf, 2) * $provisionPorcentaje;
        $ExcePa                 = round($rowresultnetofiscal->suma_exce_pa, 2) * $provisionPorcentaje;
        $ExceOb                 = round($rowresultnetofiscal->suma_exce_ob, 2) * $provisionPorcentaje;
        $PreDineObre            = round($rowresultnetofiscal->suma_pre_dine_obre, 2) * $provisionPorcentaje;
        $PreDineroPa            = round($rowresultnetofiscal->suma_pre_dine_patro, 2) * $provisionPorcentaje;
        $CensaVejezObrePatronal = round($rowresultnetofiscal->suma_censa_vejez_obre_patronal, 2);
        $GasMediPatron          = round($rowresultnetofiscal->suma_gas_medi_patro, 2) * $provisionPorcentaje;
        $GasMediObre            = round($rowresultnetofiscal->suma_gas_medi_obre, 2) * $provisionPorcentaje;
        $RiesgoTrabajo          = round($rowresultnetofiscal->suma_riesgo_trabajo, 2) * $provisionPorcentaje;
        $InvaVidaPatro          = round($rowresultnetofiscal->suma_inva_vida_patro, 2) * $provisionPorcentaje;
        $InvaVidaObre           = round($rowresultnetofiscal->suma_inva_vida_obre, 2) * $provisionPorcentaje;
        $GuardePresta           = round($rowresultnetofiscal->suma_guarde_presta, 2) * $provisionPorcentaje;
        $CensaVejezObre         = round($rowresultnetofiscal->suma_censa_vejez_obre, 2) * $provisionPorcentaje;
        $CensaVejezPatro        = round($rowresultnetofiscal->suma_censa_vejez_patron, 2) * $provisionPorcentaje;
        $InfonavitPatro         = round($rowresultnetofiscal->suma_infonavit_patro, 2) * $provisionPorcentaje;
        $SarPatron              = round($rowresultnetofiscal->suma_sar_patron, 2) * $provisionPorcentaje;
        $totalbeneficioSindical  = $rowresultnetofiscal->suma_tps;

        $netoFiscalreal = ($tipodeNominaEmpresa=='SOLOSINDICAL') ? 0 : ($PercepFiscal+$valorSubsidio);

        if($concepto_prima_vacacional){

            $cadena_empleados = $this->empleados->pluck('id')->toArray();
            $query_pVac = "SELECT sum(total$concepto_prima_vacacional->id) as result from rutinas$periodo->ejercicio where fnq_valor=0 and id_periodo='$periodo->id' and id_empleado in (select id from empleados where tipo_de_nomina = '$periodo->nombre_periodo' and id in (".implode(',', $cadena_empleados)."))";

            $total_valor_prima = DB::connection('empresa')->select($query_pVac); 
            $pagoprimavaca = $total_valor_prima[0]->result;
        } else {
            $pagoprimavaca = 0.0;
        }
       // dump($netoFiscalreal , $totalbeneficioSindical , $anticipo , $vacaciones , $comisionmismodia , $valorprovisionAgui , $valorprovisionPrimvaca);

        /*validacionnes*/
        $netoFiscalreal = ($netoFiscalreal!="")?$netoFiscalreal:0;
        $totalbeneficioSindical = ($totalbeneficioSindical!="")?$totalbeneficioSindical:0;
        $valorprovisionAgui  =($valorprovisionAgui!="")?$valorprovisionAgui:0;
        $valorprovisionPrimvaca = ($valorprovisionPrimvaca !="" )?$valorprovisionPrimvaca:0;
        
        $anticipo         = ($anti != "")?$anti:0;
        $vacaciones       = 0.0;
        $comisionmismodia = ($comisionMismo != "")?$comisionMismo:0;
        // dd($comisionmismodia);

        $TotalpagarNomina = $netoFiscalreal + $totalbeneficioSindical + $anticipo + $vacaciones + $comisionmismodia + $valorprovisionAgui + $valorprovisionPrimvaca;
        $pagoHonorarios   = $TotalpagarNomina*$valorhonorarios;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $ExceObAdicional = $ExceOb*$ExcedenteObreraPatronal;
        }else{
            $ExceObAdicional=0;
        }
        $ExcePa = $ExcePa + $ExceObAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $PrePatroAdicional = round($PreDineObre,2) * $ExcedenteObreraPatronal;
        }else{
            $PrePatroAdicional = 0;
        }
        $PreDineroPa = $PreDineroPa + $PrePatroAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero / 100;
            $GasMediPatronAdicional = $GasMediObre * $ExcedenteObreraPatronal;
        }else{
            $GasMediPatronAdicional = 0;
        }
        $GasMediPatron = $GasMediPatron + $GasMediPatronAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $InvaVidaPatroAdicional = $InvaVidaObre * $ExcedenteObreraPatronal;
			
        }else{
            $InvaVidaPatroAdicional = 0;
        }
        $InvaVidaPatro = $InvaVidaPatro + $InvaVidaPatroAdicional;
		if($rcv > 0){
            $CensaVejezPatro = $CensaVejezPatro + $CensaVejezObre;
        }else{
            $CensaVejezPatro = $CensaVejezPatro;
        }
        //$CensaVejezPatro = $CensaVejezPatro + $CensaVejezObre;
        $porcentajenom   = $PocentajeNomina/100;
        $errogacion      = $PercepFiscal*$porcentajenom;

        $cadena_empleados = implode(',', $this->empleados->pluck('id')->toArray());
        $queryNumEmple = "SELECT * from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and pre.estatus=1 and em.id in ($cadena_empleados)";
       
        $empleados_2 = DB::connection('empresa')->select($queryNumEmple);

        //$numeroEmple = (isset($empleados_2[0])) ? $empleados_2[0]->count() : 0;
        $numeroEmple = (isset($empleados_2[0])) ? \count($empleados_2) : 0;
		
        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valor_seguro_GM, sum(pre.valor_plan_espejo) as valor_plan_espejo from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and em.id in ($cadena_empleados)";
        $seguros         = DB::connection('empresa')->select($queryvalorseguroGM);
        $valorseguroGM   = ($seguros[0]->valor_seguro_GM)? $seguros[0]->valor_seguro_GM: 0;
        $ValorPlanEspejo = ($seguros[0]->valor_plan_espejo)? $seguros[0]->valor_plan_espejo:0;
		
        $prestacionesExtras = ($valorPrestacionExtra * $numeroEmple) + $valorseguroGM + $ValorPlanEspejo;
		
        $total              = $prestacionesExtras+$CuotaFija+$ExcePa+$PreDineroPa+$GasMediPatron+$RiesgoTrabajo+$InvaVidaPatro+$GuardePresta+ $SarPatron+$CensaVejezPatro+$InfonavitPatro+$errogacion;
        $cargasocial        = $CuotaFija+$ExcePa+$PreDineroPa+$GasMediPatron+$RiesgoTrabajo+$InvaVidaPatro+$GuardePresta+$SarPatron+$CensaVejezPatro+$InfonavitPatro;
        $subtotal           = $TotalpagarNomina+$pagoHonorarios+$total;
        $iva                = $subtotal*$Iva;
        $totalmayor         = $subtotal+$iva;
        $comision           = $netoFiscalreal+$cargasocial+$errogacion;
        
        $valorcomision      = $comision*($comisionVariable/100);
        $subtotal02         = $netoFiscalreal+$cargasocial+$errogacion+$valorcomision;
        $iva02              = $subtotal02*$Iva;
        $totalmayor02       = $subtotal02+$iva02;
        $asesoriaContable   = $subtotal-$subtotal02;
        $iva03              = $asesoriaContable*$Iva;
        $totalmayor03       = $iva03+$asesoriaContable;


        $table_name = 'datos_facturacion'.$this->periodo->ejercicio;
        $id = $this->verificarTablaFacturacion($table_name);

        DB::connection('empresa')->table($table_name)->where('id', $id)->update([
            'nomina' => $netoFiscalreal,
            'beneficio_sindical' => $totalbeneficioSindical,
            'anticipo' => $anticipo,
            'vacaciones' => $vacaciones,
            'pago_prima_vaca' => $pagoprimavaca,
            'comision_mismo_dia' => $comisionmismodia,
            'total_pago_nomina' => $TotalpagarNomina,
            'porcentaje_honorarios' => $porcentajeHono,
            'valores_honorarios' => $pagoHonorarios,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'ejercicio' => $periodo->ejercicio,
            'costos_patronales' => $total,
            'detalle_subtotal' => $subtotal,
            'detalle_iva' => $iva,
            'detalle_total' => $totalmayor,
            'prestaciones_extras' => $prestacionesExtras,
            'cuota_fija' => $CuotaFija,
            'exc_cf' => $ExcePa,
            'presta_dinero' => $PreDineroPa,
            'gastos_medi_pensionados' => $GasMediPatron,
            'riesgo_trabajo' => $RiesgoTrabajo,
            'invalidez_y_vida' => $InvaVidaPatro,
            'guarderias_y_pre_sociales' => $GuardePresta,
            'cuotas_imss_retiro' => $SarPatron,
            'cuotas_imss_censatiaV' => $CensaVejezPatro,
            'cred_vivienda' => $InfonavitPatro,
            'porcentaje_errogaciones' => $PocentajeNomina,
            'valor_errogaciones' => $errogacion,
            'totalcostos_patronales' => $total,
        ]);

        $totales = [
            'neto_fiscal_real'                  => $netoFiscalreal,
            'total_percepcion_sindical'        => $totalbeneficioSindical,
            'valor_provision_aguinaldo'        => $valorprovisionAgui,
            'valor_provision_prima_vacacional' => $valorprovisionPrimvaca,
            'anticipo'                         => $anticipo,
            'vacaciones'                       => $vacaciones,
            'comision_mismo_dia'               => $comisionmismodia,
            'total_pagar_nomina'               => $TotalpagarNomina,
            'pago_honorarios'                  => $pagoHonorarios,
            'total'                            => $total,
            'subtotal'                         => $subtotal,
            'iva'                              => $iva,
            'total_mayor'                      => $totalmayor,
            
            'prestaciones_extras' => $prestacionesExtras,
            'cuota_fija'           => $CuotaFija,
            'exc_cf'              => $ExcePa,
            'pre_dinero_pa'       => $PreDineroPa,
            'gas_medi_patron'     => $GasMediPatron,
            'riesgo_trabajo'      => $RiesgoTrabajo,
            'inva_vida_patro'     => $InvaVidaPatro,
            'guarde_presta'       => $GuardePresta,
            'sar_patron'          => $SarPatron,
            'censa_vejez_patron'  => $CensaVejezPatro,
            'infonavit_patro'     => $InfonavitPatro,
            'errogacion'          => $errogacion,
            'porcentaje_nomina'   => $PocentajeNomina,

            'carga_social'      => $cargasocial,
            'comision_variable' => $comisionVariable,
            'valor_comision'    => $valorcomision,
            'subtotal02'        => $subtotal02,
            'iva02'             => $iva02,
            'total_mayor02'     => $totalmayor02,

            'concepto_facturacion' => $ConcepFacturacion,
            'asesoria_contable'    => $asesoriaContable,
            'iva03'                => $iva03,
            'total_mayor03'        => $totalmayor03,

        ];

        // $cargasocial   = $cargasocial+$prestacionesExtras;
        // $valorcomision = $TotalpagarNomina*($comisionVariable/100);
        $subtotal002    = $TotalpagarNomina+$cargasocial+$errogacion+$valorcomision;
        // $iva02         = $subtotal02*$Iva;
        // $totalmayor02  = $subtotal02+$iva02;

        DB::connection('empresa')->table($table_name)->where('id', $id)->update([
            'suministro_per1_1' => $netoFiscalreal,
            'carga_social1_1' => $cargasocial,
            'porcentajes_nomina' => $PocentajeNomina,
            'porcentaje_comisionV' => $valorcomision,
            'subtotal_depo1_1' => $subtotal02,
            'iva_depo1_1' => $iva02,
            'total_depo1_1' => $totalmayor02,
        ]);        

        $queryEmisoras = "SELECT em.id, id_categoria, ememi.razon_social as razon, group_concat(em.id) as cadenaemple, ememi.id as CadenaID_EmpresaE from empleados em join categorias cat on  em.id_categoria = cat.id inner join singh.registro_patronal regpat on cat.tipo_clase = regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora = ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.estatus=1 and id_departamento in (".implode(',', $deptos).") group by ememi.razon_social";
        
        $emisoras = DB::connection('empresa')->select($queryEmisoras);

        if($emisoras){
            DB::connection('empresa')->table($table_name)->where('id', $id)->update(['cadena_emisoras' => $emisoras[0]->CadenaID_EmpresaE]);
        }



        if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical'){

            $queryEmisoras = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id in ($cadena_empleados) group by ememi.razon_social";
            $emisoras = DB::connection('empresa')->select($queryEmisoras);

            if(count($emisoras) > 1){
                $i = 0;

                foreach ($emisoras as $emisora ) {
                    $i++;
                    
                    $queryPercepFiscalEmisoras = "SELECT sum(total_percepcion_fiscal) as suma from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_perido' and em.id in ($emisora->cadena_empleados)";
                    $percep_fiscal_emisoras = DB::connection('empresa')->select($queryPercepFiscalEmisoras);

                    //$percep_fiscal_emisoras = ($percep_fiscal_emisoras[0]['suma'] == null)?0:$percep_fiscal_emisoras[0]['suma'];                    
                    //$errogacion_emisora=$percep_fiscal_emisoras * $porcentaje_nom;
                    $percep_fiscal_emisoras = ($percep_fiscal_emisoras[0]->suma == null)?0:$percep_fiscal_emisoras[0]->suma;
                    $errogacion_emisora=$percep_fiscal_emisoras * $PocentajeNomina;

                    $queryvaloresFacturacion = "SELECT suministro_per1_$i as suministro_per, carga_social1_$i as carga_social, porcentajes_nomina, porcentaje_comisionV, subtotal_depo1_$i as subtotal_depo, iva_depo1_$i as iva_depo, total_depo1_$i as total_depo, valor_sobre_nomina1_$i as valor_sobre_nomina , valor_comision_variable1_$i as valor_comision_variable from datos_facturacion$periodo->ejercicio where id_periodo = '$periodo->id'";
                    $valores_facturacion = DB::connection('empresa')->select($queryvaloresFacturacion);
                    if(is_object($valores_facturacion[0])){
                        $valores_facturacion[0] = (array)$valores_facturacion[0];
                    }
                    $totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] = $valores_facturacion[0]['suministro_per'];
                    $totales[$emisora->id_empresa_emisora]['carga_social'] = $valores_facturacion[0]['carga_social'];
                    $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] = $valores_facturacion[0]['porcentajes_nomina'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision'] = $valores_facturacion[0]['porcentaje_comisionV'];
                    $totales[$emisora->id_empresa_emisora]['subtotal02'] = $valores_facturacion[0]['subtotal_depo'];
                    $totales[$emisora->id_empresa_emisora]['iva02'] = $valores_facturacion[0]['iva_depo'];
                    $totales[$emisora->id_empresa_emisora]['total_mayor02'] = $valores_facturacion[0]['total_depo'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] = $valores_facturacion[0]['valor_comision_variable'];
                    $totales[$emisora->id_empresa_emisora]['errogacion_emisora'] = $errogacion_emisora;
                }
            }

        }



        return compact('misc', 'columnas1', 'columnas2', 'columnasSindical', 'columnasDEDUCC', 'columnas3', 'totales', 'emisoras','valoresHoras','valorDiasPHoras');
    }

    protected function verificarTablaFacturacion($table_name)
    {

        // Verificamos si existe la tabla
        if (!Schema::connection('empresa')->hasTable($table_name)) {

            Schema::connection('empresa')->create($table_name, function (Blueprint $table){

                $table->increments('id');
                
                $table->integer('id_periodo');
                $table->string('ejercicio', 20)->nullable();
                $table->dateTime('fecha_creacion');


                $table->string('nomina', 20)->nullable();
                $table->string('beneficio_sindical', 20)->nullable();
                $table->string('anticipo', 20)->nullable();
                $table->string('vacaciones', 20)->nullable();
                $table->string('pago_prima_vaca', 20)->nullable();
                $table->string('comision_mismo_dia', 20)->nullable();
                $table->string('total_pago_nomina', 20)->nullable();
                $table->string('porcentaje_honorarios', 20)->nullable();
                $table->string('valores_honorarios', 20)->nullable();
                $table->string('costos_patronales', 20)->nullable();
                $table->string('detalle_subtotal', 20)->nullable();
                $table->string('detalle_iva', 20)->nullable();
                $table->string('detalle_total', 20)->nullable();
                $table->string('prestaciones_extras', 20)->nullable();
                $table->string('cuota_fija', 20)->nullable();
                $table->string('exc_cf', 20)->nullable();
                $table->string('presta_dinero', 20)->nullable();
                $table->string('gastos_medi_pensionados', 20)->nullable();
                $table->string('riesgo_trabajo', 20)->nullable();
                $table->string('invalidez_y_vida', 20)->nullable();
                $table->string('guarderias_y_pre_sociales', 20)->nullable();
                $table->string('cuotas_imss_retiro', 20)->nullable();
                $table->string('cuotas_imss_censatiaV', 20)->nullable();
                $table->string('cred_vivienda', 20)->nullable();
                $table->string('porcentaje_errogaciones', 20)->nullable();
                $table->string('valor_errogaciones', 20)->nullable();
                $table->string('totalcostos_patronales', 20)->nullable();
                $table->string('carga_social1_1', 20)->nullable();
                $table->string('carga_social1_2', 20)->nullable();
                $table->string('carga_social1_3', 20)->nullable();
                $table->string('carga_social1_4', 20)->nullable();
                $table->string('carga_social1_5', 20)->nullable();
                $table->string('cadena_emisoras', 20)->nullable();
                $table->string('porcentajes_nomina', 20)->nullable();
                $table->string('suministro_per1_1', 20)->nullable();
                $table->string('suministro_per1_2', 20)->nullable();
                $table->string('suministro_per1_3', 20)->nullable();
                $table->string('suministro_per1_4', 20)->nullable();
                $table->string('suministro_per1_5', 20)->nullable();
                $table->string('porcentaje_comisionV', 20)->nullable();
                $table->string('subtotal_depo1_1', 20)->nullable();
                $table->string('subtotal_depo1_2', 20)->nullable();
                $table->string('subtotal_depo1_3', 20)->nullable();
                $table->string('subtotal_depo1_4', 20)->nullable();
                $table->string('subtotal_depo1_5', 20)->nullable();
                $table->string('iva_depo1_1', 20)->nullable();
                $table->string('iva_depo1_2', 20)->nullable();
                $table->string('iva_depo1_3', 20)->nullable();
                $table->string('iva_depo1_4', 20)->nullable();
                $table->string('iva_depo1_5', 20)->nullable();
                $table->string('total_depo1_1', 20)->nullable();
                $table->string('total_depo1_2', 20)->nullable();
                $table->string('total_depo1_3', 20)->nullable();
                $table->string('total_depo1_4', 20)->nullable();
                $table->string('total_depo1_5', 20)->nullable();
                $table->string('concepto', 20)->nullable();
                $table->string('valor_concepto', 20)->nullable();
                $table->string('subtotal_depo2', 20)->nullable();
                $table->string('iva_depo2', 20)->nullable();
                $table->string('total_depo2', 20)->nullable();
                $table->string('valor_sobre_nomina1_1', 20)->nullable();
                $table->string('valor_sobre_nomina1_2', 20)->nullable();
                $table->string('valor_sobre_nomina1_3', 20)->nullable();
                $table->string('valor_sobre_nomina1_4', 20)->nullable();
                $table->string('valor_sobre_nomina1_5', 20)->nullable();
                $table->string('valor_comision_variable1_1', 20)->nullable();
                $table->string('valor_comision_variable1_2', 20)->nullable();
                $table->string('valor_comision_variable1_3', 20)->nullable();
                $table->string('valor_comision_variable1_4', 20)->nullable();
                $table->string('valor_comision_variable1_5', 20)->nullable();
            });
        }

        $existe = DB::connection('empresa')->table($table_name)->select('id')->where('id_periodo', $this->periodo->id)->first();

        if(!$existe){

            $id = DB::connection('empresa')->table($table_name)->insertGetId([
                'id_periodo' => $this->periodo->id,
                'ejercicio' => $this->periodo->ejercicio,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
            return $id;

        } else {
            return $existe->id;
        }
    }

    public function confirmarNomina(Request $request)
    {
        tienePermiso('periodos_nomina');
        tienePermiso('abrir_nomina');
        cambiarBase(Session::get('base'));

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();

        $concepto_facturacion = $parametros_empresa->concepto_facturacion;

        DB::connection('empresa')->table('rutinas'.$request->ejercicio)
                                    ->where('fnq_valor', 0)
                                    ->where('id_periodo', $request->idPeriodo)
                                    ->update(['estatus_confirma'=> 1, 'concepto_fac'=> $concepto_facturacion]);


        $desc = 'Se ha confirmado la nomina #'.$request->numPeriodo.', por favor verificala y confirma si se debe finalizar. ';
        $tipo = 'CN';
        
        envioAvisosXMail(Session::get('base'), 19, $request->numPeriodo);
        agregarABitacora(Session::get('base'), 19, $tipo, $request->idPeriodo, $desc);

        // return redirect()->route('parametria.periodosnomina')->with('tipo_alerta', 'success')->with('mensaje', $desc);

        session()->flash('success', $desc);

        return redirect()->route('nomina.periodos');

        // return redirect()->route('empleados.empleados');

    }

    public function exportarCalculoNomina(Request $request)
    {
        tienePermiso('periodos_nomina');
        cambiarBase(Session::get('base'));
        $deptos = $request->deptos;
        $periodo = PeriodosNomina::where('activo', PeriodosNomina::ACTIVO)->first();
        $this->periodo = $periodo;

        $empleados_rutinas = DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
           	->where('id_periodo', $periodo->id)
           	->where('fnq_valor', 0)
           	->whereIn('id_empleado', function($query) use($deptos, $periodo){
            $query->select('id')
            ->from(with(new Empleado)->getTable())
            ->where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->where('fecha_alta', '<=', $periodo->fecha_final_periodo)
            ->where('tipo_de_nomina', $periodo->nombre_periodo)
            ->whereIn('id_departamento', $deptos);
        })->get()->keyBy('id_empleado');
        
        $rutinas_ids = $ids_empleados = [];
        foreach ($empleados_rutinas as $rutina) {
            $rutinas_ids[] = $rutina->id;
            $ids_empleados[] = $rutina->id_empleado;
        }

        $empleados = Empleado::whereIn('id', $ids_empleados)->get()->keyBy('id');
        foreach($empleados as $empleado){
            $empleados[$empleado->id]->rutinas = $empleados_rutinas[$empleado->id];
        }
        $this->empleados = $empleados;

        $esCalculoNomina = false;
        $datos = $this->generarReporteNomina($deptos, $esCalculoNomina);
        $datos['periodo'] = $periodo;
        $datos['empleados'] = $empleados;

        return Excel::download(new CalculoNominaExport($datos),"CalculoNomina_{$request->idPeriodo}_".date('d-m-Y').".xlsx");
    }

    public function exportarcalculonominaDetalle(Request $request)
    {
        tienePermiso('periodos_nomina');
        cambiarBase(Session::get('base'));
        $deptos = $request->deptos;
        $periodo = PeriodosNomina::where('activo', PeriodosNomina::ACTIVO)->first();
        $this->periodo = $periodo;

        $empleados_rutinas = DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
            ->where('id_periodo', $periodo->id)
            ->where('fnq_valor', 0)
            ->whereIn('id_empleado', function($query) use($deptos, $periodo){
                $query->select('id')
                ->from(with(new Empleado)->getTable())
                ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                ->where('fecha_alta', '<=', $periodo->fecha_final_periodo)
                ->where('tipo_de_nomina', $periodo->nombre_periodo)
                ->whereIn('id_departamento', $deptos);
      	})->get()->keyBy('id_empleado');
        
        $rutinas_ids = $ids_empleados = [];
        foreach ($empleados_rutinas as $rutina) {
            $rutinas_ids[] = $rutina->id;
            $ids_empleados[] = $rutina->id_empleado;
        }

        $emp = Empleado::all()->where('estatus',1)->whereIn('id', $ids_empleados)->groupBy('id_categoria');
        //dd($emp);
        $agrupar_emisoras = array();
        foreach($emp as $cat => $empleados){
            $queryPatronal="SELECT re.num_registro_patronal,re.tipo_clase,re.id,cat.id as cat_id, cat.nombre as 
            nombre_categoria, emi.razon_social
                        FROM categorias cat  
                        INNER JOIN singh.registro_patronal re ON cat.tipo_clase = re.id
                        INNER JOIN singh.empresas_emisoras emi on emi.id = re.id_empresa_emisora
                       WHERE   cat.estatus = 1 AND  emi.estatus = 1 AND re.estatus = 1
                        AND cat.id = ".$cat;
                
            $registroP = DB::connection('empresa')->select($queryPatronal);
            if(Session::get('usuarioPermisos')['id_usuario']==64){
                   // dd($registroP);
                }
            $agrupar_emisoras[$cat]['emisora'] = $registroP[0];
            $agrupar_emisoras[$cat]['empleados'] = $empleados; 
            foreach($agrupar_emisoras[$cat]['empleados'] as $empleado){
                $empleado->rutina = $empleados_rutinas[$empleado->id];
            }
        }
        //dd($agrupar_emisoras);
        //dd($empleados);
        foreach($agrupar_emisoras as $categoria){
        //dd($categoria);
            foreach($categoria['empleados'] as $empleado){
               // dd($empleados_rutinas->{$empleado->id});
                $empleado->rutinas = $empleados_rutinas[$empleado->id];
                //dump($empleado);
            }
            
        }
        $this->empleados = $empleados;
        $this->AsignarFaltasDetalle($agrupar_emisoras);

        $esCalculoNomina = false;
        $datos = $this->generarReporteNominaEmisoras($deptos, $esCalculoNomina,$agrupar_emisoras);
        $datos['periodo'] = $periodo;
        foreach($agrupar_emisoras as $categoria){
            //dd($categoria);
                foreach($categoria['empleados'] as $empleado){
                   // dd($empleados_rutinas->{$empleado->id});
                    $empleado->rutinas = $empleados_rutinas[$empleado->id];
                    //dump($empleado);
                }
                
            }
          $this->empleados = $empleados;
        $datos['empleados'] = $empleados;        
        $datos['agrupar_emisoras'] = $agrupar_emisoras;
/*
		if(Session::get('usuarioPermisos')['id_usuario']==64){
                    dd($this->empleados[1]->puesto->puesto);
					
                }
*/
        \libxml_use_internal_errors(true);
        return Excel::download(new CalculoNominaDetalleExport($datos),"CalculoNominaDetalle_{$request->idPeriodo}_".date('d-m-Y').".xlsx");
    }

    protected function AsignarFaltasDetalle($agrupar_emisoras){

        // Faltas
        $concepto_faltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->where('activo_en_nomina', 1)->first();
        $id_concepto_faltas = ($concepto_faltas != null) ? intval($concepto_faltas->id) : 0;

        foreach ($agrupar_emisoras as $categoria) {
            foreach($categoria['empleados'] as $empleado){
                if($id_concepto_faltas > 0){
                    $col_faltas = 'valor'.$id_concepto_faltas;
                    $faltas = $empleado->rutinas->$col_faltas;
                 } else {
                    $faltas = 0;
                }
                $empleado->faltas=$faltas;
                 //$this->empleados[$empleado->id]->faltas = $faltas;
            }
        }
    }

protected function generarReporteNominaEmisoras($deptos, $esCalculoNomina,$agrupar_emisoras)
    {
        $periodo = $this->periodo;
        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();

        $rfc = Session::get('empresa')['rfc'];
        $tipodeNominaEmpresa = strtoupper($parametros_empresa->tipo_nomina);
        $ConcepFacturacion = $parametros_empresa->concepto_facturacion;
        $provisionAguinaldo = $parametros_empresa->provision_aguinaldo;
        $povisionPrimavacacional = $parametros_empresa->provision_prima_vacacional;
        $valorprovisionAgui = 0;
        $valorprovisionPrimvaca = 0;
        if($provisionAguinaldo==1 && $auxagui==0){

            $querysumaProvisionAgui = "SELECT sum(total_aguinaldo) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT idempleado from empleados where estatus = 1 and tipo_de_nomina='$periodo->nombre_periodo' and fechaAlta <= '$periodo->fecha_final_perido' and id_departamento in (".implode(',', $deptos)."))";
            $sumaProvisionAgui = DB::connection('empresa')->select($querysumaProvisionAgui);
            $valorprovisionAgui = $sumaProvisionAgui->result;

        }

        if($povisionPrimavacacional==1 && $auxprimvaca==0){

            $querysumaProvisionPrimvaca = "SELECT sum(total_prima_vacacional) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT idempleado from empleados where estatus = 1 and tipo_de_nomina='$periodo->nombre_periodo' and fechaAlta <= '$periodo->fecha_final_perido' and id_departamento in (".implode(',', $deptos)."))";
            $sumaProvisionPrimvaca = DB::connection('empresa')->select($querysumaProvisionPrimvaca);
            $valorprovisionPrimvaca = $sumaProvisionPrimvaca->result;
        }

        // Prima vacacioinal
        $concepto_prima_vacacional = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('rutinas', 'PVAC')
                                ->where(function ($query) {
                                    $query->where('file_rool', '<=', 249)
                                        ->orWhere('file_rool', '!=', 0);
                                })->first();
        // $rowsidpvac = $concepto_prima_vacacional->id;
        $misc['columnaPVAC'] = $concepto_prima_vacacional;
        // Prima vacacioinal ANTI
        $concepto_prvacacional_anti = DB::connection('empresa')->table('conceptos_nomina')->select('id')
        ->where('estatus', 1)
        ->where('activo_en_nomina', 1)
        ->where('rutinas', 'PVACANTI')
        ->where(function ($query) {
            $query->where('file_rool', '<=', 249)
            ->orWhere('file_rool', '!=', 0);
        })->first();
        // $rowsidpvacAnti = $concepto_prvacacional_anti->id;
        $misc['rowsidpvacAnti'] = $concepto_prvacacional_anti;
        
        
        // Prima vacacioinal
        $concepto_pr_dominical = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('rutinas', 'PRDOM')
                                ->where(function ($query) {
                                    $query->where('file_rool', '<=', 249)
                                        ->orWhere('file_rool', '!=', 0);
                                })->first();
        // $rowsidprdom = $concepto_pr_dominical->id;
        $misc['rowsidprdom'] = $concepto_pr_dominical;

        $sueldo_x_hr = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                            ->where('rutinas', 'SDOXHORA')
                            ->where('estatus', 1)
                            ->where('activo_en_nomina', 1)
                            ->first();
        
        $hrs_adicionales = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                            ->where('nombre_concepto', 'HORAS ADICIONALES')
                            ->where('estatus', 1)
                            ->where('activo_en_nomina', 1)
                            ->first();

        // Se asignan sedes
        $haySedes = false;
        if (Schema::connection('empresa')->hasTable('sedes')) {
            $sedes = DB::connection('empresa')->table('sedes')->get()->keyBy('id');
            if($sedes->count() > 0){
                $haySedes = true;
                foreach ($agrupar_emisoras as $categoria) {

                    foreach($categoria['empleados'] as &$empleado){
                    if(!empty($empleado->sede) && isset($sedes[$empleado->sede]))
                        $empleado->sede_nombre = $sedes[$empleado->sede]->nombre;
                    else
                        $empleado->sede_nombre = '';
                }
            }
            }
        }
        $misc['haySedes'] = $haySedes;

        $columnas1 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 0)
                                ->where('file_rool', '<=', 249)
                                ->Where('file_rool', '!=', 0)
                                ->get()->keyBy('id');

        $columnas2 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto', 'rutinas')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 1)
                                ->where('file_rool', '<=', 249)
                                ->Where('file_rool', '!=', 0)
                                ->get()->keyBy('id');
                                
        $misc['faltas_s'] = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTAS_S')
                                ->where('tipo', 3)
                                ->first();


        $columnasSindical = collect([]);
        $rowConceptoDescuento75 = collect([]);
        $columnasDEDUCC = collect([]);
        $columnas3 = collect([]);

        if($tipodeNominaEmpresa=='SINDICAL' || $tipodeNominaEmpresa=='SOLOSINDICAL'){

            $columnasSindical = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 0)
                                ->where(function ($query) {
                                    $query->where('file_rool', '>=', 250)
                                        ->orWhere('file_rool', 0);
                                })->get();
                                
            $rowConceptoDescuento75 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'BENEFICIO SINDICAL + BONO FIJO 50%')
                                ->first();
            if($rowConceptoDescuento75){

                $queryConceptoBonoFijo = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'BONO FIJO')
                                ->first();
            }

            $columnasDEDUCC = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nomina', 1)
                                ->where('tipo', 1)
                                ->where(function ($query) {
                                    $query->where('file_rool', '>=', 250)
                                        ->orWhere('file_rool', 0);
                                })->get();

            // TODO: Revisar estos strings para que SIEMRPE sean los mismos en TODAS las empresas. Cotejar con PeriodosNominaController
            $columnas3 = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('estatus', 1)->where(function ($query) {
                                        $query->where('rutinas', 'INFONAzz')
                                            ->orWhere('rutinas', 'FONACOTssss')
                                            ->orWhere('rutinas', 'PENSIONsss')
                                            ->orWhere('rutinas', 'Credito infonavitsssss');
                                    })->get();
        }


        // TODO: revisar este funcionamiento
        // $acum = DB::connection('generica')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', 0)->count();
/*
        $validaconcepFaltaSin = DB::connection('generica')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTASS')
                                ->get();
                                */
        $validaconcepFaltaSin = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                                ->where('estatus', 1)
                                ->where('activo_en_nomina', 1)
                                ->where('nombre_concepto', 'FALTAS_S')
                                ->get();
        
        $rowvalidapremioA = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('nombre_concepto', 'PREMIO DE ASISTENCIA')
                                ->where('estatus', 1)->where('activo_en_nomina', 1)
                                ->first();

        $rowvalidapremioP = DB::connection('empresa')->table('conceptos_nomina')->select('id')
                                ->where('nombre_concepto', 'PREMIO DE PUNTUALIDAD')
                                ->where('estatus', 1)->where('activo_en_nomina', 1)
                                ->first();



    foreach ($agrupar_emisoras as $categoria) {

        foreach($categoria['empleados'] as &$empleado){
            
            $empleado->numero_empleado = (!empty($empleado->numero_empleado)) ? $empleado->numero_empleado : $empleado->id; // *************************************
            $incapacidades = $empleado->dias_incapacidad;
            $faltas = $empleado->faltas;
            $dias = $periodo->dias_periodo;

            $FechaFinal   = $periodo->fecha_final_periodo;
            $fechaIniPeri = $periodo->fecha_inicial_periodo;
            $diafechafinal = date('d', strtotime($periodo->fecha_final_periodo));
            $mesfechafinal = date('m', strtotime($periodo->fecha_final_periodo));
            $anofechafinal = date('Y', strtotime($periodo->fecha_final_periodo));

            $fin_del_periodo = $periodo->fecha_final_periodo;
            if(intval($mesfechafinal) == 2 && intval($diafechafinal) > 27){
                $fin_del_periodo = date('Y-m-t',strtotime($fechaIniPeri));
            }

            $fecha_final_periodo = Carbon::parse($fin_del_periodo);
            $fecha_alta = Carbon::parse($empleado->fecha_alta);
            $DiasNom = $fecha_final_periodo->diffInDays($fecha_alta) + 1;
            
            if($validaconcepFaltaSin->count() > 0){
                $idconceptoFaltasSin = $validaconcepFaltaSin[0]->id;

                //$valorfaltasSin = $empleado->rutinas->{'valor'.$idconceptoFaltasSin};
                $valorfaltasSin = ($empleado->rutinas->{'valor'.$idconceptoFaltasSin}!="" && $empleado->rutinas->{'valor'.$idconceptoFaltasSin} != null)?$empleado->rutinas->{'valor'.$idconceptoFaltasSin}:0;
            }

            $valorHoras = ($sueldo_x_hr) ? $empleado->rutinas->{'valor'.$sueldo_x_hr->id} : 0;
            $DiasPagadosHoras=0;

            if($empleado->fecha_alta > $periodo->fecha_inicial_periodo){

                $DiasPagados = $DiasNom - $incapacidades - $faltas ;

                if($validaconcepFaltaSin->count()>0){

                    $DiasPagados01 = $DiasNom - $incapacidades - $valorfaltasSin;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                }else{

                    $DiasPagados01 = $DiasNom - $incapacidades - $faltas;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($faltas*8);
                }
                $FaltasHoras=$DiasPagados01-($DiasPagados01xhora/8);
                $DiasPagadosHoras=$DiasPagados01xhora/8;
                
            } else {

                $DiasPagados = ($dias - $incapacidades - $faltas);

                if($validaconcepFaltaSin->count() > 0){

                    $DiasPagados01 = $dias - $incapacidades - $valorfaltasSin;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($valorfaltasSin*8);
                }else{
                    $DiasPagados01 = $dias - $incapacidades - $faltas;
                    $DiasPagados01xhora = $valorHoras - ($incapacidades*8) - ($faltas*8);
                }
                $FaltasHoras=$DiasPagados01-($DiasPagados01xhora/8);
                $DiasPagadosHoras=$DiasPagados01xhora/8;
                
            }
            if($FaltasHoras<0){
                $FaltasHoras=0;
            }
            if($DiasPagadosHoras>$DiasPagados01){
                $DiasPagadosHoras=$DiasPagados01;
            }
            $valoresHoras[$empleado->id]=$FaltasHoras;
            $valorDiasPHoras[$empleado->id]=$DiasPagadosHoras;

            
            $empleado->dias_pagados = $DiasPagados;

            $sueldoreal = $empleado->sueldo_neto / $dias;

            if($periodo->especial == 1){
                $importeBeneficio=0;

            } else {

                $importeBeneficio = (($sueldoreal * $DiasPagados01) - $empleado->rutinas->neto_fiscal) - $empleado->rutinas->total_deduccion_fiscal2 + $empleado->rutinas->total_percepcion_fiscal2;
                // if($empleado->id == 86)
                //     dd($importeBeneficio);
                
                
                if($sueldo_x_hr){
                    
                    $sueldorealxhora = $sueldoreal / 8;
                    $importeBeneficio = (($sueldorealxhora * $DiasPagados01xhora) - $empleado->rutinas->neto_fiscal) - $empleado->rutinas->total_deduccion_fiscal2 + $empleado->rutinas->total_percepcion_fiscal2;
                    if($hrs_adicionales){
                        $valorHrsAdicionales = $empleado->rutinas->{'valor'.$hrs_adicionales->id};
                        $importeBeneficio = $importeBeneficio-$valorHrsAdicionales;
                    }
                }

                $valorpremioA=0;
                $valorpremioP=0;

                if($rowvalidapremioA){
                    $valorpremioA = $empleado->rutinas->{'valor'.$rowvalidapremioA->id};
                }

                if($rowvalidapremioP){
                    $valorpremioP = $empleado->rutinas->{'valor'.$rowvalidapremioP->id};
                }

                $ValoreNulos = $valorpremioA + $valorpremioP;
                $importeBeneficio = $importeBeneficio - $ValoreNulos;
                if($importeBeneficio < 0){
                    $importeBeneficio=0;
                }
                if($empleado->tipo_fiscal==1 && $empleado->tipo_sindical==0){
                    $importeBeneficio=0;
                }
                if($tipodeNominaEmpresa == 'SOLOSINDICAL'){
                    $importeBeneficio = $sueldoreal * $DiasPagados01;
                    if($importeBeneficio < 0){
                        $importeBeneficio = 0;
                    }
                }
                
                // if($empleado->id == 86)
                //     dd($importeBeneficio);

                $empleado->rutinas->beneficio_sindical = $importeBeneficio; // ***********************************************
            }

            if($rowConceptoDescuento75 && $rowConceptoDescuento75->count() > 0)
            {
                $valorBono = 0;
                $valorBono75 = 0;
                //dd($queryConceptoBonoFijo);
                if($queryConceptoBonoFijo){
                    $valorBono = $empleado->rutinas->{'total'.$queryConceptoBonoFijo->id};
                    $valorBono75 = $valorBono * 0.50;
                }

                $importeBeneficio75 = $importeBeneficio * 0.50;
                $TotalpercepcionSindical = $empleado->rutinas->total_percep_sindical - $valorBono;
                $beneficioSindical75 = $importeBeneficio75 + $valorBono75;

                $empleado->rutinas->{'total'.$rowConceptoDescuento75->id} = $beneficioSindical75; // ******************************************

                $TotalpercepcionSindical = $TotalpercepcionSindical + $empleado->rutinas->bono_prima + $beneficioSindical75 + $empleado->rutinas->bono_prima_dom ;
            } else {
                
                $TotalpercepcionSindical = floatval($empleado->rutinas->total_percepcion_sindical) + floatval($importeBeneficio) + floatval($empleado->rutinas->bono_prima) + floatval($empleado->rutinas->bono_prima_dom);
            }

            if($esCalculoNomina)
                $empleado->rutinas->total_percepcion_sindical = $TotalpercepcionSindical; // ***********************************************

            $sumadeduccion = 0;
            if($columnas3->count() > 0){

                $saldosNomina = DB::connection('empresa')->table('saldo_nomina')->select('valor_concepto')
                                ->where('id_periodo', $periodo->id)
                                ->where('saldo', '<', 0)
                                ->where('id_empleado', $empleado->id)
                                ->whereIn('id_concepto', $columnas3->pluck('id')->toArray())
                                ->get()->keyBy('id_concepto');

                foreach($columnas3 as $col){
                    if(isset($saldosNomina[$col->id])){
                        $empleado->saldosNomina[$col->id] = $saldosNomina[$col->id]->valor_concepto;
                        $sumadeduccion += $saldosNomina[$col->id]->valor_concepto;
                    }
                    else{
                        $empleado->saldosNomina[$col->id] = $empleado->rutinas->{'total'.$col->id};
                        $sumadeduccion += $empleado->rutinas->{'total'.$col->id};
                    }
                }
            }

            $empleado->rutinas->total_deduccion_sindical += $sumadeduccion; // ************************************ 
            $empleado->rutinas->neto_sindical = $empleado->rutinas->total_percepcion_sindical - $empleado->rutinas->total_deduccion_sindical; // ************************************ 

            $TotalBeneficio = $importeBeneficio - $empleado->rutinas->total_deduccion_sindical;
            if($tipodeNominaEmpresa=='SOLOSINDICAL')
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_sindical;
            else if($tipodeNominaEmpresa=='FISCAL')
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal;
            else
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal + $empleado->rutinas->neto_sindical;  // ************************************ 
            
            if($tipodeNominaEmpresa != 'SINDICAL' && $tipodeNominaEmpresa != 'SOLOSINDICAL'){
                $empleado->rutinas->importe_total = $empleado->rutinas->neto_fiscal; // **********************************
                $TotalpercepcionSindical=0;
                $totalbeneficioSindical=0;
            }
            
        }
    }
       // dd($this->empleados);

        $misc['rowConceptoDescuento75'] = $rowConceptoDescuento75;
        $misc['sueldo_x_hr'] = $sueldo_x_hr;

        /******************************** FIN PRIMERA TABLA ****************************** */


        $this->GuardarEmpleadosRutinasEmisoras($agrupar_emisoras);

        $porcentajeHono       = $parametros_empresa->porcentaje_honorarios;
        $ConcepFacturacion    = $parametros_empresa->concepto_facturacion;
        $anti                 = $parametros_empresa->anticipo;
        $comisionMismo        = $parametros_empresa->comision_mismo_dia;
        $provisionObrero      = $parametros_empresa->provision_obrero;
        $provisionPorcentaje  = $parametros_empresa->provision_porcentaje;
		$rcv  = $parametros_empresa->rvc_patronal_obrero;
        //Rene
        $comisionVariable     = ($parametros_empresa->comision_variable != "")?$parametros_empresa->comision_variable:0;
        $PocentajeNomina      = $parametros_empresa->porcentaje_nomina;
        $Iva                  = $parametros_empresa->iva;
        $valorPrestacionExtra = ($parametros_empresa->valor_prestacion_extra!="")?$parametros_empresa->valor_prestacion_extra:0;

        $concepto_imss       = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'IMSS')->where('estatus', 1)->first();
        $IdImss              = $concepto_imss->id;

        $valorhonorarios=$porcentajeHono/100;

        $querySumas = "SELECT sum(neto_fiscal) as suma_neto_fiscal, sum(total$IdImss) as suma_imss, sum(total_percepcion_fiscal) as suma_tpf, sum(total_percepcion_sindical) as suma_tps, sum(subsidio) as suma_subsidio, sum(beneficio_sindical) as suma_bs, sum(cuota_fija) as suma_cuotaf, sum(exce_pa) as suma_exce_pa, sum(exce_ob) as suma_exce_ob, sum(pre_dine_obre) as suma_pre_dine_obre, sum(pre_dine_patro) as suma_pre_dine_patro, sum(censa_vejez_obre_patronal) as suma_censa_vejez_obre_patronal, sum(gas_medi_patro) as suma_gas_medi_patro, sum(gas_medi_obre) as suma_gas_medi_obre, sum(riesgo_trabajo) as suma_riesgo_trabajo, sum(inva_vida_patro) as suma_inva_vida_patro, sum(inva_vida_obre) as suma_inva_vida_obre, sum(guarde_presta) as suma_guarde_presta, sum(censa_vejez_obre) as suma_censa_vejez_obre, sum(censa_vejez_patron) as suma_censa_vejez_patron, sum(infonavit_patro) as suma_infonavit_patro, sum(sar_patron) as suma_sar_patron from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id where em.estatus=1  and ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_periodo' and em.id_departamento in (".implode(',', $deptos).")";
        
        $rowresultnetofiscal     = DB::connection('empresa')->select($querySumas)[0]; 
        $netofiscal              = $rowresultnetofiscal->suma_neto_fiscal;
        $sumaObrera             = $rowresultnetofiscal->suma_imss;
        $PercepFiscal           = $rowresultnetofiscal->suma_tpf;
        $valorSubsidio          = $rowresultnetofiscal->suma_subsidio * -1;
        $BeneficioSindical       = $rowresultnetofiscal->suma_bs;
        $CuotaFija              = round($rowresultnetofiscal->suma_cuotaf, 2) * $provisionPorcentaje;
        $ExcePa                 = round($rowresultnetofiscal->suma_exce_pa, 2) * $provisionPorcentaje;
        $ExceOb                 = round($rowresultnetofiscal->suma_exce_ob, 2) * $provisionPorcentaje;
        $PreDineObre            = round($rowresultnetofiscal->suma_pre_dine_obre, 2) * $provisionPorcentaje;
        $PreDineroPa            = round($rowresultnetofiscal->suma_pre_dine_patro, 2) * $provisionPorcentaje;
        $CensaVejezObrePatronal = round($rowresultnetofiscal->suma_censa_vejez_obre_patronal, 2);
        $GasMediPatron          = round($rowresultnetofiscal->suma_gas_medi_patro, 2) * $provisionPorcentaje;
        $GasMediObre            = round($rowresultnetofiscal->suma_gas_medi_obre, 2) * $provisionPorcentaje;
        $RiesgoTrabajo          = round($rowresultnetofiscal->suma_riesgo_trabajo, 2) * $provisionPorcentaje;
        $InvaVidaPatro          = round($rowresultnetofiscal->suma_inva_vida_patro, 2) * $provisionPorcentaje;
        $InvaVidaObre           = round($rowresultnetofiscal->suma_inva_vida_obre, 2) * $provisionPorcentaje;
        $GuardePresta           = round($rowresultnetofiscal->suma_guarde_presta, 2) * $provisionPorcentaje;
        $CensaVejezObre         = round($rowresultnetofiscal->suma_censa_vejez_obre, 2) * $provisionPorcentaje;
        $CensaVejezPatro        = round($rowresultnetofiscal->suma_censa_vejez_patron, 2) * $provisionPorcentaje;
        $InfonavitPatro         = round($rowresultnetofiscal->suma_infonavit_patro, 2) * $provisionPorcentaje;
        $SarPatron              = round($rowresultnetofiscal->suma_sar_patron, 2) * $provisionPorcentaje;
        $totalbeneficioSindical  = $rowresultnetofiscal->suma_tps;

        $netoFiscalreal = ($tipodeNominaEmpresa=='SOLOSINDICAL') ? 0 : ($PercepFiscal+$valorSubsidio);

        if($concepto_prima_vacacional){

            foreach ($agrupar_emisoras as $categoria) {

        foreach($categoria['empleados'] as &$empleado){
                $empleadoscadena[]=$empleado->id;
            }}
            $cadena_empleados = implode(',', $empleadoscadena);
            $query_pVac = "SELECT sum(total$concepto_prima_vacacional->id) as result 
            from rutinas$periodo->ejercicio where fnq_valor=0 and id_periodo='$periodo->id'
            and id_empleado in (select id from empleados where tipo_de_nomina = '$periodo->nombre_periodo'
            and id in (".implode(',', $empleadoscadena)."))";

            $total_valor_prima = DB::connection('empresa')->select($query_pVac); 
            $pagoprimavaca = $total_valor_prima[0]->result;
        } else {
            $pagoprimavaca = 0.0;
        }
       // dump($netoFiscalreal , $totalbeneficioSindical , $anticipo , $vacaciones , $comisionmismodia , $valorprovisionAgui , $valorprovisionPrimvaca);

        /*validacionnes*/
        $netoFiscalreal = ($netoFiscalreal!="")?$netoFiscalreal:0;
        $totalbeneficioSindical = ($totalbeneficioSindical!="")?$totalbeneficioSindical:0;
        $valorprovisionAgui  =($valorprovisionAgui!="")?$valorprovisionAgui:0;
        $valorprovisionPrimvaca = ($valorprovisionPrimvaca !="" )?$valorprovisionPrimvaca:0;
        
        $anticipo         = ($anti != "")?$anti:0;
        $vacaciones       = 0.0;
        $comisionmismodia = ($comisionMismo != "")?$comisionMismo:0;

        $TotalpagarNomina = $netoFiscalreal + $totalbeneficioSindical + $anticipo + $vacaciones + $comisionmismodia + $valorprovisionAgui + $valorprovisionPrimvaca;
        $pagoHonorarios   = $TotalpagarNomina*$valorhonorarios;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $ExceObAdicional = $ExceOb*$ExcedenteObreraPatronal;
        }else{
            $ExceObAdicional=0;
        }
        $ExcePa = $ExcePa + $ExceObAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $PrePatroAdicional = round($PreDineObre,2) * $ExcedenteObreraPatronal;
        }else{
            $PrePatroAdicional = 0;
        }
        $PreDineroPa = $PreDineroPa + $PrePatroAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero / 100;
            $GasMediPatronAdicional = $GasMediObre * $ExcedenteObreraPatronal;
        }else{
            $GasMediPatronAdicional = 0;
        }
        $GasMediPatron = $GasMediPatron + $GasMediPatronAdicional;

        if($provisionObrero > 0){
            $ExcedenteObreraPatronal = $provisionObrero/100;
            $InvaVidaPatroAdicional = $InvaVidaObre * $ExcedenteObreraPatronal;
			
        }else{
            $InvaVidaPatroAdicional = 0;
        }
        $InvaVidaPatro = $InvaVidaPatro + $InvaVidaPatroAdicional;
		if($rcv > 0){
            $CensaVejezPatro = $CensaVejezPatro + $CensaVejezObre;
        }else{
            $CensaVejezPatro = $CensaVejezPatro;
        }
        //$CensaVejezPatro = $CensaVejezPatro + $CensaVejezObre;
        $porcentajenom   = $PocentajeNomina/100;
        $errogacion      = $PercepFiscal*$porcentajenom;

        foreach ($agrupar_emisoras as $categoria) {

        foreach($categoria['empleados'] as &$empleado){
                $empleadoscadena[]=$empleado->id;
            }}
            $cadena_empleados = implode(',', $empleadoscadena);
        $queryNumEmple = "SELECT * from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and pre.estatus=1 and em.id in ($cadena_empleados)";
        if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($queryNumEmple);
                }
        $empleados_2 = DB::connection('empresa')->select($queryNumEmple);

        //$numeroEmple = (isset($empleados_2[0])) ? $empleados_2[0]->count() : 0;
        $numeroEmple = (isset($empleados_2[0])) ? \count($empleados_2) : 0;
		
        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valor_seguro_GM, sum(pre.valor_plan_espejo) as valor_plan_espejo from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and em.id in ($cadena_empleados)";
        $seguros         = DB::connection('empresa')->select($queryvalorseguroGM);
        $valorseguroGM   = ($seguros[0]->valor_seguro_GM)? $seguros[0]->valor_seguro_GM: 0;
        $ValorPlanEspejo = ($seguros[0]->valor_plan_espejo)? $seguros[0]->valor_plan_espejo:0;
		
        $prestacionesExtras = ($valorPrestacionExtra * $numeroEmple) + $valorseguroGM + $ValorPlanEspejo;
		//dd($parametros_empresa,$queryvalorseguroGM ,$seguros,$numeroEmple,$valorPrestacionExtra,$valorseguroGM ,$ValorPlanEspejo,$prestacionesExtras );
		
        $total              = $prestacionesExtras+$CuotaFija+$ExcePa+$PreDineroPa+$GasMediPatron+$RiesgoTrabajo+$InvaVidaPatro+$GuardePresta+ $SarPatron+$CensaVejezPatro+$InfonavitPatro+$errogacion;
        $cargasocial        = $CuotaFija+$ExcePa+$PreDineroPa+$GasMediPatron+$RiesgoTrabajo+$InvaVidaPatro+$GuardePresta+$SarPatron+$CensaVejezPatro+$InfonavitPatro;
        $subtotal           = $TotalpagarNomina+$pagoHonorarios+$total;
        $iva                = $subtotal*$Iva;
        $totalmayor         = $subtotal+$iva;
        $comision           = $netoFiscalreal+$cargasocial+$errogacion;
        
        $valorcomision      = $comision*($comisionVariable/100);
        $subtotal02         = $netoFiscalreal+$cargasocial+$errogacion+$valorcomision;
        $iva02              = $subtotal02*$Iva;
        $totalmayor02       = $subtotal02+$iva02;
        $asesoriaContable   = $subtotal-$subtotal02;
        $iva03              = $asesoriaContable*$Iva;
        $totalmayor03       = $iva03+$asesoriaContable;


        $table_name = 'datos_facturacion'.$this->periodo->ejercicio;
        $id = $this->verificarTablaFacturacion($table_name);

        DB::connection('empresa')->table($table_name)->where('id', $id)->update([
            'nomina' => $netoFiscalreal,
            'beneficio_sindical' => $totalbeneficioSindical,
            'anticipo' => $anticipo,
            'vacaciones' => $vacaciones,
            'pago_prima_vaca' => $pagoprimavaca,
            'comision_mismo_dia' => $comisionmismodia,
            'total_pago_nomina' => $TotalpagarNomina,
            'porcentaje_honorarios' => $porcentajeHono,
            'valores_honorarios' => $pagoHonorarios,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'ejercicio' => $periodo->ejercicio,
            'costos_patronales' => $total,
            'detalle_subtotal' => $subtotal,
            'detalle_iva' => $iva,
            'detalle_total' => $totalmayor,
            'prestaciones_extras' => $prestacionesExtras,
            'cuota_fija' => $CuotaFija,
            'exc_cf' => $ExcePa,
            'presta_dinero' => $PreDineroPa,
            'gastos_medi_pensionados' => $GasMediPatron,
            'riesgo_trabajo' => $RiesgoTrabajo,
            'invalidez_y_vida' => $InvaVidaPatro,
            'guarderias_y_pre_sociales' => $GuardePresta,
            'cuotas_imss_retiro' => $SarPatron,
            'cuotas_imss_censatiaV' => $CensaVejezPatro,
            'cred_vivienda' => $InfonavitPatro,
            'porcentaje_errogaciones' => $PocentajeNomina,
            'valor_errogaciones' => $errogacion,
            'totalcostos_patronales' => $total,
        ]);

        

        $totales = [
            'neto_fiscal_real'                  => $netoFiscalreal,
            'total_percepcion_sindical'        => $totalbeneficioSindical,
            'valor_provision_aguinaldo'        => $valorprovisionAgui,
            'valor_provision_prima_vacacional' => $valorprovisionPrimvaca,
            'anticipo'                         => $anticipo,
            'vacaciones'                       => $vacaciones,
            'comision_mismo_dia'               => $comisionmismodia,
            'total_pagar_nomina'               => $TotalpagarNomina,
            'pago_honorarios'                  => $pagoHonorarios,
            'total'                            => $total,
            'subtotal'                         => $subtotal,
            'iva'                              => $iva,
            'total_mayor'                      => $totalmayor,
            
            'prestaciones_extras' => $prestacionesExtras,
            'cuota_fija'           => $CuotaFija,
            'exc_cf'              => $ExcePa,
            'pre_dinero_pa'       => $PreDineroPa,
            'gas_medi_patron'     => $GasMediPatron,
            'riesgo_trabajo'      => $RiesgoTrabajo,
            'inva_vida_patro'     => $InvaVidaPatro,
            'guarde_presta'       => $GuardePresta,
            'sar_patron'          => $SarPatron,
            'censa_vejez_patron'  => $CensaVejezPatro,
            'infonavit_patro'     => $InfonavitPatro,
            'errogacion'          => $errogacion,
            'porcentaje_nomina'   => $PocentajeNomina,

            'carga_social'      => $cargasocial,
            'comision_variable' => $comisionVariable,
            'valor_comision'    => $valorcomision,
            'subtotal02'        => $subtotal02,
            'iva02'             => $iva02,
            'total_mayor02'     => $totalmayor02,

            'concepto_facturacion' => $ConcepFacturacion,
            'asesoria_contable'    => $asesoriaContable,
            'iva03'                => $iva03,
            'total_mayor03'        => $totalmayor03,

        ];

        // $cargasocial   = $cargasocial+$prestacionesExtras;
        // $valorcomision = $TotalpagarNomina*($comisionVariable/100);
        $subtotal002    = $TotalpagarNomina+$cargasocial+$errogacion+$valorcomision;
        // $iva02         = $subtotal02*$Iva;
        // $totalmayor02  = $subtotal02+$iva02;

        DB::connection('empresa')->table($table_name)->where('id', $id)->update([
            'suministro_per1_1' => $netoFiscalreal,
            'carga_social1_1' => $cargasocial,
            'porcentajes_nomina' => $PocentajeNomina,
            'porcentaje_comisionV' => $valorcomision,
            'subtotal_depo1_1' => $subtotal02,
            'iva_depo1_1' => $iva02,
            'total_depo1_1' => $totalmayor02,
        ]);        

        $queryEmisoras = "SELECT em.id, id_categoria, ememi.razon_social as razon, group_concat(em.id) as cadenaemple, ememi.id as CadenaID_EmpresaE from empleados em join categorias cat on  em.id_categoria = cat.id inner join singh.registro_patronal regpat on cat.tipo_clase = regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora = ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.estatus=1 and id_departamento in (".implode(',', $deptos).") group by ememi.razon_social";
        
        $emisoras = DB::connection('empresa')->select($queryEmisoras);

        if($emisoras){
            DB::connection('empresa')->table($table_name)->where('id', $id)->update(['cadena_emisoras' => $emisoras[0]->CadenaID_EmpresaE]);
        }



        if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical'){

            $queryEmisoras = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id in ($cadena_empleados) group by ememi.razon_social";
            $emisoras = DB::connection('empresa')->select($queryEmisoras);

            if(count($emisoras) > 1){
                $i = 0;

                foreach ($emisoras as $emisora ) {
                    $i++;
                    
                    $queryPercepFiscalEmisoras = "SELECT sum(total_percepcion_fiscal) as suma from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_perido' and em.id in ($emisora->cadena_empleados)";
                    $percep_fiscal_emisoras = DB::connection('empresa')->select($queryPercepFiscalEmisoras);

                    //$percep_fiscal_emisoras = ($percep_fiscal_emisoras[0]['suma'] == null)?0:$percep_fiscal_emisoras[0]['suma'];                    
                    //$errogacion_emisora=$percep_fiscal_emisoras * $porcentaje_nom;
                    $percep_fiscal_emisoras = ($percep_fiscal_emisoras[0]->suma == null)?0:$percep_fiscal_emisoras[0]->suma;
                    $errogacion_emisora=$percep_fiscal_emisoras * $PocentajeNomina;

                    $queryvaloresFacturacion = "SELECT suministro_per1_$i as suministro_per, carga_social1_$i as carga_social, porcentajes_nomina, porcentaje_comisionV, subtotal_depo1_$i as subtotal_depo, iva_depo1_$i as iva_depo, total_depo1_$i as total_depo, valor_sobre_nomina1_$i as valor_sobre_nomina , valor_comision_variable1_$i as valor_comision_variable from datos_facturacion$periodo->ejercicio where id_periodo = '$periodo->id'";
                    $valores_facturacion = DB::connection('empresa')->select($queryvaloresFacturacion);
                    if(is_object($valores_facturacion[0])){
                        $valores_facturacion[0] = (array)$valores_facturacion[0];
                    }
                    $totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] = $valores_facturacion[0]['suministro_per'];
                    $totales[$emisora->id_empresa_emisora]['carga_social'] = $valores_facturacion[0]['carga_social'];
                    $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] = $valores_facturacion[0]['porcentajes_nomina'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision'] = $valores_facturacion[0]['porcentaje_comisionV'];
                    $totales[$emisora->id_empresa_emisora]['subtotal02'] = $valores_facturacion[0]['subtotal_depo'];
                    $totales[$emisora->id_empresa_emisora]['iva02'] = $valores_facturacion[0]['iva_depo'];
                    $totales[$emisora->id_empresa_emisora]['total_mayor02'] = $valores_facturacion[0]['total_depo'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] = $valores_facturacion[0]['valor_comision_variable'];
                    $totales[$emisora->id_empresa_emisora]['errogacion_emisora'] = $errogacion_emisora;
                }
            }

        }



        return compact('misc', 'columnas1', 'columnas2', 'columnasSindical', 'columnasDEDUCC', 'columnas3', 'totales', 'emisoras','valoresHoras','valorDiasPHoras');
    }



}