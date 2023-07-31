<div class="modal" tabindex="-1" role="dialog" id="cargarAcuseEmpleadosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cargar Acuse para: <span></span></h5>
            </div>
            <div class="modal-body row">
                <div class="col-md-10 offset-md-1 text-center mt-3">
                    {{-- {{route('empleados_bck.cargarAcuse')}} --}}
                    <form method="POST" action="{{ route('empleados.cargaracuse') }}" enctype="multipart/form-data" id="acuseForm">

                        @csrf
{{--                         <div class="form-group">
                            <input class="form-control" type="file" name="file_acuse" accept=".pdf" required>
                            <input type="hidden" name="id" id="id">
                        </div>
 --}}
                        <div class="custom-file input-style mb-2">
                            <input type="file" class="custom-file-input" name="file_acuse"  onchange="file('file_acuse')" id="file_acuse" accept=".pdf" required>
                            <label class="custom-file-label" for="ine" id="file_acuse_text">Archivo .pdf</label>
                            <input type="hidden" name="id" id="id">
                        </div>
                        <br><br>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                                <button type="submit"  class="btn button-style  enviar mb-3">Enviar</button>
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



    // al abrir el modal cargamos las variables
    $('#cargarAcuseEmpleadosModal').on('shown.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');
        var nombreempleado = $(e.relatedTarget).data('nombreempleado');

        $('#cargarAcuseEmpleadosModal .modal-title span').text(nombreempleado.trim());
        $('#cargarAcuseEmpleadosModal .modal-body #id').val(id);
    });

    // Validar
    $( "#acuseForm" ).validate({
        ignore: [],
        submitHandler: function(form) {
            form.submit();
            $('#acuseForm .btn.enviar').text('Espere...');
            $('.btn').attr('disabled', true);
        },
    });

});

    function file(val){

        var text = val+"_text";
        document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
    }

</script>
