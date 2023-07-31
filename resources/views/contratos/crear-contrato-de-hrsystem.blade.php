<div class="modal" tabindex="-1" role="dialog" id="crearcontratoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Crear contrato de HRSystem</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="modal-body p-4">

        <form method="post" id="submit_contrato" action="{{ route('contratos.agregar') }}" enctype="multipart/form-data">
            @csrf

            <input type="text" name="nombre" id="nombre" class="form-control  input-style-custom mb-3" value="{{ old('nombre') }}" required placeholder="Nombre">
            <input type="text" name="alias" id="alias"  class="form-control  input-style-custom mb-3" value="{{ old('alias') }}" required
            placeholder="Alias">


            <div class="custom-file form-control input-style-custom custom-file-container mb-3">
                <input type="file" class="custom-file-input " name="archivo" onchange="file('archivo')" id="archivo" accept=".pdf, .png, .jpg, .doc, .docx" required>
                <label class="custom-file-label" for="archivo" id="archivo_text">Archivo</label>
            </div>


            <select name="tipo" id="tipo" class="form-control  input-style-custom mb-3" required>
                <option value="" disabled selected>Tipo</option>
                <option value="D">DETERMINADO</option>
                <option value="I">INDETERMINADO</option>
            </select>

            <select name="temporalidad" id="temporalidad" class="form-control  input-style-custom mb-3" required>
                <option value="" disabled selected>Temporalidad</option>
                <option value="D">DÍAS</option>
                <option value="M">MESES</option>
            </select>
        </form>



            <div class="row">
                <div class="col m12 s12 mt-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                    <button type="button" class="btn button-style" id="add_contrato">Guardar</button>
                </div>
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

    $("#add_contrato").click(function(){
        var alias = document.getElementById("alias").value;
        var archivo = document.getElementById("archivo").value;
        var tipo = document.getElementById("tipo").value;
        var temporalidad = document.getElementById("temporalidad").value;

        if(alias== "" || archivo == "" || tipo == "" || temporalidad == ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          });
          setTimeout(submitaddcontrato, 1500);
        }
    });

    function submitaddcontrato() { document.getElementById("submit_contrato").submit() }

</script>
