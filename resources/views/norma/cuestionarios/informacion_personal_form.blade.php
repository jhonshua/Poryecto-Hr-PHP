
        <form action="" method="post" id="informacion_personal" class="mt-4">
        @csrf
            </br><h2>Datos del trabajador</h2>
            <div class="form-row mt-4">
                <div class="form-group col-md-4">
                    <label class="preguntaCuestionario-4" for="nombre">Nombre: </label>
                    <input name="nombre" class="form-control" type="text" value="" id="nombre" placeholder="Nombre">
                </div>
                <div class="form-group col-md-4">
                    <label class="preguntaCuestionario-4" for="paterno">Apellido paterno: </label>
                    <input name="paterno" class="form-control" type="text" value="" id="paterno" placeholder="Paterno">
                </div>
                <div class="form-group col-md-4">
                    <label class="preguntaCuestionario-4" for="materno">Apellido materno: </label>
                    <input name="materno" class="form-control" type="text" value="" id="materno" placeholder="Materno">
                </div>
            </div>
            <div class="form-row mt-3">
                <label class="col-md-12 preguntaCuestionario-12" for="sexo">Sexo: </label>
                <div class="form-gm-group col-md-12">
                <label class="radio-inline col-md-10"> 
                    <label class="radio-inline col-md-3"><input  type="radio" name="sexo" value="18"> Masculino</label>
                    <label class="radio-inline col-md-3"> <input type="radio" name="sexo" value="19"> Femenino</label>
                    <label class="radio-inline col-md-3"> <input type="radio" name="sexo" value="20"> Otro</label>
                </div>
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="edad">Edad: </label>
                <div class="form-gm-group col-md-12">
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad21" value="21"> 15 - 19</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad22" value="22"> 20 - 24</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad23" value="23"> 25 - 29</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad24" value="24"> 30 - 34</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad25" value="25"> 35 - 39</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad26" value="26"> 40 - 44 </label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad27" value="27"> 45 - 49</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="edad" id="edad28" value="28"> 50 - 54</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="estado_civil">Estado civil: </label>
                <div class="form-gm-group col-md-12">
                    <label class="radio-inline col-md-2"><input type="radio" name="estado_civil" value="29"> Casado</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="estado_civil" value="30"> Soltero</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="estado_civil" value="32"> Divorciado</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="estado_civil" value="33"> Viudo</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="estado_civil" value="31"> Unión libre </label>
                </div>
            </div>

            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="nivel_estudios">Nivel de estudios: </label>
                <div class="form-gm-group col-md-12">
                            <table class="col-md-6">
                                <tr>
                                    <td></td>
                                    <td>Terminada</td>
                                    <td>Incompleta</td>
                                </hr>
                                <tr>
                                    <td>Secundaria</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="34" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="41" name="nivel_estudios"></label></td>
                                </tr>
                                <tr>
                                    <td>Preparatoria o Bachillerato</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="35" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="42" name="nivel_estudios"></label></td>
                                </tr>
                                <tr>
                                    <td>Técnico Superior</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="37" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="43" name="nivel_estudios"></label></td>
                                </tr>
                                <tr>
                                    <td>Licenciatura</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="38" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="44" name="nivel_estudios"></label></td>
                                </tr>
                                <tr>
                                    <td>Maestría</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="39" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="45" name="nivel_estudios"></label></td>
                                </tr>
                                <tr>
                                    <td>Doctorado</td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="40" name="nivel_estudios"></label></td>
                                    <td><label class="radio-inline col-md-2"><input type="radio" value="46" name="nivel_estudios"></label></td>
                                </tr>
                            </table>
                </div>
            
            </div></br>

            <h2>Datos laborales</h2>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="tipo_puesto">Tipo de puesto: </label>
                <div class="form-gm-group col-md-12">
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_puesto" value="47"> Operativo</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_puesto" value="48"> Supervisor</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_puesto" value="50"> Gerente</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_puesto" value="49"> Profesional o técnico</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="tipo_contratacion">Tipo de contratación: </label> 
                <div class="form-gm-group col-md-12">
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_contratacion" value="51"> Por proyecto</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_contratacion" value="52"> Tiempo indeterminado </label>
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_contratacion" value="54"> Honorarios</label>
                    <label class="radio-inline col-md-4"><input type="radio" name="tipo_contratacion" value="53"> Por tiempo determinado (temporal)</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="tipo_personal">Tipo de personal: </label>
                <div class="form-group col-md-12">
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_personal" value="55"> Sindicalizado</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_personal" value="56"> Confianza </label>
                    <label class="radio-inline col-md-2"><input type="radio" name="tipo_personal" value="57"> Ninguno</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="tipo_jornada">Tipo de jornada de trabajo: </label>
                <div class="form-group col-md-12">
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_jornada" value="59"> de 8:00 a 17:00 hrs. </label>
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_jornada" value="60"> de 9:00 a 19:00 hrs.</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="tipo_jornada" value="61"> de 10:00 a 20:00 hrs.</label>
                    <label class="radio-inline col-md-4"><input type="radio" name="tipo_jornada" value="58"> Fijo diurno (entre las 6:00 y 20:00 hrs).</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="rotacion_turnos">Realiza rotación de turnos: </label>
                <div class="form-group col-md-12">
                    <label class="radio-inline col-md-2"><input type="radio" name="rotacion_turnos" value="62"> Si</label>
                    <label class="radio-inline col-md-2"><input type="radio" name="rotacion_turnos" value="63"> No</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="experiencia_puesto_actual">Experiencia (años):<br/>Tiempo en el puesto actual: </label>
                <div class="form-group col-md-12">
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_puesto_actual" value="64"> Menos de 3 meses</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_puesto_actual" value="66"> Entre 6 meses y 1 año </label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_puesto_actual" value="68"> Entre 1 a 4 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_puesto_actual" value="65"> Entre 5 a 9 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_puesto_actual" value="67"> Más de 9 años</label>
                </div>
            
            </div>
            <div class="form-row">
                <label class="col-md-12 preguntaCuestionario-12" for="experiencia_laboral">Tiempo experiencia laboral: </label>
                <div class="form-group col-md-12">
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="69"> Menos de 6 meses</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="70"> Entre 6 meses y 1 año </label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="71"> Entre 1 a 4 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="72"> Entre 5 a 9 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="73"> Entre 10 a 14 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="74"> Entre 15 a 19 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="75"> Entre 20 a 24 años</label>
                    <label class="radio-inline col-md-3"><input type="radio" name="experiencia_laboral" value="76"> 25 años o más</label>
                </div>
            
            </div><br/>

            <div class="form-row">
                <a href="{{ route('empleado.norma') }}" id="regresara"><button type="button" id="regresarbtn" class="btn btn-dark cancelar">Regresar</button></a>
                <button class="btn btn-warning guardar ml-3" id="btn-guardar">Guardar</button>
                <input name="informacion_trabajador" type="hidden" id="informacion_trabajador" value="{{$trabajador->id}}" >
                <input name="_token" type="hidden" id="tok" value="" >
            </div>
        </form>
  
