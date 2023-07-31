<!DOCTYPE html>
<html lang="en">
@include('includes.head')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('public/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/menu.css') }}">

    <!-- ALERTIFY CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="{{ asset('css/parsley/parsley.css')}}"> <!--CDN validate input forms -->
    {{-- <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/> <!-- 'classic' theme -->
    {{-- <link rel="stylesheet" href="/public/css/style.css"> --}}
    @stack('css')

    <!--Icono de la Barra-->
    <link rel="shortcut icon" href="{{ asset('/img/hr.ico') }}">
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
    <div class="container" style="padding-top: 35px;">
        <div class="container" style="padding-top: 30px;">
            <div class="row contenedorHeader">
                <div class="col-md-12 d-inline-flex">
                    <img src="/storage/repositorio/235/logo.png" id="idEmpresaLogo" style="max-width: 5rem">
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
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    @stack('scripts')
</body>
</html>
