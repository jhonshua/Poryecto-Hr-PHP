<?php
header('Content-Type: text/html; charset=UTF-8');
include("fpdf/fpdf.php");
include("qrlib/qrlib.php");
include ("funciones.php");

class PDF extends FPDF
{
    function Header()
    {
        
    }

    function Footer()
    {
        $this->SetTextColor(0,0,0);
        $this->SetFont('arial','',12);
        $this->SetXY(19.4,26.2);
        $this->Cell(0.8, 0.25, $this->PageNo().'/{nb}', 0, 1,'L', 0);
    }
}

$SendaArchsCFDI = "archs_cfdi/";
$SendaArchsGraf = "archs_graf/";

$NomArchXML = $_GET["NomArchXML"];
$NomArchPDF = $_GET["NomArchPDF"];

$FechaHoraEmision = date("Y-m-d")."T".date("H:i:s"); // Esta fecha se asigna en este punto de manera PROVISIONAL para efectos de demostración 
$Obs = "";
$StatusCFDI = "ACTIVO"; // "ACTIVO" o "CANCELADO".
$PaginaWeb = "www.puntodeventaweb.com.mx";

$SumaImportes = 0;
$TotImpuestos = 0;


$xml = file_get_contents($SendaArchsCFDI.$NomArchXML);

#== 2. Obteniendo datos del archivo .XML =========================================

    $DOM = new DOMDocument('1.0', 'utf-8');
    $DOM->preserveWhiteSpace = FALSE;
    $DOM->loadXML($xml);

    $params = $DOM->getElementsByTagName('TimbreFiscalDigital');
    foreach ($params as $param) {
           $UUID     = $param->getAttribute('UUID');
           $noCertificadoSAT = $param->getAttribute('NoCertificadoSAT');
           $selloCFD = $param->getAttribute('SelloCFD');
           $selloSAT = $param->getAttribute('SelloSAT');
    }      

    $params = $DOM->getElementsByTagName('Emisor');
    foreach ($params as $param) {
           $Emisor_Nom = $param->getAttribute('Nombre');
           $Emisor_RFC = $param->getAttribute('Rfc');
           $Emisor_Regimen = $param->getAttribute('RegimenFiscal');
    }    
    
    $params = $DOM->getElementsByTagName('Receptor');
    foreach ($params as $param) {
           $Receptor_Nom = $param->getAttribute('Nombre');
           $Receptor_RFC = $param->getAttribute('Rfc');
           $Receptor_UsoCFDI = $param->getAttribute('UsoCFDI');
    }    
    
    
    $params = $DOM->getElementsByTagName('Comprobante');
    foreach ($params as $param) {
           $Fact_Fecha    = $param->getAttribute('Fecha');
           $Fact_Serie    = $param->getAttribute('Serie');
           $Fact_Folio    = $param->getAttribute('Folio');
           $Fact_NoFact   = $Fact_Serie.$Fact_Folio;
           $descuento     = $param->getAttribute('Descuento');
           $subTotal      = $param->getAttribute('SubTotal');
           $total         = $param->getAttribute('Total');
           $version       = $param->getAttribute('Version');
           $noCertificado = $param->getAttribute('NoCertificado');
           $formaDePago   = $param->getAttribute('FormaPago');
           $metodoDePago  = $param->getAttribute('MetodoPago');
           $NumCtaPago    = "";
           $LugarExpedicion = $param->getAttribute('LugarExpedicion');
    }

    if (strlen($Fact_NoFact)==0){
        $Fact_NoFact = "S/N";
    }
    
    $i=0; $ImpTot = 0;
    $params = $DOM->getElementsByTagName('Concepto');
    foreach ($params as $param) {
           $ArrayClaveProdServ[$i] = $param->getAttribute('ClaveProdServ');
           $ArrayClaveUnidad[$i]   = $param->getAttribute('ClaveUnidad');
           $ArrayUnidad[$i]        = $param->getAttribute('Unidad');
           $ArrayCant[$i]          = $param->getAttribute('Cantidad');
           $ArrayUniMed[$i]        = $param->getAttribute('Unidad');
           $ArrayArtSer[$i]        = $param->getAttribute('Descripcion');
           $ArrayPreUni[$i]        = $param->getAttribute('ValorUnitario');
           $ArrayImporte[$i]       = $param->getAttribute('Importe');
           $SumaImportes = $SumaImportes + $ArrayImporte[$i];
           $i++;
    }       
    
    $ImporteTotalIVA = 0;
    $ImporteTotalIEPS = 0;
    $ultimoImporteIVA = 0;
    $ultimoImporteIEPS = 0;

    $params = $DOM->getElementsByTagName('Traslado');
    foreach ($params as $param) {
        $TotImpuestos =  $TotImpuestos + $param->getAttribute('Importe');

        if ($param->getAttribute('Impuesto')=="002"){ // IVA
            $ImporteTotalIVA  = $ImporteTotalIVA + $param->getAttribute('Importe');
            $ultimoImporteIVA = $param->getAttribute('Importe');
        }

        if ($param->getAttribute('Impuesto')=="003"){ // IEPS
            $ImporteTotalIEPS  = $ImporteTotalIEPS + $param->getAttribute('Importe');
            $ultimoImporteIEPS = $param->getAttribute('Importe');
        }
    }

    $ImporteTotalIVA  = $ImporteTotalIVA - $ultimoImporteIVA;
    $ImporteTotalIEPS = $ImporteTotalIEPS - $ultimoImporteIEPS;

    

    // Datos de un campo de la Addenda: "SistemaLocal" =========================
    $params = $DOM->getElementsByTagName('SistemaLocal');
    foreach ($params as $param) {
           $Obs = $param->getAttribute('Observaciones');
    }       
    
    
    
    $CadOri = "||".$UUID."|".$Fact_Fecha."|".$selloCFD."|".$noCertificado."||";
    
