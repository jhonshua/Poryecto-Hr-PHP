<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar empleados</h5>
            </div>

            <div class="modal-body row pb-2">

                @if($errores == null)
                <div class="col-md-12 mt-3">


                    <a href="{{ $layout }}" class="text-success" target="_blank">VER EL LAYOUT DE EJEMPLO <i class="fas fa-table"></i></a> <br>


                    * NOTA: Los campos marcados en amarillo son textos que deben coincidir con el nombre en los <a href="{{ route('empleados.catalogos') }}" target="_blank" class="badge badge-warning">catálogos</a> correspondientes.

                    <HR>

                    <div class="row">
                        <div class="col-md-12">
                            {{-- {{route('empleados_bck.importar')}} --}}

                            <form method="post" id="submit-importar" action="{{ route('empleados.importar') }}" enctype="multipart/form-data">
                                @csrf

{{--                                 <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <input type="file" class="custom-file-input " name="archivo_empleado" onchange="file('file_key')" id="archivo-empleados" accept=".xls, .xlsx" required>
                                        <label class="custom-file-label" for="ine" id="farchivo-empleados_text">Subir archivo</label>
                                    </div>
                                    <div class="col-md-1"></div>
                                </div> --}}


                                        <div class="custom-file center input-style custom-file-container mb-3">
                                            <input type="file" class="custom-file-input" name="archivo_empleados" onchange="file('archivo-empleados')" id="archivo-empleados" accept=".xls, .xlsx"  required>
                                            <label class="custom-file-label" for="ine" id="archivo-empleados_text">Archivo</label>
                                        </div>

                                <br />
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="simple" id="simple">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Layout simple (solo NOM035)
                                    </label>
                                </div>
                                <div class="mt-3"></div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @else
            <div class="col-md-12 mt-3">
                Para poder importar se requiere que actualices los siguentes datos:
                <br>
                @foreach($errores as $indice => $descripcion)
                @if($descripcion == 0)
                <p>* {{$indice}}</p>
                @endif

                @endforeach


                @endif
                {{-- <div class="modal-footer"> --}}
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="button" class="btn button-style-cancel regresar" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        @if($errores == null)
                            <button type="button" class="btn button-style continuar" id="importar">Guardar</button>
                        @endif
                        </div>
                    </div>

                    <div class="mt-2"></div>
                {{-- </div> --}}

            </div>
        </div>
    </div>


    <script type="text/javascript">
        $(function() {
            $('#importarModal').on('shown.bs.modal', (function(e) {
                let errores = $e.relatedTarget.data('errores');
            }))
        })

        $("#importar").click(function() {
            var archivoempleados = document.getElementById("archivo-empleados").value;

            if (archivoempleados == "") {
                swal({
                    title: "Para continuar debes agregar la información requerida",
                });
            } else {
                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });
                setTimeout(submitimportar, 1500);
            }
        });

        function submitimportar() {
            document.getElementById("submit-importar").submit()
        }

        function file(val){

            var text = val+"_text";
            document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
        }


    </script>