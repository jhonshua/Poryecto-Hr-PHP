<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formulario {{$data['datos_generales']['titulo']}} </title>
    
    <style>
    .text-center {
        text-align: center ;
    }
    .text-right {
        text-align: right ;
    }
    .arial-12{
        font-size:12pt;
        font-family: Arial, Helvetica, sans-serif;
    }
    .arial-10{
        font-size:10pt;
        font-family: Arial, Helvetica, sans-serif;
    }
    .arial-8{
        font-size:8pt;
        font-family: Arial, Helvetica, sans-serif;
    }
    .margen{
        margin-left: 1.3cm;
        margin-right: 1.0cm;
    }
    .contenidoresp{
        margin-left: 0.5cm;
    }
    </style>
 </head>
<body>
    <div class="margen">
        <h3 class="arial-10" >Nombre completo : {{$nomempleado}}</h3>
        <h3 class="arial-10" >Departamento : {{$depart}}</h3>
        <h3 class="arial-10" >Correo electrónico : {{$correo}}</h3>
        <h3 class="arial-10" >Edad : {{$edad}}</h3>

        
        <!--<h3 class="arial-10" >Fecha de contestación:{{-- {{$fecha_finalizacion}}--}}</h3>-->
        <h2 class="text-center arial-10">{{$data['datos_generales']['titulo']}}</h2>
        <hr>
        <h3 class="arial-10">Resultados finales referentes a la {{$data['datos_generales']['titulo']}} por parte del empleado (a)  : {{$nomempleado}} .</h3>
        @php $contador @endphp
        @if(count($data['array_preguntas']))
            @foreach ($data['array_preguntas'] as $key=>$pregunta)
                @php 
                    $datos_preguntas = explode("*#--#", $pregunta);
                    $contador=$key + 1;
                    $id = $datos_preguntas[0];
                    $respuestas=$data['array_respuestas'][$id];
                @endphp
                   <div class="row">
                        <div class="col-md-12">
                            <p class="arial-10">Pregunta {{$contador}}  : {{$datos_preguntas[1]}}</p>
                        </div>
                        <div class="col-md-12">
                            @foreach ($respuestas as $respuesta)
                            <ul class="list-group ml-2">
                                @if($respuesta==="No contestada") 
                                    <li class="list-group"><span class="text-danger b-question">{{$respuesta}}</span></li>
                                @else
                                    <li class="list-group"><span class="text-success b-question">{{$respuesta}}</span></li>
                                @endif
                            </ul>
                        @endforeach
                        </div>
                    </div>
            @endforeach
        @endif
    </div>
</body>
</html>