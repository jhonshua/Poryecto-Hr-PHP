<?php 
	use Illuminate\Support\Facades\Route;

// Periodos Nomina -- Parametria Inicial
Route:: get('/periodos-nomina', [App\Http\Controllers\Procesos\NominaController::class, 'periodosNomina'])->name('nomina.periodos')->middleware('permiso.periodos.nomina');

Route::get('/crear-periodo-nomina', [App\Http\Controllers\Procesos\NominaController::class, 'crearperiodoNomina'])->name('nomina.crearperiodo')->middleware('permiso.periodos.nomina');

// PROCESOS CALCULO
// Captura de incidencias
Route::get('/periodos-nomina/prenomina', [App\Http\Controllers\Procesos\NominaController::class, 'prenomina'])->name('procesos.periodos.nomina.prenomina');

Route::get('/periodos-nomina/prenomina-exportar/{idPeriodo}', [App\Http\Controllers\Procesos\NominaController::class, 'prenominaExportar'])->name('procesos.periodos.nomina.prenomina.exportar');

Route::post('/periodos-nomina/prenomina-importar', [App\Http\Controllers\Procesos\NominaController::class, 'prenominaImportar'])->name('procesos.periodos.nomina.prenomina.importar');

Route::post('/periodos-nomina/prenomina-empleado', [App\Http\Controllers\Procesos\NominaController::class, 'prenominaEmpleado'])->name('procesos.periodos.nomina.prenomina.empleado');

Route::post('/periodos-nomina/prenomina-confirmar', [App\Http\Controllers\Procesos\NominaController::class, 'prenominaConfirmar'])->name('procesos.periodos.nomina.prenomina.confirmar');

Route::post('/agregar-periodo-nomina',[App\Http\Controllers\Procesos\NominaController::class, 'agregarperiodoNomina'])->name('nomina.agregarperiodo');

Route::post('/actualizar-periodo-nomina',[App\Http\Controllers\Procesos\NominaController::class, 'actualizarperiodoNomina'])->name('nomina.actualizarperiodo');

Route::post('/eliminar-periodo-nomina', [App\Http\Controllers\Procesos\NominaController::class, 'eliminarperiodoNomina'])->name('nomina.eliminarperiodo');

Route::get('/asistencia-periodo/{periodo}', [App\Http\Controllers\Procesos\NominaController::class, 'asistenciaPeriodo'])->name('nomina.asistencia')->middleware('permiso.periodos.nomina');

Route::post('/abrir-biometrico', [App\Http\Controllers\Procesos\NominaController::class, 'abrirBiometrico'])->name('nomina.abrirbiometrico');

Route::post('/actualizar-biometrico', [App\Http\Controllers\Procesos\NominaController::class, 'actualizarBiometrico'])->name('nomina.actualziarbiometrico');


Route::get('/reabrir-nomina/{periodo}', [App\Http\Controllers\Procesos\NominaController::class, 'reabrirNomina'])->name('nomina.reabrir');
Route::get('/abrir-nomina/', [App\Http\Controllers\Procesos\NominaController::class, 'abrirNomina'])->name('nomina.abrirNomina');

Route::get('/actualizar-periodo-biometrico/{idPeriodo}', [App\Http\Controllers\Procesos\NominaController::class, 'actualizar'])->name('nomina.actualizar');

Route::post('/cerrar-periodo', [App\Http\Controllers\Procesos\NominaController::class, 'cerrarperiodo'])->name('nomina.cerrarperiodo');

Route::post('/imprimir-nomina', [App\Http\Controllers\Procesos\NominaController::class, 'imprimirNomina'])->name('nomina.imprimirnomina');

Route::post('/exportar-reporte-excel', [App\Http\Controllers\Procesos\NominaController::class, 'exportarImprimir'])->name('nomina.exportarnomina');

Route::post('/exportar-reporte-detalle', [App\Http\Controllers\Procesos\NominaController::class, 'exportarDetalle'])->name('nomina.reportedetalle');

Route::post('/departamento-periodo', [App\Http\Controllers\Procesos\NominaController::class, 'departamentosPeriodo'])->name('nomina.deptoperiodo');


// Calculo nomina
Route::get('/calculo-nomina', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'calculonomina'])->name('calculo.nomina')->middleware('permiso.abrir.nomina');

Route::post('/revocar-nomina', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'revocarNomina'])->name('calculo.revocar');

Route::post('/calcular-nomina', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'calcularNomina'])->name('calculo.calcular');

Route::post('/confirmar-nomina', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'confirmarNomina'])->name('calculo.confirmar');

Route::post('/exportar-nomina', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'exportarCalculoNomina'])->name('calculo.exportar');

Route::post('/exportar-nomina-detalle', [App\Http\Controllers\Procesos\CalculoNominaController::class, 'exportarcalculonominaDetalle'])->name('calculo.exportardetalle');


