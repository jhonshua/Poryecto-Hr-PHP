<?php

namespace App\Http\Controllers\Empleado;

use App\EmpleadoLogin;
use App\Http\Controllers\Contabilidad\TimbradoController;
use App\Mail\ResetEmpleadoPassword;
use App\Models\Empresa;
use App\Models\Empleado;
use App\Models\TimbradoEmpleado;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\File;
use DateTime;
use DataTables;
use ZipArchive;
class EmpleadoController extends Controller
{
    use AuthenticatesUsers;

    const LONGITUD_PASSWORD = 7;

    // protected $redirectTo = '/inicio';
    protected $guard = 'empleados';

    /**
     *
     */
    public function showLoginForm()
    {
        return view('auth.empleadoLogin');
    }

    /**
     *
     */
    public function login(Request $request)
    {
        $credenciales = $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:5'
        ]);

        if (Auth::guard('empleados')->attempt($credenciales)) {
            $empleado = new Empleado;

            $empleadoDatos = $empleado->obtenerEmpleadoPorEmail(
                Auth::guard('empleados')->user()->empresa,
                Auth::guard('empleados')->user()->email
            );

            if($empleadoDatos != null){
                $empresa  = Empresa::where('base', Auth::guard('empleados')->user()->empresa)
                    ->where('estatus', 1)
                    ->first();
                /*************************************************** */
                Session::put('base', Auth::guard('empleados')->user()->empresa);
                Session::put('empresa', $empresa);
                Session::put('empleado', $empleadoDatos);
                /*************************************************** */
                return redirect()->intended(route('empleado.inicio'));
            } else{
                return back()
                    ->withInput($request->only('email'))
                    ->with('mensaje', 'Datos de usuario no encontrados');
            }
        }
        Session::forget('empleado');
        return back()
            ->withInput($request->only('email'))
            ->with('mensaje', 'Usuario o password incorrecto');
    }

    /**
     *
     */

    public function loginApi(Request $request,Client $client)
    {
        try {
            $response = $client->request('POST', "https://www.hrsystem.com.mx/api/index.php/auth/login_norma",[
                'form_params' => [
                    'username' => $request->email,
                    'password' => $request->password,
                ]
            ]);
        } catch (ClientException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }

        $login = json_decode($response->getBody());

        if(!empty($login->exito) && $login->exito){
            $empleado = $login->datos;
            //dd($empleado);
            if($empleado != null){
                /*************************************************** */
                Session::put('base', $empleado->empresa->base);
                Session::put('empresa', get_object_vars($empleado->empresa));
                Session::put('empleado', (array)get_object_vars($empleado));
                /*************************************************** */
                return redirect()->intended(route('empleados.inicio'));
            } else{ 
                return back()
                    ->withInput($request->only('email'))
                    ->with('mensaje', 'Datos de usuario no encontrados');
            }

        }else{
            Session::forget('empleado');
            return back()
                ->withInput($request->only('email'))
                ->with('mensaje', 'Usuario o password incorrecto');
        }

    }




    public function logout(Request $request)
    {
        Auth::guard('empleados')->logout();
        $request->session()->invalidate();
        return redirect()->route('empleado.loginpage');
    }

    public function logoutEmpleado(Request $request)
    {
        Auth::guard('empleados')->logout();
        $request->session()->invalidate();
        return redirect()->route('empleado.loginpage');
    }

    /**
     *
     */
    public function recuperarContrasena(Request $request)
    {
        $empleado = EmpleadoLogin::where('email', $request->email)->first();
        if($empleado){
            $empleadoObj = new Empleado;
            $password = $empleadoObj->generarPassword();
            $empleado->update([
                'password' => bcrypt($password),
                'tmp' => $password
            ]);

            // enviar mail al usuario
            Mail::to($request->email)->queue(new ResetEmpleadoPassword($password));

            return response()->json(['ok' => 1, 'mensaje' => 'Se envió un email con la nueva contraseña. Por favor revisa tu bandeja de entrada o tu bandeja de Spam.']);
        } else {
            return response()->json(['ok' => 0, 'mensaje' => 'No se encontró algún registro con el email proporcioando. Verifique']);
        }
    }

    public function recuperarContrasenaApi(Request $request,Client $client)
    {
        try {
            $response = $client->request('POST', "https://www.hrsystem.com.mx/api/index.php/auth/resetPassword",[
                'form_params' => [
                    'username' => $request->email,
                ]
            ]);
        } catch (ClientException $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }

        $recuperar = json_decode($response->getBody());

        if($recuperar->exito){
            return response()->json(['ok' => 1, 'mensaje' => $recuperar->mensaje]);
        }else{
            return response()->json(['ok' => 0, 'mensaje' => $recuperar->mensaje]);
        }
    }

    public function syncPass(){
        $query = "SELECT *
        FROM singh.usuarios
        WHERE estatus = 20;";
        $emple = DB::connection('empresa')->select($query);

        foreach($emple as $e){

            $query = "UPDATE singh.usuarios SET estatus = 1 WHERE id = " . $e->id;
            $emple = DB::connection('empresa')->select($query);
        }
    }

    public function avisoPrivacidadEmpleado(Request $request)
    {
        $id = Session::get('empleado')['id'];
        $correo = Session::get('empleado')['correo'];
        if($request->ajax()){
            $usuario=EmpleadoLogin::where('email',$correo)->update(['avisos' => 1]);
            $avisos = 1;
            return response()->json(['mensaje'=>$avisos]);
        }else{
            if ($request->post()) {

                $usuario=EmpleadoLogin::where('email',$correo)->update(['avisos' => 1]);
                $avisos = 1;

            } else {

                $avisos=EmpleadoLogin::select('avisos')->where('email',$correo)->first();
                $avisos= $avisos->avisos;

            }
            return view('privacy.privacy_empleado', compact('avisos'));
        }
    }

    public function bandeja(Request $request)
    {
        return view('empleados.empleado.inicio');
    }

    public function convertirPasswords(){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        $logins = EmpleadoLogin::where('fecha_edicion', null)->get();
        $characters = '023456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        foreach ($logins as $login) {

            if (empty($login->tmp)) {
                $password = '';
                for ($i = 0; $i < 7; $i++) {
                    $password .= $characters[rand(0, $charactersLength - 1)];
                }
            } else {
                $password = $login->tmp;
            }
            $login->password = bcrypt($password);
            $login->tmp = $password;
            $login->fecha_edicion = date('Y-m-d H:i:s');
            $login->save();
        }
        echo 'terminado';
    }

    /*
    ----------------------------------------------- R E C I B O S ------------------------------------------
    */

    public function recibos(Request $request){
        $id_empleado = Session::get('empleado')['id'];

        cambiarBase(Session::get('singh'));
        $empresas= Empresa::where('estatus',1)->where('sss',0)->orderBy('id', 'desc')->get();


        cambiarBase(Session::get('base'));
        $empleado=Empleado::where('id',$id_empleado)->first();
        $rfc_emp=$empleado->rfc;

        foreach ($empresas as $empresa) {
            $base=$empresa->base;
            $id_e=$empresa->id;
            $querytimbrado = "SELECT * from $base.timbrado  where receptor='$rfc_emp' and sello_sat<>'error'";
            $timbrado = count(DB::connection('empresa')->select($querytimbrado));
            if($timbrado>0){
                $querymisc="SELECT *,t.id as id_timbre,$id_e as id_e,'$base' as empresa from $base.timbrado t join $base.periodos_nomina p on t.id_periodo=p.id where t.receptor='$rfc_emp' and sello_sat<>'error' order by t.id desc";
                $misc[$empresa->id]=DB::connection('empresa')->select($querymisc);
            }
        }

        return view('empleados.empleado.recibos.inicio', compact('misc'));
    }

    public function verRecibosEmpleado($id_empleado){

        cambiarBase(Session::get('base'));

        return TimbradoEmpleado::where('id_empleado',$id_empleado)->with('periodo');
    }

    public function zip_PDF_empleado($empleado){
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];

        $url_repo = public_path()."/repositorio/".$repo;
        $folder_repositorio = $url_repo . '/'.$empleado.'';
        /* Checar si existen directorios, si no crearlos*/
        if(!File::exists($folder_repositorio)) {
            File::makeDirectory($folder_repositorio, $mode = 0777, true, true);
        }

        /* CREAMO URL ZIP */
        $fecha = new DateTime();
        $zip_file = $url_repo . '/'.$empleado.'/pdf_'.$empleado.'_'.$fecha->format('dmYHis').'.zip';

        if (File::exists($zip_file))
        {
            return response()->download($zip_file);
        }

        cambiarBase($base);

        $timbres = DB::connection('empresa')
            ->table('timbrado')
            ->where('id_empleado', $empleado)
            ->where('sello_sat','<>','error')
            ->get();
        //dump($timbres);
        // Initializing PHP class
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE);

        foreach($timbres as $timbre){
            $url_pdf = $url_repo . "/" . $timbre->id_empleado .'/timbrado/archs_pdf/'. $timbre->file_pdf;
            // dump($url_pdf,File::exists($url_pdf));
            if (File::exists($url_pdf))
            {
                $zip->addFile($url_pdf, $timbre->file_pdf);
            }
        }
        $zip->close();

        return response()->download($zip_file);
    }

    // igual a la funcion de timbradoEmpleadoControler, implementada en este modulo tomando en cuneta una posible separación de aplicació
    public function genera_pdf($id_empleado,$id_repo,$xml,$id_timbre,$base)
    {

        cambiarBase($base);
        $timbre = TimbradoEmpleado::find($id_timbre);
        $pdf = str_replace('.xml', '.pdf', $xml);
        $url = storage_path()."/app/public/repositorio/". $id_repo . "/" .  $id_empleado . "/timbrado/";
        $arch_xml = $url . 'archs_cfdi/'.$xml;
        $arch_pdf = $url . 'archs_pdf/'.$pdf;

        $xml_raw = trim(htmlspecialchars_decode(html_entity_decode($timbre->respuesta_pac))," \t\n\r\"");

        if(!File::exists($arch_xml)){
            $file = fopen($arch_xml, "w");
            fwrite($file, $xml_raw);
            fclose($file);
            chmod($arch_xml, 0777);
        }

        if (File::exists($arch_pdf))
        {
            return response()->download($arch_pdf);
        }else{
            $pdfa=TimbradoController::genera_pdf($arch_xml,$arch_pdf);
            return $pdfa->download($pdf);
        }
    }

    public function download_soap_xml($id ,$id_empleado,$archivo)
    {
        $url = storage_path() . "/app/public/repositorio/" . $id . "/" . $id_empleado . "/timbrado/archs_cfdi/" . $archivo;

        //verificamos si el archivo existe y lo retornamos
        if (File::exists($url)) {
            return response()->download($url);
        }
        //si no se encuentra lanzamos un error 404.
        abort(404);
    }
}
