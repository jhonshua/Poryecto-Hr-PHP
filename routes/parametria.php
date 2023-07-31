<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
/*ISR*/

Route::get('/parametria/isr', [App\Http\Controllers\Parametria\ISRController::class, 'inicio'])->name('parametria.isr');

Route::post('/parametria/isr/borrar', [App\Http\Controllers\Parametria\ISRController::class, 'borrar'])->name('parametria.isr.borrar');

Route::view('/parametria/crear-editar-isr', 'parametria.isr.crear-editar-isr', ['name' => 'crear.editar.isr']);

Route::post('/parametria/guardar/isr', [App\Http\Controllers\Parametria\ISRController::class, 'crearEditarISR'])->name('parametria.guardar.isr');

Route::get('/parametria/editar/isr/{id}', [App\Http\Controllers\Parametria\ISRController::class, 'editarISR'])->name('parametria.editar.isr');

Route::post('/parametria/isr/importar', [App\Http\Controllers\Parametria\ISRController::class, 'importar'])->name('parametria.isr.importar');

Route::get('/parametria/isr/exportar', [App\Http\Controllers\Parametria\ISRController::class, 'exportar'])->name('parametria.isr.exportar');


/*SUBSIDIOS*/
Route::get('/parametria/subsidio', [App\Http\Controllers\Parametria\SubsidiosController::class, 'inicio'])->name('parametria.subsidio');

Route::get('/parametria/subsidio/exportar', [App\Http\Controllers\Parametria\SubsidiosController::class, 'exportar'])->name('parametria.subsidio.exportar');

Route::post('/parametria/subsidio/importar', [App\Http\Controllers\Parametria\SubsidiosController::class, 'importar'])->name('parametria.subsidio.importar');

Route::post('/parametria/subsidio/borrar', [App\Http\Controllers\Parametria\SubsidiosController::class, 'borrar'])->name('parametria.subsidio.borrar');

Route::view('/parametria/crear-editar-subsidio', 'parametria.subsidios.crear-editar-subsidio', ['name' => 'crear.editar.subsidio']);

Route::post('/parametria/guardar/subsidio', [App\Http\Controllers\Parametria\SubsidiosController::class, 'crearEditarSubsidio'])->name('parametria.guardar.subsidio');

Route::get('/parametria/editar/subsidio/{id}', [App\Http\Controllers\Parametria\SubsidiosController::class, 'editarSubsidio'])->name('parametria.editar.subsidio');


/*PUESTOS DE LA EMPRESA*/
Route::get('/parametria/puestos', [App\Http\Controllers\Parametria\PuestosController::class, 'inicio'])->name('parametria.puestos');

Route::post('/parametria/puestos/borrar', [App\Http\Controllers\Parametria\PuestosController::class, 'borrar'])->name('parametria.puestos.borrar');

Route::get('/parametria/crear-editar-puesto', [App\Http\Controllers\Parametria\PuestosController::class, 'vistaCrearEditar'])->name('parametria.puestos.crear.editar');

Route::post('/parametria/guardar/puesto', [App\Http\Controllers\Parametria\PuestosController::class, 'crearEditarPuesto'])->name('parametria.guardar.puesto');

Route::get('/parametria/editar/puesto/{id}', [App\Http\Controllers\Parametria\PuestosController::class, 'editarPuesto'])->name('parametria.editar.puesto');


/*Departamentos*/
Route::get('/departamentos-inicio',  [App\Http\Controllers\Parametria\DepartamentosController::class, 'inicio'])->name('parametria.departamentos.inicio');

Route::post('/departamentos-crear-editar',  [App\Http\Controllers\Parametria\DepartamentosController::class, 'crearEditar'])->name('parametria.departamentos.crearEditar');

Route::post('/departamentos-borrar',  [App\Http\Controllers\Parametria\DepartamentosController::class, 'borrar'])->name('parametria.departamentos.borrar');


/*Tipos de prestaciones*/
Route::get('/prestaciones-inicio', [App\Http\Controllers\Parametria\PrestacionesController::class, 'inicio'])->name('parametria.prestaciones.inicio');

Route::get('/prestaciones-listado', [App\Http\Controllers\Parametria\PrestacionesController::class, 'listado'])->name('parametria.prestaciones.listado');

Route::get('/prestaciones-agregar',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'agregar'])->name('parametria.prestaciones.agregar');

Route::post('/prestaciones-insertar',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'insertar'])->name('parametria.prestaciones.insertar');

