<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

    @include('includes.header',['title'=>'Tipos de prestaciones',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-prestaciones.png',
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
                <button type="button" class="button-style" data-toggle="modal" data-target="#prestacionesModal" data-nombre="" data-id="">
                    <img src="/img/icono-crear.png" class="button-style-icon">
                    Crear
                </button>

            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>

        </div>
        <br>
        <div class="article border">
            <table id="tbl" class="table">
                <thead>
                    <tr>
                        <th scope="col" class="gone">ID</th>
                        <th scope="col" class="text-center">Tipo de prestación</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prestaciones as $prestacion)
                    <tr id="{{$prestacion->id}}">
                        <td>{{$prestacion->id}}</td>
                        <td class="text-center nombre">{{$prestacion->nombre}}</td>
                        <td class="text-center">
                            @php
                            $id = Crypt::encrypt($prestacion->id);
                            $id_clase = Crypt::encrypt( $prestacion->tipo_clase);
                            $parametros = array('id'=>$id , 'nombre'=>$prestacion->nombre, 'tipo_clase'=>$id_clase);
                            @endphp

                            <button data-id="{{$prestacion->id}}" class="editar btn btn-sm mr-2" alt="Editar prestacion" title="Editar prestacion" data-toggle="modal" data-nombre="{{$prestacion->nombre}}" data-tipo_clase="{{$prestacion->tipo_clase}}" data-target="#prestacionesModal"><img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <button data-id="{{$id}}" class="borrar btn btn-sm" alt="Eliminar prestacion" title="Eliminar prestacion"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                            <a href="{{route('parametria.prestaciones.listado',['id'=>$id])}}"><button class="button-style-custom">Prestaciones</button></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('includes.footer')
    @include('parametria.prestaciones.modal.editar-modal')

    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tbl').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por tipo de prestación',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [1]).every(function(){

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


        $(function() {

            $(".btn.borrar").click(function() {
                var id = $(this).data('id');
                swal({
                    title: `¿Está seguro de eliminar este tipo de  prestación?`,
                    text: "Al realizar la acción ya no podrá recuperarla !",
                    icon: "warning",
                    buttons: ["Cancelar", true],
                    dangerMode: true,

                }).then((willDelete) => {
                    if (willDelete) {
                        eliminarDatos(id).then(data => {

                            if (data.respuesta) {
                                swal("Datos actualizados  correctamente!", {
                                    icon: "success",
                                });

                                location.reload();
                            } else {

                                swal("Error al desactivar los datos comunicate con tu adminstrador!", {
                                    icon: "error",
                                });
                            }
                        });
                    }
                });
            });

        });

        const eliminarDatos = async id => {

            let url = "{{route('parametria.prestaciones.borrar')}}";

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'id': id
                })
            });

            const res = await response.json();
            return res;
        }
    </script>
</body>

</html>
