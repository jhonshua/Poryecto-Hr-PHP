<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('includes.head')

<body>
    @include('includes.navbar')
    <div class="container">

        @include('includes.header',['title'=>'Reporte de nóminas por periodo',
        'subtitle'=>'Consultas', 'img'=>'img/icono-parametros-empresa.png',
        'route'=>'bandeja'])

        @if(session()->has('success'))
        <div class="row">
            <div class="alert alert-success" style="width: 100%;" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Notificación: </strong>
                {{ session()->get('success') }}
            </div>
        </div>
        @endif
        <div class="row">
        <div class="col-lg-6">
        </div>
            <div class="col-lg-6 mb-3">
                <div class="dataTables_filter col-xs-12 col-md-12" id="div_buscar"></div>
            </div>
        </div>


        <div class="article border">

            <table class="table w-100 reporteNomina" id="reporteNomina">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Nombre periodo</th>
                        <th>Fecha inicial</th>
                        <th>Fecha final</th>
                        <th>Ejercicio</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody class="text-center">
                    @foreach($periodos as $periodo)
                    @if($periodo->activo != App\Models\PeriodosNomina::ACTIVO)
                    <tr id="{{$periodo->id}}">
                        <td>{{$periodo->numero_periodo}}</td>
                        <td>{{strtoupper($periodo->nombre_periodo)}}</td>
                        <td>{{formatoAFecha($periodo->fecha_inicial_periodo)}}</td>
                        <td>{{formatoAFecha($periodo->fecha_final_periodo)}}</td>
                        <td>{{$periodo->ejercicio}}</td>
                        <td>
                            @if($periodo->activo == App\Models\PeriodosNomina::CERRADO)
                            <span class="text-black-50">Periodo cerrado</span>
                            @elseif($periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR || $periodo->activo == App\Models\PeriodosNomina::DISP_ABRIR_PERIODO_ACT)
                            <span class="text-danger">Periodo sin calcular</span>
                            @endif
                        </td>
                        <td>
                            <a name="exportar_pdf" href="#" onclick="exportar('PDF','{{$periodo->id}}','{{$periodo->ejercicio}}')" role="button" class="editar" alt="Descargar PDF" title="Descargar PDF">
                                <img src="/img/icono-pdf.png" class="button-style-icon">
                            </a>
                            <a name="exportar_pdf" href="#" onclick="exportar('EXCEL','{{$periodo->id}}','{{$periodo->ejercicio}})" class="editar" alt="Descargar excel" title="Descargar excel">
                                <img src="/img/icono-registro-p.png" class="button-style-icon">
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('consultas.reporte-nominasPeriodo.reporte-nominasPeriodoModal')

        <script src="{{asset('js/typeahead.js')}}"></script>
        <script src="{{ asset('js/helper.js') }}"></script>

        <script>
            let dataSrc = [];
            let table = $('#reporteNomina').DataTable({
                scrollY: '65vh',
                scrollCollapse: true,
                "language": {
                    search: '',
                    searchPlaceholder: 'Buscar registros por nombre',
                    "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                },
                initComplete: function() {

                    let api = this.api();

                    api.cells('tr', [2]).every(function() {

                        let data = $('<div>').html(this.data()).text();
                        if (dataSrc.indexOf(data) === -1) {
                            dataSrc.push(data);
                        }
                    });
                    dataSrc.sort();

                    $('.dataTables_filter input[type="search"]', api.table().container())
                        .typeahead({
                            source: dataSrc,
                            afterSelect: function(value) {
                                api.search(value).draw();
                            }
                        });

                    // MOVER EL ELEMENTO NATIVO DE BUSQUEDA Input Datatable AL DIV PERSONALIZADO
                    let elementos = $(".dataTables_filter > label > input").detach();
                    elementos.appendTo('#div_buscar');
                    $("#div_buscar > input").addClass("input-style-custom");
                },
            });

            function exportar(tipo,periodo,ejercicio){
                $('#id_tipo').val(tipo);
                $('#id_periodo').val(periodo);
                $('#ejercicio').val(ejercicio);
                $('#exportarPModal').modal('show');
            }

        </script>