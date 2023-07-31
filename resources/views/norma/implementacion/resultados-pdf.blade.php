@if($trabajador->count() > 0)
    @foreach($trabajador as $clave => $cuestionario)
        @if($clave == 0)
            <table class="table">
                <tr>
                    <td style="width:55%;"><h2>Datos del trabajador</h2></td>
                    <td style="width:25%;"><img src="{{$logo}}" style="width:200px"/></td>
                </tr>
            </table>
            <div style="width:30%;display:inline-block;vertical-align:top;"><h4>Nombre: <small>{{$cuestionario->datosPersonales->nombre}} {{$cuestionario->datosPersonales->paterno}} {{$cuestionario->datosPersonales->materno}}</small></h4></div>
            <div style="width:20%;display:inline-block;vertical-align:top;"><h4>Fecha: <small>{{$cuestionario->fecha_inicio}} </small></h4></div>
            <div style="width:45%;display:inline-block;vertical-align:top;"><h4>Empresa: <small>{{$razon}}</small></h4></div>
            
            <table class="table table-striped table-sm arial-10">
                <tr><td colspan="3"><b>Sexo:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->sexo == 18) &nbsp;X @endif</span>&nbsp;Masculino</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->sexo == 19) &nbsp;X @endif</span>&nbsp;Femenino</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->sexo == 20) &nbsp;X @endif</span>&nbsp;Otro</td>
                </tr>
                <tr><td colspan="3"><b>Edad:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->edad == 21) &nbsp;X @endif</span>&nbsp;15 - 19<br><span class="dot1">@if($cuestionario->datosPersonales->edad == 24) &nbsp;X @endif</span>&nbsp;30 - 34<br/><span class="dot1">@if($cuestionario->datosPersonales->edad == 27) &nbsp;X @endif</span>&nbsp;45 - 49</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->edad == 22) &nbsp;X @endif</span>&nbsp;20 - 24<br/><span class="dot1">@if($cuestionario->datosPersonales->edad == 25) &nbsp;X @endif</span>&nbsp;35 - 39<br/><span class="dot1">@if($cuestionario->datosPersonales->edad == 28) &nbsp;X @endif</span>&nbsp;50 - 54</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->edad == 23) &nbsp;X @endif</span>&nbsp;25 - 29<br/><span class="dot1">@if($cuestionario->datosPersonales->edad == 26) &nbsp;X @endif</span>&nbsp;40 - 44</td>
                </tr>
                <tr><td colspan="3"><b>Estado civil:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->estado_civil == 29) &nbsp;X @endif</span>&nbsp;Casado<br>
                        <span class="dot1">@if($cuestionario->datosPersonales->estado_civil == 30) &nbsp;X @endif</span>&nbsp;Soltero<br/>
                    </td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->estado_civil == 33) &nbsp;X @endif</span>&nbsp;Viudo<br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->estado_civil == 31) &nbsp;X @endif</span>&nbsp;Unión libre<br/>
                    </td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->estado_civil == 32) &nbsp;X @endif</span>&nbsp;Divorciado</td>
                </tr>
                <tr>
                    <td><b>Nivel de estudios:</b></td>
                    <td><b>Terminada</b></td>
                    <td><b>Incompleta</b></td>
                </tr>
                <tr>
                    <td>
                        Secundaria <br>
                        Preparatoria o Bachillerato <br>
                        Técnico Superior <br>
                        Licenciatura <br>
                        Maestría <br>
                        Doctorado <br>
                    </td>
                    <td>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 34) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 35) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 37) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 38) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 39) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 40) &nbsp;X @endif</span><br/>
                    </td>
                    <td>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 41) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 42) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 43) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 44) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 45) &nbsp;X @endif</span><br/>
                        <span class="dot1">@if($cuestionario->datosPersonales->nivel_estudios == 46) &nbsp;X @endif</span><br/>
                    </td>
                </tr>
            </table>

            <h2>Datos laborales</h2>
            <table class="table table-striped table-sm">
                <tr><td colspan="3"><b>Nivel de estudios:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_puesto == 47) &nbsp;X @endif</span>&nbsp;Operativo<br/><span class="dot1">@if($cuestionario->datosPersonales->tipo_puesto == 49) &nbsp;X @endif</span>&nbsp;Profesional o técnico</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_puesto == 48) &nbsp;X @endif</span>&nbsp;Supervisor<br/></td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_puesto == 50) &nbsp;X @endif</span>&nbsp;Gerente<br/></td>
                </tr>
                <tr><td colspan="3"><b>Tipo de contratación:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_contratacion == 51) &nbsp;X @endif</span>&nbsp;Por proyecto<br/><span class="dot1">@if($cuestionario->datosPersonales->tipo_contratacion == 53) &nbsp;X @endif</span>&nbsp;Por tiempo determinado (temporal)</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_contratacion == 52) &nbsp;X @endif</span>&nbsp;Tiempo indeterminado<br/></td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_contratacion == 54) &nbsp;X @endif</span>&nbsp;Honorarios<br/></td>
                </tr>
                <tr><td colspan="3"><b>Tipo de personal:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_personal == 55) &nbsp;X @endif</span>&nbsp;Sindicalizado<br/></td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_personal == 56) &nbsp;X @endif</span>&nbsp;Confianza<br/></td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_personal == 57) &nbsp;X @endif</span>&nbsp;Ninguno<br/></td>
                </tr>
                <tr><td colspan="3"><b>Tipo de jornada de trabajo:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_jornada == 59) &nbsp;X @endif</span>&nbsp;de 8:00 a 14:00 hrs<br/><span class="dot1">@if($cuestionario->datosPersonales->tipo_jornada == 58) &nbsp;X @endif</span>&nbsp;Fijo diurno (entre las 6:00 y 20:00 hrs)</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_jornada == 60) &nbsp;X @endif</span>&nbsp;de 9:00 a 19:00 hrs<br/></td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->tipo_jornada == 61) &nbsp;X @endif</span>&nbsp;de 10:00 a 20:00 hrs<br/></td>
                </tr>
            </table>

            <div class="saltopagina"></div>

            <table class="table table-striped table-sm">
                <tr><td colspan="3"><b>Realiza rotación de turnos:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->rotacion_turnos == 62) &nbsp;X @endif</span>&nbsp;Si</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->rotacion_turnos == 63) &nbsp;X @endif</span>&nbsp;No</td>
                </tr>
                <tr><td colspan="3"><b>Experiencia (años):<br/>Tiempo en el puesto actual:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_puesto_actual == 64) &nbsp;X @endif</span>&nbsp;Menos de 3 meses<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_puesto_actual == 65) &nbsp;X @endif</span>&nbsp;Entre 5 a 9 años</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_puesto_actual == 66) &nbsp;X @endif</span>&nbsp;Entre 6 meses y 1 año<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_puesto_actual == 67) &nbsp;X @endif</span>&nbsp;Más de 9 años</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_puesto_actual == 68) &nbsp;X @endif</span>&nbsp;Entre 1 a 4 años</td>
                </tr>
                <tr><td colspan="3"><b>Tiempo experiencia laboral:</b></td></tr>
                <tr>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 69) &nbsp;X @endif</span>&nbsp;Menos de 6 meses<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 72) &nbsp;X @endif</span>&nbsp;Entre 5 a 9 años<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 75) &nbsp;X @endif</span>&nbsp;Entre 20 a 24 años</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 70) &nbsp;X @endif</span>&nbsp;Entre 6 meses y 1 año<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 73) &nbsp;X @endif</span>&nbsp;Entre 10 a 14 años<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 76) &nbsp;X @endif</span>&nbsp;25 años o más</td>
                    <td><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 71) &nbsp;X @endif</span>&nbsp;Entre 1 a 4 años<br/><span class="dot1">@if($cuestionario->datosPersonales->experiencia_laboral == 74) &nbsp;X @endif</span>&nbsp;Entre 15 a 19 años</td>
                </tr>
            </table>  
            <div class="saltopagina"></div>
        @endif
        <table class="table">
                <tr>
                    <td style="width:55%;">
                        <h1 class="bold">{{$cuestionario->cuestionario->nombre}} </h1>
                        <h5 class="bold">{{$cuestionario->datosPersonales->nombre}} {{$cuestionario->datosPersonales->paterno}} {{$cuestionario->datosPersonales->materno}}<br/>
                        {{$cuestionario->fecha_inicio}}<br/>
                        {{$razon}}</h5>
                    </td>
                    <td style="width:25%;"><img src="{{$logo}}" style="width:200px"/></td>
                </tr>
        </table>
    
        <p class="text-center arial-10 bold">{{$cuestionario->cuestionario->descripcion}}</p>

        @php  $respuesta = $cuestionario->respuestas->keyBy('id')->toArray() @endphp
        @php  $contadorbloque =0; $numeroPregunta = 1 @endphp

         @foreach($cuestionario->cuestionario->bloques as $bloque)
            @if($bloque->id == 10)
                <div class="saltopagina"></div>
            @endif
            <fieldset>
                @isset($bloque->descripcion)
                    <legend class="text-center arial-10 bold">{{$bloque->descripcion}}</legend>
                @endisset
                <div class="condicional{{$bloque->id}}" style="padding:0 1% 3% 0;text-align:center;font-weight:800;font-size:1rem;display:none;"></div>
                
                @if(($bloque->id == 21 && $cuestionario->idcuestionario == 2) || ($bloque->id == 13 && $cuestionario->idcuestionario == 3))
                    <div  style="width:100%;padding:20px;0px;background:#C2BDAB">
                        <b><label>En mi trabajo debo brindar servicio a clientes o usuarios:</label>
                        &nbsp;&nbsp; SI&nbsp;<span class="dot">@if($respuesta[73]['pivot']['valor'] == 1) &nbsp;X @endif</span>
                        &nbsp;&nbsp; NO&nbsp;<span class="dot">@if($respuesta[73]['pivot']['valor'] == 0) &nbsp;X @endif</span></b>
                    </div>
                @endif

                
                @if(($bloque->id == 22 && $cuestionario->idcuestionario == 2) || ($bloque->id == 14 && $cuestionario->idcuestionario == 3))
                <div  style="width:100%;padding:20px;0px;background:#C2BDAB">
                        <b><label>Soy jefe de otros trabajadores:</label>
                        &nbsp;&nbsp; SI&nbsp;<span class="dot">@if($respuesta[74]['pivot']['valor'] == 1) &nbsp;X @endif</span>
                        &nbsp;&nbsp; NO&nbsp;<span class="dot">@if($respuesta[74]['pivot']['valor'] == 0) &nbsp;X @endif</span></b>
                    </div>
                @endif
                
                @isset($bloque->instrucciones)
                    <p class="text-center arial-10 bold">{!!$bloque->instrucciones!!}</p>
                @endisset
                
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr class="arial-10">
                                <th>Pregunta</th>
                                @if($cuestionario->idcuestionario == 1)
                                    <th>SI</th>
                                    <th>NO</th>
                                @else
                                    <th>Siempre</th>
                                    <th>Casi siempre</th>
                                    <th>Algunas veces</th>
                                    <th>Casi nunca</th>
                                    <th>Nunca</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($bloque->preguntas as $p)
                                
                        @if($p->pivot->condicional == 0)
                            <tr class="arial-10"> 
                                    <td> <label><b>{{$numeroPregunta}}.-</b>{{$p->pregunta}}</label></td>
                                    @if(!empty($respuesta[$p->id]['id'])) 
                                        @if($cuestionario->idcuestionario == 1)
                                            <td ><div style="padding-left:37%;"><span class="dot1">@if($respuesta[$p->id]['pivot']['valor'] == 1) &nbsp;X @endif</span></div></td>
                                            <td ><div style="padding-left:37%;"><span class="dot1">@if($respuesta[$p->id]['pivot']['valor'] == 0) &nbsp;X @endif</span></div></td>
                                        @else

                                            @if($respuesta[$p->id]['tipo_respuesta'] == 1)
                                                @for($i = 0; $i < 5; $i++)
                                                    <td ><div style="padding-left:40%;"><span class="dot1" >@if($respuesta[$p->id]['pivot']['valor'] == $i) &nbsp;X @endif</span></div></td>
                                                @endfor
                                            @else
                                                @for($i = 4; $i >= 0; $i--)
                                                    <td ><div style="padding-left:40%;"><span class="dot1" >@if($respuesta[$p->id]['pivot']['valor'] == $i) &nbsp;X @endif</span></div></td>
                                                @endfor
                                            @endif
                                        @endif
                                    @else
                                        @if($cuestionario->idcuestionario == 1)
                                            <td colspan="2"><center>N/A</center></td>
                                        @else
                                            <td colspan="5"><center>N/A</center></td>
                                        @endif
                                    @endif
                                </tr>
                                @endif
                            @php  $numeroPregunta++; @endphp
                    @endforeach
                    </tbody>
                </table>
            </fieldset>
            <br/>
            @php $contadorbloque++; @endphp
        @endforeach
        @if($clave == 0)
            <div class="saltopagina"></div>
        @endif
    @endforeach
