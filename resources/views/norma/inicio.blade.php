@extends('layouts.empleado')
@section('tituloPagina', "Cuestionario Norma 035")
@section('content')
<p></p>
@php 
//dd($respuesta);
@endphp
@if($respuesta['estado'] == 1)
    <div id="panelCuestionario">
        <div class="page-header float-right text-right"><h5> {!!$tituloNorma!!} </h5><h6>Debe terminar el proceso dentro de este periodo de tiempo</h6>{!!$expansion!!}</div>
        <!-- SmartWizard html -->
        <div id="smartwizard" style="z-index:1" >
            <ul>
                <li><a href="#step-1">Paso 1<br /><small>Información personal</small></a></li>
                @foreach($stepInformativo as $step)
                    <li><a href="#step-{{$conInformativo}}">Paso {{$conInformativo}}<br /><small>{{$step['nombre']}}</small></a></li>
                    @php $conInformativo++;  @endphp
                @endforeach
                <li><a href="#step-{{$conInformativo}}">Final<br /><small>Proceso finalizado</small></a></li>
            </ul>
            @php $conInformativo = 2;  @endphp
            <div>
                <div id="step-1" class="text-justify">
                    <br/>
                    <h3 class="border-bottom border-gray pb-2">NOM-035-STPS-2019</h3>
                    Esta norma tiene como objetivo cuidar la salud física y mental de todos los interesados dentro de una organización, como empresa reafirmamos el compromiso que tenemos con cada uno de ustedes es por ello que te solicitamos llenar de manera adecuada dicha información.
                    
                    <h3 class="border-bottom border-gray pb-2">Instrucciones:</h3>
                    Favor de llenar correctamente la siguiente información tanto la <b>sección personal</b> como la <b>sección de cuestionarios</b> que le corresponden recuerda que la finalidad de realizarlos es aplicar mejoras para tu entorno organizacional y aumentar el compromiso laboral de todos los empleados.
                </div>
                @foreach($stepInformativo as $step)
                    <div id="step-{{$conInformativo}}" class="stp">
                        <br/>
                        <h3 class="border-bottom border-gray pb-2">{{$step['nombre']}}</h3>
                        <div>El siguiente paso es responder la <b>{{$step['nombre']}}</b>. </div>
                    </div>
                    @php $conInformativo++;  @endphp
                @endforeach
                <div id="step-{{$conInformativo}}" class="stp">
                    <br/>
                    <h3 class="border-bottom border-gray pb-2">Proceso finalizado</h3>
                    Ha concluido el proceso con éxito, agradecemos su valiosa participación para poder cumplir con lo requerido en la norma 035.
                </div>
            </div>
        </div>

        <!-- Tarjetas   -->
        <br/>
        <div class="card-columns" style="">
            @foreach($datosTarjeta as $tarjeta) 
                <div class="card text-center bg-warning mb-3" style="max-width: 20rem;">
                    <div class="card-body" style="min-height:100%;">
                        <h3 class="card-title" style="font-weight: 800">{{$tarjeta['encabezado']}}</h3>
                        <p class="card-text">{{$tarjeta['descripcion']}}</p>
                        <button data-enlace="{{$tarjeta['enlace']}}" data-inftrabajador="{{$tarjeta['informacion_trabajador']}}" data-cuestionariotrabajador="{{$tarjeta['cuestionario_trabajador']}}" data-cuestionario="{{$tarjeta['cuestionario']}}" class="botonCuestionario btn {{$tarjeta['clases']}} btn{{$tarjeta['cuestionario']}}" {{$tarjeta['desabilitado']}}>{{$tarjeta['textoBoton']}}</button>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <div class="row" style="padding-top:10px;width:100%;">
                <div class="col-md-12">
                    <div class="jumbotron">
                        <h1 class="display-4">{{$respuesta['tituloMsj']}}</h1>
                        <p class="lead">{{$respuesta['msj']}}</p>
                        <hr class="my-4">
                    </div>
                </div>
        </div>

    @endif
    <form action="" method="post" id="form_cuestionario">
            @csrf
            <input type="hidden" value="" name="informacion_trabajador" id="informacion_trabajador" />
            <input type="hidden" value="" name="cuestionario_trabajador" id="cuestionario_trabajador" />
            <input type="hidden" value="" name="cuestionario" id="cuestionario" />
            <input type="hidden" value="{{$estadoFinal}}" name="estadoFinal" id="estadoFinal" />
    </form>
