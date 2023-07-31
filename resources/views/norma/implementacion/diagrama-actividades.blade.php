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
        
    @include('includes.header',['title'=>'Diagrama de actividades',
        'subtitle'=>'Norma 035', 'img'=>'img/header/norma/icono-diagrama.png',
        'route'=>'norma.normaTabla'])

        @if(!empty($datosImplementacion->fecha_inicio))
        <div id="nuevaImplementacion">
            <div id="button-container"></div>
            <div class="article-header border">
                <h4> {{$i}} </h4>
            </div>

        </div>
        <br />

        <div class="article border ">
            <div id="actividades">
                <center>
                    <h2>No hay actividades</h2>
                </center>
            </div>
        </div>
    </div>
        @endif
        @include('includes.footer')

        <link href="{{ asset('css/gantt/jquery-gantt.css') }}" rel="stylesheet">

        <script src="{{ asset('js/highcharts/highcharts.js') }}"></script>
        <script src="{{ asset('js/highcharts/gantt.js') }}"></script>
        <script src="{{ asset('js/highcharts/exporting.js') }}"></script>

        <script src="{{asset('js/moment/moment.js')}}"></script>
        <script src="{{asset('js/moment/es.js')}}"></script>

        <script>
            var actividadesImplementacion = [];
            @if(count($actividades))
            $("#button-container").html('<button id="pdf" class="button-style mb-3"> <img src="/img/icono-descargar.png" class="button-style-icon">Descargar PDF</button>');

            var cont = 0;
            var actividades = [];
            var colors = [];
            var fin_implementacion;
            @foreach($actividades as $actividad)
            var actividad = @json($actividad);
            var inicio = moment(actividad['fecha_inicio']);
            var fin = moment(actividad['fecha_fin']);
            fin_implementacion = fin;

            if (cont == 0) {
                var today = new Date(inicio),
                    day = 1000 * 60 * 60 * 24,
                    // Utility functions
                    dateFormat = Highcharts.dateFormat,
                    defined = Highcharts.defined,
                    isObject = Highcharts.isObject,
                    reduce = Highcharts.reduce;

                // Set to 00:00:00:000 today
                today.setUTCHours(0);
                today.setUTCMinutes(0);
                today.setUTCSeconds(0);
                today.setUTCMilliseconds(0);
                today = today.getTime();

                actividadesImplementacion.push({
                    "id": "" + cont + "",
                    "name": "Inicio de Implementación",
                    "start": Date.UTC(inicio.format('YYYY'), (inicio.format('MM') - 1), inicio.format('DD')),
                    "end": Date.UTC(fin.format('YYYY'), (fin.format('MM') - 1), fin.format('DD')),
                    "milestone": true
                });
                colors.push("#f0c018");
            }

            actividadesImplementacion.push({
                "id": "" + actividad['id'] + "",
                "name": "" + actividad['descripcion'] + "",
                "start": Date.UTC(inicio.format('YYYY'), (inicio.format('MM') - 1), inicio.format('DD')),
                "end": Date.UTC(fin.format('YYYY'), (fin.format('MM') - 1), fin.format('DD')),
                "dependency": ["" + cont + ""]
            });

            if (actividad['apertura_formulario']) {
                var inicio_expansion = moment(actividad['fecha_fin']).add(1, 'days');
                var fin_expansion = moment(actividad['fecha_fin']).add(3, 'days');

                colors.push("#f07618");
                actividadesImplementacion.push({
                    "id": "" + actividad['id'] + "",
                    "name": "" + actividad['descripcion'] + "",
                    "start": Date.UTC(inicio_expansion.format('YYYY'), (inicio_expansion.format('MM') - 1), inicio_expansion.format('DD')),
                    "end": Date.UTC(fin_expansion.format('YYYY'), (fin_expansion.format('MM') - 1), fin_expansion.format('DD')),
                });
            } else {
                colors.push("#f0c018");
            }
            cont = actividad['id'];

            @endforeach
            actividadesImplementacion.push({
                "id": "fin",
                "name": "fin de Implementación",
                "start": Date.UTC(fin_implementacion.format('YYYY'), (fin_implementacion.format('MM') - 1), fin_implementacion.format('DD')),
                "end": Date.UTC(fin_implementacion.format('YYYY'), (fin_implementacion.format('MM') - 1), fin_implementacion.format('DD')),
                "milestone": true,
                "dependency": ["" + cont + ""]
            });
            colors.push("#f0c018");
            console.log(actividadesImplementacion);

            Highcharts.setOptions({
                colors: colors,
                lang: {
                    months: [
                        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                    ],
                    weekdays: [
                        "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"
                    ],
                    shortMonths: [
                        "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dec"
                    ],
                    downloadCSV: ["Descargar CSV"],
                    downloadJPEG: ["Descargar imagen JPEG"],
                    downloadPDF: ["Descargar documento PDF"],
                    downloadPNG: ["Descargar imagen PNG"],
                    downloadSVG: ["Descargar vector de imagen SVG"],
                    downloadXLS: ["Descargar XLS"],
                    viewFullscreen: ["Ver en pantalla completa"],
                    printChart: ["Imprimir gráfico"],
                    contextButtonTitle: ["Menú contextual del gráfico"],

                }
            });

            // THE CHART
            Highcharts.ganttChart('actividades', {
                title: {
                    text: 'Actividades de la implementación'
                },
                credits: {
                    enabled: false
                },
                yAxis: {
                    uniqueNames: true,
                },

                xAxis: {
                    currentDateIndicator: true,
                    min: today - 1 * day,
                    max: today + 20 * day
                },
                navigator: {
                    enabled: true,
                    liveRedraw: true,
                    series: {
                        type: 'gantt',
                        pointPlacement: 0.5,
                        pointPadding: 0.25,
                    },
                    yAxis: {
                        min: 0,
                        max: 4,
                        reversed: true,
                        categories: []
                    }
                },
                scrollbar: {
                    enabled: true
                },
                series: [{
                    name: 'Actividades de la implementación',
                    data: actividadesImplementacion
                }]
            });

            document.getElementById('pdf').addEventListener('click', function() {
                Highcharts.charts[0].exportChart({
                    type: 'application/pdf'
                });
            });

            @endif
        </script>