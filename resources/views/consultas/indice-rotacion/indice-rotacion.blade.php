<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<style type="text/css">
    .wrapper-table {
      
        margin-bottom: 20px;
        overflow-y: scroll;
     
    }

    .form-check-inline {
        align-items: top !important;
    }

    .invalido {
        color: #EE4A30;
    }

   

    label {
        font-weight: bold;
        margin-top: 15px;
    }

    .bg-gray {
        background-color: #fbba00 solid;
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
    }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00;
        color: gray;
    }

    .nav-tabs .nav-item.show .nav-link,
    .nav-tabs .nav-link.active {
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
</style>

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Índice de rotación de personal',
        'subtitle'=>'Consultas', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'bandeja'])

       
        <div class="row">
            <div class="col-md-12 text-center mt-4">
                <div class="article-nav border">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="periodo-tab" data-toggle="tab" href="#nav-periodo" role="tab" aria-controls="nav-periodo" aria-selected="true">Periodo</a>
                            <a class="nav-item nav-link" id="mensual-tab" data-toggle="tab" href="#nav-mensual" role="tab" aria-controls="nav-mensual" aria-selected="false">Mensual</a>
                            <a class="nav-item nav-link" id="anual-tab" data-toggle="tab" href="#nav-anual" role="tab" aria-controls="nav-anual" aria-selected="false">Anual</a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md12 mt-4">

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-periodo" role="tabpanel" aria-labelledby="periodo-tab">
                        <form class="busqueda" target="_blank" action="{{ route('exportar.rotacionPersonal') }}" method="post" id="busqueda1">
                            @csrf
                            <div class="col-md-8 d-flex my-2 float-rigth">
                                <input type="hidden" name="tipo" value="1"> <!-- periodo -->
                                <div class="col-md-8 d-flex my-2">
                                    <label class="font-weight-bold" style="width: 200px;" for="fecha_inicio">Fecha inicial</label>

                                    <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control  input-style-custom" autocomplete="off">
                                </div>
                                <div class="col-md-8 d-flex my-2">
                                    <label class="font-weight-bold" style="width: 200px;" for="fecha_fin">Fecha final</label>

                                    <input type="text" name="fecha_fin" id="fecha_fin" class="form-control  input-style-custom" autocomplete="off">
                                </div>
                                @if($tiene_sedes)
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <label class="font-weight-bold" for="sede"> Sede </label>
                                <select id="sede" class="form-control  input-style-custom" name="sede" required>
                                    <option value="0.GENERAL" selected>GENERAL</option>
                                    @foreach ($sedes as $sede)
                                    <option value="{{$sede->id}}.{{$sede->nombre}}">{{$sede->nombre}}</option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
                            <div class="col-md-4 d-flex float-rigth">
                                <a href="{{ route('bandeja') }}" class="btn button-style-cancel mb-3 mr-3 my-3">CANCELAR</a>
                                <button type="button" class="btn button-style mb-3 mr-3 my-3 btnbusca" id="btnbusca1" data-id="1">BUSCAR</button>

                            </div>
                        </form>
                    </div>


                    <div class="tab-pane fade" id="nav-mensual" role="tabpanel" aria-labelledby="mensual-tab">
                        <form class="busqueda" target="_blank" action="{{ route('exportar.rotacionPersonal') }}" method="post" id="busqueda2">
                            @csrf

                            <div class="col-md-12 d-flex my-2">
                                <input type="hidden" name="tipo" value="2"> <!-- mes -->

                                <div class="col-md-5 d-flex">
                                    <label class="font-weight-bold">Meses: </label>
                                    <div class="col-md-12 d-flex formulario">

                                        <div class="form-check-inline radio">
                                            <input class="form-check-input" type="radio" name="numero_meses" id="numero_meses1" value="1" checked>
                                            <label class="font-weight-bold" for="numero_meses1">
                                                1 MES
                                            </label>
                                        </div>
                                        <div class="form-check-inline radio">
                                            <input class="form-check-input" type="radio" name="numero_meses" id="numero_meses2" value="3">
                                            <label class="font-weight-bold" for="numero_meses2">
                                                3 MESES
                                            </label>
                                        </div>
                                        <div class="form-check-inline radio">
                                            <input class="form-check-input" type="radio" name="numero_meses" id="numero_meses3" value="6">
                                            <label class="font-weight-bold" for="numero_meses3">
                                                6 MESES
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex" style="width:450px;">
                                    <div class="col-md-8 d-flex my-2">
                                        <label class="font-weight-bold" for="mes_inicio" style="width: 200px;">Mes inicial </label>
                                        <input type="text" name="mes_inicio" id="mes_inicio" class="form-control  input-style-custom" autocomplete="off">
                                    </div>
                                    <div class="col-md-8 d-flex my-2">
                                        <label class="font-weight-bold" for="mes_fin" style="width: 200px;">Mes final </label>
                                        <input type="text" name="mes_fin" id="mes_fin" class="form-control  input-style-custom" autocomplete="off">
                                    </div>

                                </div>

                            </div>
                            <div class="col-md-5 d-flex my-2">
                                @if($tiene_sedes)
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <label class=" pt-2 mr-2" for="sede"> Sede </label>
                                <select id="sede" class="form-control mr-3" name="sede" required>
                                    <option value="0.GENERAL" selected>GENERAL</option>
                                    @foreach ($sedes as $sede)
                                    <option value="{{$sede->id}}.{{$sede->nombre}}">{{$sede->nombre}}</option>
                                    @endforeach
                                </select>
                                @endif
                            </div>

                            <div class="col-md-4 d-flex">
                                <a href="{{ route('bandeja') }}" class="btn button-style-cancel mb-3 mr-3 my-3">CANCELAR</a>
                                <button type="button" class="btn button-style mb-3 mr-3 my-3 btnbusca" id="btnbusca2" data-id="2">BUSCAR</button>
                                <button type="button" class="btn button-style mb-3 mr-3 my-3 btnexportar" id="" data-id="2">EXPORTAR</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="nav-anual" role="tabpanel" aria-labelledby="anual-tab">
                        <form class="busqueda" target="_blank" action="{{ route('exportar.rotacionPersonal') }}" method="post" id="busqueda3">
                            @csrf
                            <div class="d-flex" style="width:850px;">
                                <div class="col-md-8 d-flex ">
                                    <input type="hidden" name="tipo" value="3"> <!-- anio -->
                                    <div class="col-md-8 d-flex my-2">
                                        <label class="font-weight-bold" for="anio_inicio" style="width: 200px;">Año inicial </label>
                                        <input type="text" name="anio_inicio" id="anio_inicio" class="form-control  input-style-custom" autocomplete="off" style="width: 300px;">
                                    </div>
                                    <div class="col-md-8 d-flex my-2">
                                        <label class="font-weight-bold" for="anio_fin" style="width: 200px;">Año final </label>
                                        <input type="text" name="anio_fin" id="anio_fin" class="form-control  input-style-custom" autocomplete="off" style="width: 300px;">
                                    </div>
                                    @if($tiene_sedes)
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <label class="font-weight-bold" for="sede"> Sede </label>
                                    <select id="sede" class="form-control  input-style-custom" name="sede" required>
                                        <option value="0.GENERAL" selected>GENERAL</option>
                                        @foreach ($sedes as $sede)
                                        <option value="{{$sede->id}}.{{$sede->nombre}}">{{$sede->nombre}}</option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 d-flex">
                                <a href="{{ route('bandeja') }}" class="btn button-style-cancel mb-3 mr-3 my-3">CANCELAR</a>
                                <button type="button" class="btn button-style mb-3 mr-3 my-3 btnbusca" id="btnbusca3" data-id="3">BUSCAR</button>
                                <button type="button" class="btn button-style mb-3 mr-3 my-3 btnexportar" id="" data-id="3">EXPORTAR</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="movimientos" class="article border" style="width: 100% !important;">
                </div>

            </div>
            @include('includes.footer')
        

            <!-- fecha-->
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            <script src="{{asset('js/datapicker-es.js')}}"></script>

            <script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
            <script src="{{ asset('js/validate/jquery-validate-adicional.js') }}"></script>

            <script>
                var table;
                $(function() {

                    $("#busqueda1").validate({
                        errorClass: "invalido",
                        errorElement: "span",
                        errorPlacement: function(error, element) {
                            error.appendTo($('label[for=' + element.attr("id") + ']'));
                        },
                        rules: {
                            'fecha_inicio': {
                                required: true
                            },
                            'fecha_fin': {
                                required: true
                            },
                        },

                    });
                    $("#busqueda2").validate({
                        errorClass: "invalido",
                        errorElement: "span",
                        errorPlacement: function(error, element) {
                            error.appendTo($('label[for=' + element.attr("id") + ']'));
                        },
                        rules: {

                            'mes_inicio': {
                                required: true
                            },
                            'mes_fin': {
                                required: true
                            },
                            'numero_meses': {
                                required: true
                            }
                        },

                    });
                    $("#busqueda3").validate({
                        errorClass: "invalido",
                        errorElement: "span",
                        errorPlacement: function(error, element) {
                            error.appendTo($('label[for=' + element.attr("id") + ']'));
                        },
                        rules: {
                            'anio_inicio': {
                                required: true
                            },
                            'anio_fin': {
                                required: true
                            },
                        },

                    });


                    $(".btnbusca").on("click", function() {
                        var tipo = $(this).data('id');
                        if ($("#busqueda" + tipo).valid()) {
                            var img = "{{asset('img/spinner.gif')}}";
                            $(".btnbusca").html("<img src='" + img + "' style='width:20px' />");
                            $(".btnbusca, .btnexportar").attr("disabled", "disabled");
                            $('#movimientos').html('');
                            var url = "{{route('busqueda.rotacionPersonal')}}";
                            $.ajax({
                                type: "POST",
                                url: url,
                                data: $('#busqueda' + tipo).serialize(),
                                dataType: 'JSON',
                                success: function(response) {
                                    if (response.ok == 1) {
                                        $('#movimientos').append('<div id="contenedor_tabla"><table class="table w-100 movimientos text-center" id="tableIndice"><thead><tr><th>Fecha inicial</th><th>Fecha final</th><th>Sede</th><th>Trabajadores al inicio del periodo</th><th>Trabajadores al final del periodo</th><th>Promedio de trabajadores</th><th>Altas del periodo</th><th>Bajas del periodo</th><th>IRP</th></tr></thead><tbody id="contenidoIRP"></tbody></table></div>');
                                        if (response.tipo == 1) {
                                            $('#tableIndice #contenidoIRP').append('<tr><td>' + response.datos.finicio + '</td><td>' + response.datos.ffin + '</td><td>' + response.datos.sede + '</td><td>' + response.datos.i + '</td><td>' + response.datos.f + '</td><td>' + response.datos.promedio_total + '</td><td>' + response.datos.altas_periodo_total + '</td><td>' + response.datos.bajas_total + '</td><td>' + response.datos.irp + ' %</td><tr>');

                                            if (response.datos.irp >= 0 && response.datos.irp <= 5) {
                                                $('#movimientos').append('<div><center><table class="table w-100 movimientos text-center" id="tableIndice2""><tbody id="contenidoIRP2"></tbody></table></center></div>');
                                                $('#tableIndice2 #contenidoIRP2').append('<tr><td style="font-size:2em" >Índice de Rotación de Personal</td><td class="bg-warning" style="font-size:3em">' + response.datos.irp + ' %</td><tr><tr><td colspan="2" >El IRP del periodo es <b>BAJO</b></td></tr>');

                                            } else if (response.datos.irp > 5 && response.datos.irp <= 15) {
                                                $('#movimientos').append('<div><center><table class="table w-100 movimientos text-center" id="tableIndice2""><tbody id="contenidoIRP2"></tbody></table></center></div>');
                                                $('#tableIndice2 #contenidoIRP2').append('<tr><td style="font-size:2em" >Índice de Rotación de Personal</td><td class="bg-success" style="font-size:3em">' + response.datos.irp + ' %</td><tr><tr><td colspan="2" >El IRP del periodo es el <b>RECOMENDABLE</b></td></tr>');

                                            } else if (response.datos.irp > 15) {
                                                $('#movimientos').append('<div><center><table class="table w-100 movimientos text-center" id="tableIndice2"><tbody id="contenidoIRP2"></tbody></table></center></div>');
                                                $('#tableIndice2 #contenidoIRP2').append('<tr><td style="font-size:2em">Índice de Rotación de Personal</td><td class="bg-danger" style="font-size:3em">' + response.datos.irp + ' %</td><tr><tr><td colspan="2">El IRP del periodo es el <b>ALTO</b></td></tr>');

                                            }
                                        } else if (response.tipo == 2 || response.tipo == 3) {
                                            response.datos.forEach(datos => {
                                                if (datos.datos.irp >= 0 && datos.datos.irp <= 5) {
                                                    $('#tableIndice #contenidoIRP').append('<tr><td>' + datos.datos.finicio + '</td><td>' + datos.datos.ffin + '</td><td>' + datos.datos.sede + '</td><td>' + datos.datos.i + '</td><td>' + datos.datos.f + '</td><td>' + datos.datos.promedio_total + '</td><td>' + datos.datos.altas_periodo_total + '</td><td>' + datos.datos.bajas_total + '</td><td class="bg-warning">' + datos.datos.irp + ' %</td><tr>');

                                                } else if (datos.datos.irp > 5 && datos.datos.irp <= 15) {
                                                    $('#tableIndice #contenidoIRP').append('<tr><td>' + datos.datos.finicio + '</td><td>' + datos.datos.ffin + '</td><td>' + datos.datos.sede + '</td><td>' + datos.datos.i + '</td><td>' + datos.datos.f + '</td><td>' + datos.datos.promedio_total + '</td><td>' + datos.datos.altas_periodo_total + '</td><td>' + datos.datos.bajas_total + '</td><td class="bg-success">' + datos.datos.irp + ' %</td><tr>');

                                                } else if (datos.datos.irp > 15) {
                                                    $('#tableIndice #contenidoIRP').append('<tr><td>' + datos.datos.finicio + '</td><td>' + datos.datos.ffin + '</td><td>' + datos.datos.sede + '</td><td>' + datos.datos.i + '</td><td>' + datos.datos.f + '</td><td>' + datos.datos.promedio_total + '</td><td>' + datos.datos.altas_periodo_total + '</td><td>' + datos.datos.bajas_total + '</td><td class="bg-danger">' + datos.datos.irp + ' %</td><tr>');
                                                }
                                            });
                                            if (response.datos.length > 10) {
                                                $("#contenedor_tabla").addClass("wrapper-table");
                                            }
                                        }
                                    } else {
                                        swal("Ocurrió un error, intentar nuevamente.", {
                                            icon: "error",
                                        });
                                    }
                                    $(".btnbusca").html("BUSCAR");
                                    $(".btnbusca, .btnexportar").attr("disabled", false);
                                },
                                error: function() {
                                    swal("Ocurrió un error, intentar nuevamente.", {
                                        icon: "error",
                                    });
                                }
                            });
                        }
                        return false;
                    });


                    $(".btnexportar").on("click", function() {
                        var tipo = $(this).data('id');
                        if ($("#busqueda" + tipo).valid()) {
                            $("#busqueda" + tipo).submit();

                        }
                        return false;
                    });

                    $("#spinner").addClass("ocultar");

                });
            </script>

            <!-- fechas-->
            <script>
                $(function() {
                    $("#fecha_inicio").datepicker("setDate", "{{date('Y-m-d')}}");


                    $("#fecha_inicio").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeYear: true,
                        changeMonth: true,

                    });
                    $("#fecha_fin").datepicker("setDate", "{{date('Y-m-d')}}");


                    $("#fecha_fin").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeYear: true,
                        changeMonth: true,

                    });

                    $("#mes_inicio").datepicker("setDate", "{{date('m-yy')}}");


                    $("#mes_inicio").datepicker({
                        dateFormat: 'm-yy',
                        changeYear: true,
                        changeMonth: true,

                    });
                    $("#mes_fin").datepicker("setDate", "{{date('m-yy')}}");


                    $("#mes_fin").datepicker({
                        dateFormat: 'm-yy',
                        changeYear: true,
                        changeMonth: true,

                    });

                    $("#anio_inicio").datepicker("setDate", "{{date('yy')}}");


                    $("#anio_inicio").datepicker({
                        dateFormat: 'yy',
                        changeYear: true,


                    });
                    $("#anio_fin").datepicker("setDate", "{{date('yy')}}");


                    $("#anio_fin").datepicker({
                        dateFormat: 'yy',
                        changeYear: true,

                    });


                });
            </script>