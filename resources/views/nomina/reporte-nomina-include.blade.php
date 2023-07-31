
<table class="" id="empleadostbl" cellspacing="0" style="width:100%;border-collapse:collapse; ">
    <tr class="GridViewScrollHeader table w-100">
        <th>ID</th>
        <th>NOMBRE</th>
        <th class="text-nowrap">NO. EMPLEADO</th>
        <th>CATEGORIA</th>
        <th>DEPARTAMENTO</th>
        <th class="text-nowrap">SALARIO DIARIO</th>
        <th class="text-nowrap">SUELDO NETO</th>
        <th class="text-nowrap">SAL. DIARIO INTEGRADO</th>
        <th class="text-nowrap">DIAS DEL PERIODO</th>
        <th class="text-nowrap">DIAS PAGADOS</th>
        <th class="text-nowrap">FALTAS</th>

        @if( array_key_exists('dias_imss', Session::get('usuarioPermisos')) && (isset($dias_imss) && $dias_imss == 1))
            <th style="background:#343a40; color: #ffffff;" scope="col" class="text-nowrap text-center">DIAS IMSS</th>
        @endif

        @if($parametros_empresa->tipo_nomina!= 'solosindical')

            @if ($columnas1->count() > 0)
                @foreach ($columnas1 as $col)
                    <th class="text-nowrap" data-idConcepto="{{$col->id}}">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th class="text-nowrap">TOTAL PERCEPCIONES</th>

            @if ($columnas2->count() > 0)
                @foreach ($columnas2 as $col)
                    <th class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th class="text-nowrap">SUBSIDIO</th>
            <th class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th class="text-nowrap">IMPORTE DEPO FISCAL</th>

        @endif


        @if($parametros_empresa->tipo_nomina == 'solosindical' || $parametros_empresa->tipo_nomina == 'sindical')

            @if ($columnasSindical)
                @foreach ($columnasSindical as $col)
                    <th class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            @if ($columnaPVAC)
                <th  class="text-nowrap">PRIMA VACACIONAL</th>
            @endif
                <th class="text-nowrap">BENEFICIO SINDICAL</th>

            <th  class="text-nowrap">TOTAL PERCEPCIONES</th>

            {{-- Sindical Deducciones --}}
            @if (isset($rowConceptoISRAsimilados))
                <th class="text-nowrap">ISR</th>
            @else
            @endif
            @if ($columnasDEDUCC->count() > 0)
                @foreach ($columnasDEDUCC as $col)
                    <th  class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            @if (isset($columnas3) && count($columnas3) > 0)
                @foreach ($columnas3 as $col)
                    <th  class="text-nowrap">{{ strtoupper($col->nombre_concepto)}}</th>                        
                @endforeach
            @endif

            <th  class="text-nowrap">TOTAL DEDUCCIONES</th>
            <th  class="text-nowrap">IMPORTE DEPO SINDICAL</th>
            <th  class="text-nowrap">TOTAL A DISPERSAR</th>
        @endif
    </tr>




    @foreach ($empleados as $empleado)
        
        <tr class="GridViewScrollItem content" >
            <td>{{$empleado->id}}</td>
            <td style="font-size: 12px;"> {{strtoupper($empleado->nombre_completo)}}</td>
            <td style="text-align: center;">{{$empleado->numero_empleado}}</td>
            <td>{{$empleado->categoria->nombre}}</td>
            <td>{{$empleado->departamento->nombre}}</td>
            <td>${{number_format(round($empleado->salario_diario, 2),2,'.',',')}}</td>
            <td>${{round($empleado->sueldo_neto, 2)}}</td>
            <td>${{number_format(round($empleado->salario_diario_integrado, 2),2,'.',',')}}</td>
            <td>{{$periodo->dias_periodo}}</td>
            <td>{{round($empleado->dias_pagados, 4)}}</td>
            <td>{{round($empleado->faltas, 4)}}</td>
            

            @if($parametros_empresa->tipo_nomina != 'solosindical')
                @if ($columnas1->count() > 0)
                    @foreach ($columnas1 as $col)
                        @php  $columna = 'total'.$col->id;  @endphp
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{ number_format(round($empleado->rutinas->$columna,2),2,'.',',') }}</td>                        
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->total_percepcion_fiscal,2),2,'.',',') }}</td>

                @if ($columnas2->count() > 0)
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

            @if($parametros_empresa->tipo_nomina == 'solosindical' || $parametros_empresa->tipo_nomina == 'sindical')
                @if ($columnasSindical->count() > 0)
                    @foreach ($columnasSindical as $col)
                        @php  $columna = 'total'.$col->id;  @endphp
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">${{ number_format(round($empleado->rutinas->$columna,2),2,'.',',') }}</td>
                    @endforeach
                @endif

                @if ($columnaPVAC)
                    <td>${{ number_format(round($empleado->rutinas->bono_prima,2),2,'.',',') }}</td>
                @endif

                    <td data-idConcepto="beneficio_sindical" data-nombreConcepto="beneficio_sindical">${{ number_format(round($empleado->rutinas->beneficio_sindical, 2), 2,'.',',') }}</td>

                    <td>${{ number_format(round($empleado->rutinas->total_percepcion_sindical,2),2,'.',',') }}</td>

                {{-- Sindical Deducciones --}}

                @if ($columnasDEDUCC->count() > 0)
                    @foreach ($columnasDEDUCC as $col)
                        @php  $columna = 'total'.$col->id;  @endphp
                        <td>${{ number_format(round($empleado->rutinas->$columna,2),2,'.',',') }}</td>
                    @endforeach
                @endif

                @if (isset($columnas3) && count($columnas3) > 0)
                    @foreach ($columnas3 as $col)
                        @if (isset($empleado->columnas3))
                            <td>${{ number_format(round($empleado->columnas3->valor_concepto,2),2,'.',',') }}</td>
                        @else
                            @php  $columna = 'total'.$col->id;  @endphp
                            <td>${{ number_format(round($empleado->rutinas->$columna,2),2,'.',',') }}</td>
                        @endif
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->total_deduccion_sindical,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->total_percepcion_sindical,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->total_a_pagar,2),2,'.',',') }}</td>

            @endif
        </tr>
    @endforeach
</table>