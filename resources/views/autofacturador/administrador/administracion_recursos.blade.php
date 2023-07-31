@extends('layouts.principal')
@section('tituloPagina', "Autofactura administración")
@section('content')
    <div class="row d-flex justify-content-end">
        <div class="col-2 my-3 text-right px-1">
            <label for="base_autofacturador" class="my-0" style="line-height: 30px;">Te encuentras en: </label>
        </div>
        <div class="col-3 my-3 px-0">
            <select  name="base_autofacturador" id="base_autofacturador"  class="form-control" style="width: 100%" >
                <option value="" disabled selected>Seleccione una empresa</option>
            </select>
        </div>
    </div>
    <div id="myGroup">
        <div class="d-flex justify-content-around my-3">
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#empresas-emisoras" role="button" aria-expanded="true" aria-controls="empresas-emisoras">Proveedores</button>
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#etiquetas-emisoras" role="button" aria-expanded="true" aria-controls="etiquetas-emisoras">Giros Empresa</button>
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#productos-servicios" role="button" aria-expanded="true" aria-controls="productos-servicios">Productos y Servicios</button>
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#regimen-fiscal" role="button" aria-expanded="true" aria-controls="regimen-fiscal">Regimen Fiscal</button>
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#unidades" role="button" aria-expanded="true" aria-controls="unidades">Unidades</button>
            <button class="btn btn-warning text-white" type="button" data-toggle="collapse" data-target="#uso_cfdi" role="button" aria-expanded="true" aria-controls="uso_cfdi">Usos de CFDI</button>
        </div>

        <div class="accordion-group">
            <div class="collapse show row mb-5" id="empresas-emisoras">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Proveedores</h3>
                            <div class="col-3">
                                <input class="form-control" style="right:40%" type="text" placeholder="Buscar" id="busquesaGtable_emisoras">
                            </div>
                            @if(Auth::id() == 1583 || Auth::id() == 1558 || Auth::id() == 1626 || Auth::id() == 1670)
                                <button class="btn btn-warning text-white" type="button" onclick="modalShow('emisora')">Nuevo Proveedor</button>
                            @endif
                         </div>
                        <table class="table w-100" id="table-emisoras">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Razon social</th>
                                <th>RFC</th>
                                <th>Domicilio</th>
                                <th>Etiqueta</th>
                                <th>CSD</th>
                                <th style="min-width: 120px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse row mb-5" id="etiquetas-emisoras">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Giro Empresa</h3>
                            <div class="col-3">
                                <input class="form-control" type="text" placeholder="Buscar" id="busquesaGtable_etiquetas_emisoras">
                            </div>
                            <button class="btn btn-warning text-white" type="button" onclick="modalShow('etiqueta_emisora')">Nuevo Giro de Empresa</button>
                        </div>
                        <table class="table w-100" id="table-etiquetas-emisoras">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Etiqueta</th>
                                <th>Descripcion</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse row mb-5" id="productos-servicios">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Productos de Servicio</h3>
                            <div class="col-3">
                                <input class="form-control" type="text" placeholder="Buscar" id="busquesaGtable_productos">
                            </div>
                            <button class="btn btn-warning text-white" type="button" onclick="modalShow('productos_servicios')">Nuevo Producto o Servicio</button>
                        </div>
                        <table class="table w-100" id="table-productos-servicios">
                            <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Descripcion</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse row mb-5" id="regimen-fiscal">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Regimen Fiscal</h3>
                            <div class="col-3">
                                <input class="form-control" type="text" placeholder="Buscar" id="busquesaGtable_regimen_fiscal">
                            </div>
                            <button class="btn btn-warning text-white" type="button" onclick="modalShow('regimen_fiscal')">Nuevo Regimen Fiscal</button>
                        </div>
                        <table class="table w-100" id="table-regimen-fiscal">
                            <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Regimen</th>
                                <th>Descripcion</th>
                                <th>Tipo</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse row mb-5" id="unidades">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Unidades</h3>
                            <div class="col-3">
                                <input class="form-control" type="text" placeholder="Buscar" id="busquesaGtable_unidades">
                            </div>
                            <button class="btn btn-warning text-white" type="button" onclick="modalShow('unidades')">Nueva Unidad</button>
                        </div>
                        <table class="table w-100" id="table-unidades">
                            <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="collapse row mb-5" id="uso_cfdi">
                <div class="col-12">
                    <div class="article border text-center">
                        <div class="mb-3 d-flex justify-content-between">
                            <h3 class="m-0">Uso CFDI</h3>
                            <div class="col-3">
                                <input class="form-control" type="text" placeholder="Buscar" id="busquesaGtable_uso_fdi">
                            </div>
                            <button class="btn btn-warning text-white" type="button" onclick="modalShow('cfdi')">Nuevo Uso de CFDI</button>
                        </div>
                        <table class="table w-100" id="table-uso-cfdi">
                            <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Uso CFDI</th>
                                <th>Personas Fisicas</th>
                                <th>Personas Morales</th>
                                <th style="min-width: 100px">Acciones</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" data-backdrop="static" id="modal" tabindex="-1" aria-labelledby="modal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="tipo_info" hidden></div>
                <div class="modal-body">

                    <form id="form-data" method="POST" enctype="multipart/form-data">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger text-white" onclick="rechazar()">Rechazar</button>
                    <button type="button" class="btn btn-warning text-white" onclick="submitForm()">Enviar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #444;
            line-height: 20px;
        }

        .custom-file-input ~ .custom-file-label::after {
            content: "Elegir";
        }

        .img-thumbnail{
            padding: 0px;
        }
    </style>
