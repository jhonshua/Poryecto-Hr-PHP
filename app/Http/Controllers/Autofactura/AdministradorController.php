<?php

namespace App\Http\Controllers\Autofactura;

use App\Exports\CompletadoCFDIExport;
use App\Http\Controllers\cfdiUtils\CFDIUtils;
use App\Http\Controllers\Controller;
use App\Models\Autofacturador\CatEtiquetasEmpEmisoras;
use App\Models\Autofacturador\CatProductosServicios;
use App\Models\Autofacturador\CatUnidades;
use App\Models\Autofacturador\Cfdi;
use App\Models\Autofacturador\Clientes;
use App\Models\Autofacturador\Comision;
use App\Models\Autofacturador\ComprobantesPagos;
use App\Models\Autofacturador\EmpresasEmisoras;
use App\Models\Autofacturador\RegimenFiscal;
use App\Models\Autofacturador\UsoCFDI;
use App\Models\Autofacturador\Vendedores;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Weidner\Goutte\GoutteFacade;

class AdministradorController extends Controller
{
    public function index()
    {
        return view('autofacturador.administrador.index');
    }

    public function getInvoices()
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        $cfdi = Cfdi::select('cfdi.id','cfdi.folio', 'singh.usuarios.nombre_completo', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'cfdi.observaciones', 'cfdi.contrato_nombre')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora')
            ->leftJoin('singh.usuarios', 'cfdi.usuario_id', '=', 'singh.usuarios.id')
            ->whereIn('estado', [Cfdi::CFDI_APROBADO,Cfdi::CFDI_SOLCITADO]);

        if(!$user->timbrar)
            $cfdi = $cfdi->where('singh.usuarios.id_vendedor', $user->id);

