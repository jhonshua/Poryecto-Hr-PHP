<?php

namespace App\Http\Controllers\Procesos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\periodosNomina;
use App\Models\TimbradoAguinaldoEmpleado;
use App\Models\EmpresaEmisora;
use Storage;

include(public_path() . "/lib/qrlib/qrlib.php");

class TimbradoAguinaldoController extends Controller
{
    private $array_jornada = array('01' => 'DIURNA', '02' => 'NOCTURNA', '03' => 'MIXTA', '04' => 'POR HORA', '05' => 'REDUCIDA', '06' => 'CONTINUADA', '07' => 'PARTIDA', '08' => 'POR TURNOS', '99' => 'OTRA JORNADA');
    private $array_contrato = array('01' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
                            "02" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
                            "03" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
                            "04" => 'CONTRATO DE TRABAJO POR TEMPORADA',
                            "05" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
                            "06" => 'Contrato de trabajo con capacitación inicial',
                            "07" => 'Modalidad de contratación por pago de hora laborada',
                            "08" => 'Modalidad de trabajo por comisión laboral',
                            "09" => 'Modalidades de contratación donde no existe relación de trabajo',
                            "10" => 'JUBILACIÓN, PENSIÓN, RETIRO',
                            "99" => 'OTRO'
                            );

    public function paso1(){
        $base = Session::get('base');
        cambiarBase($base);
        $error = false;  

        $deptos = implode(',',Session::get('usuarioDepartamentos'));
        $query = "SELECT *
                  FROM departamentos  
                  WHERE estatus = 1 
                  AND id in ($deptos);";
       
        $departamentos = DB::connection('empresa')->select($query);   
        return view('procesos.timbrado-aguinaldo.inicio',compact('error','departamentos'));
    }

    public function paso2(Request $request){
        $base = Session::get('base');        
        cambiarBase($base);     

        $todos = $request->input('todos') ?? false;
        $departamentos = $request->deptos;
        $repo = Session::get('empresa')['id'];
        $query ="SELECT ejercicio from parametros";
        $ejercicio = DB::connection('empresa')->select($query)[0]->ejercicio; 

        if(is_array($departamentos))
        {
            $cadena_departamentos = implode(",",$departamentos);
        }else{
            $cadena_departamentos = $departamentos;
        }

        /* Verificamos si ya existen timbrados para el periodo */
        $r = DB::connection('empresa')
                     ->table('timbrado_aguinaldo')
                     ->where('ejercicio',$ejercicio)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();

        $existen_timbrados = $r; 
        /* Traemos los empleados para aguinaldo y su calculo de aguinaldo  */
        $query = "SELECT * FROM aguinaldo agui 
                  JOIN empleados em 
                  ON agui.id_empleado = em.id 
                  AND em.estatus = 1 
                  AND agui.neto > 0 
                  AND em.id_departamento IN ($cadena_departamentos) 
                  AND agui.ejercicio = '$ejercicio'";
        
        $empleadosR = DB::connection('empresa')->select($query); 
        $empleados = array();
        /* timbrados completo spor cada empleado */
        foreach($empleadosR as $empleado){            
            /* 
                01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                Sacamos tambien el estatus del timbre:
                0 = ?
                1 = Timbrado
                2 = Error
            */
            $query2 = "SELECT * FROM timbrado_aguinaldo 
                                    WHERE id_empleado = '$empleado->id' 
                                    AND ejercicio = '$ejercicio'
                                    AND estatus_timbre = 1";

            $r = DB::connection('empresa')->select($query2); 
             
            $empleado->timbres = $r;

            /*
              02.- Si hay timbres no error //Normalmente es 1
              $numRegistrosreTimbre -> original
            */
            $r = DB::connection('empresa')
                     ->table('timbrado_aguinaldo')
                     ->where('id_empleado',$empleado->id)
                     ->where('ejercicio',$ejercicio)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();

            $empleado->numero_timbres_noerror = $r;    
            /*
            $numRegistrosreTimbre=mysqli_num_rows($rowvalidaregistroreTimbre);
            /* 
               03.- traemos el ultimo registro de  timbre 
               $numRegistrosreTimbreError -> original
            */
            $r = DB::connection('empresa')
                    ->table('timbrado_aguinaldo')
                    ->where('id_empleado',$empleado->id)
                    ->where('ejercicio',$ejercicio)
                    ->orderBy('id','desc')
                    ->first();
                    //dd($r);
            $empleado->ultimo_timbre = $r; 
            /*
            $numRegistrosreTimbreError=mysqli_fetch_array($rowvalidaregistroreTimbreError);
            /* 
              04.-timbres cancelados 
            */
            $r = DB::connection('empresa')
                    ->table('timbrado_cancelaciones_aguinaldo')
                    ->where('id_empleado', $empleado->id)
                    ->where('ejercicio', $ejercicio)
                    ->get();
            $empleado->timbres_cancelados = $r;
            $empleados[] = $empleado;
        }        
        $tipo = 0;
        return view('procesos.timbrado-aguinaldo.lista-empleados',compact('ejercicio','empleados','existen_timbrados','cadena_departamentos','repo','tipo'));
    }

    public function validarMasivo($ejercicio,$cadena){
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $array_jornada = array('01' => 'DIURNA', '02' => 'NOCTURNA', '03' => 'MIXTA', '04' => 'POR HORA', '05' => 'REDUCIDA', '06' => 'CONTINUADA', '07' => 'PARTIDA', '08' => 'POR TURNOS', '99' => 'OTRA JORNADA');
        $array_contrato = array('01' => 'CONTRATO DE TRABAJO POR TIEMPO INDETEMINADO',
                                "02" => 'CONTRATO DE TRABAJO POR OBRA DETERMINADA',
                                "03" => 'CONTRATO DE TRABAJO POR TIEMPO DETERMINADO',
                                "04" => 'CONTRATO DE TRABAJO POR TEMPORADA',
                                "05" => 'CONTRATO DE TRABAJO SUJETO A PRUEBA',
                                "06" => 'Contrato de trabajo con capacitación inicial',
                                "07" => 'Modalidad de contratación por pago de hora laborada',
                                "08" => 'Modalidad de trabajo por comisión laboral',
                                "09" => 'Modalidades de contratación donde no existe relación de trabajo',
                                "10" => 'JUBILACIÓN, PENSIÓN, RETIRO',
                                "99" => 'OTRO'
                            );
        
        /* VALIDACION PREVIA DE USUARIOS'*/
        $base = Session::get('base');
        cambiarBase($base);        
        $cadena_departamentos = base64_decode($cadena);

        /* periodo */
        $periodo = periodosNomina::where('activo', 1)->first();    
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
            FROM aguinaldo 
            WHERE ejercicio = '$ejercicio' 
            AND neto > 0);
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
            if( !preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/",$e->rfc) ){
                $errores['rfc'] = true;
                $num_errores++;
            }

            if(!preg_match("/^[0-9]+$/",$e->nss)){
                $errores['nss'] = true;
                $num_errores++;
            }

            if(!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/",$e->curp)){
                $errores['curp'] = true;
                $num_errores++;
            }

            //VALIDAR REGISTRO PATRONAL
            $queryPatronal="SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                            FROM categorias cat  
                            JOIN empleados em  
                            ON em.id_categoria = cat.id 
                            INNER JOIN singh.registro_patronal re 
                            ON cat.tipo_clase = re.id
                            WHERE em.id = '$e->id' 
                            AND   cat.estatus = 1";
            $registroP = DB::connection('empresa')->select($queryPatronal);

