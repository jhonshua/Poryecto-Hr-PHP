@component('mail::message')
# Solicitud de beneficiaro HR System
{{-- {{ dd($prestamoData) }} --}}
<br>
Estimado <strong>{{ $prestamoData->request['empleado'] }}</strong>,

Te informamos que se generó la siguiente solicitud de tipo "<strong>{{ $prestamoData->request['nombrePrestamo'] }}</strong>".
Espera la comunicación del ejecutivo para continuar con el proceso.
<hr>
@component('mail::button', ['url' => route('prestamos.miPrestamo', $prestamoData->prestamo ), 'color' => 'danger']) Ver estatus del proceso @endcomponent

Atte,<br>
<p><img style="display: block;" src="{{asset('img/logo.png')}}" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
