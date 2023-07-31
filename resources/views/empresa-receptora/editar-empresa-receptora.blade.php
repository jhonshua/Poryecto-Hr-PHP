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
@include('includes.header',['title'=>'Editar Empresa Receptora/Cliente', 
'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-receptora.png',
 'route'=>'empresar.empresareceptora'])
   
   	<div class="col-md-12">

		<br>
		<br>
   	</div>

   	<div class="article border">
        <form method="post" id="submit_empresa" action="{{ route('empresar.actualizarempresa') }}" enctype="multipart/form-data">
        @csrf
       		<div class="row">
       		@foreach ($empresas as $empresa)
                <input type="hidden" name="id_empresa" value="{{$empresa->id}}">
                <input type="hidden" name="base" value="{{$empresa->base}}">
                <input type="hidden" name="repositorio" value="{{$empresa->repositorio}}">
    	        <div class="col-md-4">
    	        	<strong>Datos generales:</strong><br><br>

                    <label for="razon_social" class="ml-4 mb-0">Razon social:</label>
    	            <input type="text" name="razon_social" id="razon_social"  value="{{ $empresa->razon_social }}" class="form-control input-style-custom mb-3" placeholder="Razon Social" required>
                    {!! $errors->first('razon_social','<p class="text-danger">Error: El campo razon social es requerido</p>') !!}

                    <label for="ins" class="ml-4 mb-0">Instrumento Notarial de la Sociedad:</label>
    	            <input type="text" name="ins" id="ins" value="{{ $empresa->ins }}" class="form-control input-style-custom mb-3" placeholder="Instrumento Notarial de la Sociedad">

                    <label for="num_notaria" class="ml-4 mb-0">No. de Notaria:</label>
    	            <input type="text" name="num_notaria" id="num_notaria" value="{{ $empresa->num_notaria }}" class="form-control input-style-custom mb-3" placeholder="No. de Notaria">

                    <label for="nombre_notario" class="ml-4 mb-0">Nombre del Notario:</label>
    	            <input type="text" name="nombre_notario" id="nombre_notario"  value="{{ $empresa->nombre_notario }}" class="form-control input-style-custom mb-3" placeholder="Nombre del Notario">

                    <label for="lugar_notaria" class="ml-4 mb-0">Lugar de la Notaria:</label>
    	            <input type="text" name="lugar_notaria" id="lugar_notaria"  value="{{ $empresa->lugar_notaria }}" class="form-control input-style-custom mb-3" placeholder="Lugar de la Notaria">

                    <label for="otorgamiento_RdP" class="ml-4 mb-0">Otorgamiento o Revocacion de Poderes:</label>
    	            <input type="text" name="otorgamiento_RdP" id="otorgamiento_RdP" value="{{ $empresa->otorgamiento_rdP }}" class="form-control input-style-custom mb-3" placeholder="Otorgamiento o Revocacion de Poderes">

                    <label for="giro" class="ml-4 mb-0">Giro:</label>
    	            <input type="text" name="giro" id="giro" value="{{ $empresa->giro }}" class="form-control input-style-custom mb-3" placeholder="Giro">

                    <label for="representante_legal" class="ml-4 mb-0">Nombre de Representante Legal:</label>
    	            <input type="text" name="representante_legal" id="representante_legal" value="{{ $empresa->representante_legal }}" class="form-control input-style-custom mb-3" placeholder="Nombre de Representante Legal" required>
                    {!! $errors->first('representante_legal','<p class="text-danger">Error: El campo representante legal social es requerido</p>') !!}
    	        </div>


                <div class="col-md-4">
                	<br><br>
                    <label for="rfc" class="ml-4 mb-0">RFC:</label>
                    <input type="text" name="rfc" id="rfc" value="{{ $empresa->rfc }}" class="form-control input-style-custom mb-3" placeholder="RFC" required maxlength="15" minlength="10">
                    {!! $errors->first('rfc','<p class="text-danger">Error: El campo RFC es requerido</p>') !!}

                    <label for="tasa_vigente" class="ml-4 mb-0">Tasa Vig. Reg. Patron:</label>
                    <input type="text" name="tasa_vigente" id="tasa_vigente" value="{{ $empresa->tasa_vigente }}" class="form-control input-style-custom mb-3" placeholder="Tasa Vig. Reg. Patronal para Imp. sobre Nomina">

                    <label for="identiicacion_oficial" class="ml-4 mb-0">Identificación Oficial de los Representantes Legales:</label>
                    <input type="text" name="identiicacion_oficial" id="identiicacion_oficial" value="{{ $empresa->identiicacion_oficial }}" class="form-control input-style-custom mb-3" placeholder="Identificacion Oficial de los Representantes Legales">

                    <label for="telefono" class="ml-4 mb-0">Telefono de Contacto:</label>
                    <input type="text" name="telefono" id="telefono" value="{{ $empresa->telefono }}" class="form-control input-style-custom mb-3" placeholder="Telefono de Contacto" required>
                    {!! $errors->first('telefono','<p class="text-danger">Error: El campo telefono es requerido</p>') !!}

                    <label for="email" class="ml-4 mb-0">Email:</label>
                    <input type="text" name="email" id="email" value="{{ $empresa->email }}" class="form-control input-style-custom mb-3" placeholder="input-style mb-3" placeholder="Email" required>
                    {!! $errors->first('email','<p class="text-danger">Error: El campo email es requerido</p>') !!}

                    <label for="contacto_directo" class="ml-4 mb-0">Contacto directo:</label>
                    <input type="text" name="contacto_directo" id="contacto_directo" value="{{ $empresa->contacto_directo }}" class="form-control input-style-custom mb-3" placeholder="Contacto Directo">

                    <label for="porcentaje_fondo" class="ml-4 mb-0">% de Fondo de Ahorro:</label>
                    <input type="text" name="porcentaje_fondo" id="porcentaje_fondo"  value="{{ $empresa->porcentaje_fondo }}" class="form-control input-style-custom mb-3" placeholder="% de Fondo de Ahorro">
                </div>

                <div class="col-md-4">
                	<strong>Domicilio:</strong><br><br>

                    <label for="calle_num" class="ml-4 mb-0">Calle y Num Ext e Int:</label>
                    <input type="text" name="calle_num" id="calle_num" value="{{ $empresa->calle_num }}" class="form-control input-style-custom mb-3" placeholder="Calle y Num Ext e Int" required>
                    {!! $errors->first('calle_num','<p class="text-danger">Error: El campo calle y num ext e int es requerido</p>') !!}

                    <label for="colonia" class="ml-4 mb-0">Colonia:</label>
                    <input type="text" name="colonia" id="colonia" value="{{ $empresa->colonia }}" class="form-control input-style-custom mb-3" placeholder="Colonia" required>
                    {!! $errors->first('colonia','<p class="text-danger">Error: El campo colonia es requerido</p>') !!}

                    <label for="delegacion_municipio" class="ml-4 mb-0">Delegacion/Municipio:</label>
                    <input type="text" name="delegacion_municipio" id="delegacion_municipio" value="{{ $empresa->delegacion_municipio }}"  class="form-control input-style-custom mb-3" placeholder="Delegacion/Municipio" required>
                    {!! $errors->first('delegacion_municipio','<p class="text-danger">Error: El campo delegacion/municipio es requerido</p>') !!}

                    <label for="estado" class="ml-4 mb-0">Estado:</label>
                    <select name="estado" id="estado" class="form-control select-clase input-style-custom mb-3" required>
                    	<option value="" disabled selected>Estado</option>
                        <option value="Jalisco" {{ ( $empresa->estado == 'Jalisco') ? 'selected' : '' }}>Jalisco</option>
                        <option value="Aguascalientes" {{ ( $empresa->estado == 'Aguascalientes') ? 'selected' : '' }}>Aguascalientes</option>
                        <option value="Baja California" {{ ( $empresa->estado == 'Baja California') ? 'selected' : '' }}>Baja California </option>
                        <option value="Baja California Sur" {{ ( $empresa->estado == 'Baja California Sur') ? 'selected' : '' }}>Baja California Sur </option>
                        <option value="Campeche" {{ ( $empresa->estado == 'Campeche') ? 'selected' : '' }}>Campeche </option>
                        <option value="Chiapas" {{ ( $empresa->estado == 'Chiapas') ? 'selected' : '' }}>Chiapas </option>
                        <option value="Chihuahua" {{ ( $empresa->estado == 'Chihuahua') ? 'selected' : '' }}>Chihuahua </option>
                        <option value="Coahuila" {{ ( $empresa->estado == 'Coahuila') ? 'selected' : '' }}>Coahuila </option>
                        <option value="Colima" {{ ( $empresa->estado == 'Colima') ? 'selected' : '' }}>Colima </option>
                        <option value="Ciudad de México" {{ ( $empresa->estado == 'Ciudad de México') ? 'selected' : '' }}>Ciudad de México</option>
                        <option value="Durango" {{ ( $empresa->estado == 'Durango') ? 'selected' : '' }}>Durango</option>
                        <option value="Estado de México" {{ ( $empresa->estado == 'Estado de México') ? 'selected' : '' }}>Estado de México</option>
                        <option value="Guanajuato" {{ ( $empresa->estado == 'Guanajuato') ? 'selected' : '' }}>Guanajuato </option>
                        <option value="Guerrero" {{ ( $empresa->estado == 'Guerrero') ? 'selected' : '' }}>Guerrero </option>
                        <option value="Hidalgo" {{ ( $empresa->estado == 'Hidalgo') ? 'selected' : '' }}>Hidalgo </option>
                        <option value="Jalisco" {{ ( $empresa->estado == 'Jalisco') ? 'selected' : '' }}>Jalisco </option>
                        <option value="MICHOACÁN" {{ ( $empresa->estado == 'MICHOACÁN') ? 'selected' : '' }}>Michoacán </option>
                        <option value="Morelos" {{ ( $empresa->estado == 'Morelos') ? 'selected' : '' }}>Morelos </option>
                        <option value="Nayarit" {{ ( $empresa->estado == 'Nayarit') ? 'selected' : '' }}>Nayarit </option>
                        <option value="NUEVO LEÓN" {{ ( $empresa->estado == 'NUEVO LEÓN') ? 'selected' : '' }}>Nuevo León </option>
                        <option value="Oaxaca" {{ ( $empresa->estado == 'Oaxaca') ? 'selected' : '' }}>Oaxaca </option>
                        <option value="Puebla" {{ ( $empresa->estado == 'Puebla') ? 'selected' : '' }}>Puebla </option>
                        <option value="Queretaro" {{ ( $empresa->estado == 'Queretaro') ? 'selected' : '' }}>Querétaro </option>
                        <option value="Quintana Roo" {{ ( $empresa->estado == 'Quintana Roo') ? 'selected' : '' }}>Quintana Roo </option>
                        <option value="San Luis Potosí" {{ ( $empresa->estado == 'San Luis Potosí') ? 'selected' : '' }}>San Luis Potosí</option>
                        <option value="Sinaloa" {{ ( $empresa->estado == 'Sinaloa') ? 'selected' : '' }}>Sinaloa </option>
                        <option value="Sonora" {{ ( $empresa->estado == 'Sonora') ? 'selected' : '' }}>Sonora </option>
                        <option value="Tabasco" {{ ( $empresa->estado == 'Tabasco') ? 'selected' : '' }}>Tabasco </option>
                        <option value="Tamaulipas" {{ ( $empresa->estado == 'Tamaulipas') ? 'selected' : '' }}>Tamaulipas </option>
                        <option value="Tlaxcala" {{ ( $empresa->estado == 'Tlaxcala') ? 'selected' : '' }}>Tlaxcala </option>
                        <option value="Veracruz" {{ ( $empresa->estado == 'Veracruz') ? 'selected' : '' }}>Veracruz </option>
                        <option value="Yucatán" {{ ( $empresa->estado == 'Yucatán') ? 'selected' : '' }}>Yucatán </option>
                        <option value="Zacatecas" {{ ( $empresa->estado == 'Zacatecas') ? 'selected' : '' }}>Zacatecas</option>
                    </select>
                    <div class="mt-2"></div>
                    <label for="codigo_postal" class="ml-4 mb-0">CP:</label>
                    <input type="text" name="codigo_postal" id="codigo_postal" value="{{ $empresa->codigo_postal }}" class="form-control input-style-custom mb-3" placeholder="CP" required maxlength="5">
                    {!! $errors->first('codigo_postal','<p class="text-danger">Error: El campo codigo postal es requerido</p>') !!}

                    <label for="calles_referencia" class="ml-4 mb-0">Entre qué Calle y qué Calle:</label>
                    <input type="text" name="calles_referencia" id="calles_referencia" value="{{ old('calles_referencia') }}" class="form-control input-style-custom mb-3" placeholder="Entre qué Calle y qué Calle">

                    <div class="text-center">
                        <input type="hidden" name="sss" id="sss" value="1">
{{--                         <label for="sss" class="mb-0">¿Pertenece a la SSS? </label>
 --}}                    </div>

                    <label for="permiso_extranjero" class="ml-4 mb-0">¿Tiene permisos como empleador de extranjeros?:</label>
                    <select name="permiso_extranjero" id="permiso_extranjero" class="form-control input-style-custom mb-3">
                        <option value="1" {{ ( $empresa->permiso_extranjero == '1') ? 'selected' : '' }}>Si</option>
                        <option value="0" {{ ( $empresa->permiso_extranjero == '0') ? 'selected' : '' }}>No</option>
                    </select>


                </div>
            @endforeach
       		</div>


       		<div class="row mt-4">
               	<div class="col-4 no-sss">
                    <label for="calculo_imss" class="ml-4 mb-0">Calculo IMSS aplica en:</label>
                    <select name="calculo_imss" id="calculo_imss" class="form-control input-style-custom mb-3" required>
                    	<option disabled selected>Calculo IMSS aplica en: </option>
                        <option value="UMA" {{ ( $empresa->calculo_imss == 'UMA') ? 'selected' : '' }}>UMA</option>
                        <option value="SalarioDiario" {{ ( $empresa->calculo_imss == 'SalarioDiario') ? 'selected' : '' }}>Salario Diario</option>
                    </select>
               	</div>
               	<div class="col-4 pb-0 no-sss">
                    <input type="checkbox" name="activa_restricciones" {{ ( $empresa->activa_restricciones == '1') ? 'checked' : '' }} id="activa_restricciones">
                    <label for="activa_restricciones">Incluir Validacion de Expediente:</label><br>

                    <input type="checkbox" name="dias_imss" {{ ( $empresa->dias_imss == '1') ? 'checked' : '' }} id="dias_imss" value="1">
                    <label for="dias_imss"> ¿Variabilidad en dias IMSS?</label>
                    <br>

                    <input type="checkbox" name="lista_empleados" {{ ( $empresa->lista_empleados == '1') ? 'checked' : '' }} id="lista_empleados">
                    <label for="lista_empleados"> ¿Mostrar CheckList de empleados en Calculos?</label>
               	</div>
               	<div class="col-4 no-sss hide-new">
                    <input type="checkbox" name="sede" {{ ( $empresa->sede == '1') ? 'checked' : '' }} id="sede" value="1">
                    <label for="sede"> ¿Cuentas con sedes?</label>

                    <button type="button" class="btn btn-success btn-sm agregar-sede ml-3 d-none"><i class="fas fa-plus"></i></button>
                    <br>
                    <div class="d-none campos-sedes">
                        <input type="text" name="sedes[]" class="form-control mb-1" aria-describedby="inputGroup-sizing-sm" placeholder="Escribe el nombre de la sede">
                    </div>
                </div>
                <input type="hidden" name="id" id="id" value="">
       		</div>

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

