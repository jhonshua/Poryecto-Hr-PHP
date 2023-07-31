<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar datos de prestaciones extras</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row text-center pb-2">
                <div class="col-md-12 mt-3">                    
                    <a href="{{route('prestaciones.extras.exportar')}}" class="text-success" target="_blank">VER EL LAYOUT DE EJEMPLO <i class="fas fa-table"></i></a> 

                    <form method="post" action="{{route('prestaciones.extras.importar')}}" class="importarForm mt-3" enctype="multipart/form-data">
                        @csrf
                        <label for="">Subir archivo:</label>
                        <!-- <input type="file" name="archivo" id="archivo" required class="form-control" accept=".xls, .xlsx"> -->
                        <div class="custom-file form-control input-style-custom custom-file-container mb-3">
                            <input type="file" class="custom-file-input" name="prestaciones_extras_file" onchange="file(event)" id="prestaciones_extras_file" accept=".xlsx, .xls" required>
                            <label class="custom-file-label text-left" for="prestaciones_extras_file" id="archivo_text">Archivo</label>
                        </div>
                        <div>
                            <div class="col-md-12 mt-3 text-center">
                                <button type="button" data-dismiss="modal" class="button-style-cancel my-4 regresar">Cancelar</button>
                                <button type="submit" class="button-style my-4 guardar">Continuar</button>
                            </div>
                        </div>                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.importarForm').submit( function(){
            $('.importarForm .guardar').attr('disabled', true).text('ESPERE...');
            return true;
        });
    });
    function file(val){       
        let text = val;        
        $('#archivo_text').html(val.target.files[0].name); 
    }
</script>

