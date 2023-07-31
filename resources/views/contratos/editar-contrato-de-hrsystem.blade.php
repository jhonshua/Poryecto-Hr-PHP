<div class="modal" tabindex="-1" role="dialog" id="editarcontratoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Editar contrato de HRSystem</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="modal-body p-4">
          <div class="row">
            {{-- <div class="col m2 s12"></div> --}}
            <div class="col m12 s12">
              <form method="post" id="submit_contrato_upd" action="{{ route('contrato.actualizarcontrato') }}" enctype="multipart/form-data">
                @csrf


                <input type="hidden" name="id" id="id_contrato_upd" value="">
                
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre_upd" class="form-control  input-style-custom mb-3" value="" required placeholder="Nombre">
                    <label for="alias">Alias:</label>
                    <input type="text" name="alias" id="alias_upd"  class="form-control  input-style-custom mb-3" value="" required
                    placeholder="alias">

                    <input type="hidden" name="file_db" id="fiel_upd" value="">

                    <div class="custom-file form-control  input-style-custom mb-3">|
                        <label for="alias">Archivo:</label>
                        <input type="file" class="custom-file-input " name="archivo" onchange="file('archivo_upd')" id="archivo_upd" accept=".pdf, .png, .jpg, .doc, .docx" required>
                        <label class="custom-file-label" for="archivo_upd" id="archivo_upd_text">Archivo</label>
                    </div>

                    <label for="tipo_upd">Tipo:</label>
                    <select name="tipo" id="tipo_upd" class="form-control  input-style-custom mb-3" required>
                      <option value="" disabled >Tipo</option>
                        <option value="D">DETERMINADO</option>
                        <option value="I">INDETERMINADO</option>
                    </select>

                    <label for="temporalidad_upd">Temporalidad:</label>
                    <select name="temporalidad" id="temporalidad_upd" class="form-control  input-style-custom mb-3" required>
                      <option value="" disabled >Temporalidad</option>
                        <option value="D">DÍAS</option>
                        <option value="M">MESES</option>
                    </select>
              </form>

                <div class="row">
                    <div class="col m12 s12 mt-3 text-center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="button" class="btn button-style" id="update_contrato">Guardar</button>
                    </div>
                </div>
            </div>
            {{-- <div class="col m2 s12"></div> --}}
          </div>

        </div>
        </div>
    </div>
</div>




<script type="text/javascript">
  function file(val){

    var text = val+"_text";
    document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;

  }

    $("#update_contrato").click(function(){
      var name = document.getElementById("nombre_upd").value;
        var alias = document.getElementById("alias_upd").value;
        var tipo = document.getElementById("tipo_upd").value;
        var temporalidad = document.getElementById("temporalidad_upd").value;

        if(alias== "" ||  tipo == "" || temporalidad == "" || name== ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          });
          setTimeout(submitupdate, 1500);
        }
    });

    function submitupdate() { document.getElementById("submit_contrato_upd").submit() }

</script>
