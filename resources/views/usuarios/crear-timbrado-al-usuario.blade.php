<div class="modal" tabindex="-1" role="dialog" id="timbradomodal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Crear timbrado al usuario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>


        <div class="modal-body p-4">

            <form method="post" id="usuarios_timbrado_form" action="{{route('usuarios.agregartimbrado')}}" enctype="multipart/form-data">
            @csrf
                <div class="row">
                    {{-- <div class="col-md-2"></div> --}}
                    <div class="col-md-12">
                        <input type="text" name="razon_social" id="razon_social" class="form-control  input-style-custom"  value="{{ old('razon_social') }}" placeholder="Razon Social" autocomplete="off" required>
                        {!! $errors->first('razon_social','<p class="text-center text-danger">Error: El campo razon social es requerido</p>') !!}
                        <br>
                        <input type="text" name="razon_social_ss" id="razon_social_ss" class="form-control  input-style-custom"  value="{{ old('razon_social_ss') }}" placeholder="Razon Social Sin Sociedades" autocomplete="off" required>
                        {!! $errors->first('razon_social_ss','<p class="text-center text-danger">Error: El campo razon social sin sociedades es requerido</p>') !!}
                        <br>
                        <input type="text" name="rfc" id="rfc" class="form-control  input-style-custom" value="{{ old('rfc') }}" placeholder="RFC" autocomplete="off" required onkeyup="mayusculas(this);">
                        <br>
                        <input type="text" name="regimen_fiscal" id="regimen_fiscal" class="form-control  input-style-custom" value="{{ old('regimen_fiscal') }}" placeholder="Régimen Fiscal" autocomplete="off" required>
                        {!! $errors->first('regimen_fiscal','<p class="text-center text-danger">Error: El campo Régimen Fiscal es requerido</p>') !!}
                        <br>
                        <input type="text" name="certificado" id="certificado" class="form-control  input-style-custom" value="{{ old('certificado') }}" placeholder="Número de certificado" autocomplete="off"  required>
                        {!! $errors->first('certificado','<p class="text-center text-danger">Error: El campo número de certificado es requerido</p>') !!}
    {{--                    <input type="file" name="file_cer" id="file_cer" class="center input-style mb-2" value="{{ old('file_cer') }}" required accept=".cer"> --}}
                        <br>
                        <div class="custom-file form-control  input-style-custom custom-file-container mb-3">
                            <input type="file" class="custom-file-input" name="file_cer" onchange="file('cer')" id="cer" accept=".cer" required>
                            <label class="custom-file-label" for="ine" id="cer_text">Archivo .cer: Certificado de sello digital</label>
                        </div>

                        <div class="custom-file form-control  input-style-custom  custom-file-container mb-3">
                            <input type="file" class="form-control custom-file-input" name="file_key" onchange="file('file_key')" id="file_key" accept=".key" required>
                            <label class="custom-file-label" for="ine" id="file_key_text">Archivo .key: Clave de licencia genérica</label>
                        </div>
                        <br>
                        <input type="text" name="pwd_enc" id="pwd_enc" class="form-control  input-style-custom" value="{{ old('pwd_enc') }}" required placeholder="Password para encriptar" autocomplete="off">
                        {!! $errors->first('pwd_enc','<p class="text-center text-danger">Error: El campo password para encriptar es requerido</p>') !!}
                        <br>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h5>Datos de Finkok</h5>
                            </div>
                        </div>
                        <input type="text" name="user" id="user" class="form-control  input-style-custom" value="{{ old('user') }}" required placeholder="Usuario" autocomplete="off">
                        {!! $errors->first('user','<p class="text-center text-danger">Error: El campo usuario es requerido</p>') !!}
                        <br>
                        <input type="text" name="password" id="password" class="form-control  input-style-custom" value="{{ old('password') }}" required placeholder="Password" autocomplete="off">
                        {!! $errors->first('password','<p class="text-center text-danger">Error: El campo password es requerido</p>') !!}
                        <br>
                        <input type="text" name="servicio" id="servicio" class="form-control  input-style-custom"  required value="https://facturacion.finkok.com/servicios/soap/stamp.wsdl ">
                        {!! $errors->first('servicio','<p class="text-center text-danger">Error: El campo servicio es requerido</p>') !!}
                        <br>
                        <input type="text" name="servicio_cancelacion" id="servicio_cancelacion"  class="form-control  input-style-custom" required value="https://facturacion.finkok.com/servicios/soap/cancel.wsdl ">
                        {!! $errors->first('servicio_cancelacion','<p class="text-center text-danger">Error: El campo servicio cancelacion es requerido</p>') !!}
                    </div>
                    {{-- <div class="col-md-2"></div> --}}
                </div>
                <br>

            </form>

            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn button-style-cancel" data-dismiss="modal" aria-label="Close">Cerrar</button>
                    <button type="button" class="btn button-style" id="add_timbrado">Guardar</button>
                </div>
            </div>

        </div>
        </div>
    </div>
</div>




<script type="text/javascript">
	function mayusculas(e)
	{
	    e.value = e.value.toUpperCase();
	}

    function file(val){

        var text = val+"_text";
        document.getElementById(text).innerHTML = document.getElementById(val).files[0].name;
    }

    $("#add_timbrado").click(function(){
        var razon_social = document.getElementById("razon_social").value;
        var rfc = document.getElementById('rfc').value;
        var certificado = document.getElementById("certificado").value;
        var cer = document.getElementById("cer").value;
        var file_key = document.getElementById("file_key").value;
        var pwd_enc = document.getElementById("pwd_enc").value;
        var user = document.getElementById("user").value;
        var password = document.getElementById("password").value;
        var servicio = document.getElementById("servicio").value;
        var servicio_cancelacion = document.getElementById("servicio_cancelacion").value;

        if(razon_social== "" || rfc== "" || certificado== "" || cer== ""|| file_key== "" || pwd_enc== "" || password== "" || servicio== "" || servicio_cancelacion== ""){
            swal({
              title: "Para continuar debes agregar la información requerida",
            });
        }else{
          swal("Espere un momento, la información esta siendo procesada", {
            icon: "success",
            buttons: false,
          });
          setTimeout(submitForm, 1500);
        }
    });

    function submitForm() { document.getElementById("usuarios_timbrado_form").submit() }

</script>
