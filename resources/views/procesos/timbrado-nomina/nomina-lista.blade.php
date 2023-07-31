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
    @if(isset($regresar))
    @include('includes.header',['title'=>'Timbrado Factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.recibos.nomina.inicio'])
      
    @else
    @include('includes.header',['title'=>'Timbrado Factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'timbrar.nomina'])
        
    @endif
    
    <h4>Periodo: #{{ $periodo->numero_periodo}} {{$periodo->fecha_inicial_periodo}} - {{$periodo->fecha_final_periodo}}</h4>
        <br>
    <div>
        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9 ml-1 px-0">
                @if ($existen_timbrados == 0)
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                        <a name="masiva" id="masiva" class="button-style btn-block mb-3" href="{{route('timbrar.validarMasivo', [$periodo, base64_encode($cadena_departamentos), $valorRegresar])}}" role="button">Timbrar de forma masiva</a>
                    </div>
                @endif
                @if($tipo == 0)
                    <div class="col-xs-12 col-lg-3">
                        <a href="{{route('timbrar.nomina.emailMasivo', [$periodo, base64_encode($cadena_departamentos), $valorRegresar])}}" class="button-style btn-block mb-3">Enviar avisos</a>
                    </div>
                    <div class="col-xs-12 col-lg-3">
                        <a href="{{route('timbrado.generarmasivo', [$periodo])}}" target="_BLANK" class="button-style btn-block mb-3">PDF masivo</a>
                    </div>
                @endif
            </div>
            <div class="mb-3 dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 w-px-150" id="div_buscar"></div>
        </div>
    </div>

    <div class="article border">
        <table class="table w-100" id="tabla_nomina">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center"># Empleado</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">No. Timbre</th>
                    <th class="text-center">Tipo de nómina</th>
                    <th class="text-center">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empleados as $e)
                    <tr>
                        <td class="text-center" scope="row">{{ $e->id }}</td>
                        <td class="text-center">{{ $e->numero_empleado }}</td>
                        <td class="text-center">{{ $e->nombre }} {{ $e->apaterno }} {{ $e->amaterno }}</td>
                        <td class="text-center">
                            @if (count($e->timbres) > 0)
                            {{ $e->timbres[0]->num_factura }}
                            @endif
                        </td>
                        <td class="text-center"> {{ ucfirst($e->tipo_de_nomina)}}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                @if(count($e->timbres) > 0)
                                    @if(count($e->timbres) == 1 &&  (isset($e->timbres[0]->sello_sat) && $e->timbres[0]->sello_sat == 'error'))
                                        @if(count($e->timbres_cancelados) > 0)
                                            @if(count($e->timbres_cancelados) > 0 && $e->ultimo_timbre['sello_sat'] == 'error')
                                                <p class="text-danger"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al timbrar</p>
                                            @else
                                                <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                            @endif
                                            <a name="xml" id="xml" class="button-style-custom btn-sm my-1" href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button">Descargar Comprobante</a>
                                            <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom btn-sm my-1" href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->timbres[0]->id]) }}"target="_blank" role="button">Verificar Estatus</a>
                                            <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1" role="button">Retimbrar</a>
                                        @else
                                            <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Timbrar</a>
                                        @endif
                                    @else
                                        @if(count($e->timbres_cancelados) == 1 && $e->numero_timbres_noerror == 1)
                                            <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                            <a name="xml" id="xml" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button">Descargar Comprobante</a>
                                            <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$$e->timbres[0]->id]) }}" target="_blank" role="button">Verificar Estatus</a>
                                            <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Retimbrar</a>
                                        @elseif($e->numero_timbres_noerror > 1 && $e->timbres[0]->sello_sat == 1 && count($e->timbres_cancelados) > 0 )
                                            <a name="recibo" id="recibo" class="button-style-custom btn-sm my-1" href="{{ route('timbrar.nomina_empleado.ver_pdf',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button">Imprimir Recibo</a>
                                            <a name="xml" id="xml" class="button-style-custom btn-sm my-1" href="{{ route('timbrar.nominaEmpleado.descargarXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button">Descargar XML</a>
                                            <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom btn-sm my-1" href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->timbres[0]->id]) }}" target="_blank" role="button">Verificar Estatus</a>
                                        @else
                                            <a name="recibo" id="recibo" class="button-style-custom my-1 " href="{{ route('timbrar.nomina_empleado.ver_pdf',[$e->id,$repo,$e->timbres[0]->file_xml,$e->timbres[0]->id]) }}" target="_blank" role="button">Imprimir Recibo</a>
                                            <a name="xml" id="xml" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.descargarXml',[$e->id,$repo,$e->timbres[0]->file_xml]) }}" target="_blank" role="button">Descargar XML</a>
                                            <a name="timbrar" id="timbrar" class="button-style-custom my-1" href="{{ route('cancelar.timbre_empleado',[$e->timbres[0]->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" role="button">Cancelar Timbre</a>
                                        @endif
                                    @endif
                                @elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1 && $e->ultimo_timbre->sello_sat == 'error')
                                    <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado/Error al Procesar Vuelve a Timbrar</p>
                                    <a name="xml" id="xml" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres_cancelados[0]->file_soap]) }}" target="_blank" role="button">Descargar Comprobante</a>
                                    <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button">Verificar Estatus</a>
                                    <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Retimbrar</a>
                                @elseif($e->timbres_cancelados->count() == 1 && $e->numero_timbres_noerror == 1)
                                    <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> CFDI Cancelado</p>
                                    <a name="xml" id="xml" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.descargarSoapXml',[$e->id,$repo,$e->timbres_cancelados[0]->file_soap]) }}" target="_blank" role="button">Descargar Comprobante</a>
                                    <a name="vercfdicancel" id="vercfdicancel" class="button-style-custom btn-sm my-1 " href="{{ route('timbrar.nominaEmpleado.verificarEstatus',[$e->ultimo_timbre->id]) }}" target="_blank" role="button">Verificar Estatus</a>
                                    <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Retimbrar</a>
                                @elseif($e->timbres_cancelados->count() > 0 && $e->ultimo_timbre->sello_sat == 'error')
                                    <p  class="text-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error al procesar vuelve a timbrar</p>
                                    <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Retimbrar</a>
                                @elseif($e->timbres_cancelados->count() > 0 )
                                    <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Retimbrar</a>
                                @else
                                    @if( $e->ultimo_timbre != null && (!is_array($e->ultimo_timbre)  && $e->ultimo_timbre->sello_sat === "error") )
                                        <p class="text-danger">{{$e->ultimo_timbre->mensaje_error ?? 'Error en proceso de timbrado'}}</p>
                                        <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Timbrar</a>
                                    @else
                                        <a href="{{ route('timbrar.validar.empleado',[$e->id, base64_encode($cadena_departamentos), $periodo->id, $valorRegresar])}}" name="timbrar" id="timbrar" class="button-style-custom btn-sm my-1 " role="button">Timbrar</a>
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
@include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_nomina').DataTable({
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


        $('#filtro').on( 'change', function () {
            table
                .columns( 0 )
                .search( this.value )
                .draw();
        });
    </script>
