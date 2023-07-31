<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@include('empleados.biometrico.asignar')
@include('empleados.biometrico.registrar-huella')

<style type="text/css">
    .oculto {
        display: none;
    }

    label {
        font-weight: bold;
        margin-top: 15px;
    }

    .bg-gray {
        background-color: #fbba00 solid;
    }

    .file {
        box-shadow: 0px 0px 0px 2px #dadada;
        cursor: pointer;
        width: 47%;
    }

    .file label {
        cursor: pointer;
    }

    .file input[type=file] {
        width: 1px;
    }

    nav a {
        color: black;
    }

    nav a:hover {
        color: #fbba00;
    }

    .btn-outline-success {
        color: #28a745;
        border-color: white !important;
    }

    .article-nav {
        width: 100%;
        height: auto;
        float: left;
        box-sizing: border-box;
        background-color: #fff;
    }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00;
        color: gray;
    }

    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        color: black;
        font-weight: bold;
    }

    .nav-link {
        display: block;
        padding: 0.5rem 1.8rem !important;
    }

    .input-style {
        width: 260px !important;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(74%) sepia(11%) saturate(6958%) hue-rotate(2deg) brightness(104%) contrast(104%);
    }

    /* SPINNER */

    .spinner.ocultar {
        display: none;
    }

    .spinner {
        display: inline-block;
    }

    .spinner:after {
        content: url("{{asset('/img/spinner.gif')}}");
        width: 150px;
        display: block;
        margin: 15% 40%;
    }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, .8);
        z-index: 1600;
        opacity: 1;
        transition: all 0.5s;
    }

</style>

