<?php

namespace App\Http\Controllers\Herramientas;

//use App\Models\Evento;
use Illuminate\Http\Request;

use App\EmpleadoLogin;
use App\Models\Vcardg;
use App\Models\Vcard_info;
use App\Models\Empresa;
use App\Models\Empleado;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use JeroenDesloovere\VCard\VCard;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use File;
use Input;

class VcardController extends Controller
{
    /**
     * 
     */
    public function inicio()
    {
        //tienePermisoA('eventos');
        return view('herramientas.vcard.inicio');
    }
    function datavcard($empleado){
        
        $empleado_empresa=DB::connection('empresa')
                        ->table('empleados')
                        ->join('puestos','puestos.id','=','empleados.id_puesto')
                        ->join('departamentos as d','d.id','=','empleados.id_departamento')
                        ->select('empleados.id','empleados.nombre','empleados.apaterno','empleados.amaterno','empleados.correo','file_fotografia','empleados.telefono_movil','d.nombre as departamento','puestos.puesto')
                        ->where('correo',$empleado->email)
                        ->first();

        $vcard_empresas=DB::connection('empresa')
                        ->table('vcards')
                        ->join('vcards_info','vcards_info.idvcard','=','vcards.id')
                        ->get();
        
        if(sizeof($vcard_empresas) === 0){

            $vcard_empresas=Empresa::where('base',$empleado->empresa)->first();
            
            $idempresa=$vcard_empresas->id;
            $direccion=$vcard_empresas->calle_num.' '.$vcard_empresas->colonia .' '.$vcard_empresas->delegacion_municipio.' ,'.$vcard_empresas->codigo_postal.' '.$vcard_empresas->estado;
            $telefono=$vcard_empresas->telefono;
            $email=$vcard_empresas->email;
            $colorfndinp="rgba(255,255,255,1)" ; //color de fondo principal
            $colorfndbtninp="rgba(0,0,0,1)" ;// color de fondo de botonera
            $coloricnbtninp="rgba(255,255,255,1)" ;// color iconos de botonera
            $fndbodyvcardinp="rgba(255,255,255,1)" ;// color de fondo del body de la vcard
            $recfondlistintinp="rgba(255,255,255,1)"; // color de fondo lista interior
            $recletlistinitinp="rgba(0,0,0,1 )"; // color recuadro letra lista interior
            $reclistinticonsinp="rgba(0,0,0,1 )";//color recuadro iconos lista interior
            
            $vcard_empresas=array('idempresa'=>$idempresa,
                                  'direccion'=>$direccion,
                                  'telefono'=>$telefono,
                                  'email'=>$email,
                                  'colorfndinp'=>$colorfndinp,
                                  'colorfndbtninp'=>$colorfndbtninp,
                                  'coloricnbtninp'=>$coloricnbtninp,
                                  'fndbodyvcardinp'=>$fndbodyvcardinp,
                                  'recfondlistintinp'=>$recfondlistintinp,
                                  'recletlistinitinp'=>$recletlistinitinp,
                                  'reclistinticonsinp'=>$reclistinticonsinp);
            
            $status=0;
        }else{
            $status=1;
        }
        return $data=array('empleado_empresa'=>$empleado_empresa,'vcard_empresas'=>$vcard_empresas,'status'=>$status);
    }
    /*Obtener vcard mediante código*/
    public function vcard(Request $request){
        //51e59a61ed  codigo de desarrollo 
        $empleado = EmpleadoLogin::where('codigo',  $request->codigo)->first();

        if(!empty($empleado)){
      
            cambiarBase($empleado->empresa);
            $data=$this->datavcard($empleado);
            $empleado_empresa=$data['empleado_empresa'];
            $vcard_empresas=collect($data['vcard_empresas']);
            $status=$data['status'];
            $codigo=$request->codigo;
            return view('herramientas.vcard.inicio',compact('empleado_empresa','vcard_empresas','codigo','status'));  
        
        }else{
            return view('errors.error_vcard');
        }
    }
    function inicioVcard(){  
        
        return view("herramientas.vcard.iniciovcard");
    }

