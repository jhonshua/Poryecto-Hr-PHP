<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@include('empleados.biometrico.asignar')
@include('empleados.biometrico.registrar-huella')

<style type="text/css">
    .file-doc {
        background-color: #fbba00;
        border: 3px #fbba00;
        border-radius: 10px; }
    .oculto{
        display: none; }
    .title-name{
        font-weight: bold;
    }
</style>


<div class="container">
    
@include('includes.header',['title'=>'Información empleado',
        'subtitle'=>'Reingresos', 'img'=>'/img/control-empleados.png',
        'route'=>'reingresos.tabla'])
   
    <div class="col-md-12">

        <br>
        <br>
    </div>

    @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif


    <div class="article border">
        <form action="{{route('empleados.actualizarempleado')}}" method="post" id="submit_empleado" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{$empleado->id}}">
            <input type="hidden" name="estatus" value="1">

        <div class="row">
            <div class="col-md-5"></div>
            <div class="col-md-2 text-center">
                
                <img src="{{asset($empleado->avatar)}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                @if($empleado->qr)
                    <h5><strong>Código VCARD</strong></h5>
                    <img src="{{asset($empleado->qr)}}" alt="" class="fotografia img-thumbnail img-fluid mb-5">
                @endif

            </div>
            <div class="col-md-5"></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="font-size-1-3em mb-5 under-line">Datos Generales</label>
                        </div>
                    </div>
                
