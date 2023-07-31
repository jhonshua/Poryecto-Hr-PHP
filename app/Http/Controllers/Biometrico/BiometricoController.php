<?php

namespace App\Http\Controllers\biometrico;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Models\Biometrico\asignacionBiometrico;
use App\Models\Biometrico\huellaUsuario;
use App\Models\Biometrico;
use App\Models\Empleado;


class BiometricoController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
	public function asignarBiometrico(Request $request)
	{
        cambiarBase(Session::get('base'));
        $this->validate($request,[
            'id_biometrico' => 'required | numeric',
            'id_empleado' => 'required | numeric',
        ]);

        asignacionBiometrico::create($request->all());
        return response()->json(['exito' => true, 'mensaje' => 'Se asigno con exito el usuario']);
	}

	public function registrarHuella(Request $request)
	{
        cambiarBase(Session::get('base'));
        $this->validate($request,[
            'id_empleado' => 'required | numeric',
            'indice'  => ' required | numeric',
            'huella' => 'required ',
        ]);

        huellaUsuario::updateOrCreate(['id_empleado' => $request->input('id_empleado'),
                    'indice'  => $request->input('indice'),], [
                    'huella' => $request->input('huella'),
                ]); 
        return response()->json(['exito' => true, 'mensaje' => 'Se registro con exito la huella']);
	}
}