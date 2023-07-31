@extends('layouts.principal')
@section('tituloPagina', "Timbrado Asimilados")

@section('content')
					
<div class="row mt-1">
<h3>Periodo: #{{ $periodo->numero_periodo}} {{$periodo->fecha_inicial_periodo}} - {{$periodo->fecha_final_periodo}}</h3>
    <div class=" col-12 text-center">
        @if ($existen_timbrados == 0)
            <a name="masiva" id="masiva" class="btn btn-dark btn-sm float-right mb-2" href="{{ route('contabilidad.timbrar.validar_masivo_asimilados',[$periodo,$cadena_departamentos])}}" role="button">Timbrar de forma masiva</a>
        @endif
         @if($tipo == 0)
            
            <a href="{{ route('contabilidad.timbrar.nomina.pdf_masivo_asimilados',[$periodo,$cadena_departamentos])}}" target="_BLANK" class="btn btn-dark btn-sm float-right mb-2 mr-2">
                PDF Masivo
            </a>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th># Empleado</th>
                    <th>Nombre</th>
                    <th>No. Timbre</th>
                    <th>Tipo de Nomina</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empleados as $e)
									
                <tr>
                    <td scope="row">{{ $e->id }}</td>
                    <td>{{ $e->numero_empleado }}</td>
                    <td>{{ $e->nombre }} {{ $e->apaterno }} {{ $e->amaterno }}</td>
                    <td> 
                        @if (count($e->timbres) > 0)
                        {{ $e->timbres[0]->num_factura }} 
                        @endif
                    </td>
                    <td> {{ ucfirst($e->tipo_de_nomina)}}</td>
                    <td>
                        <div class="dropdown">
										
                            @if(count($e->timbres) > 0)
							
							 
	                            @if(count($e->timbres) == 1 &&  (isset($e->timbres[0]->sello_sat) && $e->timbres[0]->sello_sat == 'error'))
								
		                            @if(count($e->timbres_cancelados) > 0)
			                            @if(count($e->timbres_cancelados) > 0 && $e->ultimo_timbre['sello_sat'] == 'error')
				                            <p class="text-danger"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al timbrar</p>
			                            @else
				                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
			                            @endif
				                        <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('timbrar.asimilados.descargar_soap_xml_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Descargar Comprobante</a>
				                        <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.verificar_status_asimilados',[$e->timbres[0]->id]) }}" role="button">Verificar Estatus</a>
				                        <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>
		                            @else
				                        <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
				                        <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Timbrar</a>
		                            @endif  
									
								@else
									
		                            @if(count($e->timbres_cancelados) == 1 && $e->numero_timbres_noerror == 1)
									
									
			                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
			                            <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('timbrar.asimilados.descargar_soap_xml_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Descargar Comprobante</a>                                            
			                            <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.verificar_status_asimilados',[$$e->timbres[0]->id]) }}" role="button">Verificar Estatus</a>
			                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>
		                            
									@elseif($e->numero_timbres_noerror > 1 && $e->timbres[0]->sello_sat == 1 && count($e->timbres_cancelados) > 0 )
									
			                            <a name="recibo" id="recibo" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.ver_pdf_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Imprimir Recibo</a>
			                            <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.descargar_xml_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Descargar XML</a>
			                            <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.verificar_status_asimilados',[$e->timbres[0]->id]) }}" role="button">Verificar Estatus</a>
		                            @else
									
			                            <a name="recibo" id="recibo" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.ver_pdf_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml,$e->timbres[0]->id]) }}" role="button">Imprimir Recibo</a>
			                            <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.descargar_xml_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Descargar XML</a>
			                            <a name="timbrar" id="timbrar" class="btn btn-dark btn-sm " href="{{ route('contabilidad.cancelar.timbre_empleado_asimilados',[$e->timbres[0]->id])}}" role="button">Cancelar Timbre</a>
		                            @endif
									
	                            @endif
								
							@elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1 && $e->ultimo_timbre->sello_sat == 'error')
							
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado/Error al Procesar Vuelve a Timbrar</p>
	                            <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('timbrar.asimilados.descargar_soap_xml_asimilados',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" role="button">Descargar Comprobante</a>
	                            <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.verificar_status_asimilados',[$e->timbres[0]->id]) }}" role="button">Verificar Estatus</a>
                                <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>
							
									
                            @elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1)
							
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
	                            <a name="xml" id="xml" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.asimilados.descargar_soap_xml_asimilados',[$e->id,$repo,$e->timbres_cancelados[0]->file_soap]) }}" role="button">Descargar Comprobante</a>
	                            <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="{{ route('contabilidad.timbrar.nomina_empleado.verificar_status_asimilados',[$e->ultimo_timbre->id]) }}" role="button">Verificar Estatus</a>

	                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos,$periodo->id])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>

	                            <!--<a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>-->

								
                            @elseif($e->timbres_cancelados->count() > 0 && $e->ultimo_timbre->sello_sat == 'error')
							
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
	                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>
	                            <a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="#" role="button">VER CFDI CANCELADO</a>
								
                            @elseif($e->timbres_cancelados->count() > 0 )
							
	                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Retimbrar</a>
	                            <!--<a name="vercfdicancel" id="vercfdicancel" class="btn btn-dark btn-sm " href="#" role="button">VER CFDI CANCELADO</a>-->
								
                            @else
							    @if( $e->ultimo_timbre != null && (!is_array($e->ultimo_timbre)  && $e->ultimo_timbre->sello_sat === "error") )
		                            <p>Error al Procesar Vuelve a Timbrar</p>
		                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Timbrar</a>
	                            @else
		                            <a href="{{ route('contabilidad.timbrar.validar_empleado_asimilados',[$e->id,$cadena_departamentos])}}" name="timbrar" id="timbrar" class="btn btn-dark btn-sm " role="button">Timbrar</a>
	                            @endif 
                            @endif 
                        </div>                        
                    </td>
                </tr>    
                @endforeach                
            </tbody>
        </table>
    </div>        
</div>

<pre>
    {{-- {{ $empleados[0] }} --}}
</pre>

@endsection
@push('scripts')
    <script>
        $("#todos").click(function () {
            $(".check").prop('checked', $(this).prop('checked'));
        });
  </script>
  @endpush

