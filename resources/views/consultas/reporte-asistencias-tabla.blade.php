<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php
    $array_departamentos = [];
    foreach($departamentos as $departamento){
    if(!in_array($departamento->nombre, $array_departamentos)){
    $array_departamentos[] = $departamento->nombre;
    }
    }
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Reporte de asistencias',
        'subtitle'=>'Consultas', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'consultas.reporte-asistencias'])

        <br>
        <div>
            <div class="row">
                <div class="col-md-8">
                    <button type="button" class="button-style mb-3 mr-3 sync tooltip_" data-toggle="tooltip" data-id-empresa="{{ Crypt::encrypt(Session::get('empresa')['id'] )}}" data-dia="{{$dia}}" title="Sincronizar registros de biométricos">
                        <img src="/img/icono-sincronizacion.png" class="button-style-icon">
                    </button>
                    <button class="exportar button-style mb-3 mr-3" alt="Exportar asistencias" title="Exportar asistencias" data-toggle="modal" data-target="#reporteAsistenciasModal">
                        <img src="/img/icono-exportar.png" class="button-style-icon">Exportar
                    </button>
                </div>
                <div class="col-md-4">
                    <select name="" id="filtro" class="select-clase" style="width: 170px;">
                        <option value="">Todos</option>
                        @foreach ($array_departamentos as $key => $t)
                        <option value="{{$t}}">{{$t}}</option>
                        @endforeach
                    </select>
                    <div class="dataTables_filter" id="div_buscar" style="width: 190px"></div>
                </div>
            </div>
        </div>
        <br>
        <h4 class="bg-white p-3">Asistencias del día: {{ dia(date('N', strtotime($fecha))).' '.formatoAFecha($fecha)}}</h4>
        <br>

        <div class="article border">
            <table class="table w-100 sistencias" id="tabla_reporteAsistencias">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center"># Emp.</th>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">Departamento</th>
                        <th class="text-center">Entrada</th>
                        <th class="text-center">Salida</th>
                        <th class="text-center">Asistencia</th>
                        <th class="text-center">Retardo</th>
                        <th class="text-center">Permiso</th>
                        <th class="text-center" style="width: 20%;">Home office</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empleados as $empleado)
                    @if ($empleado->fecha_alta <= $fecha) <tr id="{{$empleado->id}}" data-id="{{$empleado->id}}" data-dia="{{$empleado->dia}}" data-id_departamento="{{$empleado->id_departamento}}" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}">
                        <td class="text-center" width="60px">{{ $empleado->id }}</td>
                        <td class="text-center" width="80px">{{ $empleado->numero_empleado }}</td>
                        <td class="text-center" width="25%">{{ $empleado->nombre_completo }}</td>
                        <td class="text-center" width="">{{ $departamentos[$empleado->id_departamento]->nombre }}</td>
                        <td class="text-center" width="150px">
                            {{ (isset($empleado->asistencia->entrada)) ? date('g:i a', strtotime($empleado->asistencia->entrada)) :'N/A' }}
                        </td>
                        <td class="text-center" width="150px">
                            {{ (isset($empleado->asistencia->salida)) ? date('g:i a', strtotime($empleado->asistencia->salida)) : 'N/A' }}
                        </td>
                        <td class="text-center" width="90px">
                            @if (isset($empleado->asistencia) && $empleado->asistencia->asistencia == 1)
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25" title="Asistio">
                            @else
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25" title="No asistió">
                            @endif
                        </td>
                        <td class="text-center" width="90px">
                            @if (isset($empleado->asistencia) && $empleado->asistencia->retardo == 1)
                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25" title="Con retardo - {{$empleado->asistencia->motivo}}">
                            @else
                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25" title="A tiempo">
                            @endif
                        </td>
                        <td class="text-center" width="90px">
                            @if (isset($empleado->asistencia) && $empleado->asistencia->retardo == 1)
                                @if (isset($empleado->asistencia) && $empleado->asistencia->permiso == 1)
                                    <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25" title="Autorizó: {{$empleado->asistencia->autorizo}}">
                                @else
                                    <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25" title="Sin autorización">
                                @endif

                            @else
                                <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25" title="Sin autorización">
                            @endif
                        </td>
                        <td class="text-center" width="90px">
                        @if (isset($asistencias[$empleado->id]) && $asistencias[$empleado->id]->lugar)
                                            @if( $asistencias[$empleado->id]->lugar ==='APP' )
                                                <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25">
                                            @else
                                                <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25">
                                            @endif
                                        
                                        @else
                                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25">
                                        @endif
                        </td>
                        </tr>

                        @endif
                        @endforeach
                </tbody>
            </table>
        </div>
        @include('consultas.reporte-asistencias-modal', array('dia'=>$dia))
    </div>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script src="{{ asset('js/helper.js') }}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_reporteAsistencias').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [2]).every(function(){

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
        });


        table.order([2, 'asc']).draw();
        $('#filtro').on('change', function() {
            table
                .columns(3)
                .search(this.value)
                .draw();
        });
        $(function() {

            // Sincronizar
            $(".sync").click(function() {

                let idempresa = $(this).data('id-empresa');
                let dia = $(this).data('dia');

                swal({

                    title: `¿ Está seguro de sincronizar el biométrico ?`,
                    text: "Preparando la ejecución !",
                    icon: "warning",
                    buttons: ["Cancelar", true],
                    dangerMode: true,

                }).then((willDelete) => {
                    if (willDelete) {
                        syncBiometrico(idempresa, dia).then(data => {
                            const {
                                respuesta
                            } = data;

                            if (respuesta == 1) {

                                swal("Sincronización completada correctamente!", {
                                    icon: "success",
                                });

                                location.reload();
                            } else if (respuesta == 2) {

                                swal("La sincronización no se pudo completar contacta a tu administrador!", {
                                    icon: "warning",
                                });

                            } else {
                                swal("Error  en sincronización comunicate con tu administrador  !", {
                                    icon: "error",
                                });
                            }
                        });
                    }
                });
            });
        });
        const syncBiometrico = async (idempresa, dia) => {

            let url = "{{route('empleado.asistencias.registroAsistenciasCron')}}";

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'idempresa': idempresa,
                    'dia': dia
                })
            });

            const res = await response.json();
            return res;
        }

        $(function() {
            $('.select-clase').select2();
        });

    </script>