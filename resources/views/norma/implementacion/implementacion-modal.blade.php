<div class="modal" tabindex="-1" role="dialog" id="implementacionModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva implementación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" id="implementacion_form" role="form">
                    @csrf
                    <div class="form-row mt-1" id="fechas">

                    </div>
                    <div class="form-row mt-1">
                        <div class="col-md-6">
                            <label for="razon_social">Registro patronal: </label>
                            <select class="form-control input-style-custom" name="razon_social" id="razon_social">
                                <option value="">seleccione</option>
                                @if(@isset($razones_sociales))
                                @foreach($razones_sociales as $razon_social)
                                <option value="{{$razon_social->id}}">{{$razon_social->razon_social}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="" for="sede">Sede: </label>
                            <select class="form-control input-style-custom" name="sede" id="sede">
                                <option value="">seleccione</option>
                                @if(@isset($sedes))
                                @foreach($sedes as $sede)
                                <option value="{{$sede->id}}">{{$sede->nombre}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div><br />
                    <h5>Encargados</h5>
                    <div id="encargados">

                    </div>
                    <div class="text-right" id="botones-encargado">
                    </div>
                    <br />
                    <div class="form-row" style="display: flex; justify-content:center;">
                        <button class="button-style guardar ml-3" id="btn-guardar">Guardar</button>
                        <a href="#"><button type="button" class="button-cancel-style ml-2" data-dismiss="modal" aria-label="Close">Cancelar</button></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.css">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css" rossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<style>
    label.requerido:before {
        content: "*";
        color: red;
    }

    label:after {
        content: " ";
    }
</style>
<script src="{{asset('js/validate/jquery.validate.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script>
    // let idUsuario = '';
    $(function() {
        // al abrir el modal cargamos el usuario
        $('#implementacionModal').on('shown.bs.modal', function(e) {
            iniciaFormulario();
        });
    });

    function iniciaFormulario() {
        $("#razon_social, #sede").val('');
        $("#fechas").html('<div class="col-md-6"><label class="requerido" for="fecha_inicio">Fecha inicio: </label><input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control input-style-custom mb-3" autocomplete="off"></div><div class="col-md-6"><label class="requerido" for="fecha_fin">Fecha fin: </label><input type="text" name="fecha_fin" id="fecha_fin" class="form-control input-style-custom mb-3" autocomplete="off"></div>');
        dateFrom = moment().add(6, 'd').format('YYYY-MM-DD');
        //console.log(dateFrom);
        $('#fecha_inicio').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            defaultDate: ''
        });
        $('#fecha_fin').datetimepicker({
            locale: 'es',
            format: 'DD-MM-YYYY',
            minDate: dateFrom
        });

        $('input[name=fecha_inicio]').on('dp.change', function(e) {
            let min = moment(e.date, "DD-MM-YYYY").add(6, 'day');
            let f = $('input[name=fecha_fin]').data("DateTimePicker").date();
            $('input[name=fecha_fin]').data("DateTimePicker").minDate(min);
            if (e == null || f == null) {
                $('input[name=fecha_fin]').data("DateTimePicker").clear();
            }
        });

        $("#encargados").html('<div id="1"><div class="form-row mt-1"><div class="col-md-6"><label class="requerido" for="nombre1">Nombre: </label><input type="text" name="nombre1" id="nombre1" class="form-control input-style-custom mb-3"></div><div class="col-md-6"><label class="requerido" for="correo1">Correo: </label><input type="text" name="correo1" id="correo1" class="form-control input-style-custom mb-3"></div></div></div>');


        $("#botones-encargado").html('<a data-encargado="1" class="button-style mr-2" id="plus"  title="Agregar encargado"><i class="fas fa-plus"></i></a><a data-encargado="1" class="button-style mr-2 disabled" id="minus" title="Eliminar encargado"><i class="fas fa-minus"></i></a>');
        eventosBotonesEncargados();
        encargadosAsignados();
        validarFormulario();
    }

    function encargadosAsignados() {
        $("#nombre1").val("Hilary Cristina Velasco Pineda");
        $("#correo1").val("do@singh.com.mx");
        $("#plus").click();
        $("#nombre2").val("Laura Lucero Hernández Dominguez");
        $("#correo2").val("asesor.rys@singh.com.mx");
    }

    function eventosBotonesEncargados() {
        $("#plus").off();
        $("#plus").on("click", function() {
            $("#minus").removeClass("disabled");
            let cuantos = $(this).data("encargado");
            cuantos++;
            if (cuantos <= 5) {
                $(this).data("encargado", cuantos);
                $("#encargados").append('<div id="e' + cuantos + '"><div class="form-row mt-1"><div class="col-md-6"><label for="nombre' + cuantos + '">Nombre: </label><input type="text" name="nombre' + cuantos + '" id="nombre' + cuantos + '" class="form-control input-style-custom mb-3"></div><div class="col-md-6"><label for="correo' + cuantos + '">Correo: </label><input type="text" name="correo' + cuantos + '" id="correo' + cuantos + '" class="form-control input-style-custom mb-3"></div></div></div>');
                if (cuantos == 5) {
                    $(this).addClass("disabled");
                }
            }
        });
        $("#minus").off();
        $("#minus").on("click", function() {
            $("#plus").removeClass("disabled");
            let cuantos = $("#plus").data("encargado");
            if (cuantos > 1) {
                $("#e" + cuantos).slideUp("slow", function() {
                    $(this).remove();
                    cuantos--;
                    $("#plus").data("encargado", cuantos);
                });
                if (cuantos == 2) {
                    $(this).addClass("disabled");
                }
            }
        });
    }

    function validarFormulario() {
        $("#implementacion_form").validate({
            errorClass: "text-danger",
            errorElement: "span",
            errorPlacement: function(error, element) {
                error.appendTo($('label[for=' + element.attr("name") + ']'));
            },
            rules: {
                fecha_inicio: {
                    required: true
                },
                fecha_fin: {
                    required: true
                },
                nombre1: {
                    required: true
                },
                correo1: {
                    required: true,
                    email: true
                },
                correo2: {
                    email: true
                },
                correo3: {
                    email: true
                },
                correo4: {
                    email: true
                },
                correo5: {
                    email: true
                }
            },
            submitHandler: function(form) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $("#implementacionModal .guardar").html(`<span class="spinner-grow" role="status" aria-hidden="true"></span>Espere...`).attr("disabled", "disabled");

                let url = "{{route('norma.implementacion.crear')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: $('#implementacion_form').serialize(),
                    success: function(response) {
                        if (response.ok == 1) {
                            iniciaFormulario();
                            $(".modal-title").html("Implementación creada");
                            $('#implementacionModal .guardar').html('Guardar').attr("disabled", false);

                            $('#DataTables_Table_0').DataTable().ajax.reload();
                            $(".enviaf").off();
                            $(".enviaf").on("click", function() {
                                $("#implementacion").val(response.implementacion);
                                $("#accion").val($(this).data('enlace'));
                                $("#accionImplementacion").attr('action', $(this).data('enlace'));
                                $("#accionImplementacion").submit();
                            });

                            swal({
                                    title: 'Actividad creada',
                                    text: 'La implementación se almacenó con éxito.',
                                    icon: 'success',
                                    timer: 1000,
                                    buttons: false,
                                })
                                .then(() => {
                                    location.reload();
                                })

                        } else {
                            swal("", "Ocurrió un error. Intente nuevamente.", "error");
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#implementacionModal .guardar').html('Guardar').attr("disabled", false);
                        swal("", "Ocurrió un error", "error");
                        // console.log(errorThrown);
                    }
                });
            }
        });


    }
</script>