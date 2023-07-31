<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<style type="text/css">
    .wrapper-table {

        margin-bottom: 20px;
        overflow-y: scroll;

    }

    .form-check-inline {
        align-items: top !important;
    }

    .invalido {
        color: #EE4A30;
    }



    label {
        font-weight: bold;
        margin-top: 15px;
    }

    .bg-gray {
        background-color: #fbba00 solid;
    }

    nav a {
        color: black;
    }

    nav a:hover {
        color: #fbba00;
    }

    .btn-outline-success {
        color: #28a745;
        border-color: white !important;
    }

    .article-nav {
        width: 100%;
        height: auto;
        float: left;
        box-sizing: border-box;
    }

    .nav-tabs .nav-link {
        border: 2px solid transparent;
        border-left-color: #fbba00;
        color: gray;
    }

    .nav-tabs .nav-item.show .nav-link,
    .nav-tabs .nav-link.active {
        color: black;
        font-weight: bold;
    }

    .nav-link {
        display: block;
        padding: 0.5rem 1.8rem !important;
    }

    .input-style {
        width: 260px !important;
    }

    .custom-form {
        margin-top: 32px;
        margin-left: 20px;
    }

    .center-table th {
        text-align: center;
    }
</style>

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Modificar activo',
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'asignaActivos.tabla'])

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

        <div class="row">
            <div class="col-md-12 mt-4">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link cambia-tab" id="nav-home-tab" data-toggle="tab" data-ntab="1" href="#nav-home" role="tab" aria-selected="false">DATOS GENERALES ACTIVO</a>
                        <a class="nav-item nav-link cambia-tab" id="nav-asignar-archivos-tab" data-toggle="tab" data-ntab="2" href="#nav-asignar-archivos" role="tab" aria-selected="false">ASIGNAR ARCHIVOS</a>
                        <a class="nav-item nav-link cambia-tab" id="nav-asignar-campos-tab" data-toggle="tab" data-ntab="3" href="#nav-asignar-campos" role="tab" aria-selected="true">ASIGNAR CAMPOS EXTRA</a>
                    </div>
                </nav>
                @foreach($resultados as $res)
                <div class="tab-content mt-4" id="nav-tabContent">
                    <div class="tab-pane fade show active " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <form action="{{route('asignaActivos.creaModifica')}}" method="post" id="addform" enctype="multipart/form-data">
                            @csrf
                            <div class="card col-md-12">
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Nombre (*)</label>
                                            <input type="text" class="form-control " name="nombre" id="nombre" value="{{$res->nombre}}" placeholder="Ingresa un nombre activo" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Marca</label>
                                            <input type="text" class="form-control " name="marca" id="marca" value="{{$res->marca}}" placeholder="Ingresa una marca">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Modelo</label>
                                            <input type="text" class="form-control " name="modelo" id="modelo" value="{{$res->modelo}}" placeholder="Ingresa el modelo">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>No.serie</label>
                                            <input type="text" class="form-control " name="nserie" id="nserie" value="{{$res->nserie}}" placeholder="Ingresa no.serie ">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Categoría (*)</label>
                                            <input type="hidden" id="ncategoria" value="{{$res->nombre_activo}}">
                                            <input type="hidden" id="idcatmod" value="{{\Crypt::encrypt($res->id_categoria_activo)}}">
                                            <select class="form-control idcat" name="id_categoria_activo" id="id_categoria" required>
                                                @if(count($categorias) > 0)
                                                <option value="">Selecciona una categoría</option>
                                                @foreach ($categorias as $categoria)
                                                <option value="{{ \Crypt::encrypt($categoria->id)}}">{{$categoria->nombre_activo }} </option>
                                                @endforeach
                                                @else
                                                <option>No hay categorías</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6 ">
                                            <label>Estado del activo:</label>
                                            <select class="form-control" name="estatus" id="estatus" required>
                                                <option value="0" {{($res->estatus_activo == 0) ? 'selected' : ''}}>Inactivo</option>
                                                <option value="1" {{($res->estatus_activo == 1) ? 'selected' : ''}}>Activo</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Descripción (*)</label>
                                            <textarea name="descripcion" id="descripcion" cols="30" class="form-control form-control-sm" placeholder="Ingresa una descripción" required>{{$res->descripcion}}</textarea>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Comentarios</label>
                                            <textarea name="comentarios" id="comentarios" cols="30" class="form-control form-control-sm" placeholder="Ingresa un comentario">{{$res->comentarios}}</textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn button-style agregar">Guardar</button>
                                    <input type="hidden" name="id" id="id" value="{{\Crypt::encrypt($res->id)}}">
                                    @if(session('respuesta_modificacion'))
                                    <input type="hidden" id="respuesta" value="{{ session('respuesta_modificacion')}}">
                                    @else
                                    <input type="hidden" name="respuesta" id="respuesta">
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="nav-asignar-archivos" role="tabpanel" aria-labelledby="nav-asignar-archivos-tab">
                        <div class="card col-md-12">
                            <div class="card-body">
                                <div class="form-row d-flex justify-content-center">
                                    <table class="table mb-0 center-table col-md-10" id="tbladdarch">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%">Nombre del archivo</th>
                                                <th>Archivo</th>
                                                <th style="width: 15%">
                                                    <div class="btn btn-warning btn-sm modificar-item-arch" data-toggle="tooltip" data-placement="bottom" title="Anexar archivo" data-idactivo="{{\Crypt::encrypt($res->id)}}">
                                                        <li class="fas fa-plus"></li>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody style="text-align: center;">
                                            @if(count($activos_archivos) > 0)
                                            @foreach($activos_archivos as $activo_archivo)
                                            <tr>
                                                <td>{{$activo_archivo->nombre_archivo}}</td>
                                                <td><a href="{{asset("public/repositorio")}}/{{$id_empresa}}/documentos_activos/{{$activo_archivo->file_archivo}}" target="_blank">{{$activo_archivo->file_archivo}}</a></td>
                                                <td>
                                                    <div class="btn  btn-sm modificar-item-arch" data-toggle="tooltip" data-placement="bottom" title="Editar archivo" data-id_activomod='{{\Crypt::encrypt($res->id)}}' data-id_archivomod="{{\Crypt::encrypt($activo_archivo->id)}}" data-nombre_archivomod="{{$activo_archivo->nombre_archivo}}">
                                                        <img src="/img/icono-editar.png" class="button-style-icon">
                                                    </div>
                                                    <div class="btn  btn-sm eliminar-item-arch" data-toggle="tooltip" data-placement="bottom" title="Eliminar archivo" data-id_activoel='{{\Crypt::encrypt($res->id)}}' data-id_archivoel="{{\Crypt::encrypt($activo_archivo->id)}}" data-nombre_archivoel="{{$activo_archivo->nombre_archivo}}">
                                                        <img src="/img/icono-eliminar.png" class="button-style-icon">
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td colspan="3">
                                                    <p class="text-center">NO HAY ARCHIVOS AGREGADOS</p>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-asignar-campos" role="tabpanel" aria-labelledby="nav-asignar-campos-tab">
                        <div class="card col-md-12">
                            <div class="card-body">
                                <form method="post" action="{{ route('asignaActivo.actualizaCampo') }}" enctype="multipart/form-data" id="modcamposext">
                                    @csrf
                                    <div class="form-row d-flex justify-content-center">
                                        <table class="table  mb-0 center-table" id="tbladdval">
                                            <thead>
                                                <tr>
                                                    <th style="width: 22%">Nombre label</th>
                                                    <th>Valor</th>
                                                    <th style="width: 10%">
                                                        <div class="btn btn-warning btn-sm" id="agregar_campo_ext" data-toggle="tooltip" data-placement="bottom" title="Agregar campo extra" data-id_activoadd='{{\Crypt::encrypt($res->id)}}'>
                                                            <li class="fas fa-plus"></li>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody style="text-align: center;">
                                                @php $ocultar_boton ='' @endphp
                                                @if(count($activosCamposExtra) >0)
                                                @foreach ($activosCamposExtra as $activoCampoExtra)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="nombre_label[]" value="{{$activoCampoExtra->nombre_label}}" placeholder="Ingresa una etiqueta" required />
                                                        <input type="hidden" class="form-control" name="id_campo_extra[]" value="{{\Crypt::encrypt($activoCampoExtra->id)}}" required>
                                                        <input type="hidden" class="form-control" name="idactivo[]" value="{{\Crypt::encrypt($res->id)}}" required>
                                                    </td>
                                                    <td><input type="text" class="form-control" name="valor_campo_extra[]" value="{{$activoCampoExtra->valor}}" placeholder="Ingresa un valor" required /></td>
                                                    <td>
                                                        <div class="btn btn-sm eliminar-campoext" data-toggle="tooltip" data-placement="bottom" title="Eliminar campo extra" data-id_activoa='{{\Crypt::encrypt($res->id)}}' data-id_campo_ext='{{\Crypt::encrypt($activoCampoExtra->id)}}' data-nombre_label='{{$activoCampoExtra->nombre_label}}'>
                                                            <img src="/img/icono-eliminar.png" class="button-style-icon">
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @else
                                                @php $ocultar_boton ='d-none' @endphp
                                                <tr>
                                                    <td colspan="3">
                                                        <p class="text-center">NO HAY CAMPOS EXTRA AGREGADOS</p>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                <div class="my-2">
                                    <button type="submit" class="btn button-style agregar {{$ocultar_boton}} btnmodcampext">Guardar</button>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @include('herramientas.asignar-activos.modals.agregaModificaCamosModal')
        @include('herramientas.asignar-activos.modals.agregaModificaArchivoModal')

        <style>
            .custom-form {
                margin-top: 32px;
                margin-left: 20px;
            }
        </style>

        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script src="{{asset('js/typeahead.js')}}"></script>

        <script>
            $(function() {

                let cont_add_items = 0;
                let cont_add_items_arch = 0;

                let respuesta = document.getElementById('respuesta');
                let estatus = document.getElementById('estatus');
                let nombre_categoriamod = document.getElementById('ncategoria').value;
                let idcategoria = document.getElementById('idcatmod').value;


                (estatus.value == "1") ? $("#activo").prop('checked', true): $("#activo").prop('checked', false);

                $('.idcat option').each(function() {

                    let texto_select = $(this).text();
                    texto_select = texto_select.toLowerCase();
                    let texto_nombre_categoria = nombre_categoriamod.toLowerCase();

                    if (texto_select.includes(texto_nombre_categoria)) {

                        $(`.idcat option[value='${$(this).val()}']`).remove();
                    }
                });

                $('#id_categoria').append(`<option value='${idcategoria}' selected >${nombre_categoriamod}</option>`);

                if (respuesta.value == "1") {

                    swal("El registro se actualizó correctamente.", {
                        icon: "success",
                    });
                    respuesta.value = "";
                }

                switch (localStorage.getItem('ntab')) {
                    case '2':
                        $("#nav-home-tab").removeClass('active');
                        $("#nav-home").removeClass('show active');

                        $("#nav-asignar-campos-tab").removeClass('active');
                        $("#nav-asignar-campos").removeClass('show active');

                        $("#nav-asignar-archivos-tab").addClass('active');
                        $("#nav-asignar-archivos").addClass('show active');
                        break;
                    case '3':
                        $("#nav-home-tab").removeClass('active');
                        $("#nav-home").removeClass('show active');

                        $("#nav-asignar-archivos-tab").removeClass('active');
                        $("#nav-asignar-archivos").removeClass('show active');

                        $("#nav-asignar-campos-tab").addClass('active');
                        $("#nav-asignar-campos").addClass('show active');
                        break;
                    default:
                        $("#nav-home-tab").addClass('active');
                        $("#nav-home").addClass('show active');

                        $("#nav-asignar-archivos-tab").removeClass('active');
                        $("#nav-asignar-archivos").removeClass('show active');

                        $("#nav-asignar-campos-tab").removeClass('active');
                        $("#nav-asignar-campos").removeClass('show active');

                        break;
                }

                $('#activo').click(() => {

                    let activo = document.getElementById('estatus');
                    (document.getElementById('activo').checked) ? activo.value = 1: activo.value = 0;
                });

                $(".cambia-tab").click(function() {
                    let ntab = $(this).data('ntab');
                    localStorage.setItem('ntab', ntab);

                });

                $('.agregar').click(function() {

                    let form = $("#addform");
                    if (form.parsley().isValid()) {
                        $(this).text('Espere...');
                        $(this).prop('disabled', true);
                        $('#addform').submit();
                    } else {
                        form.parsley().validate();
                    }
                });
                $('.eliminar-item-arch').click(function() {

                    let id_activoel = $(this).data('id_activoel');
                    let id_archivoel = $(this).data('id_archivoel');
                    validarBorradoArch(id_activoel, id_archivoel);

                });

                function validarBorradoArch(id_activoel, id_archivoel) {

                    swal({
                            title: "",
                            text: "¿Esta seguro de eliminar este registro?",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                borrarArchivo(id_activoel, id_archivoel);
                            }
                        });
                }

                function borrarArchivo(id_activoel, id_archivoel) {
                    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'id_activoel': id_activoel,
                        'id_archivoel': id_archivoel,
                        '_token': CSRF_TOKEN
                    }

                    $.ajax({
                        url: `{{ route('asignaActivos.eliminaArchivos') }}`,
                        type: 'GET',
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
                $(".modificar-item-arch").click(function() {

                    $("#custom-p-mod").empty();
                    $("#modalModificarArchivo").modal('show');
                    $("#id_archivoel_mod").val($(this).data('id_archivomod'));

                    if ($(this).data('id_archivomod') != undefined) {


                        $("#custom-p-mod").addClass('d-none');
                        $("#etiqueta_archivo").text('Editar');
                        $("#id_activoel_mod").val($(this).data('id_activomod'));
                        $("#nombre_archivo").val($(this).data('nombre_archivomod'));

                    } else {

                        $("#custom-p-mod").addClass('d-none');
                        $("#etiqueta_archivo").text('Crear');
                        $("#id_activoel_mod").val($(this).data('idactivo'));

                    }
                });

                $("#cerrar-modal-mod").click(function() {
                    $('#formaddmod')[0].reset();
                });

                $(".btnmodcampext").click(function() {

                    let form = $("#modcamposext");

                    if (form.parsley().isValid()) {

                        $(this).text('Espere...');
                        $(this).prop('disabled', true);
                        $('#modcamposext').submit();

                    } else {
                        form.parsley().validate();
                    }
                });

                $(".eliminar-campoext").click(function() {

                    let id_activo = $(this).data('id_activoa');
                    let id_campo_ext = $(this).data('id_campo_ext');
                    validarBorradoCampo(id_activo, id_campo_ext);

                });

                function validarBorradoCampo(id_activo, id_campo_ext) {

                    swal({
                            title: "",
                            text: "¿Esta seguro de eliminar este registro?",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                borrarCampo(id_activo, id_campo_ext);
                            }
                        });
                }

                function borrarCampo(id_activo, id_campo_ext) {
                    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'id_activo': id_activo,
                        'id_campo_ext': id_campo_ext,
                        '_token': CSRF_TOKEN
                    }

                    $.ajax({
                        url: `{{ route('asignaActivo.creaEliminaCampo') }}`,
                        type: 'GET',
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

                $("#agregar_campo_ext").click(function() {

                    $("#custom-p-camp").addClass('d-none');
                    $("#modalAgregarEliminarCampoExt").modal('show');
                    $("#id_activo").val($(this).data('id_activoadd'));
                    $("#agregarCampos").removeClass('d-none');
                    $("#nombre_label_add").attr('required');
                    $("#valor_add").attr('required');

                });
            });
        </script>