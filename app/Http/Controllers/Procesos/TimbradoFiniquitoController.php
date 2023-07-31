<?php

namespace App\Http\Controllers\Procesos;

use App\Http\Controllers\Contabilidad\TimbradoController;
use App\Http\Controllers\Controller;
use App\Models\ConceptosNomina;
use App\Models\Empleado;
use App\Models\TimbradoCancelacionesFiniquito;
use App\Models\TimbradoCredenciales;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Elements\Nomina12\Nomina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Rutina;
use App\Models\periodosNomina;
use App\Models\TimbradoEmpleadoFiniquito;
use App\Models\EmpresaEmisora;
use Illuminate\Support\Facades\File;
use Storage;

//include(public_path() . "/lib/qrlib/qrlib.php");

class TimbradoFiniquitoController extends Controller
{
    private $save;

    public function inicio(Request $request)
    {
        $base = Session::get('base');
        cambiarBase($base);    
        $anio = 0;        
        $datos_ejercicio = DB::connection('empresa')->table('ejercicios')->select('ejercicio')->orderBy('ejercicio', 'desc')->get();
        if($request->ejercicio > 0){            
            $anio = $request->ejercicio;
        }else if(count($datos_ejercicio) > 0){ 
            $anio = max($datos_ejercicio->toArray())->ejercicio;        
        }       
        $datos_timbrado_finiquito = null;
        if($anio > 0){
            $datos_timbrado_finiquito = $this->consultaFiniquito($anio);
        }else{
            $datos_timbrado_finiquito = [];
        }
        $id_repo = Session::get('empresa')['id'];

        return view('procesos.timbrado-finiquito.inicio', compact('datos_ejercicio', 'anio','id_repo', 'datos_timbrado_finiquito'));
    }

