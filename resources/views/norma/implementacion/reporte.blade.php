<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>    
@include('includes.navbar')
<link href="{{ asset('/css/steps.css') }}" rel="stylesheet">
<style>
h3{
    text-align:center;
}
    .invalido{
        color:#EE4A30;
    }
    .tableGr th, .tableGr td {
        padding: 0.25rem;
        text-align: center;
    }
    hr {  
        height: 7px;
        margin: 0;
        flex-grow: 1;
        transition: all .8s ease-in-out;
        background:#F2D56F;
    }
    
    .wizard > .steps a, .wizard > .steps a:hover, .wizard > .steps a:active {
        margin: 0 0.1em 0.5em;
        padding: 0em 1em;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .wizard > .steps .number {
        font-size: 1.1em;
    }

    section{
        padding:20px
    }

    .wizard > .content > .body {
        float: left;
        position: absolute;
        width: 100%;
        height: 95%;
        padding: 2% 2%;
    }

    .wizard > .content > .body table{
        width: 100%;    
    }
   
    .dataTables_scrollHeadInner, .dataTables_scrollHeadInner .tableGr{
        width:100% !important;
    }
</style>
@php                
    $id_periodo_norma = 0;
    $tipo_cuestionario = 2;
    if($periodoNorma)   {
        $id_periodo_norma = $periodoNorma->id;
    }
   // echo $id_periodo_norma."--";
    $nivel_riesgo = array(0,0,0,0,0);
    $edadBarras = array();
    $profesionBarras = array();
    $contTabla = 9;
    $contGrafica = 7;
    $total_participantes = (count($mujeres) + count($hombres));
    $contGen = 1;
    $tImplementaciones = 10; 
@endphp
<div class="container"> 

@include('includes.header',['title'=>'Reporte implementación',
        'subtitle'=>'Norma 035', 'img'=>'img/header/norma/icono-reporte.png',
        'route'=>'norma.normaTabla'])   

    @if(session()->has('success'))
    <div class="row">
        <div class="alert alert-success" style="width: 100%;" align="center">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Notificación: </strong>
            {{ session()->get('success') }}
        </div>
    </div>
    @endif

    <div class="row d-flex justify-content-between">
        <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9 mb-3">
            <div class="">    
                <form action="{{route('norma.implementacion.reporte.generar')}}" id="reporte" method="post" target="_blank">
                    @csrf
                    <input type="hidden" id="Rimplementacion" name="implementacion" value="{{$datosImplementacion->id}}"/>
                    <input type="hidden" id="Rperiodo_norma" name="periodo_norma" value="{{$id_periodo_norma}}" />
                    <div id="botonReporte"></div>
                </form>            
            </div>           
        </div>        
    </div>

    <div class="article border ">      
        <form action="" role="form" id="reporteImplementacion" name="reporteImplementacion" method="post">
            @csrf
            <div id="step-vertical" style="display:none;">
                <h3 id="78_1h3"> Resultados por género</h3>
                <section sryle="heigth:auto !importants;">
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Resultados por género</h1></td>
                        </tr>
                        <tr>
                            <td>
                                <p><b>Tabla 3.</b> Número de participantes por género.</p>
                                <table style="width:100%;" class="table table-bordered tableGr">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes</p>
                            </td>
                            <td>
                                <figure class="highcharts-figure-sexo">
                                    <div id="container-sexo"></div>
                                    <p class="highcharts-description">
                                    <b>Nota:</b> el gráfico representa el porcentaje total de la muestra de participantes conforme a su género. Del cual, se integró la variable “otro”  que facilita el proceso de detección de factores psicosociales y discriminatorios estipulados en la NOM-035-STPS-2018
                                    </p>
                                </figure>
                            </td>
                        </tr>                
                        <tr>
                            <td colspan="2">
                                <label for="interpretacion_genero"><strong>Interpretación: </strong></label>
                                <textarea max="500" style="width:100%;height:100px;" id="interpretacion_78_1" name="interpretacion_genero" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_78_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b781" idCat="78" tipoGra="1" >Guardar</button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </section>

                <!-- Grafica Mujeres -->
                <h3>Femenino</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Femenino</h1></td>
                        </tr>
                        <tr>
                            <td>
                                <p><b>Tabla 3.1.</b> Número de participantes femeninos.</p>
                                <table style="width:100%;" class="table table-bordered tableGr">
                                    <tr class="thead-dark">
                                        <th>Femenino</th>
                                        <th>Nivel de riesgo</th>
                                    </tr>
                                    @foreach($mujeresPie as $id =>$m)
                                        <tr>
                                            <td style="background:{{$m[0]}};">{{$m[1]}}</td>
                                            <td>{{$m[2]}}</td>
                                        </tr>
                                    @endforeach
                                </table>
                                <p class="small">*Muestra total de {{count($mujeres)}} participantes femeninos</p>
                            </td>
                            <td>
                                <figure class="highcharts-figure-mujeres">
                                    <div id="container-mujeres"></div>
                                    <p class="highcharts-description">
                                    <b>Nota:</b> El gráfico representa el porcentaje total de participantes conforme a su género. Se reviso la muestra poblacional del género femenino en la implementación de la NOM-035-STPS-2018.
                                    </p>
                                </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="resultado-mujeres" class="text-center">
                                <p>Conforme a los resultados y tomando como referencia la tabla 3.1, se observa que el factor de riesgo del género es:</p>
                                <table class="table table-bordered">
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
                                <label for="interpretacion_mujeres"><strong>Interpretación: </strong></label>
                                <textarea max="500" style="width:100%;height:100px;" id="interpretacion_18_1" name="interpretacion_mujeres" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_18_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b181" idCat="18" tipoGra="1">Guardar</button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </section>
                
                <!-- Grafica Hombres -->
                <h3>Masculino</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Masculino</h1></td>
                        </tr>
                        <tr>
                            <td>
                                <p><b>Tabla 3.2.</b> Número de participantes masculinos.</p>
                                <table style="width:100%;" class="table table-bordered tableGr">
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
                                <p class="small">*Muestra total de {{count($hombres)}} participantes masculinos</p>
                            </td>
                            <td>
                                <figure class="highcharts-figure-hombres">
                                    <div id="container-hombres"></div>
                                    <p class="highcharts-description">
                                    <b>Nota:</b> El gráfico representa el porcentaje total de participantes conforme a su género. Se reviso la muestra poblacional del género masculino en la implementación de la NOM-035-STPS-2018.
                                    </p>
                                </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="resultado-hombres"  class="text-center">
                            <p>Conforme a los resultados y tomando como referencia la tabla 3.2, se observa que el factor de riesgo del género es:</p>
                                <table class="table table-bordered">
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
                                <label for="interpretacion_hombres"><strong>Interpretación: </strong> </label>
                                <textarea max="500" style="width:100%;height:100px;" name="interpretacion_hombres" id="interpretacion_19_1" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_19_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b191" idCat="19" tipoGra="1">Guardar</button>
                                </div>
                            </td>
                        <tr>
                    </table>
                </section>

                <!-- Grafica  Edad-->
                <h3>Resultados por edad</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Resultados por edad</h1></td>
                        </tr>
                        <tr>
                            <td><br/><br/>
                                <p><b>Tabla 4.</b> Número de participantes por edad.</p>
                                <table style="width:100% !important;" class="table table-bordered tableGr">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes</p>
                            </td>
                            <td>
                                <figure class="highcharts-figure-edad" >
                                    <div id="container-edad"></div>
                                    <p class="highcharts-description text-center">
                                        <b>Nota:</b> El gráfico representa el porcentaje total de edad de los colaboradores dentro del centro de trabajo, no importando su género.
                                    </p>
                                </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="resultado-edad" class="text-center">
                                <label>Factor de riesgo:</label>
                                <table class="table table-bordered">
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
                                <label for="interpretacion_edad"><strong>Interpretación: </strong> </label>
                                <textarea max="500" style="width:100%;height:100px;" name="interpretacion_edad" id="interpretacion_79_1" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_79_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b791" idCat="79" tipoGra="1">Guardar</button>
                                </div>                   
                            </td>
                        </tr>
                    </table>
                </section>

                <h3>Analisis de riesgo edad</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><br><h5>A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:</h5></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p><b>Tabla 4.1.</b> Nivel de riesgo de los bloques de edad de los participantes.</p>
                                <table style="width:100% !important;" class="table table-bordered text-center tableGr">
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
                                                <table style="width:100%">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <figure class="highcharts-figure-edad-barra" >
                                    <div id="container-edad-barra"></div>
                                    <p class="highcharts-description text-center">
                                        <b>Nota:</b> Porcentajes extraídos de la calificación general de los cuestionarios.
                                    </p>
                                </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label for="interpretacion_edad_barras"><strong>Interpretación: </strong></label>
                                <textarea max="500" style="width:100%;height:100px;" id="interpretacion_79_2" name="interpretacion_edad_barras" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_79_2"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b792" idCat="79" tipoGra="2">Guardar</button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </section>

            <!-- Grafica  nivel academico -->

                <h3>Nivel academico</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Resultados por nivel academico</h1></td>
                        </tr>
                        <tr>
                            <td>
                            <p><b>Tabla 5.</b> Participantes por nivel académico.</p>
                                <table style="width:100% !important;" class="table table-bordered tableGr">
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
                                <figure class="highcharts-figure-profesion" >
                                    <div id="container-profesion"></div>
                                        <p class="highcharts-description text-center">
                                            <b>Nota:</b> Los porcentajes mostrados están basados en la información proporcionada por cada uno de los participantes, no son datos confirmados, por lo que, se considera puede existir un pequeño sesgo en la información mostrada.
                                        </p>
                                    </figure>
                            </td>
                        </tr>
                    <tr>
                        <td colspan="2" id="resultado-profesion"  class="text-center">
                            <label>Factor de riesgo:</label>
                            <table class="table table-bordered">
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
                            <label for="interpretacion_nivel_academico"><strong>Interpretación: </strong> </label>
                            <textarea max="500" style="width:100%;height:100px;" name="interpretacion_nivel_academico" id="interpretacion_77_1" class="form-control"></textarea max="500">
                            <div class="form-row" id="botones" style="padding:25px 0;">
                                <input type="hidden" id="imagen_77_1"/>
                                <button class="button-style-custom guardar-interpretacion ml-3" id="b771" idCat="77" tipoGra="1">Guardar</button>
                            </div>
                        </td>
                    </tr>
                    
                    </table>
                </section>

                <h3>Analisis de riesgo nivel academico</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center">A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:</p></td>
                        </tr>
                        <tr>
                            <td colspan="2"><br/><br/>
                            <p><b>Tabla 5.1</b> Participantes por nivel académico.</p>
                                <table style="width:100% !important;" class="table table-bordered text-center tableGr">
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
                                            <table style="width:100%">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <figure class="highcharts-figure-profesion-barra" >
                                        <div id="container-profesion-barra"></div>
                                        <p class="highcharts-description text-center">
                                            <b>Nota:</b> el gráfico representa el porcentaje total de participantes conforme a su profesión.
                                        </p>
                                    </figure>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <label for="interpretacion_nivel_academico_barras"><strong>Interpretación: </strong></label>
                                    <textarea max="500" style="width:100%;height:100px;" id="interpretacion_77_2" name="interpretacion_nivel_academico_barras" class="form-control"></textarea max="500">
                                    <div class="form-row" id="botones" style="padding:25px 0;">
                                        <input type="hidden" id="imagen_77_2"/>
                                        <button class="button-style-custom guardar-interpretacion ml-3" id="b772" idCat="77" tipoGra="2">Guardar</button>
                                    </div>
                                </td>
                            </tr>
                    </table>
                </section>

            <!-- Grafica  area de trabajo -->
                <h3>Área de trabajo</h3>
                <section>
                    <table>   
                        <tr>
                            <td colspan="2" class="text-center"><h1>Resultados por área de trabajo</h1></td>
                        </tr>
                        <tr>
                            <td>
                            <p><b>Tabla 6.</b> Participantes por área.</p>
                                <table style="width:100% !important;" class="table table-bordered tableGr">
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
                                <figure class="highcharts-figure-area" >
                                    <div id="container-area"></div>
                                        <p class="highcharts-description text-center">
                                            <b>Nota:</b> Los porcentajes mostrados están basados en la información facilitada por cada uno de los participantes, no son datos confirmados, por lo que, se considera puede existir un pequeño sesgo en la información mostrada.
                                        </p>
                                    </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="resultado-area"  class="text-center">
                                <label>Factor de riesgo:</label>
                                <table class="table table-bordered">
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
                                <label for="interpretacion_area"><strong>Interpretación: </strong> </label>
                                <textarea max="500" style="width:100%;height:100px;" name="interpretacion_area" id="interpretacion_80_1" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_80_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b801" idCat="80" tipoGra="1">Guardar</button>
                                </div>
                            </td>
                        </tr>
                        
                    </table>


                </section>

                <h3>Analisis de riesgo área de trabajo</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center">A continuación, en términos de análisis de riesgo, se presentará el resultado de cada uno de los bloques:</p></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                            <p><b>Tabla 6.1</b> Participantes por Área.</p>
                                <table style="width:100% !important;" class="table table-bordered text-center tableGr">
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
                                            <table style="width:100%">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <figure class="highcharts-figure-area-barra" >
                                        <div id="container-area-barra"></div>
                                        <p class="highcharts-description text-center">
                                            <b>Nota:</b> el gráfico representa el porcentaje total de participantes conforme a su área.
                                        </p>
                                    </figure>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <label for="interpretacion_area_barras"><strong>Interpretación: </strong></label>
                                    <textarea max="500" style="width:100%;height:100px;" id="interpretacion_80_2" name="interpretacion_area_barras" class="form-control"></textarea max="500">
                                    <div class="form-row" id="botones" style="padding:25px 0;">
                                        <input type="hidden" id="imagen_80_2"/>
                                        <button class="button-style-custom guardar-interpretacion ml-3" id="b802" idCat="80" tipoGra="2">Guardar</button>
                                    </div>
                                </td>
                            </tr>
                    </table>
                </section>

                <!-- T A B L A  D E  R E S U L T A D O S -->
                <h3>Nivel de riesgo por participantes</h3>
                <section>
                    <p class="float-right"><a class="button-style-custom ml-3" onclick="form.steps('next')" >Siguiente</a></p>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Nivel de riesgo por participantes</h1></td>
                        </tr>
                        <tr>
                            <td colspan="2" >
                                <p><b>Tabla 7</b> Nivel de riesgo por cada uno de los participantes.</p>
                                <center>
                                <table class="table table-striped tableGr" style="width:100%" id="participantesDi">
                                   
                                    <tbody style="width:100%">
                                            <tr class="thead-dark">
                                                <th>Participante</th>
                                                <th>Puntuación</th>
                                                <th>Nivel de riesgo</th>
                                            </tr>
                                        @foreach($generalResultados as $g)
                                            
                                            <tr>
                                                <td>{{$g[3]}}</td>
                                                <td>{{$g[2]}}</td>
                                                <td style="background:{{$g[0]}}">{{$g[1]}}</td>
                                            </tr>
                                        @endforeach
                                       
                                    </tbody>

                                </table>
                                </center>
                                <br/><br/>
                            </td>
                        </tr>
                    </table>
                </section>
 <!-- Grafica General -->
                <h3>Grafica general</h3>
                <section>
                    <table>
                        <tr>
                            <td colspan="2" class="text-center"><h1>Nivel de riesgo general</h1></td>
                        </tr>
                        <tr>
                            <td>
                                <p><b>Tabla 8</b> Criterios para identificar que decisión se debe tomar.</p>
                                <table style="width:100%;" class="table table-bordered tableGr">
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
                                <p class="small">*Muestra total de {{$total_participantes}} participantes.</p>
                            </td>
                            <td>
                                <figure class="highcharts-figure-general">
                                            <div id="container-general"></div>
                                            <p class="highcharts-description">
                                            <b>Nota:</b> El gráfico representa el porcentaje de la muestra poblacional  en relación al nivel de riesgo que se puede presentar conforme dicta la NOM-035-STPS-2018
                                            </p>
                                </figure>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="resultado-general"  class="text-center">
                                <p> Conforme a los resultados y en referencia a la tabla 8, que se observa el factor de riesgo</p>
                                <table class="table table-bordered">
                                    <tr id="r-general">
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
                                <label for="interpretacion_general"><strong>Interpretación: </strong> </label>
                                <textarea max="500" style="width:100%;height:100px;" name="interpretacion_general" id="interpretacion_81_1" class="form-control"></textarea max="500">
                                <div class="form-row" id="botones" style="padding:25px 0;">
                                    <input type="hidden" id="imagen_81_1"/>
                                    <button class="button-style-custom guardar-interpretacion ml-3" id="b811" idCat="81" tipoGra="1">Guardar</button>
                                </div>
                            </td>
                        <tr>
                    </table>
     
                </section>
   
         <!-- C A T E G O R I A   Y   D O M I N I O  -->

        @foreach($datos_categoria_dominio as $id => $cd)
            @php 
            $tituloTabla = ($cd[1] == 'categoría')? 'Resultados de la '.$cd[1].' "'.$cd[0].'""' : 'Resultados del '.$cd[1].' "'.$cd[0].'"';  
            $tituloDescripcion = ($cd[1] == 'categoría')? ' de la '.$cd[1].' "'.$cd[0].'""' : ' del '.$cd[1].' "'.$cd[0].'"';   
            @endphp
            <h3>{{$cd[0]}}</h3>
            <section>
                <table>
                    <tr>
                        <td colspan="2" class="text-center"><h1>{{$tituloTabla}}</h1></td>
                    </tr>
                    <tr>
                        <td>
                            <p><b>Tabla {{$contTabla}}.</b> identificación del nivel de riesgo en {{$cd[0]}}.</p>
                            <table style="width:100%;" class="table table-bordered tableGr">
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
                            <p class="small">*Muestra total de {{$total_participantes}} participantes.</p>
                        </td>
                        <td>
                            <figure class="highcharts-figure-cat-dom{{$id}}">
                                <div id="container-cat-dom{{$id}}"></div>
                                <p class="highcharts-description">
                                <b>Nota:</b>Se representa nuestra muestra de {{$total_participantes}} participantes, evaluando el nivel de riesgo {{$tituloDescripcion}}.
                                </p>
                            </figure>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" id="resultado-cat-dom{{$id}}" class="text-center">
                        <p>Conforme a los resultados y en referencia a la tabla {{$contTabla}}, que se observa el factor de riesgo:</p>
                            <table class="table table-bordered">
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
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label for="interpretacion_cat_dom_{{$id}}"><strong>Interpretación: </strong></label>
                            <textarea max="500" style="width:100%;height:100px;" id="interpretacion_{{$id}}_1" name="interpretacion_cat_dom_{{$id}}" class="form-control crearImg" id="interpretacion_{{$id}}_1"></textarea max="500">
                            <div class="form-row" id="botones" style="padding:25px 0;">
                                <button class="button-style-custom guardar-interpretacion ml-3" id="b{{$id}}1" idCat="{{$id}}" tipoGra="1">Guardar</button>
                                <input type="hidden" id="imagen_{{$id}}_1"/>
                            </div>
                        </td>
                    </tr>
                </table>
            @php $contTabla++;  $tImplementaciones++; @endphp
            </section>
        @endforeach
        <!--   -->
        </div><br/><br>
        </form>

    </div>
      
</div>
@include('includes.footer')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/sunburst.js"></script>

<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>

<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>  
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.2.1/js.cookie.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-steps/1.1.0/jquery.steps.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
/*
$('#participantesDi').DataTable({
        scrollY:'60vh',
        scrollX:'0',
        paging:false,
        ordering:false,
        searching:false,
        info: false
    });
*/

var form = $("#step-vertical").show();

form.steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    stepsOrientation: "vertical",    
    enablePagination: false,
    
    onInit:function(event, currentIndex){
        var index = readCookie("jQu3ry_5teps_St%40te_cuestionario");
        if(typeof(index) != 'undefined' && index != null){
            tamanioWizard(index);
        }else{
            tamanioWizard(0);
        }
    },
     onStepChanged: function (event, currentIndex, priorIndex)
     {  
        tamanioWizard(currentIndex);
     }
});

