<?php

namespace App\Http\Controllers\Herramientas;

use App\Events\PrestamoCreado;
use App\Exports\PrestamosExport;
use App\Models\EmpleadoProduccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PrestamosNotas;
use App\Models\PrestamosTipos;
use App\Models\DocumentoTipo;
use App\Models\PrestamosTiposGenerales;
use App\Models\PrestamosInformacion;
use App\Models\ConceptosNomina;
use App\Models\Empresa;
use App\Models\Empleado;
use App\Models\Prestamo;
use App\Models\EmpleadoDeducciones;
use App\Mail\NotificarEmpleadoDocumentoPrestamo;
use App\Mail\NotificarRechazoPrestamo;
use App\Mail\NuevoPrestamoEmpleado;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class PrestamosController extends Controller
{
    public function prestamosTabla()
    {
        $empresas = Empresa::where('estatus', 1)
            ->select('id', 'razon_social', 'base')
            ->orderBy('razon_social', 'asc')
            ->get()->keyBy('id');

        $prestamos_tipos = PrestamosTipos::where('estatus', 1)
            ->orderBy('nombre', 'asc')
            ->get()->keyBy('id');

        $prestamos = Prestamo::where('estatus', '!=', Prestamo::PRESTAMO_BORRADO)
            ->whereIn('usuario_id', [0, Auth::user()->id])
            ->orderBy('id', 'desc')->get();

        $meses = Prestamo::select('fecha_creacion')
            ->where('estatus', '!=', Prestamo::PRESTAMO_BORRADO)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return view('herramientas.prestamos.prestamos-tabla', compact('empresas', 'prestamos_tipos', 'prestamos','meses'));
    }

    public function prestamosGuarda(Request $request)
    {

        $validated = $request->validate([
            'prestamos_tipo_id' => 'required|numeric',
            'usuario_id' => 'required|numeric',
            'empleado_id' => 'required|numeric',
            'empleado' => 'required',
            'empresa_id' => 'required|numeric',
            'medio_contacto' => 'required',
            'estatus' => 'required',
        ]);

        $prestamo = Prestamo::create($validated);

        $prestamo_id = $prestamo->id;

        if (!empty($request->valor) > 0) {
            foreach ($request->valor as $key => $v)  PrestamosInformacion::create(['prestamo_requisito_id' => decrypt($request->requisito_id[$key]), 'prestamo_id' => $prestamo_id, 'valor' => $v]);
        }
        if (sizeof($request->allFiles()) > 0) {
            if ($request->allFiles()) {
                foreach ($request->requisito_id_file as $idreq) {

                    $prestamo_requisito_id = decrypt($idreq);


                    foreach ($request->allFiles()['valores_file_' . $prestamo_requisito_id] as $key => $file) {
                        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

                        if ($extension === 'word' || $extension === 'pdf' || $extension === 'jpg' || $extension === 'png' || $extension === 'zip' || $extension === 'docx') {

                            $nombreArchivo = 'requisito_' . $prestamo_requisito_id . '.' . $file->getClientOriginalExtension();

                            $nombre_documento = $request->empleado_id . '_' . $nombreArchivo;

                            $folder = 'prestamos/tickets/' . $prestamo_id . "/" . $request->empleado_id . "/";

                            $path = Storage::disk('public')->put($folder, $file);
                            $archivo_cer = strlen($folder);

                            $archivo_n = substr($path, $archivo_cer);
                            $subido_cer = Storage::disk('public')->move($folder . '/' . $archivo_n, $folder . $nombreArchivo);


                            $documentos = array('prestamo_id' => $prestamo_id, 'prestamo_requisito_id' => $prestamo_requisito_id,  'valor' => "/" . $folder . $nombreArchivo);
                            try {
                             
                                PrestamosInformacion::insert($documentos);
                            } catch (\Throwable $th) {
                                
                            }
                        } else {
                            session()->flash('danger', 'Los documentos ingresados son incorrectos, verificalo e ingresalos en el área de edición nuevamente.');
                        }
                    }
                }
                if ($request->tipo_solicitud == "2") {
                    $importe_total = $request->precio_real + $request->amortizacion;
                    $plazo_a_meses =  PrestamosInformacion::join('prestamos_requisitos', 'prestamos_informacion.prestamo_requisito_id', '=', 'prestamos_requisitos.id')
                        ->select('prestamos_informacion.valor')
                        ->where(['prestamos_informacion.prestamo_id' => $prestamo->id, 'prestamos_requisitos.tipo' => 'numerico'])/* cambia a numerico para plazo a meses */
                        ->first();

                    cambiarBase($request->empresa_base);

                    $tipo_de_nomina = Empleado::where('id', $request->empleado_id)->select('tipo_de_nomina')->first();

                    if ($tipo_de_nomina->tipo_de_nomina == "QUINCENAL") {
                        $numero_pagos_a_realizar = (int) $plazo_a_meses->valor * 2;
                    } elseif ($tipo_de_nomina->tipo_de_nomina == "SEMANAL") {
                        $numero_pagos_a_realizar = (int) $plazo_a_meses->valor * 4;
                    } else {
                        $numero_pagos_a_realizar = (int) $plazo_a_meses;
                    }

                    $cantidad_a_descontar = bcdiv($importe_total / $numero_pagos_a_realizar, 1, 2);


                    if ($request->nombrePrestamo == "Prestamo - Crédito Automotriz") {
                        $concepto_nomina = ConceptosNomina::where('nombre_corto', 'CREDITOAUTO_S')->select('id_alterno')->first();
                    } elseif ($request->nombrePrestamo == "Prestamo - Préstamo de nomina") {
                        $concepto_nomina = ConceptosNomina::where('nombre_corto', 'ARRACAPITAL_S')->select('id_alterno')->first();
                    } elseif ($request->nombrePrestamo == "Prestamo - Adelantos de Nomina") {
                        $concepto_nomina = ConceptosNomina::where('nombre_corto', 'ADELANTONOMINA_S')->select('id')->first();
                    } elseif ($request->nombrePrestamo == "Prestamo - Crédito para Motocicletas") {
                        $concepto_nomina = ConceptosNomina::where('nombre_corto', 'CREDITOMOTO_S')->select('id_alterno')->first();
                    } elseif ($request->nomrePrestamo == "Viajes Singh") {
                        $concepto_nomina = ConceptosNomina::where('nombre_corto', 'PACIFICTRAVEL')->select('id_alterno')->get();
                    }
                    $id_concepto = $concepto_nomina->id_alterno;
                    EmpleadoDeducciones::create([
                        'id_empleado' => $request->empleado_id,
                        'id_concepto' => $id_concepto,
                        'estatus' => 1,
                        'importe_total' => $importe_total,
                        'numero_pagos_a_realizar' => $numero_pagos_a_realizar,
                        'cantidad_a_descontar' => $cantidad_a_descontar,
                        'fecha_inicio' => date('Y-m-d'),
                    ]);
                }
            }
        }
        if (!empty($request->get('texto')) && $prestamo_id) {
            PrestamosNotas::create([
                'prestamo_id' => $prestamo_id,
                'texto' => $request->get('texto'),
                'fecha' => date('Y-m-d H:i:s')
            ]);
        }

        try {
            $informacion = array(
                'nombrePrestamo' => $request->nombrePrestamo,
                'medio_contacto' => $request->medio_contacto,
                'empleado_' => $request->empleado_,
                'empleado' => $request->empleado,
                'email' => $request->email,
                'prestamos_tipo_id' => $request->prestamos_tipo_id,
            );

            PrestamoCreado::dispatch(encrypt($prestamo_id), $informacion);

            session()->flash('success', 'La solicitud se creó correctamente y se envió un email al empleado con los pasos a seguir.');
        } catch (\Throwable $th) {
            session()->flash('danger', 'No se envió el correo electrónico al destinatario correspondiente , sin embargo se creo la solicitud satisfactoriamente.');
        }
        return redirect()->route('prestamos.tabla');
    }

    public function prestamosObtenerEmpleados(Request $request)
    {

        $return = ['ok' => 0, 'empleado' => null];
        if (empty($request->base)) {
            return response()->json($return);
        } else {
            cambiarBase($request->base);

            $empleados = Empleado::where('estatus', 1)
                ->orderBy('apaterno', 'asc')
                ->get();

            return response()->json(['ok' => 1, 'empleados' => $empleados]);
        }
    }
    public function prestamosElimina(Request $request)
    {
        $prestamo = Prestamo::find($request->id);
        $prestamo->estatus = Prestamo::PRESTAMO_BORRADO;
        return ($prestamo->save()) ? response()->json(['ok' => 1]) : response()->json(['ok' => 0]);
    }
    public function prestamosCrea(Request $request)
    {
        $data = $request->all();
        $prestamo_seleccionado = PrestamosTipos::with('requisitos')->whereIn('id', [$data['tipo_prestamo']])->first();
        $idUsuario = Auth::user()->id;
        $empresa = Empresa::where('base', $request->base)->first();

        return view('herramientas.prestamos.prestamos-crear', compact('data', 'prestamo_seleccionado', 'idUsuario', 'empresa'));
    }

    public function prestamosAsignaEjecutivo(Request $request)
    {
        //aun no esta concluido
        $prestamo=Prestamo::where('id',$request->id)->first();
        $prestamo->update(['usuario_id' => Auth::user()->id]);
        $empresa = Empresa::find($prestamo->empresa_id);
        $emp = new EmpleadoProduccion;

        $empleado = $emp->obtenerEmpleadoPorID($empresa->base, $prestamo->empleado_id);
        $req = [
            'email' => $empleado->correo,
            'empleado' => $prestamo->empleado,
            'nombrePrestamo' => $request->tipoPrestamo,
            'prestamos_tipo_id' => $prestamo->prestamos_tipo_id
        ];

        try {
            // ENVIAR EL MAIL CON LA INFO AL EMPLEADO
            PrestamoCreado::dispatch(encrypt($prestamo->id), $req);
        } catch (\Throwable $th) {
            return response()->json(['ok' => 2]);
        }

        return response()->json(['ok' => 1]);
    }

    /*
 * Pagina del EMPLEADO - Subir documentacion del prestamo
 */
    public function revisar(Prestamo $prestamo) {
        $prestamo->load('empresa', 'tipoPrestamo', 'usuario', 'notas');
        $prestamo->tipoPrestamo->load('requisitos');
        // dd($prestamo);
        $tiposPrestamos = PrestamosTipos::with('requisitos')->get();
        $empresa = Empresa::find($prestamo->empresa_id);
        // dd($empresa);

        cambiarBase(Session::get('base'));
        $empleado = EmpleadoProduccion::find($prestamo->empleado_id);

        // dd($empresa->base, $prestamo->empleado_id);
        // dd($empleado);
        if($prestamo->estatus == Prestamo::PRESTAMO_PARA_REVISION) {
            $prestamo->load('requisitosLlenos');
            // $prestamo->requisitosLlenos->load('requisito');
        }

        return view('herramientas.prestamos.revisar', compact('prestamo', 'tiposPrestamos', 'empleado'));
    }

    public function crearNota(Request $request) {
        if ($request->ajax()) {
            $nota = $request->validate([
                'texto' => 'required',
                'prestamo_id' => 'required'
            ]);

            PrestamosNotas::create($nota);
            return response()->json([
                'ok'   => 1,
            ]);
        }
    }

    /*
     * Eliminar documento
     */
    public function borrarDocumento(PrestamosInformacion $documento, Request $request) {
        if ( $request->ajax()) {
            $archivo = str_replace('\\', '/', public_path($documento->valor));
            $archivo = str_replace('//', '/', $archivo);
            try{
                File::delete($archivo);
            } catch(Exception $e) {
                dd($e);
            }
            if($documento::destroy($documento->id)) {
                return response()->json([
                    'ok'   => 1,
                ]);
            } else {
                return response()->json([
                    'ok'   => 0,
                ]);
            }
        } else {
            return null;
        }
    }

    /*
     * Metodo que cierra el prestamo
     */
    public function cerrar(Prestamo $prestamo, Request $request)
    {
        if ($request->ajax()) {
            $prestamo->update(['estatus' => Prestamo::PRESTAMO_CERRADO,'fecha_cierre' => date("Y-m-d\TH:i:s")]);
            return response()->json([
                'ok'   => 1,
            ]);
        }

        // TODO - FALTA ENVIAR EL MAIL CON LA INFO AL EMPLEADO (mailable)
    }

    public function exportar(Request $request){
        $estatus = $request->get('estatus');

        $query = Prestamo::join('empresas', 'prestamos.empresa_id', '=', 'empresas.id')
            ->join('usuarios', 'prestamos.usuario_id', '=', 'usuarios.id')
            ->join('prestamos_tipos', 'prestamos.prestamos_tipo_id', '=', 'prestamos_tipos.id')
            ->select('prestamos.id', 'empresas.razon_social', 'usuarios.nombre_completo', 'prestamos_tipos.nombre', 'prestamos.estatus','prestamos.fecha_creacion', 'prestamos.fecha_edicion', 'prestamos.fecha_cierre')
            ->orderBy('prestamos.id', 'desc');

        if($estatus == null) {
            $query->where('prestamos.estatus', '!=', Prestamo::PRESTAMO_BORRADO);
        } else {
            $query->where('prestamos.estatus', $estatus);
        }

        if($request->get('meses') == null) {
            $query->where('prestamos.fecha_creacion', 'like', $request->get('meses').'*');
        }
        $prestamos = $query->get();
        foreach($prestamos as $prestamo){
            switch($prestamo->estatus) {
                case 0:
                    $prestamo->estatus = 'Cerrado';
                    break;
                case 1:
                    $prestamo->estatus = 'Abierto';
                    break;
                case 3:
                    $prestamo->estatus = 'Rechazado';
                    break;
                case 4:
                    $prestamo->estatus = 'Para revisión';
                    break;
            }
        }
        return (new PrestamosExport($prestamos))->download('prestamos-'.date('Y-M-d').'.xlsx');

    }

}
