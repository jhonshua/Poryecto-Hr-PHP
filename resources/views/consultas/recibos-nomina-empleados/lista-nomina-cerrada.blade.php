<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($empleados);
$valorRegresar = 'T';
 if(isset($regresar)){
    $valorRegresar = $regresar;
 }
@endphp
<div class="container">
@include('includes.header',['title'=>'Timbrado Factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.recibos.nomina.inicio'])
    
    
        <h4>Periodo: #{{ $periodo->numero_periodo}} {{$periodo->fecha_inicial_periodo}} - {{$periodo->fecha_final_periodo}}</h4>
    
    <div class="row d-flex justify-content-between">
        <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
            @if($tipo == 0)
                <div class="col-xs-12 col-md-12 col-lg-3 mb-3 ml-3 px-0">
                    <a href="{{route('timbrar.nomina.emailMasivo', [$periodo, base64_encode($cadena_departamentos), $valorRegresar])}}" class="button-style btn-block mb-3">Enviar Avisos</a>
                </div>   
                <div class="col-xs-12 col-md-12 col-lg-3 mb-3 ml-3 px-0">
                    <a href="{{route('timbrar.nomina.pdfMasivo', [$periodo,$cadena_departamentos])}}" target="_BLANK" class="button-style btn-block mb-3">PDF Masivo</a>
                </div>   
            @endif     
        </div>
        <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
    </div>
    <div class="article border">
        <table class="table" id="tabla_recibos_nomina_cerrada">
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
				                        <a name="xml" id="xml"  href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button" title="Descargar Comprobante" rel="Descargar Comprobante"><img src="{{ asset('/img/descargar-timbrado.png') }}"  width="25px" ></a>
				                        <a name="vercfdicancel" id="vercfdicancel"  href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button" title="Verificar Estatus" rel="Verificar Estatus"><img src="{{ asset('/img/baja-empleado.png') }}"  width="25px" ></a>
		                            @else
				                        <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
		                            @endif  									
								@else									
		                            @if(count($e->timbres_cancelados) == 1 && $e->numero_timbres_noerror == 1)
			                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
			                            <a name="xml" id="xml"  href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button" title="Descargar Comprobante" rel="Descargar Comprobante"><img src="{{ asset('/img/descargar-timbrado.png') }}"  width="25px" ></a>                                            
			                            <a name="vercfdicancel" id="vercfdicancel"  href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button" title="Verificar Estatus" rel="Verificar Estatus"><img src="{{ asset('/img/baja-empleado.png') }}"  width="25px" ></a>
		                            
									@elseif($e->numero_timbres_noerror > 1 && $e->timbres[0]->sello_sat == 1 && count($e->timbres_cancelados) > 0 )
									
			                            <a name="recibo" id="recibo" class="mt-2" ref="{{ route('timbrar.nomina_empleado.ver_pdf',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button" rel="Imprimir recibo" title="Imprimir recibo"><img src="{{ asset('/img/impresora-timbrado.png') }}" width="25px" ></a>
			                            <a name="xml" id="xml" class="ml-2" href="{{ route('timbrar.nominaEmpleado.descargarXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button" rel="Descargar XML" title="Descargar XML"><img src="{{ asset('/img/descargar-timbrado.png') }}" rel="Descargar Comprobante" width="25px" ></a>
			                            <a name="vercfdicancel" id="vercfdicancel" href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button" title="Verificar Estatus" rel="Verificar Estatus"><img src="{{ asset('/img/baja-empleado.png') }}"  width="25px" ></a>
		                            @else
									
			                            <a name="recibo" id="recibo" class="ml-2" href="{{ route('timbrar.nomina_empleado.ver_pdf',[$e->id,$repo,$e->timbres[0]->file_xml,$e->timbres[0]->id]) }}" target="_blank" role="button" rel="Imprimir recibo" title="Imprimir recibo"><img src="{{ asset('/img/impresora-timbrado.png') }}" width="25px" ></a>
			                            <a name="xml" id="xml" href="{{ route('timbrar.nominaEmpleado.descargarXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button" rel="Descargar XML" title="Descargar XML"><img src="{{ asset('/img/descargar-timbrado.png') }}" rel="Descargar Comprobante" width="25px" ></a>
			                            <a name="timbrar" id="timbrar"  href="{{ route('cancelar.timbre_empleado',[$e->timbres[0]->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" role="button" rel="Cancelar timbre" title="Cancelar timbre" rel="Descargar Comprobante"><img src="{{ asset('/img/cancelar-timbre.png') }}" width="25px" ></a>                                 
		                            @endif
									
	                            @endif
								
							@elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1 && $e->ultimo_timbre->sello_sat == 'error')
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado/Error al Procesar Vuelve a Timbrar</p>
	                            <a name="xml" id="xml" href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button"  title="Descargar Comprobante" rel="Descargar Comprobante"><img src="{{ asset('/img/descargar-timbrado.png') }}"  width="25px" ></a>
	                            <a name="vercfdicancel" id="vercfdicancel"  href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button" title="Verificar Estatus" rel="Verificar Estatus"><img src="{{ asset('/img/baja-empleado.png') }}"  width="25px" ></a>
                            @elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1)							
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
	                            <a name="xml" id="xml"  href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres_cancelados[0]->file_soap]) }}" target="_blank" role="button"  title="Descargar Comprobante" rel="Descargar Comprobante"><img src="{{ asset('/img/descargar-timbrado.png') }}"  width="25px" ></a>
	                            <a name="vercfdicancel" id="vercfdicancel"  href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button" title="Verificar Estatus" rel="Verificar Estatus"><img src="{{ asset('/img/baja-empleado.png') }}"  width="25px" ></a>
                            @elseif($e->timbres_cancelados->count() > 0 && $e->ultimo_timbre->sello_sat == 'error')							
	                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
	                            CFDI CANCELADO								
                            @elseif($e->timbres_cancelados->count() > 0 )						
	                            CFDI CANCELADO
                            @endif 
                        </div>                        
                    </td>
                </tr>    
                @endforeach                
            </tbody>
        </table>

    </div>
</div>
@include('includes.footer')

<script src="{{asset('js/typeahead.js')}}"></script>
<script>
        let dataSrc = [];
        let table = $('#tabla_recibos_nomina_cerrada').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [2]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });
    // table.order([5, 'desc']).draw();
</script>
