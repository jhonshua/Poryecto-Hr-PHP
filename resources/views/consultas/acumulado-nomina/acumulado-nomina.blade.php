<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    @php

    @endphp

    <div class="container">

        @include('includes.header',['title'=>'Reporte acumulado de nomina',
        'subtitle'=>'Consultas', 'img'=>'img/icono-reporte-asistencias.png',
        'route'=>'bandeja'])

        <div class="text-center">

            <div class="row justify-content-center mb-3">
                <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="display: flex; align-items: center; border-radius: 7px;">
                    <h5 class="card-title font-size-1-5em font-weight-bold pt-3 text-center">Selecciona los departamentos a considerar</h5>
                </div>
            </div>
            <form method="post" id="aguinaldo" action="{{ route('reporte.validaAcumuladoNomina') }}" target="_blank">
                @csrf
                <div class="row justify-content-center mb-3">
                    <div class="card col-sm-12 col-xs-12 col-md-6 col-lg-6" style="border-radius: 7px;">
                        <div class="my-3" style="text-align: left; margin: auto;">
                            <div class="form-check">
                                <input class="form-check-input check" type="checkbox" value="1" name="todos" id="todos">
                                <label class="form-check-label" for="todos">
                                    <h5><strong>Marcar todos/desmarcar todos:</strong></h5>
                                </label>
                            </div>
                            <div class="deptos mt-3">
                                @foreach ( $departamentos as $departamento )
                                <div class="form-check mb-1">
                                    <input class="form-check-input check" type="checkbox" value="{{ $departamento->id }}" name="deptos[]" id="{{
                                                $departamento->id }}">
                                    <label class="form-check-label" for="{{$departamento->id}}">
                                        <strong>{{ucfirst(Str::lower($departamento->nombre))}}</strong>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <hr>
                            <div class="text-center">
                                <p>¿Qué tipo de reporte deseas generar?</p>



                                <div class="col-6 d-flex justify-content-left mb-2">
                                    <input type="radio" class="mr-2" value="pdf" name="tipo_archivo" id="pdf" checked>
                                    <label for="pdf">PDF</label>

                                </div>

                                <div class="col-6 d-flex justify-content-left mb-2">
                                    <input type="radio" class="mr-2" value="excel" name="tipo_archivo" id="excel">
                                    <label for="excel">Excel</label>

                                </div>

                            </div>
                            <hr>
                            <div class="text-center">
                                <p>¿Qué temporalidad deseas aplicar?</p>

                                <div class="col-6 d-flex justify-content-left mb-2">
                                    <input type="radio" class="mr-2" value="mes" name="tipo" id="mensual" checked>
                                    <label for="mensual">Mensual</label>
                                    <select class="input-style ml-3" name="mes">
                                        @foreach ($meses as $mes)
                                        <option value="{{str_pad($mes->mes, 2, "0", STR_PAD_LEFT)}}">{{$mes->mes}}º mes</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 d-flex justify-content-left mb-2">
                                    <input type="radio" class="mr-2" value="bim" name="tipo" id="bimestral">
                                    <label for="bimestral">Bimestre</label>
                                    <select class="input-style ml-3" name="bim">
                                        @foreach ($bimestres as $bimestre)
                                        <option value="{{ $bimestre->bimestre }}">{{$bimestre->bimestre}}º bimestre</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 d-flex justify-content-left mb-2">
                                    <input type="radio" class="mr-2" value="ano" name="tipo" id="anual">
                                    <label for="anual">Anual</label>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="row justify-content-center col-xs-12 col-md-12 col-lg-2">
                        <button type="submit" class="button-style btn-block">GENERAR</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            $("#todos").click(function() {
                $(".check").prop('checked', $(this).prop('checked'));
            });
            $('#aguinaldo').submit(function(e) {
                if ($('.check').length <= 0) {
                    e.preventDefault();
                    swal("No se a seleccionado ningún departamento !", {
                        icon: "error",
                    });
                }
                $('#aguinaldo .imprimir').attr('disabled', true).text('ESPERE ... ');
            });
            $('#aguinaldo .fecha_aguinaldo').change(function(){
                if($(this).val() == 'otra'){

                }
            })
        });
    </script>