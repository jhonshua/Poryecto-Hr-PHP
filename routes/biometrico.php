<?php 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::post('/asignar-biometrico', [App\Http\Controllers\Biometrico\BiometricoController::class, 'asignarBiometrico'])->name('biometrico.asignar');

Route::post('/agregar-huella', [App\Http\Controllers\Biometrico\BiometricoController::class, 'registrarHuella'])->name('biometrico.agregarhuella');


