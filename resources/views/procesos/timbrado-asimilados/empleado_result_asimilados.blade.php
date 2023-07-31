@extends('layouts.principal')
@section('tituloPagina', "Timbrado Asimilados")

@section('content')
<div class="row mt-5">
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
                <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "Nómina". CFDI VERSIÓN 3.3</p>
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
    <div class="col-sm-8">
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
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body  text-center">
                <h5 class="card-title font-weight-bold">Opciones</h5>
                <img src="{{ asset($data_respuesta['qr_url'])}}" class="img">
                
                <a href="{{ route('contabilidad.timbrar.nomina_empleado.ver_pdf_asimilados',[$data_respuesta['id_usuario'],$data_respuesta['id_repo'],$data_respuesta['archivo_xml'],$data_respuesta['id_timbre']]) }}" class="btn btn-block btn-dark font-weight-bold" target="_BLANK">Imprimir Factura</a>    
                
                <form method="POST" action="{{route('contabilidad.timbrar.asimilados')}}">
                  @csrf
                  <input type="hidden" name="id_periodo" value="{{$data_respuesta['id_periodo']}}" >
                  <input type="hidden" value="1" name="todos" id="todos">
                  <input type="hidden" value="{{ $data_respuesta['c_cadena'] }}" name="deptos" id="deptos">
                  <input type="submit" class="btn btn-block btn-dark font-weight-bold my-2" value="Regresar">
              </form>  
            </div>
        </div>
    </div>
    @endif
</div>
@endsection