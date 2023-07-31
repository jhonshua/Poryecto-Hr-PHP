@extends('layouts.principal_lista_avisos')
@section('tituloPagina', "Información de requisitos")

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card" >
            <div class="row no-gutters">
                <div class="col-md-7">
                    <div class="card-body ">
                        <h4 class="card-title">Mi beneficio solicitado :  
                        @if ($prestamo->estatus == 0)
                            <span class="font-weight-bold text-secondary">Cerrado</span>
                        @elseif ($prestamo->estatus == 1 && !$prestamo->usuario_id)
                            <span class="font-weight-bold text-success">Aún sin aprobar</span>
                        @elseif ($prestamo->estatus == 1)
                            <span class="font-weight-bold text-success">Abierto</span>
                        @elseif ($prestamo->estatus == 3)
                            <span class="font-weight-bold text-danger">Rechazado</span>
                        @elseif ($prestamo->estatus == 4)
                            <span class="font-weight-bold text-warning">En proceso de revisión</span>
                        @endif
                        </h4>
                        <br>
                        <h6 class="card-title">Nombre del empleado: </h6>
                        <h6 class="card-title"><strong> {{ $prestamo->empleado }} </strong></h6>
                        <br>
                        <h6 class="card-title">Empresa : </h6>
                        <h6 class="card-title"><strong> {{ $prestamo->empresa->razon_social }} </strong></h6>
                        <br>
                        @php

                            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"); 
                            $fecha_creacion =\Carbon\Carbon::parse($prestamo->fecha_creacion);
                            $mes_creacion = $meses[($fecha_creacion->format('n')) - 1];

                            $fecha =\Carbon\Carbon::parse($empleado->fecha_antiguedad);
                            $mes = $meses[($fecha->format('n')) - 1];

                        @endphp
                        <h6 class="card-title">Fecha/Hora de requisición de la solicitud:</h6>
                        <h6><strong> {{$fecha_creacion->format('d') . ' de ' . $mes_creacion . ' de ' . $fecha_creacion->format('Y h:ia')}}</strong></h6>
                        <br>
                        <h6 class="card-title">Tipo de solicitud: </h6>
                        <h6 class="card-title"><strong> {{ $prestamo->tipoPrestamo->nombre }} </strong></h6>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card-body">
                        <h6 class="card-title">Fecha de ingreso: </h6>
                        <h6><strong> {{$fecha->format('d') . ' de ' . $mes . ' de ' . $fecha->format('Y')}}</strong></h6>
                        <br>
                        <h6 class="card-title">Correo : </h6>
                        <h6><strong> {{ $empleado->correo  }} </strong></h6>
                        <br>
                        <h6 class="card-title">Medio de contacto : </h6>
                        <h6 class="card-title"><strong>{{$prestamo->medio_contacto }}</strong></h6>
                        <br>
                        <h6 class="card-title">Teléfono móvil : </h6>
                        <h6><strong>{{$empleado->telefonomovil  }}</strong></h6>
                        <br>
                        <a href="{{route('empleado.solicitudes.inicio')}}"><div class="btn btn-dark btn-block btn-sm ">Regresar  <li class="fas fa-undo"></li> </div></a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        @if(count($prestamo->requisitosLlenos)>0)
            <div class="card ">
                <div class="card-body">
                    <h5 class="card-title">Estatus de los requisitos solicitados</h5>
                    @if($prestamo->usuario_id !==0 || $prestamo->estatus!==2 )
                        <div class="table-responsive" >
                            <table class="table table-striped">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Datos cotejados</th>
                                </tr>
                                <tbody>
                                    @foreach ($prestamo->tipoPrestamo->requisitos as $req)
                                        @php $encontrado = false; @endphp
                                        <tr>
                                            <td width="350">{{$req->nombre}}:</td>
                                            <td>
                                                @if ($req->tipo == 'info')
                                                    {{ $req->valor}}
                                                @else
                                            
                                                    @foreach($prestamo->requisitosLlenos as $reqLleno)
                                                        @if($req->id == $reqLleno->prestamo_requisito_id)
                                                            @php $encontrado = true; @endphp
                                                            <p class="label-success"><i class="fas fa-check-circle text-success"> Completado</i></p>
                                                        @endif
                                                    @endforeach
                                                    @if (!$encontrado)
                                                        <p class="label-danger"><i class="fas fa-times-circle"> Faltante</i></p>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>  
                    @endif
                </div>
            </div>
        @endif
        <br>    
    </div>
</div>
@push('css')
<style>
.label-danger{
    color:#dc3545;
}
.label-success{
    color:green;
}
</style>
@endpush
@endsection

@push('scripts')
@endpush
