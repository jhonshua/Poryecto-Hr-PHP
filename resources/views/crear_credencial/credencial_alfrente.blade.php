<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style type="text/css">

        @font-face{
        font-family: "hind-bold"; 
        src: url("{{ asset('fonts/Hind/Hind-Bold.ttf') }}") format('truetype');
        font-style: normal;
        } 
        
        @font-face{
        font-family: "hind-semiBold"; 
        src: url("{{ asset('fonts/Hind/Hind-SemiBold.ttf') }}") format('truetype');
        font-style: normal;
        }

        @font-face{
        font-family: "hind-light"; 
        src: url("{{ asset('fonts/Hind/Hind-Light.ttf') }}") format('truetype');
        font-style: normal;
        } 
        
        @font-face{
        font-family: "hind-regular"; 
        src: url("{{ asset('fonts/Hind/Hind-Regular.ttf') }}") format('truetype');
        font-style: normal;
        } 
        
        body{
            padding: 0px;
            margin: 0px;
        }
        .base {
            padding: 0px;
            margin: 0px;
        }

        .layout{
           
            position: fixed;
        }

        .perfil{
            height: 233px;
            width: 233px;
            margin: 245px 0px 0px 70px ;
            border-radius: 200px;
            position: absolute;
        }

        .nombre{
            position: absolute;
            color: white;
            font-size: 25pt;
            margin: 156px 0px 0px 363px ;
            font-family: "hind-bold"  !important;
            overflow: hidden;
            width: 560px;
            font-weight: bold;
            line-height: 33px;
        }
        .puesto{
            position: absolute;
            color: #045D8D;
            font-size: 18pt;
            margin: 255px 0px 0px 485px ;
            font-family: "hind-light";
            overflow: hidden;
            width: 425px;
            font-weight: bold;
        }
        .curp{
            position: absolute;
            color: #045D8D;
            font-size: 18pt;
            margin: 385px 0px 0px 485px ;
            font-family: "hind-light";
            overflow: hidden;
            width: 425px;
            font-weight: bold;
        }
        .rfc{
            position: absolute;
            color: #045D8D;
            font-size: 18pt;
            margin: 320px 0px 0px 485px ;
            font-family: "hind-light";
            overflow: hidden;
            width: 425px;
            font-weight: bold;
        }
        .afilacion{
            position: absolute;
            color: white;
            font-size: 25pt;
            margin: 473px 0px 0px 750px ;
            font-family: "hind-semiBold"; 
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="base">
    <img class="layout" src="{{ asset('img/crear_imagen/credencial_alfrente.png') }}" alt="" >
    <img class="perfil" src="{{ $empleado['perfil'] }}" alt="">
<!--    No borrar comentario(ver nota en el controlador GeneradorCredenciales)-->
<!--    <img class="perfil" src="{{ asset($empleado['perfil']) }}" alt="">-->
    <div class="nombre">{{$empleado['nombre']}}</div>
    <div class="puesto">{{$empleado['puesto']}}</div>

    <div class="curp">{{$empleado['curp']}}</div>

    <div class="rfc">{{$empleado['rfc']}}</div>

    <div class="afilacion">{{$empleado['afilacion']}}</div>

</div>


</body>
</html>
