@extends('layouts.principal')
@section('tituloPagina', "Ordenes de compra")
@section('content')
    <div class="row d-flex justify-content-end">
        <div class="col-2 my-3 text-right px-1">
        <label for="base_autofacturador" class="my-0" style="line-height: 38px;">Te encuentras en: </label>
        </div>
        <div class="col-3 my-3 px-0">
            <select  name="base_autofacturador" id="base_autofacturador"  class="form-control" style="width: 100%" >
                <option value="" disabled selected>Seleccione una empresa</option>
            </select>
        </div>
    </div>
    <div id="myGroup">
        <div class="row mb-4">
                <button class="btn btn-warning text-white mr-3" data-toggle="collapse" data-target="#solicitudes-pendientes"
                        role="button" aria-expanded="true" aria-controls="solicitudes-pendientes">OC pendientes
                </button>
                <button class="btn btn-warning text-white mr-3" type="button" data-toggle="collapse"
                        data-target="#solicitudes-realizadas" role="button" aria-expanded="true"
                        aria-controls="solicitudes-realizadas">OC facturadas
                </button>

                @if(Auth::user()->timbrar)
                    <button class="btn btn-warning text-white mr-3" type="button" data-toggle="collapse"
                            data-target="#solicitudes-rechazadas" role="button" aria-expanded="true"
                            aria-controls="solicitudes-rechazadas">OC Rechazadas y pendientes
                    </button>
                    <button class="btn btn-warning text-white mr-3" type="button" data-toggle="collapse"
                            data-target="#solicitudes-descargar" role="button" aria-expanded="true"
                            aria-controls="solicitudes-descargar">Descarga resumen facturas
                    </button>
                    <button class="btn btn-warning text-white mr-3" type="button" data-toggle="collapse"
                            data-target="#cfdi-descargar" role="button" aria-expanded="true"
                            aria-controls="cfdi-descargar">Descarga masiva XML y PDF
                    </button>
                @endif

        </div>
        <div class="accordion-group">
            <div class="collapse show" id="solicitudes-pendientes">
                <div class="row">
                    <div class="col-12">
                        <div class="article border p-5">
                            <div class="row">
                                <h3 class="col-9">Ordenes pendientes</h3>
                                <div class="col-3">
                                    <input class="form-control" style="" type="text" placeholder="Buscar" id="busquesaGtable_cfdi">
                                </div>
                            </div>
                            <table class="table w-100" id="table-cfdi">
                                <thead>
                                <tr>
                                    <th>Folio <br><small>Fecha creación OC</small></th>
                                    <th>Cliente</th>
                                    <th>Emisor</th>
                                    <th>Receptor</th>
                                    <th>Total</th>
                                    <th>Última mod.</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            <div class="collapse" id="solicitudes-realizadas">
                <div class="col-12">
                    <div class="article border">
                        <div class="row">
                            <h3 class="col-9">Ordenes facturadas</h3>
                            <div class="col-3">
                                <input class="form-control" type="text"placeholder="Buscar" id="busquesaGtable_cfdi_emitidos">
                            </div>
                        </div>
                        <table class="table w-100" id="table-cfdi-emitidos">
                            <thead>
                            <tr>
                                <th>Folio <br><small>Fecha creación OC</small></th>
                                <th>Cliente</th>
                                <th>Emisor</th>
                                <th>Receptor</th>
                                <th>Total</th>
                                <th>Última mod.</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse" id="solicitudes-rechazadas">

                <div class="row">
                    <div class="col-12">
                        <div class="article border">
                            <div class="row">
                                <h3 class="col-9">Ordenes Rechazadas</h3>
                                <div class="col-3">
                                    <input class="form-control" type="text"placeholder="Buscar" id="busquesaGtable_cfdi_cancelados">
                                </div>
                            </div>
                            <table class="table w-100" id="table-cfdi-cancelados">
                                <thead>
                                <tr>
                                    <th>Folio <br><small>Fecha creación OC</small></th>
                                    <th>Emisor</th>
                                    <th>Receptor</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="collapse" id="solicitudes-descargar">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="article border p-5">
                            <h3>Resumen de facturación</h3>
                            <div class="card-body">
                                @foreach ($errors->get('nada') as $error)
                                    <small class="text-danger">{{ $error }}</small>
                                @endforeach

                                <form action="{{route('autofacturador.administracion.downloadCompletados')}}"
                                      method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col">
                                            <label for="">Filtro resumen de CFDI emitidos:</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="cantidad">Fecha inicio</label>
                                            <input class="form-control" type="date" name="date_inicio" id="date-inicio"
                                                   required>
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="banco">Fecha fin</label>
                                            <input class="form-control" type="date" name="date_final" id=date-final
                                                   required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="tipo_timbrado_xls">Filtrar por...</label>
                                            <select class="form-control" name="tipo_timbrado_xls" id="tipo_timbrado_xls" data-tipo="xls" onchange="tipoTimbradoObtener(this)">
                                                <option value="" selected>Todas</option>
                                                <option value="Emisor">Emisor</option>
                                                <option value="Receptor">Receptor</option>
                                                <option value="Cliente">Cliente</option>
                                                <option value="Vendedor">Vendedor</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="canceladas_xls">Incluir las Canceladas</label>
                                            <select class="form-control" name="canceladas_xls" id="canceladas_xls">
                                                <option value=0>No</option>
                                                <option value=1>Si</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-12" id="tipo_obtener_xls"></div>
                                    </div>                                    
                                    <button type="submit" class="btn btn-warning text-white" id="descargar-factura">Descargar Resumen</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="collapse" id="cfdi-descargar">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="article border p-5">
                            <h3>Descarga masiva XML y PDF</h3>
                            <div class="card-body">
                                @foreach ($errors->get('cfdi_dato') as $error)
                                    <small class="text-danger">{{ $error }}</small>
                                @endforeach

                                <form action="{{route('autofacturador.administracion.downloadCfdiXml')}}"method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col">
                                            <label for="">Filtro de descarga:</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="cantidad">Fecha inicio</label>
                                            <input class="form-control" type="date" name="date_inicio" id="date-inicio"
                                                   required>
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="banco">Fecha fin</label>
                                            <input class="form-control" type="date" name="date_final" id=date-final
                                                   required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="tipo_timbrado">Selecciona del</label>
                                            <select class="form-control" name="tipo_timbrado" id="tipo_timbrado" data-tipo="xml" onchange="tipoTimbradoObtener(this)">
                                                <option value="" selected>Escoja una Opcion</option>
                                                <option value="Emisor">Emisor</option>
                                                <option value="Receptor">Receptor</option>
                                                <option value="Cliente">Cliente</option>
                                                <option value="Vendedor">Vendedor</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="canceladas">Incluir las Canceladas</label>
                                            <select class="form-control" name="canceladas" id="canceladas">
                                                <option value=0>No</option>
                                                <option value=1>Si</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div id="tipo_obtener_xml" class="form-group col-12"></div>
                                    </div>

                                    <button type="submit" class="btn btn-warning text-white" id="descargar-xml">
                                        Descargar archivos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    @include('autofacturador.administrador.modal_cfdi')
