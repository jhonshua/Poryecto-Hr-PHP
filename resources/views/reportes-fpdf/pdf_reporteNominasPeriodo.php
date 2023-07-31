<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

include '../../../public/lib/fpdf/fpdf.php';
include '../../../public/lib/qrlib/qrlib.php';
include 'conexion.php';
include 'numerosletras.php';

set_time_limit(0);
ini_set("memory_limit", "-1");

$empresa = base64_decode($_GET["cia"]);
$id_deptos = base64_decode($_GET["depto"]);
$periodo_nomina = base64_decode($_GET["per"]);

$db = new Database($empresa);

$FecHr = date('Y-m-d H:i:s');

class PDF extends FPDF
{
    public function Header()
    {

    }

    public function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function SetWidths($w){
        $this->widths = $w;
    }
    
    function SetAligns($a){
        $this->aligns = $a;
    }
    
    function Row($data,$bandera){
            $nb=0;
            for($i=0;$i<count($data);$i++)
                    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
                    $h=4*$nb;
                    $this->CheckPageBreak($h);
                    if($bandera==true) $rellenar ='FD';
                    if($bandera==false) $rellenar ='D';
                    else  $rellenar=$bandera;
            for($i=0;$i<count($data);$i++){
                    $w=$this->widths[$i];
                    $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                    $x=$this->GetX();
                    $y=$this->GetY();
                    $this->Rect($x,$y,$w,$h,$rellenar);
                    $this->MultiCell($w,4,$data[$i],0,$a);
                    $this->SetXY($x+$w,$y);
            }
            $this->Ln($h);
            $bandera=!$bandera;
    }

    function CheckPageBreak($h){
            if($this->GetY()+$h>$this->PageBreakTrigger)
                    $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt){
            $cw=&$this->CurrentFont['cw'];
            if($w==0)
                    $w=$this->w-$this->rMargin-$this->x;
            $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
            $s=str_replace("\r",'',$txt);
            $nb=strlen($s);
            if($nb>0 and $s[$nb-1]=="\n")
                    $nb--;
            $sep=-1;
            $i=0;
            $j=0;
            $l=0;
            $nl=1;
            while($i<$nb){
                    $c=$s[$i];
                    if($c=="\n"){
                            $i++;
                            $sep=-1;
                            $j=$i;
                            $l=0;
                            $nl++;
                            continue;
                    }
                    if($c==' ')
                            $sep=$i;
                    $l+=$cw[$c];
                    if($l>$wmax){
                            if($sep==-1){
                                    if($i==$j)
                                            $i++;
                            }
                            else
                                    $i=$sep+1;
                            $sep=-1;
                            $j=$i;
                            $l=0;
                            $nl++;
                    }
                    else
                            $i++;
            }
            return $nl;
    }
}

mb_internal_encoding("UTF-8");
    
// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$array_empleados=array();

$query_fecha_periodo = "SELECT concat('Del ',DATE_FORMAT(p.fecha_inicial_periodo,'%d/%M/%Y'),' Al ', DATE_FORMAT(p.fecha_final_periodo,'%d/%M/%Y') ) as fecha_periodo,
    p.nombre_periodo, p.numero_periodo, p.fecha_final_periodo, p.fecha_inicial_periodo
    from ".$empresa.".periodos_nomina p where p.id=".$periodo_nomina." and estatus<>0";
$datos_fecha_periodo = $db->command($query_fecha_periodo);
$tipo_nomina = $datos_fecha_periodo['nombre_periodo'];
$numero_periodo = $datos_fecha_periodo['numero_periodo'];
$fecha_inicial_periodo = $datos_fecha_periodo['fecha_inicial_periodo'];
$fecha_final_periodo = $datos_fecha_periodo['fecha_final_periodo'];
$fecha_periodo = $datos_fecha_periodo['fecha_periodo'];

$fecha_inicio = new \DateTime($fecha_inicial_periodo);
$fecha_final = new \DateTime($fecha_final_periodo);
// $fecha_final = new \DateTime('2021-05-29');
$fecha_dia = date_format($fecha_final, 'd');
$fecha_mes = date_format($fecha_final, 'm');
$fecha_anio = date_format($fecha_final, 'Y');
if($fecha_dia == '28' || $fecha_dia == 29 || $fecha_dia == 31){
    $fecha_final = new \DateTime($fecha_anio.'-'.$fecha_mes.'-30');
}

$diff = $fecha_inicio->diff($fecha_final); 
$dias_dif = ($diff->days)+1;

