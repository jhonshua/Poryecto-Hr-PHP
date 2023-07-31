@include('norma.cuestionarios.cuestionario_form')
<script>
    @if(!empty($respuestas) && count($respuestas))
        @foreach($respuestas as $respuesta)
    
            $("input.{{$respuesta['pivot']['idpregunta']}}[value={{$respuesta['pivot']['valor']}}]").attr("checked", true);
            $("input.{{$respuesta['pivot']['idpregunta']}}").attr("disabled","disabled");
            
            @if($respuesta['pivot']['idpregunta'] == 73 || $respuesta['pivot']['idpregunta'] == 74)
                
                let bloque = $("input[name={{$respuesta['pivot']['idpregunta']}}]").data("bloque");
                let pivot = "@php echo $respuesta['pivot']['valor'] @endphp";
                estadoBloqueCondicional(bloque,pivot);
            @endif
        @endforeach
    @endif

    form = $("#cuestionario{{$cuestionario_trabajador->id}}").show();
 
    form.steps({

        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        enableContentCache:true,
        saveState: true,
        labels: {
            cancel: "Cancelar",
            finish: "Terminar",
            next: "Siguiente",
            previous: "Anterior",
            loading: "Cargando ..."
        },
        onInit:function(event, currentIndex){

            var index = readCookie("jQu3ry_5teps_St%40te_cuestionario{{$cuestionario_trabajador->id}}");
            if(typeof(index) != 'undefined' && index != null){
                tamanioWizard(index);
            }    
        },
        onStepChanging: function (event, currentIndex, newIndex) 
        {  
            //return false;
            // No permite regresar
            if (currentIndex > newIndex) {  return false;}
            // Needed in some cases if the user went back (clean up)
            if (currentIndex < newIndex){
                // Quitar etiqueta de error
                form.find(".body:eq(" + newIndex + ") label.error").remove();
                form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
            }
            form.validate().settings.ignore = ":disabled,:hidden";
            if(form.valid()){

                let terminar = 0;

                if({{$tipo_cuestionario}} == 1 && currentIndex == 0){
                    
                    let total = 0;
                    let respuesta_no = 0;
                    $(".p0:checked").each(function(i, element){
                        
                        total++;
                        if($(this).val() == 0){
                            respuesta_no++;
                        }
                    });
                    if(total == respuesta_no){

                        terminar = 1;
                              
                        swal({
                            
                            title: `No es necesario responder las demás secciones, su cuestionario ha concluido`,
                            text: "Preparando la ejecución !",
                            icon: "warning",
                            buttons:  ["Cancelar", true],
                            dangerMode: true,
                        
                        }).then((willDelete) => {
                            if (willDelete) {
                                let  url2 = "{{route('norma.implementacion.lista.empleados.admin.llenar.cuestionarios')}}";
                                $.ajax({
                                    url: url2,
                                    type: "POST",
                                    dataType: 'html',
                                    data: {'informacion_trabajador':'{{$cuestionario_trabajador->idinformacion_trabajador}}','_token':$('meta[name="csrf-token"]').attr('content')},
                                    beforeSend: function(){  
                                       // $("#cargando").css("display","block"); 
                                    },
                                    success: function( respuesta ){
                                        $( "#divTrabajadoresCuestionarios" ).slideUp( "slow", function() {
                                            $("#divTrabajadoresCuestionarios").html('').append('<h1>Cuestionario</h1>');
                                            $("#divTrabajadoresCuestionarios").append(respuesta);
                                            $("#divTrabajadoresCuestionarios" ).slideDown("slow");
                                        });
                                    
                                    }
                                }).done(function(){
                                    //$("#cargando").css("display","none");
                                });
                            }
                        });
                    }
                }

                enviarRespuestas(currentIndex,terminar);
                return true;
            }
            return false;
            
        },
        onStepChanged: function (event, currentIndex, priorIndex)
        {  
            //alert(currentIndex);
            if({{$tipo_cuestionario}} == 1 && currentIndex == 1){

                let total = 0;
                let respuesta_no = 0;
                
                $(".p0:checked").each(function(i, element){
                    total++;
                    if($(this).val() == 0){
                        respuesta_no++;
                    }
                });
                
                if(total == respuesta_no){
                    $("#cuestionario{{$cuestionario_trabajador->id}} input").attr('disabled','disabled');
                    form.steps("next");
                    form.steps("next");
                }
            }
            // Used to skip the "Warning" step if the user is old enough.
            tamanioWizard(currentIndex);
            
            // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
            if (currentIndex === 2 && priorIndex === 3)
            {
                // form.steps("previous");
            }
        },
        onFinishing: function (event, currentIndex)
        {
            form.validate().settings.ignore = ":disabled";
            if(form.valid()){
                enviarRespuestas(currentIndex,1);
                return true;
            }
            return false;
        },
        onFinished: function (event, currentIndex)
        {
            swal("El cuestionario se guardó con éxito.", {
                icon: "success",
            });
                
                //console.log('{{$cuestionario_trabajador->idinformacion_trabajador}}');
            let url2 = "{{route('norma.implementacion.lista.empleados.admin.llenar.cuestionarios')}}";
            $.ajax({
                url: url2,
                type: "POST",
                dataType: 'html',
                data: {'informacion_trabajador':'{{$cuestionario_trabajador->idinformacion_trabajador}}','_token':$('meta[name="csrf-token"]').attr('content')},
                async: false,
                beforeSend: function(){  
                        $("#cargando").css("display","block"); 
                    },
                success: function( respuesta ) {
                    $( "#divTrabajadoresCuestionarios" ).slideUp( "slow", function() {
                        $("#divTrabajadoresCuestionarios").html('').append('<h1>Cuestionario</h1>');
                        $("#divTrabajadoresCuestionarios").append(respuesta);
                        $("#divTrabajadoresCuestionarios" ).slideDown("slow");
                        //table.ajax.reload();
                    });
                    
                }
            }).done(function(){
                $("#cargando").css("display","none");
            });

        },
        onCanceled: function (event) { 
            window.location="{{ route('empleado.norma') }}";
        },
    }).validate({
        
        errorClass: "invalido",
        errorElement: "span",
        errorPlacement: function(error, element) {
            error.appendTo( $('label[for='+element.attr("name")+']') );
        },
    });

 $(".requerido").each(function(){
    $(this).rules( "add", {
        required: true,
        messages:{
            required: " respuesta requerida"
        }
    });
 });


