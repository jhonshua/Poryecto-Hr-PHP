@extends('layouts.principal')
@section('tituloPagina', "Timbrado Asimilados")

@section('content')
<div class="row mt-5">
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
<pre>
     {{-- {{ dd($errores) }}  --}}
</pre>
@if ($errores['empleados'] == 0 && $errores['conceptos'] == 0)
    <div class=" col-12 text-center">
        <a class="btn btn-lg btn-dark mb-2" href="{{route('contabilidad.timbrar.nomina_empleado_asimilados',['id_empelado'=>$empleado['id'],'cadena' => $cadena_departamentos,'tipo'=>01,'periodo'=>$periodo->id])}}">Timbrar</a>
    </div>
@else
    <div class="col-12 text-center">
    <form method="POST" action="{{route('contabilidad.timbrar.asimilados')}}">
        @csrf
        <input type="hidden" name="id_periodo" value="{{$periodo->id}}" >
        <input type="hidden" value="1" name="todos" id="todos">
        <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">
        <input type="submit" class="btn btn-lg btn-dark mb-2" value="Regresar">
    </form>
    </div>
@endif

@endsection

@push('scripts')
    <script>

  </script>
@endpush