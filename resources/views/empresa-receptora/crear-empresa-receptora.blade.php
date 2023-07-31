<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">
@include('includes.header',['title'=>'Crear Empresa Receptora/Cliente', 
'subtitle'=>'Administración de HR-System', 'img'=>'img/header/administracion/icono-receptora.png',
 'route'=>'empresar.empresareceptora'])
   
   	<div class="col-md-12">

		<br>
		<br>
   	</div>

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
        <form method="post" id="submit_empresa" action="{{ route('empresar.agregar') }}" enctype="multipart/form-data">
        @csrf
       		<div class="row">
    	        <div class="col-md-4">
    	        	<strong>Datos generales:</strong><br><br>
    	            <input type="text" name="razon_social" id="razon_social"  value="{{ old('razon_social') }}" class="form-control input-style-custom mb-3" placeholder="Razon Social" required>
                    {!! $errors->first('razon_social','<p class="text-danger">Error: El campo razon social es requerido</p>') !!}

    	            <input type="text" name="ins" id="ins" value="{{ old('ins') }}" class="form-control input-style-custom mb-3" placeholder="Instrumento Notarial de la Sociedad">

    	            <input type="text" name="num_notaria" id="num_notaria" value="{{ old('num_notaria') }}" class="form-control input-style-custom mb-3" placeholder="No. de Notaria">

    	            <input type="text" name="nombre_notario" id="nombre_notario"  value="{{ old('nombre_notario') }}" class="form-control input-style-custom mb-3" placeholder="Nombre del Notario">

    	            <input type="text" name="lugar_notaria" id="lugar_notaria"  value="{{ old('lugar_notaria') }}" class="form-control input-style-custom mb-3" placeholder="Lugar de la Notaria">

    	            <input type="text" name="otorgamiento_RdP" id="otorgamiento_RdP" value="{{ old('otorgamiento_RdP') }}" class="form-control input-style-custom mb-3" placeholder="Otorgamiento o Revocacion de Poderes">

    	            <input type="text" name="giro" id="giro" value="{{ old('giro') }}" class="form-control input-style-custom mb-3" placeholder="Giro">

    	            <input type="text" name="representante_legal" id="representante_legal" value="{{ old('representante_legal') }}" class="form-control input-style-custom mb-3" placeholder="Nombre de Representante Legal" required>
                    {!! $errors->first('representante_legal','<p class="text-danger">Error: El campo representante legal social es requerido</p>') !!}
    	        </div>

                <div class="col-md-4">
                	<br><br>
                    <input type="text" name="rfc" id="rfc" value="{{ old('rfc') }}" class="form-control input-style-custom mb-3" placeholder="RFC" required maxlength="15" minlength="10">
                    {!! $errors->first('rfc','<p class="text-danger">Error: El campo RFC es requerido</p>') !!}

                    <input type="text" name="tasa_vigente" id="tasa_vigente" value="{{ old('tasa_vigente') }}" class="form-control input-style-custom mb-3" placeholder="Tasa Vig. Reg. Patronal para Imp. sobre Nomina">

                    <input type="text" name="identiicacion_oficial" id="identiicacion_oficial" value="{{ old('identiicacion_oficial') }}" class="form-control input-style-custom mb-3 " placeholder="Identificacion Oficial de los Representantes Legales">

                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="form-control input-style-custom mb-3" placeholder="Telefono de Contacto" required>
                    {!! $errors->first('telefono','<p class="text-danger">Error: El campo telefono es requerido</p>') !!}

                    <input type="text" name="email" id="email" value="{{ old('email') }}" class="form-control input-style-custom mb-3 " placeholder="Email" required>
                    {!! $errors->first('email','<p class="text-danger">Error: El campo email es requerido</p>') !!}

                    <input type="text" name="contacto_directo" id="contacto_directo" value="{{ old('contacto_directo') }}" class="form-control input-style-custom mb-3" placeholder="Contacto Directo">

                    <input type="text" name="porcentaje_fondo" id="porcentaje_fondo"  value="{{ old('porcentaje_fondo') }}" class="form-control input-style-custom mb-3" placeholder="% de Fondo de Ahorro">
                </div>

                <div class="col-md-4">
                	<strong>Domicilio:</strong><br><br>

                    <input type="text" name="calle_num" id="calle_num" value="{{ old('calle_num') }}" class="form-control input-style-custom mb-3" placeholder="Calle y Num Ext e Int" required>
                    {!! $errors->first('calle_num','<p class="text-danger">Error: El campo calle y num ext e int es requerido</p>') !!}

                    <input type="text" name="colonia" id="colonia" value="{{ old('colonia') }}" class="form-control input-style-custom mb-3" placeholder="Colonia" required>
                    {!! $errors->first('colonia','<p class="text-danger">Error: El campo colonia es requerido</p>') !!}

                    <input type="text" name="delegacion_municipio" id="delegacion_municipio" value="{{ old('delegacion_municipio') }}"  class="form-control input-style-custom mb-3" placeholder="Delegacion/Municipio" required>
                    {!! $errors->first('delegacion_municipio','<p class="text-danger">Error: El campo delegacion/municipio es requerido</p>') !!}

                    <select name="estado" id="estado" class="form-control select-clase input-style-custom mb-3" required>
                    	<option value="" disabled selected>Estado</option>
                        <option value="Jalisco">Jalisco</option>
                        <option value="Aguascalientes">Aguascalientes</option>
                        <option value="Baja California">Baja California </option>
                        <option value="Baja California Sur">Baja California Sur </option>
                        <option value="Campeche">Campeche </option>
                        <option value="Chiapas">Chiapas </option>
                        <option value="Chihuahua">Chihuahua </option>
                        <option value="Coahuila">Coahuila </option>
                        <option value="Colima">Colima </option>
                        <option value="Ciudad de México">Ciudad de México</option>
                        <option value="Durango">Durango</option>
                        <option value="Estado de México">Estado de México</option>
                        <option value="Guanajuato">Guanajuato </option>
                        <option value="Guerrero">Guerrero </option>
                        <option value="Hidalgo">Hidalgo </option>
                        <option value="Jalisco">Jalisco </option>
                        <option value="MICHOACÁN">Michoacán </option>
                        <option value="Morelos">Morelos </option>
                        <option value="Nayarit">Nayarit </option>
                        <option value="NUEVO LEÓN">Nuevo León </option>
                        <option value="Oaxaca">Oaxaca </option>
                        <option value="Puebla">Puebla </option>
                        <option value="Queretaro">Querétaro </option>
                        <option value="Quintana Roo">Quintana Roo </option>
                        <option value="San Luis Potosí">San Luis Potosí</option>
                        <option value="Sinaloa">Sinaloa </option>
                        <option value="Sonora">Sonora </option>
                        <option value="Tabasco">Tabasco </option>
                        <option value="Tamaulipas">Tamaulipas </option>
                        <option value="Tlaxcala">Tlaxcala </option>
                        <option value="Veracruz">Veracruz </option>
                        <option value="Yucatán">Yucatán </option>
                        <option value="Zacatecas">Zacatecas</option>
                    </select>
                    <div class="mt-3"></div>
                    <input type="text" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal') }}" class="form-control input-style-custom mb-3" placeholder="CP" required maxlength="5">
                    {!! $errors->first('codigo_postal','<p class="text-danger">Error: El campo codigo postal es requerido</p>') !!}

                    <input type="text" name="calles_referencia" id="calles_referencia" value="{{ old('calles_referencia') }}" class="form-control input-style-custom mb-3" placeholder="Entre qué Calle y qué Calle">

                    <div class="text-center">
                        <input type="hidden" name="sss" id="sss" value="0">
                    </div>


                    <select name="permiso_extranjero" id="permiso_extranjero" class="form-control input-style-custom mb-3">
                        <option value="" disabled selected>¿Tiene permisos como empleador de extranjeros?</option>
                        <option value="1">Si</option>
                        <option value="0">No</option>
                    </select>


                </div>

       		</div>


       		<div class="row">
               	<div class="col-4 no-sss">
                    <select name="calculo_imss" id="calculo_imss" class="form-control input-style-custom mb-3" required>
                    	<option disabled selected>Calculo IMSS aplica en: </option>
                        <option value="UMA">UMA</option>
                        <option value="SalarioDiario">Salario Diario</option>
                    </select>
               	</div>
                {!! $errors->first('calculo_imss','<p class="text-danger">Error: El campo calculo IMSS es requerido</p>') !!}
               	<div class="col-4 pb-0 no-sss">
                    <input type="checkbox" name="activa_restricciones" id="activa_restricciones">
                    <label for="activa_restricciones">Incluir Validacion de Expediente:</label><br>

                    <input type="checkbox" name="dias_imss" id="dias_imss" value="1">  <label for="dias_imss"> ¿Variabilidad en dias IMSS?</label>
                    <br>

                    <input type="checkbox" name="lista_empleados" id="lista_empleados">  <label for="lista_empleados"> ¿Mostrar CheckList de empleados en Calculos?</label>
               	</div>
               	<div class="col-4 no-sss hide-new">
                    <input type="checkbox" name="sede" id="sede" value="1"> <label for="sede"> ¿Cuentas con sedes?</label>

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
                <input type="submit" class="center button-style" id="add_empresa" value="Guardar">
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
</script>

<script type="text/javascript">
    $('#sede').click(function(){
        $('.campos-sedes, .agregar-sede').toggleClass('d-none');
    });

    $('.agregar-sede').click(function(){
        $('.campos-sedes').append('<input type="text" name="sedes[]" class="form-control mb-1" aria-describedby="inputGroup-sizing-sm">');
        $("#empresaReceptoraModal .modal-body").animate({ scrollTop: 10000 }, 1000);
    });   

</script>
