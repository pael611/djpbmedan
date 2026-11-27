<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Paksa skema HTTPS pada lingkungan produksi (VA-01)
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Batasi percobaan login untuk mencegah brute-force (VA-04)
        RateLimiter::for('login', function (Request $request): Limit {
            $username = strtolower(trim((string) $request->input('username')));

            return Limit::perMinute(5)->by($username.'|'.$request->ip());
        });
    }
}