var crearGra = 0;
var totalImplementaciones = {{$tImplementaciones}};
var implementacionesCompletas = 0;
@if(count($interpretaciones))
crearGra = 0;
    @foreach($interpretaciones as $id => $interpretacion)
        $("#interpretacion_{{$interpretacion->idcatalogo_norma}}_{{$interpretacion->tipo_grafica}}").val('{{$interpretacion->interpretacion}}').attr("disabled","disabled");
        $("#b{{$interpretacion->idcatalogo_norma}}{{$interpretacion->tipo_grafica}}").remove();
        form.steps("next");
        implementacionesCompletas++;
    @endforeach
    botonReporte();
@endif

if(implementacionesCompletas >= 10){
    form.steps("next");
}

function botonReporte(){
    //console.log("completas: " + implementacionesCompletas + " ----- total: " + totalImplementaciones);
    if(implementacionesCompletas == totalImplementaciones){
        $('#botonReporte').html('<button class="button-style ml-3 tooltip_" id="btn-reporte" data-toggle="tooltip" title="Generar reporte"><img src="/img/icono-descargar.png" class="button-style-icon">Descargar PDF</button>');   
        swal("Reporte listo", "El reporte esta listo para ser generado.");  
        $("html, body").animate({
            scrollTop: 0
            }, 1000,function(){
                $("#btn-reporte").fadeIn(1000).fadeOut(1000).fadeIn(500).fadeOut(500).fadeIn(200).fadeOut(200).fadeIn(100);
            });
        }
}

