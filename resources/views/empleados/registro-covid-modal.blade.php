<div class="modal" tabindex="-1" role="dialog" id="registroCovidModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body ">

                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h4 class="font-weight-bold">Seguimiento covid</h4>
                    </div>
                </div>


                <div class="col-md-12 text-center mt-3">
                    <img src="{{ asset('/img/baja-empleado.png') }}" width="35px"> 
                </div>

                <div class="col-md-12 text-center">
                    <div class="nombreempleado font-weight-bold my-3"></div>
                </div>
                {{-- {{route('empleados.seguimientoCovid.agregar')}} --}}
                <form action="{{ route('covid.agregar') }}" class="row" id="registroCovid" method="post" role="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_registro" id="id_registro">
                    <input type="hidden" name="id_empleado" id="id_empleado">
                    <input type="hidden" name="escontacto" id="escontacto">
                    <input type="hidden" name="escontactode" id="escontactode">

                    <div class="col-md-7 registro mt-3"></div>
                    <div class="col-md-5 contactos mt-3"></div>

                    <div class="col-md-12">
                        <button type="button" data-dismiss="modal" class="button-style-cancel regresar">Cancelar</button>
                        <button type="submit" class="button-style guardar" id="guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#registroCovidModal').on('shown.bs.modal',function(e){
        inicio_formulario_covid();

        var nombreempleado = $(e.relatedTarget).data('nombre');
        $('#registroCovidModal .modal-body .nombreempleado').text('REGISTRO COVID PARA: ' + nombreempleado.trim());

        var escontacto = $(e.relatedTarget).data('escontacto');
        $('#registroCovidModal .modal-body #escontacto').val(escontacto);

        var escontactode = $(e.relatedTarget).data('escontactode');
        $('#registroCovidModal .modal-body #escontactode').val(escontactode);

        var idempleado = $(e.relatedTarget).data('idempleado');
        $('#registroCovidModal .modal-body #id_empleado').val(idempleado);
        
        var idregistro = $(e.relatedTarget).data('idregistro');
        $('#registroCovidModal .modal-body #id_registro').val(idregistro);


    });


});
function inicio_formulario_covid(){
    $(".registro").html('<h5 class="font-weight-bold mt-3">Registro Covid</h5>');
    $(".registro").append('<label for="" class="mt-3">Fecha inicio:</label><input type="date" name="fecha_inicio" id="fecha_inicio" required class="form-control"  min=""><br>');
    $(".registro").append('<label for="">Evidencia inicial:</label><input type="file" name="evidencia_inicio" id="evidencia_inicio" class="form-control" accept=".img, .png, .jpeg, .jpg, .pdf"><br>');
    $(".registro").append('<label for="" class="mt-3">Tipo de prueba realizada: </label><input type="text" name="prueba" id="prueba" required class="form-control"  min=""><br>');
    $(".registro").append('<div style="padding-left:16px;padding-right:16px;"><label>El contagio del empleado:</label><div class="row finalizo p-2"><div class="col-md-3" ><label><b>¿Finalizó?</b></label></div></div><br/></div>');
    $(".finalizo").append('<div class="col-md-3" ><div class="form-check"><input class="form-check-input" type="radio" name="termino" id="termino0" value="0" checked><label class="form-check-label" for="termino0">NO</label></div></div>');
    $(".finalizo").append('<div class="col-md-3" ><div class="form-check"><input class="form-check-input" type="radio" name="termino" id="termino1" value="1" ><label class="form-check-label" for="termino1">SI</label></div></div>');
    $(".registro").append('<div class="termino"></div>');
    
    $(".contactos").html('<h5 class="font-weight-bold">Contactos</h5><select multiple size="12" class="p-2 form-control" name="contactos[]" id="contactos"></select>');
    
    @foreach($empleados as $contacto)
    $('#contactos').append('<option value="{{$contacto->id}}"> {{$contacto->nombre}} {{$contacto->apaterno}} {{$contacto->amaterno}}</option>');
    @endforeach
    
    $(".contactos").append('<br/><label class="font-weight-bold">Notas:</label><textarea class="form-control" name="notas" id="notas" rows="5" placeholder="Detalles sobre el contagio"></textarea>');

    $("input[name=termino]").off().on('click',function () {    
        if($('input:radio[name=termino]:checked').val() == 1){
            campos_termino();
        }else{
            $('.termino').html('');
        }
            
    });

    $("#registroCovid").off().on("submit",function(e){
        $("#guardar").attr("disabled","disabled");
    });
}

function campos_termino(){
    $('.termino').html('<label for="">Fecha fin:</label><input type="date" name="fecha_fin" id="fecha_fin" required class="form-control" min=""><br><label for="">Evidencia final:</label><input type="file" name="evidencia_fin" id="evidencia_fin" class="form-control" accept=".img, .png, .jpeg, .jpg, .pdf"><br>');
    $('.termino').append('<label for="" class="mt-3">Tipo de prueba realizada: </label><input type="text" name="prueba" id="prueba" required class="form-control"  min=""><br>');
}
</script>