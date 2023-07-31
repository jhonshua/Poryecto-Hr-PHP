<div class="modal" tabindex="-1" role="dialog" id="conceptosNominaModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar concepto de nómina <span></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">

                <form method="post" id="conceptosNomina_form" action="{{ route('parametria.actualizar-conceptos-nomina') }}">
                    @csrf
                    <label for="">Nombre de cuenta:</label>

                    <input type="text" name="name_cuenta" id="name_cuenta" class="form-control mb-3" required>

                    <label for="">Número de cuenta:</label>
                    <input type="text" name="cuenta_contable" id="cuenta_contable" class="form-control mb-3" required min="0">

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label for="">Integrar a variables:</label>
                            <input type="checkbox" name="integra_variables" id="integra_variables" value="1">
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="radio" name="debe_haber" id="debe" value="">
                                <label for="debe">Debe</label>
                                &nbsp;&nbsp;
                                <input type="radio" name="debe_haber" id="haber" value="">
                                <label for="haber">Haber</label>
                            </div>
                        </div>
                    </div>

                    <div class="isr d-none">
                        <hr class="mt-0">
                        <h5>SUBSIDIO</h5>

                        <label for="">Nombre de cuenta Subsidio:</label>

                        <input type="text" name="name_cuenta_isr" id="name_cuenta_isr" class="form-control mb-3">

                        <label for="">Número de cuenta:</label>
                        <input type="text" name="cuenta_contable_isr" id="cuenta_contable_isr" class="form-control mb-3">

                    </div>
                    <input type="hidden" name="id" id="id" required>
                    <input type="submit" class="center button-style" id="edit_concepto" value="Guardar">
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    $(function() {

        // al abrir el modal cargamos las prestaciones
        $('#conceptosNominaModal').on('shown.bs.modal', function(e) {
            var idConcepto = $(e.relatedTarget).data('id');
            var name_cuenta = $(e.relatedTarget).data('name_cuenta');
            var nombre_concepto = $(e.relatedTarget).data('nombre_concepto');
            var cuenta_contable = $(e.relatedTarget).data('cuenta_contable');
            var integra_variables = $(e.relatedTarget).data('integra_variables');
            var debe_haber = $(e.relatedTarget).data('debe_haber');
            var name_cuenta_isr = $(e.relatedTarget).data('name_cuenta_isr');
            var cuenta_contable_isr = $(e.relatedTarget).data('cuenta_contable_isr');
            var rutinas = $(e.relatedTarget).data('rutinas');



            nombre_concepto = (name_cuenta) ? name_cuenta : nombre_concepto;
            $('#conceptosNominaModal .modal-body #id').val(idConcepto);
            if (rutinas == 'ISR') {
                $('#conceptosNominaModal .modal-body .isr').removeClass('d-none');
            } else {
                $('#conceptosNominaModal .modal-body .isr').addClass('d-none');
            }
            $('#conceptosNominaModal .modal-body #name_cuenta').val(nombre_concepto);
            $('#conceptosNominaModal .modal-body #nombre_concepto').val(nombre_concepto);
            $('#conceptosNominaModal .modal-body #cuenta_contable').val(cuenta_contable);
            if (integra_variables == 1) {
                $('#conceptosNominaModal .modal-body #integra_variables').prop('checked', true);
            }
            if (debe_haber == 1)
                $('#conceptosNominaModal .modal-body #debe').prop('checked', true);
            else
                $('#conceptosNominaModal .modal-body #haber').prop('checked', true);

            $('#conceptosNominaModal .modal-body #name_cuenta_isr').val(name_cuenta_isr);
            $('#conceptosNominaModal .modal-body #cuenta_contable_isr').val(cuenta_contable_isr);
        });



        function submitForm() {
            document.getElementById("submit_concepto").submit()
        }

    });
</script>