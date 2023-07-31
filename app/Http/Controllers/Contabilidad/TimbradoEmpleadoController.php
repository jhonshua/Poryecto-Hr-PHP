<?php

namespace App\Http\Controllers\contabilidad;


use App\Http\Controllers\Controller;
use App\Models\ConceptosNomina;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\TimbradoEmpleadoFiniquito;
use App\Models\TimbradoCancelacionesEmpleado;
use App\Models\TimbradoCancelacionesAsimilados;
use App\Models\TimbradoAsimilados;
use App\Http\Controllers\Contabilidad\TimbradoController;

use App\Models\EmpresaEmisora;
use App\Models\Empleado;
use App\Models\TimbradoEmpleado;
use App\Models\Nomina\periodosNomina;

class TimbradoEmpleadoController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }

	public function crea_pdf_periodo($periodo){
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];
        // dd($repo);
        $url_repo ="repositorio/".$repo;
        cambiarBase($base); 

        $timbres = DB::connection('empresa')
                    ->table('timbrado')
                    ->where('id_periodo', $periodo)
                    ->get();

        foreach($timbres as $timbre){
            $url_xml = $url_repo . "/" . $timbre->id_empleado .'/timbrado/archs_cfdi/'. $timbre->file_xml;
            // $url_xml = storage_path()."/app/public/repositorio/".$repo."/".$timbre->id_empleado."/timbrado/archs_cfdi/" . $timbre->file_xml . "";  
            $exists = Storage::disk('public')->exists($url_xml);
            
            if ($exists)
            {
                $pdf = str_replace('.xml', '.pdf', $timbre->file_xml);
                $url = $url_repo . "/" . $timbre->id_empleado .'/timbrado/archs_pdf/'. $pdf ;
                $isr = 2; //de q no hay isr, cmabiar en algún momento
                // return response()->download($url);

                Storage::disk('public')->download($url_xml);
                return  $url1 = "exist";
                //verificamos si el archivo existe y lo retornamos
		        if (!$exists){

            	  $url2 =  \file_get_contents (asset('/hrsystem/pdf_reciboNomina.php?NomArchXML='.$timbre->file_xml.'&NomArchPDF='.$pdf.'&id='.$timbre->id_empleado.'&base='.$base.'&base_id='.$repo.'&isr='.$isr));
					//echo "creado<br>$pdf<br>";
		        }else{
					//echo "existe<br>$url<br>";
				}            
            }
        }
		//echo "<br>fin";

	}

    public function timbrarEscogePeriodoAsimilados(){
        $base = Session::get('base');
        cambiarBaseA($base);     
        
        //$periodos = periodosNomina::all()->sortByDesc("id");
        $periodos = periodosNomina::orderBy("id",'desc')->where('estatus',1)->where('activo',2)->paginate(15);

        return view('contabilidad.timbrado.lista_periodos_asimilados',compact('periodos'));
    }

    public function timbrarAsimiladosPeriodo($periodo){
        $base = Session::get('base');
        cambiarBaseA($base);     

        $repo = Session::get('empresa')['id'];
        
        /* periodo */
        $periodo = periodosNomina::find($periodo);
        $id_periodo  = $periodo->id;

        $tipo_nomina = $periodo->nombre_periodo;
        $nombre_periodo = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;
        $empleados = array();
        $query = "SELECT *
                  FROM empleados
                  WHERE estatus = 1
                  AND tipo_de_nomina = '$tipo_nomina'
                  AND id IN (
                      SELECT id_empleado 
                      FROM rutinas$ejercicio 
                      WHERE id_periodo = '$id_periodo' 
                      AND fnq_valor = 0 
                      AND neto_sindical > 0);
                  ";
        $emple = DB::connection('generica')->select($query);   

        /* Verificamos si ya existen timbrados para el periodo */
        $r = DB::connection('generica')
                     ->table('timbrado_asimilados')
                     ->where('id_periodo',$id_periodo)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();
            $existen_timbrados = $r; 
        
            
        /* PROCESAMOS LOS EMPLEADOS */                  
        foreach($emple as $e){
            $query = "SELECT neto_sindical
                      FROM rutinas$ejercicio
                      WHERE id_empleado = $e->id
                      AND id_periodo = $id_periodo
                      AND fnq_valor = 0;";
            /* Sacamos sus importe */
            $r = DB::connection('generica')->select($query);   
            $e->importe_fiscal = round($r[0]->neto_sindical,2);
            //$importeFiscal=round($rowresultFiscal['NetoFiscal'],2);
            
            /* 
                01.- VERIFICAMOS SI YA TIENE UN TIMBRADO Y NO ES ERROR
                $numRegistros -> original
                Sacamos tambien el estatus del timbre:
                0 = ?
                1 = Timbrado
                2 = Error
            */
            $query2="SELECT * 
                     FROM timbrado_asimilados
                     WHERE id_empleado = '$e->id' 
                     AND id_periodo = '$id_periodo' 
                     AND estatus_timbre = 1";
            $r = DB::connection('generica')->select($query2);   
            $e->timbres = $r;

            /*
              02.- Si hay timbres no error //Normalmente es 1
              $numRegistrosreTimbre -> original
            */
            $r = DB::connection('generica')
                     ->table('timbrado_asimilados')
                     ->where('id_empleado',$e->id)
                     ->where('id_periodo',$id_periodo)
                     ->where('sello_sat', '<>', 'error')
                     ->get()
                     ->count();
            $e->numero_timbres_noerror = $r;            
            /* 
               03.- traemos el ultimo registro de  timbre 
               $numRegistrosreTimbreError -> original
            */
            $r = DB::connection('generica')
            ->table('timbrado_asimilados')
            ->where('id_empleado',$e->id)
            ->where('id_periodo',$id_periodo)
            ->orderBy('id','desc')
            ->first();
            $e->ultimo_timbre = $r; 

            /* 
              04.-timbres cancelados 
            */
            $r = DB::connection('generica')
                    ->table('timbrado_cancelaciones_asimilados')
                    ->where('id_empleado', $e->id)
                    ->where('id_periodo', $id_periodo)
                    ->get();
             $e->timbres_cancelados = $r;
            

            $empleados[] = $e;
        }

        //dd($request->all(),$periodo,$id_periodo,$todos,$cadena_departamentos,$empleados);
       // dd($periodo,$cadena_departamentos,$empleados,$existen_timbrados);

        //echo "SELECT * FROM $base.empleado WHERE Status=0 and tipodeNomina='$TipoNomina' and departamento in ($cadena_departamentos)";
        $cadena_departamentos = 0;
        $tipo = 1;
        return view('contabilidad.timbrado.asimilados_lista',compact('periodo','empleados','cadena_departamentos','existen_timbrados','repo','tipo'));

    }

    public function cancelar_cfdi_asimilados($id){
         /* Codigo html a mostrar al final del proceso(TEMPORAL)*/
         $data_string ="";
         $data_respuesta = array();

        $base = Session::get('base');
        cambiarBaseA($base);

        $timbre = TimbradoAsimilados::find($id);
        //dd($timbre);  
        $no_fac = $timbre->num_factura;
        //$data_respuesta['id_empleado']=$timbre->id_empleado;
        
        
        cambiarBaseA('singh');

        $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->get()[0];
        $usr_timbre = $emisora->user_timbre;

        $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
        $data_respuesta['no_factura']   = $timbre->num_factura;
        $data_respuesta['repo']   = Session::get('empresa')['id'].'/'.$timbre->id_empleado;

        $data_respuesta['repositorio']=Session::get('empresa')['id'];
        $data_respuesta['id_empleado']=$timbre->id_empleado;

        /* REPOS Y FODLERS */
        $repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/';
        $repositorio2 = 'public/repositorio/' . $data_respuesta['repo'] . '/timbrado/';
        $recursos = resource_path().'/timbrado/';
        
        $folder_repositorio = public_path() . '/' . $repositorio;

        $SendaCFDI     = $folder_repositorio.'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaEmpGRAFS = $folder_repositorio.'archs_grafs/';   // 2.2 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR       = $folder_repositorio.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR_url   = $repositorio2.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPreCFDI  = $folder_repositorio.'archs_precdfi/';// 2.4 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPDF      = $folder_repositorio.'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaRESP     = $folder_repositorio.'archs_resp/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
     
        $SendaPEMS    = $recursos . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $recursos . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $recursos . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);
    

        $contenido_del_nodo_acuse = "";
        $ValorUUID = "";

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr ( $usr_timbre , 7 ,strlen($usr_timbre) );
        $condicion = ($condicion == null)?1:$condicion;
        //$condicion =1;
        
        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
        $credenciales = DB::select($query);
        
        
        $FolioFiscal = $timbre->folio_fiscal; // 2.5 Folio fiscal de CFDI a cancelar.
        $taxpayer_id = $credenciales[0]->rfc;
        /*
        $rfc_emi    = $credenciales[0]->rfc;
        $rfc_emisor = $rfc_emi;
        */
        
        $username    = base64_decode($credenciales[0]->user);
        $password    = base64_decode($credenciales[0]->password);
        $ip_servicio = $credenciales[0]->servicio_cancelacion;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales[0]->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales[0]->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo.".key.pem";   // 3.3 Nombre del archivo .cer.key
        $file_enc       = $credenciales[0]->nombre_archivo.".key.enc.pem";   // 3.3 Nombre del archivo .cer.key
        
        #== Obtener el certificado del archivo .cer.pem ============================
        $cer_path = $SendaPEMS . $file_cer;
        $cer_file = fopen($cer_path, "r");
        $cer_content = fread($cer_file, filesize($cer_path));


        #== Conviritien el contenido del certificado a BASE 64 y asignarlo a una variable ======
        $cer_content = base64_encode($cer_content);
        fclose($cer_file);

        #== Encriptar con DES3 =====================================================    
        $ArchivoKeyPem       = $SendaPEMS . $file_key;  //Archivo .key.pem SIN encriptar.
        $ArchivoKeyEncripPem = $SendaPEMS . $file_enc; //Archivo .key.enc.pem ENCRIPTADO.

        #== Obtener contenido de archivo .key.pem ==================================
        $key_path = $SendaPEMS.$file_enc;
        $key_file = fopen($key_path, "r");
        $key_content = fread($key_file, filesize($key_path));
        $key_content = base64_encode($key_content);
        fclose($key_file);

        
