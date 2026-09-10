<?php

namespace App\Providers;
use App\Models\RdvCreneau;
use Illuminate\Support\ServiceProvider;
use App\Observers\RdvCreneauObserver;
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
        RdvCreneau::observe(RdvCreneauObserver::class);
    }
}
