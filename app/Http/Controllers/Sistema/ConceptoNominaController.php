<?php


namespace App\Http\Controllers\sistema;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use App\Models\ConceptosNomina;
use mysql_xdevapi\Exception;

class ConceptoNominaController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
	public function conceptosNomina()
	{
        $conceptos = DB::table('conceptos_nomina')
            ->where('estatus', 1)
            ->where('file_rool', '<>', 0)
            ->orderBy('tipo', 'desc')
            ->orderBy('nombre_concepto', 'asc')
            ->get();

        $codigosSat = DB::table('codigos_sat')->get();

        return view('conceptos-nomina.conceptos-de-nomina', compact('conceptos','codigosSat'));
	}

    public function crearconceptoNomina()
    {
        $codigosSat = DB::table('codigos_sat')->get();

        return view('conceptos-nomina.crear-concepto-de-nomina', compact('codigosSat'));
    }

    public function agregarConcepto(Request $request)
    {

        $data = [
            'nombre_concepto' => strtoupper($request->get('nombre_concepto')),
            'nombre_corto' => strtoupper($request->get('nombre_corto', "")),
            'codigo_sat' => $request->get('codigo_sat', ""),
            'activo_en_nomina' => $request->get('activo_en_nomina', 0),
            'cuenta_contable' => $request->get('cuenta_contable', ""),
            'integra_variables' => $request->get('integra_variables', ""),
            'debe_haber' => $request->get('debe_haber', 0),
            'finiquito' => $request->get('finiquito', 0),
            'nomina' => $request->get('nomina', 0),
            'tipo' => $request->get('tipo', ""),
            'rutinas' => strtoupper($request->get('rutinas', "")),
            'estatus' => 1,
            'tipo_proceso' => $request->get('tipo_proceso', ""),
            'fecha_edicion' => date('Y-m-d H:i:s')
        ];

        $maxIdAlterno = DB::table('conceptos_nomina')->get();

        foreach ($maxIdAlterno as $key => $value) {
            $id_maxalterno[] = $value->id_alterno;
        }

        $ultimo_idalterno = max($id_maxalterno);
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['id_alterno'] = $ultimo_idalterno+1;
        $data['file_rool'] = $request->filerool ? rand(1,249):rand(250,500);

        if(!$request->id){
            $validated = $request->validate([
                'nombre_concepto' => 'required|unique:conceptos_nomina',
                'nombre_corto' => 'required|unique:conceptos_nomina',
            ]);
        }else{
            unset($data['id_alterno'], $data['estatus'],$data['fecha_creacion']);
        }

        DB::table('conceptos_nomina')->updateOrInsert(
            ['id'=> $request->id, 'id_alterno' => $request->id_alterno],
            $data
        );

        if($request->id){
            $empresas=Empresa::select('base')->where('estatus',1)->get();
            foreach ($empresas as $empresa){
                try {
                    cambiarBase($empresa->base);
                    ConceptosNomina::where('id_alterno',$request->id_alterno)->update($data);
                }catch (\PDOException  $e){}
            }
            $this->logGeneral(Auth::user()->email, 'Catalogo de Conceptos de nomina id alterno: '.(($request->id_alterno) ? $request->id_alterno:$data['id_alterno']).', '.'"UPDATE"', 'Singh/Empresas', '');
        }else{
            $this->logGeneral(Auth::user()->email, 'Catalogo de Conceptos de nomina id alterno: '.(($request->id_alterno) ? $request->id_alterno:$data['id_alterno']).', '.'"INSERT"', 'Singh', '');
        }


        session()->flash('success', 'El concepto de nómina se creo correctamente');

        return redirect()->route('conceptos.nominaconceptos');
    }

    public function editarConcepto($idconcepto)
    {
        $concepto_nomina = DB::table('conceptos_nomina')
            ->where('id', $idconcepto)
            ->get();

        $codigosSat = DB::table('codigos_sat')->get();

       return view('conceptos-nomina.editar-concepto-de-nomina', compact('concepto_nomina', 'codigosSat', 'idconcepto'));
    }

    public function eliminarconceptonomina(Request $request)
    {

        $empresas_sss = Empresa::select('base', 'razon_social')
            ->where('estatus', 1)
            ->where('sss', 0)
            ->get();


        $empresasActivas = [];
        foreach($empresas_sss as $empresa){
            cambiarBase($empresa->base);
            try {
                $estaActivo = ConceptosNomina::select('estatus')
                    ->where('id_alterno', $request->idalterno)
                    ->where('estatus', 1)
                    ->first();

                if($estaActivo && $estaActivo->estatus){
                    $empresasActivas[] = $empresa->razon_social;
                }
            } catch(\PDOException  $e) {}
        }

        if (sizeof($empresasActivas) > 0) {
            session()->flash('success', 'El concepto de nómina no puede ser eliminado debido a que se encuentra activo en: '. implode(', ', $empresasActivas));
            return redirect()->route('conceptos.nominaconceptos');
        }

        DB::table('conceptos_nomina')
            ->where('id', $request->idconcepto)
            ->update(['estatus' => 0,'fecha_edicion' => date('Y-m-d H:i:s')]);

        $this->logGeneral(Auth::user()->email, 'Catalogo de Conceptos de nomina id alterno: '.$request->idalterno.', '.'"DELETE"', 'Singh', '');

        session()->flash('success', 'El concepto de nómina se elimino correctamente');

        return redirect()->route('conceptos.nominaconceptos');
    }


    function logGeneral($usuario, $evento, $base, $query = '')
    {
        DB::table('logs')->insert([
            'usuario' => $usuario,
            'evento' => $evento,
            'base' => $base,
            'query' => $query,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]);
    }
}
