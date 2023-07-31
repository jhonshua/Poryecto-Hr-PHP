<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<style type="text/css">
	.top-line-black {
	    width: 19%;}
</style>

<div class="container"> 
	@include('includes.header',['title'=>'Calculo de Finiquito/Liquidación',
		        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
		        'route'=>'procesos.finiquito'])

	<div class="row">
		<div class="col-md-12 text-center">
	        @if ($errores == 1)
	            <div class="alert alert-danger" role="alert">
	                <span>Tienes <strong>{{ $errores }}</strong> error</span>
	            </div>
	        @elseif($errores > 1)
	        	<div class="alert alert-danger" role="alert">
	                <span>Tienes <strong>{{ $errores }}</strong> errores</span>
	            </div>
	        @endif
		</div>
	</div>


	<div class="article border">
		<div class="row mt-5">
			<div class="col-12 text-center">
		        <h4>Empleado</h4>

		        
		        <table class="table">
		            <thead>
		                <tr>
		                    <th>Nombre</th>
		                    <th>Dato</th>
		                    <th>Validación</th>
		                </tr>
		            </thead>
		            <tbody>
		                @foreach ($empleado_validaciones as $e)
		                <tr>
		                    <td>
		                        {{$e['concepto']}} 
		                    </td>
		                    <td>
		                        {{$e['dato']}} 
		                    </td>
		                    <td>
		                        <ul> 
		                            @if($e['asignado'])
		                                <li class="text-success">{{$e['concepto']}} Correcto</li> 
		                            @else
		                                <li class="text-danger">{{$e['concepto']}} Incorrecto</li> 
		                            @endif

		                        </ul> 
		                    </td>
		                </tr>    
		                @endforeach                
		            </tbody>
		        </table>
		    </div>
		    <div class=" col-12 text-center">
		        <h4>Conceptos</h4>

		        <table class="table">
		            <thead>
		                <tr>
		                    <th>ID</th>
		                    <th>Nombre</th>
		                    <th>Validación</th>
		                </tr>
		            </thead>
		            <tbody>
		                @foreach ($validacion as $c)
		                <tr>
		                    <td scope="row">{{ $c['id'] }}</td>
		                    <td>{{ $c['concepto'] }} </td>
		                    <td>
		                        @if($c['asignado'])
		                            <li class="text-success">Concepto Asignado</li> 
		                        @else
		                            <li class="text-danger">Concepto no asignado</li> 
		                        @endif
		                    </td>
		                </tr>    
		                @endforeach                
		            </tbody>
		        </table>
		    </div>

		    <div class=" col-12 text-center">
		        <h4>Impuestos</h4>

		        <table class="table">
		            <thead>
		                <tr>
		                    <th>Nombre</th>
		                    <th>Validación</th>
		                </tr>
		            </thead>
		            <tbody>
		                @foreach ($validacion_impuestos as $c)
		                <tr>
		                    <td>{{ $c['concepto'] }} </td>
		                    <td>
		                        @if($c['asignado'])
		                            <li class="text-success">Impuesto Cargado</li> 
		                        @else
		                            <li class="text-danger">Impuesto  NO Cargado</li> 
		                        @endif
		                    </td>
		                </tr>    
		                @endforeach                
		            </tbody>
		        </table>
		    </div>

		</div>
	</div>

</div>
@include('includes.footer')
<script>
    $(function(){
        $("#timbrar-btn").on("click", function(e){
            e.preventDefault();
            $(this).attr(disabled,false);
            $("#idTimbrado").submit();
        });
    });

</script>