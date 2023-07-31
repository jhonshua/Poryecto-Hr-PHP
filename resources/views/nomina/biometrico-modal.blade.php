<div class="modal" tabindex="-1" role="dialog" id="biometricosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Actualizar periodo de nomina </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="periodo_biometricos_form" action="{{ route('nomina.actualziarbiometrico') }}"> 
                @csrf
                <table width="100%" class="mb-3">
                    <tr>
                        <td width="50%">
                            ¿Deseas incluir las asistencias de los biometricos en el calculo de la nomina?
                        </td>
                        <td>
                            <select name="incluirBiometrico" id="incluirBiometrico" class="form-control mb-3" required>
                                <option value="1">SI</option>
                                <option value="0">NO</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            &nbsp;
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr class="mt-3">
                        <td width="50%" class="fechas">
                            <label>Fecha inicial:</label>
                            <input type="text" name="fecha_inicial_biometrico" id="fecha_inicial_biometrico" class="form-control mb-3 mr-1 datepicker">
                        </td>
                        <td class="fechas">
                            <label>Fecha final:</label>
                            <input type="text" name="fecha_final_biometrico" id="fecha_final_biometrico" class="form-control mb-3 ml-1 datepicker">
                        </td>
                    </tr>
                </table>

                <button class="button-style center guardar mt-3 btn-block">Actualizar</button>
                <input type="hidden" name="idPeriodo" id="idPeriodo">
            </form>
        </div>
        </div>
    </div>
</div>

<script>
$(function(){

    var dias_periodo;

    // al abrir el modal cargamos las prestaciones
    $('#biometricosModal').on('shown.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');
        var fecha_inicial_periodo = $(e.relatedTarget).data('fecha_inicial_periodo');
        var fecha_final_periodo = $(e.relatedTarget).data('fecha_final_periodo');
        dias_periodo = $(e.relatedTarget).data('dias_periodo');
            

        $('#biometricosModal .modal-body #idPeriodo').val(id);
        $('#biometricosModal .modal-body #fecha_inicial_biometrico').val(fecha_inicial_periodo);
        $('#biometricosModal .modal-body #fecha_final_biometrico').val(fecha_final_periodo);

        // $("#biometricosModal #fecha_inicial_biometrico").datepicker('option', 'minDate', $('#fecha_inicial_biometrico').val());
        // $("#biometricosModal #fecha_inicial_biometrico").datepicker('option', 'maxDate', $('#fecha_final_biometrico').val());
        
        // $("#biometricosModal #fecha_final_biometrico").datepicker('option', 'minDate', $('#fecha_inicial_biometrico').val());
        // $("#biometricosModal #fecha_final_biometrico").datepicker('option', 'maxDate', $('#fecha_final_biometrico').val());
    });

    $('#biometricosModal #incluirBiometrico').change(function(){
        $('#biometricosModal .fechas').toggle();
    });

    $('#fecha_inicial_biometrico').change(function(){

        fecha = $("#biometricosModal #fecha_inicial_biometrico").datepicker('getDate');
        fecha.setDate(fecha.getDate() + dias_periodo);
        $("#biometricosModal #fecha_final_biometrico").datepicker('setDate', fecha);
    });


    $('#periodo_biometricos_form').submit(function(){
        $('#biometricosModal .guardar').attr('disabled', true).text('Espere...');
        $('#biometricosModal select, #biometricosModal input').attr('disabled', false);
    });
});
</script>
