<?php

namespace App\Http\Controllers\sistema;

use App\Models\ConceptosNomina;
use PDOException;
use App\Models\Empresa;
use App\Models\Contrato;
use Illuminate\Http\Request;
use App\Models\EmpresaEmisora;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;


class EmpresaReceptoraController extends Controller
{
    const PREFIJO_EMPRESA = 'empresa';
    const FOLDER_REPOSITORIO = 'repositorio';
    protected $errores = [];

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

	public function empresaReceptora()
	{
        $empresas = Empresa::where('estatus', 1)->orderBy('razon_social', 'asc')->get();

        foreach ($empresas as $empresa) {
            if ($empresa->sede == 1) {
            	$enterprise = "develico_".$empresa->base;
                try{
                    Config::set('database.connections.enterprise.database', $enterprise);
                    $sedes = DB::connection('empresa')
                        ->table('sedes')
                        ->get();

                    if (count($sedes) > 0) {
                        $empresa->sedes = $sedes;
                    }
                } catch (PDOException $e){}
            }
        }

        return view('empresa-receptora.empresa-receptora', compact('empresas'));
	}

    public function crearempresaReceptora()
    {
        return view('empresa-receptora.crear-empresa-receptora');
    }

    public function agregarempresaReceptora(Request $request)
    {
        $request->validate([
            'razon_social' => 'required',
            'representante_legal' => 'required',
            'rfc' => 'required',
            'telefono' => 'required',
            'email' => 'required',
            'calle_num' => 'required',
            'colonia' => 'required',
            'delegacion_municipio' => 'required',
            'codigo_postal' => 'required',
            'calculo_imss' => 'required'
        ]);

        if($request->activa_restricciones == "on"){ $activa_restricciones = 1; }else{ $activa_restricciones = 0; }

        if($request->lista_empleados == "on"){ $lista_empleados = 1; }else{ $lista_empleados = 0; }

        $data = [
            'razon_social' => strtoupper($request->get('razon_social', '')),
            'rfc' => strtoupper($request->get('rfc', '')),
            'ins' => $request->get('ins', ''),
            'identificacion_oficial' => $request->get('identificacion_oficial', ''),
            'num_notaria' => $request->get('num_notaria', ''),
            'representante_legal' => strtoupper($request->get('representante_legal', '')),
            'nombre_notario' => strtoupper($request->get('nombre_notario', '')),
            'lugar_notaria' => strtoupper($request->get('lugar_notaria', '')),
            'otorgamiento_rdp' => strtoupper($request->get('otorgamiento_rdp', '')),
            'giro' => strtoupper($request->get('giro', '')),
            'tasa_vigente' => $request->get('tasa_vigente', ''),
            'telefono' => $request->get('telefono', ''),
            'email' => $request->get('email', ''),
            'contacto_directo' => strtoupper($request->get('contacto_directo', '')),
            'porcentaje_fondo' => $request->get('porcentaje_fondo', ''),
            'calle_num' => strtoupper($request->get('calle_num', '')),
            'colonia' => strtoupper($request->get('colonia', '')),
            'delegacion_municipio' => strtoupper($request->get('delegacion_municipio', '')),
            'estado' => $request->get('estado', ''),
            'codigo_postal' => $request->get('codigo_postal', ''),
            'calles_referencia' => strtoupper($request->get('calles_referencia', '')),
            'activa_restricciones' => $activa_restricciones,
            'calculo_imss' => $request->get('calculo_imss', 'UMA'),
            'dias_imss' => $request->get('dias_imss', 0),
            'lista_empleados' => $lista_empleados,
            'sede' => ($request->get('sede')) ?$request->get('sede', 0):0,
            'sss' => $request->get('sss', 0),
            'timbrado' => $request->get('timbrado', 0),
            'norma' => $request->get('norma', 0),
            'estatus' => 1,
            'permiso_extranjero' => $request->get('permiso_extranjero', 0),
            'fecha_edicion' => date('Y-m-d H:i:s'),
            'regimen' => $request->get('regimen', 601),
        ];

        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['base'] = '';
        $data['repositorio'] = '';
        $empresa = $this->validarEmpresa($request->razon_social, $request->rfc);

        if ($empresa != "true") {
            session()->flash('danger', 'Los datos proporcionados (razon social o RFC) ya se encuentran registrados');

            return redirect()->route('empresar.crear');
        }


        if(Empresa::updateOrCreate( ['id' => $request->id], $data )){
            $accion = ($request->id != '') ? 'UPDATE' : 'CREATE';

            logGeneral(Auth::user()->email, 'Catalogo de Empresas: '.$request->razon_social.', '.$accion, '', '');
        }

        $id_empresa = Empresa::where('rfc', $request->rfc)->first();

        $nombreBase = self::PREFIJO_EMPRESA . str_pad($id_empresa->id, 6, "0", STR_PAD_LEFT);

        Empresa::where('id', $id_empresa->id)
            ->update([
                'base' => $nombreBase,
                'repositorio' => $id_empresa->id
            ]);

        $dir = public_path().'/storage/'.SELF::FOLDER_REPOSITORIO.'/'.$id_empresa->id;

        File::makeDirectory($dir,$mode = 0777,true,true);

        $f1 = $dir . '/timbrado';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $f2 = $f1 . '/archs_cfdi';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $f6 = $f1 . '/archs_pdf';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $f8 = $f1 . '/masivo_pdf';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $f9 = $f1 . '/masivo_xml';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).

        File::makeDirectory($f1,$mode = 0777,true,true);
        File::makeDirectory($f2,$mode = 0777,true,true);
        File::makeDirectory($f6,$mode = 0777,true,true);
        File::makeDirectory($f8,$mode = 0777,true,true);
        File::makeDirectory($f9,$mode = 0777,true,true);

        $this->crearEstructuraEmpresaNueva($nombreBase);

        if (count($this->errores) > 0) {
            $msg = 'Se creó la empresa, pero hubo errores en la creación. <br>' . implode('<br>&bull; ', $this->errores);
        } else {
            $msg = 'La empresa se creó correctamente.';
        }

        session()->flash('success', $msg);

