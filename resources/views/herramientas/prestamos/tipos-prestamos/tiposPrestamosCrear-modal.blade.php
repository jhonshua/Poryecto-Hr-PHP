<div class="modal" id="modalAgregarTipo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Crear tipo de préstamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('tiposPrestamos.crea')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="agregarCampos">



                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre del tipo de préstamo" required>
                        </div>
                        <div class="form-group">
                            <label>Estatus:</label>
                            <select name="estatus" id="estatus" class="form-control" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Antigüedad en meses:</label>
                            <select name="antiguedad_meses" id="" class="form-control" required>
                                <option value="">Selecciona una opción...</option>
                                @for ($i = 0; $i < 13; $i++) <option value="{{ $i }}">
                                    {{ $i }}
                                    </option>
                                    @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Tipo de solicitud:</label>
                            <select name="tipo_solicitud" id="" class="form-control" required>
                                <option value="">Selecciona una opción...</option>
                                <option value="1">Solicitud</option>
                                <option value="2">Prestamo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descripción del préstamo:</label>
                            <textarea class="form-control" name="descripcion" id="" rows="3" placeholder="Descripción del tipo de préstamo"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Notas:</label>
                            <textarea class="form-control" name="notas" id="" rows="3" placeholder="Notas hacia el usuario"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-2 mb-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn button-style btn btn-warning btn-sm btndeshabilita">Guardar </button>
                </div>
            </form>
        </div>
    </div>
</div>