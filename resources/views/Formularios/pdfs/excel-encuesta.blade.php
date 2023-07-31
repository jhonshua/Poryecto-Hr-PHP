<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

</head>
<body>
    <table> 


        @php 

            $buscarSemana = array("covid", "covid-19", "corona virus");
            $cadena =strtolower($respuestas[0]['datos_generales']['titulo']);
           

            $encontrado = false;

            foreach ($buscarSemana as $valor) {
            $posicionCoincidencia = strpos($cadena, $valor);
            if ($posicionCoincidencia !== false) 
                $encontrado=true;
            }

            
        @endphp
        
        @foreach ($datos_personales as $key1=> $empleado )
            @php  
                $data= $respuestas[$key1]; 
                $contador;
            @endphp
            
            <tr>
                @if($key1 == 0 )
                    <td><b>Nombre completo</b></td>
                    <td><b>Departamento perteneciente</b></td>
                    <td><b>Correo</b></td>
                    <td><b>Edad</b></td>
                    @foreach($data['array_preguntas'] as  $key => $pregunta)
                    @php 
                        $contador=$key + 1;
                        $datos_preguntas = explode("*#--#", $pregunta);
                        $id = $datos_preguntas[0];
                        $arr_resp = $data['array_respuestas'][$id];
                    @endphp
                        <td><b>{{$contador.' .'}}{{$datos_preguntas[1]}}</b></td>    
                    @endforeach
                    @if($encontrado)<td><b>Posibles contagios</b></td> @endif
                @endif
            </tr>
            <tr>
                <td>{{$empleado['nombre_completo']}}</td>
                <td>{{$empleado['departamento']}}</td>
                <td>{{$empleado['correo']}}</td>
                <td>{{$empleado['edad']}}</td>
                @php $i=0; @endphp
                @foreach($data['array_preguntas'] as  $key => $pregunta)
                    @php 
                        $datos_preguntas = explode("*#--#", $pregunta);
                        $id = $datos_preguntas[0];
                        $arr_resp = $data['array_respuestas'][$id];
                    @endphp
                  
                    <td>  
                        @foreach ($arr_resp as $respuesta)
                            <ul class="list-group ">
                                @if($respuesta==="No contestada") 
                                    {{$respuesta}}
                                    @php $i=2 @endphp  
                                @else 
                                    @php $aux = strpos('Ningún síntoma.',$respuesta) @endphp
                                    @if($aux!==false)
                                        @php $i++; @endphp
                                    @endif
                                    {{$respuesta}} 
                                @endif
                            </ul>
                        @endforeach
                    </td>
                @endforeach
                @if($encontrado)
                    @if($i===0) <td style="background-color:#48F053;">Síntomas</td> @endif
                @endif
            </tr>
        @endforeach
    </table>    
</body>
</html>