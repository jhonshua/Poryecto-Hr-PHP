<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<table style="background:#F0C018;">
    <tr>
        <td style="background:#F0C018;">RAZÓN SOCIAL:</td>
        <td style="background:#F0C018;">{{Session::get('empresa')['razon_social']}}</td>
    </tr>
    <tr>
        <td style="background:#F0C018;">RFC:</td>
        <td style="background:#F0C018;"><strong>{{Session::get('empresa')['rfc']}}</strong></td>
    </tr>
    <tr>
        <td style="background:#F0C018;">Periodo:</td>
        <td style="background:#F0C018;"><strong>{{$periodo->numero_periodo}}, del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</strong></td>
    </tr>
    <tr>
        <td style="background:#F0C018;">Fecha del reporte:</td>
        <td>{{ formatoAFecha(date('Y-m-d H:i:s'), true) }}</td>
    </tr>
    <tr>
        <td style="background:#F0C018;">EMPLEADO:</td>
        <td><strong>{{$empleado->id}}- {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</strong></td>
    </tr>
    <tr>
        <td style="background:#F0C018;">Fecha baja:</td>
        <td><strong>{{formatoAFecha($empleado->fecha_baja)}}</strong></td>
    </tr>
</table>



<table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th style="background:#F0C018;">Categoría</th>
                    <th style="background:#F0C018;">Departamento</th>
                    <th style="background:#F0C018;">Salario diario</th>
                    <th style="background:#F0C018;">Sueldo neto</th>
                    <th style="background:#F0C018;">Sal diario integrado</th>
                    <th style="background:#F0C018;">Días pagados</th>

                    @foreach($percepciones as $columna)
                    @php $colrutina = 'total'.$columna->id; @endphp
                    @isset($empleado->rutina->$colrutina)
                    <th style="background:#F0C018;">{{ucfirst(strtolower($columna->nombre_concepto))}}</th>
                    @endisset
                    @endforeach

                    <th style="background:#F0C018;">Total Percepciones</th>

                    @foreach($deducciones as $columna)
                    @php $colrutina = 'total'.$columna->id; @endphp
                    @isset($empleado->rutina->$colrutina)
                    <th style="background:#F0C018;">{{ucfirst(strtolower($columna->nombre_concepto))}}</th>
                    @endisset
                    @endforeach
                    <th style="background:#F0C018;" scope="col">Subsidio</th>
                    <th style="background:#F0C018;" scope="col">Total Deducciones</th>
                    <th style="background:#F0C018;" scope="col">Importe Depositar</th>

                </tr>
            </thead>




            <tbody>
                <tr>
                    <td>{{$empleado->categoria->nombre}}</td>
                    <td>{{$empleado->departamento->nombre}}</td>
                    <td>${{$empleado->salario_diario}}</td>
                    <td>${{$empleado->sueldo_neto}}</td>
                    <td>${{$empleado->salario_diario_integrado}}</td>
                    <td>{{$DiasNom}}</td>

                    @foreach($percepciones as $columna)
                    @php $colrutina = 'total'.$columna->id; @endphp
                    @isset($empleado->rutina->$colrutina)
                        <td id="td{{$colrutina}}">${{$empleado->rutina->$colrutina}}</td>
                    @endisset
                    @endforeach

                    <td id="tdtotal_percepcion_fiscal">${{$empleado->rutina->total_percepcion_fiscal}}</td>

                    @foreach($deducciones as $columna)
                    @php $colrutina = 'total'.$columna->id; @endphp
                    @isset($empleado->rutina->$colrutina)
                    <td id="td{{$colrutina}}">${{$empleado->rutina->$colrutina}}</td>
                    @endisset
                    @endforeach

                    <td>
                        @php $columnaISR = 'total'.$valores['conceptoISR']; @endphp
                        @if($empleado->rutina->$columnaISR >= 0)
                            ${{$empleado->rutina->$columnaISR}} 
                        @else
                            $0
                        @endif
                    </td>
                    <td id="tdtotal_deduccion_fiscal">${{$empleado->rutina->total_deduccion_fiscal}}</td>
                    <td id="tdtotal_fiscal">${{$empleado->rutina->neto_fiscal}}</td>

                </tr>
            </tbody>
</table>



        <table>
                <tr>
                    <td colspan="3" style="background:#F0C018;" class="text_center">DETALLE DE FACTURACIÓN</td>
                    <td colspan="2"></td>
                    <td colspan="2" style="background:#F0C018;" class="text-center">DETALLE COSTOS PATRONALES</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;">NÓMINA</td>
                    <td>{{ number_format(round($totales['netoFiscalreal'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">PRESTACIONES EXTRAS</td>
                    <td>{{ number_format(round($totales['prestacionesExtras'], 2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;">ANTICIPO</td>
                    <td>{{ number_format(round($valores['anticipo'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">CUOTA FIJA</td>
                    <td>{{ number_format(round($totales['CuotaFija'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;">VACACIONES</td>
                    <td>{{ number_format(round($valores['vacaciones'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">EXC CF</td>
                    <td>{{number_format(round($totales['ExcePa'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;">PAGO DE PRIMA VACACIONAL</td>
                    <td>{{number_format(round($totales['pagoprimavaca'], 2), 2, '.', ',')}}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">PRESTACIONES EN DINERO</td>
                    <td>{{number_format(round($totales['PreDineroPa'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;">COMISIÓN MISMO DÍA BONOS</td>
                    <td>{{ number_format(round($valores['comisionMismo'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">GASTOS MÉDICOS PARA PENSIONADOS</td>
                    <td>{{number_format(round($totales['GasMediPatron'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left" style="background:#F0C018;">
                    <td style="background:#F0C018;"><b>TOTAL PAGO NÓMINA</b></td>
                    <td style="background:#F0C018;"></td>
                    <td>{{ number_format(round($totales['TotalpagarNomina'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">RIESGO DE TRABAJO</td>
                    <td>{{number_format(round($totales['RiesgoTrabajo'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td style="background:#F0C018;"><b>{{$valores['porcentajeHono']}}%</b></td>
                    <td style="background:#F0C018;"><b>HONORARIOS</b></td>
                    <td>{{ number_format(round($totales['pagoHonorarios'],2),2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">INVALIDEZ Y VIDA</td>
                    <td>{{number_format(round($totales['InvaVidaPatro'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;"><b>COSTOS PATRONALES</b></td>
                    <td>{{ number_format(round($totales['total'], 2), 2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">GUARDERÍAS Y PRESTACIONES SOCIALES</td>
                    <td>{{number_format(round($totales['GuardePresta'],2),2,'.',',') }}</td>
                </tr>

                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;"><b>SUBTOTAL</b></td>
                    <td>{{ number_format(round($totales['subtotal'], 2), 2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">CUOTAS IMSS RETIRO</td>
                    <td>{{number_format(round($totales['SarPatron'],2),2,'.',',') }}</td>
                </tr>
                <tr class="text-left">
                    <td colspan="2" style="background:#F0C018;"><b>IVA</b></td>
                    <td>{{ number_format(round($totales['iva'], 2), 2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">CUOTAS IMSS CESANTÍA Y VEJEZ</td>
                    <td>{{number_format(round($totales['CensaVejezPatro'],2),2,'.',',') }}</td>
                </tr>

                <tr class="text-left" style="background:#F0C018;">
                    <td style="background:#F0C018;"><b>TOTAL</b></td>
                    <td style="background:#F0C018;"></td>
                    <td>{{ number_format(round($totales['totalmayor'], 2), 2,'.',',') }}</td>
                    <td colspan="2"></td>
                    <td style="background:#F0C018;">CRED VIVIENDA APORTACION PATRONAL SIN CRÉDITO</td>
                    <td>{{number_format(round($totales['InfonavitPatro'], 2),2,'.',',') }}</td>
                </tr>

                <tr>
                    <td colspan="5"></td>
                    <td style="background:#F0C018;">{{$valores['PocentajeNomina']}}% EROGACIONES</td>
                    <td>{{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                    <td style="background:#F0C018;"><B>TOTAL</B></td>
                    <td>{{number_format(round($totales['total'], 2),2,'.',',') }}</td>
                </tr>
                <tr><td></td></tr>
            </table>


            <table>
            <tr>
                <td colspan="2" style="background:#F0C018;" class="text-center">DEPÓSITO<br></td>
            </tr>
            <tr>
                <td  style="background:#F0C018;">SUMINISTRO DE PERSONAL</td>
                <td>{{ number_format(round($totales['TotalpagarNomina'],2),2,'.',',') }}</td>
            </tr>
            <tr>
                <td style="background:#F0C018;">CARGA SOCIAL</td>
                <td>{{ number_format(round($totales['cargasocial'],2),2,'.',',') }}</td>
            </tr>
            <tr>
                <td style="background:#F0C018;">{{ $valores['PocentajeNomina'] }} % SOBRE NÓMINA</td>
                <td>{{ number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
            </tr>
            <tr>
                <td style="background:#F0C018;">COMISÍON {{ $totales['comisionVariable'] }}% VARIABLE</td>
                <td>
                    {{ number_format(round($totales['valorcomision'],2),2,'.',',') }}
                </td>
            </tr>

            <tr>
                <td style="background:#F0C018;"><B>SUBTOTAL</B></td>

                <td>{{ number_format(round($totales['subtotal02'],2),2,'.',',') }}</td>
            </tr>
            <tr>
                <td style="background:#F0C018;"><B>IVA</B></td>
                <td>{{ number_format(round($totales['iva02'],2),2,'.',',') }}</td>
            </tr>
            <tr>
                <td style="background:#F0C018;"><B>TOTAL</B></td>
                <td>{{ number_format(round($totales['totalmayor02'], 2), 2,'.',',') }}</td>
            </tr>
        </table>
            
            