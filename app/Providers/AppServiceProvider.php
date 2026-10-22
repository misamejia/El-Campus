<?php

namespace App\Providers;

use App\Services\Ai\AiManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Un único servicio de IA por petición (resuelve y cachea el driver).
        $this->app->singleton(AiManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El periódico es 100 % en español; los formatos de fecha de Carbon
        // (p. ej. isoFormat) generan nombres de meses y horas en español.
        Carbon::setLocale('es');
    }
}
