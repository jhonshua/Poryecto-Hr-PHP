<?php

namespace App\Http\Controllers\Herramientas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\CategoriaActivo;
use App\Models\Activos;
use App\Models\ActivosArchivo;
use App\Models\ActivosCampos;
use App\Models\DetalleActivosEmpleados;
use App\Models\DetalleIconoFormulario;

class ActivosController extends Controller
{
    /* CATEGORIA DE ACTIVOS */
    public function categoriaActivos()
    {
        cambiarBase(Session::get('base'));

        $resultados = CategoriaActivo::orderBy('id', 'desc')->get();

        return view('herramientas.categoria-activos.categoria-activos', compact('resultados'));
    }

    public function crearEditarCategoria(Request $request)
    {
        cambiarBase(Session::get('base'));

        $request->validate([
            'nombre_activo' => 'required',
            'estatus' => 'required',
        ]);
        try {
            DB::connection('empresa')->table('categorias_activos')->updateOrInsert(
                ['id' => $request->get('id')],
                [
                    'nombre_activo' => $request->get('nombre_activo'),
                    'estatus' => $request->get('estatus')
                ]
            );
        } catch (\Throwable $th) {

            session()->flash('danger', 'Ha ocurrido un error, intentalo más tarde.' . $th);
        }

        session()->flash('success', 'Los datos se guardaron correctamente.');
        return redirect()->route('categoriaActivo.tabla');
    }

    public function eliminaCategoria(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('categorias_activos')->where('id', $request->get('id'))->delete();
        return response()->json(['ok' => 1]);
    }

    /* ASIGNAR ACTIVOS */
    public function asignaActivos()
    {
        cambiarBase(Session::get('base'));

        $resultados = Activos::orderBy('id', 'desc')->get();

        return view('herramientas.asignar-activos.asignar-activos', compact('resultados'));
    }

    public function crearEditarActivos(Request $request)
    {
        cambiarBase(Session::get('base'));
        if ($request->id != null) {

            $id = $request->id;
            $id_empresa = Session::get('empresa')['id'];
            $resultados = DB::connection('empresa')->table('activos as a')
                ->join('categorias_activos as c', 'c.id', '=', 'a.id_categoria_activo')
                ->select('a.id', 'id_categoria_activo', 'c.nombre_activo', 'a.nombre', 'a.descripcion', 'a.comentarios', 'a.marca', 'a.modelo', 'a.nserie', 'a.estatus as estatus_activo')
                ->where('a.id', $id)
                ->orderBy('a.id', 'DESC')
                ->get();
            $activos_archivos = ActivosArchivo::where('id_activo', $id)->orderBy('id', 'desc')->get();
            $activosCamposExtra = ActivosCampos::where('id_activo', $id)->orderBy('id', 'desc')->get();
            $categorias = CategoriaActivo::where('estatus', 1)->orderBy('id', 'asc')->get();

            $categorias = CategoriaActivo::where('estatus', 1)->orderBy('id', 'asc')->get();

            return view('herramientas.asignar-activos.modificar-activos', compact('resultados', 'categorias', 'activos_archivos', 'id_empresa', 'activosCamposExtra'));
        } else {
            $categorias = CategoriaActivo::where('estatus', 1)->orderBy('id', 'asc')->get();
            return view('herramientas.asignar-activos.crear-activos', compact('categorias'));
        }
    }

    public function crearModificarActivos(Request $request)
    {
        cambiarBase(Session::get('base'));

        $data = array(
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'comentarios' => $request->comentarios,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'nserie' => $request->nserie,
            'estatus' => $request->estatus,
            'id_categoria_activo' => decrypt($request->id_categoria_activo)
        );

        if (empty($request->id)) {

            $activo = Activos::create($data);

            if (!empty($request->nombre_archivo)) {
                foreach ($request->nombre_archivo as $key => $nombre_archivo) {

                    $file_archivo = $request->file_archivo[$key];
                    $file_archivo = $this->agregarArchivoDir($file_archivo);

                    ActivosArchivo::create(['id_activo' => $activo->id, 'nombre_archivo' => $nombre_archivo, 'file_archivo' => $file_archivo]);
                }
            }
            if (!empty($request->nombre_label)) {
                foreach ($request->nombre_label as $key1 => $nombre_label) {

                    ActivosCampos::create(['id_activo' => $activo->id, 'nombre_label' => $nombre_label, 'valor' => $request->valor[$key1]]);
                }
            }
            session()->flash('success', 'Los datos se guardaron correctamente.');
            return redirect()->route('asignaActivos.tabla');
        } else {
            $activo = Activos::updateOrInsert(['id' => decrypt($request->id)], $data);

            session()->flash('success', 'Los datos se guardaron correctamente.');
            return redirect()->route('asignaActivos.creaMod', ['id' => decrypt($request->id)]);
        }
    }
    public function agregarArchivoDir($file_archivo)
    {

        cambiarBase(Session::get('base'));
        $path = 'storage/repositorio/' . Session::get('empresa')['id'] . "/documentos_activos" . '/';
        $file = $file_archivo;
        $extension = $file->getClientOriginalName();
        $file_archivo = time() . $extension;
        $file->move($path, $file_archivo);
        return $file_archivo;
    }

