<?php
header('Content-Type: text/html; charset=UTF-8');
require("fpdf/fpdf.php");
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
$StatusCFDI = "ACTIVO"; // "ACTIVO" o "CANCELADO".
$PaginaWeb = "www.puntodeventaweb.com.mx";

$ArrayDocRel_IdDocumento = []; 
$ArrayDocRel_Serie = []; 
$ArrayDocRel_Folio = []; 
$ArrayDocRel_NoDoc = []; 
$ArrayDocRel_MonedaDR = []; 
$ArrayDocRel_MetodoDePagoDR = []; 
$ArrayDocRel_NumParcialidad = []; 
$ArrayDocRel_ImpSaldoAntd = []; 
$ArrayDocRel_ImpPagado = []; 
$ArrayDocRel_ImpSaldoInsoluto = []; 


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
    
    $params = $DOM->getElementsByTagName('Pago');
    foreach ($params as $param) {
           $Pago_FechaPago = $param->getAttribute('FechaPago');
           $Pago_FormaDePagoP = $param->getAttribute('FormaDePagoP');
           $Pago_MonedaP = $param->getAttribute('MonedaP');
           $Pago_Monto = $param->getAttribute('Monto');
           $Pago_NumOperacion = $param->getAttribute('NumOperacion');
    }
    
    $i=0;
    $params = $DOM->getElementsByTagName('DoctoRelacionado');
    foreach ($params as $param) {
        $ArrayDocRel_IdDocumento[$i] = $param->getAttribute('IdDocumento');
        $ArrayDocRel_Serie[$i] = $param->getAttribute('Serie');
        $ArrayDocRel_Folio[$i] = $param->getAttribute('Folio');
        $ArrayDocRel_NoDoc[$i] = $param->getAttribute('Serie').$param->getAttribute('Folio');
        $ArrayDocRel_MonedaDR[$i] = $param->getAttribute('MonedaDR'); 
        $ArrayDocRel_MetodoDePagoDR[$i] = $param->getAttribute('MetodoDePagoDR');
        $ArrayDocRel_NumParcialidad[$i] = $param->getAttribute('NumParcialidad');
        $ArrayDocRel_ImpSaldoAntd[$i] = $param->getAttribute('ImpSaldoAnt');
        $ArrayDocRel_ImpPagado[$i] = $param->getAttribute('ImpPagado');
        $ArrayDocRel_ImpSaldoInsoluto[$i] = $param->getAttribute('ImpSaldoInsoluto');
     
        $i++;
    }    
    
    $CadOri = "||".$UUID."|".$Fact_Fecha."|".$selloCFD."|".$noCertificado."||";
    
#== 3. Crear archivo .PNG con codigo bidimensional =============================
$filename = $SendaArchsGraf."/Img_".$UUID.".png";
$CadImpTot = ProcesImpTot($total);
$Cadena = "?re=".$Emisor_RFC."&rr=".$Receptor_RFC."&tt=".$CadImpTot."&id=".$UUID;
QRcode::png($Cadena, $filename, 'H', 3, 2);    
chmod($filename, 0777);  


#== 4. Construyendo el documentos con la librería FPDF =========================

