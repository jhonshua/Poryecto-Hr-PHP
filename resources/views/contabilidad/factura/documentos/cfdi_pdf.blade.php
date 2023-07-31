<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Factura</title>
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
<body>

<table class="margen">
    <tbody>
    <tr>
        <td style="overflow: hidden;">
            <table class="round_table" style="width: 100%; height: 130px">
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <strong>RECEPTOR:</strong> {{Session::get('empresa')['razon_social']}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>RFC:</strong> {{Session::get('empresa')['rfc']}}
                    </td>
                </tr>
                <tr>
                    <td><strong>USO CFDI:</strong> {{ $data->regimen }}</td>
                </tr>
                <tr>
                    <td>
                        <strong>RÉGIMEN FISCAL:</strong> {{Session::get('empresa')['regimen']}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>CÓDIGO POSTAL:</strong> {{Session::get('empresa')['codigo_postal']}}
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table class="round_table" style="width: 100%; height: 130px">
                <thead>
                <tr>
                    <th>FACTURA</th>
                </tr>
                <tr>
                    <td> <strong>FOLIO - SERIE:</strong> {{ $data->timbradoFacturador->no_factura }}</td>
                </tr>
                <tr>
                    <td><strong>FOLIO FISCAL:</strong> {{$data->timbradoFacturador->folio_fiscal}}</td>
                </tr>
                <tr>
                    <td><strong>CERTIFICADO SAT:</strong> {{$data->timbradoFacturador->certificado_sat}}</td>
                </tr>
                <tr>
                    <td><strong>CERTIFICADO DEL EMISOR: </strong> {{$data->timbradoFacturador->certificado_tim}} </td>
                </tr>
                <tr>
                    <td><strong>TIPO DE COMPROBANTE:</strong> {{$data->tipo_comprobante}}</td>
                </tr>
                <tr>
                    <td><strong>CFDI 4.0</strong></td>
                </tr>
                </thead>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2"class="round_table" style="">
            <table style="width: 100%">
                <tr>
                    <td>
                        <table style="width: 100%"><tr>
                            <td style="width: 33.33%; text-align: center"> <strong>FORMA DE PAGO:</strong> {{$data->forma}} </td>
                            <td style="width: 33.33%; text-align: center"> <strong>METODO DE PAGO:</strong> {{$data->metodo}} </td>
                            <td style="width: 33.33%; text-align: center"> <strong>USO  DE CFDI:</strong> {{$data->regimen}} </td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center"><strong>FECHA Y LUGAR DE EXPEDICIÓN:</strong> {{ $data->timbradoFacturador->fecha_emision }}  - {{$data->emisor->direccion}}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2"class="round_table" style="text-align: center;">
           <table>
               <tr>
                   <td><strong>EMISOR: {{$data->emisor->razon_social}}</strong></td>
               </tr>
               <tr>
                   <td>
                       <table style="width: 100%"><tr>
                           <td style="width: 33.33%; text-align: center"><strong>RFC: </strong>{{$data->emisor->rfc}} </td>
                           <td style="width: 33.33%; text-align: center"><strong>RÉGIMEN FISCAL: </strong> 601</td>
                           <td style="width: 33.33%; text-align: center"><strong>CÓDIGO POSTAL:</strong>{{$data->emisor->cp}}</td>
                       </tr></table>
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
                    <td><strong>Cantidad</strong> </td>
                    <td><strong>Unidad</strong> </td>
                    <td><strong>Descripción V.</strong> </td>
                    <td><strong>Unitario</strong> </td>
                    <td><strong>Importe</strong> </td>
                </tr>

                @php
                    $subtotal=0;
                @endphp
                @foreach($data->conceptos as $concepto)
                    <tr>
                        <td>{{ number_format($concepto->cantidad, 2, '.', ',') }}</td>
                        <td>{{ $concepto->unidad }}</td>
                        <td> {{ $concepto->concepto }} </td>
                        <td >$ {{ number_format($concepto->monto, 2, '.', ',') }}</td>

                        <td>$ {{ number_format($concepto->monto * $concepto->cantidad, 2, '.', ',') }}</td>
                    </tr>
                    @php
                        $subtotal+= floatval($concepto->monto) * floatval($concepto->cantidad);
                    @endphp
                @endforeach
                <tr>
                    <td colspan="3"></td>
                    <td><strong>Subtotal</strong></td>
                    <td>$ {{ number_format($subtotal, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td><strong>I.V.A.</strong> </td>
                    <td>$ {{ number_format( $data->timbradoFacturador->importe - $subtotal, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td><strong>Total</strong></td>
                    <td>$ {{ number_format($data->timbradoFacturador->importe, 2, '.', ',') }}</td>
                </tr>

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
<footer style="text-align: center; font-size: 12px; position: inherit">
    ==================== Este documento es una representación impresa de un CFDI ====================
</footer>



