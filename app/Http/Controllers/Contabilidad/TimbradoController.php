<?php

namespace App\Http\Controllers\contabilidad;


use App\Http\Controllers\Controller;
use App\Models\TimbradoCancelacionesFacturador;
use App\Models\Departamento;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Puesto;
use App\Models\TimbradoCredenciales;
use Barryvdh\DomPDF\Facade\Pdf;
use CfdiUtils\CadenaOrigen\DOMBuilder;
use CfdiUtils\Cfdi;
use CfdiUtils\XmlResolver\XmlResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\PeriodosNomina;
use App\Models\EmpresaEmisora;
use App\Models\TimbradoEmpleado;
use App\Models\TimbradoAsimilados;
use App\Models\TimbradoCancelacionesEmpleado;
use App\Models\Empleado;
use App\Mail\NuevoReciboNominaEmail;
use App\Exports\ResumenCFDIExport;
use Storage;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use App\Models\TimbradoFacturador;
use Maatwebsite\Excel\Facades\Excel;

class TimbradoController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    private $array_jornada = array(
        '01' => 'DIURNA',
        '02' => 'NOCTURNA',
        '03' => 'MIXTA',
        '04' => 'POR HORA',
        '05' => 'REDUCIDA',
        '06' => 'CONTINUADA',
        '07' => 'PARTIDA',
        '08' => 'POR TURNOS',
        '99' => 'OTRA JORNADA',
        '1' => 'DIURNA',
        '2' => 'NOCTURNA',
        '3' => 'MIXTA',
        '4' => 'POR HORA',
        '5' => 'REDUCIDA',
        '6' => 'CONTINUADA',
        '7' => 'PARTIDA',
        '8' => 'POR TURNOS',
    );
    private $array_contrato = array(
        '01' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
        "02" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
        "03" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
        "04" => 'CONTRATO DE TRABAJO POR TEMPORADA',
        "05" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
        "06" => 'Contrato de trabajo con capacitación inicial',
        "07" => 'Modalidad de contratación por pago de hora laborada',
        "08" => 'Modalidad de trabajo por comisión laboral',
        "09" => 'Modalidades de contratación donde no existe relación de trabajo',
        "10" => 'JUBILACIÓN, PENSIÓN, RETIRO',
        "99" => 'OTRO',
        '1' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
        "2" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
        "3" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
        "4" => 'CONTRATO DE TRABAJO POR TEMPORADA',
        "5" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
        "6" => 'Contrato de trabajo con capacitación inicial',
        "7" => 'Modalidad de contratación por pago de hora laborada',
        "8" => 'Modalidad de trabajo por comisión laboral',
        "9" => 'Modalidades de contratación donde no existe relación de trabajo',
    );

    public function asimilados()
    {
        $base = Session::get('base');
        cambiarBaseA($base);
        $error = false;
        /* verificamos hay periodo para timbrar */
        $periodo = periodosNomina::where('activo', 1)->first();
        //$periodo = periodosNomina::find(100); // Periodo para probar        

        if (!$periodo) {
            $id_periodo = 0;
            $error = true;
            $data_respuesta = [];
            return view('contabilidad.timbrado.asimilados', compact('error', 'data_respuesta', 'id_periodo'));
            
        }
        /* SACAMOS LOS DEPARTAMENTOS */
        $id_periodo     = $periodo->id;
        $ejercicio      = $periodo->ejercicio;
        $nombre_periodo = $periodo->nombre_periodo;

        $query = "SELECT distinct(de.nombre), em.id_departamento as depto
                  FROM departamentos de 
                  JOIN empleados em
                  ON em.id_departamento = de.id
                  JOIN rutinas$ejercicio ru 
                  ON em.id = ru.id_empleado 
                  WHERE em.estatus = 1 and em.tipo_de_nomina = '$nombre_periodo'
                  ";

        $departamentos = DB::connection('empresa')->select($query);

        return view('contabilidad.timbrado.asimilados', compact('error', 'departamentos', 'id_periodo'));
    }

    public function nomina()
    {
        cambiarBase(Session::get('base'));
        $error = false;
        /* verificamos hay periodo para timbrar */
        $periodo = PeriodosNomina::where('activo', 1)->first();
        //$periodo = PeriodosNomina::find(100); // Periodo para probar        

        if (!$periodo) {
            $id_periodo = 0;
            $error = true;
            $data_respuesta = [];
            return view('procesos.timbrado-nomina.nomina', compact('error', 'data_respuesta', 'id_periodo'));
        }
        /* SACAMOS LOS DEPARTAMENTOS */
        $id_periodo     = $periodo->id;
        $ejercicio      = $periodo->ejercicio;
        $nombre_periodo = $periodo->nombre_periodo;

        $query = "SELECT distinct(de.nombre), em.id_departamento as depto
                  FROM departamentos de 
                  JOIN empleados em
                  ON em.id_departamento = de.id
                  JOIN rutinas".$ejercicio." ru 
                  ON em.id = ru.id_empleado 
                  WHERE em.estatus = 1 and em.tipo_de_nomina = '".$nombre_periodo."' ";

        $departamentos = DB::connection('empresa')->select($query);

        return view('procesos.timbrado-nomina.nomina', compact('error', 'departamentos', 'id_periodo'));
    }

    public function timbrar_asimilados_masivo_bucle($cadena)
    {
        $numerico = rand(0, 99999);
        $base = Session::get('base');
        cambiarBaseA($base);
        $cadena_departamentos = $cadena;
        /* periodo */
        //$id_periodo   = 100;
        //$periodo      = periodosNomina::find($id_periodo); // Periodo para probar 
        $periodo      = periodosNomina::where('activo', 1)->first();
        //dump(['timbrar_masivo_bucle'=> $periodo]);
        $id_periodo   = $periodo->id;
        $ejercicio    = $periodo->ejercicio;
        $tipo_nomina  = $periodo->nombre_periodo;
        $dias_periodo = $periodo->dias_periodo;

        /* Convertimos el tipo de periodo a procesar */
        $tipo_periodo = null;
        switch (strtoupper($tipo_nomina)) {
            case 'DIARIA':
                $tipo_periodo = '01';
                break;
            case 'SEMANAL':
                $tipo_periodo = '02';
                break;
            case 'CATORCENAL':
                $tipo_periodo = '03';
                break;
            case 'QUINCENAL':
                $tipo_periodo = '04';
                break;
            case 'MENSUAL':
                $tipo_periodo = '05';
                break;
            case 'ANUAL':
                $tipo_periodo = '99';
                break;
            default:
                $tipo_periodo = '99';
                break;
        }
        /* TRAEMOS LOS EMPLEADOS A VALIDAR */
        $empleados = array();
        $query = "SELECT id,concat(nombre,' ',apaterno,' ',amaterno) AS nombre
                   FROM empleados
                   WHERE estatus = 1
                   AND tipo_de_nomina = '$tipo_nomina'
                   and id_departamento in ($cadena_departamentos)
                   AND id IN (
                       SELECT id_empleado 
                       FROM rutinas$ejercicio 
                       WHERE id_periodo = '$id_periodo' 
                       AND fnq_valor = 0 
                       AND neto_sindical > 0);
                  ";
        $empleados = DB::connection('empresa')->select($query);
        // dd($cadena,$periodo,$tipo_nomina,$tipo_periodo,$empleados);
        return view('contabilidad.timbrado.validacion_masivo_bucle_asimilados', compact('periodo', 'empleados', 'cadena_departamentos'));
    }

    public function validar_empleado_asimilados($id_empleado, $cadena)
    {
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $cadena_departamentos = $cadena;
        $base = Session::get('base');
        cambiarBaseA($base);
        //$cadena_departamentos = $cadena;
        /* periodo */
        $periodo = periodosNomina::where('activo', 1)->first();
        $id_periodo  = $periodo->id;
        //dump(['validar_empleado'=> $periodo]);
        //$id_periodo  = 100;
        //$periodo     = periodosNomina::find($id_periodo); // Periodo para probar 
        $ejercicio   = $periodo->ejercicio;
        $tipo_nomina = $periodo->nombre_periodo;
        $ejercicio   = $periodo->ejercicio;
        /* TRAEMOS LOS EMPLEADOS A VALIDAD */
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND id = $id_empleado;
                 ";
        $e = DB::connection('empresa')->select($query);
        $e = $e[0];

        $errores  = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
        $empleado = array(
            'id'     => $e->id,
            'nombre' => $e->nombre_completo,
            'rfc'    => $e->rfc,
            'curp'   => $e->curp,
            'nss'    => $e->nss,
        );

        //validar RFC/NSS/CURP
        if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
            $errores['rfc'] = true;
            $num_errores++;
        }

        if (!preg_match("/^[0-9]+$/", $e->nss)) {
            $errores['nss'] = true;
            $num_errores++;
        }

        if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
            $errores['curp'] = true;
            $num_errores++;
        }

        //VALIDAR REGISTRO PATRONAL
        $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria_asimilados = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = '$id_empleado' 
                        AND   cat.estatus = 1";
        $registroP = DB::connection('empresa')->select($queryPatronal);

        if ($registroP) {
            $r = $registroP[0];
            $XXX = ($r->tipo_jornada == "DUIRNA" || $r->tipo_jornada == "DIURNA") ? 1 : $r->tipo_jornada;
            $r->string_contrato = $this->array_contrato[$r->tipo_contrato];
            $r->string_jornada = $this->array_jornada[$XXX];
        } else {
            $r = "error";
            $errores['registro_patronal'] = true;
            $num_errores++;
        }
        $empleado['registro_patronal'] = $r;
        $empleado['errores'] = $errores;

        /* Validación de conceptos de nomina */
        $queryConceptos = "SELECT * 
                            FROM conceptos_nomina 
                            WHERE nomina = 1 
                            AND  activo_en_nomina = 1 
                            AND  tipo <> 3 
                            AND  estatus <> 2 
                            AND  rutinas = 'ASIMILADOS'";
        $concep = DB::connection('empresa')->select($queryConceptos);
        $conceptos = array();
        foreach ($concep as $c) {
            $errores = array('sat' => false);
            $concepto = array(
                'id'         => $c->id,
                'nombre'     => $c->nombre_concepto,
                'codigo_sat' => $c->codigo_sat,
            );
            if ($c->codigo_sat == NULL) {
                $errores['sat'] = true;
                $num_errores_conceptos++;
            }
            $concepto['errores'] = $errores;
            $conceptos[] = $concepto;
        }

        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);

        //dd($periodo,$empleado,$conceptos,$cadena_departamentos,$errores);
        return view('contabilidad.timbrado.validacion_empleado_asimilados', compact('periodo', 'empleado', 'conceptos', 'cadena_departamentos', 'errores'));
    }

    public function timbrar_asimilados_empleado($id_empleado, $cadena, $tipoR, Request $request)
    {

        //Corresponde al php 04_CFDI_ReciboNomina.php
        $numerico = rand(0, 99999);
        $base = Session::get('base');
        cambiarBaseA($base);

        /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
        $data_string = "";
        $data_respuesta = array();

        //verificamos el periodo activo
        $cadena_departamentos = $cadena;
        /* periodo */
        //$id_periodo   = 100;
        //$periodo      = periodosNomina::find($id_periodo); // Periodo para probar 
        $periodo      = periodosNomina::where('activo', 1)->first();

        $id_periodo   = $periodo->id;
        $ejercicio    = $periodo->ejercicio;
        $tipo_nomina  = $periodo->nombre_periodo;
        $dias_periodo = $periodo->dias_periodo;
        $fecha_inicial_periodo = new \DateTime($periodo->fecha_inicial_periodo);
        $fecha_final_periodo   = new \DateTime($periodo->fecha_final_periodo);

        $fecha_inicial_periodoStr = $periodo->fecha_inicial_periodo;
        $fecha_final_periodoStr   = $periodo->fecha_final_periodo;
        $fecha_pago = $periodo->fecha_pago;
        /* Convertimos el tipo de periodo a procesar */
        $tipo_periodo = null;
        switch (strtoupper($tipo_nomina)) {
            case 'DIARIA':
                $tipo_periodo = '01';
                break;
            case 'SEMANAL':
                $tipo_periodo = '02';
                break;
            case 'CATORCENAL':
                $tipo_periodo = '03';
                break;
            case 'QUINCENAL':
                $tipo_periodo = '04';
                break;
            case 'MENSUAL':
                $tipo_periodo = '05';
                break;
            case 'ANUAL':
                $tipo_periodo = '99';
                break;
            default:
                $tipo_periodo = '99';
                break;
        }
        /* VALIDACION DE QUE NO EXISTA TIMBRADO */
        /* 
                01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                $numRegistros -> original
                Sacamos tambien el estatus del timbre:
                0 = ?
                1 = Timbrado
                2 = Error
            */
        $query2 = "SELECT * 
                     FROM timbrado_asimilados
                     WHERE id_empleado = '$id_empleado' 
                     AND id_periodo = '$id_periodo' 
                     AND estatus_timbre = 1";
        $timbres = DB::connection('empresa')->select($query2);

        /* TRAEMOS EMISORA */
        $query = "SELECT em.id_categoria_asimilados,em.tipo_jornada,em.tipo_contrato,cat.nombre,re.num_registro_patronal,re.tipo_clase,ememi.razon_social,ememi.user_timbre,ememi.cp
                 FROM $base.empleados em  
                 JOIN $base.categorias cat  
                 ON em.id_categoria_asimilados = cat.id 
                 INNER JOIN singh.registro_patronal re 
                 ON cat.tipo_clase = re.id
                 INNER JOIN singh.empresas_emisoras ememi
                 ON re.id_empresa_emisora = ememi.id
                 WHERE em.id = '$id_empleado' 
                 AND   cat.estatus = 1
                 AND ememi.estatus =1
                 AND re.estatus = 1
                 AND em.estatus = 1;
                 ";
        $emisora = DB::connection('empresa')->select($query);
        $emisora = $emisora[0];

        $razon_emisora = strtoupper($emisora->razon_social);
        $usr_timbre    = $emisora->user_timbre;
        $cp_emisora    = $emisora->cp;

        $tipo_clase    = null;
        /*
        ORIGINAL
        $TipoJornada=str_pad($rowresulTipoClase['tipocontrato'],2,'0',STR_PAD_LEFT);
        $tipo_jornada  = str_pad( $emisora->tipo_clase,2,'0',STR_PAD_LEFT);
        */
        $tipo_jornada  = str_pad($emisora->tipo_jornada, 2, '0', STR_PAD_LEFT);
        $tipo_contrato = str_pad($emisora->tipo_contrato, 2, '0', STR_PAD_LEFT);
        switch ($emisora->tipo_clase) {
            case 'Clase I':
                $tipo_clase = '1';
                break;
            case 'Clase II':
                $tipo_clase = '2';
                break;
            case 'Clase III':
                $tipo_clase = '3';
                break;
            case 'Clase IV':
                $tipo_clase = '4';
                break;
            case 'Clase V':
                $tipo_clase = '5';
                break;
        }
        /* TOTAL DE PERCEPCIONES */
        $query = " SELECT *
                  FROM rutinas$ejercicio
                  WHERE fnq_valor = 0
                  AND id_periodo = $id_periodo
                  AND id_empleado = $id_empleado";
        $datos = DB::connection('empresa')->select($query);
        $datos = $datos[0];

        $total_percep     = round($datos->total_percepcion_sindical, 2);
        $total_deduc      = $datos->total_deduccion_sindical;
        $subsidio_causado = $datos->subsidio_al_empleo;
        $neto_fiscal      = $datos->neto_sindical;

        /* DATOS DEL EMPLEADO */
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND id = $id_empleado;
                 ";
        $e = DB::connection('empresa')->select($query);
        $e = $e[0];

        /* FECHAS EMPLEADO */
        $fecha_alta_empleado     = new \DateTime($e->fecha_alta);
        $fecha_alta_empleado_str = $e->fecha_alta;
        $rfc_empleado            = $e->rfc;
        $nombre_empleado = $e->nombre_completo;
        $nombre = $e->nombre;
        $correo_empleado = $e->correo;
        $num_empleado = $e->numero_empleado;
        $id_empleado = $e->id;
        /* ANTIGUEDAD (Diferencia de dias entre la alta y el periodo) */
        $diff = $fecha_final_periodo->diff($fecha_alta_empleado);
        $antigue = floor(($diff->days + 1) / 7);
        $antiguedad = 'P' . $antigue . 'W';

        /* DIAS A PAGAR */
        if ($fecha_alta_empleado > $fecha_inicial_periodo) {
            /* Si el alta es despues de que inicio el periodo se paga los dias proporcionales */
            $diff = $fecha_final_periodo->diff($fecha_alta_empleado);
            $dias_pagados = $diff->days;
        } else {
            $diff = $fecha_final_periodo->diff($fecha_inicial_periodo);
            $dias_pagados = $diff->days;
        }

        /* DEDUCCIONES */
        $subsidio_valor      = $datos->subsidio * 1;
        $incapacidades_valor = ($datos->incapacidades == "" || $datos->incapacidades == null) ? 0 : $datos->incapacidades;
        $total_gravado_real  = $datos->total_gravado_asimilados;

        /* FALTAS */
        /* TODO agregar a la query de mas arriba de concepto */
        $faltas_valor = 0;
        $query = "SELECT id
                   FROM conceptos_nomina 
                   WHERE nombre_concepto = 'FALTAS' 
                   AND estatus = 1";
        $f = DB::connection('empresa')->select($query);
        if (!empty($f)) {
            $f = $f[0]->id;
            $query = "SELECT Total$f AS valor_falta 
                      FROM rutinas$ejercicio
                      WHERE id_periodo = '$id_periodo'
                      AND id_empleado = '$id_empleado' 
                      AND fnq_valor = 0;";
            $ff = DB::connection('empresa')->select($query);
            $faltas_valor = ($ff[0]->valor_falta == "" || $ff[0]->valor_falta == null) ? 0 : $ff[0]->valor_falta;
        }

        /* DIAS A PAGAR */
        $dias_a_pagar = ceil(($dias_pagados + 1) - $incapacidades_valor - $faltas_valor);

        /* SUMA DE CONCEPTO A PAGAR */
        /* array[id_temporal] = id_concepto)  */
        $arr_conceptos   = array();
        $str_conceptos = "";
        $queryC = "SELECT id,nombre_concepto
                   FROM  conceptos_nomina
                   WHERE tipo = 0 
                   AND nomina = 1 
                   AND estatus<> 0 
                   AND activo_en_nomina = 1
                   AND rutinas = 'ASIMILADOS'";
        $idC = DB::connection('empresa')->select($queryC);

        foreach ($idC as $c) {
            $id = $c->id;
            $queryI = "SELECT Total$id AS result 
                       FROM rutinas$ejercicio
                       WHERE id_periodo = '$id_periodo'
                       AND id_empleado = '$id_empleado' 
                       AND fnq_valor = 0";
            $valorC = DB::connection('empresa')->select($queryI);

            if ($valorC[0]->result <= 0) {
                $arr_conceptos[$id] = $id;
                $str_conceptos .= "$id,";
            } else {
                $arr_conceptos[$id] = 0;
                $str_conceptos .= "0,";
            }
        }
        $str_conceptos = trim($str_conceptos, ',');

        /* SUMA DEDUCCIONES */
        $arr_deducciones = array();
        $str_deducciones = "";
        
            $queryI = "SELECT isr_asimilados AS result 
                       FROM rutinas$ejercicio
                       WHERE id_periodo = '$id_periodo'
                       AND id_empleado = '$id_empleado' 
                       AND fnq_valor = 0";
            $valorC = DB::connection('empresa')->select($queryI);
            $id=1;
            if ($valorC[0]->result <= 0) {
                $arr_deducciones[$id] = $id;
                $str_deducciones .= "$id,";
            } else {
                $arr_deducciones[$id] = 0;
                $str_deducciones .= "0,";
            }
        
        $str_deducciones = trim($str_deducciones, ',');

        # 1.1 Configuración de zona horaria
        date_default_timezone_set('America/Mexico_City'); //
        $data_respuesta['timezone'] = date_default_timezone_get();

        ### 2. ASIGNACIÓN DE VALORES A VARIABLES ###################################################
        $data_respuesta['idrepo'] = Session::get('empresa')['id'] . '/' . $e->id;

        $repositorio = 'repositorio/' .  Session::get('empresa')['id'] . '/' . $e->id . '/timbrado/';
        $repositorio2 = 'public/repositorio/' . Session::get('empresa')['id'] . '/' . $e->id . '/timbrado/';
        $recursos = resource_path() . '/timbrado/';
        $folder_repositorio = public_path() . '/' . $repositorio;

        $SendaCFDI     = $folder_repositorio . 'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaEmpGRAFS = $folder_repositorio . 'archs_grafs/';   // 2.2 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR       = $folder_repositorio . 'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR_url   = $repositorio2 . 'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPreCFDI  = $folder_repositorio . 'archs_precdfi/'; // 2.4 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPDF      = $folder_repositorio . 'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaRESP     = $folder_repositorio . 'archs_resp/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).

        $SendaPEMS    = $recursos . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $recursos . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $recursos . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);

        /* Checar si existen directorios, si no crearlos*/
        if (!File::exists($folder_repositorio)) {
            File::makeDirectory($folder_repositorio, $mode = 0777, true, true);
        }
        if (!File::exists($SendaCFDI)) {
            File::makeDirectory($SendaCFDI, $mode = 0777, true, true);
        }
        if (!File::exists($SendaEmpGRAFS)) {
            File::makeDirectory($SendaEmpGRAFS, $mode = 0777, true, true);
        }
        if (!File::exists($SendaPreCFDI)) {
            File::makeDirectory($SendaPreCFDI, $mode = 0777, true, true);
        }
        if (!File::exists($SendaQR)) {
            File::makeDirectory($SendaQR, $mode = 0777, true, true);
        }
        if (!File::exists($SendaPDF)) {
            File::makeDirectory($SendaPDF, $mode = 0777, true, true);
        }
        if (!File::exists($SendaRESP)) {
            File::makeDirectory($SendaRESP, $mode = 0777, true, true);
        }

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr($usr_timbre, 7, strlen($usr_timbre));
        $condicion = ($condicion == null) ? 1 : $condicion;
        //se usa para pruebas)
        //$condicion = 1;
        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
        $credenciales = DB::select($query);

        $rfc_emi    = $credenciales[0]->rfc;
        $rfc_emisor = $rfc_emi;
        $razon_emisor = $credenciales[0]->razon_social;
        $username    = base64_decode($credenciales[0]->user);
        $password    = base64_decode($credenciales[0]->password);
        $ip_servicio = $credenciales[0]->servicio;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales[0]->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales[0]->nombre_archivo . ".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo . ".key.pem";   // 3.3 Nombre del archivo .cer.key

        ### 4. DATOS GENERALES DE LA FACTURA ##################################################
        $fact_serie        = "A";                             // 4.1 Número de serie.
        $fact_folio        = mt_rand(1000, 9999);             // 4.2 Número de folio (para efectos de demostración se asigna de manera aleatoria).
        $NoFac             = $fact_serie . $fact_folio;         // 4.3 Serie de la factura concatenado con el número de folio.
        $data_respuesta['no_factura'] = $NoFac;
        ### PERCEPCIONES ###############################################################
        //TODO checar tipos en estatus y activo_en_nomina
        // ArraysPercepciones.
        $query = "SELECT id,nombre_concepto,SUBSTRING(codigo_sat,1,length(codigo_sat)-1) AS codigo_sat 
                FROM conceptos_nomina
                WHERE tipo = 0 
                AND  nomina = 1 
                AND  estatus = 1 
                AND rutinas = 'ASIMILADOS'
                AND  activo_en_nomina = 1 
                AND  id not in($str_conceptos)";
        $valorPC = DB::connection('empresa')->select($query);

        $numero_array_sat = count($valorPC);
        $numero_de_array = count($valorPC);
        /* ARRAY DE CONCEPTOS DE PERCEPCONES */
        $ArrayPercep_Concepto = array();
        $ArrayPercep_TipoPercepcion = array();
        $ArrayPercep_Clave = array();

        foreach ($valorPC as $c) {
            $ArrayPercep_Concepto[] = $c->nombre_concepto;
            $ArrayPercep_TipoPercepcion[] = $c->codigo_sat;
            if (strlen($c->id) < 3) {
                $ArrayPercep_Clave[] = str_pad($c->id, 3, "PP", STR_PAD_LEFT);
            } else {
                $ArrayPercep_Clave[] =  'PP' . $c->id;
            }
        }

        /* Importe gravado */
        /* VALOR GRAVADO */
        $total_percepciones = 0;
        $ArrayPercep_ImporteGravado = array();
        $queryGrav = "SELECT id,nombre_concepto 
                      FROM conceptos_nomina
                      WHERE tipo = 0 
                      AND   nomina = 1 
                      AND  estatus = 1 
                      AND rutinas = 'ASIMILADOS'
                      AND  activo_en_nomina = 1 
                      AND  id not in($str_conceptos)";

        $resultGrav = DB::connection('empresa')->select($queryGrav);

        foreach ($resultGrav as $r) {
            $id = $r->id;
            $qryImporte = "SELECT ROUND(gravado$id,2) AS result 
                           FROM rutinas$ejercicio
                           WHERE id_periodo ='$id_periodo'
                           AND   id_empleado = '$id_empleado' 
                           AND  fnq_valor = 0";
            $resultImp = DB::connection('empresa')->select($qryImporte);
            $ArrayPercep_ImporteGravado[] = $resultImp[0]->result;
            $total_percepciones += $resultImp[0]->result;
        }
        //$Percep_TotalGravado
        $percep_total_gravado = $total_gravado_real;

        /* VALOR EXCENTO */
        $valor_exento = 0;
        $ArrayPercep_ImporteExento = array();
        $queryEx = "SELECT id,nombre_concepto
                  FROM conceptos_nomina
                  WHERE tipo = 0 
                  AND nomina = 1 
                  AND estatus = 1 
                  AND rutinas = 'ASIMILADOS'
                  AND activo_en_nomina = 1 
                  AND id not in($str_conceptos)";

        $resultEx = DB::connection('empresa')->select($queryEx);

        foreach ($resultEx as $r) {
            $id = $r->id;
            $qryImporte = "SELECT ROUND(excento$id,2) AS result 
                           FROM rutinas$ejercicio
                           WHERE id_periodo ='$id_periodo'
                           AND   id_empleado = '$id_empleado' 
                           AND  fnq_valor = 0";
            $resultImp = DB::connection('empresa')->select($qryImporte);
            $ArrayPercep_ImporteExento[] = $resultImp[0]->result;
            $valor_exento += $resultImp[0]->result;
        }

        ### DEDUCCIONES ################################################################
        // ArraysDeducciones.
        //TODO checar tipos en estatus y activo_en_nomina
        // ArraysPercepciones.
        //$arr_deducciones = array();
        //$str_deducciones == idconcetosDeduc

        //dump($str_deducciones,$idD,$valorDC);
        //$valorDC -> $rowidDeduccion
        $ArrayDeduc_Concepto = array();
        $ArrayDeduc_Clave    = array();
        $ArrayDeduc_Importe = array();
        $ArrayDeduc_TipoDeduccion  = array();
        $tiene_imss = 0;
           // dump($rDeduc);
            $concepto = 'ISR';
            $idDed    = '46';
            if (strlen($idDed) < 3) {
                $idDed = str_pad($idDed, 3, "DD", STR_PAD_LEFT);
            } else {
                $idDed = 'DD' . $idDed;
            }

            if ($concepto == 'ISR') {
               // dump('IMPUESTO SOBRE LA RENTA');
                $concepto = 'IMPUESTO SOBRE LA RENTA';
            }

            if ($concepto == 'IMSS' || $concepto == 'imss') {
                $tiene_imss = 1;
            }

            $ArrayDeduc_Concepto[] = $concepto;
            $ArrayDeduc_Clave[]    = $idDed;
        
        // $rowvalidaDeducion=mysqli_num_rows($rowidDeduccion);
        $numDeducciones = 1;
        //dd($valorDC);
        /* Cambio de funcion a devolver array plano*/
       
            $qId = "SELECT  ROUND(isr_asimilados, 2) 
                   AS result 
                   FROM rutinas$ejercicio
                   WHERE id_periodo = '$id_periodo'
                   AND id_empleado = '$id_empleado' 
                   AND fnq_valor = 0;";

            $valoriDC = DB::connection('empresa')->select($qId);
            $ArrayDeduc_Importe[] = ($valoriDC[0]->result < 0)? $valoriDC[0]->result * -1 : $valoriDC[0]->result;

           
        
        //dd($ArrayDeduc_Importe);
        
        //dd($valorIDDC)
            $ArrayDeduc_TipoDeduccion[] = substr('002d', 0, 3);

        /* ISR */
        

        $queryISR = "SELECT ROUND(isr_asimilados, 2) AS result 
                     FROM rutinas$ejercicio
                     WHERE id_periodo = '$id_periodo'
                     AND id_empleado = '$id_empleado' 
                     AND fnq_valor = 0";
        $valorISR = DB::connection('empresa')->select($queryISR);
        $importe_valor_isr = $valorISR[0]->result;
        //dd($importe_valor_isr);
        ### OTROS PAGOS ################################################################
        $total_exento = $valor_exento;
        $total_percercepciones = round($datos->total_percepcion_sindical, 2);

        if ($importe_valor_isr > 0) {
            $importe_otros_pagos = 0;
        } else {
            $importe_otros_pagos = $importe_valor_isr * -1;
        }
        $total_con_ajuste = $total_percercepciones + $importe_otros_pagos;

        $ArrayOtrosPag_TipoOtroPago  = ['002'];
        $ArrayOtrosPag_Clave         = ['D002'];
        $ArrayOtrosPag_Concepto      = ['SUBSIDIO PARA EL EMPLEO'];
        $ArrayOtrosPag_Importe       = [number_format($importe_otros_pagos, 2, '.', '')];

        /*
        //checar si sirve de algo en otro
        for ($i=0; $i<count($ArrayOtrosPag_Importe); $i++){
            $totalOtrosPagos = $totalOtrosPagos + $ArrayOtrosPag_Importe[$i];
        }
        */
        $totalOtrosPagos = number_format($importe_otros_pagos, 2, '.', '');

        ### CONCEPTO DE PAGO DE NÓMINA #################################################
        $ConceptPagNom_Cant     = "1";
        $ConceptPagNom_Unidad   = "ACT";
        $ConceptPagNom_Descrip  = utf8_decode("Pago de nómina");
        $ConceptPagNom_ValorUni = number_format($total_percepciones + $totalOtrosPagos, 2, '.', '');
        $ConceptPagNom_Importe  = number_format($total_percepciones + $totalOtrosPagos, 2, '.', '');

        ### DETERMINAR TOTALES #########################################################
        /* VERIFICAR SI SE USAN
        $subTotal = number_format($totalPercepciones+$totalOtrosPagos,2,'.',''); // SubTotal.
        $total    = number_format($totalPercepciones+$totalOtrosPagos-$totalDeducciones,2,'.',''); // Total.
        $Percep_TotalSueldos = number_format($totalPercepciones,2,'.','');
        */
        ######################## GENERACION REAL DEL XML ##########################
        ### 11. CREACIÓN Y ALMACENAMIENTO DEL ARCHIVO .XML (CFDI) ANTES DE SER TIMBRADO ###################

        #== 11.1 Creación de la variable de tipo DOM, aquí se conforma el XML a timbrar posteriormente.
        $xml = new \DOMdocument('1.0', 'UTF-8');
        $root = $xml->createElement("cfdi:Comprobante");
        $root = $xml->appendChild($root);
        $cadena_original = '||';
        $noatt =  array();

        $toal_de_duccion = round($datos->total_deduccion_sindical, 2);
        $importe_total = $total_con_ajuste - $toal_de_duccion;

        if ($importe_valor_isr < 0) {
            $TotalPercepAdicional = $total_percep + ($importe_valor_isr * -1);
            $totalotraspagos      = $importe_valor_isr * -1;
            $totaldeducpositivo   = $total_deduc * -1;
            $totalrealdeduccion   = $totalotraspagos - ($total_deduc * -1);
        } else {
            $TotalPercepAdicional = $total_percep;
            $totalrealdeduccion   = round($total_deduc, 2);
            $totalotraspagos      = 0;
        }
        //dd($totalotraspagos);
        #== 11.3 Rutina de integración de nodos =========================================
        $cadena_original .= $this->cargaAtt(
            $root,
            array(
                "Version" => "3.3",
                "Fecha" => date("Y-m-d") . "T" . date("H:i:s"),
                "FormaPago" => "99",
                "NoCertificado" => $no_certificado,
                "SubTotal" => number_format($TotalPercepAdicional, 2, '.', ''),
                "Descuento" => number_format($totalrealdeduccion, 2, '.', ''),
                "Moneda" => "MXN",
                "Total" => number_format($neto_fiscal, 2, '.', ''),
                "TipoDeComprobante" => "N",
                "MetodoPago" => "PUE",
                "LugarExpedicion" => $cp_emisora
            )
        );

        #== 11.2 Se crea e inserta el primer nodo donde se declaran los namespaces ======
        $cadena_original .= $this->cargaAttSinIntACad(
            $root,
            array(
                "xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
                "xmlns:nomina12" => "http://www.sat.gob.mx/nomina12",
                "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
                "xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/nomina12 http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd"
            )
        );

        #== 11.2 EMISOR =====
        $emisor = $xml->createElement("cfdi:Emisor");
        $emisor = $root->appendChild($emisor);
        $cadena_original .= $this->cargaAtt(
            $emisor,
            array(
                "Rfc" => $rfc_emisor,
                "Nombre" => $razon_emisor,
                "RegimenFiscal" => "601"
            )
        );

        #== 11.2 Receptor =====
        $receptor = $xml->createElement("cfdi:Receptor");
        $receptor = $root->appendChild($receptor);
        $cadena_original .= $this->cargaAtt(
            $receptor,
            array(
                "Rfc" => $rfc_empleado,
                "Nombre" => $nombre_empleado,
                "UsoCFDI" => "G03"
            )
        );

        $conceptos = $xml->createElement("cfdi:Conceptos");
        $conceptos = $root->appendChild($conceptos);

        $concepto = $xml->createElement("cfdi:Concepto");
        $concepto = $conceptos->appendChild($concepto);

        $cadena_original .= $this->cargaAtt(
            $concepto,
            array(
                "ClaveProdServ" => "84111505",
                "Cantidad" => "1",
                "ClaveUnidad" => "ACT",
                //"Descripcion"=>utf8_encode("Pago de nómina"),// checar
                "Descripcion" => "Pago de nómina", // checar
                "ValorUnitario" => number_format($TotalPercepAdicional, 2, '.', ''),
                "Importe" => number_format($TotalPercepAdicional, 2, '.', ''),
                "Descuento" => number_format($totalrealdeduccion, 2, '.', '')
            )
        );
        // $totalSueldoss=$totalpercer;
        ########################### INICIA CODIFICACIÓN DEL COMPLEMENTO DE NÓMINA 1.2 ###########################

        $complemento = $xml->createElement("cfdi:Complemento");
        $complemento = $root->appendChild($complemento);

        $nomina = $xml->createElement("nomina12:Nomina");
        $nomina = $complemento->appendChild($nomina);

        if ($importe_valor_isr > 0) {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                    "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                    //"TotalDeducciones"=>number_format($toal_de_duccion,2,'.',''),
                    "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
                )
            );
        } else if ($toal_de_duccion > 0) {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),

                    "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                    //"TotalDeducciones"=>number_format($toal_de_duccion,2,'.',''),
                    "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
                )
            );
        } else if ($totalotraspagos == 0 && $totalrealdeduccion == 0) {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', '')
                )
            );
        } else if ($totalotraspagos == 0) {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                    "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', '')
                )
            );
        } else if ($totalotraspagos > 0 && $totalrealdeduccion == 0) {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                    "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
                )
            );
        } else {
            $cadena_original .= $this->cargaAtt(
                $nomina,
                array(
                    "Version" => "1.2",
                    "TipoNomina" => "O",
                    "FechaPago" => $fecha_pago,
                    "FechaInicialPago" => $fecha_inicial_periodoStr,
                    "FechaFinalPago" => $fecha_final_periodoStr,
                    "NumDiasPagados" => $dias_a_pagar,
                    "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                    "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                    "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
                )
            );
        }

        //VALIDAR REGISTRO PATRONAL
        $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria_asimilados = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = '$id_empleado' 
                        AND   cat.estatus = 1";
        $registroP = DB::connection('empresa')->select($queryPatronal);

        $NominaEmisor = $xml->createElement("nomina12:Emisor");
        $NominaEmisor = $nomina->appendChild($NominaEmisor);

        //$resgistroPatro
        $registro_patronal  = $registroP[0]->num_registro_patronal;


        if ($tipo_contrato == '09' || $tipo_contrato == '10' || $tipo_contrato == '99') {
        } else {
            $cadena_original .= $this->cargaAtt($NominaEmisor, array(
                "RegistroPatronal" => $registro_patronal
            ));
        }

        if ($ip_servicio == 'https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl') {
            $EntidadSNCF = $xml->createElement("nomina12:EntidadSNCF");
            $EntidadSNCF = $NominaEmisor->appendChild($EntidadSNCF);
            $cadena_original .= $this->cargaAtt(
                $EntidadSNCF,
                array(
                    "OrigenRecurso" => "IP"
                )
            );
        }

        if ($num_empleado == NULL || $num_empleado == "") {
            $NumeroEmpleado = $id_empleado;
        } else {
            $NumeroEmpleado = $num_empleado;
        }
        /* traer depto y puesto */
        /* checar la diferencia de categoria y categoriaesp, donde aplica cada uno */
        $queryDep = "SELECT nombre
                   FROM departamentos
                   WHERE id = '$e->id_departamento';";
        $registroD = DB::connection('empresa')->select($queryDep);
        $queryPue = "SELECT puesto as nombre
                    FROM puestos
                    WHERE id = '$e->id_puesto';";
        $registroP = DB::connection('empresa')->select($queryPue);
        $NominaReceptor = $xml->createElement("nomina12:Receptor");
        $NominaReceptor = $nomina->appendChild($NominaReceptor);
        $Npuesto = (count($registroP) > 0) ? $registroP[0]->nombre : "";
        $NDepto = (count($registroD) > 0) ? $registroD[0]->nombre : "";




        $cadena_original .= $this->cargaAtt(
            $NominaReceptor,
            array(
                "Curp"                   => $e->curp,
                "NumSeguridadSocial"     => $e->nss,
                "FechaInicioRelLaboral"  => $fecha_alta_empleado->format('Y-m-d'),
                "Antigüedad"             => "$antiguedad",
                "TipoContrato"           => $tipo_contrato,
                //"Sindicalizado"=>utf8_decode("Sí"),
                "Sindicalizado"          => "Sí",
                "TipoJornada"            => $tipo_jornada,
                "TipoRegimen"            => "02",
                "NumEmpleado"            => $NumeroEmpleado,
                "Departamento"           => $NDepto,
                "Puesto"                 => $Npuesto,
                "RiesgoPuesto"           => $tipo_clase,
                "PeriodicidadPago"       => $tipo_periodo,
                "SalarioBaseCotApor"     => round($e->salario_diario, 2), //
                "SalarioDiarioIntegrado" => round($e->salario_diario_integrado, 2),
                "ClaveEntFed"            => "MIC"
            )
        );
        $data_respuesta['nombre'] = $nombre_empleado;
        //TotalPercepFiscal
        $percepciones = $xml->createElement("nomina12:Percepciones");
        $percepciones = $nomina->appendChild($percepciones);
        $cadena_original .= $this->cargaAtt(
            $percepciones,
            array(
                "TotalSueldos" => number_format($total_percercepciones, 2, '.', ''),
                "TotalGravado" => number_format($total_gravado_real, 2, '.', ''),
                "TotalExento" => number_format($total_exento, 2, '.', '')
            )
        );
        // Ciclo "for", recopilación de datos de percepciones. ===============
        for ($i = 0; $i < $numero_array_sat; $i++) {
            $percepcion = $xml->createElement("nomina12:Percepcion");
            $percepcion = $percepciones->appendChild($percepcion);
            $cadena_original .= $this->cargaAtt(
                $percepcion,
                array(
                    "TipoPercepcion" => $ArrayPercep_TipoPercepcion[$i],
                    "Clave" => $ArrayPercep_Clave[$i],
                    "Concepto" => $ArrayPercep_Concepto[$i],
                    "ImporteGravado" => $ArrayPercep_ImporteGravado[$i],
                    "ImporteExento" => $ArrayPercep_ImporteExento[$i]
                )
            );
        }

        // TODO checar si se pueden quitar
        $total_otras_deduc = $toal_de_duccion - $importe_valor_isr;
        $impuestos_retenidos = $importe_valor_isr;
        //dd($subsidio_causado);
        if ($importe_valor_isr > 0) {

            if ($numDeducciones > 0) {
                $deducciones = $xml->createElement("nomina12:Deducciones");
                $deducciones = $nomina->appendChild($deducciones);
                $cadena_original .= $this->cargaAtt(
                    $deducciones,
                    array(
                        "TotalOtrasDeducciones" => number_format($total_otras_deduc, 2, '.', ''),
                        "TotalImpuestosRetenidos" => number_format($impuestos_retenidos, 2, '.', '')
                    )
                );

                // Ciclo "for", recopilación de datos de deducciones. ===============

                for ($i = 0; $i < $numDeducciones; $i++) {
                    if ($ArrayDeduc_Importe[$i] != 0) {
                        $deduccion = $xml->createElement("nomina12:Deduccion");
                        $deduccion = $deducciones->appendChild($deduccion);
                        $cadena_original .= $this->cargaAtt(
                            $deduccion,
                            array(
                                "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                                "Clave" => $ArrayDeduc_Clave[$i],
                                "Concepto" => $ArrayDeduc_Concepto[$i],
                                "Importe" => $ArrayDeduc_Importe[$i]
                            )
                        );
                    }
                }
            }

            $OtroPago = $xml->createElement("nomina12:OtrosPagos");
            $OtroPago = $nomina->appendChild($OtroPago);

            // solo tiene imss
            if ($numDeducciones == 1 && $tiene_imss) {
                $otrosPagos =  $xml->createElement("nomina12:OtroPago");
                $otrosPagos = $OtroPago->appendChild($otrosPagos);
                $cadena_original .= $this->cargaAtt(
                    $otrosPagos,
                    array(
                        "TipoOtroPago" => "002",
                        "Clave" => "D002",
                        "Concepto" => 'SUBSIDIO PARA EL EMPLEO',
                        "Importe" => $totalotraspagos // aqui va el isr convertido en negativo
                    )
                );

                $SubsidioAlEmpleo = $xml->createElement("nomina12:SubsidioAlEmpleo");
                $SubsidioAlEmpleo = $otrosPagos->appendChild($SubsidioAlEmpleo);
                $cadena_original .= $this->cargaAtt(
                    $SubsidioAlEmpleo,
                    array(
                        "SubsidioCausado" => $subsidio_causado // aquí va el subsidio causado
                    )
                );
            } else {

                for ($i = 0; $i < count($ArrayOtrosPag_TipoOtroPago); $i++) {
                    $otrosPagos =  $xml->createElement("nomina12:OtroPago");
                    $otrosPagos = $OtroPago->appendChild($otrosPagos);
                    $cadena_original .= $this->cargaAtt(
                        $otrosPagos,
                        array(
                            "TipoOtroPago" => $ArrayOtrosPag_TipoOtroPago[$i],
                            "Clave" => $ArrayOtrosPag_Clave[$i],
                            "Concepto" => $ArrayOtrosPag_Concepto[$i],
                            "Importe" => $ArrayOtrosPag_Importe[$i]
                        )
                    );
                }

                $SubsidioAlEmpleo = $xml->createElement("nomina12:SubsidioAlEmpleo");
                $SubsidioAlEmpleo = $otrosPagos->appendChild($SubsidioAlEmpleo);
                $cadena_original .= $this->cargaAtt(
                    $SubsidioAlEmpleo,
                    array(
                        "SubsidioCausado" => number_format($subsidio_causado, 2, '.', '')
                    )
                );
            }
        } else if ($totalotraspagos == 0) {
            if ($numDeducciones > 0) {
                $deducciones = $xml->createElement("nomina12:Deducciones");
                $deducciones = $nomina->appendChild($deducciones);
                $cadena_original .= $this->cargaAtt(
                    $deducciones,
                    array(
                        "TotalOtrasDeducciones" => round($totalrealdeduccion, 2)
                    )
                );
                // Ciclo "for", recopilación de datos de deducciones. ===============
                if (Session::get('usuarioPermisos')['id_usuario'] == 64) {
                    dd('dos', $ArrayDeduc_Importe);
                }
                for ($i = 0; $i < $numDeducciones; $i++) {
                    $deduccion = $xml->createElement("nomina12:Deduccion");
                    $deduccion = $deducciones->appendChild($deduccion);
                    $cadena_original .= $this->cargaAtt(
                        $deduccion,
                        array(
                            "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                            "Clave" => $ArrayDeduc_Clave[$i],
                            "Concepto" => $ArrayDeduc_Concepto[$i],
                            "Importe" => $ArrayDeduc_Importe[$i]
                        )
                    );
                }
            }
        } else {
            if ($numDeducciones > 0) {
                $deducciones = $xml->createElement("nomina12:Deducciones");
                $deducciones = $nomina->appendChild($deducciones);
                $cadena_original .= $this->cargaAtt(
                    $deducciones,
                    array(
                        "TotalOtrasDeducciones" => round($totalrealdeduccion, 2)
                    )
                );

                // Ciclo "for", recopilación de datos de deducciones. ===============
                for ($i = 0; $i < $numDeducciones; $i++) {
                    if ($ArrayDeduc_Importe[$i] != 0) {
                        $deduccion = $xml->createElement("nomina12:Deduccion");
                        $deduccion = $deducciones->appendChild($deduccion);
                        $cadena_original .= $this->cargaAtt(
                            $deduccion,
                            array(
                                "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                                "Clave" => $ArrayDeduc_Clave[$i],
                                "Concepto" => $ArrayDeduc_Concepto[$i],
                                "Importe" => $ArrayDeduc_Importe[$i]
                            )
                        );
                    }
                }
            }

            $OtroPago = $xml->createElement("nomina12:OtrosPagos");
            $OtroPago = $nomina->appendChild($OtroPago);

            // solo tiene imss
            if ($numDeducciones == 1 && $tiene_imss) {
                $otrosPagos =  $xml->createElement("nomina12:OtroPago");
                $otrosPagos = $OtroPago->appendChild($otrosPagos);
                $cadena_original .= $this->cargaAtt(
                    $otrosPagos,
                    array(
                        "TipoOtroPago" => "002",
                        "Clave" => "D002",
                        "Concepto" => 'SUBSIDIO PARA EL EMPLEO',
                        "Importe" => $totalotraspagos
                    )
                );

                $SubsidioAlEmpleo = $xml->createElement("nomina12:SubsidioAlEmpleo");
                $SubsidioAlEmpleo = $otrosPagos->appendChild($SubsidioAlEmpleo);
                $cadena_original .= $this->cargaAtt(
                    $SubsidioAlEmpleo,
                    array(
                        "SubsidioCausado" => $subsidio_causado
                    )
                );
            } else {
                for ($i = 0; $i < count($ArrayOtrosPag_TipoOtroPago); $i++) {
                    $otrosPagos =  $xml->createElement("nomina12:OtroPago");
                    $otrosPagos = $OtroPago->appendChild($otrosPagos);
                    $cadena_original .= $this->cargaAtt(
                        $otrosPagos,
                        array(
                            "TipoOtroPago" => $ArrayOtrosPag_TipoOtroPago[$i],
                            "Clave" => $ArrayOtrosPag_Clave[$i],
                            "Concepto" => $ArrayOtrosPag_Concepto[$i],
                            "Importe" => $ArrayOtrosPag_Importe[$i]
                        )
                    );
                }

                $SubsidioAlEmpleo = $xml->createElement("nomina12:SubsidioAlEmpleo");
                $SubsidioAlEmpleo = $otrosPagos->appendChild($SubsidioAlEmpleo);
                $cadena_original .= $this->cargaAtt(
                    $SubsidioAlEmpleo,
                    array(
                        "SubsidioCausado" => number_format($subsidio_causado, 2, '.', '')
                    )
                );
            }
        }

        #== 11.6 Termina de conformarse la "Cadena original" con doble ||
        $cadena_original .= "|";
        $tmp = $SendaCFDI . "CadenaOriginal_RecNom_" . $numerico . "_" . $NoFac . ".txt";

        $file = fopen($SendaPreCFDI . "CadenaOriginal_RecNom_" . $numerico . "_" . $NoFac . ".txt", "w");
        fwrite($file, $cadena_original . PHP_EOL);
        fclose($file);
        chmod($SendaPreCFDI . "CadenaOriginal_RecNom_" . $numerico . "_" . $NoFac . ".txt", 0777);
        #=== Muestra la cadena original (opcional a mostrar) =======================
        $data_respuesta['cadena_original'] = $cadena_original;

        #== 11.8 Proceso para obtener el sello digital del archivo .pem.key ========
        $keyid = openssl_get_privatekey(file_get_contents($SendaPEMS . $file_key));
        openssl_sign($cadena_original, $crypttext, $keyid, OPENSSL_ALGO_SHA256);
        //openssl_free_key($keyid);

        #== 11.9 Se convierte la cadena digital a Base 64 ==========================
        $sello = base64_encode($crypttext);
        $data_respuesta['sello'] = $sello;

        #== 11.10 Proceso para extraer el certificado del sello digital ============
        $file = $SendaPEMS . $file_cer;      // Ruta al archivo
        $datos = file($file);
        $certificado = "";
        $carga = false;
        for ($i = 0; $i < sizeof($datos); $i++) {
            if (strstr($datos[$i], "END CERTIFICATE")) $carga = false;
            if ($carga) $certificado .= trim($datos[$i]);
            if (strstr($datos[$i], "BEGIN CERTIFICATE")) $carga = true;
        }

        #=== Muestra el certificado del sello digital (opcional a mostrar) =========
        $data_respuesta['certificado'] = $certificado;

        #== 11.12 Se continua con la integración de nodos ===========================
        $root->setAttribute("Sello", $sello);
        $root->setAttribute("Certificado", $certificado);   # Certificado.

        #== Fin de la integración de nodos =========================================

        #=== 11.12 Se guarda el archivo .XML antes de ser timbrado =======================
        $NomArchPreCFDI = $SendaPreCFDI . "PreCFDI-33_RecNom_" . $numerico . "_" . $NoFac . ".xml";

        $cfdi = $xml->saveXML();
        $xml->formatOutput = true;
        $xml->save($NomArchPreCFDI); // Guarda el archivo .XML (sin timbrar) en el directorio predeterminado.
        unset($xml);
        $data_respuesta['NomArchPreCFDI'] = $NomArchPreCFDI;
        #=== 11.13 Se dan permisos de escritura al archivo .xml. =========================
        chmod($NomArchPreCFDI, 0777);

        ##### FIN DE LA CREACIÓN DEL ARCHIVO .XML ANTES DE SER TIMBRADO ####################################################

        ### 12. PROCESO DE TIMBRADO ########################################################
        $xmlaEnviar = htmlspecialchars($cfdi);
        #=== Se muestra el .XML antes de ser timbrado (opcional a mostrar)==========
        $data_respuesta['cfdi'] = $cfdi;
        $data_respuesta['xml_enviar'] = $xmlaEnviar;

        #== 12.1 Se crea una variable de tipo DOM y se le carga el CFDI =================================
        $xml2 = new \DOMDocument();
        $xml2->loadXML($cfdi);

        #== 12.2 Convirtiendo el contenido del CFDI a BASE 64 ======================
        $xml_cfdi_base64 = base64_encode($cfdi);

        #== 12.3 Datos de acceso al servicio (PAC) ============================
        $process  = curl_init($ip_servicio);

        #== 12.4 Creando el SOAP de envío ==============================================
        $cfdixml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:ns0="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ns1="http://facturacion.finkok.com/stamp"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header/>
    <ns0:Body>
        <ns1:stamp>
            <ns1:xml>$xml_cfdi_base64</ns1:xml>
            <ns1:username>$username</ns1:username>
            <ns1:password>$password</ns1:password>
        </ns1:stamp>
    </ns0:Body>
