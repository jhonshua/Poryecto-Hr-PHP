<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">
<a href="{{ route('empresae.registropatronal', $idEmpresaEmisora ) }}" ref="Usuarios del sistema" data-toggle="tooltip" data-placement="top"
    title="regresar">
        @include('includes.back')
    </a>
        <label class="font-size-1-5em mb-3 under-line font-weight">Administración de HR-System</label>
        <div class="container-sub">
            <img src="{{asset('img/header/administracion/icono-emisora.png')}}" alt="Conceptos de nómina" class="w-px-40"> <label class="custom-title mt-3 ml-2">Editar registro patronal</label>
        </div>


   	<div class="col-md-12">

		<br>
		<br>
   	</div>

   	<div class="article border">
        <div class="center w-px-280">
                <form method="post" id="submit_empresa" action="{{ route('empresae.actualizarregistropatronal') }}">
                @csrf
                    @foreach ($regPatronal as $registro)
                        <input type="hidden" name="ids" value="{{ $registro->id }}">

                        <label for="num_registro_patronal">Num registro:</label>
                        <input type="text" name="num_registro_patronal" id="num_registro_patronal" value="{{ $registro->num_registro_patronal }}" placeholder="Num Registro" class="center input-style mb-3" required>

                        <label for="porcentaje_prima">% de prima de riesgo:</label>
                        <input type="number" name="porcentaje_prima" id="porcentaje_prima" value="{{ $registro->porcentaje_prima }}" placeholder="% de Prima de Riesgo" class="center input-style mb-3" required step="0.00001">

                        <label for="tipo_clase">Tipo de clase:</label>
                        <select name="tipo_clase" id="tipo_clase" class="center input-style mb-3" required>
                            <option value="" disabled selected>Tipo de Clase</option>
                            <option value="Clase I" {{ ( $registro->tipo_clase == 'Clase I' ) ? 'selected' : '' }}>Clase I</option>
                            <option value="Clase II" {{ ( $registro->tipo_clase == 'Clase II' ) ? 'selected' : '' }}>Clase II</option>
                            <option value="Clase III" {{ ( $registro->tipo_clase == 'Clase III' ) ? 'selected' : '' }}>Clase III</option>
                            <option value="Clase IV" {{ ( $registro->tipo_clase == 'Clase IV' ) ? 'selected' : '' }}>Clase IV</option>
                            <option value="Clase V" {{ ( $registro->tipo_clase == 'Clase V' ) ? 'selected' : '' }}>Clase V</option>
                        </select>

                        <label for="subdelegacion">Subdelegacion:</label>
                        <input type="text" name="subdelegacion" id="subdelegacion" value="{{ $registro->subdelegacion }}" placeholder="Subdelegacion" class="center input-style mb-3" required>

                        <label for="tipo_documento">Tipo de documento:</label>
                        <input type="text" name="tipo_documento" id="tipo_documento" value="{{ $registro->tipo_documento }}" placeholder="Tipo de Documento" class="center input-style mb-3" required>

                        <input type="hidden" name="id" id="id" value="">
                        <input type="hidden" name="id_empresa_emisora" id="id_empresa_emisora" value="{{$registro->id_empresa_emisora }}">
                        {{-- <input type="hidden" name="empresaEmisora" value="{{$empresaEmisora}}"> --}}

                    @endforeach
                </form>


                <br>
                <div class="row ">
                    <div class="col-md-12 text-center">
                        <input type="button" class="center button-style" id="edit_registro" value="Guardar">
                    </div>
                </div>

            </div>
   	</div>

</div>
@include('includes.footer')
<script type="text/javascript">
    $("#edit_registro").click(function(){

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
