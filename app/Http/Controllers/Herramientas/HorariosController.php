<?php

namespace App\Http\Controllers\herramientas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DiasFeriadosImport;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Departamento;

class HorariosController extends Controller
{

    public function inicio()
    {
        tienePermiso('horarios');

        cambiarBase(Session::get('base'));
        $horarios = Horario::orderBy('entrada', 'asc')->get();
        return view('herramientas.horarios.inicio', compact('horarios'));
    }

    public function nuevo()
    {
        tienePermiso('horarios');

        cambiarBase(Session::get('base'));
        
        return view('herramientas.horarios.nuevo');
    }

    public function crearEditarHorario(Request $request)
    {   
        try{

            cambiarBase(Session::get('base'));
            /**INDEFINIDO */
            $indefinido = $request->get('indefinido',0);

            if($indefinido == 1){
                $lunes            = 0;
                $martes           = 0;
                $miercoles        = 0;
                $jueves           = 0;
                $viernes          = 0;
                $sabado           = 0;
                $domingo          = 0;
                $indefinido       = 1;
                $lunes_entrada    = null;
                $lunes_salida     = null;
                $martes_entrada   = null;
                $martes_salida    = null;
                $miercoles_entrada= null;
                $miercoles_salida = null;
                $jueves_entrada   = null;
                $jueves_salida    = null;
                $viernes_entrada  = null;
                $viernes_salida   = null;
                $sabado_entrada   = null;
                $sabado_salida    = null;
                $domingo_entrada  = null;
                $domingo_salida   = null;
            }else{
                $lunes = $request->get('lunes', 0);
                $martes = $request->get('martes', 0);
                $miercoles = $request->get('miercoles', 0);
                $jueves = $request->get('jueves', 0);
                $viernes = $request->get('viernes', 0);
                $sabado = $request->get('sabado', 0);
                $domingo = $request->get('domingo', 0);
                $indefinido = $request->get('indefinido', 0);
                $lunes_entrada      = $request->get('lunes_entrada', null);
                $lunes_salida       = $request->get('lunes_salida', null);
                $martes_entrada     = $request->get('martes_entrada', null);
                $martes_salida      = $request->get('martes_salida', null);
                $miercoles_entrada  = $request->get('miercoles_entrada', null);
                $miercoles_salida   = $request->get('miercoles_salida', null);
                $jueves_entrada     = $request->get('jueves_entrada', null);
                $jueves_salida      = $request->get('jueves_salida', null);
                $viernes_entrada    = $request->get('viernes_entrada', null);
                $viernes_salida     = $request->get('viernes_salida', null);
                $sabado_entrada     = $request->get('sabado_entrada', null);
                $sabado_salida      = $request->get('sabado_salida', null);
                $domingo_entrada    = $request->get('domingo_entrada', null);
                $domingo_salida     = $request->get('domingo_salida', null);
            }


            $horario = array(
                'alias' => $request->get('alias', ''),
                'entrada' => $request->get('entrada', 0),
                'salida' => $request->get('salida', 0),
                'tolerancia' => $request->get('tolerancia', 0),
                'retardos' => $request->get('retardos', 0),
                'comida' => $request->get('comida', 0),
                'entrada_comida' => $request->get('entrada_comida', 0),
                'salida_comida' => $request->get('salida_comida', 0),
                'lunes' => $lunes,
                'martes' => $martes,
                'miercoles' => $miercoles,
                'jueves' => $jueves,
                'viernes' => $viernes,
                'sabado' => $sabado,
                'domingo' => $domingo,
                'indefinido' => $indefinido,
                'lunes_entrada' => $request->get('lunes_entrada', null),
                'lunes_salida' => $request->get('lunes_salida', null),
                'martes_entrada' => $request->get('martes_entrada', null),
                'martes_salida' => $request->get('martes_salida', null),
                'miercoles_entrada' => $request->get('miercoles_entrada', null),
                'miercoles_salida' => $request->get('miercoles_salida', null),
                'jueves_entrada' => $request->get('jueves_entrada', null),
                'jueves_salida' => $request->get('jueves_salida', null),
                'viernes_entrada' => $request->get('viernes_entrada', null),
                'viernes_salida' => $request->get('viernes_salida', null),
                'sabado_entrada' => $request->get('sabado_entrada', null),
                'sabado_salida' => $request->get('sabado_salida', null),
                'domingo_entrada' => $request->get('domingo_entrada', null),
                'domingo_salida' => $request->get('domingo_salida', null),
                'estatus' => 1
            );

            //dd($request->all(),$horario);
            Horario::updateOrInsert(
                    ['id' => $request->get('id')],
                    $horario                
                );

	        session()->flash('success', 'El registro se guardó correctamente.');

	        return redirect()->route('herramientas.horarios');

        } catch (\Exception $e) {

	        session()->flash('danger', 'Error al guardar la información contacta a tu administrador.');

	        return redirect()->route('herramientas.horarios');
        }
    }

