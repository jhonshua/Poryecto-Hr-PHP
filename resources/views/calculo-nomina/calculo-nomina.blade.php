<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">

@include('includes.header',['title'=>'Cálculo de nómina - '.$periodo->nombre_periodo,
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/calculo-nomina.png',
        'route'=>'bandeja'])
      
	<div>
	@if(session()->has('success'))
		<div class="row">
			<div class="alert alert-success" style="width: 100%;" align="center">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<strong>Notificación: </strong>
				{{ session()->get('success') }}
			</div>
		</div>
	@endif

		<div>

	        @if ($validacion)

	            @if ($total > 0)
	                <div class="text-center card mb-3 p-5">
	                    <div class="text-danger font-weight-bold mb-3"> La nomina de este periodo ya fue confirmada.</div>
	                    <label for="revocarNomina">
	                        ¿Deseas Revocar la confirmacion de la nomina?
	                        <input type="checkbox" name="" id="revocarNomina">
	                    </label>

	                    <form class="text-center mt-3" method="POST" action="{{ route('calculo.revocar') }}" id="revocar_form" style="display: none;">
	                        <textarea name='motivo' cols='50' rows='5' placeholder='Motivo de la revocacion' required class="form-control"></textarea>
	                        <button type="submit" class="btn btn-danger mt-3">REVOCAR</button>
	                        <input type="hidden" name="id_periodo" id="id_periodo" value="{{$periodo->id}}">
	                        <input type="hidden" name="ejercicio" id="ejercicio" value="{{$periodo->ejercicio}}">
	                    </form>
	                </div>
	            @endif

	            @php
	                if($total <= 0) {
	                    $next = 'calculo.calcular';
	                    $btn = 'GENERAR';
	                } else {
	                    $next = 'parametria.periodosnomina.imprimir';
	                    $btn = 'IMPRIMIR';
	                }
	            @endphp	      
				<div class="row justify-content-center mb-3">
					<div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="display: flex; align-items: center; border-radius: 7px;">
						<h5 class="card-title font-size-1-5em font-weight-bold pt-3 text-center">Selecciona los departamentos a considerar</h5>
					</div>
				</div>           

				<form method="post" id="periodo_imprimir_form" action="{{route($next)}}">
					@csrf
					<div class="row justify-content-center mb-3">
						<div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="border-radius: 7px;">
							<div class="my-3" style="text-align: left; margin: auto;">
								<div class="form-check">
									<input type="checkbox" name="" id="selCheckboxes">						
									<label class="form-check-label" for="selCheckboxes">
										<h5><strong>Marcar todos/desmarcar todos:</strong></h5>                                
									</label>
								</div>
								<div class="deptos mt-3">
									@foreach ($departamentos as $depto)
										<div class="depto d{{$depto->id}} form-check">
											<input type="checkbox" name="deptos[]" value="{{$depto->id}}" id="depto{{$depto->id}}" class="mb-3">
											<label for="depto{{$depto->id}}"><strong>{{ucfirst(Str::lower($depto->nombre))}}</strong></label><br>
										</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>

					<button class="button-style center">{{$btn}}</button>
					<input type="hidden" name="id_periodo" id="id_periodo" value="{{$periodo->id}}">
					<input type="hidden" name="numero_periodo" id="numero_periodo" value="{{$periodo->numero_periodo}}">
					<input type="hidden" name="ejercicio" id="ejercicio" value="{{$periodo->ejercicio}}">
				</form>
			            
	        @else

	            <h3 class="text-center text-danger font-weight-bold my-5">Debes cargar las tablas de Impuestos y Subsidios Correspondiente al Tipo de Nomina</h3>
	        @endif

	    </div>
	</div>
</div>
@include('includes.footer')
<script>
$(function(){

    $('#selCheckboxes').click(function(){
        $('.deptos input:checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#periodo_imprimir_form').submit(function(e){
        if($('.deptos input:checkbox:checked').length <= 0){
            e.preventDefault();

			swal({
			  text: "Debes seleccionar al menos un departamento para continuar",
			  icon: "warning",
			  button: "Ok",
			});

        }
        $('#periodo_imprimir_form .imprimir').attr('disabled', true).text('ESPERE...');
    });

    $('#revocarNomina').click(function(){
        $('#revocar_form').toggle();
    })

});
</script>
