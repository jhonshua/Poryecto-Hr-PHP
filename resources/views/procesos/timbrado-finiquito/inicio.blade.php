<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">
    @include('includes.header',['title'=>'Timbrado finiquito',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'bandeja'])

    <div class="row d-flex justify-content-between">
        <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9 ml-1 px-0">
            
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <form id="form_finiquito" action="{{route('timbrar.finiquito.inicio')}}" method="get">
                    @csrf
                    <div class="form-group row">
                        <label class="form-check-label col-form-label mr-1 ml-2">Ejercicio:</label>
                        <div class="mr-3 mb-2">
                            <select class="form-control col-form-label input-style-custom select-clase" name="ejercicio" id="ejercicio">
                                @foreach($datos_ejercicio as $dato)
                                    @if(intval($anio) === intval($dato->ejercicio))
                                        <option selected="{{$anio}}" value="{{$dato->ejercicio}}">{{$dato->ejercicio}}</option>
                                    @else
                                        <option value="{{$dato->ejercicio}}">{{$dato->ejercicio}}</option>
                                    @endif
                                @endforeach
                            </select> 
                        </div>
                        <div class="col-form-label">
                            <a id="timbrar_masivo" class="button-style text-nowrap ml-2" href="{{route('validacion.masiva.finiquito', [$anio])}}">Timbrar de Forma Masiva</a>
                        </div>
                    </div>
                </form>
            </div> 
        </div>
        <div class="dataTables_filter col-sm-12 col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
    </div>
    <div class="article border"> 
        <table class="table w-100" id="tabla_timbrado_finiquito">
            <thead class="">
                    <tr>
                        <th width="">ID</th>
                        <th width="">No. Empleado</th>
                        <th width="">Nombre</th>
                        <th width="">Importe</th>                       
                        <th width="">No. Timbre</th> 
                        <th width="">Fecha Baja</th>           
                        <th width="">Estatus</th>
                        <th width="">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datos_timbrado_finiquito as $dato)
                        <tr>
                            <td>{{$dato->id_empleado}}</td>
                            <td>{{$dato->numero_empleado}}</td>   
                            <td>{{$dato->nombre}}</td>             
                            <td>{{$dato->neto_fiscal}}</td> 
                            <td>{{$dato->NoFactura}}</td> 
                            <td>{{$dato->fecha_baja}}</td>
                            <td>
                                @if ($dato->estatus_timbre == 1)
                                    <span class="badge badge-success">Timbrado</span>
                                @endif
                                @if ($dato->estatus_timbre == 0)
                                    <span class="badge badge-warning">En proceso</span>
                                @endif
                                @if ($dato->estatus_timbre == 5 || $dato->estatus_timbre==2)
                                    <span class="badge badge-danger">Cancelado</span>
                                @endif
                            </td>
                            <td>
                                @php 
                                    $idempleado = base64_encode($dato->id_empleado);
                                    $fact = base64_encode($dato->NoFactura?$dato->NoFactura:0);
                                    $archivo_xml = base64_encode($dato->file_xml);
                                    $archivo_pdf = base64_encode($dato->file_pdf);
                                    $id_periodo = $dato->id_periodo;
                                    $folio_fiscal = base64_encode($dato->folio_fiscal);
                                @endphp

                                <div class="text-center">
                                    @if ($dato->estatus_timbre == 1)

                                            <a class="button-style-custom text-nowrap" target="_blank" href="{{route('imprimir.recibo.finiquito', [base64_decode($idempleado),base64_decode($archivo_xml)])}}">Imprimir Recibo</a>
                                            <a class="button-style-custom text-nowrap" href="{{route('descargar.xml', [$idempleado, $archivo_xml])}}" target="_blank">Descargar XML</a>
                                            <a class="button-style-custom text-nowrap" href="{{ route('cancelar.cfdi', [$idempleado, $fact, $anio]) }}">Cancelar CFDI</a>

                                    @endif
                                    @if ($dato->estatus_timbre == 0)

                                            <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Timbrar</a>

                                    @endif
                                    @if ($dato->estatus_timbre == 2)
                                            <a class="button-style-custom text-nowrap" target="_blank" href="{{route('imprimir.recibo.finiquito', [base64_decode($idempleado),base64_decode($archivo_xml)])}}">Imprimir Recibo</a>
                                            <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>

                                    @endif
                                    @if ($dato->estatus_timbre == 5)

                                            <a class="button-style-custom text-nowrap" target="_blank" href="{{route('imprimir.recibo.finiquito', [base64_decode($idempleado),base64_decode($archivo_xml)])}}">Imprimir Recibo</a>
                                            <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>

                                    @endif

                                    @if ($dato->count_canc_tim_finiquito > 1)
                                        <a class="button-style-custom text-nowrap" href="{{ route('ver.cfdi.cancelados.finiquito', [$idempleado, $fact, $id_periodo,$anio]) }}">Ver CFDI Cancelados</a>

                                    @endif
                                </div>



