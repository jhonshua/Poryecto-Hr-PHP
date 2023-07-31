<div class="modal" id="agregar-departamento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          {{-- <h5 class="modal-title" id="nombre_accion">Agregar departamento</h5> --}}
          <h5><label id="nombre_accion"></label></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
            <form action="{{route('parametria.departamentos.crearEditar')}}" method="POST" id="form" >
                @csrf
                <div class="form-group">
                  <label for="recipient-name" class="col-form-label">Nombre departamento:</label><br>
                  <input type="text" name="nombre" id="nombre" class="input-style-custom w-100" placeholder="Ingresa un nombre"  required>
                  <input type="hidden" name="id" id="id">
                </div>
            </form>
        </div>
        <div class="modal-footer" style="display: flex; justify-content:center;">
          <button type="button" class="button-cancel-style" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="button-style guardar">Guardar</button>
        </div>
      </div>
    </div>
</div>