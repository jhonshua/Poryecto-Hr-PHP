
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
</head>
<body>
<table  width="100%" style="border-collapse: collapse">
    <tbody>
    <tr>
        <td width="50%">
            <img src="{{ $data->emisora->logo_base64 }}" style="{{ (Auth::user()->base_autofacturador == 6) ? 'width: 5cm' : 'width: 3cm' }}">
        </td>
        <td width="" style="padding: 0.5cm 10px;">
            <b>CFDI RELACIONADO</b><br>
            Folio - Serie: {{ $data->folio }} - {{ $data->serie }}<br>
            Forma de pago: {{ $data->formaPago->clave }} - {{ $data->formaPago->descripcion }} <br>
            Metodo de pago: {{ $data->metodoPago->metodo }} - {{ $data->metodoPago->descripcion }} <br>
            UUID R: {{ $data->uuid }} <br>
            Total: ${{ number_format($data->total, 2, '.', ',') }} - Base: ${{ number_format($data->base, 2, '.', ',') }}
        </td>
    </tr>
    <tr>
        <td style="border-bottom: black solid 2px; border-top: black solid 2px; padding: 0.5cm 10px;">
            <b>EMISOR</b> <br>
            {{ $data->emisor_nombre }} <br>
            RFC: {{ $data->emisor_rfc }} <br>
            RÉGIMEN FISCAL: {{ $data->regimenFiscalEmisor->codigo }} - {{ $data->regimenFiscalEmisor->regimen }}<br>
            CÓDIGO POSTAL: {{$data->emisora->cp}} <br>
            {{ $data->emisora->domicilio ?? '' }}<br>
        </td>
        <td style="border-bottom: black solid 2px; border-top: black solid 2px; padding: 0.5cm 10px;">
            <b>RECEPTOR</b><br>
            {{ $data->receptor_nombre }} <br>
            RFC: {{ $data->receptor_rfc }} <br>
            USO CFDI: CP01 - Pagos<br>
            RÉGIMEN FISCAL: {{ $data->regimenFiscalReceptor->codigo }} - {{ $data->regimenFiscalReceptor->regimen }}<br>
            CÓDIGO POSTAL: {{$data->receptor->cp}} <br>
            {{ $data->receptor->domicilio ?? '' }}<br>
        </td>
    </tr>
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse">
    <tbody>
    <tr>
        <td>
            <table style="width: 100%; margin-top: 15px">
                <tbody>
                <tr>
                    <th colspan="7" style="line-height: 40px"><h1 style="font-size: 15px">Tipo de comprobante: P - Pago</h1></th>
                </tr>
                <tr>
                    <td><strong>Clave prod/serv</strong></td>
                    <td><strong>Clave unidad</strong></td>
                    <td><strong>Cantidad</strong></td>
                    <td><strong>Descripción</strong></td>
                    <td><strong>Valor unitario</strong></td>
                    <td><strong>Importe</strong></td>
                    <td><strong>Objeto Impuesto</strong></td>
                </tr>
                <tr>
                    <td>84111506 - Servicios de facturación</td>
                    <td>ACT - Actividad</td>
                    <td>1</td>
                    <td>Pago</td>
                    <td>0</td>
                    <td>0</td>
                    <td>1</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; margin-top: 15px">
                <tbody>
                <tr>
                    <td><strong>Fecha y hora de Pago</strong></td>
                    <td><strong>Forma de pago</strong></td>
                    <td><strong>Tipo moneda</strong></td>
                    <td><strong>Moneda</strong></td>
                    <td><strong>Monto</strong></td>
                </tr>
                <tr>
                    <td>{{$data->comprobantePago->fecha_pago}}</td>
                    <td>{{$data->comprobantePago->formaPago->clave}} - {{$data->comprobantePago->formaPago->descripcion}}</td>
                    <td>XXX</td>
                    <td>MXN</td>
                    <td>${{ number_format($data->comprobantePago->monto_total_pagos, 2, '.', ',') }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px">
                <tbody>
                <tr>
                    <td><strong>Número de parcialidad</strong></td>
                    <td><strong>Importe saldo anterior</strong></td>
                    <td><strong>Importe pagado</strong></td>
                    <td><strong>Importe saldo insoluto</strong></td>
                </tr>
                <tr>
                    <td>{{ $data->comprobantePago->num_pago }}</td>
                    <td>${{ number_format($data->comprobantePago->imp_saldo_anterior, 2, '.', ',') }}</td>
                    <td>${{ number_format($data->comprobantePago->cantidad, 2, '.', ',') }}</td>
                    <td>${{ number_format($data->comprobantePago->imp_saldo_insoluto, 2, '.', ',') }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<br><br><br><br><br>
<table style="border-collapse: collapse;" class="fs-resumen">
    <tr style="">
        <td width="5cm">
            <img src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->color(explode(',', $data->emisora->colores)[0], explode(',', $data->emisora->colores)[1], explode(',', $data->emisora->colores)[2])->generate($qr), '<?xml version="1.0" encoding="UTF-8" ?>')) }}">
        </td>
        <td style="padding-bottom: 0.2cm; align-self: end; font-size: 10px">
            Este documento es una representación impresa de un CFDI <br>
            <strong>UUID:</strong> {{ $data->comprobantePago->uuid }} <br>
            <strong>Número de Certificado:</strong> {{ $data->emisor_certificado }} <br>
            <strong>Fecha de emisión:</strong> {{ $data->comprobantePago->fecha_timbre }}<br>
            <strong>RFC Proveedor de Certificación:</strong> CVD110412TF6 <br>
        </td>
    </tr>
</table>
<table style="border-collapse: collapse" class="fs-resumen">
    <tr style="">
        <td style="word-wrap: break-word;" colspan="2">
            <h6 style="background: black;color: white;margin: 5px;padding: 0.1cm;">Sello SAT</h6>
            <p style="margin: 5px">{{ $data->comprobantePago->sello_fiscal }}</p>
        </td>
    </tr>
    <tr style="">
        <td style="word-wrap: break-word;" colspan="2">
            <h6 style="background: black;color: white;margin: 5px;padding: 0.1cm;">Sello digital del CFDI</h6>
            <p style="margin: 5px">{{ $data->comprobantePago->sello_cdf }}</p>
        </td>
    </tr>
    <tr style="">
        <td style="word-wrap: break-word;" colspan="2">
            <h6 style="background: black;color: white;margin: 5px;padding: 0.1cm;">Cadena original del complemento de certificación digital del SAT</h6>
            <p style="margin: 5px">{{ $data->comprobantePago->cadena_origen }}</p>
        </td>
    </tr>
</table>
</body>

<style>
    td{
        font-size: 13px;
        border-color: rgba({{ $data->emisora->colores }}, .8) !important;
    }

    th{
        border-color: rgba({{ $data->emisora->colores }}, .8) !important;
    }

    thead, h6{
        background-color: rgba({{ $data->emisora->colores }}, .8) !important;
    }

    footer p, footer h6{
        font-size: 9px;
    }

    .fs-resumen tr td{
        font-size: 9px;
    }
</style>
</html>