@endpush


@push('scripts')
    <script type="module" src="https://unpkg.com/disk-color-picker"></script>
    <script>
        const modal = $('#modal');
        const modal_body = $('#modal .modal-body form');
        const modal_title = $('#modal .modal-header .modal-title');
        const tocken = '@csrf';
        const catalogos = [
            {
                element: '#id_cat_etiqueta_emisora',
                catalogo: 'etiqueta-emisora',
            },{
                element: '#regimen_fiscal',
                catalogo: 'regimen-fiscal'
            }
        ];

        function modalShow(tipo){
            let html = '';
            let title = '';
            switch (tipo) {
                case 'sellos_fiscales':
                    html = html_sellos_fiscales;
                    title = 'Sellos Fiscales';
                    break;
                case 'emisora':
                    html = html_form_emisora;
                    title = 'Proveedores';
                    break;
                case 'etiqueta_emisora':
                    html = html_form_etiqueta_emisora;
                    title = 'Giro Empresa';
                    break;
                case 'productos_servicios':
                    html = html_form_productos_servicios;
                    title = 'Productos de Servicio';
                    break;
                case 'regimen_fiscal':
                    html = html_form_regimen_fiscal;
                    title = 'Regimen Fiscal';
                    break;
                case 'unidades':
                    html = html_form_unidades;
                    title = 'Unidades';
                    break;
                case 'cfdi':
                    html = html_form_uso_cfdi;
                    title = 'Uso CFDI';
                    break;
            }
            document.querySelector('#modal .btn.btn-danger.text-white').style.display = 'none';
            modal_body.attr('action', '{{ route('autofacturador.administracion.setRegistroAdmin') }}/' + tipo);
            modal_body.html(html);
            modal_title.html(title);
            fetchCatalogos(catalogos);
            modal.modal('show');
            let info_tipo = document.getElementById('tipo_info');
            info_tipo.innerHTML=tipo;
            if(tipo=='emisora'){
                eventoTeclado('razon_social');
                eventoTeclado('rfc');
                const picker = document.querySelector('disk-color-picker');
                const preview = document.getElementById("preview");
                picker.value = 'rgb(0, 0, 0)';
                preview.style.background = picker.value;
                document.getElementById('colores').value=hexToRgb(picker.value);
                picker.addEventListener('change', () => {
                    preview.style.background = picker.value;
                    document.getElementById('colores').value=hexToRgb(picker.value);
                });
            }else if(tipo=='vendedor'){
                eventoTeclado('nombre_ven');
            }

        }

        const hexToRgb = hex =>
            hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i ,(m, r, g, b) => '#' + r + r + g + g + b + b).substring(1).match(/.{2}/g).map(x => parseInt(x, 16));

        function fileValidation(nombre,tipo) {
            var fileInput = document.getElementById(nombre);
            var filePath = fileInput.value;
            if(tipo == 'zip'){
                let allowedExtensions = /(.zip)$/i;
                if (allowedExtensions.exec(filePath))
                    return false;
                else
                    return true;
            }else if(tipo = 'png'){
                let allowedExtensions = /(.png)$/i;
                if (allowedExtensions.exec(filePath))
                    return false;
                else
                    return true;
            }
           
        }
        
        function camposVacios(tipo) {
            let vacio;
            switch (tipo) {
                case 'sellos_fiscales':
                    vacio = $('#password').val() &&  $('#cer').val() &&  $('#key').val();
                    break;
                case 'emisora':
                    vacio = $('#cp').val() != '' && $('#razon_social').val() != '' && $('#rfc').val() != '' && $('#id_cat_etiqueta_emisora').val() != '';
                    break;
                case 'etiqueta_emisora':
                    vacio = $('#etiqueta').val() != '' && $('#descripcion').val() != '';
                    break;
                case 'productos_servicios':
                    vacio = $('#clave').val() != '' && $('#descripcion').val() != '';
                    break;
                case 'regimen_fiscal':
                    vacio = $('#codigo').val() != '' && $('#regimen').val() != '' && $('#descripcion').val() != '' &&
                        $('#tipo').val() != '';
                    break;
                case 'unidades':
                    vacio = $('#clave').val() != '' && $('#nombre').val() != '';
                    break;
                case 'cfdi':
                    vacio = $('#clave').val() != '' && document.getElementById("uso_cfdi_uno").value != '' &&
                        $('#personas_fisicas').val() != '' && $('#personas_morales').val() != '';
                    break;
            }
            return vacio;
        }
        this.baseAutofacturador();

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

        function modalFill(id, tipo){
            modalShow(tipo);
            if(tipo != 'sellos_fiscales')
                fetch('{{ route('autofacturador.administracion.getRegistroAdmin') }}/'+ tipo + '/' + id)
                    .then(resp => resp.json())
                    .then(resp => {
                        switch (tipo) {
                            case 'emisora':
                                $('#cp').val(resp.cp);
                                $('#domicilio').val(resp.domicilio);
                                $('#id').val(resp.id);
                                $('#id_cat_etiqueta_emisora').val(resp.id_cat_etiqueta_emisora);
                                $('#razon_social').val(resp.razon_social);
                                $('#regimen_fiscal').val(resp.regimen_fiscal);
                                $('#rfc').val(resp.rfc);
                                $('#correo').val(resp.correo);
                                $('#regimen_fiscal').trigger('change');
                                $('#id_cat_etiqueta_emisora').trigger('change');
                                $('#colores').val(resp.colores);
                                const picker = document.querySelector('disk-color-picker');
                                picker.value = `rgb(${resp.colores})`;
                                const preview = document.getElementById("preview");
                                preview.style.background = picker.value;
                                const preview_img= document.getElementById('preview_img');
                                preview_img.setAttribute("src",resp.logo_base64);
                                break;
                            case 'etiqueta_emisora':
                                $('#etiqueta').val(resp.etiqueta);
                                $('#descripcion').val(resp.descripcion);
                                $('#id').val(resp.id);
                                break;
                            case 'productos_servicios':
                                document.getElementById('clave').readOnly= true;
                                $('#clave').val(resp.clave);
                                $('#descripcion').val(resp.descripcion);
                                break;
                            case 'regimen_fiscal':
                                document.getElementById('codigo').readOnly= true;
                                $('#codigo').val(resp.codigo);
                                $('#regimen').val(resp.regimen);
                                $('#descripcion').val(resp.descripcion);
                                $('#tipo').val(resp.tipo);
                                break;
                            case 'unidades':
                                document.getElementById('clave').readOnly= true;
                                $('#clave').val(resp.clave);
                                $('#nombre').val(resp.nombre);
                                break;
                            case 'cfdi':
                                document.getElementById('clave').readOnly= true;
                                $('#clave').val(resp.clave);
                                document.getElementById("uso_cfdi_uno").value=resp.uso_cfdi;
                                if(resp.personas_fisicas) $('#personas_fisicas').prop('checked', true);
                                if(resp.personas_morales) $('#personas_morales').prop('checked', true);
                                break;
                        }
                    });
            else{
                $('#id').val(id);
                let dato=document.getElementById(id);
                let miCelda = dato.getElementsByTagName("td")[6];
                let miDato = miCelda.firstChild.nodeValue;

                if(miDato=='Si'){
                    alertify.alert('Existe un CSD registrado', function(){
                        alertify.message('OK');
                    });
                }

            }

        }

        function fetchCatalogos(catalogos){
            catalogos.forEach(catalogo => {
                fetch('{{ route('autofacturador.catalogos')}}/' + catalogo.catalogo)
                    .then(response => response.json())
                    .then(response => {
                        $(catalogo.element).select2({
                            data : $.map(response, function (item) {
                                return {
                                    id: item.clave,
                                    text: item.text
                                }
                            }),
                        });
                    });
            });
        }

        function submitForm(){
            const formElement = document.querySelector('#modal .modal-body form');
            const formData = new FormData(formElement);
            let info_tipo = document.getElementById('tipo_info');

            if(!camposVacios(info_tipo.innerHTML)){
               return  alertify.alert('Llena todos los campos', function(){
                        alertify.message('OK');
               });
            }

            if(info_tipo.innerHTML=='emisora' && validar_correo($('#correo').val())==null){
                return alertify.alert('Correo Invalido');
            }

            if(info_tipo.innerHTML=='emisora' && fileValidation('kit_fiscal','zip') && document.getElementById('kit_fiscal').value != ''){
                return alertify.alert('Solo archivos zip o esta vacio el campo Kit Fiscal');
            }else if(info_tipo.innerHTML=='emisora' && fileValidation('logo_base64','png') && document.getElementById('logo_base64').value != ''){
                return alertify.alert('Solo archivos png o esta vacio el campo Logo');
            }

            fetch(formElement.action, {
                method: 'post',
                body: formData,
            }).then(res => res.json())
            .then(res => {
                if(!res[0].status && info_tipo.innerHTML=='sellos_fiscales'){
                    alertify.alert('Error', res[0].mensaje);
                }
                if(!res[0].status && info_tipo.innerHTML=='emisora'){
                    alertify.alert('Error', res[0].mensaje);
                }
                modal.modal('hide');

                table_emisoras.ajax.reload();
                table_etiquetas_emisoras.ajax.reload();
                table_productos_servicios.ajax.reload();
                table_regimen_fiscal.ajax.reload();
                table_unidades.ajax.reload();
                table_uso_cfdi.ajax.reload();
            });
        }

        $('#busquesaGtable_emisoras').on( 'keyup', function () {table_emisoras.search( this.value ).draw();});
        $('#busquesaGtable_etiquetas_emisoras').on( 'keyup', function () {table_etiquetas_emisoras.search( this.value ).draw();});        
        $('#busquesaGtable_regimen_fiscal').on( 'keyup', function () {table_regimen_fiscal.search( this.value ).draw();});
        $('#busquesaGtable_productos').on( 'keyup', function () {table_productos_servicios.search( this.value ).draw();});
        $('#busquesaGtable_unidades').on( 'keyup', function () {table_unidades.search( this.value ).draw();});
        $('#busquesaGtable_uso_fdi').on( 'keyup', function () {table_uso_cfdi.search( this.value ).draw();});

        function eliminarEmpresa(id, razon_social){
            alertify.confirm(`¿Desea eliminar a la empresa "${razon_social}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarEmpresa') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({
                        id: id
                    })
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_emisoras.ajax.reload();
                    });
            });
        }

        function eliminarEtiquetEmisora(id, etiqueta){
            alertify.confirm(`¿Desea eliminar "${etiqueta}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarEtiqueta') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({id: id})
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_etiquetas_emisoras.ajax.reload();
                    });
            });

        }

        function eliminarProductosServicios(clave, item)    {
            alertify.confirm(`¿Desea eliminar "${item}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarProductosServicios') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({
                        clave: clave
                    })
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_productos_servicios.ajax.reload();
                    });
            });

        }

        function eliminarRegimenFiscal(codigo, item){
            alertify.confirm(`¿Desea eliminar "${item}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarRegimenFiscal') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({
                        codigo: codigo
                    })
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_regimen_fiscal.ajax.reload();
                    });
            });

        }

        function eliminarUnidades(clave, item){
            alertify.confirm(`¿Desea eliminar "${item}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarUnidades') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({
                        clave: clave
                    })
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_unidades.ajax.reload();
                    });

            });

        }

        function eliminarUsoCFDI(clave, item){
            alertify.confirm(`¿Desea eliminar "${item}"?`, function(){
                fetch("{{ route('autofacturador.administracion.eliminarUsoCFDI') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": $('input[name="_token"]').val()
                    },
                    body: JSON.stringify({
                        clave: clave
                    })
                }).then(res => res.json())
                    .then(res => {
                        if(res)
                            table_uso_cfdi.ajax.reload();
                    });
            });

        }

        function actualizaLabel(el){
            let fileName = document.querySelector('#'+el.id).files[0].name;
            document.querySelector(`#form-data label[for=${el.id}]`).innerText = fileName;

            if(document.querySelector('#'+el.id).files[0].type == 'image/png'){
                let prev= document.getElementById('preview_img');
                var imgCodified = URL.createObjectURL(document.querySelector('#'+el.id).files[0]);
                prev.setAttribute('src',imgCodified);
            }
        }

        function eventoTeclado(id_elemento) {
            const entradaInput = document.getElementById(id_elemento);
            entradaInput.addEventListener('keyup', cambiarMayusculas);
        }

        function cambiarMayusculas(elemento) {
            let texto = elemento.target.value;
            elemento.target.value = texto.toUpperCase();
        }

        function llenarConQR(el){
            if(el.value && el.value.includes('https://siat.sat.gob.mx/app/qr/faces/pages/mobile/validadorqr.jsf')){
                fetch('{{ route('autofacturador.administracion.scapingEmpresaEmisora') }}', {
                    method: "POST",
                    headers: {
                        "Content-type": "application/json;charset=UTF-8",
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ url: el.value })
                }).then(res => res.json())
                .then(res => {
                    $('#razon_social').val(res.DenominacinoRaznSocial);
                    $('#rfc').val(el.value.split('_')[1]);
                    $('#cp').val(res.CP);
                    $('#domicilio').val(`${res.Tipodevialidad} ${res.Nombredelavialidad} ${res.Nmerointerior} ${res.Nmeroexterior}, ${res.Colonia}, ${res.CP}, ${res.Municipioodelegacin}, ${res.EntidadFederativa}`);
                    $('#regimen_fiscal').val(res.codigo_regimen);
                    $('#regimen_fiscal').trigger('change');
                })
            }
        }

        const table_emisoras = $('#table-emisoras').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'empresas-emisoras-edit') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'id'},
                {data: 'razon_social'},
                {data: 'rfc'},
                {data: 'domicilio'},
                {data: 'etiqueta'},
                {data: 'sellos_fiscales',
                    render: function ( data, type, row, meta ) {
                    let html='No';
                            if(row.sellos_fiscales==1){
                                html='Si';
                            }
                        return `${html}`;
                    }
                },
                {
                    className: 'd-flex',
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `
                            @if(Auth::id() == 1583 || Auth::id() == 1558 || Auth::id() == 1626 || Auth::id() == 1670)
                                <button class="btn text-white mr-1" onclick="modalFill(${row.id}, 'emisora')"><i class="editar-icon button-style-icon"></i></button>
                                <button class="btn text-white mr-1" onclick="eliminarEmpresa(${row.id}, '${row.razon_social}')"><i class="eliminar-icon button-style-icon"></i></button>
                                <button class="btn text-white" onclick="modalFill(${row.id}, 'sellos_fiscales')"><i class="key-icon button-style-icon"></i></button>
                            @else
                                <button class="btn text-white btn-secondary" disabled>Sin permisos</button>
                            @endif
                            `;
                    }
                }
            ],
            rowId: 'id'
        });

        const table_etiquetas_emisoras = $('#table-etiquetas-emisoras').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'cat-etiquetas-emisoras') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'id'},
                {data: 'etiqueta'},
                {data: 'descripcion'},
                {
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `<button class="btn text-white" onclick="modalFill(${row.id}, 'etiqueta_emisora')"><i class="editar-icon button-style-icon"></i></button>
                            <button class="btn text-white" onclick="eliminarEtiquetEmisora(${row.id}, '${row.etiqueta}')"><i class="eliminar-icon button-style-icon"></i></button>`;
                    }
                }
            ],
            rowId: 'id'
        });

        const table_productos_servicios = $('#table-productos-servicios').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'cat-productos-servicios') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'clave'},
                {data: 'descripcion'},
                {
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `<button class="btn text-white" onclick="modalFill(${row.clave}, 'productos_servicios')"><i class="editar-icon button-style-icon"></i></button>
                            <button class="btn text-white" onclick="eliminarProductosServicios(${row.clave}, '${row.clave}')"><i class="eliminar-icon button-style-icon"></i></button>`;
                    }
                }
            ],
            rowId: 'id'
        });

        const table_regimen_fiscal = $('#table-regimen-fiscal').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'cat-regimen-fiscal') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'codigo'},
                {data: 'regimen'},
                {data: 'descripcion'},
                {
                    data: 'tipo',
                    render: function ( data, type, row, meta ){
                        return row.tipo[0].toUpperCase() + row.tipo.substring(1);
                    }
                }, {
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `<button class="btn text-white" onclick="modalFill(${row.codigo}, 'regimen_fiscal')"><i class="editar-icon button-style-icon"></i></button>
                            <button class="btn text-white" onclick="eliminarRegimenFiscal(${row.codigo}, '${row.codigo}')"><i class="eliminar-icon button-style-icon"></i></button>`;
                    }
                }
            ],
            rowId: 'id'
        });

        const table_unidades = $('#table-unidades').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'cat-unidades') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'clave'},
                {data: 'nombre'},
                {
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `<button class="btn text-white" onclick="modalFill('${row.clave}', 'unidades')"><i class="editar-icon button-style-icon"></i></button>
                            <button class="btn text-white" onclick="eliminarUnidades('${row.clave}', '${row.clave}')"><i class="eliminar-icon button-style-icon"></i></button>`;
                    }
                }
            ],
            rowId: 'id'
        });

        const table_uso_cfdi = $('#table-uso-cfdi').DataTable({
            lengthChange: false,
            ajax: "{{ route('autofacturador.catalogos', 'cat-uso-cfdi') }}",
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            },
            columns: [
                {data: 'clave'},
                {data: 'uso_cfdi'},
                {
                    data: 'personas_fisicas',
                    render: function (data, type, row, meta){
                        return (row.personas_fisicas) ? 'Si' : 'No'
                    }
                }, {
                    data: 'personas_morales',
                    render: function (data, type, row, meta){
                        return (row.personas_morales) ? 'Si' : 'No'
                    }
                }, {
                    data: 'acciones',
                    render: function ( data, type, row, meta ) {
                        return `<button class="btn text-white" onclick="modalFill('${row.clave}', 'cfdi')"><i class="editar-icon button-style-icon"></i></button>
                            <button class="btn text-white" onclick="eliminarUsoCFDI('${row.clave}', '${row.clave}')"><i class="eliminar-icon button-style-icon"></i></button>`;
                    }
                }
            ],
            rowId: 'id'
        });

        const html_sellos_fiscales = `
            ${tocken}
            <input type="hidden" name="id" id="id">
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Contraseña:</label>
                    <input class="form-control input-style-custom" type="password" id="password" name="password">
                </div>
            </div>
            <div class="col-12 my-3 px-0">
                <div class="custom-file">
                    <input type="file" class="custom-file-input input-style-custom" id="cer" name="cer" required lang="es" onchange="actualizaLabel(this)" accept=".cer">
                    <label class="custom-file-label" for="cer">Certificado .cer</label>
                </div>
            </div>
            <div class="col-12 my-3 px-0">
                <div class="custom-file">
                    <input type="file" class="custom-file-input input-style-custom" id="key" name="key" required lang="es" onchange="actualizaLabel(this)" accept=".key">
                    <label class="custom-file-label" for="key">Llave .key</label>
                </div>
            </div>
        `;

        const html_form_emisora = `
            <div class="row">
                <div class="col-4 form-group">
                    <label for="">QR de la constancia fiscal</label>
                    <input type="text" class="form-control input-style-custom" onfocusout="llenarConQR(this)">
                </div>
            </div>
            <div class="">
                ${tocken}
                <input type="hidden" name="id" id="id">
                <div class="row">
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Razón social <small>(Sin Sociedades)</small>:</label>
                        <input class="form-control input-style-custom" type="text" id="razon_social" name="razon_social">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="recipient-name" class="col-form-label">RFC:</label>
                        <input type="text" class="form-control input-style-custom" id="rfc" name="rfc">
                    </div>
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Codigo Postal:</label>
                        <input class="form-control input-style-custom" type="text" id="cp" name="cp">
                    </div>
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Regimen fiscal:</label>
                        <select class="form-control input-style-custom" type="text" id="regimen_fiscal" name="regimen_fiscal"></select>
                    </div>
                </div>
                <div class="row">
                <div class="form-group col">
                        <label for="message-text" class="col-form-label">Correo:</label>
                        <input class="form-control input-style-custom" type="email" id="correo" name="correo">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Domicilio(Opcional):</label>
                        <input class="form-control input-style-custom" type="text" id="domicilio" name="domicilio">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Banco(Opcional):</label>
                        <input type="text" class="form-control input-style-custom" id="banco" name="banco">
                    </div>
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Clave interbancaria(Opcional):</label>
                        <input type="text" class="form-control input-style-custom" id="clave" name="clave">
                    </div>
                    <div class="form-group col">
                        <label for="message-text" class="col-form-label">Cuenta(Opcional):</label>
                        <input type="text" class="form-control input-style-custom" id="cuenta" name="cuenta">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-4 mb-0">
                        <label for="message-text" class="col-form-label">Etiqueta:</label>
                        <select class="form-control input-style-custom" type="text" id="id_cat_etiqueta_emisora" name="id_cat_etiqueta_emisora"></select>
                    </div>
                    <div class="col-4 align-self-end">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input input-style-custom" id="kit_fiscal" name="kit_fiscal" required lang="es" onchange="actualizaLabel(this)" accept="application/zip">
                            <label class="custom-file-label" for="kit_fiscal">Kit Fiscal</label>
                        </div>
                    </div>
                    <div class="col-4 align-self-end">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input input-style-custom" id="logo_base64" name="logo_base64" required lang="es" onchange="actualizaLabel(this)" accept="image/png">
                            <label class="custom-file-label" for="logo_base64">Logo</label>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row text-center">
                    <div class="form-group col-4 mb-0">
                        <input type="text" id="colores" name="colores" hidden>
                        <label for="" class="font-weight-bold">Selecciona un color para mostrar en el PDF de CFDI emitido</label>
                        <disk-color-picker><disk-color-picker>
                    </div>
                    <div class="col-4 mb-0 align-self-center">
                        <img id="preview" class="card-img-bottom img-thumbnail" style="width: 120px; height: 120px;">
                    </div>
                    <div class="form-group col-4 mb-0 align-self-center">
                        <img id="preview_img" class="card-img-bottom" alt="Imagen Logo" style="width: 50%;border: 1px solid lightgrey;">
                    </div>
                </div>
            <div>`;

        const html_form_etiqueta_emisora = `
            ${tocken}
            <input type="hidden" name="id" id="id">
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Etiqueta:</label>
                    <input class="form-control input-style-custom" type="text" id="etiqueta" name="etiqueta">
                </div>
                <div class="form-group col">
                    <label for="recipient-name" class="col-form-label">Descripcion:</label>
                    <input type="text" class="form-control input-style-custom" id="descripcion" name="descripcion">
                </div>
            </div>`;

        const html_form_productos_servicios = `
            ${tocken}
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Clave:</label>
                    <input class="form-control input-style-custom" type="text" id="clave" name="clave">
                </div>
                <div class="form-group col">
                    <label for="recipient-name" class="col-form-label">Descripcion:</label>
                    <input type="text" class="form-control input-style-custom" id="descripcion" name="descripcion">
                </div>
            </div>`;

        const html_form_regimen_fiscal = `
            ${tocken}
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Codigo:</label>
                    <input class="form-control input-style-custom" type="text" id="codigo" name="codigo">
                </div>
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Regimen:</label>
                    <input class="form-control input-style-custom" type="text" id="regimen" name="regimen">
                </div>
                <div class="form-group col-12">
                    <label for="recipient-name" class="col-form-label">Descripcion:</label>
                    <input type="text" class="form-control input-style-custom" id="descripcion" name="descripcion">
                </div>
                <div class="form-group col-6">
                    <label for="recipient-name" class="col-form-label">Persona tipo:</label>
                    <select name="tipo" id="tipo" class="form-control input-style-custom">
                        <option value="fisica">Fisica</option>
                        <option value="moral">Moral</option>
                    </select>
                </div>
            </div>`;

        const html_form_unidades  = `
            ${tocken}
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Clave:</label>
                    <input class="form-control input-style-custom" type="text" id="clave" name="clave">
                </div>
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Nombre:</label>
                    <input class="form-control input-style-custom" type="text" id="nombre" name="nombre">
                </div>
            </div>`;

        const html_form_uso_cfdi  = `
            ${tocken}
            <div class="row">
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Clave:</label>
                    <input class="form-control input-style-custom" type="text" id="clave" name="clave">
                </div>
                <div class="form-group col-12">
                    <label for="message-text" class="col-form-label">Uso CFDI:</label>
                    <input class="form-control input-style-custom" type="text" id="uso_cfdi_uno" name="uso_cfdi">
                </div>
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Personas Fisicas:</label>
                    <input class="form-control input-style-custom" type="checkbox" id="personas_fisicas" name="personas_fisicas" value='1'>
                </div>
                <div class="form-group col">
                    <label for="message-text" class="col-form-label">Personas Morales:</label>
                    <input class="form-control input-style-custom" type="checkbox" id="personas_morales" name="personas_morales" value='1'>
                </div>
            </div>`;

        var $myGroup = $('#myGroup');

        $myGroup.on('show.bs.collapse','.collapse', function() {
            $myGroup.find('.collapse.show').collapse('hide');
        });

        const validar_correo = (correo='')=>{
            const expresion=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            let valor=correo.match(expresion);
            return valor;
        }

    </script>
@endpush
