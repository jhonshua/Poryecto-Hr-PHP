@extends('layouts.principal')
@section('tituloPagina', "Ordenes de Compra")
@section('content')

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    

    @if(Auth::user()->base_autofacturador==6)
        <div class="row my-3 d-flex justify-content-around">
            <button class="btn btn-warning-usuario text-white" onclick="datosFiscales()">Configurar datos fiscales</button>
            <a class="btn btn-warning-usuario text-white" href="{{ route('autofacturador.nuevaFactura') }}">Solicitar orden de compra</a>
        </div>
        <script type="text/javascript">
            //*/
            $('#iconNavbar_usuario').attr('src', '/img/icono-usuario-blanco.png');
            $('#iconNavbar_orden_compra').attr('src', '/img/icono-orden-compra-blanco.png');
            $('#iconNavbar_cerrar_sesion').attr('src', '/img/icono-cerrar-sesion-blanco.png');
            $('#iconNavbar_terminos').attr('src', '/img/icono-terminos-blanco.png');
            $('#iconNavbar_aviso').attr('src', '/img/icono-aviso-blanco.png');
            $('#logo_navbar').attr('src', '/img/elqueretano-lateral.png');
            $('#label_icon_terminos').css('color','text-white');
            //*/
        </script>
        @push('css')
            <style>
                ::-webkit-scrollbar-thumb {
                    background-color: #67140F;
                    border-radius: 20px;
                    border: 3px solid #67140F;
                    width: 10px;
                }

                .btn-warning-usuario {
                    color: #212529;
                    background-color: #67140F;
                    border-color: #67140F
                }

                .navigation {
                    width: 350px;
                    height: 100vh;
                    float: left;
                    position: fixed;
                    padding: 10px;
                    z-index: 2;
                    left: -315px;
                    transition: 0.7s;
                    font-size: 30px;
                    color: white;
                    background-color: #67140F;
                }

                .color-white{
                    color: white;
                }

                .icon-navbar{
                    border-color: white;
                    color: white;
                }

                .page-link {
                    z-index: 3;
                    color: #000;
                    background-color: #fff;
                    border-color: #67140F !important;
                }

                .btn-warning-usuario:hover {
                    color: #212529;
                    background-color: #e0a800;
                    border-color: #d39e00
                }

                .navbar-logout {
                    cursor: pointer;
                    color: white;
                    font-size: 0.5em;
                    font-weight: bold;
                    border: 2px #67140F solid;
                    background-color: #67140F;
                }
            </style>
        @endpush
    @else
        <div class="row my-3 d-flex justify-content-around">
            <button class="btn btn-warning text-white" onclick="datosFiscales()">Configurar datos fiscales</button>
            <a class="btn btn-warning text-white" href="{{ route('autofacturador.nuevaFactura') }}">Solicitar orden de compra</a>
        </div>
    @endif

    <div class="article border">
        <table class="table w-100" id="table-cfdi">
            <thead>
            <tr>
                <th scope="col" class="gone">Folio <br><small>Fecha creación OC</small></th>
                <th scope="col">Emisor</th>
                <th scope="col">Receptor</th>
                <th scope="col">Total</th>
                <th scope="col">Última mod.</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="modal" data-backdrop="static" tabindex="-1" aria-labelledby="modal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Datos fiscales</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer" id="modal_footer">
                    <a class='btn btn-success mx-2' type="button" id="edit_factura">Editar</a>
                    <button class='btn btn-success mx-2' type="button" id="comprobante">Comprobantes de pago</button>
                    <a href="" target="_blank" class="btn btn-secondary mr-2" id="btn_hijo"><i
                                class="fa fa-download"></i> ZIP CFDI</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @include('autofacturador.modal_datos_fiscales')
    @include('autofacturador.modalEditarFactura')
    @include('autofacturador.modalComprobante')

@endsection


