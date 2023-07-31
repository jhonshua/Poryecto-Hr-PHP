@php


foreach($perfilDescriptivo as $perfil)
{
$reportan = json_decode($perfil['reportan']);
$c_interna = json_decode($perfil['c_interna']);
$relaciones = json_decode($perfil['relaciones']);
$habilidades = json_decode($perfil['habilidades']);
$competencias = json_decode($perfil['competencias']);
$experiencia =json_decode($perfil['experiencia']);
$tiempo_experiencia = json_decode($perfil['tiempo_experiencia']);
$conocimientos = json_decode($perfil['conocimientos']);
$dominio_c = json_decode($perfil['dominio_c']);
$cursos = json_decode($perfil['cursos']);
$ant_curso = json_decode($perfil['ant_curso']);
$actividades = json_decode($perfil['actividades']);
$act_autoridades = json_decode($perfil['act_autoridades']);
$otros = json_decode($perfil['otros']);
$i= $e = $co = $cu = $p = $ci =1;
}
@endphp

@foreach($perfilDescriptivo as $perfil)

<table style="border-collapse: collapse;
        border: 3px solid black;
        
        
        ;">
    <tr>
        <td style="font-weight: bolder; font-size:20px; border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black; text-align:center;" colspan="2"> Perfil descriptivo del puesto </td>
        <td style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black; text-align: center;" colspan="2">Elaboración: {{ formatoAFecha(date('Y-m-d'), true) }}</td>
    </tr>
</table>


<table style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;">
    <tr>
        <td colspan="4" style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black; width: 88.5%; text-align: center;background-color: #82A1B1; font-size: 13px;">Detalles del perfil</td>
    </tr>
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">1. Datos generales del puesto</td>
    </tr>
    <br>
    <tr>
        <td colspan="2">Nombre del puesto: </td>
        <td colspan="2" style="  border-bottom: 2px solid black;"> {{$perfil['puesto']}} . </td>
    </tr>
    <tr>
        <td colspan="2">Objetivo del puesto: </td>
        <td colspan="2" style="  border-bottom: 2px solid black;"> {{$perfil['objetivo_puesto']}} . </td>
    </tr>
    <tr>
        <td colspan="2">Área a la que pertenece: </td>
        <td colspan="2" style="  border-bottom: 2px solid black;"> {{$perfil['nombre']}} .
        </td>
    </tr>
    <tr>
        <td colspan="2">Horario:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;"> {{$perfil['alias']}} .</td>

    </tr>
    <tr>
        <td colspan="2">Tipo de contrato:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">
            @if($perfil['puesto'] = 1)
            <a> CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO .</a>
            @elseif($perfil['puesto'] = 2)
            <a> CONTRATO DE TRABAJO POR OBRA DETERMINADA .</a>
            @elseif($perfil['puesto'] = 3)
            <a> CONTRATO DE TRABAJO POR TIEMPO DETERMINADO .</a>
            @elseif($perfil['puesto'] = 4)
            <a> CONTRATO DE TRABAJO POR TEMPORADA .</a>
            @elseif($perfil['puesto'] = 5)
            <a> CONTRATO DE TRABAJO SUJETO A PRUEBA .</a>
            @elseif($perfil['puesto'] = 6)
            <a> Contrato de trabajo con capacitación inicial .</a>
            @elseif($perfil['puesto'] = 7)
            <a> Modalidad de contratación por pago de hora laborada .</a>
            @elseif($perfil['puesto'] = 8)
            <a> Modalidad de trabajo por comisión laboral .</a>
            @elseif($perfil['puesto'] = 9)
            <a> Modalidades de contratación donde no existe relación de trabajo .</a>
            @elseif($perfil['puesto'] = 10)
            <a> JUBILACIÓN, PENSIÓN, RETIRO .</a>
            @elseif($perfil['puesto'] = 99)
            <a> OTRO CONTRATO</a>
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Rango de salario:</td>
        <td>{{$perfil['rango_salario']}} .</td>

    </tr>
    <tr>
        <td colspan="2">Condiciones adicionales (prestaciones LFT, superiores, etc.):</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">{{$perfil['condiciones']}} .
        <td>

    </tr>
</table>

