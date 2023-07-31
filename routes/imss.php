<?php 
	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;


	Route::get('/incapacidades', [App\Http\Controllers\Imss\IncapacidadesController::class, 'incapacidades'])->name('incapacidades.inicio');

	Route::post('/incapacidades-empleados', [App\Http\Controllers\Imss\IncapacidadesController::class, 'crearActualizar'])->name('incapacidades.actualizar');

	Route::get('/incapacidades-empleado/{empleado}', [App\Http\Controllers\Imss\IncapacidadesController::class, 'detalleincapacidad'])->name('incapacidades.detalle');

	Route::post('/incapacidades-borrar', [App\Http\Controllers\Imss\IncapacidadesController::class, 'borrar'])->name('incapacidades.borrar');

// A F  I L I A C I O N E S
	Route::get('/afiliaciones', [App\Http\Controllers\Imss\AfiliacionesController::class, 'inicio'])->name('afiliaciones.inicio');

	Route::get('/afiliaciones/bajas', [App\Http\Controllers\Imss\AfiliacionesController::class, 'bajas'])->name('afiliaciones.bajas');

	Route::get('/afiliaciones/modificaciones', [App\Http\Controllers\Imss\AfiliacionesController::class, 'modificaciones'])->name('afiliaciones.modificaciones');

	Route::post('/afiliaciones-guardar-folio-alta', [App\Http\Controllers\Imss\AfiliacionesController::class, 'guardarFolio'])->name('afiliaciones.guardarFolio');

	Route::post('/afiliaciones-cierre-folio-alta', [App\Http\Controllers\Imss\AfiliacionesController::class, 'cierreFolio'])->name('afiliaciones.cierreFolio');

	Route::post('/afiliaciones-guardar-folio-baja', [App\Http\Controllers\Imss\AfiliacionesController::class, 'guardarFolioBaja'])->name('afiliaciones.guardarFolioBaja');

	Route::post('/afiliaciones-cierre-folio-baja', [App\Http\Controllers\Imss\AfiliacionesController::class, 'cierreFolioBaja'])->name('afiliaciones.cierreFolioBaja');

	Route::post('/afiliaciones-guardar-folio-modificacion', [App\Http\Controllers\Imss\AfiliacionesController::class, 'guardarFolioModificacion'])->name('afiliaciones.guardarFolioModificacion');

	Route::post('/afiliaciones-cierre-folio-modificacion', [App\Http\Controllers\Imss\AfiliacionesController::class, 'cierreFolioModificacion'])->name('afiliaciones.cierreFolioModificacion');


	Route::get('/afiliaciones/generar-disco', [App\Http\Controllers\Imss\AfiliacionesController::class, 'generarDisco'])->name('afiliaciones.generarDisco');

	Route::get('/afiliaciones/generar-disco-baja', [App\Http\Controllers\Imss\AfiliacionesController::class, 'generarDiscoBaja'])->name('afiliaciones.generarDiscoBaja');
	
	Route::get('/afiliaciones/generar-disco-modificaciones', [App\Http\Controllers\Imss\AfiliacionesController::class, 'generarDiscoModificaciones'])->name('afiliaciones.generarDiscoModificaciones');
