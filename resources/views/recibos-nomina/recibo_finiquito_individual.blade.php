<html lang="es">
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
<body style="margin: 0px;padding: 0px">
<table class="margen">
    <tbody>
    <tr>
        <td style="overflow: hidden;">
            <table class="round_table" style="width: 100%">
                <tbody>
                <tr>
                    <th>EMPRESA:</th>
                </tr>
                <tr>
                    <td><p>{{$data['emisor']['Nombre']}}</p></td>
                </tr>
                <tr>
                    <td>RFC: {{$data['emisor']['Rfc']}}</td>
                </tr>
                <tr>
                    <td>RÉGIMEN: {{$data['emisor']['RegimenFiscal']}}</td>
                </tr>
                <tr>
                    <td>REGISTRO
                        PATRONAL: {{ $data['nomina_emisor']['RegistroPatronal'] }}</td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td>RECIBO DE NÓMINA VERSIÓN 1.2</td>
                </tr>
                </tbody>
            </table>

        </td>
        <td>
            <table class="round_table" style="width: 100%">
                <thead>
                <tr>
                    <th>RECIBO DE NÓMINA.</th>
                </tr>
                <tr>
                    <td>FOLIO FISCAL: {{$data['tfd']['UUID']}}</td>
                </tr>
                <tr>
                    <td>CERTIFICADO SAT: {{$data['tfd']['NoCertificadoSAT']}}</td>
                </tr>
                <tr>
                    <td>CERTIFICADO DEL EMISOR: {{$data['comprobante']['NoCertificado']}}</td>
                </tr>
                <tr>
                    <td>LUGAR DE EXPEDICIÓN: {{ $data['comprobante']['LugarExpedicion'] }}</td>
                </tr>
                <tr>
                    <td>FECHA HORA DE CERTIFICACIÓN: {{$data['tfd']['FechaTimbrado']}}</td>
                </tr>
                <tr>
                    <td>RÉGIMEN FISCAL: {{$data['emisor']['RegimenFiscal']}}</td>
                </tr>
                <tr>
                    <td>CFDI 4.0</td>
                </tr>
                </thead>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="round_table" style="text-align: center;">
            EMPLEADO: {{$data['receptor']['Nombre']}} &nbsp;  &nbsp;
            RÉGIMEN FISCAL: {{$data['nomina_receptor']['TipoRegimen']}} &nbsp;  &nbsp;
            CP:{{$data['receptor']['DomicilioFiscalReceptor']}} &nbsp;  &nbsp;
            RFC: {{$data['receptor']['Rfc']}}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="round_table" style="width: 100%">
                <tbody>
                <tr>
                    <th>CONCEPTO DE PAGO</th>
                </tr>
                <tr>
                    <td>ClaProdServ</td>
                    <td>ClaUnidad</td>
                    <td>Cant</td>
                    <td>Descripción</td>
                    <td>Valor unitario</td>
                    <td>Importe</td>
                    <td>Descuento</td>
                </tr>
                <tr>
                    <td>{{$data['concepto']['ClaveProdServ']}}</td>
                    <td>{{$data['concepto']['ClaveUnidad']}}</td>
                    <td> {{$data['concepto']['Cantidad']}}</td>
                    <td> {{$data['concepto']['Descripcion']}}</td>
                    <td> {{$data['concepto']['ValorUnitario']}} </td>
                    <td> {{$data['concepto']['Importe']}}</td>
                    <td> {{$data['concepto']['Descuento']}} </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="round_table">
                <tbody>
                <tr>
                    <td>
                        <table  style="width: 100%; padding-right: 20px;">
                            <tbody>
                            <tr>
                                <th>PERCEPCIONES</th>
                            </tr>
                            <tr>
                                <td>TIPO</td>
                                <td>CLAVE</td>
                                <td>CONCEPTO</td>
                                <td>IMPORTE</td>
                            </tr>
                            @foreach($data['percepciones'] as $nomina_percepcion)
                                <tr>
                                    <td>{{$nomina_percepcion['TipoPercepcion']}}</td>
                                    <td>{{$nomina_percepcion['Clave']}}</td>
                                    <td>{{$nomina_percepcion['Concepto']}}</td>
                                    <td>{{(float) $nomina_percepcion['ImporteGravado'] + (float) $nomina_percepcion['ImporteExento']}}</td>
                                </tr>
                            @endforeach
                            <tr style="padding-bottom: 10px;">
                                <td colspan="2"></td>
                                <td><b>TOTAL DE PERCEPCIONES:</b></td>
                                <td>{{(float)$data['percepciones']['TotalSueldos']}}</td>
                            </tr>

                            <tr>
                                <th>DEDUCCIONES</th>
                            </tr>
                            <tr>
                                <td>TIPO</td>
                                <td>CLAVE</td>
                                <td>CONCEPTO</td>
                                <td>IMPORTE</td>
                            </tr>
                            @php $total_deducciones = 0;  @endphp
                            @foreach($data['nomina_deducciones'] as $nomina_deduccion)
                                @php $total_deducciones += (float) $nomina_deduccion['Importe'];  @endphp
                                <tr>
                                    <td>{{$nomina_deduccion['TipoDeduccion']}}</td>
                                    <td>{{$nomina_deduccion['Clave']}}</td>
                                    <td>{{$nomina_deduccion['Concepto']}}</td>
                                    <td>{{$nomina_deduccion['Importe']}}</td>
                                </tr>
                            @endforeach
                            <tr style="padding-bottom: 10px;">
                                <td colspan="2"></td>
                                <td><b>TOTAL DE DEDUCCIONES:</b></td>
                                <td>{{$total_deducciones}}</td>
                            </tr>

                            <tr>
                                <th>OTROS PAGOS:</th>
                            </tr>
                            <tr>
                                <td>TIPO</td>
                                <td>CLAVE</td>
                                <td>CONCEPTO</td>
                                <td>IMPORTE</td>
                            </tr>
                            @php $total_otros_pagos = 0;  @endphp
                            @foreach($data['otro_pago'] as $nomina_otro_pago)
                                @php $total_otros_pagos += (float) $nomina_otro_pago['Importe'];  @endphp
                                <tr>
                                    <td>{{$nomina_otro_pago['TipoOtroPago']}}</td>
                                    <td>{{$nomina_otro_pago['Clave']}}</td>
                                    <td>{{$nomina_otro_pago['Concepto']}}</td>
                                    <td>{{$nomina_otro_pago['Importe']}}</td>
                                </tr>
                            @endforeach
                            <tr style="padding-bottom: 10px;">
                                <td colspan="2"></td>
                                <td><b>TOTAL DE OTROS PAGOS:</b></td>
                                <td>{{$total_otros_pagos}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 25%">
                        <table>
                            <tbody>
                            <tr>
                                <td>
                                    No.Empleado: {{$data['nomina_receptor']['NumEmpleado']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    No.IMSS: {{$data['nomina_receptor']['NumSeguridadSocial']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Departamento: {{$data['nomina_receptor']['Departamento']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    CURP: {{$data['nomina_receptor']['Curp']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Puesto: {{$data['nomina_receptor']['Puesto']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Fecha inicial de pago: {{$data['nomina']['FechaInicialPago']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Fecha final de pago: {{$data['nomina']['FechaFinalPago']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Núm. de días pagados: {{$data['nomina']['NumDiasPagados']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Total de percepciones: {{$data['nomina']['TotalPercepciones']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Otros pagos: {{$data['nomina']['TotalOtrosPagos']}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Neto recibido: {{$data['comprobante']['Total']}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        CANTIDAD CON LETRA (NETO RECIBIDO): {{ $data['formatter']->format($data['comprobante']['Total']) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Recibí de conformidad la cantidad descrita, haciendo constar que con dicha suma estoy
                        totalmente pagado
                        (a) hasta la fecha señalada en el presente
                        recibo, por lo que no me reservo acción ni derecho alguno para reclamar por estos conceptos
                        ni por
                        ningún otro.
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:160px">
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(150)->generate($data['qr'])) }}">
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table style="table-layout: fixed; width: 100%; font-size: 10px">
                <tbody>
                <tr>
                    <td>
                        <strong> Sello digital del CFDI:</strong>
                        <p>
                            @foreach($data['sello_cfd'] as $cadena)
                                {{$cadena}}
                            @endforeach
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Sello del SAT:</strong>
                        <p>
                            @foreach($data['sello_sat'] as $cadena)
                                {{$cadena}}
                            @endforeach
                        </p>
                    </td>
                </tr>
                <tr>
                    <td> <strong>Cadena original del complemento de certificación digital del SAT:</strong><p>{{$data['cadena_origen']}}</p></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    </tbody>
</table>
<footer style="text-align: center; font-size: 12px;">
    ==================== Este documento es una representación impresa de un CFDI ====================
</footer>
</body>
</html>

