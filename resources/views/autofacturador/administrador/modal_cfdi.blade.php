<div class="modal fade" data-backdrop="static" id="modal" tabindex="-1" aria-labelledby="modal" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ORDEN DE COMPRA</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ver-tab" data-toggle="tab" data-target="#ver"
                                type="button" role="tab" aria-controls="ver" aria-selected="true">Ver
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="comprobante-tab" data-toggle="tab" data-target="#comprobante"
                                type="button" role="tab" aria-controls="comprobante" aria-selected="false">
                            Comprobantes de pago
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agregar-comprobante-tab" data-toggle="tab" data-target="#agregar-comprobante" type="button"
                                role="tab" aria-controls="agregar-comprobante" aria-selected="false">Agregar Comprobante
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="ver" role="tabpanel" aria-labelledby="ver-tab">
                        <form id="form-data" method="POST"></form>
                    </div>
                    <div class="tab-pane fade" id="comprobante" role="tabpanel" aria-labelledby="comprobante-tab">
                        <div id="comprobante_pago"></div>
                    </div>
                    <div class="tab-pane fade" id="agregar-comprobante" role="tabpanel" aria-labelledby="agregar-comprobante-tab">
                        <div class="card">
                            <div class="card-body">
                                <form method="post" action="" id="form-comprobante" enctype="multipart/form-data">

                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger text-white" id="rechazar-btn" onclick="rechazar()">Rechazar</button>
                <a target="_blank" class="btn btn-primary" id="prefactura">Prefactura</a>
                <button type="button" class="btn btn-warning text-white" id="submit-btn" onclick="submitForm()">Enviar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const modal = $('#modal');
        const modal_body = $('#modal .modal-body #form-data');
        const submit_btn = document.querySelector('#modal #submit-btn');
        const comprobante_tab_pane = document.querySelector('#comprobante_pago');
        const form_comprobante = document.querySelector('#form-comprobante');
        const tocken = '@csrf';

        let table_comprobante;
        let restante_comprobante = 0;

        var comprobantes_pago_datos = {
            'ultima_parcialidad'        : 0,
            'importe_pagado'            : 0,
            'importe_saldo_insoluto'    : 0,
            'agregar_otro_comprobante'  : true,
        };

        init();

        $('#descargar-comprobante').submit(function (e) {
            e.preventDefault();
        });

        function verFactura(id, state = true) {
            alertify.message('Espere por favor, cargando...').delay(2);
            submit_btn.disabled = false;
            fetch('{{ route('autofacturador.administracion.getCfdi') }}/' + id)
                .then(res => res.json())
                .then(res => {
                    let rechazar = document.querySelector('#modal button.btn.btn-danger');
                    let prefactura = document.querySelector('#prefactura');

                    prefactura.style.display = 'none';
                    prefactura.href = '{{ route('autofacturador.administracion.reloadPDF') }}/' + id;
                    rechazar.style.display = 'block';
                    rechazar.onClick = 'rechazar()';
                    rechazar.innerHTML = 'Rechazar';

                    document.querySelector('#modal .btn.btn-danger.text-white').style.display = 'block';

                    $( "#cantidad" ).keyup(function() {
                        let enviar = document.querySelector('#store_comprobante');
                        if (parseFloat(res.total) < this.value) {
                            alertify.message('El monto no puede ser mayor a : '+parseFloat(res.total).toFixed(2)).delay(5);
                            enviar.disabled = true;
                        }
                        else enviar.disabled = false;
                    });
                    
                    const factura_tabla = tabla_info_cfdi_html(res);

                    let route = '';
                    switch (res.estado){
                        case '1':
                            prefactura.style.display = 'block'
                            route = '{{ route('autofacturador.administracion.aprobarOC') }}';
                            submit_btn.innerHTML = 'Aprobar';
                            break
                        case '2':
                            prefactura.style.display = 'block'
                            route = '{{ route('autofacturador.administracion.timbrar') }}';
                            submit_btn.innerHTML = 'Timbrar';
                            break
                    }

                    modal_body.attr('action', route);
                    modal_body.html(factura_tabla);

                    let btn_rechazar = document.querySelector('#rechazar-btn');
                    btn_rechazar.style.display = 'block';

                    escribirComprobanteFormulario();
                    comprobante_tab_pane.innerHTML = tabla_comprobantes_html;
                    verComprobante(id);

                    if (!state || res.estado == "99" || res.estado == "3") {
                        submit_btn.style.display = 'none';
                        rechazar.style.display = 'none';
                    }

                    modal.modal('show');
                    $('#id_cfdi').val(id);
                })
                .finally(()=>{
                    alertify.message('Cargado con Exito');
            });
        }

        function init(){
            // boton guardar
            $('#form-comprobante').submit(function (e) {
                e.preventDefault();
                alertify.message('Enviados Datos').delay(5);

                const formData = new FormData(this);

                $.ajax({
                    url: '{{route('autofacturador.storeComprobante')}}',
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        $("#form-comprobante")[0].reset();
                        $('#id_cfdi').val(data.id_cfdi);
                        table_comprobante.ajax.reload();
                        alertify.message('Datos guardados').delay(5);
                        let  agregarclas= document.querySelector('#agregar-comprobante');
                        agregarclas.classList.remove("active");
                        let agregarclastab = document.querySelector('#agregar-comprobante-tab');
                        agregarclastab.classList.remove("active");
                        let comprobanterclastab = document.querySelector('#comprobante-tab');
                        comprobanterclastab.classList.add("active");
                        let comprobanterclas = document.querySelector('#comprobante');
                        comprobanterclas.classList.add("active");
                        comprobanterclas.classList.add("show");
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

        }

        function cancelarCFDIPago(id_cfdi, id_pago){
            alertify.confirm('Confirme cancelar el CFDI de pago.',
                function () {
                    fetch('{{ route('autofacturador.administracion.cancelarCfdiPago') }}', {
                            method: 'post',
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-Token": $('input[name="_token"]').val()
                            },
                            body: JSON.stringify({ id_cfdi: id_cfdi, id_pago: id_pago }),
                        }
                    ).then(res => res.json())
                        .then(res => {
                            alertify.alert(res.mensaje);
                            table_comprobante.ajax.reload();
                        });
                });
        }

        function verComprobante(id) {
            let url = '{{route('autofacturador.showComprobante', '*ID*')}}';
            url = url.replace('*ID*', id);
            table_comprobante = $('#table-comprobante').DataTable({
                destroy: true,
                lengthChange: false,
                ajax: {
                    url: url,
                    complete: function () {
                        calcularImportesComprobantes(id);
                    }
                },
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                columns: [
                    {
                        data: 'cantidad',
                        render: function(data, type, row){
                            return '$ ' + Intl.NumberFormat('en-US').format(Number(row.cantidad));
                        }
                    },{
                        data: 'tipo_pago',
                        render: function(data, type, row){
                            return row.tipo_pago + ' - ' + row.forma_pago.descripcion;
                        }
                    },
                    {data: 'num_pago'},
                    {
                        data: 'confirmado',
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return `<div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" name="confirmado" id="confirmado${row.id}" onchange="confirmado(${row.id})" ${parseInt(data) ? 'checked' : ''}
                                             ${parseInt(data) && row.estado == 3 || row.estado == 99 ? 'disabled' : ''} >
                                        <label class="form-check-label" for="confirmado">Confirmado</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'comprobante', orderable: false,
                        render: function (data, type, row, meta) {
                            let html = '';
                            if (row.nombre_comprobante) {
                                let url = '{{route('autofacturador.downloadComprobante', '*ID*')}}';
                                url = url.replace('*ID*', row.id);
                                html = `
                                <form action="${url}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success" id="descargar-comprobante" >Descargar</button>
                                <form/>`;
                            } else {
                                html = `<label class="form-check-label">Sin Comprobante</label>`;
                            }
                            return html;
                        },
                    },
                    {
                        data: 'acciones',
                        className: "text-center" ,
                        render: function (data, type, row, meta) {
                            const downloadRouteZip = '{{ route('autofacturador.downloadzipCfdiPago') }}/' + row.id;
                            let html = '';
                            if(row.observaciones && row.estado != 3)
                                html += `<label class="form-check-label text-danger">${row.observaciones}</label>`;
                            if (row.confirmado == 1 && row.estado == 3)
                                html += `<label class="form-check-label">Timbrado</label><br>
                                        <a href="${downloadRouteZip}" class="btn mr-2"><span class="descargar-icon-black button-style-icon"></span></a>
                                        <button class='btn mr-1' type="button" onClick="cancelarCFDIPago(${row.id_cfdi}, ${row.id})" data-toggle="tooltip" data-placement="top" title="Cancelar CFDI pago"><span class="cancelar-icon button-style-icon"></span></button>`;
                            else if(row.confirmado == 0)
                                html += `<label class="form-check-label">Falta Confirmacion</label>
                                <button type="button" class="btn mx-2" onclick="editarComprobante(${row.id})" data-toggle="tooltip" data-placement="top" title="Editar"><i class="editar-icon button-style-icon"></i></button>`;
                            else if (row.confirmado == 1 && row.estado == 1 || row.estado == 0)
                                html += `<button class="btn btn-warning text-white" onclick="timbrarComprobante(${id},${row.id}, this)">Timbrar</button>`;

                            return html;
                        },

                    },
                ],
                rowId: 'id',
                order: [[2, 'desc']],
            });

        }

        function escribirComprobanteFormulario(){
            form_comprobante.innerHTML = form_comprobante_html;
            getCatalogosFacturador();
        }

        function rechazar() {
            let btn_rechazo = document.querySelector('#modal .btn.btn-danger');
            btn_rechazo.style.display = 'none';
            submit_btn.innerHTML = 'Enviar observaciones';
            modal_body.attr('action', '{{ route('autofacturador.administracion.rechazarOP') }}');
            let form_card = document.querySelector('#form-data .card');
            const html = `
                <div class="form-group mx-5">
                    <label for="">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" class="form-control"></textarea>
                </div>
            `;
            form_card.innerHTML += html;
        }

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

        function timbrarComprobante(id, id_comprobante, el) {
            el.disabled = true;
            $.ajax({
                url: '{{route('autofacturador.administracion.timbrarPagoComprobante')}}',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {'id_cfdi': id, 'id_comprobante': id_comprobante},
                success: function (res) {
                    if (!res.status)
                        alertify.alert('Error', res.mensaje);
                    else
                        alertify.success('Timbrado');

                    el.disabled = false;
                    escribirComprobanteFormulario();
                    table_comprobante.ajax.reload();
                }
            });
        }

        function getCatalogosFacturador(){
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
        }

        function editarComprobante(id){
            const sumbit_comprobante_btn = document.querySelector('#store_comprobante');
            fetch('{{ route('autofacturador.getComprobante') }}/'+id)
                .then(res => res.json())
                .then(res => {
                    $('#agregar-comprobante-tab').tab('show');
                    $('#id_pago').val(res.id);
                    $('#tipo_pago').val(res.tipo_pago);
                    $('#tipo_pago').trigger('change');
                    $('#fecha_pago').val(res.fecha_pago.split("T")[0]);
                    $('#hora_pago').val(res.fecha_pago.split("T")[1]);
                    $('#cantidad_input').val(res.cantidad);
                    sumbit_comprobante_btn.disabled = false;
                });
        }

        function actualizaLabel(el) {
            let fileName = document.querySelector('#' + el.id).files[0].name;

            if (fileValidation(fileName)) {
                document.querySelector(`#form-comprobante label[for=${el.id}]`).innerText = fileName;
                document.querySelector(`#tipo_archivo`).value = fileName;
            } else {
                alertify.alert("Solo Archivos jpg, jpeg, png, pdf", function () {
                    alertify.message('OK');
                });
            }
        }

        function fileValidation(nombre) {
            var filePath = nombre;
            var allowedExtensions = /(.jpg|.jpeg|.png|.pdf)$/i;
            if (!allowedExtensions.exec(filePath))
                return false;
            else
                return true;
        }

        function calcularImportesComprobantes(id){
            const sumbit_comprobante_btn = document.querySelector('#store_comprobante');
            fetch('{{ route('autofacturador.administracion.getCfdi') }}/' + id)
                .then(res => res.json())
                .then(response_cfdi => {
                    let monto_pagado = 0;
                    let ultima_p = 0;
                    let agregar_comprobante = true;
                    response_cfdi.comprobantes_pago.forEach(comprobante => {
                        if (comprobante.confirmado && comprobante.estado == 3) {
                            monto_pagado += parseFloat(comprobante.cantidad);
                            ultima_p = (parseInt(comprobante.num_pago) > ultima_p) ? comprobante.num_pago : ultima_p;
                        } else {
                            agregar_comprobante = false;
                        }
                    })

                    comprobantes_pago_datos.importe_pagado = monto_pagado;
                    comprobantes_pago_datos.importe_saldo_insoluto = response_cfdi.total - monto_pagado;
                    comprobantes_pago_datos.ultima_parcialidad = ultima_p;
                    comprobantes_pago_datos.agregar_otro_comprobante = agregar_comprobante;

                    document.querySelectorAll('.imoporte-pagado').forEach(el => el.innerText = '$ ' + Intl.NumberFormat('en-US').format(Number(comprobantes_pago_datos.importe_pagado)));
                    document.querySelectorAll('.saldo-insoluto').forEach(el => el.innerText = '$ ' + Intl.NumberFormat('en-US').format(Number(comprobantes_pago_datos.importe_saldo_insoluto)));
                    document.querySelectorAll('.ultima-parcialidad').forEach(el => el.innerText = comprobantes_pago_datos.ultima_parcialidad);
                    document.querySelector('#cantidad_input').setAttribute("max",comprobantes_pago_datos.importe_saldo_insoluto);
                    document.querySelector('#id_cfdi').value = id;

                    sumbit_comprobante_btn.disabled = (comprobantes_pago_datos.agregar_otro_comprobante && response_cfdi.estado != 99 && comprobantes_pago_datos.importe_saldo_insoluto > 0) 
                        ? false : true;
                });
        }

        //Retornan HTML
        var conceptos_html = (response_cfdi) => {
            let conceptos = '';
            response_cfdi.conceptos.forEach(concepto => {
                conceptos += `
                            <tr>
                                <td>${concepto.clave_prod}</td>
                                <td>${concepto.clave_unidad}</td>
                                <td>${concepto.descripcion}</td>
                                <td style="text-align: center">${Intl.NumberFormat('en-US').format(Number(concepto.cantidad))}</td>
                                <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(concepto.valor_unitario))}</td>
                                <td width="12%">$ ${Intl.NumberFormat('en-US').format(Number(concepto.base))}</td>
                            </tr>
                    `;
            });

            return conceptos;
        }

        var tabla_info_cfdi_html = (response_cfdi) => {
            let conceptos = conceptos_html(response_cfdi);

            let creo_oc = '', aprobo_oc = '', timbro_oc = '', cancelo_oc = '', reset = 0;

            response_cfdi.logs.map((log, index) => {
                if (log.evento == '0' && index == 1 && reset == 0)
                    reset = 1;

                if (reset == 0)
                    if (log.evento == 1 && creo_oc == '')
                        creo_oc = `Alta ${new Date(log.fecha_creacion).toLocaleString("es-MX", {hour12: true})} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 2 && aprobo_oc == '')
                        aprobo_oc = `Aprobado ${new Date(log.fecha_creacion).toLocaleString("es-MX", {hour12: true})} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 3 && timbro_oc == '')
                        timbro_oc = `Timbrado ${new Date(log.fecha_creacion).toLocaleString("es-MX", {hour12: true})} por ${log.usuarios.nombre_completo} `;
                    else if (log.evento == 99 && cancelo_oc == '')
                        cancelo_oc = `Cancelado ${new Date(log.fecha_creacion).toLocaleString("es-MX", {hour12: true})} por ${log.usuarios.nombre_completo} `;
            });

            let relacion_cfdi='';

            if(response_cfdi.relacion_cfdi){
                relacion_cfdi=`CFDI Relacionado: ${response_cfdi.relacion_cfdi.uuid}<br>
                               Tipo de Relacion: ${response_cfdi.tipo_relacion}<br>`;
            }


            return `${tocken}
                    <input type="hidden" name="id" value="${response_cfdi.id}">
                    <div class="card">
                        <div class="card-body">
                            <table class="w-100 table">
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
                                        Monto comisión: $ ${(response_cfdi.pagar_del == 'subtotal') ?
                                                                (response_cfdi.comision * response_cfdi.subtotal / 100).toFixed(4) :
                                                                (response_cfdi.comision * response_cfdi.total / 100).toFixed(4)} del ${response_cfdi.pagar_del}
										<br>
                                        <hr>
                                        ${creo_oc} <br>
                                        ${aprobo_oc} <br>
                                        ${timbro_oc} <br>
                                        ${cancelo_oc}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Subtotal: $${Intl.NumberFormat('en-US').format(Number(response_cfdi.subtotal).toFixed(2))}</td>
                                    <td>Total: $${Intl.NumberFormat('en-US').format(Number(response_cfdi.total).toFixed(2))}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table class="table w-100 mt-3">
                                            <thead>
                                                <tr>
                                                    <th>Clave de P/S</th>
                                                    <th>Clave unidad</th>
                                                    <th>Descripción</th>
                                                    <th>Cantidad</th>
                                                    <th>Valor unitario</th>
                                                    <th>Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${conceptos}
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>`
        }

        var tabla_comprobantes_html = `
            ${ tocken }
            <div class="row">
                <div class="col">
                    <div class="article border">
                        <div class="row">
                            <div class="col">
                                <span class="font-weight-bold">Importe pagado:</span>
                                <span class="imoporte-pagado"></span>
                            </div>
                            <div class="col form-group">
                                <span class="font-weight-bold">Importe saldo insoluto:</span>
                                <span class="saldo-insoluto"></span>
                            </div>
                            <div class="col form-group">
                                <span class="font-weight-bold">Última parcialidad:</span>
                                <span class="ultima-parcialidad"></span>
                            </div>
                        </div>
                        <table class="table w-100" id="table-comprobante">
                            <thead>
                            <tr>
                                <th>Cantidad</th>
                                <th>Tipo de pago</th>
                                <th>Numero Pago</th>
                                <th>Confirmaciones</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>`;

        var form_comprobante_html = `${tocken}
            <div class="row">
                <div class="col">
                    <span class="font-weight-bold">Importe pagado:</span>
                    <span class="imoporte-pagado"></span>
                </div>
                <div class="col form-group">
                    <span class="font-weight-bold">Importe saldo insoluto:</span>
                    <span class="saldo-insoluto"></span>
                </div>
                <div class="col form-group">
                    <span class="font-weight-bold">Última parcialidad:</span>
                    <span class="ultima-parcialidad"></span>
                </div>
            </div>
            <input type="number" name="id_cfdi" id="id_cfdi" hidden/>
            <input type="number" name="id_pago" id="id_pago" hidden/>
            <div class="row">
                <div class="form-group col-6 forma-pago-input">
                    <label for="formaPago">Forma de Pago</label>
                    <select class="form-control input-style-custom"  name="tipo_pago" id="tipo_pago" style="width: 100%" required></select>
                </div>
                <div class="col form-group">
                    <label for="cantidad">Monto</label>
                    <div class="input-group px-0 col-6">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control input-style-custom" type="number" name="cantidad" id="cantidad_input" min="1" step=".0001" required>
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
                    <input type="time" class="form-control" id="hora_pago" name="hora_pago" step="1" required>
                </div>
            </div>

            <div class="row mt-2">
                <div class="custom-file col-6 ml-3">
                    <label class="custom-file-label" for="docu_comprobante">Comprobante(Opcional)</label>
                    <input type="file" class="custom-file-input input-style-custom" id="docu_comprobante" name="docu_comprobante" onchange="actualizaLabel(this)" lang="es" accept=".pdf,image/jpeg,image/jpg,image/png" multiple="multiple">
                </div>
            </div>

            <input type="text" name="tipo_archivo" id="tipo_archivo" value="" hidden />
            <div id="item_comprobante" name="item_comprobante"></div>
            <br>
            <button type="submit" class="btn btn-success" id="store_comprobante">Enviar</button>`;

    </script>

@endpush