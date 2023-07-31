<div class="modal" tabindex="-1" role="dialog" id="importarPrenominaModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Importar prenómina</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" id="form" action="{{route('procesos.periodos.nomina.prenomina.importar')}}"  enctype="multipart/form-data">
                    @csrf
                    <p class="text-danger font-weight-bold" > Advertencia : Favor de tomar el layout de ejemplo, verificar que el documento venga sin fórmulas, filtros, estilos o celdas de más etc ...!!  </p>
                    <label for="">Archivo a importar:</label>
                    <div class="custom-file ">
                        <input type="file"name="prenomina"  required accept=".xlsx, .xls" class="custom-file-input"  required/>
                        <label class="custom-file-label" >Seleccionar Archivo</label>
                    </div> 
                    <br>
                    <br>
                    <a href="{{route('procesos.periodos.nomina.prenomina.exportar',Crypt::encrypt($periodo->id))}}" class="text-warning mt-2" target="_blank"><i class="fas fa-table"></i> Visualizar layout de ejemplo</a>
                    <br>
                    <br>
                    <div class="button-style w-15 importar center">Importar</div>
                    <input type="hidden" name="id_periodo" id="id_periodo" value="{{Crypt::encrypt($periodo->id)}}">
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('#importar_prenomina_form').submit(function(){
    $('#importar_prenomina_form .importar').attr('disabled', true).text('ESPERE...')
});
</script>
@endpush
