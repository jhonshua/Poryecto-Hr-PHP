<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $connection = 'singh';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    protected $fillable = [
        'id',
        'razon_social',
        'estatus',
        'num_notaria',
        'nombre_notario',
        'lugar_notaria',
        'otorgamiento_rdp',
        'giro',
        'representante_legal',
        'rfc',
        'tasa_vigente',
        'identificacion_oficial',
        'telefono',
        'email',
        'contacto_directo',
        'base',
        'repositorio',
        'calle_num',
        'colonia',
        'delegacion_municipio',
        'estado',
        'codigo_postal',
        'calles_referencia',
        'ins',
        'porcentaje_fondo',
        'activa_restricciones',
        'sede',
        'sss',
        'calculo_imss',
        'dias_imss',
        'lista_empleados',
        'timbrado',
        'norma',
        'tipo_asistencias',
        'confirmar_incidencias',
        'fecha_creacion',
        'fecha_edicion'
    ];

    const TABLAS_EMPRESA = [
        'crea_esquema',

        'empleados', /*Tablas que no estan asociadas*/
        'biometricos',
        'categorias',
        'ejercicios',
        'parametros',
        'periodos_nomina',
        'permisos',
        'demandas_juridico',
        'asignacion_contratos',
        'asistencias',
        'asistencia_horario',
        'avisos',
        'bitacora',
        'conceptos_nomina',
        'demandas_audiencias_bita',
        'departamentos',
        'documentos_empleados',
        'doc_empleados',
        'empleados_campos_extras',
        'empleados_deducciones',
        'empleados_informacion_extra',
        'empleados_percepciones',
        'eventos',
        'eventos_correos',
        'facturas',
        'facturas_detalle',
        'factura_periodo',
        'horarios',
        'horarios_dias',
        'impuestos',
        'incidencias_prg_log',
        'kit_baja_campos',
        'kit_baja_info',
        'logs',
        'modificaciones_salario',
        'plantilla',
        'plantilla_detalle',
        'puestos',
        'sedes',
        'saldo_nomina',
        'subsidios',
        'subsidios_b',
        'timbrado_cancelaciones_factura',
        'timbrado_cancelaciones_facturador',
        'timbrado_factura',
        'timbrado_facturador',
        'periodos_norma',
        'cuestionarios',
        'catalogos_norma',
        'razon_social',
        'sedes',

        'activos',
        'activos_archivos',
        'activos_campos_extra',
        'adic2018',
        'adic2019',
        'adic2020',
        'adic2021',
        'categorias_activos',
        'configuracion_formulario',
        'datos_facturacion2020',
        'datos_facturacion2021',
        'detalle_activos_empleados',
        'detalle_formulario_encuesta',
        'detalle_iconos_formularios',
        'formulario_encuesta',
        'formulario_opc_preguntas',
        'formulario_preguntas',
        'formulario_respuestas',
        'iconos_configformulario',
        'pendientes',
        'rutina_valores',
        'rutinas',
        'rutinas2018',
        'rutinas2019',
        'rutinas2020',
        'rutinas2021',
        'vcards',


        'aguinaldo', /*Asociado empleados*/
        'asignacion_biometricos', /*Asociado empleados,biometricos*/
        'avisos_multimedia', /*Asociado avisos*/
        'contratos', /*Asociado empleados*/
        'credenciales', /*Asociado empleados*/
        'demandas_audiencias', /*Asociado demandas_juridico*/
        'dispersiones', /*Asociado empleados,periodos_nomina*/
        'dispersiones_aguinaldo', /*Asociado empleados*/
        'empleados_prestaciones_extra', /*Asociado empleados*/
        'encuesta', /*Asociado empleados*/
        'evaluacion_desempeno', /*Asociado empleados*/
        'evidencias_audiencias', /*demandas_audiencias*/
        'fortalezas', /*Asociado empleados*/
        'huellas_empleado', /*Asociado empleados*/
        'incapacidades', /*Asociado empleados*/
        'incidencias_prg', /*Asociado empleados,conceptos_nomina*/
        'involucrados_audiencias', /*Asociado demandas_audiencias*/
        'involucrados_demandas', /*Asociado demandas_juridico*/
        'kpis', /*Asociado empleados,evaluacion_desempeno*/
        'log_incidencias', /*Asociado empleados*/
        'modificaciones_sueldo', /*Asociado empleados*/
        'prestaciones', /*Asociado categorias*/
        'prestaciones_extras', /*Asociado empleados*/
        'provisiones_facturacion', /*Asociado empleados,periodos_nomina*/
        'timbrado', /*Asociado empleados,periodos_nomina*/
        'timbrado_aguinaldo', /*Asociado empleados, periodos_nomina*/
        'timbrado_cancelaciones', /*Asociado empleados, periodos_nomina*/
        'timbrado_cancelaciones_aguinaldo', /*Asociado empleados, periodos_nomina*/
        'timbrado_cancelaciones_finiquito', /*Asociado empleados, periodos_nomina*/
        'timbrado_finiquito', /*Asociado empleados, periodos_nomina*/
        'registro_covid', /*Asociado empleados*/
        'contactos_covid', /*Asociado empleados,registro_covid,*/
        'evidencia_covid', /*Asociado registro_covid*/
        'bloques_cuestionario', /*Asociado cuestionarios*/
        'preguntas', /*Asociado catalogos_norma*/
        'bloque_preguntas', /*Asociado bloques_cuestionario,preguntas*/
        'informacion_trabajadores', /*Asociado catalogos_norma,*/
        'cuestionarios_trabajadores', /*Asociado cuestionarios,periodos_norma,puestos*/
        'periodos_implementacion', /*Asociado razon_social*/
        'encargados', /*Asociado periodos_implementacion*/
        'interpretaciones', /*Asociado catalogos_norma,periodos_implementacion*/
        'respuestas_cuestionarios', /*Asociado cuestionarios_trabajadores,preguntas*/
        'totales_clasificacion', /*Asociado catalogos_norma,cuestionarios_trabajadores*/
        'excentos_norma', /*periodos_norma*/
        'actividades', /*Asociado periodos_implementacion,periodos_norma*/

        'datos_facturacion2019', /*Asociado periodos_nomina*/
        'empleados_prestaciones_extras', /*Asociado empleados*/
        /*'formulario_tipo', *//*Se queda por revicion,Asociado empleados,evaluacion_desempeno*/
        'interpretaciones', /*Asociado catalogos_norma,periodos_implementacion*/
        'periodos_implementacion', /*Asociado razon_social*/
        'tempora_suma_fini', /*Asociado empleados*/
        'vcards_info', /*Asociado vcards*/

        'comprobante_vacunacion', /*No se encuentran en EmpresaReceptoraController*/
        'perfil_puesto',
        'puestos_alias',
        'puestos_detalle',
    ];
}
