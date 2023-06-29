<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\WeatherService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // ...
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService($app->make(WeatherService::class));
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Illuminate\Support\Facades\Auth::viaRequest('sanctum', function ($request) {
            return $request->user();
        });
    }
}
