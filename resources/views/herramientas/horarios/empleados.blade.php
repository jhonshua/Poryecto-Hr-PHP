<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<style>
    .wrapper-table{
        height: 630px;
        margin-bottom: 20px;
        overflow-y: scroll;
        width: 100%;
    }
</style>

<div class="container">
	@include('includes.header',['title'=>'Horario de Seguridad', 'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'herramientas.horarios'])

	<div class="row">
	    <div class="col-md-12 form-inline my-3">
	        <select name="deptos" id="deptos" class="form-control mr-3 select-clase">
	            <option value="">Departamentos</option>
	            @foreach ($deptosArr as $depto_id => $depto)
	                <option value="{{$depto_id}}">{{$depto}}</option>
	            @endforeach
	        </select>
	        <button class="btn button-style d-none asignar ml-2" type="button">Asignar horario</button>
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

	<div class="row">
		<div class="col-md-6">
			<div class="article border mt-2">
		        <table class="table table-striped table-hover mb-0">
		            <thead >
		                <tr>
		                    <th>
		                        <input type="checkbox" name="" id="all">
		                    </th>
		                    <th>Nombre</th>
		                    <th >Depto</th>

		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <form action="{{ route('herramientas.asignarHorario') }}" method="post" id="empleadosSinHorario_form">
		                @csrf
		                <table class="table table-striped table-hover empleadosSinHorario">
		                    <tbody>
		                        @if ($empleadosSinHorario)
		                            @foreach ($empleadosSinHorario as $empleado)
		                                <tr class="{{$empleado->id_departamento}}">
		                                    <td>
		                                        <input type="checkbox" name="empleados[]" value="{{$empleado->id}}" id="{{$empleado->id}}">
		                                    </td>
		                                    <td>
		                                        <label for="{{$empleado->id}}">{{$empleado->nombre . " " . $empleado->apaterno}}</label>
		                                    </td>
		                                    <td>{{$deptosArr[$empleado->id_departamento]}}</td>
		                                </tr>
		                            @endforeach
		                        @else
		                            <tr class="empty"><td colspan="4">NO EXISTEN EMPLEADOS SIN HORARIOS</td></tr>
		                        @endif
		                    </tbody>
		                </table>
		                <input type="hidden" name="id_horario" value="{{$horario->id}}">
		            </form>
		        </div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="article border mt-2">
		        <table class="table  mb-0">
		            <thead >
		                <tr>
		                    <th>Nombre</th>
		                    <th>Departamento</th>
		                    <th width="110px">Quitar</th>

		                </tr>
		            </thead>
		        </table>
		        <div class="wrapper-table">
		            <table class="table  empleados">
		                <tbody>
		                    @if ($empleados)
		                        @foreach ($empleados as $empleado)
		                            <tr>
		                                <td>{{$empleado->nombre . " " . $empleado->apaterno}}</td>
		                                <td>{{$deptosArr[$empleado->id_departamento]}}</td>
		                                <td width="110px">
		                                    <form action="{{ route('herramientas.desasignarHorario') }}" method="post" enctype="multipart/form-data">
		                                        @csrf
		                                        <input type="hidden" name="id_horario" value="{{$horario->id}}">
		                                        <input type="hidden" name="id_empleado" value="{{$empleado->id}}">
		                                        <button class="borrar btn btn-danger btn-sm mr-2" type="submit"><i class="fas fa-chevron-circle-left"></i> Quitar</button>
		                                    </form>
		                                </td>
		                            </tr>
		                        @endforeach
		                    @else
		                        <tr class="empty"><td colspan="4">NO EXISTEN EMPLEADOS ASIGNADOS A ESTE HORARIO</td></tr>
		                    @endif
		                </tbody>
		            </table>
		        </div>
			</div>
		</div>
	</div>



</div>


<script type="text/javascript">
    $(function() {
        $('.select-clase').select2();
    });

	$(function(){

	    // filtro
	    $('#deptos').change(function(){
	        if($('#deptos').val() != ''){
	            $('.empleadosSinHorario tr').fadeOut('fast', function(){
	                $('.empleadosSinHorario tr.' + $('#deptos').val()).fadeIn('fast');
	            });
	        } else {
	            $('.empleadosSinHorario tr').fadeIn('fast');
	        }
	    });

	    //select all
	    $('#all').click(function(){
	        $('.empleadosSinHorario tr:visible input[type=checkbox]').prop('checked', $(this).prop('checked'));
	        if($(this).prop('checked'))
	            $('.asignar').removeClass('d-none');
	        else
	            $('.asignar').addClass('d-none');

	    });

	    $('.empleadosSinHorario input[type=checkbox]').click(function(){
	        if ($(".empleadosSinHorario input:checkbox:checked").length > 0)
	            $('.asignar').removeClass('d-none');
	        else
	            $('.asignar').addClass('d-none');
	    });

	    //Asignar btn
	    $('.asignar').click(function(){
	        $(this).text('Espere...');
	        $(this).prop('disabled', true);
	        $('#empleadosSinHorario_form').submit();
	    })
	});

</script>