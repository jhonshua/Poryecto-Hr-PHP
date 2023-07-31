<?php

namespace App\Http\Controllers\Herramientas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrestamosTipos;
use App\Models\PrestamosRequisitos;
use App\Models\PrestamosTiposGenerales;
use App\Models\DocumentoTipo;
use Illuminate\Support\Facades\File;

class TiposPrestamosController extends Controller
{
    protected const TIPO_PRESTAMO_CERRADO = 0;
    protected const TIPO_PRESTAMO_ABIERTO = 1;
    protected const TIPO_PRESTAMO_BORRADO = 2;

    public function tiposPrestamosTabla()
    {
        $prestamos = PrestamosTipos::where('estatus', "<", self::TIPO_PRESTAMO_BORRADO)
            ->orderBy('prestamos_tipos.id', 'asc')->get();
        $requisitos = PrestamosRequisitos::all();

        return view('herramientas.prestamos.tipos-prestamos.tipos-prestamos', compact('prestamos', 'requisitos'));
    }

    public function tiposPrestamosCrea(Request $request)
    {

        $id = PrestamosTipos::create($request->all())->id;

        session()->flash('success', 'El tipo de préstamo se creó correctamente.');
        return redirect()->route('tiposPrestamo.edita', ['id' => $id]);
    }

    public function tiposPrestamosActualiza(Request $request)
    {
      
        try {
           $prestamo = PrestamosTipos::where('id', $request->id)->update([
            'nombre' => $request->nombre,
            'estatus' => $request->estatus,
            'tipo_solicitud' => $request->tipo_solicitud,
            'antiguedad_meses' => $request->antiguedad_meses,
            'descripcion' => $request->descripcion,
            'notas' => $request->notas,
        ]); 
        session()->flash('success', 'El tipo de préstamo se actualizó correctamente.');
        } catch (\Throwable $th) {
            session()->flash('danger', $th);
        }
        

        return redirect()->route('tiposPrestamo.edita', ['id' => $request->id]);
    }

    public function tiposPrestamosEdita(Request $request)
    {

        $prestamo = PrestamosTipos::where('id', $request->id)->get();
        $requisito = PrestamosRequisitos::where('prestamos_tipos_id', $request->id)->get();
        $generales = PrestamosTiposGenerales::where('id_prestamo_tipos', $request->id)->get();
        $documentos = [];

        if (!empty($generales)) {
            foreach ($generales as $general) {
                $documentos = DocumentoTipo::where('id_tipos_generales', $general->id)->orderBy('id', 'desc')->get();
            }
        }

        return view('herramientas.prestamos.tipos-prestamos.tiposPrestamos-edita', compact('prestamo', 'requisito', 'generales', 'documentos'));
    }

    public function tiposPrestamosElimina(Request $request)
    {
        $prestamo = PrestamosTipos::find($request->id);
        $prestamo->estatus = self::TIPO_PRESTAMO_BORRADO;
        return ($prestamo->save()) ? response()->json(['ok' => 1]) : response()->json(['ok' => 0]);
    }

    /* GENERALES APP MÓVIL */
    public function agregarRequisitosGenerales(Request $request, $imagen_ancla = null, $imagen_detalle = null)
    {

        try {
            $datos= [
                'nombre_ancla' => $request->nombre_ancla,
                'id_prestamo_tipos' => $request->id_prestamo_tipos,
                'nombre_descripcion' => $request->nombre_descripcion,
                'descripcion_ancla' => $request->descripcion_ancla,
                'descripcion_detalle' => $request->descripcion_detalle,
                'telefono_proveedor' => $request->telefono_proveedor
            ];

            if ($request->imagen_detalle != null || $request->imagen_ancla) {
                $detalle_imagen = $request->imagen_detalle;
                $ancla_imagen = $request->imagen_ancla;

                $imagenes = $this->anexarImagenes($ancla_imagen, $detalle_imagen);
                $imagen_ancla  =  $imagenes['imagen_ancla'];
                $imagen_detalle = $imagenes['imagen_detalle'];

                if($imagenes['imagen_ancla']){
                    $datos['imagen_ancla']=$imagen_ancla;
                }

                if($imagenes['imagen_detalle']){
                    $datos['imagen_detalle']=$imagen_detalle;
                }

            }

            $prestamo_requisito = PrestamosTiposGenerales::updateOrInsert(
                ['id' => $request->id], $datos

            );
        } catch (\Throwable $th) {

            session()->flash('danger', 'Ha ocurrido un error, intentalo más tarde.');
            return redirect()->route('tiposPrestamo.edita', ['id' => $request->id_prestamo_tipos]);
        }
        session()->flash('success', 'El tipo de préstamo se actualizó correctamente.');
        return redirect()->route('tiposPrestamo.edita', ['id' => $request->id_prestamo_tipos]);
    }

