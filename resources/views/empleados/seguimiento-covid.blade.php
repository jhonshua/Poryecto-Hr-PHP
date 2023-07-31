<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<style type="text/css">
    .article {
        width: 100%;
        height: 460px;
        padding: 5%;
        float: left;
        box-sizing: border-box;
        background-color: #fff;
    }

.article-nota {
    width: 100%;
    height: auto;
    padding: 5%;
    float: left;
    box-sizing: border-box;
    background-color: #fff;
}
</style>


<div class="container">
@include('includes.header',['title'=>'Seguimiento covid',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 15.png',
        'route'=>'empleados.empleados'])
    
        @if(count($empleado) > 0)
            @php $emp = $empleado[0];  
        @endphp
            <div class="panel-btn">
                <a href="#" class="btn button-style ml-5 mt-5" data-toggle="modal" 
                    data-target="#registroCovidModal"
                    data-nombre="{{$emp->apaterno}} {{$emp->amaterno}} {{$emp->nombre}}"
                    data-idempleado="{{$emp->id}}"
                    data-escontacto="0"
                    data-escontactode="0"
                    data-idregistro="0"
                >   
                <img src="{{ asset('/img/cubrebocas-empleado.png') }}" width="25px"> 
                    Registro covid
                </a>
                
                @if( empty($comprobante_vacunacion) )
                    <a href="#" class="btn button-style ml-5 mt-5" id="modalComprobante">   
                    <img src="{{ asset('/img/cubrebocas-empleado.png') }}" width="25px"> 
                        Comprobante vacunación
                    </a>
                @endif
            </div> 
    
        <br>

    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif

    @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif

    @if(count($emp->registro_covid) > 0)
        @foreach($emp->registro_covid as $registro)
            @php 
                $evidencias = $registro->evidencias->keyBy('tipo')->toArray();
            @endphp
            <div class="row">
                <div class="col-md-4">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h4 class="font-weight-bold">Trabajador con covid</h4>
                                <hr>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            @php   // dump($registro->lo_contagio); @endphp
                             @if($registro->lo_contagio->count() > 0)
                                            
                                @if($registro->lo_contagio->first()->estatus == 0)
                                    <li class="lo_contagio list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$registro->lo_contagio->first()->empleado->id}}">
                                        <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">{{$registro->lo_contagio->first()->empleado->nombre}} {{$registro->lo_contagio->first()->empleado->apaterno}} {{$registro->lo_contagio->first()->empleado->amaterno}}</a>
                                        <div class="btn-group-vertical me-2 btn-group-sm" role="group" aria-label="First group">
                                            <a class="btn btn-success active"  style="font-size:10px" >NEGATIVO A COVID</a>  
                                        </div>

                                    </li>
                                @elseif($registro->lo_contagio->first()->estatus == 1)
                                    <li class="lo_contagio list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$registro->lo_contagio->first()->empleado->id}}">
                                        <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">{{$registro->lo_contagio->first()->empleado->nombre}} {{$registro->lo_contagio->first()->empleado->apaterno}}  {{$registro->lo_contagio->first()->empleado->amaterno}}</a>

                                        <a class="btn btn-danger active p-1"  style="font-size:10px" >POSITIVO A COVID <b>{{\Carbon\Carbon::parse($registro->lo_contagio->first()->fecha_inicio)->format('d-m-Y')}}</b></a>
                                    </li>
                                @elseif($registro->lo_contagio->first()->estatus == 2)
                                    <li class="lo_contagio list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$registro->lo_contagio->first()->empleado->id}}">
                                        <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">{{$registro->lo_contagio->first()->empleado->nombre}} {{$registro->lo_contagio->first()->empleado->apaterno}}  {{$registro->lo_contagio->first()->empleado->amaterno}}</a>

                                        <a class="btn btn-success active p-1"  style="font-size:10px" >SUPERÓ COVID <b>{{\Carbon\Carbon::parse($registro->lo_contagio->first()->fecha_fin)->format('d-m-Y')}}</b></a>
                                    </li>
                                                    

                                 @endif
                            @else
                                <div class="row mt-3">
                                    <div class="col-md-12 text-center">
                                        <h5>Ninguna trabajador con covid tuvo contacto con {{$emp->apaterno}} {{$emp->amaterno}} {{$emp->nombre}}</h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row mt-4"></div>
                            @endif
                        </ul>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h4 class="font-weight-bold">Datos del seguimiento</h4>
                                <hr>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <h4>{{$emp->nombre}} {{$emp->apaterno}} {{$emp->apaterno}}</h4>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="#" class="eliminar " data-id="{{$registro->id}}" >
                                    <img src="{{ asset('/img/icono-eliminar.png') }}" width="25px">
                                </a>
                                <a href="#" data-toggle="modal" id="editar"
                                    data-target="#registroCovidEditarModal"
                                    data-nombre="{{$emp->apaterno}} {{$emp->amaterno}} {{$emp->nombre}}"
                                    data-idempleado="{{$emp->id}}"
                                    data-escontacto="0"
                                    data-escontactode="0"
                                    data-idregistro="{{$registro->id}}" 
                                    data-fechainicio="{{\Carbon\Carbon::parse($registro->fecha_inicio)->format('Y-m-d')}}"
                                    data-fechafin="{{\Carbon\Carbon::parse($registro->fecha_fin)->format('Y-m-d')}}"
                                    data-notas= "{{$registro->notas}}"
                                    data-estatus= "{{$registro->estatus}}"
                                    data>
                                    <span><img src="{{ asset('/img/icono-editar.png') }}" width="25px"></span>
                                </a>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 text-right">
                                <p class="card-text">Fecha inicio enfermedad: {{\Carbon\Carbon::parse($registro->fecha_inicio)->format('d-m-Y')}}</p>
                            </div>
                            <div class="col-md-4">
                            @php // dump($evidencias);   @endphp
                                @if(!empty($evidencias[1]))
                                    <p><a href="#" class="card-link alert-link" id="evidencia1"
                                        data-toggle="modal" 
                                        data-target="#verEvidenciaCovid"
                                        data-url='{{asset("/storage/repositorio/". Session::get("empresa")["id"]."/covid/".$evidencias[1]["id_registro_covid"]."/")."/".$evidencias[1]["nombre"]}}'
                                        data-nombre="{{$evidencias[1]['nombre']}}"
                                        data-idregistro="{{$evidencias[1]['id_registro_covid']}}"
                                        data-idevidencia="{{$evidencias[1]['id']}}"
                                        style="text-decoration: underline;"><span><img src="{{ asset('/img/Recurso 16.png') }}" width="25px"></span></a>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5 class="font-weight-bold">Estado</h5>
                            </div>

                            <div class="col-md-12 text-center">
                                @if($registro->estatus == 1)
                                    <h5 class="text-danger card-title font-weight-bold">
                                        <b></b> Positivo a covid <img src="{{ asset('/img/Recurso 22.png') }}" width="25px"> 
                                    </h5>
                                @elseif($registro->estatus == 2)
                                    <h5 class="text-success card-title font-weight-bold">
                                        <b></b>Negativo a covid <img src="{{ asset('/img/Recurso 23.png') }}" width="25px">
                                    </h5>

                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 text-right">
                                    <p class="card-text">Alta medica: {{\Carbon\Carbon::parse($registro->fecha_fin)->format('d-m-Y')}}</p>
                            </div>
                            <div class="-col-md-4">
                                @if(!empty($evidencias[2]))
                                    <p><a href="#" class="card-link alert-link" id="evidencia2"
                                        data-toggle="modal" 
                                        data-target="#verEvidenciaCovid"
                                        data-url='{{asset("/storage/repositorio/". Session::get("empresa")["id"]."/covid/".$evidencias[2]["id_registro_covid"]."/")."/".$evidencias[2]["nombre"]}}'
                                        data-nombre="{{$evidencias[2]['nombre']}}"
                                        data-idregistro="{{$evidencias[2]['id_registro_covid']}}"
                                        data-idevidencia="{{$evidencias[2]['id']}}"
                                        style="text-decoration: underline;"><img src="{{ asset('/img/Recurso 16.png') }}" width="25px"></a>
                                    </p>
                                @endif
                            </div>
                        </div>


                    </div>
                </div>


                <div class="col-md-4">
                    <div class="article border">

                        <div class="row">
                            <div class="col-md-8 text-right">
                                <h4 class="font-weight-bold">Contactos</h4>
                            </div>
                            <div class="col-md-4 text-left">
                                <span style="color:#b42434; font-size: 20px;" class="font-weight-bold">{{$registro->contactos->count()}}</span>
                            </div>

                        </div>
                        <hr>
                        <div class="wrapper-table" style="max-height:300px">
                            <ul class="list-group list-group-flush" >
                                @foreach($registro->contactos as $contacto)
                                    @if($contacto->estatus == 0)
{{--                                         <li class="es_contacto list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$contacto->empleado->id}}">
                                                        
                                            <a class="col-md-6" style="color:black" href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</a>
                                                        
                                            <div class="btn-group-vertical me-2 btn-group-sm p-1" role="group" aria-label="First group">
                                                <a class="btn btn-success active"  style="font-size:10px" >NEGATIVO A COVID</a>  
                                                <a href="#" class="btn btn-warning  btn-xs" style="font-size:10px"
                                                    data-toggle="modal" 
                                                    data-target="#registroCovidModal"
                                                    data-nombre="{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}"
                                                    data-idempleado="{{$contacto->empleado->id}}"
                                                    data-escontacto="{{$contacto->id}}"
                                                    data-escontactode="{{$emp->id}}"
                                                    data-idregistro="0" >
                                                    <span data-toggle="tooltip"  title="REGISTRO COVID" class="tooltip_">
                                                        <img src="{{ asset('/img/cubrebocas-empleado.png') }}" width="20px">&nbsp;REGISTRO COVID
                                                    </span>
                                                </a>  
                                            </div>
                                        </li> --}}


                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <h4>{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</h4>
                                            </div>


                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold" style="color:#b42434; font-size: 20px;">Negativo a covid</span> <img src="{{ asset('/img/Recurso 24.png') }}" width="30px">     
                                            </div>
{{--                                             <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div> --}}
                                            <hr>
                                        </div>


                                    @elseif($contacto->estatus == 1)
{{--                                         <li class="es_contacto list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$contacto->empleado->id}}">
                                            <a href="{{ route('covid.inicio', $contacto->empleado->id) }}"></a>
                                            <a class="btn btn-danger active"  style="font-size:10px" >POSITIVO A COVID</a>

                                        </li> --}}

                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <h4>{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</h4>
                                            </div>


                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold" style="color:#b42434; font-size: 20px;">Positivo a covid</span> <img src="{{ asset('/img/Recurso 22.png') }}" width="30px">     
                                            </div>
{{--                                             <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div> --}}
                                            <hr>
                                        </div>

                                    @elseif($contacto->estatus == 2)
{{--                                         <li class="es_contacto list-group-item list-group-item d-flex justify-content-between align-items-center" data-empleado="{{$contacto->empleado->id}}">
                                            <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</a>
                                            <a class="btn btn-success active"  style="font-size:10px" >SUPERÓ COVID</a>

                                        </li> --}}
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <h4>{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</h4>
                                            </div>


                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold" style="color:#b42434; font-size: 20px;">Superó covid</span> <img src="{{ asset('/img/Recurso 24.png') }}" width="30px">     
                                            </div>
{{--                                             <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div> --}}
                                            <hr>
                                        </div>


                                    @endif
                                @endforeach  
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row mt-4">
                @if($comprobante_vacunacion != null)  
                    @php $col = 'col-md-6' @endphp
                @else
                    @php $col = 'col-md-12' @endphp
                @endif
                <div class="{{$col}}">
                    <div class="article-nota border" style="padding: 1%;">
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4 text-center">
                                <h4 class="font-weight-bold">Notas:</h4>
                                <hr>
                            </div>
                            <div class="col-md-4"></div>
                        </div>


                        <div class="row">
                            <div class="col-md-12 text-center">
                                {{$registro->notas}}
                            </div>
                        </div>
                    </div>
                </div>
                @if($comprobante_vacunacion != null)
                    <div class="col-md-6">
                        <div class="article-nota border" style="padding: 1%;">
                            <div class="row">
                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <h4 class="font-weight-bold">Comprobante vacunación:</h4>
                                    <hr>
                                </div>
                                <div class="col-md-3"></div>
                            </div>


                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h5>Tipo de vacuna : <strong>{{$comprobante_vacunacion->tipo_vacuna}} </strong></h5>
                                </div>
                        
                                <div class="col-md-12">
                                    <h5 class="text-center"><b> Reacciones :</b></h5> {{$comprobante_vacunacion->reacciones}}
                                </div>
                                <div class="col-md-12 text-center">
                                    <br>
                                    <a href="{{asset('storage/repositorio').'/'.Session::get('empresa')['id'].'/'.$emp->id.'/'.$comprobante_vacunacion->comprobante }}" data-toggle="tooltip" title="Visualizar comprobante" target="_blank" ><img src="http://127.0.0.1:8000/img/Recurso 16.png" width="25px"></a> 
                                </div>
                                
                            </div>
                            
                        </div>
                    </div>
                @endif
            </div>

        @endforeach
    @else

        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6 mt-4">
                <div class="article-nota border">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <img src="{{ asset('/img/empleados-sin-registro-covid.png') }}" width="50px"> 
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h5 class="font-weight-bold">{{$emp->apaterno}} {{$emp->amaterno}} {{$emp->nombre}}</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h5 class="font-weight-bold">no tiene ningun Registro de contagio</h5>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-3"></div>
        </div>

    @endif


    @if($emp->es_contacto)
        @php  // dump($emp->es_contacto); @endphp
         @foreach($emp->es_contacto as $contacto)


    <div id="{{$contacto->id}}">
        <div class="row">
            <div class="col-md-6 mt-3">
                <div class="article-nota border">
                        <div class="row">
                            <div class="col-md-8 text-left">
                                <h4 class="font-weight-bold"> Trabajador con covid</h4>
                                <hr>

                                <div class="row">
                                    <div class="col-md-12">
                                        @if($contacto->es_contacto_de->first()->estatus == 0)
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h4>
                                                    <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">
                                                        
                                                            {{$contacto->es_contacto_de->first()->empleado->nombre}} {{$contacto->es_contacto_de->first()->empleado->apaterno}} {{$contacto->es_contacto_de->first()->empleado->amaterno}}  
                                                    </a>
                                                    </h4> 
                                                </div>
                                            </div>
   
                                        @elseif($contacto->es_contacto_de->first()->estatus == 1)
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">{{$contacto->es_contacto_de->first()->empleado->nombre}} {{$contacto->es_contacto_de->first()->empleado->apaterno}}  {{$contacto->es_contacto_de->first()->empleado->amaterno}}</a>
                                                </div>
                                            </div>

                                        @elseif($contacto->es_contacto_de->first()->estatus == 2)
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <a href="{{ route('covid.inicio',  $contacto->es_contacto_de->first()->empleado->id) }}">{{$contacto->es_contacto_de->first()->empleado->nombre}} {{$contacto->es_contacto_de->first()->empleado->apaterno}}  {{$contacto->es_contacto_de->first()->empleado->amaterno}}</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-left">
                                <div class="row">
                                    @if($contacto->es_contacto_de->first()->estatus == 0)
                                        <div class="col-md-12">
                                            <div class="btn-group-vertical me-2 btn-group-sm" role="group" aria-label="First group">
                                                <a class="btn btn-success active"  style="font-size:10px" >NEGATIVO A COVID</a>  
                                            </div>
                                        </div>

                                    @elseif($contacto->es_contacto_de->first()->estatus == 1)
                                        <div class="col-md-12">
                                            <div class="btn-group-vertical me-2 btn-group-sm" role="group" aria-label="First group">
                                            <a class="btn btn-danger active"  style="font-size:10px p-1" >POSITIVO A COVID <b>{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_inicio)->format('d-m-Y')}}</b></a>
                                            </div>
                                        </div>
                                    @elseif($contacto->es_contacto_de->first()->estatus == 2)
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <img src="{{ asset('/img/Recurso 24.png') }}" width="40px">
                                            </div>
                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold">Superó COVID</span>     
                                            </div>
                                            <div class="text-center">
                                                {{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}
                                            </div>
                                            
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="article-nota border">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="font-weight-bold"> Trabajador con covid</h4>
                            <hr>

                            <div class="row">
                                @if($contacto->estatus == 0)
                                    <div class="col-md-12">
                                        <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}} {{$contacto->empleado->amaterno}}</a>
                                    </div>

                                                        
{{--                                                         <div class="btn-group-vertical me-2 btn-group-sm" role="group" aria-label="First group">
                                                            <a class="btn btn-success active"  style="font-size:10px" >NEGATIVO A COVID</a>
                                                                
                                                                <a href="#" class="btn btn-warning  btn-xs" style="font-size:10px"
                                                                    data-toggle="modal" 
                                                                    data-target="#registroCovidModal"
                                                                    data-nombre="{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}"
                                                                    data-idempleado="{{$contacto->empleado->id}}"
                                                                    data-escontacto="{{$contacto->id}}"
                                                                    data-escontactode="{{$emp->id}}"
                                                                    data-idregistro="0"
                                                                >
                                                                    <span data-toggle="tooltip"  title="REGISTRO COVID" class="tooltip_">
                                                                        <i class="fa fa-ambulance"></i>&nbsp;REGISTRO COVID
                                                                    </span>
                                                                </a>
                                                            
                                                        </div> --}}

                                @elseif($contacto->estatus == 1)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}} {{$contacto->empleado->amaterno}}</a>
                                        </div>
                                    </div>
{{--                                                     <li class="list-group-item list-group-item d-flex justify-content-between align-items-center">
                                                        <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</a>
                                                        <a class="btn btn-danger active"  style="font-size:10px" >POSITIVO A COVID</a>

                                                    </li> --}}
                                @elseif($contacto->estatus == 2)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}} {{$contacto->empleado->amaterno}}</a>
                                        </div>
                                    </div>
                                    
