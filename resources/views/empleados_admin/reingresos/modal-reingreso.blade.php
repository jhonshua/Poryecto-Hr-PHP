<div class="modal" tabindex="-1" role="dialog" id="reingresoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> Reingreso de empleado </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="col-md-12 mt-3">
                    <div class="nombre text-center font-weight-bold"></div>
                    <div class="text-danger motivo text-center"></div>

                    <form method="post" action="{{route('reingresos.individual')}}" class="reingresar" enctype="multipart/form-data">
                        @csrf
                        <label for=""> Nueva fecha de alta:</label>
                        <input type="hidden" name="id" id="id">
                        <input type="text" name="fecha_alta" id="fecha_alta" class="form-control mb-3 datepicker input-style-custom" value="{{date('Y-m-d')}}" min="" required>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                                <button class="btn button-style mb-3">Generar</button>
                            </div>
                        </div>

                    </form>
                </div>

                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                <script src="{{asset('js/datapicker-es.js')}}"></script>

                <script>
                    $(function() {
                        $("#fecha_alta").datepicker("setDate", "{{date('Y-m-d')}}");


                        $("#fecha_alta").datepicker({
                            dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            changeMonth: true,
                           
                        });

                        $('#reingresoModal').on('shown.bs.modal', function(e) {
                            var nombreempleado = $(e.relatedTarget).data('nombre');
                            var motivo = $(e.relatedTarget).data('motivo');
                            var id = $(e.relatedTarget).data('id')
                            var fecha_baja = $(e.relatedTarget).data('fecha_baja');

                            $('#reingresoModal .modal-body .nombre').text(nombreempleado.trim());
                            $('#reingresoModal .modal-body .motivo').html('Este empleado fue dado de baja por: <br/>' + motivo);
                            $('#reingresoModal .modal-body #id').val(id);
                            $('#reingresoModal .modal-body #fecha_alta').prop('min', fecha_baja);
                        });
                        $('reingresar').submit(function(e) {
                            e.preventDefault();
                            var id = $('#reingresoModal #id').val();
                            var fecha_alta = $('#reingresoModal #fecha_alta').val();

                            swal({
                                    title: "¿Esta seguro de reingresar este empleado?",
                                    icon: "warning",
                                    buttons: true,
                                    dangerMode: true,
                                })
                                .then((willDelete) => {
                                    if (willDelete) {

                                        swal("Espere un momento, la información esta siendo procesada", {
                                            icon: "success",
                                            buttons: false,
                                        });

                                        reingresar(id, fecha_alta)


                                    } else {
                                        swal("La accion fue cancelada!");
                                    }
                                });
                        });

                        function reingresar(id, fecha_alta) {
                            // alertify.alert('Espere...').set('basic', true);
                            // alertify.alert('Aviso', 'Espere...');
                            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                            data = {
                                'id': id,
                                'fecha_alta': fecha_alta,
                                '_token': CSRF_TOKEN
                            }

                            var url = "{{route('reingresos.individual')}}";
                            $.ajax({
                                    type: "POST",
                                    url: url,
                                    data: data,
                                    dataType: 'JSON',
                                    success: function(response) {
                                        if (response.ok == 1) {
                                            $('#reingresoModal').modal('hide');
                                            $('.modal-backdrop').hide();
                                            $(".empleados tr#" + id).fadeOut('slow', function() {
                                                $(".empleados tr#" + id).hide();
                                                alertify.success('El empleado se reingresó correctamente.');
                                            });

                                        } else {
                                            alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                                        }
                                    },
                                    error: function() {
                                        alertify.alert('Error', 'Ocurrió un error, intentar nuevamente.');
                                    }
                                })
                                .done(function() {
                                    $('#reingresoModal .guardar').text('CONTINUAR').prop('disabled', false);
                                });
                        }
                    });
                </script>