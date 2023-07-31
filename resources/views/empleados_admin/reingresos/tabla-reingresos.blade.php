<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Reingresos',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png',
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

        <div>
            <div class="row">
                <div class="col-lg-6">
                    <!--  <a href="#" class="button-style mb-3 mr-3" target="_blank">
                        Fecha nueva alta</a> -->

                    <input type="hidden" name="" id="fecha_reingreso_masivo" class="datepicker button-style mb-3 mr-3" placeholder="Fecha nueva alta" disabled>

                    <button class="button-style mb-3 mr-3 masivo disabled" alt="Reingreso masivo" title="Reingreso masivo">
                        Reingreso masivo
                    </button>

                </div>
                <div class="col-lg-6">

                    <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
                </div>
            </div>
        </div>

        <div class="article border">
            <form action="{{route('reingresos.masivo')}}" id="reingresos-masivos" method="POST">
                @csrf
                <table class="table w-200 reingresoTabla" id="tablaReingreso">
                    <thead class="text-center">
                        <th width="35px">
                            <input type="checkbox" id="selCheckboxes">
                        </th>
                        <th style="width: 40px;">ID</th>
                        <th># empleado</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Fecha alta</th>
                        <th>Fecha baja</th>
                        <th>Acciones</th>
                    </thead>

                    <tbody>
                        <input type="hidden" name="fecha_nueva_alta" id="fecha_nueva_alta">
                        @foreach($empleados as $empleado)
                        <tr id="{{$empleado->id}}" data-id="{{$empleado->id}}" data-id_departamento="{{$empleado->id_departamento}}" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}">
                            <td width="30px">
                                <input type="checkbox" name="reingreso[]" value="{{$empleado->id}}" id="reingreso" ref="reingresoSelected">
                            </td>

                            <td class="text-center">{{$empleado->id}}</td>
                            <td class="text-center">{{$empleado->numero_empleado}}</td>
                            <td class="text-center" data-toggle="tooltip" data-placement="top" data-html="true" title="Motivo de baja: {{$empleado->causa_baja}} - {{$empleado->baja_oficial}}" class="toolTip">
                                {{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}
                            </td>
                            <td class="text-center">
                                @foreach ($departamentos as $departamento)
                                @if($departamento->id == $empleado->id_departamento)
                                {{ $departamento->nombre }}
                                @endif
                                @endforeach
                            </td>
                            <td class="text-center">{{formatoAFecha($empleado->fecha_alta)}}</td>
                            <td class="text-center">{{formatoAFecha($empleado->fecha_baja)}}</td>
                            <td class="text-center">
                                <a href="{{ route('reingresos.info', $empleado->id) }}" class="btn btn-sm mr-2" alt="Ver expediente" title="Ver expediente" target="_blank">
                                    <img src="{{asset('img/ver-documentos-empleado.png')}}" class="button-style-icon text-center"></a>
                                </a>
                                <a href="#" data-id="{{$empleado->id}}" class="guardar button-style mb-3 mr-3" alt="Reingresar empleado" title="Reingresar empleado" data-toggle="modal" data-target="#reingresoModal" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}" data-motivo="{{$empleado->causa_baja}} - {{$empleado->baja_oficial}}" data-id="{{$empleado->id}}" data-fecha_baja="{{$empleado->fecha_baja}}">Reingresar</a>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

            @include('includes.footer')
        @include('empleados_admin.reingresos.modal-reingreso')

        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script src="{{asset('js/typeahead.js')}}"></script>

        <script>
            let dataSrc = [];
            let table = $('#tablaReingreso').DataTable({
                scrollY: '65vh',
                scrollCollapse: true,
                "language": {
                    search: '',
                    searchPlaceholder: 'Buscar registros',
                    "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                },
                initComplete: function() {

                    let api = this.api();

                    api.cells('tr', [1]).every(function() {

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
            });
            $(function() {

                $('.toolTip').tooltip();

                $('#selCheckboxes').click(function() {
                    $('.reingresoTabla input:checkbox').prop('checked', $(this).is(':checked'));

                });

                $('#fecha_reingreso_masivo').change(function() {
                    if ($(this).val != '') {
                        $('.masivo').removeClass('disabled');
                    } else {
                        $('.masivo').addClass('disabled');
                    }
                });

                $('.masivo').click(function() {
                  
                    $('#fecha_nueva_alta').val($('#fecha_reingreso_masivo').val());
                    $(this).attr('disabled', true).text('Espere...');
                    $('#reingresos-masivos').submit();

                });
            });
        </script>