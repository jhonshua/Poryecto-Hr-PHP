<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
<body>
@include('includes.navbar')
@php
//print_r($departamentos);
@endphp

<div class="container">
@include('includes.header',['title'=>'Cálculo de aguinaldo',
        'subtitle'=>'Procesos de cálculo', 'img'=>'img/icono-captura-incidencias.png',
        'route'=>'bandeja'])

        <div class="text-center">           
                <div class="row justify-content-center mb-3">
                    <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="display: flex; align-items: center; border-radius: 7px;">
                        <h5 class="card-title font-size-1-5em font-weight-bold pt-3 text-center">Selecciona los departamentos a considerar</h5>
                    </div>
                </div>              
                <form id="form_depto" method="POST" action="{{route('procesos.pre.aguinaldo')}}">
                    @csrf
                    <div class="row justify-content-center mb-3">
                        <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="border-radius: 7px;">
                            <!-- <input type="hidden" name="id_periodo" value="" > -->
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
                                            <input class="form-check-input check" type="checkbox" value="{{ $d->id }}" name="deptos[]" id="{{
                                                $d->id}}">
                                            <label class="form-check-label" for="{{$d->depto}}">
                                                <strong>{{ucfirst(Str::lower($d->nombre))}}</strong>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                                <div class="text-center">                    
                                    <p>¿Deseas que se calcule el ajuste anual?</p>
                                    <!-- <img src="http://127.0.0.1/HRSystem_v2/public/img/exclamation-mark-sign.png" width="11px" height="11px" alt="Determina diferencias de ISR" title="Determina diferencias de ISR"/> -->
                                    SI <input type="radio" name="impuestoanual" value="1" class="mr-3"> 
                                    NO <input type="radio" name="impuestoanual" value="0" checked>
                                </div>
                                <hr>
                                <div class="">                                    
                                    <p>¿Qué periodo deseas que se tome en cuenta para el cálculo de aguinaldo?</p>

                                    <select class="form-control input-style-custom select-clase" name="ejercicio" style="width: 100%!important;">
                                        <option value="{{date('Y')}}" selected>{{date('Y')}}</option>
                                        <option value="{{(date('Y')-1)}}">{{(date('Y')-1)}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>   
                    <div class="row justify-content-center">
                        <div class="row justify-content-center col-xs-12 col-md-12 col-lg-3">
                            <button type="submit" class="button-style btn-block">CONTINUAR</button>
                        </div>
                    </div>
                </form>                
        </div>
</div>
@include('includes.footer')
<script>
    $(function(){
        $('.select-clase').select2();
        $("#todos").click(function () {
            $(".check").prop('checked', $(this).prop('checked'));
        });

        $('#form_depto').submit( function(e) {
            let valida=false;
            if($('.check').is(':checked') ){
                valida = true;
            }else{         
                swal("", "Seleccione al menos un departamento.", {
                    icon: "warning",
                });
            }
            return valida;
        });
    });


</script>
