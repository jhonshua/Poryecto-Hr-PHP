<?php

use App\Models\Bitacora;
use App\Events\AvisoSistema;
use Illuminate\Http\Request;
use App\Events\EmailGenerico;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
// use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

function mes($num_mes, $completo = false)
{
    $mes = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
        '1' => 'Enero',
        '2' => 'Febrero',
        '3' => 'Marzo',
        '4' => 'Abril',
        '5' => 'Mayo',
        '6' => 'Junio',
        '7' => 'Julio',
        '8' => 'Agosto',
        '9' => 'Septiembre',
    ];

    return ($completo) ? $mes[$num_mes] : substr($mes[$num_mes], 0, 3);
}

function dia($num_dia)
{
    $dia = [
        1 => 'Lun',
        2 => 'Mar',
        3 => 'Mie',
        4 => 'Jue',
        5 => 'Vie',
        6 => 'Sab',
        7 => 'Dom',
    ];

    return $dia[$num_dia];
}

function cambiarBase($base)
{
    if (empty($base)) return false;
    try {
        if ($base == 'singh') {
            Config::set('database.connections.singh.database', 'singh');
            DB::reconnect('singh');
        } else {
            Config::set('database.connections.empresa.database', strtolower($base));
            DB::reconnect('empresa');
        }
    } catch (Exception $e) {
        dd($e);
    }
}

function formatoAFecha($fecha, $fecha_completa = false)
{
    if (empty($fecha) || strpos($fecha, '0000-00-00') !== false) {
        return 'N/A';
    }

    $fecha = explode('-', $fecha);
    $dia_tiempo = explode(' ', $fecha[2]);
    $format_fecha = $dia_tiempo[0] . '/' . mes($fecha[1]) . '/' . $fecha[0];

    if ($fecha_completa && isset($dia_tiempo[1]))
        $format_fecha .= ' ' . $dia_tiempo[1];

    return  $format_fecha;
}

function enviarMail($data, $client = null)
{
    if (count($data['para']) <= 0)
        return false;


    if ($client) {
        try {
            $response = $client->request('POST', "https://www.hrsystem.com.mx/api/index.php/norma/email", [
                'form_params' => [
                    $data,
                ]
            ]);
        } catch (ClientException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }

        $correo = json_decode($response->getBody());
        return true;
    }
    EmailGenerico::dispatch($data['para'], $data['titulo'], $data['cuerpo'], $data['btnTxt'], $data['btnUrl']);
    return true;
}


function tienePermiso($modulo = '')
{
    if (!Session::has('usuarioPermisos')) {
        header("Location: " . app('url')->route('seleccionar.empresa'));
        die;
    }
}

function tienePermisoA($modulo = '')
{

    if (!Session::has('usuarioPermisos')) {
        header("Location: " . app('url')->route('empresa.cambiar'));
        die;
    }
    if (!array_key_exists($modulo, Session::get('usuarioPermisos'))) {
        // header("Location: " . app('url')->route('sin.acceso'));
        die;
    } else return true;
}