@endsection

@push('css')
    <style>
        .custom-file-input ~ .custom-file-label::after {
            content: "Elegir";
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

        .table-emitidos-actions-td{
            min-width: 160px;
        }

        .table-cfdis-emitidos-td-folios{
            max-width: 60px;
        }

        .table-cfdis-pendientes-td-folios{
            max-width: 120px;
        }

        .max-content{
            max-width: 50px;
        }

        @media (min-width: 1200px){
            .container-xl, .container-lg, .container-md, .container-sm, .container {
                max-width: 1600px;
            }
        }

        .top-line-black {
            width: 19%;
        }

        table.dataTable thead th, table.dataTable thead td {
            vertical-align: bottom;
            border-bottom: 1px solid #dee2e6 !important;
        }

        .forma-pago-input .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .forma-pago-input .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }

        .forma-pago-input .select2-container .select2-selection--single {
            height: 38px;
        }

        .select2-container .select2-selection--single {
            height: 38px;
        }

    </style>
@endpush

@push('scripts')
    <script>

        $('#table-cfdi-emitidos thead tr').clone(true).addClass('filters').appendTo('#table-cfdi-emitidos thead');

        baseAutofacturador();

        function submitForm() {
            const formElement = document.querySelector('#modal .modal-body form');
            const formData = new FormData(formElement);

            document.querySelector('#modal button.btn.btn-warning').disabled = true;
            fetch(formElement.action, {
                method: 'post',
                body: formData,
            }).then(res => res.json())
                .then(res => {
                    if (!res.status) 
                        alertify.alert('Error', res.mensaje);
                    modal.modal('hide')
                    table_cfdi.ajax.reload();
                    table_cfdi_emitidos.ajax.reload();
                    table_cfdi_cancelados.ajax.reload();
                });
        }

        function baseAutofacturador() {
            return fetch('{{ route('autofacturador.getRelBaseAutofacturador') }}')
                .then(response => response.json())
                .then(response => {
                    $('#base_autofacturador').select2({
                        data: response.clientes.map((data) => {
                            return { text: data.nombre, id: data.id}
                        })
                    }).on("select2:select", function(e) {
                        var url = "{{route('sistema.usuarios.addUrelpdateUsuario')}}"
                        let json=JSON.stringify(e.params);
                        $.ajax({type: "POST",url: url,headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            data: {datos:JSON.parse(json)},
                            dataType: 'JSON',
                            success: function (response) {
                                if (response.ok == 1) {location.reload();}
                                else {alertify.alert('Error', 'Ocurrió un error al actualizar el usuario. Intente nuevamente.');}
                            }
                        });
                    });
                    $('#base_autofacturador').val(response.base_actual).trigger('change');
                });
        }

        function cancelarCFDI(el, id){
            alertify.confirm('Confirme cancelar el CFDI',
                function(){
                    el.disabled = true;
                    fetch('{{ route('autofacturador.administracion.cancelarTimbre') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            "X-CSRF-Token": $('input[name="_token"]').val()
                        },
                        body: JSON.stringify({id : id})
                    }).then(res => res.json())
                        .then(res => {
                            alertify.alert(res.mensaje);
                            table_cfdi_emitidos.ajax.reload();
                        });
                });
        }

        function tipoTimbradoObtener(el){
            let html = `
                <div class="form-group">
                    <label for="timbre_${el.value}">Seleccione ${el.value} (uno o varios)</label>
                    <select class="form-control" id="timbre_${el.value}" name="timbre_${el.value}[]" multiple>
                    </select>
                </div>`;
            let tipo = el.value;

            if(tipo){
                fetchgetRegistroAdmin(tipo);
                document.querySelector('#tipo_obtener_'+el.dataset.tipo).innerHTML = html;
            }else{
                document.querySelector('#tipo_obtener_'+el.dataset.tipo).innerHTML = '';
            }
        }

        function fetchgetRegistroAdmin(tipo) {
            fetch('{{ route('autofacturador.administracion.getCFDITimbrado')}}/' + tipo)
                .then(response => response.json())
                .then(response => {
                    $(`#timbre_${tipo}`).select2({
                        data: $.map(response, function (item) {
                            return {
                                id: item.code,
                                text: item.text
                            }
                        }),
                    });
                });
        }

        const table_cfdi = $('#table-cfdi').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.administracion.getInvoices') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            pageLength : 50,
            columns: [
                {
                    data: 'folio',
                    className: 'table-cfdis-pendientes-td-folios',
                },
                {data: 'nombre_completo'},
                {data: 'razon_social'},
                {data: 'receptor_nombre'},
                {
                    data: 'total',
                    type: 'num-fmt',
                    render: function (data, type, row, meta) {
                        return '$' + Intl.NumberFormat('es-MX').format(Number(row.total).toFixed(2));
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
                            case '1': estado = 'En espera de aprobación'; break;
                            case '2': estado = 'Aprobado y espera de ser timbrado'; break;
                            case '0': estado = 'CFDI cancelado'; break;
                        }
                        return estado;
                    }
                }, {
                    data: 'acciones',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return `<button class='btn mx-2 text-white' type="button" onClick="verFactura(${row.id})" data-toggle="tooltip" data-placement="top" title="Ver"><span class="ver-icon button-style-icon"></span></button>`;
                    }
                },
            ],
            order: [[0, 'desc']],
        });

        const table_cfdi_cancelados = $('#table-cfdi-cancelados').DataTable({
            lengthChange: false,
            pageLength : 50,
            ajax: "{{ route('autofacturador.getInvoicesCancelados') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {
                    data: 'folio',
                    className: 'table-cfdis-pendientes-td-folios',
                },
                {data: 'razon_social'},
                {data: 'receptor_nombre'},
                {
                    data: 'total',
                    type: 'num-fmt',
                    render: function (data, type, row, meta) {
                        return '$' + Intl.NumberFormat('es-MX').format(Number(row.total).toFixed(2));
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
                                estado = `<span class="text-danger">${row.observaciones}</span>`; break;
                            case '1':
                                estado = 'En espera de aprobación'; break;
                            case '2':
                                estado = "Aprobado y en proceso de timbrado"; break;
                        }
                        return estado;
                    }
                }
            ],
            order: [[0, 'desc']],
        });

        const table_cfdi_emitidos = $('#table-cfdi-emitidos').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.administracion.getInvoicesFull') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            fixedHeader: true,
            orderCellsTop: true,
            pageLength : 50,
            initComplete: function () {
                var api = this.api();

                // For each column
                api.columns().eq(0).each(function (colIdx) {
                        // Set the header cell to contain the input element
                        var cell = $('.filters th').eq(
                            $(api.column(colIdx).header()).index()
                        );
                        var title = $(cell).text();

                        $(cell).html((colIdx != 6) ? '<input type="text" placeholder="' + title + '" style="width: 120px"/>' : '');

                        // On every keypress in this input
                        $('input', $('.filters th').eq($(api.column(colIdx).header()).index())).off('keyup change').on('change', function (e) {
                            // Get the search value
                            $(this).attr('title', $(this).val());
                            var regexr = '({search})'; //$(this).parents('th').find('select').val();

                            var cursorPosition = this.selectionStart;
                            // Search the column for that value
                            api.column(colIdx).search(
                                this.value != '' ? regexr.replace('{search}', '(((' + this.value + ')))') : '',
                                this.value != '',
                                this.value == ''
                            ).draw();
                        }).on('keyup', function (e) {
                            e.stopPropagation();
                            $(this).trigger('change');
                            $(this).focus()[0].setSelectionRange(cursorPosition, cursorPosition);
                        });
                    });
            },
            createdRow: function( row, data, dataIndex ) {
                if(data.estado == '99')
                $( row ).css('background-color', '#F0AD9F');
            },
            columns: [
                {
                    data: 'folio', 
                    className: 'table-cfdis-emitidos-td-folios'
                },
                {data: 'nombre_completo'},
                {data: 'razon_social'},
                {data: 'receptor_nombre'},
                {
                    data: 'total',
                    type: 'num-fmt',
                    className: 'max-content',
                    render: function (data, type, row, meta) {
                        return '$' + Intl.NumberFormat('es-MX').format(Number(row.total).toFixed(2));
                    }
                }, {
                    data: 'updated_at',
                    type: 'date',
                    render: function (data, type, row, meta) {
                        return new Date(row.updated_at).toLocaleString("es-MX", { hour12: true });
                    }
                }, {
                    data: 'acciones',
                    className:"table-emitidos-actions-td px-0" ,
                    orderable: false,
                    render: function (data, type, row, meta) {
                        let html = '';
                        const downloadRouteZip = '{{ route('autofacturador.downloadzipCfdi') }}/' + row.id;
                        switch (row.estado) {
                            case '3':
                                html = `<button class='btn mr-1 text-white' type="button" onClick="verFactura(${row.id})" data-toggle="tooltip" data-placement="top" title="Ver"><span class="ver-icon button-style-icon"></span></button>
                                        <button class='btn mr-1' type="button" onClick="cancelarCFDI(this, ${row.id})" data-toggle="tooltip" data-placement="top" title="Cancelar CFDI"><span class="cancelar-icon button-style-icon"></span></button>
                                        <a href="${downloadRouteZip}" class="btn" data-toggle="tooltip" data-placement="top" title="Cancelar CFDI"><span class="descargar-icon-black button-style-icon"></span></a>`;
                                break;
                            case '99':
                                html = `<button class='btn mx-2 text-white' type="button" onClick="verFactura(${row.id})" data-toggle="tooltip" data-placement="top" title="Ver"><span class="ver-icon button-style-icon"></span></button>
                                        <a href="${downloadRouteZip}" class="btn mr-2"><span class="descargar-icon-black button-style-icon"></span></a>`;
                        }
                        return html;
                    }
                },
            ],
            order: [[0, 'desc']],
        });

        $('#busquesaGtable_cfdi').on( 'keyup', function () {
            table_cfdi.search( this.value ).draw();
        });
        $('#busquesaGtable_cfdi_emitidos').on( 'keyup', function () {
            table_cfdi_emitidos .search( this.value ).draw();
        });
        $('#busquesaGtable_cfdi_cancelados').on( 'keyup', function () {
            table_cfdi_cancelados.search( this.value ).draw();
        });

        $('#myGroup').on('show.bs.collapse', '.collapse', function () {
            $('#myGroup').find('.collapse.show').collapse('hide');
        });

    </script>
@endpush
