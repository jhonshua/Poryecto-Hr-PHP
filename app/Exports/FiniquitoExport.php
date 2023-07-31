<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;


class FiniquitoExport implements FromView, ShouldAutoSize
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }



    public function view(): View
    {

       /* $misc                   = $this->datos['misc'];
        $columnas1              = $this->datos['columnas1'];
        $columnas2              = $this->datos['columnas2'];
        $columnasSindical       = $this->datos['columnasSindical'];
        $this->datos['columnaPVAC'] = $misc['columnaPVAC'];
        $this->datos['rowsidpvacAnti']         = $misc['rowsidpvacAnti'];
        $this->datos['rowsidprdom']            = $misc['rowsidprdom'];
        $this->datos['rowConceptoDescuento75'] = $misc['rowConceptoDescuento75'];
        $columnasDEDUCC         = $this->datos['columnasDEDUCC'];
        $columnas3              = $this->datos['columnas3'];
        $totales                = $this->datos['totales'];
        $emisoras               = $this->datos['emisoras'];
        $periodo                = $this->datos['periodo'];
        $empleados              = $this->datos['empleados'];
        $this->datos['parametros_empresa'] = Session::get('empresa.parametros')[0];



        $pextras   = DB::connection('generica')->table('prestaciones_extras')->whereIn('id_empleado', $empleados->pluck('id')->toArray())->get()->keyBy('id_empleado');
        $prov_fact = DB::connection('generica')->table('provisiones_facturacion')->whereIn('id_empleado', $empleados->pluck('id')->toArray())->where('id_periodo', $periodo->id)->where('ejercicio', $periodo->ejercicio)->get()->keyBy('id_empleado');



        $parametros_empresa   = Session::get('empresa.parametros')[0];
        $provisionPorcentaje  = $parametros_empresa['provision_porcentaje'];
        $provisionObrero      = $parametros_empresa['provision_obrero'];
        $PocentajeNomina      = $parametros_empresa['porcentaje_nomina'];
        $porcentajeHono       = $parametros_empresa['porcentaje_honorarios'];
        $valorPrestacionExtra = $parametros_empresa['valor_prestacion_extra'];
        $IvaIndivi            = $parametros_empresa['iva'];
        

        foreach($empleados as &$empleado){

            $diferenciaporemple = $empleado->sueldo_neto - $empleado->rutinas->importe_total;
            $empleado->pextras = (isset($pextras[$empleado->id])) ? $pextras[$empleado->id]: null;
            $empleado->prov_fact = (isset($prov_fact[$empleado->id])) ? $prov_fact[$empleado->id] : null;

            $TotalImss = ( $empleado->rutinas->cuota_fija + $empleado->rutinas->exce_pa + $empleado->rutinas->pre_dine_patro + $empleado->rutinas->gas_medi_patro + $empleado->rutinas->riesgo_trabajo + $empleado->rutinas->guarde_presta + $empleado->rutinas->inva_vida_patro );

            $TotalImssConProvision = $TotalImss * $provisionPorcentaje;

            if($provisionObrero > 0){
                $IndPerprovisionObrero = $provisionObrero/100;
                $obreImss  = ($empleado->rutinas->exce_ob + $empleado->rutinas->pre_dine_obre + $empleado->rutinas->gas_medi_obre + $empleado->rutinas->inva_vida_obre) * $IndPerprovisionObrero;
                $TotalImss = $TotalImss + $obreImss;
                $TotalImssConProvision = $TotalImss * $provisionPorcentaje;
            }
            $RCVTotal = ($empleado->rutinas->sar_patron) + ($empleado->rutinas->censa_vejez_patron) + ($empleado->rutinas->censa_vejez_obre);

            $RCVTotalConProvision = ($empleado->rutinas->sar_patron * $provisionPorcentaje) + ($empleado->rutinas->censa_vejez_patron * $provisionPorcentaje) + ($empleado->rutinas->censa_vejez_obre * $provisionPorcentaje);

            $InfovaitTotal = $empleado->rutinas->infonavit_patro;
            $InfovaitTotalConProvision = ($empleado->rutinas->infonavit_patro * $provisionPorcentaje);

            $CargaTotal = $TotalImss + $RCVTotal + $InfovaitTotal;
            $CargaTotalConProvision = $TotalImssConProvision + $RCVTotalConProvision + $InfovaitTotalConProvision;
            $porcen = $PocentajeNomina/100;
            $isnporemple = $empleado->rutinas->total_percepcion_fiscal * $porcen;

            $costoPrestacion = 0;
            if($empleado->pextras){
                if($empleado->pextras->estatus){
                    $costoPrestacion = $valorPrestacionExtra + $empleado->pextras->valor_seguro_GM + $empleado->pextras->valor_plan_espejo;
                }else{
                    $costoPrestacion = $empleado->pextras->valor_seguro_GM + $empleado->pextras->valor_plan_espejo;
                }
            } else {
                $costoPrestacion = 0;
            }

            $subs = ($empleado->rutinas->subsidio > 0) ?:  ($empleado->rutinas->subsidio * -1);

            if($provisionObrero > 0){
                $IndiExceOb          = ($empleado->rutinas->exce_ob * $provisionPorcentaje) * ($provisionObrero/100);
                $IndiPreDineObre     = ($empleado->rutinas->pre_dine_obre * $provisionPorcentaje) * ($provisionObrero/100);
                $IndiGasMediObre     = ($empleado->rutinas->gas_medi_obre * $provisionPorcentaje) * ($provisionObrero/100);
                $IndiInvaVidaObre    = ($empleado->rutinas->inva_vida_obre*$provisionPorcentaje) * ($provisionObrero/100);
                $IndiCuotaFija       = $empleado->rutinas->cuota_fija * $provisionPorcentaje;
                $IndiExcePa          = $empleado->rutinas->exce_pa * $provisionPorcentaje;
                $IndiPreDinePatro    = $empleado->rutinas->pre_dine_patro * $provisionPorcentaje;
                $IndiGasMediPatro    = $empleado->rutinas->gas_medi_patro * $provisionPorcentaje;
                $IndiRiesgoTrabajo   = $empleado->rutinas->riesgo_trabajo * $provisionPorcentaje;
                $IndiGuardePresta    = $empleado->rutinas->guarde_presta * $provisionPorcentaje;
                $IndiInvaVidaPatro   = $empleado->rutinas->inva_vida_patro * $provisionPorcentaje;
                $IndiSarPatron       = $empleado->rutinas->sar_patron * $provisionPorcentaje;
                $IndiCensaVejezPatro = $empleado->rutinas->censa_vejez_patron * $provisionPorcentaje;
                $IndiInfonavitPatro  = $empleado->rutinas->infonavit_patro * $provisionPorcentaje;
                $IndiCensaVejezObre  = $empleado->rutinas->censa_vejez_obre * $provisionPorcentaje;
                $totalcuotasIndivi   = $IndiExceOb + $IndiPreDineObre + $IndiGasMediObre + $IndiInvaVidaObre + $IndiCuotaFija + $IndiExcePa + $IndiPreDinePatro + $IndiGasMediPatro + $IndiRiesgoTrabajo + $IndiGuardePresta + $IndiInvaVidaPatro + $IndiSarPatron + $IndiCensaVejezPatro + $IndiInfonavitPatro + $IndiCensaVejezObre;
            }else{
                $totalcuotasIndivi = ((round($empleado->rutinas->cuota_fija,2) + round($empleado->rutinas->exce_pa,2) + round($empleado->rutinas->pre_dine_patro,2) + round($empleado->rutinas->gas_medi_patro,2) + round($empleado->rutinas->riesgo_trabajo,2) + round($empleado->rutinas->guarde_presta,2) + round($empleado->rutinas->inva_vida_patro,2) + round($empleado->rutinas->sar_patron,2) + round($empleado->rutinas->censa_vejez_patron,2) + round($empleado->rutinas->_infonavit_patro,2) + round($empleado->rutinas->censa_vejez_obre,2)) * $provisionPorcentaje);
            }

            $valorprovisionAguiEmpleado     = ($empleado->prov_fact) ? $empleado->prov_fact->total_aguinaldo : 0;
            $valorprovisionPrimvacaEmpleado = ($empleado->prov_fact) ? $empleado->prov_fact->total_prima_vacacional : 0;
            $totalImssIndividual            = $costoPrestacion + $totalcuotasIndivi + $isnporemple;

            $sumapercepComple = $empleado->rutinas->total_percepcion_fiscal + $subs + $empleado->rutinas->total_percepcion_sindical + $valorprovisionAguiEmpleado + $valorprovisionPrimvacaEmpleado;
            $costocomision    = $sumapercepComple * ($porcentajeHono/100);
            $subtotalporemple = $sumapercepComple + $costocomision + $totalImssIndividual;
            $Ivaporemple      = $subtotalporemple * $IvaIndivi;
            $costoporemple    = $subtotalporemple + $Ivaporemple;




            $totales2['valorprovisionAguiEmpleado'] = $valorprovisionAguiEmpleado;
            $totales2['valorprovisionPrimvacaEmpleado'] = $valorprovisionPrimvacaEmpleado;
            $totales2['TotalImssConProvision'] = $TotalImssConProvision;
            $totales2['RCVTotalConProvision'] = $RCVTotalConProvision;
            $totales2['InfovaitTotalConProvision'] = $InfovaitTotalConProvision;
            $totales2['isnporemple'] = $isnporemple;
            $totales2['costoPrestacion'] = $costoPrestacion;
            $totales2['costocomision'] = $costocomision;
            $totales2['subtotalporemple'] = $subtotalporemple;
            $totales2['Ivaporemple'] = $Ivaporemple;
            $totales2['costoporemple'] = $costoporemple;
            $totales2['ImporteTotal'] = $empleado->rutinas->importe_total;
            $empleado->totales = (object)$totales2;
            // dd($empleado->totales);
        }*/
        $periodo      = $this->datos['periodo'];
        $empleado     = $this->datos['empleado'];
        $fecha_inicial_periodo = Carbon::parse($periodo->fecha_inicial_periodo);
        $fecha_baja_final = Carbon::parse($empleado->fecha_baja);
        $dias_pagados = $fecha_inicial_periodo->diffInDays($fecha_baja_final) + 1;
        $percepciones = $this->datos['percepciones'];
        $deducciones  = $this->datos['deducciones'];
        $valores      = $this->datos['valores'];
        $colfaltas    = 'valor' . $valores['conceptoFaltas'];
        $totales      = $this->datos['totales'];
        $faltas       = (isset($empleado->rutina->$colfaltas)) ? $empleado->rutina->$colfaltas : 0;
        $DiasNom      = intval($dias_pagados) - intval($faltas);
        return view('procesos.calculo-finiquito.exportar-finiquito',  compact('empleado', 'periodo', 'percepciones', 'deducciones', 'dias_pagados', 'valores', 'totales', 'DiasNom'));
    }
}
