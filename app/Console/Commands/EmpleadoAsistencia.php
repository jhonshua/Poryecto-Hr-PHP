<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\Empleado;
use App\Models\HorarioDia;
use App\Models\Asistencia;
use App\Models\AsistenciaHorario;

class EmpleadoAsistencia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:asistencias';

    protected $log;
    protected $fecha;
    protected $idEmpresa;


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'En este comando se calculan y actualizan las asistencias y retardos de los empleados.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($fecha = '', $idEmpresa = '')
    {
        parent::__construct();
        $this->log = new Logger('asistencias');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/empleadosAsistencias.log')), Logger::INFO);
        $this->fecha = ($fecha) ?? date('d-m-Y');
        $this->idEmpresa = $idEmpresa;

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->log->info('******************** Asistencias de Empleados del dia: ' . $this->fecha . ' ********************');

        // sacamos el nombre de las bases de datos de las empresas

        ($this->idEmpresa) ? $basesEmpresas = Empresa::select('base')->where('estatus',1)->where('id', $this->idEmpresa)->get() : $basesEmpresas = Empresa::select('base')->where('estatus',1)->get();
        foreach($basesEmpresas as $empresa) $this->calcularAistenciasPorEmpresa($empresa->base);
        
        $this->log->info('**********************************************************');
    }

    public function calcularAistenciasPorEmpresa($base)
    {
        try{

            cambiarBase($base);
            $this->log->info('Empezando aistencias de la BD: ' . $base);
            $hoy =  $this->fecha;

            // Sacamos los empleados activos y con un horario
            $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                ->with('horario')
                ->where('id_horario', '<>', 0)
                ->orderBy('id', 'asc')
                ->get();
            
            $dias_festivos = HorarioDia::select('fecha_festiva', 'id_horario')
                // ->where('id_horario', $empleado->id_horario)
                ->where('fecha_festiva', $hoy)
                ->get()->keyBy('id_horario');
            $t =[];
            foreach ($empleados as $empleado) {

                $num_dia = date('N', strtotime($hoy)); // Num dia de la semana

                $dias_laborales = [
                    1 => $empleado->horario->lunes,
                    2 => $empleado->horario->martes,
                    3 => $empleado->horario->miercoles,
                    4 => $empleado->horario->jueves,
                    5 => $empleado->horario->viernes,
                    6 => $empleado->horario->sabado,
                    7 => $empleado->horario->domingo,
                ];
                // Si es dia festivo o NO laborable - no se insertará registro
                if($dias_laborales[$num_dia] == 0 || isset($dias_festivos[$empleado->id_horario])){

                    $this->log->info('Para el empleado: ' . $empleado->id . '. Día no laborable o festivo.');
                    continue;
                }

                $entradaHorario = $empleado->horario->entrada;
                $salidaHorario = $empleado->horario->salida;

                //Se valida si el dia Domingo es laborable para el empleado
                if( $num_dia == 7 && $empleado->horario->domingo){

                    $entradaHorario = $empleado->horario->domingo_entrada;
                    $salidaHorario = $empleado->horario->domingo_salida;
                
                }else if( $num_dia == 6 && $empleado->horario->sabado){   //Se valida si el dia Sabado es laborable para el empleado
                    
                    $entradaHorario = $empleado->horario->sabado_entrada;
                    $salidaHorario = $empleado->horario->sabado_salida;
                }

                //Se define la hora de entrada del empleado tomando en cuenta los minutos de tolerancia
                $entrada_con_tolerancia = strtotime ( '+'. $empleado->horario->tolerancia .' minutes' , strtotime ( $hoy.' '.$entradaHorario ) ) ;
                $entrada_con_tolerancia = date ( 'Y-m-d H:i:s' , $entrada_con_tolerancia ); // ------------------

                //Consultamos todos los registros de asistencia con el dia que esta comprobando
                $asistencias = Asistencia::where('id_empleado', $empleado->id)->where('fecha', 'like', $hoy.'%')->get();
                
                if($asistencias->count() > 0){

                    //Se define la hora de entrada y salida sacando el valor minimo y maximo de las asistencias
                    $entrada = $asistencias->min('fecha'); //**************
                    $salida = $asistencias->max('fecha'); //**************

                    $entrada_cords = (isValidCoords($asistencias->min('lugar')))?$asistencias->min('lugar'):null;
                    $salida_cords = (isValidCoords($asistencias->max('lugar')))?$asistencias->max('lugar'):null;
                    $lugar = (isValidCoords($asistencias->max('lugar')))?'APP':$asistencias->max('lugar');

                    //Se compara la hora de entrada encontrada
                    $entrada_biometrico = explode(' ',$entrada);
                    $entrada_biometrico = $entrada_biometrico[1];

                    //Se compara la hora de entrada asignada por el horario
                    $entrada_con_tolerancia = explode(' ',$entrada_con_tolerancia);
                    $entrada_con_tolerancia = $entrada_con_tolerancia[1];

                    //Se comparan las horas para verficar si existe retardo
                    $entrada_biometrico = strtotime($entrada_biometrico);
                    $entrada_con_tolerancia = strtotime($entrada_con_tolerancia);
                    $retardo = ($entrada_biometrico > $entrada_con_tolerancia) ? 1 : 0; //**************

                    //Si el empleado tiene asignado horario de comida se valida los registros
                    if($empleado->horario->comida == 1){

                        //Se establece el horario de entrada para la hora de comida
                        $entrada_comida = strtotime ('-30 minute', strtotime ( $hoy.' '.$empleado->horario->entrada_comida ) ) ;
                        $entrada_comida = date ('Y-m-d H:i:s',$entrada_comida );

                        //Se establece el horario de salida para la hora de comida
                        $salida_comida = strtotime ('+30 minute', strtotime ( $hoy.' '. $empleado->horario->salida_comida ) ) ;
                        $salida_comida = date ('Y-m-d H:i:s',$salida_comida);

                        $registros_comida = $asistencias->whereBetween('fecha', [$entrada_comida , $salida_comida]);

                        if($registros_comida->count() > 0){

                            $entrada_comida = $registros_comida->min('fecha'); //**************
                            $salida_comida = $registros_comida->max('fecha'); //**************
                        } else{
                            
                            $entrada_comida = null;
                            $salida_comida = null;
                        }

                    } else {

                        $entrada_comida = null;
                        $salida_comida = null;
                    }

                }else{

                    $entrada = null;
                    $salida = null;
                    $entrada_comida = null;
                    $salida_comida = null;
                    $retardo = 0;
                    $entrada_cords = null;
                    $salida_cords = null;
                    $lugar = null;
                    $this->log->info('No hay registros en biometricos para el empleado: ' . $empleado->id . '.');
             
                }

                $asistencia = (empty($entrada)) ? 0 : 1; // ***************

                $atributos = array(['id_empleado' => $empleado->id,'dia' => $hoy]);
                $data=[
                    'entrada' => $entrada,
                    'entrada_horario' => $hoy . ' ' . $empleado->horario->entrada,
                    'salida' => $salida,
                    'salida_horario' => $hoy . ' ' . $empleado->horario->salida,
                    'comida' => $empleado->horario->comida,
                    'inicio_comida' => $entrada_comida,
                    'inicio_comida_horario' => $hoy . ' ' . $empleado->horario->entrada_comida,
                    'fin_comida' => $salida_comida,
                    'fin_comida_horario' => $hoy . ' ' . $empleado->horario->salida_comida,
                    'retardo' => $retardo,
                    'asistencia' => $asistencia,
                    'lugar' => $lugar,
                    'coordenadas_1' => $entrada_cords,
                    'coordenadas_4' => $salida_cords
                ];
            
                $registro = DB::connection('empresa')->table('asistencia_horario')->updateOrInsert(['id_empleado' => $empleado->id,'dia' => $hoy,], $data);
                $registro1 = ($registro) ? ' EXITOSO' : 'FALLIDO';
                $this->log->info('registro: ' . $registro);
                $this->log->info('Empleado: ' . $empleado->id . '. Registro ' . $registro1, ['entrada' => $entrada, 'salida' => $salida]);
            }

        }catch(\PDOException $e){
            
            $this->log->error('ERROR - La BD: ' . $base . ' no esta disponible.');
            $this->log->error($e->getMessage());
            
        }
    }

    protected function Aistencias_tipoII($base)
    {
        try{

            cambiarBase($base);
            $this->log->info('Empezando aistencias de la BD: ' . $base);
            $hoy = $this->fecha;

            // Sacamos los empleados activos y con un horario
            $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                ->with('horario')
                ->where('id_horario', '<>', 0)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($empleados as $empleado) {

                //Consultamos todos los registros de asistencia con el dia que esta comprobando
                $asistencias = Asistencia::where([['id_empleado', $empleado->id],['fecha', 'like', $hoy.'%'] ] )->get();

                if($asistencias->count() > 0){

                    //Se define la hora de entrada y salida sacando el valor minimo y maximo de las asistencias
                    $entrada = $asistencias->min('fecha'); //**************
                    $salida = $asistencias->max('fecha'); //**************
                    $entrada_cords = (isValidCoords($asistencias->min('lugar')))?$asistencias->min('lugar'):null;
                    $salida_cords = (isValidCoords($asistencias->max('lugar')))?$asistencias->max('lugar'):null;
                    $lugar = (isValidCoords($asistencias->max('lugar')))?'APP':$asistencias->max('lugar');
                    //Se extrae la hora.
                    $entrada_biometrico = explode(' ',$entrada);
                    $entrada  = $entrada_biometrico[1];
                    $salida_biometrico = explode(' ',$entrada);
                    $salida  = $salida_biometrico[1];
                }else{

                    $entrada = null;
                    $salida = null;
                    $entrada_cords = null;
                    $salida_cords = null;
                    $lugar = null;
                    $this->log->info('No hay registros en biometricos para el empleado: ' . $empleado->id . '.');
                }

                $registro = AsistenciaHorario::updateOrInsert(
                [
                    'id_empleado' => $empleado->id,
                    'dia' => $hoy,
                ],[
                    'entrada' => $entrada,
                    'salida' => $salida,                        
                    'comida' => $empleado->horario->comida,
                    'inicio_comida' => $empleado->horario->entrada_comida,
                    'inicio_comida_horario' => $hoy . ' ' . $empleado->horario->entrada_comida,
                    'fin_comida' => $empleado->horario->salida_comida,
                    'fin_comida_horario' => $hoy . ' ' . $empleado->horario->salida_comida,
                    'retardo' => 0,
                    'asistencia' => 1,
                    'lugar' => $lugar,
                    'coordenadas_1' => $entrada_cords,
                    'coordenadas_4' => $salida_cords,
                ]);

                $registro1 = ($registro) ? ' EXITOSO' : 'FALLIDO';
                $this->log->info('registro: ' . $registro);
                $this->log->info('Empleado: ' . $empleado->id . '. Registro ' . $registro1, ['entrada' => $entrada, 'salida' => $salida]);
            }

        } catch(\PDOException  $e) { 

            $this->log->error('ERROR - La BD: ' . $base . ' no esta disponible.');
            $this->log->error($e->getMessage());
        
        }
    }
}