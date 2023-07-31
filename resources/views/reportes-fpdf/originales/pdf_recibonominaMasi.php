<?php

if (isset($_SESSION['empresa']) && !empty($_SESSION['empresa'])) {

        $Empresa=$_SESSION['empresa'];
}else{
    $Empresa=base64_decode($_GET['emp']);
}



    $idempleado=$idemple;

//fechaAlta,idempleado,numsegurosocial,departamento,curp,categoria
    $querydirectorio="SELECT *,substring_index(categoriaesp,' ',4) as puesto,substring_index(departamento,' ',2) as depa from $base.empleado where idempleado='$idempleado'";
    $rowresultdirec=$db->command($querydirectorio);
    $repositorio=$rowresultdirec['repositorio'];
    

    $queryemisora="SELECT upper(ememi.RazonSocial) as razon,usertimbre,ememi.CP as cp from $base.empleado em join $base.categorias cat on em.categoria=cat.NombreCategoria inner join registropatronal regpat on cat.TipodeClase=regpat.idregistrop inner join empresasemisoras ememi on regpat.ID_EmpresaE=ememi.ID_EmpresaE where idempleado='$idempleado' and  ememi.Status=0 and cat.Status=0;";
$rowresultemisora=$db->command($queryemisora);
$emisora=$rowresultemisora['razon'];


//header('Content-Type: text/html; charset=UTF-8');
require("fpdf/fpdf.php");
 
      //$EmailPemisos=$_SESSION['email'];

      //$Empresa=$_SESSION['empresa'];


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

if($importevalorisr){

$valorisr=$importevalorisr;

}else{

    $valorisr=2;
}


$Nomina_FechaPago="";
$Nomina_FechaInicialPago="";
$Nomina_FechaFinalPago="";
$Nomina_NumDiasPagados="0";
$Nomina_TotalPercepciones="0";
$Nomina_TotalDeducciones="0";
$Nomina_TotalOtrosPagos="0";
$total="0";
$Nomina_FechaInicioLaboral="0";
$Nomina_NoEmpleado="0";
$Nomina_NoImss="0";
$Nomina_Departamento="0";
$Nomina_CURP="0";
$Nomina_Puesto="0";


$FechaHoraEmision = date("Y-m-d")."T".date("H:i:s"); // Esta fecha se asigna en este punto de manera PROVISIONAL para efectos de demostración 
$StatusCFDI = "ACTIVO"; // "ACTIVO" o "CANCELADO".
$PaginaWeb = "www.puntodeventaweb.com.mx";

$xml = file_get_contents($SendaArchsCFDI.$NomArchXML);

    $Emisor_nombre = "";
    $Emisor_RFC = "";
    $Emisor_RegistroPatronal = "";
    $Emisor_RfcPatronOrigen = "";

