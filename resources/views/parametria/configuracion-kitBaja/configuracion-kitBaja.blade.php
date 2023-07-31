<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=>'Configuración kit de baja',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-nomina-conceptos.png',
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

        @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif

        <div class="">

            <button type="button" class="button-style ml-3 mb-3" data-toggle="modal" data-target="#controlExpedienteModal">
                <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>




        <div class="article border ">
            <table class="table w-100 campos" id="tabla_configuracionKit">
                <thead style="text-align: center;">
                    <tr>
                        <th>Id</th>
                        <th>Alias/Etiqueta</th>
                        <th>Nombre Id</th>
                        <th>Obligatorio</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach ( $campos as $campo )
                    <tr id="{{ $campo->id }}">
                        <td> {{ $campo->id }} </td>
                        <td> {{ strtoupper($campo->alias) }} </td>
                        <td> {{ $campo->nombre_campo }} </td>
                        <td> {{ ($campo->obligatorio) ? 'SI' : 'NO' }} </td>
                        <td>
                            <button class="editar btn" alt="Editar configuración kit baja" title="Editar configuración kit baja" data-id="{{ $campo->id }}" data-nombre_campo="{{ $campo->nombre_campo }}" data-alias="{{ strtoupper($campo->alias) }}" data-obligatorio="{{ $campo->obligatorio }}" data-toggle="modal" data-target="#controlExpedienteModal"> <img src="/img/icono-editar.png" class="button-style-icon"></button>

                            <button class="borrar btn" alt="Eliminar configuración kit baja" data-id="{{ $campo->id }}" title="Eliminar configuración kit baja"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('includes.footer')
        @include('parametria.configuracion-kitBaja.configuracion-kitBajaModal')


        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            let dataSrc = [];
            let table = $('#tabla_configuracionKit').DataTable({
                scrollY: '65vh',
                scrollCollapse: true,
                "language": {
                    search: '',
                    searchPlaceholder: 'Buscar registros',
                    "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                },
                initComplete: function() {

                    let api = this.api();

                    api.cells('tr', [0]).every(function() {

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
                }
            });
            $(function() {
                $(".borrar").click(function() {
                    var id = $(this).data('id');
                    btn = $(this);
                    if (id != '') {
                        swal({
                                title: "",
                                text: "¿Esta seguro de eliminar este registro?",
                                icon: "warning",
                                buttons: true,
                                dangerMode: true,

                            })
                            .then((willDelete) => {
                                if (willDelete) {
                                    borrarCampo(id);
                                }
                            });
                    } else {
                        swal("Ocurrió un error, intentar nuevamente.", {
                            icon: "error",
                        });
                    }
                });

                function borrarCampo(id) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    data = {
                        'id': id,
                        '_token': CSRF_TOKEN
                    }

                    var url = "{{ route('kitbaja.borrar') }}";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: 'JSON',
                        success: function(response) {
                            if (response.ok == 1) {
                                $(".campos tr#" + id).remove();
                                swal("El registro se elimino correctamente!", {
                                    icon: "success",
                                });
                            } else {
                                swal("Ocurrió un error, intentar nuevamente.", {
                                    icon: "error",
                                });
                            }
                        }
                    });
                }
            });
        </script>