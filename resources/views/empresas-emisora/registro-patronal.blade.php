<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<div class="container">
  
@include('includes.header',['title'=> 'Registro patronal para: '.$empresaEmisora ,
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'empresae.empresaemisora'])

    
    <a data-toggle="modal" data-target="#crearregistroModal" class="crear_registro_modal" data-id="{{ $idEmpresaEmisora }}" rel="Crear registro patronal" title="Crear registro patronal">
        <button type="button" class="button-style">
            <img src="/img/icono-crear.png" class="button-style-icon">Crear
        </button>
    </a>

   	<div class="col-md-12 m-0">
		<br>
		<br>
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


   		<div class="article border">
   	        @if ($regPatronal)
	            <table class="table w-100 text-center" id="registropatronal">
	                <thead >
	                    <tr>
                        <th class="w-auto">Num de registro patronal</th>
                        <th class="text-center w-auto">Opciones</th>

	                    </tr>
	                </thead>
	                <tbody>
	                	@foreach ($regPatronal as $registro)
		                    <tr id="{{$registro->id}}">
		                        <td>{{$registro->num_registro_patronal}}</td>
		                        <td>
		                        	<a  data-toggle="modal" data-target="#editregistroModal" class="edit-registro" data-id="{{ $registro->id }}" data-registro="{{ $registro->num_registro_patronal }}" data-porc="{{ $registro->porcentaje_prima }}" data-tipo="{{ $registro->tipo_clase }}" data-delegacion="{{ $registro->subdelegacion }}" data-doc="{{ $registro->tipo_documento }}" data-empresa="{{ $registro->id_empresa_emisora}}">
										<button class="editar btn  btn-sm mr-2 p-1" alt="Editar horario" title="Editar horario" data-toggle="modal" data-target="#patronalModal"> <img src="/img/icono-editar.png" class="button-style-icon"></button>
		                        	</a>


		                            <button data-id="{{$registro->id}}" class="borrar btn btn-sm mr-2" alt="Eliminar" title="Eliminar"> <img src="/img/eliminar.png" class="button-style-icon"></button>
		                        </td>
		                    </tr>
	                    @endforeach
	                </tbody>
	            </table>
        	@else
	            <h3 class="mt-5">
	                No hay registro
	            </h3>
        	@endif
    	</div>

        <form method="post" id="delete_form" action="{{ route('empresae.borrarregistro') }}" enctype="multipart/form-data">
        	<input type="hidden" name="id" id="id_registro">
        	<input type="hidden" name="empresa" value="{{ $idEmpresaEmisora }}">
        @csrf
    	</form>

</div>

@include('empresas-emisora.crear-registro-patronal-modal')
@include('empresas-emisora.editar-registro-patronal-modal')
@include('includes.footer')
<script type="text/javascript">
    $(document).ready(function() {
        $('#registropatronal').DataTable({
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            }
        });
    } );


    $(".btn.borrar").click(function(){
        var id = $(this).data('id');

        swal({
            title: "Estas seguro de eliminar el registro",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                document.getElementById("id_registro").value = id;
                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });

                document.getElementById("delete_form").submit();

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });

    $(".crear_registro_modal").click(function(){
        var id = $(this).data('id');
        
        document.getElementById("id_empresa_emisora").value = id;   
    });

    $(".edit-registro").click(function(){
        var id = $(this).data('id');
        var registro = $(this).data('registro');
        var porc = $(this).data('porc');
        var tipo = $(this).data('tipo');
        var delegacion = $(this).data('delegacion');
        var doc = $(this).data('doc');
        var empresa = $(this).data('empresa');
        
        document.getElementById("edit_id_reg").value = id;
        document.getElementById("num_registro_patronal_ed").value = registro;
        document.getElementById("porcentaje_prima_ed").value = porc;
        $('#tipo_clase_ed').val(tipo);
        document.getElementById("subdelegacion_ed").value = delegacion;
        document.getElementById("tipo_documento_ed").value = doc;
        document.getElementById("id_empresa_emisora_ed").value = empresa;
    });

</script>
