<?php

namespace App\Http\Controllers\sistema;

use PDOException;
use App\Models\Empresa;
use App\Models\Contrato;
use Illuminate\Http\Request;
use App\Models\EmpresaEmisora;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;


class EmpresaEmisoraController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
	public function empresaEmisora()
	{
        $empresas = EmpresaEmisora::where('estatus', 1)
            ->orderBy('razon_social', 'asc')->get();

        $bancos = DB::table('bancos')->orderBy('nombre', 'asc')->get();

        $timbrados = DB::table('timbrado_credenciales')
            ->select('id', 'razon_social')
            // ->where('estatus', 1)
            ->orderBy('razon_social', 'asc')
            ->get();

        return view('empresas-emisora.empresa-emisora', compact('empresas', 'bancos', 'timbrados'));
    }

    public function crearempresaEmisora()
    {
        $timbrados = DB::table('timbrado_credenciales')
            ->select('id', 'razon_social')
            ->orderBy('razon_social', 'asc')
            ->get();

        $bancos = DB::table('bancos')->orderBy('nombre', 'asc')->get();

    	return view('empresas-emisora.crear-empresa-emisora', compact('bancos', 'timbrados'));
    }

    public function agregarempresaEmisora(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required',
            'rfc' => 'required',
            'direccion' => 'required',
            'cp' => 'required',
            'representante_legal' => 'required',
            'user_timbre' => 'required'
        ]);

        $data = [
            'razon_social' => $request->get('razon_social', ''),
            'rfc' => $request->get('rfc', ""),
            'direccion' => $request->get('direccion', ""),
            'cp' => $request->get('cp', ""),
            'representante_legal' => $request->get('representante_legal', ""),
            'num_cuenta_contable' => $request->get('num_cuenta_contable', ""),
            'concepto_nomina_contable' => $request->get('concepto_nomina_contable', ""),
            'banco' => $request->get('banco', "0"),
            'cuenta_bancaria' => $request->get('cuenta_bancaria', ""),
            'clave_emisora' => $request->get('clave_emisora', ""),
            'banco2' => $request->get('banco2', "0"),
            'cuenta_bancaria2' => $request->get('cuenta_bancaria2', ""),
            'banco3' => $request->get('banco3', "0"),
            'cuenta_bancaria3' => $request->get('cuenta_bancaria3', ""),
            'user_timbre' => $request->get('user_timbre', ""),
            'banco_sind' => $request->get('banco_sind', "0"),
            'cuenta_bancaria_sind' => $request->get('cuenta_bancaria_sind', ""),
            'clave_emisora_sind' => $request->get('clave_emisora_sind', ""),
            'banco_sind2' => $request->get('banco_sind2', "0"),
            'cuenta_bancaria_sind2' => $request->get('cuenta_bancaria_sind2', ""),
            'banco_sind3' => $request->get('banco_sind3', "0"),
            'cuenta_bancaria_sind3' => $request->get('cuenta_bancaria_sind3', ""),
            'estatus' => 1,
            'fecha_creacion'=> date('Y-m-d'),
            'fecha_edicion' => date('Y-m-d')
        ];


        $rfc = EmpresaEmisora::where('rfc', $request->rfc)->first();
        if(!empty($rfc)){
            session()->flash('danger', 'El RFC ya se encuentra registrado.');
            return redirect()->back();
        }

        $razon_social = EmpresaEmisora::where('razon_social', $request->razon_social)->first();
        if(!empty($razon_social)){
            session()->flash('danger', 'La razon social ya se encuentra registrada.');
            return redirect()->back();
        }

        EmpresaEmisora::insert($data);

        session()->flash('success', 'La empresa emisora se creo correctamente');

        return redirect()->route('empresae.empresaemisora');
    }

    public function editarempresaEmisora($empresa)
    {
        $empresas = EmpresaEmisora::where('id', $empresa)->get();

        $timbrados = DB::table('timbrado_credenciales')
            ->select('id', 'razon_social')
            ->orderBy('razon_social', 'asc')
            ->get();

        $bancos = DB::table('bancos')->orderBy('nombre', 'asc')->get();

        return view('empresas-emisora.editar-empresa-emisora', compact('empresas', 'bancos', 'timbrados'));
    }

    public function actualizarempresaEmisora(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required',
            'rfc' => 'required',
            'direccion' => 'required',
            'cp' => 'required',
            'representante_legal' => 'required',
            'num_cuenta_contable' => 'required',
            'concepto_nomina_contable' => 'required',
            'user_timbre' => 'required'
        ]);


        $data = [
         	'razon_social' => $request->get('razon_social', ''),
         	'rfc' => $request->get('rfc', ""),
         	'direccion' => $request->get('direccion', ""),
         	'cp' => $request->get('cp', ""),
         	'representante_legal' => $request->get('representante_legal', ""),
         	'num_cuenta_contable' => $request->get('num_cuenta_contable', ""),
         	'concepto_nomina_contable' => $request->get('concepto_nomina_contable', ""),
         	'banco' => $request->get('banco', "0"),
            'cuenta_bancaria' => $request->get('cuenta_bancaria', ""),
            'clave_emisora' => $request->get('clave_emisora', ""),
            'banco2' => $request->get('banco2', "0"),
            'cuenta_bancaria2' => $request->get('cuenta_bancaria2', ""),
            'banco3' => $request->get('banco3', "0"),
            'cuenta_bancaria3' => $request->get('cuenta_bancaria3', ""),
            'user_timbre' => $request->get('user_timbre', ""),
            'banco_sind' => $request->get('banco_sind', "0"),
            'cuenta_bancaria_sind' => $request->get('cuenta_bancaria_sind', ""),
            'clave_emisora_sind' => $request->get('clave_emisora_sind', ""),
            'banco_sind2' => $request->get('banco_sind2', "0"),
            'cuenta_bancaria_sind2' => $request->get('cuenta_bancaria_sind2', ""),
            'banco_sind3' => $request->get('banco_sind3', "0"),
            'cuenta_bancaria_sind3' => $request->get('cuenta_bancaria_sind3', ""),
            'estatus' => 1,
            'fecha_creacion'=> date('Y-m-d'),
            'fecha_edicion' => date('Y-m-d')
        ];

        EmpresaEmisora::where('id', $request->idempresa)->update($data);

        session()->flash('success', 'La empresa emisora se actualizo correctamente');

        return redirect()->route('empresae.empresaemisora');
    }


    public function borrarempresaemisora(Request $request)
    {
        EmpresaEmisora::where('id', $request->id)->update(['estatus' => 0]);

        session()->flash('success', 'La empresa se elimino correctamente');

        return redirect()->route('empresae.empresaemisora');
    }

    public function registroPatronal($empresa)
    {
        $empresaEmisora = EmpresaEmisora::find($empresa);

        $regPatronal = DB::table('registro_patronal')
            ->where('estatus', 1)
            ->where('id_empresa_emisora', $empresa)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $empresaEmisora = $empresaEmisora->razon_social;
        $idEmpresaEmisora = $empresa;

        return view('empresas-emisora.registro-patronal', compact('regPatronal', 'empresaEmisora', 'idEmpresaEmisora'));
    }

    public function crearregistroPatronal($empresa)
    {
    	return view('empresas-emisora.crear-registro-patronal',  compact('empresa'));
    }

    public function agregarregistroPatronal(Request $request)
    {
        $validated = $request->validate([
            'num_registro_patronal' => 'required',
            'tipo_clase' => 'required',
            'subdelegacion' => 'required',
            'tipo_documento' => 'required'
        ]);

        $data = [
            'num_registro_patronal' => $request->num_registro_patronal,
            'porcentaje_prima' => $request->porcentaje_prima,
            'tipo_clase' => $request->tipo_clase,
            'subdelegacion' => $request->subdelegacion,
            'tipo_documento' => $request->tipo_documento,
            'id_empresa_emisora' => $request->id_empresa_emisora,
            'estatus' => 1,
            'fecha_creacion' => date('Y-m-d'),
            'fecha_edicion' => date('Y-m-d')
        ];

        DB::table('registro_patronal')->insert($data);

        session()->flash('success', 'El registro patronal se creo correctamente');

        return redirect()->route('empresae.registropatronal',  $request->id_empresa_emisora);
    }

    public function editarregistroPatronal($empresa, $registro)
    {
    	$empresaEmisora = EmpresaEmisora::find($empresa);

        $regPatronal = DB::table('registro_patronal')
            ->where('estatus', 1)
            ->where('id', $registro)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $empresaEmisora = $empresaEmisora->razon_social;
        $idEmpresaEmisora = $empresa;

    	return view('empresas-emisora.editar-registro-patronal',  compact('idEmpresaEmisora','regPatronal','empresaEmisora'));
    }

    public function actualizarregistroPatronal(Request $request)
    {
        $validated = $request->validate([
            'num_registro_patronal' => 'required',
            'tipo_clase' => 'required',
            'subdelegacion' => 'required',
            'tipo_documento' => 'required'
        ]);

        $data = [
            'num_registro_patronal' => $request->num_registro_patronal,
            'porcentaje_prima' => $request->porcentaje_prima,
            'tipo_clase' => $request->tipo_clase,
            'subdelegacion' => $request->subdelegacion,
            'tipo_documento' => $request->tipo_documento,
            'id_empresa_emisora' => $request->id_empresa_emisora,
            'estatus' => 1,
            'fecha_creacion' => date('Y-m-d'),
            'fecha_edicion' => date('Y-m-d')
        ];

        DB::table('registro_patronal')->where('id', $request->ids)->update($data);

        session()->flash('success', 'El registro patronal se actualizo correctamente');

        return redirect()->route('empresae.registropatronal',  $request->id_empresa_emisora);
    }

    public function borrarregistropatronal(Request $request)
    {
        DB::table('registro_patronal')->where('id', $request->id)->update(['estatus' => 0]);

        session()->flash('success', 'El registro patronal se elimino correctamente');

        return redirect()->route('empresae.registropatronal',  $request->empresa);
    }
}
