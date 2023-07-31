@include('includes.head')
@include('includes.navbar')

<div class="container" id="app">

    @include('includes.header',['title'=>'Facturador - Editar', 'subtitle'=>'Contabilidad', 'img'=>'img/header/administracion/icono-usuario.png', 'route'=>'factura.index'])

    <div class="row" id="crudFactura">
        <div class="col-md-5">
            <div class="article border mt-3">

                <div class="col-md-12">
                    <h3><strong>Datos Factura</strong></h3>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('factura.insertar') }}" method="POST" class="needs-validation"
                              novalidate="">
                            @csrf
                            <button class="btn btn-dark float-right mb-3" v-on:click.prevent="update()">Guardar</button>
                            <br>
                            <div class="col-md-12 mb-3">
                                <label for="country">Empresa emisora</label>
                                <select class="custom-select d-block w-100" id="emisora" name="emisora" required=""
                                        v-model="factura.emisora" @change="update()">
                                    <option value="">Selecciona una opción...</option>
                                    @foreach ($empresas as $e)
                                        <option value="{{$e->id}}">{{ $e->razon_social}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="country">Metodo de Pago</label>
                                <select class="custom-select d-block w-100" id="forma" name="forma" required=""
                                        v-model="factura.forma">
                                    <option value="">Selecciona una opción...</option>
                                    <option value="03">Transferencia electrónica de fondos</option>
                                    <option value="01">Efectivo</option>
                                    <option value="02">Cheque nominativo</option>
                                    <option value="04">Tarjeta de crédito</option>
                                    <option value="05">Monedero electrónico</option>
                                    <option value="06">Dinero electrónico</option>
                                    <option value="08">Vales de despensa</option>
                                    <option value="12">Dación en pago</option>
                                    <option value="13">Pago por subrogación</option>
                                    <option value="14">Pago por consignación</option>
                                    <option value="15">Condonación</option>
                                    <option value="17">Compensación</option>
                                    <option value="23">Novación</option>
                                    <option value="24">Confusión</option>
                                    <option value="25">Remisión de deuda</option>
                                    <option value="26">Prescripción o caducidad</option>
                                    <option value="27">A satisfacción del acreedor</option>
                                    <option value="28">Tarjeta de débito</option>
                                    <option value="29">Tarjeta de servicios</option>
                                    <option value="30">Aplicación de anticipos</option>
                                    <option value="31">Intermediario pagos</option>
                                    <option value="99">Por definir</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="country">Forma de Pago</label>
                                <select class="custom-select d-block w-100" id="metodo" name="metodo" required=""
                                        v-model="factura.metodo">
                                    <option value="">Selecciona una opción...</option>
                                    <option value="PUE">Pago en una sola exhibición</option>
                                    <option value="PPD">Pago en parcialidades o diferido</option>
                                </select>

                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="country">Tipo de Comprobante</label>
                                <select class="custom-select d-block w-100" required="" name="tipo_comprobante"
                                        id="selecti" v-model="factura.tipo_comprobante">
                                    <option value="">Selecciona una opción...</option>
                                    <option value="I">Ingresos</option>
                                    <option value="E">Egresos</option>
                                    <option value="P">Pagos</option>
                                </select>
                            </div>
                            <div class="mb-3;"
                                 v-if="factura.tipo_comprobante == 'E' || factura.tipo_comprobante == 'P'">
                                <div class="col-md-6 mb-3">
                                    <label for="folio_fiscal">Folio Fiscal </label>
                                    <input type="text" name="folio_relacionado" class="form-control d-block w-100"
                                           placeholder="Folio Fiscal" v-model="factura.folio_relacionado">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_relacion">Tipo </label>
                                    <select name="tipo_relacion" class="custom-select d-block w-100"
                                            v-model="factura.tipo_relacion">
                                        <option value="">Selecciona un Tipo de Relacion...</option>
                                        <option value="01">Nota de crédito de los documentos relacionados</option>
                                        <option value="02">Nota de débito de los documentos relacionados</option>
                                        <option value="03">Devolución de mercancía sobre facturas o traslados previos
                                        </option>
                                        <option value="04">Sustitución de los CFDI previos</option>
                                        <option value="05">Traslados de mercancías facturados previamente</option>
                                        <option value="06">Factura generada por los traslados previos</option>
                                        <option value="07">CFDI por aplicación de anticipo</option>
                                        <option value="08">Factura generada por pagos en parcialidades</option>
                                        <option value="09">Factura generada por pagos diferidos</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3" v-if="factura.tipo_comprobante == 'E' || factura.tipo_comprobante == 'P'">
                                <div class="col-md-12 mb-3">
                                    <label for="tipo_relacion">Fecha Pago </label>
                                    <input type="datetime-local" name="fecha_pago" class="form-control"
                                           value="2020-01-24T20:36:20" step="1" v-model="factura.fecha_pago">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Monto</label>
                                        <input type="text" name="monto" class="form-control" placeholder="Monto"
                                               v-model="factura.monto">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Folio</label>
                                        <input type="text" name="folio" class="form-control" placeholder="Folio"
                                               v-model="factura.folio">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Importe Pagado</label>
                                        <input type="text" name="importe_pagado" class="form-control"
                                               placeholder="Importe Pagado" v-model="factura.importe_pagado">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Numero Parcialidad</label>
                                        <input type="text" name="num_parcialidad" class="form-control"
                                               placeholder="Num Parcialidad" v-model="factura.num_parcialidad">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Importe Saldo Anterio</label>
                                        <input type="text" name="importe_saldo_anterior" class="form-control"
                                               placeholder="Importe Saldo Anterior"
                                               v-model="factura.importe_saldo_anterior">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_relacion">Importe Saldo Insoluto</label>
                                        <input type="text" name="importe_saldo_insolu" class="form-control"
                                               placeholder="Importe Saldo Insoluto"
                                               v-model="factura.importe_saldo_insoluto">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-dark" v-if="!parcial_2"
                                        v-on:click='parcial_2 = true'>Mostrar
                                </button>
                                <div v-if="parcial_2">
                                    <fieldset>
                                        <legend>2da Parcialidad</legend>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Folio Fiscal</label>
                                                <input type="text" name="folio_relacionado_2" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.folio_relacionado_2">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Metodo de Pago</label>
                                                <select name="metodo_2" id="inputEst" class="form-control"
                                                        title="Metodo de Pago" v-model="factura.metodo_2">
                                                    <option value="">Selecciona una opcion...</option>
                                                    <option value="PUE">Pago en una sola exhibición</option>
                                                    <option value="PPD">Pago en parcialidades o diferido</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Folio </label>
                                                <input type="text" name="folio_2" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.folio_2">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Pagado</label>
                                                <input type="text" name="importe_pagado_2" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.importe_pagado_2">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Numero Parcialidad</label>
                                                <input type="text" name="num_parcialidad_2" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.num_parcialidad_2">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Saldo Anterior</label>
                                                <input type="text" name="importe_saldo__anterior_2"
                                                       placeholder="Folio Fiscal" class="form-control"
                                                       v-model="factura.importe_saldo_anterior_2">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Saldo Insoluto</label>
                                                <input type="text" name="importe_saldo_insoluto_2"
                                                       placeholder="Folio Fiscal" class="form-control"
                                                       v-model="factura.importe_saldo_insoluto_2">
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <button type="button" class="btn btn-dark btn-sm" v-if="parcial_2 && !parcial_3"
                                        v-on:click='parcial_3 = true'>Mas +
                                </button>
                                <div v-if="parcial_3">
                                    <fieldset>
                                        <legend>3da Parcialidad</legend>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="tipo_relacion">Folio Fiscal</label>
                                                <input type="text" name="folio_relacionado_3" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.folio_relacionado_3">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Metodo de Pago</label>
                                                <select name="metodo_3" id="inputEst" class="form-control"
                                                        title="Metodo de Pago" v-model="factura.metodo_3">
                                                    <option value="">Selecciona una opcion...</option>
                                                    <option value="PUE">Pago en una sola exhibición</option>
                                                    <option value="PPD">Pago en parcialidades o diferido</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Folio </label>
                                                <input type="text" name="folio_3" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.folio_3">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Pagado</label>
                                                <input type="text" name="importe_pagado_3" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.importe_pagado_3">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Numero Parcialidad</label>
                                                <input type="text" name="num_parcialidad_3" placeholder="Folio Fiscal"
                                                       class="form-control" v-model="factura.num_parcialidad_3">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Saldo Anterior</label>
                                                <input type="text" name="importe_saldo__anterior_3"
                                                       placeholder="Folio Fiscal" class="form-control"
                                                       v-model="factura.importe_saldo_anterior_3">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_relacion">Importe Saldo Insoluto</label>
                                                <input type="text" name="importe_saldo_insoluto_3"
                                                       placeholder="Folio Fiscal" class="form-control"
                                                       v-model="factura.importe_saldo_insoluto_3">
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3 mt-3">
                                <label for="country">Uso CFDI</label>
                                <select class="custom-select d-block w-100" id="regimen" name="regimen" required=""
                                        v-model="factura.regimen">
                                    <option value="">Selecciona una opción...</option>
                                    <option value="G01">Adquisición de mercancias</option>
                                    <option value="G02">Devoluciones, descuentos o bonificaciones</option>
                                    <option value="G03">Gastos en general</option>
                                    <option value="I01">Construcciones</option>
                                    <option value="I02">Mobilario y equipo de oficina por inversiones</option>
                                    <option value="I03">Equipo de transporte</option>
                                    <option value="I04">Equipo de computo y accesorios</option>
                                    <option value="I05">Dados, troqueles, moldes, matrices y herramental</option>
                                    <option value="I06">Comunicaciones telefónicas</option>
                                    <option value="I07">Comunicaciones satelitales</option>
                                    <option value="I08">Otra maquinaria y equipo</option>
                                    <option value="D01">Honorarios médicos, dentales y gastos hospitalarios.</option>
                                    <option value="D02">Gastos médicos por incapacidad o discapacidad</option>
                                    <option value="D03">Gastos funerales.</option>
                                    <option value="D04">Donativos.</option>
                                    <option value="D05">Intereses reales efectivamente pagados por créditos hipotecarios
                                        (casa habitación).
                                    </option>
                                    <option value="D06">Aportaciones voluntarias al SAR.</option>
                                    <option value="D07">Primas por seguros de gastos médicos.</option>
                                    <option value="D08">Gastos de transportación escolar obligatoria.</option>
                                    <option value="D09">Depósitos en cuentas para el ahorro, primas que tengan como base
                                        planes de pensiones.
                                    </option>
                                    <option value="D010">Pagos por servicios educativos (colegiaturas)</option>
                                    <option value="P01">Por definir</option>
                                    <option value="CP01">Pagos: Emisión de comprobantes de pagos complementarios</option>
                                </select>
                            </div>
                            <input type="hidden" name="usuario" v-model="factura.id">
                        </form>

                    </div>
                </div>


            </div>
        </div>
        <div class="col-md-7">
            <div class="article border mt-3">
                <div class="col-md-12">
                    <h3><strong>Estatus:</strong>
                        <span class="badge badge-warning" v-if="factura.estatus === '0' || factura.estatus === ''">En proceso</span>
                        <span class="badge badge-success" v-if="factura.estatus === '1'">Timbrada</span>
                        <span class="badge badge-danger" v-if="factura.estatus === '2'">Cancelada</span></h3>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Conceptos <a href="#" class="btn btn-sm btn-dark float-right"
                                                            v-on:click.prevent="showConcepto()"><i
                                        class="fa fa-plus"></i> Agregar</a></h5>
                        <table class="responsive-table table">
                            <thead>
                            <tr>
                                <th colspan="7" class="text-center">PRODUCTOS</th>
                            </tr>
                            <tr>
                                <th>Concepto</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Clave</th>
                                <th>Precio unitario</th>
                                <th>Importe</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="concepto in conceptos">
                                <th>@{{concepto.concepto}}</th>
                                <th>@{{concepto.cantidad}}</th>
                                <th>@{{concepto.unidad | tipo_unidad}}</th>
                                <th>@{{concepto.clave}}</th>
                                <th>@{{concepto.monto | toCurrency }}</th>
                                <th>@{{concepto.monto * concepto.cantidad | toCurrency}}</th>
                                <th>
                                    <a href="#" class="btn btn-warning btn-sm"
                                       v-on:click.prevent="editarConcepto(concepto)"> <i class="fas fa-edit"></i></a>
                                    <a href="#" class="btn btn-danger btn-sm"
                                       v-on:click.prevent="deleteConcepto(concepto)"> <i class="fas fa-trash"></i></a>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right">SubTotal</th>
                                <th>@{{ subtotal | toCurrency }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right">IVA</th>
                                <th>@{{ iva | toCurrency }}</th>
                            </tr>
                            <tr v-if="retenido">
                                <th colspan="6" class="text-right">Impuestos Retenidos</th>
                                <th>@{{ suma_retenidos | toCurrency }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right">Total</th>
                                <th>@{{ total | toCurrency }}</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 mt-2 text-right">
                    <button class="btn btn-lg btn-dark" v-on:click="timbrar()">TIMBRAR</button>
                </div>
                <div class="col-12 mt-2 text-right" v-if="cancelaciones.length == 1 ">
                    <button class="btn btn-lg btn-dark" v-on:click="timbrar()">Estatus Cancelación</button>
                    <button class="btn btn-lg btn-dark" v-on:click="timbrar()">Descragar XML Ccancelación</button>


                </div>
                @{{ cancelaciones.length}}
                <div class="col-12 mt-2 text-right" v-if="cancelaciones.length > 1">
                    <button class="btn btn-lg btn-dark" v-on:click="timbrar()">Ver Cancelaciones</button>
                </div>
            </div>
        </div>
        @include('contabilidad.factura.modal_editar_concepto')
        @include('contabilidad.factura.modal_concepto')
    </div>

</div>

<script type="text/javascript">
    new Vue({
        el: '#crudFactura',
        created: function () {
            this.getFactura();
            this.getDetalles();

        },
        data: {
            id_factura: {{ $id_factura }},
            factura: {
                'id': null,
                'creado': null,
                'emisora': null,
                'estatus': null,
                'fecha_pago': null,
                'folio': null,
                'folio_2': null,
                'folio_3': null,
                'folio_relacionado': null,
                'folio_relacionado_2': null,
                'folio_relacionado_3': null,
                'forma': null,
                'id': null,
                'importe_pagado': null,
                'importe_pagado_2': null,
                'importe_pagado_3': null,
                'importe_saldo_anterior': null,
                'importe_saldo_anterior_2': null,
                'importe_saldo_anterior_3': null,
                'importe_saldo_insolu_3': null,
                'importe_saldo_insoluto': null,
                'importe_saldo_insoluto_2': null,
                'metodo': null,
                'metodo_2': null,
                'metodo_3': null,
                'monto': null,
                'num_parcialidad': null,
                'num_parcialidad_2': null,
                'num_parcialidad_3': null,
                'regimen': null,
                'tipo_comprobante': null,
                'tipo_relacion': null,
                'usuario': null
            },
            newConcepto: {
                'id_factura': this.id_factura,
                'cantidad': 1,
                'unidad': null,
                'concepto': null,
                'monto': 0,
                'clave': '1010101',
                'impuesto_retenido': null,
                'iva_considerar': null
            },
            fillConcepto: {
                'id_factura': this.id_factura,
                'id_detalle': null,
                'cantidad': null,
                'unidad': null,
                'concepto': null,
                'monto': null,
                'clave': null,
                'impuesto_retenido': null,
                'iva_considerar': null
            },
            cancelaciones: [],
            conceptos: [],
            errors: [],
            subtotal: 0,
            iva: 0,
            total: 0,
            suma_retenidos: 0,
            iva_considerar: false,
            retenido: false,
            parcial_2: false,
            parcial_3: false
        }, filters: {
            tipo_unidad: function (value) {
                var tipos = {
                    "H87": "Pieza", "EA": "Elemento", "E48": "Unidad de servicio", "ACT": "Actividad",
                    "E51": "Trabajo", "KT": "Kits", "XBX": "Caja", "MON": "Mes", "11": "Equipos", "DAY": "Dia"
                };

                return tipos[value];
            },
            toCurrency: function (value) {
                if (typeof value !== "number") {
                    return value;
                }
                var formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                });
                return formatter.format(value);
            }

        },
        methods: {
            getFactura: function () {
                var url = "{{route('factura.getFactura', '*ID*')}}";
                url = url.replace('*ID*', this.id_factura);
                axios.get(url).then(response => {
                    this.factura = response.data;
                    if (this.factura.tipo_comprobante == "E") {
                        this.parcial_2 = true;
                    }
                    if (this.factura.tipo_comprobante == "P") {
                        this.parcial_2 = true;
                        this.parcial_3 = true;
                    }
                });
            },
            update() {
                url = "{{ route('factura.actualizar') }}";

                axios.post(url, this.factura).then(response => {

                    this.factura = response.data;
                    if (this.factura.tipo_comprobante == "E") {
                        this.parcial_2 = true;
                        this.parcial_3 = false;
                    } else {
                        this.parcial_2 = false;
                        this.parcial_3 = false;
                    }
                    if (this.factura.tipo_comprobante == "P") {
                        this.parcial_2 = true;
                        this.parcial_3 = true;
                    }

                    swal({
                        text: "Se actualizo con exito!",
                        icon: "success",
                        button: "Ok",
                    });
                });
            },
            getDetalles: function () {
                this.borrar();
                var url = "{{ route('factura.detalles')}}";
                url = url + '/' + this.id_factura;
                axios.get(url).then(response => {
                    this.conceptos = response.data;
                    this.sumarConceptos();
                });
            },
            borrar() {
                this.newConcepto = {
                    'id_factura': this.id_factura,
                    'cantidad': 1,
                    'unidad': null,
                    'concepto': null,
                    'monto': 0.01,
                    'clave': '1010101',
                    'impuesto_retenido': 0,
                    'iva_considerar': 0
                };
                this.fillConcepto = {
                    'id_factura': this.id_factura,
                    'id_detalle': null,
                    'cantidad': null,
                    'unidad': null,
                    'concepto': null,
                    'monto': null,
                    'clave': null,
                    'impuesto_retenido': null,
                    'iva_considerar': null
                };
                this.errors = [];
                this.conceptos = [];
            },
            showConcepto() {
                $("#modalCrear").modal('show');
            },
            createConcepto: function () {
                //InsertarNuevaFacturaDetalle.php?id=0
                var url = "{{ route('factura.detalle.crear') }}";
                axios.post(url, {
                    'id_factura': this.id_factura,
                    'cantidad': this.newConcepto.cantidad,
                    'unidad': this.newConcepto.unidad,
                    'concepto': this.newConcepto.concepto,
                    'clave': this.newConcepto.clave,
                    'monto': this.newConcepto.monto,
                    'impuesto_retenido': this.newConcepto.impuesto_retenido,
                    'iva_considerar': this.newConcepto.iva_considerar
                }).then(response => {

                    this.getDetalles();
                    this.borrar();

                    swal("Concepto agregado correctamente", "success");
                    $('#modalCrear').modal('hide');
                    $('.modal-backdrop').remove()
                }).catch(error => {
                    this.errors = error.response.data;
                });
            },
            editarConcepto(concepto) {
                this.fillConcepto.id_factura = concepto.id_factura;
                this.fillConcepto.id_detalle = concepto.id_detalle;
                this.fillConcepto.cantidad = concepto.cantidad;
                this.fillConcepto.unidad = concepto.unidad;
                this.fillConcepto.clave = concepto.clave;
                this.fillConcepto.concepto = concepto.concepto;
                this.fillConcepto.monto = concepto.monto;
                this.fillConcepto.impuesto_retenido = concepto.impuesto_retenido;
                this.fillConcepto.iva_considerar = concepto.iva_considerar;
                $('#modalEditar').modal('show');
            },
            updateConcepto() {
                var url = "{{ route('factura.detalle.update') }}";
                axios.post(url, {
                    'id_factura': this.id_factura,
                    'id_detalle': this.fillConcepto.id_detalle,
                    'cantidad': this.fillConcepto.cantidad,
                    'unidad': this.fillConcepto.unidad,
                    'concepto': this.fillConcepto.concepto,
                    'clave': this.fillConcepto.clave,
                    'monto': this.fillConcepto.monto,
                    'impuesto_retenido': this.fillConcepto.impuesto_retenido,
                    'iva_considerar': this.fillConcepto.iva_considerar
                }).then(response => {
                    console.log(response.data);
                    this.getDetalles();
                    this.borrar();

                    swal("Concepto agregado correctamente", "success");
                    $('#modalEditar').modal('hide');

                }).catch(error => {
                    this.errors = error.response.data;
                });
            },
            deleteConcepto(concepto) {
                var url = "{{ route('factura.detalle.delete') }}";

                swal({
                    title: "¿Borrar el concepto?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            axios.post(url, {
                                'id_factura': concepto.id_factura,
                                'id_detalle': concepto.id_detalle,
                            }).then(response => {
                                swal("Se elimino con exito", {
                                    icon: "success",
                                });

                                location.reload();
                            }).catch(error => {
                                swal("El Concepto no se pudo eliminar, intente de nuevo", {
                                    icon: "warning",
                                });
                            });


                        } else {
                            swal("La accion fue cancelada!");
                        }
                    });


            },
            timbrar() {
                var url = "";
                switch (this.factura.tipo_comprobante) {

                    case 'I' :
                        var url = "{{ route('timbrar.factura')}}";
                        url = url + '/' + this.id_factura;
                        break;
                    case 'E' :
                        var url = "{{ route('timbrar.factura')}}";
                        url = url + '/' + this.id_factura;
                        break;
                    case 'P' :
                        var url = "{{ route('timbrar.factura')}}";
                        url = url + '/' + this.id_factura;
                        break;
                    default :
                        console.log('se va a 04/CFDI');
                        break;
                }
                window.location.href = url;
            },
            sumarConceptos() {
                this.iva = 0;
                this.total = 0;
                this.subtotal = 0;
                this.suma_retenidos = 0;
                this.retenido = false;
                this.conceptos.forEach(c => {
                    if (c.impuesto_retenido == 1) {
                        this.retenido = true;
                    }
                    this.subtotal += c.monto * c.cantidad;
                    if (c.iva_considerar == 1) {
                        this.iva += this.subtotal * .16;
                    }
                });
                if (this.retenido == 1) {
                    this.suma_retenidos += (this.subtotal * 0.06);
                }
                this.total += this.subtotal + this.iva - this.suma_retenidos;
            }
        }

    });
</script>