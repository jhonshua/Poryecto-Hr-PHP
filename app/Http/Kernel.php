<?php

namespace App\Http;

use App\Http\Middleware\AdminHRSystem;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:10.1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin.hrsystem' => \App\Http\Middleware\AdminHRSystem::class,
        'permiso.impuesto' => \App\Http\Middleware\PermisoImpuestos::class,
        'permiso.subsidio' => \App\Http\Middleware\PermisoSubsidio::class,
        'permiso.prenomina' => \App\Http\Middleware\PermisoPrenomina::class,
        'permiso.timbrado.nomina' => \App\Http\Middleware\PermisoTimbradoNomina::class,
        'permiso.periodos.nomina' => \App\Http\Middleware\PermisoPeriodosNomina::class,
        'permiso.abrir.nomina' => \App\Http\Middleware\PermisoAbrirNomina::class,
        'permiso.empleados' => \App\Http\Middleware\PermisoEmpleados::class,
        'permiso.asistencia' => \App\Http\Middleware\PermisoAsistencia::class,
        'permiso.reporte.asistencia' => \App\Http\Middleware\PermisoReporteAsistencia::class,
        'permiso.reporte.recibo.nomina' => \App\Http\Middleware\PermisoReporteRecibosNomina::class,
        'permiso.configuracion.empresa' => \App\Http\Middleware\PermisoConfiguracionEmpresa::class,
        'loginUsuario' => \App\Http\Middleware\LoginUsuario::class,
        'autofacturador' => \App\Http\Middleware\Autofacturador::class,
    ];
}
