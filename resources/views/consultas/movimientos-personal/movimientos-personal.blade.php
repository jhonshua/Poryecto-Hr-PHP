<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Reporte movimientos de personal',
        'subtitle'=>'Consultas', 'img'=>'img/icono-parametros-empresa.png',
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



        <div class="row">

            <form action="{{route('exportar.movimientoPersonal')}}" target="_blank" method="post" style="width:100%;" id="busqueda">
                @csrf
                <div class="col-md-12 d-flex my-2">

                    <label class="font-weight-bold" for="fecha_inicio">Fecha inicial </label>
                    <input type="text" name="fecha_inicio" id="fecha_inicio" class="form-control input-style-custom pt-2 mr-2 ml-2" style="width: 200px;" autocomplete="off">



                    <label class="font-weight-bold" for="fecha_fin">Fecha final </label>
                    <input type="text" name="fecha_fin" id="fecha_fin" class="form-control input-style-custom pt-2 mr-2 ml-2" style="width: 200px;" autocomplete="off">

                    <label class=" pt-2 mr-2" for="departamentos" alt="Selecciona los departamentos a incluir en el reporte" title="Selecciona los departamentos a incluir en el reporte"> Departamentos: </label>
                    <select style="visibility: hidden;" id="departamentos" class="form-control input-style-custom mr-3" name="departamentos[]" multiple="multiple" required>
                        @foreach ($departamentos as $departamento)
                        <option value="{{$departamento->id}}" selected>{{$departamento->nombre}}</option>
                        @endforeach
                    </select>

                    @if($tiene_sedes)
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <label class=" pt-2 mr-2" for="sedes"> Sede </label>
                    <select id="sedes" class="form-control input-style-custom mr-3" name="sedes[]" multiple="multiple" required>
                        @foreach ($sedes as $sede)
                        <option value="{{$sede->id}}" selected>{{$sede->nombre}}</option>
                        @endforeach
                    </select>
                    @endif

                </div>


                <div class="col-md-8 d-flex">
                    <label class=" pt-2 mr-2">Movimientos: </label>
                    <div class="col-md-2 d-flex formulario">

                        <div class="form-check-inline checkbox">
                            <input class="form-check-input" type="checkbox" name="altas" id="altas" value="1" checked>
                            <label class="form-check-label" for="altas">
                                Altas
                            </label>
                        </div>
                        <div class="form-check-inline checkbox">
                            <input class="form-check-input" type="checkbox" name="bajas" id="bajas" value="1">
                            <label class="form-check-label" for="bajas">
                                Bajas
                            </label>
                        </div>



                    </div>
                </div>
                <div class="col-md-12 d-flex">
                    <a href="{{ route('bandeja') }}" class="btn button-style-cancel mb-3 text-nowrap mr-3 my-3">CANCELAR</a>
                    <button type="button" class="mx-1 btn button-style mb-3 mr-3 my-3" id="btnbusca" data-id="">BUSCAR</button>
                    <button type="button" class="mx-1 btn button-style mb-3 mr-3 my-3" id="btnexportar" data-id="">EXPORTAR</button>

                </div>
            </form>
        </div>
        <div class="dataTables_filter col-xs-12 col-md-12 mb-3" id="div_buscar"></div>

        <div id="movimientos" class="article border">

        </div>
    </div>
    @include('includes.footer')

    <link href="{{ asset('css/bootstrap-multiselect.min.css') }}" rel="stylesheet">


    <style>
        .form-check-inline {
            align-items: top !important;
        }

        .invalido {
            color: #EE4A30;
        }
    </style>

    <script src="{{asset('js/bootstrap-multiselect.min.js')}}" type="text/javascript"></script>

    <script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/validate/jquery-validate-adicional.js') }}"></script>

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>

    <!-- fecha-->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{asset('js/datapicker-es.js')}}"></script>

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

        });
    </script>

    <script>
        var table;

        $(function() {

            $('#departamentos, #sedes').multiselect({
                includeSelectAllOption: true,
                selectAllText: ' TODOS',
                nonSelectedText: 'NINGUNO',
                nSelectedText: 'SELECCIONADO',
                allSelectedText: 'TODOS',
                buttonWidth: '180px',
            });

            $("#busqueda").validate({
                errorClass: "invalido",
                errorElement: "span",
                errorPlacement: function(error, element) {
                    //error.appendTo( $('label[for='+element.attr("id")+']') );
                    error.appendTo($('label[for=' + element.attr("id") + ']'));
                },
                rules: {
                    'fecha_inicio': {
                        required: true
                    },
                    'fecha_fin': {
                        required: true
                    },
                    'departamentos[]': {
                        required: true
                    },
                    'sedes[]': {
                        required: true
                    },
                },

            });


            $("#btnbusca").on("click", function() {
                if ($("#busqueda").valid()) {
                    var img = "{{asset('img/spinner.gif')}}";
                    $(".btnbusca").html("<img src='" + img + "' style='width:20px' />");


                    $("#movimientos").html("");
                    var url = "{{route('busqueda.movimientoPersonal')}}";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $('#busqueda').serialize(),
                        dataType: 'JSON',
                        success: function(response) {
                            if (response.ok == 1) {
                                $('#movimientos').append('<div class="wrapper-table"><table class="table w-100 movimientos" id="tableMovimientos"><thead><tr><th class="text-center" >Id</th><th style="width:150px" class="text-center" >Nombre</th><th class="text-center" >Departamento</th><th class="text-center" >Puesto</th><th class="sde text-center">Sede</th><th class="text-center" >Estatus</th><th class="text-center" >Fecha alta</th><th class="text-center" >Fecha baja</th><th class="text-center" >Causa baja</th><th class="text-center" >Finiquito firmado</th><th class="text-center" >Finiquitado</th></tr></thead><tbody id="contenido" class="text-center"></tbody></table></div>');

                                response.movimientos.forEach(dato => {});

                                $(document).ready(function() {
                                    let dataSrc = [];
                                    let table = $('#tableMovimientos').DataTable({
                                        data: response.movimientos,
                                        scrollCollapse: true,
                                        "language": {
                                            search: '',
                                            searchPlaceholder: 'Buscar registros',
                                            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"

                                        },
                                        initComplete: function() {

                                            let api = this.api();

                                            api.cells('tr', [2]).every(function() {

                                                let data = $('<div>').html(this.data()).text();
                                                if (dataSrc.indexOf(data) === -1) {
                                                    dataSrc.push(data);
                                                }
                                            });
                                            dataSrc.sort();

                                            $('.dataTables_filter input[type="search"]', api.table().container())
                                                .typeahead({
                                                    source: dataSrc,
                                                    afterSelect: function(value) {
                                                        api.search(value).draw();
                                                    }
                                                });

                                            // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                                            let elementos = $(".dataTables_filter > label > input").detach();
                                            elementos.appendTo('#div_buscar');
                                            $("#div_buscar > input").addClass("input-style-custom");
                                        },
                                        columns: [{
                                                data: 'id',
                                                name: 'id'
                                            },
                                            {
                                                data: 'nombre',
                                                name: 'nombre'
                                            },
                                            {
                                                data: 'departamento',
                                                name: 'departamento'
                                            },
                                            {
                                                data: 'puesto',
                                                name: 'puesto'
                                            },
                                            {
                                                data: 'sede',
                                                name: 'sede'
                                            },
                                            {
                                                data: 'estatus',
                                                name: 'estatus'
                                            },
                                            {
                                                data: 'fecha_alta',
                                                name: 'fecha_alta'
                                            },
                                            {
                                                data: 'fecha_baja',
                                                name: 'fecha_baja'
                                            },
                                            {
                                                data: 'causa_baja',
                                                name: 'causa_baja'
                                            },
                                            {
                                                data: 'estatus_firma_finiquito',
                                                name: 'estatus_firma_finiquito'
                                            },
                                            {
                                                data: 'finiquitado',
                                                name: 'finiquitado'
                                            },

                                        ],
                                        columnDefs: [{
                                                className: 'text-center',
                                                targets: [0, 1, 2, 3, 4, 5]
                                            },

                                        ],
                                        rowId: 'id'
                                    });
                                });

                            } else {
                                swal("", "Ocurrió un error!", "error");
                            }
                        },
                        error: function() {
                            swal("", "Ocurrió un error!", "error");
                        }
                    });
                }
                return false;
            });


            $("#btnexportar").on("click", function() {
                if ($("#busqueda").valid()) {
                    $("#busqueda").submit();

                }
                return false;
            });

        });
    </script>