$query_periodo_nomina = "SELECT id, ejercicio, fecha_pago from ".$empresa.".periodos_nomina where id=".$periodo_nomina." and estatus<>0 and activo in (1,2);";
$datos_periodo_nomina = $db->command($query_periodo_nomina);
$ejercicio = $datos_periodo_nomina['ejercicio'];
$fecha_pago = $datos_periodo_nomina['fecha_pago'];

$query_estructura_tabla = "SELECT COLUMN_NAME AS columna FROM information_schema.columns WHERE table_schema = '".$empresa."' AND table_name = 'rutinas".$ejercicio."';";
$datos_estructura_tabla = $db->query($query_estructura_tabla);

$array_estructura_tabla = array(); 
while ($result_estructura_tabla = mysqli_fetch_array($datos_estructura_tabla)){
    $array_estructura_tabla[] = $result_estructura_tabla['columna'];
}

$query_empleados = "SELECT e.id,
    (SELECT id from ".$empresa.".conceptos_nomina where nombre_concepto='Faltas' and estatus<>0) id_falta
    from ".$empresa.".empleados e where e.estatus=1 and e.tipo_de_nomina='".$tipo_nomina."' 
    and e.tipo_fiscal=1 and e.id in 
    (SELECT id_empleado from ".$empresa.".rutinas".$ejercicio." where id_periodo=".$periodo_nomina." and fnq_valor=0)
    union
    SELECT e.id,
    (SELECT id from ".$empresa.".conceptos_nomina where nombre_concepto='Faltas' and estatus<>0) id_falta
    from ".$empresa.".empleados e where e.estatus in (0,20) and e.tipo_de_nomina='".$tipo_nomina."' 
    and e.fecha_baja <> '0000-00-00' and e.fecha_baja >= '".$fecha_inicial_periodo."' and e.tipo_fiscal=1 
    and e.id in (SELECT id_empleado from ".$empresa.".rutinas".$ejercicio." where id_periodo='".$periodo_nomina."' and fnq_valor=0); ";
$datos_empleados = $db->query($query_empleados);
$id_falta = null;

while ($result_empleados = mysqli_fetch_array($datos_empleados)) {
    $array_empleados[] = $result_empleados['id']; 
    $id_falta = $result_empleados['id_falta']; 
}

$id_empleados = join(',', $array_empleados);

$query_conceptos = "SELECT cn.nombre_concepto, cn.id, cn.nombre_corto, cn.rutinas, cn.tipo from ".$empresa.".conceptos_nomina cn
    where cn.nomina=1 and cn.estatus<>0 and (cn.file_rool<'250' and cn.file_rool<>0) order by cn.tipo;";
$datos_conceptos = $db->query($query_conceptos);

$numrow = mysqli_num_rows($datos_conceptos);
$array_conceptos = array();
$array_campos_validados = array();
$campos_total = "";

while ($result_conceptos = mysqli_fetch_array($datos_conceptos)){
    $campo_t = 'total'.$result_conceptos['id'];
    $array_conceptos[] = $result_conceptos;
    if (in_array($campo_t, $array_estructura_tabla)){
        // echo 'total29';
        $campos_total .= 'ifnull(cast(r.'.$campo_t.' as signed), 0) as '.$campo_t.',';
        $array_campos_validados[] = $campo_t;
        // $array_conceptos[] = $result_conceptos;
    } 
    
}
// print_r($array_campos_validados); exit;

/*$query_datos_emisora = "SELECT e.id, c.nombre, ee.razon_social, group_concat(e.id) as id_empleados, ee.id as id_empresa_emisoras, rp.num_registro_patronal, ee.rfc 
    from ".$empresa.".empleados e join ".$empresa.".categorias c on  e.id_categoria=c.id 
    inner join singh.registro_patronal rp on c.tipo_clase=rp.id inner join singh.empresas_emisoras ee on rp.id_empresa_emisora=ee.id 
    where c.estatus=1 and ee.estatus=1 and rp.estatus=1 and e.id in (".$id_empleados.") and e.id_departamento in (".$id_deptos.") group by ee.razon_social;";
$datos_emisora = $db->query($query_datos_emisora);*/

