<?php

namespace App\Http\Controllers\Empleados;


use App\Models\Empleado;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\HttpCache\Ssi;

class KitBajaController extends Controller
{


    public function estaCompletoKitBaja($archivosBaja, $archivosBajaEmpleado)
    {
        $estaCompleto = true;
        $obligatorios = $archivosBaja->where('obligatorio', 1);
        //dump($obligatorios, $archivosBajaEmpleado);
        foreach ($obligatorios as $archivo) {
           
            if ($archivosBajaEmpleado == null || !array_key_exists($archivo->nombre_campo, $archivosBajaEmpleado)) {
                $estaCompleto = false;
                // dump($archivosBajaEmpleado->id_empleado);die();
                return false;
            }
        }

        return $estaCompleto;
    }


    public function subirArchivos(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleado_id = $request->id_empleado;
        $return = $request->return_id;

        $archivos = ['err' => []];
        // dd($request);
        // subimos los archivos
        if ($request->allFiles() && $empleado_id > 0) {
            foreach ($request->allFiles() as $id_archivo => $archivo) {
                //dd($id_archivo);
                $nombreArchivo = $id_archivo . '.' . $archivo->getClientOriginalExtension();
                $folder = 'storage/repositorio/' . Session::get('empresa')['id'] . '/' . $empleado_id . "/kitBaja/";

                if ($archivo->move(public_path($folder), $nombreArchivo)) {

                    DB::connection('empresa')->table('kit_baja_info')->updateOrInsert(
                        [
                            'nombre_campo' => $id_archivo,
                            'id_empleado' => $empleado_id
                        ],
                        [
                            'archivo' => $nombreArchivo,
                            'fecha_creacion' => date('Y-m-d H:i:s')
                        ]
                    );
                } else {
                    $archivos['err'][] = $id_archivo;
                }
            }

            if (count($archivos['err']) > 0) {
                $msg = "En el proceso hubo errores y no se guardaron los siguientes archivos: " . implode(', ', $archivos['err']);
                $tipo = 'danger';
            } else {
                $tipo = 'success';
                $msg = 'Los archivos se subieron correctamente.';
            }


            session()->flash($tipo, $msg);
            if ($return == "kit_baja") {
                return redirect()->route('empleados.kitBajaTabla');
            } else {
                return redirect()->route('procesos.historico');
            }
        }


        session()->flash('danger', 'Los archivos no se subieron. Intente nuevamente.');

        if ($return == "kit_baja") {
            return redirect()->route('empleados.kitBajaTabla');
        } else {
            return redirect()->route('procesos.historico');
        }
    }

    /* SECCIÓN DE EMPLEADOS */
    public function listKitBaja()
    {
        cambiarBase(Session::get('base'));

        $empleados = Empleado::with('departamento')
            ->where(function ($query) {
                $query->where('estatus', Empleado::EMPLEADO_BAJA)
                    ->orWhere('estatus', Empleado::EMPLEADO_BAJA_DEFINITIVO);
            })->get()->keyBy('id');

        $archivosBaja = DB::connection('empresa')->table('kit_baja_campos')->get();

        $empleadosIds = $empleados->pluck('id')->toArray();

        $archivosBajaEmpleados = DB::connection('empresa')->table('kit_baja_info')
            ->whereIn('id_empleado', $empleadosIds)->get();

        foreach ($archivosBajaEmpleados as $archivo) {
            $kitBaja[$archivo->id_empleado][$archivo->nombre_campo] = $archivo;
        }

        $deptos = [];

        foreach ($empleados as $empleado) {
            $deptos[$empleado->id_departamento] = $empleado->id_departamento;
            if (isset($kitBaja[$empleado->id])) {
                $empleado->kitBaja = $kitBaja[$empleado->id];
            } else {
                $empleado->kitBaja = null;
            }
            $empleado->kitBajaCompleto = $this->estaCompletoKitBaja($archivosBaja, $empleado->kitBaja);
        }
        $departamentos = Departamento::where('estatus', 1)->whereIn('id', $deptos)->orderBy('nombre', 'asc')->get();

        $kitBajaCampos = DB::connection('empresa')->table('kit_baja_campos')->get();

        return view('empleados_admin.kit-baja.kitBaja-tabla', compact('empleados', 'departamentos', 'kitBajaCampos'));
    }

    /* SECCIÓN PARAMETRIA */
    public function camposKitBaja()
    {
        cambiarBase(Session::get('base'));

        $campos = DB::connection('empresa')
            ->table('kit_baja_campos')
            ->orderBy('alias', 'desc')
            ->get();

        return view('parametria.configuracion-kitBaja.configuracion-kitBaja', compact('campos'));
    }

    public function crearEditarConfiguracion(Request $request)
    {
        cambiarBase(Session::get('base'));
        try {
            DB::connection('empresa')->table('kit_baja_campos')->updateOrInsert(
                ['id' => $request->id],
                [
                    'nombre_campo' => strtolower($request->nombre_campo),
                    'alias' => strtoupper($request->alias),
                    'obligatorio' => $request->obligatorio
                ]
            );

            $accion = ($request->id) ? 'editó' : 'creó';
        } catch (\Throwable $th) {
            session()->flash('danger', 'Ha ocurrido un error, por favor intentalo más tarde');
        }

        session()->flash('success', 'El campo se ' . $accion . ' correctamente.');
        return redirect()->route('kitbaja.tabla');
    }

    public function borrarConfiguracion(Request $request)
    {
        cambiarBase(Session::get('base'));

        DB::connection('empresa')->table('kit_baja_campos')->where('id', $request->id)->delete();

        return response()->json(['ok' => 1]);
    }
}
