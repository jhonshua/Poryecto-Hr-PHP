<div class="modal" id="requisitoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        @php

        foreach($prestamo as $presta)
        {
        $idPrestamo =($presta->id);
        }
        @endphp
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Crear requisito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('requisitos.crea')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div>
                        <div class="form-group">

                            <input type="hidden" name="prestamos_tipos_id" id="prestamos_tipos_id" value="{{$idPrestamo}}">
                            <label>Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre del requisito" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="text">Texto</option>
                                <option value="file">Archivo</option>
                                <option value="info">Informativo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Valor:</label>
                            <input type="text" name="valor" id="valor" class="form-control" placeholder="Valor por default a mostrar" maxlength="254" autocomplete="off" required>
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