$pdf=new PDF('P','cm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('IDAutomationHC39M','','IDAutomationHC39M.php');
$pdf->AddFont('verdana','','verdana.php');
$pdf->SetAutoPageBreak(true);
$pdf->SetMargins(0, 0, 0);
$pdf->SetLineWidth(0.02);
$pdf->SetFillColor(0,0,0);

    
####### ENCABEZADO DE LA FACTURA ###############################################

    $X = 0;
    $Y = 0;

    $pdf->image("archs_graf/Membrete_Fact.jpg",$X+1, $Y+1 , 9, 2.3);
    $pdf->image("archs_graf/LogoSAT.jpg",$X+16.6, $Y+3.6 , 0, 0);
    $pdf->image("archs_graf/FondoTenue.jpg",$X+1, $Y+8.7 , 19.5, 18);

    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',13);
    $pdf->SetXY($X+10.5,$Y+1.25+0.1);
    $pdf->Cell(1.5, 0.25, utf8_decode("RECEPCIÓN DE PAGOS."), 0, 1,'L', 0);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',10);
        $pdf->SetXY($X+15.5,$Y+1.46+0.6);
        $pdf->Cell(2.5, 0.25, "FOLIO Y SERIE:", 0, 1,'R', 0);

        $pdf->SetTextColor(171,17,17);
        $pdf->SetFont('arial','',14);
        $pdf->SetXY($X+18,$Y+1.45+0.6);
        $pdf->Cell(1, 0.25, $Fact_NoFact, 0, 1,'L', 0);

        
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+2.05+0.33);
    $pdf->Cell(2, 0.25, "FOLIO FISCAL:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',11);
    $pdf->SetXY($X+10.5+0.5,$Y+2.5+0.33);
    $pdf->Cell(2, 0.25, $UUID, 0, 1,'L', 0);
    
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+2.5+0.6+0.26);
        $pdf->Cell(2, 0.25, "CERTIFICADO SAT:", 0, 1,'L', 0);

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+10.5+0.5,$Y+2.5+0.6+0.4+0.26);
        $pdf->Cell(2, 0.25, $noCertificadoSAT, 0, 1,'L', 0);

    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+4.06+0.24);
    $pdf->Cell(2, 0.25, "CERTIFICADO DEL EMISOR:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',10);
    $pdf->SetXY($X+10.5+0.5,$Y+4.06+0.4+0.24);
    $pdf->Cell(2, 0.25, $noCertificado, 0, 1,'L', 0);

    
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+4.46+0.5+0.25);
        $pdf->Cell(2, 0.25, utf8_decode("FECHA HORA DE EMISIÓN:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+10.5+0.5,$Y+4.46+0.5+0.34+0.25);
        $pdf->MultiCell(9.4, 0.35, utf8_decode($FechaHoraEmision), 0, 'L');

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+6+0.14);
        $pdf->Cell(2, 0.25, utf8_decode("FECHA HORA DE CERTIFICACIÓN:"), 0, 1,'L', 0);
        
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+10.5+5.5,$Y+6+0.14);
        $pdf->Cell(2, 0.25, $Fact_Fecha, 0, 1,'L', 0);
        
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+6.6+0.05);
        $pdf->Cell(2, 0.25, utf8_decode("RÉGIMEN FISCAL:"), 0, 1,'L', 0);

        $pdf->SetFont('arial','',9);
        $pdf->SetTextColor(17,71,121);
        $pdf->SetXY($X+13.5,$Y+6.6+0.05);
        $pdf->Cell(1, 0.25, utf8_decode($Emisor_Regimen), 0, 1,'L', 0);

    $pdf->RoundedRect($X+10.4, $Y+1, 10, 6.14, 0.2, '');

    //==========================================================================
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',11);
    $pdf->SetXY($X+1.05,$Y+3.7);
    $pdf->Cell(1, 0.25, "EMISOR.", 0, 1,'L', 0);
    
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
    
    //==========================================================================
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',11);
    $pdf->SetXY($X+1.05,$Y+8.6);
    $pdf->Cell(1, 0.25, "RECEPTOR.", 0, 1,'L', 0);

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

// PAGO ========================================================================

$pdf->RoundedRect($X+1, $Y+8.4+2, 19.4, 1.8, 0.2, '');
    
$pdf->SetTextColor(7,100,30);
$pdf->SetFont('arial','B',11);
$pdf->SetXY($X+1+0.1,$Y+10.65);
$pdf->Cell(1.3, 0.25, "PAGO.", 0, 1,'L', 0);

$pdf->SetFont('arial','B',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.05+0.1,$Y+11.3-0.1);
$pdf->Cell(1.3, 0.25, "FECHA DE PAGO:", 0, 1,'L', 0);
    
$pdf->SetFont('arial','',10);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+3.3+0.7,$Y+11.3-0.1);
$pdf->Cell(1, 0.25, $Pago_FechaPago, 0, 1,'L', 0);
    
$pdf->SetFont('arial','B',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.05+0.1,$Y+11.3+0.5-0.1);
$pdf->Cell(1, 0.25, "FORMA DE PAGO:", 0, 1,'L', 0);
    
$pdf->SetFont('arial','',10);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+3.8+0.3,$Y+11.3+0.5-0.1);
$pdf->Cell(1, 0.25, $Pago_FormaDePagoP, 0, 1,'L', 0);


$pdf->SetFont('arial','B',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.05+8+0.1-0.3,$Y+11.3-0.1-0.5);
$pdf->Cell(1, 0.25, "MONEDA:", 0, 1,'L', 0);
    
$pdf->SetFont('arial','',10);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+10.6,$Y+11.3-0.1-0.5);
$pdf->Cell(1, 0.25, $Pago_MonedaP, 0, 1,'L', 0);

