<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/* Parámetros de la empresa */


Route::post('/herramientas/editar-parametros',[App\Http\Controllers\Herramientas\HerramientasController::class,'editarParametros'])->name('herramientas.editar-parametros')->middleware('permiso.configuracion.empresa');

/**Avisos RH*/
Route::match(['GET','POST'], '/avisos-inicio',[App\Http\Controllers\Herramientas\AvisosController::class,'ver'])->name('herramientas.avisos.multimedia.inicio')->middleware('permiso.configuracion.empresa');

Route::post('/avisos-inicio/editar',[App\Http\Controllers\Herramientas\AvisosController::class,'editar'])->name('herramientas.avisos.multimedia.editar')->middleware('permiso.configuracion.empresa');

Route::post('/avisos-inicio/agregar',[App\Http\Controllers\Herramientas\AvisosController::class,'agregar'])->name('herramientas.avisos.multimedia.agregar')->middleware('permiso.configuracion.empresa');

Route::post('/avisos-inicio/multimedia/borrar',[App\Http\Controllers\Herramientas\AvisosController::class,'borrarMultimedia'])->name('herramientas.avisos.multimedia.multimedia.borrar')->middleware('permiso.configuracion.empresa');

Route::post('/avisos-inicio/multimedia/borrar/aviso',[App\Http\Controllers\Herramientas\AvisosController::class,'borrarAviso'])->name('herramientas.avisos.multimedia.multimedia.borrar.aviso')->middleware('permiso.configuracion.empresa');

Route::get('/herramientas/parametros', [App\Http\Controllers\Herramientas\HerramientasController::class, 'parametros'])->name('herramientas.parametros')->middleware('permiso.configuracion.empresa');

Route::post('/herramientas/editar-parametros', [App\Http\Controllers\Herramientas\HerramientasController::class, 'editarParametros'])->name('herramientas.editar-parametros')->middleware('permiso.configuracion.empresa');


/* Categoria de activos */
Route::get('/categoria-activos', [App\Http\Controllers\Herramientas\ActivosController::class, 'categoriaActivos'])->name('categoriaActivo.tabla');

Route::post('/categoria-activos-creaMod', [App\Http\Controllers\Herramientas\ActivosController::class, 'crearEditarCategoria'])->name('categoriaActivo.creaMod');

Route::post('/categoria-activos-elimina', [App\Http\Controllers\Herramientas\ActivosController::class, 'eliminaCategoria'])->name('categoriaActivo.elimina');

/*  Asignar activos */
Route::get('/activos', [App\Http\Controllers\Herramientas\ActivosController::class, 'asignaActivos'])->name('asignaActivos.tabla');

Route::get('/activos-creaMod', [App\Http\Controllers\Herramientas\ActivosController::class, 'crearEditarActivos'])->name('asignaActivos.creaMod');

Route::post('/activos-creaModifica', [App\Http\Controllers\Herramientas\ActivosController::class, 'crearModificarActivos'])->name('asignaActivos.creaModifica');

Route::post('/activos-elimina', [App\Http\Controllers\Herramientas\ActivosController::class, 'eliminaActivos'])->name('asignaActivos.elimina');

Route::get('/activos-eliminarArchivos', [App\Http\Controllers\Herramientas\ActivosController::class, 'eliminarArchivos'])->name('asignaActivos.eliminaArchivos');

Route::post('/activos-creaModArchivo', [App\Http\Controllers\Herramientas\ActivosController::class, 'creaModArchivo'])->name('asignaActivo.creaModArchivo');

Route::get('/activos-creaEliminaCampo', [App\Http\Controllers\Herramientas\ActivosController::class, 'creaEliminaCampo'])->name('asignaActivo.creaEliminaCampo');

Route::post('/activos-actualizaCampo', [App\Http\Controllers\Herramientas\ActivosController::class, 'actualizaCampo'])->name('asignaActivo.actualizaCampo');

Route::get('/activos-asignaEmpleado/{id}', [App\Http\Controllers\Herramientas\ActivosController::class, 'asignaEmpleado'])->name('asignaActivos.asignaEmpleado');

Route::post('/activos-asignarEmpleados', [App\Http\Controllers\Herramientas\ActivosController::class, 'asignarEmpleados'])->name('asignaActivos.asignarEmpleados');

Route::post('/activos-eliminaEmpleado', [App\Http\Controllers\Herramientas\ActivosController::class, 'eliminaEmpleados'])->name('asignaActivos.eliminaEmpleado');



//H O R A R I O S
Route::get('/horarios', [App\Http\Controllers\Herramientas\HorariosController::class, 'inicio'])->name('herramientas.horarios');

Route::get('/horarios/crear', [App\Http\Controllers\Herramientas\HorariosController::class, 'nuevo'])->name('herramientas.nuevo');

Route::post('/herramientas/crear-horario', [App\Http\Controllers\Herramientas\HorariosController::class, 'crearEditarHorario'])->name('herramientas.creareditar');

Route::post('/herramientas/borrar', [App\Http\Controllers\Herramientas\HorariosController::class, 'borrar'])->name('herramientas.borrar');

Route::get('/horarios/festivos/{horario}', [App\Http\Controllers\Herramientas\HorariosController::class, 'diasFeriados'])->name('herramientas.festivos');

Route::post('/herramientas/festivos/crear', [App\Http\Controllers\Herramientas\HorariosController::class, 'crearEditarDiasFeriados'])->name('herramientas.festivoscrearEditar');

