<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\QueryException;
use App\Models\Biometrico;


class BiometricosController extends Controller
{
    protected const BIO_ACTIVO = 1;
    protected const BIO_INACTIVO = 0;
    protected const BIO_ELIMINADO = 2;

    public function ver(Request $request)
    {
        if ($request->ajax()) {
            $bio = new Biometrico();
            return $data = $bio->traeTodos(Session::get('base'));
        }
        return view('biometricos.inicio');
    }

    public function getBiometricos()
    {
        cambiarBase(Session::get('base'));
        $biometrico = Biometrico::all();
        return $biometrico;
    }

    public function crear(Request $request)
    {

        $this->validate($request, [
            'nombre' => 'required',
            'ip' => 'required',
            'puerto' => 'required | numeric',
            'modelo' => 'required',
            'firmware' => 'required',
            'mac' => 'required',
            'plataforma' => 'required',
            'num_serie' => 'required',
            'proveedor' => 'required',
        ]);
        cambiarBase(Session::get('base'));

        try {
            Biometrico::create($request->all());
        } catch (QueryException $e) {
            //dd($e);
            if ($e->getCode() == "23000") {
                return response()->json('El Biometrico ya existe', 500);
            } else {
                return response()->json('Error al agregar el biometrico ', 500);
            }
        } catch (Exception $e) {
            return response()->json('error 2', 500);
        }
        return response()->json(['exito' => 'true'], 200);
    }

    public function eliminar($id)
    {
        cambiarBase(Session::get('base'));
        $biometrico = Biometrico::findOrFail($id);
        $biometrico->delete();
        return response()->json(['exito' => 'true'], 200);
    }


}
