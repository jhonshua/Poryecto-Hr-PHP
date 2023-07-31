@extends('layouts.principal')
@section('tituloPagina', "Cancelar Factura")

@section('content')
<div class="row mt-5">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    @if ($data_respuesta['error'])
                    <span class="badge badge-danger float-right" style="font-size:150%">Error de Timbrado</span>
                    @else
                        <span class="badge badge-dark float-right" style="font-size:150%">Cancelada</span>
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

            <div class="card-body text-danger">
              <p class="card-text">{{$data_respuesta['error_msg']}}</p>

            </div>
          </div>
    </div>


    @else
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title font-weight-bold">Respuesta PAC</h5>
                <p> {{ ($data_respuesta['soap'])}}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body  text-center">
                <h5 class="card-title font-weight-bold">Opciones</h5>
                <a href="{{route('factura.index')}}" class="btn btn-block btn-dark font-weight-bold"> <- Regresar</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection