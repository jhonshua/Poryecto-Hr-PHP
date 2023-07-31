<?php 

use Illuminate\Support\Facades\Route;

Route::get('/empleados', [App\Http\Controllers\EmpleadosController::class, 'empleados'])->name('empleados.empleados');

Route::get('/empleados', 'EmpleadosController@empleados')->name('empleados.empleados');

Route::get('/empleados-encuesta/{empelado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'encuesta'])->name('empleados.encuesta');

Route::get('/empleados-catalogos', [App\Http\Controllers\Empleados\EmpleadosController::class, 'empleadosCatalogos'])->name('empleados.catalogos');

Route::get('/crear-empleado', [App\Http\Controllers\Empleados\EmpleadosController::class, 'crearEmpleado'])->name('empleados.crear')->middleware('permiso.empleados');

// Route::post('/agregar-empleado', [App\Http\Controllers\EmpleadosController::class, 'agregarEmpleado'])->name('empleados.agregar');
Route::post('/importar-empleados', [App\Http\Controllers\Empleados\EmpleadosController::class, 'importarEmpleados'])->name('empleados.importar')->middleware('permiso.empleados');

Route::get('/exportar-empleados', [App\Http\Controllers\Empleados\EmpleadosController::class, 'exportarEmpleados'])->name('empleados.exportar')->middleware('permiso.empleados');

Route::post('/cambiar-columnas-empleado', [App\Http\Controllers\Empleados\EmpleadosController::class, 'cambiarColumnas'])->name('empleados.cambiarcolumna');

Route::post('/agregar-empleado-paso-uno', [App\Http\Controllers\Empleados\EmpleadosController::class, 'agregarempleadopasoUno'])->name('empleados.pasouno');

Route::get('/crear-empleado-paso-dos/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'crearempleadopasoDos'])->name('empleados.creardos');

Route::post('/agregar-empleado-paso-dos', [App\Http\Controllers\Empleados\EmpleadosController::class, 'agregarempleadopasoDos'])->name('empleados.pasodos');

Route::get('/crear-empleado-paso-tres/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'crearempleadopasoTres'])->name('empleados.creartres');

Route::post('/agregar-empleado-paso-tres', [App\Http\Controllers\Empleados\EmpleadosController::class, 'agregarempleadopasoTres'])->name('empleados.pasotres');

Route::get('/crear-empleado-paso-cuatro/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'crearempleadopasoCuatro'])->name('empleados.crearcuatro');

Route::post('/agregar-empleado-paso-cuatro', [App\Http\Controllers\Empleados\EmpleadosController::class, 'agregarempleadopasoCuatro'])->name('empleados.pasocuatro');

Route::get('/crear-empleado-paso-cinco/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'crearempleadopasoCinco'])->name('empleados.crearcinco');

Route::post('/agregar-empleado-paso-cinco', [App\Http\Controllers\Empleados\EmpleadosController::class, 'agregarempleadopasoCinco'])->name('empleados.pasocinco');

Route::get('/info-empleado/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'infoEmpleado'])->name('empleados.info');

Route::get('/editar-empleado/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'editarEmpleado'])->name('empleados.editar');

Route::post('/actualizar-empleado', [App\Http\Controllers\Empleados\EmpleadosController::class, 'actualziarEmpleado'])->name('empleados.actualizarempleado');

Route::post('/actualizar-empleado-file', [App\Http\Controllers\Empleados\EmpleadosController::class, 'actualziarempleadoFile'])->name('empleados.actualizarempleadofile');

Route::post('/eliminar-empleado', [App\Http\Controllers\Empleados\EmpleadosController::class, 'eliminarEmpleado'])->name('empleados.eliminarempleado');

Route::get('/edicion-masiva-layout/{tipo}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'edicionmasivaLayout'])->name('empleados.edmasiva');

Route::post('/edicion-masiva', [App\Http\Controllers\Empleados\EmpleadosController::class, 'edicionMasiva'])->name('empleados.editmasiva');

Route::get('/percepciones-deducciones/{tipo}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'percepcionesDeducciones'])->name('empleados.percepcionesDeducciones');

Route::post('/guardar-deduccion', [App\Http\Controllers\Empleados\EmpleadosController::class, 'guardarDeduccion'])->name('empleados.guardardeduccion');

Route::post('/guardar-percepcion', [App\Http\Controllers\Empleados\EmpleadosController::class, 'guardarPercepcion'])->name('empleados.guardarperc');

Route::post('/estatus-deduccion', [App\Http\Controllers\Empleados\EmpleadosController::class, 'estatusdeduccionPercepcion'])->name('empleados.estatusdeduccion');

Route::get('/obtener-jefe-inmediato', [App\Http\Controllers\Empleados\EmpleadosController::class, 'obtenerJefeInmediato'])->name('empleados.obtenerJefeInmediato');

Route::get('/ver-contrato-empleado/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'verContrato'])->name('empleados.vercontrato');

Route::post('/comprobante-vacunacion', [App\Http\Controllers\Empleados\EmpleadosController::class, 'comprobanteVacunacion'])->name('empleados.comprobante.vacunacion');

/* COVID */
Route::get('/covid-inicio/{empleado}', [App\Http\Controllers\Empleados\CovidController::class, 'covidinicio'])->name('empleados.covidinicio')->middleware('admin.hrsystem');

Route::post('/registro-covid', [App\Http\Controllers\Empleados\CovidController::class, 'agregarRegisto'])->name('covid.agregar');

Route::get('/baja-empleado', [App\Http\Controllers\Empleados\CovidController::class, 'bajaEmpleado'])->name('baja.inicio');

Route::post('/registro-covid-editar', [App\Http\Controllers\Empleados\CovidController::class, 'editarRegistro'])->name('covid.editar');

Route::post('/registro-covid-eliminar', [App\Http\Controllers\Empleados\CovidController::class, 'eliminarRegistro'])->name('covid.eliminar');

Route::post('/guardar-encuesta', [App\Http\Controllers\Empleados\EmpleadosController::class, 'guardarEncuesta'])->name('empleados.guardarencuesta');

Route::post('/guardar-acuse', [App\Http\Controllers\Empleados\EmpleadosController::class, 'cargarAcuse'])->name('empleados.cargaracuse');

Route::get('/ver-acuse/{empleado}', [App\Http\Controllers\Empleados\EmpleadosController::class, 'verAcuse'])->name('empleados.veracuse');

Route::post('/baja-empleado', [App\Http\Controllers\Empleados\EmpleadosController::class, 'bajaempleado'])->name('empleados.bajaemp');


/*Asistencias*/
Route::get('/asistencias-inicio', [App\Http\Controllers\Empleados\AsistenciasController::class, 'inicio'])->name('empleado.asistencias.inicio')->middleware('permiso.asistencia');

// Asistencia de un empleado en especifico
Route::match(['GET','POST'], '/asistencias/empleado/{id}', [App\Http\Controllers\Empleados\AsistenciasController::class, 'detalle'])->name('empleado.asistencias.detalle')->middleware('permiso.asistencia');

Route::get('/asistencias-agregar-permiso', [App\Http\Controllers\Empleados\AsistenciasController::class, 'agregarPermiso'])->name('empleado.asistencias.agregarPermiso')->middleware('permiso.asistencia');

Route::post('/asistencias-permiso-personal', [App\Http\Controllers\Empleados\AsistenciasController::class, 'otorgarPermisoPersonal'])->name('empleado.asistencias.permisoPersonal')->middleware('permiso.asistencia');

Route::post('/asistencias-permiso-general', [App\Http\Controllers\Empleados\AsistenciasController::class, 'permisoGeneral'])->name('empleado.asistencias.permisoGeneral')->middleware('permiso.asistencia');

Route::post('/asistencias-sincronizacion-biometrico', [App\Http\Controllers\Empleados\AsistenciasController::class, 'registroAsistenciasCron'])->name('empleado.asistencias.registroAsistenciasCron')->middleware('permiso.asistencia');

Route::post('/asistencias-importar', [App\Http\Controllers\Empleados\AsistenciasController::class, 'importar'])->name('empleado.asistencias.importar')->middleware('permiso.asistencia');


/* CUENTAS BANCARIAS */
Route::get('/cuentas-bancarias', [App\Http\Controllers\Empleados\CuentasBancariasController::class,'verCuentas'])->name('cuentas.ver');

Route::post('/guardar-cuentas', [App\Http\Controllers\Empleados\CuentasBancariasController::class, 'guardarCuentas'])->name('cuentas.guardar');

Route::get('/exportar-cuentas', [App\Http\Controllers\Empleados\CuentasBancariasController::class, 'exportarCuentas'])->name('cuentas.exportar');

Route::post('/importar-cuentas', [App\Http\Controllers\Empleados\CuentasBancariasController::class, 'importarCuentas'])->name('cuentas.importar');

/* Reingresos */
Route::get('/reingresos', [App\Http\Controllers\Empleados\ReingresosController::class, 'tablaReingresos'])->name('reingresos.tabla');

Route::post('/reingreso-individual', [App\Http\Controllers\Empleados\ReingresosController::class, 'individualReingresos'])->name('reingresos.individual');

Route::post('/reingresos-masivos', [App\Http\Controllers\Empleados\ReingresosController::class, 'masivosReingresos'])->name('reingresos.masivo');

Route::get('/reingresos-info/{empleado}', [App\Http\Controllers\Empleados\ReingresosController::class, 'infoEmpleado'])->name('reingresos.info');


// PRESTACIONES EXTRAX
Route::get('/prestaciones-extras', [App\Http\Controllers\Empleados\PrestacionesExtrasController::class, 'inicio'])->name('prestaciones.extras.inicio');

Route::get('/prestaciones-extras-exportar', [App\Http\Controllers\Empleados\PrestacionesExtrasController::class, 'exportar'])->name('prestaciones.extras.exportar');

Route::post('/prestaciones-extras-importar', [App\Http\Controllers\Empleados\PrestacionesExtrasController::class, 'importar'])->name('prestaciones.extras.importar');

Route::post('/prestaciones-extras-guardar', [App\Http\Controllers\Empleados\PrestacionesExtrasController::class, 'guardar'])->name('prestaciones.extras.guardar');

//Alias Organigrama 
Route::get('/empleados-obtener-empleados-alias', [App\Http\Controllers\Empleados\EmpleadosController::class, 'obtenerEmpleadosAlias'])->name('empleados.obtener.alias');

//KIT BAJA EMPLEADOS

Route::post('/kit-baja-empleados', [App\Http\Controllers\Empleados\KitBajaController::class, 'subirArchivos'])->name('empleados.kitbajaempleadosubirArchivos');

Route::get('/kit-baja-tabla', [App\Http\Controllers\Empleados\KitBajaController::class, 'listKitBaja'])->name('empleados.kitBajaTabla');


//VACACIONES

Route::get('/empleados-vacaciones', [App\Http\Controllers\Empleados\VacacionesController::class, 'inicio'])->name('empleados.vacaciones');

Route::get('/empleados-calendario', [App\Http\Controllers\Empleados\VacacionesController::class, 'calendario'])->name('empleados.calendario');

Route::get('/empleados-actualizar-estatus', [App\Http\Controllers\Empleados\VacacionesController::class, 'actualiza_estatus'])->name('empleados.solicitud');

Route::get('/empleados-fechas-vacaciones', [App\Http\Controllers\Empleados\VacacionesController::class, 'datos_fechas'])->name('empleados.fechas.vacaciones');

Route::get('/empleado-validar-solicitud', [App\Http\Controllers\Empleados\VacacionesController::class, 'validar_solicitud_empleado'])->name('empleados.validar.solicitud.empleado');

Route::get('/empleados-autorizar-solicitud', [App\Http\Controllers\Empleados\VacacionesController::class, 'autoriza_solicitud'])->name('empleados.autorizar.solicitud');

Route::get('/empleados-cancelar-solicitud', [App\Http\Controllers\Empleados\VacacionesController::class, 'cancela_solicitud'])->name('empleados.cancelar.solicitud');

Route::get('/empleados-control-vacaciones', [App\Http\Controllers\Empleados\VacacionesController::class, 'controlVacaciones'])->name('empleados.control.vacaciones');

Route::post('/guardar-vacaciones', [App\Http\Controllers\Empleados\VacacionesController::class, 'guardar'])->name('empleados.guardar.vacaciones');


