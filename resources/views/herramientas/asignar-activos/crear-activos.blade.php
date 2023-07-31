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

        @include('includes.header',['title'=> 'Crear activo',
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
                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-selected="false">DATOS GENERALES ACTIVO</a>
                        <a class="nav-item nav-link " id="nav-asignar-archivos-tab" data-toggle="tab" href="#nav-asignar-archivos" role="tab" aria-selected="false">ASIGNAR ARCHIVOS</a>
                        <a class="nav-item nav-link " id="nav-asignar-campos-tab" data-toggle="tab" href="#nav-asignar-campos" role="tab" aria-selected="true">ASIGNAR CAMPOS EXTRA</a>
                    </div>
                </nav>
                <form action="{{route('asignaActivos.creaModifica')}}" method="post" id="addform" enctype="multipart/form-data">
                    @csrf
                    <div class="tab-content mt-4" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                            <div class="card col-md-12">
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Nombre (*)</label>
                                            <input type="text" class="form-control " name="nombre" id="nombre" placeholder="Ingresa un nombre activo" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Marca</label>
                                            <input type="text" class="form-control " name="marca" id="marca" placeholder="Ingresa una marca">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Modelo</label>
                                            <input type="text" class="form-control " name="modelo" id="modelo" placeholder="Ingresa el modelo">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>No.Serie</label>
                                            <input type="text" class="form-control " name="nserie" id="nserie" placeholder="Ingresa no.serie ">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Categoría (*)</label>
                                            <select class="form-control select-clase mb-2 idcat" name="id_categoria_activo" id="id_categoria" required>
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
                                        <label for="">Estado del activo</label>
                                            <select name="estatus" id="estatus" class="form-control select-clase mb-2" style="width: 100%!important;" required>
                                                <option value="">SELECCIONE</option>
                                                <option value="0">Inactivo</option>
                                                <option value="1">Activo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Descripción (*)</label>
                                            <textarea name="descripcion" id="descripcion" cols="30" class="form-control form-control-sm" placeholder="Ingresa una descripción" required></textarea>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Comentarios</label>
                                            <textarea name="comentarios" id="comentarios" cols="30" class="form-control form-control-sm" placeholder="Ingresa un comentario"></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn button-style agregar">Guardar</button>
                                    @if(session('respuesta_modificacion'))
                                    <input type="hidden" id="respuesta" value="{{ session('respuesta_modificacion')}}">
                                    @else
                                    <input type="hidden" name="respuesta" id="respuesta">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-asignar-archivos" role="tabpanel" aria-labelledby="nav-asignar-archivos-tab">
                            <div class="card col-md-12">
                                <div class="card-body">
                                    <div class="form-row d-flex justify-content-center">
                                        <table class="table col-md-8" id="tbladdarch">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50%">Nombre del archivo</th>
                                                    <th>Archivo</th>
                                                    <th style="width: 5%">
                                                        <div class="btn btn-warning btn-sm" id="agregarArchivos">
                                                            <li class="fas fa-plus"></li>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-asignar-campos" role="tabpanel" aria-labelledby="nav-asignar-campos-tab">
                            <div class="card col-md-12">
                                <div class="card-body">
                                    <div class="form-row d-flex justify-content-center">
                                        <table class="table mb-0 " id="tbladdval">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30%">Nombre label</th>
                                                    <th>Valor</th>
                                                    <th style="width: 5%">
                                                        <div class="btn btn-warning btn-sm" id="agregarItem">
                                                            <li class="fas fa-plus"></li>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <script src="{{asset('public/js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('public/js/parsley/i18n/es.js')}}"></script>
        <script>
            $(function() {

                let cont_add_items = 0;
                let cont_add_items_arch = 0;

                let respuesta = document.getElementById('respuesta');
                if (respuesta.value == "1") {
                    swal("El registro se actualizó correctamente.", {
                                icon: "success",
                            });
                    respuesta.value = "";
                }

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
                $('#activo').click(() => {

                    let activo = document.getElementById('estatus');
                    (document.getElementById('activo').checked) ? activo.value = 1: activo.value = 0;
                });

                $('#agregarItem').click(function() {

                    let contenido = `<tr class='fila-items' id='fila-item${cont_add_items}' >
                            <td><input type="text" class="form-control" name="nombre_label[]" placeholder="Ingresa una etiqueta" required/></td>
                            <td><input type="text" class="form-control"  name="valor[]" placeholder="Ingresa un valor" required/></td>
                            <td> <div> <button class="borrar btn eliminar-item"  alt="Eliminar categoria de activos" title="Eliminar categoria"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button></div></td>
                        </tr>`;
                    cont_add_items++;
                    $("#tbladdval").append(contenido);

                });
                $(document).on('click', '.eliminar-item', function(event) {

                    event.preventDefault();
                    $(this).closest('tr').remove();
                    cont_add_items--;

                });
                $('#nav-tab').click(function(e) {
                    e.preventDefault();
                });
                $('#agregarArchivos').click(function() {

                    if (cont_add_items_arch < 3) {
                        let contenido = `<tr class='fila-items-archivos' id='fila-item-archivos${cont_add_items_arch}' >
                                <td><input type="text" class="form-control" name="nombre_archivo[]" placeholder="Escribe un nombre para el archivo" required/></td>
                                <td>
                                    <div class="file rounded d-flex  align-items-center p-2 mr-3 mb-3">
                            
                                        <label class="name tooltip_ btn button-style" data-toggle="tooltip" title="" for="file_archivo${cont_add_items_arch}" aria-describedby="ui-id-14">Agregar archivo</label>
                                        <input type="file" name="file_archivo[]" id="file_archivo${cont_add_items_arch}" class="invisible" accept=".pdf, .png, .jpg, .doc, .docx" style="display: none;" required/>
                                    </div>
                                </td>
                                <td> <div>  <button class="borrar btn eliminar-item-arch"  alt="Eliminar categoria de activos" title="Eliminar categoria"> <img src="/img/icono-eliminar.png" class="button-style-icon"></button></div></td>
                            </tr>`;
                        cont_add_items_arch++;
                        $("#tbladdarch").append(contenido);
                    }
                    $(".agregar").removeClass('d-none');

                });
                $(document).on('click', '.eliminar-item-arch', function(event) {

                    event.preventDefault();
                    $(this).closest('tr').remove();
                    cont_add_items_arch--;
                });
                $("#spinner").addClass("ocultar");
            });
        </script>