</SOAP-ENV:Envelope>
XML;
        #== 12.6 Proceso para guardar los datos que se envían al servidor en un archivo .XML ========================
        $NomArchSoap = $SendaPreCFDI . "DatosEnvio_RecNom_" . $numerico . "_" . $NoFac . ".xml";
        #== 12.6.1 Si el archivo ya se encuentra se elimina ===========================
        if (file_exists($NomArchSoap) == true) {
            unlink($NomArchSoap);
        }
        #== 12.6.2 Se crea el archivo .XML con el SOAP ================================
        $fp = fopen($NomArchSoap, "a");
        fwrite($fp, $cfdixml);
        fclose($fp);
        chmod($NomArchSoap, 0777);

        #=== 12.7 Muestra el contenido del SOAP que se envía al servidor del PAC (REQUEST) =========================
        $data_respuesta['soap'] = htmlspecialchars($cfdixml);

        #== 12.8 Se envía el contenido del SOAP al servidor del PAC =====================
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', ' charset=utf-8'));
        curl_setopt($process, CURLOPT_POSTFIELDS, $cfdixml);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
        $RespServ = curl_exec($process);

        #== 12.9 Se muestra la respuesta del servidor del PAC (opcional a mostrar) ================
        $respuestaPac = htmlspecialchars($RespServ);
        $data_respuesta['respuesta'] = $respuestaPac;
        curl_close($process);
        ## FIN DEL PROCESO DE TIMBRADO #################################################

        ## 13. PROCESOS POSTERIORES AL TIMBRADO ########################################

        #== 13.1 Se asigna la respuesta del servidor a una variable de tipo DOM ====
        $VarXML = new \DOMDocument();
        $VarXML->loadXML($RespServ);

        #== 13.2 Se graba la respuesta del servidor a un archivo .xml
        $VarXML->save($SendaRESP . "RespServ_RecNom_" . $numerico . "_" . $NoFac . ".xml");
        chmod($SendaRESP . "RespServ_RecNom_" . $numerico . "_" . $NoFac . ".xml", 0777);

        #== 13.3 Se asigna el contenido del tag "xml" a una variable ===============
        $RespServ = $VarXML->getElementsByTagName('xml');

        #== 13.4 Se obtiene el valor del nodo ======================================
        $valor_del_nodo = "";
        foreach ($RespServ as $Nodo) {
            $valor_del_nodo = $Nodo->nodeValue;
        }
        #== Si el nodo contiene datos se realizan los siguientes procesos ======
        $exito = false;
        if ($valor_del_nodo != "") {
            $exito = true;
            #== 13.5 Se muestra el .XML ya timbrado (CFDI V 3.2), opcional a mostrar =====
            htmlspecialchars($Nodo->nodeValue);
            $data_respuesta['xml_timbrado'] = htmlspecialchars($Nodo->nodeValue);

            #=== 13.6 Guardando el CFDI en archivo .XML  ============================
            $NomArchXML = "CFDI-33_RecNom_" . $numerico . "_" . $NoFac . ".xml";
            $NomArchPDF = "CFDI-33_RecNom_" . $numerico . "_" . $NoFac . ".pdf";

            $xmlt = new \DOMDocument();
            $xmlt->loadXML($valor_del_nodo);
            $xmlt->save($SendaCFDI . $NomArchXML);
            chmod($SendaCFDI . $NomArchXML, 0777);

            #== 13.7 Procesos para extraer datos del Timbre Fiscal del CFDI =========
            $docXML = new \DOMDocument();
            if (\PHP_VERSION_ID < 80000) { 
                libxml_disable_entity_loader(false);    
            }
            $docXML->load($SendaCFDI . "CFDI-33_RecNom_" . $numerico . "_" . $NoFac . ".xml", LIBXML_NOWARNING);

            $params = $docXML->getElementsByTagName("Comprobante");
            foreach ($params as $param) {
                $VersionCFDI = $param->getAttribute("Version");
            }

            $comprobante = $docXML->getElementsByTagName("TimbreFiscalDigital");

            #== 13.8 Se obtienen contenidos de los atributos y se asignan a variables para ser mostrados =======
            foreach ($comprobante as $timFis) {
                $version_timbre = $timFis->getAttribute('Version');
                $sello_SAT      = $timFis->getAttribute('SelloSAT');
                $cert_SAT       = $timFis->getAttribute('NoCertificadoSAT');
                $sello_CFD      = $timFis->getAttribute('SelloCFD');
                $tim_fecha      = $timFis->getAttribute('FechaTimbrado');
                $tim_uuid       = $timFis->getAttribute('UUID');

                $data_respuesta['version_cfdi']   = $VersionCFDI;
                $data_respuesta['version_timbre'] = $version_timbre;
                $data_respuesta['sello_sat']      = $sello_SAT;
                $data_respuesta['cert_sat']       = $cert_SAT;
                $data_respuesta['sello_cfd']      = $sello_CFD;
                $data_respuesta['fecha_tim']      = $tim_fecha;
                $data_respuesta['timbre_uuid']    = $tim_uuid;
            }

            #== 13.8.1 Se muestra el número de factura asignado por el sistema local (no asingado por el SAT).
            $data_respuesta['no-fac'] = $NoFac;
            $params = $docXML->getElementsByTagName('Emisor');
            foreach ($params as $param) {
                $Emisor_RFC = $param->getAttribute('Rfc');
            }

            $params = $docXML->getElementsByTagName('Receptor');
            foreach ($params as $param) {
                $Receptor_RFC = $param->getAttribute('Rfc');
            }

            $params = $docXML->getElementsByTagName('Comprobante');
            foreach ($params as $param) {
                $total = $param->getAttribute('Total');
            }

            #== 13.9 Se crea el archivo .PNG con codigo bidimensional =================================

            $archivoQR = $SendaQR . $tim_uuid . ".png";
            $urlQR = $SendaQR_url . $tim_uuid . ".png";
            $CadImpTot = $this->ProcesImpTot($total);
            $cadenaSello = substr($sello_CFD, -8);
            $cadena = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $tim_uuid . "&re=" . $Emisor_RFC . "&rr=" . $Receptor_RFC . "&tt=" . $CadImpTot . "&fe=" . $cadenaSello;
            //QrCode::format('png')->size(219)->errorCorrection('H')->generate($cadena,$archivoQR);
            \QRcode::png($cadena, $archivoQR, 'H', 3, 2);

            $data_respuesta['qr'] = $tim_uuid . '.png';
            $data_respuesta['qr_url'] = $urlQR;

            $data_respuesta['archivo_xml'] = $NomArchXML;
            $data_respuesta['archivo_pdf'] = $NomArchPDF;

            #== 13.10 Se crea código HTML para mostrar opciones al usuario.

            $respuestaPaccon = addslashes($respuestaPac);
            $facturaD = new TimbradoAsimilados;

            $totalreal = round($neto_fiscal, 2);
            $FechaEmiTimbrado = date("Y-m-d") . "T" . date("H:i:s");
            //$facturaD->id;
            $facturaD->id_empleado      = $id_empleado;
            $facturaD->id_periodo       = $id_periodo;
            $facturaD->fecha_timbrado   = $tim_fecha;
            $facturaD->sello_sat        = $sello_SAT;
            $facturaD->certificado_sat  = $cert_SAT;
            $facturaD->sello_cfdi       = $sello_CFD;
            $facturaD->folio_fiscal     = $tim_uuid;
            $facturaD->xml_enviado      = $xmlaEnviar;
            $facturaD->num_factura      = $NoFac;
            $facturaD->respuesta_pac    = $respuestaPaccon;
            $facturaD->cadena_original  = $cadena_original;
            $facturaD->file_pdf         = $NomArchPDF;
            $facturaD->file_xml         = $NomArchXML;
            $facturaD->importe          = $totalreal;
            $facturaD->receptor         = $rfc_empleado;
            $facturaD->emisor           = $rfc_emisor;
            $facturaD->certificado_tim  = $no_certificado;
            $facturaD->fecha_emision    = $FechaEmiTimbrado;
            $facturaD->num_dias_pagados  = $dias_a_pagar;
            $facturaD->estatus_timbre   = 1;

            $facturaD->save();

            $data_respuesta['tipo_c'] = 0;
            $data_respuesta['id_repo'] = Session::get('empresa')['id'];
            $data_respuesta['id_usuario'] = $id_empleado;
            $data_respuesta['id_periodo'] = $id_periodo;
            $data_respuesta['c_cadena'] = $cadena_departamentos;

            $data_respuesta['fecha_tim'] = $FechaEmiTimbrado;
            $data_respuesta['error'] = false;

            $data_respuesta['id_timbre'] = 0;
        } else {
            $exito = false;
            #== 13.11 En caso de error de timbrado se muestran los detalles al usuario.            
            $valorNod = "";
            $valorNod2 = "";
            $codigoError = $VarXML->getElementsByTagName('CodigoError');
            foreach ($codigoError as $NodoStatus) {
                $valorNod = $NodoStatus->nodeValue;
            }
            $codigoMsg = $VarXML->getElementsByTagName('MensajeIncidencia');
            foreach ($codigoMsg as $NodoStatus) {
                $valorNod2 = $NodoStatus->nodeValue;
            }

            $FechaEmiTimbrado = date("Y-m-d") . "T" . date("H:i:s");

            $data_respuesta['error']         = true;
            $data_respuesta['xml_enviar']    = $xmlaEnviar;
            $data_respuesta['respuesta']     = $respuestaPac;
            $data_respuesta['codigo_error']  = $valorNod;
            $data_respuesta['MENSAJE_error'] = $valorNod2;
            $data_respuesta['fecha_tim'] = $FechaEmiTimbrado;

            $xmlaEnviar = addslashes($xmlaEnviar);
            $respuestaPaccon = addslashes($respuestaPac);

            $facturaD = new TimbradoAsimilados;
            $facturaD->id_empleado      = $id_empleado;
            $facturaD->id_periodo       = $id_periodo;
            $facturaD->fecha_timbrado   = $FechaEmiTimbrado;
            $facturaD->sello_sat        = 'error';
            $facturaD->certificado_sat  = 'error';
            $facturaD->sello_cfdi       = 'error';
            $facturaD->folio_fiscal     = 'error';
            $facturaD->xml_enviado      = $xmlaEnviar;
            $facturaD->num_factura      = $NoFac;
            $facturaD->respuesta_pac    = $respuestaPaccon;
            $facturaD->cadena_original  = $cadena_original;
            $facturaD->estatus_timbre   = 2;

            $facturaD->save();

            $data_respuesta['id_timbre'] = $facturaD->id;
        }
        ##### FIN DE PROCEDIMIENTOS ####################################################    
        //dump($data_respuesta);    
        if ($tipoR == 1) {
            return view('contabilidad.timbrado.empleado_result_asimilados', compact('data_respuesta'));
        } else {
            $rr['qr_url'] = $data_respuesta['qr_url'];
            $rr['qr'] = $data_respuesta['qr'];
            $rr['archivo_pdf'] = $data_respuesta['archivo_pdf'];
            $rr['archivo_xml'] = $data_respuesta['archivo_xml'];
            $rr['no_factura'] = $data_respuesta['no_factura'];
            $rr['archivo_xml'] = $data_respuesta['archivo_xml'];
            if ($data_respuesta['error'] == true) {
                $rr['error']         = $data_respuesta['error'];
                $rr['respuesta']     = $data_respuesta['respuesta'];
                $rr['codigo_error']  = $data_respuesta['codigo_error'];
                $rr['MENSAJE_error'] = $data_respuesta['MENSAJE_error'];
            }
            return response()->json(['exito' => $exito, 'data' => $rr]);
        }
    } 

    public function timbrarAsimilados(Request $request)
    {
        $base = Session::get('base');

        cambiarBaseA($base);

        $todos = $request->input('todos') ?? false;
        $departamentos = $request->deptos;
        $repo = Session::get('empresa')['id'];
        if (is_array($departamentos)) {
            $cadena_departamentos = implode(",", $departamentos);

        } else {
            $cadena_departamentos = $departamentos;
        }
        /* periodo */
        //$id_periodo = $request->id_periodo;
        $periodo = periodosNomina::where('activo', 1)->first();
        $id_periodo  = $periodo->id;

        /*
        $id_periodo = 100;        
        $periodo = periodosNomina::find($id_periodo); // Periodo para probar 
        */
        $tipo_nomina = $periodo->nombre_periodo;
        $nombre_periodo = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;
        $empleados = array();
        $query = "SELECT *
                  FROM empleados
                  WHERE estatus = 1
                  AND tipo_de_nomina = '$tipo_nomina'
                  and id_departamento in ($cadena_departamentos)
                  AND id IN (
                      SELECT id_empleado 
                      FROM rutinas$ejercicio 
                      WHERE id_periodo = '$id_periodo' 
                      AND fnq_valor = 0 
                      AND neto_sindical > 0);
                  ";
                  //dd($query);
        $emple = DB::connection('empresa')->select($query);

        /* Verificamos si ya existen timbrados para el periodo */
        $r = DB::connection('empresa')
            ->table('timbrado_asimilados')
            ->where('id_periodo', $id_periodo)
            ->where('sello_sat', '<>', 'error')
            ->get()
            ->count();
        $existen_timbrados = $r;
        /* PROCESAMOS LOS EMPLEADOS */
        foreach ($emple as $e) {
            $query = "SELECT neto_sindical
                      FROM rutinas$ejercicio
                      WHERE id_empleado = $e->id
                      AND id_periodo = $id_periodo
                      AND fnq_valor = 0;";
            /* Sacamos sus importe */
            $r = DB::connection('empresa')->select($query);
            $e->importe_fiscal = round($r[0]->neto_sindical, 2);
            //$importeFiscal=round($rowresultFiscal['NetoFiscal'],2);

            /* 
                01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                $numRegistros -> original
                Sacamos tambien el estatus del timbre:
                0 = ?
                1 = Timbrado
                2 = Error
            */
            $query2 = "SELECT * 
                     FROM timbrado_asimilados 
                     WHERE id_empleado = '$e->id' 
                     AND id_periodo = '$id_periodo' 
                     AND estatus_timbre = 1";
            $r = DB::connection('empresa')->select($query2);
            $e->timbres = $r;

            /*
              02.- Si hay timbres no error //Normalmente es 1
              $numRegistrosreTimbre -> original
            */
            $r = DB::connection('empresa')
                ->table('timbrado_asimilados')
                ->where('id_empleado', $e->id)
                ->where('id_periodo', $id_periodo)
                ->where('sello_sat', '<>', 'error')
                ->get()
                ->count();
            $e->numero_timbres_noerror = $r;
            /* 
               03.- traemos el ultimo registro de  timbre 
               $numRegistrosreTimbreError -> original
            */
            /*
            $r = DB::connection('generica')
            ->table('timbrado')
            ->where('id_empleado',$e->id)
            ->where('id_periodo',$id_periodo)
            ->orderBy('id','desc')
            ->first();
            $e->ultimo_timbre = $r; 
*/
            $r = DB::connection('empresa')
                ->table('timbrado_asimilados')
                ->where('id_empleado', $e->id)
                ->where('id_periodo', $id_periodo)
                ->orderBy('id', 'desc')->get();

            $e->ultimo_timbre = [];
            if (count($r) > 0) {
                $e->ultimo_timbre = $r[0];

                $e->ultimo_timbre_sello = $e->ultimo_timbre->sello_sat;
            }
            /* 
              04.-timbres cancelados 
            */
            $r = DB::connection('empresa')
                ->table('timbrado_cancelaciones_asimilados')
                ->where('id_empleado', $e->id)
                ->where('id_periodo', $id_periodo)
                ->get();
            $e->timbres_cancelados = $r;


            $empleados[] = $e;
        }

        //dd($request->all(),$periodo,$id_periodo,$todos,$cadena_departamentos,$empleados);
        // dd($periodo,$cadena_departamentos,$empleados,$existen_timbrados);

        //echo "SELECT * FROM $base.empleado WHERE Status=0 and tipodeNomina='$TipoNomina' and departamento in ($cadena_departamentos)";
        $tipo = 0;
        return view('contabilidad.timbrado.asimilados_lista', compact('periodo', 'empleados', 'existen_timbrados', 'cadena_departamentos', 'repo', 'tipo'));
    }

    /* TIMBRAR de FORMA MASIVA*/
    public function validar_masivo_asimilados($id_periodo, $cadena)
    {
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $array_jornada = array(
            '01' => 'DIURNA',
            '02' => 'NOCTURNA',
            '03' => 'MIXTA',
            '04' => 'POR HORA',
            '05' => 'REDUCIDA',
            '06' => 'CONTINUADA',
            '07' => 'PARTIDA',
            '08' => 'POR TURNOS',
            '99' => 'OTRA JORNADA',
            '1' => 'DIURNA',
            '2' => 'NOCTURNA',
            '3' => 'MIXTA',
            '4' => 'POR HORA',
            '5' => 'REDUCIDA',
            '6' => 'CONTINUADA',
            '7' => 'PARTIDA',
            '8' => 'POR TURNOS',
        );
        $array_contrato = array(
            '01' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
            "02" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
            "03" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
            "04" => 'CONTRATO DE TRABAJO POR TEMPORADA',
            "05" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
            "06" => 'Contrato de trabajo con capacitación inicial',
            "07" => 'Modalidad de contratación por pago de hora laborada',
            "08" => 'Modalidad de trabajo por comisión laboral',
            "09" => 'Modalidades de contratación donde no existe relación de trabajo',
            "10" => 'JUBILACIÓN, PENSIÓN, RETIRO',
            "99" => 'OTRO',
            '1' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
            "2" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
            "3" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
            "4" => 'CONTRATO DE TRABAJO POR TEMPORADA',
            "5" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
            "6" => 'Contrato de trabajo con capacitación inicial',
            "7" => 'Modalidad de contratación por pago de hora laborada',
            "8" => 'Modalidad de trabajo por comisión laboral',
            "9" => 'Modalidades de contratación donde no existe relación de trabajo',
        );

        /* VALIDACION PREVIA DE USUARIOS'*/
        $base = Session::get('base');
        cambiarBaseA($base);
        $cadena_departamentos = $cadena;
        /* periodo */
        //$periodo = periodosNomina::where('activo', 1)->first();        
        $periodo = periodosNomina::find($id_periodo); // Periodo para probar 
        $id_periodo  = $periodo->id;

        $ejercicio   = $periodo->ejercicio;
        $tipo_nomina = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;
        /* TRAEMOS LOS EMPLEADOS A VALIDAD */
        $empleados = array();
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND tipo_de_nomina = '$tipo_nomina'
                  and id_departamento in ($cadena_departamentos)
                  AND id IN (
                      SELECT id_empleado 
                      FROM rutinas$ejercicio 
                      WHERE id_periodo = '$id_periodo' 
                      AND fnq_valor = 0 
                      AND neto_fiscal > 0);
                 ";
        $emple = DB::connection('empresa')->select($query);
        /*
        while($rowresulEmple=mysqli_fetch_array($rowempleado)){
            $idempleado=$rowresulEmple['idempleado'];
            $NameCompleto=$rowresulEmple['NameCompleto'];
            
            $rfc=$rowresulEmple['rfc'];
            $numSeguroSocial=$rowresulEmple['numsegurosocial'];
            $curp=$rowresulEmple['curp'];
            */
        foreach ($emple as $e) {
            $errores = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
            $empleado = array(
                'id'     => $e->id,
                'nombre' => $e->nombre_completo,
                'rfc'    => $e->rfc,
                'curp'   => $e->curp,
                'nss'    => $e->nss,
            );

            //validar RFC/NSS/CURP
            if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
                $errores['rfc'] = true;
                $num_errores++;
            }

            if (!preg_match("/^[0-9]+$/", $e->nss)) {
                $errores['nss'] = true;
                $num_errores++;
            }

            if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
                $errores['curp'] = true;
                $num_errores++;
            }

            //VALIDAR REGISTRO PATRONAL
            $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                                FROM categorias cat  
                                JOIN empleados em  
                                ON em.id_categoria_asimilados = cat.id 
                                INNER JOIN singh.registro_patronal re 
                                ON cat.tipo_clase = re.id
                                WHERE em.id = '$e->id' 
                                AND   em.tipo_de_nomina='$tipo_nomina' 
                                AND   em.id_departamento in ($cadena_departamentos) 
                                AND   cat.estatus = 1";
            $registroP = DB::connection('empresa')->select($queryPatronal);
            // $rowresultregistro=mysqli_fetch_array($rowregisstropatronal);
            // $TipoClas=$rowresulTipoClase['TipodeClase'];
            //$TipoJornada=$rowresulTipoClase['tipojornada'];
            //$TipoContrato=$rowresulTipoClase['tipocontrato'];
            //dd($registroP);
            if ($registroP) {
                $r = $registroP[0];
                $r->tipo_jornada = ($r->tipo_jornada == "DUIRNA" || $r->tipo_jornada == "DIURNA") ? "01" : $r->tipo_jornada;

                $r->string_contrato = $array_contrato[$r->tipo_contrato];
                $r->string_jornada = $array_jornada[$r->tipo_jornada];
            } else {
                $r = "error";
                $errores['registro_patronal'] = true;
                $num_errores++;
            }
            $empleado['registro_patronal'] = $r;
            $empleado['errores'] = $errores;
            $empleados[] = $empleado;
        }

        /* Validación de conceptos de nomina */
        $queryConceptos = "SELECT * 
                               FROM conceptos_nomina 
                               WHERE nomina = 1 
                               AND  activo_en_nomina = 1 
                               AND  rutinas='ASIMILADOS'";
        $concep = DB::connection('empresa')->select($queryConceptos);
        $conceptos = array();
        foreach ($concep as $c) {
            $errores = array('sat' => false);
            $concepto = array(
                'id'         => $c->id,
                'nombre'     => $c->nombre_concepto,
                'codigo_sat' => $c->codigo_sat,
            );
            // $codigosat=$rowresultconceptos['codigosat'];
            // $idconcepto=$rowresultconceptos['idconcepto'];
            // $NombreConcepto=$rowresultconceptos['NombreConcepto'];
            if ($c->codigo_sat == NULL) {
                $errores['sat'] = true;
                $num_errores_conceptos++;
            }
            $concepto['errores'] = $errores;
            $conceptos[] = $concepto;
        }
        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);
        //dd($id_periodo, $cadena,$empleados,$conceptos);
        //dd($cadena_departamentos);
        return view('contabilidad.timbrado.validacion_masivo_asimilados', compact('periodo', 'empleados', 'conceptos', 'cadena_departamentos', 'errores'));
    }

    public function timbrarNomina(Request $request){       
        cambiarBase(Session::get('base'));  
        $todos = $request->input('todos') ?? false;
        $departamentos = $request->deptos;
        $repo = Session::get('empresa')['id'];  
        if (is_array($departamentos)) {
            $cadena_departamentos = implode(",", $departamentos);
        } else {
            $cadena_departamentos = $departamentos;
        }
        /* periodo */         
        $periodo = PeriodosNomina::where('activo', 1)->first();       
        $id_periodo  = 0;
        $tipo_nomina = '';
        $nombre_periodo = '';
        $ejercicio = 0;
        $empleados = [];     
        foreach ($periodo as $key => $p) {
            $id_periodo  = $periodo->id;
            $tipo_nomina = $periodo->nombre_periodo;
            $nombre_periodo = $periodo->nombre_periodo;
            $ejercicio = $periodo->ejercicio;            
            $query = "SELECT *
                    FROM empleados
                    WHERE estatus = 1
                    AND tipo_de_nomina = '".$tipo_nomina."' 
                    and id_departamento in (".$cadena_departamentos.")
                    AND id IN (
                        SELECT id_empleado 
                        FROM rutinas".$ejercicio." 
                        WHERE id_periodo = ".$id_periodo." 
                        AND fnq_valor = 0 
                        AND neto_fiscal > 0);
                    "; 
            $datos_empleados = DB::connection('empresa')->select($query);   
            
            /* Verificamos si ya existen timbrados para el periodo */
            $r = DB::connection('empresa')
                ->table('timbrado')
                ->where('id_periodo', $id_periodo)
                ->where('sello_sat', '<>', 'error')
                ->get()
                ->count();
            $existen_timbrados = $r;
            /* PROCESAMOS LOS EMPLEADOS */
            foreach ($datos_empleados as $e) {
                $query = "SELECT neto_fiscal
                        FROM rutinas".$ejercicio."
                        WHERE id_empleado = ".$e->id."
                        AND id_periodo = ".$id_periodo."
                        AND fnq_valor = 0;";
                /* Sacamos sus importe */
                $r = DB::connection('empresa')->select($query);
                $e->importe_fiscal = round($r[0]->neto_fiscal, 2);
                /* 
                    01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                    $numRegistros -> original
                    Sacamos tambien el estatus del timbre:
                    0 = ?
                    1 = Timbrado
                    2 = Error
                */
                $query2 = "SELECT * 
                        FROM timbrado 
                        WHERE id_empleado = ".$e->id."
                        AND id_periodo = ".$id_periodo."
                        AND estatus_timbre = 1";
                $r = DB::connection('empresa')->select($query2);
                $e->timbres = $r;

                /*
                02.- Si hay timbres no error //Normalmente es 1
                $numRegistrosreTimbre -> original
                */
                $r = DB::connection('empresa')
                    ->table('timbrado')
                    ->where('id_empleado', $e->id)
                    ->where('id_periodo', $id_periodo)
                    ->where('sello_sat', '<>', 'error')
                    ->get()
                    ->count();
                $e->numero_timbres_noerror = $r;
                /* 
                03.- traemos el ultimo registro de  timbre 
                $numRegistrosreTimbreError -> original
                */               
                $r = DB::connection('empresa')
                    ->table('timbrado')
                    ->where('id_empleado', $e->id)
                    ->where('id_periodo', $id_periodo)
                    ->orderBy('id', 'desc')->get();

                $e->ultimo_timbre = [];
                if (count($r) > 0) {
                    $e->ultimo_timbre = $r[0];

                    $e->ultimo_timbre_sello = $e->ultimo_timbre->sello_sat;
                }
                /* 
                04.-timbres cancelados 
                */
                $r = DB::connection('empresa')
                    ->table('timbrado_cancelaciones')
                    ->where('id_empleado', $e->id)
                    ->where('id_periodo', $id_periodo)
                    ->get();
                $e->timbres_cancelados = $r;
                $empleados[] = $e;                
            }
            break;
        }      
        $tipo = 0;    

        return view('procesos.timbrado-nomina.nomina-lista', compact('periodo', 'empleados', 'existen_timbrados', 'cadena_departamentos', 'repo', 'tipo'));
    }

    public function validarEmpleado($id_empleado, $cadena, $valorPeriodo, $regresa)
    {
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $cadena_departamentos = base64_decode($cadena);      
        cambiarBase(Session::get('base'));      
        /* periodo */
        $periodo = PeriodosNomina::where('activo', 1)->first();
        $id_periodo  = $periodo->id;       
        //$id_periodo  = 100;
        //$periodo     = PeriodosNomina::find($id_periodo); // Periodo para probar 
        $ejercicio   = $periodo->ejercicio;
        $tipo_nomina = $periodo->nombre_periodo;
        $ejercicio   = $periodo->ejercicio;
        /* TRAEMOS LOS EMPLEADOS A VALIDAD */
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND id = ".$id_empleado.";";
        $e = DB::connection('empresa')->select($query);
        $e = $e[0];

        $errores  = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
        $empleado = array(
            'id'     => $e->id,
            'nombre' => $e->nombre_completo,
            'rfc'    => $e->rfc,
            'curp'   => $e->curp,
            'nss'    => $e->nss,
        );

        //validar RFC/NSS/CURP
        if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
            $errores['rfc'] = true;
            $num_errores++;
        }

        if (!preg_match("/^[0-9]+$/", $e->nss)) {
            $errores['nss'] = true;
            $num_errores++;
        }

        if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
            $errores['curp'] = true;
            $num_errores++;
        }

        //VALIDAR REGISTRO PATRONAL
        $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = ".$id_empleado." 
                        AND   cat.estatus = 1";
        $registroP = DB::connection('empresa')->select($queryPatronal);

        if ($registroP) {
            $r = $registroP[0];
            $XXX = ($r->tipo_jornada == "DUIRNA" || $r->tipo_jornada == "DIURNA") ? 1 : $r->tipo_jornada;
            $r->string_contrato = $this->array_contrato[$r->tipo_contrato];
            $r->string_jornada = $this->array_jornada[$XXX];
        } else {
            $r = "error";
            $errores['registro_patronal'] = true;
            $num_errores++;
        }
        $empleado['registro_patronal'] = $r;
        $empleado['errores'] = $errores;

        /* Validación de conceptos de nomina */
        $queryConceptos = "SELECT * 
                            FROM conceptos_nomina 
                            WHERE nomina = 1 
                            AND  activo_en_nomina = 1 
                            AND  tipo <> 3 
                            AND  estatus <> 2 
                            AND  file_rool <> 0 
                            AND  file_rool < 250";
        $concep = DB::connection('empresa')->select($queryConceptos);
        $conceptos = array();
        foreach ($concep as $c) {
            $errores = array('sat' => false);
            $concepto = array(
                'id'         => $c->id,
                'nombre'     => $c->nombre_concepto,
                'codigo_sat' => $c->codigo_sat,
            );
            if ($c->codigo_sat == NULL) {
                $errores['sat'] = true;
                $num_errores_conceptos++;
            }
            $concepto['errores'] = $errores;
            $conceptos[] = $concepto;
        }

        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);
        return view('procesos.timbrado-nomina.validacion-empleado', compact('periodo', 'empleado', 'conceptos', 'cadena_departamentos', 'errores', 'regresa', 'valorPeriodo'));
    }

    public function timbrar_nomina_empleado($id_empleado, $cadena, $tipoR, $regresa){
        //Corresponde al php 04_CFDI_ReciboNomina.php
        $numerico = rand(0, 99999);    
        $base = Session::get('base');
        $empresa_cliente = Session::get('empresa');
        cambiarBase($base);

        if($base == 'empresa000049')
            date_default_timezone_set('America/Mazatlan');
        else
            date_default_timezone_set('America/Mexico_City');

        $data_respuesta = array();

        //verificamos el periodo activo
        $cadena_departamentos = base64_decode($cadena);
        $periodo      = PeriodosNomina::where('activo', 1)->first();

        $id_periodo   = $periodo->id; 
        $ejercicio    = $periodo->ejercicio;
        $tipo_nomina  = $periodo->nombre_periodo;
        $fecha_inicial_periodo = new \DateTime($periodo->fecha_inicial_periodo);
        $fecha_final_periodo   = new \DateTime($periodo->fecha_final_periodo);

        $fecha_inicial_periodoStr = $periodo->fecha_inicial_periodo;
        $fecha_final_periodoStr   = $periodo->fecha_final_periodo;
        $fecha_pago = $periodo->fecha_pago;

        /* Convertimos el tipo de periodo a procesar */
        switch (strtoupper($tipo_nomina)) {
            case 'DIARIA':
                $tipo_periodo = '01';
                break;
            case 'SEMANAL':
                $tipo_periodo = '02';
                break;
            case 'CATORCENAL':
                $tipo_periodo = '03';
                break;
            case 'QUINCENAL':
                $tipo_periodo = '04';
                break;
            case 'MENSUAL':
                $tipo_periodo = '05';
                break;
            case 'ANUAL':
                $tipo_periodo = '99';
                break;
            default:
                $tipo_periodo = '99';
                break;
        }

        /* TRAEMOS EMISORA */
        $query = "SELECT em.id_categoria,em.tipo_jornada,em.tipo_contrato,cat.nombre,re.num_registro_patronal,re.tipo_clase,ememi.razon_social,ememi.user_timbre,ememi.cp
                 FROM $base.empleados em  
                 JOIN $base.categorias cat  
                 ON em.id_categoria = cat.id 
                 INNER JOIN singh.registro_patronal re 
                 ON cat.tipo_clase = re.id
                 INNER JOIN singh.empresas_emisoras ememi
                 ON re.id_empresa_emisora = ememi.id
                 WHERE em.id = '$id_empleado' 
                 AND   cat.estatus = 1
                 AND ememi.estatus =1
                 AND re.estatus = 1
                 AND em.estatus = 1;
                 ";

        $emisora = DB::connection('empresa')->select($query);
        $emisora = $emisora[0];

        $usr_timbre    = $emisora->user_timbre;
        $cp_emisora    = $emisora->cp;

        $tipo_clase    = null;

        $tipo_jornada  = str_pad($emisora->tipo_jornada, 2, '0', STR_PAD_LEFT);
        $tipo_contrato = str_pad($emisora->tipo_contrato, 2, '0', STR_PAD_LEFT);

        switch ($emisora->tipo_clase) {
            case 'Clase I':
                $tipo_clase = '1';
                break;
            case 'Clase II':
                $tipo_clase = '2';
                break;
            case 'Clase III':
                $tipo_clase = '3';
                break;
            case 'Clase IV':
                $tipo_clase = '4';
                break;
            case 'Clase V':
                $tipo_clase = '5';
                break;
        }

        /* TOTAL DE PERCEPCIONES */
        $query = " SELECT *
                  FROM rutinas$ejercicio
                  WHERE fnq_valor = 0
                  AND id_periodo = $id_periodo
                  AND id_empleado = $id_empleado";
        $datos = DB::connection('empresa')->select($query);
        $datos = $datos[0];

        $total_percep     = round($datos->total_percepcion_fiscal, 2);
        $total_deduc      = $datos->total_deduccion_fiscal;
        $subsidio_causado = $datos->subsidio_al_empleo;
        $neto_fiscal      = $datos->neto_fiscal;

        /* DATOS DEL EMPLEADO */
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND id = $id_empleado;
                 ";
        $e = DB::connection('empresa')->select($query);
        $e = $e[0];

        /* FECHAS EMPLEADO */
        $fecha_alta_empleado     = new \DateTime($e->fecha_alta);
        $rfc_empleado            = $e->rfc;
        $nombre_empleado = trim($e->nombre_completo);
        $num_empleado = $e->numero_empleado;
        $id_empleado = $e->id;

        /* ANTIGUEDAD (Diferencia de dias entre la alta y el periodo) */
        $diff = $fecha_final_periodo->diff($fecha_alta_empleado);
        $antigue = floor(($diff->days + 1) / 7);
        $antiguedad = 'P' . $antigue . 'W';

        /* DIAS A PAGAR */
        if ($fecha_alta_empleado > $fecha_inicial_periodo) {
            /* Si el alta es despues de que inicio el periodo se paga los dias proporcionales */
            $diff = $fecha_final_periodo->diff($fecha_alta_empleado);
            $dias_pagados = $diff->days;
        } else {
            $diff = $fecha_final_periodo->diff($fecha_inicial_periodo);
            $dias_pagados = $diff->days;
        }

        /* DEDUCCIONES */
        $incapacidades_valor = ($datos->incapacidades == "" || $datos->incapacidades == null) ? 0 : $datos->incapacidades;
        $total_gravado_real  = $datos->total_gravado;

        /* FALTAS */
        $faltas_valor = 0;
        $query = "SELECT id
                   FROM conceptos_nomina 
                   WHERE nombre_concepto = 'FALTAS' 
                   AND estatus = 1";
        $f = DB::connection('empresa')->select($query);
        if (!empty($f)) {
            $f = $f[0]->id;
            $query = "SELECT Total$f AS valor_falta 
                      FROM rutinas$ejercicio
                      WHERE id_periodo = '$id_periodo'
                      AND id_empleado = '$id_empleado' 
                      AND fnq_valor = 0;";
            $ff = DB::connection('empresa')->select($query);
            $faltas_valor = ($ff[0]->valor_falta == "" || $ff[0]->valor_falta == null) ? 0 : $ff[0]->valor_falta;
        }

        /* DIAS A PAGAR */
        $dias_a_pagar = ceil(($dias_pagados + 1) - $incapacidades_valor - $faltas_valor);

        /* SUMA DE CONCEPTO A PAGAR */
        $arr_conceptos   = array();
        $str_conceptos = "";
        $queryC = "SELECT id,nombre_concepto
                   FROM  conceptos_nomina
                   WHERE tipo = 0 
                   AND nomina = 1 
                   AND estatus = 1 
                   AND file_rool<> 0 
                   AND file_rool < 251 
                   AND activo_en_nomina = 1";
        $idC = DB::connection('empresa')->select($queryC);

        foreach ($idC as $c) {
            $id = $c->id;
            $queryI = "SELECT Total$id AS result 
                       FROM rutinas$ejercicio
                       WHERE id_periodo = '$id_periodo'
                       AND id_empleado = '$id_empleado' 
                       AND fnq_valor = 0";
            $valorC = DB::connection('empresa')->select($queryI);

            if ($valorC[0]->result <= 0) {
                $arr_conceptos[$id] = $id;
                $str_conceptos .= "$id,";
            } else {
                $arr_conceptos[$id] = 0;
                $str_conceptos .= "0,";
            }
        }
        $str_conceptos = trim($str_conceptos, ',');

        /* SUMA DEDUCCIONES */
        $arr_deducciones = array();
        $str_deducciones = "";
        $queryD = "SELECT id,nombre_concepto
                    FROM  conceptos_nomina
                    WHERE tipo = 1 
                    AND nomina = 1 
                    AND estatus = 1 
                    AND file_rool<> 0 
                    AND file_rool < 251 
                    AND activo_en_nomina = 1";
        $idD = DB::connection('empresa')->select($queryD);
        
        foreach ($idD as $d) {
            $id = $d->id;
            $queryI = "SELECT Total$id AS result 
                       FROM rutinas$ejercicio
                       WHERE id_periodo = '$id_periodo'
                       AND id_empleado = '$id_empleado' 
                       AND fnq_valor = 0";
            $valorC = DB::connection('empresa')->select($queryI);

            if ($valorC[0]->result <= 0) {
                $arr_deducciones[$id] = $id;
                $str_deducciones .= "$id,";
            } else {
                $arr_deducciones[$id] = 0;
                $str_deducciones .= "0,";
            }
        }
        $str_deducciones = trim($str_deducciones, ',');

        # 1.1 Configuración de zona horaria
        $data_respuesta['timezone'] = date_default_timezone_get();

        ### 2. ASIGNACIÓN DE VALORES A VARIABLES ###################################################
        $data_respuesta['idrepo'] = Session::get('empresa')['id'] . '/' . $e->id;
        $url_repositorio = '/repositorio/' . Session::get('empresa')['id'] . '/' . $e->id . '/timbrado/'; 
        $acceso_repositorio = '';       
        $url_timbrado = 'timbrado/';

        $SendaCFDI     = $url_repositorio . 'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaPDF      = $url_repositorio . 'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).

        $SendaPEMS    = $url_timbrado."archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $url_timbrado.'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $url_timbrado."archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);    

        /* Checar si existen directorios, si no crearlos*/
        Storage::disk('public')->makeDirectory($SendaCFDI, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaPDF, $mode = 0777, true, true);

        /*Crear si no existen directorios de timbrado*/
        Storage::disk('public')->makeDirectory($SendaPEMS, $mode = 0777, true, true);  
        Storage::disk('public')->makeDirectory($SendaGRAFS, $mode = 0777, true, true);  
        Storage::disk('public')->makeDirectory($SendaXSD, $mode = 0777, true, true);  

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr($usr_timbre, 7, strlen($usr_timbre));
        $condicion = ($condicion == null) ? 1 : $condicion;

        $credenciales = TimbradoCredenciales::find($condicion);

        $rfc_emi    = $credenciales->rfc;
        $rfc_emisor = $rfc_emi;
        $razon_emisor = $credenciales->razon_social_ss;
        $regimen_fiscal_emisor = $credenciales->regimen_fiscal;
        $username    = base64_decode($credenciales->user);
        $password    = base64_decode($credenciales->password);
        $ip_servicio = $credenciales->servicio;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales->nombre_archivo . ".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales->nombre_archivo . ".key.pem";   // 3.3 Nombre del archivo .cer.key

        ### 4. DATOS GENERALES DE LA FACTURA ##################################################
        $fact_serie        = "A";                             // 4.1 Número de serie.
        $fact_folio        = mt_rand(1000, 9999);             // 4.2 Número de folio (para efectos de demostración se asigna de manera aleatoria).
        $NoFac             = $fact_serie . $fact_folio;         // 4.3 Serie de la factura concatenado con el número de folio.
        $data_respuesta['no_factura'] = $NoFac;
        ### PERCEPCIONES ###############################################################
        //TODO checar tipos en estatus y activo_en_nomina
        // ArraysPercepciones.
        $query = "SELECT id,nombre_concepto,SUBSTRING(codigo_sat,1,length(codigo_sat)-1) AS codigo_sat 
                FROM conceptos_nomina
                WHERE tipo = 0 
                AND  nomina = 1 
                AND  estatus = 1 
                AND  file_rool <> 0 
                AND  file_rool < 251
                AND  activo_en_nomina = 1 
                AND  id not in($str_conceptos)";
        $valorPC = DB::connection('empresa')->select($query);

        $numero_array_sat = count($valorPC);
        $numero_de_array = count($valorPC);
        /* ARRAY DE CONCEPTOS DE PERCEPCONES */
        $ArrayPercep_Concepto = array();
        $ArrayPercep_TipoPercepcion = array();
        $ArrayPercep_Clave = array();

        foreach ($valorPC as $c) {
            $ArrayPercep_Concepto[] = $c->nombre_concepto;
            $ArrayPercep_TipoPercepcion[] = $c->codigo_sat;
            if (strlen($c->id) < 3) {
                $ArrayPercep_Clave[] = str_pad($c->id, 3, "PP", STR_PAD_LEFT);
            } else {
                $ArrayPercep_Clave[] =  'PP' . $c->id;
            }
        }

        /* Importe gravado */
        /* VALOR GRAVADO */
        $total_percepciones = 0;
        $ArrayPercep_ImporteGravado = array();
        $queryGrav = "SELECT id,nombre_concepto 
                      FROM conceptos_nomina
                      WHERE tipo = 0 
                      AND   nomina = 1 
                      AND  estatus = 1 
                      AND  file_rool <> 0 
                      AND  file_rool < 251 
                      AND  activo_en_nomina = 1 
                      AND  id not in($str_conceptos)";

        $resultGrav = DB::connection('empresa')->select($queryGrav);

        foreach ($resultGrav as $r) {
            $id = $r->id;
            $qryImporte = "SELECT ROUND(gravado$id,2) AS result 
                           FROM rutinas$ejercicio
                           WHERE id_periodo ='$id_periodo'
                           AND   id_empleado = '$id_empleado' 
                           AND  fnq_valor = 0";
            $resultImp = DB::connection('empresa')->select($qryImporte);
            $ArrayPercep_ImporteGravado[] = $resultImp[0]->result;
            $total_percepciones += $resultImp[0]->result;
        }

        /* VALOR EXCENTO */
        $valor_exento = 0;
        $ArrayPercep_ImporteExento = array();
        $queryEx = "SELECT id,nombre_concepto
                  FROM conceptos_nomina
                  WHERE tipo = 0 
                  AND nomina = 1 
                  AND estatus = 1 
                  AND file_rool <> 0 
                  AND file_rool < 251 
                  AND activo_en_nomina = 1 
                  AND id not in($str_conceptos)";

        $resultEx = DB::connection('empresa')->select($queryEx);

        foreach ($resultEx as $r) {
            $id = $r->id;
            $qryImporte = "SELECT ROUND(excento$id,2) AS result 
                           FROM rutinas$ejercicio
                           WHERE id_periodo ='$id_periodo'
                           AND   id_empleado = '$id_empleado' 
                           AND  fnq_valor = 0";
            $resultImp = DB::connection('empresa')->select($qryImporte);
            $ArrayPercep_ImporteExento[] = $resultImp[0]->result;
            $valor_exento += $resultImp[0]->result;
        }

        ### DEDUCCIONES ################################################################
        $query = "SELECT id,nombre_concepto
                FROM conceptos_nomina
                WHERE tipo = 1 
                AND  nomina = 1 
                AND  estatus = 1 
                AND  file_rool <> 0 
                AND  file_rool < 251
                AND  activo_en_nomina = 1 
                AND  id not in($str_deducciones)";

        $valorDC = DB::connection('empresa')->select($query);
        $ArrayDeduc_Concepto = array();
        $ArrayDeduc_Clave    = array();
        $ArrayDeduc_Importe = array();
        $ArrayDeduc_TipoDeduccion  = array();
        $tiene_imss = 0;

        foreach ($valorDC as $rDeduc) {
            $concepto = $rDeduc->nombre_concepto;
            $idDed    = $rDeduc->id;
            if (strlen($idDed) < 3) {
                $idDed = str_pad($idDed, 3, "DD", STR_PAD_LEFT);
            } else {
                $idDed = 'DD' . $idDed;
            }

            if ($concepto == 'ISR') {
                $concepto = 'IMPUESTO SOBRE LA RENTA';
            }

            if ($concepto == 'IMSS' || $concepto == 'imss') {
                $tiene_imss = 1;
            }

            $ArrayDeduc_Concepto[] = $concepto;
            $ArrayDeduc_Clave[]    = $idDed;
        }

        $numDeducciones = count($valorDC);

        /* Cambio de funcion a devolver array plano*/
        $queryidDeduccion = "SELECT id,nombre_concepto
                             FROM conceptos_nomina
                             WHERE tipo = 1 
                             AND nomina = 1 
                             AND estatus = 1 
                             AND file_rool <> 0 
                             AND file_rool < 251
                             AND activo_en_nomina = 1 
                             AND id not in ($str_deducciones)";
        $valorDC = DB::connection('empresa')->select($queryidDeduccion);

        foreach ($valorDC as $id => $deduccion) {
            $idd = $deduccion->id;
            $qId = "SELECT  ROUND(Total$idd, 2) 
                   AS result 
                   FROM rutinas$ejercicio
                   WHERE id_periodo = '$id_periodo'
                   AND id_empleado = '$id_empleado' 
                   AND fnq_valor = 0;";

            $valoriDC = DB::connection('empresa')->select($qId);
            $ArrayDeduc_Importe[] = ($valoriDC[0]->result < 0)? $valoriDC[0]->result * -1 : $valoriDC[0]->result;

           
        }

        $queryidDeduccion = "SELECT id,nombre_concepto,codigo_sat
                             FROM conceptos_nomina
                             WHERE tipo = 1 
                             AND nomina = 1 
                             AND estatus = 1 
                             AND file_rool <> 0 
                             AND file_rool < 251
                             AND activo_en_nomina = 1 
                             AND id not in ($str_deducciones)";
        $valorIDDC = DB::connection('empresa')->select($queryidDeduccion);

        foreach ($valorIDDC as $id => $deduccion)
            $ArrayDeduc_TipoDeduccion[] = substr($deduccion->codigo_sat, 0, 3);

        /* ISR */
        $queryISR = "SELECT id 
                    FROM conceptos_nomina
                    WHERE tipo = 1 
                    AND nomina = 1 
                    AND estatus<>0 
                    AND file_rool<>0 
                    AND file_rool<251 
                    AND activo_en_nomina = 1
                    AND rutinas = 'ISR';";
        $rISR = DB::connection('empresa')->select($queryISR);
        $idISR = $rISR[0]->id;

        $queryISR = "SELECT ROUND(Total$idISR, 2) AS result 
                     FROM rutinas$ejercicio
                     WHERE id_periodo = '$id_periodo'
                     AND id_empleado = '$id_empleado' 
                     AND fnq_valor = 0";
        $valorISR = DB::connection('empresa')->select($queryISR);
        $importe_valor_isr = $valorISR[0]->result;

        ### OTROS PAGOS ################################################################
        $total_exento = $valor_exento;
        $total_percercepciones = round($datos->total_percepcion_fiscal, 2);

        if ($importe_valor_isr > 0) {
            $importe_otros_pagos = 0;
        } else {
            $importe_otros_pagos = $importe_valor_isr * -1;
        }

        $ArrayOtrosPag_TipoOtroPago  = ['002'];
        $ArrayOtrosPag_Clave         = ['D002'];
        $ArrayOtrosPag_Concepto      = ['SUBSIDIO PARA EL EMPLEO'];
        $ArrayOtrosPag_Importe       = [number_format($importe_otros_pagos, 2, '.', '')];

        $totalOtrosPagos = number_format($importe_otros_pagos, 2, '.', '');

        #== 11.1 Creación de la variable de tipo DOM, aquí se conforma el XML a timbrar posteriormente.
        $xml = new \DOMdocument('1.0', 'UTF-8');
        $root = $xml->createElement("cfdi:Comprobante");
        $root = $xml->appendChild($root);

        $toal_de_duccion = round($datos->total_deduccion_fiscal, 2);

        if ($importe_valor_isr < 0) {
            $TotalPercepAdicional = $total_percep + ($importe_valor_isr * -1);
            $totalotraspagos      = $importe_valor_isr * -1;
            $totalrealdeduccion   = $totalotraspagos - ($total_deduc * -1);
        } else {
            $TotalPercepAdicional = $total_percep;
            $totalrealdeduccion   = round($total_deduc, 2);
            $totalotraspagos      = 0;
        }

        #== 11.3 Rutina de integración de nodos =========================================
        $certificado = new Certificado(Storage::disk('public')->get($SendaPEMS . $file_cer));

        $emisor_certificado = $certificado->getSerial();

        $key = Storage::disk('public')->get($SendaPEMS . $file_key);

        //Creamos el nodo Comprobante
        $comprobanteAtributos = [
            'Fecha' => date("Y-m-d\TH:i:s"),
            "Exportacion" => "01",
            "SubTotal" => number_format($TotalPercepAdicional, 2, '.', ''),
            "Descuento" => number_format($totalrealdeduccion, 2, '.', ''),
            "Moneda" => "MXN",
            "Total" => number_format($neto_fiscal, 2, '.', ''),
            "TipoDeComprobante" => "N",
            "MetodoPago" => "PUE",
            "LugarExpedicion" => $cp_emisora,

        ];

        $creator = new CfdiCreator40($comprobanteAtributos, $certificado);
        $comprobante = $creator->comprobante();

        #== 11.2 Se crea e inserta el primer nodo donde se declaran los namespaces ======

        #== 11.2 EMISOR =====
        $comprobante->addEmisor([
            "Rfc" => $rfc_emisor,
            "Nombre" => $razon_emisor,
            "RegimenFiscal" => $regimen_fiscal_emisor
        ]);

        #== 11.2 Receptor =====
        $comprobante->addReceptor([
            "Rfc" => $rfc_empleado,
            "Nombre" => $nombre_empleado,
            "UsoCFDI" => "CN01",
            "DomicilioFiscalReceptor" => $e->cp,
            "RegimenFiscalReceptor" => "605"
        ]);


        $comprobante->addConcepto([
            "ClaveProdServ" => "84111505",
            "Cantidad" => "1",
            "ClaveUnidad" => "ACT",
            "Descripcion" => "Pago de nómina", // checar
            "ValorUnitario" => number_format($TotalPercepAdicional, 2, '.', ''),
            "Importe" => number_format($TotalPercepAdicional, 2, '.', ''),
            "Descuento" => number_format($totalrealdeduccion, 2, '.', ''),
            "ObjetoImp" => "01",
        ]);

        ########################### INICIA CODIFICACIÓN DEL COMPLEMENTO DE NÓMINA 1.2 ###########################
        $nominaA = new \CfdiUtils\Elements\Nomina12\Nomina();

        $complemento = $xml->createElement("cfdi:Complemento");
        $complemento = $root->appendChild($complemento);

        $nomina = $xml->createElement("nomina12:Nomina");
        $nomina = $complemento->appendChild($nomina);

        if ($importe_valor_isr > 0) {
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else if ($toal_de_duccion > 0) {

            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else if ($totalotraspagos == 0 && $totalrealdeduccion == 0) {
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', '')
            ]);

        } else if ($totalotraspagos == 0) {
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', '')
            ]);

        } else if ($totalotraspagos > 0 && $totalrealdeduccion == 0) {
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else {
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodoStr,
                "FechaFinalPago" => $fecha_final_periodoStr,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percercepciones, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
            ]);

        }

        //VALIDAR REGISTRO PATRONAL
        $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = '$id_empleado' 
                        AND   cat.estatus = 1";
        $registroP = DB::connection('empresa')->select($queryPatronal);

        $NominaEmisor = $xml->createElement("nomina12:Emisor");
        $nomina->appendChild($NominaEmisor);

        $registro_patronal = $registroP[0]->num_registro_patronal;

        if ($tipo_contrato != '09' || $tipo_contrato != '10' || $tipo_contrato != '99') {
            // acceso por prefijo get (Emisor es de 1 aparición)
            $emisor = $nominaA->getEmisor();
            $emisor['RegistroPatronal'] = $registro_patronal;

            if($empresa_cliente['id'] == 209){
                $emisor['Curp'] = 'AUEJ701106HDFGSR02';
            }
        }

        $NumeroEmpleado = $num_empleado ?? $id_empleado;

        /* traer depto y puesto */
        /* checar la diferencia de categoria y categoriaesp, donde aplica cada uno */
        $registroD = Departamento::where('id', $e->id_departamento)->first();
        $registroP = Puesto::where('id', $e->id_puesto)->first();

        $NominaReceptor = $xml->createElement("nomina12:Receptor");
        $nomina->appendChild($NominaReceptor);

        $nominaA->addReceptor([
            "Curp" => $e->curp,
            "NumSeguridadSocial" => $e->nss,
            "FechaInicioRelLaboral" => $fecha_alta_empleado->format('Y-m-d'),
            "Antigüedad" => "$antiguedad",
            "TipoContrato" => $tipo_contrato,
            "Sindicalizado" => "Sí",
            "TipoJornada" => $tipo_jornada,
            "TipoRegimen" => "02",
            "NumEmpleado" => $NumeroEmpleado,
            "Departamento" => $registroD->nombre ?? '',
            "Puesto" => $registroP->puesto ?? '',
            "RiesgoPuesto" => $tipo_clase,
            "PeriodicidadPago" => $tipo_periodo,
            "SalarioBaseCotApor" => round($e->salario_diario, 2), //
            "SalarioDiarioIntegrado" => round($e->salario_diario_integrado, 2),
            "ClaveEntFed" => "MIC"
        ]);

        $data_respuesta['nombre'] = $nombre_empleado;

        $percepcionesA=$nominaA->addPercepciones([
            "TotalSueldos" => number_format($total_percercepciones, 2, '.', ''),
            "TotalGravado" => number_format($total_gravado_real, 2, '.', ''),
            "TotalExento" => number_format($total_exento, 2, '.', '')
        ]);

        // Ciclo "for", recopilación de datos de percepciones. ===============
        for ($i = 0; $i < $numero_array_sat; $i++) {

            $percepcionesA->addPercepcion([
                "TipoPercepcion" => $ArrayPercep_TipoPercepcion[$i],
                "Clave" => $ArrayPercep_Clave[$i],
                "Concepto" => $ArrayPercep_Concepto[$i],
                "ImporteGravado" => $ArrayPercep_ImporteGravado[$i],
                "ImporteExento" => $ArrayPercep_ImporteExento[$i]
            ]);

        }

        $total_otras_deduc = $toal_de_duccion - $importe_valor_isr;
        $impuestos_retenidos = $importe_valor_isr;

        if ($importe_valor_isr > 0) {

            if ($numDeducciones > 0) {
                $deduccionesA = $nominaA->addDeducciones([
                    "TotalOtrasDeducciones" => number_format($total_otras_deduc, 2, '.', ''),
                    "TotalImpuestosRetenidos" => number_format($impuestos_retenidos, 2, '.', '')
                ]);

                // Ciclo "for", recopilación de datos de deducciones. ===============
                for ($i = 0; $i < $numDeducciones; $i++) {
                    if ($ArrayDeduc_Importe[$i] != 0) {
                        $deduccionesA->addDeduccion([
                            "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                            "Clave" => $ArrayDeduc_Clave[$i],
                            "Concepto" => $ArrayDeduc_Concepto[$i],
                            "Importe" => $ArrayDeduc_Importe[$i]
                        ]);
                    }
                }
            }

            $otrosPagosA= $nominaA->addOtrosPagos();

            // solo tiene imss
            if ($numDeducciones == 1 && $tiene_imss) {
                $otrosPagosA->addOtrosPago([
                    "TipoOtroPago" => "002",
                    "Clave" => "D002",
                    "Concepto" => 'SUBSIDIO PARA EL EMPLEO',
                    "Importe" => $totalotraspagos // aqui va el isr convertido en negativo
                ])->addSubsidioAlEmpleo([
                    "SubsidioCausado" => $subsidio_causado // aquí va el subsidio causado
                ]);


            } else {
                for ($i = 0; $i < count($ArrayOtrosPag_TipoOtroPago); $i++) {
                    $otrosPagosA->addOtrosPago([
                        "TipoOtroPago" => $ArrayOtrosPag_TipoOtroPago[$i],
                        "Clave" => $ArrayOtrosPag_Clave[$i],
                        "Concepto" => $ArrayOtrosPag_Concepto[$i],
                        "Importe" => $ArrayOtrosPag_Importe[$i]
                    ])->addSubsidioAlEmpleo([
                        "SubsidioCausado" => number_format($subsidio_causado, 2, '.', '')
                    ]);
                }
            }
        } else if ($totalotraspagos == 0) {
            if ($numDeducciones > 0) {
                $deduccionesA = $nominaA->addDeducciones([
                    "TotalOtrasDeducciones" => round($totalrealdeduccion, 2)
                ]);

                // Ciclo "for", recopilación de datos de deducciones. ===============
                for ($i = 0; $i < $numDeducciones; $i++) {
                    $deduccionesA->addDeduccion([
                        "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                        "Clave" => $ArrayDeduc_Clave[$i],
                        "Concepto" => $ArrayDeduc_Concepto[$i],
                        "Importe" => $ArrayDeduc_Importe[$i]
                    ]);
                }
            }
        } else {
            if ($numDeducciones > 0) {
                $deduccionesA = $nominaA->addDeducciones([
                    "TotalOtrasDeducciones" => round($totalrealdeduccion, 2)
                ]);

                // Ciclo "for", recopilación de datos de deducciones. ===============
                for ($i = 0; $i < $numDeducciones; $i++) {
                    if ($ArrayDeduc_Importe[$i] != 0) {
                        $deduccionesA->addDeduccion([
                            "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                            "Clave" => $ArrayDeduc_Clave[$i],
                            "Concepto" => $ArrayDeduc_Concepto[$i],
                            "Importe" => $ArrayDeduc_Importe[$i]
                        ]);
                    }
                }
            }

            $otrosPagosA= $nominaA->addOtrosPagos();

            // solo tiene imss
            if ($numDeducciones == 1 && $tiene_imss) {
                $otrosPagosA->addOtrosPago([
                    "TipoOtroPago" => "002",
                    "Clave" => "D002",
                    "Concepto" => 'SUBSIDIO PARA EL EMPLEO',
                    "Importe" => $totalotraspagos
                ])->addSubsidioAlEmpleo([
                    "SubsidioCausado" => $subsidio_causado
                ]);

                $nominaA->addAttributes([
                    "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
               ]);
            } else {
                for ($i = 0; $i < count($ArrayOtrosPag_TipoOtroPago); $i++) {
                    $otrosPagosA->addOtrosPago([
                        "TipoOtroPago" => $ArrayOtrosPag_TipoOtroPago[$i],
                        "Clave" => $ArrayOtrosPag_Clave[$i],
                        "Concepto" => $ArrayOtrosPag_Concepto[$i],
                        "Importe" => $ArrayOtrosPag_Importe[$i]
                    ])->addSubsidioAlEmpleo([
                        "SubsidioCausado" => number_format($subsidio_causado, 2, '.', '')
                    ]);
                }
            }
        }

        $comprobante->addComplemento($nominaA);
        $creator->addSumasConceptos(null, 2);

        $creator->addSello($key, $password);
        $creator->moveSatDefinitionsToComprobante();

        $cadena_original = $creator->buildCadenaDeOrigen();

        // método de ayuda para generar el xml y retornarlo como un string
        $xml_a =  $creator->asXml();
        $url = $ip_servicio;
        #== 12.4 Creando el SOAP de envío ==============================================
        $params = array(
            "xml" => $xml_a,
            "username" => $username,
            "password" => $password,
        );

        $client = new \SoapClient($url, array('trace' => 1));

        $response = $client->__soapCall("stamp", array($params));

        $xml = $client->__getLastResponse();

        ## FIN DEL PROCESO DE TIMBRADO #################################################

        ## 13. PROCESOS POSTERIORES AL TIMBRADO ########################################

        #== Si el nodo contiene datos se realizan los siguientes procesos ======
        if ($response->stampResult->xml) {
            $exito = true;

            #=== 13.6 Guardando el CFDI en archivo .XML  ============================
            $NomArchXML = "CFDI-40_RecNom_" . $numerico . "_" . $NoFac . ".xml";
            $NomArchPDF = "CFDI-40_RecNom_" . $numerico . "_" . $NoFac . ".pdf";

            $xmlt = new \DOMDocument();
            $xmlt->loadXML($response->stampResult->xml);
            $xmlt->save(storage_path('app/public'.$SendaCFDI . $NomArchXML));
            chmod(storage_path('app/public'.$SendaCFDI . $NomArchXML), 0777);

            #== 13.7 Procesos para extraer datos del Timbre Fiscal del CFDI =========
            $docXML = new \DOMDocument();
            if (\PHP_VERSION_ID < 80000) { 
                libxml_disable_entity_loader(false);    
            }
            $docXML->load(storage_path('app/public'.$SendaCFDI . "CFDI-40_RecNom_" . $numerico . "_" . $NoFac . ".xml"), LIBXML_NOWARNING);

            $params = $docXML->getElementsByTagName("Comprobante");
            foreach ($params as $param) {
                $VersionCFDI = $param->getAttribute("Version");
            }

            $comprobante = $docXML->getElementsByTagName("TimbreFiscalDigital");

            #== 13.8 Se obtienen contenidos de los atributos y se asignan a variables para ser mostrados =======
            foreach ($comprobante as $timFis) {
                $version_timbre = $timFis->getAttribute('Version');
                $sello_SAT      = $timFis->getAttribute('SelloSAT');
                $cert_SAT       = $timFis->getAttribute('NoCertificadoSAT');
                $sello_CFD      = $timFis->getAttribute('SelloCFD');
                $tim_fecha      = $timFis->getAttribute('FechaTimbrado');
                $tim_uuid       = $timFis->getAttribute('UUID');

                $data_respuesta['version_cfdi']   = $VersionCFDI;
                $data_respuesta['version_timbre'] = $version_timbre;
                $data_respuesta['sello_sat']      = $sello_SAT;
                $data_respuesta['cert_sat']       = $cert_SAT;
                $data_respuesta['sello_cfd']      = $sello_CFD;
                $data_respuesta['fecha_tim']      = $tim_fecha;
                $data_respuesta['timbre_uuid']    = $tim_uuid;
            }

            #== 13.8.1 Se muestra el número de factura asignado por el sistema local (no asingado por el SAT).
            $data_respuesta['no-fac'] = $NoFac;
            $params = $docXML->getElementsByTagName('Emisor');
            foreach ($params as $param) {
                $Emisor_RFC = $param->getAttribute('Rfc');
            }

            $params = $docXML->getElementsByTagName('Receptor');
            foreach ($params as $param) {
                $Receptor_RFC = $param->getAttribute('Rfc');
            }

            $params = $docXML->getElementsByTagName('Comprobante');
            foreach ($params as $param) {
                $total = $param->getAttribute('Total');
            }

            #== 13.10 Se crea código HTML para mostrar opciones al usuario.
            $respuestaPaccon = addslashes($xml);
            
            $facturaD = new timbradoEmpleado;

            $totalreal = round($neto_fiscal, 2);
            $FechaEmiTimbrado = date("Y-m-d") . "T" . date("H:i:s");
            $facturaD->id_empleado      = $id_empleado;
            $facturaD->id_periodo       = $id_periodo;
            $facturaD->fecha_timbrado   = $tim_fecha;
            $facturaD->sello_sat        = $sello_SAT;
            $facturaD->certificado_sat  = $cert_SAT;
            $facturaD->sello_cfdi       = $sello_CFD;
            $facturaD->folio_fiscal     = $tim_uuid;
            $facturaD->num_factura      = $NoFac;
            $facturaD->respuesta_pac    = $xml;
            $facturaD->respuesta_pac    = $respuestaPaccon;
            $facturaD->cadena_original  = $cadena_original;
            $facturaD->file_pdf         = $NomArchPDF;
            $facturaD->file_xml         = $NomArchXML;
            $facturaD->importe          = $totalreal;
            $facturaD->receptor         = $rfc_empleado;
            $facturaD->emisor           = $rfc_emisor;
            $facturaD->certificado_tim  = $no_certificado;
            $facturaD->fecha_emision    = $FechaEmiTimbrado;
            $facturaD->num_dias_pagados  = $dias_a_pagar;
            $facturaD->estatus_timbre   = 1;
            $facturaD->mensaje_error   = '';

            $facturaD->save();

            $data_respuesta['tipo_c'] = 0;
            $data_respuesta['id_repo'] = Session::get('empresa')['id'];
            $data_respuesta['id_usuario'] = $id_empleado;
            $data_respuesta['id_periodo'] = $id_periodo;
            $data_respuesta['c_cadena'] = $cadena_departamentos;
            $data_respuesta['archivo_xml'] = $NomArchXML;
            $data_respuesta['fecha_tim'] = $FechaEmiTimbrado;
            $data_respuesta['error'] = false;
            $data_respuesta['id_timbre'] = $facturaD->id;
            $data_respuesta['emisor_rfc'] = $Emisor_RFC;
            $data_respuesta['receptor_rfc'] = $Receptor_RFC;
            $data_respuesta['total'] = $total;

        } else {
            $incidencia = $response->stampResult->Incidencias->Incidencia;
            $exito = false;

            $FechaEmiTimbrado = date("Y-m-d") . "T" . date("H:i:s");

            $data_respuesta['id_periodo']    = $id_periodo;
            $data_respuesta['error']         = true;
            $data_respuesta['respuesta']     = $xml;
            $data_respuesta['codigo_error']  = $incidencia->CodigoError;
            $data_respuesta['MENSAJE_error'] = $incidencia->MensajeIncidencia;
            $data_respuesta['fecha_tim']     = $incidencia->FechaRegistro;
            $data_respuesta['c_cadena']      = null;
            $data_respuesta['xml_enviar']    = $xml_a;

            $facturaD = new timbradoEmpleado;
            $facturaD->id_empleado      = $id_empleado;
            $facturaD->id_periodo       = $id_periodo;
            $facturaD->fecha_timbrado   = $FechaEmiTimbrado;
            $facturaD->file_pdf         = 'error';
            $facturaD->file_xml         = 'error';
            $facturaD->sello_sat        = 'error';
            $facturaD->certificado_sat  = 'error';
            $facturaD->sello_cfdi       = 'error';
            $facturaD->folio_fiscal     = 'error';
            $facturaD->receptor         = 'error';
            $facturaD->emisor           = 'error';
            $facturaD->certificado_tim  = 'error';
            $facturaD->fecha_emision    = 'null';
            $facturaD->num_dias_pagados  = 0;
            $facturaD->importe          = 0;
            $facturaD->num_factura      = $NoFac;
            $facturaD->respuesta_pac    = $xml;
            $facturaD->cadena_original  = $cadena_original;
            $facturaD->estatus_timbre   = 2;
            $facturaD->mensaje_error   = $incidencia->MensajeIncidencia;

            $facturaD->save();

            $data_respuesta['id_timbre'] = $facturaD->id;
        }
             
        ##### FIN DE PROCEDIMIENTOS ####################################################
        if ($tipoR == 1) {
            return view('procesos.timbrado-nomina.empleado-resultado', compact('data_respuesta', 'regresa'));            
        } else {
            $rr['archivo_xml'] = $xml_a;
            $rr['no_factura'] = $data_respuesta['no_factura'];
            if ($data_respuesta['error'] == true) {
                $rr['error']         = $data_respuesta['error'];
                $rr['respuesta']     = $data_respuesta['respuesta'];
                $rr['codigo_error']  = $data_respuesta['codigo_error'];
                $rr['MENSAJE_error'] = $data_respuesta['MENSAJE_error'];
            }

            return response()->json(['exito' => $exito, 'data' => $rr]);
        }       
    }

    static function genera_pdf($path_arch_xml, $path_arch_pdf)
    {
        $striong_xml = file_get_contents($path_arch_xml);
        $cfdi = Cfdi::newFromString($striong_xml);

        $resolver = new XmlResolver();

        // el resolvedor tiene un método de ayuda para obtener la ubicación del XSLT
        // dependiendo de la versión del comprobante
        $location = $resolver->resolveCadenaOrigenLocation('4.0');

        // fabricar la cadena de origen
        $builder = new DOMBuilder();
        $cadenaorigen = $builder->build($striong_xml, $location);

        $comprobante = $cfdi->getNode();
        $tfd = $comprobante->searchNode('cfdi:Complemento', 'tfd:TimbreFiscalDigital');
        $emisor = $comprobante->searchNode('cfdi:Emisor');
        $receptor = $comprobante->searchNode('cfdi:Receptor');

        $data = [
            'comprobante'        => $comprobante,
            'tfd'                => $tfd,
            'emisor'             => $emisor,
            'receptor'           => $receptor,
            'concepto'           => $comprobante->searchNode('cfdi:Conceptos', 'cfdi:Concepto'),
            'nomina'             => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina'),
            'nomina_emisor'      => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Emisor'),
            'nomina_receptor'    => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Receptor'),
            'percepciones'       => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones'),
            'percepcion'         => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion'),
            'nomina_percepcion'  => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion'),
            'nomina_deducciones' => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones'),
            'deduccion'          => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones','nomina12:Deduccion'),
            'otro_pago'          => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:OtrosPagos','nomina12:OtroPago'),
            'qr'                 => "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=".$tfd['UUID']."&re=".$emisor['Rfc']."&rr=".$receptor['Rfc']."&tt=".$comprobante['Total']."&fe=".substr($tfd['SelloCFD'],-8),
            'sello_cfd'          => str_split($tfd['SelloCFD'], 125),
            'sello_sat'          => str_split($tfd['SelloSAT'], 120),
            'cadena_origen'      => $cadenaorigen,
            'formatter'          => new \NumberFormatter('es', \NumberFormatter::SPELLOUT)
        ];

        //return view('recibos-nomina.recibo_nomina_individual')->with(['data' => $data]);
        $pdf = Pdf::loadView('recibos-nomina.recibo_nomina_individual', ['data' => $data]);
        $pdf->save($path_arch_pdf);

        return $pdf;
    }

    static function genera_pdf_finiquito($path_arch_xml, $path_arch_pdf)
    {
        $striong_xml = file_get_contents($path_arch_xml);
        $cfdi = Cfdi::newFromString($striong_xml);

        $resolver = new XmlResolver();

        // el resolvedor tiene un método de ayuda para obtener la ubicación del XSLT
        // dependiendo de la versión del comprobante
        $location = $resolver->resolveCadenaOrigenLocation('4.0');

        // fabricar la cadena de origen
        $builder = new DOMBuilder();
        $cadenaorigen = $builder->build($striong_xml, $location);

        $comprobante = $cfdi->getNode();
        $tfd = $comprobante->searchNode('cfdi:Complemento', 'tfd:TimbreFiscalDigital');
        $emisor = $comprobante->searchNode('cfdi:Emisor');
        $receptor = $comprobante->searchNode('cfdi:Receptor');

        $data = [
            'comprobante'        => $comprobante,
            'tfd'                => $tfd,
            'emisor'             => $emisor,
            'receptor'           => $receptor,
            'concepto'           => $comprobante->searchNode('cfdi:Conceptos', 'cfdi:Concepto'),
            'nomina'             => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina'),
            'nomina_emisor'      => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Emisor'),
            'nomina_receptor'    => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Receptor'),
            'percepciones'       => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones'),
            'percepcion'         => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion'),
            'nomina_percepcion'  => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Percepciones','nomina12:Percepcion'),
            'nomina_deducciones' => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones'),
            'deduccion'          => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:Deducciones','nomina12:Deduccion'),
            'otro_pago'          => $comprobante->searchNode('cfdi:Complemento', 'nomina12:Nomina','nomina12:OtrosPagos','nomina12:OtroPago'),
            'qr'                 => "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=".$tfd['UUID']."&re=".$emisor['Rfc']."&rr=".$receptor['Rfc']."&tt=".$comprobante['Total']."&fe=".substr($tfd['SelloCFD'],-8),
            'sello_cfd'          => str_split($tfd['SelloCFD'], 125),
            'sello_sat'          => str_split($tfd['SelloSAT'], 120),
            'cadena_origen'      => $cadenaorigen,
            'formatter'          => new \NumberFormatter('es', \NumberFormatter::SPELLOUT)
        ];

        // return view('recibos-nomina.recibo_finiquito_individual')->with(['data' => $data]);
        $pdf = Pdf::loadView('recibos-nomina.recibo_finiquito_individual', ['data' => $data]);
        $pdf->save($path_arch_pdf);

        return $pdf;
    }

    public function descargaPDF($id_empleado, $id_repo, $xml_file_name){
        cambiarBase(Session::get('base'));

        $path = storage_path()."/app/public/repositorio/". $id_repo . "/" .  $id_empleado . "/timbrado/";
        $path_arch_xml = $path . 'archs_cfdi/'.$xml_file_name;
        $path_arch_pdf = $path . 'archs_pdf/'.str_replace('.xml', '.pdf', $xml_file_name);

        if (File::exists($path_arch_xml)){
            $pdf = $this->genera_pdf($path_arch_xml, $path_arch_pdf);
            return $pdf->download(str_replace('.xml', '.pdf', $xml_file_name));
        }else
            return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$xml_file_name.'</h3></div>';
    }

    public function cancelar_cfdi($id, $cadena, $periodo, $regresa){
        /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
        $cadena_departamentos = base64_decode($cadena);
        $data_string ="";
        $data_respuesta = array();

        $base = Session::get('base');
        cambiarBase($base);

        $timbre = TimbradoEmpleado::find($id);
        $no_fac = $timbre->num_factura;

        $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->first();
        $usr_timbre = $emisora->user_timbre;

        $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
        $data_respuesta['no_factura']   = $timbre->num_factura;
        $data_respuesta['repo']   = Session::get('empresa')['id'].'/'.$timbre->id_empleado;
        $data_respuesta['repositorio']=Session::get('empresa')['id'];
        $data_respuesta['id_empleado']=$timbre->id_empleado;

        /* REPOS Y FODLERS */
        $acceso_repositorio = storage_path().'/app/public/';
        $url_timbrado = 'timbrado/';

        $SendaPEMS    = $url_timbrado . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).

        $contenido_del_nodo_acuse = "";
        $ValorUUID = "";

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr($usr_timbre , 7 ,strlen($usr_timbre));
       
        /* credenciales timbrado */
        $credenciales = TimbradoCredenciales::find($condicion);
        $FolioFiscal = $timbre->folio_fiscal; // 2.5 Folio fiscal de CFDI a cancelar.
        $taxpayer_id = $credenciales->rfc;

        $username    = base64_decode($credenciales->user);
        $password    = base64_decode($credenciales->password);
        $ip_servicio = $credenciales->servicio_cancelacion;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $file_cer       = $credenciales->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_enc       = $credenciales->nombre_archivo.".key.enc.pem";   // 3.3 Nombre del archivo .cer.key

        #== Obtener el certificado del archivo .cer.pem ============================
        $cer_path =  $acceso_repositorio.$SendaPEMS . $file_cer;
        $cer_file = fopen($cer_path, "r");
        $cer_content = fread($cer_file, filesize($cer_path));
        fclose($cer_file);

        #== Obtener contenido de archivo .key.pem ==================================
        $key_path =  $acceso_repositorio.$SendaPEMS.$file_enc;
        $key_file = fopen($key_path, "r");
        $key_content = fread($key_file, filesize($key_path));
        fclose($key_file);

        $client = new \SoapClient($ip_servicio, array('trace' => 1));

        $uuids = array("UUID" => $FolioFiscal, "Motivo" => "02", "FolioSustitucion" => "");
        $uuid_ar = array('UUID' => $uuids);
        $params = array("UUIDS" => $uuid_ar,
            "username" => $username,
            "password" => $password,
            "taxpayer_id" => $taxpayer_id,
            "cer" => $cer_content,
            "key" => $key_content);

        $client->__soapCall("cancel", array($params));

        $RespServ = $client->__getLastResponse();
       
        #== 12.9 Se muestra la respuesta del servidor del PAC (opcional a mostrar) ================
        $respuestaPac = htmlspecialchars($RespServ);
        $data_respuesta['respuesta'] = $respuestaPac;

        if (!$RespServ){
           $data_respuesta['error'] = TRUE;
           $data_respuesta['codigo_error'] .= "Error: ".$RespServ;
           $data_respuesta['error_msg'] = 'Error al mandar la solicitud SOAP';
        }else{
            #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
            $domResp = new \DOMDocument();
            $domResp->loadXML($RespServ);

            ## TAG UUID ####################################################
            $tagUUID = $domResp->getElementsByTagName('EstatusUUID');

            foreach($tagUUID as $ObjTag ){
               $ValorUUID = $ObjTag->nodeValue; // Código de error o acierto.
            }

            ## TAG STATUS ####################################################
            $tagCodigo = $domResp->getElementsByTagName('CodEstatus');
            $valorCodigo='';
            foreach($tagCodigo as $ObjTag ){
               $valorCodigo = $ObjTag->nodeValue; // Código de error o acierto.
            }

            ## .XML ACUSE DE CANCELACIÓN ###################################
            $tagAcuse = $domResp->getElementsByTagName('Acuse');

            foreach($tagAcuse as $ObjTag ){
               $contenido_del_nodo_acuse = $ObjTag->nodeValue;
            }

            $data_respuesta['contenido']  = $contenido_del_nodo_acuse;
            $data_respuesta['soap']       = $cadena;
            $data_respuesta['valor_UUID'] = $ValorUUID;

            $CodResp = $ValorUUID;

            if($CodResp !=""){
                $data_respuesta['contenido'] = htmlspecialchars($cadena);
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = "205: UUID No existente";
                $data_respuesta['error_msg'] = $valorCodigo;
            }

            if($CodResp == 201 || $CodResp == "201" ) {
                ### SE ASIGNA EL CONTENIDO DEL ACUSE A UNA VARIABLE DE TIPO DOM PARA LA LECTURA DE DATOS ####################
                $DOM = new \DOMDocument('1.0', 'utf-8');
                $DOM->preserveWhiteSpace = FALSE;
                $DOM->loadXML($contenido_del_nodo_acuse);

                #== Fecha de cancelación ===================================
                $params = $DOM->getElementsByTagName('CancelaCFDResult');
                foreach ($params as $param) {
                   $FecCanc = $param->getAttribute('Fecha');
                   $data_respuesta['fecha_resp']  = $FecCanc;
                   $data_string .= 'Fecha de cancelación:'.$FecCanc;
                }

                $respString='Petición de cancelación realizada exitosamente';

                #== Sello digital del SAT (cancelación) ===========================
                $nodoSignatureValue = $DOM->getElementsByTagName('SignatureValue');

                foreach($nodoSignatureValue as $param){
                   $SelloCanc = $param->nodeValue;
                   $data_respuesta['sello_cancelacion']  = $SelloCanc;
                   $data_string .= 'Sello digital SAT:'.$SelloCanc;
                }

                $request  = addslashes($cadena);
                $response = addslashes($RespServ);
                $xmlacuse = addslashes($contenido_del_nodo_acuse);

                // GUARDAR EN DB
                $facturaD = new TimbradoCancelacionesEmpleado;
                $facturaD->id_empleado       = $timbre->id_empleado;
                $facturaD->id_periodo        = $timbre->id_periodo;
                $facturaD->fecha_cancelacion = $FecCanc;
                $facturaD->request_cancel    = $request;
                $facturaD->response          = $response;
                $facturaD->xml_acuse_cancel  = $xmlacuse;
                $facturaD->sello_sat         = $SelloCanc;
                $facturaD->no_factura        = $no_fac;
                $facturaD->save();

                $timbre->estatus_timbre = 2;
                $timbre->update();

                $data_respuesta['error'] = false;
                $data_respuesta['mnsg'] = addslashes($respString);
            }
            if($CodResp == 704 || $CodResp == "704"){
                $data_string .= "Error con la contraseña de la llave privada";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 708 || $CodResp == "708"){
                $data_string .= "Error de conexion del SAT ....";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 202 || $CodResp == "202"){
                $data_string .= " 202: UUID Cancelado Previamente";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 203 || $CodResp == "203"){
                $data_string .= "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 205 || $CodResp == "205"){
                $data_string .= "205: UUID No existente";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

        }

        ## FIN DEL PROCESO DE TIMBRADO #################################################
        return view('procesos.timbrado-nomina.timbre-cancelado',compact('data_respuesta', 'regresa', 'cadena_departamentos', 'periodo'));
   }

   public function verificarEstatus($id){
        $data_string ="";
        $data_respuesta = array();
         
        $base = Session::get('base');
        cambiarBase($base);

        $timbre = TimbradoEmpleado::find($id);       
        $no_fac = $timbre->num_factura;     
        // cambiarBase('singh');

        $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->get()[0];
        $usr_timbre = $emisora->user_timbre;

        $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
        $data_respuesta['no_factura']   = $timbre->num_factura;
        $data_respuesta['repo']   = Session::get('empresa')['id'].'/'.$timbre->id_empleado;

        $importe=$timbre->importe;
        $rfcemi=$timbre->emisor;
        $receptor=$timbre->receptor;
        $FolioFiscal=$timbre->folio_fiscal;

        /* REPOS Y FODLERS */
        $url_repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/';
        // $repositorio2 = 'public/repositorio/' . $data_respuesta['repo'] . '/timbrado/';
        // $recursos = resource_path().'/timbrado/';        
        // $folder_repositorio = public_path() . '/' . $repositorio;

        $acceso_repositorio = storage_path().'/app/public/';       
        $url_timbrado = 'timbrado/';

        $SendaCFDI     = $url_repositorio.'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaEmpGRAFS = $url_repositorio.'archs_grafs/';   // 2.2 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR       = $url_repositorio.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR_url   = $url_repositorio.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPreCFDI  = $url_repositorio.'archs_precdfi/';// 2.4 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPDF      = $url_repositorio.'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaRESP     = $url_repositorio.'archs_resp/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
     
        $SendaPEMS    = $url_timbrado . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $url_timbrado . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $url_timbrado . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);    

        $contenido_del_nodo_acuse = "";
        $ValorUUID = "";

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr ( $usr_timbre , 7 ,strlen($usr_timbre) );
        $condicion = ($condicion == null)?1:$condicion;
        $condicion =1;
     
        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id=".$condicion;
        $credenciales = DB::select($query);  
        
        $FolioFiscal = $timbre->folio_fiscal; // 2.5 Folio fiscal de CFDI a cancelar.
        $taxpayer_id = $credenciales[0]->rfc;
        /*
        $rfc_emi    = $credenciales[0]->rfc;
        $rfc_emisor = $rfc_emi;
        */
        
        $username    = base64_decode($credenciales[0]->user);
        $password    = base64_decode($credenciales[0]->password);
        $ip_servicio = $credenciales[0]->servicio_cancelacion;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales[0]->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales[0]->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo.".key.pem";   // 3.3 Nombre del archivo .cer.key
        $file_enc       = $credenciales[0]->nombre_archivo.".key.enc.pem";   // 3.3 Nombre del archivo .cer.key
        
        #== Obtener el certificado del archivo .cer.pem ============================
        $cer_path = $acceso_repositorio.$SendaPEMS.$file_cer;
        $cer_file = fopen($cer_path, "r");
        $cer_content = fread($cer_file, filesize($cer_path));


        #== Conviritien el contenido del certificado a BASE 64 y asignarlo a una variable ======
        $cer_content = base64_encode($cer_content);
        fclose($cer_file);

        #== Encriptar con DES3 =====================================================    
        $ArchivoKeyPem       = $SendaPEMS . $file_key;  //Archivo .key.pem SIN encriptar.
        $ArchivoKeyEncripPem = $SendaPEMS . $file_enc; //Archivo .key.enc.pem ENCRIPTADO.

        #== Obtener contenido de archivo .key.pem ==================================
        $key_path = $acceso_repositorio.$SendaPEMS.$file_enc;
        $key_file = fopen($key_path, "r");
        $key_content = fread($key_file, filesize($key_path));
        $key_content = base64_encode($key_content);
        fclose($key_file);

        
