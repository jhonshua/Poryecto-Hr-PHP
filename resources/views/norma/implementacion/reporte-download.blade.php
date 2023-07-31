@php                
   //dd($datos_categoria_dominio);
    $nivel_riesgo = array(0,0,0,0,0);
    $edadBarras = array();
    $profesionBarras = array();
    $contTabla = 9;
    $contGrafica = 7;
    $total_participantes = (count($mujeres) + count($hombres));
    $contGen = 1;
@endphp

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Implementación Norma Oficial Mexicana</title>
        <style>
            @font-face{
                
                font-family: "Maven Pro Light 300 Regular";
                src: url('{{public_path()."/fonts/mavenpro/a7a65bad96b6c72d21859e81ad765c02.eot"}}');
                src:url('{{public_path()."/fonts/mavenpro/a7a65bad96b6c72d21859e81ad765c02.eot?#iefix"}}') format("embedded-opentype"),  
                    url('{{public_path()."/fonts/mavenpro/a7a65bad96b6c72d21859e81ad765c02.woff2"}}') format("woff2"),
                    url('{{public_path()."/fonts/mavenpro/a7a65bad96b6c72d21859e81ad765c02.woff"}}') format("woff"),  
                    url('{{public_path()."/fonts/mavenpro/MavenProLight-300.ttf"}}') format("truetype"),
                    url('{{public_path()."/fonts/mavenpro/a7a65bad96b6c72d21859e81ad765c02.svg#Maven Pro Light 300 Regular"}}') format("truetype") 
            }
            
            body{
                font-family:"Maven Pro Light 300 Regular" !important;}
             
            table.tablaRes tr td{
                padding:15px 0px;
                font-size:15px;}
 
            .titulo{
                 text-align: center;}
                 
             #footer { position: fixed; left: 0px; bottom: -180px; right: 0px; height: 180px; background-color: #f0c018; text-align:center; }
             /*#footer .page:after { content: counter(page, upper-roman); }*/
        </style>
        <link rel="stylesheet" href="{{ public_path().'/css/reporte_norma.css'}}">
      
    </head>
    <body id="app">
        <div id="caratula" style="border-width:4px;border-style: double;">
            <table style="text-align:center;width:100%;">
                <tr>
                    <td colspan="{{$total_logos}}" style="font-size:5em;padding:180px 0px 120px 0px;font-weight:480;">Implementación<br/>Norma Oficial<br/>Mexicana</td>
                </tr>
                <tr>
                    <td colspan="{{$total_logos}}" style="font-size:4em;padding-bottom:100px;text-align:center;font-weight:480;"> NOM-035-STPS-2018</td>
                </tr>
                <tr>
                    @foreach($logos_portada as $logo)
                        <td><img src="{{$logo}}" style="height:100px;padding-bottom:75px;"/></td>
                    @endforeach
                </tr>
            </table>
        </div>
        <div id="footer">
            <p class="page">&#169;HRSystem</p>
        </div>
        <div style="page-break-after:always;"></div>
        <table style="width:100%;">
            <!-- Grafica Genero -->
            <tr>
                <td></td>
                <td class="titulo">Resultados por género<br/><br/></td>
            </tr>
            <tr>
                <td valign="top">
                <p class="nota"><b>Tabla 3.</b> Número de participantes por género.</p>
                    <table style="width:100%;" class="table" >
                        <tr class="thead-dark">
                            <th>Género</th>
                            <th>Participantes</th>
                        </tr>
                        <tr>
                            <td style="background:rgb(112,184,150);color:#ffffff;">Femenino</td>
                            <td>{{count($mujeres)}}</td>
                        </tr>
                        <tr>
                            <td style="background:rgb(149,102,223);color:#ffffff;">Masculino</td>
                            <td>{{count($hombres)}}</td>
                        </tr>
                        <tr>
                            <td style="background:rgb(202,206,204);color:#ffffff;">Otro</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <p class="nota">*Muestra total de {{$total_participantes}} participantes</p>
                </td>
                <td>
                    <div style="width:100%;">
                        <img src="{{$interpretaciones['78_1']['imagen']}}" style="width:500px;"/>
                    </div>
                    <p class="nota">
                    <b>Nota:</b> el gráfico representa el porcentaje total de la muestra de participantes conforme a su género. Del cual, se integró la variable “otro”  que facilita el proceso de detección de factores psicosociales y discriminatorios estipulados en la NOM-035-STPS-2018

                    </p>
                </td>
            </tr>                
            <tr>
                <td colspan="2" class="">
                    <br/><p class="interpretacion espacio2">{{$interpretaciones['78_1']['interpretacion']}}</p><br/>
                </td>
            </tr>
        </table>
        <div style="page-break-after:always;"></div>
        <!-- Grafica Mujeres  -->
        <table style="width:100%;">
            <tr>
                <td></td>
                <td class="titulo">Femenino<br/><br/></td>
            </tr>
            <tr>
                <td>
                    <p class="nota"><b>Tabla 3.1.</b> Número de participantes femeninos.</p>
                    <table style="width:100%;" class="table tableGr">
                        <tr class="thead-dark">
                            <th>Femenino</th>
                            <th>Porcentaje nivel de riesgo</th>
                        </tr>
                        @foreach($mujeresPie as $id =>$m)
                            <tr>
                                <td style="background:{{$m[0]}};">{{$m[1]}}</td>
                                <td>{{$m[2]}}</td>
                            </tr>
                        @endforeach
                    </table>
                    <p class="nota">*Muestra total de {{count($mujeres)}} participantes femeninos</p>
                </td>
                <td>
                        <div id="container-mujeres">
                            <img src="{{$interpretaciones['18_1']['imagen']}}" style="width:500px;"/>
                        </div>
                        <p class="nota">
                            <b>Nota:</b>  El gráfico representa el porcentaje total de participantes conforme a su género. Se reviso la muestra poblacional del género femenino en la implementación de la NOM-035-STPS-2018.
                                            Conforme a los resultados y tomando como referencia la tabla 3.1, se observa que el factor de riesgo del género es:

                        </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label>Factor de riesgo:</label>
                    <p>Conforme a los resultados y tomando como referencia la tabla 3.1, se observa que el factor de riesgo del género es:</p>
                    <table class="table tablaRes table-borderless" style="width:100%">
                        <tr id="r-mujeres">
                            @if(count($mujeresNivel)>0)
                                @foreach($mujeresNivel as $mn)
                                    <td style="background:{{$mn[0]}};">{{$mn[1]}}</td>
                                @endforeach
                            @else
                                <th>Sin resultados</th>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                  <p class="interpretacion">  {{$interpretaciones['18_1']['interpretacion']}}</p>
                </td>
            </tr>
        </table>
        <div style="page-break-after:always;"></div> 
         <!-- Grafica Hombres -->
         <table style="width:100%;">
            <tr>
                <td></td>
                <td class="titulo">Masculino<br/><br/></td>
            </tr>
            <tr>
                <td>
                    <p class="nota"><b>Tabla 3.2.</b> Número de participantes masculinos.</p>
                    <table style="width:100%;" class="table">
                        <tr class="thead-dark">
                            <th>Masculino</th>
                            <th>Porcentaje nivel de riesgo</th>
                        </tr>
                        @foreach($hombresPie as $m)
                            <tr>
                                <td style="background:{{$m[0]}};">{{$m[1]}}</td>
                                <td>{{$m[2]}}</td>
                            </tr>
                        @endforeach
                    </table>
                    <p class="nota">*Muestra total de {{count($hombres)}} participantes masculinos</p>
                </td>
                <td>
                        <div id="container-hombres"><img src="{{$interpretaciones['19_1']['imagen']}}" style="width:500px"/></div>
                        <p class="nota">
                        <b>Nota:</b> El gráfico representa el porcentaje total de participantes conforme a su género. Se reviso la muestra poblacional del género masculino en la implementación de la NOM-035-STPS-2018.
                        </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>Conforme a los resultados y tomando como referencia la tabla 3.2, se observa que el factor de riesgo del género es:</p>
                    <table class="table tablaRes" style="width:100%">
                        <tr id="r-hombres">
                            @if(count($hombresNivel)>0)
                                @foreach($hombresNivel as $mn)
                                    <td style="background:{{$mn[0]}};">{{$mn[1]}}</td>
                                @endforeach
                            @else
                                <th>Sin resultados</th>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                  <p class="interpretacion espacio5">  {{$interpretaciones['19_1']['interpretacion']}}</p>
                </td>
            </tr>
         </table>
         <div style="page-break-after:always;"></div>       
            <table style="width:100%;"> 
                <!-- Grafica  Edad  -->
                <tr>
                        <td></td>
                        <td class="titulo">Resultados por edad<br/><br/></td>
                    </tr>
                    <tr>
                        <td><br/>
                            <p class="nota"><b>Tabla 4.</b> Número de participantes por edad.</p>
                            <table style="width:100% !important;" class="table tableGr">
                                <tr class="thead-dark">
                                    <th>Edad</th>
                                    <th>Participantes</th>
                                </tr>
                                @foreach($edad['pieEdad'] as $ed)
                                    <tr>
                                        <td style="background:{{$ed[0]}};">{{$ed[1]}}</td>
                                        <td>{{$ed[2]}}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <p class="nota">*Muestra total de {{$total_participantes}} participantes</p>

                        </td>
                        <td>
                            <div id="container-edad"><img src="{{$interpretaciones['79_1']['imagen']}}" style="width:500px"/></div>
                            <p class="nota">
                                <b>Nota:</b> El gráfico representa el porcentaje total de edad de los colaboradores dentro del centro de trabajo, no importando su género.
                            </p>
                           
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label>Factor de riesgo:</label>
                            <table class="table tablaRes table-borderless" style="width:100%">
                                <tr id="r-edad">
                                @if(count($edad['edadNivel'])>0)
                                    @foreach($edad['edadNivel'] as $ed)
                                        <td style="background:{{$ed[0]}};">{{$ed[1]}}</td>
                                    @endforeach
                                @else
                                    <th>Sin resultados</th>
                                @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                          <p class="interpretacion">  {{$interpretaciones['79_1']['interpretacion']}}</p>               
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="2"><br/>
                        <h5>A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:</h5><br/>
                            <p class="nota"><b>Tabla 4.1.</b> Nivel de riesgo de los bloques de edad de los participantes.</p>
                            <table style="width:100% !important;" class="table text-center tableGr">
                                <tr class="thead-dark">
                                    <th>Edad</th>
                                    <th style="background:rgb(84,226,248);color:#000;">Nulo</th>
                                    <th style="background:rgb(100,247,129);color:#000;">Bajo</th>
                                    <th style="background:rgb(249,249,83);color:#000;">Medio</th>
                                    <th style="background:rgb(243,152,54);color:#000;">Alto</th>
                                    <th style="background:rgb(249,51,27);color:#000;">Muy alto</th>
                                    <th>Participantes</th>
                                    <th>% Nivel de riesgo</th>
                                    <th>Nivel de riesgo</th>
                                </tr>
                                @foreach($edad['edad'] as $ed)
                                    <tr>
                                        <td style="background:{{$ed[3]}};">{{$ed[1]}}</td>
                                        @foreach($ed[0] as $tot)
                                                <td>{{$tot[2]}}</td>
                                        @endforeach
                                        <td>{{$ed[2]}}</td>
                                        <td>{{$ed[5]}} %</td>
                                        <td>
                                            <table style="width:100%" class="table-borderless">
                                                <tr>
                                                    @if(count($ed[4]) > 0)
                                                    @foreach($ed[4] as $total)
                                                        <td style="background:{{$total[0]}}">{{$total[1]}}</td>
                                                    @endforeach
                                                    @else
                                                        <td>Ninguno</td>
                                                    @endif
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    @php    $edadBarras[] = array($ed[3],$ed[1],$ed[5]);        @endphp
                                @endforeach
                            </table>
                            <p class="nota">*Muestra total de {{$total_participantes}} participantes</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="container-edad-barra"><center><img src="{{$interpretaciones['79_2']['imagen']}}" style="width:600px"/></center></div>
                            <p class="nota">
                                <b>Nota:</b> Porcentajes extraídos de la calificación general de los cuestionarios.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                          <p class="interpretacion espacio3">  {{$interpretaciones['79_2']['interpretacion']}}</p>
                        </td>
                    </tr>
        </table>
        <div style="page-break-after:always;"></div>       
        <table style="width:100%;"> 
        <!-- Grafica  nivel academico -->
        <tr>
            <td></td>
            <td class="titulo">Resultados por nivel académico<br/><br/></td>
        </tr>
        <tr>
            <td><br/>
            <p class="nota"><b>Tabla 5.</b> Participantes por nivel académico.</p>
                <table style="width:100% !important;font-size:11px;" class="table tableGr">
                    <tr class="thead-dark">
                            <th>Nivel académico</th>
                            <th>Participante</th>
                    </tr>
                    @foreach($profesion['pieProfesion'] as $pr)
                        <tr>
                            <td style="background:{{$pr[0]}};">{{$pr[1]}}</td>
                            <td>{{$pr[2]}}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td>
                <div id="container-profesion"><img src="{{$interpretaciones['77_1']['imagen']}}" style="width:500px"/></div>
                <p class="nota">
                    <b>Nota:</b>Los porcentajes mostrados están basados en la información proporcionada por cada uno de los participantes, no son datos confirmados, por lo que, se considera puede existir un pequeño sesgo en la información mostrada.
                </p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label>Factor de riesgo:</label>
                <table class="table tablaRes table-borderless" style="width:100%">
                    <tr id="r-profesion">
                        @if(count($profesion['profesionNivel'])>0)
                            @foreach($profesion['profesionNivel'] as $pr)
                                <td style="background:{{$pr[0]}};">{{$pr[1]}}</td>
                            @endforeach
                        @else
                            <th>Sin resultados</th>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
              <p class="interpretacion">  {{$interpretaciones['77_1']['interpretacion']}}</p>
            </td>
        </tr>
       
        <tr>
            <td colspan="2"><br/><br/>
            <h5>A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:<h5>
            <p class="nota"><b>Tabla 5.1</b> Participantes por nivel académico.</p>
                <table style="width:100% !important;" class="table text-center tableGr">
                    <tr class="thead-dark">
                        <th>Profesión</th>
                        <th style="background:rgb(84,226,248);color:#000;">Nulo</th>
                        <th style="background:rgb(100,247,129);color:#000;">Bajo</th>
                        <th style="background:rgb(249,249,83);color:#000;">Medio</th>
                        <th style="background:rgb(243,152,54);color:#000;">Alto</th>
                        <th style="background:rgb(249,51,27);color:#000;">Muy alto</th>
                        <th>Participantes</th>
                        <th>% Nivel de riesgo</th>
                        <th>Nivel de riesgo</th>
                    </tr>
                    @foreach($profesion['profesion'] as $pr)
                    <tr>
                        <td style="background:{{$pr[3]}};">{{$pr[1]}}</td>
                        @foreach($pr[0] as $tot)
                            <td>{{$tot[2]}}</td>
                        @endforeach
                        <td>{{$pr[2]}}</td>
                        <td>{{$pr[5]}} %</td>
                        <td>
                            <table style="width:100%" class="table-borderless">
                                <tr>
                                @if(count($pr[4]) > 0)
                                    @foreach($pr[4] as $total)
                                        <td style="background:{{$total[0]}}">{{$total[1]}}</td>
                                    @endforeach
                                @else
                                    <td>Ninguno</td>
                                @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @php    $profesionBarras[] = array($pr[3],$pr[1],$pr[5]);        @endphp
                    @endforeach
                </table>
                <p class="nota">*Muestra total de {{$total_participantes}} participantes</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                        <div id="container-profesion-barra">
                            <center><img src="{{$interpretaciones['77_2']['imagen']}}" style="width:650px"/></center>
                        </div>
                        <p class="nota">
                            <b>Nota:</b> el gráfico representa el porcentaje total de participantes conforme a su profesión.
                        </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                  <p class="interpretacion espacio2">  {{$interpretaciones['77_2']['interpretacion']}}</p>
                </td>
            </tr>
        </table>
        <div style="page-break-after:always;"></div>       
        <table style="width:100%;"> 

        <!-- Grafica  area de trabajo -->
        <tr>
            <td></td>
            <td class="titulo">Resultados por área<br/><br/></td>
        </tr>
        <tr>
            <td><br/>
            <p class="nota"><b>Tabla 6.</b> Participantes por área.</p>
                <table style="width:100% !important;" class="table tableGr">
                    <tr class="thead-dark">
                            <th>Área</th>
                            <th>Participante</th>
                    </tr>
                    @foreach($area['pieArea'] as $ar)
                        <tr>
                            <td style="background:{{$ar[0]}};">{{$ar[1]}}</td>
                            <td>{{$ar[2]}}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td>
                <div id="container-area">
                <center><img src="{{$interpretaciones['80_1']['imagen']}}" style="width:500px"/></center>
                </div>
                <p class="nota">
                    <b>Nota:</b> Los porcentajes mostrados están basados en la información facilitada por cada uno de los participantes, no son datos confirmados, por lo que, se considera puede existir un pequeño sesgo en la información mostrada.
                </p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label>Factor de riesgo:</label>
                <table class="table tablaRes table-borderless" style="width:100%">
                    <tr id="r-area">
                        @if(count($area['areaNivel'])>0)
                            @foreach($area['areaNivel'] as $ar)
                                <td style="background:{{$ar[0]}};">{{$ar[1]}}</td>
                            @endforeach
                        @else
                            <th>Sin resultados</th>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
              <p class="interpretacion espacio5">  {{$interpretaciones['80_1']['interpretacion']}}</p>
            </td>
        </tr>
        
        <tr>
            <td colspan="2"><br/>
            <h5>A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:</h5><br/>
            <p><b>Tabla 6.1</b> Participantes por Área.</p>
                <table style="width:100% !important;" class="table text-center tableGr">
                    <tr class="thead-dark">
                        <th>Área</th>
                        <th style="background:rgb(84,226,248);color:#000;">Nulo</th>
                        <th style="background:rgb(100,247,129);color:#000;">Bajo</th>
                        <th style="background:rgb(249,249,83);color:#000;">Medio</th>
                        <th style="background:rgb(243,152,54);color:#000;">Alto</th>
                        <th style="background:rgb(249,51,27);color:#000;">Muy alto</th>
                        <th>Participantes</th>
                        <th>% Nivel de riesgo</th>
                        <th>Nivel de riesgo</th>
                    </tr>
                    @foreach($area['area'] as $ar)
                    <tr>
                        <td style="background:{{$ar[3]}};">{{$ar[1]}}</td>
                        @foreach($ar[0] as $tot)
                            <td>{{$tot[2]}}</td>
                        @endforeach
                        <td>{{$ar[2]}}</td>
                        <td>{{$ar[5]}} %</td>
                        <td>
                            <table style="width:100%" class="table-borderless">
                                <tr>
                                @if(count($ar[4]) > 0)
                                    @foreach($ar[4] as $total)
                                        <td style="background:{{$total[0]}}">{{$total[1]}}</td>
                                    @endforeach
                                @else
                                    <td>Ninguno</td>
                                @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @php    $areaBarras[] = array($ar[3],$ar[1],$ar[5]);        @endphp
                    @endforeach
                </table>
                <p class="nota">*Muestra total de {{$total_participantes}} participantes</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div id="container-area-barra"><center><img src="{{$interpretaciones['80_2']['imagen']}}" style="width:500px"/></center></div>
                    <p class="nota">
                        <b>Nota:</b> el gráfico representa el porcentaje total de participantes conforme a su área.
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                  <p class="interpretacion">  {{$interpretaciones['80_2']['interpretacion']}}</p><br/>
                </td>
            </tr>

        </table>
        <div style="page-break-after:always;"></div>       
        <table style="width:100%;"> 
        <!-- Grafica General -->
            <tr>
                <td></td>
                <td class="titulo">General<br/><br/></td>
            </tr>
            <tr>
                <td>
                    <p class="nota"><b>Tabla 8</b> Criterios para identificar que decisión se debe tomar.</p>
                    <table style="width:100%;" class="table tableGr">
                        <tr class="thead-dark">
                            <th>Nivel de riesgo</th>
                            <th>Porcentaje nivel de riesgo</th>
                        </tr>
                        @foreach($general['pie'] as $p)
                        <tr>
                            <td style="background:{{$p[0]}};">{{$p[1]}}</td>
                            <td>{{$p[2]}}</td>
                        </tr>
                        @endforeach
                    </table>
                    <p class="nota">*Muestra total de {{$total_participantes}} participantes.</p>
                    
                </td>
                <td>
                    <div id="container-general"><img src="{{$interpretaciones['81_1']['imagen']}}" style="width:500px"/></div></div>
                    <p class="nota">
                    <b>Nota:</b> El gráfico representa el porcentaje de la muestra poblacional  en relación al nivel de riesgo que se puede presentar conforme dicta la NOM-035-STPS-2018
                                (Conforme a los resultados y en referencia a la tabla 8, que se observa el factor de riesgo)

                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p> Conforme a los resultados y en referencia a la tabla 8, que se observa el factor de riesgo</p>
                    <table class="table tablaRes table-borderless" style="width:100%">
                        <tr>
                            @if(count($general['nivelRiesgo'])>0)
                                @foreach($general['nivelRiesgo'] as $gn)
                                    <td style="background:{{$gn[0]}};">{{$gn[1]}}</td>
                                @endforeach
                            @else
                                <th>Sin resultados</th>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p class="interpretacion espacio5">  {{$interpretaciones['81_1']['interpretacion']}}</p>
                </td>
            </tr>

        </table>

        <!-- C A T E G O R I A   Y   D O M I N I O  -->

            @foreach($datos_categoria_dominio as $id => $cd)
                @php 
                    $tituloTabla = ($cd[1] == 'categoría')? 'Resultados de la '.$cd[1].' "'.$cd[0].'"' : 'Resultados del '.$cd[1].' "'.$cd[0].'"';  
                    $tituloDescripcion = ($cd[1] == 'categoría')? ' de la '.$cd[1].' "'.$cd[0].'"' : ' del '.$cd[1].' "'.$cd[0].'"';  
                @endphp
                
                <div style="page-break-after:always;"></div>
                <table style="width:100%;"> 
                    <tr>
                        <td></td>
                        <td class="titulo"><p style="height:30px !important;">{{$tituloTabla}}</p><br/></td>
                    </tr>
                    <tr>
                        <td>
                            <p class="nota"><b>Tabla {{$contTabla}}.</b> identificación del nivel de riesgo en {{$cd[0]}}.</p>
                            <table style="width:100%;" class="table tableGr">
                                <tr class="thead-dark">
                                    <th>{{$cd[0]}}</th>
                                    <th>Nivel de riesgo</th>
                                </tr>
                                @foreach($total_clasificacion_dominio[$id] as $i => $t)
                                    <tr>
                                        <td style="background:{{$t[2]}};">{{$t[3]}}</td>
                                        <td>{{$t[4]}}</td>
                                    </tr>
                                @endforeach 
                            </table>
                            <p class="nota">*Muestra total de {{$total_participantes}} participantes.</p>
                            
                        </td>
                        <td>
                            <div id="container-cat-dom{{$id}}">
                                <img src="{{$interpretaciones[$id.'_1']['imagen']}}" style="width:500px"/></div>
                            </div>

                            <p class="nota">
                                <b>Nota:</b>Se representa nuestra muestra de {{$total_participantes}} participantes, evaluando el nivel de riesgo {{$tituloDescripcion}}.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="nriesgo">
                                <p>Conforme a los resultados y en referencia a la tabla {{$contTabla}}, que se observa el factor de riesgo:</p>
                                    <table class="table tablaRes table-borderless" style="width:100%">
                                        <tr id="r-cat-dom{{$id}}">
                                            @if(count($cd[2])>0)
                                                @foreach($cd[2] as $mn)
                                                    <td style="background:{{$mn[2]}};">{{$mn[3]}}</td>
                                                @endforeach
                                            @else
                                                <th>Sin resultados</th>
                                            @endif
                                        </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><br/>
                            <p class="interpretacion">{{$interpretaciones[$id.'_1']['interpretacion']}}</p>
                        </td>
                    </tr>
                    @php $contTabla++;  @endphp
        </table>
               
              
            @endforeach
            <div style="page-break-after:always;"></div>       
            <table style="width:100%;"> 
            <!-- Grafica General -->
                <tr>
                    <td></td>
                    <td class="titulo">General<br/><br/></td>
                </tr>
                <tr>
                    <td>
                        <p class="nota"><b>Tabla 8</b> Criterios para identificar que decisión se debe tomar.</p>
                        <table style="width:100%;" class="table tableGr">
                            <tr class="thead-dark">
                                <th>Nivel de riesgo</th>
                                <th>Porcentaje nivel de riesgo</th>
                            </tr>
                            @foreach($general['pie'] as $p)
                            <tr>
                                <td style="background:{{$p[0]}};">{{$p[1]}}</td>
                                <td style="text-align: center">{{$p[2]}}</td>
                            </tr>
                            @endforeach
                        </table>
                        <p class="nota">*Muestra total de {{$total_participantes}} participantes.</p>
                        
                    </td>
                    <td>
                        <div id="container-general"><img src="{{$interpretaciones['81_1']['imagen']}}" style="width:500px"/></div></div>
                        <p class="nota">
                        <b>Nota:</b> El gráfico representa el porcentaje de la muestra poblacional  en relación al nivel de riesgo que se puede presentar conforme dicta la NOM-035-STPS-2018
                                    (Conforme a los resultados y en referencia a la tabla 8, que se observa el factor de riesgo)
    
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p> Conforme a los resultados y en referencia a la tabla 8, que se observa el factor de riesgo</p>
                        <table class="table tablaRes table-borderless" style="width:100%">
                            <tr>
                                @if(count($general['nivelRiesgo'])>0)
                                    @foreach($general['nivelRiesgo'] as $gn)
                                        <td style="background:{{$gn[0]}};">{{$gn[1]}}</td>
                                    @endforeach
                                @else
                                    <th>Sin resultados</th>
                                @endif
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="interpretacion espacio5">  {{$interpretaciones['81_1']['interpretacion']}}</p>
                    </td>
                </tr>
    
            </table>
             <!-- T A B L A  D E  R E S U L T A D O S -->
        <table style="width:100%;">
             <tr>
                <td colspan="2" >
                    <p class="nota"><b>Tabla 7</b> Nivel de riesgo por cada uno de los participantes.</p>
                    <center>
                    <table class="table tableGr" style="width:100%">
                        <tr class="thead-dark">
                            <th>Participante</th>
                            <th>Puntuación</th>
                            <th>Nivel de riesgo</th>
                        </tr>
                        @php $contPag = 0; $contadorEmp = 0; @endphp
                        @foreach($generalResultados as $g)
                            @if($contadorEmp == 30)
                                @if($contPag > 0)
                                    </table>
                                    <div style="page-break-after:always;"></div> 
                                    <table style="width:100%;" class="table tableGr">
                                @else
                                    @php $contPag++; @endphp
                                @endif
                                @php $contadorEmp = 0; @endphp
                            @endif
                            <tr>
                                <td>{{$g[3]}}</td>
                                <td>{{$g[2]}}</td>
                                <td style="background:{{$g[0]}}">{{$g[1]}}</td>
                            </tr>
                            @php $contadorEmp++; @endphp
                        @endforeach

                    </table>
                    </center>
                </td>
            </tr>

        </table> 
    </body>
</html>