<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> Importar impuestos ISR</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="importar_isr_form" action="{{route('parametria.isr.importar')}}" enctype="multipart/form-data">
                @csrf
                <label for="">Archivo a importar:</label>        
                <div class="custom-file form-control input-style-custom custom-file-container mb-3">
                    <input type="file" class="custom-file-input " name="isr_file" onchange="file('isr_file')" id="isr_file" accept=".xlsx, .xls" required>
                    <label class="custom-file-label" for="isr_file" id="archivo_text">Archivo</label>
                </div>
                <br>                
                <a href="{{asset('storage/templates/isr_ejemplo.xlsx')}}" class="text-success mt-2" target="_blank"><i class="fas fa-table"></i> Layout ejemplo de archivo de isr</a>
                <br>

                <div class="row">
                    <div class="col-md-12 mt-3 text-center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="button" class="btn button-style importarBtn">Importar</button>
                    </div>
                </div>

            </form>
        </div>
        </div>
    </div>
</div>

<script>
    $(function(){    
        $('#importarModal .importarBtn').click(function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            if($('#isr_file').val() == '') {         
                swal("", "Debes seleccionar un archivo para poder continuar.");      
            }else{
                swal({
                    title: "Aviso Importante",
                    text: `Al importar esta información se borrarán "TODOS" los datos existentes siendo sustituidos por los nuevos. ¿Deseas continuar?`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete){
                        $('#importarModal .importarBtn').text('Espere...');
                        $('#importarModal #importar_isr_form').submit();
                    }                                    
                });  
            }
        });
    });
</script>

