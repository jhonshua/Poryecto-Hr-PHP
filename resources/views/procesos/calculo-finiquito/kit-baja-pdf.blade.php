<div class="contenido">
    <div id="convenio_fuera_de_juicio_privado">
        <p class="text-center arial-10 bold">CONVENIO PRIVADO DE TERMINACION DE LA RELACION DE TRABAJO</p>
        <p class="text-justify arial-10 bold">POR MEDIO DEL PRESENTE CELEBRAN LA TERMINACIÓN DE LA RELACIÓN LABORAL ENTRE LA EMPRESA DENOMINADA <span class="subrayado">{{$emisora[0]->razon_social}}</span>, REPRESENTADA POR <span style="text-transform: uppercase;" class="subrayado bold">{{$emisora[0]->representante_legal}}</span>, A QUIEN SE LE DENOMINA “EL PATRON” Y POR LA OTRA EL <span class="subrayado">C. {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span>, A QUIEN SE LE DENOMINA “EL TRABAJADOR”</p>

        <p class="text-justify arial-10">En los términos del presente documento y de común acuerdo venimos a enunciar las siguientes:</p>

        <p class="text-center arial-10 bold">C L A U S U L A S</p>

        <p class="text-justify arial-10"><b>PRIMERA</b>. - Las partes comparecientes se reconocen recíprocamente la personalidad con que se ostentan y la capacidad legal para obligarse en los términos de este convenio.</p>

        <p class="text-justify arial-10"><b>SEGUNDA</b>. - Manifiesta <span class="subrayado">C. {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span> que ha venido prestando sus servicios personales para la empresa <span class="subrayado">{{$emisora[0]->razon_social}}</span>, desde el <span class="subrayado">{{$alta->format('d')}} de {{mes($alta->format('m'),true)}} de {{$alta->format('Y')}}</span> y hasta el día <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}</span>, fecha en la que en forma voluntaria da por terminada su relación laboral en términos del artículo 53 fracción I de la Ley Federal del Trabajo.</p>

        <p class="text-justify arial-10">Asimismo, manifiesta que durante el tiempo que laboró para <span class="subrayado">{{$emisora[0]->razon_social}}</span>, lo hizo en una jornada de labores que jamás excedió de la jornada máxima legal permitida de 48 horas semanales, y cuando esporádicamente laboré tiempo extraordinario siempre me fue pagado en términos de ley, percibiendo a últimas fechas un salario diario de <span class="subrayado">${{$empleado->salario_diario}} ({{strtoupper($sueldo_texto)}})</span>, menos los descuentos legales y desempeñándose actualmente con la categoría de <span class="subrayado bold">{{$empleado->puesto->puesto}}</span>.</p>

        <p class="text-justify arial-10"><b>TERCERA</b>.- Las partes comparecientes <span class="subrayado">C. {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span> y la empresa <span class="subrayado">{{$emisora[0]->razon_social}}</span>, convienen y ratifican de mutuo acuerdo la terminación voluntaria de la relación laboral a que se refiere la cláusula anterior hecha el día <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$alta->format('Y')}}</span> y fundamentada en el artículo 53 fracción I de la Ley Laboral vigente por lo que la empresa está de acuerdo con la misma y en consecuencia le otorga la cantidad neta de <span class="subrayado bold">$ {{$rutina->neto_fiscal}} ({{strtoupper($fiscal_texto)}})</span>, misma que se integra de:</p>
    <br/>
            @php $total_percepciones = $total_deducciones = 0; @endphp
            <center><table class="text-center arial-10" style="width:100% !important;">
                <tr>
                    <td colspan="2" class="bold">PERCEPCIONES</td>
                    <td colspan="2" class="bold">DEDUCCIONES</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="table-bordered" style="width:100% !important;">
                            @foreach($rutina->valores_conceptos->where('tipo_concepto',0) as $percepcion)
                                <tr>
                                    <td>{{ucfirst(strtolower($percepcion->nombre_concepto))}}</td>
                                    <td>${{$percepcion->total}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                
                    <td colspan="2">
                        <table class="table-bordered" style="width:100% !important;">
                        @foreach($rutina->valores_conceptos->where('tipo_concepto',1) as $deducciones)
                        <tr>
                            <td>{{ucfirst(strtolower($deducciones->nombre_concepto))}}</td>
                            <td>${{$deducciones->total}}</td>
                        </tr>
                        @endforeach
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="bold">TOTAL PERCEPCIONES </td>
                    <td class="bold">${{$rutina->total_percepcion_fiscal}}</td>
                    <td class="bold">TOTAL DEDUCCIONES </td>
                    <td class="bold">${{$rutina->total_deduccion_fiscal}}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td class="bold">NETO A RECIBIR</td>
                    <td class="bold"> ${{$rutina->neto_fiscal}}</td>
                </tr>              
        </table></center>
        <div style="page-break-after:always;"></div>


    <br>
        <p class="text-justify arial-10">Debido a lo anterior y con el recibo de la suma mencionada, <span class="subrayado">C. {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span>, manifiesta que durante el tiempo que laboró al servicio de <span class="subrayado">{{$emisora[0]->razon_social}}</span>, recibió el pago puntual y oportuno de todas y cada una de las prestaciones a que tuvo derecho conforme a su Contrato Individual de trabajo vigente en las instalaciones de la empresa, razón por la cual no se le adeuda cantidad alguna por conceptos de salarios ordinarios, extraordinarios, descansos semanales y obligatorios, vacaciones y prima de vacaciones, utilidades, aguinaldo anual, prima de antigüedad, ni por ningún otro concepto derivado de su Contrato de Trabajo o de la Ley, otorgando en consecuencia el recibo finiquito más amplio que en derecho proceda y liberándola de cualquier responsabilidad derivada de las relaciones de trabajo.</p>

        <p class="text-justify arial-10"><b>CUARTA</b>. - Por su parte <span class="subrayado">C. {{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span>, manifiesta su conformidad con el contenido en la Cláusula Tercera anterior y por lo tanto recibe a su entera satisfacción la cantidad neta antes mencionada. Asimismo, manifiesta bajo protesta de decir verdad que prestó sus servicios para <span class="subrayado">{{$emisora[0]->razon_social}}</span>, hasta el día <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}</span>, consecuentemente habiendo llegado a un convenio satisfactorio deja sin efecto legal alguno cualquier demanda, queja o reclamación que hubiere presentado por lo que la exhibición de este convenio para que opere por ser esa su voluntad el desistimiento que hace valer.</p>

        <p class="text-justify arial-10"><b>QUINTA</b>.- Las partes comparecientes ratifican y reproducen el contenido de este convenio en tal virtud, bajo protesta de decir verdad manifiestan no reservarse acción o derecho alguno que ejercitarse recíprocamente por lo que se otorgan mutuamente el recibo finiquito más amplio que en derecho proceda, fundamentado en los Artículos 33, 53 Fracción I, 987 y demás relativos y aplicables de la Ley Federal del Trabajo, condenándose a las partes a estar y pasar por él en todo tiempo y lugar como si se tratara de un laudo ejecutoriado pasado ante autoridad de cosa juzgada.</p>




        <p class="text-center arial-10">Ciudad de México, a <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}</span></p>
        <br><br>
        <table class="firmas text-center arial-10 padding-10">
            <tr>
                <td class="firma"></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="firma"></td>
            </tr>
            <tr class="arial-8">
                <td><span class="bold">REPRESENTANTE LEGAL DE {{$emisora[0]->razon_social}}</span></td>
                <td></td>
                <td>EL "TRABAJADOR" <br/><span  class="subrayado">{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span></td>
            </tr>
            <tr>
                <td> Representada por <span class="subrayado">{{$emisora[0]->representante_legal}}</span></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div style="page-break-after:always;"></div>
    <div class="arial-11">
        <br>
        <p class="derecha">Ciudad de México a {{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}.</p>
        <br><br>
        <p><h4>{{$emisora[0]->razon_social}}</h4></p>
        <p>A quien corresponda:</p>
        <p class="text-justify">Por medio de la presente les comunico a ustedes que a partir de esta fecha y por así convenir a mis intereses renuncio y 
        doy por terminada mi relación de trabajo en forma voluntaria al empleo que venía desempeñado para <span class="subrayado">{{$emisora[0]->razon_social}}</span> 
        y que realizaba desde el <span class="subrayado">{{$alta->format('d')}} de {{mes($alta->format('m'),true)}} de {{$alta->format('Y')}}</span> con la categoría de <span class="subrayado">{{$empleado->puesto->puesto}}</span>, en una jornada que no excedía de las 48 horas
         semanales por ser jornada diurna debidamente distribuida en términos del artículo 59 de la ley laboral vigente,
          con un salario diario de <span class="subrayado">${{$empleado->salario_diario}} ({{strtoupper($sueldo_texto)}})</span>, menos los descuentos legales correspondientes.
        </p> 
        <p class="text-justify">
            Asimismo, manifiesto que durante el tiempo en que preste mis servicios, me fueron cubiertos oportunamente todos y cada uno de mis 
            salarios ordinarios y extraordinarios, así como todas y cada una de las prestaciones a las que tuve derecho de conformidad con mi 
            contrato individual de trabajo, colectivo o la Ley, por lo que no me reservo derecho o acción alguna que ejercitar en contra de 
            esa empresa de ninguna naturaleza, ya sea civil, laboral, mercantil, administrativa, penal u otra.
        </p>

        <p>
            Agradeciendo a ustedes todas y cada una de las atenciones prestadas.
        </p>


        <br>
        <table class="firmas text-center arial-11 padding-10" style="padding: 0px 180px !important;">
            <tr>
                <td class="firma bold">A t e n t a m e n t e.</td>
            </tr>
            <tr class="arial-8">
                <td><span  class="subrayado">{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span></td>
            </tr>
        </table>

    </div>

    <div style="page-break-after:always;"></div>
    <div class="arial-10">
        <p class="text-center bold"> RECIBO FINIQUITO</p>
        <p>Bueno por ------------------------------------------------------------------------------------------------------------------- <span class="subrayado bold">${{$rutina->neto_fiscal}}</span></p>
        <p class="text-justify">Recibí de <span class="subrayado">{{$emisora[0]->razon_social}}</span> la cantidad de <span class="subrayado bold">$ {{$rutina->neto_fiscal}}  ({{strtoupper($fiscal_texto)}})</span>, por los conceptos abajo indicados con motivo de mi renuncia voluntaria de esta misma fecha y que son:</p>
        <table style="width:100%">
            <tr>
                <td>
                    <p class="bold">PERCEPCIONES</p>
                    <table class="arial-10 table-bordered">
                        @foreach($rutina->valores_conceptos->where('tipo_concepto',0) as $percepcion)
                            <tr>
                                <td>{{ucfirst(strtolower($percepcion->nombre_concepto))}}</td>
                                <td>${{$percepcion->total}}</td>
                            </tr>
                        @endforeach

                        
                        <tr>
                            <td class="bold">SUBTOTAL</td>
                            <td></td>
                            <td>$ {{$rutina->total_percepcion_fiscal}}</td>
                        </tr>
                    </table>

                </td>
                <td>
                    <p class="bold">DEDUCCIONES</p>
                    <table class="arial-10 table-bordered">
                        
                        @foreach($rutina->valores_conceptos->where('tipo_concepto',1) as $deducciones)
                        <tr>
                            <td>{{ucfirst(strtolower($deducciones->nombre_concepto))}}</td>
                            <td>${{$deducciones->total}}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td class="bold">SUBTOTAL</td>
                            <td></td>
                            <td>$ {{$rutina->total_deduccion_fiscal}}</td>
                        </tr>
                    
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bold">NETO A RECIBIR</td>
                <td class="subrayado">${{$rutina->neto_fiscal}}</td>
            </tr>
        </table>

        <p class="text-justify">
            Con lo anterior, me doy por cubierto de todos y cada uno de las prestaciones a que tuve derecho, dando con esta fecha por terminada voluntariamente la relación laboral que me unía con esa empresa en términos de la fracción I del Artículo 53 de la Ley Federal del Trabajo desde el <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}</span> y devengando a últimas fechas un salario diario bruto de <span class="subrayado">${{$empleado->salario_diario}} ({{strtoupper($sueldo_texto)}})</span>, laborando en una jornada que no excedía de 48 horas semanales por ser diurna debidamente distribuida en términos del artículo 59 de la ley laboral vigente, manifestado que durante el tiempo en que preste mis servicios no adquirí ni sufrí enfermedad profesional ni accidente de trabajo alguno, por lo que otorgo el finiquito más amplio que en derecho proceda, sin reservarme acción o derecho que ejercitar con posterioridad en contra de esa empresa. 
        </p>
        <table class="firmas text-center arial-10" style="padding: 0px 180px !important;">
            <tr>
                <td class="firma bold">Recibí</td>
            </tr>
            <tr class="arial-8">
                <td><span  class="subrayado">{{$empleado->nombre}} {{$empleado->apaterno}} {{$empleado->amaterno}}</span>
                        <p class="text-center arial-10 subrayado">Ciudad de México, a <span class="subrayado">{{$baja->format('d')}} de {{mes($baja->format('m'),true)}} de {{$baja->format('Y')}}</span></p>
                </td>
            </tr>
        </table>
    </div>



</div>


<style>
    .table-bordered {
        width: 100%;
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }


    table.bordes tr td{
        vertical-align: top;
        border: 1px solid #000;
        border-collapse: collapse;
        width: 33% !important;
    }
    .contenido {
        margin: 0 25px;
    }

    .text-justify {
        text-align: justify !important;
    }

    .text-center {
        text-align: center !important;
    }

    .arial-10 {
        font-size: 10pt;
        font-family: Arial, Helvetica, sans-serif;
    }

    .arial-8 {
        font-size: 8pt;
        font-family: Arial, Helvetica, sans-serif;
    }

    .bold {
        font-weight: bold;
    }
    .subrayado {
        background: yellow;
        font-weight: bold;
    }

    .espacio-20 {
        line-height: 20px;
    }

    .padding-10 {
        padding: 10px 0;
    }

    table.firmas {
        width: 100%;
    }

    table.firmas tr td.firma {
        height: 180px;
        border-bottom: 1px solid #000;
    }

    table.firmas tr td.firma-corta {
        height: 130px;
        border-bottom: 1px solid #000;
    }

    .cuadro {
        border: 2px solid #000;
        height: 30px;
        width: 30px;
        margin-left: 10px;
    }

    .huella {
        border: 2px solid #000;
        height: 100px;
        width: 90px;
        margin-left: 30%;
        margin-top: -5% !important;
    }

    .saltopagina {
        page-break-after: always;
    }

    .interlineado {
        line-height: 15px;
    }

    .derecha {
        text-align: right;
    }
</style>