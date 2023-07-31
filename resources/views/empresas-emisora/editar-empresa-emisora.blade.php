<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
  <style type="text/css">
  .top-line-black {
    width: 19%;}
  </style>
@include('includes.navbar')
<div class="container">
@include('includes.header',['title'=>'Editar empresa Pagadora/Emisora',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'empresae.empresaemisora'])

   	<div class="col-md-12">

		<br>
		<br>
   	</div>

   	<div class="article border">
      <form method="post" id="submit_empresa" action="{{ route('empresae.actualizarempresae') }}" enctype="multipart/form-data">
      @csrf
        @foreach ($empresas as $empresa)
     		<div class="row">
     			<div class="col-md-4">
     				<div class="row">
     					<div class="font-weight-bold center">Empresa Emisora</div>
     				</div>
     				<br>
                <label for="razon_social" class="mb-0">Razon social:</label>
                <input type="hidden" name="idempresa" value="{{ $empresa->id }}">
                <input type="text" name="razon_social" id="razon_social"  value="{{ $empresa->razon_social }}" class="form-control  input-style-custom mb-3" placeholder="Razon Social" required>

                <label for="rfc" class=" mb-0">RFC:</label>
                <input type="text" name="rfc" id="rfc"  value="{{ $empresa->rfc }}" class="form-control  input-style-custom mb-3" placeholder="RFC" minlength="11" maxlength="16" required>

                <label for="direccion" class="mb-0">Dirección:</label>
                <input type="text" name="direccion" id="direccion"  value="{{ $empresa->direccion }}" class="form-control  input-style-custom mb-3" placeholder="Dirección" required>

                <label for="cp" class="mb-0">Código postal:</label>
                <input type="number" name="cp" id="cp"  value="{{ $empresa->cp }}" class="form-control  input-style-custom mb-3" placeholder="Código Postal" required>

                <label for="representante_legal" class="mb-0">Representante legal:</label>
                <input type="text" name="representante_legal" id="representante_legal"  value="{{ $empresa->representante_legal }}" class="form-control  input-style-custom mb-3" placeholder="Respresentante Legal" required>

                <div class="row">
                  <div class="center">Datos contables</div>
                </div>

                <label for="num_cuenta_contable" class="mb-0">Numero de cuenta:</label>
                <input type="text" name="num_cuenta_contable" id="num_cuenta_contable"  value="{{ $empresa->num_cuenta_contable }}" class="form-control  input-style-custom mb-3" placeholder="Numero de Cuenta" required>

                <label for="concepto_nomina_contable" class="mb-0">Concepto nomina:</label>
                <input type="text" name="concepto_nomina_contable" id="concepto_nomina_contable"  value="{{ $empresa->concepto_nomina_contable }}" class="form-control  input-style-custom mb-3" placeholder="Concepto Nomina" required>

                <label for="user_timbre" class="mb-0">Elige el usuario de timbrado:</label>
                <select name="user_timbre" id="user_timbre" class="form-control  input-style-custom mb-3" required>
                      <option value="" selected disabled>Elige el Usuario de Timbrado</option>
                      @foreach ($timbrados as $timbrado)
                          <option value="timbres{{$timbrado->id}}" {{ ( $empresa->user_timbre == 'timbres'.$timbrado->id) ? 'selected' : '' }}  >{{$timbrado->razon_social}}</option>
                      @endforeach
                </select>
     			</div>
     			<div class="col-md-4">
     				<div class="row">
     					<div class="font-weight-bold center">Banco</div>
     				</div>
     				<br>
                <label for="banco" class="mb-0">Banco:</label>
                <select name="banco" id="banco" class="form-control  input-style-custom mb-3">
                    <option value="0" selected disabled {{ ( $empresa->banco == 0) ? 'selected' : '' }}>Banco</option>
                      @foreach ($bancos as $banco)
                          <option value="{{$banco->id}}" {{ ( $empresa->banco == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                      @endforeach
                </select>

                <label for="cuenta_bancaria" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria" id="cuenta_bancaria"  value="{{ $empresa->cuenta_bancaria }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

                <label for="clave_emisora" class="mb-0">Clave emisora:</label>
                <input type="text" name="clave_emisora" id="clave_emisora"  value="{{ $empresa->clave_emisora }}" class="form-control  input-style-custom mb-3" placeholder="Clave emisora">

                <label for="banco2" class="mb-0">Banco 2:</label>
                <select name="banco2" id="banco2" class="form-control  input-style-custom mb-3">
                    <option value="" selected disabled>Banco 2</option>
                    @foreach ($bancos as $banco)
                        <option value="{{$banco->id}}" {{ ( $empresa->banco2 == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                    @endforeach
                </select>

                <label for="cuenta_bancaria2" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria2" id="cuenta_bancaria2"  value="{{ $empresa->cuenta_bancaria2 }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta Bancaria">

                <label for="banco3" class="mb-0">Banco 3:</label>
                <select name="banco3" id="banco3" class="form-control  input-style-custom mb-3">
                  <option value="" selected disabled>Banco 3</option>
                  @foreach ($bancos as $banco)
                      <option value="{{$banco->id}}" {{ ( $empresa->banco3 == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                  @endforeach
                </select>

                <label for="cuenta_bancaria3" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria3" id="cuenta_bancaria3"  value="{{ $empresa->cuenta_bancaria3 }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">
     			</div>
     			<div class="col-md-4">

     				<div class="row">
     					<div class="font-weight-bold center">Sindical</div>
     				</div>
     				<br>

     				{{-- <div class="row" id="cont-sindical"> --}}

                <label for="banco_sind" class="mb-0">Banco:</label>
                <select name="banco_sind" id="banco_sind" class="form-control  input-style-custom mb-3">
                  <option value="" disabled selected>Banco</option>
                  @foreach ($bancos as $banco)
                    <option value="{{$banco->id}}" {{ ( $empresa->banco_sind == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                  @endforeach
                </select>

                <label for="cuenta_bancaria_sind" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria_sind" id="cuenta_bancaria_sind"  value="{{ $empresa->cuenta_bancaria_sind }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

                <label for="clave_emisora_sind" class="mb-0">Clave emisora:</label>
                <input type="text" name="clave_emisora_sind" id="clave_emisora_sind"  value="{{ $empresa->clave_emisora_sind }}" class="form-control  input-style-custom mb-3" placeholder="Clave emisora">

                <label for="banco_sind2" class="mb-0">Banco 2:</label>
                <select name="banco_sind2" id="banco_sind2" class="form-control  input-style-custom mb-3">
                  <option value="" disabled selected>Banco 2</option>
                  @foreach ($bancos as $banco)
                    <option value="{{$banco->id}}" {{ ( $empresa->banco_sind2 == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                  @endforeach
                </select>

                <label for="cuenta_bancaria_sind2" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria_sind2" id="cuenta_bancaria_sind2"  value="{{ $empresa->cuenta_bancaria_sind2 }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

                <label for="banco_sind3" class="mb-0">Banco 3:</label>
                <select name="banco_sind3" id="banco_sind3" class="form-control  input-style-custom mb-3">
                  <option value="" disabled selected>Banco 3</option>
                  @foreach ($bancos as $banco)
                    <option value="{{$banco->id}}" {{ ( $empresa->banco_sind3 == $banco->id) ? 'selected' : '' }}>{{$banco->nombre}}</option>
                  @endforeach
                </select>

                <label for="cuenta_bancaria_sind3" class="mb-0">Cuenta bancaria:</label>
                <input type="text" name="cuenta_bancaria_sind3" id="cuenta_bancaria_sind3"  value="{{ $empresa->cuenta_bancaria_sind3 }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

     				{{-- </div> --}}

     			</div>
     		</div>
        @endforeach

    </form>

        <br>
          <div class="row ">
              <div class="col-md-12 text-center">
                <input type="submit" id="edit_empresa" class="center button-style" value="Guardar">
              </div>
          </div>

   	</div>

</div>
@include('includes.footer')
<script type="text/javascript">
  $("#edit_empresa").click(function(){

    var razon_social = document.getElementById("razon_social").value;
    var rfc = document.getElementById("rfc").value;
    var direccion = document.getElementById("direccion").value;
    var cp = document.getElementById("cp").value;
    var representante_legal = document.getElementById("representante_legal").value;
    var num_cuenta_contable = document.getElementById("num_cuenta_contable").value;
    var concepto_nomina_contable = document.getElementById("concepto_nomina_contable").value;
    var user_timbre = document.getElementById("user_timbre").value;

        if(razon_social== "" || rfc == "" || direccion == "" || cp == "" || representante_legal == "" || num_cuenta_contable == "" || concepto_nomina_contable == "" || user_timbre == ""){
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

  function submitForm() { document.getElementById("submit_empresa").submit() }
</script>
