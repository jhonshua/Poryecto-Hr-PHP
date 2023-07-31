<?php 

	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;


	/* Reporte de asistencias */
Route::get('/consultas/reporte-asistencias', [App\Http\Controllers\Consultas\ConsultasController::class, 'departamentosAsistencias'])->name('consultas.reporte-asistencias')->middleware('permiso.reporte.asistencia');

Route::post('/consultas/reporte-asistencias-tabla', [App\Http\Controllers\Consultas\ConsultasController::class, 'reporteAsistencias'])->name('consultas.reporte-asistencias.tabla');

Route::get('/consultas/reporte-asistencias-sync/{fecha?}/{idEmpresa?}', [App\Http\Controllers\Consultas\ConsultasController::class, 'sincronizarAsistencias'])->name('consultas.reporte-asistencias.sync');

Route::post('/consultas/reporte-asistencias-fechas', [App\Http\Controllers\Consultas\ConsultasController::class, 'fechasAsistencias'])->name('consultas.reporte-asistencias.fechas');


/*Recibos de nomina*/
Route::get('/consultas/recibos-nomina', [App\Http\Controllers\Consultas\RecibosNominaEmpleado::class, 'inicio'])->name('consultas.recibos.nomina.inicio')->middleware('permiso.reporte.recibo.nomina');

Route::get('timbrar-nomina-periodo/{id_periodo}', [App\Http\Controllers\Consultas\RecibosNominaEmpleado::class, 'timbrarNominaPeriodo'])->name('timbrar.nomina.periodo')->middleware('permiso.reporte.recibo.nomina');

/* timbress con periodo cerrado */

Route::get('timbrar-periodo-nomina-cerrada/{id_periodo}', [App\Http\Controllers\Consultas\RecibosNominaEmpleado::class, 'timbrarNominaPeriodocerrado'])->name('timbrar.periodo.nomina.cerrada')->middleware('permiso.reporte.recibo.nomina');

/* Reporte de índice de rotación personal */
Route::get('/indice-rotacion', [App\Http\Controllers\Consultas\ReporteRotacionController::class, 'indiceRotacionPersonal'])->name('reporte.rotacionPersonal');

Route::post('/busqueda-indice-rotacion', [App\Http\Controllers\Consultas\ReporteRotacionController::class, 'busquedaIndice'])->name('busqueda.rotacionPersonal');

Route::post('/exportar-indice-rotacion', [App\Http\Controllers\Consultas\ReporteRotacionController::class, 'exportarIndice'])->name('exportar.rotacionPersonal');

/* Reporte de movimiento de personal */
Route::get('/movimiento-personal', [App\Http\Controllers\Consultas\MovimientoPersonalController::class, 'movimientoPersonal'])->name('reporte.movimientoPersonal');

Route::post('/busqueda-movimiento-personal', [App\Http\Controllers\Consultas\MovimientoPersonalController::class, 'busquedaMovimiento'])->name('busqueda.movimientoPersonal');

Route::post('/exportar-movimiento-personal', [App\Http\Controllers\Consultas\MovimientoPersonalController::class, 'exportarMovimiento'])->name('exportar.movimientoPersonal');

/* Reporte de nóminas por periodo */
Route::get('/reporte-nomina', [App\Http\Controllers\Consultas\ReporteNominasPeriodoController::class, 'reporteNominas'])->name('reporte.nominasPeriodo');

Route::post('/docs-reporte-nomina', [App\Http\Controllers\Consultas\ReporteNominasPeriodoController::class, 'docsReporteNominas'])->name('docs.nominasPeriodo');

Route::get('/departamentos-reporte-nomina/{periodo}', [App\Http\Controllers\Consultas\ReporteNominasPeriodoController::class, 'departamentosReporteNorma'])->name('departamento.nominasPeriodo');

/* Reporte de acumulados de nomina */
Route::get('/acumulado-nomina', [App\Http\Controllers\Consultas\ReporteAcumuladoNominaController::class,  'acumuladoNomina'])->name('reporte.acumuladoNomina');

Route::post('/valida-acumulado-nomina', [App\Http\Controllers\Consultas\ReporteAcumuladoNominaController::class, 'validaAcumulado'])->name('reporte.validaAcumuladoNomina');


/*ORGANIGRAMA FER -DE MAURICIO*/
Route::get('/organigrama', [App\Http\Controllers\Consultas\OrganigramaController::class, 'inicio'])->name('organigrama.inicio');
Route::post('/organigrama-asignar-configuracion', [App\Http\Controllers\Consultas\OrganigramaController::class, 'asignarConfiguracion'])->name('organigrama.asignar.configuracion');

/* Reporte documentos de empleados */
Route::get('/doc-empleados', [App\Http\Controllers\Consultas\DocumentosEmpleadosController::class, 'docEmpleados'])->name('doc-empleados.tabla');

Route::get('/doc-empleados-exporta', [App\Http\Controllers\Consultas\DocumentosEmpleadosController::class, 'exportaDocEmpleados'])->name('doc-empleados.exporta');