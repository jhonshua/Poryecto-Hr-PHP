<?php 
	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;

/* D E M A N D A S  */
	Route::match(['get', 'post'],'/demandas', [App\Http\Controllers\Juridico\DemandasController::class, 'demandas'])->name('demandas.inicio');

	Route::post('/demandas-empleado/{idDemanda}', [App\Http\Controllers\Juridico\DemandasController::class, 'detalledemanda'])->name('demandas.detalle');

	Route::post('/editar-demanda', [App\Http\Controllers\Juridico\DemandasController::class, 'editardemanda'])->name('demandas.editar');

/*A U D I E N C I A S */

	Route::post('/juridico-audiencia', [App\Http\Controllers\Juridico\AudienciaController::class, 'iniciodemanda'])->name('demandas.audienciainicio');

	Route::post('/juridico-demanda-prejudicial', [App\Http\Controllers\Juridico\AudienciaController::class, 'verPre'])->name('demandas.audienciaprejudicial');

	Route::post('/juridico-prejudicial-editar', [App\Http\Controllers\Juridico\AudienciaController::class, 'editarPre'])->name('demandas.audienciaprejudicialeditar');

	Route::post('/juridico-prejudicial-agregar', [App\Http\Controllers\Juridico\AudienciaController::class, 'agregarPre'])->name('demandas.audienciaprejudicialagregar');

	Route::post('/juridico-prejudicial-guarda', [App\Http\Controllers\Juridico\AudienciaController::class, 'guardaJudicial'])->name('demandas.audienciaprejudicialguarda');

	Route::post('/juridico-constitucional-editar', [App\Http\Controllers\Juridico\AudienciaController::class, 'editaConstitucional'])->name('demandas.audienciaconstitucionaleditar');

	Route::post('/juridico-audiencias-masiva', [App\Http\Controllers\Juridico\AudienciaController::class, 'guardaMasiva'])->name('demandas.audienciamasivasguarda');

	Route::post('/juridico-evidencia-borrar', [App\Http\Controllers\Juridico\AudienciaController::class, 'borrarEvidencia'])->name('demandas.audienciaevidenciaborrar');


/*C A L E N D A R I O */

	Route::match(['get', 'post'],'/juridico-calendario', [App\Http\Controllers\Juridico\DemandasController::class, 'calendario'])->name('demandas.calendario');
 ?>