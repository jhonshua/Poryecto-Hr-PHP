<div class="modal" tabindex="-1" role="dialog" id="abrirPModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Abrir periodo de nomina </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="periodo_biometricos_form" action="{{ route('nomina.abrirbiometrico') }}">
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
                            <input type="text" name="fecha_inicial_apertura" id="fecha_inicial_apertura" class="form-control mb-3 datepicker">
                        </td>
                        <td class="fechas">
                            <label>Fecha final:</label>
                            <input type="text" name="fecha_final_apertura" id="fecha_final_apertura" class="form-control mb-3 datepicker">
                        </td>
                    </tr>
                </table>

                <button class="btn btn-warning guardar mt-3 btn-block">Abrir Periodo</button>
                <input type="hidden" name="idPeriodo" id="idPeriodo">
            </form>
        </div>
        </div>
    </div>
</div>

<style>
    #abrirPModal label{
        margin-bottom: 0px;
    }
</style>

<script>
$(function(){

    var dias_periodo;

    // al abrir el modal cargamos las prestaciones
    $('#abrirPModal').on('shown.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');
        var fecha_inicial_periodo = $(e.relatedTarget).data('fecha_inicial_periodo');
        var fecha_final_periodo = $(e.relatedTarget).data('fecha_final_periodo');
        dias_periodo = $(e.relatedTarget).data('dias_periodo');
            

        $('#abrirPModal .modal-body #idPeriodo').val(id);
        $('#abrirPModal .modal-body #fecha_inicial_apertura').val(fecha_inicial_periodo);
        $('#abrirPModal .modal-body #fecha_final_apertura').val(fecha_final_periodo);

        // $("#abrirPModal #fecha_inicial_apertura").datepicker('option', 'minDate', $('#fecha_inicial_apertura').val());
        // $("#abrirPModal #fecha_inicial_apertura").datepicker('option', 'maxDate', $('#fecha_final_apertura').val());
        
        // $("#abrirPModal #fecha_final_apertura").datepicker('option', 'minDate', $('#fecha_inicial_apertura').val());
        // $("#abrirPModal #fecha_final_apertura").datepicker('option', 'maxDate', $('#fecha_final_apertura').val());
    });

    $('#abrirPModal #incluirBiometrico').change(function(){
        $('#abrirPModal .fechas').toggle();
    });

    $('#fecha_inicial_apertura').change(function(){

        fecha = $("#abrirPModal #fecha_inicial_apertura").datepicker('getDate');
        fecha.setDate(fecha.getDate() + dias_periodo);
        $("#abrirPModal #fecha_final_apertura").datepicker('setDate', fecha);
    });


    $('#abrirPModal #periodo_biometricos_form').submit(function(){
        $('#abrirPModal .guardar').attr('disabled', true).text('Espere...');
        $('#abrirPModal select, #abrirPModal input').attr('disabled', false);
    });
});
</script>