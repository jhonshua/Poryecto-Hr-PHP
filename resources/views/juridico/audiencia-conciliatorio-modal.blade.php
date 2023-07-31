<div class="row" id="DivCheckArregloConciliatorio">
    <div class="col-md-4">
        <div class="form-group">
            <div class="checkbox">
                <label><b>Arreglo concilatorio</b>&nbsp;&nbsp;
                <input type="checkbox" name="arreglo_conciliatorio" id="arreglo_conciliatorio" onClick="arreglo();"/></label>
            </div>
        </div>
    </div>
</div>
<div id="arregloConciliatorio">
    <div id="arreglo" style="display:none">
        <div class="row">
            <div class="col-md-12 row">
                <div class="col-md-12">
                    <label><b><i>SELECCIONA QUE OPCIONES ESTAN INCLUIDAS EN EL ARREGLO CONCILIATORIO</i></b></label>
                </div>
                <div class="checkbox col-md-4">
                    <label>
                        @if(!empty($demanda))
                            <input type="checkbox" class="conciliatorio" data-valor="{{$demanda->importe}}" name="EstImporte" id="EstImporte" onclick="calcular();" />&nbsp;Importe finiquito &nbsp;<small><i><d>(${{$demanda->importe}})</d></i></small>
                        @else
                            <input type="checkbox" class="conciliatorio" name="EstImporte" id="EstImporte" />&nbsp;Importe finiquito
                        @endif
                    </label>
                </div>
                <div class="checkbox col-md-4">
                    <label>
                        @if(!empty($demanda))
                            <input type="checkbox" class="conciliatorio" data-valor="{{$demanda->prestaciones_devengadas}}" name="EstPrestaciones" id="EstPrestaciones" onclick="calcular();" />&nbsp;Prestaciones devengadas&nbsp;<small><i><d>(${{$demanda->prestaciones_devengadas}})</d></i></small>
                        @else
                            <input type="checkbox" class="conciliatorio" name="EstPrestaciones" id="EstPrestaciones" />&nbsp;Prestaciones devengadas&nbsp;

                        @endif
                    </label>
                </div>
                <div class="checkbox col-md-4">
                    <label>
                        @if(!empty($demanda))
                            @php
                                $IndmCon = $demanda->indemnizacion_constitucional * $empleado->salario_diario;
                            @endphp
                            <input type="checkbox" class="conciliatorio" data-valor="{{$IndmCon}}" name="EstIndmCon" id="EstIndmCon" onclick="calcular();" />&nbsp;Indemizacion Constitucional&nbsp;<small><i><d>(${{$IndmCon}}, días {{$demanda->indemnizacion_constitucional}})</d></i></small>
                        @else
                            <input type="checkbox" class="conciliatorio" data-valor="" name="EstIndmCon" id="EstIndmCon" />&nbsp;Indemizacion Constitucional
                        @endif
                    </label>
                </div>
                <div class="checkbox col-md-4">
                    <label>
                        @if(!empty($demanda))
                            <input type="checkbox" class="conciliatorio" data-valor="{{$demanda->indemnizacion_anio}}" name="EstIndmAno" id="EstIndmAno" onclick="calcular();" />&nbsp;Indemizacion Anual&nbsp;<small><i><d>(${{$demanda->indemnizacion_anio}})</d></i></small>
                        @else
                            <input type="checkbox" class="conciliatorio" data-valor="" name="EstIndmAno" id="EstIndmAno" />&nbsp;Indemizacion Anual&nbsp;
                        @endif
                    </label>
                </div>
                <div class="checkbox col-md-4">
                    <label>
                        @if(!empty($demanda))
                            <input type="checkbox" class="conciliatorio" data-valor="{{$demanda->salario_caido}}" name="EstSalarioCaido" id="EstSalarioCaido" onclick="calcular();" />&nbsp;Salarios Caidos&nbsp;<small><i><d>(${{$demanda->salario_caido}})</d></i></small>
                        @else
                            <input type="checkbox" class="conciliatorio" data-valor="" name="EstSalarioCaido" id="EstSalarioCaido" />&nbsp;Salarios Caidos&nbsp;
                        @endif
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                        @if(!empty($demanda))
                            <label>Importe Extra</label><input type="number" class="form-control" name="ImporteExtra" id="ImporteExtra" step="0.01" OnChange="calcular();" value="{{$demanda->importe_extra}}" />
                        @else
                            <label>Importe Extra</label><input type="number" class="form-control" name="ImporteExtra" id="ImporteExtra" step="0.01" OnChange="calcular();" value="0.0" />
                        @endif     
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="">Total</label>
                    <input type="number" readonly id="total" class="form-control"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Motivo</label><textarea class="form-control" name="MotivoArregloConci" id="MotivoArregloConci" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div id="proxima">
        
        </div>
    </div>

<script>
    function calcular(){
        console.log(calcular);
            var total=0;
            $(".conciliatorio").each(function(){
                if( $(this).prop('checked')){
                    total = total + parseInt($(this).data("valor"));
                }
            });

            total = total + parseInt($('#ImporteExtra').val());
            $("#total").val(total);
    }

    function arreglo(){
        if( $("#arreglo_conciliatorio").prop('checked')){
            $("#arreglo").slideDown('slow');
            $("#proxima").html('');
        }else{
            $("#arreglo").slideUp('slow');
            fechaProxima();
        }
    }

    function fechaProxima(){
        $("#proxima").html('<div class="row"><div class="form-group"><div class="col-md-12" id="DivFechaProxAudi"><label>Fecha Proxima Audiencia</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div><input type="text" class="form-control" name="FechaProxima" id="FechaProxima"></div></div></div></div>');
        $('#FechaProxima').datetimepicker({format: 'DD-MM-YYYY'});
    }
</script>