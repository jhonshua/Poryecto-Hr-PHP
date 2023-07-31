<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Solicitudes de beneficiarios',
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'prestamos.tabla'])

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
            <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#crearSolicitudModal" > <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
            <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#exportarPrestamoModal" data-id=""> <img src="/img/icono-exportar.png" class="button-style-icon">Exportar</button>
            <a type="button" class="button-style ml-3 mb-3 nuevo" href="{{ route('tiposPrestamos.tabla') }}"> Tipos de beneficios</a>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100" id="prestamos-table">
                <thead style="text-align: center;">
                    <th>ID</th>
                    <th width="180">Tipo de préstamo</th>
                    <th width="180">Empleado</th>
                    <th>Empresa</th>
                    <th width="90">Medio de contacto</th>
                    <th>Estatus</th>
                    <th>Fecha creación</th>
                    <th>Fecha cierre</th>
                    <th width="160px">Acciones</th>
                </thead>
                <tbody style="text-align: center;">
                    @foreach($prestamos as $prestamo)
                    <tr id="{{$prestamo->id}}" data-id="{{$prestamo->id}}" data-estatus="{{$prestamo->estatus}}" data-empleado="{{strtolower($prestamo->empleado)}}" data-empresa_id="{{$prestamo->empresa_id}}">
                        <td>{{ $prestamo->id }}</td>
                        <td>
                            @if(isset( $prestamos_tipos[$prestamo->prestamos_tipo_id] ))
                            {{ $prestamos_tipos[$prestamo->prestamos_tipo_id]->nombre }}
                            @else
                            <strong class="text-danger">No hay dato</strong>
                            @endif
                        </td>
                        <td>{{ $prestamo->empleado }}</td>
                        <td>
                            @if (isset($empresas[$prestamo->empresa_id]))
                            {{$empresas[$prestamo->empresa_id]->razon_social}}
                            @else
                            <strong class="text-danger">No hay dato</strong>
                            @endif
                        </td>
                        <td>{{ $prestamo->medio_contacto }}</td>
                        <td>
                            @if($prestamo->estatus == App\Models\Prestamo::PRESTAMO_CERRADO)
                            <span class='text-secondary font-weight-bold'>Cerrado</span>
                            @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_ABIERTO && $prestamo->usuario_id == 0)
                            <span class='text-danger font-weight-bold unassigned'>Sin asignar</span>
                            @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_ABIERTO)
                            <span class='text-success font-weight-bold'>Abierto</span>
                            @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_RECHAZADO)
                            <span class='text-danger font-weight-bold'>Rechazado</span>
                            @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_PARA_REVISION)
                            <span class='text-warning font-weight-bold'>Para revisión</span>
                            @endif
                        </td>
                        <td>{{ formatoAFecha($prestamo->fecha_creacion) }}</td>
                        <td>{{ formatoAFecha($prestamo->fecha_cierre) }}</td>
                        <td>
                            <a href="#" data-id="{{$prestamo->id}}" class="borrar btn btn-sm mr-2" alt="Borrar" title="Borrar"> <img src="/img/icono-eliminar.png" class="button-style-icon"></a>
                            {{-- Prestamo sin asignar --}}
                            @if($prestamo->usuario_id == 0)
                            <a href="#" data-id="{{$prestamo->id}}" data-tipoPrestamo="{{$prestamos_tipos[$prestamo->prestamos_tipo_id]->nombre }}" class="asignar btn btn-sm mr-2" alt="Asignarmelo a mi" title="Asignarmelo a mi"><img src="/img/icono-permisos.png" class="button-style-icon"></a>

                            {{-- Si esta abierto (en espera a que suban los requisitos) --}}
                            @elseif($prestamo->estatus == App\Models\Prestamo::PRESTAMO_ABIERTO)
                            {{-- <a href="#" data-id={{$prestamo->id}}" class="editar btn btn-sm mr-2"><img src="/img/icono-editar.png" class="button-style-icon"></a> --}}
                            @endif

                            {{-- Listo para revision de documentos --}}
                            @if($prestamo->usuario_id != 0)
                            <a href="{{route('prestamos.revisar', $prestamo->id)}}" data-id="{{$prestamo->id}}" class="revisar btn  btn-sm mr-2" alt="Revisar" title="Revisar"><img src="/img/icono-editar.png" class="button-style-icon"></a>
                            @endif
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('includes.footer')
        @include('herramientas.prestamos.modals.prestamosCrear-modal')
        @include('herramientas.prestamos.modals.prestamo-exportar')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script>
            let dataSrc = [];
            let table = $('#prestamos-table').DataTable({
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
                },
                "drawCallback": function(settings) {
                    $(".btn.borrar").click(function() {
                        let id = $(this).data('id');
                        validarBorrado(id);
                    });
                },
            });

            function validarBorrado(id) {

                swal({
                        title: "",
                        text: "¿Esta seguro de eliminar este registro?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            borrarPrestamo(id);
                        }
                    });
            }

            function borrarPrestamo(id) {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id': id,
                    '_token': CSRF_TOKEN
                }

                $.ajax({
                    url: `{{ route('prestamos.elimina') }}`,
                    type: 'POST',
                    data: data,
                    dataType: 'JSON',
                    beforeSend: function() {},
                    success: function(response) {
                        if (response.ok == 1) {
                            swal("El registro se eliminó correctamente.", {
                                icon: "success",
                            });
                            setTimeout('location.reload()', 500);
                        }

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        swal("", "Ocurrió un error al eliminar el registro!", "error");

                    }
                });
            }
            // Asignar prestamo
            $(".btn.asignar").click(function() {

                var id = $(this).data('id');
                var tipoPrestamo = $(this).data('tipoprestamo');

                validarAsignacion(id, tipoPrestamo);
            });

            function validarAsignacion(id, tipoPrestamo) {
                swal({
                        title: "",
                        text: "¿Esta seguro de asignarse este registro?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            asignarPrestamo(id, tipoPrestamo);
                        }
                    });
            }

            function asignarPrestamo(id, tipoPrestamo) {

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id': id,
                    'tipoPrestamo': tipoPrestamo,
                    '_token': CSRF_TOKEN
                }

                
                var url = "{{route('prestamos.asignaEjecutivo', '*ID*')}}";
                url = url.replace('*ID*', id);
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.ok == 1) {
                            $(".table-prestamos tr#" + id + " td .asignar").remove();
                            $(".table-prestamos tr#" + id + " td .unassigned").text('Asignado');
                            swal("El prestamo fue asignado correctamente y fue enviado un mail al empleado con los pasos a seguir.", {
                                icon: "success",
                            });
                            setTimeout('location.reload()', 500);
                        } else if (response.ok == 2) {
                            swal("", "Se asignó correctamente la solicitud de prestamo, pero NO pudo ser enviado el email de seguimiento al empleado.", "error");

                        } else {
                            swal("", "Ocurrió un error al eliminar el registro!", "error");
                        }
                    }
                });
            }
        </script>