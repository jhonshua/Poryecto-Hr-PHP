<?php
session_start();
include 'conexion.php';


if (class_exists('Database')) {
    if(!isset($_SESSION['db'])) $baseD='maestra';  else $baseD=$_SESSION['db'];
    $db = new Database($baseD);
}

if(isset($_SESSION['email'])){
    $Emailactivo=$_SESSION['email'];
    $email=$_SESSION['email'];
    $queryid="SELECT id FROM usuarios WHERE email='$Emailactivo'";
    $rowresultresultid=$db->command($queryid);
    $resultid=$rowresultresultid['idUsuario'];

    $sqli="SELECT repositorioCFDI,repositorioGRAF,repositorio,nmbRepo FROM maestra.usuarios INNER JOIN maestra.bases ON empresa=Base WHERE Email='$email'; ";
    list($archs_cfdi,$archs_graf,$nmbEmp,$pdfRepo)=$db->command($sqli);
    //$NmbRepo='../Repositorio'.$nmbEmp.'/';
    $_SESSION['dir']=$nmbEmp;
    $_SESSION['pdf']=$pdfRepo;
    $rutapdfr='../pdf/web/'.$pdfRepo.'/';

    if($email=='desarrollo@singh.com.mx' || $email=='desarrollo2@singh.com.mx' || $email=='desarrollo3@singh.com.mx'){
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }

}

if(isset($_SESSION['user'])){
    $email=$_SESSION['user'];

    $sql="SELECT empresa FROM empleado_login WHERE email='$email' AND estatus = 1";
    list($Empresa)=$db->command($sql);

    $queryBase="SELECT * FROM empresas WHERE razon_social='$Empresa'";
    $rowresultresulbase=$db->command($queryBase);
    $base=$rowresultresulbase['Base'];
    $_SESSION['base']=$base;

    // $sqli="SELECT repositorioCFDI,repositorioGRAF,repositorio FROM maestra.empleado INNER JOIN maestra.bases ON empresa=Base WHERE Email='$email'; ";
    // list($archs_cfdi,$archs_graf,$nmbEmp)=$db->command($sqli);
    // $_SESSION['dir']=$nmbEmp;

    if($email=='desarrollo@singh.com.mx' || $email=='desarrollo2@singh.com.mx' || $email=='desarrollo3@singh.com.mx'){
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }

}


if(isset($_SESSION['empresa'])){
    $Empresa=$_SESSION['empresa'];
    $empresa=$_SESSION['empresa'];
    $queryBase="SELECT * FROM empresas WHERE RazonSocial='$Empresa'";
    $rowresultresulbase=$db->command($queryBase);
    $base=$rowresultresulbase['Base'];
    $_SESSION['base']=$base;
    $IdEmpresaSession=$rowresultresulbase['idEmpresa'];

    if(isset($_SESSION['email'])){
        $queryAsignacionDepa="SELECT * from userempresas where idUsuario='$resultid' and idEmpresa='$IdEmpresaSession' and status=0";
    //echo $queryAsignacionDepa;
    $resulAsignacionDepa=$db->command($queryAsignacionDepa);
    $cadenaDepasAsignados=$resulAsignacionDepa['Departamentos'];
    if($cadenaDepasAsignados==NULL){
        $cadenaDepasAsignados="''";
    }

    }

}

$FecHr = date('Y/m/d H:i:s');
$FecHr2 = date('d/M/Y');

if(isset($_SESSION['email'])){
    $queryvalidacion="SELECT * FROM permisos WHERE idUsuario='$resultid'";
    $rowresultresultvalida=$db->command($queryvalidacion);
}

//echo $base;
?>
