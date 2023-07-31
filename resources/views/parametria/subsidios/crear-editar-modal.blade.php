<div class="modal" tabindex="-1" role="dialog" id="subsidiosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> subsidio</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="subsidio_form" action="{{route('parametria.guardar.subsidio')}}">
                @csrf
                <label for="">Tipo tabla:</label>
                <select name="tipo_tabla" id="tipo_tabla" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                    <option value="" >SELECCIONE</option>
                    <option value="SEMANAL">SEMANAL</option>
                    <option value="QUINCENAL">QUINCENAL</option>
                    <option value="MENSUAL">MENSUAL</option>
                    <option value="DIARIA">DIARIA</option>
                    <option value="ANAUAL">ANAUAL</option>
                    <option value="DECENAL">DECENAL</option>
                </select>

                <label for="">Para ingresos de:</label>
                <input type="number" name="ingreso_desde" id="ingreso_desde" class="form-control input-style-custom mb-3" placeholder="Para ingresos de" step="any" required min="0">

                <label for="">Hasta ingresos de:</label>
                <input type="number" name="ingreso_hasta" id="ingreso_hasta" class="form-control input-style-custom mb-3" placeholder="Hasta ingresos de" step="any" required min="0">

                <label for="">SUBSIDIO:</label>
                <input type="number" name="subsidio" id="subsidio" class="form-control input-style-custom mb-3" placeholder="Subsidio" step="any" required min="0">

                <!-- <div class="btn btn-warning guardar mt-4">Guardar</div> -->

                <div class="row">
                    <div class="col-md-12 mt-3 text-center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="submit" class="btn button-style guardar">Guardar</button>
                    </div>
                </div>


                <input type="hidden" name="id" id="id">
            </form>
        </div>
        </div>
    </div>
</div>

<script>
$(function(){
    let accionLabel = '';
    let tipo_tabla = '';
    let ingreso_desde = 0;
    let ingreso_hasta = 0;
    let subsidio = 0;
    // al abrir el modal cargamos las prestaciones
    $('#subsidiosModal').on('shown.bs.modal', function (e) {
        let idSubsidio = $(e.relatedTarget).data('id');
        if(idSubsidio == ''){
            accionLabel = 'Crear';
            // tipo_tabla = 0;
            ingreso_desde = 0;
            ingreso_hasta = 0;
            subsidio = 0;
        } else {
            accionLabel = 'Editar';
            tipo_tabla = $(e.relatedTarget).data('tipo_tabla').toUpperCase();
            ingreso_desde = $(e.relatedTarget).data('ingreso_desde');
            ingreso_hasta = $(e.relatedTarget).data('ingreso_hasta');
            subsidio = $(e.relatedTarget).data('subsidio');
        }

        $('#subsidiosModal .modal-title span').text(accionLabel);
        $('#subsidiosModal .modal-body #id').val(idSubsidio);
        $('#subsidiosModal .modal-body #tipo_tabla').val(tipo_tabla);
        $('#subsidiosModal .modal-body #ingreso_desde').val(ingreso_desde);
        $('#subsidiosModal .modal-body #ingreso_hasta').val(ingreso_hasta);
        $('#subsidiosModal .modal-body #subsidio').val(subsidio);
    });

    // boton guardar
    // $('#subsidiosModal .guardar').click(function(){
    //     $(this).attr('disabled', true);
    //     $(this).val('Espere...');
    //     $('#subsidio_form').submit();
    // });
    $('#subsidiosModal').submit( function() {  
        let valida = false;
       if($('#tipo_tabla').val().trim() != '') {            
            valida = true;
            $('#subsidiosModal .guardar').attr('disabled', true).text('Espere...');
        } else {            
            swal("", "Seleccione tipo tabla");
        }
        return valida;        
    });
    $('.select-clase').select2();
});
</script>

