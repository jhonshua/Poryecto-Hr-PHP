<?php
// Obtiene empleados activos de una BD

Route::name('ajax.')->controller(AjaxController::class)->group(function (){
    Route::get('/ajax/obtiene-asistencias/{id}', 'obtieneAsistenciaEmpleados')->name('obtieneAsistencias');

    Route::get('/multimedia/inicio', 'listadoInicio')->name('listadoInicio');
    Route::get('/ajax/obtiene-salidas/{id}', 'obteneSalidas')->name('obteneSalidas');

});

