<div class="modal fade" data-backdrop="static" id="modal_comprobante" tabindex="-1" aria-labelledby="modal"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comprobantes de pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="miComprobante-tab" data-toggle="tab"
                                data-target="#miComprobante" type="button" role="tab" aria-controls="miComprobante"
                                aria-selected="true">Comprobantes de pago
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agregar-comprobante-tab" data-toggle="tab" data-target="#agregar-comprobante" type="button"
                                role="tab" aria-controls="agregar" aria-selected="false">Agregar Comprobante
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="miComprobante" role="tabpanel"
                         aria-labelledby="miComprobante-tab">
                        <div id="comprobante_pago"></div>


                    </div>
                    <div class="tab-pane fade" id="agregar-comprobante" role="tabpanel" aria-labelledby="agregar-comprobante-tab">
                        <div class="card">
                            <div class="card-body">
                                <form method="post" action="" id="form-comprobante" enctype="multipart/form-data">
                                    @csrf
                                    <input type="number" name="id_cfdi" id="id_cfdi" hidden/>
                                    <input type="number" name="id_pago" id="id_pago" hidden/>
                                    <h5>Forma de Pago</h5>
                                    <div class="row">
                                        <div class="form-group col-6 forma-pago-input">
                                            <label for="formaPago">Forma de Pago</label>
                                            <select class="form-control input-style-custom"  name="tipo_pago" id="tipo_pago" style="width: 100%"></select>
                                        </div>
                                        <div class="col form-group">
                                            <label for="cantidad">Monto</label>
                                            <div class="input-group px-0 col-6">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input class="form-control input-style-custom" type="number" step="0.0001" name="cantidad" id="cantidad" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col form-group">
                                            <label for="fecha-pago">Fecha de Pago</label>
                                            <input class="form-control" type="date" name="fecha_pago" id="fecha_pago"
                                                   required>
                                        </div>
                                        <div class="col form-group">
                                            <label for="hora-pago" class="form-label">Hora de Pago</label>
                                            <input type="time" class="form-control" id="hora_pago" name="hora_pago" step="1">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="custom-file col-6 ml-3">
                                            <input type="file" class="custom-file-input input-style-custom" id="docu_comprobante" name="docu_comprobante" onchange="actualizaLabel(this)" lang="es" accept=".pdf,image/jpeg,image/jpg,image/png" multiple="multiple">
                                            <label class="custom-file-label" for="docu_comprobante">Comprobante(Opcional)</label>
                                        </div>
                                    </div>

                                    <input type="text" name="tipo_archivo" id="tipo_archivo" value="" hidden />
                                    <div id="item_comprobante" name="item_comprobante"></div>
                                    <br>
                                    <button type="submit" class="btn btn-success" id="store_comprobante">Enviar</button>
                                </form>
                            </div>
                        </div>
                        <br>

                    </div>
                </div>


            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-success" data-dismiss="modal" data-toggle="modal"
                        data-target="#modal">Regresar
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@push('css')
    <style>
        .custom-file-input ~ .custom-file-label::after {
            content: "Elegir";
        }
    </style>
@endpush

@push('scripts')
    <script>
        let table_comprobante;

        function tablaComprobante(id, cant_pagado, cant_total) {
            let url = '{{route('autofacturador.showComprobante', '*ID*')}}';
            url = url.replace('*ID*', id);
            table_comprobante = $('#table-comprobante').DataTable({
                destroy: true,
                lengthChange: false,
                ajax: url,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                columns: [
                    {data: 'cantidad'},
                    {
                        data: 'tipo_pago', render: function (data, type, row, meta) {
                            return row.forma_pago.descripcion;
                        }
                    },
                    {data: 'num_pago'},
                    {
                        data: 'confirmado',
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return `<div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" name="confirmado" id="confirmado${row.id}" onchange="confirmado(${row.id})" ${parseInt(data) ? 'checked' : ''}
                                             ${parseInt(data) || !parseInt(data) && cant_pagado>=Number(cant_total) || row.estado == 3 || row.estado == 99 ? 'disabled' : ''} >
                                        <label class="form-check-label" for="confirmado">Confirmado</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'comprobante', orderable: false,
                        render: function (data, type, row, meta) {
                            let html = '';
                            let url = '{{route('autofacturador.downloadComprobante', '*ID*')}}';
                            url = url.replace('*ID*', row.id);
                            if (row.nombre_comprobante) {
                                html = `
                                <form action="${url}" method="POST">
                                         @csrf
                                <button type="submit" class="btn btn-success" id="descargar-comprobante" >Descargar</button>
                           <form/>
                            `;
                            } else {
                                html = `
                                    <label class="form-check-label">Sin Comprobante</label>

                            `;
                            }
                            return html;
                        }
                    },
                    {data:'estado',
                        render:function (data, type, row, meta) {
                            const downloadRouteZip = '{{ route('autofacturador.downloadzipCfdiPago') }}/' + row.id;
                            let html = '';
                            if (row.confirmado == 1 && row.estado == 3)
                                html = `<label class="form-check-label">Timbrado</label>
                                        <a href="${downloadRouteZip}" class="btn mr-2"><span class="descargar-icon-black button-style-icon"></span></a>`;
                            else if(cant_pagado>= Number(cant_total))
                                html = `Ya se han echo todos los pagos necesarios`;
                            else if(row.confirmado == 0)
                                html = `<label class="form-check-label">Falta Confirmacion</label>
                                <button type="button" class="btn mx-2" onclick="editarComprobante(${row.id})" data-toggle="tooltip" data-placement="top" title="Editar"><i class="editar-icon button-style-icon"></i></button>
                                `;
                            else if (row.confirmado == 1 && row.estado == 1 || row.estado == 0)
                                html = `<label class="form-check-label">En espera de ser timbrado</label>`;

                            return html;
                        }
                    }
                ],
                rowId: 'id',
                order: [[2, 'desc']],
            });
        }

        $('#descargar-comprobante').submit(function (e) {
            e.preventDefault();
        });

        function confirmado(id) {
            let checked = $('#confirmado' + id).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{route('autofacturador.administracion.confirmarComprobante')}}',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {'id': id, 'confirmado': checked},
                success: function (res) {
                    table_comprobante.ajax.reload();
                }
            });
        }


        fetch("{{route('autofacturador.catalogos')}}/cat-formas-pago")
            .then(res => res.json())
            .then(res => {
                $('#tipo_pago').select2({
                    searchInputPlaceholder: 'Buscar',
                    placeholder: 'Seleccione',
                    data: $.map(res, function (item) {
                        return {
                            id: item.clave,
                            text: item.text
                        }
                    }),
                });
            });

        function editarComprobante(id){
            fetch('{{ route('autofacturador.getComprobante') }}/'+id)
                .then(res => res.json())
                .then(res => {
                    $('#agregar-comprobante-tab').tab('show');
                    $('#id_pago').val(res.id);
                    $('#tipo_pago').trigger('change');
                    $('#fecha_pago').val(res.fecha_pago.split("T")[0]);
                    $('#hora_pago').val(res.fecha_pago.split("T")[1]);
                    $('#cantidad').val(res.cantidad);


                });
        }

    </script>
@endpush