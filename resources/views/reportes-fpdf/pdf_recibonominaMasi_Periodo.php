<?php
header('Content-Type: text/html; charset=UTF-8');
include '../../../public/lib/fpdf/fpdf.php';
include '../../../public/lib/qrlib/qrlib.php';
include 'conexion.php';
include 'numerosletras.php';

$base        = $_GET['base'];
$repo        = $_GET['repo'];
$id_periodo  = $_GET['periodo'];

/* Vartiables Globales */
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

$Emisor_nombre = "";
$Emisor_RFC = "";
$Emisor_RegistroPatronal = "";
$Emisor_RfcPatronOrigen = "";

if (isset($_GET['isr'])) 
{
    $valorisr = base64_decode($_GET['isr']);
} else {
    $valorisr = 2;
}

$archs_graf = "../../../storage/app/public/timbrado/archs_graf/";
$archs_cfdi = "../../../storage/app/public/timbrado/archs_cfdi/";
$archs_backup_qr = "../../../storage/app/public/timbrado/bck_qr/";

$url = "../../../storage/app/public/repositorio/".$repo."/timbrado/";
$folder_qr = $url . '/archs_qr/';
$folder_pdf = $url . '/masivo_cfdi/';
$folder_pdf = $url . '/masivo_pdf/';
if(!is_dir($folder_pdf)) {		
	mkdir($folder_pdf, $mode = 0777, true);	
}
/*
$url_xml = $url . 'archs_cfdi/' . $nom_xml;
$url_pdf = $url . 'archs_pdf/' . $nom_pdf;
*/

$NomArchPDF='PDF_Masivo_Periodo_'.$id_periodo.'.pdf';
$url_pdf = $url . 'masivo_pdf/' . $NomArchPDF;


if(file_exists($url_pdf)){
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=$NomArchPDF"); 
    header("Content-Type: application/zip"); 
    header("Content-Transfer-Encoding: binary"); 

    // read the file from disk 
    readfile($url_pdf); 
    exit;
}


$db = new Database($base);

$queryejercicio="SELECT * 
                 FROM periodos_nomina 
                 WHERE id='$id_periodo'";
$rowresulteje=$db->command($queryejercicio);

$ejercicio = $rowresulteje['ejercicio'];
$Nomina_FechaInicialPago=$rowresulteje['fecha_inicial_periodo'];
$Nomina_FechaFinalPago=$rowresulteje['fecha_final_periodo'];
$Nomina_FechaPago=$rowresulteje['fecha_pago'];
$fechainicialPeriodo=new DateTime($rowresulteje['fecha_inicial_periodo']);
$fechafinalPeriodo=new DateTime($rowresulteje['fecha_final_periodo']);

$queryTim = "SELECT *,tim.file_pdf AS NomArchPDF,tim.file_xml AS NomArchXML,em.id AS idemp 
             FROM timbrado tim 
             JOIN empleados em 
             ON tim.id_empleado = em.id 
             WHERE tim.sello_sat <> 'error' 
             AND tim.id_periodo ='$id_periodo' 
             ORDER BY em.apaterno;";
$rowqueryTim=$db->query($queryTim);


class PDF extends FPDF
{
    function Header()
    {
        
    }

    function Footer()
    {

    }
}

$pdf=new PDF('P','cm','Letter');
$pdf->AliasNbPages();

