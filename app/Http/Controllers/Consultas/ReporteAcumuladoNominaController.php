<?php

namespace App\Http\Controllers\Consultas;

useApp\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Departamento;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Empleado;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteAcumuladoNominaExport;

class ReporteAcumuladoNominaController extends Controller
{
    public function acumuladoNomina()
    {
        cambiarBase(Session::get('base'));

        $deptos_asignados = Session::get('usuarioDepartamentos');

        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get();

        $periodos = periodosNomina::where('ejercicio', date('Y'))->orderBy('mes')->get();

        $meses = $periodos->unique('mes');

        $bimestres = $periodos->unique('bimestre');

        return view('consultas.acumulado-nomina.acumulado-nomina', compact('departamentos', 'meses', 'bimestres'));
    }

    public function validaAcumulado(Request $request)
    {

        cambiarBase(Session::get('base'));

        if ($request->tipo_archivo == 'pdf') {
            $pdf = app('dompdf.wrapper');

            if ($request->tipo == 'mes') {
                $datos = $this->generarReporteMensual($request->deptos, $request->mes);
                $mes = $request->mes;
                // return view('consultas.acumulado.reporteMensual', compact('datos', 'mes'));
                $pdf =  Pdf::loadView('consultas.acumulado-nomina.reporteMensual-acumuladoNomina', compact('datos', 'mes'));
                $file = 'reporteAcumuladoMensual.pdf';
            } else if ($request->tipo == 'bim') {
                $datos = $this->generarReporteBimestral($request->deptos, $request->bim);
                $pdf = Pdf::loadView('consultas.acumulado-nomina.reporteBimestral-acumuladoNomina', compact('datos'));
                $file = 'reporteAcumuladoBimestral.pdf';
            } else {
                $bim = null;
                $datos = $this->generarReporteBimestral($request->deptos, $bim);
                $pdf = Pdf::loadView('consultas.acumulado-nomina.reporteBimestral-acumuladoNomina', compact('datos'));
                $file = 'reporteAcumuladoAnual.pdf';
            }

            return $pdf->setPaper('letter', 'landscape')->stream($file);
        } else if ($request->tipo_archivo == 'excel') {


            $parametros_empresa   = DB::connection('empresa')
                ->table('parametros')
                ->first();
            $ejercicio          = $parametros_empresa->ejercicio;
            $tipo_nomina = $parametros_empresa->tipo_nomina;
            $cadena_deptos = implode(',', $request->deptos);

            //dd($request);

            $queryEmisoras = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora,COUNT(em.id) as num_empleados from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id_departamento in ($cadena_deptos) and em.estatus=1 group by ememi.razon_social";
            $emisoras = DB::connection('empresa')->select($queryEmisoras);

            //dump($queryEmisoras);

            $queryEmisoras_fini = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora,COUNT(em.id) as num_empleados from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id_departamento in ($cadena_deptos) group by ememi.razon_social";
            $emisoras_fini = DB::connection('empresa')->select($queryEmisoras_fini);

            //dump($queryEmisoras_fini);

            //dd($emisoras_fini);
            //dd($request);
            if ($request->tipo == 'mes') {
                $periodicidad = "Mensual-" . $request->mes;
                $periodosvalidos = DB::connection('empresa')->table('periodos_nomina')->select('id')
                    ->where('estatus', 1)
                    ->where('ejercicio', $ejercicio)
                    ->where('mes', $request->mes)
                    ->get()->keyBy('id');
                foreach ($periodosvalidos as $periodosvalido) {
                    $periodosv[] = $periodosvalido->id;
                    # code...
                }
                //dd($periodosv);
                $periodos_v = implode(',', $periodosv);

                //dd($periodos_v);
            } else if ($request->tipo == 'bim') {
                $periodicidad = "Bimestral-" . $request->bim;
                $periodosvalidos = DB::connection('empresa')->table('periodos_nomina')->select('id')
                    ->where('estatus', 1)
                    ->where('ejercicio', $ejercicio)
                    ->where('bimestre', $request->bim)
                    ->get()->keyBy('id');
                foreach ($periodosvalidos as $periodosvalido) {
                    $periodosv[] = $periodosvalido->id;
                    # code...
                }
                //dd($periodosv);
                $periodos_v = implode(',', $periodosv);
            } else if ($request->tipo == 'ano') {
                $periodicidad = "Anual-".$ejercicio;
                $periodosvalidos = DB::connection('empresa')->table('periodos_nomina')->select('id')
                    ->where('estatus', 1)
                    ->where('ejercicio', $ejercicio)
                    ->get()->keyBy('id');
                foreach ($periodosvalidos as $periodosvalido) {
                    $periodosv[] = $periodosvalido->id;
                    # code...
                }
                //dd($periodosv);
                $periodos_v = implode(',', $periodosv);
            }


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

            $columnas3 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                ->where('estatus', 1)
                ->where('nomina', 1)
                ->where('tipo', 0)
                ->where(function ($query) {
                    $query->where('file_rool', '>=', 250)
                        ->orWhere('file_rool', 0);
                })->get();

            $columnas4 = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                ->where('estatus', 1)
                ->where('nomina', 1)
                ->where('tipo', 1)
                ->where(function ($query) {
                    $query->where('file_rool', '>=', 250)
                        ->orWhere('file_rool', 0);
                })->get();
            //dd($columnas4);
            ///dd($emisoras);
            foreach ($columnas1 as $col) {
                $qry[] = "sum(total" . $col->id . ") as total" . $col->id;
            }
            $qryStr = implode(', ', $qry);

            foreach ($columnas2 as $col) {
                $qry2[] = "sum(total" . $col->id . ") as total" . $col->id;
            }
            $qryStr_d = implode(', ', $qry2);

            foreach ($columnas3 as $col) {
                $qry3[] = "sum(total" . $col->id . ") as total" . $col->id;
            }
            $qryStr_p_s = implode(', ', $qry3);
            if (count($columnas4) > 0) {
                foreach ($columnas4 as $col) {
                    $qry4[] = "sum(total" . $col->id . ") as total" . $col->id;
                }
                $qryStr_d_s = implode(', ', $qry4);
            } else {
                $qryStr_d_s = 'sum(infonavit)';
            }

            //dump($emisoras);

            foreach ($emisoras as $emisora) {
                $queryvalores = "SELECT $qryStr,sum(total_percepcion_fiscal) as percep_fiscal,$qryStr_d,sum(subsidio)*-1 as subsidio,sum(total_deduccion_fiscal) as deducion_f ,sum(neto_fiscal) as neto_fiscal,$qryStr_p_s,$qryStr_d_s,sum(beneficio_sindical) as beneficio_s,sum(total_percepcion_sindical) as total_p_s,sum(total_deduccion_sindical) as total_d_s,sum(neto_sindical) as neto_s,sum(importe_total) as importe_total from rutinas$ejercicio where id_empleado in ($emisora->cadena_empleados) and fnq_valor=0 and id_periodo in ($periodos_v)";
                //dd($queryvalorpercepfiscal);
                //dd($v);
                $valores_acumulados[$emisora->id] = DB::connection('empresa')->select($queryvalores);
                # code...
                //dd($valores_acumulados[$emisora->id][0]);

                # code...
            }

            $columnas1_fini = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto')
                ->where('estatus', 1)
                ->where('finiquito', 1)
                ->where('tipo', 0)
                ->where('file_rool', '<=', 249)
                ->Where('file_rool', '!=', 0)
                ->get()->keyBy('id');

            $columnas2_fini = DB::connection('empresa')->table('conceptos_nomina')->select('id', 'nombre_concepto', 'rutinas')
                ->where('estatus', 1)
                ->where('finiquito', 1)
                ->where('tipo', 1)
                ->where('file_rool', '<=', 249)
                ->Where('file_rool', '!=', 0)
                ->get()->keyBy('id');
            if (count($columnas1_fini) > 0) {
                foreach ($columnas1_fini as $col) {
                    $qry_f[] = "if(sum(total" . $col->id . ")>0,sum(total" . $col->id . "),0) as total" . $col->id;
                }
                $qryStr_p_f = implode(', ', $qry_f);
            } else {
                $qryStr_p_f = 'sum(infonavit)';
            }

            if (count($columnas2_fini) > 0) {
                foreach ($columnas2_fini as $col) {
                    $qry_f2[] = "if(sum(total" . $col->id . ")>0,sum(total" . $col->id . "),0) as total" . $col->id;
                }
                $qryStr_d_f = implode(', ', $qry_f2);
            } else {
                $qryStr_d_f = 'sum(infonavit)';
            }

            //dd($emisoras_fini);

            foreach ($emisoras_fini as $emisora_fini) {
                $queryvalores_f = "SELECT $qryStr_p_f,if(sum(total_percepcion_fiscal)>0,sum(total_percepcion_fiscal),0) as percep_fiscal,$qryStr_d_f,if((sum(subsidio)*-1)>0,(sum(subsidio)*-1),0) as subsidio,if(sum(total_deduccion_fiscal)>0,sum(total_deduccion_fiscal),0) as deducion_f,if(sum(neto_fiscal)>0,sum(neto_fiscal),0) as neto_fiscal,count(id_empleado) as numero_empleados from rutinas$ejercicio where id_empleado in ($emisora_fini->cadena_empleados) and fnq_valor=1 and id_periodo in ($periodos_v) and neto_fiscal>0";
                //dd($queryvalores_f);
                //dd($v);
                $valores_acumulados_f[$emisora_fini->id] = DB::connection('empresa')->select($queryvalores_f);
                # code...
                //dd($valores_acumulados[$emisora->id][0]);

                # code...
            }



            //dd($emisoras);
            $datos['columnas1'] = $columnas1;
            $datos['columnas2'] = $columnas2;
            $datos['columnasSindical'] = $columnas3;
            $datos['columnasDEDUCC'] = $columnas4;
            $datos['valores'] = $valores_acumulados;
            $datos['columnas1_fini'] = $columnas1_fini;
            $datos['columnas2_fini'] = $columnas2_fini;
            $datos['valores_fini'] = $valores_acumulados_f;
            $datos['Periodicidad'] = $periodicidad;
            $datos['emisoras'] = $emisoras;
            $datos['tipo_nomina'] = $tipo_nomina;
            $datos['emisoras_fini'] = $emisoras_fini;
            //dd($datos);


           //  return view('consultas.acumulado-nomina.reporteExcel-acumuladoNomina', $datos);
            return Excel::download(new ReporteAcumuladoNominaExport($datos), "ReporteAcumuladoNomina" . $periodicidad ."-". date('d-m-Y') . ".xlsx");
        }
    }

