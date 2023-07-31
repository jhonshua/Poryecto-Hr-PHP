<!DOCTYPE html>
<html>
@include('includes.head')
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HRSystem </title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
    <!-- Your custom styles (optional) -->
    <link rel="stylesheet" href="{{ asset('css/pantalla.css') }}">
    @stack('css')

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VCGK3W5GQC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-VCGK3W5GQC');
    </script>
</head>
<body>
@yield('content')
<!-- SCRIPTS -->
<!-- JQuery --

<script type="text/javascript" src="./public/assets/js/jquery.totemticker.js"></script>
<script type="text/javascript" src="./public/assets/js/custom.js?v=454545"></script>-->
<script src="{{ asset('js/moment/moment.js')}}"></script>
<script src="{{ asset('js/moment/es.js')}}"></script>
<script src="{{ asset('js/circle-progress/circle-progress.min.js') }}"></script>
<script>

    let scroll = 0;
    $(function() {

        reloj();
        fechaActual();
        asistencias();
        setTimeout("location.reload()", 1200000);
        setInterval(function(){  $('#panel2, #panel11').find('.card').toggleClass('flipped'); }, 80000);
        setInterval(function(){  renueva_asistencia(); }, 80000);

    });

    const  fechaActual = () => {

        let meses = new Array ("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        let f = new Date();
        let fecha = ", " + f.getDate() + " de " + meses[f.getMonth()] + " de " + f.getFullYear();
        $("#fecha-texto").html(fecha);
    }

    const reloj = () =>{

        momentoActual = new Date()
        hora = momentoActual.getHours()
        minuto =('0'+momentoActual.getMinutes()).slice(-2);
        segundo = ('0'+momentoActual.getSeconds()).slice(-2);

        horaImprimible = hora + ":" + minuto + ":" + segundo + " hrs"

        $("#reloj").html( horaImprimible );

        setTimeout("reloj()",1000)
    }

    const asistencias = async ()=>{

        let data = await obtieneAsistencias();
        const {total,faltas,retardos} = data;

        // Asistencias
        $('.uno.circle').circleProgress({
            value: total/100,
            thickness: 5,
            size: 70,
            fill: {gradient: ['#4ec226','#f7c90b']}
        }).on('circle-animation-progress', function(event, progress) {
            $(this).find('strong').html(total);
        });
        // Retardos
        $('.dos.circle').circleProgress({
            startAngle: -Math.PI / 4 *6  ,
            value: retardos/100,
            thickness: 5,
            size: 70,
            fill: {gradient: ['#f7c90b', '#ff5f43']}
        }).on('circle-animation-progress', function(event, progress,stepValue) {
            $(this).find('strong').text( Math.round(stepValue.toFixed(2).substr(1) * 100));
        });
        //Ausencias
        $('.tres.circle').circleProgress({
            startAngle: -Math.PI / 2 ,
            value: faltas/100,
            thickness: 5,
            size: 70,
            fill: {gradient: ['#F70b0b',  '#ff5f43']}
        }).on('circle-animation-progress', function(event, progress,stepValue) {
            $(this).find('strong').text( Math.round(stepValue.toFixed(2).substr(1) * 100));
        });
    }

    const renueva_asistencia= async() =>{

        let data = await obtieneAsistencias();
        const {total,faltas,retardos} = data;

        $('.uno').circleProgress({value:  total/100});
        $('.dos').circleProgress({value:  retardos/100});
        $('.tres').circleProgress({value: faltas/100});
        $('.uno').circleProgress('redraw');
        $('.dos').circleProgress('redraw');
        $('.tres').circleProgress('redraw');
    }

    const obtieneAsistencias = async ()=>{

        const url = '{{route("ajax.obtieneAsistencias",$empresa->id)}}';
        try{

            const res = await fetch(url);
            const data= await res.json();
            return data;

        }catch(err){ console.log(err) }
    }
</script>
@stack('scripts')
</body>
</html>

