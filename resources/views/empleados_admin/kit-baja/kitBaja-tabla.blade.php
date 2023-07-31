<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Kit de baja',
        'subtitle'=>'Empleados', 'img'=>'img/header/administracion/icono-emisora.png',
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


        <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>

        <div class="article border">
            <table class="table w-100 kit_baja" id="tabla_kit_baja">
                <thead style="text-align: center;">
                    <tr>
                        <th>Id</th>
                        <th># empleado</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Fecha de ingreso</th>
                        <th>Puesto</th>
                        <th>Estatus</th>
                        <th width="190px">Opciones</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach($empleados as $empleado)
                    <tr id="{{$empleado->id}}" data-numempleado="{{$empleado->numero_empleado}}" data-nombreempleado="{{$empleado->nombre_completo}}" data-departamento="{{$empleado->id_departamento}}">
                        <td>{{ $empleado->id }}</td>
                        <td>{{ $empleado->numero_empleado }}</td>
                        <td>{{ $empleado->nombre_completo }}</td>
                        <td>{{ $empleado->departamento->nombre }}</td>
                        <td>{{ formatoAFecha($empleado->fecha_antiguedad) }}</td>
                        <td>{{ (optional($empleado->puesto)->puesto)?: '' }}</td>
                        <td>
                            @if ($empleado->kitBajaCompleto)
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td width="190px" class="text-center position-relative">
                            @php
                            $return = 'kit_baja';
                            @endphp
                            <button class="button-style btn-block" data-toggle="modal" data-target="#archivosModal" data-id_empleado="{{$empleado->id}}" data-return_id="{{$return}}" @if ($empleado->kitBaja != null)
                                @foreach ($empleado->kitBaja as $campo)
                                dd($campo);
                                data-file_{{$campo->nombre_campo}}="{{$campo->archivo}}"
                                @endforeach
                                @endif >
                                {{($empleado->completo) ? 'Ver' : 'Subir'}} archivos
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('includes.footer')
        @include('empleados_admin.kit-baja.archivos-modal')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            let dataSrc = [];
            let table = $('#tabla_kit_baja').DataTable({
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
        </script>