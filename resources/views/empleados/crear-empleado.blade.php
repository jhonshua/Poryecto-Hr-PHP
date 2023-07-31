<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<style type="text/css">
    .file-doc {
        background-color: #fbba00;
        border: 3px #fbba00;
        border-radius: 10px;
    }
    .custom-file-label {
        z-index: 0;
    }

    input[type="date"]::-webkit-calendar-picker-indicator{
        filter: invert(74%) sepia(11%) saturate(6958%) hue-rotate(2deg) brightness(104%) contrast(104%);
    }

    select[class="input-style"]::-webkit-selection{
        filter: invert(74%) sepia(11%) saturate(6958%) hue-rotate(2deg) brightness(104%) contrast(104%);
    }
    .input-style{
        width: 260px !important;
    }

    nav a{
        color:black; }

    nav a:hover{
        color:#fbba00; }

    .btn-outline-success {
        color: #28a745; 
        border-color: white !important;}

    .article-nav {
        width: 100%;
        height: auto;
        float: left;
        box-sizing: border-box;
        background-color: #fff; }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00; 
        color: gray;}
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        color: black;
        font-weight: bold;
    }

    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link {
        display: block;
        padding: 0.5rem 2.5rem !important;
    }
    .select-clase{
        margin-top: 5px;
    }
    .select2-selection{
        text-align: center;
    }
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.disabled {
        color: gray;
        font-weight: bold;
        border-right-color: #fbba00;
    }
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        border-right-color: #fbba00;
    }

   #fecha_nacimiento:not(.has-value):before{
      color: lightgray;
      content: attr(placeholder);
    }

    #fecha_alta:not(.has-value):before{
      color: lightgray;
      content: attr(placeholder);
    }
    .group { 
        position:relative; 
        margin-bottom:30px; 
    }
    .labeldes {
        color:#A8A5A4; 
        font-size:18px;
        font-weight:normal;
        position:absolute;
        pointer-events:none;
        left:5px;
        top:10px;
        transition:0.2s ease all; 
        -moz-transition:0.2s ease all; 
        -webkit-transition:0.2s ease all;
    }
    input:focus ~ label, input:valid ~ label {
        top:-20px;
        font-size:15px;
        color:#3D3B3B;
    }
    select:focus ~ label, select:valid ~ label {
        top:-20px;
        font-size:15px;
        color:#3D3B3B;
    }
</style>


