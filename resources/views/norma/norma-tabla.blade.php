<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

    @include('includes.header',['title'=>'Implementación',
        'subtitle'=>'Norma 035', 'img'=>'img/header/norma/icono-norma.png',
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
            <div class="col-lg-5 col-md-12">
                <button class="button-style" 
                        data-toggle="modal" 
                        data-toggle="tooltip" 
                        title="Nueva implementación" 
                        data-target="#implementacionModal" 
                        data-id="'.$row->id.'">
                        <img src="/img/icono-crear.png" class="button-style-icon">Nueva implementación
                </button>
                <button id="btnGroupDrop1" type="button" class="button-style dropdown-toggle " data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Cuestionarios
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item" href="{{route('norma.implementacion.cuestionario.pdf',4)}}" target="_blank">Información personal</a>
                    <a class="dropdown-item" href="{{route('norma.implementacion.cuestionario.pdf',1)}}" target="_blank">Guía de referencia I </a>
                    <a class="dropdown-item" href="{{route('norma.implementacion.cuestionario.pdf',2)}}" target="_blank">Guía de referencia II</a>
                    <a class="dropdown-item" href="{{route('norma.implementacion.cuestionario.pdf',3)}}" target="_blank">Guía de referencia III</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 mt-1">
                <form action="{{route('nom035.exportar.empleados')}}" method="get" id="form-export" >
                    @csrf
                    <select name="id" id="exportEmp" class="form-control input-style-custom select-clase" style="width: 100%!important;">
                        <option selected disabled>Exportar empleados sin contestar encuesta por sede</option>
                        <option value="">TODOS</option>
                        @foreach ($sedes as $sede)
                            <option value="{{$sede->id}}">{{$sede->nombre}}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="dataTables_filter mb-12 col-xs-12 col-md-12 col-lg-3 mt-1" id="div_buscar"></div>
        </div>
        <br>
        <div class="article border ">
          
                <table class="table w-100 implementacion">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estatus</th>
                            <th>Encargados</th>
                            <th style="width:15%;">Razon social</th>
                            <th>Sede</th>
                            <th >Participantes</th>
                            <th >Excentos</th>
                            <th style="width:300px !important;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <form action="" role="form" id="accionImplementacion" method="post">
            @csrf
            <input type="hidden" id="implementacion" name="implementacion" value="" />
            <input type="hidden" id="accion" name="accion" value="" style="width:500px" />
        </form>
        @include('norma.implementacion.implementacion-modal')
    </div>

    <form action="" role="form" id="accionImplementacion" method="post">
        @csrf
        <input type="hidden" id="implementacion" name="implementacion" value="" />
        <input type="hidden" id="accion" name="accion" value="" style="width:500px" />
    </form>
    @include('norma.implementacion.implementacion-modal')
    </div>
    @include('includes.footer')
    @include('includes.spinner')
    <style>
        td.details-control {
            background-size: 20px;
            background-position: 50% 25%;
            padding: auto;
            background-image: url("{{asset('/img/icono-detalle.png')}}");
            background-repeat: no-repeat;
            cursor: pointer;

        }

        tr.details td.details-control {
            background-size: 20px;
            background-position: 50% 25%;
            padding: auto;
            background-image: url("{{asset('/img/icono-contrario-detalle.png')}}");
            background-repeat: no-repeat;
        }

        .excentos_tabla {
            font-size: 7px;
            justify-content: center;
          

        }

        td.details-encargado {
            background-size: 20px;
            background-position: 50% 25%;
            padding: auto;
            background-image: url("{{asset('/img/icono-usuario.png')}}");
            background-repeat: no-repeat;
            cursor: pointer;

        }

        tr.details td.details-encargado {
            background-size: 20px;
            background-position: 50% 25%;
            padding: auto;
            background-image: url("{{asset('/img/icono-contrario-detalle.png')}}");
            background-repeat: no-repeat;
        }

        .encargados_tabla {
            font-size: 7px;
            justify-content: center;
           
        }
    </style>



    <script>
    $(function(){

        $('.select-clase').select2();
        let table = $('.implementacion').DataTable({
            initComplete: function(oSettings, json) {
                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
            processing: true,
            serverSide: true,
            lengthChange: false,
            language: {
                search: '',
                searchPlaceholder: 'Buscar registros',
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            ajax: "{{ route('norma.normaTabla') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'fecha_inicio',
                    name: 'fecha_inicio'
                },
                {
                    data: 'fecha_fin',
                    name: 'fecha_fin'
                },
                {
                    data: 'estatus',
                    name: 'estatus'
                },
                {
                    "class": "details-encargado",
                    "orderable": false,
                    "data": 'encargados.length',
                    "defaultContent": ""
                    /*   data: 'encargados',
                      name: 'encargados' */
                },
                {
                    data: 'razon_social',
                    name: 'razon_social'
                },
                {
                    class: 'text-center',
                    data: 'sede',
                    name: 'sede'
                },
                {
                    class: "text-center",
                    data: 'participantes',
                    name: 'participantes'
                },
                {
                    "class": "details-control",
                    "orderable": false,
                    "data": 'total_excentos.length',
                    "defaultContent": ""
                },
                {
                    class: "text-center",
                    data: 'acciones',
                    name: 'acciones',
                    orderable: false,
                    searchable: false
                },
            ],
            order: [0, 'desc'],
            columnDefs: [{
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5]
                },

            ],
            rowId: 'id'
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $(".implementacion").on("click", ".enviaf", function() {
                enviaFormulario($(this).data('implementacion'), $(this).data('enlace'));
            });

            function enviaFormulario(implementacion, ruta) {
                $("#implementacion").val(implementacion);
                $("#accion").val(ruta);
                $("#accionImplementacion").attr('action', ruta);
                $("#accionImplementacion").submit();
            }


            var detailRows = [];

            $('.implementacion').on('click', 'tr td.details-control', function() {
                var tr = $(this).closest('tr');
                //console.log(tr);
                var row = table.row(tr);
                //console.log(row);
                var idx = $.inArray(tr.attr('id'), detailRows);
                //console.log(idx)
                if (row.child.isShown()) {
                    tr.removeClass('details');
                    row.child.hide();
                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    tr.addClass('details');
                    row.child(format(row.data())).show();
                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });
            $('.implementacion').on('click', 'tr td.details-encargado', function() {
                var tr = $(this).closest('tr');
                //console.log(tr);
                var row = table.row(tr);
                //console.log(row);
                var idx = $.inArray(tr.attr('id'), detailRows);
                //console.log(idx)
                if (row.child.isShown()) {
                    tr.removeClass('details');
                    row.child.hide();
                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    tr.addClass('details');
                    row.child(formatencargado(row.data())).show();
                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });



            table.on('draw', function() {
                // alert(table.data().count());
            });
            $('#exportEmp').change(function() {      
                $("#form-export").submit();
            });

            function format(d) {

                var mensaje = "";
                if (d.excentos.length > 0) {
                    d.excentos.forEach(function callback(excento, index) {
                        mensaje += '<div class="col-3 themed-grid-col" style="font-family: Hind; font-size: small;">' + excento.nombre + ' ' + excento.paterno + ' ' + excento.materno + '</div>';
                    });
                } else {
                    mensaje = ' <div class="col-12 themed-grid-col" style="font-family: Hind; font-size: small;"><center>No hay empleados exentos</center></div>';
                }
                return '<div class="row mb-3  excentos_tabla" style="font-family: Hind; font-size: small;">' + mensaje + '</div><br/>';
            }


            function formatencargado(d) {
                console.log(d);
                var mensaje = "";
                if (d.encargados.length > 0) {
                    d.encargados.forEach(function callback(encargados, index) {
                        mensaje += '<div class="col-3 themed-grid-col" style="font-family: Hind; font-size: small;">' + encargados.nombre + '</div>';
                    });
                } else {
                    mensaje = ' <div class="col-12 themed-grid-col" style="font-family: Hind; font-size: small;"><center>No hay encargados</center></div>';
                }
                return '<div class="row mb-3  encargados_tabla" style="font-family: Hind; font-size: small;">' + mensaje + '</div><br/>';
            }

        });
        $("#spinner").toggle();
    });
    </script>