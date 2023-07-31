<div class="modal" tabindex="-1" role="dialog" id="genContratoEmpleadosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Contrato empleado</h5>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('contratos.generarcontratoEmp') }}" id="contrato_empleado_form">
                @csrf
                <input type="hidden" name="id_empleado" id="id_empleado">
                <input type="hidden" name="renovacion" id="renovacion">
                <input type="hidden" name="ruta_actualizar" id="ruta_actualizar">
                <div class="nombre">Esta generando un Contrato para: <strong></strong></div>

                <label for="contrato">Elige el contrato que deseas generar: </label>
                <select name="contrato" id="contrato" class="form-control mb-3">
                    <option value="">Selecciona un tipo de contrato</option>
                    @foreach ($contratos_asignados as $cAsignado)
                        <option value="{{$cAsignado->id}}-{{$cAsignado->tipo}}-{{$cAsignado->temporalidad}}">{{$cAsignado->alias}}</option>
                    @endforeach
                </select>
                <input type="hidden" name="id_contrato" id="id_contrato" value=""/>
                <input type="hidden" name="nombre_contrato" id="nombre_contrato" value=""/>
                <input type="hidden" name="tipo_contrato" id="tipo_contrato" value=""/>
                <input type="hidden" name="temporalidad_contrato" id="temporalidad_contrato" value=""/>

                <div class="row">
                    <div class="col-md-6 nTargeta d-none">
                        <label for="diascontrato">Días de contrato: </label>
                        <input type="number" class="form-control mb-3" id="diascontrato" name="diascontrato">
                    </div>


                    <div class="col-md-6 nTargeta022 d-none">
                        <label for="actividadesrealizar02">Puesto Designado a Actividades: </label>
                        <select name="actividadesrealizar02" id="actividadesrealizar02" class="form-control mb-3">
                            <option value='TÉCNICO SOPORTE AL CLIENTE'>TÉCNICO SOPORTE AL CLIENTE</option>
                            <option value='TÉCNICO EN MANTENIMIENTO EXTINTORES'>TÉCNICO EN MANTENIMIENTO EXTINTORES</option>
                            <option value='TÉCNICO B'>TÉCNICO B</option>
                            <option value='TÉCNICO A'>TÉCNICO A</option>
                            <option value='SUPERVISOR DE SEGURIDAD'>SUPERVISOR DE SEGURIDAD</option>
                            <option value='INSPECTOR DE EXTINTORES'>INSPECTOR DE EXTINTORES</option>
                            <option value='COORDINADOR EN SITIO'>COORDINADOR EN SITIO</option>
                            <option value='COMPRADOR'>COMPRADOR</option>
                            <option value='CADISTA'>CADISTA</option>
                            <option value='AUXILIAR TÉCNICO C'>AUXILIAR TÉCNICO C</option>
                            <option value='AUXILIAR TÉCNICO B'>AUXILIAR TÉCNICO B</option>
                            <option value='AUXILIAR TÉCNICO A'>AUXILIAR TÉCNICO A</option>
                            <option value='AUXILIAR DE GESTIÓN'>AUXILIAR DE GESTIÓN</option>
                            <option value='ANALISTA DE RECURSOS HUMANOS'>ANALISTA DE RECURSOS HUMANOS</option>
                            <option value='ALMACENISTA'>ALMACENISTA</option>
                            <option value='INGENIERO ESPECIALISTA EN GAS Y FUEGO'>INGENIERO ESPECIALISTA EN GAS Y FUEGO</option>
                            <option value='ELÉCTRICO'>ELÉCTRICO</option>
                            <option value='TÉCNICO ESPECIALISTA'>TÉCNICO ESPECIALISTA</option>
                            <option value='PINTOR'>PINTOR</option>
                            <option value='SUPERVISOR  SSPA OPERATIVO'>SUPERVISOR  SSPA OPERATIVO</option>
                            <option value='SUPERVISOR SSPA ADMINISTRATIVO'>SUPERVISOR SSPA ADMINISTRATIVO</option>
                            <option value='ANALISTA DE BASE DE DATOS'>ANALISTA DE BASE DE DATOS</option>
                            <option value='ANALISTA DE PLANEACION'>ANALISTA DE PLANEACION</option>
                            <option value='AUXILIAR DE LOGISTICA'>AUXILIAR DE LOGISTICA</option>
                            <option value='SIGNATARIO'>SIGNATARIO</option>
                            <option value='SOLDADOR'>SOLDADOR</option>
                        </select>
                    </div>
                    <div class="col-md-6 nTargeta022 d-none">
                        <label for="proyectorealizar">Proyecto: </label>
                        <input type="text" name="proyectorealizar" id="proyectorealizar" class="form-control mb-3">
                    </div>
                    <div class="col-md-6 nTargeta022 d-none">
                        <label for="centrocosto">Centro de Costo: </label>
                        <input type="text" name="centrocosto" id="centrocosto" class="form-control mb-3">
                    </div>
                    <div class="col-md-6 nTargeta022 d-none">
                        <label for="diaskdm">Número de días: </label>
                        <input type="number" name="diaskdm" id="diaskdm" class="form-control mb-3">
                    </div>

                    <div class="col-md-6 nTargeta023 d-none">
                        <label for="actividadesrealizar03">Puesto Designado a Actividades: </label>
                        <select name="actividadesrealizar03" id="actividadesrealizar03" class="form-control mb-3">
                            <option value="ASISTENTE ESPECIALIZADO DE PINTOR">ASISTENTE ESPECIALIZADO DE PINTOR</option>
                            <option value="ASISTENTE ESPECIALIZADO DE CORTADOR">ASISTENTE ESPECIALIZADO DE CORTADOR</option>
                            <option value="ASISTENTE ESPECIALIZADO DE OPERADOR DE MAQUINA">ASISTENTE ESPECIALIZADO DE OPERADOR DE MAQUINA</option>
                            <option value="ASITENTE ESPECIALIZADO DE OPERADOR DE GRUA EN PLANTA">ASITENTE ESPECIALIZADO DE OPERADOR DE GRUA  EN PLANTA</option>
                            <option value="ASISTENTE ESPECIALIZADO">ASISTENTE ESPECIALIZADO</option>
                            <option value="ASISTENTE ESPECIALIZADO DE SOLDADOR">ASISTENTE ESPECIALIZADO DE SOLDADOR</option>
                            <option value="ASISTENTE ESPECIALIZADO DE ARMADOR">ASISTENTE ESPECIALIZADO DE ARMADOR</option>
                            <option value="ASISTENTE ESPECIALIZADO DE ELECTRICISTA">ASISTENTE ESPECIALIZADO DE ELECTRICISTA</option>
                            <option value="ASISTENTE ESPECIALIZADO DE JEFE DE ALMACEN">ASISTENTE ESPECIALIZADO DE JEFE DE ALMACEN</option>
                            <option value="ASISTENTE ESPECIALIZADO OPERADOR DE GRUA DE PATIO EN OBRA">ASISTENTE ESPECIALIZADO OPERADOR DE PATIO EN OBRA</option>
                        </select>
                    </div>
                    <div class="col-md-6 nTargeta023 d-none">
                        <label for="proyectorealizarfabre">Proyecto: </label>
                        <input type="text" name="proyectorealizarfabre" id="proyectorealizarfabre" class="form-control mb-3">
                    </div>
                    <div class="col-md-6 nTargeta023 d-none">
                        <label for="centrocostofabre">Centro de Costo: </label>
                        <input type="text" name="centrocostofabre" id="centrocostofabre" class="form-control mb-3">
                    </div>
                    <div class="col-md-6 nTargeta023 d-none">
                        <label for="ndias">Número de días: </label>
                        <input type="number" name="ndias" id="ndias" class="form-control mb-3">
                    </div>


                    <div class="col-md-6 mb-3 nTargeta04 d-none">
                        <label for="actividadesrealizar">Puesto Designado a Actividades: </label>
                        <select name="actividadesrealizar" id="actividadesrealizar" class="form-control">
                            <option value='TÉCNICO SOPORTE AL CLIENTE'>TÉCNICO SOPORTE AL CLIENTE</option>
                            <option value='TÉCNICO EN MANTENIMIENTO EXTINTORES'>TÉCNICO EN MANTENIMIENTO EXTINTORES</option>
                            <option value='TÉCNICO B'>TÉCNICO B</option>
                            <option value='TÉCNICO A'>TÉCNICO A</option>
                            <option value='SUPERVISOR DE SEGURIDAD'>SUPERVISOR DE SEGURIDAD</option>
                            <option value='INSPECTOR DE EXTINTORES'>INSPECTOR DE EXTINTORES</option>
                            <option value='COORDINADOR EN SITIO'>COORDINADOR EN SITIO</option>
                            <option value='COMPRADOR'>COMPRADOR</option>
                            <option value='CADISTA'>CADISTA</option>
                            <option value='AUXILIAR TÉCNICO C'>AUXILIAR TÉCNICO C</option>
                            <option value='AUXILIAR TÉCNICO B'>AUXILIAR TÉCNICO B</option>
                            <option value='AUXILIAR TÉCNICO A'>AUXILIAR TÉCNICO A</option>
                            <option value='AUXILIAR DE GESTIÓN'>AUXILIAR DE GESTIÓN</option>
                            <option value='ANALISTA DE RECURSOS HUMANOS'>ANALISTA DE RECURSOS HUMANOS</option>
                            <option value='ALMACENISTA'>ALMACENISTA</option>
                            <option value='INGENIERO ESPECIALISTA EN GAS Y FUEGO'>INGENIERO ESPECIALISTA EN GAS Y FUEGO</option>
                            <option value='ELÉCTRICO'>ELÉCTRICO</option>
                            <option value='TÉCNICO ESPECIALISTA'>TÉCNICO ESPECIALISTA</option>
                            <option value='PINTOR'>PINTOR</option>
                            <option value='SUPERVISOR  SSPA OPERATIVO'>SUPERVISOR  SSPA OPERATIVO</option>
                            <option value='SUPERVISOR SSPA ADMINISTRATIVO'>SUPERVISOR SSPA ADMINISTRATIVO</option>
                            <option value='ANALISTA DE BASE DE DATOS'>ANALISTA DE BASE DE DATOS</option>
                            <option value='ANALISTA DE PLANEACION'>ANALISTA DE PLANEACION</option>
                            <option value='AUXILIAR DE LOGISTICA'>AUXILIAR DE LOGISTICA</option>
                            <option value='SIGNATARIO'>SIGNATARIO</option>
                            <option value='SOLDADOR'>SOLDADOR</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 nTargeta04 d-none">
                        <label for="numdiaskdm">Número de días: </label>
                        <input type="number" name="numdiaskdm" id="numdiaskdm" class="form-control">
                    </div>


                    <div class="col-md-6 d-none nTargeta05">
                        <label for="meses_determinado">Numero de meses: </label>
                        <select name="meses_determinado" class="form-control mb-3">
                            @for ($i = 1; $i<= 12; $i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6 nTargeta06 d-none">
                        <label for="fecha_inicio_contrato">Fecha de inicio: </label>
                        <input type="date" name="fecha_inicio_contrato" id="fecha_inicio_contrato" class="form-control mb-3">
                    </div>
                </div>


                <div class="form-row mt-4">
                    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning generarContrato" id="btn-guardar-contrato">Guardar</button>

                    <input type="hidden" name="prestamos_tipo_id" id="prestamos_tipo_id">
                </div>
            </form>
            
        </div>
        </div>
    </div>
</div>
@push('css')
<style>
    .invalido{
        color:#EE4A30;
    }
</style>
@endpush


<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/validate/jquery-validate-adicional.js') }}"></script>
<script src="{{ asset('js/moment/moment.js') }}"></script>
<script src="{{ asset('js/moment/es.js') }}"></script>
<script>

$(function(){
    // al abrir el modal cargamos los datos
    $('#genContratoEmpleadosModal').on('shown.bs.modal', function (e) {
        // inicioForm();
        var idempleado = $(e.relatedTarget).data('idempleado');
        var nombre = $(e.relatedTarget).data('nombre');
        var fecha_inicio_contrato = $(e.relatedTarget).data('fecha_inicio_contrato');
        var fecha_vencimiento = $(e.relatedTarget).data('fecha_vencimiento');
        var renovacion = $(e.relatedTarget).data('renovacion');
        var ruta_actualizar = $(e.relatedTarget).data('urlactualizar');
        $("#id_empleado").val(idempleado);
        $("#ruta_actualizar").val(ruta_actualizar);
        $('#renovarContratoModal .modal-body .nombre strong').text(nombre);

        if(fecha_inicio_contrato != undefined){
            $('#renovarContratoModal .modal-body #fecha_inicio_contrato').val(fecha_inicio_contrato.split(' ')[0])
        }

        $('#renovarContratoModal .modal-body #renovacion').val(renovacion);
        if(fecha_vencimiento != undefined){
            var nueva_fecha_inicio = moment(fecha_vencimiento).add(1,'day');
            $("#fecha_inicio_contrato").val(nueva_fecha_inicio.format('YYYY-MM-DD'));
        }
    });

    $('#contrato').change(function(){
        var contrato = $(this).val().split("-");
        var idContrato = contrato[0];
        var tipo = contrato[1];
        var temporalidad = contrato[2];
        $("#id_contrato").val(idContrato);
        $("#tipo_contrato").val(tipo);
        $("#temporalidad_contrato").val(temporalidad);
        $("#nombre_contrato").val($('select[name="contrato"] option:selected').text());

        if (idContrato == 3 || idContrato == 16 || idContrato ==15 || idContrato ==18 || idContrato ==19 || idContrato == 22){

            $(".nTargeta").removeClass('d-none');
            $(".nTargeta022").addClass('d-none');
            $(".nTargeta023").addClass('d-none');
            $(".nTargeta04").addClass('d-none');
            $(".nTargeta05").addClass('d-none');
            $(".nTargeta06").removeClass('d-none');

        }else if(idContrato == 9){

            $(".nTargeta").addClass('d-none');
            $(".nTargeta022").removeClass('d-none');
            $(".nTargeta023").addClass('d-none');
            $(".nTargeta04").addClass('d-none');
            $(".nTargeta05").addClass('d-none');
            $(".nTargeta06").removeClass('d-none');

        }else if(idContrato == 8){

            $(".nTargeta").addClass('d-none');
            $(".nTargeta022").addClass('d-none');
            $(".nTargeta023").addClass('d-none');
            $(".nTargeta04").removeClass('d-none');
            $(".nTargeta05").addClass('d-none');
            $(".nTargeta06").removeClass('d-none');

        }else if(idContrato == 10){

            $(".nTargeta").addClass('d-none');
            $(".nTargeta022").addClass('d-none');
            $(".nTargeta023").removeClass('d-none');
            $(".nTargeta04").addClass('d-none');
            $(".nTargeta05").addClass('d-none');
            $(".nTargeta06").removeClass('d-none');

        } else if(idContrato == 1 || idContrato == 12 || idContrato == 7 || idContrato == 6 || idContrato == 17 ){

            $(".nTargeta").addClass('d-none');
            $(".nTargeta022").addClass('d-none');
            $(".nTargeta023").addClass('d-none');
            $(".nTargeta04").addClass('d-none');
            $(".nTargeta05").removeClass('d-none');
            $(".nTargeta06").removeClass('d-none');

        }else if(idContrato == "" || idContrato == 0){
            $(".nTargeta").addClass('d-none');
            $(".nTargeta022").addClass('d-none');
            $(".nTargeta023").addClass('d-none');
            $(".nTargeta04").addClass('d-none');
            $(".nTargeta05").addClass('d-none');
            $(".nTargeta06").removeClass('d-none');
        }else{
            if(tipo == "D"){
                if(temporalidad == "M"){
                    $(".nTargeta").addClass('d-none');
                    $(".nTargeta022").addClass('d-none');
                    $(".nTargeta023").addClass('d-none');
                    $(".nTargeta04").addClass('d-none');
                    $(".nTargeta05").removeClass('d-none');
                    $(".nTargeta06").removeClass('d-none');
                }else if(temporalidad == "D"){
                    $(".nTargeta").removeClass('d-none');
                    $(".nTargeta022").addClass('d-none');
                    $(".nTargeta023").addClass('d-none');
                    $(".nTargeta04").addClass('d-none');
                    $(".nTargeta05").addClass('d-none');
                    $(".nTargeta06").removeClass('d-none');
                }
            }else if(tipo == "O"){
                $(".nTargeta").addClass('d-none');
                $(".nTargeta022").addClass('d-none');
                $(".nTargeta023").addClass('d-none');
                $(".nTargeta04").addClass('d-none');
                $(".nTargeta05").addClass('d-none');
                $(".nTargeta06").addClass('d-none');
            }else{
                $(".nTargeta").addClass('d-none');
                $(".nTargeta022").addClass('d-none');
                $(".nTargeta023").addClass('d-none');
                $(".nTargeta04").addClass('d-none');
                $(".nTargeta05").addClass('d-none');
                $(".nTargeta06").removeClass('d-none');
            }
        }
    });

$("#contrato_empleado_form").validate({
  errorClass: "invalido",
  errorElement: "span",
  errorPlacement: function(error, element) {
    error.appendTo( $('label[for='+element.attr("name")+']') );
  },
  rules: {
    contrato: {required: true},
    diascontrato: {required: true},
    actividadesrealizar02: {required: true},
    proyectorealizar: {required: true},
    centrocosto: {required: true},
    diaskdm: {required: true},
    actividadesrealizar03: {required: true},
    proyectorealizarfabre: {required: true},
    centrocostofabre: {required: true},
    ndias: {required: true},
    actividadesrealizar: {required: true},
    numdiaskdm: {required: true},
    meses_determinado: {required: true},
    fecha_inicio_contrato: {required: true}
  },

});

});

function inicioForm(){
    $('#contrato_empleado_form input').val('').removeClass('invalido');
    $('#contrato_empleado_form select').val('').removeClass('invalido');
    $("span.invalido").remove();
    $(".nTargeta").addClass('d-none');
    $(".nTargeta022").addClass('d-none');
    $(".nTargeta023").addClass('d-none');
    $(".nTargeta04").addClass('d-none');
    $(".nTargeta05").addClass('d-none');
}
</script>
