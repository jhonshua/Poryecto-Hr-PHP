<?php

namespace App\Http\Controllers\Procesos;

use App\Http\Controllers\Controller;
use App\Exports\exportPrenomina;
use App\Imports\importPrenomina;
use App\Models\AsistenciaHorario;
use App\Models\ConceptosNomina;
use App\Models\ConfirmacionIncidencia;
use App\Models\IncidenciasProgLog;
use App\Models\IncidenciasProgramadas;
use App\Models\Parametros;
use App\Models\Permiso;
use App\Models\Bitacora;
use App\Models\Sede;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Models\Empleado;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\DB\TableController;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciasExport;
use App\Exports\ReportePreNominaExport;
use App\Exports\ReportePreNominaDetalleExport;
use App\Exports\AsistenciasExport3_periodo;
use Illuminate\Database\Schema\Blueprint;

class NominaController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function periodosNomina()
    {
        cambiarBase(Session::get('base'));

        $user = auth()->user();

        $periodos = PeriodosNomina::where('estatus', '<>', PeriodosNomina::ESTATUS_ELIMINADO)
            ->withCount('timbres')
            ->orderBy('id', 'DESC')->get();

        $departamentos = Departamento::where('estatus', 1)->orderBy('nombre', 'asc')->get();
        $hayPeriodoAbierto = $this->hayPeriodoAbierto();

        $permisos = Permiso::where('id_usuario', $user->id)->first();
        $parametros = Parametros::first();

        return view('nomina.nomina-periodos', compact('periodos', 'departamentos', 'hayPeriodoAbierto', 'permisos', 'parametros'));
    }

    public function crearperiodoNomina()
    {
        return view('nomina.crear-periodo-nomina');
    }

    public function agregarperiodoNomina(Request $request)
    {
        cambiarBase(Session::get('base'));

        if ($this->hayPeriodoEnRangoFechas($request->nombre_periodo, $request->fecha_inicial_periodo, $request->fecha_final_periodo)) {
            session()->flash('danger', 'Actualmente ya existe un periodo '. $request->nombre_periodo.' que se encuentra en el rango de fechas indicado, por favor modifica estos datos.');
            return redirect()->route('nomina.periodos');
        }

        $activo = ($this->hayPeriodoAbierto()) ? periodosNomina::DISP_ABRIR_PERIODO_ACT : periodosNomina::DISP_ABRIR;
        $request->request->add(['activo' => $activo, 'estatus' => periodosNomina::ESTATUS_DISPONIBLE]);

        periodosNomina::create($request->except('_token'));
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de PeriodosdeNomina: {$request->numero_periodo} INSERT');

        $this->verificarTablaDatosFacturacion($request->ejercicio);
       
        $p = DB::connection('empresa')->table('datos_facturacion' . $request->ejercicio)->where('id_periodo', $request->numero_periodo)->get();
        if ($p->count() <= 0) {
            DB::connection('empresa')->table('datos_facturacion' . $request->ejercicio)->insert([
                'id_periodo' => $request->numero_periodo,
                'ejercicio' => $request->ejercicio,
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->verificarTablaRutinasAdic('rutinas' . $request->ejercicio);
        $this->verificarTablaRutinasAdic('adic' . $request->ejercicio);

        session()->flash('success', 'Se creó correctamente el periodo.');

        return redirect()->route('nomina.periodos');

    }

    public function asistenciaPeriodo($request)
    {
        if (isset(SESSION::get('empresa')['tipo_asistencias']) && SESSION::get('empresa')['tipo_asistencias'] == 2) {
            return Excel::download(new AsistenciasExport3_periodo($request), "AsistenciaPeriodoNom{$request}_.xlsx");
        } else {
            return Excel::download(new AsistenciasExport($request), "AsistenciaPeriodoNom{$request}_.xlsx");
        }
    }

    public function actualizarperiodoNomina(Request $request)
    {
        cambiarBase(Session::get('base'));

        if ($this->hayPeriodoEnRangoFechas($request->nombre_periodo, $request->fecha_inicial_periodo, $request->fecha_final_periodo, $request->id)) {
            session()->flash('danger', 'Actualmente ya existe un periodo '. $request->nombre_periodo.' que se encuentra en el rango de fechas indicado, por favor modifica estos datos.');
            return redirect()->route('nomina.periodos');
        }

        periodosNomina::where('id', $request->id)->update($request->except('_token'));
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de PeriodosdeNomina: {$request->numero_periodo} UPDATE');

        session()->flash('success', 'Se editó correctamente el periodo.');

        return redirect()->route('nomina.periodos');
    }

    public function eliminarperiodoNomina(Request $request)
    {

        tienePermiso('periodos_nomina');
        cambiarBase(Session::get('base'));

        PeriodosNomina::where('id', $request->id)->update(['estatus' => periodosNomina::ESTATUS_ELIMINADO, 'activo' => periodosNomina::CERRADO]);
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de PeriodosdeNomina ID: {$request->id} DELETE');

        session()->flash('success', 'El periodo se eliminó correctamente.');

        return redirect()->route('nomina.periodos');
    }

    public function actualizarBiometrico(Request $request)
    {
        $this->actualizar($request);

        session()->flash('success', 'Se ha actualizado correctamente el Periodo');

        return redirect()->route('nomina.periodos');
    }

    public function actualizar(Request $request)
    {
        cambiarBase(Session::get('base'));

        $periodo = periodosNomina::find($request->idPeriodo);
        $this->verificarTablaDatosFacturacion($periodo->ejercicio);

        $this->verificarTablaRutinasAdic('rutinas' . $periodo->ejercicio);
        $this->verificarTablaRutinasAdic('adic' . $periodo->ejercicio);

        // Activar los conceptos de nomina
        ConceptosNomina::where('estatus', 1)->update(['activo_en_nomina' => 1]);

        $this->actualizarEmpleados($periodo->ejercicio, $periodo->id);

        session()->flash('success', 'Se ha actualizado correctamente el Periodo');

        return redirect()->route('nomina.periodos');
    }

    protected function actualizarEmpleados($ejercicio, $periodoId)
    {
        cambiarBase(Session::get('base'));
        $empleados = Empleado::select('id')->where('estatus', Empleado::EMPLEADO_ACTIVO)->get();
        $empleadosRutinas =  DB::connection('empresa')->table('rutinas' . $ejercicio)->select('id_empleado')
            ->where('id_periodo', $periodoId)->get()->keyBy('id_empleado');
        $empleadosAdic =  DB::connection('empresa')->table('adic' . $ejercicio)->select('id_empleado')
            ->where('id_periodo', $periodoId)->get()->keyBy('id_empleado');

        foreach ($empleados as $empleado) {
            if (!$empleadosRutinas->contains('id_empleado', $empleado->id)) {

                DB::connection('empresa')->table('rutinas' . $ejercicio)->insert([
                    'id_periodo' => $periodoId,
                    'id_empleado' => $empleado->id,
                    'fnq_valor' => 0
                ]);

                /*TODO: Revisar bien por que se hace este registro doble
                 DB::connection('empresa')->table('rutinas' . $ejercicio)->insert([
                    'infonavit' => '',
                    'id_periodo' => $periodoId,
                    'id_empleado' => $empleado->id,
                    'fnq_valor' => 1
                ]);
                */
            }

            if (!$empleadosAdic->contains('id_empleado', $empleado->id)) {
                DB::connection('empresa')->table('adic' . $ejercicio)->insert([
                    'id_periodo' => $periodoId,
                    'id_empleado' => $empleado->id,
                    'fnq_valor' => 0
                ]);

                /*TODO: Revisar bien por que se hace este registro doble
                 DB::connection('empresa')->table('adic' . $ejercicio)->insert([
                    'infonavit' => '',
                    'id_periodo' => $periodoId,
                    'id_empleado' => $empleado->id,
                    'fnq_valor' => 1
                ]);*/
            }
        }
    }

    protected function sincronizarAsistencias_calcularFaltas($fecha_inicial_biometrico, $fecha_final_biometrico, $idPeriodo, $ejercicio, $concepto_faltas)
    {
        try {

            cambiarBase(Session::get('base'));

            $comprobar_asistencia_horario=AsistenciaHorario::first();
            if(!isset($comprobar_asistencia_horario)){
                return false;
            }

            $dias_a_revisar = CarbonPeriod::create($fecha_inicial_biometrico, $fecha_final_biometrico);

            // Sacamos los empleados activos y con un horario
            $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
                ->with('horario')
                ->where('id_horario', '<>', 0)
                ->orderBy('id', 'asc')
                ->get();

            $dias_festivos = DB::connection('empresa')->table('horarios_dias')
                ->select('fecha_festiva', 'id_horario')
                // ->where('id_horario', $empleado->id_horario)
                ->whereBetween('fecha_festiva', [$fecha_inicial_biometrico, $fecha_final_biometrico])
                ->get();

            $horarios_festivos = [];
            if(count($dias_festivos)){
                foreach ($dias_festivos as $diaf) {
                    $horarios_festivos[$diaf->id_horario][] = $diaf->fecha_festiva;
                }
            }


            foreach ($empleados as $empleado) {

                // sacamos y verificamos que no haya un registro hecho por la APP (para no sobreescribirlo)
                $asistencia_horario = DB::connection('empresa')->table('asistencia_horario')
                    ->where('id_empleado', $empleado->id)
                    ->whereBetween('dia', [$fecha_inicial_biometrico, $fecha_final_biometrico])
                    ->get()->keyBy('dia');


                foreach ($dias_a_revisar as $dia) {

                    $fecha_a_revisar = $dia->format('Y-m-d');

                    // Si NO hay un registro hecho desde la APP
                    if (isset($asistencia_horario[$fecha_a_revisar]) && empty($asistencia_horario[$fecha_a_revisar]->coordenadas_1)) {

                        $num_dia = date('N', strtotime($fecha_a_revisar)); // Num dia de la semana
                        $dias_laborales = [
                            1 => $empleado->horario->lunes,
                            2 => $empleado->horario->martes,
                            3 => $empleado->horario->miercoles,
                            4 => $empleado->horario->jueves,
                            5 => $empleado->horario->viernes,
                            6 => $empleado->horario->sabado,
                            7 => $empleado->horario->domingo,
                        ];

                        // Si es dia festivo o NO laborable - no se insertará registro
                        if ($dias_laborales[$num_dia] == 0 || in_array($fecha_a_revisar, $horarios_festivos[$empleado->id_horario])) {
                            continue;
                        }

                        $entradaHorario = $empleado->horario->entrada;
                        $salidaHorario = $empleado->horario->salida;

                        //Se valida si el dia Domingo es laborable para el empleado
                        if ($num_dia == 7 && $empleado->horario->domingo) {
                            $entradaHorario = $empleado->horario->domingo_entrada;
                            $salidaHorario = $empleado->horario->domingo_salida;
                        }
                        //Se valida si el dia Sabado es laborable para el empleado
                        else if ($num_dia == 6 && $empleado->horario->sabado) {
                            $entradaHorario = $empleado->horario->sabado_entrada;
                            $salidaHorario = $empleado->horario->sabado_salida;
                        }

                        //Se define la hora de entrada del empleado tomando en cuenta los minutos de tolerancia
                        $entrada_con_tolerancia = strtotime('+' . $empleado->horario->tolerancia . ' minutes', strtotime($fecha_a_revisar . ' ' . $entradaHorario));
                        $entrada_con_tolerancia = date('Y-m-d H:i:s', $entrada_con_tolerancia); // ------------------

                        //Consultamos todos los registros de asistencia con el dia que esta comprobando
                        $asistencias = DB::connection('empresa')->table('asistencias')
                            ->where('id_empleado', $empleado->id)
                            ->where('fecha', 'like', $fecha_a_revisar . '%')
                            ->get();

                        if ($asistencias->count() > 0) {

                            //Se define la hora de entrada y salida sacando el valor minimo y maximo de las asistencias
                            $entrada = $asistencias->min('fecha'); //**************
                            $salida = $asistencias->max('fecha'); //**************

                            //Se compara la hora de entrada encontrada
                            $entrada_biometrico = explode(' ', $entrada);
                            $entrada_biometrico = $entrada_biometrico[1];

                            //Se compara la hora de entrada asignada por el horario
                            $entrada_con_tolerancia = explode(' ', $entrada_con_tolerancia);
                            $entrada_con_tolerancia = $entrada_con_tolerancia[1];

                            //Se comparan las horas para verficar si existe retardo
                            $entrada_biometrico = strtotime($entrada_biometrico);
                            $entrada_con_tolerancia = strtotime($entrada_con_tolerancia);
                            $retardo = ($entrada_biometrico > $entrada_con_tolerancia) ? 1 : 0; //**************

                            //Si el empleado tiene asignado horario de comida se valida los registros
                            if ($empleado->horario->comida == 1) {

                                //Se establece el horario de entrada para la hora de comida
                                $entrada_comida = strtotime('-30 minute', strtotime($fecha_a_revisar . ' ' . $empleado->horario->entrada_comida));
                                $entrada_comida = date('Y-m-d H:i:s', $entrada_comida);

                                //Se establece el horario de salida para la hora de comida
                                $salida_comida = strtotime('+30 minute', strtotime($fecha_a_revisar . ' ' . $empleado->horario->salida_comida));
                                $salida_comida = date('Y-m-d H:i:s', $salida_comida);

                                $registros_comida = $asistencias->whereBetween('fecha', [$entrada_comida, $salida_comida]);

                                if ($registros_comida->count() > 0) {
                                    $entrada_comida = $registros_comida->min('fecha'); //**************
                                    $salida_comida = $registros_comida->max('fecha'); //**************
                                } else {
                                    $entrada_comida = null;
                                    $salida_comida = null;
                                }
                            } else {
                                $entrada_comida = null;
                                $salida_comida = null;
                            }
                        } else {
                            $entrada = null;
                            $salida = null;
                            $entrada_comida = null;
                            $salida_comida = null;
                            $retardo = 0;
                        }

                        //Se define si hay asistencia
                        $asistencia = (empty($entrada)) ? 0 : 1; // ***************

                        $registro = DB::connection('empresa')->table('asistencia_horario')->updateOrInsert(
                            [
                                'id_empleado' => $empleado->id,
                                'dia' => $fecha_a_revisar,
                            ],
                            [
                                'entrada' => $entrada,
                                'entrada_horario' => $fecha_a_revisar . ' ' . $empleado->horario->entrada,
                                'salida' => $salida,
                                'salida_horario' => $fecha_a_revisar . ' ' . $empleado->horario->salida,
                                'comida' => $empleado->horario->comida,
                                'inicio_comida' => $entrada_comida,
                                'inicio_comida_horario' => $fecha_a_revisar . ' ' . $empleado->horario->entrada_comida,
                                'fin_comida' => $salida_comida,
                                'fin_comida_horario' => $fecha_a_revisar . ' ' . $empleado->horario->salida_comida,
                                'retardo' => $retardo,
                                'asistencia' => $asistencia,
                            ]
                        );
                    }
                }

                $query_faltas = DB::connection('empresa')->table('asistencia_horario')
                    ->where('id_empleado', $empleado->id)
                    ->whereBetween('dia', [$fecha_inicial_biometrico, $fecha_final_biometrico])
                    ->where('asistencia', 0)
                    ->where('permiso', 0);

                if (isset($horarios_festivos[$empleado->id_horario]) && count($horarios_festivos[$empleado->id_horario]) > 0)
                    $query_faltas->whereNotIn('dia', $horarios_festivos[$empleado->id_horario]);

                $faltas = $query_faltas->count();




                $query_retardos = DB::connection('empresa')->table('asistencia_horario')
                    ->where('id_empleado', $empleado->id)
                    ->whereBetween('dia', [$fecha_inicial_biometrico, $fecha_final_biometrico])
                    ->where('asistencia', 1)
                    ->where('retardo', 1);

                if (isset($horarios_festivos[$empleado->id_horario]) && count($horarios_festivos[$empleado->id_horario]) > 0)
                    $query_retardos->whereNotIn('dia', $horarios_festivos[$empleado->id_horario]);

                $retardos = $query_retardos->count();



                $faltas_retardos = 0;
                if ($empleado->horario->retardos > 0) {
                    $faltas_retardos = floor($retardos / $empleado->horario->retardos);
                }
                $total_faltas = $faltas_retardos + $faltas;

                if ($empleado->horario->indefinido != 1) {

                    DB::connection('empresa')->table('rutinas' . $ejercicio)
                        ->where('id_periodo', $idPeriodo)
                        ->where('fnq_valor', 0)
                        ->where('id_empleado', $empleado->id)
                        ->update([
                            'valor' . $concepto_faltas => $total_faltas
                        ]);
                }
            }
        } catch (\PDOException  $e) {
        }
    }

    public function cerrarperiodo(Request $request)
    {
        cambiarBase(Session::get('base'));

        // Cerrar Periodo
        $periodo = PeriodosNomina::find($request->idPeriodo);
        $periodo->activo = PeriodosNomina::CERRADO;
        $periodo->save();

        // Habilitar otros Periodos
        PeriodosNomina::where('id', '!=', $request->idPeriodo)
            ->where('activo', PeriodosNomina::DISP_ABRIR_PERIODO_ACT)
            ->where('estatus', '!=', PeriodosNomina::ESTATUS_ELIMINADO)
            ->update(['activo' => PeriodosNomina::DISP_ABRIR]);

        // $queryvalidaempleados = "SELECT * from $base.$tabla where idperiodo='$idperiodo' and FnqValor=0 and idempleado in (select idempleado from $base.empleado where status=0)";
        $empleados = DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
            ->select('id_empleado')
            ->where('id_periodo', $request->idPeriodo)
            ->where('fnq_valor', 0)
            ->whereIn('id_empleado', function ($query) {
                $query->select('id')
                    ->from(with(new Empleado)->getTable())
                    ->where('estatus', Empleado::EMPLEADO_ACTIVO);
            })->get();

        $empleadosArr = [];
        foreach ($empleados as $empleado) {
            $empleadosArr[] = $empleado->id_empleado;
        }

        DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)->where('id_periodo', $periodo->id)->whereIn('id_empleado', $empleadosArr)->update(['estatus' => 0]);

        $logs = IncidenciasProgLog::whereIn('id_empleado', $empleadosArr)
            ->where('id_periodo', $periodo->id)
            ->get()->keyBy('id_empleado')->toArray();

        // Actualizar descuentos y aportaciones
        /*TODO pasar a las nueva stablas de decucciones y percepciones
         * 0 en pausa
         * 1 activo normal
         * 2 pagada completamente
         * 3 eliminado
        */
        foreach ($empleados as $empleado) {
            $deduccionesTmp = DB::connection('empresa')->table('empleados_deducciones')->where('id_empleado', $empleado->id_empleado)->where('estatus', 1)->get();
            if ($deduccionesTmp->count() > 0) {
                foreach ($deduccionesTmp as $incidencia) {
                    $saldo_upd = $incidencia->saldo - $incidencia->cantidad_a_descontar;

                    $st = 1;
                    $nPagos = $incidencia->numero_pagos_realizados + 1;
                    //CASO UNO es unica aportacion
                    if ($incidencia->numero_pagos_a_realizar == 1) {
                        $st = 2;
                    } else if ($incidencia->numero_pagos_a_realizar > 1) {
                        /* checamos si los totales son diferentes */
                        if ($saldo_upd <= 0) {
                            /* hacemos update si es cero o menos, entonces se temrina*/
                            $st = 2;
                        } else {
                            /* si no actualizamos saldo solmante*/
                            $st = 1;
                        }
                    }
                    DB::connection('empresa')->table('empleados_deducciones')
                        ->where('id', $incidencia->id)
                        ->where('id_concepto', $incidencia->id_concepto)
                        ->update(['saldo' => $saldo_upd, 'numero_pagos_realizados' => $nPagos, 'estatus' => $st]);
                }
            }

            // Actualizar descuentos y aportaciones
            foreach ($empleados as $empleado) {
                if (!array_key_exists($empleado->id_empleado, $logs)) {
                    $incidencias = IncidenciasProgramadas::where('id_empleado', $empleado->id_empleado)->where('activa_descuento', 1)->where('percep_deduc', 1)->where('estatus', '!=',  0)->get();
                    $aportaciones = IncidenciasProgramadas::where('id_empleado', $empleado->id_empleado)->where('activa_aportacion', 1)->where('percep_deduc', 1)->where('estatus', '!=',  0)->get();

                    if ($incidencias->count() > 0) {

                        foreach ($incidencias as $incidencia) {

                            $saldo_upd = $incidencia->saldo - $incidencia->importe_a_descontar;
                            $total_descuento_upd = $incidencia->total_descuento + $incidencia->importe_a_descontar;

                            if ($saldo_upd == 0) {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $incidencia->id)->where('id_concepto', $incidencia->id_concepto)
                                    ->update(['saldo' => $saldo_upd, 'total_descuento' => $total_descuento_upd, 'activa_descuento' => 0]);
                            } elseif ($saldo_upd < 0) {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $incidencia->id)->where('id_concepto', $incidencia->id_concepto)
                                    ->update(['activa_descuento' => 0]);
                            } else {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $incidencia->id)->where('id_concepto', $incidencia->id_concepto)
                                    ->update(['saldo' => $saldo_upd, 'total_descuento' => $total_descuento_upd]);
                            }

                            DB::connection('empresa')->table('incidencias_prg_log')->insert(['id_empleado' => $empleado->id_empleado, 'id_periodo' => $periodo->id, 'id_concepto' => $incidencia->id_concepto, 'fecha_creacion' => date('Y-m-d H:i:s')]);
                        }
                    } elseif ($aportaciones->count() > 0) {

                        foreach ($aportaciones as $aportacion) {

                            $importe_a_aportar_upd = $aportacion->importe / $aportacion->numero_pagos;
                            $total_aportacion_upd = $aportacion->total_aportaciones + $importe_a_aportar_upd;

                            if ($total_aportacion_upd == $aportacion->importe) {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $aportacion->id)->where('id_concepto', $aportacion->id_concepto)
                                    ->update(['total_aportaciones' => $total_aportacion_upd, 'activa_aportacion' => 0]);
                            } elseif ($total_aportacion_upd > $aportacion->importe) {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $aportacion->id)->where('id_concepto', $aportacion->id_concepto)
                                    ->update(['activa_aportacion' => 0]);
                            } else {

                                DB::connection('empresa')->table('incidencias_prg')->where('id', $aportacion->id)->where('id_concepto', $aportacion->id_concepto)
                                    ->update(['total_aportaciones' => $total_aportacion_upd]);
                            }

                            DB::connection('empresa')->table('incidencias_prg_log')->insert(['id_empleado' => $empleado->id_empleado, 'id_periodo' => $periodo->id, 'id_concepto' => $aportacion->id_concepto, 'fecha_creacion' => date('Y-m-d H:i:s')]);
                        }
                    }
                }
            }

            DB::connection('empresa')->table('conceptos_nomina')->where('activo_en_nomina', 1)->where('estatus', '!=', 0)->update(['activo_en_nomina' => 0]);


            if ($request->enviarAvisos == false) {
                // return redirect()->route('parametria.periodosnomina')
                //     ->with('tipo_alerta', 'success')
                //     ->with('mensaje', 'Se ha cerrado correctamente el Periodo ' . $request->idPeriodo);

                session()->flash('success', 'Se ha cerrado correctamente el Periodo');

                return redirect()->route('nomina.periodos');

            }
        }
    }

    public function departamentosPeriodo(Request $request)
    {
        tienePermiso('periodos_nomina');
        tienePermiso('abrir_nomina');
        cambiarBase(Session::get('base'));

        $periodo = periodosNomina::find($request->id_periodo);
        $tblRutinas = 'rutinas' . $periodo->ejercicio;
        $deptos = Empleado::distinct('id_departamento')
            ->select('id_departamento')
            ->join($tblRutinas, $tblRutinas . '.id_empleado', '=', 'empleados.id')
            ->where('empleados.estatus', Empleado::EMPLEADO_ACTIVO)
            // ->where('empleados.tipo_de_nomina', 'like',  $periodo->nombre_periodo)
            ->where($tblRutinas . '.id_periodo',  $periodo->id)
            ->get();
        return response()->json(['ok' => 1, 'deptos' => $deptos]);
    }

    public function imprimirNomina(Request $request)
    {
        tienePermiso('periodos_nomina');
        $departamentos = $request->deptos;

        $datos = $this->generarReporteNomina($request->idPeriodo, $departamentos);
       
        $periodo = $datos['periodo'];
        $columnas1 = $datos['columnas1'];
        $columnas2 = $datos['columnas2'];
        $columnasSindical = $datos['columnasSindical'];
        $columnaPVAC = $datos['columnaPVAC'];
        $columnasDEDUCC = $datos['columnasDEDUCC'];
        $columnas3 = $datos['columnas3'];
        $empleados = $datos['empleados'];
        $totales = $datos['totales'];
        $parametros_empresa = $datos['parametros_empresa'];
        $emisoras = $datos['emisoras'];

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();


        return view('nomina.reporte-nomina', compact('periodo', 'departamentos', 'columnas1', 'columnas2', 'columnasSindical', 'columnaPVAC', 'columnasDEDUCC', 'columnas3', 'empleados', 'totales', 'parametros_empresa', 'emisoras', 'parametros_empresa'));
    }

    public function exportarImprimir(Request $request)
    {
        tienePermiso('periodos_nomina');
        $departamentos = $request->deptos;

        $datos = $this->generarReporteNomina($request->idPeriodo, $departamentos);

        $nombre_archivo = "Nomina_{$request->idPeriodo}_" . date('d-m-Y') . ".xlsx";
       
        return Excel::download(new ReportePreNominaExport($datos), $nombre_archivo);
    }

    public function exportarDetalle(Request $request)
    {
        tienePermiso('periodos_nomina');
        $departamentos = $request->deptos;
        $datos = $this->generarReporteNominaDetalle($request->idPeriodo, $departamentos);

        return Excel::download(new ReportePreNominaDetalleExport($datos), "NominaDetalle_{$request->idPeriodo}_" . date('d-m-Y') . ".xlsx");
    }

    protected function hayPeriodoAbierto()
    {
        $periodos_abiertos = PeriodosNomina::where('estatus', PeriodosNomina::ESTATUS_DISPONIBLE)->where('activo', PeriodosNomina::ACTIVO)->count();
        return ($periodos_abiertos) ? true : false;
    }
    
    public function prenomina(Request $request)
    {
        cambiarBase(Session::get('base'));
        $deptos_asignados = Session::get('usuarioDepartamentos');
        $sedes_asignadas = Session::get('usuarioSedes');

        $periodo = periodosNomina::where('activo', periodosNomina::ACTIVO)->first();
  
        if (!$this->hayPeriodoAbierto()) {
            session()->flash('danger', 'No existen prenominas activas.');
            return redirect()->route('nomina.periodos')->with('tipo_alerta', 'danger')->with('mensaje', 'No exsten prenominas activas.');
        }

        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get();
        $columnas = ConceptosNomina::where('tipo_proceso', 0)->where('estatus', 1)->where('file_rool', '!=', 0)->where('activo_en_nomina', 1)->where('nomina', 1)->get();

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->where('tipo_de_nomina', 'like',  $periodo->nombre_periodo)
            ->whereIn('id_departamento', $deptos_asignados);
        
        $tiene_sedes = Session::get('empresa')['sede'];
        $sedes = array();

        if ($tiene_sedes == 1) {
            $empleados = $empleados->whereIn('sede', $sedes_asignadas);
            $sedes = Sede::where('estatus', 1)->whereIn('id', $sedes_asignadas)->orderBy('nombre', 'asc')->get();
        }

        $empleados = $empleados->orderBy('apaterno', 'asc')->get();

        $confirmar_incidencias = Session::get('empresa')['confirmar_incidencias'];
        $validacion_incidencias = array('estatus' => 0);

        if($confirmar_incidencias == 1)
            $validacion_incidencias = $this->confirmacionInsidencias($periodo);

        $valores_rutinas_empleados = DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
            ->where('id_periodo', $periodo->id)
            ->where('fnq_valor', 0)
            ->get()->keyBy('id_empleado');

        $dias_imss = Session::get('empresa')['dias_imss'];

        if ($dias_imss == 1 && $empleados) {
            foreach ($empleados as $empleado) {

                if ($empleado->fecha_alta > $periodo->fecha_inicial_periodo) {
                    $fAlta = Carbon::parse($empleado->fecha_alta);
                    $fFinal = Carbon::parse($periodo->fecha_final_periodo);
                    $dias_naturales_periodo = $fAlta->diffInDays($fFinal) + 1;
                } else {
                    $fInicial = Carbon::parse($periodo->fecha_inicial_periodo);
                    $fFinal = Carbon::parse($periodo->fecha_final_periodo);
                    $dias_naturales_periodo = $fFinal->diffInDays($fInicial) + 1;
                }

                $dias_imss_empleado = (isset($valores_rutinas_empleados[$empleado->id])) ? $valores_rutinas_empleados[$empleado->id]->dias_imss : '';

                if(($empleado->fecha_alta > $periodo->fecha_inicial_periodo) && ($dias_imss_empleado >= $dias_naturales_periodo))
                    $dias_naturales = $dias_naturales_periodo;
                elseif ($dias_imss_empleado == '')
                    $dias_naturales = $dias_naturales_periodo;
                elseif(($dias_imss_empleado != $dias_naturales_periodo))
                    $dias_naturales = $dias_imss_empleado;
                else
                    $dias_naturales = $dias_naturales_periodo;

                DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
                    ->where('id_empleado', $empleado->id)
                    ->where('fnq_valor', 0)
                    ->where('id_periodo', $periodo->id)
                    ->update(['dias_imss' => $dias_naturales]);
            }
        }
     
        return view('parametria.periodos-nomina.prenomina', compact('validacion_incidencias', 'confirmar_incidencias', 'dias_imss', 'columnas', 'empleados', 'valores_rutinas_empleados', 'periodo', 'departamentos', 'tiene_sedes', 'sedes'));
    }

    public function confirmacionInsidencias($periodo)
    {
        $proceso_validacion = DB::connection('empresa')->table('confirmacion_incidencias')
            ->where('id_periodo', $periodo->id)
            ->first();
 
        $faltantes = array();
        
        if ($proceso_validacion != null) {

            if(tienePermisoABool('confirmacion_gerente')) { // es gerente
                
                if($proceso_validacion->estatus == 1) {
                   
                    $confirmaron = explode(",",$proceso_validacion->confirmar);
                    $faltantes = $this->faltantesConfirmar($confirmaron,'confirmacion_gerente');

                    if(in_array(Auth::user()->id,$confirmaron)){
                        $validacion_incidencias['estatus'] = 0;
                        $validacion_incidencias['msg'] = "ya confirmó";
                        $validacion_incidencias['bloqueo'] = 1;
                        
                    }else{
                        $validacion_incidencias['estatus'] = 1;
                        $validacion_incidencias['msg'] = "validación gerencial";
                        $validacion_incidencias['bloqueo'] = 0;
                    }
                    
                } else {
                    $validacion_incidencias['estatus'] = 0;
                    $validacion_incidencias['msg'] = "es gerente y ya validaron";
                    $validacion_incidencias['bloqueo'] = 1;
                }
            } else if (tienePermisoABool('confirmacion_estatal')) { // es distrital
                if ($proceso_validacion->estatus == 2) {
                    $ratificaron = explode(",",$proceso_validacion->ratificar);
                    $faltantes = $this->faltantesConfirmar($ratificaron,'confirmacion_estatal');
                    if(in_array(Auth::user()->id,$ratificaron)){
                        $validacion_incidencias['estatus'] = 0;
                        $validacion_incidencias['msg'] = "es distrital y ya confirmó";
                        $validacion_incidencias['bloqueo'] = 1;
                    }else{
                        $validacion_incidencias['estatus'] = 2;
                        $validacion_incidencias['msg'] = "validación distrital";
                        $validacion_incidencias['bloqueo'] = 0;
                    }
                    
                } else {
                    $validacion_incidencias['estatus'] = 0;
                    $validacion_incidencias['msg'] = "es distrital y ya validaron";
                    $validacion_incidencias['bloqueo'] = 1;
                }
            } else if (tienePermisoABool('confirmacion_prenomina')) { // es rh de empresa
                if ($proceso_validacion->estatus == 3) {
                    $verificaron = explode(",",$proceso_validacion->verificar);
                    $faltantes = $this->faltantesConfirmar($verificaron,'confirmacion_prenomina');
                    if(in_array(Auth::user()->id,$verificaron)){
                        $validacion_incidencias['estatus'] = 0;
                        $validacion_incidencias['msg'] = "es rh de empresa y ya ratificó";
                        $validacion_incidencias['bloqueo'] = 1;
                    }else{
                        $validacion_incidencias['estatus'] = 3;
                        $validacion_incidencias['msg'] = "validación rh";
                        $validacion_incidencias['bloqueo'] = 0;
                    }
                    
                } else {
                    $validacion_incidencias['estatus'] = 0;
                    $validacion_incidencias['msg'] = "es rh de empresa y ya validaron";
                    $validacion_incidencias['bloqueo'] = 1;
                }
            } else { //permisos
                if ($proceso_validacion->estatus == 4) {
                    $validacion_incidencias['estatus'] = 4;
                    $validacion_incidencias['msg'] = "validación RH singh";
                    $validacion_incidencias['bloqueo'] = 0;
                } else {
                    $validacion_incidencias['estatus'] = 0;
                    $validacion_incidencias['msg'] = "es rh de singh, ". $proceso_validacion->estatus;
                    $validacion_incidencias['bloqueo'] = 1;
                }
            }

            if ($proceso_validacion->estatus == 1) { $validacion_incidencias['estatus_mensaje'] = "Las incidencias se encuentran en validación gerencial"; }
            else if ($proceso_validacion->estatus == 2) { $validacion_incidencias['estatus_mensaje'] = "Las incidencias se encuentran en validación distrital"; }
            else if ($proceso_validacion->estatus == 3) { $validacion_incidencias['estatus_mensaje'] = "Las incidencias se encuentran en validación RH"; }

        } else {
           $validacion_incidencias['estatus_mensaje'] = "Las incidencias se encuentran en validación gerencial";
           $faltantes = $this->faltantesConfirmar(array(),'confirmacion_gerente');

            if (tienePermisoABool('confirmacion_gerente')) { // es gerente
                $validacion_incidencias['estatus'] = 1;
                $validacion_incidencias['msg'] = "validación gerencial ";
                $validacion_incidencias['bloqueo'] = 0;
            } else {
                $validacion_incidencias['estatus'] = 0;
                $validacion_incidencias['msg'] = "No tiene permiso para validación gerencial";
                $validacion_incidencias['bloqueo'] = 1;
            }
        }

        $validacion_incidencias['faltantes'] = $faltantes;
        return $validacion_incidencias;
    }

    public function prenominaExportar(Request $request)
    {
        $id_periodo = decrypt($request->idPeriodo);
        return Excel::download(new exportPrenomina($request->idPeriodo), "Prenomina-{$id_periodo}.xlsx");
    }

    public function prenominaImportar(Request $request)
    {
        try{
            $id_periodo = decrypt($request->id_periodo);

            Excel::import(new importPrenomina($id_periodo), $request->file('prenomina'));
            
            //envioAvisosXMail(Session::get('base'), 130,$id_periodo);
            /*return redirect()->route('parametria.periodosnomina.prenomina', $id_periodo)
                ->with('tipo_alerta', 'success')
                ->with('mensaje', 'Se importó correctamente el archivo.');*/
                session()->flash('success', 'Documento cargado exitosamente !!');

        }catch(\Exception $e){

            session()->flash('danger', 'Error al cargar el archivo intente nuevamente !!');
        }

        return redirect()->route('procesos.periodos.nomina.prenomina');
    }

    public function prenominaEmpleado(Request $request)
    {
        try{
            //tienePermisoA('periodos_nomina');
            cambiarBase(Session::get('base'));
    
            $periodo = periodosNomina::find($request->id_periodo);

            $dias_imss = Session::get('empresa')['dias_imss'];
            $permiso_dias_imss = array_key_exists('dias_imss', Session::get('usuarioPermisos'));
            
            
            $datos = ($dias_imss && $permiso_dias_imss) ? $request->except(['_token', 'id_empleado', 'id_periodo']) : $request->data->except(['id_empleado', 'id_periodo', 'dias_imss']);
    
            DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
                ->where('id_empleado', $request->id_empleado)
                ->where('id_periodo', $periodo->id)
                ->where('fnq_valor', 0)
                ->update($datos);

            logEmpresa(Session::get('base'), Auth::user()->email, 'Carga individual de prenomina para el empleado: ' . $request->id_empleado . ' y Periodo ID: ' . $periodo->id);
            
            $respuesta=1;

        }catch(\Exception $e){
            
            $respuesta = $e;
        }

        return response()->json(['respuesta'=>$respuesta]);
    }

    public function prenominaConfirmar(Request $request)
    {
        try{
            
            cambiarBase(Session::get('base'));
            
            $periodo = PeriodosNomina::where('id', $request->periodo)->first();
            $proceso_validacion = ConfirmacionIncidencia::where('id_periodo', $periodo->id)->first();
        
            if ($proceso_validacion != null) {
                //$request->operacion
                if($request->operacion == 1){ // gerencial
                    
                    $permisos = Permiso::select('id_usuario')->where('confirmacion_gerente', 1)->get()->keyBy('id_usuario')->toArray();
                    $confirmaron = explode(",",$proceso_validacion->confirmar);
                    $confirmaron[] = Auth::user()->id;
                
                // dump($permisos,$confirmaron);
                    foreach($confirmaron as $i => $c){

                        if($c != ""){
                            
                            if(isset($permisos[$c])){
                                
                                unset($permisos[$c]);
                            }
                        }else{
                            
                            unset($confirmaron[$i]);
                        }
                    }
                    // dd($permisos,$confirmaron,count($permisos));
                
                    (count($permisos) == 0) ? $datos = ['estatus' => 2,'confirmar' => implode(',',$confirmaron)] : $datos = ['confirmar' => implode(',',$confirmaron)];
            
                }else if($request->operacion == 2){ //distrital
                    
                    $permisos = Permiso::select('id_usuario')->where('confirmacion_estatal', 1)->get()->keyBy('id_usuario')->toArray();
                    $ratificaron = explode(",",$proceso_validacion->ratificar);
                    $ratificaron[] = Auth::user()->id;
                    
                    foreach($ratificaron as $i => $c){
                        
                        if($c != ""){
                            
                            if(isset($permisos[$c])){
                                
                                unset($permisos[$c]);
                            }
                        }else{
                            
                            unset($ratificaron[$i]);
                        }
                    }
            
                    (count($permisos) == 0) ? $datos = ['estatus' => 3,'ratificar' => implode(',',$ratificaron)] : $datos = ['ratificar' => implode(',',$ratificaron)];
            
                }else if($request->operacion == 3){ // RH

                    $permisos = Permiso::select('id_usuario')->where('confirmacion_prenomina', 1)->get()->keyBy('id_usuario')->toArray();
                    $verificaron = explode(",",$proceso_validacion->verificar);
                    $verificaron[] = Auth::user()->id;
                    
                    foreach($verificaron as $i => $c){

                        if($c != ""){
                            
                            if(isset($permisos[$c])){
                                
                                unset($permisos[$c]);
                            }
                        }else{
                            
                            unset($verificaron[$i]);
                        }
                    }
                    foreach($verificaron as $c){
                        
                        if(isset($permisos[$c])){
                            
                            unset($permisos[$c]);
                        }
                    }

                    (count($permisos) == 0)? $datos = ['estatus' => 4,'verificar' => implode(',',$verificaron)] : $datos = ['verificar' => implode(',',$verificaron)];

                }
                
                ConfirmacionIncidencia::where('id',$proceso_validacion->id)->update($datos);
            }else{

                ConfirmacionIncidencia::create([
                    'id_periodo' => $periodo->id,
                    'estatus' => 1,
                    'confirmar' => Auth::user()->id
                ]);
            }

            $cuerpo = "El usuario " . $request->correo . " confirmó correctamente las incidencias del periodo " . $periodo->nombre_periodo . " (" . $periodo->fecha_inicial_periodo . " al " . $periodo->fecha_final_periodo . ") , el día " . date('d-m-Y H:i:s');
            //dump($_POST,$periodo);
            $data['para'] = [$request->correo_jefe, 'rafael.alexromero@gmail.com', 'desarrollo2@singh.com.mx', 'uchiha_anemixd@hotmail.com'];
            $data['titulo'] = 'Confirmación incidencias';
            $data['cuerpo'] = $cuerpo;
            $data['btnTxt'] = 'Entrar';
            $data['btnUrl'] = 'http://prod.hrsystem.com.mx/';
            // enviarMail($data);
        
            Bitacora::insert([
                'usuario' => $request->correo_jefe,
                'descripcion' => $cuerpo,
                'estatus' => 0,
                'tipo' => 1,
                'referencia' => 1,
                'genero' => $request->correo,
                'evento' => 1
            ]);

            $respuesta = 1;
        
        }catch(\Exception $e){

            $respuesta = 2;
        }
        return response()->json(['respuesta' => $respuesta]);
    }

    protected function verificarTablaDatosFacturacion($ejercicio)
    {
        if (empty($ejercicio)) return false;

        $table_name = 'datos_facturacion' . $ejercicio;

        // set your dynamic fields (you can fetch this data from database this is just an example)
        $fields = [
            ['name' => 'id', 'type' => 'increments', 'nullable' => false],
            ['name' => 'id_periodo', 'type' => 'integer', 'nullable' => false],
            ['name' => 'nomina', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'beneficio_sindical', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'anticipo', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'vacaciones', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'pago_prima_vaca', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'comision_mismo_dia', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_pago_nomina', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'porcentaje_honorarios', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valores_honorarios', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'ejercicio', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'costos_patronales', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'detalle_subtotal', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'detalle_iva', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'detalle_total', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'prestaciones_extras', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'cuota_fija', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'exc_cf', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'presta_dinero', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'gastos_medi_pensionados', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'riesgo_trabajo', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'invalidez_y_vida', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'guarderias_y_pre_sociales', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'cuotas_imss_retiro', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'cuotas_imss_censatiaV', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'cred_vivienda', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'porcentaje_errogaciones', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_errogaciones', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'totalcostos_patronales', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'carga_social1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'carga_social1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'carga_social1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'carga_social1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'carga_social1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'cadena_emisoras', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'porcentajes_nomina', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'suministro_per1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'suministro_per1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'suministro_per1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'suministro_per1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'suministro_per1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'porcentaje_comisionV', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'concepto', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_concepto', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'subtotal_depo2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'iva_depo2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'total_depo2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_sobre_nomina1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_sobre_nomina1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_sobre_nomina1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_sobre_nomina1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_sobre_nomina1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_comision_variable1_1', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_comision_variable1_2', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_comision_variable1_3', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_comision_variable1_4', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'valor_comision_variable1_5', 'type' => 'string', 'size' => 25, 'nullable' => true],
            ['name' => 'fecha_creacion', 'type' => 'dateTime', 'nullable' => false],
        ];

        $table = new TableController;
        return $table->createTable(Session::get('base'), $table_name, $fields, false);
    }

    protected function hayPeriodoEnRangoFechas($nombre_periodo, $fecha_inicial_periodo, $fecha_final_periodo, $exceptID = '')
    {
        if (empty($nombre_periodo) || empty($fecha_final_periodo) || empty($fecha_inicial_periodo)) return true;
        if (Session::get('empresa')['sss'] == 1) return false;

        $p1 = periodosNomina::where('nombre_periodo', $nombre_periodo)
            ->where('estatus', periodosNomina::ESTATUS_DISPONIBLE)
            ->whereBetween('fecha_inicial_periodo', [$fecha_inicial_periodo, $fecha_final_periodo]);

        $p2 = periodosNomina::where('nombre_periodo', $nombre_periodo)
            ->where('estatus', periodosNomina::ESTATUS_DISPONIBLE)
            ->whereBetween('fecha_final_periodo', [$fecha_inicial_periodo, $fecha_final_periodo]);

        if ($exceptID > 0) {
            $p1->where('id', '!=', $exceptID);
            $p2->where('id', '!=', $exceptID);
        }

        $p1 = $p1->count();
        $p2 = $p2->count();

        return ($p1 > 0 || $p2 > 0) ? true : false;
    }

    public static function verificarTablaRutinasAdic($table_name)
    {
        cambiarBase(Session::get('base'));
        $conceptos = ConceptosNomina::select('id')->where('estatus', 1)->get();

        if (!Schema::connection('empresa')->hasTable($table_name))
            NominaController::createTableRutinas($table_name, $conceptos);
        else
            NominaController::verifyColsTableRutinas($table_name, $conceptos);

        return true;
    }

    public static function verifyColsTableRutinas($table_name, $conceptos){
        $cols = Schema::connection('empresa')->getColumnListing($table_name);

        foreach ($conceptos as $concepto) {
            if(!in_array('valor'.$concepto->id, $cols)){
                Schema::connection('empresa')->table($table_name, function (Blueprint $table) use ($concepto) {
                    $table->string('valor' . $concepto->id, 20)->nullable();
                });
                Schema::connection('empresa')->table($table_name, function (Blueprint $table) use ($concepto) {
                    $table->string('total' . $concepto->id, 20)->nullable();
                });
                Schema::connection('empresa')->table($table_name, function (Blueprint $table) use ($concepto) {
                    $table->string('excento' . $concepto->id, 20)->nullable();
                });
                Schema::connection('empresa')->table($table_name, function (Blueprint $table) use ($concepto) {
                    $table->string('gravado' . $concepto->id, 20)->nullable();
                });
            }
        }

        $cols_table_rutinas = ['infonavit', 'dias_imss', 'concepto_fac', 'total_percepcion_fiscal', 'total_percepcion_fiscal2', 'total_percepcion_sindical', 'total_deduccion_fiscal',
                'total_deduccion_fiscal2',  'total_deduccion_sindical', 'neto_fiscal',  'neto_sindical', 'incapacidades', 'total_gravado', 'sdo_faltas', 'sdo_incapacidades', 'cuota_fija',
                'exce_pa', 'exce_ob', 'pre_dine_obre', 'pre_dine_patro', 'gas_medi_patro', 'gas_medi_obre', 'riesgo_trabajo', 'inva_vida_patro', 'inva_vida_obre', 'guarde_presta',
                'sar_patron', 'infonavit_patro', 'censa_vejez_patron', 'censa_vejez_obre', 'beneficio_sindical', 'importe_total', 'subsidio', 'infonavit_patron', 'subsidio_al_empleo',
                'censa_vejez_obre_patronal', 'bono_prima', 'dias_laborados', 'dias_no_laborados', 'true_isr', 'isr_asimilados', 'estatus_confirma'];

        foreach ($cols_table_rutinas as $col_rutina) {
            if(!in_array($col_rutina, $cols))
                Schema::connection('empresa')->table($table_name, function (Blueprint $table) use ($col_rutina) {
                    $table->string($col_rutina, 20)->nullable();
                });
        }
    }

    public static function createTableRutinas($table_name, $conceptos){
        Schema::connection('empresa')->create($table_name, function (Blueprint $table) use ($conceptos) {
            $table->increments('id');
            $table->integer('id_periodo');
            $table->integer('id_empleado');
            $table->integer('estatus')->default(1);
            $table->string('infonavit', 20)->nullable();
            $table->string('dias_imss', 20)->nullable();
            $table->string('concepto_fac', 20)->nullable();
            $table->string('total_percepcion_fiscal', 20)->nullable();
            $table->string('total_percepcion_fiscal2', 20)->nullable();
            $table->string('total_percepcion_sindical', 20)->nullable();
            $table->string('total_deduccion_fiscal', 20)->nullable();
            $table->string('total_deduccion_fiscal2', 20)->nullable();
            $table->string('total_deduccion_sindical', 20)->nullable();
            $table->string('neto_fiscal', 20)->nullable();
            $table->string('neto_sindical', 20)->nullable();
            $table->string('incapacidades', 20)->nullable();
            $table->string('total_gravado', 20)->nullable();
            $table->string('sdo_faltas', 20)->nullable();
            $table->string('sdo_incapacidades', 20)->nullable();
            $table->integer('fnq_valor')->nullable()->default(0);
            $table->string('cuota_fija', 20)->nullable();
            $table->string('exce_pa', 20)->nullable();
            $table->string('exce_ob', 20)->nullable();
            $table->string('pre_dine_obre', 20)->nullable();
            $table->string('pre_dine_patro', 20)->nullable();
            $table->string('gas_medi_patro', 20)->nullable();
            $table->string('gas_medi_obre', 20)->nullable();
            $table->string('riesgo_trabajo', 20)->nullable();
            $table->string('inva_vida_patro', 20)->nullable();
            $table->string('inva_vida_obre', 20)->nullable();
            $table->string('guarde_presta', 20)->nullable();
            $table->string('sar_patron', 20)->nullable();
            $table->string('infonavit_patro', 20)->nullable();
            $table->string('censa_vejez_patron', 20)->nullable();
            $table->string('censa_vejez_obre', 20)->nullable();
            $table->string('beneficio_sindical', 20)->nullable()->default(0);
            $table->string('importe_total', 20)->nullable()->default(0);
            $table->string('subsidio', 20)->nullable()->default(0);
            $table->string('infonavit_patron', 20)->nullable();
            $table->string('subsidio_al_empleo', 20)->nullable();
            $table->string('censa_vejez_obre_patronal', 20)->nullable();
            $table->string('bono_prima', 20)->nullable();
            $table->string('dias_laborados', 20)->nullable();
            $table->string('dias_no_laborados', 20)->nullable();
            $table->integer('true_isr')->nullable()->default(0);
            $table->string('isr_asimilados', 20)->nullable();
            $table->string('estatus_confirma', 20)->nullable()->default(0);

            foreach ($conceptos as $concepto) {
                $table->string('valor' . $concepto->id, 20)->nullable();
                $table->string('total' . $concepto->id, 20)->nullable();
                $table->string('excento' . $concepto->id, 20)->nullable();
                $table->string('gravado' . $concepto->id, 20)->nullable();
            }
        });
    }

    protected function generarReporteNomina($idPeriodo, $departamentos)
    {
        cambiarBase(Session::get('base'));

        // $departamentos = $request->deptos;

        $periodo = PeriodosNomina::find($idPeriodo);
        
        $user = auth()->user();

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();

        
        if ($periodo->cadena_empleados != NULL) {
            $cadena = ' AND id in (' . $periodo->cadena_empleados . ')';
            $cadenarutina = ' AND ru.id in (' . $periodo->cadena_empleados . ')';
        } else {
            $cadena = ' and id_departamento in (' . implode(',', $departamentos) . ')';
            $cadenarutina = ' and em.id_departamento in (' . implode(',', $departamentos) . ')';
        }

        $ids_empleados = [];

        $queryempleados = "SELECT * from empleados where estatus=1 and tipo_de_nomina='$periodo->nombre_periodo' and id in (SELECT id_empleado from rutinas$periodo->ejercicio where id_periodo='$periodo->id' and fnq_valor=0) $cadena";
        $empleados1 = DB::connection('empresa')->select($queryempleados);
        foreach ($empleados1 as $empleado) {
            $ids_empleados[] = $empleado->id;
        }

        $queryempleadosbajas = "SELECT * from empleados where estatus in (2,20) and tipo_de_nomina='$periodo->nombre_periodo' and fecha_baja <> '0000-00-00' and fecha_baja >= '$periodo->fecha_inicial_periodo' and id in (SELECT id_empleado from rutinas$periodo->ejercicio where id_periodo='$periodo->id' and fnq_valor=0) $cadena";
        $empleados2 = DB::connection('empresa')->select($queryempleadosbajas);
        foreach ($empleados2 as $empleado) {
            $ids_empleados[] = $empleado->id;
        }

        $cadena_empleados = implode(',', $ids_empleados);

        if ($parametros_empresa->provision_aguinaldo == 1 && $periodo->aux_agui == 0) {

            $query = "SELECT sum(total_aguinaldo) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT id from empleados where estatus=1 and tipo_de_nomina='$periodo->nombre_periodo' and fecha_alta<='$periodo->fecha_final_perido' $cadena)";
            $valor_provision_aguinaldo = DB::connection('empresa')->select($query);
        } else {
            $valor_provision_aguinaldo = 0;
        }

        if ($parametros_empresa->provision_prima_vacacional == 1 && $periodo->aux_prima_vacacional == 0) {

            $query = "SELECT sum(total_prima_vacacional) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT id from empleados where estatus=0 and tipo_de_nomina='$periodo->nombre_periodo' and fecha_alta<='$periodo->fecha_final_perido' $cadena)";
            $valor_provision_prima_vacacional = DB::connection('empresa')->select($query);
        } else {
            $valor_provision_prima_vacacional = 0;
        }

        // Se sacan los conceptos de nomina
        $conceptos = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('nomina', 1)->get();
        $conceptos_validados = [];
        $columnas_tabla = obtenerColumnasTabla('rutinas' . $periodo->ejercicio);
        // Se verifica que las columnas existan
        foreach ($conceptos as $concepto) {
            if (in_array('valor' . $concepto->id, $columnas_tabla)) {
                $conceptos_validados[] = $concepto->id;
            }
        }

        $columnas1 = $columnas2 = $columnasSindical = $columnasDEDUCC = collect([]);

        if (strtolower($parametros_empresa->tipo_nomina) != 'solosindical') {
            $columnas1 = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 0)->whereIn('id', $conceptos_validados)->get();
            $columnas2 = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 1)->whereIn('id', $conceptos_validados)->get();
        }

        // Sindical
        if (strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical') {
            $columnasSindical = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('nomina', 1)->where('tipo', 0)->whereIn('id', $conceptos_validados)
                ->where(function ($query) {
                    $query->where('file_rool', '>=',  250)
                        ->orWhere('file_rool', '=', 0);
                })->get();
        }

        $columnaPVAC = DB::connection('empresa')->table('conceptos_nomina')->where('rutinas', 'PVAC')->whereIn('id', $conceptos_validados)->where('estatus', 1)->where(function ($query) {
            $query->where('file_rool', '<=',  249)
                ->orWhere('file_rool', '!=', 0);
        })->first();

        // Sindical Deducciones
        $columnasDEDUCC = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->whereIn('id', $conceptos_validados)->where('nomina', 1)->where(function ($query) {
            $query->where('file_rool', '>=',  250)
                ->orWhere('file_rool', 0);
        })->where('tipo', 1)->get();


        $columnas3 = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where(function ($query) {
            $query->where('rutinas', 'INFONAzz')
                ->orWhere('rutinas', 'FONACOTssss')
                ->orWhere('rutinas', 'PENSIONsss')
                ->orWhere('rutinas', 'Credito infonavitsssss');
        })->get();

        if ($columnas3->count() > 0) {
            $columnas3_ids = [];
            foreach ($columnas3 as $col) {
                $columnas3_ids[] = $col->id;
            }
            $columnas3_valores = DB::connection('empresa')->table('saldo_nomina')->where('id_periodo', $periodo->id)->where('saldo', '<', 0)->whereIn('id_concepto', $columnas_ids)->get()->keyBy('id_empleado');
        }

        $concepto_faltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->whereIn('id', $conceptos_validados)->first();
        $ID_conceptoFaltas = ($concepto_faltas != null) ? intval($concepto_faltas->id) : 0;
        

        

        /***** EMPLEADOS DEL PERIODO **********/
        $empleados = Empleado::with('categoria', 'departamento')
            ->whereIn('id', function ($query) use ($periodo, $ids_empleados) {
                $query->select('id_empleado')
                    ->from('rutinas' . $periodo->ejercicio)
                    ->where('fnq_valor', 0)
                    ->where('id_periodo', $periodo->id)
                    ->whereIn('id_empleado', $ids_empleados);
            })->get()->keyBy('id');

        /** VALORES DEL A TABLA RUTINAS DEL EMPLEADO */
        $empleados_rutinas = DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
            ->where('fnq_valor', 0)
            ->where('id_periodo', $periodo->id)
            ->whereIn('id_empleado', $ids_empleados)->get()->keyBy('id_empleado');

        foreach ($empleados as $empleado) {

            $empleado->rutinas = $empleados_rutinas[$empleado->id];
                if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($concepto_faltas);
                }
            if($concepto_faltas!=NULL){
                if(Session::get('usuarioPermisos')['id_usuario']==64){
                   // dd($concepto_faltas->id);
                }
                $valorFaltas = 'valor' . $concepto_faltas->id;
            $faltas = ($concepto_faltas->id > 0) ? $empleados_rutinas[$empleado->id]->$valorFaltas : 0;

            $totalFaltas = 'total' . $concepto_faltas->id;

            $empleado->faltas = $empleados_rutinas[$empleado->id]->$totalFaltas;

            }else{
            $faltas = 0;

            $totalFaltas = 0;

            $empleado->faltas = 0;
            }
            

            $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
            $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));
            $ano_final_periodo = date('Y', strtotime($periodo->fecha_final_periodo));

            if ($dia_final_periodo == '28' || $dia_final_periodo == '29' || $dia_final_periodo == '31') {
                $fecha_final_periodo = $ano_final_periodo . '-' . $mes_final_periodo . '-30';
            } else {
                $fecha_final_periodo = $periodo->fecha_final_periodo;
            }

            if ($mes_final_periodo == 2 && $dia_final_periodo > 15) {
                $fecha_final_periodo = date('Y-m-t', strtotime($periodo->fecha_inicial_periodo));
            }

            $fecha_final_periodo = Carbon::parse($fecha_final_periodo);
            $fecha_alta = Carbon::parse($empleado->fecha_alta);
            $dias_nom = $fecha_final_periodo->diffInDays($fecha_alta) + 1;

            $incapacidades = ($empleados_rutinas[$empleado->id]->incapacidades > 0) ? $empleados_rutinas[$empleado->id]->incapacidades : 0;

            if ($empleado->fecha_alta > $periodo->fecha_inicial_periodo) {
                $dias_pagados = $dias_nom - $incapacidades  - intval($faltas);
                // $dias_pagados01 = $dias_nom - $incapacidades  - $faltas;
            } else {
                // dd($periodo->dias_periodo, $incapacidades, $faltas);
                $dias_pagados = $periodo->dias_periodo - $incapacidades - intval($faltas);
                // $dias_pagados01 = $periodo->dias_periodo - $incapacidades - $faltas;
            }
            $empleado->dias_pagados = $dias_pagados;

            if ($columnas3->count() > 0) {

                if (isset($columnas3_valores[$empleado->id])) {
                    $monto = $columnas3_valores[$empleado->id]->valor_concepto;
                } else {
                    $col = 'total' . $columnas3->id_concepto;
                    $monto = $empleado->rutinas->$col;
                }
                DB::connection('empresa')->table('temporasuma')->insert([
                    'id_empleado' => $empleado->id,
                    'monto' => $monto
                ]);

                $suma_deduccion = DB::connection('empresa')->table('temporasuma')
                    ->where('id_empleado', $empleado->id)
                    ->sum('monto')->get();
            } else {
                $suma_deduccion = 0;
            }

            $empleado->total_deduccion_sindical = $suma_deduccion + $empleado->rutinas->total_deduccion_sindical;
            //dd($empleado->rutinas->total_percepcion_sindical,$empleado->total_deduccion_sindical,"");
            $empleado->total_percepcion_sindical = floatval($empleado->rutinas->total_percepcion_sindical) - $empleado->total_deduccion_sindical;

            // $TotalBeneficio=$importeBeneficio - $empleado->total_deduccion_sindical;

            if (strtolower($parametros_empresa->tipo_nomina) == 'solosindical') {
                $empleado->total_a_pagar = $empleado->total_percepcion_sindical;
            } else {
                $empleado->total_a_pagar = round($empleado->rutinas->neto_fiscal, 2) + $empleado->total_percepcion_sindical;
            }
        }

        if ($columnas3->count() > 0) {
            foreach ($columnas3_valores as $valor) {
                $empleado[$valor->id_empleado]->columnas3 = $valor;
            }
        }
        /***************************** FIN PRIMERA TABLA *********************** */


        $porcentaje_honorarios = $parametros_empresa->porcentaje_honorarios;
        $provision_porcentaje = $parametros_empresa->provision_porcentaje;
        $concepto_facturacion = $parametros_empresa->concepto_facturacion;
        $provision_obrero = $parametros_empresa->provision_obrero;
        $anticipo = ($parametros_empresa->anticipo != "" && $parametros_empresa->anticipo != null) ? $parametros_empresa->anticipo : 0;
        $comision_mismo_dia = ($parametros_empresa->comision_mismo_dia != "" && $parametros_empresa->comision_mismo_dia != null) ? $parametros_empresa->comision_mismo_dia : 0;

        $valor_honorarios = $porcentaje_honorarios / 100;

        $query_sumas = "SELECT sum(neto_fiscal) as neto_fiscal, sum(total_percepcion_fiscal) as total_percepcion_fiscal, sum(total_percepcion_sindical) as total_percepcion_sindical, sum(subsidio) as subsidio, sum(beneficio_sindical) as beneficio_sindical, sum(pre_dine_patro) as pre_dine_patro, sum(pre_dine_obre) as pre_dine_obre, sum(gas_medi_patro) as gas_medi_patro, sum(gas_medi_obre) as gas_medi_obre, sum(riesgo_trabajo) as riesgo_trabajo, sum(inva_vida_patro) as inva_vida_patro, sum(inva_vida_obre) as inva_vida_obre, sum(guarde_presta) as guarde_presta, sum(censa_vejez_patron) as censa_vejez_patron, sum(censa_vejez_obre) as censa_vejez_obre, sum(infonavit_patro) as infonavit_patro, sum(sar_patron) as sar_patron from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id' and em.tipo_de_nomina='$periodo->nombre_periodo' and em.id in ($cadena_empleados)";
        // dd($cadena_empleados);
        $totales = DB::connection('empresa')->select($query_sumas);
        $totales = $totales[0];

        $neto_fiscal_real = $totales->total_percepcion_fiscal + ($totales->subsidio * -1);

        if ($columnaPVAC) {
            $query_valor_prima = "SELECT sum(total$columnaPVAC->id) as result from rutinas$periodo->ejercicio where fnq_valor=0 and id_periodo='$periodo->id' and id_empleado in (select id from empleados where tipo_de_nomina = '$periodo->nombre_periodo' and id in ($cadena_empleados))";

            $total_valor_prima = DB::connection('empresa')->select($query_valor_prima);
            $pago_prima_vacacional = $total_valor_prima[0]->result;
        } else {
            $pago_prima_vacacional = 0.0;
        }

        $vacaciones = 0.0;
        $total_percepcion_sindical = ($totales->total_percepcion_sindical != null && $totales->total_percepcion_sindical != "") ? $totales->total_percepcion_sindical : 0;

        $total_pagar_nomina = $neto_fiscal_real + $totales->total_percepcion_sindical + $anticipo + $vacaciones + $comision_mismo_dia + $valor_provision_aguinaldo + $valor_provision_prima_vacacional;

        $pago_honorarios = $total_pagar_nomina * $valor_honorarios;

        $datos_facturacion = DB::connection('empresa')->table('datos_facturacion' . $periodo->ejercicio)->where('id_periodo', $periodo->id)->get();
        $cuota_fija = round($datos_facturacion[0]->cuota_fija, 2);
        $exc_cf = round($datos_facturacion[0]->exc_cf, 2);
        $pre_dinero_patronal = round($totales->pre_dine_patro, 2) * $provision_porcentaje;
        $pre_dine_obrero = round($totales->pre_dine_obre, 2) * $provision_porcentaje;

        $pre_patro_adicional = ($provision_obrero > 0) ? $pre_dine_obrero * ($provision_obrero / 100) : 0;
        $pre_dinero_pa = $pre_dinero_patronal + $pre_patro_adicional;


        $query_censa_vejez_obre_patronal = "SELECT sum(censa_vejez_obre_patronal) as suma  from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where em.estatus=1 and finiquitado = 0 and ru.fnq_valor = 0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' $cadenarutina";
        $censa_vejez_obre_patronal_ = DB::connection('empresa')->select($query_censa_vejez_obre_patronal);
        $censa_vejez_obre_patronal = round($censa_vejez_obre_patronal_[0]->suma, 2);

        $gas_medi_patron = round($totales->gas_medi_patro, 2) * $provision_porcentaje;
        $gas_medi_obre = round($totales->gas_medi_obre, 2) * $provision_porcentaje;

        $gas_medi_patron_adicional = ($provision_obrero > 0) ? $gas_medi_obre * ($provision_obrero / 100) : 0;
        $gas_medi_patron = $gas_medi_patron + $gas_medi_patron_adicional;


        $riesgo_trabajo = round($totales->riesgo_trabajo, 2) * $provision_porcentaje;
        $inva_vida_patro = round($totales->inva_vida_patro, 2) * $provision_porcentaje;
        $inva_vida_obre = round($totales->inva_vida_obre, 2) * $provision_porcentaje;

        $inva_vida_patro_adicional = ($provision_obrero > 0) ? $inva_vida_obre * ($provision_obrero / 100) : 0;
        $inva_vida_patro = $inva_vida_patro + $inva_vida_patro_adicional;

        $guarde_presta = round($totales->guarde_presta, 2) * $provision_porcentaje;
        $censa_vejez_patron = round($totales->censa_vejez_patron, 2) * $provision_porcentaje;
        $censa_vejez_obre = round($totales->censa_vejez_obre, 2) * $provision_porcentaje;

        $censa_vejez_patron = $censa_vejez_patron + $censa_vejez_obre;

        $infonavit_patro = round($totales->infonavit_patro, 2) * $provision_porcentaje;
        $sar_patron = round($totales->sar_patron, 2) * $provision_porcentaje;

        $comision_variable = $parametros_empresa->comision_variable;
        $porcentaje_nomina = $parametros_empresa->porcentaje_nomina;

        $porcentaje_nom = $porcentaje_nomina / 100;
        $errogacion = $totales->total_percepcion_fiscal * $porcentaje_nom;

        $valor_prestacion_extra = $parametros_empresa->valor_prestacion_extra;
        $iva = $parametros_empresa->iva;

        $queryNumEmple = "SELECT * from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and pre.estatus=1 and em.id in ($cadena_empleados)";
        $empleados_2 = DB::connection('empresa')->select($queryNumEmple);
        //        $num_empleados_2 = (isset($empleados_2[0])) ? $empleados_2[0]->count() : 0;
        $num_empleados_2 = (isset($empleados_2)) ? count($empleados_2) : 0;

        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valor_seguro_GM, sum(pre.valor_plan_espejo) as valor_plan_espejo from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and em.id in ($cadena_empleados)";
        $seguros = DB::connection('empresa')->select($queryvalorseguroGM);
        $valor_seguro_GM = $seguros[0]->valor_seguro_GM;
        $valor_plan_espejo = $seguros[0]->valor_plan_espejo;

        $prestaciones_extras = ($valor_prestacion_extra * $num_empleados_2) + $valor_seguro_GM + $valor_plan_espejo;

        $total = $prestaciones_extras + $cuota_fija + $exc_cf + $pre_dinero_pa + $gas_medi_patron + $riesgo_trabajo + $inva_vida_patro + $guarde_presta + $sar_patron + $censa_vejez_patron + $infonavit_patro + $errogacion;

        // dd($cuota_fija,$exc_cf,$pre_dinero_pa,$gas_medi_patron,$riesgo_trabajo,$inva_vida_patro,$guarde_presta,$sar_patron,$censa_vejez_patron,$infonavit_patro);
        $carga_social = $cuota_fija + $exc_cf + $pre_dinero_pa + $gas_medi_patron + $riesgo_trabajo + $inva_vida_patro + $guarde_presta + $sar_patron + $censa_vejez_patron + $infonavit_patro;
        $subtotal = $total_pagar_nomina + $pago_honorarios + $total;
        $iva = $subtotal * $parametros_empresa->iva;
        $total_mayor = $subtotal + $iva;
        $comision = $neto_fiscal_real + $carga_social + $errogacion;
        $valor_comision = $comision * ($comision_variable / 100);
        $subtotal02 = $neto_fiscal_real + $carga_social + $errogacion + $valor_comision;
        $iva02 = $subtotal02 * $parametros_empresa->iva;
        $total_mayor02 = $subtotal02 + $iva02;
        $asesoria_contable = $subtotal - $subtotal02;
        $iva03 = $asesoria_contable * $parametros_empresa->iva;
        $total_mayor03 = $iva03 + $asesoria_contable;

        $totales = [
            'neto_fiscal_real' => $neto_fiscal_real,
            'total_percepcion_sindical' => $totales->total_percepcion_sindical,
            'valor_provision_aguinaldo' => $valor_provision_aguinaldo,
            'valor_provision_prima_vacacional' => $valor_provision_prima_vacacional,
            'total_pagar_nomina' => $total_pagar_nomina,
            'pago_honorarios' => $pago_honorarios,
            'total' => $total,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total_mayor' => $total_mayor,
            'prestaciones_extras' => $prestaciones_extras,
            'cuota_fija' => $cuota_fija,
            'exc_cf' => $exc_cf,
            'pre_dinero_pa' => $pre_dinero_pa,
            'gas_medi_patron' => $gas_medi_patron,
            'riesgo_trabajo' => $riesgo_trabajo,
            'inva_vida_patro' => $inva_vida_patro,
            'guarde_presta' => $guarde_presta,
            'sar_patron' => $sar_patron,
            'censa_vejez_patron' => $censa_vejez_patron,
            'infonavit_patro' => $infonavit_patro,
            'porcentaje_nomina' => $porcentaje_nomina,
            'errogacion' => $errogacion,
            'total' => $total,

            'porcentaje_nomina' => $porcentaje_nomina,
            'carga_social' => $carga_social,
            'comision_variable' => $comision_variable,
            'valor_comision' => $valor_comision,
            'subtotal02' => $subtotal02,
            'iva02' => $iva02,
            'total_mayor02' => $total_mayor02,

            'concepto_facturacion' => $concepto_facturacion,
            'asesoria_contable' => $asesoria_contable,
            'iva03' => $iva03,
            'total_mayor03' => $total_mayor03,
        ];

        /***************************** FIN 2da y 3era TABLA *********************** */
        //dd($parametros_empresa);
        $emisoras = [];
        if (strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical') {

            $queryEmisoras = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id in ($cadena_empleados) group by ememi.razon_social";

            $emisoras = DB::connection('empresa')->select($queryEmisoras);
           
            if (count($emisoras) > 1) {
                $i = 0;

                foreach ($emisoras as $emisora) {
                    $i++;

                    $queryPercepFiscalEmisoras = "SELECT sum(total_percepcion_fiscal) as suma from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_perido' and em.id in ($emisora->cadena_empleados)";
                    $percep_fiscal_emisoras = DB::connection('empresa')->select($queryPercepFiscalEmisoras);
                    
                    
                    
                    //$percep_fiscal_emisoras = $percep_fiscal_emisoras[0]['suma'];
                    $percep_fiscal_emisoras = ($percep_fiscal_emisoras[0]->suma != null)?$percep_fiscal_emisoras[0]->suma:0 ;
                    
                    $errogacion_emisora = $percep_fiscal_emisoras * $porcentaje_nom;

                    $queryvaloresFacturacion = "SELECT suministro_per1_$i as suministro_per, carga_social1_$i as carga_social, porcentajes_nomina, porcentaje_comisionV, subtotal_depo1_$i as subtotal_depo, iva_depo1_$i as iva_depo, total_depo1_$i as total_depo, valor_sobre_nomina1_$i as valor_sobre_nomina , valor_comision_variable1_$i as valor_comision_variable from datos_facturacion$periodo->ejercicio where id_periodo = '$periodo->id'";
                    $valores_facturacion = DB::connection('empresa')->select($queryvaloresFacturacion);
                    /*
                    $totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] = $valores_facturacion[0]['suministro_per'];
                    $totales[$emisora->id_empresa_emisora]['carga_social'] = $valores_facturacion[0]['carga_social'];
                    $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] = $valores_facturacion[0]['porcentajes_nomina'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision'] = $valores_facturacion[0]['porcentaje_comisionV'];
                    $totales[$emisora->id_empresa_emisora]['subtotal02'] = $valores_facturacion[0]['subtotal_depo'];
                    $totales[$emisora->id_empresa_emisora]['iva02'] = $valores_facturacion[0]['iva_depo'];
                    $totales[$emisora->id_empresa_emisora]['total_mayor02'] = $valores_facturacion[0]['total_depo'];
                    $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] = $valores_facturacion[0]['valor_comision_variable'];
                    $totales[$emisora->id_empresa_emisora]['errogacion_emisora'] = $errogacion_emisora;
                    */
                    
                    $totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] = $valores_facturacion[0]->suministro_per;
                    $totales[$emisora->id_empresa_emisora]['carga_social'] = $valores_facturacion[0]->carga_social;
                    $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] = $valores_facturacion[0]->porcentajes_nomina;
                    $totales[$emisora->id_empresa_emisora]['valor_comision'] = $valores_facturacion[0]->porcentaje_comisionV;
                    $totales[$emisora->id_empresa_emisora]['subtotal02'] = $valores_facturacion[0]->subtotal_depo;
                    $totales[$emisora->id_empresa_emisora]['iva02'] = $valores_facturacion[0]->iva_depo;
                    $totales[$emisora->id_empresa_emisora]['total_mayor02'] = $valores_facturacion[0]->total_depo;
                    $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] = $valores_facturacion[0]->valor_comision_variable;
                    $totales[$emisora->id_empresa_emisora]['errogacion_emisora'] = $errogacion_emisora;
                }
            }
        }

        return compact('periodo', 'departamentos', 'columnas1', 'columnas2', 'columnasSindical', 'columnaPVAC', 'columnasDEDUCC', 'columnas3', 'empleados', 'totales', 'parametros_empresa', 'emisoras');
    }

    protected function generarReporteNominaDetalle($idPeriodo, $departamentos)
    {
        cambiarBase(Session::get('base'));

        $periodo = periodosNomina::find($idPeriodo);

        $parametros_empresa = DB::connection('empresa')
            ->table('parametros')
            ->first();

        if ($periodo->cadena_empleados != NULL) {
            $cadena = ' AND id in (' . $periodo->cadena_empleados . ')';
            $cadenarutina = ' AND ru.id in (' . $periodo->cadena_empleados . ')';
        } else {
            $cadena = ' and id_departamento in (' . implode(',', $departamentos) . ')';
            $cadenarutina = ' and em.id_departamento in (' . implode(',', $departamentos) . ')';
        }
        
        $ids_empleados = [];

        $queryempleados = "SELECT * from empleados where estatus=1 and tipo_de_nomina='$periodo->nombre_periodo' and id in (SELECT id_empleado from rutinas$periodo->ejercicio where id_periodo='$periodo->id' and fnq_valor=0) $cadena";
        $empleados1 = DB::connection('empresa')->select($queryempleados);
        foreach ($empleados1 as $empleado) {
            $ids_empleados[] = $empleado->id;
        }

        $queryempleadosbajas = "SELECT * from empleados where estatus in (2,20) and tipo_de_nomina='$periodo->nombre_periodo' and fecha_baja <> '0000-00-00' and fecha_baja >= '$periodo->fecha_inicial_periodo' and id in (SELECT id_empleado from rutinas$periodo->ejercicio where id_periodo='$periodo->id' and fnq_valor=0) $cadena";
        $empleados2 = DB::connection('empresa')->select($queryempleadosbajas);
        foreach ($empleados2 as $empleado) {
            $ids_empleados[] = $empleado->id;
        }
        
    
        $cadena_empleados = implode(',', $ids_empleados);

        
        if ($parametros_empresa->provision_aguinaldo == 1 && $periodo->aux_agui == 0) {

            $query = "SELECT sum(total_aguinaldo) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT id from empleados where estatus=1 and tipo_de_nomina='$periodo->nombre_periodo' and fecha_alta<='$periodo->fecha_final_perido' $cadena)";
            $valor_provision_aguinaldo = DB::connection('empresa')->select($query);
        } else {
            $valor_provision_aguinaldo = 0;
        }

        if ($parametros_empresa->provision_prima_vacacional == 1 && $periodo->aux_prima_vacacional == 0) {

            $query = "SELECT sum(total_prima_vacacional) as result from provisiones_facturacion where id_periodo='$periodo->id' AND ejercicio='$periodo->ejercicio' and id_empleado in (SELECT id from empleados where estatus=0 and tipo_de_nomina='$periodo->nombre_periodo' and fecha_alta<='$periodo->fecha_final_perido' $cadena)";
            $valor_provision_prima_vacacional = DB::connection('empresa')->select($query);
        } else {
            $valor_provision_prima_vacacional = 0;
        }
        
        // Se sacan los conceptos de nomina
        $conceptos = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('nomina', 1)->get();
        $conceptos_validados = [];
        $columnas_tabla = obtenerColumnasTabla('rutinas' . $periodo->ejercicio);
        // Se verifica que las columnas existan
        foreach ($conceptos as $concepto) {
            if (in_array('valor' . $concepto->id, $columnas_tabla)) {
                $conceptos_validados[] = $concepto->id;
            }
        }

        $columnas1 = $columnas2 = $columnasSindical = $columnasDEDUCC = collect([]);

        if (strtolower($parametros_empresa->tipo_nomina) != 'solosindical') {
            $columnas1 = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 0)->whereIn('id', $conceptos_validados)->get();

            $columnas2 = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('file_rool', '!=', 0)->where('file_rool', '<=',  249)->where('nomina', 1)->where('tipo', 1)->whereIn('id', $conceptos_validados)->get();
        }
        /*
        if(Auth::user()->email == "desarrollo@singh.com.mx"){
            dd('hasta aqui todo ok 1');
        }
        */
        $idConcepto_faltas_s = 0;
        // Sindical
        if (strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical') {

            $faltas_s = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS_S')->where('estatus', 1)->where('tipo', 3)->get();
            $idConcepto_faltas_s = ($faltas_s->count() > 0) ? $faltas_s[0]->id : 0;

            $columnasSindical = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->where('nomina', 1)->where('tipo', 0)->whereIn('id', $conceptos_validados)
                ->where(function ($query) {
                    $query->where('file_rool', '>=',  250)
                        ->orWhere('file_rool', '=', 0);
                })->get();
        }

        $columnaPVAC = DB::connection('empresa')->table('conceptos_nomina')->where('rutinas', 'PVAC')->where('estatus', 1)->first();

        // Sindical Deducciones
        $columnasDEDUCC = DB::connection('empresa')->table('conceptos_nomina')->where('estatus', 1)->whereIn('id', $conceptos_validados)->where('nomina', 1)->where(function ($query) {
            $query->where('file_rool', '>=',  250)
                ->orWhere('file_rool', 0);
        })->where('tipo', 1)->get();

        $ID_conceptoFaltas = DB::connection('empresa')->table('conceptos_nomina')->select('id')->where('nombre_concepto', 'FALTAS')->where('estatus', 1)->whereIn('id', $conceptos_validados)->first();

        /***** EMPLEADOS DEL PERIODO **********/
        $empleados = Empleado::with('categoria', 'departamento', 'puesto', 'sede')
            ->whereIn('id', function ($query) use ($periodo, $ids_empleados) {
                $query->select('id_empleado')
                    ->from('rutinas' . $periodo->ejercicio)
                    ->where('fnq_valor', 0)
                    ->where('id_periodo', $periodo->id)
                    ->whereIn('id_empleado', $ids_empleados);
            })->get()->keyBy('id');

        /** VALORES DEL A TABLA RUTINAS DEL EMPLEADO */
        $empleados_rutinas = DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
            ->where('fnq_valor', 0)
            ->where('id_periodo', $periodo->id)
            ->whereIn('id_empleado', $ids_empleados)->get()->keyBy('id_empleado');


        // AGUINALDO Y PRIVA VACACIONAL
        $provisiones_facturacion = DB::connection('empresa')->table('provisiones_facturacion')
            ->where('ejercicio', $periodo->ejercicio)
            ->where('id_periodo', $periodo->id)
            ->whereIn('id_empleado', $ids_empleados)->get()->keyBy('id_empleado');

        // PRESTACIONES EXTRAS
        $prestaciones_extras_empleados = DB::connection('empresa')->table('prestaciones_extras')->whereIn('id_empleado', $ids_empleados)->get()->keyBy('id_empleado');

        foreach ($empleados as $empleado) {

            $empleado->rutinas = $empleados_rutinas[$empleado->id];

            $valorFaltas = 'valor' . $ID_conceptoFaltas->id;
            $faltas = ($ID_conceptoFaltas->id > 0) ? $empleados_rutinas[$empleado->id]->$valorFaltas : 0;

            $totalFaltas = 'total' . $ID_conceptoFaltas->id;
            $empleado->faltas = $empleados_rutinas[$empleado->id]->$totalFaltas;

            if ($idConcepto_faltas_s) {
                $valorFaltas_s = 'valor' . $idConcepto_faltas_s;
                $empleado->faltas_s = $empleados_rutinas[$empleado->id]->$valorFaltas_s;
            }

            $dia_final_periodo = date('d', strtotime($periodo->fecha_final_periodo));
            $mes_final_periodo = date('m', strtotime($periodo->fecha_final_periodo));
            $ano_final_periodo = date('Y', strtotime($periodo->fecha_final_periodo));

            if ($dia_final_periodo == '28' || $dia_final_periodo == '29' || $dia_final_periodo == '31') {
                $fecha_final_periodo = $ano_final_periodo . '-' . $mes_final_periodo . '-30';
            } else {
                $fecha_final_periodo = $periodo->fecha_final_periodo;
            }

            if ($mes_final_periodo == 2 && $dia_final_periodo > 15) {
                $fecha_final_periodo = date('Y-m-t', strtotime($periodo->fecha_inicial_periodo));
            }

            $fecha_final_periodo = Carbon::parse($fecha_final_periodo);
            $fecha_alta = Carbon::parse($empleado->fecha_alta);
            $dias_nom = $fecha_final_periodo->diffInDays($fecha_alta) + 1;

            $incapacidades = ($empleados_rutinas[$empleado->id]->incapacidades > 0) ? $empleados_rutinas[$empleado->id]->incapacidades : 0;

            if ($empleado->fecha_alta > $periodo->fecha_inicial_periodo) {
                $dias_pagados = $dias_nom - $incapacidades  - intval($faltas);
            } else {
                $dias_pagados = $periodo->dias_periodo - $incapacidades - intval($faltas);
            }
            $empleado->dias_pagados = $dias_pagados;


            $empleado->total_deduccion_sindical = $empleado->rutinas->total_deduccion_sindical;
            $empleado->total_percepcion_sindical = $empleado->rutinas->total_percepcion_sindical - $empleado->total_deduccion_sindical;

            if (isset($provisiones_facturacion[$empleado->id]))
                $empleado->provisiones_facturacion = $provisiones_facturacion[$empleado->id];
            else
                $empleado->provisiones_facturacion = null;

            $provision_porcentaje = $parametros_empresa->provision_porcentaje;
            $provision_obrero = $parametros_empresa->provision_obrero;

            // IMSS
            $total_imss = round($empleado->rutinas->cuota_fija, 2) + round($empleado->rutinas->exce_pa, 2) + round($empleado->rutinas->pre_dine_patro, 2) + round($empleado->rutinas->gas_medi_patro, 2) + round($empleado->rutinas->riesgo_trabajo, 2) + round($empleado->rutinas->guarde_presta, 2) + round($empleado->rutinas->inva_vida_patro, 2);
            $empleado->total_imss_con_provision = $total_imss * $provision_porcentaje;

            if ($provision_obrero > 0) {
                $IndPerprovisionObrero = $provision_obrero / 100;
                $obreImss = (round($empleado->rutinas->exce_ob, 2) + round($empleado->rutinas->pre_dine_obre, 2) + round($empleado->rutinas->gas_medi_obre, 2) + round($empleado->rutinas->inva_vida_obre, 2)) * $IndPerprovisionObrero;
                $total_imss = $total_imss + $obreImss;

                $empleado->total_imss_con_provision = $total_imss * $provision_porcentaje;
            }

            // RCV PATRONAL
            $empleado->RCV_total_con_provision = (round($empleado->rutinas->sar_patron, 2) * $provision_porcentaje) + (round($empleado->rutinas->censa_vejez_patron, 2) * $provision_porcentaje) + (round($empleado->rutinas->censa_vejez_obre, 2) * $provision_porcentaje);

            // INFONAVIT
            $empleado->infonavit_total_con_provision = (round($empleado->rutinas->infonavit_patro, 2) * $provision_porcentaje);

            // Impuesto Sobre Nomina(3 %)
            $porcen = $parametros_empresa->porcentaje_nomina / 100;
            $empleado->isn_por_empleado = round($empleado->rutinas->total_percepcion_fiscal, 2) * $porcen;


            // Prestaciones Extras
            $empleado->costo_prestacion = 0;
            if (isset($prestaciones_extras_empleados[$empleado->id])) {

                $estatus_pe = $prestaciones_extras_empleados[$empleado->id]->estatus;
                $valor_seguro_GM = $prestaciones_extras_empleados[$empleado->id]->valor_seguro_GM;
                $valor_plan_espejo = $prestaciones_extras_empleados[$empleado->id]->valor_plan_espejo;
                
                if($valor_seguro_GM == ""){ $valor_seguro_GM = 0; }else{  $valor_seguro_GM =  $valor_seguro_GM;}
                if($valor_plan_espejo == ""){ $valor_plan_espejo = 0;}else{ $valor_plan_espejo =$valor_plan_espejo;}
                
                if ($estatus_pe == 1) {
                    $empleado->costo_prestacion = $parametros_empresa->valor_prestacion_extra + $valor_seguro_GM + $valor_plan_espejo;
                } else {
                    $empleado->costo_prestacion = $valor_seguro_GM + $valor_plan_espejo;
                }
            }


            // COMISION
            $valor_provision_agui_empleado = 0;
            $valor_provision_primvaca_empleado = 0;
            if (isset($provisiones_facturacion[$empleado->id])) {
                $valor_provision_agui_empleado = $empleado->provisiones_facturacion->total_aguinaldo;
                $valor_provision_primvaca_empleado = $empleado->provisiones_facturacion->total_prima_vacacional;
            }
            $subs = ($empleado->rutinas->subsidio < 0) ? $empleado->rutinas->subsidio * -1 : $empleado->rutinas->subsidio;
            $sumapercepComple = round($empleado->rutinas->total_percepcion_fiscal, 2) + $subs + round($empleado->rutinas->total_percepcion_sindical, 2) + $valor_provision_agui_empleado + $valor_provision_primvaca_empleado;
            $empleado->costo_comision = $sumapercepComple * ($parametros_empresa->porcentaje_honorarios / 100);

            // Subtotal
            if ($provision_obrero > 0) {

                $IndiExceOb = (round($empleado->rutinas->exce_ob, 2) * $provision_porcentaje) * ($provision_obrero / 100);
                $IndiPreDineObre = (round($empleado->rutinas->pre_dine_obre, 2) * $provision_porcentaje) * ($provision_obrero / 100);
                $IndiGasMediObre = (round($empleado->rutinas->gas_medi_obre, 2) * $provision_porcentaje) * ($provision_obrero / 100);
                $IndiInvaVidaObre = (round($empleado->rutinas->inva_vida_obre, 2) * $provision_porcentaje) * ($provision_obrero / 100);
                $IndiCuotaFija = round($empleado->rutinas->cuota_fija, 2) * $provision_porcentaje;
                $IndiExcePa = round($empleado->rutinas->exce_pa, 2) * $provision_porcentaje;
                $IndiPreDinePatro = round($empleado->rutinas->pre_dine_patro, 2) * $provision_porcentaje;
                $IndiGasMediPatro = round($empleado->rutinas->gas_medi_patro, 2) * $provision_porcentaje;
                $IndiRiesgoTrabajo = round($empleado->rutinas->riesgo_trabajo, 2) * $provision_porcentaje;
                $IndiGuardePresta = round($empleado->rutinas->guarde_presta, 2) * $provision_porcentaje;
                $IndiInvaVidaPatro = round($empleado->rutinas->inva_vida_patro, 2) * $provision_porcentaje;
                $IndiSarPatron = round($empleado->rutinas->sar_patron, 2) * $provision_porcentaje;
                $IndiCensaVejezPatro = round($empleado->rutinas->censa_vejez_patron, 2) * $provision_porcentaje;
                $IndiInfonavitPatro = round($empleado->rutinas->infonavit_patro, 2) * $provision_porcentaje;
                $IndiCensaVejezObre = round($empleado->rutinas->censa_vejez_obre, 2) * $provision_porcentaje;

                $totalcuotasIndivi = $IndiExceOb + $IndiPreDineObre + $IndiGasMediObre + $IndiInvaVidaObre + $IndiCuotaFija + $IndiExcePa + $IndiPreDinePatro + $IndiGasMediPatro + $IndiRiesgoTrabajo + $IndiGuardePresta + $IndiInvaVidaPatro + $IndiSarPatron + $IndiCensaVejezPatro + $IndiInfonavitPatro + $IndiCensaVejezObre;
            } else {

                $totalcuotasIndivi = ((round($empleado->rutinas->cuota_fija, 2) + round($empleado->rutinas->exce_pa, 2) + round($empleado->rutinas->pre_dine_patro, 2) + round($empleado->rutinas->gas_medi_patro, 2) + round($empleado->rutinas->riesgo_trabajo, 2) + round($empleado->rutinas->guarde_presta, 2) + round($empleado->rutinas->inva_vida_patro, 2) + round($empleado->rutinas->sar_patron, 2) + round($empleado->rutinas->censa_vejez_patron, 2) + round($empleado->rutinas->infonavit_patro, 2) + round($empleado->rutinas->censa_vejez_obre, 2)) * $provision_porcentaje);
            }
            $totalImssIndividual = $empleado->costo_prestacion + $totalcuotasIndivi + $empleado->isn_por_empleado;
            $empleado->subtotal_por_empleado = $sumapercepComple + $empleado->costo_comision + $totalImssIndividual;

            $empleado->iva_por_empleado = $empleado->subtotal_por_empleado * $parametros_empresa->iva;
            $empleado->costo_por_empleado = $empleado->subtotal_por_empleado + $empleado->iva_por_empleado;

            // Comision 18%
            $cargasocialcomision = $empleado->total_imss_con_provision + $empleado->RCV_total_con_provision + $empleado->infonavit_total_con_provision;
            $empleado->comision_por_empleado = (round($empleado->rutinas->total_percepcion_fiscal, 2) - round($empleado->rutinas->subsidio, 2) + $cargasocialcomision + $empleado->isn_por_empleado) * ($parametros_empresa->comision_variable / 100);

            // SUB TOTAL FACT.01
            $empleado->subtotal_adic_por_empleado = round($empleado->rutinas->total_percepcion_fiscal, 2) - round($empleado->rutinas->subsidio, 2) + $cargasocialcomision + $empleado->isn_por_empleado + $empleado->comision_por_empleado;

            // IVA 16%
            $empleado->iva_adic_por_empleado = round($empleado->subtotal_adic_por_empleado * $parametros_empresa->iva, 2);

            // TOTAL FACT 01
            $empleado->total_fact01 = $empleado->subtotal_adic_por_empleado + $empleado->iva_adic_por_empleado;

            // COMISION 2.5 %
            $empleado->comision_sindical_por_empleado = (round($empleado->rutinas->total_percepcion_fiscal, 2) - round($empleado->rutinas->subsidio, 2) + round($empleado->rutinas->total_percepcion_sindical, 2)) * ($parametros_empresa->porcentaje_honorarios / 100);

            // GRan total
            $empleado->gran_total_por_empleado = round($empleado->rutinas->total_percepcion_fiscal, 2) - round($empleado->rutinas->subsidio, 2) + round($empleado->rutinas->total_percepcion_sindical, 2) + $cargasocialcomision + $empleado->isn_por_empleado + $empleado->costo_prestacion + $empleado->comision_sindical_por_empleado;

            //  IMPORTE A DEPOSITAR BENEFICIO SINDICAL
            $empleado->deposito_benefi_por_empleado = $empleado->gran_total_por_empleado - $empleado->subtotal_adic_por_empleado;

            // 16 %IVA
            $empleado->iva_deposito_benefi_por_empleado = $empleado->deposito_benefi_por_empleado * $parametros_empresa->iva;

            // TOTAL FAC. 02
            $empleado->total_fact02 = $empleado->iva_deposito_benefi_por_empleado + $empleado->deposito_benefi_por_empleado;
        }


        /***************************** FIN PRIMERA TABLA *********************** */


        $porcentaje_honorarios = $parametros_empresa->porcentaje_honorarios;
        $provision_porcentaje = $parametros_empresa->provision_porcentaje;
        $concepto_facturacion = $parametros_empresa->concepto_facturacion;
        $provision_obrero = $parametros_empresa->provision_obrero;
        $anticipo = $parametros_empresa->anticipo;
        $comision_mismo_dia = $parametros_empresa->comision_mismo_dia;

        $valor_honorarios = $porcentaje_honorarios / 100;

        $query_sumas = "SELECT sum(neto_fiscal) as neto_fiscal, sum(total_percepcion_fiscal) as total_percepcion_fiscal, sum(total_percepcion_sindical) as total_percepcion_sindical, sum(subsidio) as subsidio, sum(beneficio_sindical) as beneficio_sindical, sum(pre_dine_patro) as pre_dine_patro, sum(pre_dine_obre) as pre_dine_obre, sum(gas_medi_patro) as gas_medi_patro, sum(gas_medi_obre) as gas_medi_obre, sum(riesgo_trabajo) as riesgo_trabajo, sum(inva_vida_patro) as inva_vida_patro, sum(inva_vida_obre) as inva_vida_obre, sum(guarde_presta) as guarde_presta, sum(censa_vejez_patron) as censa_vejez_patron, sum(censa_vejez_obre) as censa_vejez_obre, sum(infonavit_patro) as infonavit_patro, sum(sar_patron) as sar_patron from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id' and em.tipo_de_nomina='$periodo->nombre_periodo' and em.id in ($cadena_empleados)";
        $totales = DB::connection('empresa')->select($query_sumas);
        $totales = $totales[0];

        $neto_fiscal_real = $totales->total_percepcion_fiscal + ($totales->subsidio * -1);

        if ($columnaPVAC != null) {

            $query_valor_prima = "SELECT sum(total$columnaPVAC->id) as result from rutinas$periodo->ejercicio where fnq_valor=0 and id_periodo='$periodo->id' and id_empleado in (select id from empleados where tipo_de_nomina = '$periodo->nombre_periodo' and id in ($cadena_empleados))";

            $total_valor_prima = DB::connection('empresa')->select($query_valor_prima);
            $pago_prima_vacacional = $total_valor_prima[0]->result;
        } else {
            $pago_prima_vacacional = 0.0;
        }

        $vacaciones = 0.0;

        $total_pagar_nomina = (float)$neto_fiscal_real + (float)$totales->total_percepcion_sindical + (float)$anticipo + (float)$vacaciones + (float)$comision_mismo_dia + (float)$valor_provision_aguinaldo + (float)$valor_provision_prima_vacacional;

        $pago_honorarios = $total_pagar_nomina * $valor_honorarios;

        $datos_facturacion = DB::connection('empresa')->table('datos_facturacion' . $periodo->ejercicio)->where('id_periodo', $periodo->id)->get();
        $cuota_fija = round($datos_facturacion[0]->cuota_fija, 2);
        $exc_cf = round($datos_facturacion[0]->exc_cf, 2);
        $pre_dinero_patronal = round($totales->pre_dine_patro, 2) * $provision_porcentaje;
        $pre_dine_obrero = round($totales->pre_dine_obre, 2) * $provision_porcentaje;

        $pre_patro_adicional = ($provision_obrero > 0) ? $pre_dine_obrero * ($provision_obrero / 100) : 0;
        $pre_dinero_pa = $pre_dinero_patronal + $pre_patro_adicional;


        $query_censa_vejez_obre_patronal = "SELECT sum(censa_vejez_obre_patronal) as suma  from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where em.estatus=1 and finiquitado = 0 and ru.fnq_valor = 0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' $cadenarutina";
        $censa_vejez_obre_patronal_ = DB::connection('empresa')->select($query_censa_vejez_obre_patronal);
        $censa_vejez_obre_patronal = round($censa_vejez_obre_patronal_[0]->suma, 2);

        $gas_medi_patron = round($totales->gas_medi_patro, 2) * $provision_porcentaje;
        $gas_medi_obre = round($totales->gas_medi_obre, 2) * $provision_porcentaje;

        $gas_medi_patron_adicional = ($provision_obrero > 0) ? $gas_medi_obre * ($provision_obrero / 100) : 0;
        $gas_medi_patron = $gas_medi_patron + $gas_medi_patron_adicional;


        $riesgo_trabajo = round($totales->riesgo_trabajo, 2) * $provision_porcentaje;
        $inva_vida_patro = round($totales->inva_vida_patro, 2) * $provision_porcentaje;
        $inva_vida_obre = round($totales->inva_vida_obre, 2) * $provision_porcentaje;

        $inva_vida_patro_adicional = ($provision_obrero > 0) ? $inva_vida_obre * ($provision_obrero / 100) : 0;
        $inva_vida_patro = $inva_vida_patro + $inva_vida_patro_adicional;

        $guarde_presta = round($totales->guarde_presta, 2) * $provision_porcentaje;
        $censa_vejez_patron = round($totales->censa_vejez_patron, 2) * $provision_porcentaje;
        $censa_vejez_obre = round($totales->censa_vejez_obre, 2) * $provision_porcentaje;

        $censa_vejez_patron = $censa_vejez_patron + $censa_vejez_obre;

        $infonavit_patro = round($totales->infonavit_patro, 2) * $provision_porcentaje;
        $sar_patron = round($totales->sar_patron, 2) * $provision_porcentaje;

        $comision_variable = $parametros_empresa->comision_variable;
        $porcentaje_nomina = $parametros_empresa->porcentaje_nomina;

        $porcentaje_nom = $porcentaje_nomina / 100;
        $errogacion = $totales->total_percepcion_fiscal * $porcentaje_nom;

        $valor_prestacion_extra = $parametros_empresa->valor_prestacion_extra;
        $iva = $parametros_empresa->iva;

        $queryNumEmple = "SELECT * from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and pre.estatus=1 and em.id in ($cadena_empleados)";
        $empleados_2 = DB::connection('empresa')->select($queryNumEmple);

        $num_empleados_2 = count($empleados_2);

        $queryvalorseguroGM = "SELECT sum(pre.valor_seguro_GM) as valor_seguro_GM, sum(pre.valor_plan_espejo) as valor_plan_espejo from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado = em.id left join prestaciones_extras pre on em.id = pre.id_empleado where ru.fnq_valor=0 and id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$fecha_final_periodo' and em.id in ($cadena_empleados)";
        $seguros = DB::connection('empresa')->select($queryvalorseguroGM);
        $valor_seguro_GM = $seguros[0]->valor_seguro_GM;
        $valor_plan_espejo = $seguros[0]->valor_plan_espejo;

        $prestaciones_extras = ($valor_prestacion_extra * $num_empleados_2) + $valor_seguro_GM + $valor_plan_espejo;

        $total = $prestaciones_extras + $cuota_fija + $exc_cf + $pre_dinero_pa + $gas_medi_patron + $riesgo_trabajo + $inva_vida_patro + $guarde_presta + $sar_patron + $censa_vejez_patron + $infonavit_patro + $errogacion;

        // dd($cuota_fija,$exc_cf,$pre_dinero_pa,$gas_medi_patron,$riesgo_trabajo,$inva_vida_patro,$guarde_presta,$sar_patron,$censa_vejez_patron,$infonavit_patro);
        $carga_social = $cuota_fija + $exc_cf + $pre_dinero_pa + $gas_medi_patron + $riesgo_trabajo + $inva_vida_patro + $guarde_presta + $sar_patron + $censa_vejez_patron + $infonavit_patro;
        $subtotal = $total_pagar_nomina + $pago_honorarios + $total;
        $iva = $subtotal * $parametros_empresa->iva;
        $total_mayor = $subtotal + $iva;
        $comision = $neto_fiscal_real + $carga_social + $errogacion;
        $valor_comision = $comision * ($comision_variable / 100);
        $subtotal02 = $neto_fiscal_real + $carga_social + $errogacion + $valor_comision;
        $iva02 = $subtotal02 * $parametros_empresa->iva;
        $total_mayor02 = $subtotal02 + $iva02;
        $asesoria_contable = $subtotal - $subtotal02;
        $iva03 = $asesoria_contable * $parametros_empresa->iva;
        $total_mayor03 = $iva03 + $asesoria_contable;

        $totales = [
            'neto_fiscal_real' => $neto_fiscal_real,
            'total_percepcion_sindical' => $totales->total_percepcion_sindical,
            'valor_provision_aguinaldo' => $valor_provision_aguinaldo,
            'valor_provision_prima_vacacional' => $valor_provision_prima_vacacional,
            'total_pagar_nomina' => $total_pagar_nomina,
            'pago_honorarios' => $pago_honorarios,
            'total' => $total,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total_mayor' => $total_mayor,
            'prestaciones_extras' => $prestaciones_extras,
            'cuota_fija' => $cuota_fija,
            'exc_cf' => $exc_cf,
            'pre_dinero_pa' => $pre_dinero_pa,
            'gas_medi_patron' => $gas_medi_patron,
            'riesgo_trabajo' => $riesgo_trabajo,
            'inva_vida_patro' => $inva_vida_patro,
            'guarde_presta' => $guarde_presta,
            'sar_patron' => $sar_patron,
            'censa_vejez_patron' => $censa_vejez_patron,
            'infonavit_patro' => $infonavit_patro,
            'porcentaje_nomina' => $porcentaje_nomina,
            'errogacion' => $errogacion,
            'total' => $total,

            'porcentaje_nomina' => $porcentaje_nomina,
            'carga_social' => $carga_social,
            'comision_variable' => $comision_variable,
            'valor_comision' => $valor_comision,
            'subtotal02' => $subtotal02,
            'iva02' => $iva02,
            'total_mayor02' => $total_mayor02,

            'concepto_facturacion' => $concepto_facturacion,
            'asesoria_contable' => $asesoria_contable,
            'iva03' => $iva03,
            'total_mayor03' => $total_mayor03,
        ];

        /***************************** FIN 2da y 3era TABLA *********************** */

        $emisoras = [];

        if (strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical') {

            $queryEmisoras = "SELECT em.id, em.id_categoria, ememi.razon_social as razon_social, group_concat(em.id) as cadena_empleados, ememi.id as id_empresa_emisora from empleados em join categorias cat on  em.id_categoria=cat.id inner join singh.registro_patronal regpat on cat.tipo_clase=regpat.id inner join singh.empresas_emisoras ememi on regpat.id_empresa_emisora=ememi.id  where cat.estatus=1 and ememi.estatus=1 and regpat.estatus=1 and em.id in ($cadena_empleados) group by ememi.razon_social";
            $emisoras = DB::connection('empresa')->select($queryEmisoras);

            if (count($emisoras) > 1) {
                $i = 0;

                foreach ($emisoras as $emisora) {
                    $i++;

                    $queryPercepFiscalEmisoras = "SELECT sum(total_percepcion_fiscal) as suma from rutinas$periodo->ejercicio ru join empleados em on ru.id_empleado=em.id where ru.fnq_valor=0 and ru.id_periodo='$periodo->id'  and em.tipo_de_nomina='$periodo->nombre_periodo' and em.fecha_alta<='$periodo->fecha_final_perido' and em.id in ($emisora->cadena_empleados)";
                    $percep_fiscal_emisoras = DB::connection('empresa')->select($queryPercepFiscalEmisoras);

                    $percep_fiscal_emisoras = $percep_fiscal_emisoras[0]['suma'];
                    $errogacion_emisora = $percep_fiscal_emisoras * $porcentaje_nom;

                    $queryvaloresFacturacion = "SELECT suministro_per1_$i as suministro_per, carga_social1_$i as carga_social, porcentajes_nomina, porcentaje_comisionV, subtotal_depo1_$i as subtotal_depo, iva_depo1_$i as iva_depo, total_depo1_$i as total_depo, valor_sobre_nomina1_$i as valor_sobre_nomina , valor_comision_variable1_$i as valor_comision_variable from datosfacturacion$periodo->ejercicio where id_periodo = '$periodo->id'";

                    $valores_facturacion = DB::connection('empresa')->select($queryvaloresFacturacion);

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

        return compact('periodo', 'departamentos', 'columnas1', 'columnas2', 'columnasSindical', 'columnaPVAC', 'columnasDEDUCC', 'empleados', 'totales', 'parametros_empresa', 'emisoras', 'idConcepto_faltas_s');
    }

    public function reabrirNomina($id_periodo)
    {
        tienePermiso('periodos_nomina');
        cambiarBase(Session::get('base'));

        if ($this->hayPeriodoAbierto()) {

            session()->flash('danger', 'Actualemnte tiene un periodo abierto, cierralo e intenta de nuevo.');

            return redirect()->route('nomina.periodos');

        }

        // Se activa el periodo
        $periodo = PeriodosNomina::find($id_periodo);
        $periodo->activo = PeriodosNomina::DISP_ABRIR;
        $periodo->save();

        DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)
            ->where('id_periodo', $periodo->id)
            ->update(['estatus' => 1]);

        // Habilitar Deducciones Recurrentes
        $conceptos = DB::connection('empresa')->table('conceptos_nomina')->where('tipo', 1)->where('tipo_proceso', 2)->where('estatus', 1)->get();

        $empleados_rutinas = DB::connection('empresa')->table('rutinas' . $periodo->ejercicio)->where('id_periodo', $periodo->id)->where('fnq_valor', 0)
            ->whereIn('id_empleado', function ($query) use ($periodo) {
                $query->select('id')
                    ->from(with(new Empleado)->getTable())
                    ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                    ->where('tipo_de_nomina', $periodo->nombre_periodo);
            })->get();

        foreach ($conceptos as $concepto) {
            foreach ($empleados_rutinas as $empleado) {
                $col = "total" . $concepto->id;
                if ($empleado->$col > 0) {
                    $incidencia = DB::connection('empresa')->table('incidencias_prg')->where('id_concepto', $concepto->id)->where('id_empleado', $empleado->id_empleado)->max('id');
                    DB::connection('empresa')->table('incidencias_prg')->where('id', $incidencia)->update(['activa_descuento' => 1]);
                }
            }
        }

        session()->flash('success', 'El periodo se re-abrió correctamente.');

        return redirect()->route('nomina.periodos');
    }

    public function abrirNomina(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleados = Empleado::select('id')->where('estatus', Empleado::EMPLEADO_ACTIVO)->count();

        if ($empleados <= 0) {
            session()->flash('danger', 'No existen empleados dados de alta para abrir el periodo.');
            return redirect()->route('nomina.periodos');
        }

        // Se activa el periodo
        $periodo = PeriodosNomina::where('id', $request->idPeriodo)->first();
        $periodo->activo = PeriodosNomina::ACTIVO;


        $conceptoFaltas = ConceptosNomina::where('nombre_concepto', 'FALTAS')->where('estatus', 1)->first();
        $parametros = Parametros::first();

          if ($parametros->biometrico && $request->incluirBiometrico) {
            $fecha_inicial_biometrico = $request->fecha_inicial_apertura;
            $fecha_final_biometrico = ($request->fecha_final_apertura > date('Y-m-d')) ? date('Y-m-d') : $request->fecha_final_apertura;

            $sincronizar=$this->sincronizarAsistencias_calcularFaltas($fecha_inicial_biometrico, $fecha_final_biometrico, $periodo->id, $periodo->ejercicio, ($conceptoFaltas) ? $conceptoFaltas->id : null);

            if($sincronizar===false){
                session()->flash('danger', 'No existen registros del biometrico');
                return redirect()->route('nomina.periodos');
            }

        }
        $periodo->save();

        // Activar los conceptos de nomina
        ConceptosNomina::where('estatus', 1)->update(['activo_en_nomina' => 1]);

        $this->verificarTablaRutinasAdic('rutinas' . $periodo->ejercicio);
        $this->verificarTablaRutinasAdic('adic' . $periodo->ejercicio);

        // Actualizar los otros periodos
        PeriodosNomina::where('estatus', periodosNomina::ESTATUS_DISPONIBLE)
            ->where('id', '!=', $periodo->id)
            ->where('activo', '!=',  periodosNomina::CERRADO)
            ->update(['activo' => periodosNomina::DISP_ABRIR_PERIODO_ACT]);

        $this->actualizarEmpleados($periodo->ejercicio, $periodo->id);

        return redirect()->route('procesos.periodos.nomina.prenomina');
    }

    public function abrirbiometrico(Request $request)
    {
        $this->abrirNomina($request);
        return redirect()->route('nomina.periodos');
    }
}

