<div class="modal" tabindex="-1" role="dialog" id="notasModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Motivo del rechazo</h5>
        </div>
        <div class="modal-body">
            <form method="post" id="rechazo_form" action="{{ route('prestamos.guarda') }}">
                @csrf
                <textarea name="texto" id="texto" width="100%" rows="10" class="form-control" placeholder="Agrega un comentario describiendo el motivo por el cual se rechaza esta petición..." required></textarea>
                <div class="form-row mt-4">
                    <button type="button" class="btn btn-secondary cancelar mr-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rechazarPrestamo">Guardar</button>

                    <input type="hidden" name="prestamos_tipo_id" id="prestamos_tipo_id">
                    <input type="hidden" name="empresa_id" id="empresa_id" value="{{ intval(str_ireplace('empresa00', '', $data['base'])) }}">
                    <input type="hidden" name="usuario_id" id="usuario_id">
                    <input type="hidden" name="empleado_id" id="empleado_id">
                    <input type="hidden" name="empleado" id="empleado">
                    <input type="hidden" name="medio_contacto" id="medio_contacto">
                    <input type="hidden" name="estatus" id="estatus">
                    <input type="hidden" name="fecha_cierre" id="fecha_cierre">
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){

    // Rechazar prestamo
    $('.rechazarPrestamo').click(function(e) {

        e.preventDefault();
        $('#rechazo_form .btn').attr('disabled', true);
        $('#rechazo_form .rechazarPrestamo').val('Espere...');
        empleado_id = $('#prestamos_form #empleado_').val().split(' - ')[0];
        empleado = $('#prestamos_form #empleado_').val().split(' - ')[1];

        $('#rechazo_form #prestamos_tipo_id').val($('#prestamos_form #prestamo_tipo_id').val());
        $('#rechazo_form #usuario_id').val('{{ $idUsuario  }}');
        $('#rechazo_form #empleado_id').val(empleado_id);
        $('#rechazo_form #empleado').val(empleado);
        $('#rechazo_form #medio_contacto').val($('#prestamos_form #medio_contacto').val());
        $('#rechazo_form #fecha_cierre').val('{{ date("Y-m-d H:i:s") }}');
        $('#rechazo_form #estatus').val(2);
        $('#rechazo_form').submit();
    });
});
</script>
@endpush
