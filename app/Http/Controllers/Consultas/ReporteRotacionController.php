<?php

namespace App\Http\Controllers\consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Sede;
use App\Models\Empleado;
use App\Models\Departamento;
use DateInterval;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteRotacionEmppleadosExport;


class ReporteRotacionController extends Controller
{
    function indiceRotacionPersonal()
    {
        cambiarBase(Session::get('base'));
        $deptos_asignados = Session::get('usuarioDepartamentos');
        $sedes_asignadas = Session::get('usuarioSedes');
        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get();
        $tiene_sedes = Session::get('empresa')['sede'];
        $sedes = array();
        if ($tiene_sedes == 1) {
            $sedes = Sede::where('estatus', 1)->whereIn('id', $sedes_asignadas)->orderBy('nombre', 'asc')->get();
        }
        return view('consultas.indice-rotacion.indice-rotacion', compact('departamentos', 'tiene_sedes', 'sedes'));
    }

    function datosBusqueda(Request $request)
    {
        cambiarBase(Session::get('base'));

        $movimientos = array();
        if (!empty($request->altas) && $request->altas == 1) {
            $altas = $this->altasEmpleado($request);
            $movimientos = array_merge($movimientos, $altas);
        }
        if (!empty($request->bajas) && $request->bajas == 1) {
            $bajas = $this->bajasEmpleado($request);
            $movimientos = array_merge($movimientos, $bajas);
        }
        return $movimientos;
    }

    function exportar(Request $request)
    {
        $mo = $this->datosBusqueda($request);
        return Excel::download(new ReporteRotacionEmppleadosExport($mo), "RotacionEmpleados_" . date('d-m-Y') . ".xlsx");
    }

    function busqueda(Request $request)
    {
        $movimientos = $this->datosBusqueda($request);
        $tiene_sedes = Session::get('empresa')['sede'];

        return response()->json(['ok' => 1, 'movimientos' => $movimientos, 'tiene_sedes' => $tiene_sedes]);
    }


