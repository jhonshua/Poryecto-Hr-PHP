<?php

namespace App\Http\Controllers\DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

use App\Models\Empresa;
use App\Models\Contrato;
use App\Models\EmpresaEmisora;
class TableController extends Controller
{
    const PREFIJO_EMPRESA = 'empresa';
    const FOLDER_REPOSITORIO = 'repositorio';
    protected $errores = [];
    /**
     * Create dynamic table along with dynamic fields
     *
     * @param       $table_name
     * @param array $fields
     *
     * @return Boolean
     */
    public function createTable($base, $table_name, $fields = [], $timestamps = false, $default = 0)
    {
        
        // check if table is not already exists
        if (!Schema::connection('empresa')->hasTable($table_name)) {
            cambiarBase($base);
            
            Schema::connection('empresa')->create($table_name, function (Blueprint $table) use ($fields, $table_name, $timestamps, $default) {
                // $table->increments('id');
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        if($field['nullable']){
                            if($field['type'] == 'string'){
                                if(isset($field['size']))
                                    $table->{$field['type']}($field['name'], $field['size'])->nullable();
                                else
                                    $table->{$field['type']}($field['name'])->nullable();
                            }else{
                                $table->{$field['type']}($field['name'])->nullable();
                            }
                        } else{
                            if($field['type'] == 'string'){
                                if(isset($field['size']))
                                    $table->{$field['type']}($field['name'], $field['size']);
                                else
                                    $table->{$field['type']}($field['name']);
                            }else{
                                $table->{$field['type']}($field['name']);
                            }
                        }

                        if($default > 0)
                            $table->default($default);
                    }
                }
                if($timestamps) $table->timestamps();
            });

