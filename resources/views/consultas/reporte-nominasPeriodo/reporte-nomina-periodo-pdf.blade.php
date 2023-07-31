<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<p style="text-align: center;font-size:20pt !important;"><b>{{Session::get('empresa')['razon_social']}}</b></p>
<table style="width: 100%;text-align: center;" class="totales">
    <tr>
        <td>
            Periodo: <strong> {{$periodo->fecha_final_periodo}} </strong>
        </td>
        <td>
            Registro patronal: <strong>{{$registro_patronal}}</strong><br>
        </td>
        <td>RFC: <strong>{{Session::get('empresa')['rfc']}} </strong></td>
    </tr>
</table>

<p style="text-align: center;font-size:20pt !important;"><b>Reporte de la Nómina</b></p>
<table>
    <tr>
        <td>
            <strong>Periodo de pago:</strong> Del {{$periodo->fecha_inicial_periodo}} Al {{$periodo->fecha_final_periodo}} <br>
            <strong>Tipo de Nomina:</strong> {{$periodo->nombre_periodo}} <br>
            <strong>Clave:</strong> {{$periodo->numero_periodo}} <br>
        </td>
    </tr>
</table>
@php
    $total_percepciones=0;
    $total_deduccion=0;
    $total_neto_fiscal=0;
    $total_gravable=0;
    $percepciones=Array();
    $deducciones=Array();
    $tabla='<tr>';
    $tabla_conceptos='<tr>';
@endphp
<table style="font-size:10pt;">
    <tr>
        <th>Nombre</th>
        <th>Categoria</th>
        <th>Departamento</th>
        <th>Salario Diario</th>
        <th>Salario Diario Integrado</th>
        <th>Salario Dias Periodo</th>
        <th>Salario Dias Pagados</th>
    </tr>

        @foreach($conceptos as $key=>$concepto)
            @php
                $a=fmod($key,6);
            @endphp
            @if($a==0)
                @php
                    $tabla=$tabla." </tr> <tr> <th> ".$concepto->nombre_concepto." </th> ";
                @endphp
            @else
                @php
                    $tabla=$tabla." <th> ".$concepto->nombre_concepto." </th> ";
                @endphp
            @endif
        @endforeach
        @php
            $tabla=$tabla."</tr>";
           echo $tabla;
        @endphp

    <tr>
        <th>Total Percepcion</th>
        <th>Total Deduccion</th>
        <th>Total Neto</th>
    </tr>
    @foreach($empleados as $empleado)

        <tr>
            <td>{{$empleado->nombre}} {{$empleado->apaterno}}</td>
            <td>{{$empleado->categoria->nombre}} </td>
            <td>{{$empleado->departamento->nombre}} </td>
            <td>{{$empleado->salario_diario}}</td>
            <td>{{$empleado->salario_diario_integrado}}</td>
            <td>{{$periodo->dias_periodo}}</td>
            <td>{{$empleado->rutinas->dias_imss}}</td>
        </tr>

        @foreach ($conceptos as $key=>$col)
            @php
                $a=fmod($key,6);
            @endphp
            @if($a==0)
                @php
                    $tabla_conceptos=$tabla_conceptos." </tr> <tr> <td> ".number_format(round($empleado->rutinas->{'total'.$col->id}, 2), 2,'.',',')." </td> ";
                @endphp
            @else
                @php
                    $tabla_conceptos=$tabla_conceptos." <td> ".number_format(round($empleado->rutinas->{'total'.$col->id}, 2), 2,'.',',')." </td> ";
                @endphp
            @endif
        @endforeach
        @php
            $tabla_conceptos=$tabla_conceptos."</tr>";
           echo $tabla_conceptos;
        @endphp

        <tr>
            <td>${{$empleado->rutinas->total_percepcion_fiscal}}</td>
            <td>${{$empleado->rutinas->total_deduccion_fiscal}}</td>
            <td>${{$empleado->rutinas->neto_fiscal}}</td>
        </tr>
        @php
            $total_neto_fiscal=$total_neto_fiscal+$empleado->rutinas->neto_fiscal;
            $total_gravable=$total_gravable+$empleado->rutinas->total_gravado;
        @endphp
    @endforeach