$cadena = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:can="http://facturacion.finkok.com/cancel">
   <soapenv:Header/>
   <soapenv:Body>
    <can:get_sat_status>
         <can:username>$username</can:username>
         <can:password>$password</can:password>
         <can:taxpayer_id>$rfcemi</can:taxpayer_id>
         <can:rtaxpayer_id>$receptor</can:rtaxpayer_id>
         <can:uuid>$FolioFiscal</can:uuid>
         <can:total>$importe</can:total>
    </can:get_sat_status>
   </soapenv:Body>
</soapenv:Envelope>
XML
;
        $process  = curl_init($ip_servicio);
        
        #== 12.8 Se envía el contenido del SOAP al servidor del PAC =====================
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml',' charset=utf-8'));
        curl_setopt($process, CURLOPT_POSTFIELDS, $cadena);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
        $RespServ = curl_exec($process);
        
        #== 12.9 Se muestra la respuesta del servidor del PAC (opcional a mostrar) ================
        $respuestaPac = htmlspecialchars($RespServ);
        $data_respuesta['respuesta'] = $respuestaPac;

        $mystring = $RespServ;
          $findme   = 'Cancelado';
          $pos = strpos($mystring, $findme);

          // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
          // porque la posición de 'a' está en el 1° (primer) caracter.
          if ($pos === false) {
              $respString='La Factura aun no ha sido Cancelada Verifica mas Tarde';
          } else {
              $respString="La Factura fue Canelada Exitosamente";

          }
        
        $err = 0;
        if (!$RespServ)
        {
            $data_respuesta['error'] = TRUE;
            $data_respuesta['codigo_error'] .= "<h1>Error: ".$RespServ."</h1><br>";
            $data_respuesta['error_msg'] = curl_error($process);
        }
        else
        {
            $data_respuesta['error'] = FALSE;
            $data_respuesta['respuesta_string'] = $respString;
            
            #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
            $domResp = new \DOMDocument();
            $domResp->loadXML($RespServ);

            if (File::exists($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml")){
                // Se guarda la respuesta del servidor
                $domResp->save($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml");
                chmod($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml", 0777);
            }else{                
                return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$acceso_repositorio.$SendaRESP.'Status_Server'.$no_fac.'.xml'.'</h3></div>';
            }
        }
        //dd($data_respuesta);
        curl_close($process);

        return view('procesos.timbrado-nomina.check-estatus',compact('data_respuesta'));
    }

    /* TIMBRAR de FORMA MASIVA*/
    public function validarMasivo($id_periodo, $cadena, $regresa){
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $array_jornada = array(
            '01' => 'DIURNA',
            '02' => 'NOCTURNA',
            '03' => 'MIXTA',
            '04' => 'POR HORA',
            '05' => 'REDUCIDA',
            '06' => 'CONTINUADA',
            '07' => 'PARTIDA',
            '08' => 'POR TURNOS',
            '99' => 'OTRA JORNADA',
            '1' => 'DIURNA',
            '2' => 'NOCTURNA',
            '3' => 'MIXTA',
            '4' => 'POR HORA',
            '5' => 'REDUCIDA',
            '6' => 'CONTINUADA',
            '7' => 'PARTIDA',
            '8' => 'POR TURNOS',
        );
        $array_contrato = array(
            '01' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
            "02" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
            "03" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
            "04" => 'CONTRATO DE TRABAJO POR TEMPORADA',
            "05" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
            "06" => 'Contrato de trabajo con capacitación inicial',
            "07" => 'Modalidad de contratación por pago de hora laborada',
            "08" => 'Modalidad de trabajo por comisión laboral',
            "09" => 'Modalidades de contratación donde no existe relación de trabajo',
            "10" => 'JUBILACIÓN, PENSIÓN, RETIRO',
            "99" => 'OTRO',
            '1' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
            "2" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
            "3" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
            "4" => 'CONTRATO DE TRABAJO POR TEMPORADA',
            "5" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
            "6" => 'Contrato de trabajo con capacitación inicial',
            "7" => 'Modalidad de contratación por pago de hora laborada',
            "8" => 'Modalidad de trabajo por comisión laboral',
            "9" => 'Modalidades de contratación donde no existe relación de trabajo',
        );

        /* VALIDACION PREVIA DE USUARIOS'*/
        $base = Session::get('base');
        cambiarBase($base);
        $cadena_departamentos = base64_decode($cadena);
        /* periodo */
        //$periodo = periodosNomina::where('activo', 1)->first();        
        $periodo = periodosNomina::find($id_periodo); // Periodo para probar 
        $id_periodo  = $periodo->id;

        $ejercicio   = $periodo->ejercicio;
        $tipo_nomina = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;
        /* TRAEMOS LOS EMPLEADOS A VALIDAD */
        $empleados = array();
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND tipo_de_nomina = '$tipo_nomina'
                  and id_departamento in ($cadena_departamentos)
                  AND id IN (
                      SELECT id_empleado 
                      FROM rutinas$ejercicio 
                      WHERE id_periodo = '$id_periodo' 
                      AND fnq_valor = 0 
                      AND neto_fiscal > 0);
                 ";
        $emple = DB::connection('empresa')->select($query);     
        foreach ($emple as $e) {
            $errores = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
            $empleado = array(
                'id'     => $e->id,
                'nombre' => $e->nombre_completo,
                'rfc'    => $e->rfc,
                'curp'   => $e->curp,
                'nss'    => $e->nss,
            );

            //validar RFC/NSS/CURP
            if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
                $errores['rfc'] = true;
                $num_errores++;
            }

            if (!preg_match("/^[0-9]+$/", $e->nss)) {
                $errores['nss'] = true;
                $num_errores++;
            }

            if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
                $errores['curp'] = true;
                $num_errores++;
            }

            //VALIDAR REGISTRO PATRONAL
            $queryPatronal = "SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                                FROM categorias cat  
                                JOIN empleados em  
                                ON em.id_categoria = cat.id 
                                INNER JOIN singh.registro_patronal re 
                                ON cat.tipo_clase = re.id
                                WHERE em.id = '$e->id' 
                                AND   em.tipo_de_nomina='$tipo_nomina' 
                                AND   em.id_departamento in ($cadena_departamentos) 
                                AND   cat.estatus = 1";
            $registroP = DB::connection('empresa')->select($queryPatronal);
      
            if ($registroP) {
                $r = $registroP[0];
                $r->tipo_jornada = ($r->tipo_jornada == "DUIRNA" || $r->tipo_jornada == "DIURNA") ? "01" : $r->tipo_jornada;

                $r->string_contrato = $array_contrato[$r->tipo_contrato];
                $r->string_jornada = $array_jornada[$r->tipo_jornada];
            } else {
                $r = "error";
                $errores['registro_patronal'] = true;
                $num_errores++;
            }
            $empleado['registro_patronal'] = $r;
            $empleado['errores'] = $errores;
            $empleados[] = $empleado;
        }

        /* Validación de conceptos de nomina */
        $queryConceptos = "SELECT * 
                               FROM conceptos_nomina 
                               WHERE nomina = 1 
                               AND  activo_en_nomina = 1 
                               AND  tipo <> 3 
                               AND  estatus <> 2 
                               AND  file_rool <> 0 
                               AND  file_rool < 250";
        $concep = DB::connection('empresa')->select($queryConceptos);
        $conceptos = array();
        foreach ($concep as $c) {
            $errores = array('sat' => false);
            $concepto = array(
                'id'         => $c->id,
                'nombre'     => $c->nombre_concepto,
                'codigo_sat' => $c->codigo_sat,
            );        
            if ($c->codigo_sat == NULL) {
                $errores['sat'] = true;
                $num_errores_conceptos++;
            }
            $concepto['errores'] = $errores;
            $conceptos[] = $concepto;
        }
        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);

        return view('procesos.timbrado-nomina.validacion-masiva', compact('periodo', 'empleados', 'conceptos', 'cadena_departamentos', 'errores', 'regresa'));
    }

    /* TIMBRAR MASIVO */
    public function timbrarMasivoBucle($cadena, $regresa){
        $base = Session::get('base');
        cambiarBase($base);
        $cadena_departamentos = base64_decode($cadena);
        $periodo      = periodosNomina::where('activo', 1)->first();     
        $id_periodo   = $periodo->id;
        $ejercicio    = $periodo->ejercicio;
        $tipo_nomina  = $periodo->nombre_periodo;

        $existen_timbrados = DB::connection('empresa')
            ->table('timbrado')
            ->where('id_periodo', $id_periodo)
            ->where('sello_sat', '<>', 'error')
            ->get()
            ->count();
        if($existen_timbrados !=0){
            return redirect()->route('timbrar.nomina');
        }

        /* Convertimos el tipo de periodo a procesar */
        $tipo_periodo = null;
        switch (strtoupper($tipo_nomina)) {
            case 'DIARIA':
                $tipo_periodo = '01';
                break;
            case 'SEMANAL':
                $tipo_periodo = '02';
                break;
            case 'CATORCENAL':
                $tipo_periodo = '03';
                break;
            case 'QUINCENAL':
                $tipo_periodo = '04';
                break;
            case 'MENSUAL':
                $tipo_periodo = '05';
                break;
            case 'ANUAL':
                $tipo_periodo = '99';
                break;
            default:
                $tipo_periodo = '99';
                break;
        }
        /* TRAEMOS LOS EMPLEADOS A VALIDAR */
        $query = "SELECT id,concat(nombre,' ',apaterno,' ',amaterno) AS nombre
                   FROM empleados
                   WHERE estatus = 1
                   AND tipo_de_nomina = '$tipo_nomina'
                   and id_departamento in ($cadena_departamentos)
                   AND id IN (
                       SELECT id_empleado 
                       FROM rutinas$ejercicio 
                       WHERE id_periodo = '$id_periodo' 
                       AND fnq_valor = 0 
                       AND neto_fiscal > 0);
                  ";
        $empleados = DB::connection('empresa')->select($query); 
        return view('procesos.timbrado-nomina.validacion-masiva-bucle', compact('periodo', 'empleados', 'cadena_departamentos', 'regresa'));
    }

    public function downloadXml($id, $repo, $archivo){
        //verificamos si el archivo existe y lo retornamos
        if (Storage::disk('public')->exists("repositorio/".$repo."/".$id."/timbrado/archs_cfdi/".$archivo)){
            return Storage::disk('public')->download("repositorio/".$repo."/".$id."/timbrado/archs_cfdi/".$archivo);
        }
        return '<div style="justify-content: center; display: flex; color:red;"><h3><strong>No se encontró el archivo!</strong></h3></div>';
    }

    public function downloadSoapXml($id, $repo, $archivo){         
        //verificamos si el archivo existe y lo retornamos
        if (Storage::disk('public')->exists("repositorio/".$repo."/".$id."/timbrado/archs_precdfi/".$archivo)){        
            return Storage::disk('public')->download("repositorio/".$repo."/".$id."/timbrado/archs_precdfi/".$archivo);
        }     
        return '<div style="justify-content: center; display: flex; color:red;"><h3><strong>No se encontró el archivo!</strong></h3></div>';
    }

    public function pdfMasivo($periodo){      
        $this->creaPdfPeriodo($periodo);  
       
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];  
        $url_pdf = storage_path()."/app/public/repositorio/".$repo."/timbrado/archs_pdf/PDF_Masivo_Pe_" . $periodo . ".pdf";  

        //verificamos si el archivo existe y lo retornamos
        if (File::exists($url_pdf)){        
            return response()->download($url_pdf);
        }    
      
        $url = asset('../resources/views/reportes-fpdf/pdf_recibonominaMasi_Periodo.php?base='.$base.'&repo='.$repo.'&periodo='.$periodo);
        return redirect()->to($url);
    }

    public function creaPdfPeriodo($periodo){   
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];      
        $acceso_repositorio = storage_path().'/app/public/repositorio/'.$repo; 
        cambiarBase($base); 

        $timbres = DB::connection('empresa')
                    ->table('timbrado')
                    ->where('id_periodo', $periodo)
                    ->where('sello_sat','<>', 'error')
                    ->get();

        foreach($timbres as $timbre){
            $url_xml = $acceso_repositorio . "/" . $timbre->id_empleado .'/timbrado/archs_cfdi/'. $timbre->file_xml;
            if (File::exists($url_xml)){
                $pdf = str_replace('.xml', '.pdf', $timbre->file_xml);
                $url = $acceso_repositorio . "/" . $timbre->id_empleado .'/timbrado/archs_pdf/'. $pdf ;
                $isr = 2; //de q no hay isr, cmabiar en algún momento            
              
                //verificamos si el archivo existe y lo retornamos
                if (!File::exists($url)){                   
                    $url2 = \file_get_contents(asset('../resources/views/reportes-fpdf/pdf_reciboNomina.php?NomArchXML='.$timbre->file_xml.'&NomArchPDF='.$pdf.'&id='.$timbre->id_empleado.'&base='.$base.'&base_id='.$repo.'&isr='.$isr));
                    // echo "creado<br>$pdf<br>";
                }else{
                    //echo "existe<br>$url<br>";
                }            
            }
        }       
    }

    public function emailMasivo($periodo, $cadena, $regresa){       
        $cadena_departamentos = base64_decode($cadena); 
        // app('App\Http\Controllers\Parametria\PeriodosNominaController')->enviarRecibosNomina($periodo);
        $this->enviarRecibosNomina($periodo);
        return view('procesos.timbrado-nomina.email-masivo', compact('periodo', 'cadena_departamentos', 'regresa'));
    }

    public function enviarRecibosNomina($idPeriodo){   
        cambiarBase(Session::get('base'));
        $periodoNomina = periodosNomina::find($idPeriodo);
        $fecha_inicial_periodo = formatoAFecha($periodoNomina->fecha_inicial_periodo);
        $fecha_final_periodo = formatoAFecha($periodoNomina->fecha_final_periodo);

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->where('tipo_de_nomina', $periodoNomina->nombre_periodo)
            ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            // ->where('id', 106)
            ->whereIn('id', function ($query) use ($periodoNomina) {
                $query->select('id_empleado')
                    ->from('rutinas' . $periodoNomina->ejercicio)
                    ->where('id_periodo', $periodoNomina->id)
                    ->where('fnq_valor', 0)                    
                    ->where('neto_fiscal', '>', 0);
            })->get();
        
        // print_r($empleados); exit;

        foreach ($empleados as $empleado) {     
            \Mail::to($empleado->correo)->later(now()->addSeconds(5), new NuevoReciboNominaEmail($fecha_inicial_periodo, $fecha_final_periodo));
        }       
    }

    ## 14. FUNCIONES DEL MÓDULO ###################################################

    # 14.1 Función que integra los nodos al archivo .XML y forma la "Cadena original".
    private function cargaAtt(&$nodo, $attr)
    {
        $cadena_original = "";
        $quitar = array('sello' => 1, 'noCertificado' => 1, 'certificado' => 1);
        foreach ($attr as $key => $val) {
            $val = preg_replace('/\s\s+/', ' ', $val);
            $val = trim($val);
            if (strlen($val) > 0) {
                $val = str_replace("|", "/", $val);
                $nodo->setAttribute($key, $val);
                if (!isset($quitar[$key]))
                    if (
                        substr($key, 0, 3) != "xml" &&
                        substr($key, 0, 4) != "xsi:"
                    )
                        $cadena_original .= $val . "|";
            }
        }
        return $cadena_original;
    }

    # 14.2 Función que integra los nodos al archivo .XML sin integrar a la "Cadena original".
    private function cargaAttSinIntACad(&$nodo, $attr)
    {
        global $xml;
        $quitar = array('sello' => 1, 'noCertificado' => 1, 'certificado' => 1);
        foreach ($attr as $key => $val) {
            $val = preg_replace('/\s\s+/', ' ', $val);
            $val = trim($val);
            if (strlen($val) > 0) {
                $val = str_replace("|", "/", $val);
                $nodo->setAttribute($key, $val);
                if (!isset($quitar[$key]))
                    if (
                        substr($key, 0, 3) != "xml" &&
                        substr($key, 0, 4) != "xsi:"
                    );
            }
        }
    }

    # 14.3 Funciónes que da formato al "Importe total" como lo requiere el SAT para ser integrado al código QR.
    private function ProcesImpTot($ImpTot)
    {
        $ImpTot = number_format($ImpTot, 4); // <== Se agregó el 30 de abril de 2017.
        $ArrayImpTot = explode(".", $ImpTot);
        $NumEnt = $ArrayImpTot[0];
        $NumDec = $this->ProcesDecFac($ArrayImpTot[1]);

        return $NumEnt . "." . $NumDec;
    }

    private function ProcesDecFac($Num)
    {
        $FolDec = "";
        if ($Num < 10) {
            $FolDec = "00000" . $Num;
        }
        if ($Num > 9 and $Num < 100) {
            $FolDec = $Num . "0000";
        }
        if ($Num > 99 and $Num < 1000) {
            $FolDec = $Num . "000";
        }
        if ($Num > 999 and $Num < 10000) {
            $FolDec = $Num . "00";
        }
        if ($Num > 9999 and $Num < 100000) {
            $FolDec = $Num . "0";
        }
        return $FolDec;
    }

    public function timbrarFactura($id)
    {
        $base = Session::get('base');
        cambiarBase($base);

        /* Informacion general y parametria */
        $data_respuesta = array();
        $repositorio = 0;
        $carpeta_facturas = "";
        $carpeta_xml = "";
        $carpeta_pdf = "";
        $carpeta_pre = "";
        $carpeta_data = "";
        /* datos de empresa que factura */
        $rfc_empresa = Session::get('empresa')['rfc'];
        $empresa     = Session::get('empresa')['razon_social'];
        $domicilio_fiscal_receptor = Session::get('empresa')['codigo_postal'];
        $regimen_receptor = Session::get('empresa')['regimen'];


        /* IVA de los parametros de la empresa */
        $iva_empresa = Session::get('empresa')['parametros'][0]['iva'];

        /* FACTURA & DETALLE de FACTURA (conceptos)*/
        $factura = Factura::find($id);
        $conceptos_factura =  FacturaDetalle::where('id_factura', $factura->id)->where('estatus', 0)->get();

        $id_empresa_emisora = $factura->emisora;
        $metodo_pago        = $factura->metodo;
        $forma_pago         = $factura->forma;
        $uso_cfdi_receptor            = $factura->regimen;

        //TIPO 'E' & 'P'
        $folio_relacionado  = $factura->folio_relacionado;
        $tipo_relacion      = $factura->tipo_relacion;

        // tIPO 'P'
        // dd($factura);
        $fecha_pago             = $factura->fecha_pago;
        $monto                  = $factura->monto;

        $folio                  = $factura->folio;
        $importe_pagado         = $factura->importe_pagado;
        $num_parcialidad        = $factura->num_parcialidad;
        $importe_saldo_anterior = $factura->importe_saldo_anterior;
        $importe_saldo_insoluto = $factura->importe_saldo_insoluto;
        /* Relacionado 2 */
        $folio_relacionado_2      = $factura->folio_relacionado_2;
        $folio_2                  = $factura->folio_2;
        $metodo_2                 = $factura->metodo_2;
        $importe_pagado_2         = $factura->importe_pagado_2;
        $num_parcialidad_2        = $factura->num_parcialidad_2;
        $importe_saldo_anterior_2 = $factura->importe_saldo_anterior_2;
        $importe_saldo_insoluto_2 = $factura->importe_saldo_insoluto_2;
        /* Relacionado 3 */
        $folio_relacionado_3      = $factura->folio_relacionado_3;
        $folio_3                  = $factura->folio_3;
        $metodo_3                 = $factura->metodo_3;
        $importe_pagado_3         = $factura->importe_pagado_3;
        $num_parcialidad_3        = $factura->num_parcialidad_3;
        $importe_saldo_anterior_3 = $factura->importe_saldo_anterior_3;
        $importe_saldo_insoluto_3 = $factura->importe_saldo_insoluto_3;

        /* DATOS DE LA EMPRESA EMISORA */
        $emisora = EmpresaEmisora::find($id_empresa_emisora);
        $usr_timbre    = (isset($emisora->user)) ? $emisora->user : $emisora->user_timbre;
        $cp_emisora    = $emisora->cp;

        ### 1. CONFIGURACIÓN INICIAL ######################################################
        # 1.1 Configuración de zona horaria
        date_default_timezone_set('America/Mexico_City'); //
        $data_respuesta['timezone'] = date_default_timezone_get();

        ### 2. ASIGNACIÓN DE VALORES A VARIABLES ###################################################
        $repositorio = '/repositorio/' . Session::get('empresa')['id'] . '/timbrado/';
        $repositorio2 = '/storage/repositorio/' . Session::get('empresa')['id'] . '/timbrado/';
        $recursos = 'timbrado/';

        $folder_repositorio = $repositorio;

        $SendaCFDI     = $folder_repositorio . 'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaEmpGRAFS = $folder_repositorio . 'archs_grafs/';   // 2.2 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR       = $folder_repositorio . 'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR_url   = $repositorio2 . 'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPreCFDI  = $folder_repositorio . 'archs_precdfi/'; // 2.4 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPDF      = $folder_repositorio . 'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaRESP     = $folder_repositorio . 'archs_resp/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).

        $SendaPEMS    = $recursos . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $recursos . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $recursos . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);

        /* Checar si existen directorios, si no crearlos*/

        Storage::disk('public')->makeDirectory($folder_repositorio, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaCFDI, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaEmpGRAFS, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaPreCFDI, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaQR, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaPDF, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaRESP, $mode = 0777, true, true);


        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr($usr_timbre, 7, strlen($usr_timbre));
        $condicion = ($condicion == null) ? 1 : $condicion;

        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
        $credenciales = DB::select($query);

    

        $username    = base64_decode($credenciales[0]->user);
        $password    = base64_decode($credenciales[0]->password);
        $ip_servicio = $credenciales[0]->servicio;
        $regimen_fiscal_emisor = $credenciales[0]->regimen_fiscal;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $file_cer       = $credenciales[0]->nombre_archivo . ".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo . ".key.pem";   // 3.3 Nombre del archivo .cer.key

        ### 4. DATOS GENERALES DE LA FACTURA ##################################################
        $mxn = "MXN";
        $data_respuesta['tipo_c'] = $factura->tipo_comprobante;

        if ($factura->tipo_compronante == 'P') {
            $mxn = "XXX";
        }

        $fact_serie        = "A";                             // 4.1 Número de serie.
        $fact_folio        = mt_rand(1000, 9999);             // 4.2 Número de folio (para efectos de demostración se asigna de manera aleatoria).
        $no_Fac            = $fact_serie . $fact_folio;       // 4.3 Serie de la factura concatenado con el número de folio.
        //$fact_tipo_compr   = "I";                             // 4.4 Tipo de comprobante.
        $fact_tipo_compr   = $factura->tipo_comprobante;                             // 4.4 Tipo de comprobante.
        $tasa_iva          = 16;                              // 4.5 Tasa del impuesto IVA.
        $subTotal          = 0;                               // 4.6 Subtotal, suma de los importes antes de descuentos e impuestos (se calculan mas abajo).
        $descuento         = 0;                               // 4.7 Descuento (se calculan mas abajo).
        $iva               = 0;                               // 4.8 IVA, suma de los impuestos (se calculan mas abajo).
        $total             = 0;                               // 4.9 Total, Subtotal - Descuentos + Impuestos (se calculan mas abajo).
        $fecha_fact        = date("Y-m-d") . "T" . date("H:i:s"); // 4.10 Fecha y hora de facturación.
        $num_cta_pago      = "6473";                          // 4.11 Número de cuenta (sólo últimos 4 dígitos, opcional).
        $condiciones_pago  = "CONDICIONES";                   // 4.12 Condiciones de pago.
        $forma_pago        = $forma_pago;                     // 4.13 Forma de pago.
        $metodo_pago       = $metodo_pago;                    // 4.14 Clave del método de pago. Consultar catálogos de métodos de pago del SAT.
        $tipo_cambio       = 1;                               // 4.15 Tipo de cambio de la moneda.
        $lugar_expedicion  = $cp_emisora;                     // 4.16 Lugar de expedición (código postal).
        $moneda            = $mxn;                           // 4.17 Moneda        
        $totalImpuestosRetenidos   = 0;                       // 4.18 Total de impuestos retenidos (se calculan mas abajo).
        $totalImpuestosTrasladados = 0;                       // 4.19 Total de impuestos trasladados (se calculan mas abajo).

        ### 5. MUESTRA LA ZONA HORARIA PREDETERMINADA DEL SERVIDOR (OPCIONAL A MOSTRAR) ######
        $data_respuesta['fecha_factura'] = $fecha_fact;

        $query = "SELECT SUM(monto*cantidad) as total, sum(impuesto_retenido) as retenidos,iva_considerar FROM facturas_detalle WHERE id_factura='" . $factura->id . "' AND estatus='0';";
        $result = DB::connection('empresa')->select($query);

        $factura_retenidos = 0;
        $valida_iva_considerar=0;

        if ($result[0]->retenidos == 1) {
            $factura_retenidos = 1;
        }
        if($result[0]->iva_considerar ==1){
            $valida_iva_considerar=1;
        }

        $subtotal_factura = $result[0]->total;
        $iva_factura      = 0;

        /* PROCESAMOS LOS CONCEPTOS */

        $conceptos = array();

        if (!empty($conceptos_factura)) {
            foreach ($conceptos_factura as $c) {
                $importe = round($c->cantidad * $c->monto, 2);
                $tmp_iva = 0;
                if($c->iva_considerar == 1){
                    $tmp_iva = round($importe * 0.16, 2);
                    $iva_factura+=$tmp_iva;   
                }
                // Checar si solo agregamos if && $factura_comprobante == I
                if ($factura_retenidos == 1) {
                    $iva_retenido = round($importe * 0.06, 2);
                } else {
                    $iva_retenido = 0;
                }

                $dato = array(
                    'claveProdServ'=> $c->clave, // 6.1 Clave del SAT correspondiente al artículo o servicio (consultar el catálogo de productos del SAT).
                    'cantidad'=> $c->cantidad, // 6.3 Cantidad.
                    'claveUnidad'=> $c->unidad, // 6.4 Clave del SAT correspondiente a la unidad de medida (consultar el catálogo de productos del SAT).
                    'descripcion'=> $c->concepto, // 6.6 Descripción del artículo o servicio.
                    'valorUnitario'=> $c->monto,  // 6.7 Valor unitario del artículo o servicio.
                    'importe'=>  $importe,  // 6.8 Importe del artículo o servicio.
                    'descuento'=> '0',    // 6.9 Descuento aplicado al artículo o servicio.
                    'traslado_Base'=> $importe, // 7.1 Atributo requerido para señalar la base para el cálculo del impuesto, la determinación de la base se realiza de acuerdo con las disposiciones fiscales vigentes. No se permiten valores negativos
                    'traslado_Impuesto'=> '002', // 7.2 Atributo requerido para señalar la clave del tipo de impuesto trasladado aplicable al concepto (consultar catálogos del SAT).
                    'traslado_TipoFactor'=> 'Tasa', // 7.3 Atributo requerido para señalar la clave del tipo de factor que se aplica a la base del impuesto (consultar catálogos del SAT).
                    'traslado_TasaOCuota'=> ($c->iva_considerar == 1) ? str_pad($iva_empresa, 8, '0') : '0.000000',
                    'traslado_Importe'=> $tmp_iva,  // 7.4 Atributo condicional para señalar el valor de la tasa o cuota del impuesto que se traslada para el presente concepto. Es requerido cuando el atributo TipoFactor tenga una clave que corresponda a Tasa o Cuota (consultar catálogos del SAT).
                    'retencion_Base'=> ($factura->tipo_comprobante == 'I' || 'E') ? $importe : 0,
                    'retencion_Impuesto'=> ($factura->tipo_comprobante == 'I' || 'E') ? '002' :'',
                    'retencion_TipoFactor'=> ($factura->tipo_comprobante == 'I' || 'E') ? 'Tasa' :'',
                    'retencion_TasaOCuota'=> ($factura->tipo_comprobante == 'I' || 'E') ? '0.060000' :0,
                    'retencion_Importe'=> ($factura->tipo_comprobante == 'I' || 'E' ) ? $iva_retenido :0,
                );
                array_push($conceptos, $dato);

                // 8.1 Calculando subTotal.
                $subTotal += $dato['importe'];

                // 8.2 Total impuestos trasladados.
                $totalImpuestosTrasladados += $dato['traslado_Importe'];

                // 8.3 Total impuestos retenidos.
                $totalImpuestosRetenidos += floatval($dato['retencion_Importe']);

            }
        }

        // 8.1 Calculando subTotal.
        $subTotal = number_format($subTotal, 2, '.', '');

        // 8.2 Total impuestos trasladados.
        $totalImpuestosTrasladados = round($totalImpuestosTrasladados, 2);

        // 8.3 Total impuestos retenidos.

        $total = $subTotal - $descuento + $totalImpuestosTrasladados - $totalImpuestosRetenidos;


        ### 9. DATOS GENERALES DEL EMISOR #################################################
        $emisor_rs     = $credenciales[0]->razon_social_ss;                         // 9.1 Nombre o Razón social.
        $emisor_rfc    = $credenciales[0]->rfc;                               // 9.2 RFC (al momento de timbrar el SAT comprueba que el RFC se encuentre registrado y vigente en su base de datos)
        $emisor_regfis = "REGIMEN GENERAL DE PERSONAS MORALES";  // 9.3 Régimen fiscal.

        ### 10. DATOS GENERALES DEL RECEPTOR (CLIENTE) #####################################
        $RFC_Recep    = $rfc_empresa;                                                   // 10.1 RFC (al momento de timbrar el SAT comprueba que el RFC se encuentre registrado y vigente en su base de datos).
        $receptor_rfc = (strlen($RFC_Recep) == 12) ? " " . $RFC_Recep :  $RFC_Recep; // 10.2 Al RFC de personas morales se le antecede un espacio en blanco para que su longitud sea de 13 caracteres ya que estos son de longitud 12.
        $receptor_rs  = $empresa;                                                    // 10.3 Nombre o Razon social.

        ### 11. CREACIÓN Y ALMACENAMIENTO DEL ARCHIVO .XML (CFDI) ANTES DE SER TIMBRADO ###################

        #== 11.1 Creación de la variable de tipo DOM, aquí se conforma el XML a timbrar posteriormente.

        $certificado = new Certificado(Storage::disk('public')->get($SendaPEMS . $file_cer));

        #== 11.3 Rutina de integración de nodos =========================================
        if ($factura->tipo_comprobante == 'P') {
            $comprobanteAtributos = [
                "Serie"             => $fact_serie,
                "Folio"             => $fact_folio,
                "Fecha"             => date("Y-m-d") . "T" . date("H:i:s"),
                'Moneda' => "XXX",
                "SubTotal" => 0,
                "Total" => 0,
                "TipoDeComprobante" => $fact_tipo_compr,
                "LugarExpedicion"   => $lugar_expedicion,
                'Exportacion' => '01',
            ];

        } else {
            $comprobanteAtributos = [
                "Serie"             => $fact_serie,
                "Folio"             => $fact_folio,
                "Fecha"             => date("Y-m-d") . "T" . date("H:i:s"),
                "FormaPago"         => $forma_pago,
                "CondicionesDePago" => $condiciones_pago,
                "Moneda"            => $moneda,
                "TipoCambio"        => $tipo_cambio,
                "TipoDeComprobante" => $fact_tipo_compr,
                "MetodoPago"        => $metodo_pago,
                "LugarExpedicion"   => $lugar_expedicion,
                'Exportacion' => '01',
            ];

        }

        $creator = new CfdiCreator40($comprobanteAtributos, $certificado);
        $comprobante = $creator->comprobante();
        if ($factura->tipo_comprobante == 'P') {

            // Creación del nodo de pago:20
            $pagoA = new \CfdiUtils\Nodes\Node(
                'pago20:Pagos', // nombre del elemento raíz
                [ // nodos obligatorios de XML y del nodo
                    "xmlns:pago20" => "http://www.sat.gob.mx/Pagos20",
                    'xsi:schemaLocation' => 'http://www.sat.gob.mx/Pagos20'
                        . ' http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd',
                    'Version' => '2.0',
                ]
            );
        }

        ///////////// TIPOE 'E' ///////////////////////////////////////////////
        if ($factura->tipo_comprobante == 'E') {
            $comprobante->addCfdiRelacionados(["TipoRelacion" => $tipo_relacion])->addCfdiRelacionado(["UUID" => $folio_relacionado]);
        }

        // No agrego (aunque puedo) el Rfc y Nombre porque uso los que están establecidos en el certificado
        $comprobante->addEmisor([
            'RegimenFiscal' => $regimen_fiscal_emisor,
            'Nombre' => $emisor_rs,
        ]);

        $comprobante->addReceptor([
            'Rfc' => trim($receptor_rfc),
            'Nombre' => $receptor_rs,
            'UsoCFDI' => $uso_cfdi_receptor,
            'RegimenFiscalReceptor' => $regimen_receptor,
            'DomicilioFiscalReceptor' =>  $domicilio_fiscal_receptor
        ]);


        #== 11.4 Ciclo "for", recopilación de datos de artículos e integración de sus respectivos nodos =

        if ($factura->tipo_comprobante == 'P') {
            $conceptoA=$comprobante->addConcepto([
                'ClaveProdServ' => "84111506",
                'Cantidad' => "1",
                'ClaveUnidad' => "ACT",
                'Descripcion' => "Pago",
                'ValorUnitario' => '0',
                'Importe' => '0',
                'ObjetoImp' => '01',
            ]);

        }else{
            foreach ($conceptos as $concepto){

                $conceptoA=$comprobante->addConcepto([
                    "ClaveProdServ"  => $concepto['claveProdServ'],
                    "Cantidad"       => $concepto['cantidad'],
                    "ClaveUnidad"    => $concepto['claveUnidad'],
                    "Descripcion"    => $concepto['descripcion'],
                    "ValorUnitario"  => number_format($concepto['valorUnitario'], 2, '.', ''),
                    "Importe"        => number_format($concepto['importe'], 2, '.', ''),
                    "Descuento"      => number_format($concepto['descuento'], 2, '.', ''),
                    "ObjetoImp"      => '02'
                ]);

                if ($concepto['traslado_TipoFactor'] == "Exento") {
                    $conceptoA->addTraslado([
                        "Base" => number_format($concepto['traslado_Base'], 2, '.', ''),
                        "Impuesto" => $concepto['traslado_Impuesto'],
                        "TipoFactor" => $concepto['traslado_TipoFactor']
                    ]);
                } else {
                    $conceptoA->addTraslado([
                        "Base" => number_format($concepto['traslado_Base'], 2, '.', ''),
                        "Impuesto" => $concepto['traslado_Impuesto'],
                        "TipoFactor" => $concepto['traslado_TipoFactor'],
                        "TasaOCuota" => $concepto['traslado_TasaOCuota'],
                        "Importe" => number_format($concepto['traslado_Importe'], 2, '.', '')
                    ]);
                }

                if ($factura_retenidos == 1) {
                    $conceptoA->addRetencion([
                        "Base" => number_format($concepto['retencion_Base'], 2, '.', ''),
                        "Impuesto" => $concepto['retencion_Impuesto'],
                        "TipoFactor" => $concepto['retencion_TipoFactor'],
                        "TasaOCuota" => $concepto['retencion_TasaOCuota'],
                        "Importe" => number_format($concepto['retencion_Importe'], 2, '.', '')
                    ]);

                }

            }
        }



        #== 11.5 Impuestos retenidos y trasladados ==========================================
        if ($factura->tipo_comprobante == 'P') {

            $pagoA->addChild(new \CfdiUtils\Nodes\Node('pago20:Totales', [
                "MontoTotalPagos" => number_format($monto, 2, '.', '')
            ]));

            $pagoA->addChild(new \CfdiUtils\Nodes\Node('pago20:Pago', [
                "FechaPago" => $fecha_pago,
                "FormaDePagoP" => $forma_pago,
                "MonedaP" => "MXN",
                "Monto" => $monto,
                "TipoCambioP" => "1"
            ]));

            //MetododePago  FolioRelacionado  folio  importepagado  numparcialidad  importesaldoante  importesaldoinsolu
            $pago_pago=$pagoA->getIterator()[1];
            $pago_pago->addChild(new \CfdiUtils\Nodes\Node('pago20:DoctoRelacionado', [
                "IdDocumento" => $folio_relacionado,
                "Folio"            => $folio,
                "MonedaDR"         => "MXN",
                "NumParcialidad"   => $num_parcialidad,
                "ImpSaldoAnt"      => $importe_saldo_anterior,
                "ImpPagado"        => $importe_pagado,
                "ImpSaldoInsoluto" => $importe_saldo_insoluto,
                "ObjetoImpDR" => "01",
                "EquivalenciaDR" => "1"
            ]));


            if ($importe_pagado_2 > 0) {

                $pago_pago->addChild(new \CfdiUtils\Nodes\Node('pago20:DoctoRelacionado', [
                    "IdDocumento"    => $folio_relacionado_2,
                    "Folio"            => $folio_2,
                    "MonedaDR"         => "MXN",
                    "NumParcialidad"   => $num_parcialidad_2,
                    "ImpSaldoAnt"      => $importe_saldo_anterior_2,
                    "ImpPagado"        => $importe_pagado_2,
                    "ImpSaldoInsoluto" => $importe_saldo_insoluto_2,
                    "ObjetoImpDR" => "01",
                    "EquivalenciaDR" => "1"
                ]));

            }

            if ($importe_pagado_3 > 0) {

                $pago_pago->addChild(new \CfdiUtils\Nodes\Node('pago20:DoctoRelacionado', [
                    "IdDocumento" => $folio_relacionado_3,
                    "Folio" => $folio_3,
                    "MonedaDR" => "MXN",
                    "NumParcialidad" => $num_parcialidad_3,
                    "ImpPagado" => $importe_pagado_3,
                    "ImpSaldoAnt" => $importe_saldo_anterior_3,
                    "ImpSaldoInsoluto" => $importe_saldo_insoluto_3,
                    "ObjetoImpDR" => "01",
                    "EquivalenciaDR" => "1"
                ]));

            }
            $comprobante->addComplemento($pagoA);
        }

        if ($factura->tipo_comprobante == 'P')
            $creator->addSumasConceptos(null, 0);
        else
            $creator->addSumasConceptos(null, 2);


        $key = Storage::disk('public')->get($SendaPEMS . $file_key);

        $creator->addSello($key, $password);
        $creator->moveSatDefinitionsToComprobante();

        $document_save=storage_path('app/public'.$SendaCFDI."CFDI-33_Factura_" . $no_Fac . ".xml");

        // método de ayuda para generar el xml y guardar los contenidos en un archivo
        $creator->saveXml($document_save);
        $xmlA =  $creator->asXml();

        $client = new \SoapClient($ip_servicio);

        $xml_content = $xmlA;

        $params = array(
            "xml" => $xml_content,
            "username" => $username,
            "password" => $password,
        );

        $response = $client->__soapCall("stamp", array($params));

        $certificado = $certificado->getSerial();
        $sello_cdf = $creator->comprobante()->attributes()->get('Sello');
        $cadena_origen = $creator->buildCadenaDeOrigen();

        $facturador = TimbradoFacturador::where('factura',$factura->id)->first();
        $data_respuesta['id_factura'] = $factura->id;

        if(!$facturador){
            $facturador = new TimbradoFacturador();
        }

        if (isset($response->stampResult->Incidencias->Incidencia)) {
            $facturador->fecha_timbrado   = null;
            $facturador->sello_sat        = 'error';
            $facturador->certificado_sat  = 'error';
            $facturador->sello_cfdi       = 'error';
            $facturador->folio_fiscal     = 'error';
            $facturador->xml_enviado      = $xmlA;
            $facturador->no_factura       = $no_Fac;
            $facturador->respuesta_pac    = json_encode($response);
            $facturador->cadena_original  = $cadena_origen;
            $facturador->estatus_timbre   = 2;
            $facturador->emisora          = $id_empresa_emisora;
            $facturador->factura          = $factura->id;
            $facturador->save();

            $data_respuesta['error'] = true;
            $data_respuesta['codigo_error'] = $response->stampResult->Incidencias->Incidencia->CodigoError;
            $data_respuesta['MENSAJE_error'] = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia;
            $data_respuesta['xml_enviar'] = $xmlA;
            $data_respuesta['respuesta'] = json_encode($response);
        }else{
            $facturador->fecha_timbrado   = date("Y-m-d\TH:i:s");
            $facturador->sello_sat        = $response->stampResult->SatSeal;
            $facturador->certificado_sat  = $response->stampResult->NoCertificadoSAT;
            $facturador->sello_cfdi       = $sello_cdf;
            $facturador->folio_fiscal     = $response->stampResult->UUID;
            $facturador->xml_enviado      = $xmlA;
            $facturador->no_factura       = $no_Fac;
            $facturador->respuesta_pac    = json_encode($response);
            $facturador->cadena_original  = $cadena_origen;
            $facturador->file_pdf         = "CFDI-33_Factura_" . $no_Fac . ".pdf";
            $facturador->file_xml         = "CFDI-33_Factura_" . $no_Fac . ".xml";
            $facturador->importe          = $subtotal_factura + $iva_factura;
            $facturador->receptor         = $receptor_rfc;
            $facturador->emisor           = $emisor_rfc;
            $facturador->certificado_tim  = $certificado;
            $facturador->fecha_emision    = date("Y-m-d\TH:i:s");
            $facturador->estatus_timbre   = 1;
            $facturador->emisora          = $id_empresa_emisora;
            $facturador->factura          = $factura->id;

            $facturador->save();
            $factura->estatus = 1;
            $factura->update();

            $data_respuesta['error'] = false;
            $data_respuesta['version_cfdi']   = '4.0';
            $data_respuesta['version_timbre'] = '1.0';
            $data_respuesta['sello_sat']      = $response->stampResult->SatSeal;
            $data_respuesta['cert_sat']       = $response->stampResult->NoCertificadoSAT;
            $data_respuesta['sello_cfd']      = $sello_cdf;
            $data_respuesta['fecha_tim']      = date("Y-m-d\TH:i:s");
            $data_respuesta['timbre_uuid']    = $response->stampResult->UUID;

            $data_respuesta['no-fac'] = $no_Fac;
            $data_respuesta['archivo_xml']=$facturador->file_xml;

            $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $response->stampResult->UUID . "&re=" . $emisor_rfc . "&rr=" . $receptor_rfc . "&tt=" . $total . "&fe=" . substr($sello_cdf, -8);
            $data_respuesta['qr'] = $qr;

            $file = fopen($document_save, "w");
            fwrite($file, $response->stampResult->xml);
            fclose($file);

            $this->crearFacturaPdf($factura->id,$factura->tipo_comprobante);
        }

        return view('contabilidad.timbrado.04_factura', compact('data_respuesta'));

    }

    public function crearFacturaPdf($id,$tipo)
    {
        cambiarBase(Session::get('base'));
        $factura = Factura::with(['emisor','conceptos','timbradoFacturador'])->find($id);

        $document_save=storage_path('app/public/repositorio/'.Session::get('empresa')['id'].'/timbrado/archs_pdf/'.$factura->timbradoFacturador->file_pdf);

        switch ($tipo) {
            case 'I':
                $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $factura->timbradoFacturador->folio_fiscal . "&re=" . $factura->timbradoFacturador->emisor . "&rr=" . $factura->timbradoFacturador->receptor . "&tt=" . $factura->timbradoFacturador->importe . "&fe=" . substr($factura->timbradoFacturador->sello_sat, -8);
                Pdf::loadView('contabilidad/factura/documentos/cfdi_pdf', ['data' => $factura, 'qr' => $qr])->save($document_save);
                break;
            case 'E':
                $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $factura->timbradoFacturador->folio_fiscal . "&re=" . $factura->timbradoFacturador->emisor . "&rr=" . $factura->timbradoFacturador->receptor . "&tt=" . $factura->timbradoFacturador->importe . "&fe=" . substr($factura->timbradoFacturador->sello_sat, -8);
                Pdf::loadView('contabilidad/factura/documentos/cfdi_pdf', ['data' => $factura, 'qr' => $qr])->save($document_save);
                break;
            case 'P':
                $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $factura->timbradoFacturador->folio_fiscal . "&re=" . $factura->timbradoFacturador->emisor . "&rr=" . $factura->timbradoFacturador->receptor . "&tt=" . $factura->timbradoFacturador->importe . "&fe=" . substr($factura->timbradoFacturador->sello_sat, -8);
                Pdf::loadView('contabilidad/factura/documentos/cfdi_pagos_pdf', ['data' => $factura, 'qr' => $qr])->save($document_save);
                break;
        }


    }

    public function downloadFacturaPdf(Request $request){
        cambiarBase(Session::get('base'));
        $factura = Factura::with(['emisor','conceptos','timbradoFacturador'])->find($request->id);
        $path=storage_path('app/public/repositorio/'.Session::get('empresa')['id'].'/timbrado/archs_pdf/'.$factura->timbradoFacturador->file_pdf);

        if(!file_exists($path)){
            $this->crearFacturaPdf($factura->id,$factura->tipo_comprobante);
        }

        $file = \Illuminate\Support\Facades\Storage::disk('public')->get('repositorio/'.Session::get('empresa')['id'].'/timbrado/archs_pdf/'.$factura->timbradoFacturador->file_pdf);
        return (new Response($file, 200))->header('Content-Type', 'application/pdf');
    }

    public function downloadFacturaXml($id, $archivo)
    {

        $url = storage_path() . "/app/public/repositorio/" . $id . "/timbrado/archs_cfdi/" . $archivo;
        //verificamos si el archivo existe y lo retornamos
        if (File::exists($url)) {
            return response()->download($url);
        }
        //si no se encuentra lanzamos un error 404.
        abort(404);
    }

    public function cancelarFactura(Request $request){
        cambiarBase(Session::get('base'));
        $factura = Factura::with(['emisor','timbradoFacturador'])->find($request->id);

        $user_timbre= substr($factura->emisor->user_timbre, 7, strlen($factura->emisor->user_timbre));
        $credencial=TimbradoCredenciales::where('id',$user_timbre)->first();

        $username    = base64_decode($credencial->user);
        $password    = base64_decode($credencial->password);
        $url = $credencial->servicio_cancelacion;
        $file_cer       = $credencial->nombre_archivo . ".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credencial->nombre_archivo . ".key.pem";   // 3.3 Nombre del archivo .cer.key
        $taxpayer = $credencial->rfc;

        $cer_content = Storage::disk('public')->get('timbrado/archs_pem/'. $file_cer);
        $key_content = Storage::disk('public')->get('timbrado/archs_pem/' . $file_key);


        $client = new \SoapClient($url, array('trace' => 1));

        $uuids = array("UUID" => $factura->timbradoFacturador->folio_fiscal, "Motivo" => "02", "FolioSustitucion" => "");
        $uuid_ar = array('UUID' => $uuids);
        $params = array("UUIDS" => $uuid_ar,
            "username" => $username,
            "password" => $password,
            "taxpayer_id" => $taxpayer,
            "cer" => $cer_content,
            "key" => $key_content);


        $response = $client->__soapCall("cancel", array($params));


        $factura_cancelada = new TimbradoCancelacionesFacturador;

        $factura_cancelada->emisora           = $factura->emisor->id;
        $factura_cancelada->factura           = $factura->id;
        $factura_cancelada->fecha_cancelacion = isset($response->cancelResult->Fecha) ?  $response->cancelResult->Fecha:'';
        $factura_cancelada->request_cancel    = '';
        $factura_cancelada->response          = json_encode($response);
        $factura_cancelada->xml_acuse_cancel  = '';
        $factura_cancelada->sello_sat         = '';
        $factura_cancelada->file_acuse        = '';
        $factura_cancelada->file_soap         = '';
        $factura_cancelada->no_factura        = $factura->timbradoFacturador->no_factura;

        $data_respuesta=array();
        $data_respuesta['error'] = true;
        $data_respuesta['no_factura']=$factura->timbradoFacturador->no_factura;
        $data_respuesta['folio_fiscal']=$factura->timbradoFacturador->folio_fiscal;
        $data_respuesta['soap']= json_encode($response);

        $estado_pac = (string)$response->cancelResult->Folios->Folio->EstatusUUID;

        switch ($estado_pac) {
            case "704":
                $mensaje = 'Error con la contraseña de la llave privada';
                break;
            case "708":
                $mensaje = "Error de conexion del SAT ....";
                break;
            case "202":
                $mensaje = "202: UUID Cancelado Previamente";
                $factura_cancelada->save();

                $factura->estatus = 2;
                $factura->save();

                $timbrado_facturador=TimbradoFacturador::where('id',$factura->timbradoFacturador->id)->first();
                $timbrado_facturador->estatus_timbre = 2;
                $timbrado_facturador->save();
                $data_respuesta['error'] = false;
                break;
            case "203":
                $mensaje = "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                break;
            case "205":
                $mensaje = "205: UUID No existente";
                break;
            case "201":
                $mensaje = "Factura cancelada exitosamente";
                $factura_cancelada->save();

                $factura->estatus = 2;
                $factura->save();

                $timbrado_facturador=TimbradoFacturador::where('id',$factura->timbradoFacturador->id)->first();
                $timbrado_facturador->estatus_timbre = 2;
                $timbrado_facturador->save();
                $data_respuesta['error'] = false;
                break;
            default:
                $mensaje = "Error desconocido";
                break;
        }

        $data_respuesta['error_msg']=$mensaje;

        return view('contabilidad.timbrado.cancelar_factura', compact('data_respuesta'));
    }

    public function resumenCFDI($periodo){
        $base = Session::get('base');
        $datosCFDI = array();
        cambiarBase($base);

        $timbres = TimbradoEmpleado::
        where('id_periodo', $periodo)
            ->where('sello_sat','<>', 'error')
            ->where('estatus_timbre', '1')
            ->get();

        $timbres_cancelados =TimbradoCancelacionesEmpleado::
        where('id_periodo', $periodo)
            ->where('sello_sat','<>', 'error')
            ->get();

        foreach ($timbres_cancelados as $timbre){
            $timbre_cancelado = TimbradoEmpleado::where('num_factura', $timbre->no_factura)->where('sello_sat', '!=', 'error')->first();
            if($timbre_cancelado){
                $resumen_datos = $this->extraerDatosCDFI($timbre_cancelado->respuesta_pac, $timbre->fecha_cancelacion);
                array_push($datosCFDI,$resumen_datos);
            }
        }

        foreach ($timbres as $timbre){
            $resumen_datos = $this->extraerDatosCDFI($timbre->respuesta_pac);
            array_push($datosCFDI,$resumen_datos);
        }

        return Excel::download(new ResumenCFDIExport($datosCFDI),'ResumenCFDIs_'.date('d-m-Y_H:i').'.xlsx');
    }

    public function extraerDatosCDFI($respuesta_pac, $fecha_cancelacion = null){
        $stm = trim(htmlspecialchars_decode(html_entity_decode($respuesta_pac))," \t\n\r\"");
        $str_fin = strlen($stm);
        $str_inicio = strpos($stm, '<s0:xml>') + 8;
        $str_tmp = substr($stm,$str_inicio,$str_fin);
        $str_fin = strpos($str_tmp, '</s0:xml>');
        $str_tmp = substr($str_tmp,0,$str_fin);
        $str_tmp = preg_replace("/[\r\n|\n|\r]+/", "", $str_tmp);
        $str_tmp = str_replace("\\", '', $str_tmp);

        $cfdi = Cfdi::newFromString($str_tmp);

        $complemento = $cfdi->getNode(); // Nodo de trabajo del nodo cfdi:Comprobante

        $tfd = $complemento->searchNode('cfdi:Complemento', 'tfd:TimbreFiscalDigital');
        $emisor = $complemento->searchNode('cfdi:Emisor');
        $receptor = $complemento->searchNode('cfdi:Receptor');

        $resumen_datos = [
            'uuid'              => $tfd['UUID'],
            'RFC_emisor'        => $emisor['Rfc'],
            'nombre_emisor'     => $emisor['Nombre'],
            'RFC_receptor'      => $receptor['Rfc'],
            'nombre_receptor'   => $receptor['Nombre'],
            'RFC_pac'           => $tfd['RfcProvCertif'],
            'fecha_emision'     => $complemento['Fecha'],
            'fecha certificado' => $tfd['FechaTimbrado'],
            'monto'             => $complemento['Total'],
            'efecto_comprobante'=> $complemento['TipoDeComprobante'],
            'estatus'           => $fecha_cancelacion ? 'Cancelado' : "Activo",
            'fecha_cancelacion' => $fecha_cancelacion,
        ];

        return $resumen_datos;
    }

}
