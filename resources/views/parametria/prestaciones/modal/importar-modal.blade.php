<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> Importar prestaciones para: {{$prestacion->nombre}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form method="post" id="addform" action="{{route('parametria.prestaciones.importar')}}" enctype="multipart/form-data" >
                    @csrf
                    <p class="text-danger font-weight-bold" > Advertencia : Favor de tomar el layout de ejemplo, verificar que el documento venga sin fórmulas, filtros, estilos o celdas de más etc ...!!  </p>
                    <label for="">Archivo a importar:</label>
                    <div class="custom-file ">
                        <input type="file" name="prestaciones_file" id="prestaciones_file" required accept=".xlsx, .xls" class="custom-file-input"  required/>
                        <label class="custom-file-label" >Seleccionar Archivo</label>
                    </div> 
                    <br>
                    <br>
                    <a href="{{asset("storage/templates/prestaciones_ejemplo.xlsx")}}" class="text-warning mt-2" target="_blank"><i class="fas fa-table"></i> Layout ejemplo de archivo de prestaciones</a>
                    <br>
                    <br>
                    <div style="display: flex; justify-content:center;">
                    <div class="button-style w-15" id="importar-excel">Importar</div>
                    <input type="hidden" name="id_categoria" id="id_categoria" value="{{ Crypt::encrypt($prestacion->id)}}" required>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
