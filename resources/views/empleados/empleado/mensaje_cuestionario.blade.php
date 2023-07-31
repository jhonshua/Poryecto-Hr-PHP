@extends('layouts.simple')
@section('tituloPagina', "Página/Contenido no encontrado")

@section('content')
<div class="row">
	<div class="col-md-12 mt-5">
        <div class="jumbotron">
            <h1 class="display-4">Mensaje de respuesta.!</h1>
            <p class="lead">La encuesta fue enviada correctamente , Agradecemos tu tiempo para realizar esta solicitud ..!</p>
            <hr class="my-4">
            <a class="btn btn-warning btn-lg" href="{{route('inicio')}}" role="button">Ir al inicio</a>
          </div>
	</div>
</div>
@endsection