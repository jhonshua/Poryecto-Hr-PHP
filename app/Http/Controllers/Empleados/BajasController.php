<?php

namespace App\Http\Controllers\Empleados;

use App\Models\Empleado;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class BajasController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    public function bajaEmpleado()
    {
        tienePermisoA('bajas');

        cambiarBaseA(Session::get('base'));
        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
                        ->with('departamento')
                        ->orderBy('apaterno', 'asc')
                        ->get();
        $departamentos = Departamento::where('estatus', 1)->orderBy('nombre', 'asc')->get();

        return view('empleados.bajas-empleado', compact('empleados', 'departamentos'));
    }


}