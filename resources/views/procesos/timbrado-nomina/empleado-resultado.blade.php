<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <!-- <link rel="icon" type="image/x-icon" href="favicon.ico"> -->

    <!-- Title app -->
    <title>HR-System</title>



  <!-- Styles -->
  <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/> -->
    <!-- alertify -->
    <!-- <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/semantic.min.css"/> -->
    <link href="{{asset('css/styles.css')}}" rel="stylesheet">

    <!-- JavaScript -->

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script> -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.18/vue.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="{{asset('js/main.js')}}"></script>



</head>
<body>
@include('includes.navbar')
<div class="container">
    @if($regresa == 'R')
    @include('includes.header',['title'=>'Timbrado factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.recibos.nomina.inicio'])
        
    @else
        <form method="POST" action="{{route('timbrar.nomina')}}">
            @csrf
            <input type="hidden" name="id_periodo" value="{{$data_respuesta['id_periodo']}}" >
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $data_respuesta['c_cadena'] }}" name="deptos" id="deptos">
            @include('includes.header',['title'=>'Timbrado de nómina',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'timbrar.nomina'])
        </form>
    @endif

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
                        <p class="card-text text-danger font-weight-bold">PROCESO DE TIMBRADO PARA "Nómina". CFDI VERSIÓN 4.0</p>
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
                                XML Enviado
                            </button>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                            {{ $data_respuesta['xml_enviar'] }}
                            </div>
                        </div>
                        </div>
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
                        <h5 class="card-title font-weight-bold">Opciones</h5><div class="my-3">
                            <a href="{{ route('timbrar.nomina_empleado.ver_pdf',[$data_respuesta['id_usuario'],$data_respuesta['id_repo'],$data_respuesta['archivo_xml'],$data_respuesta['id_timbre']]) }}" class="button-style" target="_BLANK">Imprimir Factura</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>

</div>
