@component('mail::message')
# Prestamos HR System

Estimado <strong>{{ explode(' ', $nombreEmpleado)[2] }}</strong>,

Le notificamos que alguno de los documentos que nos hizo llegar de su prestamo <strong>{{$tipoPrestamo}}</strong>, fue descartado y necesitamos que lo proporcione nuevamente.

"{{$mensaje}}"

De click en el siguiente botón para proporcionar la información faltante y poder continuar con el proceso del prestamo/prestación.

@component('mail::button', ['url' => route('prestamos.miPrestamo', $prestamoId ), 'color' => 'danger'])
Subir información faltante
@endcomponent

Atte,<br>
<p>@component('mail::message')
# Prestamos HR System

Estimado <strong>{{ explode(' ', $nombreEmpleado)[2] }}</strong>,

Le notificamos que alguno de los documentos que nos hizo llegar de su prestamo <strong>{{$tipoPrestamo}}</strong>, fue descartado y necesitamos que lo proporcione nuevamente.

"{{$mensaje}}"

De click en el siguiente botón para proporcionar la información faltante y poder continuar con el proceso del prestamo/prestación.

@component('mail::button', ['url' => route('prestamos.miPrestamo', $prestamoId ), 'color' => 'danger'])
Subir información faltante
@endcomponent

Atte,<br>
<p><img style="display: block;" src="{{asset('img/logo.png')}}" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
</p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
