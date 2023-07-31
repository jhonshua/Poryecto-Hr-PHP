@extends('layouts.empleado')
@section('tituloPagina', "Solicitudes de beneficios generadas para: " . Session::get('empleado')['nombre'].' '.Session::get('empleado')['apaterno'].' '.Session::get('empleado')['amaterno'])
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="col-12">
                            <div class="article border text-center">
                                <div class="mb-3 d-flex justify-content-between">
                                    <h4 class="m-0">En este apartado podrás encontrar tus beneficios solicitados , así
                                        mismo ver en que proceso se encuentra cada beneficio por medio del estatus
                                        (Abierto , Pendiente o Rechazado ), y en que fecha se concluyó dicho
                                        proceso.</h4>
                                </div>
                                <table class="table w-100" id="tblforms">
                                    <thead>
                                    <tr>
                                        <th>Nombre de la solicitud</th>
                                        <th>Medio de contacto</th>
                                        <th>Fecha de creación.</th>
                                        <th>Estatus</th>
                                        <th>Ver proceso</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
@push('css')

    <style>
        .label-success {
            color: rgb(56, 193, 114);
        }

        .label-error {
            color: #c82333;
        }

        .label-warning {
            color: #F0C018;
        }
    </style>
@endpush

@push('scripts')

    <script>
        $(function () {

            const table = $('#tblforms').DataTable({ // Aquí se carga la tabla que se muestra al inicio de la vista
                aProcessing: true,
                aServerside: true,
                lengthChange: false, //Si se desea que se muestre el paginador hay que quitar esta línea
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
                },
                ajax:
                    {
                        type: "GET",
                        url: "{{route('empleado.obtenerSolicitudes.general')}}",
                        dataType: "json",
                        error: function (e) {
                            console.log(e.responseText);
                        }
                    },
                bDestroy: true,
                iDisplayLength: 8,//paginacion cada 5 registros
                order: [[0, "desc"]],
            });

        });
    </script>
@endpush
