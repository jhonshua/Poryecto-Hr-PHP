<div class="modal" tabindex="-1" role="dialog" id="demandaModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo demanda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="containertable" id="containertable">
                    <table class="table table-striped table-condensed" style="font-size:11px;text-align:center;">
                        <thead >
                        <tr>
                            <th>ID</th>
                            <th>Empleado</th>
                            <th>Fecha Baja</th>
                            <th>Importe</th>
                            <th>Indeminizacion Anual</th>
                            <th>Salarios caidos</th>
                            <th>Estatus</th>
                        </tr>
                        </thead>
                        <tbody>
                            <th id="t_idDemanda"></th>
                            <th id="t_empleado"></th>
                            <th id="t_fecha_baja"></th>
                            <th id="t_importe"></th>
                            <th id="t_indemnizacion_anual"></th>
                            <th id="t_salatios_caidos"></th>
                            <th id="t_estatus"></th>
                        </tbody>
                    </table>
                </div>
                @include('juridico.form-demanda')
            </div>
        </div>
    </div>
</div>

<link href="{{asset('css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
<link href="{{asset('css/bootstrap-switch/bootstrap-switch.min.css')}}" rel="stylesheet">
<link href="{{asset('css/fileinput/fileinput.min.css')}}" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
.file-input{
    width:100%;
    padding:10px;
}
</style>

<script src="{{asset('js/bootstrap-switch/bootstrap-switch.min.js') }}"></script>
<script src="{{asset('js/datetimepicker/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{asset('js/moment/moment.js')}}"></script>
<script src="{{asset('js/moment/es.js')}}"></script>
<script src="{{asset('js/fileinput/fileinput.min.js')}}"></script>
<script src="{{asset('js/fileinput/locales/es.js')}}"></script>

<script src="{{asset('js/fileinput/plugins/piexif.min.js')}}"></script>
<script src="{{asset('js/fileinput/plugins/purify.min.js')}}"></script>
<script src="{{asset('js/fileinput/plugins/sortable.min.js')}}"></script>
<script src="{{asset('js/fileinput/themes/fas/theme.js')}}"></script>
<script src="{{asset('js/fileinput/themes/explorer-fas/theme.js')}}"></script>

