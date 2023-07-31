<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">
        
        @include('includes.header',['title'=>'Captura de incidencias '.$periodo->nombre_periodo,
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'bandeja'])
        
        <div class="row">
            <div align="left" class="col-lg-3">
                <button type="button" class="button-style mt-1" data-toggle="modal" data-target="#importarPrenominaModal" title="Importar"> <img src="{{asset('img/icono-importar.png')}}" class="w-15px mb-1"> Importar</button>
                <a href="{{route('procesos.periodos.nomina.prenomina.exportar',Crypt::encrypt($periodo->id))}}"><button type="button" class="button-style mt-1" title="Exportar">Exportar <img src="{{asset('img/icono-exportar.png')}}" class="w-15px mb-1"></button></a>
            </div>
            <div align="right" class="col-lg-9">
                <select id="departamento" class="input-style-custom select-clase w-px-250 right">
                    <option value="">Todos los departamentos</option>
                    @foreach ($departamentos as $departamento)
                    <option value="{{$departamento->id}}">{{$departamento->nombre}}</option>
                    @endforeach
                </select>
                <input type="text" id="nombre" class="form-control input-style-custom ml-2 w-px-250 right" placeholder="Filtrar por nombre de  empleado">
                <input type="number" id="id" class="form-control input-style-custom ml-2 w-px-250 right" placeholder="#ID">
                @if($tiene_sedes)
                <select id="sede" class="form-control input-style-custom ml-2 w-px-250 right">
                    <option value="">TODOS</option>
                    @foreach ($sedes as $sede)
                    <option value="{{$sede->id}}">{{$sede->nombre}}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
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
        <br>
        <!--<div class="article border">-->
        <div class="card col-md-12 border-r">
            <div class="col-md-12 ml">
                <table class="empleadostbl" id="empleadostbl" cellspacing="0" style="border-collapse:collapse;">
                    <tr class="GridViewScrollHeader">
                        <th class="text-center font-weight-bold">Nombre</th>
                        <th class="text-center font-weight-bold">Opciones</th>
                        <th class="text-center font-weight-bold">ID</th>
                        <th class="text-center font-weight-bold">No. Emp.</th>
                        @if(array_key_exists('dias_imss', Session::get('usuarioPermisos')) && $dias_imss == 1)
                            <th scope="col" class="text-center text-center font-weight-bold">Días imss</th>
                        @endif
                        @foreach ($columnas as $col)
                            @php $string = strtoupper($col->nombre_concepto) @endphp
                            <th scope="col" class="text-nowrap text-center font-weight-bold">{{ucfirst(Str::lower($string))}}</th>
                        @endforeach
                    </tr>
                    @foreach ($empleados as $empleado)
                        @if (isset($valores_rutinas_empleados[$empleado->id]))
                            <tr class="GridViewScrollItem content" id="{{$empleado->id}}" data-id="{{$empleado->id}}" data-nombre="{{$empleado->nombre_completo}}" data-departamento="{{$empleado->id_departamento}}" data-sede="{{$empleado->sede}}">
                                <input type="hidden" name="id_empleado" value="{{$empleado->id}}">
                                <input type="hidden" name="id_periodo" value="{{$periodo->id}}">
                                <td style="font-size: 12px; ">{{strtoupper($empleado->nombre_completo)}}</td>
                                <td>
                                    <button type="button" class="btn button-style-custom guardarInfoEmpleado btn-sm" data-id_empleado="{{ $empleado->id }}">Guardar</button>
                                </td>
                                <td class="text-center">{{$empleado->id}}</td>
                                <td class="text-center">{{$empleado->numero_empleado}}</td>
                                @if( array_key_exists('dias_imss', Session::get('usuarioPermisos')) && $dias_imss == 1)
                                    <td>
                                        <input type="number" name="dias_imss" id="dias_imss" class="form-control form-control-sm" value="{{$valores_rutinas_empleados[$empleado->id]->dias_imss}}" min="0">
                                    </td>
                                @endif
                                @foreach ($columnas as $col)
                                    <td>
                                        @php $col_valor = "valor".$col->id; @endphp
                                        <input type="number" name="{{$col_valor}}" id="{{$col_valor}}" class="form-control form-control-sm" value="{{$valores_rutinas_empleados[$empleado->id]->$col_valor}}" min="0">
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
            <br>
        </div>
    </div>
    @include('includes.footer')
    @include('parametria.periodos-nomina.modals.importar-prenomina-modal')
    <script src="{{asset('js/gridviewscroll.js')}}" type="text/javascript"></script>
    <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
    <!-- Cambiar idioma de parsley -->
    <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{asset('js/datapicker-es.js')}}"></script>
    <script src="{{asset('js/helper.js')}}"></script>
    <script>
        var gridViewScroll = null;
        var options = new GridViewScrollOptions();
        options.elementID = "empleadostbl";
        options.width = '100%';
        options.height = '800px';
        options.freezeColumn = true;
        options.freezeFooter = false;
        options.freezeColumnCssClass = "GridViewScrollItemFreeze";
        options.freezeColumnCount = 2;

        gridViewScroll = new GridViewScroll(options);
        gridViewScroll.enhance();

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $(function() {
            $('#departamento').select2();

            $('#nombre').change(function() {
                nombre = $(this).val().toUpperCase();
                buscar(nombre, 'nombre');
            });

            $('#departamento').change(function() {
                departamento = $(this).val();
                buscar(departamento, 'departamento');
            });

            $('#id').change(function() {
                id = $(this).val();
                buscar(id, 'id');
            });

            $(document).on('change', 'input[type="file"]', function() {
                let fileName = this.files[0].name;

                if (fileName != "") {
                    let ext_archivo = fileName.split('.').pop();
                    ext_archivo = ext_archivo.toLowerCase();
                    let extension = ['xlsx', 'xls'];

                    if (!extension.includes(ext_archivo)) {
                        this.value = '';
                        swal("El archivo no es valido , intentalo nuevamente !", {
                            icon: "error",
                        });
                    }
                } else {
                    swal("No se a seleccionado ningún archivo !", {
                        icon: "error",
                    });
                }
            });

            $(".importar").on('click', function() {
                let form = $("#form");
                if (form.parsley().isValid()) {
                    swal({
                        title: `Aviso Importante`,
                        text: 'Al importar esta información se borrarán "TODOS" los datos existentes siendo sustituidos por los nuevos. ¿Deseas continuar?',
                        icon: "warning",
                        buttons: ["Cancelar", true],
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {

                            $(this).text('Espere...');
                            $(this).prop('disabled', true);
                            form.submit();
                        }
                    });
                } else {
                    form.parsley().validate();
                }
            });

            function buscar(valorABuscar, campo) {

                if (valorABuscar.trim() == '') {

                    $("#empleadostbl tr:not(:eq(0)), #empleadostbl_Content_Freeze_Grid tr:not(:eq(0))").show();

                } else {

                    $("#empleadostbl tr, #empleadostbl_Content_Freeze_Grid tr").hide();
                    $("#empleadostbl tr, #empleadostbl_Content_Freeze_Grid tr").each(function() {

                        valor = $(this).data(campo) + '';
                        if (valor.indexOf(valorABuscar) > -1) {

                            $(this).show();
                            id = $(this).attr('id');

                            // Re-dimensionar celdas
                            $('#empleadostbl_Header_Fixed_Grid tr th').each(function() {
                                // console.log($(this).innerWidth());
                                // console.log($(this).index());
                                // console.log(id);

                                $("#empleadostbl").css('width', $('#empleadostbl_Header_Fixed_Grid').innerWidth());
                                $("#empleadostbl tr#" + id + " td:eq(" + $(this).index() + ")").css('width', $(this).innerWidth());

                                if ($(this).index() <= 1) {
                                    $("#empleadostbl_Content_Freeze_Grid tr#" + id + " td:eq(" + $(this).index() + ")").css('width', $(this).innerWidth());
                                }

                            });
                        }
                    });
                }
            }

            // Guardar click
            $('.guardarInfoEmpleado').on('click', function() {
                let button = $(this);
                button.text('Espere...');
                button.prop('disabled', true);

                let id_empleado = $(this).data('id_empleado');
                let data = {
                    '_token': CSRF_TOKEN
                };

                $('#empleadostbl tr#' + id_empleado + ' input').each(function() {
                    data[$(this).attr('name')] = $(this).val();
                });

                $.ajax({
                    type: "POST",
                    url: "{{route('procesos.periodos.nomina.prenomina.empleado')}}",
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.respuesta == '1') {
                            alertify.success('Datos guardados.');
                            button.prop('disabled', false);
                            button.text('Guardar');
                        } else {
                            swal("Ocurrió un error al actualizar el usuario. Intente nuevamente.", {
                                icon: "error",
                            });
                        }
                    }
                });
            });

            @if($confirmar_incidencias == 1)
                @if($validacion_incidencias['estatus'] == 1)
                    $("#panel-btn").append('<button type="button" class="btn button-style-custom confirmar btn-sm" data-correo="{{Auth::user()->email}}" data-jefe="{{Auth::user()->email_jefe}}" data-periodo="{{$periodo->id}}" data-id="1">CONFIRMAR</button>');
                @elseif($validacion_incidencias['estatus'] == 2)
                    $("#panel-btn").append('<button type="btn button-style-custom confirmar btn-sm" data-correo="{{Auth::user()->email}}" data-jefe="{{Auth::user()->email_jefe}}" data-periodo="{{$periodo->id}}" data-id="2">RATIFICAR</button>');
                @elseif($validacion_incidencias['estatus'] == 3)
                    $("#panel-btn").append('<button type="button" class="btn button-style-custom confirmar btn-sm" data-correo="{{Auth::user()->email}}" data-jefe="{{Auth::user()->email_jefe}}" data-periodo="{{$periodo->id}}" data-id="3">VERIFICAR</button>');
                @endif

                @if($validacion_incidencias['bloqueo'] == 1)
                    $(".guardarInfoEmpleado, #panel-btn").remove();
                    $("#empleadostbl input[type=number]").attr("readonly", true);
                @endif
            @endif

            $(".confirmar").off().on("click", function() {

                let correo = $(this).data('correo');
                let correo_jefe = $(this).data('jefe');
                let periodo = $(this).data('periodo');
                let operacion = $(this).data('id');

                swal({
                    title: `¿Está seguro de enviar confirmación?`,
                    text: " Si lo hace, ya no podrá realizar cambios..!",
                    icon: "warning",
                    buttons: ["Cancelar", true],
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        let data = {
                            '_token': $('meta[name="csrf-token"]').attr('content'),
                            'periodo': periodo,
                            'correo': correo,
                            'operacion': operacion,
                            'correo_jefe': correo_jefe,
                            'tipo': 1
                        };

                        let url = "{{route('procesos.periodos.nomina.prenomina.confirmar')}}";

                        $.ajax({
                            type: "POST",
                            url: url,
                            data: data,
                            dataType: 'JSON',
                            success: function(response) {
                                if (response.respuesta == 1) {

                                    swal("Datos generados  correctamente!", {
                                        icon: "success",
                                    });

                                    $(".guardarInfoEmpleado, #panel-btn").remove();
                                    $("#empleadostbl input[type=number]").attr("readonly", true);

                                } else {

                                    swal("Error al realizar la acción favor de contactar a tu administrador!", {
                                        icon: "error",
                                    });
                                }
                                return true;
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                swal("Ocurrió un error al cargar los datos de la actividad. Intente nuevamente.", {
                                    icon: "error",
                                });
                            }
                        });
                    }
                });

            });
        });
    </script>
</body>

</html>