pie([['rgb(149,102,223)','Hombres',{{count($hombres)}}],['rgb(112,184,150)','Mujeres',{{count($mujeres)}}],['','Otros',0]],'sexo','<b>Gráfico 1.</b> Muestra por género participante en el cuestionario.',0,78);
pie(@json($mujeresPie),'mujeres','<b>Gráfico 1.2 </b>Factor de riesgo de los participantes del género femenino.',0,18);
pie(@json($hombresPie),'hombres','<b>Gráfico 1.3 </b> Factor de riesgo de los participantes del género masculino.',0,19);
pie(@json($general['pie']),'general','<b>Gráfico 6 </b> Factor de riesgo de los participantes de general.',0,81);
pie(@json($edad['pieEdad']),'edad','<b>Gráfico 2 </b> Distribución de los participantes conforme a su edad.',0,79);
barras("Edades",@json($edadBarras),'edad-barra','<b>Gráfico 2.1. </b> Factor de riesgo de los participantes conforme a su bloque de edad.','Total de porcentajes',79);
pie(@json($profesion['pieProfesion']),'profesion','<b>Gráfico 3. </b> Representación porcentual de los participantes conforme a su nivel académico.',0,77);
barras("Profesiones",@json($profesionBarras),'profesion-barra','<b>Gráfico 3.1. </b>Porcentaje nivel de riesgo de participantes por nivel academico.','Total de porcentajes',77);
pie(@json($area['pieArea']),'area','<b>Gráfico 4. </b> Representación porcentual de los participantes conforme al área de trabajo.',0,80);
barras("Área",@json($areaBarras),'area-barra','<b>Gráfico 4.1. </b>Porcentaje nivel de riesgo de participantes por área.','Total de porcentajes',80);

