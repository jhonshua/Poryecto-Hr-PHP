<div class="modal" tabindex="-1" role="dialog" id="nuevaListaModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Lista de empleados </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="trabajadores_sede" role="form">
                    @csrf
                    <input type="hidden" name="sede" id="sede" value="{{$datosImplementacion->sede}}">
                </form>
                <div id="contenedorLista"></div>
            </div>
        </div>
    </div>
</div>
