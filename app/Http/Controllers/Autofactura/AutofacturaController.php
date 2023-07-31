<?php

namespace App\Http\Controllers\Autofactura;

use App\Http\Controllers\Controller;
use App\Models\Autofacturador\CatEtiquetasEmpEmisoras;
use App\Models\Autofacturador\CatFormasPagos;
use App\Models\Autofacturador\CatMetodosPagos;
use App\Models\Autofacturador\CatProductosServicios;
use App\Models\Autofacturador\CatTipoRelacion;
use App\Models\Autofacturador\CatUnidades;
use App\Models\Autofacturador\Cfdi;
use App\Models\Autofacturador\CfdiConceptos;
use App\Models\Autofacturador\ComprobantesPagos;
use App\Models\Autofacturador\DatosFiscalesReceptor;
use App\Models\Autofacturador\EmpresasEmisoras;
use App\Models\Autofacturador\RegimenFiscal;
use App\Models\Autofacturador\Retornos;
use App\Models\Autofacturador\RetornosUsuarios;
use App\Models\Autofacturador\UsoCFDI;
use App\Models\Autofacturador\UsuariosEmpresas;
use App\Models\Autofacturador\Vendedores;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AutofacturaController extends Controller
{

    public function index()
    {
        return view('autofacturador.index');
    }

    public function nuevaFactura()
    {
        $catalogos = [
            'uso_cfdi' => $this->getCatalogos('uso-cfdi'),
            'empresas_emisoras' => $this->getCatalogos('empresas-emisoras'),
            'productos_servicios' => $this->getCatalogos('productos-servicios'),
            'unidades' => $this->getCatalogos('unidades'),
            'receptores' => $this->getCatalogos('datos-fiscales-receptor'),
            'metodos_pago' => $this->getCatalogos('cat-metodos-pago'),
            'formas_pago' => $this->getCatalogos('cat-formas-pago'),
            'clientes' => $this->getCatalogos('cat-clientes'),
            'tipo_relacion' => $this->getCatalogos('cat-tipo-relacion'),
            'cfdi_facturado' => $this->getCatalogos('cat-cfdi-facturado'),
        ];
        return view('autofacturador.nuevaFactura', ['catalogos' => $catalogos]);
    }

    public function getCatalogos($catalogo)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        switch ($catalogo) {
            case 'uso-cfdi':
                $response = UsoCFDI::select('clave', DB::raw('CONCAT(clave, " - ", uso_cfdi) as text'))->get();
                break;
            case 'regimen-fiscal':
                $response = RegimenFiscal::select(DB::raw('codigo as clave'), DB::raw('CONCAT(codigo, " - ", regimen) as text'))->get();
                break;
            case 'empresas-emisoras':
                $empresas_asignadas = UsuariosEmpresas::select('id_empresa')->where('id_usuario', $user->id)->get();
                $ids_array = [];
                foreach ($empresas_asignadas as $emp)
                    $ids_array[] = $emp->id_empresa;

                $response = CatEtiquetasEmpEmisoras::select('cat_etiquetas_emisoras.*')
                    ->join('empresas_emisoras', 'empresas_emisoras.id_cat_etiqueta_emisora', 'cat_etiquetas_emisoras.id')
                    ->join('usuarios_empresas_emisoras', function ($join) use ($user) {
                        $join->on('usuarios_empresas_emisoras.id_empresa', 'empresas_emisoras.id')
                            ->on('usuarios_empresas_emisoras.id_usuario', DB::raw($user->id));
                    })
                    ->with(['empresas' => function ($query) use ($ids_array) {
                        $query->whereIn('id', $ids_array);
                    }])->groupBy('id')->get();
                break;
            case 'productos-servicios':
                $response = CatProductosServicios::select('clave', DB::raw('CONCAT(clave, " - ", descripcion) as text'))->get();
                break;
            case 'unidades':
                $response = CatUnidades::select('clave', DB::raw('CONCAT(clave, " - ", nombre) as text'))->get();
                break;
            case 'fiscal-data-user':
                $response = DatosFiscalesReceptor::where('usuario_id', $user->id)->first();
                break;
            case 'empresas-emisoras-edit':
                $empresas = EmpresasEmisoras::selectRaw('empresas_emisoras.*, cat_etiquetas_emisoras.etiqueta')
                    ->leftJoin('cat_etiquetas_emisoras', 'empresas_emisoras.id_cat_etiqueta_emisora', '=', 'cat_etiquetas_emisoras.id')
                    ->get()->toArray();

                $response = ['data' => $empresas];
                break;
            case 'etiqueta-emisora':
                $response = CatEtiquetasEmpEmisoras::selectRaw('id as clave, etiqueta as text')->get();
                break;
            case 'cat-etiquetas-emisoras':
                $etiquetas = CatEtiquetasEmpEmisoras::all();
                $response = ['data' => $etiquetas];
                break;
            case 'cat-productos-servicios':
                $productoServicios = CatProductosServicios::all();
                $response = ['data' => $productoServicios];
                break;
            case 'cat-regimen-fiscal':
                $regimenFiscal = RegimenFiscal::all();
                $response = ['data' => $regimenFiscal];
                break;
            case 'cat-unidades':
                $unidades = CatUnidades::all();
                $response = ['data' => $unidades];
                break;
            case 'cat-uso-cfdi':
                $uso_cfdi = UsoCFDI::all();
                $response = ['data' => $uso_cfdi];
                break;
            case 'cat-metodos-pago':
                $cat_metodos_pago = CatMetodosPagos::select(DB::raw('metodo as clave'), DB::raw('CONCAT(metodo, " - ", descripcion) as text'))->get();
                $response = $cat_metodos_pago;
                break;
            case 'cat-formas-pago':
                $cat_formas_pago = CatFormasPagos::select('clave', DB::raw('CONCAT(clave, " - ", descripcion) as text'))->get();
                $response = $cat_formas_pago;
                break;
            case 'datos-fiscales-receptor':
                $datos_fiscales_receptor = DatosFiscalesReceptor::select(DB::raw('id as clave'), DB::raw('razon_social as text'))->where('usuario_id', $user->id)->get();
                $response = $datos_fiscales_receptor;
                break;
            case 'datos-fiscales-receptor-table':
                $datos_fiscales_receptor = DatosFiscalesReceptor::where('usuario_id', $user->id)->get();
                $response = ['data' => $datos_fiscales_receptor];
                break;
            case 'datos-retorno-usuario':
                $datos_retorno = RetornosUsuarios::where('usuario_id', Auth::user()->id)->get();
                $response = $datos_retorno;
                break;
            case 'datos-retorno-usuario-table':
                $datos_retorno = RetornosUsuarios::where('usuario_id', Auth::user()->id)->get();
                $response = ['data' => $datos_retorno];
                break;
            case 'vendedor':
                $datos_vendedor = Vendedores::all();
                $response = ['data' => $datos_vendedor];
                break;
            case 'cat-tipo-relacion':
                $tipo_relacion = CatTipoRelacion::select('clave', DB::raw('CONCAT(clave, " - ", descripcion) as text'))->get();
                $response = $tipo_relacion;
                break;
            case 'cat-cfdi-facturado':
                $cfdi_facturado = Cfdi::select(DB::raw('id as clave'), DB::raw('CONCAT(folio, " - ", serie) as text'))
                                    ->where('estado',Cfdi::CDFI_FACTURADO)->get();
                $response = $cfdi_facturado;
                break;
            case 'cat-clientes':
                $clientes = Usuario::select(DB::raw('id as clave'), DB::raw('nombre_completo as text'))
                    ->where('autofacturador', 1)
                    ->where('admin', 0)
                    ->where('estatus', 1)
                    ->where('base_autofacturador', Auth::user()->clientes->id);

                if(Auth::user()->clientes->id == 5)
                    $clientes->orWhereIn('id',[1655,1654,1653,1657,1656]);
                $response = $clientes->get();
                break;
            default:
                $response = [];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        if (isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador) {
            $user = Usuario::find($request['clientes']);
        }

        $emisor = EmpresasEmisoras::find($request['empresa-emisora']);
        $receptor = DatosFiscalesReceptor::find($request['datos_receptor']);

        $cfdi = new Cfdi;
        $cfdi->usuario_id = $user->id;
        $cfdi->id_emp_emsora = $emisor->id;
        $cfdi->subtotal = $request['subtotal'];
        $cfdi->total = $request['total'];
        $cfdi->lugar_expedicion = $emisor->cp;
        $cfdi->emisor_rfc = $emisor->rfc;
        $cfdi->emisor_nombre = $emisor->razon_social;
        $cfdi->emisor_reg_fiscal = $emisor->regimen_fiscal;
        $cfdi->receptor_rfc = $receptor->rfc;
        $cfdi->receptor_nombre = $receptor->razon_social;
        $cfdi->receptor_uso_cfdi = $request['uso_cfdi'];
        $cfdi->receptor_regimen_fiscal = $receptor->regimen_fiscal;
        $cfdi->receptor_domicilio = $receptor->cp;
        $cfdi->importe = $request['subtotal'];
        $cfdi->base = $request['subtotal'];
        $cfdi->metodo_pago = $request['metodo-pago'];
        $cfdi->forma_pago = $request['forma-pago'];
        $cfdi->estado = Cfdi::CFDI_SOLCITADO;
        $cfdi->receptor_id = $receptor->id;
        $cfdi->folio = date('YmdHis');
        $cfdi->comision = $user->comision;
        $cfdi->pagar_del = $user->pagar_del;
        $cfdi->tasa_cuota = $request['iva'];
        $cfdi->fecha = $request['modificar-fecha'] ? $request['fecha_emision'].'T'.$request['hora_emision'] : date("Y-m-d\TH:i:s");
        $cfdi->modificar_fecha = $request['modificar-fecha'];
        $cfdi->tipo_relacion= $request['tipo-relacion'] ? $request['tipo-relacion'] : '';
        $cfdi->cfdi_relacionado= $request['cfdi-facturado'] ? $request['cfdi-facturado'] : '';
        $cfdi->save();
        $cfdi->serie = $cfdi->id . '0' . $cfdi->usuario_id;
        $cfdi->save();

        foreach ($request['descripcion'] as $key => $descript) {
            $cfdi_conceptos = new CfdiConceptos();
            $cfdi_conceptos->id_cfdi = $cfdi->id;
            $cfdi_conceptos->clave_prod = $request['clave-producto'][$key];
            $cfdi_conceptos->cantidad = $request['cantidad'][$key];
            $cfdi_conceptos->clave_unidad = $request['clave-unidad'][$key];
            $cfdi_conceptos->descripcion = $request['descripcion'][$key];
            $cfdi_conceptos->valor_unitario = $request['valor-unitario'][$key];
            $base = $request['cantidad'][$key] * $request['valor-unitario'][$key];
            $cfdi_conceptos->taza_cuota = $request['iva'];
            $cfdi_conceptos->base = $base;
            $cfdi_conceptos->importe = $base * $request['iva'];
            $cfdi_conceptos->save();
        }

        logAutofacturador(Auth::user()->id, Cfdi::CFDI_SOLCITADO, $cfdi->id);

        return redirect()->route('autofacturador.index')->withSuccess('Orden de compra en espera de aprobación.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        if (isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador) {
            $user = Usuario::find($request['clientes']);
        }

        $emisor = EmpresasEmisoras::find($request['empresa-emisora']);
        $receptor = DatosFiscalesReceptor::find($request['datos_receptor']);

        $cfdi = Cfdi::findOrFail($request['id']);
        $cfdi->usuario_id = $user->id;
        $cfdi->id_emp_emsora = $emisor->id;
        $cfdi->subtotal = $request['subtotal'];
        $cfdi->total = $request['total'];
        $cfdi->lugar_expedicion = $emisor->cp;
        $cfdi->emisor_rfc = $emisor->rfc;
        $cfdi->emisor_nombre = $emisor->razon_social;
        $cfdi->emisor_reg_fiscal = $emisor->regimen_fiscal;
        $cfdi->receptor_rfc = $receptor->rfc;
        $cfdi->receptor_nombre = $receptor->razon_social;
        $cfdi->receptor_uso_cfdi = $request['uso_cfdi'];
        $cfdi->receptor_regimen_fiscal = $receptor->regimen_fiscal;
        $cfdi->receptor_domicilio = $receptor->cp;
        $cfdi->importe = $request['subtotal'];
        $cfdi->base = $request['subtotal'];
        $cfdi->metodo_pago = $request['metodo-pago'];
        $cfdi->forma_pago = $request['forma-pago'];
        $cfdi->estado = Cfdi::CFDI_SOLCITADO;
        $cfdi->receptor_id = $receptor->id;
        $cfdi->comision = $user->comision;
        $cfdi->pagar_del = $user->pagar_del;
        $cfdi->tasa_cuota = $request['iva'];
        $cfdi->fecha = $request['modificar-fecha'] ? $request['fecha_emision'].'T'.$request['hora_emision'] : date("Y-m-d\TH:i:s");
        $cfdi->modificar_fecha = $request['modificar-fecha'];
        $cfdi->save();

        $where_in_conceptos = [];
        foreach ($request['descripcion'] as $key => $item) {
            $cfdi_conceptos = CfdiConceptos::updateOrCreate(
                ['id' => $request['id_concepto'][$key]],
                [
                    'id_cfdi' => $cfdi->id,
                    'clave_prod' => $request['clave-producto'][$key],
                    'cantidad' => $request['cantidad'][$key],
                    'clave_unidad' => $request['clave-unidad'][$key],
                    'descripcion' => $request['descripcion'][$key],
                    'valor_unitario' => $request['valor-unitario'][$key],
                    'base' => $request['cantidad'][$key] * $request['valor-unitario'][$key],
                    'importe' => $request['cantidad'][$key] * $request['valor-unitario'][$key] * $request['iva'],
                ]
            );
            array_push($where_in_conceptos, $cfdi_conceptos->id);
        }
        CfdiConceptos::where('id_cfdi', $request['id'])->whereNotIn('id', $where_in_conceptos)->delete();

        logAutofacturador(Auth::user()->id, Cfdi::CFDI_SOLCITADO, $cfdi->id);

        return redirect()->route('autofacturador.index')->withSuccess('Orden de compra en espera de aprobación..');
    }

    public function getDatoFiscal($id)
    {
        cambiarBase(Auth::user()->clientes->base);
        $datos_fiscales = DatosFiscalesReceptor::where('id', $id)->first();

        return response()->json($datos_fiscales);
    }

    public function getInvoices()
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        $cfdi = Cfdi::select('cfdi.id', 'cfdi.folio', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'cfdi.observaciones', 'cfdi.contrato_nombre')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora');

        if (!$user->admin)
            $cfdi = $cfdi->where('usuario_id', $user->id);
        else {
            $cfdi = $cfdi->where('estado', Cfdi::PROCESO_CANCELADO);
        }

        $cfdi = $cfdi->get();

        return response()->json(['data' => $cfdi]);
    }

    public function getInvoicesPendientesRechazados()
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        $cfdi = Cfdi::select('cfdi.id', 'cfdi.folio', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'cfdi.observaciones', 'cfdi.contrato_nombre')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora');

        if (!$user->admin)
            $cfdi = $cfdi->where('usuario_id', $user->id);
        else
            $cfdi = $cfdi->whereIn('estado', [Cfdi::CFDI_SOLCITADO, Cfdi::PROCESO_CANCELADO]);

        $cfdi = $cfdi->get();

        return response()->json(['data' => $cfdi]);
    }

    public function getInvoicesCancelados()
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::select('cfdi.id', 'cfdi.folio', 'empresas_emisoras.razon_social', 'cfdi.receptor_nombre', 'cfdi.updated_at', 'cfdi.total', 'cfdi.estado', 'cfdi.observaciones', 'cfdi.contrato_nombre')
            ->leftJoin('empresas_emisoras', 'empresas_emisoras.id', '=', 'cfdi.id_emp_emsora');

        if (Auth::user()->admin)
            $cfdi = $cfdi->whereIn('estado', [Cfdi::PROCESO_CANCELADO, Cfdi::CFDI_SOLCITADO, Cfdi::CFDI_APROBADO]);

        $cfdi = $cfdi->get();
        return response()->json(['data' => $cfdi]);
    }

    public function storeDatosFiscales(Request $request)
    {
        $request->request->remove('_token');
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $datos = DatosFiscalesReceptor::updateOrCreate([
            'id' => $request->id,
            'usuario_id' => $user->id
        ], $request->toArray());

        $datos->save();

        return response()->json(['status' => true]);
    }

    public function cancelarProceso(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::find($request->id);
        $cfdi->estado = Cfdi::PROCESO_CANCELADO;
        $cfdi->save();

        logAutofacturador(Auth::user()->id, Cfdi::PROCESO_CANCELADO, $cfdi->id);

        return response()->json(['status' => true]);
    }

    public function eliminarOrden(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::where('id', $request->id)->delete();

        return response()->json(['status' => true]);
    }

    public function downloadContrato($id)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $cfdi = Cfdi::find($id);

        if ($user->id == $cfdi->usuario_id) {
            $file = Storage::disk('public')->get('autofacturas/' . $cfdi->usuario_id . '/contratos/' . $cfdi->contrato_nombre);

            return (new Response($file, 200))->header('Content-Type', 'application/pdf');
        }
        return 'Acceso no permitido';
    }

    public function downloadZipCfdi($id)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $cfdi = Cfdi::find($id);

        if (($user->admin || ($user->id == $cfdi->usuario_id)) && ($cfdi->estado == Cfdi::CDFI_FACTURADO || $cfdi->estado == Cfdi::CFDI_CANCELADO)) {
            $fecha = new \DateTime();
            $zip_file = storage_path('app/public/trash/' . 'OC-' . $cfdi->folio . '.zip');

            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE);

            $path = storage_path('app/public/' . $user->clientes->base . '/' . $cfdi->usuario_id . '/facturas');

            $pdf = $path . '/autofactura-' . $cfdi->id . '.pdf';
            $zip->addFile($pdf, 'OC_'.$cfdi->folio.'-'.$cfdi->serie.'.pdf');
            $xml = $path . '/autofactura-' . $cfdi->id . '.xml';
            $zip->addFile($xml, 'OC_'.$cfdi->folio.'-'.$cfdi->serie.'.xml');

            $zip->close();

            return response()->download($zip_file);
        }
        return "No se encontro el documento solicitado.";
    }

    public function downloadzipCfdiPago($id)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $comprobante_pago = ComprobantesPagos::where('id', $id)->first();

        if (($user->admin || ($user->id == $comprobante_pago->getCFDI->usuario_id)) &&
            ($comprobante_pago->getCFDI->estado == ComprobantesPagos::PAGO_FACTURADO || $comprobante_pago->getCFDI->estado == ComprobantesPagos::PAGO_CANCELADO)) {
            $fecha = new \DateTime();
            $zip_file = storage_path('app/public/trash/' . 'P_OC-' . $comprobante_pago->getCFDI->folio . '.zip');

            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE);

            $path = storage_path('app/public/' . $user->clientes->base . '/' . $comprobante_pago->getCFDI->usuario_id . '/pagos');

            $pdf = $path . '/autofactura-pago-' . $comprobante_pago->getCFDI->id . '-' . $comprobante_pago->num_pago . '.pdf';
            $zip->addFile($pdf, 'cfdi_pago_pdf.pdf');
            $xml = $path . '/autofactura-pago-' . $comprobante_pago->getCFDI->id . '-' . $comprobante_pago->num_pago . '.xml';
            $zip->addFile($xml, 'cfdi_pago_xml.xml');

            $zip->close();

            return response()->download($zip_file);
        }
        return "No se encontro el documento solicitado.";
    }

    public function storeComprobante(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $cfdi = Cfdi::find($request->id_cfdi);
        $ultimo_complemento = $cfdi->ultimoComplementoPago;

        $pag_ant = 1;
        if ($ultimo_complemento)
            $pag_ant = $ultimo_complemento->num_pago + 1;

        $nombre_file = '';

        if ($request->docu_comprobante) {
            $nombre_file = $request->id_cfdi . '_' . $pag_ant . '_comprobante_' . $pag_ant . '.' . pathinfo($request->tipo_archivo, PATHINFO_EXTENSION);
            $request->file('docu_comprobante')->storeAs(Auth::user()->clientes->base . '/' . $cfdi->usuario_id . '/' . 'comprobantes_pagos', $nombre_file, 'public');
        }

        if(isset($request->id_pago)){
            $comprobante_array = [
                'tipo_pago' => $request->tipo_pago,
                'cantidad' => $request->cantidad,
                'fecha_pago' => $request->fecha_pago.'T'.$request->hora_pago,
                'nombre_comprobante' => $nombre_file
            ];

            if($nombre_file=='')
                unset($comprobante_array['nombre_comprobante']);

        }else{
            $comprobante_array = [
                'id_cfdi' => $request->id_cfdi,
                'tipo_pago' => $request->tipo_pago,
                'cantidad' => $request->cantidad,
                'num_pago' => $pag_ant,
                'confirmado' => 0,
                'fecha_pago' => $request->fecha_pago.'T'.$request->hora_pago,
                'nombre_comprobante' => $nombre_file
            ];
        }

        $comprobante=ComprobantesPagos::updateOrCreate([
            'id' => $request->id_pago,
            'id_cfdi' => $request->id_cfdi
        ],$comprobante_array);

        $comprobante->save();

        return $request;
    }

    public function getComprobante(Request $request){
        cambiarBase(Auth::user()->clientes->base);
        return ComprobantesPagos::find($request->id);
    }

    public function showComprobante(Request $request)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);

        $comprobante = ComprobantesPagos::where('id_cfdi', $request->id)->with('formaPago')->get();

        return response()->json(['data' => $comprobante]);
    }

    public function downloadComprobante(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $comprobante = ComprobantesPagos::with(['getCFDI'])->find($request->id);
        $cfdi = json_decode($comprobante, true)['get_c_f_d_i'];
        if (isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador) {
            $file = storage_path('app/public/' . Auth::user()->clientes->base . '/' . $cfdi['usuario_id'] . '/comprobantes_pagos/' . $comprobante->nombre_comprobante);
            return response()->download($file);
        } else {
            $file = storage_path('app/public/' . Auth::user()->clientes->base . '/' . Auth::user()->id . '/comprobantes_pagos/' . $comprobante->nombre_comprobante);
            return response()->download($file);
        }
    }

    public function storeRetornoUsuario(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $retornosUsuarios = RetornosUsuarios::updateOrCreate(
            ['id' => $request->id_user_retorno],
            [
                'usuario_id' => Auth::user()->id,
                'nombre' => $request->nombre,
                'banco' => $request->banco,
                'num_cuenta' => $request->num_cuenta,
                'clave_interbancaria' => $request->clave_interbancaria,
            ]);


        return $retornosUsuarios;
    }

    public function showRetornoUsuario(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $retornoUsurio = RetornosUsuarios::find($request->id);
        return $retornoUsurio;
    }

    public function storeRetorno(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        foreach ($request->datos_retorno as $index => $id_retorno) {
            $retornos = new Retornos();
            $retornos->id_cfdi = $request->id_cfdi_retorno;
            $retornos->id_retorno_usuario = $id_retorno;
            $retornos->cantidad = $request->cantidad[$index];
            $retornos->retorno_tipo = $request->retorno_tipo[$index];
            $retornos->save();
        }
        return $retornos;
    }

    public function showRetorno(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $retornos = Retornos::where('id_cfdi', $request->id)->with(['usuarios'])->get();

        return response()->json(['data' => $retornos]);
    }

    public function getCfdi($id)
    {
        $user = Auth::user();
        cambiarBase($user->clientes->base);
        $cfdi = Cfdi::where('id', $id)->with(['emisora', 'conceptos', 'receptor', 'logs','comprobantesPago','relacionCfdi'])->first();

        return response()->json($cfdi);
    }

    public function cfdi_pdf(Request $request)
    {
        cambiarBase(Auth::user()->clientes->base);
        $data = EmpresasEmisoras::where('id', $request->id)->first();
        //dd($data->logo_base64);
        return view('autofacturador.documentos.cfdi_pdf_img', ['data' => $data]);
    }

}
