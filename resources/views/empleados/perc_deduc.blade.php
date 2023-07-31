<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="container">
@include('includes.header',['title'=>'Percepciones y deducciones',
        'subtitle'=>'Empleados', 'img'=>'/img/percepciones-empleados.png',
        'route'=>'empleados.empleados'])

    <div class="row mt-3">
    	<div class="col-md-3 text-right mt-4">
    		<h2 class="font-weight-bold">Deducciones</h2>
    	</div>
    	<div class="col-md-9 mt-4">
    		<button type="button" class="btn button-style" data-toggle="modal" data-target="#deduccionesModal"><img src="{{ asset('/img/icono-crear.png') }}" width="20px"> Crear nuevo</button> 
    	</div>
    </div>

   
    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif

    <div class="article border" style="margin-top: 20px;">
    	<div class="row mt-2">
    		<div class="col-md-12">
		        <table class="table w-100 deducciones">
		            <thead>
		                <tr>
		                    <th width="22%">Concepto</th>
		                    <th>Importe</th>
		                    <th>Pagos a realizar</th>
		                    <th>Importe a Descontar</th>
		                    <th>Inicio de Descuento</th>
		                    <th>Pagos realizados</th>
		                    <th>Saldo</th>
		                    <th width="120">Estatus</th>
		                    <th width="150">Acciones</th>
		                </tr>
		            </thead>

		            @if ($deducciones->count() <= 0)
		                <tbody>
		                    <tr>
		                        <td colspan="8"> Sin registros</td>
		                    </tr>
		                </tbody>
		            @else
		                <tbody>
		                    @foreach ($deducciones as $deduc) 
		                        <tr id="d{{$deduc->id}}">
		                            <td class="text-left">{{$conceptosDeducciones[$deduc->id_concepto]->nombre_concepto}}</td>
		                            <td>${{number_format($deduc->importe_total, 2, '.', ',')}}</td>
		                            <td class="text-center">{{$deduc->numero_pagos_a_realizar}}</td>
		                            <td>${{number_format($deduc->cantidad_a_descontar, 2, '.', ',')}}</td>
		                            <td>{{formatoAFecha($deduc->fecha_inicio, true)}}</td>
		                            <td class="text-center">{{$deduc->numero_pagos_realizados}}</td>
		                            <td>${{number_format($deduc->saldo, 2, '.', ',')}}</td>
		                            <td class="text-center">
		                                @if($deduc->estatus == App\Models\EmpleadoDeducciones::ACTIVO)
		                                    <span class="text-success">Activo</span>
		                                @elseif($deduc->estatus == App\Models\EmpleadoDeducciones::INACTIVO)
		                                    <span class="text-secondary">En pausa</span>
		                                @elseif($deduc->estatus == App\Models\EmpleadoDeducciones::TERMINADO)
		                                    <span  style="color:#28a745;">Completado</span>
		                                @endif
		                            </td>
		                            <td class="d-flex">
		                                @if ($deduc->numero_pagos_realizados == 0)
		                                
		                                    @if ($deduc->estatus == 1)
		                                        <form action="{{ route('empleados.estatusdeduccion') }}" method="post">
		                                            @csrf
		                                            <button type="submit" class="estatus btn " alt="Pausar deducción" title="Pausar deducción">
		                                                <img src="{{asset('/img/pausa_deducccion.png')}}" alt="Pausar deducción" style="width: 20px">

		                                            </button>
		                                            <input type="hidden" name="id_empleado" value="{{$deduc->id_empleado}}">
		                                            <input type="hidden" name="id" value="{{$deduc->id}}">
		                                            <input type="hidden" name="tipo" value="d">
		                                            <input type="hidden" name="estatus" value="{{App\Models\EmpleadoDeducciones::INACTIVO}}">
		                                        </form>
		                                    @else
		                                        <form action="{{ route('empleados.estatusdeduccion') }}" method="post">
		                                            @csrf

		                                            <button type="submit" class="estatus  btn btn-sm mr-2" alt="Activar deducción" title="Activar deducción">
		                                                <img src="{{asset('/img/play-deducciones.png')}}" alt="Activar deducción" style="width: 20px">
		                                            </button>
		                                            <input type="hidden" name="id_empleado" value="{{$deduc->id_empleado}}">
		                                            <input type="hidden" name="id" value="{{$deduc->id}}">
		                                            <input type="hidden" name="tipo" value="d">
		                                            <input type="hidden" name="estatus" value="{{App\Models\EmpleadoDeducciones::ACTIVO}}">
		                                        </form>
		                                    @endif

		                                    <a class="borrar btn" data-id="{{$deduc->id}}" data-estatus="{{App\Models\EmpleadoDeducciones::ELIMINADO}}" data-tipo="d">
		                                    	<img src="{{asset('/img/eliminar.png')}}" alt="Borrar deducción" style="width: 20px"> 
		                                    </a>
		                                @endif
		                            </td>
		                        </tr>
		                    @endforeach
		                </tbody>
		            @endif
		            
		        </table>
    		</div>
    	</div>
    </div>


    <div class="row" >
    	<div class="col-md-3 text-right mt-4">
    		<h2 class="font-weight-bold">Percepciones</h2>
    	</div>
    	<div class="col-md-9 mt-4">
    		<button type="button" class="btn button-style" data-toggle="modal" data-target="#percepcionesModal"><img src="{{ asset('/img/icono-crear.png') }}" width="20px"> Crear nuevo</button> 
    	</div>
    </div>



    <div class="article border" style="margin-top: 20px;">
    	<div class="col m12 s12">
	        <table class="table w-100 percepciones">
	            <thead>
	                <tr>
	                    <th width="22%">Concepto</th>
	                    <th>Importe</th>
	                    <th>Aportaciones a realizar</th>
	                    <th>Importe a aportar</th>
	                    <th>Inicio de la Aportacion</th>
	                    <th>Aportaciones realizadas</th>
	                    <th>Saldo</th>
	                    <th width="120">Estatus</th>
	                    <th width="150">Acciones</th>
	                </tr>
	            </thead>

	            @if ($percepciones->count() <= 0)
	                <tbody>
	                    <tr>
	                        <td colspan="8"> Sin registros</td>
	                    </tr>
	                </tbody>
	            @else
	                <tbody>
	                    @foreach ($percepciones as $percep) 
	                        <tr id="p{{$percep->id}}">
	                            <td class="text-left">{{$conceptosPercepciones[$percep->id_concepto]->nombre_concepto}}</td>
	                            <td>${{number_format($percep->importe_total, 2, '.', ',')}}</td>
	                            <td class="text-center">{{$percep->numero_aportaciones_a_realizar}}</td>
	                            <td>${{number_format($percep->cantidad_a_aportar, 2, '.', ',')}}</td>
	                            <td>{{formatoAFecha($percep->fecha_inicio, true)}}</td>
	                            <td class="text-center">{{$percep->numero_aportaciones_realizadas}}</td>
	                            <td>${{number_format($percep->saldo, 2, '.', ',')}}</td>
	                            <td class="text-center">
	                                @if($percep->estatus == App\Models\EmpleadoPercepciones::ACTIVO)
	                                    <span class="text-success">Activo</span>
	                                @elseif($percep->estatus == App\Models\EmpleadoPercepciones::INACTIVO)
	                                    <span class="text-secondary">En pausa</span>
	                                @elseif($percep->estatus == App\Models\EmpleadoPercepciones::TERMINADO)
	                                    <span class="text-info">Completado</span>
	                                @endif
	                            </td>
	                            <td class="d-flex">
	                                @if ($percep->numero_aportaciones_realizadas == 0)
	                                
	                                    @if ($percep->estatus == 1)

											<form action="{{ route('empleados.estatusdeduccion') }}" method="post">
												@csrf
												<button type="submit" class="estatus btn" alt="Pausar percepción" id="percepcion_sub" title="Pausar percepción">
													<img src="{{asset('/img/pausa_deducccion.png')}}"   alt="Pausar deducción" style="width: 20px">
												</button>

												<input type="hidden" name="id_empleado" value="{{$percep->id_empleado}}">
												<input type="hidden" name="id" value="{{$percep->id}}">
												<input type="hidden" name="tipo" value="p">
												<input type="hidden" name="estatus" value="{{App\Models\EmpleadoPercepciones::INACTIVO}}">
											</form>
	                                    @else

											<form action="{{ route('empleados.estatusdeduccion') }}" method="post">
												@csrf
												<button type="submit" class="estatus btn" alt="Activar percepción" id="percepcion_sub" title="Activar percepción">
													<img src="{{asset('/img/play-deducciones.png')}}" alt="Activar deducción" style="width: 20px">
												</button>

												<input type="hidden" name="id_empleado" value="{{$percep->id_empleado}}">
												<input type="hidden" name="id" value="{{$percep->id}}">
												<input type="hidden" name="tipo" value="p">
												<input type="hidden" name="estatus" value="{{App\Models\EmpleadoPercepciones::ACTIVO}}">
											</form>

	                                    @endif
		                                    <a class="borrar btn"data-id="{{$percep->id}}" data-estatus="{{App\Models\EmpleadoPercepciones::ELIMINADO}}" data-tipo="p">
		                                    	<img src="{{asset('/img/eliminar.png')}}" alt="Borrar percepción" style="width: 20px"> 
		                                    </a>

	                                    
	                                @endif
	                            </td>
	                        </tr>
	                    @endforeach
	                </tbody>
	            @endif
	            
	        </table>
    	</div>


    </div>


