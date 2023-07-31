<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>

    @include('includes.navbar')


    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>

    <style type="text/css">

        .input-search {
            width: 105px;
            text-align: center;
            border: 2px #c4c4c4 solid;
            border-radius: 6px;
            padding: 5px;
            font-size: 15px;
        }

        .wrapper-table {
            max-height: 70vh;
            margin-bottom: 20px;
            overflow-y: auto;
            width: 100%;
        }

        .btn.menu {
            position: relative;
        }

        ul.menu {
            box-shadow: #666 3px 4px 8px;
            background-color: #cbcac6;
            display: none;
            list-style: none;
            left: -140px;
            padding: 0px;
            position: absolute;
            top: 30px;
            width: 190px;
            z-index: 10;
            text-align: left;
            color: black;
            font-weight: bold;
        }

        ul.menu li {
            padding: 10px;
            border-bottom: 1px solid #999a9e;
        }

        .menu a li {
            color: black;
        }

        ul.menu a li:hover {
            transition: all 0.1s;
            background-color: #F0C018;
        }

        ul.menu a li:hover a {
            font-size: 14px;
            text-decoration: none;
            color: #000000;
        }

        .periodos .btn {
            padding: 10px;
        }
    </style>

    <div class="container">

        @include('includes.header',['title'=>'Periodos de nómina',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-nomina-periodos.png',
        'route'=>'bandeja'])
        <div class="row">
            <div class="col-md-8">
                <span class="text-nowrap pt-2 mr-2">Mes: </span>
                <select name="" id="mes" class="input-search  mr-3 select-clase">
                    <option value="">TODOS</option>
                    @for ($mes = 1; $mes <= 12; $mes++) <option value="{{mes(str_pad($mes, 2, "0", STR_PAD_LEFT))}}">{{mes(str_pad($mes, 2, "0", STR_PAD_LEFT))}}</option>
                        @endfor
                </select>

                <span class="text-nowrap pt-2 mr-2">Tipo: </span>
                <select name="" id="tipo" class="input-search mr-3 select-clase">
                    <option value="">TODOS</option>
                    <option value="SEMANAL">SEMANAL</option>
                    <option value="QUINCENAL">QUINCENAL</option>
                </select>
                <span class="text-nowrap pt-2 mr-2">Estatus: </span>
                <select name="" id="activo" class="input-search  mr-3 select-clase">
                    <option value="">TODOS</option>
                    <option value="1">ACTIVO</option>
                    <option value="2">CERRADO</option>
                </select>

                <button type="button" class="button-style ml-2  mt-2 nuevo text-nowrap" data-toggle="modal" data-target="#periodoModal" data-id="">
                    <img src="/img/icono-crear.png" class="button-style-icon">
                    Crear
                </button>
            </div>

            <div class="col-md-4">
                <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-12 mt-1" style="float: right;" id="div_buscar"></div>
            </div>

        </div>

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


        <div class="article border mt-3">

            <table class="table mb-0 " id="periodonomina">
                <thead>
                    <tr>
                        <th>periodo</th>
                        <th width="200" class="text-center">Nombre Periodo</th>
                        <th width="30" class="text-center">#</th>
                        <th width="150" class="text-center">Fecha inicial</th>
                        <th width="150" class="text-center">Fecha final</th>
                        <th width="150" class="text-center">Fecha pago</th>
                        <th width="100" class="text-center">Ejercicio</th>
                        <th width="100" class="text-center">Timbres</th>
                        <th width="" class="text-center">Estatus</th>
                        <th width="100">Opciones</th>
                    </tr>
                </thead>

                <tbody class="periodos">
                    @foreach ($periodos as $periodo)

                        <tr id="{{$periodo->id}}" data-id="{{$periodo->id}}" data-numero_periodo="{{$periodo->numero_periodo}}" data-mes="{{mes(date('m', strtotime($periodo->fecha_inicial_periodo)))}}" data-activo="{{$periodo->activo}}" data-tipo="{{strtoupper($periodo->nombre_periodo)}}">

                            <td>{{$periodo->id}}</td>
                            <td width="180" class="text-center">{{strtoupper($periodo->nombre_periodo)}}</td>
                            <td width="30" class="text-center">{{$periodo->numero_periodo}} </td>
                            <td width="150" class="text-center">{{formatoAFecha($periodo->fecha_inicial_periodo)}}</td>
                            <td width="150" class="text-center">{{formatoAFecha($periodo->fecha_final_periodo)}}</td>
                            <td width="150" class="text-center">{{formatoAFecha($periodo->fecha_pago)}}</td>
                            <td width="100" class="text-center">{{$periodo->ejercicio}}</td>
                            <td width="150" class="text-center {{(!$periodo->timbres_count)?'text-black-50':''}}">{{$periodo->timbres_count}} Timbre(s)</td>
                            <td width="" class="text-center">

                                @if ($periodo->activo == App\Models\PeriodosNomina::ACTIVO)
                                <span class="text-success">Periodo en Proceso de Calculo</span>
                                @elseif ($periodo->activo == App\Models\PeriodosNomina::CERRADO)
                                <span class="text-black-50">Periodo Cerrado</span>
                                @elseif ($periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR || $periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR_PERIODO_ACT)
                                <span class="text-danger">Período Sin Calcular</span>
                                @endif
                            </td>


                            <td width="" class="text-center" width="100">
                                <button data-id="{{$periodo->id}}" class="menu btn mr-2">
                                    <img src="/img/icono-opciones.png" class="button-style-icon">

                                    <ul class="menu" style="display: none">

                                        @if ($periodo->activo == App\Models\PeriodosNomina::CERRADO && $permisos->abrir_nomina == 1 && !$hayPeriodoAbierto)
                                            <a href="{{ route('nomina.reabrir' , $periodo->id) }}">
                                                <li>
                                                    Re-Abrir Periodo
                                                </li>
                                            </a>
                                        @endif

                                        @if ($parametros->biometrico)
                                            <a href="{{route('nomina.asistencia', $periodo->id)}}" class="color_li">
                                                <li>

                                                    Asistencia Excel

                                                </li>
                                            </a>
                                        @endif

                                        @if ($parametros->biometrico == 1 && $periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR)
                                            <a href="#" data-toggle="modal" data-target="#abrirPModal" data-id="{{$periodo->id}}" data-fecha_inicial_periodo="{{$periodo->fecha_inicial_periodo}}" data-fecha_final_periodo="{{$periodo->fecha_final_periodo}}" data-dias_periodo="{{$periodo->dias_periodo}}">
                                                <li>

                                                    Abrir Periodo
                                                </li>
                                            </a>
                                        @elseif ($parametros->biometrico == 0 && $periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR)
                                            <a href="{{ route('nomina.abrirNomina', ['idPeriodo' => $periodo->id]) }}">
                                                <li>
                                                    Abrir Periodo
                                                </li>
                                            </a>
                                        @endif

                                        @if ($periodo->activo != App\Models\PeriodosNomina::CERRADO )
                                            <a href="#" data-toggle="modal" data-target="#periodoModal" class="edit_nomina" data-id="{{$periodo->id}}" data-numero_periodo="{{$periodo->numero_periodo}}" data-nombre_periodo="{{$periodo->nombre_periodo}}" data-fecha_inicial_periodo="{{$periodo->fecha_inicial_periodo}}" data-fecha_final_periodo="{{$periodo->fecha_final_periodo}}" data-fecha_pago="{{$periodo->fecha_pago}}" data-especial="{{$periodo->especial}}">
                                                <li>
                                                    Editar
                                                </li>
                                            </a>
                                            <a href="#" class="eliminarP" data-id="{{$periodo->id}}">
                                                <li>
                                                    Eliminar
                                                </li>
                                            </a>
                                        @endif

                                        @if ($periodo->activo == App\Models\PeriodosNomina::ACTIVO )
                                            <a href="{{ route('nomina.actualizar', $periodo->id) }}">
                                                <li>
                                                    Actualizar
                                                </li>
                                            </a>
                                            <a href="#" data-id="{{$periodo->id}}" data-toggle="modal" data-target="#cerrarPeriodoModal" id="cerrar_modal">
                                                <li>
                                                    Cerrar
                                                </li>
                                            </a>
                                        @endif

                                        @if ($periodo->activo == App\Models\PeriodosNomina::CERRADO )
                                            <a href="#" data-toggle="modal" data-target="#imprimirPModal" data-id="{{$periodo->id}}">
                                                <li>
                                                    Imprimir Nomina
                                                </li>
                                            </a>
                                            <a href="{{ route('timbrado.generarmasivo',$periodo->id) }}">
                                                <li>
                                                    Imprimir PDFs Masivos
                                                </li>
                                            </a>
                                            <a href="{{ route('timbrado.descargarcdfis',$periodo->id) }}">
                                                <li>
                                                    Descargar CFDIs
                                                </li>
                                            </a>
                                            <a href="{{ route('timbrado.descargar-zip_PDF', $periodo->id) }}">
                                                <li>Descargar CFDIs PDFs</li>
                                            </a>
                                            <a href="{{ route('timbrado.nomina.resumen_xls', $periodo->id) }}">
                                                <li>Resumen CFDIs Excel</li>
                                            </a>
                                        @endif
                                    </ul>


                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>


        </div>

    </div>

    @include('includes.footer')
    @include('nomina.crear-periodo-nomina')
    @include('nomina.abrir-biometrico-modal')
    @include('nomina.biometrico-modal')
    @include('nomina.cerrar-periodo-modal')
    @include('nomina.imprimir-nomina-modal')

    <form method="post" id="periodos_delete_form" action="{{ route('nomina.eliminarperiodo') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="id_delete" value="">
    </form>

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script type="text/javascript">
        let dataSrc = [];
        let table = $('#periodonomina').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "paging": false,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre periodo',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            columns:[
                {'visible' : false , 'searchable': false},
                null,null,null,null,null,null,null,null,null,
            ],
            initComplete: function() {
                let api = this.api();
            
                api.cells('tr', [1]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
            order: [[0, 'desc']]
        });


        $('#mes').change(function() {
            mes = $(this).val();
            buscar(mes, 'mes');
        });

        $('#activo').change(function() {
            activo = $(this).val();
            buscar(activo, 'activo');
        });

        $('#tipo').change(function() {
            tipo = $(this).val();
            buscar(tipo, 'tipo');
        });

        $('#numero_periodo').change(function() {
            numero_periodo = $(this).val();
            buscar(numero_periodo, 'numero_periodo');
        });

        function buscar(valorABuscar, campo) {
            if (valorABuscar.trim() == '') {
                $(".periodos tr").show();
            } else {
                $(".periodos tr").hide();
                $(".periodos tr").each(function() {
                    valor = $(this).data(campo) + '';
                    if (valor.indexOf(valorABuscar) > -1) {
                        $(this).show();
                    }
                });
            }
        }

        $('.btn.menu').hover(function() {
            $(this).find('ul.menu').fadeIn('fast');
        }, function() {
            $(this).find('ul.menu').fadeOut('fast');
        });

        $(".eliminarP").click(function() {
            id = $(this).data('id');

            swal({
                    title: "Estas seguro de eliminar el registro",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        document.getElementById("id_delete").value = id;
                        swal("Espere un momento, la información esta siendo procesada", {
                            icon: "success",
                            buttons: false,
                        });

                        document.getElementById("periodos_delete_form").submit();

                    } else {
                        swal("La accion fue cancelada!");
                    }
                });

        });

        $("#cerrar_modal").click(function() {
            id = $(this).data('id');
            document.getElementById("cerrar_nomina").value = id;
        });


        $(function() {
            $('.select-clase').select2();
        });
    </script>