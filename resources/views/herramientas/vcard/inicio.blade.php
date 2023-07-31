@extends('layouts.principal_vcard')
<!--@ section('tituloPagina', "Configuración de vcard para: " . Session::get('empresa')['razon_social'])-->
@section('content')
<div class="row">
	<div class="col-md-12">
        <div class="row">
            <div class="col-lg-6 col-xl-6 mx-auto">
                <div class="card-container manual-flip">
                    <div class="card my-1">
                        <div class="card">
                            @if($status==1)
                                <img src="{{asset("storage/repositorio")}}/{{$vcard_empresas[0]->idempresa}}/{{$empleado_empresa->id}}/{{$empleado_empresa->file_fotografia}}" class="card-img-top imgemp" thum alt="..." >
                                @if($vcard_empresas[0]->logo_empresa_empleado === "logoletra.png")
                                    @php
                                        dd('hola1');
                                    @endphp
                                    <img src="{{$img=asset("/img/logoletra.png")}}" class="logocard">
                                @else
                                    <img src="{{$img=asset("storage/repositorio/".$vcard_empresas[0]->idempresa."/vcard/".$vcard_empresas[0]->logo_empresa_empleado )}}" class="logocard">
                                @endif
                            @else

                                <img src="{{asset("storage/repositorio")}}/{{$vcard_empresas['idempresa']}}/{{$empleado_empresa->id}}/{{$empleado_empresa->file_fotografia}}" class="card-img-top imgemp" thum alt="..." >
                                @if($vcard_empresas['idempresa']=='218')
                                    <img src="{{$img=asset("/img/logo_ejem.png")}}" class="logocard">
                                @else
                                    <img src="{{$img=asset("/img/logoletra.png")}}" class="logocard">
                                @endif
                            @endif
                            <div class="iconos">
                                <ul class="list-group list-group-horizontal col-lg-12 p-0">
                                    <li class="list-group-item icono-top col-3 text-center btngroup">
                                        <a href="tel:{{$empleado_empresa->telefono_movil}}">
                                            <i class="fas fa-phone-volume"></i>
                                        </a>
                                    </li>
                                    <li class="list-group-item icono-top col-3 text-center px-0 btngroup">
                                        <div class="divider">
                                            <a href="mailto:{{$empleado_empresa->correo}}">
                                                <i class="fa fa-envelope"></i>
                                            </a>
                                        </div>
                                    </li>
                                    <li class="list-group-item icono-top col-3 text-center px-0 btngroup">
                                        <div class="divider2">
                                            <a href="#" onclick="rotateCard(this)" >
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                        </div>
                                    </li>
                                    <li class="list-group-item icono-top col-3 text-center btngroup">
                                        @if($status==1)
                                            <a href="{{url('herramientas/vcard/downloadVcard',['codigo' =>$codigo,'empresa'=>$vcard_empresas[0]->idempresa])}}">
                                                @else
                                                    <a href="{{url('herramientas/vcard/downloadVcard',['codigo' =>$codigo,'empresa'=>$vcard_empresas['idempresa']])}}">
                                                        @endif
                                                        <i class="far fa-address-card"></i>
                                                    </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body" id="cardbody">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"> <i class="fas fa-user"></i></div>
                                        <div class="list-details">
                                            <span>{{$empleado_empresa->nombre}} {{$empleado_empresa->apaterno}} </span>
                                            <div class="divider3">
                                                <span>{{$empleado_empresa->puesto}}</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"><i class="fas fa-phone-volume"></i></div>
                                        <div class="list-details">
                                            @if($status==1)
                                                @foreach ($vcard_empresas as $vcard_empresa)
                                                    @if($vcard_empresa->tipocontacto==2)
                                                            <?php $div_cadena=explode("#",$vcard_empresa->contacto); $telefono=$div_cadena[0]; $ext=$div_cadena[1]; ?>
                                                        <div class='divider3'>
                                                                <span>
                                                                    <a class='negro' href='tel:". dato . "'>{{$telefono}}</a>
                                                                </span>
                                                            <small class="pt-2">Ext: {{$ext}} </small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class='divider3'>
                                                        <span>
                                                            <a class='negro' href='tel:". dato . "'>{{$vcard_empresas['telefono']}}</a>
                                                        </span>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"><i class="fa fa-mobile"></i></div>
                                        <div class="list-details">
                                            <a class="negro" href="tel:{{$empleado_empresa->telefono_movil}}">
                                                <span>{{$empleado_empresa->telefono_movil}}</span>
                                            </a>
                                            <div class="divider3">
                                                <a class="wp" href="https://wa.me/{{$empleado_empresa->telefono_movil}}" target="_BLANK">
                                                    <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"> <i class="fa fa-envelope"></i></div>
                                        <div class="list-details">{{$empleado_empresa->correo}}</span></div>
                                    </li>
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"> <i class="fa fa-map-marker-alt"></i></div>
                                        <div class="list-details">
                                            @if($status==1)
                                                <span> {{$vcard_empresas[0]->direccion}} </span>
                                            @else
                                                <span> {{$vcard_empresas['direccion']}} </span>
                                            @endif
                                        </div>
                                    </li>
                                    <li class="list-group-item groupitem">
                                        <div class="list-icon"> <i class="fa fa-globe"></i></div>
                                        <div class="list-details">
                                            @if($status==1)
                                                @foreach ($vcard_empresas as $vcard_empresa)
                                                    @if($vcard_empresa->tipocontacto==1)
                                                        <div class='divider3'>
                                                                <span>
                                                                    <a class='negro' href="{{$vcard_empresa->contacto}}" target="_blank">{{$vcard_empresa->contacto}}</a>
                                                                </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else

                                            @endif
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- end front panel -->
                        <div class="back col-12 px-0">
                            <div id="map"></div>
                            <div>
                                <a class="btn btn-dark btn-block" href="#" onclick="rotateCard(this)"> <i class="fa fa-reply" aria-hidden="true"></i> Regresar</a>
                            </div>
                        </div> <!-- end back panel -->
                    </div> <!-- end card -->
                </div> <!-- end card-container -->
            </div><!--colauto-->
        </div><!--row-->
    </div>