Route::post('/herramientas/festivos/borrar', [App\Http\Controllers\Herramientas\HorariosController::class, 'borrarFeriado'])->name('herramientas.borrarferiado');

Route::post('/herramientas/festivos/importar', [App\Http\Controllers\Herramientas\HorariosController::class, 'importarFeriados'])->name('herramientas.importar');

Route::post('/herramientas/festivos/clonar', [App\Http\Controllers\Herramientas\HorariosController::class, 'clonarFeriados'])->name('herramientas.clonar');

Route::get('/horarios/empleados/{horario}', [App\Http\Controllers\Herramientas\HorariosController::class, 'empleados'])->name('herramientas.empleados');

Route::post('/herramientas/empleados', [App\Http\Controllers\Herramientas\HorariosController::class, 'asignarHorario'])->name('herramientas.asignarHorario');

Route::post('/herramientas/empleados/desasignar', [App\Http\Controllers\Herramientas\HorariosController::class, 'desasignarHorario'])->name('herramientas.desasignarHorario');

Route::post('/herramientas/estatus', [App\Http\Controllers\Herramientas\HorariosController::class, 'estatus'])->name('herramientas.estatus');

/* Solicitudes préstamos/prestaciones */
Route::get('/prestamos', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosTabla'])->name('prestamos.tabla');

Route::post('/prstamos-elimina', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosElimina'])->name('prestamos.elimina');

Route::post('/prestamos-asignaEjecutivo/{id}', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosAsignaEjecutivo'])->name('prestamos.asignaEjecutivo');

Route::post('/prestamos-crear', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosCrea'])->name('prestamos.crea');

Route::post('/prestamos-guardar', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosGuarda'])->name('prestamos.guarda');

Route::post('prestamos-obtenerEmpleados', [App\Http\Controllers\Herramientas\PrestamosController::class, 'prestamosObtenerEmpleados'])->name('prestamos.obtenerEmpleados');

Route::get('/prestamos/revisar/{prestamo}', [App\Http\Controllers\Herramientas\PrestamosController::class, 'revisar'])->name('prestamos.revisar');

Route::delete('/prestamos/documento/borrar/{documento}', [App\Http\Controllers\Herramientas\PrestamosController::class, 'borrarDocumento'])->name('prestamos.documento.borrar');

Route::post('/prestamos/notas/crear', [App\Http\Controllers\Herramientas\PrestamosController::class, 'crearNota'])->name('prestamos.notas.crear');
// Concluir prestamo
Route::put('/prestamos/cerrar/{prestamo}', [App\Http\Controllers\Herramientas\PrestamosController::class, 'cerrar'])->name('prestamos.cerrar');

//Exportar Prestamos
Route::post('/prestamos/exportar', [App\Http\Controllers\Herramientas\PrestamosController::class, 'exportar'])->name('prestamos.exportar');

// Notificar al usuario que hacen falta documentos/requisitos por llenar
Route::post('/prestamos/notificar', [App\Http\Controllers\Herramientas\PrestamosController::class, 'notificar'])->name('prestamos.documento.notificar');

/* Tipos préstamos/prestaciones */
Route::get('/tiposPrestamos', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'tiposPrestamosTabla'])->name('tiposPrestamos.tabla');

Route::post('/tiposPrestamos-crea', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'tiposPrestamosCrea'])->name('tiposPrestamos.crea');

Route::get('/tiposPrestamos-edita/{id}', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'tiposPrestamosEdita'])->name('tiposPrestamo.edita');

Route::post('/tiposPrestamo-actualiza', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'tiposPrestamosActualiza'])->name('tiposPrestamo.actualiza');

Route::post('/tiposPrestamo-elimina', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'tiposPrestamosElimina'])->name('tiposPrestamo.elimina');

/* Requisitos */
Route::post('/requisitosPrestamos-crea', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'requisitosCrea'])->name('requisitos.crea');

Route::post('/requisitosPrestamo-elimina', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'requisitosElimina'])->name('requisitos.elimina');


/* Generales app móvil */
Route::post('/generales-crea', [App\Http\Controllers\Herramientas\TiposPrestamosController::class,'agregarRequisitosGenerales'])->name('generales.crea');

Route::post('/generales-anexaDoc', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'anexarDocumentos'])->name('generales.anexaDoc');

Route::post('/generales-actualizaDoc', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'actualizarDocumentos'])->name('generales.actualizaDoc');

Route::post('/generales-eliminaDoc', [App\Http\Controllers\Herramientas\TiposPrestamosController::class, 'eliminaDocumentos'])->name('generales.eliminaDoc');

/* Módulo vcard*/
Route::get('/vcard/inicio', [App\Http\Controllers\Herramientas\VcardController::class,'inicioVcard'])->name('herramientas.inicioVcard');
Route::get('/vcard/getExistCard', [App\Http\Controllers\Herramientas\VcardController::class,'getExistCard'])->name('herramientas.getExistCard');
Route::post('/vcard/agregarVcard', [App\Http\Controllers\Herramientas\VcardController::class,'agregarVcard'])->name('herramientas.agregarVcard');
Route::get('/vcard/downloadVcard/{codigo}/{empresa}', [App\Http\Controllers\Herramientas\VcardController::class,'downloadVcard'])->name('herramientas.downloadVcard');