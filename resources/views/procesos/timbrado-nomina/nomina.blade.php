<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php

@endphp

<div class="container">
@include('includes.header',['title'=>'Timbrado de nómina',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'bandeja'])

        <div class="text-center">
            @if ($error)
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">No disponible!</h4>
                    <p>¡No hay períodos de nómina disponibles a calcular!</p>
                    <hr>
                </div>
            @else
                <div class="row justify-content-center mb-3">
                    <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="display: flex; align-items: center; border-radius: 7px;">
                        <h5 class="card-title font-size-1-5em font-weight-bold pt-3 text-center">Selecciona los departamentos a considerar</h5>
                    </div>
                </div>              
                <form id="form_depto" method="POST" action="{{route('timbrar.nomina')}}">
                    @csrf
                    <div class="row justify-content-center mb-3">
                        <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="border-radius: 7px;">
                            <input type="hidden" name="id_periodo" value="{{$id_periodo}}" >
                            <div class="my-3" style="text-align: left; margin: auto;">
                                <div class="form-check">
                                    <input class="form-check-input check" type="checkbox" value="1" name="todos" id="todos">
                                    <label class="form-check-label" for="todos">
                                        <h5><strong>Marcar todos/desmarcar todos:</strong></h5>                                
                                    </label>
                                </div>
                                <div class="deptos mt-3">
                                    @foreach ( $departamentos as $d )
                                        <div class="form-check mb-1">
                                            <input class="form-check-input check" type="checkbox" value="{{ $d->depto }}" name="deptos[]" id="{{
                                                $d->depto}}">
                                            <label class="form-check-label" for="{{$d->depto}}">
                                                <strong>{{ucfirst(Str::lower($d->nombre))}}</strong>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <input type="submit" class="btn bg-color-yellow" value="Generar"> -->
                    <div class="row justify-content-center">
                        <div class="row justify-content-center col-xs-12 col-md-12 col-lg-3">
                            <button type="submit" class="button-style btn-block">GENERAR</button>
                        </div>
                    </div>
                </form>
                
            @endif
        </div>
</div>
@include('includes.footer')
<script>
    $(function(){
        $("#todos").click(function () {
            $(".check").prop('checked', $(this).prop('checked'));
        });

        $('#form_depto').submit( function(e) {
            let valida=false;
            if($('.check').is(':checked') ){
                valida = true;
            }else{
                // console.log('seleccione')
                swal("", "Seleccione al menos un departamento.");
            }
            return valida;
        });
    });


</script>
