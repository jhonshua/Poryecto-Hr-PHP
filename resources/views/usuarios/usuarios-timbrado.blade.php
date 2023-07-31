<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>

@include('includes.navbar')
@include('usuarios.crear-timbrado-al-usuario')

<div class="container">
@include('includes.header',['title'=>'Usuarios timbrado', 
'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-timbrado.png',
 'route'=>'bandeja'])

    <div class="mb-4">
        <a data-toggle="modal" data-target="#timbradomodal" title="Crear usuario de timbrado">
            <button type="button" class="button-style">
                <img src="/img/icono-crear.png" class="button-style-icon">Crear
            </button>
        </a>
        <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-4" id="div_buscar"> </div>
        <br>
        <br>

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


		<div class="article border">
			<div class="col-md-12">
		        <table class="table w-100 text-center" id="usuariostimbrado">
		            <thead >
		                <tr>
		                    <th width="50px">ID</th>
		                    <th width="50%">Razón Social</th>
		                    <th class="text-center">RFC</th>
		                    <th class="text-center" width="50px">Opciones</th>
		                </tr>
		            </thead>

		            <tbody>
		                @foreach ($usuarios_timbrado as $usuario)
		                    <tr id="{{$usuario->id}}">
		                        <td width="50px">{{$usuario->id}}</td>
		                        <td width="50%">{{$usuario->razon_social}}</td>
		                        <td class="text-center">{{$usuario->rfc}}</td>
		                        <td class="text-center" width="50px">
		                            <a data-id="{{$usuario->id}}"   class="borrar" alt="Eliminar" title="Eliminar" ref="Eliminar usuario {{ $usuario->rfc }}"><img src="/img/eliminar.png" class="button-style-icon m-2"></a>

		                        </td>
		                    </tr>
		                @endforeach
		            </tbody>
		        </table>
			</div>
		</div>
	</div>
</div>

	<form method="post" id="timbrado_delete_form" action="{{ route('usuarios.eliminartimbrado') }}" enctype="multipart/form-data">
    	@csrf
    	<input type="hidden" name="id" id="id_delete" value="">
    </form>
@include('includes.footer')

<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">

        let dataSrc = [];
        let table = $('#usuariostimbrado').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por razón social',
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

    $(".borrar").click(function(){
        $("#succes_alert").hide();
        id=$(this).data('id');

        swal({
            title: "Estas seguro de eliminar el registro",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
    	.then((willDelete) => {
      		if (willDelete) {
      			document.getElementById("id_delete").value = id;
        		swal("Espere un momento, la información esta siendo procesada", {
         			icon: "success",
         			buttons: false,
        		});

        		document.getElementById("timbrado_delete_form").submit();

      		} else {
        		swal("La accion fue cancelada!");
      		}
    	});

    });

</script>