#== 3. Crear archivo .PNG con codigo bidimensional =================================
$filename = $SendaArchsGraf."/Img_".$UUID.".png";
$CadImpTot = ProcesImpTot($total);
$Cadena = "?re=".$Emisor_RFC."&rr=".$Receptor_RFC."&tt=".$CadImpTot."&id=".$UUID;
QRcode::png($Cadena, $filename, 'H', 3, 2);    
chmod($filename, 0777);  


#== 4. Construyendo el documentos con la librería FPDF =======================================

$pdf=new FPDF('P','cm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('IDAutomationHC39M','','IDAutomationHC39M.php');
$pdf->AddFont('verdana','','verdana.php');
$pdf->SetAutoPageBreak(true);
$pdf->SetMargins(0, 0, 0);
$pdf->SetLineWidth(0.02);
$pdf->SetFillColor(0,0,0);

####### ENCABEZADO DE LA FACTURA #############################################################
    
    $X = 0;
    $Y = 0;

    $pdf->image("archs_graf/Membrete_Fact.jpg",$X+1, $Y+1 , 9, 2.3);
    $pdf->image("archs_graf/LogoSAT.jpg",$X+16.6, $Y+3.4 , 0, 0);
    $pdf->image("archs_graf/FondoTenue.jpg",$X+1, $Y+8.7 , 19.5, 18);

    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',13);
    $pdf->SetXY($X+10.5,$Y+1.25+0.1);
    $pdf->Cell(1.5, 0.25, utf8_decode("NOTA DE CRÉDITO."), 0, 1,'L', 0);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',10);
        $pdf->SetXY($X+15.5,$Y+1.46+0.3);
        $pdf->Cell(2.5, 0.25, "FOLIO Y SERIE:", 0, 1,'R', 0);

        $pdf->SetTextColor(171,17,17);
        $pdf->SetFont('arial','',14);
        $pdf->SetXY($X+18,$Y+1.45+0.3);
        $pdf->Cell(1, 0.25, $Fact_NoFact, 0, 1,'L', 0);

        
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+2.05);
    $pdf->Cell(2, 0.25, "FOLIO FISCAL:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',11);
    $pdf->SetXY($X+10.5+0.5,$Y+2.5);
    $pdf->Cell(2, 0.25, $UUID, 0, 1,'L', 0);
    
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+2.5+0.6);
        $pdf->Cell(2, 0.25, "CERTIFICADO SAT:", 0, 1,'L', 0);

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+10.5+0.5,$Y+2.5+0.6+0.4);
        $pdf->Cell(2, 0.25, $noCertificadoSAT, 0, 1,'L', 0);

    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+4.06);
    $pdf->Cell(2, 0.25, "CERTIFICADO DEL EMISOR:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',10);
    $pdf->SetXY($X+10.5+0.5,$Y+4.06+0.4);
    $pdf->Cell(2, 0.25, $noCertificado, 0, 1,'L', 0);

    
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+4.46+0.5);
        $pdf->Cell(2, 0.25, utf8_decode("FECHA HORA DE EMISIÓN:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+10.5+0.5,$Y+4.46+0.5+0.34);
        $pdf->MultiCell(9.4, 0.35, utf8_decode($FechaHoraEmision), 0, 'L');


        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+6);
        $pdf->Cell(2, 0.25, utf8_decode("FECHA HORA DE CERTIFICACIÓN:"), 0, 1,'L', 0);
        
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+10.5+5.5,$Y+6);
        $pdf->Cell(2, 0.25, $Fact_Fecha, 0, 1,'L', 0);
        
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+6.6);
        $pdf->Cell(2, 0.25, utf8_decode("RÉGIMEN FISCAL:"), 0, 1,'L', 0);

        $pdf->SetFont('arial','',9);
        $pdf->SetTextColor(17,71,121);
        $pdf->SetXY($X+13.5,$Y+6.6);
        $pdf->Cell(1, 0.25, utf8_decode($Emisor_Regimen), 0, 1,'L', 0);

    $pdf->RoundedRect($X+10.4, $Y+1, 10, 6.14, 0.2, '');

    //======================================================================
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',11);
    $pdf->SetXY($X+1.05,$Y+3.7);
    $pdf->Cell(1, 0.25, "EMISOR:", 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+1.4,$Y+3.7+0.4);
        $pdf->MultiCell(8.6, 0.35, utf8_decode($Emisor_Nom), 0, 'L');
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+3.7+0.45+1.25);
    $pdf->Cell(1, 0.25, "RFC:", 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',11);
        $pdf->SetXY($X+2.05,$Y+3.7+0.45+1.25);
        $pdf->Cell(1, 0.25, utf8_decode($Emisor_RFC), 0, 1,'L', 0);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+1.05,$Y+3.7+0.45+1.8);
    $pdf->Cell(1, 0.25, utf8_decode("LUGAR DE EXPEDICIÓN (CÓDIGO POSTAL):"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+1.05+7,$Y+3.7+0.45+1.8);
        $pdf->Cell(1, 0.25, $LugarExpedicion, 0, 1,'L', 0);
        
    $pdf->RoundedRect($X+1, $Y+3.5, 9, 3.64, 0.2, '');
    
    $Y = $Y -1;
    
    //======================================================================
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',11);
    $pdf->SetXY($X+1.05,$Y+8.6);
    $pdf->Cell(1, 0.25, "RECEPTOR:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',9);
    $pdf->SetXY($X+1.5,$Y+8.4+0.65);
    $pdf->Cell(1, 0.25, utf8_decode($Receptor_Nom), 0, 1,'L', 0);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+9.5+0.1);
    $pdf->Cell(1, 0.25, "RFC:", 0, 1,'L', 0);
    
    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',11);
    $pdf->SetXY($X+1.05+0.9,$Y+9.5+0.1);
    $pdf->Cell(1, 0.25, $Receptor_RFC, 0, 1,'L', 0);
    
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',10);
        $pdf->SetXY($X+8,$Y+9.5+0.1);
        $pdf->Cell(2, 0.25, utf8_decode("USO CFDI:"), 0, 1,'L', 0);    

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',11);
        $pdf->SetXY($X+8+2,$Y+9.5+0.1);
        $pdf->Cell(1, 0.25, $Receptor_UsoCFDI, 0, 1,'L', 0);
        
        
$pdf->SetTextColor(199,199,199);
$pdf->SetFont('arial','BI',29);
$pdf->SetXY($X+15.4,$Y+9.16);
$pdf->Cell(2, 0.25, utf8_decode("CFDI 3.3"), 0, 1,'L', 0);    
    
    $pdf->RoundedRect($X+1, $Y+8.4, 19.4, 1.7, 0.2, '');
    
    VerifStatusCFDI($pdf, $StatusCFDI);


    
    
#== Registros de artículos ================================================

    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',11);
    $pdf->SetXY($X+0.8,$Y+10.5);
    $pdf->Cell(1, 0.25, "CONCEPTOS:", 0, 1,'L', 0);    
    
    $Y = $pdf->GetY()+0.9;
    
    $Regs = 0;
    $RefAgregPag = 0;

    Titulos($pdf, $Y-0.8);    
    
    $TotRegs = count($ArrayCant);
    
    for ($i=0; $i<$TotRegs; $i++){
        
            $pdf->SetFont('arial','',8);
            
            $pdf->SetTextColor(171,18,18);
            $pdf->SetXY($X+1,$Y);
            $pdf->Cell(1.8, 0.35, $ArrayClaveProdServ[$i], 0, 0,'C', 0);

            $pdf->SetXY($X+2.85,$Y);
            $pdf->Cell(1.3, 0.35, $ArrayClaveUnidad[$i], 0, 0,'C', 0);
            
            $pdf->SetTextColor(17,71,121);
            $pdf->SetXY($X+4.2,$Y);
            $pdf->Cell(1.5, 0.35, $ArrayCant[$i], 0, 0,'C', 0);
            
            $pdf->SetXY($X+5.75,$Y);
            $pdf->Cell(2.1, 0.35, $ArrayUnidad[$i], 0, 0,'L', 0);
            
            $pdf->SetXY($X+7.9,$Y);
            $pdf->MultiCell(8.5, 0.35, $ArrayArtSer[$i], 0, 'L', 0);
            $pdf->Write(0.4,'');
            $YY = $pdf->GetY()+0.18;
            $Puntero = $pdf->GetY();
            
            $pdf->SetXY($X+16.6,$Y);
            $pdf->Cell(1.7, 0.35, number_format($ArrayPreUni[$i],2), 0, 0,'R', 0);

            $pdf->SetXY($X+18.5,$Y);
            $pdf->Cell(1.8, 0.35, number_format($ArrayImporte[$i],2), 0, 0,'R', 0);

            $pdf->line($X+1, $Y-0.1, $X+20.4, $Y-0.1);

            $Y = $YY;
            $Regs++;

            if ($Puntero>23.5){

                if ($TotRegs>$Regs){

                    $pdf->AddPage();
                    $pdf->image("archs_graf/FondoTenue.jpg",1, 5 , 19.5, 18);
                    Titulos($pdf, 1.5);
                    VerifStatusCFDI($pdf, $StatusCFDI);
                    $Y = 2.4;
                }else{
                    $pdf->AddPage();
                    $pdf->image("archs_graf/FondoTenue.jpg",1, 5 , 19.5, 18);
                    VerifStatusCFDI($pdf, $StatusCFDI);
                    $Y = 1.4;
                }
            }  
    }    
    
    
    // Impresión de los subtotales.
    if ($Puntero<=23.5){
        SubTotales($pdf, $Y, $subTotal, $descuento, $ImporteTotalIVA, $ImporteTotalIEPS, $total, $formaDePago, $metodoDePago, $NumCtaPago, $Puntero);
    }

    if ($Puntero>23.5 && $TotRegs==$Regs){
        SubTotales($pdf, $Y, $subTotal, $descuento, $ImporteTotalIVA, $ImporteTotalIEPS, $total, $formaDePago, $metodoDePago, $NumCtaPago, $Puntero);
    }

    
    // Impresión de los datos inferiores.
    if ($Puntero<18.5){
        
        DatosInf($pdf, $filename, 0, $selloCFD, $selloSAT, $CadOri, $PaginaWeb);
    }

    if ($Puntero>=18.5 && $Puntero<23.5){
        
        $pdf->AddPage();
        $pdf->image("archs_graf/FondoTenue.jpg",1, 5 , 19.5, 18);        
        DatosInf($pdf, $filename, -20.5, $selloCFD, $selloSAT, $CadOri, $PaginaWeb);
    }    

    if ($Puntero>=23.5){
        DatosInf($pdf, $filename, $Puntero -41, $selloCFD, $selloSAT, $CadOri, $PaginaWeb);
    }    
    
    // Impresión de observaciones ==============================================

    
    $Y = $pdf->GetY()-11.5;
    
    if (strlen($Obs)>0){
        
        $pdf->SetTextColor(7,100,30);
        $pdf->SetFont('arial','B',11);
        $pdf->SetXY($X+0.8,$Y);
        $pdf->Cell(1, 0.35, "OBSERVACIONES:", 0, 1,'L', 0);            
        
        $pdf->SetXY($X+1.3,$Y+0.5);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','',9);
        $pdf->MultiCell(19, 0.35, $Obs, 0, 'J');
    }    
    
    
    
    

$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'F'); // Se graba el documento .PDF en el disco duro o unidad de estado sólido.

