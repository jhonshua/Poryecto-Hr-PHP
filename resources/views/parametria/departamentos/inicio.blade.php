<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

    @include('includes.header',['title'=>'Departamentos de la empresa',
        'subtitle'=>'Parametría inicial', 'img'=>'img/header/parametria/icono-departamentos.png',
        'route'=>'bandeja'])
     
        <div>
            <a href="#" ref="Agregar departamento">
                <button type="button" class="button-style nuevo" id="add_dep">
                    <img src="/img/icono-crear.png" class="button-style-icon">
                    Crear
                </button>
            </a>
            <div class="dataTables_filter col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>

        </div>
        <br>


        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @elseif(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif
        <div class="article border">
            <table id="tbl" class="table col-md-12 deptos">
                <thead>
                    <tr>
                        <th scope="col" class="gone">ID</th>
                        <th scope="col" class="">Nombre</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($deptos as $depto)
                    @php $id = Crypt::encrypt($depto->id) @endphp
                    <tr id="{{$id}}">
                        <td>{{$depto->id}}</td>
                        <td class="nombre">{{$depto->nombre}}</td>
                        <td class="text-center">
                            <button data-id="{{$id}}" class="editar edit_dep btn" alt="Editar Departamento" title="Editar Departamento"><img src="/img/icono-editar.png" class="button-style-icon"></button>
                            <button data-id="{{$id}}" class="borrar_dep btn" alt="Eliminar Departamento" title="Eliminar Departamento"><img src="/img/icono-eliminar.png" class="button-style-icon"></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('includes.footer')
    @include('parametria.departamentos.modals.departamento-modal')
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
    <script src="{{asset('js/typeahead.js')}}"></script>
    <script>
        let dataSrc = [];
        let table = $('#tbl').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
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

            $('.guardar').click(function() {

                let form = $("#form");
                if (form.parsley().isValid()) {
                    $(this).text('Espere...');
                    $(this).prop('disabled', true);
                    form.submit();
                } else {
                    form.parsley().validate();
                }
            });

        });

        $(".borrar_dep").click(function() {
            var id = $(this).data('id');
            swal({
                title: `¿Está seguro de eliminar este departamento ?`,
                text: "Al realizar la acción ya no podrá recuperarla !",
                icon: "warning",
                buttons: ["Cancelar", true],
                dangerMode: true,

            }).then((willDelete) => {
                if (willDelete) {

                    eliminarDatos(id).then(data => {

                        if (data.respuesta) {
                            swal("Dato eliminado correctamente!", {
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

        const eliminarDatos = async id => {

            let url = "{{route('parametria.departamentos.borrar')}}";

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


        $(".edit_dep").click(function(){
            $("#agregar-departamento").modal('show');
            let id = $(this).data('id');
            if (id !== undefined) {
                let nombreActual = $('.deptos tr#' + id + ' .nombre').text();
                $("#nombre").val(nombreActual);
                $("#id").val(id);
            }
            document.getElementById('nombre_accion').innerHTML= 'Editar departamento';
        });

        $("#add_dep").click(function(){
            $("#agregar-departamento").modal('show');
            let id = $(this).data('id');
            if (id !== undefined) {
                let nombreActual = $('.deptos tr#' + id + ' .nombre').text();
                $("#nombre").val(nombreActual);
                $("#id").val(id);
            }
            document.getElementById('nombre_accion').innerHTML= 'Agregar departamento';
        });

    </script>
</body>

</html>
