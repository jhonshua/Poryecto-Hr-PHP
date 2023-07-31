<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">  
    <div class="col-12 text-center">
        <form method="GET" action="{{route('timbrar.finiquito.inicio')}}">
              @csrf
              <input type="hidden" name="ejercicio" value="{{$anio_ejercicio}}">
              @include('includes.header-alt', ['title'=>'Timbrado de finiquito',
                'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-parametros-empresa.png'])
          </form>
    </div> 

    <div class="article border"> 
        <table class="table w-100">
            <thead>
                <tr>
                    <th width="">ID</th>              
                    <th width="">Nombre</th>
                    <th width="">Importe</th>                       
                    <th width="">No. Timbre</th> 
                    <th width="">Fecha Baja</th>           
                    <th width="">Opciones</th>   
                    
                </tr>
            </thead>
            <tbody>
                @foreach($datos_timbrado_finiquito as $dato)
                    <tr>
                        <td>{{$dato->id_empleado}}</td>                     
                        <td>{{$dato->nombre}}</td>             
                        <td>{{$dato->neto_fiscal}}</td> 
                        <td>{{$dato->no_factura}}</td> 
                        <td>{{$dato->fecha_baja}}</td> 
                        <td>                   
                            @php 
                                $idempleado = base64_encode($dato->id_empleado);
                                $archiv_xml = base64_encode($dato->file_xml);
                            @endphp
                            <div cclass="text-center">
                                <a class="button-style-custom text-nowrap" target="_blank" href="{{route('imprimir.recibo.finiquito', [$idempleado,$archiv_xml])}}">Imprimir Recibo</a>
                            </div>
                        </td>
                    </tr>
                @endforeach                
            </tbody>
        </table>
    </div>
</div>
@include('includes.footer')