</div>
<div class="datacolor">
@if($status==1)
   
        {{ $colorfndbtninp=$vcard_empresas[0]->colorfndbtninp }}
        {{ $recletlistinitinp=$vcard_empresas[0]->recletlistinitinp }}
        {{ $colorfndinp=$vcard_empresas[0]->colorfndinp }}
        {{ $coloricnbtninp=$vcard_empresas[0]->coloricnbtninp }}
        {{ $fndbodyvcardinp=$vcard_empresas[0]->fndbodyvcardinp }}
        {{ $recfondlistintinp=$vcard_empresas[0]->recfondlistintinp}}
        {{ $reclistinticonsinp=$vcard_empresas[0]->reclistinticonsinp }}
    
@else
    {{ $colorfndbtninp=$vcard_empresas['colorfndbtninp']}}
    {{ $recletlistinitinp=$vcard_empresas['recletlistinitinp']}}
    {{ $colorfndinp=$vcard_empresas['colorfndinp']}}
    {{ $coloricnbtninp=$vcard_empresas['coloricnbtninp'] }}
    {{ $fndbodyvcardinp=$vcard_empresas['fndbodyvcardinp'] }}
    {{ $recfondlistintinp=$vcard_empresas['recfondlistintinp']}}
    {{ $reclistinticonsinp=$vcard_empresas['reclistinticonsinp'] }}
@endif
<div>
<!--@ include('herramientas.vcard.vcard_modal')-->
@endsection
@push('css')
<style>
body{
    background-color: #FFF;
}
.imgemp{
    height: 500px;
}
.card-body{
    border: 1px solid #ddd;
}

.icono-top{
    background-color: black;
    color: white;
    font-size: 1rem;
}

.list-group{
    background-color: #000000;
}

.divider {
    display: inline-block;
    line-height:3px;
    width: 100%;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
}
.divider2 {
    display: inline-block;
    line-height:3px;
    width: 100%;
    border-right: 1px solid #ccc;
}
.divider3 {
    display: inline-block;
    line-height:15px;
    margin-left:5px;
    border-left: 1px solid #ccc;
    padding-left: 5px;
}
 .list-icon {
    display: table-cell;
    font-size: 18px;
    vertical-align: middle;
    color: #dee2e6;
    width: 30px;
    text-align: center;
}

 .list-details {
	display: table-cell;
	vertical-align: middle;
	font-weight: 600;
    color: #223035;
    font-size: 13px;
    line-height: 15px;
    padding-left:15px;
}

.list-details small{
	display: table-cell;
	vertical-align: middle;
	font-size: 12px;
	font-weight: 400;
    color: #808080;
}
a{
    display: block;
    width: 100%;
    color: #FFFFFF;
}
a:hover{
    color: #cbcbcb;
}

a.negro{
	display: inline;
    color: #000000;
}
a.negro:hover{
    color: #cbcbcb;
}

.card {
    border: 0px solid rgba(0,0,0,.125);
}
.wp{
    color: green;
    display: inline;
    font-size: 1.5rem;
    margin-left: 10px;
}
@media (max-width: 960px) {
  span {
    font-size: .7rem;
  }
}
#map {
  height: 500px;
  width:100%;
  margin-bottom: 50px;
}

.logocard{
    width: 30%;
    position: absolute;
    z-index: 1;
    margin-top: 8px;
    margin-left: 67%;
}
.datacolor{
    display: none;
}
</style>
@endpush


@push('scripts')

