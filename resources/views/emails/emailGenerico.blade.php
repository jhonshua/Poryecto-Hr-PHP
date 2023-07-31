@component('mail::message')

<style>
.button-style{
width: auto;
color: #fff;
padding-bottom: 0px;
padding-left: 10px;
padding-right: 10px;
font-size: 20px;
font-weight: 500;
height: 36px;
text-align: center;
border-radius: 5px;
border: 1px #fbba00 solid;
background-color: #fbba00;}

.center {
display: block;
margin-left: auto;
margin-right: auto;}

a:hover {
color: white;
cursor: pointer;
text-decoration: none;}

a{
cursor: pointer;
text-decoration: none;}
</style>

@if(!empty($titulo))
<b>#{!! $titulo !!}
@endif

@if(!empty($cuerpo))
{!! $cuerpo !!}
@endif

@if(!empty($btnTxt) && !empty($btnUrl))
<a href="{{$btnUrl}}"><button class="button-style center">{{$btnTxt}}</button></a>
@endif

Atte,<br>
<p><img style="display: block;" src="{{asset('img/logo.png')}}" alt="logo" height="28" /></p>
<br>
<small style="text-align: center; font-size:11px;">* Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo no es correcto reportalo a desarrollo@singh.com.mx. No contestar a este correo ya que solo es informativo *</small>
@endcomponent