chmod ($SendaArchsCFDI.$NomArchPDF,0777);  // <-- Descomentar si está utilizando el sistema operativo LINUX.
    
$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'I'); // Se muestra el documento .PDF en el navegador.



function Titulos($pdf, $Y){
    
    $Y = $Y + 0.24;
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);    
    
    $pdf->SetXY(1.1,$Y);
    $pdf->Cell(1.5, 0.30, "ClaProdServ", 0, 1,'C', 0);

    $pdf->SetXY(1.1+1.65,$Y);
    $pdf->Cell(1.5, 0.30, "ClaUni", 0, 1,'C', 0);

    $pdf->SetXY(1.1+3.3,$Y);
    $pdf->Cell(1, 0.30, "Cant", 0, 1,'C', 0);

    $pdf->SetXY(1.1+4.7,$Y);
    $pdf->Cell(1, 0.30, "Unidad", 0, 1,'L', 0);

    $pdf->SetXY(1.1+4.7+2.1,$Y);
    $pdf->MultiCell(4.2, 0.35, utf8_decode("Descripción"), 0, 'L', 0);

    $pdf->SetXY(16.65,$Y);
    $pdf->Cell(1.5, 0.35, utf8_decode("P/U"), 0, 1,'R', 0);

    $pdf->SetXY(16.65+1.8,$Y);
    $pdf->Cell(1.8, 0.35, utf8_decode("Importe"), 0, 1,'R', 0);    
}


