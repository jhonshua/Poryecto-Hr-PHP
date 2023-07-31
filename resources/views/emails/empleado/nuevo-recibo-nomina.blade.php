@component('mail::message')
# RECIBOS DE NOMINA

Tienes disponible un nuevo recibo de nómina del periodo del {{$fecha_inicial_periodo}} al {{$fecha_final_periodo}}.

@component('mail::button', ['url' => '', 'color' => 'danger'])
Ver Recibo de nómina
@endcomponent

Atte,<br>
<p><img style="display: block;" src="{{asset('img/logo.png')}}" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">--- Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo ---</small>
@endcomponent