    public function consultaFiniquito($noEjercicio){        
        if ($noEjercicio == 0) {
            exit;
        }      
        $empresa = Session::get('base');

        $query = "SELECT distinct(em.id)as id_empleado, concat(em.apaterno,' ',em.amaterno,' ',em.nombre) as nombre, em.numero_empleado, em.fecha_baja, 
            format(ru.neto_fiscal, 2) neto_fiscal, em. estatus, ru.fnq_valor, ru.id_periodo,            
            ifnull((SELECT sello_sat FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') sello_sat,
            (SELECT no_factura FROM " . $empresa . ".timbrado_finiquito t where t.id_empleado=em.id and t.id_periodo=ru.id_periodo
            order by id desc limit 1) NoFactura,
            (SELECT estatus_timbre FROM " . $empresa . ".timbrado_finiquito t where t.id_empleado=em.id and t.id_periodo=ru.id_periodo
            order by id desc limit 1) estatus_timbre,
            (SELECT count(*) FROM " . $empresa . ".timbrado_finiquito t where t.id_empleado=em.id and t.id_periodo=ru.id_periodo and t.estatus_timbre=1) count_estatus_timbre,
            (SELECT count(*) FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo and sello_sat<>'error') count_retimbrar,
            (SELECT count(*) FROM " . $empresa . ".timbrado_cancelaciones_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo) count_canc_tim_finiquito,
            ifnull((SELECT file_xml FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') file_xml,
            ifnull((SELECT file_pdf FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') file_pdf,
            ifnull((SELECT folio_fiscal FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') folio_fiscal 
            from " . $empresa . ".empleados em join " . $empresa . ".rutinas" . $noEjercicio . " ru on em.id=ru.id_empleado inner join " . $empresa . ".periodos_nomina pe on ru.id_periodo=pe.id 
            where (em.estatus=20 || em.estatus=2) 
            and ru.fnq_valor=1 
            and em.finiquitado=1 
            and ru.neto_fiscal<>'' 
            and pe.fecha_inicial_periodo<=em.fecha_baja 
            and pe.fecha_final_periodo>=em.fecha_baja 
            and pe.estatus<>0 ;";
        $datos_timbrado_finiquito = DB::connection('empresa')->select($query);
        return $datos_timbrado_finiquito;
    }

    public function validacionesTimbradoFiniquito($id_empleado, $factura, $anio_ejercicio)
    {    
        cambiarBase(Session::get('base'));
        $id_emp = base64_decode($id_empleado);
        $fact = base64_decode($factura);
        $desc_empresa = Session::get('empresa')['razon_social'];
        $empresa = Session::get('base');

        $query = "SELECT r.num_registro_patronal, e.id, e.rfc, e.curp, e.nss, concat(e.nombre,' ',e.apaterno,' ',e.amaterno) as nombre_completo,
            c.nombre, c.tipo_clase, e.tipo_jornada, e.tipo_contrato 
            from " . $empresa . ".categorias c join " . $empresa . ".empleados e on e.id_categoria=c.id 
            inner join singh.registro_patronal r on c.tipo_clase=r.id where e.id=" . $id_emp . " and c.estatus=1;";

        $datos_empleado = DB::connection('empresa')->select($query);

        $empleados = array();
        $num_errores = 0;
        $num_errores_conceptos = 0;

        foreach ($datos_empleado as $e) {
            $errores = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
            $empleado = array(
                'id'     => $e->id,
                'nombre' => $e->nombre_completo,
                'rfc'    => $e->rfc,
                'curp'   => $e->curp,
                'nss'    => $e->nss,
                'registro_patronal' => $e->num_registro_patronal,
                'tipo_contrato' => $e->tipo_contrato,
                'tipo_jornada' => $e->tipo_jornada,
            );

            //validar RFC
            if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
                $errores['rfc'] = true;
                $num_errores++;
            }
            //validar NSS
            if (!preg_match("/^[0-9]+$/", $e->nss)) {
                $errores['nss'] = true;
                $num_errores++;
            }
            //validar CURP
            if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
                $errores['curp'] = true;
                $num_errores++;
            }

            //validar registro patronal
            if (strlen(trim($e->num_registro_patronal)) < 1) {
                $errores['registro_patronal'] = true;
                $num_errores++;
            }

            $empleado['errores'] = $errores;
            $empleados[] = $empleado;
        }

        $rutina = Rutina::where('id_empleado', $id_emp)
            ->where('fnq_valor', 1)
            ->where('estatus',1)
            ->with('valores_conceptos')
            ->first();
        
            // return $id_emp;       
        $ids = $rutina->valores_conceptos->pluck('id_concepto')->toArray();        

        $concep = DB::connection('empresa')->table('conceptos_nomina')->whereIn('id', $ids)->get();        

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
        return view('procesos.timbrado-finiquito.validaciones-timbrado-finiquito', compact('desc_empresa', 'datos_empleado', 'conceptos', 'errores', 'empleados', 'anio_ejercicio', 'rutina'));
    }

    function timbrarFiniquitoEmpleado($id_empleado, $anio, $rutina, $modalidad)
    {        

        $numerico = rand(0, 99999);
        $id_empleado = intval(base64_decode($id_empleado));
        cambiarBase(Session::get('base'));

        /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
        $data_respuesta = array();

        //nueva tabla rutina
        $rutina = Rutina::where('id', $rutina)
        ->with(['valores_conceptos','periodoNomina'])->first();

        $id_periodo   = $rutina->periodoNomina->id;
        $tipo_nomina  = $rutina->periodoNomina->nombre_periodo;
        $fecha_pago   = $rutina->periodoNomina->fecha_pago;
        $fecha_inicial_periodo = new \DateTime($rutina->periodoNomina->fecha_inicial_periodo);
        $fecha_final_periodo   = new \DateTime($rutina->periodoNomina->fecha_final_periodo);
        $fecha_inicial_periodo2 = $rutina->periodoNomina->fecha_inicial_periodo;
        $fecha_final_periodo2   = $rutina->periodoNomina->fecha_final_periodo;

        /* Convertimos el tipo de periodo a procesar **/
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
        $emisora=Empleado::select('empleados.id_categoria','empleados.tipo_jornada','empleados.tipo_contrato','categorias.nombre',
            'singh.registro_patronal.num_registro_patronal','singh.registro_patronal.tipo_clase',
            'singh.empresas_emisoras.razon_social','singh.empresas_emisoras.user_timbre','singh.empresas_emisoras.cp')
            ->join('categorias', 'categorias.id', 'empleados.id_categoria')
            ->join('singh.registro_patronal', 'singh.registro_patronal.id', 'categorias.tipo_clase')
            ->join('singh.empresas_emisoras', 'singh.empresas_emisoras.id', 'singh.registro_patronal.id_empresa_emisora')
            ->where('categorias.estatus',1)
            ->where('singh.empresas_emisoras.estatus',1)
            ->where('singh.registro_patronal.estatus',1)
            ->where('empleados.id',$id_empleado)->first();

        $usr_timbre    = $emisora->user_timbre;
        $cp_emisora    = $emisora->cp;
        $tipo_jornada  = str_pad($emisora->tipo_jornada, 2, '0', STR_PAD_LEFT);
        $tipo_contrato = str_pad($emisora->tipo_contrato, 2, '0', STR_PAD_LEFT);
        $num_registro_patronal = $emisora->num_registro_patronal;
        $tipo_clase    = null;

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
        $total_percep     = round($rutina->total_percepcion_fiscal, 2);
        $total_deduc      = $rutina->total_deduccion_fiscal;
        $neto_fiscal      = $rutina->neto_fiscal;

        /* DATOS DEL EMPLEADO */
        $empleado=Empleado::select('empleados.*',
            DB::raw('CONCAT(departamentos.nombre) as departamento'),DB::raw('CONCAT(puestos.puesto) as puesto'))
            ->join('departamentos', 'departamentos.id', 'empleados.id_departamento')
            ->join('puestos', 'puestos.id', 'empleados.id_puesto')
            ->where('empleados.id',$id_empleado)->first();

        /* FECHAS EMPLEADO */
        $fecha_alta_empleado     = new \DateTime($empleado->fecha_alta);

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

        /* --------------------------- R U T I N A S -------------------------------------------------- */

        /* DEDUCCIONES */
        $incapacidades_valor = ($rutina->incapacidades == "" || $rutina->incapacidades == null) ? 0 : $rutina->incapacidades;

        /* FALTAS */
        $faltas_valor = 0;

        ### PERCEPCIONES Y DEDUCCIONES ###############################################################
        $importe_valor_isr = 0;
        $array_datos_deducciones = array();

        $ids = $rutina->valores_conceptos->pluck('id_concepto')->toArray();
        $concep = ConceptosNomina::whereIn('id', $ids)->get();

        $total_percepciones_gravado = floatval($rutina->total_gravado);
        $total_percepciones_exento = 0;

        foreach ($concep as $datos) {
            $campo_total = "total" . $datos->id;

            // OBTIENE INFORMACION DE ISR
            if ($datos->tipo == 1 && $datos->nombre_concepto == 'ISR') {
                $array_datos_deducciones[] = $datos;
                $importe_valor_isr = round($rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->total, 2);
            }
            $total_percepciones_exento = $total_percepciones_exento + round($rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->exento, 2);
        }

        /* DIAS A PAGAR */
        $dias_a_pagar = ceil(($dias_pagados + 1) - $incapacidades_valor - $faltas_valor);

        # 1.1 Configuración de zona horaria
        date_default_timezone_set('America/Mexico_City');
        $data_respuesta['timezone'] = date_default_timezone_get();

        ### 2. ASIGNACIÓN DE VALORES A VARIABLES ###################################################
        $data_respuesta['id_repo'] = Session::get('empresa')['id'];
        $data_respuesta['id_usuario'] = $id_empleado;
        $data_respuesta['id_periodo'] = $id_periodo;

        $url_repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/' . $empleado->id . '/timbrado/';
        $url_timbrado = 'timbrado/';

        $SendaCFDI     = $url_repositorio . 'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaPDF      = $url_repositorio . 'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).

        $SendaPEMS    = $url_timbrado . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $url_timbrado . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $url_timbrado . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);

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

        /* credenciales timbrado */
        $credenciales = DB::connection('empresa')->table("singh.timbrado_credenciales")->where('id', '=', $condicion)->get();
        $credenciales = $credenciales[0];

        $rfc_emisor   = $credenciales->rfc;
        $regimen_fiscal_emisor   = $credenciales->regimen_fiscal;
        $username     = base64_decode($credenciales->user);
        $password     = base64_decode($credenciales->password);
        $ip_servicio  = $credenciales->servicio;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $file_cer       = $credenciales->nombre_archivo . ".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales->nombre_archivo . ".key.pem";   // 3.3 Nombre del archivo .cer.key

        ### 4. DATOS GENERALES DE LA FACTURA ##################################################
        $fact_serie        = "A";                             // 4.1 Número de serie.
        $fact_folio        = mt_rand(1000, 9999);             // 4.2 Número de folio (para efectos de demostración se asigna de manera aleatoria).
        $NoFac             = $fact_serie . $fact_folio;         // 4.3 Serie de la factura concatenado
        ### PERCEPCIONES ###############################################################

        if ($importe_valor_isr < 0) {
            $importe_otros_pagos = $importe_valor_isr * -1;
            $total_real_reduccion = $importe_otros_pagos - ($total_deduc * -1);
        } else {
            $importe_otros_pagos = 0;
            $total_real_reduccion = $total_deduc;
        }

        ### 11. CREACIÓN Y ALMACENAMIENTO DEL ARCHIVO .XML (CFDI) ANTES DE SER TIMBRADO ###################
        $toal_de_duccion = round($rutina->total_deduccion_fiscal, 2);

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

        #== 11.3 Rutina de integración de nodos =========================================
        $certificado = new Certificado(Storage::disk('public')->get($SendaPEMS . $file_cer));

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
            "LugarExpedicion" => $cp_emisora
        ];

        $creator = new CfdiCreator40($comprobanteAtributos, $certificado);
        $comprobante = $creator->comprobante();

        #== 11.2 EMISOR =====
        $comprobante->addEmisor([
            "Rfc" => $credenciales->rfc,
            "Nombre" => $credenciales->razon_social_ss,
            "RegimenFiscal" => $regimen_fiscal_emisor,
        ]);


        #== 11.2 Receptor =====
        $comprobante->addReceptor([
            "Rfc" => $empleado->rfc,
            "Nombre" => $empleado->nombre.' '.$empleado->apaterno.' '.$empleado->amaterno,
            "UsoCFDI" => "CN01",
            "DomicilioFiscalReceptor" => $empleado->cp,
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

        // INICIA CODIFICACIÓN DEL COMPLEMENTO DE NÓMINA 1.2 ###########################

        $nominaA = new Nomina();

        if ($importe_valor_isr > 0) {

            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else if ($toal_de_duccion > 0 ) {

            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else if ($totalotraspagos == 0 && $totalrealdeduccion == 0) {

            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
            ]);

        }else if($totalotraspagos == 0){
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
            ]);

        }else if($totalotraspagos > 0 && $totalrealdeduccion == 0){
            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        } else {

            $nominaA->addAttributes([
                "Version" => "1.2",
                "TipoNomina" => "O",
                "FechaPago" => $fecha_pago,
                "FechaInicialPago" => $fecha_inicial_periodo2,
                "FechaFinalPago" => $fecha_final_periodo2,
                "NumDiasPagados" => $dias_a_pagar,
                "TotalPercepciones" => number_format($total_percep, 2, '.', ''),
                "TotalDeducciones" => number_format($totalrealdeduccion, 2, '.', ''),
                "TotalOtrosPagos" => number_format($totalotraspagos, 2, '.', '')
            ]);

        }

        $emisor = $nominaA->getEmisor();
        $emisor['RegistroPatronal'] = $num_registro_patronal;

        $nominaA->addReceptor([
            "Curp" =>$empleado->curp,
            "NumSeguridadSocial" => $empleado->nss,
            "FechaInicioRelLaboral" => $fecha_alta_empleado->format('Y-m-d'),
            "Antigüedad" => "$antiguedad",
            "TipoContrato" => $tipo_contrato,
            "Sindicalizado" => "Sí",
            "TipoJornada" => $tipo_jornada,
            "TipoRegimen" => "02",
            "NumEmpleado" => $empleado->numero_empleado==null || $empleado->numero_empleado=="" ? $empleado->id:$empleado->numero_empleado,
            "Departamento" => $empleado->departamento,
            "Puesto" => $empleado->puesto,
            "RiesgoPuesto" => $tipo_clase,
            "PeriodicidadPago" => $tipo_periodo,
            "SalarioBaseCotApor" => round($empleado->salario_diario, 2), //
            "SalarioDiarioIntegrado" => round($empleado->salario_diario_integrado, 2),
            "ClaveEntFed" => "MIC"
        ]);

        //TotalPercepFiscal
        $percepcionesA=$nominaA->addPercepciones([
            "TotalSueldos" => number_format($total_percep, 2, '.', ''),
            "TotalGravado" => number_format($total_percepciones_gravado, 2, '.', ''),
            "TotalExento" => number_format($total_percepciones_exento, 2, '.', '')
        ]);

        foreach ($concep as $datos) {

            $cod_sat = (substr($datos->codigo_sat, 0, strlen($datos->codigo_sat) - 1));
            $id_concepto = $datos->id;
            $nom_concepto = $datos->nombre_concepto;

            if (strlen($id_concepto) < 3) {
                $id_concepto = str_pad($id_concepto, 3, "PP", STR_PAD_LEFT);
            } else {
                $id_concepto = 'PP' . $id_concepto;
            }
            // OBTIENE INFORMACION DE PERCEPCIONES
            if ($datos->tipo == 0) {

                if ($rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->total > 0) {
                    $t_gravado = $rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->gravado;
                    $t_exento = $rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->exento;

                    $percepcionesA->addPercepcion([
                        "TipoPercepcion" => $cod_sat,
                        "Clave" => $id_concepto,
                        "Concepto" => $nom_concepto,
                        "ImporteGravado" => number_format($t_gravado, 2, '.', ''),
                        "ImporteExento" => number_format($t_exento, 2, '.', '')
                    ]);

                }
            }
        }

        $total_otras_deducciones = $total_deduc - $importe_valor_isr;

        if ($importe_valor_isr > 0) {
            if (count($array_datos_deducciones) > 0) {
                $deduccionesA = $nominaA->addDeducciones([
                    "TotalOtrasDeducciones" => number_format($total_otras_deducciones, 2, '.', ''),
                    "TotalImpuestosRetenidos" => number_format($importe_valor_isr, 2, '.', '')
                ]);

            }
        } else {
            if (count($array_datos_deducciones) > 0) {
                $deduccionesA = $nominaA->addDeducciones([
                    "TotalOtrasDeducciones" => number_format($total_real_reduccion, 2, '.', ''),
                ]);

            }
        }

        foreach ($concep as $datos) {

            $cod_sat = (substr($datos->codigo_sat, 0, strlen($datos->codigo_sat) - 1));
            $id_concepto = $datos->id;
            $nom_concepto = $datos->nombre_concepto;

            if (strlen($id_concepto) < 3) {
                $id_concepto = str_pad($id_concepto, 3, "PP", STR_PAD_LEFT);
            } else {
                $id_concepto = 'PP' . $id_concepto;
            }
            // OBTIENE INFORMACION DE DEDUCCIONES
            if ($datos->tipo == 1) {

                if ($rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->total > 0) {
                    $total_deduccion = $rutina->valores_conceptos->where('id_concepto', $datos->id)->first()->total;

                    $deduccionesA->addDeduccion([
                        "TipoDeduccion" => $cod_sat,
                        "Clave" => $id_concepto,
                        "Concepto" => $nom_concepto,
                        "Importe" => number_format(round($total_deduccion, 2), 2, '.', '')
                    ]);

                }
            }
        }

        $otrosPagosA= $nominaA->addOtrosPagos();
        $otrosPagosA->addOtrosPago([
            "TipoOtroPago" => '002',
            "Clave" => 'D002',
            "Concepto" => 'SUBSIDIO PARA EL EMPLEO',
            "Importe" => number_format($importe_otros_pagos, 2, '.', '') // aqui va el isr convertido en negativo
        ])->addSubsidioAlEmpleo([
            "SubsidioCausado" => (!empty($importe_valor_isr) && $importe_valor_isr > 0)? 0:$rutina->subsidio_al_empleo // aquí va el subsidio causado
        ]);

        $comprobante->addComplemento($nominaA);
        $creator->addSumasConceptos(null, 2);

        $key = Storage::disk('public')->get($SendaPEMS . $file_key);

        $creator->addSello($key, $password);
        $creator->moveSatDefinitionsToComprobante();

        $document_save=storage_path('app/public/'.$SendaCFDI."CFDI-33_RecNom_" . $numerico . "_" . $NoFac  . ".xml");

        // método de ayuda para generar el xml y guardar los contenidos en un archivo
        $creator->saveXml($document_save);

        // método de ayuda para generar el xml y retornarlo como un string
        $xml_enviado =  $creator->asXml();

        $params = array(
            "xml" => $xml_enviado,
            "username" => $username,
            "password" => $password,
        );

        $client = new \SoapClient($ip_servicio, array('trace' => 1));
        $response = $client->__soapCall("stamp", array($params));

        $certificado = $certificado->getSerial();
        $sello_cfd = $creator->comprobante()->attributes()->get('Sello');
        $cadena_origen = $creator->buildCadenaDeOrigen();

        if ($response->stampResult->xml) {
            $exito = true;
            $NomArchXML = "CFDI-40_RecNom_" . $numerico . "_" . $NoFac . ".xml";
            $NomArchPDF = "CFDI-40_RecNom_" . $numerico . "_" . $NoFac . ".pdf";

            $xmlt = new \DOMDocument();
            $xmlt->loadXML($response->stampResult->xml);
            $xmlt->save(storage_path('app/public/'.$SendaCFDI . $NomArchXML));
            chmod(storage_path('app/public/'.$SendaCFDI . $NomArchXML), 0777);


            #== 13.8.1 Se muestra el número de factura asignado por el sistema local (no asingado por el SAT).
            $data_respuesta['no-fac'] = $NoFac;


            $facturaD = new TimbradoEmpleadoFiniquito;
            $facturaD->id_empleado      = $id_empleado;
            $facturaD->id_periodo       = $id_periodo;
            $facturaD->ejercicio        = $rutina->ejercicio;
            $facturaD->fecha_timbrado   = $response->stampResult->Fecha;
            $facturaD->sello_sat        = $response->stampResult->SatSeal;
            $facturaD->certificado_sat  = $response->stampResult->NoCertificadoSAT;
            $facturaD->sello_cfdi       = $sello_cfd;
            $facturaD->folio_fiscal     = $response->stampResult->UUID;
            $facturaD->xml_enviado      = $xml_enviado;
            $facturaD->no_factura       = $NoFac;
            $facturaD->respuesta_pac    = json_encode($response);
            $facturaD->cadena_original  = $cadena_origen;
            $facturaD->file_pdf         = $NomArchPDF;
            $facturaD->file_xml         = $NomArchXML;
            $facturaD->importe          = round($neto_fiscal, 2);
            $facturaD->receptor         = $empleado->rfc;
            $facturaD->emisor           = $rfc_emisor;
            $facturaD->certificado_tim  = $certificado;
            $facturaD->fecha_emision    = $response->stampResult->Fecha;
            $facturaD->num_dias_pagados = $dias_a_pagar;
            $facturaD->estatus_timbre   = 1;
            $facturaD->save();

            //Solo modificamos el estatus de la rutina en caso de que vuelvan a timbrar despues de un cancelado
            if($rutina->estatus!=1){
                $rutina->estatus=1;
                $rutina->update();
            }

            $data_respuesta['no_factura'] = $NoFac;
            $data_respuesta['archivo_xml'] = $NomArchXML;
            $data_respuesta['archivo_pdf'] = $NomArchPDF;
            $data_respuesta['version_cfdi']= '4.0';
            $data_respuesta['sello_sat']      = $response->stampResult->SatSeal;
            $data_respuesta['cert_sat']       = $response->stampResult->SatSeal;
            $data_respuesta['sello_cfd']      = $sello_cfd;
            $data_respuesta['fecha_tim']      = $response->stampResult->Fecha;
            $data_respuesta['timbre_uuid']    = $response->stampResult->UUID;

            $data_respuesta['c_cadena'] = $cadena_origen;
            $data_respuesta['id_timbrado'] = $facturaD->id;
            $data_respuesta['id_empleado'] = $id_empleado;

            $qr = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id=" . $response->stampResult->UUID . "&re=" . $rfc_emisor . "&rr=" . $empleado->rfc . "&tt=" . number_format($neto_fiscal, 2, '.', '') . "&fe=" . substr($sello_cfd, -8);
            $data_respuesta['qr_url']=$qr;

        } else {
            $exito = false;
            #== 13.11 En caso de error de timbrado se muestran los detalles al usuario.
            $data_respuesta['error']         = true;
            $data_respuesta['xml_enviar']    = $xml_enviado;
            $data_respuesta['respuesta']     = json_encode($response);
            $data_respuesta['codigo_error']  = $response->stampResult->Incidencias->Incidencia->CodigoError;
            $data_respuesta['MENSAJE_error'] = $response->stampResult->Incidencias->Incidencia->MensajeIncidencia;
            
        }
        ##### FIN DE PROCEDIMIENTOS #################################################### 
        if ($modalidad == 1) {
            return view('procesos.timbrado-finiquito.empleado-resultado', compact('data_respuesta', 'anio'));
        } else {
            if ($data_respuesta['error'] == true) {
                $rr['error']         = $data_respuesta['error'];
                $rr['respuesta']     = $data_respuesta['respuesta'];
                $rr['codigo_error']  = $data_respuesta['codigo_error'];
                $rr['MENSAJE_error'] = $data_respuesta['MENSAJE_error'];
                $rr['archivo_xml'] = $data_respuesta['xml_enviar'];
            }
            return response()->json(['exito' => $exito, 'data' => $rr]);
        }
    }

    public function generaPdfFiniquito($id_empleado,$id_repo,$xml,$id_timbre)
    {        
        $base = Session::get('base');
		cambiarBase($base);
		$timbre = TimbradoEmpleadoFiniquito::find($id_timbre);
        $pdf = str_replace('.xml', '.pdf', $xml);        
        $url = storage_path()."/app/public/repositorio/". $id_repo . "/" .  $id_empleado . "/timbrado/";
        $arch_xml = $url . 'archs_cfdi/'.$xml;
        $arch_pdf = $url . 'archs_pdf/'.$pdf;
        $isr = 2; //de q no hay isr, cmabiar en algún momento
        //verificamos si el archivo existe y lo retornamos
		
		$xml_raw = trim(htmlspecialchars_decode(html_entity_decode($timbre->respuesta_pac))," \t\n\r\"");
			
		if(File::exists($arch_xml)){
			$file = fopen($arch_xml, "w");
			fwrite($file, $xml_raw);
			fclose($file);
			chmod($arch_xml, 0777);
		
            if (File::exists($arch_pdf)){ 
                return response()->download($url);
            }else{
                $url =  '../resources/views/reportes-fpdf/pdf_reciboNomina.php?NomArchXML='.$xml.'&NomArchPDF='.$pdf.'&id='.$id_empleado.'&base='.$base.'&base_id='.$id_repo.'&isr='.$isr;
                return redirect()->to($url);        
            }
        }else{
            return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$arch_xml.'</h3></div>';
        }        
    }

    public function imprimirReciboFiniquito($idempleado,$file_xml)
    {
        $path = storage_path()."/app/public/repositorio/". Session::get('empresa')['id'] . "/" .  $idempleado . "/timbrado/";
        $path_arch_xml = $path . 'archs_cfdi/'.$file_xml;
        $path_arch_pdf = $path . 'archs_pdf/'.str_replace('.xml', '.pdf', $file_xml);

        if(File::exists($path_arch_pdf)){
            return response()->download($path_arch_pdf);
        }else{
            $pdf = TimbradoController::genera_pdf_finiquito($path_arch_xml, $path_arch_pdf);
            return $pdf->download();
        }
    }

    public function descargarXml($id_empleado, $file_xml, Request $request)
    {
        $id_empresa = Session::get('empresa')['id'];
        $id_empleado = base64_decode($id_empleado);
        $file_xml = base64_decode($file_xml);     

        if (Storage::disk('public')->exists("repositorio/".$id_empresa."/".$id_empleado."/timbrado/archs_cfdi/".$file_xml)){        
            return Storage::disk('public')->download("repositorio/".$id_empresa."/".$id_empleado."/timbrado/archs_cfdi/".$file_xml);
        } 
        return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$file_xml.'</h3></div>';
    }

    public function cancelarCfdi($id_empleado, $factura, $anio_ejercicio, Request $request)
    {
        $data_respuesta = array();

        $base = Session::get('base');
        cambiarBase($base);
        $id_empleado = base64_decode($id_empleado);
        $factura = base64_decode($factura);

        $findtimbre = DB::connection('empresa')->table($base . ".timbrado_finiquito")
            ->select('id', 'id_periodo', 'emisor', 'folio_fiscal')
            ->where('id_empleado', '=', $id_empleado)
            ->where('no_factura', '=', $factura)
            ->where('sello_sat', '<>', 'error')
            ->first();

        $timbre = TimbradoEmpleadoFiniquito::find($findtimbre->id);

        $rutina = Rutina::where('id_empleado', $id_empleado)
        ->where('estatus',1)
        ->first();

        $no_fac = $timbre->no_factura;        

        $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->get()[0];
        $usr_timbre = $emisora->user_timbre;

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr ( $usr_timbre , 7 ,strlen($usr_timbre) );
        $condicion = ($condicion == null)?1:$condicion;
        
        /* credenciales timbrado */
        $credenciales= TimbradoCredenciales::find($condicion);
        $taxpayer = $credenciales->rfc;

        $username    = base64_decode($credenciales->user);
        $password    = base64_decode($credenciales->password);
        $ip_servicio = $credenciales->servicio_cancelacion;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $file_cer       = $credenciales->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales->nombre_archivo.".key.pem";   // 3.3 Nombre del archivo .cer.key

        $cer_content = Storage::disk('public')->get('timbrado/archs_pem/'. $file_cer);
        $key_content = Storage::disk('public')->get('timbrado/archs_pem/' . $file_key);

        $client = new \SoapClient($ip_servicio, array('trace' => 1));

        $uuids = array("UUID" => $timbre->folio_fiscal, "Motivo" => "02", "FolioSustitucion" => "");
        $uuid_ar = array('UUID' => $uuids);
        $params = array("UUIDS" => $uuid_ar,
            "username" => $username,
            "password" => $password,
            "taxpayer_id" => $taxpayer,
            "cer" => $cer_content,
            "key" => $key_content);

        $response =$client->__soapCall("cancel", array($params));

        $finiquito_cancelado = new TimbradoCancelacionesFiniquito();
        $finiquito_cancelado->id_empleado       = $timbre->id_empleado;
        $finiquito_cancelado->id_periodo        = $timbre->id_periodo;
        $finiquito_cancelado->fecha_cancelacion = isset($response->cancelResult->Fecha) ?  $response->cancelResult->Fecha:'';
        $finiquito_cancelado->request_cancel    = '';
        $finiquito_cancelado->response          = json_encode($response);
        $finiquito_cancelado->xml_acuse_cancel  = '';
        $finiquito_cancelado->sello_sat         = '';
        $finiquito_cancelado->file_acuse        = '';
        $finiquito_cancelado->file_soap         = '';
        $finiquito_cancelado->no_factura        = $no_fac;
        $finiquito_cancelado->ejercicio         = $timbre->ejercicio;

        $data_respuesta['respuesta'] = json_encode($response);
        $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
        $data_respuesta['no_factura']   = $timbre->no_factura;
        $data_respuesta['repositorio']=Session::get('empresa')['id'];
        $data_respuesta['id_empleado']=$timbre->id_empleado;
        $data_respuesta['anio_ejercicio']=$anio_ejercicio;
        $data_respuesta['error']=true;
        $data_respuesta['contenido']=json_encode($response);


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

                $finiquito_cancelado->save();
                $timbre->estatus_timbre = 5;
                $timbre->update();

                $rutina->estatus=2;
                $rutina->update();

                $data_respuesta['error'] = false;
                $data_respuesta['mnsg'] = $mensaje;

                break;
            case "203":
                $mensaje = "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                break;
            case "205":
                $mensaje = "205: UUID No existente";
                break;
            case "201":
                $mensaje = "Factura cancelada exitosamente";

                $finiquito_cancelado->save();
                $timbre->estatus_timbre = 2;
                $timbre->update();

                $rutina->estatus=2;
                $rutina->update();

                $data_respuesta['error'] = false;
                $data_respuesta['mnsg'] =$mensaje;

                break;
            default:
                $mensaje = "Error desconocido";
                break;
        }

        $data_respuesta['codigo_error']=$mensaje;

        return view('procesos.timbrado-finiquito.timbre-cancelado',compact('data_respuesta'));
    }

    public function downloadSoapXml($id,$repo,$archivo)
    {
        $url_archivo = "repositorio/".$repo."/".$id."/timbrado/archs_precdfi/".$archivo;
        if (Storage::disk('public')->exists($url_archivo)){        
            return Storage::disk('public')->download($url_archivo);
        } 
        return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$url_archivo.'</h3></div>';
    }

    public function descargarComprobanteFiniquito($id_empleado, $file_xml, $anio_ejercicio, Request $request)
    {
        $id_empresa = Session::get('empresa')['id'];
        $id_empleado = base64_decode($id_empleado);
        $file_xml = base64_decode($file_xml); 
        
        $url_archivo = "repositorio/".$id_empresa."/".$id_empleado."/timbrado/archs_precdfi/".$file_xml;

        if (Storage::disk('public')->exists($url_archivo)){        
            return Storage::disk('public')->download($url_archivo);
        } 
        return '<div style="justify-content: center; display: flex;"><h3><strong style="color:red;">No se encontró el archivo: </strong> '.$url_archivo.'</h3></div>';
    }

    public function verificarEstatusFiniquito($id_empleado, $folio_fiscal, $anio_ejercicio, Request $request)
    {
        $id_empresa = Session::get('empresa')['id'];
        $empresa = Session::get('base');
        $id_empleado = base64_decode($id_empleado);
        $folio_fiscal = base64_decode($folio_fiscal);

        $data_string ="";
        $data_respuesta = array();
         
        $base = Session::get('base');
        cambiarBase($base);

        $findtimbre = DB::connection('empresa')->table($base . ".timbrado_finiquito")
            ->select('id', 'id_periodo', 'emisor', 'folio_fiscal')
            ->where('id_empleado', '=', $id_empleado)
            ->where('folio_fiscal', '=', $folio_fiscal)
            ->where('sello_sat', '<>', 'error')
            ->first();

        $id = $findtimbre->id;

        $timbre = TimbradoEmpleadoFiniquito::find($id);

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
        if (!$RespServ){
            $data_respuesta['error'] = TRUE;
            $data_respuesta['codigo_error'] .= "<h1>Error: ".$RespServ."</h1><br>";
            $data_respuesta['error_msg'] = curl_error($process);
        }else{
            $data_respuesta['error'] = FALSE;
            $data_respuesta['respuesta_string'] = $respString;
            
            #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
            $domResp = new \DOMDocument();
            $domResp->loadXML($RespServ);

            // Se guarda la respuesta del servidor
            $domResp->save($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml");
            chmod($acceso_repositorio.$SendaRESP."Status_Server".$no_fac.".xml", 0777);
        }
        
        curl_close($process);

        return view('procesos.timbrado-finiquito.check-estatus', compact('data_respuesta', 'anio_ejercicio'));
    }

    public function verCfdiCancelados($id_empleado,$factura,$id_periodo,$anio_ejercicio, Request $request)
    {
        $id_empleado = base64_decode($id_empleado);
        cambiarBase(Session::get('base'));

        $datos_timbrado_finiquito=Empleado::select('timbrado_finiquito.id as id_timbrado',
            'timbrado_finiquito.id_empleado',
            'timbrado_finiquito.importe as neto_fiscal',
            'empleados.fecha_baja',
            'timbrado_finiquito.no_factura','timbrado_finiquito.file_xml','empleados.nombre','empleados.id')
            ->leftJoin( 'timbrado_finiquito', 'timbrado_finiquito.id_empleado', '=','empleados.id')
            ->leftJoin('timbrado_cancelaciones_finiquito', 'timbrado_cancelaciones_finiquito.no_factura','timbrado_finiquito.no_factura')
            ->whereIn('timbrado_finiquito.estatus_timbre',[2,5])
            ->where('timbrado_finiquito.id_periodo',$id_periodo)
            ->where('empleados.id',$id_empleado)->get();

        return view('procesos.timbrado-finiquito.ver-cfdi-cancelados', compact('datos_timbrado_finiquito', 'anio_ejercicio'));
    }

    public function validacionMasiva($anio_ejercicio, Request $request)
    {
        $empresa = Session::get('base');
        $num_errores = 0;
        $num_errores_conceptos = 0;
        cambiarBase($empresa);

        $query_empleados = "SELECT distinct(em.id)as id_empleado, concat(em.apaterno,' ',em.amaterno,' ',em.nombre) as nombre, em.numero_empleado, em.fecha_baja, format(ru.neto_fiscal, 2) neto_fiscal, em. estatus, ru.fnq_valor, ru.id_periodo, pe.nombre_periodo, em.curp, em.nss, em.rfc, rp.num_registro_patronal, em.tipo_contrato, em.tipo_jornada,
            ifnull((SELECT t.no_factura FROM " . $empresa . ".timbrado_finiquito t where t.id_empleado=em.id and t.id_periodo=ru.id_periodo and t.estatus_timbre=1 limit 1), '') NoFactura, 
            ifnull((SELECT r.id FROM " . $empresa . ".rutinas r where r.id_empleado=em.id and r.fnq_valor=1 and r.estatus=1 limit 1), 0) id_rutina,
            ifnull((SELECT t.estatus_timbre FROM " . $empresa . ".timbrado_finiquito t where t.id_empleado=em.id and t.id_periodo=ru.id_periodo and t.estatus_timbre=1 limit 1), 0) estatus_timbre,           
            ifnull((SELECT file_xml FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') file_xml,
            ifnull((SELECT folio_fiscal FROM " . $empresa . ".timbrado_finiquito where id_empleado=em.id and id_periodo=ru.id_periodo order by id desc limit 1), '') folio_fiscal 
            from " . $empresa . ".empleados em join " . $empresa . ".rutinas" . $anio_ejercicio . " ru on em.id=ru.id_empleado inner join " . $empresa . ".periodos_nomina pe on ru.id_periodo=pe.id 
            join " . $empresa . ".categorias c on c.id=em.id_categoria inner join singh.registro_patronal rp on rp.id=c.tipo_clase
            where (em.estatus=20 || em.estatus=2) and ru.fnq_valor=1 and em.finiquitado=1 and ru.neto_fiscal<>'' 
            and pe.fecha_inicial_periodo<=em.fecha_baja and pe.fecha_final_periodo>=em.fecha_baja and pe.estatus<>0 and pe.nombre_periodo=em.tipo_de_nomina and c.estatus=1;";
        // echo $query_empleados,'<br>'; exit;
        $datos_empleados = DB::connection('empresa')->select($query_empleados);

        $empleados_error = array(0);

        foreach ($datos_empleados as $e) {
            $errores = array('rfc' => false, 'nss' => false, 'curp' => false, 'registro_patronal' => false);
            $empleado = array(
                'id'     => $e->id_empleado,
                'nombre' => $e->nombre,
                'id_periodo' => $e->id_periodo,
                'ejercicio' => $anio_ejercicio,
                'nombre_periodo' => $e->nombre_periodo,
                'rfc'    => $e->rfc,
                'curp'   => $e->curp,
                'nss'    => $e->nss,
                'registro_patronal' => $e->num_registro_patronal,
                'tipo_contrato' => $e->tipo_contrato,
                'tipo_jornada' => $e->tipo_jornada,
                'estatus_timbre' => $e->estatus_timbre,
                'id_rutina' => $e->id_rutina,
            );

            //validar RFC
            if (!preg_match("/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/", $e->rfc)) {
                $errores['rfc'] = true;
                $num_errores++;
                if (!in_array(($e->id_empleado), $empleados_error)) {
                    $empleados_error[] = $e->id_empleado;
                }
            }
            //validar NSS
            if (!preg_match("/^[0-9]+$/", $e->nss)) {
                $errores['nss'] = true;
                $num_errores++;
                if (!in_array(($e->id_empleado), $empleados_error)) {
                    $empleados_error[] = $e->id_empleado;
                }
            }
            //validar CURP
            if (!preg_match("/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/", $e->curp)) {
                $errores['curp'] = true;
                $num_errores++;
                if (!in_array(($e->id_empleado), $empleados_error)) {
                    $empleados_error[] = $e->id_empleado;
                }
            }

            //validar registro patronal
            if (strlen(trim($e->num_registro_patronal)) < 1) {
                $errores['registro_patronal'] = true;
                $num_errores++;
                if (!in_array(($e->id_empleado), $empleados_error)) {
                    $empleados_error[] = $e->id_empleado;
                }
            }

            $empleado['errores'] = $errores;
            $empleados[] = $empleado;
        }

        // print_r($empleados_error); exit;

        $datos_conceptos_nomina = DB::connection('empresa')->table($empresa . ".conceptos_nomina")
            ->select('nombre_concepto', 'id', 'codigo_sat')
            ->where('finiquito', '=', 1)
            ->where('tipo', '<>', 3)
            ->where('estatus', '<>', 0)
            ->where('file_rool', '<>', 0)
            ->where('file_rool', '<', 251)
            ->get();

        $conceptos = array();
        foreach ($datos_conceptos_nomina as $c) {
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
        // $num_errores_conceptos++;

        $errores = array('empleados' => $num_errores, 'conceptos' => $num_errores_conceptos);
        return view('procesos.timbrado-finiquito.validacion-masiva', compact('anio_ejercicio', 'empleados', 'conceptos', 'errores', 'empleados_error'));
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
                    if (substr($key, 0, 3) != "xml" && substr($key, 0, 4) != "xsi:"){
                        $cadena_original .= $val . "|";
                    }
                }                   
                        
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
                if (!isset($quitar[$key])){
                    if (
                        substr($key, 0, 3) != "xml" &&
                        substr($key, 0, 4) != "xsi:"
                    );
                }                    
            }
        }
    }

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
