<?php 

namespace App\Http\Controllers\Juridico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Demanda;
use App\Models\Audiencia;
use App\Models\Empleado;
use App\Models\InvolucradoDemanda;
use DateTime;
use DataTables;


class DemandasController extends Controller
{

    protected const DEMANDA_ACTIVA = 1;
    protected const DEMANDA_AMPARO = 2;
    protected const DEMANDA_CONCILIADO = 3;


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function demandas(Request $request)
    {
        cambiarBase(Session::get('base'));

        if ($request->ajax()) {
            $demandas = Demanda::with('empleado','audiencias')->orderBy('fecha_baja');
           // dd($demandas->get());
            return Datatables::of($demandas->get())
            ->addColumn('nombre', function($row){
                return $row->empleado->nombre." ".$row->empleado->apaterno." ".$row->empleado->amaterno;
            })
            ->editColumn('proxima_audiencia', function($row){
                if($row->estatus == self::DEMANDA_CONCILIADO){
                    $ProxAudiencia='NA,';
                }else if(($row->estatus == self::DEMANDA_AMPARO) || ($row->estatus == self::DEMANDA_ACTIVA)){
                    if( $row->audiencias->first() != null ){
                        $fprox = new DateTime($row->audiencias->first()->fecha_proxima);
                        $ProxAudiencia = $fprox->format('d-m-Y');
                    }else{
                        return "NA";
                    }
                }
                return $ProxAudiencia;
            })
            ->editColumn('fecha_baja', function($row){
                    if(!empty($row->fecha_baja)){
                        $f = new DateTime($row->fecha_baja);
                        $fechaBaja = $f->format('d-m-Y');
                    }else{
                       $fechaBaja='-'; 
                    }
                
                return $fechaBaja;
            })
            ->addColumn('total', function($row){
                $tot = $this->calcularTotal($row);
                return "$" . $tot['total'];
            })
            ->addColumn('operaciones',function($row){
                $btn = '<div>';
                if($row->estatus != self::DEMANDA_CONCILIADO){
                    $btn .= '<span class="tooltip_" data-toggle="tooltip"  title="Editar Demanda"/>
                                <a data-demanda="'.$row->id.'"  data-toggle="modal" data-target="#demandaModal">
                                    <img src="/img/icono-editar.png" width="20px">
                                </a>
                            </span>';
                }
                            
                    $btn .= '<span class="tooltip_" data-toggle="tooltip"  title="VER AUDIENCIAS"/>
                                <a href="#" data-demanda="'.$row->id.'" class="audiencia mr-2" data-toggle="tooltip">
                                    <i class="fa fa-balance-scale"></i>
                                </a>
                            </span>
                    </div>'; 
                    return $btn;
            })
            ->editColumn('folio', function($row){
                if($row->folio == "" || $row->folio == null){
                    return "-";
                }
                return $row->folio;
            })
            ->editColumn('estatus', function($row){
                if($row->estatus == self::DEMANDA_ACTIVA){
                    return "ACTIVA";
                }else if($row->estatus == self::DEMANDA_AMPARO){
                    return "AMPARO";
                }else if($row->estatus == self::DEMANDA_CONCILIADO){
                    return "CONCILIADO";
                }else{
                    return "-";
                }
            })
            ->rawColumns(['operaciones'])
            ->make(true);
        }
        $demandas = Demanda::join('empleados', 'demandas_juridico.id_empleado', '=', 'empleados.id')
            ->select('demandas_juridico.id', 'empleados.nombre', 'empleados.apaterno','empleados.amaterno')
            ->where('demandas_juridico.estatus',1)->get();


        return view('juridico.demandas-inicio',compact('demandas'));
    }

    public function calcularTotal($row,$salario_diario = null){
        // si no viene el salario diario en el objeto se tiene que enviar como parametro
        $total = $honorarios = 0;
        if($salario_diario == null){ 
            $IndmConst = $row->salario_diario * $row->indemnizacion_constitucional;
        }else{
            $IndmConst = $salario_diario * $row->indemnizacion_constitucional;
        }
        
        $h = $row->audiencias_costo; //audienciasdf78}
        if($h->count()>0){
            $honorarios = $h->sum('CostoEstHono');// suma de todas las audiencias
        }
                
        if(($row->estatus == self::DEMANDA_ACTIVA) OR ($row->estatus == self::DEMANDA_AMPARO)){
            $total = $row->importe + $IndmConst + $row->indemnizacion_anio + $row->salario_caido + $row->prestaciones_devengadas + $row->importe_extra + $honorarios;
        }else if($row->estatus == self::DEMANDA_CONCILIADO){
            $total = $row->importe_extra + $honorarios;
        if($row->importe != null)
            $total= $total + $row->importe;
        if($row->indemnizacion_anio != null)
            $total = $total + $row->indemnizacion_anio;
        if($row->indemnizacion_constitucional != null)
            $total = $total + $row->indemnizacion_constitucional;
        if($row->salario_caido != null)
            $total = $total + $row->salario_caido;
        if($row->prestaciones_devengadas != null)
            $total = $total + $row->prestaciones_devengadas;
        }
        return array('total' => $total, 'honorarios' => $honorarios);
    }

