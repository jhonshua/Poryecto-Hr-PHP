<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@php
foreach($prestamo as $presta){
$id = $presta->id;
$nombre = $presta->nombre;
}
$idGeneral= null;

foreach($generales as $general){
$idGeneral = $general->id;

}

@endphp


<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Editar: '.$nombre,
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'tiposPrestamos.tabla'])

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
        <div class="row m-0 p-0">
            <div class="col-md-6 p-2">
                <div class="article border mb-4 px-4">
                    <label class="font-weight-bold center font-size-1-2em">Beneficio</label>
                    <hr>
                    @foreach($prestamo as $presta)
                    <form action="{{route('tiposPrestamo.actualiza')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{$presta->id}}" name="id" id="id">
                        <div class="form-row mt-4">
                            <label for="">Nombre:</label>
                            <input name="nombre" class="form-control" type="text" value="{{ old('nombre', ($presta) ? $presta->nombre : null ) }}" id="nombre" placeholder="Nombre del tipo de préstamo" required>
                        </div>
                        <div class="form-row mt-3">
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label for="">Estatus:</label>
                                    <select name="estatus" id="estatus" class="form-control" required>
                                        <option value="1" {{ old('estatus', ($presta) ? $presta->estatus : null) == 1 ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('estatus', ($presta) ? $presta->estatus : null) == 0 ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label for="">Tipo de solicitud:</label>
                                    <select name="tipo_solicitud" id="" class="form-control" required>
                                        <option value="1" {{ old('tipo_solicitud', ($presta) ? $presta->tipo_solicitud : null) == 1 ? 'selected' : '' }}>Solicitud</option>
                                        <option value="2" {{ old('tipo_solicitud', ($presta) ? $presta->tipo_solicitud : null) == 2 ? 'selected' : '' }}>Prestamo</option>
                                    </select>

                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label for="">Antigüedad en meses:</label>
                                    <select name="antiguedad_meses" id="" class="form-control" required>
                                        <option value="">Selecciona una opción...</option>
                                        @for ($i = 0; $i < 13; $i++) <option value="{{ $i }}" {{ old('antiguedad_meses', ($presta) ? $presta->antiguedad_meses : null) == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                            </option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label for="">Descripción del préstamo:</label>
                                    <textarea class="form-control" name="descripcion" id="" rows="3" placeholder="Descripción del tipo de préstamo">{{ old('descripcion', ($presta) ? $presta->descripcion : null) }}</textarea>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label for="">Notas:</label>
                                    <textarea class="form-control" name="notas" id="" rows="3" placeholder="Notas hacia el usuario">{{ old('notas', ($presta) ? $presta->notas : null) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-1 text-center">
                            <button type="submit" class="btn button-style btn btn-warning btn-sm btndeshabilita">Guardar </button>
                        </div>
                    </form>
                    @endforeach
                </div>
                <br>
                <br>
                <div class="article border mb-4">
                    <label class="font-weight-bold center font-size-1-2em">Requisitos</label>
                    <hr>
                    <table class="table w-100">
                        <thead style="text-align: center;">
                            <tr>
                                <th width="240px">Requisito</th>
                                <th>Tipo</th>
                                <th width="40px">Valor</th>
                                <th>Acciones</th>
                            </tr>
                        <tbody style="text-align: center;">
                            @foreach($requisito as $requisi)
                            <tr>
                                <td width="240px">{{ $requisi->nombre }}</td>
                                <td>{{ $requisi->tipo }}</td>
                                <td width="40px">{{ $requisi->valor }}</td>
                                <td>
                                    <a href="#" data-id="{{$requisi->id}}" class="borrar btn btn-sm mr-2" alt="Borrar" title="Borrar"> <img src="/img/icono-eliminar.png" class="button-style-icon"></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        </thead>
                    </table>
                    <div class="col-md-12 mt-2 mb-1 text-center">

                        <button type="button" class="button-style ml-3 mb-3 nuevo" data-toggle="modal" data-target="#requisitoModal" data-idPresta="{{$id}}"> <img src="/img/icono-crear.png" class="button-style-icon">Crear</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 p-2">
                <div class="article border mb-4 px-4">
                    <label class="font-weight-bold center font-size-1-2em">Generales app móvil</label>
                    <hr>


                    <form action="{{route('generales.crea')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <input type="hidden" value="{{$id}}" name="id_prestamo_tipos" id="id_prestamo_tipos">
                            <input type="hidden" value="{{$idGeneral}}" name="id" id="id">

                            <div class="form-group col-md-12">
                                <label>Nombre ancla: (*)</label>
                                <input type="text" class="form-control " name="nombre_ancla" id="nombre_ancla" value='{{ !empty($general->nombre_ancla) ? $general->nombre_ancla :""}}' placeholder="Ej. Si tu barrio no te respalda, tu seguro sí. " required>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Nombre descripción: (*)</label>
                                <input type="text" class="form-control " name="nombre_descripcion" id="nombre_descripcion" value="{{ !empty($general->nombre_descripcion) ? $general->nombre_descripcion :""}}" placeholder="Ej. Protección, seguros y más..." required>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Subir imagen ancla:</label>
                                <div class="justify-content-center text-center">
                                    <div class="custom-file ">
                                        <input type="file" class="custom-file-input" onchange="file('archivo')" name="imagen_ancla" id="imagen_ancla" accept=".png, .jpg, .jpeg, .gif">
                                        <label class="custom-file-label" for="archivo" id="archivo_text">Archivo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Subir imagen detalle: </label>
                                <div class="justify-content-center text-center">
                                    <div class="custom-file ">
                                        <input type="file" class="custom-file-input" onchange="file('archivo')" name="imagen_detalle" id="imagen_detalle" accept=".png, .jpg, .jpeg, .gif">
                                        <label class="custom-file-label" for="archivo" id="archivo_text">Archivo</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    @if(!empty($general->imagen_ancla))
                                    <div class="">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <a href="{{ asset($general->imagen_ancla)}}" target="_blank">Ver imagen ancla</a>
                                    </div>
                                    @endif
                                </div>
                                <div class="form-group col-md-12">
                                    @if(!empty($general->imagen_detalle) )
                                    <div class="">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <a href="{{ asset($general->imagen_detalle)}}" target="_blank">Ver imagen detalle</a>

                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Descripción ancla: (*)</label>
                                    <br>
                                    <textarea name="descripcion_ancla" id="descripcion_ancla" class="form-control" rows="6" required>{{ !empty($general->nombre_descripcion) ? $general->descripcion_ancla :""}}</textarea>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Descripción detalle: </label>
                                    <br>
                                    <textarea name="descripcion_detalle" id="summernote" cols="66" rows="8">{{ !empty($general->nombre_descripcion) ? $general->descripcion_detalle :""}}</textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Teléfono: (*)</label>
                                    <input type="text" class="form-control " name="telefono_proveedor" id="telefono_proveedor" placeholder="Si el empleado puede comunicarse directamente con el proveedor de la prestación" value="{{ !empty($general->telefono_proveedor) ? $general->telefono_proveedor :""}}" placeholder="Ej. Protección, seguros y más...">
                                </div>

                                <div style="text-align: center; align-items:center;">
                                    <button type="submit" class="btn button-style btn btn-warning btn-sm btndeshabilita">Guardar </button>
                                </div>
                                @if(!empty($general->id))

                                <button type="button" class="button-style ml-3 mr-3 nuevo" data-toggle="modal" data-target="#anexarDocModal" data-idPresta="{{$id}}"> <img src="/img/icono-crear.png" class="button-style-icon">Crear documento</button>



                                @endif
                            </div>
                    </form>


                    @if($documentos != null)

                    <form action="{{ route('generales.actualizaDoc') }}" method="post" enctype="multipart/form-data" id="modDoc">
                        @csrf
                        <div class="form-row mt-4">
                            <label class="font-weight-bold  font-size-1-2em">Documentos</label>
                        </div>
                        <div class="form-row mt-2">
                            <div class="form-group col-md-12">
                                <table class="table">
                                    <thead class="table">
                                        <tr>
                                            <th>Descripción</th>
                                            <th>Documento</th>
                                            <th>Remplazo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentos as $doc )
                                        <tr>
                                            <td><input type="text" class="form-control" name="descripcion[]" value="{{$doc->descripcion}}" required></td>
                                            <td>
                                                <a href="{{ asset($doc->documento)}}" target="_blank">
                                                    <div class="btn  btn-block "> <img src="/img/ver-documentos-empleado.png" class="button-style-icon">
                                                    </div>
                                                </a>
                                            </td>
                                            <td><input type="file" class="form-control" name="documento[]"></td>
                                            <td> <a href="#" data-id="{{$doc->id}}" class="btn borrarDoc">
                                                    <div class="btn borrarDoc  d-flex justify-content-center">
                                                        <img src="/img/icono-eliminar.png" class="button-style-icon">
                                                    </div>
                                                </a></td>
                                            <td>
                                                <input type="hidden" name="id_documento[]" value="{{$doc->id}}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="col-md-12 mt-2 mb-1 text-center">

                                    <button type="submit" class="button-style ml-3 mr-3 btndeshabilita">Guardar </button>

                                </div>

                            </div>

                        </div>
                        <input type="hidden" name="idBeneficio" value="{{$id}}"></td>
                    </form>

                    @endif
                </div>
            </div>
        </div>

        @include('includes.footer')
        @include('herramientas.prestamos.tipos-prestamos.tiposPrestamosRequisito-modal')
        @include('herramientas.prestamos.tipos-prestamos.anexarDocumento-modal')

        <script src="{{asset('public/js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('public/js/parsley/i18n/es.js')}}"></script>

        <script>
            $(".btn.borrar").click(function() {
                let id = $(this).data('id');
                validarBorrado(id);
            });


            function validarBorrado(id) {

                swal({
                        title: "",
                        text: "¿Esta seguro de eliminar este requisito?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            borrarRequisito(id, estatus);
                        }
                    });

                function borrarRequisito(id) {
                    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'id': id,
                        '_token': CSRF_TOKEN
                    }

                    $.ajax({
                        url: `{{ route('requisitos.elimina') }}`,
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
                            } else {
                                swal("", "Ha ocurrido un error, intentalo más tarde.", "error");

                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            swal("", "Ocurrió un error al eliminar el registro!", "error");

                        }
                    });

                }
            }
        </script>

        <script>
            $(".btn.borrarDoc").click(function() {
                let id = $(this).data('id');
                validarBorradoDoc(id);
            });


            function validarBorradoDoc(id) {

                swal({
                        title: "",
                        text: "¿Esta seguro de eliminar este documento?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            borrarDocumento(id, estatus);
                        }
                    });

                function borrarDocumento(id) {
                    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'id': id,
                        '_token': CSRF_TOKEN
                    }

                    $.ajax({
                        url: `{{ route('generales.eliminaDoc') }}`,
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
                            } else {
                                swal("", "Ha ocurrido un error, intentalo más tarde.", "error");

                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            swal("", "Ocurrió un error al eliminar el registro!", "error");

                        }
                    });

                }
            }
        </script>
        <script type="text/javascript">
            function file(val) {

                var text = val + "_text";
                document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
            }
        </script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .form-group .row {
                margin-bottom: 0px;
            }
        </style>


        <script type="text/javascript">
            let contador_val_num = 0;
            let contador_items = 0;

            $(function() {

                let contador_numerico = 0;

                $('.guardar-doc').click(function() {

                    let form = $("#adddoc");

                    if (form.parsley().isValid()) {

                        $(this).text('Espere...');
                        $(this).prop('disabled', true);
                        $('#adddoc').submit();

                    } else {

                        form.parsley().validate();

                    }
                });


                $(document).on('change', 'input[type="file"]', function() {

                    let fileName = this.files[0].name;

                    if (fileName != "") {

                        let ext_archivo = fileName.split('.').pop();
                        ext_archivo = ext_archivo.toLowerCase();
                        let extension = ['jpg', 'png', 'icon', 'pdf'];

                        if (!extension.includes(ext_archivo)) {
                            swal("", "El formato es incorrecto inténtalo  nuevamente.", "error");

                            this.value = '';
                        }

                    } else {

                        swal("", "No has seleccionado una imagen, inténtalo  nuevamente.", "error");
                        alertify.error("No has seleccionado una imagen inténtalo  nuevamente ");

                    }

                });

            });



            const agregarValNum = () => {
                let componente_numero = `<tr id="tr-valor-${contador_val_num}">
                            <td></td>
                            <td><input type="number" name="valor[]" class="form-control form-control-sm" min="0" step="1"  required></td>
                            <td><div class="btn btn-sm" onclick="eliminarValNum(${contador_val_num})" data-toggle='toltip' title="Elimminar valor"><img src="/img/icono-eliminar.png" class="button-style-icon"></div></td>
                        </tr>`;
                $("#tbl-valores tbody").append(componente_numero);
                contador_val_num++;
            }

            const eliminarValNum = numeroitem => {

                contador_val_num--;
                $(`#tr-valor-${numeroitem}`).remove();
                if (contador_val_num == 0) {
                    contador_val_num = 0;
                }
            }

            const agregaValores = componente => {

                $("#tbl-valores tbody").append(componente);
                $("#tr-val-file").addClass('d-none');
                $('#tr-info-file').addClass('d-none');
            }
            const addDocumentos = () => {

                let agregarItems = `<tr id="item${contador_items}" >
                       
                            <td><input type="text" name="descripcion[]" class="form-control" placeholder="Ingresa una descripción" required ></td>
                            <td><input type="file" name="documentos[]" class="form-control" required></td>
                            <td><div class="btn btn-sm"  onclick="eliminarItems(${contador_items})" ><img src="/img/icono-eliminar.png" class="button-style-icon"></div></td>
                        </tr>`;
                contador_items++;
                $("#guardar_doc").removeClass('d-none');
                $("#tblinfo tbody").append(agregarItems);

            }
            const eliminarItems = (index) => {

                $("#item" + index).remove();
                contador_items--;
                if (contPreguntas == 0) {
                    contPreguntas = 0;

                    $("#guardar_doc").addClass('d-none');
                }

            }
        </script>