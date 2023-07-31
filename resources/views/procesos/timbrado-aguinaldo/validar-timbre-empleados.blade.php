<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">

    <div class="col-12 text-center">        
        
        <form id="formulario1" method="POST" action="{{route('timbrar.aguinaldo.paso2')}}">
            @csrf
            <input type="hidden" name="id_periodo" value="{{$periodo->id}}" >
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">            
            @include('includes.header-alt', ['title'=>'Timbrado factura',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
        </form>
    </div>      

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
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Validación</th>
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
        @if ($errores['empleados'] == 0)
            <div class=" col-12 text-center">
                <a class="button-style mb-2" href="{{route('timbrar.aguinaldo.empleado', [$empleado['id'], base64_encode($cadena_departamentos), 01])}}">Timbrar</a>
            </div>
        @endif
    </div>
</div>
@include('includes.footer')
   
<script>    
    $(function(){
       
    });
</script>