function SubTotales($pdf, $Y, $subTotal, $descuento, $ImporteTotalIVA, $ImporteTotalIEPS, $total, $formaDePago, $metodoDePago, $NumCtaPago, $Puntero){
    
    $X = 0;
    $Y = $Y - 0.15;
    
    //== Subtotales ============================================================
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+16.27,$Y+0.35);
    $pdf->Cell(1.7, 0.30, "Subtotal:", 0, 1,'R', 0);    

    $pdf->SetFont('arial','',9); 
    $pdf->SetXY($X+16.16+2.5,$Y+0.35);
    $pdf->Cell(1.7, 0.30, number_format($subTotal,2), 0, 1,'R', 0);    

    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+16.27,$Y+0.35+0.5);
    $pdf->Cell(1.7, 0.30, "Descuento:", 0, 0,'R', 0);    

    $pdf->SetFont('arial','',9); 
    $pdf->SetXY($X+16.16+2.5,$Y+0.35+0.5);
    $pdf->Cell(1.7, 0.30, number_format($descuento,2), 0, 1,'R', 0);

    $pdf->SetFont('arial','B',9);         
    $pdf->SetXY($X+16.27,$Y+0.35+(0.5*2));
    $pdf->Cell(1.7, 0.30, "IEPS:", 0, 0,'R', 0);

    $pdf->SetFont('arial','',9);
    $pdf->SetXY($X+16.16+2.5,$Y+0.35+(0.5*2));
    $pdf->Cell(1.7, 0.30, number_format($ImporteTotalIEPS,2), 0, 1,'R', 0);

    $pdf->SetFont('arial','B',9);         
    $pdf->SetXY($X+16.27,$Y+0.35+(0.5*3));
    $pdf->Cell(1.7, 0.30, "IVA:", 0, 0,'R', 0);

    $pdf->SetFont('arial','',9);
    $pdf->SetXY($X+16.16+2.5,$Y+0.35+(0.5*3));
    $pdf->Cell(1.7, 0.30, number_format($ImporteTotalIVA,2), 0, 1,'R', 0);

    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+16.27,$Y+0.37+(0.5*4));
    $pdf->Cell(1.7, 0.30, "Total:", 0, 1,'R', 0);    

    $pdf->SetFont('arial','',9); 
    $pdf->SetXY($X+16.16+2.5,$Y+0.37+(0.5*4));
    $pdf->Cell(1.7, 0.30, number_format($total,2), 0, 1,'R', 0);    

    //================================================================
    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+1,$Y+0.35);
    $pdf->Cell(1.7, 0.30, "Total con letra: ".$Puntero, 0, 1,'L', 0);    

    $pdf->SetFont('arial','',8); 
    $pdf->SetXY($X+1,$Y+0.35+0.40);
    $pdf->Cell(1.7, 0.30, NumLet($total), 0, 1,'L', 0);
    
    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+1,$Y+0.35+0.40+0.8);
    $pdf->Cell(1.7, 0.30, "Forma de pago:", 0, 1,'L', 0);

    $pdf->SetFont('arial','',9); 
    $pdf->SetXY($X+3.6,$Y+0.35+0.40+0.8);
    $pdf->Cell(1.7, 0.30, utf8_decode($formaDePago), 0, 1,'L', 0);   
    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+1,$Y+0.35+0.40+1.3);
    $pdf->Cell(1.7, 0.30, utf8_decode("Método de pago:"), 0, 1,'L', 0);

    $pdf->SetFont('arial','',9); 
    $pdf->SetXY($X+3.76,$Y+0.35+0.40+1.3);
    $pdf->Cell(1.7, 0.30, utf8_decode($metodoDePago), 0, 1,'L', 0);   
    
        
    if (strlen($NumCtaPago)>0){
        
        $pdf->SetFont('arial','B',9); 
        $pdf->SetXY($X+1,$Y+0.35+0.40+1.8);
        $pdf->Cell(1.7, 0.30, utf8_decode("Número de cuenta:"), 0, 1,'L', 0);

        $pdf->SetFont('arial','',9); 
        $pdf->SetXY($X+3.76+0.3,$Y+0.35+0.40+1.8);
        $pdf->Cell(1.7, 0.30, $NumCtaPago, 0, 1,'L', 0);   
    }
}


