<?php

namespace App\Http\Controllers\parametria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Prestacion;
use App\Models\Categoria;
use App\Models\ConceptosNomina;
use App\Models\CodigosSat;

class ParametriaInicialController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    /* Inicia vista de conceptos de nómina */
    public function conceptosNomina()
    {
        cambiarBase(Session::get('base'));
        $conceptosNomina = ConceptosNomina::where('estatus', 1)
            ->where('file_rool', '<>', 0)
            ->orderBy('tipo', 'desc')
            ->get();

        return view('parametria.conceptos-nomina.conceptos-nomina', compact('conceptosNomina'));
    }

    /* Actualizar conceptos de nómina */
    public function actualizarConceptosNomina(Request $request)
    {
        $request->validate([
            'name_cuenta' => 'required',
            'cuenta_contable' => 'required'
        ]);

        $data = [
            'name_cuenta' => $request->get('name_cuenta'),
            'cuenta_contable' => $request->get('cuenta_contable', ""),
            'debe_haber' => ($request->debe_haber == 1) ?? 0,
            'integra_variables' => $request->get('integra_variables', ""),
            'name_cuenta_isr' => $request->get('name_cuenta_isr'),
            'cuenta_contable' => $request->get('cuenta_contable', ""),
        ];

        cambiarBase(Session::get('base'));
        ConceptosNomina::where('id', $request->id)->update($data);

        session()->flash('success', 'El concepto de nómina se edito correctamente');

        return redirect()->route('parametria.conceptos-nomina');
    }

    public function actualizaEditaPrestaciones(Request $request){
        cambiarBase(Session::get('base'));
        try{
             DB::connection('empresa')
            ->table('categorias')
            ->updateOrInsert(
                ['id' => $request->get('id')],
                [
                    'nombre' => $request->get('nombre'),
                    'tipo_clase' => $request->get('tipo_clase'),
                    'estatus' => 1,
                    'fecha_edicion' => date('Y-m-d H:i:s')
                ]
            );
            session()->flash('success', 'Los datos se guardaron correctamente');
        }catch(\Exception $e){
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }
       
        return redirect()->route('parametria.prestaciones.inicio');
                        
    }

    public function creaEditaPrestacion(Request $request){
        $porcentaje = $request->prima_vacacional / 100;
        $factor1 = $request->vacaciones * $porcentaje;
        $b = $request->aguinaldo + 365;
        $facInt = ($factor1 + $b) / 365;

        cambiarBase(Session::get('base'));
        try{
        Prestacion::updateOrInsert(
                ['id' => $request->get('id')],
                [
                    'id_categoria' => $request->get('id_categoria'),
                    'antiguedad' => $request->get('antiguedad'),
                    'vacaciones' => $request->get('vacaciones'),
                    'prima_vacacional' => $request->get('prima_vacacional'),
                    'aguinaldo' => $request->get('aguinaldo'),
                    'factor_integracion' => round($facInt,4),
                    'bono_aguinaldo' => $request->get('bono_aguinaldo'),
                    'bono_vacaciones' => $request->get('bono_vacaciones'),
                    'bono_prima_vacacional' => $request->get('bono_prima_vacacional'),
                    'estatus' => 1,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'fecha_edicion' => date('Y-m-d H:i:s')
                ]
            );
            session()->flash('success', 'Los datos se guardaron correctamente');
        }catch(\Exception $e){
            session()->flash('danger', 'Los datos no se púdieron procesar favor de contactar a su administrador..!!');
        }
        return redirect()->route('parametria.prestaciones.inicio');
        
    }
}