    protected function generarReporteMensual($deptos, $mes)
    {
        $empresa            = Session::get('empresa');

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();;


        $ejercicio          = $parametros_empresa->ejercicio;
        // $ejercicio = '2019';


        $queryemisora       = "SELECT upper(emi.razon_social) as rSocial, upper(emi.rfc) as rfc, upper(emi.representante_legal) as rLegal, emi.direccion as direccionempresa from asigna_empresas_emisoras asi join empresas e on asi.id_empresa = e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id where e.id='" . $empresa['id'] . "';";
        $rowresultemisora   = DB::select($queryemisora);
        $emisoraResult      = $rowresultemisora[0];
        $emisora            = $emisoraResult->rSocial;
        $rfc                = $emisoraResult->rfc;
        $representantelegal = $emisoraResult->rLegal;
        $direccionempresa   = $emisoraResult->direccionempresa;


        $querynumregi        = "SELECT re.num_registro_patronal as result from asigna_empresas_emisoras asi join empresas e on asi.id_empresa=e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id inner join registro_patronal re on emi.id = re.id_empresa_emisora where e.id='" . $empresa['id'] . "'";
        $rowresultregistropa = DB::select($querynumregi);
        $registropa          = $rowresultregistropa[0]->result;


        $ids_periodos = "SELECT group_concat(id) as result from periodos_nomina where estatus = 1 and mes = $mes and ejercicio = $ejercicio";
        // dump($ids_periodos);
        $periodos     = DB::connection('empresa')->select($ids_periodos);
        $ids_periodos = explode(',', $periodos[0]->result);

        $FechaPago = periodosNomina::select('fecha_pago')->whereIn('id', $ids_periodos)->where('estatus', 1)->get();


        $conceptosIDs = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('file_rool', '<>', 0)
            ->get()->toArray();

        $caConcep = [];
        foreach ($conceptosIDs as $concepto) {
            $campo = 'valor' . $concepto->id;
            if (Schema::connection('empresa')->hasColumn('rutinas' . $ejercicio, $campo))
                $caConcep[] = $concepto->id;
        }

        $cadenaconcepValidos = implode(",", $caConcep);
        $cadenaconcep        = base64_encode($cadenaconcepValidos);


        $conceptosIDs = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->whereIn('id', $caConcep)
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('tipo', '!=', 3)
            ->where('file_rool', '<',  250)
            ->where('file_rool', '<>', 0)
            ->get();
        // dump($conceptosIDs);

        $conceptosIDsArr = $conceptosIDs->pluck('id')->toArray();
        $f_i = $qry = $percepcionesIds = $deduccionesIds = [];

        // Deducciones y Percecpciones
        foreach ($conceptosIDs as $concepto) {
            if ($concepto->tipo == 0)
                $percepcionesIds[$concepto->id] = $concepto->nombre_concepto;
            if ($concepto->tipo == 1)
                $deduccionesIds[$concepto->id] = $concepto->nombre_concepto;
        }

        $empleados = Empleado::with('departamento')
            ->with('categoria')
            ->where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->whereIn('id_departamento', $deptos)
            ->whereIn('id', function ($query) use ($ejercicio, $ids_periodos) {
                $query->select('id_empleado')
                    ->from('rutinas' . $ejercicio)
                    ->where('fnq_valor', 0)
                    ->whereIn('id_periodo', $ids_periodos);
            })
            ->orderBy('apaterno')
            ->get()->keyBy('id');
        $empleadosStr = implode(',', $empleados->pluck('id')->toArray());


        $ID_conceptoFaltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->whereIn('id', $caConcep)->first();

        // FALTAS, INCAPACIDADES Y RUTINAS VALORES
        foreach ($conceptosIDsArr as $idConcepto) {
            $qry[] = "sum(total" . $idConcepto . ") as total" . $idConcepto;
        }
        $qryStr = implode(', ', $qry);

        $incapacidades_faltas = "SELECT id_empleado, sum(incapacidades) as incapacidades, sum(valor$ID_conceptoFaltas->id) as faltas, sum(total_percepcion_fiscal) as tpf, sum(total_deduccion_fiscal) as tdf, sum(neto_fiscal) as neto_fiscal, $qryStr from rutinas$ejercicio where id_empleado in (" . $empleadosStr . ") and fnq_valor = 0 and id_periodo in (" . implode(',', $ids_periodos) . ") group by id_empleado";
        // dd($incapacidades_faltas);
        $incapacidades_faltas_arr = DB::connection('empresa')->select($incapacidades_faltas);

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

        $querydias = "SELECT  datediff(max(fecha_final_periodo), min(fecha_inicial_periodo)) as dias, min(fecha_inicial_periodo) as min, max(fecha_final_periodo) as max from periodos_nomina where id in (" . implode(',', $ids_periodos) . ");";
        $diasArr   = DB::connection('empresa')->select($querydias);
        $FechaIni  = $diasArr[0]->min;
        $FechaFin  = $diasArr[0]->max;
        $dias      = $diasArr[0]->dias;



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
            $empleado->totales = $f_i[$empleado->id]; // **********************
            // dd($empleado->totales);


        }