function DatosInf($pdf, $filename, $Y, $selloCFD, $selloSAT, $CadOri, $PaginaWeb){
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY(1.2,22.9+$Y-0.25 -1.5);
    $pdf->Cell(1.7,0.30, "Sello digital del CFDI:", 0, 1,'L', 0);    

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(1.2,+22.9+0.35+$Y-0.2 -1.5);
        $pdf->MultiCell(19.4, 0.25, $selloCFD, 0, 'L', 0);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY(4.2,21.9+2+$Y-1.2-0.2);
    $pdf->Cell(1.7, 0.30, "Sello del SAT:", 0, 1,'L', 0);    

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(4.2,21.9+0.35+2+$Y-1.2-0.2);
        $pdf->MultiCell(16.1, 0.25, $selloSAT, 0, 'L', 0);
        
    $pdf->SetTextColor(0,0,0);    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY(4.2,25+$Y-0.7-0.25);
    $pdf->Cell(1.7, 0.30, utf8_decode("Cadena original del complemento de certificación digital del SAT:"), 0, 1,'L', 0);    
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(4.2,25.1+0.25+$Y-0.7-0.2);
        $pdf->MultiCell(16.1, 0.25, $CadOri, 0, 'L', 0);
        
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10); 
    $pdf->SetXY(4.2,26.36+$Y -0.65);
    $pdf->Cell(15.6, 0.30, utf8_decode("===== Este documento es una representación impresa de un CFDI ====="), 0, 1,'C', 0);    
        
    $pdf->Image($filename,1.2,23.8+$Y-1.1-0.25,3,3,'PNG');

    $pdf->SetFont('arial','I',13); 
    $pdf->SetTextColor(132,132,132);
    $pdf->SetXY(1.3,26.9+$Y-0.55);
    $pdf->Cell(19, 0.30, utf8_decode($PaginaWeb), 0, 1,'C', 0);       
}