    public function detalledemanda(Request $request, $id){
        $empresa = Session::get('empresa'); // datos de la empresa
        //ontener datos de las emisoras de la base singh
        //$query = "select ID_EmpresaE,razonSocial from empresas_emisoras where ID_EmpresaE in (select ID_EmpresaE from asigna_empresas_emisoras where idEmpresa= ".$empresa['id']." and  status=0) and status=0";
        $query = "SELECT emi.id, emi.razon_social from asigna_empresas_emisoras asi 
        inner JOIN empresas_emisoras emi on emi.id = asi.id_empresa_e where asi.id_empresa = " .Session::get('empresa')['id'] . "
        and  asi.estatus= 1;";
        $emisoras = DB::select($query);
        //dd($emisoras);
        cambiarBase(Session::get('base'));
        $demanda = Demanda::find($id); //obtener informacion de la demanda
        $contrato = $actor = $acusado = array();
        //obtener involucrados en la demanda (actor y acusado)
        foreach($demanda->involucrados as $involucrado){
            if($involucrado->tipo_involucrado == 1){
                array_push($actor,$involucrado);
            }else if($involucrado->tipo_involucrado == 2){
                array_push($acusado,$involucrado);
            }else{
                array_push($contrato,$involucrado);
            }
        }
        //dd($demanda->empleado);
        return response()->json(['ok' => 1,'contrato' => $contrato,'emisoras' => $emisoras,'demanda' => $demanda,'empresa' => $empresa,'empleado'=> $demanda->empleado,'actor'=>$actor,'acusado'=>$acusado]);
    }

    
    public function editardemanda(Request $request){
        
        cambiarBase(Session::get('base'));
        //print_r($_POST);
        $demanda = Demanda::find($request->idDemanda);
        // tipo_ involucrado  1 = actor, 2 = acusado, 3 = contrato
        //elimino los acusados (tipo_involucrado = 2) para volver a insertarlos ya que pueden ser n
        InvolucradoDemanda::where('id_demanda_juridico',$request->idDemanda)->delete();
        /*insertar el actor  (tipo_involucrado = 1)
        id_involucrado = 1 es el trabajador
        id_involucrado = 2 es el beneficiario*/
        InvolucradoDemanda::insert([
            'id_involucrado' => intval($request->PActora),
            'id_demanda_juridico' => $request->idDemanda,
            'tipo_involucrado' => 1,
        ]);

        //insertar quien contrató (tipo_involucrado = 3)
        //se guarda el id de la empresa
        InvolucradoDemanda::insert([
            'id_involucrado' => intval($request->contrato),
            'id_demanda_juridico' => $request->idDemanda,
            'tipo_involucrado' => 3,
        ]);
      

        //insertar los acusados(tipo_involucrado = 2)
        //se guarda el id de la empresa
        
        foreach($request->cliente as $acusado){
            if($acusado == 0){
                InvolucradoDemanda::insert([
                    'id_involucrado' => $acusado,
                    'id_demanda_juridico' => $request->idDemanda,
                    'tipo_involucrado' => 2,
                    'otro_involucrado' => $request->NmbOtro
                ]);

            }else{
                InvolucradoDemanda::insert([
                    'id_involucrado' => $acusado,
                    'id_demanda_juridico' => $request->idDemanda,
                    'tipo_involucrado' => 2
                ]);
            }
        }

        //modificar datos de demandas_juridico
        $demanda->prestaciones_devengadas = $request->prestaciones_devengadas;
        $demanda->folio = $request->folio;
        $demanda->indemnizacion_constitucional = $request->indemnizacion_constitucional;
        $demanda->motivo = $request->motivo;
        $finicio_demanda = new DateTime($request->InicioDemanda);
        $demanda->created_at = $finicio_demanda->format('Y-m-d').' 00:00:00';
        if($demanda->save()){
            return response()->json(['ok' => 1,'msg' => 'La demanda se actualizó con éxito']);
        }
        return response()->json(['ok' => 0,'msg' => 'No fue posible actualizar la demanda, intentelo nuevamente']);
        
    }

    public function calendario()
    {
        cambiarBase(Session::get('base'));
        $demandas = Demanda::join('empleados', 'demandas_juridico.id_empleado', '=', 'empleados.id')
        ->select('demandas_juridico.*', 'empleados.nombre', 'empleados.apaterno','empleados.amaterno')
        ->with('audiencias')->get();
        
        return view('juridico.calendario-inicio',compact('demandas'));
    }

}