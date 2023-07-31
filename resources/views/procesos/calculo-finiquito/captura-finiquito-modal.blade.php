<div class="modal" tabindex="-1" role="dialog" id="capturaFiniquitoModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Capturar finiquito</h5>
            </div>
            <div class="modal-body row pb-5">
                <div class="col-md-12 text-center">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mt-3"></i>
                    <div class="nombreempleado font-weight-bold my-3"></div>
                </div>
                <div class="col-md-12 mt-3">

                    <form method="post" action="{{route('procesos.finiquitocapturar')}}" class="row" id="capturaFiniquito">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="nombre" id="nombre">
                        <input type="hidden" name="fecha_baja_empleado" id="fecha_baja_empleado">
                        <input type="hidden" name="causa_baja_empleado" id="causa_baja_empleado">
                        <input type="hidden" name="idperiodo" id="idperiodo">
                        <input type="hidden" name="nombreperiodo" id="nombreperiodo">


                        <div class="col-md-12">
                            <label for="">Fecha de Baja: </label>
                            <input type="date" name="fecha_baja" id="fecha_baja" required class="form-control mb-2" value="" autocomplete="off">
                        </div>
                        <div class="col-md-12" id="lista_causa_baja"></div>
                        <div class="col-md-12" style="padding-bottom:15px;padding-top:15px">
                            <label for="">Causa de la baja oficial: </label>
                            <input type="text" name="baja_oficial" id="baja_oficial" required class="form-control mb-2" value="" autocomplete="off">
                        </div>
                        <div id="conceptos_nomina" class="row col-md-12" style="font-size:11px"></div>
                        <div class="col-md-12 text-center">
                            <div class="btn-group col-md-6" style="padding-top:15px">
                                <a href="#" data-dismiss="modal" class="btn button-style-cancel mr-2">Cancelar</a>
                                <button type="submit" id="btn-guardar" class="btn button-style mr-2 guardar">Guardar</button>
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
        font-size: 20px;
        color: brown;
    }
    .col-md-3{
        padding-bottom:5px;
    }
    .col-md-3 label{
        padding-top:5px;
    }
</style>

<script>
$(function(){

    // $('.bajaEmpleadoForm').validate();

    // al abrir el modal cargamos las prestaciones
    $('#capturaFiniquitoModal').on('shown.bs.modal', function (e) {

        $('#capturaFiniquitoModal .modal-body #causa_baja_empleado').val("");
        $('#capturaFiniquitoModal .modal-body #fecha_baja_empleado').val("");
        $("#baja_oficial").val("");

        //obtener valor para hidden de id empleado
        var id = $(e.relatedTarget).data('id');
        $('#capturaFiniquitoModal .modal-body #id').val(id);
        
        //obtener valor para hidden de nombre empleado
        var nombreempleado = $(e.relatedTarget).data('nombre');
        $('#capturaFiniquitoModal .modal-body .nombreempleado').text('Capturando finiquito de: ' + nombreempleado.trim());
        $('#capturaFiniquitoModal .modal-body #nombre').val(nombreempleado);
        
        //obtener valor para hidden de fecha_baja
        var fecha_baja_empleado = $(e.relatedTarget).data('fechabaja');
        $('#capturaFiniquitoModal .modal-body #fecha_baja_empleado').val(fecha_baja_empleado);
        $("#fecha_baja").val(fecha_baja_empleado); 

        //obtener valor para hidden de causa baja
        var causa_baja_empleado = $(e.relatedTarget).data('causa');
        $('#capturaFiniquitoModal .modal-body #causa_baja_empleado').val(causa_baja_empleado);

        var causa_baja_oficial = $(e.relatedTarget).data('causaoficial');
        var idperiodo = $(e.relatedTarget).data('idperiodo');
        var nombreperiodo = $(e.relatedTarget).data('nombreperiodo');


        //crear lista de causa baja
        $("#lista_causa_baja").html('<label for="">Causa de la baja: </label><select name="causa_baja" id="causa_baja" required class="form-control">');
        $("#causa_baja").append('<option value="TERMINO DE CONTRATO">TERMINO DE CONTRATO</option><option value="SEPARACION VOLUNTARIA">SEPARACION VOLUNTARIA</option><option value="ABANDONO DE EMPLEO">ABANDONO DE EMPLEO</option><option value="DEFUNCION">DEFUNCION</option><option value="CLAUSURA">CLAUSURA</option><option value="OTRAS">OTRAS</option><option value="AUSENTISMO">AUSENTISMO</option><option value="RESCISION DE CONTRATO">RESCISION DE CONTRATO</option><option value="JUBILACION ">JUBILACION </option><option value="PENSION">PENSIÓN</option>');

        $("#conceptos_nomina").html('');
        if(fecha_baja_empleado != "0000-00-00" && idperiodo != ""){ // si ya tiene una fecha de baja
             // asignar valor a la lista de causa baja
            $("#causa_baja").val(causa_baja_empleado); // asignar valor al select de causa baja
            $("#baja_oficial").val(causa_baja_oficial);
            $("#idperiodo").val(idperiodo);
            $("#nombreperiodo").val(nombreperiodo);
            traerConceptosNomina(id);

        }
        
    });


    $("#capturaFiniquito").submit(function(){
        guardarConceptosNomina();
        return false;
    });
});


