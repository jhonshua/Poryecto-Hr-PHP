<!DOCTYPE html>
<html lang="en">
@include('includes.head')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @stack('css')

    <!--Icono de la Barra-->
    <link rel="shortcut icon" href="/img/hr.ico">
    <title>HR-System - @yield('tituloPagina')</title>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VCGK3W5GQC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-VCGK3W5GQC');
    </script>
</head>
<body id="app">
    @include('includes.menuEmpleado')

    <div class="container" style="padding-top: 30px;">
        <div class="row contenedorHeader">
            <div class="col-md-12 d-inline-flex">
                <img src="/storage/repositorio/{{ Session::get('empresa')['id'] }}/logo.png" id="idEmpresaLogo" style="max-width: 5rem" alt="">
                <h1 id="EncabezadoTitulo" class="font-weight-bold">@yield('tituloPagina')</h1>
            </div>

            <div class="col-md-10 offset-md-1 mt-4 mb-4">
                @if (session('mensaje'))
                    <div class="alert alert-{{ session('tipo_alerta', 'secondary') }}" role="alert" id="alerta">
                        {{ session('mensaje') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <script> setTimeout(()=> $('#alerta').fadeOut(), 5000); </script>
                @endif
            </div>
        </div>

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
