<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LoginUsuario
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
        $user=Auth::user();

        if(isset($user)){

            if($user->autofacturador && !$user->admin){
                cambiarBase($user->clientes);
                return redirect()->route('autofacturador.index');
            }
            else if($user->autofacturador && $user->admin){
                cambiarBase($user->clientes);
                return redirect()->route('autofacturador.administracion.index');
            }

        }

        return $next($request);
    }
}