$pdf->SetFont('arial','B',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.05+8+0.1-0.3,$Y+11.3-0.1);
$pdf->Cell(1, 0.25, "MONTO:", 0, 1,'L', 0);

$pdf->SetFont('arial','',10);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+1.05+5.5+3.8,$Y+11.3-0.1);
$pdf->Cell(1, 0.25, number_format($Pago_Monto,2), 0, 1,'L', 0);

$pdf->SetFont('arial','B',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.05+8+0.1-0.3,$Y+11.3-0.1+0.5);
$pdf->Cell(1, 0.25, utf8_decode("No. DE OPERACIÓN:"), 0, 1,'L', 0);

$pdf->SetFont('arial','',10);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+1.05+5.5+3.8+1.8,$Y+11.3-0.1+0.5);
$pdf->Cell(1, 0.25, $Pago_NumOperacion, 0, 1,'L', 0);

    
    
// DOCUMENTOS RELACIONADOS =====================================================

$Y = $Y -0.5;

$pdf->RoundedRect($X+1, $Y+8.4+2+2.6, 19.4, 8, 0.2, '');

$pdf->SetTextColor(7,100,30);
$pdf->SetFont('arial','B',11);
$pdf->SetXY($X+1+0.1,$Y+13.3);
$pdf->Cell(1.3, 0.25, "DOCUMENTOS RELACIONADOS.", 0, 1,'L', 0);

$Y = $Y + 1;

$pdf->SetFont('arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.7,$Y+13);

$pdf->Cell(7.7, 0.6, "UUID (Folio fiscal).", 1, 0,'L', 0);
$pdf->Cell(2, 0.6, "No. Doc.", 1, 0,'L', 0);
$pdf->Cell(1.6, 0.6, "No. Parc", 1, 0,'C', 0);
$pdf->Cell(2.2, 0.6, "Saldo Ant.", 1, 0,'R', 0);
$pdf->Cell(2.2, 0.6, "Pago", 1, 0,'R', 0);
$pdf->Cell(2.2, 0.6, "Saldo insol.", 1, 0,'R', 0);

$Y = $Y + 0.6;

for ($i=0; $i<count($ArrayDocRel_IdDocumento); $i++ ){

    $pdf->SetFont('arial','',9);
    $pdf->SetTextColor(17,71,121);
    $pdf->SetXY($X+1.7,$Y+13);
    
    $pdf->Cell(7.7, 0.6, $ArrayDocRel_IdDocumento[$i], 1, 0,'L', 0);
    $pdf->Cell(2, 0.6, $ArrayDocRel_NoDoc[$i], 1, 0,'L', 0);
    $pdf->Cell(1.6, 0.6, $ArrayDocRel_NumParcialidad[$i], 1, 0,'C', 0);
    $pdf->Cell(2.2, 0.6, number_format($ArrayDocRel_ImpSaldoAntd[$i],2), 1, 0,'R', 0);
    $pdf->Cell(2.2, 0.6, number_format($ArrayDocRel_ImpPagado[$i],2), 1, 0,'R', 0);
    $pdf->Cell(2.2, 0.6, number_format($ArrayDocRel_ImpSaldoInsoluto[$i],2), 1, 0,'R', 0);

    $Y = $Y + 0.6;
}


DatosInf($pdf, $filename, 0, $selloCFD, $selloSAT, $CadOri, $PaginaWeb);

$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'F'); // Se graba el documento .PDF en el disco duro o unidad de estado sólido.

chmod ($SendaArchsCFDI.$NomArchPDF,0777);  // <-- Descomentar si está utilizando el sistema operativo LINUX.
    
$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'I'); // Se muestra el documento .PDF en el navegador.




function Titulos($pdf, $Y){
    
    $Y = $Y + 0.24;
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);    
    
    $pdf->SetXY(1,$Y);
    $pdf->Cell(1.5, 0.30, "Cant.", 0, 1,'C', 0);

    $pdf->SetXY(3.5,$Y);
    $pdf->Cell(3.3, 0.30, "Clave Prod.", 0, 1,'L', 0);

    $pdf->SetXY(7.3,$Y);
    $pdf->MultiCell(4.2, 0.35, utf8_decode("Descripción"), 0, 'L', 0);

    $pdf->SetXY(16.8,$Y);
    $pdf->Cell(1.5, 0.30, utf8_decode("P/U"), 0, 1,'R', 0);

    $pdf->SetXY(18.5,$Y);
    $pdf->Cell(1.8, 0.30, utf8_decode("Importe"), 0, 1,'R', 0);    
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


