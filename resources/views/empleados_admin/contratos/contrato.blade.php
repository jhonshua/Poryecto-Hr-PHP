
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato</title>
</head>
<body>
    {!! $contenido !!}
</body>
</html>


<style>
@page {
    margin: 0cm 0cm;
    font-family: Arial;
}

body {
     margin-top: .9cm;
     margin-bottom: 1.4cm;
}

.margen .principal{
    margin-left: 1.3cm;
    margin-right: 1.3cm;
}

.inicial {
     margin-left: 1.3cm;
     margin-right: 1.3cm;
}

.margen-2 {
     margin-left: .5cm;
     margin-right: .5cm;
}

table.mega, table.mega th,table.mega  td {
  border: 1px solid black;
}
.contenido{
    margin:0 25px;
}
.text-justify {
    text-align: justify !important;
}
.text-center {
    text-align: center !important;
}
.arial-10{
    font-size:10pt;
    font-family: Arial, Helvetica, sans-serif;
}
.arial-9{
    font-size:9pt !important;
    font-family: Arial, Helvetica, sans-serif;
}
.arial-95{
    font-size:9.5pt !important;
    font-family: Arial, Helvetica, sans-serif;
}
.arial-8{
    font-size:8pt !important;
    font-family: Arial, Helvetica, sans-serif;
}

.arial-7{
    font-size:7pt !important;
    font-family: Arial, Helvetica, sans-serif;
}
.bold{
    font-weight: bold;
}
.espacio-20{
    line-height : 20px;
}
.padding-10{
    padding:10px 0;
}
table.firmas{
    width:100%;
}

table.firmas tr td.firma{
    height: 140px;
    border-bottom: 2px solid #000;
}
table.firmas tr td.firma-corta{
    height: 130px;
    border-bottom: 2px solid #000;
}
table.firmas tr td.beneficiario{
    height: 30px;
    border-bottom: 1px solid #000;
}
.cuadro{
    border:2px solid #000;
    height:30px;
    width:30px;
    margin-left:10px;
}
.huella{
    border:2px solid #000;
    height:100px;
    width:90px;
    margin-left:30%;
    margin-top:-5% !important;
}

.saltopagina{
    page-break-after:always;
}
.interlineado{
    line-height : 15px;
}
.derecha{
    text-align:right !important;
}
.izquierda{
    text-align:left !important;
}
.imgfondo{
    background-image: url("{{asset('public/repositorio/64/logos/rojo.PNG')}}");
    background-repeat: no-repeat;

}

.imgfondo2{
    background-image: url("{{asset('public/repositorio/64/logos/logo.png')}}");
    background-repeat: no-repeat;

}
</style>