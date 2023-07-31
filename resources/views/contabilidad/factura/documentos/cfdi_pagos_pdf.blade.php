<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        .round_table {
            border-collapse: separate;
            border: solid black 1px;
            border-radius: 10px;
            -moz-border-radius: 10px;
            -webkit-border-radius: 5px;

        }

        .margen{
            table-layout: fixed;
            width: 100%;
            font-size: 10px;
            line-height: 10px;
        }

        td, th {
            padding: 3px;

        }

    </style>
</head>
<body  style="margin: 0px;padding: 0px">

<table class="margen">
    <tbody>
    <tr>
        <td style="overflow: hidden;">
            <table class="round_table" style="width: 100%;  height: 130px">
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>RECEPTOR:</strong>  {{Session::get('empresa')['razon_social']}}</td>
                </tr>
                <tr>
                    <td> <strong>RFC:</strong> {{Session::get('empresa')['rfc']}}</td>
                </tr>
                <tr>
                    <td><strong>CÓDIGO POSTAL:</strong> {{Session::get('empresa')['codigo_postal']}} </td>
                </tr>
                <tr>
                    <td rowspan="5"></td>
                </tr>
            </table>

        </td>
        <td>
            <table class="round_table" style="width: 100%;  height: 130px">
                <thead>
                <tr>
                    <th>FACTURA PAGO.</th>
                </tr>
                <tr>
                    <td><strong>FOLIO FISCAL:</strong> {{$data->timbradoFacturador->folio_fiscal}}</td>
                </tr>
                <tr>
                    <td><strong>CERTIFICADO SAT:</strong> {{$data->timbradoFacturador->certificado_sat}}</td>
                </tr>
                <tr>
                    <td><strong>CERTIFICADO DEL EMISOR: </strong> {{$data->timbradoFacturador->certificado_tim}}</td>
                </tr>
                <tr>
                    <td><strong>EFECTO DE COMPROBANTE:</strong>  P PAGO</td>
                </tr>
                <tr>
                    <td><strong>CFDI 4.0</strong></td>
                </tr>
                </thead>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2"class="round_table" style="text-align: center;">
            <table>
                <tbody>
                <tr>
                    <td><strong>TIPO DE MONEDA:</strong>MXN</td>
                    <td><strong>FORMA DE PAGO:</strong> {{$data->forma}} </td>
                    <td><strong>METODO DE PAGO:</strong> {{$data->metodo}} </td>
                </tr>
                <tr>
                    <td><strong>USO  DE CFDI:</strong> {{$data->regimen}} </td>
                    <td><strong>LUGAR, FECHA Y HORA DE EMISI&Oacute;N / CERTIFICACI&Oacute;N:</strong>  {{ $data->timbradoFacturador->fecha_timbrado }} {{ $data->emisor->cp }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="round_table" style="text-align: center;">

            <table>
                <tr>
                    <td>
                        <strong>EMISOR: {{$data->emisor->razon_social}}</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>RFC: </strong>{{$data->emisor->rfc}}  &nbsp;  &nbsp;
                        <strong>RÉGIMEN FISCAL: </strong> 601  &nbsp;  &nbsp;
                        <strong>CÓDIGO POSTAL: </strong>{{$data->emisor->cp}}  &nbsp;  &nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="round_table" style="width: 100%">
                <tbody>
                <tr>
                    <th>OTROS DATOS FISCALES</th>
                </tr>
                <tr>
                    <td><strong>Cantidad</strong> </td>
                    <td><strong>Descripción</strong> </td>
                    <td><strong>Valor unitario</strong> </td>
                    <td><strong>Importe</strong> </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Pago</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><strong>Clave prod/serv</strong></td>
                    <td><strong>Clave unidad</strong></td>
                </tr>
                <tr>
                    <td>84111506 - Servicios de facturación</td>
                    <td>ACT - Actividad</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="round_table" style="width: 100%">
                <tbody>
                <tr>
                    <th>DATOS GENERALES DEL PAGO</th>
                </tr>
                <tr>
                    <td>Fecha y hora de pago</td>
                    <td>Forma de pago</td>
                    <td>Moneda</td>
                    <td>Monto</td>
                </tr>
                <tr>
                    <td>{{$data->fecha_pago}}</td>
                    <td>{{$data->forma}}</td>
                    <td>MXN</td>
                    <td>{{$data->monto}}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="round_table" style="width: 100%">
                <tbody>
                <tr>
                    <th>DOCUMENTO RELACIONADO</th>
                </tr>
                <tr>
                    <td><strong>Id documento (UUID)</strong></td>
                    <td><strong>Folio</strong> </td>
                </tr>
                <tr>
                    <td>{{$data->folio_relacionado}}</td>
                    <td>{{$data->folio}}</td>
                </tr>
                <tr>
                    <td><strong>Método de pago</strong> </td>
                    <td><strong>Moneda del documento relacionado</strong> </td>
                </tr>
                <tr>
                    <td>{{$data->metodo}}</td>
                    <td>MXN</td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Número de parcialidad</strong></td>
                    <td><strong>Importe saldo anterior</strong></td>
                    <td><strong>Importe pagado</strong></td>
                    <td><strong>Importe saldo insoluto</strong></td>
                </tr>
                <tr>
                    <td>{{$data->num_parcialidad}}</td>
                    <td>{{$data->importe_saldo_anterior}}</td>
                    <td>{{$data->importe_pagado}}</td>
                    <td>{{$data->importe_saldo_insoluto}}</td>
                </tr>


                @if(isset($data->folio_relacionado_2))
                    <tr>
                        <td><strong>Id documento (UUID)</strong></td>
                        <td><strong>Folio</strong> </td>
                    </tr>
                    <tr>
                        <td>{{$data->folio_relacionado_2}}</td>
                        <td>{{$data->folio_2}}</td>
                    </tr>
                    <tr>
                        <td><strong>Método de pago</strong> </td>
                        <td><strong>Moneda del documento relacionado</strong> </td>
                    </tr>
                    <tr>
                        <td>{{$data->metodo_2}}</td>
                        <td>MXN</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>Número de parcialidad</strong></td>
                        <td><strong>Importe saldo anterior</strong></td>
                        <td><strong>Importe pagado</strong></td>
                        <td><strong>Importe saldo insoluto</strong></td>
                    </tr>
                    <tr>
                        <td>{{$data->num_parcialidad_2}}</td>
                        <td>{{$data->importe_saldo_anterior_2}}</td>
                        <td>{{$data->importe_pagado_2}}</td>
                        <td>{{$data->importe_saldo_insoluto_2}}</td>
                    </tr>
                @endif


                @if(isset($data->folio_3))
                    <tr>
                        <td><strong>Id documento (UUID)</strong></td>
                        <td><strong>Folio</strong> </td>
                    </tr>
                    <tr>
                        <td>{{$data->folio_relacionado_3}}</td>
                        <td>{{$data->folio_3}}</td>
                    </tr>
                    <tr>
                        <td><strong>Método de pago</strong> </td>
                        <td><strong>Moneda del documento relacionado</strong> </td>
                    </tr>
                    <tr>
                        <td>{{$data->metodo_pago_3}}</td>
                        <td>MXN</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>Número de parcialidad</strong></td>
                        <td><strong>Importe saldo anterior</strong></td>
                        <td><strong>Importe pagado</strong></td>
                        <td><strong>Importe saldo insoluto</strong></td>
                    </tr>
                    <tr>
                        <td>{{$data->num_parcialidad_3}}</td>
                        <td>{{$data->importe_saldo_anterior_3}}</td>
                        <td>{{$data->importe_pagado_3}}</td>
                        <td>{{$data->importe_saldo_insoluto_3}}</td>
                    </tr>

                @endif

                </tbody>
            </table>
        </td>
    </tr>

    </tbody>
</table>
<footer class="margen">
    <table width="18cm" style="border-collapse: collapse; margin-left: 10px">
        <tr style="max-width: 18cm;">
            <td style="align-self: start;word-wrap: break-word;max-width: 10cm;">
                <h6>Sello SAT</h6>
                <p>{{$data->timbradoFacturador->sello_sat}}</p>
            </td>
        </tr>
    </table>
    <table width="18cm" style="border-collapse: collapse; margin-left: 10px">
        <tr style="max-width: 15cm;">
            <td width="5cm">
                <img src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->generate($qr), '<?xml version="1.0" encoding="UTF-8" ?>')) }}">
            </td>
            <td style="align-self: end;word-wrap: break-word;max-width: 10cm;">
                <h6>Sello digital del CFDI</h6>
                <p>{{$data->timbradoFacturador->sello_cfdi}}</p>
                <h6>Cadena original del complemento de certificación digital del SAT</h6>
                <p>{{$data->timbradoFacturador->cadena_original}}</p>
            </td>
        </tr>
    </table>
</footer>
<footer style="text-align: center; font-size: 12px;">
    ==================== Este documento es una representación impresa de un CFDI ====================
</footer>

</body>
</html>