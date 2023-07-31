<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

    @include('includes.header',['title'=>'Parametros de la empresa',
        'subtitle'=>'Herramientas', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'bandeja'])
      
        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @endif

        <form method="post" id="submit_parametro" action="{{route('herramientas.editar-parametros')}}" enctype="multipart/form-data">
            @csrf
            @foreach ($parametros as $parametro)
            <div class="row m-0 p-0">
                <div class="col-md-6 p-2">
                    <div class="article border mb-4 px-4">
                        <label class="font-weight-bold center font-size-1-2em">Parámetros</label>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Ejercicio:</label>
                                </td>
                                <td>
                                    <select name="ejercicio" id="ejercicio" class="float-right input-style-parametro mb-2" required>
                                        {{ $last= date('Y')-2 }}
                                        {{ $now = date('Y')+1 }}
                                        @for ($i = $now; $i >= $last ; $i--)
                                        <option value="{{$i}}" {{($parametro->ejercicio == $i) ? 'selected' : ''}}>{{$i}}</option>
                                        @endfor
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">UMA:</label>
                                </td>
                                <td>
                                    <input type="text" name="uma" id="uma" class="float-right input-style-parametro mb-2" required value="{{$parametro->uma}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Salario mínimo:</label>
                                </td>
                                <td>
                                    <input type="text" name="salario_minimo" id="salario_minimo" class="float-right input-style-parametro mb-2" required value="{{$parametro->salario_minimo}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Salario máximo:</label>
                                </td>
                                <td>
                                    <input type="text" name="salario_maximo" id="salario_maximo" class="float-right input-style-parametro mb-2" required value="{{$parametro->salario_maximo}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Días aviso contrato:</label>
                                </td>
                                <td>
                                    <input type="text" name="dias_aviso_contrato" id="dias_aviso_contrato" class="float-right input-style-parametro mb-2" required value="{{$parametro->dias_aviso_contrato}}">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <br>
                    <div class="article border mb-4 px-4">
                        <label class="font-weight-bold center font-size-1-2em">Cuota obrera patronal</label>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Cuota fija:</label>
                                </td>
                                <td>
                                    <input type="text" name="cuota_fija" id="cuota_fija" class="input-style-parametro mb-2" value="{{$parametro->cuota_fija}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Excedente patronal:</label>
                                </td>
                                <td>
                                    <input type="text" name="excedente_patro" id="excedente_patro" class="input-style-parametro mb-2" value="{{$parametro->excedente_patro}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Excedente obrera:</label>
                                </td>
                                <td>
                                    <input type="text" name="excedente_obrera" id="excedente_obrera" class="input-style-parametro mb-2" value="{{$parametro->excedente_obrera}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Prestaciones en dinero patronal:</label>
                                </td>
                                <td>
                                    <input type="text" name="prestaciones_patronal" id="prestaciones_patronal" class="input-style-parametro mb-2" value="{{$parametro->prestaciones_patronal}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Prestaciones en dinero obrera:</label>
                                </td>
                                <td>
                                    <input type="text" name="prestaciones_obrera" id="prestaciones_obrera" class="input-style-parametro mb-2" value="{{$parametro->prestaciones_obrera}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Gastos médicos pensionados patronal:</label>
                                </td>
                                <td>
                                    <input type="text" name="gastos_medi_patronal" id="gastos_medi_patronal" class="input-style-parametro mb-2" value="{{$parametro->gastos_medi_patronal}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Gastos médicos pensionados obrera:</label>
                                </td>
                                <td>
                                    <input type="text" name="gastos_medi_obrera" id="gastos_medi_obrera" class="input-style-parametro mb-2" value="{{$parametro->gastos_medi_obrera}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Invalidez y vida patronal:</label>
                                </td>
                                <td>
                                    <input type="text" name="invalidez_patronal" id="invalidez_patronal" class="input-style-parametro mb-2" value="{{$parametro->invalidez_patronal}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Invalidez y vida obrera:</label>
                                </td>
                                <td>
                                    <input type="text" name="invalidez_obrera" id="invalidez_obrera" class="input-style-parametro mb-2" value="{{$parametro->invalidez_obrera}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Guarderías y prestaciones sociales:</label>
                                </td>
                                <td>
                                    <input type="text" name="guarderia_presta_social" id="guarderia_presta_social" class="input-style-parametro mb-2" value="{{$parametro->guarderia_presta_social}}">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <br>
                    <div class="article border mb-4 px-4">
                        <table class="w-100">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Biométricos:</label>
                                </td>
                                <td>
                                    <select name="biometrico" id="biometrico" class="float-right input-style-parametro mb-2">
                                        <option value="0" {{(!$parametro->biometrico) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->biometrico) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Móvil app:</label>
                                </td>
                                <td>
                                    <select name="app" id="app" class="float-right input-style-parametro mb-2">
                                        <option value="0" {{(!$parametro->app) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->app) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <hr>
                        <label class="text-left font-weight-bold">Actualmente los conceptos sindicales estan visibles en las empresas:</label>
                        <table class="w-100">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Esconder: </label>
                                </td>
                                <td>
                                    <select name="esconder" id="esconder" class="float-right input-style-parametro mb-2">
                                        <option value="0">No</option>
                                        <option value="1">Si</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <input type="hidden" name="id" value="{{$parametro->id}}">
                        <input type="submit" class="center button-style" id="edit_parametro" value="Guardar">
                    </div>
                </div>
                <div class="col-md-6 p-2">
                    <div class="article border mb-4">
                        <label class="font-weight-bold center font-size-1-2em">Datos facturación</label>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Porcentaje honorarios:</label>
                                </td>
                                <td>
                                    <input type="text" name="porcentaje_honorarios" id="porcentaje_honorarios" class="float-right input-style-parametro mb-2" value="{{$parametro->porcentaje_honorarios}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Porcentaje impuesto nómina:</label>
                                </td>
                                <td>
                                    <input type="text" name="porcentaje_nomina" id="porcentaje_nomina" class="float-right input-style-parametro mb-2" value="{{$parametro->porcentaje_nomina}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Comisíon variable:</label>
                                </td>
                                <td>
                                    <input type="text" name="comision_variable" id="comision_variable" class="float-right input-style-parametro mb-2" value="{{$parametro->comision_variable}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Provisión carga social: %</label>
                                </td>
                                <td>
                                    <input type="text" name="provision_porcentaje" id="provision_porcentaje" class="float-right input-style-parametro mb-2" value="{{$parametro->provision_porcentaje}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Provisión carga social: $</label>
                                </td>
                                <td>
                                    <input type="text" name="provision_valor" id="provision_valor" class="float-right input-style-parametro mb-2" value="{{$parametro->provision_valor}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Comisión mismo día: $</label>
                                </td>
                                <td>
                                    <input type="text" name="comision_mismo_dia" id="comision_mismo_dia" class="float-right input-style-parametro mb-2" value="{{$parametro->comision_mismo_dia}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Prestaciones extras: $</label>
                                </td>
                                <td>
                                    <input type="text" name="valor_prestacion_extra" id="valor_prestacion_extra" class="float-right input-style-parametro mb-2" value="{{$parametro->valor_prestacion_extra}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Anticipo: $</label>
                                </td>
                                <td>
                                    <input type="text" name="anticipo" id="anticipo" class="float-right input-style-parametro mb-2" value="{{$parametro->anticipo}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Provisión obrero-patronal: %</label>
                                </td>
                                <td>
                                    <input type="text" name="provision_obrero" id="provision_obrero" class="float-right input-style-parametro mb-2" value="{{$parametro->provision_obrero}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Concepto facturación:</label>
                                </td>
                                <td>
                                    <input type="text" name="concepto_facturacion" id="concepto_facturacion" class="float-right input-style-parametro mb-2" value="{{$parametro->concepto_facturacion}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">IVA:</label>
                                </td>
                                <td>
                                    <input type="text" name="iva" id="iva" class="float-right input-style-parametro mb-2" value="{{$parametro->iva}}">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Provisionar aguinaldo:</label>
                                </td>
                                <td>
                                    <select name="provision_aguinaldo" id="provision_aguinaldo" class="float-right input-style-parametro mb-2">
                                        <option value="0" {{(!$parametro->provision_aguinaldo) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->provision_aguinaldo) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Provisionar prima vacacional:</label>
                                </td>
                                <td>
                                    <select name="provision_prima_vacacional" id="provision_prima_vacacional" class="float-right input-style-parametro mb-2">
                                        <option value="0" {{(!$parametro->provision_prima_vacacional) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->provision_prima_vacacional) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">RCV patronal-obrero:</label>
                                </td>
                                <td>
                                    <select name="rvc_patronal_obrero" id="rvc_patronal_obrero" class="float-right input-style-parametro mb-2">
                                        <option value="0" {{(!$parametro->rvc_patronal_obrero) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->rvc_patronal_obrero) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <br>
                    <div class="article border mb-4">
                        <label class="font-weight-bold center font-size-1-2em">Datos empresa</label>
                        <hr>
                        <table class="w-100">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Logo empresa cliente:</label>
                                </td>
                                <td>
                                    <div class="float-right w-px-250 custom-file mb-2">
                                    <input type="file" class="custom-file-input" name="logo_empresa_cliente_" onchange="file('logo_empresa_cliente')" id="logo_empresa_cliente" accept=".png, .jpg" >
                                            <label class="custom-file-label" for="logo_empresa_cliente" id="logo_empresa_cliente_text">{{ $parametro->logo_empresa_cliente }}</label>
                                         </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Logo empresa emisora:</label>
                                </td>
                                <td>
                                    <div class="float-right w-px-250 custom-file mb-2">
                                        <input class="custom-file-input" type="file" name="logo_empresa_emisora_" onchange="file('logo_empresa_emisora')" id="logo_empresa_emisora" accept=".png, .jpg">
                                        <label class="custom-file-label" for="logo_empresa_emisora"  id="logo_empresa_emisora_text">{{ $parametro->logo_empresa_emisora }}</label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="w-100">
                            <label class="font-weight-bold center mr-2 mt-3">Tipo de nómina:</label>
                            <input type="checkbox" name="tipo_nomina" id="" value="sindical" {{(strtolower($parametro->tipo_nomina) == 'sindical') ? 'checked' : ''}}> Ambas
                            <input type="checkbox" name="tipo_nomina" id="" class="ml-3" value="solosindical" {{(strtolower($parametro->tipo_nomina) == 'solosindical') ? 'checked' : ''}}> Sindical
                            <input type="checkbox" name="tipo_nomina" id="" class="ml-3" value="fiscal" {{(strtolower($parametro->tipo_nomina) == 'fiscal') ? 'checked' : ''}}> Fiscal
                            <br>
                            <br>
                        </div>
                        <table class="w-100">
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">Autoenumerar empleados:</label>
                                </td>
                                <td>
                                    <select name="autoenumerar_empleado" id="autoenumerar_empleado" class="input-style-parametro float-right w-px-250">
                                        <option value="0" {{(!$parametro->autoenumerar_empleado) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->autoenumerar_empleado) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="text-left font-weight-bold">El usuario puede editar conceptos:</label>
                                </td>
                                <td>
                                    <select name="editar_conceptos" id="editar_conceptos" class="input-style-parametro float-right w-px-250">
                                        <option value="0" {{(!$parametro->editar_conceptos) ? 'selected' : ''}}>No</option>
                                        <option value="1" {{($parametro->editar_conceptos) ? 'selected' : ''}}>Si</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="article border mb-4">
                        <label class="font-weight-bold center font-size-1-2em">Configuración de organigrama </label>
                        <hr>        
                        <form action="{{route('organigrama.asignar.configuracion')}}" method="POST" id="puestos_form">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-11">
                                        <select name="lleva_alias" id="lleva_alias" class="form-control input-style-custom mb-2" style="width: 100%!important;">
                                            @if($lleva_puestos_reales=="1" )
                                                <option value="1" selected >Si</option>
                                            @else
                                                <option value="0" selected >Seleccione si los puestos llevan alias: </option>
                                                <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-1 mt-2">
                                        <a data-toggle="collapse" href="#selectpuesto" role="button" aria-expanded="false" aria-controls="collapseExample">
                                            <img src="/img/icono-llenar-encuesta.png" class="button-style-icon" data-toggle="tooltip" title="Leer info." >
                                        </a>
                                    </div>
                                </div>
                                <div class="collapse" id="selectpuesto">
                                    <div class="card card-body ">
                                      <strong>LEER: Especificaciones de organigrama</strong> <br>
                                      Si selecciona que el organigrama llevará alías, afirma que los puestos tienen que llevar un identificador como alias ejemplo : <br><br>
                                      <b> Puesto : ASISTENTE ESPECIAL EN DESARROLLO SR = Desarrollador SR <br>
                                          Puesto : ASISTENTE ESPECIAL EN DESARRROLLO JR = Desarrollador web JR</b>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-11">
                                        <select name="lleva_rama" id="lleva_rama" class="form-control input-style-custom mb-2" style="width: 100%!important;" >
                                        
                                            @if($lleva_rama=="1" )
                                                <option value="1" selected >Si</option>
                                            @else
                                                <option value="0" selected >Seleccione si el organigrama lleva divición por ramas : </option>
                                                <option value="1">Si</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-1 mt-2">
                                        <a data-toggle="collapse" href="#selectrama" role="button" aria-expanded="false" aria-controls="collapseExample">
                                            <img src="/img/icono-llenar-encuesta.png" class="button-style-icon" data-toggle="tooltip" title="Leer info.">
                                        </a>
                                    </div>
                                </div>
                                <div class="collapse" id="selectrama">
                                    <div class="card card-body ">
                                      <strong>LEER: Especificaciones de organigrama</strong> <br>
                                      Si selecciona que el organigrama llevará rama, afirma que el organigrama será dividido por diferentes partes ejemplo:  <br><br>
                                      <b> Rama : Front - end <br>
                                          Rama : Back - end </b><br>
                                        
                                        Si no selecciona nada afirma que el organigrama será general 
                                    </div>
                                </div>
                            </div>
                            <!--<input type="hidden" name="pasos" value="0" readonly>
                            <div class="row justify-content-center">
                              <button type="button" class=" button-style" id="guardarbtn">Guardar</button>
                            </div>-->
                          </form>
                          
                    </div>
                </div>
            </div>
            @endforeach
        </form>
    </div>
    @include('includes.footer')
    <script type="text/javascript">
        $(function() {
            $("#edit_parametro").click(function() {
                var ejercicio = document.getElementById("ejercicio").value;
                var uma = document.getElementById("uma").value;
                var salario_minimo = document.getElementById("salario_minimo").value;
                var salario_maximo = document.getElementById("salario_maximo").value;
                var dias_aviso_contrato = document.getElementById("dias_aviso_contrato").value;
                var cuota_fija = document.getElementById("cuota_fija").value;
                var excedente_patro = document.getElementById("excedente_patro").value;
                var excedente_obrera = document.getElementById("excedente_obrera").value;
                var prestaciones_patronal = document.getElementById("prestaciones_patronal").value;
                var prestaciones_obrera = document.getElementById("prestaciones_obrera").value;
                var gastos_medi_patronal = document.getElementById("gastos_medi_patronal").value;
                var gastos_medi_obrera = document.getElementById("gastos_medi_obrera").value;
                var invalidez_patronal = document.getElementById("invalidez_patronal").value;
                var invalidez_obrera = document.getElementById("invalidez_obrera").value;
                var guarderia_presta_social = document.getElementById("guarderia_presta_social").value;
                var porcentaje_honorarios = document.getElementById("porcentaje_honorarios").value;
                var porcentaje_nomina = document.getElementById("porcentaje_nomina").value;
                var comision_variable = document.getElementById("comision_variable").value;
                var provision_valor = document.getElementById("provision_valor").value;
                var provision_porcentaje = document.getElementById("provision_porcentaje").value;
                var comision_mismo_dia = document.getElementById("comision_mismo_dia").value;
                var anticipo = document.getElementById("anticipo").value;
                var valor_prestacion_extra = document.getElementById("valor_prestacion_extra").value;
                var provision_obrero = document.getElementById("provision_obrero").value;
                var parametria = document.getElementById("parametria").value;
                var dias_aviso_contrato = document.getElementById("dias_aviso_contrato").value;
                var tipo_nomina = document.getElementById("tipo_nomina").value;
                var biometrico = document.getElementById("biometrico").value;
                var iva = document.getElementById("iva").value;
                var rvc_patronal_obrero = document.getElementById("rvc_patronal_obrero").value;

                if (ejercicio == " " || uma == " " || salario_minimo == " " || salario_maximo == " " || dias_aviso_contrato == " " || cuota_fija == " " || excedente_patro == " " || excedente_obrera == " " || prestaciones_patronal == " " || prestaciones_obrera == " " || gastos_medi_patronal == " " || gastos_medi_obrera == " " || invalidez_patronal == " " || invalidez_obrera == " " || guarderia_presta_social == " " || porcentaje_honorarios == " " || porcentaje_nomina == "" || comision_variable == " " || provision_valor == " " || provision_porcentaje == " " || comision_mismo_dia == " " || anticipo == " " || valor_prestacion_extra == " " || provision_obrero == " " || parametria == " " || dias_aviso_contrato == " " || tipo_nomina == " " || biometrico == " " || iva == " " || rvc_patronal_obrero == " ") {
                    swal({
                        title: "Para continuar debes agregar la información requerida",
                    });
                } else {
                    swal("Espere un momento, la información esta siendo procesada", {
                        icon: "success",
                        buttons: false,
                    });
                    setTimeout(submitForm, 1500);
                }
            });

            function submitForm() {
                document.getElementById("submit_parametro").submit()
            }


        });

        function file(val) {
            var text = val + "_text";
            document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;

        }
    </script>