function logEmpresa($base, $usuario, $evento)
{
    cambiarBase($base);
    DB::connection('empresa')->table('logs')->insert(
        [
            'usuario' => $usuario,
            'evento' => $evento,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]
    );
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

function logAutofacturador($usuario, $evento, $id_cfdi='', $id_pago='', $response_soap='')
{
    cambiarBase(Auth::user()->clientes->base);
    DB::connection('empresa')->table('logs')->insert(
        [
            'usuario' => $usuario,
            'evento' => $evento,
            'id_cfdi' => $id_cfdi,
            'id_pago' => $id_pago,
            'response_soap' => $response_soap,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]
    );
}

function agregarABitacora($base, $idEvento, $tipo, $referencia, $descripcion)
{
    cambiarBase($base);
    //Se verifica el estatus del evento
    $evento = DB::connection('empresa')
        ->table('eventos')
        ->select('estatus')
        ->where('id', $idEvento)
        ->first();

    if ($evento->estatus == 1) { //Estatus activo
        //Se verifica si existen correos adjuntos al evento
        $involucrados = DB::connection('empresa')
            ->table('eventos_correos')
            ->select('correo')
            ->where('id_evento', $idEvento)
            ->get();

        if (count($involucrados) > 0) {
            foreach ($involucrados as $usr) {
                $avisos[] = [
                    'usuario' => $usr->correo,
                    'descripcion' => $descripcion,
                    'estatus' => 0,
                    'tipo' => $tipo,
                    'referencia' => $referencia,
                    'genero' => Auth::user()->email,
                    'evento' => $idEvento,
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ];
            }
            Bitacora::insert($avisos);
        }
    }
}

function envioAvisosXMail($base, $idEvento, $var1 = null, $var2 = null, $var3 = null, $var4 = null)
{
    cambiarBase($base);

    //Se obtiene el evento a realizar
    $evento = DB::connection('empresa')
        ->table('eventos')
        ->find($idEvento);

    $destinatarios = DB::connection('empresa')
        ->table('eventos_correos')
        ->select('correo')
        ->where('id_evento', $idEvento)
        ->get();
    //dd($idEvento,$evento);

    if ($evento->estatus == 1 && count($destinatarios) > 0) { //Estatus activo


        //Se busca y remplazan los posibles elementos enviados por parametros auxiliares
        $cuerpo = $evento->cuerpo;
        $cuerpo = str_replace("#usuario", Auth::user()->email, $cuerpo);
        $cuerpo = str_replace("#jefe", Auth::user()->email_jefe, $cuerpo);
        $cuerpo = str_replace("#ejecutivo", Auth::user()->email_ejecutivo, $cuerpo);
        $cuerpo = str_replace("#empresa", Session::get('empresa')['razon_social'], $cuerpo);
        $fecha = date('d') . "/" . substr(mes(date('n')), 0, 3) . "/" . date('Y');
        $cuerpo = str_replace("#fecha", $fecha, $cuerpo);
        $asunto = $evento->nombre . ' / ' . Session::get('empresa')['razon_social'];

        //Se busca el nombre de empleado para insertarlo en el texto del correo
        if (strpos($cuerpo, '#empleado') !== false) {
            $empleado = DB::connection('empresa')
                ->table('empleados')
                ->select('nombre', 'apaterno', 'amaterno')
                ->find($var1);
            $empleadoNombre = $empleado->nombre . ' ' . $empleado->apaterno . ' ' . $empleado->amaterno;
            $cuerpo = str_replace("#empleado", $empleadoNombre, $cuerpo);
        }

        //Se busca el periodo para insertarlo en el texto del correo
        if (strpos($cuerpo, '#periodo') !== false) {
            $cuerpo = str_replace("#periodo", $var1, $cuerpo);
        }

        //Se busca el nombre de la encuesta para insertarlo en el texto del correo
        if (strpos($cuerpo, '#EncuestaUser') !== false) {
            $cuerpo = str_replace("#EncuestaUser", $var2, $cuerpo);
        }

        //Se busca el contrato para insertarlo en el texto del correo
        if (strpos($cuerpo, '#contra') !== false) {
            $cuerpo = str_replace("#contra", $var3, $cuerpo);
        }

        //Se busca el tipo de movimiento para insertarlo en el texto del correo
        if (strpos($cuerpo, '#movimiento') !== false) {
            $cuerpo = str_replace("#movimiento", $var2, $cuerpo);
            if (strpos($cuerpo, '#clvIncapa') !== false) {
                $cuerpo = str_replace("#clvIncapa", $var3, $cuerpo);
            }
        }

        //Se busca la fecha de baja anterior para insertarlo en el texto del correo
        if (strpos($cuerpo, '#anterior') !== false) {
            $cuerpo = str_replace("#anterior", $var2, $cuerpo);
            if (strpos($cuerpo, '#nueva') !== false) {
                $cuerpo = str_replace("#nueva", $var3, $cuerpo);
            }
        }
        // Se envian los mails
        AvisoSistema::dispatch($destinatarios->toArray(), $cuerpo);
    }
}


function tienePermisoABool($modulo = '')
{
    if (!Session::has('usuarioPermisos')) {
        header("Location: " . app('url')->route('home'));
        die;
    }
    if (!array_key_exists($modulo, Session::get('usuarioPermisos'))) {
        return false;
    } else if (Session::get('usuarioPermisos')[$modulo] == 1) {
        return true;
    } else {
        return false;
    }
}
function obtenerColumnasTabla($tabla)
{
    return DB::connection('empresa')->getSchemaBuilder()->getColumnListing($tabla);
}

function elegirBase()
{
    //dump(Session::get('base'));
    if (empty(Session::get('base'))) {

        return redirect()->route('home');
    }

    //validación para unificar  los trabajadores de las subempresas en una sola
    if (Session::get('base') == 'empresa000162' || Session::get('base') == 'empresa000175') { // trabajar con la base de franklin
        cambiarBaseAApi('empresa000059');
    } else if (Session::get('base') == 'empresa000174' || Session::get('base') == 'empresa000161') { //Trabajar con la base de consorcio Amesa
        cambiarBaseAApi('empresa000060');
    } else if (Session::get('base') == 'empresa000163') { //Trabajar con la base de 61 INMOBILIARIA GAOS S. DE R.L. DE C.V.   163 INMOBILIARIA GAOS S. DE R.L. DE C.V. CLIENTE
        cambiarBaseAApi('empresa000061');
    } else if (Session::get('base') == 'empresa000116' || Session::get('base') == 'empresa000185') { //  GYMCO SPORT WEAR SA DE CV   GYMCO SPORT Y SPA, S. DE R.L. DE C.V. (CLIENTE)
        cambiarBaseAApi('empresa000063'); // GYMCO SPORT Y SPA, S. DE R.L. DE C.V.
    } else if (Session::get('base') == 'empresa000176' || Session::get('base') == 'empresa000177' || Session::get('base') == 'empresa000178' || Session::get('base') == 'empresa000179' || Session::get('base') == 'empresa000183' || Session::get('base') == 'empresa000184') { // trabajar con la base de Carls
        // dd("si");
        cambiarBaseAApi('empresa000177');
    } else if (Session::get('base') == 'empresa000169' || Session::get('base') == 'empresa000169' || Session::get('base') == 'empresa000042' || Session::get('base') == 'empresa000042') { // trabajar con la base de Carls
        cambiarBaseAApi('empresa000042');
    } else {
        cambiarBaseAApi(Session::get('base'));
    }
}

function cambiarBaseAApi($base)
{
    //cambio para ya no usar la api
    cambiarBase($base);
    return true;


    //codigo para apa (ya no se utilizará)
    if (empty($base)) return false;

    try {

        Config::set('database.connections.empresa.database', $base);
        DB::purge("empresa");
        DB::reconnect('empresa');
        // dd(DB::getConnections());
        return true;
    } catch (Exception $e) {

        return false;
    }

}
