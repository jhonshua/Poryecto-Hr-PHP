<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">

    <div class="col-12 text-center">        
        @include('includes.header',['title'=>'Timbrado finiquito',
                'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
                'route'=>'timbrar.finiquito.inicio'])  
    </div>  
    <div class="article border">
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            @if (isset($data_respuesta['error']))
                            <span class="badge badge-danger float-right" style="font-size:150%">Error de Timbrado</span>
                            @else
                                <span class="badge badge-success float-right" style="font-size:150%">Timbrado</span>
                            @endif
                        </h5>
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "Finiquito". CFDI VERSIÓN 4.0</p>
                        <p class="font-weight-bold">ZONA HORARIA PREDETERMINADA</p>
                        <p>{{ $data_respuesta['timezone']}}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            @if (isset($data_respuesta['error']))
            <div class="col-sm-12">
                <div class="card border-danger mb-3">
                    <div class="card-header"><h5>Error {{$data_respuesta['codigo_error']}}</h5></div>
                    <div class="card-body text-danger">
                    <p class="card-text">{{$data_respuesta['MENSAJE_error']}}</p>
                    <div id="accordion">
                        <div class="card">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                XML Enviado
                            </button>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                            {{$data_respuesta['xml_enviar']}}
                            </div>
                        </div>
                        </div>
                        <div class="card">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Respuesta del PAC
                            </button>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                {{$data_respuesta['respuesta']}}
                            </div>
                        </div>
                        </div>

                    </div>
                    </div>
                </div>
            </div>             

            @else
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title font-weight-bold">Respuesta PAC</h5>
                        <p><strong>Versión de CFDI:</strong>  {{ ($data_respuesta['version_cfdi'])}}</p>
                        <p><strong>Sello del SAT:</strong> {{ ($data_respuesta['sello_sat'])}}</p>
                        <p><strong>Certificado del SAT:</strong> {{($data_respuesta['cert_sat'])}}</p>
                        <p><strong>Sello del CFDI:</strong> {{ ($data_respuesta['sello_cfd'])}}</p>
                        <p><strong>Folio fiscal:</strong> {{ ($data_respuesta['timbre_uuid'])}}</p>
                        <p><strong>No. de factura asignado por el sistema local:</strong> {{ ($data_respuesta['no-fac'])}}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body  text-center">
                        <h5 class="card-title font-weight-bold ">Opciones</h5>
                        <div class="mb-2">
                            <img class="img" src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->generate($data_respuesta['qr_url']), '<?xml version="1.0" encoding="UTF-8" ?>')) }}">
                        </div>
                        
                        <a href="{{ route('timbrar.nomina_empleado.ver_pdf',[$data_respuesta['id_empleado'],$data_respuesta['id_repo'],$data_respuesta['archivo_xml'],$data_respuesta['id_timbrado']]) }}" class="button-style text-nowrap" target="_BLANK">Imprimir Factura</a>
                         
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>

</div>
@include('includes.footer')
   
<script>    
    $(function(){
       
    });
</script>