@foreach($datos_categoria_dominio as $id => $cd)
    var titulo = "";
    
    @php
        if($cd[1] == "dominio"){
            $titulo = $cd[1].": ".$cd[0];
        }else{
            $titulo = $cd[1].": ".$cd[0];
        }
    @endphp
    pie(@json($total_clasificacion_dominio[$id]),'cat-dom{{$id}}','<b>Gráfico {{$contGrafica}} </b>{{$titulo}}',1,{{$id}});

    @php $contGrafica++; @endphp
@endforeach


function pie(d,contenedor,titulo,contenido_datos,idcat){
    var color = [];
    var datos = [];
    var mayor = [];
    var may = 0;
    if(contenido_datos){
        d.forEach(function(d){
                color.push(d[2]);
                datos.push({'name':d[3],'y':d[4],'color':d[2]});
                if(may < d[4]){
                    mayor = [d];
                    may = d[4];
                }else if(may == d[4] && may != 0){
                    mayor.push(d);
                }
            });
    }else{
            d.forEach(function(d){
                color.push(d[0]);
                datos.push({'name':d[1],'y':d[2],'color':d[0]});
                if(may < d[2]){
                    mayor = [d];
                    may = d[2];
                }else if(may == d[2] && may != 0){
                    mayor.push(d);
                }
            });

    }
    Highcharts.setOptions({
        colors: color
    });

    datosGraf = {
        chart: {
            plotBackgroundColor:null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: '<h2>'+titulo+'</h2>'
        },
        tooltip: {
            pointFormat: 'Porcentaje :<b>{point.percentage:.1f}%<br>Total :<b>{point.y}</b>'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        credits:{
            enabled:false
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} % ({point.y})',
                    connectorColor: 'silver'
                }
            }
        },
        series: [{
            name: 'Share',
            data: datos
        }]
    };

    Highcharts.chart('container-'+contenedor, datosGraf);

    $("#imagen_"+idcat+"_1").val(JSON.stringify(datosGraf));

}

