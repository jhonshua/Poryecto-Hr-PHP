<?php 

namespace App\Http\Controllers\Imss;

use File;
use Response;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AfiliacionesController extends Controller
{

    public function inicio()
    {
        tienePermiso('avisos_imss');
        cambiarBase(Session::get('base'));

        $modificacion_sueldo = DB::connection('empresa')
            ->table('modificaciones_salario')
            ->get();
        $id_emp = array();
        foreach ($modificacion_sueldo as $key => $value) {
            $id_emp[] = $value->id_empleado;
        }

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->where('folio_alta', '')
            ->where('estatus_folio_modificacion', '!=', 1)
            ->whereNotIn('numero_empleado',  $id_emp)
            ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')
            ->get();
        
        if(Session::get('usuarioPermisos')['id_usuario']==64){
                    //dd($empleados);
        }

        return view('imss.afiliaciones.inicio', compact('empleados'));

    }

    public function bajas()
    {
        tienePermiso('avisos_imss');
        
        cambiarBase(Session::get('base'));
        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_BAJA])
            ->whereYear('fecha_baja', '>', '2000') // not null && != 0000-00-00
            // ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('fecha_baja', 'desc')
            ->get();

        return view('imss.afiliaciones.bajas', compact('empleados'));

    }


    public function modificaciones()
    {
        tienePermiso('avisos_imss');
        cambiarBase(Session::get('base'));

        $modificacion_sueldo = DB::connection('empresa')
            ->table('modificaciones_salario')
            ->get();

        $id_emp = array();
        foreach ($modificacion_sueldo as $key => $value) {
            $id_emp[] = $value->id_empleado;
        }

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            // ->where('estatus_folio_modificacion', 1)
            // ->where('folio_modificacion', '!=', '')
            ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->whereIn('numero_empleado',$id_emp)
            ->orderBy('fecha_edicion', 'desc')
            ->get();
        
        return view('imss.afiliaciones.modificaciones', compact('empleados'));

    }


    public function guardarFolio(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['folio_alta' => $request->folio_alta]);
        logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo AvisosIMSS '. $request->folio_alta .' UPDATE ');
        return response()->json(['ok' => 1]);
    }

    public function guardarFolioBaja(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['folio_baja' => $request->folio_baja]);
        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo AvisosIMSS '. $request->folio_baja .' UPDATE ');
        return response()->json(['ok' => 1]);
    }


    public function guardarFolioModificacion(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['folio_modificacion' => $request->folio_modificacion]);
        // logEmpresa(Session::get('base'), Auth::user()->email, 'Catalogo AvisosIMSS '. $request->folio_modificacion .' UPDATE ');
        return response()->json(['ok' => 1]);
    }

    public function cierreFolioModificacion(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['estatus_folio_modificacion' => 2]);
        return response()->json(['ok' => 1]);
    }


    public function cierreFolio(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['estatus_folio_alta' => 2]);
        return response()->json(['ok' => 1]);
    }

    public function cierreFolioBaja(Request $request)
    {
        cambiarBase(Session::get('base'));
        Empleado::where('id', $request->id)->update(['estatus_folio_baja' => 2]);
        return response()->json(['ok' => 1]);
    }


    public function generarDisco(Request $request)
    {
        tienePermiso('avisos_imss');
        cambiarBase(Session::get('base'));
        $txt = '';
        
        $folio_interno = $request->f;

        $categorias = $tipos_clase = [];

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
            ->where('folio_alta', '')
            // ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')
            ->get();
                    

        // Sino hay empleados elegibles, regresamos
        if($empleados->count() <= 0){

	        session()->flash('danger', 'Todos los empleados cuentan ya con un folio asignado. No fue generado el archivo.');
	        return redirect()->route('afiliaciones.inicio');

        }

        foreach($empleados as $empleado){
            $ids_categorias[] = $empleado->id_categoria;
        }
        $categorias = DB::connection('empresa')
            ->table('categorias')
            ->select('id', 'tipo_clase')
            ->whereIn('id', $ids_categorias)
            ->where('estatus', 1)
            ->get();

        foreach($categorias as $categoria){
            $tipos_clase[$categoria->id] = $categoria->tipo_clase;
        }

        $registro_patronal = DB::table('registro_patronal')
            ->select('id', 'num_registro_patronal', 'subdelegacion', 'tipo_documento')
            ->whereIn('id', $tipos_clase)
            ->where('estatus', 1)
            ->get();

        foreach($registro_patronal as $reg){
            $registro_patronal[$reg->id] = $reg->num_registro_patronal;
            $subdelegaciones[$reg->id] = $reg->subdelegacion;
            $tipos_documentos[$reg->id] = $reg->tipo_documento;
        }

        
        foreach($empleados as $empleado){
            
            $partes = explode('.', $empleado->salario_diario_integrado);
            $parte_decimal = substr(end($partes), 0, 2);
            $parte_entera = $partes[0];
            $parte_entera = str_pad($parte_entera, 4, "0" ,STR_PAD_LEFT);
            $salario_diario_integrado = $parte_entera.$parte_decimal;

            $reg_patronal = $registro_patronal[$tipos_clase[$empleado->id_categoria]];

            $registro = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 0, 10);
            $registro = str_pad($registro, 10, " ", STR_PAD_RIGHT);

            $digito_verificador = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 10, 1);
            $digito_verificador = str_pad($digito_verificador, 1, " ", STR_PAD_RIGHT);

            $num_seg_social = substr($empleado->nss, 0, 11);
            $num_seg_social = str_pad($num_seg_social, 11, " ", STR_PAD_RIGHT);

            $fecha_alta = date('dmY', strtotime($empleado->fecha_alta));
            $aPaterno = $this->limpiarCadena($empleado->apaterno);
            $aMaterno = $this->limpiarCadena($empleado->amaterno);
            $nombre = $this->limpiarCadena($empleado->nombre);   
            $tipo_trabajador = strtoupper(str_pad($empleado->tipo_empleado, 1, " ", STR_PAD_RIGHT));
            $tipo_salario = ($empleado->tipo_salario == NULL) ? 0 : $empleado->tipo_salario;

            $subdelegacion = $subdelegaciones[$tipos_clase[$empleado->id_categoria]];
            $subdelegacion = strtoupper(str_pad($subdelegacion, 2, "0", STR_PAD_RIGHT));

            $tipoDocumento = $tipos_documentos[$tipos_clase[$empleado->id_categoria]];
            $tipoDocumento = ($tipoDocumento == NULL) ? 400 : strtoupper(str_pad($tipoDocumento,3,"0",STR_PAD_RIGHT));

            $guia = $subdelegacion.$tipoDocumento;

            $curp = str_pad($empleado->curp, 18, "0", STR_PAD_RIGHT);
            $ide = '          ';

            $txt .= $registro.$digito_verificador.$num_seg_social.$aPaterno.$aMaterno.$nombre.$salario_diario_integrado.'000000'.$tipo_trabajador.$tipo_salario.'0'.$fecha_alta.'000'.'  '.'08'.$guia.$ide.' '.$curp.'9'."\r\n";

            // Empleado::where('id', $empleado->id)->update([
            //     'folio_alta' => $folio_interno,
            //     'folio_alta_interno' => $folio_interno
            // ]);
        }

        $numMovimientos = str_pad($empleados->count(), 6, "0", STR_PAD_LEFT);
        $txt .=  "*************                                           ".$numMovimientos."                                                                       ".$guia."                             9";

        $fileName = 'DiscoAlta_'.date('Y_m_d_h_i_s').'.txt';

        // use headers in order to generate the download
        $headers = [
        'Content-type' => 'text/plain', 
        'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        // make a response, with the content, a 200 response code and the headers
        return Response::make($txt, 200, $headers);
    }


    public function generarDiscoBaja(Request $request)
    {
        tienePermiso('avisos_imss');
        cambiarBase(Session::get('base'));
        $txt = '';
        
        $folio_interno = $request->f;

        $categorias = $tipos_clase = [];

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_BAJA])
            ->whereYear('fecha_baja', '>', '2000') // not null && != 0000-00-00
            ->where('folio_baja', '')
            // ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')
            ->get();
                    

        // Sino hay empleados elegibles, regresamos
        if($empleados->count() <= 0){

	        session()->flash('danger', 'Todos los empleados cuentan ya con un folio asignado. No fue generado el archivo.');
	        return redirect()->route('afiliaciones.bajas');

        }

        foreach($empleados as $empleado){
            $ids_categorias[] = $empleado->id_categoria;
        }

        $categorias = DB::connection('empresa')
            ->table('categorias')
            ->select('id', 'tipo_clase')
            ->whereIn('id', $ids_categorias)
            ->where('estatus', 1)
            ->get();

        foreach($categorias as $categoria){
            $tipos_clase[$categoria->id] = $categoria->tipo_clase;
        }

        $registro_patronal = DB::table('registro_patronal')
            ->select('id', 'num_registro_patronal', 'subdelegacion', 'tipo_documento')
            ->whereIn('id', $tipos_clase)
            ->where('estatus', 1)
            ->get();

        foreach($registro_patronal as $reg){
            $registro_patronal[$reg->id] = $reg->num_registro_patronal;
            $subdelegaciones[$reg->id] = $reg->subdelegacion;
            $tipos_documentos[$reg->id] = $reg->tipo_documento;
        }

        
        foreach($empleados as $empleado){
            
            $partes = explode('.', $empleado->salario_diario_integrado);
            $parte_decimal = substr(end($partes), 0, 2);
            $parte_entera = $partes[0];
            $parte_entera = str_pad($parte_entera, 4, "0" ,STR_PAD_LEFT);
            $salario_diario_integrado = $parte_entera.$parte_decimal;

            $reg_patronal = $registro_patronal[$tipos_clase[$empleado->id_categoria]];

            $registro = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 0, 10);
            $registro = str_pad($registro, 10, " ", STR_PAD_RIGHT);

            $digito_verificador = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 10, 1);
            $digito_verificador = str_pad($digito_verificador, 1, " ", STR_PAD_RIGHT);

            $num_seg_social = substr($empleado->nss, 0, 11);
            $num_seg_social = str_pad($num_seg_social, 11, " ", STR_PAD_RIGHT);

            $fecha_alta = date('dmY', strtotime($empleado->fecha_alta));
            $aPaterno = $this->limpiarCadena($empleado->apaterno);
            $aMaterno = $this->limpiarCadena($empleado->amaterno);
            $nombre = $this->limpiarCadena($empleado->nombre);   
            $tipo_trabajador = strtoupper(str_pad($empleado->tipo_empleado, 1, " ", STR_PAD_RIGHT));
            $tipo_salario = ($empleado->tipo_salario == NULL) ? 0 : $empleado->tipo_salario;

            $subdelegacion = $subdelegaciones[$tipos_clase[$empleado->id_categoria]];
            $subdelegacion = strtoupper(str_pad($subdelegacion, 2, "0", STR_PAD_RIGHT));

            $tipoDocumento = $tipos_documentos[$tipos_clase[$empleado->id_categoria]];
            $tipoDocumento = ($tipoDocumento == NULL) ? 400 : strtoupper(str_pad($tipoDocumento,3,"0",STR_PAD_RIGHT));

            $guia = $subdelegacion.$tipoDocumento;

            $curp = str_pad($empleado->curp, 18, "0", STR_PAD_RIGHT);
            $ide = '          ';

            switch ($empleado->causa_baja) {

                case 'TERMINO DE CONTRATO':
                    $causa=1;
                    break;
                case 'SEPARACION VOLUNTARIA':
                    $causa=2;
                    break;
                case 'ABANDONO DE EMPLEO':
                    $causa=3;
                    break;
                case 'DEFUNCION':
                    $causa=4;
                    break;
                case 'CLAUSURA':
                    $causa=5;
                    break;
                case 'AUSENTISMO':
                    $causa=7;
                    break;
                case 'RESCISION DE CONTRATO':
                    $causa=8;
                    break;
                case 'JUBILACION':
                    $causa=9;
                    break;
                case 'PENSION':
                    $causa=A;
                    break;
                case 'OTRAS':
                    $causa=6;
                    break;
                default:
                    $causa=0;
                    break;
            }

            $txt .= $registro.$digito_verificador.$num_seg_social.$aPaterno.$aMaterno.$nombre.'               '.$fecha_alta.'     '.'02'.$guia.$ide.$causa.'                  '.'9'."\r\n";

            // Empleado::where('id', $empleado->id)->update([
            //     'folio_baja' => $folio_interno,
            //     'folio_baja_interno' => $folio_interno
            // ]);
        }

        $numMovimientos = str_pad($empleados->count(), 6, "0", STR_PAD_LEFT);
        $txt .=  "*************                                           ".$numMovimientos."                                                                       ".$guia."                             9";

        $fileName = 'DiscoBaja_'.date('Y_m_d_h_i_s').'.txt';

        // use headers in order to generate the download
        $headers = [
            'Content-type' => 'text/plain', 
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        // make a response, with the content, a 200 response code and the headers
        return Response::make($txt, 200, $headers);
    }


    public function generarDiscoModificaciones(Request $request)
    {
        tienePermiso('avisos_imss');
        cambiarBase(Session::get('base'));
        $txt = '';
        
        $folio_interno = $request->f;

        $categorias = $tipos_clase = [];

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_BAJA])
            ->where('folio_modificacion', '')
            ->whereYear('estatus_folio_modificacion', 1) 
            // ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
            ->orderBy('apaterno', 'asc')
            ->get();
                    

        // Sino hay empleados elegibles, regresamos
        if($empleados->count() <= 0){

	        session()->flash('danger', 'No hay empleados que cumplan con los criterios para generar el archivo.');
	        return redirect()->route('afiliaciones.modificaciones');

        }



        foreach($empleados as $empleado){
            $ids_categorias[] = $empleado->id_categoria;
        }
        $categorias = DB::connection('empresa')
            ->table('categorias')
            ->select('id', 'tipo_clase')
            ->whereIn('id', $ids_categorias)
            ->where('estatus', 1)
            ->get();

        foreach($categorias as $categoria){
            $tipos_clase[$categoria->id] = $categoria->tipo_clase;
        }

        $registro_patronal = DB::table('registro_patronal')
            ->select('id', 'num_registro_patronal', 'subdelegacion', 'tipo_documento')
            ->whereIn('id', $tipos_clase)
            ->where('estatus', 1)
            ->get();

        foreach($registro_patronal as $reg){
            $registro_patronal[$reg->id] = $reg->num_registro_patronal;
            $subdelegaciones[$reg->id] = $reg->subdelegacion;
            $tipos_documentos[$reg->id] = $reg->tipo_documento;
        }

        
        foreach($empleados as $empleado){
            
            $partes = explode('.', $empleado->salario_diario_integrado);
            $parte_decimal = substr(end($partes), 0, 2);
            $parte_entera = $partes[0];
            $parte_entera = str_pad($parte_entera, 4, "0" ,STR_PAD_LEFT);
            $salario_diario_integrado = $parte_entera.$parte_decimal;

            $reg_patronal = $registro_patronal[$tipos_clase[$empleado->id_categoria]];

            $registro = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 0, 10);
            $registro = str_pad($registro, 10, " ", STR_PAD_RIGHT);

            $digito_verificador = substr($registro_patronal[$tipos_clase[$empleado->id_categoria]], 10, 1);
            $digito_verificador = str_pad($digito_verificador, 1, " ", STR_PAD_RIGHT);

            $num_seg_social = substr($empleado->nss, 0, 11);
            $num_seg_social = str_pad($num_seg_social, 11, " ", STR_PAD_RIGHT);

            $fecha_alta = date('dmY', strtotime($empleado->fecha_alta));
            $aPaterno = $this->limpiarCadena($empleado->apaterno);
            $aMaterno = $this->limpiarCadena($empleado->amaterno);
            $nombre = $this->limpiarCadena($empleado->nombre);   
            $tipo_trabajador = strtoupper(str_pad($empleado->tipo_empleado, 1, " ", STR_PAD_RIGHT));
            $tipo_salario = ($empleado->tipo_salario == NULL) ? 0 : $empleado->tipo_salario;

            $subdelegacion = $subdelegaciones[$tipos_clase[$empleado->id_categoria]];
            $subdelegacion = strtoupper(str_pad($subdelegacion, 2, "0", STR_PAD_RIGHT));

            $tipoDocumento = $tipos_documentos[$tipos_clase[$empleado->id_categoria]];
            $tipoDocumento = ($tipoDocumento == NULL) ? 400 : strtoupper(str_pad($tipoDocumento,3,"0",STR_PAD_RIGHT));

            $guia = $subdelegacion.$tipoDocumento;

            $curp = str_pad($empleado->curp, 18, "0", STR_PAD_RIGHT);
            $ide = '          ';

            $txt .= $registro.$digito_verificador.$num_seg_social.$aPaterno.$aMaterno.$nombre.$salario_diario_integrado.'000000'.'0'.$tipo_salario.'0'.$fecha_alta.'000'.'  '.'07'.$guia.$ide.' '.$curp.'9'."\r\n";
            // echo $registro.$digitover.$Numse.$apaterno.$amaterno.$nombre.$salDiarioInt.'000000'.'0'.$TipoSalario.'0'.$fechaaltabaja.'000'.'  '.'07'.$guia.$ide.' '.$Curp.'9'."\r\n";

            // Empleado::where('id', $empleado->id)->update([
            //     'folio_modificacion' => $folio_interno,
            //     'folio_modif_interno' => $folio_interno
            // ]);
        }

        $numMovimientos = str_pad($empleados->count(), 6, "0", STR_PAD_LEFT);
        $txt .=  "*************                                           ".$numMovimientos."                                                                       ".$guia."                             9";

        $fileName = 'DiscoModificacion_'.date('Y_m_d_h_i_s').'.txt';

        // use headers in order to generate the download
        $headers = [
        'Content-type' => 'text/plain', 
        'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        // make a response, with the content, a 200 response code and the headers
        return Response::make($txt, 200, $headers);
    }

    protected function limpiarCadena($cadena)
    {
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'AAAAAAACEEEEIIIID#OOOOOOUUUUYBSAAAAAAACEEEEIIIID#OOOOOOUUUYYBYRR';

        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
        $cadena = strtoupper(str_pad($cadena,27," ",STR_PAD_RIGHT));
        return $cadena;
    }

}