        return redirect()->route('empresar.empresareceptora');
    }

    public function validarEmpresa($razon_social, $rfc)
    {
        $empresa = Empresa::where('razon_social', $razon_social)
            ->orWhere('rfc', $rfc)
            ->first();

        return ($empresa == null) ? true : $empresa->razon_social;
    }

    protected function crearEstructuraEmpresaNueva($base)
    {
        $conexion = 'empresa';

        $stm = 'DROP DATABASE IF EXISTS '.$base.' ;';
        DB::statement($stm);
        $stm = 'CREATE DATABASE IF NOT EXISTS '.$base.' /*!40100 DEFAULT CHARACTER SET utf8 */;';
        DB::statement($stm);

        cambiarBase($base);

        foreach (Empresa::TABLAS_EMPRESA as $tabla) {
            switch ($tabla) {
                case "actividades":
                    try {
                        $tabla_desa = 'actividades';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `descripcion` varchar(255) NOT NULL,
                            `fecha_inicio` datetime NOT NULL,
                            `fecha_fin` datetime NOT NULL,
                            `notificacion` int(11) DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `apertura_formulario` int(11) DEFAULT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_actividades_periodos_norma1_idx` (`apertura_formulario`),
                            KEY `fk_actividades_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            CONSTRAINT `fk_actividades_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_actividades_periodos_norma1` FOREIGN KEY (`apertura_formulario`) REFERENCES `periodos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "aguinaldo":
                    try {
                        $tabla_desa = 'aguinaldo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `ejercicio` varchar(20) NOT NULL,
                            `gravado` varchar(20) NOT NULL,
                            `pago_aguinaldo` varchar(20) NOT NULL,
                            `impuestos` varchar(20) NOT NULL,
                            `neto` varchar(20) NOT NULL,
                            `dias_aguinaldo` varchar(50) NOT NULL,
                            `dias_aguinaldo2` varchar(50) NOT NULL,
                            `importe2` varchar(50) NOT NULL,
                            `neto2` varchar(50) NOT NULL,
                            `fecha_alta` date NOT NULL,
                            `impuesto_anual` varchar(40) NOT NULL,
                            `fecha_fiscal` date NOT NULL,
                            `dias_fiscales` varchar(20) NOT NULL,
                            `pension_alimenticia` varchar(20) NOT NULL DEFAULT '0',
                            `descuentos_otros` varchar(20) NOT NULL DEFAULT '0',
                            `s_pension_alimenticia` varchar(20) NOT NULL DEFAULT '0',
                            `s_descuentos_otros` varchar(20) NOT NULL DEFAULT '0',
                            `total_fiscal` varchar(20) NOT NULL DEFAULT '0',
                            `total_sindical` varchar(20) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `aguinaldo_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "asignacion_biometricos":
                    try {
                        $tabla_desa = 'asignacion_biometricos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_biometrico` int(11) unsigned NOT NULL,
                            `id_empleado` int(11) NOT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_baja` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_Asignacion_empleado_biometico_biometricos1_idx` (`id_biometrico`),
                            KEY `fk_Asignacion_empleado_biometico_empleado1_idx` (`id_empleado`),
                            CONSTRAINT `fk_Asignacion_empleado_biometico_biometricos1` FOREIGN KEY (`id_biometrico`) REFERENCES `biometricos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `fk_Asignacion_empleado_biometico_empleado1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "asignacion_contratos":
                    try {
                        $tabla_desa = 'asignacion_contratos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_contrato` int(11) NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `idcontrato` (`id_contrato`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "asistencias":
                    try {
                        $tabla_desa = 'asistencias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id_empleado` int(11) NOT NULL,
                            `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            `lugar` tinytext,
                            PRIMARY KEY (`id_empleado`,`fecha`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "asistencia_horario":
                    try {
                        $tabla_desa = 'asistencia_horario';

                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id_empleado` varchar(20) NOT NULL,
                            `dia` date NOT NULL,
                            `entrada` datetime DEFAULT NULL,
                            `entrada_horario` datetime NOT NULL,
                            `salida` datetime DEFAULT NULL,
                            `salida_horario` datetime NOT NULL,
                            `comida` varchar(1) DEFAULT '0',
                            `inicio_comida` datetime DEFAULT NULL,
                            `inicio_comida_horario` datetime NOT NULL,
                            `fin_comida` datetime DEFAULT NULL,
                            `fin_comida_horario` datetime NOT NULL,
                            `retardo` varchar(1) NOT NULL,
                            `salida_anticipada` varchar(1) DEFAULT '0',
                            `asistencia` varchar(1) NOT NULL,
                            `permiso` varchar(1) NOT NULL DEFAULT '0',
                            `motivo` text,
                            `autorizo` varchar(100) DEFAULT NULL,
                            `elimino` varchar(100) DEFAULT NULL,
                            `lugar` tinytext,
                            `coordenadas_1` varchar(20) DEFAULT NULL,
                            `coordenadas_2` varchar(20) DEFAULT NULL,
                            `coordenadas_3` varchar(20) DEFAULT NULL,
                            `coordenadas_4` varchar(20) DEFAULT NULL,
                            PRIMARY KEY (`id_empleado`,`dia`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "avisos":
                    try {
                        $tabla_desa = 'avisos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `titulo` text NOT NULL,
                            `inicio` date NOT NULL,
                            `fin` date NOT NULL,
                            `tipo` varchar(10) DEFAULT NULL,
                            `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            `estatus` int(11) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "avisos_multimedia":
                    try {

                        $tabla_desa = 'avisos_multimedia';

                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_avisos` int(11) DEFAULT NULL,
                            `nombre` varchar(50) DEFAULT NULL,
                            `tipo` varchar(10) DEFAULT NULL,
                            `tiempo` int(11) DEFAULT '3000',
                            PRIMARY KEY (`id`),
                            KEY `id_avisos` (`id_avisos`),
                            CONSTRAINT `FK_avisos_multimedia_avisos` FOREIGN KEY (`id_avisos`) REFERENCES `avisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "biometricos":
                    try {

                        $tabla_desa = 'biometricos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `principal` int(11) DEFAULT '0' COMMENT '1:principal,0:nada',
                            `nombre` varchar(255) DEFAULT NULL,
                            `ip` varchar(255) DEFAULT NULL,
                            `mac` varchar(255) DEFAULT NULL,
                            `puerto` int(11) DEFAULT NULL,
                            `modelo` varchar(255) DEFAULT NULL,
                            `num_serie` varchar(255) DEFAULT NULL,
                            `firmware` varchar(255) DEFAULT NULL,
                            `plataforma` varchar(255) DEFAULT NULL,
                            `proveedor` varchar(255) DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL COMMENT '0:inactivo,1:activo,2:Borrado',
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `mac` (`mac`),
                            UNIQUE KEY `num_serie` (`num_serie`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "bloques_cuestionario":
                    try {
                        $tabla_desa = 'bloques_cuestionario';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(255) DEFAULT NULL,
                            `descripcion` varchar(255) DEFAULT NULL,
                            `instrucciones` varchar(255) DEFAULT NULL,
                            `idcuestionario` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_bloque_cuestionarios1_idx` (`idcuestionario`),
                            CONSTRAINT `fk_bloque_cuestionarios1` FOREIGN KEY (`idcuestionario`) REFERENCES `cuestionarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "bloque_preguntas":
                    try {
                        $tabla_desa = 'bloque_preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idpregunta` int(11) NOT NULL,
                            `orden` int(11) NOT NULL,
                            `idbloque` int(11) NOT NULL,
                            `condicional` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_cuestionario_preguntas_preguntas1_idx` (`idpregunta`),
                            KEY `fk_cuestionario_preguntas_bloque1_idx` (`idbloque`),
                            CONSTRAINT `fk_cuestionario_preguntas_Bloque1` FOREIGN KEY (`idbloque`) REFERENCES `bloques_cuestionario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_cuestionario_preguntas_preguntas1` FOREIGN KEY (`idpregunta`) REFERENCES `preguntas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "bitacora":
                    try {
                        $tabla_desa = 'bitacora';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(15) NOT NULL AUTO_INCREMENT,
                            `usuario` text NOT NULL,
                            `descripcion` text NOT NULL,
                            `estatus` varchar(1) NOT NULL,
                            `tipo` varchar(5) NOT NULL,
                            `referencia` varchar(20) NOT NULL,
                            `genero` text NOT NULL,
                            `finalizo` varchar(200) DEFAULT NULL,
                            `evento` varchar(20) NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "catalogos_norma":
                    try {
                        $tabla_desa = 'catalogos_norma';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `dato` varchar(255) NOT NULL,
                            `orden` int(11) DEFAULT NULL,
                            `clase` varchar(45) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "categorias":
                    try {
                        $tabla_desa = 'categorias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(50) NOT NULL,
                            `estatus` int(2) NOT NULL DEFAULT '0',
                            `tipo_clase` varchar(50) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "conceptos_nomina":
                    try {
                        $tabla_desa = 'conceptos_nomina';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                           `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_alterno` varchar(20) NOT NULL,
                            `nombre_concepto` varchar(100) NOT NULL,
                            `nombre_corto` varchar(50) NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            `file_rool` int(11) NOT NULL,
                            `tipo` int(11) NOT NULL,
                            `tipo_proceso` int(11) NOT NULL,
                            `nomina` int(11) NOT NULL DEFAULT '0',
                            `finiquito` int(11) NOT NULL DEFAULT '0',
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `rutinas` varchar(50) DEFAULT NULL,
                            `activo_en_nomina` int(11) DEFAULT '0',
                            `codigo_sat` varchar(30) NOT NULL,
                            `cuenta_contable` varchar(50) DEFAULT NULL,
                            `integra_variables` varchar(50) NOT NULL,
                            `debe_haber` int(11) NOT NULL,
                            `name_cuenta` varchar(50) DEFAULT NULL,
                            `name_cuenta_isr` varchar(50) DEFAULT NULL,
                            `cuenta_contable_isr` varchar(50) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "configuracion_formulario":
                    try {
                        $tabla_desa = 'configuracion_formulario';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `titulo` varchar(250) DEFAULT NULL,
                            `estatus` int(1) DEFAULT '1',
                            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            `updated_at` timestamp NULL DEFAULT NULL,
                            `deleted_At` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "contactos_covid":
                    try {
                        $tabla_desa = 'contactos_covid';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                           `id` int(11) NOT NULL AUTO_INCREMENT,
                            `registro_covid_id` int(11) NOT NULL,
                            `id_empleado` int(11) NOT NULL,
                            `fecha` datetime DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `notas` varchar(45) DEFAULT NULL,
                            `id_registro` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_contactos_covid_registro_covid1_idx` (`registro_covid_id`),
                            KEY `fk_contactos_covid_registro_covid2_idx` (`id_registro`),
                            KEY `fk_contactos_covid_empleados1_idx` (`id_empleado`),
                            CONSTRAINT `fk_contactos_covid_empleados1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_contactos_covid_registro_covid1` FOREIGN KEY (`registro_covid_id`) REFERENCES `registro_covid` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_contactos_covid_registro_covid2` FOREIGN KEY (`id_registro`) REFERENCES `registro_covid` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "cuestionarios":
                    try {
                        $tabla_desa = 'cuestionarios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            `descripcion` varchar(255) DEFAULT NULL,
                            `tipo` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "cuestionarios_trabajadores":
                    try {
                        $tabla_desa = 'cuestionarios_trabajadores';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idperiodo` int(11) NOT NULL,
                            `idcuestionario` int(11) NOT NULL,
                            `idinformacion_trabajador` int(11) NOT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `fecha_inicio` datetime DEFAULT NULL,
                            `fecha_fin` datetime DEFAULT NULL,
                            `total_cuestionario` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_periodo_seleccionado_cuestionario_periodo_norma1_idx` (`idperiodo`),
                            KEY `fk_periodo_seleccionado_cuestionario_cuestionarios1_idx` (`idcuestionario`),
                            KEY `fk_periodo_seleccionado_cuestionario_seleccionados_cuestion_idx` (`idinformacion_trabajador`),
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_cuestionarios1` FOREIGN KEY (`idcuestionario`) REFERENCES `cuestionarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_periodo_norma1` FOREIGN KEY (`idperiodo`) REFERENCES `periodos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_periodo_seleccionado_cuestionario_seleccionados_cuestionar1` FOREIGN KEY (`idinformacion_trabajador`) REFERENCES `informacion_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "contratos":
                    try {
                        $tabla_desa = 'contratos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fecha_contrato` datetime NOT NULL,
                            `fecha_vencimiento` datetime DEFAULT NULL,
                            `estatus` int(11) NOT NULL,
                            `numero_dias` int(11) NOT NULL,
                            `contrato` varchar(200) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `alerta` date DEFAULT NULL,
                            `archivo` varchar(45) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "credenciales":
                    try {
                        $tabla_desa = 'credenciales';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `repositorio` varchar(200) NOT NULL,
                            `estatus` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "demandas_audiencias":
                    try {
                        $tabla_desa = 'demandas_audiencias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_demanda` int(11) NOT NULL,
                            `pre` int(11) DEFAULT NULL,
                            `tipo_audiencia` varchar(45) DEFAULT NULL,
                            `junta` varchar(150) DEFAULT NULL,
                            `expediente` varchar(100) DEFAULT NULL,
                            `ciudad` varchar(45) DEFAULT NULL,
                            `fecha_audiencia` datetime DEFAULT NULL,
                            `hora_audiencia` time DEFAULT NULL,
                            `fecha_aviso` datetime DEFAULT NULL,
                            `arreglo_conciliatorio` text,
                            `incidencia` text,
                            `fecha_proxima` datetime DEFAULT NULL,
                            `costo_estimado_honorarios` decimal(10,2) DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `conciliacion` text,
                            `tipo_contestacion` varchar(50) DEFAULT NULL,
                            `tipo_prueba` varchar(50) DEFAULT NULL,
                            `motivo` text,
                            `prueba_pericial` varchar(45) DEFAULT NULL,
                            `alegatos` text,
                            `laudo` varchar(45) DEFAULT NULL,
                            `documento_laudo` int(11) DEFAULT NULL,
                            `desahogo` varchar(45) DEFAULT NULL,
                            `motivo_desahogo` text,
                            `monto` decimal(10,2) DEFAULT NULL,
                            `forma_pago` varchar(45) DEFAULT NULL,
                            `estatus_final` varchar(10) DEFAULT NULL,
                            `concluido` varchar(45) DEFAULT NULL,
                            `concluido_motivo` varchar(10) DEFAULT NULL,
                            `amparo` varchar(45) DEFAULT NULL,
                            `fecha_sentencia` datetime DEFAULT NULL,
                            `sentido` varchar(45) DEFAULT NULL,
                            `historial` text,
                            PRIMARY KEY (`id`),
                            KEY `fk_demandas_audiencias_demandas_juridico1_idx` (`id_demanda`),
                            CONSTRAINT `fk_demandas_audiencias_demandas_juridico1` FOREIGN KEY (`id_demanda`) REFERENCES `demandas_juridico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "demandas_audiencias_bita":
                    try {
                        $tabla_desa = 'demandas_audiencias_bita';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(15) NOT NULL AUTO_INCREMENT,
                            `id_audiencia` varchar(10) NOT NULL,
                            `id_demanda` varchar(10) NOT NULL,
                            `fecha` date DEFAULT NULL,
                            `comentario` text NOT NULL,
                            `fecha_creado` datetime NOT NULL,
                            `creado` varchar(50) NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "demandas_juridico":
                    try {
                        $tabla_desa = 'demandas_juridico';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `periodo` varchar(20) NOT NULL,
                            `ejercicio` varchar(45) NOT NULL,
                            `fecha_baja` datetime DEFAULT NULL,
                            `importe` decimal(10,2) NOT NULL,
                            `motivo` varchar(255) DEFAULT NULL,
                            `salario` decimal(10,2) DEFAULT NULL,
                            `fecha_antiguedad` datetime DEFAULT NULL,
                            `salario_caido` decimal(10,2) DEFAULT NULL,
                            `prestaciones_devengadas` decimal(10,2) DEFAULT NULL,
                            `estatus` int(11) NOT NULL,
                            `est_importe` int(11) DEFAULT NULL,
                            `est_prestaciones_d` int(11) DEFAULT NULL,
                            `est_indm_cons` int(11) DEFAULT NULL,
                            `est_indm_anio` int(11) DEFAULT NULL,
                            `est_salario_caido` int(11) DEFAULT NULL,
                            `fecha_proxima_audiencia` datetime DEFAULT NULL,
                            `motivo_arreglo_conciliacion` varchar(350) DEFAULT NULL,
                            `indemnizacion_constitucional` int(11) DEFAULT NULL,
                            `indemnizacion_anio` decimal(10,2) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            `fecha_alta` datetime DEFAULT NULL,
                            `folio` varchar(45) DEFAULT NULL,
                            `importe_extra` decimal(10,0) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_demandas_juridico_empleados1_idx` (`id_empleado`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "departamentos":
                    try {
                        $tabla_desa = 'departamentos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(50) NOT NULL,
                            `estatus` int(11) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);

                        DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();

                        $stm = "INSERT INTO ".$base.".".$tabla_desa." (
                            `id`,
                            `nombre`,
                            `estatus`,
                            `fecha_creacion`
                            )
                                VALUES
                            ('','General','1',now())";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "detalle_iconos_formularios":
                    try {
                        $tabla_desa = 'detalle_iconos_formularios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_opc_pregunta` int(11) DEFAULT NULL COMMENT 'Relación con tabla configuracion formulario_opc_preg',
                            `idicono` int(11) DEFAULT NULL COMMENT 'Relación con tabla configuracion_formulario',
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "dispersiones":
                    try {
                        $tabla_desa = 'dispersiones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_guardado` datetime NOT NULL,
                            `confirmado` int(11) NOT NULL DEFAULT '0',
                            `archivo_generado` varchar(200) NOT NULL,
                            `name_archivo` varchar(200) NOT NULL,
                            `ruta` varchar(200) NOT NULL,
                            `importe` varchar(90) NOT NULL,
                            `tipo_dispersion` int(11) DEFAULT '1',
                            `ejercicio` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            KEY `IdPeriodo` (`id_periodo`),
                            CONSTRAINT `dispersiones_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `dispersiones_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "dispersiones_aguinaldo":
                    try {
                        $tabla_desa = 'dispersiones_aguinaldo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `ejercicio` int(11) NOT NULL,
                            `fecha_guardado` datetime NOT NULL,
                            `confirmado` int(11) NOT NULL DEFAULT '0',
                            `archivo_generado` varchar(200) NOT NULL,
                            `nombre_archivo` varchar(200) NOT NULL,
                            `ruta` varchar(200) NOT NULL,
                            `importe` varchar(90) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`),
                            CONSTRAINT `dispersiones_aguinaldo_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "documentos_empleados":
                    try {
                        $tabla_desa = 'documentos_empleados';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `file_documento` text NOT NULL,
                            PRIMARY KEY (`id`,`id_empleado`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "doc_empleados":
                    try {
                        $tabla_desa = 'doc_empleados';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre_doc` text NOT NULL,
                            `tipo_archivo` text NOT NULL,
                            `obligatorio` int(11) NOT NULL,
                            `estatus` int(11) NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "ejercicios":
                    try {
                        $tabla_desa = 'ejercicios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `ejercicio` int(11) NOT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "empleados":
                    try {
                        $tabla_desa = 'empleados';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `numero_empleado` varchar(10) NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            `nombre` varchar(50) NOT NULL,
                            `apaterno` varchar(50) NOT NULL,
                            `amaterno` varchar(50) DEFAULT NULL,
                            `genero` varchar(20) DEFAULT NULL,
                            `correo` varchar(50) DEFAULT NULL,
                            `rfc` varchar(20) DEFAULT NULL,
                            `curp` varchar(25) DEFAULT NULL,
                            `fecha_nacimiento` date DEFAULT NULL,
                            `lugar_nacimiento` varchar(50) DEFAULT NULL,
                            `nacionalidad` varchar(50) DEFAULT NULL,
                            `estado_civil` varchar(50) DEFAULT NULL,
                            `escolaridad` varchar(50) DEFAULT NULL,
                            `profesion` varchar(50) DEFAULT NULL,
                            `calle_numero` varchar(50) DEFAULT NULL,
                            `colonia` varchar(50) DEFAULT NULL,
                            `delegacion` varchar(50) DEFAULT NULL,
                            `estado` varchar(50) DEFAULT NULL,
                            `cp` varchar(10) DEFAULT NULL,
                            `telefono_movil` varchar(50) DEFAULT NULL,
                            `telefono_casa` varchar(50) DEFAULT NULL,
                            `nss` varchar(30) DEFAULT NULL,
                            `id_categoria` int(2) NOT NULL,
                            `id_categoria_asimilados` int(2) NOT NULL,
                            `id_departamento` int(2) DEFAULT NULL,
                            `id_prestacion` int(4) DEFAULT NULL,
                            `id_horario` int(4) DEFAULT '0',
                            `id_puesto` int(4) DEFAULT NULL,
                            `repositorio` varchar(250) DEFAULT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            `fecha_alta` date NOT NULL,
                            `fecha_antiguedad` date DEFAULT NULL,
                            `tipo_contrato` varchar(50) DEFAULT NULL,
                            `tipo_empleado` varchar(50) DEFAULT NULL,
                            `tipo_jornada` varchar(50) DEFAULT NULL,
                            `tipo_de_nomina` varchar(50) DEFAULT NULL,
                            `forma_de_pago` varchar(50) DEFAULT NULL,
                            `salario_diario` varchar(50) DEFAULT '0',
                            `salario_digital` varchar(50) DEFAULT '0',
                            `salario_diario_integrado` varchar(50) DEFAULT '0',
                            `sueldo_neto` varchar(50) DEFAULT NULL,
                            `dias_vacaciones` int(11) DEFAULT NULL,
                            `dias_aguinaldo` int(11) DEFAULT '0',
                            `porcentaje_prima` int(11) DEFAULT '0',
                            `sede` varchar(10) DEFAULT NULL,
                            `ubicacion` varchar(50) DEFAULT NULL,
                            `id_banco` int(11) DEFAULT NULL,
                            `id_bancario` varchar(50) DEFAULT NULL,
                            `tipo_cuenta` varchar(30) DEFAULT NULL,
                            `clabe_interbancaria` varchar(90) DEFAULT NULL,
                            `cuenta_bancaria` varchar(50) DEFAULT NULL,
                            `cuenta_bancaria2` varchar(100) DEFAULT NULL,
                            `cuenta_bancaria3` varchar(100) DEFAULT NULL,
                            `num_tarjeta` varchar(200) DEFAULT NULL,
                            `folio_alta` varchar(50) DEFAULT NULL,
                            `estatus_folio_alta` varchar(50) DEFAULT NULL,
                            `folio_alta_interno` varchar(50) DEFAULT NULL,
                            `folio_baja` varchar(50) DEFAULT NULL,
                            `estatus_folio_baja` varchar(50) DEFAULT NULL,
                            `folio_baja_interno` varchar(50) DEFAULT NULL,
                            `folio_modificacion` varchar(50) DEFAULT NULL,
                            `folio_modif_interno` varchar(50) DEFAULT NULL,
                            `estatus_folio_modificacion` varchar(50) DEFAULT NULL,
                            `avisar_a` varchar(50) DEFAULT NULL,
                            `avisar_a_parentesco` varchar(200) DEFAULT NULL,
                            `avisar_a_telefono` varchar(50) DEFAULT NULL,
                            `beneficiario` varchar(50) DEFAULT NULL,
                            `beneficiario_parentesco` varchar(50) DEFAULT NULL,
                            `beneficiario_avisar_telefono` varchar(200) DEFAULT NULL,
                            `tipo_fiscal` int(11) DEFAULT '1',
                            `tipo_sindical` int(11) DEFAULT '1',
                            `tipo_salario` varchar(90) DEFAULT NULL,
                            `valida_exp` varchar(1) DEFAULT NULL,
                            `num_credito_infonavit` varchar(50) DEFAULT NULL,
                            `tipo_descuento` varchar(50) DEFAULT NULL,
                            `valor_descuento` varchar(50) DEFAULT NULL,
                            `num_credito_fonacot` varchar(50) DEFAULT NULL,
                            `valor_fonacot` varchar(50) DEFAULT NULL,
                            `estatus_evaluacion` varchar(4) DEFAULT '0',
                            `fecha_baja` date DEFAULT NULL,
                            `fecha_ult_alta` date DEFAULT NULL,
                            `fecha_ult_baja` date DEFAULT NULL,
                            `causa_baja` varchar(50) DEFAULT NULL,
                            `baja_oficial` varchar(50) DEFAULT NULL,
                            `estatus_firma_finiquito` varchar(1) DEFAULT '0',
                            `finiquitado` int(11) DEFAULT '0',
                            `demanda_activa` int(2) DEFAULT '0',
                            `file_ine` varchar(255) DEFAULT NULL,
                            `file_curp` varchar(255) DEFAULT NULL,
                            `file_nss` varchar(255) DEFAULT NULL,
                            `file_nacimiento` varchar(255) DEFAULT NULL,
                            `file_comprobante` varchar(255) DEFAULT NULL,
                            `file_aviso` varchar(255) DEFAULT NULL,
                            `file_estado` varchar(255) DEFAULT NULL,
                            `file_contrato` varchar(255) DEFAULT NULL,
                            `file_rfc` varchar(255) DEFAULT NULL,
                            `file_fotografia` varchar(255) DEFAULT NULL,
                            `file_analisis` varchar(255) DEFAULT NULL,
                            `file_fonacot` varchar(255) DEFAULT NULL,
                            `file_curriculum` varchar(255) DEFAULT NULL,
                            `file_acuse` varchar(255) DEFAULT NULL,
                            `file_estado_cuenta` varchar(255) DEFAULT NULL,
                            `file_fiel_imss` varchar(255) DEFAULT NULL,
                            `jefe_inmediato` varchar(100) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `correo` (`correo`),
                            KEY `idprestacion` (`id_prestacion`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "empleados_campos_extras":
                    try {
                        $tabla_desa = 'empleados_campos_extras';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre_campo` varchar(50) NOT NULL DEFAULT '0',
                            `alias` varchar(50) NOT NULL DEFAULT '0',
                            `tipo` varchar(50) NOT NULL DEFAULT '0',
                            `obligatorio` int(11) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "empleados_deducciones":
                    try {
                        $tabla_desa = 'empleados_deducciones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_concepto` int(11) NOT NULL,
                            `estatus` tinyint(1) NOT NULL DEFAULT '1',
                            `importe_total` decimal(10,2) NOT NULL,
                            `numero_pagos_a_realizar` int(2) NOT NULL,
                            `cantidad_a_descontar` decimal(10,2) NOT NULL,
                            `saldo` decimal(10,2) NOT NULL,
                            `numero_pagos_realizados` int(11) NOT NULL DEFAULT '0',
                            `fecha_inicio` date NOT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*TODO Checar para eliminar*/
                case "empleados_informacion_extra":
                    try {
                        $tabla_desa = 'empleados_informacion_extra';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                           `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
                           PRIMARY KEY (`id_empleado`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "empleados_percepciones":
                    try {
                        $tabla_desa = 'empleados_percepciones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_concepto` int(11) NOT NULL,
                            `estatus` tinyint(1) NOT NULL,
                            `importe_total` decimal(10,2) NOT NULL,
                            `numero_aportaciones_a_realizar` int(11) NOT NULL DEFAULT '0',
                            `cantidad_a_aportar` decimal(10,2) NOT NULL,
                            `saldo` decimal(10,2) NOT NULL,
                            `numero_aportaciones_realizadas` int(11) DEFAULT '0',
                            `fecha_inicio` date NOT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "empleados_prestaciones_extras":
                    try {
                        $tabla_desa = 'empleados_prestaciones_extras';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            `numero_certificado` varchar(90) NOT NULL,
                            `valor_seguro_gastos_m` varchar(90) NOT NULL,
                            `valor_plan_espejo` varchar(90) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `empleados_prestaciones_extras_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "encargados":
                    try {
                        $tabla_desa = 'encargados';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(255) NOT NULL,
                            `correo` varchar(255) NOT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_encargados_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            CONSTRAINT `fk_encargados_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*TODO Checar para eliminar*/
                case "encuesta":
                    try {
                        $tabla_desa = 'encuesta';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `uno` varchar(100) NOT NULL,
                            `dos` varchar(100) NOT NULL,
                            `tres` varchar(100) NOT NULL,
                            `cuatrouno` varchar(100) NOT NULL,
                            `cuatrodos` varchar(100) NOT NULL,
                            `cuatrotres` varchar(100) NOT NULL,
                            `cuatrocuatro` varchar(100) NOT NULL,
                            `cuatrocinco` varchar(100) NOT NULL,
                            `cuatroseis` varchar(100) NOT NULL,
                            `cuatrosiete` varchar(100) NOT NULL,
                            `cuatroocho` varchar(100) NOT NULL,
                            `cinco` varchar(100) NOT NULL,
                            `seis` varchar(100) NOT NULL,
                            `siete` varchar(100) NOT NULL,
                            `ocho` varchar(100) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `encuesta_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*TODO Checar para eliminar*/
                case "evaluacion_desempeno":
                    try {
                        $tabla_desa = 'evaluacion_desempeno';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fecha_creacion` date NOT NULL,
                            `estatus` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `evaluacionDesempeno_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "eventos":
                    try {
                        $tabla_desa = 'eventos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(10) NOT NULL AUTO_INCREMENT,
                            `nombre` text NOT NULL,
                            `cuerpo` text NOT NULL,
                            `usuario` varchar(1) NOT NULL DEFAULT '1',
                            `estatus` varchar(1) NOT NULL,
                            `descripcion` text NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);

                        DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();

                        $stm = 'INSERT INTO '.$base.'.'.$tabla_desa.' (
                            `id`, `nombre`, `cuerpo`, `usuario`, `estatus`, `fecha_edicion`)
                                VALUES
                            (1, "Alta de empleados", "<h2 style\=\"text-align: center;\">ALERTA HR System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta de empleado<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero un nuevo empleado \#empleado, en el sistema, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>;\"", "0", "0", "2018-10-23 11:03:49"),
                                                (2, "Cambios de parametros", "<h2 style\=\"text-align: center;\">ALERTA HR System<\/h2>
                                <h3 style\=\"text-align: center;\">Cambios en los parametros<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero cambios en los parametros del sistema el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>;\"", "0", "0", "2018-10-23 11:03:49"),
                                                (5, "Generacion de contrato", "<h2 style\=\"text-align: center;\">ALERTA HR System<\/h2>
                                <h3 style\=\"text-align: center;\">Generacion de contrato<\/h3><p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero un nuevo contrato para el empleado  \#empleado, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p><center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>\";", "0", "0", "2018-10-23 11:03:49"),
                                                (7, "Validacion de expediente", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Validacion de expediente<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El expediente del colaborador \#empleado, fue validado satisfactoriamente por \#usuario el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "0", "2018-10-23 11:03:49"),
                                                (8, "Alta de empleados masiva", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta de empleados masiva<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">Se dio de alta empleados de forma masiva por el usuario \#usuario el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "0", "2018-10-23 11:03:49"),
                                                (9, "Alta de concepto", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta de concepto<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero un nuevo concepto en el catalogo de conceptos dentro del sistema, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (10, "Generacion de nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Generacion de nomina<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario genero la nomina correctamente, el d\&iacute;a \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (11, "Nomina correcta", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Nomina aprobada<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario aprobo la nomina generada, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (12, "Nomina incorrecta", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Nomina incorrecta<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario marco la nomina como incorrecta, el dia \#fecha. \#periodo . <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (13, "Factura disponible para pago", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Generacion de Factura<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero una factura disponible para pago, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (14, "Solicitud para financiamiento de nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Solicitud para financiamiento<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario ha generado una solicitud para financiamiento de nomina, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (15, "Nomina dispersada y pagada", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Nomina dispersada y pagada<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario indico que la nomina fue pagada y dispersada, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (16, "Pago de nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Pago de nomina<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario indico que la nomina debe ser pagada, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (17, "Renovacion de contratos", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Renovacion de contratos<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">Existen contratos de empleados proximos a vencer, accede al sistema para validarlo. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (18, "Alta de empresa", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta de empresa<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario\&nbsp;\#usuario genero la empresa \#empresa en el sistema, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (19, "Confirmacion de nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Confirmacion de nomina<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario confirmo la nomina de del periodo \#periodo, el dia \#fecha. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (20, "Alta datos bancarios", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta datos bancarios<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">Se dio de alta el empleado \#empleado, por favor dar de alta sus datos bancarios. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (21, "Alta datos IMSS", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta datos IMSS<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">Se dio de alta el empleado \#empleado, por favor dar de alta sus datos bancarios. <\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (22, "Alta Finiquito", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Alta Finiquito<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">Se dio de alta el finiquito del empleado \#empleado, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (23, "Modificacion de fecha de antiguedad", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Modificacion de fecha de antiguedad<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario modifico la fecha de antiguedad del empleado \#empleado,
                                fecha original: \#fechaanterior y fecha nueva: \#fechanueva.<br>Nota: El cambio de la fecha de la fecha de antigüedad puede implicar cambios en las prestaciones, vacaciones, aguinaldo, etc.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (24, "Encuesta de salida", "<p style\=\'text-align:center\'>Hola Buen dia <b>\#empleado<\/b>,<p><br><p style\=\'text-align:center\'>Como parte de la Evaluacion a la empresa <b>\#empresa<\/b> te pedimos de favor ingresar a nuestro portal \(<a href\=\'https:\/\/www.hrsystem.com.mx\/\'>https:\/\/www.hrsystem.com.mx\/<\/a>\) a responder una breve Encuesta y asi poder mejorar nuestros procesos.<\/p><br><br>
                                <p style\=\'text-align:center\'> Podras ingresar con los siguientes datos:<\/p><br>
                                <p style\=\'text-align:center\'>User: \#EncuestaUser <br>Password: \#contra <\/p>
                                <p style\=\'text-align: center;\'>\&nbsp;<\/p>
                                 <p>\&nbsp;<\/p>
                                 <center><p><img style\=\'display: block; margin-left: auto; margin-right: auto;\' src\=\'https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\' alt\=\'logo\' width\=\'200\' height\=\'48\' \/><\/p><\/center>
                                 <h6 style\=\'text-align: center;\'>-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (25, "Incapacidades", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">INCAPACIDADES<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero \#movimiento en las incapacidades del empleado \#empleado, clave incapacidad: \#clvIncapa.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (26, "Timbrado nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">TIMBRADO NOMINA<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero el timbrado de nomina del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (27, "Timbrado nomina masivo", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">TIMBRADO NOMINA MASIVO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero el timbrado masivo de nomina, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (28, "Cancelacion timbrado nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">CANCELACION TIMBRADO NOMINA<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, solicito la cancelacion del timbre de nomina del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (29, "Verificacion estatus cancelacion nomina", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">VERIFICACION ESTATUS CANCELACION NOMINA<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, verifico el estatus de la cancelacion del timbre de nomina, No. factura \#periodo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (30, "Dispersion", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Dispersion<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero un archivo de dispersion para \#periodo, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (31, "Timbrado aguinaldo", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">TIMBRADO AGUINALDO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero el timbrado de aguinaldo del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (32, "Timbrado aguinaldo masivo", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">TIMBRADO AGUINALDO MASIVO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero el timbrado masivo de aguinaldo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (33, "Cancelacion timbrado aguinaldo", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">CANCELACION TIMBRADO AGUINALDO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, solicito la cancelacion del timbre de aguinaldo del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (34, "Verificacion estatus cancelacion aguinaldo", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">VERIFICACION ESTATUS CANCELACION AGUINALDO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, verifico el estatus de la cancelacion del timbre de aguinaldo, No. factura \#periodo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (35, "Timbrado finiquito", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">TIMBRADO FINIQUITO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, genero el timbrado de finiquito del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (36, "Cancelacion timbrado finiquito", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">CANCELACION TIMBRADO FINIQUITO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, solicito la cancelacion del timbre de finiquito del empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (37, "Verificacion estatus cancelacion finiquito", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">VERIFICACION ESTATUS CANCELACION FINIQUITO<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, verifico el estatus de la cancelacion del timbre de finiquito, No. factura \#periodo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (44, "Baja de empleado", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Baja de empleado<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, dio de baja a el empleado \#empleado, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (45, "Revocacion de confirmacion", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Revocacion de confirmacion de nomina<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario, solicito la revocacion de confirmacion de nomina del periodo \#periodo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (100, "Solicitud de soporte", "<h2 style\=\"text-align: center;\">ALERTA HR-System<\/h2>
                                <h3 style\=\"text-align: center;\">Solicitud de soporte<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El usuario \#usuario solicito soporte con prioridad \#periodo, el dia \#fecha.
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <p>\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado automaticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>", "0", "1", "2018-10-23 11:03:49"),
                                                (110, "Aniversario Cliente", "<h2 style\=\"text-align: center;\">ALERTA HR System<\/h2>
                                <h3 style\=\"text-align: center;\">Aniversario Cliente<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El cliente\&nbsp;\#empresa cumple aniversario con nosotros, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>;\"", "0", "0", "2018-10-23 11:03:49"),
                                                (120, "Aniversario Empleado", "<h2 style\=\"text-align: center;\">ALERTA HR System<\/h2>
                                <h3 style\=\"text-align: center;\">Aniversario Empleado<\/h3>
                                <p>\&nbsp;<\/p>
                                <p style\=\"text-align: center;\">El empleado\&nbsp;\#empleado cumple aniversario con nosotros, el dia \#fecha.<\/p>
                                <p style\=\"text-align: center;\">\&nbsp;<\/p>
                                <p>\&nbsp;<\/p>
                                <center><p><img style\=\"display: block; margin-left: auto; margin-right: auto;\" src\=\"https:\/\/www.hrsystem.com.mx\/img\/HR\%20SYSTEM\%20LOGO.png\" alt\=\"logo\" width\=\"200\" height\=\"48\" \/><\/p><\/center>
                                <h6 style\=\"text-align: center;\">-----Mensaje Generado autom\&aacute;ticamente por HR-System. Si el destinatario de este correo es incorrecto reportalo a ayuda@hrsystem.com.mx. No contestar a este correo ya que solo es informativo-----<\/h6>;\"", "0", "0", "2018-10-23 11:03:49")';
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "eventos_correos":
                    try {
                        $tabla_desa = 'eventos_correos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id_evento` int(15) NOT NULL AUTO_INCREMENT,
                            `id_correo` int(15) NOT NULL,
                            `correo` text NOT NULL,
                            `nombre` text NOT NULL,
                            `tipo` varchar(1) NOT NULL,
                            `destinatario` varchar(1) NOT NULL,
                            PRIMARY KEY (`id_evento`,`id_correo`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "evidencias_audiencias":
                    try {
                        $tabla_desa = 'evidencias_audiencias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) NOT NULL,
                            `tipo` varchar(5) DEFAULT NULL,
                            `masiva` int(11) DEFAULT NULL,
                            `id_audiencia` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_evidencias_audiencias_demandas_audiencias1_idx` (`id_audiencia`),
                            CONSTRAINT `fk_evidencias_audiencias_demandas_audiencias1` FOREIGN KEY (`id_audiencia`) REFERENCES `demandas_audiencias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "evidencia_covid":
                    try {
                        $tabla_desa = 'evidencia_covid';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) NOT NULL,
                            `tipo` int(11) NOT NULL,
                            `id_registro_covid` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_evidencia_covid_registro_covid1_idx` (`id_registro_covid`),
                            CONSTRAINT `fk_evidencia_covid_registro_covid1` FOREIGN KEY (`id_registro_covid`) REFERENCES `registro_covid` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "excentos_norma":
                    try {
                        $tabla_desa = 'excentos_norma';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) DEFAULT NULL,
                            `paterno` varchar(45) DEFAULT NULL,
                            `materno` varchar(45) DEFAULT NULL,
                            `periodo_norma` int(11) NOT NULL,
                            `empleados_id` int(11) DEFAULT NULL,
                            `sexo` int(11) DEFAULT NULL,
                            `correo` varchar(45) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_excentos_norma_periodos_norma1_idx` (`periodo_norma`),
                            CONSTRAINT `fk_excentos_norma_periodos_norma1` FOREIGN KEY (`periodo_norma`) REFERENCES `periodos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "facturas":
                    try {
                        $tabla_desa = 'facturas';
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `usuario` varchar(100) NOT NULL,
                            `creado` datetime NOT NULL,
                            `emisora` int(11) NOT NULL,
                            `metodo` varchar(4) NOT NULL,
                            `forma` varchar(4) NOT NULL,
                            `estatus` varchar(1) NOT NULL,
                            `regimen` varchar(4) NOT NULL,
                            `tipo_comprobante` varchar(40) NOT NULL,
                            `folio_relacionado` varchar(40) DEFAULT NULL,
                            `tipo_relacion` varchar(40) DEFAULT NULL,
                            `fecha_pago` varchar(40) DEFAULT NULL,
                            `monto` varchar(40) DEFAULT NULL,
                            `folio` varchar(40) DEFAULT NULL,
                            `importe_pagado` varchar(40) DEFAULT NULL,
                            `num_parcialidad` varchar(40) DEFAULT NULL,
                            `importe_saldo_anterior` varchar(40) DEFAULT NULL,
                            `importe_saldo_insoluto` varchar(40) DEFAULT NULL,
                            `folio_relacionado_2` varchar(100) DEFAULT NULL,
                            `folio_2` varchar(40) DEFAULT NULL,
                            `importe_pagado_2` varchar(40) DEFAULT NULL,
                            `num_parcialidad_2` varchar(40) DEFAULT NULL,
                            `importe_saldo_anterior_2` varchar(40) DEFAULT NULL,
                            `importe_saldo_insoluto_2` varchar(40) DEFAULT NULL,
                            `folio_relacionado_3` varchar(100) DEFAULT NULL,
                            `folio_3` varchar(40) DEFAULT NULL,
                            `importe_pagado_3` varchar(40) DEFAULT NULL,
                            `num_parcialidad_3` varchar(40) DEFAULT NULL,
                            `importe_saldo_anterior_3` varchar(40) DEFAULT NULL,
                            `importe_saldo_insolu_3` varchar(40) DEFAULT NULL,
                            `metodo_3` varchar(40) DEFAULT NULL,
                            `metodo_2` varchar(40) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "facturas_detalle":
                    try {
                        $tabla_desa = 'facturas_detalle';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id_factura` int(11) NOT NULL,
                            `id_detalle` int(11) NOT NULL,
                            `cantidad` int(11) NOT NULL,
                            `unidad` varchar(4) NOT NULL,
                            `concepto` text NOT NULL,
                            `clave` varchar(8) NOT NULL,
                            `monto` decimal(10,2) NOT NULL,
                            `estatus` varchar(1) NOT NULL,
                            `impuesto_retenido` varchar(20) NOT NULL,
                            PRIMARY KEY (`id_factura`,`id_detalle`) USING BTREE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "factura_periodo":
                    try {
                        $tabla_desa = 'factura_periodo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `emisora` int(11) NOT NULL,
                            `deposito2` varchar(1) NOT NULL DEFAULT '0',
                            `concepto` text NOT NULL,
                            `clave` varchar(8) NOT NULL,
                            `metodo` varchar(3) NOT NULL,
                            `estatus` varchar(1) NOT NULL,
                            `emisora2` varchar(4) DEFAULT '0',
                            PRIMARY KEY (`id`,`emisora`,`deposito2`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                //ToDo: checar para borrar
                case "fortalezas":
                    try {
                        $tabla_desa = 'fortalezas';

                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL,
                              `id_empleado` int(11) NOT NULL,
                              `fortalezas` varchar(80) NOT NULL,
                              `areas_opor` varchar(80) NOT NULL,
                              KEY `id_empleado` (`id_empleado`),
                              KEY `id` (`id`),
                              CONSTRAINT `fortalezas_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);

                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);

                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_encuesta":
                    try {
                        $tabla_desa = 'formulario_encuesta';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `titulo` varchar(100) DEFAULT NULL,
                            `descripcion` varchar(250) DEFAULT NULL,
                            `fecha_vencimiento` date DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            `updated_at` timestamp NULL DEFAULT NULL,
                            `deleted_at` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_opc_preguntas":
                    try {
                        $tabla_desa = 'formulario_opc_preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idpregunta` int(11) DEFAULT NULL COMMENT 'Relación con tabla formulario_pregunta',
                            `titulo` varchar(250) DEFAULT NULL,
                            `valor` varchar(250) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_preguntas":
                    try {
                        $tabla_desa = 'formulario_preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                            `idencuesta` int(11) DEFAULT NULL COMMENT 'id tabla formulario_encuesta',
                            `idtipo` int(11) DEFAULT NULL COMMENT 'id tabla formulario_tipo',
                            `pregunta` varchar(250) DEFAULT NULL COMMENT 'nombre de las preguntas',
                            `valor` varchar(250) DEFAULT NULL,
                            `estatus` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_respuestas":
                    try {
                        $tabla_desa = 'formulario_respuestas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idpreguntas` int(11) DEFAULT NULL COMMENT 'id de formulario_preguntas',
                            `idempleado` int(11) DEFAULT NULL,
                            `respuestas` varchar(250) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_tipo":
                    try {
                        $tabla_desa = 'formulario_tipo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fortalezas` varchar(80) NOT NULL,
                            `areas_opor` varchar(80) NOT NULL,
                            KEY `id_empleado` (`id_empleado`),
                            KEY `id` (`id`),
                            CONSTRAINT `fortalezas_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `fortalezas_ibfk_2` FOREIGN KEY (`id`) REFERENCES `evaluacion_desempeno` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "horarios":
                    try {
                        $tabla_desa = 'horarios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `alias` varchar(100) NOT NULL,
                            `estatus` varchar(1) NOT NULL,
                            `entrada` time NOT NULL,
                            `salida` time NOT NULL,
                            `tolerancia` int(11) NOT NULL DEFAULT '0',
                            `comida` varchar(1) NOT NULL DEFAULT '0',
                            `entrada_comida` time DEFAULT NULL,
                            `salida_comida` time  DEFAULT NULL,
                            `lunes` varchar(1) NOT NULL DEFAULT '0',
                            `martes` varchar(1) NOT NULL DEFAULT '0',
                            `miercoles` varchar(1) NOT NULL DEFAULT '0',
                            `jueves` varchar(1) NOT NULL DEFAULT '0',
                            `viernes` varchar(1) NOT NULL DEFAULT '0',
                            `sabado` varchar(1) NOT NULL DEFAULT '0',
                            `domingo` varchar(1) NOT NULL DEFAULT '0',
                            `indefinido` varchar(1) NOT NULL DEFAULT '0',
                            `retardos` varchar(10) NOT NULL DEFAULT '0',
                            `lunes_entrada` time NOT NULL DEFAULT '0',
                            `lunes_salida` time NOT NULL DEFAULT '0',
                            `martes_entrada` time NOT NULL DEFAULT '0',
                            `martes_salida` time NOT NULL DEFAULT '0',
                            `miercoles_entrada` time NOT NULL DEFAULT '0',
                            `miercoles_salida` time NOT NULL DEFAULT '0',
                            `jueves_entrada` time NOT NULL DEFAULT '0',
                            `jueves_salida` time NOT NULL DEFAULT '0',
                            `viernes_entrada` time NOT NULL DEFAULT '0',
                            `viernes_salida` time NOT NULL DEFAULT '0',
                            `sabado_entrada` time NOT NULL DEFAULT '0',
                            `sabado_salida` time NOT NULL DEFAULT '0',
                            `domingo_entrada` time NOT NULL DEFAULT '0',
                            `domingo_salida` time NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "horarios_dias":
                    try {
                        $tabla_desa = 'horarios_dias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_horario` int(11) NOT NULL,
                            `motivo` varchar(100) NOT NULL,
                            `fecha_festiva` date NOT NULL,
                            `usuario_alta` varchar(100) NOT NULL,
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "huellas_empleado":
                    try {
                        $tabla_desa = 'huellas_empleado';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `indice` int(11) DEFAULT NULL,
                            `huella` text,
                            PRIMARY KEY (`id`),
                            KEY `fk_huellas_empleado_empleado1_idx` (`id_empleado`),
                            CONSTRAINT `fk_huellas_empleado_empleado1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "iconos_configformulario":
                    try {
                        $tabla_desa = 'iconos_configformulario';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `idconfigform` int(11) DEFAULT NULL COMMENT 'Relacion con la tabla configuración formulario',
                            `icono` varchar(250) DEFAULT NULL,
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "impuestos":
                    try {
                        $tabla_desa = 'impuestos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `limite_inferior` varchar(50) NOT NULL,
                            `limite_superior` varchar(50) NOT NULL,
                            `cuota_fija` varchar(40) NOT NULL,
                            `porcentaje` varchar(45) NOT NULL,
                            `estatus` int(11) NOT NULL,
                            `tipo_tabla` text NOT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "incapacidades":
                    try {
                        $tabla_desa = 'incapacidades';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fecha_inicio_incapacidad` date NOT NULL,
                            `fecha_fin_incapacidad` date NOT NULL,
                            `estatus` int(11) NOT NULL,
                            `dias` int(11) NOT NULL,
                            `clave_incapacidad` text NOT NULL,
                            `tipo_incapacidad` text NOT NULL,
                            `saldo` varchar(50) DEFAULT NULL,
                            `periodo` int(11) NOT NULL,
                            `tipo_aplicacion` varchar(200) NOT NULL DEFAULT 'Bimestral',
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`),
                            CONSTRAINT `incapacidades_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "incidencias_prg":
                    try {
                        $tabla_desa = 'incidencias_prg';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_concepto` int(11) NOT NULL,
                            `estatus` int(11) NOT NULL,
                            `concepto` varchar(50) NOT NULL,
                            `importe` varchar(20) NOT NULL,
                            `inicia_descuento` date NOT NULL,
                            `activa_descuento` int(11) NOT NULL,
                            `saldo` varchar(20) NOT NULL,
                            `total_descuento` varchar(20) NOT NULL,
                            `numero_pagos` int(11) NOT NULL,
                            `inicio_aportacion` date NOT NULL,
                            `fin_aportacion` date NOT NULL,
                            `activa_aportacion` int(11) NOT NULL,
                            `total_aportaciones` varchar(20) NOT NULL,
                            `descuento_nomina` varchar(20) NOT NULL,
                            `suma_nomina` varchar(20) NOT NULL,
                            `num_pagos` int(11) NOT NULL DEFAULT '1',
                            `importe_a_descontar` text NOT NULL,
                            `percep_deduc` int(11) NOT NULL,
                            `especial` int(11) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            KEY `idconcepto` (`id_concepto`),
                            CONSTRAINT `incidencias_prg_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `incidencias_prg_ibfk_2` FOREIGN KEY (`id_concepto`) REFERENCES `conceptos_nomina` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "incidencias_prg_log":
                    try {
                        $tabla_desa = 'incidencias_prg_log';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_periodo` int(11) NOT NULL DEFAULT '0',
                            `id_empleado` int(11) NOT NULL DEFAULT '0',
                            `id_concepto` int(11) NOT NULL DEFAULT '0',
                            `fecha_creacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "informacion_trabajadores":
                    try {
                        $tabla_desa = 'informacion_trabajadores';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `estatus` int(11) NOT NULL,
                            `nombre` varchar(255) DEFAULT NULL,
                            `paterno` varchar(45) DEFAULT NULL,
                            `materno` varchar(45) DEFAULT NULL,
                            `sexo` int(11) DEFAULT NULL,
                            `edad` int(11) DEFAULT NULL,
                            `estado_civil` int(11) DEFAULT NULL,
                            `nivel_estudios` int(11) DEFAULT NULL,
                            `tipo_puesto` int(11) DEFAULT NULL,
                            `tipo_contratacion` int(11) DEFAULT NULL,
                            `tipo_personal` int(11) DEFAULT NULL,
                            `tipo_jornada` int(11) DEFAULT NULL,
                            `rotacion_turnos` int(11) DEFAULT NULL,
                            `experiencia_puesto_actual` int(11) DEFAULT NULL,
                            `experiencia_laboral` int(11) DEFAULT NULL,
                            `empleados_id` int(11) DEFAULT NULL,
                            `informacion_validada` int(11) NOT NULL,
                            `correo` varchar(65) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma1_idx` (`sexo`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma2_idx` (`edad`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma3_idx` (`estado_civil`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma4_idx` (`nivel_estudios`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma5_idx` (`tipo_puesto`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma6_idx` (`tipo_contratacion`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma7_idx` (`tipo_personal`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma8_idx` (`tipo_jornada`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma9_idx` (`rotacion_turnos`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma10_idx` (`experiencia_puesto_actual`),
                            KEY `fk_seleccionados_cuestionario_catalogos_preguntas_norma11_idx` (`experiencia_laboral`),
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma1` FOREIGN KEY (`sexo`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma10` FOREIGN KEY (`experiencia_puesto_actual`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma11` FOREIGN KEY (`experiencia_laboral`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma2` FOREIGN KEY (`edad`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma3` FOREIGN KEY (`estado_civil`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma4` FOREIGN KEY (`nivel_estudios`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma5` FOREIGN KEY (`tipo_puesto`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma6` FOREIGN KEY (`tipo_contratacion`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma7` FOREIGN KEY (`tipo_personal`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma8` FOREIGN KEY (`tipo_jornada`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_seleccionados_cuestionario_catalogos_preguntas_norma9` FOREIGN KEY (`rotacion_turnos`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "interpretaciones":
                    try {
                        $tabla_desa = 'interpretaciones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `interpretacion` varchar(700) DEFAULT NULL,
                            `tipo_grafica` int(11) DEFAULT NULL,
                            `imagen` varchar(100) DEFAULT NULL,
                            `idperiodo_implementacion` int(11) NOT NULL,
                            `idcatalogo_norma` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_interpretaciones_periodos_implementacion1_idx` (`idperiodo_implementacion`),
                            KEY `fk_interpretaciones_catalogos_norma1_idx` (`idcatalogo_norma`),
                            CONSTRAINT `fk_interpretaciones_catalogos_norma1` FOREIGN KEY (`idcatalogo_norma`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_interpretaciones_periodos_implementacion1` FOREIGN KEY (`idperiodo_implementacion`) REFERENCES `periodos_implementacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "involucrados_audiencias":
                    try {
                        $tabla_desa = 'involucrados_audiencias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre` varchar(45) DEFAULT NULL,
                            `domicilio` varchar(100) DEFAULT NULL,
                            `estado` varchar(45) DEFAULT NULL,
                            `id_audiencia` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_involucrados_audiencias_demandas_audiencias1_idx` (`id_audiencia`),
                            CONSTRAINT `fk_involucrados_audiencias_demandas_audiencias1` FOREIGN KEY (`id_audiencia`) REFERENCES `demandas_audiencias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "involucrados_demandas":
                    try {
                        $tabla_desa = 'involucrados_demandas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `otro_involucrado` varchar(75) DEFAULT NULL,
                            `tipo_involucrado` int(11) NOT NULL,
                            `id_demanda_juridico` int(11) NOT NULL,
                            `id_involucrado` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_involucrados_demandas_demandas_juridico1_idx` (`id_demanda_juridico`),
                            CONSTRAINT `fk_involucrados_demandas_demandas_juridico1` FOREIGN KEY (`id_demanda_juridico`) REFERENCES `demandas_juridico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "kit_baja_campos":
                    try {
                        $tabla_desa = 'kit_baja_campos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre_campo` varchar(255) NOT NULL DEFAULT '0',
                            `alias` varchar(255) NOT NULL DEFAULT '0',
                            `obligatorio` tinyint(4) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "kit_baja_info":
                    try {
                        $tabla_desa = 'kit_baja_info';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `nombre_campo` int(11) NOT NULL,
                            `archivo` varchar(100) DEFAULT NULL,
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*TODO checar su eliminacion*/
                case "kpis":
                    try {
                        $tabla_desa = 'kpis';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `kpi` varchar(100) NOT NULL,
                            `criterios` varchar(100) NOT NULL,
                            `calificacion` int(11) NOT NULL,
                            `comentarios` text NOT NULL,
                            KEY `id_empleado` (`id_empleado`),
                            KEY `id` (`id`),
                            CONSTRAINT `KPIs_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `KPIs_ibfk_2` FOREIGN KEY (`id`) REFERENCES `evaluacion_desempeno` (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "logs":
                    try {
                        $tabla_desa = 'logs';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `usuario` text NOT NULL,
                            `evento` text NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`)
                        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "log_incidencias":
                    try {
                        $tabla_desa = 'log_incidencias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `fecha` date NOT NULL,
                            `tipo` varchar(90) NOT NULL,
                            `ejecutivo` varchar(200) NOT NULL,
                            `descripcion` text,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            CONSTRAINT `log_incidencias_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                        )ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "modificaciones_salario":
                    try {
                        $tabla_desa = 'modificaciones_salario';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `salario_anterior` varchar(100) NOT NULL,
                            `salario_nuevo` varchar(100) NOT NULL,
                            `salario_integrado_anterior` varchar(100) NOT NULL,
                            `salario_integrado_nuevo` varchar(100) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`)
                        )ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "modificaciones_sueldo":
                    try {
                        $tabla_desa = 'modificaciones_sueldo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `sueldo_anterior` varchar(100) NOT NULL,
                            `sueldo_nuevo` varchar(100) NOT NULL,
                            `sueldo_real_anterior` varchar(100) NOT NULL,
                            `sueldo_real_nuevo` varchar(100) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`),
                            CONSTRAINT `modifSueldo_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "modificaciones_salario":
                    try {
                        $tabla_desa = 'modificaciones_salario';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `salario_anterior` varchar(100) NOT NULL,
                            `salario_nuevo` varchar(100) NOT NULL,
                            `salario_integrado_anterior` varchar(100) NOT NULL,
                            `salario_integrado_nuevo` varchar(100) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "parametros":
                    try {
                        $tabla_desa = 'parametros';

                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `uma` varchar(40) NOT NULL,
                            `cuota_fija` varchar(29) NOT NULL,
                            `excedente_patro` varchar(30) NOT NULL,
                            `excedente_obrera` varchar(30) NOT NULL,
                            `prestaciones_patronal` varchar(29) NOT NULL,
                            `prestaciones_obrera` varchar(30) NOT NULL,
                            `gastos_medi_patronal` varchar(30) NOT NULL,
                            `gastos_medi_obrera` varchar(30) NOT NULL,
                            `riesgo_trabajo` varchar(30) NOT NULL,
                            `invalidez_patronal` varchar(30) NOT NULL,
                            `invalidez_obrera` varchar(30) NOT NULL,
                            `guarderia_presta_social` varchar(30) NOT NULL,
                            `porcentaje_honorarios` varchar(10) NOT NULL,
                            `porcentaje_nomina` varchar(10) NOT NULL,
                            `comision_variable` varchar(10) NOT NULL,
                            `ejercicio` varchar(20) NOT NULL,
                            `provision_valor` varchar(20) NOT NULL,
                            `provision_porcentaje` varchar(20) NOT NULL,
                            `comision_mismo_dia` varchar(50) NOT NULL,
                            `anticipo` varchar(50) NOT NULL,
                            `valor_prestacion_extra` varchar(50) NOT NULL,
                            `provision_obrero` varchar(20) NOT NULL,
                            `concepto_facturacion` varchar(90) DEFAULT NULL,
                            `salario_minimo` varchar(40) NOT NULL,
                            `salario_maximo` varchar(40) NOT NULL,
                            `logo_empresa_cliente` varchar(200) DEFAULT NULL,
                            `logo_empresa_emisora` varchar(200) DEFAULT NULL,
                            `parametria` varchar(1) NOT NULL DEFAULT '0',
                            `tipo_empleado` varchar(50) NOT NULL,
                            `dias_aviso_contrato` varchar(20) NOT NULL,
                            `tipo_nomina` varchar(30) NOT NULL,
                            `autoenumerar_empleado` varchar(20) NOT NULL,
                            `editar_conceptos` int(11) NOT NULL,
                            `logo_empresa_cliente_crede` varchar(200) DEFAULT NULL,
                            `logo_empresa_emisora_crede` varchar(200) DEFAULT NULL,
                            `biometrico` varchar(1) NOT NULL DEFAULT '0',
                            `region` varchar(20) DEFAULT NULL,
                            `iva` varchar(20) NOT NULL DEFAULT '0.16',
                            `provision_aguinaldo` varchar(20) DEFAULT '0',
                            `provision_prima_vacacional` varchar(20) DEFAULT '0',
                            `app` tinyint(1) DEFAULT '0',
                            `rvc_patronal_obrero` int(11) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);

                        DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();

                        $stm = "INSERT INTO ".$base.".".$tabla_desa." (
                            `id`,
                            `uma`,
                            `cuota_fija`,
                            `excedente_patro`,
                            `excedente_obrera`,
                            `prestaciones_patronal`,
                            `prestaciones_obrera`,
                            `gastos_medi_patronal`,
                            `gastos_medi_obrera`,
                            `riesgo_trabajo`,
                            `invalidez_patronal`,
                            `invalidez_obrera`,
                            `guarderia_presta_social`,
                            `tipo_nomina`,
                            `autoenumerar_empleado`)
                                VALUES
                            ('','0','0','0','0','0','0','0','0','0','0','0','0','Sindical','1')";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "periodos_implementacion":
                    try {
                        $tabla_desa = 'periodos_implementacion';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_inicio` datetime NOT NULL,
                            `fecha_fin` datetime NOT NULL,
                            `create_at` datetime DEFAULT NULL,
                            `update_at` datetime DEFAULT NULL,
                            `sede` int(11) DEFAULT NULL,
                            `razon_social` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_periodos_implementacion_sede1_idx` (`sede`),
                            KEY `fk_periodos_implementacion_razon_social1_idx` (`razon_social`),
                            CONSTRAINT `fk_periodos_implementacion_razon_social1` FOREIGN KEY (`razon_social`) REFERENCES `razon_social` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "periodos_nomina":
                    try {
                        $tabla_desa = 'periodos_nomina';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `nombre_periodo` text NOT NULL,
                            `numero_periodo` int(11) NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            `fecha_inicial_periodo` date NOT NULL,
                            `fecha_final_periodo` date NOT NULL,
                            `ejercicio` text NOT NULL,
                            `activo` int(11) NOT NULL DEFAULT '0',
                            `fecha_pago` date NOT NULL,
                            `fecha_apertura_periodo` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL,
                            `mes` int(11) NOT NULL,
                            `bimestre` int(11) NOT NULL,
                            `dias_periodo` int(11) NOT NULL,
                            `revocado` varchar(1) NOT NULL DEFAULT '0',
                            `motivo_revoca` text,
                            `especial` int(11) NOT NULL DEFAULT '0',
                            `enviar_avisos` varchar(1) DEFAULT '0',
                            `cadena_empleados` text,
                            `aux_agui` int(11) NOT NULL DEFAULT '0',
                            `aux_prima_vacacional` int(11) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "periodos_norma":
                    try {
                        $tabla_desa = 'periodos_norma';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_inicio` datetime DEFAULT NULL,
                            `fecha_fin` datetime DEFAULT NULL,
                            `fecha_fin_expansion` datetime DEFAULT NULL,
                            `estatus` varchar(45) DEFAULT NULL,
                            `created_at` datetime DEFAULT NULL,
                            `updated_at` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "permisos":
                    try {
                        $tabla_desa = 'permisos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_usuario` mediumint(9) NOT NULL,
                            `abrir_nomina` tinyint(3) unsigned DEFAULT '0',
                            `abrir_periodo_nomina` tinyint(3) unsigned DEFAULT '0',
                            `acumulados_clientes` tinyint(3) unsigned DEFAULT '0',
                            `admin_sss` tinyint(3) unsigned DEFAULT '0',
                            `aguinaldo` tinyint(3) unsigned DEFAULT '0',
                            `anexos_sss` tinyint(3) unsigned DEFAULT '0',
                            `apertura` tinyint(3) unsigned DEFAULT '0',
                            `asistencia` tinyint(3) unsigned DEFAULT '0',
                            `asistencia_periodos` tinyint(3) unsigned DEFAULT '0',
                            `avisos_imss` tinyint(3) unsigned DEFAULT '0',
                            `avisos_rh` tinyint(3) unsigned DEFAULT '0',
                            `bajas` tinyint(3) unsigned DEFAULT '0',
                            `biometricos` tinyint(3) unsigned DEFAULT '0',
                            `biometricos_confg` tinyint(3) unsigned DEFAULT '0',
                            `calendario` tinyint(3) unsigned DEFAULT '0',
                            `carga_acuse` tinyint(3) unsigned DEFAULT '0',
                            `catalogos` tinyint(3) unsigned DEFAULT '0',
                            `categorias` tinyint(3) unsigned DEFAULT '0',
                            `categorias_especiales` tinyint(3) unsigned DEFAULT '0',
                            `cierre_periodo` tinyint(3) unsigned DEFAULT '0',
                            `clientes` tinyint(3) unsigned DEFAULT '0',
                            `conceptos_nomina` tinyint(3) unsigned DEFAULT '0',
                            `conceptos_nomina_admin` tinyint(3) unsigned DEFAULT '0',
                            `config_panel` tinyint(3) unsigned DEFAULT '0',
                            `configuracion_empresa` tinyint(3) unsigned DEFAULT '0',
                            `confirma_nomina` tinyint(3) unsigned DEFAULT '0',
                            `confirmacion_prenomina` tinyint(3) unsigned DEFAULT '0',
                            `confirmacion_estatal` tinyint(3) unsigned DEFAULT '0',
                            `confirmacion_gerente` tinyint(3) unsigned DEFAULT '0',
                            `confronta_eba` tinyint(3) unsigned DEFAULT '0',
                            `confronta_sua` tinyint(3) unsigned DEFAULT '0',
                            `contabilidad` tinyint(3) unsigned DEFAULT '0',
                            `contratos` tinyint(3) DEFAULT '0',
                            `control_polizas` tinyint(3) unsigned DEFAULT '0',
                            `cotizador` tinyint(3) unsigned DEFAULT '0',
                            `credencial` tinyint(3) unsigned DEFAULT '0',
                            `ctrl_fac` tinyint(3) unsigned DEFAULT '0',
                            `ctrl_fact_sss` tinyint(3) unsigned DEFAULT '0',
                            `cuenta_banco` tinyint(3) unsigned DEFAULT '0',
                            `demandas` tinyint(3) unsigned DEFAULT '0',
                            `departamentos` tinyint(3) unsigned DEFAULT '0',
                            `dias_imss` tinyint(3) unsigned DEFAULT '0',
                            `dispersion_bancaria` tinyint(3) unsigned DEFAULT '0',
                            `dispersion_sss` tinyint(3) unsigned DEFAULT '0',
                            `doc_empleados` tinyint(3) unsigned DEFAULT '0',
                            `edicion_masiva` tinyint(3) unsigned DEFAULT '0',
                            `editar_expediente` tinyint(3) unsigned NOT NULL DEFAULT '0',
                            `ejercicio` tinyint(3) unsigned DEFAULT '0',
                            `empleados` tinyint(3) unsigned DEFAULT '0',
                            `empleado_alta` tinyint(3) unsigned DEFAULT '0',
                            `empleado_contrato` tinyint(3) unsigned DEFAULT '0',
                            `empleado_editar` tinyint(3) unsigned DEFAULT '0',
                            `empleado_eliminar` tinyint(3) unsigned DEFAULT '0',
                            `empleado_exportar` tinyint(3) unsigned DEFAULT '0',
                            `empleado_importar` tinyint(3) unsigned DEFAULT '0',
                            `empleado_percep_deduc` tinyint(3) unsigned DEFAULT '0',
                            `empresas` tinyint(3) unsigned DEFAULT '0',
                            `empresas_emisoras` tinyint(3) unsigned DEFAULT '0',
                            `especial` tinyint(3) unsigned DEFAULT '0',
                            `estadisticas_nomina` tinyint(3) unsigned DEFAULT '0',
                            `eva_desempeno` tinyint(3) unsigned DEFAULT '0',
                            `eventos` tinyint(3) unsigned DEFAULT '0',
                            `facturador` tinyint(3) unsigned DEFAULT '0',
                            `factura_sss` tinyint(3) unsigned DEFAULT '0',
                            `fecha_creacion` datetime DEFAULT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            `file_admin` tinyint(3) unsigned DEFAULT '0',
                            `finiquitos` tinyint(3) unsigned DEFAULT '0',
                            `horarios` tinyint(3) unsigned DEFAULT '0',
                            `impuestos` tinyint(3) unsigned DEFAULT '0',
                            `juridico` tinyint(3) unsigned DEFAULT '0',
                            `nomina_acumulados` tinyint(3) unsigned DEFAULT '0',
                            `nomina_periodo` tinyint(3) unsigned DEFAULT '0',
                            `nominas_cuotas` tinyint(3) unsigned DEFAULT '0',
                            `organigrama` tinyint(3) unsigned DEFAULT '0',
                            `panel_maestro` tinyint(3) unsigned DEFAULT '0',
                            `periodos_nomina` tinyint(3) unsigned DEFAULT '0',
                            `permisosh` tinyint(3) unsigned DEFAULT '0',
                            `prenomina` tinyint(3) unsigned DEFAULT '0',
                            `prestaciones` tinyint(3) unsigned DEFAULT '0',
                            `prestaciones_extras` tinyint(3) unsigned DEFAULT '0',
                            `prestamos` tinyint(3) unsigned DEFAULT '0',
                            `procesos` tinyint(3) unsigned DEFAULT '0',
                            `puestos` tinyint(3) unsigned DEFAULT '0',
                            `recibos_nomina` tinyint(3) unsigned DEFAULT '0',
                            `registro_incapacidades` tinyint(3) unsigned DEFAULT '0',
                            `reingresos` tinyint(3) unsigned DEFAULT '0',
                            `reporteador` tinyint(3) unsigned DEFAULT '0',
                            `reporte_aguinaldo` tinyint(3) unsigned DEFAULT '0',
                            `reporte_comparativo` tinyint(3) unsigned DEFAULT '0',
                            `reporte_finiquitos` tinyint(3) unsigned DEFAULT '0',
                            `reporte_rh` tinyint(3) unsigned DEFAULT '0',
                            `reportes` tinyint(3) unsigned DEFAULT '0',
                            `rotacion_personal` tinyint(3) unsigned DEFAULT '0',
                            `soporte` tinyint(3) unsigned DEFAULT '0',
                            `subsidios` tinyint(3) unsigned DEFAULT '0',
                            `tabla_ispt` tinyint(3) unsigned DEFAULT '0',
                            `timbrado_aguinaldo` tinyint(3) unsigned DEFAULT '0',
                            `timbrado_finiquito` tinyint(3) unsigned DEFAULT '0',
                            `timbrado_nomina` tinyint(3) unsigned DEFAULT '0',
                            `usuarios` tinyint(3) unsigned DEFAULT '0',
                            `utilerias` tinyint(3) unsigned DEFAULT '0',
                            `validar_expediente` tinyint(3) unsigned DEFAULT '0',
                            `ver_acuse` tinyint(3) unsigned DEFAULT '0',
                            `ver_expediente` tinyint(3) unsigned DEFAULT '0',
                            `ver_reingresos` tinyint(3) unsigned DEFAULT '0',
                            `vigencia_contratos` tinyint(3) unsigned DEFAULT '0',
                            `formularios` tinyint(3) unsigned DEFAULT '0',
                            `encuesta_salida` tinyint(3) DEFAULT '0',
                            `norma035` tinyint(3) DEFAULT '0',
                            `vcard` tinyint(3) DEFAULT '0',
                            `parametria` tinyint(3) DEFAULT '0',
                            `tabla_isr` tinyint(3) DEFAULT '0',
                            `tabla_subsidios` tinyint(3) DEFAULT '0',
                            `puestos_empresa` tinyint(3) DEFAULT '0',
                            `departamentos_empresa` tinyint(3) DEFAULT '0',
                            `tipo_prestaciones` tinyint(3) DEFAULT '0',
                            `conf_kit_baja` tinyint(3) DEFAULT '0',
                            `procesos_calculo` tinyint(3) DEFAULT '0',
                            `captura_incidencias` tinyint(3) DEFAULT '0',
                            `timbrado_asimilados` tinyint(3) DEFAULT '0',
                            `control_empleados` tinyint(3) DEFAULT '0',
                            `cuentas_bancarias` tinyint(3) DEFAULT '0',
                            `kit_baja` tinyint(3) DEFAULT '0',
                            `imss` tinyint(3) DEFAULT '0',
                            `movi_afiliatorios` tinyint(3) DEFAULT '0',
                            `consultas` tinyint(3) DEFAULT '0',
                            `control_credenciales` tinyint(3) DEFAULT '0',
                            `reporte_asistencias` tinyint(3) DEFAULT '0',
                            `reporte_acumulados_nomina` tinyint(3) DEFAULT '0',
                            `docu_empleados` tinyint(3) DEFAULT '0',
                            `recibos_asimilados` tinyint(3) DEFAULT '0',
                            `reporte_movi_personal` tinyint(3) DEFAULT '0',
                            `indice_rotacion_personal` tinyint(3) DEFAULT '0',
                            `reporte_nominas_periodo` tinyint(3) DEFAULT '0',
                            `conf_formularios` tinyint(3) DEFAULT '0',
                            `herramientas` tinyint(3) DEFAULT '0',
                            `horarios_empleados` tinyint(3) DEFAULT '0',
                            `solicitud_beneficiarios` tinyint(3) DEFAULT '0',
                            `categoria_activos` tinyint(3) DEFAULT '0',
                            `asignar_activos` tinyint(3) DEFAULT '0',
                            `calendario_demandas` tinyint(3) DEFAULT '0',
                            `sistema` tinyint(3) DEFAULT '0',
                            `usuarios_sistema` tinyint(3) DEFAULT '0',
                            `usuarios_timbrado` tinyint(3) DEFAULT '0',
                            `contratos_hrsystem` tinyint(3) DEFAULT '0',
                            `empresas_receptora` tinyint(3) DEFAULT '0',
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `id_usuario` (`id_usuario`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='0 - sin permisos\r\n1 - todos los permisos\r\n2 - solo lectura';";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "plantilla":
                    try {
                        $tabla_desa = 'plantilla';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(10) NOT NULL AUTO_INCREMENT,
                            `user` varchar(200) NOT NULL,
                            `nombre` text NOT NULL,
                            `fecha_actu` datetime NOT NULL,
                            `tabla` varchar(20) NOT NULL,
                            `tmp` varchar(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`,`user`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "plantilla_detalle":
                    try {
                        $tabla_desa = 'plantilla_detalle';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(10) NOT NULL AUTO_INCREMENT,
                            `id_detalle` int(15) NOT NULL,
                            `tipo` varchar(10) NOT NULL,
                            `id_reporte` varchar(20) DEFAULT NULL,
                            `id_mapa` varchar(20) NOT NULL,
                            `filtro` varchar(30) DEFAULT NULL,
                            `valor` text,
                            `valor2` text,
                            `orden` varchar(20) DEFAULT NULL,
                            `estatus` varchar(1) NOT NULL,
                            PRIMARY KEY (`id`,`id_detalle`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "preguntas":
                    try {
                        $tabla_desa = 'preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `pregunta` varchar(255) NOT NULL,
                            `pregunta_simple` varchar(255) DEFAULT NULL,
                            `tipo_respuesta` int(11) DEFAULT NULL,
                            `idcategoria` int(11) DEFAULT NULL,
                            `iddominio` int(11) DEFAULT NULL,
                            PRIMARY KEY (`id`),
                            KEY `fk_preguntas_catalogos_preguntas_norma1_idx` (`idcategoria`),
                            KEY `fk_preguntas_catalogos_preguntas_norma2_idx` (`iddominio`),
                            CONSTRAINT `fk_preguntas_catalogos_preguntas_norma1` FOREIGN KEY (`idcategoria`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                            CONSTRAINT `fk_preguntas_catalogos_preguntas_norma2` FOREIGN KEY (`iddominio`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "prestaciones":
                    try {
                        $tabla_desa = 'prestaciones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_categoria` int(11) NOT NULL,
                            `estatus` int(11) NOT NULL DEFAULT '0',
                            `antiguedad` text NOT NULL,
                            `vacaciones` text NOT NULL,
                            `prima_vacacional` text NOT NULL,
                            `aguinaldo` text NOT NULL,
                            `factor_integracion` varchar(50) NOT NULL DEFAULT '0',
                            `bono_aguinaldo` int(11) NOT NULL DEFAULT '0',
                            `bono_vacaciones` int(11) NOT NULL,
                            `bono_prima_vacacional` varchar(20) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (`id`),
                            KEY `prestaciones_ibfk_1_idx` (`id_categoria`),
                            CONSTRAINT `prestaciones_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "prestaciones_extras":
                    try {
                        $tabla_desa = 'prestaciones_extras';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `fecha_edicion` datetime NOT NULL,
                                `estatus` tinyint(1) NOT NULL DEFAULT '0',
                                `num_certificado` varchar(20) NOT NULL,
                                `valor_seguro_GM` varchar(20) NOT NULL,
                                `valor_plan_espejo` varchar(20) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `id_empleado` (`id_empleado`),
                                CONSTRAINT `prestaciones_extras_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "provisiones_facturacion":
                    try {

                        $tabla_desa = 'provisiones_facturacion';

                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_periodo` int(11) NOT NULL,
                                `id_empleado` int(11) NOT NULL,
                                `ejercicio` varchar(20) NOT NULL,
                                `total_aguinaldo` varchar(200) NOT NULL,
                                `total_prima_vacacional` varchar(200) NOT NULL,
                                `dias_aguinaldo` varchar(200) NOT NULL,
                                `dias_prima_vacaciona` varchar(200) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `idempleado` (`id_empleado`),
                                KEY `idperiodo` (`id_periodo`),
                                CONSTRAINT `provisionesFacturacion_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                CONSTRAINT `provisionesFacturacion_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "puestos":
                    try {
                        $tabla_desa = 'puestos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `puesto` varchar(200) NOT NULL,
                            `estatus` int(2) NOT NULL DEFAULT '0',
                            `jerarquia` varchar(20) NOT NULL,
                            `dependencia` varchar(10) DEFAULT '0',
                            `actividades` tinytext,
                            `fecha_creacion` datetime NOT NULL ,
                            `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            PRIMARY KEY (`id`)
                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);

                        DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();

                        $stm = "INSERT INTO ".$base.".".$tabla_desa." (
                                `id`,
                                `puesto`,
                                `estatus`,
                                `jerarquia`,
                                `dependencia`,
                                `actividades`,
                                `fecha_creacion`
                                )
                                    VALUES
                                ('','General','1','0','0','- empleado General',now())";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "razon_social":
                    try {
                        $tabla_desa = 'razon_social';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `razon_social` varchar(45) NOT NULL,
                                `estatus` int(11) NOT NULL,
                                `emisora_id` int(11) DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "respuestas_cuestionarios":
                    try {
                        $tabla_desa = 'respuestas_cuestionarios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `idcuestionario_trabajador` int(11) NOT NULL,
                                `idpregunta` int(11) NOT NULL,
                                `valor` int(11) DEFAULT NULL,
                                `created_at` datetime DEFAULT NULL,
                                `updated_at` datetime DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                KEY `fk_respuestas_cuestionarios_cuestionarios_trabajadores1_idx` (`idcuestionario_trabajador`),
                                KEY `fk_respuestas_cuestionarios_preguntas1_idx` (`idpregunta`),
                                CONSTRAINT `fk_respuestas_cuestionarios_cuestionarios_trabajadores1` FOREIGN KEY (`idcuestionario_trabajador`) REFERENCES `cuestionarios_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                                CONSTRAINT `fk_respuestas_cuestionarios_preguntas1` FOREIGN KEY (`idpregunta`) REFERENCES `preguntas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                ) ENGINE=InnoDB AUTO_INCREMENT=2034 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "rutina_valores":
                    try {
                        $tabla_desa = 'rutina_valores';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_rutina` int(11) NOT NULL,
                                `id_concepto` int(11) DEFAULT NULL,
                                `tipo_concepto` int(11) DEFAULT NULL,
                                `nombre_concepto` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total` decimal(15,2) DEFAULT NULL,
                                `valor` decimal(15,2) DEFAULT NULL,
                                `exento` decimal(15,2) DEFAULT NULL,
                                `gravado` decimal(15,2) DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "sedes":
                    try {
                        $tabla_desa = 'sedes';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `nombre` varchar(30) NOT NULL,
                              `estatus` int(11) DEFAULT '1',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "saldo_nomina":
                    try {
                        $tabla_desa = 'saldo_nomina';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `id_concepto` int(11) NOT NULL,
                            `saldo` varchar(100) NOT NULL,
                            `valor_concepto` varchar(100) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "subsidios":
                    try {
                        $tabla_desa = 'subsidios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `ingreso_desde` varchar(50) NOT NULL,
                            `ingreso_hasta` varchar(50) NOT NULL,
                            `subsidio` varchar(50) NOT NULL,
                            `tipo_tabla` text NOT NULL,
                            `estatus` int(11) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime DEFAULT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "subsidios_b":
                    try {
                        $tabla_desa = 'subsidios_b';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `limite_inferior` varchar(50) NOT NULL,
                            `limite_inferior2` varchar(50) NOT NULL,
                            `limite_superior` varchar(50) NOT NULL,
                            `cuota_fija` varchar(40) NOT NULL,
                            `porcentaje_s_exce` varchar(45) NOT NULL,
                            `subsidio_empleado` varchar(45) NOT NULL,
                            `fecha_creacion` datetime NOT NULL,
                            `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `estatus` int(11) NOT NULL,
                            `tipo_tabla` text NOT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*TODO checar eliminacion*/
                case "tempora_suma_fini":
                    try {
                        $tabla_desa = 'tempora_suma_fini';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `monto` varchar(30) NOT NULL,
                              `id_empleado` int(11) NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `idempleado` (`id_empleado`),
                              CONSTRAINT `temporasumaFini_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado":
                    try {
                        $tabla_desa = 'timbrado';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_timbrado` datetime NULL,
                            `sello_sat` text NOT NULL,
                            `certificado_sat` text NOT NULL,
                            `sello_cfdi` text NOT NULL,
                            `folio_fiscal` text NOT NULL,
                            `xml_enviado` text NOT NULL,
                            `num_factura` text NOT NULL,
                            `respuesta_pac` text NOT NULL,
                            `cadena_original` text NOT NULL,
                            `file_pdf` text NOT NULL,
                            `file_xml` text NOT NULL,
                            `importe` varchar(40) NOT NULL,
                            `receptor` varchar(40) NOT NULL,
                            `emisor` varchar(40) NOT NULL,
                            `certificado_tim` varchar(40) NOT NULL,
                            `fecha_emision` varchar(40) NULL,
                            `num_dias_pagados` varchar(40) NOT NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            `mensaje_error` text NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            KEY `idperiodo` (`id_periodo`),
                            CONSTRAINT `timbrado_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `timbrado_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_aguinaldo":
                    try {
                        $tabla_desa = 'timbrado_aguinaldo';
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `ejercicio` int(11) NOT NULL,
                            `fecha_timbrado` datetime NOT NULL,
                            `sello_sat` text NOT NULL,
                            `certificado_sat` text NOT NULL,
                            `sello_cfdi` text NOT NULL,
                            `folio_fiscal` text NOT NULL,
                            `xml_enviado` longtext NOT NULL,
                            `num_factura` text NOT NULL,
                            `respuesta_pac` longtext NOT NULL,
                            `cadena_original` text NOT NULL,
                            `file_pdf` text NOT NULL,
                            `file_xml` text NOT NULL,
                            `importe` varchar(40) NOT NULL,
                            `receptor` varchar(40) NOT NULL,
                            `emisor` varchar(40) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `certificado_tim` varchar(40) NOT NULL,
                            `fecha_emision` varchar(40) NOT NULL,
                            `num_dias_pagados` varchar(40) NOT NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`),
                            KEY `id_periodo` (`id_periodo`),
                            CONSTRAINT `timbradoaguinaldo_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `timbradoaguinaldo_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_cancelaciones":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_cancelacion` datetime NOT NULL,
                            `request_cancel` text NOT NULL,
                            `response` text NOT NULL,
                            `xml_acuse_cancel` text NOT NULL,
                            `sello_sat` text NOT NULL,
                            `file_acuse` text NOT NULL,
                            `file_soap` text NOT NULL,
                            `no_factura` varchar(20) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            KEY `idperiodo` (`id_periodo`),
                            CONSTRAINT `timbrado_cancelaciones_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `timbrado_cancelaciones_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_cancelaciones_aguinaldo":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_aguinaldo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `fecha_cancelacion` datetime NOT NULL,
                                `request_cancel` text NOT NULL,
                                `response` text NOT NULL,
                                `xml_acuse_cancel` text NOT NULL,
                                `sello_sat` text NOT NULL,
                                `file_acuse` text NOT NULL,
                                `file_soap` text NOT NULL,
                                `ejercicio` varchar(20) NOT NULL,
                                `no_factura` varchar(20) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `id_empleado` (`id_empleado`),
                                KEY `id_periodo` (`id_periodo`),
                                CONSTRAINT `timbradocancelacionesaguinaldo_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                CONSTRAINT `timbradocancelacionesaguinaldo_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_cancelaciones_factura":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_factura';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `emisora` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_cancelacion` datetime NOT NULL,
                            `request_cancel` text NOT NULL,
                            `response` text NOT NULL,
                            `xml_acuse_cancel` text NOT NULL,
                            `sello_sat` text NOT NULL,
                            `file_acuse` text NOT NULL,
                            `file_soap` text NOT NULL,
                            `no_factura` varchar(20) NOT NULL,
                            `deposito` varchar(1) DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `idperiodo` (`id_periodo`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_cancelaciones_facturador":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_facturador';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `emisora` int(11) NOT NULL,
                            `factura` int(11) NOT NULL,
                            `fecha_cancelacion` datetime NOT NULL,
                            `request_cancel` text NOT NULL,
                            `response` text NOT NULL,
                            `xml_acuse_cancel` text NOT NULL,
                            `sello_sat` text NOT NULL,
                            `file_acuse` text NOT NULL,
                            `file_soap` text NOT NULL,
                            `no_factura` varchar(20) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `Factura` (`factura`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_cancelaciones_finiquito":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_finiquito';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_cancelacion` datetime NOT NULL,
                            `request_cancel` text NOT NULL,
                            `response` text NOT NULL,
                            `xml_acuse_cancel` text NOT NULL,
                            `sello_sat` text NOT NULL,
                            `file_acuse` text NOT NULL,
                            `file_soap` text NOT NULL,
                            `ejercicio` varchar(20) NOT NULL,
                            `no_factura` varchar(20) NOT NULL,
                            PRIMARY KEY (`id`),
                            KEY `id_empleado` (`id_empleado`),
                            KEY `id_periodo` (`id_periodo`),
                            CONSTRAINT `timbradocancelacionesfiniquito_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `timbradocancelacionesfiniquito_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                          ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_factura":
                    try {
                        $tabla_desa = 'timbrado_factura';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_timbrado` datetime NULL,
                            `sello_sat` text NOT NULL,
                            `certificado_sat` text NOT NULL,
                            `sello_cfdi` text NOT NULL,
                            `folio_fiscal` text NOT NULL,
                            `xml_enviado` longtext NOT NULL,
                            `no_factura` text NOT NULL,
                            `respuesta_pac` longtext NOT NULL,
                            `cadena_original` text NOT NULL,
                            `file_pdf` text NOT NULL,
                            `file_xml` text NOT NULL,
                            `importe` varchar(40) NOT NULL,
                            `receptor` varchar(40) NOT NULL,
                            `emisor` varchar(40) NOT NULL,
                            `certificado_tim` varchar(40) NOT NULL,
                            `fecha_emision` varchar(40)  NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            `emisora` int(11) NOT NULL,
                            `deposito` varchar(1) DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `idperiodo` (`id_periodo`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_facturador":
                    try {
                        $tabla_desa = 'timbrado_facturador';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_timbrado` datetime NULL,
                            `sello_sat` text NOT NULL,
                            `certificado_sat` text NOT NULL,
                            `sello_cfdi` text NOT NULL,
                            `folio_fiscal` text NOT NULL,
                            `xml_enviado` longtext NOT NULL,
                            `no_factura` text NOT NULL,
                            `respuesta_pac` longtext NOT NULL,
                            `cadena_original` text NOT NULL,
                            `file_pdf` text NOT NULL,
                            `file_xml` text NOT NULL,
                            `importe` varchar(40) NOT NULL,
                            `receptor` varchar(40) NOT NULL,
                            `emisor` varchar(40) NOT NULL,
                            `certificado_tim` varchar(40) NOT NULL,
                            `fecha_emision` varchar(40) NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            `emisora` int(11) NOT NULL,
                            `factura` int(11) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "timbrado_finiquito":
                    try {
                        $tabla_desa = 'timbrado_finiquito';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `ejercicio` int(11) NOT NULL,
                                 `fecha_timbrado` datetime NULL,
                                 `sello_sat` text NOT NULL,
                                 `certificado_sat` text NOT NULL,
                                 `sello_cfdi` text NOT NULL,
                                 `folio_fiscal` text NOT NULL,
                                `xml_enviado` longtext NOT NULL,
                                 `no_factura` text NOT NULL,
                                 `respuesta_pac` longtext NOT NULL,
                                 `cadena_original` text NOT NULL,
                                 `file_pdf` text NOT NULL,
                                 `file_xml` text NOT NULL,
                                 `importe` varchar(40) NOT NULL,
                                 `receptor` varchar(40) NOT NULL,
                                 `emisor` varchar(40) NOT NULL,
                                 `id_periodo` int(11) NOT NULL,
                                 `certificado_tim` varchar(40) NOT NULL,
                                 `fecha_emision` varchar(40) NULL,
                                 `num_dias_pagados` varchar(40) NOT NULL,
                                 `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                                 PRIMARY KEY (`id`),
                                 KEY `id_empleado` (`id_empleado`),
                                 KEY `id_periodo` (`id_periodo`),
                                 CONSTRAINT `timbradofiniquito_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                 CONSTRAINT `timbradofiniquito_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                 )ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "vcards":
                    try {
                        $tabla_desa = 'vcards';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                              `idempresa` int(11) DEFAULT NULL,
                              `direccion` varchar(250) DEFAULT NULL,
                              `colorfndinp` varchar(100) DEFAULT NULL COMMENT 'color de fondo inicial',
                              `colorfndbtninp` varchar(100) DEFAULT NULL COMMENT 'color de fondo de botonera',
                              `coloricnbtninp` varchar(100) DEFAULT NULL COMMENT 'color de iconos de botonera',
                              `fndbodyvcardinp` varchar(100) DEFAULT NULL COMMENT 'color de fondo body vcard',
                              `recfondlistintinp` varchar(100) DEFAULT NULL COMMENT 'color de fondo recuadro lista interior',
                              `recletlistinitinp` varchar(100) DEFAULT NULL COMMENT 'color de fondo letra recuardo lista interior',
                              `reclistinticonsinp` varchar(100) DEFAULT NULL COMMENT 'color de fondo iconos lista interior',
                             `logo_empresa_empleado` varchar(250) DEFAULT NULL,
                              `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                              `updated_at` timestamp NULL DEFAULT NULL,
                              `deleted_at` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "vcards_info":
                    try {

                        $tabla_desa = 'vcards_info';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `idvcard` int(11) DEFAULT NULL,
                                `contacto` varchar(250) DEFAULT NULL,
                                `tipocontacto` int(50) DEFAULT NULL COMMENT '1 es los links de sitios web y 2 son los teléfonos de la empresa',
                                PRIMARY KEY (`id`),
                                KEY `fk_vcard` (`idvcard`),
                                CONSTRAINT `fk_vcard` FOREIGN KEY (`idvcard`) REFERENCES `vcards` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "registro_covid":
                    try {
                        $tabla_desa = 'registro_covid';

                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `fecha_inicio` datetime DEFAULT NULL,
                                `fecha_fin` datetime DEFAULT NULL,
                                `estatus` int(11) DEFAULT NULL,
                                `notas` text,
                                PRIMARY KEY (`id`),
                                KEY `fk_registro_covid_empleados1_idx` (`id_empleado`),
                                CONSTRAINT `fk_registro_covid_empleados1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "pendientes":
                    try {
                        $tabla_desa = 'pendientes';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `titulo` varchar(200) NOT NULL,
                                  `descripcion` text,
                                  `archivo` tinytext,
                                  `estatus` int(11) NOT NULL DEFAULT '0' COMMENT '0 nuevo,1 completado, 2 revision,3 eliminado',
                                  `fecha_creacion` datetime NOT NULL,
                                  `fecha_edicion` datetime NOT NULL,
                                   PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                            ";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                /*Last table added*/
                case "activos":
                    try {
                        $tabla_desa = 'activos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_categoria_activo` int(11) DEFAULT NULL,
                                `nombre` varchar(250) DEFAULT NULL,
                                `descripcion` mediumtext,
                                `comentarios` mediumtext,
                                `marca` varchar(50) DEFAULT NULL,
                                `modelo` varchar(50) DEFAULT NULL,
                                `nserie` varchar(50) DEFAULT NULL,
                                `estatus` int(11) DEFAULT NULL,
                                `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                `fecha_edicion` timestamp NULL DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "activos_archivos":
                    try {
                        $tabla_desa = 'activos_archivos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `id_activo` int(11) DEFAULT NULL COMMENT 'id de la tabla activos',
                              `nombre_archivo` varchar(50) DEFAULT NULL,
                              `file_archivo` varchar(250) DEFAULT NULL,
                              `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                              `fecha_edicion` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "activos_campos_extra":
                    try {
                        $tabla_desa = 'activos_campos_extra';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `id_activo` int(11) DEFAULT NULL COMMENT 'id relacion con tabla activos',
                                `nombre_label` varchar(50) DEFAULT NULL,
                                `valor` varchar(250) DEFAULT NULL,
                                `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                                `fecha_edicion` timestamp NULL DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "adic2018":
                    try {
                        $tabla_desa = 'adic2018';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "adic2019":
                    try {
                        $tabla_desa = 'adic2019';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "adic2020":
                    try {
                        $tabla_desa = 'adic2020';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "adic2021":
                    try {
                        $tabla_desa = 'adic2021';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_periodo` int(11) NOT NULL,
                                `id_empleado` int(11) NOT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "bitacora":
                    try {
                        $tabla_desa = 'bitacora';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(15) NOT NULL AUTO_INCREMENT,
                                    `usuario` text NOT NULL,
                                    `descripcion` text NOT NULL,
                                    `estatus` varchar(1) NOT NULL,
                                    `tipo` varchar(5) NOT NULL,
                                    `referencia` varchar(20) NOT NULL,
                                    `genero` text NOT NULL,
                                    `finalizo` varchar(200) DEFAULT NULL,
                                    `evento` varchar(20) NOT NULL,
                                    PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "categorias_activos":
                    try {
                        $tabla_desa = 'categorias_activos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `nombre_activo` varchar(250) DEFAULT NULL,
                                    `estatus` int(11) DEFAULT NULL,
                                    `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    `fecha_edicion` timestamp NULL DEFAULT NULL,
                                    PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "contratos":
                    try {
                        $tabla_desa = 'contratos';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `id_empleado` int(11) NOT NULL,
                                          `fecha_contrato` datetime NOT NULL,
                                          `fecha_vencimiento` datetime DEFAULT NULL,
                                          `estatus` int(11) NOT NULL,
                                          `numero_dias` int(11) NOT NULL,
                                          `contrato` varchar(200) NOT NULL,
                                          `fecha_creacion` datetime NOT NULL,
                                          `alerta` date DEFAULT NULL,
                                          `archivo` varchar(45) DEFAULT NULL,
                                          PRIMARY KEY (`id`),
                                          KEY `idempleado` (`id_empleado`),
                                          CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "credenciales":
                    try {
                        $tabla_desa = 'credenciales';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `id_empleado` int(11) NOT NULL,
                                          `repositorio` varchar(200) NOT NULL,
                                          `estatus` int(11) NOT NULL,
                                          PRIMARY KEY (`id`),
                                          KEY `idempleado` (`id_empleado`),
                                          CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "datos_facturacion2019":
                    try {
                        $tabla_desa = 'datos_facturacion2019';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `id_periodo` int(11) NOT NULL,
                                        `nomina` varchar(90) NOT NULL,
                                        `beneficio_sindical` varchar(90) NOT NULL,
                                        `anticipo` varchar(90) NOT NULL,
                                        `vacaciones` varchar(90) NOT NULL,
                                        `pago_prima_vaca` varchar(90) NOT NULL,
                                        `comision_mismo_dia` varchar(90) NOT NULL,
                                        `total_pago_nomina` varchar(90) NOT NULL,
                                        `porcentaje_honorarios` varchar(90) NOT NULL,
                                        `valores_honorarios` varchar(90) NOT NULL,
                                        `fecha_creacion` datetime NOT NULL,
                                        `ejercicio` varchar(90) NOT NULL,
                                        `costos_patronales` varchar(90) NOT NULL,
                                        `detalle_subtotal` varchar(90) NOT NULL,
                                        `detalle_iva` varchar(90) NOT NULL,
                                        `detalle_total` varchar(90) NOT NULL,
                                        `prestaciones_extras` varchar(90) NOT NULL,
                                        `cuota_fija` varchar(90) NOT NULL,
                                        `exc_cf` varchar(90) NOT NULL,
                                        `presta_dinero` varchar(90) NOT NULL,
                                        `gastos_medi_pensionados` varchar(90) NOT NULL,
                                        `riesgo_trabajo` varchar(90) NOT NULL,
                                        `invalidez_y_vida` varchar(90) NOT NULL,
                                        `guarderias_y_pre_sociales` varchar(90) NOT NULL,
                                        `cuotas_imss_retiro` varchar(90) NOT NULL,
                                        `cuotas_imss_censatiaV` varchar(90) NOT NULL,
                                        `cred_vivienda` varchar(90) NOT NULL,
                                        `porcentaje_errogaciones` varchar(90) NOT NULL,
                                        `valor_errogaciones` varchar(90) NOT NULL,
                                        `totalcostos_patronales` varchar(90) NOT NULL,
                                        `carga_social1_1` varchar(90) NOT NULL,
                                        `carga_social1_2` varchar(90) NOT NULL,
                                        `carga_social1_3` varchar(90) NOT NULL,
                                        `carga_social1_4` varchar(90) NOT NULL,
                                        `carga_social1_5` varchar(90) NOT NULL,
                                        `cadena_emisoras` varchar(90) NOT NULL,
                                        `porcentajes_nomina` varchar(90) NOT NULL,
                                        `suministro_per1_1` varchar(90) NOT NULL,
                                        `suministro_per1_2` varchar(90) NOT NULL,
                                        `suministro_per1_3` varchar(90) NOT NULL,
                                        `suministro_per1_4` varchar(90) NOT NULL,
                                        `suministro_per1_5` varchar(90) NOT NULL,
                                        `porcentaje_comisionV` varchar(90) NOT NULL,
                                        `subtotal_depo1_1` varchar(90) NOT NULL,
                                        `subtotal_depo1_2` varchar(90) NOT NULL,
                                        `subtotal_depo1_3` varchar(90) NOT NULL,
                                        `subtotal_depo1_4` varchar(90) NOT NULL,
                                        `subtotal_depo1_5` varchar(90) NOT NULL,
                                        `iva_depo1_1` varchar(90) NOT NULL,
                                        `iva_depo1_2` varchar(90) NOT NULL,
                                        `iva_depo1_3` varchar(90) NOT NULL,
                                        `iva_depo1_4` varchar(90) NOT NULL,
                                        `iva_depo1_5` varchar(90) NOT NULL,
                                        `total_depo1_1` varchar(90) NOT NULL,
                                        `total_depo1_2` varchar(90) NOT NULL,
                                        `total_depo1_3` varchar(90) NOT NULL,
                                        `total_depo1_4` varchar(90) NOT NULL,
                                        `total_depo1_5` varchar(90) NOT NULL,
                                        `concepto` varchar(90) NOT NULL,
                                        `valor_concepto` varchar(90) NOT NULL,
                                        `subtotal_depo2` varchar(90) NOT NULL,
                                        `iva_depo2` varchar(90) NOT NULL,
                                        `total_depo2` varchar(90) NOT NULL,
                                        `valor_sobre_nomina1_1` varchar(90) NOT NULL,
                                        `valor_sobre_nomina1_2` varchar(90) NOT NULL,
                                        `valor_sobre_nomina1_3` varchar(90) NOT NULL,
                                        `valor_sobre_nomina1_4` varchar(90) NOT NULL,
                                        `valor_sobre_nomina1_5` varchar(90) NOT NULL,
                                        `valor_comision_variable1_1` varchar(90) NOT NULL,
                                        `valor_comision_variable1_2` varchar(90) NOT NULL,
                                        `valor_comision_variable1_3` varchar(90) NOT NULL,
                                        `valor_comision_variable1_4` varchar(90) NOT NULL,
                                        `valor_comision_variable1_5` varchar(90) NOT NULL,
                                        PRIMARY KEY (`id`),
                                        KEY `idperiodo` (`id_periodo`),
                                        CONSTRAINT `datos_facturacion2019_ibfk_1` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "datos_facturacion2020":
                    try {
                        $tabla_desa = 'datos_facturacion2020';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                        `id_periodo` int(11) NOT NULL,
                                        `nomina` varchar(25) DEFAULT NULL,
                                        `beneficio_sindical` varchar(25) DEFAULT NULL,
                                        `anticipo` varchar(25) DEFAULT NULL,
                                        `vacaciones` varchar(25) DEFAULT NULL,
                                        `pago_prima_vaca` varchar(25) DEFAULT NULL,
                                        `comision_mismo_dia` varchar(25) DEFAULT NULL,
                                        `total_pago_nomina` varchar(25) DEFAULT NULL,
                                        `porcentaje_honorarios` varchar(25) DEFAULT NULL,
                                        `valores_honorarios` varchar(25) DEFAULT NULL,
                                        `ejercicio` varchar(25) DEFAULT NULL,
                                        `costos_patronales` varchar(25) DEFAULT NULL,
                                        `detalle_subtotal` varchar(25) DEFAULT NULL,
                                        `detalle_iva` varchar(25) DEFAULT NULL,
                                        `detalle_total` varchar(25) DEFAULT NULL,
                                        `prestaciones_extras` varchar(25) DEFAULT NULL,
                                        `cuota_fija` varchar(25) DEFAULT NULL,
                                        `exc_cf` varchar(25) DEFAULT NULL,
                                        `presta_dinero` varchar(25) DEFAULT NULL,
                                        `gastos_medi_pensionados` varchar(25) DEFAULT NULL,
                                        `riesgo_trabajo` varchar(25) DEFAULT NULL,
                                        `invalidez_y_vida` varchar(25) DEFAULT NULL,
                                        `guarderias_y_pre_sociales` varchar(25) DEFAULT NULL,
                                        `cuotas_imss_retiro` varchar(25) DEFAULT NULL,
                                        `cuotas_imss_censatiaV` varchar(25) DEFAULT NULL,
                                        `cred_vivienda` varchar(25) DEFAULT NULL,
                                        `porcentaje_errogaciones` varchar(25) DEFAULT NULL,
                                        `valor_errogaciones` varchar(25) DEFAULT NULL,
                                        `totalcostos_patronales` varchar(25) DEFAULT NULL,
                                        `carga_social1_1` varchar(25) DEFAULT NULL,
                                        `carga_social1_2` varchar(25) DEFAULT NULL,
                                        `carga_social1_3` varchar(25) DEFAULT NULL,
                                        `carga_social1_4` varchar(25) DEFAULT NULL,
                                        `carga_social1_5` varchar(25) DEFAULT NULL,
                                        `cadena_emisoras` varchar(25) DEFAULT NULL,
                                        `porcentajes_nomina` varchar(25) DEFAULT NULL,
                                        `suministro_per1_1` varchar(25) DEFAULT NULL,
                                        `suministro_per1_2` varchar(25) DEFAULT NULL,
                                        `suministro_per1_3` varchar(25) DEFAULT NULL,
                                        `suministro_per1_4` varchar(25) DEFAULT NULL,
                                        `suministro_per1_5` varchar(25) DEFAULT NULL,
                                        `porcentaje_comisionV` varchar(25) DEFAULT NULL,
                                        `subtotal_depo1_1` varchar(25) DEFAULT NULL,
                                        `subtotal_depo1_2` varchar(25) DEFAULT NULL,
                                        `subtotal_depo1_3` varchar(25) DEFAULT NULL,
                                        `subtotal_depo1_4` varchar(25) DEFAULT NULL,
                                        `subtotal_depo1_5` varchar(25) DEFAULT NULL,
                                        `iva_depo1_1` varchar(25) DEFAULT NULL,
                                        `iva_depo1_2` varchar(25) DEFAULT NULL,
                                        `iva_depo1_3` varchar(25) DEFAULT NULL,
                                        `iva_depo1_4` varchar(25) DEFAULT NULL,
                                        `iva_depo1_5` varchar(25) DEFAULT NULL,
                                        `total_depo1_1` varchar(25) DEFAULT NULL,
                                        `total_depo1_2` varchar(25) DEFAULT NULL,
                                        `total_depo1_3` varchar(25) DEFAULT NULL,
                                        `total_depo1_4` varchar(25) DEFAULT NULL,
                                        `total_depo1_5` varchar(25) DEFAULT NULL,
                                        `concepto` varchar(25) DEFAULT NULL,
                                        `valor_concepto` varchar(25) DEFAULT NULL,
                                        `subtotal_depo2` varchar(25) DEFAULT NULL,
                                        `iva_depo2` varchar(25) DEFAULT NULL,
                                        `total_depo2` varchar(25) DEFAULT NULL,
                                        `valor_sobre_nomina1_1` varchar(25) DEFAULT NULL,
                                        `valor_sobre_nomina1_2` varchar(25) DEFAULT NULL,
                                        `valor_sobre_nomina1_3` varchar(25) DEFAULT NULL,
                                        `valor_sobre_nomina1_4` varchar(25) DEFAULT NULL,
                                        `valor_sobre_nomina1_5` varchar(25) DEFAULT NULL,
                                        `valor_comision_variable1_1` varchar(25) DEFAULT NULL,
                                        `valor_comision_variable1_2` varchar(25) DEFAULT NULL,
                                        `valor_comision_variable1_3` varchar(25) DEFAULT NULL,
                                        `valor_comision_variable1_4` varchar(25) DEFAULT NULL,
                                        `valor_comision_variable1_5` varchar(25) DEFAULT NULL,
                                        `fecha_creacion` datetime NOT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "datos_facturacion2021":
                    try {
                        $tabla_desa = 'datos_facturacion2021';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                        `id_periodo` int(11) NOT NULL,
                                        `nomina` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `beneficio_sindical` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `anticipo` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `vacaciones` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `pago_prima_vaca` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `comision_mismo_dia` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_pago_nomina` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `porcentaje_honorarios` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valores_honorarios` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `ejercicio` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `costos_patronales` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `detalle_subtotal` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `detalle_iva` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `detalle_total` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `prestaciones_extras` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `cuota_fija` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `exc_cf` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `presta_dinero` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `gastos_medi_pensionados` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `riesgo_trabajo` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `invalidez_y_vida` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `guarderias_y_pre_sociales` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `cuotas_imss_retiro` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `cuotas_imss_censatiaV` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `cred_vivienda` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `porcentaje_errogaciones` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_errogaciones` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `totalcostos_patronales` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `carga_social1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `carga_social1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `carga_social1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `carga_social1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `carga_social1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `cadena_emisoras` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `porcentajes_nomina` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `suministro_per1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `suministro_per1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `suministro_per1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `suministro_per1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `suministro_per1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `porcentaje_comisionV` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `concepto` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_concepto` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `subtotal_depo2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `iva_depo2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `total_depo2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_sobre_nomina1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_sobre_nomina1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_sobre_nomina1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_sobre_nomina1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_sobre_nomina1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_comision_variable1_1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_comision_variable1_2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_comision_variable1_3` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_comision_variable1_4` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `valor_comision_variable1_5` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `fecha_creacion` datetime NOT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "detalle_activos_empleados":
                    try {
                        $tabla_desa = 'detalle_activos_empleados';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `id_activo` int(11) DEFAULT NULL COMMENT 'Relación con la tabla activos',
                                          `id_empleado` int(11) DEFAULT NULL COMMENT 'Relación con la tabla empleados',
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "detalle_formulario_encuesta":
                    try {
                        $tabla_desa = 'detalle_formulario_encuesta';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `id_empleado` int(11) DEFAULT NULL COMMENT 'relación con el empleado',
                                          `id_encuesta` int(11) DEFAULT NULL COMMENT 'relación con la tabla formulario_encuesta',
                                          `estatus` int(11) DEFAULT '1' COMMENT '1:activo ,2:inactivo ,3:cerrado',
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_encuesta":
                    try {
                        $tabla_desa = 'formulario_encuesta';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `titulo` varchar(100) DEFAULT NULL,
                                        `descripcion` varchar(250) DEFAULT NULL,
                                        `fecha_vencimiento` date DEFAULT NULL,
                                        `estatus` int(11) DEFAULT NULL,
                                        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                        `updated_at` timestamp NULL DEFAULT NULL,
                                        `deleted_at` timestamp NULL DEFAULT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_opc_preguntas":
                    try {
                        $tabla_desa = 'formulario_opc_preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                         `id_pregunta` int(11) DEFAULT NULL COMMENT 'Relación con tabla formulario_pregunta',
                                         `titulo` varchar(250) DEFAULT NULL,
                                         `valor` varchar(250) DEFAULT NULL,
                                         PRIMARY KEY (`id`)
                                         ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_preguntas":
                    try {
                        $tabla_desa = 'formulario_preguntas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                          `id_encuesta` int(11) DEFAULT NULL COMMENT 'id tabla formulario_encuesta',
                                          `id_tipo` int(11) DEFAULT NULL COMMENT 'id tabla formulario_tipo',
                                          `pregunta` varchar(250) DEFAULT NULL COMMENT 'nombre de las preguntas',
                                          `valor` varchar(250) DEFAULT NULL,
                                          `lleva_icono` varchar(1) DEFAULT NULL COMMENT 'Si la pregunta lleva iconos su valor es 1',
                                          `estatus` int(11) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "formulario_respuestas":
                    try {
                        $tabla_desa = 'formulario_respuestas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `id_pregunta` int(11) DEFAULT NULL COMMENT 'id de formulario_preguntas',
                                        `id_empleado` int(11) DEFAULT NULL,
                                        `id_encuesta` int(11) DEFAULT NULL COMMENT 'relación con formulario encuesta',
                                        `respuestas` varchar(250) DEFAULT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                //ToDo: checar para borrar
                case "formulario_tipo":
                    try {
                        $tabla_desa = 'formulario_tipo';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `id_empleado` int(11) NOT NULL,
                                        `fortalezas` varchar(80) NOT NULL,
                                        `areas_opor` varchar(80) NOT NULL,
                                        KEY `id_empleado` (`id_empleado`),
                                        KEY `id` (`id`),
                                        CONSTRAINT `fortalezas_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                        CONSTRAINT `fortalezas_ibfk_2` FOREIGN KEY (`id`) REFERENCES `evaluacion_desempeno` (`id`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "horarios":
                    try {
                        $tabla_desa = 'horarios';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `alias` varchar(100) NOT NULL,
                                    `estatus` varchar(1) NOT NULL,
                                    `entrada` time NOT NULL,
                                    `salida` time NOT NULL,
                                    `tolerancia` int(11) NOT NULL DEFAULT '0',
                                    `comida` varchar(1) NOT NULL DEFAULT '0',
                                    `entrada_comida` time DEFAULT NULL,
                                    `salida_comida` time  DEFAULT NULL,
                                    `lunes` varchar(1) NOT NULL DEFAULT '0',
                                    `martes` varchar(1) NOT NULL DEFAULT '0',
                                    `miercoles` varchar(1) NOT NULL DEFAULT '0',
                                    `jueves` varchar(1) NOT NULL DEFAULT '0',
                                    `viernes` varchar(1) NOT NULL DEFAULT '0',
                                    `sabado` varchar(1) NOT NULL DEFAULT '0',
                                    `domingo` varchar(1) NOT NULL DEFAULT '0',
                                    `indefinido` varchar(1) NOT NULL DEFAULT '0',
                                    `retardos` varchar(10) NOT NULL DEFAULT '0',
                                    `lunes_entrada` time NOT NULL DEFAULT '0',
                                    `lunes_salida` time NOT NULL DEFAULT '0',
                                    `martes_entrada` time NOT NULL DEFAULT '0',
                                    `martes_salida` time NOT NULL DEFAULT '0',
                                    `miercoles_entrada` time NOT NULL DEFAULT '0',
                                    `miercoles_salida` time NOT NULL DEFAULT '0',
                                    `jueves_entrada` time NOT NULL DEFAULT '0',
                                    `jueves_salida` time NOT NULL DEFAULT '0',
                                    `viernes_entrada` time NOT NULL DEFAULT '0',
                                    `viernes_salida` time NOT NULL DEFAULT '0',
                                    `sabado_entrada` time NOT NULL DEFAULT '0',
                                    `sabado_salida` time NOT NULL DEFAULT '0',
                                    `domingo_entrada` time NOT NULL DEFAULT '0',
                                    `domingo_salida` time NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "horarios_dias":
                    try {
                        $tabla_desa = 'horarios_dias';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_horario` int(11) NOT NULL,
                                      `motivo` varchar(100) NOT NULL,
                                      `fecha_festiva` date NOT NULL,
                                      `usuario_alta` varchar(100) NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "huellas_empleado":
                    try {
                        $tabla_desa = 'huellas_empleado';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                      `id_empleado` int(11) NOT NULL,
                                      `indice` int(11) DEFAULT NULL,
                                      `huella` text,
                                      PRIMARY KEY (`id`),
                                      KEY `fk_huellas_empleado_empleado1_idx` (`id_empleado`),
                                      CONSTRAINT `fk_huellas_empleado_empleado1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "pendientes":
                    try {
                        $tabla_desa = 'pendientes';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "registro_covid":
                    try {
                        $tabla_desa = 'registro_covid';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                 `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `id_empleado` int(11) NOT NULL,
                                  `fecha_inicio` datetime DEFAULT NULL,
                                  `fecha_fin` datetime DEFAULT NULL,
                                  `estatus` int(11) DEFAULT NULL,
                                  `notas` text,
                                  PRIMARY KEY (`id`),
                                  KEY `fk_registro_covid_empleados1_idx` (`id_empleado`),
                                  CONSTRAINT `fk_registro_covid_empleados1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "rutinas":
                    try {
                        $tabla_desa = 'rutinas';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_periodo` int(11) NOT NULL,
                                `id_empleado` int(11) NOT NULL,
                                `ejercicio` int(11) NOT NULL,
                                `tipo_baja` int(11) NOT NULL DEFAULT '0',
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` decimal(15,2) DEFAULT NULL,
                                `total_percepcion_fiscal2` decimal(15,2) DEFAULT NULL,
                                `total_percepcion_sindical` decimal(15,2) DEFAULT NULL,
                                `total_deduccion_fiscal` decimal(15,2) DEFAULT NULL,
                                `total_deduccion_fiscal2` decimal(15,2) DEFAULT NULL,
                                `total_deduccion_sindical` decimal(15,2) DEFAULT NULL,
                                `neto_fiscal` decimal(15,2) DEFAULT NULL,
                                `neto_sindical` decimal(15,2) DEFAULT NULL,
                                `incapacidades` decimal(15,2) DEFAULT NULL,
                                `total_gravado` decimal(15,2) DEFAULT NULL,
                                `sdo_faltas` decimal(15,2) DEFAULT NULL,
                                `sdo_incapacidades` decimal(15,2) DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `cuota_fija` decimal(15,2) DEFAULT NULL,
                                `exce_pa` decimal(15,2) DEFAULT NULL,
                                `exce_ob` decimal(15,2) DEFAULT NULL,
                                `pre_dine_obre` decimal(15,2) DEFAULT NULL,
                                `pre_dine_patro` decimal(15,2) DEFAULT NULL,
                                `gas_medi_patro` decimal(15,2) DEFAULT NULL,
                                `gas_medi_obre` decimal(15,2) DEFAULT NULL,
                                `riesgo_trabajo` decimal(15,2) DEFAULT NULL,
                                `inva_vida_patro` decimal(15,2) DEFAULT NULL,
                                `inva_vida_obre` decimal(15,2) DEFAULT NULL,
                                `guarde_presta` decimal(15,2) DEFAULT NULL,
                                `sar_patron` decimal(15,2) DEFAULT NULL,
                                `infonavit_patro` decimal(15,2) DEFAULT NULL,
                                `censa_vejez_patron` decimal(15,2) DEFAULT NULL,
                                `censa_vejez_obre` decimal(15,2) DEFAULT NULL,
                                `beneficio_sindical` decimal(15,2) DEFAULT '0.00',
                                `importe_total` decimal(15,2) DEFAULT '0.00',
                                `subsidio` decimal(15,2) DEFAULT '0.00',
                                `infonavit_patron` decimal(15,2) DEFAULT NULL,
                                `subsidio_al_empleo` decimal(15,2) DEFAULT NULL,
                                `censa_vejez_obre_patronal` decimal(15,2) DEFAULT NULL,
                                `bono_prima` decimal(15,2) DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "rutinas2018":
                    try {
                        $tabla_desa = 'rutinas2018';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor19` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total19` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado3` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado21` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado27` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "rutinas2019":
                    try {
                        $tabla_desa = 'rutinas2019';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado20` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado28` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado30` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "rutinas2020":
                    try {
                        $tabla_desa = 'rutinas2020';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_empleado` int(11) NOT NULL,
                                `id_periodo` int(11) NOT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "rutinas2021":
                    try {
                        $tabla_desa = 'rutinas2021';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_periodo` int(11) NOT NULL,
                                `id_empleado` int(11) NOT NULL,
                                `estatus` int(11) NOT NULL DEFAULT '1',
                                `infonavit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_imss` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `concepto_fac` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_percepcion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_fiscal2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_deduccion_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `neto_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total_gravado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_faltas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sdo_incapacidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `fnq_valor` int(11) DEFAULT '0',
                                `cuota_fija` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `exce_ob` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `pre_dine_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gas_medi_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `riesgo_trabajo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `inva_vida_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `guarde_presta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `sar_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `infonavit_patro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `beneficio_sindical` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `importe_total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `subsidio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `infonavit_patron` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `subsidio_al_empleo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `censa_vejez_obre_patronal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `bono_prima` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `dias_no_laborados` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `true_isr` int(11) DEFAULT '0',
                                `estatus_confirma` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '0',
                                `valor1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado1` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado4` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado5` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado6` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado7` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado8` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado9` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado10` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado15` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado16` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado17` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado18` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado22` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado23` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado25` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado26` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado29` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado31` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado32` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado33` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado34` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado35` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado36` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado37` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado38` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado39` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado40` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado41` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado42` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado43` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado44` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado45` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado46` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado47` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado48` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado49` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado50` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado51` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `valor52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `excento52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `gravado52` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `bono_prima_dom` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;

                case "rutina_valores":
                    try {
                        $tabla_desa = 'rutina_valores';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_rutina` int(11) NOT NULL,
                                `id_concepto` int(11) DEFAULT NULL,
                                `tipo_concepto` int(11) DEFAULT NULL,
                                `nombre_concepto` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `total` decimal(15,2) DEFAULT NULL,
                                `valor` decimal(15,2) DEFAULT NULL,
                                `exento` decimal(15,2) DEFAULT NULL,
                                `gravado` decimal(15,2) DEFAULT NULL,
                                PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
                case "totales_clasificacion":
                    try {
                        $tabla_desa = 'totales_clasificacion';
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `idclasificacion` int(11) NOT NULL,
                                `idcuestionario_trabajador` int(11) NOT NULL,
                                `total` int(11) DEFAULT NULL,
                                 PRIMARY KEY (`id`),
                                 KEY `fk_totales_clasificacion_cuestionarios_trabajadores1_idx` (`idcuestionario_trabajador`),
                                 KEY `fk_totales_clasificacion_catalogos_preguntas_norma1_idx` (`idclasificacion`),
                                 CONSTRAINT `fk_totales_clasificacion_catalogos_preguntas_norma1` FOREIGN KEY (`idclasificacion`) REFERENCES `catalogos_norma` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                                 CONSTRAINT `fk_totales_clasificacion_cuestionarios_trabajadores1` FOREIGN KEY (`idcuestionario_trabajador`) REFERENCES `cuestionarios_trabajadores` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);
                    }
                    break;
            }
        }
    }

    public function editarempresaReceptora($empresa)
    {
        $empresas = Empresa::where('id', $empresa)->get();

        return view('empresa-receptora.editar-empresa-receptora', compact('empresas'));
    }

    public function actualizarempresaReceptora(Request $request)
    {

        $validated = $request->validate([
            'razon_social' => 'required',
            'representante_legal' => 'required',
            'rfc' => 'required',
            'telefono' => 'required',
            'email' => 'required',
            'calle_num' => 'required',
            'colonia' => 'required',
            'delegacion_municipio' => 'required',
            'codigo_postal' => 'required',
            'calculo_imss' => 'required'
        ]);


        if($request->activa_restricciones == "on"){ $activa_restricciones = 1; }else{ $activa_restricciones = 0; }
        if($request->lista_empleados == "on"){ $lista_empleados = 1; }else{ $lista_empleados = 0; }

        $data = [
            'razon_social' => strtoupper($request->get('razon_social', '')),
            'rfc' => strtoupper($request->get('rfc', '')),
            'ins' => $request->get('ins', ''),
            'identificacion_oficial' => $request->get('identificacion_oficial', ''),
            'num_notaria' => $request->get('num_notaria', ''),
            'representante_legal' => strtoupper($request->get('representante_legal', '')),
            'nombre_notario' => strtoupper($request->get('nombre_notario', '')),
            'lugar_notaria' => strtoupper($request->get('lugar_notaria', '')),
            'otorgamiento_rdp' => strtoupper($request->get('otorgamiento_rdp', '')),
            'giro' => strtoupper($request->get('giro', '')),
            'tasa_vigente' => $request->get('tasa_vigente', ''),
            'telefono' => $request->get('telefono', ''),
            'email' => $request->get('email', ''),
            'contacto_directo' => strtoupper($request->get('contacto_directo', '')),
            'base'=>$request->base,
            'repositorio'=>$request->repositorio,
            'porcentaje_fondo' => $request->get('porcentaje_fondo', ''),
            'calle_num' => strtoupper($request->get('calle_num', '')),
            'colonia' => strtoupper($request->get('colonia', '')),
            'delegacion_municipio' => strtoupper($request->get('delegacion_municipio', '')),
            'estado' => $request->get('estado', ''),
            'codigo_postal' => $request->get('codigo_postal', ''),
            'calles_referencia' => strtoupper($request->get('calles_referencia', '')),
            'activa_restricciones' => $activa_restricciones,
            'calculo_imss' => $request->get('calculo_imss', 'UMA'),
            'dias_imss' => $request->get('dias_imss', 0),
            'lista_empleados' => $lista_empleados,
            'sede' => ($request->get('sede')) ?$request->get('sede', 0):0,
            'sss' => $request->get('sss', 0),
            'timbrado' => $request->get('timbrado', 0),
            'norma' => $request->get('norma', 0),
            'permiso_extranjero' => $request->get('permiso_extranjero', 0),
            'estatus' => 1,
            'fecha_edicion' => date('Y-m-d H:i:s')
        ];

        Empresa::where('id', $request->id_empresa)->update($data);

        $mp = DB::table('logs')->insert(
            [
                'usuario' => Auth::user()->email,
                'evento' => 'Catalogo de Empresas: '.$request->razon_social. '"UPDATE"',
                'base' => $request->base,
                'query' => '',
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        );

        $base = $request->base;
        $this->guardarSedes($request->sede, $request->sedes, $base);

        session()->flash('success', 'La empresa receptora se modifico correctamente');
        return redirect()->route('empresar.empresareceptora');
    }

    public function guardarSedes($haySedes, $sedes, $base)
    {

        $enterprise = $base;

        Config::set('database.connections.empresa.database', $enterprise);

        if(count($sedes) > 0 && $haySedes == 1){
            if (!Schema::hasTable('sedes')) {
                $query ="CREATE TABLE IF NOT EXISTS sedes (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `nombre` varchar(30) NOT NULL,
                    PRIMARY KEY (`id`)
                )  CHARSET=latin1 AUTO_INCREMENT=1 ;";
                DB::connection('generica')->select( DB::raw($query));
            }

            DB::connection('empresa')->table('sedes')->truncate();
            foreach($sedes as $sede){
                if(!empty($sede))
                    $s[] = ['nombre' => $sede];
            }
            DB::connection('empresa')->table('sedes')->insert($s);
        } else {
            DB::connection('empresa')->table('sedes')->truncate();
        }
    }

    public function asignarEmpresaEmisora($empresa)
    {
        $idEmpresa = $empresa;
        $idEmpresasAsignadas = DB::table('asigna_empresas_emisoras')
                                ->select('id_empresa_e')
                                ->where('id_empresa', $idEmpresa)
                                ->where('estatus', 1)
                                ->get();
        $ids = [];
        foreach($idEmpresasAsignadas->toArray() as $item){
            $ids[] = $item->id_empresa_e;
        }

        $empresasDisponibles = EmpresaEmisora::whereNotIn('id', $ids)->orderBy('razon_social', 'asc')->get();
        $empresasAsignadas = EmpresaEmisora::whereIn('id', $ids)->orderBy('razon_social', 'asc')->get();

        return view('empresa-receptora.asignar-empresa-emisora', compact('empresasDisponibles', 'empresasAsignadas', 'idEmpresa'));
    }

    public function agregarempresaEmisora(Request $request)
    {

        foreach($request->empresas as $empresa){
            $data[] = [
                'id_empresa' => $request->idEmpresa,
                'id_empresa_e' => $empresa,
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
        }
        DB::table('asigna_empresas_emisoras')->insert($data);

        // cambiarBaseA(Session::get('base'));
        // logGeneral(Auth::user()->email, 'Catálogo empresas AspciarEmisora con ID: '.$request->id, 'Singh', '');
        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empresas AsociarEmisora');

        session()->flash('success', 'Se asociaron correctamente las empresas.');
        return redirect()->route('empresar.empresareceptora');

    }

    public function eliminarempresaEmisora(Request $request)
    {
        DB::table('asigna_empresas_emisoras')
            ->where('id_empresa', $request->idEmpCliente)
            ->where('id_empresa_e', $request->idEmpAsignada)
            ->update(['estatus' => 0]);
        // cambiarBaseA(Session::get('base'));
        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empresas DesAsociarEmisora');
        // logGeneral(Auth::user()->email, 'Catálogo empresas DesasociarEmisora con ID: '.$request->id, 'Singh', '');
        return response()->json(['ok' => 1]);
    }

    public function asignarempresaConceptos($empresa)
    {

        try{

            $idContratosAsignados = DB::connection('singh')
                            ->table('conceptos_nomina')
                            ->select('id', 'id_alterno')
                            ->where('estatus', 1)
                            ->orderBy('nombre_concepto')
                            ->get();

        } catch(PDOException $e){

            session()->flash('success', 'No se encontró la Base de Datos de la empresa.');
            return redirect()->route('empresar.empresareceptora');

        }

        $idEmpresa = $empresa;

        $emp = Empresa::find($idEmpresa);

        $enterprise = $emp->base;

        Config::set('database.connections.empresa.database', $enterprise);

        $ids = [];
        foreach($idContratosAsignados->toArray() as $item){

            $ids[] = $item->id_alterno;

        }


        $conceptosAsignados = DB::connection('empresa')->table('conceptos_nomina')
                                ->whereIn('id_alterno', $ids)
                                ->where('estatus', 1)
                                ->orderBy('nombre_concepto', 'asc')
                                ->get();

        $conceptosDisponibles = DB::table('conceptos_nomina')
                                    ->whereIn('id_alterno', $ids)
                                    ->where('estatus', 1)
                                    ->orderBy('nombre_concepto', 'asc')
                                    ->get();

        return view('empresa-receptora.asignar-conceptos', compact('conceptosAsignados', 'conceptosDisponibles', 'idEmpresa'));
    }

    public function agregarConcepto(Request $request)
    {

        $conceptos = DB::table('conceptos_nomina')
                    ->where('estatus', 1)
                    ->whereIn('id_alterno', $request->conceptos)
                    ->get();

        $emp = Empresa::find($request->idEmpresa);
        cambiarBase($emp->base);

        foreach($conceptos as $concepto){

            unset($concepto->id,$concepto->fecha_creacion);
            $concepto->fecha_edicion = date('Y-m-d H:i:s');
            $concepto = (array) $concepto;

            $datos=ConceptosNomina::updateOrCreate(
                ['nombre_concepto' => $concepto['nombre_concepto']],
                $concepto
            );

        }

        // logEmpresa(Session::get('base'), Auth::user()->email, 'Asignacion de conceptos: ' . implode(',', $request->conceptos));

        session()->flash('success', 'Se asociaron correctamente los conceptos de nomina.');
        return redirect()->route('empresar.empresareceptora');

    }

    public function eliminarconceptoEmpresa(Request $request)
    {
        $emp = Empresa::find($request->idEmpresa);

        $enterprise = $emp->base;

        Config::set('database.connections.empresa.database', $enterprise);

        DB::connection('empresa')->table('conceptos_nomina')
            ->where('id_alterno', $request->idConcAsignado)
            ->update(['estatus' => 0]);

        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empresas DesAsociarConcepto');
        return response()->json(['ok' => 1]);
    }

    public function asignarempresaContratos($empresa)
    {

        $idEmpresa = $empresa;
        $emp = Empresa::find($idEmpresa);

        $empresa = $emp->base;

        Config::set('database.connections.empresa.database', $empresa);

        $idContratosAsignados = DB::connection('empresa')
                                ->table('asignacion_contratos')
                                ->where('estatus', 1)
                                ->get();
        $ids = [];
        foreach($idContratosAsignados->toArray() as $item){
            $ids[] = $item->id_contrato;
        }

        $contratosDisponibles = Contrato::whereNotIn('id', $ids)->orderBy('nombre', 'asc')->get();
        $contratosAsignados = Contrato::whereIn('id', $ids)->orderBy('nombre', 'asc')->get();

        return view('empresa-receptora.asignar-contratos', compact('contratosAsignados', 'contratosDisponibles', 'idEmpresa'));
    }

    public function agregarcontratoEmpresa(Request $request)
    {
        foreach($request->contratos as $contrato){
            $data[] = [
                'id_contrato' => $contrato,
                'estatus' => 1
            ];
        }
        $idEmpresa = $request->idEmpresa;
        $emp = Empresa::find($idEmpresa);

        $enterprise = $emp->base;

        Config::set('database.connections.empresa.database', $enterprise);

        DB::connection('empresa')->table('asignacion_contratos')->insert($data);

        // logEmpresa(Session::get('base'), Auth::user()->email, 'Asignacion de contratos: ' . implode(',', $request->contratos));

        session()->flash('success', 'Se asociaron correctamente los contratos');
        return redirect()->route('empresar.empresareceptora');

    }

    public function eliminarcontratoEmpresa(Request $request)
    {
        $emp = Empresa::find($request->idEmpresa);

        $enterprise = $emp->base;

        Config::set('database.connections.empresa.database', $enterprise);

        DB::connection('empresa')
            ->table('asignacion_contratos')
            ->where('id_contrato', $request->idContAsignado)
            ->update(['estatus' => 0]);

        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo de Empresas DesAsociarContrato');
        return response()->json(['ok' => 1]);
    }

    public function borrarempresareceptora(Request $request)
    {
        Empresa::where('id', $request->idempresa)->update(['estatus' => 0]);
        // logGeneral(Auth::user()->email, 'Catálogo empresas con ID: '.$request->id.', DELETE', 'Singh', '');
        session()->flash('success', 'La empresa fue eliminada correctamente');
        return redirect()->route('empresar.empresareceptora');

    }

    protected function descripcionSalidaError($e, $tabla)
    {
        $this->errores[] = 'Error en la tabla: '. $tabla.'. Descripción: ' . $e;
    }
}
