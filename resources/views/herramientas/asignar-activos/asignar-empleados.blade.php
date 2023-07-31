<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Asignar empleados a un activo',
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'asignaActivos.tabla'])

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

        <div class="col-md-12">

            <br>
            <br>
        </div>


        <div>
            <form class="d-flex align-items-center  p-3" style="border-radius: 30% 30% 30% 30%;" id="submit_empleados" method="POST" action="{{ route('asignaActivos.asignarEmpleados') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-5">
                    <input type="hidden" name="id_activo" value="{{$id}}">
                    <select name="empleados[]" id="empleados" multiple style="width:100%" size="20" class="p-2 form-control">
                        @foreach ($empleados as $empleado)
                        <option value="{{$empleado->id}}" class="pt-1">{{$empleado->nombre .' '. $empleado->apaterno .' '. $empleado->amaterno .'     ('. $empleado->puesto .')'}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="container-asignar">
                    <button type="button" id="add_empleado" class="btn font-weight-bold">
                        <img src="/img/icono-asociar-seleccionado.png" class="button-style-icon ml-3" style="float: right;">
                        Asociar seleccionados
                    </button>
                </div>


                <div class="article-tab border ml-2">
                    <div class="empleados-asignados" style="height: 315px; overflow:hidden auto; vertical-align:middle;">
                        @if(count($empleados_asignados) > 0)
                        @foreach($empleados_asignados as $empleas)
                        <div class="mb-2 d-flex" id="{{$empleas->id}}">
                            <form action="{{route('asignaActivos.eliminaEmpleado')}}" method="post">
                                @csrf
                                <input type="hidden" name="id_activo" value="{{$id}}">
                                <input type="hidden" name="id_empleado" value="{{$empleas->id}}">
                                <button type="submit" class="btn  btn-sm float-right" data-toggle="tooltip" data-placement="left" title="Eliminar empleado">
                                    <img src="/img/icono-borrar.png" class="button-style-icon">
                                </button>
                            </form>

                            <a>{{$empleas->nombre .' '. $empleas->apaterno .' '. $empleas->amaterno}}</a>
                        </div>
                        @endforeach
                        @else
                        <a>No hay empleados asignados</a>
                        @endif
                    </div>
                </div>
            </form>

        </div>
        @include('includes.footer')

        <script>
            $("#add_empleado").click(function() {
                var empleados = document.getElementById("empleados").value;

                if (empleados == "") {
                    swal({
                        title: "Para continuar es necesario que selecciones un empleado",
                    });
                } else {
                    swal("Espere un momento, la información esta siendo procesada", {
                        icon: "success",
                        buttons: false,
                    });
                    setTimeout(submitForm, 1500);
                }
            });

            function submitForm() {
                document.getElementById("submit_empleados").submit()
            }
        </script>