<?php

namespace App\Providers;

use App\Models\Quote;
use App\Models\RegulatoryEvent;
use App\Observers\QuoteObserver;
use App\Observers\RegulatoryEventObserver;
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
     */
    public function boot(): void
    {
        Quote::observe(QuoteObserver::class);
        RegulatoryEvent::observe(RegulatoryEventObserver::class);

        // Zona horaria configurable desde Configuración > Sistema
        try {
            $settings = app(\App\Settings\GeneralSettings::class);
            $tz = $settings->timezone ?? null;
            if (is_string($tz) && $tz !== '') {
                config(['app.timezone' => $tz]);
                @date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {
            // Es posible que la tabla de settings aún no exista durante migraciones iniciales; ignorar.
        }
    }
}
