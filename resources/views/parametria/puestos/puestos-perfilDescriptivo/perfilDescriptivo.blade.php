@php

$val_perfil = count($perfil);
if($val_perfil != 0){
$perfil = $perfil[0];
$reportan = json_decode($perfil->reportan);
$c_interna =json_decode($perfil->c_interna);
$relaciones = json_decode($perfil->relaciones);
$habilidades = json_decode($perfil->habilidades);
$competencias = json_decode($perfil->competencias);
$experiencia = json_decode($perfil->experiencia);
$tiempo_experiencia = json_decode($perfil->tiempo_experiencia);
$conocimientos = json_decode($perfil->conocimientos);
$dominio_c = json_decode($perfil->dominio_c);
$cursos = json_decode($perfil->cursos);
$ant_curso = json_decode($perfil->ant_curso);
$actividades = json_decode($perfil->actividades);
$act_autoridades = json_decode($perfil->act_autoridades);
$otros = json_decode($perfil->otros);
}else{
$perfil = null;
$reportan = null;
$c_interna =null;
$relaciones = null;
$habilidades = null;
$competencias = null;
$experiencia = null;
$tiempo_experiencia = null;
$conocimientos = null;
$dominio_c = null;
$cursos = null;
$ant_curso = null;
$actividades = null;
$act_autoridades = null;
$otros = null;
}