<table style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;       ;">
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">2. Posición en el organigrama</td>

    </tr>
    <br>
    <tr>
        <td colspan="2">Nivel jerarquico:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">
            @if($perfil['jerarquia'] != null)
            {{$perfil['jerarquia']}} .
            @else
            No asignado .
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Puesto al que le reporta: </td>
        <td colspan="2" style="  border-bottom: 2px solid black;">
            @foreach( $puestos as $puesto)
            @if($perfil['id_dependencia'] == $puesto['id'])
            <a>{{$puesto['puesto']}} .</a>
            <br>
            @endif
            @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="2">¿Tiene personal a su cargo?:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">
            @if($perfil['personal'] == 1)
            Si .
            @else
            No .
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Puesto (os) que le reportan:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">

            @if($reportan != null)
            @foreach($reportan as $interna)
            @foreach($puestos as $puesto)
            @if($interna == $puesto['id'])
            <a> {{$p}} - {{$puesto['puesto']}} .<a>
                    <br>
                    @php
                    $p++;
                    @endphp

                    @endif
                    @endforeach
                    @endforeach
                    @else
                    <a>No asignado .</a>
                    @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Comunicación interna (puestos principales con los que se relaciona):</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">

            @foreach($c_interna as $interna)
            @foreach($puestos as $puesto)
            @if($interna == $puesto['id'])
            <a> {{$ci}} - {{$puesto['puesto']}} .<a>
                    <br>
                    @php
                    $ci++;
                    @endphp

                    @endif
                    @endforeach
                    @endforeach


        </td>
    </tr>
</table>

<table style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;      ;">
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">3. Relaciones y manejos particulares</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left; font-size: 9px;border-bottom: 2px solid black; height:50px;" colspan="4">Instrucciones: Identificar conforme a las responsabilidades del puesto y marcar con una "x" en la columna según corresponda.
            <br>¿Cuáles son las relaciones que se encuentran adscritas al puesto?
            <br>¿Cuál es el material y equipo que se requiere en las funciones del puesto?
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; border-left: 2px solid black;font-weight:bold;">Concepto</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Si</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">No</td>
    </tr>
    <tr>
        <td colspan="2">Uso de materiales y equipos </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->materiales == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->materiales == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Manejo de recursos económicos </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rec_economicos == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rec_economicos == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Manejo de documentos importantes (expedientres, contratos, poderes, etc.) </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->doc_imp == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->doc_imp == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Manejo de información confidencial (listado de clientes, estado de cuenta, altas, bajas de personal, etc.) </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->inf_confidencial == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->inf_confidencial == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Uso de correo electrónico empresarial </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->mail_empresarial == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->mail_empresarial == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con los clientes </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_clientes == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_clientes == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con los directivos de la compañía </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_directivos == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_directivos == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con las gerencias </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_gerencias == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_gerencias == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con las jefaturas, supervisiones o subgerencias </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_jefaturas == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_jefaturas == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con los auxiliares </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_auxiliares == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_auxiliares == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con los becarios </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_becarios == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_becarios == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Relación directa con servicios generales </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_serviciosG == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($relaciones->rel_serviciosG == 0)
            X
            @endif
        </td>
    </tr>
</table>

<table style="border-collapse: collapse;border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;    ;">
    <tr>
        <td style=" border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">4. Habilidades especificas</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:64px;" colspan="4">Instrucciones: Indicar conforme a las responsabilidades y actividades del puesto, las habilidades específicas requeridas para su desarrollo.
            <br>¿Qué habilidades requiere el puesto?
            <br>¿Requiere el puesto alguna habilidad verbal, física, técnica, etc.?
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; border-left: 2px solid black;font-weight:bold;">Concepto</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Si</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">No</td>
    </tr>
    <tr>
        <td colspan="2">Escrita: Redactar informes, memorándums, comunicarse vía correo electrónico, convocatorias, reportes, etc.
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->escritura == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->escritura == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Verbal: Expresar pensamientos, ideas, asesorías, comunicarse vía teléfonica, grabación de voz, desarrollar y
            brindar conferencias, capacitaciones, hablar con clientes o usuarios, etc.
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->verbal == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->verbal == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Física: Hacer uso de la fuerza para el traslado de equipos, materiales, objetos de peso considerable, manejo
            de automóvil o equipos especializados, etc.
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->fisica == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->fisica == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Visual:Identificar letras, números, hacer uso de computadoras o lecturas de textos.
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->visual == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->visual == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Númerica: Hacer uso de las operaciones númericas esenciales, desarrollar estrategias útiles y financieras,
            medir, estimar.
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->numerica == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($habilidades->numerica == 0)
            X
            @endif
        </td>
    </tr>
