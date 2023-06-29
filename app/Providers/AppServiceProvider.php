<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\WeatherDataRepository;
use App\Repositories\WeatherDataRepositoryInterface;
use App\Services\WeatherService;
use App\Services\WeatherServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WeatherDataRepositoryInterface::class, WeatherDataRepository::class);
        $this->app->bind(WeatherServiceInterface::class, WeatherService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
