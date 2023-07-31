<?php 
	use Illuminate\Support\Facades\Route;
	use Illuminate\Http\Request;


    Route::name('empleado.')->controller(EmpleadoController::class)->group(function (){
        Route::get('/', 'bandeja')->name('inicio');
        // muestra la pantalla inicial del empleado
        Route::get('/bandeja', 'bandeja')->name('inicio');

        /* RECIBO DE NOMINA */
        Route::match(['get', 'post'],'/recibos', 'recibos')->name('recibosss');

        /* RECIBOS DE NOMINA */
        Route::get('/recibosNomina/pdf', 'generarReciboNominaPdf')->name('recibospdf');
        Route::get('/recibos-nomina/pdf/{id_periodo}/{xml}', 'generarReciboNominaPdf')->name('recibospdf');
        Route::get('/recibos-nomina/empleado/zip_pdf/{empleado}', 'zip_PDF_empleado')->name('nomina.zip_pdf');
        Route::get('/recibos-nomina/empleado/genera_pdf/{id_empleado}/{id_repo}/{xml}/{id_timbre}/{base}', 'genera_pdf')->name('nomina.ver_pdf');
        Route::get('/recibos-nomina/empleado/descarga_xml/{id}/{id_empleado}/{archivo}', 'download_soap_xml')->name('nomina.download_soap_xml');

    });


Route::name('empleado.')->controller(FormularioNormaController::class)->group(function (){
    //Norma 035
    Route::get("/norma","index")->name("norma");
    Route::post("/norma/personal","informacionPersonal")->name("norma.informacionPersonal");
    Route::post("/norma/personal/confirmar","confirmarInformacionPersonal")->name("norma.confirmarInformacionPersonal");
    Route::get("/norma/cuestionario","obtenerCuestionario")->name("norma.cuestionario");
    Route::post("/norma/cuestionario","obtenerCuestionario")->name("norma.cuestionario");
    Route::post("/norma/cuestionario/guarda","guardarRespuestas")->name("norma.cuestionario.guarda");
});



// Evaluaciones
Route::get('/evaluaciones', 'EmpleadoController@bandeja')->name('empleados.evaluaciones');

// prestamos
Route::get('/prestamos', 'PrestamosController@inicio')->name('empleados.prestamos');
Route::post('/prestamos/solicitar', 'PrestamosController@solicitar')->name('empleados.prestamos.solicitar');
Route::get('/prestamo/detalle/{prestamo}', 'PrestamosController@detalle')->name('empleados.prestamos.detalle');
Route::post('/prestamos/tipos-por-antiguedad/', 'PrestamosController@obtenerPrestamosPorAntiguedad')->name('empleados.prestamos.tiposporantiguedad');

// PRESTAMOS API
Route::get('/api/prestamos/{$id}', 'PrestamosController@inicio_')->name('empleados.api.prestamos');


Route::name('empleado.')->controller(FormularioEncuestaController::class)->group(function (){
    //FORMULARIO RESPUESTAS
    Route::get('/encuesta/inicio', 'inicio')->name('encuesta.inicio');
    Route::get('/encuesta/datos_generales_encuesta', 'obtieneDatosGenerales')->name('encuesta.obtieneDatosGenerales');
    Route::get('/encuesta/datoscuestionario', 'obtieneDatosCuestionario')->name('encuesta.datoscuestionario');
    Route::post('/encuesta/registarRespuestas', 'registarRespuestas')->name('encuesta.registarRespuestas');
    Route::get('/encuesta/obtener-aviso-info', 'obtenerInfoAviso')->name('encuesta.obtenerInfoAviso');

});




// SOLICITUD DE BENEFICIARIOS

Route::get('/solicitudes/inicio', [\App\Http\Controllers\Empleado\SolicitudesController::class,'inicio'])->name('empleado.solicitudes.inicio');
Route::get('/obtenerSolicitudes/general', [\App\Http\Controllers\Empleado\SolicitudesController::class,'obtenerSolicitudes'])->name('empleado.obtenerSolicitudes.general');
Route::get('/detalles/prestamo/{id}', [\App\Http\Controllers\Empleado\SolicitudesController::class,'obtenerDetallesDelPrestamo'])->name('empleado.obtenerSolicitudes.detalles.prestamo');

/* MI PERFLI */
Route::get('/mi-perfil', [\App\Http\Controllers\Empleado\MiPerfilEmpleadoController::class,'inicio'])->name('empleado.miPerfil');
