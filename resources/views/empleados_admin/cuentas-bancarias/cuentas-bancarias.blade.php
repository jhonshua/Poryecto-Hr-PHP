<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Cuentas bancarias',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png',
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
            <div class="row">
                <div class="col-lg-6">
                    <a href="{{route('cuentas.exportar')}}" class="button-style mb-3 mr-3" target="_blank">
                        <img src="/img/icono-exportar.png" class="button-style-icon"> Exportar</a>

                    <button class="button-style mb-3 mr-3" alt="Exportar asistencias" title="Exportar asistencias" data-toggle="modal" data-target="#importarModal">
                        <img src="/img/icono-importar.png" class="button-style-icon">Importar
                    </button>
                </div>
                <div class="col-lg-6">

                    <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
                </div>
            </div>
        </div>



        <div class="article border">
            <table class="table w-200 cuentas" id="tablaCuentas">
                <thead class="text-center">
                    <tr>
                        <th style="width: 40px;">ID</th>
                        <th># empleado</th>
                        <th>Nombre</th>
                        <th>ID bancario</th>
                        <th>Banco</th>
                        <th>Cuenta 1</th>
                        <th>Cuenta 2</th>
                        <th>Cuenta 3</th>
                        <th>Clabe</th>
                        <th>Tipo de cuenta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empleados as $empleado)
                    <tr data-id="{{$empleado->id}}" data-rfc="{{$empleado->rfc}}" data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}">

                        <td> {{ $empleado->numero_empleado }} </td>

                        <td style="text-align: center;"> {{ $empleado->id }} </td>

                        <td> {{ $empleado->apaterno }} {{ $empleado->amaterno }} {{ $empleado->nombre }} </td>

                        <td>
                            <input type="text" id="id_bancario" class="form-control  input-style-custom" value="{{ $empleado->id_bancario }}" required>
                        </td>

                        <td>
                            <select class="form-control input-style-custom" id="id_banco" required>
                                @foreach ($bancos as $banco)
                                <option value="{{$banco->banco1}}" {{($banco->id == $empleado->id_banco) ? 'selected' : ''}}> {{ $banco->nombre }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="text" class="form-control input-style-custom" id="cuenta_bancaria" value="{{ $empleado->cuenta_bancaria }}" required>
                        </td>

                        <td>
                            <input type="text" class="form-control input-style-custom" id="cuenta_bancaria2" value="{{ $empleado->cuenta_bancaria2 }}" required>
                        </td>

                        <td>
                            <input type="text" class="form-control input-style-custom" id="cuenta_bancaria3" value="{{ $empleado->cuenta_bancaria3 }}" required>
                        </td>

                        <td>
                            <input type="text" class="form-control input-style-custom" id="clabe_interbancaria" value="{{ $empleado->clabe_interbancaria }}" required>
                        </td>

                        <td>
                            <select class="form-control input-style-custom" name="tipo_cuenta" required id="tipo_cuenta">
                                <option value="" {{("" == $empleado->tipo_cuenta) ? 'selected' : ''}}> Selecciona </option>
                                <option value="01" {{("01" == $empleado->tipo_cuenta) ? 'selected' : ''}}> CHEQUES </option>
                                <option value="03" {{("03" == $empleado->tipo_cuenta) ? 'selected' : ''}}> TARJETA DE DÉBITO </option>
                                <option value="40" {{("40" == $empleado->tipo_cuenta) ? 'selected' : ''}}> CLABE </option>
                            </select>
                        </td>

                        <td>
                            <div style="text-align: center;">
                                <a class="guardar text-center" data-toggle="tooltip" data-placement="right" title="Guardar" data-id="{{$empleado->id}}">
                                    <img src="{{asset('img/icono-guardar.png')}}" class="button-style-icon text-center"></a>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
        @include('includes.footer')
        @include('empleados_admin.cuentas-bancarias.cuentas-importar')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                let dataSrc = [];
                let table = $('#tablaCuentas').DataTable({
                    scrollCollapse: true,
                    "language": {
                        search: '',
                        searchPlaceholder: 'Buscar registros',
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    initComplete: function() {

                        let api = this.api();

                        api.cells('tr', [2]).every(function() {

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
                });
            });
        </script>

        <script>
            $(function() {

                $("td .guardar").click(function() {

                    btn = $(this);

                    id_bancario = $(this).parents('tr').find('#id_bancario').val();
                    id_banco = $(this).parents('tr').find('#id_banco').val();
                    cuenta_bancaria = $(this).parents('tr').find('#cuenta_bancaria').val();
                    cuenta_bancaria2 = $(this).parents('tr').find('#cuenta_bancaria2').val();
                    cuenta_bancaria3 = $(this).parents('tr').find('#cuenta_bancaria3').val();
                    clabe_interbancaria = $(this).parents('tr').find('#clabe_interbancaria').val();
                    tipo_cuenta = $(this).parents('tr').find('#tipo_cuenta').val();
                    id = $(this).data('id');

                    if (id_bancario.trim() == '' || id_banco == '' || cuenta_bancaria.trim() == '' || cuenta_bancaria2.trim() == '' || cuenta_bancaria3.trim() == '' || clabe_interbancaria.trim() == '' || tipo_cuenta == '') {
                        alertify.error('Todos los campos son requeridos. Por favor Verifique.');
                        return;
                    }

                    swal("Espere un momento, la información esta siendo procesada", {
                        icon: "success",
                        buttons: false,
                    });
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'id': id,
                        'id_bancario': id_bancario,
                        'id_banco': id_banco,
                        'cuenta_bancaria': cuenta_bancaria,
                        'cuenta_bancaria2': cuenta_bancaria2,
                        'cuenta_bancaria3': cuenta_bancaria3,
                        'clabe_interbancaria': clabe_interbancaria,
                        'tipo_cuenta': tipo_cuenta,
                        '_token': CSRF_TOKEN
                    }

                    var url = "{{route('cuentas.guardar')}}";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: 'JSON',
                        success: function(response) {
                            if (response.ok == 1) {
                                console.log(response.ok);
                                swal("Datos actualizados  correctamente!", {
                                    icon: "success",
                                });
                                window.location.href = "{{route('cuentas.ver')}}";
                            } else {
                                swal("Error al eliminar los datos comunicate con tu adminstrador!", {
                                    icon: "error",
                                });
                            }
                        },
                        error: function() {
                            swal("Error al eliminar los datos comunicate con tu admnistrador!", {
                                icon: "error",
                            });
                        }
                    }).done(function() {
                        btn.text('Guardar').prop('disabled', false);
                    });
                });
            });
        </script>