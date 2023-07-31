<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">
    <a href="{{ route('empresae.registropatronal' , $empresa) }}" ref="Usuarios del sistema" data-toggle="tooltip" data-placement="top"
    title="regresar">
        @include('includes.back')
    </a>


	<label class="font-size-1-5em mb-5 under-line font-weight-bold">Crear registro patronal</label>


   	<div class="col-md-12">

		<br>
   	</div>

   	<div class="article border">
        <form method="post" id="submit_empresa" action="{{ route('empresae.agregarregistro') }}">
        @csrf
            <input type="text" name="num_registro_patronal" id="num_registro_patronal" placeholder="Num Registro" class="center input-style mb-3" required>

            <input type="number" name="porcentaje_prima" id="porcentaje_prima" placeholder="% de Prima de Riesgo" class="center input-style mb-3" required step="0.00001">

            <label class="mb-0">:</label>
            <select name="tipo_clase" id="tipo_clase" class="center input-style mb-3" required>
                <option value="" disabled selected>Tipo de Clase</option>
                <option value="Clase I">Clase I</option>
                <option value="Clase II">Clase II</option>
                <option value="Clase III">Clase III</option>
                <option value="Clase IV">Clase IV</option>
                <option value="Clase V">Clase V</option>
            </select>

            <input type="text" name="subdelegacion" id="subdelegacion" placeholder="Subdelegacion" class="center input-style mb-3" required>


            <input type="text" name="tipo_documento" id="tipo_documento" placeholder="Tipo de Documento" class="center input-style mb-3" required>

            <input type="hidden" name="id" id="id" value="">
            <input type="hidden" name="id_empresa_emisora" id="id_empresa_emisora" value="{{$empresa}}">
            {{-- <input type="hidden" name="empresaEmisora" value="{{$empresaEmisora}}"> --}}
        </form>

        <br>
        <div class="row ">
            <div class="col-md-12 text-center">
                <input type="submit" id="add_registro" class="center button-style" value="Guardar">
            </div>
        </div>

   	</div>

</div>
@include('includes.footer')
<script type="text/javascript">
    $("#add_registro").click(function(){

        var num_registro_patronal = document.getElementById("num_registro_patronal").value;
        var porcentaje_prima = document.getElementById("porcentaje_prima").value;
        var tipo_clase = document.getElementById("tipo_clase").value;
        var subdelegacion = document.getElementById("subdelegacion").value;
        var tipo_documento = document.getElementById("tipo_documento").value;

        if(num_registro_patronal== "" || porcentaje_prima == "" || tipo_clase == "" || subdelegacion == "" || tipo_documento == ""){
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
