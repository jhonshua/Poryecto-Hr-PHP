
<table style="background:#F0C018;">
    <tr>
        <td>RAZÓN SOCIAL:</td>
        <td>{{Session::get('empresa')['razon_social']}}</td>
    </tr>
    <tr>
        <td>RFC:</td>
        <td><strong>{{Session::get('empresa')['rfc']}}</strong></td>
    </tr>
    <tr>
        <td>Periodo:</td>
        <td><strong>{{$periodo->numero_periodo}}, del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</strong></td>
    </tr>
    <tr>
        <td>Fecha del reporte:</td>
        <td>{{ formatoAFecha(date('Y-m-d H:i:s'), true) }}</td>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
</table>

@include('calculo-nomina.reporte-nomina-include')

<table>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr>
        <td></td>
        <td colspan="3">
            <table class="table table-striped mt-5 mr-3">
                <tr>
                    <td colspan="2" style="background:#343a40; color: #ffffff; text-align:center; font-size:16px">DETALLE DE FACTURACIÓN</td>
                </tr>
                <tr>
                    <td>NÓMINA</td>
                    <td>${{ number_format(round($totales['neto_fiscal_real'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td>BENEFICIO SOCIAL</td>
                    <td>${{ number_format(round($totales['total_percepcion_sindical'],2),2,'.',',') }}</td>
                </tr>
                @if($parametros_empresa->provision_aguinaldo==1 && $periodo->aux_agui==0)
                    <tr>
                        <td>PROVISION AGUINALDO</td>
                        <td>${{ number_format(round($totales['valor_provision_aguinaldo'],2),2,'.',',') }}</td>
                    </tr>
                @endif

                @if($parametros_empresa->provision_prima_vacacional==1 && $periodo->aux_prima_vacacional==0)
                    <tr>
                        <td>PROVISION PRIMA VACACIONAL</td>
                        <td>${{ number_format(round($totales['valor_provision_prima_vacacional'],2),2,'.',',') }}</td>
                    </tr>
                @endif

                <tr>
                    <td>ANTICIPO</td>
                    <td>${{($parametros_empresa->anticipo == '' || $parametros_empresa->anticipo == 0) ? '0.00' : number_format(round($parametros_empresa->anticipo,2),2,'.',',')}}</td>
                </tr>
                <tr>
                    <td>VACACIONES</td>
                    <td>$0.00</td>
                </tr>
                <tr>
                    <td>PAGO DE PRIMA VACACIONAL</td>
                    <td>$0.00</td>
                </tr>
                <tr>
                    <td>COMISIÓN MISMO DIA BONOS</td>
                    <td>${{($parametros_empresa->comision_mismo_dia == '' || $parametros_empresa->comision_mismo_dia == 0) ? '0.00' : number_format(round($parametros_empresa->comision_mismo_dia,2),2,'.',',')}}</td>
                </tr>
                <tr>
                    <td  style="background:#F0C018;"><b>TOTAL PAGO NÓMINA</b></td>
                    <td style="background:#F0C018;">${{ number_format(round($totales['total_pagar_nomina'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td><b>{{$parametros_empresa->porcentaje_honorarios}}% - HONORARIOS</b></td>
                    <td>${{ number_format(round($totales['pago_honorarios'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td><b>COSTOS PATRONALES</b></td>
                    <td>${{ number_format(round($totales['total'], 2), 2,'.',',') }}</td>
                </tr>
                <tr>
                    <td><b>SUBTOTAL</b></td>
                    <td>${{ number_format(round($totales['subtotal'], 2), 2,'.',',') }}</td>
                </tr>
                <tr>
                    <td><b>IVA</b></td>
                    <td>${{ number_format(round($totales['iva'], 2), 2,'.',',') }}</td>
                </tr>
                <tr>
                    <td style="background:#F0C018;"><b>TOTAL</b></td>
                    <td style="background:#F0C018;">${{ number_format(round($totales['total_mayor'], 2), 2,'.',',') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td></td>
        <td colspan="3">
            <table class="table table-striped mt-5">
                <tr >
                    <td colspan="2" style="background:#343a40; color: #ffffff; color text-align:center; font-size:16px">DETALLE COSTOS PATRONALES</td>
                </tr>
                <tr>
                    <td >PRESTACIONES EXTRAS</td>
                    <td>${{ number_format(round($totales['prestaciones_extras'], 2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >CUOTA FIJA</td>
                    <td>${{ number_format(round($totales['cuota_fija'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >EXC CF</td>
                    <td>${{number_format(round($totales['exc_cf'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >PRESTACIONES EN DINERO</td>
                    <td>${{number_format(round($totales['pre_dinero_pa'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >GASTOS MEDICOS PARA PENSIONADOS</td>
                    <td>${{number_format(round($totales['gas_medi_patron'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >RIESGO DE TRABAJO</td>
                    <td>${{number_format(round($totales['riesgo_trabajo'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >INVALIDEZ Y VIDA</td>
                    <td>${{number_format(round($totales['inva_vida_patro'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >GUARDERÍAS Y PRESTACIONES SOCIALES</td>
                    <td>${{number_format(round($totales['guarde_presta'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td>CUOTAS IMSS RETIRO</td>
                    <td>${{number_format(round($totales['sar_patron'],2),2,'.',',')  }}</td>
                </tr>
                <tr>
                    <td >CUOTAS IMSS CESANTIA Y VEJEZ</td>
                    <td>${{number_format(round($totales['censa_vejez_patron'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td>CRED VIVIENDA APORTACION PATRONAL SIN CREDITO</td>
                    <td>${{number_format(round($totales['infonavit_patro'], 2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td >{{$totales['porcentaje_nomina']}}%  EROGACIONES</td>
                    <td>${{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td style="background:#F0C018;"><B>TOTAL</B></td>
                    <td style="background:#F0C018;">${{number_format(round($totales['total'], 2),2,'.',',') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td></td>
        <td>
            @if(strtolower($parametros_empresa->tipo_nomina) == 'solosindical' || strtolower($parametros_empresa->tipo_nomina) == 'sindical')
                @if (count($emisoras) > 1)

                    @foreach ($emisoras as $emisora)
                        <table class="table table-striped mt-5 mr-3">
                            <tr >
                                <td colspan="2" style="background:#343a40; color: #ffffff; color text-align:center; font-size:16px">DEPOSITO<br><small><?php echo $emisora->razon_social; ?></small></td>
                            </tr>
                            <tr>
                                <td >SUMINISTRO DE PERSONAL</td>
                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['neto_fiscal_real'],2),2,'.',',') }}</td>
                            </tr>
                            <tr>
                                <td >CARGA SOCIAL</td>
                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['carga_social'],2),2,'.',',') }}</td>
                            </tr>
                            <tr>
                                <td >{{ $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] }} % SOBRE NÓMINA</td>
                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['errogacion_emisora'],2),2,'.',',') }}</td>
                            </tr>
                            <tr>
                                <td >COMISÍON {{ $totales['comision_variable'] }}% VARIABLE</td>
                                <td>
                                    @if($totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] == NULL || $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] ==0 )

                                        @php 
                                        $valorcomisionvariable = ($totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] + $totales[$emisora->id_empresa_emisora]['carga_social'] + $totales[$emisora->id_empresa_emisora]['errogacion_emisora']) * ($totales['comision_variable']/100); 
                                        @endphp
                                        ${{number_format(round($valorcomisionvariable,2),2,'.',',')}}
                                    @else
                                        ${{number_format(round($totales[$emisora->id_empresa_emisora]['valor_comision_variable02'], 2), 2,'.',',')}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><B>SUBTOTAL</B></td>

                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['subtotal02'],2),2,'.',',') }}</td>
                            </tr>
                            <tr>
                                <td><B>IVA</B></td>
                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['iva02'],2),2,'.',',') }}</td>
                            </tr>
                            <tr style="background:#F0C018;">
                                <td><B>TOTAL</B></td>
                                <td>${{ number_format(round($totales[$emisora->id_empresa_emisora]['total_mayor02'], 2), 2,'.',',') }}</td>
                            </tr>
                        </table>
                    @endforeach
                @else
                    
                    <table class="table table-striped mt-5 mr-3">
                        <tr>
                            <td colspan="2" style="background:#343a40; color: #ffffff; color text-align:center; font-size:16px">DEPOSITO 1</td>
                        </tr>
                        <tr>
                            <td>SUMINISTRO DE PERSONAL</td>
                            <td>${{number_format(round($totales['neto_fiscal_real'],2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td >CARGA SOCIAL</td>
                            <td>${{number_format(round($totales['carga_social'],2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td >{{$totales['porcentaje_nomina']}} % SOBRE NÓMINA</td>
                            <td>${{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td >COMISÍON {{$totales['comision_variable']}}% VARIABLE</td>
                            <td>${{number_format(round($totales['valor_comision'],2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td><B>SUBTOTAL</B></td>
                            <td>${{number_format(round($totales['subtotal02'],2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td><B>IVA</B></td>
                            <td>${{number_format(round($totales['iva02'], 2),2,'.',',') }}</td>
                        </tr>
                        <tr>
                            <td style="background:#F0C018;"><B>TOTAL</B></td>
                            <td style="background:#F0C018;">${{number_format(round($totales['total_mayor02'],2),2,'.',',') }}</td>
                        </tr>
                    </table>
                @endif
        </td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td></td>
        <td>
                <table class="table table-striped mt-5">
                    <tr>
                        <td colspan="2" style="background:#343a40; color: #ffffff; color text-align:center; font-size:16px">DEPOSITO 2</td>
                    </tr>
                    <tr>
                        <td>{{$totales['concepto_facturacion'] }}</td>
                        <td>${{number_format(round($totales['asesoria_contable'],2),2,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td><B>SUBTOTAL</B></td>
                        <td>{{number_format(round($totales['asesoria_contable'],2),2,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td><B>IVA</B></td>
                        <td>${{number_format(round($totales['iva03'],2),2,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td style="background:#F0C018;"><B>TOTAL</B></td>
                        <td style="background:#F0C018;">${{number_format(round($totales['total_mayor03'],2),2,'.',',') }}</td>
                    </tr>
                </table>

            @else

                @php
                    $valor_comision = $totales['total_pagar_nomina'] * ($totales['comision_variable']/100);
                @endphp

                <table class="table table-striped mt-5">
                    <tr >
                        <td colspan="2" style="background:#343a40; color: #ffffff; color text-align:center; font-size:16px">DEPOSITO</td>
                    </tr>
                    <tr>
                        <td >SUMINISTRO DE PERSONAL</td>
                        <td>${{number_format(round($totales['total_pagar_nomina'], 2), 2,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td >CARGA SOCIAL</td>
                        <td>
                            @php $cargasocial = $totales['carga_social'] + $totales['prestaciones_extras']; @endphp
                            ${{number_format(round($cargasocial, 2), 2,'.',',') }}
                        </td>
                    </tr>
                    <tr>
                        <td >{{$totales['porcentaje_nomina'] }} % SOBRE NÓMINA</td>
                        <td>${{number_format(round($totales['errogacion'], 2), 2,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td >COMISÍON {{$totales['comision_variable'] }}% VARIABLE</td>
                        <td>${{number_format(round($valor_comision, 2), 2,'.',',') }}</td>
                    </tr>
                    @php
                        $subtotal02 = $totales['total_pagar_nomina'] + $totales['carga_social'] + $totales['errogacion'] + $valor_comision;
                        $iva02 = $subtotal02 * $parametros_empresa->iva;
                        $totalmayor02 = $subtotal02 + $iva02;
                    @endphp
                    <tr>
                        <td><B>SUBTOTAL</B></td>

                        <td>${{number_format(round($subtotal02,2),2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td><B>IVA</B></td>
                        <td>${{number_format(round($iva02,2),2,'.',',')}}</td>
                    </tr>
                    <tr>
                        <td style="background:#F0C018;"><B>TOTAL</B></td>
                        <td style="background:#F0C018;">${{number_format(round($totalmayor02,2),2,'.',',')}}</td>
                    </tr>

                </table>
            @endif
        </td>
    </tr>
</table>

