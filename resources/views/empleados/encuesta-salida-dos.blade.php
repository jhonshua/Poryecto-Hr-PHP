
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="container">
	<div class="article border">
		<div class="row">

		    <div class="col-md-12">
		        <img src="{{asset('/img/hr_logo.png')}}" class="my-5" height="100">

		        <h3 class="mb-4 mt-5">La encuesta ha sido Enviada de Forma Satisfactoria. <br> Gracias!!!</h3>


		        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
		            @csrf
		            <button class="btn btn-warning my-2 px-5 font-weight-bold">CONTINUAR</button>
		        </form>

		    </div>
		</div>
	</div>
</div>

@include('includes.footer')