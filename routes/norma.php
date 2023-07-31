<?php 
	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;

/* NORMA */
/* Inicio administrativo */
Route::get('/norma-035', [App\Http\Controllers\Norma\NormaController::class, 'normaTabla'])->name('norma.normaTabla');


/*Implementacion*/

// Route::get("/norma/implementacion", [App\Http\Controllers\NormaController::class, 'cuestionarioPdf'] "ImplementacionController@inicio")->name("norma.implementacion");

Route::get("/norma-implementacion-cuestionario-pdf/{id}", [App\Http\Controllers\Norma\NormaController::class, 'cuestionarioPdf'])->name("norma.implementacion.cuestionario.pdf");

Route::post("/norma-implementacion-crear", [App\Http\Controllers\Norma\NormaController::class, 'crear'])->name("norma.implementacion.crear");
// Lista Empleados
Route::match(['get', 'post'],"/norma-implementacion-lista",[App\Http\Controllers\Norma\NormaController::class, 'listaEmpleados'])->name("norma.implementacion.lista.empleados");

Route::post("/norma-implementacion-generar-lista",[App\Http\Controllers\Norma\NormaController::class, 'generarListaEmpleados'])->name("norma.implementacion.lista.empleados.generar");

Route::post("/norma-implementacion-lista-importar",[App\Http\Controllers\Norma\NormaController::class,'importarListaEmpleados'])->name("norma.implementacion.lista.importar");

Route::post("/norma-implementacion-lista-crear-api",[App\Http\Controllers\Norma\NormaController::class, 'crearListaApi'])->name("norma.implementacion.lista.empleados.crear.api");

Route::post("/norma-implementacion-lista-empleado-remplazar-api",[App\Http\Controllers\Norma\NormaController::class, 'remplazarEmpleadoApi'])->name("norma.implementacion.lista.empleados.remplazar.api");

Route::post("/norma-implementacion-lista-empleado-admin-llenar-cuestionarios",[App\Http\Controllers\Norma\NormaController::class, 'llenarCuestionariosEmpleado'])->name("norma.implementacion.lista.empleados.admin.llenar.cuestionarios"); 

Route::post("/norma-implementacion-lista-empleado-correo",[App\Http\Controllers\Norma\NormaController::class, 'verCorreos'])->name("norma.implementacion.lista.empleados.correo"); 

Route::post("/norma-implementacion-lista-empleado-correo-operacion",[App\Http\Controllers\Norma\NormaController::class, 'operacionCorreo'])->name("norma.implementacion.lista.empleados.correo.operacion"); 

Route::post("/norma-implementacion-lista-empleados-recordatorio",[App\Http\Controllers\Norma\NormaController::class, 'recordatorioLlenadoNorma'])->name("norma.implementacion.lista.empleados.recordatorio");

Route::match(['get', 'post'],"/norma/implementacion-lista-empleados-resultados",[App\Http\Controllers\Norma\NormaController::class, 'resultadosEmpleado'])->name("norma.implementacion.lista.empleados.resultadosEmpleado"); 

Route::get("/norma-implementacion-cuestionarios-pdf/{id}",[App\Http\Controllers\Norma\NormaController::class, 'cuestionariosPdfTrabajador'])->name("norma.implementacion.cuestionarios.pdf.infTrabajador");

Route::get("/norma",[App\Http\Controllers\Norma\FormularioNormaController::class, 'inicio'])->name("empleado.norma");

Route::post("/norma-cuestionario-guarda",[App\Http\Controllers\Norma\FormularioNormaController::class, 'guardarRespuestas'])->name("empleado.norma.cuestionario.guarda");

Route::post("/norma-personal-confirmar",[App\Http\Controllers\Norma\FormularioNormaController::class, 'confirmarInformacionPersonal'])->name("empleado.norma.confirmarInformacionPersonal"); 


