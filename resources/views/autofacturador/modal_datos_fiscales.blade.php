<div class="modal fade" id="modal-datos-discales" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel"aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-datos-fiscales" action="{{ route('autofacturador.storeDatosFiscales') }}" method="POST">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')

    <script>

        const modal_fiscal = $('#modal-datos-discales');
        const modal_body_fiscal = $('#modal-datos-discales .modal-body form');

        var catalogos_datos_fiscales = [
            {
                element: '#regimen_fiscal',
                catalogo: 'regimen-fiscal',
                state: false,
            }
        ];

        function datosFiscales() {
            fetchCatalogos(catalogos_datos_fiscales, 0);

            let html_form = `
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="datosFiscales-tab" data-toggle="tab" data-target="#datosFiscales" type="button"
                                role="tab" aria-controls="datosFiscales" aria-selected="false">Datos Fiscales</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agregar-tab" data-toggle="tab" data-target="#agregar" type="button" role="tab" aria-controls="agregar" aria-selected="true">Agregar</button>
                    </li>
                </ul>
                <div class="tab-content" id="contentDatosFiscales">
                    <div class="tab-pane active" id="datosFiscales" role="tabpanel" aria-labelledby="datosFiscales-tab">
                        <div class="row">
                            <div class="col">
                                 <div class="article border">
                                    <table class="table w-100" id="table-datosFiscales">
                                    <thead>
                                    <tr>
                                        <th>Razón social</th>
                                        <th>RFC</th>
                                        <th>Codigo Postal</th>
                                        <th>Regimen Fiscal</th>
                                        <th>Correo</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    </table>
                                 </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="agregar" role="tabpanel" aria-labelledby="agregar-tab">
                    <form type="post" action="{{route('autofacturador.storeDatosFiscales')}}">
                    <div class="card">
                        <div class="card-body">
                             ${tocken}
                            <input type="hidden" name="id" id="datos-fisles-id" value="">
                            <div class="form-group">
                                <label for="recipient-name" class="col-form-label">RFC:</label>
                                <input type="text" class="form-control input-style-custom" id="rfc" name="rfc">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Razón social <small>(Sin Sociedades)</small>:</label>
                                <input class="form-control input-style-custom" type="text" id="razon_social" name="razon_social">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Correo:</label>
                                <input type="email" class="form-control input-style-custom" type="text" id="correo" name="correo">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Codigo Postal:</label>
                                <input class="form-control input-style-custom" type="text" id="cp" name="cp">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Domicilio:</label>
                                <input class="form-control input-style-custom" type="text" id="domicilio" name="domicilio">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Regimen fiscal:</label>
                                <select class="form-control input-style-custom" type="text" id="regimen_fiscal" name="regimen_fiscal"></select>
                            </div>
                             <div class="row justify-content-end d-flex mx-2">
                                <button type="button" class="btn btn-warning text-white" onclick="submitFormFiscal()">Guardar</button>
                            </div>
                        </div>
                    </div>
                    </form>
                    </div>
                </div>`;

            modal_body_fiscal.html(html_form);
            modal_fiscal.modal('show');

            const table_datosFiscales = $('#table-datosFiscales').DataTable({
                destroy: true,
                lengthChange: false,
                ajax: '{{ route('autofacturador.catalogos')}}/datos-fiscales-receptor-table',
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                columns: [
                    {data: 'rfc'},
                    {data: 'razon_social'},
                    {data: 'cp'},
                    {data: 'regimen_fiscal'},
                    {data: 'correo'},
                    {
                        data: 'acciones',
                        orderable: false,
                        render: function (data, type, row, meta){
                            return `<button class='btn mx-2' type="button" onClick="editarDatosFiscales(${row.id})" data-toggle="tooltip" data-placement="top" title="Editar"><i class="editar-icon button-style-icon"></i></button>`;
                        }
                    }
                ],
                order: [[0, 'desc']],
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            eventoTeclado('razon_social');
            eventoTeclado('rfc');

        }

        function editarDatosFiscales(id){
            let contenedor = document.querySelector('#contentDatosFiscales')
            fetch('{{ route('autofacturador.getDatoFiscal') }}/'+id)
                .then(res => res.json())
                .then(res => {
                    $('#agregar-tab').tab('show');

                    $('#rfc').val(res.rfc);
                    $('#razon_social').val(res.razon_social);
                    $('#cp').val(res.cp);
                    $('#domicilio').val(res.domicilio);
                    $('#uso_cfdi').val(res.uso_cfdi);
                    $('#regimen_fiscal').val(res.regimen_fiscal);
                    $('#datos-fisles-id').val(res.id);
                    $('#correo').val(res.correo);

                    $('#uso_cfdi').trigger('change');
                    $('#regimen_fiscal').trigger('change');
                });
        }

        function submitFormFiscal(){
            const formElement = document.querySelector('#form-datos-fiscales');
            const data = new URLSearchParams();
            for (const pair of new FormData(formElement)) {
                data.append(pair[0], pair[1]);
            }

            if ($('#rfc').val() == '' || $('#razon_social').val() == '' || $('#domicilio_fiscal').val() == '' || $('#uso_cfdi').val() == '' || $('#regimen_fiscal').val() == '' || $('#correo').val() == '') {
                return alertify
                    .alert('Llena todos los campos  ', function () {
                        alertify.message('OK');
                    });
            }

            fetch(formElement.action, {
                method: 'post',
                body: data,
            }).then(res => res.json())
                .then(res => {
                    if (res.status)
                        modal_fiscal.modal('hide')
                });
        }

        function fetchCatalogos(catalogos, fill_edit) {
            catalogos.forEach(catalogo => {
                fetch('{{ route('autofacturador.catalogos')}}/' + catalogo.catalogo)
                    .then(response => response.json())
                    .then(response => {
                        $(catalogo.element).select2({
                            searchInputPlaceholder: 'Buscar',
                            placeholder: 'Seleccione',
                            data: $.map(response, function (item) {
                                return {
                                    id: item.clave,
                                    text: item.text
                                }
                            }),
                        });
                    }).then(() => {
                    if(fill_edit){
                        let val;
                        switch (catalogo.catalogo) {
                            case 'productos-servicios': val = fill_edit.conceptos[0].clave_prod; break;
                            case 'unidades': val = fill_edit.conceptos[0].clave_unidad; break;
                            case 'cat-metodos-pago': val = fill_edit.metodo_pago; break;
                            case 'cat-formas-pago': val = fill_edit.forma_pago; break;
                        }
                        opcionDefault(catalogo.element.replace('#', ''), '', val);
                    }
                });
            });
        }

    </script>
@endpush