Route::get('/prestaciones-modificar',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'modificar'])->name('parametria.prestaciones.modificar');

Route::post('/prestaciones-modificar-registros',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'modificarRegistros'])->name('parametria.prestaciones.modificarRegistros');

Route::get('/prestaciones-exportar/{id}',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'exportar'])->name('parametria.prestaciones.exportar');

Route::post('/prestaciones-importar',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'importar'])->name('parametria.prestaciones.importar');

Route::get('/prestaciones-crear-prestacion/{id}', function ($id) {
    return view('parametria.prestaciones.crear-prestacion', compact('id'));
});

Route::post('/prestaciones-insertar-prestacion',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'insertarPrestacion'])->name('parametria.prestaciones.insertarPrestacion');

Route::post('/prestaciones-borrar-prestacion',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'borrarPrestacion'])->name('parametria.prestaciones.borrarPrestacion');

Route::post('/prestaciones-borrar',  [App\Http\Controllers\Parametria\PrestacionesController::class, 'borrar'])->name('parametria.prestaciones.borrar');


/* Conceptos de nómina */
Route::get('/parametria/conceptos-nomina', [App\Http\Controllers\Parametria\ParametriaInicialController::class, 'conceptosNomina'])->name('parametria.conceptos-nomina');

Route::get('/parametria/editar-conceptos-nomina/{id}', [\App\Http\Controllers\Parametria\ParametriaInicialController::class, 'editarConceptosNomina'])->name('parametria.editar.conceptos-nomina');

Route::post('parametria/actualizar-conceptos-nomina', [\App\Http\Controllers\Parametria\ParametriaInicialController::class, 'actualizarConceptosNomina'])->name('parametria.actualizar-conceptos-nomina');


/* Prestaciones */
Route::post('/parametria/prestaciones', [\App\Http\Controllers\Parametria\ParametriaInicialController::class, 'actualizaEditaPrestaciones'])->name('parametria.prestaciones.crear.editar');

Route::post('/parametria/prestacion', [\App\Http\Controllers\Parametria\ParametriaInicialController::class, 'creaEditaPrestacion'])->name('parametria.prestacion');

Route::get('/puestos/reales/inicio', [App\Http\Controllers\Parametria\PuestosController::class, 'inicioPuestosReales'])->name('puestos.reales.inicio');
Route::post('/puestos/reales/guardar', [App\Http\Controllers\Parametria\PuestosController::class, 'guardarEditarPuestoReal'])->name('parametria.puesto.real.guardar');
Route::post('/puestos/reales/eliminar', [App\Http\Controllers\Parametria\PuestosController::class, 'borrarEditarPuestoReal'])->name('parametria.puesto.real.borrar');
Route::post('/puestos/reales/importar', [App\Http\Controllers\Parametria\PuestosController::class, 'importarPuestosReales'])->name('parametria.puestos.reales.importar');
Route::get('/puestos/obtener/puestos-reales', [App\Http\Controllers\Parametria\PuestosController::class, 'obtenerPuestosAlias'])->name('parametria.puestos.obtenerPuestosPorJerarquia');
Route::get('/puestos/obtener/puestos', [App\Http\Controllers\Parametria\PuestosController::class, 'obtenerPuestos'])->name('parametria.puestos.obtenerPuestos');
Route::get('/puestos/perfiles/inicio/{id}', [App\Http\Controllers\Parametria\PuestosController::class, 'inicioPerfilPuestos'])->name('puestos.perfilDescriptivo');
Route::post('/puestos/perfiles/editar', [App\Http\Controllers\Parametria\PuestosController::class, 'editarPerfilPuestos'])->name('puestos.editarPerfilDescriptivo');
Route::get('/puestos/perfiles/exportar/{tipo}/{id_puesto}', [App\Http\Controllers\Parametria\PuestosController::class, 'exportarPerfilPuestos'])->name('puestos.exportarPerfilDescriptivo');

/* Configuracion Kit de baja */
Route::get('/kit-baja', [App\Http\Controllers\Empleados\KitBajaController::class, 'camposKitBaja'])->name('kitbaja.tabla');

Route::post('/kit-baja-creaEdita', [App\Http\Controllers\Empleados\KitBajaController::class, 'crearEditarConfiguracion'])->name('kitbaja.creaEdita');

Route::post('/kit-baja-borrar', [App\Http\Controllers\Empleados\KitBajaController::class, 'borrarConfiguracion'])->name('kitbaja.borrar');
