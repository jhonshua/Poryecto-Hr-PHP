@extends('layouts.empleado')
@section('tituloPagina', "BIENVENIDO A TU PERFIL : ". Session::get('empleado')['nombre'].' '.Session::get('empleado')['apaterno'].' '.Session::get('empleado')['amaterno'])
@section('content')
<div class="row">
    <div class="col-md-12">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active" id="generles-tab" data-toggle="tab" href="#generles" role="tab"
                    aria-controls="generles" aria-selected="true">DATOS GENERALES</a>
                <a class="nav-item nav-link" id="salario-tab" data-toggle="tab" href="#salario" role="tab"
                    aria-controls="salario" aria-selected="false">SALARIO</a>
                <a class="nav-item nav-link" id="personales-tab" data-toggle="tab" href="#personales" role="tab"
                    aria-controls="personales" aria-selected="false">DATOS PERSONALES</a>

                @if (Session::get('empresa')['id'] != 111) {{--JEDISAM --}}
                <a class="nav-item nav-link" id="infonavit-tab" data-toggle="tab" href="#infonavit" role="tab"
                    aria-controls="infonavit" aria-selected="false">INFONAVIT/FONACOT</a>
                @endif
 
                <a class="nav-item nav-link" id="expediente-tab" data-toggle="tab" href="#expediente" role="tab"
                    aria-controls="expediente" aria-selected="false">EXPEDIENTE</a>

            </div>
        </nav>
        <div class="tab-content tab-validate border p-4 bg-white" id="nav-tabContent">
            
            <div class="tab-pane fade show active " id="generles" role="tabpanel" aria-labelledby="generles-tab">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <img src="{{$empleado->avatar}} " alt=""class="mt-5 rounded-circle img-fluid"><br>
                    </div>
                    <div class="col-md-3">
                   
                        <label class="mb-0">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" value="{{$empleado->nombre}}"
                            class="form-control form-control-sm mb-2 mayusculas" disabled>

                        <label class="mb-0">RFC:</label>
                        <input type="text" name="rfc" id="rfc" value="{{$empleado->rfc}}" class="form-control form-control-sm mb-2 mayusculas"
                            minlength="11" maxlength="16" disabled>

                        <label class="mb-0">CURP:</label>
                        <input type="text" name="curp" id="curp" value="{{$empleado->curp}}"
                            class="form-control form-control-sm  mb-2 mayusculas" minlength="18" maxlength="18" disabled>

                        <label class="mb-0">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                            value="{{$empleado->fecha_nacimiento}}" class="form-control form-control-sm  mb-2" disabled>

                    </div>
                    <div class="col-md-3">

                        <label class="mb-0">Apellido paterno:</label>
                        <input type="text" name="apaterno" id="apaterno" value="{{$empleado->apaterno}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Fecha de Alta:</label>
                        <input type="date" name="fecha_alta" id="fecha_alta" value="{{$empleado->fecha_alta}}"
                            class="form-control form-control-sm  mb-2" disabled>
            
                        <label class="mb-0">Genero:</label>
                        <input type="text" class="form-control form-control-sm  mb-2" value="{{('F' == $empleado->genero) ? $genero='FEMENINO' : $genero='MASCULINO'}}" disabled>

                        <label class="mb-0">Lugar de Nacimiento:</label>
                        <input type="text" class="form-control form-control-sm  mb-2" value="{{$empleado->lugar_nacimiento}}" disabled >
            
                    </div>
                    <div class="col-md-3">

                        <label class="mb-0">Apellido Materno:</label>
                        <input type="text" name="amaterno" id="amaterno" value="{{$empleado->amaterno}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        @foreach ($departamentos as $departamento)
                            @if(($departamento->id == $empleado->id_departamento))
                                <label class="mb-0">{{(Session::get('empresa')['id'] == 111)? 'Sucursal' : 'Departamento'}}:</label>
                                <input type="text" class="form-control form-control-sm  mb-2" value="{{$departamento->nombre}}" disabled >
                            @endif
                        @endforeach
            
                        <label class="mb-0">Centro de Trabajo:</label>
                        <input type="text" class="form-control  form-control-sm  mb-2" value="{{$empleado->ubicacion}}" disabled >

                        <label class="mb-0">Num Seguro Social:</label>
                        <input type="text" name="nss" id="nss" value="{{$empleado->nss}}" class="form-control form-control-sm  mb-2" disabled >
        
                        @foreach ($horarios as $horario)
                            @if(($horario->id == $empleado->id_horario))
                                <label class="mb-0">Horario:</label>
                                <input type="text" class="form-control  form-control-sm  mb-2" value="{{$horario->alias}}" disabled >
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="salario" role="tabpanel" aria-labelledby="salario-tab">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <img src="{{asset($empleado->avatar)}} " alt=""class="mt-5 rounded-circle img-fluid"><br>
                    </div>
                    <div class="col-md-3">
                        @foreach ($tipos_nomina as $tn)
                            @if((strtoupper($tn) == strtoupper($empleado->tipo_de_nomina)))
                                <label class="mb-0">Tipo de Nomina:</label>
                                <input type="text" class="form-control form-control-sm  mb-2" value="{{ strtoupper($tn)}}" disabled >
                            @endif
                        @endforeach
                            
                        <label class="mb-0">Salario Diario:</label>
                        <input type="text" name="salario_diario" id="salario_diario"
                            value="{{$empleado->salario_diario}}" class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Salario Diario Integrado:</label>
                        <input type="text" name="salario_diario_integrado" id="salario_diario_integrado"
                            value="{{$empleado->salario_diario_integrado}}" class="form-control  form-control-sm  mb-2" disabled>

                        <label class="mb-0">Sueldo Neto del Periodo:</label>
                        <input type="text" name="sueldo_neto" id="sueldo_neto" value="{{$empleado->sueldo_neto}}"
                            class="form-control form-control-sm mb-2" disabled>

                    </div>

                    <div class="col-md-3">
                        <label class="mb-0">Fecha de Antiguedad:</label>
                        <input type="date" name="fecha_antiguedad" id="fecha_antiguedad"
                            value="{{$empleado->fecha_antiguedad}}" class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Días de Vacaciones:</label>
                        <input type="number" name="dias_vacaciones" id="dias_vacaciones"
                            value="{{$empleado->dias_vacaciones}}" class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Días de Aguinaldo:</label>
                        <input type="number" name="dias_aguinaldo" id="dias_aguinaldo"
                            value="{{$empleado->dias_aguinaldo}}" class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">% de Prima vacacional:</label>
                        <input type="number" name="porcentaje_prima" id="porcentaje_prima"
                            value="{{$empleado->porcentaje_prima}}" class="form-control  form-control-sm  mb-2" disabled>

                        @php
                        $date = Carbon\Carbon::parse($empleado->fecha_antiguedad);
                        $now = Carbon\Carbon::now();
                        $diff = $date->diffInYears($now);
                        @endphp

                    </div>

                    <div class="col-md-3">

                        <label class="mb-0">Clabe Interbancaria:</label>
                        <input type="number" name="clabe_interbancaria" id="clabe_interbancaria"
                            value="{{$empleado->clabe_interbancaria}}" class="form-control form-control-sm  mb-2" disabled>

                        @foreach ($bancos as $banco)
                            @if(($banco->id == $empleado->id_banco))
                                <label class="mb-0">Banco:</label>
                                <input type="text" class="form-control form-control-sm" value="{{ strtoupper($banco->nombre)}}" disabled>
                            @endif
                        @endforeach
          
                        <label class="mb-0">Cuenta Banco:</label>
                        <input type="number" name="cuenta_bancaria" id="cuenta_bancaria"
                            value="{{$empleado->cuenta_bancaria}}" class="form-control form-control-sm form-control-sm  mb-2" disabled>
    
                        @php $tipocuentas=[0 =>array('cuenta'=>'01','nombre'=>'CHEQUES'),
                                           1=>array('cuenta'=>'03','nombre'=>'TARJETA DE DÉBITO'),
                                           2=>array('cuenta'=>'40','nombre'=>'CLABE')] @endphp
                       
                        @foreach ($tipocuentas as $key => $tipocuenta  )
                            @if(($empleado->tipo_cuenta == $tipocuenta['cuenta']))
                                <label class="mb-0">Tipo Cuenta:</label>
                                <input type="text" class="form-control form-control-sm" value="{{$tipocuenta['nombre']}}" disabled>
                            @endif
                        @endforeach

                        <label class="mb-0">Sueldo Diario Real:</label>
                        <input type="text" name="salario_digital" id="salario_digital"
                            value="{{$empleado->salario_digital}}" class="form-control form-control-sm  mb-2" disabled>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="personales" role="tabpanel" aria-labelledby="personales-tab">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <img src="{{asset($empleado->avatar)}} " alt=""class="mt-5 rounded-circle img-fluid"><br>
                    </div>
                    <div class="col-md-3">

                        <label class="mb-0">Nacionalidad:</label>
                        <input type="text" name="nacionalidad" id="nacionalidad" value="{{$empleado->nacionalidad}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Calle y Num Ext e Int:</label>
                        <input type="text" name="calle_numero" id="calle_numero" value="{{$empleado->calle_numero}}"
                            class="form-control  form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Colonia:</label>
                        <input type="text" name="colonia" id="colonia" value="{{$empleado->colonia}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Alcaldía o Municipio:</label>
                        <input type="text" name="delegacion" id="delegacion" value="{{$empleado->delegacion}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Estado:</label>
                        <input type="text" class="form-control form-control-sm" value="{{$empleado->estado}}" disabled >

                    </div>

                    <div class="col-md-3">
                        <label class="mb-0">Correo Electrónico:</label>
                        <input type="email" name="correo" id="correo" value="{{$empleado->correo}}"
                            class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Teléfono Casa:</label>
                        <input type="tel" name="telefono_casa" id="telefono_casa"
                            value="{{$empleado->telefono_casa}}" class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Teléfono Movil:</label>
                        <input type="tel" name="telefono_movil" id="telefono_movil"
                            value="{{$empleado->telefono_movil}}" class="form-control  form-control-sm  mb-2" disabled>

                        <label class="mb-0">Estado Civil:</label>
                        <input type="text" name="estado_civil" id="estado_civil" value="{{$empleado->estado_civil}}"
                            class="form-control  form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">Código Postal:</label>
                        <input type="number" name="cp" id="cp" value="{{$empleado->cp}}" class="form-control form-control-sm  mb-2"
                            maxlength="5" disabled>
                    </div>

                    <div class="col-md-3">

                        <label class="mb-0">Escolaridad:</label>
                        <input type="text" name="escolaridad" id="escolaridad" value="{{$empleado->escolaridad}}"
                            class="form-control form-control-sm  mb-2" disabled>

                        <label class="mb-0">Profesión:</label>
                        <input type="text" name="profesion" id="profesion" value="{{$empleado->profesion}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>

                        <label class="mb-0">En caso de Accidente Avisar a:</label>
                        <input type="text" name="avisar_a" id="avisar_a" value="{{$empleado->avisar_a}}"
                            class="form-control form-control-sm  mb-2" disabled>
                        <input type="text" name="avisar_a_telefono" id="avisar_a_telefono"
                            value="{{$empleado->avisar_a_telefono}}" class="form-control  form-control-sm  mb-2" disabled>

                        <label class="mb-0">Beneficiario:</label>
                        <input type="text" name="beneficiario" id="beneficiario" value="{{$empleado->beneficiario}}"
                            class="form-control form-control-sm  mb-2 mayusculas" disabled>
                    
                        @php  $parentescos=array(0=>'HERMANO(A)',2=>'ESPOSO(A)',3=>'HIJO(A)',4=>'PADRES',5=>'OTRO',6=>'OTROS')  @endphp
                        @foreach ($parentescos as $parentesco)
                            @if($empleado->avisar_a_parentesco==$parentesco)
                                <label class="mb-0">Parentesco:</label>
                                <input type="text" class="form-control form-control-sm" value="{{$parentesco}}" disabled>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @if (Session::get('empresa')['id'] != 111) {{--JEDISAM --}}
                <div class="tab-pane fade" id="infonavit" role="tabpanel" aria-labelledby="infonavit-tab">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <img src="{{asset($empleado->avatar)}} " alt=""class="mt-5 rounded-circle img-fluid"><br>
                        </div>

                        <div class="col-md-1"></div>

                        <div class="col-md-3" id="credito_inf">

                            <div class="infonavit">

                                <label class="mb-0">Num.Crédito INFONAVIT:</label>
                                <input type="text" name="num_credito_infonavit" id="num_credito_infonavit"
                                    class="form-control mb-2 form-control-sm" value="{{$empleado->num_credito_infonavit}}" disabled>

                               
                                @php $creditos=array(0=>'POR PORCENTAJE',1=>'VECES EN SALARIO',2=>'CUOTA FIJA' )  @endphp
                                @foreach ($creditos as $credito)
                                    @if($empleado->num_credito_infonavit == $credito )
                                        <label class="mb-0">¿Cuenta con crédito INFONAVIT?:</label>
                                        <input type="text" class="form-control form-control-sm" value="{{$credito}}" disabled>
                                    @endif
                                @endforeach
                                <label class="mb-0">Valor:</label>
                                <input type="text" name="valor_descuento" id="valor_descuento" class="form-control mb-2 form-control-sm"
                                    value="{{$empleado->valor_descuento}}" disabled>
                            </div>
                        </div>

                        <div class="col-md-1"></div>

                        <div class="col-md-3" id="credito_fon">

                            <label class="mb-0">¿Cuenta con crédito FONACOT?:</label>
                            <input type="text" class="form-control mb-2 form-control-sm" value="{{(!empty($empleado->num_credito_fonacot)) ? 'Si':'' }}" disabled >
                          
                            <div class="fonacot ">
                                <label class="mb-0">Num.Crédito FONACOT:</label>
                                <input type="text" name="num_credito_fonacot" id="num_credito_fonacot"
                                    class="form-control mb-2 form-control-sm" value="{{$empleado->num_credito_fonacot}}" disabled>

                                <label class="mb-0">Valor:</label>
                                <input type="text" name="valor_fonacot" id="valor_fonacot" class="form-control mb-2 form-control-sm"
                                    value="{{$empleado->valor_fonacot}}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="tab-pane fade" id="expediente" role="tabpanel" aria-labelledby="expediente-tab">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <img src="{{asset($empleado->avatar)}} " alt=""class="mt-5 rounded-circle img-fluid"><br>
                    </div>
                    <div class="col-md-10 d-flex flex-wrap files">

                        @foreach ($archivos as $key => $archivo)
                            @if($empleado->$key)
                                <div class="file rounded d-flex  align-items-center p-2 mr-3 mb-3 bg-warning"
                                    for="{{$key}}">
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
                                            <a href="{{'../storage/repositorio/'.Session::get('empresa')['id'].'/'.$empleado->id.'/'.$empleado->$key}}" class="btn btn-warning" target="_blank"
                                                title="Ver archivo" title="Ver archivo">
                                                <i class="fas {{$icon}} fa-3x icon"></i>
                                            </a>
                                            <br> <a href="{{'../storage/repositorio/'.Session::get('empresa')['id'].'/'.$empleado->id.'/'.$empleado->$key}}" class="btn btn-primary pull-right" target="_blank"
                                                title="Ver archivo" title="Ver archivo">
                                                Ver
                                            </a>&nbsp;&nbsp;

                                        @endif
                                    <label class="name tooltip_" data-toggle="tooltip"
                                    for="{{$key}}">{{$archivo}}</label>        
                                </div>
                            @endif
                        @endforeach

                        @foreach ($archivos_extras as $archivo)

                            @php
                            $nombre_campo = $archivo->nombre_campo;
                            $camposExtras = collect($empleado->camposExtras)->keyBy('nombre_campo');
                            @endphp
                            @if(isset($camposExtras[$nombre_campo]) && !empty($camposExtras[$nombre_campo]->info))

                                <div class="file rounded d-flex  align-items-center p-2 mr-3 mb-3 bg-warning"
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
                                        <a href="{{asset($camposExtras[$nombre_campo]->info)}}" class="btn btn-warning"
                                            target="_blank" title="Ver archivo" title="Ver archivo">
                                            <i class="fas {{$icon}} fa-3x icon"></i>
                                        </a>
                                        
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
                                    <input type="file" name="{{$nombre_campo}}" id="{{$nombre_campo}}" class="invisible"
                                        accept=".pdf, .png, .jpg, .doc, .docx" {{($requerido) ? 'required' : '' }}>
                                </div>
                        
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="spinner" class="spinner  overlay"></div>

