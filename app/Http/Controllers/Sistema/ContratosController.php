<?php

namespace App\Http\Controllers\sistema;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\ContratoEmpleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;
use Auth;
use DateTime;
use DateInterval;

class ContratosController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    public function contratosHr()
    {
        $contratos = Contrato::orderBy('nombre', 'asc')->get();

        return view('contratos.contratos-de-hrsystem', compact('contratos'));
    }

    public function crearcontratoHr()
    {
    	return view('contratos.crear-contrato-de-hrsystem');
    }

    public function agregarContrato(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required',
            'alias' => 'required',
            'tipo' => 'required',
            'temporalidad' => 'required',
        ]);


        $data = [
            'nombre' => strtoupper($request->get('nombre')),
            'alias' => strtoupper($request->get('alias', "")),
            'tipo' => $request->get('tipo', ""),
            'temporalidad' => $request->get('temporalidad', ""),
            'archivo' => $request->nombre.".txt",
        ];

        $contrato = Contrato::create($data);
        //DB::table('contratos')->insert($data);
        $contrato->archivo=$contrato->id.".txt";
        $contrato->save();


        $path = Storage::disk('public')->put('repositorio/contrato/', $request->file('archivo'));

        //$archivo_cer = $rest = substr($path, 9);

        $subido_cer = Storage::disk('public')->move($path, "/repositorio/contrato/".$contrato->id.".txt");



        session()->flash('success', 'El contrato se creo correctamente');

        return redirect()->route('contratos.contratosHr');
    }

    public function editarContrato($idcontrato)
    {
        $contratos =  Contrato::where('id', $idcontrato)->get();

        return view('contratos.editar-contrato-de-hrsystem', compact('contratos', 'idcontrato'));
    }

    public function actualizarContrato(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required',
            'alias' => 'required',
            'tipo' => 'required',
            'temporalidad' => 'required',
        ]);

        $data = [
            'nombre' => strtoupper($request->get('nombre')),
            'alias' => strtoupper($request->get('alias', "")),
            'tipo' => $request->get('tipo', ""),
            'temporalidad' => $request->get('temporalidad', "")
        ];

        if ($request->file('archivo')) {
            Storage::disk('public')->delete('contratos/'.$request->file_db);
            $path = Storage::disk('public')->put('contratos', $request->file('archivo'));
            $archivo_cer = $rest = substr($path, 10);
            $subido_cer = Storage::disk('public')->move('contratos/'.$archivo_cer, "/contratos/".$request->nombre.".txt");
            $data['archivo'] = $request->nombre.".txt";
        } else {
            $data['archivo'] = $request->file_db;
        }

        DB::table('contratos')->where('id', $request->id)->update($data);

        session()->flash('success', 'El contrato se edito correctamente');

        return redirect()->route('contratos.contratosHr');
    }

    public function eliminarCotrato(Request $request)
    {

        $file = Contrato::where('id', $request->id)->get();

        foreach ($file as $key => $value) {
            $archivo = $value['archivo'];
        }

        if ($archivo != NULL) {
            Storage::disk('public')->delete('contratos/'.$archivo);
        }

        Contrato::findOrFail($request->id)->delete();

        
        session()->flash('success', 'El contrato se elimino correctamente');

        return redirect()->route('contratos.contratosHr');
    }

    public function pdfContrato($idcontrato)
    {
        $file = Contrato::where('id', $idcontrato)->first();

    	$contenido =  Storage::disk('public')->get('repositorio/contrato/'.$file->id.".txt");
        
        $pdf = app('dompdf.wrapper');

    	return  $pdf = PDF::loadView("empleados_admin.contratos.contrato", compact('contenido'))->stream('contrato.pdf');
    }

    public function generarContrato(Request $request)
    {
        $mesescontrato = $diascontrato = 0;
        $fechavence = null;
        $fecha_contrato = $request->fecha_inicio_contrato . ' 00:00:00';
        cambiarBase(Session::get('base'));
        $empleado = Empleado::with(["puesto", "contrato", "categoria"])->find($request->id_empleado);


        $ruta_contrato = storage_path('app/public/repositorio/' . Session::get('empresa')['id'] . '/' . $empleado->id . '/contratos/generado');

        if (!File::exists($ruta_contrato)) {
            File::makeDirectory($ruta_contrato, $mode = 0777, true, true);
        }

        $fnombre = new DateTime($request->fecha_inicio_contrato);
        $nombre_archivo = $request->tipo_contrato . "CONTRATO" . $fnombre->format('Ymd') . ".pdf";

        if ($request->tipo_contrato == "D") { //tiempo determinado
            $fechavence = $fechainicio = new DateTime($request->fecha_inicio_contrato);
            if ($request->temporalidad_contrato == "M") {
                $fechavence->add(new DateInterval('P' . $request->meses_determinado . 'M'));
                $mesescontrato = $request->meses_determinado;
            } else if ($request->temporalidad_contrato == "D") {
                $fechavence->add(new DateInterval('P' . $diascontrato . 'D'));
            }
        } else if ($request->tipo_contrato == "O") { // obra determinada
            $fecha_contrato =  $empleado->fecha_alta . ' 00:00:00';
        }

        $fecha_vence_contrato = ($fechavence == null) ?  $fechavence : $fechavence->format('Y-m-d') . ' 00:00:00';
        $fecha_alerta = ($fechavence == null) ?  $fechavence : $fechavence->format('Y-m-d');

        //obtener empresa emisora
        $query = "SELECT empemi.* FROM singh.registro_patronal rp 
        INNER JOIN singh.empresas_emisoras empemi ON empemi.id = rp.id_empresa_emisora
        WHERE rp.id = " . $empleado->categoria->tipo_clase . ";";
        $emisora = DB::connection('empresa')->select($query);

        //obtener plantilla de contrato
        if (!file_exists(storage_path('app/public/repositorio/contrato/' . $request->id_contrato . '.txt'))) {
            $filename = storage_path('app/public/repositorio/contrato/contrato.plantilla.txt');
        } else {
            $filename = storage_path('app/public/repositorio/contrato/' . $request->id_contrato . '.txt');
        }

        $edad = 0;
        if (!empty($empleado->fecha_nacimiento)) {
            $cumpleanos = new DateTime($empleado->fecha_nacimiento);
            $hoy = new DateTime();
            $annos = $hoy->diff($cumpleanos);
            $edad = $annos->y;
        }

        $name_contrato = Contrato::where('id', $request->id_contrato)->first();

        // reemplazar tags con valores
        $contenido = File::get($filename);
        $contenido = str_replace('[correo_electronico]', $empleado->correo, $contenido);
        $contenido = str_replace('[telefono_movil]', $empleado->telefono_movil, $contenido);
        $contenido = str_replace("[nombre]", $empleado->nombre . " " . $empleado->apaterno . " " . $empleado->amaterno, $contenido);
        $contenido = str_replace("[curp]", $empleado->curp, $contenido);
        $contenido = str_replace("[nss]", $empleado->nss, $contenido);
        $contenido = str_replace("[rfc]", $empleado->rfc, $contenido);
        $contenido = str_replace("[ubicacion]", $empleado->ubicacion, $contenido);
        $contenido = str_replace("[nacionalidad]", $empleado->nacionalidad, $contenido);
        $contenido = str_replace("[edad]", $edad, $contenido);
        $contenido = str_replace("[estado_civil]", $empleado->estado_civil, $contenido);
        $contenido = str_replace('[telefono_casa]', $empleado->telefono_casa, $contenido);
        $contenido = str_replace('[avisar_a_telefono]', $empleado->avisar_a_telefono, $contenido);
        $contenido = str_replace('[avisar_a]', $empleado->avisar_a, $contenido);

        if (!empty($empleado->puesto->puesto)) {
            $contenido = str_replace("[puesto]", $empleado->puesto->puesto, $contenido);
            $actividades_puesto = explode(",", $empleado->puesto->actividades);
            $actividades = "Actividades no especificadas";
            if (count($actividades_puesto) > 0) {
                $actividades = "<ul>";
                foreach ($actividades_puesto as $actividad) {
                    $actividades .= "<li>" . $actividad . "</li>";
                }
                $actividades .= "</ul>";
            }
            $contenido = str_replace("[descripcionactividades]", $actividades, $contenido);
        }

        $contenido = str_replace("[direccion_empleado]", $empleado->calle_numero . ", " . $empleado->colonia . ", " . $empleado->delegacion . " " . $empleado->cp . ", " . $empleado->estado, $contenido);
        $nacimiento = new DateTime($empleado->fecha_nacimiento);
        $contenido = str_replace("[dia_nacimiento]", $nacimiento->format('d'), $contenido);
        $contenido = str_replace("[mes_nacimiento]",  strtoupper(mes($nacimiento->format('m'), true)), $contenido);
        $contenido = str_replace("[anio_nacimiento]", $nacimiento->format('Y'), $contenido);
        $alta = new DateTime($empleado->fecha_alta);
        $contenido = str_replace("[dia_alta]", $alta->format('d'), $contenido);
        $contenido = str_replace("[mes_alta]", strtoupper(mes($alta->format('m'), true)), $contenido);
        $contenido = str_replace("[anio_alta]", $alta->format('Y'), $contenido);

        $inicio_contrato = new DateTime($fecha_contrato);
        $contenido = str_replace("[dia_inicio]", $inicio_contrato->format('d'), $contenido);
        $contenido = str_replace("[mes_inicio]", strtoupper(mes($inicio_contrato->format('m'), true)), $contenido);
        $contenido = str_replace("[anio_inicio]", $inicio_contrato->format('Y'), $contenido);
        $contenido = str_replace("[dias_contrato]", $diascontrato, $contenido);
        if ($empleado->salario_diario != null && $empleado->salario_diario != "" && $empleado->salario_diario > 0) {
            $contenido = str_replace("[salario_diario]", number_format($empleado->salario_diario, 2, '.', ' '), $contenido);
        } else {
            $contenido = str_replace("[salario_diario]", "0.0", $contenido);
        }

        $sueldo = explode(".", $empleado->salario_diario);
        if (!isset($sueldo[1])) {
            $sueldo[1] = "00";
        } else {
            $sueldo[1] = str_replace("0.", "", round("." . $sueldo[1], 2));
        }
        $sueldo_letra = convertir_letra($sueldo[0]);
        $sueldo_texto = $sueldo_letra . " PESOS " . $sueldo[1] . "/100 M.N.";

        $contenido = str_replace("[salario_texto]", $sueldo_texto, $contenido);

        $contenido = str_replace("[tipo_nomina]", $empleado->tipo_de_nomimna, $contenido);
        $contenido = str_replace("[salto_pagina]", '<div style="page-break-after:always;"></div>', $contenido);

        $diasdescanso = (!empty($request->dd)) ?: 1;
        if ($diasdescanso == 2) {
            $contenido = str_replace("[dias_descanso]", "de dos días de descanso", $contenido);
        } else {
            $contenido = str_replace("[dias_descanso]", "de un día de descanso", $contenido);
        }

        if ($empleado->genero == 'M') {
            $contenido = str_replace("[generoparrafo]", "EL", $contenido);
            $contenido = str_replace("[sexo]", "MASCULINO", $contenido);
        } else {
            $contenido = str_replace("[generoparrafo]", "ELLA", $contenido);
            $contenido = str_replace("[sexo]", "FEMENINO", $contenido);
        }


        $hoy = new DateTime();
        $contenido = str_replace("[dia]", $hoy->format('d'), $contenido);
        $contenido = str_replace("[mes]", strtoupper(mes($hoy->format('m'))), $contenido);
        $contenido = str_replace("[anio]", $hoy->format('Y'), $contenido);

        if (count($emisora) > 0) {
            $contenido = str_replace("[emisora]", $emisora[0]->razon_social, $contenido);
            $contenido = str_replace("[razon_social]", session::get('empresa')['razon_social'], $contenido);
            $contenido = str_replace("[direccion_empresa]", $emisora[0]->direccion, $contenido);
            $contenido = str_replace("[rfc_empresa]", $emisora[0]->rfc, $contenido);
            $contenido = str_replace("[representante_legal]", strtoupper($emisora[0]->representante_legal), $contenido);

            $emisora_separado = explode(" ", $emisora[0]->razon_social);
            $contenido = str_replace("[emisora_corto]", $emisora_separado[0], $contenido);
        }

        $contenido = str_replace("[tipo_contrato]", strtoupper(str_replace("CONTRATO ", "", $request->nombre_contrato)), $contenido);

        if ($mesescontrato != 0) {
            if($mesescontrato==1){
                $mesesletra=convertir_letra($mesescontrato).' mes improrrogable';
            }else{
                $mesesletra=convertir_letra($mesescontrato).' meses improrrogables';
            }
            $contenido = str_replace("[meses_letra]",$mesesletra,$contenido);
            $contenido = str_replace("[meses_contrato]", $mesescontrato, $contenido);
            $contenido = str_replace("[dia_termino]", $fechavence->format('d'), $contenido);
            $contenido = str_replace("[mes_termino]", strtoupper(mes($fechavence->format('m'), true)), $contenido);
            $contenido = str_replace("[anio_termino]", $fechavence->format('Y'), $contenido);
        }

        // generar pdf y guardarlo en el repositorio
        $pdf = PDF::loadView("empleados.contratos.contrato", compact('contenido'))->save($ruta_contrato. '/' . $nombre_archivo);
        // return view("empleados_admin.contratos.contrato",compact('contenido'));

        if ($pdf) {
            ContratoEmpleado::insert(
                [
                    'id_empleado' => $request->id_empleado,
                    'fecha_contrato' => $fecha_contrato,
                    'fecha_vencimiento' => $fecha_vence_contrato,
                    'estatus' => 1,
                    'numero_dias' => $diascontrato,
                    'contrato' => $name_contrato->nombre,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'alerta' => $fecha_alerta,
                    'archivo' => $nombre_archivo
                ]
            );
            session()->flash('success', 'El contrato se genero de forma correcta.');
            return redirect()->route('contratos.vigenciacontratos');
        }
            session()->flash('danger', 'El contrato no se pudo generar.');
            return redirect()->route('empleados.empleados');
    }

    public function vigenciaContratos()
    {
        tienePermisoa('vigencia_contratos');
        cambiarBase(Session::get('base'));
        $parametros = DB::connection('empresa')
            ->table('parametros')
            ->first();
        
        $dias_aviso_contrato = $parametros->dias_aviso_contrato;
        $usuario_departamentos = Session::get('usuarioDepartamentos');
        cambiarBase(Session::get('base'));

        $empleados = Empleado::where('estatus', 1)
            ->with('contratos')
            ->orderByRaw('apaterno, amaterno');

        $contratos = collect();
        foreach ($empleados->get() as $empleado) {
            if ($empleado->contratos->count() > 0) {
                $e = collect();
                $e->nombre = $empleado->nombre;
                $e->apaterno = $empleado->apaterno;
                $e->amaterno = $empleado->amaterno;
                $e->tipo_fiscal = $empleado->tipo_fiscal;
                $e->tipo_sindical = $empleado->tipo_sindical;
                $e->estatus_evaluacion = $empleado->estatus_evaluacion;
                $e->contratos = $empleado->contratos;
                $e->id = $empleado->contratos->first()->id;
                $e->id_empleado = $empleado->contratos->first()->id_empleado;
                $e->fecha_contrato = $empleado->contratos->first()->fecha_contrato;
                $e->fecha_vencimiento = $empleado->contratos->first()->fecha_vencimiento;
                $frenovar = new DateTime($e->fecha_vencimiento);
                $e->fecha_permite_renovar = $frenovar->sub(new DateInterval('P14D'))->format('Y-m-d H:i:s');
                $e->ruta = '../storage/repositorio/' . Session::get('empresa')['id'] . '/' . $empleado->id . '/contratos/generado';
                $contratos->push($e);
                
            }
        }
        //dd($contratos);
        $ids = DB::connection('empresa')
            ->table('asignacion_contratos')
            ->select('id_contrato')
            ->where('estatus', 1)
            ->get();

        foreach ($ids as $id) {
            $idsContrato[] = $id->id_contrato;
        }
        $contratos_asignados = Contrato::whereIn('id', $idsContrato)->get();
        

        return view('contratos.vigenciacontratos', compact('contratos', 'contratos_asignados'));
    }


    public function eliminarContratoVigencia(Request $request)
    {
        cambiarBase(Session::get('base'));
        ContratoEmpleado::where('id', $request->id)->delete();

        DB::connection('empresa')->table('log_incidencias')->insert([
            'id_empleado' => Auth::user()->id,
            'fecha' => date('Y-m-d'),
            'tipo' => 'Eliminó contrato',
            'ejecutivo' => Auth::user()->email,
            'descripcion' => 'Eliminó contrato del empleado '.$request->empleado,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]);

        session()->flash('success', 'Contrato eliminado con éxito.');

        return redirect()->route('contratos.vigenciacontratos');

    }

}