<script src="{{asset('js/helper.js')}}"></script>
<script type="text/javascript">
    $(function() {
        $('.select-clase').select2();
    });


    $("#edit_empresa").click(function(){

        var razon_social = document.getElementById("razon_social").value;
        var representante_legal = document.getElementById("representante_legal").value;
        var rfc = document .getElementById("rfc").value;
        var telefono = document.getElementById("telefono").value;
        var email = document.getElementById("email").value;
        var calle_num = document.getElementById("calle_num").value;
        var colonia = document.getElementById("colonia").value;
        var delegacion_municipio = document.getElementById("delegacion_municipio").value;
        var estado = document.getElementById("estado").value;
        var codigo_postal = document.getElementById("codigo_postal").value;
        var calculo_imss = document.getElementById("calculo_imss").value;

        if(razon_social== "" || representante_legal == "" || rfc == "" || telefono == "" || email == "" || calle_num == "" || colonia == "" || delegacion_municipio == "" || estado =="" || codigo_postal =="" || calculo_imss == ""){
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


    $('#sede').click(function(){
        $('.campos-sedes, .agregar-sede').toggleClass('d-none');
    });

    $('.agregar-sede').click(function(){
        $('.campos-sedes').append('<input type="text" name="sedes[]" class="form-control mb-1" aria-describedby="inputGroup-sizing-sm">');
        $("#empresaReceptoraModal .modal-body").animate({ scrollTop: 10000 }, 1000);
    });   
</script>
