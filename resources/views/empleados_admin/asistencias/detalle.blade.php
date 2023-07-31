<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">

        @include('includes.header',['title'=>'Asistencias',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png',
        'route'=>'empleado.asistencias.inicio'])

            @if(session()->has('success'))
                <div class="row">
                    <div class="alert alert-success" style="width: 100%;" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>Notificación: </strong>
                        {{ session()->get('success') }}
                    </div>
                </div>
            @endif
            <form id="diasForm">
                <div class="form-row">
                    <div class="col-lg-1 col-md-12 mt-4"><p>Filtrar fecha:</p> </div>
                    <div class="col-lg-2 col-md-12 mt-2">
                        <input type="text" name="fecha_inicio" id="fecha_inicio_f" class="form-control mtp-5 input-style-custom datepicker"  placeholder="Filtrar fecha inicio"  autocomplete="off" required>
                    </div>
                    <div class="col-lg-2 col-md-12 mt-2 ">
                        <input type="text" name="fecha_fin" id="fecha_fin_f" class="form-control mtp-5 input-style-custom datepicker"  placeholder="Filtrar fecha final"  autocomplete="off" required>
                    </div>
                    <div class="col-lg-2 col-md-12 mt-3 ">
                       <button class="button-style" >Buscar</button>
                    </div>
                </div>
            </form>
            <br>
            <div class="article-header border">
                <h5><strong>Empleado: {{$empleado->nombre .' '. $empleado->apaterno. ' '. $empleado->amaterno}}</strong></h5>
                <h5><strong>Horario asignado: <u>{{$empleado->horario->entrada}}hrs - {{$empleado->horario->salida}}hrs</u></strong></h5>
            </div>
            <br>
            <div class="article border">
                <div class="wrapper-table">
                    @if($tipo_asistencia==1)
                        <table class="table col-md-12" style="width:100% " >
                            <thead>
                                <tr>
                                    <th scope="col" class="gone">Fecha</th>
                                    <th scope="col" class="gone">Entrada registrada</th>
                                    <th scope="col" class="gone">Salida registrada</th>
                                    <th scope="col" class="gone">Inicio comida</th>
                                    <th scope="col" class="gone">Fin comida</th>
                                    <th scope="col" class="gone">Asistencia</th>
                                    <th scope="col" class="gone">Retardo</th>
                                    <th scope="col" class="gone">Lugar</th>
                                    <th scope="col" class="gone">Permiso</th>
                                    <th scope="col" class="gone">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                                @foreach ($registros as $fecha => $registro)
                                @if (is_string($registro))
                                    <tr class="{{--{{($registro == 'NO ASISTIÓ') ? 'bc-red' : ''}}--}}
                                            {{($registro == 'DÍA FERIADO O INHABIL') ? 'bc-green' : ''}}
                                            {{($registro == 'DÍA NO LABORABLE') ? 'bc-gray' : ''}}">
                                                
                                        <td width="150px"> {{ dia(date('N', strtotime($fecha))).' '.formatoAFecha($fecha)}}</td>
                                        <td colspan="9" class="text-center">{{$registro}}</td> 
                                    </tr>
                                @else
                                    <tr class="{{(!$registro->asistencia) ? 'bc-red' : '' }} {{($registro->permiso) ? 'bc-green' : '' }}">
                                        <td width="150px"> {{ dia(date('N', strtotime($fecha))).' '.formatoAFecha($fecha)}} </td>
                                        {{-- <td>
                                            {{\Carbon\Carbon::parse($registro->entrada_horario)->format('H:i:s')}} hrs
                                        </td> --}}
                                        <td class="{{($registro->retardo) ? 'red' : ''}}">{{($registro->entrada != NULL)?\Carbon\Carbon::parse($registro->entrada)->format('H:i:s').' hrs':'N/A'}} </td>
                                        {{-- <td>{{\Carbon\Carbon::parse($registro->salida_horario)->format('H:i:s')}} hrs</td> --}}
                                        <td>{{($registro->salida != NULL && $registro->salida != $registro->entrada)?\Carbon\Carbon::parse($registro->salida)->format('H:i:s').' hrs': 'N/A'}} </td>

                                        <td>{{($registro->inicio_comida != null )?\Carbon\Carbon::parse($registro->inicio_comida)->format('H:i:s').' hrs': 'N/A'}}</td>

                                        <td>{{($registro->fin_comida != null )?\Carbon\Carbon::parse($registro->fin_comida)->format('H:i:s').' hrs': 'N/A'}}</td> 

                                        <td width="90px" class="text-center">
                                            @if ($registro->asistencia) 
                                                <img src="{{ asset('/img/icono-borrar-inhabilitado.png') }}" class="widht-20">
                                            @else
                                                <img src="{{ asset('/img/icono-borrar.png') }}" class="widht-20">
                                            @endif
                                        </td>
                                        <td width="90px" class="text-center">
                                            @if ($registro->retardo) 
                                                <img src="{{ asset('/img/icono-retardo-rojo.png') }}" class="widht-20" data-toggle="tooltip" title="Retardo">
                                            @else
                                                <img src="{{ asset('/img/icono-retardo.png') }}" class="widht-20">
                                            @endif
                                        </td>
                                        <td>
                                            @if($registro->coordenadas_1 != null && isValidCoords($registro->coordenadas_1))
                                                <img src="{{ asset('/img/icono-ubicacion.png') }}" class="widht-20"
                                                    data-toggle="modal" 
                                                    data-target="#asistenciaModal" 
                                                    data-coord1="{{ $registro->coordenadas_1 }}" 
                                                    data-coord4="{{ $registro->coordenadas_4 }}" 
                                                    data-horaentrada="{{$registro->entrada}}" 
                                                    data-horasalida="{{$registro->salida}}" 
                                                >
                                            @endif 
                                        </td>
                                        <td width="90px" class="text-center">
                                            @if ($registro->permiso) 
                                                <img src="{{ asset('/img/asistencia-check.png') }}" 
                                                    class="widht-20"
                                                    data-toggle="tooltip" 
                                                    title="{{$registro->motivo .' - Autorizó: '. $registro->autorizo}}">
                                                
                                            @else
                                                <img src="{{ asset('/img/icono-borrar.png') }}" class="widht-20">
                                            @endif
                                        </td>
                                        <td width="90px" class="text-center">
                                            @if ($registro->retardo || $registro->asistencia == 0)
                                                @php 
                                                    $id = Crypt::encrypt($empleado->id);
                                                    $parametros = array('id' =>$id , 'dia'=>$fecha );
                                                @endphp
                                                 <img src="{{ asset('/img/icono-agregar.png') }}" 
                                                    class="widht-20 editar"
                                                    data-toggle="tooltip" 
                                                    title="Otorgar permiso"
                                                      data-empleado="{{$empleado->id}}"
                                                      data-dia="{{$fecha}}">
                                            @else
                                                <img src="{{ asset('/img/icono-agregar-inhabilitado.png') }}" class="widht-20">
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>    
                    @else
                        <table class="table col-md-12" style="width:100% " >
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    {{-- <th width="">Hora Entrada</th> --}}
                                    <th>Entrada registrada</th>                       
                                    {{-- <th width="">Hora Salida</th> --}}
                                    <th>Salida registrada </th>  
                                    <th class="text-center" >Horas trabajadas</th>  
                                    <th>Lugar</th>
                                    <th class="text-center" >Permiso</th>
                                    <th class="text-center" >Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($registros as $fecha => $registro)
                                    @if (is_string($registro))
                                        <tr class="{{-- {{($registro == 'NO ASISTIÓ') ? 'bc-red' : ''}} --}}
                                                {{($registro == 'SIN REGISTRO') ? 'bc-red' : ''}}
                                                {{($registro == 'DÍA FERIADO O INHABIL') ? 'bc-green' : ''}}
                                                {{($registro == 'DÍA NO LABORABLE') ? 'bc-gray' : ''}}"> 
                                            <td>{{ dia(date('N', strtotime($fecha))).' '.formatoAFecha($fecha)}}</td>
                                            <td colspan="9" class="text-center">{{$registro}}</td> 
                                        </tr>
                                    @else
                                        <tr>
                                            <td>{{ dia(date('N', strtotime($fecha))).' '.formatoAFecha($fecha)}}</td>
                                            {{-- <td>{{\Carbon\Carbon::parse($registro->entrada_horario)->format('H:i:s')}} hrs </td> --}}
                                            <td>{{($registro->entrada != NULL)?\Carbon\Carbon::parse($registro->entrada)->format('H:i:s').' hrs':'N/A'}} </td>
                                            {{-- <td>{{\Carbon\Carbon::parse($registro->salida_horario)->format('H:i:s')}} hrs</td> --}}
                                            <td>{{($registro->salida != NULL && $registro->salida != $registro->entrada)?\Carbon\Carbon::parse($registro->salida)->format('H:i:s').' hrs': 'N/A'}} </td>
                                            <td class="text-center" >
                                                @if( (isset($registro->entrada) && $registro->entrada != NULL) && (isset($registro->salida) && $registro->salida != NULL) )
                                                
                                                {{  Carbon\Carbon::parse($registro->salida)->diffInHours(Carbon\Carbon::parse($registro->entrada)) }}
                                                {{-- number_format((Carbon\Carbon::parse($asistencias[$empleado->id]->salida)->diffInMinutes(Carbon\Carbon::parse($asistencias[$empleado->id]->entrada)))/60,2) --}}
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->coordenadas_1 != null && isValidCoords($registro->coordenadas_1))
                                                    <img src="{{ asset('/img/icono-ubicacion.png') }}" class="widht-20"
                                                        data-toggle="modal" 
                                                        data-target="#asistenciaModal" 
                                                        data-coord1="{{ $registro->coordenadas_1 }}" 
                                                        data-coord4="{{ $registro->coordenadas_4 }}" 
                                                        data-horaentrada="{{$registro->entrada}}" 
                                                        data-horasalida="{{$registro->salida}}" 
                                                    >
                                                @endif 
                                            </td>
                                            <td class="text-center" >
                                                @if ($registro->permiso) 
                                                    <img src="{{ asset('/img/asistencia-check.png') }}" 
                                                        class="widht-20"
                                                        data-toggle="tooltip" 
                                                        title="{{$registro->motivo .' - Autorizó: '. $registro->autorizo}}">
                                                @else
                                                    <img src="{{ asset('/img/icono-borrar.png') }}" class="widht-20">
                                                @endif
                                            </td>
                                            <td class="text-center" >
                                                @if ($registro->retardo || $registro->asistencia == 0)
                                                    @php 
                                                        $id = Crypt::encrypt($empleado->id);
                                                        $parametros = array('id' =>$id , 'dia'=>$fecha );
                                                    @endphp
                                                    <img src="{{ asset('/img/icono-agregar.png') }}" 
                                                        class="widht-20 editar"
                                                        data-toggle="tooltip" 
                                                        title="Otorgar permiso"
                                                        data-empleado="{{$empleado->id}}"
                                                        data-dia="{{$fecha}}">
                                                @else
                                                    <img src="{{ asset('/img/icono-agregar-inhabilitado.png') }}" class="widht-20">
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        @include('empleados_admin.asistencias.modals.geolocalizacion-asistencia-modal')
        @include('includes.footer')
        @include('empleados_admin.asistencias.otorgar-permisos')
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDS6yKYVuwzrQx_ovIlwgE9Zj0M8l4Oqwg&callback="></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="{{asset('js/datapicker-es.js')}}"></script>
        <script>

            let map;
            
            $(function() {
                  
                $('#tbl').DataTable({
                    "sScrollX": '100%',
                    "order": [[ 0, 'asc' ]],
                    "language": {
                        search: '',
                        searchPlaceholder: 'Buscar registros',
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    columnDefs: [{ 
                        className: "text-center", "targets": []
                    }],
                });
                // Campo fechas Jquery UI
                 $( ".datepicker" ).datepicker({
                    changeMonth: true,
                    changeYear: true
                });
                $( ".datepicker" ).datepicker( "option", "dateFormat", 'yy-mm-dd' );
                
                $( "#fecha_inicio_f, #fecha_fin_f" ).datepicker( "option", "maxDate", '+0m +0w');
                $( "#fecha_inicio_f" ).datepicker( "setDate", '{{$fecha_inicio}}');
                $( "#fecha_fin_f" ).datepicker( "setDate", '{{$fecha_fin}}');

                $("#fecha_inicio").datepicker().on("change", function() {
                    $("#fecha_fin_f" ).datepicker( "option", "minDate", this.value);
                    $("#fecha_fin_f" ).datepicker( "setDate", this.value);
                });

                $('#diasForm').submit(function(){});

                $('#asistenciaModal').on('shown.bs.modal', function (e) {

                    let coord1 = $(e.relatedTarget).data('coord1');
                    let coord4 = $(e.relatedTarget).data('coord4');
                    let horaentrada = $(e.relatedTarget).data('horaentrada');
                    let horasalida = $(e.relatedTarget).data('horasalida');

                    initMap(coord1, coord4, horaentrada, horasalida);
                });

                $(".editar").on('click', async function (e) {

                    $("#otorgarPermisoModal").modal('show');
                    let url = "{{route('empleado.asistencias.permisoPersonal')}}";

                    const response = await  fetch(url,{
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),
                                'Content-Type': 'application/json'},
                        body: JSON.stringify({'empleado' : $(this).data('empleado') ,'dia':$(this).data('dia') })
                    });

                    const res = await response.json();
                    const {fecha_inicio,fecha_fin,motivo,tipo_permiso} = res;

                    let dia_actual = "@php echo $fecha @endphp";

                    $("#fecha_inicio").datepicker("setDate", fecha_inicio);
                    $("#fecha_fin").datepicker("setDate", fecha_fin);
                    $("#motivo").val(motivo);

                    const radioButtons = document.querySelectorAll('input[name="tipo_permiso"]');
                    for(const radioButton of radioButtons){
                        if(radioButton.value==tipo_permiso){
                            document.querySelector(`#${tipo_permiso}`).checked=true;
                        }
                    }


                    $("#fecha_inicio_").val(fecha_inicio);
                    $("#fecha_fin_").val(fecha_fin);
                    $("#idemp").val($(this).data('empleado'));
                    $("#dia_").val($(this).data('dia'));

                });
            });
            
         
            const  initMap = (coord1, coord2, horaentrada, horasalida) =>{

                coord1 = coord1.split(',');
                coord2 = coord2.split(',');

                let coords1 = {lat: Number(coord1[0]), lng: Number(coord1[1])};
                let coords2 = {lat: Number(coord2[0]), lng: Number(coord2[1])};
                
                map = new google.maps.Map(document.getElementById('map'), {
                    center: coords1,
                    zoom: 15
                });
                let marker = new google.maps.Marker({
                    
                    position: coords1,
                    map: map,
                    title: "Entrada: "+horaentrada
                });

                    marker = new google.maps.Marker({
                    position: coords2,
                    map: map,
                    title: 'Salida: '+horasalida
                });
            }

        </script>
    </body>
</html>