    public function anexarImagenes($imagen_ancla, $imagen_detalle)
    {
/* Cambiar a storage */
        $folder = 'storage/uploads/prestamos/requisitos_generales/';

        if (!empty($imagen_ancla)) {

            $nombre = $imagen_ancla->getClientOriginalName();
            $nombre = 'img_detalle_' . time() . '_' . $nombre;
            $imagen_ancla->move(public_path($folder), $nombre);
            $imagen_ancla = "/" . $folder . $nombre;
        }

        if (!empty($imagen_detalle)) {

            $nombre = $imagen_detalle->getClientOriginalName();
            $nombre = 'img_detalle_' . time() . '_' . $nombre;
            $imagen_detalle->move(public_path($folder), $nombre);
            $imagen_detalle = "/" . $folder . $nombre;
        }

        return $data = ['imagen_ancla' => $imagen_ancla, 'imagen_detalle' => $imagen_detalle];
    }

    /* Documetntos generales aplicación móvil */
    public function anexarDocumentos(Request $request)
    {
        $id = $request->id;
        $idrequisito = $request->idrequisito;

        $folder = 'storage/uploads/prestamos/documentos_defecto_requisitos/';
        if (sizeof($request->documentos) >  0) {
            foreach ($request->documentos as $key => $file) {

                $descripcion = $request->descripcion[$key];
                $nombre = $file->getClientOriginalName();
                $nombre = time() . '_' . $nombre;
                $file->move(public_path($folder), $nombre);
                $file = "/" . $folder . $nombre;
                DocumentoTipo::create(['id_tipos_generales' => $id, 'descripcion' => $descripcion, 'documento' => $file]);
            }
        }
        session()->flash('success', 'El tipo de préstamo se actualizó correctamente.');
        return redirect()->route('tiposPrestamo.edita', ['id' => $request->idrequisito]);
    }

    public function actualizarDocumentos(Request $request)
    {
        $idrequisito = $request->idBeneficio;
        $folder = 'uploads/prestamos/documentos_defecto_requisitos/';

        foreach ($request->descripcion as $key => $descripcion) {

            $id_documento = $request->id_documento[$key];

            $documento = DocumentoTipo::where('id', $id_documento)->first();

            if (!empty($request->documento[$key])) {

                $file = $request->documento[$key];
                if (!empty($documento->documento)) {
                    unlink(public_path() . $documento->documento);
                }
                $nombre = $file->getClientOriginalName();
                $nombre = time() . '_' . $nombre;
                $file->move(public_path($folder), $nombre);
                $file = "/" . $folder . $nombre;
                $documento->update(['documento' => $file]);
            }

            $documento->update(['descripcion' => $descripcion]);
        }
        session()->flash('success', 'El documento se actualizó correctamente.');
        return redirect()->route('tiposPrestamo.edita', ['id' => $idrequisito]);
    }

    public function eliminaDocumentos(Request $request)
    {

        $documento = DocumentoTipo::where('id', $request->id)->first();
        unlink(public_path().$documento->documento);
        $documento->delete();
        
        return response()->json([
            'ok'   => 1,
        ]);
    }

    /* REQUISITOS */
    public function requisitosCrea(Request $request)
    {
        try {
            $requisito = $request->validate([
                'nombre' => 'required|max:255',
                'tipo' => 'required|max:255',
                'valor' => '',
                'prestamos_tipos_id' => 'numeric',
            ]);
            $archivo = '';

            if ($request->get('tipo') == 'file' && $request->hasFile('valor')) {
                $file = $request->file('valor');
                $archivo = $file->getClientOriginalName();
                $folder = 'storage/uploads/prestamos/requisitos/' . $request->get('prestamos_tipos_id') . "/";
                $file->move(public_path($folder), $archivo);
                $requisito['valor'] = "/" . $folder . $archivo;
            }
            $id = PrestamosRequisitos::create($requisito)->id;
        } catch (\Throwable $th) {
            session()->flash('danger', $th);
            return redirect()->route('tiposPrestamo.edita', ['id' => $request->prestamos_tipos_id]);
        }

        session()->flash('success', 'El tipo de préstamo se actualizó correctamente.');
        return redirect()->route('tiposPrestamo.edita', ['id' => $request->prestamos_tipos_id]);
    }
    public function requisitosElimina(Request $request)
    {
        if ($request->tipo == "file") {
            File::delete(public_path($request->valor));
        }
        if (PrestamosRequisitos::find($request->id)->delete()) {
            return response()->json([
                'ok'   => 1,
            ]);
        } else {
            return response()->json([
                'ok'   => 0,
            ]);
        }
    }
}
