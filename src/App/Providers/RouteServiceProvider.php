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
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            Route::group([
                'prefix' => 'api',
            ], function () {

                Route::middleware('api')
                ->prefix('stops')
                ->namespace($this->namespace)
                ->group(base_path('routes/stops.php'));

                Route::middleware('api')
                ->prefix('routes')
                ->namespace($this->namespace)
                ->group(base_path('routes/routes.php'));

                Route::middleware('api')
                    ->prefix('buses')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/buses.php'));

                Route::middleware('api')
                ->prefix('auth')
                ->namespace($this->namespace)
                ->group(base_path('routes/auth.php'));

                Route::middleware('api')
                    ->prefix('tarifs')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/tarifs.php'));

            });


            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));


            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
