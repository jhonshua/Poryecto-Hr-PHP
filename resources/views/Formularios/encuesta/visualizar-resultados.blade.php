<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">
        @php $ruta =route('formularios.obtenerEmpleadosAsignados',['id'=>Crypt::encrypt($consultas['datos_generales']['id'])]) @endphp
        @include('includes.header',['title'=>'Resultados empleado',
        'subtitle'=>'Formularios', 'img'=>'img/header/norma/icono-norma.png',
        'route'=>'formularios.inicio'])

            <div class="article border box-shadow">
                <h4 class="text-center"> <strong> {{$consultas['datos_generales']['titulo']}} </strong></h4><br>
                <p class="font-size-1-2em">Resultados finales referentes a la <strong>{{$consultas['datos_generales']['titulo']}}</strong> por parte del empleado (a) : <strong>{{$datos['nombreEmpleado']}}</strong></p><hr>
                <div class="form-group row">
                    <div class="col-md-5">
                        <p class="font-size-1-2em">Empleado: <b>{{$datos['nombreEmpleado']}}</b></p>
                    </div>
                    <div class="col-md-3">
                        <p class="font-size-1-2em">Departamento: <b>{{$datos['departamento']}}</b></p>
                    </div>
                    <div class="col-md-4">
                        <p class="font-size-1-2em">Fecha vencimiento: <span class="text-danger b-question"> {{$consultas['datos_generales']['fecha_vencimiento']}}</span></p>
                    </div>
                </div>
                <hr>
                <div class="content" >
                    @foreach ($consultas['array_preguntas'] as $key=> $preguntas)
                        @php
                            $npreguntas = $key + 1;
                            $cadena = explode('*#--#',$preguntas);
                            $id = $cadena[0];
                            $pregunta = $cadena[1];
                            $respuestas=$consultas['array_respuestas'][$id];
                        
                        @endphp
                        <div class="row">
                            <div class="col-md-12">
                                <p class="font-size-1-2em"><b>Pregunta : {{$npreguntas}} .- </b>{{$pregunta}}</p>
                            </div>
                            <div class="col-md-12">
                                @foreach($respuestas as $respuesta)
                                    <ul class="list-group ml-2">
                                        @if($respuesta==="No contestada") 
                                            <li class="list-group"><span class="text-danger b-question">{{$respuesta}}</span></li>
                                        @else
                                            <li class="list-group"><span class="text-success b-question">{{$respuesta}}</span></li>
                                        @endif
                                    </ul>
                                @endforeach
                                <br>
                            </div>
                        </div> 
                    @endforeach
                </div>
                <br>        
                @if($datos['estatus']!=3)
                    <div class="col-md-2 center">
                       <a href="{{route('formularios.cerrarEncuesta',['id_empleado'=>$datos['idemplado'],'id_encuesta'=> $datos['idencuesta']])}}"><div class="button-style cerrar_encuesta">Cerrar encuesta</div></a>
                    </div>
                @endif
            </div>
        </div>
        <script>
        $(document).ready(function() {});
        </script>
        @include('includes.footer')
    </body>
</html>
