<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class PermisoSubsidio
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (empty(Auth::user())) {
            return redirect()->route('home');
        }else{
            if (!array_key_exists('subsidios', Session::get('usuarioPermisos')) || Session::get('usuarioPermisos')['subsidios'] == 0) {
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
