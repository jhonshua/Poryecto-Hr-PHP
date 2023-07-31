<div class="modal" tabindex="-1" role="dialog" id="prestacionesModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> prestación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">
            <form method="post" id="prestaciones_form" action="{{route('parametria.prestaciones.crear.editar')}}">
                @csrf
                <label for="">Nombre:</label>
                <input type="text" name="nombre" id="nombre"  class="form-control input-style-custom mb-3" required>
                <label for="">Jerarquía:</label>
                <select name="tipo_clase" id="tipo_clase"  class="form-control input-style-custom mb-3" required>
                    @foreach ($clases as $clase)
                        <option value="{{$clase->id}}">{{$clase->tipo_clase}}</option>
                    @endforeach
                </select>
                <div class="row justify-content-center"> 
                    <div class="row justify-content-center col-xs-12 col-md-12 col-lg-2">                        
                        <button type="submit" class="button-style mt-4 guardar btn-block">Guardar</button>
                    </div> 
                </div> 
                <input type="hidden" name="id" id="id" value="" required>
            </form>
        </div>
        </div>
    </div>
</div>

<script>
$(function(){

    // al abrir el modal cargamos los departamentos
    $('#prestacionesModal').on('shown.bs.modal', function (e) {
        var nombre = $(e.relatedTarget).data('nombre').toUpperCase();
        var idPrestacion = $(e.relatedTarget).data('id');
        var tipo_clase = $(e.relatedTarget).data('tipo_clase');
        var accionLabel = (idPrestacion == '') ? 'Crear' : 'Editar';
        $('#prestacionesModal .modal-title span').text(accionLabel);
        $('#prestacionesModal .modal-body #nombre').val(nombre);
        $('#prestacionesModal .modal-body #id').val(idPrestacion);
        $('#prestacionesModal .modal-body #tipo_clase').val(tipo_clase); 
    });

    // boton guardar
    $('#prestacionesModal .guardar').click(function(){
        $(this).attr('disabled', true);
        $(this).val('Espere...');
        $('#prestacionesModal #nombre').val($('#prestacionesModal #nombre').val().toUpperCase());
        $('#prestaciones_form').submit();
    });
});
</script>

