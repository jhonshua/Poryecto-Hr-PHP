<div class="modal" id="anexarDocModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title">Anexar documentos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('generales.anexaDoc')}}" method="post" enctype="multipart/form-data">
                @csrf

                <table class="table" id="tblinfo" >
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Documentos por defecto</th>
                                    <th><div class="btn btn-warning btn-sm"  onclick="addDocumentos()" ><li class="fas fa-plus"></li></div></th>     
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                <input type="hidden" name="id" value="{{ $idGeneral }}">
                <input type="hidden" class="form-control" name="idrequisito" value="{{ $id }}">
                <div class="col-md-12 mt-2 mb-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn button-style btn btn-warning btn-sm guardar">Guardar </button>
                </div>
            </form>
        </div>
    </div>
</div>