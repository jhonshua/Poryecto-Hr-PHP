<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
$id_empleado = base64_encode($empleados[0]['id']);
@endphp

<div class="container">

    <div class="col-12 text-center">        
        @include('includes.header',['title'=>'Timbrado finiquito',
            'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
            'route'=>'timbrar.finiquito.inicio'])    
    </div>      

    <div class="article border">
        <div class="row mt-3">
            <div class="col-12 text-center">
                <h5>Empleados</h5>
                @if ($errores['empleados'] == 1)
                    <div class="alert alert-success" role="alert">
                        <span>Tienes <strong>{{ $errores['empleados'] }}</strong> error</span>
                    </div>
                @elseif($errores['empleados'] > 1)
                    <div class="alert alert-success" role="alert">
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
                @if ($errores['conceptos'] == 1)
                    <div class="alert alert-danger" role="alert">
                        <span>Tienes <strong>{{ $errores['conceptos'] }}</strong> error</span>
                    </div>
                @elseif($errores['conceptos'] > 1)
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

    </div>
    <div class="row justify-content-center">
        <div class="">
            <form action="{{route('timbrar.finiquito.empleado', [$id_empleado, $anio_ejercicio,$rutina->id, 1])}}" method="GET" id="idTimbrado">
                <input type="hidden" name="anio_ejercicio" value="{{$anio_ejercicio}}">                
                @if ($errores['empleados'] == 0 && $errores['conceptos'] == 0)
                    <button class="button-style my-3" id="timbrar-btn" onclick="">TIMBRAR</button>
                @endif                            
            </form>
        </div>
    </div>

</div>
@include('includes.footer')
   
<script>    
    $(function(){
        $("#timbrar-btn").on("click", function(e){
            e.preventDefault();
            // $(this).attr(disabled,false);
            $("#idTimbrado").submit();
        });
    });
</script>
