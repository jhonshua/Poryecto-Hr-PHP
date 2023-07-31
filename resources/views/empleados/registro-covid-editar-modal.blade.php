<div class="modal" tabindex="-1" role="dialog" id="registroCovidEditarModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seguimiento covid</h5>
            </div>
            <div class="modal-body ">
                <div class="col-md-12 text-center">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mt-3"></i>
                    <div class="nombreempleado font-weight-bold my-3"></div>
                </div>
                {{-- {{route('empleados.seguimientoCovid.editar')}} --}}
                <form action="{{ route('covid.editar') }}" class="row" id="registroCovidEditar" method="post" role="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_registro" id="id_registro_editar">
                    <input type="hidden" name="id_empleado" id="id_empleado_editar">
                    <input type="hidden" name="escontacto" id="escontacto_editar">
                    <input type="hidden" name="escontactode" id="escontactode_editar">

                    <div class="col-md-7 registroEditar"></div>
                    <div class="col-md-5 contactosEditar"></div>

                    <div class="col-md-12">
                        <button type="button" data-dismiss="modal" class="btn btn-dark font-weight-bold my-4 regresar">CANCELAR</button>
                        <button type="submit" class="btn btn-warning font-weight-bold my-4 guardar" id="editarbtn">GUARDAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

$(function(){
    $('#registroCovidEditarModal').on('shown.bs.modal',function(e){
        inicio_formulario_covid_editar();

        var nombreempleado = $(e.relatedTarget).data('nombre');
        $('#registroCovidEditarModal .modal-body .nombreempleado').text('REGISTRO COVID PARA: ' + nombreempleado.trim());

        var escontacto = $(e.relatedTarget).data('escontacto');
        $('#registroCovidEditarModal .modal-body #escontacto_editar').val(escontacto);

        var escontactode = $(e.relatedTarget).data('escontactode');
        $('#registroCovidEditarModal .modal-body #escontactode_editar').val(escontactode);

        var idempleado = $(e.relatedTarget).data('idempleado');
        $('#registroCovidEditarModal .modal-body #id_empleado_editar').val(idempleado);
        
        var idregistro = $(e.relatedTarget).data('idregistro');
        $('#registroCovidEditarModal .modal-body #id_registro_editar').val(idregistro);

        var fechainicio = $(e.relatedTarget).data('fechainicio');
        $('#registroCovidEditarModal .modal-body #fecha_inicio_editar').val(fechainicio);

        var notas = $(e.relatedTarget).data('notas');
        $('#registroCovidEditarModal .modal-body #notas_editar').val(notas);

        var evidencia_inicio = $("#evidencia1").data('nombre');
        if(evidencia_inicio != undefined){
            $('#registroCovidEditarModal .modal-body #nombre_inicio').html("<label>" + evidencia_inicio + "</label>");
        }

        var estatus = $(e.relatedTarget).data('estatus');
        if(estatus == 1){
            $("#termino0editar").prop("checked",true).click();
        }else if(estatus == 2){
            $("#termino1editar").prop("checked",true).click();

            var evidencia_fin = $("#evidencia2").data('nombre');
            if(evidencia_fin != undefined){
                $('#registroCovidEditarModal .modal-body #nombre_fin').html("<label>" + evidencia_fin + "</label>");
            }
        
            var fechafin = $(e.relatedTarget).data('fechafin');
            $('#registroCovidEditarModal .modal-body #fecha_fin_editar').val(fechafin);
        }
        
        $("#registro" + idregistro + " .lo_contagio").each(function(){
            var lo_contagio = $(this).data("empleado");
            $('#registroCovidEditarModal  #contactosEditar option[value="'+lo_contagio+'"]').remove();
        });

        $("#registro" + idregistro + " .es_contacto").each(function(){
            var es_contacto = $(this).data("empleado");
            $('#registroCovidEditarModal  #contactosEditar option[value="'+es_contacto+'"]').prop("selected",true);
        });
        
    });

});
function inicio_formulario_covid_editar(){
    $(".registroEditar").html('<h4>Registro Covid</h4>');
    $(".registroEditar").append('<label for="">Fecha inicio:</label><input type="date" name="fecha_inicio" id="fecha_inicio_editar" required class="form-control"  min=""><br>');
    $(".registroEditar").append('<label for="">Evidencia inicial:</label><input type="file" name="evidencia_inicio" id="evidencia_inicio_editar" class="form-control" accept=".img, .png, .jpeg, .jpg, .pdf"><div id="nombre_inicio" class="col-md-12 text-center"></div><br>');
    $(".registroEditar").append('<label for="" class="mt-3">Tipo de prueba realizada: </label><input type="text" name="prueba" id="prueba" required class="form-control"  min=""><br>');
    $(".registroEditar").append('<div style="padding-left:16px;padding-right:16px;"><label>El contagio del empleado:</label><div class="row finalizoEditar p-2" style="background:#f0c018;"><div class="col-md-3" ><label><b>¿Finalizó?</b></label></div></div><br/></div>');
    $(".finalizoEditar").append('<div class="col-md-3" ><div class="form-check"><input class="form-check-input" type="radio" name="termino" id="termino0editar" value="0" ><label class="form-check-label" for="termino0editar">NO</label></div></div>');
    $(".finalizoEditar").append('<div class="col-md-3" ><div class="form-check"><input class="form-check-input" type="radio" name="termino" id="termino1editar" value="1" ><label class="form-check-label" for="termino1editar">SI</label></div></div>');
    $(".registroEditar").append('<div class="terminoEditar"></div>');
    
    $(".contactosEditar").html('<h4>Contactos</h4><select multiple size="12" class="p-2 form-control" name="contactos[]" id="contactosEditar"></select>');
    
    
    $(".contactosEditar").append('<br/><label>Notas:</label><textarea class="form-control" name="notas" id="notas_editar" rows="5" placeholder="Detalles sobre el contagio"></textarea>');
    @foreach($empleados as $contacto)
        $('#contactosEditar').append('<option value="{{$contacto->id}}"> {{$contacto->nombre}} {{$contacto->apaterno}} {{$contacto->amaterno}}</option>');
    @endforeach

    $("input[name=termino]").off().on('click',function () {    
        if($('input:radio[name=termino]:checked').val() == 1){
            campos_termino_editar();
        }else{
            $('.terminoEditar').html('');
        }
            
    });

    $("#registroCovidEditar").off().on("submit",function(e){
        // var img = "{{asset('public/img/spinner.gif')}}";
        // $("#editarbtn").html("<img src='"+img+"' style='width:20px' />");
        // $("#editarbtn").attr("disabled","disabled");
    });


}

function campos_termino_editar(){
    $('.terminoEditar').html('<label for="">Fecha fin:</label><input type="date" name="fecha_fin" id="fecha_fin_editar" required class="form-control" min=""><br><label for="">Evidencia final:</label><input type="file" name="evidencia_fin" id="evidencia_fin_editar" class="form-control" accept=".img, .png, .jpeg, .jpg, .pdf"><div id="nombre_fin" class="col-md-12 text-center"></div><br>');
    $('.terminoEditar').append('<label for="" class="mt-3">Tipo de prueba realizada: </label><input type="text" name="prueba" id="prueba" required class="form-control"  min=""><br>');
}
</script>