@endif

<style>
#idSistemaLogo{
    width:100%;}

.contenido{
    margin:0 25px;}

.text-justify {
    text-align: justify !important;}

.text-derecha {
    text-align: right !important;}

.text-center {
    text-align: center !important;}

.arial-10{
    font-size:10pt;
    font-family: Arial, Helvetica, sans-serif;
}
.arial-8{
    font-size:8pt;
    font-family: Arial, Helvetica, sans-serif;}

.bold{
    font-weight: bold;}

.espacio-20{
    line-height : 20px;}

.padding-10{
    padding:10px 0;}

table.firmas{
    width:100%;}

table.firmas tr td.firma{
    height: 200px;
    border-bottom: 3px solid #000;}

table.firmas tr td.firma-corta{
    height: 150px;
    border-bottom: 3px solid #000;}

.cuadro{
    border:2px solid #000;
    height:30px;
    width:30px;
    margin-left:10px;}

.huella{
    border:2px solid #000;
    height:120px;
    width:100px;
    margin-left:30%;
    margin-top:-5% !important;}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;}

.table tbody+tbody {
    border-top: 2px solid #dee2e6;}

.table-sm th,
.table-sm td {
    padding: 0.3rem;}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);}

.dot {
    vertical-align:top;
    height: 15px;
    width: 15px;
    border-radius: 50%;
    border: 2px solid #000000;
    display: inline-block;
    background:#ffffff;
    text-align:center;}

.dot1 {
    vertical-align:top;
    height: 10px;
    width: 10px;
    border-radius: 40%;
    border: 2px solid #000000;
    display: inline-block;
    background:#ffffff;
    text-align:center;}

.dot2 {
    vertical-align:top;
    height: 10px;
    width: 10px;
    border-radius: 40%;
    border: 2px solid #000000;
    display: inline-block;
    background:#ffffff;
    text-align:center;}

small,.small {
    font-size: 80%;
    font-weight: 400;}

.saltopagina{
    page-break-after:always;}

</style>