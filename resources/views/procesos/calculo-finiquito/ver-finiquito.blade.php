<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')
    <link href="{{asset('/public/css/web.css')}}" rel="stylesheet" />
    <style>
        .container {
            max-width: 93%;
            margin-left:5%;
        }

        .yellow {
            background-color: #ffd519;
        }

        .GridViewScrollHeader th,
        .GridViewScrollItem td {
            font-size: 12px;
        }

.top-line-black {
    width: 19%;}

    </style>

<div class="container">

			@include('includes.header',['title'=>'Finiquito',
		        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
		        'route'=>'procesos.historico'])

	<div class="row">
	   

	    <div class="text-center col-md-12 d-flex">
	        @if(!isset($ver))    
	            {{-- {{route('procesos.calculo_finiquito.pdf.kit')}} --}}
	             <form action="{{ route('procesos.calculo_finiquitopdfkit') }}" method="POST" target="_blank">
	                @csrf
	                <button type="submit" class="btn button-style-cancel confirmar my-5 mx-3">
	                    <i class="fa fa-file-pdf tooltip_" data-toggle="tooltip"  title=""></i>    
	                    KIT BAJA
	                </button>
	                <input type="hidden" name="idperiodo" value="{{$periodo->id}}">
	                <input type="hidden" name="ejercicio" value="{{$periodo->ejercicio}}">
	                <input type="hidden" name="numPeriodo" value="{{$periodo->numero_periodo}}">
	                <input type="hidden" name="idempleado" value="{{$empleado->id}}">
	                <input type="hidden" name="idRutina" value="{{$rutina->id}}">
	            </form>
	            
	            <form action="{{ route('procesos.calculo_finiquitoexportar') }}" method="POST" target="_blank">
	                @csrf
	                <button class="btn button-style-cancel imprimir my-5">
	                    <i class="fa fa-file-excel tooltip_" data-toggle="tooltip"  title="EDITAR FINIQUITO"></i> 
	                    EXPORTAR
	                </button>
	                <input type="hidden" name="id_periodo_calculo" value="{{$periodo->id}}">
	                <input type="hidden" name="ejercicio_calculo" value="{{$periodo->ejercicio}}">
	                <input type="hidden" name="numPeriodo" value="{{$periodo->numero_periodo}}">
	                <input type="hidden" name="id_empleado_calculo" value="{{$empleado->id}}">
	                <input type="hidden" name="idRutina" value="{{$rutina->id}}">
	            </form>
	        @endif

	       
	    </div>



	    <div class="col-md-12 d-flex justify-content-between bg-warning rounded p-4">
	        <div>
	            EMPLEADO: <strong>{{$empleado->id}}- {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</strong> <br>
	            Periodo: <strong>{{$periodo->numero_periodo}}, del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</strong><br />
	            Fecha baja: <strong>{{formatoAFecha($empleado->fecha_baja)}}</strong>
	        </div>
	        <div>
	            Fecha del reporte: <br>
	            <strong>{{formatoAFecha(date('Y-m-d H:i:s'), true)}}</strong>
	        </div>
	    </div>


	    <div class="col-md-12 d-flex  table-responsive" style="font-size:.8em;padding-left:0px !important;padding-right:0px;">
	        <table class="table table-striped table-sm">
	            <thead>
	                <tr style="background:#F0C018;">
	                    <th>#</th>
	                    <th>Nombre</th>
	                    <th>Categoría</th>
	                    <th>Departamento</th>
	                    <th>Salario diario</th>
	                    <th>Sueldo neto</th>
	                    <th>Sal diario integrado</th>
	                    <th>Días pagados</th>

	                    @if ($rutina->valores_conceptos != null)
		                    @foreach($rutina->valores_conceptos->where('tipo_concepto',0) as $percepcion)
		                        <th>{{$percepcion->nombre_concepto}}</th> 
		                    @endforeach
	                    @endif


	                    <th>Total Percepciones</th>
	                    @if ($rutina->valores_conceptos != null)
		                    @foreach($rutina->valores_conceptos->where('tipo_concepto',1) as $deducciones)
		                        <th>{{$deducciones->nombre_concepto}}</th>
		                    @endforeach
	                    @endif
	                    <th scope="col">Subsidio</th>
	                    <th scope="col">Total Deducciones</th>
	                    <th scope="col">Importe Depositar</th>

	                </tr>
	            </thead>
	            <tbody>
	                <tr>
	                    <td>{{$empleado->id}}</td>
	                    <td> {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</td>
	                    <td>{{$empleado->categoria->nombre}}</td>
	                    <td>{{$empleado->departamento->nombre}}</td>
	                    <td>${{$empleado->salario_diario}}</td>
	                    <td>${{$empleado->sueldo_neto}}</td>
	                    <td>${{$empleado->salario_diario_integrado}}</td>
	                    <td>{{$rutina->dias_laborados}}</td>

	                    @if ($rutina->valores_conceptos != null)
		                    @foreach($rutina->valores_conceptos->where('tipo_concepto',0) as $percepcion)
		                        <th>{{$percepcion->total}}</th> 
		                    @endforeach
	                    @endif

	                    <td id="tdtotal_percepcion_fiscal">${{$rutina->total_percepcion_fiscal}}</td>

	                    @if ($rutina->valores_conceptos != null)
		                    @foreach($rutina->valores_conceptos->where('tipo_concepto',1) as $deducciones)
		                        <th>{{$deducciones->total}}</th>
		                    @endforeach
		                @endif
	                    <td id="tdtotal_percepcion_fiscal">${{$rutina->subsidio}}</td>
	                    
	                    <td id="tdtotal_deduccion_fiscal">${{$rutina->total_deduccion_fiscal}}</td>
	                    <td id="tdtotal_fiscal">${{$rutina->neto_fiscal}}</td>

	                </tr>
	            </tbody>

	        </table>
	    </div>

	     <!--  Segunda tabla  -->
	     <div class="col-md-9 offset-3 d-flex">
	        <div class="mr-5">
	            <table class="table table-striped table-sm mt-5 mr-3">
	                <tr>
	                    <td colspan="3" style="background:#F0C018;" class="text_center">DETALLE DE FACTURACIÓN</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2">NÓMINA</td>
	                    <td>{{ number_format(round($totales['netoFiscalreal'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2">ANTICIPO</td>
	                    <td>{{ number_format(round($valores['anticipo'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2">VACACIONES</td>
	                    <td>{{ number_format(round($valores['vacaciones'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2">PAGO DE PRIMA VACACIONAL</td>
	                    <td>{{number_format(round($totales['pagoprimavaca'], 2), 2, '.', ',')}}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2">COMISIÓN MISMO DÍA BONOS</td>
	                    <td>{{ number_format(round($valores['comisionMismo'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left" style="background:#F0C018;">
	                    <td><b>TOTAL PAGO NÓMINA</b></td>
	                    <td></td>
	                    <td>{{ number_format(round($totales['TotalpagarNomina'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td><b>{{$valores['porcentajeHono']}}%</b></td>
	                    <td><b>HONORARIOS</b></td>
	                    <td>{{ number_format(round($totales['pagoHonorarios'],2),2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2"><b>COSTOS PATRONALES</b></td>
	                    <td>{{ number_format(round($totales['total'], 2), 2,'.',',') }}</td>
	                </tr>

	                <tr class="text-left">
	                    <td colspan="2"><b>SUBTOTAL</b></td>
	                    <td>{{ number_format(round($totales['subtotal'], 2), 2,'.',',') }}</td>
	                </tr>
	                <tr class="text-left">
	                    <td colspan="2"><b>IVA</b></td>
	                    <td>{{ number_format(round($totales['iva'], 2), 2,'.',',') }}</td>
	                </tr>

	                <tr class="text-left" style="background:#F0C018;">
	                    <td><b>TOTAL</b></td>
	                    <td></td>
	                    <td>{{ number_format(round($totales['totalmayor'], 2), 2,'.',',') }}</td>
	                </tr>
	            </table>
	            <table>

	            </table>
	        </div>

	        <div>
	            <table class="table table-striped table-sm mt-5">
	                <tr>
	                    <td colspan="2" style="background:#F0C018;" class="text-center">DETALLE COSTOS PATRONALES</td>
	                </tr>
	                <tr>
	                    <td>PRESTACIONES EXTRAS</td>
	                    <td>{{ number_format(round($totales['prestacionesExtras'], 2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>CUOTA FIJA</td>
	                    <td>{{ number_format(round($totales['CuotaFija'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>EXC CF</td>
	                    <td>{{number_format(round($totales['ExcePa'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>PRESTACIONES EN DINERO</td>
	                    <td>{{number_format(round($totales['PreDineroPa'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>GASTOS MÉDICOS PARA PENSIONADOS</td>
	                    <td>{{number_format(round($totales['GasMediPatron'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>RIESGO DE TRABAJO</td>
	                    <td>{{number_format(round($totales['RiesgoTrabajo'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>INVALIDEZ Y VIDA</td>
	                    <td>{{number_format(round($totales['InvaVidaPatro'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>GUARDERÍAS Y PRESTACIONES SOCIALES</td>
	                    <td>{{number_format(round($totales['GuardePresta'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>CUOTAS IMSS RETIRO</td>
	                    <td>{{number_format(round($totales['SarPatron'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>CUOTAS IMSS CESANTÍA Y VEJEZ</td>
	                    <td>{{number_format(round($totales['CensaVejezPatro'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td>CRED VIVIENDA APORTACION PATRONAL SIN CRÉDITO</td>
	                    <td>{{number_format(round($totales['InfonavitPatro'], 2),2,'.',',') }}</td>
	                </tr>
	                <tr>
	                    <td>{{$valores['PocentajeNomina']}}% EROGACIONES</td>
	                    <td>{{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td><B>TOTAL</B></td>
	                    <td>{{number_format(round($totales['total'], 2),2,'.',',') }}</td>
	                </tr>
	            </table>
	        </div>
	    </div>


	    <div class="col-md-6 offset-5 d-flex">


	        <table class="table table-striped table-sm" style="width:30%;">
	            <tr>
	                <td colspan="2" style="background:#F0C018;" class="text-center">DEPOSITO<br></td>
	            </tr>
	            <tr>
	                <td>SUMINISTRO DE PERSONAL</td>
	                <td>{{ number_format(round($totales['TotalpagarNomina'],2),2,'.',',') }}</td>
	            </tr>
	            <tr>
	                <td>CARGA SOCIAL</td>
	                <td>{{ number_format(round($totales['cargasocial'],2),2,'.',',') }}</td>
	            </tr>
	            <tr>
	                <td>{{ $valores['PocentajeNomina'] }} % SOBRE NÓMINA</td>
	                <td>{{ number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
	            </tr>
	            <tr>
	                <td>COMISÍON {{ $totales['comisionVariable'] }}% VARIABLE</td>
	                <td>
	                    {{ number_format(round($totales['valorcomision'],2),2,'.',',') }}
	                </td>
	            </tr>

	            <tr>
	                <td><B>SUBTOTAL</B></td>

	                <td>{{ number_format(round($totales['subtotal02'],2),2,'.',',') }}</td>
	            </tr>
	            <tr>
	                <td><B>IVA</B></td>
	                <td>{{ number_format(round($totales['iva02'],2),2,'.',',') }}</td>
	            </tr>
	            <tr style="background:#F0C018;">
	                <td><B>TOTAL</B></td>
	                <td>{{ number_format(round($totales['totalmayor02'], 2), 2,'.',',') }}</td>
	            </tr>
	        </table>

	    </div>

	</div>
</div>