{{--                                                     <li class="list-group-item list-group-item d-flex justify-content-between align-items-center">
                                                        <a href="{{ route('covid.inicio', $contacto->empleado->id) }}">{{$contacto->empleado->nombre}} {{$contacto->empleado->apaterno}}</a>
                                                        <a class="btn btn-success active"  style="font-size:10px" >SUPERÓ COVID</a>

                                                    </li> --}}

                                @endif
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                @if($contacto->estatus == 0)
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <img src="{{ asset('/img/Recurso 24.png') }}" width="40px">
                                            </div>
                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold">Negativo a COVID</span>     
                                            </div>
                                            <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div>
                                        </div>
                                @elseif($contacto->estatus == 1)
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <img src="{{ asset('/img/Recurso 22.png') }}" width="40px">
                                            </div>
                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold">Positivo a COVID</span>     
                                            </div>
                                            <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div>
                                        </div>
                                @elseif($contacto->estatus == 2)
                                        <div class="col-md-12">
                                            <div class="text-center">
                                                <img src="{{ asset('/img/Recurso 24.png') }}" width="40px">
                                            </div>
                                            <div class="text-center mt-2">
                                               <span class="font-weight-bold">Superó COVID</span>     
                                            </div>
                                            <div class="text-center">
                                                <span class="font-weight-bold">{{\Carbon\Carbon::parse($contacto->es_contacto_de->first()->fecha_fin)->format('d-m-Y')}}</span>
                                            </div>
                                        </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

                @endforeach


            @endif
        @else
                <p>No se encontró el empleado</p>
        @endif


</div>

@include('empleados.registro-covid-modal')
@include('empleados.registro-covid-editar-modal')
@include('empleados.evidencia-covid-modal')
@include('empleados.comprobante-covid-modal')

    <form method="post" id="usuario_delete_form" action="{{ route('covid.eliminar') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="id_delete" value="">
    </form>
@include('includes.footer')
<script type="text/javascript">
    $(".eliminar").click(function(){
        id=$(this).data('id');

        swal({
            title: "Estas seguro de eliminar el registro",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                document.getElementById("id_delete").value = id;
                swal("Espere un momento, la información esta siendo procesada", {
                    icon: "success",
                    buttons: false,
                });

                document.getElementById("usuario_delete_form").submit();

            } else {
                swal("La accion fue cancelada!");
            }
        });

    });
</script>