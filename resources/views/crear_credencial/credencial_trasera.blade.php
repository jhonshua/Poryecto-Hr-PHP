<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        
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
            height: 160px;
            width: 160px;
            margin: 282px 0px 0px 102px ;
            position: absolute;
        }
        
    </style>
</head>
<body>

<div class="base">
    <img class="layout" src="{{ asset('img/crear_imagen/credencial_trasera.png') }}" alt="" >
    <img class="perfil"  src="data:image/svg+xml;base64,{{ base64_encode(trim(QrCode::size(150)->generate($empleado['qr']), '<?xml version="1.0" encoding="UTF-8" ?>')) }}">

</div>

</body>
</html>