<div class="container">
@include('includes.header',['title'=>'Crear empleado',
        'subtitle'=>'Empleados', 'img'=>'img/header/administracion/icono-usuario.png',
        'route'=>'empleados.empleados'])

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
        <div class="col-md-12 text-center mt-4">
            <div class="article-nav border">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="generles-tab" data-toggle="tab" href="#generles" role="tab"aria-controls="generles" aria-selected="true">Datos generales</a>

                        <a class="nav-item nav-link disabled" id="salario-tab" data-toggle="tab" href="#salario" role="tab" aria-controls="salario" aria-selected="false">Salario</a>
                        <a class="nav-item nav-link disabled" id="personales-tab" data-toggle="tab" href="#personales" role="tab"aria-controls="personales" aria-selected="false">Datos personales</a>

                        @if (Session::get('empresa')['id'] != 111) {{--JEDISAM --}}
                            <a class="nav-item nav-link disabled" id="infonavit-tab" data-toggle="tab" href="#infonavit" role="tab"aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
                        @endif

                        {{-- <a class="nav-item nav-link disabled" id="params-tab" data-toggle="tab" href="#params" role="tab" aria-controls="params" aria-selected="false">Parámetros</a> --}}
                        
                        <a class="nav-item nav-link disabled" id="expediente-tab" data-toggle="tab" href="#expediente" role="tab"aria-controls="expediente" aria-selected="false">Expediente</a>
                        
                        {{-- @if ((Session::get('empresa.parametros')[0]['biometrico']) == '1') --}}
                        <a class="nav-item nav-link disabled" id="biometrico-tab" data-toggle="tab" href="#biometrico" role="tab"aria-controls="biometrico" aria-selected="false">Biométrico</a>
                        {{-- @endif --}}

                    </div>
                </nav>
            </div>
        </div>
    </div>

    @if($empresa == 'empresa000186')
    {{-- {{route('empleados_bck.paso1b.186')}} --}}
        <form action="" method="post" id="empleado_datos">
        @csrf
            <div class="row">
                <div class="col-md-2 mt-3">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-12 mt-3">
                                <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="button" class="center button-style" id="add_pasouno" value="Guardar">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-md-10 mt-3">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-4">
                                
                                <input type="number" name="numero_empleado" id="numero_empleado" value="{{ old('numero_empleado')}}" class="form-control input-style-custom mb-2" placeholder="Empleado">

                                <input type="text" name="nombre" id="nombre" value="{{old('nombre')}}" class="form-control input-style-custom mb-2" placeholder="Nombre" required>

                                <input type="text" name="apaterno" id="apaterno" value="{{old('apaterno')}}" class="form-control input-style-custom mb-2" placeholder="Apellido paterno" required>

                                <input type="text" name="amaterno" id="amaterno" value="{{old('amaterno')}}" class="form-control input-style-custom mb-2" placeholder="Apellido Materno" required>

                                <input type="text" name="rfc" id="rfc" value="{{old('rfc')}}" class="form-control input-style-custom mb-2" minlength="12" maxlength="13" placeholder="RFC"  onkeyup="mayusculas(this);">

                                <input type="text" name="curp" id="curp" value="{{old('curp')}}" class="center input-style mb-2" minlength="18" maxlength="18" placeholder="CURP">
                            </div>

                            <div class="col-md-4">

                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{old('fecha_nacimiento')}}" class="form-control input-style-custom mb-2" placeholder="" required>
                                <label class="labeldes">Fecha de nacimiento</label>   
                                <input type="date" name="fecha_alta" id="fecha_alta" value="{{old('fecha_alta', date('Y-m-d'))}}" class="form-control input-style-custom mb-2" placeholder="" required>
                                <label class="labeldes">Fecha de alta</label>   
                                <label>
                                <select name="lugar_nacimiento" id="lugar_nacimiento" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option selected value="" disabled="">Lugar de Nacimiento</option>
                                    <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA </OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR </OPTION>
                                    <OPTION VALUE="CAMPECHE">CAMPECHE </OPTION>
                                    <OPTION VALUE="CHIAPAS">CHIAPAS </OPTION>
                                    <OPTION VALUE="CHIHUAHUA">CHIHUAHUA </OPTION>
                                    <OPTION VALUE="COAHUILA">COAHUILA </OPTION>
                                    <OPTION VALUE="COLIMA">COLIMA </OPTION>
                                    <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                    <OPTION VALUE="DURANGO">DURANGO </OPTION>
                                    <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO </OPTION>
                                    <OPTION VALUE="GUANAJUATO">GUANAJUATO </OPTION>
                                    <OPTION VALUE="GUERRERO">GUERRERO </OPTION>
                                    <OPTION VALUE="HIDALGO">HIDALGO </OPTION>
                                    <OPTION VALUE="JALISCO">JALISCO </OPTION>
                                    <OPTION VALUE="MICHOACÁN">MICHOACÁN </OPTION>
                                    <OPTION VALUE="MORELOS">MORELOS </OPTION>
                                    <OPTION VALUE="NAYARIT">NAYARIT </OPTION>
                                    <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN </OPTION>
                                    <OPTION VALUE="OAXACA">OAXACA </OPTION>
                                    <OPTION VALUE="PUEBLA">PUEBLA </OPTION>
                                    <OPTION VALUE="QUERETARO">QUERÉTARO </OPTION>
                                    <OPTION VALUE="QUINTANA ROO">QUINTANA ROO </OPTION>
                                    <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ </OPTION>
                                    <OPTION VALUE="SINALOA">SINALOA </OPTION>
                                    <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                    <OPTION VALUE="TABASCO">TABASCO </OPTION>
                                    <OPTION VALUE="TAMAULIPAS">TAMAULIPAS </OPTION>
                                    <OPTION VALUE="TLAXCALA">TLAXCALA </OPTION>
                                    <OPTION VALUE="VERACRUZ">VERACRUZ </OPTION>
                                    <OPTION VALUE="YUCATÁN">YUCATÁN </OPTION>
                                    <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                    <OPTION VALUE="EXTRANJERO">EXTRANJERO</OPTION>
                                </select>
                                </label>
                                <label>h</label>
                                <select name="id_categoria" id="id_categoria" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option vlaue="" disabled selected>Prestaciones</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{$categoria->id}}">
                                            {{$categoria->nombre}}
                                        </option>
                                    @endforeach
                                </select>


                                

                                <select name="genero" id="genero" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option value="" disabled="" selected="">Genero</option>
                                    <option value="M" {{('M' == old('genero')) ? 'selected' : ''}}> MASCULINO</option>
                                    <option value="F" {{('F' == old('genero')) ? 'selected' : ''}}> FEMENINO</option>
                                </select>
                                <div class="mt-2"></div>
                                <input type="number" name="nss" id="nss" value="{{old('nss')}}" class="form-control input-style-custom mb-2" maxlength="13" minlength="9" placeholder="Num Seguro Social">
                            </div>

                            <div class="col-md-4">
                                <select name="id_puesto" id="id_puesto" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option value="" disabled selected >Puesto</option>
                                    @foreach ($puestos as $puesto)
                                        <option value="{{$puesto->id}}" {{($puesto->id == old('id_puesto')) ? 'selected' : ''}}>
                                            {{$puesto->puesto}}
                                        </option>
                                    @endforeach
                                </select>

                                @foreach ($empresa_sede as $empresa)

                                    <select name="id_departamento" id="id_departamento" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option value="" disabled selected>{{($empresa->sede == 111)? 'Sucursal' : 'Departamento'}}</option>
                                        @foreach ($departamentos as $departamento)
                                            <option value="{{$departamento->id}}" {{($departamento->id == old('id_departamento')) ? 'selected' : ''}}>
                                                {{$departamento->nombre}}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('id_departamento','<p class="text-danger">Error: El campo departamento es requerido</p>') !!}


                                    @if($empresa->sede ==1)
                                        <select name="sede" id="sede" class="form-control input-style-custom mb-2 select-clase" required>
                                            <option value="" selected disabled>Sedes</option>
                                            @foreach ($sedes as $sede)
                                                <option value="{{$sede->id}}" {{($sede->id == old('sede')) ? 'selected' : ''}}>
                                                    {{$sede->nombre}}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                @endforeach

                                <select name="ubicacion" id="ubicacion" class="form-control input-style-custom mb-2 select-clase">
                                    <option value="" selected disabled>Centro de Trabajo</option>
                                    <option selected value="{{old('ubicacion')}}">{{old('ubicacion')}}</option>
                                    <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA </OPTION>
                                    <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR </OPTION>
                                    <OPTION VALUE="CAMPECHE">CAMPECHE </OPTION>
                                    <OPTION VALUE="CHIAPAS">CHIAPAS </OPTION>
                                    <OPTION VALUE="CHIHUAHUA">CHIHUAHUA </OPTION>
                                    <OPTION VALUE="COAHUILA">COAHUILA </OPTION>
                                    <OPTION VALUE="COLIMA">COLIMA </OPTION>
                                    <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                    <OPTION VALUE="DURANGO">DURANGO </OPTION>
                                    <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO </OPTION>
                                    <OPTION VALUE="GUANAJUATO">GUANAJUATO </OPTION>
                                    <OPTION VALUE="GUERRERO">GUERRERO </OPTION>
                                    <OPTION VALUE="HIDALGO">HIDALGO </OPTION>
                                    <OPTION VALUE="JALISCO">JALISCO </OPTION>
                                    <OPTION VALUE="MICHOACÁN">MICHOACÁN </OPTION>
                                    <OPTION VALUE="MORELOS">MORELOS </OPTION>
                                    <OPTION VALUE="NAYARIT">NAYARIT </OPTION>
                                    <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN </OPTION>
                                    <OPTION VALUE="OAXACA">OAXACA </OPTION>
                                    <OPTION VALUE="PUEBLA">PUEBLA </OPTION>
                                    <OPTION VALUE="QUERETARO">QUERÉTARO </OPTION>
                                    <OPTION VALUE="QUINTANA ROO">QUINTANA ROO </OPTION>
                                    <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ </OPTION>
                                    <OPTION VALUE="SINALOA">SINALOA </OPTION>
                                    <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                    <OPTION VALUE="TABASCO">TABASCO </OPTION>
                                    <OPTION VALUE="TAMAULIPAS">TAMAULIPAS </OPTION>
                                    <OPTION VALUE="TLAXCALA">TLAXCALA </OPTION>
                                    <OPTION VALUE="VERACRUZ">VERACRUZ </OPTION>
                                    <OPTION VALUE="YUCATÁN">YUCATÁN </OPTION>
                                    <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                </select>

                                <select name="tipo_jornada" id="tipo_jornada" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option value="" selected disabled>Tipo de Jornada</option>
                                    <option value="01" {{('01' == old('tipo_jornada')) ? 'selected' : ''}}> DIURNA</option>
                                    <option value="02" {{('02' == old('tipo_jornada')) ? 'selected' : ''}}> NOCTURNA</option>
                                    <option value="03" {{('03' == old('tipo_jornada')) ? 'selected' : ''}}> MIXTA</option>
                                    <option value="04" {{('04' == old('tipo_jornada')) ? 'selected' : ''}}> POR HORA</option>
                                    <option value="05" {{('05' == old('tipo_jornada')) ? 'selected' : ''}}> REDUCIDA</option>
                                    <option value="06" {{('06' == old('tipo_jornada')) ? 'selected' : ''}}> CONTINUADA</option>
                                    <option value="07" {{('07' == old('tipo_jornada')) ? 'selected' : ''}}> PARTIDA</option>
                                    <option value="08" {{('08' == old('tipo_jornada')) ? 'selected' : ''}}> POR TURNOS</option>
                                    <option value="99" {{('99' == old('tipo_jornada')) ? 'selected' : ''}}> OTRA JORNADA</option>

                                </select>

                                <select name="tipo_contrato" id="tipo_contrato" class="form-control input-style-custom mb-2 select-clase" required>
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

                                <select name="id_horario" id="id_horario" class="form-control input-style-custom mb-2 select-clase">
                                    <option value="" selected disabled>Horario</option>
                                    @foreach ($horarios as $horario)
                                        <option value="{{$horario->id}}" {{($horario->id == old('id_horario')) ? 'selected' : ''}}>
                                            {{$horario->alias}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </form>

    @else
        <form method="post" action="{{ route('empleados.pasouno') }}" id="submit_user" enctype="multipart/form-data">
        @csrf
            <div class="row">
                <div class="col-md-2 mt-3">
                    <div class="article border">
                        <br>
                        <div class="row">
                            <div class="col-md-12 mt-3">
                                <img src="{{asset('/img/avatar.png')}}" alt="" class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="button" class="center button-style" id="add_pasouno" value="Guardar">
                            </div>
                        </div>
                        <br>
                        <br>
                    </div>
                </div>
                <div class="col-md-10 mt-3">
                    <div class="article border">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="group">
                                    <input type="number" name="numero_empleado" id="numero_empleado" value="{{ old('numero_empleado')}}" class="form-control  input-style-custom mb-2"required>
                                    {!! $errors->first('numero_empleado','<p class="text-danger">El campo # Empleado es requerido  y debe ser solo numerico.</p>') !!}
                                    <label class="labeldes">No. Empleado</label>    
                                </div>
                                <div class="group">
                                    <input type="text" name="nombre" id="nombre_pu" value="{{old('nombre')}}" class="form-control  input-style-custom  mb-2" minlength="3" maxlength="23" required>
                                    {!! $errors->first('nombre','<p class="text-danger">El campo Nombre es requerido y debe ser solo letras.</p>') !!}
                                    <label class="labeldes">Nombre</label>    
                                </div>
                                <div class="group">
                                    <input type="text" name="apaterno" id="apaterno" value="{{old('apaterno')}}" class="form-control  input-style-custom  mb-2" minlength="3" maxlength="23" required>
                                    {!! $errors->first('apaterno','<p class="text-danger">El campo Apellido paterno es requerido y debe ser solo letras.</p>') !!}
                                    <label class="labeldes">Apellido paterno</label>    
                                </div>
                                <div class="group">
                                    <input type="text" name="amaterno" id="amaterno" value="{{old('amaterno')}}" class="form-control  input-style-custom  mb-2" minlength="3" maxlength="23" >
                                    <label class="labeldes">Apellido Materno</label>    
                                </div>
                                <div class="group">
                                    <input type="text" name="rfc" id="rfc" value="{{old('rfc')}}" class="form-control  input-style-custom  mb-2" minlength="12" maxlength="13" required onkeyup="mayusculas(this);">
                                    {!! $errors->first('rfc','<p class="text-danger">El campo RFC es requerido y va de 12 a 13 caracteres.</p>') !!}
                                    <label class="labeldes">RFC</label>    
                                </div>
                                <div class="group">
                                    <input type="text" name="curp" id="curp" value="{{old('curp')}}" class="form-control  input-style-custom  mb-2" minlength="18" maxlength="18" required onkeyup="mayusculas(this);">
                                    {!! $errors->first('curp','<p class="text-danger">El campo CURP es requerido y debe terner 18 caracteres.</p>') !!}
                                    <label class="labeldes">CURP</label>    
                                </div>
                            </div>

                            <div class="col-md-4">
                            <div class=" group">
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{old('fecha_nacimiento')}}" placeholder="" class="form-control input-style-custom mb-2" required>
                                {!! $errors->first('fecha_nacimiento','<p class="text-danger">Error: El campo fecha de nacimiento es requerido.</p>') !!}
                                <label class="labeldes">Fecha de nacimiento</label>   
                            </div>
                            <div class=" group">
                                <input type="date" name="fecha_alta" id="fecha_alta" value="{{old('fecha_alta', date('Y-m-d'))}}" class="form-control input-style-custom mb-2" placeholder="" required>
                                {!! $errors->first('fecha_alta','<p class="text-danger">Error: El campo fecha de alta es requerido.</p>') !!}
                                <label class="labeldes">Fecha de alta</label>   
                            </div>
                                <div class="mt-2 group">
                                    <select name="lugar_nacimiento" id="lugar_nacimiento" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option selected value="" disabled=""></option>
                                        <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                        <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA </OPTION>
                                        <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR </OPTION>
                                        <OPTION VALUE="CAMPECHE">CAMPECHE </OPTION>
                                        <OPTION VALUE="CHIAPAS">CHIAPAS </OPTION>
                                        <OPTION VALUE="CHIHUAHUA">CHIHUAHUA </OPTION>
                                        <OPTION VALUE="COAHUILA">COAHUILA </OPTION>
                                        <OPTION VALUE="COLIMA">COLIMA </OPTION>
                                        <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                        <OPTION VALUE="DURANGO">DURANGO </OPTION>
                                        <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO </OPTION>
                                        <OPTION VALUE="GUANAJUATO">GUANAJUATO </OPTION>
                                        <OPTION VALUE="GUERRERO">GUERRERO </OPTION>
                                        <OPTION VALUE="HIDALGO">HIDALGO </OPTION>
                                        <OPTION VALUE="JALISCO">JALISCO </OPTION>
                                        <OPTION VALUE="MICHOACÁN">MICHOACÁN </OPTION>
                                        <OPTION VALUE="MORELOS">MORELOS </OPTION>
                                        <OPTION VALUE="NAYARIT">NAYARIT </OPTION>
                                        <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN </OPTION>
                                        <OPTION VALUE="OAXACA">OAXACA </OPTION>
                                        <OPTION VALUE="PUEBLA">PUEBLA </OPTION>
                                        <OPTION VALUE="QUERETARO">QUERÉTARO </OPTION>
                                        <OPTION VALUE="QUINTANA ROO">QUINTANA ROO </OPTION>
                                        <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ </OPTION>
                                        <OPTION VALUE="SINALOA">SINALOA </OPTION>
                                        <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                        <OPTION VALUE="TABASCO">TABASCO </OPTION>
                                        <OPTION VALUE="TAMAULIPAS">TAMAULIPAS </OPTION>
                                        <OPTION VALUE="TLAXCALA">TLAXCALA </OPTION>
                                        <OPTION VALUE="VERACRUZ">VERACRUZ </OPTION>
                                        <OPTION VALUE="YUCATÁN">YUCATÁN </OPTION>
                                        <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                        <OPTION VALUE="EXTRANJERO">EXTRANJERO</OPTION>
                                    </select>
                                    {!! $errors->first('lugar_nacimiento','<p class="text-danger">Error: El lugar de nacimiento es requerido.</p>') !!}
                                    <label class="labeldes">Lugar de Nacimient</label>    
                                </div>

                                <div class="mt-2 group">
                                    <select name="id_categoria" id="id_categoria" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option disabled="" value="" selected=""></option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{$categoria->id}}" {{($categoria->id == old('id_catgoria')) ? 'selected' : ''}}>
                                                {{$categoria->nombre}}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('id_categoria','<p class="text-danger">Error: El campo prestaciones es requerido.</p>') !!}
                                    <label class="labeldes">Prestaciones</label>    
                                </div>

                               

                                <div class="mt-2 group">
                                    <select name="genero" id="genero" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option value="" selected="" disabled=""></option>
                                        <option value="M" {{('M' == old('genero')) ? 'selected' : ''}}> MASCULINO</option>
                                        <option value="F" {{('F' == old('genero')) ? 'selected' : ''}}> FEMENINO</option>
                                    </select>
                                    {!! $errors->first('genero','<p class="text-danger">Error: El campo genero es requerido.</p>') !!}
                                    <label class="labeldes">Genero</label>    
                                </div>

                                <div class="mt-2 group">
                                    <input type="number" name="nss" id="nss" value="{{old('nss')}}" class="form-control input-style-custom mb-2" maxlength="13" minlength="9" required >
                                    {!! $errors->first('nss','<p class="text-danger">Error: El campo Num Seguro Social es requerido y debe ser un numeros.</p>') !!}
                                    <label class="labeldes">No. Seguro Social</label> 
                                </div>

                            </div>

                            <div class="col-md-4 ">
                                <div class="group">
                                    <select name="id_puesto" id="id_puesto" class="form-control input-style-custom mb-2 select-clase" placeholder="Puesto" required>
                                        <option value="" disabled selected></option>
                                        @foreach ($puestos as $puesto)
                                            <option value="{{$puesto->id}}" {{($puesto->id == old('id_puesto')) ? 'selected' : ''}}>
                                                {{$puesto->puesto}}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('id_puesto','<p class="text-danger">Error: El campo puesto es requerido</p>') !!}
                                    <label class="labeldes">Puesto</label> 
                                </div>
                                    <div class="mt-2 group">
                                    <select name="jefe_inmediato"id="jefe_inmediato"  class="form-control input-style-custom mb-2 select-clase">
                                        <option value=""></option>
                                    </select>
                                 </div>
                                @foreach ($empresa_sede as $empresa)
                                    <div class="mt-2 group">
                                        <select name="id_departamento" id="id_departamento" class="form-control input-style-custom mb-2 select-clase" required>
                                            <option value="" disabled selected></option>
                                            @foreach ($departamentos as $departamento)
                                                <option value="{{$departamento->id}}" {{($departamento->id == old('id_departamento')) ? 'selected' : ''}}>
                                                    {{$departamento->nombre}}
                                                </option>
                                            @endforeach
                                        </select>
                                        {!! $errors->first('id_departamento','<p class="text-danger">Error: El campo departamento es requerido</p>') !!}               
                                        <label class="labeldes">{{($empresa->sede == 111)? 'Sucursal' : 'Departamento'}}</label> 
                                    </div>

                                    @if($empresa->sede ==1)
                                    <div class="mt-2 group">
                                        <select name="sede" id="sede" class="form-control input-style-custom mb-2 select-clase" required>
                                            <option value="" selected disabled>Sedes</option>
                                            @foreach ($sedes as $sede)
                                                <option value="{{$sede->id}}" {{($sede->id == old('sede')) ? 'selected' : ''}}>
                                                    {{$sede->nombre}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label class="labeldes">Sedes</label> 
                                    </div>
                                    @endif
                                @endforeach

                                <div class="mt-2 group">
                                    <select name="ubicacion" id="ubicacion" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option value="" selected disabled=""></option>
                                        <OPTION VALUE="AGUASCALIENTES">AGUASCALIENTES</OPTION>
                                        <OPTION VALUE="BAJA CALIFORNIA">BAJA CALIFORNIA </OPTION>
                                        <OPTION VALUE="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR </OPTION>
                                        <OPTION VALUE="CAMPECHE">CAMPECHE </OPTION>
                                        <OPTION VALUE="CHIAPAS">CHIAPAS </OPTION>
                                        <OPTION VALUE="CHIHUAHUA">CHIHUAHUA </OPTION>
                                        <OPTION VALUE="COAHUILA">COAHUILA </OPTION>
                                        <OPTION VALUE="COLIMA">COLIMA </OPTION>
                                        <OPTION VALUE="CIUDAD DE MÉXICO">CIUDAD DE MÉXICO</OPTION>
                                        <OPTION VALUE="DURANGO">DURANGO </OPTION>
                                        <OPTION VALUE="ESTADO DE MÉXICO">ESTADO DE MÉXICO </OPTION>
                                        <OPTION VALUE="GUANAJUATO">GUANAJUATO </OPTION>
                                        <OPTION VALUE="GUERRERO">GUERRERO </OPTION>
                                        <OPTION VALUE="HIDALGO">HIDALGO </OPTION>
                                        <OPTION VALUE="JALISCO">JALISCO </OPTION>
                                        <OPTION VALUE="MICHOACÁN">MICHOACÁN </OPTION>
                                        <OPTION VALUE="MORELOS">MORELOS </OPTION>
                                        <OPTION VALUE="NAYARIT">NAYARIT </OPTION>
                                        <OPTION VALUE="NUEVO LEÓN">NUEVO LEÓN </OPTION>
                                        <OPTION VALUE="OAXACA">OAXACA </OPTION>
                                        <OPTION VALUE="PUEBLA">PUEBLA </OPTION>
                                        <OPTION VALUE="QUERETARO">QUERÉTARO </OPTION>
                                        <OPTION VALUE="QUINTANA ROO">QUINTANA ROO </OPTION>
                                        <OPTION VALUE="SAN LUIS POTOSÍ">SAN LUIS POTOSÍ </OPTION>
                                        <OPTION VALUE="SINALOA">SINALOA </OPTION>
                                        <OPTION VALUE="HERMOSILLO, SONORA">HERMOSILLO, SONORA</OPTION>
                                        <OPTION VALUE="TABASCO">TABASCO </OPTION>
                                        <OPTION VALUE="TAMAULIPAS">TAMAULIPAS </OPTION>
                                        <OPTION VALUE="TLAXCALA">TLAXCALA </OPTION>
                                        <OPTION VALUE="VERACRUZ">VERACRUZ </OPTION>
                                        <OPTION VALUE="YUCATÁN">YUCATÁN </OPTION>
                                        <OPTION VALUE="ZACATECAS">ZACATECAS</OPTION>
                                    </select>   
                                    <label class="labeldes">Centro de Trabajo</label> 
                                </div>

                                <div class="mt-2 group">
                                    <select name="tipo_jornada" id="tipo_jornada" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option value="" selected disabled></option>
                                        <option value="01" {{('01' == old('tipo_jornada')) ? 'selected' : ''}}> DIURNA</option>
                                        <option value="02" {{('02' == old('tipo_jornada')) ? 'selected' : ''}}> NOCTURNA</option>
                                        <option value="03" {{('03' == old('tipo_jornada')) ? 'selected' : ''}}> MIXTA</option>
                                        <option value="04" {{('04' == old('tipo_jornada')) ? 'selected' : ''}}> POR HORA</option>
                                        <option value="05" {{('05' == old('tipo_jornada')) ? 'selected' : ''}}> REDUCIDA</option>
                                        <option value="06" {{('06' == old('tipo_jornada')) ? 'selected' : ''}}> CONTINUADA</option>
                                        <option value="07" {{('07' == old('tipo_jornada')) ? 'selected' : ''}}> PARTIDA</option>
                                        <option value="08" {{('08' == old('tipo_jornada')) ? 'selected' : ''}}> POR TURNOS</option>
                                        <option value="99" {{('99' == old('tipo_jornada')) ? 'selected' : ''}}> OTRA JORNADA</option>
                                    </select>
                                    {!! $errors->first('tipo_jornada','<p class="text-danger">Error: El campo tipo de jornada es requerido</p>') !!}
                                    <label class="labeldes">Tipo de Jornada</label> 
                                 </div>

                                <div class="mt-2 group">
                                    <select name="tipo_contrato" id="tipo_contrato" class="form-control input-style-custom mb-2 select-clase" required>
                                        <option value=""></option>
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
                                    {!! $errors->first('tipo_contrato','<p class="text-danger">Error: El campo tipo de contrato es requerido</p>') !!}   
                                    <label class="labeldes">Tipo de Contrato</label> 
                                </div>

                                <div class="mt-2 group">
                                    <select name="id_horario" id="id_horario" class="form-control input-style-custom mb-2 select-clase" required>
                                    <option value="" disabled selected></option>
                                        @foreach ($horarios as $horario)
                                            <option value="{{$horario->id}}" {{($horario->id == old('id_horario')) ? 'selected' : ''}}>
                                                {{$horario->alias}}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('id_horario','<p class="text-danger">Error: El campo Horario es requerido</p>') !!}
                                    <label class="labeldes">Horario</label> 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mt-2"></div>
                        </div>
                    </div>      
                </div>

            </div>
        </form>
    @endif




