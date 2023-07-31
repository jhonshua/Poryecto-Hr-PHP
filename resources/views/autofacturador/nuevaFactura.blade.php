@extends('layouts.principal')
@section('tituloPagina', "Ordenes de Compra")

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="" id="card-orden-compra">
                <hr>
            </div>
        </div>
    </div>

@endsection

@push('css')

    <style>
        @media (min-width: 1200px) {
            .container-xl, .container-lg, .container-md, .container-sm, .container {
                max-width: 1400px;
            }
        }

        .select2-container .select2-selection--single {
            height: 38px;
        }
    </style>
@endpush

@push('scripts')

    <script>
        const token = '{{ csrf_token() }}';
        const catalogo_crear_oc = [
            {
                element: '#metodo-pago',
                catalogo: 'metodos_pago'
            }, {
                element: '#forma-pago',
                catalogo: 'formas_pago'
            }, {
                element: '#datos_receptor',
                catalogo: 'receptores'
            }, {
                element: '#uso_cfdi',
                catalogo: 'uso_cfdi'
            }, {
                element: '#clientes',
                catalogo: 'clientes'
            },
        ];
        const concepto_catalogo = [{
                element: 'clave-producto',
                catalogo: 'productos_servicios',
            }, {
                element: 'clave-unidad',
                catalogo: 'unidades',
        }];

        const factura_relacion=[
            {
                element: 'tipo-relacion',
                catalogo: 'tipo_relacion'
            },
            {
                element: 'cfdi-facturado',
                catalogo: 'cfdi_facturado'
            }
        ];

        var catalogos = @json($catalogos) ;
        var contenedor_conceptos, contenedor_conceptos_jq;
        var conceptos = [];
        var orden_body = document.querySelector('#card-orden-compra');
        var contador_conceptos = 0;
        var locacion = window.location.pathname.toString();

        document.addEventListener("DOMContentLoaded", function(event) {
            if(isNaN(locacion.split('/')[locacion.split('/').length-1]))
                crearOC(); // Si es verdadero es por que se trata de una nueva orden de compra
            else
                editarFactura(locacion.split('/')[locacion.split('/').length-1]); // Si es falso entonces se trata de una edición
        });

        function crearOC() {
            const route = '{{route('autofacturador.store')}}';
            orden_body.innerHTML = factura_body_general(route, token);
            contenedor_conceptos = document.querySelector('#conceptos');
            contenedor_conceptos_jq = $('#conceptos');
            fetchEmisoras();
            fetchCatalogos(catalogo_crear_oc);
            addConcepto();
        }

        function modificarFechaEmision(el, fecha = '', hora = ''){
            let div_fecha = document.querySelector('#editar-fecha');
            if(el.value)
                div_fecha.innerHTML = `
                    <div class="col form-group">
                        <label for="fecha-pago">Fecha de emisión</label>
                        <input class="form-control" type="date" name="fecha_emision" id="fecha_emision" value="${fecha}" required>
                    </div>
                    <div class="col form-group">
                        <label for="hora-pago" class="form-label">Hora de emisión</label>
                        <input type="time" class="form-control" id="hora_emision" name="hora_emision" step="1" value="${hora}" required>
                    </div>`;
            else div_fecha.innerHTML = '';
        }

        function cfdiRelacionado(el){
            let div_relacion = document.querySelector('#add-relacion');
            if(el.value){
                div_relacion.innerHTML = `
                     <div class="form-group col-12">
                        <label for="formaPago">Tipo de Relacion</label>
                        <select class="form-control"  name="tipo-relacion" id="tipo-relacion" style="width: 100%"></select>
                     </div>
                    <div class="form-group col-12">
                        <label for="formaPago">Relacion Factura</label>
                        <select class="form-control"  name="cfdi-facturado" id="cfdi-facturado" style="width: 100%"></select>
                     </div>
                    `;

                factura_relacion.forEach(catalogo => {
                    let select = $(`select[name="${catalogo.element}"]`);
                    select.select2({
                        data: $.map(catalogos[`${catalogo.catalogo}`].original, function (item) {
                            return { id: item.clave, text: item.text }
                        }),
                    });
                })
            } else div_relacion.innerHTML = '';
        }

        function editarFactura(id) {
            const route = '{{ route('autofacturador.update') }}';
            orden_body.innerHTML = factura_body_general(route, token);
            contenedor_conceptos = document.querySelector('#conceptos');
            contenedor_conceptos_jq = $('#conceptos');
            fetchEmisoras();
            fetchCatalogos(catalogo_crear_oc);

            fetch('{{ route('autofacturador.getCfdi') }}/' + id)
                .then(res => res.json())
                .then(res => {
                    let checkbox_fecha = document.querySelector('#modificar-fecha');
                    let checkbox_factura_relacion = document.querySelector('#factura-relacionado');
                    opcionDefault('empresas-emisoras', res.id_emp_emsora);
                    opcionDefault('clientes', parseInt(res.usuario_id));
                    opcionDefault('datos_receptor', res.receptor_id);
                    opcionDefault('uso_cfdi', res.receptor_uso_cfdi);
                    opcionDefault('metodo-pago', res.metodo_pago);
                    opcionDefault('forma-pago', res.forma_pago);
                    opcionDefault('iva', Intl.NumberFormat('en-US').format(Number(res.tasa_cuota).toFixed(2)));
                    checkbox_fecha.checked = (res.modificar_fecha) ? true : false;
                    checkbox_factura_relacion.checked=(res.cfdi_relacionado) ? true: false;
                    if(res.modificar_fecha) 
                        modificarFechaEmision(checkbox_fecha, res.fecha.split('T')[0], res.fecha.split('T')[1])

                    if(res.cfdi_relacionado){
                        cfdiRelacionado(checkbox_factura_relacion);
                        $('#tipo-relacion').val(res.tipo_relacion);
                        $('#tipo-relacion').trigger('change');
                        $('#cfdi-facturado').val(res.cfdi_relacionado);
                        $('#cfdi-facturado').trigger('change');
                    }

                    $('#id').val(res.id);
                    res.conceptos.forEach(concepto => {
                        addConcepto(concepto);
                    });
                    calcularImporteTotal();
                });

        }

        function calcularImporteTotal() {
            let importe = 0;
            for (const concepto in conceptos) {
                importe += (conceptos[`${concepto}`].cantidad.value * conceptos[`${concepto}`].valor_unitario.value);
            }
            let total = importe + (importe * document.querySelector('#iva').value);
            document.querySelector('#importe').value = importe.toFixed(4);
            document.querySelector('#total').value = total.toFixed(2);
        }

        function addConcepto(concepto){
            contenedor_conceptos_jq.append(concepto_html(contador_conceptos));
            let element = contenedor_conceptos.querySelector(`[data-container="${contador_conceptos}"]`);
            let objeto_concepto = {
                'element': element,
                'valor_unitario': element.querySelector(`[name="valor-unitario[${contador_conceptos}]"]`),
                'cantidad': element.querySelector(`[name="cantidad[${contador_conceptos}]"]`),
                'clave_unidad': element.querySelector(`[name="clave-unidad[${contador_conceptos}]"]`),
                'clave_producto': element.querySelector(`[name="clave-producto[${contador_conceptos}]"]`),
                'descripcion': element.querySelector(`[name="descripcion[${contador_conceptos}]"]`),
                'id_concepto': element.querySelector(`[name="id_concepto[${contador_conceptos}]"]`),
            };

            conceptos[contador_conceptos+'_concepto'] = objeto_concepto;
            concepto_catalogo.forEach(catalogo => {
                let select = $(`select[name="${catalogo.element}[${contador_conceptos}]"]`);
                select.select2({
                    data: $.map(catalogos[`${catalogo.catalogo}`].original, function (item) {
                        return { id: item.clave, text: item.text }
                    }),
                });
            })

            if(concepto){
                objeto_concepto.valor_unitario.value = concepto.valor_unitario;
                objeto_concepto.cantidad.value = concepto.cantidad;
                objeto_concepto.clave_unidad.value = concepto.clave_unidad;
                $(`[name="clave-unidad[${contador_conceptos}]"]`).trigger('change');
                objeto_concepto.clave_producto.value = concepto.clave_prod;
                $(`[name="clave-producto[${contador_conceptos}]"]`).trigger('change');
                objeto_concepto.descripcion.value = concepto.descripcion;
                objeto_concepto.id_concepto.value = concepto.id;
            }
            document.getElementById('contador-conceptos').innerText = Object.keys(conceptos).length;
            contador_conceptos ++;
        }

        function fetchEmisoras(){
            $('#empresas-emisoras').select2({
                searchInputPlaceholder: 'Buscar',
                placeholder: 'Seleccione una empresa emisora',
                data: $.map(catalogos.empresas_emisoras.original , function (item) {
                    return {
                        text: `${item.etiqueta}`,
                        children: $.map(item.empresas, function (these) {
                            return {
                                id: these.id,
                                text: `${these.razon_social}`
                            }
                        })
                    }
                }),
            });
        }

        function fetchCatalogos(catalogos_list) {
            catalogos_list.forEach(catalogo => {
                $(catalogo.element).select2({
                    searchInputPlaceholder: 'Buscar',
                    placeholder: 'Seleccione',
                    data: $.map(catalogos[`${catalogo.catalogo}`].original, function (item) {
                        return {
                            id: item.clave,
                            text: item.text
                        }
                    }),
                });
            });
        }

        function opcionDefault(componente, value) {
            $(`#${componente}`).val(value);
            $(`#${componente}`).trigger('change');
        }

        function deleteConcepto(id){
            if(document.getElementById('conceptos').children.length == 1){
                return alertify.alert('Debe haber 1 concepto almenos');
            }else{
                contenedor_conceptos.querySelector(`[data-container="${id}"]`).remove();
                delete conceptos[id+'_concepto'];
                document.getElementById('contador-conceptos').innerText = Object.keys(conceptos).length;
                calcularImporteTotal();
            }
        }

        const concepto_html = (contenedor_count) => {
            return `
                <div class="col-12" data-container="${contenedor_count}">
                    <input type="hidden" name="id_concepto[${contenedor_count}]" value="">
                    <div class="row">
                        <div class="col-11">
                            <div class="row">
                                <div class="col-4 form-group">
                                    <label for="">Clave de producto o servicio</label>
                                    <select name="clave-producto[${contenedor_count}]" class="form-control" id="clave-producto" style="width: 100%"></select>
                                </div>
                                <div class="col-8 form-group">
                                    <label for="">Descripción producto o servicio</label>
                                    <input type="text" class="form-control" name="descripcion[${contenedor_count}]" required>
                                </div>
                                <div class="col-4 form-group">
                                    <label for="">Clave de unidad</label>
                                    <select name="clave-unidad[${contenedor_count}]" class="form-control" style="width: 95%"></select>
                                </div>
                                <div class="col-4 form-group">
                                    <label for="">Cantidad</label>
                                    <input name="cantidad[${contenedor_count}]" class="form-control" value="" type="number" onkeyup="calcularImporteTotal()" required step="0.0001">
                                </div>
                                <div class="col-4 form-group">
                                    <label for="">Valor unitario</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input name="valor-unitario[${contenedor_count}]" class="form-control" value="" type="number" onkeyup="calcularImporteTotal()" required step="0.0001">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-1 text-center align-self-center pr-4 pl-0">
                            <button type="button" onclick="deleteConcepto(${contenedor_count})" class="btn"><span class="cancel-icon button-style-icon"></span></button>
                        </div>
                    </div>
                    <hr class="mb-2 mt-0 border">
                </div>`;
        }

        const factura_body_general = (route, token) => {
            let super_super = '{{(isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador) ? 'true' : 'false' }}';
            let selector_clientes = `
                <h5>Clientes</h5>
                    <div class="row">
                        <div class="col-12">
                            <label for="" class="col-form-label">Usuarios Clientes</label>
                            <div class="form-group">
                                <select class="js-example-responsive" id="clientes" name="clientes" style="width: 100%">
                                </select>
                            </div>
                        </div>
                    </div>`;

            return `
            <div class="card">
                <form action="${route}" method="POST" id="form-autofacturador-store">
                    <div class="row">
                        <div class="col-12 col-md-4 pr-0">
                            <input type="hidden" name="_token" value="${token}" />
                            <input type="hidden" name="id" id="id" value="" />
                            <div class="card-body">
                                ${(super_super) ? selector_clientes : ''}
                                <h5>Emisor</h5>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="" class="col-form-label">Empresas emisoras</label>
                                        <div class="form-group">
                                            <select class="js-example-responsive" id="empresas-emisoras" name="empresa-emisora" style="width: 100%" required>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-3 border">
                                <h5>Fecha de emisión</h5>
                                <div class="row" >
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="modificar-fecha" id="modificar-fecha" onchange="modificarFechaEmision(this)">
                                            <label class="form-check-label" for="defaultCheck1">Modificar fecha de emisión</label><br>
                                            <small>El periodo entre la fecha de generación del documento y la fecha en la que se pretende certificar no debe exceder de 72 horas, o dicho periodo no puede ser menor a cero horas.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3" id="editar-fecha">

                                    </div>
                                </div>
                                <hr class="my-3 border">
                                <h5>Relacionar</h5>
                                <div class="row" >
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="factura-relacionado" id="factura-relacionado" onchange="cfdiRelacionado(this)">
                                            <label class="form-check-label" for="defaultCheck1">Agregar una Factura relacionda</label><br>
                                            <small>Se mostraran las Facturas que ya han sido timbradas.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3" id="add-relacion"></div>
                                </div>
                                <hr class="my-3 border">
                                <h5>Datos fiscales receptor</h5>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="" class="col-form-label">Usuarios Datos Receptor</label>
                                        <div class="form-group">
                                            <select class="js-example-responsive" id="datos_receptor" name="datos_receptor" style="width: 100%">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="" class="col-form-label">Uso CFDI</label>
                                        <div class="form-group">
                                            <select class="js-example-responsive" id="uso_cfdi" name="uso_cfdi" style="width: 100%">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-3 border">
                                <h5>Forma y método de pago</h5>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label for=metodoPago"">Método de Pago</label><br>
                                        <select class="form-control" name="metodo-pago" id="metodo-pago" style="width: 100%"></select>
                                    </div>
                                    <div class="form-group col-12">
                                        <label for="formaPago">Forma de Pago</label>
                                        <select class="form-control"  name="forma-pago" id="forma-pago" style="width: 100%"></select>
                                    </div>
                                </div>
                                <hr class="my-3 border">
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label for="impuestos">% IVA</label>
                                        <select name="iva" id="iva" class="form-control" onchange="calcularImporteTotal()">
                                            <option value="0.16" selected>16 %</option>
                                            <option value="0">0 %</option>
                                        </select>
                                    </div>
                                </div>
                                <hr class="my-3 border">
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label for="">Importe</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control" name="subtotal" id="importe" readonly required>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <label for="">Total</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control" name="total" id="total" readonly required>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group text-center">
                                        <button type="submit" class="btn btn-warning text-white w-100">Enviar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-8 py-4 pl-0">
                            <div class="card-header bg-white d-flex justify-content-between mb-3">
                                <h5>Conceptos (<span id="contador-conceptos"></span>)</h5>
                                <button type="button" class="btn" onclick="addConcepto()"><span class="agregar-icon button-style-icon"></span></button>
                            </div>
                            <div class="row" id="conceptos">
                            </div>
                            <div class="card-header bg-white d-flex justify-content-end mb-3">
                                <button type="button" class="btn" onclick="addConcepto()"><span class="agregar-icon button-style-icon"></span></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;
        }
    </script>
@endpush
