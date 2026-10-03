<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Keep the public auth pages available through their custom web routes.

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // AI visual search runs CLIP inference on the CPU, so it is far more
        // expensive per request than a normal API call and gets its own tighter
        // budget. Keyed by user when signed in, otherwise by IP.
        RateLimiter::for('visual-search', function (Request $request) {
            $attempts = (int) config('image_search.search.rate_limit.attempts', 20);
            $decay = (int) config('image_search.search.rate_limit.decay_minutes', 1);

            return Limit::perMinutes($decay, $attempts)
                ->by($request->user()?->id ?: $request->ip());
        });

        // $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
