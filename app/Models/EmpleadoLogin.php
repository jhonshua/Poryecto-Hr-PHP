<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpleadoLogin extends Model
{
    use HasFactory;
    protected $table = 'empleado_login';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';

    protected $rememberTokenName = false;
    protected $fillable = ['empresa', 'email', 'password', 'estatus', 'fecha', 'tmp','app','codigo'];
    protected $hidden = ['password'];

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