#== 2. Obteniendo datos del archivo .XML =========================================

    $DocXML = new DOMDocument('1.0', 'utf-8');
    $DocXML->preserveWhiteSpace = FALSE;
    $DocXML->loadXML($xml);

    $params = $DocXML->getElementsByTagName('TimbreFiscalDigital');
    foreach ($params as $param) {
           $UUID     = $param->getAttribute('UUID');
           $noCertificadoSAT = $param->getAttribute('NoCertificadoSAT');
           $selloCFD = $param->getAttribute('SelloCFD');
           $selloSAT = $param->getAttribute('SelloSAT');
    }      

    $params = $DocXML->getElementsByTagName('Emisor');
    foreach ($params as $param) {
        
        if (strlen($param->getAttribute('RegimenFiscal'))>0){
           $Emisor_Regimen = $param->getAttribute('RegimenFiscal');
        }
        
        if (strlen($param->getAttribute('Nombre'))>0){
            $Emisor_nombre = $param->getAttribute('Nombre');
        }
        
        if (strlen($param->getAttribute('Rfc'))>0){
            $Emisor_RFC = $param->getAttribute('Rfc');
        }
        
        if (strlen($param->getAttribute('RegistroPatronal'))>0){
            $Emisor_RegistroPatronal = $param->getAttribute('RegistroPatronal');
        }
    }      
    
    $params = $DocXML->getElementsByTagName('SistemaLocal');
    foreach ($params as $param) {
           $serie = $param->getAttribute('serie');
           $folio = $param->getAttribute('folio');
           $NoRec = $param->getAttribute('NoRec');
    }      
    

    $k=0;

    $Nomina_FechaInicioLaboral=$rowresultdirec['fechaAlta'];;
    $Nomina_NoEmpleado        = $rowresultdirec['idempleado'];
    $Nomina_NoImss            = $rowresultdirec['numsegurosocial'];
    $Nomina_Departamento      = $rowresultdirec['depa'];
    $Nomina_CURP              = $rowresultdirec['curp'];
    $Nomina_Puesto            = $rowresultdirec['puesto'];

    
    $params = $DocXML->getElementsByTagName('Receptor');
    foreach ($params as $param) {
        if (strlen($param->getAttribute('Nombre'))>0){
            $Receptor_Nom = $param->getAttribute('Nombre'); 
        }
        if (strlen($param->getAttribute('Rfc'))>0){
            $Receptor_RFC = $param->getAttribute('Rfc');
        }
    }    
    
    $params = $DocXML->getElementsByTagName('Comprobante');
    foreach ($params as $param) {
           $LugarExpedicion = $param->getAttribute('LugarExpedicion');
           $Fact_Fecha      = $param->getAttribute('Fecha');
           $total           = $param->getAttribute('Total');
           $version         = $param->getAttribute('Version');
           $noCertificado   = $param->getAttribute('NoCertificado');
    }


    
    $params = $DocXML->getElementsByTagName('Concepto');
    foreach ($params as $param) {
        $Concept_ClaveProdServ = $param->getAttribute('ClaveProdServ');
        $Concept_ClaveUnidad   = $param->getAttribute('ClaveUnidad');
        $Concept_Cantidad      = $param->getAttribute('Cantidad'); 
        $Concept_Descripcion   = $param->getAttribute('Descripcion');
        $Concept_ValorUnitario = $param->getAttribute('ValorUnitario');
        $Concept_Importe       = $param->getAttribute('Importe');
        $Concept_Descuento     = $param->getAttribute('Descuento');
    }       
    
    $CadOri = "||".$UUID."|".$Fact_Fecha."|".$selloCFD."|".$noCertificado."||";
    
    
    $params = $DocXML->getElementsByTagName('Nomina');
    if($valorisr>0){
    foreach ($params as $param) {
        
        $Nomina_FechaPago         = $param->getAttribute('FechaPago');
        $Nomina_FechaInicialPago  = $param->getAttribute('FechaInicialPago');
        $Nomina_FechaFinalPago    = $param->getAttribute('FechaFinalPago'); 
        $Nomina_NumDiasPagados    = $param->getAttribute('NumDiasPagados');
        $Nomina_TotalPercepciones = $param->getAttribute('TotalPercepciones');
        $Nomina_TotalDeducciones  = $param->getAttribute('TotalDeducciones');
    }  
    }else{

            foreach ($params as $param) {
        
        $Nomina_FechaPago         = $param->getAttribute('FechaPago');
        $Nomina_FechaInicialPago  = $param->getAttribute('FechaInicialPago');
        $Nomina_FechaFinalPago    = $param->getAttribute('FechaFinalPago'); 
        $Nomina_NumDiasPagados    = $param->getAttribute('NumDiasPagados');
        $Nomina_TotalPercepciones = $param->getAttribute('TotalPercepciones');
        $Nomina_TotalDeducciones  = $param->getAttribute('TotalDeducciones');
        $Nomina_TotalOtrosPagos   = $param->getAttribute('TotalOtrosPagos');
    }  

    }    
    
    $i=0;
    $params = $DocXML->getElementsByTagName('Percepcion');
    foreach ($params as $param) {
           $ArrayPercep_TipoPercepcion[$i] = $param->getAttribute('TipoPercepcion');
           $ArrayPercep_Clave[$i] = $param->getAttribute('Clave');
           $ArrayPercep_Concepto[$i] = $param->getAttribute('Concepto');
           $ArrayPercep_ImporteGravado[$i] = $param->getAttribute('ImporteGravado');
           $ArrayPercep_ImporteExento[$i] = $param->getAttribute('ImporteExento');
           $i++;
    }       
        
    $i=0;
    $params = $DocXML->getElementsByTagName('Deduccion');
    foreach ($params as $param) {
           $ArrayDeduc_TipoDeduccion[$i] = $param->getAttribute('TipoDeduccion');
           $ArrayDeduc_Clave[$i] = $param->getAttribute('Clave');
           $ArrayDeduc_Concepto[$i] = $param->getAttribute('Concepto');
           $ArrayDeduc_Importe[$i] = $param->getAttribute('Importe');
           $i++;
    }       
    
    $i=0;
    $params = $DocXML->getElementsByTagName('OtroPago');
    foreach ($params as $param) {
           $ArrayOtrosPag_TipoOtroPago[$i] = $param->getAttribute('TipoOtroPago');
           $ArrayOtrosPag_Clave[$i] = $param->getAttribute('Clave');
           $ArrayOtrosPag_Concepto[$i] = $param->getAttribute('Concepto');
           $ArrayOtrosPag_Importe[$i] = $param->getAttribute('Importe');
           $i++;
    }       
    
    $i=0;
    
    $params = $DocXML->getElementsByTagName('HorasExtra');
    
    $ArrayHrsExtra_Dias = []; 
    
    foreach ($params as $param) {
           $ArrayHrsExtra_Dias[$i] = $param->getAttribute('Dias');
           $ArrayHrsExtra_TipoHoras[$i] = $param->getAttribute('TipoHoras');
           $ArrayHrsExtra_HorasExtra[$i] = $param->getAttribute('HorasExtra');
           $ArrayHrsExtra_ImportePagado[$i] = $param->getAttribute('ImportePagado');
           $i++;
    }       
    
    $i=0;
    $params = $DocXML->getElementsByTagName('Incapacidad');
    $ArrayIncap_Dias = [];
    
    foreach ($params as $param) {
           $ArrayIncap_Dias[$i] = $param->getAttribute('DiasIncapacidad');
           $ArrayIncap_TipoIncapacidad[$i] = $param->getAttribute('TipoIncapacidad');
           $ArrayIncap_ImporteMonetario[$i] = $param->getAttribute('ImporteMonetario');
           $i++;
    }       

    
    $SubsidioCausado = "0";
    $params = $DocXML->getElementsByTagName('SubsidioAlEmpleo');
    foreach ($params as $param) {
           $SubsidioCausado = $param->getAttribute('SubsidioCausado');
    }        
    
    
    $SubContr_RfcLabora = "";
    $SubContr_PorcentajeTiempo = "";
    $params = $DocXML->getElementsByTagName('SubContratacion');
    foreach ($params as $param) {
        $SubContr_RfcLabora = $param->getAttribute('RfcLabora');
        $SubContr_PorcentajeTiempo = $param->getAttribute('PorcentajeTiempo');
    }        
    

    $SepIndem_TotalPagado="0";
    $SepIndem_NumAñosServicio="0";
    $SepIndem_UltimoSueldoMensOrd="0";
    $SepIndem_IngresoAcumulable="0";
    $SepIndem_IngresoNoAcumulable="0";
    
    $params = $DocXML->getElementsByTagName('SeparacionIndemnizacion');
    foreach ($params as $param) {
        $SepIndem_TotalPagado = $param->getAttribute('TotalPagado');    
        $SepIndem_NumAniosServicio = $param->getAttribute('NumAñosServicio');
        $SepIndem_UltimoSueldoMensOrd = $param->getAttribute('UltimoSueldoMensOrd');
        $SepIndem_IngresoAcumulable = $param->getAttribute('IngresoAcumulable');
        $SepIndem_IngresoNoAcumulable = $param->getAttribute('IngresoNoAcumulable');
    }        
        
    $JubPenRet_TotalParcialidad=0;
    $JubPenRet_IngresoAcumulable="0";
    $JubPenRet_IngresoNoAcumulable="0";
    
    $params = $DocXML->getElementsByTagName('JubilacionPensionRetiro');
    foreach ($params as $param) {
        if (strlen($param->getAttribute('TotalParcialidad')>0)){
            $JubPenRet_TotalParcialidad = $param->getAttribute('TotalParcialidad');
        }
        $JubPenRet_IngresoAcumulable = $param->getAttribute('IngresoAcumulable');    
        $JubPenRet_IngresoNoAcumulable = $param->getAttribute('IngresoNoAcumulable');
    }            
    
    
    
