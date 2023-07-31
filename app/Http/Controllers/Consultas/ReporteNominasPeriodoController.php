<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use App\Models\AsignaEmpresasEmisoras;
use App\Models\Autofacturador\EmpresasEmisoras;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\PeriodosNomina;
use App\Models\Departamento;
use Illuminate\Support\Facades\DB;
use App\Models\ConceptosNomina;
use App\Models\Empleado;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Parametros;
use App\Models\Rutina;
use SebastianBergmann\Environment\Console;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteNominasPeriodoExport;

class ReporteNominasPeriodoController extends Controller
{
    public function reporteNominas()
    {
        cambiarBase(Session::get('base'));

        $periodos = PeriodosNomina::where('estatus', '<>', PeriodosNomina::ESTATUS_ELIMINADO)->orderBy('id', 'desc')->get();

        $departamentos = Departamento::where('estatus', 1)->orderBy('nombre', 'asc')->get();

        return view('consultas.reporte-nominasPeriodo.reporte-nominasPeriodo', compact('periodos', 'departamentos'));
    }

    public function docsReporteNominas(Request $request)
    {
        $empresa = base64_encode(Session::get('base'));
        cambiarBase(Session::get('base'));

        $deptos = base64_encode(join(",", $request->deptos));
        $periodo = base64_encode($request->id_periodo);
        $tipo = $request->tipo;

        $id_periodo=$request->id_periodo;
        $departamentos= $request->deptos;
        if ($request->tipo == "PDF") {

            $empresas=AsignaEmpresasEmisoras::where('id_empresa',Session::get('empresa')['id'])->with(['emisora.registroPatronal'])->first();

            $periodo_nomina=PeriodosNomina::where('id',$id_periodo)->first();
            $conceptos= ConceptosNomina:: where('estatus', 1)
                ->whereNotIn('file_rool', [0])
                ->where('file_rool', '<',250)
                ->where('nomina', 1)
                ->get();

            $empleados_rutinas = DB::connection('empresa')->table('rutinas'.$periodo_nomina->ejercicio)
                ->where('id_periodo', $periodo_nomina->id)
                ->where('fnq_valor', 0)
                ->whereIn('id_empleado', function($query) use($departamentos, $periodo_nomina){
                    $query->select('id')
                        ->from(with(new Empleado)->getTable())
                        ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                        ->where('fecha_alta', '<=', $periodo_nomina->fecha_final_periodo)
                        ->where('tipo_de_nomina', $periodo_nomina->nombre_periodo)
                        ->whereIn('id_departamento', $departamentos);
                })->get()->keyBy('id_empleado');


            $ids_empleados = [];
            foreach ($empleados_rutinas as $rutina) {
                $ids_empleados[] = $rutina->id_empleado;
            }

            $empleados = Empleado::whereIn('id', $ids_empleados)->with(['departamento','categoria'])->get()->keyBy('id');

            foreach($empleados as $empleado){
                $empleados[$empleado->id]->rutinas = $empleados_rutinas[$empleado->id];
            }

            $pdf=PDF::loadView('consultas.reporte-nominasPeriodo.reporte-nomina-periodo-pdf',['periodo'=>$periodo_nomina,'empleados'=>$empleados,'conceptos'=>$conceptos,'registro_patronal'=>$empresas->emisora[0]->registroPatronal->num_registro_patronal]);
            return $pdf->stream();
        } else {
            $datos = $this->generarReporteMensual($request->deptos, $periodo);

              return Excel::download(new ReporteNominasPeriodoExport($datos,$periodo),"PeriodosNomina_".date('d-m-Y').".xlsx"); 

           /*  return view('consultas.reporte-nominasPeriodo.reporteA-nominasPeriodo', compact('datos', 'periodo'));*/
        }
        return $pdf->setPaper('letter', 'landscape')->stream($file);
    }

