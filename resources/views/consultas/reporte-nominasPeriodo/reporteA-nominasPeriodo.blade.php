@php
// dd($datos['fecha_pago'][0]->fecha_inicial_periodo);
// foreach ($datos['conceptosIDs'] as $concepto)
//if( $concepto->tipo == 1)
//dd($concepto->nombre_concepto)


@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0.5cm 1cm;
            font-family: Arial;
        }

        @font-face {
            font-family: "Maven Pro Light 300 Regular";
            src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot") }}');
            src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot?#iefix") }}') format("embedded-opentype"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff2") }}') format("woff2"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff") }}') format("woff"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.ttf") }}') format("truetype"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.svg#Maven Pro Light 300 Regular") }}') format("truetype")
        }

        body * {
            /* font-family:"Maven Pro Light 300 Regular" ; */
            font-family: Helvetica;
            font-size: 11px;
        }

        h2 {
            font-size: 2em;
            font-family: "Helvetica" !important;
            font-weight: normal;
        }

        .container {
            max-width: 100%;
        }

        #contenido {
            font-size: 9px;
        }

        table#contenido th {
            padding: 2px;
            background-color: #000;
            color: white;
            font-family: "Helvetica" !important;
            font-size: 8px;
            font-weight: normal;
        }

        table#contenido tr {
            border-bottom: 2px solid #888;
        }

        tr:nth-child(even) {
            background: #eee
        }

        table#contenido td {
            font-size: 9px;
        }

        strong {
            font-family: Helvetica !important;
        }

        table.totales {
            font-size: 16px;
        }

        table.totales td {
            font-size: 12px;
        }
    </style>

</head>

<body id="app">
    <div class="row">

        <div class="col-md-12">


            @foreach($datos['empleadoss'] as $clave => $empleados)
            <p style="text-align: center;font-size:20pt !important;"><b>{{Session::get('empresa')['razon_social']}}</b></p>

            <table style="width: 100%;text-align: center;" class="totales">
                <tr style="font-size: 2em;">
                    <td>
                        Periodo: <strong>{{ base64_decode($periodo) }}</strong>
                    </td>
                    <td>
                        Del: <strong>{{ $datos['fecha_pago'][0]->fecha_inicial_periodo }}</strong> Al: <strong>{{ $datos['fecha_pago'][0]->fecha_final_periodo }}</strong>
                    </td>
                    <td>
                        Registro patronal: <strong>{{$datos['registro_patronal']}}</strong><br>
                    </td>
                    <td>RFC: <strong>{{Session::get('empresa')['rfc']}} </strong></td>
                    <td>
                        FECHA: <strong>{{formatoAFecha(date('Y-m-d'))}} </strong><br>

                    </td>
                </tr>
            </table>

            <p style="text-align: center;font-size:18pt !important;"><b>Reporte de la Nómina <small>(parte {{($clave + 1)}})</small></b></p>
            <center>
                <table class="table table-striped mt-4 text-center" id="contenido" style="width:100%">
                    <tbody>
                        @foreach ($empleados as $c => $emp)
                        <tr>
                            @foreach($emp as $cv => $val)
                            @if($c == 'nombre')
                            <th class="thead-dark">{!!$val!!}</th>
                            @else
                            @if($cv == 0)
                            <th class="thead-dark">{{$val}}</th>
                            @else
                            <td style="text-align: center;">{{$val}}</td>
                            @endif
                            @endif

                            @endforeach
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </center>
            <div style="page-break-after:always;"></div>
            @endforeach


            <p style="text-align: center;font-size:20pt !important;"><b>{{Session::get('empresa')['razon_social']}}</b></p>

            <table style="width: 100%;text-align: center;" class="totales">
                <tr style="font-size: 2em;">
                    <td>
                        Periodo: <strong>{{ base64_decode($periodo) }}</strong>
                    </td>
                    <td>
                        Registro patronal: <strong>{{$datos['registro_patronal']}}</strong><br>
                    </td>
                    <td>RFC: <strong>{{Session::get('empresa')['rfc']}} </strong></td>
                    <td>
                        FECHA: <strong>{{formatoAFecha(date('Y-m-d'))}} </strong><br>

                    </td>
                </tr>
            </table>

            <table width="100%" style="margin-top: 80px">
                <tr>
                    <td width="30%">
                        <strong style="margin-bottom: 20px">PERCEPCIONES</strong>
                        <table width="70%" class="totales">
                            @php
                            $total_percepciones = 0;

                            @endphp
                            @foreach ($datos['conceptosIDs'] as $concepto)

                            @if ($concepto->tipo == 1)
                            <tr>
                                <td style="white-space: nowrap;">{{$concepto->nombre_concepto}}</td>
                                <td style="white-space: nowrap;">
                                    @php
                                    
                                    $total_concepto = $datos['empleadoss'][0]['total_percep'];
                                    $total_percepciones =array_sum($total_concepto)

                                    @endphp
                                    ${{number_format(round($total_percepciones, 2) ,2,'.',',')}}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            <tr>
                                <td style="white-space: nowrap;"><strong>TOTAL DE PERCEPCIONES:</strong></td>
                                <td>${{ number_format(round($total_percepciones , 2), 2,'.', ',') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="30%">
                        <strong style="margin-bottom: 20px">DEDUCCIONES</strong>
                        <table width="80%" class="totales">
                            @php
                            $total_deducciones = 0;
                            @endphp
                            @foreach ($datos['conceptosIDs'] as $concepto)
                           
                            @if ($concepto->tipo == 0)
                            <tr>
                                <td style="white-space: nowrap;">{{$concepto->nombre_concepto}}</td>
                                <td style="white-space: nowrap;">
                                    @php
                                    
                                    $total_concepto = $datos['empleadoss'][0]['total_deduc'];
                                    $total_deducciones =array_sum($total_concepto)
                                   
                                    @endphp

                                </td>
                            </tr>
                            @endif
                            @endforeach
                            <tr>
                                <td style="white-space: nowrap;"><strong>TOTAL DE DEDUCCIONES:</strong></td>
                                <td>${{ number_format(round($total_deducciones , 2), 2, '.', ',')}}</td>
                            </tr>
                        </table>
                    </td>
                    <td valign="top">
                        <strong style="margin-bottom: 20px">TOTALES</strong> <br><br>
                        <table width="90%" class="totales">
                            <tr>
                                <td style="white-space: nowrap;"> <strong>TOTAL EN EFECTIVO</strong></td>
                                <td style="white-space: nowrap;">${{ number_format(round(($total_percepciones-$total_deducciones) , 2), 2,'.', ',')}}</td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;"> <strong>NETO PAGADO</strong></td>
                                <td style="white-space: nowrap;">${{ number_format(round(($total_percepciones-$total_deducciones) , 2), 2,'.', ',')}}</td>
                            </tr>
                            <tr>
                                <td style="white-space: nowrap;"> <strong>TOTAL GRAVABLE</strong></td>
                                <td style="white-space: nowrap;">${{ number_format(round($total_percepciones , 2), 2,'.', ',')}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>

</html>
<style>


</style>