#== 3. Crear archivo .PNG con codigo bidimensional =================================
$filename = $SendaArchsGraf."/Img_".$UUID.".png";
$CadImpTot = ProcesImpTot($total);
$cadenaSello= substr($selloCFD,-8);
//echo 'CADENAAAAA      '.$cadenaSello;
$Cadena = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=".$UUID."&re=".$Emisor_RFC."&rr=".$Receptor_RFC."&tt=".$CadImpTot."&fe=".$cadenaSello;

QRcode::png($Cadena, $filename, 'H', 3, 2);    
chmod($filename, 0777);  


#== 4. Construyendo el documentos con la librería FPDF =======================================

$pdf=new PDF('P','cm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('IDAutomationHC39M','','IDAutomationHC39M.php');
$pdf->AddFont('verdana','','verdana.php');
$pdf->SetAutoPageBreak(true);
$pdf->SetMargins(0, 0, 0);
$pdf->SetLineWidth(0.02);
$pdf->SetFillColor(0,0,0);

    
    $X = 0;
    $Y = 0;


    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',12);
    $pdf->SetXY($X+1,$Y+1.65+0.1);

    $pdf->image("archs_graf/FondoTenue.jpg",$X+1, $Y+8.6 , 19.5, 18);
    
    //==========================================================================

    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',12);
    $pdf->SetXY($X+10.5,$Y+1.25+0.1);
    $pdf->Cell(1.5, 0.25, utf8_decode("RECIBO DE NÓMINA."), 0, 1,'L', 0);
    
    $pdf->SetTextColor(199,199,199);
    $pdf->SetFont('arial','BI',9);
    $pdf->SetXY($X+18.7,$Y+6.7);
    $pdf->Cell(2, 0.25, utf8_decode("CFDI 3.3"), 0, 1,'L', 0);        
    
       
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+2.05+0.05);
    $pdf->Cell(2, 0.25, "FOLIO FISCAL:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',11);
    $pdf->SetXY($X+10.5+0.5,$Y+2.5+0.05);
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
        $pdf->Cell(2, 0.25, utf8_decode("LUGAR DE EXPEDICIÓN:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',9);
        $pdf->SetXY($X+10.5+0.5,$Y+4.46+0.5+0.34);
        $pdf->Cell(2, 0.25, utf8_decode($LugarExpedicion), 0, 1,'L', 0);
        
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);
    $pdf->SetXY($X+10.5,$Y+5.86);
    $pdf->Cell(2, 0.25, utf8_decode("FECHA HORA DE CERTIFICACIÓN:"), 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',9);
    $pdf->SetXY($X+10.5+0.5,$Y+6.21);
    $pdf->Cell(2, 0.25, $Fact_Fecha, 0, 1,'L', 0);
        
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('arial','B',9);
        $pdf->SetXY($X+10.5,$Y+5.86+0.95-0.05);
        $pdf->Cell(2, 0.25, utf8_decode("RÉGIMEN FISCAL:"), 0, 1,'L', 0);

        $pdf->SetFont('arial','',9);
        $pdf->SetTextColor(17,71,121);
        $pdf->SetXY($X+10.5+0.5,$Y+6.21+0.92-0.05);
        $pdf->MultiCell(9.4, 0.35, utf8_decode($Emisor_Regimen), 0, 'L');

    $pdf->RoundedRect($X+10.4, $Y+1, 10, 6.6, 0.2, '');        

    //======================================================================
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+1.7);
    $pdf->MultiCell(8.8, 0.35, "EMPRESA:", 0, 'L');
    
    $pdf->SetTextColor(0,0,0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+1.4,$Y+2.5);
        $pdf->MultiCell(8.6, 0.35, utf8_decode($Emisor_nombre), 0, 'L');

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+2.6+1.4);
    $pdf->Cell(0.5, 0.25, utf8_decode("RFC:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+1.05+1.04,$Y+2.6+1.4);
        $pdf->Cell(1, 0.25, utf8_decode($Emisor_RFC), 0, 1,'L', 0);
        
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+2.7+1.5+0.4);
    $pdf->Cell(0.5, 0.25, utf8_decode("RÉGIMEN:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+1.05+2,$Y+2.7+1.5+0.4);
        $pdf->Cell(0.5, 0.25, utf8_decode($Emisor_Regimen), 0, 1,'L', 0);

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+2.7+1.5+0.4+0.6);
    $pdf->Cell(0.5, 0.25, utf8_decode("REGISTRO PATRONAL:"), 0, 1,'L', 0);
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',10);
        $pdf->SetXY($X+1.05+4.2,$Y+2.7+1.5+0.4+0.6);
        $pdf->Cell(0.5, 0.25, utf8_decode($Emisor_RegistroPatronal), 0, 1,'L', 0);
        
        
    $pdf->SetTextColor(199,199,199);
    $pdf->SetFont('arial','BI',9);
    $pdf->SetXY($X+1.2,$Y+6.5);
    $pdf->Cell(8.5, 0.25, utf8_decode("RECIBO DE NÓMINA VERSIÓN 1.2"), 0, 1,'C', 0);        
        

    $pdf->RoundedRect($X+1, $Y+1, 9, 6.6, 0.2, '');  
    
    //======================================================================
    
    $pdf->RoundedRect($X+1, $Y+7.86, 19.4, 0.8, 0.2, '');
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.05,$Y+8.6-0.45);
    $pdf->Cell(1, 0.25, "EMPLEADO:", 0, 1,'L', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',10);
    $pdf->SetXY($X+1.5+1.9,$Y+8.6-0.45);
    $pdf->Cell(1, 0.25, utf8_decode($Receptor_Nom), 0, 1,'L', 0);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+2.05+12+1.2,$Y+8.6-0.45);
    $pdf->Cell(1, 0.25, "RFC:", 0, 1,'L', 0);
    
    $pdf->SetTextColor(17,71,121);
    $pdf->SetFont('arial','',11);
    $pdf->SetXY($X+2.5+12+0.6+1.2,$Y+8.6-0.45);
    $pdf->Cell(1, 0.25, $Receptor_RFC, 0, 1,'L', 0);

    
    $pdf->RoundedRect($X+1, $Y+8.9, 19.4, 1.5, 0.2, '');
    
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',10);
    $pdf->SetXY($X+1.15,$Y+9.06);
    $pdf->Cell(1.5, 0.25, utf8_decode("CONCEPTO DE PAGO"), 0, 1,'L', 0);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','',10);
    $pdf->SetXY($X+1.2,$Y+9.06+0.45);
    $pdf->Cell(2.2, 0.25, utf8_decode("ClaProdServ"), 0, 1,'L', 0);
    
    $pdf->SetXY($X+1.4+2.6,$Y+9.06+0.45);
    $pdf->Cell(1.8, 0.25, utf8_decode("ClaUnidad"), 0, 1,'L', 0);
    
    $pdf->SetXY($X+1.4+5.1,$Y+9.06+0.45);
    $pdf->Cell(1, 0.25, utf8_decode("Cant"), 0, 1,'C', 0);

    $pdf->SetXY($X+1.4+6.7,$Y+9.06+0.45);
    $pdf->Cell(1.5, 0.25, utf8_decode("Descripción"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.-0.35,$Y+9.06+0.45);
    $pdf->Cell(1.5, 0.25, utf8_decode("Valor unitario"), 0, 1,'R', 0);

    $pdf->SetXY($X+11+4.6,$Y+9.06+0.45);
    $pdf->Cell(1.5, 0.25, utf8_decode("Importe"), 0, 1,'R', 0);

    $pdf->SetXY($X+11+7.2,$Y+9.06+0.45);
    $pdf->Cell(1.5, 0.25, utf8_decode("Descuento"), 0, 1,'R', 0);

    
    $pdf->SetFont('arial','',10);
    
    $pdf->SetTextColor(171,18,18);
    $pdf->SetXY($X+1.2,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(2.2, 0.25, $Concept_ClaveProdServ, 0, 1,'C', 0);

    $pdf->SetXY($X+1.4+2.6,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1.8, 0.25, $Concept_ClaveUnidad, 0, 1,'C', 0);

    $pdf->SetTextColor(17,71,121);
    $pdf->SetXY($X+1.4+5.1,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1, 0.25, $Concept_Cantidad, 0, 1,'C', 0);

    $pdf->SetXY($X+1.4+6.7,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1.5, 0.25, utf8_decode($Concept_Descripcion), 0, 1,'L', 0);
    
    $pdf->SetXY($X+13.-0.35,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1.5, 0.25, number_format($Concept_ValorUnitario,2), 0, 1,'R', 0);

    $pdf->SetXY($X+11+4.6,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1.5, 0.25, number_format($Concept_Importe,2), 0, 1,'R', 0);
    
    $pdf->SetXY($X+11+7.2,$Y+9.06+0.45+0.4+0.04);
    $pdf->Cell(1.5, 0.25, number_format($Concept_Descuento,2), 0, 1,'R', 0);
    
    
// =============================================================================    
    
    $pdf->RoundedRect($X+1, $Y+10.6, 19.4, 11.2, 0.2, '');

    
    $pdf->SetFont('arial','',9);
    $pdf->SetTextColor(0,0,0);
    
    $pdf->SetXY($X+13.3,$Y+11.32);
    $pdf->Cell(3.7, 0.25, utf8_decode("No.Empleado:"), 0, 1,'L', 0);

    /*$pdf->SetXY($X+13.3,$Y+11.32+0.55);
    $pdf->Cell(3.7, 0.25, utf8_decode("Fecha inicio Laboral:"), 0, 1,'L', 0);*/

    $pdf->SetXY($X+13.3,$Y+11.32+0.55);
    $pdf->Cell(3.7, 0.25, utf8_decode("No.IMSS:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*2));
    $pdf->Cell(3.7, 0.25, utf8_decode("Departamento:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*3));
    $pdf->Cell(3.7, 0.25, utf8_decode("CURP:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*4));
    $pdf->Cell(3.7, 0.25, utf8_decode("Puesto:"), 0, 1,'L', 0);

    /*$pdf->SetXY($X+13.3,$Y+11.32+(0.55*6));
    $pdf->Cell(3.7, 0.25, utf8_decode("Fecha de pago:"), 0, 1,'L', 0);*/

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*5));
    $pdf->Cell(3.7, 0.25, utf8_decode("Fecha inicial de pago:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*6));
    $pdf->Cell(3.7, 0.25, utf8_decode("Fecha final de pago:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*7));
    $pdf->Cell(3.7, 0.25, utf8_decode("Núm. de días pagados:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*8));
    $pdf->Cell(3.7, 0.25, utf8_decode("Total de percepciones:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*9));
    $pdf->Cell(3.7, 0.25, utf8_decode("Total de deducciones:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*10));
    $pdf->Cell(3.7, 0.25, utf8_decode("Otros pagos:"), 0, 1,'L', 0);

    $pdf->SetXY($X+13.3,$Y+11.32+(0.55*11));
    $pdf->Cell(3.7, 0.25, utf8_decode("Neto recibido:"), 0, 1,'L', 0);


    $pdf->SetFont('arial','IU',8);
    $pdf->SetXY($X+1,$Y+20.82);
    $pdf->Cell(3.7, 0.25, utf8_decode("Recibí de conformidad la cantidad descrita, haciendo constar que con dicha suma estoy totalmente pagado (a) hasta la fecha señalada en el presente "), 0, 1,'L', 0);
    $pdf->SetXY($X+1,$Y+21.32);
    $pdf->Cell(3.7, 0.25, utf8_decode("recibo, por lo que no me reservo acción ni derecho alguno para reclamar por estos conceptos ni por ningún otro."), 0, 1,'L', 0);


    
    
        $pdf->SetFont('arial','',9);
        $pdf->SetTextColor(17,71,121);

        $pdf->SetXY($X+18.3,$Y+11.32);
        $pdf->Cell(2, 0.25, ($Nomina_NoEmpleado), 0, 1,'R', 0);

        /*$pdf->SetXY($X+18.3,$Y+11.32+0.55);
        $pdf->Cell(2, 0.25, InvertirFecha($Nomina_FechaInicioLaboral), 0, 1,'R', 0);*/

        $pdf->SetXY($X+18.3,$Y+11.32+0.55);
        $pdf->Cell(2, 0.25, ($Nomina_NoImss), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*2));
        $pdf->Cell(2, 0.25, ($Nomina_Departamento), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*3));
        $pdf->Cell(2, 0.25, ($Nomina_CURP), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*4));
        $pdf->Cell(2, 0.25, ($Nomina_Puesto), 0, 1,'R', 0);

        /*$pdf->SetXY($X+18.3,$Y+11.32+(0.55*6));
        $pdf->Cell(2, 0.25, InvertirFecha($Nomina_FechaPago), 0, 1,'R', 0);*/

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*5));
        $pdf->Cell(2, 0.25, InvertirFecha($Nomina_FechaInicialPago), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*6));
        $pdf->Cell(2, 0.25, InvertirFecha($Nomina_FechaFinalPago), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*7));
        $pdf->Cell(2, 0.25, number_format($Nomina_NumDiasPagados,0), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*8));
        $pdf->Cell(2, 0.25, number_format($Nomina_TotalPercepciones,2), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*9));
        $pdf->Cell(2, 0.25, number_format($Nomina_TotalDeducciones,2), 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*10));
        $pdf->Cell(2, 0.25, $Nomina_TotalOtrosPagos, 0, 1,'R', 0);

        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*11));
        $pdf->Cell(2, 0.25, number_format($total,2), 0, 1,'R', 0);
        
        
   
// Conceptos de percepciones ===================================================
  
$Y = 10.8;

$pdf->SetFont('arial','B',9); 
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+1.2,$Y);
$pdf->Cell(1.7, 0.30, "PERCEPCIONES:", 0, 1,'L', 0);

    $pdf->SetFont('arial','',8); 
    $pdf->SetXY($X+1.3,$Y+0.5);
    $pdf->Cell(1, 0.35, "TIPO", 0, 1,'C', 0);
    
    $pdf->SetXY($X+1.3+1.2,$Y+0.5);
    $pdf->Cell(1, 0.35, "CLAVE", 0, 1,'C', 0);

    $pdf->SetXY($X+2.4+1.3,$Y+0.5);
    $pdf->Cell(1, 0.35, "CONCEPTO", 0, 1,'L', 0);
    $pdf->Write(0.4,'');
    $YY = $pdf->GetY()+0.18;

    $pdf->SetXY($X+9.5+1.3,$Y+0.5);
    $pdf->Cell(1.7, 0.35, "IMPORTE", 0, 1,'R', 0);

$Y = $Y + 1;

$TotRegs = count($ArrayPercep_Clave);
$SumaPercep = 0;

for ($i=0; $i<$TotRegs; $i++){
    
    $pdf->SetFont('arial','',8);
    $pdf->SetTextColor(17,71,121);

    $pdf->SetXY($X+1.3,$Y);
    $pdf->Cell(1, 0.35, $ArrayPercep_TipoPercepcion[$i], 0, 1,'C', 0);
    
    $pdf->SetXY($X+1.3+1.2,$Y);
    $pdf->Cell(1, 0.35, $ArrayPercep_Clave[$i], 0, 1,'C', 0);

    $pdf->SetXY($X+2.4+1.3,$Y);
    $pdf->MultiCell(7, 0.35, utf8_decode($ArrayPercep_Concepto[$i]), 0, 'L', 0);
    $pdf->Write(0.4,'');
    $YY = $pdf->GetY()+0.18;

    $pdf->SetXY($X+9.5+1.3,$Y);
    $pdf->Cell(1.7, 0.35, number_format($ArrayPercep_ImporteGravado[$i],2), 0, 1,'R', 0);
    
    $SumaPercep = $SumaPercep + $ArrayPercep_ImporteGravado[$i];

    $pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.1);

    $Y = $YY;
}
    
$pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.05);

$Y = $Y + 0.1;

$pdf->SetFont('arial','',8);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($X+7.8+1.3,$Y);
$pdf->Cell(1.7, 0.35, "TOTAL DE PERCEPCIONES:", 0, 1,'R', 0);

$pdf->SetFont('arial','',8);
$pdf->SetTextColor(17,71,121);
$pdf->SetXY($X+9.5+1.3,$Y);
$pdf->Cell(1.7, 0.35, number_format($SumaPercep,2), 0, 1,'R', 0);


// Conceptos de deducciones ====================================================

if (count($ArrayDeduc_Clave)>0){

    $Y = $pdf->GetY()+0.1;

    $pdf->SetFont('arial','B',9); 
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($X+1.2,$Y);
    $pdf->Cell(1.7, 0.30, "DEDUCCIONES:", 0, 1,'L', 0);

        $pdf->SetFont('arial','',8); 
        $pdf->SetXY($X+1.3,$Y+0.5);
        $pdf->Cell(1, 0.35, "TIPO", 0, 1,'C', 0);

        $pdf->SetXY($X+1.3+1.2,$Y+0.5);
        $pdf->Cell(1, 0.35, "CLAVE", 0, 1,'C', 0);

        $pdf->SetXY($X+2.4+1.3,$Y+0.5);
        $pdf->Cell(1, 0.35, "CONCEPTO", 0, 1,'L', 0);
        $pdf->Write(0.4,'');
        $YY = $pdf->GetY()+0.18;

        $pdf->SetXY($X+9.5+1.3,$Y+0.5);
        $pdf->Cell(1.7, 0.35, "IMPORTE", 0, 1,'R', 0);

    $Y = $Y + 1;

    $TotRegs = count($ArrayDeduc_TipoDeduccion);
    $SumaDeduc = 0;

    for ($i=0; $i<$TotRegs; $i++){

        $pdf->SetFont('arial','',8);
        $pdf->SetTextColor(17,71,121);

        $pdf->SetXY($X+1.3,$Y);
        $pdf->Cell(1, 0.35, $ArrayDeduc_TipoDeduccion[$i], 0, 1,'C', 0);

        $pdf->SetXY($X+1.3+1.2,$Y);
        $pdf->Cell(1, 0.35, $ArrayDeduc_Clave[$i], 0, 1,'C', 0);

        $pdf->SetXY($X+2.4+1.3,$Y);
        $pdf->MultiCell(7, 0.35, utf8_decode($ArrayDeduc_Concepto[$i]), 0, 'L', 0);
        $pdf->Write(0.4,'');
        $YY = $pdf->GetY()+0.18;

        $pdf->SetXY($X+9.5+1.3,$Y);
        $pdf->Cell(1.7, 0.35, number_format($ArrayDeduc_Importe[$i],2), 0, 1,'R', 0);

        $SumaDeduc = $SumaDeduc + $ArrayDeduc_Importe[$i];

        $pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.1);

        $Y = $YY;
    }

    $pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.05);

    $Y = $Y + 0.1;

    $pdf->SetFont('arial','',8);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($X+7.8+1.3,$Y);
    $pdf->Cell(1.7, 0.35, "TOTAL DE DEDUCCIONES:", 0, 1,'R', 0);

    $pdf->SetFont('arial','',8);
    $pdf->SetTextColor(17,71,121);
    $pdf->SetXY($X+9.5+1.3,$Y);
    $pdf->Cell(1.7, 0.35, number_format($SumaDeduc,2), 0, 1,'R', 0);

}

// Conceptos de otros pagos ====================================================
if($valorisr<0){

if (count($ArrayOtrosPag_Clave)>0){

    $Y = $pdf->GetY()+0.1;

    $pdf->SetFont('arial','B',9); 
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($X+1.2,$Y);
    $pdf->Cell(1.7, 0.30, "OTROS PAGOS:", 0, 1,'L', 0);

        $pdf->SetFont('arial','',8); 
        $pdf->SetXY($X+1.3,$Y+0.5);
        $pdf->Cell(1, 0.35, "TIPO", 0, 1,'C', 0);

        $pdf->SetXY($X+1.3+1.2,$Y+0.5);
        $pdf->Cell(1, 0.35, "CLAVE", 0, 1,'C', 0);

        $pdf->SetXY($X+2.4+1.3,$Y+0.5);
        $pdf->Cell(1, 0.35, "CONCEPTO", 0, 1,'L', 0);
        $pdf->Write(0.4,'');
        $YY = $pdf->GetY()+0.18;

        $pdf->SetXY($X+9.5+1.3,$Y+0.5);
        $pdf->Cell(1.7, 0.35, "IMPORTE", 0, 1,'R', 0);

    $Y = $Y + 1;

    $TotRegs = count($ArrayOtrosPag_Clave);
    $SumaOtrosPag = 0;
    $Regs = 0;

    for ($i=0; $i<$TotRegs; $i++){

        $pdf->SetFont('arial','',8);
        $pdf->SetTextColor(17,71,121);

        $pdf->SetXY($X+1.3,$Y);
        $pdf->Cell(1, 0.35, $ArrayOtrosPag_TipoOtroPago[$i], 0, 1,'C', 0);

        $pdf->SetXY($X+1.3+1.2,$Y);
        $pdf->Cell(1, 0.35, $ArrayOtrosPag_Clave[$i], 0, 1,'C', 0);

        $pdf->SetXY($X+2.4+1.3,$Y);
        $pdf->MultiCell(7, 0.35, utf8_decode($ArrayOtrosPag_Concepto[$i]), 0, 'L', 0);
        $pdf->Write(0.4,'');
        $YY = $pdf->GetY()+0.18;

        $pdf->SetXY($X+9.5+1.3,$Y);
        $pdf->Cell(1.7, 0.35, number_format($ArrayOtrosPag_Importe[$i],2), 0, 1,'R', 0);

        $SumaOtrosPag = $SumaOtrosPag + $ArrayOtrosPag_Importe[$i];

        $pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.1);

        $Y = $YY;
        $Regs++;    
    }

    $pdf->line($X+1.2, $Y-0.1, $X+12.6, $Y-0.05);

    $Y = $Y + 0.1;

    $pdf->SetFont('arial','',8);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($X+7.8+1.3,$Y);
    $pdf->Cell(1.7, 0.35, "TOTAL DE OTROS PAGOS:", 0, 1,'R', 0);

    $pdf->SetFont('arial','',8);
    $pdf->SetTextColor(17,71,121);
    $pdf->SetXY($X+9.5+1.3,$Y);
    $pdf->Cell(1.7, 0.35, number_format($SumaOtrosPag,2), 0, 1,'R', 0);

   

}
}
 $Y = $pdf->GetY()+0.7;

    $pdf->SetFont('arial','B',9);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($X+1.5,$Y);
    $pdf->Cell(1, 0.35, "CANTIDAD CON LETRA (NETO RECIBIDO):", 0, 1,'L', 0);

    $pdf->SetFont('arial','',9);
    $pdf->SetTextColor(17,71,121);
    $pdf->SetXY($X+1.5,$Y+0.5);
    $pdf->Cell(1, 0.35, numtoletras($total), 0, 1,'L', 0);

