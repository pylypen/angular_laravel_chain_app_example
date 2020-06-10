<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapCmsRoutes();

        $this->mapCertRoutes();

        $this->mapWebRoutes();
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
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::namespace('App\Http\Controllers\Api')
            ->domain('{api}.' . env('DOMAIN_NAME'))
            ->middleware('api')
            ->middleware('api.response')
            ->group(base_path('routes/api.php'));
    }

    /**
     * CMS routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapCmsRoutes()
    {
        Route::namespace('App\Http\Controllers\Cms')
            ->domain('{cms}.' . env('DOMAIN_NAME'))
            ->group(base_path('routes/cms.php'));
    }

    /**
     * Certificates routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapCertRoutes()
    {
        Route::namespace('App\Http\Controllers\Certificates')
            ->domain('{certificates}.' . env('DOMAIN_NAME'))
            ->group(base_path('routes/certificates.php'));
    }
}
