<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<div class="container">
	@include('includes.header',['title'=>'IMSS', 'subtitle'=>'Control de incapacidades', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])


	<div class="row">
        <div class="col-md-2"></div>
		<div class="col-md-6 d-flex mt-4">
	        <span class="text-nowrap pt-2 mr-2">Busqueda por:</span>
	        <input type="number" name="" id="id" placeholder="ID empleado" class="form-control mr-3">
	        {{-- <input type="text" name="" id="nombre" placeholder="Nombre Empleado" class="form-control mr-3"> --}}
	        <select class="form-control mr-3 select-clase" id="id_departamento">
	            <option value="">Departamento/TODOS</option>
	            @foreach ($departamentos as $depto)
	                <option value="{{$depto->id}}">{{$depto->nombre}}</option>
	            @endforeach
	        </select>
		</div>	
		<div class="col-md-4 d-flex mt-4">
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


	<div class="article border mt-4">

        <table class="table empleados-head" id="incapacidades">
            <thead>
                <tr>
                    <th width="60px">ID</th>
                    <th width="80px"># Emp.</th>
                    <th width="28%">Nombre</th>                       
                    <th width="180px">Departamento</th>                       
                    <th width="150px">Fecha alta</th>
                    <th width="110px">Incapacidades</th>
                    <th width="170px">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($empleados as $empleado)
                    <tr id="{{$empleado->id}}" data-id="{{$empleado->id}}" data-id_departamento="{{$empleado->id_departamento}}" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}">
                        <td width="60px">{{$empleado->id}}</td>
                        <td width="80px">{{$empleado->numero_empleado}}</td>
                        <td width="30%">{{$empleado->nombre_completo}}</td>
                        <td width="">{{$empleado->departamento->nombre}}</td>
                        <td width="140px">{{formatoAFecha($empleado->fecha_alta)}}</td>
                        <td width="120px" class="text-center">{{$empleado->incapacidadesActivas->count()}}</td>
                        <td width="170px" class="position-relative">
                            <button class="btn btn-warning id_emp" data-toggle="modal" data-target="#incapacidadModal" data-id="" data-id_empleado="{{$empleado->id}}">
                                <i class="fas fa-plus tooltip_" data-toggle="tooltip" title="Crear incapacidad"></i>
                            </button>
                            @if ($empleado->incapacidadesActivas->count() > 0)
                                <a href="{{route('incapacidades.detalle',$empleado->id)}}" class="btn btn-warning mr-2 font-weight-bold ">VER</a>
                            @endif
                        </td>

                   	</tr>
                @endforeach
            </tbody>

        </table>

	</div>
</div>

@include('imss.incapacidades-modal')
@include('includes.footer')

<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">

        let dataSrc = [];
        let table = $('#incapacidades').DataTable({
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
	        buscar(id, 'id');
	    });

	    $('#id_departamento').change(function(){
	        id_departamento = $(this).val().toUpperCase();
	        buscar(id_departamento, 'id_departamento');
	    });

	    $('#nombre').change(function(){
	        nombre = $(this).val().toUpperCase();
	        buscar(nombre, 'nombre');
	    });

	    function buscar(valorABuscar, campo){

	        if(valorABuscar.trim() == ''){
	            $("#incapacidades tr").show();
	        } else{
	            $("#incapacidades tr").hide();
	            $("#incapacidades tr").each(function(){
	                valor = $(this).data(campo)+'';
	                if(valor.indexOf(valorABuscar ) > -1){
	                    $(this).show();
	                }
	            });
	        }
	    }


    $(function() {
        $('.select-clase').select2();
    });

    

</script>