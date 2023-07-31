<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')
@php
$finicio = new DateTime($datosImplementacion->fecha_inicio);
$ffin = new DateTime($datosImplementacion->fecha_fin);
$i = "Periodo de implementación ".$finicio->format('d-m-Y')." al ".$ffin->format('d-m-Y');
$hoy = new DateTime();
@endphp

<body>
    @include('includes.navbar')
    <div class="container">
        <a href="{{ route('norma.normaTabla') }}" data-toggle="tooltip" title="Regresar" ref="Bandeja de notificaciones">
            @include('includes.back')
        </a>
        <label class="font-size-1-5em mb-3 under-line font-weight">Norma 035</label>
        <div class="container-sub">
            <img src="{{asset('img/header/norma/icono-actividades.png')}}" alt="Norma 035" class="w-px-40"> <label class="custom-title mt-3 ml-2">Actividades</label>
        </div>
        </br>
        </br>
        @if(!empty($datosImplementacion->fecha_inicio))

        <div id="nuevaImplementacion">
           
            <div class="form-row ml-1">
                <a href="{{ route('norma.actividades.exportar', $datosImplementacion->id) }}" class="button-style mb-3 nuevo" target="_blank"> <img src="/img/icono-exportar.png" class="button-style-icon"> Exportar</a>&nbsp;&nbsp;&nbsp;
                @if($hoy < $ffin) <a href="#" class="revisar button-style mb-3" data-toggle="modal" data-target="#actividadModal" data-id="'.$row->id.'"> <img src="/img/icono-crear.png" class="button-style-icon"> Crear</a>
                    @endif
            </div>
        </div>
        @endif
        <div class="article-header border">
            <div class="dataTables_filter mb-3 col-xs-12 col-md-12 col-lg-3" id="div_buscar"></div>
            <h4> {{$i}} </h4>
        </div>
        </br>

        <div class="article border">
            <div class="col-md-12">
                <table class="table w-100  tablaActividad">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Notificación</th>
                            <th>Apertura formulario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <form action="" method="post" role="form" id="implementacionActividades">
                @csrf
                <input type="hidden" id="implementacion" name="implementacion" value="{{$datosImplementacion->id}}" />
                <input type="hidden" id="implementacion_inicio" name="implementacion_inicio" value="{{$datosImplementacion->fecha_inicio}}" />
                <input type="hidden" id="implementacion_fin" name="implementacion_fin" value="{{$datosImplementacion->fecha_fin}}" />
            </form>
        </div>
        @include('norma.implementacion.modals.nueva-actividad-modal')


        <style>
            label.requerido:before {
                content: "*";
                color: red;
            }
        </style>

        <script src="{{asset('js/helper.js')}}"></script>

        <script>
            let table = $('.tablaActividad').DataTable({
                processing: true,
                serverSide: true,
                lengthChange: false,
                language: {
                    search: '',
                    searchPlaceholder: 'Buscar registros',
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                ajax: {
                "url":"{{ route('norma.actividades') }}",
                "type":"POST",
                "data": {'_token':"{{ csrf_token() }}","implementacion" : $("#implementacion").val()}
            },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'descripcion',
                        name: 'descripcion'
                    },
                    {
                        data: 'fecha_inicio',
                        name: 'fecha_inicio'
                    },
                    {
                        data: 'fecha_fin',
                        name: 'fecha_fin'
                    },
                    {
                        data: 'notificacion',
                        name: 'notificacion'
                    },
                    {
                        data: 'apertura_formulario',
                        name: 'apertura_formulario'
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [2, 'desc'],
                columnDefs: [{
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5]
                }, ],
                rowId: 'id'
            });
            $(function() {

                $('[data-toggle="tooltip"]').tooltip();

                $(".tablaActividad").on("click", ".btn.borrar", function() {
                    var id = $(this).data('id');

                    swal({
                            title: "Estas seguro de eliminar la actividad",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                document.getElementById("implementacion").value = id;

                                swal("Espere un momento, la información esta siendo procesada", {
                                    icon: "success",
                                    buttons: false,
                                });
                                borrarActividad(id);

                            } else {
                                swal("La accion fue cancelada!");
                            }
                        });

                });

                function borrarActividad(id) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    data = {
                        'idactividad': id,
                        '_token': CSRF_TOKEN
                    }

                    var url = "{{route('norma.actividades.borrar')}}";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: 'JSON',
                        async: false,
                        success: function(response) {
                            if (response.ok == 1) {
                                $(".tablaActividad tr#" + id).remove();
                                swal(response.msg, {
                                    icon: "success"
                                });

                            } else {
                                swal('Error', 'Ocurrió un error. Intente nuevamente.', {
                                    icon: "error",
                                });
                            }
                            $('.ajs-buttons .btn-warning').html('OK').attr("disabled", false);
                        }
                    });
                }

                $(".actividad").on("click", ".enviaf", function() {
                    enviaFormulario($(this).data('actividad'), $(this).data('enlace'));
                });

                function enviaFormulario(actividad, ruta) {
                    $("#actividad").val(actividad);
                    $("#accion").val(ruta);
                    $("#accionActividad").attr('action', ruta);
                    $("#accionActividad").submit();
                }

                $("#spinner").addClass("ocultar");
            });
        </script>