<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<style type="text/css">
.top-line-black {
    width: 19%;}
</style>

<div class="container">
  @include('includes.header',['title'=>'Crear empresa Pagadora/Emisora',
        'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-emisora.png',
        'route'=>'empresae.empresaemisora'])  


        @if(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif


   	<div class="article border">
        <form method="post" id="submit_empresa" action="{{ route('empresae.agregar') }}" enctype="multipart/form-data">
        @csrf
	   		<div class="row">
	   			<div class="col-md-4">
	   				<div class="row">
	   					<div class="font-weight-bold center">Empresa Emisora</div>
	   				</div>
	   				<br>
	                <input type="text" name="razon_social" id="razon_social"  value="{{ old('razon_social') }}" class="form-control  input-style-custom mb-3" placeholder="Razon Social" required>

	                <input type="text" name="rfc" id="rfc"  value="{{ old('rfc') }}" class="form-control  input-style-custom mb-3" placeholder="RFC" minlength="11" maxlength="16" required>

	                <input type="text" name="direccion" id="direccion"  value="{{ old('direccion') }}" class="form-control  input-style-custom mb-3" placeholder="Dirección" required>

	                <input type="number" name="cp" id="cp"  value="{{ old('cp') }}" class="form-control  input-style-custom mb-3" placeholder="Código Postal" required>

	                <input type="text" name="representante_legal" id="representante_legal"  value="{{ old('representante_legal') }}" class="form-control  input-style-custom mb-3" placeholder="Respresentante Legal" required>

	                <div class="row">
	                	<div class="center">Datos contables</div>
	                </div>

	                <input type="text" name="num_cuenta_contable" id="num_cuenta_contable"  value="{{ old('num_cuenta_contable') }}" class="form-control  input-style-custom mb-3" placeholder="Numero de Cuenta" required>

	                <input type="text" name="concepto_nomina_contable" id="concepto_nomina_contable"  value="{{ old('concepto_nomina_contable') }}" class="form-control  input-style-custom mb-3" placeholder="Concepto Nomina" required>

	                <select name="user_timbre" id="user_timbre" class="form-control select-clase input-style-custom mb-3" required>
	                    <option value="" selected disabled>Elige el Usuario de Timbrado</option>
	                    @foreach ($timbrados as $timbrado)
	                        <option value="timbres{{$timbrado->id}}">{{$timbrado->razon_social}}</option>
	                    @endforeach
	                </select>
	   			</div>
	   			<div class="col-md-4">
	   				<div class="row">
	   					<div class="font-weight-bold center">Banco</div>
	   				</div>
	   				<br>
	                <select name="banco" id="banco" class="form-control select-clase input-style-custom mb-3">
	                    <option value="" selected disabled>Banco</option>
	                    @foreach ($bancos as $banco)
	                        <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                    @endforeach
	                </select>
	                <div class="mt-3"></div>
	                <input type="text" name="cuenta_bancaria" id="cuenta_bancaria"  value="{{ old('cuenta_bancaria') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

	                <input type="text" name="clave_emisora" id="clave_emisora"  value="{{ old('clave_emisora') }}" class="form-control  input-style-custom mb-3" placeholder="Clave emisora">

	                <select name="banco2" id="banco2" class="form-control select-clase input-style-custom mb-3">
	                    <option value="" selected disabled>Banco 2</option>
	                    @foreach ($bancos as $banco)
	                        <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                    @endforeach
	                </select>
	                <div class="mt-3"></div>
	                <input type="text" name="cuenta_bancaria2" id="cuenta_bancaria2"  value="{{ old('cuenta_bancaria2') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta Bancaria">

	                <select name="banco3" id="banco3" class="form-control select-clase input-style-custom mb-3">
	                    <option value="" selected disabled>Banco 3</option>
	                    @foreach ($bancos as $banco)
	                        <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                    @endforeach
	                </select>
	                <div class="mt-3"></div>
	                <input type="text" name="cuenta_bancaria3" id="cuenta_bancaria3"  value="{{ old('cuenta_bancaria3') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">
	   			</div>
	   			<div class="col-md-4">

	   				<div class="row">
	   					<div class="font-weight-bold center">Sindical</div>
	   				</div>
	   				<br>

	                    <select name="banco_sind" id="banco_sind" class="form-control select-clase input-style-custom mb-3">
	                        <option value="" disabled selected>Banco</option>
	                        @foreach ($bancos as $banco)
	                            <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                        @endforeach
	                    </select>
	                    <div class="mt-3"></div>
	                	<input type="text" name="cuenta_bancaria_sind" id="cuenta_bancaria_sind"  value="{{ old('cuenta_bancaria_sind') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

	                	<input type="text" name="clave_emisora_sind" id="clave_emisora_sind"  value="{{ old('clave_emisora_sind') }}" class="form-control  input-style-custom mb-3" placeholder="Clave emisora">

	                    <select name="banco_sind2" id="banco_sind2" class="form-control select-clase input-style-custom mb-3">
	                        <option value="" disabled selected>Banco 2</option>
	                        @foreach ($bancos as $banco)
	                            <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                        @endforeach
	                    </select>
	                    <div class="mt-3"></div>
	                	<input type="text" name="cuenta_bancaria_sind2" id="cuenta_bancaria_sind2"  value="{{ old('cuenta_bancaria_sind2') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

	                    <select name="banco_sind3" id="banco_sind3" class="form-control select-clase input-style-custom mb-3">
	                        <option value="" disabled selected>Banco 3</option>
	                        @foreach ($bancos as $banco)
	                            <option value="{{$banco->id}}">{{$banco->nombre}}</option>
	                        @endforeach
	                    </select>
	                    <div class="mt-3"></div>
	                	<input type="text" name="cuenta_bancaria_sind3" id="cuenta_bancaria_sind3"  value="{{ old('cuenta_bancaria_sind3') }}" class="form-control  input-style-custom mb-3" placeholder="Cuenta bancaria">

	   			</div>
	   		</div>

    	</form>
	  	<br>
	    <div class="row ">
	        <div class="col-md-12 text-center">
	            <input type="submit" id="add_empresa" class="center button-style" value="Guardar">
	        </div>
	    </div>

   	</div>

</div>
@include('includes.footer')

<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">

    $(function() {
        $('.select-clase').select2();
    });

	$("#add_empresa").click(function(){
		var razon_social = document.getElementById("razon_social").value;
		var rfc = document.getElementById("rfc").value;
		var direccion = document.getElementById("direccion").value;
		var cp = document.getElementById("cp").value;
		var representante_legal = document.getElementById("representante_legal").value;
		var user_timbre = document.getElementById("user_timbre").value;

		console.log(rfc.length);

        if(razon_social == "" || rfc == "" || direccion == "" || cp == "" || representante_legal == "" ||  user_timbre == "" || rfc.length < 11){
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
