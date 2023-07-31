<div class="modal" tabindex="-1" role="dialog" id="audienciaModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo demanda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('juridico.audiencia-form-modal')
            </div>
        </div>
    </div>
</div>

<script>
var idAudiencia = '';
$(function(){
    
    $('#audienciaModal').on('shown.bs.modal', function(e){

        $("#idAudiencia").val('');
        var tipo = $(e.relatedTarget).data('tipo');
        formAudienciasIni(tipo);
        
        idAudiencia = $(e.relatedTarget).data('audiencia');

       if(idAudiencia){ 
            $(".modal-title").html("Editar audiencia");
            $("#btn_audiencia").html("Actualizar audiencia");
            $("#idAudiencia").val(idAudiencia);             
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {'_token': CSRF_TOKEN, id :idAudiencia };
            
            var url = "{{route('demandas.audienciaprejudicial')}}";
            // url = url.replace('*ID*', idAudiencia);

            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                beforeSend: function(){
                    $("#audienciaModal").hide(); // Para input de tipo button
                },
                complete:function(data){
                    $("#audienciaModal").slideDown();
                    $("#spinner").addClass("ocultar");
                },
                success: function (response) {
                    if(response.ok == 1) {
                        $("#idDemanda").val(response.demanda.id);
                        $("#expediente").val(response.audiencia.expediente);
                        $("#ciudad").val(response.audiencia.ciudad);
                        if(response.audiencia.fecha_aviso){
                            $("#FechaAviso").val(moment(response.audiencia.fecha_aviso).format('DD-MM-YYYY'));
                        }
                        if(response.audiencia.fecha_audiencia){
                            $("#FechaAudiencia").val(moment(response.audiencia.fecha_audiencia).format('DD-MM-YYYY'));
                        }
                        if(response.audiencia.hora_audiencia){
                            $("#HoraAudiencia").val(response.audiencia.hora_audiencia);
                        }
                        $("#honorarios").val(response.audiencia.costo_estimado_honorarios);
                        $("#ArregloConci").val(response.audiencia.arreglo_conciliatorio);
                     

                        if(response.audiencia.concluido == 1){
                            $("#arreglo_conciliatorio").prop('checked', true);
                            arreglo();
                            if(response.demanda.est_importe == 1){
                                $("#EstImporte").prop('checked', true);
                            }
                            if(response.demanda.est_prestaciones_d == 1){
                                $("#EstPrestaciones").prop('checked', true);
                            }
                            if(response.demanda.est_indm_cons == 1){
                                $("#EstIndmCon").prop('checked', true);
                            }
                            if(response.demanda.est_indm_anio == 1){
                                $("#EstIndmAno").prop('checked', true);
                            }
                            if(response.demanda.est_salario_caido == 1){
                                $("#EstSalarioCaido").prop('checked', true);
                            }
                            if(response.demanda.importe_extra == "" || response.demanda.importe_extra == null){
                                response.demanda.importe_extra = 0;
                            }
                            $("#ImporteExtra").val(response.demanda.importe_extra);
                            
                            calcular();
                            
                           
                            $("#MotivoArregloConci").val(response.demanda.motivo_arreglo_conciliacion);

                        }else{
                            if(response.audiencia.fecha_proxima){
                                $("#FechaProxima").val(moment(response.audiencia.fecha_proxima).format('DD-MM-YYYY'));
                            } 
                        }
                    
                    //judicial
                        $("#junta").val(response.audiencia.junta);
                        $("#pre").val(response.audiencia.pre);
                        //alert(response.audiencia.tipo_audiencia);
                        $("#TipoAudiencia").val(response.audiencia.tipo_audiencia).change();
                        if(response.audiencia.tipo_audiencia == 2){
                            $("#constestacion").val(response.audiencia.tipo_contestacion);
                        }else if(response.audiencia.tipo_audiencia == 3){
                            $("#tipoPrueba").val(response.audiencia.tipo_prueba).change();
                        }else if(response.audiencia.tipo_audiencia == 7){
                            $("#TipoSitua").val(response.audiencia.laudo);
                            habilitarSituacion();
                        }

                        $("#ArregloConciTipo").val(response.audiencia.arreglo_conciliatorio);
                        $("#incidencias").val(response.audiencia.incidencia);
                        $("#contestacion").val(response.audiencia.tipo_contestacion);
                        
                        $("#motivo").val(response.audiencia.motivo);
    
                        if(response.audiencia.prueba_pericial){
                            $(".RadioTipoPrueba[value='"+response.audiencia.prueba_pericial+"']").prop("checked",true);
                        }
                        $("#ObservacionesAlegato").val(response.audiencia.alegatos);
                        
                        $("#desahogo").val(response.audiencia.desahogo);
                        $("#motivoDesahogo").val(response.audiencia.motivo_desahogo);
                        $("#monto").val(response.audiencia.monto);
                        $("#FormaPago").val(response.audiencia.forma_pago);

                        $("#TipoAudiencia").val(response.audiencia.historial);
                        if(response.audiencia.amparo == 1){
                            $("#Amparo").prop("checked",true);
                            amparo();
                        }

                        //involucrados
                            if(response.audiencia.tipo_prueba == 3 || response.audiencia.tipo_prueba == 4 || response.audiencia.tipo_prueba == 5){
                                if(response.audiencia.involucrados.length > 0){
                                    for(cont = 0; cont < response.audiencia.involucrados.length; cont++){
                                       $("#persona"+(cont+1)).val(response.audiencia.involucrados[cont]['nombre']);
                                       $("#domicilio"+(cont+1)).val(response.audiencia.involucrados[cont]['domicilio']);
                                       $("#estado"+(cont+1)).val(response.audiencia.involucrados[cont]['estado']);
                                    }
                                }
                            }
                        
                        //constitucional


                        if(response.audiencia.pre == 2){
                            if(response.audiencia.fecha_sentencia){
                                $("#FechaSentencia").val(moment(response.audiencia.fecha_sentencia).format('DD-MM-YYYY'));
                            }

                            $("#TipoPruebaCons").val(response.audiencia.tipo_prueba);
                          /*  alert(response.audiencia.tipo_prueba);
                            if(response.audiencia.tipo_prueba == 3 || response.audiencia.tipo_prueba == "3" || response.audiencia.tipo_prueba == 4 || response.audiencia.tipo_prueba == 5){
                                alert(response.audiencia.tipo_prueba);
                                if(response.audiencia.involucrados.length > 0){
                                    for(cont = 0; cont < response.audiencia.involucrados.length; cont++){
                                        console.log(response.audiencia.involucrados[cont]);
                                    }
                                }
                            }*/

                            $("#observacionesCons").val(response.audiencia.motivo);
                            $("#Sentido").val(response.audiencia.sentido).change();

                            $("#montoCons").val(response.audiencia.monto);
                            $("#FormaPagoCons").val(response.audiencia.forma_pago);


                        }

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
                        title: "Ocurrió un error al cargar los datos del aviso  ",
                        text: "Intente nuevamente!",
                        icon: "warning",
                        button: "Ok",
                    });
                }
            });

        }else{
            $(".modal-title").html("Nueva audiencia");
            $("#btn_audiencia").html("Agregar audiencia");

        }




        $("#btn_audiencia").off();
        $("#btn_audiencia").on("click",function(e){
            e.preventDefault();
            if($("#frmAudienciaPreJudicial").valid()){
                var idAudiencia = $("#idAudiencia").val();
                var pre = $("#pre").val();
                if(pre == 1){ //prejudicial
                    if(idAudiencia){ // modificar
                        var url = "{{route('demandas.audienciaprejudicialeditar')}}";
                        var txtBoton = "Actualizar audiencia";
                    }else{ 
                        var url = "{{route('demandas.audienciaprejudicialagregar')}}";
                        var txtBoton = "Agregar audiencia";
                    }

                }else if(pre == 0){//judicial
                    var url = "{{route('demandas.audienciaprejudicialguarda')}}";
                    if(idAudiencia){ // modificar
                        var txtBoton = "Actualizar audiencia";
                    }else{ //agregar
                        var txtBoton = "Agregar audiencia";
                    }
                }else if(pre == 2){
                    var url = "{{route('demandas.audienciaconstitucionaleditar')}}";
                    var txtBoton = "Actualizar audiencia";
                }else if(pre == 3){
                    var url = "{{route('demandas.audienciamasivasguarda')}}";
                    var txtBoton = "Agregar audiencias";
                }
                var formData = new FormData(document.getElementById("frmAudienciaPreJudicial"));

                var btnEnviar = $("#btn_audiencia");
                    $.ajax({
                        type: "post",
                        url: url,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "JSON",
                        beforeSend: function(){
                            var img = "{{asset('public/img/spinner.gif')}}";
                            btnEnviar.html("<img src='"+img+"' style='width:20px' />"); // Para input de tipo button
                            btnEnviar.attr("disabled","disabled");
                        },
                        complete:function(data){
                    
                            btnEnviar.html(txtBoton);
                            btnEnviar.removeAttr("disabled");
                        },
                        success: function(response){
                            console.log(response);
                            if(response.ok == 1) {
                                    // alertify.success(response.msg);

                                    swal({
                                      title: response.msg,
                                      icon: "success",
                                      button: "Ok",
                                    });

                                    tabla.ajax.reload();
                                    $('#audienciaModal').modal('toggle');
                            }else {
                                // alertify.alert('Error', response.msg);
                                swal({
                                    title: response.msg,
                                    text: "Intente nuevamente!",
                                    icon: "warning",
                                    button: "Ok",
                                });
                            }
                        },
                        error: function(data){
                            alert("Problemas al tratar de enviar el formulario");
                        }
                    });
            }else{
                $("#expediente").focus();
            }
            // Nos permite cancelar el envio del formulario
             return false;

        });


    });


     
});


</script>