</div>

@endsection

@push('css')
<link href="{{ asset('css/steps.css') }}" rel="stylesheet">
<link href="{{ asset('css/steps/smart_wizard.css') }}" rel="stylesheet" type="text/css" />

<!-- Optional SmartWizard theme -->
<link href="{{ asset('css/steps/smart_wizard_theme_circles.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')

<script src="{{ asset('js/steps/modernizr-2.6.2.min.js') }}"></script>
<script src="{{ asset('js/steps/js.cookie.min.js') }}"></script>
<script src="{{ asset('js/steps/jquery.steps.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script type="text/javascript" src="{{ asset('js/steps/jquery.smartWizard.min.js') }}"></script>

<script>

var edoFinal = {{$estadoFinal}};
if(edoFinal == 4){
    edoFinal--;
}else if(edoFinal == 5){
    edoFinal = edoFinal -2;
}
//alert(edoFinal);
$(function(){  
            // Smart Wizard
            $('#smartwizard').smartWizard({
                    selected: edoFinal,
                    anchorClickable:false, 
                    contentCache:false,
                    keyNavigation:false,
                    backButtonSupport:false,
                    theme: 'circles',
                    transitionEffect:'fade',
                    showStepURLhash: true,
                    toolbarSettings: {
                        showNextButton:false,
                        showPreviousButton:false,
                    },
                    anchorSettings : { 
                        anchorClickable : false , // Activar / Desactivar navegación de anclaje  
                        enableAllAnchors : false , // Activa todos los anclajes en los que se puede hacer clic en todo momento  
                        enableAnchorOnDoneStep : false // Habilita / deshabilita la navegación de pasos realizados  
                    },
            });




    $(".botonCuestionario").on("click",function(){
        $("#form_cuestionario").attr('action',$(this).data("enlace"));
        $("#informacion_trabajador").val($(this).data("inftrabajador"));
        $("#cuestionario_trabajador").val($(this).data("cuestionariotrabajador"));
        $("#cuestionario").val($(this).data("cuestionario"));
        $("#form_cuestionario").submit();
    });

    var height = 0;
    $(".card").each(function(){
        if($(this).height()>height){
            height = $(this).height();
        }
    });
    $(".card .card-body").css("min-height", height+"px");
   
   $("#panelCuestionario").fadeIn("slow");
});



    //alert(edoFinal);
    if(edoFinal == 0){
   //     alert("0");
    }else if(edoFinal == 1){
        $(".btn2, .btn3").attr("disabled","disabled").removeClass("botonCuestionario");
    }else if(edoFinal == 2){
    //    alert("2");
        $(function(){$("li.active").removeClass("active").addClass("done");});
    }else if(edoFinal == 3){
        console.log("3");
        $(function(){$("li.active").removeClass("active").addClass("done");});
        
    }

</script>
@endpush


@push('css')
<style>
    .panel-btn{
        width:32%;
        display:inline-block;
        vertical-align: top;
        text-align: center;
        border-radius:15px;
        margin: 1% .5%;
        padding:20px 10px;
    }

    .panel-btn p{
        padding:5% 5%;
    }

    .cuestionario_activo{
        border-color: 3px solid #F0C018;
        
    }
    .cuestionario_comenzado{
        background-color:#F0C018;
    }
    .cuestionario_completado{
        background-color:#F0C018;
    }
    
    .current{
        background:#f0c018 !important;
    }
    #content{
        display:none;
    }
</style>
@endpush