<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>

<style type="text/css">
    .file-doc {
        background-color: #fbba00;
        border: 3px #fbba00;
        border-radius: 10px;
    }

    .oculto {
        display: none;
    }

    .title-name {
        font-weight: bold;
    }
</style>


<div class="container">

    @include('includes.header',['title'=>'Información empleado',
            'subtitle'=>'Empleados', 'img'=>'/img/control-empleados.png',
            'route'=>'empleados.empleados'])

    <div class="article border" id="imprimir_emplado" name="imprimir_emplado">
        <div class="row">
            <div class="col d-flex justify-content-end m-2">
                <button class="btn button-style" id="btn_imprimir" onclick="imprimirEmpleado()"><span class="impresora-icon button-style-icon"></span></button>
            </div>
        </div>

        <div class="row">
            <div class="col-4">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="{{asset($empleado->avatar)}}" alt=""
                             class="fotografia img-thumbnail rounded-circle img-fluid mb-5">
                        @if($empleado->qr)
                            <h5><strong>Código VCARD</strong></h5>
                            <img  src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->generate($empleado->qr), '<?xml version="1.0" encoding="UTF-8" ?>')) }}"
                                  alt=""
                                 class="fotografia img-thumbnail img-fluid mb-5">
                        @endif
                    </div>

                </div>
            </div>
            <div class="col-8">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="font-size-1-3em mb-5 under-line">Datos Generales</label>
                            </div>
                        </div>

                        {{-- PASO 1 --}}
                        <div class="row">

                            <div class="col-md-4">

                                <span class="title-name"># Empleado: </span>
                                <span>{{ $empleado->numero_empleado }}</span><br>
                                <span class="title-name">Nombre: </span> <span>{{ $empleado->nombre }}</span><br>
                                <span class="title-name">Apellido paterno: </span>
                                <span>{{ $empleado->apaterno }}</span><br>
                                <span class="title-name">Apellido materno: </span>
                                <span>{{ $empleado->amaterno }}</span><br>
                                <span class="title-name">RFC: </span> <span>{{ $empleado->rfc }}</span><br>
                                <span class="title-name">CURP: </span> <span>{{ $empleado->curp }}</span><br>

                            </div>


                            <div class="col-md-4">
                                <span class="title-name">Fecha de nacimiento: </span>
                                <span>{{ $empleado->fecha_nacimiento }}</span><br>
                                <span class="title-name">Fecha de alta: </span>
                                <span>{{ $empleado->fecha_alta }}</span><br>
                                <span class="title-name">Lugar de nacimiento: </span>
                                <span>{{ $empleado->lugar_nacimiento }}</span><br>
                                <span class="title-name">Prestaciones: </span>
                                {{-- <label>Prestaciones:  </label> --}}
                                @foreach ($categorias as $categoria)
                                    @php
                                        $categoria = ($categoria->id == $empleado->id_categoria) ? $categoria->nombre : '';
                                        echo $categoria;
                                    @endphp
                                @endforeach
                                <br>
                                <span class="title-name">Genero: </span>
                                <span>{{ ( $empleado->genero == 'M') ? 'Masculino' : 'Femenino' }}</span><br>
                                <span class="title-name">Num Seguro Social: </span>
                                <span>{{ $empleado->nss }}</span><br>

                            </div>

                            <div class="col-md-4">
                                <span class="title-name">Puesto: </span>
                                @foreach ($puestos as $puesto)
                                    <span>{{ ($puesto->id == $empleado->id_puesto) ? $puesto->puesto : '' }}</span>
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

                                <span class="title-name">Centro de trabajo: </span>
                                <span>{{ $empleado->ubicacion }}</span><br>

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

                        {{-- PASO 2 --}}
                        <div class="row">
                            <div class="col-md-4">
                                <span class="title-name">Tipo de nomina: </span>
                                @foreach ($tipos_nomina as $tn)
                                    {{ (strtoupper($tn) == $empleado->tipo_de_nomina) ? strtoupper($tn) : '' }}
                                @endforeach
                                <br>

                                <span class="title-name">Salario diario: </span>
                                <span>{{ $empleado->salario_diario }}</span><br>
                                <span class="title-name">Salario diario integrado: </span>
                                <span>{{ $empleado->salario_diario_integrado }}</span><br>

                                <span class="title-name">Sueldo neto del periodo: </span>
                                <span>{{ $empleado->sueldo_neto }}</span><br>
                                <span class="title-name">Sueldo diario real: </span>
                                <span>{{ $empleado->salario_digital }}</span><br>

                            </div>

                            <div class="col-md-4">
                                <span class="title-name">Fecha de antiguedad: </span>
                                <span>{{ $empleado->fecha_antiguedad }}</span><br>
                                <span class="title-name">Días de vacaciones: </span>
                                <span>{{ $empleado->dias_vacaciones }}</span><br>

                                <span class="title-name">Días de aguinaldo: </span>
                                <span>{{ $empleado->dias_aguinaldo }}</span><br>
                                <span class="title-name">% de prima vacacional: </span>
                                <span>{{ $empleado->porcentaje_prima }}</span><br>
                            </div>

                            <div class="col-md-4">

                                <span class="title-name">Tipo salario: </span>
                                <span>{{ $empleado->tipo_salario }}</span><br>
                                <span class="title-name">Clabe interbancaria: </span>
                                <span>{{ $empleado->clabe_interbancaria }}</span><br>

                                <span class="title-name">Banco: </span>
                                @foreach ($bancos as $banco)
                                    {{ ( $banco->id == $empleado->id_banco) ? strtoupper($banco->nombre) : '' }}
                                @endforeach
                                <br>
                                <span class="title-name">Cuenta banco: </span>
                                <span>{{ $empleado->cuenta_bancaria }}</span><br>
                                <span class="title-name">Tipo cuenta: </span>
                                {{ ( $empleado->tipo_cuenta == '01') ? 'CHEQUES' : '' }}
                                {{ ( $empleado->tipo_cuenta == '03') ? 'TARJETA DEDÉBITO' : '' }}
                                {{ ( $empleado->tipo_cuenta == '40') ? 'CLABE' : '' }}
                                <br>
                            </div>
                        </div>


                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="font-size-1-3em mb-5 under-line">Datos Personales</label>
                            </div>
                        </div>

                        {{-- PASO 2 --}}
                        <div class="row">
                            <div class="col md-4">

                                <span class="title-name">Nacionalidad: </span>
                                <span>{{ $empleado->nacionalidad }}</span><br>
                                <span class="title-name">Calle y num ext e int: </span>
                                <span>{{ $empleado->calle_numero }}</span><br>
                                <span class="title-name">Colonia: </span> <span>{{ $empleado->colonia }}</span><br>
                                <span class="title-name">Alcaldía o municipio: </span>
                                <span>{{ $empleado->delegacion }}</span><br>
                                <span class="title-name">Estado: </span> <span>{{ $empleado->estado }}</span><br>
                                <span class="title-name">Código postal: </span> <span>{{ $empleado->cp }}</span><br>

                            </div>

                            <div class="col-md-4">
                                <span class="title-name">Correo electrónico: </span>
                                <span>{{ $empleado->correo }}</span><br>
                                <span class="title-name">Teléfono casa: </span>
                                <span>{{ $empleado->telefono_casa }}</span><br>
                                <span class="title-name">Teléfono movil: </span>
                                <span>{{ $empleado->telefono_movil }}</span><br>
                                <span class="title-name">Estado civil: </span>
                                <span>{{ $empleado->estado_civil }}</span><br>
                                <span class="title-name">Escolaridad: </span>
                                <span>{{ $empleado->escolaridad }}</span><br>
                                <span class="title-name">Profesión: </span>
                                <span>{{ $empleado->profesion }}</span><br>
                            </div>

                            <div class="col-md-4">
                                <span class="title-name">En caso de accidente avisar a: </span>
                                <span>{{ $empleado->avisar_a }}</span><br>
                                <span class="title-name">Telefono: </span>
                                <span>{{ $empleado->avisar_a_telefono }}</span><br>
                                <span class="title-name">Beneficiario: </span>
                                <span>{{ $empleado->beneficiario }}</span><br>
                                <span class="title-name">Parentesco: </span>
                                <span>{{ $empleado->avisar_a_parentesco }}</span><br>
                            </div>
                        </div>

                        <br>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="font-size-1-3em mb-5 under-line">Infonavit/Fonacot</label>
                                    </div>
                                    <div class="col-md-12">
                                        <span class="title-name">¿Cuenta con crédito INFONAVIT?: </span>
                                        <span>{{(!empty($empleado->num_credito_infonavit)) ? 'SI' : 'NO'}}</span><br>
                                        <div id="infonavit_div"
                                             class="{{(empty($empleado->num_credito_infonavit)) ? 'oculto' : ''}}">
                                            <span class="title-name">Num.Crédito INFONAVIT: </span>
                                            <span>{{ $empleado->num_credito_infonavit }}</span><br>
                                            <span class="title-name">Tipo de descuento: </span>
                                            <span>{{ $empleado->tipo_descuento }}</span><br>
                                            <span class="title-name">Valor descuento: </span>
                                            <span>{{ $empleado->valor_descuento }}</span><br>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <span class="title-name">¿Cuenta con crédito FONACOT?: </span>
                                        <span>{{(!empty($empleado->num_credito_fonacot)) ? 'SI' : 'NO'}}</span><br>
                                        <div id="fonacot_div"
                                             class="{{(empty($empleado->num_credito_fonacot)) ? 'oculto' : ''}}">
                                            <span class="title-name">Num.Crédito FONACOT: </span>
                                            <span>{{ $empleado->num_credito_fonacot }}</span><br>
                                            <span class="title-name">Valor: </span>
                                            <span>{{ $empleado->valor_fonacot }}</span><br>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col">
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
                                                <span class="title-name">Sindical: </span>
                                                <span>{{($empleado->tipo_sindical == 1) ? 'SI' : 'NO'}}</span><br>

                                            </div>
                                            <div class="col-md-4">
                                                <span class="title-name">Fiscal: </span>
                                                <span>{{($empleado->tipo_fiscal == 1) ? 'SI' : 'NO'}}</span><br>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

</div>


<script type="text/javascript">
    function imprimirEmpleado() {
        $('#btn_imprimir').hide();
        let contenido = document.getElementById('imprimir_emplado').innerHTML;
        let contenidoOriginal = document.body.innerHTML;
        document.body.innerHTML = contenido;
        window.print();
        document.body.innerHTML = contenidoOriginal;
        $('#btn_imprimir').show()
    }
</script>