
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
            <img src='{{ $data->emisora->logo_base64 }}' style='{{ (Auth::user()->base_autofacturador == 6) ? 'width: 5cm' : 'width: 3cm' }}'>
        </td>
        <td width="" style="padding: 0.5cm 10px;">
            <h1 style="color: red">{{ (Auth::user()->base_autofacturador == 6) ? 'Orden de compra' : 'Prefactura' }}</h1>
            Folio - Serie: {{ $data->folio }} - {{ $data->serie }}<br>
            @if($data->cfdi_relacionado)
                CFDI Relacionado: {{$data->relacionCfdi->uuid}} <br>
                Tipo de Relacion: {{$data->tipo_relacion}}<br>
            @endif
            Fecha de Expedición: {{ date('d-m-Y') }} <br>
            Lugar de Expedición: {{ $data->lugar_expedicion }} <br>
            Forma de pago: {{ $data->formaPago->clave }} - {{ $data->formaPago->descripcion }} <br>
            Metodo de pago: {{ $data->metodoPago->metodo }} - {{ $data->metodoPago->descripcion }}
        </td>
    </tr>
    <tr>
        <td style="border-bottom: black solid 2px; border-top: black solid 2px; padding: 0.5cm 10px;">
            EMISOR <br>
            {{ $data->emisor_nombre }} <br>
            RFC: {{ $data->emisor_rfc }} <br>
            RÉGIMEN FISCAL: {{ $data->regimenFiscalEmisor->codigo }} - {{ $data->regimenFiscalEmisor->regimen }}<br>
        </td>
        <td style="border-bottom: black solid 2px; border-top: black solid 2px; padding: 0.5cm 10px;">
            RECEPTOR <br>
            {{ $data->receptor_nombre }} <br>
            RFC: {{ $data->receptor_rfc }} <br>
            USO CFDI: {{ $data->usoCFDI->clave }} - {{ $data->usoCFDI->uso_cfdi }}<br>
            RÉGIMEN FISCAL: {{ $data->regimenFiscalReceptor->codigo }} - {{ $data->regimenFiscalReceptor->regimen }}<br>
        </td>
    </tr>
    </tbody>
</table>
<table width="100%" style="border-collapse: collapse">
    <thead style="background: black;color: white;">
    <tr>
        <th style="border: solid black 2px">Cantidad</th>
        <th style="border: solid black 2px">Unidad</th>
        <th style="border: solid black 2px">Descripción V.</th>
        <th style="border: solid black 2px">Unitario</th>
        <th style="border: solid black 2px">Impuestos</th>
        <th style="border: solid black 2px">Importe</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data->conceptos as $concepto)
        <tr>
            <td style="border: solid black 2px">{{ number_format($concepto->cantidad, 2, '.', ',') }}</td>
            <td style="border: solid black 2px">{{ $concepto->clave_unidad }}</td>
            <td style="border: solid black 2px">{{ $concepto->clave_prod }} - {{ $concepto->descripcion }}</td>
            <td style="border: solid black 2px">${{ number_format($concepto->valor_unitario, 2, '.', ',') }}</td>
            <td style="border: solid black 2px">${{ number_format($concepto->importe, 2, '.', ',') }}</td>
            <td style="border: solid black 2px">${{ number_format($concepto->base, 2, '.', ',') }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4"></td>
        <td style="border: solid black 2px">Subtotal</td>
        <td style="border: solid black 2px">${{ number_format($data->subtotal, 2, '.', ',') }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td style="border: solid black 2px">I.V.A.</td>
        <td style="border: solid black 2px">${{ number_format($data->total - $data->subtotal, 2, '.', ',') }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td style="border: solid black 2px">Total</td>
        <td style="border: solid black 2px">${{ number_format($data->total, 2, '.', ',') }}</td>
    </tr>
    </tbody>
</table>

<table>
    <tbody>
    <tr>
        <td>
            <table width="100%">
                <tr>
                    <th>Traslados</th>
                </tr>
                <tr>
                    <th style="text-align: left">Impuesto</th>
                    <th style="text-align: left">Tipo Factor</th>
                    <th style="text-align: left">Tasa o cuota</th>
                    <th style="text-align: left">Importe</th>
                </tr>
                <tr>
                    <td>{{ $data->impuesto }}</td>
                    <td>{{ $data->tipo_factor }}</td>
                    <td>{{ number_format($data->tasa_cuota, 2) }}</td>
                    <td>$ {{ number_format($data->total - $data->subtotal, 2, '.', ',') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
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
