<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">
    @if($regresa == 'R')
    @include('includes.header',['title'=>'Timbrado factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.recibos.nomina.inicio'])
      
    @else
        <form method="POST" action="{{route('timbrar.nomina')}}">
            @csrf
            <input type="hidden" name="id_periodo" value="{{$periodo->id}}" >
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">
            @include('includes.header',['title'=>'Timbrado factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'timbrar.nomina'])
        </form>
    @endif

    <div class="article border">
        <div class="row mt-5">
            <div class=" col-12 text-center">
                <h5>Empleados</h5>
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
                        @foreach ($empleados as $e)
                        <tr>
                            <td scope="row">{{ $e['id'] }}</td>
                            <td>{{ $e['nombre'] }} </td>
                            <td>
                                <ul>
                                    @if($e['errores']['rfc'])
                                        <li class="text-danger">RFC Incorrecto</li>
                                    @else
                                        <li class="text-success">RFC Correcto</li>
                                    @endif
                                    @if($e['errores']['nss'])
                                        <li class="text-danger">NSS Incorrecto</li>
                                    @else
                                        <li class="text-success">NSS Correcto</li>
                                    @endif
                                    @if($e['errores']['curp'])
                                        <li class="text-danger">CURP Incorrecto</li>
                                    @else
                                        <li class="text-success">CURP Correcto</li>
                                    @endif
                                    @if($e['errores']['registro_patronal'])
                                        <li class="text-danger">Registro Patronal Incorrecto</li>
                                    @else
                                        <li class="text-success">Registro Patronal Correcto</li>
                                    @endif
                                </ul>
                            </td>
                        </tr>
                        @endforeach
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
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Codigo SAT</th>
                            <th>Validación</th>
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
        <div class="text-center">
            @if ($errores['empleados'] == 0 && $errores['conceptos'] == 0)
                <a class="button-style my-2" href="{{route('timbrar.nominaMasivoBucle',['cadena' => base64_encode($cadena_departamentos), $regresa])}}">Timbrar</a>
            @endif
        </div>
    </div>
</div>
<script>
    $(function(){
    });
</script>
