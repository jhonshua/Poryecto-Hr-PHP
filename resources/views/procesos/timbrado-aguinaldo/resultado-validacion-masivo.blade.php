<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($departamentos);
@endphp

<div class="container">

        <form id="formulario1" method="POST" action="{{route('timbrar.aguinaldo.paso2')}}">
            @csrf
            <input type="hidden" name="id_periodo" value="{{$id_periodo}}" >
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">            
            @include('includes.header-alt', ['title'=>'Timbrado factura',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
        </form>

        <div class="article border">
            <div class="row">
                <div class=" col-12 text-center">
                    <h5>Empleados</h5>
                    @if ($errores['empleados'] > 0)
                        <div class="alert alert-success" role="alert">
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
                            
            </div>
            <div class="">
                <div class="row d-flex justify-content-center">
                    @if ($errores['empleados'] == 0 )
                        <a class="button-style my-2" href="{{route('timbrar.aguinaldo.masivo.bucle', ['cadena' => base64_encode($cadena_departamentos)])}}">Timbrar</a>                                  
                    @endif
                </div>                
            </div>

        </div>
</div>
@include('includes.footer')