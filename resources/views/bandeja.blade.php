<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet" />
<style type="text/css">
    .back-badge{
        background-color: #fbba00;
        color:white; }

    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active{
        font-size: 20px;
        font-weight: 600;
        border-left-color: #fbba00;
        border-right-color: #fbba00;
        border-top-color: #fbba00; }

    a .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link{
        font-size: 20px;
        font-weight: 600;
        color: #BABABA; }

    .nombre-cumple{
        margin-top: 10px;
        font-weight: bold; }

    .progress{
        width: 80px; height: 80px; line-height: 120px; background: none; margin: 0 auto; box-shadow: none;
        position: relative; }

    .progress:after{
        content: ""; width: 100%; height: 100%; border-radius: 50%; border: 12px solid #fff; position: absolute;
        top: 0; left: 0; }

    .progress > span{
        width: 50%; height: 100%; overflow: hidden; position: absolute; top: 0; z-index: 1; }

    .numero{
        font-size: 28px !important; margin-left: 25px; height: 90px !important; }

    .texto{
        border: 0px !important; width: 165px; margin-top: 50px !important; }

    .progress .progress-left{
        left: 0; }

    .progress .progress-bar{
        width: 100%; height: 100%; background: none; border-width: 12px; border-style: solid; position: absolute; top: 0; }

    .progress .progress-left .progress-bar{
        left: 100%; border-top-right-radius: 290px; border-bottom-right-radius: 290px; border-left: 0;
        -webkit-transform-origin: center left; transform-origin: center left; }

    .progress .progress-right{
        right: 0; }

    .progress .progress-right .progress-bar{
        left: -100%; border-top-left-radius: 160px; border-bottom-left-radius: 160px; border-right: 0;
        -webkit-transform-origin: center right; transform-origin: center right; animation: loading-1 1.8s linear forwards;}

    .progress .progress-value{
        width: 90%; height: 90%; border-radius: 50%; font-size: 24px; color: #fff; line-height: 135px;
        text-align: center; position: absolute; top: -3%;left: 3%; }

    .progress.blue .progress-bar{
        border-color: #049dff; }

    .progress.blue .progress-left .progress-bar{
        animation: loading-1 1.8s linear forwards 1.8s; }

    .progress.yellow .progress-bar{
        border-color: yellow; }

    .progress.yellow .progress-left .progress-bar{
        animation: loading-1 1.8s linear forwards 1.8s; }

    .progress.red .progress-bar{
        border-color: red; }

    .progress.red .progress-left .progress-bar{
        animation: loading-1 1.8s linear forwards 1.8s; }

    .progress.orange .progress-bar{
        border-color: orange; }

    .progress.orange .progress-left .progress-bar{
        animation: loading-1 1.8s linear forwards 1.8s; }

    .progress.pink .progress-bar{
        border-color: #ed687c; }

    .progress.pink .progress-left .progress-bar{
        animation: loading-4 1.8s linear forwards 1.8s; }

    .progress.green .progress-bar{
        border-color: #fbba00; }

    .progress.green .progress-left .progress-bar{
        animation: loading-1 1.8s linear forwards 1.8s; }

    .numero{
        background-color: transparent !important; border: 0px !important; width: 145px !important; }

    @keyframes loading-1{
        0%{
            -webkit-transform: rotate(0deg); transform: rotate(0deg); }

        100%{
            -webkit-transform: rotate(180deg); transform: rotate(180deg); }
    }
    @keyframes loading-2{
        0%{
            -webkit-transform: rotate(0deg); transform: rotate(0deg); }

        100%{
            -webkit-transform: rotate(144deg); transform: rotate(144deg); }
    }
    @keyframes loading-3{
        0%{
            -webkit-transform: rotate(0deg); transform: rotate(0deg); }

        100%{
            -webkit-transform: rotate(90deg); transform: rotate(90deg); }
    }
    @keyframes loading-4{
        0%{
            -webkit-transform: rotate(0deg); transform: rotate(0deg); }

        100%{
            -webkit-transform: rotate(36deg); transform: rotate(36deg); }
    }
    @keyframes loading-5{
        0%{
            -webkit-transform: rotate(0deg); transform: rotate(0deg); }

        100%{
            -webkit-transform: rotate(126deg); transform: rotate(126deg); }
    }
    @media only screen and (max-width: 990px){
        .progress{ margin-bottom: 20px; }.footer{margin-top: 200px; }
    }

    svg.radial-progress {
        height: auto;
        max-width: 200px;
        padding: 0em;
        transform: rotate(-90deg);
        width: 100%; }

    svg.radial-progress circle {
        fill: rgba(0, 0, 0, 0);
        stroke: #fff;
        stroke-dashoffset: 219.91148575129;
        /* Circumference */
        stroke-width: 10; }

    svg.radial-progress circle.incomplete {
        opacity: 0.25; }

    svg.radial-progress circle.complete {
        stroke-dasharray: 219.91148575129;
        /* Circumference */ }

    svg.radial-progress text {
        fill: #000;
        font: 400 1.2em/1 'Oswald', sans-serif;
        text-anchor: middle; }

    /*** COLORS ***/

    svg.radial-progress-success circle {
        stroke: #a2ed56; }

    svg.radial-progress-warning circle {
        stroke: #f0c018; }

    svg.radial-progress-danger circle {
        stroke: #bd2130!important; }

    .calendar{
        color: black;
        margin: 10px auto;
        background: white;
        padding: 60px 27px 80px 23px;
        width: 100%;
        max-width: 600px;
        height: 325px;
        border-radius: 10px;
        position: relative;  }

    .calendar__title{
        text-align: center; }

    .calendar--day-view{
        position: absolute;
        border-radius: 3px;
        top: -2.5%;
        left: -2.5%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,1);
        box-shadow: 3px 12px 5px rgba(2,2,2,0.16);
        z-index: 2;
        overflow: hidden;
        transform: scale(0.9) translate(30px,30px);
        opacity: 0;
        visibility: hidden;
        display: none;
        align-items: flex-start;
        flex-wrap: wrap; }

    .day-view-content{
        color: #222;
        width: 100%;
        padding-top: 55px; }

    .day-highlight, .day-add-event{
        padding: 8px 10px;
        margin: 12px 15px;
        border-radius: 4px;
        background: #e7e8e8;
        color: #222;
        font-size: 14px;
        font-weight: 600;
        font-family: "Avenir", sans-serif; }

    @keyframes shake {
        20%, 60%{
            transform: translateX(4px); }
        40%, 80%{
            transform: translateX(-4px); }
    }

    .calendar--day-view-active{
        animation: popIn 200ms 1 forwards;
        visibility: visible;
        display: flex;
        transition: visibility 0ms; }

    .calendar--view{
        display: flex;
        flex-wrap: wrap;
        align-content: center;
        justify-content: flex-start;
        width: 100%; }

    .cview__month{
        width: 100%;
        text-align: center;
        font-weight: 800;
        font-size: 22px;
        font-family: 'Avenir', sans-serif;
        padding-bottom: 20px;
        color: #222;
        text-transform: uppercase;
        display: flex;
        flex-wrap: nowrap;
        align-items: baseline;
        justify-content: space-around;
        margin-top: -42px; }

    .cview__month-last,.cview__month-next,.cview__month-current{
        width: 33.33333%;
        text-align: center;
        font-size: 16px;
        cursor: pointer;
        color: #222; }

    .cview__month-last:hover,.cview__month-next:hover{
        color: #fbba00; }

    .cview__month-current{
        font-size: 19px;
        cursor: default;
        animation: popIn 200ms 1 forwards;
        transform: translateY(20px);
        opacity: 0;
        position: relative;
        color:#fbba00; }

    .cview__month-reset{
        animation: none; }

    .cview__month-activate{
        animation: popIn 100ms 1 forwards; }

    .cview--spacer, .cview__header, .cview--date{
        width: 14.28571428571429%;
        max-width: 14.28571428571429%;
        padding: 5px;
        box-sizing: border-box;
        position: relative;
        text-align: center;
        overflow: hidden;
        text-overflow: clip;
        font-size: 13px;
        font-weight: 700; }

    .cview--date{
        font-size: 14px;
        font-weight: 400;
        cursor: pointer; }

    .has-events::after{
        border-radius:100%;
        animation: popIn 200ms 1 forwards;
        background: rgba(255,255,255,0.95);
        transform: scale(0);
        content: '';
        display: block;
        position: absolute;
        width: 8px;
        height: 8px;
        top: 8px;
        left: 12px; }

    .cview--date:hover::before{
        background: rgba(255,255,255,0.2);}

    .cview--date.today{
        color: #fbba00;  
        font-weight: bold; }

    .cview--date.today::before{
        animation: popIn 200ms 1 forwards;
        background: rgba(255,255,255,0.2);
        transform: scale(0); }

    @keyframes popIn{

      100%{
        transform: scale(1);
        opacity: 1;
      }
    }

</style>

<div class="container">

@include('includes.header',['title'=>'Bandeja de notificaciones',
        'subtitle'=>'Avisos', 'img'=>'img/avisos.png',
        'route'=>'home'])
   
    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
{{-- cambios verificar juan --}}
    @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif

{{-- MErge master cambios --}}
    <div class="row">
        <div class="col-md-8">
{{--             <div class="article border box-shadow">
                <div class="row">
                    <h4>Pendientes</h4>
                </div>
                <div class="table-responsive">
                    @if(count($pendientes)== 0)
                        <div class="alert alert-danger text-center" role="alert" v-if="pendientes.length == 0">
                            <h3>No hay pendientes aún</h3>
                        </div>
                    @else

                        <table class="table  w-100 pendientes" id="pendientes" >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pendiente</th>
                                    <th>Descripcion</th>
                                    <th>Archivo</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendientes as $d)
                                    <tr>
                                        <td>{{ $d->id }} </td>
                                        <td>{{ $d->titulo }}</td>
                                        <td>{{ $d->descripcion }}</td>
                                        <td>
                                            @if($d->archivo != "" )
                                                <a href="{{$d->archivo}}" class="btn btn-sm btn-dark" target="_blank"><i class="fas fa-eye"></i> Ver</a>
                                            @else
                                                <a class="btn btn-secondary btn-sm vern" href="#" onclick="subir({{$d->id}})"><i class="fas fa-upload"></i> Subir</a>
                                            @endif
                                         </td>
                                        <td>
                                            @if($d->estatus =="1")
                                                <span class="badge badge-success" id="tagP{{$d->id}}">Completado</span>
                                            @else
                                                <span class="badge badge-warning" id="tagP{{$d->id}}">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                             @if($d->estatus =="0")
                                                <button class="btn btn-success btn-sm completar" alt="Marcar completado" title="Marcar Completado" data-id="{{ $d->id }}" id="btnPR{{$d->id}}"><i class="fas fa-check-circle"></i> Completado</button>&nbsp;
                                            @endif
                                        </td>
                                    </tr>
                                 @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
 --}}
      @php

            function mesnum($numero)
            {
                if($numero == "01"){ $month = "Enero"; } if($numero == "02"){ $month = "Febrero"; } if($numero == "03"){ $month = "Marzo"; }
                if($numero == "04"){ $month = "Abril"; } if($numero == "05"){ $month = "Mayo"; } if($numero == "06"){ $month = "Junio"; }
                if($numero == "07"){ $month = "Julio"; } if($numero == "08"){ $month = "Agosto"; } if($numero == "09"){ $month = "Septiembre"; }
                if($numero == "10"){ $month = "Octubre"; } if($numero == "11"){ $month = "Noviembre"; } if($numero == "12"){ $month = "Diciembre"; }

                return $month;
            }

            $fecha = date_default_timezone_set('America/Mexico_City');
            $dia = date('d');
            $day = date("l");
            $mes = date("m");
            $fechas = mesnum($mes);


        @endphp


        @php
            $total = array();
            foreach($empleados as $empleado){
                if(!$empleado->contratoActivo && $empleado->existioContrato){
                    $total[] = $empleado->id; 
                }
            }
        @endphp


            <div class="article border mt-3">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Avisos 
                        @if (count($avisos) != 0)
                            <span class="badge back-badge">{{count($avisos)}}</span>
                        @endif
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Pendientes
                        @if (count($pendientes) != 0)
                            <span class="badge back-badge">{{count($pendientes)}}</span>
                        @endif
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#contratos" role="tab" aria-controls="profile" aria-selected="false">Contratos 
                        @if (count($total) != 0)
                            <span class="badge back-badge">{{count($total)}}</span>
                        @endif
                    </a>
                  </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    @if (count($avisos) > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>

                            @foreach ($avisos as $aviso)
                                <tr id="{{ $aviso->id }}">
                                    <td>{{ $aviso->descripcion }}</td>
                                    <td>{{ $aviso->fecha_creacion }}</td>
                                    <td></td>
                                    <td>
                                        @if ($aviso->tipo == 'CN')
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <a class="correcta"alt="Nomina Correcta" title="Nomina Correcta" data-ref="{{ $aviso->referencia }}" id="{{ $aviso->id }}">
                                                        <img src="{{asset('img/empleado-documentos.png')}}" alt="Nomina Correcta" style="width: 25px;">
                                                    </a>

                                                </div>
                                                <div class="col-md-4">
                                                    <a class="cancelar"  data-toggle="modal" data-target="#cancelarnomina" alt="Nomina Correcta" title="Nomina Incorrecta" data-ref="{{ $aviso->referencia }}" id="{{ $aviso->id }}">
                                                        <img src="{{asset('img/icono-borrar.png')}}" alt="Nomina Incorrecta" style="width: 25px;">
                                                    </a>
                                                </div>  
                                                <div class="col-md-4">
                                                    <a  href="{{ route('calculo.nomina') }}" class="vern" alt="Nomina Correcta" title="Ver nomina" data-ref="{{ $aviso->referencia }}" id="{{ $aviso->id }}">
                                                        <img src="{{asset('img/ver-documentos-empleado.png')}}" alt="Ver nomina" style="width: 25px;">
                                                    </a>                                                
                                                </div>

                                            </div>
                                        @else
                                            <a class="aceptar" id="{{ $aviso->id }}">
                                                <img src="{{asset('img/empleado-documentos.png')}}" alt="Enterado" style="width: 25px;">
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="text-black-50 mt-4 font-weight-bold">NO HAY AVISOS PENDIENTES</div>
                    @endif
                     

                    <div class="modal fade" id="cancelarnomina" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Cancelar Nómina</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col m2 s12"></div>
                                        <div class="col m8 s12">
                                            <form method="post" id="nomina-cancelar-form" action="{{ route('bandeja.cerrarnomina') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="id" id="id_cancelar" value="">
                                                <input type="hidden" name="ref" id="ref_cancelar" value="">
                                                <label for="razon_cancelar">Por favor escribe el motivo por el que cancelas la nómina</label>
                                                <input type="text" class="input-style center" name="razon" id="razon_cancelar" value="">
                                                <input type="hidden" name="op" value="1">
                                            </form>
                                        </div>
                                        <div class="col m2 s12"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="add_cancelar_nomina" class="btn button-style">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                  </div>
                  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <span style="color: #807C7C;">* Nota: En esta seccion se muestran los empleados a los que les falta información</span>
                        </div>
                    </div>
                    @if (count($pendientes) > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pendiente</th>
                                    <th>Nombre empleado</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            @foreach ($pendientes as $pend)
                                <tr id="{{ $pend->id }}">
                                    <td>{{ $pend->id }}</td>
                                    <td class="text-center">
                                        
                                        <svg class="radial-progress @if($pend->porcentaje >= 95) {{'radial-progress-success'}} @elseif ($pend->porcentaje < 95 && $pend->porcentaje >= 70)  {{'radial-progress-warning'}} @else {{'radial-progress-danger'}} @endif" data-percentage="{{ $pend->porcentaje }}" viewBox="0 0 80 80" width="30px">
                                            <circle class="incomplete" cx="40" cy="40" r="35"></circle>
                                            <circle class="complete" cx="40" cy="40" r="35" style="stroke-dashoffset: 219.91148575129;"></circle>
                                            <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">{{ $pend->porcentaje }}%</text>
                                        </svg>
                                    </td>
                                    <td>{{ $pend->nombre }} {{ $pend->apaterno }} {{ $pend->amaterno }}</td>
                                    <td><span class="badge badge-warning" id="tagP">Pendiente</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('empleados.editar',  $pend->id) }}">
                                            <img src="{{asset('img/ver-documentos-empleado.png')}}" title="Ver empleado" alt="Ver empleado" style="width: 32px;">
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="text-black-50 mt-4 font-weight-bold">NO HAY PENDIENTES</div>
                    @endif
                     
                  </div>

                    <div class="tab-pane fade show" id="contratos" role="tabpanel" aria-labelledby="home-tab">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID empleado</th>
                                    <th>Nombre</th>
                                    <th>Estatus</th>
                                    <th>Fecha vencimiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            @foreach ($empleados as $empleado)
                                @if(!$empleado->contratoActivo && $empleado->existioContrato)

                                    @php
                                        $fecha_actual = date("y-m-d H:i:00",time());
                                        $fecha_entrada = $empleado->contratoFechaVencimiento;
                                        
                                        $dateDifference = abs(strtotime($fecha_actual) - strtotime($fecha_entrada));

                                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                                        $datos =  $years." year,  ".$months." months and ".$days." days";

                                        $puesto = "Puesto sin asignar";
                                        foreach ($puestos as $element){
                                            if($element->id == $empleado->id_puesto){
                                                $puesto  = $element->puesto;
                                            }
                                        }
                                        
                                        $departamento ="Departamento sin asignar";
                                        foreach ($departamentos as $dep){
                                            if($dep->id == $empleado->id_departamento){

                                                $departamento  = $dep->nombre;
                                            }
                                        }      
                                    @endphp

                                    <tr>
                                        <td>
                                            {{$empleado->numero_empleado}}
                                        </td>
                                        <td>
                                            {{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}
                                        </td>
                                        <td>
                                            <span class="badge badge-warning" id="tagP">Vencido</span>
                                        </td>
                                        <td class="text-center">
                                            {{ substr($empleado->contratoFechaVencimiento, 0, -9) }}
                                        </td>
                                        <td>
                                            <a data-toggle="modal" data-target="#exampleModal" class="contrato_emp" data-numemp="{{$empleado->numero_empleado}}" data-nombre="{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}" data-correo="{{$empleado->correo}}" data-telfonomovil="{{$empleado->telefono_movil}}" data-telefonocasa="{{$empleado->telefono_casa}}"  data-fotografia="{{$empleado->file_fotografia}}" data-fechaven="{{substr($empleado->contratoFechaVencimiento, 0, -9)}}" data-datos="{{$datos}}" data-empresa="{{Session::get('empresa')['id']}}" data-id="{{$empleado->id}}" data-puesto="{{$puesto}}" data-departamento="{{$departamento}}" data-fechaalta="{{$empleado->fecha_alta}}" data-filecontrato="{{$empleado->file_contrato}}">
                                                <img src="{{asset('img/icono-asociar-e.png')}}" title="Ver empleado" alt="Ver empleado" style="width: 27px;">
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                             @endforeach
                        </table>
                    </div>

                </div>
            </div>


            <!-- Modal CONTRATO EMPLEADO-->
            <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content  modal-lg">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Información empleado</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <img src="" id="img_mod_cont" width="130px">
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-2"><hr style="border-bottom: 2px #fbba00 solid;"></div>
                                <div class="col-md-8">
                                    <span id="nombre_mod_cont"></span><br>
                                    Correo: <span id="correo_mod_cont"></span><br>
                                    Telefono: <span id="telefono_mod_cont"></span><br>
                                    Departamento: <span id="departamento_mod_cont"></span><br>
                                    Puesto: <span id="puesto_mod_cont"></span>
                                </div>
                                <div class="col-md-2"><hr style="border-bottom: 2px #fbba00 solid;"></div>
                            </div>
                            <div class="row" style="margin-top: 12px;">
                                <div class="col-md-12 text-center">
                                    <input type="hidden" id="file_mod_cont">
                                    Contrato Anterior 
                                    <a id="view_contrato">
                                        <img src="{{asset('img/ver-documentos-empleado.png')}}" title="Contrato" alt="Contrato" style="width: 27px;">
                                    </a>

                                    <div>
                                        Fecha alta: <span id="fechaalta_mod_cont"></span><br>
                                        Fecha de vencimiento: <span id="fechaven_mod_cont"></span><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn button-style" data-dismiss="modal">cerrar</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="article border" style="margin-top: 10px;">
                <div class="row">
                    <div class="col-md-12 ">
                       <label class="font-size-1-5em mb-5 under-line font-weight-bold">Cumpleaños del mes</label>
                        <div class="row">
                            @foreach ($cumple as $cump)
                            <div class="col-md-6 text-center">
                                <div class="row">
                                    <div class="col-md-4 mt-3">
                                        @if(!empty($cump->file_fotografia) && file_exists('storage/repositorio/'.Session::get('empresa')['id'].'/'.$cump->id.'/'.$cump->file_fotografia))
                                            <td width="94px"><img src="{{ asset('storage/repositorio/'.Session::get('empresa')['id'].'/'.$cump->id.'/'.$cump->file_fotografia) }}" class="rounded-circle img-fluid center" width="94" height="26" alt="{{$cump->file_fotografia}}"></td>
                                        @else
                                            {{-- <td width="94px"> --}}
                                                <img src="{{ asset('/img/avatar.png')}}" class="rounded-circle img-fluid" width="94" height="26" alt="sin imagen">
                                            {{-- </td> --}}
                                        @endif
                                    </div>
                                    <div class="col-md-8 mt-5">
                                    <span class="nombre-cumple">{{  $cump->nombre }} {{  $cump->apaterno }}</span> <br>
                                    @php
                                        $dia = substr($cump->fecha_nacimiento, -2);
                                    @endphp
                                    <span>{{$dia}}  {{$fechas}}</span> <img src="{{asset('img/bandeja-pastel.png')}}" alt="Periodo de implementación" class="under-line font-weight-bold" style="width: 17px;">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>





        </div>



        <div class="col-md-4">
{{--             <div class="row">
                <div class="col-md-12 text-center">
                    <img src="{{asset('/img/favicon.png')}}" class="img-fluid" width="94" height="140" alt="sin imagen">
                </div>
            </div> --}}
            <div class="row">
                <div class="col-md-8 ">
                    <div class="calendar article border" id="calendar-app">

                        <div class="calendar--view" id="calendar-view">
                            <div class="cview__month">
                                <span class="cview__month-last" id="calendar-month-last">Apr</span>
                                <span class="cview__month-current" id="calendar-month">May</span>
                                <span class="cview__month-next" id="calendar-month-next">Jun</span>
                            </div>
                            <div class="cview__header">Dom</div>
                            <div class="cview__header">Lun</div>
                            <div class="cview__header">Mar</div>
                            <div class="cview__header">Mie</div>
                            <div class="cview__header">Jue</div>
                            <div class="cview__header">Vie</div>
                            <div class="cview__header">Sab</div>
                            <div class="calendar--view" id="dates"></div>
                        </div>

                        <div class="footer">
                            <span><span id="footer-date" class="footer__link">Today is May 30</span></span>    
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="article border" style="margin-top: 16px;">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <span class="font-weight-bold" style="font-size: 20px;">Estatus de nómina</span>
                            </div>
                        </div>

                    </div>

                    <div class="article border" style="margin-top: 16px;">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <span>Nómina abierta</span>
                            </div>
                        </div>
                            <div class="progress green colorbar">
                                <span class="progress-left">
                                    <span class="progress-bar"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar"></span>
                                </span>

                                <div class="progress-value">
                                    <h2 class="barra">
                                        @if ($activo_periodo == 1)
                                            <input type="text" id="porcentaje_final" class="numero" value="SI" readonly>
                                        @else
                                            <input type="text" id="porcentaje_final" class="numero" value="NO" readonly style="margin-left: 19px;">
                                        @endif

                                    </h2>
                                </div>
                            </div>
                    </div>
                </div>

            </div>

             @if ($activo_periodo == 1)
                <div class="article border" style="margin-top: 10px;">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <label class="font-size-1-5em mb-5 under-line font-weight-bold">Periodo de nómina</label>
                        </div>
                    </div>

                    @php
                        $inicio = substr($periodo_nomina[0]->fecha_inicial_periodo, -2);
                        $mes_inico = substr($periodo_nomina[0]->fecha_inicial_periodo, -5,2);
                        $nombre_mes_inicio = mesnum($mes_inico);
                        $termino = substr($periodo_nomina[0]->fecha_final_periodo, -2);
                        $mes_final = substr($periodo_nomina[0]->fecha_final_periodo, -5,2);
                        $nombre_mes_final = mesnum($mes_final);

                    @endphp

                    <div class="row" style="margin-top: -10px;">
                        <div class="col-md-6 text-center">
                            <span>Inicio: {{ $inicio }} de {{ $nombre_mes_inicio }}</span>
                        </div>
                        <div class="col-md-6 text-center">
                            <span>Termino: {{ $termino }} de {{ $nombre_mes_final }}</span>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <h5 class="font-weight-bold">{{ $periodo_nomina[0]->nombre_periodo }}</h5>
                        </div>
                    </div>
                </div>
            @endif

            <div class="article border" style="margin-top: 10px;">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <label class="font-size-1-5em mb-5 under-line font-weight-bold">Asistencias</label>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px">
                    <div class="col-md-4 asistencias">
                        <svg class="radial-progress radial-progress-warning" data-percentage="{{ $data['total']}}" viewBox="0 0 80 80">
                            <circle class="incomplete" cx="40" cy="40" r="35"></circle>
                            <circle class="complete" cx="40" cy="40" r="35" style="stroke-dashoffset: 219.91148575129;"></circle>
                            <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">{{ $data['total']}}</text>
                        </svg>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <span class="font-weight-bold">Presentes</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 asistencias">
                        <svg class="radial-progress radial-progress-warning" data-percentage="{{ $data['total']}}" viewBox="0 0 80 80">
                            <circle class="incomplete" cx="40" cy="40" r="35"></circle>
                            <circle class="complete" cx="40" cy="40" r="35" style="stroke-dashoffset: 219.91148575129;"></circle>
                            <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">{{ $data['retardos']}}</text>
                        </svg>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <span class="font-weight-bold">Retardos</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 asistencias">
                        <svg class="radial-progress radial-progress-warning" data-percentage="{{ $data['total']}}" viewBox="0 0 80 80">
                            <circle class="incomplete" cx="40" cy="40" r="35"></circle>
                            <circle class="complete" cx="40" cy="40" r="35" style="stroke-dashoffset: 219.91148575129;"></circle>
                            <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">{{ $data['faltas']}}</text>
                        </svg>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <span class="font-weight-bold">Ausentes</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12 text-center">
                        <small>* Este monitor solamente aplica para clientes con sincronización de biométricos.</small>
                    </div>
                </div>

            </div>

{{--             <div class="article border box-shadow" style="margin-top: 10px;">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4>Cumpleaños del mes</h4>
                        @foreach ($cumple as $cump)
                            <div class="row">
                                <div>
                                    @if(!empty($cump->file_fotografia) && file_exists('public/repositorio/'.Session::get('empresa')['id'].'/'.$cump->id.'/'.$cump->file_fotografia))
                                        <td width="94px"><img src="{{asset('public/repositorio/'.Session::get('empresa')['id'].'/'.$cump->id.'/'.$cump->file_fotografia)}}" class="rounded-circle img-fluid" width="94" height="26" alt="{{$cump->file_fotografia}}"></td>
                                    @else
                                        <td width="94px">
                                            <img src="{{ asset('/img/avatar.png')}}" class="rounded-circle img-fluid" width="94" height="26" alt="sin imagen">
                                        </td>
                                    @endif
                                </div>
                                <div class="center">
                                    <span class="nombre-cumple">{{  $cump->nombre }}</span>  <i class="fas fa-birthday-cake"></i> <br>

                                    @php
                                        $dia = substr($cump->fecha_nacimiento, -2);
                                    @endphp
                                    <span>{{$dia}}  {{$month}}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div> --}}

            <form action="{{ route('bandeja.cerrarEvento') }}" method="post" id="submit_cerrarevento">
                @csrf

                <input type="hidden" name="id" id="id_evento" value="">
            </form>

            <form method="post" id="nomina-correcta-form" action="{{ route('bandeja.cerrarnomina') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="id_correcta" value="">
                <input type="hidden" name="ref" id="ref_correcta" value="">
                <input type="hidden" name="op" value="0">
            </form>




        </div>
    </div>
</div>
{{--     @php
        dd(Session::get('base'));
    @endphp --}}



@include('includes.footer')
<script>
    $(function() {


        // Remove svg.radial-progress .complete inline styling
        $('svg.radial-progress').each(function(index, value) {
            $(this).find($('circle.complete')).removeAttr('style');
        });

        $(".asistencias").scroll(function() {
            $('svg.radial-progress').each(function(index, value) {
                // If svg.radial-progress is approximately 25% vertically into the window when scrolling from the top or the bottom
                /*    if (
                      $("#empleados-table").scrollTop() > $(this).offset().top - ($("#empleados-table").height() * 0.75) &&
                      $("#empleados-table").scrollTop() < $(this).offset().top + $(this).height() - ($("#empleados-table").height() * 0.25)
                    ) {*/
                // Get percentage of progress
                percent = $(value).data('percentage');
                // Get radius of the svg's circle.complete
                radius = $(this).find($('circle.complete')).attr('r');
                // Get circumference (2πr)
                circumference = 2 * Math.PI * radius;
                // Get stroke-dashoffset value based on the percentage of the circumference
                strokeDashOffset = circumference - ((percent * circumference) / 100);
                // Transition progress for 1.25 seconds
                $(this).find($('circle.complete')).animate({
                    'stroke-dashoffset': strokeDashOffset
                }, 1250);
                //  }
            });
        }).trigger('scroll');


        $("#spinner").addClass("ocultar");

    });

</script>
<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-36251023-1']);
    _gaq.push(['_setDomainName', 'jqueryscript.net']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>



<script type="text/javascript">

    $(document).ready(function() {
        $('#pendientes').DataTable({
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            }
        });
    } );

    $('.aceptar').click(function(e) {
        e.preventDefault();
        var id = $(this).attr('id');

        swal({
            title: "¿Realmente deseas marcar esta tarea como realizada?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {

                document.getElementById("id_evento").value =id;

                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });

                document.getElementById("submit_cerrarevento").submit();

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });

    $('.correcta').click(function() {

        var ref = $(this).data('ref');
        var id = $(this).attr('id');

        swal({
            title: "¿Éstas seguro de aceptar esta nómina?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {

                document.getElementById("id_correcta").value =id;
                document.getElementById("ref_correcta").value = ref;

                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });

                document.getElementById("nomina-correcta-form").submit();

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });

    $(".cancelar").click(function(){

        var ref = $(this).data('ref');
        var id = $(this).attr('id');

        document.getElementById("id_cancelar").value =id;
        document.getElementById("ref_cancelar").value = ref;

    });

    $("#add_cancelar_nomina").click(function(){
        var razon_cancelar = document.getElementById("razon_cancelar").value;

        if(razon_cancelar== ""){
            swal({
                title: "Para continuar debes agregar un motivo",
            });
        }else{
            swal("Espere un momento, la información esta siendo procesada", {
                icon: "success",
                buttons: false,
            });
            document.getElementById("nomina-cancelar-form").submit();
        }

    });

    $(".contrato_emp").click(function(){
        var numemp = $(this).data('numemp');
        var nombre = $(this).data('nombre');
        var correo = $(this).data('correo');
        var telfonomovil = $(this).data('telfonomovil');
        var telefonocasa = $(this).data('telefonocasa');
        var fotografia = $(this).data('fotografia');
        var fechaven = $(this).data('fechaven');
        var empresa = $(this).data('empresa');
        var datos = $(this).data('datos');
        var id = $(this).data('id');
        var puesto = $(this).data('puesto');
        var departamento = $(this).data('departamento');
        var fechaalta = $(this).data('fechaalta');
        var filecontrato = $(this).data('filecontrato');

        document.getElementById('nombre_mod_cont').innerHTML= nombre;
        document.getElementById('correo_mod_cont').innerHTML= correo;
        document.getElementById('telefono_mod_cont').innerHTML= telfonomovil;
        document.getElementById('puesto_mod_cont').innerHTML= puesto;
        document.getElementById('departamento_mod_cont').innerHTML= departamento;
        document.getElementById('fechaven_mod_cont').innerHTML= fechaven;
        document.getElementById('fechaalta_mod_cont').innerHTML= fechaalta;
        document.getElementById('file_mod_cont').value =  "/storage/repositorio/"+empresa+"/"+id+"/"+filecontrato+"";

        if(fotografia == ""){
            document.getElementById("img_mod_cont").src = "/img/avatar.png";
        }else{
            document.getElementById("img_mod_cont").src = "/storage/repositorio/"+empresa+"/"+id+"/"+fotografia+""; 
        }
    });

    $("#view_contrato").click(function(){
        contrato = document.getElementById('file_mod_cont').value;
        window.open(contrato);
    });

</script>


<script type="text/javascript">
    function CalendarApp(date) {
      
        if (!(date instanceof Date)) {
            date = new Date();
        }
      
        this.days = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        this.months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        this.quotes = [''];
        this.apts = [
            {
                name: 'Finish this web app',
                endTime: new Date(2016, 4, 30, 23),
                startTime: new Date(2016, 4, 30, 21),
                day: new Date(2016, 4, 30).toString()
            },
            {
                name: 'My Birthday!',
                endTime: new Date(2016, 4, 1, 23, 59),
                startTime: new Date(2016, 4, 1, 0),
                day: new Date(2016, 4, 1).toString()
            },
        
        ];
      
        this.aptDates = [new Date(2016, 4, 30).toString(),new Date(2016, 4, 1).toString()];
        this.eles = {
        };
        this.calDaySelected = null;
      
        this.calendar = document.getElementById("calendar-app");
      
        this.calendarView = document.getElementById("dates");
      
        this.calendarMonthDiv = document.getElementById("calendar-month");
        this.calendarMonthLastDiv = document.getElementById("calendar-month-last");
        this.calendarMonthNextDiv = document.getElementById("calendar-month-next");
      
        this.dayInspirationalQuote = document.getElementById("inspirational-quote");
       
        this.todayIsSpan = document.getElementById("footer-date");
        // this.eventsCountSpan = document.getElementById("footer-events");
        this.dayViewEle = document.getElementById("day-view");
        this.dayViewExitEle = document.getElementById("day-view-exit");
        this.dayViewDateEle = document.getElementById("day-view-date");
        this.addDayEventEle = document.getElementById("add-event");
        this.dayEventsEle = document.getElementById("day-events");
      
        this.dayEventAddForm = {
            cancelBtn: document.getElementById("add-event-cancel"),
            addBtn: document.getElementById("add-event-save"),
            nameEvent:  document.getElementById("input-add-event-name"),
            startTime:  document.getElementById("input-add-event-start-time"),
            endTime:  document.getElementById("input-add-event-end-time"),
            startAMPM:  document.getElementById("input-add-event-start-ampm"),
            endAMPM:  document.getElementById("input-add-event-end-ampm")
        };
        this.dayEventsList = document.getElementById("day-events-list");
        this.dayEventBoxEle = document.getElementById("add-day-event-box");
      
        /* Start the app */
        this.showView(date);
        this.addEventListeners();
        this.todayIsSpan.textContent = "Today is " + this.months[date.getMonth()] + " " + date.getDate();  
    }

    CalendarApp.prototype.addEventListeners = function(){
        this.calendar.addEventListener("click", this.mainCalendarClickClose.bind(this));
        this.todayIsSpan.addEventListener("click", this.showView.bind(this));
        this.calendarMonthLastDiv.addEventListener("click", this.showNewMonth.bind(this));
        this.calendarMonthNextDiv.addEventListener("click", this.showNewMonth.bind(this));
        this.dayViewExitEle.addEventListener("click", this.closeDayWindow.bind(this));
        this.dayViewDateEle.addEventListener("click", this.showNewMonth.bind(this));
        this.addDayEventEle.addEventListener("click", this.addNewEventBox.bind(this));
        this.dayEventAddForm.cancelBtn.addEventListener("click", this.closeNewEventBox.bind(this));
        this.dayEventAddForm.cancelBtn.addEventListener("keyup", this.closeNewEventBox.bind(this));
      
        this.dayEventAddForm.startTime.addEventListener("keyup",this.inputChangeLimiter.bind(this));
        this.dayEventAddForm.startAMPM.addEventListener("keyup",this.inputChangeLimiter.bind(this));
        this.dayEventAddForm.endTime.addEventListener("keyup",this.inputChangeLimiter.bind(this));
        this.dayEventAddForm.endAMPM.addEventListener("keyup",this.inputChangeLimiter.bind(this));
        this.dayEventAddForm.addBtn.addEventListener("click",this.saveAddNewEvent.bind(this));
    };

    CalendarApp.prototype.showView = function(date){
        if ( !date || (!(date instanceof Date)) ) date = new Date();
        var now = new Date(date),
        y = now.getFullYear(),
        m = now.getMonth();
        var today = new Date();
      
        var lastDayOfM = new Date(y, m + 1, 0).getDate();
        var startingD = new Date(y, m, 1).getDay();
        var lastM = new Date(y, now.getMonth()-1, 1);
        var nextM = new Date(y, now.getMonth()+1, 1);
     
        this.calendarMonthDiv.classList.remove("cview__month-activate");
        this.calendarMonthDiv.classList.add("cview__month-reset");
      
        while(this.calendarView.firstChild) {
            this.calendarView.removeChild(this.calendarView.firstChild);
        }
      
        // build up spacers
        for ( var x = 0; x < startingD; x++ ) {
            var spacer = document.createElement("div");
            spacer.className = "cview--spacer";
            this.calendarView.appendChild(spacer);
        }
      
        for ( var z = 1; z <= lastDayOfM; z++ ) {
       
            var _date = new Date(y, m ,z);
            var day = document.createElement("div");
            day.className = "cview--date";
            day.textContent = z;
            day.setAttribute("data-date", _date);
            day.onclick = this.showDay.bind(this);
        
            // check if todays date
            if ( z == today.getDate() && y == today.getFullYear() && m == today.getMonth() ) {
                day.classList.add("today");
            }
        
            // check if has events to show
            if ( this.aptDates.indexOf(_date.toString()) !== -1 ) {
                day.classList.add("has-events");
            }
        
            this.calendarView.appendChild(day);
        }
      
        var _that = this;
        setTimeout(function(){
            _that.calendarMonthDiv.classList.add("cview__month-activate");
        }, 50);
      
        this.calendarMonthDiv.textContent = this.months[now.getMonth()] + " " + now.getFullYear();
        this.calendarMonthDiv.setAttribute("data-date", now);

        this.calendarMonthLastDiv.textContent = "← " + this.months[lastM.getMonth()];
        this.calendarMonthLastDiv.setAttribute("data-date", lastM);
      
        this.calendarMonthNextDiv.textContent = this.months[nextM.getMonth()] + " →";
        this.calendarMonthNextDiv.setAttribute("data-date", nextM);
    }

    CalendarApp.prototype.showDay = function(e, dayEle) {
        e.stopPropagation();
        if ( !dayEle ) {
            dayEle = e.currentTarget;
        }
        var dayDate = new Date(dayEle.getAttribute('data-date'));
        console.log(dayDate);
        this.calDaySelected = dayEle;
        this.openDayWindow(dayDate);
    };

    CalendarApp.prototype.openDayWindow = function(date){
        var now = new Date();
        var day = new Date(date);
        this.dayViewDateEle.textContent = this.days[day.getDay()] + ", " + this.months[day.getMonth()] + " " + day.getDate() + ", " + day.getFullYear();
        this.dayViewDateEle.setAttribute('data-date', day);
        this.dayViewEle.classList.add("calendar--day-view-active");
      
        /* Contextual lang changes based on tense. Also show btn for scheduling future events */
        var _dayTopbarText = '';
        if ( day < new Date(now.getFullYear(), now.getMonth(), now.getDate())) {
            _dayTopbarText = "had ";
            this.addDayEventEle.style.display = "none";
        } else {
            _dayTopbarText = "have ";
            this.addDayEventEle.style.display = "inline";
        }
        this.addDayEventEle.setAttribute("data-date", day);
      
        var eventsToday = this.showEventsByDay(day);
        if ( !eventsToday ) {
            _dayTopbarText += "no ";
            var _rand = Math.round(Math.random() * ((this.quotes.length - 1 ) - 0) + 0);
            this.dayInspirationalQuote.textContent = this.quotes[_rand];
        } else {
            _dayTopbarText += eventsToday.length + " ";
            this.dayInspirationalQuote.textContent = null;
        }
        //this.dayEventsList.innerHTML = this.showEventsCreateHTMLView(eventsToday);
        while(this.dayEventsList.firstChild) {
            this.dayEventsList.removeChild(this.dayEventsList.firstChild);
        }
      
        this.dayEventsList.appendChild(this.showEventsCreateElesView(eventsToday));
      
        this.dayEventsEle.textContent = _dayTopbarText + "events on " + this.months[day.getMonth()] + " " + day.getDate() + ", " + day.getFullYear();
    };

    CalendarApp.prototype.showEventsCreateElesView = function(events) {
        var ul = document.createElement("ul");
        ul.className = 'day-event-list-ul';
        events = this.sortEventsByTime(events);
        var _this = this;
        events.forEach(function(event){
            var _start = new Date(event.startTime);
            var _end = new Date(event.endTime);
            var idx = event.index;
            var li = document.createElement("li");
            li.className = "event-dates";
            // li.innerHtml
            var html = "<span class='start-time'>" + _start.toLocaleTimeString(navigator.language,{hour: '2-digit', minute:'2-digit'}) + "</span> <small>through</small> ";
            html += "<span class='end-time'>" + _end.toLocaleTimeString(navigator.language,{hour: '2-digit', minute:'2-digit'}) + ( (_end.getDate() != _start.getDate()) ? ' <small>on ' + _end.toLocaleDateString() + "</small>" : '') +"</span>";
        

            html += "<span class='event-name'>" + event.name + "</span>";
        
            var div = document.createElement("div");
            div.className = "event-dates";
            div.innerHTML = html;
        
            var deleteBtn = document.createElement("span");
            var deleteText = document.createTextNode("delete");
            deleteBtn.className = "event-delete";
            deleteBtn.setAttribute("data-idx", idx);
            deleteBtn.appendChild(deleteText);
            deleteBtn.onclick = _this.deleteEvent.bind(_this);
        
            div.appendChild(deleteBtn);
        
            li.appendChild(div);
            ul.appendChild(li);
        });
        return ul;
    };

    CalendarApp.prototype.deleteEvent = function(e) {
        var deleted = this.apts.splice(e.currentTarget.getAttribute("data-idx"),1);
        var deletedDate = new Date(deleted[0].day);
        var anyDatesLeft = this.showEventsByDay(deletedDate);
        if ( anyDatesLeft === false ) {
            // safe to remove from array
            var idx = this.aptDates.indexOf(deletedDate.toString());
            if (idx >= 0) {
                this.aptDates.splice(idx,1);
                // remove dot from calendar view
                var ele = document.querySelector('.cview--date[data-date="'+ deletedDate.toString() +'"]');
                if ( ele ) {
                    ele.classList.remove("has-events");
                }
            }
        }
        this.openDayWindow(deletedDate);;
    };

    CalendarApp.prototype.sortEventsByTime = function(events) {
        if (!events) return [];
        return events.sort(function compare(a, b) {
            if (new Date(a.startTime) < new Date(b.startTime)) {
                return -1;
            }
            if (new Date(a.startTime) > new Date(b.startTime)) {
                return 1;
            }
            // a must be equal to b
            return 0;
        });
    };

    CalendarApp.prototype.showEventsByDay = function(day) {
        var _events = [];
        this.apts.forEach(function(apt, idx){
            if ( day.toString() == apt.day.toString() ) {
                apt.index = idx;
                _events.push(apt);
            }
        });
        return (_events.length) ? _events : false;
    };

    CalendarApp.prototype.closeDayWindow = function(){
        this.dayViewEle.classList.remove("calendar--day-view-active");
        this.closeNewEventBox();
    };

    CalendarApp.prototype.mainCalendarClickClose = function(e){
      if ( e.currentTarget != e.target ) {
        return;
      }
      
      this.dayViewEle.classList.remove("calendar--day-view-active");
      this.closeNewEventBox();
    };

    CalendarApp.prototype.addNewEventBox = function(e){
        var target = e.currentTarget;
        this.dayEventBoxEle.setAttribute("data-active", "true"); 
        this.dayEventBoxEle.setAttribute("data-date", target.getAttribute("data-date"));
    };

    CalendarApp.prototype.closeNewEventBox = function(e){
        if (e && e.keyCode && e.keyCode != 13) return false;
        this.dayEventBoxEle.setAttribute("data-active", "false");
        this.resetAddEventBox();
    };

    CalendarApp.prototype.saveAddNewEvent = function() {
        var saveErrors = this.validateAddEventInput();
        if ( !saveErrors ) {
            this.addEvent();
        }
    };

    CalendarApp.prototype.addEvent = function() {
        var name = this.dayEventAddForm.nameEvent.value.trim();
        var dayOfDate = this.dayEventBoxEle.getAttribute("data-date");
        var dateObjectDay =  new Date(dayOfDate);
        var cleanDates = this.cleanEventTimeStampDates();
      
        this.apts.push({
            name: name,
            day: dateObjectDay,
            startTime: cleanDates[0],
            endTime: cleanDates[1]
        });

        this.closeNewEventBox();
        this.openDayWindow(dayOfDate);
        this.calDaySelected.classList.add("has-events");
        
        if ( this.aptDates.indexOf(dateObjectDay.toString()) === -1 ) {
            this.aptDates.push(dateObjectDay.toString());
        }
    };

    CalendarApp.prototype.convertTo23HourTime = function(stringOfTime, AMPM) {
        // convert to 0 - 23 hour time
        var mins = stringOfTime.split(":");
        var hours = stringOfTime.trim();
        if ( mins[1] && mins[1].trim() ) {
            hours = parseInt(mins[0].trim());
            mins = parseInt(mins[1].trim());
        } else {
            hours = parseInt(hours);
            mins = 0;
        }
        hours = ( AMPM == 'am' ) ? ( (hours == 12) ? 0 : hours ) : (hours <= 11) ? parseInt(hours) + 12 : hours;
        return [hours, mins];
    };

    CalendarApp.prototype.cleanEventTimeStampDates = function() {
        var startTime = this.dayEventAddForm.startTime.value.trim() || this.dayEventAddForm.startTime.getAttribute("placeholder") || '8';
        var startAMPM = this.dayEventAddForm.startAMPM.value.trim() || this.dayEventAddForm.startAMPM.getAttribute("placeholder") || 'am';
        startAMPM = (startAMPM == 'a') ? startAMPM + 'm' : startAMPM;
        var endTime = this.dayEventAddForm.endTime.value.trim() || this.dayEventAddForm.endTime.getAttribute("placeholder") || '9';
        var endAMPM = this.dayEventAddForm.endAMPM.value.trim() || this.dayEventAddForm.endAMPM.getAttribute("placeholder") || 'pm';
        endAMPM = (endAMPM == 'p') ? endAMPM + 'm' : endAMPM;
        var date = this.dayEventBoxEle.getAttribute("data-date");
      
        var startingTimeStamps = this.convertTo23HourTime(startTime, startAMPM);
        var endingTimeStamps = this.convertTo23HourTime(endTime, endAMPM);
      
        var dateOfEvent = new Date(date);
        var startDate = new Date(dateOfEvent.getFullYear(), dateOfEvent.getMonth(), dateOfEvent.getDate(), startingTimeStamps[0], startingTimeStamps[1]);
        var endDate = new Date(dateOfEvent.getFullYear(), dateOfEvent.getMonth(), dateOfEvent.getDate(), endingTimeStamps[0], endingTimeStamps[1]);
      
         // if end date is less than start date - set end date back another day
        if ( startDate > endDate ) endDate.setDate(endDate.getDate() + 1);
      
        return [startDate, endDate];
    };

    CalendarApp.prototype.validateAddEventInput = function() {

        var _errors = false;
        var name = this.dayEventAddForm.nameEvent.value.trim();
        var startTime = this.dayEventAddForm.startTime.value.trim();
        var startAMPM = this.dayEventAddForm.startAMPM.value.trim();
        var endTime = this.dayEventAddForm.endTime.value.trim();
        var endAMPM = this.dayEventAddForm.endAMPM.value.trim();
      
        if (!name || name == null) {
            _errors = true;
            this.dayEventAddForm.nameEvent.classList.add("add-event-edit--error");
            this.dayEventAddForm.nameEvent.focus();
        } else {
            this.dayEventAddForm.nameEvent.classList.remove("add-event-edit--error");
        }
      
        //   if (!startTime || startTime == null) {
        //     _errors = true;
        //     this.dayEventAddForm.startTime.classList.add("add-event-edit--error");
        //   } else {
        //      this.dayEventAddForm.startTime.classList.remove("add-event-edit--error");
        //   }
      
        return _errors;  
    };

    var timeOut = null;
    var activeEle = null;
    CalendarApp.prototype.inputChangeLimiter = function(ele) {
      
        if ( ele.currentTarget ) {
            ele = ele.currentTarget;
        }
        if (timeOut && ele == activeEle){
            clearTimeout(timeOut);
        }
      
        var limiter = CalendarApp.prototype.textOptionLimiter;

        var _options = ele.getAttribute("data-options").split(",");
        var _format = ele.getAttribute("data-format") || 'text';
        timeOut = setTimeout(function(){
            ele.value = limiter(_options, ele.value, _format);
        }, 600);
        activeEle = ele;
    };

    CalendarApp.prototype.textOptionLimiter = function(options, input, format){
        if ( !input ) return '';
      
        if ( input.indexOf(":") !== -1 && format == 'datetime' ) {
     
            var _splitTime = input.split(':', 2);
            if (_splitTime.length == 2 && !_splitTime[1].trim()) return input;
            var _trailingTime = parseInt(_splitTime[1]);
            /* Probably could be coded better -- a block to clean up trailing data */
            if (options.indexOf(_splitTime[0]) === -1) {
                return options[0];
            }
            else if (_splitTime[1] == "0" ) {
                return input;
            }
            else if (_splitTime[1] == "00" ) {
                return _splitTime[0] +  ":00";
            }
            else if (_trailingTime < 10 ) {
                return _splitTime[0] + ":" + "0" + _trailingTime;
            }
            else if ( !Number.isInteger(_trailingTime) || _trailingTime < 0 || _trailingTime > 59 )  {
                return _splitTime[0];
            } 
            return _splitTime[0] + ":" + _trailingTime;
        }

        if ((input.toString().length >= 3) ) {
            var pad = (input.toString().length - 4) * -1;
            var _hour, _min;
            if (pad == 1) {
                _hour = input[0];
                _min = input[1] + input[2];
            } else {
                _hour = input[0] + input[1];
                _min = input[2] + input[3];
            }
        
            _hour = Math.max(1,Math.min(12,(_hour)));
            _min = Math.min(59,(_min));
            if ( _min < 10 ) { 
                _min = "0" + _min;
            }
            _min = (isNaN(_min)) ? '00' : _min;
            _hour = (isNaN(_hour)) ? '9' : _hour ;

            return _hour + ":" + _min;
        
        }

        if (options.indexOf(input) === -1) {
            return options[0];
        }
      
        return input;
    };

    CalendarApp.prototype.resetAddEventBox = function(){
        this.dayEventAddForm.nameEvent.value = '';
        this.dayEventAddForm.nameEvent.classList.remove("add-event-edit--error");
        this.dayEventAddForm.endTime.value = '';
        this.dayEventAddForm.startTime.value = '';
        this.dayEventAddForm.endAMPM.value = '';
        this.dayEventAddForm.startAMPM.value = '';
    };

    CalendarApp.prototype.showNewMonth = function(e){
        var date = e.currentTarget.dataset.date;
        var newMonthDate = new Date(date);
        this.showView(newMonthDate);
        this.closeDayWindow();
         return true;
    };

    var calendar = new CalendarApp();
    console.log(calendar);
</script>