</div>


	<form action="{{ route('empleados.estatusdeduccion') }}" method="post" id="submit_deduccion">
	    @csrf
	    <input type="hidden" name="id_empleado" id="id_empleado_per" value="">
	    <input type="hidden" name="id" id="id_per" value="">
	    <input type="hidden" name="tipo" value="p">
	    <input type="hidden" name="estatus" id="estatus_per" value="">
	</form>


@include('empleados.deducciones-modal')
@include('empleados.percepciones-modal')
@include('includes.footer')
<style>
    label{
        font-weight: bold;
        margin-top: 15px;
    }

</style>

<script type="text/javascript">

$(function(){

    $('.deducciones .btn.estatus, .percepciones .btn.estatus').click(function(){
        $(this).text('Espere...');
	});

    $('.deducciones .btn.borrar, .percepciones .btn.borrar').click(function(){
   
        var id = $(this).data('id');
        var estatus = $(this).data('estatus');
        var tipo = $(this).data('tipo');
        btn = $(this);


        swal({
            title: "¿Esta seguro de eliminar este registro?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });

                cambiarEstatus(id, estatus, tipo) 

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });

    function cambiarEstatus(id, estatus, tipo){
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            'estatus': estatus,
            'tipo': tipo,
            '_token': CSRF_TOKEN
        }

        var url = "{{ route('empleados.estatusdeduccion') }}";
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    tabla = (tipo == 'd') ? '.deducciones' : '.percepciones';
                    row = tabla +' tr#' + tipo + String(id);
                    
                    $(row).fadeOut('slow', function(){
                        $(row).remove();
                       
						swal({
						  text: "El registro se eliminó correctamente",
						  icon: "success",
						  button: "Ok!",
						});

                    });
                } else {

						swal({
						  text: "Ocurrió un error. Intente nuevamente",
						  icon: "warning",
						  button: "Ok!",
						});


                }
            },
            error: function() {
						swal({
						  text: "Ocurrió un error. Intente nuevamente",
						  icon: "warning",
						  button: "Ok!",
						});
            }
        });
    }

});


	$("#percepcion_sub").click(function(){
		var idempleado = $(this).data('idempleado');
		var id = $(this).data('id');
		var estatus = $(this).data('estatus');

		if(estatus == 1){ est = 0; }else{ est = 1; }

		document.getElementById("id_empleado_per").value = idempleado;
		document.getElementById("id_per").value = id;
		document.getElementById("estatus_per").value = est;
		document.getElementById("submit_deduccion").submit();
	});

</script>