<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
     *
     * Les routes API sont chargées par bootstrap/app.php (withRouting: api).
     */
    public function boot(): void
    {
        //
    }
}
