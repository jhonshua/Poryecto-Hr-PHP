<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar datos de Asistencias</h5>
            </div>
            <div class="modal-body row pb-2">
                <div class="col-md-12 mt-3">
                    <form method="post" action="{{route('empleado.asistencias.importar')}}" class="importarForm mt-3" enctype="multipart/form-data" id="form-import">
                        @csrf
                        <div class="custom-file ">
                            <input type="file" name="archivo" class="custom-file-input" accept=".xls, .xlsx" required/>
                            <label class="custom-file-label" >Seleccionar Archivo</label>
                        </div> 
                        <br>
                        <br>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                                <button type="submit" class=" button-style importar mb-3 btn">Importar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>