function traerConceptosNomina(id_empleado){
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    data = {'_token': CSRF_TOKEN};
            
    var url = "{{route('procesos.finiquitoconceptosnomina','*ID*')}}";
    url = url.replace('*ID*', id_empleado);

    $.ajax({
        type: "POST",
        url: url,
        data: data,
        dataType: 'JSON',
        beforeSend: function(){
            var img = "{{asset('public/img/spinner.gif')}}";
            $("#conceptos_nomina").html("<div class='col-md-12' id='spinner'><center><img src='"+img+"' style='width:30px' /></center></div>"); 
        },
        complete:function(data){
            $("#spinner").remove();
        },
        success: function (response) {
                    if(response.ok == 1) {
                       // $("#conceptos_nomina").html('<div class="col-md-3 text-right"><label for="form-control">Prueba</label></div><div class="col-md-3"><input type="text"class="form-control" name="" id="" /></div><div class="col-md-3 text-right"><label for="form-control">Prueba</label></div><div class="col-md-3"><input type="text"class="form-control" name="" id="" /></div>');
                        response.inputs.forEach(input => {
                            $('#conceptos_nomina').append('<div class="col-md-3 text-right"><label for="form-control">'+input.concepto+'</label></div><div class="col-md-3"><input type="number"class="form-control" name="valor'+input.idconcepto+'" id="valor'+input.idconcepto+'" value="'+input.valor+'" required min="0"/>');
                        });
                        $('#conceptos_nomina').append('<div class="col-md-12"><label for="form-control">DÍAS NO LABORADOS</label></div><div class="col-md-12"><input type="text"class="form-control" name="dias_no_laborados" id="dias_no_laborados" value="'+response.no_laborados+'" required/>');
                        $("#conceptos_nomina").append('<input type="hidden" name="true_isr" value="1"/>');
                        $("#conceptos_nomina").append('<input type="hidden" name="idrutina" value="'+response.idrutina+'"/>');
                        $("#conceptos_nomina").append('<input type="hidden" name="rutina_ejercicio" value="'+response.rutina_ejercicio+'"/>');
                    }else if(response.ok == 0){
                        swal("Falta Periodo!", response.msj);
                        $('#capturaFiniquitoModal').modal('toggle'); 
                    } else {
                        swal("Error", "Ocurrió un error. Intente nuevamente.", "warning");
                    }
        }, error: function(jqXHR, textStatus, errorThrown){
                        swal("Error", "Ocurrió un error al cargar los datos. Intente nuevamente.", "warning");
                }
       });

}

// guardar valores de conceptos en la captura
function guardarConceptosNomina(){
    
    var btn = $("#btn-guardar");
    var url = $("#capturaFiniquito").attr("action");

    $.ajax({
        type: "POST",
        url: url,
        data: $("#capturaFiniquito").serialize(),
        dataType: 'JSON',
        beforeSend: function(){
            // var img = "{{asset('public/img/spinner.gif')}}";
            // btn.html("<img src='"+img+"' style='width:30px' />"); 
        },
        complete:function(data){
            btn.html("GUARDAR");
        },
        success: function (response) {
            if(response.ok == 1) {
                    swal("La captura se realizó correctamente.", "success");
                if(response.tipo == 0){
                    traerConceptosNomina(response.idempleado);
                }else{
                    table.ajax.reload();
                }
            }else if(response.ok == 2){
                swal("Error", "La fecha de baja ingresada no está dentro de ningun periodo.", "warning"); 
            }else {
                swal("Error", "Ocurrió un error. Intente nuevamente.", "warning");
            }
        }, error: function(jqXHR, textStatus, errorThrown){
                swal("Error", "Ocurrió un error al cargar los datos. Intente nuevamente.", "warning");
            }
       });

}
</script>