//CALCULO AGUINALDO
Route::get('/calculo-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'inicio'])->name('procesos.calculo.aguinaldo');

Route::post('/precalculo-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'precalculoAguinaldo'])->name('procesos.pre.aguinaldo');

Route::post('/exportar-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'exportarAguinaldo'])->name('procesos.exportar.aguinaldo');

Route::post('/importar-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'importarAguinaldo'])->name('procesos.importar.aguinaldo');

Route::post('/recalculo-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'recalculoAguinaldo'])->name('procesos.recalculo.aguinaldo');

Route::post('/adicionales-aguinaldo', [App\Http\Controllers\Procesos\CalculoAguinaldoController::class, 'guardarDatosAdicionalesAguinaldo'])->name('procesos.guardarDatosAdicionalesAguinaldo');

//TIMBRADO AGUINALDO
Route::get('timbrar-aguinaldo', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'paso1'])->name('timbrar.aguinaldo.paso1');

Route::post('timbrar-aguinaldo', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'paso2'])->name('timbrar.aguinaldo.paso2');

Route::get('validar-timbre-aguinaldo-empleado/{id_empleado}/{cadena}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'validarEmpleado'])->name('validar.timbre.aguinaldo.empleado'); 

Route::get('timbrar-aguinaldo-validar-masivo/{ejercicio}/{cadena}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'validarMasivo'])->name('timbrar.aguinaldo.validar.masivo'); 

Route::get('timbrar-aguinaldo-bucle/{cadena}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'timbrarMasivoBucle'])->name('timbrar.aguinaldo.masivo.bucle'); 

Route::get('timbrar-aguinaldo-empleado/{id_empleado}/{cadena}/{tipo}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'timbrarAguinaldoEmpleado'])->name('timbrar.aguinaldo.empleado'); 

Route::get('pdf-timbrar-aguinaldo/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'generaPdf'])->name('pdf-timbrar-aguinaldo');

Route::get('timbrar-aguinaldo-soapxml/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'downloadSoapXml'])->name('timbrar.aguinaldo.descargar.soapxml'); 

Route::get('cancelar-timbre-aguinaldo/{id}/{deptos}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'cancelarCfdi'])->name('cancelar.timbre.aguinaldo');

Route::get('timbrar-aguinaldo-verificar-status/{id}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'verificarEstatus'])->name('timbrar.aguinaldo.verificar.estatus'); 

Route::get('timbrar-aguinaldo-cfdi-zipxml/{ejercicio}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'zipCFDIS'])->name('descarga.aguinaldo.zipxml'); 

Route::get('timbrar-aguinaldo-cfdi-zippdf/{ejercicio}', [App\Http\Controllers\Procesos\TimbradoAguinaldoController::class, 'zipPDF'])->name('descarga.aguinaldo.zippdf');


// F I N I Q U I T O

Route::get('/finiquito', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'inicio'])->name('procesos.finiquito');

Route::get('/finiquito-historico', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'inicioHistorico'])->name('procesos.historico');

Route::post('/finiquito-capturar', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'guardaCapturaFiniquito'])->name('procesos.finiquitocapturar');

// Route::post('/finiquito-conceptos-nomina', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'verConceptosNominaEmpleado'])->name('procesos.finiquitoconceptosnomina');

Route::post('/finiquito-conceptos-nomina-capturar/{id}', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'verConceptosNominaEmpleado'])->name('procesos.finiquitoconceptosnomina');



Route::match(['get', 'post'],'/finiquito-historico/{buscar_ejercicio?}', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'inicioHistorico'])->name('procesos.historico')->where(['buscar_ejercicio' => '[0-9]+']);

Route::post('/finiquito-historico-kit', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'subirArchivosFiniquito'])->name('procesos.finiquitosubirArchivos');

Route::get('/finiquito-encuesta-salida', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'validarEncuestaSalida'])->name('procesos.encuestasalida');

Route::post('/finiquito-calculadora', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'vistaCalculadora'])->name('procesos.finiquitocalculadora');

Route::post('/finiquito-calculadora-guardar', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'guardarCalculadora'])->name('procesos.finiquitocalculadoraguardar');

Route::post('/finiquito-calculo-kit', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'archivosBaja'])->name('procesos.calculo_finiquitopdfkit');

Route::post('/finiquito-exportar', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'exportarFiniquito'])->name('procesos.calculo_finiquitoexportar');


//TIMBRADO FINIQUITO
Route::get('timbrar-finiquito', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'inicio'])->name('timbrar.finiquito.inicio');