        $return = [
            'conceptosIDs' => $conceptosIDs,
            'empleados'    => $empleados,
            'registro_patronal' => $registropa
        ];

        return $return;
    }
    protected function generarReporteBimestral($deptos, $bim)
    {
        $empresa            = Session::get('empresa');

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();;


        $ejercicio          = $parametros_empresa->ejercicio;
        // $ejercicio = '2019';


        $queryemisora       = "SELECT upper(emi.razon_social) as rSocial, upper(emi.rfc) as rfc, upper(emi.representante_legal) as rLegal, emi.direccion as direccionempresa from asigna_empresas_emisoras asi join empresas e on asi.id_empresa = e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id where e.id='" . $empresa['id'] . "';";
        $rowresultemisora   = DB::select($queryemisora);
        $emisoraResult      = $rowresultemisora[0];



        $querynumregi        = "SELECT re.num_registro_patronal as result from asigna_empresas_emisoras asi join empresas e on asi.id_empresa=e.id inner join empresas_emisoras emi on asi.id_empresa_e = emi.id inner join registro_patronal re on emi.id = re.id_empresa_emisora where e.id='" . $empresa['id'] . "'";
        $rowresultregistropa = DB::select($querynumregi);
        $registropa          = $rowresultregistropa[0]->result;

        if ($bim != null) {
            $ids_periodos = "SELECT group_concat(id) as result from periodos_nomina where estatus = 1 and bimestre = $bim and ejercicio = $ejercicio";

            $periodoBim = "Bimestre - " . $bim;
        } else {
            $ids_periodos = "SELECT group_concat(id) as result from periodos_nomina where estatus = 1 and ejercicio = $ejercicio";

            $periodoBim = "Anual - " . $ejercicio;
        }

        $periodos     = DB::connection('empresa')->select($ids_periodos);
        $ids_periodos = explode(',', $periodos[0]->result);

        $FechaPago = periodosNomina::select('fecha_pago')->whereIn('id', $ids_periodos)->where('estatus', 1)->get();


        $conceptosIDs = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->select('id')
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('file_rool', '<>', 0)
            ->get()->toArray();

        $caConcep = [];
        foreach ($conceptosIDs as $concepto) {
            $campo = 'valor' . $concepto->id;
            if (Schema::connection('empresa')->hasColumn('rutinas' . $ejercicio, $campo))
                $caConcep[] = $concepto->id;
        }

        $cadenaconcepValidos = implode(",", $caConcep);
        $cadenaconcep        = base64_encode($cadenaconcepValidos);


        $conceptosIDs = DB::connection('empresa')
            ->table('conceptos_nomina')
            ->whereIn('id', $caConcep)
            ->where('estatus', 1)
            ->where('nomina', 1)
            ->where('tipo', '!=', 3)
            ->where('file_rool', '<',  250)
            ->where('file_rool', '<>', 0)
            ->get();
        // dump($conceptosIDs);

        $conceptosIDsArr = $conceptosIDs->pluck('id')->toArray();
        $f_i = $qry = $percepcionesIds = $deduccionesIds = [];

        // Deducciones y Percecpciones
        foreach ($conceptosIDs as $concepto) {
            if ($concepto->tipo == 0)
                $percepcionesIds[$concepto->id] = $concepto->nombre_concepto;
            if ($concepto->tipo == 1)
                $deduccionesIds[$concepto->id] = $concepto->nombre_concepto;
        }

        $empleados = Empleado::with('departamento')
            ->with('categoria')
            ->where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->whereIn('id_departamento', $deptos)
            ->whereIn('id', function ($query) use ($ejercicio, $ids_periodos) {
                $query->select('id_empleado')
                    ->from('rutinas' . $ejercicio)
                    ->where('fnq_valor', 0)
                    ->whereIn('id_periodo', $ids_periodos);
            })
            ->orderBy('apaterno')
            ->get()->keyBy('id');
        $empleadosStr = implode(',', $empleados->pluck('id')->toArray());


        $ID_conceptoFaltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->whereIn('id', $caConcep)->first();

        // FALTAS, INCAPACIDADES Y RUTINAS VALORES
        foreach ($conceptosIDsArr as $idConcepto) {
            $qry[] = "sum(total" . $idConcepto . ") as total" . $idConcepto;
        }
        $qryStr = implode(', ', $qry);

        $incapacidades_faltas = "SELECT id_empleado, sum(incapacidades) as incapacidades, sum(valor$ID_conceptoFaltas->id) as faltas, sum(total_percepcion_fiscal) as tpf, sum(total_deduccion_fiscal) as tdf, sum(neto_fiscal) as neto_fiscal, $qryStr from rutinas$ejercicio where id_empleado in (" . $empleadosStr . ") and fnq_valor = 0 and id_periodo in (" . implode(',', $ids_periodos) . ") group by id_empleado";
        // dd($incapacidades_faltas);
        $incapacidades_faltas_arr = DB::connection('empresa')->select($incapacidades_faltas);

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

        $querydias = "SELECT  datediff(max(fecha_final_periodo), min(fecha_inicial_periodo)) as dias, min(fecha_inicial_periodo) as min, max(fecha_final_periodo) as max from periodos_nomina where id in (" . implode(',', $ids_periodos) . ");";
        $diasArr   = DB::connection('empresa')->select($querydias);
        $FechaIni  = $diasArr[0]->min;
        $FechaFin  = $diasArr[0]->max;
        $dias      = $diasArr[0]->dias;



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
            $empleado->totales = $f_i[$empleado->id]; // **********************
            // dd($empleado->totales);


        }

        $return = [
            'conceptosIDs' => $conceptosIDs,
            'empleados'    => $empleados,
            'registro_patronal' => $registropa,
            'periodo' => $periodoBim,

        ];

        return $return;
    }
}