if($id_falta<>null){
   $query_datos_emisora = "SELECT e.id, concat(e.nombre,' ',e.apaterno) as nombre_completo, c.nombre categoria, LEFT(c.nombre, 16) categoria_16P, 
    (select nombre from ".$empresa.".departamentos where id=e.id_departamento) departamento, ".$campos_total."
    ifnull(cast(r.valor".$id_falta." as signed), 0) faltas, ifnull(cast(r.total".$id_falta." as signed), 0) total_percepcion, ifnull(r.total_percepcion_fiscal, 0) total_percepcion_fiscal,
    ifnull(r.total_deduccion_fiscal, 0) total_deduccion_fiscal, ifnull(r.neto_fiscal, 0) neto_fiscal, ifnull(r.total_gravado, 0) total_gravado, ifnull(cast(r.incapacidades as signed), 0) incapacidades,
    e.salario_diario, e.salario_diario_integrado, e.fecha_alta, ee.razon_social, ee.id as id_empresa_emisoras, rp.num_registro_patronal, ee.rfc
    from ".$empresa.".empleados e join ".$empresa.".categorias c on e.id_categoria=c.id 
    inner join singh.registro_patronal rp on c.tipo_clase=rp.id 
    inner join singh.empresas_emisoras ee on rp.id_empresa_emisora=ee.id 
    inner join ".$empresa.".rutinas".$ejercicio." r on r.id_empleado=e.id
    where c.estatus=1 and ee.estatus=1 and rp.estatus=1 and e.id in (".$id_empleados.") 
    and e.id_departamento in (".$id_deptos.") and r.id_periodo=".$periodo_nomina." and r.fnq_valor=0 order by ee.razon_social, e.id;"; 
}else{
    $query_datos_emisora = "SELECT e.id, concat(e.nombre,' ',e.apaterno) as nombre_completo, c.nombre categoria, LEFT(c.nombre, 16) categoria_16P, 
    (select nombre from ".$empresa.".departamentos where id=e.id_departamento) departamento, ".$campos_total." ifnull(r.total_percepcion_fiscal, 0) total_percepcion_fiscal,
    ifnull(r.total_deduccion_fiscal, 0) total_deduccion_fiscal, ifnull(r.neto_fiscal, 0) neto_fiscal, ifnull(r.total_gravado, 0) total_gravado, ifnull(cast(r.incapacidades as signed), 0) incapacidades,
    e.salario_diario, e.salario_diario_integrado, e.fecha_alta, ee.razon_social, ee.id as id_empresa_emisoras, rp.num_registro_patronal, ee.rfc
    from ".$empresa.".empleados e join ".$empresa.".categorias c on e.id_categoria=c.id 
    inner join singh.registro_patronal rp on c.tipo_clase=rp.id 
    inner join singh.empresas_emisoras ee on rp.id_empresa_emisora=ee.id 
    inner join ".$empresa.".rutinas".$ejercicio." r on r.id_empleado=e.id
    where c.estatus=1 and ee.estatus=1 and rp.estatus=1 and e.id in (".$id_empleados.") 
    and e.id_departamento in (".$id_deptos.") and r.id_periodo=".$periodo_nomina." and r.fnq_valor=0 order by ee.razon_social, e.id;";
}


$datos_emisora = $db->query($query_datos_emisora);
 //echo $query_datos_emisora; exit;

$array_emisora = array();
$array_detalle_emisora = array();

while ($result_emisora = mysqli_fetch_array($datos_emisora)){
    // print_r($result_emisora); echo '<br>';
    $array_detalle_emisora[] = $result_emisora;
}

foreach($array_detalle_emisora as $key => $dato){
    // echo $dato['id'], '<br>';
    $id_emisora = $dato['id_empresa_emisoras'];
    if (!array_key_exists($id_emisora, $array_emisora)) {
        $array_emisora[$id_emisora] = array(
           'id_empresa_emisora' => $dato['id_empresa_emisoras'],
           'razon_social' => $dato['razon_social'],
           'num_registro_patronal' => $dato['num_registro_patronal'],
           'rfc' => $dato['rfc']
        ); 
    }
}

 //print_r($array_detalle_emisora); exit;