{{--                                @if($dato->count_estatus_timbre > 0)
                                        @if($dato->count_estatus_timbre==1 && $dato->count_canc_tim_finiquito>0 && $dato->sello_sat=='error')
                                            <div class="text-center">
                                                <p class="text-danger">Error al procesar, vuelve a Timbrar</p>
                                                <a class="button-style-custom text-nowrap" href="{{ route('descargar.comprobante.finiquito', [$idempleado, $archivo_xml, $anio]) }}">Descargar Comprobante</a>
                                                <a class="button-style-custom text-nowrap" target="_blank" href="{{ route('verificar.estatus.finiquito', [$idempleado, $folio_fiscal, $anio]) }}">Verificar Estatus</a>
                                                <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>
                                            </div>
                                        @elseif($dato->count_canc_tim_finiquito==1 && $dato->count_retimbrar==1)
                                            <div class="text-center">
                                                <p>CFDI Cancelado</p>
                                                <a class="button-style-custom text-nowrap" href="{{ route('descargar.comprobante.finiquito', [$idempleado, $archivo_xml, $anio]) }}">Descargar Comprobante</a>
                                                <a class="button-style-custom text-nowrap" target="_blank" href="{{ route('verificar.estatus.finiquito', [$idempleado, $folio_fiscal, $anio]) }}">Verificar Estatus</a>
                                                <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>
                                            </div>
                                        @elseif($dato->count_retimbrar>1 && $dato->estatus_timbre==1 && $dato->count_canc_tim_finiquito>0)
                                            <div class="text-center">
                                                <a class="button-style-custom text-nowrap" target="_blank" href="{{route('imprimir.recibo.finiquito', [base64_decode($idempleado),base64_decode($archivo_xml)])}}">Imprimir Recibo</a>
                                                <a class="button-style-custom text-nowrap" href="{{route('descargar.xml', [$idempleado, $archivo_xml])}}" target="_blank">Descargar XML</a>
                                                <a class="button-style-custom text-nowrap" href="{{ route('cancelar.cfdi', [$idempleado, $fact, $anio]) }}">Cancelar CFDI</a>
                                                <a class="button-style-custom text-nowrap" href="{{ route('ver.cfdi.cancelados.finiquito', [$idempleado, $fact, $anio]) }}">Ver CFDI Cancelados</a>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <a class="button-style-custom text-nowrap" target="_blank" href="{{ route('imprimir.recibo.finiquito',[base64_decode($idempleado),base64_decode($archivo_xml)])}}">Imprimir Recibo</a>
                                                <a class="button-style-custom text-nowrap" href="{{route('descargar.xml', [$idempleado, $archivo_xml])}}" target="_blank">Descargar XML</a>
                                                <a class="button-style-custom text-nowrap" href="{{ route('cancelar.cfdi', [$idempleado, $fact, $anio]) }}">Cancelar CFDI</a>
                                            </div>
                                        @endif
                                @elseif($dato->count_canc_tim_finiquito==1 && $dato->count_retimbrar==1 && $dato->sello_sat=='error')
                                    <div class="text-center">
                                        <p class="text-danger">CFDI Cancelado/Error, al Procesar Vuelve a Timbrar</p>
                                        <a class="button-style-custom text-nowrap" href="{{ route('descargar.comprobante.finiquito', [$idempleado, $archivo_xml, $anio]) }}">Descargar Comprobante</a>
                                        <a class="button-style-custom text-nowrap" target="_blank" href="{{ route('verificar.estatus.finiquito', [$idempleado, $folio_fiscal, $anio]) }}">Verificar Estatus</a>
                                        <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>
                                    </div>
                                @elseif($dato->count_canc_tim_finiquito==1 && $dato->count_retimbrar==1)
                                    <div class="text-center">
                                        <p>CFDI Cancelado</p>
                                        <a class="button-style-custom text-nowrap" href="{{ route('descargar.comprobante.finiquito', [$idempleado, $archivo_xml, $anio]) }}">Descargar Comprobante</a>
                                        <a class="button-style-custom text-nowrap" target="_blank" href="{{ route('verificar.estatus.finiquito', [$idempleado, $folio_fiscal, $anio]) }}">Verificar Estatus</a>
                                        <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>
                                    </div>
                                @elseif($dato->count_canc_tim_finiquito>0)
                                  <div class="text-center">
                                    <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Retimbrar</a>
                                    <a class="button-style-custom text-nowrap" href="{{ route('ver.cfdi.cancelados.finiquito', [$idempleado, $fact, $anio]) }}">Ver CFDI Cancelados</a>
                                  </div>
                                @elseif($dato->sello_sat=='error')
                                    <div class="text-center">
                                        <p class="text-danger">Error al procesar, vuelve a Timbrar</p>
                                        <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Timbrar</a>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <a class="button-style-custom text-nowrap" href="{{route('validaciones.timbrado.finiquito', [$idempleado, $fact, $anio])}}">Timbrar</a>
                                    </div>
                                @endif --}}
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
    let table = $('#tabla_timbrado_finiquito').DataTable({
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
        // $('.select-clase').select2();
        $( "#ejercicio" ).change(function() {
            $('#form_finiquito').submit();
        });

    });


</script>
