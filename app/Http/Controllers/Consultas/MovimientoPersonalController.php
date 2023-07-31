<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteMovimientoPersonalExport;
use App\Models\Departamento;
use App\Models\Sede;
use App\Models\Empleado;
use DateTime;


class MovimientoPersonalController extends Controller
{
    public function movimientoPersonal()
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
        return view('consultas.movimientos-personal.movimientos-personal', compact('departamentos', 'tiene_sedes', 'sedes'));
    }

    public function busquedaMovimiento(Request $request)
    {
        $movimientos = $this->datosBusqueda($request);
        $tiene_sedes = Session::get('empresa')['sede'];

       

        return response()->json(['ok' => 1, 'movimientos' => $movimientos, 'tiene_sedes' => $tiene_sedes]);
    }

    public function datosBusqueda(Request $request)
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
                'puesto' => (!empty($alta->puesto->puesto)) ? $alta->puesto->puesto : "",
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
                'puesto' => (!empty($baja->puesto->puesto)) ? $baja->puesto->puesto : "",
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

    public function exportarMovimiento(Request $request)
    {
        $mo = $this->datosBusqueda($request);

        return Excel::download(new ReporteMovimientoPersonalExport($mo), "MovimientosEmpleados_" . date('d-m-Y') . ".xlsx");
    }
}
