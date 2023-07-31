<div class="modal" tabindex="-1" role="dialog" id="categoriasModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> categoria de activos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <form action="{{ route('categoriaActivo.creaMod') }}" method="post" id="addform">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <label for="">Nombre del activo</label>
                    <input type="text" class="form-control mb-2" name="nombre_activo" id="nombre_activo" placeholder="Agrega una categoria" required>
                    <br>
                    <label for="">Estado de la categoria</label>
                    <div class="form-check mb-2">
                        <select name="estatus" id="estatus" class="form-control input-style-custom select-clase mb-2" style="width: 100%!important;" required>
                            <option value="">SELECCIONE</option>
                            <option value="0">Inactivo</option>
                            <option value="1">Activo</option>

                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 text-center">
                            <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                            <button type="submit" class="btn button-style guardar">Guardar</button>
                        </div>
                    </div>

                </form>
            </div>

            <script>
                $(function() {
                    let accionLabel = '';
                    let nombre_activo = '';
                    let estatus = '';

                    $('#categoriasModal').on('shown.bs.modal', function(e) {
                        let id = $(e.relatedTarget).data('id');
                        if (id == '') {
                            accionLabel = 'Crear'
                            nombre_activo = '';
                            estatus = '';
                        } else {
                            accionLabel = 'Editar';
                            nombre_activo = $(e.relatedTarget).data('nombre_activo');
                            estatus = $(e.relatedTarget).data('estatus');
                        }

                        $('#categoriasModal .modal-title span').text(accionLabel);
                        $('#categoriasModal .modal-body #id').val(id);
                        $('#categoriasModal .modal-body #nombre_activo').val(nombre_activo);
                        $('#categoriasModal .modal-body #estatus').val(estatus);
                    });
                });
            </script>