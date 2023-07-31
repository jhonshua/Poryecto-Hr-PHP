@include('norma.cuestionarios.informacion-personal-form')
<script>
    $("#regresara").remove();
    //var form = $("#example-advanced-form").show();
    $("#informacion_personal").validate({
    errorClass: "invalido",
    errorElement: "span",
    errorPlacement: function(error, element) {
        error.appendTo( $('label[for='+element.attr("name")+']') );
    },
    rules: {
        nombre: {required: true},
        paterno: {required: true},
        materno: {required: true},
        sexo:"required",
        edad:"required",
        estado_civil:"required",
        nivel_estudios:"required",
        tipo_puesto:"required",
        tipo_contratacion:"required",
        tipo_personal:"required",
        tipo_jornada:"required",
        rotacion_turnos:"required",
        experiencia_puesto_actual:"required",
        experiencia_laboral:"required"
    },
    submitHandler: function(form) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let img = "{{asset('img/spinner.gif')}}";
        $('#informacion_personal #btn-guardar').html("<img src='"+img+"' style='width:20px' />").attr("disabled","disabled");
        let url = "{{route('empleado.norma.confirmarInformacionPersonal')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: $('#informacion_personal').serialize(),
            success: function( response ) {
                
                if(response.ok == 1) {
                    
                    $('#informacion_personal #btn-guardar').html('Guardado').attr("disabled",true);
                    $(".editar"+response.informacion_trabajador).attr("data-target","#resultadosListaModal").attr("title","Resultados");
                    $("#fasedit"+response.informacion_trabajador).removeClass('fa-edit').addClass('fa-check-square');
                    
                    swal("La información personal se guardó con éxito.", {
                        icon: "success",
                    });
                
                    let url2 = "{{route('norma.implementacion.lista.empleados.admin.llenar.cuestionarios')}}";
                    $.ajax({
                        url: url2,
                        type: "POST",
                        dataType: 'html',
                        data: {'informacion_trabajador':response.informacion_trabajador,'_token':$('meta[name="csrf-token"]').attr('content')},
                        success: function( respuesta ) {
                            $( "#divTrabajadoresCuestionarios" ).slideUp( "slow", function() {
                                $("#divTrabajadoresCuestionarios").html('').append('<h1>Cuestionario</h1>');
                                $("#divTrabajadoresCuestionarios").append(respuesta);
                                $("#divTrabajadoresCuestionarios" ).slideDown("slow");
                            });
                            
                        }
                    });

                }else{

                    swal("Ocurrio un error , intentalo nuevamente.", {
                        icon: "error",
                    });
                    
                    $('#informacion_personal #btn-guardar').html('Guardar').attr("disabled",false);
                }
            }
        });
        }
    });

</script>
