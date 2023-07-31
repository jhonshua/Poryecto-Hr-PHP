<div class="modal" role="dialog" id="reporteAsistenciasModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Exportar asistencia</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row d-flex justify-content-center align-items-center my-3">
                    <form method="post" id="reporte_asistencia_form" action="{{ route('consultas.reporte-asistencias.fechas') }}">
                        @csrf
                        <table width="100%" class="mb-3 ">
                            <tr>
                                <td width="50%">
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                            </tr>

                            <tr>
                                <td width="90%" class="fechas">
                                    <label class="mr-3">Fecha inicial:</label>
                                    <input type="text" name="fecha_inicio" id="fecha_inicio" required class="form-control input-style-custom mb-2 datepicker" value="{{$dia}}" autocomplete="off">
                                </td>
                            </tr>
                            <tr>
                                <td width="90%" class="fechas">
                                    <label class="mr-3">Fecha final:</label>
                                    <input type="text" name="fecha_fin" id="fecha_fin" required class="form-control input-style-custom mb-2 datepicker" value="{{$dia}}" autocomplete="off">
                                </td>
                            </tr>
                            <input type="hidden" name="tipo_asistencias" value="{{ Session::get('empresa')['tipo_asistencias'] }}">
                        </table>
                        <div class="row d-flex justify-content-center align-items-center my-3">
                            <button class="button-style btn-block guardar">Exportar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{asset('js/datapicker-es.js')}}"></script>

<script>
    $(function() {
    

        $("#fecha_inicio").datepicker("setDate", '{{$dia}}');
                $("#fecha_fin").datepicker("setDate", '{{$dia}}');
            
                $("#fecha_inicio").datepicker({ 
                                       
                    dateFormat: 'yy-mm-dd',                                           
                    changeYear: true,
                    changeMonth: true,
                    maxDate:new Date(),
                    
                    onSelect: function (date) {      

                        let fecha_fin=date.split("-");
                                
                        $("#fecha_fin").prop('disabled', false);     
                        $("#fecha_fin" ).val('');
                        $("#fecha_fin" ).datepicker( "option", "maxDate", new Date() );
                        $("#fecha_fin").datepicker({          
                                        changeYear: true,
                                        changeMonth: true,
                                        dateFormat: 'yy-mm-dd',             
                                        maxDate: new Date()
                        });
                    
                                                    
                    }
                });
        $('#reporteAsistenciasModal').on('shown.bs.modal', function(e) {});
        $('#reporteAsistenciasModal #incluirBiometrico').change(function() {
            $('reporteAsistenciasModal .fechas').toggle();
        });
        $('#reporte_asistencia_form').submit(function() {
     
            location.reload();
            $('#reporteAsistenciasModal .guardar').attr('disabled', true).text('Espere...');
            $('#reporteAsistenciasModal select , #reporteAsistenciasModal input').attr('disabled', false);
        
    });
    });
</script>