<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
	function ischecked($nameBtn,$urlname)
	{
	    if ($nameBtn==1 && $urlname==null) {
	    	return "checked";
	    }

	    return ($nameBtn == $urlname)?"checked":"";
	}
@endphp
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<div class="container">

    @include('includes.header',['title'=>'Empresas del usuario', 'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'sistema.usuarios.usuariosistema'])

	<div>

        <br>
        <br>
	</div>
	<div class="article border">
		@if (empty($sedes))
		@else
			<div class="row">
				<div class="col-md-12">
					<input type="checkbox" name="" id="todos_sede"> <label for="todos" class="font-weight-bold">Marcar/Desmarcar TODOS</label>
				</div>
			</div>
			<form method="post" id="submit_sedes" action="{{route('usuarios.actualizarsede')}}">
				<div class="row">
	                @csrf
	        		<input type="hidden" name="id_usuario" value="{{ $usuario }}">
	        		<input type="hidden" id="empresa" name="id_empresa" value="{{ $empresa }}">
	        		<input type="hidden" name="enterprise" value="{{ $enterprise }}">
					@foreach($sedes as $sed)
						<div class="col-md-6">
							<li>
								<input type="checkbox" class="mr-2 mb-2 dept_id" name="dept_id[]" value="{{ $sed->id }}" data-id="{{ $sed->id }}" data-status="{{ $sed->estatus }}" data-nombre="{{ $sed->nombre }}" {{ ischecked($sed->estatus,1) }}><label for="1">{{ $sed->nombre }}</label>
							</li>
						</div>
					@endforeach
				</div>
				<br>
				<br>
			</form>

				<div class="row">
					<div class="col-md-12 text-center">
						<input type="submit" class="center button-style" id="add_sedes" value="Guardar">
					</div>
				</div>

		@endif
	</div>
</div>
@include('includes.footer')
<script type="text/javascript">
    $('#todos_sede').on('click', function(){
        $('.dept_id').prop('checked', $(this).prop('checked'));
    });


    $("#add_sedes").click(function(){
        var empresa = document.getElementById("empresa").value;

        if(empresa== ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          });
          setTimeout(submitForm, 1500);
        }
    });

    function submitForm() {  document.getElementById("submit_sedes").submit() }


</script>
