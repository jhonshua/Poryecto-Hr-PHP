<?php

namespace App;

use App\Models\Empleado;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EmpleadoLogin extends Authenticatable
{
    protected $table = 'empleado_login';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    protected $rememberTokenName = false;

    protected $fillable = [
        'empresa', 'email', 'password', 'estatus', 'fecha', 'tmp','app','codigo'
    ];

    protected $hidden = [
        'password'
    ];

    public function crearCuentaEmpleado($email, $bdEmpresa, $estatus)
    {
        $existe = EmpleadoLogin::where('email', $email)
                                ->where('empresa', $bdEmpresa)
                                ->first();
        if(!$existe){
            $empleado = new Empleado();
            $password = $empleado->generarPassword();
            $codigo = $empleado->generarPassword(10);
            EmpleadoLogin::create([
                'email' => $email,
                'password' => bcrypt($password),
                'empresa' => $bdEmpresa,
                'estatus' => $estatus,
                'tmp' => $password,
                'app' => 0,
                'codigo' => $codigo,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