</div>

@include('includes.footer')
<script type="text/javascript">
    function mayusculas(e)
	{
	    e.value = e.value.toUpperCase();
	}

    function infonavitdiv() {
        
        var val = document.getElementById("infonavit").value;
        
        if(val == 1){ $("#infonavit_div").show(); }else{ $("#infonavit_div").hide(); }
    }

    function fonacotdiv() {
        
        var val = document.getElementById("fonacot").value;
        
        if(val == 1){ $("#fonacot_div").show(); }else{ $("#fonacot_div").hide(); }
    }

    // document.getElementById('ine').onchange = function () {
    //     console.log(this.value);
    //     document.getElementById('ine_text').innerHTML = document.getElementById('ine').files[0].name;
    // }

    function file(val){

        var text = val+"_text";
        document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
    }


    $("#add_pasouno").click(function(){
        var nombre = document.getElementById("nombre_pu").value;
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
    function submitForm() { document.getElementById("submit_user").submit() }

    $(function() {
        $('.select-clase').select2();
    });

    $("#id_puesto").on('change',function(){

        $("#jefe_inmediato").empty();
        $.get("{{route('empleados.obtenerJefeInmediato')}}",{'id':this.value },function(resp){
            const {respuesta,data} = resp;
            if(respuesta == 1){
                
                $("#jefe_inmediato").append(`<option value="" > Selecciona un jefe inmediato </option>`);
                data.map((emp,i)=>{
                    $("#jefe_inmediato").append(`<option value="${emp.id}">${emp.nombre}</option>`);
                
                });
            
            }else if(respuesta==0){
                
                $("#jefe_inmediato").append(`<option value="" >${data}</option>`);
            
            }else{
                
                $("#jefe_inmediato").append(`<option value="" >${data}</option>`);
            }  
        });
    });
</script>
