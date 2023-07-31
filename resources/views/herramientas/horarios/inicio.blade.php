<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')



<div class="container">
	@include('includes.header',['title'=>'Nuevo horario', 'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])

	<div class="row">
		<div class="col-md-12">
			<a href="{{ route('herramientas.nuevo') }}" class="btn button-style nuevo">Crear nuevo</a>
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

        @if(session()->has('danger'))
	        <div class="row">
	            <div class="alert alert-danger" style="width: 100%;" align="center">
	                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	                <strong>Notificación: </strong>
	                {{ session()->get('danger') }}
	            </div>
	        </div>
        @endif

	<div class="article border mt-2">
        <table class="table mb-0" id="horarios">
            <thead>
                <tr>
                    <th class="" width="39%">Nombre</th>
                    <th class="">Entrada</th>
                    <th class="">Salida</th>
                    <th class="">Estatus</th>
                    <th width="250px">Opciones</th>

                </tr>
            </thead>
                <tbody>
                    @foreach ($horarios as $horario)
                        <tr id="{{$horario->id}}">
                            <td class="" width="40%">{{$horario->alias}}</td>
                            <td class="">{{$horario->entrada}}</td>
                            <td class="">{{$horario->salida}}</td>
                            <td class="estatus font-weight-bold {{($horario->estatus) ? 'text-success' : 'text-secondary'}}">
                                {{($horario->estatus) ? 'ACTIVO' : 'INACTIVO'}}
                            </td>
                            <td width="250px">
                                <button class="editar btn btn-warning btn-sm mr-2 p-1" alt="Editar horario" title="Editar horario" data-toggle="modal" data-target="#horarioModal"
                                    data-id="{{$horario->id}}"
                                    data-alias="{{$horario->alias}}"
                                    data-entrada="{{$horario->entrada}}"
                                    data-salida="{{$horario->salida}}"
                                    data-tolerancia="{{$horario->tolerancia}}"
                                    data-retardos="{{$horario->retardos}}"
                                    data-comida="{{$horario->comida}}"
                                    data-entrada_comida="{{$horario->entrada_comida}}"
                                    data-salida_comida="{{$horario->salida_comida}}"
                                    data-lunes="{{$horario->lunes}}"
                                    data-martes="{{$horario->martes}}"
                                    data-miercoles="{{$horario->miercoles}}"
                                    data-jueves="{{$horario->jueves}}"
                                    data-viernes="{{$horario->viernes}}"
                                    data-sabado="{{$horario->sabado}}"
                                    data-domingo="{{$horario->domingo}}"
                                    data-indefinido="{{$horario->indefinido}}"
                                    data-estatus="{{$horario->estatus}}"
                                    data-sabado_entrada="{{$horario->sabado_entrada}}"
                                    data-sabado_salida="{{$horario->sabado_salida}}"
                                    data-domingo_entrada="{{$horario->domingo_entrada}}"
                                    data-domingo_salida="{{$horario->domingo_salida}}"
                                ><i class="fas fa-edit tooltip_" data-toggle="tooltip" title="Editar" style="font-size:16px;"></i></button>

                                
                                <a href="{{ route('herramientas.festivos', $horario->id) }}" class="dias btn btn-warning btn-sm mr-2 p-1 tooltip_" data-toggle="tooltip" title="Asignar Días Feriados"><i style="font-size:18px"class="far fa-calendar-alt"></i></a>


                                <a href="{{route('herramientas.empleados', $horario->id)}}" class="empleados btn btn-warning btn-sm mr-2 p-1 tooltip_" data-toggle="tooltip" title="Empleados"><i style="font-size:18px;"class="fas fa-user-friends"></i></a>

                                <button data-id="{{$horario->id}}" class="inactivar btn btn-secondary btn-sm mr-2 p-1 {{($horario->estatus) ? '' : 'd-none'}} tooltip_" data-toggle="tooltip" title="Inactivar horario" data-estatus="0"><i style="font-size:18px;"class="far fa-calendar-times"></i></button>

                                <button data-id="{{$horario->id}}" class="activar btn btn-success btn-sm mr-2 p-1 {{($horario->estatus) ? 'd-none' : ''}} tooltip_" data-toggle="tooltip" title="Activar horario" data-estatus="1"><i style="font-size:18px;"class="far fa-calendar-check"></i></button>

                                <button data-id="{{$horario->id}}" class="borrar btn btn-danger btn-sm mr-2 tooltip_" data-toggle="tooltip" title="Eliminar horario"><i style="font-size:14px;"class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
        </table>

	</div>
</div>

@include('herramientas.horarios.horario-editar-modal')
<script src="{{asset('js/typeahead.js')}}"></script>
<script type="text/javascript">


        let dataSrc = [];
        let table = $('#horarios').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [0]).every(function(){

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
		     borrarhorario(id); 
		  } else {
		    swal("La acción fue cancelada");
		  }
		});

    });


    function borrarhorario(id) {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('herramientas.borrar', '*ID*')}}";
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


    $(".btn.inactivar, .btn.activar").click(function(){
        var estatus = $(this).data('estatus');
        var id = $(this).data('id');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            'estatus' : estatus,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('herramientas.estatus', '*ID*')}}";
        url = url.replace('*ID*', id);
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    $(".horarios tr#" + id + ' td.estatus').removeClass('text-success text-secondary');
                    if(estatus){
                        $(".horarios tr#" + id + ' td.estatus').addClass('text-success');
                        $(".horarios tr#" + id + ' td.estatus').text('ACTIVO');
                        $(".btn.inactivar").removeClass('d-none');
                        $(".btn.activar").addClass('d-none');
                    } else {
                        $(".horarios tr#" + id + ' td.estatus').text('INACTIVO');
                        $(".horarios tr#" + id + ' td.estatus').addClass('text-secondary');
                        $(".btn.inactivar").addClass('d-none');
                        $(".btn.activar").removeClass('d-none');
                    }

					swal({
					  text: "El registro se edito correctamente.",
					  icon: "success",
					  button: "Ok",
					});

                } else {
					swal({
					  text: "Ocurrió un error. Intente nuevamente.",
					  icon: "success",
					  button: "Ok",
					});
                }
            }
        });
    });
</script>