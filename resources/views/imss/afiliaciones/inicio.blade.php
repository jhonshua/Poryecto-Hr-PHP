<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">

	@include('includes.header',['title'=>'Movimientos afiliatorios - Altas', 'subtitle'=>'IMSS', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])

	<div class="row">
		<div class="col-md-12 d-flex mt-4">
	        <a href="{{ route('afiliaciones.bajas') }}" class="btn button-style ml-3">Bajas</a>
	     
	        <a href="{{ route('afiliaciones.modificaciones') }}" class="btn button-style mx-3">Modificaciones</a> |

	        <a href="#" class="btn button-style text-nowrap ml-3 disco">Generar disco (Altas)</a>

	        <span class="text-nowrap pt-2 ml-4">Búsqueda por:</span>
	        <input type="number" name="" id="id" placeholder="ID empleado" class="form-control mr-3  ml-2">
	        <div class="dataTables_filter col-md-3" id="div_buscar"></div>	

		</div>
	</div>

    @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif


	<div class="article border mt-4">
	    <div class="col-md-12 mt-3">
	        <table class="table mb-0 afiliaciones empleados">
	            <thead >
	                <tr>
	                    <th width="60px">ID</th>
	                    <th width="80px"># Emp.</th>
	                    <th width="31%">Nombre</th>                      
	                    <th width="150px">Fecha alta</th>
	                    <th width="150px">Folio</th>
	                    <th width="170px">Acciones</th>
	                </tr>
	            </thead>
	                <tbody>
	                    @foreach ($empleados as $empleado)
	                        <tr 
	                            id="{{$empleado->id}}"
	                            data-id="{{$empleado->id}}"
	                            data-nombre="{{$empleado->nombre_completo}}"
	                        >

	                            <td width="60px">{{$empleado->id}}</td>
	                            <td width="80px">{{$empleado->numero_empleado}}</td>
	                            <td width="30%">{{$empleado->nombre_completo}}</td>
	                            <td width="140px">{{formatoAFecha($empleado->fecha_alta)}}</td>
	                            <td width="140px">
	                                <input type="text" name="folio_alta" id="folio_alta" class="form-control folio_alta" value="{{$empleado->folio_alta}}" {{($empleado->estatus_folio_alta == 2) ? 'disabled' : ''}}>
	                            </td>

	                            <td width="170px" class="position-relative">
	                                
	                                @if ($empleado->estatus_folio_alta != 2)
	                                    <button class="btn btn-warning btn-sm guardar" data-id_empleado="{{$empleado->id}}">Guardar</button>
	                                    <button class="btn btn-warning btn-sm cerrar" data-id_empleado="{{$empleado->id}}">Cerrar folio</button>
	                                @else
	                                    Folio Cerrado
	                                @endif
	                                
	                            </td>
	                        </tr>
	                    @endforeach
	                </tbody>
	        </table>
	    </div>
	</div>

</div>
@include('includes.footer')
<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">

    let dataSrc = [];
    let table = $('.afiliaciones').DataTable({
        scrollY:'65vh',
        scrollCollapse: true,
        "language": {
            search: '',
            searchPlaceholder: 'Buscar registros por nombre',
            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        initComplete: function() {
             
            let api = this.api();
            
            api.cells('tr', [2]).every(function(){

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



    $('#id').change(function(){
        id = $(this).val();
        $('#nombre').val('');
        buscar(id, 'id');
    });


    $('#nombre').change(function(){
        nombre = $(this).val().toUpperCase();
        $('#id').val('');
        buscar(nombre, 'nombre');
    });

    function buscar(valorABuscar, campo){
        if(valorABuscar.trim() == ''){
            $(".empleados tr").show();
        } else{
            $(".empleados tr").hide();
            $(".empleados tr").each(function(){
                valor = $(this).data(campo)+'';
                if(valor.indexOf(valorABuscar ) > -1){
                    $(this).show();
                }
            });
        }
    }

    $('.btn.guardar').click(function(){
        btn = $(this);
        btn.text('Espere...').attr('disabled', true);
        id = btn.data('id_empleado');
        folio_alta = btn.parents('tr').find('#folio_alta').val();
        
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            'folio_alta': folio_alta,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('afiliaciones.guardarFolio')}}";

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
					swal({
					  text: "Se guardo correctamente el folio",
					  icon: "success",
					  button: "Ok",
					});
                } else {
					swal({
					  text: "Ocurrió un error. Intente nuevamente.",
					  icon: "warning",
					  button: "Ok",
					});
                }
            }
        })
        .always(function(){
            btn.text('Guardar').attr('disabled', false);
        });

    });

     // Cerrar Folio
    $('.btn.cerrar').click(function(){
        btn = $(this);
        btn.text('Espere...').attr('disabled', true);
        id = btn.data('id_empleado');
        
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        data = {
            'id' : id,
            '_token': CSRF_TOKEN
        }

        var url = "{{route('afiliaciones.cierreFolio')}}";

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
					swal({
					  text: "Se cerró correctamente el folio",
					  icon: "success",
					  button: "Ok",
					});
                    btn.text('Folio Cerrado');
                    btn.prev('.guardar').attr('disabled', true);
                    btn.parents('tr').find('#folio_alta').attr('disabled', true);
                } else {
                    btn.text('Cerrar folio').attr('disabled', false);
					swal({
					  text: "Ocurrió un error. Intente nuevamente.",
					  icon: "warning",
					  button: "Ok",
					});
                }
            }
        })
        .always(function(){
            
        });

    });

    // Generar disco
    $('.btn.disco').click(function(){

		swal({
		  	title: "El archivo solo incluirá a todos los empleados que no tienen un folio asignado.",
		 	text: "¿Desea generar el archivo?",
		  	icon: "warning",
		  	buttons: true,
		  	dangerMode: true,
		})
		.then((willDelete) => {
		  	if (willDelete) {
		  		var folio = Math.floor(Math.random()*(3000-1000+1)+1000);
				var x = document.getElementsByClassName('folio_alta');
				for(i = 0; i < x.length; i++) {
				  x[i].value = folio;
				}
			    swal("A los empleados se les asignará el folio: "+folio+ ". Recuerde regresar a colocar el Folio Oficial Otorgado por el IMSS.", {
			      icon: "success",
			    });
			    window.location.href = "{{route('afiliaciones.generarDisco')}}?f=" + folio;
		  	} else {
		    	swal("La acción fue cancelada!");
		  	}
		});


    });

</script>