<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

  
    public function map()
    {
        $this->mapApiRoutes();

        // Rutas para peticiones Ajax generales
        $this->mapAjaxRoutes();
        //Rutas del modulo de la norma 035
        $this->mapCuestionarioNorma();
        //Rutas del modulo de la Consultas/reportes
        $this->mapConsultasRoutes();
        // Rutas del modulo de Prestamos
        $this->mapParametriaRoutes();
        // Rutas del modulo de Prestamos
        $this->mapPrestamosRoutes();
        // Rutas del modulo de Herramientas
        $this->mapHerramientasRoutes();
        // Rutas del modulo de Empleados Hrsystem/backend
        $this->mapEmpleadosRoutes();
        // Rutas del modulo de Empleado/front/cliente
        $this->mapEmpleadoRoutes();
        // Rutas del modulo de Herramientas
        $this->mapHerramientasRoutes();
         // Rutas del modulo de IMSS
        $this->mapImssRoutes();
        // Rutas del modulo de Prestamos
        $this->mapParametriaRoutes();
        // Rutas del modulo de Prestamos
        $this->mapPrestamosRoutes();
        // Rutas del modulo de Procesos de calculo
        $this->mapProcesosCalculoRoutes();
        // Rutas del modulo del sistema general/global
        $this->mapSistemaRoutes();
        // Login y Dashboard
        $this->mapWebRoutes();
        // Biometricos
        $this->mapBiometricoRoutes();
        // Contabilidad
        $this->mapContabilidadRoutes();
        //Formularios
        $this->mapFormulariosRoutes();
        //Juridico
        $this->mapJuridicoRoutes();
        //Autofacturador
        $this->mapAutofacturador();

    }

    protected function mapAjaxRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/ajax.php'));
    }


    protected function mapApiRoutes()
    {
        Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
    }

    /**
     * Custom routes for Consultas/HRSystem backend functionality
     */
    protected function mapConsultasRoutes()
    {
        Route::middleware('web')
            ->prefix('consultas')
            ->namespace($this->namespace . '\Consultas')
            ->group(base_path('routes/consultas.php'));
    }

    /**
     * Custom routes for Empleados/HRSystem backend functionality
     */
        protected function mapEmpleadosRoutes()
        {
            Route::middleware('web')
                ->prefix('empleados')
                ->namespace($this->namespace . '\Empleados')
                ->group(base_path('routes/empleados.php'));
        }

        /**
         * Custom routes for Empleado/user/front functionality
         */
        protected function mapEmpleadoRoutes()
        {
            Route::middleware('web')
                ->prefix('empleado')
                ->namespace($this->namespace . '\Empleado')
                ->group(base_path('routes/empleado.php'));
        }

    /**
     * Custom routes for Herramientas functionality
     */
    protected function mapHerramientasRoutes()
    {
        Route::middleware('web')
            ->prefix('herramientas')
            ->namespace($this->namespace . '\Herramientas')
            ->group(base_path('routes/herramientas.php'));
    }

    /**
     * Custom routes for IMSS Module functionality
     */
    protected function mapImssRoutes()
    {
        Route::middleware('web')
            ->prefix('imss')
            ->namespace($this->namespace . '\Imss')
            ->group(base_path('routes/imss.php'));
    }

    /**
     * Custom routes for Prestamos functionality
     */
    protected function mapParametriaRoutes()
    {
        Route::middleware('web')
            ->prefix('parametria')
            ->namespace($this->namespace . '\Parametria')
            ->group(base_path('routes/parametria.php'));
    }

    /**
     * Custom routes for Prestamos functionality
     */
    protected function mapPrestamosRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Prestamos')
            ->group(base_path('routes/prestamos.php'));
    }
    
    /**
     * Custom routes for Prestamos functionality
     */
    protected function mapProcesosCalculoRoutes()
    {
        Route::middleware('web')
            ->prefix('procesos')
            ->namespace($this->namespace . '\procesos')
            ->group(base_path('routes/procesos_calculo.php'));
    }

    /**
     * Custom routes for Prestamos functionality
     */
    protected function mapSistemaRoutes()
    {
        Route::middleware('web')
            ->prefix('sistema')
            ->namespace($this->namespace . '\Sistema')
            ->group(base_path('routes/sistema.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Custom routes for Norma functionality
     */
    protected function mapCuestionarioNorma()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Norma')
            ->group(base_path('routes/norma.php'));
    }
    /**
     * Custom routes for Biometricos functionality
     */
    protected function mapBiometricoRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Biometrico')
            ->group(base_path('routes/biometrico.php'));
    }
    /**
     * Custom routes for Contabilidad functionality
     */
    protected function mapContabilidadRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Contabilidad')
            ->group(base_path('routes/contabilidad.php'));
    }

        /**
     * Custom routes for Formularios functionality
    */
    protected function mapFormulariosRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Formularios')
            ->group(base_path('routes/formularios.php'));
    }


    protected function mapJuridicoRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Juridico')
            ->group(base_path('routes/juridico.php'));
    }

    protected function mapAutofacturador(){
        Route::middleware('web')
            ->prefix('autofacturador')
            ->namespace($this->namespace . '\Autofactura')
            ->group(base_path('routes/autofacturador.php'));
    }

}
