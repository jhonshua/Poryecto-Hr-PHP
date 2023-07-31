<table class="" id="empleadostbl" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr class="GridViewScrollHeader table w-100">
        <th style="background:#fbba00;">ID</th>
        <th style="background:#fbba00;">NOMBRE</th>
        <th style="background:#fbba00;" class="text-nowrap">NO. EMPLEADO</th>
        <th style="background:#fbba00;">CATEGORIA</th>
        <th style="background:#fbba00;">DEPARTAMENTO</th>
        <th style="background:#fbba00;" class="text-nowrap">FECHA INGRESO</th>
        @if($misc['haySedes'])
            <th style="background:#fbba00;">SEDE</th>
        @endif
        <th style="background:#fbba00;" class="text-nowrap">SALARIO DIARIO</th>
        <th style="background:#fbba00;" class="text-nowrap">SUELDO NETO</th>
        <th style="background:#fbba00;" class="text-nowrap">SAL. DIARIO INTEGRADO</th>
        <th style="background:#fbba00;" class="text-nowrap">DIAS DEL PERIODO</th>
        <th style="background:#fbba00;" class="text-nowrap">DIAS PAGADOS</th>
        <th style="background:#fbba00;" class="text-nowrap">FALTAS</th>

        @if ($rowsiddiasretro->count() >0)
            <th style="background:#fbba00;" class="text-nowrap">DIAS RETROACTIVO</th>
        @endif

        @if ($rowsiddiasausentismo->count() >0)
            <th style="background:#fbba00;" class="text-nowrap">DIAS AUSENTISMO</th>
        @endif

        @if ($rowsidfaltasproporcion->count() >0)
            <th style="background:#fbba00;" class="text-nowrap">FALTAS CON PROPORCION</th>
        @endif

        @if ($rowsiddiasvaca->count() >0)
            <th style="background:#fbba00;" class="text-nowrap">DIAS DE VACACIONES</th>
        @endif


        @if(strtolower($parametros_empresa->tipo_nomina) != 'solosindical')

            @if ($columnas1->count() > 0)
                @foreach ($columnas1 as $col)
                    <th style="background:#fbba00;" class="text-nowrap"
                        data-idConcepto="{{$col->id}}">{{ strtoupper($col->nombre_concepto)}}</th>
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

        <th></th>
        <th style="background:#fbba00;" class="text-nowrap">IMSS</th>
        <th style="background:#fbba00;" class="text-nowrap">RCV PATRONAL</th>
        <th style="background:#fbba00;" class="text-nowrap">INFONAVIT</th>
        <th style="background:#fbba00;" class="text-nowrap">Impuesto Sobre
            Nomina({{Session::get('empresa.parametros')[0]['porcentaje_nomina']}} %)
        </th>
        <th style="background:#fbba00;" class="text-nowrap">Prestaciones Extras</th>
        <th style="background:#fbba00;" class="text-nowrap">Comision</th>
        <th style="background:#fbba00;" class="text-nowrap">Subtotal</th>
        <th style="background:#fbba00;" class="text-nowrap">IVA</th>
        <th style="background:#fbba00;" class="text-nowrap">Costo por Trabajador</th>

        <th></th>
        <th style="background:#fbba00;" class="text-nowrap">TOTAL A DISPERSAR</th>
        <th style="background:#fbba00;" class="text-nowrap">SUELDO {{$periodo->nombre_periodo}}</th>
        <th style="background:#fbba00;" class="text-nowrap">DIFERENCIAS</th>
        <th style="background:#fbba00;" class="text-nowrap">OBSERVACIONES</th>

    </tr>


    @foreach ($empleados as $empleado)
        <tr class="GridViewScrollItem content">
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

            @if ($rowsiddiasretro->count() >0)
                <td>{{round($empleado->rutinas->{'valor'.$rowsiddiasretro[0]->id},2)}}</td>
            @endif

            @if ($rowsiddiasausentismo->count() >0)
                <td>{{round($empleado->rutinas->{'valor'.$rowsiddiasausentismo[0]->id},2)}}</td>
            @endif

            @if ($rowsidfaltasproporcion->count() >0)
                <td>{{round($empleado->rutinas->{'total'.$rowsidfaltasproporcion[0]->id},2)}}</td>
            @endif

            @if ($rowsiddiasvaca->count() >0)
                <td>{{round($empleado->rutinas->{'valor'.$rowsiddiasvaca[0]->id},2)}}</td>
            @endif


            @if(strtolower($parametros_empresa->tipo_nomina) != 'solosindical')
                @if ($columnas1)
                    @foreach ($columnas1 as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                            ${{ number_format(round($empleado->rutinas->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->total_percepcion_fiscal,2),2,'.',',') }}</td>

                @if ($columnas2)
                    @foreach ($columnas2 as $col)
                        @php  $columna = 'total'.$col->id;  @endphp
                        @if ($col->rutinas == 'ISR')
                            @if ($empleado->rutinas->$columna < 0)
                                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                                    $0.00
                                </td>
                            @else
                                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                                    ${{number_format(round($empleado->rutinas->$columna,2),2,'.',',')}}</td>
                            @endif
                        @else
                            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                                ${{number_format(round($empleado->rutinas->$columna,2),2,'.',',')}}</td>
                        @endif
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->subsidio,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->total_deduccion_fiscal,2),2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->neto_fiscal,2),2,'.',',') }}</td>
            @endif

            @if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical')

                @if($misc['faltas_s'])
                    <td data-idConcepto="{{$misc['faltas_s']->id}}"
                        data-nombreConcepto="{{$misc['faltas_s']->nombre_concepto}}">{{intval($empleado->rutinas->{'valor'.$misc['faltas_s']->id})}}</td>
                @endif

                @if ($columnasSindical)
                    @foreach ($columnasSindical as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                            ${{ number_format(round($empleado->rutinas->{'total'.$col->id}, 2), 2,'.',',') }}</td>
                    @endforeach
                @endif

                @if ($columnaPVAC || $rowsidpvacAnti)
                    <td data-idConcepto="bono_prima" data-nombreConcepto="bono_prima">
                        ${{ number_format(round($empleado->rutinas->bono_prima,2),2,'.',',') }}</td>
                @endif

                @if ($rowsidprdom)
                    <td data-idConcepto="bono_prima_dominical" data-nombreConcepto="bono_prima_dominical">
                        ${{ number_format(round($empleado->rutinas->bono_prima_dom, 2), 2,'.',',') }}</td>
                @endif

                <td data-idConcepto="beneficio_sindical" data-nombreConcepto="beneficio_sindical">
                    ${{ number_format(round($empleado->rutinas->beneficio_sindical, 2), 2,'.',',') }}</td>


                <td>${{ number_format(round($empleado->rutinas->total_percepcion_sindical,2),2,'.',',') }}</td>

                {{-- Sindical Deducciones --}}

                @if ($columnasDEDUCC)
                    @foreach ($columnasDEDUCC as $col)
                        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}">
                            ${{ number_format(round($empleado->rutinas->{'total'.$col->id},2),2,'.',',') }}</td>
                    @endforeach
                @endif

                @if (count($columnas3) > 0)
                    @foreach ($columnas3 as $col)
                        <td>${{ number_format(round($empleado->saldosNomina[$col->id], 2), 2,'.',',') }}</td>
                    @endforeach
                @endif

                <td>${{ number_format(round($empleado->rutinas->total_deduccion_sindical, 2), 2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->rutinas->neto_sindical, 2), 2,'.',',') }}</td>

                <td></td>

                <td>${{ number_format(round($empleado->totales->TotalImssConProvision, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->RCVTotalConProvision, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->InfovaitTotalConProvision, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->isnporemple, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->costoPrestacion, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->costocomision, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->subtotalporemple, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->Ivaporemple, 2), 2,'.',',') }}</td>

                <td>${{ number_format(round($empleado->totales->costoporemple, 2), 2,'.',',') }}</td>

                <td></td>

                <td>${{ number_format(round($empleado->rutinas->importe_total, 2), 2,'.',',') }}</td>
                <td>${{ number_format(round($empleado->sueldo_neto, 2), 2,'.',',') }}</td>
                <td>
                    ${{ number_format(round($empleado->sueldo_neto - $empleado->rutinas->importe_total, 2), 2,'.',',') }}</td>
                <td></td>
            @endif

        </tr>

        </tr>
    @endforeach

    {{-- TOTALES --}}

    @php
        $faltasHoras=array();
        $diaspagadosHoras=array();

    @endphp

    <tr class="GridViewScrollItem content">
        <td colspan="{{($misc['haySedes']) ? 6 : 5}}">&nbsp;</td>
        <td>TOTAL</td>
        <td style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->salario_diario);
            }) , 2),2,'.',',')}}
        </td>
        <td style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->sueldo_neto);
            }) , 2),2,'.',',')}}
        </td>
        <td style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->salario_diario_integrado);
            }) , 2),2,'.',',')}}
        </td>
        <td style="font-weight:bold; font-size: 16px">
            {{ $empleados->sum(function ($empleado) use($periodo){
                return intval($periodo->dias_periodo);
            })}}
        </td>

        @if($misc['sueldo_x_hr'])
            @php
                $sumaH=array_sum($faltasHoras);
                $sumaD=array_sum($diaspagadosHoras);
            @endphp
            <td style="font-weight:bold; font-size: 16px">
                {{ $sumaD }}
            </td>
            <td style="font-weight:bold; font-size: 16px">
                {{ $sumaH }}
            </td>

        @else
            <td style="font-weight:bold; font-size: 16px">
                {{ $empleados->sum(function ($empleado) {
                    return floatval($empleado->dias_pagados);
                })}}
            </td>
            <td style="font-weight:bold; font-size: 16px">
                {{ $empleados->sum(function ($empleado) {
                    return intval($empleado->faltas);
                })}}
            </td>
        @endif

        @if($rowsiddiasretro->count() >0)
            @php  $columna = 'valor'.$rowsiddiasretro[0]->id; @endphp
            <td style="font-weight:bold; font-size: 16px">
                {{ round($empleados->sum(function ($empleado) use($columna) {
                    return floatval($empleado->rutinas->$columna);
                }) ,2) }}
            </td>
        @endif

        @if ($rowsiddiasausentismo->count() >0)
            @php  $columna = 'valor'.$rowsiddiasausentismo[0]->id; @endphp
            <td style="font-weight:bold; font-size: 16px">
                {{ round($empleados->sum(function ($empleado) use($columna) {
                    return floatval($empleado->rutinas->$columna);
                }) ,2) }}
            </td>
        @endif

        @if ($rowsidfaltasproporcion->count() >0)
            @php  $columna = 'total'.$rowsidfaltasproporcion[0]->id; @endphp
            <td style="font-weight:bold; font-size: 16px">
                {{ round($empleados->sum(function ($empleado) use($columna) {
                    return floatval($empleado->rutinas->$columna);
                }) ,2) }}
            </td>
        @endif

        @if ($rowsiddiasvaca->count() >0)
            @php  $columna = 'valor'.$rowsiddiasvaca[0]->id; @endphp
            <td style="font-weight:bold; font-size: 16px">
                {{ round($empleados->sum(function ($empleado) use($columna) {
                    return floatval($empleado->rutinas->$columna);
                }) ,2) }}
            </td>
        @endif

        @foreach ($columnas1 as $col)
            @php  $columna = 'total'.$col->id; @endphp
            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                style="font-weight:bold; font-size: 16px">
                ${{ number_format(round($empleados->sum(function ($empleado) use($columna) {
                    return floatval($empleado->rutinas->$columna);
                }) ,2),2,'.',',') }}
            </td>
        @endforeach

        <td style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->total_percepcion_fiscal);
            }) , 2),2,'.',',')}}
        </td>

        @if ($columnas2->count() > 0)
            @foreach ($columnas2 as $col)
                @php  $columna = 'total'.$col->id;  @endphp
                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                    style="font-weight:bold; font-size: 16px">
                    ${{ number_format(round($empleados->sum(function ($empleado) use($columna, $col) {
                        return ($col->rutinas == 'ISR' && $empleado->rutinas->$columna < 0) ? 0 : floatval($empleado->rutinas->$columna);
                    }) ,2),2,'.',',') }}
                </td>
            @endforeach
        @endif

        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px; ">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->subsidio);
            }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{ number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->total_deduccion_fiscal);
            }), 2),2,'.',',') }}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->neto_fiscal);
            }) , 2),2,'.',',')}}
        </td>


        @if($misc['faltas_s'])

            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                style="font-weight:bold; font-size: 16px">
                @php
                    $v = $misc['faltas_s']->id;
                @endphp

                ${{number_format(round($empleados->sum(function ($empleado) use ($v ) {
                        return ($empleado->rutinas->{'valor'.$v} != null && $empleado->rutinas->{'valor'.$v} != "")? floatval($empleado->rutinas->{'valor'.$v}) : 0;
                    }
                    ) , 2),2,'.',',')
                    }}
            </td>
        @endif

        @if(strtolower(Session::get('empresa.parametros')[0]['tipo_nomina']) == 'solosindical' || strtolower(Session::get('empresa.parametros')[0]['tipo_nomina']) == 'sindical')
            @if ($columnasSindical)
                @foreach ($columnasSindical as $col)
                    @php  $columna = 'total'.$col->id;  @endphp
                    <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                        style="font-weight:bold; font-size: 16px">
                        ${{ number_format(round($empleados->sum(function ($empleado) use($columna, $col) {
                            return ($empleado->rutinas->$columna == 'ISR' && $empleado->rutinas->$columna < 0) ? 0 : floatval($empleado->rutinas->$columna);
                        }) ,2),2,'.',',') }}
                    </td>
                @endforeach
            @endif

            @if ($columnaPVAC != null || $rowsidpvacAnti)
                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                    style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->rutinas->bono_prima);
                    }) , 2),2,'.',',')}}
                </td>
            @endif

            @if ($rowsidprdom != null)
                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                    style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->rutinas->bono_prima_dom);
                    }) , 2),2,'.',',')}}
                </td>
            @endif

            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="beneficio_sindical"
                style="font-weight:bold; font-size: 16px">
                ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->beneficio_sindical);
            }) , 2),2,'.',',')}}
            </td>

            @if($rowConceptoDescuento75 != null && (is_array($rowConceptoDescuento75) && $rowConceptoDescuento75->count() >0) )
                <td data-idConcepto="{{$col->id}}" data-nombreConcepto="beneficio_sindical"
                    style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->rutinas->{'total'.$rowConceptoDescuento75->id});
                }) , 2),2,'.',',')}}
                </td>
            @endif

            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                style="font-weight:bold; font-size: 16px">
                ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->total_percepcion_sindical);
            }) , 2),2,'.',',')}}
            </td>

            {{-- Sindical Deducciones --}}
            @if ($columnasDEDUCC->count() > 0)
                @foreach ($columnasDEDUCC as $col)
                    @php  $columna = 'total'.$col->id;  @endphp
                    <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
                        style="font-weight:bold; font-size: 16px">
                        ${{ number_format(round($empleados->sum(function ($empleado) use($columna, $col) {
                        return ($empleado->rutinas == 'ISR' && $empleado->rutinas->$columna < 0) ? 0 : floatval($empleado->rutinas->$columna);
                    }) ,2),2,'.',',') }}
                    </td>
                @endforeach
            @endif

            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="total_deduccion_sindical"
                style="font-weight:bold; font-size: 16px">
                ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->total_deduccion_sindical);
            }) , 2),2,'.',',')}}
            </td>

            <td data-idConcepto="{{$col->id}}" data-nombreConcepto="neto_sindical"
                style="font-weight:bold; font-size: 16px">
                ${{number_format(round($empleados->sum(function ($empleado) {
                return floatval($empleado->rutinas->neto_sindical);
            }) , 2),2,'.',',')}}
            </td>
        @endif

        <td width="100px">&nbsp;</td>
        @if(strtolower(Session::get('empresa.parametros')[0]['tipo_nomina']) != 'solosindical')

            @if(Session::get('empresa.parametros')[0]['provision_aguinaldo'] == 1 && Session::get('empresa.parametros')[0]['provision_prima_vacacional'] == 1 )

                <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->totales->valorprovisionAguiEmpleado);
                    }) , 2),2,'.',',')}}
                </td>

                <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->totales->valorprovisionPrimvacaEmpleado);
                    }) , 2),2,'.',',')}}
                </td>

            @elseif(Session::get('empresa.parametros')[0]['provision_aguinaldo'] == 0 && Session::get('empresa.parametros')[0]['provision_prima_vacacional'] == 1 )
                <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->totales->valorprovisionPrimvacaEmpleado);
                    }) , 2),2,'.',',')}}
                </td>

            @elseif(Session::get('empresa.parametros')[0]['provision_aguinaldo'] == 1 && Session::get('empresa.parametros')[0]['provision_prima_vacacional'] == 0 )

                <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
                    ${{number_format(round($empleados->sum(function ($empleado) {
                        return floatval($empleado->totales->valorprovisionAguiEmpleado);
                    }) , 2),2,'.',',')}}
                </td>
            @endif

        @endif

        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->TotalImssConProvision);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->RCVTotalConProvision);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->InfovaitTotalConProvision);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->isnporemple);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->costoPrestacion);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->costocomision);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->subtotalporemple);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->Ivaporemple);
                }) , 2),2,'.',',')}}
        </td>
        <td data-idConcepto="{{$col->id}}" data-nombreConcepto="{{$col->nombre_concepto}}"
            style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->totales->costoporemple);
                }) , 2),2,'.',',')}}
        </td>

        <td>&nbsp;</td>
        <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->rutinas->importe_total);
                }) , 2),2,'.',',')}}
        </td>

        <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval($empleado->sueldo_neto);
                }) , 2),2,'.',',')}}
        </td>

        <td data-idConcepto="" data-nombreConcepto="" style="font-weight:bold; font-size: 16px">
            ${{number_format(round($empleados->sum(function ($empleado) {
                    return floatval(($empleado->sueldo_neto - $empleado->rutinas->importe_total));
                }) , 2),2,'.',',')}}
        </td>

</table>