/* INICIO CICLO CREACION D EPAGINAS DEL DOCUMENTO */
while ( $resultTim = mysqli_fetch_array( $rowqueryTim ) ) {
    $X = 0;
    $Y = 0;
    $pdf->AddPage();
    $pdf->AddFont('IDAutomationHC39M','','IDAutomationHC39M.php');
    $pdf->AddFont('verdana','','verdana.php');
    $pdf->SetAutoPageBreak(true);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetLineWidth(0.02);
    $pdf->SetFillColor(0,0,0);

    $id_empleado = $resultTim['idemp'];
    $queryDep = "SELECT nombre
                 FROM departamentos
                 WHERE id = '". $resultTim['id_departamento'] ."';";
    $depto = $rowresulteje=$db->command($queryDep);
    
    $queryPue = "SELECT puesto as nombre
                 FROM puestos
                 WHERE id = '" . $resultTim['id_puesto'] . "';";
    $puesto = $rowresulteje=$db->command($queryPue);
 

    $querytabletempo = "CREATE TABLE tabletempo(idtem int not null auto_increment,idconcep int not null,primary key(idtem))";
    $db->query($querytabletempo);

    $queryidValidaConcep = "SELECT nombre_concepto,id 
                            FROM conceptos_nomina
                            WHERE nomina = 1 
                            AND estatus <> 2 
                            AND file_rool <> 0 
                            AND file_rool <251;";

     $rowid = $db->query($queryidValidaConcep);

     while ($resul=mysqli_fetch_array($rowid)) {
         $idconce = $resul['id'];
         $queryvalorimporte = "SELECT total$idconce 
                               AS result 
                               FROM rutinas$ejercicio
                               WHERE id_periodo = '$id_periodo' 
                               AND  id_empleado = '$id_empleado' 
                               AND fnq_valor = 0;";
         
         $rowvalorimporte = $db->query($queryvalorimporte);
         $importevalor = 999999999;
         if($rowvalorimporte){
            $resultvalorimporte = mysqli_fetch_array($rowvalorimporte);
            $importevalor = $resultvalorimporte['result'];
        }

         if($importevalor <= 0 ) {
             $queryinsert = "INSERT INTO tabletempo(idconcep) values('$idconce')";
             $rowinsert=$db->query($queryinsert);
         }else{ 
             $queryinsert = "INSERT INTO tabletempo(idconcep) values('0')";
             $rowinsert=$db->query($queryinsert);
         }
        
         $queryidconceptos="SELECT upper(group_concat(idconcep)) as cadena FROM tabletempo";
         $rowresulidconcep = $db->command($queryidconceptos);
         $idconcetosPercep = $rowresulidconcep['cadena'];
     }


    $queryemisora="SELECT upper(ememi.razon_social) AS razon,ememi.user_timbre,ememi.cp AS cp,em.rfc AS rfcemi,regpat.num_registro_patronal 
                         FROM $base.empleados em 
                         JOIN $base.categorias cat on em.id_categoria = cat.id
                         INNER JOIN singh.registro_patronal regpat ON cat.tipo_clase = regpat.id 
                         INNER JOIN singh.empresas_emisoras ememi ON regpat.id_empresa_emisora = ememi.id 
                         WHERE em.id = '$id_empleado' 
                         AND  ememi.estatus = 1 
                         AND cat.estatus = 1;";
     
     $rowresultemisora = $db->command($queryemisora);

     $emisora         = $rowresultemisora['razon'];
     $usertimbre      = $rowresultemisora['user_timbre'];
     $LugarExpedicion = $rowresultemisora['cp'];
     $Emisor_nombre   = $emisora;
     
     $Emisor_RegistroPatronal=$rowresultemisora['num_registro_patronal'];

     $queryidISR = "SELECT id
                    FROM conceptos_nomina 
                    WHERE tipo = 1 
                    AND nomina = 1 
                    AND estatus <> 0 
                    AND file_rool <> 0 
                    AND file_rool < 251 
                    AND rutinas = 'ISR'";

     $resul=$db->command($queryidISR);

     $idisr = $resul['id'];

     $queryvalorisr = "SELECT ROUND(total$idisr, 2) 
                       AS result,incapacidades,total_percepcion_fiscal,total_deduccion_fiscal,neto_fiscal 
                       FROM rutinas$ejercicio 
                       WHERE id_periodo = '$id_periodo' 
                       AND id_empleado='$id_empleado' 
                       AND fnq_valor = 0;";
     $resultvalorisr = $db->command($queryvalorisr);
     $importevalorisr = $resultvalorisr['result'];
     $Incapacidadesvalor = $resultvalorisr['incapacidades'];
     $Nomina_TotalPercepciones = round($resultvalorisr['total_percepcion_fiscal'],2);
     $Nomina_TotalDeducciones =  round($resultvalorisr['total_deduccion_fiscal'],2);
     $total = round($resultvalorisr['neto_fiscal'],2);

     if($importevalorisr < 0){
        $TotalPercepAdicional = $Nomina_TotalPercepciones + ( $importevalorisr * -1) ;
        $importeotrospagos    = $importevalorisr * -1;
        $totalotraspagos      = $importevalorisr * -1;
        $totaldeducpositivo   = $Nomina_TotalDeducciones * -1;
        $totalrealdeduccion   = $totalotraspagos - ( $Nomina_TotalDeducciones * -1);
    }else{
        $TotalPercepAdicional = $Nomina_TotalPercepciones;
        $totalrealdeduccion   = round($Nomina_TotalDeducciones,2);
        $totalotraspagos      = 0;
    }
    
    $Nomina_TotalOtrosPagos     = $totalotraspagos;
    $ArrayPercep_TipoPercepcion = array();
    $ArrayPercep_Clave          = array();
    $ArrayPercep_Concepto       = array();
    
    $queryidpercep = "SELECT id,nombre_concepto,SUBSTRING(codigo_sat,1,length(codigo_sat)-1) AS codigo_sat 
                      FROM conceptos_nomina
                      WHERE tipo = 0 
                      AND nomina = 1 
                      AND estatus<>0 
                      AND file_rool <> 0 
                      AND file_rool<251 
                      AND id not in($idconcetosPercep)";

    $rowid = $db->query( $queryidpercep );
    $numerodearraysat = 0;
    $numerodearray    = 0;
    $ArrayPercep_Clave=array();
    if($rowid){
    $numerodearraysat=mysqli_num_rows($rowid);
    $numerodearray=mysqli_num_rows($rowid);
    
    while ($resul=mysqli_fetch_array($rowid)) {
        $idconce=$resul['nombre_concepto'];
        $ArrayPercep_Concepto[]=$idconce;
        $codigosat=$resul['codigo_sat'];
        $ArrayPercep_TipoPercepcion[]=$codigosat;
        $str = $resul['id'];
        $validaid=strlen($str);
        if($validaid<3){
            $idconcepto=str_pad($resul['id'], 3, "PP", STR_PAD_LEFT); 
        }else{
            $idconcepto='PP'.$resul['id'];
        }
        $ArrayPercep_Clave[] = $idconcepto;
    }
    }

    $queryid="SELECT nombre_concepto,id 
                  FROM conceptos_nomina
                  WHERE tipo = 0 
                  AND  nomina = 1 
                  AND  estatus <> 0
                  AND file_rool<> 0 
                  AND file_rool < 251 
                  AND id not in($idconcetosPercep)";
    
    $rowid = $db->query($queryid);
    $ArrayPercep_ImporteGravado = array();

    if($rowid){
    while ($resul = mysqli_fetch_array($rowid)) {
        $idconce = $resul['id'];
        $queryvalorimporte = "SELECT ROUND(gravado$idconce,2) AS result,ROUND(excento$idconce,2) AS result2 
                              FROM rutinas$ejercicio
                              WHERE id_periodo ='$id_periodo' 
                              AND id_empleado='$id_empleado' 
                              AND fnq_valor = 0;";

        $resultvalorimporte = $db->command( $queryvalorimporte );
        $importevalor = $resultvalorimporte['result']+$resultvalorimporte['result2'];
        $ArrayPercep_ImporteGravado[] = $importevalor;
    }

}
    // ArraysDeducciones.
    $queryidDeduccion = "SELECT nombre_concepto,id,codigo_sat 
                         FROM conceptos_nomina
                         WHERE tipo = 1 
                         AND nomina = 1 
                         AND estatus <> 0 
                         AND file_rool <> 0 
                         AND file_rool < 251 
                         AND id not in ($idconcetosPercep);";

       $rowidDeduccion = $db->query( $queryidDeduccion );
       $ArrayDeduc_Importe       = array();
       $ArrayDeduc_TipoDeduccion = array();
       $ArrayDeduc_Concepto      = array();
       $ArrayDeduc_Clave         = array();
       if($rowidDeduccion){
       while ( $rowresultTipoDe = mysqli_fetch_array( $rowidDeduccion ) ) {
            $idconce           = $rowresultTipoDe['nombre_concepto'];
            $idTipoconceptoDed = $rowresultTipoDe['id'];
            $idconcededuc      = $rowresultTipoDe['id'];
            $str02             = $idTipoconceptoDed;
            $validaidDed       = strlen($str02);
            if( $validaidDed < 3 ){
               $idTipoconceptoDed = str_pad($rowresultTipoDe['id'], 3, "DD", STR_PAD_LEFT); 
            }else{
                $idTipoconceptoDed = 'DD'.$rowresultTipoDe['id'];
            }
   
           if( $idconce == 'ISR' ){
               $idconce = 'IMPUESTO SOBRE LA RENTA';
           }

           $codigosat = $rowresultTipoDe['codigo_sat'];
           $importevalordeduc = substr($codigosat,0,3);
   
           $queryvalorimportededuci = "SELECT  ROUND(total$idconcededuc, 2) AS result 
                                       FROM rutinas$ejercicio
                                       WHERE id_periodo = '$id_periodo' 
                                       AND id_empleado = '$id_empleado' 
                                       AND fnq_valor = 0;";
           
           $resultvalorimportededuci = $db->command( $queryvalorimportededuci );
           $importevaloresdeduc      = $resultvalorimportededuci['result'];
           //echo $importevalordeduc;
           if( $importevaloresdeduc == 0 ){
   
           }else{
            $ArrayDeduc_Importe[]       = $importevaloresdeduc;
            $ArrayDeduc_TipoDeduccion[] = $importevalordeduc;
            $ArrayDeduc_Concepto[]      = $idconce;
            $ArrayDeduc_Clave[]         = $idTipoconceptoDed;
           }
        }

    }
    
    if($usertimbre=='timbres'){
        $condicion='1';
    }else if($usertimbre=='timbres2'){
        $condicion='2';
    }else if($usertimbre=='timbres3'){
        $condicion='3';
    }else if($usertimbre=='timbres4'){
        $condicion='4';
    }else if($usertimbre=='timbres5'){
        $condicion='5';
    }else if($usertimbre=='timbres6'){
        $condicion='6';
    }else if($usertimbre=='timbres7'){
        $condicion='7';
    }else if($usertimbre=='timbres8'){
        $condicion='8';
    }else if($usertimbre=='timbres9'){
        $condicion='9';
    }else if($usertimbre=='timbres10'){
        $condicion='10';
    }else if($usertimbre=='timbres11'){
        $condicion='11';
    }else if($usertimbre=='timbres12'){
        $condicion='12';
    }else if($usertimbre=='timbres13'){
        $condicion='13';
    }else if($usertimbre=='timbres14'){
        $condicion='14';
    }else if($usertimbre=='timbres16'){
        $condicion='16';
    }else if($usertimbre=='timbres17'){
        $condicion='17';
    }else if($usertimbre=='timbres18'){
        $condicion='18';
    }else if($usertimbre=='timbres19'){
        $condicion='19';
    }else if($usertimbre=='timbres20'){
        $condicion='20';
    }else if($usertimbre=='timbres21'){
        $condicion='21';
    }else if($usertimbre=='timbres22'){
        $condicion='22';
    }else if($usertimbre=='timbres23'){
        $condicion='23';
    }else if($usertimbre=='timbres24'){
        $condicion='24';
    }else if($usertimbre=='timbres25'){
        $condicion='25';
    }else if($usertimbre=='timbres26'){
        $condicion='26';
    }else if($usertimbre=='timbres27'){
        $condicion='27';
    }else if($usertimbre=='timbres28'){
        $condicion='28';
    }

    $querydatosKey  = "SELECT * FROM singh.timbrado_credenciales WHERE id = '$condicion'";
    $resultdatoskey = $db->command($querydatosKey);
    $Emisor_RFC     = $resultdatoskey['rfc'];

    $SendaArchsCFDI = $archs_cfdi."/";
    $SendaArchsGraf = $archs_graf."/";

    $NomArchXML   = $resultTim["NomArchXML"];
    $NomArchPDF       = $resultTim["NomArchPDF"];
    $UUID             = $resultTim["folio_fiscal"];
    $selloCFD         = $resultTim["sello_cfdi"];
    $selloSAT         = $resultTim["sello_sat"];
    $Receptor_RFC     = $resultTim["receptor"];
    $noCertificadoSAT = $resultTim["certificado_sat"];
    $noCertificado    = $resultdatoskey['certificado'];

    $Emisor_Regimen        = '603';
    $Receptor_Nom          = $resultTim["nombre"].' '.$resultTim["apaterno"].' '.$resultTim["amaterno"]; 
    $Concept_ClaveProdServ = '84111505';
    $Concept_ClaveUnidad   = 'ACT';
    $Concept_Cantidad      = 1;
    $Concept_Descripcion   = 'Pago de nómina';
    $Concept_ValorUnitario = $TotalPercepAdicional;
    $Concept_Importe       = $TotalPercepAdicional;
    $Concept_Descuento     = $totalrealdeduccion;

    if (file_exists($SendaArchsCFDI.$NomArchXML)) {
        $xml = simplexml_load_file($SendaArchsCFDI.$NomArchXML);
       // print_r($xml);
       $Fact_Fecha = $xml['Fecha'];
    } else {
        $Fact_Fecha = $resultTim["fecha_timbrado"];
    }
    
    $FechaHoraEmision = date("Y-m-d")."T".date("H:i:s"); // Esta fecha se asigna en este punto de manera PROVISIONAL para efectos de demostración 
    $StatusCFDI = "ACTIVO"; // "ACTIVO" o "CANCELADO".
    $k=0;
    
    $Nomina_FechaInicioLaboral = $resultTim['fecha_alta'];
    $Nomina_NoEmpleado         = $resultTim['id'];
    $Nomina_NoImss             = $resultTim['nss'];
    $Nomina_Departamento       = $depto['nombre'];
    $Nomina_CURP               = $resultTim['curp'];
    $Nomina_Puesto             = $puesto['nombre'];
    $fechaaltaempleado         = new DateTime($resultTim['fecha_alta']);

    $queryidfaltas = "SELECT id 
                      FROM $base.conceptos_nomina 
                      WHERE nombre_concepto = 'Faltas' 
                      AND estatus = 1";

    $rowidfaltas  = $db->query($queryidfaltas);
    $validafaltas = 0;
    $faltasvalor  = 0;
    if($rowidfaltas){
        $validafaltas = mysqli_num_rows($rowidfaltas);
    }
    if( $validafaltas > 0 ){
        $rowresultfaltas = mysqli_fetch_array($rowidfaltas);
        $idconcepFaltas = $rowresultfaltas['id'];
        
        $queryvalorfaltas = "SELECT Total$idconcepFaltas as valorfalta 
                             FROM $base.rutinas$ejercicio
                             WHERE id_periodo = '$id_periodo'
                             AND id_empleado = '$id_empleado' 
                             AND fnq_valor = 0;";
        
        $resultvalorfaltas = $db->command($queryvalorfaltas);
        $faltasvalor = $resultvalorfaltas['valorfalta'];
    }

    if( $fechaaltaempleado > $fechainicialPeriodo){
        $diaspagados = date_diff($fechafinalPeriodo,$fechaaltaempleado);
    }else{
        $diaspagados = date_diff($fechafinalPeriodo,$fechainicialPeriodo);
    }
    @$diasapagar = ceil ( ( $diaspagados->format('%d') + 1 ) - $Incapacidadesvalor - $faltasvalor);
    $Nomina_NumDiasPagados = $diasapagar;

    $CadOri = "||".$UUID."|".$Fact_Fecha."|".$selloCFD."|".$noCertificado."||";    
    #== 3. Crear archivo .PNG con codigo bidimensional =================================
    //TODO: checar el png
    $archs_backup_qr = "../../../storage/app/public/timbrado/bck_qr/";
    $repoUser = "../../../storage/app/public/repositorio/".$repo. "/". $id_empleado ."/timbrado/archs_qr";

    $filename = $repoUser . "/".$UUID.".png";
    
    $filename_bck = $archs_backup_qr . "/Img_".$UUID.".png";
    if(! file_exists($filename)){
        var_dump($filename);
    echo "<br>";    
    exit;
        $filename = $filename_bck;
    
    }

    $CadImpTot = ProcesImpTot($total);
    $cadenaSello= substr($selloCFD,-8);
    
    // $archs_graf."/FondoTenue.jpg";

    /***********Inicio de hoja****************/
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('arial','B',12);
    $pdf->SetXY($X+1,$Y+1.65+0.1);
    $pdf->image("../../../public/img/FondoTenue.jpg",$X+1, $Y+8.6 , 19.5, 18);
    
    $pdf->SetTextColor(7,100,30);
    $pdf->SetFont('arial','B',12);
    $pdf->SetXY($X+10.5,$Y+1.25+0.1);
    $pdf->Cell(1.5, 0.25, utf8_decode("RECIBO DE NÓMINA."), 0, 1,'L', 0);
    
    $pdf->SetTextColor(199,199,199);
    $pdf->SetFont('arial','BI',9);
    $pdf->SetXY($X+18.7,$Y+6.7);
    $pdf->Cell(2, 0.25, utf8_decode("CFDI 3.3"), 0, 1,'L', 0);        
        
    $pdf->SetTextColor(0,0,0);
    //$pdf->SetTextColor(171,17,17);
    //$pdf->SetFont('arial','',18);
    //$pdf->SetXY($X+18.9,$Y+1.5);
    //$pdf->Cell(1, 0.25, $NoRec, 0, 1,'R', 0);
        
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

    if( $Nomina_TotalDeducciones > 0 ){
        $pdf->SetXY($X+13.3,$Y+11.32+(0.55*9));
        $pdf->Cell(3.7, 0.25, utf8_decode("Total de deducciones:"), 0, 1,'L', 0);
    }

    if( $Nomina_TotalOtrosPagos > 0 ){
        $pdf->SetXY($X+13.3,$Y+11.32+(0.55*10));
        $pdf->Cell(3.7, 0.25, utf8_decode("Otros pagos:"), 0, 1,'L', 0);
    }  
    
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
    
    if($Nomina_TotalDeducciones>0){
        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*9));
        $pdf->Cell(2, 0.25, number_format($Nomina_TotalDeducciones,2), 0, 1,'R', 0);
    }

    if($Nomina_TotalOtrosPagos>0){
        $pdf->SetXY($X+18.3,$Y+11.32+(0.55*10));
        $pdf->Cell(2, 0.25, $Nomina_TotalOtrosPagos, 0, 1,'R', 0);
    }       

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
    $TotRegs = count($ArrayDeduc_TipoDeduccion);
    $SumaDeduc = 0;
    for ($i=0; $i<$TotRegs; $i++){
        $SumaDeduc = $SumaDeduc + $ArrayDeduc_Importe[$i];
    }
    
    if($SumaDeduc==0){
    }else{
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
    }

    // Conceptos de otros pagos ====================================================
    if($importevalorisr<0){
        $totalOtrosPagos=0;

        $ArrayOtrosPag_TipoOtroPago  = ['002'];
        $ArrayOtrosPag_Clave         = ['D002'];
        $ArrayOtrosPag_Concepto      = ['SUBSIDIO PARA EL EMPLEO'];

        $ArrayOtrosPag_Importe       = [number_format($importeotrospagos,2,'.','')];
    
        for ($i=0; $i<count($ArrayOtrosPag_Importe); $i++){
            $totalOtrosPagos = $totalOtrosPagos + $ArrayOtrosPag_Importe[$i];
        }

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

    //==============================================================================
    DatosInf($pdf, $filename, 0, $selloCFD, $selloSAT, $CadOri);
    VerifStatusCFDI($pdf, $StatusCFDI);
    $queryborrartable="DROP table $base.tabletempo";
    $rowborratable=$db->query($queryborrartable);
}
/*
$namePDF=$SendaArchsCFDI.'PDF_Masivo_Pe_'.$idperiodo.'.pdf';
$NomArchPDF='PDF_Masivo_Pe_'.$idperiodo.'.pdf';


$repositorio=$rowresultresulbase['Repositorio'];

$verifCar=$_SESSION['dir'].$repositorio.'/';
$verificaRepo=str_replace("/","\ ", $verifCar);
$veriCarpeta=str_replace(" ","", $verificaRepo);

$carpeta = $veriCarpeta;
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$fiche=$archs_cfdi.'/'.$NomArchPDF;

$fichero=str_replace(" ", "", $fiche);

$archi=$_SESSION['dir'].$repositorio;

$archivo=$archi.'/'.$NomArchPDF;

$nuevo_fichero=str_replace(" ","", $archivo);

*/

$pdf->Output($url_pdf, 'F'); // Se graba el documento .PDF en el disco duro o unidad de estado sólido.
chmod ($url_pdf,0777);  // <-- Descomentar si está utilizando el sistema operativo LINUX.
$pdf->Output($url_pdf, 'I');
exit();




function ProcesImpTot($ImpTot){
    $ImpTot = number_format($ImpTot, 4);
    $ArrayImpTot=array();
    $ArrayImpTot = explode(".", $ImpTot);
    $NumEnt = $ArrayImpTot[0];
    $NumDec = ProcesDecFac($ArrayImpTot[1]);
    return $NumEnt.".".$NumDec;
}

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


function DatosInf($pdf, $filename, $Y, $selloCFD, $selloSAT, $CadOri){
    
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


function InvertirFecha($Fecha){
    $ArrayDatos=array();
    $ArrayDatos = explode("-",$Fecha); 
        
    $NvaFec = $ArrayDatos[2]."/".$ArrayDatos[1]."/".$ArrayDatos[0];
    
    return $NvaFec;
}

?>