{{-- PASO 1 --}}<div class="row"> 
    
                    <div class="col-md-4">
                        
                        <span class="title-name"># Empleado: </span> <span>{{ $empleado->numero_empleado }}</span><br>
                        <span class="title-name">Nombre: </span> <span>{{ $empleado->nombre }}</span><br>
                        <span class="title-name">Apellido paterno: </span> <span>{{ $empleado->apaterno }}</span><br>
                        <span class="title-name">Apellido materno: </span> <span>{{ $empleado->amaterno }}</span><br>
                        <span class="title-name">RFC: </span> <span>{{ $empleado->rfc }}</span><br>
                        <span class="title-name">CURP: </span> <span>{{ $empleado->curp }}</span><br>
                    
                    </div>
                    

                    <div class="col-md-4">
                        <span class="title-name">Fecha de nacimiento: </span> <span>{{ $empleado->fecha_nacimiento }}</span><br>
                        <span class="title-name">Fecha de alta: </span> <span>{{ $empleado->fecha_alta }}</span><br>
                        <span class="title-name">Lugar de nacimiento: </span> <span>{{ $empleado->lugar_nacimiento }}</span><br>
                        <span class="title-name">Prestaciones: </span>
                        {{-- <label>Prestaciones:  </label> --}}
                        @foreach ($categorias as $categoria)
                            @php
                                $categoria = ($categoria->id == $empleado->id_categoria) ? $categoria->nombre : '';
                                echo $categoria;
                            @endphp
                        @endforeach
                        <br>
                        <span class="title-name">Genero: </span> <span>{{ ( $empleado->genero == 'M') ? 'Masculino' : 'Femenino' }}</span><br>
                        <span class="title-name">Num Seguro Social: </span> <span>{{ $empleado->nss }}</span><br>

                    </div>

                    <div class="col-md-4">
                        <span class="title-name">Puesto: </span>
                            @foreach ($puestos as $puesto)
                                <span>{{ ($puesto->id == $empleado->id_puesto) ? $puesto->puesto : '' }}</span> 
                            @endforeach
                       <br>
                       <span class="title-name">Jefe inmediato:  </span>
                       @foreach ($jefeInmediato as $jefe)
                        {{ ($jefe->nombre) ? $jefe->nombre : '' }}
                        @endforeach
                        <br>
                       
                        <span class="title-name">Departamento: </span>
                            @foreach ($departamentos as $departamento)
                                {{ ($departamento->id == $empleado->id_departamento) ? $departamento->nombre : '' }}
                            @endforeach
                        <br>

                        @if(Session::get('empresa')['sede'] ==1)
                            <span class="title-name">Sede: </span>
                                @foreach ($sedes as $sede)
                                    {{ ($sede->id == $empleado->sede) ? $sede->nombre : '' }}
                                @endforeach
                            <br>
                        @endif

                        <span class="title-name">Centro de trabajo: </span> <span>{{ $empleado->ubicacion }}</span><br>

                        <span class="title-name">Tipo de jornada: </span>
                            {{ ( $empleado->tipo_jornada == '01') ? 'Diurna' : '' }}
                            {{ ( $empleado->tipo_jornada == '02') ? 'NOCTURNA' : '' }}
                            {{ ( $empleado->tipo_jornada == '03') ? 'MIXTA' : '' }}
                            {{ ( $empleado->tipo_jornada == '04') ? 'POR HORA' : '' }}
                            {{ ( $empleado->tipo_jornada == '05') ? 'REDUCIDA' : '' }}
                            {{ ( $empleado->tipo_jornada == '06') ? 'CONTINUADA' : '' }}
                            {{ ( $empleado->tipo_jornada == '07') ? 'PARTIDA' : '' }}
                            {{ ( $empleado->tipo_jornada == '08') ? 'POR TURNOS' : '' }}
                            {{ ( $empleado->tipo_jornada == '09') ? 'OTRA JORNADA' : '' }}
                        <br>

                        <span class="title-name">Tipo de contrato: </span>
                            {{ ( $empleado->tipo_contrato == '01') ? 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO ' : '' }}
                            {{ ( $empleado->tipo_contrato == '02') ? 'CONTRATO DE TRABAJO POR OBRA DETERMINADA' : '' }}
                            {{ ( $empleado->tipo_contrato == '03') ? 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO' : '' }}
                            {{ ( $empleado->tipo_contrato == '04') ? 'CONTRATO DE TRABAJO POR TEMPORADA' : '' }}
                            {{ ( $empleado->tipo_contrato == '05') ? 'CONTRATO DE TRABAJO SUJETO A PRUEBA' : '' }}
                            {{ ( $empleado->tipo_contrato == '06') ? 'Contrato de trabajo con capacitación inicial' : '' }}
                            {{ ( $empleado->tipo_contrato == '07') ? 'Modalidad de contratación por pago de hora laborada' : '' }}
                            {{ ( $empleado->tipo_contrato == '08') ? 'Modalidad de trabajo por comisión laboral' : '' }}
                            {{ ( $empleado->tipo_contrato == '09') ? 'Modalidades de contratación donde no existe relación de trabajo' : '' }}
                            {{ ( $empleado->tipo_contrato == '10') ? 'JUBILACIÓN, PENSIÓN, RE' : '' }}
                            {{ ( $empleado->tipo_contrato == '11') ? 'OTRO CONTRATO' : '' }}
                        <br>

                        <span class="title-name">Horario: </span>
                            @foreach ($horarios as $horario)
                                {{ ($horario->id == $empleado->id_horario) ? $horario->alias : '' }}
                            @endforeach
                        <br>


                    </div>

                </div>  

                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Salario</label>
                    </div>
                </div>

{{-- PASO 2 --}}<div class="row">
                        <div class="col-md-4">
                            <span class="title-name">Tipo de nomina: </span>
                                @foreach ($tipos_nomina as $tn)
                                    {{ (strtoupper($tn) == $empleado->tipo_de_nomina) ? strtoupper($tn) : '' }}
                                @endforeach
                            <br>

                            <span class="title-name">Salario diario: </span> <span>{{ $empleado->salario_diario }}</span><br>
                            <span class="title-name">Salario diario integrado: </span> <span>{{ $empleado->salario_diario_integrado }}</span><br>

                            <span class="title-name">Sueldo neto del periodo: </span> <span>{{ $empleado->sueldo_neto }}</span><br>
                            <span class="title-name">Sueldo diario real: </span> <span>{{ $empleado->salario_digital }}</span><br>

                        </div>

                        <div class="col-md-4">
                            <span class="title-name">Fecha de antiguedad: </span> <span>{{ $empleado->fecha_antiguedad }}</span><br>
                            <span class="title-name">Días de vacaciones: </span> <span>{{ $empleado->dias_vacaciones }}</span><br>

                            <span class="title-name">Días de aguinaldo: </span> <span>{{ $empleado->dias_aguinaldo }}</span><br>
                            <span class="title-name">% de prima vacacional: </span> <span>{{ $empleado->porcentaje_prima }}</span><br>
                        </div>

                        <div class="col-md-4">

                            <span class="title-name">Tipo salario: </span> <span>{{ $empleado->tipo_salario }}</span><br>
                            <span class="title-name">Clabe interbancaria: </span> <span>{{ $empleado->clabe_interbancaria }}</span><br>

                            <span class="title-name">Banco: </span> 
                                @foreach ($bancos as $banco)
                                    {{ ( $banco->id == $empleado->id_banco) ? strtoupper($banco->nombre) : '' }}
                                @endforeach
                            <br>
                            <span class="title-name">Cuenta banco: </span> <span>{{ $empleado->cuenta_bancaria }}</span><br>
                            <span class="title-name">Tipo cuenta: </span> 
                                {{ ( $empleado->tipo_cuenta == '01') ? 'CHEQUES' : '' }}
                                {{ ( $empleado->tipo_cuenta == '03') ? 'TARJETA DEDÉBITO' : '' }}
                                {{ ( $empleado->tipo_cuenta == '40') ? 'CLABE' : '' }}
                            <br>
                        </div>
                </div>


                @if ($msu == 1 && isset($last_modificacion->fecha_creacion))
                    <div class="row">
                        <div class="col-md-12 text-right mt-3">
                            <span class="under-line">{{ $empleado->nombre }} {{ $empleado->apaterno }} {{ $empleado->amaterno }} fue promovido(a) el dia {{ substr($last_modificacion->fecha_creacion ?? '00-00-00', 0,-9) }}</span>
                        </div>
                    </div>                                   
                @endif
                

                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Datos Personales</label>
                    </div>
                </div>

                {{-- PASO 2 --}}
                <div class="row">
                    <div class="col md-4">

                        <span class="title-name">Nacionalidad: </span> <span>{{ $empleado->nacionalidad }}</span><br>
                        <span class="title-name">Calle y num ext e int: </span> <span>{{ $empleado->calle_numero }}</span><br>
                        <span class="title-name">Colonia: </span> <span>{{ $empleado->colonia }}</span><br>
                        <span class="title-name">Alcaldía o municipio: </span> <span>{{ $empleado->delegacion }}</span><br>
                        <span class="title-name">Estado: </span> <span>{{ $empleado->estado }}</span><br>
                        <span class="title-name">Código postal: </span> <span>{{ $empleado->cp }}</span><br>

                    </div>

                    <div class="col-md-4">
                        <span class="title-name">Correo electrónico: </span> <span>{{ $empleado->correo }}</span><br>
                        <span class="title-name">Teléfono casa: </span> <span>{{ $empleado->telefono_casa }}</span><br>
                        <span class="title-name">Teléfono movil: </span> <span>{{ $empleado->telefono_movil }}</span><br>
                        <span class="title-name">Estado civil: </span> <span>{{ $empleado->estado_civil }}</span><br>
                        <span class="title-name">Escolaridad: </span> <span>{{ $empleado->escolaridad }}</span><br>
                        <span class="title-name">Profesión: </span> <span>{{ $empleado->profesion }}</span><br>
                    </div>

                    <div class="col-md-4">
                        <span class="title-name">En caso de accidente avisar a: </span> <span>{{ $empleado->avisar_a }}</span><br>
                        <span class="title-name">Telefono: </span> <span>{{ $empleado->avisar_a_telefono }}</span><br>
                        <span class="title-name">Beneficiario: </span> <span>{{ $empleado->beneficiario }}</span><br>
                        <span class="title-name">Parentesco: </span>  <span>{{ $empleado->avisar_a_parentesco }}</span><br>
                    </div>
                </div>

                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Infonavit/Fonacot</label>
                    </div>
                </div>


{{-- PASO 2 --}}<div class="row">
                    <div class="col-md-4">

                        <span class="title-name">¿Cuenta con crédito INFONAVIT?: </span>  <span>{{(!empty($empleado->num_credito_infonavit)) ? 'SI' : 'NO'}}</span><br>


                            <div id="infonavit_div"  class="{{(empty($empleado->num_credito_infonavit)) ? 'oculto' : ''}}">
                                <span class="title-name">Num.Crédito INFONAVIT: </span>  <span>{{ $empleado->num_credito_infonavit }}</span><br>
                                <span class="title-name">Tipo de descuento: </span>  <span>{{ $empleado->tipo_descuento }}</span><br>
                                <span class="title-name">Valor descuento: </span>  <span>{{ $empleado->valor_descuento }}</span><br>
                            </div>

                    </div>

                    <div class="col-md-4">
                        <span class="title-name">¿Cuenta con crédito FONACOT?: </span>  <span>{{(!empty($empleado->num_credito_fonacot)) ? 'SI' : 'NO'}}</span><br>


                        <div id="fonacot_div" class="{{(empty($empleado->num_credito_fonacot)) ? 'oculto' : ''}}">

                            <span class="title-name">Num.Crédito FONACOT: </span>  <span>{{ $empleado->num_credito_fonacot }}</span><br>
                            <span class="title-name">Valor: </span>  <span>{{ $empleado->valor_fonacot }}</span><br>
                        </div>

                    </div>
                    <div class="col-md-4"></div>
                </div>

                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Parametros</label>
                    </div>
                </div>

                    <div>
                        <label class="mb-0">Tipo Nomina Empleado</label>
                    </div>

                <div class="row">
                    <div class="col-md-4">
                        <span class="title-name">Sindical: </span>  <span>{{($empleado->tipo_sindical == 1) ? 'SI' : 'NO'}}</span><br>

                    </div>
                    <div class="col-md-4">
                         <span class="title-name">Fiscal: </span>  <span>{{($empleado->tipo_fiscal == 1) ? 'SI' : 'NO'}}</span><br>

                    </div>
                    <div class="col-md-4"></div>
                </div>

                <br>
{{--                 <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Expediente</label>
                    </div>
                </div> --}}

{{--                 <div class="row">
                    <div class="col-md-4">

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_ine" id="ine" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Identificación oficial vigente</label>
                        </div>

                         <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_curp" id="curp" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">CURP</label>
                        </div>

                         <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_comprobante" id="comprobante" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Comprobante de domicilio</label>
                        </div>

                         <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_contrato" id="contrato" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Contrato</label>
                        </div>

                         <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_curriculum" id="curriculum" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Curriculum</label>
                        </div>


                    </div>
                    <div class="col-md-4">
                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_fotografica" id="foto" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Fotografica</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_nss" id="nss" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">NSS</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_aviso" id="infonavit_file" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Aviso de retenciones infonavit</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_analisis" id="analisis" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Análisis</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_fiel_imss" id="imss" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Afil IMSS</label>
                        </div>

                    </div>
                    <div class="col-md-4">

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_nacimiento" id="nacimiento" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Acta de nacimiento</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_rfc" id="rfc" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">RFC</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_estado_cuenta" id="cuenta" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Estado de cuenta</label>
                        </div>

                        <div class="custom-file center mb-2">
                            <input type="file" class="custom-file-input" name="file_fonacot" id="fonacot_file" accept=".pdf, .png, .jpg, .doc, .docx">
                            <label class="custom-file-label" for="ine">Fonacot</label>
                        </div>


                    </div>
                </div> --}}

{{--                 <br>
                <div class="row">
                    <div class="col-md-12">
                        <label class="font-size-1-3em mb-5 under-line">Biométricos</label>
                    </div>
                </div> --}}

{{--                 <div class="row">
                    <div class="col-md-4" style="margin-top: 5px;">
                        <div class="card">
                            <div class="card-header">
                                Biométricos
                                <a class="btn btn-warning btn-sm float-right font-weight-bold" href="#" role="button"
                                    data-toggle="modal" data-target="#modalAsignar">Asignar</a>
                            </div>
                            
                            <ul class="list-group list-group-flush">
                                @foreach ( $asignados as $asignacion)
                                @foreach ( $biometricos as $bio)
                                @if ($asignacion->id_biometrico == $bio->id)
                                <li class="list-group-item">
                                    {{ $bio->nombre }}
                                    <div class="float-right">
                                    <button class="borraBio badge badge-danger" data-id="{{ $bio->id }}" data-ip="{{ $bio->ip }}" data-puerto="{{ $bio->puerto }}">X</button>
                                    </div>
                                </li>
                                @php
                                unset($biometricos[$loop->index]);
                                @endphp
                                @endif
                                @endforeach
                                @endforeach
                            </ul>
                            <div class="card-footer p-1">
                                <a class="btn btn-warning btn-block font-weight-bold" href="#"
                                    role="button">Sincronizar</a>
                            </div>
                        </div>
                        
                            <div class="card mt-2">
                                <div class="card-header">
                                    Rostro
                                    @php
                                        $tipo = 'success';
                                        $bt ="Registrar";                                                    
                                    @endphp
                                    @foreach ($huellas as $h)
                                        @if ($h['indice'] == 11)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                    <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="10">
                                        <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                        {{$bt}}
                                    </button>

                                </div>
                            </div>
                            
                    </div>


                    <div class="col-md-4">
                        <div class="card" style=" margin-top: 5px;">
                            <div class="card-header">
                                Mano Izquierda
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    Pulgar
                                    <div class="float-right">
                                        @php
                                                    $tipo = 'success';
                                                    $bt ="Registrar";                                                    
                                                @endphp
                                        @foreach ($huellas as $h)
                                            @if ($h['indice'] == 4)
                                                @php                                                
                                                    $tipo = 'dark';
                                                    $bt ="Actualizar";
                                                @endphp
                                                @break;
                                            @else
                                                @php
                                                    $tipo = 'success';
                                                    $bt ="Registrar";                                                    
                                                @endphp
                                            @endif
                                        @endforeach
                                        <button  class="btn btn-outline-{{$tipo}} asignarH"  data-id="4">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>

                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Índice
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 3)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="3">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Medio
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 2)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="2">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Anular
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 1)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="1">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Meñique<div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 0)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="0">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card" style="margin-top: 5px;">
                            <div class="card-header">
                                Mano Derecha
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    Pulgar
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 5)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="5">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Índice
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 6)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="6">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Medio
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 7)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="7">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Anular
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 8)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="8">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    Meñique
                                    <div class="float-right">
                                        @foreach ($huellas as $h)
                                        @if ($h['indice'] == 9)
                                            @php                                                
                                                $tipo = 'dark';
                                                $bt ="Actualizar";
                                            @endphp
                                            @break;
                                        @else
                                            @php
                                                $tipo = 'success';
                                                $bt ="Registrar";                                                    
                                            @endphp
                                        @endif
                                    @endforeach
                                        <button class="btn btn-outline-{{$tipo}} asignarH"  data-id="9">
                                            <i class="fas fa-fingerprint fa-x2 text-success"></i>
                                            {{$bt}}
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>


                </div> --}}

            </div>

        </div>
    </form>