$cadena = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:can="http://facturacion.finkok.com/cancel" xmlns:apps="apps.services.soap.core.views" xmlns:can1="http://facturacion.finkok.com/cancellation">
   <soapenv:Header/>
   <soapenv:Body>
      <can:cancel>
        <can:UUIDS>            
            <apps:UUID UUID="$FolioFiscal" FolioSustitucion="" Motivo="02"/>
        </can:UUIDS>
         <can:username>$username</can:username>
         <can:password>$password</can:password>
         <can:taxpayer_id>$taxpayer_id</can:taxpayer_id>
         <can:cer>$cer_content</can:cer>
         <can:key>$key_content</can:key>
         <can:store_pending>0</can:store_pending>
      </can:cancel>
   </soapenv:Body>
</soapenv:Envelope>
XML;
        #== 12.6 Proceso para guardar los datos que se envían al servidor en un archivo .XML ========================
        $NomArchSoap = $SendaPreCFDI."soap_cancel_Factura_".$no_fac.".xml";

        #== 12.6.1 Si el archivo ya se encuentra se elimina ===========================
        if (file_exists ($NomArchSoap)==true){
             unlink($NomArchSoap);
        }

        #== 12.6.2 Se crea el archivo .XML con el SOAP ================================
        $fp = fopen($NomArchSoap,"a");
        fwrite($fp, $cadena);
        fclose($fp);
        chmod($NomArchSoap, 0777);
        
        
        #=== 12.7 Muestra el contenido del SOAP que se envía al servidor del PAC (REQUEST) =========================
        $data_respuesta['soap'] = htmlspecialchars($cadena);
        $data_respuesta['contenido'] = htmlspecialchars($cadena);
        //$ip_servicio = 'https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl';

        $process  = curl_init($ip_servicio);
        
        #== 12.8 Se envía el contenido del SOAP al servidor del PAC =====================
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml',' charset=utf-8'));
        curl_setopt($process, CURLOPT_POSTFIELDS, $cadena);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
        $RespServ = curl_exec($process);
        
        #== 12.9 Se muestra la respuesta del servidor del PAC (opcional a mostrar) ================
        $respuestaPac = htmlspecialchars($RespServ);
        $data_respuesta['respuesta'] = $respuestaPac;
        
        $err = 0;
        if (!$RespServ)
        {
            $data_respuesta['error'] = TRUE;
            $data_respuesta['codigo_error'] .= "Error: ".$RespServ;
            $data_respuesta['error_msg'] = curl_error($process);
        }
        else
        {
            #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
            $domResp = new \DOMDocument();
            $domResp->loadXML($RespServ);

       #== 12.6.1 Si el archivo ya se encuentra se elimina ===========================
      
       if (file_exists ($NomArchSoap)==true){
            unlink($NomArchSoap);
       }

            // Se guarda la respuesta del servidor
            $domResp->save($SendaRESP."RespServ_Cancel_".$no_fac.".xml");
            chmod($SendaRESP."RespServ_Cancel_".$no_fac.".xml", 0777);

            ## TAG UUID ####################################################
            $tagUUID = $domResp->getElementsByTagName('EstatusUUID');

            foreach($tagUUID as $ObjTag ){
                $ValorUUID = $ObjTag->nodeValue; // Código de error o acierto.
            }

            ## TAG STATUS ####################################################
            $tagCodigo = $domResp->getElementsByTagName('CodEstatus');
            $valorCodigo='';

            foreach($tagCodigo as $ObjTag ){
                $valorCodigo = $ObjTag->nodeValue; // Código de error o acierto.
            }

            ## .XML ACUSE DE CANCELACIÓN ###################################
            $tagAcuse = $domResp->getElementsByTagName('Acuse');

            foreach($tagAcuse as $ObjTag ){
                $contenido_del_nodo_acuse = $ObjTag->nodeValue;
            }

            $data_respuesta['contenido']  = $contenido_del_nodo_acuse;
            $data_respuesta['soap']       = $cadena;
            $data_respuesta['valor_UUID'] = $ValorUUID;
            
            $CodResp = $ValorUUID;

            if($CodResp !=""){
                $data_respuesta['contenido'] = htmlspecialchars($cadena);
                $data_respuesta['error'] = true;                
                $data_respuesta['codigo_error'] = "205: UUID No existente";
                $data_respuesta['error_msg'] = $valorCodigo;

            }
            

            if($CodResp == 201 || $CodResp == "201" )
            {
                # 201: UUID Cancelado Exitoso
                #=== Guardando el SOAP =====================================================
                $NomArchSoap = $SendaPreCFDI."soap_cancel_2_Factura_".$no_fac.".xml";
                $NomArchSoap2 = "soap_cancel_2_Factura_".$no_fac.".xml";
                $data_respuesta['file_xml'] = $NomArchSoap2;
                
                if (file_exists ($NomArchSoap)==true){
                    unlink($NomArchSoap);
                }
                $fp = fopen($NomArchSoap,"a");
                fwrite($fp, $cadena);
                fclose($fp);
                chmod($NomArchSoap, 0777);
                
                ###### GUARDANDO EL .XML DEL ACUSE #############################
                $fileAcuse ="Fact_CFDI_".$no_fac."_AcuseCancel.xml";
                $xmlt = new \DOMDocument();
                $xmlt->loadXML($contenido_del_nodo_acuse);
                $xmlt->save( $SendaCFDI.$fileAcuse);
                chmod($SendaCFDI."Fact_CFDI_".$no_fac."_AcuseCancel.xml", 0777);
                unset($xmlt);

                ### SE ASIGNA EL CONTENIDO DEL ACUSE A UNA VARIABLE DE TIPO DOM PARA LA LECTURA DE DATOS ####################
                $DOM = new \DOMDocument('1.0', 'utf-8');
                $DOM->preserveWhiteSpace = FALSE;
                $DOM->loadXML($contenido_del_nodo_acuse);

            
                ### LECTURA DE ATRIBUTOS ###################################

                #== Fecha de cancelación ===================================
                $params = $DOM->getElementsByTagName('CancelaCFDResult');
                foreach ($params as $param) {
                    $FecCanc = $param->getAttribute('Fecha');
                    $data_respuesta['fecha_resp']  = $FecCanc;
                    $data_string .= 'Fecha de cancelación:'.$FecCanc;
                }
                $respString='Petición de cancelación realizada exitosamente';

                #== Sello digital del SAT (cancelación) ===========================
                $nodoSignatureValue = $DOM->getElementsByTagName('SignatureValue');

                foreach($nodoSignatureValue as $param){
                    $SelloCanc = $param->nodeValue;
                    $data_respuesta['sello_cancelacion']  = $SelloCanc;
                    $data_string .= 'Sello digital SAT:'.$SelloCanc;
                }
            
                $request  = addslashes($cadena);
                $response = addslashes($RespServ);
                $xmlacuse = addslashes($contenido_del_nodo_acuse);

                $base = Session::get('base');
                cambiarBaseA($base);
                
                // GUARDAR EN DB
                $facturaD = new TimbradoCancelacionesAsimilados;
         
                $facturaD->id_empleado       = $timbre->id_empleado;
                $facturaD->id_periodo        = $timbre->id_periodo;
                $facturaD->fecha_cancelacion = $FecCanc;
                $facturaD->request_cancel    = $request;
                $facturaD->response          = $response;
                $facturaD->xml_acuse_cancel  = $xmlacuse;
                $facturaD->sello_sat         = $SelloCanc;
                $facturaD->file_acuse        = $fileAcuse;
                $facturaD->file_soap         = $NomArchSoap2;
                $facturaD->no_factura        = $no_fac;   
                $facturaD->save();
                
                
                $timbre->estatus_timbre = 2;
                $timbre->update();

                $data_respuesta['error'] = false;
                $data_respuesta['mnsg'] = addslashes($respString);

                /* CHECAR BITACORAS
                 $desc='Se solicito la cancelacion del timbre de nomina del empleado '.$NameEmple.'.';
                        $tipo='CT';

                        add_bitacora(28,$desc,$tipo,'',$db);
                        correo_eventos($db,28,$referencia);
                */
            }

            //dump($data_respuesta);
            
             if($CodResp == 704 || $CodResp == "704"){
                $data_string .= "Error con la contraseña de la llave privada";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 708 || $CodResp == "708"){
                $data_string .= "Error de conexion del SAT ....";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 202 || $CodResp == "202"){
                $data_string .= " 202: UUID Cancelado Previamente";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 203 || $CodResp == "203"){
                $data_string .= "203: UUID No corresponde el RFC del emisor y de quien solicita la cancelación";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            if($CodResp == 205 || $CodResp == "205"){
                $data_string .= "205: UUID No existente";
                $data_respuesta['error'] = true;
                $data_respuesta['codigo_error'] = $data_string;
            }

            
            
            //dd($data_respuesta);
            
        }

        
        curl_close($process);

        ## FIN DEL PROCESO DE TIMBRADO #################################################
        return view('contabilidad.timbrado.timbre_cancelado_asimilados',compact('data_respuesta'));

    }

    public function crea_pdf_periodo_asimilidos($periodo){
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];
        $url_repo = public_path()."/repositorio/".$repo."/";
        cambiarBaseA($base); 

        $timbres = DB::connection('generica')
                    ->table('timbrado_asimilados')
                    ->where('id_periodo', $periodo)
                    ->get();

        foreach($timbres as $timbre){
            $url_xml = $url_repo . "/" . $timbre->id_empleado .'/timbrado/archs_cfdi/'. $timbre->file_xml;
            if (File::exists($url_xml))
            {
                $pdf = str_replace('.xml', '.pdf', $timbre->file_xml);
                $url = $url_repo . "/" . $timbre->id_empleado .'/timbrado/archs_pdf/'. $pdf ;
                $isr = 2; //de q no hay isr, cmabiar en algún momento
                //verificamos si el archivo existe y lo retornamos
                if (!File::exists($url)){
                  $url2 =  \file_get_contents (asset('/hrsystem/pdf_reciboasimiladosMasi_Periodo.php?NomArchXML='.$timbre->file_xml.'&NomArchPDF='.$pdf.'&id='.$timbre->id_empleado.'&base='.$base.'&base_id='.$repo.'&isr='.$isr));
                    //echo "creado<br>$pdf<br>";
                }else{
                    //echo "existe<br>$url<br>";
                }            
            }
        }
        //echo "<br>fin";
    }

    public function pdf_masivo_asimilados($periodo){

        $this->crea_pdf_periodo_asimilidos($periodo);
                

        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];
        $url_pdf = public_path()."/repositorio/".$repo."/timbrado/archs_pdf/PDF_Masivo_Asimilados_Pe_" . $periodo . ".pdf";

        

        //verificamos si el archivo existe y lo retornamos
        if (File::exists($url_pdf))
        {
        return response()->download($url_pdf);
        }

        $url =  asset('/hrsystem/pdf_reciboasimiladosMasi_Periodo.php?base='.$base.'&repo='.$repo.'&periodo='.$periodo);
            return redirect()->to($url);
    }

    public function verificar_status_asimilados($id)
    {
        $data_string ="";
         $data_respuesta = array();

         
        $base = Session::get('base');
        cambiarBaseA($base);

        $timbre = TimbradoAsimilados::find($id);
        //dd($timbre);  
        $no_fac = $timbre->num_factura;
        
        
        cambiarBaseA('singh');

        $emisora = EmpresaEmisora::where('rfc', $timbre->emisor)->get()[0];
        $usr_timbre = $emisora->user_timbre;


        $data_respuesta['folio_fiscal'] = $timbre->folio_fiscal;
        $data_respuesta['no_factura']   = $timbre->num_factura;
        $data_respuesta['repo']   = Session::get('empresa')['id'].'/'.$timbre->id_empleado;

        $importe=$timbre->importe;
        $rfcemi=$timbre->emisor;
        $receptor=$timbre->receptor;
        $FolioFiscal=$timbre->folio_fiscal;


        /* REPOS Y FODLERS */
        $repositorio = 'repositorio/' .$data_respuesta['repo'] . '/timbrado/';
        $repositorio2 = 'public/repositorio/' . $data_respuesta['repo'] . '/timbrado/';
        $recursos = resource_path().'/timbrado/';
        
        $folder_repositorio = public_path() . '/' . $repositorio;

        $SendaCFDI     = $folder_repositorio.'archs_cfdi/';    // 2.1 Directorio en donde se almacenarán los archivos *.xml (CFDIs).
        $SendaEmpGRAFS = $folder_repositorio.'archs_grafs/';   // 2.2 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR       = $folder_repositorio.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaQR_url   = $repositorio2.'archs_qr/';     // 2.3 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPreCFDI  = $folder_repositorio.'archs_precdfi/';// 2.4 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaPDF      = $folder_repositorio.'archs_pdf/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaRESP     = $folder_repositorio.'archs_resp/';   // 2.5 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
     
        $SendaPEMS    = $recursos . "archs_pem/";    // 2.6 Directorio en donde se encuentran los archivos *.cer.pem y *.key.pem (para efectos de demostración se utilizan los que proporciona el SAT para pruebas).
        $SendaGRAFS   = $recursos . 'archs_graf/';  // 2.7 Directorio en donde se almacenan los archivos .jpg (logo de la empresa) y .png (códigos bidimensionales,QR).
        $SendaXSD     = $recursos . "archs_xsd/";   // 2.8 Directorio en donde se almacenan los archivos .xsd (esquemas de validación, especificaciones de campos del Anexo 20 del SAT);
    

        $contenido_del_nodo_acuse = "";
        $ValorUUID = "";

        // 2.5 Datos de acceso del usuario (proporcionados por www.finkok.com) modo de integración (para pruebas) o producción.
        $condicion = substr ( $usr_timbre , 7 ,strlen($usr_timbre) );
        $condicion = ($condicion == null)?1:$condicion;
        //$condicion =1;
        
        /* credenciales timbrado */
        $query = "SELECT * from singh.timbrado_credenciales where id='$condicion'";
        $credenciales = DB::select($query);
        
        
        $FolioFiscal = $timbre->folio_fiscal; // 2.5 Folio fiscal de CFDI a cancelar.
        $taxpayer_id = $credenciales[0]->rfc;
        /*
        $rfc_emi    = $credenciales[0]->rfc;
        $rfc_emisor = $rfc_emi;
        */
        
        $username    = base64_decode($credenciales[0]->user);
        $password    = base64_decode($credenciales[0]->password);
        $ip_servicio = $credenciales[0]->servicio_cancelacion;

        ### 3. DEFINICIÓN DE VARIABLES INICIALES ##########################################
        $no_certificado = $credenciales[0]->certificado;                  // 3.1 Número de certificado.
        $file_cer       = $credenciales[0]->nombre_archivo.".cer.pem";   // 3.2 Nombre del archivo .cer.pem
        $file_key       = $credenciales[0]->nombre_archivo.".key.pem";   // 3.3 Nombre del archivo .cer.key
        $file_enc       = $credenciales[0]->nombre_archivo.".key.enc.pem";   // 3.3 Nombre del archivo .cer.key
        
        #== Obtener el certificado del archivo .cer.pem ============================
        $cer_path = $SendaPEMS . $file_cer;
        $cer_file = fopen($cer_path, "r");
        $cer_content = fread($cer_file, filesize($cer_path));


        #== Conviritien el contenido del certificado a BASE 64 y asignarlo a una variable ======
        $cer_content = base64_encode($cer_content);
        fclose($cer_file);

        #== Encriptar con DES3 =====================================================    
        $ArchivoKeyPem       = $SendaPEMS . $file_key;  //Archivo .key.pem SIN encriptar.
        $ArchivoKeyEncripPem = $SendaPEMS . $file_enc; //Archivo .key.enc.pem ENCRIPTADO.

        #== Obtener contenido de archivo .key.pem ==================================
        $key_path = $SendaPEMS.$file_enc;
        $key_file = fopen($key_path, "r");
        $key_content = fread($key_file, filesize($key_path));
        $key_content = base64_encode($key_content);
        fclose($key_file);

        
$cadena = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:can="http://facturacion.finkok.com/cancel">
   <soapenv:Header/>
   <soapenv:Body>
    <can:get_sat_status>
         <can:username>$username</can:username>
         <can:password>$password</can:password>
         <can:taxpayer_id>$rfcemi</can:taxpayer_id>
         <can:rtaxpayer_id>$receptor</can:rtaxpayer_id>
         <can:uuid>$FolioFiscal</can:uuid>
         <can:total>$importe</can:total>
    </can:get_sat_status>
   </soapenv:Body>
</soapenv:Envelope>
XML
;
        

        $process  = curl_init($ip_servicio);
        
        #== 12.8 Se envía el contenido del SOAP al servidor del PAC =====================
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml',' charset=utf-8'));
        curl_setopt($process, CURLOPT_POSTFIELDS, $cadena);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($process, CURLOPT_POST, true);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
        $RespServ = curl_exec($process);
        
        #== 12.9 Se muestra la respuesta del servidor del PAC (opcional a mostrar) ================
        $respuestaPac = htmlspecialchars($RespServ);
        $data_respuesta['respuesta'] = $respuestaPac;

        $mystring = $RespServ;
          $findme   = 'Cancelado';
          $pos = strpos($mystring, $findme);

          // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
          // porque la posición de 'a' está en el 1° (primer) caracter.
          if ($pos === false) {
              $respString='La Factura aun no ha sido Cancelada Verifica mas Tarde';
          } else {
              $respString="La Factura fue Canelada Exitosamente";

          }
        
        $err = 0;
        if (!$RespServ)
        {
            $data_respuesta['error'] = TRUE;
            $data_respuesta['codigo_error'] .= "<h1>Error: ".$RespServ."</h1><br>";
            $data_respuesta['error_msg'] = curl_error($process);
        }
        else
        {
            $data_respuesta['error'] = FALSE;
            $data_respuesta['respuesta_string'] = $respString;
            
            #### DATOS DEVUELTOS POR EL SERVIDOR DE FINKOK #################
            $domResp = new \DOMDocument();
            $domResp->loadXML($RespServ);

            // Se guarda la respuesta del servidor
            $domResp->save($SendaRESP."Status_Server".$no_fac.".xml");
            chmod($SendaRESP."Status_Server".$no_fac.".xml", 0777);

        }
        
        curl_close($process);

        return view('contabilidad.timbrado.check_status',compact('data_respuesta'));
    }

	public function generarpdfMasivo($periodo)
	{
        $base = Session::get('base');
        cambiarBase($base);
        $periodos=TimbradoEmpleado::where('id_periodo',$periodo)->where('estatus_timbre', TimbradoEmpleado::TIMBRE_EXITOSO)->with(['empleadoReceptor'])->get();
        $pdf = Pdf::loadView('procesos.timbrado-nomina.pdf-periodo-nomina',['data'=>$periodos, 'id_empresa' => Session::get('empresa')['id']]);
        return $pdf->stream();
	}

    public function zip_CFDIS($periodo){
        $base = Session::get('base');
        $repo = Session::get('empresa')['id'];
        $url_repo = "/repositorio/".$repo;
        $folder_repositorio = $url_repo . '/timbrado/masivo_xml';

        /* Checar si existen directorios, si no crearlos*/
        if(!File::exists($folder_repositorio)) {
            File::makeDirectory($folder_repositorio, $mode = 0777, true, true);
        }  
        

        $zip_file = $url_repo . '/timbrado/masivo_xml/xml_'.$periodo.'.zip';
     
        $zip_file_path = Storage::disk('public')->path($zip_file);

        if (!Storage::disk('public')->has($zip_file)){

            cambiarBase($base); 

            $timbres = TimbradoEmpleado::where('id_periodo', $periodo)->where('estatus_timbre', TimbradoEmpleado::TIMBRE_EXITOSO)->get();
            //dd($timbres);
            
            $zip = new \ZipArchive();
            $zip->open($zip_file_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            
            foreach($timbres as $timbre){
                $files = Storage::disk('public')->files('repositorio/'.$repo.'/'.$timbre->id_empleado.'/timbrado/archs_cfdi/');
                $file_xml =  collect($files)->filter(function (string $file_name) use ($timbre) {
                                    return (str_contains($file_name, $timbre->num_factura));
                                });
                
                if(!$file_xml)
                    continue;   

                $file_name = explode('/', $file_xml->first());

                $zip->addFile(Storage::disk('public')->path($file_xml->first()), (string) end($file_name));
            }
            
            $zip->close();
        }
        return Storage::disk('public')->download($zip_file, 'cfdi_'.date('Ymd_hms').'.zip');
    }

    public function zip_PDF($periodo){
        $base = Session::get('base');
        $id_repo = Session::get('empresa')['id'];
        
        $url_repo = "repositorio/".$id_repo;
        $folder_repositorio = $url_repo . '/timbrado/masivo_pdf';

        /* Checar si existen directorios, si no crearlos*/
        if(!File::exists($folder_repositorio)) {
            File::makeDirectory($folder_repositorio, $mode = 0777, true, true);
        }  
        
        /* CREAMO URL ZIP */
        $zip_file = $url_repo . '/timbrado/masivo_pdf/pdf_'.$periodo.'.zip';
        $zip_file_path = Storage::disk('public')->path($zip_file);

        if (!Storage::disk('public')->has($zip_file)){

            cambiarBase($base); 

            $timbres = TimbradoEmpleado::where('id_periodo', $periodo)->where('estatus_timbre', TimbradoEmpleado::TIMBRE_EXITOSO)->get();

            // Initializing PHP class
            $zip = new \ZipArchive();
            $zip->open($zip_file_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach($timbres as $timbre){
                $path = "repositorio/". $id_repo . "/" .  $timbre->id_empleado . "/timbrado/";
                $files = Storage::disk('public')->files($path.'archs_cfdi/');
                $file_xml =  collect($files)->filter(function (string $file_name) use ($timbre) {
                        return (str_contains($file_name, $timbre->num_factura));
                    });
                
                if(!$file_xml)
                    continue;   

                $xml_name_file = explode('/', $file_xml->first()); 

                $zip->addFile(Storage::disk('public')->path($file_xml->first()), (string) end($xml_name_file));

                $file_name = explode('/', str_replace('.xml', '.pdf', $file_xml->first()));

                $path_arch_pdf = Storage::disk('public')->path($path . 'archs_pdf/'.(string) end($file_name));
                    
                TimbradoController::genera_pdf(Storage::disk('public')->path($file_xml->first()), $path_arch_pdf);

                $zip->addFile($path_arch_pdf, (string) end($file_name));
            }

            $zip->close();
        }

        return Storage::disk('public')->download($zip_file, 'pdf_'.date('Ymd_hms').'.zip');
    }


}