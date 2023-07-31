<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>
<body>
    @include('includes.navbar')
    <div class="container">
    @include('includes.header',['title'=>'Asignar contratos',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png',
        'route'=>'empresar.empresareceptora'])
       
        <div class="col-md-12">

            <br>
            <br>
        </div>

        <div class="row alert_success" style="display: none;">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: El contrato se desasoció correctamente.</strong>
            </div>
        </div>



        <div>
            <form class="d-flex align-items-center p-3" id="submit_contratos" method="POST" action="{{ route('empresar.agregarcontratoempresa') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-5">
                    <select name="contratos[]" id="contratos" multiple style="width: 100%" size="20" class="p-2 form-control">
                        @foreach ($contratosDisponibles as $contDisponible)
                        <option value="{{$contDisponible->id}}" class="pt-1">{{$contDisponible->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 p-14">
                    <div class="container-asignar">
                        <button type="button" id="add_contratos" class="btn font-weight-bold">
                            <img src="/img/icono-asociar-seleccionado.png" class="button-style-icon ml-3" style="float: right;">
                            Asociar seleccionados
                        </button>
                    </div>
                </div>
                <div class="article-tab border ml-2">
                    <div class="contratos-asignados" style="height: 315px;  overflow: hidden auto; vertical-align:middle;">
                        @foreach ($contratosAsignados as $contAsignado)
                        <div class="mb-2 d-flex" id="{{$contAsignado->id}}">
                            <a href="#" class="borrar mr-2" data-contAsignado="{{$contAsignado->id}}" data-idEmpresa="{{$idEmpresa}}">
                                <img src="/img/icono-borrar.png" class="button-style-icon">
                            </a>
                            {{$contAsignado->nombre}}
                        </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" name="idEmpresa" value="{{$idEmpresa}}">
            </form>

        </div>

    </div>
    @include('includes.footer')
    <script>
        $("#add_contratos").click(function() {
            var contratos = document.getElementById("contratos").value;

            if (contratos == "") {
                swal({
                    title: "Para continuar es necesario que selecciones contratos",
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
            document.getElementById("submit_contratos").submit()
        }


        $(function() {

            $(".borrar").click(function() {
                $(".alert_success").hide();
                var idContAsignado = $(this).data('contasignado');
                var idEmpresa = $(this).data('idempresa');


                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'idContAsignado': idContAsignado,
                    'idEmpresa': idEmpresa,
                    '_token': CSRF_TOKEN
                }

                var url = "{{route('empresar.eliminarcontratoempresa')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.ok == 1) {
                            $(".alert_success").show();
                            $(".contratos-asignados #" + idContAsignado + ' a').remove();
                            nombreContrato = $(".contratos-asignados #" + idContAsignado).text();
                            $("#contratos").append(new Option(nombreContrato, idContAsignado));
                            $(".contratos-asignados #" + idContAsignado).remove();

                            // alertify.success('El registro se eliminó correctamente.');
                        } else {
                            // alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                        }
                    }
                });


            });

            function Contrato(idContAsignado, idEmpresa) {

            }
        });
    </script>
