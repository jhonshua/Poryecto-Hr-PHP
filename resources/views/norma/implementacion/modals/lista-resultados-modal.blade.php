<div class="modal" tabindex="-1" role="dialog" id="resultadosListaModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Resultados empleado</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="trabajadores_resultados_form">
                    @csrf
                    <input type="hidden" value="" name="idEmpleadoResultados" id="idEmpleadoResultados" />
                    <div id="divTrabajadoresResultados"></div>
                </form>
            </div>
        </div>
    </div>
</div>