    public function eliminaActivos(Request $request)
    {
        cambiarBase(Session::get('base'));
        $existe_activo = DetalleActivosEmpleados::where('id_activo', $request->id)->exists();
        $estado = "";
        if ($request->estatus === "1") {

            if ($existe_activo) {
                Activos::where('id', $request->id)->update(['estatus' => 2]);
                $estado = 2;
            } else {
                $id_activo = $request->id;
                Activos::where('id', $id_activo)->delete();
                ActivosCampos::where('id_activo', $id_activo)->delete();
                DetalleActivosEmpleados::where('id_activo', $id_activo)->delete();
                $archivos = ActivosArchivo::where('id_activo', $id_activo)->get();

                if (sizeof($archivos) > 0) {
                    foreach ($archivos as $archivo) {
                        $url_empresa = Session::get('empresa')['id'] . "/documentos_activos" . '/';
                        $url = 'storage/repositorio/' . $url_empresa . $archivo->file_archivo;
                        unlink($url);
                    }
                }
                $archivos = ActivosArchivo::where('id_activo', $id_activo)->delete();
                $estado = 1;
            }
        } else {

            Activos::where('id', $request->id)->update(['estatus' => 1]);
            $estado = 1;
        }

        return response()->json(['ok' => $estado]);
        return redirect()->route('asignaActivos.tabla');
    }

    public function eliminarArchivos(Request $request)
    {
        cambiarBase(Session::get('base'));

        $ids = array('id_activo' => \decrypt($request->id_activoel), 'id' => \decrypt($request->id_archivoel));
        $nombre_anterior = ActivosArchivo::where($ids)->first();
        $activos_archivos = ActivosArchivo::where($ids)->delete();

        $url_empresa = Session::get('empresa')['id'] . "/documentos_activos" . '/';

        $url = 'storage/repositorio/' . $url_empresa . $nombre_anterior->file_archivo;
        unlink($url);

        return response()->json(['ok' => 1]);
    }

    public function creaModArchivo(Request $request)
    {

        cambiarBase(Session::get('base'));
        if (empty($request->id_archivo)) {
            if ($request->hasFile("file_archivo")) {

                $file_archivo = $this->agregarArchivoDir($request->file_archivo);

                $data = array(
                    'id_activo' => \decrypt($request->id_activo),
                    'nombre_archivo' => $request->nombre_archivo,
                    'file_archivo' => $file_archivo
                );

                $nombre_anterior = ActivosArchivo::create($data);
            }
        } else {

            $ids = ['id_activo' => \decrypt($request->id_activo), 'id' => \decrypt($request->id_archivo)];

            $nombre_anterior = ActivosArchivo::where($ids)->update(['nombre_archivo' => $request->nombre_archivo]);
            $nombre_anterior = ActivosArchivo::where($ids)->first();

            if ($request->hasFile("file_archivo")) {

                $file = $request->file_archivo;
                $file_archivo = $this->eliminarArchivoDir($file, $nombre_anterior);
                ActivosArchivo::where($ids)->update(['file_archivo' => $file_archivo]);
            }
        }

        return redirect()->route('asignaActivos.creaMod', ['id' => \decrypt($request->id_activo)])->with('respuesta_modificacion', 1);
    }

    public function creaEliminaCampo(Request $request)
    {
        cambiarBase(Session::get('base'));

        if ($request->id_campo_ext == null) {
            ActivosCampos::create(['id_activo' => \decrypt($request->id_activo), 'nombre_label' => $request->nombre_label_add, 'valor' => $request->valor_add]);

            return redirect()->route('asignaActivos.creaMod', ['id' => \decrypt($request->id_activo)])->with('respuesta_modificacion', 1);
        } else {
            ActivosCampos::where(['id' => decrypt($request->id_campo_ext), 'id_activo' => decrypt($request->id_activo)])->delete();
            return response()->json(['ok' => 1]);
        }
    }

