<div class="modal" data-backdrop="static" tabindex="-1" role="dialog" id="usuariosModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="post" id="usuario_form" action="">
                    @csrf
                    <p class="text-center text-secondary">Completa los campos para crear un nuevo usuario, recuerda que todos los campos son obligatorios</p>

                    <input type="text" name="nombre_completo" id="nombre_completo" class="form-control  input-style-custom mb-3"
                           value="{{ old('nombre_completo') }}" placeholder="Nombre completo" autocomplete="off" required>
                    {!! $errors->first('nombre_completo','<p class="text-center text-danger">Error: El campo nombre es requerido y solo acepta letras y espacios</p>') !!}

                    <input type="email" name="email" id="email" class="form-control  input-style-custom mb-3"
                           value="{{ old('email') }}" placeholder="Email" autocomplete="off" required>
                    {!! $errors->first('email','<p class="text-danger">Error: El campo email es requerido</p>') !!}

                    <p class="text-center text-secondary font-size-0-7em">Tu password debe de tener un mínimo de 8 caracteres </p>
                    <input type="password" name="password" id="password" class="form-control  input-style-custom mb-3" placeholder="Password" autocomplete="off" required>
                    {!! $errors->first('password','<p class="text-center text-danger">Error: El campo password es obligatorio y debe tener como mínimo 8 caracteres</p>') !!}

                    <input type="text" name="email_jefe" id="email_jefe" class="form-control  input-style-custom mb-3" placeholder="Email Jefe" required>

                    <input type="text" name="email_ejecutivo" id="email_ejecutivo" class="form-control  input-style-custom mb-3" placeholder="Email ejecutivo" required>

                    <select name="estatus" id="estatus" class="form-control input-style-custom mb-3" required>
                        <option value="" selected disabled>Estatus</option>
                        <option value="1" {{ old('estatus') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('estatus') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    <br>


                    @if(isset(Auth::user()->admin) && Auth::user()->admin && isset(Auth::user()->autofacturador) && Auth::user()->autofacturador)
                        <div class="form-group">
                            <!--Autofacturas-->
                            <select name="autofacturador" id="autofacturador" class="form-control" hidden>
                                <option value="1">Si</option>
                            </select>
                        </div>

                        <input type="text" name="base_autofacturador[]" class="form-control" value="@php echo Auth::user()->clientes->id @endphp" hidden />

                        <div class="" id="doc-autof">
                        </div>

                        @push('scripts')
                            <script>

                                function docsAutofac(el,accion){
                                    let html = '';
                                    if(el.val() == 1 && accion==0){
                                        html= html_vendedor+html_comision+html_pagar_del;
                                        document.querySelector('#doc-autof').innerHTML = html;
                                        baseAutofacturador();
                                        getVendedor();
                                    }else if(el.val() == 1 && accion==1){
                                        html= html_vendedor+html_comision+html_pagar_del;
                                        document.querySelector('#doc-autof').innerHTML = html;
                                    }else{
                                        document.querySelector('#doc-autof').innerHTML='';
                                    }

                                }

                            </script>
                        @endpush
                    @else

                        <select name="admin" id="admin" class="form-control  input-style-custom mb-3" required>
                            <option selected disabled value="">Administrador de HR-System</option>
                            <option value="1" {{ old('admin') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('admin') == '0' ? 'selected' : '' }}>No</option>
                        </select>

                        <select name="autofacturador" id="autofacturador" class="form-control  input-style-custom mb-3" required onchange="docsAutofac($(this),0)">
                            <option selected disabled value="">Administrador de Autofacturas</option>
                            <option value="1" {{ old('admin') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('admin') == '0' ? 'selected' : '' }}>No</option>
                        </select>

                        <div class="" id="doc-autof"></div>

                        @push('scripts')
                            <script>

                                function docsAutofac(el,accion){
                                    let html = '';

                                    if(el.val() == 1 && accion==0){
                                        html= html_vendedor+html_timbrar+html_base+html_comision+html_pagar_del;
                                        document.querySelector('#doc-autof').innerHTML = html;
                                        baseAutofacturador();
                                        getVendedor();
                                    }else if(el.val() == 1 && accion==1){
                                        html= html_vendedor+html_timbrar+html_base+html_comision+html_pagar_del;
                                        document.querySelector('#doc-autof').innerHTML = html;
                                    }else{
                                        document.querySelector('#doc-autof').innerHTML='';
                                    }

                                }

                            </script>
                        @endpush
                    @endif
                    <div class="btn btn-dark guardar" id="btn_guardar">Guardar</div>
                    <div class="btn btn-dark actualizar" id="btn_actualizar">actualizar</div>
                </form>

            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        var idUsuario = '';

        let html_vendedor = `
                <select name="id_vendedor" id="vendedor" class="form-control  input-style-custom mb-3" required>
                    <option selected disabled value="">Seleccione Datos del Vendedor</option>
                </select>
<br><br>
    `;

        let html_timbrar = `
            <select name="timbrar" id="timbrar" class="form-control  input-style-custom mb-3" required>
                    <option selected disabled value="">Permiso Timbrar</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
            </select>
    `;

        let html_base = `
            <div class="form-group">
                <select name="base_autofacturador[]" id="base_autofacturador" multiple="multiple" class="form-control input-style-custom mb-3" required></select>
            </div>
    `;

        let html_comision = `
        <div class="input-group mb-3">
          <span class="input-group-text" id="basic-addon1">%</span>
          <input type="number" name="comision" id="comision" class="form-control" placeholder="Comisiòn" aria-label="Comisiòn" aria-describedby="basic-addon1">
        </div>
    `;

        let html_pagar_del = `
        <label for=""  class="form-label">Pagar del:</label>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="pagar_del" id="total_comi" value="total" checked>
               <label class="form-check-label" for="flexRadioDefault2">
                Total
               </label>
             </div>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="pagar_del" id="subtotal_comi" value="subtotal">
               <label class="form-check-label" for="flexRadioDefault2">
                 SubTotal
               </label>
             </div>
             <br>
    `;

        function datosUsuario(id){
            idUsuario = id;
            $('#password').val('');
            $('#nombre_completo').val('');
            $('#email').val('');
            $('#email_jefe').val('');
            $('#email_ejecutivo').val('');
            $('#estatus').val('');
            $('#admin').val('');
            $('#autofacturador').val('');

            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            data = {
                '_token': CSRF_TOKEN
            }

            var url = "{{route('sistema.usuarios.usuario', '*ID*')}}";
            url = url.replace('*ID*', idUsuario);

            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'JSON',
                success: function (response) {
                    if (response.ok == 1) {

                        if (response.usuario.admin && response.usuario.autofacturador) {

                            if (response.usuario.autofacturador) {
                                docsAutofac($('#autofacturador').val(1), 1);
                                asyncSelectedBase(response.base);
                                asyncSelectedVendedor(response.usuario.id_vendedor);
                            } else {
                                docsAutofac($('#autofacturador').val(0), 1);
                                asyncSelectedBase(response.base);
                                asyncSelectedVendedor(response.usuario.id_vendedor);
                            }

                            if (response.usuario.pagar_del == 'total') {
                                document.getElementById("total_comi").checked = true;
                            } else if (response.usuario.pagar_del == 'subtotal') {
                                document.getElementById("subtotal_comi").checked = true;
                            }

                        } else if (response.usuario.admin) {
                            if (response.usuario.autofacturador) {
                                docsAutofac($('#autofacturador').val(1), 1);
                                asyncSelectedBase(response.base);
                                asyncSelectedVendedor(response.usuario.id_vendedor);
                            } else {
                                docsAutofac($('#autofacturador').val(0), 1);
                                asyncSelectedBase(response.base);
                                asyncSelectedVendedor(response.usuario.id_vendedor);
                            }

                            if (response.usuario.autofacturador) {
                                if (response.usuario.pagar_del == 'total') {
                                    document.getElementById("total_comi").checked = true;
                                } else if (response.usuario.pagar_del == 'subtotal') {
                                    document.getElementById("subtotal_comi").checked = true;
                                }
                            }

                        } else if (response.usuario.autofacturador) {
                            docsAutofac($('#autofacturador').val(1), 1);
                            asyncSelectedBase(response.base);
                            asyncSelectedVendedor(response.usuario.id_vendedor);

                            if (response.usuario.pagar_del == 'total') {
                                document.getElementById("total_comi").checked = true;
                            } else if (response.usuario.pagar_del == 'subtotal') {
                                document.getElementById("subtotal_comi").checked = true;
                            }

                        } else {
                            docsAutofac($('#autofacturador').val(3), 3);
                            baseAutofacturador();
                            getVendedor();
                        }

                        $('#nombre_completo').val(response.usuario.nombre_completo);
                        $('#email').val(response.usuario.email);
                        $('#email_jefe').val(response.usuario.email_jefe);
                        $('#email_ejecutivo').val(response.usuario.email_ejecutivo);
                        $('#estatus').val(response.usuario.estatus);
                        $('#timbrar').val(response.usuario.timbrar);
                        $('#admin').val(response.usuario.admin);
                        $('#autofacturador').val(response.usuario.autofacturador);
                        $('#comision').val(response.usuario.comision);
                    } else {
                        alertify.alert('Error', 'Ocurrió un error al cargar los datos del usuario. Intente nuevamente.');
                    }
                }, error: function (jqXHR, textStatus, errorThrown) {
                    alertify.alert('Error', 'Ocurrió un error al cargar los datos del usuario. Intente nuevamente.');
                }
            });

        }

        // boton guardar
        $('#usuariosModal #btn_actualizar').click(function () {
            if ($('#comision').val() < 0) {
                return alertify.alert('Error', 'Comision no puede ser negativa');
            }

            $(this).attr('disabled', true).text('Espere...');
            var url = "{{route('sistema.usuarios.addUpdateUsuario', '*ID*')}}";
            url = url.replace('*ID*', idUsuario);

            let datos=$('#usuario_form').serializeArray();
             
            $.ajax({
                type: "POST",
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: datos,
                dataType: 'JSON',
                success: function (response) {
                    if (response.ok == 1) {
                        $('#usuariosModal').modal('hide');
                        $('.modal-backdrop').hide(); 
                        alertify.success('El usuario se actualizó correctamente.');
                        //table.ajax.reload();
                    } else {
                        alertify.alert('Error', 'Ocurrió un error al actualizar el usuario. Intente nuevamente.');
                    }

                }
            }).done(function () {
                $('#usuariosModal #btn_actualizar').text('Guardar');
            });
        });



        function actualizaLabel(el){
            let fileName = document.querySelector('#'+el.id).files[0].name;
            document.querySelector(`#usuario_form label[for=${el.id}]`).innerText = fileName;
        }


        async function asyncbaseAutofacturador(){
            return new Promise((resolve, reject) => {
                resolve(baseAutofacturador());
            });
        }

        function asyncSelectedBase(value){
            
            asyncbaseAutofacturador().then(v => {
                value.forEach(element => {
                $(`#base_autofacturador option[value='${element.id_autofacturacion}']`).attr("selected", true).trigger('change');
            
            });
            });
        }

        async function asyncbaseVendedor(){
            return new Promise((resolve, reject) => {
                resolve(getVendedor());
            });
        }

        function asyncSelectedVendedor(value){
            asyncbaseVendedor().then(v => {
                $(`#vendedor option[value='${value}']`).attr("selected", true).trigger('change');
            });
        }

        function baseAutofacturador() {
            return fetch('{{ route('autofacturador.getBaseAutofacturador') }}')
                .then(response => response.json())
                .then(response => {
                    $('#base_autofacturador').select2({
                        searchInputPlaceholder: 'Buscar',
                          placeholder: 'Seleccione Bases',
                        data: $.map(response, function (item) {
                            return {
                                text: item.nombre,
                                id: item.id
                            }
                        })
                    });

                });
        }

        function getVendedor() {
            return fetch('{{ route('autofacturador.administracion.getVendedor') }}')
                .then(response => response.json())
                .then(response => {
                    $('#vendedor').select2({
                        searchInputPlaceholder: 'Buscar',
                        data: response.map((item) => {
                            return {
                                text: item.nombre_completo,
                                id: item.id
                            }
                        })
                    });
                });
        }


    </script>
@endpush

