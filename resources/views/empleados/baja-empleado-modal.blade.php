<div class="modal" tabindex="-1" role="dialog" id="bajaEmpleadosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body row pb-5">

                <div class="col-md-12 text-center mt-3">
                    <h5 class="font-weight-bold">Baja de Empleado</h5>
                </div>

                <div class="col-md-12 text-center">
                    <img src="{{ asset('/img/baja-empleado.png') }}" width="50px"> 
                    <div class="nombreempleado font-weight-bold my-3"></div>
                </div>
                <div class="col-md-12 mt-3">
                    {{-- {{route('empleados_bck.baja')}} --}}
                    <form method="post" action="{{ route('empleados.bajaemp') }}" class="bajaEmpleadoForm">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="correo" id="correo">
                        <label class="mb-0">Fecha de Baja:</label>
                        <input type="date" name="fecha_baja" id="fecha_baja" value="{{date('Y-m-d')}}" class="form-control mb-3" min="" required>

                        <label class="mb-0">Motivo de la Baja:</label>
                        <SELECT class="form-control mb-3" id="causa_baja" name="causa_baja" required >
                            <option value="">SELECCIONAR</option>
                            <option value="TERMINO DE CONTRATO">TERMINO DE CONTRATO</option>
                            <option value="SEPARACION VOLUNTARIA">SEPARACION VOLUNTARIA</option>
                            <option value="ABANDONO DE EMPLEO">ABANDONO DE EMPLEO</option>
                            <option value="DEFUNCION">DEFUNCION</option>
                            <option value="CLAUSURA">CLAUSURA</option>
                            <option value="AUSENTISMO">AUSENTISMO</option>
                            <option value="RESCISION DE CONTRATO">RESCISION DE CONTRATO</option>
                            <option value="JUBILACION ">JUBILACION </option>
                            <option value="PENSION">PENSION</option>
                            <option value="OTRA">OTRA</option>
                        </SELECT>
                        <input type="text" name="causa_baja2" id="causa_baja2" class="form-control mb-3 d-none" placeholder="Especifique el motivo">


                        <label class="mb-0 mt-2">Causa Baja Oficial:</label>
                        <input type="text" name="causa_baja_oficial" class="form-control mb-3">

                        <label class="mb-0">¿Desea Generar Finiquito?</label>
                        <SELECT name="finiquito" id="finiquito" onChange="encuesta(this.val)" class="form-control mb-3">
                            <option value="1">SI</option>
                            <option value="0">NO</option>
                        </SELECT>

                        <div class="d-none mb-3 encuesta">
                            <label class="mb-0">Encuesta de Salida</label>
                            <SELECT name="encuesta" class="form-control mb-3">
                                <option value="">Sin encuesta</option>
                                <option value="correo">Enviar por Correo Encuesta</option>
                                <option value="generar">Generar en este Momento</option>
                            </SELECT>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel  mb-3">Cancelar</a>
                                <button type="submit" class="btn button-style  mb-3 guardar">Guardar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nombreempleado{
        font-size: 14px;
    }
</style>
<script>
$(function(){

    $('.bajaEmpleadoForm').validate();

    // al abrir el modal cargamos las prestaciones

});
</script>

<script type="text/javascript">
    $('#bajaEmpleadosModal').on('shown.bs.modal', function (e) {
        var nombreempleado = $(e.relatedTarget).data('nombreempleado');
        $('#bajaEmpleadosModal .modal-body .nombreempleado').text('Baja de: ' + nombreempleado.trim());

        var fecha_alta = $(e.relatedTarget).data('fecha_alta');
        $('#bajaEmpleadosModal .modal-body #fecha_baja').attr('min', fecha_alta); 

        var id = $(e.relatedTarget).data('id');
        $('#bajaEmpleadosModal .modal-body #id').val(id);

        var correo = $(e.relatedTarget).data('correo');
        $('#bajaEmpleadosModal .modal-body #correo').val(correo); 
    });

    $('#causa_baja').change(function(){
        if($(this).val() == 'otra'){
            $('#causa_baja2').removeClass('d-none');
            $('#causa_baja2').focus();
            $("#causa_baja2").prop('required',true);
        } else { 
            $('#causa_baja2').addClass('d-none');
            $("#causa_baja2").prop('required',false);
            $('#causa_baja2-error').hide();
        }
    });

    $('#finiquito').change(function(){
        if($(this).val() == 0){
            $('.encuesta').removeClass('d-none');
        } else { 
            $('.encuesta').addClass('d-none');
        }
    });

    $('.bajaEmpleadoForm').submit(function(){
        $('#bajaEmpleadosModal .modal-body .btn.guardar').attr('disabled', true).text('ESPERE...');
    });
</script>
