<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> Importar días feriados</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="importar_feriados_form" action="{{route('herramientas.importar')}}" enctype="multipart/form-data">
                @csrf
                <label for="">Archivo a importar:</label>
                <input type="file" name="feriados_file" id="feriados_file" required accept=".xlsx, .xls">
                <br>
                <a href="{{ asset('storage/templates/dias_feriados_ejemplo.xlsx')}}" class="text-success mt-2" target="_blank"><i class="fas fa-table"></i> Layout ejemplo de archivo de Días feriados</a>
                <br>
                <button type="button" class="btn btn-warning importarBtn">Importar</button>
                <input type="hidden" name="id_horario" id="id_horario" value="{{$id_horario}}">
            </form>
        </div>
        </div>
    </div>
</div>


<script>
$('.importarBtn').click(function(e){
    
    e.preventDefault();
    var id = $(this).attr('id');
    if($('#isr_file').val() == '') {
        swal("Aviso!", "Debes seleccionar un archivo para poder continuar.", "warning");
        return;
    }
    swal({
        title: "Aviso Importante",
        text: "Al importar esta información se borrarán TODOS los días feriados existentes de este horario  siendo sustituidos por los nuevos. ¿Deseas continuar?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            swal("La información esta siendo procesada", {
                icon: "success",
            });
            $('#importarModal #importar_feriados_form').submit();
        } else {
            swal("La acción fue cancelada");
        }
    });

});
</script>