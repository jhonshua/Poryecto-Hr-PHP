<div class="modal" tabindex="-1" role="dialog" id="importarModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span></span> Importar Subsidios</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body p-4">

            <form method="post" id="importar_subsidios_form" action="{{route('parametria.subsidio.importar')}}" enctype="multipart/form-data">
                @csrf
                <label for="">Archivo a importar:</label>            
                <div class="custom-file form-control input-style-custom custom-file-container mb-3">
                    <input type="file" class="custom-file-input " name="subsidios_file" onchange="file('subsidios_file')" id="subsidios_file" accept=".xlsx, .xls" required>
                    <label class="custom-file-label" for="subsidios_file">Archivo</label>
                </div>
                <br>
                <a href="{{ asset('storage/templates/subsidios_ejemplo.xlsx')}}" class="text-success mt-2" target="_blank"><i class="fas fa-table"></i> Layout ejemplo de archivo de subsidios</a>
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
$('#importarModal .importarBtn').click(function(e){
    e.preventDefault();
    let id = $(this).attr('id');
    if($('#subsidios_file').val() == '') {
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
                $('#importarModal #importar_subsidios_form').submit();
            }                                    
        });
    }
});
</script>

