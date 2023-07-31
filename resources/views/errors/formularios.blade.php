@extends('layouts.empleado')
@section('tituloPagina', "Permiso denegado")

@section('content')
<div class="row">
	<div class="col-md-12 mt-5">
        <div class="jumbotron">
            <h1 class="display-4">Permiso denegado</h1>
            <p class="lead">No tienes permiso para ver este módulo.</p>
            <hr class="my-4">
            <a class="btn btn-warning btn-lg" href="{{ route('empleado.inicio') }}" role="button">Ir al inicio</a>
          </div>
	</div>
</div>
@endsection