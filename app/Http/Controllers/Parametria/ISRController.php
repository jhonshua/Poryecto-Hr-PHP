<?php

namespace App\Http\Controllers\parametria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ISRExport;
use App\Imports\ISRImport;

class ISRController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    /**
     * Pantalla de inicio de impuestos
     */
    public function inicio()
    {
        cambiarBase(Session::get('base'));
        $impuestos = DB::connection('empresa')->table('impuestos')
        ->where('estatus', 1)->get();
        return view('parametria.isr.inicio', compact('impuestos'));
    }

    /*
     * Exportar ISR
     */
    public function exportar()
    {
        return Excel::download( new ISRExport(), 'ISR_'.date('d-m-Y_H:i').'.xlsx' );
    }

    /**
     * Borrado de categoria de prestacion
     */
    public function borrar(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('impuestos')->where('id', $request->get('id'))->delete();
        return response()->json(['ok' => 1]);
    }

    /*
     *  Importar ISR
     */
    public function importar(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')->table('impuestos')->truncate();
        try{
            Excel::import( new ISRImport(), $request->file('isr_file'));

        }catch(\Exception $e){
            session()->flash('danger', 'Error al cargar el archivo intente nuevamente !!.');

            return redirect()->route('parametria.isr');
        }
        

        session()->flash('success', 'Se importó correctamente el archivo !!.');

        return redirect()->route('parametria.isr');

    }

    /**
     * Edición del nombre de depto
     */
    public function crearEditarISR(Request $request)
    {
        $request->validate([
            'tipo_tabla' => 'required',
            'limite_superior' => 'required',
            'limite_inferior' => 'required',
            'cuota_fija' => 'required',
            'porcentaje' => 'required',
        ]);

        cambiarBase(Session::get('base'));

        DB::connection('empresa')->table('impuestos')->updateOrInsert(
            ['id' => $request->get('id')],
            [
                'tipo_tabla' => $request->get('tipo_tabla'),
                'limite_inferior' => $request->get('limite_inferior'),
                'limite_superior' => $request->get('limite_superior'),
                'cuota_fija' => $request->get('cuota_fija'),
                'porcentaje' => $request->get('porcentaje'),
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            ]
        );

        return redirect()->route('parametria.isr')
            ->with('tipo_alerta', 'success')
            ->with('mensaje', 'El registro se guardó correctamente.');
    }

    public function editarISR(Request $request, $id)
    {
        cambiarBase(Session::get('base'));
        $datos_impuestos = DB::connection('empresa')->table('impuestos')->where('id', $id)->get();
        return view('parametria.isr.crear-editar-isr', compact('datos_impuestos'));
    }
}