    function altasEmpleado(Request $request)
    {

        cambiarBase(Session::get('base'));
        $altas_final = array();
        $inicio = new DateTime($request->fecha_inicio);
        $fin = new DateTime($request->fecha_fin);
        $altas = Empleado::whereBetween('fecha_alta', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->whereIn('id_departamento', $request->departamentos)->with('departamento', 'puesto');

        if (!empty($request->sedes)) {
            $altas = $altas->whereIn('sede', $request->sedes)->with('sde');
        }

        foreach ($altas->orderBy('fecha_creacion', 'desc')->get() as $alta) {
            $falta = new DateTime($alta->fecha_alta);
            $temp = array(
                'id' => $alta->id,
                'nombre' => $alta->nombre . " " . $alta->apaterno . " " . $alta->amaterno,
                'departamento' => $alta->departamento->nombre,
                'puesto' => (!empty($alta->puesto->nombre)) ? $alta->puesto->nombre : "",
                'estatus' => 'ALTA',
                'fecha_alta' => $falta->format('d-m-Y'),
                'fecha_baja' => 'N/A',
                'causa_baja' => 'N/A',
                'estatus_firma_finiquito' => 'N/A',
                'finiquitado' => 'N/A'
            );
            if (!empty($request->sedes)) {
                $temp['sede'] = (isset($alta->sde)) ? $alta->sde->nombre : "";
            } else {
                $temp['sede'] = "N/A";
            }

            $altas_final[] = $temp;
        }

        return $altas_final;
    }

    function bajasEmpleado(Request $request)
    {

        cambiarBase(Session::get('base'));
        $bajas_final = array();
        $inicio = new DateTime($request->fecha_inicio);
        $fin = new DateTime($request->fecha_fin);
        $bajas = Empleado::where(function ($query) {
            $query->where('estatus', Empleado::EMPLEADO_BAJA)
                ->orWhere('estatus', Empleado::EMPLEADO_BAJA_DEFINITIVO)
                ->orWhere('estatus', Empleado::EMPLEADO_INACTIVO);
        })->whereBetween('fecha_baja', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->whereIn('id_departamento', $request->departamentos)->with('departamento', 'puesto');

        if (!empty($request->sedes)) {
            $bajas = $bajas->whereIn('sede', $request->sedes)->with('sde');
        }

        foreach ($bajas->orderBy('fecha_baja', 'desc')->get() as $baja) {
            $falta = new DateTime($baja->fecha_alta);
            $fbaja = new DateTime($baja->fecha_baja);
            $temp = array(
                'id' => $baja->id,
                'nombre' => $baja->nombre . " " . $baja->apaterno . " " . $baja->amaterno,
                'departamento' => $baja->departamento->nombre,
                'puesto' => (!empty($baja->puesto->nombre)) ? $baja->puesto->nombre : "",
                'estatus' => 'BAJA',
                'fecha_alta' => $falta->format('d-m-Y'),
                'fecha_baja' => $fbaja->format('d-m-Y'),
                'causa_baja' => $baja->causa_baja,
                'estatus_firma_finiquito' => ($baja->estatus_firma_finiquito == 1) ? 'SI' : 'NO',
                'finiquitado' => ($baja->finiquitado == 1) ? 'SI' : 'NO'
            );
            if (!empty($request->sedes)) {
                $temp['sede'] = (isset($baja->sde)) ? $baja->sde->nombre : "";
            } else {
                $temp['sede'] = "N/A";
            }

            $bajas_final[] = $temp;
        }

        return $bajas_final;
    }

    public function rotacionIndicePersonal()
    {
        cambiarBase(Session::get('base'));
        //$deptos_asignados = Session::get('usuarioDepartamentos');
        $sedes_asignadas = Session::get('usuarioSedes');
        //$departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos_asignados)->orderBy('nombre', 'asc')->get();
        $tiene_sedes = Session::get('empresa')['sede'];
        $sedes = array();
        if ($tiene_sedes == 1) {
            // $empleados = $empleados->whereIn('sede', $sedes_asignadas);
            $sedes = Sede::where('estatus', 1)->whereIn('id', $sedes_asignadas)->orderBy('nombre', 'asc')->get();
            //dd($sedes,$sedes_asignadas);
        }
        return view('consultas.empleados.indice_rotacion_personal', compact('tiene_sedes', 'sedes'));
    }

    public function datosBusquedAIndice(Request $request)
    {
        if ($request->tipo == 1) { // periodo
            return $this->datosIndice($request->fecha_inicio, $request->fecha_fin, $request->sede);
        } else if ($request->tipo == 2) { // meses
            $datos = array();
            $inicio = new DateTime('01-' . $request->mes_inicio);
            $fin = new DateTime('31-' . $request->mes_fin);
            $intervalo = new DateInterval('P' . $request->numero_meses . 'M');
            $intervalo2 = new DateInterval('P1D');
            $i = 0;
            while ($inicio < $fin) {
                $datos[$i]['inicio'] = $inicio->format('d-m-Y');

                $inicio->add($intervalo);
                $datos[$i]['fin'] = $inicio->format('d-m-Y');
                $i++;
            }

            for ($i = 0; $i < count($datos); $i++) {
                $ffinal = new DateTime($datos[$i]['fin']);
                $ffinal->sub($intervalo2);
                $datos[$i]['fin'] = $ffinal->format('d-m-Y');
                $datos[$i]['datos'] = $this->datosIndice($datos[$i]['inicio'], $datos[$i]['fin'], $request->sede);
            }

            return $datos;
        } else if ($request->tipo == 3) { // anio
            $datos = array();
            $anio_inicio = $request->anio_inicio;
            $anio_fin = $request->anio_fin;

            for ($i = 0; $anio_inicio <= $anio_fin; $i++) {
                $inicio = new DateTime('01-01-' . $anio_inicio);
                $fin = new DateTime('31-12-' . $anio_inicio);

                $datos[$i]['inicio'] = $inicio->format('d-m-Y');
                $datos[$i]['fin'] = $fin->format('d-m-Y');
                $datos[$i]['datos'] = $this->datosIndice($datos[$i]['inicio'], $datos[$i]['fin'], $request->sede);
                $anio_inicio++;
            }
            return $datos;
        }
    }

    public function datosIndice($fecha_inicio, $fecha_fin, $sede)
    {
        $datos['finicio'] = $fecha_inicio;
        $datos['ffin'] = $fecha_fin;
        $sde = 0;
        $datos['sede'] = 'N/A';
        if (!empty($sede)) {
            $datos_sede = explode(".", $sede);
            if ($datos_sede[0] == 0) {
                $datos['sede'] = 'N/A';
                $sede = 0;
            } else {
                $datos['sede'] = $datos_sede[1];
                $sede = $datos_sede[0];
            }
        }
        $datos['empleados_inicio'] = $this->altasInicial($fecha_inicio, $sede);

        $datos['i'] = $datos['empleados_inicio']->count();

        $datos['empleados_fin'] = $this->altasInicial($fecha_fin, $sede);

        $datos['f'] = $datos['empleados_fin']->count();

        $datos['bajas'] = $this->bajasPeriodo($fecha_inicio, $fecha_fin, $sede);
        $datos['bajas_total'] = $datos['bajas']->count();
        $datos['altas_periodo'] = $this->altasPeriodo($fecha_inicio, $fecha_fin, $sede);
        $datos['altas_periodo_total'] = $datos['altas_periodo']->count();

        if ($datos['bajas_total'] > $datos['altas_periodo_total']) {
            $datos['s'] = $datos['bajas_total'];
        } else {
            $datos['s'] = $datos['altas_periodo_total'] - $datos['bajas_total'];
        }

        $datos['promedio_total'] = round(($datos['i'] + $datos['f']) / 2);


        if ($datos['promedio_total'] == 0) {
            $datos['irp'] = 0;
        } else {
            $datos['irp'] = round(($datos['s'] / $datos['promedio_total']) * 100);
        }
        return $datos;
    }

    function altasInicial($fecha_inicio, $sede)
    {

        cambiarBase(Session::get('base'));
        $altas_inicial = array();
        $inicio = new DateTime($fecha_inicio);

        $altas = Empleado::where('fecha_alta', '<', $inicio->format('Y-m-d'))
            ->where(function ($query) use ($inicio) {
                $query->where('fecha_baja', '=', '0000-00-00')
                    ->orWhere('fecha_baja', '=', null)
                    ->orWhere('fecha_baja', '>', $inicio->format('Y-m-d'));
            });

        if (!empty($sede) && $sede > 0) {
            $altas = $altas->where('sede', '=', $sede);
        }
        return $altas->get();
    }

    function bajasPeriodo($fecha_inicio, $fecha_fin, $sede)
    {

        cambiarBase(Session::get('base'));
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $bajas = Empleado::where(function ($query) {
            $query->where('estatus', Empleado::EMPLEADO_BAJA)
                ->orWhere('estatus', Empleado::EMPLEADO_BAJA_DEFINITIVO)
                ->orWhere('estatus', Empleado::EMPLEADO_INACTIVO);
        })->whereBetween('fecha_baja', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->where(function ($query) {
                $query->where('causa_baja', '!=', 'JUBILACION')
                    ->where('causa_baja', '!=', 'DEFUNCION');
            });

        if (!empty($sede) && $sede > 0) {
            $bajas = $bajas->where('sede', '=', $sede);
        }
        return $bajas->get();
    }

    function altasPeriodo($fecha_inicio, $fecha_fin, $sede)
    {

        cambiarBase(Session::get('base'));
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $altas = Empleado::whereBetween('fecha_alta', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->where(function ($query) use ($fin) {
                $query->where('fecha_baja', '=', '0000-00-00')
                    ->orWhere('fecha_baja', '=', null)
                    ->orWhere('fecha_baja', '>', $fin->format('Y-m-d'));
            });
        if (!empty($sede) && $sede > 0) {
            $altas = $altas->where('sede', '=', $sede);
        }
        return $altas->get();
    }

    public function exportarIndice(Request $request)
    {
        $datos = $this->datosBusquedAIndice($request);
        $txt = "";
        if ($request->tipo == 2) {
            $txt = "Mensual_" . $request->numero_meses . "mes";
        } else {
            $txt = "Anual";
        }
        return Excel::download(new ReporteRotacionEmppleadosExport($datos), "IndiceRotacionEmpleados_" . $txt . "_" . date('d-m-Y') . ".xlsx");
    }

    public function busquedaIndice(Request $request)
    {
        $datos = $this->datosBusquedAIndice($request);
        return response()->json(['ok' => 1, 'datos' => $datos, 'tipo' => intval($request->tipo)]);
    }
}