@if($tipo_cuestionario == 2)
    $(".wizard > .content").css("min-height","38em");
@endif

function enviarRespuestas(index,final){

    let respuesta = new Array();
    let activos = 0;
    //alert(index);
    $(".p"+index+":checked").each(function(i, element){
        //console.log($(this).prop("disabled"));
        if(!$(this).prop("disabled")){
            respuesta.push( {'idcuestionario_trabajador':$("#cuestionario_trabajador").val(),'idpregunta':$(this).data("pregunta"),'valor':$(this).val()});
            $("input."+$(this).data("pregunta")).attr("disabled","disabled");
            //console.log($(this).data("pregunta")+ "  " +$(this).val()); 
            activos++;
        }
    });
    
    if(activos > 0){
        let bloque = {"cuestionario_trabajador":$("#cuestionario_trabajador").val(),"respuesta":respuesta,"final":final};
        guardaBloque(bloque);
    }
}

function guardaBloque(bloque){

    $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });

    let url = "{{route('empleado.norma.cuestionario.guarda')}}";
    
    $.ajax({
        url: url,
        type: "POST",
        data: bloque,
        async: true,
        beforeSend: function(){  
            $("#cargando").css("display","block"); 
        },
        success: function( response ) {
            if(response.ok == 1) {
                   
                swal("El bloque se almacenó correctamente.", {
                    icon: "success",
                });
                
            }else {
                swal("Ocurrió un error. Intente nuevamente.", {
                    icon: "error",
                });
                return false;
            }
        }
    }).done(function(){
        $("#cargando").css("display","none");
    });
}

function tamanioWizard(currentIndex){
    //console.log(currentIndex+" indice");
   // alert(currentIndex + " -- " + {{$tipo_cuestionario}});
    @if($tipo_cuestionario == 2)

            if (currentIndex == 1 || currentIndex == 6){ $(".wizard > .content").css("height","27em");
            }else if(currentIndex == 5){  $(".wizard > .content").css("height","65em");   }
    
    @elseif($tipo_cuestionario == 3)

            if (currentIndex == 10){  $(".wizard > .content").css("min-height","44em");
            }else if( currentIndex == 4){ $(".wizard > .content").css("min-height","37em");
            }else if( currentIndex == 5){  $(".wizard > .content").css("min-height","35em");
            }else if( currentIndex == 11){  $(".wizard > .content").css("min-height","40em");  
            }else if( currentIndex == 12){  $(".wizard > .content").css("min-height","30em"); }
    @endif
}

function readCookie(name) {
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + name.replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
}



$('input.condicional[type="radio"]').on("change",function(){

    let bloque = $(this).data("bloque");
    estadoBloqueCondicional(bloque, $(this).val());
    
});

function estadoBloqueCondicional(bloque, estado){
    
    if(estado == 1) {
        $(".reqpre"+bloque).each(function(){
            $(this).attr("disabled",false);
        });
    }else{
        $(".reqpre"+bloque).each(function(){
            $(this).prop('checked',false).attr("disabled","disabled");
        });
    }
}
//console.log("{{$cuestionario_trabajador->id}}");
index = readCookie("jQu3ry_5teps_St%40te_cuestionario{{$cuestionario_trabajador->id}}");
if(typeof(index) != 'undefined' && index != null){
    tamanioWizard(index);
}

</script>
