<!-- Modal -->
<div class="modal fade" id="comprobanteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold " id="exampleModalLabel">Ingrese comprobante de vacunación </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{route('empleados.comprobante.vacunacion')}}" enctype="multipart/form-data" id="addform" >
                @csrf
                <div class="form-group">
                    <input type="hidden" class="form-control input-style-custom" name="id_empleado"  value="{{$emp->id}}" >
                  </div>
                <div class="form-group">
                  <label>Tipo de vacuna:</label>
                  <input type="text" name="tipo_vacunacion" id="tipo_vacunacion" class="form-control input-style-custom"  placeholder="Ejemplo Sputink V " required >
                </div>
                <div class="form-group">
                    <label>Redacte si hubo reacciones:</label>
                    <textarea name="reacciones" id="reacciones" class="form-control input-style-custom" cols="30" rows="10" placeholder="Describa si hubo alguna reacción durante la vacuna " required ></textarea>
                </div>
                <div class="custom-file ">
                    <input type="file" name="file" class="custom-file-input" accept=".jpg,.jpeg,.pdf" required >
                    <label class="custom-file-label" >Cargar comprobante de vacunación</label>
                </div> 
            </form>
        </div>
        <br>
        <div class="text-center">
          <button type="button" class="button-cancel-style" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="button-style" id="guardar_comprobante" >Guardar</button>
        </div>
        <br>
      </div>
    </div>
</div>
<script src="{{asset('js/parsley/parsley.min.js')}}"></script>
<!-- Cambiar idioma de parsley -->
<script src="{{asset('js/parsley/i18n/es.js')}}"></script>
<script>
    $(document).ready(function() {
        $("#modalComprobante").on('click',function(){
            $("#comprobanteModal").modal("show");
        });

        $("#guardar_comprobante").on('click',function(e){
            e.preventDefault();
            let form = $("#addform");
            (form.parsley().isValid()) ?  $('#addform').submit() : form.parsley().validate();
        });

        $(document).on('change','input[type="file"]',function(){
                
            let fileName = this.files[0].name;
            if(fileName !=""){
                let ext_archivo = fileName.split('.').pop();
                ext_archivo = ext_archivo.toLowerCase();
                let extension = ['jpeg','jpg','pdf'];
            
                if(!extension.includes(ext_archivo)){
                
                    this.value = '';
                    swal("El archivo no es valido , intentalo nuevamente !", {
                        icon: "error",
                    });   
                }
                
            }else{
                swal("No se a seleccionado ningún archivo !", {
                    icon: "error",
                });    
            }
        });

    })
</script>