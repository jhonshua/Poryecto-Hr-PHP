<?php

namespace App\Http\Controllers\Imss;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Empleado;
use App\Models\Incapacidad;
use App\Models\Departamento;


class IncapacidadesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function incapacidades()
    {

        tienePermiso('registro_incapacidades');
        
        cambiarBase(Session::get('base'));

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->with('incapacidadesActivas')
            ->with('departamento')
            ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')
            ->get();
        $departamentos = Departamento::where('estatus', 1)->orderBy('nombre', 'asc')->get()->keyBy('id');
        $periodos_nomina =  DB::connection('empresa')
            ->table('periodos_nomina')
            ->where('estatus', 1)
			->orderBy('id','desc')
            ->get();

    	return view('imss.incapacidades-inicio', compact('empleados', 'departamentos', 'periodos_nomina'));
    }


    public function crearActualizar(Request $request)
    {
        cambiarBase(Session::get('base'));

        $data = $request->except('_token');
        
        $data['fecha_edicion'] = date('Y-m-d H:i:s');
        if($request->id <= 0)  $data['fecha_creacion'] = date('Y-m-d H:i:s');
        Incapacidad::updateOrInsert(['id' => $request->id], $data);

        session()->flash('success', 'La incapacidad se guardó correctamente.');

        return redirect()->route('incapacidades.inicio');

    }


    public function detalleincapacidad($empleado)
    {
        cambiarBase(Session::get('base'));

        $empleado = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->with('incapacidadesActivas')
            ->with('departamento')
            ->where('id', $empleado)
            ->orderBy('apaterno', 'asc')
            ->first();
                        
        $periodos_nomina =  DB::connection('empresa')
            ->table('periodos_nomina')
            ->where('estatus', 1)
            ->get();

        return view('imss.incapacidades-detalle', compact('empleado', 'periodos_nomina'));
    }

    public function borrar(Request $request)
    {
        cambiarBase(Session::get('base'));
        Incapacidad::where('id',$request->id)->update(['estatus'=> 0]);
        logGeneral(Auth::user()->email, 'Catalogo Incapacidad ' . $request->id . ' DELETE', Session::get('base'), '');
        return response()->json(['ok' => 1]);
    }
}
