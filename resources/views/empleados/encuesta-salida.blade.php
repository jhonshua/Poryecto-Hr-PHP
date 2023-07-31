<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')

<div class="container">
	<label class="font-size-1-5em mb-5 under-line font-weight-bold">Encuesta Salida</label>

	<div class="article border">
    <div class="col-md-12">
        <img src="{{asset('/img/hr_logo.png')}}" class="my-5" height="100">

        <h3 class="mb-5">Encuesta de salida</h3>

        <h4 class="font-weight-bold mb-3">{{$empleado->nombre . ' ' . $empleado->apaterno}},</h4>

        <p class="mb-4">Por favor, dedique unos minutos a completar esta encuesta. La información obtenida servirá para entender los motivos de su baja. Sus respuestas serán tratadas de forma CONFIDENCIAL.</p>

        <form method="post" action="{{ route('empleados.guardarencuesta') }}">
            @csrf
            <input type="hidden" name="id_empleado" value="{{$empleado->id}}">
            <input type="hidden" name="fecha_creacion" value="{{date('Y-m-d H:i:s')}}">
            
            <div class="question">1. ¿Cuánto tiempo lleva pensando en dejar la empresa?</div>
            <div>
                <input type="radio" name="uno" required id="11" value="Menos de un mes">
                <label for="11">Menos de un mes</label>
            </div>
            <div>
                <input type="radio" name="uno" required id="12" value="Entre uno y tres Meses">
                <label for="12">Entre uno y tres Meses</label>
            </div> 
            <div>
                <input type="radio" name="uno" required id="13" value="Entre tres y seis meses">
                <label for="13">Entre tres y seis meses</label>
            </div> 
            <div>
                <input type="radio" name="uno" required id="14" value="Más de seis meses">
                <label for="14">Más de seis meses</label>
            </div> 


            <div class="question">2. ¿Cuándo ingresó a la empresa recibió el curso de inducción: ¿Quiénes somos, misión, visión y valores, instalaciones, lugar de trabajo, reglamento interno, prestaciones, horarios de trabajo, obligaciones, derechos y presentación con sus compañeros?</div>
            <div>
                <input type="radio" name="dos" required id="21" value="Si">
                <label for="21">Si</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="dos" required id="22" value="No">
                <label for="22">No</label>
            </div> 



            <div class="question">3. ¿Recibió instrucciones y capacitación acerca de cómo realizar su trabajo al inicio y durante su estancia laboral?</div>
            <div>
                <input type="radio" name="tres" required id="31" value="Si">
                <label for="31">Si</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="tres" required id="32" value="No">
                <label for="32">No</label>
            </div> 


            <div class="question">4. ¿Cuál o cuáles son sus motivos para dejar la empresa? Por favor, seleccione todas las casillas que procedan:</div>
            <div>
                <input type="checkbox" name="cuatrouno" id="41" value="Me ofrecen un mejor puesto">
                <label for="41">Me ofrecen un mejor puesto</label>
            </div>
            <div>
                <input type="checkbox" name="cuatrodos" id="42" value="Me ofrecen mayor sueldo o mejores prestaciones">
                <label for="42">Me ofrecen mayor sueldo o mejores prestaciones</label>
            </div> 
            <div>
                <input type="checkbox" name="cuatrotres" id="43" value="Motivos familiares y/o enfermedad">
                <label for="43">Motivos familiares y/o enfermedad</label>
            </div> 
            <div>
                <input type="checkbox" name="cuatrocuatro" id="44" value="Desmotivación, no cumple la empresa con lo ofrecido">
                <label for="44">Desmotivación, no cumple la empresa con lo ofrecido</label>
            </div> 
            <div>
                <input type="checkbox" name="cuatrocinco" id="45" value="Me cambio de residencia">
                <label for="45">Me cambio de residencia</label>
            </div>
            <div>
                <input type="checkbox" name="cuatroseis" id="46" value="Mal ambiente de trabajo con compañeros">
                <label for="46">Mal ambiente de trabajo con compañeros</label>
            </div> 
            <div>
                <input type="checkbox" name="cuatrosiete" id="47" value="Mal ambiente de trabajo con mi jefe">
                <label for="47">Mal ambiente de trabajo con mi jefe</label>
            </div> 
            <div>
                <input type="checkbox" name="cuatroocho" id="48" value="Falta de reconocimiento por mi trabajo y/o aportaciones">
                <label for="48">Falta de reconocimiento por mi trabajo y/o aportaciones</label>
            </div> 


            <div class="question">5. ¿Cree que su paso por la empresa ha aportado valor a la misma?</div>
            <div>
                <input type="radio" name="cinco" required id="51" value="Si">
                <label for="51">Si</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="cinco" required id="52" value="No">
                <label for="52">No</label>
            </div>


            <div class="question">6.- Su paso por la empresa le ha aportado a usted algo?</div>
            <div>
                <input type="radio" name="seis" required id="61" value="Si">
                <label for="61">Si</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="seis" required id="62" value="No">
                <label for="62">No</label>
            </div>


            <div class="question">7.- ¿Recomendaría a sus amigos y familiares que entrara trabajar con nosotros?</div>
            <div>
                <input type="radio" name="siete" required id="71" value="Si">
                <label for="71">Si</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="siete" required id="72" value="No">
                <label for="72">No</label>
            </div>


            <div class="question">8.- ¿Sin estuviera es sus manos, que cambios haría en la empresa para mejorarla?</div>
            <textarea id="ocho" class="form-control" name="ocho" rows="3"></textarea>

            <button class="btn btn-warning my-5 px-5 font-weight-bold">ENVIAR</button>

        </form>

    </div>
	</div>

</div>
@include('includes.footer')