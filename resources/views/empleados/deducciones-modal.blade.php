<div class="modal" tabindex="-1" role="dialog" id="deduccionesModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear una deducción al empleado</h5>
            </div>
            <div class="modal-body row">
                <div class="col-md-12">
                   
                
                    <form action="{{ route('empleados.guardardeduccion') }}" name="" method="POST" id="deduccionesForm">
                        @csrf
                        <label class="mb-0 mt-2">Concepto:</label>
                        <select name="id_concepto" id="id_concepto" class="form-control mb-3" required>
                            <option value="">Selecciona un concepto...</option>
                            @foreach ($conceptosDeducciones as $concepto)
                                <option value="{{$concepto->id}}">{{strtoupper($concepto->nombre_concepto)}}</option>
                            @endforeach
                        </select>

                        <label class="mb-0 mt-2">Importe total:</label>
                        <input type="number" name="importe_total" id="importe_total" class="form-control mb-3" step="0.0001" required>

                        <label class="mb-0 mt-2">Número de pagos a realizar:</label>
                        <select name="numero_pagos_a_realizar" id="numero_pagos_a_realizar" class="form-control mb-3" required>
                            <option value="">Selecciona un plazo...</option>
                            @for($i = 1; $i <= 60; $i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select>

                        <label class="mb-0 mt-2">Importe a descontar por periodo:</label>
                        <input type="number" name="cantidad_a_descontar" id="cantidad_a_descontar" class="form-control mb-3" readonly>

                        <label class="mb-0 mt-2">Fecha de inicio:</label>
                        <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control mb-3" required value="{{date('Y-m-d')}}">

                        <label class="mb-0 mt-2">Activar descuento:</label>
                        <select name="estatus" class="form-control mb-3" required>
                            <option value="{{App\Models\EmpleadoDeducciones::ACTIVO}}">SI</option>
                            <option value="{{App\Models\EmpleadoDeducciones::INACTIVO}}">NO</option>
                        </select>

                        <hr>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                                <button type="submit" class="btn button-style mb-3 guardar">Guardar</button>
                                <input type="hidden" name="id_empleado" id="id_empleado_deduccion" value="{{$id_empleado}}">
                                <input type="hidden" name="saldo" id="saldo">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
$(function(){


    $( "#fecha_inicio" ).datepicker({ 
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    $('#deduccionesModal').on('shown.bs.modal', function (e) {
    });


});
</script>

<script type="text/javascript">
    $('#numero_pagos_a_realizar, #importe_total').change(function(){
        pagos = $('#importe_total').val()/$('#numero_pagos_a_realizar').val();
       
        $('#cantidad_a_descontar').val(pagos.toFixed(2));
        $('#saldo').val($('#importe_total').val());
    });


    $( "#deduccionesForm" ).validate({
        ignore: [],
        submitHandler: function(form) {
            form.submit();
            $('#deduccionesForm .btn.guardar').text('ESPERE...');
            $('#deduccionesForm .btn.guardar').attr('disabled', true);
        },
    });
</script>