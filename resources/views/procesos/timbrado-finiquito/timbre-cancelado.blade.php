<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="container">    
    <div class="col-12 text-center">
        <form method="GET" action="{{route('timbrar.finiquito.inicio')}}">
              @csrf
              <input type="hidden" name="ejercicio" value="{{$data_respuesta['anio_ejercicio']}}">
              @include('includes.header-alt', ['title'=>'Cancelación finiquito',
                'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
          </form>       
    </div> 

    <div class="article border">    
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            @if ($data_respuesta['error'])
                            <span class="badge badge-danger float-right" style="font-size:150%">Error de Timbrado</span>
                            @else
                                <span class="badge badge-success float-right" style="font-size:150%">Cancelado</span>
                            @endif
                        </h5>
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "CANCELAR". CFDI VERSIÓN 4.0</p>
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
                    <div id="accordion">
                        <div class="card">
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
                <div class="card border-danger mb-3">
                    <div class="card-header"><h5> {{$data_respuesta['mnsg']}}</h5></div>
                    <div class="card-body text-danger">
                        <div id="accordion">
                            <div class="card">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Respuesta del PAC
                                </button>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                    <div class="card-body">
                                        {{ $data_respuesta['respuesta'] }}
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
@include('includes.footer')