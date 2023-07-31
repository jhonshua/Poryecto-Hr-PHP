<div class="modal" tabindex="-1" role="dialog" id="imprimirPModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Selecciona los departamentos a considerar</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="periodo_imprimir_form" action="{{ route('nomina.imprimirnomina') }}" class="" target="_blank">
                @csrf

                <label style='color:red;font-style: italic;'>La nomina de este periodo ya fue Cerrada.</label><br><br>
                <input type="checkbox" name="" id="selCheckboxes">
                <label for="selCheckboxes">MARCAR/DESMARCAR:</label>

                <div class="deptos mt-4">
                    @foreach ($departamentos as $depto)
                        <div class="depto d{{$depto->id}}">
                            <input type="checkbox" name="deptos[]" value="{{$depto->id}}" id="depto{{$depto->id}}" class="mb-3">
                            <label for="depto{{$depto->id}}">{{$depto->nombre}}</label><br>
                        </div>
                    @endforeach
                </div>
                <button class="button-style center">IMPRIMIR</button>
                <input type="hidden" name="idPeriodo" id="idPeriodo">
            </form>
        </div>
        </div>
    </div>
</div>
{{-- {{route('parametria.periodosnomina.deptosPeriodo')}} --}}
<script>
$(function(){

    // al abrir el modal cargamos las prestaciones
    $('#imprimirPModal').on('shown.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');
        $('#imprimirPModal .modal-body #idPeriodo').val(id);
        getPeriodDepts(id);
    });

    $('#imprimirPModal #selCheckboxes').click(function(){
        $('#imprimirPModal .deptos input:checkbox:visible').prop('checked', $(this).is(':checked'));
    });

    $('#periodo_imprimir_form').submit(function(e){
        // $('#periodo_imprimir_form .imprimir').attr('disabled', true).text('ESPERE...');
        $('#imprimirPModal').modal('hide');
    });

    function getPeriodDepts(id_periodo){
        $('#periodo_imprimir_form .deptos .depto').hide();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: "POST",
            url: "{{ route('nomina.deptoperiodo') }}",
            data: {'_token': CSRF_TOKEN, 'id_periodo':id_periodo},
            dataType: 'JSON',
            success: function (response) {
                if(response.ok == 1) {
                    response.deptos.forEach(element => {

                        $('#periodo_imprimir_form .deptos .d' + element.id_departamento).fadeIn();
                    });
                } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                }
            }
        });
    }

});
</script>
