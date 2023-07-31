<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <style>
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(74%) sepia(11%) saturate(6958%) hue-rotate(2deg) brightness(104%) contrast(104%);
        }

        .empleados tr td .menu {
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
        }

        .empleados tr td .menu li {
            border-bottom: 1px solid #595c5f;
            padding: 5px 10px;
            color: black;
        }

        .empleados tr td .menu li:hover {
            background-color: #F0C018;
            color: #000;
            transition: background-color 0.5s ease-out;
        }

        .empleados tr td .menu li:hover a {
            color: #000;
        }

        .empleados tr td .menu a {
            color: #fff;
            text-decoration: none;
        }

        td a {
            color: black;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        td a:hover {
            color: #fbba00;
            cursor: pointer;
            text-decoration: none;
        }

        /*** RADIAL PROGRESS ***/
        /* Circumference = 2πr */
        /* π = 3.1415926535898 */
        /* r = 35 */

        svg.radial-progress {
            height: auto;
            max-width: 200px;
            padding: 0em;
            transform: rotate(-90deg);
            width: 85%;
        }

        svg.radial-progress circle {
            fill: rgba(0, 0, 0, 0);
            stroke: #fff;
            stroke-dashoffset: 219.91148575129;
            /* Circumference */
            stroke-width: 10;
        }

        svg.radial-progress circle.incomplete {
            opacity: 0.25;
        }

        svg.radial-progress circle.complete {
            stroke-dasharray: 219.91148575129;
            /* Circumference */
        }

        svg.radial-progress text {
            fill: #000;
            font: 400 1.2em/1 'Oswald', sans-serif;
            text-anchor: middle;
        }

        /*** COLORS ***/

        svg.radial-progress-success circle {
            stroke: #a2ed56;
        }

        svg.radial-progress-warning circle {
            stroke: #f0c018;
        }

        svg.radial-progress-danger circle {
            stroke: #bd2130 !important;
        }

        .input-search {
            width: 104px;
            text-align: center;
            border: 2px #c4c4c4 solid;
            border-radius: 6px;
            padding: 5px;
        }

        .button-style {
            font-size: 18px !important;
        }

        .btn.menu {
            position: relative;
        }
    </style>


    <div class="container">

        @include('includes.header',['title'=>'Control de empleados', 'subtitle'=>'Empleados', 'img'=>'/img/control-empleados.png', 'route'=>'bandeja'])

        <div class="row">
            <div class="col-md-6 mt-3">
                <div class="mt-4"></div>
                <a href="#" class="button-style config" alt="EDITAR COLUMNAS A VISUALIZAR" title="EDITAR COLUMNAS A VISUALIZAR" data-toggle="modal" data-target="#configColumnsModal"><img src="{{ asset('/img/empleados-columnas.png') }}" width="20px"></a>

                @if ($permisos->empleado_alta = 1)
                <a href="{{ route('empleados.crear') }}" ref="Crear empresareceptora">
                    <button type="button" class="button-style"> <img src="{{ asset('/img/icono-crear.png') }}" width="20px">
                        Crear nuevo
                    </button>
                </a>
                @endif
                @if ($permisos->empleado_importar = 1)
                <button data-toggle="modal" data-target="#importarModal" type="button" class="button-style nuevo text-nowrap"><img src="{{ asset('/img/icono-importar.png') }}" width="20px"> Importar</button>
                @endif

                @if ($permisos->empleado_exportar = 1)
                <a href="{{ route('empleados.exportar') }}" class="button-style nuevo text-nowrap" target="_blank"><img src="{{ asset('/img/icono-exportar.png') }}" width="20px"> Exportar</a>
                @endif

                @if ($permisos->edicion_masiva = 1)
                <button data-toggle="modal" data-target="#edMasivaModal" type="button" class="button-style mb-3 mr-2 nuevo text-nowrap"><img src="{{ asset('/img/icono-carpeta.png') }}" width="20px"> Edición masiva</button>
                @endif

            </div>

            <div class="col-md-4 mt-3">
                <span class="text-nowrap pt-2">| Buscar por:</span> <br>
                <select name="" id="departamento" class="input-search select-clase " style="width: 200px !important">
                    <option value="">DEPARTAMENTO/TODOS</option>
                    @foreach ($departamentos as $dep)
                    <option value="{{$dep->id}}">{{$dep->nombre}}</option>
                    @endforeach
                </select>

                <select name="" id="estatus" class="input-search select-clase" style="width: 200px !important">
                    <option value="1">ESTATUS</option>
                    <option value="1">ACTIVOS</option>
                    <option value="5">ELIMINADOS</option>
                    <option value="30">EN PROCESO DE CREACIÓN</option>
                </select>
            </div>

            <div class="col-md-2 mt-3 align-self-center">
                <div class="dataTables_filter" id="div_buscar"></div>
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
        @endif


        @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif

        <div class="article border mt-2">
            <table class="table empleados w-100 empleados-head" id="empleados-table">
                <thead>
                    <tr>
                        <th width="40px">ID</th>
                        <th width="40px">#</th>
                        <th width="74px">Fotografia</th>
                        <th width="17%">Nombre</th>
                        <th class="d-none estatus">Estatus</th>
                        <th class="d-none correo">Correo</th>
                        <th class="d-none departamento">Departamento</th>
                        <th class="d-none fecha_antiguedad">Fecha Ingreso</th>
                        <th class="d-none rfc">RFC</th>
                        <th class="d-none puesto">Puesto</th>
                        <th class="d-none ubicacion">Ubicacion</th>
                        <th class="d-none covid">Covid</th>
                        <th width="90px">Acciones</th>
                        <th width="90px">Completado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empleados as $empleado)
                    <tr id="{{$empleado->id}}" data-numempleado="{{$empleado->numero_empleado}}" data-nombreempleado="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-departamento="{{$empleado->id_departamento}}" data-estatus="{{$empleado->estatus}}" {{($empleado->estatus != 1) ? 'style=display:none;' : ''}}>
                        <td width="40px">{{$empleado->id}}</td>
                        <td width="40px">{{$empleado->numero_empleado}}</td>
                        @if(!empty($empleado->file_fotografia) && Storage::disk('public')->has('/repositorio/'.Session::get('empresa')['id'].'/'.$empleado->id.'/'.$empleado->file_fotografia))
                        <td width="94px"><img src="{{asset('/storage/repositorio/'.Session::get('empresa')['id'].'/'.$empleado->id.'/'.$empleado->file_fotografia)}}" class="rounded-circle img-fluid" width="94" height="26" alt="{{$empleado->file_fotografia}}"></td>
                        @else

                        <td width="94px"><img src="{{asset('/img/avatar.png')}}" class="rounded-circle img-fluid" width="94" height="26" alt="sin imagen"></td>

                        @endif
                        <td width="17%">
                            @if(!empty($empleado->file_fotografia) && file_exists('public/repositorio/'.Session::get('empresa')['id'].'/'.$empleado->id.'/'.$empleado->file_fotografia))
                            <a href="{{ route('empleados.info', $empleado->id) }}">{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}</a>

                            @else
                            <a href="{{ route('empleados.info', $empleado->id) }}">{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}</a>

                            @endif
                        </td>

                        <td width="15%" class="estatus d-none">
                            @if ($empleado->estatus == 1) <span class="text-success">ACTIVO</span>
                            @elseif ($empleado->estatus == 30) <span class="text-warning">En proceso de creación</span>
                            @elseif ($empleado->estatus == 5) <span class="text-danger">ELIMINADO</span>
                            @elseif ($empleado->estatus == 10) <span class="">DESHABILITADO</span>
                            @endif
                        </td>
                        <td class="d-none correo">{{$empleado->correo}}</td>
                        <td class="d-none departamento">{{$empleado->departamento->nombre}}</td>
                        <td class="d-none fecha_antiguedad">{{formatoAFecha($empleado->fecha_antiguedad)}}</td>
                        <td class="d-none rfc">{{$empleado->rfc}}</td>
                        <td class="d-none puesto">{{ (optional($empleado->puesto)->puesto) ?: ''}}</td>
                        <td class="d-none ubicacion">{{$empleado->ubicacion}}</td>
                        <td class="d-none covid">
                            @if(!empty($empleado->registro_covid) && !empty($empleado->registro_covid->first()->estatus))
                            @if($empleado->registro_covid->first()->estatus == 0)
                            {{-- <span class="badge badge-success" data-toggle="popover" title="Popover title" data-content="NEGATIVO A COVID">NEGATIVO A COVID</span> --}}
                            <a href="#" title="Negativo a covid" rel="Negativo a covid">
                                <img src="{{ asset('/img/Recurso 23.png') }}" width="25px" > 
                            </a>
                            
                            @elseif ($empleado->registro_covid->first()->estatus == 1)
                            {{-- <span class="badge badge-danger" data-toggle="popover" title="Popover title" data-content="POSITIVO A COVID">POSITIVO A COVID</span> --}}
                            <a href="#" title="Positivo a covid" rel="Positivo a covid">
                                <img src="{{ asset('/img/Recurso 22.png') }}" width="25px">
                            </a>
                            @elseif ($empleado->registro_covid->first()->estatus == 2)
                            {{-- <span class="badge badge-success" data-toggle="popover" title="Popover title" data-content="SUPERÓ COVID">SUPERÓ COVID</span> --}}
                            <a href="#" title="Superó covid" rel="Superó covid">
                                <img src="{{ asset('/img/Recurso 24.png') }}" width="25px" >
                            </a>
                            @endif
                            @else
                            {{-- <span class="badge badge-success" data-toggle="popover" title="Popover title" data-content="NEGATIVO A COVID">NEGATIVO A COVID</span> --}}
                            

                            <a href="#" title="Negativo a covid" rel="Negativo a covid">
                                <img src="{{ asset('/img/Recurso 23.png') }}" width="25px">
                            </a>
                            
                            @endif
                        </td>
                        <td width="110px" class="text-center position-relative">
                            <button data-id="{{$empleado->id}}" class="menubtn btn btn-sm mr-2" alt="Editar Departamento" title="Editar Departamento">
                                <img src="/img/icono-opciones.png" class="button-style-icon">
                            </button>
                            <ul class="menu text-left">
                                
                                <a href="{{ route('empleados.info', $empleado->id) }}">
                                    <li>Ver empleado</li>
                                </a>
                           
                                @if ($empleado->estatus == 5)
                                <a href="#" class="reactivar" data-id="{{$empleado->id}}">
                                    <li>Reactivar</li>
                                </a>
                                @else
                                
                                <a href="{{ route('empleados.editar', $empleado->id) }}">
                                    <li>Editar</li>
                                </a>
                              
                                @if ($empleado->estatus != 5)

                                <a class="eliminar_emp" data-id="{{$empleado->id}}">
                                    <li>Eliminar</li>
                                </a>
                                @endif
                                @if ($empleado->estatus == 1)

                                @if ($empleado->contratoActivo && !empty($empleado->file_contrato))
                                <a href=" {{ route('empleados.vercontrato', $empleado->id) }}"> {{-- {{route('empleados_bck.verContrato', $empleado->id)}} --}}
                                    <li>Ver contrato</li>
                                </a>

                                @elseif (!$empleado->existioContrato)
                                <a href="#" data-toggle="modal" class="ren_Contrato" data-target="#genContratoEmpleadosModal" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-idempleado="{{$empleado->id}}" data-urlactualizar="{{route('empleados.empleados')}}" data-contratoFechaVencimiento=""> {{-- {{route('empleados_bck.inicio')}} --}}
                                    <li>Generar contrato</li>
                                </a>

                                @elseif(!$empleado->contratoActivo && $empleado->existioContrato)
                                <a href="#" class="ren_Contrato" data-toggle="modal" data-target="#genContratoEmpleadosModal" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-idempleado="{{$empleado->id}}" data-urlactualizar="{{route('empleados.empleados')}}" data-contratoFechaVencimiento="{{$empleado->contratoFechaVencimiento}}">
                                    <li>Renovar contrato</li>
                                </a>
                                @endif
                                @endif


                                {{-- @if ($empleado->file_acuse == null)
                                <a href="#" data-toggle="modal" data-target="#cargarAcuseEmpleadosModal" data-nombreempleado="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-id="{{$empleado->id}}">
                                <li>Carga de acuse</li>
                                </a>
                                @else
                              
                                <a href="{{route('empleados.veracuse', $empleado->id)}}">
                                    <li>Ver acuse</li>
                                </a>
                              
                                @endif
                                --}}

                               
                                <a href="{{route('empleados.percepcionesDeducciones', $empleado->id)}}">
                                    <li>Percer/Deduc</li>
                                </a>
                              
                                @endif
                                <a href="{{route('empleados.covidinicio', $empleado->id)}}">
                                    <li>Seguimiento covid</li>
                                </a>

                                @if (array_key_exists('bajas', Session::get('usuarioPermisos')))
                                <a href="#" data-toggle="modal" data-target="#bajaEmpleadosModal" data-nombreempleado="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-fecha_alta="{{$empleado->fecha_alta}}" data-id="{{$empleado->id}}" data-correo="{{$empleado->correo}}">
                                    <li>Dar de baja</li>
                                </a>
                                @endif
                            </ul>
                        </td>
                        <td width="100px" class="">
                            <svg class="radial-progress @if($empleado->porcentaje >= 95) {{'radial-progress-success'}} @elseif ($empleado->porcentaje < 95 && $empleado->porcentaje >= 70)  {{'radial-progress-warning'}} @else {{'radial-progress-danger'}} @endif" data-percentage="{{$empleado->porcentaje}}" viewBox="0 0 80 80">
                                <circle class="incomplete" cx="40" cy="40" r="35"></circle>
                                <circle class="complete" cx="40" cy="40" r="35" style="stroke-dashoffset: 219.91148575129;"></circle>
                                <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">{{$empleado->porcentaje}}%</text>
                            </svg>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>





    <div class="modal fade" id="configColumnsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar las columnas a visualizar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            (Tienes un maximo de 4 Columnas)
                            <form method="post" action="{{ route('empleados.cambiarcolumna') }}" class="columnasForm mt-3" id="submit_column">
                                @csrf
                                <div class="form-check">
                                    <input id="campo_estatus" class="form-check-input" type="checkbox" name="campos[]" value="estatus" {{($columnas->contains('estatus')) ? 'checked' : ''}}>
                                    <label for="campo_estatus" class="form-check-label">Estatus</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_correo" class="form-check-input" type="checkbox" name="campos[]" value="correo" {{($columnas->contains('correo')) ? 'checked' : ''}}>
                                    <label for="campo_correo" class="form-check-label">Correo</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_fecha_antiguedad" class="form-check-input" type="checkbox" name="campos[]" value="fecha_antiguedad" {{($columnas->contains('fecha_antiguedad')) ? 'checked' : ''}}>
                                    <label for="campo_fecha_antiguedad" class="form-check-label">Fecha Ingreso</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_rfc" class="form-check-input" type="checkbox" name="campos[]" value="rfc" {{($columnas->contains('rfc')) ? 'checked' : ''}}>
                                    <label for="campo_rfc" class="form-check-label">RFC</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_covid" class="form-check-input" type="checkbox" name="campos[]" value="covid" {{($columnas->contains('covid')) ? 'checked' : ''}}>
                                    <label for="campo_covid" class="form-check-label">Covid</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_id_departamento" class="form-check-input" type="checkbox" name="campos[]" value="departamento" {{($columnas->contains('departamento')) ? 'checked' : ''}}>
                                    <label for="campo_id_departamento" class="form-check-label">Departamento</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_id_puesto" class="form-check-input" type="checkbox" name="campos[]" value="puesto" {{($columnas->contains('puesto')) ? 'checked' : ''}}>
                                    <label for="campo_id_puesto" class="form-check-label">Puesto</label>
                                </div>
                                <div class="form-check">
                                    <input id="campo_ubicacion" class="form-check-input" type="checkbox" name="campos[]" value="ubicacion" {{($columnas->contains('ubicacion')) ? 'checked' : ''}}>
                                    <label for="campo_ubicacion" class="form-check-label">Centro de Trabajo</label>
                                </div>
                                <br>
                                <br>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button-style-gray" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="button-style" id="column_change">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <form method="post" id="empleado_delete_form" action="{{ route('empleados.eliminarempleado') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="id_delete" value="">
    </form>


    @include('empleados.importar-empleados-modal')
    @include('empleados.generar-contrato-empleado-modal')
    @include('empleados.baja-empleado-modal')
    @include('empleados.cargar-acuse-modal')
    @include('empleados.edicion-masiva-modal')

    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script type="text/javascript">
    
        let dataSrc = [];
        let table = $('#empleados-table').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [3]).every(function(){

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
            order: [[3, 'asc']],
        });

        
        $('.menubtn').hover(
            function() {
                $('.empleados td .menu').fadeOut();
                $(this).next('.menu').fadeIn();
            },
            function() {}
        );

        $('.menu').hover(
            function() {
                $(this).show();
            },
            function() {
                $(this).fadeOut();
            }
        );

        $('.columnasForm input[type="checkbox"]').click(function() {
            if ($('.columnasForm input[type="checkbox"]:checked').length > 4) {
                $('.columnasForm .guardar').attr('disabled', true);

                if ($(this).is(':checked')) {
                    swal({
                        text: "Solo puedes escoger 4 columnas!",
                        icon: "warning",
                        button: "ok",
                    });
                }
            } else {
                $('.columnasForm .guardar').attr('disabled', false);
            }

        });

        $("#column_change").click(function() {

            document.getElementById("submit_column").submit();
        });

        @foreach($columnas as $col)
        $('.empleados td.{{$col}}, .empleados-head th.{{$col}}').removeClass('d-none');
        @endforeach
    </script>

    <script>
        $(function() {
            $('svg.radial-progress').each(function(index, value) {
                $(this).find($('circle.complete')).removeAttr('style');
            });

            $("#empleados-table").scroll(function() {
                $('svg.radial-progress').each(function(index, value) {

                    percent = $(value).data('percentage');
                    radius = $(this).find($('circle.complete')).attr('r');

                    circumference = 2 * Math.PI * radius;
                    strokeDashOffset = circumference - ((percent * circumference) / 100);

                    $(this).find($('circle.complete')).animate({
                        'stroke-dashoffset': strokeDashOffset

                });
            }).trigger('scroll');


            $("#spinner").addClass("ocultar");

        });
    </script>
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-36251023-1']);
        _gaq.push(['_setDomainName', 'jqueryscript.net']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>

    <script type="text/javascript">
        $(".eliminar_emp").click(function() {
            var id = $(this).data('id');
            swal({
                    title: "¿Está seguro de eliminar este registro?",
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

                        document.getElementById("empleado_delete_form").submit();

                    } else {
                        swal("La acción fue cancelada");
                    }
                });
        });

        $('#departamento').change(function() {
            departamentoid = $(this).val();
            buscar(departamentoid, 'departamento');
        });

        $('#num-empleado').change(function() {
            numEmpleado = $(this).val();
            buscar(numEmpleado, 'numempleado');
        });

        $('#estatus').change(function() {
            estatus = $(this).val();
            buscar(estatus, 'estatus');
        });

        $('#nombre-empleado').change(function() {
            nombreempleado = $(this).val().toUpperCase();
            buscar(nombreempleado, 'nombreempleado');
        });


        function buscar(valorABuscar, campo) {
            if (valorABuscar.trim() == '') {
                $(".empleados tbody tr").show();
            } else {
                $(".empleados tbody tr").hide();
                $(".empleados tbody tr").each(function() {
                    valor = $(this).data(campo) + '';
                    if (valor.indexOf(valorABuscar) > -1) {
                        $(this).show();
                    }
                });
            }
        }

        $(function() {
            $('.select-clase').select2();
        });

        $('#percent').on('change', function(){
          var val = parseInt($(this).val());
          var $circle = $('#svg #bar');
          
          if (isNaN(val)) {
           val = 100; 
          }
          else{
            var r = $circle.attr('r');
            var c = Math.PI*(r*2);
           
            if (val < 0) { val = 0;}
            if (val > 100) { val = 100;}
            
            var pct = ((100-val)/100)*c;
            
            $circle.css({ strokeDashoffset: pct});
            
            $('#cont').attr('data-pct',val);
          }
        });

    </script>