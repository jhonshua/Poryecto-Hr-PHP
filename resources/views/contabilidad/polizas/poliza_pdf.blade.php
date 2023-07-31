<!DOCTYPE html>
<html lang="en">
<head>	
	<meta charset="utf-8" />
	<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>póliza</title>

</head>
<body style="font-size:12px;font-family: Arial, Helvetica, sans-serif;">
<div width="100%">
<div width="100%">
    <p style="text-align:center;font-size:16;fotn-wight:bold;">
    {{session()->get('empresa')['razon_social']}}
    </p>
</div>
<table style="width:100%;border-spacing: 0px;padding:15px;">
    <tr>
        <td style="background-color: #F7DF87;text-align:center;font-size:12;width:20%">
            PÓLIZA
        </td>
        <td colspan="2" style="background-color:#F0C018;font-weight: bold;width:60%;padding-left:20px;">
           No. {{ $data->id }}
        </td>
        <td colspan="2" style="background-color:#F0C018;font-weight: bold;text-align:right;width:20%;">
            Fecha: {{ date('d-m-Y') }}
        </td>    
    </tr>
    <tr>
        <td colspan="5" style="background-color:#F0C018;font-weight: bold;width:100%;padding:5px;">
            Nómina {{$data->nombre_periodo}} del {{ \Carbon\Carbon::parse($data->fecha_inicial_periodo)->format('d/m/Y')  }} AL {{ \Carbon\Carbon::parse($data->fecha_final_periodo)->format('d/m/Y') }}
        </td>
    </tr>
        <thead>
            <tr style="background-color:#000;color:#FFF; font-size:10">
                <th style="font-weight: normal;">Cuenta</th>
                <th style="font-weight: normal;">Concepto</th>
                <th style="font-weight: normal;">Nombre</th>
                <th style="font-weight: normal;">Debe</th>
                <th style="font-weight: normal;">Haber</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conceptos as $c )    
            <tr style="padding-left:35px; font-size:8">
                <td style="width:20%;text-align:center;border:1px solid black;">{{ $c[0] }}</td>
                <td style="width:35%;border:1px solid black;">{{ $c[1] }}</td>
                <td style="width:45%;border:1px solid black;">{{ $c[2] }}</td>
                <td style="width:5%;border:1px solid black;text-align:right;">{{ $c[3] }}</td>
                <td style="width:5%;border:1px solid black;text-align:right;">{{ $c[4] }}</td>
            </tr>
            @endforeach
            <tr><td colspan="5" style="height:50px"></td></tr>
            <tr>
                <td colspan="2"  style="background-color:#F7DF87;width:48%"></td>
                <td style="border:1px solid black;background-color:#000; color:#FFF;text-align:center;font-size:11;font-weight: bold;width: 24%;">SUMAS IGUALES</td>
                <td style="border:1px solid black;width: 14%;font-weight: bold;text-align:right">{{ $total_debe }}</td>
                <td style="border:1px solid black;font-weight: bold;text-align:right;"> {{ $total_haber }}</td>
            </tr>
            <tr>
                <td  style="vertical-align:top;font-weight: bold;height: 60px;border:1px solid black;padding-left:5px">Hecho por:</td>
                <td  style="vertical-align:top;font-weight: bold;height: 60px;border:1px solid black;padding-left:5px">Revisado por:</td>
                <td  style="vertical-align:top;font-weight: bold;height: 60px;border:1px solid black;padding-left:5px">Autorizado:</td>
                <td  style="vertical-align:top;font-weight: bold;height: 60px;border:1px solid black;padding-left:5px" colspan="2">Axuliiares:</td>
                
            </tr>
        </tbody>

</table>

   <table style="width:97%;border-spacing: 0px;">
   
    </table>
</div>
</body>
</html>