foreach($array_emisora as $key => $valor){  
    $id_emisora_empleado = $valor['id_empresa_emisora'];
    $pdf->AddPage();
    #Establecemos los márgenes izquierda, arriba y derecha:
    $pdf->SetMargins(5, 10 , 5);
    #Establecemos el margen inferior:
    $pdf->SetAutoPageBreak(true,25);

    //------------------------------------------------------------------------------------
    
    $pdf->SetFont('Arial','B',15);
    $pdf->Ln();
    $pdf->Ln();

    $emisora=utf8_decode($valor['razon_social']);
    $pdf->Cell(200,5,$emisora,0,0,'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $FechaPago=utf8_decode($fecha_pago);
    $pdf->Cell(45,5,$FechaPago,0,0,'L');
    $pdf->SetFont('Arial','B',10);
    $registropa=utf8_decode($valor['num_registro_patronal']);
    $pdf->Cell(32,5,'Registro Patronal:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(80,5, $registropa, 0,0,'L');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,5,'RFC:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $rfc=utf8_decode($valor['rfc']);
    $pdf->Cell(30,5, $rfc, 0,0,'R');
    $pdf->Ln();

    $pdf->Ln();
    $pdf->Ln();    
    
    $pdf->SetFont('Arial','B',17);
    $titulo=utf8_decode('Reporte de la Nómina');
    $pdf->Cell(200,5,$titulo,0,0,'C');
    
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $search  = array('January', 'February', 'March', 'April', 'May','June','July','August','September','October','November','December');
    $replace = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    $subject = $fecha_periodo;
    $periodo=str_replace($search, $replace, $subject);
    $periodo=utf8_decode($periodo);
    $pdf->Cell(30,5,'Periodo de pago:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$periodo,0,0,'L');

    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,5,'Tipo de Nomina:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$tipo_nomina,0,0,'L');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(15,5,'Clave:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$numero_periodo,0,0,'L');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(200,4,'______________________________________________________________________________________________________',0,0,'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(10,4,'#');
    $pdf->Cell(40,4,'Nombre');
    $pdf->Cell(25,4,'Categoria');
    $pdf->Cell(30,4,'Departamento');
    $pdf->Cell(20,4,'Sal Diario');
    $pdf->Cell(27,4,'Sal Diario Int');
    $pdf->Cell(25,4,'Dias Periodo');
    $pdf->Cell(25,4,'Dias Pagados');
    $pdf->Ln();

    $col1=25; $col2=25; $col3=25; $col4=25; $col5=25; $col6=25; $col7=25; $col8=25;
    $pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5,$col6,$col7,$col8));
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetAligns(array('L','L','L','L','L','L','L','L'));
    $temp = array();  

    // $pdf->Row($nombre_corto,'F');    
    foreach($array_conceptos as $key => $concepto){
        $nombre_corto=strtoupper($concepto['nombre_corto']);
        // $rutinas = $result_conceptos01['rutinas'];
        // echo $nombre_corto, '<br>';
        // $pdf->Row(array($nombre_corto),'F');
        if($concepto['tipo'] <> 3){

            switch($key){
                case 0:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 1:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 2:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 3:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 4:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 5:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 6:
                    $pdf->Cell(25,4, utf8_decode($nombre_corto));
                    break;
                case 7:
                    $pdf->Cell(20,4, utf8_decode($nombre_corto));
                    break;                
            }            
            
            if($key >= 7){
                break;
            }
        }        
        
    }

    $pdf->Ln();
    $pdf->Cell(20,4,'Subsidio');
    $pdf->Cell(20,4,'Total Percep');
    $pdf->Cell(20,4,'Total Deducc');
    $pdf->Cell(20,4,'Neto');
    $pdf->Ln(2);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(200,4,'______________________________________________________________________________________________________',0,0,'C');
    $pdf->Ln();
    $pdf->Ln();
    
    foreach($array_detalle_emisora as $key => $registro){
        if($id_emisora_empleado == $registro['id_empresa_emisoras']){            
            $idempleado = $registro['id'];
            $nombre_empleado = $registro['nombre_completo'];
            $categoria = $registro['categoria_16P'];
            $departamento = $registro['departamento'];
            $salario_diario = round($registro['salario_diario'], 2);
            $salario_diario_integrado = round($registro['salario_diario_integrado'], 2);
            $fecha_alta = $registro['fecha_alta'];
            $incapacidades = $registro['incapacidades'];  
            $faltas = $registro['faltas'];  

            $total_percepcion_fiscal = floatval($registro['total_percepcion_fiscal']);
            $total_deduccion_fiscal = floatval($registro['total_deduccion_fiscal']);
            $neto_fiscal = floatval($registro['neto_fiscal']);
            
            $pdf->SetFont('Arial','B',6);     
            $dias_pagados = 0;

            $fecha_alta2 = new \DateTime($fecha_alta);
            // $fecha_final2 = new \DateTime($fecha_final_periodo);
            $diff2 = $fecha_alta2->diff($fecha_final); 
            $dias_nom_dif = ($diff2->days)+1;

            if($fecha_alta > $fecha_inicial_periodo){
                $dias_pagados = floatval($dias_nom_dif) - floatval($incapacidades) - floatval($faltas);
            }else{
                $dias_pagados = floatval($dias_dif) - floatval($incapacidades) - floatval($faltas);
            }

            $pdf -> Line(5, $pdf->GetY(), 205, $pdf->GetY());
            $pdf->Row(array($idempleado, utf8_decode($nombre_empleado), utf8_decode($categoria), utf8_decode($departamento), $salario_diario, $salario_diario_integrado, $dias_dif, $dias_pagados),'F');

            $total_subsidio = 0;
            foreach($array_conceptos as $key => $reg_concepto_percepcion){ 
                $total_percepcion = 0;  
                $campo_total = 'total'.$reg_concepto_percepcion['id'];
                if (in_array($campo_total, $array_campos_validados)){
                    if($reg_concepto_percepcion['tipo'] <> 3){ 
                        
                        $total_percepcion = $registro[$campo_total]; 
        
                        if($reg_concepto_percepcion['rutinas'] == 'ISR'){
                            $total_subsidio = floatval($registro[$campo_total]); 
                            if($total_percepcion <= 0){
                                $total_percepcion = 0;
                            }                  
                        }
                                    
                        $pdf->SetFont('Arial','B',6);
                        $pdf->Cell(25,4,'$'.number_format(floatval($total_percepcion), 2, '.', ','));                
                        if($key >= 7){
                            break;
                        }
                    }
                }else{
                    $pdf->SetFont('Arial','B',6);
                    $pdf->Cell(25,4,'$'.number_format( 0, 2, '.', ','));
                }         
                
            }
            $pdf->Ln();

            $pdf->Cell(25,4,'$'.number_format($total_subsidio, 2, '.', ','));
            $pdf->Cell(25,4,'$'.number_format($total_percepcion_fiscal, 2, '.', ','));
            $pdf->Cell(25,4,'$'.number_format($total_deduccion_fiscal, 2, '.', ','));
            $pdf->Cell(25,4,'$'.number_format($neto_fiscal, 2, '.', ','));
            $pdf->Ln();
        }
        
    }
    
}

// ---------------------------------------------------------------

foreach($array_emisora as $key => $valor2){
    $id_emisora_total = $valor2['id_empresa_emisora'];
    $pdf->AddPage();
    
    $pdf->SetFont('Arial','B',15);
    
    $emisora=utf8_decode($valor2['razon_social']);
    $pdf->Cell(200,5,$emisora,0,0,'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $FechaPago=utf8_decode($fecha_pago);
    $pdf->Cell(170,5,$FechaPago,0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,5,$FecHr,0,0,'R');
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $rfc=utf8_decode($valor2['rfc']);
    $pdf->Cell(10,5,'RFC:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$rfc,0,0,'L');
    
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $registropa=utf8_decode($valor2['num_registro_patronal']);
    $pdf->Cell(33,5,'Registro Patronal:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$registropa,0,0,'L');
    //
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial','B',15);
    $titulo=utf8_decode('Reporte de la Nómina');
    $pdf->Cell(200,5,$titulo,0,0,'C');
    
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial','B',10);
    $periodo=utf8_decode($periodo);
    $pdf->Cell(30,5,'Periodo de pago:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$periodo,0,0,'L');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,5,'Tipo de Nomina:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$tipo_nomina,0,0,'L');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(15,5,'Clave:',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(100,5,$numero_periodo,0,0,'L');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','B',15);
    $titulo=utf8_decode('PERCEPCIONES');
    $pdf->Cell(200,5,$titulo,0,0,'C');
    
    
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','',10);

    $suma_total_percepcion = 0;
    foreach($array_conceptos as $key => $conceptoP){    
        $campo_total = 'total'.$conceptoP['id'];  
        $nombre_concepto = $conceptoP['nombre_concepto'];   
        if (in_array($campo_total, $array_campos_validados)){
            if($conceptoP['tipo'] == 0){
                // echo $concepto2['nombre_concepto'], '<br>';                
                $c_total = 0;
                
                foreach($array_detalle_emisora as $key => $reg){
                    // echo $campo_total, '--',$reg[$campo_total], '<br>';
                    if($id_emisora_total == $reg['id_empresa_emisoras']){
                        $c_total = $c_total + floatval($reg[$campo_total]);
                    }    
                                 
                }
                // echo $c_total, '<br>';
                $suma_total_percepcion = $suma_total_percepcion + $c_total;
                if($c_total <> 0){
                    $pdf->Cell(50,5,'',0,0,'L');
                    $pdf->Cell(80,5, utf8_decode($nombre_concepto),0,0,'L');
                    $pdf->Cell(30,5, number_format($c_total , 2, '.', ','));
                    $pdf->Ln(); 
                }
            }
        }       
        
    }
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(125,5,'',0,0,'C');
    $pdf->Cell(30,5,'____________');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(130,5,'Total de Percepciones:',0,0,'R');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,5,'$'.number_format($suma_total_percepcion, 2, '.', ','));

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','B',15);
    $tituloD=utf8_decode('DEDUCIONES');
    $pdf->Cell(200,5,$tituloD,0,0,'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','',10);

    $suma_total_deduccion = 0;    
    $c_total_menor0 = 0;
    foreach($array_conceptos as $key => $conceptoD){  
        $campo_total = 'total'.$conceptoD['id'];     
        $nombre_concepto = $conceptoD['nombre_concepto'];  
        $c_total = 0;       

        if (in_array($campo_total, $array_campos_validados)){
            if($conceptoD['tipo'] == 1){            
                // echo $concepto2['nombre_concepto'], '<br>';
                foreach($array_detalle_emisora as $key => $reg2){
                    // echo $campo_total, '--',$reg[$campo_total], '--', $conceptoD['rutinas'], '<br>';
                    if($id_emisora_total == $reg2['id_empresa_emisoras']){
                        if($conceptoD['rutinas'] == 'ISR' && $reg2[$campo_total] > 0){
                            // echo $campo_total, '--',$reg[$campo_total], '--', $conceptoD['rutinas'], '<br>';
                            $c_total = $c_total + floatval($reg2[$campo_total]); 
                        }else if($conceptoD['rutinas'] == 'ISR'){
                            $c_total_menor0 = $c_total_menor0 + floatval($reg2[$campo_total]);
                        }else{
                            $c_total = $c_total + floatval($reg2[$campo_total]);
                        }  
                        
                    }     
                }          
            
                
                
            }

            if($c_total <> 0){
                $pdf->Cell(50,5,'',0,0,'L');
                $pdf->Cell(80,5, utf8_decode($nombre_concepto),0,0,'L');
                $pdf->Cell(30,5, number_format($c_total , 2, '.', ','));
                $pdf->Ln(); 
                $suma_total_deduccion = $suma_total_deduccion + $c_total;
            }
            
        }                 
        
    }

    $pdf->Cell(50,5,'',0,0,'L');
    $pdf->Cell(80,5,'SUBSIDIO PARA EL EMPLEO',0,0,'L');
    $pdf->Cell(30,5,'$'.number_format($c_total_menor0, 2, '.', ','));
    $pdf->Ln();


    $suma_total_efectivo = 0;
    $suma_neto_pagado = 0;
    $suma_total_gravado = 0;
    foreach($array_detalle_emisora as $key => $reg_suma_total){   
        if($id_emisora_total == $reg_suma_total['id_empresa_emisoras']){         
            $suma_total_efectivo = $suma_total_efectivo + floatval($reg_suma_total['neto_fiscal']);
            $suma_neto_pagado = $suma_neto_pagado + floatval($reg_suma_total['neto_fiscal']);
            $suma_total_gravado = $suma_total_gravado + floatval($reg_suma_total['total_gravado']);  
        }      
    }

    $suma_total_deduccion = $suma_total_deduccion + $c_total_menor0;

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(125,5,'',0,0,'C');
    $pdf->Cell(30,5,'____________');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(130,5,'Total de Deducciones:',0,0,'R');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,5,'$'.number_format($suma_total_deduccion, 2, '.', ','));

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $tituloTotal=utf8_decode('TOTALES');
    $pdf->Cell(200,5,$tituloTotal,0,0,'C');

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50,5,'',0,0,'L');
    $pdf->Cell(75,5,'TOTAL EN EFECTIVO',0,0,'L');
    $pdf->SetFont('Arial','',10);

    $pdf->Cell(30,5,'$'.number_format($suma_total_efectivo, 2, '.', ','));
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50,5,'',0,0,'L');
    $pdf->Cell(75,5,'NETO PAGADO',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,5,'$'.number_format($suma_neto_pagado, 2, '.', ','));
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50,5,'',0,0,'L');
    $pdf->Cell(75,5,'Total gravable',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->GetX();
    $pdf->Cell(30,5,'$'.number_format($suma_total_gravado, 2, '.', ','));

}


$pdf->Output('reporteNomina_periodo.pdf','I');
?>