function ProcesImpTot($ImpTot){
    $ImpTot = number_format($ImpTot, 4); // <== Se agregó el 30 de abril de 2017.
    $ArrayImpTot = explode(".", $ImpTot);
    $NumEnt = $ArrayImpTot[0];
    $NumDec = ProcesDecFac($ArrayImpTot[1]);
    return $NumEnt.".".$NumDec;
}


function ProcesDecFac($Num){
    $FolDec = "";
    if ($Num < 10){$FolDec = "00000".$Num;}
    if ($Num > 9 and $Num < 100){$FolDec = $Num."0000";}
    if ($Num > 99 and $Num < 1000){$FolDec = $Num."000";}
    if ($Num > 999 and $Num < 10000){$FolDec = $Num."00";}
    if ($Num > 9999 and $Num < 100000){$FolDec = $Num."0";}
    return $FolDec;
}    


        
function VerifStatusCFDI($pdf, $StatusCFDI){

    if ($StatusCFDI=="CANCELADO"){

        $pdf->SetLineWidth(0.1);
        $pdf->SetDrawColor(200,0,0);        
        $pdf->SetTextColor(200,0,0);
        $pdf->SetFont('verdana','',53); 
        
        $pdf->RoundedRect(4.4, 7.4-2.5, 12.6, 2.05, 0.4, '');
        $pdf->SetXY(1,8.4-2.5);
        $pdf->Cell(19.4, 0.30, "CANCELADO", 0, 1,'C', 0);    

        $pdf->SetLineWidth(0.02);
        $pdf->SetDrawColor(0,0,0);        
        $pdf->SetTextColor(0,0,0);
    }
}       


