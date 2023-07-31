<?php


namespace App\Http\Controllers\parametria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubsidioExport;
use App\Imports\SubsidioImport;

class SubsidiosController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    public function inicio()
    {
        cambiarBase(Session::get('base'));
        $subsidios = DB::connection('empresa')->table('subsidios')->where('estatus', 1)->get();

        return view('parametria.subsidios.inicio', compact('subsidios'));
    }

    /*
     * Exportar ISR
     */
    public function exportar()
    {
        return Excel::download(new SubsidioExport(), 'Subsidios_' . date('d-m-Y_H:i') . '.xlsx');
    }

    /*
     *  Importar ISR
     */
    public function importar(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('subsidios')->truncate();

        Excel::import(
            new SubsidioImport(),
            $request->file('subsidios_file')
        );
        return redirect()->route('parametria.subsidio')
            ->with('tipo_alerta', 'success')
            ->with('mensaje', 'Se importó correctamente el archivo.');
    }

    /**
     * Borrado de Subsidios
     */
    public function borrar(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('subsidios')->where('id', $request->get('id'))->delete();
        return response()->json(['ok' => 1]);
    }

    /**
     * Edición del nombre de depto
     */
    public function crearEditarSubsidio(Request $request)
    {
       
        $request->validate([
            'tipo_tabla' => 'required',
            'ingreso_desde' => 'required',
            'ingreso_hasta' => 'required',
            'subsidio' => 'required',
        ]);

        cambiarBase(Session::get('base'));

        DB::connection('empresa')->table('subsidios')->updateOrInsert(
            ['id' => $request->get('id')],
            [
                'tipo_tabla' => $request->get('tipo_tabla'),
                'ingreso_desde' => $request->get('ingreso_desde'),
                'ingreso_hasta' => $request->get('ingreso_hasta'),
                'subsidio' => $request->get('subsidio'),
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            ]
        );

        return redirect()->route('parametria.subsidio')
            ->with('tipo_alerta', 'success')
            ->with('mensaje', 'El registro se guardó correctamente.');
    }

    public function editarSubsidio(Request $request, $id)
    {
        cambiarBase(Session::get('base'));

        $datos_subsidios = DB::connection('empresa')->table('subsidios')->where('id', $id)->get();

        return view('parametria.subsidios.crear-editar-subsidio', compact('datos_subsidios'));
    }
}
