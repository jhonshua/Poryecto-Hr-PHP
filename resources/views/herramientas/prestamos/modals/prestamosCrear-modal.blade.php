<div class="modal" id="crearSolicitudModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Crear solicitud</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ Route('prestamos.crea') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    @csrf
                    <div class="form-group">
                        <label for="">Seleccionar empresa: </label>
                        <select name="base" id="base" class="form-control" required>
                            <option value="">Selecciona una empresa...</option>
                            @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->base }}" {{ ($empresa->base == Session::get('base')) ? 'selected' : ''}}>{{ $empresa->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Selecciona tipo de beneficio: </label>
                        <select name="tipo_prestamo" id="tipo_prestamo" class="form-control" required>
                            <option value="">Selecciona el tipo de prestamo</option>
                            
                            @foreach ($prestamos_tipos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                </div>
                <div class="col-md-12 mt-2 mb-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn button-style btn-sm btndeshabilita">Guardar </button>
                </div>
            </form>
        </div>
    </div>
</div>