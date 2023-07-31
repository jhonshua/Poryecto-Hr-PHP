<div class="modal" tabindex="-1" role="dialog" id="percepcionesModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear una percepción al empleado</h5>
            </div>
            <div class="modal-body row">
                <div class="col-md-12">
                    <form action="{{ route('empleados.guardarperc') }}" name="" method="POST" id="percepcionesForm">
                        @csrf
                        <label class="mb-0 mt-2">Concepto:</label>
                        <select name="id_concepto" id="id_concepto_p" class="form-control mb-3" required>
                            <option value="">Selecciona un concepto...</option>
                            @foreach ($conceptosPercepciones as $concepto)
                                <option value="{{$concepto->id}}">{{strtoupper($concepto->nombre_concepto)}}</option>
                            @endforeach
                        </select>

                        <label class="mb-0 mt-2">Importe total:</label>
                        <input type="number" name="importe_total" id="importe_total_p" class="form-control mb-3" step="0.0001" required>

                        <label class="mb-0 mt-2">Número de pagos a realizar:</label>
                        <select name="numero_aportaciones_a_realizar" id="numero_aportaciones_a_realizar_p" class="form-control mb-3" required>
                            <option value="">Selecciona un plazo...</option>
                            @for($i = 1; $i <= 36; $i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select>

                        <label class="mb-0 mt-2">Importe a aportar por periodo:</label>
                        <input type="number" name="cantidad_a_aportar" id="cantidad_a_aportar_p" class="form-control mb-3" readonly>

                        <label class="mb-0 mt-2">Fecha de inicio:</label>
                        <input type="text" name="fecha_inicio" id="fecha_inicio_p" class="form-control mb-3" required >

                        <label class="mb-0 mt-2">Activar Aportacion:</label>
                        <select name="estatus" class="form-control mb-3" required>
                            <option value="{{App\Models\EmpleadoPercepciones    ::ACTIVO}}">SI</option>
                            <option value="{{App\Models\EmpleadoPercepciones    ::INACTIVO}}">NO</option>
                        </select>

                        <hr>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mb-3">Cancelar</a>
                                <button type="submit" class="btn button-style mb-3 guardar">Guardar</button>
                                <input type="hidden" name="id_empleado" id="id_empleado_p" value="{{$id_empleado}}">
                                <input type="hidden" name="saldo" id="saldo_p">
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

    $(document).ready(function() {
    $( "#fecha_inicio_p" ).datepicker({ 
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // al abrir el modal 
    $('#percepcionesModal').on('shown.bs.modal', function (e) {
    });
     });

</script>


<script type="text/javascript">
    $('#percepcionesModal #numero_aportaciones_a_realizar_p, #percepcionesModal #importe_total_p').change(function(){
        pagos = $('#percepcionesModal #importe_total_p').val()/$('#percepcionesModal #numero_aportaciones_a_realizar_p').val();
        $('#percepcionesModal #cantidad_a_aportar_p').val(pagos.toFixed(2));
        $('#percepcionesModal #saldo_p').val($('#percepcionesModal #importe_total_p').val());
    });

    // Validar
    $( "#percepcionesForm" ).validate({
        ignore: [],
        submitHandler: function(form) {
            form.submit();
            $('#percepcionesForm .btn.guardar').text('ESPERE...');
            $('#percepcionesForm .btn.guardar').attr('disabled', true);
        },
    });
</script>