    /*Agregar y modificar Vcard*/
    public function agregarVcard(Request $request){

        cambiarBase(Session::get('base'));
        $email = Auth::user()->email;
        $empleado = EmpleadoLogin::where('email', $email)->first();

        if(empty($request->idvcard)){  // Si el input vcard esta vacío entonces es una alta de vcard de lo contrario es una modificación
  
            $vcard=new Vcardg;
            $vcard->idempresa=Session::get('empresa')['id'];
            $vcard->direccion=$request->direccion;
            // Configuración de la vcard donde se reciben los colores o se asignan por default
            (empty($request->colorfndinp))? $vcard->colorfndinp="rgba(255,255,255,1)" : $vcard->colorfndinp=$request->colorfndinp; //color de fondo principal
            (empty($request->colorfndbtninp))? $vcard->colorfndbtninp="rgba(0,0,0,1)" : $vcard->colorfndbtninp=$request->colorfndbtninp; // color de fondo de botonera
            (empty($request->coloricnbtninp))? $vcard->coloricnbtninp="rgba(255,255,255,1)" : $vcard->coloricnbtninp=$request->coloricnbtninp; // color iconos de botonera
            (empty($request->fndbodyvcardinp))? $vcard->fndbodyvcardinp="rgba(255,255,255,1)" : $vcard->fndbodyvcardinp=$request->fndbodyvcardinp; // color de fondo del body de la vcard
            (empty($request->recfondlistintinp))? $vcard->recfondlistintinp="rgba(255,255,255,1)" : $vcard->recfondlistintinp=$request->recfondlistintinp; // color de fondo lista interior
            (empty($request->recletlistinitinp))? $vcard->recletlistinitinp="rgba(0,0,0,1 )" : $vcard->recletlistinitinp=$request->recletlistinitinp; // color recuadro letra lista interior
            (empty($request->reclistinticonsinp))? $vcard->reclistinticonsinp="rgba(0,0,0,1 )" : $vcard->reclistinticonsinp=$request->reclistinticonsinp; //color recuadro iconos lista interior
            
            if($request->hasFile('logo_empresa_empleado')){ // Si seleccionaron un logotipo
                $path="storage/repositorio/".Session::get('empresa')['id']."/vcard".'/';  //url ejemplo /public/repositorio/46/vcard/nombre de la imagen    // url donde se guarda la imagen
                $file=$request->logo_empresa_empleado;
                $extension = $file->getClientOriginalName();
                $nombre = time().$extension;
                $file->move($path,$nombre);
                $foto=$nombre;
            }else{ // Si no seleccionaron logotipo manda logo hrsystem por defecto 
                dd('holaa');
                if(Session::get('empresa')['id']=='218'){
                    $foto = "logo_ejem.png";
                }else{
                  $foto = "logoletra.png";  
                }
                
            }
            $vcard->logo_empresa_empleado=$foto;
            $vcard->save();

            foreach($request->link_web as $key1 => $link_web){ // Recorre el numero de links y los inserta la db
                $vcard_info=new Vcard_info;
                if($request->link_web[$key1]!==NULL){
                    $vcard_info->idvcard=$vcard->id;
                    $vcard_info->contacto=$request->link_web[$key1];
                    $vcard_info->tipocontacto=1; // 1 default es link web
                    $vcard_info->save();
                }
            }
            foreach(  $request->tel_empresa as $key => $tel_empresa){ // Recorre el numero de telefonos y los inserta la db
                $vcard_info=new Vcard_info ;
                if($request->tel_empresa[$key]!==NULL){
                    $vcard_info->idvcard=$vcard->id;
                    $vcard_info->contacto=$request->tel_empresa[$key].'#'.$request->ext[$key];
                    $vcard_info->tipocontacto=2; // 2 son teléfonos de la empresa
                    $vcard_info->save();
                }
            }
        }else{ // Modificación

            $vcard=Vcardg::where('id',$request->idvcard)->first();

            if($request->hasFile('logo_empresa_empleado')){ // Si hay una imagen diferente a la seleccionada la remplaza y elimina la que estaba almacenada en el storage
                $path="storage/repositorio/".Session::get('empresa')['id']."/vcard".'/';  //url ejemplo /public/repositorio/46/vcard/
                $file=$request->logo_empresa_empleado;
                $extension = $file->getClientOriginalName();
                $nombre = time().$extension;
                $file->move($path,$nombre);
                $foto=$nombre;
                $logodelete = $vcard->logo_empresa_empleado;
                if($logodelete != "logoletra.png"){
                    $url=public_path()."/repositorio/".Session::get('empresa')['id']."/vcard".'/'.$logodelete;
                    unlink($url);
                } 	
            }else{
                $foto = $vcard->logo_empresa_empleado;
            }
            
            $vcard->direccion=$request->direccion;
            $vcard->colorfndinp=$request->colorfndinp; //color de fondo principal
            $vcard->colorfndbtninp=$request->colorfndbtninp; // color de fondo de botonera
            $vcard->coloricnbtninp=$request->coloricnbtninp; // color iconos de botonera
            $vcard->fndbodyvcardinp=$request->fndbodyvcardinp; // color de fondo del body de la vcard
            $vcard->recfondlistintinp=$request->recfondlistintinp; // color de fondo lista interior
            $vcard->recletlistinitinp=$request->recletlistinitinp; // color recuadro letra lista interior
            $vcard->reclistinticonsinp=$request->reclistinticonsinp; //color recuadro iconos lista interior
            $vcard->logo_empresa_empleado=$foto;
            $vcard->save();
            
            foreach($request->link_web as $key1 => $link_web){
               
                if($request->link_web[$key1]!==NULL){
                    $vcard_info=Vcard_info::where('id',$request->idlinkweb[$key1])->first();
                    $vcard_info->contacto=$request->link_web[$key1];
                    $vcard_info->save();
                }
            }

            foreach($request->tel_empresa as $key => $tel_empresa){
                
                if($request->tel_empresa[$key]!==NULL){
                    $vcard_info=Vcard_info::where('id',$request->idem[$key])->first();
                    $vcard_info->contacto=$request->tel_empresa[$key].'#'.$request->ext[$key];
                    $vcard_info->save();
                }else{
                    $vcard_info=Vcard_info::where('id',$request->idem[$key])->delete();
                }    
            }
        }
        return response()->json();
    }
    public function getExistCard(){  //Metódo que se ejecuta cuando una vcard ya esta insertada en la db
        
        cambiarBase(Session::get('base'));
        $querys=DB::connection('empresa')
                    ->table('vcards')
                    ->join('vcards_info','vcards_info.idvcard','=','vcards.id')
                    ->where('vcards.idempresa',Session::get('empresa')['id']) 
                    ->get();
        if(sizeof($querys) > 0){
            $links=[];
            $telefonos=[];
            $idtel=[];
            foreach($querys as $query){

                $id=$query->id;
                $idempresa=$query->idempresa;
                $direccion=$query->direccion;
                $colorfndinp=$query->colorfndinp;
                $colorfndbtninp=$query->colorfndbtninp;
                $coloricnbtninp=$query->coloricnbtninp;
                $fndbodyvcardinp=$query->fndbodyvcardinp;
                $recfondlistintinp=$query->recfondlistintinp;
                $recletlistinitinp=$query->recletlistinitinp;
                $reclistinticonsinp=$query->reclistinticonsinp;
                $logo_empresa_empleado=$query->logo_empresa_empleado;
                $idvcard=$query->idvcard;
                if($query->tipocontacto==1){
                    $links[]=$query->id;
                    $links[]=$query->contacto;
                }else{
                    
                    $telefonos[]=$query->id.'#'.$query->contacto; 
                } 
            }
            $data=array('idempresa'=>$idempresa,
                        'direccion'=>$direccion,
                        'colorfndinp'=>$colorfndinp,
                        'colorfndbtninp'=>$colorfndbtninp,
                        'coloricnbtninp'=>$coloricnbtninp,
                        'fndbodyvcardinp'=>$fndbodyvcardinp,
                        'recfondlistintinp'=>$recfondlistintinp,
                        'recletlistinitinp'=>$recletlistinitinp,
                        'reclistinticonsinp'=>$reclistinticonsinp,
                        'logo_empresa_empleado'=>$logo_empresa_empleado,
                        'idvcard'=>$idvcard,
                        'links'=>$links,
                        'telefonos'=>$telefonos,
                        'idtel'=>$idtel);
        }else{
            $data="";
        }
        return response()->json($data);
    }
    public function downloadVcard(Request $request){ // Metódo que se ejecuta para descargar una vcard
      
        if($request->codigo!==""){
            
            $empleado = EmpleadoLogin::where('codigo',  $request->codigo)->first();
            $empresa=Empresa::where('id',$request->empresa)->first();
            
            cambiarBase($empleado->empresa);
            $data=$this->datavcard($empleado);
            $empleado_empresas=$data['empleado_empresa'];
            $vcard_empresas=$data['vcard_empresas'];

            $vcard = new VCard();
            $firstname = $empleado_empresas->nombre;
            $lastname =  $empleado_empresas->apaterno;
            $additional = '';
            $prefix = '';
            $suffix = '';
            // agregar datos personales 
            $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);
            // agregar datos de trabajo
            $vcard->addCompany($empresa->razon_social);
            $vcard->addJobtitle($empleado_empresas->departamento);
            $vcard->addRole($empleado_empresas->puesto);
            $vcard->addEmail($empleado_empresas->correo);
            $vcard->addPhoneNumber($empleado_empresas->telefono_movil,'PREF;WORK');
            /*$vcard->addAddress(null, null, 'street', 'worktown', null, 'workpostcode', 'Belgium');*/
            $contador_tel=0;
            $contador_link=0;
            if(empty($vcard_empresas['email'])){
                foreach($vcard_empresas as $vcard_empresa){
                    if($vcard_empresa->tipocontacto==2){
                        if($contador_tel==0){
                            $vcard->addPhoneNumber($vcard_empresa->contacto, 'WORK');
                        }                
                        $contador_tel++;
                    }elseif($vcard_empresa->tipocontacto==1){
                        if($contador_link==0){
                            $vcard->addURL($vcard_empresa->contacto);  
                        }       
                        $contador_link++;
                    }
                }
            }else{
                $vcard->addPhoneNumber($vcard_empresas['telefono'], 'WORK');
                //$vcard->addURL('');  
            }
            return $vcard->download();
        }
    }

    public function generarQR_empresa(){
         $repositorio = 'public/repositorio/';
         $url ="https://hrsystem.com.mx/vcard/";
        cambiarBase(Session::get('base'));
        
        $empleados = Empleado::all();
        
        foreach($empleados as $empleado){

            $repo = $repositorio . Session::get('empresa')['id'] . '/'.$empleado->id.'/' ;
            $usuario = EmpleadoLogin::where('email', $empleado->correo)->first();
            //dump($usuario);

            
            if(isset($usuario->codigo)){
            $archivo_qr =  $usuario->codigo . '.svg';
            $archivo = $repo . $archivo_qr;
            $txt = $url . $usuario->codigo;
            
            QrCode::generate($txt, $archivo);
            
            dump($archivo,$txt);
            }else{

                dump($empleado->correo,$usuario);
            }
        }
        
    }
}