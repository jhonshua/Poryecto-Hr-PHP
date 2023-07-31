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
        'route'=>'timbrar.nomina.periodo'.$valorPeriodo])

        @else
        <form method="POST" action="{{route('timbrar.nomina')}}">
            @csrf
            <input type="hidden" name="id_periodo" value="{{$periodo->id}}">
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">
            @include('includes.header',['title'=>'Timbrado factura',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
            'route'=>'timbrar.nomina'])

        </form>
        @endif

        <div class="article border">
            <div class=" col-12 text-center">
                <h5>Empleado</h5>
                @if ($errores['empleados'] > 0)
                <div class="alert alert-danger" role="alert">
                    <span>Tienes <strong>{{ $errores['empleados'] }}</strong> errores</span>
                </div>
                @endif
                <table class="table">
                    <thead>
                        <tr>
                            <th class="border-warning">ID</th>
                            <th class="border-warning">Nombre</th>
                            <th class="border-warning">Validación</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td scope="row">{{ $empleado['id'] }}</td>
                            <td>{{ $empleado['nombre'] }} </td>
                            <td>
                                <ul>
                                    @if($empleado['errores']['rfc'])
                                    <li class="text-danger">RFC Incorrecto</li>
                                    @else
                                    <li class="text-success">RFC Correcto</li>
                                    @endif
                                    @if($empleado['errores']['nss'])
                                    <li class="text-danger">NSS Incorrecto</li>
                                    @else
                                    <li class="text-success">NSS Correcto</li>
                                    @endif
                                    @if($empleado['errores']['curp'])
                                    <li class="text-danger">CURP Incorrecto</li>
                                    @else
                                    <li class="text-success">CURP Correcto</li>
                                    @endif
                                    @if($empleado['errores']['registro_patronal'])
                                    <li class="text-danger">Registro Patronal Incorrecto</li>
                                    @else
                                    <li class="text-success">Registro Patronal Correcto</li>
                                    @endif
                                </ul>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class=" col-12 text-center">
                <h5>Conceptos</h5>
                @if ($errores['conceptos'] > 0)
                <div class="alert alert-danger" role="alert">
                    <span>Tienes <strong>{{ $errores['conceptos'] }}</strong> errores</span>
                </div>
                @endif
                <table class="table">
                    <thead>
                        <tr>
                            <th class="border-warning">ID</th>
                            <th class="border-warning">Nombre</th>
                            <th class="border-warning">Codigo SAT</th>
                            <th class="border-warning">Validación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conceptos as $c)
                        <tr>
                            <td scope="row">{{ $c['id'] }}</td>
                            <td>{{ $c['nombre'] }} </td>
                            <td>{{ $c['codigo_sat'] }} </td>
                            <td>
                                <ul>
                                    @if($c['errores']['sat'])
                                    <li class="text-danger">SAT Incorrecto</li>
                                    @else
                                    <li class="text-success">SAT Correcto</li>
                                    @endif
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if ($errores['empleados'] == 0 && $errores['conceptos'] == 0)
        <div class="row justify-content-center">
            <div class="col-xs-12 col-md-3 col-lg-2 my-2">
                <a class="button-style btn-block " href="{{route('timbrar.nomina_empleado', [$empleado['id'], base64_encode($cadena_departamentos), 1, $regresa, $periodo->id])}}">Timbrar</a>
            </div>
        </div>
        @endif
    </div>