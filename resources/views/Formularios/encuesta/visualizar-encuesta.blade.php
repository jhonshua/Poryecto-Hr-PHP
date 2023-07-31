<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Visualizar encuesta',
        'subtitle'=>'Formularios', 'img'=>'img/header/norma/icono-norma.png',
        'route'=>'formularios.inicio'])

        <div class="article border">
            <h4 class="text-center font-weight-bold">{{$data['datos_generales']['titulo']}}</h4>
            <br>
            <div class="form-row">
                <div class="col-md-7"></div>
                <div class="col-md-5 mt-2">
                    <p class="font-size-1-2em float-right">Fecha de vencimiento: {{$data['datos_generales']['fecha_vencimiento']}}</p>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-12">
                    <p class="font-size-1-2em text-indent-custom">Descripción : {{$data['datos_generales']['descripcion']}}</p>
                </div>
            </div>
            <br>
            <div class="form-row">
                <div class="col-md-12">
                    <div id="contenidoItems">

                        @foreach ($data['preguntas'] as $key => $pregunta)
                            @php
                            $contador = $key + 1;
                            $string = explode('*#--#',$pregunta );
                            $id = $string[0];
                            $pregunta = $string[1];
                            $id_tipo = $string[3];
                            $lleva_icono = $string[4];
                            @endphp

                            @switch($id_tipo)
                                @case(2)
                                
                                    <div class="content" id="radios"><strong>Pregunta {{$contador}}: </strong> {{$pregunta}} 
                                        @foreach($data['datos_opc_preguntas'][$id] as $opc_pregunta)

                                            @php

                                                $contador = $key + 1;
                                                $stringOpc = explode('*#--#',$opc_pregunta);
                                                $id_opc = $stringOpc[0];
                                                $pregunta_opc = $stringOpc[1];
                                            @endphp
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1">
                                                <label class="form-check-label" for="exampleRadios1">
                                                    {{$pregunta_opc}}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div><br>
                                @break
                                @case(3)
                                    
                                    <div class="form-row" id="row_select">
                                        <div class="form-group col-md-12 col-sm-12"><strong>Pregunta {{$contador}}: </strong> {{$pregunta}} </div>
                                        @foreach($data['datos_opc_preguntas'][$id] as $opc_pregunta)
                                        @php

                                        $contador = $key + 1;
                                        $stringOpc = explode('*#--#',$opc_pregunta);
                                        $id_opc = $stringOpc[0];
                                        $pregunta_opc = $stringOpc[1];
                                        $lleva_icono = $stringOpc[3];
                                        $id_pregunta = $stringOpc[4];

                                        @endphp
                                        @if($lleva_icono == "1" )
                                            @foreach ($data['det_iconos'][$id_opc] as $icons)
                                            @php

                                            $icons = explode('*#--#',$icons);
                                            $icono = $icons[1];
                                            @endphp

                                            <div class="form-group col-md-2 col-sm-4">
                                                <div class="ml-2">
                                                    <img src="{{asset('storage/configuracion-formularios/svg/'.$icono )}}" class="iconcustom">
                                                </div>
                                                <div class="mt-3"><label>{{$pregunta_opc}} </label></div>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="form-group col-md-2 col-sm-4"><div ><label>{{$pregunta_opc}} </label></div></div>
                                        @endif
                                        @endforeach
                                    </div>
                                @break
                                @case(4)
                                <div class="row">
                                    <div class="form-group col-md-12 col-sm-12"><strong>Pregunta {{$contador}}: </strong> {{$pregunta}} </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <table class="table" id="tblcheck" >
                                            <thead>
                                                <tr>
                                                    <th>Selección</th>
                                                    <th>Respuestas</th>                       
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="checkbox" name="respuestas" value="" disabled ></td>
                                                    <td><b>Seleccione alguna opción </b></td>
                                                </tr>
                                                @foreach($data['datos_opc_preguntas'][$id] as $opc_pregunta)

                                                    @php
                                                        $stringOpc = explode('*#--#',$opc_pregunta);
                                                        $pregunta_opc = $stringOpc[1];
                                                    @endphp
                                                    <tr>
                                                        <td><input type="checkbox" name="respuestas[]" disabled></td>
                                                        <td>{{$pregunta_opc}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @break
                            @default
                                <strong>Pregunta {{$contador}} : </strong>{{$pregunta}}
                                <div class="content mb-3">
                                    <input type="text" class="form-control form-control-sm " placeholder="Ingresa tu respuesta" name="respuestas[]" readonly diabled>
                                </div>

                            @endswitch
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('formularios.encuesta.modals.descripcion_modal')

    <script>
        $(document).ready(function() {

            $('#tbl').DataTable({
                "language": {
                    search: '',
                    searchPlaceholder: 'Buscar registros',
                    "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                }
            });

        });
    </script>
    @include('includes.footer')
</body>
</html>