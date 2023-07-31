<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="container">
@include('includes.header',['title'=>$tituloPag,
        'subtitle'=>'Empleados', 'img'=>'img/catalogo-empleado.png',
        'route'=>'empleados.empleados'])

     <iframe src="{{asset($archivo)}}" frameborder="0" width="100%" height="750px"></iframe>
</div>