</table>

<table style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;   ">
    <tr>
        <td style="  border-bottom: 2px solid black;   background-color: #c4c4c4;  border: 3px solid black;" colspan="4">5. Competencias</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:22px;" colspan="4">Instrucciones: Indicar las competencias específicas requeridas para el puesto.
        </td>
    </tr>
    <tr>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Competencia</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Alto</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Medio</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">A desarrollar</td>
    </tr>
    <tr>
        <td>Capacidad de sintesis</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->sintesis == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->sintesis == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->sintesis == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Lealtad y sentido de pertenencia </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->lealtad == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->lealtad == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->lealtad == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Confiabilidad-franqueza </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->confiabilidad == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->confiabilidad == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->confiabilidad == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Ética </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->etica == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->etica == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->etica == 2)
            X
            @endif
        </td>
    </tr>
    <tr>

        <td>Disponibilidad</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->disponibilidad == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->disponibilidad == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->disponibilidad == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Temple </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->temple == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->temple == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->temple == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Facilidad de palabra</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->fac_palabra == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->fac_palabra == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->fac_palabra == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Trabajo en equipo </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->tra_equipo == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->tra_equipo == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->tra_equipo == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Relaciones publicas-diplomacia</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->diplomacia == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->diplomacia == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->diplomacia == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Negociación</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->negociacion == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->negociacion == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->negociacion == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Pensamiento analítico</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->analitico == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->analitico == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($competencias->analitico == 2)
            X
            @endif
        </td>
    </tr>
</table>
<table style="border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;    ;">
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">6. Requisitos técnicos</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:65px;" colspan="4">Instrucciones: Llenar conforme a lo requerido para el desarrollo del puesto.
            <br>¿Cuál es la experiencia necesaria para el puesto?
            <br>¿Cuál es el nivel educativo del puesto?
            <br>¿Requiere de cursos?
        </td>
    </tr>
    <tr>
        <td colspan="2">Nivel educativo:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">{{$perfil['nivel_educativo']}} .</td>
    </tr>
    <tr>
        <td colspan="2">¿Terminado o trunco?:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">
            @if($perfil['terminado'] ==0 )
            Terminado .
            @else
            Trunco .
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Título o especialización:</td>
        <td colspan="2" style="  border-bottom: 2px solid black;">{{$perfil['titulo']}} .</td>
    </tr>
    <tr>
        <td>
            Experiencia en:
        </td>
        <td>
            @foreach($experiencia as $exp)
            <a> {{$e}} - {{$exp}} .</a>
            <br>
            @php
            $e++;
            @endphp
            @endforeach
        </td>
        <td>Tiempo de experiencia:</td>
        <td>
            @foreach($tiempo_experiencia as $dom_exp)
            <a> {{$dom_exp}} .</a>
            <br>
            @endforeach
        </td>
    </tr>
    <tr>
        <td>
            Conocimientos:
        </td>
        <td>
            @foreach($conocimientos as $exp)
            <a> {{$co}} - {{$exp}} .</a>
            <br>
            @php
            $e++;
            @endphp
            @endforeach
        </td>
        <td>Dominio:</td>
        <td>
            @foreach($dominio_c as $dom_exp)
            <a> {{$dom_exp}} .</a>
            <br>
            @endforeach
        </td>
    </tr>
    <tr>
        <td>
            Cursos:
        </td>
        <td>
            @foreach($cursos as $exp)
            <a> {{$cu}} - {{$exp}} .</a>
            <br>
            @php
            $e++;
            @endphp
            @endforeach
        </td>
        <td>Antigüedad:</td>
        <td>
            @foreach($ant_curso as $dom_exp)
            <a> {{$dom_exp}} .</a>
            <br>
            @endforeach
        </td>
    </tr>
</table>

