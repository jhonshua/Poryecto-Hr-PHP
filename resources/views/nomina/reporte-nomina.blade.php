<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<style>
    .container{
        max-width: 96%;
    }
	.article-info {
	    width: 100%;
	    height: auto;
	    padding: 1%;
	    float: left;
	    box-sizing: border-box;
	    background-color: #fff;
	}
</style>

<div class="container">

@include('includes.header',['title'=>'Reporte de nómina',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/periodo-nomina.png',
        'route'=>'nomina.periodos'])

    <h5 class="font-weight-bold mt-3">Razón social: {{Session::get('empresa')['razon_social']}}</h5>
    <div class="article-info border">
        <div class="row">
            <div class="col-md-6">
                RFC: <strong>{{Session::get('empresa')['rfc']}}</strong> <br>
                Fecha del reporte: <strong>{{formatoAFecha(date('Y-m-d H:i:s'), true)}}</strong>
            </div>
            <div class="col-md-6 text-right">
                Periodo: <strong>{{$periodo->numero_periodo}} del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</strong>
            </div>
        </div>
    </div>


    <div class="article border mt-3">
        <div class="row">
            <div class="col m12 s12">
                @include('nomina.reporte-nomina-include')
            </div>
        </div>
    </div>


    <div class="row">
    	<div class="col-md-5">
		    <div class="article border mt-4">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4 class="font-weight-bold">DETALLE DE FACTURACIÓN</h4>
                    </div>
                </div>
                <hr>

		        <table class="table mt-5 mr-3">
		            <tr class="text-left">
		                <td colspan="2">Nómina</td>
		                <td>{{ number_format(round($totales['neto_fiscal_real'],2),2,'.',',') }}</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2">Beneficio social</td>
		                <td>{{ number_format(round($totales['total_percepcion_sindical'],2),2,'.',',') }}</td>
		            </tr>

		            @if($parametros_empresa->provision_aguinaldo==1 && $periodo->aux_agui==0)
		                <tr class="text-left">
		                    <td colspan="2">Provision aguinaldo</td>
		                    <td>{{ number_format(round($totales['valor_provision_aguinaldo'],2),2,'.',',') }}</td>
		                </tr>  
		            @endif


		            @if($parametros_empresa->provision_prima_vacacional==1 && $periodo->aux_prima_vacacional==0)
		                <tr class="text-left">
		                    <td colspan="2">Provision prima vacacional</td>
		                    <td>{{ number_format(round($totales['valor_provision_prima_vacacional'],2),2,'.',',') }}</td>
		                </tr>
		            @endif


		            <tr class="text-left">
		                <td colspan="2">Anticipo</td>
		                <td>{{($parametros_empresa->anticipo == '' || $parametros_empresa->anticipo == 0) ? '0.00' : number_format(round($parametros_empresa->anticipo,2),2,'.',',')}}</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2">Vacaciones</td>
		                <td>0.00</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2">Pago de prima vacacional</td>
		                <td>0.00</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2">Comisión mismo dia bonos</td>
		                <td>{{($parametros_empresa->comision_mismo_dia == '' || $parametros_empresa->comision_mismo_dia == 0) ? '0.00' : number_format(round($parametros_empresa->comision_mismo_dia,2),2,'.',',')}}</td>
		            </tr>
		            <tr class="text-left" style="background:#F0C018; color:white">
		                <td><b>Total pago nómina</b></td>
		                <td></td>
		                <td><h5 class="font-weight-bold">{{ number_format(round($totales['total_pagar_nomina'],2),2,'.',',') }}</h5></td>
		            </tr>
		            <tr class="text-left">
		                <td><b>{{$parametros_empresa->porcentaje_honorarios}}%</b></td>
		                <td><b>Honorarios</b></td>
		                <td>{{ number_format(round($totales['pago_honorarios'],2),2,'.',',') }}</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2"><b>Costos patronales</b></td>
		                <td>{{ number_format(round($totales['total'], 2), 2,'.',',') }}</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2"><b>Subtotal</b></td>
		                <td>{{ number_format(round($totales['subtotal'], 2), 2,'.',',') }}</td>
		            </tr>
		            <tr class="text-left">
		                <td colspan="2"><b>Iva</b></td>
		                <td>{{ number_format(round($totales['iva'], 2), 2,'.',',') }}</td>
		            </tr>
		            <tr class="text-left" style="background:#F0C018; color:white">
		                <td><b>Total</b></td>
		                <td></td>
                        <td>
                            <h5 class="font-weight-bold">{{ number_format(round($totales['total_mayor'],2),2,'.',',') }}</h5>
                        </td>
		            </tr>
		        </table>
		    </div>
    	</div>
    	<div class="col-md-1"></div>
    	<div class="col-md-6">
		    <div class="article border mt-4">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4 class="font-weight-bold">DETALLE COSTOS PATRONALES</h4>
                    </div>
                </div>
                <hr>

		        <table class="table mt-5">
		            <tr>
		                <td >Prestaciones extras</td>
		                <td>{{ number_format(round($totales['prestaciones_extras'], 2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td >Cuota fija</td>
		                <td>{{ number_format(round($totales['cuota_fija'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td>Exc cf</td>
		                <td>{{number_format(round($totales['exc_cf'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td>Prestaciones en dinero</td>
		                <td>{{number_format(round($totales['pre_dinero_pa'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td>Gastos medicos para pensionados</td>
		                <td>{{number_format(round($totales['gas_medi_patron'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td>Riesgo de trabajo</td>
		                <td>{{number_format(round($totales['riesgo_trabajo'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td>Invalidez y vida</td>
		                <td>{{number_format(round($totales['inva_vida_patro'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td >Guarderías y prestaciones sociales</td>
		                <td>{{number_format(round($totales['guarde_presta'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td >Cuotas IMSS retiro</td>
		                <td>{{number_format(round($totales['sar_patron'],2),2,'.',',')  }}</td>
		            </tr>
		            <tr>
		                <td >Cuotas IMSS cesantia y vejez</td>
		                <td>{{number_format(round($totales['censa_vejez_patron'],2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td >Cred vivienda aportacion patronal sin credito</td>
		                <td>{{number_format(round($totales['infonavit_patro'], 2),2,'.',',') }}</td>
		            </tr>
		            <tr>
		                <td >{{$totales['porcentaje_nomina']}}%  erogaciones</td>
		                <td>{{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
		            </tr>
		            <tr style="background:#F0C018; color:white;">
		                <td><B>TOTAL</B></td>
                        <td>
                            <h5 class="font-weight-bold">{{ number_format(round($totales['total'],2),2,'.',',') }}</h5>
                        </td>

		            </tr>
		        </table>
		    </div>
    	</div>
    </div>


    <div class="row">
    	@if (count($emisoras) > 1)
	            	 <div class="row">
	                @foreach ($emisoras as $emisora)
	               
	                	<div class="col-md-4">
	                		<div class="article border mt-4">

				                <div class="row">
				                    <div class="col-md-12 text-center">
				                        <h4 class="font-weight-bold">DEPÓSITO</h4>
				                    </div>
				                </div>
				                <hr>

	                			<table class="table mt-5">
	                				<tr>
	                					<td>SUMINISTRO DE PERSONAL</td>
	                					<td>{{ number_format(round($totales[$emisora->id_empresa_emisora]['neto_fiscal_real'],2),2,'.',',') }}</td>
	                				</tr>
	                				<tr>
	                					<td >CARGA SOCIAL</td>
	                					<td>{{ number_format(round($totales[$emisora->id_empresa_emisora]['carga_social'],2),2,'.',',') }}
	                					</td>
	                				</tr>
	                				<tr>
	                            		<td >{{ $totales[$emisora->id_empresa_emisora]['porcentaje_nomina'] }}
	                            			% SOBRE NÓMINA
	                            		</td>
	                            		<td>
	                            			{{ number_format(round($totales[$emisora->id_empresa_emisora]['errogacion_emisora'],2),2,'.',',') }}
	                            		</td>
	                				</tr>

	                            	<tr>
	                            		<td >COMISÍON {{ $totales['comision_variable'] }}% VARIABLE</td>
	                            		<td>
	                                		@if($totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] == NULL || $totales[$emisora->id_empresa_emisora]['valor_comision_variable02'] ==0 )

		                                    	@php 
		                                    		$valorcomisionvariable = ($totales[$emisora->id_empresa_emisora]['neto_fiscal_real'] + $totales[$emisora->id_empresa_emisora]['carga_social'] + $totales[$emisora->id_empresa_emisora]['errogacion_emisora']) * ($totales['comision_variable']/100); 
		                                    	@endphp
		                                    	{{number_format(round($valorcomisionvariable,2),2,'.',',')}}
	                               			@else
	                                    		{{number_format(round($totales[$emisora->id_empresa_emisora]['valor_comision_variable02'], 2), 2,'.',',')}}
	                                		@endif
	                            		</td>       			
	                            	</tr>
			                        <tr>
			                            <td><B>SUBTOTAL</B></td>

			                            <td>{{ number_format(round($totales[$emisora->id_empresa_emisora]['subtotal02'],2),2,'.',',') }}</td>
			                        </tr>
			                        <tr>
			                            <td><B>IVA</B></td>
			                            <td>{{ number_format(round($totales[$emisora->id_empresa_emisora]['iva02'],2),2,'.',',') }}</td>
			                        </tr>
			                        <tr style="background:#F0C018;">
			                            <td><B>TOTAL</B></td>
			                            <td>{{ number_format(round($totales[$emisora->id_empresa_emisora]['total_mayor02'], 2), 2,'.',',') }}</td>
			                        </tr>
	                			</table>
	                		</div>
	                	</div>
	                @endforeach
	                </div>

	                @endif

    </div>


	<div class="row">

	    <div class="col-md-12 offset-2 d-flex">
	        @if($parametros_empresa->tipo_nomina == 'solosindical' || $parametros_empresa->tipo_nomina == 'sindical')

	            @if (count($emisoras) > 1)

	            @else
	                <div class="row">
	                	<div class="col-md-12 mt-3">
			                <div class="article border mr-5">
			                    <div class="row">
			                            <div class="col-md-12 text-center">
			                                <h4 class="font-weight-bold">DEPOSITO 1</h4>
			                            </div>
			                        </div>
			                        <hr>
			                            
			                    <div>
			                        <table class="table mt-5 mr-3">
			                            <tr>
			                                <td>SUMINISTRO DE PERSONAL</td>
			                                <td>{{number_format(round($totales['neto_fiscal_real'],2),2,'.',',') }}</td>
			                            </tr>
			                            <tr>
			                                <td >CARGA SOCIAL</td>
			                                <td>{{number_format(round($totales['carga_social'],2),2,'.',',') }}</td>
			                            </tr>
			                            <tr>
			                                <td >{{$totales['porcentaje_nomina']}} % SOBRE NÓMINA</td>
			                                <td>{{number_format(round($totales['errogacion'],2),2,'.',',') }}</td>
			                            </tr>
			                            <tr>
			                                <td >COMISÍON {{$totales['comision_variable']}}% VARIABLE</td>
			                                <td>{{number_format(round($totales['valor_comision'],2),2,'.',',') }}</td>
			                            </tr>
			                            <tr>
			                                <td><B>SUBTOTAL</B></td>
			                                <td>{{number_format(round($totales['subtotal02'],2),2,'.',',') }}</td>
			                            </tr>
			                            <tr>
			                                <td><B>IVA</B></td>
			                                <td>{{number_format(round($totales['iva02'], 2),2,'.',',') }}</td>
			                            </tr>
			                            <tr style="background:#F0C018; color: white;">
			                                <td><B>TOTAL</B></td>
			                                <td><h5 class="font-weight-bold">{{number_format(round($totales['total_mayor02'],2),2,'.',',') }}</h5></td>
			                            </tr>
			                        </table>
			                    </div>
			                </div>

	                	</div>	
	                </div>

	            @endif

	            <div class="col-md-4 mt-3">
	                <div class="article border mr-5">
	                    <div class="row">
	                            <div class="col-md-12 text-center">
	                                <h4 class="font-weight-bold">DEPOSITO 2</h4>
	                            </div>
	                        </div>
	                        <hr>
	                            
	                    <div>
			                <table class="table mt-5">

			                    <tr>
			                        <td>{{$totales['concepto_facturacion'] }}</td>
			                        <td>{{number_format(round($totales['asesoria_contable'],2),2,'.',',') }}</td>
			                    </tr>
			                    <tr>
			                        <td><B>SUBTOTAL</B></td>
			                        <td>{{number_format(round($totales['asesoria_contable'],2),2,'.',',') }}</td>
			                    </tr>
			                    <tr>
			                        <td><B>IVA</B></td>
			                        <td>{{number_format(round($totales['iva03'],2),2,'.',',') }}</td>
			                    </tr>
			                    <tr style="background:#F0C018; color: white;">
			                        <td><B>TOTAL</B></td>
			                        <td><h5 class="font-weight-bold">{{number_format(round($totales['total_mayor03'],2),2,'.',',') }}</h5></td>
			                    </tr>

			                </table>

	                    </div>
	                </div>	
	            </div>


	        @else

	            @php
	                $valor_comision = $totales['total_pagar_nomina'] * ($totales['comision_variable']/100);
	            @endphp

{{-- 	            <table class="table table-striped mt-5">
	                <tr >
	                    <td colspan="2" style="background:#F0C018;" class="text-center">DEPOSITO</td>
	                </tr>
	                <tr>
	                    <td >SUMINISTRO DE PERSONAL</td>
	                    <td>{{number_format(round($totales['total_pagar_nomina'], 2), 2,'.',',') }}</td>
	                </tr>
	                <tr>
	                    <td >CARGA SOCIAL</td>
	                    <td>
	                        @php $cargasocial = $totales['carga_social'] + $totales['prestaciones_extras']; @endphp
	                        {{number_format(round($cargasocial, 2), 2,'.',',') }}
	                    </td>
	                </tr>
	                <tr>
	                    <td >{{$totales['porcentaje_nomina'] }} % SOBRE NÓMINA</td>
	                    <td>{{number_format(round($totales['errogacion'], 2), 2,'.',',') }}</td>
	                </tr>
	                <tr>
	                    <td >COMISÍON {{$totales['comision_variable'] }}% VARIABLE</td>
	                    <td>{{number_format(round($valor_comision, 2), 2,'.',',') }}</td>
	                </tr>
	                @php
	                    $subtotal02 = $totales['total_pagar_nomina'] + $totales['carga_social'] + $totales['errogacion'] + $valor_comision;
	                    $iva02 = $subtotal02 * $parametros_empresa['iva'];
	                    $totalmayor02 = $subtotal02 + $iva02;
	                @endphp
	                <tr>
	                    <td><B>SUBTOTAL</B></td>

	                    <td>{{number_format(round($subtotal02,2),2,'.',',')}}</td>
	                </tr>
	                <tr>
	                    <td><B>IVA</B></td>
	                    <td>{{number_format(round($iva02,2),2,'.',',')}}</td>
	                </tr>
	                <tr style="background:#F0C018;">
	                    <td><B>TOTAL</B></td>
	                    <td>{{number_format(round($totalmayor02,2),2,'.',',')}}</td>
	                </tr>

	            </table> --}}
	        @endif
	    </div>


	    <div class="text-center col-md-12 d-flex justify-content-center">
	        <form action="{{ route('calculo.exportar') }}" method="POST" target="_blank" enctype="multipart/form-data">
	            @csrf
	            <button class="button-style-gray  center imprimir my-5"> <img src="{{asset('/img/icono-importar.png')}}" alt="Periodo de implementación" width="20px"> Exportar Nomina</button>
	            <input type="hidden" name="idPeriodo" value="{{$periodo->id}}">
	            @foreach ($departamentos as $depto)
	                <input type="hidden" name="deptos[]" value="{{$depto}}" class="mb-3">
	            @endforeach
	        </form>

	        <form action="{{ route('calculo.exportardetalle') }}" method="POST" target="_blank">
	            @csrf
	            <button class="button-style center imprimir my-5 ml-3">Exportar Nomina  Detalle</button>
	            <input type="hidden" name="idPeriodo" value="{{$periodo->id}}">
	            @foreach ($departamentos as $depto)
	                <input type="hidden" name="deptos[]" value="{{$depto}}" class="mb-3">
	            @endforeach
	        </form>
	    </div>

	</div>

</div>

@include('includes.footer')
<script src="{{asset('/js/gridviewscroll.js')}}" type="text/javascript"></script>

<script type="text/javascript">
    var gridViewScroll = null;
    var options = new GridViewScrollOptions();
    options.elementID = "empleadostbl";
    options.width = '100%';
    options.height = '700px';
    options.freezeColumn = true;
    options.freezeFooter = false;
    options.freezeColumnCssClass = "GridViewScrollItemFreeze";
    options.freezeColumnCount = 2;

    gridViewScroll = new GridViewScroll(options);
    gridViewScroll.enhance();


    $('.confirmForm').submit(function(){
        $('.btn.confirmar').attr('disabled', true).text('ESPERE...');
    });
</script>
