<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
/*RUTAS AGRUPADAS DE AUTENTICACION*/
Auth::routes();

//Route::view('/', 'auth.login')->middleware('guest');
Route::get('loginQueretano', function(){
    return view('auth.loginQueretano');
})->name('loginQueretano');

Route::get('SystemLogout', function () {
    if (Auth::user()->base_autofacturador==6) {
        Auth::logout();
        return redirect()->route('loginQueretano');
    }
    else {
        Auth::logout();
        return redirect()->route('login');
    }
})->name('logout.system');



Route::get('/',['middleware' => 'loginUsuario', function () {
    $user = Auth::user();
    if($user){
        if($user->autofacturas && $user->admin)
            return redirect()->route('autofacturador.administracion.index');
        else if ($user->autofacturas && !$user->admin)
            return redirect()->route('autofacturador.index');
        else
            return redirect()->route('bandeja');
    }else{
        return redirect()->route('login');
    }
}]);

Route::get('inicio', function(){
    return redirect('/');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('loginUsuario');

/*********************************** */
/*        EMPLEADOS                */
/********************************** */

Route::name('empleado.')->controller(Empleado\EmpleadoController::class)->group(function (){
    Route::get('/empleado/login', 'showLoginForm')->name('loginpage');

    Route::post('/empleado/login', 'login')->name('login');

//loginApi
//Route::post('/empleado/login', 'EmpleadoController@loginApi')->name('empleado.login');

    Route::post('/empleado/recuperar', 'recuperarContrasena')->name('recuperar');
    Route::post('/empleado/logout','logout')->name('logout');

    Route::match(['GET','POST'],'/aviso-de-privacidad-empleado', 'avisoPrivacidadEmpleado')->name('avisoprivacidadempleado');
});


/*TERMINOS Y AVISO*/
Route::view('/terminos-y-condiciones', 'terms.terms');

Route::view('/aviso-de-privacidad', 'privacy.privacy');

Route::get('/bandeja', [App\Http\Controllers\HomeController::class, 'bandeja'])->name('bandeja')->middleware('admin.hrsystem');

Route::get('/calendario', [App\Http\Controllers\HomeController::class, 'calendario'])->name('calendario')->middleware('admin.hrsystem');

Route::post('/bitacora-cerrar', [App\Http\Controllers\HomeController::class, 'cerrarEvento'])->name('bandeja.cerrarEvento');

Route::post('/cerrar-evento', [App\Http\Controllers\HomeController::class, 'bitacoracerrarEvento'])->name('bandeja.cerrarevento');

Route::post('/cancelar-evento', [App\Http\Controllers\HomeController::class, 'bitacoracancelarEvento'])->name('bandeja.cancelarevento');

Route::post('/cerrar-nomina', [App\Http\Controllers\HomeController::class, 'cancelarNomina'])->name('bandeja.cerrarnomina');

/*CAMBIO DE EMPRESA*/
Route::post('/cambiar-empresa', [App\Http\Controllers\EmpresaController::class, 'cambiarEmpresa'])->name('empresa.cambiar');



/*CONSULTAS*/
Route::get('/prestaciones-modificar-prestacion',function(Request $request){
        $data = $request->all();
        return view('parametria.prestaciones.modificar-prestaciones',compact('data') );
    })->name('parametria.prestaciones.modificarPrestacion');


/* Pantalla */
Route::match(array('GET','POST'), '/pantalla', [\App\Http\Controllers\Herramientas\PantallaController::class,'ver'])->name('pantalla.ver');
Route::get('/pantallas/sindicato', [\App\Http\Controllers\Herramientas\PantallaController::class,'sindicato'])->name('pantalla.sindicato ');

/* VCARD */
Route::get('/vcard/{codigo?}', [App\Http\Controllers\Herramientas\VcardController::class,'vcard'])->name('herramientas.vcard');

