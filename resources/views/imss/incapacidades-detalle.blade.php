<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')


<div class="container">
	@include('includes.header',['title'=>'IMSS', 'subtitle'=>'Control de incapacidades', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'incapacidades.inicio'])

	<div class="col-md-10 d-flex mt-4">
        <button class="btn btn-warning mr-2" data-toggle="modal" data-target="#incapacidadModal" 
            data-id="" 
            data-id_empleado="{{$empleado->id}}"
        >
            Crear incapacidad
        </button>
        <h5>Incapacidades de: {{ $empleado->apaterno }} {{ $empleado->amaterno }} {{ $empleado->nombre }}</h5>
	</div>


	<div class="article border mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th width="60px">ID Incapacidad</th>
                    <th width="80px"># Días</th>
                    <th width="">Tipo Incapacidad</th>
                    <th width="180px">Fecha Inicial</th>                       
                    <th width="190px">Fecha Final</th>
                    <th width="200px">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($empleado->incapacidadesActivas as $incapacidad)
                    <tr id="{{$incapacidad->id}}">
                        <td width="120px">{{$incapacidad->id}}</td>
                        <td width="70px">{{$incapacidad->dias}}</td>
                        <td width="">{{$incapacidad->tipo_incapacidad}}</td>
                        <td width="180px">{{formatoAFecha($incapacidad->fecha_inicio_incapacidad)}}</td>
                        <td width="180px">{{formatoAFecha($incapacidad->fecha_fin_incapacidad)}}</td>
                        <td width="200px" class="position-relative">
                        	<a class="id_emp" data-toggle="modal" data-target="#incapacidadModal" 
                                data-id="{{$incapacidad->id}}" 
                                data-id_empleado="{{$empleado->id}}"
                                data-fecha_inicio_incapacidad="{{$incapacidad->fecha_inicio_incapacidad}}"
                                data-fecha_fin_incapacidad="{{$incapacidad->fecha_fin_incapacidad}}"
                                data-clave_incapacidad="{{$incapacidad->clave_incapacidad}}"
                                data-periodo="{{$incapacidad->periodo}}"
                                data-dias="{{$incapacidad->dias}}"
                                data-tipo_incapacidad="{{$incapacidad->tipo_incapacidad}}"
                                data-tipo_aplicacion="{{$incapacidad->tipo_aplicacion}}"
                                title="Editar incapacidad">
                        		<img src="/img/icono-editar.png" class="button-style-icon m-2">
                        	</a>

                            <a href="#" class="eliminar font-weight-bold" data-id="{{$incapacidad->id}}" >
                            	<img src="/img/eliminar.png" class="button-style-icon m-2" title="Eliminar incapacidad">
                            </a>
                                
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
<script>


    $(".id_emp").click(function(){
        id = $(this).data('id_empleado');
        document.getElementById('id_empleado').value = id;
    });

$(function(){

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

    $('.eliminar').click(function(){
        id_incapacidad = $(this).data('id');
        btn = $(this);


swal({
  title: "¿Realmente deseas eliminar esta incapacidad?",
  icon: "warning",
  buttons: true,
  dangerMode: true,
})
.then((willDelete) => {
  if (willDelete) {
    swal("La incapacidad se eliminó correctamente.", {
      icon: "success",
    });
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id' : id_incapacidad,
                    '_token': CSRF_TOKEN
                }
               url = "{{route('incapacidades.borrar')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function (response) {
                        if(response.ok == 1) {
                            $(".incapacidades tr#" + id_incapacidad).remove();
                            // alertify.success('La incapacidad se eliminó correctamente.');
                            btn.attr('disabled', false);
                            window.location.href = "{{route('incapacidades.inicio')}}";
                        } else {
                            // alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                            btn.attr('disabled', false);
                        }
                    }
                });
  } else {
    swal("La acción fue cancelada");
  }
});





    });
});
</script>