function barras(nombre,d,contenedor,titulo,ya,idcat){
    var color = [];
    var datos = [];

    d.forEach(function(d){
        color.push(d[0]);
        datos.push({'name':d[1],'y':d[2],'drilldown':d[1],'color':d[0]});
    });

    Highcharts.setOptions({
        colors: color
    });

    datosGraf = {
        chart: {
            plotBackgroundColor:null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'column'
        },
        title: {
            text: '<h2>'+titulo+'</h2>'
        },
        accessibility: {
            announceNewData: {
                enabled: true
            }
        },
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: ya
            }

        },
        legend: {
            enabled: false
        },
        credits:{
                enabled:false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}%'
                }
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
        },

        series: [
            {
                name: "Browsers",
                colorByPoint: true,
                data: datos,                
        }]
    };
    Highcharts.chart('container-'+contenedor,datosGraf);
    $("#imagen_"+idcat+"_2").val(JSON.stringify(datosGraf));

}


function guardarImplementacion(boton,optionsStr,idCat,tipoGra,interpretacion,implementacion){
    // optionsStr = cadena para generar la grafica
    // idCat = id del catalogo que indica de que es la grafica
    boton.html("Guardando...");
    boton.attr("disabled",true);
    var exportUrl = 'https://export.highcharts.com/';
    dataString = encodeURI('b64=true&&type=png&width=500&options=' + optionsStr);


        if (window.XDomainRequest) {
            var xdr = new XDomainRequest();
            xdr.open("post", exportUrl+ '?' + dataString);
            xdr.onload = function () {
                console.log(xdr.responseText);
                $('#container').html('<img src="' + exportUrl + xdr.responseText + '"/>');
                guardaImg(exportUrl,xdr.responseText);
            };
            xdr.send();
        } else {
            $.ajax({
                type: 'POST',
                data: dataString,
                url: exportUrl,
                async: false,
                success: function (data) {
                    //console.log('archivo con ruta: ', data);
                    guardaDatos(boton,exportUrl,data,idCat,tipoGra,interpretacion,implementacion);
                    form.steps("next");

                },
                error: function (err) {
                   // debugger;                  
                   swal("", "Ocurrió un error!", "error");
                   boton.attr("prop","Guardar").attr("disabled",false);
                    console.log('error', err.statusText);
                }
            });
        }
}