<br/>
@isset($trabajador)
@push('scripts')
<script>
$(function(){
    $("#nombre").val("@php echo $trabajador->nombre; @endphp");
    $("#materno").val("@php echo $trabajador->materno; @endphp");
    $("#paterno").val("@php echo $trabajador->paterno; @endphp");
    @php if($trabajador->sexo != "" && $trabajador->sexo != null) { echo '$("input:radio[value='. $trabajador->sexo.']").attr("checked", true);'; } 
     if($trabajador->edad != "" && $trabajador->edad != null) { echo '$("input:radio[value='. $trabajador->edad.']").attr("checked", true);'; } 
     if($trabajador->estado_civil != "" && $trabajador->estado_civil != null) { echo '$("input:radio[value='. $trabajador->estado_civil.']").attr("checked", true);'; } 
     if($trabajador->nivel_estudios != "" && $trabajador->nivel_estudios != null) { echo '$("input:radio[value='. $trabajador->nivel_estudios.']").attr("checked", true);'; } 
     if($trabajador->tipo_puesto != "" && $trabajador->tipo_puesto != null) { echo '$("input:radio[value='. $trabajador->tipo_puesto.']").attr("checked", true);'; } 
     if($trabajador->tipo_contratacion != "" && $trabajador->tipo_contratacion != null) { echo '$("input:radio[value='. $trabajador->tipo_contratacion.']").attr("checked", true);'; } 
     if($trabajador->tipo_personal != "" && $trabajador->tipo_personal != null) { echo '$("input:radio[value='. $trabajador->tipo_personal.']").attr("checked", true);'; } 
     if($trabajador->tipo_jornada != "" && $trabajador->tipo_jornada != null) { echo '$("input:radio[value='. $trabajador->tipo_jornada.']").attr("checked", true);'; } 
     if($trabajador->rotacion_turnos != "" && $trabajador->rotacion_turnos != null) { echo '$("input:radio[value='. $trabajador->rotacion_turnos.']").attr("checked", true);'; } 
     if($trabajador->experiencia_puesto_actual != "" && $trabajador->experiencia_puesto_actual != null) { echo '$("input:radio[value='. $trabajador->experiencia_puesto_actual.']").attr("checked", true);'; } 
     if($trabajador->experiencia_laboral != "" && $trabajador->experiencia_laboral != null) { echo '$("input:radio[value='. $trabajador->experiencia_laboral.']").attr("checked", true);'; } 
    @endphp


    if(@php echo $trabajador->informacion_validada @endphp == 1){
        $("#informacion_personal input").attr('disabled','disabled');
        $("#btn-guardar").remove();
    }    
    
});
</script>
@endpush
@endisset




