<?php

namespace Segwitz\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../Controllers' => app_path('Http/Controllers/Segwitz/Auth'),
            __DIR__.'/../Services' => app_path('Services/Segwitz/Services'),
            __DIR__.'/../routes' => base_path('routes/Segwitz'),
        ]);
        
    }
}
