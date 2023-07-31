<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Empleado;
use App\Exports\DocumentoEmpleadosExport;
use Maatwebsite\Excel\Facades\Excel;

class DocumentosEmpleadosController extends Controller
{
    public function docEmpleados()
    {
        cambiarBase(Session::get('base'));

        $empleados = array();

        $repositorio = 'storage/repositorio/' . Session::get('empresa')['id'];

        $emplea = Empleado::select(
            'id',
            'numero_empleado',
            'nombre',
            'apaterno',
            'amaterno',
            'repositorio',
            'file_ine',
            'file_curp',
            'file_nss',
            'file_nacimiento',
            'file_comprobante',
            'file_aviso',
            'file_estado',
            'file_contrato',
            'file_rfc',
            'file_fotografia',
            'file_analisis',
            'file_fonacot',
            'file_curriculum',
            'file_acuse',
            'file_estado_cuenta',
            'file_fiel_imss'
        )
            ->where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->get();

        foreach ($emplea as $empleado) {

            $ine = array(
                'nombre' => $empleado->file_ine,
                'archivo' => ($empleado->file_ine != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_ine : "",
                'existe' => ($empleado->file_ine != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_ine) : false
            );
            $curp = array(
                'nombre'  => $empleado->file_curp,
                'archivo' => ($empleado->file_curp != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_curp : "",
                'existe'  => ($empleado->file_curp != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_curp) : false
            );
            $nss = array(
                'nombre'  => $empleado->file_nss,
                'archivo' => ($empleado->file_nss != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_nss : "",
                'existe'  => ($empleado->file_nss != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_nss) : false
            );
            $nacimiento = array(
                'nombre'  => $empleado->file_nacimiento,
                'archivo' => ($empleado->file_nacimiento != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_nacimiento : "",
                'existe'  => ($empleado->file_nacimiento != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_nacimiento) : false
            );

            $comprobante = array(
                'nombre'  => $empleado->file_comprobante,
                'archivo' => ($empleado->file_comprobante != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_comprobante : "",
                'existe'  => ($empleado->file_comprobante != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_comprobante) : false
            );
            $aviso = array(
                'nombre'  => $empleado->file_aviso,
                'archivo' => ($empleado->file_aviso != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_aviso : "",
                'existe'  => ($empleado->file_aviso != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_aviso) : false
            );
            $estado = array(
                'nombre'  => $empleado->file_estado,
                'archivo' => ($empleado->file_estado != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_estado : "",
                'existe'  => ($empleado->file_estado != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_estado) : false
            );
            $contrato = array(
                'nombre'  => $empleado->file_contrato,
                'archivo' => ($empleado->file_contrato != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_contato : "",
                'existe'  => ($empleado->file_contrato != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_contrato) : false
            );
            $rfc = array(
                'nombre'  => $empleado->file_rfc,
                'archivo' => ($empleado->file_rfc != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_rfc : "",
                'existe'  => ($empleado->file_rfc != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_rfc) : false
            );
            $foto = array(
                'nombre'  => $empleado->file_fotografia,
                'archivo' => ($empleado->file_fotografia != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_fotografia : "",
                'existe'  => ($empleado->file_fotografia != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fotografia) : false
            );
            $analisis = array(
                'nombre'  => $empleado->file_analisis,
                'archivo' => ($empleado->file_analisis != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_analisis : "",
                'existe'  => ($empleado->file_analisis != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_analisis) : false
            );
            $fonacot = array(
                'nombre'  => $empleado->file_fonacot,
                'archivo' => ($empleado->file_fonacot != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_fonacot : "",
                'existe'  => ($empleado->file_fonacot != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fonacot) : false
            );
            $curriculum = array(
                'nombre'  => $empleado->file_curriculum,
                'archivo' => ($empleado->file_curriculum != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_curriculum : "",
                'existe'  => ($empleado->file_curriculum != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_curriculum) : false
            );
            $acuse = array(
                'nombre'  => $empleado->file_acuse,
                'archivo' => ($empleado->file_acuse != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_acuse : "",
                'existe'  => ($empleado->file_acuse != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_acuse) : false
            );
            $estado_cuenta = array(
                'nombre'  => $empleado->file_estado_cuenta,
                'archivo' => ($empleado->file_estado_cuenta != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_estado_cuenta : "",
                'existe'  => ($empleado->file_estado_cuenta != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_estado_cuenta) : false
            );
            $fiel_imss = array(
                'nombre'  => $empleado->file_estado_cuenta,
                'archivo' => ($empleado->file_fiel_imss != "") ? $repositorio . '/' . $empleado->id . '/' . $empleado->file_fiel_imss : "",
                'existe'  => ($empleado->file_fiel_imss != "") ? file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fiel_imss) : false
            );
            $e = array(
                'id'            => $empleado->id,
                'num_empleado'  => $empleado->numero_empleado,
                'nombre'        => $empleado->nombre . ' ' . $empleado->apaterno . ' ' . $empleado->amaterno,
                'ine'           => $ine,
                'curp'          => $curp,
                'nss'           => $nss,
                'nacimiento'    => $nacimiento,
                'comprobante'   => $comprobante,
                'aviso'         => $aviso,
                'estado'        => $estado,
                'contrato'      => $contrato,
                'rfc'           => $rfc,
                'foto'          => $foto,
                'analisis'      => $analisis,
                'fonacot'       => $fonacot,
                'curriculum'    => $curriculum,
                'acuse'         => $acuse,
                'estado_cuenta' => $estado_cuenta,
                'fiel_imss'     => $fiel_imss
            );
            $empleados[] = $e;
        }

        return view('consultas.doc-empleados.doc-empleados', compact('empleados', 'emplea'));
    }

    public function exportaDocEmpleados(){
        return Excel::download(new DocumentoEmpleadosExport(),"Reporte-DocumentosEmpleados".date('d-m-Y').".xlsx");
    }
}
