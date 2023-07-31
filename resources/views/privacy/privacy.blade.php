<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
<div class="container">
    
@include('includes.header',['title'=>'Aviso de privacidad',
        'subtitle'=>'', 'img'=>'/img/icono-aviso-privacidad.png',
        'route'=>'bandeja'])   
   
    <div class="article border">
        <div>
            <p class="text-left">
                <b>CORPORACIÓN DE EMPRENDIMIENTO POR MÉXICO, S.A. DE C.V.</b>, en adelante <b>HR SYSTEM</b> es el responsable del tratamiento de los datos personales que nos proporciones.
                <br>
                <br>
                Los datos personales que nos proporcionas, serán utilizados para lo siguiente: administración y validación de asistencias, emisión de incapacidades, registro de datos de colaboradores para llevar acabo el alta, registro y resguardo de expediente digital, registrar todo tipo de incidencia que se vea reflejada en el sueldo a percibir ya sea de manera semanal, quincenal o mensual; calcular y administración de nómina, ingresar bonos o percepciones adicionales al salario e ingresar deducciones cuando estas correspondan; generar timbrado de nómina; elaboración y emisión de contrato, general credencial;  registrar la baja de colaborador cuando corresponda, procesos de cálculo de finiquito y/o liquidación según sea el caso; llevar el registro, control y seguimiento de las demandas laborales por etapas y estatus hasta su total conclusión; elaboración de reportes administrativos.
                <br>
                <br>
                De manera adicional, utilizaremos la información que nos brindas para mejorar y desarrollar nuestros productos a efecto de cumplir las necesidades que tengas.
                <br>
                <br>
                En caso de que no desees que tus datos personales sean tratados para las finalidades adicionales, puedes manifestarlo mediante una solicitud dirigida a la dirección electrónica <b>info@talevto.com.mx</b>
                <br>
                <br>
                Para mayor información acerca del tratamiento y de los derechos que puede hacer valer, usted puede acceder al aviso de privacidad integral a través de la dirección electrónica <b>www.singh.com.mx</b>
            </p>
        </div>
    </div>
    <button style="margin-top: 20px; background-color: #a1a1a1; border: 2px #a1a1a1 solid; color: white; padding: 5px; border-radius: 3px;">Ya has aceptado el aviso de privacidad</button>
</div>
@include('includes.footer')
</body>
</html>
