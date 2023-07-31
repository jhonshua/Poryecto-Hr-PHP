@php
foreach($perfilDescriptivo as $perfil)
{
$reportan = json_decode($perfil->reportan);
$c_interna = json_decode($perfil->c_interna);
$relaciones = json_decode($perfil->relaciones);
$habilidades = json_decode($perfil->habilidades);
$competencias = json_decode($perfil->competencias);
$experiencia =json_decode($perfil->experiencia);
$tiempo_experiencia = json_decode($perfil->tiempo_experiencia);
$conocimientos = json_decode($perfil->conocimientos);
$dominio_c = json_decode($perfil->dominio_c);
$cursos = json_decode($perfil->cursos);
$ant_curso = json_decode($perfil->ant_curso);
$actividades = json_decode($perfil->actividades);
$act_autoridades = json_decode($perfil->act_autoridades);
$otros = json_decode($perfil->otros);
$i= $e = $co = $cu = $p = $ci =1;
}
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0.5cm 1cm;
            font-family: Arial;
        }

        @font-face {
            font-family: "Maven Pro Light 300 Regular";
            src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot") }}');
            src: url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.eot?#iefix") }}') format("embedded-opentype"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff2") }}') format("woff2"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.woff") }}') format("woff"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.ttf") }}') format("truetype"),
            url('{{ asset("public/fonts/a7a65bad96b6c72d21859e81ad765c02.svg#Maven Pro Light 300 Regular") }}') format("truetype")
        }

        .title {
            font-size: 30px;
            display: block;
            float: right;
        }

        .cont_subtitle {
            width: 390px;
            color: #fff;
            margin: 9px;
            padding-top: 3px;
            padding-bottom: 0px;
            padding-left: 10px;
            padding-right: 10px;
            font-size: 20px;
            font-weight: 500;
            text-align: center;
            border-radius: 6px;
            border: 1px #fbba00 solid;
            background-color: #fbba00;
            box-shadow: 0px 4px 0px #C4C4C4;

        }

        div {
            justify-content: center;
            display: flex;

        }

        .subtitle {
            font-size: 20px;
            font-weight: 500;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #C4C4C4 ;
          
            width: 2%;
            align-items: center;
        }

        .input {

            border-radius: 3px;
            border: 1px solid transparent;
            border-color: #C4C4C4;
            height: min-content;
            width: 200px;
            margin: 5px;
            padding: 6px;
        }

        .list_data {
            width: 250px;
            color: #000;
            padding: 20px;
            margin-left: 1em;
            margin-right: 1em;
            font-size: 14px;
            text-align: left;
            border-radius: 6px;
            border: 1px #c4c4c4 solid;
            background: #DADADA;
            box-shadow: 0px 5px 0px 4px #fbba00;
        }

        .cont_data {
            width: 80%;
            color: #000;
            padding: 20px;
            margin: 30px;
            font-size: 14px;
            text-align: left;
            border-radius: 6px;
            border: 1px #c4c4c4 solid;
            background: #DADADA;
            box-shadow: 5px 0 1px #fbba00, -5px 0 1px #fbba00;
        }

        .encabezado-tabla {
            background-color: #DADADA;
            font-size: 18px;
            font-weight: bolder;
            color: #fbba00;
            padding: 0% !important;
            margin: 0% !important;
            text-align: center;
            border: none !important;
            border-collapse: collapse;
            border-spacing: 0px !important;
            border-color: #DADADA;

        }

        table {
            width: 100% !important;
        }

        .cuerpo-tabla {
            background-color: #DADADA;
            font-size: 15px;
            font-weight: normal;
            color: #000;
            padding: 20px !important;
            margin: 0% !important;
            text-align: left;
            border: none !important;
            border-collapse: collapse;
            border-spacing: 0px !important;
            border-color: #C4C4C4;
        }
    </style>
</head>

