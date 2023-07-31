<?php

namespace App\Http\Controllers\Sistema;

use App\EmpleadoLogin;
use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/*Se comentaron lineas de codigo originales debido a que la libreria snappy en el servidor de produccion no puede ser
    ejecutado porque cuando se le implemento el certificado ssl ya no pudo vivir el correcto funcionamiento por las librerias instaladas ya sean nuevas o
    se actualizaron, para solucionar esto estamos apuntando directamente al servidor de produccion para tomar los archivos que necesitamos
    y manejarlo de manera local la generacion de credenciales
 * */

class GeneradorCredencialesController extends Controller
{
    public function index(){
        $empresas=Empresa::where('estatus',1)->get();
        return view('crear_credencial.index',compact('empresas'));
    }

    public function empleados(Request $request){
        $empresa=substr('empresa000000', 0, '-'.strlen($request->id)).$request->id;
        cambiarBase($empresa);
        $empleados= Empleado::where('estatus',1)->with(['departamento','puesto'])->get();

        return response()->json(['data' => $empleados]);
    }

    public function createCredenciales(Request $request){

        $empresa=substr('empresa000000', 0, '-'.strlen($request->id)).$request->id;
        cambiarBase($empresa);
        $empleados= Empleado::where('estatus',1)->with(['departamento','puesto'])->get();

        $datos=array();
        foreach ($empleados as $key => $empleado) {
            $empleado= array(
                'id'=> $empleado->id,
                'nombre'=> $empleado->nombre.' '.$empleado->apaterno.' '.$empleado->amaterno,
                'puesto'=> $empleado->puesto->puesto,
                'curp'=> $empleado->curp,
                'mail'=> $empleado->correo,
                'rfc'=> $empleado->rfc,
                'afilacion'=> substr('0000', strlen($empleado->id)).$empleado->id,
                'file_fotografia' => $empleado->file_fotografia,
            );
            array_push($datos, $empleado);
            $this->borrarCredencial($empleado['id'],$request->id);
            $this->generarCredencial($empleado,$request->id);
        }

        return response()->download($this->descargarCredencial($datos,$request->id));
    }

    public function createCredencial(Request $request){

        $empresa=substr('empresa000000', 0, '-'.strlen($request->id_empresa)).$request->id_empresa;
        cambiarBase($empresa);
        $empleado = Empleado::where('id',$request->id)->first();

        $empleado= array(
            'id'=> $empleado->id,
            'nombre'=> $empleado->nombre.' '.$empleado->apaterno.' '.$empleado->amaterno,
            'puesto'=> $empleado->puesto->puesto,
            'curp'=> $empleado->curp,
            'mail'=> $empleado->correo,
            'rfc'=> $empleado->rfc,
            'afilacion'=> substr('0000', strlen($empleado->id)).$empleado->id,
            'file_fotografia' => $empleado->file_fotografia,
        );
        $this->borrarCredencial($empleado['id'],$request->id_empresa);
        $this->generarCredencial($empleado,$request->id_empresa);

        $datos= array();
        array_push($datos, $empleado);

        return response()->download($this->descargarCredencial($datos,$request->id_empresa));
    }
    protected $url ="https://hrsystem.com.mx/vcard/";
    public function generarCredencial($empleado,$id_empresa){
        $usuario = EmpleadoLogin::where('email', $empleado['mail'])->first();
        $empleado['qr'] =$this->url.$usuario->codigo;
        if($usuario->codigo =="" || $usuario->codigo == null){
            $empleado_codigo = new Empleado();
            $usuario->codigo=$empleado_codigo->generarPassword(10);
            $usuario->save();
            $empleado['qr'] = $this->url.$usuario->codigo;
        }

        $empleado['perfil'] = ($empleado['file_fotografia']) ? 'https://hrsystem.com.mx/storage/repositorio/'.$id_empresa .'/'. $empleado['id'] .'/'. $empleado['file_fotografia'] : 'https://hrsystem.com.mx/public/img/avatar.png';
        //No borrar comentario(ver nota en el controlador GeneradorCredenciales)
        //$empleado['perfil'] = ($empleado['file_fotografia']) ? 'storage/repositorio/'.$id_empresa .'/'. $empleado['id'] .'/'. $empleado['file_fotografia'] : 'public/img/avatar.png';

        $snappy = \App::make('snappy.image');
        $html_credencial_alfrente = view('crear_credencial.credencial_alfrente',compact('empleado'))->render();
        $html_credencial_trasera = view('crear_credencial.credencial_trasera',compact('empleado'))->render();

        $snappy->setOption('width',2000);
        $snappy->setOption('height',800);
        $snappy->setOption('crop-w',1000);
        $snappy->setOption('crop-h',630);
        $snappy->setOption('images',true);
        $snappy->setOption('quality',100);


        $nameImage_credencial_alfrente ='credenciales/'.$id_empresa.'/'.$empleado['id'].'_alfrente.png';
        $nameImage_credencial_trasera = 'credenciales/'.$id_empresa.'/'.$empleado['id'].'_trasera.png';

        if (!file_exists(storage_path('app/public/trash/'.$nameImage_credencial_alfrente))) {

            try {
                $snappy->generateFromHtml($html_credencial_alfrente,storage_path('app/public/trash/'.$nameImage_credencial_alfrente));
                $snappy->generateFromHtml($html_credencial_trasera,storage_path('app/public/trash/'.$nameImage_credencial_trasera));
            } catch (\Throwable $th) {
                
            }
           
        }
        
    }

    public function descargarCredencial($empleados,$id_empresa){
        $fecha = new \DateTime();
        $zip_file = storage_path('app/public/trash/'.$id_empresa.'_'. $fecha->format('dmYHis') . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE);

        foreach ($empleados as $key => $empleado) {
            $nameImage_credencial_alfrente ='credenciales/'.$id_empresa.'/'.$empleado['id'].'_alfrente.png';
            $nameImage_credencial_trasera = 'credenciales/'.$id_empresa.'/'.$empleado['id'].'_trasera.png';

            try {
                $path_alfrente = storage_path('app/public/trash/'.$nameImage_credencial_alfrente);
                $zip->addFile($path_alfrente, $empleado['nombre'].'_alfrente.png');

                $path_trasera = storage_path('app/public/trash/'.$nameImage_credencial_trasera);
                $zip->addFile($path_trasera, $empleado['nombre'].'_trasera.png');
            } catch (\Throwable $th) {
                
            }
            
        }

        $zip->close();
        return $zip_file;
    }


    public function borrarCredencial($id_empleado,$id_empresa){
        $nameImage_credencial_alfrente ='credenciales/'.$id_empresa.'/'.$id_empleado.'_alfrente.png';
        $nameImage_credencial_trasera = 'credenciales/'.$id_empresa.'/'.$id_empleado.'_trasera.png';
        if (file_exists(storage_path('app/public/trash/'.$nameImage_credencial_alfrente)))
            File::delete([storage_path('app/public/trash/'.$nameImage_credencial_alfrente),storage_path('app/public/trash/'.$nameImage_credencial_trasera)]);

    }
}
