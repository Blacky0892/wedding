<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        RateLimiter::for('wedding-upload', function (Request $request) {
            [$maxAttempts, $decayMinutes] = array_pad(
                array_map('intval', explode(',', (string) config('wedding.throttle', '20,1'))),
                2,
                1,
            );

            return Limit::perMinutes(max(1, $decayMinutes), max(1, $maxAttempts))->by($request->ip());
        });

        Vite::prefetch(concurrency: 3);
    }
}
