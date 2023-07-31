<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\PeriodosNomina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PolizaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('contabilidad.polizas.inicio');
    }

    function polizaPaginacion(Request $request){
        cambiarBase(Session::get('base'));
        $data = PeriodosNomina::where('estatus','<>', 2)
            ->orderBy('id','desc')
            ->paginate(10);
        return array('pagination' =>[
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page'     => $data->perPage(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ],'periodos' =>$data);
    }

    public function facturas(){
        return view('contabilidad.polizas.periodos_cerrados');
    }

    public function facturaPaginacion(Request $request){
        cambiarBase(Session::get('base'));
        $data = PeriodosNomina::where('estatus','<>', 2)
            ->orderBy('id','desc')
            ->paginate(10);

        return array('pagination' =>[
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page'     => $data->perPage(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ],'periodos' =>$data);

    }

    public function exportar(Request $request){
        /* Periodo */
        $id_periodo =$request->id;
        $nombre_periodo = "";
        $ejercicio = "";
        // Empresa
        $id_empresa = session::get('empresa')['id'];
        /* Empresa = Razon social de la empresa */
        $empresa =  Session::get('empresa')['razon_social'];
        /*Cuenta contable y conceptoo de nomina contable */
        $query = "SELECT num_cuenta_contable,concepto_nomina_contable from empresas_emisoras emi 
                         join asigna_empresas_emisoras asiemi on emi.id = asiemi.id_empresa_e 
                         inner join empresas empre on asiemi.id_empresa = empre.id where empre.razon_social='$empresa' ORDER BY num_cuenta_contable DESC";

        $rEmpresa = DB::select($query);
        $cuentaBancaria=$rEmpresa[0]->num_cuenta_contable;
        $conceptonominaContable=$rEmpresa[0]->concepto_nomina_contable;
        /* DATOS del Formulario y Generales */
        $numero_poliza     = $request->poliza;
        $titulo_nomina     = $request->titulo;
        $dias_poliza       = $request->dia;
        $cuenta_imms       = $request->imss;
        $cuenta_imms2      = '2170-001-000';
        $nombre_imss       = 'IMSS';
        $cuenta_rcv        = $request->rcv;
        $cuenta_rcv2       = '2170-002-000';
        $nombre_rcv        = 'RCV';
        $cuenta_infonavit  = $request->infonavit;
        $cuenta_infonavit2 = '2170-003-000';
        $nombre_infonavit  = 'INFONAVIT';
        $cuenta_ins        =  $request->ins_deber;
        $cuenta_ins2       =  $request->ins_haber;
        $nombre_ins        = 'INS';

        /* Cambiamos a la base de datos de la empresa */
        cambiarBase(Session::get('base'));
        /* Poliza */
        $periodo = PeriodosNomina::find($id_periodo);
        $nombre_periodo = $periodo->nombre_periodo;
        $ejercicio = $periodo->ejercicio;

        /* conceptos de nomina */
        $catalogo_conceptos = array();

        /* ToDo Cambiar a un modelo conceptosNomina */
        /* Percepciones */
        $query = "SELECT id,nombre_concepto,nombre_corto
                  FROM conceptos_nomina 
                  WHERE estatus = 1 AND file_rool <> 0 AND nomina = 1";
        $rConceptos = DB::connection('empresa')->select($query);
        foreach($rConceptos as $c){
            $campo = 'Valor'.$c->id;
            $query = "SHOW COLUMNS FROM rutinas$ejercicio WHERE Field ='$campo'";
            $rConcepto = DB::connection('empresa')->select($query);

            if(!empty($rConcepto)){
                $catalogo_conceptos[] = $c->id;
            }
        }
        $cadena_catalogo_conceptos = implode(',',$catalogo_conceptos);

        /* Parametro del sistema */
        $query ="SELECT provision_porcentaje, porcentaje_nomina 
                 FROM parametros";
        $rParametros = DB::connection('empresa')->select($query);

        $provision_porcentaje = $rParametros[0]->provision_porcentaje;
        $porcentaje_nomina    = $rParametros[0]->porcentaje_nomina;

        /* Percepcion Fiscal *
        echo $query="SELECT sum(total_percepcion_fiscal) AS suma
                            FROM rutinas$ejercicio ru
                            JOIN empleados em ON ru.id_empleado = em.id
                            WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                            AND id_periodo = '$id_periodo' and em.tipo_de_nomina='$nombre_periodo'";
        $rPercepcion = DB::connection('generica')->select($query);
        /* Cuota Fija *
        $query = "SELECT sum(cuota_fija) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleados em ON ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo='$id_periodo' AND em.tipo_de_nomina='$nombre_periodo'";
        $rCuota = DB::connection('generica')->select($query);

        /* ExcePa *
        $query = "SELECT sum(exce_pa) as suma
                  FROM rutinas$ejercicio ru
                  JOIN empleados em on ru.id_empleado = em.ide
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' and em.tipo_de_nomina='$nombre_periodo'";
        $rExce = DB::connection('generica')->select($query);

        /* Porcentaje Patronal *
        $query = "SELECT sum(pre_dine_patro) as suma
                            from rutinas$ejercicio ru
                            join empleados em on ru.idempleado=em.idempleado
                            where em.estatus = 1 and finiquitado=0 and ru.fnq_valor=0
                            and id_periodo='$id_periodo'  and em.tipo_de_nomina='$nombre_periodo'";

        /* Censa Vejez Obrero Patronal
        $query = "SELECT sum(censa_vejez_obre_patronal) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleados em on ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 and ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' AND em.tipo_de_nomina='$nombre_periodo'";

        /* GasTo Medio Patronal
        $query = "SELECT sum(gas_medi_patro) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleados em ON ru.id_empleado = em.id
                  WHERE em.estatus = 1 and finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo='$id_periodo' AND em.tipo_de_nomina='$nombre_periodo'";

        /* Riesgo de Trabajo
        $query = "SELECT sum(riesgo_trabajo) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em on ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo='$idperiodo' AND em.tipo_de_nomina='$nombre_eriodo'";

        /* Invalidez Vida Patron
        $query = "SELECT sum(inva_vida_patro) as suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em ON ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' AND em.tipo_de_nomina = '$nombre_periodo'";

        /* Prestacion Guarderia
        $query = "SELECT sum(guarde_presta) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em ON ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' AND em.tipo_de_nomina = '$nombre_periodo'";

        /* Censa Verjez Patronal
        $query = "SELECT sum(censa_vejez_patro) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em ON ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND  id_periodo = '$id_periodo' AND em.tipo_de_nomina='$nombre_periodo'";

        /* Infonavit Patron
        $query = "SELECT sum(infonavit_patro) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em on ru.id_empleado = em.id
                  WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' AND em.tipo_de_nomina = '$nombre_periodo'";

        /* SAR Patron

        $query = "SELECT sum(sar_patron) AS suma
                  FROM rutinas$ejercicio ru
                  JOIN empleado em on ru.id_empleado = em.id_empleado
                  WHERE em.estatus = 1 and diniquitado = 0 AND ru.fnq_valor = 0
                  AND id_periodo = '$id_periodo' AND em.tipo_de_nomina = '$nombre_periodo'";
        */


        /* Juntas */
        $query = "SELECT sum(total_percepcion_fiscal) AS percepcion_fiscal, 
                         sum(cuota_fija) AS cuota_fija, 
                         sum(exce_pa) AS exce_pa,
                         sum(pre_dine_patro) AS pre_dine_patro, 
                         sum(censa_vejez_obre_patronal) AS censa_vejez_obre_patronal,
                         sum(gas_medi_patro) AS gas_medi_patro, 
                         sum(riesgo_trabajo) AS riesgo_trabajo, 
                         sum(inva_vida_patro) AS inva_vida_patro,
                         sum(guarde_presta) AS guarde_presta, 
                         sum(censa_vejez_patron) AS censa_vejez_patro, 
                         sum(infonavit_patro) AS infonavit_patro,
                         sum(sar_patron)  AS sar_patron
                         FROM rutinas$ejercicio ru 
                         JOIN empleados em ON ru.id_empleado = em.id
                         WHERE em.estatus = 1 AND finiquitado = 0 AND ru.fnq_valor = 0
                         AND id_periodo='$id_periodo' AND em.tipo_de_nomina='$nombre_periodo'";

        $cuotas = DB::connection('empresa')->select($query);

        $percepcion_fiscal          = $cuotas[0]->percepcion_fiscal;
        $cuota_fija                 = round($cuotas[0]->cuota_fija,2)        * $provision_porcentaje;
        $exce_pa                    = round($cuotas[0]->exce_pa,2)           * $provision_porcentaje;
        $pre_dine_patro             = round($cuotas[0]->pre_dine_patro,2)    * $provision_porcentaje;
        $censa_vejez_obre_patronal  = round($cuotas[0]->censa_vejez_obre_patronal,2);
        $gas_medi_patro             = round($cuotas[0]->gas_medi_patro,2)    * $provision_porcentaje;
        $riesgo_trabajo             = round($cuotas[0]->riesgo_trabajo,2)    * $provision_porcentaje;
        $inva_vida_patro            = round($cuotas[0]->inva_vida_patro,2)   * $provision_porcentaje;
        $guarde_presta              = round($cuotas[0]->guarde_presta,2)     * $provision_porcentaje;
        $censa_vejez_patro          = round($cuotas[0]->censa_vejez_patro,2) * $provision_porcentaje;
        $infonavit_patro            = round($cuotas[0]->infonavit_patro,2)   * $provision_porcentaje;
        $sar_patron                 = round($cuotas[0]->sar_patron,2)        * $provision_porcentaje;


        $porcentaje_nomina = $porcentaje_nomina / 100;
        $errogacion = $percepcion_fiscal * $porcentaje_nomina;
        $valor_imms = $cuota_fija + $exce_pa + $pre_dine_patro + $gas_medi_patro + $riesgo_trabajo + $inva_vida_patro + $guarde_presta;
        $valor_rcv  = $sar_patron + $censa_vejez_patro;
        $valor_infonavit = $infonavit_patro;
        $valor_ins = $errogacion;


        /* Concepto de Nomina */
        $queryConcepto="SELECT * FROM conceptos_nomina 
                        WHERE estatus = 1 
                        AND file_rool <> 0 AND nomina = 1 AND file_rool <= '249' AND id IN ($cadena_catalogo_conceptos)";
        $result = DB::connection('empresa')->select($queryConcepto);

        $suma_debe = 0;
        $suma_haber = 0;
        $array_conceptos = array();
        $array_subsidio = array();
        $cuentaSubsidio2 ;
        $namecuentaSubsidio2;
        foreach($result as $r){
            $id_concepto=$r->id;
            /* Valor en rutina */
            $q = "SELECT sum(total$id_concepto) 
                  AS result 
                  FROM rutinas$ejercicio 
                  WHERE fnq_valor = 0 AND id_periodo='$id_periodo'";

            $result1 = DB::connection('empresa')->select($q);
            $valor = round($result1[0]->result,2);

            $cuentaSubsidio = $r->cuenta_contable;
            $namecuentaSubsidio = $r->name_cuenta;

            /* Sacamos su ISR */
            $qIsr = "SELECT * from conceptos_nomina 
                     WHERE estatus = 1 AND rutinas='ISR' AND id='$id_concepto'";
            $result2 = DB::connection('empresa')->select($qIsr);


            if(!empty($result2)){
                $qValor = "SELECT sum(total$id_concepto) AS result 
                           FROM rutinas$ejercicio 
                           WHERE fnq_valor = 0 AND id_periodo='$id_periodo' AND total$id_concepto > 0";
                $rValor = DB::connection('empresa')->select($qValor);
                $valor = $rValor[0]->result;

                /* Sacamos Subsidio */
                $qValorSubsidio = "SELECT sum(total$id_concepto) AS result 
                                   FROM rutinas$ejercicio 
                                   WHERE fnq_valor = 0 AND id_periodo='$id_periodo' and total$id_concepto < 0";
                $ValorSubsidio = DB::connection('empresa')->select($qValorSubsidio);
                $valor_subsidio = $ValorSubsidio[0]->result*-1;

                $cuentaSubsidio2 = $r->cuenta_contable_isr;
                $namecuentaSubsidio2 = $r->name_cuenta_isr;

                $debe_subsidio = $valor_subsidio;
                $haber_subsidio = 0;
            }

            if( $r->debe_haber == 1 ){
                $debe=$valor;
                $haber=0;
            }else{
                $debe=0;
                $haber=$valor;
            }
            $array_conceptos[]= array(
                'cuenta_contable' => $r->cuenta_contable,
                'nombre_concepto' => $r->nombre_concepto,
                'name_cuenta' => $r->name_cuenta,
                'debe' => number_format($debe,2),
                'haber' => number_format($haber,2)
            );

            $suma_debe += $debe;
            $suma_haber += $haber;

        }

        if($valor_subsidio > 0 ){
            /* HACER String */
            $array_subsidio[]= array(
                'cuenta_subsidio' => $cuentaSubsidio2,
                'nombre_cuenta' => $namecuentaSubsidio2,
                'debe' => number_format($debe_subsidio,2),
                'haber' => number_format($haber_subsidio,2),
            );
            $suma_debe  = $suma_debe  + $debe_subsidio;
            $suma_haber = $suma_haber + $haber_subsidio;
        }

        $diferencia = $suma_debe - $suma_haber;
        if($diferencia<0){
            $diferencia = $diferencia * -1;
        }
        if($suma_debe < $suma_haber){
            $total_debe = $diferencia;
            $total_haber = 0;
        }else{
            $total_haber = $diferencia;
            $total_debe = 0;
        }

        return view('contabilidad.polizas.excel',compact('empresa','cuentaBancaria','conceptonominaContable','numero_poliza',
            'titulo_nomina',
            'dias_poliza',
            'cuenta_imms',
            'cuenta_imms2',
            'nombre_imss',
            'cuenta_rcv',
            'cuenta_rcv2',
            'nombre_rcv',
            'cuenta_infonavit',
            'cuenta_infonavit2',
            'nombre_infonavit',
            'cuenta_ins',
            'cuenta_ins2',
            'nombre_ins','array_conceptos','array_subsidio',
            'valor_imms',
            'valor_rcv',
            'valor_infonavit',
            'valor_ins',
            'total_haber','total_debe' ));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
