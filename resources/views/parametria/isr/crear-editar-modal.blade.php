<div class="modal" tabindex="-1" role="dialog" id="isrModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> impuesto ISR</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="isr_form" action="{{route('parametria.guardar.isr')}}">
                @csrf
                <label for="">Tipo tabla:</label>
                <select name="tipo_tabla" id="tipo_tabla" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                    <option value="">SELECCIONE</option>
                    <option value="SEMANAL">SEMANAL</option>
                    <option value="QUINCENAL">QUINCENAL</option>
                    <option value="ANUAL">ANUAL</option>
                </select>

                <label for="">Limite inferior:</label>
                <input type="number" name="limite_inferior" id="limite_inferior" class="form-control input-style-custom mb-3" placeholder="Limite inferior" required step="any" min="0">

                <label for="">Limite superior:</label>
                <input type="number" name="limite_superior" id="limite_superior" class="form-control input-style-custom mb-3" placeholder="Limite superior" required step="any" min="0">

                <label for="">Cuota fija:</label>
                <input type="number" name="cuota_fija" id="cuota_fija" class="form-control input-style-custom mb-3" placeholder="Cuota fija" step="any" required min="0">

                <label for="">Porcentaje:</label>
                <input type="number" name="porcentaje" id="porcentaje" class="form-control input-style-custom mb-3" placeholder="Porcentaje" step="any" required min="0">
                
{{--                 <div class="row justify-content-center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="submit" class="button-style guardar mt-4 btn-block">Guardar</button>

                </div>
 --}}

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
    let tipo_tabla = '';
    let limite_inferior = 0;
    let limite_superior = 0;
    let cuota_fija = 0;
    let porcentaje = 0;
    // al abrir el modal cargamos las prestaciones
    $('#isrModal').on('shown.bs.modal', function (e) {
        let idImpuesto = $(e.relatedTarget).data('id');        
        
        if(idImpuesto == ''){
            accionLabel = 'Crear';
            // var tipo_tabla = 0;
            limite_inferior = 0;
            limite_superior = 0;
            cuota_fija = 0;
            porcentaje = 0;
        } else {
            accionLabel = 'Editar';
            tipo_tabla = $(e.relatedTarget).data('tipo_tabla').toUpperCase();
            limite_inferior = $(e.relatedTarget).data('limite_inferior');
            limite_superior = $(e.relatedTarget).data('limite_superior');
            cuota_fija = $(e.relatedTarget).data('cuota_fija');
            porcentaje = $(e.relatedTarget).data('porcentaje');
        }

        $('#isrModal .modal-title span').text(accionLabel);
        $('#isrModal .modal-body #id').val(idImpuesto);
        $('#isrModal .modal-body #tipo_tabla').val(tipo_tabla);
        $('#isrModal .modal-body #limite_inferior').val(limite_inferior);
        $('#isrModal .modal-body #limite_superior').val(limite_superior);
        $('#isrModal .modal-body #cuota_fija').val(cuota_fija);
        $('#isrModal .modal-body #porcentaje').val(porcentaje);
    });

    $('#isr_form').submit( function() {  
        let valida = false;
       if($('#tipo_tabla').val().trim() != '') {            
            valida = true;
            $('#isrModal .guardar').attr('disabled', true).text('Espere...');
        } else {            
            swal("", "Seleccione tipo tabla");
        }
        return valida;        
    });
    $('.select-clase').select2();
});


</script>

