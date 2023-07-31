<div class="modal" tabindex="-1" role="dialog" id="recordatorioListaModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Recordatorios</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="trabajadores_recordatorio_form" >
                    @csrf
                    <input type="hidden" value="{{$id_periodo_norma}}" name="normaRecordatorio" id="normaRecordatorio" />
                    <input type="hidden" name="correos_enviar" id="correos_enviar" />
                    <input type="hidden" value="{{$datosImplementacion->id}}" name="implementacion_recordatorio" id="implementacion_recordatorio" />

                    <div id="divTrabajadoresRecordatorio" class="text-center" ></div>
                    <div class="row">
                        <div class="col-md-8 offset-md-2 text-center" id="botonesRec"><br/><br/></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
idActividad = '';
$(function(){
    
    $('#recordatorioListaModal').on('shown.bs.modal', function (e) {
        /*$('#recordatorioListaModal').modal({backdrop: 'static', keyboard: false})*/
        var correos = [];
        var idPersonal = $(e.relatedTarget).data('ipersonal');

        if(idPersonal){
            let nombre = $(e.relatedTarget).data('nombre');
            let paterno = $(e.relatedTarget).data('paterno');
            let materno = $(e.relatedTarget).data('materno');

     
            $("#divTrabajadoresRecordatorio").html('¿Está seguro de enviar recordatorio a <strong>' + nombre + ' '+ paterno + ' ' + materno + '</strong>?<br/><br/>');
            $("#botonesRec").html('<button id="regresarRecordatorio" type="button" data-dismiss="modal" aria-label="Close" class=" button-style-cancel ml-2 cancelar">Cancelar</button><button class="button-style ml-2" id="btn-recordar">Enviar recordatorio</button>')
             correos.push($(e.relatedTarget).data('correo'));
            
        }else{

            $("#divTrabajadoresRecordatorio").html('¿Está seguro de realizar un recordatorio masivo?<br/><br/>');
            $("#botonesRec").html('<button id="regresarRecordatorio" type="button" data-dismiss="modal" aria-label="Close" class=" button-style-cancel ml-2 cancelar">Cancelar</button><button class="button-style ml-2" id="btn-recordar">Enviar recordatorio</button>')

            $("#botonesRec").html('<button id="regresarRecordatorio" type="button" data-dismiss="modal" aria-label="Close" class=" button-style-cancel ml-2 cancelar">Cancelar</button><button class="button-style ml-2" id="btn-recordar">Enviar recordatorio</button>')

            $(".recordatorio").each(function(recordatorio){
                //console.log($(this).data('correo'));
                correos.push($(this).data('correo'));
            });
           
        }
        
        $("#correos_enviar").val(JSON.stringify(correos));

        $("#btn-recordar").off();
        $("#btn-recordar").on("click", function(){

            $("#btn-recordar").html(`<span class="spinner-grow " role="status" aria-hidden="true"></span> Espere...`).attr("disabled","disabled");
            var url = "{{route('norma.implementacion.lista.empleados.recordatorio')}}";
            $.ajax({
                type: "POST",
                url: url,
                data: $('#trabajadores_recordatorio_form').serialize(),
                dataType: 'JSON',
                success: function (response) {
                   
                    if(response.ok == 1) {
                        $("#divTrabajadoresRecordatorio").html('<strong>Recordatorio enviado con éxito</strong><br/><br/>');
                        $("#botonesRec").html('<button id="regresarRecordatorio" type="button" data-dismiss="modal" aria-label="Close" class=" button-style-cancel cancelar">OK</button>')
                        
                    }else if(response.ok == 2){
                        $("#divTrabajadoresRecordatorio").html('<strong>'+response.msg+'</strong><br/><br/>');
                        $("#botonesRec").html('<button id="regresarRecordatorio" type="button" data-dismiss="modal" aria-label="Close" class=" button-style-cancel cancelar">OK</button>');
                    }else {
                    
                        swal("Ocurrió un error al cargar los datos del empleado. Intente nuevamente.", {
                            icon: "error",
                            timer: 4000,
                        });

                        location.reload();
                    }
                    
                }, error: function(jqXHR, textStatus, errorThrown){
                 
                    swal("Ocurrió un error al realizar la petición intentalo nuevamente.", {
                        icon: "error",
                        timer: 4000,
                    });
                    location.reload();
                }
            });
            return false;
        });
   
    });

});
</script>