DatosInf($pdf, $filename, 0, $selloCFD, $selloSAT, $CadOri, $PaginaWeb);

VerifStatusCFDI($pdf, $StatusCFDI);

$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'F');
 // Se graba el documento .PDF en el disco duro o unidad de estado sólido.

$verifCar=$_SESSION['dir'].$repositorio.'/';
$verificaRepo=str_replace("/","\ ", $verifCar);
$veriCarpeta=str_replace(" ","", $verificaRepo);

$carpeta = $veriCarpeta;
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$fiche='archs_cfdi/'.$NomArchPDF;

$fiche02='archs_cfdi/'.$NomArchXML;

$fichero=str_replace(" ", "", $fiche);

$fichero02=str_replace(" ", "", $fiche02);

$archi=$_SESSION['dir'].$repositorio;

$archivo=$archi.'/'.$NomArchPDF;

$archivo02=$archi.'/'.$NomArchXML;

$nuevo_fichero=str_replace(" ","", $archivo);

$nuevo_fichero02=str_replace(" ","", $archivo02);

//echo $fichero.$nuevo_fichero;



if (copy($fichero,$nuevo_fichero)) {
    if(copy($fichero02,$nuevo_fichero02)){
    //echo "El fichero ha sido copiado\n";
    }else{

    }
} else {
    //echo "Se ha producido un error al intentar copiar el fichero\n";
}





