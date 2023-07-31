<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Empleado;
use App\Models\Bancos;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasBancariasExport;
use App\Imports\CuentasBancariasImport;

class CuentasBancariasController extends Controller
{
    public function verCuentas()
    {
        tienePermisoA('cuenta_banco');
        cambiarBase(Session::get('base'));

        $empleados = Empleado::whereIn('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')->get();
        
        $bancos = Bancos::orderBy('nombre', 'asc')
            ->get();

        return view('empleados_admin.cuentas-bancarias.cuentas-bancarias', compact('empleados', 'bancos'));
    }

    public function guardarCuentas(Request $request)
    {
        
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update($request->except('_token'));

        return response()->json(['ok' => 1]);
    }

    public function exportarCuentas(){
        return Excel::download(new CuentasBancariasExport, 'EmpleadosCuentasBancarias_'.date('d-m-Y_H:i').'.xlsx');
    }

    public function importarCuentas(Request $request){
        try {
          
            Excel::import(new CuentasBancariasImport(), $request->file('archivo'));
           
        } catch (\Throwable $th) {
            dd($th);
            
            session()->flash('danger', 'Error al cargar el archivo intente nuevamente !!.');

            return redirect()->route('cuentas.ver');
        
        }
       
        session()->flash('success', 'El archivo se importó correctamente !!.');

        return redirect()->route('cuentas.ver');
        
    }
}
