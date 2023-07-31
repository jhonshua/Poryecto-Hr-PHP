<?php

namespace App\Http\Controllers\Empleados;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Covid\ContactosCovid;
use App\Models\Covid\RegistroCovid;
use App\Models\Covid\EvidenciaCovid;
use App\Models\Empleado;
use App\Models\ComprobanteVacunacion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DateTime;


class CovidController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    protected const SIN_COVID = 0;
    protected const COVID_ACTIVO = 1;
    protected const COVID_FINALIZADO = 2;

    public function covidinicio(Request $request, $id)
    {
        tienePermiso('empleados');
        cambiarBase(Session::get('base'));

        $empleado = Empleado::where('id',$id)->with('registro_covid','es_contacto')->get();
		 
        $empleados = Empleado::all()->where('estatus',1)->where('id','!=',$id);

        $comprobante_vacunacion =ComprobanteVacunacion::where('id_empleado',$id)->first();
    
        return view('empleados.seguimiento-covid',compact('empleado','empleados','comprobante_vacunacion'));

    }


    public function agregarRegisto(Request $request)
    {
        $registro = $this->guardarRegistro($request);
        if($registro['ok']){

            session()->flash('success', 'El registro Covid se realizó con éxito.');

            return redirect()->route('covid.inicio', $registro['vistaid']);

        }

            session()->flash('danger', 'El registro Covid no pudo realizarse, intente nuevamente.');

            return redirect()->route('covid.inicio', $request->id_empleado);
    }

    public function guardarRegistro(Request $request)
    {

        cambiarBase(Session::get('base'));
        $vistaid = $request->id_empleado;
        $registro = new  RegistroCovid;
        $registro->id_empleado = $request->id_empleado;
        $registro->fecha_inicio = $request->fecha_inicio;
        $registro->estatus = self::COVID_ACTIVO;
        $registro->notas = $request->notas;

        if($request->termino == 1){
            $registro->estatus = self::COVID_FINALIZADO;
            $registro->fecha_fin = $request->fecha_fin;
        }

        if($registro->save()){
            if(!empty($request->contactos) && count($request->contactos) > 0){
                $c = array();
                foreach($request->contactos as $contacto){
                    $c[] = array('registro_covid_id' => $registro->id, 'id_empleado' => $contacto, 'fecha' => date('Y-m-d'),'estatus' => self::SIN_COVID);
                }
                ContactosCovid::insert($c);
            }
            if($request->escontacto > 0){
                ContactosCovid::where('id', $request->escontacto)
                ->update(['estatus'=> $registro->estatus,'id_registro' => $registro->id]);
                $vistaid = $request->escontactode;
            }
            
            if(!empty($request->evidencia_inicio)){
                $this->cargarEvidencia($registro->id,$request->prueba,$request->evidencia_inicio,self::COVID_ACTIVO);
            }
            if(!empty($request->evidencia_fin)){
                $this->cargarEvidencia($registro->id,$request->prueba,$request->evidencia_fin,self::COVID_FINALIZADO);
            }

            return array('ok' => 1,'id_registro' => $registro->id,'estatus' => $registro->estatus,'vistaid' => $vistaid);
           
        }
        return array('ok' => 0);
    }

    function cargarEvidencia($idregistro = null,$prueba,$evidencia,$tipoevidencia)
    {
        //$audiencia = ($idaudiencia != null)? $idaudiencia : $request->idAudiencia;
        $fcreado = new DateTime();
        $folder_repositorio = 'repositorio/' . Session::get('empresa')['id'] . '/covid/';
        $extension = pathinfo($evidencia->getClientOriginalName(), PATHINFO_EXTENSION);
        $nombre = "registroCovid_".$idregistro."_".$tipoevidencia."_".$fcreado->format('YmdHis').".".$extension;
        	

			$path = Storage::disk('public')->put('repositorio/' . Session::get('empresa')['id'] . '/covid/', $evidencia);
            $archivo_cer = $rest = substr($path, 22);
            $archivo_move= 'repositorio/'.Session::get('empresa')['id'].'/covid/'.$nombre;
            $subido_cer = Storage::disk('public')->move('repositorio/'.Session::get('empresa')['id'].'/covid/'.$archivo_cer, $archivo_move);

        EvidenciaCovid::updateOrInsert([
            'id_registro_covid' => $idregistro,
            'tipo' => $tipoevidencia,
            'prueba' => $prueba
        ],[
            'id_registro_covid' => $idregistro,
            'nombre' => $nombre,
            'tipo' => $tipoevidencia,
            'prueba' => $prueba
        ]);
        return true;
    }


    public function editarRegistro(Request $request)
    {
       
        $registro = $this->actualizarRegistro($request);
        if($registro['ok']){

            session()->flash('success', 'El registro Covid se actualizó con éxito');

            return redirect()->route('covid.inicio', $request->id_empleado);

        }

            session()->flash('danger', 'El registro Covid no pudo actualizarse, intente nuevamente');

            return redirect()->route('covid.inicio', $request->id_empleado);

    }


    function actualizarRegistro(Request $request)
    {
        cambiarBase(Session::get('base'));
        $registro = RegistroCovid::where('id', $request->id_registro)->with('contactos')->first();
        
        $registro->fecha_inicio = $request->fecha_inicio;
        $registro->notas = $request->notas;

        if(!empty($request->contactos) && count($request->contactos) > 0){
            $contactos_guardados = $registro->contactos->keyBy('id_empleado')->toArray();
            
            $contacto_agregar = array();

            foreach($request->contactos as $contacto){
                if(empty($contactos_guardados[$contacto])){
                    $contacto_agregar[] = array('registro_covid_id' => $registro->id, 'id_empleado' => $contacto, 'fecha' => date('Y-m-d'),'estatus' => self::SIN_COVID);
                }else{
                    unset($contactos_guardados[$contacto]);

                }
            }
           
            foreach($contactos_guardados as $eliminar){
                ContactosCovid::where('id',$eliminar['id'])->delete();
            }

            ContactosCovid::insert($contacto_agregar);
        }else{
            if(empty($registro->contactos)){
                $registro->contactos->delete();
            }
            
        }


        if($request->termino == 1){
            $registro->estatus = self::COVID_FINALIZADO;
            $registro->fecha_fin = $request->fecha_fin;
            if(!empty($request->evidencia_fin)){
                $this->cargarEvidencia($registro->id,$request->prueba,$request->evidencia_fin,self::COVID_FINALIZADO);
            }
        }else{
            $registro->estatus = self::COVID_ACTIVO;
            $registro->fecha_fin = null;
        }

        if(!empty($request->evidencia_inicio)){
            $this->cargarEvidencia($registro->id,$request->prueba,$request->evidencia_inicio,self::COVID_ACTIVO);
        }

        if($registro->update()){

            return array('ok' => true);
        }
                
        
    }

    public function eliminarRegistro(Request $request)
    {

        cambiarBase(Session::get('base'));
        $registro = RegistroCovid::where('id',$request->id)->with('contactos','evidencias')->first();
        //dd($registro);
        foreach($registro->contactos as $contacto){
            $contacto->delete();
        }
        foreach($registro->evidencias as $evidencia){
            $evidencia->delete();
        }
        $id_empleado = $registro->id_empleado;

        ContactosCovid::where('id_registro',$request->id)->update(['id_registro' => null,'estatus' => 0]);
        $registro->delete();

        session()->flash('danger', 'El registro Covid se eliminó con éxito');

        return redirect()->route('covid.inicio', $id_empleado);

    }

}