function guardaDatos(boton,ruta,imagen,idCat,tipoGra,interpretacion,implementacion){
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
                type: 'POST',
                data: {'imagen':imagen,'ruta':ruta,'idCat':idCat,'tipoGra':tipoGra,'interpretacion':interpretacion.replace("\n"," ").replace("'",'"'),'implementacion':implementacion},
                url: "{{route('norma.implementacion.reporte.grafica.guardar')}}",
                async: false,
                success: function (r) {
                    //console.log(r);
                    if(r.ok == 1){ 
                        swal("La interpretación almacenó correctamente.", {
                        icon: "success",
                        });
                        boton.remove();
                        $("#interpretacion_"+idCat+"_"+tipoGra).attr("disabled","disabled");
                        implementacionesCompletas++;
                        botonReporte();
                    }else{                        
                        swal("", "La interpretación no pudo almacenarce, intente nuevamente", "error");
                        boton.attr("prop","Guardar").attr("disabled",false);
                    }
                },
                error: function (err) {
                    console.log('error', err.statusText)
                }
        });
}

$(".guardar-interpretacion").on("click",function(e){
    
    var idCat = $(this).attr("idCat");
    var tipoGra = $(this).attr("tipoGra");
    $("#b"+idCat+tipoGra).html("Guardando...").attr("disabled",true);
    //console.log("#b"+idCat+tipoGra);
    var interpretacion = $("#interpretacion_"+idCat+"_"+tipoGra).val(); 
    setTimeout(function(){
        let interpretacion_limpiar=interpretacion.trim();
        interpretacion = interpretacion_limpiar.replace(/(\r\n|\n|\r)/gm, "");
        if(interpretacion != ""){     
            guardarImplementacion($("#b"+idCat+tipoGra),$("#imagen_"+idCat+"_"+tipoGra).val(),idCat,tipoGra,interpretacion.trim(),$("#Rimplementacion").val());
        
        }else{           
            swal("La interpretación es requerida.", {
            title: "Campo invalido",
            icon: "warning",
            });
            $("#b"+idCat+tipoGra).html("Guardar").attr("disabled",false);
        }
     }, 500);
    return false;
});

function tamanioWizard(currentIndex){
    if (currentIndex == 6 ){    $(".wizard > .content").css("height","110em");
    }else if ( currentIndex == 4 ){    $(".wizard > .content").css("height","85em");
    }else if(currentIndex == 5 || currentIndex == 8){  $(".wizard > .content").css("height","90em");
    }else if ( currentIndex == 9 ){    $(".wizard > .content").css("height","50em");
    }else{
        $(".wizard > .content").css("height","60em");
    }
}

function readCookie(name) {
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + name.replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
}

$(function(){
    $("#step-vertical").fadeIn("slow");
});
</script>
