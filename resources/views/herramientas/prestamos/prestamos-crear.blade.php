<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')

    <div class="container">

        @include('includes.header',['title'=> 'Crear solicitud',
        'subtitle'=>'Herramientas', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'prestamos.tabla'])

        <div class="article border">
            <form action="{{ route('prestamos.guarda') }}" method="post" class="mt-4" id="prestamos_form" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="">Empresa:</label>
                        <input class="form-control" type="text" value="{{ $empresa['razon_social'] }}" readonly>
                    </div>
                    <div class="form-group  col-md-6">
                        <label for="">Tipo de prestamo:</label>
                        <input class="form-control" type="text" value="{{ $prestamo_seleccionado->nombre }}" name="nombrePrestamo" readonly>
                    </div>
                    {{-- <i class="mb-4">Descripción: {{ $prestamo_seleccionado->descripcion }}</i> --}}
                </div>
                <div class="form-row">
                    <div class="form-group  col-md-6">
                        <label for="">Fecha/Hora:</label>
                        <input class="form-control" type="text" value="{{ date('Y-m-d H:i:s')}}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Medio de contacto:</label>
                        <select name="medio_contacto" id="medio_contacto" class="form-control" required>
                            <option value="">Selecciona una opcion...</option>
                            <option value="email">Email</option>
                            <option value="telefono">Teléfono</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="">Amortización:</label>
                        @if($prestamo_seleccionado->tipo_solicitud==2)
                        <input type="text" class="form-control" name="amortizacion" placeholder="Ej.2593.22" onkeypress="return filterFloat(event,this);" required>
                        @else
                        <input type="disabled" class="form-control" name="amortizacion" placeholder="N/A" readonly>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Precio real:</label>
                        @if($prestamo_seleccionado->tipo_solicitud==2)
                        <input type="text" class="form-control" name="precio_real" placeholder="Ej.2593.22" onkeypress="return filterFloat(event,this);" required>
                        @else
                        <input type="disabled" class="form-control" name="precio_real" placeholder="N/A" readonly>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Empleado:</label>
                        <div>
                            <input type="text" list="empleados_" id="empleado_" name="empleado_" class="form-control" placeholder="Selecciona un empleado..." required />
                            <datalist id="empleados_"></datalist>
                        </div>
                    </div>
                </div>

                <div class="empleado-data d-none">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="">Fecha de ingreso:</label>
                            <input class="form-control" type="text" value="" id="fecha_ingreso" name="fecha_ingreso">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="">Email:</label>
                            <input class="form-control" type="text" value="" id="email" name="email">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="">Teléfono:</label>
                            <input class="form-control" type="text" value="" id="telefono" name="telefono">
                        </div>
                    </div>
                </div>
                <div class="requisitos d-none">
                    <div class="form-row col-md-12 my-lg-3">
                        <h3>Requisitos del prestamo: </h3>
                    </div>
                    <table class="table">
                        <tr>
                            <th>Nombre</th>
                            <th>Valor asignado</th>
                            <th>Cargar documentos</th>
                        </tr>
                        <tr>
                            <th>Notas:</th>
                            <td>{{ $prestamo_seleccionado->notas }}</td>
                            <td>Selecciona los documentos correspondientes con base a las especificaciones de cada campo</td>
                        </tr>
                        @foreach ($prestamo_seleccionado->requisitos as $req)
                        <tr>
                            @if($req->tipo == 'file')

                            <td class="d-none"><input type="text" name="requisito_id_file[]" value="{{Crypt::encrypt($req->id)}}"></td>
                            <td width="350">{{$req->nombre}}</td>
                            <td>{{$req->valor}}</td>
                            <td><input type="file" name="valores_file_{{$req->id}}[]" required data-ext="{{$req->valor}}" class="file-doc" multiple></td>
                            @else

                            <td class="d-none"><input type="text" name="requisito_id[]" value="{{Crypt::encrypt($req->id)}}"></td>
                            <td width="350">{{$req->nombre}}</td>
                            <td>{{$req->valor}}</td>
                            @switch($req->tipo)
                            @case('number')
                            <td>
                                <select name="valor[]" class="form-control form-control-sm" required>
                                    <option value="">Selecciona un {{strtolower($req->nombre)}} </option>
                                    @php $numeros = explode(",", $req->valor ) @endphp
                                    @foreach ($numeros as $numero)
                                    <option value="{{$numero}}">{{$numero}}</option>
                                    @endforeach
                                </select>
                            </td>
                            @break
                            @case('percentage')
                            <td>
                                <select name="valor[]" class="form-control form-control-sm" required>
                                    <option value="">Selecciona un {{strtolower($req->nombre)}} </option>
                                    @php $plazos = explode(",", $req->valor ) @endphp
                                    @foreach ($plazos as $plazo)
                                    <option value="{{$plazo}}">{{$plazo}}</option>
                                    @endforeach
                                </select>
                            </td>
                            @break
                            @case('date')
                            <td><input type="date" name="valor[]" value="{{date('Y-m-d')}}" class="form-control form-control-sm" required></td>
                            @break
                            @case('price')
                            <td>
                                <div class="input-group mb-3 input-group-sm">
                                    <div class="input-group-prepend ">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="valor[]" class="form-control form-control-sm inpt-precio" required>
                                </div>
                            </td>
                            @break
                            @case('info')
                            <td></td>
                            @break
                            @default
                            <td><input type="text" name="valor[]" class="form-control form-control-sm" required></td>
                            @endswitch
                            @endif
                        </tr>
                        @endforeach

                    </table>
                </div>
                <div class="form-row botones my-lg-5">
                    <button type="button" class="btn button-style btn-sm d-none mr-3 rechazar" data-toggle="modal" data-target="#notasModal">Rechazar prestamo</button>
                    <button type="submit" class=" btn button-style btn-sm d-none guardar">Crear prestamo</button>
                    <input type="hidden" name="tipo_solicitud" id="tipo_solicitud" value="{{ $prestamo_seleccionado->tipo_solicitud }}">
                    
                    <input type="hidden" name="empresa_base" id="empresa_base" value="{{ $data['base']}}">
                    <input type="hidden" name="empresa_id" id="empresa_id" value="{{ intval(str_ireplace('empresa00', '', $data['base'])) }}">
                                        <input type="hidden" name="prestamos_tipo_id" id="prestamos_tipo_id" value="{{ $data['tipo_prestamo'] }}">
                    <input type="hidden" name="usuario_id" id="usuario_id" value="{{ $idUsuario  }}">
                    <input type="hidden" name="empleado_id" id="empleado_id">
                    <input type="hidden" name="empleado" id="empleado">
                    <input type="hidden" name="estatus" id="estatus" value="1">
                </div>
            </form>
        </div>
        @include('includes.footer')
        @include('herramientas.prestamos.modals.rechazo-modal')

        <script src="{{asset('/js/moment/moment.js')}}"></script>
        <script src="{{asset('/js/moment/es.js')}}"></script>

        <script>
            let empleados = "";
            let antiguedad_meses = {{$prestamo_seleccionado->antiguedad_meses}};

            $(function() {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                data = {
                    'base': '{{$data['base']}}',
                    '_token': CSRF_TOKEN
                }
                $.ajax({
                    type: "post",
                    url: "{{route('prestamos.obtenerEmpleados')}}",
                    data: data,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.ok == 1) {
                            /* console.log(response.empleados); */
                            empleados = response.empleados;
                            empleados.forEach(empleado => {
                                $("#prestamos_form #empleados_").append(new Option(empleado.id + " - " + empleado.apaterno + " " + empleado.amaterno + " " + empleado.nombre));
                            });
                            $("#prestamos_form #empleados_").attr('disabled', false);

                            var detailRows = [];
                            var tabla;

                            tabla.on('click', 'tr td.details-control', function() {
                                var tr = $(this).closest('tr');
                                //console.log(tr);
                                var row = tabla.row(tr);

                                var idx = $.inArray(tr.attr('id'), detailRows);

                                if (row.child.isShown()) {
                                    tr.removeClass('details');
                                    row.child.hide();

                                    detailRows.splice(idx, 1);
                                } else {
                                    tr.addClass('details');
                                    row.child(format(row.data())).show();

                                    if (idx === -1) {
                                        detailRows.push(tr.attr('id'));
                                    }
                                }
                            });
                        } else {
                            swal("", "No se encontraron empleados. Verificar", "error");

                        }
                    }
                });

                $('#empleado_').focus(function() {
                    $(this).select();
                });

                /* Seleccionar empleado */
                $('#empleado_').change(function() {
                    var _empleado = $(this).val();
                    empleadoId = _empleado.split(' - ')[0];
                    empleadoNombre = _empleado.split(' - ')[1];

                    if (empleadoId <= 0) {
                        $('#fecha_ingreso').val('');
                        $('#email').val('');
                        $('#telefono').val('');
                        return;
                    }
                    const empleado = empleados.find(empleado => empleado.id == empleadoId);

                    $('#fecha_ingreso').val(empleado.fecha_antiguedad);
                    $('#email').val(empleado.correo);
                    $('#telefono').val("Movil: " + empleado.telefono_movil + "; Casa: " + empleado.telefono_casa);
                    $('.empleado-data').removeClass('d-none');

                    ingreso = empleado.fecha_antiguedad;
                    hoy = "{{ date('Y-m-d') }}";
                    antiguedad_empleado = moment(moment(hoy, "YYYY-MM-DD")).diff(moment(ingreso, "YYYY-MM-DD"), 'months', true);

                    if (antiguedad_empleado < antiguedad_meses) {
                        swal("", 'El empleado no es candidato para este proceso, ya que no cumple con la antiguedad necesaria. El empleado solo tiene ' + Math.floor(antiguedad_empleado) + " meses de antiguedad.", "error");

                        $('.rechazar').removeClass('d-none');
                        $('.guardar, .requisitos').addClass('d-none');
                        return;
                    } else {
                        $('#prestamos_form #empleado_id').val(empleadoId);
                        $('#prestamos_form #empleado').val(empleadoNombre);
                        $('.guardar, .requisitos').removeClass('d-none');
                        $('.rechazar').addClass('d-none');
                    }
                });

                $('#prestamos_form').submit(function() {
                    $('#prestamos_form .guardar').attr('disabled', true).text('Espere...');
                });

                $(".inpt-precio").on('input', function() {
                    this.value = this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');
                });

                $(document).on('change', 'inpout[type="file"]', function() {
                    if (this.files.length <= 2) {
                        for (let i = 0; this.files.length > i; i++) {
                            let fileName = this.files[i].name;
                            let fileSize = this.files[i].size;

                            if (fileSize > 20971520) {
                                swal("", "El archivo debe de contener 20 MB.", "error");
                                this.value = '';
                            } else {
                                let ext_archivo = fileName.split('.').pop();
                                let extension = $(this).data('ext');

                                extension = extension.split(',');

                                let exts = extension;

                                if (!exts.includes(ext_archivo)) {
                                    swal("", "El formato es incorrecto inténtalo  nuevamente.", "error");
                                    this.value = '';
                                }
                            }
                        }
                    } else {
                        swal("", "Solo se pueden anexar dos documentos, inténtalo  nuevamente.", "error");
                        this.value = '';
                    }
                });
            });

            function filterFloat(evt, input) {
                // Backspace = 8, Enter = 13, ‘0′ = 48, ‘9′ = 57, ‘.’ = 46, ‘-’ = 43
                var key = window.Event ? evt.which : evt.keyCode;
                var chark = String.fromCharCode(key);
                var tempValue = input.value + chark;
                if (key >= 48 && key <= 57) {
                    if (filter(tempValue) === false) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    if (key == 8 || key == 13 || key == 0) {
                        return true;
                    } else if (key == 46) {
                        if (filter(tempValue) === false) {
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                        return false;
                    }
                }
            }

            function filter(__val__) {
                var preg = /^([0-9]+\.?[0-9]{0,2})$/;
                if (preg.test(__val__) === true) {
                    return true;
                } else {
                    return false;
                }

            }
        </script>