chmod ($SendaArchsCFDI.$NomArchPDF,0777);  // <-- Descomentar si está utilizando el sistema operativo LINUX.
    
//$pdf->Output($SendaArchsCFDI.$NomArchPDF, 'I'); // Se muestra el documento .PDF en el navegador.





function Titulos($pdf, $Y){
    
    $Y = $Y + 0.24;
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9);    
    
    $pdf->SetXY(1,$Y);
    $pdf->Cell(1.5, 0.30, "Cant.", 0, 1,'C', 0);
    
    $pdf->SetXY(2.6,$Y);
    $pdf->Cell(1.8, 0.30, "Uni. Med.", 0, 1,'L', 0);

    $pdf->SetXY(7.3,$Y);
    $pdf->MultiCell(4.2, 0.35, utf8_decode("Concepto"), 0, 'L', 0);

    $pdf->SetXY(16.8,$Y);
    $pdf->Cell(1.5, 0.30, utf8_decode("Valor unitario"), 0, 1,'R', 0);

    $pdf->SetXY(18.5,$Y);
    $pdf->Cell(1.8, 0.30, utf8_decode("Importe"), 0, 1,'R', 0);    
}


function SubTotales($pdf, $Y, $total, $formaDePago, $metodoDePago, $NumCtaPago, $Puntero, $totalPercepciones, $totalDeducciones, $totalAPagar){
    
    $X = 0 + 0.3;
    $Y = $Y + 0.3;
    
    //== Subtotales ============================================================
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+16.27,$Y+0.35);
    $pdf->Cell(1.7, 0.30, "Total percepciones:", 0, 1,'R', 0);    
    
        $pdf->SetFont('arial','',9); 
        $pdf->SetXY($X+16.27+2,$Y+0.35);
        $pdf->Cell(1.7, 0.30, number_format($totalPercepciones,2), 0, 1,'R', 0);        

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+16.27,$Y+0.35+0.5);
    $pdf->Cell(1.7, 0.30, "Total deducciones:", 0, 1,'R', 0);    
    
        $pdf->SetFont('arial','',9); 
        $pdf->SetXY($X+16.27+2,$Y+0.35+0.5);
        $pdf->Cell(1.7, 0.30, number_format($totalDeducciones,2), 0, 1,'R', 0);        

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+16.27,$Y+0.35+0.5+0.5);
    $pdf->Cell(1.7, 0.30, "Neto a pagar:", 0, 1,'R', 0);    
    
        $pdf->SetFont('arial','',9); 
        $pdf->SetXY($X+16.27+2,$Y+0.35+0.5+0.5);
        $pdf->Cell(1.7, 0.30, number_format($totalAPagar,2), 0, 1,'R', 0);        


    //================================================================
    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY($X+1,$Y+0.35);
    $pdf->Cell(1.7, 0.30, "Total a pagar con letra: ", 0, 1,'L', 0);    

    $pdf->SetFont('arial','',8); 
    $pdf->SetXY($X+1,$Y+0.35+0.40);
    $pdf->Cell(1.7, 0.30, numtoletras($totalAPagar), 0, 1,'L', 0);
    
    
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
    $pdf->SetXY(1.2,22.9+$Y-0.2 -0.8);
    $pdf->Cell(1.7,0.30, "Sello digital del CFDI:", 0, 1,'L', 0);    

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(1.2,+22.9+0.35+$Y-0.2 -0.8);
        $pdf->MultiCell(19.4, 0.25, $selloCFD, 0, 'L', 0);
    
    $pdf->SetTextColor(0,0,0);        
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY(4.2,21.9+2+$Y -0.7);
    $pdf->Cell(1.7, 0.30, "Sello del SAT:", 0, 1,'L', 0);    

        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(4.2,21.9+0.35+2+$Y -0.7);
        $pdf->MultiCell(16.1, 0.25, $selloSAT, 0, 'L', 0);
        
    $pdf->SetTextColor(0,0,0);    
    $pdf->SetFont('arial','B',9); 
    $pdf->SetXY(4.2,25+$Y-0.3);
    $pdf->Cell(1.7, 0.30, utf8_decode("Cadena original del complemento de certificación digital del SAT:"), 0, 1,'L', 0);    
    
        $pdf->SetTextColor(17,71,121);
        $pdf->SetFont('arial','',7); 
        $pdf->SetXY(4.2,25.1+0.25+$Y-0.3);
        $pdf->MultiCell(16.1, 0.25, $CadOri, 0, 'L', 0);
        
    $pdf->SetTextColor(0,0,0);        
    $pdf->SetFont('arial','B',10); 
    $pdf->SetXY(3.2,26.36+$Y);
    $pdf->Cell(15.6, 0.30, utf8_decode("===== Este documento es una representación impresa de un CFDI ====="), 0, 1,'C', 0);    
        
    $pdf->Image($filename,1.2,23.8+$Y -0.7,3,3,'PNG');

//    $pdf->SetFont('arial','I',11); 
//    $pdf->SetTextColor(132,132,132);
//    $pdf->SetXY(1.3,26.9+$Y -0.7);
//    $pdf->Cell(19, 0.30, utf8_decode($PaginaWeb), 0, 1,'C', 0);    
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


function InvertirFecha($Fecha){
    
    $ArrayDatos = explode("-",$Fecha);    
    
    $NvaFec = $ArrayDatos[2]."/".$ArrayDatos[1]."/".$ArrayDatos[0];
    
    return $NvaFec;
}


?>


