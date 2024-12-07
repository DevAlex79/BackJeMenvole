<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadApiRoutes();
    }

    protected function loadApiRoutes(): void
    {
        // Route::middleware('api')
        //     ->namespace('App\Http\Controllers\Api') 
        //     ->group(base_path('routes/api.php'));

        Route::prefix('api') // Assure le préfixe "api"
        ->middleware('api')
        ->namespace('App\Http\Controllers\Api') 
        ->group(base_path('routes/api.php'));
    }
}
