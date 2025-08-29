<?php

namespace Acme\ProductStatus;

use Acme\ProductStatus\Http\Controllers\ProductStatusController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Acme\ProductStatus\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', 'nova.auth', Authorize::class], 'product-status')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
            ->prefix('nova-vendor/product-status')
                ->group(function () {
                    Route::get('/products', [ProductStatusController::class, 'getProducts']);
                    Route::post('/bulk-update', [ProductStatusController::class, 'bulkUpdate']);
                    Route::post('/update', [ProductStatusController::class, 'updateSingle']);
                });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
