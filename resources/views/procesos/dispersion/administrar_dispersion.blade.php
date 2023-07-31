<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link href="{{ asset('css/radios_check.css') }}" rel="stylesheet">
@include('includes.head')
<style>
    .top-line-black {
        width: 19%;}
</style>
<body>
    @include('includes.navbar')
    <div class="container">
        @include('includes.header',['title'=>'Dispersiones','subtitle'=>'Procesos de cálculo', 'img'=>'img/header/parametria/icono-puestos.png','route'=>'procesos.dispersion.inicio'])
        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @elseif(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif
        <div class="article border">
            <div class="row" id="row">
                <div class="col-md-3">
                    <form method="post" id="banco_form" action="{{$modal_administrar_dispersion['ruta']}}" class="card p-3">
                        @csrf
                        @if(!empty($bancos))
                        <label for="banco"><strong>Selecciona el Archivo a Generar: </strong></label>
                        <select name="banco" id="banco" class="form-control mb-3" required>
                            @foreach($bancos as $banco)
                            @if($banco['nombre'] == "BANORTE" || $banco['nombre'] == "AZTECA")  <!-- || $banco['nombre'] == "BBVA BANCOMER" -->
                            <option style="font-weight:900;" value="{{$banco['id']}}">{{$banco['nombre']}}</option>
                            @else
                            <!--option value="{{$banco['id']}}">{{$banco['nombre']}}</option-->
                            @endif
                            @endforeach
            
                        </select>
                        @endif
            
                        @if(!empty($ejercicios))
                        <label for="ejercicio">Ejercicio:</label>
                        <select name="ejercicio_aguinaldo" id="ejercicio_aguinaldo" class="form-control mb-3" required>
                            @foreach($ejercicios as $e)
                            <option style="font-weight:900;" value="{{$e->ejercicio}}">{{$e->ejercicio}}</option>
                            @endforeach
            
                        </select>
                        @endif
            
                        <input type="hidden" name="tipo_dispersion" id="tipo_dispersion" value="{{$tipo_dispersion}}">
                        <input type="hidden" name="idperiodo" id="idperiodo" value="{{$idperiodo}}">
                        @if(!empty($idempleado))
                        <input type="hidden" name="idempleado" id="idempleado" value="{{$idempleado}}">
                        @endif
                        <input type="hidden" name="ejercicio" id="ejercicio" value="{{$ejercicio}}" />
                        <input type="hidden" name="nombre_periodo" id="nombre_periodo" value="{{$nombre_periodo}}">
            
                        <div class="text-center">
                            <a href="{{route('procesos.dispersion.inicio')}}"  id="cerrar"><div class="button-style-cancel">Cancelar</div></a>
                            <div class="button-style mt-1" id="enviar_banco">Enviar</div>
                        </div>
                    </form>
                </div>
            
            </div>
        </div>

        {{-- @include('parametria.puestos.puestos-reales.crear-editar-puesto-real-modal') --}}
        {{--@include('parametria.puestos.puestos-reales.importar-puestos-modal')--}}
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="verDispersionModal">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ver dispersión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
    @include('includes.footer')
    <script>
        $(function() {
    
            $(" #enviar_banco").off().on("click", function() {
                var url = "{{$modal_administrar_dispersion['ruta']}}";
                $("#divGestion").html("");
                //var formData = new FormData(document.getElementById("banco_form"));
                var btnEnviar = $("#enviar_banco");
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "post",
                    url: url,
                    data: $("#banco_form").serialize(),
                    dataType: 'JSON',
                    beforeSend: function() {
                        var img = "{{asset('img/spinner.gif')}}";
                        btnEnviar.html("<img src='" + img + "' style='width:20px' />").attr("disabled", "disabled");
                    },
                    complete: function(data) {
    
                        btnEnviar.html("Enviar").removeAttr("disabled");
                    },
                    success: function(response) {
                        if (response.ok == 1) {
                            $("#row").append('<div class="col-md-4" style="padding-bottom:20px;" id="divDepartamentos"></div>');
                            $("#row").append('<div class="col-md-5" id="divGestion"></div>');
                            if ($("#tipo_dispersion").val() == 1 || $("#tipo_dispersion").val() == 3) { // Nomina y aguinaldo
                                formularioDepartamentos(response);
                            }
                            if ($("#tipo_dispersion").val() == 2) { // finiquito
                                $("#divDepartamentos").remove();
    
                                formularioArchivo(response);
                            } else {
                                formularioDepartamentos(response); // aguinaldo
                            }
    
                        } else {
                            $("#divDepartamentos").html('<h6 style="color:red;">No hay departamentos con el Banco seleccionado</h6>');
    
                        }
                    },
                    error: function(data) {
                
                        alert("Problemas al tratar de enviar el formulario");
                    }
                });
                return false;
            });
        });
    
    
        function formularioDepartamentos(response) {

            $("#divDepartamentos").html('<form id="departamentos-form" method="POST" action="' + response.ruta + '" class="formulario card p-3"><div class="row mb-4 checkbox" id="contenedor-departamentos"></div></form>');
            $("#contenedor-departamentos").prepend('<div style="border-bottom-style:inset;width:100%" class="text-right checkbox"><input type="checkbox" id="marcar"/><label for="marcar"><b>MARCAR TODAS/DESMARCAR TODAS</b></label></div>');
            response.departamentos.forEach(function(departamento, index) {
                $("#contenedor-departamentos").append('<input class="form-check-input deptos" type="checkbox" value="' + departamento.id + '" name="checkDepartamento[]" id="ch' + departamento.id + '" required><label class="" for="ch' + departamento.id + '">' + departamento.nombre + '<small> (' + departamento.empleados + ' trabajadores)</small></label><br/>');
            });
            $("#departamentos-form").append('<input type="hidden" value="' + response.idperiodo + '" name="idperiodo" id="idperiodo" required>');
            $("#departamentos-form").append('<input type="hidden" value="' + response.idbanco + '" name="idbanco" id="idbanco" required>');
            $("#departamentos-form").append('<input type="hidden" value="' + response.ejercicio + '" name="ejercicio" id="ejercicio" required>');
            $("#departamentos-form").append('<input type="hidden" value="' + response.tipo_dispersion + '" name="tipo_dispersion" id="tipo_dispersion" required>');
            $("#departamentos-form").append('<input type="hidden" value="' + response.nombre_periodo + '" name="nombre_periodo" id="nombre_periodo" required>');
    
            $("#departamentos-form").append('<div class="row"><div class="col-md-6"><button type="button" class="button-style" id="enviar_departamentos">Enviar</button></div></div>');
            eventoCheck();
    
            $("#enviar_departamentos").off().on("click", function() {
                var url = $("#departamentos-form").attr('action');
    
                //alert("enviar");
                if ($('#departamentos-form input[type=checkbox]:checked').length == 0) {

                    swal("Campos requeridos", "Debe seleccionar al menos un departamento.", "warning");

                
                } else {
    
                    var btnEnviar = $("#enviar_departamentos");
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "post",
                        url: url,
                        data: $("#departamentos-form").serialize(),
                        dataType: 'JSON',
                        beforeSend: function() {
                            var img = "{{asset('/img/spinner.gif')}}";
                            btnEnviar.html("<img src='" + img + "' style='width:20px' />").attr("disabled", "disabled");
                        },
                        complete: function(data) {
                            btnEnviar.html("Enviar").removeAttr("disabled");
                        },
                        success: function(response) {
                            if (response.ok == 1) {
                                formularioArchivo(response);
                                $("#departamentos-form input[type=checkbox]").prop("disabled", true);
                                btnEnviar.hide();
                                $("#enviar_departamentos_cerrar").hide();
                            } else {
                                $("#departamentosDiv").html('<h6 style="color:red;">El otro formulario</h6>');
    
                            }
                        },
                        error: function(data) {
    
                            swal("", "Problemas al tratar de enviar el formulario..!!", "error");
                        }
                    });
                }
                return false;
            });
    
        }
    
        function eventoCheck() {
            $('#marcar').on('click', function() {
                if ($(this).is(':checked')) {
                    $(".deptos").prop("checked", true);
                } else {
                    $(".deptos").prop("checked", false);
                }
            });
        }
    
        function formularioArchivo(response) {
            $("#divGestion").html("<h3>Empleados con <b>" + $("#banco option:selected").text() + "</b> : " + response.numero_empleados_banco + "</h3>");
            $("#divGestion").append('<form class="formulario card p-3" action="' + response.ruta_ver_dispersion + '" id="gestionarDispersionForm"></form>');
            $("#gestionarDispersionForm").append('<label for="">El importe a generar es: &nbsp;&nbsp;</label><div class="row radio"><div class="col-md-6"><input type="radio" name="tipoimporte" id="tipoimporteFiscal" value="Fiscal" ><label for="tipoimporteFiscal">FISCAL</label></div><div class="col-md-6"><input type="radio" name="tipoimporte" id="tipoimporteSin" value="Sindical" > <label for="tipoimporteSin">SINDICAL</label></div></div>');
            $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6" style="display:none"><label for="">Banco a emitir:</label><select name="bancofiscal" class="form-control mb-3"></select></div></div>');
        
    
            if (response.idbanco == 72) { // BANORTE
                $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6"><label for="">Elije la cuenta bancaria: </label><select name="cuenta" class="form-control mb-3" required ><option value="cuenta_bancaria">Cuenta Num 1</option><option value="cuenta_bancaria2">Cuenta Num 2</option><option value="cuenta_bancaria3">Cuenta Num 3</option><option value="clabe_interbancaria">Clave Interbancaria</option></select></div><div class="col-md-6"><label for="">Fecha de dispersión: </label><input type="date" name="fechadispersion" class="form-control mb-3" required ></div></div>');
                if (response.numero_empleados_banco > 0) {
                    $("#gestionarDispersionForm").append('<label for="">Selecciona el tipo de archivo a generar:</label><div class="radio row"><div class="col-md-6"><input type="radio" name="tipoarchivo" value="SPEIXLS" id="SPEIXLS" ><label for="SPEIXLS">.XLS(SPEI)</label></div><div class="col-md-6"><input type="radio" name="tipoarchivo" value="PAG" id="PAG"   required><label for="PAG">.PAG(Layout Banco)</label></div><div class="col-md-12"><input type="radio" name="tipoarchivo" value="XLS" id="XLS"  required checked><label for="XLS">.XLS(Layout Excel PAGOS MASIVOS)</label></div></div>');
    
                } else {
                    $("#gestionarDispersionForm").append('<label for="">Selecciona el tipo de archivo a generar:</label><div class="col-md-12"><input type="radio" name="tipoarchivo" value="XLS" id="XLS" checked><label for="XLS">.XLS(Layout Excel PAGOS MASIVOS)</label></div></div>');
                }
                $("#gestionarDispersionForm").append('<div id="bancos_exentos" class="d-none"><label for="">¿Qué Banco(s) deseas dejar Excento?:</label><div id="dosarchivo" class="checkbox row"><div class="col-md-6"><input type="checkbox" name="banco_omitir[]" value="72" class="omitir_banco" id="72"><label for="72">BANORTE</label></div><div class="col-md-6"><input type="checkbox" name="banco_omitir[]" value="12" class="omitir_banco" id="12"><label for="12"> BBVA BANCOMER</label></div><div class="col-md-6"><input type="checkbox" name="banco_omitir[]" value="127" class="omitir_banco" id="127"><label for="127">AZTECA</label></div></div></div>');
                $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6"><label for="">Tipo de Operación:</label><select name="TipoOperacion" class="form-control mb-3" ><option value="01">Propias</option><option value="02">Terceros</option><option value="04">Spei</option><option value="05">TEF</option><option value="07">OPIs</option></select></div><div class="col-md-6"><label for="">Referencia:</label><input type="text" name="referencia" class="form-control mb-3" /></div></div>');
    
            } else if (response.idbanco == 127) { // AZTECA
                $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6"><label for="">Elije la cuenta Bancaria: </label><select name="cuenta" class="form-control mb-3" required ><option value="cuenta_bancaria">Cuenta Num 1</option><option value="cuenta_bancaria2">Cuenta Num 2</option><option value="cuenta_bancaria3">Cuenta Num 3</option><option value="clabe_interbancaria">Clave Interbancaria</option></select></div><div class="col-md-6"><label for="">Fecha de dispersión: </label><input type="date" name="fechadispersion" class="form-control mb-3" required ></div><div class="col-md-6"><label for="">Concepto: </label><input type="text" name="concepto" class="form-control mb-3" required ></div></div>');
                if (response.numero_empleados_banco > 0) {
                    $("#gestionarDispersionForm").append('<label for="">Selecciona el Tipo de Archivo a Generar:</label><div class="radio row"><div class="col-md-6"><input type="radio" name="tipoarchivo" value="XLSbaz" id="XLSbaz"  checked><label for="XLSbaz">.XLS(Layout Excel)</label></div><div class="col-md-12"><input type="radio" name="tipoarchivo" value="XLSbazMas" id="XLSbazMas"  required><label for="XLSbazMas">.XLS(Layout Excel PAGOS MASIVOS)</label></div></div>');
    
                } else {
                    //alert(response.numero_empleados_banco);
                    $("#gestionarDispersionForm").append('<label for="">Selecciona el Tipo de Archivo a Generar:</label><div class="radio row"><div class="col-md-12"><input type="radio" name="tipoarchivo" value="XLSbazMas" id="XLSbazMas"  checked><label for="XLSbazMas">.XLS(Layout Excel PAGOS MASIVOS)</label></div></div>');
                }
                $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6"><label for="">Tipo de Operación:</label><select name="TipoOperacion" class="form-control mb-3" required><option value="01">Transferencia SPEI</option><option value="02">Transferencia TEF</option><option value="03">Traspaso Cuentas BAZ</option><option value="04">Pago o Tarjeta Debito Corporativa</option></select></div></div>');
    
    
            }
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.idperiodo + '" name="idperiodo" id="idperiodo" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.idbanco + '" name="idbanco" id="idbanco" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.ejercicio + '" name="ejercicio" id="ejercicio" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.tipo_dispersion + '" name="tipo_dispersion" id="tipo_dispersion" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.idempleado + '" name="idempleado" id="idempleado" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.nombre_periodo + '" name="nombre_periodo" id="nombre_periodo" required>');
            $("#gestionarDispersionForm").append('<input type="hidden" value="' + response.cadena_departamentos + '" name="cadena_departamentos" id="cadena_departamentos" required>');
    
            $("#gestionarDispersionForm").append('<div class="row"><div class="col-md-6"><input type="submit" class="button-style" id="enviar_gestion" value="Enviar"></div></div>');
    
            eventosGestion();
        }
    
        function eventosGestion() {
            $('input:radio[name=tipoarchivo]').off().on("click", function(e){
                //e.preventDefault();
                var tipo = $('input:radio[name=tipoarchivo]:checked').val();
    
                if(tipo == 'XLSbazMas' || tipo == 'SPEIXLS'){
                    $("#bancos_exentos").removeClass("d-none");
                }else{
                    $("#bancos_exentos").addClass("d-none");
                    $('.omitir_banco').prop("checked", false);
                }
            });
            
            $("#gestionarDispersionForm").off().on("submit", function() {
                if ($('#gestionarDispersionForm input[name=tipoimporte]:checked').length == 0) {
                    
                    swal("Campos requeridos", "Debe seleccionar el importe.", "warning");
                } else {
    
                    var url = $("#gestionarDispersionForm").attr('action');
    
    
                    var btnEnviar = $("#enviar_gestion");
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "post",
                        url: url,
                        data: $("#gestionarDispersionForm").serialize(),
                        dataType: 'JSON',
                        beforeSend: function() {
                            var img = "{{asset('img/spinner.gif')}}";
                            btnEnviar.html("<img src='" + img + "' style='width:20px' />").attr("disabled", "disabled");
                        },
                        complete: function(data) {
                            btnEnviar.html("Enviar").removeAttr("disabled");
                        },
                        success: function(response) {
                       
                            if (response.ok == 1) {
                                $('#verDispersionModal .modal-body').html('');
                                $.each(response.empleados,function(razon,emisora){
                                    var cont = 0;
                                    var emisora_id = 0;
                                    $.each(emisora,function(index,empleado){
                                        if(cont == 0){
                                            $('#verDispersionModal .modal-body').append('<h3>'+razon+'</h3><table class="table table-striped adispersar table-sm" id="table'+empleado['id_emisora']+'" style="font-size:11px;"><thead class="thead-dark"><tr><th>Empleado</th><th>' + response.encabezado + '</th><th>Importe</th></tr></thead><tbody id="contenido'+empleado['id_emisora']+'"></tbody></table>');
                                            $('#verDispersionModal .modal-body').append("<h5>Datos que se enviarán al archivo tipo: <b>" + $("#banco option:selected").text() + '</b> <small>(' + response.tipo_archivo + ")</small></h5>");
                                            $('#verDispersionModal .modal-body').append('<form method="POST" data-emisoraid="'+empleado['id_emisora']+'" action="' + response.url_archivo_generar + '" id="form'+empleado['id_emisora']+'" class="row generarArchivo"> @csrf</form>');
                                            emisora_id = empleado['id_emisora'];
                                            cont++;
                                        }
                                        if(Number.parseFloat(empleado[response.tipoimporte]).toFixed(2) == 0.00){
                                            $('#contenido'+empleado['id_emisora']).append('<tr class="table-danger"><td>' + empleado['nombre'] + " " + empleado['apaterno'] + " " + empleado['amaterno'] + '</td><td>' + empleado[response.cuenta] + '</td><td>$ ' +  Number.parseFloat(empleado[response.tipoimporte]).toFixed(2) + '</td></tr>');
    
                                        }else{
                                            $('#contenido'+empleado['id_emisora']).append('<tr><td>' + empleado['nombre'] + " " + empleado['apaterno'] + " " + empleado['amaterno'] + '</td><td>' + empleado[response.cuenta] + '</td><td>$ ' +  Number.parseFloat(empleado[response.tipoimporte]).toFixed(2) + '</td></tr>');
                                        }
                                    });
    
                                    if (response.tipo_archivo == 'PAG') {
    
                                    } else if (response.tipo_archivo == 'XLSbazMas') {
                                        $("#form"+emisora_id).append('<div class="col-md-4"><label for="alias_cliente">Ingresa Alias del Cliente:</label><input type="text" name="alias_cliente" class="form-control" required></div>');
                                        $("#form"+emisora_id).append('<div class="col-md-4"><label for="nombre_archivo">Ingesa el Nombre del Archivo:</label><input type="text" name="nombre_archivo" class="form-control" required></div>');
    
                                    } else {
                                        $("#form"+emisora_id).append('<div class="col-md-4"><label for="nombre_archivo">Ingesa el Nombre del Archivo:</label><input type="text" name="nombre_archivo" class="form-control" required></div>');
                                    }
                                    $("#form"+emisora_id).append('<div class="col-md-4"><label for="">¿Desea Generar el Archivo ' + response.tipo_archivo + '?</label><input type="submit" name="genera" class="button-style" value="Generar" id="btn-archivo"/></div>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="tipo_archivo" id="tipo_archivo" value="' + response.tipo_archivo + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="fechadispersion" id="fechadispersion" value="' + response.fechadispersion + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="idperiodo" id="idperiodo" value="' + response.idperiodo + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="idbanco" id="idbanco" value="' + response.idbanco + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="ejercicio" id="ejercicio" value="' + response.ejercicio + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="tipo_dispersion" id="tipo_dispersion" value="' + response.tipo_dispersion + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="idempleado" id="idempleado" value="' + response.idempleado + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="nombre_periodo" id="nombre_periodo" value="' + response.nombre_periodo + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="cadena_departamentos" id="cadena_departamentos" value="' + response.cadena_departamentos + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="tipoimporte" id="tipoimporte" value="' + response.tipoimporte + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="cuenta" id="cuenta" value="' + response.cuenta + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="TipoOperacion" id="TipoOperacion" value="' + response.TipoOperacion + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="referencia" id="referencia" value="' + response.referencia + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="banco_omitir" id="banco_omitir" value="' + response.banco_omitir + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="emisora_id" id="emisora_id" value="' + emisora_id + '"/>');
                                    $("#form"+emisora_id).append('<input type="hidden" name="concepto" id="concepto" value="' + response.concepto + '"/>');
                                });
    
    
                                
                                //alert(response.tipo_archivo);
                                $('#verDispersionModal').modal('show');
                                $(".generarArchivo").off().on("submit", function() {
                                    var emisoraid = $(this).data("emisoraid");
                                    var btnEnviar = $("#btn-archivo");
                                    var img = "{{asset('img/spinner.gif')}}";
                                    btnEnviar.html("<img src='" + img + "' style='width:20px' />"); // Para input de tipo button
                                    btnEnviar.attr("disabled", "disabled");
                                    setTimeout(function() {
                                        swal({
                                                title: "",
                                                text: "¿Esta seguro de generar la dispersión?",
                                                icon: "warning",
                                                buttons: true,
                                                dangerMode: true,
                                            })
                                            .then((willDelete) => {
                                                if (willDelete) {
                                                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                                                    var url = "{{route('procesos.dispersion.confirmar')}}";
                                                    $.ajax({
                                                        type: "POST",
                                                        url: url,
                                                        data: {
                                                            '_token': CSRF_TOKEN,
                                                            'id_periodo': $("#form" + emisoraid + " #idperiodo").val(),
                                                            'tipo_importe' : $("#form" + emisoraid + " #tipoimporte").val(),
                                                            'tipo_dispersion' : $("#form" + emisoraid + " #tipo_dispersion").val(),
        
                                                        },
                                                        dataType: 'JSON',
                                                        success: function(response) {
                                                            if (response.ok == 1) {
                                                                //$(".avisos tr#" + id).remove();
                                                                swal("", "La dispersión se confirmó correctamente.", "success");
                                                              
                                                            } else {
                                                               
                                                                swal("", "Ocurrió un error. Intente nuevamente.", "error");
                                                            }
                                                        }
                                                    });
                                                }
                                            });



                                       
    
                                    }, 2000);
                                });
                            } else {
                                $("#departamentosDiv").html('<h6 style="color:red;">El otro formulario</h6>');
    
                            }
                        },
                        error: function(data) {
    
                            alert("Problemas al tratar de enviar el formulario");
                        }
                    });
    
    
    
    
    
    
    
                }
                return false;
            });
        }
    
        /*
        $(function(){
    
            // al abrir el modal cargamos las prestaciones
            $('#administrarDispersionModal').on('shown.bs.modal', function (e) {
                var idperiodo = $(e.relatedTarget).data('periodo');
                var ejercicio = $(e.relatedTarget).data('ejercicio');
                $('#administrarDispersionModal .modal-body #idperiodo').val(idperiodo);
                $('#administrarDispersionModal .modal-body #ejercicio').val(ejercicio);
                $("#departamentosDiv").html("");
                $("#administrarDispersionModal .modal-body #enviar_banco").slideDown();
                
                
    
    
                $("#banco").off().on("change",function(){
                    $("#departamentosDiv").html('');
                    $("#administrarDispersionModal .modal-body #enviar_banco").slideDown();
                });
            
            });
    
            
        });
    
    
    
    
    
        */
    </script>
</body>


    