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

@foreach($data as $periodo)

    @php
        $files = Storage::disk('public')->files('repositorio/'.$id_empresa.'/'.$periodo->id_empleado.'/timbrado/archs_cfdi/');
        $file_xml =  collect($files)->filter(function (string $file_name) use ($periodo) {
                            return (str_contains($file_name, $periodo->num_factura));
                        });
        
        if(!$file_xml)
            continue;

        $stm = Storage::disk('public')->get($file_xml->first());        
        
        $xmlContents = \CfdiUtils\Cleaner\Cleaner::staticClean($stm);
        $cfdi = \CfdiUtils\Cfdi::newFromString($xmlContents);

        $comprobante = $cfdi->getNode();
        $tfd = $comprobante->searchNode('cfdi:Complemento', 'tfd:TimbreFiscalDigital');
        $emisor = $comprobante->searchNode('cfdi:Emisor');
        $receptor = $comprobante->searchNode('cfdi:Receptor');
        $concepto = $comprobante->searchNode('cfdi:Conceptos', 'cfdi:Concepto');
        $nomina = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina');
        $nomina_emisor = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Emisor');
        $nomina_receptor = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Receptor');
        $percepciones = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones');
        $percepcion = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion');
        $nomina_percepciones = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion');
        $nomina_deducciones = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones');
        $deduccion = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones','nomina12:Deduccion');
        $otro_pago = $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:OtrosPagos','nomina12:OtroPago');
        $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=".$tfd['UUID']."&re=".$emisor['rfc']."&rr=".$receptor['rfc']."&tt=".$comprobante['Total']."&fe=".substr($tfd['SelloCFD'],-8);
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $sello_cfd = str_split($tfd['SelloCFD'], 125);
        $sello_sat=str_split($tfd['SelloSAT'], 120);

    @endphp

    <table class="margen">
        <tbody>
        <tr>
            <td style="overflow: hidden;">
                <table class="round_table" style="width:100%">
                    <tbody>
                    <tr>
                        <th>EMPRESA:</th>
                    </tr>
                    <tr>
                        <td><p>{{$emisor['Nombre']}}</p></td>
                    </tr>
                    <tr>
                        <td>RFC: {{$emisor['Rfc']}}</td>
                    </tr>
                    <tr>
                        <td>RÉGIMEN: {{$emisor['RegimenFiscal']}}</td>
                    </tr>
                    <tr>
                        <td>REGISTRO
                            PATRONAL: {{ $nomina_emisor['RegistroPatronal'] }}</td>
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
                <table class="round_table" style="width:100%">
                    <thead>
                    <tr>
                        <th>RECIBO DE NÓMINA.</th>
                    </tr>
                    <tr>
                        <td>FOLIO FISCAL: {{$tfd['UUID']}}</td>
                    </tr>
                    <tr>
                        <td>CERTIFICADO SAT: {{$tfd['NoCertificadoSAT']}}</td>
                    </tr>
                    <tr>
                        <td>CERTIFICADO DEL EMISOR: {{$comprobante['NoCertificado']}}</td>
                    </tr>
                    <tr>
                        <td>LUGAR DE EXPEDICIÓN: {{ $comprobante['LugarExpedicion'] }}</td>
                    </tr>
                    <tr>
                        <td>FECHA HORA DE CERTIFICACIÓN: {{$tfd['FechaTimbrado']}}</td>
                    </tr>
                    <tr>
                        <td>RÉGIMEN FISCAL: {{$emisor['RegimenFiscal']}}</td>
                    </tr>
                    <tr>
                        <td>CFDI {{ $comprobante['Version'] }}</td>
                    </tr>
                    </thead>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="round_table" style="text-align: center;">
                EMPLEADO: {{$receptor['Nombre']}} &nbsp;  &nbsp;
                RÉGIMEN FISCAL: {{$nomina_receptor['TipoRegimen']}} &nbsp;  &nbsp;
                CP:{{$periodo->empleadoReceptor->cp}} &nbsp;  &nbsp;
                RFC: {{$receptor['Rfc']}}
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
                        <td>{{$concepto['ClaveProdServ']}}</td>
                        <td>{{$concepto['ClaveUnidad']}}</td>
                        <td> {{$concepto['Cantidad']}}</td>
                        <td> {{$concepto['Descripcion']}}</td>
                        <td> {{$concepto['ValorUnitario']}} </td>
                        <td> {{$concepto['Importe']}}</td>
                        <td> {{$concepto['Descuento']}} </td>
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
                            <table style="width: 100%; padding-right: 20px;">
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
                                <tr>
                                    <td>{{$percepcion['TipoPercepcion']}}</td>
                                    <td>{{$percepcion['Clave']}}</td>
                                    <td>{{$percepcion['Concepto']}}</td>
                                    <td>{{$percepcion['ImporteGravado']}}</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>TOTAL DE PERCEPCIONES:</td>
                                    <td>{{$nomina['TotalPercepciones']}}</td>
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
                                @foreach($nomina_deducciones as $nomina_deduccion)
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
                                @php $total_percepciones = 0;  @endphp
                                @foreach($nomina_percepciones as $nomina_percepcion)
                                    @php $total_percepciones += (float) $nomina_percepcion['Importe'];  @endphp
                                    <tr>
                                        <td>{{$nomina_percepcion['TipoOtroPago']}}</td>
                                        <td>{{$nomina_percepcion['Clave']}}</td>
                                        <td>{{$nomina_percepcion['Concepto']}}</td>
                                        <td>{{$nomina_percepcion['Importe']}}</td>
                                    </tr>
                                @endforeach
                                <tr style="padding-bottom: 10px;">
                                    <td colspan="2"></td>
                                    <td><b>TOTAL DE OTROS PAGOS:</b></td>
                                    <td>{{$total_percepciones}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 30%">
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        No.Empleado: {{$nomina_receptor['NumEmpleado']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        No.IMSS: {{$nomina_receptor['NumSeguridadSocial']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Departamento: {{$nomina_receptor['Departamento']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        CURP: {{$nomina_receptor['Curp']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Puesto: {{$nomina_receptor['Puesto']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Fecha inicial de pago: {{$nomina['FechaInicialPago']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Fecha final de pago: {{$nomina['FechaFinalPago']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Núm. de días pagados: {{$nomina['NumDiasPagados']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Total de percepciones: {{$nomina['TotalPercepciones']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Otros pagos: {{$nomina['TotalOtrosPagos']}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Neto recibido: {{$comprobante['Total']}}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            CANTIDAD CON LETRA (NETO RECIBIDO):  {{ $formatter->format($comprobante['Total']) }} MXN
                        </td>
                    </tr>
                    <tr>
                        <td>
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
            <td>
                <img src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->generate(json_encode($qr,true)), '<?xml version="1.0" encoding="UTF-8" ?>')) }}">
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
                                @foreach($sello_cfd as $cadena)
                                   {{$cadena}}
                                @endforeach
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Sello del SAT:</strong>
                            <p>
                                @foreach($sello_sat as $cadena)
                                    {{$cadena}}
                                @endforeach
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td> <strong>Cadena original del complemento de certificación digital del SAT:</strong><p>{{$periodo->cadena_original}}</p></td>
                    </tr>
                    <tr>
                        <th> ===== Este documento es una representación impresa de un CFDI =====</th>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        </tbody>
    </table>

@endforeach

</body>
</html>

