<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')


<div class="container">
	@include('includes.header',['title'=>'Días Feriados', 'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'herramientas.horarios'])

	<div class="row">
		<div class="col-md-12"> 
	        <button type="button" class="btn button-style mb-3 nuevo" data-toggle="modal" data-target="#feriadoModal" data-id="">Crear nuevo</button>
	        <button type="button" class="btn button-style mb-3 importar" data-toggle="modal" data-target="#importarModal" data-id=""><i class="fas fa-file-import"></i> Importar fechas</button>
	        <button type="button" class="btn button-style mb-3 clonar d-none"><i class="fas fa-copy"></i> Clonar a todos los horarios</button>

	        <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
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

	<div class="article border mt-2">
        <table class="table dias" id="festivos">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" name="" id="all">
                    </th>
                    <th width="60%">Nombre</th>
                    <th>Fecha</th>
                    <th width="150px">Opciones</th>

                </tr>
            </thead>
                <tbody>
                    @if (count($dias) > 0)
                        <form action="{{ route('herramientas.clonar') }}" method="post" id="clonarform" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="horario_base" value="{{$id_horario}}">
                            @foreach ($dias as $dia)
                                <tr  id="{{$dia->id}}">
                                    <td width="40">
                                        <input type="checkbox" name="dias[]" value="{{$dia->id}}">
                                    </td>
                                    <td width="60%">{{$dia->motivo}}</td>
                                    <td>
                                        {{Carbon\Carbon::parse($dia->fecha_festiva)->format('d/M/Y')}}
                                    </td>
                                    <td width="130px">
                                        <button class="editar btn btn-warning btn-sm mr-2 p-1" alt="Editar" title="Editar" data-toggle="modal" data-target="#feriadoModal"
                                        data-id="{{$dia->id}}"
                                        data-motivo="{{$dia->motivo}}"
                                        data-fecha="{{$dia->fecha_festiva}}"
                                        ><i class="fas fa-edit" style="font-size:16px;"></i></button>

                                        <button data-id="{{$dia->id}}" class="borrar btn btn-danger btn-sm mr-2 p-1" alt="Eliminar" title="Eliminar"><i style="font-size:18px" class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </form>
                    @else
                        <tr>
                            <td colspan="3">No hay fechas registradas</td>
                        </tr>
                    @endif
                </tbody>
        </table>
	</div>

</div>
@include('herramientas.horarios.festivos-modal')
@include('herramientas.horarios.importar-modal')

<script src="{{asset('js/typeahead.js')}}"></script>
<script type="text/javascript">
        let dataSrc = [];
        let table = $('#festivos').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [1]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });

    $(".btn.editar").click(function(e){
        e.preventDefault();
    });


    $(".btn.borrar").click(function(){
        var id = $(this).data('id');

		swal({
		 	title: "Aviso",
		 	text: "¿Esta seguro de eliminar este registro?",
		 	icon: "warning",
		 	buttons: true,
		 	dangerMode: true,
		})
		.then((willDelete) => {
		  if (willDelete) {
		    swal("Espere un momento, la acción esta siendo procesada!", {
		      icon: "success",
		    });
		     borrarDiaFeriado(id); 
		  } else {
		    swal("La acción fue cancelada");
		  }
		});

    });


    function borrarDiaFeriado(id) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('herramientas.borrarferiado', '*ID*')}}";
        url = url.replace('*ID*', id);
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    $(".horarios tr#" + id).remove();
					swal({
					  text: "El registro se eliminó correctamente.",
					  icon: "success",
					  button: "Ok",
					});

                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
					swal({
					  text: "Ocurrió un error. Intente nuevamente.",
					  icon: "warning",
					  button: "Ok",
					});
                }
            }
        });
    }

    $('#all').click(function(){
        $('.dias tr:visible input[type=checkbox]').prop('checked', $(this).prop('checked'));
        if($(this).prop('checked'))
            $('.clonar').removeClass('d-none');
        else
            $('.clonar').addClass('d-none');

    });

    $('.dias input[type=checkbox]').click(function(){
        if ($(".dias input:checkbox:checked").length > 0)
            $('.clonar').removeClass('d-none');
        else
            $('.clonar').addClass('d-none');
    });


    $('.clonar').click(function(){
		swal({
			title: "Aviso",
			text: "¿Esta seguro de clonar estos días feriados en TODOS los horarios disponibles? (Los días feriados o registros ya ingresados en dichos horarios, SE BORRARÄN Y SE SUSTITUIRÁN por estos seleccionados)",
		  	icon: "warning",
		 	 buttons: true,
		  	dangerMode: true,
		})
		.then((willDelete) => {
		  	if (willDelete) {
		  		
		    	swal("La información esta siendo procesada", {
		      		icon: "success",
		    	});
		    	clonar_id();
		  	} else {
		    	swal("La acción fue cancelada");
		  	}
		});
    });


    function clonar_id() 
    {
        $('#clonarform').submit(); 
    }
</script>