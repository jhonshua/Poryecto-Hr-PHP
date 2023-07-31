<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

    @include('includes.header',['title'=>'Implementación',
        'subtitle'=>'Norma 035', 'img'=>'img/header/norma/icono-norma.png',
        'route'=>'bandeja'])

        <div class="article border ">
            <h4 style="text-align: center;"> Aún no cuenta con implementación de la norma 035 <br> ¿Desea comenzar la norma para {{ Session::get('empresa')['razon_social'] }} ? </h4>
            <div style="text-align: center;margin-top: 5%">
                <a href="{{ route('norma.crear.tablas') }}">
                    <button type="button" class="button-style" style="width: 250px;"> Si, implementar Norma 035 </button>
                </a>
                <a href="{{ route('bandeja') }}">
                    <button type="button" class="button-style" style="width: 250px;"> Cancelar </button>
                </a>
            </div>
        </div>
        @include('includes.footer')