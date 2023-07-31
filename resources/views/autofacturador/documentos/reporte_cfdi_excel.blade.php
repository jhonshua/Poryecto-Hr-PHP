<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<table style="background:#F0C018;">
    <tr><td></td></tr>
    <tr>
        <td>Quien lo descargo:</td>
        <td><strong>{{Auth::user()->nombre_completo}}</strong> </td>
    </tr>
    <tr>
        <td>Fecha del reporte:</td>
        <td><strong>{{ formatoAFecha(date('Y-m-d H:i:s'), true) }}</strong></td>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
</table>

<table class="border" id="" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <tr>
        <th colspan="19"></th>
        <th style="background:#F0C018; color: #ffffff;" >Conceptos</th>
    </tr>
    <tr class="">
        <th style="background:#343a40; color: #ffffff;"><b>Estado</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Fecha Emsión</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Fecha Timbrado</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Folio</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>RFC Emisor</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Nombre Emisor</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>RFC Receptor</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Nombre Receptor</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Uso CFDI</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Comisión %</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Monto comisión</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Comisión Base</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Sub Total</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>IVA %</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Total</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Forma De Pago</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Método De Pago</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Cliente</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Vendedor</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Cantidad</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Unidad</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Descripción</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Unitario</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Impuestos</b></th>
        <th style="background:#343a40; color: #ffffff;"><b>Importe</b></th>

    @php
        $subtotal=0;
        $total=0;
        $comisiones=0;
        $piso=0;
    @endphp
    @foreach ($cfdis as $cfdi)
        @php
            $subtotal=$subtotal+floatval($cfdi['subtotal']);
            $total=$total+floatval($cfdi['total']);
            $comisiones=$comisiones+floatval($cfdi['monto']);
        @endphp

        <tr class="GridViewScrollItem content" >
            <td>{{$cfdi['tipo']}}</td>
            <td>{{$cfdi['fechaEmision']}}</td>
            <td>{{$cfdi['fechatTimbrado']}}</td>
            <td>{{$cfdi['folio']}}</td>
            <td>{{$cfdi['rfc_emisor']}}</td>
            <td>{{$cfdi['nombre_emisor']}}</td>
            <td>{{$cfdi['rfc_receptor']}}</td>
            <td>{{$cfdi['nombre_receptor']}}</td>
            <td>{{$cfdi['uso_cfdi']}}</td>
            <td>{{$cfdi['comision']}}</td>
            <td>{{$cfdi['monto']}}</td>
            <td>{{$cfdi['comision_base']}}</td>
            <td>{{$cfdi['subtotal']}}</td>
            <td>{{$cfdi['iva']*100}}</td>
            <td>{{$cfdi['total']}}</td>
            <td>{{$cfdi['formaPago']}}</td>
            <td>{{$cfdi['metodoPago']}}</td>
            <td>{{$cfdi['cliente']}}</td>
            <td>{{$cfdi['vendedor']}}</td>

        @foreach($cfdi['conceptos'] as $key=>$concepto)
            @if($key==0)

                <td>{{$concepto['cantidad']}} </td>
                <td>{{$concepto['clave_unidad']}} </td>
                <td>{{$concepto['clave_prod']}} - {{$concepto['descripcion']}} </td>
                <td>{{$concepto['valor_unitario']}} </td>
                <td>{{$concepto['importe']}} </td>
                <td>{{$concepto['base']}} </td>
                </tr>
            @else
            <tr class="GridViewScrollItem content">
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>
                <td style="background:#a4a4a4;"></td>

                <td>{{$concepto['cantidad']}} </td>
                <td>{{$concepto['clave_unidad']}} </td>
                <td>{{$concepto['clave_prod']}} - {{$concepto['descripcion']}} </td>
                <td>{{$concepto['valor_unitario']}} </td>
                <td>{{$concepto['importe']}} </td>
                <td>{{$concepto['base']}} </td>
            </tr>
            @endif

        @endforeach



    @endforeach
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td></td>
        <td>
            <table class="border">
                <tr>
                    <td colspan="2" style="background:#F0C018; color: #ffffff; text-align:center; font-size:16px">Resumen Total</td>
                </tr>
                <tr>
                    <td><b>Subtotal</b></td>
                    <td>{{ $subtotal }}</td>
                </tr>
                <tr>
                    <td><b>Total</b></td>
                    <td>{{ $total }}</td>
                </tr>
                <tr>
                    <td><b>Monto comisión</b></td>
                    <td>{{ $comisiones }}</td>
                </tr>
                <tr>
                    <td><b>Piso 1%</b></td>
                    <td>{{ $comisiones*(1/100) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>