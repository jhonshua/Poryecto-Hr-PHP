<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($data_respuesta['c_cadena']); 
@endphp

<div class="container">
    <form method="POST" action="{{route('timbrar.aguinaldo.paso2')}}">
        @csrf
        <input type="hidden" name="id_periodo" value="{{$data_respuesta['id_periodo']}}" >
        <input type="hidden" value="1" name="todos" id="todos">
        <input type="hidden" value="{{ $data_respuesta['c_cadena'] }}" name="deptos" id="deptos">
        @include('includes.header-alt', ['title'=>'Timbrado factura',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
    </form>
    <div class="article border">     
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            @if ($data_respuesta['error'])
                            <span class="badge badge-danger float-right" style="font-size:150%">Error de Timbrado</span>
                            @else
                                <span class="badge badge-success float-right" style="font-size:150%">Timbrado</span>
                            @endif
                        </h5>
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "AGUINALDO". CFDI VERSIÓN 3.3</p>
                        <p class="font-weight-bold">ZONA HORARIA PREDETERMINADA</p>
                        <p>{{ $data_respuesta['timezone']}}</p>
                        <p class="font-weight-bold">FECHA Y HORA DE SOLICITUD DE TIMBRADO</p>
                        <p>{{ $data_respuesta['fecha_tim']}}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            @if ($data_respuesta['error'])
            <div class="col-sm-12">
                <div class="card border-danger mb-3">
                    <div class="card-header"><h5>Error {{$data_respuesta['codigo_error']}}</h5></div>
                    <div class="card-body text-danger">
                    <p class="card-text">{{$data_respuesta['MENSAJE_error']}}</p>
                    <div id="accordion">
                        <div class="card">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                XML Envidalo
                            </button>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                            {{!! $data_respuesta['xml_enviar'] !!}}
                            </div>
                        </div>
                        </div>
                        <div class="card">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Respuesta del PAC
                            </button>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                {{!! $data_respuesta['respuesta'] !!}}
                            </div>
                        </div>
                        </div>

                    </div>
                    </div>
                </div>
            </div>


            @else
            <div class="col-sm-12 col-xs-12 col-md-12 col-lg-8 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title font-weight-bold">Respuesta PAC</h5>
                        <p><strong>Versión de CFDI:</strong>  {{ ($data_respuesta['version_cfdi'])}}</p>
                        <p><strong>Versión de timbre:</strong> {{ ($data_respuesta['version_timbre'])}}</p>
                        <p><strong>Sello del SAT:</strong> {{ ($data_respuesta['sello_sat'])}}</p>
                        <p><strong>Certificado del SAT:</strong> {{($data_respuesta['cert_sat'])}}</p>
                        <p><strong>Sello del CFDI:</strong> {{ ($data_respuesta['sello_cfd'])}}</p>
                        <p><strong>Fecha de timbrado:</strong> {{ ($data_respuesta['fecha_tim'])}}</p>
                        <p><strong>Folio fiscal:</strong> {{ ($data_respuesta['timbre_uuid'])}}</p>
                        <p><strong>No. de factura asignado por el sistema local:</strong> {{ ($data_respuesta['no-fac'])}}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-xs-12 col-md-12 col-lg-4">
                <div class="card">
                    <div class="card-body  text-center">
                        <h5 class="card-title font-weight-bold">Opciones</h5>
                        <img src="{{ asset($data_respuesta['qr_url'])}}" class="img">
                        
                        <div class="my-3">
                            <a href="{{ route('pdf-timbrar-aguinaldo',[$data_respuesta['id_usuario'],$data_respuesta['id_repo'],$data_respuesta['archivo_xml'],$data_respuesta['id_timbre']]) }}" class="button-style" target="_BLANK">Imprimir Factura</a>    
                        </div>
                        
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@include('includes.footer')