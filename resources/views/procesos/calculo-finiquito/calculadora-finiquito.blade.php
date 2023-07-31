<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@include('includes.navbar')

<style type="text/css">
	.top-line-black {
	    width: 19%;}

    input[type=number], input[type=text], select{
        font-size: .85em;
    }

</style>



<div class="container">
	@include('includes.header',['title'=>'Calculo de Finiquito/Liquidación',
		        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
		        'route'=>'procesos.finiquito'])

<div>
    <div class="col-md-12 d-flex justify-content-between bg-warning px-4 py-4">
        <div class="">
            EMPLEADO: <strong>{{$empleado->id}}- {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</strong> <br>
            Periodo: <strong>{{$periodo->numero_periodo}}, del {{formatoAFecha($periodo->fecha_inicial_periodo)}} al {{formatoAFecha($periodo->fecha_final_periodo)}}</strong><br />
            Fecha baja: <strong>{{formatoAFecha($empleado->fecha_baja)}}</strong>
        </div>
        <div>
            Fecha del reporte: <br>
            <strong>{{formatoAFecha(date('Y-m-d H:i:s'), true)}}</strong>
        </div>
    </div>
    {{-- {{route('procesos.finiquito.calculadora.guardar')}} --}}
    <form action="{{ route('procesos.finiquitocalculadoraguardar') }}" class="w-100" method="post" id="calculadora">
        @csrf
        <input type="hidden" name="ejercicio" id="ejercicio" value="{{$valores_calculadora['ejercicio']}}">
        <input type="hidden" name="prima_riesgo_trabajo" id="prima_riesgo_trabajo" value="{{$claves_conceptos['imss']['primas_riesgo']}}">
        <input type="hidden" name="smg" id="smg" value="{{$claves_conceptos['imss']['primas_riesgo']}}">
        <input type="hidden" name="id_rutina" id="id_rutina" value="@if(isset($empleado->rutina->id)) {{$empleado->rutina->id}} @else {{0}} @endif">
        <input type="hidden" name="id_periodo" id="id_periodo" value="{{$periodo->id}}">
        <input type="hidden" name="id_empleado" id="id_empleado" value="{{$empleado->id}}">
        <input type="hidden" name="subsidio_al_empleado" id="subsidio_al_empleado" value="@if(isset($empleado->rutina->subsidio_al_empleo)) {{$empleado->rutina->subsidio_al_empleo}} @else {{0}} @endif">
        <input type="hidden" name="isr_o_subsidio" id="isr_o_subsidio">



        <div class="w-100  bg-warning px-4">
            <div class="row">
                <label class="col-md-1" for="fecha_antiguedad">Fecha antigüedad </label>
                <div class="col-md-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" aria-label="" name="fecha_usar" checked value="fecha_antiguedad">
                            </div>
                        </div>
                        <input type="text" name="fecha_antiguedad" id="fecha_antiguedad" value="{{$empleado->fecha_antiguedad}}" class="form-control" autocomplete="off" readonly>
                    </div>
                </div>

                <label class="col-md-1" for="fecha_alta">Fecha alta </label>
                <div class="col-md-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" aria-label="" name="fecha_usar" value="fecha_alta">
                            </div>
                        </div>
                        <input type="text" name="fecha_alta" id="fecha_alta" value="{{$empleado->fecha_alta}}" class="form-control" autocomplete="off" readonly>
                        <!--input type="text" class="form-control" aria-label="Text input with radio button"-->
                    </div>
                </div>

                <label class="col-md-1" for="ultimo_aniversario">Ultimo aniversario </label>
                <div class="col-md-2">
                    <input type="text" name="ultimo_aniversario" id="ultimo_aniversario" value="" class="form-control" autocomplete="off" readonly>
                </div>

                <label class="col-md-1" for="fecha_baja">Fecha baja </label>
                <div class="col-md-2">
                    <input type="text" name="fecha_baja" id="fecha_baja" value="{{$empleado->fecha_baja}}" class="form-control" autocomplete="off" readonly>
                </div>


            </div>

            <div class="row">

                <label class="col-md-1" for="">Salario diario </label>
                <div class="col-md-2">
                    <input type="number" name="salario_diario" id="salario_diario" value="{{$empleado->salario_diario}}" class="form-control">
                </div>

                <label class="col-md-1" for="">Salario diario integrado </label>
                <div class="col-md-2">
                    <input type="number" name="salario_diario_integrado" id="salario_diario_integrado" value="{{$empleado->salario_diario_integrado}}" class="form-control">
                </div>

                <label class="col-md-1" for="">Salario digital </label>
                <div class="col-md-2">
                    <input type="number" name="salario_digital" id="salario_digital" value="{{$empleado->salario_digital}}" class="form-control">
                </div>

                <label class="col-md-1" for="">Salario digital integrado</label>
                <div class="col-md-2">
                    <input type="number" step="0.001" name="salario_digital_integrado" id="salario_digital_integrado" value="{{$empleado->salario_digital}}" class="form-control">
                </div>

            </div>


            <div class="row">
                <label class="col-md-1" for="dias_desde_ultimo_aniversario">Días desde el ultimo aniversario</label>
                <div class="col-md-2">
                    <input type="number" name="dias_desde_ultimo_aniversario" id="dias_desde_ultimo_aniversario" value="{{$diaspagar}}" class="form-control" autocomplete="off" readonly>
                </div>
                <label class="col-md-1" for="dias_trabajados_periodo">Días del periodo</label>
                <div class="col-md-2">
                    <input type="number" name="dias_trabajados_periodo" id="dias_trabajados_periodo" value="@if(isset($valores_calculadora['dias_laborados'])){{$valores_calculadora['dias_laborados']}}@else {{0}} @endif" class="form-control" autocomplete="off">
                </div>
                <label class="col-md-1" for="dias_vacaciones">Días vacaciones</label>
                <div class="col-md-2">
                    <input type="number" name="dias_vacaciones" id="dias_vacaciones" value="@if(!empty($valores_calculadora['dias_vacaciones'])){{$valores_calculadora['dias_vacaciones']}}@else 0 @endif" class="form-control" autocomplete="off">
                </div>
                <label class="col-md-1" for="dias_vacaciones_pendientes">Días de vacaciones pendientes</label>
                <div class="col-md-2">
                    <input type="number" name="dias_vacaciones_pendientes" id="dias_vacaciones_pendientes" value="0" class="form-control" autocomplete="off">
                </div>
            </div>

            <div class="row">

                <label class="col-md-1" for="">Salario minimo</label>
                <div class="col-md-2">

                    <input type="number" name="salario_minimo" id="salario_minimo" class="form-control" value="{{$claves_conceptos['parametros_empresa']->salario_minimo}}" readonly>
                </div>

                <label class="col-md-1" for="">UMA</label>
                <div class="col-md-2">
                    <input type="number" name="uma" id="uma" class="form-control" value="{{$claves_conceptos['parametros_empresa']->uma}}" readonly>
                </div>

                <label class="col-md-1" for="">Prima vacacional</label>
                <div class="col-md-2">
                    <input type="number" name="prima_vacacional" id="prima_vacacional" class="form-control" value="@if(!empty($valores_calculadora['prima'])){{$valores_calculadora['prima']}}@else 0 @endif" readonly>
                </div>

                <label class="col-md-1" for="">Factor integración</label>
                <div class="col-md-2">
                    <input type="number" name="factor_integracion" id="factor_integracion" class="form-control" value="{{ $valores_calculadora['factor_integracion'] }}" readonly>
                </div>



            </div>
            <div class="row">

                <label class="col-md-1" for="antiguedad">Antigüedad </label>
                <div class="col-md-2">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" value="{{$anios_antiguedad}}" readonly name="antiguedad" id="antiguedad" aria-label="">
                        <div class="input-group-append">
                            <span class="input-group-text">Años</span>
                        </div>
                    </div>
                </div>


                <label class="col-md-1" for="">Calcular</label>
                <div class="col-md-2">
                    <select class="form-control" name="calcular" id="calcular">
                        <option value="1">Finiquito</option>
                        <option value="2">Liquidación</option>
                        <!--option value="3">Liquidación</option-->
                    </select>
                </div>

            </div>

            <div class="row">
                <div class="col-md-2 offset-5  my-4">
                <center>
                    <button class="btn btn-warning active tooltip_" title="Calcular valores" data-toggle="tooltip" name="btn_calcular" id="btn_calcular"><i class="fa fa-calculator fa-5x" aria-hidden="true"></i></button>
                </center>
                </div>
            </div>

        </div>


        <div class="row p-4 ">
            <div class="col-md-6 text-white bg-dark">
                <h3 class="text-white mt-2">Percepciones</h3>
                <h4 class="text-warning">Finiquito</h4>

                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0"></label>
                    <div class="col-md-8 row">
                        <div class="bg-transparent col text-center text-warning">Fiscal</div>
                        <div class="bg-transparent col text-center text-warning">Compensación</div>
                        <div class="bg-transparent col text-center text-warning">Gravado</div>
                        <div class="bg-transparent col text-center text-warning">Exento</div>
                    </div>
                </div>


                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0">Salario del periodo</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" value="{{$claves_conceptos['sueldo']['valor']}}" name="total{{$claves_conceptos['sueldo']['id']}}" id="total{{$claves_conceptos['sueldo']['id']}}">
                        <input type="number" class="form-control compensacion" value="{{$claves_conceptos['sueldo']['compensacion']}}" id="total{{$claves_conceptos['sueldo']['id']}}_compensacion">
                        <input type="number" class="form-control gravado" value="{{$claves_conceptos['sueldo']['valor']}}" name="gravado{{$claves_conceptos['sueldo']['id']}}" id="gravado{{$claves_conceptos['sueldo']['id']}}">
                        <input type="number" class="form-control" value="" name="" id="" readonly>

                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="mb-0 col-md-4">Aguinaldo</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" data-id="aguinaldo" value="{{$claves_conceptos['aguinaldo']['valor']}}" name="total{{$claves_conceptos['aguinaldo']['id']}}" id="total{{$claves_conceptos['aguinaldo']['id']}}">
                        <input type="number" class="form-control compensacion" value="{{$claves_conceptos['aguinaldo']['compensacion']}}" id="total{{$claves_conceptos['aguinaldo']['id']}}_compensacion">
                        <input type="number" class="form-control gravado" data-id="aguinaldo" value="0" name="gravado{{$claves_conceptos['aguinaldo']['id']}}" id="gravado{{$claves_conceptos['aguinaldo']['id']}}">
                        <input type="number" class="form-control exento" data-id="aguinaldo" value="0" name="exento{{$claves_conceptos['aguinaldo']['id']}}" id="exento{{$claves_conceptos['aguinaldo']['id']}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0">Vacaciones</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" value="{{$claves_conceptos['vacaciones']['valor']}}" name="total{{$claves_conceptos['vacaciones']['id']}}" id="total{{$claves_conceptos['vacaciones']['id']}}">
                        <input type="number" class="form-control compensacion" value="{{$claves_conceptos['vacaciones']['compensacion']}}" id="total{{$claves_conceptos['vacaciones']['id']}}_compensacion">
                        <input type="number" class="form-control gravado " value="{{$claves_conceptos['vacaciones']['valor']}}" name="gravado{{$claves_conceptos['vacaciones']['id']}}" id="gravado{{$claves_conceptos['vacaciones']['id']}}">
                        <input type="number" class="form-control" value="" name="" id="" readonly>

                    </div>
                </div>


                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0">Vacaciones pendientes</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" name="totalvacaciones_pendientes" id="totalvacaciones_pendientes">
                        <input type="number" class="form-control compensacion" id="vacaciones_pendientes_compensacion">
                        <input type="number" class="form-control gravado" name="gravadovacaciones_pendientes" id="gravadovacaciones_pendientes">
                        <input type="number" class="form-control" value="" name="" id="" readonly>

                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0">Prima vacacional</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" data-id="prima" value="{{$claves_conceptos['prima']['valor']}}" name="total{{$claves_conceptos['prima']['id']}}" id="total{{$claves_conceptos['prima']['id']}}">
                        <input type="number" class="form-control compensacion" value="{{$claves_conceptos['prima']['compensacion']}}" name="total{{$claves_conceptos['prima']['id']}}_compensacion" id="total{{$claves_conceptos['prima']['id']}}_compensacion">
                        <input type="number" class="form-control gravado" data-id="prima" value="0" name="gravado{{$claves_conceptos['prima']['id']}}" id="gravado{{$claves_conceptos['prima']['id']}}">
                        <input type="number" class="form-control exento" data-id="prima" value="0" name="exento{{$claves_conceptos['prima']['id']}}" id="exento{{$claves_conceptos['prima']['id']}}">
                    </div>
                </div>

                

                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0">Compensación</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control finiquito fiscal" value="@if(!empty($claves_conceptos['compensacion']['valor'])){{$claves_conceptos['compensacion']['valor']}}@else 0 @endif" name="@if(!empty($claves_conceptos['compensacion']['id'])){{'total'.$claves_conceptos['compensacion']['id']}}@else 0 @endif" id="@if(!empty($claves_conceptos['compensacion']['id'])){{'total'.$claves_conceptos['compensacion']['id']}}@else 0 @endif">
                        <input type="number" class="form-control" value="" id="" readonly>
                        <input type="number" class="form-control gravado" value="@if(!empty($claves_conceptos['compensacion']['valor'])){{$claves_conceptos['compensacion']['valor']}}@else 0 @endif" name="@if(!empty($claves_conceptos['compensacion']['id'])){{'gravado'.$claves_conceptos['compensacion']['id']}}@else 0 @endif" id="@if(!empty($claves_conceptos['compensacion']['id'])){{'gravado'.$claves_conceptos['compensacion']['id']}}@else 0 @endif">
                        <input type="number" class="form-control" value="" name="" id="" readonly>

                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0 text-warning">Subtotal</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control" value="" name="" id="" readonly>
                        <input type="number" class="form-control" value="" name="" id="" readonly>
                        <input type="number" class="form-control" value="@if(isset($empleado->rutina->total_gravado)) {{$empleado->rutina->total_gravado}} @else {{0}} @endif" name="total_gravado" id="total_gravado">
                        <input type="number" class="form-control" value="" name="total_exento" id="total_exento">

                    </div>
                </div>
                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0 text-warning">Total</label>
                    <div class="input-group col-md-4">
                        <input type="number" class="form-control" name="subtotal_finiquito" id="subtotal_finiquito" required>
                        <input type="number" class="form-control" value="" name="" id="" readonly>


                    </div>
                    <div class="input-group col-md-4">
                        <input type="number" class="form-control" value="suma_gravado_exento" name="suma_gravado_exento" id="suma_gravado_exento" readonly>
                    </div>
                </div>



            </div>
            <div class="col-md-3 text-white bg-dark">
                <h3 class="text-success">&nbsp;</h3>
                <h4 class="text-warning pb-5">Liquidación</h4>


                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">Indemnización</label>
                    <div class="input-group col-md-7">
                        <!--input type="number" class="form-control" value="" id=""-->
                        <input type="number" class="form-control liquidacion" value="@if(!empty($claves_conceptos['indemnizacion']['valor'])){{$claves_conceptos['indemnizacion']['valor']}}@else{{0}}@endif" id="@if(!empty($claves_conceptos['indemnizacion']['id'])){{'total'.$claves_conceptos['indemnizacion']['id']}}@else{{'indemnizacion'}}@endif" name="@if(!empty($claves_conceptos['indemnizacion']['id'])){{'total'.$claves_conceptos['indemnizacion']['id']}}@else{{'indemnizacion'}}@endif">
                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0" for="prima_antiguedad">Prima antigüedad</label>
                    <div class="input-group col-md-7">
                        <!--input type="number" class="form-control" value="" id=""-->
                        <input type="number" class="form-control liquidacion" value="@if(!empty($claves_conceptos['priantig']['valor'])){{$claves_conceptos['priantig']['valor']}}@else{{0}}@endif" id="@if(!empty($claves_conceptos['priantig']['id'])){{'total'.$claves_conceptos['priantig']['id']}}@else{{'priantig'}}@endif" name="@if(!empty($claves_conceptos['priantig']['id'])){{'total'.$claves_conceptos['priantig']['id']}}@else{{'priantig'}}@endif">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="veinte_dias_por_anio" class="col-md-5 mb-0">20 días por año</label>
                    <div class="input-group col-md-7">
                       <!--input type="number" class="form-control" value="" id=""-->
                        <input type="number" class="form-control liquidacion" value="@if(!empty($claves_conceptos['veinte_dias_por_anio']['valor'])){{$claves_conceptos['veinte_dias_por_anio']['valor']}}@else{{0}}@endif" id="@if(!empty($claves_conceptos['veinte_dias_por_anio']['id'])){{'total'.$claves_conceptos['veinte_dias_por_anio']['id']}}@else{{'veinte_dias_por_anio'}}@endif" name="@if(!empty($claves_conceptos['veinte_dias_por_anio']['id'])){{'total'.$claves_conceptos['veinte_dias_por_anio']['id']}}@else{{'veinte_dias_por_anio'}}@endif">
                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0 text-warning">Subtotal</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control" name="subtotal_liquidacion" id="subtotal_liquidacion" required>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-white bg-secondary">
                <h3 class="text-white mt-2">Deducciones</h3>
                <h4 class="text-warning pb-5">&nbsp;</h4>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">ISR</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control deducciones" value="@if(!empty($claves_conceptos['isr']['valor'])) {{$claves_conceptos['isr']['valor']}} @else 0 @endif" name="total{{$claves_conceptos['isr']['id']}}" id="total{{$claves_conceptos['isr']['id']}}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="isr_liquidacion_deducciones" class="col-md-5 mb-0">ISR por liquidación</label>
                    <div class="input-group col-md-7">
                       <input type="number" class="form-control deducciones" value="0" id="isr_liquidacion_deducciones" name="isr_liquidacion_deducciones">
                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">IMSS </label>
                    <div class="input-group col-md-7">
                    <input type="number" class="form-control deducciones" value="@if(!empty($claves_conceptos['imss']['valor'])) {{$claves_conceptos['imss']['valor']}} @else 0 @endif" name="total{{$claves_conceptos['imss']['id']}}" id="total{{$claves_conceptos['imss']['id']}}">
                    </div>
                </div>


                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">Otros descuentos</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control deducciones" value="@if(!empty($claves_conceptos['otros']['valor'])) {{$claves_conceptos['otros']['valor']}} @else 0 @endif" id="@if(!empty($claves_conceptos['otros']['id'])){{'total'.$claves_conceptos['otros']['id']}}@else otros @endif" name="@if(!empty($claves_conceptos['otros']['id'])){{'total'.$claves_conceptos['otros']['id']}}@else otros @endif">
                    </div>
                </div>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">Infonavit</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control deducciones" value="@if(!empty($claves_conceptos['infonavit']['valor'])) {{$claves_conceptos['infonavit']['valor']}} @else 0 @endif" id="@if(!empty($claves_conceptos['infonavit']['id'])){{'total'.$claves_conceptos['infonavit']['id']}}@else infonavit @endif" name="@if(!empty($claves_conceptos['infonavit']['id'])){{'total'.$claves_conceptos['infonavit']['id']}}@else infonavit @endif">
                    </div>

                </div>

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0">Sueldos pagados en demasia</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control deducciones" value="@if(!empty($claves_conceptos['demasia']['valor'])) {{$claves_conceptos['demasia']['valor']}} @else 0 @endif" name="@if(!empty($claves_conceptos['demasia']['id'])){{'total'.$claves_conceptos['demasia']['id']}}@else 'demasia' @endif" id="@if(!empty($claves_conceptos['demasia']['id'])){{'total'.$claves_conceptos['demasia']['id']}}@else 'demasia' @endif">
                    </div>
                </div>

               

                <div class="form-group row">
                    <label id="" class="col-md-5 mb-0 text-warning">Subtotal</label>
                    <div class="input-group col-md-7">
                        <input type="number" class="form-control" name="subtotal_deducciones" id="subtotal_deducciones" required>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-white bg-dark">
            </div>
            <div class="col-md-5 text-white bg-dark">
                <div class="form-group row">
                    <label id="" class="col-md-4 mb-0 text-warning">Total a depositar</label>
                    <div class="input-group col-md-8">
                        <input type="number" class="form-control" name="neto_fiscal" id="neto_fiscal" required>
                    </div>
                </div>


                <div class="form-group row">
                    <div class="input-group col-md-12">
                        <button type="button" class="btn btn-warning btn-lg btn-block" id="btn-guardar" >Guardar</button>

                    </div>
                </div>

            </div>
            <div class="col-md-3 text-white bg-secondary">
            </div>
        </div>

        <h3>Calculos ISR</h3>
        <div class="row" id="">
            <div class="col-md-4 px-0">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">ISR Mensual</h5>
                        @if(!empty($valores_calculadora['impuestos']))
                        <table id="impuestos" class="impuestos table table-sm" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th>Limite inferior</th>
                                    <th>Limite superior</th>
                                    <th>cuota fija</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>

                            @foreach($valores_calculadora['impuestos'] as $impuesto)
                            <tbody>
                                <tr id="{{$impuesto->id}}">
                                    <td class="limite_inferior">{{$impuesto->limite_inferior}}</td>
                                    <td class="limite_superior">{{$impuesto->limite_superior}}</td>
                                    <td class="cuota_fija">{{$impuesto->cuota_fija}}</td>
                                    <td class="porcentaje">{{$impuesto->porcentaje}}</td>
                                </tr>
                            </tbody>
                            @endforeach
                        </table>

                        @else
                        No hay tabla Mensual de impuestos
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 px-0">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subsidio Mensual</h5>
                        @if(!empty($valores_calculadora['subsidios']))
                        <table id="subsidio" class="subsidios table table-sm" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th>Ingreso desde</th>
                                    <th>Ingreso hasta</th>
                                    <th>subsidio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($valores_calculadora['subsidios'] as $subsidio)
                                <tr id="{{$subsidio->id}}">
                                    <td class="ingreso_desde">{{$subsidio->ingreso_desde}}</td>
                                    <td class="ingreso_hasta">{{$subsidio->ingreso_hasta}}</td>
                                    <td class="subsidio">{{$subsidio->subsidio}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @else
                        No hay tabla Mensual de subsidios
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-2 px-0">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">CALCULO ISR FINIQUITO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>GRAVADO</th>
                                    <td id="gravado"></td>
                                </tr>
                                <tr>
                                    <th>(-) LIM INFERIOR</th>
                                    <td id="lim_inferior"></td>
                                </tr>
                                <tr>
                                    <th>EXCEDENTE LIM INFERIOR</th>
                                    <td id="excedente_lim_inferior"></td>
                                </tr>
                                <tr>
                                    <th>TASA IMPUESTO</th>
                                    <td id="tasa_impuesto"></td>
                                </tr>
                                <tr>
                                    <th>IMP MARGINAL</th>
                                    <td id="imp_marginal"></td>
                                </tr>
                                <tr>
                                    <th>CUOTA FIJA</th>
                                    <td id="cuota_fija_finiquito"></td>
                                </tr>
                                <tr class="text-white bg-secondary">
                                    <th>ISR</th>
                                    <td id="isr"></td>
                                </tr>
                                <tr>
                                    <th>(-) SUBSIDIO</th>
                                    <td id="menos_subsidio"></td>
                                </tr>
                                <tr>
                                    <th>ISR O SUBSIDIO</th>
                                    <td id="isrosubsidio"></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="col-md-3 px-0">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">CALCULO ISR LIQUIDACIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>SUELDO MENSUAL GRAVADO</th>
                                    <td id="sueldo_mensual_gravado"></td>
                                </tr>
                                <tr>
                                    <th>(-) LIM INFERIOR</th>
                                    <td id="lim_inferior_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>EXCEDENTE LIM INFERIOR</th>
                                    <td id="excedente_lim_inferior_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>TASA IMPUESTO</th>
                                    <td id="tasa_impuesto_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>IMP MARGINAL</th>
                                    <td id="imp_marginal_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>CUOTA FIJA</th>
                                    <td id="cuota_fija_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>ISR</th>
                                    <td id="isr_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>(-) SUBSIDIO</th>
                                    <td id="menos_subsidio_liquidacion"></td>
                                </tr>
                                <tr>
                                    <th>ISR O SUBSIDIO</th>
                                    <td id="isrosubsidio_liquidacion"></td>
                                </tr>

                                <tr>
                                    <th>BASE GRAVABLE DEL IMPUESTO</th>
                                    <td id="base_gravable_del_impuesto"></td>
                                </tr>
                                <tr>
                                    <th>TASA IMPUESTO LIQUIDACION</th>
                                    <td id="tasa_impuesto_liquidacion_2"></td>
                                </tr>
                                <tr class="text-white bg-secondary">
                                    <th> ISR DE LA LIQUIDACIÓN</th>
                                    <td id="isr_de_la_liquidacion"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <br /><br />
        <h3>Calculos IMSS</h3>
        <div class="row" id="collapseImss">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <table id="cuotas_imss" class="table table-sm" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <td colspan="2">DETERMINACIÓN DE CUOTAS OBRERO-PATRONALES</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="">
                                    <th>SMG</th>
                                    <td id="imss_smg"></td>
                                </tr>
                                <tr>
                                    <th>UMA</th>
                                    <td id="imss_uma"></td>
                                </tr>
                                <tr>
                                    <th>3 VS UMA</th>
                                    <td id="imss_3vsuma"></td>
                                </tr>
                                <tr>
                                    <th>PRIMA R. T.</th>
                                    <td id="imss_primart"></td>
                                </tr>
                                <tr>
                                    <th>DÍAS DEL BIMESTRE</th>
                                    <td id="imss_dias_del_bimestre"></td>
                                </tr>
                                <tr>
                                    <th>LIMITE SUPERIOR 21 SMG</th>
                                    <td id="imss_limite_superior_21_smg"></td>
                                </tr>
                                <tr>
                                    <th>LIMITE SUPERIOR 25 SMG</th>
                                    <td id="imss_limite_superior_25_smg"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm w-100 text-center table-bordered" style="font-size:11px;">
                            <tr>
                                <th colspan="7" class="bg-dark text-white">ENFERMEDADES Y MATERNIDAD</th>
                            </tr>
                            <tr class="bg-warning ">
                                <th>C. FIJA PATRON</th>
                                <th colspan="2">EXCED. 3 SMG</th>
                                <th colspan="2">P. EN DINERO</th>
                                <th colspan="2">G. MED.</th>
                            </tr>
                            <tr class="bg-warning">
                                <th>PATRON</th>
                                <th>PATRON</th>
                                <th>TRAB</th>
                                <th>PATRON</th>
                                <th>TRAB</th>
                                <th>PATRON</th>
                                <th>TRAB</th>

                            </tr>
                            <tr>
                                <th><input type="number" class="form-control" name="porcentaje_cuota_fija" id="porcentaje_cuota_fija" value="{{$claves_conceptos['parametros_empresa']->cuota_fija}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_exce_pa" id="porcentaje_exce_pa" value="{{$claves_conceptos['parametros_empresa']->excedente_patro}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_exce_ob" id="porcentaje_exce_ob" value="{{$claves_conceptos['parametros_empresa']->excedente_obrera}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_pre_dine_patro" id="porcentaje_pre_dine_patro" value="{{$claves_conceptos['parametros_empresa']->prestaciones_patronal}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_pre_dine_obre" id="porcentaje_pre_dine_obre" value="{{$claves_conceptos['parametros_empresa']->prestaciones_obrera}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_gas_medi_patro" id="porcentaje_gas_medi_patro" value="{{$claves_conceptos['parametros_empresa']->gastos_medi_patronal}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_gas_medi_obre" id="porcentaje_gas_medi_obre" value="{{$claves_conceptos['parametros_empresa']->gastos_medi_obrera}}" /></th>

                            </tr>
                            <tr>
                                <td><input type="number" class="form-control suma_cuotas patron" id="cuota_fija" name="cuota_fija" value="{{$claves_conceptos['imss']['cuota_fija']}}"></td>
                                <td><input type="number" class="form-control suma_cuotas patron" id="exce_pa" name="exce_pa"></td>
                                <td><input type="number" class="form-control suma_cuotas obrera" id="exce_ob" name="exce_ob"></td>
                                <td><input type="number" class="form-control suma_cuotas patron" id="pre_dine_patro" name="pre_dine_patro"></td>
                                <td><input type="number" class="form-control suma_cuotas obrera" id="pre_dine_obre" name="pre_dine_obre"></td>
                                <td><input type="number" class="form-control suma_cuotas patron" id="gas_medi_patro" name="gas_medi_patro"></td>
                                <td><input type="number" class="form-control suma_cuotas obrera" id="gas_medi_obre" name="gas_medi_obre"></td>
                            </tr>
                        </table>
                        <br>
                        <table class="table table-sm w-100 text-center table-bordered" style="font-size:11px;">
                            <tr class="bg-dark text-white">
                                <th>RIESGO DE TRABAJO</th>
                                <th colspan="2">INVALIDEZ Y VIDA</th>
                                <th>GUARDER</th>
                                <th rowspan="3">SUMA</th>
                                <th rowspan="3">TOTALES PATRON</th>
                                <th rowspan="3">TOTALES TRABAJADOR</th>
                            </tr>
                            <tr class="bg-warning">
                                <th>PATRON</th>
                                <th>PATRON</th>
                                <th>TRAB</th>
                                <th>PATRON</th>
                            </tr>
                            <tr>
                                <th><input type="number" class="form-control" name="porcentaje_riesgo_trabajo" id="porcentaje_riesgo_trabajo" value="{{$claves_conceptos['imss']['primas_riesgo']}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_inva_vida_patro" id="porcentaje_inva_vida_patro" value="{{$claves_conceptos['parametros_empresa']->invalidez_patronal}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_inva_vida_obre" id="porcentaje_inva_vida_obre" value="{{$claves_conceptos['parametros_empresa']->invalidez_obrera}}" /></th>
                                <th><input type="number" class="form-control" name="porcentaje_guarde_presta" id="porcentaje_guarde_presta" value="{{$claves_conceptos['parametros_empresa']->guarderia_presta_social}}" /></th>


                            </tr>
                            <tr>
                                <td><input type="number" class="form-control suma_cuotas patron" id="riesgo_trabajo" name="riesgo_trabajo"></td>
                                <td><input type="number" class="form-control suma_cuotas patron" id="inva_vida_patro" name="inva_vida_patro"></td>
                                <td><input type="number" class="form-control suma_cuotas obrera" id="inva_vida_obre" name="inva_vida_obre"></td>
                                <td><input type="number" class="form-control suma_cuotas patron" id="guarde_presta" name="guarde_presta"></td>
                                <td><input type="number" class="form-control" id="suma_cuotas" name="suma_cuotas"></td>
                                <td><input type="number" class="form-control patron_cesantia total_patron_imss" id="total_patron" name="total_patron"></td>
                                <td><input type="number" class="form-control obrera_cesantia total_obrera_imss" id="total_obrera" name="total_obrera"></td>
                            </tr>
                        </table>

                        <!-- ---------------- C E S A N T I A  Y  V E J E Z --------------------------------------------->
                        <br>
                        <table class="table table-sm w-100 text-center table-bordered" style="font-size:11px;">
                            <tr class="bg-dark text-white">
                                <th>SAR</th>
                                <th>INFONAVIT</th>
                                <th colspan="2">CESANTÍA Y VEJEZ</th>
                                <th rowspan="3">TOTAL</th>
                                <th rowspan="3">TOTAL RET. IMSS TRABAJADOR</th>
                                <th rowspan="3">TOTAL IMSS PATRON </th>
                                <th rowspan="3">TOTAL MENSUAL</th>
                            </tr>
                            <tr class="bg-warning">
                                <th>PATRON</th>
                                <th>PATRON</th>
                                <th>PATRON</th>
                                <th>TRAB</th>
                            </tr>
                            <tr>
                                <th><input type="number" class="form-control patron_cesantia" name="porcentaje_sar_patron" id="porcentaje_sar_patron" value="0.02" /></th>
                                <th><input type="number" class="form-control patron_cesantia" name="porcentaje_infonavit_patro" id="porcentaje_infonavit_patro" value="0.05" /></th>
                                <th><input type="number" class="form-control patron_cesantia" name="porcentaje_censa_vejez_patron" id="porcentaje_censa_vejez_patron" value="0.03150" /></th>
                                <th><input type="number" class="form-control obrera_cesantia" name="porcentaje_censa_vejez_obre" id="porcentaje_censa_vejez_obre" value="0.01125" /></th>


                            </tr>
                            <tr>
                                <td><input type="number" class="form-control suma_cuotas_cesantiayvejez total_patron_imss" id="sar_patron" name="sar_patron"></td>
                                <td><input type="number" class="form-control suma_cuotas_cesantiayvejez total_patron_imss" id="infonavit_patro" name="infonavit_patro"></td>
                                <td><input type="number" class="form-control suma_cuotas_cesantiayvejez total_patron_imss" id="censa_vejez_patron" name="censa_vejez_patron"></td>
                                <td><input type="number" class="form-control suma_cuotas_cesantiayvejez total_obrera_imss" id="censa_vejez_obre" name="censa_vejez_obre"></td>
                                <td><input type="number" class="form-control" id="suma_cuotas_cesantiayvejez" name="suma_cuotas_cesantiayvejez"></td>
                                <td><input type="number" class="form-control" id="total_obrera_imss" name="total_obrera_imss"></td>
                                <td><input type="number" class="form-control" id="total_patron_imss" name="total_patron_imss"></td>
                                <td><input type="number" class="form-control" id="total_mensual" name="total_mensual"></td>

                            </tr>
                        </table>
                    </div>
                </div>
            </div>



        </div>


	</form>
</div>

</div>
@include('includes.footer')


<script src="{{asset('js/moment/moment.js')}}"></script>
<script src="{{asset('js/moment/es.js')}}"></script>

<script>
    $('.collapse').collapse('hide');
    $(function() {
        var sdi = Math.round10($("#factor_integracion").val() * $("#salario_digital").val(), -2);
        //alert(sdi);
        $("#salario_digital_integrado").val(sdi);
        subtotalLiquidacion();
        subtotalFiniquito();
        subtotalDeducciones();
        calcula_total();

        $("#calcular").on("change", function() {
            liquidacion();
            subtotalLiquidacion();
            subtotalFiniquito();
            subtotalDeducciones();
            calcula_total();

        });

        $("#btn_calcular").on("click", function(e) {
            e.preventDefault();
            $("#spinner").removeClass("ocultar");
            calculadora();
            $("#spinner").addClass("ocultar");
            // alertify.success('Se actualizaron los valores con éxito');

			swal({
			  text: "Se actualizaron los valores con éxito",
			  icon: "success",
			  button: "Ok",
			});


            return false;
        });

        $("#btn-guardar").on("click", function(){

			swal({
				title: "¿Esta seguro de guardar el finiquito?",
			  	text: "Una vez hecho, no podrá modificarse!",
			  	icon: "warning",
			  	buttons: true,
			  	dangerMode: true,
			})
			.then((willDelete) => {
  				if (willDelete) {
    				swal("El finiquito se guardo correctamente", {
      					icon: "success",
    				});
    				enviarCalculadora()

  				} else {
    				swal("Acción cancelada");
  				}
			});


        //     alertify.confirm('Multimedia ','¿Esta seguro de guardar el finiquito?, una vez hecho, no podrá modificarse',
        //         function(){ enviarCalculadora();},
        //         function(){ alertify.alert().close(); }
        // );
            
            return false;
        });


        eventos();

    });

    function enviarCalculadora(){

            var btnEnviar = $("#btn-guardar");
            //var url = "{{route('procesos.finiquitocalculadoraguardar')}}";
            var img = "{{asset('public/img/spinner.gif')}}";
            btnEnviar.html("<img src='"+img+"' style='width:20px' />"); // Para input de tipo button
           //         btnEnviar.attr("disabled","disabled");
            $("#calculadora").submit();
      
    }

    function calculadora() {
        //Finiquito
        salario();
        vacaciones();
        aguinaldo();
        compensacion();
        subtotalFiniquito();
        totalGravado();
        totalExento();
        //liquidacion
        liquidacion();
        subtotalLiquidacion();
        //deducciones
        isr();
        isr_liquidacion();
        imss();
        subtotalDeducciones();
        //total
        calcula_total();
        calcula_total_gravado_exento();
        valida_listo_guardar();
    }

    function calcula_total() {
        var subtotal_finiquito = parseFloat(($('#subtotal_finiquito').val()) ? $('#subtotal_finiquito').val() : 0);
        var subtotal_liquidacion = parseFloat(($('#subtotal_liquidacion').val()) ? $('#subtotal_liquidacion').val() : 0);
        var subtotal_deducciones = parseFloat(($('#subtotal_deducciones').val()) ? $('#subtotal_deducciones').val() : 0);

        var total = Math.round10(((subtotal_finiquito + subtotal_liquidacion) - subtotal_deducciones), -2);
        $("#neto_fiscal").val(total);
        
    }

    function calcula_total_gravado_exento() {
        var total_gravado = parseFloat(($('#total_gravado').val()) ? $('#total_gravado').val() : 0);
        var total_exento = parseFloat(($('#total_exento').val()) ? $('#total_exento').val() : 0);
        var total_gravado_exento = Math.round10(((total_gravado + total_exento)), -2);
        $("#suma_gravado_exento").val(total_gravado_exento);
        //alert(subtotal_finiquito);
        
    }

    function valida_listo_guardar(){
        var subtotal_finiquito = $("#subtotal_finiquito").val();
        var suma_gravado_exento = $("#suma_gravado_exento").val();
        var btnEnviar = $("#btn-guardar");

        if(subtotal_finiquito == suma_gravado_exento){
            btnEnviar.html(' <span class="fa fa-check text-success"></span> Guardar').attr('disabled',false); // Para input de tipo button
        }else{
            btnEnviar.html('<span class="fa fa-times text-danger"></span><span style="font-size:13px;"> La suma del total Gravado y total Exento no coincide con el total Fiscal</span>').attr('disabled',true); // Para input de tipo button
        }
    }

    function subtotalLiquidacion() {
        var subtotal = 0;
        $('.liquidacion').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            subtotal += tmp;
        });
        $('#subtotal_liquidacion').val(Math.round10(subtotal, -2));
        isr_liquidacion();
        subtotalDeducciones();
    }

    function subtotalFiniquito() {
        var subtotal = 0;
        $('.finiquito').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            subtotal += tmp;
        });
        $('#subtotal_finiquito').val(Math.round10(subtotal, -2));
    }

    function subtotalDeducciones() {
        var subtotal = 0;
        $('.deducciones').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            subtotal += tmp;
        });
        $('#subtotal_deducciones').val(Math.round10(subtotal, -2));
    }

    function totalGravado() {
        var total_gravado = 0;
        $('.gravado').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            total_gravado += tmp;
        });
        $('#total_gravado').val(Math.round10(total_gravado, -2));
        isr();
        subtotalDeducciones();
    }

    function totalExento() {
        var total_exento = 0;
        $('.exento').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            total_exento += tmp;
        });
        $('#total_exento').val(Math.round10(total_exento, -2));
    }

    function salario() {
        var salario_diario = $("#salario_diario").val();
        var salario_digital = $("#salario_digital").val();
        var dias_laborados = $("#dias_trabajados_periodo").val();
        var salario = Math.round10(salario_diario * dias_laborados, -2);
        var salario_compensacion = Math.round10((salario_digital * dias_laborados) - salario, -2);

        $("#total{{$claves_conceptos['sueldo']['id']}}").val(salario);
        $("#gravado{{$claves_conceptos['sueldo']['id']}}").val(salario);
        $("#total{{$claves_conceptos['sueldo']['id']}}_compensacion").val(salario_compensacion);
    }


    function vacaciones() {
        var fecha_usar = $('input:radio[name=fecha_usar]:checked').val();
        //var anios_antiguedad    =  $("#antiguedad").val();
        var salario_diario = $("#salario_diario").val();
        var salario_digital = $("#salario_digital").val();
        var dias_laborados = $("#dias_trabajados_periodo").val();
        var fecha_usar_moment = moment($("#" + fecha_usar).val());
        var fecha_baja_moment = moment($("#fecha_baja").val());
        var ejercicio = $("#ejercicio").val();
        var anios_antiguedad = fecha_baja_moment.diff(fecha_usar_moment, 'years');
        var dias_vacaciones = $("#dias_vacaciones").val();

        $("#antiguedad").val(anios_antiguedad);
        if (anios_antiguedad > 0) {
            var fecha_contabilizar = moment(ejercicio + '-' + fecha_usar_moment.format('MM') + '-' + fecha_usar_moment.format('DD'));
            if (fecha_contabilizar > fecha_baja_moment) {
                fecha_contabilizar = moment((ejercicio - 1) + '-' + fecha_usar_moment.format('MM') + '-' + fecha_usar_moment.format('DD'));
            }
            //var fecha_contabilizar =  moment($("#ultimo_aniversario").val());
        } else {
            var fecha_contabilizar = fecha_usar_moment;
        }

        $("#ultimo_aniversario").val(fecha_contabilizar.format('YYYY-MM-DD'));
        var diaspagar = fecha_baja_moment.diff(fecha_contabilizar, 'days') + 1;
        var diasvaca = Math.round10((diaspagar * dias_vacaciones) / 365, -2);
        var vacaciones_fiscal = Math.round10(diasvaca * salario_diario, -2);
        var vacaciones_total = Math.round10(diasvaca * salario_digital, -2);
        var vacaciones_compensacion = Math.round10(vacaciones_total - vacaciones_fiscal, -2);
        $("#total{{$claves_conceptos['vacaciones']['id']}}").val(vacaciones_fiscal);
        $("#gravado{{$claves_conceptos['vacaciones']['id']}}").val(vacaciones_fiscal);
        $("#total{{$claves_conceptos['vacaciones']['id']}}_compensacion").val(vacaciones_compensacion);

        /* -----V A C A C I O N E S   P E N D I E N T E S ------------------------------------------------------------------------------------------------------------- */
        var vacaciones_pendientes = $("#dias_vacaciones_pendientes").val();
        var vacaciones_pendientes_fiscal = Math.round10(vacaciones_pendientes * salario_diario, -2);
        var vacaciones_pendientes_total = Math.round10(vacaciones_pendientes * salario_digital, -2);
        var vacaciones_pendientes_compensacion = Math.round10(vacaciones_pendientes_total - vacaciones_pendientes_fiscal, -2);

        $("#totalvacaciones_pendientes").val(vacaciones_pendientes_fiscal);
        $("#gravadovacaciones_pendientes").val(vacaciones_pendientes_fiscal);
        $("#vacaciones_pendientes_compensacion").val(vacaciones_pendientes_compensacion);

        /* -----P R I M A   V A C A C I O N A L----------------------------------------------------------------------------------------------- */
        var prima_vacacional = $("#prima_vacacional").val();
        var prima = Math.round10(diasvaca * prima_vacacional, -2);
        var pvac_fiscal = Math.round10(salario_diario * prima, -2);
        var pvacreal = Math.round10((diasvaca * salario_digital) * prima_vacacional, -2);
        var pvac_compensacion = Math.round10(pvacreal - pvac_fiscal, -2);
        var uma = $("#uma").val();

        var uma15 = Math.round10((uma * 15),-2);
        $("#total{{$claves_conceptos['prima']['id']}}").val(pvac_fiscal);
        $("#total{{$claves_conceptos['prima']['id']}}_compensacion").val(pvac_compensacion);
       
        if(pvac_fiscal > uma15){
            $("#exento{{$claves_conceptos['prima']['id']}}").val(uma15);
            $("#gravado{{$claves_conceptos['prima']['id']}}").val(Math.round10((pvac_fiscal - uma15),-2));
            
        }else{
            $("#gravado{{$claves_conceptos['prima']['id']}}").val(0);
            $("#exento{{$claves_conceptos['prima']['id']}}").val(pvac_fiscal);

        }

    }

    function aguinaldo(){
        var fecha_usar = $('input:radio[name=fecha_usar]:checked').val();
        var salario_diario = $("#salario_diario").val();
        var salario_digital = $("#salario_digital").val();
        var dias_laborados = $("#dias_trabajados_periodo").val();
        var fecha_usar_moment = moment($("#" + fecha_usar).val());
        var AniofechaAntiguedad = fecha_usar_moment.format('YYYY');
        var anioActual = moment().format("YYYY");
        var fecha_baja_moment = moment($("#fecha_baja").val());
        var ejercicio = $("#ejercicio").val();
        var diasaguinaldo = 15;
        var uma = $("#uma").val();

        if (AniofechaAntiguedad < anioActual) {

            var fecha_contabilizar = moment(ejercicio + '-01-01');
            
           // var fecha_contabilizar =  moment($("#ultimo_aniversario").val());
        } else {
            var fecha_contabilizar = fecha_usar_moment;
            //var fecha_contabilizar = moment(ejercicio + '-01-01');
        }
        var diaspagar = fecha_baja_moment.diff(fecha_contabilizar, 'days') + 1;
        var diasagui =  Math.round10((diaspagar * diasaguinaldo) / 365, -2);
        var aguinaldo_fiscal = Math.round10(diasagui * salario_diario, -2);
        var aguinaldo_total = Math.round10(diasagui * salario_digital, -2);
        var aguinaldo_compensacion = Math.round10(aguinaldo_total - aguinaldo_fiscal, -2);
        var uma30 = Math.round10((uma * 30),-2);
        $("#total{{$claves_conceptos['aguinaldo']['id']}}").val(aguinaldo_fiscal);
        $("#total{{$claves_conceptos['aguinaldo']['id']}}_compensacion").val(aguinaldo_compensacion);

        if(aguinaldo_fiscal > uma30){
            $("#gravado{{$claves_conceptos['aguinaldo']['id']}}").val(Math.round10((aguinaldo_fiscal - uma30),-2));
            $("#exento{{$claves_conceptos['aguinaldo']['id']}}").val(uma30);
            
        }else{
            $("#gravado{{$claves_conceptos['aguinaldo']['id']}}").val(0);
            $("#exento{{$claves_conceptos['aguinaldo']['id']}}").val(aguinaldo_fiscal);

        }
    }

    function compensacion() {
        var compensacion = 0;
        $('.compensacion').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            compensacion += tmp;
        });

        $("#total{{$claves_conceptos['compensacion']['id']}}").val(Math.round10(compensacion, -2));
        $("#gravado{{$claves_conceptos['compensacion']['id']}}").val(Math.round10(compensacion, -2));
    }




    function liquidacion() {
        if ($("#calcular").val() == 1) {
            $('.liquidacion').each(function() {
                $(this).val(0);
            });
        } else {
            indemnizacion();
            prima_antiguedad_y_veinte_dias_por_anio();

        }

    }


    function indemnizacion() {
        var salario_digital = $("#salario_digital").val();

        var indemnizacion = Math.round10(90 * salario_digital, -2);
        @if(!empty($claves_conceptos['indemnizacion']['id']))
            $("#total{{$claves_conceptos['indemnizacion']['id']}}").val(indemnizacion);
        @else
            $("#indemnizacion").val(indemnizacion);
        @endif

    }

    function prima_antiguedad_y_veinte_dias_por_anio() {
        var fecha_usar = $('input:radio[name=fecha_usar]:checked').val();
        var salario_digital = $("#salario_digital").val();
        var fecha_usar_moment = moment($("#" + fecha_usar).val());
        var fecha_baja_moment = moment($("#fecha_baja").val());
        var diaspagar = fecha_baja_moment.diff(fecha_usar_moment, 'days') + 1;
        var dias_trabajados = Math.round10((diaspagar / 365), -8);
        var prima_antiguedad = dias_trabajados * 12;
        var total_prima_antiguedad = Math.round10(prima_antiguedad * salario_digital, -2);
        var veinte_dias_por_anio = dias_trabajados * 20;
        var total_veinte_dias_por_anio = Math.round10(veinte_dias_por_anio * salario_digital, -2);
        @if(!empty($claves_conceptos['priantig']['id']))
            $("#total{{$claves_conceptos['priantig']['id']}}").val(total_prima_antiguedad);
        @else
            $("#priantig").val(total_prima_antiguedad);
        @endif

        @if(!empty($claves_conceptos['veinte_dias_por_anio']['id']))
            $("#total{{$claves_conceptos['veinte_dias_por_anio']['id']}}").val(total_veinte_dias_por_anio);
        @else
            $("#veinte_dias_por_anio").val(total_veinte_dias_por_anio);
        @endif
        
    }

    /* ------------------------------ D E D U C C I O N E S --------------------------------------------------------*/
    function isr() {
        var total_gravado = $("#total_gravado").val();
        var impuesto = iterar_tabla_impuestos(total_gravado);
        var excedente_lim_inferior = total_gravado - impuesto.inferior; // ingresoExce
        var tasa_impuesto = (impuesto.porcentaje/100).toFixed(4); //PorcentajeAplicar
        var imp_marginal = excedente_lim_inferior * tasa_impuesto;
        var cuota_fija = impuesto.cuota;
        var isr = imp_marginal + cuota_fija; //isrretener
        var menossubsidio = iterar_tabla_subsidio(total_gravado);
        var isrosubsidio = (menossubsidio - isr);

        $("#total{{$claves_conceptos['isr']['id']}}").val(Math.round10(isr, -2));
        $("#gravado").text(Math.round10(total_gravado, -2));
        $("#lim_inferior").text(Math.round10(impuesto.inferior, -2));
        $("#excedente_lim_inferior").text(Math.round10(excedente_lim_inferior, -2));
        $("#tasa_impuesto").text(tasa_impuesto);
        $("#imp_marginal").text(Math.round10(imp_marginal, -2));
        $("#cuota_fija_finiquito").text(Math.round10(cuota_fija, -2));
        $("#isr").text(Math.round10(isr, -2));

        $("#menos_subsidio").text(Math.round10(menossubsidio, -2));
        $("#subsidio_al_empleado").val(Math.round10(menossubsidio, -2));
        $("#isrosubsidio").text(Math.round10(isrosubsidio, -2));
        $("#isr_o_subsidio").val(Math.round10(isrosubsidio, -2));

    }

    function isr_liquidacion() {
        var salario_diario = $("#salario_diario").val();
        var sueldo_mensual_gravado = salario_diario * 30;
        var impuesto = iterar_tabla_impuestos(sueldo_mensual_gravado);
        var excedente_lim_inferior_liquidacion = sueldo_mensual_gravado - impuesto.inferior;
        var tasa_impuesto_liquidacion = impuesto.porcentaje;
        var imp_marginal_liquidacion = excedente_lim_inferior_liquidacion * tasa_impuesto_liquidacion;
        var cuota_fija_liquidacion = impuesto.cuota;
        var isr_liquidacion = imp_marginal_liquidacion + cuota_fija_liquidacion;
        var menossubsidio_liquidacion = iterar_tabla_subsidio(sueldo_mensual_gravado);
        var isrosubsidio_liquidacion = (menossubsidio_liquidacion - isr_liquidacion);
        var fecha_usar = $('input:radio[name=fecha_usar]:checked').val();
        var fecha_usar_moment = moment($("#" + fecha_usar).val());
        var fecha_baja_moment = moment($("#fecha_baja").val());
        var diaspagar = fecha_baja_moment.diff(fecha_usar_moment, 'days') + 1;
        var dias_trabajados = Math.round10((diaspagar / 365), -8);
        var uma = $("#uma").val();
        var isr_liquidacion_indemnización_1 = Math.round10((90 * dias_trabajados) * uma, -2);
        var total_liquidacion = $('#subtotal_liquidacion').val();
        var base_gravable_del_impuesto = (isr_liquidacion_indemnización_1 > total_liquidacion) ? 0 : (total_liquidacion - isr_liquidacion_indemnización_1);
        var tasa_impuesto_liquidacion_2 = (isr_liquidacion / sueldo_mensual_gravado);
        var isr_de_la_liquidacion = (base_gravable_del_impuesto * tasa_impuesto_liquidacion_2);
        //alert(base_gravable_del_impuesto + " " + isr_liquidacion_indemnización_1  + " " + total_liquidacion);
        $("#sueldo_mensual_gravado").text(sueldo_mensual_gravado);
        $("#lim_inferior_liquidacion").text(Math.round10(impuesto.inferior, -2));
        $("#excedente_lim_inferior_liquidacion").text(Math.round10(excedente_lim_inferior_liquidacion, -2));
        $("#tasa_impuesto_liquidacion").text(tasa_impuesto_liquidacion);
        $("#imp_marginal_liquidacion").text(imp_marginal_liquidacion);
        $("#cuota_fija_liquidacion").text(cuota_fija_liquidacion);
        $("#isr_liquidacion").text(Math.round10(isr_liquidacion, -2));
        $("#menos_subsidio_liquidacion").text(Math.round10(menossubsidio_liquidacion, -2));
        $("#isrosubsidio_liquidacion").text(Math.round10(isrosubsidio_liquidacion, -2));
        $("#base_gravable_del_impuesto").text(Math.round10(base_gravable_del_impuesto, -2));
        $("#tasa_impuesto_liquidacion_2").text(tasa_impuesto_liquidacion_2);
        $("#isr_de_la_liquidacion").text(Math.round10(isr_de_la_liquidacion, -2));
        $("#isr_liquidacion_deducciones").val(Math.round10(isr_de_la_liquidacion, -2));


    }



    function iterar_tabla_impuestos(total) {
        var impuesto;
        $("#impuestos tbody tr").each(function(index) {
            var inferior, superior, cuota, porcentaje;
            $(this).children("td").each(function(index2) {
                switch (index2) {
                    case 0:
                        inferior = parseFloat($(this).text());
                        break;
                    case 1:
                        superior = parseFloat($(this).text());
                        break;
                    case 2:
                        cuota = parseFloat($(this).text());
                        break;
                    case 3:
                        porcentaje = parseFloat($(this).text());
                        break;
                }

            })

            if (total >= inferior && total <= superior) {
                $(this).addClass("bg-success");
                impuesto = {
                    'inferior': inferior,
                    'superior': superior,
                    'cuota': cuota,
                    'porcentaje': porcentaje
                };
            }

        });
        return impuesto;
    }

    function Impuesto(inferior, superior, cuota, porcentaje) {
        this.inferior = inferior;
        this.superior = superior;
        this.cuota = cuota;
        this.porcentaje = porcentaje;
    }

    function iterar_tabla_subsidio(total) {
        var subsidio_final = 0;
        $("#subsidio tbody tr").each(function(index) {
            var desde, hasta, subsidio;
            $(this).children("td").each(function(index2) {
                switch (index2) {
                    case 0:
                        desde = parseFloat($(this).text());
                        break;
                    case 1:
                        hasta = parseFloat($(this).text());
                        break;
                    case 2:
                        subsidio = parseFloat($(this).text());
                        break;
                }

            })

            if (total >= desde && total <= hasta) {
                $(this).addClass("bg-success");
                subsidio_final = subsidio;
            }

        });
        return subsidio_final;

    }


    function imss() {
        var salario_diario = $("#salario_diario").val();
        var dias_naturales, dias_patron, dias_laborados = $("#dias_trabajados_periodo").val();
        var salario_diario_integrado = $("#salario_diario_integrado").val();
        var sueldo_mensual_integrado = salario_diario_integrado * dias_laborados;
        var uma = $("#uma").val();
        var smg = $("#salario_minimo").val();
        var tres_vs_uma = Math.round10(((uma * 3) * dias_laborados), -2);
        var prima_riesgo_trabajo = $("#prima_riesgo_trabajo").val();
        var dias_del_bimestre = 30.42;
        var limite_superior_veintiuno_smg = Math.round10(((uma * 21) * dias_del_bimestre), -2);
        var limite_superior_veinticinco_smg = Math.round10(((uma * 25) * dias_del_bimestre), -2);
        var base_de_cotizacion = (sueldo_mensual_integrado > limite_superior_veinticinco_smg)? limite_superior_veinticinco_smg : sueldo_mensual_integrado;

        var procentaje_cuota_fija = $("#porcentaje_cuota_fija").val();
        var porcentaje_exce_pa = $("#porcentaje_exce_pa").val();
        var porcentaje_exce_ob = $("#porcentaje_exce_ob").val();
        var porcentaje_pre_dine_patro = $("#porcentaje_pre_dine_patro").val();
        var porcentaje_pre_dine_obre = $("#porcentaje_pre_dine_obre").val();
        var porcentaje_gas_medi_patro = $("#porcentaje_gas_medi_patro").val();
        var porcentaje_gas_medi_obre = $("#porcentaje_gas_medi_obre").val();
        var porcentaje_riesgo_trabajo = $("#porcentaje_riesgo_trabajo").val();
        var porcentaje_inva_vida_patro = $("#porcentaje_inva_vida_patro").val();
        var porcentaje_inva_vida_obre = $("#porcentaje_inva_vida_obre").val();
        var porcentaje_guarde_presta = $("#porcentaje_guarde_presta").val();
        var porcentaje_sar_patron = $("#porcentaje_sar_patron").val();
        var porcentaje_infonavit_patro = $("#porcentaje_infonavit_patro").val();
        var porcentaje_censa_vejez_patron = $("#porcentaje_censa_vejez_patron").val();
        var porcentaje_censa_vejez_obre = $("#porcentaje_censa_vejez_obre").val();

        var cuota_fija = Math.round10(uma * procentaje_cuota_fija * dias_laborados, -2);
        var exce_pa = (base_de_cotizacion > tres_vs_uma)? ((base_de_cotizacion - tres_vs_uma) * porcentaje_exce_pa) : 0; 
        var exce_ob = (base_de_cotizacion > tres_vs_uma)? ((base_de_cotizacion - tres_vs_uma) * porcentaje_exce_ob) : 0;
        var pre_dine_patro = Math.round10(base_de_cotizacion * porcentaje_pre_dine_patro,-2);
        var pre_dine_obre = Math.round10(base_de_cotizacion * porcentaje_pre_dine_obre,-2);
        var gas_medi_patro = Math.round10(base_de_cotizacion * porcentaje_gas_medi_patro,-2);
        var gas_medi_obre = Math.round10(base_de_cotizacion * porcentaje_gas_medi_obre,-2);
        var riesgo_trabajo = Math.round10(base_de_cotizacion * porcentaje_riesgo_trabajo,-2);
        var inva_vida_patro = (base_de_cotizacion > limite_superior_veintiuno_smg)? Math.round10((porcentaje_inva_vida_patro * limite_superior_veintiuno_smg),-2) : Math.round10((porcentaje_inva_vida_patro * base_de_cotizacion),-2);
        var inva_vida_obre = (base_de_cotizacion > limite_superior_veintiuno_smg)? Math.round10((porcentaje_inva_vida_obre * limite_superior_veintiuno_smg),-2) : Math.round10((porcentaje_inva_vida_obre * base_de_cotizacion),-2);
        var guarde_presta = Math.round10((porcentaje_guarde_presta * base_de_cotizacion),-2);
        var sar_patron = Math.round10((porcentaje_sar_patron * base_de_cotizacion),-2);
        var infonavit_patro = Math.round10((porcentaje_infonavit_patro * base_de_cotizacion),-2);
        var censa_vejez_patron = Math.round10((porcentaje_censa_vejez_patron * base_de_cotizacion),-2);
        var censa_vejez_obre = Math.round10((porcentaje_censa_vejez_obre * base_de_cotizacion),-2);

        $("#imss_smg").text(smg);
        $("#imss_uma").text(uma);
        $("#imss_3vsuma").text(tres_vs_uma);
        $("#imss_primart").text(prima_riesgo_trabajo);
        $("#imss_dias_del_bimestre").text(dias_del_bimestre);
        $("#imss_limite_superior_21_smg").text(limite_superior_veintiuno_smg);
        $("#imss_limite_superior_25_smg").text(limite_superior_veinticinco_smg);

        $("#cuota_fija").val(cuota_fija);
        $("#exce_pa").val(exce_pa);
        $("#exce_ob").val(exce_ob);
        $("#pre_dine_patro").val(pre_dine_patro);
        $("#pre_dine_obre").val(pre_dine_obre);
        $("#gas_medi_patro").val(gas_medi_patro);
        $("#gas_medi_obre").val(gas_medi_obre);
        $("#riesgo_trabajo").val(riesgo_trabajo);
        $("#inva_vida_patro").val(inva_vida_patro);
        $("#inva_vida_obre").val(inva_vida_obre);
        $("#guarde_presta").val(guarde_presta);

        suma_cuotas();
        suma_patron_imss();
        suma_obrera_imss();

        $("#sar_patron").val(sar_patron);
        $("#infonavit_patro").val(infonavit_patro);
        $("#censa_vejez_patron").val(censa_vejez_patron);
        $("#censa_vejez_obre").val(censa_vejez_obre);
        //alert(tres_vs_uma + " cot" + base_de_cotizacion);
        suma_cuotas_cesantiayvejez();
        total_obrera_imss();
        total_patron_imss();
        total_mensual();

    }

    function suma_cuotas(){
        var suma_cuotas = 0;
        $('.suma_cuotas').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            suma_cuotas += tmp;
        });
        $("#suma_cuotas").val(Math.round10(suma_cuotas, -2));
    }

    function suma_cuotas_cesantiayvejez(){
        var suma_cuotas_cesantiayvejez = 0;
        $('.suma_cuotas_cesantiayvejez').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            suma_cuotas_cesantiayvejez += tmp;
        });
        $("#suma_cuotas_cesantiayvejez").val(Math.round10(suma_cuotas_cesantiayvejez, -2));
    }

    function suma_patron_imss(){
        var patron = 0;
        $('.patron').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            patron += tmp;
        });
        $("#total_patron").val(Math.round10(patron, -2));
    }

    function suma_obrera_imss(){
        var obrera = 0;
        $('.obrera').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            obrera += tmp;
        });
        $("#total_obrera").val(Math.round10(obrera, -2));
        
    }

    function total_obrera_imss(){
        var total_obrera_imss = 0;
        $('.total_obrera_imss').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            total_obrera_imss += tmp;
        });
        $("#total_obrera_imss").val(Math.round10(total_obrera_imss, -2));
        $("#total{{$claves_conceptos['imss']['id']}}").val(Math.round10(total_obrera_imss, -2));
    }

    function total_patron_imss(){
        var total_patron_imss = 0;
        $('.total_patron_imss').each(function() {
            var tmp = parseFloat(($(this).val()) ? $(this).val() : 0);
            total_patron_imss += tmp;
        });
        $("#total_patron_imss").val(Math.round10(total_patron_imss, -2));
    }

    function total_mensual(){
        var obrera = Math.round10($("#total_obrera_imss").val(),-2);
        var patron = Math.round10($("#total_patron_imss").val(),-2);
        var total = (obrera + patron);
        //alert(obrera + " " + patron + " " + total + " " + Math.round10(total,-2));
        $("#total_mensual").val(Math.round10(total,-2));
    }


    function eventos(){
        $('.fiscal').off().on('keyup change',function(){
            var id = $(this).attr('id').replace("total","");
            var campo = $(this).data('id');
            var valor = $(this).val();
            var uma = $("#uma").val();

            $("#gravado"+id).val(valor);
           // alert(valor + " " + id);
            if(campo == 'aguinaldo'){
                var uma30 = Math.round10((uma * 30),-2);
                
                if(valor > uma30){
                    $("#gravado{{$claves_conceptos['aguinaldo']['id']}}").val(Math.round10((valor - uma30),-2));
                    $("#exento{{$claves_conceptos['aguinaldo']['id']}}").val(uma30);
                    
                }else{
                    $("#gravado{{$claves_conceptos['aguinaldo']['id']}}").val(0);
                    $("#exento{{$claves_conceptos['aguinaldo']['id']}}").val(valor);

                }
            }

            if(campo == 'prima'){
                var prima_vacacional_exento  = Math.round10((uma * 15),-2);
                var uma15 = Math.round10((uma * 15),-2);
                if(valor > uma15){
                    $("#exento{{$claves_conceptos['prima']['id']}}").val(uma15);
                    $("#gravado{{$claves_conceptos['prima']['id']}}").val(Math.round10((valor - uma15),-2));
                    
                }else{
                    $("#gravado{{$claves_conceptos['prima']['id']}}").val(0);
                    $("#exento{{$claves_conceptos['prima']['id']}}").val(valor);

                }
            }


            //compensacion();
            subtotalFiniquito();
            totalGravado();
            totalExento();
            calcula_total();
            calcula_total_gravado_exento();
            valida_listo_guardar();
        });

       /* $('.compensacion').off().on('keyup change',function(){
            //compensacion();
            subtotalFiniquito();
            calcula_total();
        });*/

        $('.gravado').off().on('keyup change',function(){
            totalGravado();
            calcula_total_gravado_exento();
            valida_listo_guardar();
        });

       
        $('.liquidacion').off().on('keyup change',function(){
            subtotalLiquidacion();
            calcula_total();
        });

        $('.deducciones').off().on('keyup change',function(){
            subtotalDeducciones();
            calcula_total();
        });

        $('.exento').off().on('keyup change',function(){
            totalExento();
            calcula_total_gravado_exento();
            valida_listo_guardar();

        });
    }

    function decimalAdjust(type, value, exp) {
        // Si el exp no está definido o es cero...
        if (typeof exp === 'undefined' || +exp === 0) {
            return Math[type](value);
        }
        value = +value;
        exp = +exp;
        // Si el valor no es un número o el exp no es un entero...
        if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
            return NaN;
        }
        // Shift
        value = value.toString().split('e');
        value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
        // Shift back
        value = value.toString().split('e');
        return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
    }

    // Decimal round
    if (!Math.round10) {
        Math.round10 = function(value, exp) {
            return decimalAdjust('round', value, exp);
        };
    }
</script>