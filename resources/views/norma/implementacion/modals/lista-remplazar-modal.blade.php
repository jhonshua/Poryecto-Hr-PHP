<div class="modal" tabindex="-1" role="dialog" id="remplazarListaModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Remplazar empleado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="trabajadores_editar_form" role="form">
                    @csrf
                    <input type="hidden" value="{{$id_periodo_norma}}" name="normaEditar" id="normaEditar" />
                    <input type="hidden" name="idEmpleadoEdit" id="idEmpleadoEdit" />
                    <input type="hidden" name="generoEdit" id="generoEdit" />
                    <input type="hidden" value="{{$datosImplementacion->id}}" name="implementacion_remplazar" id="implementacion_remplazar" />
                    <div id="divTrabajadoresEditar"></div>
                    <div class="form-row" id="botonesEditar"><br/><br/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