            if($registroP){
                $r = $registroP[0];
                $r->string_contrato = $array_contrato[$r->tipo_contrato];
                
                $r->string_jornada = $array_jornada[$r->tipo_jornada];
            }else{
                $r ="error";
                $errores['registro_patronal'] = true;
                $num_errores++;
            }
            $empleado['registro_patronal'] = $r;
            $empleado['errores'] = $errores;
            $empleados[] = $empleado;
        }
        /* Validación de conceptos de nomina */
        
        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);
        return view('procesos.timbrado-aguinaldo.resultado-validacion-masivo',compact('ejercicio','empleados','cadena_departamentos', 'errores', 'id_periodo'));
        
    }    

    ### Timbrar Masivo(BUCLE) ###################################################
    public function timbrarMasivoBucle($cadena){        
        $numerico=rand(0,99999);
        $base = Session::get('base');
        cambiarBase($base);        
        $cadena_departamentos = base64_decode($cadena);
        /* periodo */
        $periodo      = periodosNomina::where('activo', 1)->first();        
        $id_periodo   = $periodo->id;
        $ejercicio    = $periodo->ejercicio;
        $tipo_nomina  = $periodo->nombre_periodo;
        $dias_periodo = $periodo->dias_periodo;
    
        /* Convertimos el tipo de periodo a procesar */
        $tipo_periodo = null;
        switch(strtoupper($tipo_nomina)){
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
                $tipo_periodo= '04';
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
        /* TRAEMOS LOS EMPLEADOS A TIMBRAR */
        $empleados = array();
        $query = "SELECT id,concat(nombre,' ',apaterno,' ',amaterno) AS nombre
               FROM empleados
               WHERE estatus = 1
               AND tipo_de_nomina = '$tipo_nomina'
               and id_departamento in ($cadena_departamentos)
               AND id IN (
                            SELECT id_empleado 
                            FROM aguinaldo 
                            WHERE ejercicio = '$ejercicio' 
                            AND neto > 0);
              ";
        $empleados = DB::connection('empresa')->select($query);
        //dd($cadena,$periodo,$tipo_nomina,$tipo_periodo,$empleados);
        return view('procesos.timbrado-aguinaldo.timbrado-bucle',compact('periodo','empleados','cadena_departamentos'));
    }

    ### Timbrar Empleado ###################################################
    public function timbrarAguinaldoEmpleado($id_empleado,$cadena,$tipoR, Request $request){     
    //Corresponde al php 04_CFDI_ReciboNominaAguinaldo.php
        $numerico=rand(0,99999);
        $base = Session::get('base');
        cambiarBase($base);        
        $cadena_departamentos = base64_decode($cadena);     
        
        // return response()->json(['exito' => 'exito', 'data' => $cadena_departamentos]);
         
        /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
         $data_string ="";
         $data_respuesta = array();
         
         //verificamos el periodo activo
         $periodo      = periodosNomina::where('activo', 1)->first();        
         
         $id_periodo   = $periodo->id;
         $ejercicio    = $periodo->ejercicio;
         $tipo_nomina  = $periodo->nombre_periodo;
         $dias_periodo = $periodo->dias_periodo;
         $ejercicio    = $periodo->ejercicio;
         $fecha_inicial_periodo = new \DateTime($periodo->fecha_inicial_periodo);
         $fecha_final_periodo   = new \DateTime($periodo->fecha_final_periodo);

         $fecha_inicial_periodoStr = $periodo->fecha_inicial_periodo;
         $fecha_final_periodoStr   = $periodo->fecha_final_periodo;

         /* Convertimos el tipo de periodo a procesar **/
         $tipo_periodo = null;
         switch(strtoupper($tipo_nomina)){
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
                $tipo_periodo= '04';
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
            $query2="SELECT * 
                     FROM timbrado_aguinaldo 
                     WHERE id_empleado = '$id_empleado' 
                     AND ejercicio = '$ejercicio' 
                     AND estatus_timbre = 1";
            $timbres = DB::connection('empresa')->select($query2);   
            
        /* TRAEMOS EMISORA */
        $query ="SELECT em.id_categoria,em.tipo_jornada,em.tipo_contrato,cat.nombre,re.num_registro_patronal,re.tipo_clase,ememi.razon_social,ememi.user_timbre,ememi.cp
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
                 AND em.estatus =1;
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
        $tipo_jornada  = str_pad( $emisora->tipo_jornada,2,'0',STR_PAD_LEFT);
        $tipo_contrato = str_pad( $emisora->tipo_contrato,2,'0',STR_PAD_LEFT);
        switch($emisora->tipo_clase){
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
        /* DATOS DEL EMPLEADO */
        $query = "SELECT *, concat(nombre,' ',apaterno,' ',amaterno) AS nombre_completo
                  FROM empleados
                  WHERE estatus = 1
                  AND id = $id_empleado;";
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
        $antigue=floor(($diff->days+1)/7);
        $antiguedad='P'.$antigue.'W';

        /* DIAS A PAGAR */
        if($fecha_alta_empleado > $fecha_inicial_periodo){
            /* Si el alta es despues de que inicio el periodo se paga los dias proporcionales */
            $diff = $fecha_final_periodo->diff($fecha_alta_empleado);
            $dias_pagados = $diff->days;
        }else{
            $diff = $fecha_final_periodo->diff($fecha_inicial_periodo);
            $dias_pagados = $diff->days;
        }

        /* TOTAL DE PERCEPCIONES */
        $query ="SELECT *
                 FROM rutinas$ejercicio
                 WHERE fnq_valor = 0
                 AND id_periodo = $id_periodo
                 AND id_empleado = $id_empleado";         
        $datos = DB::connection('empresa')->select($query); 
        $datos = $datos[0];

        /* DEDUCCIONES */
        $subsidio_valor      = $datos->subsidio * 1;
        $incapacidades_valor = ($datos->incapacidades == "" || $datos->incapacidades == null) ? 0 : $datos->incapacidades; 
        //$total_gravado_real  = $datos->total_gravado;

        /* FALTAS */
        /* TODO agregar a la query de mas arriba de concepto */
        $faltas_valor = 0;
        $query = "SELECT id
                  FROM conceptos_nomina 
                  WHERE nombre_concepto = 'FALTAS' 
                  AND estatus = 1";
        $f = DB::connection('empresa')->select($query);
        if(!empty($f)){
            $f = $f[0]->id;
            $query = "SELECT Total$f AS valor_falta 
                      FROM rutinas$ejercicio
                      WHERE id_periodo = '$id_periodo'
                      AND id_empleado = '$id_empleado' 
                      AND fnq_valor = 0;";
            $ff = DB::connection('empresa')->select($query);
            $faltas_valor = ($ff[0]->valor_falta == "" || $ff[0]->valor_falta == null)? 0 : $ff[0]->valor_falta;
        }
        
        /* DIAS A PAGAR */
        $dias_a_pagar = ceil(($dias_pagados + 1) - $incapacidades_valor - $faltas_valor);

        /* TRAEMOS AGUINALGO */

        $queryA ="SELECT * FROM aguinaldo 
                  WHERE id_empleado = '$id_empleado' 
                  AND ejercicio = '$ejercicio' 
                  AND  neto > 0; ";
                  //dd($queryA);
        $result = DB::connection('empresa')->select($queryA);
        //dd($result);
        $neto                = $result[0]->neto;
        $impuesto_anual      = $result[0]->impuesto_anual;
        $impuestos           = $result[0]->impuestos;
        $pago_aguinaldo      = $result[0]->pago_aguinaldo;
        $pension_alimenticia = $result[0]->pension_alimenticia;
        $descuentos_otros    = $result[0]->descuentos_otros;
        
        $deducciones_adic = $impuesto_anual + $pension_alimenticia + $descuentos_otros;
        $subtotal = $pago_aguinaldo;

        # 1.1 Configuración de zona horaria
        date_default_timezone_set('America/Mexico_City'); //
        $data_respuesta['timezone'] = date_default_timezone_get();
        
        ### 2. ASIGNACIÓN DE VALORES A VARIABLES ###################################################
        $data_respuesta['idrepo'] = Session::get('empresa')['id'] . '/' . $e->id;

        $url_repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/' . $e->id . '/timbrado/'; 
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

        /* Checar si existen directorios, si no crearlos*/
        Storage::disk('public')->makeDirectory($SendaCFDI, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaEmpGRAFS, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaPreCFDI, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaQR, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaPDF, $mode = 0777, true, true);
        Storage::disk('public')->makeDirectory($SendaRESP, $mode = 0777, true, true);       

        /*Crear si no existen directorios de timbrado*/
        Storage::disk('public')->makeDirectory($SendaPEMS, $mode = 0777, true, true);  
        Storage::disk('public')->makeDirectory($SendaGRAFS, $mode = 0777, true, true);  
        Storage::disk('public')->makeDirectory($SendaXSD, $mode = 0777, true, true);  
        
        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr ( $usr_timbre , 7 ,strlen($usr_timbre) );
        $condicion = ($condicion == null) ? 1 : $condicion;
        $condicion = 1;   
        //TODO quitar(se usa para pruebas)
        $condicion = 1;

        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
        $credenciales = DB::select($query);

        $rfc_emi      = $credenciales[0]->rfc; //rfcemi
        $rfc_emisor   = $rfc_emi;
        $razon_emisor = $credenciales[0]->razon_social;
        $username     = base64_decode($credenciales[0]->user);
        $password     = base64_decode($credenciales[0]->password);
        $ip_servicio  = $credenciales[0]->servicio;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales[0]->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales[0]->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo.".key.pem";   // 3.3 Nombre del archivo .cer.key
        
        ### 4. DATOS GENERALES DE LA FACTURA ##################################################
        $fact_serie        = "A";                             // 4.1 Número de serie.
        $fact_folio        = mt_rand(1000, 9999);             // 4.2 Número de folio (para efectos de demostración se asigna de manera aleatoria).
        $NoFac             = $fact_serie . $fact_folio;         // 4.3 Serie de la factura concatenado con el número de folio.
        $data_respuesta['num_factura'] = $NoFac;
        
        ### PERCEPCIONES ###############################################################
        
        $query = "SELECT uma from $base.parametros";
        $RUMA = DB::select($query);
        $uma=$RUMA[0]->uma;
    
        if( $pago_aguinaldo > ( $uma * 30 ) ){
            $parte_gravada= $pago_aguinaldo - ( $uma * 30 );
            $parte_exenta = $uma * 30;
        }else{
            $parte_gravada = 0;
            $parte_exenta  = $pago_aguinaldo;
        }
        
        // ArraysPercepciones.
        $ArrayPercep_Clave          = ['101'];
        $ArrayPercep_Concepto       = ['AGUINALDO'];
        $ArrayPercep_ImporteExento  = [number_format($parte_exenta,2,'.','')];
        $ArrayPercep_ImporteGravado = [number_format($parte_gravada,2,'.','')];
        $ArrayPercep_TipoPercepcion = ['002'];
        
        $total_percepciones = number_format($parte_gravada,2,'.','');
        $percep_total_gravado = number_format($total_percepciones,2,'.','');
        
        ### DEDUCCIONES ################################################################
        $importe_valor_isr = $impuestos;
        // ArraysDeducciones.
        $ArrayDeduc_Clave=array();
        $ArrayDeduc_Concepto=array();
        $ArrayDeduc_Importe=array();
        $ArrayDeduc_TipoDeduccion=array();
        
        if( $impuestos > 0 ){
            $ArrayDeduc_Clave[]         = '251';
            $ArrayDeduc_Concepto[]      = 'IMPUESTO SOBRE LA RENTA';
            $ArrayDeduc_Importe[]       = $impuestos;
            $ArrayDeduc_TipoDeduccion[] = '002';
        }

        if( $impuesto_anual > 0 ){
            $ArrayDeduc_Clave[]         = '252';
            $ArrayDeduc_Concepto[]      = 'AJUSTE ANUAL';
            $ArrayDeduc_Importe[]       = $impuesto_anual;
            $ArrayDeduc_TipoDeduccion[] = '004';
        }
        
        if( $pension_alimenticia > 0 ){
            $ArrayDeduc_Clave[]         = '253';
            $ArrayDeduc_Concepto[]      = 'PENSION ALIMENTICIA';
            $ArrayDeduc_Importe[]       = $pension_alimenticia;
            $ArrayDeduc_TipoDeduccion[] = '007';
        }
        
        if( $descuentos_otros > 0 ){
            $ArrayDeduc_Clave []         = '254';
            $ArrayDeduc_Concepto []      = 'DESCUENTOS OTROS';
            $ArrayDeduc_Importe  []      = $descuentos_otros;
            $ArrayDeduc_TipoDeduccion [] = '004';
        }

        ### 11. CREACIÓN Y ALMACENAMIENTO DEL ARCHIVO .XML (CFDI) ANTES DE SER TIMBRADO ###################

        #== 11.1 Creación de la variable de tipo DOM, aquí se conforma el XML a timbrar posteriormente.
        $xml = new \DOMdocument('1.0', 'UTF-8');
        $root = $xml->createElement("cfdi:Comprobante");
        $root = $xml->appendChild($root);

        $cadena_original='||';
        $noatt =  array();

        $descuentos = $impuestos + $impuesto_anual + $pension_alimenticia +$descuentos_otros;
        $neto = $neto - $pension_alimenticia - $descuentos_otros;


        if( $impuestos > 0 || $deducciones_adic > 0 ){
            $cadena_original .= $this->cargaAtt($root, array(
                "Version"           => "3.3",
                "Fecha"             => date("Y-m-d")."T".date("H:i:s"),
                "FormaPago"         => "99",
                "NoCertificado"     => $no_certificado,
                "SubTotal"          => number_format($subtotal,2,'.',''),
                "Descuento"         => number_format($descuentos,2,'.',''),
                "Moneda"            => "MXN",
                "Total"             => number_format($neto,2,'.',''),
                "TipoDeComprobante" => "N",
                "MetodoPago"        => "PUE",
                "LugarExpedicion"   => $cp_emisora
                )
            );
        }else{
            $cadena_original .= $this->cargaAtt($root, array(
                "Version"           => "3.3",
                "Fecha"             => date("Y-m-d")."T".date("H:i:s"),
                "FormaPago"         => "99",
                "NoCertificado"     => $no_certificado,
                "SubTotal"          => number_format($subtotal,2,'.',''),
                "Moneda"            => "MXN",
                "Total"             => number_format($neto,2,'.',''),
                "TipoDeComprobante" => "N",
                "MetodoPago"        => "PUE",
                "LugarExpedicion"   => $cp_emisora
              )
           );    
        }


        
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

        //dd($no_certificado,$subtotal,$descuentos,number_format($neto,2,'.',''),$cp_emisora,$impuestos,$deducciones_adic,$rfc_emi,$emisora);
        //$rfcEmisor = $rfcemi; TODO checar si se usa
        $emisor = $xml->createElement("cfdi:Emisor");
        $emisor = $root->appendChild($emisor);
        $cadena_original .=$this->cargaAtt($emisor, array(
                            "Rfc"=>$rfc_emi,
                            "Nombre"=>$emisora->nombre,
                            "RegimenFiscal"=>"601"
                            )
                        );

        
        $receptor = $xml->createElement("cfdi:Receptor");
        $receptor = $root->appendChild($receptor);
        $cadena_original .=$this->cargaAtt($receptor, array(
                            "Rfc"=>$e->rfc,
                            "Nombre"=>$e->nombre_completo,
                            "UsoCFDI"=>"G03"
                            )
                        );


        $conceptos = $xml->createElement("cfdi:Conceptos");
        $conceptos = $root->appendChild($conceptos);
        
        $concepto = $xml->createElement("cfdi:Concepto");
        $concepto = $conceptos->appendChild($concepto);
        


        if($impuestos > 0 || $deducciones_adic > 0 ){
            $cadena_original .= $this->cargaAtt($concepto, array(
                           "ClaveProdServ"  => "84111505",
                           "Cantidad"       => "1",
                           "ClaveUnidad"    => "ACT",
                           "Descripcion" => "Pago de nómina",
                           "ValorUnitario"  => number_format($pago_aguinaldo,2,'.',''),
                           "Importe"        => number_format($pago_aguinaldo,2,'.',''),
                           "Descuento"      => number_format($descuentos,2,'.','')
                        ));
        }else{
            $cadena_original .= $this->cargaAtt($concepto, array(
                           "ClaveProdServ"  => "84111505",
                           "Cantidad"       => "1",
                           "ClaveUnidad"    => "ACT",
                           "Descripcion" => "Pago de nómina",
                           "ValorUnitario"  => number_format($pago_aguinaldo,2,'.',''),
                           "Importe"        => number_format($pago_aguinaldo,2,'.','')
                ) );
        }

                //dd($impuestos,$deducciones_adic,$pago_aguinaldo);



        // INICIA CODIFICACIÓN DEL COMPLEMENTO DE NÓMINA 1.2 ###########################
        $complemento = $xml->createElement("cfdi:Complemento");
        $complemento = $root->appendChild($complemento);

        $nomina = $xml->createElement("nomina12:Nomina");
        $nomina = $complemento->appendChild($nomina);

        if( $descuentos > 0 ){
            $cadena_original .= $this->cargaAtt($nomina, array(
                            "Version"           => "1.2",
                            "TipoNomina"        => "O",
                            "FechaPago"         => $periodo->fecha_pago,
                            "FechaInicialPago"  => $fecha_inicial_periodoStr,
                            "FechaFinalPago"    => $fecha_final_periodoStr,
                            "NumDiasPagados"    => $dias_a_pagar,
                            "TotalPercepciones" => number_format($pago_aguinaldo,2,'.',''),
                            "TotalDeducciones"  => number_format($descuentos,2,'.',''),
                            "TotalOtrosPagos" => "0.00"
                            )
                        );
        }else{
            $cadena_original .= $this->cargaAtt($nomina, array(
                            "Version"           => "1.2",
                            "TipoNomina"        => "O",
                            "FechaPago"         => $periodo->fecha_pago,
                            "FechaInicialPago"  => $fecha_inicial_periodoStr,
                            "FechaFinalPago"    => $fecha_final_periodoStr,
                            "NumDiasPagados"    => $dias_a_pagar,
                            "TotalPercepciones" => number_format($pago_aguinaldo,2,'.',''),
                            "TotalOtrosPagos" => "0.00"
                        )
                    );
        }

        $queryPatronal="SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = '$id_empleado' 
                        AND   cat.estatus = 1";
        $registroP = DB::connection('empresa')->select($queryPatronal);         
        //$resgistroPatro
        $registro_patronal  = $registroP[0]->num_registro_patronal;

        $NominaEmisor = $xml->createElement("nomina12:Emisor");
        $NominaEmisor = $nomina->appendChild($NominaEmisor);

        $cadena_original .=$this->cargaAtt($NominaEmisor, array(
                "RegistroPatronal"=>$registro_patronal
                )
        );

        if($ip_servicio=='https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl'){
            $EntidadSNCF = $xml->createElement("nomina12:EntidadSNCF");
            $EntidadSNCF = $NominaEmisor->appendChild($EntidadSNCF);
            $cadena_original .=$this->cargaAtt($EntidadSNCF, array(
                     "OrigenRecurso"=>"IP"
                    )
                );
        }
        
        if($num_empleado == NULL || $num_empleado == ""){
            $NumeroEmpleado= $id_empleado;
        }else{
            $NumeroEmpleado = $num_empleado;
        }

        /* traer depto y puesto */
        /* checar la diferencia de categoria y categoriaesp, donde aplica cada uno */
        $queryDep="SELECT nombre
                   FROM departamentos
                   WHERE id = '$e->id_departamento';";
        $registroD = DB::connection('empresa')->select($queryDep);
        $queryPue ="SELECT puesto as nombre
                    FROM puestos
                    WHERE id = '$e->id_puesto';";
        $registroP = DB::connection('empresa')->select($queryPue);

        $NominaReceptor = $xml->createElement("nomina12:Receptor");
        $NominaReceptor = $nomina->appendChild($NominaReceptor);

        $cadena_original .= $this->cargaAtt($NominaReceptor, array(
                        "Curp"                   => $e->curp,
                        "NumSeguridadSocial"     => $e->nss,
                        "FechaInicioRelLaboral"  => $fecha_alta_empleado->format('Y-m-d'),
                        "Antigüedad"             => "$antiguedad",
                        "TipoContrato"           => $tipo_contrato,
                        "Sindicalizado"          => "Sí",
                        "TipoJornada"            => $tipo_jornada,
                        "TipoRegimen"            => "02",
                        "NumEmpleado"            => $NumeroEmpleado,
                        "Departamento"           => $registroD[0]->nombre,
                        "Puesto"                 => isset($registroP[0]->nombre) ? $registroP[0]->nombre : '',
                        // "Puesto"                 => $registroP[0]->nombre,
                        "RiesgoPuesto"           => $tipo_clase,
                        "PeriodicidadPago"       => $tipo_periodo,
                        "SalarioBaseCotApor"     => round($e->salario_diario,2),
                        "SalarioDiarioIntegrado" => round($e->salario_diario_integrado,2),
                        "ClaveEntFed"            => "MIC"
                    )
                );
        
        $percepciones = $xml->createElement("nomina12:Percepciones");
        $percepciones = $nomina->appendChild($percepciones);
        $cadena_original .= $this->cargaAtt($percepciones, array(
                            "TotalSueldos" => number_format($pago_aguinaldo,2,'.',''),
                            "TotalGravado" => number_format($parte_gravada,2,'.',''),
                            "TotalExento"  => number_format($parte_exenta,2,'.','')
                        )
                    );

        // Ciclo "for", recopilación de datos de percepciones. ===============
        for ( $i = 0; $i < count ( $ArrayPercep_TipoPercepcion ); $i++){
            $percepcion = $xml->createElement("nomina12:Percepcion");
            $percepcion = $percepciones->appendChild($percepcion);
            $cadena_original .= $this->cargaAtt($percepcion, array(
                    "TipoPercepcion" => $ArrayPercep_TipoPercepcion[$i],
                    "Clave"          => $ArrayPercep_Clave[$i],
                    "Concepto"       => $ArrayPercep_Concepto[$i],
                    "ImporteGravado" => $ArrayPercep_ImporteGravado[$i],
                    "ImporteExento"  => $ArrayPercep_ImporteExento[$i]
                    )
                );
        }

        if( $deducciones_adic > 0 || $impuestos > 0) {
            if($impuestos > 0 && $deducciones_adic > 0 ){
                if ( count( $ArrayDeduc_TipoDeduccion ) > 0 ){
                    $deducciones = $xml->createElement("nomina12:Deducciones");
                    $deducciones = $nomina->appendChild($deducciones);
                    $cadena_original .= $this->cargaAtt($deducciones, array(
                            "TotalOtrasDeducciones"   => $deducciones_adic,
                            "TotalImpuestosRetenidos" => $impuestos
                        )
                    );
                    for ( $i = 0; $i < count( $ArrayDeduc_TipoDeduccion ); $i++ ){
                        $deduccion = $xml->createElement("nomina12:Deduccion");
                        $deduccion = $deducciones->appendChild($deduccion);
                        $cadena_original .= $this->cargaAtt($deduccion, array(
                                    "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                                    "Clave"         => $ArrayDeduc_Clave[$i],
                                    "Concepto"      => $ArrayDeduc_Concepto[$i],
                                    "Importe"       => $ArrayDeduc_Importe[$i]
                                    )
                                );
                    }
                }
            }else if( $impuestos > 0 ){
                if ( count( $ArrayDeduc_TipoDeduccion ) > 0 ){
                    $deducciones = $xml->createElement("nomina12:Deducciones");
                    $deducciones = $nomina->appendChild($deducciones);
                    $cadena_original .= $this->cargaAtt($deducciones, array(
                            "TotalImpuestosRetenidos" => $impuestos
                        )
                    );
                    // Ciclo "for", recopilación de datos de deducciones. ===============
                    for ( $i = 0; $i < count( $ArrayDeduc_TipoDeduccion ); $i++){
                        $deduccion = $xml->createElement("nomina12:Deduccion");
                        $deduccion = $deducciones->appendChild($deduccion);
                        $cadena_original .= $this->cargaAtt($deduccion, array(
                                "TipoDeduccion" => $ArrayDeduc_TipoDeduccion[$i],
                                "Clave"         => $ArrayDeduc_Clave[$i],
                                "Concepto"      => $ArrayDeduc_Concepto[$i],
                                "Importe"       => $ArrayDeduc_Importe[$i]
                            )
                        );
                    }
                }
            }else if( $deducciones_adic > 0){
                if ( count( $ArrayDeduc_TipoDeduccion ) > 0){
                    $deducciones = $xml->createElement("nomina12:Deducciones");
                    $deducciones = $nomina->appendChild($deducciones);
                    $cadena_original .= $this->cargaAtt($deducciones, array(
                            "TotalOtrasDeducciones" => $deducciones_adic
                        )
                    );
                    // Ciclo "for", recopilación de datos de deducciones. ===============
                    for ( $i = 0; $i < count( $ArrayDeduc_TipoDeduccion ); $i++){
                        $deduccion = $xml->createElement("nomina12:Deduccion");
                        $deduccion = $deducciones->appendChild($deduccion);
                        $cadena_original .= $this->cargaAtt($deduccion, array(
                               "TipoDeduccion"  => $ArrayDeduc_TipoDeduccion[$i],
                               "Clave"          => $ArrayDeduc_Clave[$i],
                               "Concepto"       => $ArrayDeduc_Concepto[$i],
                               "Importe"        => $ArrayDeduc_Importe[$i]
                            )
                        );
                    }   
                }
            }
            

        }
        $totalotraspagos=0;
            $subsidio_causado=0;
            $OtroPago = $xml->createElement("nomina12:OtrosPagos");
            $OtroPago = $nomina->appendChild($OtroPago);
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

        #== 11.6 Termina de conformarse la "Cadena original" con doble ||
        $cadena_original .= "|";
    
        $file = fopen($acceso_repositorio.$SendaPreCFDI."CadenaOriginal_RecNomAgui_".$numerico."_".$NoFac.".txt", "w");
        fwrite($file, $cadena_original . PHP_EOL);
        fclose($file);
        chmod($acceso_repositorio.$SendaPreCFDI."CadenaOriginal_RecNomAgui_".$numerico."_".$NoFac.".txt", 0777);
        
        #=== Muestra la cadena original (opcional a mostrar) =======================
        $data_respuesta['cadena_original'] = $cadena_original;

        #== 11.8 Proceso para obtener el sello digital del archivo .pem.key ========
        $keyid = openssl_get_privatekey(file_get_contents($acceso_repositorio.$SendaPEMS.$file_key));
        openssl_sign($cadena_original, $crypttext, $keyid, OPENSSL_ALGO_SHA256);
        //openssl_free_key($keyid);

        #== 11.9 Se convierte la cadena digital a Base 64 ==========================
        $sello = base64_encode($crypttext);
        $data_respuesta['sello'] = $sello;

        #== 11.10 Proceso para extraer el certificado del sello digital ============
        $file = $acceso_repositorio.$SendaPEMS.$file_cer;      // Ruta al archivo
        $datos = file($file);
        $certificado = "";
        $carga=false;
        for ($i=0; $i<sizeof($datos); $i++){
            if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
            if ($carga) $certificado .= trim($datos[$i]);
            if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
        }
        
        #=== Muestra el certificado del sello digital (opcional a mostrar) =========
        $data_respuesta['certificado'] = $certificado;

        #== 11.12 Se continua con la integración de nodos ===========================
        $root->setAttribute("Sello",$sello);
        $root->setAttribute("Certificado",$certificado);   # Certificado.

        #== Fin de la integración de nodos =========================================

        #=== 11.12 Se guarda el archivo .XML antes de ser timbrado =======================
        $NomArchPreCFDI = $acceso_repositorio.$SendaPreCFDI."PreCFDI-33_RecNomAgui_".$numerico."_".$NoFac.".xml";
 
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
        $NomArchSoap = $acceso_repositorio.$SendaPreCFDI."DatosEnvio_RecNomAgui_".$numerico."_".$NoFac.".xml";
    
        #== 12.6.1 Si el archivo ya se encuentra se elimina ===========================
        if (file_exists ( $NomArchSoap ) == true){
            unlink( $NomArchSoap );
        }

        #== 12.6.2 Se crea el archivo .XML con el SOAP ================================
        $fp = fopen($NomArchSoap,"a");
        fwrite($fp, $cfdixml);
        fclose($fp);
        chmod($NomArchSoap, 0777);
    
        #=== 12.7 Muestra el contenido del SOAP que se envía al servidor del PAC (REQUEST) =========================
        $data_respuesta['soap'] = htmlspecialchars($cfdixml);

        #== 12.8 Se envía el contenido del SOAP al servidor del PAC =====================
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml',' charset=utf-8'));
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
        $VarXML->save($acceso_repositorio.$SendaRESP."RespServ_RecNomAgui_".$numerico."_".$NoFac.".xml");
        chmod($acceso_repositorio.$SendaRESP."RespServ_RecNomAgui_".$numerico."_".$NoFac.".xml", 0777);

        #== 13.3 Se asigna el contenido del tag "xml" a una variable ===============
        $RespServ = $VarXML->getElementsByTagName('xml');

        #== 13.4 Se obtiene el valor del nodo ======================================
        $valor_del_nodo = "";
        foreach( $RespServ as $Nodo ){
            $valor_del_nodo = $Nodo->nodeValue;
        }
        
        #== Si el nodo contiene datos se realizan los siguientes procesos ======
        $exito = false;
        if($valor_del_nodo != ""){
            $exito = true;
            #== 13.5 Se muestra el .XML ya timbrado (CFDI V 3.2), opcional a mostrar =====
            //htmlspecialchars($Nodo->nodeValue);
            $data_respuesta['xml_timbrado'] = htmlspecialchars($Nodo->nodeValue);

            #=== 13.6 Guardando el CFDI en archivo .XML  ============================
            $NomArchXML = "CFDI-33_RecNomAgui_".$numerico."_".$NoFac.".xml";
            $NomArchPDF = "CFDI-33_RecNomAgui_".$numerico."_".$NoFac.".pdf";

            $xmlt = new \DOMDocument();
            $xmlt->loadXML($valor_del_nodo);
            $xmlt->save($acceso_repositorio.$SendaCFDI.$NomArchXML);
            chmod($acceso_repositorio.$SendaCFDI.$NomArchXML, 0777);

            #== 13.7 Procesos para extraer datos del Timbre Fiscal del CFDI =========
            $docXML = new \DOMDocument();
            if (\PHP_VERSION_ID < 80000) { 
                libxml_disable_entity_loader(false);    
            }
            $docXML->load($acceso_repositorio.$SendaCFDI."CFDI-33_RecNomAgui_".$numerico."_".$NoFac.".xml",LIBXML_NOWARNING);

            $params = $docXML->getElementsByTagName("Comprobante");
            foreach ($params as $param) {
                $VersionCFDI = $param->getAttribute("Version");
            }

            $comprobante = $docXML->getElementsByTagName("TimbreFiscalDigital");

            #== 13.8 Se obtienen contenidos de los atributos y se asignan a variables para ser mostrados =======
            //dd($data_respuesta);
            foreach($comprobante as $timFis){
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
            $archivoQR = $acceso_repositorio.$SendaQR.$tim_uuid.".png";
            $urlQR = '../storage/app/public/'.$SendaQR_url.$tim_uuid.".png";
            $CadImpTot = $this->ProcesImpTot($total);
            $cadenaSello= substr($sello_CFD,-8);
            $cadena = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=".$tim_uuid."&re=".$Emisor_RFC."&rr=".$Receptor_RFC."&tt=".$CadImpTot."&fe=".$cadenaSello;
            //QrCode::format('png')->size(219)->errorCorrection('H')->generate($cadena,$archivoQR);
            \QRcode::png($cadena, $archivoQR, 'H', 3, 2);
             
            $data_respuesta['qr'] = $tim_uuid.'.png';
            $data_respuesta['qr_url'] = $urlQR;

            $data_respuesta['archivo_xml'] = $NomArchXML;
            $data_respuesta['archivo_pdf'] = $NomArchPDF;

            $respuestaPaccon=addslashes($respuestaPac);
            $totalreal = round($pago_aguinaldo,2);
            $FechaEmiTimbrado=date("Y-m-d")."T".date("H:i:s");
        
            $timbreAguinaldo = new timbradoAguinaldoEmpleado;
            //$facturaD->id;
            $timbreAguinaldo->id_empleado      = $id_empleado;
            $timbreAguinaldo->id_periodo       = $id_periodo;
            $timbreAguinaldo->ejercicio        = $ejercicio;
            $timbreAguinaldo->fecha_timbrado   = $tim_fecha;
            $timbreAguinaldo->sello_sat        = $sello_SAT;
            $timbreAguinaldo->certificado_sat  = $cert_SAT;
            $timbreAguinaldo->sello_cfdi       = $sello_CFD;
            $timbreAguinaldo->folio_fiscal     = $tim_uuid;
            $timbreAguinaldo->xml_enviado      = $xmlaEnviar;        
            $timbreAguinaldo->num_factura      = $NoFac;
            $timbreAguinaldo->respuesta_pac    = $respuestaPaccon;
            $timbreAguinaldo->cadena_original  = $cadena_original;
            $timbreAguinaldo->file_pdf         = $NomArchPDF;
            $timbreAguinaldo->file_xml         = $NomArchXML;
            $timbreAguinaldo->importe          = $totalreal;
            $timbreAguinaldo->receptor         = $rfc_empleado;
            $timbreAguinaldo->emisor           = $rfc_emisor;
            $timbreAguinaldo->certificado_tim  = $no_certificado;
            $timbreAguinaldo->fecha_emision    = $FechaEmiTimbrado;
            $timbreAguinaldo->num_dias_pagados  = $dias_a_pagar;
            $timbreAguinaldo->estatus_timbre   = 1;
        
            $timbreAguinaldo->save();
        
            $data_respuesta['tipo_c'] = 0;
            $data_respuesta['id_repo'] = Session::get('empresa')['id'];
            $data_respuesta['id_usuario'] = $id_empleado;
            $data_respuesta['id_periodo'] = $id_periodo;
            $data_respuesta['ejericio'] = $ejercicio;
            $data_respuesta['c_cadena'] = $cadena_departamentos;
            $data_respuesta['fecha_tim'] = $FechaEmiTimbrado;
            $data_respuesta['error'] = false ;

            $data_respuesta['id_timbre'] = 0;
        }else{
            $exito = false;
            #== 13.11 En caso de error de timbrado se muestran los detalles al usuario.            
            $valorNod = "";
            $valorNod2 = "";
            
            $codigoError = $VarXML->getElementsByTagName('CodigoError');
            foreach($codigoError as $NodoStatus){
                $valorNod = $NodoStatus->nodeValue;
            }

            $codigoMsg = $VarXML->getElementsByTagName('MensajeIncidencia');
            foreach($codigoMsg as $NodoStatus){
                $valorNod2 = $NodoStatus->nodeValue;
            }
            
            $data_respuesta['error']         = true ;
            $data_respuesta['xml_enviar']    = $xmlaEnviar;
            $data_respuesta['respuesta']     = $respuestaPac; 
            $data_respuesta['codigo_error']  = $valorNod;
            $data_respuesta['MENSAJE_error'] = $valorNod2;
            $data_respuesta['fecha_tim'] = '';
            
            $xmlaEnviar=addslashes($xmlaEnviar);

            $respuestaPaccon=addslashes($respuestaPac);

            $timbreAguinaldo = new timbradoAguinaldoEmpleado;
            $timbreAguinaldo->id_empleado      = $id_empleado;
            $timbreAguinaldo->id_periodo       = $id_periodo;
            $timbreAguinaldo->fecha_timbrado   = '';
            $timbreAguinaldo->sello_sat        = 'error';
            $timbreAguinaldo->certificado_sat  = 'error';
            $timbreAguinaldo->sello_cfdi       = 'error';
            $timbreAguinaldo->folio_fiscal     = 'error';
            $timbreAguinaldo->xml_enviado      = $xmlaEnviar;        
            $timbreAguinaldo->num_factura       = $NoFac;
            $timbreAguinaldo->respuesta_pac    = $respuestaPaccon;
            $timbreAguinaldo->cadena_original  = $cadena_original;
            $timbreAguinaldo->estatus_timbre   = 2;
            
            $timbreAguinaldo->save();
        }
        ##### FIN DE PROCEDIMIENTOS ####################################################    
        //dump($data_respuesta);
    
        if($tipoR == 1){
            return view('procesos.timbrado-aguinaldo.resultado-timbrado-aguinaldo',compact('data_respuesta'));
        }else{
            $rr['qr_url']       = $data_respuesta['qr_url'];
            $rr['qr']           = $data_respuesta['qr'];
            $rr['archivo_pdf']  = $data_respuesta['archivo_pdf'];
            $rr['archivo_xml']  = $data_respuesta['archivo_xml'];
            $rr['num_factura']   = $data_respuesta['num_factura'];
            $rr['archivo_xml']  = $data_respuesta['archivo_xml'];
            if($data_respuesta['error'] == true){
                $rr['error']         = $data_respuesta['error'];
                $rr['respuesta']     = $data_respuesta['respuesta']; 
                $rr['codigo_error']  = $data_respuesta['codigo_error'];
                $rr['MENSAJE_error'] = $data_respuesta['MENSAJE_error'];
            }
            return response()->json(['exito' => $exito, 'data' => $rr]);
        } 
    }

    public function validarEmpleado($id_empleado, $cadena)
    {
        $num_errores = 0;
        $num_errores_conceptos = 0;
        $cadena_departamentos = base64_decode($cadena);
        $base = Session::get('base');
        cambiarBase($base);
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
                        ON em.id_categoria = cat.id 
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
        $errores = array('empleados' => $num_errores);
      
        return view('procesos.timbrado-aguinaldo.validar-timbre-empleados', compact('periodo', 'empleado', 'cadena_departamentos', 'errores'));
    }

    public function generaPdf($id_empleado,$id_repo,$xml)
    {        
        $base = Session::get('base');
        $pdf = str_replace('.xml', '.pdf', $xml);    
        $url = storage_path()."/app/public/repositorio/". $id_repo . "/" .  $id_empleado . "/timbrado/";
        $arch_xml = $url . 'archs_cfdi/'.$xml;
        $arch_pdf = $url . 'archs_pdf/'.$pdf;
        $isr = 2; 

        //verificamos si el archivo existe y lo retornamos
        if (File::exists($arch_xml)){
            if (File::exists($arch_pdf))
            { 
                return response()->download($arch_pdf);
            }else{
                $url =  '../resources/views/reportes-fpdf/pdf_reciboNomina.php?NomArchXML='.$xml.'&NomArchPDF='.$pdf.'&id='.$id_empleado.'&base='.$base.'&base_id='.$id_repo.'&isr='.$isr;
                return redirect()->to($url);                
            }
        }else{
            return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$arch_xml.'</h3></div>';
        }
    }

    public function downloadSoapXml($id,$repo,$archivo)
    {
        // return $archivo;
        //verificamos si el archivo existe y lo retornamos
        if (Storage::disk('public')->exists("repositorio/".$repo."/".$id."/timbrado/archs_cfdi/".$archivo)){        
            return Storage::disk('public')->download("repositorio/".$repo."/".$id."/timbrado/archs_cfdi/".$archivo);
        }     
        return '<div style="justify-content: center; display: flex; color:red;"><h3><strong>No se encontró el archivo!</strong></h3></div>';
    }

    public function cancelarCfdi($id, $deptos){
        /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
        $data_string ="";
        $data_respuesta = array();

       $base = Session::get('base');
       cambiarBase($base);

       $timbre = TimbradoAguinaldoEmpleado::find($id);
       $no_fac = $timbre->num_factura;

       $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->get()[0];
       $usr_timbre = $emisora->user_timbre;

       $periodo = periodosNomina::where('activo', 1)->first();   

       $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
       $data_respuesta['no_factura']   = $timbre->num_factura;
       $data_respuesta['repo']   = Session::get('empresa')['id'].'/'.$timbre->id_empleado;
       $data_respuesta['repositorio']=Session::get('empresa')['id'];
       $data_respuesta['id_empleado']=$timbre->id_empleado;
       $data_respuesta['deptos']=base64_decode($deptos);
       $data_respuesta['id_periodo']=$periodo->id;

       $url_repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/'; 
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
       $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
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
       $cer_path = $acceso_repositorio.$SendaPEMS . $file_cer;
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
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:can="http://facturacion.finkok.com/cancel" xmlns:apps="apps.services.soap.core.views" xmlns:can1="http://facturacion.finkok.com/cancellation">
   <soapenv:Header/>
   <soapenv:Body>
      <can:cancel>
        <can:UUIDS>            
            <apps:UUID UUID="$FolioFiscal" FolioSustitucion="" Motivo="02"/>
        </can:UUIDS>
         <can:username>$username</can:username>
         <can:password>$password</can:password>
         <can:taxpayer_id>$taxpayer_id</can:taxpayer_id>
         <can:cer>$cer_content</can:cer>
         <can:key>$key_content</can:key>
         <can:store_pending>0</can:store_pending>
      </can:cancel>
   </soapenv:Body>
</soapenv:Envelope>
XML;
       #== 12.6 Proceso para guardar los datos que se envían al servidor en un archivo .XML ========================
       $NomArchSoap = $acceso_repositorio.$SendaPreCFDI."soap_cancel_Factura_".$no_fac.".xml";

       #== 12.6.1 Si el archivo ya se encuentra se elimina ===========================
       if (file_exists ($NomArchSoap)==true){
            unlink($NomArchSoap);
       }

       #== 12.6.2 Se crea el archivo .XML con el SOAP ================================
       $fp = fopen($NomArchSoap,"a");
       fwrite($fp, $cadena);
       fclose($fp);
       chmod($NomArchSoap, 0777);
       
       
       #=== 12.7 Muestra el contenido del SOAP que se envía al servidor del PAC (REQUEST) =========================
       $data_respuesta['soap'] = htmlspecialchars($cadena);
       $data_respuesta['contenido'] = htmlspecialchars($cadena);
       //$ip_servicio = 'https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl';

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
       
       $err = 0;
       if (!$RespServ)
       {
           $data_respuesta['error'] = TRUE;
           $data_respuesta['codigo_error'] .= "Error: ".$RespServ;
           $data_respuesta['error_msg'] = curl_error($process);
       }
       else
       {
           #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
           $domResp = new \DOMDocument();
           $domResp->loadXML($RespServ);

           // Se guarda la respuesta del servidor
           $domResp->save($acceso_repositorio.$SendaRESP."RespServ_Cancel_".$no_fac.".xml");
           chmod($acceso_repositorio.$SendaRESP."RespServ_Cancel_".$no_fac.".xml", 0777);

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

           //dd($CodResp);
           
           if($CodResp !=""){
               $data_respuesta['contenido'] = htmlspecialchars($cadena);
               $data_respuesta['error'] = true;                
               $data_respuesta['codigo_error'] = "205: UUID No existente";
               $data_respuesta['error_msg'] = $valorCodigo;

           }           
           if($CodResp == 201 || $CodResp == "201" )
           {
               # 201: UUID Cancelado Exitoso
               #=== Guardando el SOAP =====================================================
               $NomArchSoap = $acceso_repositorio.$SendaPreCFDI."soap_cancel_2_Factura_".$no_fac.".xml";
               $NomArchSoap2 = "soap_cancel_2_Factura_".$no_fac.".xml";
               $data_respuesta['file_xml'] = $NomArchSoap2;
               
               if (file_exists ($NomArchSoap)==true){
                   unlink($NomArchSoap);
               }
               $fp = fopen($NomArchSoap,"a");
               fwrite($fp, $cadena);
               fclose($fp);
               chmod($NomArchSoap, 0777);
               
               ###### GUARDANDO EL .XML DEL ACUSE #############################
               $fileAcuse ="Fact_CFDI_".$no_fac."_AcuseCancel.xml";
               $xmlt = new \DOMDocument();
               $xmlt->loadXML($contenido_del_nodo_acuse);
               $xmlt->save( $acceso_repositorio.$SendaCFDI.$fileAcuse);
               chmod($acceso_repositorio.$SendaCFDI."Fact_CFDI_".$no_fac."_AcuseCancel.xml", 0777);
               unset($xmlt);

               ### SE ASIGNA EL CONTENIDO DEL ACUSE A UNA VARIABLE DE TIPO DOM PARA LA LECTURA DE DATOS ####################
               $DOM = new \DOMDocument('1.0', 'utf-8');
               $DOM->preserveWhiteSpace = FALSE;
               $DOM->loadXML($contenido_del_nodo_acuse);
           
               ### LECTURA DE ATRIBUTOS ###################################

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

               $base = Session::get('base');
               cambiarBaseA($base);
               
               // GUARDAR EN DB

               $facturaD = new TimbradoCancelacionesAguinaldo;
        
               $facturaD->id_empleado       = $timbre->id_empleado;
               $facturaD->id_periodo        = $timbre->id_periodo;
               $facturaD->fecha_cancelacion = $FecCanc;
               $facturaD->request_cancel    = $request;
               $facturaD->response          = $response;
               $facturaD->xml_acuse_cancel  = $xmlacuse;
               $facturaD->sello_sat         = $SelloCanc;
               $facturaD->file_acuse        = $fileAcuse;
               $facturaD->file_soap         = $NomArchSoap2;
               $facturaD->no_factura        = $no_fac;  
               $facturaD->ejercicio         = $timbre->ejercicio; 
               $facturaD->save();
               
               
               $timbre->estatus_timbre = 2;
               $timbre->update();

               $data_respuesta['error'] = false;                
               $data_respuesta['mnsg'] = addslashes($respString);
               /* CHECAR BITACORAS
                $desc='Se solicito la cancelacion del timbre de nomina del empleado '.$NameEmple.'.';
                       $tipo='CT';

                       add_bitacora(28,$desc,$tipo,'',$db);
                       correo_eventos($db,28,$referencia);
               */
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
       
       curl_close($process);

       ## FIN DEL PROCESO DE TIMBRADO #################################################
       return view('procesos.timbrado-aguinaldo.timbre-cancelado',compact('data_respuesta'));
   }

   public function verificarEstatus($id)
    {
        $data_string ="";
        $data_respuesta = array();
         
        $base = Session::get('base');
        cambiarBase($base);

        $timbre = TimbradoAguinaldoEmpleado::find($id);
        //dd($timbre);  
        $no_fac = $timbre->num_factura;        

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
        // $repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/';
        // $repositorio2 = 'public/repositorio/' . $data_respuesta['repo'] . '/timbrado/';
        // $recursos = resource_path().'/timbrado/';
        // $folder_repositorio = public_path() . '/' . $repositorio;

        $url_repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/';
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
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
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
        $cer_path = $acceso_repositorio.$SendaPEMS . $file_cer;
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

            // Se guarda la respuesta del servidor
            $domResp->save($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml");
            chmod($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml", 0777);

        }
        //dd($data_respuesta);      
        curl_close($process);

        return view('procesos.timbrado-aguinaldo.check-estatus',compact('data_respuesta'));
    }

    public function zipCFDIS($ejercicio){
        $base = Session::get('base');
        $id_empresa = Session::get('empresa')['id'];     
              
        $acceso_repositorio = storage_path().'/app/public/';
        $folder_repositorio = 'repositorio/' .$id_empresa . '/timbrado/masivo_xml'; 
        Storage::disk('public')->makeDirectory($folder_repositorio, $mode = 0777, true, true);
       
        /* CREAR URL ZIP */        
        $validar_zip_file = $folder_repositorio.'/xml_aguinaldo_'.$ejercicio.'.zip';
        if (Storage::disk('public')->exists($validar_zip_file)){  
            return Storage::disk('public')->download($validar_zip_file);
        }

        cambiarBase($base);
        $timbres = DB::connection('empresa')
                    ->table('timbrado_aguinaldo')
                    ->where('ejercicio', $ejercicio)
                    ->get();
        
        // Initializing PHP class
        $zip_file = $acceso_repositorio.$folder_repositorio.'/xml_aguinaldo_'.$ejercicio.'.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);              
     
        foreach($timbres as $timbre){
            $url_xml = $acceso_repositorio.'repositorio/'.$id_empresa.'/'.$timbre->id_empleado .'/timbrado/archs_cfdi/'. $timbre->file_xml; 
            if (File::exists($url_xml)){                
                $zip->addFile($url_xml, $timbre->file_xml);               
            }
        }        
        $zip->close();
        return response()->download($zip_file);        
    }

    public function zipPDF($ejercicio){        
        $this->crea_pdf_periodo($ejercicio);

        $base = Session::get('base');
        $id_empresa = Session::get('empresa')['id'];        

        $acceso_repositorio = storage_path().'/app/public/';
        $folder_repositorio = 'repositorio/' .$id_empresa . '/timbrado/masivo_pdf'; 
        Storage::disk('public')->makeDirectory($folder_repositorio, $mode = 0777, true, true); 

        $validar_zip_file = $folder_repositorio.'/pdf_aguinaldo_'.$ejercicio.'.zip';
        if (Storage::disk('public')->exists($validar_zip_file)){        
            return Storage::disk('public')->download($validar_zip_file);
        }
        cambiarBase($base);

        $timbres = DB::connection('empresa')
                    ->table('timbrado_aguinaldo')
                    ->where('ejercicio', $ejercicio)
                    ->get();
        
        // Initializing PHP class
        $zip_file = $acceso_repositorio.$folder_repositorio.'/pdf_aguinaldo_'.$ejercicio.'.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        foreach($timbres as $timbre){ 
            $url_pdf = $acceso_repositorio.'repositorio/'.$id_empresa.'/'.$timbre->id_empleado .'/timbrado/archs_pdf/'. $timbre->file_pdf; 
            if (File::exists($url_pdf)){
                $zip->addFile($url_pdf, $timbre->file_pdf);
            }
        }        
        $zip->close();
        return response()->download($zip_file);
    }

    public function crea_pdf_periodo($ejercicio){
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $base = Session::get('base');
        $id_empresa = Session::get('empresa')['id'];
        $acceso_repositorio = storage_path()."/app/public/repositorio/".$id_empresa."/";
        cambiarBase($base); 

        $timbres = DB::connection('empresa')
                    ->table('timbrado_aguinaldo')
                    ->where('ejercicio', $ejercicio)
                    ->get();
                  
        foreach($timbres as $timbre){            
            $url_xml = $acceso_repositorio . "/" . $timbre->id_empleado .'/timbrado/archs_cfdi/'. $timbre->file_xml;
            if (File::exists($url_xml)){
                $pdf = str_replace('.xml', '.pdf', $timbre->file_xml);
                $url = $acceso_repositorio . $timbre->id_empleado .'/timbrado/archs_pdf/'. $pdf ;
                //dd($url);
                $isr = 2; //de q no hay isr, cmabiar en algún momento
                //verificamos si el archivo existe y lo retornamos
                if (!File::exists($url)){
                  $url2 =  \file_get_contents (asset('../resources/views/reportes-fpdf/pdf_reciboNominaAguinaldo.php?NomArchXML='.$timbre->file_xml.'&NomArchPDF='.$pdf.'&id='.$timbre->id_empleado.'&base='.$base.'&base_id='.$id_empresa.'&isr='.$isr));
                //   echo $url2; exit;
                    //echo "creado<br>$pdf<br>";
                }else{
                    //echo "existe<br>$url<br>";
                }            
            }
        }
        //echo "<br>fin";
    }

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
                if (!isset($quitar[$key])){
                    if (
                        substr($key, 0, 3) != "xml" &&
                        substr($key, 0, 4) != "xsi:"
                    ){
                        $cadena_original .= $val . "|";
                    }                        
                }                    
            }
        }
        return $cadena_original;
    }

    # 14.2 Función que integra los nodos al archivo .XML sin integrar a la "Cadena original".
    private function cargaAttSinIntACad(&$nodo, $attr){
        global $xml;
        $quitar = array('sello'=>1,'noCertificado'=>1,'certificado'=>1);
        foreach ($attr as $key => $val){
            $val = preg_replace('/\s\s+/', ' ', $val);
            $val = trim($val);
            if (strlen($val)>0){
                 $val = str_replace("|","/",$val);
                 $nodo->setAttribute($key,$val);
                 if (!isset($quitar[$key])){
                    if (substr($key,0,3) != "xml" &&
                    substr($key,0,4) != "xsi:"){};
                 }
                   
            }
         }
     }

     # 14.3 Funciónes que da formato al "Importe total" como lo requiere el SAT para ser integrado al código QR.
    private function ProcesImpTot($ImpTot){
        $ImpTot = number_format($ImpTot, 4); // <== Se agregó el 30 de abril de 2017.
        $ArrayImpTot = explode(".", $ImpTot);
        $NumEnt = $ArrayImpTot[0];
        $NumDec = $this->ProcesDecFac($ArrayImpTot[1]);

        return $NumEnt.".".$NumDec;
    }

    private function ProcesDecFac($Num){
        $FolDec = "";
        if ($Num < 10){$FolDec = "00000".$Num;}
        if ($Num > 9 and $Num < 100){$FolDec = $Num."0000";}
        if ($Num > 99 and $Num < 1000){$FolDec = $Num."000";}
        if ($Num > 999 and $Num < 10000){$FolDec = $Num."00";}
        if ($Num > 9999 and $Num < 100000){$FolDec = $Num."0";}
        return $FolDec;
    }

}