<div class="container">

    @include('includes.header',['title'=>'Editar empleado',
            'subtitle'=>'Empleados', 'img'=>'/img/editar-empleado.png',
            'route'=>'empleados.empleados'])


    <div class="row">
        <div class="col-md-12 text-center mt-4">
            <div class="article-nav border">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="generles-tab" data-toggle="tab" href="#generles"
                           role="tab" aria-controls="generles" aria-selected="true">Datos generales</a>

                        <a class="nav-item nav-link" id="salario-tab" data-toggle="tab" href="#salario" role="tab"
                           aria-controls="salario" aria-selected="false">Salario</a>
                        <a class="nav-item nav-link" id="personales-tab" data-toggle="tab" href="#personales" role="tab"
                           aria-controls="personales" aria-selected="false">Datos personales</a>

                        @if (Session::get('empresa')['id'] != 111)
                            {{--JEDISAM --}}
                            <a class="nav-item nav-link" id="infonavit-tab" data-toggle="tab" href="#infonavit"
                               role="tab" aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
                        @endif

                        <a class="nav-item nav-link" id="params-tab" data-toggle="tab" href="#params" role="tab"
                           aria-controls="params" aria-selected="false">Parámetros</a>

                        <a class="nav-item nav-link" id="expediente-tab" data-toggle="tab" href="#expediente" role="tab"
                           aria-controls="expediente" aria-selected="false">Expediente</a>

                        {{-- @if ((Session::get('empresa.parametros')[0]['biometrico']) == '1') --}}
                        <a class="nav-item nav-link" id="biometrico-tab" data-toggle="tab" href="#biometrico" role="tab"
                           aria-controls="biometrico" aria-selected="false">Biométrico</a>
                        {{-- @endif --}}

                    </div>
                </nav>
            </div>
        </div>
    </div>


    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif

    @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
    @endif


    <div class="row">
        <div class="col-md-12 mt-4">


            <div class="tab-content " id="nav-tabContent">
                <form action="{{route('empleados.actualizarempleado')}}" method="post" id="empleado_datos">
                    @csrf
                    <input type="hidden" name="id" value="{{$empleado->id}}">
                    <input type="hidden" name="estatus" value="1">

                    <div class="tab-pane fade show active " id="generles" role="tabpanel"
                         aria-labelledby="generles-tab">
                        <div class="row">
                            <div class="col-md-2 mt-1">
                                <div class="article border">
                                    <div class="col-md-12 text-center mt-5">
                                        <img src="{{asset($empleado->avatar)}}" alt=""
                                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5"><br>
                                        @if($empleado->qr)
                                            <h5><strong>Código VCARD</strong></h5>

                                            <img src="{{asset($empleado->qr)}}" alt=""
                                                 class="fotografia img-thumbnail img-fluid mb-5">

                                        @endif
                                        @if ($btns)
                                            <button type="submit" class="center button-style">Guardar</button>

                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10 mt-1">
                                <div class="article border">
                                    <div class="row">

                                        <div class="col-md-4">

                                            <tr>
                                                <td>
                                                    <label for="numero_empleado" class="mb-0"># Empleado</label>
                                                    <input type="number" name="numero_empleado" id="numero_empleado"
                                                           value="{{($empleado->numero_empleado == null && Session::get('empresa.parametros')[0]['autonumerar_empleado'] == 1) ? $empleado->id : $empleado->numero_empleado}}"
                                                           class="form-control input-style-custom mb-2" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <label class="mb-0">Nombre:</label>
                                                <input type="text" name="nombre" id="nombre"
                                                       value="{{$empleado->nombre}}"
                                                       class="form-control input-style-custom mayusculas mb-2" required>
                                            </tr>
                                            <tr>
                                                <label class="mb-0">Apellido paterno:</label>
                                                <input type="text" name="apaterno" id="apaterno"
                                                       value="{{$empleado->apaterno}}"
                                                       class="form-control input-style-custom mb-2 mayusculas" required>
                                            </tr>
                                            <tr>
                                                <label class="mb-0">Apellido Materno:</label>
                                                <input type="text" name="amaterno" id="amaterno"
                                                       value="{{$empleado->amaterno}}"
                                                       class="form-control input-style-custom mb-2 mayusculas" required>
                                            </tr>
                                            <tr>
                                                <label class="mb-0">RFC:</label>
                                                <input type="text" name="rfc" id="rfc" value="{{$empleado->rfc}}"
                                                       class="form-control input-style-custom mb-2 mayusculas"
                                                       minlength="11" maxlength="16">
                                            </tr>
                                            <tr>
                                                <label class="mb-0">CURP:</label>
                                                <input type="text" name="curp" id="curp" value="{{$empleado->curp}}"
                                                       class="form-control input-style-custom mb-2 mayusculas"
                                                       minlength="18" maxlength="18">
                                            </tr>

                                        </div>

                                        <div class="col-md-4">

                                            <tr>
                                                <td>
                                                    <label class="mb-0">Fecha de Nacimiento:</label>
                                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                                           value="{{$empleado->fecha_nacimiento}}"
                                                           class="form-control input-style-custom mb-2" required>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="mb-0">Fecha de Alta:</label>
                                                    <input type="date" name="fecha_alta" id="fecha_alta"
                                                           value="{{$empleado->fecha_alta}}"
                                                           class="form-control input-style-custom mb-2" required>
                                                </td>
                                            </tr>
                                            <div class="mt-2"></div>
                                            <tr>
                                                <td>
                                                    <label class="mb-0">Lugar de Nacimiento:</label>
                                                    <select name="lugar_nacimiento" id="lugar_nacimiento"
                                                            class="form-control input-style-custom mb-3 select-clase"
                                                            required style="">
                                                        <option selected
                                                                value="{{$empleado->lugar_nacimiento}}">{{$empleado->lugar_nacimiento}}
                                                        </option>
                                                        <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                                        <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA</OPTION>
                                                        <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR</OPTION>
                                                        <OPTION VALUE="CAMPECHE">CAMPECHE</OPTION>
                                                        <OPTION VALUE="CHIAPAS">CHIAPAS</OPTION>
                                                        <OPTION VALUE="CHIHUAHUA">CHIHUAHUA</OPTION>
                                                        <OPTION VALUE="COAHUILA">COAHUILA</OPTION>
                                                        <OPTION VALUE="COLIMA">COLIMA</OPTION>
                                                        <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                                        <OPTION VALUE="DURANGO">DURANGO</OPTION>
                                                        <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO</OPTION>
                                                        <OPTION VALUE="GUANAJUATO">GUANAJUATO</OPTION>
                                                        <OPTION VALUE="GUERRERO">GUERRERO</OPTION>
                                                        <OPTION VALUE="HIDALGO">HIDALGO</OPTION>
                                                        <OPTION VALUE="JALISCO">JALISCO</OPTION>
                                                        <OPTION VALUE="MICHOACÁN">MICHOACÁN</OPTION>
                                                        <OPTION VALUE="MORELOS">MORELOS</OPTION>
                                                        <OPTION VALUE="NAYARIT">NAYARIT</OPTION>
                                                        <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN</OPTION>
                                                        <OPTION VALUE="OAXACA">OAXACA</OPTION>
                                                        <OPTION VALUE="PUEBLA">PUEBLA</OPTION>
                                                        <OPTION VALUE="QUERETARO">QUERÉTARO</OPTION>
                                                        <OPTION VALUE="QUINTANA ROO">QUINTANA ROO</OPTION>
                                                        <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ</OPTION>
                                                        <OPTION VALUE="SINALOA">SINALOA</OPTION>
                                                        <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                                        <OPTION VALUE="TABASCO">TABASCO</OPTION>
                                                        <OPTION VALUE="TAMAULIPAS">TAMAULIPAS</OPTION>
                                                        <OPTION VALUE="TLAXCALA">TLAXCALA</OPTION>
                                                        <OPTION VALUE="VERACRUZ">VERACRUZ</OPTION>
                                                        <OPTION VALUE="YUCATÁN">YUCATÁN</OPTION>
                                                        <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                                        <OPTION VALUE="EXTRANJERO">EXTRANJERO</OPTION>
                                                    </select>
                                                </td>
                                            </tr>
                                            <div class="mt-2"></div>
                                            <tr>
                                                <td>
                                                    <label class="mb-0">Prestaciones:</label>
                                                    <select name="id_categoria" id="id_categoria"
                                                            class="form-control input-style-custom mb-3 select-clase"
                                                            required>
                                                        @foreach ($categorias as $categoria)
                                                            <option value="{{$categoria->id}}"
                                                                    {{($categoria->id == $empleado->id_categoria) ? 'selected' : ''}}>
                                                                {{$categoria->nombre}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>


                                            <div class="mt-2"></div>
                                            <label class="mb-0">Genero:</label>
                                            <select name="genero" id="genero"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                <option value="M" {{('M' == $empleado->genero) ? 'selected' : ''}}>
                                                    MASCULINO
                                                </option>
                                                <option value="F" {{('F' == $empleado->genero) ? 'selected' : ''}}>
                                                    FEMENINO
                                                </option>
                                            </select>

                                            <div class="mt-2"></div>
                                            <label class="mb-0">Num Seguro Social:</label>
                                            <input type="number" name="nss" id="nss" value="{{$empleado->nss}}"
                                                   class="form-control input-style-custom mb-2" maxlength="13"
                                                   minlength="9">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="mb-0">Puesto:</label>
                                            <select name="id_puesto" id="id_puesto"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                @foreach ($puestos as $puesto)
                                                    <option value="{{$puesto->id}}"
                                                            {{($puesto->id == $empleado->id_puesto) ? 'selected' : ''}}>
                                                        {{$puesto->puesto}}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($lleva_puestos_reales =="1")
                                                <label class="mb-0">Alias:</label>
                                                <select name="id_alias" id="id_alias"
                                                        class="form-control input-style-custom mb-2 select-clase">
                                                    @if(!empty($select_alias))
                                                        <option value="{{$select_alias['id']}}"
                                                                selected> {{$select_alias['alias']}}</option>;
                                                    @endif

                                                </select>
                                            @endif
                                            <label>Jefe inmediato</label>
                                            <select name="jefe_inmediato" id="jefe_inmediato"
                                                    class="form-control input-style-custom mb-2 select-clase">
                                                <option value="{{$jefeInmediato[0]['id']}}">{{$jefeInmediato[0]['nombre']}} </option>
                                            </select>
                                            <div class="mt-2"></div>
                                            <label
                                                    class="mb-0">{{(Session::get('empresa')['id'] == 111)? 'Sucursal' : 'Departamento'}}
                                                :</label>
                                            <select name="id_departamento" id="id_departamento"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                @foreach ($departamentos as $departamento)
                                                    <option value="{{$departamento->id}}"
                                                            {{($departamento->id == $empleado->id_departamento) ? 'selected' : ''}}>
                                                        {{$departamento->nombre}}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(Session::get('empresa')['sede'] ==1)
                                                <label class="mb-0">Sedes:</label>
                                                <select name="sede" id="sede"
                                                        class="form-control input-style-custom mb-2 select-clase"
                                                        required>
                                                    <option value="">Selecciona</option>
                                                    @foreach ($sedes as $sede)
                                                        <option value="{{$sede->id}}" {{($sede->id == $empleado->sede) ? 'selected' : ''}}>
                                                            {{$sede->nombre}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Centro de Trabajo:</label>
                                            <select name="ubicacion" id="ubicacion"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                <option selected
                                                        value="{{$empleado->ubicacion}}">{{$empleado->ubicacion}}</option>
                                                <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                                <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA</OPTION>
                                                <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR</OPTION>
                                                <OPTION VALUE="CAMPECHE">CAMPECHE</OPTION>
                                                <OPTION VALUE="CHIAPAS">CHIAPAS</OPTION>
                                                <OPTION VALUE="CHIHUAHUA">CHIHUAHUA</OPTION>
                                                <OPTION VALUE="COAHUILA">COAHUILA</OPTION>
                                                <OPTION VALUE="COLIMA">COLIMA</OPTION>
                                                <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                                <OPTION VALUE="DURANGO">DURANGO</OPTION>
                                                <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO</OPTION>
                                                <OPTION VALUE="GUANAJUATO">GUANAJUATO</OPTION>
                                                <OPTION VALUE="GUERRERO">GUERRERO</OPTION>
                                                <OPTION VALUE="HIDALGO">HIDALGO</OPTION>
                                                <OPTION VALUE="JALISCO">JALISCO</OPTION>
                                                <OPTION VALUE="MICHOACÁN">MICHOACÁN</OPTION>
                                                <OPTION VALUE="MORELOS">MORELOS</OPTION>
                                                <OPTION VALUE="NAYARIT">NAYARIT</OPTION>
                                                <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN</OPTION>
                                                <OPTION VALUE="OAXACA">OAXACA</OPTION>
                                                <OPTION VALUE="PUEBLA">PUEBLA</OPTION>
                                                <OPTION VALUE="QUERETARO">QUERÉTARO</OPTION>
                                                <OPTION VALUE="QUINTANA ROO">QUINTANA ROO</OPTION>
                                                <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ</OPTION>
                                                <OPTION VALUE="SINALOA">SINALOA</OPTION>
                                                <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                                <OPTION VALUE="TABASCO">TABASCO</OPTION>
                                                <OPTION VALUE="TAMAULIPAS">TAMAULIPAS</OPTION>
                                                <OPTION VALUE="TLAXCALA">TLAXCALA</OPTION>
                                                <OPTION VALUE="VERACRUZ">VERACRUZ</OPTION>
                                                <OPTION VALUE="YUCATÁN">YUCATÁN</OPTION>
                                                <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                            </select>
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Tipo de Jornada:</label>
                                            <select name="tipo_jornada" id="tipo_jornada"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                <option value="01" {{('01' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    DIURNA
                                                </option>
                                                <option value="02" {{('02' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    NOCTURNA
                                                </option>
                                                <option value="03" {{('03' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    MIXTA
                                                </option>
                                                <option value="04" {{('04' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    POR HORA
                                                </option>
                                                <option value="05" {{('05' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    REDUCIDA
                                                </option>
                                                <option value="06" {{('06' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    CONTINUADA
                                                </option>
                                                <option value="07" {{('07' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    PARTIDA
                                                </option>
                                                <option value="08" {{('08' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    POR TURNOS
                                                </option>
                                                <option value="99" {{('99' == $empleado->tipo_jornada) ? 'selected' : ''}}>
                                                    OTRA JORNADA
                                                </option>

                                            </select>
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Tipo de Contrato:</label>
                                            <select name="tipo_contrato" id="tipo_contrato"
                                                    class="form-control input-style-custom mb-2 select-clase" required>
                                                <option value="01" {{('01' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    CONTRATO DE
                                                    TRABAJO POR TIEMPO INDETEMINADO
                                                </option>
                                                <option value="02" {{('02' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    CONTRATO DE
                                                    TRABAJO POR OBRA DETERMINADA
                                                </option>
                                                <option value="03" {{('03' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    CONTRATO DE
                                                    TRABAJO POR TIEMPO DETERMINADO
                                                </option>
                                                <option value="04" {{('04' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    CONTRATO DE
                                                    TRABAJO POR TEMPORADA
                                                </option>
                                                <option value="05" {{('05' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    CONTRATO DE
                                                    TRABAJO SUJETO A PRUEBA
                                                </option>
                                                <option value="06" {{('06' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    Contrato de
                                                    trabajo con capacitación inicial
                                                </option>
                                                <option value="07" {{('07' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    Modalidad de
                                                    contratación por pago de hora laborada
                                                </option>
                                                <option value="08" {{('08' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    Modalidad de
                                                    trabajo por comisión laboral
                                                </option>
                                                <option value="09" {{('09' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    Modalidades
                                                    de contratación donde no existe relación de trabajo
                                                </option>
                                                <option value="10" {{('10' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    JUBILACIÓN,
                                                    PENSIÓN, RETIRO
                                                </option>
                                                <option value="99" {{('99' == $empleado->tipo_contrato) ? 'selected' : ''}}>
                                                    OTRO
                                                    CONTRATO
                                                </option>

                                            </select>
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Horario:</label>
                                            <select name="id_horario" id="id_horario"
                                                    class="form-control input-style-custom mb-2 select-clase">
                                                @foreach ($horarios as $horario)
                                                    <option value="{{$horario->id}}"
                                                            {{($horario->id == $empleado->id_horario) ? 'selected' : ''}}>
                                                        {{$horario->alias}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- dos  --}}
                    <div class="tab-pane fade" id="salario" role="tabpanel" aria-labelledby="salario-tab">
                        <div class="row">
                            <div class="col-md-2 mt-3">
                                <div class="article border">
                                    <div class="col-md-12 text-center mt-5">
                                        <img src="{{asset($empleado->avatar)}}" alt=""
                                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                                        @if($empleado->qr)
                                            <h5><strong>Código VCARD</strong></h5>

                                            <img src="{{asset($empleado->qr)}}" alt=""
                                                 class="fotografia img-thumbnail img-fluid mb-5">
                                        @endif
                                        @if ($btns)
                                            <button type="submit" class="center button-style">Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10 mt-3">
                                <div class="article border">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="mb-0">Tipo de Nomina:</label><br>
                                            <select name="tipo_de_nomina" id="tipo_de_nomina"
                                                    class="input-style mb-2 select-clase">
                                                @foreach ($tipos_nomina as $tn)
                                                    <option value="{{ strtoupper($tn)}}" {{(strtoupper($tn) == strtoupper($empleado->tipo_de_nomina)) ? 'selected' : ''}}>{{ strtoupper($tn)}}</option>
                                                @endforeach

                                            </select>

                                            <div class="mt-2"></div>
                                            <label class="mb-0">Salario Diario:</label> <br>
                                            <input type="number" name="salario_diario" id="salario_diario"
                                                   value="{{$empleado->salario_diario}}" class="input-style mb-2"
                                                   step="0.01" required>

                                            <label class="mb-0">Salario Diario Integrado:</label>
                                            <input type="number" name="salario_diario_integrado"
                                                   id="salario_diario_integrado"
                                                   value="{{$empleado->salario_diario_integrado}}"
                                                   class="input-style mb-2" step="0.01" required>

                                            <label class="mb-0">Sueldo Neto del Periodo:</label>
                                            <input type="number" name="sueldo_neto" id="sueldo_neto"
                                                   value="{{$empleado->sueldo_neto}}"
                                                   class="input-style mb-2" step="0.01" required> <br>

                                            <label class="mb-0">Sueldo Diario Real:</label> <br>
                                            <input type="number" name="salario_digital" id="salario_digital"
                                                   value="{{$empleado->salario_digital}}" class="input-style mb-2"
                                                   step="0.01" required>
                                        </div>


                                        <div class="col-md-4">
                                            <label class="mb-0">Fecha de Antiguedad:</label>
                                            <input type="date" name="fecha_antiguedad" id="fecha_antiguedad"
                                                   value="{{$empleado->fecha_antiguedad}}" class="input-style mb-2"
                                                   required>

                                            <label class="mb-0">Días de Vacaciones:</label>
                                            <input type="number" name="dias_vacaciones" id="dias_vacaciones"
                                                   value="{{$empleado->dias_vacaciones}}" class="input-style mb-2"
                                                   disabled> <br>

                                            <label class="mb-0">Días de Aguinaldo:</label> <br>
                                            <input type="number" name="dias_aguinaldo" id="dias_aguinaldo"
                                                   value="{{$empleado->dias_aguinaldo}}" class="input-style mb-2"
                                                   disabled>

                                            <label class="mb-0">% de Prima vacacional:</label>
                                            <input type="number" name="porcentaje_prima" id="porcentaje_prima"
                                                   value="{{$empleado->porcentaje_prima}}" class="input-style mb-2"
                                                   disabled>

                                            @php
                                                $date = Carbon\Carbon::parse($empleado->fecha_antiguedad);
                                                $now = Carbon\Carbon::now();
                                                $diff = $date->diffInYears($now);
                                            @endphp
                                            <br>

                                            <label class="mb-0">Empresa Emisora:</label> <br>
                                            <input type="text" name="empresa_emisora" id="empresa_emisora"
                                                   value="{{@$empresa_emisora[0]->razon_social}}"
                                                   class="input-style mb-2" disabled>
                                        </div>


                                        <div class="col-md-4">

                                            <label class="mb-0">Tipo Salario:</label> <br>
                                            <select name="tipo_salario" class="input-style mb-2 select-clase" required>
                                                <option {{($empleado->tipo_salario == 'FIJO') ? 'selected' : '' }}>
                                                    FIJO
                                                </option>
                                                <option {{($empleado->tipo_salario == 'MIXTO') ? 'selected' : '' }}>
                                                    MIXTO
                                                </option>
                                                <option {{($empleado->tipo_salario == 'VARIABLE') ? 'selected' : '' }}>
                                                    VARIABLE
                                                </option>
                                            </select>
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Clabe Interbancaria:</label>
                                            <input type="number" name="clabe_interbancaria" id="clabe_interbancaria"
                                                   value="{{$empleado->clabe_interbancaria}}" class="input-style mb-2">
                                            <br>

                                            <label class="mb-0">Banco:</label> <br>
                                            <select name="id_banco" id="id_banco" class="input-style mb-2 select-clase"
                                                    required>
                                                @foreach ($bancos as $banco)
                                                    <option value="{{ $banco->id}}"
                                                            {{($banco->id == $empleado->id_banco) ? 'selected' : ''}}>
                                                        {{ strtoupper($banco->nombre)}}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2"></div>
                                            <label class="mb-0">Cuenta Banco:</label> <br>
                                            <input type="number" name="cuenta_bancaria" id="cuenta_bancaria"
                                                   value="{{$empleado->cuenta_bancaria}}" class="input-style mb-2"> <br>

                                            <label class="mb-0">Tipo Cuenta:</label> <br>
                                            <select name="tipo_cuenta" id="tipo_cuenta"
                                                    class="input-style mb-2 select-clase">
                                                <option value="01" {{($empleado->tipo_cuenta == '01') ? 'selected' : ''}}>
                                                    CHEQUES
                                                </option>
                                                <option value="03" {{($empleado->tipo_cuenta == '03') ? 'selected' : ''}}>
                                                    TARJETA DE
                                                    DÉBITO
                                                </option>
                                                <option value="40" {{($empleado->tipo_cuenta == '40') ? 'selected' : ''}}>
                                                    CLABE
                                                </option>
                                            </select>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{--  3  --}}
                    <div class="tab-pane fade" id="personales" role="tabpanel" aria-labelledby="personales-tab">

                        <div class="row">
                            <div class="col-md-2 mt-3">
                                <div class="article border">
                                    <div class="col-md-12 text-center mt-5">
                                        <img src="{{asset($empleado->avatar)}}" alt=""
                                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                                        @if($empleado->qr)
                                            <h5><strong>Código VCARD</strong></h5>
                                            <img src="{{asset($empleado->qr)}}" alt=""
                                                 class="fotografia img-thumbnail img-fluid mb-5">
                                        @endif
                                        @if ($btns)
                                            <button type="submit" class="center button-style">Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10 mt-3">
                                <div class="article border">
                                    <div class="row">
                                        <div class="col-md-4">

                                            <label class="mb-0">Nacionalidad:</label> <br>
                                            <input type="text" name="nacionalidad" id="nacionalidad"
                                                   value="{{$empleado->nacionalidad}}"
                                                   class="input-style mb-2 mayusculas" required>

                                            <label class="mb-0">Calle y Num Ext e Int:</label>
                                            <input type="text" name="calle_numero" id="calle_numero"
                                                   value="{{$empleado->calle_numero}}"
                                                   class="input-style mb-2 mayusculas"><br>

                                            <label class="mb-0">Colonia:</label> <br>
                                            <input type="text" name="colonia" id="colonia"
                                                   value="{{$empleado->colonia}}"
                                                   class="input-style mb-2 mayusculas" required>

                                            <label class="mb-0">Alcaldía o Municipio:</label>
                                            <input type="text" name="delegacion" id="delegacion"
                                                   value="{{$empleado->delegacion}}"
                                                   class="input-style mb-2 mayusculas" required><br>

                                            <label class="mb-0">Estado:</label><br>
                                            <select name="estado" id="estado" class="input-style mb-2 select-clase"
                                                    required>
                                                <option selected
                                                        value="{{$empleado->estado}}">{{$empleado->estado}}</option>
                                                <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                                <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA</OPTION>
                                                <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR</OPTION>
                                                <OPTION VALUE="CAMPECHE">CAMPECHE</OPTION>
                                                <OPTION VALUE="CHIAPAS">CHIAPAS</OPTION>
                                                <OPTION VALUE="CHIHUAHUA">CHIHUAHUA</OPTION>
                                                <OPTION VALUE="COAHUILA">COAHUILA</OPTION>
                                                <OPTION VALUE="COLIMA">COLIMA</OPTION>
                                                <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                                <OPTION VALUE="DURANGO">DURANGO</OPTION>
                                                <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO</OPTION>
                                                <OPTION VALUE="GUANAJUATO">GUANAJUATO</OPTION>
                                                <OPTION VALUE="GUERRERO">GUERRERO</OPTION>
                                                <OPTION VALUE="HIDALGO">HIDALGO</OPTION>
                                                <OPTION VALUE="JALISCO">JALISCO</OPTION>
                                                <OPTION VALUE="MICHOACÁN">MICHOACÁN</OPTION>
                                                <OPTION VALUE="MORELOS">MORELOS</OPTION>
                                                <OPTION VALUE="NAYARIT">NAYARIT</OPTION>
                                                <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN</OPTION>
                                                <OPTION VALUE="OAXACA">OAXACA</OPTION>
                                                <OPTION VALUE="PUEBLA">PUEBLA</OPTION>
                                                <OPTION VALUE="QUERETARO">QUERÉTARO</OPTION>
                                                <OPTION VALUE="QUINTANAROO">QUINTANA ROO</OPTION>
                                                <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ</OPTION>
                                                <OPTION VALUE="SINALOA">SINALOA</OPTION>
                                                <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                                <OPTION VALUE="TABASCO">TABASCO</OPTION>
                                                <OPTION VALUE="TAMAULIPAS">TAMAULIPAS</OPTION>
                                                <OPTION VALUE="TLAXCALA">TLAXCALA</OPTION>
                                                <OPTION VALUE="VERACRUZ">VERACRUZ</OPTION>
                                                <OPTION VALUE="YUCATÁN">YUCATÁN</OPTION>
                                                <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                            </select><br>

                                            <label class="mb-0">Código Postal:</label> <br>
                                            <input type="number" name="cp" id="cp" value="{{$empleado->cp}}"
                                                   class="input-style mb-2"
                                                   maxlength="5" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="mb-0">Correo Electrónico:</label><br>
                                            <input type="email" name="correo" id="correo" value="{{$empleado->correo}}"
                                                   class="input-style mb-2" required><br>

                                            <label class="mb-0">Teléfono Casa:</label><br>
                                            <input type="tel" name="telefono_casa" id="telefono_casa"
                                                   value="{{$empleado->telefono_casa}}" class="input-style mb-2"
                                                   required><br>

                                            <label class="mb-0">Teléfono Movil:</label><br>
                                            <input type="tel" name="telefono_movil" id="telefono_movil"
                                                   value="{{$empleado->telefono_movil}}" class="input-style mb-2"
                                                   required><br>


                                            <label class="mb-0">Estado Civil:</label><br>
                                            <select name="estado_civil" id="estado_civil"
                                                    class="input-style mb-2 select-clase">
                                                    <option value="SOLTERO(A)"  {{($empleado->estado_civil == "SOLTERO" || $empleado->estado_civil == "SOLTERO" || $empleado->estado_civil == "SOLTERO(A)") ? 'selected' : ''}}>SOLTERO(A)</option>
                                                    <option value="CASADO(A)"  {{($empleado->estado_civil == "CASADO" || $empleado->estado_civil == "CASADA" || $empleado->estado_civil == "CASADO(A)") ? 'selected' : ''}} >CASADO(A)</option>
                                                    <option value="UNION LIBRE" {{($empleado->estado_civil == "UNION LIBRE") ? 'selected' : ''}}>UNION LIBRE</option>
                                                    <option value="DIVORCIADO" {{($empleado->estado_civil == "DIVORCIADO(A)") ? 'selected' : ''}}>DIVORCIADO(A)</option>
                                            </select><br>

                                            <label class="mb-0">Escolaridad:</label><br>
                                            <input type="text" name="escolaridad" id="escolaridad"
                                                   value="{{$empleado->escolaridad}}"
                                                   class="input-style mb-2"><br>

                                            <label class="mb-0">Profesión:</label><br>
                                            <input type="text" name="profesion" id="profesion"
                                                   value="{{$empleado->profesion}}"
                                                   class="input-style mb-2 mayusculas">
                                        </div>


                                        <div class="col-md-4">

                                            <label class="mb-0">En caso de Accidente Avisar a:</label><br>
                                            <input type="text" name="avisar_a" id="avisar_a"
                                                   value="{{$empleado->avisar_a}}"
                                                   class="input-style mb-2"><br>
                                            <input type="text" name="avisar_a_telefono" id="avisar_a_telefono"
                                                   value="{{$empleado->avisar_a_telefono}}"
                                                   class="input-style mb-2"><br>

                                            <label class="mb-0">Beneficiario:</label><br>
                                            <input type="text" name="beneficiario" id="beneficiario"
                                                   value="{{$empleado->beneficiario}}"
                                                   class="input-style mb-2 mayusculas"><br>

                                            <label class="mb-0">Parentesco:</label><br>
                                            <select name="avisar_a_parentesco" id="avisar_a_parentesco"
                                                    class="input-style mb-2 select-clase">
                                                <option value="HERMANO(A)"
                                                        {{($empleado->avisar_a_parentesco == 'HERMANO(A)') ? 'selected' : ''}}>
                                                    HERMANO(A)
                                                </option>
                                                <option value="ESPOSO(A)"
                                                        {{($empleado->avisar_a_parentesco == 'ESPOSO(A)') ? 'selected' : ''}}>
                                                    ESPOSO(A)
                                                </option>
                                                <option value="HIJO(A)"
                                                        {{($empleado->avisar_a_parentesco == 'HIJO(A)') ? 'selected' : ''}}>
                                                    HIJO(A)
                                                </option>
                                                <option value="PADRES"
                                                        {{($empleado->avisar_a_parentesco == 'PADRES') ? 'selected' : ''}}>
                                                    PADRES
                                                </option>
                                                <option value="OTRO" {{($empleado->avisar_a_parentesco == 'OTRO') ? 'selected' : ''}}>
                                                    OTRO
                                                </option>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (Session::get('empresa')['id'] != 111)
                        {{--JEDISAM --}}
                        {{--  4    --}}
                        <div class="tab-pane fade" id="infonavit" role="tabpanel" aria-labelledby="infonavit-tab">
                            <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-2 mt-3">
                                    <div class="article border">
                                        <div class="col-md-12 text-center mt-5">
                                            <img src="{{asset($empleado->avatar)}}" alt=""
                                                 class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                                            @if($empleado->qr)
                                                <h5><strong>Código VCARD</strong></h5>
                                                <img src="{{asset($empleado->qr)}}" alt=""
                                                     class="fotografia img-thumbnail img-fluid mb-5">
                                            @endif
                                            @if ($btns)
                                                <button type="submit" class="center button-style">Guardar</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 mt-3">
                                    <div class="article border">
                                        <div class="row">

                                            <div class="col-md-12 text-center">

                                                <label class="mb-0">¿Cuenta con crédito INFONAVIT?:</label><br>
                                                <select name="infonavit_val" id="infonavit_val" required
                                                        class="input-style mb-2 select-clase">
                                                    <option value="1" {{(!empty($empleado->num_credito_infonavit)) ? 'selected' : ''}}>
                                                        SI
                                                    </option>
                                                    <option value="0" {{(empty($empleado->num_credito_infonavit)) ? 'selected' : ''}}>
                                                        NO
                                                    </option>
                                                </select>

                                                <div class="infonavit {{(empty($empleado->num_credito_infonavit)) ? 'oculto' : ''}}">

                                                    <label class="mb-0">Num.Crédito INFONAVIT:</label><br>
                                                    <input type="text" name="num_credito_infonavit"
                                                           id="num_credito_infonavit"
                                                           class="input-style mb-2"
                                                           value="{{$empleado->num_credito_infonavit}}">

                                                    <label class="mb-0">¿Cuenta con crédito INFONAVIT?:</label>
                                                    <select name="tipo_descuento" id="tipo_descuento"
                                                            class="input-style mb-2 select-clase">
                                                        <option value="">Selecciona una opción</option>
                                                        <option value="POR PORCENTAJE"
                                                                {{($empleado->tipo_descuento == 'POR PORCENTAJE') ? 'selected' : ''}}>
                                                            POR
                                                            PORCENTAJE
                                                        </option>
                                                        <option value="VECES EN SALARIO"
                                                                {{($empleado->tipo_descuento == 'VECES EN SALARIO') ? 'selected' : ''}}>
                                                            VECES EN SALARIO
                                                        </option>
                                                        <option value="CUOTA FIJA"
                                                                {{($empleado->tipo_descuento == 'CUOTA FIJA') ? 'selected' : ''}}>
                                                            CUOTA FIJA
                                                        </option>
                                                    </select><br>

                                                    <label class="mb-0">Valor:</label><br>
                                                    <input type="text" name="valor_descuento" id="valor_descuento"
                                                           class="input-style mb-2"
                                                           value="{{$empleado->valor_descuento}}">
                                                </div>
                                            </div>
                                            <div class="col-md-12 text-center">

                                                <label class="mb-0">¿Cuenta con crédito FONACOT?:</label><br>
                                                <select name="fonacot" id="fonacot" class="input-style mb-2 select-clase">
                                                    <option value="1" {{(!empty($empleado->num_credito_fonacot)) ? 'selected' : ''}}>
                                                        SI
                                                    </option>
                                                    <option value="0" {{(empty($empleado->num_credito_fonacot)) ? 'selected' : ''}}>
                                                        NO
                                                    </option>
                                                </select>

                                                <div class="fonacot {{(empty($empleado->num_credito_fonacot)) ? 'd-none' : ''}}">

                                                    <label class="mb-0">Num.Crédito FONACOT:</label><br>
                                                    <input type="text" name="num_credito_fonacot"
                                                           id="num_credito_fonacot"
                                                           class="input-style mb-2"
                                                           value="{{$empleado->num_credito_fonacot}}"><br>

                                                    <label class="mb-0">Valor:</label><br>
                                                    <input type="text" name="valor_fonacot" id="valor_fonacot"
                                                           class="input-style mb-2"
                                                           value="{{$empleado->valor_fonacot}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                        </div>
                    @endif


                    <div class="tab-pane fade" id="params" role="tabpanel" aria-labelledby="params-tab">
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-2 mt-3">
                                <div class="article border">
                                    <div class="col-md-12 text-center mt-5">
                                        <img src="{{asset($empleado->avatar)}}" alt=""
                                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                                        @if($empleado->qr)
                                            <h5><strong>Código VCARD</strong></h5>
                                            <img src="{{asset($empleado->qr)}}" alt=""
                                                 class="fotografia img-thumbnail img-fluid mb-5">
                                        @endif
                                        @if ($btns)
                                            <button type="submit" class="center button-style">Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 mt-3 text-center">
                                <div class="article border">
                                    <div class="row">

                                        <div class="col-md-12 text-center">

                                            <label class="mb-0 font-weight-bold">Tipo Nomina Empleado</label> <br><br>

                                            <label class="mb-0">Sindical:</label><br>
                                            <select name="tipo_sindical" id="tipo_sindical"
                                                    class="input-style mb-2 select-clase" required>
                                                <option value="1" {{($empleado->tipo_sindical == 1) ? 'selected' : ''}}>
                                                    SI
                                                </option>
                                                <option value="0" {{($empleado->tipo_sindical == 0) ? 'selected' : ''}}>
                                                    NO
                                                </option>
                                            </select><br>


                                            <label class="mb-0">Fiscal:</label><br>
                                            <select name="tipo_fiscal" id="tipo_fiscal"
                                                    class="input-style mb-2 select-clase" required>
                                                <option value="1" {{($empleado->tipo_fiscal == 1) ? 'selected' : ''}}>
                                                    SI
                                                </option>
                                                <option value="0" {{($empleado->tipo_fiscal == 0) ? 'selected' : ''}}>
                                                    NO
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="tab-pane fade" id="biometrico" role="tabpanel" aria-labelledby="biometrico-tab">
                    <div class="row">
                        <div class="col-md-4 mt-3">
                            <div class="article border">
                                <div class="row">
                                    <div class="col-md-8 text-center">
                                        <h5 class="font-weight-bold">Biométricos</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <a class="btn button-style" href="#" role="button"
                                           data-toggle="modal" data-target="#modalAsignar">Asignar</a>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <table class="table">
                                            @foreach ( $asignados as $asignacion)
                                                @foreach ( $biometricos as $bio)
                                                    @if ($asignacion->id_biometrico == $bio->id)
                                                        <tbody>
                                                        <td>
                                                            {{ $bio->nombre }}
                                                            <div class="float-right">
                                                                <a href="#" class="badge badge-danger borraBio"
                                                                   data-id="{{ $bio->id }}" data-ip="{{ $bio->ip }}"
                                                                   data-puerto="{{ $bio->puerto }}">X</a>
                                                            </div>
                                                        </td>
                                                        </tbody>
                                                        @php
                                                            unset($biometricos[$loop->index]);
                                                        @endphp
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6">
                                        <a class="btn button-style center" href="#" role="button">Sincronizar</a>
                                    </div>
                                    <div class="col-md-3"></div>
                                </div>


                                <div class="row mt-3">
                                    <div class="col-md-12 text-center">
                                        <table class="table">
                                            <tbody>
                                            <td>
                                                Rostro
                                            </td>
                                            <td>
                                                @php
                                                    $tipo = 'success';
                                                    $bt ="";
                                                @endphp
                                                @foreach ($huellas as $h)
                                                    @if ($h['indice'] == 11)
                                                        @php
                                                            $tipo = 'dark';
                                                            $bt ="";
                                                        @endphp
                                                        @break;
                                                    @else
                                                        @php
                                                            $tipo = 'success';
                                                            $bt ="";
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="10">
                                                    <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                    {{$bt}}
                                                </a>
                                            </td>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-4 mt-3 text-center">
                            <div class="article border">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h5 class="font-weight-bold">Mano Izquierda</h5>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12 text-center">
                                        <table class="table">
                                            <tbody>
                                            <tr>
                                                <td>Pulgar</td>
                                                <td>
                                                    @php
                                                        $tipo = 'success';
                                                        $bt ="";
                                                    @endphp
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 4)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="4">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Índice</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 3)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="3">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Medio</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 2)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="2">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Anular</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 1)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="1">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Meñique</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 0)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="0">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="article border">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h5 class="font-weight-bold">Mano Derecha</h5>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12 text-center">
                                        <table class="table">
                                            <tbody>
                                            <tr>
                                                <td>Pulgar</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 5)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="5">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Índice</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 6)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="6">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Medio</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 7)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="7">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Anular</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 8)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="8">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Meñique</td>
                                                <td>
                                                    @foreach ($huellas as $h)
                                                        @if ($h['indice'] == 9)
                                                            @php
                                                                $tipo = 'dark';
                                                                $bt ="";
                                                            @endphp
                                                            @break;
                                                        @else
                                                            @php
                                                                $tipo = 'success';
                                                                $bt ="";
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <a href="#" class="btn btn-outline-{{$tipo}} asignarH" data-id="9">
                                                        <i class="fas fa-fingerprint fa-2x text-success"></i>
                                                        {{$bt}}
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('empleados.actualizarempleadofile') }}" method="post"
                      enctype="multipart/form-data"
                      id="archivosForm">
                    @csrf
                    <input type="hidden" name="id_empleado" value="{{$empleado->id}}">
                    <div class="tab-pane fade" id="expediente" role="tabpanel" aria-labelledby="expediente-tab">
                        <div class="row">
                            <div class="col-md-2 mt-3">
                                <div class="article border">
                                    <div class="col-md-12 text-center mt-5">
                                        <img src="{{asset($empleado->avatar)}}" alt=""
                                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                                        @if($empleado->qr)
                                            <h5><strong>Código VCARD</strong></h5>
                                            <img src="{{asset($empleado->qr)}}" alt=""
                                                 class="fotografia img-thumbnail img-fluid mb-5">
                                        @endif
                                        @if ($btns)
                                            <button id="expediente1" type="submit" class="center button-style">Guardar</button>
                                            <script >
                                                $(function() {$(document).on('click', '#expediente1', function () {
                                                $('#expediente-tab').attr('aria-selected',true);
                                                //console.log("prueba1");
                                                })
                                            });
                                            </script>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10 mt-3">
                                <div class="article border">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-md-1"></div>
                                            <div class="col-md-10">
                                                <div class="row">

                                                    @foreach ($archivos as $key => $archivo)

                                                        <div class="file rounded d-flex  align-items-center p-2 mr-3 mb-3">

                                                            <div class="col-md-9">
                                                                <label class="name tooltip_" data-toggle="tooltip"
                                                                       title="{{($empleado->$key) ? "Cambiar" :"Subir"}} archivo"
                                                                       for="{{$key}}"
                                                                       style="font-size: 15px;">{{$archivo}}</label>
                                                                <input type="file" name="{{$key}}" id="{{$key}}"
                                                                       class="invisible"
                                                                       accept=".pdf, .png, .jpg, .doc, .docx">
                                                            </div>
                                                            <div class="col-md-3">
                                                                @if ($empleado->$key)
                                                                    @php
                                                                        $extension = explode('.', $empleado->$key);
                                                                        $extension = end($extension);
                                                                        if($extension == 'doc' || $extension == 'docx')
                                                                            $icon = 'fa-file-word';
                                                                        elseif ($extension == 'pdf')
                                                                            $icon = 'fa-file-pdf';
                                                                        elseif ($extension == 'jpg' || $extension == 'png')
                                                                            $icon = 'fa-file-image';
                                                                        else
                                                                            $icon = 'fa-file-alt';
                                                                    @endphp

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <img src="{{ asset('/img/empleado-documentos.png') }}"
                                                                                 width="30px">
                                                                        </div>
                                                                        <div class="col-md-6">

                                                                            <a href="{{ asset('storage/repositorio/'.Session::get('empresa')['id'].'/'.$nombre.'/'.$empleado->$key) }}"
                                                                               target="_blank" title="Ver archivo"
                                                                               title="Ver archivo">
                                                                                <img src="{{ asset('/img/ver-documentos-empleado.png') }}"
                                                                                     width="30px">
                                                                            </a>&nbsp;&nbsp;


                                                                        </div>
                                                                    </div>

                                                                @else
                                                                    <div class="row">
                                                                        <div class="col-md-12 text-right">
                                                                            <img src="{{ asset('/img/sin-archivo.png') }}"
                                                                                 width="30px">
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                            </div>

                                                        </div>

                                                    @endforeach



                                                    @foreach ($archivos_extras as $archivo)

                                                        @php
                                                            $nombre_campo = $archivo->nombre_campo;
                                                            $camposExtras = collect($empleado->camposExtras)->keyBy('nombre_campo');
                                                        @endphp

                                                        <div class="file rounded d-flex  align-items-center p-2 mr-3 mb-3 {{(isset($camposExtras[$nombre_campo]) && !empty($camposExtras[$nombre_campo]->info)) ? 'bg-warning' : 'bg-gray'}}"
                                                             for="{{$nombre_campo}}">
                                                            @if (isset($camposExtras[$nombre_campo]) && !empty($camposExtras[$nombre_campo]->info))
                                                                @php
                                                                    $extension = explode('.', $camposExtras[$nombre_campo]->info);
                                                                    $extension = end($extension);
                                                                    if($extension == 'doc' || $extension == 'docx')
                                                                    $icon = 'fa-file-word';
                                                                    elseif ($extension == 'pdf')
                                                                    $icon = 'fa-file-pdf';
                                                                    elseif ($extension == 'jpg' || $extension == 'png')
                                                                    $icon = 'fa-file-image';
                                                                    else
                                                                    $icon = 'fa-file-alt';
                                                                @endphp
                                                                <a href="{{asset($camposExtras[$nombre_campo]->info)}}"
                                                                   class="btn btn-warning"
                                                                   target="_blank" title="Ver archivo"
                                                                   title="Ver archivo">
                                                                    <i class="fas {{$icon}} fa-3x icon"></i>
                                                                </a>
                                                            @else
                                                                <i class="fas fa-3x fa-ban text-muted mr-2 icon"></i>
                                                            @endif
                                                            <label class="name tooltip_" data-toggle="tooltip"
                                                                   title="{{(isset($camposExtras[$nombre_campo]) && !empty($camposExtras[$nombre_campo]->info)) ? "Cambiar" :"Subir"}} archivo"
                                                                   for="{{$nombre_campo}}">{{strtoupper($archivo->alias)}}</label>
                                                            @php
                                                                // Si es obligatorio este campo extra
                                                                $requerido = ($archivo->obligatorio) ? true : false;
                                                                // Pero si ya esta lleno, no es obligatorio
                                                                $requerido = ($requerido && (isset($camposExtras[$nombre_campo]) &&
                                                                !empty($camposExtras[$nombre_campo]->info))) ? false : true;
                                                            @endphp
                                                            <input type="file" name="{{$nombre_campo}}"
                                                                   id="{{$nombre_campo}}" class="invisible"
                                                                   accept=".pdf, .png, .jpg, .doc, .docx"
                                                                   {{($requerido) ? 'required' : '' }} onchange="file('{{$nombre_campo}}')">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

            </div>


        </div>
    </div>

</div>
<div id="spinner" class="spinner ocultar overlay"></div>
@include('includes.footer')

<script type="text/javascript">
    if (window.location.hash) {
        $('#nav-tab a[href="' + window.location.hash + '"]').click();
    }

    $(".mayusculas").keyup(function () {
        $(this).val($(this).val().toUpperCase());
    });


    // mostrar el primer tab
    $('.tab-content .tab-pane').hide();
    $('.tab-content .tab-pane:eq(0)').show();

    // Fix para el agrupamiento del <form>
    $('.nav-tabs a.nav-item').click(function () {
        $('.tab-content .tab-pane').hide();
        content = $(this).attr('aria-controls');
        $('.tab-content #' + content).fadeIn();
    });

    $('#infonavit').change(function () {

        var infonavit = document.getElementById("infonavit_val").value;

        if (infonavit == '1') {
            $(".infonavit").show();
        } else {
            $(".infonavit").hide();
        }
    });

    $('#fonacot').change(function () {
        if ($(this).val() == '1') {
            $('.fonacot').removeClass('d-none');
            $('.fonacot input').val('');
        } else {
            $('.fonacot').addClass('d-none');
        }
    });

    $('.files input[type=file]').change(function () {
        file = $(this).val();
        var extension = file.substr((file.lastIndexOf('.') + 1));
        if (extension == 'pdf')
            iconClass = 'fa-file-pdf';
        else if (extension == 'doc' || extension == 'docx')
            iconClass = 'fa-file-word';
        else if (extension == 'jpg' || extension == 'png')
            iconClass = 'fa-file-image';
        else
            iconClass = 'fa-file-alt';

        $(this).parents('.file').find('.icon').removeClass('fa-file-pdf fa-file-word fa-file-image fa-file-alt fa-ban').addClass(iconClass);
    });


    $('.asignarH').click(function () {
        huella = $(this).data('id');
        console.log('Huella seleccionada:' + huella);
        $("#modalRegistrarH").modal('show');
    });

    $(function () {
        $('.select-clase').select2();
    });

    $("#id_puesto").on('change', function () {

        $("#id_alias").empty();
        $.get("{{route('empleados.obtener.alias')}}", {'id': this.value}, function (resp) {
            $("#id_alias").append(`<option value="" selected hidden disabled > Selecciona puesto real(Alias) </option>`);

            resp.map((puesto, i) => {
                $("#id_alias").append(`<option value="${puesto.id_alias}">${puesto.alias}</option>`);

            });

        });
    });




    

</script>