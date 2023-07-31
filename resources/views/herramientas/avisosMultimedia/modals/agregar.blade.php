<div class="modal" tabindex="-1" role="dialog" id="avisoModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo aviso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('herramientas.avisosMultimedia.modals.form')
            </div>
        </div>
    </div>
</div>
<script>
    var idAviso = '';
    var inicio = '';
    var fin = '';
    var titulo = '';
    var tipo = 0;
    var estatus = '';
    var tiempo = 0;
    var video = '';
    $(function(){

        $(".select-clase").select2();
        
        $('#avisoModal').on('shown.bs.modal', function(e){
            $("#spinner").removeClass("ocultar");
            $("#idAviso").val('');
            idAviso = $(e.relatedTarget).data('aviso');
            titulo = $(e.relatedTarget).data('titulo');
            inicio = $(e.relatedTarget).data('inicio');
            fin = $(e.relatedTarget).data('fin');
            tipo = $(e.relatedTarget).data('tipo');
            estatus = $(e.relatedTarget).data('estatus');
            video = $(e.relatedTarget).data('video');
            tiempo = $(e.relatedTarget).data('tiempo');
            reiniciarForm();
            formAvisoIni();
           if(idAviso){ 
                $(".modal-title").html("Editar aviso");
                $("#btn-agregar-aviso").html("Actualizar aviso");
                $("#btn-agregar-aviso").attr('title',"Actualizar aviso");
                            $("#idAviso").val(idAviso);
                            $("#titulo").val(titulo);
    
                            $('input[name=inicio]').data("DateTimePicker").defaultDate(moment(inicio).format('DD-MM-YYYY'));
                            $('input[name=fin]').data("DateTimePicker").defaultDate(moment(fin).format('DD-MM-YYYY'));
                            $("#tipo").val(tipo);
                            $(function(){
                                $("#tipo").change();
                                if(tipo == 2){
                                    $("#url").val(video);
                                    $("#tiempo").val(tiempo);
                                  /*  response.aviso['multimedia'].forEach(function(multimedia){
                                        $("#tiempo").val(multimedia['tiempo']);
                                    });*/
                                }else{
                                    $("#imagen_1").attr("required",false);
                                }
                            });
                            if(estatus){
                                $('#estatus').bootstrapSwitch('state' , true);
                            }else{
                                $('#estatus').bootstrapSwitch('state' , false);
                            }
                            $("#spinner").addClass("ocultar");
                       
                 
    
    
    
            }else{
                $("#tipo").change();
                $(".modal-title").html("Agregar aviso");
                $("#btn-agregar-aviso").html("Agregar aviso");
                $("#spinner").addClass("ocultar");
            }
    
            $("#frmPrincipal").off();
            $("#frmPrincipal").on("submit",function(e){
                e.preventDefault();
    
                var formData = new FormData(document.getElementById("frmPrincipal"));
                var idAviso = $("#idAviso").val();
                if(idAviso){ // modificar
                        var url = "{{route('herramientas.avisos.multimedia.editar')}}";
                        var txtBoton = "Actualizar aviso";
                }else{
                        var url = "{{route('herramientas.avisos.multimedia.agregar')}}";
                        var txtBoton = "Agregar aviso";
    
                }
                var btnEnviar = $("#btn-agregar-aviso");
                    $.ajax({
                        type: "post",
                        url: url,
                        data: formData,
                        dataType: "JSON",
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function(){
                            var img = "{{asset('img/spinner.gif')}}";
                            btnEnviar.html("<img src='"+img+"' style='width:20px' />"); // Para input de tipo button
                            btnEnviar.attr("disabled","disabled");
                        },
                        complete:function(data){
                            btnEnviar.html(txtBoton);
                            btnEnviar.removeAttr("disabled");
                            
                        },
                        success: function(response){
                            if(response.ok == 1) {
                             
                                    swal("", `${response.msg}`, "success");
                                    table.ajax.reload();
                                    if(!idAviso){ // modificar
                                        reiniciarForm();
                                        formAvisoIni();
                                        $("#tipo").change();
                                    }else{
                                        
                                    }
                                } else {
                                    swal("", `Error ${response.msg} `, "error");
                                }
                        },
                        error: function(data){
                            swal("", "Problemas al tratar de enviar el formulario", "error");
                        }
                    });
                    // Nos permite cancelar el envio del formulario
                    return false;
    
            });
    
    
        });
    
    
         
    });
    
              
    function formAvisoIni(){
            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch('state', $(this).prop('checked'));
            });
    
            $('input[name=inicio]').on('dp.change',function(e){
               // var min = moment(e.date,"DD-MM-YYYY").add(1,'day');
               var min = moment(e.date,"DD-MM-YYYY");
                var f = $('input[name=fin]').data("DateTimePicker").date();
                $('input[name=fin]').data("DateTimePicker").minDate(min);
                if (e == null || f == null) {
                    $('input[name=fin]').data("DateTimePicker").clear();
                }
            });
    
            $('#tipo').off();
            $('#tipo').on('change', function() {
                var tipo = $("#tipo option:selected" ).val();
               // $('#imagen_1').val('');
               // $('#t_1').val('5');
                if( tipo ==1 ){
                    console.log(tipo);
                    imagenMultimedia();
                }else if(tipo == 2){
                    videoMultimedia()
                }else{
                    $("#vdo").fadeOut("slow",function(){
                        $(this).html("");
                    });
                    $("#imgs").fadeOut("slow",function(){
                        $(this).html("");
                    });
                }
    
            });
    }
    
    function fechas(){
        $("#Divinicio").html('<label for="inicio">Fecha inicio </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="inicio" id="inicio" required></div>');
        $("#Divfin").html('<label  for="fin">Fecha fin </label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="fin" id="fin" required></div>');
    
        $('#inicio').datetimepicker({locale: 'es',format: 'DD-MM-YYYY'});
        $('#fin').datetimepicker({locale: 'es',format: 'DD-MM-YYYY'});
    }
    
    function imagenMultimedia(){
        $("#vdo").fadeOut(function(){
            $("#vdo").html('');
        });
        if($("#idAviso").val()){
            $("#imgs").html('<div id="imagenes"></div>');
            $("#imgs").append('<br/><div class="text-right"><a data-imagen="0" class="mr-2" id="plus"  title="Agregar imagen"><img src="{{asset("img/icono-detalle.png")}}" class="w-30"></a><a data-imagen="0" class="mr-2 disabled" id="minus" title="Eliminar imagen"><img src="{{asset("img/icono-contrario-detalle.png")}}" class="w-30"></a><br/><small><i>Si requiere agregar más imagenes<br/>agregue un nuevo campo de imagen</i></small></div>');
        }else{
            $("#imgs").html('<div id="imagenes">'+componenteIMG(1)+'</div>');
            $("#imgs").append('<br/><div class="text-right"><a data-imagen="1" class="mr-2" id="plus"  title="Agregar imagen"><img src="{{asset("img/icono-detalle.png")}}" class="w-30"></a><a data-imagen="1" mr-2 disabled" id="minus" title="Eliminar imagen"><img src="{{asset("img/icono-contrario-detalle.png")}}" class="w-30" ></a><br/><small><i>Si requiere imagenes con un tiempo de duración distinto <br/>agregue un nuevo campo de imagen</i></small></div>');
        }
        
        botones();
        $("input[type=file]").fileinput({
            language:'es',
            theme: 'fas',
            disabledPreviewExtensions: ['msi','exe','com','zip','rar','app','vb','scr'],
            allowedFileExtensions: ["jpg", "pdf", "png", "jpeg"],
            showUpload:false,
            browseIcon:'<i class="fa fa-folder-open"></i>',
            browseClass:'btn btn-warning',
            removeIcon:'<i class="fa fa-trash"></i>',
            removeClass:'btn btn-default btn-secondary',
            previewFileType: "image",
            previewFileIcon:'<i class="fa fa-file-archive-o"></i>',
        });
    
        $("#imgs").fadeIn("slow");
    }
    
    
    function componenteIMG(contador){
        return '<div id="'+contador+'"><label for="imagen_'+contador+'">Imagen multimedia '+contador+'  <small><i>(hasta 10 imagenes)</i></small></label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="far fa-clock"></i></span></div><input type="number" class="form-control input-style-custom" name="t_'+contador+'" id="t_'+contador+'" value="5" min="1" required></div><div class="input-group mt-3"><input id="imagen_'+contador+'" name="imagen_'+contador+'[]" type="file" accept="image/*" class="file form-control" data-theme="fas" multiple required></div><br/><hr/></div>';
    }
    
    function videoMultimedia(){
        $("#imgs").fadeOut(function(){
            $("#imgs").html('');
        });
        $("#vdo").html('<div class="form-group"><label>URL del video</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-link"></i></span></div><input type="url" class="form-control" name="url" id="url" required></div><div class="form-group"><label>Duración</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-clock"></i></span></div><input type="text" class="form-control" name="tiempo" id="tiempo" value="10" required></div><small id="passwordHelpBlock" class="form-text text-muted"><ul><li>La url debe de ser de vimeo</li><li>La duración es en segundos ejemplo 10 = 10 segundos </li></ul></small></div></div>');
    
        //$("#vdo").html('<div class="form-group"><label>URL del video</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-link"></i></span></div><input type="file" class="form-control" name="url" id="url" required></div><div class="form-group"><label>Duración</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-clock"></i></span></div><input type="text" class="form-control" name="tiempo" id="tiempo" value="300" min="1" required></div><small id="passwordHelpBlock" class="form-text text-muted"><ul><li>La url debe de ser de vimeo</li><li>La duración es en segundos (dividir los minutos / 60)</li></ul></small></div></div>');
        $("#vdo").fadeIn("slow");
    }
    
    
    function botones(){
        $("#plus, #minus").off();
        $("#plus").on("click",function(){
           $("#minus").removeClass("disabled");
           var cuantos = $(this).data("imagen");
           cuantos++;
           if(cuantos <= 10){
                $(this).data("imagen",cuantos);
                $("#imagenes").append(componenteIMG(cuantos));
                $("#imagen_"+cuantos).fileinput({
                    language:'es',
                    required:true,
                    theme: 'fas',
                    disabledPreviewExtensions: ['msi','exe','com','zip','rar','app','vb','scr'],
                    allowedFileExtensions: ["jpg", "pdf", "png", "jpeg"],
                    showUpload:false,
                    browseIcon:'<i class="fa fa-folder-open"></i>',
                    browseClass:'btn btn-warning',
                    removeIcon:'<i class="fa fa-trash"></i>',
                    removeClass:'btn btn-default btn-secondary',
                    previewFileType: "image",
                    previewFileIcon:'<i class="fa fa-file-archive-o"></i>',
                });
                if(cuantos == 10){
                    $(this).addClass("disabled");
                }
           }
       });
    
       $("#minus").on("click",function(){
            $("#plus").removeClass("disabled");
            var cuantos = $("#plus").data("imagen");
            if(cuantos > 1){
                $("#"+cuantos).slideUp("slow",function(){
                    $(this).remove();
                    cuantos--;
                    $("#plus").data("imagen",cuantos);
                });
                if(cuantos == 2){
                    $(this).addClass("disabled");
                }
           }
       });
    
    }
    
    function reiniciarForm(){
        fechas();
      /*  $("#vdo,#imgs").fadeOut(function(){
            $(this).html("");
            
        });*/
        $("#titulo,#tipo").val("");
    }
    
    function cerrarModal(){
        $("#avisoModal").modal('hide');//ocultamos el modal
        $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
        $('.modal-backdrop').remove();
    }
</script>