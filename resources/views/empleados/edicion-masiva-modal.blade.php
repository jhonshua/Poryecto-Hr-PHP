<div class="modal" tabindex="-1" role="dialog" id="edMasivaModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edición Masiva de empleados</h5>
            </div>
            <div class="modal-body row pb-2">
                <div class="col-md-12 mt-3">

                    <div class="paso1">
                        <label class="mb-0">Selecciona el Campo a Editar:</label>
                        <SELECT class="input-style center mb-3 campo" name="campo" required >
                            <option value="sNeto">Sueldo Neto</option>
                            <option value="sDiario">Salario Diaro Integrado</option>
                        </SELECT>
                        
                        <div class="col-md-12 mt-3 text-center">

                            <button type="button" class="btn button-style-cancel regresar" data-dismiss="modal" aria-label="Close">Cerrar</button>
                            <button type="submit" class="btn button-style continuar" >Guardar</button>

                        </div>

                    </div>

                    <div class="col-md-12 text-center paso2">
                        <a href="{{route("empleados.edmasiva", "sNeto")}}" class="text-dark layout mb-3" target="_blank">
                            <i class="fas fa-download fa-2x text-dark mt-3"></i><br>
                            Descargar el layout con la informacion de los empleados:
                        </a>
                        <button type="button" class="btn button-style regresar">REGRESAR</button>
                        <a href="{{route("empleados.edmasiva", "sNeto")}}" class="layout btn btn-secondary font-weight-bold my-4" target="_blank">
                        DESCARGAR</a>
                        <button type="button" class="btn button-style continuar">CONTINUAR</button>
                    </div>

                    <form method="post" action="{{ route('empleados.editmasiva') }}" class="edMasivaForm paso3" enctype="multipart/form-data">
                        @csrf
                        <label for="">Subir archivo con las modificaciones hechas:</label>
                        <input type="file" name="archivo" id="archivo" required class="form-control" accept=".xls, .xlsx">
                        <input type="hidden" name="tipo" class="tipo" value="sNeto">

                        <div class="row">
                            <div class="col-md-12 mt-3 text-center">

                                <button type="button" class="btn button-style-cancel regresar" data-dismiss="modal" aria-label="Close">Regresar</button>
                                <button type="submit" class="btn button-style guardar" id="edit_registro">Guardar</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .paso2, .paso3{ display: none;}
</style>

{{-- {{route("empleados.edmasiva", "**tipo**")}} --}}
{{-- {{route("empleados_bck.edicionMasivaLayout", "**tipo**")}} --}}
<script>
$(function(){

    // al abrir el modal 
    $('#edMasivaModal').on('shown.bs.modal', function (e) {
        $('#edMasivaModal .paso1').show();
        $('#edMasivaModal .paso2, #edMasivaModal .paso3').hide();
    });

    $('#edMasivaModal .paso1 .continuar').click(function(){
        $('#edMasivaModal .paso1').fadeOut('fast', function(){
            $('#edMasivaModal .paso2').fadeIn();
        });
    });

    $('#edMasivaModal .paso2 .continuar').click( function(){
        $('#edMasivaModal .paso2').fadeOut('fast', function(){
            $('#edMasivaModal .paso3').fadeIn();
        });
    });

    $('#edMasivaModal .paso2 .regresar').click(function(){
        $('#edMasivaModal .paso2').fadeOut('fast', function(){
            $('#edMasivaModal .paso1').fadeIn();
        });
    });

    $('#edMasivaModal .paso3 .regresar').click( function(){
        $('#edMasivaModal .paso3').fadeOut('fast', function(){
            $('#edMasivaModal .paso2').fadeIn();
        });
    });

    $('.paso1 .campo').change(function(){
        url = '{{route("empleados.edmasiva", "**tipo**")}} ';
        url = url.replace('**tipo**', $(this).val());
        $('.paso2 .layout').attr('href', url);
        $('.paso3 .tipo').val($(this).val());
    });


    

    $('.edMasivaForm').submit(function(){
        $('#edMasivaModal .modal-body .btn.guardar').attr('disabled', true).text('ESPERE...');
    });

    $('.paso2 .layout').click(function(){
            swal({
                    title: "NO CAMBIAR EL NOMBRE DE LOS ENCABEZADOS DE CADA COLUMNA al subir nuevamente el layout con la informacion corregida.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })

    });
});
</script>
