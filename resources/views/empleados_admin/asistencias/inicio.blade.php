<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('includes.head')
    <body>
        @include('includes.navbar')
        <div class="container">

        @include('includes.header',['title'=>'Asistencia',
        'subtitle'=>'Empleados', 'img'=>'/img/Recurso 16.png',
        'route'=>'bandeja'])

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
            <div class="row">
                <div class="col-lg-5 col-md-12 mb-2">
                    <button type="button"
                        class="button-style sync"
                        data-toggle="tooltip"
                        title="Sincronización de biométricos"
                        data-id-empresa = {{Crypt::encrypt(Session::get('empresa')['id'])}}
                        data-dia={{$dia}}
                    ><i class="fas fa-sync"></i></button>
                    <a href="{{ url('/asistencias-agregar-permiso') }}" ref="Agregar permiso">
                        <button type="button" class="button-style " alt="Asignar permiso a todo el listado" title="Asignar permiso a todo el listado"> <img src="{{ asset('/img/icono-crear.png') }}" width="20px">
                            Crear nuevo
                        </button>
                    </a>
                    <button type="button" class="button-style mt-1" data-toggle="modal" data-target="#importarModal" title="Importar"><img src="{{ asset('/img/icono-importar.png') }}" width="20px"> Importar</button>
                    <button type="button" class="button-style mt-1" data-toggle="modal" data-target="#exportarPModal" title="Exportar"><img src="{{ asset('/img/icono-exportar.png') }}" width="20px"> Exportar</button>
                </div>
                <div class="col-lg-2 col-md-12 mb-2">
                    <select id="filtro" class="form-control input-style-custom select-clase" style="width: 100%">
                        <option value="">TODOS</option>
                        @foreach ($departamentos as $depto)
                        <option value="{{$depto->nombre}}">{{$depto->nombre}}</option>
                    @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 mb-2">
                    <form id="diaForm">
                        <input type="text" name="dia" id="dia" value="{{$dia}}"  class="form-control  input-style-custom datepicker"  placeholder="{{$dia}}"  autocomplete="off" required>
                    </form>
                </div>
                <div class="dataTables_filter  col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
            </div>
            <br>
            <div class="article-header border">
                <h5><strong>Asistencias del día: {{ dia(date('N', strtotime($dia))).' '.formatoAFecha($dia)}}</strong></h5>
            </div>
            <br>
            <div class="article border">
                <table id="tbl" class="table col-md-12 w-100">
                    <thead>
                        <tr>
                            <th scope="col" class="gone">ID</th>
                            <th scope="col" class="gone">Empleado</th>
                            <th scope="col" class="gone">Nombre</th>
                            <th scope="col" class="gone">Departamento</th>
                            <th scope="col" class="gone">Horario entrada</th>
                            <th scope="col" class="gone">Horario salida</th>
                            <th scope="col" class="gone">Asistencia</th>
                            <th scope="col" class="gone">Home office</th>
                            <th scope="col" class="gone">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($tipo_asistencia !==2)
                            @foreach ($empleados as $empleado)
                                <tr
                                    id="{{$empleado->id}}"
                                    data-id="{{$empleado->id}}"
                                    data-dia="{{$empleado->dia}}"
                                    data-id_departamento="{{$empleado->id_departamento}}"
                                    data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}">

                                    <td>{{$empleado->id}}</td>
                                    <td>{{$empleado->numero_empleado}}</td>
                                    <td>{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}</td>
                                    <td>{{$departamentos[$empleado->id_departamento]->nombre}}</td>
                                    <td>
                                        {{(isset($asistencias[$empleado->id]->entrada) && $asistencias[$empleado->id]->entrada != NULL) ? date('H:i \h\r\s', strtotime($asistencias[$empleado->id]->entrada)) : 'N/A' }}
                                    </td>
                                    <td>
                                        {{(isset($asistencias[$empleado->id]->salida) && $asistencias[$empleado->id]->salida != NULL && $asistencias[$empleado->id]->salida != $asistencias[$empleado->id]->entrada) ? date('H:i \h\r\s', strtotime($asistencias[$empleado->id]->salida)) : 'N/A' }}
                                    </td>

                                    <td>
                                        @if (isset($asistencias[$empleado->id]) && $asistencias[$empleado->id]->asistencia)
                                            <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25">
                                        @else
                                            
                                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25">
                                        @endif
                                    </td>
                                    <td>              
                                        @if (isset($asistencias[$empleado->id]) && $asistencias[$empleado->id]->lugar)
                                            @if( $asistencias[$empleado->id]->lugar ==='APP' )
                                                <img src="{{ asset('/img/asistencia-check.png') }}" class="widht-25">
                                            @else
                                                <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25">
                                            @endif
                                        
                                        @else
                                            <img src="{{ asset('/img/sin-archivo.png') }}" class="widht-25">
                                        @endif
                                    </td>
                                    <td class="position-relative">
                                        @php
                                            $id = Crypt::encrypt($empleado->id);
                                            $parametros = array('id' =>$id , 'dia'=>$dia );
                                        @endphp
                                        <a href="{{route('empleado.asistencias.detalle', $id)}}"  data-toggle="tooltip" title="Ver registro  de asistencias">
                                            <img src="{{ asset('/img/ver-documentos-empleado.png') }}" class="widht-20" ></a>

                                        @if( @!$asistencias[$empleado->id] || optional($asistencias[$empleado->id])->asistencia == 0 || optional($asistencias[$empleado->id])->retardo)
                                            <button  
                                                class="editar btn btn-sm"  
                                                alt="Otorgar permiso" 
                                                title="Otorgar permiso" 
                                                data-toggle="modal" 
                                                data-id="{{$empleado->id}}"
                                                data-dia="{{$dia}}">
                                                <img src="{{ asset('/img/permisos.png') }}" class="widht-20 mostrarModal" >
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($empleados as $empleado)
                                <tr
                                    id="{{$empleado->id}}"
                                    data-id="{{$empleado->id}}"
                                    data-dia="{{$empleado->dia}}"
                                    data-id_departamento="{{$empleado->id_departamento}}"
                                    data-nombre="{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}"
                                >
                                    <td>{{$empleado->id}}</td>
                                    <td>{{$empleado->numero_empleado}}</td>
                                    <td>{{$empleado->apaterno}} {{$empleado->amaterno}} {{$empleado->nombre}}</td>
                                    <td>{{$departamentos[$empleado->id_departamento]->nombre}}</td>
                                    <td>{{(isset($asistencias[$empleado->id]->entrada) && $asistencias[$empleado->id]->entrada != NULL) ? date('H:i \h\r\s', strtotime($asistencias[$empleado->id]->entrada)) : 'N/A' }}</td>
                                    <td>{{(isset($asistencias[$empleado->id]->salida) && $asistencias[$empleado->id]->salida != NULL && $asistencias[$empleado->id]->salida != $asistencias[$empleado->id]->entrada) ? date('H:i \h\r\s', strtotime($asistencias[$empleado->id]->salida)) : 'N/A' }}</td>
                                    <td>
                                        @if( (isset($asistencias[$empleado->id]->entrada) && $asistencias[$empleado->id]->entrada != NULL) && (isset($asistencias[$empleado->id]->salida) && $asistencias[$empleado->id]->salida != NULL) )

                                            {{  Carbon\Carbon::parse($asistencias[$empleado->id]->salida)->diffInHours(Carbon\Carbon::parse($asistencias[$empleado->id]->entrada)) }}
                                            {{-- number_format((Carbon\Carbon::parse($asistencias[$empleado->id]->salida)->diffInMinutes(Carbon\Carbon::parse($asistencias[$empleado->id]->entrada)))/60,2) --}}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $id = Crypt::encrypt($empleado->id);
                                            $parametros = array('id' =>$id , 'dia'=>$dia );
                                        @endphp
                                        <a href="{{route('empleado.asistencias.detalle', $id)}}" class="button-style-custom" data-toggle="tooltip" title="Ver registro  de asistencias">VER</a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @include('includes.footer')
        @include('empleados_admin.asistencias.modals.exportar-asistencias-modal')
        @include('empleados_admin.asistencias.modals.importar-asistencias-modal')
        @include('empleados_admin.asistencias.otorgar-permisos')
        <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
        <!-- Cambiar idioma de parsley -->
        <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="{{asset('js/datapicker-es.js')}}"></script>
        <script src="{{asset('js/helper.js')}}"></script>
        <script src="{{asset('js/typeahead.js')}}"></script>
        <script>

        let dataSrc = [];
        let table = $('#tbl').DataTable({
            scrollY:'65vh',
            scrollCollapse: true,
            "language": {
                search: '',
                searchPlaceholder: 'Buscar registros por nombre',
                "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
            },
            initComplete: function() {
             
                let api = this.api();
            
                api.cells('tr', [2]).every(function(){

                let data = $('<div>').html( this.data()).text(); if(dataSrc.indexOf(data) === -1){ dataSrc.push(data); }});
                dataSrc.sort();
                
                $('.dataTables_filter input[type="search"]', api.table().container())
                    .typeahead({
                        source: dataSrc,
                        afterSelect: function(value){
                            api.search(value).draw();
                        }
                    }
                );

                // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                let elementos = $(".dataTables_filter > label > input").detach();
                elementos.appendTo('#div_buscar');
                $("#div_buscar > input").addClass("input-style-custom");
            },
        });



            $('#filtro').on('change', function() {
                table
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $(document).ready(function() {

                $('.select-clase').select2();

                $( ".datepicker" ).datepicker({
                    changeMonth: true,
                    changeYear: true
                });

                $(".datepicker" ).datepicker( "option", "dateFormat", 'yy-mm-dd' );

                $("#dia" ).datepicker( "option", "maxDate", '+0m +0w');

                $('#dia').change(function(){

                    $('#diaForm').submit();
                });

                $(".sync").click(function(){

                    let idempresa = $(this).data('id-empresa');
                    let dia = $(this).data('dia');

                    swal({

                        title: `¿ Está seguro de sincronizar el biométrico ?`,
                        text: "Preparando la ejecución !",
                        icon: "warning",
                        buttons:  ["Cancelar", true],
                        dangerMode: true,

                    }).then((willDelete) => {
                        if (willDelete) {
                            syncBiometrico(idempresa,dia).then(data=>{
                                const  {respuesta }=data;

                                if (respuesta==1){

                                    swal("Sincronización completada correctamente!", {
                                        icon: "success",
                                    });

                                    location.reload();
                                }else if(respuesta == 2){

                                    swal("La sincronización no se pudo completar contacta a tu administrador!", {
                                        icon: "warning",
                                    });

                                }else{
                                    swal("Error  en sincronización comunicate con tu administrador  !", {
                                        icon: "error",
                                    });
                                }
                            });
                        }
                    });
                });

                $('.importar').click(function(){

                    let form = $("#form-import");
                    if(form.parsley().isValid()){
                        $(this).text('Espere...');
                        $(this).prop('disabled', true);
                        form.submit();
                    }else{
                        form.parsley().validate();
                    }

                });

                $(".editar").on('click', async function (e) {

                    $("#otorgarPermisoModal").modal('show');

                    let dia_actual = "@php echo $dia @endphp";
           
                    $("#fecha_inicio").datepicker("setDate", dia_actual);
                    $("#fecha_fin").datepicker("setDate", dia_actual);

                    $("#idemp").val($(this).data('id'));
                    $("#dia_").val($(this).data('dia'));

                });
                
            });

            const syncBiometrico = async (idempresa,dia) =>{

                let url = "{{route('empleado.asistencias.registroAsistenciasCron')}}";

                const response = await  fetch(url,{
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),
                              'Content-Type': 'application/json'},
                    body: JSON.stringify({'idempresa' : idempresa ,'dia':dia })
                });

                const res = await response.json();
                return res;
            }       
        </script>
    </body>
</html>
