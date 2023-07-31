<?php 
	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;


Route::get('/formulario-inicio',  [App\Http\Controllers\Formularios\FormularioController::class, 'inicio'])->name('formularios.inicio');

Route::get('/formulario-datos-por-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'obtenerDatosPorEncuesta'])->name('formularios.obtenerDatosPorEncuesta'); //ok

Route::get('/formulario-asignar-encuesta',  [App\Http\Controllers\Formularios\FormularioController::class, 'asignarEncuesta'])->name('formularios.asignarEncuesta'); //ok

Route::get('/formulario-obtener-empleados-asignados', [App\Http\Controllers\Formularios\FormularioController::class, 'obtenerEmpleadosAsignados'])->name('formularios.obtenerEmpleadosAsignados'); //ok

Route::post('/formulario-pdf-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'pdfEncuesta'])->name('formularios.pdfEncuesta'); //ok

Route::get('/formulario-resultados-empleado', [App\Http\Controllers\Formularios\FormularioController::class, 'obtenerResultadosEmpleado'])->name('formularios.obtenerResultadosEmpleado'); //ok

Route::get('/formulario-cerrar-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'cerrarEncuesta'])->name('formularios.cerrarEncuesta'); //ok

Route::post('/formulario-agrega-empleado', [App\Http\Controllers\Formularios\FormularioController::class, 'agregaEmpleado'])->name('formularios.agregaEmpleado'); //ok

Route::post('/formulario-desasignar-formulario', [App\Http\Controllers\Formularios\FormularioController::class, 'desasignarFormulario'])->name('formularios.desasignarFormulario'); //ok

Route::get('/formulario-visualizar-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'visualizarEncuesta'])->name('formularios.visualizarEncuesta'); //ok

Route::get('/formulario-agregar-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'agregar'])->name('formularios.agregar'); //ok

Route::get('/formulario-obtiene-iconos', [App\Http\Controllers\Formularios\FormularioController::class, 'obtieneIconos'])->name('formularios.obtieneIconos'); //ok

Route::get('/formulario-obtiene-detalles-iconos', [App\Http\Controllers\Formularios\FormularioController::class, 'obtieneDetallesIconos'])->name('formularios.obtieneDetallesIconos');//ok

Route::post('/formulario/agregar-editar-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'agregarEditarEncuesta'])->name('formularios.agregarEditarEncuesta'); //ok

Route::get('/formulario/eliminar-pregunta', [App\Http\Controllers\Formularios\FormularioController::class, 'eliminarPregunta'])->name('formularios.eliminarPregunta'); //ok

Route::get('/formulario/cambiar-estatus-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'cambiaStatusEncuesta'])->name('formularios.cambiaStatusEncuesta'); //ok

Route::get('/test',[App\Http\Controllers\Formularios\FormularioController::class, 'agregarEncuestaCovid'])->name('configuracion.formularios.agregarEncuestaCovid');

Route::get('/formulario/exportar-resultados-excel',[App\Http\Controllers\Formularios\FormularioController::class, 'exportarExcelResultados'])->name('configuracion.formularios.exportarExcelResultados');

Route::post('/formulario-exportar-encuesta', [App\Http\Controllers\Formularios\FormularioController::class, 'exportarEncuesta'])->name('formularios.exportarEncuesta'); //ok


/*Catalogo configuracion de formularios*/
Route::get('/configuracion-formulario-inicio',[App\Http\Controllers\Formularios\ConfiguracionFormularioController::class, 'inicio'])->name('configuracion.formularios.inicio');

Route::get('/configuracion-formulario-agregar',function(){ return view('formularios.configuracion-formularios.agregar-iconos-formulario'); });

Route::post('/configuracion-formulario-agregar-editar',[App\Http\Controllers\Formularios\ConfiguracionFormularioController::class, 'agregarEditar'])->name('configuracion.formularios.agregarEditar');

Route::get('/configuracion-obtener-formulario',[App\Http\Controllers\Formularios\ConfiguracionFormularioController::class, 'obtenerFormularios'])->name('configuracion.formularios.obtenerFormularios');

Route::get('/configuracion-formulario-eliminar-item',[App\Http\Controllers\Formularios\ConfiguracionFormularioController::class, 'eliminarItem'])->name('configuracion.formularios.eliminarItem');

Route::get('/configuracion-formulario-deshabilitar-iconos',[App\Http\Controllers\Formularios\ConfiguracionFormularioController::class, 'deshabilitarIconos'])->name('configuracion.formularios.deshabilitarIconos');