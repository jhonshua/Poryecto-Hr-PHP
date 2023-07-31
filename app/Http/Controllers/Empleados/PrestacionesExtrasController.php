<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Empleado;
use App\Exports\PrestacionesExtrasExport;
use App\Imports\PrestacionesExtrasImport;
use Illuminate\Support\Facades\DB;

class PrestacionesExtrasController extends Controller
{
    public function inicio(){
        tienePermisoA('prestaciones_extras');
        cambiarBase(Session::get('base'));        
        $empleados = Empleado::select('*', 'empleados.id as empId', 'prestaciones_extras.estatus as estatus_extras')
                        ->where('empleados.estatus', [Empleado::EMPLEADO_ACTIVO])
                        ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
                        ->leftJoin('prestaciones_extras', 'empleados.id', '=', 'prestaciones_extras.id_empleado')
                        ->orderBy('apaterno', 'asc')
                        ->get();
        // return $empleados;
        return view('empleados_admin.prestaciones-extras.inicio', compact('empleados'));
    }

    public function exportar()
    {
        return Excel::download(new PrestacionesExtrasExport,'EmpleadosPrestacioniesExtras_'.date('d-m-Y_H:i').'.xlsx');
    }

    public function importar(Request $request)
    {      
        try{  
            $prestacionesExtras_import = new PrestacionesExtrasImport;   
            Excel::import($prestacionesExtras_import, $request->file('prestaciones_extras_file'));        
            $resultados = $prestacionesExtras_import->getImportedResults();
            
            $errores = $resultados['errores'];
            $importados = $resultados['importados'];
            $mensajeDeError = $resultados['mensajeDeError'];
            $tipo_alerta = '';

            if($errores <= 0) {
                $tipo_alerta = 'success';
            }else if($errores > 0 && $importados > 0) {
                $tipo_alerta = 'warning';
            }else if($errores > 0 && $importados <= 0) {
                $tipo_alerta = 'danger';
            }            

            $resultados = 'Se procesó correctamente el archivo.';
            if($errores > 0) {
                $resultados .= '<br> Se encontraron los siguientes errores('.$errores.'): <br><br>';
                $resultados .= $mensajeDeError;
            }
            $resultados .= '<br>Se importaron '.$importados.' registros.';

            session()->flash('success', $resultados);
            
            return redirect()->route('prestaciones.extras.inicio');
                            // ->with('tipo_alerta', $tipo_alerta)
                            // ->with('mensaje', $resultados);
        }catch(\Exception $e){
            session()->flash('danger', 'Error al cargar el archivo, intente nuevamente !!.');
            return redirect()->route('prestaciones.extras.inicio');
        }
    }

    public function guardar(Request $request)
    {
        try{
            // return response()->json([$request->valor_plan_espejo]);
            // exit;
            cambiarBase(Session::get('base'));
            DB::connection('empresa')->table('prestaciones_extras')->updateOrInsert(
                [
                    'id_empleado' => $request->id
                ],
                [
                    'estatus' => $request->estatus,
                    'num_certificado' => $request->numero_certificado,
                    'valor_seguro_GM' => $request->valor_seguro_gastos_m,
                    'valor_plan_espejo' => $request->valor_plan_espejo,
                    'fecha_edicion' => date('Y-m-d H:i:s'),
                ]
            );
            return response()->json(['ok' => 1]);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