        return response()->json(['data' => $cfdi->get()]);
    }

    public function getInvoicesFull()
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $cfdi = Cfdi::select('cfdi.id', 'cfdi.folio', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'singh.usuarios.nombre_completo')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora')
            ->leftJoin('singh.usuarios', 'cfdi.usuario_id', '=', 'singh.usuarios.id')
            ->whereIn('estado', [Cfdi::CFDI_CANCELADO, Cfdi::CDFI_FACTURADO]);

        if(!$user->timbrar)
            $cfdi =$cfdi->where('singh.usuarios.id_vendedor', $user->id);

        $cfdi = $cfdi->get();

        return response()->json(['data' => $cfdi]);
    }

    public function getInvoicesCancelados(){
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $cfdi = Cfdi::select('cfdi.id','cfdi.folio', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'cfdi.observaciones', 'cfdi.contrato_nombre', 'singh.usuarios.nombre_completo as cliente')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora')
            ->leftJoin('singh.usuarios', 'cfdi.usuario_id', '=', 'singh.usuarios.id')
            ->whereIn('estado', [Cfdi::PROCESO_CANCELADO,Cfdi::CFDI_SOLCITADO,Cfdi::CFDI_APROBADO]);

        $cfdi = $cfdi->get();

        return response()->json(['data' => $cfdi]);
    }

    public function getRegistroAdmin($tipo, $id)
    {
        cambiarBase(Auth::user()->clientes->base);
        switch ($tipo) {
            case 'emisora':
                $response = EmpresasEmisoras::find($id);
                break;
            case 'etiqueta_emisora':
                $response = CatEtiquetasEmpEmisoras::find($id);
                break;
            case 'productos_servicios':
                $response = CatProductosServicios::where('clave', $id)->first();
                break;
            case 'regimen_fiscal':
                $response = RegimenFiscal::where('codigo', $id)->first();
                break;
            case 'unidades':
                $response = CatUnidades::select('clave', 'nombre')->where('clave', $id)->first();
                break;
            case 'cfdi':
                $response = UsoCFDI::where('clave', $id)->first();
                break;
            case 'vendedor':
                $response = Vendedores::where('id', $id)->first();
                break;
        }

        return response()->json($response);
    }

    public function setRegistroAdmin($tipo, Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        switch ($tipo) {
            case 'emisora':
                if(Auth::id() == 1583 || Auth::id() == 1558 || Auth::id() == 1626 || Auth::id() == 1670){
                    $contenidoBinario = ($request->logo_base64) ? file_get_contents($request->logo_base64) : null;
                    $imagenComoBase64 = ($request->logo_base64) ? base64_encode($contenidoBinario) : null;
                    $imagenPreparada = ($request->logo_base64) ? 'data:image/png;base64,'.$imagenComoBase64 : null;

                    $data = ['razon_social' => $request->razon_social,
                        'rfc' => $request->rfc,
                        'regimen_fiscal' => $request->regimen_fiscal,
                        'banco' => $request->banco,
                        'clave' => $request->clave,
                        'cp' => $request->cp,
                        'domicilio' => $request->domicilio,
                        'cuenta' => $request->cuenta,
                        'id_cat_etiqueta_emisora' => $request->id_cat_etiqueta_emisora,
                        'colores' => $request->colores,
                        'logo_base64' => $imagenPreparada,
                        'correo' => $request->correo,
                    ];

                    if($imagenPreparada==null)
                        unset($data['logo_base64']);


                    $datos = EmpresasEmisoras::updateOrCreate(
                        ['id' => $request->id],$data
                    );

                    if ($request->kit_fiscal != null)
                        $filePath = $request->file('kit_fiscal')->storeAs('kit_fiscal/' . $datos->id, 'kit_fiscal.zip', 'public');

                    $datos = ['status' => true];
                }else{
                    $datos = ['status' => false,'mensaje' => 'No tienes los permisos suficientes'];
                }

                break;
            case 'etiqueta_emisora':
                $datos = CatEtiquetasEmpEmisoras::updateOrCreate(['id' => $request->id], $request->toArray());
                break;
            case 'productos_servicios':
                $datos = CatProductosServicios::updateOrCreate(['clave' => $request->clave], $request->toArray());
                break;
            case 'regimen_fiscal':
                $datos = RegimenFiscal::updateOrCreate(['codigo' => $request->codigo], $request->toArray());
                break;
            case 'unidades':
                $datos = CatUnidades::updateOrCreate(['clave' => $request->clave], $request->toArray());
                break;
            case 'cfdi':
                $datos = UsoCFDI::updateOrCreate(['clave' => $request->clave], $request->toArray());
                break;
            case 'sellos_fiscales':
                $request->file('key')->storeAs('trash/', $request->id . '-key.key', 'public');
                $request->file('cer')->storeAs('trash/', $request->id . '-cer.cer', 'public');

                try {
                    CFDIUtils::convertKeyToPem($request->id, $request->password);
                    CFDIUtils::convertCetToPem($request->id);
                    logAutofacturador(Auth::user()->id,'Aguardado,Convercion de keys');
                    $datos = ['status' => true];

                    $emisora= EmpresasEmisoras::where('id',$request->id)->first();
                    $emisora->sellos_fiscales=1;
                    $emisora->save();
                } catch (\Throwable $th) {
                    $datos = ['status' => false,'mensaje' => 'La contraseña es incorrecta'];
                }

                break;
            case 'vendedor':
                $datos = Vendedores::updateOrCreate(['id' => $request->id], $request->toArray());
                break;
        }

        return response()->json([$datos]);
    }

    public function eliminarEmpresa(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = EmpresasEmisoras::find($request->id);
        $delete->delete();
        return response()->json($delete);
    }

    public function eliminarEtiqueta(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = CatEtiquetasEmpEmisoras::find($request->id);
        $delete->delete();
        return response()->json($delete);
    }

    public function eliminarProductosServicios(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = CatProductosServicios::where('clave', $request->clave)->first();
        $delete->delete();
        return response()->json($delete);
    }

    public function eliminarRegimenFiscal(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = RegimenFiscal::where('codigo', $request->codigo)->first();
        $delete->delete();
        return response()->json($delete);
    }

    public function eliminarUnidades(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = CatUnidades::where('clave', $request->clave)->first();
        $delete->delete();
        return response()->json($delete);
    }

    public function eliminarUsoCFDI(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $delete = UsoCFDI::where('clave', $request->clave)->first();
        $delete->delete();
        return response()->json($delete);
    }

    public function getCfdi($id)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $id)->with(['emisora', 'conceptos', 'logs','comprobantesPago','relacionCfdi'])->first();

        return response()->json($cfdi);
    }

    public function aprobarOC(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::find($request->id);
        $cfdi->estado = Cfdi::CFDI_APROBADO;
        $cfdi->save();

        logAutofacturador(Auth::user()->id,Cfdi::CFDI_APROBADO, $cfdi->id);

        return response()->json(['status' => true]);
    }

    public function reloadPDF($id)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        if ($user->admin) {
            $cfdi = Cfdi::where('id', $id)->with(['emisora', 'conceptos'])->first();
            $cfdi_utils = new CFDIUtils($cfdi);
            $cfdi_utils->generaPdf();
            $file = Storage::disk('public')->get($user->clientes->base .'/' . $cfdi->usuario_id . '/facturas/autofactura-' . $cfdi->id . '.pdf');

            return (new Response($file, 200))->header('Content-Type', 'application/pdf');
        }
        return 'Acceso no permitido';
    }

    public function reloadPDFPagos($id){
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        if ($user->admin) {
            $pago = ComprobantesPagos::find($id);
            $cfdi = $pago->getCFDI;
            $cfdi_utils = new CFDIUtils($cfdi);
            $cfdi_utils->generarPdfPago($id);
            $file = Storage::disk('public')->get($user->clientes->base . '/' . $cfdi->usuario_id . '/pagos/autofactura-pago-' . $cfdi->id .'-'.$cfdi->comprobantePago->num_pago. '.pdf');

            return (new Response($file, 200))->header('Content-Type', 'application/pdf');
        }
        return 'Acceso no permitido';
    }

    public function timbrar(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id)->with(['emisora', 'conceptos','comprobantesPago','relacionCfdi'])->first();

        $ruta_key=storage_path('app/public/'.Auth::user()->clientes->base.'/emisoras/'.$cfdi->id_emp_emsora.'/key.pem');
        $ruta_pem=storage_path('app/public/'.Auth::user()->clientes->base.'/emisoras/'.$cfdi->id_emp_emsora.'/cer.pem');

        if (!file_exists($ruta_key) && !file_exists($ruta_pem)){
            return response()->json(['mensaje' => 'No existen sus llaves']);
        }else if(!file_exists($ruta_key)){
            return response()->json(['mensaje' => 'No existen su llave key.pem']);
        }else if(!file_exists($ruta_pem)){
            return response()->json(['mensaje' => 'No existen su llave cer.pem']);
        }

        $cfdi_utils = new CFDIUtils($cfdi);

        $cfdi_utils->crearCDFI();
        $soap_pac = $cfdi_utils->sendSoap();

        if ($soap_pac->status) {
            $cfdi->estado = Cfdi::CDFI_FACTURADO;
            $cfdi->save();

            $cfdi_utils->generaPdf();

            $call = ['cfdi'=>$cfdi, 'emisora'=>$cfdi->emisora];

            $path = storage_path('app/public/'.Auth::user()->clientes->base.'/' . $cfdi->usuario_id . '/facturas');
            $pdf = $path . '/autofactura-' . $cfdi->id . '.pdf';
            $xml = $path . '/autofactura-' . $cfdi->id . '.xml';

            if($cfdi->receptor->correo){
                try{
                    $subject = "Facturas HR System";
                    $correos = explode(',', trim($cfdi->receptor->correo));
                    $correos = array_map('trim', $correos);

                    Mail::send('emails.autofacturador.avisosTimbrado',$call,function($msj) use($correos,$xml,$pdf,$subject,$cfdi){
                        $msj->attach($xml, ['as' => 'OC_' . $cfdi->folio. '-' . $cfdi->serie .'.xml']);
                        $msj->attach($pdf, ['as' => 'OC_' . $cfdi->folio. '-' . $cfdi->serie .'.pdf']);
                        $msj->subject($subject);
                        $msj->to($correos);
                    } );
                }catch(\Exception $e){
                    $soap_pac->status = true;
                    $soap_pac->mensaje = 'Factura timbrada exitosamente.';
                }

            }

            // TODO: Se esperan instrucciones en generar el contrato
            /*if($cfdi->total >= 150000){
                $cfdi->contrato_nombre = $cfdi->id.'_'.time().'.pdf';
                $this->generaContrato($cfdi);
            }*/
        }
        $cfdi->save();

        return response()->json($soap_pac);
    }

    public function cancelarTimbre(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id)->with(['emisora', 'conceptos'])->first();
        $cfdi_utils = new CFDIUtils($cfdi);
        $mensaje = $cfdi_utils->cancelarCFDI();
        logAutofacturador(Auth::user()->id,Cfdi::CFDI_CANCELADO,$cfdi->id);
        $cfdi_utils->generaPdf();
        return response()->json(['mensaje' => $mensaje]);
    }

    public function cancelarCfdiPago(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id_cfdi)->first();
        $cfdi_utils = new CFDIUtils($cfdi);
        $mensaje = $cfdi_utils->cancelarCFDIPago($request->id_pago);

        return response()->json(['mensaje' => $mensaje]);
    }

    public function timbrarPagoComprobante(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id_cfdi)->with(['emisora', 'comprobantePago'=> function ($query) use ($request) {
            $query->where('id', $request->id_comprobante);
        },'comprobantesPago'=> function ($query){
            $query->where('confirmado',1)->where('estado',3);
        }])->first();

        $ruta_key = storage_path('app/public/'.Auth::user()->clientes->base.'/emisoras/'.$cfdi->id_emp_emsora.'/key.pem');
        $ruta_pem = storage_path('app/public/'.Auth::user()->clientes->base.'/emisoras/'.$cfdi->id_emp_emsora.'/cer.pem');

        if (!file_exists($ruta_key) && !file_exists($ruta_pem)){
            return response()->json(['mensaje' => 'No existen sus llaves']);
        }else if(!file_exists($ruta_key)){
            return response()->json(['mensaje' => 'No existen su llave key.pem']);
        }else if(!file_exists($ruta_pem)){
            return response()->json(['mensaje' => 'No existen su llave cer.pem']);
        }

        $cfdi_utils = new CFDIUtils($cfdi);
        $cfdi_utils->crearPago();
        $soap_pac= $cfdi_utils->sendSoap();

        if ($soap_pac->status) {
            $cfdi_utils->generarPdfPago();

            $call = ['cfdi' => $cfdi, 'emisora' => $cfdi->emisora];

            $path = storage_path('app/public/' . Auth::user()->clientes->base . '/' . $cfdi->usuario_id . '/pagos');
            $pdf = $path . '/autofactura-pago-' . $cfdi->id .'-'.$cfdi->comprobantePago->num_pago. '.pdf';
            $xml = $path . '/autofactura-pago-' . $cfdi->id .'-'.$cfdi->comprobantePago->num_pago. '.xml';

            if ($cfdi->receptor->correo) {
                try {
                    $subject = "Facturas HR System";
                    $for = array();
                    array_push($for, $cfdi->receptor->correo);

                    Mail::send('emails.autofacturador.avisosTimbrado', $call, function ($msj) use ($for, $xml, $pdf, $subject) {
                        $msj->attach($xml);
                        $msj->attach($pdf);
                        $msj->subject($subject);
                        $msj->to($for);
                    });
                } catch (\Exception $e) {
                    $soap_pac->status = false;
                    $soap_pac->mensaje = 'Factura timbrada exitosamente.';
                }

            }
        }

        return response()->json($soap_pac);
    }

    public function generaContrato($cfdi)
    {
        cambiarBase(Auth::user()->clientes->base);
        $ruta_contrato = storage_path('app/public/'.Auth::user()->clientes->base.'/' . $cfdi->usuario_id . '/contratos/');

        if (!File::exists($ruta_contrato)) {
            File::makeDirectory($ruta_contrato, $mode = 0777, true, true);
        }

        //obtener plantilla de contrato
        $filename = storage_path('app/public/'.Auth::user()->clientes->base.'/plantilla_contrato/contrato.txt');

        // reemplazar tags con valores
        $contenido = File::get($filename);
        $contenido = str_replace("[nombre]", 'Memo', $contenido);

        // generar pdf y guardarlo en el repositorio
        $pdf = PDF::loadView("empleados_admin.contratos.contrato", compact('contenido'))->save($ruta_contrato . '/' . $cfdi->contrato_nombre);

        if ($pdf)
            return response()->json(['status' => true, 'msg' => "El contrato se generó con éxito"]);

        return response()->json(['status' => false, 'msg' => "No fue posible generar el contrato, intentelo de nuevo"]);
    }

    public function downloadContrato($id)
    {
        cambiarBase(Auth::user()->clientes->base);
        if (Auth::user()->admin) {
            $cfdi = Cfdi::find($id);
            $file = Storage::disk('public')->get('autofacturas/' . $cfdi->usuario_id . '/contratos/' . $cfdi->contrato_nombre);

            return (new Response($file, 200))->header('Content-Type', 'application/pdf');
        }
        return 'Usuario no es Administrador';
    }

    public function rechazarOP(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::find($request->id);
        $cfdi->observaciones = $request->observaciones;
        $cfdi->estado = Cfdi::PROCESO_CANCELADO;
        $cfdi->save();

        logAutofacturador(Auth::user()->id,Cfdi::PROCESO_CANCELADO,$cfdi->id);

        return response()->json(['status' => true]);
    }

    public function recursosVew()
    {
        return view('autofacturador.administrador.administracion_recursos');
    }

    public function confirmarComprobante(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $comprobante = ComprobantesPagos::where('id', $request->id)->first();
        $comprobante->confirmado = $request->confirmado;
        $comprobante->save();

        return $comprobante;
    }

    public function downloadCompletados(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $datos = array();
        $cfdis = Cfdi::selectRaw('cfdi.*, vendedor.nombre_completo, singh.usuarios.nombre_completo')
            ->with(['cfdiConceptos'])
            ->whereBetween('fecha_timbre', [$request->date_inicio, $request->date_final])
            ->leftJoin('singh.usuarios', 'cfdi.usuario_id', '=', 'singh.usuarios.id')
            ->leftJoin('singh.usuarios as vendedor', 'vendedor.id_vendedor', '=', 'singh.usuarios.id');

        if(isset($request->timbre_Emisor) && count($request->timbre_Emisor))
            $cfdis->whereIn('emisor_nombre', $request->timbre_Emisor);

        if(isset($request->timbre_Receptor) && count($request->timbre_Receptor))
            $cfdis->WhereIn('receptor_nombre', $request->timbre_Receptor);

        if(isset($request->timbre_Cliente) && count($request->timbre_Cliente))
            $cfdis->whereIn('usuario_id', $request->timbre_Cliente);

        if(isset($request->timbre_Vendedor) && count($request->timbre_Vendedor))
            $cfdis->whereIn('singh.usuarios.id_vendedor', $request->timbre_Vendedor);

        if($request->canceladas_xls)
            $cfdis->whereIn('estado', [Cfdi::CFDI_CANCELADO, Cfdi::CDFI_FACTURADO]);
        else
            $cfdis->where('estado', Cfdi::CDFI_FACTURADO);

        $cfdis = $cfdis->get();

        if (count($cfdis)) {
            foreach ($cfdis as $cfdi) {

                if (!$cfdi->comision) {
                    $comision_porcentaje = 'Sin Datos';
                    $comision_pagar = 'Sin Datos';
                } else {
                    $comision_porcentaje = $cfdi->comision;
                    $comision_pagar = ($cfdi->pagar_del == 'subtotal') ? $cfdi->comision * $cfdi->subtotal / 100 : $cfdi->comision * $cfdi->total / 100;
                }

                $cantidad_comprobante = "0";
                foreach ($cfdi->comprobantesPago as $comprobante)
                    if ($comprobante->confirmado)
                        $cantidad_comprobante += $comprobante->cantidad;

                $dato = array(
                    'tipo' => ($cfdi->estado == Cfdi::CDFI_FACTURADO) ? 'Facturado' : 'Cancelado',
                    'fechaEmision' => $cfdi->created_at,
                    'fechatTimbrado' => $cfdi->fecha_timbre,
                    'folio' => 'OC-'.$cfdi->folio,
                    'rfc_emisor' => $cfdi->emisor_rfc,
                    'nombre_emisor' => $cfdi->emisor_nombre,
                    'rfc_receptor' => $cfdi->receptor_rfc,
                    'nombre_receptor' => $cfdi->receptor_nombre,
                    'uso_cfdi' => $cfdi->usoCFDI->clave . ' - ' . $cfdi->usoCFDI->uso_cfdi,
                    'comision' => $comision_porcentaje,
                    'monto' => $comision_pagar,
                    'comision_base' => $cfdi->pagar_del,
                    'subtotal' => $cfdi->subtotal,
                    'iva' => $cfdi->tasa_cuota,
                    'total' => $cfdi->total,
                    'formaPago' => $cfdi->formaPago->clave . ' - ' . $cfdi->formaPago->descripcion,
                    'metodoPago' => $cfdi->metodoPago->metodo . ' - ' . $cfdi->metodoPago->descripcion,
                    'cliente' => $cfdi->nombre_completo ?? 'No registrado',
                    'vendedor' => $cfdi->nombre ?? 'No registrado',
                    'conceptos' =>$cfdi->cfdiConceptos,
                );
                array_push($datos, $dato);
            }

            return Excel::download(new CompletadoCFDIExport($datos), "CDFI_Completados_" . date('d-m-Y') . ".xlsx");
        } else {
            return back()->withErrors([
                'nada' => 'No se ha encontrando ningun registro'
            ]);
        }
    }

    public function storeComision(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        $comisiones = new Comision();
        $comisiones->id_cfdi = $request->id_cfdi_comision;
        $comisiones->comision = $request->comision;
        $comisiones->pagar = $request->pago;
        $comisiones->pagar_del = ($request->pagar_del == 0) ? 'total' : 'subtotal';

        if ($request->tipo == 0) {
            $comisiones->retorno = 'efectivo';
        } else {
            $comisiones->retorno = 'tarjeta';
            $comisiones->nombre = $request->nombre;
            $comisiones->banco = $request->banco;
            $comisiones->numCuenta = $request->cuenta;
            $comisiones->clave_interbancaria = $request->clave_interbancaria;
        }

        $comisiones->save();

        return $request;
    }

    public function totalsubtotal(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id)->first();
        return $cfdi;
    }

    public function getRelBaseAutofacturador(){
        $user = Auth::user();
        $clientes = $user->clientesAutofacturas()->get();
        
        return response()->json(['clientes'  => $clientes, 'base_actual' => $user->base_autofacturador]);
    }

    public function getBaseAutofacturador(){
        if(isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador ||
            isset(Auth::user()->admin) && !Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador){
            $clientes = Clientes::where('base',Auth::user()->clientes->base)->get();
        }else{
            $clientes = Clientes::all();
        }
        return $clientes;
    }

    public function scapingEmpresaEmisora(Request $request){
        cambiarBase(Auth::user()->clientes->base);

        $document = GoutteFacade::request('GET', $request->url);
        $items = $document->filter('.ui-datatable-even, .ui-datatable-odd')->each(function ($node) {
            $explode = explode(':', $node->text());
            return ['item' => preg_replace("/[^a-zA-Z0-9\_\-]+/", "", $explode[0]), 'data' => $explode[1]];
        });

        $data = [];
        foreach ($items as $item)
            $data[$item['item']] = $item['data'];

        $codigo_regimen = RegimenFiscal::where('regimen', 'like', '%'.substr(str_replace('Régimen', '', $data['Rgimen']), 1).'%')->first();
        $data['codigo_regimen'] = $codigo_regimen->codigo ?? null;

        return response()->json($data);
    }

    public function getVendedor(){
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        $vendedor = Usuario::select('id', 'nombre_completo')
            ->where([['admin', 1], ['estatus', 1], ['autofacturador', 1],['base_autofacturador', $user->base_autofacturador]])->get();

        return \response()->json($vendedor);
    }

    public function getCFDITimbrado($tipo){
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        switch($tipo){
            case 'Emisor':
                $response = Cfdi::selectRaw('emisor_nombre as text, emisor_nombre as code')
                    ->whereIn('estado', [CFDI::CDFI_FACTURADO, Cfdi::CFDI_CANCELADO])
                    ->whereRaw('YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))')
                    ->groupBy('emisor_nombre')->get();
                break;
            case 'Receptor':
                $response = Cfdi::selectRaw('receptor_nombre as text, receptor_nombre as code')
                    ->whereIn('estado', [CFDI::CDFI_FACTURADO, Cfdi::CFDI_CANCELADO])
                    ->whereRaw('YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))')
                    ->groupBy('receptor_nombre')->get();
                break;
            case 'Cliente':
                $response = Usuario::selectRaw('id as code, nombre_completo as text')
                    ->where('autofacturador', 1)
                    ->where('base_autofacturador',Auth::user()->base_autofacturador)->get();
                break;
            case 'Vendedor':
                $response = User::selectRaw('id as code, nombre_completo as text')
                    ->where([['admin', 1], ['estatus', 1], ['autofacturador', 1], ['timbrar', 0],['base_autofacturador', $user->base_autofacturador]])->get();
                break;
        }
        return response()->json($response);
    }

    public function downloadCfdiXml(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        $cfdis = Cfdi::selectRaw('cfdi.*, vendedores.nombre, singh.usuarios.nombre_completo')
            ->whereBetween('fecha_timbre', [$request->date_inicio, $request->date_final])
            ->leftJoin('singh.usuarios', 'cfdi.usuario_id', '=', 'singh.usuarios.id')
            ->leftJoin('vendedores', 'vendedores.id', '=', 'singh.usuarios.id_vendedor');

        if(isset($request->timbre_Emisor) && count($request->timbre_Emisor))
            $cfdis->whereIn('emisor_nombre', $request->timbre_Emisor);

        if(isset($request->timbre_Receptor) && count($request->timbre_Receptor))
            $cfdis->WhereIn('receptor_nombre', $request->timbre_Receptor);

        if(isset($request->timbre_Cliente) && count($request->timbre_Cliente))
            $cfdis->whereIn('usuario_id', $request->timbre_Cliente);

        if(isset($request->timbre_Vendedor) && count($request->timbre_Vendedor))
            $cfdis->whereIn('singh.usuarios.id_vendedor', $request->timbre_Vendedor);

        if($request->canceladas_xls)
            $cfdis->whereIn('estado', [Cfdi::CFDI_CANCELADO, Cfdi::CDFI_FACTURADO]);
        else
            $cfdis->where('estado', Cfdi::CDFI_FACTURADO);

        $cfdis = $cfdis->get();

        if(count($cfdis)){
            $fecha = new \DateTime();
            $zip_file = storage_path('app/public/trash/' . $fecha->format('dmYHis') . '.zip');

            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE);

            foreach ($cfdis as $key => $cfdi) {
                $path = storage_path('app/public/'.Auth::user()->clientes->base.'/' . $cfdi->usuario_id . '/facturas');
                $xml = $path . '/autofactura-' . $cfdi->id . '.xml';
                $zip->addFile($xml, $cfdi->folio.'_xml.xml');
                $pdf = $path . '/autofactura-' . $cfdi->id . '.pdf';
                $zip->addFile($pdf, $cfdi->folio.'_pdf.pdf');
            }

            $zip->close();

            return response()->download($zip_file);
        }else{
            return redirect()->back()->withErrors([
                'cfdi_dato' => 'No se encontro ningun dato solicitado.'
            ]);
        }
    }
}
