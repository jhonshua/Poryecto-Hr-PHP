<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\formularios\FormularioController;
use App\Models\Formulario_respuesta;
use App\Models\Empleado;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncuestaCovidEmail;
use App\Models\DetalleFormularioEncuesta;

class EncuestaTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:encuestaTask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecución de encuesta covid ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //return Command::SUCCESS;
        cambiarBase('empresa000046');
        $date = Carbon::now()->locale('es');
        $agregarEncuestaCovid = new FormularioController();
        $idencuesta = $agregarEncuestaCovid->agregarEncuestaCovid();
      
        try{
            
            $empleados= Empleado::select('correo','id')->where('estatus',1)->get()->toArray();
            //$empleados= Empleado::select('correo','id')->whereIn('id',[38,112] )->get()->toArray();
       
            foreach($empleados as $empleado) DetalleFormularioEncuesta::create(['id_empleado'=>$empleado['id'], 'id_encuesta'=>$idencuesta]);
            
            $titulo = 'Aviso Cuestionario COVID';
            $cuerpo = "Usted tiene  un nuevo cuestionario <b>COVID</b> por responder para la empresa <b> DESARROLLADORA DE EMPRESAS ACT SA DE CV </b> <br>el día ".$date->isoFormat('LL');
            foreach($empleados as $empleado) Mail::to($empleado['correo'])->later(now()->addSeconds(3), new EncuestaCovidEmail($titulo, $cuerpo));
            $mensaje ="ok";

        }catch(\Exception $e){
            
           $mensaje = $e->getMessage();
        
        }
        Storage::append('encuestaCovidLog.txt',$mensaje);
    }
}