@endsection

@push('css')
<style>
    label {
        font-weight: bold;
        margin-top: 15px;
    }

    .bg-gray {
        background-color: #eee;
    }

    .file {
        box-shadow: 3px 3px 3px #dadada;
        cursor: pointer;
        width: 30%;
    }

    .file label {
        cursor: pointer;
    }

    .file input[type=file] {
        width: 1px;
    }

    label {
        font-weight: bold;
        margin-top: 15px; }

    nav a{
        color:black; }

    nav a:hover{
        color:#fbba00; }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00;
        color: gray;}
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
        color: black;
        font-weight: bold;
    }

    .nav-link {
        display: block;
        padding: 0.5rem 1.8rem !important;
    }


</style>
@endpush

@push('scripts')
<script>

    $(function(){

        let credito_infonavit = '@php echo $empleado->num_credito_infonavit @endphp';
        let credito_fonacot =   '@php echo $empleado->num_credito_fonacot @endphp';

        if( credito_infonavit !=="" || credito_fonacot  !=="" ){ 

            $("#infonavit-tab").removeAttr('d-none');
            ( credito_infonavit !== "") ?   $("#credito_inf").removeAttr('d-none') : $("#credito_inf").attr( 'class','d-none');
            ( credito_fonacot !=="" ) ?  $("#credito_fon").removeAttr('d-none'): $("#credito_fon").attr('class','d-none');
          
        }else{
            $("#infonavit-tab").attr('class','d-none')

        }
        $('#spinner').attr('class','ocultar');       
    });
</script>
@endpush