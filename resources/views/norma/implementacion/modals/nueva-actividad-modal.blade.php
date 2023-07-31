<div class="modal" tabindex="-1" role="dialog" id="actividadModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear actividad</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="actividad_form" role="form">
                    @csrf
                    <input type="hidden" value="" name="idimplementacion" id="idimplementacion" />
                    <input type="hidden" value="" name="id" id="id" />
                    <div class="form-row mt-1">
                        <div class="col-md-12">
                            <label for="descripcion">Descripción:</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control input-style-custom mb-3" required />
                        </div>
                    </div>
                    <div class="form-row mt-1" id="fechas">

                    </div>
                    <div class="form-check form-row mt-1">
                        <div class="col-md-6">
                            <input type="checkbox" name="notificacion" id="notificacion" value="1" />
                            <label for="notificacion">Requiere notificación</label>
                        </div>
                        <div class="col-md-6" id="aperturaFormulario"></div>
                    </div>
                    <br/>
                    <div class="form-row" style="display: flex; justify-content:center;">
                     <button class="button-style guardar ml-3" id="btn-guardar">Guardar</button>   
                     <button id="regresar" type="button" data-dismiss="modal" aria-label="Close" class="button-cancel-style ml-2 cancelar">Cancelar</button>
                        
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.css">

<script src="{{asset('js/validate/jquery.validate.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<script>
    var idActividad = '';

    $(function(){

        $('#actividadModal').on('shown.bs.modal', function (e) {
            fechasIni();
            validarPeriodoNorma();
            idActividad = $(e.relatedTarget).data('actividad');

            if(idActividad){
                formActividadIni();
                $(".modal-title").html("Editar actividad");
                $("#btn-guardar").html("Guardar").attr("disabled", false);

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {'_token': CSRF_TOKEN, 'idactividad': idActividad}

               var url = "{{ route('norma.actividades.ver') }}";
                $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                //async:false,
                success: function (response) {
                    if(response.ok == 1) {
                        $("#id").val(idActividad);
                        $("#descripcion").val(response.actividad.descripcion);

                        $('input[name=fecha_fin]').data("DateTimePicker").defaultDate(moment(response.actividad.fecha_fin).format('DD-MM-YYYY'));
                        $('input[name=fecha_inicio]').data("DateTimePicker").defaultDate(moment(response.actividad.fecha_inicio).format('DD-MM-YYYY'));
                        console.log(moment(response.actividad.fecha_inicio).format('DD-MM-YYYY') + " -- " +moment(response.actividad.fecha_fin).format('DD-MM-YYYY'));
                        
                        if(response.actividad.notificacion == 1){
                            $("#notificacion").prop("checked",true);
                        }
                        if(response.actividad.apertura_formulario == 1){
                            $("#apertura_formulario").prop("checked",true);
                        }
                    } else {
                        swal('Error', 'Ocurrió un error al cargar los datos del usuario. Intente nuevamente.', {
                                        icon: "error",
                                    });
                    }
                    $("#spinner").addClass("ocultar");
                }, error: function(jqXHR, textStatus, errorThrown){
                    swal('Error', 'Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.', {
                                        icon: "error",
                                    });
                                  }
            });
            
        // A G R E G A R    A T E N C I O N
        }else{
            $(".modal-title").html("Agregar actividad");
            $("#btn-guardar").html("Guardar").attr("disabled",false);
            formActividadIni();
            $("#id").val("");
        }
    });


});

function formActividadIni(){
    limpliarFormulario();
    $("#idimplementacion").val($("#implementacion").val());

    $("#actividad_form").validate({
            errorClass: "text-danger",
            errorElement: "span",
            errorPlacement: function(error, element) {
                error.appendTo( $('label[for='+element.attr("name")+']') );
            },
            rules: {
                fecha_inicio: {required: true},
                fecha_fin: {required: true},
                descripcion: {required: true},
            },submitHandler: function(form) {
                var txt = $('#btn-guardar').html();
                $('#btn-guardar').html('Guardando..').attr("disabled","disabled");
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                if($("#id").val() == undefined || $("#id").val() == ""){
                    var url = "{{route('norma.actividades.crear')}}"
                }else{
                    var url = "{{route('norma.actividades.modificar')}}"
                }
                $.ajax({
                    url: url,
                    type:"POST",
                    data: $('#actividad_form').serialize(),
                    success:function(response){
                       
                        if(response.ok == 1) {
                            swal(response.msg,{
                                icon: "success"
                            });
                            
                            table.ajax.reload();
                            cerrarModal();
                        } else {
                            swal('Error', 'Ocurrió un error al cargar los datos del usuario. Intente nuevamente.', {
                                        icon: "error",
                                    });
                        }
                        $('#btn-guardar').html(txt).attr("disabled",false);
                    }
                });
            }
    });
}

function limpliarFormulario(){
    $("span.text-danger").remove();
    $("#actividad_form input[type=text]").val('');
    $("#actividad_form input[type=checkbox]").prop("checked",false);
}

function fechasIni(){
    $("#fechas").html('<div class="col-md-6"><label for="fecha_inicio" class="requerido">Fecha inicio: </label><input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control mb-3" autocomplete="off"></div><div class="col-md-6"><label class="requerido" for="fecha_fin">Fecha fin: </label><input type="text" name="fecha_fin" id="fecha_fin" class="form-control mb-3" autocomplete="off"></div>');
    var implementacionInicio = moment($("#implementacion_inicio").val());
    var implementacionFinInicio = moment($("#implementacion_fin").val()).subtract(1,'days');
    var implementacionFin = moment($("#implementacion_fin").val());
    $('#fecha_inicio').datetimepicker({locale: 'es',format: 'DD-MM-YYYY',minDate:implementacionInicio,defaultDate:implementacionInicio,maxDate:implementacionFinInicio});
    $('#fecha_fin').datetimepicker({locale: 'es',format: 'DD-MM-YYYY',minDate:implementacionInicio,defaultDate:implementacionInicio,maxDate:implementacionFin});

    $('input[name=fecha_inicio]').on('dp.change',function(e){
         var min = moment(e.date,"DD-MM-YYYY");
            var min = moment(e.date,"DD-MM-YYYY").add(1,'day');
            var f = $('input[name=fecha_fin]').data("DateTimePicker").date();
            $('input[name=fecha_fin]').data("DateTimePicker").minDate(min);
            if (e == null || f == null) {
                $('input[name=fecha_fin]').data("DateTimePicker").clear();
            }
    });
}
function validarPeriodoNorma(){
    $.ajax({
        type:"POST",
        url: "{{route('norma.actividades.validarPeriodo')}}",
        data: $('#implementacionActividades').serialize(),
        success:function(response){
            if(response.ok == 1) {
                if(response.periodo){
                    $("#aperturaFormulario").html("");
                }else{
                    $("#aperturaFormulario").html('<input type="checkbox" name="apertura_formulario" class="form-check-input" id="apertura_formulario" value="1"/><label for="apertura_formulario">Apertura de formularios</label>');
                }
            } else {
                swal('Error', 'Ocurrió un error al cargar los datos del usuario. Intente nuevamente.', {
                                        icon: "error",
                                    });
               
            }
        }
    });
}

function cerrarModal(){
    $("#actividadModal").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();
}

</script>