    public function actualizaCampo(Request $request)
    {
        cambiarBase(Session::get('base'));
        foreach ($request->idactivo as $key => $id) {

            $data = array(
                'nombre_label' => $request->nombre_label[$key],
                'valor' => $request->valor_campo_extra[$key]
            );

            $activosCamposExtra = ActivosCampos::where(['id' => decrypt($request->id_campo_extra[$key]), 'id_activo' => decrypt($id)])->update($data);
        }
        return redirect()->route('asignaActivos.creaMod', ['id' => \decrypt($id)])->with('respuesta_modificacion', 1);
    }

    public function eliminarArchivoDir($file, $nombre_anterior)
    {

        cambiarBase(Session::get('base'));
        $url_empresa = Session::get('empresa')['id'] . "/documentos_activos" . '/';
        $path = 'storage/repositorio/' . $url_empresa;
        $extension = $file->getClientOriginalName();
        $nombre = time() . $extension;
        $file->move($path, $nombre);
        $file_archivo = $nombre;
        $documento_eliminar = $nombre_anterior->file_archivo;
        if ($file_archivo !== $documento_eliminar) {
            $url = 'storage/repositorio/' . $url_empresa . $documento_eliminar;
            unlink($url);
        } else {
            $file_archivo = $nombre_anterior->file_archivo;
        }
        return $file_archivo;
    }
    public function asignaEmpleado(Request $request)
    {
        $id = $request->id;

        cambiarBase(Session::get('base'));
        $detalle_empleados = DetalleActivosEmpleados::where('id_activo', $id)->get();
        $array_empleado = [];
        if (\sizeof($detalle_empleados) > 0)

            foreach ($detalle_empleados as $empleado) $array_empleado[] = $empleado->id_empleado;

        $empleados = DB::connection('empresa')->table('empleados as e')
            ->join('departamentos as d', 'd.id', '=', 'e.id_departamento')
            ->join('puestos as p', 'p.id', '=', 'e.id_puesto')
            ->select('e.id', 'e.nombre', 'e.apaterno', 'e.amaterno', 'd.nombre as departemento', 'p.puesto')
            ->where('e.estatus', 1)
            ->whereNotIn('e.id', $array_empleado)
            ->orderBy('e.nombre', 'asc')
            ->get();

        $empleados_asignados = DB::connection('empresa')->table('detalle_activos_empleados as da')
            ->join('empleados as e', 'e.id', '=', 'da.id_empleado')
            ->join('departamentos as d', 'd.id', '=', 'e.id_departamento')
            ->join('puestos as p', 'p.id', '=', 'e.id_puesto')
            ->select('e.id', 'e.nombre', 'e.apaterno', 'e.amaterno', 'd.nombre as departemento', 'p.puesto')
            ->where('da.id_activo', $id)
            ->orderBy('e.nombre', 'asc')
            ->get();

        return view('herramientas.asignar-activos.asignar-empleados', compact('empleados', 'empleados_asignados', 'id'));
    }
    public function asignarEmpleados(Request $request)
    {
        $id_activo = $request->id_activo;
        cambiarBase(Session::get('base'));

        try {
            foreach ($request->empleados as $empleas) {
                $existe_empleado = DetalleActivosEmpleados::where('id_activo', $id_activo)->where('id_empleado', $empleas)->first();
                ($existe_empleado) ? $id = $existe_empleado->id : $id = "";
                $respuesta = DetalleActivosEmpleados::updateOrCreate(['id' => $id], ['id_activo' => $id_activo, 'id_empleado' => $empleas]);
            }
        } catch (\Throwable $th) {
            dd($th);
        }

        return redirect()->route('asignaActivos.asignaEmpleado', $request->id_activo)->with('respuesta', 1);
    }

    public function eliminaEmpleados(Request $request)
    {

        cambiarBase(Session::get('base'));
        try {
            $existe_empleado = DetalleActivosEmpleados::where('id_activo', $request->id_activo)->where('id_empleado', $request->id_empleado)->delete();
        } catch (\Throwable $th) {
            session()->flash('danger', 'Ha ocurrido un error.' . $th);
            return redirect()->route('asignaActivos.asignaEmpleado', $request->id_activo);
        }
        session()->flash('success', 'Los datos se actualizaron correctamente.');
        return redirect()->route('asignaActivos.asignaEmpleado', $request->id_activo);
    }
}