    public function borrar(Request $request)
    {
        cambiarBase(Session::get('base'));
        Horario::where('id', $request->id)->delete();

        // eliminar dias feriados
        DB::connection('empresa')
            ->table('horarios_dias')
            ->where('id_horario', $request->id)
            ->delete();

        return response()->json(['ok' => 1]);
    }

    public function diasFeriados(Request $request)
    {
        cambiarBase(Session::get('base'));
        $dias = DB::connection('empresa')
        ->table('horarios_dias')
        ->where('id_horario', $request->horario)
        ->get();

        $id_horario = $request->horario;

        return view('herramientas.horarios.dias', compact('dias', 'id_horario'));
    }


    public function crearEditarDiasFeriados(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')
                ->table('horarios_dias')
                ->updateOrInsert(
                    ['id' => $request->get('idFeriado')],
                    [
                        'motivo' => strtoupper($request->get('motivo')),
                        'fecha_festiva' => $request->get('fecha_festiva'),
                        'id_horario' => $request->get('id_horario'),
                        'usuario_alta' => Auth::user()->email,
                    ]
                );

	        session()->flash('success', 'El día feriado se guardó correctamente.');

	        return redirect()->route('herramientas.festivos', $request->get('id_horario'));

    }

    public function borrarFeriado(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')
                ->table('horarios_dias')
                ->where('id', $request->id)
                ->delete();
        return response()->json(['ok' => 1]);
    }

    public function importarFeriados(Request $request)
    {
        cambiarBase(Session::get('base'));
        DB::connection('empresa')
                ->table('horarios_dias')
                ->where('id_horario', $request->id_horario)
                ->delete();

        Excel::import( new DiasFeriadosImport($request->id_horario), $request->file('feriados_file')
        );

	    session()->flash('success', 'Se importó correctamente el archivo.');

	    return redirect()->route('herramientas.festivos', $request->get('id_horario'));

    }

    public function clonarFeriados(Request $request)
    {
        cambiarBase(Session::get('base'));
        $horarios = Horario::where('estatus', 1)
                            ->whereNotIn('id', [$request->horario_base])
                            ->get();

        // eliminar dias feriados antiguos
        DB::connection('empresa')
            ->table('horarios_dias')
            ->whereIn('id_horario', $horarios)
            ->delete();

        // dias feriados a clonar
        $dias_feriados = DB::connection('empresa')
            ->table('horarios_dias')
            ->whereIn('id', $request->dias)
            ->get();

        foreach($horarios as $horario) {
            foreach($dias_feriados as $dia) {
                $dia->id = null;
                $dia->id_horario = $horario->id;
                $dia->usuario_alta = Auth::user()->email;
                DB::connection('empresa')
                    ->table('horarios_dias')
                    ->insert(collect($dia)->toArray());
            }
        }

	    session()->flash('success', 'Se clonaron correctamente los registros seleccionados.');

	    return redirect()->route('herramientas.festivos', $request->get('id_horario'));

    }

    public function empleados(Request $request)
    {
        cambiarBase(Session::get('base'));
        $horario = Horario::find($request->horario);
        $empleados = Empleado::select('id', 'nombre', 'apaterno', 'amaterno', 'id_departamento')
                            ->where('estatus', 1)
                            ->where('id_horario', $request->horario)
                            ->orderBy('nombre', 'asc')
                            ->get();
        $empleadosSinHorario = Empleado::select('id', 'nombre', 'apaterno', 'amaterno', 'id_departamento')
                            ->where('estatus', 1)
                            ->where('id_horario', 0)
                            ->orderBy('nombre', 'asc')
                            ->get();

        $deptos = Departamento::where('estatus', 1)
                                ->orderBy('nombre', 'asc')
                                ->get();
        $deptosArr = [];
        foreach ($deptos as $depto) {
            $deptosArr[$depto->id] = $depto->nombre;
        }

        return view('herramientas.horarios.empleados', compact('empleados', 'empleadosSinHorario', 'horario', 'deptosArr'));
    }

    public function asignarHorario(Request $request)
    {
        cambiarBase(Session::get('base'));
        $empleados = $request->empleados;
        $id_horario = $request->id_horario;

        if(!isset($empleados)){
            session()->flash('success', 'No hay datos de empleados.');
            return redirect()->route('herramientas.empleados', $id_horario);
        }

        Empleado::whereIn('id', $empleados)
                ->update([
                    'id_horario'=> $id_horario
                ]);

	    session()->flash('success', 'Los empleados se asignaron correctamente a este horario.');

	    return redirect()->route('herramientas.empleados', $id_horario);

    }

    public function desasignarHorario(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id_empleado)
                ->update(['id_horario'=> 0 ]);

	    session()->flash('success', 'El horario fue desasignado del empleado correctamente.');

	    return redirect()->route('herramientas.empleados', $request->id_horario);

    }

    public function estatus(Request $request)
    {
        cambiarBase(Session::get('base'));
        Horario::where('id', $request->id)->update(['estatus' => $request->estatus]);
        return response()->json(['ok' => 1]);
    }


}