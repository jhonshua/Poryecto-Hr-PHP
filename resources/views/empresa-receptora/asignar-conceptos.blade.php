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
    @include('includes.header',['title'=>'Asignar conceptos',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png',
        'route'=>'empresar.empresareceptora'])
 
        <div class="col-md-12">

            <br>
            <br>
        </div>

        <div class="row alert_success" style="display: none;">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: El concepto se desasoció correctamente.</strong>
            </div>
        </div>

        <div>
            <form class="d-flex align-items-center p-3"  id="submit_conceptos" method="POST" action="{{ route('empresar.agregarconcepto') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-5">
                    <select name="conceptos[]" id="conceptos" multiple style="width: 100%" size="20" class="p-2 form-control">
                        @foreach ($conceptosDisponibles as $contDisponible)
                        <option value="{{$contDisponible->id_alterno}}" class="pt-1">{{strtoupper($contDisponible->nombre_concepto)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="container-asignar">
                    <button type="button" id="add_conceptos" class="btn font-weight-bold">
                        <img src="/img/icono-asociar-seleccionado.png" class="button-style-icon ml-3" style="float: right;">
                        Asociar seleccionados
                    </button>
                </div>
                <div class="article-tab border ml-2">
                <div class=" conceptos-asignados " style="height: 315px; vertical-align:middle; overflow: hidden auto;">
                    @foreach ($conceptosAsignados as $concAsignado)
                    <div class="mb-2 d-flex" id="{{$concAsignado->id_alterno}}">
                        <a href="#" class="borrar mr-2" data-concAsignado="{{$concAsignado->id_alterno}}">
                        <img src="/img/icono-borrar.png" class="button-style-icon">
                        </a>
                        {{strtoupper($concAsignado->nombre_concepto)}}
                    </div>
                    @endforeach
                </div>
            </div>
                <input type="hidden" id="idEmpresa" name="idEmpresa" value="{{$idEmpresa}}">
            </form>
        </div>


    </div>
    @include('includes.footer')
    <script>
        $("#add_conceptos").click(function() {
            var conceptos = document.getElementById("conceptos").value;

            if (conceptos == "") {
                swal({
                    title: "Para continuar es necesario que selecciones conceptos",
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
            document.getElementById("submit_conceptos").submit()
        }

        $(function() {

            $(".borrar").click(function() {
                $(".alert_success").hide();
                var idConcAsignado = $(this).data('concasignado');
                var idEmpresa = $('#idEmpresa').val();

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'idConcAsignado': idConcAsignado,
                    'idEmpresa': idEmpresa,
                    '_token': CSRF_TOKEN
                }

                var url = "{{route('empresar.eliminarconceptoempresa')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.ok == 1) {
                            $(".conceptos-asignados #" + idConcAsignado + ' a').remove();
                            nombreConcepto = $(".conceptos-asignados #" + idConcAsignado).text();
                            $("#conceptos").prepend(new Option(nombreConcepto, idConcAsignado));
                            $('#conceptos').scrollTop(0);
                            $(".conceptos-asignados #" + idConcAsignado).remove();
                            $(".alert_success").show();
                            // alertify.success('El concepto se desasoció correctamente.');
                        } else {
                            // alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                        }
                    }
                });


            });

            function borrarConcepto(idEmpresa, idConcAsignado) {

            }
        });
    </script>
