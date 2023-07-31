<?php

namespace App\Console\Commands;

use App\Models\Permiso;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsuarioPermisoEmpresa extends Command
{
    /**
     * ejecutar comando:
     * php artisan make:make:usuarioPermisosEmpresa empresa000231
     */
    protected $signature = 'make:usuarioPermisosEmpresa {nombrebd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $nombrebd = $this->argument('nombrebd');

        //Todos los permisos existentes para la tabla permisos
        $permisos_columns = array(
            'parametria',
            'tabla_isr',
            'tabla_subsidios',
            'puestos_empresa',
            'departamentos_empresa',
            'tipo_prestaciones',
            'conceptos_nomina',
            'periodos_nomina',
            'conf_kit_baja',

            'procesos_calculo',
            'captura_incidencias',
            'timbrado_nomina',
            'timbrado_asimilados',
            'abrir_nomina',
            'aguinaldo',
            'timbrado_aguinaldo',
            'finiquitos',
            'timbrado_finiquito',
            'dispersion_bancaria',

            'empleados',
            'control_empleados',
            'cuentas_bancarias',
            'reingresos',
            'kit_baja',
            'asistencia',
            'prestaciones_extras',

            'imss',
            'registro_incapacidades',
            'movi_afiliatorios',

            'contabilidad',
            'control_polizas',
            'ctrl_fac',
            'facturador',

            'consultas',
            'control_credenciales',
            'reporte_asistencias',
            'reporte_acumulados_nomina',
            'recibos_nomina',
            'docu_empleados',
            'recibos_asimilados',
            'reporte_movi_personal',
            'indice_rotacion_personal',
            'reporte_nominas_periodo',
            'organigrama',

            'formularios',
            'encuesta_salida',
            'conf_formularios',

            'herramientas',
            'configuracion_empresa',
            'horarios_empleados',
            'vigencia_contratos',
            'solicitud_beneficiarios',
            'categoria_activos',
            'asignar_activos',
            'avisos_rh',

            'juridico',
            'demandas',
            'calendario_demandas',

            'norma035',

            'sistema',
            'usuarios_sistema',
            'usuarios_timbrado',
            'contratos_hrsystem',
            'conceptos_nomina_admin',
            'empresas_emisoras',
            'empresas_receptora',);

        cambiarBase((string) $nombrebd);
        $res = DB::table($nombrebd.'.permisos')->first();
        $res = array_keys((array) $res);
        echo 'Empresa: ' . $nombrebd . ' Permisos: ';
        foreach ($permisos_columns as $permiso) {
            if (!in_array($permiso, $res)) { // Si no encontramos la columna, la agregamos
                try{
                    Schema::table($nombrebd.'.permisos', function ($table) use ($permiso) {
                        $table->boolean($permiso)->default(0);
                    });
                    echo ' ' . $permiso . ',';
                }catch (\Exception $e){}
            }
        }


        $this->info("Columnas actualizadas");
    }
}
