<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Tipos de beneficios',
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
            <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#modalAgregarTipo" > <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>

            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3 mb-3" id="div_buscar"></div>
        </div>

        <div class="article border">
            <table class="table w-100 tipos-prestamo" id="tabla_tipos-prestamo">
                <thead style="text-align: center;">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Antigüedad requerida</th>
                        <th>Estatus</th>
                        <th>Requisitos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                    @foreach($prestamos as $prestamo)
                    <tr>
                        <td>{{ $prestamo->id }}</td>
                        <td>{{ $prestamo->nombre }}</td>
                        <td>{{ $prestamo->antiguedad_meses }} meses</td>
                        <td>
                            @if( $prestamo->estatus == 1)
                            Activo
                            @else
                            Inactivo
                            @endif
                        </td>
                        <td>
                            @php
                            $i = 0;
                            foreach($requisitos as $requisito){
                            if($requisito->prestamos_tipos_id == $prestamo->id){
                            $i++;
                            }
                            }
                            @endphp
                            {{$i}}
                        </td>
                        <td>
                            <a href="#" data-id="{{$prestamo->id}}" class="borrar btn btn-sm mr-2" alt="Borrar" title="Borrar"> <img src="/img/icono-eliminar.png" class="button-style-icon"></a>

                            <a href="{{route('tiposPrestamo.edita',['id'=>$prestamo->id])}}" class="revisar btn  btn-sm mr-2" alt="Revisar" title="Revisar"><img src="/img/icono-editar.png" class="button-style-icon"></a>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('includes.footer')
        @include('herramientas.prestamos.tipos-prestamos.tiposPrestamosCrear-modal')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>

        <script>
            let dataSrc = [];
            let table = $('#tabla_tipos-prestamo').DataTable({
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

            table.order([0, 'asc']).draw();
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
                            borrarTipo(id);
                        }
                    });
            }
       
            function borrarTipo(id) {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'id': id,
                    '_token': CSRF_TOKEN
                }

                $.ajax({
                    url: `{{ route('tiposPrestamo.elimina') }}`,
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
                        }else {
                            swal("", "Ha ocurrido un error, por favor intentalo más tarde.", "error");

                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        swal("", "Ocurrió un error al eliminar el registro!", "error");

                    }
                });

            }
        </script>