</table>

<div style="page-break-after:always;"></div>
<p style="text-align: center;font-size:20pt !important;"><b>{{Session::get('empresa')['razon_social']}}</b></p>

<table>
    <tr>
        <td>
            <strong>Periodo:</strong> {{$periodo->fecha_final_periodo}}<br>
            <strong>RFC:</strong> {{Session::get('empresa')['rfc']}}<br>
            <strong>Registro patronal:</strong> {{$registro_patronal}}<br>
            <strong>{{formatoAFecha(date('Y-m-d'))}} </strong>

        </td>
    </tr>
</table>
<p style="text-align: center;font-size:20pt !important;"><b>Reporte de la Nómina</b></p>
<table>
    <tr>
        <td>
            <strong>Periodo de pago:</strong> Del {{$periodo->fecha_inicial_periodo}} Al {{$periodo->fecha_final_periodo}} <br>
            <strong>Tipo de Nomina:</strong> {{$periodo->nombre_periodo}} <br>
            <strong>Clave:</strong> {{$periodo->numero_periodo}} <br>
        </td>
    </tr>
</table>

<div>
    <table style="text-align: justify; margin: auto;">
        @foreach ($conceptos as $col)

            @php
                $suma_percepciones=0;
                $suma_deducciones=0;
            @endphp
            @foreach($empleados as $empleado)

                @if($empleado->rutinas->{'total'.$col->id} >0 && $col->tipo==0)
                    @php
                        $suma_percepciones=$suma_percepciones+$empleado->rutinas->{'total'.$col->id};
                    @endphp
                @endif
                @if($empleado->rutinas->{'total'.$col->id} >0 && $col->tipo==1)
                    @php
                        $suma_deducciones=$suma_deducciones+$empleado->rutinas->{'total'.$col->id};
                    @endphp
                @endif
            @endforeach
            @if($suma_percepciones>0)
                @php
                    array_push($percepciones,[$col->nombre_concepto,$suma_percepciones])
                @endphp
            @endif
            @if($suma_deducciones>0)
                @php
                    array_push($deducciones,[$col->nombre_concepto,$suma_deducciones])
                @endphp
            @endif
        @endforeach
        <br>
        <tr>
            <th>PERCEPCIONES</th>
        </tr>
        <tr>
            <td>
                @foreach($percepciones as $per)
                    {{$per[0]}}: {{$per[1]}} <br>
                    @php
                        $total_percepciones=$total_percepciones+$per[1];
                    @endphp
                @endforeach
                Total de Percepciones: ${{$total_percepciones}}
            </td>
        </tr>
        <br>
        <tr>
            <th>DEDUCIONES</th>
        </tr>
        <tr>
            <td>
                @foreach($deducciones as $dec)
                    {{$dec[0]}}: {{$dec[1]}} <br>
                    @php
                        $total_deduccion=$total_deduccion+$dec[1];
                    @endphp
                @endforeach
                Total de Deducciones: ${{$total_deduccion}}
            </td>
        </tr>
        <br>
        <tr>
            <th>TOTALES</th>
        </tr>
        <tr>
            <td>
                <strong>TOTAL EN EFECTIVO:</strong> ${{ number_format(round($total_neto_fiscal, 2), 2,'.',',') }} <br>
                <strong>NETO PAGADO:</strong> ${{number_format(round($total_neto_fiscal, 2), 2,'.',',') }} <br>
                <strong>Total gravable:</strong> ${{number_format(round($total_gravable, 2), 2,'.',',') }}

            </td>
        </tr>
    </table>
</div>


</body>
</html>