@extends('layouts.empleado')
@section('tituloPagina', "Cuestionario Norma 035")

@section('content')

@include('norma.cuestionarios.cuestionario_form')

@endsection


@push('css')
<link href="{{ asset('css/steps.css') }}" rel="stylesheet">
<style>
    .invalido{
        color:#EE4A30;
        font-size:12px;   
    }
     .table th, .table td {
    padding: 0.20rem;
    }

    .wizard > .content{
        min-height: 0em !important;
        padding: 1rem 0rem;
    }

    .wizard > .steps .current a, .wizard > .steps .current a:hover, .wizard > .steps .current a:active{


    }

    .wizard > .steps a, .wizard > .steps a:hover, .wizard > .steps a:active{
       font-size: 14px;
    }

</style>
@endpush

@push('scripts')
    <script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/validate/jquery-validate-adicional.js') }}"></script>
<script src="{{ asset('js/steps/modernizr-2.6.2.min.js') }}"></script>
<script src="{{ asset('js/steps/js.cookie.min.js') }}"></script>
<script src="{{ asset('js/steps/jquery.steps.min.js') }}"></script>

<script>


@if(count($respuestas))
    @foreach($respuestas as $respuesta)
        $("input.{{$respuesta['pivot']['idpregunta']}}[value={{$respuesta['pivot']['valor']}}]").attr("checked", true);
        $("input.{{$respuesta['pivot']['idpregunta']}}").attr("disabled","disabled");
        @if($respuesta['pivot']['idpregunta'] == 73 || $respuesta['pivot']['idpregunta'] == 74){
            
            var bloque = $("input[name={{$respuesta['pivot']['idpregunta']}}]").data("bloque");
            estadoBloqueCondicional(bloque, {{$respuesta['pivot']['valor']}});
        }
        @endif
    @endforeach
@endif


var form = $("#cuestionario{{$cuestionario_trabajador->id}}").show();
 
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
    //    var img = "{{asset('img/spinner.gif')}}";
    //    $("#cuestionario{{$cuestionario_trabajador->id}} a[href$='next']").html("<img src='"+img+"' style='width:20px' />").attr("disabled","disabled");
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
            var terminar = 0;
            if({{$tipo_cuestionario}} == 1 && currentIndex == 0){
                var total = 0;
                var respuesta_no = 0;
                $(".p0:checked").each(function(i, element){
                    total++;
                    if($(this).val() == 0){
                        respuesta_no++;
                    }
                });
                if(total == respuesta_no){
                    terminar = 1;
                    alertify.alert('Cuestionario concluido', 'No es necesario responder las siguientes secciones. Usted ha concluido el cuestionario con éxito. Agradecemos su apoyo para llenar los cuestionarios. Una vez se tengan los resultados, su empresa le proporcionará más detalles. Gracias.', function(){ window.location="{{ route('empleado.norma') }}"; });
                }
            }

           enviarRespuestas(currentIndex,terminar);
           return true;
        }
        //$("a[href='#next']").html("Siguiente").attr("disabled",false);
        return false;
         
     },
     onStepChanged: function (event, currentIndex, priorIndex)
     {  
         //alert(currentIndex);
         if({{$tipo_cuestionario}} == 1 && currentIndex == 1){
            var total = 0;
            var respuesta_no = 0;
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
       // alertify.alert('Cuestionario concluido', 'Ha concluido el cuestionario con éxito, agradecemos su valiosa participación para poder cumplir con lo requerido en la norma 035', function(){ window.location="{{ route('empleado.norma') }}"; });
        alertify.alert('Cuestionario concluido', 'Usted ha concluido el cuestionario con éxito. Agradecemos su apoyo para llenar los cuestionarios. Una vez se tengan los resultados, su empresa le proporcionará más detalles. Gracias.', function(){ window.location="{{ route('empleado.norma') }}"; });

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

 $( ".requerido" ).each(function(){
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
        var respuesta = new Array();
        var activos = 0;
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
            var bloque = {"cuestionario_trabajador":$("#cuestionario_trabajador").val(),"respuesta":respuesta,"final":final};
            guardaBloque(bloque);
        }
}


function guardaBloque(bloque){
    $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
      });
      var url = "{{route('empleado.norma.cuestionario.guarda')}}";
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
                    alertify.success('El bloque se almacenó correctamente.');
            } else {
                    alertify.alert('Error', 'Ocurrió un error. Intente nuevamente.');
                    return false;
            }
        }
      }).done(function(){
            $("#cargando").css("display","none");
      });
}

function tamanioWizard(currentIndex){
    //alert(currentIndex + " -- " + {{$tipo_cuestionario}});
    @if($tipo_cuestionario == 2)
            if (currentIndex == 1 || currentIndex == 6){    $(".wizard > .content").css("min-height","27em");
            }else if(currentIndex == 5){    $(".wizard > .content").css("min-height","50em");   }
    @elseif($tipo_cuestionario == 3)
            if (currentIndex == 10){  $(".wizard > .content").css("min-height","44em");
            }else if( currentIndex == 4){ $(".wizard > .content").css("min-height","37em");
            }else if( currentIndex == 5){  $(".wizard > .content").css("min-height","30em");
            }else if( currentIndex == 11){  $(".wizard > .content").css("min-height","40em");  
            }else if( currentIndex == 12){  $(".wizard > .content").css("min-height","30em");   }
    @endif
}

function readCookie(name) {
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + name.replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
}



$('input.condicional[type="radio"]').on("change",function(){
    var bloque = $(this).data("bloque");
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

</script>
@endpush