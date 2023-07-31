@component('mail::message')
# Solicitud HR System
<br>

<p style="text-align: justify;">Estimado empleado(a):<strong> {{$informacion->empleado}} </strong>  de la empresa <strong> {{$informacion->razon_social}} </strong>  , este mail es para comunicarle  que su solicitud de beneficio con el nombre <strong>{{$informacion->nombre}}</strong> fue rechazada  con el ID de referencia <strong>{{ $informacion->id }}</strong> , ya que no cuentas con los parámetros requeridos. </p><br><br>

<p style="text-align: justify;">Si tiene dudas al respecto para más información por favor, comuníquese a los siguientes medios de contacto  <strong> prestaciones@singh.com.mx</strong> o bien al teléfono <strong>(+52) 55 3197 8398 </strong></p>



Atte,<br>
<p><img style="display: block;" src="{{asset('img/logo.png')}}" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