Route::get('validaciones-timbrado-finiquito/{id_empleado}/{factura}/{anio_ejercicio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'validacionesTimbradoFiniquito'])->name('validaciones.timbrado.finiquito');

Route::get('timbrar-finiquito-empleado/{id_empleado}/{anio}/{rutina}/{modalidad}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'timbrarFiniquitoEmpleado'])->name('timbrar.finiquito.empleado');

Route::get('finiquito-empleado-pdf/{id_empleado}/{id_repo}/{xml}/{id_timbre}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'generaPdfFiniquito'])->name('finiquito.empleado.pdf'); 

Route::get('imprimir-recibo-finquito/{idempleado}/{file_xml}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'imprimirReciboFiniquito'])->name('imprimir.recibo.finiquito');

Route::get('descargar-xml/{id_empleado}/{file_xml}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'descargarXml'])->name('descargar.xml');

Route::get('cancelar-cfdi/{id_empleado}/{factura}/{anio_ejercicio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'cancelarCfdi'])->name('cancelar.cfdi');

Route::get('descargar-finiquito-soap-xml/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'downloadSoapXml'])->name('descargar.finiquito.soapXml'); 

Route::get('descargar-comprobante-finiquito/{id_empleado}/{factura}/{anio_ejercicio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'descargarComprobanteFiniquito'])->name('descargar.comprobante.finiquito');

Route::get('verificar-estatus-finiquito/{id_empleado}/{folio}/{anio_ejercicio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'verificarEstatusFiniquito'])->name('verificar.estatus.finiquito');

Route::get('cfdi-cancelados-finiquito/{id_empleado}/{factura}/{id_periodo}/{anio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'verCfdiCancelados'])->name('ver.cfdi.cancelados.finiquito');

Route::get('validacion-masiva-finiquito/{anio_ejercicio}', [App\Http\Controllers\Procesos\TimbradoFiniquitoController::class, 'validacionMasiva'])->name('validacion.masiva.finiquito');

Route::post('/finiquito-ver', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'vistaCalculoFiniquitoVer'])->name('procesos.finiquitover');

Route::post('/calculo-finiquito-firma', [App\Http\Controllers\Procesos\CalculoFiniquitoController::class, 'firmaFiniquito'])->name('procesos.calculofiniquitofirma');

// ------------------------------------ D I S P E R S I O N E S ----------------------------------------------------------------------------------------

Route::match(['get', 'post'],'/dispersion', [App\Http\Controllers\Procesos\DispersionesController::class, 'inicio'])->name('procesos.dispersion.inicio');

Route::post('/dispersion/panelAdministracion', [App\Http\Controllers\Procesos\DispersionesController::class, 'panelAdminDispersion'])->name('procesos.dispersion.panelAdministracion');

Route::post('/dispersion/nomina/excel', [App\Http\Controllers\Procesos\DispersionesController::class, 'exportarExcel'])->name('procesos.dispersion.nomina.excel');

Route::post('/dispersion/nomina/totales', [App\Http\Controllers\Procesos\DispersionesController::class, 'exportarTotales'])->name('procesos.dispersion.nomina.totales');

Route::match(['get', 'post'],'/dispersion/panelAdministracion', [App\Http\Controllers\Procesos\DispersionesController::class, 'panelAdminDispersion'])->name('procesos.dispersion.panelAdministracion');

Route::post('/dispersion/panelAdministracion/periodo', [App\Http\Controllers\Procesos\DispersionesController::class, 'panelAdminDispersionPeriodo'])->name('procesos.dispersion.panelAdministracion.periodo');

Route::post('/dispersion/confirmar', [App\Http\Controllers\Procesos\DispersionesController::class, 'confirmarDispersion'])->name('procesos.dispersion.confirmar');

Route::post('/dispersion/panelAdministracion/gestion', [App\Http\Controllers\Procesos\DispersionesController::class, 'gestionaDispersion'])->name('procesos.dispersion.panelAdministracion.gestion');

Route::match(['get', 'post'],'/dispersion/ver', [App\Http\Controllers\Procesos\DispersionesController::class, 'verDatosDispersion'])->name('procesos.dispersion.ver');

Route::post('/dispersion/generaDiscoBancomer', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaDiscoBancomer'])->name('procesos.dispersion.generaDiscoBancomer');

Route::post('/dispersion/generaPAG', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaPAG'])->name('procesos.dispersion.generaPAG');

Route::post('/dispersion/generaXls', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaXls'])->name('procesos.dispersion.generaXls');

Route::post('/dispersion/generaXlsBanorteSPEI', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaXlsBanorteSPEI'])->name('procesos.dispersion.generaXlsBanorteSPEI');

Route::post('/dispersion/generaXlsBaz', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaXlsBaz'])->name('procesos.dispersion.generaXlsBaz');

Route::post('/dispersion/generaXlsBazMas', [App\Http\Controllers\Procesos\DispersionesController::class, 'generaXlsBazMas'])->name('procesos.dispersion.generaXlsBazMas');

