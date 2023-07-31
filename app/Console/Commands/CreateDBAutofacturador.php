<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDBAutofacturador extends Command
{
    /**
     ejecutar comando:
    php artisan make:createAutofacturadorDB autofacturador02
     */
    protected $signature = 'make:createAutofacturadorDB {nombrebd} ';

    protected $description = 'Este comando crea una Base de Datos para Autofacturador';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        //
        try {
            $nombrebd = $this->argument('nombrebd');

            // Referencia: https://stackoverflow.com/questions/38832166/how-to-create-a-mysql-db-with-laravel#answer-47316035

            $crearbd = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = "."'".$nombrebd."'");
            if(empty($crearbd)) {
                DB::select('CREATE DATABASE '. $nombrebd .' CHARACTER SET latin1 COLLATE latin1_swedish_ci');

                $tables=DB::select('SHOW TABLES FROM autofacturador;');

                $tablasCreadas='';
                foreach ($tables as $tabla){
                    DB::select('CREATE TABLE  '.$nombrebd.'.'.$tabla->Tables_in_autofacturador.' LIKE autofacturador.'.$tabla->Tables_in_autofacturador.';');
                    $tablasCreadas=$tablasCreadas.$tabla->Tables_in_autofacturador.', ';
                }

                $this->info("La Base de Datos '$nombrebd' con Cotejamiento latin1 con tablas '$tablasCreadas' ha sido creada Correctamente ! ");

            }
            else {
                $this->info("La Base de Datos con el nombre '$nombrebd' ya existe ! ");
            }

        }

        catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
