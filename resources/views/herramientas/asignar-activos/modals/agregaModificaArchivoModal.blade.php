<div class="modal" id="modalModificarArchivo" tabindex="-1" role="dialog" aria-labelledby="modificar-archivo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title"><span id="etiqueta_archivo"></span> archivo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('asignaActivo.creaModArchivo')}}" method="post" enctype="multipart/form-data" id="formaddmod">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name='id_activo' id="id_activoel_mod">
                    <input type="hidden" name='id_archivo' id="id_archivoel_mod">
                    <div class="alert alert-danger" role="alert" id="custom-p-mod"></div>
                    <label>Nombre del archivo:</label>
                    <input type="text" name="nombre_archivo" id="nombre_archivo" class="form-control" placeholder="Escribe un nombre para el archivo" required>
                    <div class="file rounded d-flex  btn button-style mt-3">

                        <label class="name tooltip_" data-toggle="tooltip" title="" for="file_archivo" aria-describedby="ui-id-14"><a>Elegir archivo</a></label>
                        <input type="file" name="file_archivo" id="file_archivo" class="invisible" accept=".pdf, .png, .jpg, .jpeg, .gif , .doc, .docx" style="display: none;">
                    </div>
                </div>

                <div class="col-md-12 mt-3 mb-2 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal" id="cerrar-modal-mod">Cerrar</button>
                    <button type="submit" class="btn button-style btn btn-warning btn-sm btndeshabilita">Guardar </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function() {
        let accionLabel = '';

        $('#modalModificarArchivo').on('shown.bs.modal', function(e) {
            let accion = $(e.relatedTarget).data('etiqueta_archivo');
            if (accion == 'Crear') {
                accionLabel = 'Crear'

            } else {
                accionLabel = 'Editar';

            }

            $('#categoriasModal .modal-title span').text(accionLabel);

        });
    });
</script>