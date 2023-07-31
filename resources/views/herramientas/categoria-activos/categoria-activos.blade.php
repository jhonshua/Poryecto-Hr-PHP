<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Categoria de activos',
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
            <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#categoriasModal" data-id=""> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100 categoria-act" id="tabla_categoria-act">
                <thead style="text-align: center;">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @if(count ($resultados) > 0)
                    @foreach ( $resultados as $resultado )
                    <tr>
                        <td>{{ $resultado->id }}</td>

                        <td>{{ $resultado->nombre_activo }}</td>

                        @if( $resultado->estatus == "1" )
                        <td>
                            <span class="estatus font-weight-bold text-success pull-right">Activo</span>
                        </td>
                        @else
                        <td>
                            <span class="estatus font-weight-bold text-danger pull-right">Inactivo</span>
                        </td>
                        @endif

                        <td>
                            <button class="editar btn" alt="Editar categoria de activos" title="Editar categoria" data-toggle="modal" data-target="#categoriasModal" data-id="{{$resultado->id}}" data-nombre_activo="{{$resultado->nombre_activo}}" data-estatus="{{$resultado->estatus}}"> <img src="/img/icono-editar.png" class="button-style-icon"></button>

                            <button class="borrar btn" data-id="{{$resultado->id}}" alt="Eliminar categoria de activos" title="Eliminar categoria"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                        </td>

                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @include('includes.footer')
        @include('herramientas.categoria-activos.crearEditarCategoria-modal')


        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>

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
                        validarBorrado(id);
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
                            borrarCategoria(id);
                        }
                    });
            }

            function borrarCategoria(id) {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id': id,
                    '_token': CSRF_TOKEN
                }

                $.ajax({
                    url: `{{ route('categoriaActivo.elimina') }}`,
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
        </script>