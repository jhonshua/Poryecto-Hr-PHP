<!-- Modal -->
<div class="modal fade" id="importarPuestos" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Importar puestos con alias</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="importar_puestos" action="{{route('parametria.puestos.reales.importar')}}" enctype="multipart/form-data">
          @csrf
          <label for="">Archivo a importar:</label>        
          <div class="custom-file ">
              <input type="file" class="custom-file-input " name="file_puesto" accept=".xlsx, .xls" required>
              <label class="custom-file-label" for="isr_file" id="archivo_text">Archivo</label>
          </div>
          <br>
          <br>                
          <a href="{{asset('storage/templates/puestos_reales.xlsx')}}" class="text-success mt-2" target="_blank"><i class="fas fa-table"></i> Layout ejemplo de archivo de puestos con alias</a>
          <br>
          <div class="row">
              <div class="col-md-12 mt-3 text-center">
                  <button type="button" class="button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                  <button type="button" class="button-style" id="importarBtn">Importar</button>
              </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="{{asset('js/parsley/parsley.min.js')}}"></script>
<!-- Cambiar idioma de parsley -->
<script src="{{asset('js/parsley/i18n/es.js')}}"></script>
<script>
  $('#importarBtn').click(function(){
             
    let form = $("#importar_puestos");
    if(form.parsley().isValid()){
        $(this).text('Espere...');
        $(this).prop('disabled', true);
        form.submit();
    }else{
        form.parsley().validate();
    }
  });
</script>