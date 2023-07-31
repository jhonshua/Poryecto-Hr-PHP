<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php
    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Puestos de la empresa',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-puestos.png',
        'route'=>'bandeja'])

        @if(\Session::get('mensaje'))
        <div class="alert alert-info" role="alert" id="alerta">
            {!! \Session::get('mensaje') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(() => $('#alerta').fadeOut(), 10000);
        </script>
        @endif

        <div class="row d-flex justify-content-between">
            <div class="row d-flex justify-content-start col-sm-12 col-xs-12 col-md-12 col-lg-9">
                <div class="">
                    <!-- <a type="button" class="button-style ml-3 mb-3" href="{{route('parametria.puestos.crear.editar')}}">Crear nuevo</a>        -->
                    <button type="button" class="button-style ml-3 mb-3" data-toggle="modal" data-id="" data-target="#puestosModal"> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
                    @if($lleva_puestos_reales===1 )
                    <a href="{{route('puestos.reales.inicio')}}">
                        <button type="button" class="button-style ml-3 mb-3" data-toggle="tooltip" title="Anexar puestos reales"> <img src="/img/icono-crear.png" class="button-style-icon">Crear puesto reales</button>
                    </a>
                    @endif
                </div>
            </div>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>

        </div>

        <div class="article border">
            <table class="table w-100 text-center" id="tabla_puestos">
                <thead class="text-center">
                    <tr>
                        <th>ID</th>
                        <th style="width: 20%">Puesto</th>

                        <th>Dependencia</th>
                        <th>Jerarquía</th>
                        <th style="width: 2%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach ($puestos as $puesto)
                    <tr id="{{$puesto->id}}">
                        <td width="50px">{{$puesto->id}}</td>
                        <td width="30%">{{$puesto->puesto}}</td>
                        <td width="37%" class="text-center">
                            {{@$puestos[$puesto->dependencia]->puesto}}
                        </td>
                        <td width="150px" class="text-center">{{$puesto->jerarquia}}</td>
                        <td width="210px">
                            <button data-id="{{$puesto->id}}" class="editar btn" alt="Editar Puesto" title="Editar Puesto" data-toggle="modal" data-target="#puestosModal" data-id="{{$puesto->id}}" data-puesto="{{$puesto->puesto}}" data-jerarquia="{{$puesto->jerarquia}}" data-dependencia="{{$puesto->dependencia}}" data-nombre-dependencia="{{@$puestos[$puesto->dependencia]->puesto}}" data-actividades="{{$puesto->actividades}}" data-rama="{{!empty($puesto->rama)?$puesto->rama :null }}">
                                <img src="/img/icono-editar.png" class="button-style-icon">
                            </button>


                            <button data-id="{{$puesto['id']}}" class="borrar btn" onclick="validarBorrado({{$puesto['id']}});" alt="Eliminar Puesto" title="Eliminar Puesto"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>

                            <a href="{{route('puestos.perfilDescriptivo',$puesto->id)}}">
                                <img src="/img/ver-documentos-empleado.png" alt="Ver perfil de puesto" title="Ver perfil de puesto" class="button-style-icon">
                            </a>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('parametria.puestos.crear-editar-modal')

    </div>
    @include('includes.footer')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tabla_puestos').DataTable({
            scrollY: '65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por puesto',
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
                    text: "¿Esta seguro de eliminar este puesto?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        borrarPuesto(id);
                    }
                });
        }

        function borrarPuesto(id) {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                'id': id,
                '_token': CSRF_TOKEN
            }
            $.ajax({
                url: `{{route('parametria.puestos.borrar')}}`,
                type: 'POST',
                data: data,
                dataType: 'JSON',
                beforeSend: function() {},
                success: function(response) {
                    if (response.ok == 1) {
                        swal("El puesto se eliminó correctamente.", {
                            icon: "success",
                        });
                        setTimeout('location.reload()', 500);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("", "Ocurrió un error al eliminar el registro!", "error");
                    // console.log(errorThrown);
                }
            });
        }
    </script>