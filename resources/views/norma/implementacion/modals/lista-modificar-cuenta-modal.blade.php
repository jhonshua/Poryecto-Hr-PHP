<div class="modal" tabindex="-1" role="dialog" id="modificarCorreosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Editar correos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"  >
                <form id="addform">
                    @csrf
                    <div class="row" id="correos"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
    $('#modificarCorreosModal').on('shown.bs.modal', function (e) {

        let icono_guardar =" @php echo  asset('img/icono-guardar.png')  @endphp";
        $('#correos').html('');
        var correo = $(e.relatedTarget).data('correo');
        var id_informacion = $(e.relatedTarget).data('idinformacion');
       /* var informacion = table.row( $(".seleccionados tbody tr[id=" + idEmpleado + "]") ).data();
        $("#idEmpleadoResultados").val(idEmpleado);
        $("#divTrabajadoresResultados").html('');*/
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var url = "{{route('norma.implementacion.lista.empleados.correo')}}";
        $.ajax({
                type: "POST",
                url: url,
                data: {'_token': CSRF_TOKEN, 'idinformacion_trabajador':id_informacion, 'correo': correo},
                dataType: 'JSON',
                success: function (response) {
                    $('#correos').append('<div class="col-md-12"><label for="form-control text-success">CORREO EMPLEADO</label></div>');
                    if(response.empleado.length > 0){
                         response.empleado.forEach(input => {
                            $('#correos').append('<div class="form-row col-md-12 py-2" style="padding-top:3px"><div class="col-2"><label for="form-control">Correo</label></div><div class="col-8"><input type="email" id="empleado'+input.id+'" class="form-control actualizar input-style-custom" value="'+input.correo+'" required data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" ></div><div class="col-2"><img src="'+icono_guardar+'" class="button-style-icon-32px empleado"  data-idlogin="'+input.id+'"></div></div>');
                        });
                    }else{
                        $('#correos').append('<div class="form-row col-md-12 py-2" style="padding-top:3px"><div class="col-12"><label for="form-control text-danger" ><i>(No hay un empleado con ese correo)</i></label></div></div></div>');

                    }
                   
                    $('#correos').append('<br/><div class="col-md-12"><label for="form-control text-success">CORREO NORMA</label></div>');
                    $('#correos').append('<div class="form-row col-md-12 py-2" style="padding-top:3px"><div class="col-2"><label for="form-control">Correo</label></div><div class="col-8"><input type="email" id="informacion'+response.idinformacion_trabajador+'" class="form-control actualizar input-style-custom" value="'+response.correo+'"  required data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/" ></div><div class="col-2"><img src="'+icono_guardar+'" class="button-style-icon-32px informaciontra"  data-idinfotrabajador="'+response.idinformacion_trabajador+'"  ></div></div>');

                    $('#correos').append('<br/><div class="col-md-12"><label for="form-control text-success">CORREO USUARIO</label></div>');
                    if(response.usuario.length > 0){

                        response.usuario.forEach(input => {
                            $('#correos').append('<div class="form-row col-md-12 py-2" style="padding-top:3px"><div class="col-2"><label for="form-control">Correo</label></div><div class="col-8"><input type="email" id="login'+input.id+'" class="form-control input-style-custom  actualizar" value="'+input.email+'" required data-parsley-regexemail="/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/"></div><div class="col-2"><img src="'+icono_guardar+'" class="button-style-icon-32px login-sesion"  data-idlogin="'+input.id+'"></div></div>');
                            $('#correos').append('<div class="p-3"><div  class="button-style center reseteop" data-idlogin="'+input.id+'">Resetear contraseña</div></div>');
                        });
                        
                    }else{
                        $('#correos').append('<div class="form-row col-md-12 py-2 nuevacuentaDIV" style="padding-top:3px"><div class="col-12"><label for="form-control text-danger" ><i>(No hay una cuenta de usuario con ese correo)</i></label></div></div></div>');
                        $('#correos').append('<div class="p-3 nuevacuentaDIV"><div  class="button-style my-4 nuevacuenta" data-correo="'+response.correo+'">Crear usuario</div></div>');

                    }
                    
                    eventosBotones();
                    return false;
                }, error: function(jqXHR, textStatus, errorThrown){
                    alertify.alert('Error', 'Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.');
                }
        });
    });
});
function eventosBotones(){

    $(".empleado").off().on("click",function(){
        let form = $("#addform");
        if(form.parsley().isValid()){
            let id = $(this).data('idempleado');
            let correo = $('#empleado' + id).val();
            swal({
                title: "¿Esta seguro de modificar el correo del empleado?",
                text: "Preparando los cambios!",
                icon: "warning",
                buttons:  ["Cancelar", true],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    let parametros =  {'_token': $('meta[name="csrf-token"]').attr('content'), 'correo': correo, 'id': id,'tipo' : 1};
                    enviarDatos(parametros).then(response=>{
                        if(response!=null){
                            if(response.ok == 1){
                                swal("Usuaurio creado correctamente.!", {
                                    icon: "success",
                                });
                            }

                        }else{
                            swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.!", {
                                icon: "error",
                            });
                        }
                    });
                    //eliminarIcono(index);
                    //location.reload();
                }
            });
            return false;
        }else{
            form.parsley().validate();
        }
    });

    //correo norma
    $(".informaciontra").off().on("click",function(){
        let form = $("#addform");
        if(form.parsley().isValid()){
            //$('#addform').submit();
            let icono_guardar =" @php echo  asset('img/icono-guardar.png')  @endphp";
            let id = $(this).data('idinfotrabajador');
            let correo = $('#informacion' + id).val();
            swal({
                title: "¿Esta seguro de modificar el correo de información en norma 035?",
                text: "Al eliminarlo no podrás recuperar los cambios !",
                icon: "warning",
                buttons:  ["Cancelar", true],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    let parametros = {'_token': $('meta[name="csrf-token"]').attr('content'), 'correo': correo, 'id': id,'tipo' : 2};
                    enviarDatos(parametros).then(response=>{
                        if(response!=null){
                            if(response.ok == 1){
                                swal(`${response.msj}`, {
                                    icon: "success",
                                });
                                if(parametros.tipo  == 5){
                                    $(".nuevacuentaDIV").empty();
                                    $('#correos').append('<div class="form-row col-md-12 py-2" style="padding-top:3px"><div class="col-2"><label for="form-control">Correo</label></div><div class="col-8"><input type="email" id="login'+response.id+'" class="form-control actualizar" value="'+response.email+'" required/></div><div class="col-2"><img src="'+icono_guardar+'" class="button-style-icon-32px login-sesion "  data-idlogin="'+response.id+'"></div></div>');
                                    $('#correos').append('<div class="p-3"><button type="submit" class="button-style my-4 reseteop" data-idlogin="'+response.id+'">RESETEAR CONTRASEÑA</button></div>');
                                    eventosBotones();
                                }else if(parametros.tipo == 2){
                                    $("#correotabla" + response.id ).text(response.email);
                                    $("#correotabla" + response.id ).data('correo',response.email);
                                }
                            }

                        }else{
                            swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.!", {
                                icon: "error",
                            });
                        }
                    });
                    //eliminarIcono(index);
                    //location.reload();
                }
            });
        }else{
            form.parsley().validate();
        }   
    });

    // cambiar correo en cuenta
    $(".login-sesion").off().on("click",function(){
        let form = $("#addform");
        if(form.parsley().isValid()){
            let id = $(this).data('idlogin');
            let correo = $('#login' + id).val();
            swal({
                title: "¿Esta seguro de modificar el correo de la cuenta del usuario?",
                text: "Preparando los cambios!",
                icon: "warning",
                buttons:  ["Cancelar", true],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    let parametros =  {'_token': $('meta[name="csrf-token"]').attr('content'), 'correo': correo, 'id': id,'tipo' : 3};
                    enviarDatos(parametros).then(response=>{
                        if(response!=null){
                            if(response.ok == 1){
                                swal("Usuaurio creado correctamente.!", {
                                    icon: "success",
                                });
                            }

                        }else{
                            swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.!", {
                                icon: "error",
                            });
                        }
                    });
                    //eliminarIcono(index);
                    //location.reload();
                }
            });
            return false;
        }else{
            form.parsley().validate();
        }   
    });

    // resetear contraseña
    $(".reseteop").off().on("click",function(){
        let id = $(this).data('idlogin');

        swal({
            title: "¿Esta seguro de resetear la contraseña?",
            text: "Preparando los cambios!",
            icon: "warning",
            buttons:  ["Cancelar", true],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                let parametros = {'_token': $('meta[name="csrf-token"]').attr('content'), 'id': id,'tipo' : 4};
                enviarDatos(parametros).then(response=>{
                    if(response!=null){
                        if(response.ok == 1){
                            swal("Usuaurio creado correctamente.!", {
                                icon: "success",
                            });
                        }

                    }else{
                        swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.!", {
                            icon: "error",
                        });
                    }
                });
                //eliminarIcono(index);
                //location.reload();
            }
        });
        return false;
    });

    //nuevo usuario
    $(".nuevacuenta").off().on("click",function(){
        let correo = $(this).data('correo');
        swal({
            title: "¿Esta seguro de crear el usuario para el trabajador?",
            text: "Preparando los cambios!",
            icon: "warning",
            buttons:  ["Cancelar", true],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                let parametros = {'_token': $('meta[name="csrf-token"]').attr('content'), 'correo': correo,'tipo' : 5};
                enviarDatos(parametros).then(response=>{
                    if(response!=null){
                        if(response.ok == 1){
                            swal("Usuaurio creado correctamente.!", {
                                icon: "success",
                            });
                        }

                    }else{
                        swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.!", {
                            icon: "error",
                        });
                    }
                });
                //eliminarIcono(index);
                //location.reload();
            }
        });
    });
}
function enviarDatos(datos){
    return new Promise((resolve,reject)=>{
        setTimeout(() => {
            let url = "{{route('norma.implementacion.lista.empleados.correo.operacion')}}";
            $.ajax({
                type: "POST",
                url: url,
                data: datos,
                dataType: 'JSON',
                success: function (response) {
                    resolve(response);
                }, error: function(jqXHR, textStatus, errorThrown){
                    resolve(response=null);
                }
            });
        },500);
    });
}
</script>

