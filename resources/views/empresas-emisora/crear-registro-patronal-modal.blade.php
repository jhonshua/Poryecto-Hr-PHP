<div class="modal" tabindex="-1" role="dialog" id="crearregistroModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Crear registro patronal</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="modal-body p-4">
        <form method="post" id="submit_empresa" action="{{ route('empresae.agregarregistro') }}">
        @csrf
            <input type="text" name="num_registro_patronal" id="num_registro_patronal" placeholder="Num Registro" class="form-control  input-style-custom mb-3" required>

            <input type="number" name="porcentaje_prima" id="porcentaje_prima" placeholder="% de Prima de Riesgo" class="form-control  input-style-custom mb-3" required step="0.00001">

            <label class="mb-0"></label>
            <select name="tipo_clase" id="tipo_clase" class="form-control  input-style-custom mb-3" required>
                <option value="" disabled selected>Tipo de Clase</option>
                <option value="Clase I">Clase I</option>
                <option value="Clase II">Clase II</option>
                <option value="Clase III">Clase III</option>
                <option value="Clase IV">Clase IV</option>
                <option value="Clase V">Clase V</option>
            </select>

            <input type="text" name="subdelegacion" id="subdelegacion" placeholder="Subdelegacion" class="form-control  input-style-custom mb-3" required>


            <input type="text" name="tipo_documento" id="tipo_documento" placeholder="Tipo de Documento" class="form-control  input-style-custom mb-3" required>

            <input type="hidden" name="id" id="id" value="">
            <input type="hidden" name="id_empresa_emisora" id="id_empresa_emisora" value="">
            {{-- <input type="hidden" name="empresaEmisora" value="{{$empresaEmisora}}"> --}}
        </form>

            <div class="row">
                <div class="col m12 s12 mt-3 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                    <button type="button" class="btn button-style" id="add_registro">Guardar</button>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $("#add_registro").click(function(){

        var num_registro_patronal = document.getElementById("num_registro_patronal").value;
        var porcentaje_prima = document.getElementById("porcentaje_prima").value;
        var tipo_clase = document.getElementById("tipo_clase").value;
        var subdelegacion = document.getElementById("subdelegacion").value;
        var tipo_documento = document.getElementById("tipo_documento").value;

        if(num_registro_patronal== "" || porcentaje_prima == "" || tipo_clase == "" || subdelegacion == "" || tipo_documento == ""){
            swal({
                title: "Para continuar debes agregar la información requerida",
            });
        }else{
            swal("Espere un momento, la información esta siendo procesada", {
                icon: "success",
                buttons: false,
            });
            setTimeout(submitForm, 1500);
        }

    });


    function submitForm() { document.getElementById("submit_empresa").submit() }
</script>
