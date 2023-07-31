<?php

namespace App\Http\Controllers\Empleado;

use App\Models\Empresa;
use App\Models\Empleado;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use App\Models\PrestamosTipos;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class PrestamosController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except('miPrestamo','subirInformacion');
        $this->middleware('hayEmpleado')->except('inicio_');

    }

    public function inicio(Request $request)
    {
        $prestamos = Prestamo::join('prestamos_tipos', 'prestamos.prestamos_tipo_id', '=', 'prestamos_tipos.id')
                        ->select('prestamos.*', 'prestamos.id AS pid', 'prestamos_tipos.id as prestamo_tipo_id', 'prestamos_tipos.nombre')
                        ->where('prestamos.estatus', '!=', Prestamo::PRESTAMO_BORRADO)
                        ->where('prestamos.empleado_id', Session::get('empleado')['id'])
                        ->orderBy('prestamos.id', 'desc')
                        ->get();
            // dd($prestamos);

        // Peticion API
        if ($request->ajax()) {
            return response()->json(['prestamos' => $prestamos]);
        }

        $prestamos_disponibles = $this->obtenerPrestamosPorFechaAntiguedadEmpleado(Session::get('empleado')['fecha_antiguedad']);

        // $empresa = Empresa::where('base', Session::get('base'))->first();
        $empresaId = Session::get('empresa')['id'];

        return view('empleados.prestamos.inicio', compact('prestamos', 'prestamos_disponibles', 'empresaId'));
    }

    /**
     * Regresa un JSON con los prestamos que tiene un empleado
     * Recibe un int con el ID del empleado
     */
    public function inicio_(Request $request)
    {
        $prestamos = Prestamo::join('prestamos_tipos', 'prestamos.prestamos_tipo_id', '=', 'prestamos_tipos.id')
                        ->select('prestamos.*', 'prestamos.id AS pid', 'prestamos_tipos.id as prestamo_tipo_id', 'prestamos_tipos.nombre')
                        ->where('prestamos.estatus', '!=', Prestamo::PRESTAMO_BORRADO)
                        ->where('prestamos.empleado_id', $request->id)
                        ->orderBy('prestamos.id', 'desc')
                        ->get();
            // dd($prestamos);

        // Peticion API
        return response()->json(['prestamos' => $prestamos]);
    }

    /**
     *
     */
    public function solicitar(Request $request)
    {
        $prestamo = Prestamo::create($request->all());
        // Peticion API
        if ($request->ajax()) {
            ($prestamo) ? response()->json(['ok' => 1]) : response()->json(['ok' => 0]);
        }
        return redirect()->route('empleados.prestamos')
                        ->with('tipo_alerta', 'success')
                        ->with('mensaje', 'El prestamo se solicitó correctamente. Espera la respuesta y seguimiento de tu ejecutivo.');
    }

    /**
     *
     */
    public function detalle(Prestamo $prestamo, Request $request)
    {
        $prestamo->load('tipoPrestamo');

        if ($request->ajax()) {
            $prestamo->tipoPrestamo->load('requisitos');
            return response()->json(['prestamo' => $prestamo]);
        }

        return view('empleados.prestamos.detalle', compact('prestamo'));
    }

    /**
     * Obtiene los tipos de prestamos disponibles segun su antiguedad
     */
    public function obtenerPrestamosPorEmpleadoId($empleadoId = null, $empresaId = null)
    {
        if($empleadoId && $empresaId){
            $emp = Empresa::find($empresaId);
            cambiarBaseA($emp->base);
            $empleado = Empleado::find($empleadoId);
            $fecha_antiguedad = $empleado->fecha_antiguedad;
        } else{
            $fecha_antiguedad = Session::get('empleado')['fecha_antiguedad'];
        }

        $prestamos_disponibles = null;
        $prestamos_tipos = PrestamosTipos::where('estatus', 1)
                            ->with('requisitos')
                            ->orderBy('nombre', 'asc')
                            ->get();

        foreach($prestamos_tipos as $prestamo) {
            $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $from = Carbon::createFromFormat('Y-m-d', $fecha_antiguedad);
            $meses = $to->diffInMonths($from);
            if($meses >= $prestamo->antiguedad_meses){
                $prestamos_disponibles[] = $prestamo;
            }
        }

        return $prestamos_disponibles;
    }

    /**
     *
     */
    public function obtenerPrestamosPorFechaAntiguedadEmpleado($fecha_antiguedad_empleado)
    {
        if(!$fecha_antiguedad_empleado)
            return response()->json(['prestamos' => null]);

        $prestamos_disponibles = null;
        $prestamos_tipos = PrestamosTipos::where('estatus', 1)
                            ->with('requisitos')
                            ->orderBy('nombre', 'asc')
                            ->get();

        foreach($prestamos_tipos as $prestamo) {
            $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $from = Carbon::createFromFormat('Y-m-d', $fecha_antiguedad_empleado);
            $meses = $to->diffInMonths($from);
            if($meses >= $prestamo->antiguedad_meses){
                $prestamos_disponibles[] = $prestamo;
            }
        }

        // Peticion API
        if (request()->ajax()) {
            return response()->json(['prestamos' => $prestamos_disponibles]);
        } else{
            return $prestamos_disponibles;
        }
    }
}
