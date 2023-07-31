@component('mail::message')
<p style="text-align: center; font-weight: bold; font-size: 22px;">
Notificación HR System <br>
Nueva contraseña
</p>
<br>

A continuación te damos la nueva contraseña que usarás para entrar a HRSystem.

<p style="text-align: center; font-weight: bold;">{{$password}}</p>


Conservala,  pero no la compartas por razones de seguirdad de tu información.


@component('mail::button', ['url' => route('empleado.loginpage'), 'color' => 'danger'])
Entrar
@endcomponent

Atte,<br>
<p><img style="display: block;" src="https://hrsystem.com.mx/img/logo.png" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
