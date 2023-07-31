<?php

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\EncuestaTask::class,
        Commands\UsuariosPermiso::class,
        Commands\UsuarioPermisoEmpresa::class,
        Commands\CreateDBAutofacturador::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('command:encuestaTask')->dailyAt('8:00');
        //$schedule->command('command:encuestaTask')->everyMinute();
        $schedule->call(function () {

            $files = Storage::allFiles('public/trash');
            foreach ($files as $key => $value) {
                Storage::delete([$value]);
            }

        })->name('trash')
            ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
