<div class="modal" tabindex="-1" role="dialog" id="feriadoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> día feriado</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="friados_form" action="{{route('herramientas.festivoscrearEditar')}}">
                @csrf

                <table width="100%">
                    <tr>
                        <td>
                            <label for="">Motivo:</label>
                        </td>
                        <td>
                            <input type="text" name="motivo" id="motivo" class="form-control mb-3" required>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="">Fecha:</label></td>
                        <td>
                            <input type="text" name="fecha_festiva" id="fecha_festiva" class="form-control mb-3 datepicker" required>
                        </td>
                    </tr>

                </table>
                <input type="hidden" name="idFeriado" id="idFeriado" value="">
                <input type="hidden" name="id_horario" id="id_horario" value="{{$id_horario}}">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn button-style guardar mt-4">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<link href="{{asset('css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
<script src="{{asset('js/moment/moment.js')}}"></script>
<script src="{{asset('js/moment/es.js')}}"></script>
<script src="{{asset('js/datetimepicker/bootstrap-datetimepicker.min.js')}}"></script>
<script>
$('#fecha_festiva').datetimepicker({format: 'YYYY-MM-DD'});

$(function(){

    // al abrir el modal cargamos los datos
    $('#feriadoModal').on('shown.bs.modal', function (e) {
        var idFeriado = $(e.relatedTarget).data('id');
        if(idFeriado == ''){
            accionLabel = 'Crear';
            var motivo = fecha = '';
        } else {
            accionLabel = 'Editar';
            var motivo = $(e.relatedTarget).data('motivo');
            var fecha = $(e.relatedTarget).data('fecha');
        }

        $('#feriadoModal .modal-title span').text(accionLabel);
        $('#feriadoModal #motivo').val(motivo);
        $('#feriadoModal #fecha_festiva').val(fecha);
        $('#feriadoModal #idFeriado').val(idFeriado);

    });
});
</script>