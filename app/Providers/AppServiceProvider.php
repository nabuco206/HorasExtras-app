<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use App\Models\TblSolicitudCompensa;
use App\Models\TblSolicitudHe;
use App\Observers\SolicitudEstadoObserver;

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
        // Set the default redirect path after login
        Request::macro('wantsJson', function () {
            return $this->expectsJson();
        });

        TblSolicitudCompensa::observe(SolicitudEstadoObserver::class);
        TblSolicitudHe::observe(SolicitudEstadoObserver::class);

    }
}
