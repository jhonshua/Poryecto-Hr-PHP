<?php  
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Contabilidad\PolizaController;
/*PROCESOS DE CALCULO*/

/* NOMINAS */
Route::get('timbrar/nomina', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'nomina'])->name('timbrar.nomina')->middleware('permiso.timbrado.nomina');

Route::post('timbrar/nomina', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'timbrarNomina'])->name('timbrar.nomina_depto')->middleware('permiso.timbrado.nomina');

/* EMPLEADO */
Route::get('timbrar/validar/empleado/{id_empleado}/{cadena}/{periodo}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'validarEmpleado'])->name('timbrar.validar.empleado');

Route::get('timbrar/nomina/empleado/{id_empleado}/{cadena}/{tipo}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'timbrar_nomina_empleado'])->name('timbrar.nomina_empleado');

Route::get('timbrar/nomina/empleado/genera_pdf/{id_empleado}/{id_repo}/{xml}/{id_timbre}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'descargaPDF'])->name('timbrar.nomina_empleado.ver_pdf');

Route::get('timbrar/nomina/empleado/download_xml/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'download_xml'])->name('timbrar.nomina_empleado.descargar_xml');

Route::get('cancelar/empleado/{id}/{cadena}/{periodo}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'cancelar_cfdi'])->name('cancelar.timbre_empleado');

Route::get('/timbrar-nomina-empleado-download-xml/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'downloadXml'])->name('timbrar.nominaEmpleado.descargarXml');

Route::get('timbrar-nomina-empleado-download-soap-xml/{id_empleado}/{id_repo}/{xml}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'downloadSoapXml'])->name('timbrar.nominaEmpleado.descargarSoapXml');

Route::get('timbrar-nomina-empleado-verificar-estatus/{id}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'verificarEstatus'])->name('timbrar.nominaEmpleado.verificarEstatus');

Route::get('timbrar-validar-masivo/{id_periodo}/{cadena}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'validarMasivo'])->name('timbrar.validarMasivo');

Route::get('timbrar-nomina-bucle/{cadena}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'timbrarMasivoBucle'])->name('timbrar.nominaMasivoBucle');

Route::get('timbrar-nomina-empleado-genera-pdf-masivo/{periodo}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'pdfMasivo'])->name('timbrar.nomina.pdfMasivo');

Route::get('timbrar-nomina-empleado-email-masivo/{periodo}/{cadena}/{back}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'emailMasivo'])->name('timbrar.nomina.emailMasivo');

Route::get('timbrar-nomina-resumen-xml/{periodo_id}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'resumenCFDI'])->name('timbrado.nomina.resumen_xls');

//Timbrado EMpleado Controller
Route::get('/generar-pdf-masivo-timbrado/{periodo}', [App\Http\Controllers\Contabilidad\TimbradoEmpleadoController::class, 'generarpdfMasivo'])->name('timbrado.generarmasivo');

Route::get('/descargar-cdfis/{periodo}', [App\Http\Controllers\Contabilidad\TimbradoEmpleadoController::class, 'zip_CFDIS'])->name('timbrado.descargarcdfis');

Route::get('/descargar-zip_PDF/{periodo}', [App\Http\Controllers\Contabilidad\TimbradoEmpleadoController::class, 'zip_PDF'])->name('timbrado.descargar-zip_PDF');

//Poliza
Route::get('poliza',[PolizaController::class,'index'])->name('poliza.index');
Route::get('poliza/paginacion',[PolizaController::class,'polizaPaginacion'])->name('poliza.paginacion');
Route::get('poliza/factura/paginacion',[PolizaController::class,'facturaPaginacion'])->name('poliza.factura.paginacion');
Route::get("poliza/facturas/listado",[PolizaController::class,'facturas'])->name("poliza.facturas");
Route::post("poliza/exportar",[PolizaController::class,'exportar'])->name("poliza.exportar");
Route::get("factura/periodo/{id?}",[App\Http\Controllers\Contabilidad\FacturaController::class, 'periodo'])->name("factura.periodo");


//  F A C T U R A D O R
Route::get('/factura', [App\Http\Controllers\Contabilidad\FacturaController::class, 'index'])->name('factura.index');

Route::post('factura/insertar', [App\Http\Controllers\Contabilidad\FacturaController::class, 'insertar'])->name('factura.insertar');

Route::get('/factura/editar/{id}', [App\Http\Controllers\Contabilidad\FacturaController::class, 'editar'])->name('factura.editar');

Route::get('/factura/geteditar/{id}', [App\Http\Controllers\Contabilidad\FacturaController::class, 'getFactura'])->name('factura.getFactura');

Route::post('factura/actualizar', [App\Http\Controllers\Contabilidad\FacturaController::class, 'actualizar'])->name('factura.actualizar');

Route::get('factura/detalles/{id}', [App\Http\Controllers\Contabilidad\FacturaController::class, 'getDetalles'])->name('factura.detalles');

Route::post('factura/detalles', [App\Http\Controllers\Contabilidad\FacturaController::class, 'getDetalles'])->name('factura.detalles');

Route::post('factura/detalle/crear', [App\Http\Controllers\Contabilidad\FacturaController::class, 'detalleC'])->name('factura.detalle.crear');

Route::post('factura/detalle/update', [App\Http\Controllers\Contabilidad\FacturaController::class, 'updateDetalle'])->name('factura.detalle.update');

Route::post('factura/detalle/delete', [App\Http\Controllers\Contabilidad\FacturaController::class, 'deleteDetalle'])->name('factura.detalle.delete');

Route::get('factura/nueva/', [App\Http\Controllers\Contabilidad\FacturaController::class, 'nueva'])->name('factura.nueva');

Route::get("factura/ver/{id}", [App\Http\Controllers\Contabilidad\FacturaController::class, 'ver'])->name('factura.ver');

Route::get('timbrar/factura/{id}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'timbrarFactura'])->name('timbrar.factura');

Route::post('timbrar/factura', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'timbrarFactura'])->name('timbrar.factura');

Route::get('timbrar/genera_pdf/{id}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'downloadFacturaPdf'])->name('timbrar.factura.downloadFacturaPdf');

Route::get('timbrar/download_xml/{id}/{xml}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'downloadFacturaXml'])->name('timbrar.factura.downloadFacturaXml');

Route::get('cancelar/factura/{id}', [App\Http\Controllers\Contabilidad\TimbradoController::class, 'cancelarFactura'])->name('cancelar.cancelarFactura');