//Reporte
Route::match(['get', 'post'],"/norma-implementacion-reporte", [App\Http\Controllers\Norma\NormaController::class, 'reporteInicio'])->name("norma.implementacion.reporte");

Route::post("/norma-implementacion-reporte-generar", [App\Http\Controllers\Norma\NormaController::class, 'download'])->name("norma.implementacion.reporte.generar"); 

Route::post("/norma/implementacion/reporte/grafica/guardar", [App\Http\Controllers\Norma\NormaController::class, 'guardarGrafica'])->name("norma.implementacion.reporte.grafica.guardar"); 

// Diagnostico

Route::match(['get', 'post'],"/norma-implementacion-diagnostico", [App\Http\Controllers\Norma\ListaEmpleadosController::class, 'diagnosticoEmpleados'])->name("norma.implementacion.diagnostico");

Route::get('/norma-implementacion-diagnostico-exportar/{idNorma}', [App\Http\Controllers\Norma\ListaEmpleadosController::class, 'exportar'])->name('norma.implementacion.diagnostico.exportar');

Route::post("/norma-implementacion-diagnostico-menos16", [App\Http\Controllers\Norma\ListaEmpleadosController::class, 'diagnosticoEmpleadosMenosDiesiseis'])->name("norma.implementacion.diagnostico.menosdiesiseis");

Route::get('/norma-implementacion-diagnostico-exportar-menos16/{idNorma}', [App\Http\Controllers\Norma\ListaEmpleadosController::class, 'exportarMenosDiesiseis'])->name('norma.implementacion.diagnostico.exportarMenos16');

Route::get('/norma-035-nueva', [App\Http\Controllers\Norma\NormaController::class, 'normaNueva'])->name('norma.normaNueva');

/* Actividades */
Route::get('/norma-035-nueva', [App\Http\Controllers\Norma\NormaController::class, 'normaNueva'])->name('norma.normaNueva');

Route::match(['get', 'post'],'/norma-035-actividades',[App\Http\Controllers\Norma\NormaController::class, 'normaActividades'])->name('norma.actividades');


Route::get('/norma-035-actividades-exportar/{idImplementacion}', [App\Http\Controllers\Norma\ActividadController::class, 'exportarActividades'])->name('norma.actividades.exportar');

Route::post('/norma-035-actividades-ver', [App\Http\Controllers\Norma\ActividadController::class, 'verActividades'])->name('norma.actividades.ver');

Route::post('/norma-035-actividades-crear', [App\Http\Controllers\Norma\ActividadController::class, 'crearActividades'])->name('norma.actividades.crear');

Route::post('/norma-035-actividades-modificar', [\App\Http\Controllers\Norma\ActividadController::class, 'modificarActividades'])->name('norma.actividades.modificar');

Route::post('/norma-035-actividades-validarPeriodo', [\App\Http\Controllers\Norma\ActividadController::class, 'validarPeriodo'])->name('norma.actividades.validarPeriodo');

Route::post('/norma-035-actividades-borrar', [\App\Http\Controllers\Norma\ActividadController::class, 'borrarActividades'])->name('norma.actividades.borrar');

/* Calendario */
Route::match(['get', 'post'],'/norma-035-diagrama', [\App\Http\Controllers\Norma\ActividadController::class, 'diagramaActividades'])->name('norma.actividades.diagrama');

/* NORMA */
Route::get('/norma-035/crear-tablas', [App\Http\Controllers\Norma\NormaController::class, 'crearTablasNOM035'])->name('norma.crear.tablas');


/* Inicio administrativo */
Route::get('/norma-035', [App\Http\Controllers\Norma\NormaController::class, 'normaTabla'])->name('norma.normaTabla');



/*RUTAS NOM35 APLICA PARA ACTUALIZACION FER -DE MAURICIO */

Route::get('/nom035-exportar-empleados', [App\Http\Controllers\Norma\NormaController::class, 'exportEmpNoContestado'])->name('nom035.exportar.empleados');