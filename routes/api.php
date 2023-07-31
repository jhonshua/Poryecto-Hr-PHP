<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('api.')->controller(AjaxController::class)->group(function (){
    Route::get('/video/{id}', 'video')->name('video');
    Route::get('/cumpleaños/{id}', 'cumpleaños')->name('cumpleaños');
    Route::get('/avisos/{id}', 'listado')->name('avisos');
});