@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Perfil descriptivo del puesto',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'parametria.puestos'])

        <div>
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

                <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                    <div class="">
                        @if($perfil != null)

                        <a name="exportar_pdf" href="{{ route('puestos.exportarPerfilDescriptivo',[ 'pdf',  base64_encode($puestos[0]->id)])}}" role="button" class="editar" alt="Descargar PDF" title="Descargar PDF">
                            <button type="button" class="button-style ml-3 mb-3"> <img src="/img/icono-exportar.png" class="button-style-icon">Exportar PDF</button>
                        </a>

                        <a name="exportar_pdf" href="{{ route('puestos.exportarPerfilDescriptivo',[ 'excel',  base64_encode($puestos[0]->id)])}}" class="editar" alt="Descargar excel" title="Descargar excel">
                            <button type="button" class="button-style ml-3 mb-3" data-toggle="tooltip" title="Anexar puestos reales"> <img src="/img/icono-exportar.png" class="button-style-icon">Exportar excel</button>
                        </a>

                        @endif
                    </div>
                </div>

            </div>

            <form method="post" id="submit_perfil" action="{{route('puestos.editarPerfilDescriptivo')}}" enctype="multipart/form-data">
                @csrf
                @foreach( $puestos as $puesto)
                <div class="row m-0 p-0">
                    <div class="col-md-6 p-2">
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Datos generales del puesto</label>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Nombre del puesto:</label>
                                    </td>
                                    <td>
                                        <input type="text" name="id_puesto" class="float-right input-style-parametro mb-2" required hidden value="{{$puesto->id}}">
                                        <input type="text" name="nombre_puesto" id="nombre_puesto" class="float-right input-style-parametro mb-2" required readonly value="{{$puesto->puesto}}">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Objetivo del puesto:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <textarea class="float-right input-style-parametro mb-2" name="objetivo_puesto" id="objetivo_puesto" rows="3" placeholder="Por favor detalle el objetivo del puesto" required>{{$perfil->objetivo_puesto}}</textarea>
                                        @else
                                        <textarea class="float-right input-style-parametro mb-2" name="objetivo_puesto" id="objetivo_puesto" rows="3" placeholder="Por favor detalle el objetivo del puesto" required></textarea>
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Área a la que pertenece:</label>
                                    </td>
                                    <td>
                                        <select name="id_departamento" id="id_departamento" class="float-right input-style-parametro mb-2" required>
                                            @foreach($departamentos as $departamento)

                                            @if($perfil != null)

                                            <option value="{{$departamento->id}}" {{($perfil->id_departamento == $departamento->id) ? 'selected' : ''}}>{{$departamento->nombre}}
                                            </option>
                                            @else
                                            <option value="" selected disabled>Área a la que pertenece</option>
                                            <option value="{{$departamento->id}}">{{$departamento->nombre}}</option>
                                            @endif

                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Horario:</label>
                                    </td>
                                    <td>
                                        <select name="id_horario" id="id_horario" class="float-right input-style-parametro mb-2" required>
                                            @foreach($horarios as $horario)
                                                <option value="{{$horario->id}}" {{($perfil)? ($perfil->id_horario == $horario->id) ? 'selected' : '' :'' }}>{{$horario->alias}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Tipo de contrato:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <select name="tipo_contrato" id="tipo_contrato" class="float-right input-style-parametro mb-2" required>

                                            <option value="{{$perfil->id_contrato}}" {{('01' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO</option>
                                            <option value="{{$perfil->id_contrato}}" {{('02' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR OBRA DETERMINADA</option>
                                            <option value="{{$perfil->id_contrato}}" {{('03' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TIEMPO DETERMINADO</option>
                                            <option value="{{$perfil->id_contrato}}" {{('04' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TEMPORADA</option>
                                            <option value="{{$perfil->id_contrato}}" {{('05' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO SUJETO A PRUEBA</option>
                                            <option value="{{$perfil->id_contrato}}" {{('06' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>Contrato de trabajo con capacitación inicial</option>
                                            <option value="{{$perfil->id_contrato}}" {{('07' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>Modalidad de contratación por pago de hora laborada</option>
                                            <option value="{{$perfil->id_contrato}}" {{('08' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>Modalidad de trabajo por comisión laboral</option>
                                            <option value="{{$perfil->id_contrato}}" {{('09' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>Modalidades de contratación donde no existe relación de trabajo</option>
                                            <option value="{{$perfil->id_contrato}}" {{('10' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>JUBILACIÓN, PENSIÓN, RETIRO</option>
                                            <option value="{{$perfil->id_contrato}}" {{('99' == old('$perfil->tipo_contrato')) ? 'selected' : ''}}>OTRO CONTRATO</option>
                                        </select>
                                        @else
                                        <select name="tipo_contrato" id="tipo_contrato" class="float-right input-style-parametro mb-2" required>
                                            <option value="" selected disabled>Tipo de Contrato</option>
                                            <option value="01" {{('01' == old('tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO</option>
                                            <option value="02" {{('02' == old('tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR OBRA DETERMINADA</option>
                                            <option value="03" {{('03' == old('tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TIEMPO DETERMINADO</option>
                                            <option value="04" {{('04' == old('tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO POR TEMPORADA</option>
                                            <option value="05" {{('05' == old('tipo_contrato')) ? 'selected' : ''}}>CONTRATO DE TRABAJO SUJETO A PRUEBA</option>
                                            <option value="06" {{('06' == old('tipo_contrato')) ? 'selected' : ''}}>Contrato de trabajo con capacitación inicial</option>
                                            <option value="07" {{('07' == old('tipo_contrato')) ? 'selected' : ''}}>Modalidad de contratación por pago de hora laborada</option>
                                            <option value="08" {{('08' == old('tipo_contrato')) ? 'selected' : ''}}>Modalidad de trabajo por comisión laboral</option>
                                            <option value="09" {{('09' == old('tipo_contrato')) ? 'selected' : ''}}>Modalidades de contratación donde no existe relación de trabajo</option>
                                            <option value="10" {{('10' == old('tipo_contrato')) ? 'selected' : ''}}>JUBILACIÓN, PENSIÓN, RETIRO</option>
                                            <option value="99" {{('99' == old('tipo_contrato')) ? 'selected' : ''}}>OTRO CONTRATO</option>
                                        </select>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Rango de salario:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <input type="text" name="rango_salario" id="rango_salario" class="float-right input-style-parametro mb-2" value="{{$perfil->rango_salario}}" required>
                                        @else
                                        <input type="text" name="rango_salario" id="rango_salario" class="float-right input-style-parametro mb-2" placeholder="Ingresa el rango en cifras" required>
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Condiciones adicionales:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <textarea class="float-right input-style-parametro mb-2" name="condiciones" rows="3" placeholder="Prestaciones LFT, superiores, etc">{{$perfil->condiciones}}</textarea>
                                        @else
                                        <textarea class="float-right input-style-parametro mb-2" name="condiciones" rows="3" placeholder="Prestaciones LFT, superiores, etc"></textarea>
                                        @endif


                                    </td>
                                </tr>
                            </table>
                        </div>
                        <br>
                        <br>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Posición en el organigrama</label>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Nivel jerarquico:</label>
                                    </td>
                                    <td>
                                        <input type="text" name="jerarquia" class="float-right input-style-parametro mb-2" readonly value="{{$puesto->jerarquia}}" placeholder="No asignado">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Puesto al que le reporta:</label>
                                    </td>
                                    <td>
                                        <select name="id_dependencia" id="id_dependencia" class="float-right input-style-parametro mb-2">
                                            @foreach($dependencias as $dependencia)
                                            @if($perfil != null)
                                            <option value="{{$dependencia->id}}" {{($perfil->id_dependencia == $dependencia->id) ? 'selected' : ''}}>{{$dependencia->puesto}}
                                                <{{$dependencia->puesto}}< /option>
                                                    @else
                                            <option value="" selected disabled>Puesto al que le reporta</option>
                                            <option value="{{$dependencia->id}}">{{$dependencia->puesto}}</option>
                                            @endif


                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">¿Tiene personal a su cargo?</label>
                                    </td>
                                    <td>
                                        <select name="personal" id="personal" class="float-right input-style-parametro mb-2">
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Puestos que le reportan:</label>
                                    </td>
                                    <td>
                                        <div class="float-right input-style-parametro mb-2" style="height:90px; overflow:auto">
                                            @foreach ($dependencias as $reportan)
                                            <div>

                                                <input type="checkbox" name="reportan[]" value="{{$reportan->id}}" id="reportan{{$reportan->id}}" class="mb-3">
                                                <label for="reportan{{$reportan->id}}"><strong>{{ucfirst(Str::lower($reportan->puesto))}}</strong></label><br>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Comunicación interna:</label>
                                    </td>
                                    <td>
                                        <div class="float-right input-style-parametro mb-2" style="height:90px; overflow:auto">
                                            @foreach ($dependencias as $reportan)
                                            <div>
                                                <input type="checkbox" name="c_interna[]" value="{{$reportan->id}}" id="interna{{$reportan->id}}" class="mb-3">
                                                <label for="interna{{$reportan->id}}"><strong>{{ucfirst(Str::lower($reportan->puesto))}}</strong></label><br>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <br>
                        <br>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Relaciones y manejos particulares</label>
                            <hr>
                            <p>
                                Identificar conforme a las responsabilidades del puesto:
                                <br>
                                ¿Cuáles son las relaciones que se encuentran adscritas al puesto?
                                <br>
                                ¿Cuál es el material y equipo que se requiere en las funciones del puesto?
                            </p>

                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Uso de materiales y equipos:</label>
                                    </td>
                                    <td>
                                        <select name="materiales" id="materiales" class="float-right input-style-parametro mb-2">

                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->materiales == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->materiales == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif


                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Manejo de recursos económicos:</label>
                                    </td>
                                    <td>
                                        <select name="rec_econ" id="rec_econ" class="float-right input-style-parametro mb-2">

                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rec_economicos == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rec_economicos == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Manejo de documentos importantes:</label>
                                    </td>
                                    <td>
                                        <select name="doc_imp" id="doc_imp" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->doc_imp == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->doc_imp == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Manejo de información confidencial:</label>
                                    </td>
                                    <td>
                                        <select name="info_conf" id="info_conf" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->inf_confidencial == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->inf_confidencial == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Uso de correo electrónico empresarial:</label>
                                    </td>
                                    <td>
                                        <select name="mail_empresarial" id="mail_empresarial" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->mail_empresarial == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->mail_empresarial == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con los clientes:</label>
                                    </td>
                                    <td>
                                        <select name="rel_clientes" id="rel_clientes" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_clientes == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_clientes == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con los directivos de la compañia:</label>
                                    </td>
                                    <td>
                                        <select name="rel_directivo" id="rel_directivo" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_directivos == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_directivos == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con las gerencias:</label>
                                    </td>
                                    <td>
                                        <select name="rel_gerencia" id="rel_gerencia" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_gerencias == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_gerencias == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con las jefaturas, supervisiones o subgerencias:</label>
                                    </td>
                                    <td>
                                        <select name="rel_jefatura" id="rel_jefatura" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_jefaturas == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_jefaturas == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con los auxiliares:</label>
                                    </td>
                                    <td>
                                        <select name="rel_aux" id="rel_aux" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_auxiliares == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_auxiliares == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con los becarios:</label>
                                    </td>
                                    <td>
                                        <select name="rel_beca" id="rel_beca" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_becarios == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_becarios == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relación directa con servicios generales:</label>
                                    </td>
                                    <td>
                                        <select name="rel_sg" id="rel_sg" class="float-right input-style-parametro mb-2">
                                            @if($relaciones != null)
                                            <option value="0" {{($relaciones->rel_serviciosG == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($relaciones->rel_serviciosG == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Habilidades especificas</label>
                            <hr>
                            <p>
                                Indicar conforme a las responsabilidades y actividades del puesto, las habilidades específicas
                                requeridas para su desarrollo.
                            </p>
                            <table class="w-100">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Escrita:</label>
                                        <p>Redactar informes, memorándums, comunicarse vía correo electrónico, convocatorias, reportes, etc.</p>
                                    </td>
                                    <td>
                                        <select name="escritura" id="escritura" class="float-right input-style-parametro mb-2">
                                            @if($habilidades != null)
                                            <option value="0" {{($habilidades->escritura == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($habilidades->escritura == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Verbal:</label>
                                        <p>Expresar pensamientos, ideas, asesorías, comunicarse vía teléfonica, grabación de voz, desarrollar y
                                            brindar conferencias, capacitaciones, hablar con clientes o usuarios, etc. </p>
                                    </td>
                                    <td>
                                        <select name="verbal" id="verbal" class="float-right input-style-parametro mb-2">
                                            @if($habilidades != null)
                                            <option value="0" {{($habilidades->verbal == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($habilidades->verbal == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Física:</label>
                                        <p>
                                            Hacer uso de la fuerza para el traslado de equipos, materiales, objetos de peso considerable, manejo
                                            de automóvil o equipos especializados, etc.
                                        </p>
                                    </td>
                                    <td>
                                        <select name="fisica" id="fisica" class="float-right input-style-parametro mb-2">
                                            @if($habilidades != null)
                                            <option value="0" {{($habilidades->fisica == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($habilidades->fisica == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Visual:</label>
                                        <p>
                                            Identificar letras, números, hacer uso de computadoras o lecturas de textos.
                                        </p>
                                    </td>
                                    <td>
                                        <select name="visual" id="visual" class="float-right input-style-parametro mb-2">
                                            @if($habilidades != null)
                                            <option value="0" {{($habilidades->visual == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($habilidades->visual == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Númerica:</label>
                                        <p>
                                            Hacer uso de las operaciones númericas esenciales, desarrollar estrategias útiles y financieras,
                                            medir, estimar.
                                        </p>
                                    </td>
                                    <td>
                                        <select name="numerica" id="numerica" class="float-right input-style-parametro mb-2">
                                            @if($habilidades != null)
                                            <option value="0" {{($habilidades->numerica == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($habilidades->numerica == 1) ? 'selected' : ''}}>Si</option>
                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <br>
                            <input type="button"  class="center button-style" id="edit_parametro" value="Guardar">
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="article border mb-4">
                            <label class="font-weight-bold center font-size-1-2em">Competencias</label>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Capacidad de sintesis:</label>
                                    </td>
                                    <td>
                                        <select name="sintesis" id="sintesis" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->sintesis == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->sintesis == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->sintesis == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Lealtad y sentido de pertenencia:</label>
                                    </td>
                                    <td>
                                        <select name="lealtad" id="lealtad" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->lealtad == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->lealtad == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->lealtad == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Confiabilidad - franqueza:</label>
                                    </td>
                                    <td>
                                        <select name="confiabilidad" id="confiabilidad" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->confiabilidad == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->confiabilidad == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->confiabilidad == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Ética:</label>
                                    </td>
                                    <td>
                                        <select name="etica" id="etica" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->etica == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->etica == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->etica == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Disponibilidad:</label>
                                    </td>
                                    <td>
                                        <select name="disponibilidad" id="disponibilidad" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->disponibilidad == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->disponibilidad == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->disponibilidad == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Temple:</label>
                                    </td>
                                    <td>
                                        <select name="temple" id="temple" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->temple == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->temple == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->temple == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Facilidad de palabra:</label>
                                    </td>
                                    <td>
                                        <select name="fac_palabra" id="fac_palabra" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->fac_palabra == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->fac_palabra == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->fac_palabra == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Trabajo en equipo:</label>
                                    </td>
                                    <td>
                                        <select name="tra_equipo" id="tra_equipo" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->tra_equipo == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->tra_equipo == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->tra_equipo == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Relaciones publicas-diplomacia:</label>
                                    </td>
                                    <td>
                                        <select name="diplomacia" id="diplomacia" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->diplomacia == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->diplomacia == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->diplomacia == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Negociacion:</label>
                                    </td>
                                    <td>
                                        <select name="negociacion" id="negociacion" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->negociacion == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->negociacion == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->negociacion == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Pensamiento analítico:</label>
                                    </td>
                                    <td>
                                        <select name="analitico" id="analitico" class="float-right input-style-parametro mb-2">
                                            @if($competencias != null)
                                            <option value="0" {{($competencias->analitico == 0) ? 'selected' : ''}}>Alto</option>
                                            <option value="1" {{($competencias->analitico == 1) ? 'selected' : ''}}>Medio</option>
                                            <option value="2" {{($competencias->analitico == 2) ? 'selected' : ''}}>A desarrollar</option>

                                            @else
                                            <option value="0">Alto</option>
                                            <option value="1">Medio</option>
                                            <option value="2">A desarrollar</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="article border mb-4">
                            <label class="font-weight-bold center font-size-1-2em">Requisitos técnicos</label>
                            <hr>
                            <table class="w-100">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Nivel educativo:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <input type="text" name="nivel_educativo" class="float-right input-style-parametro mb-2" value="{{$perfil->nivel_educativo}}">
                                        @else
                                        <input type="text" name="nivel_educativo" class="float-right input-style-parametro mb-2">
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">¿Terminado o trunco?</label>
                                    </td>
                                    <td>
                                        <select name="terminado" id="terminado" class="float-right input-style-parametro mb-2">
                                            @if($perfil != null)
                                            <option value="0" {{($perfil->terminado == 0) ? 'selected' : ''}}>Terminado</option>
                                            <option value="1" {{($perfil->terminado == 1) ? 'selected' : ''}}>Trunco</option>
                                            @else
                                            <option value="0">Terminado</option>
                                            <option value="1">Trunco</option>
                                            @endif

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Título o espacialización:</label>
                                    </td>
                                    <td>
                                        @if($perfil != null)
                                        <input type="text" name="titulo" class="float-right input-style-parametro mb-2" value="{{$perfil->titulo}}">
                                        @else
                                        <input type="text" name="titulo" class="float-right input-style-parametro mb-2">
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold mt-3" for="experiencia">Experiencia en:</label>
                                    </td>
                                    <td>
                                        <label class="text-left font-weight-bold float-right mt-3" for="tiempo_experiencia">Tiempo de experiencia:<label>
                                    </td>
                                </tr>
                                @if($experiencia != null)
                                <tr>
                                    <td>
                                        @foreach($experiencia as $exp)

                                        <input type="text" name="experiencia[]" id="experiencia" value="{{$exp}}" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required>


                                        @endforeach
                                    </td>

                                    <td>
                                        @foreach($tiempo_experiencia as $exp)

                                        <input type="text" name="tiempo_experiencia[]" id="tiempo_experiencia" value="{{$exp}}" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left;" required>


                                        @endforeach
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td>
                                        <input type="text" name="experiencia[]" id="experiencia" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required>

                                    </td>
                                    <td>
                                        <input type="text" name="tiempo_experiencia[]" id="tiempo_experiencia" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left;" required>
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <td>

                                        <div class="btn bg-color-yellow btn-sm ml-2 mb-5" id="agregar-experiencia" name="agregar-experiencia" data-toogle="tooltip" title="Agregar experiencia " data-placement="left">
                                            <li class="fas fa-plus"></li>

                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Conocimientos:</label>
                                    </td>
                                    <td>
                                        <label class="text-left font-weight-bold float-right">Dominio del conocimiento:</label>
                                    </td>
                                </tr>
                                @if($conocimientos != null)
                                <tr>
                                    <td>
                                        @foreach($conocimientos as $conocimiento)
                                        <input type="text" name="conocimientos[]" id="conocimientos" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" value="{{$conocimiento}}" required>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($dominio_c as $dominio)
                                        <input type="text" name="dominio_c[]" id="dominio_c" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left;" value="{{$dominio}}" required>
                                        @endforeach
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td>
                                        <input type="text" name="conocimientos[]" id="conocimientos" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required>
                                    </td>
                                    <td>
                                        <input type="text" name="dominio_c[]" id="dominio_c" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left;" required>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        <div class="btn bg-color-yellow btn-sm ml-2 mb-5" id="agregar-conocimientos" data-toogle="tooltip" title="Agregar conocimientos" data-placement="left">
                                            <li class="fas fa-plus"></li>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Cursos realizados:</label>
                                    </td>
                                    <td>
                                        <label class="text-left font-weight-bold float-right">Antigüedad del curso:</label>
                                    </td>
                                </tr>
                                @if($cursos != null)
                                <tr>
                                    <td>
                                        @foreach($cursos as $curso)
                                        <input type="text" name="cursos[]" id="cursos" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" value="{{$curso}}" required>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($ant_curso as $ant)
                                        <input type="text" name="ant_curso[]" id="ant_curso" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left" value="{{$ant}}" required>
                                        @endforeach
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td>
                                        <input type="text" name="cursos[]" id="cursos" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required>
                                    </td>
                                    <td>
                                        <input type="text" name="ant_curso[]" id="ant_curso" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left" required>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        <div class="btn bg-color-yellow btn-sm ml-2 mb-2" id="agregar-cursos" data-toogle="tooltip" title="Agregar cursos" data-placement="left">
                                            <li class="fas fa-plus"></li>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                        </div>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Actividades</label>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Elaboración de kits de contrato laboral, convenio de
                                            confidencialidad, carta de agremio, documentos cliente:</label>
                                    </td>
                                    <td>
                                        <select name="kit_contrato" id="kit_contrato" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->kit_contrato == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->kit_contrato == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->kit_contrato == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Elaboración de kits de desvinculaciones (carta renuncia,
                                            cálculo de finiquito, integración de timbre fiscal y constancia):</label>
                                    </td>
                                    <td>
                                        <select name="kit_dev" id="kit_dev" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->kit_desvincula == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->kit_desvincula == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->kit_desvincula == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Elaboración de contratos y desvinculaciones:</label>
                                    </td>
                                    <td>
                                        <select name="cont_dev" id="cont_dev" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->cont_desvincula == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->cont_desvincula == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->cont_desvincula == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Análisis de base de datos:</label>
                                    </td>
                                    <td>
                                        <select name="a_bd" id="a_bd" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->anal_db == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->anal_db == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->anal_db == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Firma de finiquitos, validación de documentos y liberación
                                            de pago:</label>
                                    </td>
                                    <td>
                                        <select name="finiq" id="finiq" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->f_finiquito == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->f_finiquito == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->f_finiquito == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Manejo del sistema para anexar documentos de expedientes
                                            y para evidencia (altas, bajas):</label>
                                    </td>
                                    <td>
                                        <select name="sist_doc" id="sist_doc" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->sist_doc == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->sist_doc == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->sist_doc == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Escaneo o digitalización de documentos:</label>
                                    </td>
                                    <td>
                                        <select name="dig_doc" id="dig_doc" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->dig_doc == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->dig_doc == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->dig_doc == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Resguardo y manejo de expedientes:</label>
                                    </td>
                                    <td>
                                        <select name="man_exp" id="man_exp" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->m_expedientes == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->m_expedientes == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="1" {{($actividades->m_expedientes == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Firma recibos de nóminas:</label>
                                    </td>
                                    <td>
                                        <select name="firm_nom" id="firm_nom" class="float-right input-style-parametro mb-2">
                                            @if($actividades != null)
                                            <option value="0" {{($actividades->f_nomina == 0) ? 'selected' : ''}}>Frecuentemente</option>
                                            <option value="1" {{($actividades->f_nomina == 1) ? 'selected' : ''}}>Algunas veces</option>
                                            <option value="2" {{($actividades->f_nomina == 2) ? 'selected' : ''}}>Ocasionalmente</option>
                                            @else
                                            <option value="0">Frecuentemente</option>
                                            <option value="1">Algunas veces</option>
                                            <option value="2">Ocasionalmente</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Autoridades</label>
                            <hr>
                            <p>
                                Determinar las principales funciones que realiza la posición.
                            </p>
                            <table width="100%">


                                @if($act_autoridades != null)
                                @foreach($act_autoridades as $actividad)
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold" for="act_auto">Actividad:</label>
                                    </td>
                                    <td>
                                        <input type="text" name="act_auto[]" id="act_auto" class="float-right input-style-parametro mb-2" value="{{$actividad}}" required>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold" for="act_auto">Actividad:</label>
                                    </td>
                                    <td>
                                        <input type="text" name="act_auto[]" id="act_auto" class="float-right input-style-parametro mb-2">
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        <div class="btn bg-color-yellow btn-sm ml-2" style="margin-top: -9px;" id="agregar-actividad" data-toogle="tooltip" title="Agregar actividad" data-placement="left">
                                            <li class="fas fa-plus"></li>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="article border mb-4 px-4">
                            <label class="font-weight-bold center font-size-1-2em">Otros</label>
                            <hr>
                            <p>
                                Indicar otros aspectos requeridos en la posición que no están contemplados en las secciones anteriores.
                            </p>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Disponibilidad para viajar:</label>
                                    </td>
                                    <td>
                                        <select name="viajar" id="viajar" class="float-right input-style-parametro mb-2">
                                            @if($otros != null)
                                            <option value="0" {{($otros->viajar == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($otros->viajar == 1) ? 'selected' : ''}}>Si</option>

                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Cambio de residencia:</label>
                                    </td>
                                    <td>
                                        <select name="camb_resi" id="camb_resi" class="float-right input-style-parametro mb-2">
                                            @if($otros != null)
                                            <option value="0" {{($otros->camb_resi == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($otros->camb_resi == 1) ? 'selected' : ''}}>Si</option>

                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Manejar:</label>
                                    </td>
                                    <td>
                                        <select name="manejar" id="manejar" class="float-right input-style-parametro mb-2">
                                            @if($otros != null)
                                            <option value="0" {{($otros->manejar == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($otros->manejar == 1) ? 'selected' : ''}}>Si</option>

                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="text-left font-weight-bold">Contar con licencia:</label>
                                    </td>
                                    <td>
                                        <select name="lice_c" id="lice_c" class="float-right input-style-parametro mb-2">
                                            @if($otros != null)
                                            <option value="0" {{($otros->lic_conducir == 0) ? 'selected' : ''}}>No</option>
                                            <option value="1" {{($otros->lic_conducir == 1) ? 'selected' : ''}}>Si</option>

                                            @else
                                            <option value="0">No</option>
                                            <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </form>
            @include('includes.footer')

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>


            <script>
                var reporta = document.getElementsByName('reportan[]');
                var c_interna = document.getElementsByName('c_interna[]');

                $(document).ready(function() {
                    if(@json($perfil)){
                        const check_reporta=@if($perfil) @json($perfil->reportan) @else '' @endif ;
                        const check_interna=@if($perfil) @json($perfil->c_interna) @else '' @endif;
                        const limp_reporta = check_reporta.slice(1, -1).split(',');
                        limp_reporta.map((item)=>{
                            let extrac_num=item.replace(/[^0-9]+/g, "");
                            reporta.forEach((item,key)=>{
                                if(item.id==`reportan${extrac_num}`){
                                    item.checked=true;
                                }
                            });

                        });

                        const limp_interna = check_interna.slice(1, -1).split(',');
                        limp_interna.map((item)=>{
                            let extrac_num=item.replace(/[^0-9]+/g, "");
                            c_interna.forEach((item,key)=>{
                                if(item.id==`interna${extrac_num}`){
                                    item.checked=true;
                                }
                            });

                        });
                    }


                    $("#agregar-experiencia").click(function() {
                        var contador = $("input[type='experiencia']").length;

                        let table = '<tr> <td> <input type="text" name="experiencia[]" id="experiencia" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required>  </td> <td> <input type="text" name="tiempo_experiencia[]" id="tiempo_experiencia" class=" input-style-parametro mb-2" style="display: flex; justify-content:left;" required> </td> </tr> ';

                        $(this).before(table);
                    });
                    $("#agregar-conocimientos").click(function() {
                        var contador = $("input[type='conocimientos']").length;

                        let table = '<tr> <td> <input type="text" name="conocimientos[]" id="conocimientos" class=" input-style-parametro mr-5 mb-2" style="display: flex; justify-content:right;" required></td> <td> <input type="text" name="dominio_c[]" id="dominio_c" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left;" required> </td> </tr>'
                        $(this).before(table);
                    });
                    $("#agregar-cursos").click(function() {
                        var contador = $("input[type='cursos']").length;

                        let table = '<tr> <td> <input type="text" name="cursos[]" id="cursos" class=" input-style-parametro mb-2" style="display: flex; justify-content:right;" required> </td> <td> <input type="text" name="ant_curso[]" id="ant_curso" class="float-right input-style-parametro mb-2" style="display: flex; justify-content:left" required> </td> </tr>'
                        $(this).before(table);
                    });
                    $("#agregar-actividad").click(function() {
                        var contador = $("input[type='act_auto']").length;

                        let table = '<tr> <td> <label class="text-left font-weight-bold" for="act_auto">Actividad:</label>  </td> <td><input type="text" name="act_auto[]" id="act_auto" class="float-right input-style-parametro mb-2" required> </td> </tr>'
                        $(this).before(table);
                    });
                });
            </script>

            <script type="text/javascript">
                $("#edit_parametro").click(function() {
                    var nombre_puesto = document.getElementById("nombre_puesto").value;
                    var objetivo_puesto = document.getElementById("objetivo_puesto").value;
                    var id_departamento = document.getElementById("id_departamento").value;
                    var id_horario = document.getElementById("id_horario").value;
                    var tipo_contrato = document.getElementById("tipo_contrato").value;
                    var rango_salario = document.getElementById("rango_salario").value;
                    var experiencia = document.getElementById("experiencia").value;
                    var tiempo_experiencia = document.getElementById("tiempo_experiencia").value;
                    var conocimientos = document.getElementById("conocimientos").value;
                    var dominio_c = document.getElementById("dominio_c").value;
                    var cursos = document.getElementById("cursos").value;
                    var ant_curso = document.getElementById("ant_curso").value;
                    var act_auto = document.getElementById("act_auto").value;


                    let check_reporta=false;
                    reporta.forEach((item,key)=>{
                        if(item.checked){
                            check_reporta=true;
                        }
                    });

                    let check_interna=false;
                    c_interna.forEach((item,key)=>{
                        if(item.checked){
                            check_interna=true;
                        }
                    });

                    if (check_interna==false || check_reporta==false || nombre_puesto == "" || objetivo_puesto == "" || id_departamento == "" || id_horario == "" || tipo_contrato == "" || rango_salario == "" || experiencia == "" || tiempo_experiencia == "" || conocimientos == "" || dominio_c == "" || cursos == "" || ant_curso == "" || act_auto == "") {
                        swal({
                            title: "Para continuar debes agregar la información requerida",
                        });
                    } else {
                        swal("Espere un momento, la información esta siendo procesada", {
                            icon: "success",
                            buttons: false,
                        });
                        setTimeout(submitperfil, 1500);
                    }

                });

                function submitperfil() {
                    document.getElementById("submit_perfil").submit()
                }
            </script>

</body>

</html>