<table style=" border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;   ;">
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">7. Actividades</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:22px;" colspan="4">Instrucciones: Determinar las principales funciones que realiza la posición
        </td>
    </tr>
    <tr>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Actividad</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">
            <p>Frecuentemente (a diario y cada tercer día)</p>
        </td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">
            <p>Algunas veces (1 vez al año o eventualmente)</p>
        </td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">
            <p>Ocasionalmente
                (1 vez a la semana o 1 vez al mes) </p>
        </td>
    </tr>
    <tr>
        <td>Elaboración de kits de contrato laboral, convenio de
            confidencialidad, carta de agremio, documentos cliente</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_contrato == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_contrato == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_contrato == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Elaboración de kits de desvinculaciones (carta renuncia,
            cálculo de finiquito, integración de timbre fiscal y constancia)</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_desvincula == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_desvincula == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->kit_desvincula == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Elaboración de contratos y desvinculaciones.</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->cont_desvincula == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->cont_desvincula == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->cont_desvincula == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Análisis de base de datos.</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->anal_db == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->anal_db == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->anal_db == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Firma de finiquitos, validación de documentos y liberación
            de pago</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_finiquito == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_finiquito == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_finiquito == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Manejo del sistema para anexar documentos de expedientes
            y para evidencia (altas, bajas)</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->sist_doc == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->sist_doc == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->sist_doc == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Escaneo o digitalización de documentos.</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->dig_doc == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->dig_doc == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->dig_doc == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Resguardo y manejo de expedientes.</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->m_expedientes == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->m_expedientes == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->m_expedientes == 2)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td>Firma recibos de nóminas.</td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_nomina == 0)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_nomina == 1)
            X
            @endif
        </td>
        <td style="text-align: center;border-left: 2px solid black;">
            @if($actividades->f_nomina == 2)
            X
            @endif
        </td>
    </tr>
</table>

<table style=" border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;    ;">
    <tr>
        <td colspan="4" style="border-bottom: 2px solid black;background-color: #c4c4c4;  border: 3px solid black;">8. Autoridades</td>
    </tr>
    <br>
    <tr>
        <td colspan="4" style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:22px;">Instrucciones: Determinar las principales funciones que realiza la posición
        </td>
    </tr>
    <tr>
        <td colspan="4">
            @foreach($act_autoridades as $act)
            <p style="font-weight: bold; color: #fbba00;">{{$i}}) {{$act}}</p>
            @php
            $i++
            @endphp
            @endforeach
        </td>
    </tr>
</table>

<table style="  border-left: 2px solid black;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;   ;">
    <tr>
        <td style="  border-bottom: 2px solid black;  ; background-color: #c4c4c4;  border: 3px solid black;" colspan="4">9. Otros</td>
    </tr>
    <br>
    <tr>
        <td style="background-color: #82A1B1;justify-content: left;  font-size: 9px;border-bottom: 2px solid black; height:64px;" colspan="4">Instrucciones: Indicar otros aspectos requeridos en la posición que no están contemplados en las secciones anteriores.
            <br>¿Disponibilidad para viajar?
            <br>¿Disponibillidad para cambiar de residencia?
            <br>¿Saber manejar?
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; border-left: 2px solid black;font-weight:bold;">Concepto</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">Si</td>
        <td style="text-align: center; border-left: 2px solid black;font-weight:bold;">No</td>
    </tr>
    <tr>
        <td colspan="2">Disponibilidad para viajar</td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->viajar == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->viajar == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Cambio de residencia</td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->camb_resi == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->camb_resi == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Manejar</td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->manejar == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->manejar == 0)
            X
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2">Contar con licencia</td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->lic_conducir == 1)
            X
            @endif
        </td>
        <td style="text-align: center; border-left: 2px solid black;">
            @if($otros->lic_conducir == 0)
            X
            @endif
        </td>
    </tr>
</table>

<table style="border: none; margin-top: 120px;">
    <tr>

    </tr>
    <tr>
        <td style="width:350px;"></td>
        <td style="font-weight: bold; font-size:8px;text-align:center;border-top: 2px solid black ;width: 300px;">
            Firma del gerente del área
        </td>
        <td style="width: 150px;"></td>
        <td style="font-weight: bold; font-size:8px;text-align:center;border-top: 2px solid black ; width: 300px;">
            Firma del empleado que ocupa el puesto
        </td>
    </tr>
</table>
@endforeach