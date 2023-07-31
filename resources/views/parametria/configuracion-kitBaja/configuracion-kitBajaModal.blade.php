<div class="modal" tabindex="-1" role="dialog" id="controlExpedienteModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> campo para el kit de baja </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form method="post" id="expediente_form" action="{{ route('kitbaja.creaEdita') }}">
                    @csrf
                    <label for="">Alias/Etiqueta a mostrar:</label>
                    <input type="text" name="alias" id="alias" class="form-control mb-3" required>

                    <label for="">Nombre ID:</label>
                    <input type="text" name="nombre_campo" id="nombre_campo" class="form-control mb-3" required>

                    <label for="">Obligatorio:</label>
                    <select name="obligatorio" id="obligatorio" class="form-control mb-2" required>
                        <option value="0">NO</option>
                        <option value="1">SI</option>
                    </select>
                    <div class="col-md-12 text-center">
                        <input type="hidden" name="id" id="id">
                        <button type="button" data-dismiss="modal" class="button-style-cancel mr-2 mt-4">CANCELAR</button>
                        <button class="button-style mt-4">GUARDAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(function() {
        // al abrir el modal cargamos las prestaciones
        $('#controlExpedienteModal').on('shown.bs.modal', function(e) {
            var idSubsidio = $(e.relatedTarget).data('id');
            var tipo = 'file';
            if (idSubsidio == null) {
                accionLabel = 'Crear';
                var nombre_campo = '';
                var alias = '';
                var obligatorio = 0;
            } else {
                accionLabel = 'Editar';
                var nombre_campo = $(e.relatedTarget).data('nombre_campo');
                var alias = $(e.relatedTarget).data('alias');
                var obligatorio = $(e.relatedTarget).data('obligatorio');
            }

            $('#controlExpedienteModal .modal-title span').text(accionLabel);
            $('#controlExpedienteModal .modal-body #id').val(idSubsidio);
            $('#controlExpedienteModal .modal-body #nombre_campo').val(nombre_campo);
            $('#controlExpedienteModal .modal-body #alias').val(alias);
            $('#controlExpedienteModal .modal-body #obligatorio').val(obligatorio);
        });

        // boton guardar
        $('#controlExpedienteModal .guardar').click(function() {
            $(this).attr('disabled', true);
            $(this).text('ESPERE...');
            $('#expediente_form').submit();
        });

        // CAMBIAR EL NOMBRE ID
        $('#nombre_campo').change(function() {
            $('#controlExpedienteModal #nombre_campo').val($(this).val().trim().replace(/ /gi, '_').replace(/[^a-zA-Z0-9_]/g, '').toLowerCase());
        });

        $('#alias').on('keyup', function() {
            $('#nombre_campo').val($(this).val());
            $('#nombre_campo').trigger('change');
        });
    });
</script>