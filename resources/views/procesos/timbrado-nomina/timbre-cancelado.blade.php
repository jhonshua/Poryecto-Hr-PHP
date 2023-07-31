<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="ml-5 pl-5">
@if($regresa == 'R')

@include('includes.header',['title'=>'Timbre cancelado',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.recibos.nomina.inicio'])

@else
    <form method="POST" action="{{route('timbrar.nomina')}}">
        @csrf
        <input type="hidden" name="id_periodo" value="{{$periodo}}" >
        <input type="hidden" value="1" name="todos" id="todos">
        <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">

        @include('includes.header',['title'=>'Timbre cancelado',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'timbrar.nomina'])

    </form>
@endif
</div>
@if(!isset($data_respuesta['error']))                                
<div class="container">   
    <div class="article border">
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">                            
                            <span class="badge badge-danger float-right" style="font-size:150%">Error en el proceso</span>                          
                        </h5>
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "CANCELAR". CFDI VERSIÓN 3.2</p>
                        <p class="font-weight-bold">No. DE FACTURA ASIGNADO POR EL SISTEMA LOCAL:</p>
                        <p>{{ $data_respuesta['no_factura']}}</p>
                        <p class="font-weight-bold">FOLIO FISCAL:</p>
                        <p>{{ $data_respuesta['folio_fiscal']}}</p>
                        
                    </div>
                </div>
            </div>
        </div> 

    </div>  
</div>
@else

<div class="container">   
    <div class="article border">
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">                            
                            @if($data_respuesta['error'])
                            <span class="badge badge-danger float-right" style="font-size:150%">Error de Timbrado</span>
                            @else
                                <span class="badge badge-success float-right" style="font-size:150%">Cancelado</span>
                            @endif                           
                        </h5>
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "CANCELAR". CFDI VERSIÓN 3.2</p>
                        <p class="font-weight-bold">No. DE FACTURA ASIGNADO POR EL SISTEMA LOCAL:</p>
                        <p>{{ $data_respuesta['no_factura']}}</p>
                        <p class="font-weight-bold">FOLIO FISCAL:</p>
                        <p>{{ $data_respuesta['folio_fiscal']}}</p>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            @if ($data_respuesta['error'])
            <div class="col-sm-12">
                <div class="card border-danger mb-3">
                    <div class="card-header"><h5> {{$data_respuesta['codigo_error']}}</h5></div>
                    <div class="card-body text-danger">
                    <p class="card-text">{{$data_respuesta['error_msg']}}</p>
                    <div id="accordion">
                        <div class="card">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                XML Envidalo
                            </button>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                            {{!! $data_respuesta['contenido'] !!}}
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
            <div class="col-sm-12">
                <div class="card border-danger mb-3">
                    <div class="card-header"><h5> {{$data_respuesta['mnsg']}}</h5></div>
                    <div class="card-body text-danger">
                    <div id="accordion">
                        <div class="card">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" disabled="true">
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
            @endif
        </div>

    </div>  
</div>
@endif