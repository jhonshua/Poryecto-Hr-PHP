<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="article border">
	<div class="row">
		<div class="col-md-12  mt-4">
	        
	        <a href="{{route($regresarUrl)}}" class="button-style">REGRESAR</a>
	        <br>
	        <br>

	        <iframe src="{{ asset($archivo) }}" frameborder="0" width="100%" height="750px"></iframe>
	    </div>
	</div>
</div>