{{--         <br>
        <br>
        <input type="button" id="edit_empleado" class="center button-style" value="Guardar"> --}}

    </div>

</div>

 @include('includes.footer')
<script type="text/javascript">
    function infonavit() {

        var val = document.getElementById("infonavit").value;
        
        if(val == 1){ $("#infonavit_div").show(); }else{ $("#infonavit_div").hide(); }
    }

    function fonacot() {
        
        var val = document.getElementById("fonacot").value;
        
        if(val == 1){ $("#fonacot_div").show(); }else{ $("#fonacot_div").hide(); }
    }


    var fileInput = document.querySelector('input[name=identificacion]');
    var filenameContainer = document.querySelector('#filename');
    var dropzone = document.querySelector('div');

    fileInput.addEventListener('change', function() {
        filenameContainer.innerText = fileInput.value.split('\\').pop();
    });

</script>


<script type="text/javascript">
    $(".borraBio").click(function(){

        var bio = $(this).data('id');
        var ip = $(this).data('ip');
        var puerto = $(this).data('puerto');
        var empleado = {{ $empleado->id }};
            

        swal({
            title: "Confirmas eliminar del biométrico",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {

                $.ajax({
                    type: 'GET',
                    url: 'http://wsbiometrics.ddns.net/biometricos/usuario/eliminar',
                    data: {ip: ip,puerto: puerto,id : empleado},
                    dataType: "json",    
                    beforeSend: function() {
                        // $('#spinner').removeClass('ocultar'); // Le quito la clase que oculta mi animación 
                    },            
                }).then( function(resp){  
                
                    datos = (resp.respuesta)?JSON.parse(resp.respuesta):"";                   
                    if(resp.error && datos == ""){
                        err=resp.error;
                        // alertify.error(resp.error);
                        swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                    }else{
                        //ToDo Eliminar asignacion de la base
                        $.ajax({
                        type: 'DELETE',
                        url: '{{ route('biometrico.asignar') }}'+'/'+empleado,
                        data: {id_empleado : empleado,id_biometrico : bio ,  _token: '{{csrf_token()}}'},
                        dataType: "json",    
                        }).then( function(resp2){             
                             console.log(resp2);
                             datos2 = (resp2.respuesta)?JSON.parse(resp2.respuesta):"";
                            if(resp2.error && datos2 == ""){
                                // alertify.error(resp2.error);
                                swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                            }else{
                                
                                swal("El biométrico se elimino correctamente", "success");
                            }
                        }).fail(function(ee) {
                            // alertify.error(ee);
                            swal("Algo salio mal", "Vuelve a intentarlo", "warning");
                        });                        
                    }
                }).fail(function(e) {
                    swal("Algo salio mal", "Vuelve a intentarlo", "warning");                    
                }).always(function(resp){
                    // $('#spinner').addClass('ocultar');
                    // location.reload();
                });



            } else {
                swal("La acción fue cancelada");
            }
        });
    });


    var huella;
    $('.asignarH').click(function(){
        huella = $(this).data('id');
        console.log('Huella seleccionada:' + huella);
        $("#modalRegistrarH").modal('show');

    });


   $("#edit_empleado").click(function(){
        var nombre = document.getElementById("nombre").value;

        if(nombre== ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          }); 
          setTimeout(submitForm, 1500);     
        }
    });

    function submitForm() { document.getElementById("submit_empleado").submit() }

</script>