<?php

namespace App\Http\Controllers\cfdiUtils;

use App\Http\Controllers\Controller;
use App\Models\Autofacturador\Cfdi;
use App\Models\Autofacturador\ComprobantesPagos;
use App\Models\Autofacturador\EmpresasEmisoras;
use Barryvdh\DomPDF\Facade as PDF;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\OpenSSL\OpenSSL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CFDIUtils extends Controller
{

    protected $database;
    protected $xml;
    protected $cfdi_model;
    protected $comprobante_pago_model;
    protected $xmlPath;
    protected $keyPemPass;
    protected $documents_path;
    protected $username_soap;
    protected $password_soap;
    protected $cer_path;
    protected $key_path;
    protected $trash_path;
    protected $response = [
        'status' => true,
        'mensaje' => '',
    ];

    function __construct(Cfdi $model_cfdi)
    {
        $this->database = Auth::user()->clientes->base;
        $this->cfdi_model = $model_cfdi;
        $this->trash_path = 'public/trash/';
        $this->documents_path = storage_path('app/public/' . $this->database . '/' . $this->cfdi_model->usuario_id . '/facturas/');
        $this->documents_path_pago = storage_path('app/public/' . $this->database . '/' . $this->cfdi_model->usuario_id . '/pagos/');
        $this->username_soap = base64_decode($model_cfdi->emisora->username_soap);
        $this->password_soap = base64_decode($model_cfdi->emisora->password_soap);
        $this->keyPemPass = $this->password_soap;
        $this->cer_path = storage_path('app/public/' . $this->database . '/emisoras/' . $this->cfdi_model->emisora->id . '/cer.pem');
        $this->key_path = storage_path('app/public/' . $this->database . '/emisoras/' . $this->cfdi_model->emisora->id . '/key.pem');
    }

    public static function convertKeyToPem($id, $password)
    {
        $database = Auth::user()->clientes->base;
        cambiarBase($database);
        $model_empresa = EmpresasEmisoras::find($id);
        $keyDerFile = storage_path('app/public/trash/' . $id . '-key.key');
        $keyPemPath = storage_path('app/public/' . $database . '/emisoras/' . $id . '/');
        $keyPemFile = $keyPemPath . 'key.pem';
        $keyPemPass = base64_decode($model_empresa->password_soap);

        $keyDerPass = $password;
        $openssl = new OpenSSL();

        if (!file_exists($keyPemPath))
            mkdir($keyPemPath, 0777, true);

        if (file_exists($keyPemFile)) {
            File::delete($keyPemFile);
            set_time_limit(3);
        }

        // convertir la llave original DER a formato PEM con nueva contraseña, guardar en $keyPemFile
        // lo mismo que los dos pasos anteriores pero en una llamada
        $openssl->derKeyProtect($keyDerFile, $keyDerPass, $keyPemFile, $keyPemPass);

        return $openssl;
    }

    public static function convertCetToPem($id)
    {
        $database = Auth::user()->clientes->base;
        $cerFile = storage_path('app/public/trash/' . $id . '-cer.cer');
        $cerPemPath = storage_path('app/public/' . $database . '/emisoras/' . $id . '/');
        $cerPemFile = $cerPemPath . 'cer.pem';
        $openssl = new OpenSSL();

        if (!file_exists($cerPemPath))
            mkdir($cerPemPath, 0777, true);

        if (file_exists($cerPemFile)) {
            File::delete($cerPemFile);
            set_time_limit(3);
        }

        // guardar el certificado en PEM a partir del archivo DER usando openssl
        $openssl->derCerConvert($cerFile, $cerPemFile);

        return $openssl;
    }

    public function crearCDFI()
    {
        $certificado = new Certificado($this->cer_path);

        $key = 'file://' . $this->key_path;
        $document_save = $this->documents_path . 'autofactura-' . $this->cfdi_model->id . '.xml';
        $this->xmlPath = $document_save;

        $this->cfdi_model->emisor_certificado = $certificado->getSerial();

        if(!$this->cfdi_model->modificar_fecha)
            $this->cfdi_model->fecha = date("Y-m-d\TH:i:s");

        $comprobanteAtributos = [
            'Serie' => $this->cfdi_model->serie,
            'Folio' => $this->cfdi_model->folio,
            'Fecha' => $this->cfdi_model->fecha,
            'LugarExpedicion' => $this->cfdi_model->lugar_expedicion,
            'TipoDeComprobante' => $this->cfdi_model->tipo_comprobante,
            'MetodoPago' => $this->cfdi_model->metodo_pago,
            'Moneda' => $this->cfdi_model->moneda,
            'TipoCambio' => $this->cfdi_model->tipo_cambio,
            'Exportacion' => '01',
            'FormaPago' => $this->cfdi_model->forma_pago,
        ];

        $creator = new CfdiCreator40($comprobanteAtributos, $certificado);

        $comprobante = $creator->comprobante();

        //en caso de relacionar el CFDI
        if(isset($this->cfdi_model->relacionCfdi)){
            $comprobante->addCfdiRelacionados([
                'TipoRelacion'=> $this->cfdi_model->tipo_relacion
            ])->addCfdiRelacionado([
                'UUID' => $this->cfdi_model->relacionCfdi->uuid
            ]);
        }

        // No agrego (aunque puedo) el Rfc y Nombre porque uso los que están establecidos en el certificado
        $comprobante->addEmisor([
            'RegimenFiscal' => $this->cfdi_model->emisora->regimen_fiscal,
            'Nombre' => $this->cfdi_model->emisora->razon_social,
        ]);

        $comprobante->addReceptor([
            'Rfc' => $this->cfdi_model->receptor_rfc,
            'Nombre' => $this->cfdi_model->receptor_nombre,
            'UsoCFDI' => $this->cfdi_model->receptor_uso_cfdi,
            'RegimenFiscalReceptor' => $this->cfdi_model->receptor_regimen_fiscal,
            'DomicilioFiscalReceptor' => $this->cfdi_model->receptor_domicilio
        ]);

        foreach ($this->cfdi_model->conceptos as $concepto) {
            $comprobante->addConcepto([
                'ClaveProdServ' => $concepto->clave_prod,
                'Cantidad' => $concepto->cantidad,
                'ClaveUnidad' => $concepto->clave_unidad,
                'Descripcion' => $concepto->descripcion,
                'ValorUnitario' => $concepto->valor_unitario,
                'Importe' => $concepto->base,
                'ObjetoImp' => $concepto->objeto_impuesto,
            ])->addTraslado([
                'Base' => $concepto->base,
                'Impuesto' => $concepto->impuesto,
                'TipoFactor' => $concepto->tipo_factor,
                'TasaOCuota' => $concepto->taza_cuota,
                'Importe' => $concepto->importe
            ]);

        }

        // método de ayuda para establecer las sumas del comprobante e impuestos
        // con base en la suma de los conceptos y la agrupación de sus impuestos
        $creator->addSumasConceptos(null, 2);

        // método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privada)
        $creator->addSello($key, $this->keyPemPass);
        $this->cfdi_model->sello_cdf = $creator->comprobante()->attributes()->get('Sello');

        // método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
        $creator->moveSatDefinitionsToComprobante();

        $this->cfdi_model->cadena_origen = $creator->buildCadenaDeOrigen();
        $this->cfdi_model->save();

        if (!file_exists($this->documents_path))
            mkdir($this->documents_path, 0777, true);

        if (file_exists($document_save)) {
            File::delete($document_save);
            set_time_limit(3);
        }

        // método de ayuda para generar el xml y guardar los contenidos en un archivo
        $creator->saveXml($document_save);

        // método de ayuda para generar el xml y retornarlo como un string
        $this->xml = $creator->asXml();

        return $this->xml;
    }

    public function crearPago()
    {
        $importe_saldo_anterior = 0;

        $this->comprobante_pago_model = ComprobantesPagos::find($this->cfdi_model->comprobantePago->id);
        $pago = $this->cfdi_model->comprobantePago;

        if (count($this->cfdi_model->comprobantesPago)){
            foreach ($this->cfdi_model->comprobantesPago as $pagado) {
                $importe_saldo_anterior += $pagado->cantidad;
            }
            $importe_saldo_anterior=$this->cfdi_model->total-$importe_saldo_anterior;
        }else
            $importe_saldo_anterior = $this->cfdi_model->total;

        $folio = $this->cfdi_model->folio . '-' . $this->cfdi_model->comprobantePago->num_pago;
        $importe_saldo_insoluto=$importe_saldo_anterior-$pago->cantidad;
        $this->comprobante_pago_model->imp_saldo_anterior=number_format($importe_saldo_anterior, 2, '.', '');
        $this->comprobante_pago_model->imp_saldo_insoluto=number_format($importe_saldo_insoluto, 2, '.', '');


        $this->comprobante_pago_model->folio = $folio;

        $certificado = new Certificado($this->cer_path);

        $key = 'file://' . $this->key_path;
        $document_save = $this->documents_path_pago . 'autofactura-pago-' . $this->cfdi_model->id . '-' . $this->cfdi_model->comprobantePago->num_pago . '.xml';
        $this->xmlPath = $document_save;

        $comprobanteAtributos = [
            'Folio' => $folio,
            'Fecha' => date("Y-m-d\TH:i:s"),
            'LugarExpedicion' => $this->cfdi_model->lugar_expedicion,
            'TipoDeComprobante' => 'P',
            'Moneda' => 'XXX',
            'Exportacion' => '01',
            "SubTotal" => 0,
            "Total" => 0
        ];

        $creator = new CfdiCreator40($comprobanteAtributos, $certificado);

        $comprobante = $creator->comprobante();

        // No agrego (aunque puedo) el Rfc y Nombre porque uso los que están establecidos en el certificado
        $comprobante->addEmisor([
            'RegimenFiscal' => $this->cfdi_model->emisora->regimen_fiscal,
            'Nombre' => $this->cfdi_model->emisora->razon_social,
        ]);

        $comprobante->addReceptor([
            'Rfc' => $this->cfdi_model->receptor->rfc,
            'Nombre' => $this->cfdi_model->receptor->razon_social,
            'UsoCFDI' => "CP01",
            'RegimenFiscalReceptor' => $this->cfdi_model->receptor->regimen_fiscal,
            'DomicilioFiscalReceptor' => $this->cfdi_model->receptor->cp
        ]);


        $comprobante->addConcepto([
            'ClaveProdServ' => "84111506",
            'Cantidad' => "1",
            'ClaveUnidad' => "ACT",
            'Descripcion' => "Pago",
            'ValorUnitario' => '0',
            'Importe' => '0',
            'ObjetoImp' => '01',
        ]);

        // Creación del nodo de pago20
        $pagos = new \CfdiUtils\Nodes\Node(
            'pago20:Pagos', // nombre del elemento raíz
            [ // nodos obligatorios de XML y del nodo
                "xmlns:pago20" => "http://www.sat.gob.mx/Pagos20",
                'xsi:schemaLocation' => 'http://www.sat.gob.mx/Pagos20'
                    . ' http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd',
                'Version' => '2.0',
            ]
        );

        $pagos->addChild(new \CfdiUtils\Nodes\Node('pago20:Totales'));

        $pagos->addChild(new \CfdiUtils\Nodes\Node('pago20:Pago', [
            "FechaPago" => $pago->fecha_pago,
            "FormaDePagoP" => $pago->tipo_pago,
            "MonedaP" => "MXN",
            "Monto" => number_format($pago->cantidad, 2, '.', ''),
            "TipoCambioP" => "1"
        ]))->addChild(new \CfdiUtils\Nodes\Node('pago20:DoctoRelacionado', [
            "IdDocumento" => $this->cfdi_model->uuid,
            "MonedaDR" => "MXN",
            "NumParcialidad" => $pago->num_pago,
            "ImpSaldoAnt" => number_format($importe_saldo_anterior, 2, '.', ''),
            "ImpPagado" => number_format($pago->cantidad, 2, '.', ''),
            "ImpSaldoInsoluto" => number_format($importe_saldo_insoluto, 2, '.', ''), //le falta por pagar
            "ObjetoImpDR" => "01",
            "EquivalenciaDR" => "1"
        ]));


        $pagos->getIterator()[0]->addAttributes([
            "MontoTotalPagos" => number_format($pago->cantidad, 2, '.', ''),
        ]);

        $this->comprobante_pago_model->monto_total_pagos = number_format( $pago->cantidad, 2, '.', '');

        // Agregar el nodo $leyendasFisc a los complementos del CFDI
        $comprobante->addComplemento($pagos);
        $comprobante->getComplemento();


        // método de ayuda para establecer las sumas del comprobante e impuestos
        // con base en la suma de los conceptos y la agrupación de sus impuestos
        $creator->addSumasConceptos(null, 0);

        // método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privada)
        $creator->addSello($key, $this->keyPemPass);
        $this->comprobante_pago_model->sello_cdf = $creator->comprobante()->attributes()->get('Sello');

        // método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
        $creator->moveSatDefinitionsToComprobante();

        // método de ayuda para validar usando las validaciones estándar de creación de la librería
        $asserts = $creator->validate();
        $asserts->hasErrors(); // contiene si hay o no errores

        $this->comprobante_pago_model->cadena_origen = $creator->buildCadenaDeOrigen();
        //$this->cfdi_model->save();

        if (!file_exists($this->documents_path_pago))
            mkdir($this->documents_path_pago, 0777, true);

        if (file_exists($document_save)) {
            File::delete($document_save);
            set_time_limit(3);
        }

        // método de ayuda para generar el xml y guardar los contenidos en un archivo
        $creator->saveXml($document_save);

        // método de ayuda para generar el xml y retornarlo como un string
        $this->xml = $creator->asXml();
        return $this->xml;
    }

    public function cancelarCFDI()
    {
        $taxpayer = $this->cfdi_model->emisora->rfc;
        $cer_file = fopen($this->cer_path, "r");
        $cer_content = fread($cer_file, filesize($this->cer_path));
        fclose($cer_file);

        # Read the Encrypted Private Key (des3) file on PEM format and encode it on base64
        $key_file = fopen($this->key_path, "r");
        $key_content = fread($key_file, filesize($this->key_path));
        fclose($key_file);

        $url = $this->cfdi_model->emisora->url_cancel_soap;
        $client = new \SoapClient($url, array('trace' => 1));

        $uuids = array("UUID" => $this->cfdi_model->uuid, "Motivo" => "02", "FolioSustitucion" => "");
        $uuid_ar = array('UUID' => $uuids);
        $params = array("UUIDS" => $uuid_ar,
            "username" => $this->username_soap,
            "password" => $this->password_soap,
            "taxpayer_id" => $taxpayer,
            "cer" => $cer_content,
            "key" => $key_content);

        $response = $client->__soapCall("cancel", array($params));

        $this->cfdi_model->response_soap_cancel = json_encode($response);

        $estado_pac = (string)$response->cancelResult->Folios->Folio->EstatusUUID;

        switch ($estado_pac) {
            case "704":
                $mensaje = 'Error con la contraseña de la llave privada';
                break;
            case "708":
                $mensaje = "Error de conexion del SAT ....";
                break;
            case "202":
                $mensaje = "202: UUID Cancelado Previamente";
                $this->cfdi_model->estado = Cfdi::CFDI_CANCELADO;
                break;
            case "203":
                $mensaje = "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                break;
            case "205":
                $mensaje = "205: UUID No existente";
                break;
            case "201":
                $mensaje = "Factura cancelada exitosamente. PFD modificado.";
                $this->cfdi_model->estado = Cfdi::CFDI_CANCELADO;
                break;
            default:
                $mensaje = "Error desconocido";
                break;
        }

        $this->cfdi_model->observaciones = $mensaje;
        $this->cfdi_model->save();

        return $mensaje;
    }

    public function cancelarCFDIPago($id){
        $complemento_pago = ComprobantesPagos::find($id);
        $taxpayer = $this->cfdi_model->emisora->rfc;
        $cer_file = fopen($this->cer_path, "r");
        $cer_content = fread($cer_file, filesize($this->cer_path));
        fclose($cer_file);

        # Read the Encrypted Private Key (des3) file on PEM format and encode it on base64
        $key_file = fopen($this->key_path, "r");
        $key_content = fread($key_file, filesize($this->key_path));
        fclose($key_file);

        $url = $this->cfdi_model->emisora->url_cancel_soap;
        $client = new \SoapClient($url, array('trace' => 1));

        $uuids = array("UUID" => $complemento_pago->uuid, "Motivo" => "02", "FolioSustitucion" => "");
        $uuid_ar = array('UUID' => $uuids);
        $params = array("UUIDS" => $uuid_ar,
            "username" => $this->username_soap,
            "password" => $this->password_soap,
            "taxpayer_id" => $taxpayer,
            "cer" => $cer_content,
            "key" => $key_content);

        $response = $client->__soapCall("cancel", array($params));

        $complemento_pago->response_soap_cancel = json_encode($response);

        $estado_pac = (string) $response->cancelResult->Folios->Folio->EstatusUUID;

        switch ($estado_pac) {
            case "704":
                $mensaje = 'Error con la contraseña de la llave privada';
                break;
            case "708":
                $mensaje = "Error de conexion del SAT ....";
                break;
            case "202":
                $mensaje = "202: UUID Cancelado Previamente";
                $complemento_pago->estado = ComprobantesPagos::PAGO_SOLCITADO;
                break;
            case "203":
                $mensaje = "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                break;
            case "205":
                $mensaje = "205: UUID No existente";
                break;
            case "201":
                $mensaje = "CFDI de pago cancelado exitosamente.";
                $complemento_pago->estado = ComprobantesPagos::PAGO_SOLCITADO;
                break;
            default:
                $mensaje = "Error desconocido";
                break;
        }

        $complemento_pago->observaciones = $mensaje;
        $complemento_pago->save();

        logAutofacturador(Auth::user()->id,ComprobantesPagos::PAGO_CANCELADO, $this->cfdi_model->id, $complemento_pago->id, json_encode($response));

        return $mensaje;
    }

    public function sendSoap()
    {
        $return = (object)$this->response;

        # Read the xml file and encode it on base64
        $invoice_path = $this->xmlPath;
        $xml_file = fopen($invoice_path, "rb");
        $xml_content = fread($xml_file, filesize($invoice_path));
        fclose($xml_file);

        # Consuming thed stamp service
        $url = $this->cfdi_model->emisora->url_service_soap;
        $client = new \SoapClient($url);

        $params = array(
            "xml" => $xml_content,
            "username" => $this->username_soap,
            "password" => $this->password_soap,
        );

        $response = $client->__soapCall("stamp", array($params));

        if ($this->comprobante_pago_model) {
            $this->comprobante_pago_model->response_soap = json_encode($response);
            if (isset($response->stampResult->Incidencias->Incidencia)) {
                $this->comprobante_pago_model->observaciones = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia. $response->stampResult->Incidencias->Incidencia->ExtraInfo;
                $this->comprobante_pago_model->estado = ComprobantesPagos::PROCESO_CANCELADO;
                $return->status = false;
                $return->mensaje = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia.$response->stampResult->Incidencias->Incidencia->ExtraInfo;
                logAutofacturador(Auth::user()->id,'error', $this->cfdi_model->id, $this->comprobante_pago_model->id, json_encode($response));
            } else {
                $this->comprobante_pago_model->uuid = $response->stampResult->UUID;
                $this->comprobante_pago_model->fecha_timbre = $response->stampResult->Fecha;
                $this->comprobante_pago_model->sello_fiscal = $response->stampResult->SatSeal;
                $file = fopen($invoice_path, "w");
                fwrite($file, $response->stampResult->xml);
                fclose($file);
                $this->comprobante_pago_model->estado = ComprobantesPagos::PAGO_FACTURADO;
                logAutofacturador(Auth::user()->id,ComprobantesPagos::PAGO_FACTURADO,$this->cfdi_model->id,$this->comprobante_pago_model->id,json_encode($response));
            }

            $this->comprobante_pago_model->save();
        } else {
            $this->cfdi_model->response_soap = json_encode($response);
            if (isset($response->stampResult->Incidencias->Incidencia)) {
                $this->cfdi_model->observaciones = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia;
                $this->cfdi_model->estado = Cfdi::PROCESO_CANCELADO;
                $return->status = false;
                $return->mensaje = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia;
                logAutofacturador(Auth::user()->id,'error',$this->cfdi_model->id,'',json_encode($response));
            } else {
                $this->cfdi_model->uuid = $response->stampResult->UUID;
                $this->cfdi_model->fecha_timbre = $response->stampResult->Fecha;
                $this->cfdi_model->sello_fiscal = $response->stampResult->SatSeal;
                $this->cfdi_model->certificado_sat = $response->stampResult->NoCertificadoSAT;
                $file = fopen($invoice_path, "w");
                fwrite($file, $response->stampResult->xml);
                fclose($file);
                logAutofacturador(Auth::user()->id,Cfdi::CDFI_FACTURADO,$this->cfdi_model->id,'',json_encode($response));
            }

            $this->cfdi_model->save();
        }


        return $return;
    }

    public function generaPdf()
    {
        if (!file_exists($this->documents_path))
            mkdir($this->documents_path, 0777, true);

        $document_save = $this->documents_path . 'autofactura-' . $this->cfdi_model->id . '.pdf';

        if (file_exists($document_save)) {
            File::delete($document_save);
            set_time_limit(3);
        }

        $cadenaSello = substr($this->cfdi_model->sello_cdf, -8);
        $CadImpTot = number_format($this->cfdi_model->total, 6, '.', ',');
        $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $this->cfdi_model->uuid . "&re=" . $this->cfdi_model->emisor_rfc . "&rr=" . $this->cfdi_model->receptor_rfc . "&tt=" . $CadImpTot . "&fe=" . $cadenaSello;

        //die(view('autofacturador.documentos.cfdi_pdf', ['data' => $this->cfdi_model, 'qr' => $qr]));
        if($this->cfdi_model->estado == Cfdi::CDFI_FACTURADO || $this->cfdi_model->estado == Cfdi::CFDI_CANCELADO)
            PDF::loadView('autofacturador.documentos.cfdi_pdf', ['data' => $this->cfdi_model, 'qr' => $qr])->save($document_save);
        else
            PDF::loadView('autofacturador.documentos.prefactura', ['data' => $this->cfdi_model, 'qr' => $qr])->save($document_save);

        return true;
    }

    public function generarPdfPago($id = null){
        if($id)
            $this->cfdi_model->comprobantePago = ComprobantesPagos::find($id);
        else
            $this->cfdi_model->comprobantePago = $this->comprobante_pago_model;

        $document_save = $this->documents_path_pago . 'autofactura-pago-' . $this->cfdi_model->id . '-' . $this->cfdi_model->comprobantePago->num_pago . '.pdf';
        $cadenaSello = substr($this->cfdi_model->comprobantePago->sello_cdf, -8);
        $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $this->cfdi_model->comprobantePago->uuid . "&re=" . $this->cfdi_model->emisor_rfc . "&rr=" . $this->cfdi_model->receptor_rfc . "&tt=" . $this->cfdi_model->comprobantePago->imp_saldo_insoluto . "&fe=" . $cadenaSello;
        //die(view('autofacturador.documentos.pago_pdf', ['data' => $this->cfdi_model, 'qr' => $qr]));
        PDF::loadView('autofacturador.documentos.pago_pdf', ['data' => $this->cfdi_model, 'qr' => $qr])->save($document_save);
        return true;
    }
}