<body>
    @foreach($perfilDescriptivo as $perfil)
    <label class="title"> Perfil descriptivo del puesto </label>
    <br>
    <br>
    <div> <label class="cont_subtitle"> Detalles del perfil </label></div><br>
    <label class="subtitle">Datos generales del puesto</label>

    <table>

        <tr>
            <td> Nombre del puesto: </td>
            <td class="input">{{$perfil->puesto}}</td>
            <td> Objetivo del puesto: </td>
            <td class="input">{{$perfil->objetivo_puesto}}</td>
        </tr>
        <tr>
            <td> Área a la que pertenece: </td>
            <td class="input">{{$perfil->nombre}}</td>
            <td> Horario: </td>
            <td class="input">{{$perfil->alias}}</td>
        </tr>
        <tr>
            <td> Tipo de contrato: </td>
            @if($perfil->puesto = 1)
            <td class="input">CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO</td>
            @elseif($perfil->puesto = 2)
            <td class="input">CONTRATO DE TRABAJO POR OBRA DETERMINADA</td>
            @elseif($perfil->puesto = 3)
            <td class="input">CONTRATO DE TRABAJO POR TIEMPO DETERMINADO</td>
            @elseif($perfil->puesto = 4)
            <td class="input">CONTRATO DE TRABAJO POR TEMPORADA</td>
            @elseif($perfil->puesto = 5)
            <td class="input">CONTRATO DE TRABAJO SUJETO A PRUEBA</td>
            @elseif($perfil->puesto = 6)
            <td class="input">Contrato de trabajo con capacitación inicial</td>
            @elseif($perfil->puesto = 7)
            <td class="input">Modalidad de contratación por pago de hora laborada</td>
            @elseif($perfil->puesto = 8)
            <td class="input">Modalidad de trabajo por comisión laboral</td>
            @elseif($perfil->puesto = 9)
            <td class="input">Modalidades de contratación donde no existe relación de trabajo</td>
            @elseif($perfil->puesto = 10)
            <td class="input">JUBILACIÓN, PENSIÓN, RETIRO</td>
            @elseif($perfil->puesto = 99)
            <td class="input">OTRO CONTRATO</td>
            @endif

            <td> Rango de salario: </td>
            <td class="input">{{$perfil->rango_salario}}</td>
        </tr>
        <tr>
            <td> Condiciones adicionales (prestaciones LFT, superiores, etc.): </td>
            <td class="input">{{$perfil->condiciones}}</td>
        </tr>
    </table>
    <br>
    <div><label class="cont_subtitle"> Posición en el organigrama </label></div>
    <br>
    <table>

        <tr>
            <td> Nivel jerarquico: </td>
            <td class="input">
                @if($perfil->jerarquia != null)
                {{$perfil->jerarquia}}
                @else
                No asignado
                @endif
            </td>
            <td> Puesto al que le reporta: </td>

            <td class="input">
                @foreach( $puestos as $puesto)
                @if($perfil->id_dependencia == $puesto->id )
                {{$puesto->puesto}}
                @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <td>¿Tiene personal a su cargo?</td>
            <td class="input">
                @if($perfil->personal == 1)
                Si
                @else
                No
                @endif
            </td>
        </tr>
    </table>
    <table>
        <tr>

            <td style="font-weight: bold;">Puestos que le reportan</td>
            <td style="font-weight: bold;">Comunicación interna <br>(puestos principales con los que se relaciona)</td>
        </tr>
        <tr>
            <td class="list_data">
                @if($reportan != null)
                @foreach($reportan as $interna)
                @foreach($puestos as $puesto)
                @if($interna == $puesto->id)
                {{$p}} - {{$puesto->puesto}}
                @php
                $p++;
                @endphp
                <br>
                @endif
                @endforeach
                @endforeach
                @else
                No asignado
                @endif
            </td>

            <td class="list_data">
                @foreach($c_interna as $interna)

                @foreach($puestos as $puesto)
                @if($interna == $puesto->id)
                {{$ci}} - {{$puesto->puesto}}
                @php
                $ci++;
                @endphp
                <br>
                @endif
                @endforeach


                @endforeach
            </td>
        </tr>
    </table>

    <div style="page-break-after:always;"></div>
    <div><label class="cont_subtitle"> Relaciones y manejos particulares </label></div>
    <p>Instrucciones:
        <br>
        Identificar conforme a las responsabilidades del puesto:
        <br>
        ¿Cuáles son las relaciones que se encuentran adscritas al puesto?
        <br>
        ¿Cuál es el material y equipo que se requiere en las funciones del puesto?
    </p>
    <br>
    <table>
        <tr class="encabezado-tabla">
            <td width="500px">Conceptos</td>
            <td width="50px">Si</td>
            <td width="50px">No</td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Uso de materiales y equipos </td>
            <td style="text-align: center;">
                @if($relaciones->materiales == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->materiales == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Manejo de recursos económicos </td>
            <td style="text-align: center;">
                @if($relaciones->rec_economicos == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rec_economicos == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Manejo de documentos importantes (expedientres, contratos, poderes, etc.) </td>
            <td style="text-align: center;">
                @if($relaciones->doc_imp == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->doc_imp == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Manejo de información confidencial (listado de clientes, estado de cuenta, altas, bajas de personal, etc.) </td>
            <td style="text-align: center;">
                @if($relaciones->inf_confidencial == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->inf_confidencial == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Uso de correo electrónico empresarial </td>
            <td style="text-align: center;">
                @if($relaciones->mail_empresarial == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->mail_empresarial == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con los clientes </td>
            <td style="text-align: center;">
                @if($relaciones->rel_clientes == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_clientes == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con los directivos de la compañía </td>
            <td style="text-align: center;">
                @if($relaciones->rel_directivos == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_directivos == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con las gerencias </td>
            <td style="text-align: center;">
                @if($relaciones->rel_gerencias == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_gerencias == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con las jefaturas, supervisiones o subgerencias </td>
            <td style="text-align: center;">
                @if($relaciones->rel_jefaturas == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_jefaturas == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con los auxiliares </td>
            <td style="text-align: center;">
                @if($relaciones->rel_auxiliares == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_auxiliares == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con los becarios </td>
            <td style="text-align: center;">
                @if($relaciones->rel_becarios == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_becarios == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relación directa con servicios generales </td>
            <td style="text-align: center;">
                @if($relaciones->rel_serviciosG == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($relaciones->rel_serviciosG == 0)
                X
                @endif
            </td>
        </tr>
    </table>
    <div style="page-break-after:always;"></div>
    <label class="cont_subtitle">Habilidades especificas</label>
    <p>
        Indicar conforme a las responsabilidades y actividades del puesto, las habilidades específicas
        requeridas para su desarrollo:
        <br>
        ¿Qué habilidades requiere el puesto?
        <br>
        ¿Requiere el puesto alguna habilidad verbal, física, técnica, etc.?
    </p>
    <br>
    <table>
        <tr class="encabezado-tabla">
            <td width="500px">Conceptos</td>
            <td width="50px">Si</td>
            <td width="50px">No</td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Escrita:
                <p>Redactar informes, memorándums, comunicarse vía correo electrónico, convocatorias, reportes, etc.</p>
            </td>
            <td style="text-align: center;">
                @if($habilidades->escritura == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($habilidades->escritura == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Verbal:
                <p>Expresar pensamientos, ideas, asesorías, comunicarse vía teléfonica, grabación de voz, desarrollar y
                    brindar conferencias, capacitaciones, hablar con clientes o usuarios, etc.</p>

            </td>
            <td style="text-align: center;">
                @if($habilidades->verbal == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($habilidades->verbal == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Física:
                <p>Hacer uso de la fuerza para el traslado de equipos, materiales, objetos de peso considerable, manejo
                    de automóvil o equipos especializados, etc.</p>
            </td>
            <td style="text-align: center;">
                @if($habilidades->fisica == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($habilidades->fisica == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Visual:
                <p>Identificar letras, números, hacer uso de computadoras o lecturas de textos.</p>
            </td>
            <td style="text-align: center;">
                @if($habilidades->visual == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($habilidades->visual == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Númerica:
                <p>Hacer uso de las operaciones númericas esenciales, desarrollar estrategias útiles y financieras,
                    medir, estimar.</p>
            </td>
            <td style="text-align: center;">
                @if($habilidades->numerica == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($habilidades->numerica == 0)
                X
                @endif
            </td>
        </tr>
    </table>
    <div style="page-break-after:always;"></div>
    <div><label class="cont_subtitle">Competencias</label></div>
    <p>
        Instrucciones:
        <br>
        Indicar las competencias específicas requeridas para el puesto.
    </p>
    <table>
        <tr class="encabezado-tabla">
            <td width="500px">Competencia</td>
            <td width="60px">Alto</td>
            <td width="60px">Medio</td>
            <td width="110px">A desarrollar</td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Capacidad de sintesis</td>
            <td style="text-align: center;">
                @if($competencias->sintesis == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->sintesis == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->sintesis == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Lealtad y sentido de pertenencia </td>
            <td style="text-align: center;">
                @if($competencias->lealtad == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->lealtad == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->lealtad == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Confiabilidad-franqueza </td>
            <td style="text-align: center;">
                @if($competencias->confiabilidad == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->confiabilidad == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->confiabilidad == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Ética </td>
            <td style="text-align: center;">
                @if($competencias->etica == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->etica == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->etica == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Disponibilidad</td>
            <td style="text-align: center;">
                @if($competencias->disponibilidad == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->disponibilidad == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->disponibilidad == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Temple </td>
            <td style="text-align: center;">
                @if($competencias->temple == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->temple == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->temple == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px" class="cuerpo-tabl">Facilidad de palabra</td>
            <td style="text-align: center;">
                @if($competencias->fac_palabra == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->fac_palabra == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->fac_palabra == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Trabajo en equipo </td>
            <td style="text-align: center;">
                @if($competencias->tra_equipo == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->tra_equipo == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->tra_equipo == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Relaciones publicas-diplomacia</td>
            <td style="text-align: center;">
                @if($competencias->diplomacia == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->diplomacia == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->diplomacia == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Negociación</td>
            <td style="text-align: center;">
                @if($competencias->negociacion == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->negociacion == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->negociacion == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Pensamiento analítico</td>
            <td style="text-align: center;">
                @if($competencias->analitico == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->analitico == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($competencias->analitico == 2)
                X
                @endif
            </td>
        </tr>
    </table>
    <br>
    <div><label class="cont_subtitle">Requisitos técnicos</label></div>

    <p>
        Llenar conforme a lo requerido para el desarrollo del puesto:
        <br>
        ¿Cuál es la experiencia necesaria para el puesto?
        <br>
        ¿Cuál es el nivel educativo del puesto?
        <br>
        ¿Requiere de cursos?
    </p>
    <br>
    <table>
        <tr>
            <td>Nivel educativo:</td>
            <td class="input" width="350px">{{$perfil->nivel_educativo}}</td>
            <td>¿Terminado o trunco?</td>
            <td class="input" width="350px">
                @if($perfil->terminado ==0 )
                Terminado
                @else
                Trunco
                @endif
            </td>
        </tr>
        <tr>
            <td>Título o especialización:</td>
            <td class="input" width="350px">{{$perfil->titulo}}</td>
        </tr>
    </table>
    <label>Experiencia en:</label>
    <table style="width: 55% !important;" class="cont_data">
        <tr>
            <td>
                @foreach($experiencia as $exp)
                {{$e}} - {{$exp}}
                <br>
                @php
                $e++;
                @endphp
                @endforeach
            </td>
            <td>@foreach($tiempo_experiencia as $dom_exp)
                Tiempo de experiencia:
                {{$dom_exp}}
                <br>
                @endforeach
            </td>
        </tr>


    </table>
    <label>Conocimientos:</label>
    <table style="width: 55% !important;" class="cont_data">


        <tr>
            <td>
                @foreach($conocimientos as $exp)
                {{$co}} - {{$exp}}
                <br>
                @php
                $co++;
                @endphp
                @endforeach
            </td>
            <td>
                @foreach($dominio_c as $dom_exp)
                Dominio:
                {{$dom_exp}}
                <br>
                @endforeach
            </td>
        </tr>


    </table>
    <label>Cursos:</label>
    <table style="width: 55% !important;" class="cont_data">
        <tr>
            <td>
                @foreach($cursos as $exp)
                {{$cu}} - {{$exp}}
                <br>
                @php
                $cu++;
                @endphp
                @endforeach
            </td>
            <td>
                @foreach($ant_curso as $dom_exp)
                Antigüedad:
                {{$dom_exp}}
                <br>
                @endforeach
            </td>
        </tr>


    </table>
    <div style="page-break-after:always;"></div>
    <div><label class="cont_subtitle">Actividades</label></div>
    <p>Instrucciones:
        <br>
        Determinar las principales funciones que realiza la posición.
    </p>
    <table>
        <tr class="encabezado-tabla">
            <td>Actividad</td>
            <td>Frecuentemente
                <p>(a diario y cada tercer día)</p>
            </td>
            <td>Algunas veces
                <p>(1 vez al año o eventualmente)</p>
            </td>
            <td>
                Ocasionalmente
                <p>(1 vez a la semana o 1 vez al mes) </p>
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Elaboración de kits de contrato laboral, convenio de
                confidencialidad, carta de agremio, documentos cliente</td>
            <td style="text-align: center;">
                @if($actividades->kit_contrato == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->kit_contrato == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->kit_contrato == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Elaboración de kits de desvinculaciones (carta renuncia,
                cálculo de finiquito, integración de timbre fiscal y constancia)</td>
            <td style="text-align: center;">
                @if($actividades->kit_desvincula == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->kit_desvincula == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->kit_desvincula == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Elaboración de contratos y desvinculaciones.</td>
            <td style="text-align: center;">
                @if($actividades->cont_desvincula == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->cont_desvincula == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->cont_desvincula == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Análisis de base de datos.</td>
            <td style="text-align: center;">
                @if($actividades->anal_db == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->anal_db == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->anal_db == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Firma de finiquitos, validación de documentos y liberación
                de pago</td>
            <td style="text-align: center;">
                @if($actividades->f_finiquito == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->f_finiquito == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->f_finiquito == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Manejo del sistema para anexar documentos de expedientes
                y para evidencia (altas, bajas)</td>
            <td style="text-align: center;">
                @if($actividades->sist_doc == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->sist_doc == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->sist_doc == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Escaneo o digitalización de documentos.</td>
            <td style="text-align: center;">
                @if($actividades->dig_doc == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->dig_doc == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->dig_doc == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Resguardo y manejo de expedientes.</td>
            <td style="text-align: center;">
                @if($actividades->m_expedientes == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->m_expedientes == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->m_expedientes == 2)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td>Firma recibos de nóminas.</td>
            <td style="text-align: center;">
                @if($actividades->f_nomina == 0)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->f_nomina == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($actividades->f_nomina == 2)
                X
                @endif
            </td>
        </tr>
    </table>
    <div style="page-break-after:always;"></div>
    <div><label class="cont_subtitle">Autoridades</label></div>

    <p>Instrucciones:
        <br>
        Determinar las principales funciones que realiza la posición.
    </p>
    <br>
    <label style="font-weight: bold; color: #fbba00; font-size:18px;">Actividad</label>
    <hr>
    <table>
        <tr>
            @foreach($act_autoridades as $act)
            <a style="font-weight: bold; color: #fbba00;">{{$i}}) {{$act}}</a>
            <br>
            @php
            $i++
            @endphp
            @endforeach
        </tr>
    </table>
    <br>
    <br>
    <div><label class="cont_subtitle">Otros</label></div>
    <p>
        Indicar otros aspectos requeridos en la posición que no están contemplados en las secciones anteriores.
        <br>
        ¿Disponibilidad para viajar?
        <br>
        ¿Disponibillidad para cambiar de residencia?
        <br>
        ¿Saber manejar?
    </p>
    <br>
    <table>
        <tr class="encabezado-tabla">
            <td width="500px">Concepto</td>
            <td width="50px">Si</td>
            <td width="50px">No</td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Disponibilidad para viajar</td>
            <td style="text-align: center;">
                @if($otros->viajar == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($otros->viajar == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Cambio de residencia</td>
            <td style="text-align: center;">
                @if($otros->camb_resi == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($otros->camb_resi == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Manejar</td>
            <td style="text-align: center;">
                @if($otros->manejar == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($otros->manejar == 0)
                X
                @endif
            </td>
        </tr>
        <tr class="cuerpo-tabla">
            <td width="500px">Contar con licencia</td>
            <td style="text-align: center;">
                @if($otros->lic_conducir == 1)
                X
                @endif
            </td>
            <td style="text-align: center;">
                @if($otros->lic_conducir == 0)
                X
                @endif
            </td>
        </tr>
    </table>
    @endforeach
</body>

</html>