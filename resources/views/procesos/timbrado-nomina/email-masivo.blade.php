<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
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
            <input type="hidden" name="id_periodo" value="{{$periodo}}">
            <input type="hidden" value="1" name="todos" id="todos">
            <input type="hidden" value="{{ $cadena_departamentos }}" name="deptos" id="deptos">
            @include('includes.header',['title'=>'Timbrado factura',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'timbrar.nomina'])
            
        </form> 
    @endif
          
    <div class="article border">
        <div class="box-alert">
            <div class="alert bg-color-yellow text-white text-center">
                <!-- <i class="fa fa-info"></i> -->
                <h5><strong>Se enviaron los avisos correctamente.</strong></h5>               
            </div>
        </div>        
    </div>

</div>
