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
    }
}
