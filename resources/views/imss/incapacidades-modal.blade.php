<div class="modal" tabindex="-1" role="dialog" id="incapacidadModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span></span> Incapacidad</h5>
            </div>
            <form method="post" action="{{ route('incapacidades.actualizar') }}" class="incapacidadForm mt-3 container-fluid">
                <div class="modal-body row pb-2">
                    @csrf
                    <div class="col-md-4 mt-1">
                        <label for="">Fecha de Inicio de la incapacidad: </label>
                        <input type="text" name="fecha_inicio_incapacidad" id="fecha_inicio_incapacidad" required class="form-control mb-2 datepicker input-style-custom" autocomplete="off">
                    </div>

                    <div class="col-md-4 mt-1">
                        <label for="">Fecha de Fin de la incapacidad: </label>
                        <input type="text" name="fecha_fin_incapacidad" id="fecha_fin_incapacidad" required class="form-control mb-4 datepicker input-style-custom" autocomplete="off">
                    </div>
                    
                    <div class="col-md-2 mt-1">
                        <br><label for="inasistencia">Dias:</label>
                        <input type="text" class="form-control input-style-custom" required name="dias" id="dias">
                    </div>

                    <div class="col-md-12 mt-1">
                        <div>
                            <label for="">Clave incapacidad:</label>
                            <input type="text" class="form-control input-style-custom" required name="clave_incapacidad" id="clave_incapacidad">
                        </div>

                        <div class="mt-3">
                            <label for="inasistencia">Periodo a aplicar:</label><br>
                            <select name="periodo" class="form-control input-style-custom" id="periodo" required>
                                <option value="">Seleccionar</option>
                                @foreach ($periodos_nomina as $periodo)
                                    <option value="{{$periodo->numero_periodo}}">{{$periodo->numero_periodo}} -- del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</option>
                                @endforeach           
                            </select>
                        </div>

                        <div class="mt-3">
                            <label for="">Tipo Incapacidad:</label>
                            <select name="tipo_incapacidad" class="form-control input-style-custom" id="tipo_incapacidad" required>
                                <option value="">Seleccionar</option>
                                <option value="Riesgo de Trabajo">Riesgo de Trabajo</option>
                                <option value="Enfermedad General">Enfermedad General</option>
                                <option value="Maternidad">Maternidad</option>                     
                            </select>
                        </div>

                        <div class="my-3">
                            <label for="">Tipo aplicación sobre cuotas:</label>
                            <select name="tipo_aplicacion" class="form-control input-style-custom" id="tipo_aplicacion" required>
                                <option value="">Seleccionar</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Bimestral">Bimestral</option>                 
                            </select>
                        </div>



                        <input type="hidden" name="id"  id="id">
                        <input type="hidden" name="id_empleado"  id="id_empleado">
                        <input type="hidden" name="estatus"  id="estatus" value="1">
{{--                         <button type="button" data-dismiss="modal" class="btn btn-dark font-weight-bold my-4">CANCELAR</button>
                        <button type="submit" class="btn btn-warning font-weight-bold my-4 continuar">CONTINUAR</button> --}}
                    </div>

                    <div class="row center">
                        <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                        <button type="submit" class="btn button-style continuar" >Guardar</button>
                    </div>
        
                </div>
            </form>
        </div>
    </div>
</div>


<script>    

    $(".id_emp").click(function(){
        id = $(this).data('id_empleado');
        document.getElementById('id_empleado').value = id;
    });


    // al abrir el modal cargamos las prestaciones
    $(".id_emp").click(function(){

        var id = $(this).data('id');

        $('#incapacidadModal .modal-body #id').val(id);

        var id_empleado = $(this).data('id_empleado');
      
        $('#incapacidadModal .modal-body #id_empleado').val(id_empleado);

        if(id == ''){
            $('#incapacidadModal .modal-title span').text('Crear');
            $('#incapacidadModal .modal-body #fecha_inicio_incapacidad').val('');
            $('#incapacidadModal .modal-body #fecha_fin_incapacidad').val('');
            $('#incapacidadModal .modal-body #dias').val('');
            $('#incapacidadModal .modal-body #clave_incapacidad').val('');
            $('#incapacidadModal .modal-body #periodo').val('');
            $('#incapacidadModal .modal-body #tipo_incapacidad').val('');
            $('#incapacidadModal .modal-body #tipo_aplicacion').val('');
        } else {
            $('#incapacidadModal .modal-title span').text('Editar');

            var fecha_inicio_incapacidad = $(this).data('fecha_inicio_incapacidad');
            $('#incapacidadModal .modal-body #fecha_inicio_incapacidad').val(fecha_inicio_incapacidad);

            var fecha_fin_incapacidad = $(this).data('fecha_fin_incapacidad');
            $('#incapacidadModal .modal-body #fecha_fin_incapacidad').val(fecha_fin_incapacidad);

            var dias = $(this).data('dias');
            $('#incapacidadModal .modal-body #dias').val(dias);

            var clave_incapacidad = $(this).data('clave_incapacidad');
            $('#incapacidadModal .modal-body #clave_incapacidad').val(clave_incapacidad);

            var periodo = $(this).data('periodo');
            $('#incapacidadModal .modal-body #periodo').val(periodo);

            var tipo_incapacidad = $(this).data('tipo_incapacidad');
            $('#incapacidadModal .modal-body #tipo_incapacidad').val(tipo_incapacidad);

            var tipo_aplicacion = $(this).data('tipo_aplicacion');
            $('#incapacidadModal .modal-body #tipo_aplicacion').val(tipo_aplicacion);
        }
    });

    $( "#incapacidadModal #fecha_inicio_incapacidad, #incapacidadModal #fecha_fin_incapacidad" ).change(function(){

        if($( "#incapacidadModal #fecha_inicio_incapacidad").val() != '' &&  $("#incapacidadModal #fecha_fin_incapacidad").val() != ''){
            var date1 = new Date($("#incapacidadModal #fecha_inicio_incapacidad").val()); 
            var date2 = new Date($("#incapacidadModal #fecha_fin_incapacidad").val()); 
            
            // To calculate the time difference of two dates 
            var Difference_In_Time = date2.getTime() - date1.getTime(); 
            
            // To calculate the no. of days between two dates 
            var days = (Difference_In_Time / (1000 * 3600 * 24))+1;

            $('#incapacidadModal #dias').val(parseInt(days));
        }
    });

    $('#incapacidadModal .incapacidadForm').submit(function(){
        $('#incapacidadModal .continuar').attr('disabled', true).text('ESPERE...')
    });
    



$(function(){
    $('.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
});

</script>

