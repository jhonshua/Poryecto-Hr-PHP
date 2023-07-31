<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link href="{{ asset('css/radios_check.css') }}" rel="stylesheet">
@include('includes.head')
<style>
    .form-check-label{
        font-size:20px; }

    .top-line-black {
        width: 19%; }
</style>
<body>
    @include('includes.navbar')
    <div class="container">
        @include('includes.header',['title'=>'Dispersiones','subtitle'=>'Procesos de cálculo', 'img'=>'img/header/parametria/icono-puestos.png','route'=>'parametria.puestos'])
        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @elseif(session()->has('danger'))
        <div class="row">
            <div class="alert alert-danger" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('danger') }}
            </div>
        </div>
        @endif
        <div class="row" >
            <div class="col-md-5 offset-4">
                <div class="card p-4">
                    <h3 class="center"> <strong>¿Qué importes vas a dispersar? </strong></h3>
                    <form method="post" id="form_dispersion" action="{{ route('procesos.dispersion.inicio') }}" class="pt-3 formulario">
                        @csrf
                        <div class="text-center"> 
                            <div class="form-check-inline radio">
                                <input class="form-check-input" type="radio" name="tipo_dispersion" id="tipo_dispersion1" value="finiquitos">
                                <label class="form-check-label" for="tipo_dispersion1">
                                    Finiquitos
                                </label>
                            </div>
                            <div class="form-check-inline radio">
                                <input class="form-check-input" type="radio" name="tipo_dispersion" id="tipo_dispersion2" value="nominas" checked>
                                <label class="form-check-label" for="tipo_dispersion2">
                                    Nóminas
                                </label>
                            </div>
                            <div class="form-check-inline radio">
                                <input class="form-check-input" type="radio" name="tipo_dispersion" id="tipo_dispersion3" value="aguinaldo">
                                <label class="form-check-label" for="tipo_dispersion3">
                                    Aguinaldo
                                </label>
                            </div>
                        </div>
                        <br/>
                        <button type="submit" class="button-style center">Ingresar</button>   
                    </form>
                </div>
            </div>
        </div>

        {{-- @include('parametria.puestos.puestos-reales.crear-editar-puesto-real-modal') --}}
        {{--@include('parametria.puestos.puestos-reales.importar-puestos-modal')--}}
    </div>
    @include('includes.footer')

    