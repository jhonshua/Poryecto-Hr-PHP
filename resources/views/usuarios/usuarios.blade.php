<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
    .top-line-black {
        width: 19%;
    }
</style>

<body>
@include('includes.navbar')
@include('usuarios.usuario_modal')

<div class="container">
    @include('includes.header',['title'=>'Usuarios del sistema', 'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'bandeja'])
    <div>

        <button type="button" class="button-style" onclick="registarUsuarioModal()">
            <img src="/img/icono-crear.png" class="button-style-icon">Crear
        </button>
        <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
        <br>
        <br>
    </div>


    @if ($errors->first('nombre_completo') || $errors->first('email') || $errors->first('password'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: Ocurrio un problema, intentelo nuevamente.</strong>
            </div>
        </div>
    @endif


    @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
    <div class="row" id="succes_alert" style="display: none;">
        <div class="alert alert-success" style="width: 100%;" align="center">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Notificación: EL usuario se elimino correctamente </strong>
        </div>
    </div>
    <div class="article border">
        <table id="usuariosSistema" class="table w-100">
            <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col" class="gone">ID</th>
                <th scope="col" class="text-center">Nombre</th>
                <th scope="col" class="text-center">Email</th>
                <th scope="col" class="text-center gone">Registro</th>
                <th scope="col" class="text-center">Estatus</th>
                <th scope="col" class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach($usuarios as $usuario)
                <tr>
                    <td></td>
                    <td class="gone">{{ $usuario->id }}</td>
                    <td class="text-center">{{ $usuario->nombre_completo }}</td>
                    <td class="text-center">{{ $usuario->email }}</td>
                    <td class="text-center gone">{{ $usuario->fecha_creacion }}</td>
                    <td class="text-center">
                        @if($usuario->estatus == 1)
                            <label class="text-success font-weight-bold">Activo</label>
                        @elseif($usuario->estatus == 0)
                            <label class="text-secondary font-weight-bold">Inactivo</label>
                        @endif
                    </td>
                    <td>
                        <div class="center w-px-200">
                            <a onclick="editarUsuarioModal({{ $usuario->id }})" rel="Editar usuario" title="Editar usuario">
                                <img src="/img/icono-editar.png" class="button-style-icon m-2">
                            </a>

                            @if(isset(Auth::user()->autofacturador) && !Auth::user()->autofacturador)
                                <a href="{{ route('sistema.usuarios.permisos.cambiar.empresa', ['usuario' => $usuario->id,'empresa' => '0'] ) }}"
                                   title="Editar permisos" rel="Permisos del usuario">
                                    <img src="/img/icono-permisos.png" class="button-style-icon m-2">
                                </a>
                            @endif

                                <a title="Editar empresas" href="{{route('usuarios.empresa', $usuario->id )}}">
                                    <img src="/img/icono-edita-empresa.png" class="button-style-icon m-2">
                                </a>

                            <div data-id="{{$usuario->id}}" alt="Eliminar usuario" title="Eliminar usuario"
                                 class="btn delete">
                                <img src="/img/eliminar.png" class="button-style-icon">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<form method="post" id="usuario_delete_form" action="{{ route('usuarios.eliminar') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" id="id_delete" value="">
</form>
@include('includes.footer')

<script src="{{asset('js/typeahead.js')}}"></script>
<script src="{{asset('js/helper.js')}}"></script>
@stack('scripts')
<script type="text/javascript">


    let dataSrc = [];
    let table = $('#usuariosSistema').DataTable({
        scrollY: '65vh',
        scrollCollapse: true,
        processing: true,
        "language": {
            search: '',
            searchPlaceholder: 'Buscar registros por nombre',
            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
        },
        initComplete: function () {

            let api = this.api();
            api.cells('tr', [2]).every(function () {

                let data = $('<div>').html(this.data()).text();
                if (dataSrc.indexOf(data) === -1) {
                    dataSrc.push(data);
                }
            });
            dataSrc.sort();

            $('.dataTables_filter input[type="search"]', api.table().container())
                .typeahead({
                        source: dataSrc,
                        afterSelect: function (value) {
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


    $(".delete").click(function () {
        $("#succes_alert").hide();
        id = $(this).data('id');

        swal({
            title: "Estas seguro de eliminar el registro",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    document.getElementById("id_delete").value = id;
                    swal("Espere un momento, la información esta siendo procesada", {
                        icon: "success",
                        buttons: false,
                    });

                    document.getElementById("usuario_delete_form").submit();

                } else {
                    swal("La accion fue cancelada!");
                }
            });

    });

    const modal_usuario = $('#usuariosModal');

    function registarUsuarioModal() {
        modal_usuario.modal('show');
        document.getElementById("btn_guardar").hidden = false;
        document.getElementById("btn_actualizar").hidden = true;

        $('#usuario_form')[0].reset();

        if ($('#autofacturador').val() == 1) {
            docsAutofac($('#autofacturador').val(1), 0);
        }
        baseAutofacturador();
        getVendedor();

    }

    $('#usuariosModal #btn_guardar').click(function () {

        if ($('#comision').val() < 0) {
            return alertify.alert('Error', 'Comision no puede ser negativa');
        }

        $(this).attr('disabled', true).text('Espere...');
        var url = "{{route('sistema.usuarios.addUpdateUsuario')}}";
        url = url.replace('*ID*', idUsuario);
        $.ajax({
            type: "POST",
            url: url,
            data: $('#usuario_form').serialize(),
            dataType: 'JSON',
            success: function (response) {
                if (response.ok == 1) {
                    $('#usuariosModal').modal('hide');
                    $('.modal-backdrop').hide();
                    alertify.success('El usuario se registrado correctamente.');
                    //table.ajax.reload();
                } else {
                    alertify.alert('Error', 'Ocurrió un error al aguardar el usuario. Intente nuevamente.');
                }
            }
        }).done(function () {
            $('#usuariosModal #btn_guardar').text('Guardar');
        });
    });

    function editarUsuarioModal(id) {
        modal_usuario.modal('show');
        document.getElementById("btn_guardar").hidden = true;
        document.getElementById("btn_actualizar").hidden = false;
        datosUsuario(id);

    }
</script>
</body>

</html>
