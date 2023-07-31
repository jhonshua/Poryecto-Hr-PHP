<div class="modal" id="modalAgregarEliminarCampoExt" tabindex="-1" role="dialog" aria-labelledby="eliminarCampoExt-archivo" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Crear campo extra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('asignaActivo.creaEliminaCampo')}}" method="get">
                <div class="modal-body">
                    <input type="hidden" name='id_campo_ext' id="id_campo_ext">
                    <input type="hidden" name='id_activo' id="id_activo">
                    <div class="alert alert-danger" role="alert" id="custom-p-camp"></div>
                    <div id="agregarCampos">
                        <div class="form-group">
                            <label>Nombre label (*)</label>
                            <input type="text" name="nombre_label_add" id="nombre_label_add" class="form-control" placeholder="Agrega una etiqueta" required>
                        </div>
                        <div class="form-group">
                            <label>Valor (*)</label>
                            <input type="text" class="form-control" name="valor_add" id="valor_add" placeholder="Ingresa un valor" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3 mb-2 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn button-style btn btn-warning btn-sm btndeshabilita">Guardar </button>
                </div>
            </form>
        </div>
    </div>
</div>