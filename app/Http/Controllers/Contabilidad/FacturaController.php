<?php

namespace App\Http\Controllers\contabilidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\Poliza;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\TimbradoFacturador;
use App\Models\TimbradoCancelacionesFacturador;
use App\Models\EmpresaEmisora;
use App\Models\PeriodosNomina;

class FacturaController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin.hrsystem');
    }
    
    private $ArrayForma=array("01"=>"Efectivo","02"=>"Cheque nominativo","03"=>"Transferencia electrónica de fondos","04"=>"Tarjeta de crédito",
    "05"=>"Monedero electrónico","06"=>"Dinero electrónico","08"=>"Vales de despensa","12"=>"Dación en pago","13"=>"Pago por subrogación",
    "14"=>"Pago por consignación","15"=>"Condonación","17"=>"Compensación","23"=>"Novación","24"=>"Confusión","25"=>"Remisión de deuda",
    "26"=>"Prescripción o caducidad","27"=>"A satisfacción del acreedor","28"=>"Tarjeta de débito","29"=>"Tarjeta de servicios",
    "30"=>"Aplicación de anticipos","31"=>"Intermediario pagos","99"=>"Por definir");
    private $ArrayUnidad=array("H87"=>"Pieza","EA"=>"Elemento","E48"=>"Unidad de servicio","ACT"=>"Actividad","E51"=>"Trabajo","KT"=>"Kits","XBX"=>"Caja","MON"=>"Mes","11"=>"Equipos","DAY"=>"Dia");

    private $ArrayMetodo=array("PUE"=>"Pago en una sola exhibición","PPD"=>"Pago en parcialidades o diferido");
    private $ArrayEst=array("0"=>"EN PROCESO","1"=>"TIMBRADO","2"=>"CANCELADO");
    private $arrayTipo = array("I" => 'Ingresos', "E"=> 'Egresos', "P" => 'Pagos');
    private $arrayRegimen = array("G01" =>'Adquisición de mercancias',"G02" => 'Devoluciones, descuentos o bonificaciones',
    "G03" => 'Gastos en general',"I01" => 'Construcciones',"I02" => 'Mobilario y equipo de oficina por inversiones',"I03" => 'Equipo de transporte',
    "I04" => 'Equipo de computo y accesorios',"I05" => 'Dados, troqueles, moldes, matrices y herramental',"I06" => 'Comunicaciones telefónicas', "I07" => 'Comunicaciones satelitales', "I08" => 'Otra maquinaria y equipo',
    "D01" => 'Honorarios médicos, dentales y gastos hospitalarios.',"D02" => 'Gastos médicos por incapacidad o discapacidad',"D03" => 'Gastos funerales.',"D04" => 'Donativos.',
    "D05" => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).',"D06" => 'Aportaciones voluntarias al SAR.',
    "D07" => 'Primas por seguros de gastos médicos.',"D08" => 'Gastos de transportación escolar obligatoria.',"D09" => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.',
    "D010" => 'Pagos por servicios educativos (colegiaturas)', "P01" => 'Por definir', "CP01" => "Pagos: Emisión de comprobantes de pagos complementarios");


    public function index(Request $request)
    {
            $base= Session::get('base');
            $facturas=array();            

            $data = DB::table($base.'.facturas')
                        ->join('singh.empresas_emisoras', $base.'.facturas.emisora', '=', 'singh.empresas_emisoras.id')
                        ->select($base.'.facturas.*', 'singh.empresas_emisoras.razon_social')
                        ->orderBy($base.'.facturas.id','desc')
                        ->paginate(15);
                        
            cambiarBase($base);
            foreach($data as $d){
                //TODO verificar por que cambio 0 a 1
                //$query = "SELECT SUM(monto*cantidad) as total FROM facturas_detalle WHERE id_factura='".$d->id."' AND estatus='1';";
                $query = "SELECT SUM(monto*cantidad) as total FROM facturas_detalle WHERE id_factura='".$d->id."';";
                $result = DB::connection('empresa')->select($query);
               // dd($d);
                if($result){
                    $d->sub_total = $result[0]->total;
                    $d->iva =  $result[0]->total * 0.16;
                    $d->total = $result[0]->total + $d->iva;
                    $d->metodo_string = $this->ArrayMetodo[$d->metodo];
                    $d->forma_string = $this->ArrayForma[$d->forma];
                    $d->estatus_string = ($d->estatus!= null && $d->estatus !="")?$this->ArrayEst[$d->estatus]:$this->ArrayEst[0];
                }
                $facturas[]= $d;
            }
        
        return view('contabilidad.factura.inicio',compact('facturas'));
    }

    public function insertar(Request $request)
    {
        $base= Session::get('base');
        cambiarBase($base);
        $factura = new Factura();

        $id = $factura->create(array_merge($request->all(), ['estatus' => 0]))->id;
        
        return redirect()->route('factura.editar', ['id' => $id]);
    }

    public function editar(Request $request)
    {        
        $id_factura = $request->route('id');
        
        if ($request->ajax()) {
            $base = Session::get('base');
            cambiarBase($base);
            return $data = Factura::find($id_factura);
        }

        $empresas = EmpresaEmisora::all();
        
        return view('contabilidad.factura.editar',compact('empresas','id_factura'));

    }

    public function getFactura(Request $request){
        $id_factura = $request->route('id');
        $base = Session::get('base');
        cambiarBase($base);
        $data=Factura::find($id_factura);
        return response()->json($data);
    }

    public function actualizar(Request $request)
    {
        $base = Session::get('base');
        cambiarBase($base);
        $factura = Factura::find($request->id);
        $factura->update($request->all());

        
        return $factura = Factura::find($request->id);
    }

    public function getDetalles($id){
        $base = Session::get('base');
        cambiarBase($base);
        return $factura = FacturaDetalle::where('id_factura', $id)->where('estatus',0)->get();
    }

    public function detalleC(Request $request){
        $base = Session::get('base');
        cambiarBase($base);
        $id_factura =$request->input('id_factura');
        $query = "SELECT MAX(id_detalle) idd FROM facturas_detalle WHERE id_factura='$id_factura'; ";
        $result = DB::connection('empresa')->select($query);
        $id_detalle = ($result)?$result[0]->idd:0;
        $id_detalle++;
        $impuesto = $request->input('impuesto_retenido','0');
        $iva_considerar = $request->input('iva_considerar','0');
        $detalle= array (
            'id_factura' => $id_factura,
            'id_detalle' => $id_detalle,
            'cantidad' => $request->input('cantidad'),
            'unidad' => $request->input('unidad'),
            'concepto' => $request->input('concepto'),
            'clave' => $request->input('clave'),
            'monto' => $request->input('monto'),
            'estatus' => $request->input('estatus',0),
            'impuesto_retenido' => $impuesto,
            'iva_considerar' => $iva_considerar
        );
        try{
         DB::connection('empresa')->table('facturas_detalle')->insert($detalle);
         return response()->json(['exito' => true, 'mensaje' => 'Se creo el concepto con exito']);
        }catch(Exception $e){
            return response()->json(['exito' => false, 'mensaje' => 'Ocurrio un error']);
        }

    }

    public function updateDetalle(Request $request){
        $base = Session::get('base');
        cambiarBase($base);
        $detalle = FacturaDetalle::where('id_factura', $request->input('id_factura'))->where('id_detalle',$request->input('id_detalle'))->firstOrFail();
        return DB::connection('empresa')->table('facturas_detalle')->where('id_factura', '=', $request->input('id_factura'))->where('id_detalle','=',$request->input('id_detalle'))
                                            ->update($request->all());
    }

    public function deleteDetalle(Request $request){
        
        $base = Session::get('base');
        cambiarBase($base);
        $detalle = FacturaDetalle::where('id_factura', $request->input('id_factura'))->where('id_detalle',$request->input('id_detalle'))->firstOrFail();
        return DB::connection('empresa')->table('facturas_detalle')
                                         ->where('id_factura', '=', $request->input('id_factura'))
                                         ->where('id_detalle','=',$request->input('id_detalle'))
                                         ->update(['estatus' => '1']);
        return true;
    }

    public function nueva()
    {
        $empresas = EmpresaEmisora::all();
        return view('contabilidad.factura.nueva',compact('empresas'));
    }

    public function ver($id){
        $base = Session::get('base');
        cambiarBase($base);
        $repo =  Session::get('empresa')['id'];

        $factura= Factura::find($id);

        //TODO verificar por que cambio 0 a 1
        $conceptos = FacturaDetalle::where('id_factura', $factura->id)->where('estatus',0)->get();

        $factura->metodo_string = $this->ArrayMetodo[$factura->metodo];
        $factura->forma_string = $this->ArrayForma[$factura->forma];
        $factura->estatus_string = $this->ArrayEst[$factura->estatus];
        $factura->tipo_string = $this->arrayTipo[$factura->tipo_comprobante];
        $factura->regimen_string = $this->arrayRegimen[$factura->regimen];
        $factura->sub_total = 0;
        $factura->iva =  0;
        $factura->total = 0;

        //TODO verificar por que cambio 0 a 1
        $query = "SELECT SUM(monto*cantidad) as total, sum(impuesto_retenido) as retenidos,iva_considerar FROM facturas_detalle WHERE id_factura='".$factura->id."' and estatus=0;";

        $result = DB::connection('empresa')->select($query);
        $factura->sum_retenidos = 0;
        $factura->retenidos = 0;

        if($result){
            if($result[0]->retenidos == 1){
                $factura->retenidos = 1;
                $factura->sum_retenidos = $result[0]->total * 0.06;
            }
            $factura->sub_total = $result[0]->total;
            $factura->iva =  0;
            if($result[0]->iva_considerar ==1){
                $factura->iva =  $result[0]->total * 0.16;
            }
            $factura->total = $result[0]->total + $factura->iva - $factura->sum_retenidos;
        }

        $timbrado = TimbradoFacturador::where('factura', $factura->id)->where('estatus_timbre',1)->get();

        //dd($timbrado, $factura->id, $factura);

        $timbrado_cancelaciones = TimbradoCancelacionesFacturador::where('factura', $factura->id)->get();
        cambiarBase('singh');
        $emisora = EmpresaEmisora::find($factura->emisora);


        return view('contabilidad.factura.ver',compact('factura','conceptos','emisora','timbrado','timbrado_cancelaciones','repo'));
    }

    public function periodo($id){
        $id_periodo = $id;
        $razon_social = session::get('empresa')['razon_social'];
        $id_empresa= session::get('empresa')['id'];
        cambiarBase(Session::get('base'));
        /* Poliza */
        $data = PeriodosNomina::find($id);
        $ejercicio = $data->ejercicio;

        /* Emisoras */
        $query = "SELECT * FROM datos_facturacion$ejercicio WHERE id_periodo='$id_periodo';";
        $datos_facturacion = DB::connection('empresa')->select($query);
        $datos_facturacion = $datos_facturacion[0];
        $emisoras = explode(",",$datos_facturacion->cadena_emisoras);


        /* deposito uno */
        $deposito_uno= array();
        $cont=0;
        for($i=1;$i<=5;$i++){
            $x= 'total_depo1_'.$i;
            if($datos_facturacion->$x > 0 ){
                $id_emisora  = $emisoras[$i-1];
                /* sacamos la factura timbra */
                $query = "SELECT * FROM timbrado_factura 
                          WHERE id_periodo = '$id_periodo' 
                          AND emisora = '$id_emisora' 
                          AND deposito ='0'";
                $facturas = DB::connection('empresa')->select($query);

                //$emisora2 = base64_encode($IDEmpresa);
                /* razon social de la emisora */
                $query = "SELECT razon_social 
                        FROM singh.empresas_emisoras 
                        WHERE id = '$id_emisora';";

                $nombre_emisora = DB::connection('empresa')->select($query);
                $nombre_emisora = $nombre_emisora[0]->razon_social;
                $estatus='NO TIMBRADO';

                if(!empty($facturas)){
                    $a = 'iva_depo1_'.$i;
                    $b = 'subtotal_depo1_'.$i;
                    $c = 'total_depo1_'.$i;

                    $cont++;
                    $deposito_uno['estatus'] = "NO TIMBRADO";
                    $deposito_uno['razon_social_emisora'] = $nombre_emisora;
                    $deposito_uno['iva_deposito_1'] = number_format($datos_facturacion->$a,2);
                    $deposito_uno['subtotal_deposito_1'] = number_format($datos_facturacion->$b,2);
                    $deposito_uno['total_deposito_1'] = number_format($datos_facturacion->$c,2);

                    /* sacamos datos factura */
                    $query = "SELECT * FROM timbrado_factura 
                          WHERE id_periodo='$id_periodo' 
                          AND emisora = '$id_emisora' 
                          AND estatus_timbre = '1' 
                          AND deposito='0'";
                    $factura = DB::connection('empresa')->select($query);

                    /* Datosde los archivos de timbrado */
                    if(!empty($factura)){
                        $deposito_uno['estatus'] = "TIMBRADO";
                        $deposito_uno['XML'] = $factura[0]->file_XML;
                        $deposito_uno['PDF'] = $factura[0]->file_PDF;
                        $deposito_uno['folio_fiscal'] = $factura[0]->folio_fiscal;
                        $deposito_uno['no_factura'] = $factura[0]->no_factura;
                    }
                    /* Datos de una cancelacion de timbrado (En Proceso) */
                    $query = "SELECT * FROM timbrado_cancelaciones_factura 
                          WHERE id_periodo = '$id_periodo'
                          AND emisora = '$id_emisora' AND deposito ='0';";
                    $cancelado_factura = DB::connection('empresa')->select($query);

                    if(!empty($canceladofactura) && count($canceladofactura) == 1){
                        $query = "SELECT * FROM timbrado_factura 
                              WHERE id_periodo='$id_periodo' 
                              AND emisora = '$id_emisora' 
                              AND no_factura = '". $factura[0]->no_factura ."'";

                        $factura_cancelada = DB::connection('empresa')->select($query);
                        $deposito_uno['estatus_cancelacion'] = $factura_cancelada[0];
                    }

                    /* Datos de una cancelacion de timbrado (ya cancelados) */
                    if(!empty($canceladofactura) && count($canceladofactura) > 1){
                        $query="SELECT * FROM factura_periodo 
                                         WHERE id='$id_periodo' 
                                         AND emisora='$id_emisora' 
                                         AND deposito2 = '0' ";
                        $factura_cancelada = DB::connection('empresa')->select($query);
                        $deposito_uno['facturas_cancelacion'] = $factura_cancelada;
                    }
                }
            }
        }
        /* deposito dos */
        $deposito_dos= array();

        $query = "SELECT * FROM timbrado_factura 
                    WHERE id_periodo = '$id_periodo' 
                    AND deposito = '1' ";
        $facturaT = DB::connection('empresa')->select($query);

        if(!empty($facturaT)){

            $query="SELECT * FROM factura_periodo 
                    WHERE idperiodo='$id_periodo' AND deposito2='1' ";
            $facturaP = DB::connection('empresa')->select($query);
            $id_emisora = $facturaP[0]->id;

            /* razon social de la emisora */
            $query = "SELECT razon_social 
            FROM singh.empresas_emisoras 
            WHERE id = '$id_emisora';";
            $nombre_emisora = DB::connection('empresa')->select($query);
            $nombre_emisora = $nombre_emisora[0]->razon_social;
            $estatus='NO TIMBRADO';

            $a = 'iva_depo2_'.$i;
            $b = 'subtotal_depo2_'.$i;
            $c = 'total_depo2_'.$i;

            $deposito_dos['estatus'] = "NO TIMBRADO";
            $deposito_dos['razon_social_emisora'] = $nombre_emisora;
            $deposito_dos['iva_deposito_1'] = number_format($datos_facturacion->$a,2);
            $deposito_dos['subtotal_deposito_1'] = number_format($datos_facturacion->$b,2);
            $deposito_dos['total_deposito_1'] = number_format($datos_facturacion->$c,2);

            /* timbrados */
            $query = "SELECT * FROM timbrado_factura 
                      WHERE id_periodo='$id_periodo' 
                      AND emisora='$id_emisora' 
                      AND etatus_timbre = '1' 
                      AND deposito = '1'";

            $factura_timbrada = DB::connection('empresa')->select($query);

            /* Datos de los archivos de timbrado */
            if(!empty($factura)){
                $deposito_dos['estatus'] = "TIMBRADO";
                $deposito_dos['XML'] = $factura_timbrada[0]->file_XML;
                $deposito_dos['PDF'] = $factura_timbrada[0]->file_PDF;
                $deposito_dos['folio_fiscal'] = $factura_timbrada[0]->folio_fiscal;
                $deposito_dos['no_factura'] = $factura_timbrada[0]->no_factura;
            }


            /************************************************************ */
            /* Datos de una cancelacion de timbrado (En Proceso) */
            $query = "SELECT * FROM timbrado_cancelaciones_factura 
                      WHERE id_periodo = '$id_periodo'
                      AND deposito ='1';";
            $cancelado_factura = DB::connection('empresa')->select($query);

            if(!empty($canceladofactura) && count($canceladofactura) == 1){
                $query = "SELECT * FROM timbrado_factura 
                          WHERE id_periodo='$id_periodo' 
                          AND no_factura = '". $factura_timbrada[0]->no_factura ."'";

                $factura_cancelada = DB::connection('empresa')->select($query);
                $deposito_dos['estatus_cancelacion'] = $factura_cancelada[0];
            }

            /* Datos de una cancelacion de timbrado (ya cancelados) */
            if(!empty($canceladofactura) && count($canceladofactura) > 1){
                /***hayq  ver aquiq  onda */
                $deposito_dos['id_periodo'] = $id_periodo;
                $deposito_dos['id_emisora'] = $id_emisora;
            }
        }

        /* Datos facturador */
        $depositos_fact_uno= array();
        $cont=0;
        for($i=1;$i<=5;$i++){
            $x= 'total_depo1_'.$i;
            if($datos_facturacion->$x>0){
                $id_emisora  = $emisoras[$i-1];
                //$IDEmpresa2=base64_encode($IDEmpresa);
                $query = "SELECT razon_social 
                        FROM singh.empresas_emisoras 
                        WHERE id = '$id_emisora';";

                $nombre_emisora = DB::connection('empresa')->select($query);
                $nombre_emisora = $nombre_emisora[0]->razon_social; //$nmb

                /* sacamos datos factura */
                $query = "SELECT * FROM timbrado_factura 
                          WHERE id_periodo='$id_periodo' 
                          AND emisora = '$id_emisora' 
                          AND estatus_timbre = '1' 
                          AND deposito='0'";
                $facturas = DB::connection('empresa')->select($query);

                if(empty($facturas)){
                    //dd($datos_facturacion);
                    $a = 'iva_depo1_'.$i;
                    $b = 'subtotal_depo1_'.$i;
                    $c = 'total_depo1_'.$i;
                    /*
                                    $cont++;
                                    $deposito_uno['estatus'] = "NO TIMBRADO";
                                    $deposito_uno['razon_social_emisora'] = $nombre_emisora;
                                    $deposito_uno['iva_deposito_1'] = number_format($datos_facturacion->$a,2);
                                    $deposito_uno['subtotal_deposito_1'] = number_format($datos_facturacion->$b,2);
                                    $deposito_uno['total_deposito_1'] = number_format($datos_facturacion->$c,2);
                                    */
                }

            }
        }
        return view('contabilidad.factura.periodo',compact('deposito_uno','deposito_dos'));
    }
}