<script type="text/javascript">
$( document ).ready(()=>{
 
    let colorfndbtninp="{{$colorfndbtninp}}";
    let recletlistinitinp="{{$recletlistinitinp}}";
    $("body").css("background-color","{{$colorfndinp }}");
    $(".icono-top").css("background-color", colorfndbtninp);
    $(".list-group").css("background-color",colorfndbtninp);
    $(".btngroup a").css("color","{{$coloricnbtninp}}");
    $(".card-body").css("background-color","{{$fndbodyvcardinp }}");
    $(".groupitem").css("background-color", "{{$recfondlistintinp }}");
    $(".list-details").css("color",recletlistinitinp);
    $(".negro").css("color", recletlistinitinp);
    $(".list-icon i").css("color","{{$reclistinticonsinp }}");

    const geocoder = new google.maps.Geocoder();
    let resultados,	resultados_lat,resultados_long;
 
    let direccion=@if($status==1) @json($vcard_empresas[0]->direccion) @else @json($vcard_empresas['direccion']) @endif;
    geocoder.geocode({'address': direccion},(results, status)=>{
        let map;
		if (status === 'OK'){
               
            resultados = results[0].geometry.location;
            resultados_lat = resultados.lat();
            resultados_long = resultados.lng();
    
            const mapProp = {
                                center: new google.maps.LatLng(resultados_lat, resultados_long),
                                zoom: 19,
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                disableDefaultUI: true,
                            };       
            //Creando un marker en el mapa
		    const marker = new google.maps.Marker({
		        position: new google.maps.LatLng(resultados_lat, resultados_long),
		        map: new google.maps.Map(document.getElementById('map'), mapProp),
		    });
        }else{
            let mensajeError = "";
            if (status === "ZERO_RESULTS") {
                mensajeError = "No hubo resultados para la dirección ingresada.";
            }else if (status === "OVER_QUERY_LIMIT" || status === "REQUEST_DENIED" || status === "UNKNOWN_ERROR") {
                mensajeError = "Error general del mapa.";
			}else if (status === "INVALID_REQUEST") {
                mensajeError = "Error de la web. Contacte con Name Agency.";
            }
            alertify.alert(mensajeError);
        }
    });
});
const rotateCard=(btn)=>{
    
    let $card=$(btn).closest('.card-container');
    ($card.hasClass('hover'))? $card.removeClass('hover') :  $card.addClass('hover');
};
</script>
<style>
    #map {
      height: 500px;
      width:100%;
      margin-bottom: 20px;
    }
    
    /* flip the pane when hovered */
    .card-container:not(.manual-flip):hover .card,
    .card-container.hover.manual-flip .card{
        -webkit-transform: rotateY( 180deg );
    -moz-transform: rotateY( 180deg );
     -o-transform: rotateY( 180deg );
        transform: rotateY( 180deg );
    }
    
    
    .card-container.static:hover .card,
    .card-container.static.hover .card {
        -webkit-transform: none;
    -moz-transform: none;
     -o-transform: none;
        transform: none;
    }
    /* flip speed goes here */
    .card {
         -webkit-transition: -webkit-transform .5s;
       -moz-transition: -moz-transform .5s;
         -o-transition: -o-transform .5s;
            transition: transform .5s;
    -webkit-transform-style: preserve-3d;
       -moz-transform-style: preserve-3d;
         -o-transform-style: preserve-3d;
            transform-style: preserve-3d;
        position: relative;
    }
    
    /* hide back of pane during swap */
    .front, .back {
        -webkit-backface-visibility: hidden;
       -moz-backface-visibility: hidden;
         -o-backface-visibility: hidden;
            backface-visibility: hidden;
        position: absolute;
        top: 0;
        left: 0;
        background-color: #FFF;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.14);
    }
    
    /* front pane, placed above back */
    .front {
        z-index: 2;
    }
    
    /* back, initially hidden pane */
    .back {
            -webkit-transform: rotateY( 180deg );
       -moz-transform: rotateY( 180deg );
         -o-transform: rotateY( 180deg );
            transform: rotateY( 180deg );
            z-index: 3;
    }
    
    .back .btn-simple{
        position: absolute;
        left: 0;
        bottom: 4px;
    }
    /*       Fix bug for IE      */
    
    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        .front, .back{
            -ms-backface-visibility: visible;
            backface-visibility: visible;
        }
    
        .back {
            visibility: hidden;
            -ms-transition: all 0.2s cubic-bezier(.92,.01,.83,.67);
        }
        .front{
            z-index: 4;
        }
        .card-container:not(.manual-flip):hover .back,
        .card-container.manual-flip.hover .back{
            z-index: 5;
            visibility: visible;
        }
    }
    
</style>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDS6yKYVuwzrQx_ovIlwgE9Zj0M8l4Oqwg&callback"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gmaps.js/0.4.25/gmaps.js"></script>
@endpush