<script>
var idDemanda = '';
$(function(){
    $('#demandaModal').on('shown.bs.modal', function(e){
        $("#idDemanda").val('');
        formDemandasIni();
        idDemanda = $(e.relatedTarget).data('demanda');
        
       if(idDemanda){ 
            $(".modal-title").html("Editar demanda");
            $("#btn_demanda").html("Actualizar demanda");

            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {'_token': CSRF_TOKEN};
            
            var url = "{{route('demandas.detalle','*ID*')}}";
            url = url.replace('*ID*', idDemanda);
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                beforeSend: function(){
                    $("#formDemanda").hide(); // Para input de tipo button
                    $("#spinner").removeClass("ocultar");
                },
                complete:function(data){
                    $("#formDemanda").slideDown();
                    $("#spinner").addClass("ocultar");
                },
                success: function (response) {
                    console.log("Resultado"+ response);
                    if(response.ok == 1) {
                        //armado del formulario de los involucrados
                        fechass();
                        $("#acusado").html('<div id="acusado_div"></div>');
                        $("#contrato").html('<div id="contrato_div"></div>');
                        
                        $("#acusado_div").append('<div class="checkbox"><input type="checkbox" name="cliente[]" id="ch'+response.empresa.id+'" value="'+response.empresa.id+'"/>&nbsp;'+response.empresa.razon_social+'</div>');
                        $("#contrato_div").append('<div class="radio"><label><input type="radio" name="contrato" id="ra'+response.empresa.id+'" value="'+response.empresa.id+'" />&nbsp;'+response.empresa.razon_social+'</label></div>');
                        response.emisoras.forEach(function(emisora){
                            $("#acusado_div").append('<div class="checkbox"><input type="checkbox" name="cliente[]" id="ch'+emisora.id+'" value="'+emisora.id+'"/>&nbsp;'+emisora.razon_social+'</div>');
                            $("#contrato_div").append('<div class="radio"><label><input type="radio" name="contrato" id="ra'+emisora.id+'" value="'+emisora.id+'" />&nbsp;'+emisora.razon_social+'</label></div>');
                        });
                        $("#acusado_div").append('<div class="checkbox"><input type="checkbox" name="cliente[]" id="ch0" onclick="activarOtro();" value="0"/>&nbsp;Otro</div>');
                        
                        //se asignan valores de la demanda al formulario
                        $("#idDemanda").val(idDemanda);
                        $("#t_idDemanda").html(response.demanda.id);
                        //console.log(empleado[0]);
                        $("#t_empleado").html(response.empleado.nombre + " " + response.empleado.apaterno + " " + response.empleado.amaterno);
                        $("#t_fecha_baja").html(moment(response.demanda.fecha_baja).format('DD-MM-YYYY'));
                        $("#t_importe").html(response.demanda.importe);
                        $("#t_indemnizacion_anual").html(response.demanda.indemnizacion_anio);
                        $("#t_salatios_caidos").html(response.demanda.salario_caido);

                        if(response.demanda.estatus == 1){
                            $("#t_estatus").html('NUEVA');
                        }else if(response.demanda.estatus == 2){
                            $("#t_estatus").html('AMPARO');
                        }else if(response.demanda.estatus == 3){
                            $("#t_estatus").html('CONCILIADO');
                        }
                       
                        if(response.demanda.created_at != "" && response.demanda.created_at != null){
                            $('#InicioDemanda').data("DateTimePicker").defaultDate(moment(response.demanda.created_at).format('DD-MM-YYYY'));
                        }
                        $('#prestaciones_devengadas').val(response.demanda.prestaciones_devengadas);
                        $("#indemnizacion_constitucional").val(response.demanda.indemnizacion_constitucional);
                        $("#folio").val(response.demanda.folio);
                        $("#motivo").val(response.demanda.motivo);
                        $("#actores").html('<div class="radio"><label><input type="radio" name="PActora" value="1" />&nbsp;'+response.empleado.nombre + ' ' + response.empleado.apaterno + ' ' + response.empleado.amaterno+'</label></div><div class="radio"><label><input type="radio" name="PActora" value="2" />&nbsp;'+response.empleado.beneficiario+'</label></div>');
                        
                        response.actor.forEach(function(actor){
                            $("input[name='PActora']").filter('[value="'+actor.id_involucrado+'"]').attr('checked', true);
                        });

                        response.acusado.forEach(function(acusado){
                            $("#ch"+acusado.id_involucrado).attr('checked', true);
                            if(acusado.id_involucrado == 0){
                                activarOtro();
                                $("#NmbOtro").val(acusado.otro_involucrado);
                            }
                        });
                        
                        response.contrato.forEach(function(contrato){
                            $("#ra"+contrato.id_involucrado).attr('checked', true);
                        });
                        
                        
                    
                    } else {
                        // alertify.alert('Error', 'Ocurrió un error al cargar los datos del aviso. Intente nuevamente.');
                        swal({
                          title: "Ocurrió un error al cargar los datos del aviso",
                          text: "Intente nuevamente!",
                          icon: "warning",
                          button: "Ok",
                        });
                    }
                }, error: function(jqXHR, textStatus, errorThrown){
                    // alertify.alert('Error', 'Ocurrió un error al cargar los datos de la aviso. Intente nuevamente.');
                    swal({
                        title: "Ocurrió un error al cargar los datos del aviso",
                        text: "Intente nuevamente!",
                        icon: "warning",
                        button: "Ok",
                    });
                }
            });



        }else{

        }

        $("#btn_demanda").off();
        $("#btn_demanda").on("click",function(e){
            e.preventDefault();
            if(validaDemandado()){
                var idDemanda = $("#idDemanda").val();
                if(idDemanda){ // modificar
                    var url = "{{route('demandas.editar')}}";
                    var txtBoton = "Actualizar demanda";
                }else{
                    
                }
                var formData = new FormData(document.getElementById("formDemanda"));

                var btnEnviar = $("#btn_demanda");
                    $.ajax({
                        type: "post",
                        url: url,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "JSON",
                        beforeSend: function(){
                            var img = "{{asset('img/avatar.png')}}";
                            btnEnviar.html("<img src='"+img+"' style='width:20px' />"); // Para input de tipo button
                            btnEnviar.attr("disabled","disabled");
                        },
                        complete:function(data){
                    
                            btnEnviar.html(txtBoton);
                            btnEnviar.removeAttr("disabled");
                        },
                        success: function(response){
                            if(response.ok == 1) {
                                    //reiniciarForm();
                                    swal("La demanda se actualizó con éxito", {
                                        icon: "success",
                                        buttons: true,
                                    });
                                    tabla.ajax.reload();
                                } else {
                                    swal("No fue posible actualizar la demanda, intentelo nuevamente", {
                                        icon: "danger",
                                        buttons: true,
                                    });
                                }
                        },
                        error: function(data){

                            alert("Problemas al tratar de enviar el formulario");
                        }
                    });
            }else{
                
            }
                // Nos permite cancelar el envio del formulario
                return false;

        });


    });


     
});

function activarOtro(){
    if( $("#ch0").prop('checked')){
        $("#NmbOtroDiv").slideDown('slow');
        $( "#NmbOtro" ).rules( "add", {
            required: true
        });
    }else{
        $("#NmbOtroDiv").slideUp('slow');
        $( "#NmbOtro" ).rules("remove");
    }
}

function fechass(){
    $("#DivFechaNotificacion").html('<label for="InicioDemanda">Notificacion demanda </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="InicioDemanda" id="InicioDemanda" required></div>');
    $('#InicioDemanda').datetimepicker({format: 'DD-MM-YYYY'});
}

function validaDemandado(){
    //alert($("input[name*='cliente']:checked").length);
    if (($("input[name*='cliente']:checked").length)<=0) {
        $("label[for='cliente']").append('<span id="motivo-error" id="span_cliente" class="text-danger" style="">Campo requerido</span>');
        return false;
    }
    $("#span_cliente").remove();
    return true;
}


          
function formDemandasIni(){
    $("span.text-danger").remove();
    $("#formDemanda input[type=text]").val('').removeClass('text-danger');
    $("#formDemanda input[type=number]").val('').removeClass('text-danger');
    $("#formDemanda select").val('').removeClass('text-danger');
    $("#formDemanda textarea").val('').removeClass('text-danger');
    $("#formDemanda input[type=checkbox]").prop("checked",false).removeClass('text-danger');
    $("#NmbOtroDiv").hide();
    $("#NmbOtro" ).removeClass('text-danger');
}




</script>