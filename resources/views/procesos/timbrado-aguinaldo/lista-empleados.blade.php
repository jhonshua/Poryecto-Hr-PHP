<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($departamentos);
@endphp

<div class="container">
@include('includes.header',['title'=>'Timbrado aguinaldo',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'timbrar.aguinaldo.paso1'])

        <h4 class="mb-5">Ejercicio: #{{ $ejercicio }}</h4>

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="row col-sm-12 col-xs-12 col-md-12 col-lg-12 mb-3 ml-3 px-0">   
                    @if ($existen_timbrados === 0)
                        <a name="masiva" id="masiva" class="button-style text-nowrap mb-2 mr-2" href="{{route('timbrar.aguinaldo.validar.masivo', [$ejercicio, base64_encode($cadena_departamentos)])}}" role="button">Timbrar de forma masiva</a>
                    @else 
                        <a name="masiva" id="masiva" class="button-style text-nowrap mb-2 mr-2" href="{{route('descarga.aguinaldo.zipxml',[$ejercicio])}}" role="button">XML Masivo ZIP</a>
                        <a name="masiva" id="masiva" class="button-style text-nowrap mb-2 mr-2" href="{{route('descarga.aguinaldo.zippdf',[$ejercicio])}}" role="button">PDF Masivo ZIP</a>       
                    @endif    
                </div>
                <div class="">                   
                </div>
            </div>
            <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <div class=" col-12 text-center">
                <table class="table w-100" id="tabla_timbrado_aguinaldo">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th># Empleado</th>
                            <th>Nombre</th>
                            <th>Importe a Timbrar</th>
                            <th>No. Timbre</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empleados as $e)
                        <tr>
                            <td scope="row">{{ $e->id }}</td>
                            <td>{{ $e->numero_empleado }}</td>
                            <td>{{ $e->nombre }} {{ $e->apaterno }} {{ $e->amaterno }}</td>
                            <td> {{ ($e->neto - $e->pension_alimenticia - $e->descuentos_otros) }}</td>
                            <td> 
                                @if (count($e->timbres) > 0)
                                    {{ $e->timbres[0]->num_factura }} 
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                        @if (count($e->timbres) > 0)  
                                            @if(count($e->timbres) == 1 && $e->timbres[0]->sello_sat == 'error')
                                                @if(count($e->timbres_cancelados) > 0)
                                                    @if(count($e->timbres_cancelados) > 0 && $e->ultimo_timbre->sello_sat == 'error')
                                                        <p class="text-danger"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al timbrar</p>
                                                    @else
                                                        <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                                    @endif
                                                    <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar Comprobante</a>
                                                    <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.verificar.estatus', [$e->ultimo_timbre->id])}}" target="_BLANK" role="button">Verificar Estatus</a>
                                                    <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>
                                                @else
                                                    <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
                                                    <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Timbrar</a>
                                                @endif  

                                            @else

                                                @if(count($e->timbres_cancelados) == 1 && $e->numero_timbres_noerror == 1)
                                                    <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                                    <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar Comprobante</a>                                            
                                                    <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.verificar.estatus', [$e->ultimo_timbre->id])}}" target="_BLANK" role="button">Verificar Estatus</a>
                                                    <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>
                                                @elseif($e->numero_timbres_noerror > 1 && $e->timbres[0]->sello_sat == 1 && count($e->timbres_cancelados) >0 )

                                                    <a name="recibo" id="recibo" class="button-style-custom text-nowrap" href="{{route('pdf-timbrar-aguinaldo',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Imprimir Recibo</a>
                                                    <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar XML</a>
                                                    <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.verificar.estatus', [$e->ultimo_timbre->id])}}" target="_BLANK" role="button">Verificar Estatus</a>

                                                @else

                                                    <a name="recibo" id="recibo" class="button-style-custom text-nowrap" href="{{route('pdf-timbrar-aguinaldo',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Imprimir Recibo</a>
                                                    <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar XML</a>
                                                    <a name="timbrar" id="timbrar" class="button-style-custom text-nowrap" href="{{route('cancelar.timbre.aguinaldo',[$e->timbres[0]->id, base64_encode($cadena_departamentos)])}}" role="button">Cancelar Timbre</a>

                                                @endif

                                            @endif

                                        @elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1 && $e->ultimo_timbre->sello_sat == 'error')

                                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado/Error al Procesar Vuelve a Timbrar</p>
                                            <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar Comprobante</a>
                                            <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.verificar.estatus', [$e->ultimo_timbre->id])}}" target="_BLANK" role="button">Verificar Estatus</a>
                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>

                                        @elseif(count($e->timbres_cancelados) == 1 && $e->numero_timbres_noerror == 1)
                                        
                                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                            <a name="xml" id="xml" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.descargar.soapxml',[$e->id,$repo,$e->timbres[0]->file_xml])}}" target="_BLANK" role="button">Descargar Comprobante</a>
                                            <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="{{route('timbrar.aguinaldo.verificar.estatus', [$e->ultimo_timbre->id])}}" target="_BLANK" role="button">Verificar Estatus</a>
                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>

                                        @elseif(count($e->timbres_cancelados) > 0 && $e->ultimo_timbre->sello_sat == 'error')

                                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al Procesar Vuelve a Timbrar</p>
                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>
                                            <!-- <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="#" role="button">VER CFDI CANCELADO</a> -->

                                        @elseif(count($e->timbres_cancelados) > 0 )

                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom text-nowrap" role="button">Retimbrar</a>
                                            <!-- <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom text-nowrap" href="#" role="button">VER CFDI CANCELADO</a> -->

                                        @else
                                        @if( $e->ultimo_timbre != null && (!is_array($e->ultimo_timbre)  && $e->ultimo_timbre->sello_sat === "error") )
                                            <p>Error al Procesar Vuelve a Timbrar</p>
                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom" role="button">Timbrar</a>
                                        @else
                                            <a href="{{route('validar.timbre.aguinaldo.empleado',[$e->id, base64_encode($cadena_departamentos)])}}" name="timbrar" id="timbrar" class="button-style-custom" role="button">Timbrar</a>
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
</div>
@include('includes.footer')

<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>   
<script>
    let dataSrc = [];
    let table = $('#tabla_timbrado_aguinaldo').DataTable({
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
                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }
            });
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
    $(function(){
       
    });
</script>
