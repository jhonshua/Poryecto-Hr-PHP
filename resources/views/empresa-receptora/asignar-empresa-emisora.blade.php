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
        @include('includes.header',['title'=>'Asignar empresas emisoras',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png',
        'route'=>'empresar.empresareceptora'])


        <div class="col-md-12">

            <br>
            <br>
        </div>

        <div>

            <form class="d-flex align-items-center  p-3" style="border-radius: 30% 30% 30% 30%;" id="submit_empresa" method="POST" action="{{ route('empresar.empresaemisora') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-5">
                    <select name="empresas[]" id="empresasEmisoras" multiple style="width: 100%" size="20" class="p-2 form-control">
                        @foreach ($empresasDisponibles as $empDisponible)
                        <option value="{{$empDisponible->id}}" class="pt-1">{{$empDisponible->razon_social}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="container-asignar">
                    <button type="button" id="add_empresa" class="btn font-weight-bold">
                        <img src="/img/icono-asociar-seleccionado.png" class="button-style-icon ml-3" style="float: right;">
                        Asociar seleccionados

                    </button>
                </div>
                <div class="article-tab border ml-2">
                    <div class=" empresas-asignadas" style="height: 315px;  overflow: hidden auto; vertical-align:middle;">
                        @foreach ($empresasAsignadas as $empAsignada)
                        <div class="mb-2 d-flex" id="{{$empAsignada->id}}">
                            <a href="#" class="borrar mr-2" data-empAsignada="{{$empAsignada->id}}" data-empCliente="{{$idEmpresa}}">
                                <img src="/img/icono-borrar.png" class="button-style-icon">
                            </a>
                            {{$empAsignada->razon_social}}
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
        $("#add_empresa").click(function() {
            var empresasEmisoras = document.getElementById("empresasEmisoras").value;

            if (empresasEmisoras == "") {
                swal({
                    title: "Para continuar es necesario que selecciones una empresa",
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
            document.getElementById("submit_empresa").submit()
        }


        $(function() {

            $(".borrar").click(function() {
                var idEmpAsignada = $(this).data('empasignada');
                var idEmpCliente = $(this).data('empcliente');


                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'idEmpCliente': idEmpCliente,
                    'idEmpAsignada': idEmpAsignada,
                    '_token': CSRF_TOKEN
                }

                var url = "{{ route('empresar.eliminarememisora') }}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.ok == 1) {
                            $(".empresas-asignadas #" + idEmpAsignada + ' a').remove();
                            empEmisora = $(".empresas-asignadas #" + idEmpAsignada).text();
                            $("#empresasEmisoras").append(new Option(empEmisora, idEmpAsignada));
                            $(".empresas-asignadas #" + idEmpAsignada).remove();
                            
                        } else {
                          
                        }
                    }
                });

            });


        });
    </script>