    public function generarReporteMensual($deptos, $periodo)
    {
        $periodo = base64_decode($periodo);
        $empresa            = Session::get('empresa');
        $parametros_empresa = Parametros::all();

        foreach ($parametros_empresa as $emp) {
            $ejercicio = $emp->ejercicio;
        }


        $queryemisora       = "SELECT upper(emi.razon_social) as rSocial, upper(emi.rfc) as rfc, upper(emi.representante_legal) as rLegal, emi.direccion as direccionempresa from asigna_empresas_emisoras asi join empresas e on asi.id_empresa = e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id where e.id='" . $empresa['id'] . "';";
        $rowresultemisora   = DB::select($queryemisora);
        $emisoraResult      = $rowresultemisora[0];
        $emisora            = $emisoraResult->rSocial;
        $rfc                = $emisoraResult->rfc;
        $representantelegal = $emisoraResult->rLegal;
        $direccionempresa   = $emisoraResult->direccionempresa;

        /* dd($rowresultemisora); */

        $querynumregi        = "SELECT re.num_registro_patronal as result from asigna_empresas_emisoras asi join empresas e on asi.id_empresa=e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id inner join registro_patronal re on emi.id = re.id_empresa_emisora where e.id='" . $empresa['id'] . "'";
        $rowresultregistropa = DB::select($querynumregi);
        $registropa          = $rowresultregistropa[0]->result;

        /* dd($rowresultregistropa); */

        $FechaPago = PeriodosNomina::select('fecha_inicial_periodo', 'fecha_final_periodo')->where('id', $periodo)->where('estatus', 1)->get();

        /* dd($FechaPago);  */
        $conceptosIDs = ConceptosNomina::select('id')
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('file_rool', '<>', 0)
            ->get()->toArray();



        $caConcep = [];

        foreach ($conceptosIDs as $concepto) {
            $campo = 'valor' . $concepto['id'];
            if (
                Schema::connection('empresa')->hasColumn('rutinas' . $ejercicio, $campo)
            )
                $caConcep[] = $concepto['id'];
        }

        $cadenaconcepValidos = implode(",", $caConcep);
        $cadenaconcep        = base64_encode($cadenaconcepValidos);


        $conceptosIDs = ConceptosNomina::where('id', $caConcep)
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('tipo', '!=', 3)
            ->where('file_rool', '<',  250)
            ->where('file_rool', '<>', 0)
            ->get();
        //dump($conceptosIDs);

        $conceptosIDsArr = $conceptosIDs->pluck('id')->toArray();
        $f_i = $qry = $percepcionesIds = $deduccionesIds = [];

        // Deducciones y Percecpciones
        foreach ($conceptosIDs as $concepto) {

            if ($concepto['tipo'] = 0) {
                $percepcionesIds[$concepto['id']] = $concepto['nombre_concepto'];
            } else if ($concepto['tipo'] = 1) {
                $deduccionesIds[$concepto['id']] = $concepto['nombre_concepto'];
            }
        }

        $intento = DB::connection('empresa')->table('rutinas' . $ejercicio)->select('id_empleado')->where('fnq_valor', 0)
            ->where('id_periodo', $periodo)->get();
        $empleados = [];

        foreach ($deptos as $dep) {
            $empleado = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                ->where('id_departamento', $dep)
                ->with(['categoria', 'departamento'])
                ->get();
        }
        foreach ($empleado as $emp) {
            foreach ($intento as $intent) {

                if ($emp->id == $intent->id_empleado) {
                    $empleados = $empleado;
                }
            }
        }

        $ID_conceptoFaltas = ConceptosNomina::select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->whereIn('id', $caConcep)->first();

        // FALTAS, INCAPACIDADES Y RUTINAS VALORES
        foreach ($conceptosIDsArr as $idConcepto) {
            $qry[] = "total" . $idConcepto . " as total" . $idConcepto;
        }
        $qryStr = implode(', ', $qry);

        $incapacidades_faltas = DB::connection('empresa')->table('rutinas' . $ejercicio)->select('id_empleado', 'incapacidades', 'valor' . $ID_conceptoFaltas->id . ' as faltas', 'total_percepcion_fiscal as tpf', 'total_deduccion_fiscal as tdf', 'neto_fiscal as neto_fiscal')
            ->whereIn('id_empleado', $empleados)
            ->where('fnq_valor', '=', '0')
            ->where('id_periodo', '=', $periodo)->get();


        // dd($incapacidades_faltas);
        $incapacidades_faltas_arr = $incapacidades_faltas;

        // faltas e incapacidades
        foreach ($incapacidades_faltas_arr as $valores) {
            $f_i[$valores->id_empleado] = [
                'incapacidades'          => (intval($valores->incapacidades) > 0) ? $valores->incapacidades : 0,
                'faltas'                 => (intval($valores->faltas) > 0) ? $valores->faltas : 0,
                'total_percepcion_fiscal' => (intval($valores->tpf) > 0) ? $valores->tpf : 0,
                'total_deduccion_fiscal'  => (intval($valores->tdf) > 0) ? $valores->tdf : 0,
                'neto_fiscal'             => (intval($valores->neto_fiscal) > 0) ? $valores->neto_fiscal : 0,
            ];
        }

        // Valores de los conceptos 
        foreach ($incapacidades_faltas_arr as $valores) {
            foreach ($conceptosIDsArr as $idConcepto) {
                $f_i[$valores->id_empleado]['total' . $idConcepto] = $valores->{'total' . $idConcepto};
            }
        }

        $querydias = "SELECT  datediff(max(fecha_final_periodo), min(fecha_inicial_periodo)) as dias, min(fecha_inicial_periodo) as min, max(fecha_final_periodo) as max from periodos_nomina where id = " . $periodo . ";";
        $diasArr   = DB::connection('empresa')->select($querydias);
        $FechaIni  = $diasArr[0]->min;
        $FechaFin  = $diasArr[0]->max;
        $dias      = $diasArr[0]->dias;




        //dd($matriz);
        // Empleados del reporte
        foreach ($empleados as &$empleado) {

            $fechaAlta = $empleado->fecha_alta;
            if (isset($f_i[$empleado->id])) {
                $incapacidades = $f_i[$empleado->id]['incapacidades'];
                $faltas = $f_i[$empleado->id]['faltas'];
            } else {
                $incapacidades = 0;
                $faltas = 0;
            }

            if ($fechaAlta > $FechaIni) {

                $fecha_final = Carbon::parse($FechaFin);
                $fecha_alta = Carbon::parse($fechaAlta);
                $dias_nom   = $fecha_final->diffInDays($fecha_alta);
                $Diaspagado = $dias_nom - $incapacidades - $faltas;
            } else {

                $Diaspagado = $dias - $incapacidades - $faltas;
            }


            $empleado->dias_pagados = $Diaspagado; // **********************
            $empleado->dias_periodo = $dias; // **********************

            /*  $empleado->totales = $f_i[$empleado->id]; */ // **********************
            // dd($empleado->totales);
        }

        // -----------------------------------------------------------------------------
        $base = array(
            'nombre' => array('NOMBRE'),
            'categoria' => array('CATEGORIA'),
            'depto' => array('DEPTO.'),
            'sal_diario' => array('SAL. DIARIO'),
            'sal_d_int' => array('SAL D.INT.'),
            'dias_periodo' => array('DÍAS PERIODO'),
            'dias_pagados' => array('DÍAS PAGADOS'),
            'total_percep' => array('TOTAL PERCEP.'),
            'total_deduc' => array('TOTAL DEDUCC.'),
            'neto' => array('NETO')
        );
        foreach ($conceptosIDs as $concepto) {
            $base[$concepto->id] = array(strtoupper($concepto->nombre_concepto));
        }

        $base['total_percep'] = array('TOTAL PERCEP.');
        $base['total_deduc'] = array('TOTAL DEDUCC.');
        $base['neto'] = array('NETO');

        $matriz = [];

        $i = $cont = 0;
        foreach ($empleados as &$empleado) {
            if ($i == 0) {
                $matriz[$cont] = $base;
            }
            $matriz[$cont]['nombre'][] = $empleado->id . " " . $empleado->nombre . "<br/>" . $empleado->apaterno . " " . $empleado->amaterno;
            $matriz[$cont]['categoria'][] = str_replace("PRESTACIONES DE LEY ", "", $empleado->categoria->nombre);
            $matriz[$cont]['depto'][] = $empleado->departamento->nombre;
            $matriz[$cont]['sal_diario'][] = $empleado->salario_diario;
            $matriz[$cont]['sal_d_int'][] = $empleado->salario_diario_integrado;


            $fechaAlta = $empleado->fecha_alta;
            if (isset($f_i[$empleado->id])) {
                $incapacidades = $f_i[$empleado->id]['incapacidades'];
                $faltas = $f_i[$empleado->id]['faltas'];
            } else {
                $incapacidades = 0;
                $faltas = 0;
            }

            if ($fechaAlta > $FechaIni) {

                $fecha_final = Carbon::parse($FechaFin);
                $fecha_alta = Carbon::parse($fechaAlta);
                $dias_nom   = $fecha_final->diffInDays($fecha_alta);
                $Diaspagado = $dias_nom - $incapacidades - $faltas;
            } else {

                $Diaspagado = $dias - $incapacidades - $faltas;
            }

            $matriz[$cont]['dias_periodo'][] = $dias;
            $matriz[$cont]['dias_pagados'][] = $Diaspagado;

            foreach ($empleados as $empleado) {
                $total[] = DB::connection('empresa')->table('rutinas' . $ejercicio)
                    ->select('id', 'id_empleado', 'total_percepcion_fiscal', 'total_deduccion_fiscal', 'neto_fiscal')
                    ->where('id_empleado', $empleado->id)
                    ->where('id_periodo', '=', $periodo)
                    ->get();
            }

            $matriz[$cont]['total_percep'][] = $total[0][0]->total_percepcion_fiscal;
            $matriz[$cont]['total_deduc'][] = $total[0][0]->total_deduccion_fiscal;
            $matriz[$cont]['neto'][] = $total[0][0]->neto_fiscal;

            foreach ($conceptosIDs as $concepto) {
                if (isset($total[0][0]->total, $concepto->id)) {
                    $matriz[$cont][$concepto->id][] = $total[0][0]->total . $concepto->id;
                } else {
                    $matriz[$cont][$concepto->id][] = "-";
                }
            }


            $i++;
            if ($i == 15) {
                $cont++;
                $i = 0;
            }
        }

        /* dd($matriz); */

        //-------------------------------------------------------------------------------------------------------
        $return = [
            'conceptosIDs' => $conceptosIDs,
            'empleados'    => $empleados,
            'registro_patronal' => $registropa,
            'empleadoss' => $matriz,
            'fecha_pago' =>$FechaPago,

        ];

        return $return;
    }
    public function departamentosReporteNorma($id_periodo)
    {

        cambiarBase(Session::get('base'));

        $periodo = periodosNomina::find($id_periodo);
        $tblRutinas = 'rutinas' . $periodo->ejercicio;
        $deptos = Empleado::distinct('empleados.id_departamento')
            ->select('departamentos.id', 'departamentos.nombre')
            ->join($tblRutinas, $tblRutinas . '.id_empleado', '=', 'empleados.id')
            ->join('departamentos', 'departamentos.id', '=', 'empleados.id_departamento')
            ->where('empleados.estatus', Empleado::EMPLEADO_ACTIVO)
            ->where('empleados.tipo_de_nomina', 'like',  $periodo->nombre_periodo)
            ->where($tblRutinas . '.id_periodo',  $periodo->id)
            ->get();


        return response()->json(['ok' => 1, 'deptos' => $deptos]);
    }
}
