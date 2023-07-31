<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Autofacturador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::user()->autofacturador == 0 && isset(Auth::user()->autofacturador)) {
            abort(403, "¡No tienes permisos suficentes.");
        }else if(Auth::user()->autofacturador && Auth::user()->admin==0 ){
            abort(403, "¡No tienes permisos suficentes.");
        }

        return $next($request);
    }
}