            return true;
        }

        return false;
    }

    public function checaTabla($base,$tabla){
        cambiarBase($base); 
        $queryPatronal="SELECT re.num_registro_patronal,re.tipo_clase,re.id,em.tipo_jornada,em.tipo_contrato, cat.nombre as nombre_categoria
                        FROM categorias cat  
                        JOIN empleados em  
                        ON em.id_categoria = cat.id 
                        INNER JOIN singh.registro_patronal re 
                        ON cat.tipo_clase = re.id
                        WHERE em.id = '$id_empleado' 
                        AND   cat.estatus = 1";
        $result = DB::connection('empresa')->select($queryPatronal);

        dd($result);
    }

    public function creaEmpresas($id){

        echo "hola $id";
        
        $base="empresa000$id";

        $conexion = 'empresa';
        try {	
            $stm = 'DROP DATABASE IF EXISTS '.$base.' ;';
            DB::connection($conexion)->statement($stm);	

            $stm = 'CREATE DATABASE IF NOT EXISTS '.$base.' /*!40100 DEFAULT CHARACTER SET utf8 */;';
            DB::connection($conexion)->statement($stm);	      			

        }catch (\Exception $e) {
            $this->errores[] = 'No se pudo crear la Base de datos ' .$base;
            return;
        }

        cambiarBase($base);
        foreach (Empresa::TABLAS_EMPRESA as $tabla) {

            switch ($tabla){
                
                case "aguinaldo":
                    try {
        
                        $tabla_desa = 'aguinaldo';							
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                             ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
                        DB::connection($conexion)->statement($stm);		    
        
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                    break;

                    case "asignacion_biometricos":
                        try {
            
                            $tabla_desa = 'asignacion_biometricos';
            
                            // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                            DB::connection($conexion)->statement($stm);
            
                            // IMPRIME RESULTADO DE LA TABLA MIGRADA
                            // descripcionSalida2($conexion, $base, $tabla_desa);
            
                        }catch (\Exception $e) {
                            $this->descripcionSalidaError($e, $tabla_desa);		
                        }
                        break;

                        case "asignacion_contratos":
                            try {
                                $tabla_desa = 'asignacion_contratos'; 
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_contrato` int(11) NOT NULL,
                                      `estatus` int(11) NOT NULL DEFAULT 0,
                                      PRIMARY KEY (`id`),
                                      KEY `idcontrato` (`id_contrato`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "asistencias":
                            try {
                                $tabla_desa = 'asistencias';						
                    
                               // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id_empleado` int(11) NOT NULL,
                                      `fecha` datetime NOT NULL,
                                      `lugar` TINYTEXT DEFAULT NULL,
                                      `coordenada` TINYTEXT DEFAULT NULL,
                                      PRIMARY KEY (`id_empleado`,`fecha`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);					    					
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                                    
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "asistencia_horario":
                            try {
                                $tabla_desa = 'asistencia_horario';							
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                
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
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                        
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "avisos":
                            try {
                                $tabla_desa = 'avisos';						
                            
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `titulo` text NOT NULL,
                                    `inicio` date NOT NULL,
                                    `fin` date NOT NULL,
                                    `tipo` varchar(10) DEFAULT NULL,
                                    `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    `estatus` int(11) NOT NULL DEFAULT '1',
                                     PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }     
                        break;
                                        
                        case "avisos_multimedia":
                            try {
                            
                                $tabla_desa = 'avisos_multimedia';						
                                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `id_avisos` int(11) DEFAULT NULL,
                                    `nombre` varchar(50) DEFAULT NULL,
                                    `tipo` varchar(10) DEFAULT NULL,
                                    `tiempo` int(11) DEFAULT '3000',
                                    PRIMARY KEY (`id`),
                                    KEY `id_avisos` (`id_avisos`),
                                    CONSTRAINT `FK_avisos_multimedia_avisos` FOREIGN KEY (`id_avisos`) REFERENCES `avisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE                                                  
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;	

                        case "biometricos":
                            try {	
                                    
                                $tabla_desa = 'biometricos';					
                                    
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                                    
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "bitacora":
                            // echo "impuestos actualizado<br>";
                            try {
                                $tabla_desa = 'bitacora';
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "categorias":
                            try {
                                $tabla_desa = 'categorias';						
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `nombre` varchar(50) NOT NULL,
                                      `estatus` int(2) NOT NULL DEFAULT '0',
                                      `tipo_clase` varchar(50) NOT NULL,
                                      `fecha_creacion` datetime NOT NULL,
                                      `fecha_edicion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "conceptos_nomina":
                            try {
                                $tabla_desa = 'conceptos_nomina';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "contratos":
                            try {
                                $tabla_desa = 'contratos';						
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_empleado` int(11) NOT NULL,
                                      `fecha_contrato` datetime NOT NULL,
                                      `fecha_vencimiento` datetime DEFAULT NULL,
                                      `estatus` int(11) NOT NULL,
                                      `numero_dias` int(11) DEFAULT NULL,
                                      `contrato` varchar(200) NOT NULL,
                                      `fecha_creacion` datetime NOT NULL,
                                      `alerta` date DEFAULT NULL,
                                      PRIMARY KEY (`id`),
                                      KEY `idempleado` (`id_empleado`),
                                      CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
        
                        case "credenciales":
                            try {
                                $tabla_desa = 'credenciales';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_empleado` int(11) NOT NULL,
                                      `repositorio` varchar(200) NOT NULL,
                                      `estatus` int(11) NOT NULL,
                                      PRIMARY KEY (`id`),
                                      KEY `idempleado` (`id_empleado`),
                                      CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;	

                        case "demandas_audiencias":
                            try {
                                $tabla_desa = 'demandas_audiencias';						
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "demandas_audiencias_bita":
                            try {
                                $tabla_desa = 'demandas_audiencias_bita';						
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "demandas_juridico":
                            try {
                                $tabla_desa = 'demandas_juridico';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "departamentos":
                            try {
                                $tabla_desa = 'departamentos';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `nombre` varchar(50) NOT NULL,
                                    `estatus` int(11) NOT NULL,
                                    `fecha_creacion` datetime NOT NULL,
                                    `fecha_edicion` datetime NOT NULL,
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "dispersiones":
                            try {
                                $tabla_desa = 'dispersiones';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                    PRIMARY KEY (`id`),
                                    KEY `idempleado` (`id_empleado`),
                                    KEY `IdPeriodo` (`id_periodo`),
                                    CONSTRAINT `dispersiones_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                    CONSTRAINT `dispersiones_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "dispersiones_aguinaldo":
                            try {
                                $tabla_desa = 'dispersiones_aguinaldo';							
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "documentos_empleados":
                            try {	
                                $tabla_desa = 'documentos_empleados';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL,
                                      `id_empleado` int(11) NOT NULL,
                                      `file_documento` text NOT NULL,
                                      PRIMARY KEY (`id`,`id_empleado`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;    

                        case "doc_empleados":
                            try {                
                                $tabla_desa = 'doc_empleados';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `nombre_doc` text NOT NULL,
                                      `tipo_archivo` text NOT NULL,
                                      `obligatorio` int(11) NOT NULL,
                                      `estatus` int(11) NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "ejercicios":
                            try {                
                                $tabla_desa = 'ejercicios';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `ejercicio` int(11) NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados":
                            try {                
                                $tabla_desa = 'empleados';
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `numero_empleado` varchar(10) NOT NULL,
                                    `estatus` int(11) NOT NULL DEFAULT '0',
                                    `nombre` varchar(50) NOT NULL,
                                    `apaterno` varchar(50) NOT NULL,
                                    `amaterno` varchar(50) NOT NULL,
                                    `genero` varchar(20) NOT NULL,
                                    `correo` varchar(50) DEFAULT NULL,
                                    `rfc` varchar(20) NOT NULL,
                                    `curp` varchar(25) NOT NULL,
                                    `fecha_nacimiento` date DEFAULT NULL,
                                    `lugar_nacimiento` varchar(50) NOT NULL,
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
                                    `nss` varchar(30) NOT NULL,
                                    `id_categoria` int(2) NOT NULL,
                                    `id_departamento` int(2) DEFAULT NULL,
                                    `id_prestacion` int(4) DEFAULT NULL,
                                    `id_horario` int(4) DEFAULT '0',
                                    `id_puesto` int(4) NOT NULL,
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
                                    PRIMARY KEY (`id`),
                                    UNIQUE KEY `correo` (`correo`),
                                    KEY `idprestacion` (`id_prestacion`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados_campos_extras":
                            try {
                                $tabla_desa = 'empleados_campos_extras';
                    
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `nombre_campo` varchar(50) NOT NULL DEFAULT '0',
                                    `alias` varchar(50) NOT NULL DEFAULT '0',
                                    `tipo` varchar(50) NOT NULL DEFAULT '0',
                                    `obligatorio` int(11) NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                                DB::connection($conexion)->statement($stm);
                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                    
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados_deducciones":
                            try {
                                $tabla_desa = 'empleados_deducciones';
                    
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados_informacion_extra":
                            try {
                                $tabla_desa = 'empleados_informacion_extra';
                        
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                   `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
                                   PRIMARY KEY (`id_empleado`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                                DB::connection($conexion)->statement($stm);
                        
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                        
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados_percepciones":
                            try {
                                $tabla_desa = 'empleados_percepciones';	
                    
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;";
                                DB::connection($conexion)->statement($stm);
                                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                    
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "empleados_prestaciones_extras":
                            try {	
                                $tabla_desa = 'empleados_prestaciones_extras';	
                    
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                                    
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "eventos":
                            try {	
                
                                $tabla_desa = 'eventos';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(10) NOT NULL AUTO_INCREMENT,
                                      `nombre` text NOT NULL,
                                      `cuerpo` text NOT NULL,
                                      `usuario` varchar(1) NOT NULL DEFAULT '1',
                                      `estatus` varchar(1) NOT NULL,
                                      `descripcion` text NOT NULL,
                                      `fecha_edicion` datetime NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;";
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
                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "eventos_correos":
                            try {	
                
                                $tabla_desa = 'eventos_correos';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id_evento` int(15) NOT NULL AUTO_INCREMENT,
                                      `id_correo` int(15) NOT NULL,
                                      `correo` text NOT NULL,
                                      `nombre` text NOT NULL,
                                      `tipo` varchar(1) NOT NULL,
                                      `destinatario` varchar(1) NOT NULL,
                                      PRIMARY KEY (`id_evento`,`id_correo`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;    

                        case "evidencias_audiencias":
                            try {	
                
                                $tabla_desa = 'evidencias_audiencias';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "facturas_detalle":
                            try {
                                $tabla_desa = 'facturas_detalle';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `id_detalle` int(11) NOT NULL,
                                    `cantidad` int(11) NOT NULL,
                                    `unidad` varchar(4) NOT NULL,
                                    `concepto` text NOT NULL,
                                    `clave` varchar(8) NOT NULL,
                                    `monto` decimal(10,2) NOT NULL,
                                    `estatus` varchar(1) NOT NULL,
                                    `impuesto_retenido` varchar(20) NOT NULL,
                                    PRIMARY KEY (`id`,`id_detalle`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "factura_periodo":
                            try {
                                $tabla_desa = 'factura_periodo';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                        //ToDo: checar 
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
                                      CONSTRAINT `fortalezas_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                      CONSTRAINT `fortalezas_ibfk_2` FOREIGN KEY (`id`) REFERENCES `evaluacion_desempeno` (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "horarios":
                            try {	
                
                                $tabla_desa = 'horarios';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `alias` varchar(100) NOT NULL,
                                    `estatus` varchar(1) NOT NULL,
                                    `entrada` time NOT NULL,
                                    `salida` time NOT NULL,
                                    `tolerancia` int(11) NOT NULL DEFAULT '0',
                                    `comida` varchar(1) NOT NULL DEFAULT '0',
                                    `entrada_comida` time NOT NULL,
                                    `salida_comida` time NOT NULL,
                                    `lunes` varchar(1) NOT NULL DEFAULT '0',
                                    `martes` varchar(1) NOT NULL DEFAULT '0',
                                    `miercoles` varchar(1) NOT NULL DEFAULT '0',
                                    `jueves` varchar(1) NOT NULL DEFAULT '0',
                                    `viernes` varchar(1) NOT NULL DEFAULT '0',
                                    `sabado` varchar(1) NOT NULL DEFAULT '0',
                                    `domingo` varchar(1) NOT NULL DEFAULT '0',
                                    `indefinido` varchar(1) NOT NULL DEFAULT '0',
                                    `retardos` varchar(10) NOT NULL DEFAULT '0',
                                    `sabado_entrada` time NOT NULL,
                                    `sabado_salida` time NOT NULL,
                                    `domingo_entrada` time NOT NULL,
                                    `domingo_salida` time NOT NULL,
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                            break;
                
                        case "horarios_dias":
                            try {                
                                $tabla_desa = 'horarios_dias';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_horario` int(11) NOT NULL,
                                      `motivo` varchar(100) NOT NULL,
                                      `fecha_festiva` date NOT NULL,
                                      `usuario_alta` varchar(100) NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;";
                                DB::connection($conexion)->statement($stm);	
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "huellas_empleado":
                            try {                
                                $tabla_desa = 'huellas_empleado';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "impuestos":
                            try {	
                
                                $tabla_desa = 'impuestos';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "incapacidades":
                            try {
                                $tabla_desa = 'incapacidades';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "incidencias_prg":
                            try {
                                $tabla_desa = 'incidencias_prg';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);    					
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "incidencias_prg_log":
                            try {	
                
                                $tabla_desa = 'incidencias_prg_log';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `id_periodo` int(11) NOT NULL DEFAULT '0',
                                      `id_empleado` int(11) NOT NULL DEFAULT '0',
                                      `id_concepto` int(11) NOT NULL DEFAULT '0',
                                      `fecha_creacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                                DB::connection($conexion)->statement($stm);	   					
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "involucrados_audiencias":
                            try {	
                                $tabla_desa = 'involucrados_audiencias';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "involucrados_demandas":
                            try {	
                                $tabla_desa = 'involucrados_demandas';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "kit_baja_campos":
                            try {
                                $tabla_desa = 'kit_baja_campos';				
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `nombre_campo` varchar(255) NOT NULL DEFAULT '0',
                                    `alias` varchar(255) NOT NULL DEFAULT '0',
                                    `obligatorio` tinyint(4) NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;
                
                        case "kit_baja_info":
                            try {
                                $tabla_desa = 'kit_baja_info';	
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `id_empleado` int(11) NOT NULL,
                                    `nombre_campo` int(11) NOT NULL,
                                    `archivo` varchar(100) DEFAULT NULL,
                                    `fecha_creacion` datetime DEFAULT NULL,
                                    `fecha_edicion` datetime DEFAULT NULL,
                                    PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);
                                
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "logs":
                            try {	
                
                                $tabla_desa = 'logs';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `usuario` text NOT NULL,
                                      `evento` text NOT NULL,
                                      `fecha_creacion` datetime NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);    					
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                        break;

                        case "log_incidencias":
                            try {
                                $tabla_desa = 'log_incidencias';			
                
                                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                                DB::connection($conexion)->statement($stm);	   					
                            
                                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                                // descripcionSalida2($conexion, $base, $tabla_desa);
                
                            }catch (\Exception $e) {
                                $this->descripcionSalidaError($e, $tabla_desa);		
                            }
                            break;
                
                case "modificaciones_salario":
                    try {	
        
                        $tabla_desa = 'modificaciones_salario';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	   					
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                    break;
        
                case "modificaciones_sueldo":
                    try {	
        
                        $tabla_desa = 'modificaciones_sueldo';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;       

                case "modificaciones_salario":
                    try {	
                        $tabla_desa = 'modificaciones_salario';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "modificaciones_sueldo":
                    try {
                        $tabla_desa = 'modificaciones_sueldo';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	  					
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
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
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break; 

                case "periodos_nomina":
                    try {
                        $tabla_desa = 'periodos_nomina';							
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "permisos":
                    try {	
        
                        $tabla_desa = 'permisos';	
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `id_usuario` (`id_usuario`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='0 - sin permisos\r\n1 - todos los permisos\r\n2 - solo lectura';";
                        DB::connection($conexion)->statement($stm);
                        
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "prestaciones":
                    try {	
        
                        $tabla_desa = 'prestaciones';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);    					
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "prestaciones_extras":
                    try {	
        
                        $tabla_desa = 'prestaciones_extras';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "puestos":
                    try {	
                        $tabla_desa = 'puestos';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "sedes":
                    try {	
        
                        $tabla_desa = 'sedes';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `nombre` varchar(30) NOT NULL,
                              `estatus` int(11) DEFAULT '1',
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);    					
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "saldo_nomina":
                    try {	
        
                        $tabla_desa = 'saldo_nomina';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "subsidios":
                    try {	
        
                        $tabla_desa = 'subsidios';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
        
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "subsidios_b":
                    try {
                        $tabla_desa = 'subsidios_b';
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "tempora_suma_fini":
                    try {
                        $tabla_desa = 'tempora_suma_fini';
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `monto` varchar(30) NOT NULL,
                              `id_empleado` int(11) NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `idempleado` (`id_empleado`),
                              CONSTRAINT `temporasumaFini_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado":
                    try {
                        $tabla_desa = 'timbrado';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_empleado` int(11) NOT NULL,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_timbrado` datetime NOT NULL,
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
                            `fecha_emision` varchar(40) NOT NULL,
                            `num_dias_pagados` varchar(40) NOT NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`id`),
                            KEY `idempleado` (`id_empleado`),
                            KEY `idperiodo` (`id_periodo`),
                            CONSTRAINT `timbrado_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                            CONSTRAINT `timbrado_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_cancelaciones":
                    try {	
        
                        $tabla_desa = 'timbrado_cancelaciones';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_cancelaciones_aguinaldo":
                    try {	
        
                        $tabla_desa = 'timbrado_cancelaciones_aguinaldo';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_cancelaciones_factura":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_factura';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "timbrado_cancelaciones_facturador":
                    try {	
        
                        $tabla_desa = 'timbrado_cancelaciones_facturador';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_cancelaciones_finiquito":
                    try {
                        $tabla_desa = 'timbrado_cancelaciones_finiquito';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_factura":
                    try {
                        $tabla_desa = 'timbrado_factura';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `id_periodo` int(11) NOT NULL,
                            `fecha_timbrado` datetime NOT NULL,
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
                            `fecha_emision` varchar(40) NOT NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            `emisora` int(11) NOT NULL,
                            `deposito` varchar(1) DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `idperiodo` (`id_periodo`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
        
                case "timbrado_facturador":
                    try {
                        $tabla_desa = 'timbrado_facturador';			
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `fecha_timbrado` datetime NOT NULL,
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
                            `fecha_emision` varchar(40) NOT NULL,
                            `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                            `emisora` int(11) NOT NULL,
                            `factura` int(11) NOT NULL,
                            PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "timbrado_finiquito":
                    try {	
        
                        $tabla_desa = 'timbrado_finiquito';			
        
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
                                 `fecha_emision` varchar(40) NOT NULL,
                                 `num_dias_pagados` varchar(40) NOT NULL,
                                 `estatus_timbre` int(11) NOT NULL DEFAULT '1',
                                 PRIMARY KEY (`id`),
                                 KEY `id_empleado` (`id_empleado`),
                                 KEY `id_periodo` (`id_periodo`),
                                 CONSTRAINT `timbradofiniquito_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`),
                                 CONSTRAINT `timbradofiniquito_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id`)
                                 )ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                        DB::connection($conexion)->statement($stm);    					
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "vcards":
                    try {
                        $tabla_desa = 'vcards';
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;

                case "vcards_info":
                    try {	
        
                        $tabla_desa = 'vcards_info';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
                /* TEMPLATE 
                case "":
                    try {	
        
                        $tabla_desa = '';				
        
                        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                            ";
                        DB::connection($conexion)->statement($stm);	
                    
                        // IMPRIME RESULTADO DE LA TABLA MIGRADA
                        // descripcionSalida2($conexion, $base, $tabla_desa);
        
                    }catch (\Exception $e) {
                        $this->descripcionSalidaError($e, $tabla_desa);		
                    }
                break;
                */
########################################################################################               
########################################################################################               
########################################################################################               
########################################################################################               
########################################################################################               
################################################CHECAR SI SIRVEN O BORRAR ##############                   
case "encuesta":
    try {	

        $tabla_desa = 'encuesta';				

        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
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
            ) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;";
        DB::connection($conexion)->statement($stm);	
    
        // IMPRIME RESULTADO DE LA TABLA MIGRADA
        // descripcionSalida2($conexion, $base, $tabla_desa);

    }catch (\Exception $e) {
        $this->descripcionSalidaError($e, $tabla_desa);		
    }
    break;

case "evaluacion_desempeno":
    try {	

        $tabla_desa = 'evaluacion_desempeno';			

        // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
        $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
              id   int(11) NOT NULL AUTO_INCREMENT,
            id_empleado   int(11) NOT NULL,
            fecha_creacion   date NOT NULL,
            evaluador   varchar(30) DEFAULT NULL,
            area_trabajo   varchar(30) DEFAULT NULL,
            nombre_jefe   varchar(50) DEFAULT NULL,
            periodo_inicio   date NOT NULL,
            periodo_fin   date NOT NULL,
            proposito   varchar(200) DEFAULT NULL,
            cyh1   int(11) NOT NULL,
            cyh2   int(11) NOT NULL,
            cyh3   int(11) NOT NULL,
            cyh4   int(11) NOT NULL,
            cyh5   int(11) NOT NULL,
            cyh6   int(11) NOT NULL,
            cyh7   int(11) NOT NULL,
            aac1   int(11) NOT NULL,
            aac2   int(11) NOT NULL,
            aac3   int(11) NOT NULL,
            pya1   int(11) NOT NULL,
            pya2   int(11) NOT NULL,
            pya3   int(11) NOT NULL,
            pya4   int(11) NOT NULL,
            tee1   int(11) NOT NULL,
            tee2   int(11) NOT NULL,
            tee3   int(11) NOT NULL,
            tee4   int(11) NOT NULL,
            tee5   int(11) NOT NULL,
            act1   int(11) NOT NULL,
            act2   int(11) NOT NULL,
            act3   int(11) NOT NULL,
            act4   int(11) NOT NULL,
            act5   int(11) NOT NULL,
            act6   int(11) NOT NULL,
            pro1   int(11) NOT NULL,
            pro2   int(11) NOT NULL,
            pro3   int(11) NOT NULL,
            pro4   int(11) NOT NULL,
            pro5   int(11) NOT NULL,
            lid1   int(11) NOT NULL,
            lid2   int(11) NOT NULL,
            lid3   int(11) NOT NULL,
            lid4   int(11) NOT NULL,
            act_ser1   int(11) NOT NULL,
            act_ser2   int(11) NOT NULL,
            act_ser3   int(11) NOT NULL,
            act_ser4   int(11) NOT NULL,
            cyh_obser1   text NOT NULL,
            cyh_obser2   text NOT NULL,
            cyh_obser3   text NOT NULL,
            cyh_obser4   text NOT NULL,
            cyh_obser5   text NOT NULL,
            cyh_obser6   text NOT NULL,
            cyh_obser7   text NOT NULL,
            aac_obser1   text NOT NULL,
            aac_obser2   text NOT NULL,
            aac_obser3   text NOT NULL,
            pya_obser1   text NOT NULL,
            pya_obser2   text NOT NULL,
            pya_obser3   text NOT NULL,
            pya_obser4   text NOT NULL,
            tee_obser1   text NOT NULL,
            tee_obser2   text NOT NULL,
            tee_obser3   text NOT NULL,
            tee_obser4   text NOT NULL,
            tee_obser5   text NOT NULL,
            act_obser1   text NOT NULL,
            act_obser2   text NOT NULL,
            act_obser3   text NOT NULL,
            act_obser4   text NOT NULL,
            act_obser5   text NOT NULL,
            act_obser6   text NOT NULL,
            pro_obser1   text NOT NULL,
            pro_obser2   text NOT NULL,
            pro_obser3   text NOT NULL,
            pro_obser4   text NOT NULL,
            pro_obser5   text NOT NULL,
            lid_obser1   text NOT NULL,
            lid_obser2   text NOT NULL,
            lid_obser3   text NOT NULL,
            lid_obser4   text NOT NULL,
            act_ser_obser1   text NOT NULL,
            act_ser_obser2   text NOT NULL,
            act_ser_obser3   text NOT NULL,
            act_ser_obser4   text NOT NULL,
            prom_eva1   double NOT NULL,
            prom_eva2   double NOT NULL,
            prom_eva3   double NOT NULL,
            prom_eva4   double NOT NULL,
            prom_eva5   double NOT NULL,
            prom_eva6   double NOT NULL,
            prom_eva7   double NOT NULL,
            prom_eva8   double NOT NULL,
            prom_gen   double NOT NULL,
            estatus   int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `idempleado` (`id_empleado`),
              CONSTRAINT `evaluacionDesempeno_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        DB::connection($conexion)->statement($stm);	
    
        // IMPRIME RESULTADO DE LA TABLA MIGRADA
        // descripcionSalida2($conexion, $base, $tabla_desa);

    }catch (\Exception $e) {
        $this->descripcionSalidaError($e, $tabla_desa);		
    }
    break;        

    case "kpis":
        try {	

            $tabla_desa = 'kpis';							

            // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
            $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                  `id` int(11) NOT NULL,
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
        
            // IMPRIME RESULTADO DE LA TABLA MIGRADA
            // descripcionSalida2($conexion, $base, $tabla_desa);

        }catch (\Exception $e) {
            $this->descripcionSalidaError($e, $tabla_desa);		
        }
        break;        

        case "plantilla":
            try {
                $tabla_desa = 'plantilla';			

                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                      `id` int(10) NOT NULL,
                      `user` varchar(200) NOT NULL,
                      `nombre` text NOT NULL,
                      `fecha_actu` datetime NOT NULL,
                      `tabla` varchar(20) NOT NULL,
                      `tmp` varchar(1) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`,`user`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                DB::connection($conexion)->statement($stm);	

                DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();

                $stm = "INSERT INTO ".$base.".".$tabla_desa." (
                        id, user, nombre, fecha_actu, tabla, tmp)
                            VALUES
                        (3,'','Asistencias','2019-04-03 08:32:55','9','0'),
                        (5,'','Datos Generales Empleados','2019-04-05 11:25:02','1','0'),
                        (6,'','Contratos de Empleados','2019-04-05 11:26:53','3','0'),
                        (7,'','Puesto por Empleado','2019-04-05 11:28:21','5','0'),
                        (8,'','Pago de Aguinaldo','2019-04-05 11:30:49','6','0'),
                        (10,'','INFONAVIT','2019-04-25 09:05:18','1','0'),
                        (11,'','FONACOT','2019-04-25 09:08:43','1','0'),
                        (12,'','INCAPACIDADES','2019-04-25 09:11:13','7','0')";
                DB::connection($conexion)->statement($stm);
            
                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                // descripcionSalida2($conexion, $base, $tabla_desa);        
            }catch (\Exception $e) {
                $this->descripcionSalidaError($e, $tabla_desa);		
            }
            break;

        case "plantilla_detalle":
            try {	

                $tabla_desa = 'plantilla_detalle';			

                // CREA LA TABLA EN BD DESARROLLO SI NO EXISTE
                $stm = "CREATE TABLE IF NOT EXISTS ".$base.".".$tabla_desa." (
                      `id` int(10) NOT NULL,
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                DB::connection($conexion)->statement($stm); 

                DB::connection($conexion)->table($base.".".$tabla_desa)->truncate();   

                $stm = "INSERT INTO ".$base.".".$tabla_desa." (
                        id, id_detalle, tipo, id_reporte,id_mapa, filtro, valor, valor2, orden, estatus)
                            VALUES
                        (3,1,'C','1','4','','','','1','1'),
                        (3,2,'C','1','5','','','','2','1'),
                        (3,3,'C','1','6','','','','3','1'),
                        (3,4,'C','9','2','','','','4','1'),
                        (3,5,'C','9','3','','','','5','1'),
                        (3,6,'C','9','5','','','','6','1'),
                        (3,7,'C','9','7','','','','7','1'),
                        (3,8,'C','9','9','','','','8','1'),
                        (3,9,'C','9','11','','','','9','1'),
                        (3,10,'F','9','2','BETWEEN','2019-03-01','2019-03-31','','1'),
                        (3,11,'O','1','4','','','','ASC','1'),
                        (3,12,'O','1','5','','','','ASC','1'),
                        (3,13,'O','1','6','','','','ASC','1'),
                        (5,1,'C','1','4','','','','1','1'),
                        (5,2,'C','1','5','','','','2','1'),
                        (5,3,'C','1','6','','','','3','1'),
                        (5,4,'C','1','7','','','','4','1'),
                        (5,5,'C','1','8','','','','5','1'),
                        (5,6,'C','1','12','','','','6','1'),
                        (5,7,'C','1','9','','','','7','1'),
                        (5,8,'C','1','42','','','','8','1'),
                        (5,9,'C','1','22','','','','9','1'),
                        (5,10,'C','1','21','','','','10','1'),
                        (5,11,'C','1','23','','','','11','1'),
                        (5,12,'C','1','20','','','','12','1'),
                        (5,13,'C','1','24','','','','13','1'),
                        (5,14,'C','1','33','','','','14','1'),
                        (5,15,'O','1','5','','','','ASC','1'),
                        (5,16,'O','1','6','','','','ASC','1'),
                        (5,17,'R','1','21','','','','','1'),
                        (5,18,'R','1','24','','','','','1'),
                        (5,19,'R','1','33','','','','','1'),
                        (6,1,'C','1','3','','','','1','1'),
                        (6,2,'C','1','4','','','','2','1'),
                        (6,3,'C','1','5','','','','3','1'),
                        (6,4,'C','1','6','','','','4','1'),
                        (6,5,'C','3','5','','','','5','1'),
                        (6,6,'C','3','3','','','','6','1'),
                        (6,7,'O','1','5','','','','ASC','1'),
                        (6,8,'O','1','6','','','','ASC','1'),
                        (6,9,'R','3','5','','','','','1'),
                        (7,1,'C','1','3','','','','1','1'),
                        (7,2,'C','1','4','','','','2','1'),
                        (7,3,'C','1','5','','','','3','1'),
                        (7,4,'C','1','6','','','','4','1'),
                        (7,5,'C','5','2','','','','5','1'),
                        (7,6,'C','1','9','','','','6','1'),
                        (7,7,'O','1','5','','','','ASC','1'),
                        (7,8,'O','1','6','','','','ASC','1'),
                        (7,9,'R','5','2','','','','','1'),
                        (8,1,'C','1','3','','','','1','1'),
                        (8,2,'C','1','4','','','','2','1'),
                        (8,3,'C','1','5','','','','3','1'),
                        (8,4,'C','1','6','','','','4','1'),
                        (8,5,'C','6','2','','','','5','1'),
                        (8,6,'C','6','4','','','','6','1'),
                        (8,7,'C','6','3','','','','7','1'),
                        (8,8,'C','1','9','','','','8','1'),
                        (8,9,'C','1','42','','','','9','1'),
                        (8,10,'O','1','5','','','','ASC','1'),
                        (8,11,'O','1','6','','','','ASC','1'),
                        (10,1,'C','1','3','','','','1','1'),
                        (10,2,'C','1','4','','','','2','1'),
                        (10,3,'C','1','5','','','','3','1'),
                        (10,4,'C','1','6','','','','4','1'),
                        (10,5,'C','5','2','','','','5','1'),
                        (10,6,'C','1','38','','','','6','1'),
                        (10,7,'C','1','37','','','','7','1'),
                        (10,8,'C','1','47','','','','8','1'),
                        (10,9,'F','1','47','=','\"Activo(s)\"','','','1'),
                        (10,10,'O','1','5','','','','ASC','1'),
                        (10,11,'O','1','6','','','','ASC','1'),
                        (11,1,'C','1','3','','','','1','1'),
                        (11,2,'C','1','4','','','','2','1'),
                        (11,3,'C','1','5','','','','3','1'),
                        (11,4,'C','1','6','','','','4','1'),
                        (11,5,'C','5','2','','','','5','1'),
                        (11,6,'C','1','38','','','','6','1'),
                        (11,7,'C','1','45','','','','7','1'),
                        (11,8,'C','1','47','','','','8','1'),
                        (11,9,'F','1','47','=','\"Activo(s)\"','','','1'),
                        (11,10,'O','1','5','','','','ASC','1'),
                        (11,11,'O','1','6','','','','ASC','1'),
                        (12,1,'C','1','3','','','','1','1'),
                        (12,2,'C','1','4','','','','2','1'),
                        (12,3,'C','1','5','','','','3','1'),
                        (12,4,'C','1','6','','','','4','1'),
                        (12,5,'C','7','2','','','','5','1'),
                        (12,6,'C','7','5','','','','6','1'),
                        (12,7,'C','7','4','','','','7','1'),
                        (12,8,'C','7','3','','','','8','1'),
                        (12,9,'C','1','47','','','','9','1'),
                        (12,10,'F','1','47','=','\"Activo(s)\"','','','1'),
                        (12,11,'O','1','5','','','','ASC','1'),
                        (12,12,'O','1','6','','','','ASC','1')";
                DB::connection($conexion)->statement($stm);					
            
                // IMPRIME RESULTADO DE LA TABLA MIGRADA
                // descripcionSalida2($conexion, $base, $tabla_desa);

            }catch (\Exception $e) {
                $this->descripcionSalidaError($e, $tabla_desa);		
            }
            break;
########################################################################################               
########################################################################################               
########################################################################################               
########################################################################################               
########################################################################################               
            }
        }

        dd($this->errores);
    }

    /**
     * 
     */
    protected function descripcionSalidaError($e, $tabla)
    {
        $this->errores[] = 'Error en la tabla: '. $tabla.'. Descripción: ' . $e;
    }
}
