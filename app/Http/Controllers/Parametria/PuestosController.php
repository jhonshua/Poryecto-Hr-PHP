<?php

namespace App\Http\Controllers\parametria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\PuestoAlias;
use App\Models\PuestoDetalle;
use Illuminate\Support\Facades\DB;
use App\Imports\importPuestosReales;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ConfiguracionOrganigrama;
use App\Models\Horario;
use App\Models\PerfilPuesto;
use App\Exports\PerfilDescriptivoExport;
use Barryvdh\DomPDF\Facade\Pdf;

class PuestosController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

    public function inicio(Request $request)
    {
        cambiarBase(Session::get('base'));
        $puestos = Puesto::where('estatus', 1)->orderBy('puesto', 'asc')->get()->keyBy('id');

        $id_empresa = Session::get('empresa')['id'];

        $verificar_config = ConfiguracionOrganigrama::where('id_empresa', $id_empresa)->first();
        $lleva_puestos_reales = "";
        $lleva_rama = "";
        $puestos_reales = "";

        if (!empty($verificar_config)) {

            $lleva_puestos_reales = $verificar_config->lleva_puestos_reales;
            if (!empty($lleva_puestos_reales)) {

                //$array_puestos = $this->puestosAlias($puestos,true);
                //$puestos = $array_puestos;
                //$puestos_reales = PuestoAlias::where("estatus" ,1)->orderBy('alias','asc')->get();
            }
            $lleva_rama = $verificar_config->lleva_ramas;
        }
        return view('parametria.puestos.inicio', compact('puestos', 'puestos_reales', 'lleva_puestos_reales', 'lleva_rama'));
    }
    public function puestosAlias($puestos, $aux)
    {
        $array_puestos = [];
        foreach ($puestos as $key => $puesto) {

            $puestosConAlias = PuestoDetalle::join('puestos AS p', 'puestos_detalle.id_puesto', '=', 'p.id')
                ->join('puestos_alias AS pa', 'puestos_detalle.id_alias', '=', 'pa.id')
                ->select('pa.id AS id_alias', 'pa.alias', 'p.id', 'p.puesto', 'pa.jerarquia', 'pa.dependencia', 'pa.estatus', 'p.actividades')
                ->where(['pa.id' => $puesto->id, 'p.estatus' => 1])
                ->first();

            (!empty($puestosConAlias)) ? $alias = $puestosConAlias->id_alias . '--' . $puestosConAlias->alias : $alias = "";
            ($aux) ? $valor = $puesto->id : $valor = $key;
            $array_puestos[$valor] = array(
                'id' => $puestosConAlias->id,
                'puesto' => $puestosConAlias->puesto,
                'estatus' => $puestosConAlias->estatus,
                'jerarquia' => $puesto->jerarquia,
                'dependencia' => $puesto->dependencia,
                'actividades' => $puesto->actividades,
                'alias' => $alias
            );
        }
        return $array_puestos;
    }

    /**
     * Borrado logico de puestos
     */
    public function borrar(Request $request)
    {
        cambiarBase(Session::get('base'));
        Puesto::destroy($request->get('id'));
        return response()->json(['ok' => 1]);
    }

    public function vistaCrearEditar(Request $request)
    {
        cambiarBase(Session::get('base'));
        $puestos = Puesto::where('estatus', 1)
            ->orderBy('puesto', 'asc')
            ->get();
        $puestos = $puestos->keyBy('id');
        return view('parametria.puestos.crear-editar-puesto', compact('puestos'));
    }

    /**
     * Edición del nombre de puesto
     */
    public function crearEditarPuesto(Request $request)
    {
        $request->validate([
            'puesto' => 'required',
            'jerarquia' => 'required'
        ]);
        $actividades = $request->get('actividades', null);
        $actividades = ($actividades) ? implode(',', $actividades) : '';
        $data = [
            'puesto' => strtoupper($request->get('puesto', '')),
            'jerarquia' => $request->jerarquia,
            'dependencia' => $request->dependencia,
            'actividades' => $actividades,
            'estatus' => 1,
            'fecha_edicion' => date('Y-m-d H:i:s'),
        ];

        cambiarBase(Session::get('base'));
        if (empty($request->id)) {
            $existe = Puesto::where('puesto', strtoupper($request->puesto))->get();
        } else {
            $existe = [];
        }

        //if(count($existe) <= 0){
        if ($request->id <= 0)
            $data['fecha_creacion'] =  date('Y-m-d H:i:s');

        $puestoId = Puesto::updateOrInsert(['id' => $request->id], $data);
        $accion = (empty($request->id)) ? 'creó' : 'editó';


        if (!empty($request->alias)) {
            if (empty($request->id)) {
                $idpuesto = Puesto::latest('id')->first();
                $datos = array('id_puesto' => $idpuesto->id, 'id_alias' => $request->alias);
                PuestoDetalle::create($datos);
            } else {
                PuestoDetalle::updateOrInsert(['id_puesto' => $request->id], ['id_puesto' => $request->id, 'id_alias' => $request->alias]);
            }
        }


        return redirect()->route('parametria.puestos')
            ->with('tipo_alerta', 'success')
            ->with('mensaje', 'El puesto se ' . $accion . ' correctamente.');
        /*} else {
            return redirect()->route('parametria.puestos')
                            ->with('tipo_alerta', 'danger')
                            ->with('mensaje', 'Este puesto ya existe.');
        }*/
    }
    public function editarPuesto(Request $request, $idPuesto)
    {
        cambiarBase(Session::get('base'));
        $puestos = Puesto::where('estatus', 1)->get();
        // $editar = true;

        return view('parametria.puestos.crear-editar-puesto', compact('puestos', 'idPuesto'));
    }

    public function inicioPuestosReales()
    {
        cambiarBase(Session::get('base'));
        $puestos_empresa = Puesto::where('estatus', 1)->orderBy('puesto', 'asc')->get();
        $puestos = PuestoDetalle::join('puestos AS p', 'puestos_detalle.id_puesto', '=', 'p.id')
            ->join('puestos_alias AS pa', 'puestos_detalle.id_alias', '=', 'pa.id')
            ->select('pa.id AS id_alias', 'pa.alias', 'p.puesto', 'p.id as id_puesto', 'pa.dependencia', 'pa.rama', 'pa.jerarquia', 'pa.rama', 'puestos_detalle.id AS id_detalle')
            ->where(['p.estatus' => 1])
            ->get()->keyBy('id_alias');


        return view('parametria.puestos.puestos-reales.inicio', compact('puestos_empresa', 'puestos'));
    }
    public function guardarEditarPuestoReal(Request $request)
    {
        cambiarBase(Session::get('base'));

        $id = $request->id;
        $array_datos = [
            'alias' => $request->puesto,
            'jerarquia' => $request->jerarquia,
            'dependencia' => $request->dependencia,
            'rama' => $request->rama
        ];

        if (empty($id)) {

            //Puesto::where('id',$request->puesto_empresa)->update(['rama'=>$request->rama]);
            $alias = PuestoAlias::create($array_datos);
            PuestoDetalle::create(['id_puesto' => $request->puesto_empresa, 'id_alias' => $alias->id]);
        } else {

            PuestoAlias::where('id', $request->id)->update($array_datos);
            PuestoDetalle::where('id', $request->id_detalle)->update(['id_puesto' => $request->puesto_empresa]);
        }

        session()->flash('success', 'Los datos se guardaron correctamente');
        return redirect()->route('puestos.reales.inicio');
    }

    public function borrarEditarPuestoReal(Request $request)
    {
        cambiarBase(Session::get('base'));
        PuestoAlias::where('id', decrypt($request->id))->update(['estatus' => 2]);
        return response()->json(['ok' => 1]);
    }

    public function importarPuestosReales(Request $request)
    {
        try {

            Excel::import(new importPuestosReales, $request->file_puesto);
            session()->flash('success', 'Los datos se guardaron correctamente');
        } catch (\Exception $e) {

            session()->flash('danger', 'El documento no cumple con las especificaciones requeridas, intentalo nuevamente..!!');
        }
        return redirect()->route('puestos.reales.inicio');
    }

    public function obtenerPuestosAlias(Request $request)
    {
        cambiarBase(Session::get('base'));
        $puestos = PuestoAlias::where(['jerarquia' => $request->jerarquia, 'estatus' => 1])->orderBy('alias', 'asc')->get();

        $array_puestos = $this->puestosAlias($puestos, false);

        $puestos = $array_puestos;
        return response()->json(['puestos' => $puestos]);
    }

    public function obtenerPuestos(Request $request)
    {
        cambiarBase(Session::get('base'));
        $puestos = Puesto::where(['jerarquia' => $request->jerarquia, 'estatus' => 1])->orderBy('puesto', 'asc')->get();
        return response()->json(['puestos' => $puestos]);
    }

    /* Perfil descriptivo de puestos */
    public function inicioPerfilPuestos(Request $request)
    {
        cambiarBase(Session::get('base'));
        try {

            cambiarBase(Session::get('base'));
            $empresa = 'empresa';
            $base = Session::get('base');
            $stm = "CREATE TABLE IF NOT EXISTS " . $base . ".perfil_puesto(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_puesto` int(11),
            `objetivo_puesto` tinytext,
            `id_departamento` int(11),
            `id_horario` int(11),
            `id_contrato` int(11),
            `rango_salario` varchar(150),
            `condiciones` tinytext,
            `id_dependencia` int(11),
            `personal` tinytext,
            `reportan` tinytext,
            `c_interna` tinytext,
            `relaciones` tinytext,
            `habilidades` tinytext,
            `competencias` tinytext,
            `nivel_educativo` varchar(150),
            `terminado` int(11),
            `titulo` varchar(150),
            `experiencia` tinytext,
            `tiempo_experiencia` tinytext,
            `conocimientos` tinytext,
            `dominio_c` tinytext,
            `cursos` tinytext,
            `ant_curso` tinytext,
            `actividades` tinytext,
            `act_autoridades` tinytext,
            `otros` tinytext,
            PRIMARY KEY (`id`)
            )ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;";

            DB::connection($empresa)->statement($stm);
        } catch (\Throwable $th) {
            session()->flash('danger', 'Ha ocurrido un error, intentalo más tarde');

            return redirect()->route('parametria.puestos');
        }
        $puestos = Puesto::where('id', '=', $request->id)->get();
        $departamentos = Departamento::where('estatus', '=', 1)->orderBy('nombre', 'asc')->get();
        $horarios = Horario::get();
        $dependencias = Puesto::where('estatus', '=', 1)->get();
        $perfil = PerfilPuesto::where('id_puesto', '=', $request->id)->get();
        return view('parametria.puestos.puestos-perfilDescriptivo.perfilDescriptivo', compact('puestos', 'departamentos', 'horarios', 'dependencias', 'perfil',));
    }

    public function editarPerfilPuestos(Request $request)
    {

        $relaciones = [
            'materiales' => $request->materiales,
            'rec_economicos' => $request->rec_econ,
            'doc_imp' => $request->doc_imp,
            'inf_confidencial' => $request->info_conf,
            'mail_empresarial' => $request->mail_empresarial,
            'rel_clientes' => $request->rel_clientes,
            'rel_directivos' => $request->rel_directivo,
            'rel_gerencias' => $request->rel_gerencia,
            'rel_jefaturas' => $request->rel_jefatura,
            'rel_auxiliares' => $request->rel_aux,
            'rel_becarios' => $request->rel_beca,
            'rel_serviciosG' => $request->rel_sg,
        ];
        $habilidad = [
            'escritura' => $request->escritura,
            'verbal' => $request->verbal,
            'fisica' => $request->fisica,
            'visual' => $request->visual,
            'numerica' => $request->numerica
        ];
        $competencias = [
            'sintesis' => $request->sintesis,
            'lealtad' => $request->lealtad,
            'confiabilidad' => $request->confiabilidad,
            'etica' => $request->etica,
            'disponibilidad' => $request->disponibilidad,
            'temple' => $request->temple,
            'fac_palabra' => $request->fac_palabra,
            'tra_equipo' => $request->tra_equipo,
            'diplomacia' => $request->diplomacia,
            'negociacion' => $request->negociacion,
            'analitico' => $request->analitico
        ];
        $actividades = [
            'kit_contrato' => $request->kit_contrato,
            'kit_desvincula' => $request->kit_dev,
            'cont_desvincula' => $request->cont_dev,
            'anal_db' => $request->a_bd,
            'f_finiquito' => $request->finiq,
            'sist_doc' => $request->sist_doc,
            'dig_doc' => $request->dig_doc,
            'm_expedientes' => $request->man_exp,
            'f_nomina' => $request->firm_nom
        ];
        $otros = [
            'viajar' => $request->viajar,
            'camb_resi' => $request->camb_resi,
            'manejar' => $request->manejar,
            'lic_conducir' => $request->lice_c
        ];

        try {
            cambiarBase(Session::get('base'));

            DB::connection('empresa')->table('perfil_puesto')->updateOrInsert(
                [
                    'id_puesto' => $request->id_puesto,
                ],
                [
                    'objetivo_puesto'   => $request->objetivo_puesto,
                    'id_departamento' => $request->id_departamento,
                    'id_horario' => $request->id_horario,
                    'id_contrato' => $request->tipo_contrato,
                    'rango_salario' => $request->rango_salario,
                    'condiciones' => $request->condiciones,
                    'id_dependencia' => $request->id_dependencia,
                    'personal' => $request->personal,
                    'reportan' => json_encode($request->reportan),
                    'c_interna' => json_encode($request->c_interna),
                    'relaciones' => json_encode($relaciones),
                    'habilidades' => json_encode($habilidad),
                    'competencias' => json_encode($competencias),
                    'nivel_educativo' => $request->nivel_educativo,
                    'terminado' => $request->terminado,
                    'titulo' => $request->titulo,
                    'experiencia' => json_encode($request->experiencia),
                    'tiempo_experiencia' => json_encode($request->tiempo_experiencia),
                    'conocimientos' => json_encode($request->conocimientos),
                    'dominio_c' => json_encode($request->dominio_c),
                    'cursos' => json_encode($request->cursos),
                    'ant_curso' => json_encode($request->ant_curso),
                    'actividades' => json_encode($actividades),
                    'act_autoridades' =>  json_encode($request->act_auto),
                    'otros' => json_encode($otros)
                ]
            );
        } catch (\Throwable $th) {
            dd($th);
            session()->flash('danger', 'Ha ocurrido un error, intentalo más tarde');

            return redirect()->route('puestos.perfilDescriptivo', $request->id_puesto);
        }

        session()->flash('success', 'El registro  se actualizó con éxito');

        return redirect()->route('puestos.perfilDescriptivo', $request->id_puesto);
    }

    public function exportarPerfilPuestos(Request $request)
    {
        cambiarBase(Session::get('base'));
        $id_puesto = base64_decode($request->id_puesto);
        $perfilDescriptivo = PerfilPuesto::join('departamentos', 'departamentos.id', '=', 'perfil_puesto.id_departamento')
            ->join('horarios', 'horarios.id', '=', 'perfil_puesto.id_horario')
            ->join('puestos', 'puestos.id', '=', 'perfil_puesto.id_puesto')
            ->select(
                'puestos.puesto',
                'puestos.jerarquia',
                'perfil_puesto.objetivo_puesto',
                'departamentos.nombre',
                'horarios.alias',
                'perfil_puesto.id_contrato',
                'perfil_puesto.rango_salario',
                'perfil_puesto.condiciones',
                'perfil_puesto.id_dependencia',
                'perfil_puesto.personal',
                'perfil_puesto.reportan',
                'perfil_puesto.c_interna',
                'perfil_puesto.relaciones',
                'perfil_puesto.habilidades',
                'perfil_puesto.competencias',
                'perfil_puesto.nivel_educativo',
                'perfil_puesto.terminado',
                'perfil_puesto.titulo',
                'perfil_puesto.experiencia',
                'perfil_puesto.tiempo_experiencia',
                'perfil_puesto.tiempo_experiencia',
                'perfil_puesto.conocimientos',
                'perfil_puesto.dominio_c',
                'perfil_puesto.cursos',
                'perfil_puesto.ant_curso',
                'perfil_puesto.actividades',
                'perfil_puesto.act_autoridades',
                'perfil_puesto.otros'
            )
            ->where('perfil_puesto.id_puesto', '=', $id_puesto)->get();
        $departamentos = Departamento::where('estatus', '=', 1)->orderBy('nombre', 'asc')->get();
        $puestos = Puesto::where('estatus', '=', 1)->get();

        if ($request->tipo == 'excel') {
         $datos=['perfilDescriptivo'=> $perfilDescriptivo, 'departamentos' => $departamentos, 'puestos'=> $puestos];
                return Excel::download(new PerfilDescriptivoExport($datos),"PerfilDescriptivo-".date('d-m-Y') . ".xlsx");
        } else if ($request->tipo == 'pdf') {

            $pdf = app('dompdf.wrapper');
            $pdf = Pdf::loadView('parametria.puestos.puestos-perfilDescriptivo.perfilDescriptivo-PDF', compact('perfilDescriptivo', 'departamentos', 'puestos'));
            $file = 'PerfilDescriptivo' . $perfilDescriptivo[0]->puesto . '.pdf';
        }
        return $pdf->setPaper('letter', 'portrait')->stream($file);
    }
}
