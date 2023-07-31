<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Documentos de empleados',
        'subtitle'=>'Consultas', 'img'=>'img/header/administracion/icono-emisora.png',
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
            <a type="button" href="{{ route('doc-empleados.exporta') }}" class="button-style ml-3 mb-3">
                <img src="/img/icono-exportar.png" class="button-style-icon">Exportar</a>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100 doc-empleados" id="tabla_doc-empleados">
                <thead style="text-align: center;">
                    <tr>
                        <th>ID</th>
                        <th width="200px">Nombre</th>
                        <th>INE</th>
                        <th>CURP</th>
                        <th>NSS</th>
                        <th>Acta nacimiento</th>
                        <th>Comprobante de domicilio</th>
                        <th>Contrato</th>
                        <th>RFC</th>
                        <th>Fotografía</th>
                        <th>Fonacot</th>
                        <th>Curriculum</th>
                        <th>Estado de cuenta</th>
                        <th>Fiel IMSS</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach ($empleados as $empleado)
                    <tr>
                        <td>{{ $empleado['id'] }}</td>
                        <td width="200px">{{ $empleado['nombre'] }}</td>
                        <td>
                            @if( $empleado['ine']['existe'])
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['curp']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['nss']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['nacimiento']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['comprobante']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>

                        <td class="text-center">
                            @if ( $empleado['contrato']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['rfc']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['foto']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>

                        <td class="text-center">
                            @if ( $empleado['fonacot']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['curriculum']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>

                        <td class="text-center">
                            @if ( $empleado['estado_cuenta']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                        <td class="text-center">
                            @if ( $empleado['fiel_imss']['existe'] )
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-20" title="Kit de baja completo">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-20" title="Kit de baja incompleto">
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('includes.footer')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            let dataSrc = [];
            let table = $('#tabla_doc-empleados').DataTable({
                scrollY: '70vh',
                scrollX: '15vh',
                scrollCollapse: true,
                lengthChange: true,
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