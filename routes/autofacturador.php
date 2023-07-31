<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::name('autofacturador.')->controller(AutofacturaController::class)->group(function (){
    Route::get('/', 'index')->name('index');
    Route::get('/cfdi_pdf/{id}', 'cfdi_pdf')->name('cfdi_pdf');

    Route::get('orden-de-compra/{id?}', 'nuevaFactura')->name('nuevaFactura');
    Route::post('store', 'store')->name('store');
    Route::post('update', 'update')->name('update');
    Route::post('cancelar-proceso', 'cancelarProceso')->name('cancelarProceso');
    Route::post('eliminar-orden-compra', 'eliminarOrden')->name('eliminarOrden');

    Route::get('catalogos/{catalogo?}', 'getCatalogos')->name('catalogos');
    Route::get('get-invoices', 'getInvoices')->name('getInvoices');
    Route::get('get-invoices-cancelados', 'getInvoicesCancelados')->name('getInvoicesCancelados');
    Route::get('get-invoices-pendientes-rechazados', 'getInvoicesPendientesRechazados')->name('getInvoicesPendientesRechazados');
    Route::get('download/contrato/{id?}', 'downloadContrato')->name('downloadContrato');
    Route::get('download/zip-cfdi/{id?}', 'downloadzipCfdi')->name('downloadzipCfdi');
    Route::get('download/zip-cfdi-pago/{id?}', 'downloadzipCfdiPago')->name('downloadzipCfdiPago');
    Route::get('get-cfdi/{id?}', 'getCfdi')->name('getCfdi');

    Route::post('store-datos-fiscales', 'storeDatosFiscales')->name('storeDatosFiscales');
    Route::get('get-dato-fiscal/{id?}', 'getDatoFiscal')->name('getDatoFiscal');

    Route::post('comprobante', 'storeComprobante')->name('storeComprobante');
    Route::get('comprobante/{id}', 'showComprobante')->name('showComprobante');
    Route::post('downloadComprobante/{id}', 'downloadComprobante')->name('downloadComprobante');

    Route::get('comision/{id}', 'showComision')->name('showComision');

    Route::post('retorno/usuario', 'storeRetornoUsuario')->name('storeRetornoUsuario');
    Route::post('retorno', 'storeRetorno')->name('storeRetorno');
    Route::get('retorno/{id}', 'showRetorno')->name('showRetorno');

    Route::get('retorno/usuario/{id}', 'showRetornoUsuario')->name('showRetornoUsuario');

    Route::get('get-comprobante/{id?}', 'getComprobante')->name('getComprobante');
});

Route::get('base-autofacturador', [\App\Http\Controllers\Autofactura\AdministradorController::class,'getBaseAutofacturador'])->name('autofacturador.getBaseAutofacturador');
Route::get('base-autofacturadors', [\App\Http\Controllers\Autofactura\AdministradorController::class,'getRelBaseAutofacturador'])->name('autofacturador.getRelBaseAutofacturador');

Route::get('vendedor', [\App\Http\Controllers\Autofactura\AdministradorController::class,'getVendedor'])->name('autofacturador.administracion.getVendedor');
Route::post('comprobante/confirmar', [\App\Http\Controllers\Autofactura\AdministradorController::class,'confirmarComprobante'])->name('autofacturador.administracion.confirmarComprobante');

Route::middleware(['autofacturador'])->prefix('administracion')->name('autofacturador.administracion.')->controller(AdministradorController::class)->group(function () {
    Route::get('administrador', 'index')->name('index');
    Route::get('administracion-recursos', 'recursosVew')->name('recursosVew');

    Route::get('get-pending-invoices', 'getInvoices')->name('getInvoices');
    Route::get('get-invoices', 'getInvoicesFull')->name('getInvoicesFull');
    Route::get('get-registro-administrativo/{tipo?}/{id?}', 'getRegistroAdmin')->name('getRegistroAdmin');
    Route::get('get-cfdi/{id?}', 'getCfdi')->name('getCfdi');
    Route::post('eliminar-empresa', 'eliminarEmpresa')->name('eliminarEmpresa');
    Route::get('download/contrato/{id?}', 'downloadContrato')->name('downloadContrato');
    Route::post('rechazar-orden-compra', 'rechazarOP')->name('rechazarOP');
    Route::post('set-registro-administrativo/{tipo?}', 'setRegistroAdmin')->name('setRegistroAdmin');

    Route::post('aprobar-oc', 'aprobarOC')->name('aprobarOC');
    Route::post('timbrar', 'timbrar')->name('timbrar');
    Route::post('cancelar-timbre', 'cancelarTimbre')->name('cancelarTimbre');
    Route::get('recargar-cfdi-pdf/{id?}', 'reloadPDF')->name('reloadPDF');
    Route::get('recargar-cfdi-pagos-pdf/{id?}', 'reloadPDFPagos')->name('reloadPDFPagos');

    Route::post('timbrar-comprobante','timbrarPagoComprobante')->name('timbrarPagoComprobante');
    Route::post('cancelar-timbre-comprobante','cancelarCfdiPago')->name('cancelarCfdiPago');

    Route::post('eliminar-etiqueta/', 'eliminarEtiqueta')->name('eliminarEtiqueta');
    Route::post('eliminar-productos-servicios/', 'eliminarProductosServicios')->name('eliminarProductosServicios');
    Route::post('eliminar-regimen-fiscal/', 'eliminarRegimenFiscal')->name('eliminarRegimenFiscal');
    Route::post('eliminar-Unidades/', 'eliminarUnidades')->name('eliminarUnidades');
    Route::post('eliminar-UsoCFDI/', 'eliminarUsoCFDI')->name('eliminarUsoCFDI');

    Route::post('download/completados', 'downloadCompletados')->name('downloadCompletados');

    Route::post('comision', 'storeComision')->name('storeComision');
    Route::get('totalsubtotal/{id}', 'totalsubtotal')->name('totalsubtotal');

    Route::post('scraping/emisora/sat', 'scapingEmpresaEmisora')->name('scapingEmpresaEmisora');

    Route::post('download/cfdi_xml', 'downloadCfdiXml')->name('downloadCfdiXml');
    Route::get('get-cfdiTimbrado/{tipo?}/{id?}', 'getCFDITimbrado')->name('getCFDITimbrado');
});
