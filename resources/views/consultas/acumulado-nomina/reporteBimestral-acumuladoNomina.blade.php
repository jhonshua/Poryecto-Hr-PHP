@php

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

            @font-face  {
                font-family: "Maven Pro Light 300 Regular";
                src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot") }}'); 
                src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot?#iefix") }}') format("embedded-opentype"), 
                url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff2") }}') format("woff2"),
                url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff") }}') format("woff"), 
                url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.ttf") }}') format("truetype"),
                url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.svg#Maven Pro Light 300 Regular") }}') format("truetype")
            }

            body *{
                /* font-family:"Maven Pro Light 300 Regular" ; */
                font-family: Helvetica;
                font-size: 11px;
            }

            h2{
                font-size: 18px;
                font-family:"Helvetica" !important;
                font-weight: normal;
            }

            .container{
                max-width: 100%;
            }

            .table{
                font-size: 8px;
            }

            table th{
                padding: 5px;
                background-color: #000;
                color: white;
                font-family:"Helvetica" !important;
                font-size: 8px;
                font-weight: normal;
            }

            table tr{
                border-bottom: 3px solid #888;
            }


            table td{
                font-size: 8px;
            }

            strong{
                font-family: Helvetica !important;
            }

            table.totales{
                font-size: 16px;
            }

            table.totales td{
                font-size: 12px;
            }

        </style>
    </head>
    <body id="app">
        <div class="row">

            <div class="col-md-12">
                <h2 style="text-align: center;"><b>{{Session::get('empresa')['razon_social']}}</b></h2>

                <table style="width: 120%">
                    <tr>
                        <td>
                            Registro patronal: <strong>{{$datos['registro_patronal']}}</strong><br>
                            Periodo <strong> {{ $datos['periodo'] }} </strong> 
                        </td>
                        <td><strong>Reporte de la Nómina-Acumulados</strong></td>
                        <td>
                            FECHA: <strong>{{formatoAFecha(date('Y-m-d'))}} </strong><br>
                            RFC: <strong>{{Session::get('empresa')['rfc']}} </strong>
                        </td>
                    </tr>
                </table>

                <table class="table table-striped table-hover mt-4">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>NOMBRE</th>
                            <th>CATEGORÍA</th>
                            <th>DEPTO.</th>
                            <th>SAL. DIARIO</th>
                            <th>SAL D.INT.</th>
                            <th>DÍAS PERIODO</th>
                            <th>DÍAS PAGADOS</th>
                            @foreach ($datos['conceptosIDs'] as $concepto)
                                <th>{{strtoupper($concepto->nombre_corto)}}</th>
                            @endforeach
                            <th>TOTAL PERCEP.</th>
                            <th>TOTAL DEDUCC.</th>
                            <th>NETO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datos['empleados'] as $emp)
                                
                            <tr>
                                <td>{{$emp->id}}</td>
                                <td>{{$emp->nombre_completo}}</td>
                                <td>{{ optional($emp->categoria)->nombre }}</td>
                                <td>{{ optional($emp->departamento)->nombre }}</td>
                                <td>${{number_format(round($emp->salario_diario, 2), 2,'.',',')}}</td>
                                <td>${{number_format(round($emp->salario_diario_integrado, 2), 2,'.',',')}}</td>
                                <td>{{$emp->dias_periodo}}</td>
                                <td>{{$emp->dias_pagados}}</td>
                                @foreach ($datos['conceptosIDs'] as $concepto)
                                    <td style="white-space: nowrap;">${{number_format(round($emp->totales['total'.$concepto->id], 2), 2,'.',',')}}</td>
                                @endforeach
                                <td style="white-space: nowrap;">${{number_format(round($emp->totales['total_percepcion_fiscal'], 2), 2,'.',',')}}</td>
                                <td style="white-space: nowrap;">${{number_format(round($emp->totales['total_deduccion_fiscal'], 2), 2,'.',',')}}</td>
                                <td style="white-space: nowrap;">${{number_format(round($emp->totales['neto_fiscal'], 2), 2,'.',',')}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>


                <div style="page-break-after:always;"></div>


                <h2 style="text-align: center;"><b>{{Session::get('empresa')['razon_social']}}</b></h2>

                <table style="width: 120%">
                    <tr>
                        <td>
                            Registro patronal: <strong>{{$datos['registro_patronal']}}</strong><br>
                            
                            Periodo <strong> {{ $datos['periodo'] }} </strong> 
                        </td>
                        <td><strong>Reporte de la Nómina-Acumulados</strong></td>
                        <td>
                            FECHA: <strong>{{formatoAFecha(date('Y-m-d'))}} </strong><br>
                            RFC: <strong>{{Session::get('empresa')['rfc']}} </strong>
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
                                    @if ($concepto->tipo == 0)
                                        <tr>
                                            <td style="white-space: nowrap;">{{strtoupper($concepto->nombre_concepto)}}</td>
                                            <td style="white-space: nowrap;">
                                                @php
                                                    $total_concepto = $datos['empleados']->sum(function ($empleado) use($concepto) {
                                                        return floatval($empleado->totales['total'.$concepto->id]);
                                                    });
                                                    $total_percepciones += $total_concepto;
                                                @endphp
                                                ${{number_format(round($total_concepto, 2) ,2,'.',',')}}
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
                                    @if ($concepto->tipo == 1)
                                        <tr>
                                            <td style="white-space: nowrap;">{{strtoupper($concepto->nombre_concepto)}}</td>
                                            <td style="white-space: nowrap;">
                                                @php
                                                    $total_concepto = $datos['empleados']->sum(function ($empleado) use($concepto, $total_deducciones) {
                                                        $total_deducciones += floatval($empleado->totales['total'.$concepto->id]);
                                                        return floatval($empleado->totales['total'.$concepto->id]);
                                                    });
                                                    $total_deducciones += $total_concepto;
                                                @endphp
                                                ${{number_format(round($total_concepto , 2),2,'.',',')}}
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