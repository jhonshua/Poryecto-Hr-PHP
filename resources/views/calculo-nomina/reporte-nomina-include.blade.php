
<table class="" id="empleadostbl" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr class="GridViewScrollHeader table w-100">
        <th style="background:#fbba00;">ID</th>
        <th style="background:#fbba00;">NOMBRE</th>
        <th style="background:#fbba00;" class="text-nowrap">NO. EMPLEADO</th>
        <th style="background:#fbba00;">CATEGORIA</th>
        <th style="background:#fbba00;">DEPARTAMENTO</th>
        <th style="background:#fbba00;"class="text-nowrap">FECHA INGRESO</th>
        @if($misc['haySedes'])<th style="background:#fbba00;">SEDE</th>@endif
        <th style="background:#fbba00;" class="text-nowrap">SALARIO DIARIO</th>
        <th style="background:#fbba00;" class="text-nowrap">SUELDO NETO</th>
        <th style="background:#fbba00;" class="text-nowrap">SAL. DIARIO INTEGRADO</th>
        <th style="background:#fbba00;" class="text-nowrap">DIAS DEL PERIODO</th>
        <th style="background:#fbba00;" class="text-nowrap">DIAS PAGADOS</th>
        <th style="background:#fbba00;" class="text-nowrap">FALTAS</th>


        @if(strtolower($parametros_empresa->tipo_nomina) != 'solosindical')

            @if ($columnas1->count() > 0)
                @foreach ($columnas1 as $col)
                    <th style="background:#fbba00;" class="text-nowrap" data-idConcepto="{{$col->id}}">{{ strtoupper($col->nombre_concepto)}}</th>
                @endforeach
            @endif

            <th style="background:#fbba00;" class="text-nowrap">TOTAL PERCEPCIONES</th>

            @if ($columnas2->count() > 0)
                @foreach ($columnas2 as $col)
                    <th style="background:#fbba00;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>
                @endforeach
            @endif

            <th style="background:#fbba00;" class="text-nowrap">SUBSIDIO</th>
            <th style="background:#fbba00;" class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th style="background:#fbba00;" class="text-nowrap">IMPORTE DEPO FISCAL</th>

        @endif


        @if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical')

            @if($misc['faltas_s'])
                <th style="background:#fbba00;">FALTAS_S</th>
            @endif

            @if ($columnasSindical)
                @foreach ($columnasSindical as $col)
                    <th style="background:#fbba00;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>
                @endforeach
            @endif

            @if ($columnaPVAC || $rowsidpvacAnti)
                <th style="background:#fbba00;" class="text-nowrap">PRIMA VACACIONAL</th>
            @endif

            @if ($rowsidprdom)
                <th style="background:#fbba00;" class="text-nowrap">PRIMA DOMINICAL</th>
            @endif

            <th style="background:#fbba00;" class="text-nowrap">BENEFICIO SINDICAL</th>

            <th style="background:#fbba00;" class="text-nowrap">TOTAL PERCEPCIONES</th>

            {{-- Sindical Deducciones --}}
            @endif
            @if ($columnasDEDUCC)
                @foreach ($columnasDEDUCC as $col)
                    <th style="background:#fbba00;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>
                @endforeach
            @endif

            @if (count($columnas3) > 0)
                @foreach ($columnas3 as $col)
                    <th style="background:#fbba00;" class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>
                @endforeach
            @endif

            <th style="background:#fbba00;" class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th style="background:#fbba00;" class="text-nowrap">IMPORTE DEPO SINDICAL</th>
            <th style="background:#fbba00;" class="text-nowrap">TOTAL A DISPERSAR</th>
    </tr>




    @foreach ($empleados as $empleado)
        <tr class="GridViewScrollItem content" >
            <td>{{$empleado->id}}</td>
            <td style="font-size: 12px;"> {{strtoupper($empleado->nombre_completo)}}</td>
            <td style="text-align: center;">{{$empleado->numero_empleado}}</td>
            <td>{{$empleado->categoria->nombre}}</td>
            <td>{{$empleado->departamento->nombre}}</td>
            <td>{{formatoAFecha($empleado->fecha_antiguedad)}}</td>
            <td>{{$empleado->sede_nombre ?? 'N/A'}}</td>
            <td>${{number_format(round($empleado->salario_diario, 2),2,'.',',')}}</td>
            <td>${{round($empleado->sueldo_neto, 2)}}</td>
            <td>${{number_format(round($empleado->salario_diario_integrado, 2),2,'.',',')}}</td>
            <td>{{$periodo->dias_periodo}}</td>
            <td>{{round($empleado->dias_pagados, 4)}}</td>
            <td>{{round($empleado->faltas, 4)}}</td>

            @if(strtolower($parametros_empresa->tipo_nomina) != 'solosindical')
                @if ($columnas1)
                    @foreach ($columnas1 as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{ number_format(round($empleado->rutinas->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->total_percepcion_fiscal,2),2,'.',',') }}</td>

                @if ($columnas2)
                    @foreach ($columnas2 as $col)
                        @php  $columna = 'total'.$col->id;  @endphp
                        @if ($col->rutinas == 'ISR')
                            @if ($empleado->rutinas->$columna < 0)
                                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">$0.00</td>
                            @else
                                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{number_format(round($empleado->rutinas->$columna,2),2,'.',',')}}</td>
                            @endif
                        @else
                            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{number_format(round($empleado->rutinas->$columna,2),2,'.',',')}}</td>
                        @endif
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->subsidio,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->total_deduccion_fiscal,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->neto_fiscal,2),2,'.',',') }}</td>
            @endif

            @if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical')

                @if($misc['faltas_s'])
                    <td data-idConcepto="{{$misc['faltas_s']->id}}" data-nombreConcepto="{{$misc['faltas_s']->nombre_concepto}}">{{intval($empleado->rutinas->{'valor'.$misc['faltas_s']->id})}}</td>
                @endif

                @if ($columnasSindical)
                    @foreach ($columnasSindical as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{ number_format(round($empleado->rutinas->{'total'.$col->id}, 2), 2,'.',',') }}</td>
                    @endforeach
                @endif

                @if ($columnaPVAC || $rowsidpvacAnti)
                    <td data-idConcepto="bono_prima" data-nombreConcepto="bono_prima">${{ number_format(round($empleado->rutinas->bono_prima,2),2,'.',',') }}</td>
                @endif

                @if ($rowsidprdom)
                    <td data-idConcepto="bono_prima_dominical" data-nombreConcepto="bono_prima_dominical">${{ number_format(round($empleado->rutinas->bono_prima_dom, 2), 2,'.',',') }}</td>
                @endif

                <td data-idConcepto="beneficio_sindical" data-nombreConcepto="beneficio_sindical">${{ number_format(round($empleado->rutinas->beneficio_sindical, 2), 2,'.',',') }}</td>


                <td>${{ number_format(round($empleado->rutinas->total_percepcion_sindical,2),2,'.',',') }}</td>

                {{-- Sindical Deducciones --}}

                @if ($columnasDEDUCC)
                    @foreach ($columnasDEDUCC as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{ number_format(round($empleado->rutinas->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

                @if (count($columnas3) > 0)
                    @foreach ($columnas3 as $col)
                        <td>${{ number_format(round($empleado->saldosNomina[$col->id], 2), 2,'.',',') }}</td>
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->total_deduccion_sindical, 2), 2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->neto_sindical, 2), 2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->importe_total, 2), 2,'.',',') }}</td>

            @endif
        </tr>
    @endforeach
</table>