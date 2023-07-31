<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Asignar activos',
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
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
            <a type="button" href="{{ route('asignaActivos.creaMod') }}" class="button-style ml-3 mb-3 nuevo"> <img src="/img/icono-crear.png" class="button-style-icon">Crear</a>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100 categoria-act" id="tabla_categoria-act">
                <thead style="text-align: center;">
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th width="40%">Descripción</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>No. serie</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach ($resultados as $activo)
                    <tr>
                        <td>{{ $activo->id }}</td>
                        <td>{{ $activo->nombre }}</td>
                        <td width="40%">{{ $activo->descripcion }}</td>
                        <td>{{ $activo->marca }}</td>
                        <td>{{ $activo->modelo }}</td>
                        <td>{{ $activo->nserie }}</td>
                        @if ( $activo->estatus == '1')
                        <td>
                            <a class="editar btn" href="{{ route('asignaActivos.creaMod',['id' => $activo->id]) }}" alt="Editar activo" title="Editar activo"> <img src="/img/icono-editar.png" class="button-style-icon"></a>

                            <button class="borrar btn" data-id="{{$activo->id}}" data-estatus="{{$activo->estatus}}" alt="Eliminar activo" title="Eliminar activo"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button>

                            <a class="editar btn" href="{{ route('asignaActivos.asignaEmpleado',['id' => $activo->id]) }}" alt="Asignar activos a empleados" title="Editar categoria"> <img src="/img/icono-empleados.png" class="button-style-icon"></a>
                            
                        </td>
                        @else
                        <td>
                            <button class="borrar btn" data-id="{{$activo->id}}" data-estatus="{{$activo->estatus}}" data-tipo="1" alt="Habilitar activo" title="Habilitar activo"> <img src="/img/ver-documentos-empleado.png" class="button-style-icon"></button>

                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('includes.footer')

        <script>
            let dataSrc = [];
            let table = $('#tabla_categoria-act').DataTable({
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
                        let estatus = $(this).data('estatus');
                        let tipo = $(this).data('tipo');
                        if (tipo != null) {
                            validarHabilitado(id, estatus);
                        } else {
                            validarBorrado(id, estatus);
                        }

                    });

                },
            });

            table.order([0, 'desc']).draw();
            $('#filtro').on('change', function() {
                table
                    .columns(0)
                    .search(this.value)
                    .draw();
            });

            function validarBorrado(id, estatus) {

                swal({
                        title: "",
                        text: "¿Esta seguro de eliminar/deshabilitar este registro?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            borrarActivo(id, estatus);
                        }
                    });
            }

            function validarHabilitado(id, estatus) {

                swal({
                        title: "",
                        text: "¿Esta seguro de habilitar este registro?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            borrarActivo(id, estatus);
                        }
                    });
            }

            function borrarActivo(id, estatus) {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id': id,
                    'estatus': estatus,
                    '_token': CSRF_TOKEN
                }

                $.ajax({
                    url: `{{ route('asignaActivos.elimina') }}`,
                    type: 'POST',
                    data: data,
                    dataType: 'JSON',
                    beforeSend: function() {},
                    success: function(response) {
                        if (response.ok == 1) {
                            swal("El registro se actualizo correctamente.", {
                                icon: "success",
                            });
                            setTimeout('location.reload()', 500);
                        }
                        if (response.ok == 2) {
                            swal("", "No se puede borrar el activo por que ha sido asignado a un empleado.", "error");

                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        swal("", "Ocurrió un error al eliminar el registro!", "error");

                    }
                });

            }
        </script>