@push('css')
    <style>
        .btn-warning.focus,.btn-warning:focus {
            color: #212529;
            background-color: #e0a800;
            border-color: #d39e00;
            box-shadow: 0 0 0 .2rem rgba(222,170,12,.5)
        }

        .btn-warning.disabled,.btn-warning:disabled {
            color: #212529;
            background-color: #67140F;
            border-color: #67140F
        }

        


        .cancel-icon {
            background-image: url("{{ asset('img/rectangle-xmark-solid.svg') }}");
            display: inline-block;
            width: 1.1em;
            height: 1.1em;
            vertical-align: middle;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100%;
            fill: white;
        }

        .table-emitidos-actions-td {
            min-width: 151px;
        }

        .table-cfdis-emitidos-td-folios {
            max-width: 60px;
        }

        .table-cfdis-pendientes-td-folios {
            max-width: 120px;
        }

        .max-content {
            max-width: 120px;
        }

        @media (min-width: 1200px) {
            .container-xl, .container-lg, .container-md, .container-sm, .container {
                max-width: 1600px;
            }
        }

        .select2-container .select2-selection--single {
            height: 38px;
        }

    </style>
@endpush

@push('scripts')
    <script>
        const modal_ver = $('#modal');
        const modal_editar = $('#modal_editar');
        const modal_comprobante = $('#modal_comprobante');
        const modal_ver_body = $('#modal .modal-body');
        const modal_editar_title = $('#modal_editar .modal-title');
        const modal_editar_body = $('#modal_editar .modal-body');
        const modal_editar_footer = $('#modal_editar .modal-footer');
        const tocken = '@csrf';

        var comprobantes_pago_datos = {
            'ultima_parcialidad' : 0,
            'importe_saldo_anterior' : 0,
            'importe_pagado': 0,
            'importe_saldo_insoluto': 0,
        };

        var comprobante = document.getElementById('comprobante_pago');

        $('#modal-datos-discales').on('hidden.bs.modal', function (event) {
            $('#form-datos-fiscales').html('')
        })

        const table = $('#table-cfdi').DataTable({
            destroy: true,
            lengthChange: false,
            ajax: "{{route('autofacturador.getInvoices')}}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            pageLength: 50,
            columns: [
                {
                    data: 'folio',
                    className: 'table-cfdis-pendientes-td-folios'
                },
                {data: 'razon_social'},
                {data: 'receptor_nombre'},
                {
                    data: 'total',
                    type: 'num-fmt',
                    className: 'max-content',
                    render: function (data, type, row, meta) {
                        return '$ ' + Intl.NumberFormat('en-US').format(Number(row.total).toFixed(2));
                    }
                }, {
                    data: 'updated_at',
                    type: 'date',
                    render: function (data, type, row, meta) {
                        return new Date(row.updated_at).toLocaleString("es-MX", { hour12: true });
                    }
                }, {
                    data: 'estado',
                    render: function (data, type, row, meta) {
                        let estado = '';
                        switch (row.estado) {
                            case '0':
                                let str = row.observaciones || 'Proceso de facturacion cancelado';
                                estado = `<span class="text-danger">${str}</span>`;
                                break;
                            case '1':
                                estado = 'En espera de aprobación';
                                break;
                            case '2':
                                estado = "Aprobado y espera de ser timbrado";
                                break;
                            case '3':
                                estado = '<span class="text-success">Orden de compra facturada</span>';
                                break;
                            case '99':
                                estado = '<span class="text-danger">CFDI cancelado</span>';
                                break
                        }
                        return estado;
                    }
                }, {
                    data: 'acciones',
                    className: 'table-emitidos-actions-td',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        let html = `<button class='btn mx-2 text-white' type="button" onClick="verFactura(${row.id})" data-toggle="tooltip" data-placement="top" title="Ver"><span class="ver-icon button-style-icon"></span></button>`;

                        switch (row.estado) {
                            case '1':
                                html += `<button class='btn mx-2' type="button" onClick="cancelarFactura(${row.id})" data-toggle="tooltip" data-placement="top" title="Cancelar proceso"><span class="cancelar-icon button-style-icon"></span></button>`;
                                break;
                            case '0':
                                let route = '{{ route('autofacturador.nuevaFactura') }}/' + row.id;
                                html = `<a class='btn mx-2' href="${route}" data-toggle="tooltip" data-placement="top" title="Editar"><span class="editar-icon button-style-icon"></span></a>
                                    <button class='btn mx-2' onClick="eliminarFactura(${row.id})" title="Eliminar">
                                         <span class="eliminar-icon button-style-icon"></span>
                                    </button>`;
                                break;
                            case '2':
                                let route_contrato = '{{ route('autofacturador.downloadContrato') }}';
                                if (row.contrato_nombre)
                                    html += `<a class="btn btn-secondary text-white" href="${route_contrato}/${row.id}" data-toggle="tooltip" data-placement="top" title="Descargar ZIP CFDI"><span class="descargar-icon button-style-icon"></span> Contrato</a>`;
                                break;
                        }
                        return html;
                    },
                },
            ],
            rowId: 'id',
            order: [[0, 'desc']],
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        function datosUsuariosRetorno() {
            const tocken = '@csrf';
            let html_form = `
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="agregarRetornoUser-tab" data-toggle="tab"
                                data-target="#agregarRetornoUser" type="button" role="tab" aria-controls="agregarRetornoUser"
                                aria-selected="true">Agregar Retorno
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="verRetornoUser-tab" data-toggle="tab" data-target="#verRetornoUser" type="button"
                                role="tab" aria-controls="verRetornoUser" aria-selected="false">Retornos Existentes
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="agregarRetornoUser" role="tabpanel"
                         aria-labelledby="agregarRetorno-tab">

                    <form method="post" id="form-retorno" enctype="multipart/form-data">
                    ${tocken}

                     <div class="row">
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Nombre:</label>
                                    <input class="form-control" type="text" id="nombre_retorno" name="nombre">
                                </div>
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Banco:</label>
                                    <input class="form-control" type="text" id="banco_retorno" name="banco">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Num. Cuenta:</label>
                                    <input class="form-control" type="number" id="num_cuenta_retorno" name="num_cuenta">
                                </div>
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Clave Interbancaria:</label>
                                    <input class="form-control" type="number" id="clave_interbancaria_retorno" name="clave_interbancaria">
                                </div>
                            </div>

                    <br>
                    <button type="button" class="btn btn-success" onclick="enviarUsuRetorno()">Enviar</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="verRetornoUser" role="tabpanel" aria-labelledby="verRetorno-tab">

                    <div class="row">
                        <div class="col">
                            <table class="table w-100" id="table-retorno-usuarios">
                                <thead class="thead-dark table-striped">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Banco</th>
                                    <th>Num Cuenta</th>
                                    <th>Clave Interbancaria</th>
                                    <th>Aciones</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>`;
            modal_editar_title.html('Usuarios Retorno');
            modal_editar_body.html(html_form);
            modal_editar.modal('show');

            const table_retorno_usuarios = $('#table-retorno-usuarios').DataTable({
                destroy: true,
                lengthChange: false,
                ajax: '{{ route('autofacturador.catalogos')}}/datos-retorno-usuario-table',
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                columns: [
                    {data: 'nombre'},
                    {
                        data: 'banco',
                        render: function (data, type, row, meta) {
                            let html = ``;
                            if (data) {
                                html = `${data}`;
                            } else {
                                html = `Sin Dato`;
                            }
                            return html;
                        }
                    },
                    {
                        data: 'num_cuenta',
                        render: function (data, type, row, meta) {
                            let html = ``;
                            if (data) {
                                html = `${data}`;
                            } else {
                                html = `Sin Dato`;
                            }
                            return html;
                        }
                    },
                    {
                        data: 'clave_interbancaria',
                        render: function (data, type, row, meta) {
                            let html = ``;
                            if (data) {
                                html = `${data}`;
                            } else {
                                html = `Sin Dato`;
                            }
                            return html;
                        }
                    },
                    {
                        data: 'Acciones',
                        render: function (data, type, row, meta) {
                            return `<button type="button" class="btn btn-success" onclick="editarUserRetorno(${row.id})" id="editar-usu-retorno" >Editar</button>`;
                        }
                    }
                ],
                rowId: 'id',
                order: [[0, 'desc']],
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

        }

        function editarUserRetorno(id) {
            const tocken = '@csrf';
            $.ajax({
                url: 'autofactura/retorno/usuario/' + id,
                type: 'GET',
                success: function (res) {
                    let html = `
                          <form method="post" id="form-retorno" enctype="multipart/form-data">
                            ${tocken}
                        <input type="text" id="id_user_retorno" name="id_user_retorno" hidden>
                          <div class="row">
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Nombre:</label>
                                    <input class="form-control" type="text" id="nombre_retorno" name="nombre">
                                </div>
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Banco:</label>
                                    <input class="form-control" type="text" id="banco_retorno" name="banco">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Num. Cuenta:</label>
                                    <input class="form-control" type="number" id="num_cuenta_retorno" name="num_cuenta">
                                </div>
                                <div class="form-group col">
                                    <label for="message-text" class="col-form-label">Clave Interbancaria:</label>
                                    <input class="form-control" type="number" id="clave_interbancaria_retorno" name="clave_interbancaria">
                                </div>
                            </div>

                        <br>
                        <button type="button" class="btn btn-success" onclick="enviarUsuRetorno()">Enviar</button>
                        </form>
                    `;

                    modal_editar_title.html('Usuarios Retorno');
                    modal_editar_body.html(html);
                    modal_editar.modal('show');


                    $('#id_user_retorno').val(id);
                    $('#cantidad_retorno').val(res.cantidad);
                    $('#nombre_retorno').val(res.nombre);
                    $('#banco_retorno').val(res.banco);
                    $('#num_cuenta_retorno').val(res.num_cuenta);
                    $('#clave_interbancaria_retorno').val(res.clave_interbancaria);

                }
            });
        }

        function enviarUsuRetorno() {
            $.ajax({
                url: 'autofactura/retorno/usuario',
                type: 'POST',
                data: $('#form-retorno').serialize(),
                success: function (respuesta) {
                    modal_editar.modal('hide');
                }
            });
        }

        function validar_correo(correo = '') {
            const expresion = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            let valor = correo.match(expresion);
            return valor;
        }

        function eliminarFactura(id) {
            alertify.confirm('Confirme eliminar la orden de compra.',
                function () {
                    fetch('{{ route('autofacturador.eliminarOrden') }}', {
                            method: 'post',
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-Token": $('input[name="_token"]').val()
                            },
                            body: JSON.stringify({
                                id: id,
                            }),
                        }
                    ).then(res => res.json())
                        .then(res => {
                            if (res)
                                table.ajax.reload();
                        });
                });
        }

        function cancelarFactura(id) {
            alertify.confirm('Confirme cancelar el proceso de facturación.',
                function () {
                    fetch('{{ route('autofacturador.cancelarProceso') }}', {
                            method: 'post',
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-Token": $('input[name="_token"]').val()
                            },
                            body: JSON.stringify({
                                id: id,

                            }),
                        }
                    ).then(res => res.json())
                        .then(res => {
                            if (res)
                                table.ajax.reload();
                        });
                });


        }

        function verFactura(id) {
            alertify.message('Espere por favor, cargando...').delay(5);

            let button_zip = document.querySelector('#btn_hijo');
            button_zip.style.display = 'none';
            let button_edit = document.querySelector('#edit_factura');
            button_edit.style.display = 'block';
            let button_comprobante = document.querySelector('#comprobante');
            button_comprobante.style.display = 'block';

            let url = "{{route('autofacturador.getCfdi', '*ID*')}}";
            url = url.replace('*ID*', id);

            let estadoFactura = '';
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    estadoFactura = res.estado;

                    let conceptos = conceptos_html(res);
                    let factura_tabla = info_cfdi_html(id, res, conceptos);

                    switch (estadoFactura) {
                        case '3':
                            button_zip.href = '{{ route('autofacturador.downloadzipCfdi') }}/' + id;
                            button_zip.style.display = 'block';
                            button_edit.style.display = 'none';
                            break;
                        case '99':
                            button_zip.href = '{{ route('autofacturador.downloadzipCfdi') }}/' + id;
                            button_zip.style.display = 'block';
                            button_comision.style.display = 'none';
                            button_edit.style.display = 'none';
                            button_comprobante.style.display = 'none';
                            break;
                        case '2':
                            button_edit.style.display = 'none';
                            break;
                        default:
                            button_edit.setAttribute('href', '{{ route('autofacturador.nuevaFactura') }}/' + id)
                            button_edit.setAttribute("type", "button");
                            button_edit.setAttribute("class", "btn btn-success text-white");
                            button_edit.setAttribute("id", "edit_factura");
                    }

                    let btn_comprobante = document.getElementById('comprobante');

                    btn_comprobante.onclick = function () {
                        modal_comprobante.modal('show');

                        $('#id_cfdi').val(id);
                        let cant_pagada = 0;
                        res.comprobantes_pago.map((pago, index) => {
                            cant_pagada += Number(pago.cantidad);
                        });

                        comprobante.innerHTML = tabla_comprobantes_html();

                        tablaComprobante(id, cant_pagada, Number(res.total));
                        modal_ver.modal('hide');
                    };

                    modal_ver_body.html(factura_tabla);
                    modal_ver.modal('show');
                })
                .finally(()=>{
                alertify.message('Cargado con Exito');
            });
        }

        function fileValidation(nombre) {
            var filePath = nombre;
            var allowedExtensions = /(.jpg|.jpeg|.png|.pdf)$/i;
            if (!allowedExtensions.exec(filePath))
                return false;
            else
                return true;
        }

        function actualizaLabel(el) {
            let fileName = document.querySelector('#' + el.id).files[0].name;

            if (fileValidation(fileName)) {
                document.querySelector(`#form-comprobante label[for=${el.id}]`).innerText = fileName;
                const contenedor = document.getElementById("item_comprobante");
                const hijos = contenedor.children;
                const hijos_cont = hijos.length;
                document.querySelector(`#tipo_archivo`).value = fileName;
            } else {
                alertify.alert("Solo Archivos jpg, jpeg, png, pdf", function () {
                    alertify.message('OK');
                });
            }


        }

        //comprobante
        $(function () {
            // boton guardar
            $('#form-comprobante').submit(function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                $('#modal_comprobante .store_comprobante').attr('disabled', true).text('Espere...');
                $.ajax({
                    url: '{{route('autofacturador.storeComprobante')}}',
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        $("#form-comprobante")[0].reset();
                        table.ajax.reload();
                        modal_comprobante.modal('hide');
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
        });

        function opcionDefault(componente, texto, id) {
            $(`#${componente}`).val(id);
            $(`#${componente}`).trigger('change');
        }

        function enviar() {
            if ($('#clave-producto').val() == '' || $('#metodo-pagol').val() == '' || $('#forma-pago').val() == '' ||
                $('#descripcionl').val() == '' || $('#clave-unidad').val() == '' || $('#valor-unitario').val() == '' ||
                $('#importe').val() == '' || $('#total').val() == '') {
                return alertify
                    .alert('Llena todos los campos  ', function () {
                        alertify.message('OK');
                    });
            }

            $.ajax({
                url: 'autofactura/store',
                type: 'POST',
                data: $('#form-autofacturador-store').serialize(),
                success: function (respuesta) {
                    if (respuesta) {
                        modal_editar.modal('hide');
                        table.ajax.reload();
                    }
                }
            });
        }

        function validateSelects(selects) {
            let state = true;
            selects.forEach(select => {
                if (select.selectedIndex < 0) {
                    state = false;
                    $($("#" + select.id).select2("container")).addClass("is-invalid");
                }
            });
            return state;
        }

        function validateInputs(inputs) {
            let state = true;
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
                if (!input.value) {
                    state = false;
                    input.classList.add('is-invalid');
                }
            });
            return state;
        }

        //Evento teclado, solamente mayusculas
        function eventoTeclado(id_elemento) {
            const entradaInput = document.getElementById(id_elemento);
            entradaInput.addEventListener('keyup', cambiarMayusculas);
        }

        function cambiarMayusculas(elemento) {
            let texto = elemento.target.value;
            elemento.target.value = texto.toUpperCase();
        }

        // Return HTML text
        var tabla_comprobantes_html = () => {
            return `${ tocken }
                    <div class="row">
                        <div class="col">
                            <div class="article border">
                                <table class="table" id="table-comprobante">
                                    <thead>
                                    <tr>
                                        <th>Cantidad</th>
                                        <th>Tipo de pago</th>
                                        <th>Numero Pago</th>
                                        <th>Confirmaciones</th>
                                        <th>Comprobante</th>
                                        <th>Estado</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    `;
        }

        var conceptos_html = (response_cfdi) => {
            let html_conceptos = ''
            response_cfdi.conceptos.map(concepto => {
                let html = `<tr>
                            <td>${concepto.clave_prod}</td>
                            <td>${concepto.clave_unidad}</td>
                            <td>${concepto.descripcion}</td>
                            <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(concepto.valor_unitario))}</td>
                            <td>${Intl.NumberFormat('en-US').format(Number(concepto.cantidad))}</td>
                            <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(concepto.base))}</td>
                            <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(concepto.importe))}</td>
                            <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(parseFloat(concepto.importe) + parseFloat(concepto.base)))}</td>
                        </tr>`;
                return html;
            });

            return html_conceptos;
        };

        var info_cfdi_html = (id, response_cfdi, conceptos_html) => {
            let creo_oc = '', aprobo_oc = '', timbro_oc = '', cancelo_oc = '', reset = false;

            response_cfdi.logs.map((log, index) => {
                reset = (log.evento == '0' && index && !reset) ? true : false;
                if (!reset)
                    if (log.evento == 1 && creo_oc == '')
                        creo_oc = `Alta ${log.fecha_creacion} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 2 && aprobo_oc == '')
                        aprobo_oc = `Aprobado ${log.fecha_creacion} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 3 && timbro_oc == '')
                        timbro_oc = `Timbrado ${log.fecha_creacion} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 99 && cancelo_oc == '')
                        cancelo_oc = `Cancelado ${log.fecha_creacion} por ${log.usuarios.nombre_completo} `;
            });

            let relacion_cfdi='';

            if(response_cfdi.relacion_cfdi){
                relacion_cfdi=`CFDI Relacionado: ${response_cfdi.relacion_cfdi.uuid}<br>
                               Tipo de Relacion: ${response_cfdi.tipo_relacion}<br>`;
            }

            return `${tocken}
                    <input type="hidden" name="id" value="${id}">
                    <div class="card">
                        <div class="card-body">
                            <table class="w-100 table">
                                <tbody>
                                    <tr>
                                        <td width="55%">
                                            <b>${response_cfdi.emisora.razon_social}</b> <br>
                                            RFC: ${response_cfdi.emisora.rfc}  <br>
                                            Domicilio fiscal: ${response_cfdi.emisora.cp} <br>
                                            Regimen fiscal: ${response_cfdi.emisora.regimen_fiscal}
                                        </td>
                                        <td width="45%">
                                            Folio - Serie: ${response_cfdi.folio} - ${response_cfdi.serie} <br>
                                            Tipo comprobante: ${response_cfdi.tipo_comprobante || ''} <br>
                                            UUID:  ${response_cfdi.uuid || ''} <br>
                                            Certificado SAT: ${response_cfdi.certificado_sat || ''} <br>
                                            ${relacion_cfdi}
                                            Fecha de Expedición: ${response_cfdi.fecha_timbre || ''} <br>
                                            Método de pago: ${response_cfdi.metodo_pago} <br>
                                            Forma de pago: ${response_cfdi.forma_pago} <br>
                                            ${(response_cfdi.modificar_fecha) ? 'Fecha emisión: ' + response_cfdi.fecha : ''}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pt-2">
                                            <b>${response_cfdi.receptor_nombre}</b> <br>
                                            RFC: ${response_cfdi.receptor_rfc} <br>
                                            Domicilio fiscal: ${response_cfdi.receptor_domicilio} <br>
                                            Uso CFDI: ${response_cfdi.receptor_uso_cfdi} <br>
                                            Regimen fical: ${response_cfdi.receptor_regimen_fiscal}
                                        </td>
                                        <td class="align-self-center">
                                            Porcentaje de comisión: %${response_cfdi.comision} <br>
                                            Monto comisión: $ ${(response_cfdi.pagar_del == 'subtotal') ? (response_cfdi.comision * response_cfdi.subtotal / 100).toFixed(4) : (response_cfdi.comision * response_cfdi.total / 100).toFixed(4)} del ${response_cfdi.pagar_del}
                                        <br>
                                        <hr>
                                        ${creo_oc} <br>
                                        ${aprobo_oc} <br>
                                        ${timbro_oc} <br>
                                        ${cancelo_oc}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Subtotal: $${Intl.NumberFormat('en-US').format(Number(response_cfdi.subtotal))}</td>
                                        <td>Total: $${Intl.NumberFormat('en-US').format(Number(response_cfdi.total))}</td>
                                    </tr>
                                    <tr><td colspan="2"><table class="table mt-3 w-100">
                                                <thead>
                                                    <tr>
                                                        <th>Clave de P/S</th>
                                                        <th>Clave unidad</th>
                                                        <th>Descripción</th>
                                                        <th>Valor unitario</th>
                                                        <th>Cantidad</th>
                                                        <th>Importe</th>
                                                        <th>IVA</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${conceptos_html}
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        `;
        }

    </script>

@endpush